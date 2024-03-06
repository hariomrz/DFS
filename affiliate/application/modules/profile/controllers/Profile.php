<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends Common_Api_Controller {

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->post();
		$this->current_date = format_date();
		$this->load->model('Profile_model');
		//Do your magic here
	}

	/**
	 * Used for get affiliate profile details
	 * @param void
	 * @return boolean
	 */
	public function get_profile_post()
	{
		$post_data = $this->input->post();
		$profile_data = $this->Profile_model->get_affiliate_profile($post_data);
		$this->api_response_arry['data'] = $profile_data;
		$this->api_response();
	}

	/**
	 * Used for get affiliate profile details
	 * @param affiliate id
	 * @return json
	 */
	public function get_aff_campaign_detail_post()
	{ 
		$post_data = $this->input->post();
		$campaign_details = $this->Profile_model->get_campaign_details($post_data);
		$this->api_response_arry['data'] = $campaign_details;
		$this->api_response();
	}

	/**
	 * Used for get affiliate profile details
	 * @param affiliate id
	 * @return csv file
	 */

	public function get_aff_campaign_detail_get()
    {
        $_POST = $this->input->get();
        if(!isset($_POST['csv']))
        {
            $_POST['csv']=true;
        }
		$_POST['affiliate_id'] = $this->admin_id;
		// $this->load->model('admin/Affiliate_model');
        $result = $this->Profile_model->get_campaign_details($_POST);
		foreach($result['result'] as $key=>$res)
        {
        if($res['deposit']>0)
        {
        $result['result'][$key]['deposit'] = $result['result'][$key]['deposit'].'%';
        }
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
            $name = 'New_Affiliate_users.csv';
            force_download($name, $data);
        }
        else{
            $result = "no record found";
            $this->load->helper('download');
            $this->load->helper('csv');
            $data = array_to_csv($result);
            $name = 'New_Affiliate_users.csv';
            force_download($name, $result);
        }
    }

	/**
	 * Used for get affiliate profile details
	 * @param affiliate id
	 * @return json
	 */
	public function track_single_url_post()
    {
        $this->form_validation->set_rules('campaign_id', 'Campaign ID', 'trim|required|callback_is_campaign_exist');
        
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
		$this->load->model('admin/Affiliate_model');
        $url_details = $this->Affiliate_model->track_single_url($post_data);
        if($url_details)
        {
            $this->api_response_arry['message'] = "URL tracking List";
            $this->api_response_arry['data'] = $url_details;
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "No details found for this campaign.";
        }
        $this->api_response();
    }

	/**
	 * Used for get affiliate profile details
	 * @param affiliate id
	 * @return csv file
	 */

	public function track_single_url_get()
    {
        $_POST = $this->input->get();
        if(!isset($_POST['csv']))
        {
            $_POST['csv']=true;
        }
		$this->load->model('admin/Affiliate_model');
        $result = $this->Affiliate_model->track_single_url($_POST);
        if(!empty($result['result'])){
            $result =$result['result'];
            $header = array_keys($result[0]);
            $camelCaseHeader = array_map("camelCaseString", $header);
            $result = array_merge(array($camelCaseHeader),$result);
            $this->load->helper('download');
            $this->load->helper('csv');
            $data = array_to_csv($result);

            $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
            $name = 'Single_campaign_users.csv';
            force_download($name, $data);
        }
        else{
            $result = "no record found";
            $this->load->helper('download');
            $this->load->helper('csv');
            $data = array_to_csv($result);
            $name = 'Single_campaign_users.csv';
            force_download($name, $result);
        }
    }

	/**
	 * supporting method for validation
	 * @param campaign_id
	 * @return boolean
	 */
	public function is_campaign_exist()
	{
		$this->load->model('admin/Affiliate_model');
		$campaign_id = $this->input->post('campaign_id');
		$result = $this->Profile_model->get_single_row('campaign_id',CAMPAIGN,["campaign_id"=>$campaign_id]);
	   //  echo $this->db->last_query();exit;
		if(!empty($result['campaign_id']))
		{
			return TRUE;
		}
		
		$this->form_validation->set_message('is_campaign_exist','campaign ID '.$campaign_id.' does not exists.');
		return FALSE;
	}


	/**
	 * supporting method for validation
	 * @param user_id
	 * @return boolean
	 */
	public function is_valid_user()
	{

		$this->load->model('admin/Affiliate_model');
		$user_id = $this->input->post('user_id');
		$result = $this->Profile_model->get_single_row('user_id',CAMPAIGN_USERS,["user_id"=>$user_id]);
	   //  echo $this->db->last_query();exit;
		if(!empty($result['user_id']))
		{
			return TRUE;
		}
		
		$this->form_validation->set_message('is_valid_user','user ID '.$user_id.' does not exists.');
		return FALSE;
	}



	public function get_single_user_details_post()
    {    	
        $post_data = $this->input->post();
        $this->form_validation->set_rules('user_id', 'User ID', 'trim|required|callback_is_valid_user');
        
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }        

        $user_details = $this->Affiliate_model->get_single_user_details($post_data);

        if($user_details)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = "URL tracking List";
            $this->api_response_arry['data'] = $user_details;
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "No details found for this user.";
        }
        $this->api_response();
    }

    // we have to add filter start date and end date & name email search

    public function get_single_user_details_get()
    {
        $_POST = $this->input->get();
        if(!isset($_POST['csv']) || $_POST['csv']==false)
        {
            $_POST['csv'] = true;
        }
        $this->load->model('admin/Affiliate_model');
		$result = $this->Affiliate_model->get_single_user_details($_POST);
					
			if(!empty($result['result'])){
				$result          = $result['result'];
				$header          = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result          = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('download');
                $this->load->helper('csv');
                $data = array_to_csv($result);

                $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
                $name = 'USER_DETAIL.csv';
				force_download($name, $data);
			}
			else{
				$result = "no record found";
				$this->load->helper('download');
				$this->load->helper('csv');
				$data = array_to_csv($result);
				$name = 'USER_DETAIL.csv';
				force_download($name, $result);
			}
    }

}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */