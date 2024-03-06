<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Player_management extends MYREST_Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Used for get player list by sports
     * @param array $post_data
     * @return json array response
     */
    public function get_all_player_list_post() {

        $this->form_validation->set_rules('sports_id', 'sports_id','trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("Player_model");
        $players = $this->Player_model->get_player_list($post_data);
        $palyers_list = [];
        $palyers_list['total'] = $players['total'];
        $palyers_list['player_list'] = $players['player_list'];

        $this->api_response_arry['data'] = $palyers_list;
        $this->api_response();
    }

    /**
     * Function used for save player image details
     * @param array $post_data
     * @return json array
     */
    public function save_player_image_post()
    {
        $this->form_validation->set_rules('player_id', 'Player ID','trim|required');
        $this->form_validation->set_rules('image', 'image','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $this->load->model("Player_model");
        $response = $this->Player_model->save_player_image($post_data);
        if($response){
            $this->api_response_arry['message']  = 'Player image uploaded successfully';
            $this->api_response();
        }
        else{
            $this->api_response_arry['message']            = "Error in uploading Image";
            $this->api_response_arry['response_code']      = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }

    /**
     * Function used for upload player image 
     * @param array
     * @return json array
     */
    public function do_upload_post()
    {   $_POST = $this->input->post();
     
        $segment = $this->uri->segment(3);
        $team_id = $this->post('player_id');
        $file_field_name = $this->post('name');
        $dir = APP_ROOT_PATH.UPLOAD_DIR;
        $temp_file_image = $_FILES['file']['tmp_name'];
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

        if(strtolower( IMAGE_SERVER ) == 'remote' ){
            $file_name = $this->do_upload_process($ext);
            $temp_file = $dir.$file_name;
        }
        $vals = @getimagesize($temp_file_image);
        $width = $vals[0];
        $height = $vals[1];
        if($segment == 'jersey'){
            $subdir = ROOT_PATH.JERSEY_CONTEST_DIR;
            $s3_dir = JERSEY_CONTEST_DIR;
            if ($height < '36' || $width < '36') {
                $invalid_size = str_replace("{max_height}",'36',$this->admin_lang['team_jersey_image_invalid_size']);
                $invalid_size = str_replace("{max_width}",'36',$invalid_size);
                $this->api_response_arry["message"] = $invalid_size;
                $this->api_response();
            }
        }

        if( strtolower( IMAGE_SERVER ) == 'local')
        {
            $this->check_folder_exist($dir);
            $this->check_folder_exist($subdir);
        }

        $file_name = 'ply_'.time().".".$ext ;
        $team_image_arr= array("team_id" => $team_id);
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
                    $team_image_arr['image'] = IMAGE_PATH.$filePath;
                    @unlink($temp_file);
                    $data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
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
                $team_image_arr['image'] = IMAGE_PATH.$s3_dir.$file_name;
                $this->api_response_arry["data"] = array('image_name' =>IMAGE_PATH.$s3_dir.$file_name ,'image_url'=> $subdir);
                $this->api_response();
            }
        }       
    }

    /**
    * [do_upload_process description]
    * Summary :- internal function used to upload and resize team's logo and jersey files to local folder.
    */
    public function do_upload_process($ext)
    { 
        $dir = APP_ROOT_PATH.UPLOAD_DIR;
        $config['image_library']    = 'gd2';
        $config['allowed_types']    = 'jpg|png|jpeg|gif|PNG';
        $config['max_size']         = '2000';
        $config['min_width']        = '36';
        $config['min_height']       = '36';
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
}