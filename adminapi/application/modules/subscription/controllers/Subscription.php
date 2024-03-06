<?php if (!defined('BASEPATH'))	exit('No direct script access allowed');

class Subscription extends MYREST_Controller 
{

    public function __construct()
	{
		parent::__construct();
        $_POST = $this->input->post();

        $is_subscription = isset($this->app_config['allow_subscription'])?$this->app_config['allow_subscription']['key_value']:0;
        $is_coin = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        if(!$is_subscription || !$is_coin){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['status'] = FALSE;
                $this->api_response_arry['message'] = $this->admin_lang["subscription_module_enable"];
                $this->api_response();
        }

		$this->load->model('Subscription_model','subm');		
		$this->admin_lang = $this->lang->line('subscription');
    }
    
    /**
     * add new subscription package
     * @param array of subscripti package details
     * return boolean;
     */
    public function add_package_post()
    {
        $post_data = $this->input->post();
        if(empty($post_data['android_id']) && empty($post_data['ios_id']))
        {
            $this->api_response_arry['response_code']	= 500;
            $this->api_response_arry['service_name']	= 'add_package';
            $this->api_response_arry['message']			= "Atleast one playstore id is required";
            $this->api_response();
        }
        $this->form_validation->set_rules('name','Package Name','trim|required');
        $this->form_validation->set_rules('amount','Package Amount','trim|required');
        $this->form_validation->set_rules('coins','Benifit Coins','trim|required');

        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $exist = $this->subm->check_exist($post_data);
        if($exist==false)
        {
            $result = $this->subm->add_package($post_data);
            if($result)
            {
                $this->api_response_arry['service_name']	= 'add_package';
                $this->api_response_arry['message']       = $this->admin_lang['success_add_package'];
            }
            else{
                $this->api_response_arry['response_code']	= 500;
                $this->api_response_arry['service_name']	= 'add_package';
                $this->api_response_arry['message']			= "there is some problem in adding package";
            }
        }
        else{
            $this->api_response_arry['response_code']	= 500;
            $this->api_response_arry['service_name']	= 'add_package';
            $this->api_response_arry['message']			= "either ios or android package already exists";
        }
        
		$this->api_response(); 
    }

    /**
     * method to remove package
     * @param master_subscription_id
     */
    public function remove_package_post()
    {
        $post = $this->input->post();
        $this->form_validation->set_rules('subscription_id','Subscription ID','trim|required');

        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }
        $result = $this->subm->remove_package($post);
        if($result)
        {
            $this->api_response_arry['message']       = $this->admin_lang["success_delete_package"];
        }else{
            $this->api_response_arry['message']       = $this->admin_lang["no_change"];
        }
        $this->api_response();
    }

    public function get_packages_post()
    {
        $post =  $this->input->post();
        $result = $this->subm->get_packages($post);

        $this->api_response_arry['response_code']	= 200;
        $this->api_response_arry['data']	        = $result;
        $this->api_response_arry['service_name']	= 'get_packages';
        $this->api_response_arry['message']			= "get packages successfully";
        $this->api_response();

    }

    public function get_subscription_report_post()
    {
        $post_data = $this->input->post();
        $result = $this->subm->get_subscription_report($post_data);
        $result['total_earn'] = $result['result'] ? array_sum(array_column($result['result'],'amount')):0;
        foreach($result['result'] as $key=>$value)
        {
            $result['result'][$key]['amount']   = json_decode($value['amount'],true);
            $result['result'][$key]['coins']    = json_decode($value['coins'],true);
        }

        $this->api_response_arry['response_code']	= 200;
        $this->api_response_arry['data']	        = $result;
        $this->api_response_arry['service_name']	= 'get_packages';
        $this->api_response_arry['message']			= "get packages successfully";
        $this->api_response();
    }

    public function get_subscription_report_get()
    {
        $post_data = $this->input->get();
        if(!isset($post_data['csv'])) $post_data['csv'] = 1;
        $result = $this->subm->get_subscription_report($post_data);
        foreach($result['result'] as $key=>$value)
        {
            $result['result'][$key]['amount'] = (int)json_decode($value['amount'],true);
            $result['result'][$key]['coins'] = (int)json_decode($value['coins'],true);
        }

        if(!empty($result['result'])){
            $result =$result['result'];
            $header = array_keys($result[0]);
            $camelCaseHeader = array_map("camelCaseString", $header);
            $result = array_merge(array($camelCaseHeader),$result);
            $this->load->helper('download');
                            $this->load->helper('csv');
                            $data = array_to_csv($result);
                            $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
                            $name = 'Subscription_report.csv';
                            force_download($name, $data);
        }
        else{
            $result = "no record found";
            $this->load->helper('download');
            $this->load->helper('csv');
            $data = array_to_csv($result);
            $name = 'Subscription_report.csv';
            force_download($name, $result);

        }
    }


}
/**
 * Subscription controller adminapi/application/modules/subscription/controllers/Subscription.php
 */
?>