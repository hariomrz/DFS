<?php 

class Private_contest extends Common_Api_Controller {
     
    public $group_id = 7;//private contest group 
	public function __construct()
	{
		parent::__construct();
        $this->load->model("Private_contest_model");
	}

	/**
     * used for get create contest master data
     * @param array $post_data
     * @return array
    */
	public function create_contest_master_data_post()
	{
        $site_rake = $this->Private_contest_model->get_single_row_from_user("key_value", APP_CONFIG, array("key_name"=> 'site_rake'));
        $host_rake = $this->Private_contest_model->get_single_row_from_user("key_value", APP_CONFIG, array("key_name"=> 'host_rake'));

		$post_data = $this->input->post();
		$site_rake = $site_rake['key_value'];
		$host_rake = $host_rake['key_value'];
    	$result = array('site_rake'=>$site_rake,'host_rake'=>$host_rake);
        $prize_distribution_data = get_prize_distribution_data_for_PC();
        $result['prize_distribution_data'] = $prize_distribution_data;
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}


	/**
     * used for save user contest
     * @param array $post_data
     * @return array
    */
	public function create_user_contest_post()
	{
		if($this->app_config['allow_private_contest']['key_value'] != 1 && $this->app_config['allow_private_contest']['key_value'] != 2)
        {
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('disable_private_contest');
			$this->api_response();
		}

		
		$this->form_validation->set_rules('currency_type', $this->lang->line('currency_type'), 'trim|required');
		$this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
		$this->form_validation->set_rules('prize_type', $this->lang->line('prize_type'), 'trim|required');
		
		$this->form_validation->set_rules('size', $this->lang->line('size'), 'trim|required|is_natural_no_zero|callback_league_size_check|callback_check_participant_size');
		$this->form_validation->set_rules('game_name', $this->lang->line('game_name'), 'trim|required');
		$this->form_validation->set_rules('game_desc', $this->lang->line('game_desc'), 'trim|required');
		$this->form_validation->set_rules('entry_fee', $this->lang->line('entry_fee'), 'trim|required|max_length[4]');
		$this->form_validation->set_rules('number_of_winners',$this->lang->line('number_of_winners'), 'trim|required|is_natural_no_zero|callback_number_of_winners_check');
		$this->form_validation->set_rules('prize_distribution_detail', $this->lang->line('prize_distribution_detail'), 'trim|callback_prize_distribution_detail_check');
        $this->form_validation->set_rules('multiple_lineup', $this->lang->line('multiple_lineup'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
        $post_data = $this->input->post();

		
        $site_rake = $this->app_config['site_rake']['key_value'];
        $host_rake = $this->app_config['host_rake']['key_value'];
        $minimum_size = 2; // fixed for private contest

		$prize_pool = 0;
		// $multiple_lineup = 0;
		if($post_data['prize_type'] == 2 || $post_data['prize_type'] == 4)
		{
			$site_rake = 0;
            $host_rake = 0;
		}
    
		if($post_data['prize_type'] != 4)
		{
            $prize_pool = ($post_data["entry_fee"]*$minimum_size)-($site_rake*($post_data["entry_fee"]*$minimum_size)/100)-($host_rake*($post_data["entry_fee"]*$minimum_size)/100);

			//validate prize pool
			$this->form_validation->set_rules('prize_pool', $this->lang->line('prize_pool'), 'trim|required|callback_prize_pool_check['.$prize_pool.']');
			if (!$this->form_validation->run()) 
			{
				$this->send_validation_errors();
			}
		}
        
		$collection_info = $this->Private_contest_model->get_single_row("scheduled_date,category_id",COLLECTION,array("collection_id"=>$post_data['collection_id']));
		$scheduled_date = date("Y-m-d H:i:s",strtotime($collection_info['scheduled_date']));
		$contest_unique_id = random_string('alnum', 9);
		$contest = array(
						"contest_unique_id"        => $contest_unique_id,
						"category_id"                => $collection_info['category_id'],
                        "group_id"                 => $this->group_id,
						"collection_id"     => $post_data['collection_id'],
						"contest_name"             => $post_data['game_name'],
                        "contest_description"      => $post_data['game_desc'],
						"scheduled_date"    => $scheduled_date,
						//"contest_type"             => 1,
						"prize_distibution_detail" => json_encode($post_data["prize_distribution_detail"]),
						"size"                     => $post_data['size'],
						// "minimum_size"             => $post_data['size_min'],
                        "minimum_size"             => $minimum_size,
						"entry_fee"                => $post_data['entry_fee'],
						"prize_pool"               => $prize_pool,
						"site_rake"                => $site_rake,
                        "host_rake"                => $host_rake,
						"added_date"               => format_date(),
						"contest_access_type"      => 1,
						"user_id"                  => $this->user_id,
						"multiple_lineup"          => $post_data['multiple_lineup'],	
						"is_custom_prize_pool"     => 1	,
                        "max_bonus_allowed"        => 0	,
                        "currency_type"			   => $post_data['currency_type'],
						"prize_type"     		   => $post_data['prize_type'],
						"base_prize_details"	   => json_encode($post_data["prize_distribution_detail"])
						);

        // echo "<pre>";print_r($collection_info);echo "-=-=season_info<br>";
        // echo "<pre>";print_r($contest);echo "-=-=contest<br>";
        // die('data validated successfully');
		$contest_id = $this->Private_contest_model->save_contest($contest);
		if($contest_id) 
		{
            $post_data['contest_id'] = $contest_id;
            $this->load->model("Contest_model");
            $contest = $this->Contest_model->get_contest_detail($post_data);
			$this->api_response_arry['data'] = $contest;
			$this->api_response_arry['message'] = $this->lang->line('contest_added_success');
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('contest_added_error');
			$this->api_response();
		}		
	}

	/**
     * used for validate contest size
     * @param int $size
     * @return boolean
    */
	public function league_size_check($size) {
        $prize_type = $this->input->post("prize_type");
        if($prize_type == 4)
        {
            return TRUE;
        }

        if(!ctype_digit($size))
        {
            $this->form_validation->set_message('league_size_check', "Please provide valid participants value.");
            return FALSE;
        }

        // $this->load->model("private_contest/Private_contest_model");
        $master_data = $this->Private_contest_model->get_single_row("data_id,data_desc,user_fixed,user_lower_limit,user_upper_limit", MASTER_DATA_ENTRY, array("data_id" => 2));
        if ($size > $master_data['user_lower_limit'] || $size < $master_data["user_upper_limit"]) {
            return TRUE;
        }
        $this->form_validation->set_message('league_size_check', "Please provide size between " . $master_data['user_lower_limit'] . " and " . $master_data["user_upper_limit"] . ".");
        return FALSE;
    }

    /**
     * used for validate winner size
     * @param array post data
     * @return boolean
    */
    public function check_participant_size()
    {
        $min = $this->input->post('size_min');
        $max = $this->input->post('size');
        $prize_type = $this->input->post("prize_type");
        if($prize_type == 4)
        {
            return TRUE;
        }

        if($max > UNCAPPED_MAX_SIZE)
        {
            $this->form_validation->set_message('check_participant_size', "Max Size Can not Greater than ".UNCAPPED_MAX_SIZE.".");
            return FALSE; 
        }
        if($min > $max)
        {
            $this->form_validation->set_message('check_participant_size', "Please Provide Max Size Greater Or Equal To Min Size.");
            return FALSE; 
        }
        return TRUE;
    }

    /**
     * used for validate contest min size
     * @param array post data
     * @return boolean
    */
    public function check_participant_min_size()
    {
        $min = $this->input->post('size_min');
        if($min > 100)
        {
            $this->form_validation->set_message('check_participant_min_size', "Min Size Can not Greater than 100.");
            return FALSE; 
        }
        if($min < 2)
        {
         	$this->form_validation->set_message('check_participant_min_size', "Min Team Size Can not be less than 2");
            return FALSE; 
        }
        return TRUE;
    }

    /**
     * used for validate number of winner
     * @param array post data
     * @return boolean
    */
    public function number_of_winners_check() 
    {
        $size =$this->input->post('size_min');
        $amt = $this->input->post('prize_pool');
        $number_of_winners = $this->input->post("number_of_winners");
        if($number_of_winners > 10)
        {
            $this->form_validation->set_message('number_of_winners_check', "Please provide number of winners less then or equal to 10.");
            return FALSE;
        }

        if($amt == 0 && $number_of_winners == 1)
        {
           return TRUE;
        }

        return TRUE;

        // $prize_type = $this->input->post("prize_type");
        // echo $prize_type;echo "-=-=prize_type-=-=<br>";
        // echo $number_of_winners;echo "-=-=number_of_winners-=-=<br>";
        // echo $size;echo "-=-=size-=-=<br>";

        // die('sahjd');
        // if($number_of_winners <= $size || $prize_type == 4) 
        // {
        //     return TRUE;
        // }

        // $this->form_validation->set_message('number_of_winners_check', "Please provide number of winners less then or equal to Min Team Size.");
        // return FALSE;
    }

    /**
     * used for validate prize pool data array
     * @param array post data
     * @return boolean
    */
    public function prize_distribution_detail_check() {

        $amt = $this->input->post("prize_pool");
        if (!is_numeric($amt)) {
            $this->form_validation->set_message('prize_distribution_detail_check', "Please provide valid winning amount.");
            return FALSE;
        }
        $prize_distribution_detail = $this->post('prize_distribution_detail');
        $prize_count = count($prize_distribution_detail);
        $valid_prize = TRUE;
        foreach ($prize_distribution_detail as $key => $value) {
            # code...
            if($value['min'] != $value['max'] || $value['min'] != $key+1 || $value['max'] != $key+1 )
            {
                $valid_prize = FALSE;
                break;
            }
        }

        if($valid_prize == FALSE)
        {
            $this->form_validation->set_message('prize_distribution_detail_check', "Please provide valid prize distribution details.");
            return FALSE;
        }

        if (empty($prize_distribution_detail)) {
            $this->form_validation->set_message('prize_distribution_detail_check', "Please provide valid prize distribution.");
            return FALSE;
        }

        $number_of_winners = $this->input->post("number_of_winners");
        if ($number_of_winners > $prize_count || $number_of_winners < $prize_count) {
            $this->form_validation->set_message('prize_distribution_detail_check', "Number of winners should equal to prize distribution.");
            return FALSE;
        }

        $percent_arr = array_column($prize_distribution_detail, "per");
        $per_sum= round(array_sum($percent_arr));
        if ($per_sum > 100) {
            $this->form_validation->set_message('prize_distribution_detail_check', "Percent can not be greater than 100.");
            return FALSE;
        }
        else if($per_sum < 100)
        {
            $this->form_validation->set_message('prize_distribution_detail_check', "Percent can not be less than 100.");
            return FALSE;
        }

        $amount_arr = array_column($prize_distribution_detail, "amount");
        $winning_amt = (int)$amt;
        $sum_amt = array_sum($amount_arr);
        $sum_amt = (int)$sum_amt;
        if ($sum_amt > $winning_amt) 
        {
            $this->form_validation->set_message('prize_distribution_detail_check', "amount can not be greater than " . $amt);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * used for validate prize pool amount
     * @param decimal $amt
     * @param decimal $new_prize_pool
     * @return boolean
    */
    public function prize_pool_check($amt,$new_prize_pool) {
        if (!is_numeric($amt)) {
            $this->form_validation->set_message('prize_pool_check', "Please provide valid prize pool ");
            return FALSE;
        }

        if($amt != $new_prize_pool)
        {
            $this->form_validation->set_message('prize_pool_check', "Please provide valid prize pool ");
            return FALSE;
        }

        // $this->load->model("private_contest/Private_contest_model");
        $master_data = $this->Private_contest_model->get_single_row("data_id,data_desc,user_fixed,user_lower_limit,user_upper_limit", MASTER_DATA_ENTRY, array("data_id" => 3));
        if ($amt > $master_data['user_lower_limit'] || $amt < $master_data["user_upper_limit"]) {
            return TRUE;
        }
        $this->form_validation->set_message('prize_pool_check', "Please provide prize pool amount between " . $master_data['user_lower_limit'] . " and " . $master_data["user_upper_limit"] . ".");
        return FALSE;
    }
}