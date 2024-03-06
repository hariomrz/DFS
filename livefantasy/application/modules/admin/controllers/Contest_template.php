<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Contest_template extends Common_Api_Controller {
	public function __construct()
	{
		parent::__construct();
		$_POST = $this->post();
		$this->load->model('Contest_template_model');
		$this->admin_lang = $this->lang->line('Contest');
		$allow_livefantasy =  isset($this->app_config['allow_livefantasy'])?$this->app_config['allow_livefantasy']['key_value']:0;
        if($allow_livefantasy == 0)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Live fantasy not enabled";
            $this->api_response_arry['global_error'] = "Module Disable";
            $this->api_response();
        }
	}

	public function get_contest_template_list_post()
	{
		$post_data = $this->input->post();
		$data = $this->Contest_template_model->get_contest_template_list($post_data);
		$contest_template = array();
		if(isset($data['result'])){
			foreach($data['result'] as $template){
				if(!isset($contest_template[$template['group_id']]) || empty($contest_template[$template['group_id']])){
					$contest_template[$template['group_id']] = array("group_id"=>$template['group_id'],"group_name"=>$template['group_name'],"template_list"=>array());
				}
				$template['prize_distibution_detail'] = json_decode($template['prize_distibution_detail']);
				$template['template_leagues'] = explode(",", $template['template_leagues']);
				$contest_template[$template['group_id']]['template_list'][] = $template;
			}
		}
		$contest_template = array_values($contest_template);
		if(!empty($data)){
			$this->api_response_arry['data'] = $contest_template;
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response();
		}
	}

	public function get_all_master_data_post()
	{ 
		
		$this->load->model(array('Merchandise_model'));
		$group_list	=$this->Merchandise_model->get_all_group_list(array("type"=>"admin"));
		$merchandise_list = $this->Merchandise_model->get_merchandise_list();
		
		$multiple_lineup = array();
		$multiple_lineup[] = array("label"=>'1',"value"=>'1');
		$currency_type = array(array("label"=>CURRENCY_CODE,"value"=>"1"));
		$prize_type = array();
		$prize_type[] = array("label"=>"Bonus Cash","value"=>"0");
		$prize_type[] = array("label"=>"Real Cash","value"=>"1");
		$allow_coin_system =  isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
		if($allow_coin_system == 1){
			$prize_type[] = array("label"=>"Coins","value"=>"2");
			$currency_type[] = array("label"=>"Coins","value"=>"2");
		}
		$prize_type[] = array("label"=>"Merchandise","value"=>"3");
		
		$result = array(
					'group_list'				=> $group_list,
					'merchandise_list'			=> $merchandise_list,
					'multiple_lineup'			=> $multiple_lineup,
					'currency_type'				=> $currency_type,
					'max_bonus_allowed'			=> MAX_BONUS_PERCEN_USE,
					'site_rake'					=> DEFAULT_SITE_RAKE,
					'prize_type'				=> $prize_type
				);

		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	public function create_template_post()
	{       
		if($this->input->post())
		{
			$game_data = $this->input->post();
			$this->form_validation->set_rules('sports_id', $this->lang->line('sport'), 'trim|required|is_natural_no_zero');
			$this->form_validation->set_rules('group_id', $this->lang->line('group'), 'trim|required|is_natural_no_zero');
			$this->form_validation->set_rules('template_name', 'template name', 'trim|required');
			$this->form_validation->set_rules('minimum_size', 'minimum size', 'trim|required|is_natural_no_zero');
			$this->form_validation->set_rules('size', 'size', 'trim|required|is_natural_no_zero');
			$this->form_validation->set_rules('multiple_lineup', 'multiple lineup', 'trim|required');
			$this->form_validation->set_rules('entry_fee', $this->lang->line('entry_fee'), 'trim|required');
			$this->form_validation->set_rules('site_rake', $this->lang->line('site_rake'), 'trim|required');
			$this->form_validation->set_rules('max_bonus_allowed', $this->lang->line('max_bonus_allowed'), 'trim|required');
			$this->form_validation->set_rules('prize_type', $this->lang->line('prize_type'), 'trim|required');
			$this->form_validation->set_rules('prize_pool', $this->lang->line('prize_pool'), 'trim|required');
			$this->form_validation->set_rules('entry_fee_type', 'entry fee type', 'trim|required');
			
			if($this->input->post('prize_pool_type') == '0')
			{
				$this->form_validation->set_rules('master_contest_type_id', $this->lang->line('number_of_winner_id'), 'trim|required|is_natural_no_zero');
			}

			if(isset($game_data['set_sponsor']) && $game_data['set_sponsor'] == 1)
			{
				$this->form_validation->set_rules('sponsor_name', $this->lang->line("sponsor_name"),'trim|max_length[60]');
	            $this->form_validation->set_rules('sponsor_logo', $this->lang->line("sponsor_logo"),'trim|required|max_length[50]');
	            $this->form_validation->set_rules('sponsor_contest_dtl_image', $this->lang->line("sponsor_contest_dtl_image"),'trim|required|max_length[50]');
	            $this->form_validation->set_rules('sponsor_link', $this->lang->line("sponsor_link"),'trim|max_length[255]');
			}

			if (!$this->form_validation->run()) 
			{
				$this->send_validation_errors();
			}

			// if(empty($game_data['league_id'])){
			// 	$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>"Please select atleast one league.") , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
			// }

			$multiple_lineup = $this->input->post('multiple_lineup');
			$prize_pool_type = $this->input->post('prize_pool_type');
			$prize_pool = 0;
			$guaranteed_prize = '0';
			if($game_data['prize_pool_type'] == '1'){
				$guaranteed_prize = '1';
			}else if($game_data['prize_pool_type'] == '2'){
				$guaranteed_prize = '2';
				$game_data['site_rake'] = 0;
			}

            if($multiple_lineup && $multiple_lineup > $game_data['size']){
                $this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>$this->lang->line('invalid_game_multiple_lineup_size')) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            }

			if($prize_pool_type == '0')
		   	{
		    	$game_data['is_custom_prize_pool'] = 0;
		   	}
		   	else if($prize_pool_type == '1' || $prize_pool_type == '2')
		   	{
		    	$game_data['is_custom_prize_pool'] = 1;
		    	$game_data['master_contest_type_id'] = 0;
		   	}

		   	$payout_data = isset($game_data['payout_data']) ? $game_data['payout_data'] : array();
		   	$max_winners = array_column($payout_data, "max");
		   	$max_winners = max($max_winners);
		   	if($max_winners > $game_data['size']){
		   		$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = "You can't define winner more then size.";
				$this->api_response();
		   	}
		   	
		   	if($game_data['prize_pool_type'] == '1' || $game_data['prize_pool_type'] == '2')
			{
				$prize_pool = $game_data['prize_pool'];
				$payout_data = isset($game_data['payout_data']) ? $game_data['payout_data'] : array();
			}
			$merchandise_ids = array_column($payout_data, "prize_type");
			if(in_array("3", $merchandise_ids) && $game_data['is_tie_breaker'] != 1){
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = $this->lang->line('invalid_tie_breaker_status');
				$this->api_response();
			}

            //change values for percentage case
			$max_prize_pool = 0;
            foreach($payout_data as $key=>$prize){
                $amount = $prize['amount'];
                if(isset($game_data['prize_value_type']) && $game_data['prize_value_type'] == 1  && $prize['prize_type'] != "3"){
                    $payout_data[$key]['per'] = $prize['amount'];
                    $person_count = ($prize['max'] - $prize['min']) + 1;
                    $per_person = ((($prize_pool * $prize['amount']) / 100) / $person_count);
                    $amount = $per_person;
                    $payout_data[$key]["amount"] = number_format($per_person,"2",".","");
                }
                if(isset($prize['prize_type']) && $prize['prize_type'] == 1){
                    if(isset($prize['max_value'])){
                        $mx_amount = $prize['max_value'];
                    }else{
                        $mx_amount = $amount;
                    }
                    $max_prize_pool = $max_prize_pool + $mx_amount;
                }
            }

			$payout_data = json_encode($payout_data);
			$contest_data = array(
								"sports_id"					=> $game_data['sports_id'],
								"league_id"					=> $game_data['league_id'],
								"group_id"					=> $game_data['group_id'],
								"template_name"				=> $game_data['template_name'],
								"template_title"			=> isset($game_data['template_title']) ? $game_data['template_title'] : "",
								"template_description"		=> isset($game_data['template_description']) ? $game_data['template_description'] : "",
								"minimum_size"				=> $game_data['minimum_size'],
								"size"						=> $game_data['size'],
								"multiple_lineup"			=> $multiple_lineup,
								"entry_fee"					=> $game_data['entry_fee'],
								"site_rake"					=> $game_data['site_rake'],
								"max_bonus_allowed"			=> $game_data['max_bonus_allowed'],
								"currency_type"				=> $game_data['entry_fee_type'],
								"prize_type"				=> $game_data['prize_type'],
								"prize_pool"				=> $prize_pool,
								"max_prize_pool"			=> $max_prize_pool,
								"prize_distibution_detail"	=> $payout_data,
								"master_contest_type_id"	=> $game_data['master_contest_type_id'],
								"guaranteed_prize"			=> $guaranteed_prize,
								"is_custom_prize_pool"		=> $game_data['is_custom_prize_pool'],
                                "is_auto_recurring"			=> isset($game_data['is_auto_recurring']) && $game_data['is_auto_recurring'] ? 1 : 0,
								"is_tie_breaker"			=> $game_data['is_tie_breaker'],
                                "prize_value_type"			=> isset($game_data['prize_value_type']) ? $game_data['prize_value_type'] : 0,
								"status"					=> 1,
								"added_date"				=> format_date()
							);

			//add sponsor if checked 
			if(isset($game_data['set_sponsor']) && $game_data['set_sponsor'] == 1)
			{
				$contest_data['sponsor_name'] = $game_data['sponsor_name'];
            	$contest_data['sponsor_logo'] = $game_data['sponsor_logo'];
            	$contest_data['sponsor_link'] = (isset($game_data['sponsor_link']) && $game_data['sponsor_link'] != '') ? $game_data['sponsor_link'] : NULL;
			}
		    //consolation prize check
			if(isset($game_data['consolation_prize_type']) && in_array($game_data['consolation_prize_type'],array(0,2)) && !empty($game_data['consolation_prize_value'])  )				
			{
				$contest_data['consolation_prize'] = json_encode(array(
					'prize_type' => $game_data['consolation_prize_type'],
					'value' => $game_data['consolation_prize_value']
				));
			}

			if(!isset($game_data['contest_template_id']))
			{
				if($game_data['entry_fee'] == 0 && $prize_pool > 0 && $contest_data['is_auto_recurring']==1) 
				{
					$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['status']			= FALSE;
					$this->api_response_arry['message']			= $this->lang->line('auto_recurrent_create_error');
					$this->api_response();
				}

				$league_ids = $contest_data['league_id'];
				unset($contest_data['league_id']);

				$this->load->model('Contest_template_model'); 
				$contest_template_id = $this->Contest_template_model->save_template($contest_data);

				if($contest_template_id){
					//save template league data
					foreach($league_ids as $league_id){
						$league_data = array();
						$league_data['contest_template_id'] = $contest_template_id;
						$league_data['league_id'] = $league_id;
						$league_data['date_created'] = format_date();
						$this->Contest_template_model->save_template_league($league_data);
					}
				}
			}

			$this->api_response_arry['data'] = $contest_template_id;
			$this->api_response_arry['message'] = 'template created successfully.';
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('invalid_parameter');
			$this->api_response();
		}
	}

	public function delete_template_post()
	{
		$this->form_validation->set_rules('contest_template_id', 'Contest Template Id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$post_data = $this->input->post();
		$this->load->model('Contest_template_model'); 
		$result = $this->Contest_template_model->delete_template($post_data['contest_template_id']);
		if($result)
		{
			$this->api_response_arry['message'] = "Template deleted successfully.";
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('no_change');
			$this->api_response();
		}
	}

	public function apply_template_to_league_post()
	{
		$this->form_validation->set_rules('league_id', 'League Id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		if(empty($post_data['selected_templates'])){
			$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>"Please select atleast one template.") , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		
		$this->load->model('Contest_template_model');
		$result = $this->Contest_template_model->apply_template_to_league($post_data);
		if($result)
		{
			$this->api_response_arry['message'] = "Template applied for selected league successfully.";
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('no_change');
			$this->api_response();
		}
	}

	/**
	 * to get content of coppied contest template
	 * @param contest_template_id
	 * @return array
	 */
	public function get_coppied_contest_template_details_post(){
		$this->form_validation->set_rules('contest_template_id', 'Contest template id', 'trim|required');
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
	   	}

	   	$post_data = $this->input->post();
	   	$this->load->model('Contest_template_model');
	   	$contest_template_data = $this->Contest_template_model->get_contest_template_by_id($post_data['contest_template_id']);
	   	if(!$contest_template_data){
			$this->api_response_arry['message'] = "No record found";
	   	}else{
	      	$contest_template_data['prize_distibution_detail'] = json_decode($contest_template_data['prize_distibution_detail']);
		   	$contest_template_data['template_leagues'] = explode(",", $contest_template_data['template_leagues']);
		   	$this->api_response_arry['data'] = $contest_template_data;
	   	}
   		$this->api_response();
	}

	/**
     * For uploading sponsor image
     * @param
     * @return json array
     */
    public function do_upload_sponsor_post()
    {   
        $post_data = $this->input->post();

        $dir                = APP_ROOT_PATH.UPLOAD_DIR;
        
        $temp_file_image    = $_FILES['file']['tmp_name'];
        $ext                = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

        if( strtolower( IMAGE_SERVER ) == 'remote' ){
            $file_name = $this->do_upload_process($ext);
            $temp_file = $dir.$file_name;
        }
        $vals = @getimagesize($temp_file_image);
        $width = $vals[0];
        $height = $vals[1];
        $subdir = ROOT_PATH.SPONSOR_IMAGE_DIR;
		$s3_dir = SPONSOR_IMAGE_DIR;
		$expected_width = '1036';
		$expected_hight = '60';
        if ($height != $expected_hight || $width != $expected_width) {
            $invalid_size = str_replace("{max_height}",$expected_hight,$this->lang->line('invalid_sponsor_image_size'));
            $invalid_size = str_replace("{max_width}",$expected_width,$invalid_size);
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["message"] = $invalid_size;
            $this->api_response();
        }

        if( strtolower( IMAGE_SERVER ) == 'local')
        {
            $this->check_folder_exist($dir);
            $this->check_folder_exist($subdir);
        }

        $file_name = time().".".$ext ;
        $filePath = $s3_dir.$file_name;
        if( strtolower( IMAGE_SERVER ) == 'remote' )
        {
        	try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
                    @unlink($temp_file);
        			if(!empty($post_data['previous_image'])){
        				$post_data['sponsor_logo'] = $post_data['previous_image'];
        				$this->remove_sponsor_logo($post_data);
        			}
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
                    $this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('image_upload_error');
                $this->api_response();
            }
        } else {
            $config['allowed_types']    = 'jpg|png|jpeg|gif|PNG';
            $config['max_size']         = '2000';
            $config['max_width']        = '2400';
            $config['max_height']       = '1200';
            $config['min_width']        = '64';
            $config['min_height']       = '42';
            $config['upload_path']      = $dir;
            $config['file_name']        = time();

            $this->load->library('upload', $config);
            if ( ! $this->upload->do_upload('file'))
            {
                $error = $this->upload->display_errors();
                $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry["message"] = strip_tags($error);
                $this->api_response();
            }
            else
            {
                $upload_data = $this->upload->data();
                $this->api_response_arry["data"] = array('image_name' =>IMAGE_PATH.$s3_dir.$file_name ,'image_url'=> $subdir);
                $this->api_response();
            }
        }       
	}
	
    /**
    * [do_upload_process description]
    * Summary :- internal function used to upload sponsor to local folder.
    */
    public function do_upload_process($ext)
    {
        $dir                        = APP_ROOT_PATH.UPLOAD_DIR;
        $config['image_library']    = 'gd2';
        $config['allowed_types']    = 'jpg|png|jpeg|gif|PNG';
        $config['max_size']         = '2000';
        $config['min_width']        = '36';//64
        $config['min_height']       = '30';//42
        $config['max_width']        = '2400';
        $config['max_height']       = '1200';
        $config['upload_path']      = $dir;
        $config['file_name']        = rand(1,1000).time().'.'.$ext;

        $this->load->library('upload', $config);
        if ( ! $this->upload->do_upload('file'))
        {
            $error = $this->upload->display_errors();
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["message"] = strip_tags($error);
            $this->api_response();
        }
        else
        {
            return $config['file_name'];
        }
    }

    /**
     * Remove uploaded sponsor image and in case of edit remove from table as well 
     * @param
     * @return json array
     */
    public function remove_sponsor_logo($post_data)
    {
		if(isset($post_data['sponsor_contest_dtl_image']))
		{
			$image_name = $post_data['sponsor_contest_dtl_image'];
		}
		else
		{
			$image_name = $post_data['sponsor_logo'];
		}
    	$dir = ROOT_PATH.SPONSOR_IMAGE_DIR;
    	$s3_dir = SPONSOR_IMAGE_DIR;
    	$dir_path    = $s3_dir.$image_name;
    	if( strtolower( IMAGE_SERVER ) == 'remote' )
    	{
    		try{
                $data_arr = array();
                $data_arr['file_path'] = $dir_path;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_deleted = $upload_lib->delete_file($data_arr);
                if($is_deleted){
                    return true;
                }else{
                	return false;
                }
            }catch(Exception $e){
                return false;
            }
		}
		return true;
	}

	public function get_fixture_template_post()
	{
		$this->form_validation->set_rules('league_id', 'League id', 'trim|required');
		$this->form_validation->set_rules('season_game_uid', 'Season game id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$post_data = $this->input->post();
		$this->load->model('Contest_template_model');
		$data = $this->Contest_template_model->get_fixture_template($post_data);
		//echo "<pre>";print_r($data);die;
		$contest_template = array();
		if(isset($data)){
			foreach($data as $template){
				if(!isset($contest_template[$template['group_id']]) || empty($contest_template[$template['group_id']])){
					$contest_template[$template['group_id']] = array("group_id"=>$template['group_id'],"group_name"=>$template['group_name'],"template_list"=>array());
				}
				$template['prize_distibution_detail'] = json_decode($template['prize_distibution_detail']);
				$contest_template[$template['group_id']]['template_list'][] = $template;
			}
		}
		$contest_template = array_values($contest_template);
		if($data){
			$this->api_response_arry['data'] = $contest_template;
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['message'] = "No template available for this match.";
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response();
		}
	}

	/**
	 * method to get list of groups of contest
	 */
	public function get_group_post(){
		$result = $this->Contest_template_model->get_group();
		
		if(!empty($result)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message'] = $this->lang->line('get_group_success');
			$this->api_response_arry['data'] = $result;
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['data']          = array();
			$this->api_response_arry['error_message'] = $this->lang->line('get_group_error');
			$this->api_response();
		}
	}

	/**
	 * method to delete contest group
	 */

	 public function inactive_group_post(){
		if($this->input->post()){
			$this->form_validation->set_rules('group_id', $this->lang->line('group_id'), 'trim|required');

			if (!$this->form_validation->run()) 
			{
				$this->send_validation_errors();
			}
			$check = $this->Contest_template_model->check_group_in_contest();
			if(empty($check['count'])){
				$update_data = array(
					"status"=>0,
				);
				$update = $this->Contest_template_model->update_group($update_data);
				if($update){
					$group_list_cache_key = "group_list";
					$this->delete_cache_data($group_list_cache_key);
					$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
					$this->api_response_arry['message'] = $this->lang->line('delete_group_success');
					$this->api_response_arry['data'] = array();
					$this->api_response();
				}
				else
				{
					$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['data']          = array();
					$this->api_response_arry['global_error'] = $this->lang->line('delete_group_error');
					$this->api_response();
				}
			} 
			else{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['data']          = array();
				$this->api_response_arry['global_error'] = $this->lang->line('cannot_delete_group');
				$this->api_response();
			}
	 	}
	}
	
	/**
	 * update group of contest on basis of group_id
	 */
	public function update_group_post(){
		
		$this->form_validation->set_rules('group_name', $this->lang->line('group_name'), 'trim|required');
		$this->form_validation->set_rules('description', $this->lang->line('description_val'), 'trim|required');
		$this->form_validation->set_rules('icon', $this->lang->line('icon_val'), 'trim|required');
		$this->form_validation->set_rules('group_id', $this->lang->line('group_id'), 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$check_duplicate = $this->Contest_template_model->get_single_row ('group_name', MASTER_GROUP, $where = ["group_id !="=>$post_data['group_id'],"group_name"=>$post_data['group_name']]);
		if(!$check_duplicate){
			$update_data = array(
				"group_name"=>$post_data['group_name'],
				"description"=>$post_data['description'],
				"icon"=>$post_data['icon']
			);			
			
			$update = $this->Contest_template_model->update_group($update_data);
			if($update){
				$group_list_cache_key = "group_list";
				$this->delete_cache_data($group_list_cache_key);
				$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['message'] = $this->lang->line('group_update_success');
				$this->api_response_arry['data'] = array();
				$this->api_response();
			}
			else
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['data']          = array();
				$this->api_response_arry['error_message'] = $this->lang->line('group_update_error');
				$this->api_response();
			}
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['data']          = array();
			$this->api_response_arry['error_message'] = $this->lang->line('update_group_error');
			$this->api_response();
		}
		
	}

	/**
	 * create a new group for contest
	 */
	public function create_group_post(){
		$this->form_validation->set_rules('group_name', $this->lang->line('group_name'), 'trim|required|is_unique[master_group.group_name]');
		$this->form_validation->set_rules('description', $this->lang->line('description_val'), 'trim|required');
		$this->form_validation->set_rules('icon', $this->lang->line('icon_val'), 'trim|required');
		$this->form_validation->set_rules('sort_order', $this->lang->line('sort_order'), 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$insert_data = array(
			"group_name"=>$post_data['group_name'],
			"description"=>$post_data['description'],
			"icon"=>$post_data['icon'],
			"sort_order"=>$post_data['sort_order'],
		);
		$insert = $this->db->insert(MASTER_GROUP,$insert_data);
		if($insert){
			$group_list_cache_key = "group_list";
			$this->delete_cache_data($group_list_cache_key);
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message'] = $this->lang->line('create_group_success');
			$this->api_response_arry['data'] = array();
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['data']          = array();
			$this->api_response_arry['error_message'] = $this->lang->line('create_group_error');
			$this->api_response();
		}
		
	}
}