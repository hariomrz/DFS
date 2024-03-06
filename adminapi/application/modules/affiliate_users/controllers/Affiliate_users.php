<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Affiliate_users extends MYREST_Controller{

	public function __construct()
	{
        parent::__construct();
        $affiliate_module = isset($this->app_config['affiliate_module'])?$this->app_config['affiliate_module']['key_value']:0;
        
        if(!$affiliate_module){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['status'] = FALSE;
                $this->api_response_arry['message'] = $this->admin_lang["aff_module_enable"];
                $this->api_response();
        }
        $this->admin_lang = $this->lang->line('affiliate');
		$_POST = $this->input->post();
		$this->load->model('Affiliate_users_model','aum');
		//Do your magic here
    }
    /**  method will update status of user as well commission and other values , also affiliate date in case when it is going to become an affiliate.
     * @param : user_id;
     * @return : message
     * 
    */
    public function update_affiliate_post(){
        $this->form_validation->set_rules('is_affiliate', 'Affiliate Status', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        
        if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }
        
        $is_affiliate_status = array(1,2,3,4);
        $post_data = $this->input->post();
      
        if(in_array($post_data['is_affiliate'],$is_affiliate_status)){
            $result = $this->aum->update_affiliate();
            if($result){
                $user_profile_cache_key = "user_profile_" . $post_data['user_id'];
                $this->delete_cache_data($user_profile_cache_key);
                if($post_data['is_affiliate']==1){
                    $user_details = $this->aum->get_single_row ('user_name,referral_code,email,', USER, $where = ["user_id"=>$post_data['user_id']]);
                    /* Send Email Notifications*/

                    $content = array(
                        "email" => $user_details['email'],
                        "username" => $user_details['user_name'],
                        "aff_link"  => WEBSITE_URL."signup?affcd=".$user_details['referral_code'],
                        "post_date" => format_date('today', 'd-M-Y h:i A'),
                        "banner_image" => ''
                    );
                    $subject = "Affiliate Request Accepted";
                    $today 	= format_date();
                    $email_content                     	= array();
                    $email_content['to']            	= $user_details['email'];
                    $email_content['user_name']         = $user_details['user_name'];
                    $email_content['subject'] 			= $subject;
                    $email_content['notification_type'] = 422;
                    $email_content['content']           = $content;
                    $email_content['user_id']           = $post_data['user_id'];
                    $email_content['source']           = 422;
                    $email_content["added_date"] 		= $today;
				    $email_content["modified_date"] 	= $today;
                    $email_content['notification_destination'] = 6;  
                    $email_content['custom_notification_subject'] = '🥳 Congratulations 🥳';
                    $email_content['custom_notification_text'] = 'You are an Affiliate now. Welcome to the team. See what you got in store for you. 💰💰';
                    
                    $this->load->model(array('notification/Notification_model', 'notification/Notify_nosql_model'));
                    $device_ids = $this->Notification_model->get_all_device_id(array($post_data['user_id']));
                    
                    $android_device_ids = $ios_device_ids = array();
                    foreach($device_ids as $device_detail )
                    {
                        if(isset($device_detail['device_type']) && $device_detail['device_type']=='1' )
                        {
                            $android_device_ids[] = $device_detail['device_id'];
                        }

                        if(isset($device_detail['device_type']) && $device_detail['device_type']=='2' )
                        {
                            $ios_device_ids[] = $device_detail['device_id'];
                        }
                    }

                    $email_content['device_ids'] = $android_device_ids; 
                    $email_content['ios_device_ids'] = $ios_device_ids; 

                    $this->Notify_nosql_model->send_notification($email_content);

                    //$this->load->helper('queue_helper');
                   // $email_content['email']            	= $user_details['email'];
                   // add_data_in_queue($email_content, 'email');
                }
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['status']          = TRUE;
                    $this->api_response_arry['message']         = $this->admin_lang["update_aff_success"];
            }
            else{
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['status']          = FALSE;
                $this->api_response_arry['global_error']         = $this->admin_lang["update_aff_error"];
            }            
        }
        else{
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['global_error']         = $this->admin_lang["update_aff_error"];
        }

        $this->api_response();
    }
    /**
     * Method is used to get pending affiliate request list
     * @param :  n/a
     * @return : array()
     */
    public function get_pending_affiliate_post(){
        $result = $this->aum->get_pending_affiliate_request();
        if($result){
            $this->api_response_arry['data']          = $result;
            $this->api_response_arry['message']         = $this->admin_lang["aff_pending_rec_get"];
        }
        else{
            $this->api_response_arry['data']          = array();
            $this->api_response_arry['message']         = $this->admin_lang["aff_pending_rec_not_get"];
        }
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response();
    }


    /**
     * Method is used to search users by username or mobile number or email
     * @param :  string
     * @return : array()
     */
    public function users_post()
	{
        $this->form_validation->set_rules('keyword', 'Search word', 'trim|required');
        $this->form_validation->set_rules('action', 'Action', 'trim|required|in_list[1,2,3]'); // 1: search, 2 : verify, 3 :update
        
        if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }
        $result = $this->aum->get_users();
        //$result['affiliate_url']='';
        if(!empty($result)){
            foreach($result as $key=>$res){
                $result[$key]['affiliate_url']= WEBSITE_URL.'signup?affcd='.$res['referral_code'];
            }
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            $this->api_response_arry['message']         = $this->admin_lang["user_success"];
            $this->api_response_arry['data']  			= $result[0];
        }
        else{
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = $this->admin_lang["no_user"];
        }
        $this->api_response();
	}
    /**
     * Method is used to get transaction records of a single user or all users  
     * @param :  user_id
     * @return : array()
     */
    public function get_affiliate_records_post(){
        $result = $this->aum->get_affiliate_records();
        if($result){
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['status']          = TRUE;
            $this->api_response_arry['data']  			= $result;
            $this->api_response_arry['message']         =  $this->admin_lang["aff_tr_rec_success"];
        }
        else{
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['data']  			= array();
            $this->api_response_arry['message']    = $this->admin_lang["aff_tr_rec_error"];
        }
        $this->api_response();
    }
     /**
     * Method is used to get transaction download
     * @param :  user_id
     * @return : array()
     */
    public function get_affiliate_records_get(){
        $_POST = $this->input->get();
        $_POST["csv"] = TRUE;
        $result = $this->aum->get_affiliate_records();
        if($result){
            $result =$result['result'];
				$header = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('download');
                                $this->load->helper('csv');
                                $data = array_to_csv($result);

                                //$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
                                $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
                                $name = 'Affiliate_report.csv';
                                force_download($name, $data);
        }
        else{

            $header = "No record found";
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('download');
                                $this->load->helper('csv');
                                $data = array_to_csv($result);

                                //$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
                                $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
                                $name = 'Affiliate_report.csv';
                                force_download($name, $data);
        }
    }
     /**
      * Method si used to get values to represent a graph
      * @param : user_id;
      *@return : array();
      */
    public function get_commission_graph_post(){

        $result = $this->aum->get_commission_graph();
        if($result){
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['status']          = TRUE;
            $this->api_response_arry['data']['series_data']		= $result[0];
            $this->api_response_arry['message']         = $this->admin_lang["aff_pai_success"];
        }
        else{
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['data']  			= array();
            $this->api_response_arry['global_error']    = $this->admin_lang["aff_pai_error"];
        }
        $this->api_response();
    }

    function get_signup_graph_post()
    {
        $post = $this->input->post();
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            if(isset($post['user_id'])){
                $reg_date = $this->aum->get_single_row ('added_date', USER, $where = ["user_id"=>$post['user_id']]);
                $post['from_date'] = date('Y-m-d',strtotime($reg_date['added_date']));
            }else{
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -60 days'));
            }
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }
        $Dates = get_dates_from_range($post['from_date'], $post['to_date'],'Y-m-d');
        $Dates_show = get_dates_from_range($post['from_date'], $post['to_date'],'d M');
        
        $result =  $this->aum->get_signup_graph($post);
        $data = array();
        $data['deposit_data']=0;
        $str_date = array();
        $total_signup = $result['total'];
        if(!$result['result']){ 
            $result['result'] = array(
            "signup"=>0,
            "date_added"=>date('Y-m-d',strtotime($post['to_date']))
            );
        }
            
        foreach($Dates as $oneDate){
            $date = strtotime($oneDate);
            $str_date[]=$date;
            $data['signup_data'][$date] = 0;
            foreach($result['result'] as $row){
                if(isset($row['date_added']))
                {
                $main_date = strtotime($row['date_added']);
                }else{
                $main_date = strtotime(format_date("today"));
                }
                if(in_array($main_date,$str_date))
               {
                    $data['signup_data'][$main_date] = $row['signup'];
               }
            }
        }
        if(!empty($data['signup_data']))
       {
           $data['signup_data'] = array_values($data['signup_data']);
           foreach($data['signup_data'] as &$val)
           {
               $val = (int)$val;
               
            }
        }

        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['data']['series'] = array('data'=>$data['signup_data']);
        $this->api_response_arry['data']['dates'] 	= $Dates_show;
        $this->api_response_arry['data']['total_signup'] 	= $total_signup;
        $this->api_response_arry['data']['currency'] 	= CURRENCY_CODE;
        $this->api_response(); 
    }

    function get_deposit_graph_post()
    {
        $post = $this->input->post();
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            if(isset($post['user_id'])){
                $reg_date = $this->aum->get_single_row ('added_date', USER, $where = ["user_id"=>$post['user_id']]);
                $post['from_date'] = date('Y-m-d',strtotime($reg_date['added_date']));
            }else{
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -60 days'));
            }
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }
        $Dates = get_dates_from_range($post['from_date'], $post['to_date'],'Y-m-d');
        $Dates_show = get_dates_from_range($post['from_date'], $post['to_date'],'d M');
        $result =  $this->aum->get_deposit_graph($post);
        $data = array();
        // $data['deposit_data']=0;
        $str_date = array();
        $total_deposit = 0;
        if(!$result['result']){ 
            $result['result'] = array(
            "deposit"=>0,
            "deposit_date"=>date('Y-m-d',strtotime($post['to_date']))
            );
        }
        foreach($Dates as $oneDate){
            $date = strtotime($oneDate);
            $str_date[]=$date;
            $data['deposit_data'][$date] = 0;
            foreach($result['result'] as $row){
                if(isset($row['deposit_date']))
                {
                $main_date = strtotime($row['deposit_date']);
                }else{
                $main_date = strtotime(format_date("today"));
                }
                if(in_array($main_date,$str_date))
               {
                    $data['deposit_data'][$main_date] = $row['deposit'];
               }
            }
        }
        if(!empty($data['deposit_data']))
       {
           $data['deposit_data'] = array_values($data['deposit_data']);
           foreach($data['deposit_data'] as &$val)
           {
               $val = (int)$val;
            }
        }
            
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['data']['series'] = array('data'=>$data['deposit_data']);
        $this->api_response_arry['data']['total_deposit'] 	= $total_deposit;
        $this->api_response_arry['data']['currency'] 	= CURRENCY_CODE;
        $this->api_response_arry['data']['dates'] 	= $Dates_show;
        $this->api_response(); 
    }

    /**
     * method to get exported list of affiliate
     * @param fromdate 
     * @param todate
     */
    public function get_affilicate_list_get(){
        $_POST = $this->input->get();
        if(!isset($_POST['csv'])) {
            $_POST['csv'] = 1;
        } 
        
        $result = $this->aum->get_pending_affiliate_request();
        $this->load->helper(['download','csv']);
        if(empty($result))
        {
            $result[0] = ["Note : "=>"No Affiliate found"];
            
        }
            $header = array_keys($result[0]);
            $camelCaseHeader = array_map("camelCaseString",$header);
            $result = array_merge(array($camelCaseHeader),$result);
            $data = array_to_csv($result);
            $data = "created on ".format_date('today')."\n\n".html_entity_decode($data);
            $name = "Affiliate_list.csv";
            force_download($name,$data);
    }	

    /**
     * method to update user site rake commission
     * @param ( user_unique_id, siterakestatus, siterakecommission)     * 
     * 
     */

     public function update_users_rake_post(){
        //    $this->form_validation->set_rules('is_affiliate', 'Affiliate Status', 'trim|required');
        //   $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        $this->form_validation->set_rules('user_unique_id','user unique id', 'trim|required');
        $this->form_validation->set_rules('siterake_status','site rake status','trim|required');
        $this->form_validation->set_rules('siterake_commission', 'site rake commssion', 'trim|required');

        
        if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        } 

        $result =  $this->aum->update_affiliat_rake();

        if ($result) {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['message']         = "Update Successfully.";
            $this->api_response(); 
        }
        
     }

     /**
     * Used for generate Export report
     * @param 
     * @return string
     */
	public function affliate_match_report_post(){
		$post_data = $this->post();
		if(!isset($post_data['start_date']) || ! isset($post_data['end_date']))
		{
			$post_data['start_date_str'] = date('Y-m-d',strtotime(format_date('today'))).' 00:00:00';
			$post_data['end_date_str'] = date('Y-m-d',strtotime(format_date('today'))).' 23:59:59';
			$temp_convert_start = get_timezone(strtotime($post_data['start_date_str']),'Y-m-d H:i:s',$this->app_config['timezone'],1);
			$temp_convert_end = get_timezone(strtotime($post_data['end_date_str']),'Y-m-d H:i:s',$this->app_config['timezone'],1);
			$post_data['start_date'] = $temp_convert_start['date'];
			$post_data['end_date'] = $temp_convert_end['date'];
		}
        $this->benchmark->mark('code_start');
	
        $result = $this->aum->affliate_match_report($post_data);

        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['data']            =  $result;
        $this->api_response_arry['message']         = "";
        $this->api_response(); 
     
	}

    /**
     * Used for generate export affiliate report
     * @param 
     * @return string
     */
	public function affliate_match_report_get(){
		$post_data = $this->get();
		if(!isset($post_data['start_date']) || ! isset($post_data['end_date']))
		{
			$post_data['start_date_str'] = date('Y-m-d',strtotime(format_date('today'))).' 00:00:00';
			$post_data['end_date_str'] = date('Y-m-d',strtotime(format_date('today'))).' 23:59:59';
			$temp_convert_start = get_timezone(strtotime($post_data['start_date_str']),'Y-m-d H:i:s',$this->app_config['timezone'],1);
			$temp_convert_end = get_timezone(strtotime($post_data['end_date_str']),'Y-m-d H:i:s',$this->app_config['timezone'],1);
			$post_data['start_date'] = $temp_convert_start['date'];
			$post_data['end_date'] = $temp_convert_end['date'];
		}
        $this->benchmark->mark('code_start');
	
        $result = $this->aum->affliate_match_report($post_data);

        if(!empty($result['result'])){
				$result =$result['result'];
				$header = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('download');
                                $this->load->helper('csv');
                                $data = array_to_csv($result);

                                //$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
                                $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
                                $name = 'affiliate.csv';
								force_download($name, $data);
			}
			else{
				$result = "no record found";
				$this->load->helper('download');
				$this->load->helper('csv');
				$data = array_to_csv($result);
				$name = 'affiliate.csv';
				force_download($name, $result);

			}

        // $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        // $this->api_response_arry['data']            =  $result;
        // $this->api_response_arry['message']         = "";
        // $this->api_response(); 
     
	}


     /**
     * Used for get contest leaguelist list
     * @param array
     * @return array
     */
    public function get_affiliate_sport_leagues_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $data = $this->aum->get_affiliate_sport_leagues($post_data);

        
        $this->api_response_arry['data'] = $data;
        $this->api_response();
    }

    /**
     * Used for get contest leaguelist list
     * @param array
     * @return array
     */
    public function get_affiliate_match_by_leagues_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        $this->form_validation->set_rules('league_id', 'League id', 'trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $data = $this->aum->get_affiliate_match_by_leagues($post_data);

        
        $this->api_response_arry['data'] = $data;
        $this->api_response();
    }


       /**
     * Used for get contest leaguelist list
     * @param array
     * @return array
     */
    public function get_total_affiliate_site_rake_post()
    {  
       
        $data = $this->aum->get_total_affiliate_site_rake();        
        $this->api_response_arry['data'] = $data;
        $this->api_response();
    }



    /* Location: ./application/controllers/Affiliate_users.php */
}




?>
