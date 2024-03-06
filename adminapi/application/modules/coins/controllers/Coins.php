<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Coins extends MYREST_Controller
{
    var $source_label_map = array();
    var $source_label_map_color = array();
    var $graph_colors = array();
    var $dailycheckin_source=144;
	function __construct()
	{
        parent::__construct();
        
        $this->graph_colors[1] ='#F8436E'; //bonus cash
        $this->graph_colors[2] ='#48BF21'; //real cash
        $this->graph_colors[3] ='#EB5E5E';//gift card 
        $this->graph_colors[4] ='#2B2E47';//other 
       
		$this->source_label_map[144]='Daily Streak coins';
        $this->source_label_map[151]='Feedback Coins';
        $this->source_label_map[52]='New Signup';
        $this->source_label_map[55]='New Signup(referral)';
        $this->source_label_map[58]='New Signup(referred)';
        $this->source_label_map[61]='PAN verifcation';
        $this->source_label_map[64]='PAN verifcation(referral)';
        $this->source_label_map[67]='PAN verifcation(referred)';
        $this->source_label_map[88]='Email Verification(referred)';
        $this->source_label_map[91]='Email Verification(referral)';
        $this->source_label_map[97]='First Deposit(referred)';
        $this->source_label_map[100]='First Deposit(referral)';
        $this->source_label_map[107]='First Deposit(referred)';
        $this->source_label_map[134]='Bank Verify';
        $this->source_label_map[143]='Bank Verification(referred)';
        $this->source_label_map[147]='PAN verifcation';
        $this->source_label_map[155]='Edit Referral code';
        $this->source_label_map[3]='Game Won';
        $this->source_label_map[137]='Deal';

        $this->source_label_map_color[144]='#04A057';
        $this->source_label_map_color[151]='#5B8806';
        $this->source_label_map_color[52]='#8085e8';
        $this->source_label_map_color[55]='#F8436E';
        $this->source_label_map_color[55]='#48BF21';
        $this->source_label_map_color[61]='#EB5E5E';
        $this->source_label_map_color[64]='#CCFFFF';
        $this->source_label_map_color[67]='#A2FF33';
        $this->source_label_map_color[88]='#33FFD7';
        $this->source_label_map_color[91]='#33C7FF';
        $this->source_label_map_color[97]='#33FFC4';
        $this->source_label_map_color[100]='#33FF86';
        $this->source_label_map_color[107]='#FF8633';
        $this->source_label_map_color[134]='#FFAF33';
        $this->source_label_map_color[143]='#FFDA33';
        $this->source_label_map_color[147]='#E3FF33';
        $this->source_label_map_color[155]='#B8FF33';
        $this->source_label_map_color[0]='#212235';
        $this->source_label_map_color[3]='#A2FF35';
        $this->source_label_map_color[137]='#A2FF39';

        $this->admin_roles_manage($this->admin_id,'coins');
	}

/**
     * @method user_coin_redeem_graph
     * @since Dec 2019
     * @uses function to create data for user_coin_redeem_graph
     * @param NA
     * @return json
     * ***/
    function user_coin_redeem_graph_post()
    {

        $this->form_validation->set_rules('user_unique_id', 'user Unique id', 'trim|required');
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }
        $this->load->model('auth/Auth_nosql_model');
        $this->load->model('Coins_model');
        
        $post = $this->input->post();
        $user_row = $this->Coins_model->get_single_row('user_id',USER,array('user_unique_id'=> $post['user_unique_id']));
        
        $ops = array();
      
        $ops[] = array('$match' => array(
            'user_id' => (string)$user_row['user_id']
        ));

        $ops[] = array(
            '$lookup' => array(
                'from' => COLL_COIN_REWARDS,
                'localField' => 'coin_reward_id',
                'foreignField' => 'coin_reward_id',
                'as' => 'reward_detail'
           )
        );

        $result = $this->Auth_nosql_model->aggregate(COLL_COIN_REWARD_HISTORY,$ops);

        $graph_data =array();    
        $redeem_types =$this->config->item('redeem_types');
        $total_users = array();
        $total_coin_redeem =0;
        foreach($result as $row)
        {
            if(!empty( $row['reward_detail']))
            {
                $row['reward_detail'] = (array)$row['reward_detail'][0];
                $type =$row['reward_detail']['type'];
                if(isset($redeem_types[$type]))
                {
                    
                    $total_coin_redeem += (int)$row['reward_detail']['redeem_coins'];
                    $graph_data[$redeem_types[$type]['key']]['name'] = $redeem_types[$type]['value']; 
                    $graph_data[$redeem_types[$type]['key']]['color'] = $this->graph_colors[$type]; 
                    if(isset($graph_data[$redeem_types[$type]['key']]['total_coins']))
                    {
                        $graph_data[$redeem_types[$type]['key']]['total_coins'] +=(int)$row['reward_detail']['redeem_coins'];
                    }
                    else
                    {
                        $graph_data[$redeem_types[$type]['key']]['total_coins'] = (int)$row['reward_detail']['redeem_coins'];
                    }

                }

            }
   
        }

        foreach($graph_data as &$graph_element)
        {
            $graph_element['y'] = round(($graph_element['total_coins']*100)/$total_coin_redeem);
            //removed color
        }

        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['data']['series_data'] = array_values($graph_data);
        $this->api_response_arry['data']['total_coin_redeem'] = $total_coin_redeem;
        $this->api_response();   
    }  

