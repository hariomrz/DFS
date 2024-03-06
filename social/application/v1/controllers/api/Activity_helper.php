<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_helper extends Common_API_Controller {

    function __construct($bypass = false) {    
        parent::__construct($bypass);
        
        $this->load->model(array(
            'users/user_model', 
            'activity/activity_front_helper_model', 
        ));
    }
    
    /**
    * Function Name: set_promoted_status
    * @param ActivityID
    * Description: Set activity as promoted status
    */
    public function set_promotion_status_post() {
        /* Define variables - starts */
        $return     = $this->return;
        $return['TotalRecords'] = 0;/* added by gautam*/
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        
        $is_admin = $this->user_model->is_super_admin($user_id);
        
        if(!$is_admin) {
            $error  = 'Not an admin.';
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            //$this->response($return);
        }
        
        $validation_rule =array(
            array(
                'field' => 'ActivityID',
                'label' => 'ActivityID',
                'rules' => 'trim|required|integer',
            ),
            array(
                'field' => 'IsPromoted',
                'label' => 'IsPromoted',
                'rules' => 'trim|required|integer',
            ),
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error  = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
        
        $activity_id = (int)isset($data['ActivityID']) ? $data['ActivityID'] : 0;
        $is_promoted = (int)isset($data['IsPromoted']) ? $data['IsPromoted'] : 0;
        $this->activity_front_helper_model->set_promotion($activity_id, 0, '', $is_promoted);
        
        $return['Message'] = ($is_promoted) ? 'Promoted.' : 'Unpromoted.';
        $this->response($return);
    }
    
    public function get_entity_bradcrumbs_post() {
        /* Define variables - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        
        $validation_rule =array(
            array(
                'field' => 'ModuleID',
                'label' => 'ModuleID',
                'rules' => 'trim|required|integer',
            ),
            array(
                'field' => 'ModuleEntityID',
                'label' => 'ModuleEntityID',
                'rules' => 'trim|required|integer',
            ),
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error  = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
        
        $return['Data'] = $this->activity_front_helper_model->get_entity_bradcrumbs($data['ModuleID'], $data['ModuleEntityID']);
        $this->response($return);
    }
   
}