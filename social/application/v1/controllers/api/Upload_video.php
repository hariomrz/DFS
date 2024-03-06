<?php

/**
 * Controller for the upload files create thumbs and save on local or remote server
 * @package upload files
 * @author
 * @version 1.0
 *
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// error_reporting(0);
class Upload_video extends Common_API_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('upload_file_model'));
    }

    /**
     * [index_post used to upload image]
     * @return [Object] [json object]
     */
    public function index_post()
    {
        $Return = $this->return;

        $Data = $this->post_data;
        /* Validation - starts */

        $validation_rule[] = array(
            'field' => 'Type',
            'label' => 'Type',
            'rules' => 'trim|required'
        );
        if (empty($this->DeviceTypeID))
        {
            $validation_rule[] = array(
                'field' => 'DeviceType',
                'label' => 'DeviceType',
                'rules' => 'trim|required'
            );
        } 
        else if (isset($Data['DeviceType']) == FALSE)
        {
            $Data['DeviceType'] = $this->DeviceTypeID;
        }

        $this->form_validation->set_rules($validation_rule);
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE)
        {
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else
        {
            $generateThumb = 1;
            $Result = $this->upload_file_model->uploadVideo($Data);

            if($Result['ResponseCode']==200 && $Data['Type']=='profile' && $this->IsApp == 1){
                $this->upload_file_model->updateProfilePicture($Result['Data']['MediaGUID'],$Result['Data']['ImageName'],$this->session->userdata('UserID'),3,$Data['ModuleEntityGUID']);
            }
            $Return['ResponseCode'] = $Result['ResponseCode'];
            $Return['Message'] = $Result['Message'];
            $Return['Data'] = $Result['Data'];
        }
        $this->response($Return);
    }
}
