<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include_once APPPATH . 'controllers/api/Upload_image.php';

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


// error_reporting(0);
class Adminupload_image extends Upload_image {

    function __construct() {
        parent::__construct(true);
        
        $this->load->model(array(    
            'admin/activity/activity_helper_model'
        ));
        $this->activity_helper_model->setUserSessionData();
    }

    function set_profile_pic_by_admin_post()
    {
        $Return = $this->return;

        $Data   = $this->post_data;
        $UserID = $this->UserID;

        $media_guid = isset($Data['MediaGUID']) ? $Data['MediaGUID'] : '' ;
        $user_id = isset($Data['UserID']) ? $Data['UserID'] : '' ;

        $this->upload_file_model->set_profile_pic_by_admin($media_guid,$user_id);

        $this->response($Return);
    }

    function updateProfilePicture_post(){
        $Return = $this->return;

        $Data   = $this->post_data;
        $UserID = $this->UserID;
        if($this->form_validation->run('admin_api/uploadimage/updateProfilePicture') == FALSE){
            $error = $this->form_validation->rest_first_error_string();         
            $Return['ResponseCode'] = 511;
            $Return['Message'] = $error;
        } else {
            $CropSize           = '220x220';
            $Type               = 'profile';
            if(isset($Data['CropSize']) && !empty($Data['CropSize'])) {
                $CropSize       = $Data['CropSize'];                
            }
            if(isset($Data['Type']) && !empty($Data['Type'])) {
                $Type         = strtolower($Data['Type']);                
            }
            $Data['CropSize']   = $CropSize;
            $Data['Type']       = $Type;

            $Data['IsMediaExisted']     = isset($Data['IsMediaExisted']) ? $Data['IsMediaExisted'] : 0 ;
            $MediaGUID          = $Data['MediaGUID'];
            $ModuleID           = 3;
            $ModuleEntityGUID   = 0; 
            $FilePath           = $Data['FilePath'] = PATH_IMG_UPLOAD_FOLDER.$Data['Type'].'/';
            $fileAllowedArray   = array('png','jpg','jpeg','PNG','JPG','JPEG','GIF','gif');
            $ImageData          = $Data['ImageData'];            
            $Data['IsFrontEnd']        = (isset($Data['IsFrontEnd']) && $Data['IsFrontEnd']==0)? 0 : 1;
            foreach($fileAllowedArray as $farr){
               $ImageData = str_replace('data:image/'.$farr.';base64,', '', $ImageData);
            }

            $Data['ImageName'] = explode('?', $Data['ImageName']);
            $Data['ImageName'] = $Data['ImageName'][0];

            $Data['ImageData'] = $ImageData;
            $this->upload_file_model->uploadProfilePicture($Data,$UserID,1);
        }
        $this->response($Return);
    }
}