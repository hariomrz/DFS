<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Upload extends CI_Controller {

// $page_name variable help to identify page in the header section and according to page javascript file will load
    public $AccessKey = '';
    protected $wall_folder                  = array("wall", "wall/220x220", "wall/750x500");
    protected $wall_thumb_size              = array(array("220", "220"), array("750", "500"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $wall_zoom_crop_array         = array(1, 1, 1);
    protected $allowed_image_types          = 'gif|jpg|png|JPG|GIF|PNG|jpeg|JPEG|bmp|BMP';
    protected $allowed_image_max_size       = '16096'; //KB
    protected $allowed_image_max_width      = '1024';
    protected $allowed_image_max_height     = '768';
    public function __construct() {
        parent::__construct();
        
    }

    public function index() {
        $Data = $_POST;
        $Return['Message']      = lang('success');
        $Return['ResponseCode'] = 200;
        $Return['Data']         =array();
        $Data['DeviceType'] = isset($Data['DeviceType'])?$Data['DeviceType']:'native';
        $type       = strtolower($Data['Type']);
        $DeviceType = strtolower($Data['DeviceType']);

        
        $folder_arr = $type . '_folder';
        $zc_arr     = $type . '_zoom_crop_array';
 
        $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;
        $chk_folder = PATH_IMG_UPLOAD_FOLDER;

        //$this->check_folder_exist($chk_folder, $this->$folder_arr);

        $config['upload_path']      = $dir_name . "/";
        $config['allowed_types']    = $this->allowed_image_types;
        $config['max_size']         = $this->allowed_image_max_size;
        
        $config['encrypt_name']     = TRUE;
        $this->load->library('upload', $config);
        if ( ! $this->upload->do_upload('qqfile')){
            $Return['ResponseCode'] = 412;
            $Errors = $this->upload->error_msg;
            if(!empty($Errors)){
                $Return['Message'] =  $Errors['0']; // first message
            }else{
                $Return['Message'] =  "Unable to fetch error code."; // first message
            }
            print_r($Return);
            //Shows all error messages as a string              
        } else {
            $UploadData = $this->upload->data();
            print_r($UploadData);
        }
    }
}
