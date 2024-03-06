<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Nw_contest extends MYREST_Controller {

    public function __construct() {
        parent::__construct();
        $_POST = $this->input->post();
        $this->load->model('Nw_contest_model');
        $this->admin_lang = $this->lang->line('Contest');
    }

    

    

	public function get_conntest_filter_post(){ 
		$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$post_data = $this->input->post();
		$this->load->model('league/League_model');
		
		$league_list = $this->League_model->get_sport_leagues();
		$this->load->model('common/Common_model');
		
		$post   = $this->post();
		$status_list = array();
		$status_list[] = array("label"=>"Select Status","value"=>"");
		$status_list[] = array("label"=>"Current Contest","value"=>"current_game");
		$status_list[] = array("label"=>"Completed Contest","value"=>"completed_game");
		$status_list[] = array("label"=>"Cancelled Contest","value"=>"cancelled_game");
		$status_list[] = array("label"=>"Upcoming Contest","value"=>"upcoming_game");

		$result = array(
					'league_list'		=> isset($league_list['result']) ? $league_list['result'] : array(),
					'status_list'		=> $status_list
				);

		$this->api_response_arry['data']          = $result;
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response();
	}

	/**
    * [get_all_network_contest]
    * Summary :- get contest list
    */  
	public function get_all_network_contest_post()
	{
		$post_data = $this->input->post();
		$this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$post_data = $this->post();
		$data = $this->Nw_contest_model->get_all_network_contest($post_data);
		$total = $data['total'];
		$contest_list = array();
		
		if(!empty($data['result']))
        {
            foreach($data['result'] as $contest)
            { 
				
				$contest['contest_details'] = json_decode($contest['contest_details']);
				$contest_list[] = $contest;
				
			}
		}
		$data['total'] = $total;
		$data['result'] = $contest_list;
		$this->api_response_arry['data']= $data;
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response();
	}

    /**
    * [publish_network_contest]
    * Summary :- get contest list
    */  
    public function publish_network_contest_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        $this->form_validation->set_rules('league_id', 'league_id id', 'trim|required');
        $this->form_validation->set_rules('network_contest_id', 'Network Contest Id', 'trim|required');
        $this->form_validation->set_rules('id', 'Contest id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->post();
        $current_date = format_date();

        $where_arr = array(
            "league_id" => $post_data['league_id'],
            "sports_id" => $post_data['sports_id'],
            "network_contest_id" => $post_data['network_contest_id'],
            "id" => $post_data['id'],
            "active" => 0,
            "status" => 0

        );


        $contest_detail = $this->Nw_contest_model->get_network_contest_detail($where_arr);
        if(empty($contest_detail))
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']         = "Invalid request!";
            $this->api_response();
        }    
        $nc_detail_arr = json_decode($contest_detail['contest_details'],true);
        //echo "<pre>";print_r($nc_detail_arr);die;
        $collection_check_arr = array(
            "league_id" => $contest_detail['league_id'],
            "season_game_uid" => $nc_detail_arr['season_game_uid'],
            "season_scheduled_date" => $nc_detail_arr['season_scheduled_date']
        );

        //echo "<pre>";print_r($collection_check_arr);die;
        $collection_info = $this->Nw_contest_model->check_collection_exist($collection_check_arr);
        //echo "<pre>";print_r($collection_info);die;
        if(empty($collection_info))
        {
            $collection_insert_array = array();
            $collection_insert_array['league_id'] = $contest_detail['league_id'];
            $collection_insert_array['collection_name'] = $nc_detail_arr['collection_name'];
            $collection_insert_array['collection_salary_cap']=$nc_detail_arr['collection_salary_cap'];
            $collection_insert_array["season_scheduled_date"] = $nc_detail_arr['season_scheduled_date'];
            $collection_insert_array["deadline_time"] = $nc_detail_arr['deadline_time'];
            $collection_insert_array['added_date'] = $current_date;
            $collection_insert_array['modified_date'] = $current_date;
            $collection_master_id = $this->Nw_contest_model->save_network_collection($collection_insert_array,$nc_detail_arr);


        }    
        else
        {
            $collection_master_id = $collection_info['collection_master_id'];
        }    
        
        //publish network contest
        if(!empty($collection_master_id))
        {

            $nc_publish_data_arr = array(

                "active" => 1,
                "collection_master_id"=>$collection_master_id,
                "date_added" => $current_date
            );

            $nc_publish_where_arr = array(
                "league_id" => $post_data['league_id'],
                "sports_id" => $post_data['sports_id'],
                "network_contest_id" => $post_data['network_contest_id'],
                "id" => $post_data['id'],
                "active" => 0,
                "status" => 0
            ); 

            $this->Nw_contest_model->update_network_contest_details($nc_publish_data_arr,$nc_publish_where_arr,$nc_detail_arr);
            if($this->app_config['allow_ngn']['key_value']==1)
            {
                $result = $this->Nw_contest_model->get_notification_details($collection_master_id);
                $nc_detail_arr['home'] = $result['home'];
                $nc_detail_arr['away'] = $result['away'];
                $this->_notify_game_publish($nc_detail_arr,$collection_master_id);
            }

        }

        
        $this->api_response_arry['message']         = "Contest published successfully.";
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    }


    public function get_network_contest_details_post()
    {
    	
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        $this->form_validation->set_rules('contest_unique_id', 'Contest unique id','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

    	$post_data = $this->input->post();
    	$post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/index.php/contest/get_nw_game_detail";
        $api_response =  $this->http_post_request($url,$post_data,3);
        $this->api_response_arry = $api_response;
        $this->api_response();

    }
	

	public function get_network_contest_participants_post()
    {
    	
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        $this->form_validation->set_rules('game_id', 'game id','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

    	$post_data = $this->input->post();
    	$post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/index.php/contest/get_nw_game_lineup_detail";
        $api_response =  $this->http_post_request($url,$post_data,3);
        $this->api_response_arry = $api_response;
        $this->api_response();

    }

    public function get_network_lineup_detail_post()
    {
    	
        $this->form_validation->set_rules('lineup_master_contest_id', 'Lineup master contest id', 'trim|required');
        $this->form_validation->set_rules('league_id', 'league id','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

    	$post_data = $this->input->post();
    	$post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/index.php/contest/get_nw_lineup_detail";
        $api_response =  $this->http_post_request($url,$post_data,3);
        $this->api_response_arry = $api_response;
        $this->api_response();

    }

    public function get_nw_contest_report_filters_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/report/get_contest_report_fitlers";
        $api_response =  $this->http_post_request($url,$post_data,3);
        $this->api_response_arry = $api_response;
        $this->api_response();

    
    }

    public function get_nw_collection_list_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        $this->form_validation->set_rules('league_id', 'League_model id', 'trim|required');
        $this->form_validation->set_rules('collection_type', 'Collection Type', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/contest/get_all_collections_by_league";
        $api_response =  $this->http_post_request($url,$post_data,3);
        $this->api_response_arry = $api_response;
        $this->api_response();

    
    }

     public function get_all_nw_contest_report_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/report/get_all_contest_report";
        $api_response =  $this->http_post_request($url,$post_data,3);
        $this->api_response_arry = $api_response;
        $this->api_response();

    
    }

    public function export_nw_contest_report_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/report/export_report";
        $api_response =  $this->http_post_request($url,$post_data,3);
        $this->api_response_arry = $api_response;
        $this->api_response();

    
    }

    public function export_nw_contest_report_get()
    {
        $post_data = $this->input->get();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/report/export_report";
        $api_response =  $this->http_post_request($url,$post_data,3);
        
        $result = $api_response['data'];
        if(!empty($result))
        {    
            $header = array_keys($result[0]);
                    $camelCaseHeader = array_map("camelCaseString", $header);
                    $result = array_merge(array($camelCaseHeader),$result);
            $this->load->helper('csv');
            array_to_csv($result,'Contest_report_list.csv');
        }    
    
    }


    public function get_contest_commission_history_post()
    {
        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['from_client'] = 1;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/finance/get_client_commission_history";
        $api_response =  $this->http_post_request($url,$post_data,3);
       //echo "<pre>12";print_r(json_encode($api_response));die;
        $this->api_response_arry = $api_response;
        $this->api_response();

    }


    public function get_contest_commission_history_export_get()
    {
        $post_data = $this->input->get();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['from_client'] = 1;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/finance/get_client_commission_history_export";
        $api_response =  $this->http_post_request($url,$post_data,3);
        //echo "<pre>";print_r($api_response['data']['result']);die;
        $result = $api_response['data']['result'];
        $header = array_keys($result[0]);
                $camelCaseHeader = array_map("camelCaseString", $header);
                $result = array_merge(array($camelCaseHeader),$result);
        $this->load->helper('csv');
        array_to_csv($result,'Commission_list.csv');
    }


    public function get_client_all_contest_details_post()
    {
        
        $this->form_validation->set_rules('from_date', 'From date', 'trim|required');
        $this->form_validation->set_rules('to_date', 'To date','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/index.php/user/get_client_all_contest_details";
        $api_response =  $this->http_post_request($url,$post_data,3);
        $this->api_response_arry = $api_response;
        $this->api_response();

    }


    /**
     * sent network game publish push notification
     */
    // public function notify_game_publish_post($data=array()) //use this for debug
    public function _notify_game_publish($data=array(),$collection_master_id)
    {
        if(!empty($data))
        {
            $collection_name = isset($data['collection_name']) ? "of ".$data['collection_name'] : "";
            $notification = $this->Nw_contest_model->get_single_row('en_subject,message',NOTIFICATION_DESCRIPTION,["notification_type"=>440]);
            $notification['message'] = str_replace('{{collection_name}}',$collection_name,$notification['message']);
        }
        $user_ids = $this->Nw_contest_model->get_all_table_data('user_id',USER,array('is_systemuser'=> 0,'status'=>1));
        $user_detail = array();
            if(!empty($user_ids))
				{
					$user_ids =array_unique(array_column($user_ids, 'user_id')) ;
					$user_detail['total_users'] = count($user_ids);
					$user_detail['user_ids'] = $user_ids;
                }

        $device_detail = $this->Nw_contest_model->get_users_device_by_ids($user_ids);
        $new_device_detail = array();
        foreach($device_detail as $key_device => $device)
        {
            $new_device_detail[$device['user_id']] = $device;
        }
        
            $chunks = array_chunk($user_detail['user_ids'], 200);
                foreach($chunks as $key=>$chunk)
                {
                    $notification_data=array();
                    foreach($chunk as $sub_key => $user)
                    {
                        $unique_device_ids = explode(',',$new_device_detail[$user]['device_ids']);
						$u_device_types = explode(',',$new_device_detail[$user]['device_types']);
                        // print_r($u_device_types);exit;
						if(!empty($unique_device_ids))
						{
							foreach($unique_device_ids as $d_key => $d_value) 
							{
								if(!empty($d_value) && !in_array($d_value,[1,2]))
								{
									$notification_data['device_details'][] =array('device_id' => $d_value,
																			'device_type' => $u_device_types[$d_key]) ;
									// $device_ids[] = $d_value;
								}
                            }
                            
                        }       
                    }
                    
            $notification_data["notification_type"]   = 440;
            $notification_data["content"]             = array(
                    "custom_notification_subject"   =>$notification['en_subject'],
                    "custom_notification_text"      =>$notification['message'],
                    "template_data"                 => array(
                        "season_scheduled_date"             => isset($data['season_scheduled_date']) ? $data['season_scheduled_date'] : "2021-09-07" ,
                        "sports_id"                         => isset($data['sports_id']) ? $data['sports_id'] : "7" ,
                        "collection_master_id"              => isset($collection_master_id) ? $collection_master_id : "706",
                        "home"                              => isset($data['home']) ? $data['home'] : "IND",
                        "away"                              => isset($data['away']) ? $data['away'] : "ENG",
                    ),
            );
            
            $new_notification_data[] =  $notification_data;
        }
        $data = ["push_notification_data" => $new_notification_data];
        //print_r($data);exit;
        $this->load->helper('queue_helper');
        add_data_in_queue($data, CD_PUSH_QUEUE);
    }
}
