<?php defined('BASEPATH') OR exit('No direct script access allowed');

class H2hchallenge extends MYREST_Controller {

    public $h2h_group_id = 0;
    public $h2h_data = array();
    public $date_filter_map = array();
    public function __construct()
	{
		parent::__construct();
		$this->load->model('H2hchallenge_model');
		$_POST = $this->input->post();
        $h2h_challenge = isset($this->app_config['h2h_challenge'])?$this->app_config['h2h_challenge']['key_value']:0;
        if($h2h_challenge == 0)
        {
            $this->api_response_arry['response_code'] 	= 500;
            $this->api_response_arry['global_error']  	= 'Module not activated';
            $this->api_response();
        }
        else{
            $h2h_data = $this->app_config['h2h_challenge']['custom_data'];
            $this->h2h_group_id = isset($h2h_data['group_id']) ? $h2h_data['group_id'] : 0;
            $this->h2h_data = $h2h_data;

            $this->date_filter_map['last_week'] = ' -7 days';
            $this->date_filter_map['last_month'] = ' -1 month';
            $this->date_filter_map['last_3_month'] = ' -3 months';
            $this->date_filter_map['last_6_month'] = ' -6 months';
        }
    }

    private function get_user_level($count){
        $level = "Amateur";
        if($count >= $this->h2h_data['mid_min'] && $count <= $this->h2h_data['mid_max']){
            $level = "Mid Level";
        }else if($count >= $this->h2h_data['pro_min'] && $count <= $this->h2h_data['pro_max']){
            $level = "Pro";
        }

        return $level;
    }

    public function get_dashboard_data_post()
    {
        $this->form_validation->set_rules('filter', 'Filter', 'trim|required');
        if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
        $final_data = array();
        $final_data['setting'] = array();
        $final_data['setting']['amateur'] = array('min'=>$this->h2h_data['amateur_min'],'max'=>$this->h2h_data['amateur_max']);
        $final_data['setting']['mid'] = array('min'=>$this->h2h_data['mid_min'],'max'=>$this->h2h_data['mid_max']);
        $final_data['setting']['pro'] = array('min'=>$this->h2h_data['pro_min'],'max'=>$this->h2h_data['pro_max']);

        $current_date = format_date();
        $filter_str = $this->input->post('filter');
        $filter_date = $this->date_filter_map['last_week']; 
        if(isset($this->date_filter_map[$filter_str]))
        {
            $filter_date = date('Y-m-d',strtotime($current_date.' '.$this->date_filter_map[$filter_str]));
        }
        $contest_ids = $this->H2hchallenge_model->get_h2h_contest_ids($filter_date);
        if(!empty($contest_ids))
        {
            $contest_ids = array_column($contest_ids,'contest_id');
        }
        $participation = $this->H2hchallenge_model->get_h2h_user_participation($contest_ids);
        $final_data['paticipation'] = array("total_entry_fee"=>"0","total_winning"=>"0","profit"=>"0","total_users"=>"0","total_contest"=>"0","graph_data"=>array());
        if(!empty($participation)){
            $final_data['paticipation']['total_entry_fee'] = array_sum(array_values(array_column($participation,'total_entry_fee')));
            $final_data['paticipation']['total_winning'] = array_sum(array_values(array_column($participation,'total_winning')));
            $final_data['paticipation']['profit'] = number_format(($final_data['paticipation']['total_entry_fee'] - $final_data['paticipation']['total_winning']),2,'.','');
            
            $total_users = array();
            foreach($participation as &$row)
            {
                $user_id_arr = explode(',',$row['user_ids']);
                $user_id_arr = array_filter($user_id_arr, function($item){ return !empty($item);}); 
                $user_ids = array_unique($user_id_arr);
                $total_users = array_merge($total_users,$user_ids);
                $row['data_value'] = count($user_ids);
            }
            $final_data['paticipation']['total_contest'] = count(array_unique($contest_ids));
            $final_data['paticipation']['graph_data'] = get_lineup_graph_data($filter_date,$current_date,$participation);
            if(isset($final_data['paticipation']['graph_data']['series']['data'])){
                $final_data['paticipation']['total_users'] = array_sum($final_data['paticipation']['graph_data']['series']['data']);
            }
        }
        
        $final_data['tracking'] = $this->H2hchallenge_model->get_h2h_tracking_contest_data($filter_date);
        $final_data['upcoming'] = $this->H2hchallenge_model->get_h2h_upcoming_contest_data();
        $this->api_response_arry['data'] = $final_data;
		$this->api_response();
    }

