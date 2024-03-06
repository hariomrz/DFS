<?php

class Affiliate extends Common_Api_Controller {

    function __construct() {
        parent::__construct();        
    }
    
    /*  
     * Used for get affiliate master data
     */
    function get_master_data_post($call_from_api = true) {
        $result                     = array();
        $result["total_joined"]     = 0;
        $result["total_bonus_cash"] = 0;
        $result["total_real_cash"]  = 0;
        $result["total_coin_earned"]  = 0;
        $result["refer_by"]  = array();

        if ($this->user_id) {
            $this->load->model("Affiliate_model");
            $result = $this->Affiliate_model->get_affiliate_user_detail();
            if(empty($result["total_joined"])) {
                $result["refer_by"]  = $this->Affiliate_model->get_refer_by_details($this->user_id);
            }
        }
        
        if ($call_from_api) {
            $this->api_response_arry['data'] = $result;
            $this->api_response();
        } else {
            return $result;
        }
    }
    
    
    /*
     * Used to get user referral list
     */
    function get_referral_list_post() {
        $post_data = $this->post();
        $this->load->model("Affiliate_model");        
        $result = $this->Affiliate_model->get_referral_list($post_data);        
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }
    
    /*
     * Used to get user referral list
     */
    function get_user_earning_by_friend_post() {
        $this->form_validation->set_rules('user_id', $this->lang->line("user_id"), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $user_id = $this->input->post("user_id");
        $this->load->model("Affiliate_model");        
        $result = $this->Affiliate_model->get_user_earning_by_friend($user_id);        
        
        $affiliate_cache_key = 'aff_master_type_14';
        $affililate_master_detail = $this->get_cache_data($affiliate_cache_key);
        if (!$affililate_master_detail) {
            $affililate_master_detail = $this->Affiliate_model->get_single_row('*', AFFILIATE_MASTER, array("affiliate_type" => 14));
            $this->set_cache_data($affiliate_cache_key, $affililate_master_detail, REDIS_30_DAYS);
        }
        $result['friends_deposit']['affiliate_description'] = $affililate_master_detail['affiliate_description'];
        $result['friends_deposit']['max_earning_amount'] = $affililate_master_detail['max_earning_amount'];
        $result['friends_deposit']['real_amount'] = $affililate_master_detail['real_amount'];
        $result['friends_deposit']['amount_type'] = $affililate_master_detail['amount_type'];
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    function get_affiliate_master_data_post()
    {
        $affiliate_master_cache_key = "affiliate_master_data";
        $affiliate_master_data = $this->get_cache_data($affiliate_master_cache_key);

        if(empty($affiliate_master_data))
        {
            $this->load->model("Affiliate_model");        
            $affiliate_master_data = $this->Affiliate_model->get_affiliate_master_data();  
            $this->set_cache_data($affiliate_master_cache_key,$affiliate_master_data,REDIS_2_DAYS); 
        }

        $this->api_response_arry['data'] = $affiliate_master_data;     
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK; 
        $this->api_response();
 
    }

    function affiliate_action_on_email_verify()
    {
        $affililate_detail = $this->Auth_model->get_single_row('*', AFFILIATE_MASTER,"affiliate_type=7 OR  affiliate_type=13");


    }

    public function add_affiliate_activity_post()
	{
		$this->form_validation->set_rules('user_id', $this->lang->line("user_id"), 'trim|required');
		$this->form_validation->set_rules('source_type', $this->lang->line("source_type"), 'trim|required');
		$this->form_validation->set_rules('affiliate_type', $this->lang->line("affiliate_type"), 'trim|required');
		$this->form_validation->set_rules('amount_type', $this->lang->line("amount_type"), 'trim|required');
		
		//affiliate entry for : 0 = Non referral users,1= Referral users 
		$is_referral = $this->input->post("is_referral");
		$post_data 	 = $this->input->post();
	

		if($this->input->post("affiliate_type") == 1)
		{
			$this->form_validation->set_rules('friend_id', $this->lang->line("friend_id"), 'trim|required');
		}
		
		if ($this->input->post("affiliate_type") == 2)
		{
			$this->form_validation->set_rules('contest_id', $this->lang->line("contest_id"), 'trim|required');
		}
		
		if ($this->input->post("affiliate_type") == 3)
		{
			$this->form_validation->set_rules('collection_id', $this->lang->line("collection_id"), 'trim|required');
		}

		//apply form validations for affiliate type : 6 signup bonus for all users(w/o referral)
		if ($this->input->post("affiliate_type") == 6)
		{
			$this->form_validation->set_rules('friend_id', $this->lang->line("friend_id"), 'trim|required');
		}

		//apply form validations for affiliate type : 7 email verification bonus for all users(w/o referral)
		if ($this->input->post("affiliate_type") == 7)
		{
			$this->form_validation->set_rules('friend_id', $this->lang->line("friend_id"), 'trim|required');
		}

		//apply form validations for affiliate type : 9 pan verification bonus for all users(w/o referral)
		if ($this->input->post("affiliate_type") == 9)
		{
			$this->form_validation->set_rules('friend_id', $this->lang->line("friend_id"), 'trim|required');
		}


		if (!$this->form_validation->run())
		{
			$this->send_validation_errors();
		}

		$affiliate_user_data = array();

		$affiliate_user_data["user_id"] = $this->input->post("user_id");
		$affiliate_user_data["source_type"] = $this->input->post("source_type");
		$affiliate_user_data["affiliate_type"] = $this->input->post("affiliate_type");
		$affiliate_user_data["status"] = 1;
		$affiliate_user_data["created_date"] = format_date();

		if(!empty($this->input->post("friend_id")))
		{
			$affiliate_user_data["friend_id"] = $this->input->post("friend_id");
		}

		if(!empty($this->input->post("collection_id")))
		{
			$affiliate_user_data["collection_id"] = $this->input->post("collection_id");
		}

		if(!empty($this->input->post("contest_id")))
		{
			$affiliate_user_data["contest_id"] = $this->input->post("contest_id");
		}
		
		if(!empty($this->input->post("bouns_condition")))
		{
			$affiliate_user_data["bouns_condition"] = $this->input->post("bouns_condition");
		}

		if(!empty($this->input->post("friend_email")))
		{
			$affiliate_user_data["friend_email"] = $this->input->post("friend_email");
		}

		if(!empty($this->input->post("friend_mobile")))
		{
			$affiliate_user_data["friend_mobile"] = $this->input->post("friend_mobile");
		}

		//check for all types of bonus : real,bonus,coin
		$affiliate_user_data['user_bonus_cash'] = (!empty($post_data['user_bonus_cash'])) ? $post_data['user_bonus_cash'] : 0;
		$affiliate_user_data['user_real_cash'] = (!empty($post_data['user_real_cash'])) ? $post_data['user_real_cash'] : 0;
		$affiliate_user_data['user_coin'] = (!empty($post_data['user_coin'])) ? $post_data['user_coin'] : 0;
		$affiliate_user_data['friend_bonus_cash'] = (!empty($post_data['friend_bonus_cash'])) ? $post_data['friend_bonus_cash'] : 0;
		$affiliate_user_data['friend_real_cash'] = (!empty($post_data['friend_real_cash'])) ? $post_data['friend_real_cash'] : 0;
		$affiliate_user_data['friend_coin'] = (!empty($post_data['friend_coin'])) ? $post_data['friend_coin'] : 0; 

		$result = $this->Affiliate_model->add_affiliate_activity($affiliate_user_data);
				
		$this->api_response_arry['data']			= $result;
		$this->api_response_arry['message']			= "Successfully Added Affiliate";
		$this->api_response();
	}
}