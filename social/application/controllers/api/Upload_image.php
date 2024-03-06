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


// error_reporting(0);
class Upload_image extends Common_API_Controller {
    public function __construct($bypass = false) {
        parent::__construct($bypass);
        $this->load->model(array('upload_file_model', 'users/user_model')); 
    }
    /**
     * [index_post used to upload image]
     * @return [Object] [json object]
     */
    public function index_post() {
        $this->benchmark->mark('upload_image_start');
        $Return = $this->return;
        $Data = $this->post_data;
        /* Validation - starts */
        $validation_rule[]      =    array(
            'field' => 'Type',
            'label' => 'Type',
            'rules' => 'trim|required'
        );
        if(empty($this->DeviceTypeID)) {
            $validation_rule[]      =    array(
                'field' => 'DeviceType',
                'label' => 'DeviceType',
                'rules' => 'trim|required'
            );
        }

        $this->form_validation->set_rules($validation_rule);
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE && 0) { 
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else {
            $update_user_id = isset($data['UserID']) ? $data['UserID'] : '';
            if(!empty($update_user_id) && $update_user_id != $this->UserID) {
                $is_super_admin = $this->user_model->is_super_admin($this->UserID, 1);
                if(!$is_super_admin){
                    $this->return['ResponseCode']= self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message']= "You are not authorized to perform this action";
                    $this->response($return);
                }
                $this->UserID = $update_user_id;
            }
            
            $generateThumb = 0;
            $Data['Type'] = strtolower($Data['Type']);
            if(isset($Data['IsDocument']))
            {
                $Result = $this->upload_file_model->upload_documents($Data);                
            }
            else 
            {
                if($Data['Type'] == 'temp'){
                    $Result = $this->upload_file_model->uploadTempFile($Data,$Return);
                } /* else if($Data['Type'] == 'profile') {                    
                    if($this->IsApp == 1){
                        $generateThumb = 1;
                    }                    
                    $Result = $this->upload_file_model->uploadImage($Data,$generateThumb);           
                } */
                else {                   
                    if ((function_exists("gearman_version") && JOBSERVER == "Gearman") || (JOBSERVER == "Rabbitmq")) {
                        $Data['keep_original'] = 1;
                        $Result = $this->upload_file_model->upload_image_without_thumb($Data);
                        if(!empty($Result) && $Result['ResponseCode']==200)
                        {   
                            $media_data = array();
                            $media_data['MediaGUID'] = $Result['Data']['MediaGUID'];
                            $media_data['ImageName'] = $Result['Data']['ImageName'];
                            $media_data['TotalSize'] = $Result['Data']['TotalSize'];
                            $media_data['Type'] = $Data['Type'];         
                            $media_data['full_path'] = $Result['Data']['full_path'];     
                            $media_data['keep_original'] = $Data['keep_original'];
                            $media_data['document_root'] = DOCUMENT_ROOT;
                            $media_data['is_app'] = $this->IsApp;
                            //will generate thumb from job queue server
                            initiate_worker_job('create_thumb', $media_data, '', 'process_image');
                            //$this->upload_file_model->uploadImageInBg($media_data);
                            unset($Result['Data']['full_path']);
                        }
                    } else{
                        $generateThumb = 1;
                        $Result = $this->upload_file_model->uploadImage($Data,$generateThumb);
                    }
                }
            }
            
            /*edited by gautam - starts*/
            if($Result['ResponseCode']==200 && $this->IsApp == 1){
                if($Data['Type']=='profile' || $Data['Type']=='group'){
                    $this->upload_file_model->updateProfilePicture($Result['Data']['MediaGUID'],$Result['Data']['ImageName'],$this->UserID,$Data['ModuleID'],$Data['ModuleEntityGUID']);

                }elseif($Data['Type']=='profilebanner'){
                    $this->upload_file_model->updateProfileCover(array("MediaGUID" => $Result['Data']['MediaGUID'], "ModuleID" => $Data['ModuleID'], "ModuleEntityGUID" => $Data['ModuleEntityGUID'], 'ImageName' => $Result['Data']['ImageName']),$this->UserID);
                }
            }
            /*edited by gautam - ends*/

            $Return['ResponseCode']  = $Result['ResponseCode'];
            $Return['Message']       = $Result['Message'];
            $Return['Data']          = $Result['Data'];             
        }
        $image_name = isset($Result['Data']['ImageName']) ? $Result['Data']['ImageName'] : '';
        $this->benchmark->mark('upload_image_end');
        log_message('error', $image_name."   upload_image => ".$this->benchmark->elapsed_time('upload_image_start', 'upload_image_end'));
        $this->response($Return);    
    }
    /**
     * [uploadFile_post used to upload file]
     * @return [Object] [json object]
     */
    public function uploadFile_post() {

        $Return = $this->return;

        $Data = $this->post_data;
        /* Validation - starts */
       
        $validation_rule[]      =    array(
            'field' => 'Type',
            'label' => 'Type',
            'rules' => 'trim|required'
        );
        if(empty($this->DeviceTypeID)) {
            $validation_rule[]      =    array(
                'field' => 'DeviceType',
                'label' => 'DeviceType',
                'rules' => 'trim|required'
            );
        }
        
        $this->form_validation->set_rules($validation_rule); 
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE) { 
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else {
            $generateThumb = 0;
            if($Data['Type'] == 'temp'){
                $Result = $this->upload_file_model->uploadTempFile($Data,$Return);
            } else if($Data['Type'] == 'profile') {
                $Result = $this->upload_file_model->uploadImage($Data,$generateThumb);                
            } else {
                $generateThumb = 1;
                $Result = $this->upload_file_model->uploadImage($Data,$generateThumb);
            }
            $Return['ResponseCode']  = $Result['ResponseCode'];
            $Return['Message']       = $Result['Message'];
            $Return['Data']          = $Result['Data'];             
        }       
        $this->response($Return);
    }