/**
     * @method coin_redeem_graph
     * @since Dec 2019
     * @uses function to export coin_redeem_graph
     * @param Array $_GET status
     * @return json
     * ***/
    function coin_redeem_graph_post()
    {
        $this->load->model('auth/Auth_nosql_model');
        $ops = array();
        $post = $this->input->post();
        if(!empty($post['from_date']) && !empty($post['to_date']))
        {
           $from_date = format_date($post['from_date'].' 00:00:00');
           $to_date= format_date($post['to_date']. ' 23:59:59');
           $from_date =convert_normal_to_mongo($from_date);
           $to_date =convert_normal_to_mongo($to_date);
           $ops[] = array('$match' => array(
               'added_date' => array(
                   '$gte' => $from_date,
                   '$lte' => $to_date
               )
           ));
        }

        $ops[] = array(
            '$lookup' => array(
                'from' => COLL_COIN_REWARDS,
                'localField' => 'coin_reward_id',
                'foreignField' => 'coin_reward_id',
                'as' => 'reward_detail'
           )
            );

        $result = $this->Auth_nosql_model->aggregate(COLL_COIN_REWARD_HISTORY,$ops);

        $graph_data =array();    
        $redeem_types =$this->config->item('redeem_types');
        $total_users = array();
        $total_coin_redeem =0;
        foreach($result as $row)
        {
            if(!empty( $row['reward_detail']))
            {
                $row['reward_detail'] = (array)$row['reward_detail'][0];
                $type =$row['reward_detail']['type'];
                if(isset($redeem_types[$type]))
                {
                    
                    $total_coin_redeem += (int)$row['reward_detail']['redeem_coins'];
                    $graph_data[$redeem_types[$type]['key']]['name'] = $redeem_types[$type]['value']; 
                    $graph_data[$redeem_types[$type]['key']]['color'] = $this->graph_colors[$type]; 
                    if(isset($graph_data[$redeem_types[$type]['key']]['total_coins']))
                    {
                        $graph_data[$redeem_types[$type]['key']]['total_coins'] +=(int)$row['reward_detail']['redeem_coins'];
                    }
                    else
                    {
                        $graph_data[$redeem_types[$type]['key']]['total_coins'] = (int)$row['reward_detail']['redeem_coins'];
                    }

                    if(isset($graph_data[$redeem_types[$type]['key']]['coins_user']))
                    {
                        $total_users[]= $row['user_id'];
                        $graph_data[$redeem_types[$type]['key']]['coins_user'][] =$row['user_id']; 
                    }
                    else
                    {
                        $total_users[]= $row['user_id'];
                        $graph_data[$redeem_types[$type]['key']]['coins_user'] = array(); 
                        $graph_data[$redeem_types[$type]['key']]['coins_user'][] =$row['user_id']; 
                    }

                }

            }
   
        }

        foreach($graph_data as &$graph_element)
        {
            $graph_element['y'] = round(($graph_element['total_coins']*100)/$total_coin_redeem);
            $graph_element['coins_user'] =count(array_unique($graph_element['coins_user']));
            //removed color
        }

        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['data']['series_data'] = array_values($graph_data);
        $this->api_response_arry['data']['total_coin_redeem'] = $total_coin_redeem;
        $this->api_response();   
    }  
    /**
     * @since Nov 2019
     * @uses function to get coin configuration details
     * @method get_coin_configuration_details_post 
     * 
    */
    function get_coin_configuration_details_post()
    {
        $this->load->model('Coins_model');
        $setting_result = $this->Coins_model->get_coin_configuration_details();

        $data = array();
        $data['coins_module_setting'] = array();
        $data['coins_module_setting']['coin_modules'] = array();
        $data['coins_module_setting']['daily_checkin_days'] = $this->config->item('daily_checkin_days');

        if(!empty($setting_result))
        {
            $data['coins_module_setting'][$setting_result[0]['module_name']] = $setting_result[0]['module_status'];
            foreach($setting_result as $row)
            {
                unset($row['module_name']);
                unset($row['module_status']);
                if(!empty($row['daily_coins_data']))
                {
                    $row['daily_coins_data'] = json_decode($row['daily_coins_data']);
                }
                $data['coins_module_setting']['coin_modules'][] = $row;
            }
        }

        $this->api_response_arry['response_code'] 	= 200;
        $this->api_response_arry['error']  			= array();
        $this->api_response_arry['data']  			= $data;
        $this->api_response();
    }

    /**
     * @since Nov 2019
     * @uses function to update coin status
     * @method update_coins_status_post 
     * 
    */
    function update_coins_status_post()
    {
        $this->form_validation->set_rules('status', 'Status', 'trim|required');
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }

        $post= $this->input->post();
        $this->load->model('Coins_model');
        $result = $this->Coins_model->update_coin_status($post['status']);

        $this->delete_cache_data('pickem_flag');
        $this->http_post_request("auth/get_app_master_list",array(),2);
        if($result)
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            $this->api_response_arry['global_error']  	= $this->lang->line('coin_status_success_msg');
            $this->api_response();
        }
        else{
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('coin_status_error_msg');
            $this->api_response();
        }

    }

 /**
     * @method save_coins_configuration 
     * @since Nov 2019
     * @uses function to save coins configuration
     * @param Array coin_module_details contains all submodule informations
     * 
    */
    function save_coins_configuration_post()
    {
        $this->form_validation->set_rules('coin_module_details', 'coin module details', 'trim|callback_validate_coin_details');
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }

        $submodule_data = array();

        $submodules_details = $this->post('coin_module_details');

        foreach($submodules_details as $submodule)
        {
            $new_data = array('submodule_setting_id' => $submodule['submodule_setting_id'],
            'status' => $submodule['status']);    
            if($submodule['submodule_key'] == 'daily_streak_bonus')
            {
                foreach($submodule['daily_coins_data'] as $key =>  $row)
                {
                    if(empty($row))
                    {
                        unset($submodule['daily_coins_data'][$key]);
                    }
                }
        
                $submodule['daily_coins_data'] = array_values($submodule['daily_coins_data']);
                $new_data['daily_coins_data'] = json_encode($submodule['daily_coins_data']);
            }
            $submodule_data[] = $new_data;
        }
        $this->load->model('Coins_model');
        $this->Coins_model->update_coins_setting($submodule_data);

        $master_coin_data_key = "master_coin_data";
        $this->delete_cache_data($master_coin_data_key);
        
        $how_to_earn_coins_key = "how_to_earn_coins";
        $this->delete_cache_data($how_to_earn_coins_key);

        $config_cache_key = 'app_config';
        $this->delete_cache_data($config_cache_key);
        $this->push_s3_data_in_queue('app_master_data',array(),"delete");
        $this->flush_cache_data();
        
        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['global_error']  	= $this->lang->line('coin_config_status_success_msg');
        $this->api_response();


    }
    
    function validate_coin_details()
    {
        $details = $this->post('coin_module_details');
        if(!empty($details))
        {
            foreach($details as $submodule)
            {
                if(empty($submodule['submodule_setting_id']) || !isset($submodule['status']) || empty($submodule['submodule_key']))
                {
                   
                    $this->form_validation->set_message('validate_coin_details', 'Please enter module details');
                    return FALSE;
                }

                if(isset($submodule['submodule_key']) &&  $submodule['submodule_key']=='daily_streak_bonus' )
                {
                    if(empty($submodule['daily_coins_data']))
                    {
                        $this->form_validation->set_message('validate_coin_details', 'Please enter valid module details');
                        return FALSE;
                    }
                    else
                    {
                        $master_days= $this->config->item('daily_checkin_days');
                        $daily_streak_data= $submodule['daily_coins_data'];
                        $valid = true;
                        foreach($daily_streak_data as $key =>  $row)
                        {
                            if(empty($row))
                            {
                                //unset($daily_streak_data[$key]);
                                $this->form_validation->set_message('validate_coin_details', 'Please enter valid daily streak data');
                                return FALSE;
                            }
                        }
                
                        $daily_streak_data =array_values($daily_streak_data);
                        $daily_streak_data_length = count($daily_streak_data);
                
                        if(!in_array($daily_streak_data_length,$master_days))
                        {
                            $this->form_validation->set_message('validate_coin_details', 'Please enter valid daily streak data');
                            return FALSE;
                        }
 
                    }
                  
                }
            }

            return TRUE;
        }
        $this->form_validation->set_message('validate_coin_details', 'Please enter valid module details');
        return FALSE;
    }

    function check_module_status($status,$field)
    {
        $post = $this->post();

        if(isset($post[$field]) && in_array($post[$field],array('0','1')))
        {
            return true;
        }

        $this->form_validation->set_message('check_module_status', 'Please enter valid '.$field);
        return FALSE;
       
    }

    function validate_daily_streak_data()
    {
        $daily_streak_data = $this->post('daily_streak_data');
        $master_days= $this->config->item('daily_checkin_days');
        $valid = true;
        foreach($daily_streak_data as $key =>  $row)
        {
            if(empty($row))
            {
                unset($daily_streak_data[$key]);
            }
        }

        $daily_streak_data =array_values($daily_streak_data);
        $daily_streak_data_length = count($daily_streak_data);

        if(!in_array($daily_streak_data_length,$master_days))
        {
            $this->form_validation->set_message('validate_daily_streak_data', 'Please enter valid daily streak data');
            return FALSE;
        }
        
        return TRUE;

    }
     
    /**
     * Regards Apis 
     *
     *
     *
     **/
    function add_reward_post()
    {
        $this->form_validation->set_rules('value', 'Value', 'trim|required|is_natural_no_zero|max_length[7]');
        $this->form_validation->set_rules('detail', 'Detail', "trim|required");
        $this->form_validation->set_rules('redeem_coins', 'Redeem coins', 'trim|required|is_natural_no_zero|max_length[7]');
        $this->form_validation->set_rules('type', 'type', 'trim|required');
        //1->bonus,2=> real ,3=>Gift Card
        $this->form_validation->set_rules('image', 'Image', 'trim');
        
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }   

        $data = array();
        $post = $this->input->post();
        $data['coin_reward_id'] = new MongoDB\BSON\ObjectId();
        $data['value'] = $post['value'];
        $data['detail'] = $post['detail'];
        $data['redeem_coins'] = (int)$post['redeem_coins'];
        $data['type'] = $post['type'];
        $data['image'] = !empty($post['image'])?$post['image']:'';
        $data['status'] = 1;
        $data['added_date'] = convert_normal_to_mongo(format_date());
        $data['updated_date'] = convert_normal_to_mongo(format_date());

        $this->load->model('auth/Auth_nosql_model');
        $this->Auth_nosql_model->insert_nosql(COLL_COIN_REWARDS,$data);

        //delete reward list
        $this->delete_cache_data('coin_rewards');
        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['message']  	= $this->lang->line('reward_added_success_msg');
        $this->api_response();


    }
        
    public function get_reward_list_post()
    {   
        $this->form_validation->set_rules('status', 'status', 'trim|required');
        
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }   
        
        $post = $this->input->post();
        $limit = 30;
        $offset = 0;

        $count = 0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;
        $count = $this->Auth_nosql_model->count(COLL_COIN_REWARDS,array('status'=> (int)$post['status'])); 
        // if(isset($post['current_page']))
		// {
		 	$page = $post['current_page']-1;
		// 	if($post['current_page']==1) {
				
		// 	}
		// }

         $offset	= $limit * $page;

        $this->load->model('auth/Auth_nosql_model');
        $result= $this->Auth_nosql_model->select_nosql(COLL_COIN_REWARDS,array('status'=> (int)$post['status']),$limit,$offset,array('updated_date' => 'desc'));
       

        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['data']['reward_list'] 	= $result;
        $this->api_response_arry['data']['total'] 	= $count;
        $this->api_response_arry['data']['next_offset'] = $offset + count($result);
        $this->api_response();      
    }

    public function update_reward_status_post()
    {
        $this->form_validation->set_rules('status', 'status', 'trim|required');
        $this->form_validation->set_rules('coin_reward_id', 'coin reward id', 'trim|required');
        
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }   

        $post = $this->input->post();
        $this->load->model('auth/Auth_nosql_model');

        $update_data = array();
        $update_data['status'] =(int)$post['status'];
        $update_data['updated_date'] = convert_normal_to_mongo(format_date());
        $this->Auth_nosql_model->update_nosql(COLL_COIN_REWARDS,array('coin_reward_id' =>   new MongoDB\BSON\ObjectId($post['coin_reward_id'])),
        $update_data); 
        
         //delete reward list
         $this->delete_cache_data('coin_rewards');
        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = "Reward status updated.";
        $this->api_response();   

    }


    public function do_upload_process($ext)
	{
		$dir						= APP_ROOT_PATH.UPLOAD_DIR;
		$config['image_library'] 	= 'gd2';
		$config['allowed_types']	= 'jpg|png|jpeg|gif|PNG';
		$config['max_size']			= '2000';
		$config['min_width']		= '36';//64
		$config['min_height']		= '36';//42
		$config['max_width']		= '2400';
		$config['max_height']		= '1200';
		$config['upload_path']		= $dir;
		$config['file_name']		= rand(1,1000).time().'.'.$ext;

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
			$config1['image_library']	= 'gd2';
			$config1['source_image']	= $dir.$config['file_name'];
			//$config['create_thumb']		= TRUE;
			$config1['maintain_ratio']	= TRUE;
			$config1['width']			= 200;
			$config1['height']			= 200;
			
			$this->load->library('image_lib', $config1);
			if ( !$this->image_lib->resize())
			{
				$error = $this->image_lib->display_errors();
		        $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		        $this->api_response_arry["message"] = strip_tags($error);
				$this->api_response();
			}
			return $config['file_name'];

		}
	}

    public function do_upload_reward_image_post()
	{
		$segment = $this->uri->segment(3);
		
		$file_field_name	= $this->post('name');
		$dir				= APP_ROOT_PATH.UPLOAD_DIR;
		
		$temp_file_image	= $_FILES['file']['tmp_name'];
		$ext				= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		
		if( strtolower( IMAGE_SERVER ) == 'remote' ){
			$file_name = $this->do_upload_process($ext);
			$temp_file = $dir.$file_name;
		}

		$vals = 			@getimagesize($temp_file_image);
		$width = $vals[0];
		$height = $vals[1];
        $subdir				= ROOT_PATH.REWARD_IMG_DIR;
        $s3_dir 			= REWARD_IMG_DIR;
        if ($height < '62' || $width < '100') {//72x120
        
            $invalid_size = str_replace("{max_height}",'62',$this->lang->line('team_jersey_image_invalid_size'));
            $invalid_size = str_replace("{max_width}",'100',$invalid_size);
            //$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>$invalid_size) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            $this->api_response_arry["message"] = $invalid_size;
            $this->api_response();
        }

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
			$this->check_folder_exist($subdir);
		}

		$file_name = time().".".$ext ;
		$team_image_arr= array();
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
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $return_array = array('image_url'=>  IMAGE_PATH.$filePath,'image_name'=> $file_name);
                    $this->api_response_arry['data'] = $return_array;
                    $this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
        } else {

			$config['allowed_types']	= 'jpg|png|jpeg|gif|PNG';
			$config['max_size']			= '2000';
			$config['max_width']		= '2400';
			$config['max_height']		= '1200';
			$config['min_width']		= '64';
			$config['min_height']		= '42';
			$config['upload_path']		= $dir;
			$config['file_name']		= time();

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
				$team_image_arr['image'] = IMAGE_PATH.$s3_dir.$file_name;
				$this->api_response_arry["data"] = array('image_name' =>IMAGE_PATH.$s3_dir.$file_name ,'image_url'=> $subdir);
				$this->api_response();
			}
		}		
	}
   

    /**
     * @method reward_history_post
     * @since Dec 2019
     * @uses function get one reward history
     * @param Array $_POST coin_reward_id
     * @return json
     * ***/
    public function get_reward_history_post()
    {
        $this->form_validation->set_rules('coin_reward_id', 'coin reward id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }   

        $post = $this->input->post();
        $limit = 30;
        $offset = 0;

        $count = 0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        $history_cond =array('coin_reward_id'=> new MongoDB\BSON\ObjectId($post['coin_reward_id']) );
        $count = $this->Auth_nosql_model->count(COLL_COIN_REWARD_HISTORY,$history_cond); 
    
		$page = $post['current_page']-1;
		$offset	= $limit * $page;
        //get reward details
        $this->load->model('auth/Auth_nosql_model');
        $result= $this->Auth_nosql_model->select_one_nosql(COLL_COIN_REWARDS,array('coin_reward_id'=> new MongoDB\BSON\ObjectId($post['coin_reward_id']) ));

        //get history
        $history= $this->Auth_nosql_model->select_nosql(COLL_COIN_REWARD_HISTORY,$history_cond,$limit,$offset);

        $this->load->model('auth/Auth_model');
        //get user details
        $user_ids = array_unique(array_column($history,'user_id'));
        $user_details = $this->Auth_model->get_users_by_ids($user_ids);
        $user_details = array_column($user_details,NULL,'user_id');
       // print_r($user_details);
        foreach($history as &$row)
        {
            $row['username'] = $user_details[$row['user_id']]['user_name'];
            $row['added_date']=convert_mongo_to_normal_date($row['added_date']);
        }

        //count in mongo
        $redeem_by =$this->Auth_nosql_model->count(COLL_COIN_REWARD_HISTORY,$history_cond);
        $this->api_response_arry['data']['redeem_by']= $redeem_by;
        $this->api_response_arry['data']['total_coin_redeem']= $redeem_by*$result['redeem_coins'];
        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = "Reward status updated.";
        $this->api_response_arry['data']['history'] = $history;
        $this->api_response_arry['data']['reward_detail'] = $result;
        $this->api_response_arry['data']['total'] 	= $count;
        $this->api_response_arry['data']['next_offset'] = $offset + count($history);
        $this->api_response();   
    }

     /**
     * @method all_reward_history_post
     * @since Dec 2019
     * @uses function get reward history
     * @param Array $_POST status
     * @return json
     * ***/
    function get_reward_list_by_status_post()
    {
        $this->form_validation->set_rules('status', 'Status', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }   
        $post = $this->input->post();
        $limit = 30;
        $offset = 0;

        $count = 0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        if($post['current_page'])
		{
			$page = $post['current_page']-1;
		}

		$offset	= $limit * $page;


        $ops = array(
            array(
                '$match' => array(
                    'status'=> (int)$post['status']
                )
            ),
            array(
                '$lookup' => array(
                    'from' => COLL_COIN_REWARDS,
                    'localField' => 'coin_reward_id',
                    'foreignField' => 'coin_reward_id',
                    'as' => 'reward_detail'
                )
            ),
            array(
                '$sort' => array('update_date' => -1)// Other example option
            ),
            
          
            array(
                '$skip' => (int)$offset
            ),
            array(
                '$limit' => (int)$limit// Example option
            ),
        );

       
        $this->load->model('auth/Auth_nosql_model');
      
        $result = $this->Auth_nosql_model->aggregate(COLL_COIN_REWARD_HISTORY,$ops);

        $user_details =array();
        $user_ids = array();
        if(!empty($result))
        {
            $this->load->model('auth/Auth_model');
            $user_ids = array_unique(array_column($result,'user_id'));
            $user_details = $this->Auth_model->get_users_by_ids($user_ids); 
            $user_details = array_column($user_details,NULL,'user_id');
        }

        foreach($result as &$row)
        {
            $row['username'] = $user_details[$row['user_id']]['user_name'];
            $date =convert_mongo_to_normal_date($row['added_date']);
            $date = get_timezone(strtotime($date),'Y-m-d H:i:s',$tz_arr=$this->app_config['timezone'],2,2);
            $row['added_date'] = $date['date'];
        }

        $count =$this->Auth_nosql_model->count(COLL_COIN_REWARD_HISTORY,array('status' => (int)$post['status']));    
      

        $this->api_response_arry['data']['list'] = $result;
        $this->api_response_arry['data']['total'] 	= $count;
        $this->api_response_arry['data']['next_offset'] = $offset + count($result);
        $this->api_response();   
    }

    /**
     * @method approve_reward_request
     * @since Dec 2019
     * @uses function to approve_reward_request
     * @param Array $_POST coin_reward_history_id
     * @return json
     * ***/
    public function approve_reward_request_post(){
        $this->form_validation->set_rules('coin_reward_history_id', 'coin reward history id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }   

        $post = $this->input->post();
        $this->load->model('auth/Auth_nosql_model');
        $this->Auth_nosql_model->update_nosql(COLL_COIN_REWARD_HISTORY,array('coin_reward_history_id' =>   new MongoDB\BSON\ObjectId($post['coin_reward_history_id'])),
        array('status' => (int)1,
              'update_date' => convert_normal_to_mongo(format_date())
    )); 
        
        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = "Request Approved.";
        $this->api_response();   

    }    


       /**
     * @method export_reward_list_by_status
     * @since Dec 2019
     * @uses function to export rewart list
     * @param Array $_GET status
     * @return json
     * ***/
    public function export_reward_list_by_status_get()
    {
        ini_set('max_execution_time','0');	
		ini_set('memory_limit', '-1');
		$post = $this->input->get();

        
        $ops = array(
            array(
                '$match' => array(
                    'status'=> (int)$post['status']
                )
            ),
            array(
                '$lookup' => array(
                    'from' => COLL_COIN_REWARDS,
                    'localField' => 'coin_reward_id',
                    'foreignField' => 'coin_reward_id',
                    'as' => 'reward_detail'
                )
            ),
            // array(
            //     '$limit' => $limit// Example option
            // ),
            array(
                '$sort' => array('added_date' => -1)// Other example option
            )
        );

        $this->load->model('auth/Auth_nosql_model');
      
        $result = $this->Auth_nosql_model->aggregate(COLL_COIN_REWARD_HISTORY,$ops);

        $user_details =array();
        $user_ids = array();
        if(!empty($result))
        {
            $this->load->model('auth/Auth_model');
            $user_ids = array_unique(array_column($result,'user_id'));
            $user_details = $this->Auth_model->get_users_by_ids($user_ids); 
            $user_details = array_column($user_details,NULL,'user_id');
        }

        $export_data =array();    
        foreach($result as &$row)
        {
            $row['reward_detail'][0] =  (array)$row['reward_detail'][0];
            $date = convert_mongo_to_normal_date($row['added_date']);
            $date = get_timezone(strtotime($date),'Y-m-d H:i:s',$tz_arr=$this->app_config['timezone'],2,2);
            $export_data[] = array(
                'username' => $user_details[$row['user_id']]['user_name'],
                'added_date' => $date['date'],
                'event'=> $row['reward_detail'][0]['detail'],
                'status' => (!empty($post['status']) && $post['status']=='1')?'Approved':'Pending',
                'value' => $row['reward_detail'][0]['value']
            );
            
        }


        if(!empty($export_data)){
			$header = array_keys($export_data[0]);
			$result = array_merge(array($header),$export_data);
			$this->load->helper('csv');
			array_to_csv($result,'Redeem_reward_report.csv');
		}
     
    }

      /**
     * @method get_coin_distributed_history
     * @since Dec 2019
     * @uses function to get coin distributed
     * @param Array $_POST items_perpage,current_page,from_date,to_date
     * @return json
     * ***/
    public function get_coin_distributed_history_post()
    {

        $post = $this->input->post();
        
        $this->load->model('Coins_model');
        $result =  $this->Coins_model->get_coin_distributed_list();

        //get transaction desciptions
        $this->load->model('auth/Auth_nosql_model');
        $transaction_msgs =  $this->Auth_nosql_model->select_nosql(COLL_TRANSACTION_MESSAGES);
        $transaction_by_source= array();
        if(!empty($transaction_msgs))
        {
            $transaction_by_source = array_column($transaction_msgs,NULL,'source');
        }
       

        foreach($result['result'] as $key => &$row)
        {
            $result['result'][$key]['custom_data'] = json_decode($row['custom_data'],TRUE);
            
            $row['message'] = '';
            if(isset($transaction_by_source[$row['source']]))
            {
                $row['message'] = $transaction_by_source[$row['source']]['en_message'];
                
            }
            $row['trans_desc'] = '';
              if (is_array($row["custom_data"]) || is_object($row["custom_data"])){
                             
                if (!empty($row["custom_data"]) && !empty($row["custom_data"]) != NULL && $row["custom_data"] !="") { 
              
                    foreach($row["custom_data"] as $ckey=>$cval){                     
                       
                       
                        if (!empty($cval) && $cval != "" && $cval != NULL && $cval != null) {  
                            if (!is_array($cval)) {                               
                                $row['message'] =  str_replace("{{".$ckey."}}",$cval,$row['message']);
                            } 
                        }                                                               
                            
                    }  
                }
            }  
  
            

        }

        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['data']['list'] = $result['result'];
        $this->api_response_arry['data']['total'] = $result['total'];
        $this->api_response_arry['data']['total_coins_distributed'] = $result['total_coins_distributed'];
        $this->api_response();   

    }

    public function insert_transaction_msgs_post()
    {
        $this->load->model('auth/Auth_nosql_model');

        for($i=5;$i<151;$i++)
        {
            $tmp = array(
                "source" => $i,
                "en_message"=> '',
                "hi_message"=> '',
                "guj_message"=> '',

            );

            $this->Auth_nosql_model->insert_nosql('transaction_messages', $tmp);

        }
    }

     /**
     * @method export_coin_distribution_history_get
     * @since Dec 2019
     * @uses function to export coins distributed list
     * @param Array $_GET status
     * @return json
     * ***/
    public function export_coin_distribution_history_get()
    {
        ini_set('max_execution_time','0');	
		ini_set('memory_limit', '-1');
		$post = $this->input->get();    

        $this->load->model('Coins_model');
        $result =  $this->Coins_model->get_export_coin_distributed_list($post);

        //get transaction desciptions
        $this->load->model('auth/Auth_nosql_model');
        $transaction_msgs =  $this->Auth_nosql_model->select_nosql(COLL_TRANSACTION_MESSAGES);
        $transaction_by_source= array();
        if(!empty($transaction_msgs))
        {
            $transaction_by_source = array_column($transaction_msgs,NULL,'source');
        }
       

        foreach($result['result'] as $key => &$row)
        {
            $result['result'][$key]['custom_data'] = json_decode($row['custom_data'],TRUE);
            
            $row['message'] = '';
            if(isset($transaction_by_source[$row['source']]))
            {
                $row['message'] = $transaction_by_source[$row['source']]['en_message'];
                
            }

            unset($row['source']);
            unset($row['status']);

              if (is_array($row["custom_data"]) || is_object($row["custom_data"])){
                             
                if (!empty($row["custom_data"]) && !empty($row["custom_data"]) != NULL && $row["custom_data"] !="") { 
              
                    foreach($row["custom_data"] as $ckey=>$cval){  
                        
                      
                       
                       
                        if (!empty($cval) && $cval != "" && $cval != NULL && $cval != null) {  
                            if (!is_array($cval)) {                               
                                $row['message'] =  str_replace("{{".$ckey."}}",$cval,$row['message']);
                            } 
                        }                                                               
                            
                    }  
                }
            } 

             unset($row["custom_data"]);


        }

        // echo '<pre>';
        // print_r($result);
        // die('dfd');

        if(!empty($result['result'])){
			$header = array_keys($result['result'][0]);
			$result = array_merge(array($header),$result['result']);
			$this->load->helper('csv');
			array_to_csv($result,'coin_distributed.csv');
		}
     
    }

       

      /**
     * @method coin_redeem_leaderboard
     * @since Dec 2019
     * @uses function to  coin_redeem_leaderboard_post
     * @param Array $_GET status
     * @return json
     * ***/
    function coin_redeem_history_post()
    {
        $post = $this->input->post();

        $limit = 30;
        $offset = 0;

        $count = 0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }
        
        $page = $post['current_page']-1;
		$offset	= $limit * $page;

         //get transaction desciptions
         $this->load->model('auth/Auth_nosql_model');
         $ops = array();

         if(!empty($post['from_date']) && !empty($post['to_date']))
         {
            $from_date = format_date($post['from_date'].' 00:00:00');
            $to_date= format_date($post['to_date']. ' 23:59:59');
            $from_date =convert_normal_to_mongo($from_date);
            $to_date =convert_normal_to_mongo($to_date);
            $ops[] = array('$match' => array(
                'added_date' => array(
                    '$gte' => $from_date,
                    '$lte' => $to_date
                )
            ));
         }

         $ops[] = array(
            '$lookup' => array(
                'from' => COLL_COIN_REWARDS,
                'localField' => 'coin_reward_id',
                'foreignField' => 'coin_reward_id',
                'as' => 'reward_detail'
           )
            );

            $ops[] =  array(
                '$limit' => (int)$limit// Example option
            );
            $ops[] =  array(
                '$sort' => array('added_date' => -1)// Other example option
            );
            $ops[] = array(
                '$skip' => (int)$offset// Example option
            );
       
        $result = $this->Auth_nosql_model->aggregate(COLL_COIN_REWARD_HISTORY,$ops);

        $user_details =array();
        $user_ids = array();
        if(!empty($result))
        {
            $this->load->model('auth/Auth_model');
            $user_ids = array_unique(array_column($result,'user_id'));
            $user_details = $this->Auth_model->get_users_by_ids($user_ids); 
            $user_details = array_column($user_details,NULL,'user_id');
        }

        foreach($result as &$row)
        {
            $row['username'] = $user_details[$row['user_id']]['user_name'];
            $row['added_date']=convert_mongo_to_normal_date($row['added_date']);

            $row['value'] = 0;
            $row['detail'] = '';
            $row['type'] = 1;
            $row['redeem_coins'] = 0;
            if(!empty( $row['reward_detail']))
            {
                $row['reward_detail'][0] = (array)$row['reward_detail'][0];
                $row['value'] = $row['reward_detail'][0]['value'];
                $row['detail'] = $row['reward_detail'][0]['detail'];
                $row['type'] = $row['reward_detail'][0]['type'];
                $row['redeem_coins'] = $row['reward_detail'][0]['redeem_coins'];
                unset($row['reward_detail']);
            }    
            
        }


        $search_condition = array();
        $search_condition['status'] =1;

        if(!empty($post['from_date']) && !empty($post['to_date']))
        {
            $search_condition['added_date'] =array(
                '$gte' => $from_date,
                '$lte' => $to_date
            );
        }
        $count =$this->Auth_nosql_model->count(COLL_COIN_REWARD_HISTORY,$search_condition);    
        $this->api_response_arry['data']['list'] = $result;
        $this->api_response_arry['data']['total'] 	= $count;
        $this->api_response_arry['data']['next_offset'] = $offset + count($result);
        $this->api_response();   
    }

      /**
     * @method export_coin_redeem_leaderboard
     * @since Dec 2019
     * @uses function to  export_coin_redeem_leaderboard
     * @param Array $_GET status
     * @return json
     * ***/
    function export_coin_redeem_history_get()
    {
        $post = $this->input->get();

         //get transaction desciptions
         $this->load->model('auth/Auth_nosql_model');
         $ops = array();

         if(!empty($post['from_date']) && !empty($post['to_date']))
         {
            $from_date = format_date($post['from_date'].' 00:00:00');
            $to_date= format_date($post['to_date']. ' 23:59:59');
            $from_date =convert_normal_to_mongo($from_date);
            $to_date =convert_normal_to_mongo($to_date);
            $ops[] = array('$match' => array(
                'added_date' => array(
                    '$gte' => $from_date,
                    '$lte' => $to_date
                )
            ));
         }

         $ops[] = array(
            '$lookup' => array(
                'from' => COLL_COIN_REWARDS,
                'localField' => 'coin_reward_id',
                'foreignField' => 'coin_reward_id',
                'as' => 'reward_detail'
           )
            );

            $ops[] =  array(
                '$sort' => array('added_date' => -1)// Other example option
            );
       
        $result = $this->Auth_nosql_model->aggregate(COLL_COIN_REWARD_HISTORY,$ops);

        $user_details =array();
        $user_ids = array();
        if(!empty($result))
        {
            $this->load->model('auth/Auth_model');
            $user_ids = array_unique(array_column($result,'user_id'));
            $user_details = $this->Auth_model->get_users_by_ids($user_ids); 
            $user_details = array_column($user_details,NULL,'user_id');
        }

        foreach($result as &$row)
        {
            $row['username'] = $user_details[$row['user_id']]['user_name'];
            $row['added_date']=convert_mongo_to_normal_date($row['added_date']);

            $row['value'] = 0;
            $row['detail'] = '';
            $row['type'] = 1;
            $row['redeem_coins'] = 0;
            if(!empty( $row['reward_detail']))
            {
                $row['reward_detail'][0] = (array)$row['reward_detail'][0];
                $row['value'] = $row['reward_detail'][0]['value'];
                $row['detail'] = $row['reward_detail'][0]['detail'];
                $row['type'] = $row['reward_detail'][0]['type'];
                $row['redeem_coins'] = $row['reward_detail'][0]['redeem_coins'];
                unset($row['reward_detail']);
            }   
            
            unset($row['coin_reward_history_id']);
            unset($row['coin_reward_id']);
            unset($row['user_id']);
            unset($row['status']);
            unset($row['_id']);
            
        }

        if(!empty($result)){
			$header = array_keys($result[0]);
			$result = array_merge(array($header),$result);
			$this->load->helper('csv');
			array_to_csv($result,'coin_redeem_leaderboard.csv');
		}

       
    }

    /**
     * @method get_top_earner
     * @since Dec 2019
     * @uses function to  get_top_earner
     * @param Array $_GET status
     * @return json
     * ***/
    public function get_top_earner_post()
    {
        $this->load->model('Coins_model');
        $post = $this->input->post();
        $result = $this->Coins_model->get_top_earner($post);
        $count_result = $this->Coins_model->get_top_earner_count();

        // print_r($result);
        // print_r($count_result);
        // die('dfd');
        $this->api_response_arry['data']['list'] = $result['list'];
        $this->api_response_arry['data']['total'] 	= $count_result['total'];
        $this->api_response_arry['data']['next_offset'] = $result['next_offset'];
        $this->api_response(); 
    }

       /**
     * @method export_top_earner
     * @since Dec 2019
     * @uses function to export top earner list
     * @param Array $_GET status
     * @return json
     * ***/
    public function export_top_earner_get()
    {
        ini_set('max_execution_time','0');	
		ini_set('memory_limit', '-1');
		$post = $this->input->get();
        $this->load->model('Coins_model');
        $export_data= $this->Coins_model->export_top_earner();

        if(!empty($export_data)){
			$header = array_keys($export_data[0]);
			$result = array_merge(array($header),$export_data);
			$this->load->helper('csv');
			array_to_csv($result,'top_earner_leaderboard.csv');
		}
     
    }

    /**
     * @method get_top_redeemer
     * @since Dec 2019
     * @uses function to  get_top_redeemer
     * @param Array $_GET 
     * @return json
     * ***/
    public function get_top_redeemer_post()
    {
        $this->load->model('Coins_model');
        $post = $this->input->post();
        
        $result = $this->Coins_model->get_top_redeemer($post);
        $count_result = $this->Coins_model->get_top_redeemer_count();

        // print_r($result);
        // print_r($count_result);
        // die('dfd');
        $this->api_response_arry['data']['list'] = $result['list'];
        $this->api_response_arry['data']['total'] 	= $count_result['total'];
        $this->api_response_arry['data']['next_offset'] = $result['next_offset'];
      
        $this->api_response(); 
    }

    /**
     * @method export_top_redeemer
     * @since Dec 2019
     * @uses function to  export top_redeemer
     * @param Array $_GET 
     * @return json
     * ***/
    public function export_top_redeemer_get()
    {
        ini_set('max_execution_time','0');	
		ini_set('memory_limit', '-1');
		$post = $this->input->get();
        $this->load->model('Coins_model');
        $export_data= $this->Coins_model->export_top_redeemer();

        if(!empty($export_data)){
			$header = array_keys($export_data[0]);
			$result = array_merge(array($header),$export_data);
			$this->load->helper('csv');
			array_to_csv($result,'top_redeemer_leaderboard.csv');
		}
    }

     /**
     * @method coin_distributed_graph
     * @since Dec 2019
     * @uses function to  coin distributed graph
     * @param Array $_POST 
     * @return json
     * ***/
    function coin_distributed_graph_post()
    {
        $post = $this->input->post();
        
        $this->load->model('Coins_model');
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date(' -70 days')));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $result =  $this->Coins_model->get_coin_distributed_graph($post);

        //get transaction desciptions
        $this->load->model('auth/Auth_nosql_model');
        $transaction_msgs =  $this->Auth_nosql_model->select_nosql(COLL_TRANSACTION_MESSAGES);
        $transaction_by_source= array();
        if(!empty($transaction_msgs))
        {
            $transaction_by_source = array_column($transaction_msgs,NULL,'source');
        }
       
         //$data
            /**
             * {
      *  name: 'Tokyo',
       * data: [7.0, 6.9, 9.5, 14.5, 18.4, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
   * }
             * **/

        $Dates = get_dates_from_range($post['from_date'], $post['to_date']); 
  
        $dates = array();

        $data = array();
        //$exiting_dates
        foreach($result['result'] as $row)
        {
            if(!in_array($row['date_added'],$dates))
            {
                $dates[] = $row['date_added'];
                foreach($this->source_label_map as $key => $value)
                {
                    if(!isset($data[$key]['data'][$row['date_added']]))
                    {
                        $data[$key]['data'][$row['date_added']] = 0;
                    }
                }

                $data[$row['source']]['data'][$row['date_added']] = 0;
            }
            if(isset($data[$row['source']]['data'][$row['date_added']]))
            {
                $data[$row['source']]['data'][$row['date_added']] += (int)$row['points'];
            }
            else
            {
                $data[$row['source']]['data'][$row['date_added']] = (int)$row['points']; 
            }
        }        
       

        //  $str_dates = array();
        // foreach($Dates as $oneDate)
        // {
        //     $date = $oneDate ;
        //     $str_dates[] = $date;
        //     foreach($result['result'] as $row)
        //     {  
        //         $main_date = $row['date_added'];

        //         if(!in_array($main_date,$str_dates))
        //         {
        //             $data[$row['source']]['data'][$date] = 0;
        //         }
        //         else
        //         {
        //             if(isset($data[$row['source']]['data'][$main_date]))
        //                 {
        //                     $data[$row['source']]['data'][$main_date] += $row['points'];
        //                 }
        //                 else
        //                 {
        //                     $data[$row['source']]['data'][$main_date] = 0;
        //                 }
                    
        //         }  
        //     }
        // }
       
        // $new_data = array();
        foreach($data as $key =>  &$value)
        {
            if(isset($this->source_label_map[$key]))
            {
                $value['name'] = $this->source_label_map[$key];
            }
            else
            {
                $value['name'] = 'Other';
            }

            if(isset($this->source_label_map_color[$key]))
            {
                $value['color'] =$this->source_label_map_color[$key];
            }
            else
            {
                $value['color'] =$this->source_label_map_color[0];
            }
            ksort($value['data']);

            $data[$key]['data'] = array_values($value['data']);
            //$data[$key]['data'] = $value['data'];
           
        }

       


        foreach($dates as &$date)
        {
            $date = date('d M',strtotime($date));
        }

        $user_condition = array();   
        
        if(!empty($post['user_unique_id']))
        {
            $user_condition['user_unique_id'] = $post['user_unique_id'];
            $closing_balance =$this->Coins_model->get_single_row('point_balance as closing_balance',USER,$user_condition);
        }
        else
        {
            $closing_balance =$this->Coins_model->get_single_row('SUM(point_balance) as closing_balance',USER);
        }
       
        $total_result =  $this->Coins_model->get_all_coins_distributed_counts($post);
        $expired_coins =  $this->Coins_model->get_expired_coins($post);
        $this->api_response_arry['data']['total_coins_distributed'] = $total_result['total_coins_distributed'];
       $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['data']['series'] = array_values($data);
        $this->api_response_arry['data']['categories'] = $dates;
        $this->api_response_arry['data']['closing_balance'] = $closing_balance['closing_balance'];
        $this->api_response_arry['data']['expired_balance'] = $expired_coins['expired_coins'];
        $this->api_response();   
    }

    function get_daily_checkin_top_gainers_post()
    {
        $post = $this->input->post();
        //get spin counts
        $this->load->model('Coins_model');
       $counts = $this->Coins_model->get_entity_counts($post);
       $top_gainers = $this->Coins_model->get_top_gainers($post);
        
         $categories = array_column($top_gainers,'full_name');
         $series = array();
         $coin_arr= array();
         $bonus_arr= array();
         $cash_arr = array();
         foreach ($top_gainers as $row)
         {
            $coin_arr[] = (int)$row['coins'];
         }

         $series[] = array('name' => "Coin",'data' => $coin_arr);

        //  echo '<pre>';
        //  print_r($categories);
        //  print_r($series);
        //  die();

         $this->api_response_arry['service_name']    = 'get_daily_checkin_top_gainers';
         $this->api_response_arry['data']['series']  = $series;
         $this->api_response_arry['data']['categories']  = $categories;
         $this->api_response_arry['data']['counts']  = $counts;
         $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
         $this->api_response();

    }

    function get_leaderboard_dailycheckin_post()
    {
        $post = $this->input->post();
        $this->load->model('Coins_model');
        $result =  $this->Coins_model->get_leaderboard_dailycheckin($post);
      
        $this->api_response_arry['data']  = $result;
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response();
    }

  
        

}

/* End of file cron.php */
