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

class Anonymous_upload_image extends Common_API_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model(array('upload_file_model')); 
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
        $validation_rule[]      =    array(
            'field' => 'DeviceType',
            'label' => 'DeviceType',
            'rules' => 'trim|required'
        );         
        
        $this->form_validation->set_rules($validation_rule); 
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE) { 
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else {
            $Result = $this->upload_file_model->uploadImage($Data);
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
        $validation_rule[]      =    array(
            'field' => 'DeviceType',
            'label' => 'DeviceType',
            'rules' => 'trim|required'
        );         
        
        $this->form_validation->set_rules($validation_rule); 
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE) { 
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else {
            $ImageUrl = $Data['ImageUrl'];
            $ImageData = @file_get_contents($ImageUrl);

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
}