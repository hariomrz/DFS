<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This Class used as REST API for Favourit module
 * @category	Controller
 * @author		Vinfotech Team
 */
class Favourite extends Common_API_Controller {

    /**
     * @Summary: call parent constructor
     * @access: public
     * @param:
     * @return:
     */
    function __construct($bypass = false) {
        parent::__construct($bypass);
        $this->check_module_status(16);        
    }

    public function index_post() {
        $return = $this->return;
        $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
        $return['Message'] = lang('input_invalid_format');
        $this->response($return);
    }

    /**
     * [toggle_favourite_post used to set entity as Favourite or un Favourite]
     */
    public function toggle_favourite_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'EntityGUID',
                    'label' => 'entity GUID',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $this->load->model(array('favourite_model'));
                $data['UserID'] = $this->UserID;
                $entity_guid        = $data['EntityGUID'];
                $entity_data        = get_detail_by_guid($entity_guid, 0, 'ActivityID, ModuleEntityID, ModuleID', 2); 
                $entity_id          = $entity_data['ActivityID'];
                $module_entity_id   =  $entity_data['ModuleEntityID'];
                $module_id          =  $entity_data['ModuleID'];
                if(empty($entity_id)){
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "entity GUID");
                } else {
                    $data['EntityID'] = $entity_id;
                    $data['ModuleEntityID'] = $module_entity_id;
                    $data['ModuleID'] = $module_id;
                    $data['EntityType'] = 'ACTIVITY';
                    $this->favourite_model->toggle_favourite($data);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
}