    public function update_setting_post()
    {
        $this->form_validation->set_rules('amateur_min', 'amateur min', 'trim|required|numeric');
        $this->form_validation->set_rules('amateur_max', 'amateur max', 'trim|required|numeric');
        $this->form_validation->set_rules('mid_min', 'mid min', 'trim|required|numeric');
        $this->form_validation->set_rules('mid_max', 'mid max', 'trim|required|numeric');
        $this->form_validation->set_rules('pro_min', 'pro min', 'trim|required|numeric');
        $this->form_validation->set_rules('pro_max', 'pro max', 'trim|required|numeric');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->post();

        $this->h2h_data['amateur_min'] = $post_data['amateur_min'];
        $this->h2h_data['amateur_max'] = $post_data['amateur_max'];
        $this->h2h_data['mid_min'] = $post_data['mid_min'];
        $this->h2h_data['mid_max'] = $post_data['mid_max'];
        $this->h2h_data['pro_min'] = $post_data['pro_min'];
        $this->h2h_data['pro_max'] = $post_data['pro_max'];
        
        $update_data = array('custom_data' => json_encode($this->h2h_data));
        $this->H2hchallenge_model->update_setting($update_data);

        $this->delete_cache_data('app_config');
        $this->push_s3_data_in_queue('app_master_data',array(),"delete");
        
        $this->api_response_arry['message'] = "Setting Updated." ;
        $this->api_response();
    }

    public function get_h2h_user_list_post()
    {
        $post_data = $this->input->post();
        $post_data['group_id'] = $this->h2h_group_id;
        $result = $this->H2hchallenge_model->get_h2h_user_list($post_data);
        if(!empty($result['result'])){
            $user_ids = array_column($result['result'],"user_id");
            $users = $this->H2hchallenge_model->get_users_by_ids($user_ids);
            $users = array_column($users,NULL,'user_id');
            foreach($result['result'] as &$row){
                $row['user_unique_id'] = $users[$row['user_id']]['user_unique_id'];
                $row['name'] = rtrim($users[$row['user_id']]['first_name']." ".$users[$row['user_id']]['last_name']);
                $row['image'] = $users[$row['user_id']]['image'];
            }
        }
		$this->api_response_arry['data'] = $result;
		$this->api_response();
    }

