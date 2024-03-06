<?php

class Common extends Common_Api_Controller {

    function __construct() {
        parent::__construct();
    }

    /**
     * Used for get static page data
     * @param
     * @return json array
     */
    public function get_static_content_post() {
        $this->form_validation->set_rules('page_alias', 'page alias', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $page_alias = $post_data['page_alias'];

        $this->load->model("common/Common_model");
        $static_content = $this->Common_model->get_static_content($page_alias);
        
        if($page_alias=='contact_us'){
                $static_content['custom_data']= json_decode($static_content['custom_data'],true);
            }
        if($page_alias=='faq'){
            $result=array();
            
            $categories = $this->Common_model->get_faq_category();
            foreach($categories as $key=>$category){
                $get_num_row = $this->Common_model->get_question_num_row($category['category_id']);
                $count = (int)$get_num_row->count;

            if(!empty($count)){
            $result['all_category'][$key]['count'] = $get_num_row;
            $result['all_category'][$key]['questions'] = $this->Common_model->get_faq_question_answer($category['category_id']);
            $result['all_category'][$key]['category_name']=$category['category'];
            }
            }
            $static_content = array_merge($static_content,$result);
        }
        //for upload data on s3 bucket
        $this->push_s3_data_in_queue("static_page_".$page_alias."_".$this->lang_abbr, $static_content);

        $this->api_response_arry['data'] = $static_content;
        $this->api_response();
    }

    /**
     * Used for save user feedback
     * @param
     * @return json array
     */
    public function save_feedback_post() {
        $this->form_validation->set_rules('subject', 'subject', 'trim|required');
        $this->form_validation->set_rules('message', 'message', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();

        $feedback_arr = array();
        $feedback_arr['user_id'] = $this->user_id;
        $feedback_arr['name'] = $this->user_name;
        $feedback_arr['subject'] = $post_data['subject'];
        $feedback_arr['message'] = $post_data['message'];
        $feedback_arr['date_created'] = format_date();

        $this->load->model("auth/Auth_nosql_model");
        $this->Auth_nosql_model->insert_nosql(MG_FEEDBACK, $feedback_arr);

        $this->api_response_arry['message'] = $this->lang->line('feedback_saved');
        $this->api_response();
    }

    /**
     * Used for upload media file
     * @param array $post_data
     * @return json array
     */
	public function do_upload_post() 
	{
		$post_data = $this->input->post();
		$type = isset($post_data['type']) ? $post_data['type'] : "";
		$keep_name = isset($post_data['keep_name']) ? $post_data['keep_name'] : "0";
		$media_config = get_media_setting($type);
		if(empty($media_config)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid media config.";
            $this->api_response();
		}

		$field_name	= 'file_name';
		$dir = ROOT_PATH.$media_config['path']."/";
		$s3_dir = $media_config['path']."/";
		$temp_file = $_FILES[$field_name]['tmp_name'];
		$size = number_format(($_FILES[$field_name]['size'] / 1000000),"2",".","");
		$ext = pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);
		$width = $height = "";
		if($ext != "pdf"){
			$vals = @getimagesize($temp_file);
			$width = $vals[0];
			$height = $vals[1];
		}
		//media type validation
		if(!in_array($ext,$media_config['type'])){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{media_type}",implode(",",$media_config['type']),$this->lang->line('image_invalid_ext'));
            $this->api_response();
		}
		//media size
		if($height > $media_config['max_h'] || $width > $media_config['max_w']){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{max_width}x{max_height}",$media_config['max_w']."x".$media_config['max_h'],$this->lang->line('image_invalid_dim'));
            $this->api_response();
		}
		if($height < $media_config['min_h'] || $width < $media_config['min_w']){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{max_width}x{max_height}",$media_config['max_w']."x".$media_config['max_h'],$this->lang->line('image_invalid_dim'));
            $this->api_response();
		}

		if($size > $media_config['size']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{size}",$media_config['size'],$this->lang->line('image_invalid_size_error'));
            $this->api_response();
		}
		
		if($keep_name == 1){
			$org_file_name = pathinfo($_FILES[$field_name]['name'], PATHINFO_FILENAME);
			$file_name = str_replace(" ","_",$org_file_name);
			$file_name = $file_name."_".time().".".$ext;
		}else{
			$file_name = time().".".$ext;
		}
		$filePath = $s3_dir.$file_name;
		try{
            $data_arr = array();
            $data_arr['file_path'] = $filePath;
            $data_arr['source_path'] = $temp_file;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_uploaded = $upload_lib->upload_file($data_arr);
            if($is_uploaded){
	            $this->api_response_arry['data'] = array('image_url'=>  IMAGE_PATH.$filePath,'image_name'=> $file_name);
                $this->api_response_arry['message'] = "Upload Successfully.";
	            $this->api_response();
            }
        }catch(Exception $e){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('image_file_upload_error');
            $this->api_response();
        }		
	}

	/**
     * Used for remove media file from bucket
     * @param array $post_data
     * @return json array
     */
	public function remove_media_post()
	{
		$post_data = $this->input->post();
		$type = isset($post_data['type']) ? $post_data['type'] : "";
		$media_config = get_media_setting($type);
		if(empty($media_config)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid media config.";
            $this->api_response();
		}

		$file_name	= $post_data['file_name'];
		$file_path = $media_config['path']."/".$file_name;
		try{
            $data_arr = array();
            $data_arr['file_path'] = $file_path;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_deleted = $upload_lib->delete_file($data_arr);
            if($is_deleted){
	            $this->api_response_arry['message'] = "Media file removed successfully.";
	            $this->api_response();
            }
        }catch(Exception $e){
        	$error_msg = 'Caught exception: '.  $e->getMessage(). "\n";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $error_msg;
            $this->api_response();
        }
	}
}