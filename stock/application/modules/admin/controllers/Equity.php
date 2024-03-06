<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Equity extends Common_Api_Controller {

    public function __construct() {
        parent::__construct();
        $_POST = $this->post();
    }

    public function get_lineup_detail_post()
	{
		$lineup_master_contest_id = $this->input->post('lineup_master_contest_id');
		
		$post_params = $this->input->post();
		$data_arr = $this->input->post();
		
		$this->load->model('Contest_model');
		$contest_info = $this->Contest_model->get_contest_collection_details_by_lmc_id($lineup_master_contest_id,"CM.collection_id,LM.lineup_master_id,CM.scheduled_date,C.status,CM.is_lineup_processed,LMC.total_score,LMC.game_rank,LM.user_name,LM.team_name,LM.team_data,LMC.prize_data,LMC.is_winner,CM.published_date,CM.end_date,LM.remaining_cap");
		
		if(empty($contest_info)){
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "No details found.";
            $this->api_response_arry['service_name'] = "get_admin_lineup_detail";
            $this->api_response();
        }

        $team_data = json_decode($contest_info['team_data'],TRUE);
       /* $collection_player_cache_key = "st_collection_stocks_" . $contest_info['collection_id'];f
        $stocks_list = $this->get_cache_data($collection_player_cache_key);
        if (!$stocks_list) { */
            $post_data['collection_id'] = $contest_info['collection_id'];
            $post_data['published_date'] = $contest_info['published_date'];
            $post_data['end_date'] = $contest_info['end_date'];
            $stocks_list = $this->Contest_model->get_all_stocks($post_data);
            //set collection players in cache for 2 days
         /*   $this->set_cache_data($collection_player_cache_key, $stocks_list, REDIS_2_DAYS);
        }*/
        $stock_list_array = array_column($stocks_list, NULL, 'stock_id');
        if($contest_info['is_lineup_processed'] == "1"){
            $lineup_details = $this->Contest_model->get_lineup_with_score($lineup_master_contest_id, $contest_info);
            $team_data['pl'] = array_column($lineup_details,NULL,"stock_id");
        }else if(in_array($contest_info['is_lineup_processed'],array("2","3"))){
            $completed_team = $this->Contest_model->get_single_row("collection_id,lineup_master_id,team_data",COMPLETED_TEAM, array("collection_id" => $contest_info['collection_id'], "lineup_master_id" => $contest_info['lineup_master_id']));
            $team_data = json_decode($completed_team['team_data'],TRUE);

			$team_data['pl'] = $this->get_rendered_stock_complted_fixture($team_data['b'],1);
			$sell_data = $this->get_rendered_stock_complted_fixture($team_data['s'],2);
			$team_data['pl'] = array_merge($team_data['pl'],$sell_data);
			$team_data['pl'] = array_column($team_data['pl'],NULL,"stock_id");
			
        }else{

			$team_data['pl'] = $this->get_rendered_stock_array($team_data['b'],1);
			$sell_data = $this->get_rendered_stock_array($team_data['s'],2);
			$team_data['pl'] = array_merge($team_data['pl'],$sell_data);
			$team_data['pl'] = array_column($team_data['pl'],NULL,"stock_id");
			
        }
        $final_stock_list = array();
       
        if(!empty($team_data['pl'])){
            $lineup_total = $contest_info['remaining_cap'];
            foreach ($team_data['pl'] as $stock_id=> $stock_data) {
                $stock_info = $stock_list_array[$stock_id];
                if(!empty($stock_info)) {
                    $lineup = array();
                    $lineup['result_amount'] = $stock_info['result_rate']*$stock_data['user_lot_size'];
                    $captain = 0;
                    if($stock_id == $team_data['c_id']){
                        $captain = 1;
                        $lineup['result_amount'] = $lineup['result_amount']*CAPTAIN_POINT;
                    }

                    if(isset($team_data['vc_id']) && $stock_id == $team_data['vc_id']){
                        $captain = 2;
                        $lineup['result_amount'] = $lineup['result_amount']*VICE_CAPTAIN_POINT;
                    }
                    $lineup['stock_id'] = $stock_info['stock_id'];
                    $lineup['stock_name'] = $stock_info['stock_name'];
                    $lineup['logo'] = $stock_info['logo'];
                    $lineup['lot_size'] = $stock_info['lot_size'];
                    $lineup['display_name'] = $stock_info['display_name'];
                    $lineup['captain'] = $captain;
                    $lineup['score'] = $stock_data['score'];
                    $lineup['type'] = $stock_data['type'];
                    $lineup['user_lot_size'] = $stock_data['user_lot_size'];
                    $lineup['joining_rate'] = $stock_info['joining_rate'];
                    $lineup['result_rate'] = $stock_info['result_rate'];
                    $lineup['closing_rate'] = $stock_info['closing_rate'];
                    
                    $lineup['user_invested_amount'] = $stock_info['joining_rate']*$stock_data['user_lot_size'];
					$lineup_total+=$lineup['result_amount'];
                    $final_stock_list[] = $lineup;
                }
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['team_detail_not_found'];
            $this->api_response();
        }

      
		
        $prize_data = json_decode($contest_info['prize_data'],TRUE);

        $salary_cap = $this->Contest_model->get_stock_price_cap('salary_cap',2);
		$lineup_result_temp = array(
            'prize_data'=>$prize_data,
            'game_rank'=>$contest_info['game_rank'],
            'lineup_master_id'=>$contest_info['lineup_master_id'],
            'score'=>$contest_info['total_score'],
            "user_name"=>$contest_info['user_name'],
            "team_name"=>$contest_info['team_name'],
            "is_winner"=>$contest_info['is_winner'],
            "lineup_total" => $lineup_total,
            "lineup"=>array());
		$lineup_result_temp['lineup'] = $final_stock_list;
		$lineup_result_temp['salary_cap'] = $salary_cap;

		$this->api_response_arry['data'] = $lineup_result_temp;
		$this->api_response();		
	}

    private function get_rendered_stock_array($arr,$type)
	{
		$tmp = array();
		foreach($arr as $stock_id => $user_lot_size)	{
			$tmp[] = array('stock_id' => $stock_id,'user_lot_size' => $user_lot_size,'type' => $type );
	  }
	  return $tmp;
	}

	private function get_rendered_stock_complted_fixture($arr,$type)
	{
		$tmp = array();
		foreach($arr as $stock_id => $score_data)	{
			$tmp[] = array('stock_id' => $stock_id,'user_lot_size' => $score_data['user_lot_size'],'score' => $score_data['score'],'type' => $type );
	  }
	  return $tmp;
	}

    /**
     * Get Industry list for stock
     * @return industry_id, display_name 
     */
    public function get_industry_list_post()
    {   $this->load->model('Stock_model');
        $industry_list= $this->Stock_model->get_industry_list();
        $this->api_response_arry['data']['industry_list'] = $industry_list;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    }
}