    public function get_upcoming_game_list_post()
    {
        $post_data = $this->input->post();
        $post_data['group_id'] = $this->h2h_group_id;
        $result = $this->H2hchallenge_model->get_upcoming_game_list($post_data);
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    public function get_h2h_game_users_post()
    {
        $this->form_validation->set_rules('collection_master_id','collection master id', 'trim|required|numeric');
        $this->form_validation->set_rules('contest_template_id','contest template id', 'trim|required|numeric');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $post_data['group_id'] = $this->h2h_group_id;
        $result = $this->H2hchallenge_model->get_h2h_game_users($post_data);
        if(!empty($result['result'])){
            $user_ids = array_column($result['result'],"user_id");
            $users = $this->H2hchallenge_model->get_users_by_ids($user_ids);
            $users = array_column($users,NULL,'user_id');
            foreach($result['result'] as &$row){
                $win_count = isset($user_win[$row['user_id']]) ? $user_win[$row['user_id']] : 0;
                $row['level'] = $this->get_user_level($row['total_win']);
                $row['user_unique_id'] = $users[$row['user_id']]['user_unique_id'];
                $row['name'] = rtrim($users[$row['user_id']]['first_name']." ".$users[$row['user_id']]['last_name']);
                $row['image'] = $users[$row['user_id']]['image'];
                unset($row['total_win']);
            }
        }
        $result['game_info'] = $this->H2hchallenge_model->get_collection_details($post_data['collection_master_id']);
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * For uploading h2h cms image
     * @param
     * @return json array
     */
    public function do_upload_post()
    {   
        $post_data = $this->input->post();
        $this->form_validation->set_rules('source', $this->lang->line("source"),'trim|required');
        if(isset($post_data['source']) && $post_data['source'] == 'edit'){
            $this->form_validation->set_rules('id','id','trim|required');
        }

        if($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        $media_type = isset($post_data['type']) ? $post_data['type'] : "0";
        $post_data['media_type'] = $media_type;
        $dir = APP_ROOT_PATH.UPLOAD_DIR;
        $temp_file_image = $_FILES['file']['tmp_name'];
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        if( strtolower(IMAGE_SERVER) == 'remote' ){
            $file_name = $this->do_upload_process($ext);
            $temp_file = $dir.$file_name;
        }
        
        $vals = @getimagesize($temp_file_image);
        $width = $vals[0];
        $height = $vals[1];
        $subdir = ROOT_PATH.H2H_IMAGE_DIR;
        $s3_dir = H2H_IMAGE_DIR;
        $max_height = 200;
        $max_width = 200;
        if($media_type == "1"){
            $max_height = 400;
            $max_width = 400;
        }
        if ($height > $max_height || $width > $max_width) {
            $invalid_size = str_replace("{max_height}",$max_height,$this->lang->line('h2h_invalid_image_size'));
            $invalid_size = str_replace("{max_width}",$max_width,$invalid_size);
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
        if(strtolower( IMAGE_SERVER ) == 'remote' )
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
                    if($post_data['source'] == 'edit'){
                        $cms_data = $this->H2hchallenge_model->get_cms_by_id($post_data);
                        $post_data['image_name'] = $cms_data['image_name'];
                        $post_data['new_image_name'] = $file_name;
                        $this->remove_cms_image($post_data);
                    }

                    if(!empty($post_data['previous_image']) && $post_data['source'] == 'add'){
                        $post_data['image_name'] = $post_data['previous_image'];
                        $this->remove_cms_image($post_data);
                    }

                    $this->api_response_arry["data"] = $data;
                    $this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
                    $this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
        } else {
            $config['allowed_types']    = 'jpg|png|jpeg|gif|PNG';
            $config['max_size']         = '2000';
            $config['max_width']        = $max_width;
            $config['max_height']       = $max_height;
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
    * Summary :- internal function used to upload merchandise to local folder.
    */
    public function do_upload_process($ext)
    {
        $dir = APP_ROOT_PATH.UPLOAD_DIR;
        $config['image_library']    = 'gd2';
        $config['allowed_types']    = 'jpg|png|jpeg|gif|PNG';
        $config['max_size']         = '2000';
        $config['min_width']        = '36';//64
        $config['min_height']       = '36';//42
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
            $upload_data = $this->upload->data();
            $config1['image_library']   = 'gd2';
            $config1['source_image']    = $dir.$config['file_name'];
            $config1['maintain_ratio']  = TRUE;
            $config1['width']           = 200;
            $config1['height']          = 200;
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

    /**
     * internal funcation to Remove uploaded cms image and remove from table as well 
     * @param
     * @return json array
     */
    public function remove_cms_image($post_data)
    {
        $media_type = $post_data['media_type'];
        $image_name = $post_data['image_name'];
        $dir = ROOT_PATH.H2H_IMAGE_DIR;
        $s3_dir = H2H_IMAGE_DIR;
        $dir_path = $s3_dir.$image_name;
        if( strtolower( IMAGE_SERVER ) == 'remote' )
        {
            try{
                $data_arr = array();
                $data_arr['file_path'] = $dir_path;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_deleted = @$upload_lib->delete_file($data_arr);
                if(!$is_deleted){
                    return false;
                }
            }catch(Exception $e){
                return false;
            }
        }
        // for removing the image name from DB
        if(!empty($post_data['id']))
        {
            if($media_type == "1"){
                $update_data = array("bg_image" => $post_data['new_image_name']);
            }else{
                $update_data = array("image_name" => $post_data['new_image_name']);
            }
            $this->H2hchallenge_model->update_cms_by_id($update_data,$post_data['id']);
        }

        return true;
    }

    /**
    * Function used for get cms list
    * @param void
    * @return string
    */
    public function get_cms_list_post()
    {
        $post_data = $this->post();
        $result = $this->H2hchallenge_model->get_cms_list();
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * For saving h2h cms 
     * @param
     * @return json array
     */
    public function save_cms_post()
    {
        $this->form_validation->set_rules('name', 'name', 'trim|required|min_length[10]|max_length[70]');
        $this->form_validation->set_rules('image_name', "logo",'trim|required');
        $this->form_validation->set_rules('bg_image', "background image",'trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        if(isset($post_data['id']) && $post_data['id'] != ""){
            $result = $this->H2hchallenge_model->update_cms_by_id($post_data,$post_data['id']);
        }else{
            $result = $this->H2hchallenge_model->save_cms($post_data);
        }
        if($result){
            $this->delete_cache_data('h2h_cms');

            $this->api_response_arry["message"] = $this->lang->line('save_cms_success');
            $this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
            $this->api_response();
        }else{
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["error"] = $this->lang->line('save_cms_error');
            $this->api_response();
        }
    }

    /**
     * For delete h2h cms 
     * @param
     * @return json array
     */
    public function delete_cms_post()
    {
        $this->form_validation->set_rules('id', 'id', 'trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $cms_data = $this->H2hchallenge_model->get_cms_by_id($post_data);
        $result = $this->H2hchallenge_model->delete_cms_record($post_data['id']);
        if($result){
            if(!empty($cms_data)){
                $del_data = array('image_name'=>$cms_data['image_name'],"media_type"=>"0");
                $this->remove_cms_image($del_data);

                $del_data = array('image_name'=>$cms_data['bg_image'],"media_type"=>"1");
                $this->remove_cms_image($del_data);
            }

            $this->delete_cache_data('h2h_cms');

            $this->api_response_arry["message"] = $this->lang->line('delete_cms_success');
            $this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
            $this->api_response();
        }else{
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["error"] = $this->lang->line('save_cms_error');
            $this->api_response();
        }
    }
}