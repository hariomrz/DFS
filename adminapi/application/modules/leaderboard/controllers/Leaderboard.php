<?php
/**
 * Leaderboard for all type of leaderboard
 * @package Leaderboard
 * @category Leaderboard
 */
class Leaderboard extends MYREST_Controller 
{

	public $prize_type_arr = array("2"=>"Weekly","3"=>"Monthly","4"=>"League");
    function __construct()
    {
        parent::__construct();

        $this->load->model('Leaderboard_model');
    }

    /**
     * get master tada of all leaderboards
     */
    public function get_master_data_post()
	{
		$type_arr['1'] = $this->prize_type_arr;
		$type_arr['2'] = $this->prize_type_arr;
        $type_arr['3'] = $this->prize_type_arr;
        $type_arr['4'] = $this->prize_type_arr;
        $type_arr['5'] = $this->prize_type_arr;
        $type_arr['6'] = $this->prize_type_arr;
		unset($type_arr['1']['4'], $type_arr['1']['2']);
        unset($type_arr['2']['2'], $type_arr['3']['4'], $type_arr['4']['4'],$type_arr['5']['4'],$type_arr['6']['4']);

		$leaderboard = $this->get_leaderboard_type_list();
        $asf  = isset($this->app_config['allow_stock_fantasy'])?$this->app_config['allow_stock_fantasy']['key_value']:0;
        $a_equity  = isset($this->app_config['allow_equity'])?$this->app_config['allow_equity']['key_value']:0;
        $a_predict  = isset($this->app_config['allow_stock_predict'])?$this->app_config['allow_stock_predict']['key_value']:0;
        $a_live_stock_fantasy  = isset($this->app_config['allow_live_stock_fantasy'])?$this->app_config['allow_live_stock_fantasy']['key_value']:0;
		
        foreach($leaderboard as $key=>&$row){
          
			$row['type'] = isset($type_arr[$row['category_id']]) ? $type_arr[$row['category_id']] : $type_arr['1'];
            if(($row['category_id'] == 3 && $asf==0) || ($row['category_id'] == 4 && $a_equity==0 ) || ($row['category_id'] == 5 && $a_predict==0 ) || ($row['category_id'] == 6 && $a_live_stock_fantasy==0 ))  {
                unset($leaderboard[$key]);
            }
		}
		$result = array();
		$result['leaderboard'] = $leaderboard;
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}
    
    /**
     * save method to save the new prize as well to update the prizes
     * @paramcategory_id,type,allow_prize,referance_id, league_name,prize_detail
     */
	public function save_prizes_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('category_id', 'Category', 'trim|required');
        $this->form_validation->set_rules('type', 'Type', 'trim|required');
        $this->form_validation->set_rules('allow_prize', 'Allow Prize', 'trim|required');
        if(isset($post_data['type']) && $post_data['type'] == "4"){
        	$this->form_validation->set_rules('reference_id', 'League id', 'trim|required');
        	$this->form_validation->set_rules('league_name', 'League name', 'trim|required');
        }
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $reference_id = isset($post_data['reference_id']) ? $post_data['reference_id'] : "0";
        $prize_id = isset($post_data['prize_id']) ? $post_data['prize_id'] : "";
        $check_exist = $this->Leaderboard_model->get_single_row('prize_id',LEADERBOARD_PRIZE,array('category_id'=> $post_data['category_id'],'type'=> $post_data['type'],'reference_id'=> $reference_id));
        if(!empty($check_exist) && $check_exist['prize_id'] != $prize_id){
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "Sorry!, You have already save prize for this criteria.";
            $this->api_response();
        }

