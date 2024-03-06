<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends Common_Api_Controller{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/Report_model');
	}

	/**
     * Used for get user report list 
     * @param array $post_data
     * @return json array
     */   
	public function get_user_report_post()
	{
        $post_data = $this->input->post();
        $post_data['csv'] = false;
		$result = $this->Report_model->get_user_report($post_data);
        if(!empty($result['result'])){
            $report_result = array_column($result['result'],NULL,"user_id");
            $user_ids = array_keys($report_result);
            $this->load->model('user/User_model');
            $user_list = $this->User_model->get_user_detail_by_user_id($user_ids);
            if(!empty($user_list)){
                $user_list = array_column($user_list,NULL,"user_id");
                $report_result = array_replace_recursive($report_result,$user_list);
            }
            $result['result'] = array_values($report_result);
        }
        $result['real_cash'] = $this->props_config['real_cash'];
        $result['coins'] = $this->props_config['coins'];
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

    /**
    * Function used for export user report in csv file
    * @param array $post_data
    * @return array
    */
    public function get_user_report_get()
    {
        try{
            $_POST = $this->input->get();
            $post_data = $_POST;
            $post_data['csv'] = true;
            $result = $this->Report_model->get_user_report($post_data);
            if(!empty($result['result'])){
                $report_result = array_column($result['result'],NULL,"user_id");
                $user_ids = array_keys($report_result);
                $this->load->model('user/User_model');
                $user_list = $this->User_model->get_user_detail_by_user_id($user_ids);
                $report_arr = array();
                if(!empty($user_list)){
                    $user_list = array_column($user_list,NULL,"user_id");
                    foreach($report_result as &$row){
                        if(isset($user_list[$row['user_id']])){
                            $row['user_name'] = $user_list[$row['user_id']]['user_name'];
                        }
                        unset($row['user_id']);
                        $tmp_arr = array();
                        $tmp_arr['user_name'] = $row['user_name'];
                        $tmp_arr['total_entries'] = $row['total_team'];
                        if($this->props_config['real_cash'] == 1){
                            $tmp_arr['total_stake_real'] = $row['real_entry'];
                            $tmp_arr['total_winning_real'] = $row['real_winning'];
                            $tmp_arr['operator_profit_real'] = $row['real_profit'];
                        }
                        if($this->props_config['coins'] == 1){
                            $tmp_arr['total_stake_coin'] = $row['coin_entry'];
                            $tmp_arr['total_winning_coin'] = $row['coin_winning'];
                            $tmp_arr['operator_profit_coin'] = $row['coin_profit'];
                        }
                        $tmp_arr['winning_limit'] = $row['winning_cap'];
                        $tmp_arr['status'] = $row['user_status'];
                        $report_arr[] = $tmp_arr;
                    }
                }

                $header = array_keys($report_arr[0]);
                $camelCaseHeader = array_map("camelCaseString", $header);
                $report_arr = array_merge(array($camelCaseHeader),$report_arr);
                $this->load->helper('download');
                $this->load->helper('csv');
                $data = array_to_csv($report_arr);
                $data = "Created on ".format_date('today', 'Y-m-d')."\n\n".html_entity_decode($data);
                $name = 'Props_User_Report.csv';
                force_download($name, $data);
            }
            else{
               $result = "no record found";
               $this->load->helper('download');
               $this->load->helper('csv');
               $data = array_to_csv($result);
               $name = 'Props_User_Report.csv';
               force_download($name, $result);
            }
        }catch(Exception $e)
        {
            $this->api_response_arry['global_error'] = "some error";
            $this->api_response();
        }
    }

    /**
    * Function used for update user playing status
    * @param int $user_id
    * @return array
    */
    public function update_user_status_post()
    {
        $this->form_validation->set_rules('user_id', 'user id', 'trim|required');
        $this->form_validation->set_rules('note', 'note', 'trim');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $user_id = $post_data['user_id'];
        $note = isset($post_data['note']) ? $post_data['note'] : "";
        $record_info = $this->Report_model->get_single_row('id,user_id,status',USER_SETTING,array('user_id' => trim($user_id)));
        $status = 1;
        if(empty($record_info) || (!empty($record_info) && $record_info['status'] == 1)){
            $status = 0;
        }
        if(empty($record_info)){
            $data_arr = array();
            $data_arr['user_id'] = $user_id;
            $data_arr['winning_cap'] = "0";
            $data_arr['status'] = $status;
            $data_arr['note'] = $note;
            $data_arr['added_date'] = format_date();
            $data_arr['modified_date'] = format_date();
            $result = $this->Report_model->save_record(USER_SETTING,$data_arr);
        }else{
            $data_arr = array();
            $data_arr['status'] = $status;
            $data_arr['note'] = $note;
            $data_arr['modified_date'] = format_date();
            $result = $this->Report_model->update(USER_SETTING,$data_arr,array("user_id"=>$user_id));
        }
        if(!$result){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("user_status_error");
            $this->api_response();
        }

        $this->api_response_arry['message'] = $this->lang->line("user_status_success");
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

    /**
    * Function used for update user winning limit
    * @param int $user_id
    * @param float $winning_cap
    * @return array
    */
    public function update_user_limit_post()
    {
        $this->form_validation->set_rules('user_id', 'user id', 'trim|required');
        $this->form_validation->set_rules('winning_cap', 'winning cap', 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $user_id = $post_data['user_id'];
        $winning_cap = $post_data['winning_cap'];
        $record_info = $this->Report_model->get_single_row('id,user_id,status',USER_SETTING,array('user_id' => trim($user_id)));
        if(empty($record_info)){
            $data_arr = array();
            $data_arr['user_id'] = $user_id;
            $data_arr['winning_cap'] = $winning_cap;
            $data_arr['status'] = 1;
            $data_arr['added_date'] = format_date();
            $data_arr['modified_date'] = format_date();
            $result = $this->Report_model->save_record(USER_SETTING,$data_arr);
        }else{
            $data_arr = array();
            $data_arr['winning_cap'] = $winning_cap;
            $data_arr['modified_date'] = format_date();
            $result = $this->Report_model->update(USER_SETTING,$data_arr,array("user_id"=>$user_id));
        }
        if(!$result){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("user_winning_limit_error");
            $this->api_response();
        }

        $this->api_response_arry['message'] = $this->lang->line("user_winning_limit_success");
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }
}