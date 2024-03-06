<?php

/**
 * Controller for the upload files create thumbs and save on local or remote server
 * @package upload files
 * @author
 * @version 1.0
 *
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Upload_image extends CI_Controller {

    public $user_id;
    public $unique_id;
    public $pathToFolder;
    /* both variable folder and thmb size need to decide */
    protected $profile_folder = array("profile", "profile/36x36", "profile/220x220");
    protected $profilebanner_folder = array("profilebanner", "profilebanner/1200x300");
    protected $profilebanner_thumb_size = array();
    protected $profilebanner_zoom_crop_array = array(1,1);
    protected $profile_thumb_size = array(array("36", "36"), array("220", "220"));
    protected $profile_zoom_crop_array = array(1, 1);
    protected $group_folder = array("group", "group/36x36", "group/220x220", "group/1000x1000");
    protected $group_thumb_size = array(array("36", "36"), array("220", "220"), array("1000", "1000"));
    protected $group_zoom_crop_array = array(1, 1, 1);
    protected $course_folder = array("course", "course/150x150", "course/1000x1000");
    protected $course_thumb_size = array(array("192", "192"), array("1000", "1000"));
    protected $course_zoom_crop_array = array(1, 1);
    protected $wall_folder = array("wall", "wall/220x220", "wall/1000x1000");
    protected $wall_thumb_size = array(array("192", "192"), array("1000", "1000"));
    protected $wall_zoom_crop_array = array(1, 1);
    protected $temp_folder = array("temp", "temp/192x191", "temp/36x36");
    protected $temp_thumb_size = array(array("192", "191"), array("36", "36"));
    protected $temp_zoom_crop_array = array(1, 1);

    /* both variable folder and thmb size need to decide */
    protected $name_skip_chars = array(
        "~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "-", "+", "=", "{", "}",
        "[", "]", "|", "\\", ":", ";", "\"", "'", "<", ",", ">", ".", "?", "/", " "
    );

    public function __construct() {
        parent::__construct();

        $this->load->model('upload_file_model');

        if ($this->input->post('unique_id') != '') {
// $this->user_id = $admin->user_id;
            $this->unique_id = $this->input->post('unique_id');
        } elseif ($this->session->userdata('ht_admin') != '') {
            $admin = $this->session->userdata('ht_admin');
            $this->user_id = $admin->user_id;
            $this->unique_id = $admin->unique_id;
        } else {
            $this->user_id = $this->session->userdata('UserID');
            $this->unique_id = $this->session->userdata('UserGUID');
        }

        if ($this->user_id == '') {
//redirect('');
        }

        if (strtolower(IMAGE_SERVER) == 'remote') { //if upload on s3 is enabled
            $this->load->library('S3');
        }
//$this->load->model('users/signup_model');
// $this->member_model->remember_me();
        $urlArr = explode($_SERVER['HTTP_HOST'] . '/', base_url());
        if (isset($urlArr[1]) && !empty($urlArr[1])) {
            $this->pathToFolder = $urlArr[1];
        } else {
            $this->pathToFolder = '/';
        }
    }

    public function uploadWallFile(){
      $fileName = json_decode(file_get_contents('php://input'));
      $fileName = $fileName->fileName;
        
        if($fileName){
          foreach($fileName as $fName){
            $fArr = array('upload/wall/'.$fName,'upload/wall/1000x1000/'.$fName,'upload/wall/220x220/'.$fName);
            foreach($fArr as $farr){
              $s3 = new S3(array("access_key"=>AWS_ACCESS_KEY,"secret_key"=>AWS_SECRET_KEY,"region"=>BUCKET_ZONE,"use_ssl"=>false,"verify_peer"=>true));
              $is_s3_upload = $s3->putObjectFile($farr, BUCKET, $farr, S3::ACL_PUBLIC_READ);
            }
          }
        }
    }

    public function uploadFile() {

// Get Inputs and Define Variables
        $type = $this->input->post('type');
        $signup = $this->input->post('signup');
        $user_unique_id = $this->input->post('user_unique_id');
        $current_upload_image_id = $this->input->post('current_upload_image_id');
        $user_id = $this->user_id;
        $user_unique_id = $this->unique_id;
        $folder_arr = $type . '_folder';
        $thumb_arr = $type . '_thumb_size';
        $zc_arr = $type . '_zoom_crop_array';
        $is_file_uploaded = false;

        $dir_name = PATH_IMG_UPLOAD_FOLDER . $type;
        $chk_folder = PATH_IMG_UPLOAD_FOLDER;
        $this->check_folder_exist($chk_folder, $this->$folder_arr);


        $file_name = $_FILES['qqfile']['name'];
        $temp_file = $_FILES['qqfile']['tmp_name'];
        $name_parts = pathinfo($file_name);
        $ext = $name_parts['extension'];
        $file_name = $name_parts['filename'];
        $file_name = str_replace($this->name_skip_chars, "_", $file_name);
        $file_name = $file_name . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 4) . time() . '.' . $ext;
        $image_path = $dir_name . "/" . $type . "/" . $file_name;
        $retunr_big_thumb = "";

        $dname = $dir_name.'/';
        $is_file_uploaded = $this->upload_file_model->upload_image($dname,$temp_file,$file_name,$type);
        if($is_file_uploaded){
            if (strtolower(IMAGE_SERVER) == 'remote') {
                if($type=="wall"){
                  $path = base_url();
                } else {
                  $path = IMAGE_SERVER_PATH;
                }
            } else {
                $path = site_url();
            }
        }

        if ($is_file_uploaded) {
            $this->upload_file_model->generate_thumb($temp_file, $dir_name, $file_name, $this->$thumb_arr, $this->$zc_arr,1,$this->pathToFolder,$type);
            $orignal_file_path = $path . $dir_name . "/" . $file_name;
            $thumbType = 'THUMB_' . $type;
            $retunr_thumb = constant($thumbType);
            $data = array('success' => true,
                'result' => 'success',
                'file_path' => $orignal_file_path,
                'file_name' => $file_name,
                'thumb' => $path . $dir_name . $retunr_thumb . $file_name,
                'big_thumb' => $path . $dir_name . $retunr_big_thumb . $file_name,
                'thumb_id' => '',
                'size' => $_FILES['qqfile']['size'],
                'current_upload_image_id' => $current_upload_image_id
            );
        } else {
            
            $data = array('error' => 'upload failed'
                , 'result' => 'error',
                'file_path' => $image_path,
                'file_name' => $file_name,
                'reason' => 'The uploaded file exceeds the maximum allowed size in your PHP configuration file.' //$this->upload->display_errors('', '')
            );
        }
        echo json_encode($data);
    }

    /**
     * @Summary: upload file on s3
     * @create_date: 3 apr, 2013
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    public function upload_s3($temp_file, $dir_name, $file_name, $thumb = array()) {
        
    }

    /**
     * @Summary: upload file on local
     * @create_date: 3 apr, 2013
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    public function upload_local($temp_file, $dir_name, $file_name, $thumb = array()) {
        
    }

    /**
     * @Summary: generate thubnail according to provided array of thumb
     * @create_date: 3 apr, 2013
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    

    /**
     * @Summary: Function for the upload : return true/false
     * @create_date: 3 apr, 2013
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    public function do_upload($file = '') {

        if ($this->upload->do_upload($file)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @Summary: check if folder exists otherwise create new
     * @create_date: 3 apr, 2013
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    public function check_folder_exist($dir_name, $folder = array()) {
        $d = $_SERVER['DOCUMENT_ROOT'] . '/' . $this->pathToFolder . PATH_IMG_UPLOAD_FOLDER;
        if (!is_dir($d))
            mkdir($d, 0777);
        $d1 = $_SERVER['DOCUMENT_ROOT'] . '/' . $this->pathToFolder . $dir_name;
        if (!is_dir($dir_name))
            mkdir($dir_name, 0777);

        foreach ($folder as $row) {
            if (!is_dir($dir_name . $row))
                mkdir($dir_name . $row, 0777);
        }
    }

    /**
     * @Summary: check if folder exists otherwise create new
     * @create_date: 3 apr, 2013
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    public function test_thumb($temp_file, $dir_name, $file_name, $thumb = array(), $zc = array()) {
        $this->load->library('phpthumb');
        $phpThumb = new phpThumb();
        $temp_file = 'upload/515156e721664/contact/9_55341365162444.jpg';
        $temp_file = file_get_contents($temp_file);
//root path is needed
        $size = ROOT_PATH . 'upload/515156e721664/contact/300X300/9_55341365162444.jpg';
//$this->load->library('image_lib', $config);
//$phpThumb->resetObject();
        $phpThumb->setSourceData($temp_file);
        $phpThumb->setParameter('w', '100');
        /* if(isset($w))
          $phpThumb->setParameter('w', $w);

          if(isset($h))
          $phpThumb->setParameter('h', $h);

          if($zc[$i] == 1)
          $phpThumb->setParameter('zc', true); */

        if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
            echo 'success thumb';
            if ($phpThumb->RenderToFile($size)) {
                echo 'success render';
            } else {
                echo 'fails render';
                print_r($phpThumb->DebugMessage());
            }
        } else {
            print_r($phpThumb->DebugMessage());
        }
    }

    /**
     * @Summary: check if folder exists otherwise create new
     * @create_date: 3 apr, 2013
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    function import_csv() {

        $file_category = $this->input->post('xls_cat_type');



        if ((!empty($_FILES["qqfile"])) && ($_FILES['qqfile']['error'] == 0)) {


            $filename = basename($_FILES['qqfile']['name']);

            $ext = substr($filename, strpos($filename, '.') + 1);

            if ($ext == 'csv') {

                $filepath = $_FILES['qqfile']['tmp_name'];

                $this->$file_category($filepath);
            } else {
                $data['error'] = "Please upload file in CSV format";
            }
        }

        echo json_encode($data);
    }


    //Crop Image

    public function create_cropped_image(){
        $post              = new StdClass;
        $post->x1          = $this->input->post('img_offset_x');
        $post->y1          = $this->input->post('img_offset_y');
        $post->x2          = $this->input->post('x2');
        $post->y2          = $this->input->post('y2');
        $post->img_w       = $this->input->post('width');
        $post->img_h       = $this->input->post('height');
        $post->upload_type = $this->input->post('upload_type');
        $post->unique_id   = $this->input->post('unique_id');
        $path_parts        = pathinfo($this->input->post('file_path'));
        
        $post->file_name   = $this->input->post('src');

        $original_path        = $this->upload_file_model->get_path_img_original_folder($post->file_name,$post->upload_type);
        
        if($post->upload_type == 'profile_image' ||   $post->upload_type == 'course' ){
            $new_height = $new_width;
        }       

 
        $this->load->library('image_lib');
        
        $time = time();        

        $fileAllowedArray = array('png','jpg','jpeg','PNG','JPG','JPEG','GIF','gif');
         $imageData = $this->input->post('image_data');
         foreach($fileAllowedArray as $farr){
            $imageData = str_replace('data:image/'.$farr.';base64,', '', $imageData);
         }
        file_put_contents(IMAGE_ROOT_PATH.'/'.$post->upload_type.'/'.$time.$post->file_name,base64_decode($imageData));

        $imgDetails = getimagesize(IMAGE_ROOT_PATH.'/'.$post->upload_type.'/'.$time.$post->file_name);

        $imgWidth = $imgDetails[0];

        $config['new_image']      = IMAGE_ROOT_PATH.'/'.$post->upload_type.'/';
        $config['image_library']  = 'gd2';
        $config['source_image']   = IMAGE_ROOT_PATH.'/'.$post->upload_type.'/'.$time.$post->file_name;
        $config['create_thumb']   = TRUE;
        $config['thumb_marker']   = '';
        $config['maintain_ratio'] = TRUE;
        $config['width']          = 2500;
        $config['height']         = $post->img_h;
        $config['overwrite'] = TRUE;

        $this->image_lib->initialize($config); 
        $this->image_lib->resize();

        $config = array();

        $yRatio = ceil(($imgWidth/2500)*$post->y1);

        $config['new_image']      = $this->upload_file_model->get_path_img_thumb_folder('profilebanner','1200x300');
        $config['image_library']  = 'gd2';
        $config['source_image']   = IMAGE_ROOT_PATH.'/'.$post->upload_type.'/'.$time.$post->file_name;
        $config['create_thumb']   = TRUE;
        $config['thumb_marker']   = '';
        $config['maintain_ratio'] = FALSE;
        $config['height']         = 372;
        $config['x_axis']         = $post->x1;
        $config['y_axis']         = $post->y1;

        $this->image_lib->initialize($config); 
        $this->image_lib->crop();
        $this->image_lib->clear();

        $thumb_data = $this->upload_file_model->create_thumbnails(IMAGE_ROOT_PATH.'/'.$post->upload_type.'/'.$time.$post->file_name, $post->upload_type, 1);

        $this->upload_file_model->save_cropped_image($post->upload_type, $post->unique_id, $time.$post->file_name,$thumb_data);

        echo json_encode($thumb_data);
    }
}