        $prize_name = $this->prize_type_arr[$post_data['type']];
        if($post_data['type'] == 4){
            $prize_name = $post_data['league_name'];
            $custom_data = json_encode(["league_id"=>$post_data['reference_id'], "league_name"=>$prize_name]);
        }
        $data = array();
        $data['category_id'] = $post_data['category_id'];
        $data['type'] = $post_data['type'];
        $data['reference_id'] = isset($post_data['reference_id']) ? $post_data['reference_id'] : 0;
        $data['name'] = $prize_name;
        $data['status'] = 1;
        $data['allow_prize'] = $post_data['allow_prize'];
        $data['prize_detail'] = null;
        $data['custom_data'] = $custom_data;
        if(isset($post_data['allow_prize']) && $post_data['allow_prize'] == "1")
        {
            if(empty($post_data['prize_detail']))
            {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = "Please provide prize distribution details.";
                $this->api_response();
            }
            else
            {
                $data['prize_detail'] = json_encode($post_data['prize_detail']);
            }
        }

        //update prizes
        if($prize_id != ""){
    		$result = $this->Leaderboard_model->update_prizes($data,array('prize_id' => $post_data['prize_id']));
        }else{
    		$result = $this->Leaderboard_model->save_prizes($data);
        }

        if($result){
            if(isset($post_data['type']) && $post_data['type'] == "4" && !empty($post_data['reference_id'])){
                $this->Leaderboard_model->update_league_data($post_data['reference_id'],1);
            }
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = "Prize details saved successfully.";
            $this->api_response();
        }else{
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "Something went wrong while save prize.";
            $this->api_response();
        }
    }
    
    public function get_leaderboard_list_post()
    {
        $leaderboards = $this->Leaderboard_model->get_leaderboard_list($this->input->post());
        if(!$leaderboards)
        {
            $this->api_response_arry['global_error'] = "No record Found.";
            $this->api_response();
        }
        foreach($leaderboards['result'] as $key=>$value)
        {
            $leaderboards['result'][$key]['prize_detail'] = $value['prize_detail'] ? json_decode($value['prize_detail'],true):array();
            $leaderboards['result'][$key]['custom_data'] = $value['custom_data'] ? json_decode($value['custom_data'],true):array();
        }
        $this->api_response_arry['data'] = $leaderboards;
        $this->api_response_arry['message'] = "Get Leaderboards List.";
        $this->api_response();
    }

    public function change_leaderboard_status_post(){
        $this->form_validation->set_rules('prize_id', 'Prize Id', 'trim|required');
        $this->form_validation->set_rules('status', 'Status', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $toggle_status = $this->Leaderboard_model->change_leaderboard_status($this->input->post());
        if(!$toggle_status)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "No Change.";
            $this->api_response();
        }
        $this->api_response_arry['message'] = "Status changes Successfully";
        $this->api_response();
    }

	/**
	 * get leaderboard prize detail for leaderboard detail page
	 */
     public function get_leaderboard_prize_details_post()
    {
        $this->form_validation->set_rules('prize_id', 'Prize Id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $lead_details = $this->Leaderboard_model->get_leaderboard_prize_details($this->input->post('prize_id'));
        if(!$lead_details)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['global_error'] = "Something went wrong while fetching details.";
            $this->api_response();
        }
        $lead_details['prize_data_master'] = $lead_details['prize_data_master'] ? json_decode($lead_details['prize_data_master'],true):array();
        foreach($lead_details['leaderboard'] as $key=>$leaderboard)
		{
			$lead_details['leaderboard'][$key]['prize_detail'] = json_decode($leaderboard['prize_detail'],true);
		}
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data'] = $lead_details;
        $this->api_response();
    }

    /**
     * to get record of users participated and goes under ranking.
     */
    public function get_leaderboard_user_list_post()
    {
        $this->form_validation->set_rules('leaderboard_id', 'Leaderboard Id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $lead_details = $this->Leaderboard_model->get_leaderboard_user_list($this->input->post());
        if(!$lead_details['result'])
        {
            $this->api_response_arry['global_error'] = "Right now, no user available.";
            $this->api_response();
        }
        foreach($lead_details['result'] as $key=>$res)
		{
            $res['prize_data'] = json_decode($res['prize_data'],true);
			$lead_details['result'][$key]['prize_data'] = $res['prize_data'] ? array($res['prize_data']):array();
        }
        
        $this->api_response_arry['data'] = $lead_details;
        $this->api_response();
    }

    /**
     * method to get all live and upcomming leagues
     */
    public function get_live_upcomming_leagues_post()
	{
		$this->form_validation->set_rules('sports_id', 'Sports Id', 'trim');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
		$post_api_response       = $this->Leaderboard_model->get_sport_leagues($this->input->post());
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data'] = $post_api_response;
		$this->api_response();
    }
    
    /**
     * method to get detail of a single leaderboard in case of edit
     */
    public function get_prize_detail_post(){
        $this->form_validation->set_rules('prize_id', 'Prize Id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $prizes = $this->Leaderboard_model->get_prize_detail($this->input->post('prize_id'));
        if(!$prizes)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "Something went wrong while fetching prizes.";
            $this->api_response();
        }

        $this->api_response_arry['data'] = $prizes;
        $this->api_response();
    }

    /**
    * Function used for update league leaderboard status
    * @param int $prize_id
    * @return string
    */
    public function mark_complete_post(){
        $this->form_validation->set_rules('prize_id', 'Prize Id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();
        $current_date = format_date();
        $prize_info = $this->Leaderboard_model->get_league_leaderboard_details($post_data['prize_id']);
        if(empty($prize_info)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Leaderboard details not found.";
            $this->api_response();
        }
        if(isset($prize_info['type']) && $prize_info['type'] != "4"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "You can mark complete only league leaderboard.";
            $this->api_response();
        }
        if(isset($prize_info['status']) && $prize_info['status'] == "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "You can't mark complete to cancelled Leaderboard.";
            $this->api_response();
        }
        if(isset($prize_info['is_complete']) && $prize_info['is_complete'] == "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Leaderboard already completed.";
            $this->api_response();
        }
        if($prize_info['end_date'] == "" || strtotime($prize_info['end_date']) > strtotime($current_date)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "You can't mark complete before league end date.";
            $this->api_response();
        }
        $result = $this->Leaderboard_model->mark_complete_leaderboard($post_data['prize_id']);
        if(!$result)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "Status already updated.";
            $this->api_response();
        }
        $this->api_response_arry['message'] = "League complete status updated successfully.";
        $this->api_response();
    }

    /**
    * Function used for update league leaderboard status
    * @param int $prize_id
    * @return string
    */
    public function mark_cancel_post(){
        $this->form_validation->set_rules('prize_id', 'Prize Id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();
        $current_date = format_date();
        $prize_info = $this->Leaderboard_model->get_league_leaderboard_details($post_data['prize_id']);
        if(empty($prize_info)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Leaderboard details not found.";
            $this->api_response();
        }
        if(isset($prize_info['type']) && $prize_info['type'] != "4"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "You can mark cancel only league leaderboard.";
            $this->api_response();
        }
        if(isset($prize_info['is_complete']) && $prize_info['is_complete'] == "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "You can't mark cancel to completed Leaderboard.";
            $this->api_response();
        }
        if(isset($prize_info['status']) && $prize_info['status'] > "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Leaderboard already closed, you can't cancel it.";
            $this->api_response();
        }
        $result = $this->Leaderboard_model->mark_cancel_leaderboard($prize_info['leaderboard_id'],$post_data['prize_id']);
        if(!$result)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "Status already updated.";
            $this->api_response();
        }

        if(isset($prize_info['type']) && $prize_info['type'] == "4" && !empty($prize_info['reference_id'])){
            $this->Leaderboard_model->update_league_data($post_data['reference_id'],0);
        }
        $this->api_response_arry['message'] = "Leaderboard has been cancelled successfully.";
        $this->api_response();
    }


}