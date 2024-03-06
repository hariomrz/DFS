<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Fixed_open_predictor extends MYREST_Controller {

    var $op_entry_fee_min = 0;
    var $op_entry_fee_max = 9999;
    var $op_win_prize_min = 10;
    var $op_win_prize_max = 9999;
    var $source_desc_min = 3;
    var $source_desc_max = 140;
	var $proof_desc_min = 3;
    var $proof_desc_max = 140;
	
	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
    }

     /**
     * @since Nov 2019
     * @uses function to get prediction status
     * @method get_prediction_status 
     * 
    */
    function get_prediction_status_post()
    {
        $result = $this->get_master_setting();
        
        $this->api_response_arry['data']       = $result;
        $this->api_response_arry['response_code']      = rest_controller::HTTP_OK;
        $this->api_response();
      
    }

    /**
     * @since Nov 2019
     * @uses function to update coin status
     * @method update_coins_status_post 
     * 
    */
    function update_prediction_status_post()
    {
        $this->form_validation->set_rules('status', 'Status', 'trim|required');
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }

        $post= $this->input->post();
        $this->load->model('Fixed_open_predictor_model');
        $result = $this->Fixed_open_predictor_model->update_prediction_status($post['status']);

        $this->http_post_request("auth/get_app_master_list",array(),2);
        if($result)
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            if($post['status'] =='1')
            {

                $this->api_response_arry['global_error']  	= $this->lang->line('activate_prediction_status_success_msg');
            }
            else
            {

                $this->api_response_arry['global_error']  	= $this->lang->line('deactivated_prediction_status_success_msg');
            }
            $this->api_response();
        }
        else{
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('prediction_status_error_msg');
            $this->api_response();
        }

    }

    public function check_category() {
        $post_data = $this->post();
        $this->load->model("Fixed_open_predictor_model"); 
        $this->db = $this->Fixed_open_predictor_model->db_open_predictor;
        $data = $this->Fixed_open_predictor_model->get_single_row('name', CATEGORY, array("name" => $post_data['name']));

        if (empty($data)) {
            return TRUE;
        }
        $this->form_validation->set_message('check_category', $this->lang->line("category_name_already_exists"));
        return FALSE;
    }

    public function check_update_category() {
        $post_data = $this->post();
        $this->load->model("Fixed_open_predictor_model"); 
        $this->db = $this->Fixed_open_predictor_model->db_open_predictor;
        $data = $this->Fixed_open_predictor_model->get_single_row('name', CATEGORY, array("name" => $post_data['name'],"category_id<>" =>$post_data['category_id']));

        if (empty($data)) {
            return TRUE;
        }
        $this->form_validation->set_message('check_update_category', $this->lang->line("category_name_already_exists"));
        return FALSE;
    }

    public function add_category_post()
    {
        $post_params             = $this->input->post();
         if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'catetory Name', 'trim|required|callback_check_category');
            $this->form_validation->set_rules('image', 'Imafge', 'trim|required|max_length[200]');
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $post_data = $this->input->post();
              
        
            $category_arr = array(
                'name'                  => $post_data['name'],
                'image'                 => $post_data['image'],
                'status'                 => 1,
                'added_date'            => format_date(),
                'updated_date'          => format_date(),
                );
            
            $this->load->model('Fixed_open_predictor_model');
            $category_id = $this->Fixed_open_predictor_model->add_category($category_arr);

            if(empty($category_id))
            {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']         = "Category Not inserted!Try again.";
                $this->api_response_arry['service_name']    = 'add_category';
                $this->api_response();
            }    
         
            $this->response(array(config_item('rest_status_field_name') => TRUE, 'message' =>"Category has been added successfully." ,'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
           
        } else {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Category not added! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
         
    }

    public function update_category_post()
    {
        $post_params             = $this->input->post();
         if ($this->input->post()) {
            $this->form_validation->set_rules('category_id', 'Category ID', 'trim|required');
            $this->form_validation->set_rules('name', 'catetory Name', 'trim|required|callback_check_update_category');
            $this->form_validation->set_rules('image', 'Imafge', 'trim|max_length[200]');
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $post_data = $this->input->post();
              
        
            $category_arr = array();
            
            if(!empty($post_data['name']))
            {
                $category_arr['name']     = $post_data['name'];
            }

            if(!empty($post_data['image']))
            {
                $category_arr['image']     = $post_data['image'];
            }

            if(!empty($category_arr))
            {
                $category_arr['updated_date']     = format_date();
            }
            
            $this->load->model('Fixed_open_predictor_model');
            $row = $this->Fixed_open_predictor_model->update_category($post_data['category_id'],$category_arr);

            if(empty($row))
            {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']         = "Category Not updated!Try again.";
                $this->api_response_arry['service_name']    = 'add_category';
                $this->api_response();
            }    
         
            $this->push_s3_data_in_queue("lobby_category_list_open_predictor",array(),"delete"); 
            $this->response(array(config_item('rest_status_field_name') => TRUE, 'message' =>"Category has been updated successfully." ,'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
           
        } else {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Category not updated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
         
    }


    public function get_all_category_post()
	{
       
        $this->load->model('Fixed_open_predictor_model');
        $data = array();
        $data['category_list'] = $this->Fixed_open_predictor_model->get_category_by_status();

		
        $this->response(array(config_item('rest_status_field_name') => TRUE, 
        'data' =>$data ,
        'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
    }

    function do_upload_post() 
	{
		$data_post			= $this->post();
		
		$file_field_name	= 'userfile';
		$dir				= ROOT_PATH.CATEGORY_IMAGE_DIR;
		$s3_dir				= CATEGORY_IMAGE_DIR;

		$temp_file	= $_FILES['userfile']['tmp_name'];
		$ext		= pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals		= @getimagesize($temp_file);
		$width		= $vals[0];
		$height		= $vals[1];
		$image_height = "72";//"60";
        $image_width = "168";//"340";
        if ($height != $image_height || $width != $image_width)
		{
			
			$invalid_size = str_replace("{max_height}",$image_height,$this->lang->line('ad_image_invalid_size'));
			$invalid_size = str_replace("{max_width}",$image_width,$invalid_size);

			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $invalid_size;
			$this->api_response();
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
		}
		
		$file_name = time() . "." . $ext;
		$filePath = $s3_dir . $file_name;
		
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
                    $data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
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
			$config['max_width'] = '168';
			$config['max_height'] = '72';
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
				$image_path =  CATEGORY_IMAGE_DIR . $uploaded_data['file_name'];
				
				$data = array(
							'image_url'=> $image_path,
							'file_name'=> $uploaded_data['file_name']
					);
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $data;
				$this->api_response();
			}
			
		}		
    }
    
    private function validate_entry_fee($entry_fee)
    {

        if(!numbers_only($entry_fee))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please provide numbers only for entry fee",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);   
        }
        
        if ($entry_fee < $this->op_entry_fee_min || $entry_fee > $this->op_entry_fee_max)
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please provide entry fee ".$this->op_entry_fee_min." - ".$this->op_entry_fee_max,'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                
        }

    }

    private function validate_win_prize($win_prize)
    {

        if(!numbers_only($win_prize))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please provide numbers only for win prize",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);   
        }

        if ($win_prize < $this->op_win_prize_min || $win_prize > $this->op_win_prize_max)
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please provide win prize ".$this->op_win_prize_min." - ".$this->op_win_prize_max,'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                
        }

    }

    private function validate_source()
    {
        $source_desc =$this->input->post('source_desc');
        $source_url =$this->input->post('source_url');
        if(!empty($source_desc))
        {
            $source_length = strlen($source_desc);
            if ($source_length < $this->source_desc_min || $source_length > $this->source_desc_max)
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please provide source description between ".$this->source_desc_min." - ".$this->source_desc_max,'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);     
            }
        }

        if(!empty($source_url))
        {
            if (!filter_var($source_url, FILTER_VALIDATE_URL)) {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please enter valid source URL. It should start with http:// or https:// (e.g. https://www.vinfotech.com)",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                
            }
            
        }
    }

    /**
     *@method  create_prediction_post
     * @uses function to create predictions
     * @param Array
     */
    public function create_prediction_post()
    {
        
         if ($this->input->post()) {
            $this->form_validation->set_rules('category_id', 'Category ID', 'trim|required');
            $this->form_validation->set_rules('question', 'Question', 'trim|required|max_length[200]');
            $this->form_validation->set_rules('options[]', 'Option(s)', 'trim|required');
            $this->form_validation->set_rules('deadline_date', 'Closure Date & Time', 'trim|required');
            $this->form_validation->set_rules('site_rake', 'Site Rake', 'trim');
            $this->form_validation->set_rules('entry_type', 'Entry Type', 'trim');
            $this->form_validation->set_rules('entry_fee', 'Entry Fee', 'trim');
            $this->form_validation->set_rules('win_prize', 'Win Prize', 'trim');
            $this->form_validation->set_rules('source_url', 'Source Url', 'trim');
            $this->form_validation->set_rules('source_desc', 'Source Desc', 'trim');
            
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

           // $this->check_deadline_time();

           $this->validate_source();
            $post_data = $this->input->post();
            $prediction_arr = array();
            if(isset($post_data['entry_type']) && $post_data['entry_type']=='1' )
            {
                if(!isset($post_data['entry_fee']))
                {
                    $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please provide entry fee",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                }

                //validation entry fee
                $this->validate_entry_fee($post_data['entry_fee']);

                $prediction_arr['entry_type'] = $post_data['entry_type'];
                $prediction_arr['entry_fee'] = $post_data['entry_fee'];
                
                if(!isset($post_data['win_prize']))
                {
                    $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please provide winning prize",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                }

                //validate prize amount
                $this->validate_win_prize($post_data['win_prize']);
                $prediction_arr['win_prize'] = $post_data['win_prize'];
            }

            $options    = array_column($post_data['options'],"text");
            $option_count = count($options);

            foreach($options as $key =>  $option_text)
            {
                if(strlen($option_text) > 30)
                {
                    $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Option length can not be greater than 20 characters",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                }

                if(empty(trim($option_text)))
                {
                    unset($options[$key]);
                }

            }

            if(empty($options) || $option_count < 2 || $option_count > 4 )
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Invalid options",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            }    
            

            $feed_date_time = $post_data['deadline_date']." Asia/Kolkata";
                   // echo " old time : ".$feed_date_time;
            $date = new DateTime($feed_date_time);
            $tz = new DateTimeZone(DEFAULT_TIME_ZONE);
            $date->setTimezone($tz);
            //print_r($date);die;
            $deadline_date   = $date->format('Y-m-d H:i:s');

            $current_date = format_date();
            $prediction_arr['desc']= $post_data['question'];
            $prediction_arr['category_id']= $post_data['category_id'];
            $prediction_arr['deadline_date']= $deadline_date;
            $prediction_arr['added_date']= $current_date;
            $prediction_arr['updated_date']= $current_date;
            
            if(!empty($post_data['source_url']))
            {
                $prediction_arr['source_url']= $post_data['source_url'];
            }

            if(!empty($post_data['source_desc']))
            {
                $prediction_arr['source_desc']= $post_data['source_desc'];
            }

            $this->load->model('Fixed_open_predictor_model');
            $prediction_master_id = $this->Fixed_open_predictor_model->add_prediction($prediction_arr);

            if(empty($prediction_master_id))
            {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']         = "Prediction Not inserted!Try again.";
                $this->api_response_arry['service_name']    = 'create_prediction';
                $this->api_response();
            }    
    
             //insert options in db
            $options_arr = array();
            foreach ($options as $key => $value)
            {
                if(empty($value))
                {
                    continue;
                }    

                $options_arr[] = array(
                    'option'                => $value,
                    'prediction_master_id'  => $prediction_master_id,
                    'added_date'            => format_date(),
                    'updated_date'          => format_date()
                );
            }

            if(!empty($options_arr))
            {
                $this->Fixed_open_predictor_model->insert_prediction_option($options_arr);
            }  

            $this->push_s3_data_in_queue("lobby_category_list_open_predictor",array(),"delete"); 

            //node data update
            $one_prediction = $this->Fixed_open_predictor_model->get_prediction_details($prediction_master_id);
            $one_prediction['is_pin'] = 0;
            $one_prediction['total_predictions'] = 0;
            $node_url = "newFixedOpenPredictionAlert";
            $node_data=  array('category_id' => (int)$post_data['category_id'],
										'prediction' => $one_prediction);
            $this->notify_prediction_to_client($node_url,$node_data);

         
            //add to queue
            $this->rabbit_mq_push(array('prediction_master_id'=>$prediction_master_id,
            'category_id' => $post_data['category_id'],
            "prediction_action"    => 2 ,
            'question' => $post_data['question']
                ),'open_predictor');
      

                $this->response(array(config_item('rest_status_field_name') => TRUE, 'message' =>"Prediction has been added successfully." ,'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
           
        } else {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction not added! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
         
    }

 /**
     *@method  update_prediction_post
     * @uses function to Update predictions
     * @param Array
     */

    public function update_prediction_post()
    {
         if ($this->input->post()) {
            $this->form_validation->set_rules('prediction_master_id', 'Prediction Master ID', 'trim|required');
            $this->form_validation->set_rules('category_id', 'Category ID', 'trim|required');
            $this->form_validation->set_rules('question', 'Question', 'trim|required|max_length[200]');
            $this->form_validation->set_rules('options[]', 'Option(s)', 'trim|required');
            $this->form_validation->set_rules('deadline_date', 'Closure Date & Time', 'trim|required');
            $this->form_validation->set_rules('site_rake', 'Site Rake', 'trim');
            $this->form_validation->set_rules('source_url', 'Source Url', 'trim');
            $this->form_validation->set_rules('source_desc', 'Source Desc', 'trim');
            
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $post_data = $this->input->post();
            $this->load->model('Fixed_open_predictor_model');
            $this->db = $this->Fixed_open_predictor_model->db_open_predictor;
            $prediction_row = $this->Fixed_open_predictor_model->get_single_row('total_user_joined',PREDICTION_MASTER,array('prediction_master_id' => $post_data['prediction_master_id']));

            if(isset($prediction_row['total_user_joined']) && $prediction_row['total_user_joined'] > 0)
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "You can not update this Prediction, It is joined by few users.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            }

           // $this->check_deadline_time();

           $this->validate_source();
            $prediction_arr = array();

            $options    = array_column($post_data['options'],"text");
            $option_count = count($options);

            foreach($options as $key =>  $option_text)
            {
                if(strlen($option_text) > 30)
                {
                    $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Option length can not be greater than 20 characters",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                }

                if(empty(trim($option_text)))
                {
                    unset($options[$key]);
                }

            }

            if(empty($options) || $option_count < 2 || $option_count > 4 )
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Invalid options",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            }    
            

            $feed_date_time = $post_data['deadline_date']." Asia/Kolkata";
                   // echo " old time : ".$feed_date_time;
            $date = new DateTime($feed_date_time);
            $tz = new DateTimeZone(DEFAULT_TIME_ZONE);
            $date->setTimezone($tz);
            //print_r($date);die;
            $deadline_date   = $date->format('Y-m-d H:i:s');

            $current_date = format_date();
            $prediction_arr['desc']= $post_data['question'];
            $prediction_arr['category_id']= $post_data['category_id'];
            $prediction_arr['deadline_date']= $deadline_date;
            $prediction_arr['added_date']= $current_date;
            $prediction_arr['updated_date']= $current_date;
            
            if(!empty($post_data['source_url']))
            {
                $prediction_arr['source_url']= $post_data['source_url'];
            }

            if(!empty($post_data['source_desc']))
            {
                $prediction_arr['source_desc']= $post_data['source_desc'];
            }

            
            $affected_count = $this->Fixed_open_predictor_model->update_prediction($post_data['prediction_master_id'],$prediction_arr);

            if(empty($affected_count))
            {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']         = "Prediction Not updated!Try again.";
                $this->api_response_arry['service_name']    = 'create_prediction';
                $this->api_response();
            }    
    
             //insert options in db
            $options_arr = array();
            foreach ($options as $key => $value)
            {
                if(empty($value))
                {
                    continue;
                }    

                $options_arr[] = array(
                    'option'                => $value,
                    'prediction_master_id'  => $post_data['prediction_master_id'],
                    'added_date'            => format_date(),
                    'updated_date'          => format_date()
                );
            }

            if(!empty($options_arr))
            {
                $this->Fixed_open_predictor_model->update_prediction_option($post_data['prediction_master_id'],$options_arr);
            }  

            $this->push_s3_data_in_queue("lobby_category_list_open_predictor",array(),"delete"); 

            //node data update
            $one_prediction = $this->Fixed_open_predictor_model->get_prediction_details($post_data['prediction_master_id']);
            $one_prediction['is_pin'] = 0;
            $one_prediction['total_predictions'] = 0;
            $node_url = "UpdateFixedOpenPredictionAlert";
            $node_data=  array('category_id' => (int)$post_data['category_id'],
										'prediction' => $one_prediction);
            $this->notify_prediction_to_client($node_url,$node_data);

         
            //add to queue
            $this->rabbit_mq_push(array('prediction_master_id'=>$post_data['prediction_master_id'],
            'category_id' => $post_data['category_id'],
            "prediction_action"    => 2 ,
            'question' => $post_data['question']
                ),'open_predictor');
      

                $this->response(array(config_item('rest_status_field_name') => TRUE, 'message' =>"Prediction has been updated successfully." ,'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
           
        } else {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction not updated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
         
    }

     /**
     * @method get_category_list_by_status
     * @uses function to get category by status type
     * @param $_POST get  
     * **/
    function get_category_list_by_status_post()
    {
        $this->form_validation->set_rules('status', 'Status', 'trim|required'); //1=> active , 2=> invactive

        if (!$this->form_validation->run()) {
            $this->send_validation_errors('get_category_list_by_status');
        }

        //get live match list
        $this->load->model('Fixed_open_predictor_model');

        $status = $this->input->post('status');
        $_POST['limit']=1000;
        $_POST['offset']=0;
        $category_list = $this->Fixed_open_predictor_model->get_all_category();
      
        $data = array();
        $data['category_list'] = $category_list['result'];
        $data['total'] = $category_list['total'];
        $this->response(array(config_item('rest_status_field_name') => TRUE,
        'data' => $data ,
        'response_code'=>rest_controller::HTTP_OK),
         rest_controller::HTTP_OK);
    }

    public function get_all_prediction_post()
	{
        $this->form_validation->set_rules('category_id', 'category ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $this->load->model('Fixed_open_predictor_model');
        $data = array();
        $data['predictions'] = $this->Fixed_open_predictor_model->get_all_prediction();

		
        $this->response(array(config_item('rest_status_field_name') => TRUE, 
        'data' =>$data ,
        'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
    }

    /**
     * @since jan 2020
     * @uses function to play pause
     * @method pause_play_prediction 
     * 
    */
    function pause_play_prediction_post()
    {
        $this->form_validation->set_rules('pause', 'Pause', 'trim|required');
        $this->form_validation->set_rules('prediction_master_id', 'Pause', 'trim|required');
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }

        $post= $this->input->post();

        if(!in_array($post['pause'],array(0,1)))
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('pause_value_invalid_err');
            $this->api_response();
        }


        $this->load->model('Fixed_open_predictor_model');

        $prediction = $this->Fixed_open_predictor_model->get_one_prediction($post['prediction_master_id']);

        if(!empty($prediction))
        {
            if(in_array($prediction['status'],array(1,2)))//prediction closed or prize distributed
            {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->lang->line('prediction_processed_error_msg');
                $this->api_response();
            }
        }

        $result = $this->Fixed_open_predictor_model->pause_play_prediction($post['pause'],$post['prediction_master_id']);

       
        if($result)
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            
            $node_data=  array('prediction_master_id' => $post['prediction_master_id'],
            'category_id' => $prediction['category_id']);
            if($post['pause'] ==1)
            {
                $node_data['pause'] = 1;
                $this->api_response_arry['message']  	= $this->lang->line('prediction_pause_success_msg');
            }
            else
            {
                $node_data['pause'] = 0;
                //node data update
                $one_prediction = $this->Fixed_open_predictor_model->get_prediction_details($post['prediction_master_id']);
                $node_data['prediction'] = $one_prediction;
                $this->api_response_arry['message']  	= $this->lang->line('prediction_resume_success_msg');
            }

            $node_url = "pausePlayFixedOpenPrediction";
         
            $this->notify_prediction_to_client($node_url,$node_data);
            $this->api_response();
        }
        else{
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('prediction_status_error_msg');
            $this->api_response();
        }

    }

    public function update_pin_prediction_post()
    {
        $this->form_validation->set_rules('prediction_master_id', 'Prediction Master Id', 'trim|required');
        $this->form_validation->set_rules('is_pin', 'is_pin', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post= $this->input->post();
        $this->load->model('Fixed_open_predictor_model');
        $result = $this->Fixed_open_predictor_model->update_pin_prediction($post['is_pin'],$post['prediction_master_id']);
        if($result)
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            $this->api_response_arry['global_error']  	= $this->lang->line('prediction_pin_success_msg');
            $this->api_response();
        }
        else{
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('prediction_pin_error_msg');
            $this->api_response();
        }

    }

      /**
     * [delete_prediction_post description]
     * @uses :- delete prediction
     * @param Number 1,2,3,4 for tab_no, sports id
     */
    function delete_prediction_post()
    {
        $this->form_validation->set_rules('prediction_master_id', 'Prediction ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $prediction_master_id = $this->input->post('prediction_master_id');
        $this->load->model('Fixed_open_predictor_model');
        $prediction_result = $this->Fixed_open_predictor_model->get_one_prediction($prediction_master_id);

        if(empty($prediction_result))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Not a valid prediction.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
       
        if( $prediction_result['status'] > 0)
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Only open prediction can be deleted.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }   

        $delete_result = $this->Fixed_open_predictor_model->delete_prediction($prediction_master_id);

        if($prediction_result['total_user_joined'] > 0)
        {
            //refund process
            $this->rabbit_mq_push(array('prediction_master_id'=>$prediction_master_id,
            "prediction_action"    => 0 
            ),'open_predictor');
        }

        $node_url = "deleteFixedOpenPrediction";
            $node_data=  array('category_id' => (int)$prediction_result['category_id'],
										'prediction_master_id' => $prediction_master_id);
            $this->notify_prediction_to_client($node_url,$node_data);

        $this->response(array(config_item('rest_status_field_name') => TRUE,
        'message' => 'Prediction Deleted.',
        'response_code'=>rest_controller::HTTP_OK),
         rest_controller::HTTP_OK);
    }


    private function validate_prediction_proof()
    {
        $proof_desc =$this->input->post('proof_desc');
        if(!empty($proof_desc))
        {
            $proof_desc_length = strlen($proof_desc);
            if ($proof_desc_length < $this->proof_desc_min || $proof_desc_length > $this->proof_desc_max)
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please provide proof description between ".$this->proof_desc_min." - ".$this->proof_desc_max,'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);     
            }
        }

    }

    public function update_prediction_proof_post()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('prediction_master_id', 'Prediction Master Id', 'trim|required');
            $this->form_validation->set_rules('proof_desc', 'Proof description', 'trim');
            $this->form_validation->set_rules('proof_image', 'Proof Image', 'trim');
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $this->load->model('Fixed_open_predictor_model');
            $post = $this->input->post();

            $this->validate_prediction_proof();
            $prediction = $this->Fixed_open_predictor_model->get_prediction_answer($post['prediction_master_id']);
    
            if(empty($prediction))
            {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->lang->line('prediction_processed_error_msg');
                $this->api_response();
            }

            $prediction_status_arr = array();
            if(!empty($post['proof_desc']))
            {
               $prediction_status_arr['proof_desc'] = $post['proof_desc'];
            }

            if(!empty($post['proof_image']))
            {
               $prediction_status_arr['proof_image'] = $post['proof_image'];
            }

            if(!empty($prediction_status_arr))
            {
                $this->Fixed_open_predictor_model->update_prediction_result_status($post['prediction_master_id'],$prediction_status_arr);
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
                $this->api_response_arry['message']  	= "Prediction proof updated.";
                $this->api_response();
            }
            else
            {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= "No data to update";
                $this->api_response();

            }


        }

    }

          /**
     * [submit_prediction_answer_post description]
     * @uses :- submit prediction answer
     * @param  
     */
    public function submit_prediction_answer_post()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('prediction_master_id', 'Prediction Master Id', 'trim|required');
            $this->form_validation->set_rules('prediction_option_id', 'Prediction Option', 'trim|required');
            $this->form_validation->set_rules('proof_desc', 'Proof description', 'trim');
            $this->form_validation->set_rules('proof_image', 'Proof Image', 'trim');
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $this->load->model('Fixed_open_predictor_model');
            $post = $this->input->post();

            $this->validate_prediction_proof();
            $prediction = $this->Fixed_open_predictor_model->get_prediction_answer($post['prediction_master_id']);
    
            if(!empty($prediction))
            {
               
                if(isset($prediction['is_correct']) && $prediction['is_correct']=='1')
                {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= $this->lang->line('prediction_processed_error_msg');
                    $this->api_response();
                }
                
            }
            else
            { 
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->lang->line('invalid_prediction');
                $this->api_response();
            }
          
            $prediction_status_arr = array(
                "status"                 => 2, 
                "updated_date"           => format_date()
             );  

             if(!empty($post['proof_desc']))
             {
                $prediction_status_arr['proof_desc'] = $post['proof_desc'];
             }

             if(!empty($post['proof_image']))
             {
                $prediction_status_arr['proof_image'] = $post['proof_image'];
             }

            $this->db_open_predictor		= $this->load->database('db_fixed_open_predictor', TRUE);
            $this->db_open_predictor->trans_start();
            $this->Fixed_open_predictor_model->update_prediction_results($post['prediction_option_id']);
            $this->Fixed_open_predictor_model->update_prediction_result_status($post['prediction_master_id'],$prediction_status_arr);

            $this->db_open_predictor->trans_complete();

            if ($this->db_open_predictor->trans_status() === FALSE)
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction result not udpated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
              
                    // generate an error... or use the log_message() function to log your error
            }
            else
            {
                //get prediction data
               $prediction_data = $this->get_prediction_with_options();

                $queue_content = array(
                    "prediction_master_id" => $post['prediction_master_id'],
                    "status"               => 1,
                    "added_on_queue"       => format_date(),
                    "prediction_action"    => 1 ,//day
                    "prediction_data" => $prediction_data,
                    "deadline_date" => $prediction_data['deadline_date']
                 );
                 
                 $this->rabbit_mq_push($queue_content, 'fixed_open_predictor');
                 $queue_content = array(
                    "prediction_master_id" => $post['prediction_master_id'],
                    "status"               => 1,
                    "added_on_queue"       => format_date(),
                    "prediction_action"    => 2 ,//week
                    "prediction_data" => $prediction_data,
                    "deadline_date" => $prediction_data['deadline_date']
                 );

                 $this->rabbit_mq_push($queue_content, 'fixed_open_predictor');
                 $queue_content = array(
                    "prediction_master_id" => $post['prediction_master_id'],
                    "status"               => 1,
                    "added_on_queue"       => format_date(),
                    "prediction_action"    => 3 ,//month
                    "prediction_data" => $prediction_data,
                    "deadline_date" => $prediction_data['deadline_date']
                 );
             $this->rabbit_mq_push($queue_content, 'fixed_open_predictor');

                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
                $this->api_response_arry['message']  	= $this->lang->line('prediction_result_submited');
                $this->api_response();
            }    

        }
        else 
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction result not udpated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    function do_upload_proof_image_post() 
	{
		$data_post			= $this->post();
		
		$file_field_name	= 'userfile';
		$dir				= ROOT_PATH.OPEN_PREDICTOR_PROOF_IMAGE_DIR;
		$s3_dir				= OPEN_PREDICTOR_PROOF_IMAGE_DIR;

		$temp_file	= $_FILES['userfile']['tmp_name'];
		$ext		= pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals		= @getimagesize($temp_file);
		$width		= $vals[0];
		$height		= $vals[1];
		// $image_height = "72";//"60";
        // $image_width = "168";//"340";
        // if ($height != $image_height || $width != $image_width)
		// {
			
		// 	$invalid_size = str_replace("{max_height}",$image_height,$this->lang->line('ad_image_invalid_size'));
		// 	$invalid_size = str_replace("{max_width}",$image_width,$invalid_size);

		// 	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		// 	$this->api_response_arry['message']			= $invalid_size;
		// 	$this->api_response();
		// }

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
		}
		
		$file_name = time() . "." . $ext;
		$filePath = $s3_dir . $file_name;
		
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
                    $data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
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
			//$config['max_width'] = $image_width;
			//$config['max_height'] = $image_height;
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
				$image_path =  OPEN_PREDICTOR_PROOF_IMAGE_DIR . $uploaded_data['file_name'];
				
				$data = array(
							'image_url'=> $image_path,
							'file_name'=> $uploaded_data['file_name']
					);
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $data;
				$this->api_response();
			}
			
		}		
    }

    private function get_prediction_with_options()
    {
        $post = $this->input->post();
        $this->load->model('Fixed_open_predictor_model');
        $result = $this->Fixed_open_predictor_model->get_one_prediction_details($post['prediction_master_id']);

        $category_data = $this->Fixed_open_predictor_model->get_category_details($result[0]['category_id']);

        $prediction_data = array_merge($result[0],$category_data);
        return $prediction_data;
    }

    /**
     * [get_prediction_participants description]
     * @uses :- get participants
     * @param Number prediction master id
     */
    public function get_prediction_participants_post()
	{
        $this->form_validation->set_rules('prediction_master_id', 'Prediction ID', 'trim|required');
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $prediction_master_id = $this->input->post('prediction_master_id');
            $this->load->model('Fixed_open_predictor_model');
            $result = $this->Fixed_open_predictor_model->get_prediction_participants($prediction_master_id);

            if(!empty($result['prediction_participants']))
            {
                $user_ids = array_unique( array_column($result['prediction_participants'],'user_id'));
                $user_details = $this->Fixed_open_predictor_model->get_users_by_ids($user_ids);
                if(!empty($user_details))
                {
                    $user_details = array_column($user_details,'user_name','user_id');
                }

                foreach($result['prediction_participants'] as & $val)
                {
                    $val['username'] = '';
                    if(isset($user_details[$val['user_id']]))
                    {
                        $val['user_name'] =  $user_details[$val['user_id']];
                    }
                }

            }

            $this->response(array(config_item('rest_status_field_name') => TRUE,
             'data' => $result,
             'response_code'=>rest_controller::HTTP_OK),
              rest_controller::HTTP_OK);
            
    }

     /**
     * [get_trending_predictions description]
     * @uses :- get trending predictions,recent, popular, 1 bid no bid
     * @param Number 1,2,3,4 for tab_no, sports id
     */
    public function get_trending_predictions_post()
    {
        $this->form_validation->set_rules('tab_no', 'tab no', 'trim|required');
        
        $tab_no = $this->input->post('tab_no');
        
        $this->config->load('fixed_open_predictor_config');
        $trending_types = $this->config->item('trending_prediction');

        $this->load->model('Fixed_open_predictor_model');

        if(!isset($trending_types[$tab_no]['func']))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please select a valid tab.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }    

        $function_name = $trending_types[$tab_no]['func'];

        $result = $this->Fixed_open_predictor_model->$function_name();    

        $this->response(array(config_item('rest_status_field_name') => TRUE,
        'data' => $result,
        'response_code'=>rest_controller::HTTP_OK),
         rest_controller::HTTP_OK);

    }

    /**
     * [delete_category description]
     * @uses :- delete category if not prediction created for category
     * @param Number category id
     */
    public function delete_category_post()
    {
        $this->form_validation->set_rules('category_id', 'Category ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $category_id = $this->input->post('category_id');
        //check if category prediction exists or not
        $this->load->model('Fixed_open_predictor_model');

        $prediction = $this->Fixed_open_predictor_model->get_category_prediction($category_id);

        if(empty($prediction))
        {
           //now category can be deleted 
           $this->Fixed_open_predictor_model->delete_category($category_id);
           $this->response(array(config_item('rest_status_field_name') => TRUE,
            'data' => array(),
            'message' => "Category deleted Successfully.",
            'response_code'=>rest_controller::HTTP_OK),
            rest_controller::HTTP_OK);

        }
        else
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "You can not delete this category.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * [get_prediction_counts description]
     * @uses :- get prediction counts
     * @param Number sports id
     */

    function get_prediction_counts_post()
    {
       
        $this->config->load('fixed_open_predictor_config');
        $trending_types = $this->config->item('trending_prediction');

        $this->load->model('Fixed_open_predictor_model');

        if(!isset($trending_types[3]['func']) || !isset($trending_types[4]['func']))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please select a valid tab.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }    

        $one_bid_function_name = $trending_types[3]['func'];
        $no_bid_function_name = $trending_types[4]['func'];

        $data = array();
        $_POST['tab_no'] = 3;
        $data['one_bid_count'] = $this->Fixed_open_predictor_model->$one_bid_function_name(TRUE);    
        $_POST['tab_no'] = 4;
        $data['no_bid_count'] = $this->Fixed_open_predictor_model->$no_bid_function_name(TRUE);    

        $this->response(array(config_item('rest_status_field_name') => TRUE,
        'data' => $data,
        'response_code'=>rest_controller::HTTP_OK),
         rest_controller::HTTP_OK);

    }

    function get_attempts_vs_users_graph_post()
    {
        $post = $this->input->post();

        if(isset($post['filter']) && $post['filter'] == 'weekly' )
        {
            $this->get_attempts_vs_users_graph_week_post();
        }

        if(isset($post['filter']) && $post['filter'] == 'monthly' )
        {
            $this->get_attempts_vs_users_graph_monthly_post();
        }


        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -70 days'));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $this->load->model('Fixed_open_predictor_model');

        //$data
            /**
             * {
            *  name: 'Tokyo',
            * data: [7.0, 6.9, 9.5, 14.5, 18.4, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
            * }
                    * **/

        $Dates = get_dates_from_range($post['from_date'], $post['to_date']); 

       
        $result =  $this->Fixed_open_predictor_model->get_prediction_attempts($post);

        $data = array();
        $str_dates = array();
       foreach($Dates as $oneDate)
       {
           $date = strtotime($oneDate) ;
           $str_dates[] = $date;
           foreach($result['result'] as $row)
           {  
               $main_date = strtotime($row['date_added']);

               if(!in_array($main_date,$str_dates))
               {
                   $data['attempts_data'][$date] = 0;
               }
               else
               {
                   if(isset($data['attempts_data'][$main_date]))
                    {
                        $data['attempts_data'][$main_date] += (int)$row['attempts'];
                    }
                    else
                    {
                        $data['attempts_data'][$main_date] = (int)$row['attempts'];
                    }
               }  
           }
       }

       $user_result = $this->Fixed_open_predictor_model->get_prediction_attempted_users($post);
       $str_dates = array();
       foreach($Dates as $oneDate)
       {
           $date = strtotime($oneDate) ;
           $str_dates[] = $date;
           foreach($user_result['result'] as $row)
           {  
               $main_date = strtotime($row['date_added']);

               if(!in_array($main_date,$str_dates))
               {
                   $data['user_data'][$date] = 0;
               }
               else
               {
                   if(isset($data['user_data'][$main_date]))
                    {
                        $data['user_data'][$main_date] += (int)$row['user_count'];
                    }
                    else
                    {
                        $data['user_data'][$main_date] = (int)$row['user_count'];
                    }
               }  
           }
       }

       foreach($Dates as &$date)
       {
           $date = date('d M',strtotime($date));
       }

       if(!empty($data['attempts_data']))
       {
           $data['attempts_data'] = array_values($data['attempts_data']);
       }

       if(!empty($data['user_data']))
       {
           $data['user_data'] = array_values($data['user_data']);
       }


        $this->api_response_arry['data']['graph_data'] = $data;
        $this->api_response_arry['data']['total_user'] 	= $user_result['total'];
        $this->api_response_arry['data']['total_attempts'] 	= $result['total'];
        $this->api_response_arry['data']['dates'] 	= $Dates;
        $this->api_response(); 


    }

    /**
     * @method get_coins_vs_users_graph_week
     * @uses function for weekly
     * @param Array from_date and to_date
    */
    function get_attempts_vs_users_graph_week_post()
    {
        $post = $this->input->post();
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -70 days'));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $this->load->model('Fixed_open_predictor_model');

        $result =  $this->Fixed_open_predictor_model->get_prediction_attempts_weekly($post);

        $categories = array();
        $series_data = array();
        $total_attempts = 0;
        if(!empty($result['result']))
        {
            $categories = array_column($result['result'],'created');
            $series_data = array_column($result['result'],'attempts');
            $total_attempts = array_sum($series_data);
        }

        foreach($series_data as &$val)
        {
            $val = (int)$val;

        }
        //get users count
        $user_result =  $this->Fixed_open_predictor_model->get_prediction_users_weekly($post);
        $user_data = array();
        $total_users = 0;
        if(!empty($user_result['result']))
        {
            $user_data = array_column($user_result['result'],'user_count');
            $total_users = array_sum($user_data);
        }

        foreach($user_data as &$val)
        {
            $val = (int)$val;

        }

        $this->api_response_arry['data']['graph_data'] = array('user_data'=> $user_data,'attempts_data' => $series_data);
        $this->api_response_arry['data']['total_user'] 	= $total_users;
        $this->api_response_arry['data']['total_attempts'] 	= $total_attempts;
        $this->api_response_arry['data']['dates'] 	= $categories;
        $this->api_response(); 
    }

    /**
     * @method get_coins_vs_users_graph_monthly
     * @uses function for monthly
     * @param Array from_date and to_date
    */
    function get_attempts_vs_users_graph_monthly_post()
    {
        $post = $this->input->post();
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -70 days'));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $this->load->model('Fixed_open_predictor_model');

        $result =  $this->Fixed_open_predictor_model->get_prediction_attempts_monthly($post);

        $categories = array();
        $series_data = array();
        $total_attempts = 0;
        if(!empty($result['result']))
        {
            $categories = array_column($result['result'],'month_year');
            $series_data = array_column($result['result'],'attempts');
            $total_attempts = array_sum($series_data);
        }

        foreach($series_data as &$val)
        {
            $val = (int)$val;

        }

        //get users count
        $user_result =  $this->Fixed_open_predictor_model->get_prediction_users_monthly($post);
        $user_data = array();
        $total_users = 0;
        if(!empty($user_result['result']))
        {
            $user_data = array_column($user_result['result'],'user_count');
            $total_users = array_sum($user_data);
        }

        foreach($user_data as &$val)
        {
            $val = (int)$val;

        }

        $this->api_response_arry['data']['graph_data'] = array('user_data'=> $user_data,'attempts_data' => $series_data);
        $this->api_response_arry['data']['total_user'] 	= $total_users;
        $this->api_response_arry['data']['total_attempts'] 	= $total_attempts;
        $this->api_response_arry['data']['dates'] 	= $categories;
        $this->api_response(); 
    }


    /**
     * @method get_top_category_graph_post
     * @uses function for top category graph
     * @param Array from_date and to_date
    */
    function get_top_category_graph_post()
    {
        $post = $this->input->post();
        $this->load->model('Fixed_open_predictor_model');
        $data = array();
        $data['top_category_data'] =$this->Fixed_open_predictor_model->get_top_category();
        $categories = array_column($data['top_category_data'],'name');
        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['data']['series'] = array_slice(array_values($data['top_category_data']),0,10) ;
        $this->api_response_arry['data']['categories'] = array_slice($categories,0,10) ;
        $this->api_response_arry['data']['category_count'] = count($categories) ;
        $this->api_response();   

        }
    
     /**
     * [most_win_leaderboard_post description]
     * @uses :- function to get most win leader board
     * @param NA
     */
    function most_correct_predictions_leaderboard_post()
    {
        $this->load->model('Fixed_open_predictor_model');
        $post = $this->input->post();
        $result =$this->Fixed_open_predictor_model->get_most_correct_predictions_leaderboard($post);

        $this->load->model('user/User_model');
        $user_ids = array_unique( array_column($result['list'],'user_id'));
        $user_details = $this->User_model->get_users_by_ids($user_ids);
        if(!empty($user_details))
        {
            $user_details = array_column($user_details,'user_name','user_id');
        }

        foreach($result['list'] as $key => &$val)
        {
            $val['user_name'] = '';
            if(isset($user_details[$val['user_id']]))
            {
                $val['user_name'] =  $user_details[$val['user_id']];
            }
        }
        $count_result = $this->Fixed_open_predictor_model->get_most_correct_counts();
        $this->api_response_arry['data']['list'] = $result['list'];
        $this->api_response_arry['data']['total'] 	= $count_result['total'];
        $this->api_response_arry['data']['next_offset'] = $result['next_offset'];
        $this->api_response(); 

    }

    /**
     * [most_bid_leaderboard_post description]
     * @uses :- function to get most bid leaderboard
     * @param NA
     */
    function most_attempts_leaderboard_post()
    {
        $this->load->model('Fixed_open_predictor_model');
        $post = $this->input->post();
        $result =$this->Fixed_open_predictor_model->most_attempts_leaderboard($post);
        $count_result = $this->Fixed_open_predictor_model->get_most_attempt_count();

        $this->load->model('user/User_model');
        $user_ids = array_unique( array_column($result['list'],'user_id'));
        $user_details = $this->User_model->get_users_by_ids($user_ids);
        if(!empty($user_details))
        {
            $user_details = array_column($user_details,'user_name','user_id');
        }

        foreach($result['list'] as $key => &$val)
        {
            $val['user_name'] = '';
            if(isset($user_details[$val['user_id']]))
            {
                $val['user_name'] =  $user_details[$val['user_id']];
            }
        }
        $this->api_response_arry['data']['list'] = $result['list'];
        $this->api_response_arry['data']['total'] 	= $count_result['total'];
        $this->api_response_arry['data']['next_offset'] = $result['next_offset'];
        $this->api_response(); 

    }


    private function validate_sponsor($post)
    {
        if(empty($post['sponsor_logo']) || empty($post['sponsor_link']) || empty($post['sponsor_name']))
        {
            $this->api_response_arry['response_code'] 	= 500;
            $this->api_response_arry['data']  			= array();
            $this->api_response_arry['global_error']    = "Please provide all sponsor details.";
            $this->api_response();
        }

        if(!empty($post['sponsor_link']) && !filter_var($post['sponsor_link'], FILTER_VALIDATE_URL))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please enter valid source link. It should start with http:// or https:// (e.g. https://www.vinfotech.com)",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * [update_prizes_post description]
     * @uses :- function to update prizes
     * @param NA
     */
    public function update_prizes_post()
    {
        $this->form_validation->set_rules('prize_category', 'Prize Type', 'trim|required');//1=day , 2=> week, 3=> month
        $this->form_validation->set_rules('allow_prize', 'Allow Prize', 'trim|required');
        $this->form_validation->set_rules('allow_sponsor', 'Allow Sponsor', 'trim|required');
        $this->form_validation->set_rules('sponsor_logo', 'Sponsor logo', 'trim');
        $this->form_validation->set_rules('sponsor_link', 'Sponsor link', 'trim');
        $this->form_validation->set_rules('sponsor_name', 'Sponsor name', 'trim');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        if(!in_array($post['prize_category'],array(1,2,3)))
        {
            $this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['data']  			= array();
			$this->api_response_arry['global_error']    = "Please select a prize category.";
			$this->api_response();
        }

        $data = array();
        $data['allow_prize'] = $post['allow_prize'];
        $data['allow_sponsor'] = 0;
        $data['prize_distribution_detail'] = null;
        if($post['allow_prize'])
        {
            if(empty($post['prize_distribution_details']))
            {
                $this->api_response_arry['response_code'] 	= 500;
                $this->api_response_arry['data']  			= array();
                $this->api_response_arry['global_error']    = "Please provide prize distribution details.";
                $this->api_response();
            }
            else
            {
                //prize_type 0-Bonus,1-Real,2-Point/Coins, 3- Merchandise
                $data['prize_distribution_detail'] =json_encode($post['prize_distribution_details']);
            }
        }
        
        if(!empty($post['allow_sponsor']) && $post['allow_sponsor'] == 1)
        {
            $this->validate_sponsor($post);
            $data['allow_sponsor'] = $post['allow_sponsor'];
            $data['sponsor_logo'] = $post['sponsor_logo'];
            $data['sponsor_link'] = $post['sponsor_link'];
            $data['sponsor_name'] = $post['sponsor_name'];
        }
        else
        {
            $data['sponsor_logo'] = null;
            $data['sponsor_link'] = null;
            $data['sponsor_name'] = null;
        }

        //update prizes
        $this->load->model('Fixed_open_predictor_model');

        $update_count = $this->Fixed_open_predictor_model->update_prizes($data,array('prize_category' => $post['prize_category']));

        if(!empty($update_count))
        {
           //now category can be deleted 
           $this->response(array(config_item('rest_status_field_name') => TRUE,
            'data' => array(),
            'message' => "Prize updated Successfully.",
            'response_code'=>rest_controller::HTTP_OK),
            rest_controller::HTTP_OK);

        }
        else
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "No changes to update",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_prediction_prizes_post()
    {
        $this->load->model('Fixed_open_predictor_model');
        $result = $this->Fixed_open_predictor_model->get_prediction_prizes();

        if(!empty($result))
        {
            foreach($result as &$row)
            {
                if(!empty($row))
                {
                    $row['prize_distribution_detail'] = json_decode($row['prize_distribution_detail'],TRUE);
                }
            }
        }

        $this->response(array(config_item('rest_status_field_name') => TRUE,
            'data' => $result,
            'response_code'=>rest_controller::HTTP_OK),
            rest_controller::HTTP_OK);

    }

    /**
     * @method do_upload_sponsor_image
     * @uses uplaod sponsor images
     *  
     * */
    function do_upload_sponsor_image_post() 
	{
		$data_post			= $this->post();
		
		$file_field_name	= 'userfile';
		$dir				= ROOT_PATH.FIXED_OPEN_PREDICTOR_SPONSOR_IMAGE_DIR;
		$s3_dir				= FIXED_OPEN_PREDICTOR_SPONSOR_IMAGE_DIR;

		$temp_file	= $_FILES['userfile']['tmp_name'];
		$ext		= pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals		= @getimagesize($temp_file);
		$width		= $vals[0];
		$height		= $vals[1];
		// $image_height = "72";//"60";
        // $image_width = "168";//"340";
        // if ($height != $image_height || $width != $image_width)
		// {
			
		// 	$invalid_size = str_replace("{max_height}",$image_height,$this->lang->line('ad_image_invalid_size'));
		// 	$invalid_size = str_replace("{max_width}",$image_width,$invalid_size);

		// 	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		// 	$this->api_response_arry['message']			= $invalid_size;
		// 	$this->api_response();
		// }

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
		}
		
		$file_name = time() . "." . $ext;
		$filePath = $s3_dir . $file_name;
		
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
                    $data = array( 'file_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
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
			//$config['max_width'] = $image_width;
			//$config['max_height'] = $image_height;
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
				$image_path =  FIXED_OPEN_PREDICTOR_SPONSOR_IMAGE_DIR . $uploaded_data['file_name'];
				
				$data = array(
							'image_url'=> $image_path,
							'file_name'=> $uploaded_data['file_name']
					);
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $data;
				$this->api_response();
			}
			
		}		
    }

    private function get_day_dates($date,$filter)
    {
       
        $from_date = date('Y-m-d',strtotime($date.$filter)).' 00:00:00';
        $to_date = date('Y-m-d',strtotime($date.$filter)).' 23:59:59';
        return array(
            'label' => date('Y-m-d',strtotime($from_date)),
            'from_date' => $from_date,
            'to_date' => $to_date
        );
    }

    private function get_week_dates($date)
    {
        $week_start_date = date("Y-m-d",strtotime($date));
        list($week_from_date, $week_to_date) = x_week_range($week_start_date);

        $label =date('d-M',strtotime($week_from_date)).'-'.date('d-M',strtotime($week_to_date));
        return array(
            'label' => $label,
            'from_date' => $week_from_date,
            'to_date' => $week_to_date
        );
    }

    private function get_month_dates($date)
    {
     
        $from_date = date('Y-m-01',strtotime($date)).' 00:00:00';
        $to_date = date('Y-m-t',strtotime($date)).' 23:59:59';
        $label = date('M',strtotime($date));

        return array(
            'label' => $label,
            'from_date' => $from_date,
            'to_date' => $to_date
        );
    }


    /**
     * @method get_open_predictor_leaderboard
     * @uses leaderboard API
     * **/
    public function get_leaderboard_master_data_post()
    {
        $current_date = format_date();
        //get day number and date
        $day_number = date("z",strtotime($current_date))+1;
        $day_date = date("Y-m-d",strtotime($current_date.' -1 day'));
            
        $data['day_filter'] = array();
        $last_day =$this->get_day_dates($current_date,' -1 day');
        
        $data['day_filter'][0]['label'] = 'yesterday';
        $data['day_filter'][0]['from_date'] = $last_day['from_date'];
        $data['day_filter'][0]['to_date'] = $last_day['to_date'];

        $data['day_filter'][] =$this->get_day_dates($current_date,' -2 day');
        $data['day_filter'][] =$this->get_day_dates($current_date,' -3 day');

        //get day number and date
        $date = date('Y-m-d',strtotime($current_date));
        list($from_date, $to_date) = x_week_range($date);
        $week_number = date("W",strtotime($from_date));
        $week_start_date = $from_date;
        $week_end_date = $to_date;
            
            
        $data['week_filter'] = array();
        $last_week =$this->get_week_dates($current_date.' -7 day');
        
        $data['week_filter'][0]['label'] = 'last_week';
        $data['week_filter'][0]['from_date'] = $last_week['from_date'];
        $data['week_filter'][0]['to_date'] = $last_week['to_date'];
        
        $data['week_filter'][] =$this->get_week_dates($current_date.' -14 day');
        $data['week_filter'][] =$this->get_week_dates($current_date.' -21 day');
       
        $last_month =$this->get_month_dates($current_date.' -1 month');

        $data['month_filter'][0]['label'] = 'last_month';
        $data['month_filter'][0]['from_date'] = $last_month['from_date'];
        $data['month_filter'][0]['to_date'] = $last_month['to_date'];
        $data['month_filter'][] =$this->get_month_dates($current_date.' -2 month');
        $data['month_filter'][] =$this->get_month_dates($current_date.' -3 month');

        $this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
        $this->api_response_arry['data']			= $data;
        $this->api_response();
    }

    /**
     * @method get_open_predictor_leaderboard
     * @uses leaderboard API
     * **/
    function get_open_predictor_leaderboard_post()
    {
        $post = $this->input->post();
        $current_date = format_date();
        
        if(isset($post['filter']) && $post['filter'] == 'today')
		{
            //get day number and date
            $day_number = date("z",strtotime($current_date))+1;
		    $day_date = date("Y-m-d",strtotime($current_date)).' 00:00:00';
            $this-> get_day_leaderboard($day_number,$day_date);
        }
        
        if(isset($post['filter']) && $post['filter'] == 'day_date')
		{
            
            //get day number and date
            $day_number = date("z",strtotime($post['filter_date']))+1;
		    $day_date = date("Y-m-d",strtotime($post['filter_date'])).' 00:00:00';
            $this-> get_day_leaderboard($day_number,$day_date);
        }
        
        if(isset($post['filter']) && $post['filter'] == 'this_week')
		{
            //get day number and date
            $date = date('Y-m-d',strtotime($current_date));
            list($from_date, $to_date) = x_week_range($date);
            $week_number = date("W",strtotime($from_date));
            $week_start_date = $from_date;
            $week_end_date = $to_date;
            
            $this-> get_week_leaderboard($week_number,$week_start_date);
        }

        if(isset($post['filter']) && $post['filter'] == 'week_date')
		{
            //get day number and date
            $date = date('Y-m-d',strtotime($post['filter_date']));
            list($from_date, $to_date) = x_week_range($date);
            $week_number = date("W",strtotime($from_date));
            $week_start_date = $from_date;
            $week_end_date = $to_date;
            
            $this-> get_week_leaderboard($week_number,$week_start_date);
        }

        if(isset($post['filter']) && $post['filter'] == 'this_month')
		{
            //get day number and date
            $from_date = date('Y-m-01',strtotime($current_date)).' 00:00:00';
            $to_date = date('Y-m-t',strtotime($current_date)).' 23:59:59';
            $month_number = date("m",strtotime($from_date));
            $month_start_date = $from_date;
            $month_end_date = $to_date;

            
            $this-> get_month_leaderboard($month_number,$month_start_date);
        }

        if(isset($post['filter']) && $post['filter'] == 'month_date')
		{
            //get day number and date
            $from_date = date('Y-m-01',strtotime($post['filter_date'])).' 00:00:00';
            $to_date = date('Y-m-t',strtotime($post['filter_date'])).' 23:59:59';
            $month_number = date("m",strtotime($from_date));
            $month_start_date = $from_date;
            $month_end_date = $to_date;
            $this-> get_month_leaderboard($month_number,$month_start_date);
        }


    }

    function get_day_leaderboard($day_number,$day_date)
    {
        $this->load->model('Fixed_open_predictor_model');

        $result = $this->Fixed_open_predictor_model->get_day_leaderboard($day_number,$day_date);
        $total = $this->Fixed_open_predictor_model->get_day_leaderboard_count($day_number,$day_date);
    
        if(!empty($result))
        {
            $this->load->model('user/User_model');
            $user_ids = array_unique( array_column($result,'user_id'));
            $user_details = $this->User_model->get_users_by_ids($user_ids);
            if(!empty($user_details))
            {
                $user_details = array_column($user_details,'user_name','user_id');
            }

            foreach($result as $key => & $val)
            {
                $val['username'] = '';
                if(isset($user_details[$val['user_id']]))
                {
                    $val['user_name'] =  $user_details[$val['user_id']];
                }

                if(!empty($val['prize_data']))
                {
                    $val['prize_data'] = json_decode($val['prize_data'],TRUE);
                }

            }

            $result = array_values($result);
            
        }

        $response = array();    
        $response['total'] = $total;
        $response['result'] = $result;

        $this->api_response_arry['service_name'] = 'get_fixed_prediction_leaderboard';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $response;
        $this->api_response();


    }

    function get_week_leaderboard($week_number,$week_date)
    {
        $this->load->model('Fixed_open_predictor_model');

        $result = $this->Fixed_open_predictor_model->get_week_leaderboard($week_number,$week_date);
        $total = $this->Fixed_open_predictor_model->get_week_leaderboard_count($week_number,$week_date);
        
        if(!empty($result))
        {
           
            $this->load->model('user/User_model');
            $user_ids = array_unique( array_column($result,'user_id'));
            $user_details = $this->User_model->get_users_by_ids($user_ids);
            if(!empty($user_details))
            {
                $user_details = array_column($user_details,'user_name','user_id');
            }

            foreach($result as $key => & $val)
            {
                $val['username'] = '';
                if(isset($user_details[$val['user_id']]))
                {
                    $val['user_name'] =  $user_details[$val['user_id']];
                }

                if(!empty($val['prize_data']))
                {
                    $val['prize_data'] = json_decode($val['prize_data'],TRUE);
                }
            }

            $result = array_values($result);
            
        }

        $response = array();    
        $response['total'] = $total;
        $response['result'] = $result;

        $this->api_response_arry['service_name'] = 'get_fixed_prediction_leaderboard';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $response;
        $this->api_response();


    }

    function get_month_leaderboard($month_number,$month_date)
    {
        $this->load->model('Fixed_open_predictor_model');
        $result = $this->Fixed_open_predictor_model->get_month_leaderboard($month_number,$month_date);

        $total = $this->Fixed_open_predictor_model->get_month_leaderboard_count($month_number,$month_date);
        
        if(!empty($result))
        {
            $this->load->model('user/User_model');
            $user_ids = array_unique( array_column($result,'user_id'));
            $user_details = $this->User_model->get_users_by_ids($user_ids);
            if(!empty($user_details))
            {
                $user_details = array_column($user_details,'user_name','user_id');
            }

            foreach($result as $key => & $val)
            {
                $val['username'] = '';
                if(isset($user_details[$val['user_id']]))
                {
                    $val['user_name'] =  $user_details[$val['user_id']];
                }

                if(!empty($val['prize_data']))
                {
                    $val['prize_data'] = json_decode($val['prize_data'],TRUE);
                }

            }

            $result = array_values($result);
            
        }
       
        $response = array();    
        $response['total'] = $total;
        $response['result'] = $result;
        $this->api_response_arry['service_name'] = 'get_fixed_prediction_leaderboard';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $response;
        $this->api_response();


    }

}