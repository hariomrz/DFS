<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Private_contest extends MYREST_Controller{

	public function __construct()
	{
        parent::__construct();
        $this->admin_lang = $this->lang->line('private_contest');
		$_POST = $this->input->post();
		$this->load->model('Private_contest_model');
		//Do your magic here
    }
    /**  method will update status of user as well commission and other values , also affiliate date in case when it is going to become an affiliate.
     * @param : user_id;
     * @return : message
     * 
    */
    public function dashboard_data_post()
    {
        $post = $this->input->post();
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d H:i:s',strtotime(format_date().' -7 days'));
            $post['to_date'] = date('Y-m-d H:i:s',strtotime(format_date()));
        }

        $dashboard_data = array();
        $real_cash_only_data = $this->Private_contest_model->get_dashboard_data($post, $real_cash_only=TRUE);
        $real_and_coin_data = $this->Private_contest_model->get_dashboard_data($post, $real_cash_only=FALSE);
        // echo "<pre>";print_r(count($real_cash_only_data));
        // echo "<pre>";print_r(count($real_and_coin_data));
        // die;

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
        // echo "<pre>";print_r($top_creators);
        // die;
        foreach ($top_creators as $key1 => &$value1) // use where_in to get data
        {
            $value1['total_earning'] = bcdiv(array_sum(array_column($this->Private_contest_model->get_contest_commission($value1['user_id']), 'commission')), 1, 2);
            $value1['user_name'] = $this->Private_contest_model->get_username($value1['user_id']);
        }

        $dashboard_data['top_creators']      = $top_creators;

        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['status']          = TRUE;
        $this->api_response_arry['message']         = 'information get successfully';
        $this->api_response_arry['data']            = $dashboard_data;
        $this->api_response();
    }

    public function get_settings_data_post()
    {
        $visibility = $this->Private_contest_model->get_single_row('key_value',APP_CONFIG,array('key_name' => 'allow_private_contest'));
        $site_rake = $this->Private_contest_model->get_single_row('key_value',APP_CONFIG,array('key_name' => 'site_rake'));
        $host_rake = $this->Private_contest_model->get_single_row('key_value',APP_CONFIG,array('key_name' => 'host_rake'));

        $settings_data['visibility'] = $visibility['key_value'];
        $settings_data['site_rake'] = $site_rake['key_value'];
        $settings_data['host_rake'] = $host_rake['key_value'];

        // echo "<pre>";print_r($settings_data);die;
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['status']          = TRUE;
        $this->api_response_arry['message']         = 'information get successfully';
        $this->api_response_arry['data']            = $settings_data;
        $this->api_response();
    }

    public function get_private_contest_created_graph_post()
    {
        $post = $this->input->post();
        
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d H:i:s',strtotime(format_date().' -70 days'));
            $post['to_date'] = date('Y-m-d H:i:s',strtotime(format_date()));
        }
        $Dates      = array();
        $contests   = array();

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

    public function toggle_private_contest_visibility_post()
    {
        $this->form_validation->set_rules('visibility', 'Private contest visibility', 'trim|required');
        
        if (!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }
        $post = $this->input->post();

        if (in_array($post["visibility"], array(1,2)))
        {
            $this->Private_contest_model->update_private_contest_visibility($post["visibility"]);

            $config_cache_key = 'app_config';
            $this->delete_cache_data($config_cache_key);
            $this->push_s3_data_in_queue('app_master_data',array(),"delete");
            $this->flush_cache_data();

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data']          = array();
            $this->api_response_arry['message']       = $this->admin_lang["visibility_updated"];
        }
        else
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = $this->admin_lang["invalid_visibility"];
        }
        $this->api_response(); 
    }

    public function update_site_rake_post()
    {
        $this->form_validation->set_rules('site_rake', 'Site rake percentage', 'trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post = $this->input->post();

        $host_rake = $this->Private_contest_model->get_single_row('key_value',APP_CONFIG,array('key_name' => 'host_rake'));
        $host_rake = $host_rake['key_value'];
        $host_rake = (isset($host_rake) && $host_rake != '') ? $host_rake : 0;
        if ($host_rake + $post['site_rake'] > 100 || $host_rake + $post['site_rake'] <= 0)
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = $this->admin_lang["invalid_rake_percentage"];
            $this->api_response();
        }
        else
        {
            $this->Private_contest_model->update_site_rake($post["site_rake"]);
            $config_cache_key = 'app_config';
            $this->delete_cache_data($config_cache_key);
            $this->push_s3_data_in_queue('app_master_data',array(),"delete");
            $this->flush_cache_data();

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['service_name']  = 'update_site_rake';
            $this->api_response_arry['data']          = array();
            $this->api_response_arry['message']       = $this->admin_lang["rake_updated"];
            $this->api_response(); 
        }
    }

    public function update_host_rake_post()
    {
        $this->form_validation->set_rules('host_rake', 'Host rake percentage', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post = $this->input->post();

        $site_rake = $this->Private_contest_model->get_single_row('key_value',APP_CONFIG,array('key_name' => 'site_rake'));
        $site_rake = $site_rake['key_value'];
        $site_rake = (isset($site_rake) && $site_rake != '') ? $site_rake : 0;
        if ($site_rake + $post['host_rake'] > 100 || $site_rake + $post['host_rake'] <= 0)
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = $this->admin_lang["invalid_rake_percentage"];
            $this->api_response();
        }
        else
        {
            $this->Private_contest_model->update_host_rake($post["host_rake"]);
            $config_cache_key = 'app_config';
            $this->delete_cache_data($config_cache_key);
            $this->push_s3_data_in_queue('app_master_data',array(),"delete");
            $this->flush_cache_data();

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data']          = array();
            $this->api_response_arry['message']       = $this->admin_lang["rake_updated"];
            $this->api_response(); 
        }
    }

    public function get_new_user_signup_graph_post()
    {
        $post = $this->input->post();

        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d H:i:s',strtotime(format_date().' -30 days'));
            $post['to_date'] = date('Y-m-d H:i:s',strtotime(format_date()));
        }

        $Dates      = array();
        $contests   = array();

        $Dates = get_dates_from_range($post['from_date'], $post['to_date']);
        foreach($Dates as &$date)
        {
            $this->load->model('auth/Auth_nosql_model');
            $contests[] = $this->Auth_nosql_model->count('private_contest_new_users',array('signup_date'=> $date,"contest_type" => "1"));
            $date = date('d M',strtotime($date));
        }

        $this->api_response_arry['data']['graph_data']          = $contests;
        $this->api_response_arry['data']['dates']               = $Dates;
        $this->api_response_arry['data']['total_new_signups']   = array_sum($contests);
        $this->api_response();
    }

}
?>
