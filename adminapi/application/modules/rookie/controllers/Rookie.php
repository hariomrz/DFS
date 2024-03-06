<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rookie extends MYREST_Controller {

    var $rookie_winning_amount =0;
    var $rookie_month_number=0;
    var $rookie_group_id=0;
    var $date_filter_map = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Rookie_model');
		$_POST = $this->input->post();
        $this->date_filter_map['last_week'] = ' -7 days';
        $this->date_filter_map['last_month'] = ' -1 month';
        $this->date_filter_map['last_3_month'] = ' -3 months';
        $this->date_filter_map['last_6_month'] = ' -6 months';
        $allow_rookie_contest = isset($this->app_config['allow_rookie_contest'])?$this->app_config['allow_rookie_contest']['key_value']:0;
		
        if($allow_rookie_contest == 0)
        {
            $this->api_response_arry['response_code'] 	= 500;
            $this->api_response_arry['global_error']  	= 'Module not activated';
            $this->api_response();
        }
        else{
            $rookie_data = $this->app_config['allow_rookie_contest']['custom_data'];
            $this->rookie_winning_amount=$rookie_data['winning_amount'];
            $this->rookie_month_number=$rookie_data['month_number'];
            $this->rookie_group_id=$rookie_data['group_id'];

        }
    }

    function get_dashboard_data_post()
    {
        $this->form_validation->set_rules('filter', 'Filter', 'trim|required');
        if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
        $final_data = array();
        //get all users
      
        $final_data['total_users'] = $this->Rookie_model->get_all_user_count()['total_users'];
        //get rookie users
        $final_data['rookie_users'] = $this->Rookie_model->get_rookie_user_count()['rookie_users'];

        $current_date = format_date();
        $filter_str = $this->input->post('filter');
        $filter_date = $this->date_filter_map['last_week']; 
        if(isset($this->date_filter_map[$filter_str]))
        {
            $filter_date =date('Y-m-d',strtotime($current_date.' '.$this->date_filter_map[$filter_str]));
        }

        //get rookie contestids
        $contest_id_list = $this->Rookie_model->get_rookie_contest_ids($filter_date);
        if(!empty($contest_id_list))
        {
            $contest_id_list = array_column($contest_id_list,'contest_id');
        }
        $rookie_participation = $this->Rookie_model->get_rookie_user_participation($filter_date,$contest_id_list);

       
        $final_data['rookie_paticipation'] = array();

        if(!empty($rookie_participation))
        {
            $final_data['total_entry_fee']  = array_sum(array_values(array_column($rookie_participation,'total_entry_fee')));
            $final_data['total_winning']   = array_sum(array_values(array_column($rookie_participation,'total_winning')));
            $final_data['profit'] = $final_data['total_entry_fee']-$final_data['total_winning'];
            
            $contest_ids = array();
            $total_users = array();
            foreach($rookie_participation as &$row)
            {
                $user_ids = array();
                $user_id_arr = explode(',',$row['user_ids']);
                $user_id_arr = array_filter($user_id_arr, function($item){ return !empty($item);}); 
                $user_ids = array_unique(array_merge($user_ids,$user_id_arr));
                $row['data_value'] = count($user_ids);
                $total_users = array_merge($total_users,$user_ids);
                $contest_id_arr = explode(',',$row['contest_ids']);
                $contest_id_arr = array_filter($contest_id_arr, function($item){ return !empty($item);}); 
                $contest_ids = array_merge($contest_ids,$contest_id_arr);
            }

            $final_data['rookie_paticipation']['total_users'] = count(array_unique($total_users));
            $final_data['rookie_paticipation']['total_contests'] = count(array_unique($contest_ids));

            $final_data['rookie_paticipation']['graph_data'] = get_lineup_graph_data($filter_date,$current_date,$rookie_participation);
            
        }

        $final_data['graduated_rookie_data'] = $this->get_graduated_rookie_data($filter_date,$contest_id_list,$total_users);
        //echo '<pre>';print_r($final_data);die('dfd');
        $this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $final_data;
		$this->api_response();
    }

    private function get_graduated_rookie_data($filter_date,$contest_id_list,$rookie_user_ids)
    {
        $current_date = format_date();
        $graduated_rookie_data = $this->Rookie_model->get_graduated_rookie_data($filter_date,$contest_id_list,$rookie_user_ids);

        $final_data = array();

        if(!empty($graduated_rookie_data))
        {
            $final_data['total_entry_fee']  = array_sum(array_values(array_column($graduated_rookie_data,'total_entry_fee')));
            $final_data['total_winning']   = array_sum(array_values(array_column($graduated_rookie_data,'total_winning')));
            $final_data['profit'] = $final_data['total_entry_fee']-$final_data['total_winning'];
           
            $contest_ids = array();
            $won_users_ids = array();
            $total_users = array();
            foreach($graduated_rookie_data as &$row)
            {
                $user_ids = array();
                $user_id_arr = explode(',',$row['user_ids']);
                $user_id_arr = array_filter($user_id_arr, function($item){ return !empty($item);}); 
                $user_ids = array_unique(array_merge($user_ids,$user_id_arr));
                $row['data_value'] = count($user_ids);
                $total_users = array_merge($total_users,$user_ids);
                $contest_id_arr = explode(',',$row['total_contest_ids']);
                $contest_id_arr = array_filter($contest_id_arr, function($item){ return !empty($item);}); 
                $contest_ids = array_merge($contest_ids,$contest_id_arr);

                $won_users_id_arr = explode(',',$row['won_users']);
                $won_users_id_arr = array_filter($won_users_id_arr, function($item){ return !empty($item);}); 
                $won_users_ids = array_merge($won_users_ids,$won_users_id_arr);
            }

            $final_data['graduated_rookie_data']['total_users'] = count(array_unique($total_users));
            $final_data['graduated_rookie_data']['total_contests'] = count(array_unique($contest_ids)); 

            //get won users
            $rookie_grad = $this->Rookie_model->get_graduated_rookie_participation($filter_date,$contest_id_list,$rookie_user_ids);
            
            $final_data['graduated_rookie_data']['with_win'] = 0 ;

            if(isset($rookie_grad['with_win']))
            {
                $final_data['graduated_rookie_data']['with_win'] = $rookie_grad['with_win'] ;
            }

            $final_data['graduated_rookie_data']['graph_data'] = get_lineup_graph_data($filter_date,$current_date,$graduated_rookie_data);

            return $final_data['graduated_rookie_data'];
        }

        return $final_data;
    }

    function check_rookie_user_count_post()
    {
        $this->form_validation->set_rules('winning_amount', 'Winning amount', 'trim|required');
        $this->form_validation->set_rules('month_number', 'Month number', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

        $winning_amount = $this->input->post('winning_amount');
        $month_number = $this->input->post('month_number');
        
        $this->rookie_winning_amount = $winning_amount;
        $this->rookie_month_number = $month_number;
      
        $data = array();
        $data['rookie_users'] = $this->Rookie_model->get_rookie_user_count()['rookie_users'];
        $this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $data;
		$this->api_response();
    }

    public function get_rookie_user_list_post()
    {
        $post_data = $this->input->post();
        $data = $this->Rookie_model->get_rookie_user_list($post_data);

        $users_ids = array();
        if(!empty($data['result']))
        {
            $users_ids = array_column($data['result'],'user_id');
        }

        //get free contest join count for rookie contests
        $free_contest_result =$this->Rookie_model->get_free_contest_users($users_ids);

        if(!empty($free_contest_result))
        {
            $free_contest_result = array_column($free_contest_result,'free_contests','user_id');
        }

        foreach($data['result'] as &$row)
        {
            $row['free_contests'] = 0;
            if(isset($free_contest_result[$row['user_id']]))
            {
                $row['free_contests']=$free_contest_result[$row['user_id']];
            }
        }
        $this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $data;
		$this->api_response();
    }

    function update_rookie_setting_post()
    {
        $this->form_validation->set_rules('winning_amount', 'Winning amount', 'trim|required');
        $this->form_validation->set_rules('month_number', 'Month number', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
       
        $rookie_winning_amount = 0;
        $rookie_month_number = 0;

        $winning_amount = $this->input->post('winning_amount');
        $month_number = $this->input->post('month_number');
          //get setting 
          $setting = $this->Rookie_model->get_single_row('custom_data',APP_CONFIG,array('key_name' => 'allow_rookie_contest','key_value' => 1));

         
        if(!empty($setting['custom_data']))
        {
            $custom_data = json_decode($setting['custom_data'],TRUE);
        }
        else{
            $this->api_response_arry['response_code'] 	= 500;
            $this->api_response_arry['global_error']  	= 'Module not activated';
            $this->api_response();
        }

        if($winning_amount > 0)
        {
            $rookie_winning_amount = $winning_amount;
        }
        
        if($month_number > 0)
        {
            $rookie_month_number = $month_number;
        }

        $custom_data['winning_amount'] = $rookie_winning_amount;
        $custom_data['month_number'] = $rookie_month_number;

        $update_data = array('custom_data' => json_encode($custom_data));

        $this->Rookie_model->update_setting($update_data);
        $this->delete_cache_data('app_config');
        $this->push_s3_data_in_queue('app_master_data',array(),"delete");
        $this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['message']  ="Setting Updated." ;
		$this->api_response();

    }

    function get_rookie_setting_post()
    {
        $rookie = $this->Rookie_model->get_single_row('custom_data',APP_CONFIG,array('key_name' => 'allow_rookie_contest'));
        $this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  =json_decode($rookie['custom_data'],TRUE);
		$this->api_response();
    }

}