    /**
     * Function Name: used to upload file from URL
     * @return [Object] [json object]
     */
    public function updateProfileBanner_post() {

        $Return = $this->return;

        $Data = $this->post_data;
        /* Validation - starts */
        $validation_rule[]      =    array(
            'field' => 'ImageData',
            'label' => 'Image Data',
            'rules' => 'trim|required'
        );
        $validation_rule[]      =    array(
            'field' => 'ImageHeight',
            'label' => 'Image Height',
            'rules' => 'trim|required'
        );
        $validation_rule[]      =    array(
            'field' => 'ImageWidth',
            'label' => 'Image Width',
            'rules' => 'trim|required'
        );
        $validation_rule[]      =    array(
            'field' => 'ModuleID',
            'label' => 'Module ID',
            'rules' => 'trim|required'
        );
        $validation_rule[]      =    array(
            'field' => 'ModuleEntityGUID',
            'label' => 'Module Entity GUID',
            'rules' => 'trim|required'
        );
        if(empty($this->DeviceTypeID)) {
            $validation_rule[]      =    array(
                'field' => 'DeviceType',
                'label' => 'DeviceType',
                'rules' => 'trim|required'
            );
        }         
        
        $this->form_validation->set_rules($validation_rule); 
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE) { 
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else {    
            $Type       = strtolower($Data['Type']);  
            if(empty($Type)) {
                $Type = "profilebanner";
            }  
            $Data['Type'] = $Type;    
            $fileAllowedArray = array('png','jpg','jpeg','PNG','JPG','JPEG','GIF','gif');
            $ImageData = $Data['ImageData'];
            if (filter_var($ImageData, FILTER_VALIDATE_URL) === FALSE) {
                $ImageData  = $Data['ImageData'];
                $Data['ImageUrl']   = 0;
            } else {
                $ImageData = file_get_contents($ImageData);
                $Data['ImageUrl']   = $Data['ImageData'];
            }

            foreach($fileAllowedArray as $farr){
               $ImageData = str_replace('data:image/'.$farr.';base64,', '', $ImageData);
            }
            if($ImageData===FALSE) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;                
                $Return['Message'] = "Image Data invalid.";
            } else {
                $Data['ImageData']  = $ImageData; 
                //$Data['ImageUrl']   = 0;
                $Result = $this->upload_file_model->saveFileFromUrl($Data);
                $Return['Message']       = $Result['Message'];
                if($Result['ResponseCode']==200){
                    $UserID = $this->UserID;
                    $updateData = array("MediaGUID" => $Result['Data']['MediaGUID'], "ModuleID" => $Data['ModuleID'], "ModuleEntityGUID" => $Data['ModuleEntityGUID'], 'ImageName' => $Result['Data']['ImageName']);
                        $Return['Data']['ProfileCover'] = $this->upload_file_model->updateProfileCover($updateData,$UserID);
                } else {
                   $Return['ResponseCode']  = $Result['ResponseCode'];
                   $Return['Data']          = $Result['Data'];  
                }              
            }
        }       
        $this->response($Return);
    }

    /**
     * Function Name: used to upload file from URL
     * @return [Object] [json object]
     */
    public function saveFileFromUrl_post() {

        $Return = $this->return;

        $Data = $this->post_data;

        /* Validation - starts */
        $validation_rule[]      =    array(
            'field' => 'ImageUrl',
            'label' => 'ImageUrl',
            'rules' => 'trim|required'
        );
        $validation_rule[]      =    array(
            'field' => 'Type',
            'label' => 'Type',
            'rules' => 'trim|required'
        );
        if(empty($this->DeviceTypeID)) {
            $validation_rule[]      =    array(
                'field' => 'DeviceType',
                'label' => 'DeviceType',
                'rules' => 'trim|required'
            );
        }         
        
        $this->form_validation->set_rules($validation_rule); 
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE) { 
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else {
            $ImageUrl = $Data['ImageUrl'];
            if(!$ImageUrl){
                $fileAllowedArray = array('png','jpg','jpeg','PNG','JPG','JPEG','GIF','gif');
                $ImageData = $Data['ImageData'];
                foreach($fileAllowedArray as $farr){
                   $ImageData = str_replace('data:image/'.$farr.';base64,', '', $ImageData);
                }
            } else {
                $ImageData = @file_get_contents($ImageUrl);
            }
            if($ImageData===FALSE) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;                
                $Return['Message'] = "ImageUrl not valid or require authentication.";
            } else {
                $Data['ImageData'] = $ImageData; 
                $Result = $this->upload_file_model->saveFileFromUrl($Data);
                $Return['ResponseCode']  = $Result['ResponseCode'];
                $Return['Message']       = $Result['Message'];
                $Return['Data']          = $Result['Data'];                
            }
        }       
        $this->response($Return);
    }

    function apply_default_theme_post()
    {
        $return = $this->return;

        $data = $this->post_data;
        /* Validation - starts */
        
        $validation_rule[]      =    array(
            'field' => 'CoverTheme',
            'label' => 'cover theme',
            'rules' => 'trim|required'
        );
        $validation_rule[]      =    array(
            'field' => 'Type',
            'label' => 'Type',
            'rules' => 'trim|required'
        );
        if(empty($this->DeviceTypeID)) {
            $validation_rule[]      =    array(
                'field' => 'DeviceType',
                'label' => 'DeviceType',
                'rules' => 'trim|required'
            );
        }         
        
        $this->form_validation->set_rules($validation_rule); 
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE) 
        { 
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } 
        else 
        {
            $cover_theme   = $data['CoverTheme'];          
            
            $data['CoverTheme']      = $cover_theme;
            $result = $this->upload_file_model->apply_default_theme($data);
            $return['ResponseCode']  = $result['ResponseCode'];
            $return['Message']       = $result['Message'];
            $return['Data']          = $result['Data'];
        }
        $this->response($return);    
    }

    /**
    * Function Name : updateProfileCover
    * Description : Update user profile cover and update in media table
    */
    function updateProfileCover_post(){
        $Return = $this->return;

        $Data   = $this->post_data;
        $UserID = $this->UserID;
        $Return['Data']['ProfileCover'] = $this->upload_file_model->updateProfileCover($Data,$UserID);
        $this->response($Return);
    }

    /**
    * Function Name : updateProfilePicture
    * Description : Update user / entity profile picture and update in media table
    */
    /*function updateProfilePicture_post(){
        $Return = $this->return;

        $Data   = $this->post_data;
        $UserID = $this->UserID;
        if($this->form_validation->run('api/uploadimage/updateuserprofile') == FALSE){
            $error = $this->form_validation->rest_first_error_string();         
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $ProfilePicture     = $Data['ProfilePicture'];
            $MediaGUID          = $Data['MediaGUID'];
            $ModuleID           = $Data['ModuleID'];
            $ModuleEntityGUID   = $Data['ModuleEntityGUID'];
            $Top                = $Data['Top'];
            $Left               = $Data['Left'];
            $SkipCropping       = isset($Data['SkipCropping']) ? $Data['SkipCropping'] : 0 ;  
            $FileDetails        = getimagesize(IMAGE_SERVER_PATH.PATH_IMG_UPLOAD_FOLDER.'/profile/'.$ProfilePicture);
            $FilePath = PATH_IMG_UPLOAD_FOLDER.$Data['Type'].'/';
            $Left = $Left*-1;
            $Top = $Top*-1;
            $this->upload_file_model->cropProfilePicture($Data);
            //$this->upload_file_model->cropFile($FilePath,$ProfilePicture,192,192,$FileDetails[0],$Top,'150x150/','upload/profile',$Left,2,320,320,$FileDetails[1]);
            //$this->upload_file_model->cropFile($FilePath,$ProfilePicture,36,36,$FileDetails[0],$Top,'36x36/','upload/profile',$Left,2,320,320,$FileDetails[1]);
            $Return['Data']['ProfilePicture'] = $this->upload_file_model->updateProfilePicture($MediaGUID,$ProfilePicture,$UserID,$ModuleID,$ModuleEntityGUID);
            $this->response($Return);
        }
    }*/
    function updatePictureWithoutId_post(){
        $Return = $this->return;

        $Data   = $this->post_data;
        $UserID = $this->UserID;

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
        $ModuleID           = $Data['ModuleID'];
        $ModuleEntityGUID   = $Data['ModuleEntityGUID']; 
        $FilePath           = $Data['FilePath'] = PATH_IMG_UPLOAD_FOLDER.$Data['Type'].'/';
        $fileAllowedArray   = array('png','jpg','jpeg','PNG','JPG','JPEG','GIF','gif');
        $ImageData          = $Data['ImageData'];            
        
        foreach($fileAllowedArray as $farr){
           $ImageData = str_replace('data:image/'.$farr.';base64,', '', $ImageData);
        }

        $Data['ImageName'] = explode('?', $Data['ImageName']);
        $Data['ImageName'] = $Data['ImageName'][0];

        $Data['ImageData'] = $ImageData;
        $this->upload_file_model->uploadProfilePicture($Data,$UserID);

        $this->response($Return);
    }

    function updateProfilePicture_post(){
        $Return = $this->return;

        $Data   = $this->post_data;
        $UserID = $this->UserID;
        if($this->form_validation->run('api/uploadimage/updateProfilePicture') == FALSE){
            $error = $this->form_validation->rest_first_error_string();         
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
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
            $ModuleID           = $Data['ModuleID'];
            $ModuleEntityGUID   = $Data['ModuleEntityGUID']; 
            $FilePath           = $Data['FilePath'] = PATH_IMG_UPLOAD_FOLDER.$Data['Type'].'/';
            $fileAllowedArray   = array('png','jpg','jpeg','PNG','JPG','JPEG','GIF','gif');
            $ImageData          = $Data['ImageData'];            
            
            foreach($fileAllowedArray as $farr){
               $ImageData = str_replace('data:image/'.$farr.';base64,', '', $ImageData);
            }

            $Data['ImageName'] = explode('?', $Data['ImageName']);
            $Data['ImageName'] = $Data['ImageName'][0];

            $Data['ImageData'] = $ImageData;
            $this->upload_file_model->uploadProfilePicture($Data,$UserID);
        }
        $this->response($Return);
    }

    /**
     * Function Name : removeProfileCover
     * @param  LoginSessionKey
     * @return Data[ProfileCoder]
     * Description : remove profile cover of current user
     */
    function removeProfileCover_post(){
        /* Define variables - starts */
        $Return = $this->return;
        /* Define variables - ends */

        $Data = $this->post_data;
        $UserID = $this->UserID;
        if($this->form_validation->run('api/uploadimage/removeProfileCover') == FALSE){
            $error = $this->form_validation->rest_first_error_string();         
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $ModuleID = $Data['ModuleID'];
            $ModuleEntityGUID = $Data['ModuleEntityGUID'];
            $Return['Data']['ProfileCover'] = $this->upload_file_model->removeProfileCover($UserID,$ModuleID,$ModuleEntityGUID);
        }
        $this->response($Return);
    }
}
