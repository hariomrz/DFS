<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_hide extends Common_API_Controller {

    function __construct($bypass = false) {    
        parent::__construct($bypass);
        
        $this->load->model(array(
            'activity/activity_hide_model', 
        ));
    }


    public function index_post() {
         /* Define variables - starts */
         $return     = $this->return;
         $data       = $this->post_data;
         
         $validation_rule =array(
            array(
                'field' => 'ActivityGUID',
                'label' => 'activity guid',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'Status',
                'label' => 'status',
                'rules' => 'trim|required|in_list[1,2]',
            )
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error  = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        } else {
            $user_id    = $this->UserID;
            $activity_id = get_detail_by_guid($data['ActivityGUID']);
            if($activity_id) {  
                $hide_data['ActivityID'] = $activity_id;   
                $hide_data['UserID'] = $user_id;  
                $hide_data['Status'] = $data['Status'];         
                $this->activity_hide_model->toggle_hide($hide_data);                     
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "activity guid");
            }
        }
        $this->response($return);        
    }

    public function get_total_user_used_hide_post() {

        echo 'No of unique users who used Hide Post(Today) = '.$this->activity_hide_model->get_total_user_used_hide_post();
        echo '<br>No of unique users who used Hide Post(All) = '.$this->activity_hide_model->get_total_user_used_hide_post('All');
        die;
    }
}