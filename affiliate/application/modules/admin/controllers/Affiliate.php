<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Affiliate extends Admin_Api_Controller {
	public function __construct()
	{
		parent::__construct();
		$_POST = $this->post();
		$this->load->model('Affiliate_model');
        $this->current_date = format_date('today');
	}

    //validation call back method
    public function is_user_exist()
    {
        $email = $this->input->post('email');
        $result = $this->Affiliate_model->get_single_row('affiliate_id',AFFILIATE,["email"=>$email]);
        // echo $this->db->last_query();exit;
        if(!empty($result['affiliate_id']))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Email: ".$email." already exists.";
            $this->api_response();
        }
        return TRUE;
    }

    //validation call back method
    public function is_campaign_name_exist()
    {
        $name = $this->input->post('name');
        $affiliate_id = $this->input->post('affiliate_id');
        $campaign_id = $this->input->post('campaign_id');
        $result = $this->Affiliate_model->get_single_row('name',CAMPAIGN,["name"=>$name,"campaign_id!="=>$campaign_id,"status!="=>3,"affiliate_id"=>$affiliate_id]);
        // echo $this->db->last_query();exit;
        if(!empty($result['name']))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Campaign name :'.$name.' already exists please choose something different.';
            $this->api_response();
        }
        return TRUE;
    }

     //validation call back method
     public function is_campaign_exist()
     {
         $campaign_id = $this->input->post('campaign_id');
         $result = $this->Affiliate_model->get_single_row('campaign_id',CAMPAIGN,["campaign_id"=>$campaign_id]);
        //  echo $this->db->last_query();exit;
         if(!empty($result['campaign_id']))
         {
             return TRUE;
         }
         
        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['message'] = 'Campaign ID :'.$campaign_id.' does not exists.';
        $this->api_response();
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
        $result = $this->Affiliate_model->get_single_row('user_id',CAMPAIGN_USERS,["user_id"=>$user_id]);
        //  echo $this->db->last_query();exit;
        if(!empty($result['user_id']))
        {
            return TRUE;
        }
        
        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['message'] = 'User ID '.$user_id.' does not exists.';
        $this->api_response();
    }

    public function is_others_email()
    {
        $email = $this->input->post('email');
        $affiliate_id = $this->input->post('affiliate_id');
        $result = $this->Affiliate_model->get_single_row('affiliate_id',AFFILIATE,["email"=>$email]);
        if(!empty($result['affiliate_id']) && $affiliate_id != $result['affiliate_id'])
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Email :'.$email.' already exists.';
            $this->api_response();
        }
        return TRUE;
    }

    public function is_others_phone()
    {
        $mobile = $this->input->post('mobile');
        $affiliate_id = $this->input->post('affiliate_id');
        $result = $this->Affiliate_model->get_single_row('affiliate_id',AFFILIATE,["mobile"=>$mobile]);
        if(!empty($result['affiliate_id']) && $affiliate_id != $result['affiliate_id'])
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Phone number :'.$mobile.' already exists.';
            $this->api_response();
        }
        return TRUE;
    }

    public function add_affiliate_post(){
        
        $post_data = $this->input->post();
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_is_user_exist');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $is_mobile = $this->Affiliate_model->get_single_row('affiliate_id',AFFILIATE,["mobile"=>$post_data['mobile']]);
        if(!empty($is_mobile))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Mobile Number: ".$post_data['mobile']." is already exists.";
            $this->api_response(); 
        }
        $org_pwd = $post_data['password'];
        $password = md5(md5($org_pwd));

        $affiliate_data = array(
            "name"=>$post_data['name'],
            "email"=>$post_data['email'],
            "mobile"=>$post_data['mobile'] ? $post_data['mobile'] : null,
            "password"=>$password,
            "note"=>$post_data['note'] ? $post_data['note'] : null,
            "date_created"=>$this->current_date,
            "date_modified"=>$this->current_date
        );
        $is_added = $this->Affiliate_model->add_affiliate($affiliate_data);
        if($is_added)
        {
            $this->load->helper('queue_helper');
            $content = array(
                'name' => $post_data['name'],
                'url' => AFF_URL,
                'email' => $post_data['email'],
                'pwd' => $org_pwd,
                'site_title' =>SITE_TITLE
            );
            $email_content = array();
            $email_content['type']          = "signup";
            $email_content['email']         = $post_data['email'];
            $email_content['subject']       = "Affiliate Request Accepted";
            $email_content['user_name']     = $post_data["user_name"];
            $email_content['content']       = $content;
            add_data_in_queue($email_content, 'af_email');

            $this->api_response_arry['message'] = "Affiliate Added Successfully";
            $this->api_response();
        }
        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['message'] = "Some issue in adding affiliate";
        $this->api_response();

    }

    public function update_affiliate_post(){
        $this->form_validation->set_rules('affiliate_id', 'Affiliate Id', 'trim|required');
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|callback_is_others_phone');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_is_others_email');
        $this->form_validation->set_rules('password', 'Password', 'trim');
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        if(count($post_data)==1)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please mention the details which needs to update";
        }

        $data = array(
            "name"=>$post_data['name'],
            "email"=>$post_data['email'],
            "mobile"=>isset($post_data['mobile']) ? $post_data['mobile'] : null,
            "note"=> isset($post_data['note']) ? $post_data['note'] : null,
            "date_modified"=>$this->current_date
        );

        //password will be updated only when it is set.
        if(isset($post_data['password']) && $post_data['password']!="")
        {
            $data['password'] = md5(md5($post_data['password']));
        }
        
        $update = $this->Affiliate_model->update_table(AFFILIATE,$data,["affiliate_id"=>$post_data['affiliate_id']]);
        if($update)
        {
            if(isset($post_data['password']) && $post_data['password']!="")
            {
                //send password change mail
                $this->load->helper('queue_helper');
                $content = array(
                    'name' => $post_data['name'],
                    'url' => AFF_URL,
                    'email' => $post_data['email'],
                    'pwd' => $post_data['password'],
                    'site_title' =>SITE_TITLE
                );
                $email_content = array();
                $email_content['type']          = "pwd_chng";
                $email_content['email']         = $post_data['email'];
                $email_content['subject']       = "Password changed successfully";
                $email_content['user_name']     = $post_data["name"];
                $email_content['content']       = $content;
                add_data_in_queue($email_content, 'af_email');
            }
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = "Affiliate updated successfully";
        }else{
            $this->api_response_arry['message'] = "No change";
        }

        $this->api_response();
    }

    public function get_affiliates_post(){
       $post_data = $this->input->post();
       $affiliates = $this->Affiliate_model->get_affiliates($post_data);
       $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
       $this->api_response_arry['message'] = "Affiliate Users List";
       $this->api_response_arry['data'] = $affiliates;
       $this->api_response();
    }
    
    // we have to add filter start date and end date & name email search
    public function get_affiliates_get()
    {
        $_POST = $this->input->get();
        if(!isset($_POST['csv']) || $_POST['csv']==false)
        {
            $_POST['csv'] = true;
        }

		$result = $this->Affiliate_model->get_affiliates($_POST);
					
			if(!empty($result['result'])){
				$result =$result['result'];
				$header = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('download');
                $this->load->helper('csv');
                $data = array_to_csv($result);

                $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
                $name = 'Affiliate_URL.csv';
				force_download($name, $data);
			}
			else{
				$result = "no record found";
				$this->load->helper('download');
				$this->load->helper('csv');
				$data = array_to_csv($result);
				$name = 'Affiliate_URL.csv';
				force_download($name, $result);
			}
    }

    public function create_campaign_post()
    {
        $this->form_validation->set_rules('affiliate_id', 'Affiliate ID', 'trim|required');
        $this->form_validation->set_rules('name', 'Name', 'trim|required|callback_is_campaign_name_exist');
        $this->form_validation->set_rules('source', 'Source', 'trim|required');
        $this->form_validation->set_rules('medium', 'Medium', 'trim|required');
        $this->form_validation->set_rules('url', 'Website URL', 'trim|required');
        $this->form_validation->set_rules('expiry_date', 'Expiry Date', 'trim|required');
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        if(empty($post_data['commission']))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please mention some commission for at least one event.";
            $this->api_response();
        }

        if($post_data['expiry_date'] <= $this->current_date)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Expiry date should be some future date.";
            $this->api_response();
        }

        $post_data['campaign_code'] = strtoupper(generateRandomString());

        $campaign_data = array(
            "affiliate_id"=>$post_data['affiliate_id'],
            "name"=>$post_data['name'],
            "source"=>$post_data['source'],
            "medium"=>$post_data['medium'],
            "url"=>$post_data['url'],
            "expiry_date"=>$post_data['expiry_date'],
            "commission"=>$post_data['commission'] ? json_encode($post_data['commission']) : null,
            "campaign_code"=>$post_data['campaign_code'],
            "date_created"=> $this->current_date,
            "modified_date"=>$this->current_date,
        );
        $is_added = $this->Affiliate_model->add_campaign($campaign_data);

        if($is_added)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = "Campaign Added Successfully";
            $this->api_response();
        }
        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['message'] = "Some issue in adding campaign";
        $this->api_response();
    }

    /**
     * function to publish, edit & delete a campaign
     */
    public function update_campaign_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('campaign_id', 'Campaign Id', 'trim|required|callback_is_campaign_exist');
        $this->form_validation->set_rules('status', 'status', 'trim|in_list[1,2,3,4]');
        $this->form_validation->set_rules('name', 'Name', 'trim|required|callback_is_campaign_name_exist');
        $this->form_validation->set_rules('expiry_date', 'Expiry Date', 'trim|required');
        $this->form_validation->set_rules('source', 'Source', 'trim|required');
        $this->form_validation->set_rules('medium', 'Medium', 'trim|required');
        $this->form_validation->set_rules('url', 'Website URL', 'trim|required');
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        if(empty($post_data['commission']))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please mention some commission for at least one event.";
        }

        if($post_data['expiry_date'] <= $this->current_date)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Expiry date should be some future date.";
        }

        $campaign_data = array(
            "expiry_date"=>$post_data['expiry_date'],
            "commission"=>$post_data['commission'] ? json_encode($post_data['commission']) : null,
            "modified_date"=>$this->current_date,
        );

        if(isset($post_data['status']) && $post_data['status']!="")
        {
            $campaign_data['status'] = $post_data['status'];
            if($post_data['status']==1 && $post_data['expiry_date'] > format_date())
            {
                $campaign_data['is_unpublished'] =0; 
            }
        }

        $update = $this->Affiliate_model->update_table(CAMPAIGN,$campaign_data,["campaign_id"=>$post_data['campaign_id']]);
        if($update)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = "Campaign updated successfully";
        }else{
            $this->api_response_arry['message'] = "No change";
        }

        $this->api_response();

    }

    /**
     * get affiliate campaign detail
     */
    public function get_campaign_details_post()
    {
        $this->form_validation->set_rules('affiliate_id', 'Affiliate ID', 'trim|required');
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $campaigns = $this->Affiliate_model->get_campaign_details($post_data);
        if($campaigns)
        {
            $this->api_response_arry['message'] = "Affiliate Campaign List";
            $this->api_response_arry['data'] = $campaigns;
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = "No record found.";
        }
        $this->api_response();
    }

    public function get_campaign_details_get()
    {
        $_POST = $this->input->get();
        if(!isset($_POST['csv']))
        {
            $_POST['csv']=true;
        }
        $result = $this->Affiliate_model->get_campaign_details($_POST);
        foreach($result['result'] as $key=>$res)
        {
        if($res['deposit_comm']>0)
        {
        $result['result'][$key]['deposit_comm'] = $result['result'][$key]['deposit_comm'].'%';
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
     * tracking a single url 
     */
    public function track_single_url_post()
    {
        $this->form_validation->set_rules('campaign_id', 'Campaign ID', 'trim|required|callback_is_campaign_exist');
        
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $url_details = $this->Affiliate_model->track_single_url($post_data);
        if($url_details)
        {
            $this->api_response_arry['message'] = "URL tracking List";
        }else{
            $this->api_response_arry['message'] = "No details found for this campaign.";
        }
        $this->api_response_arry['data'] = $url_details;
        $this->api_response(); 
    }

    /**
     * tracking a single url 
     */
    public function track_single_url_get()
    {
        $_POST = $this->input->get();
        if(!isset($_POST['csv']) || $_POST['csv']==false)
        {
            $_POST['csv'] = true;
        }

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
                $name = 'campaign_detail.csv';
				force_download($name, $data);
			}
			else{
				$result = "no record found";
				$this->load->helper('download');
				$this->load->helper('csv');
				$data = array_to_csv($result);
				$name = 'campaign_detail.csv';
				force_download($name, $result);
			}
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
            $this->api_response_arry['message'] = "No details found for this user.";
        }
        $this->api_response();
    }

    /**
     * tracking a single url 
     */
    public function get_single_user_details_get()
    {
        $_POST = $this->input->get();
        if(!isset($_POST['csv']) || $_POST['csv']==false)
        {
            $_POST['csv'] = true;
        }

		$result = $this->Affiliate_model->get_single_user_details($_POST);
					
			if(!empty($result['result'])){
				$result =$result['result'];
				$header = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('download');
                $this->load->helper('csv');
                $data = array_to_csv($result);

                $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
                $name = 'user_detail.csv';
				force_download($name, $data);
			}
			else{
				$result = "no record found";
				$this->load->helper('download');
				$this->load->helper('csv');
				$data = array_to_csv($result);
				$name = 'user_detail.csv';
				force_download($name, $result);
			}
    }
}
?>