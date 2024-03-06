<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Private_contest extends Common_Api_Controller{

	public function __construct()
	{
        parent::__construct();
		$_POST = $this->post();
        $this->admin_lang = $this->lang->line('private_contest');
    }

    /**
     * Used for get private contest setting
     * @param void
     * @return array
     */
    public function get_settings_data_post()
    {
        $visibility = isset($this->app_config['lf_private_contest']['key_value']) ? $this->app_config['lf_private_contest']['key_value']:0;
        $site_rake = isset($this->app_config['lf_site_rake']['key_value']) ? $this->app_config['lf_site_rake']['key_value']:0;
        $host_rake = isset($this->app_config['lf_host_rake']['key_value']) ? $this->app_config['lf_host_rake']['key_value']:0;

        $settings_data['visibility'] = $visibility;
        $settings_data['site_rake'] = $site_rake;
        $settings_data['host_rake'] = $host_rake;

        //echo "<pre>";print_r($settings_data);die;
        $this->api_response_arry['message'] = 'information get successfully';
        $this->api_response_arry['data'] = $settings_data;
        $this->api_response();
    }

    /**
     * Used for update private contest host rake
     * @param decimal $host_rake
     * @return array
     */
    public function update_host_rake_post()
    {
        $this->form_validation->set_rules('host_rake', 'Host rake percentage', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post = $this->input->post();
        $site_rake = isset($this->app_config['lf_site_rake']['key_value']) ? $this->app_config['lf_site_rake']['key_value']:0;
        $site_rake = (isset($site_rake) && $site_rake != '') ? $site_rake : 0;
        if ($site_rake + $post['host_rake'] > 100 || $site_rake + $post['host_rake'] <= 0)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->admin_lang["invalid_rake_percentage"];
            $this->api_response();
        }
        else
        {
            $this->load->model('user/User_model');
            $this->User_model->update_app_setting(array("key_value"=>$post["host_rake"]),array("key_name"=>"lf_host_rake"));

            $config_cache_key = 'app_config';
            $this->delete_cache_data($config_cache_key);
            $this->push_s3_data_in_queue('app_master_data',array(),"delete");

            $this->api_response_arry['data']          = array();
            $this->api_response_arry['message']       = $this->admin_lang["rake_updated"];
            $this->api_response(); 
        }
    }

    /**
     * Used for update private contest site rake
     * @param decimal $site_rake
     * @return array
     */
    public function update_site_rake_post()
    {
        $this->form_validation->set_rules('site_rake', 'Site rake percentage', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post = $this->input->post();

        $host_rake = isset($this->app_config['lf_host_rake']['key_value']) ? $this->app_config['lf_host_rake']['key_value']:0;
        $host_rake = (isset($host_rake) && $host_rake != '') ? $host_rake : 0;
        if ($host_rake + $post['site_rake'] > 100 || $host_rake + $post['site_rake'] <= 0)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->admin_lang["invalid_rake_percentage"];
            $this->api_response();
        }
        else
        {
            $this->load->model('user/User_model');
            $this->User_model->update_app_setting(array("key_value"=>$post["site_rake"]),array("key_name"=>"lf_site_rake"));

            $config_cache_key = 'app_config';
            $this->delete_cache_data($config_cache_key);
            $this->push_s3_data_in_queue('app_master_data',array(),"delete");

            $this->api_response_arry['data'] = array();
            $this->api_response_arry['message'] = $this->admin_lang["rake_updated"];
            $this->api_response(); 
        }
    }

    /**
     * Used for update private contest site rake
     * @param decimal $site_rake
     * @return array
     */
    public function toggle_private_contest_visibility_post()
    {
        $this->form_validation->set_rules('visibility', 'Private contest visibility', 'trim|required');
        if (!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }
        $post = $this->input->post();
        if(in_array($post["visibility"], array(1,2)))
        {
            $this->load->model('user/User_model');
            $this->User_model->update_app_setting(array("key_value"=>$post["visibility"]),array("key_name"=>"lf_private_contest"));

            $config_cache_key = 'app_config';
            $this->delete_cache_data($config_cache_key);
            $this->push_s3_data_in_queue('app_master_data',array(),"delete");

            $this->api_response_arry['data'] = array();
            $this->api_response_arry['message'] = $this->admin_lang["visibility_updated"];
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->admin_lang["invalid_visibility"];
        }
        $this->api_response(); 
    }

    /**
     * Used for get private contest dashboard data
     * @param array $post
     * @return array
     */
    public function dashboard_data_post()
    {
        $post = $this->input->post();
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -7 days'));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $dashboard_data = array();
        $this->load->model('Private_contest_model');
        $real_cash_only_data = $this->Private_contest_model->get_dashboard_data($post, $real_cash_only=TRUE);
        $real_and_coin_data = $this->Private_contest_model->get_dashboard_data($post, $real_cash_only=FALSE);
        foreach ($real_cash_only_data as $key => $value)
        {
            $real_cash_only_data[$key]['host_earning'] = (($value['entry_fee']*$value['host_rake'])/100)*$value['total_user_joined'];
            $real_cash_only_data[$key]['admin_earning'] = (($value['entry_fee']*$value['site_rake'])/100)*$value['total_user_joined'];
        }
        $admin_earning_arr = array_column($real_cash_only_data, "admin_earning");
        $admin_earning_sum = array_sum($admin_earning_arr);

        $host_earning_arr = array_column($real_cash_only_data, "host_earning");
        $host_earning_sum = array_sum($host_earning_arr);

        $dashboard_data['total_private_contests']   = count($real_and_coin_data);
        $dashboard_data['total_user_earning']       = $host_earning_sum;
        $dashboard_data['total_admin_earning']      = $admin_earning_sum;

        $top_creators = $this->Private_contest_model->get_top_creators_data();
        //echo "<pre>";print_r($top_creators);die;
        // die;
        if(!empty($top_creators)){
            $user_ids = array_column($top_creators,"user_id");
            $this->load->model('user/User_model');
            $user_list = $this->User_model->get_users_by_ids($user_ids);
            $user_list = array_column($user_list,NULL,"user_id");

            foreach ($top_creators as $key1 => &$value1)
            {
                $value1['total_earning'] = bcdiv(array_sum(array_column($this->Private_contest_model->get_contest_commission($value1['user_id']), 'commission')), 1, 2);
                $value1['user_name'] = $user_list[$value1['user_id']]['user_name'];
            }
        }
        
        $dashboard_data['top_creators'] = $top_creators;

        $this->api_response_arry['message'] = 'information get successfully';
        $this->api_response_arry['data'] = $dashboard_data;
        $this->api_response();
    }

    /**
     * Used for get private contest create dashboard data
     * @param array $post
     * @return array
     */
    public function get_private_contest_created_graph_post()
    {
        $post = $this->input->post();
        $this->load->model('Private_contest_model');
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -70 days'));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $Dates = array();
        $contests = array();
        $Dates = get_dates_from_range($post['from_date'], $post['to_date']);
        foreach($Dates as &$date)
        {
            $start_time = date('H:i:s',strtotime($post['from_date']));
            $contests[] = $this->Private_contest_model->get_contests_by_dates($date,$start_time);
            $date = date('d M',strtotime($date));
        }

        $contests = array_column($contests, "total_contests");
        $contests = array_map('intval', $contests);
        $this->api_response_arry['data']['graph_data'] = $contests;
        $this->api_response_arry['data']['total_contest'] = array_sum($contests);
        $this->api_response_arry['data']['dates']   = $Dates;
        $this->api_response();
    }

    /**
     * Used for get private users signup dashboard data
     * @param array $post
     * @return array
     */
    public function get_new_user_signup_graph_post()
    {
        $post = $this->input->post();
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -30 days'));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $Dates = array();
        $contests = array();
        $Dates = get_dates_from_range($post['from_date'], $post['to_date']);
        foreach($Dates as &$date)
        {
            $this->load->model('user/User_nosql_model');
            $contests[] = $this->User_nosql_model->count('private_contest_new_users',array('signup_date'=> $date,"contest_type"=>"2"));
            $date = date('d M',strtotime($date));
        }

        $this->api_response_arry['data']['graph_data'] = $contests;
        $this->api_response_arry['data']['dates'] = $Dates;
        $this->api_response_arry['data']['total_new_signups'] = array_sum($contests);
        $this->api_response();
    }

}
?>
