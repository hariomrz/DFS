<?php
/**
 * Leaderboard for all type of leaderboard
 * @package Leaderboard
 * @category Leaderboard
 */
class Leaderboard extends Common_Api_Controller 
{
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