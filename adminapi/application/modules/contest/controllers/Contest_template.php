<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contest_template extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->admin_lang = $this->lang->line('Contest');
		$this->admin_roles_manage($this->admin_id,'dfs');
		$this->load->model(array('Contest_model','Contest_template_model'));
	}

	/**
    * Function used for get fixture template list
    * @param int $sports_id
    * @param int $collection_master_id
    * @return array
    */
    public function get_fixture_template_post()
    {
        $this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
        $this->form_validation->set_rules('collection_master_id', 'collection master id', 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $result = $this->Contest_template_model->get_fixture_template($post_data);
        $contest_template = array();
        if(isset($result)){
            foreach($result as $template){
                if(!isset($contest_template[$template['group_id']]) || empty($contest_template[$template['group_id']])){
                    $contest_template[$template['group_id']] = array("group_id"=>$template['group_id'],"group_name"=>$template['group_name'],"template_list"=>array());
                }
                unset($template['added_date']);
                unset($template['modified_date']);
                unset($template['consolation_prize']);
                $contest_template[$template['group_id']]['template_list'][] = $template;
            }
            $contest_template = array_values($contest_template);
        }
        $this->api_response_arry['data'] = $contest_template;
        $this->api_response();
    }

    public function get_all_master_data_post()
	{ 
		
		$this->load->model(array('common/Common_model','merchandise/Merchandise_model'));
		$group_list	=$this->Common_model->get_all_group_list();
		$merchandise_list = $this->Merchandise_model->get_merchandise_list();
		$multiple_lineup = array();
		for($i = 1; $i <= ALLOWED_USER_TEAM; $i++){
			$multiple_lineup[] = array("label"=>$i,"value"=>$i);
		}
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

    public function get_contest_template_list_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
		$post_data = $this->input->post();
		$data = $this->Contest_template_model->get_contest_template_list($post_data);
		$contest_template = array();
		if(isset($data['result'])){
			foreach($data['result'] as $template){
				if(!isset($contest_template[$template['group_id']]) || empty($contest_template[$template['group_id']])){
					$contest_template[$template['group_id']] = array("group_id"=>$template['group_id'],"group_name"=>$template['group_name'],"template_list"=>array());
				}
				$template['prize_distibution_detail'] = json_decode($template['prize_distibution_detail']);
				$contest_template[$template['group_id']]['template_list'][] = $template;
			}
		}
		$contest_template = array_values($contest_template);
		if(!empty($data)){
			$this->api_response_arry['data'] = $contest_template;
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['data']          = array();
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response();
		}
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
			$this->form_validation->set_rules('is_reverse', $this->lang->line('reverse'), 'trim');
			$this->form_validation->set_rules('max_bonus_allowed', $this->lang->line('max_bonus_allowed'), 'trim|required');
			$this->form_validation->set_rules('prize_type', $this->lang->line('prize_type'), 'trim|required');
			$this->form_validation->set_rules('prize_pool', $this->lang->line('prize_pool'), 'trim|required');
			$this->form_validation->set_rules('entry_fee_type', 'entry fee type', 'trim|required');
			$this->form_validation->set_rules('is_scratchwin', "Scratch & Win", 'trim');
			$this->form_validation->set_rules('is_2nd_inning', $this->lang->line('2nd_inning'), 'trim');

			
			$is_2nd_inning = 0;
			$allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
			if($allow_2nd_inning == 1 && $game_data['is_2nd_inning'])
			{
				$is_2nd_inning = 1;
			}

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

			//H2H challenge validation
			$h2h_challenge = isset($this->app_config['h2h_challenge'])?$this->app_config['h2h_challenge']['key_value']:0;
			$h2h_group_id = isset($this->app_config['h2h_challenge']['custom_data']['group_id']) ? $this->app_config['h2h_challenge']['custom_data']['group_id'] : 0;
			if($h2h_challenge && $h2h_group_id == $game_data['group_id'] && ($game_data['minimum_size'] != $game_data['size'] || $game_data['size'] != "2")){
				$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>"H2H challenge participants should be 2.") , rest_controller::HTTP_INTERNAL_SERVER_ERROR);	
			}
                        
			$multiple_lineup = $this->input->post('multiple_lineup');
			$prize_pool_type = $this->input->post('prize_pool_type');
			$prize_pool = 0;
			$guaranteed_prize = '0';
			if($game_data['prize_pool_type'] == '1'){
				$guaranteed_prize = '1';
				
			}else if($game_data['prize_pool_type'] == '2'){
				$guaranteed_prize = '2';
				$game_data['site_rake'] =0;

			}

            if($multiple_lineup && $multiple_lineup > $game_data['size']){
                $this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>$this->lang->line('invalid_game_multiple_lineup_size')) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            }

			$prize_details_inputs['entry_fee']	= $game_data['entry_fee'];
			$prize_details_inputs['site_rake']	= $game_data['site_rake'];

			if($prize_pool_type == '0')
		   	{
		    	$game_data['is_custom_prize_pool'] = 0;
		   	}
		   	else if($prize_pool_type == '1' || $prize_pool_type == '2')
		   	{
		    	$game_data['is_custom_prize_pool'] = 1;
		    	$game_data['master_contest_type_id'] = 0;
		   	}

		   	$prize_details_inputs['size']						= $game_data['minimum_size'];
		   	$prize_details_inputs['league_number_of_winner_id']	= $game_data['master_contest_type_id'];
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
			if(in_array("3", $merchandise_ids)){
				if($game_data['is_tie_breaker'] != 1){
					$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['message'] = $this->lang->line('invalid_tie_breaker_status');
					$this->api_response();
				}
			}

			$max_prize_pool = 0;
            //change values for percentage case
            foreach($payout_data as $key=>$prize){
                $amount = $prize['amount'];
                if(isset($game_data['prize_value_type']) && $game_data['prize_value_type'] == 1  && $prize['prize_type'] != "3"){
					if($game_data['is_copy'])
					{
						$payout_data[$key]['per'] = $prize['per'];
					}else{
						$payout_data[$key]['per'] = $prize['amount'];
					}
                    $person_count = ($prize['max'] - $prize['min']) + 1;
                    $per_person = ((($prize_pool * $prize['amount']) / 100) / $person_count);
                    $amount = $per_person;
					if($game_data['is_copy'])
					{
						$payout_data[$key]["amount"] = number_format($prize['amount'],"2",".","");
					}else{
						$payout_data[$key]["amount"] = number_format($per_person,"2",".","");
					}
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
			$salary_cap = SALARY_CAP;

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
								"is_reverse"			=> isset($game_data['is_reverse']) && $game_data['is_reverse']=='1' ? 1 : 0,
								"is_scratchwin"			=> isset($game_data['is_scratchwin']) && $game_data['is_scratchwin']=='1' ? 1 : 0,
								"is_2nd_inning"			=> $is_2nd_inning,
                                "is_tie_breaker"			=> $game_data['is_tie_breaker'],
                                "prize_value_type"			=> isset($game_data['prize_value_type']) ? $game_data['prize_value_type'] : 0,
								"salary_cap"				=> $salary_cap,
								"status"					=> 1,
								"added_date"				=> format_date()
							);

			//add sponsor if checked 
			if(isset($game_data['set_sponsor']) && $game_data['set_sponsor'] == 1)
			{
				$contest_data['sponsor_name'] = $game_data['sponsor_name'];
            	$contest_data['sponsor_logo'] = $game_data['sponsor_logo'];
            	$contest_data['sponsor_contest_dtl_image'] = $game_data['sponsor_contest_dtl_image'];
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

				$this->load->model('Contest_template_model');
				unset($contest_data['league_id']);
				$contest_template_id = $this->Contest_template_model->save_template($contest_data);
			}	
			else
			{
				
				$contest_data['contest_template_id'] = $game_data['contest_template_id'];
				$this->load->model('Contest_template_model'); 
				$contest_template_id = $this->Contest_template_model->update_contest($contest_data);

			}

			$this->api_response_arry['data'] = $contest_template_id;
			$this->api_response_arry['message'] = 'template created successfully.';
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['data'] = array();
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
		
		$data_arr = $this->input->post();
		$this->load->model('Contest_template_model'); 
		$contest = $this->Contest_template_model->delete_template($data_arr);

		if($contest)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['message']			= "Template deleted successfully.";
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $this->lang->line('no_change');
			$this->api_response();
		}
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
        //echo "file name : ".$temp_file;die;

        $vals = @getimagesize($temp_file_image);
        $width = $vals[0];
        $height = $vals[1];

        
        /*$ratio            = $width/$height;
        $ratio              = number_format((float)$ratio, 2, '.', '');
        $ratio              = ceil($ratio*2)/2;*/

        $subdir             = ROOT_PATH.SPONSOR_IMAGE_DIR;
		$s3_dir             = SPONSOR_IMAGE_DIR;
		
		$expected_width='1036';
		$expected_hight='60';
        if ($height != $expected_hight || $width != $expected_width) {
        
            $invalid_size = str_replace("{max_height}",$expected_hight,$this->lang->line('invalid_sponsor_image_size'));
            $invalid_size = str_replace("{max_width}",$expected_width,$invalid_size);
            //$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>$invalid_size) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
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
        $filePath     = $s3_dir.$file_name;

        /*--Start amazon server upload code--*/
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
        	/*--End amazon server upload code--*/
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
     * For uploading sponsor image
     * @param
     * @return json array
     */
    public function do_upload_sponsor_contest_dtl_post()
    {   
        $post_data = $this->input->post();

        $dir                = APP_ROOT_PATH.UPLOAD_DIR;
        
        $temp_file_image    = $_FILES['file']['tmp_name'];
        $ext                = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

        if( strtolower( IMAGE_SERVER ) == 'remote' ){
            $file_name = $this->do_upload_process($ext);
            $temp_file = $dir.$file_name;
        }
        //echo "file name : ".$temp_file;die;

        $vals = @getimagesize($temp_file_image);
        $width = $vals[0];
        $height = $vals[1];

        
        /*$ratio            = $width/$height;
        $ratio              = number_format((float)$ratio, 2, '.', '');
        $ratio              = ceil($ratio*2)/2;*/

        $subdir             = ROOT_PATH.SPONSOR_IMAGE_DIR;
		$s3_dir             = SPONSOR_IMAGE_DIR;
		$expected_width='1100';
		$expected_hight='88';

        if ($height != $expected_hight || $width != $expected_width) {
        
            $invalid_size = str_replace("{max_height}",$expected_hight,$this->lang->line('invalid_sponsor_image_size'));
            $invalid_size = str_replace("{max_width}",$expected_width,$invalid_size);
            //$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>$invalid_size) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
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
        $filePath     = $s3_dir.$file_name;

        /*--Start amazon server upload code--*/
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
        				$post_data['sponsor_contest_dtl_image'] = $post_data['previous_image'];
        					$this->remove_sponsor_logo($post_data);
        			}

        			$this->api_response_arry["data"] = $data;
        			$this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
        			$this->api_response();

        		} else {
        			

        			$error = $this->lang->line('image_upload_error');
        			$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        			$this->api_response_arry["message"] = strip_tags($error);
        			$this->api_response();
        		}
			}
			catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('image_upload_error');
                $this->api_response();
            }
        	/*--End amazon server upload code--*/
        } else {

            $config['allowed_types']    = 'jpg|png|jpeg|gif|PNG';
            $config['max_size']         = '2000';
            $config['max_width']        = '2400';
            $config['max_height']       = '1200';
            $config['min_width']        = '64';
            $config['min_height']       = '30';
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
			// print_r($check);exit;
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
		if($this->input->post()){
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
			}
		}
	}

	/**
	 * create a new group for contest
	 */
	public function create_group_post(){
		$this->db = $this->load->database('db_fantasy', TRUE);
		if($this->input->post()){
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
			$this->db = $this->load->database('db_user', TRUE);
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
	/**uplad icon image of category */
	function upload_group_icon_post() 
	{
		$pos_type = $this->post('post_type');

		$file_field_name	= 'userfile';
		if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
        }
		$dir				= ROOT_PATH.CONTEST_CATEGORY_IMG_UPLOAD_PATH;
		$s3_dir				= CONTEST_CATEGORY_IMG_UPLOAD_PATH;

		$temp_file	= $_FILES['userfile']['tmp_name'];
		// $temp_file	= __DIR__.'test.jpg';
		// $ext		= pathinfo($temp_file, PATHINFO_EXTENSION);

		$ext		= pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals		= @getimagesize($temp_file);
		$width		= $vals[0];
		$height		= $vals[1];
		
		if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > 4194304) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }

        $file_name = time() . "." . $ext;
        $allowed_ext = array('jpg', 'jpeg', 'png');
        if (!in_array(strtolower($ext), $allowed_ext)) {
            $error_msg = sprintf($this->lang->line('invalid_image_ext'), implode(', ', $allowed_ext));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $error_msg);
            $this->api_response();
        }

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
		}
		
		$file_name = time() . "." . $ext;
		$filePath     = $s3_dir.$file_name;
		//Start amazon server upload code
		if (strtolower(IMAGE_SERVER) == 'remote')
		{
			try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $image_path = IMAGE_PATH.$filePath;
					$return_array = array('image_url' => $image_path, 'file_name' => $file_name);
					$this->api_response_arry['message'] = $this->lang->line('icon_upload_success');
					$this->api_response_arry['data'] = $return_array;
					$this->api_response();
                }

            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response(); 
            }
		}
		else
		{
			$config['allowed_types'] = 'jpg|png|jpeg|gif';
			$config['max_size'] = '4048'; //204800
			$config['max_width'] = '1024';
			$config['max_height'] = '1000';
			$config['upload_path'] = $dir;
			$config['file_name'] = $file_name;

			$this->load->library('upload', $config);

			if (!$this->upload->do_upload($file_field_name))
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = strip_tags($this->upload->display_errors());
				$this->api_response();
			}
			else
			{
				$uploaded_data = $this->upload->data();
				$image_path =  CONTEST_CATEGORY_IMG_UPLOAD_PATH . $uploaded_data['file_name'];
				
				$data = array(
							'image_url'=> $image_path,
							'file_name'=> $uploaded_data['file_name']
					);
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['message'] = $this->lang->line('icon_upload_success');
				$this->api_response_arry['data']			= $data;
				$this->api_response();
				
			}
			
		}		
	}

	public function remove_group_icon_post()
	{
		$icon_name = $this->Contest_template_model->get_single_row ('icon', MASTER_GROUP, $where = ["group_id"=>$this->input->post('group_id')]);
		$image_name = $icon_name['icon'];
		$dir = ROOT_PATH.CONTEST_CATEGORY_IMG_UPLOAD_PATH;
		$s3_dir = CONTEST_CATEGORY_IMG_UPLOAD_PATH;
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
		unlink($dir. $image_name);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['message']			= $this->lang->line('image_removed');
		$this->api_response();
	}

	/**
	 * to get content of coppied contest template
	 * @param contest_template_id
	 */
	public function get_coppied_contest_template_details_post(){
		$this->form_validation->set_rules('contest_template_id', 'Contest template id', 'trim|required');
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
	   	}
		   
	   	$contest_template_data = $this->Contest_template_model->get_contest_template_by_id();
	   	if(!$contest_template_data){
			$this->api_response_arry['message'] = "No record found";
			$this->api_response_arry['data'] = array();
	   	}else{
		   $contest_template_data['result']['prize_distibution_detail'] = json_decode($contest_template_data['result']['prize_distibution_detail']);
		   $this->api_response_arry['message'] = "";
		   $this->api_response_arry['data'] = $contest_template_data['result'];
	   	}
	   	$this->api_response();
	}


	/**
	* Function used for update league featured status	
	* @param int $contest_template_id
	* @param int $auto_published
	* @return array
	*/ 
	public function update_auto_publish_status_post()
	{		
		$this->form_validation->set_rules('contest_template_id', 'contest template id','trim|required');
		$this->form_validation->set_rules('auto_published', 'auto published','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }		
		$post_data = $this->input->post();	  
		$result = $this->Contest_template_model->update_auto_publish_status($post_data);
		if($result){	
			$this->api_response_arry['message'] = "Auto publish status update successfully.";
	        $this->api_response();
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "Something went wrong while update status. please try again.";
            $this->api_response();
		}
	}
}