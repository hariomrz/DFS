<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Announcement extends Common_API_Controller {

    function __construct() {
        parent::__construct();
    }

    /**
     * used to get announcement details
     */
    public function index_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID; 
        
        $this->load->model('announcement/announcement_model');
        if(API_VERSION == "v4"){
            $return['Data'] = $this->announcement_model->list($user_id);
            
            $return['ANDROID_VERSION'] = ANDROID_VERSION;
            $return['IOS_VERSION'] = IOS_VERSION;
        } else {
            $return['Data'] = $this->announcement_model->details($user_id);
        }
        
        
        $this->response($return);
    }

    /**
     * used to ignore announcement
     */
    public function ignore_post() {
        $return = $this->return;
        $data = $this->post_data; 
        $user_id = $this->UserID; 
        if ($data) {

            $config = array(
                array(
                    'field' => 'BlogGUID',
                    'label' => 'announcement guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $blog_id = get_detail_by_guid($data['BlogGUID'], 24,'BlogID');
                if($blog_id) {
                    $this->load->model(array('announcement/announcement_model'));
                    $this->announcement_model->ignore($blog_id, $user_id);
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = sprintf(lang('valid_value'), "announcement guid");
                }
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }
}
