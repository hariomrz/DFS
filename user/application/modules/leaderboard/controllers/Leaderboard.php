<?php
/**
 * Leaderboard for all type of leaderboard
 * @package Leaderboard
 * @category Leaderboard
 */
class Leaderboard extends Common_Api_Controller 
{
	public $referral_category = REFERRAL_LEADERBOARD_ID;
 	public $fantasy_category = FANTASY_LEADERBOARD_ID;
	public $stock_category = STOCK_LEADERBOARD_ID;
	public $stock_equity_category = STOCK_EQUITY_LEADERBOARD_ID;
	public $stock_predict_category = STOCK_PREDICT_LEADERBOARD_ID;
	public $live_stock_fantasy_category = LIVE_STOCK_FANTASY_LEADERBOARD_ID;
	public $prize_type_arr = array("2"=>"Weekly","3"=>"Monthly","4"=>"League");
    function __construct()
    {
        parent::__construct();

    }

    /**
	* Function used for get master data of leaderboard page
	* @param void
	* @return string
	*/
    public function get_leaderboard_master_post()
	{
		$this->load->model('Leaderboard_model');
		$leaderboard = $this->get_leaderboard_type_list();
		foreach($leaderboard as $key=>&$row){
			$asf  = isset($this->app_config['allow_stock_fantasy'])?$this->app_config['allow_stock_fantasy']['key_value']:0;
            $a_equity  = isset($this->app_config['allow_equity'])?$this->app_config['allow_equity']['key_value']:0;
            $a_predict  = isset($this->app_config['allow_stock_predict'])?$this->app_config['allow_stock_predict']['key_value']:0;

            if(($row['category_id'] == 3 && $asf==0) || ($row['category_id'] == 4 && $a_equity==0 ) || ($row['category_id'] == 5 && $a_predict==0 ))  {
            	unset($leaderboard[$key]);
            }

			$row['league'] = array();
			$input_arr = array('category_id'=>$row['category_id'],'limit'=>'3','type' => 3);
			if(in_array($row['category_id'], array($this->stock_category, $this->stock_equity_category,$this->stock_predict_category,$this->live_stock_fantasy_category))) {
				$row['leaderboard']['monthly'] = $this->Leaderboard_model->get_category_leaderboard($input_arr);
				
				$input_arr['type'] = 2;
				$row['leaderboard']['weekly'] = $this->Leaderboard_model->get_category_leaderboard($input_arr);
			} else {
				$row['leaderboard'] = $this->Leaderboard_model->get_category_leaderboard($input_arr);
				if($row['category_id'] == $this->fantasy_category){
					$input_arr = array('category_id'=>$row['category_id'],'type' => 4);
					$row['league'] = $this->Leaderboard_model->get_category_leaderboard($input_arr);
				}
			}
			
		}
		$this->api_response_arry['data'] = $leaderboard;
		$this->api_response();
	}

	/**
	* Function used for get leaderboard users list
	* @param int $leaderboard_id
	* @return string
	*/
	public function get_leaderboard_list_post()
	{
		$this->form_validation->set_rules('leaderboard_id', "leaderboard_id", 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->post();
		$this->load->model('Leaderboard_model');
		$result = array();
		$detail = array();
		if(!isset($post_data['page_no']) || $post_data['page_no'] == "1"){
			$detail = $this->Leaderboard_model->get_leaderboard_details($post_data['leaderboard_id']);
			if(!empty($detail)){
				$detail['prize_detail'] = json_decode($detail['prize_detail'],TRUE);
				if($detail['category_id'] == $this->fantasy_category && $detail['type'] == "3"){
		            $detail['name'] = "Fantasy Leaderboard";
		        }
			}
		}
		$result['detail'] = $detail;
		$result['list'] = $this->Leaderboard_model->get_leaderboard_list($post_data);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
	* Function used for get user points details
	* @param int $leaderboard_id
	* @return string
	*/
	public function get_user_leaderboard_detail_post()
	{
		$this->form_validation->set_rules('history_id', "id", 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->post();
		$this->load->model('Leaderboard_model');
		$record_info = $this->Leaderboard_model->get_user_leaderboard_detail($post_data['history_id']);
		$result = array();
		if(!empty($record_info)){
			$tmp_arr = array();
			if($record_info['type'] == "4"){
				$tmp_arr['league_id'] = $record_info['entity_no'];
			}
			$tmp_arr['start_date'] = $record_info['start_date'];
			$tmp_arr['end_date'] = $record_info['end_date'];
			$tmp_arr['custom_data'] = json_decode($record_info['custom_data'],TRUE);
			$match_list = array();
			if(!empty($tmp_arr['custom_data'])){
				$match_list = $this->Leaderboard_model->get_user_match_list($tmp_arr);
			}

			$result['user_name'] = $record_info['user_name'];
			$result['image'] = $record_info['image'];
			$result['total'] = count($match_list);
			$result['match'] = $match_list;
		}
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}
}