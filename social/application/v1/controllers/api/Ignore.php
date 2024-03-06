<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This Class used as REST API for Ignore module
 * @category Controller
 * @author       Vinfotech Team
 */
class Ignore extends Common_API_Controller {

    function __construct() {
        parent::__construct();
        $this->check_module_status(1);
        $this->load->model(array('ignore_model'));
    }

    public function index_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($this->form_validation->run('api/ignore') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $entity_type = $data['EntityType'];
            $entity_guid = $data['EntityGUID'];
            if ($entity_type == 'Group') {
                $module_id = 1;
            } else if ($entity_type == 'User') {
                $module_id = 3;
            } else if ($entity_type == 'Page') {
                $module_id = 18;
            } else if ($entity_type == 'Event') {
                $module_id = 14;
            } else if ($entity_type == 'SuggestedArticle') {
                $module_id = 0;
            }
            $entity_id = get_detail_by_guid($entity_guid, $module_id);
            $this->ignore_model->ignore($user_id, $entity_type, $entity_id);
        }
        $this->response($return);
    }

}
