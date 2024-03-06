<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 * This Class used as REST API for Flag module
 * @category    Controller
 * @author      Vinfotech Team
 */
class Flag extends Common_API_Controller {

    /**
     * @Summary: call parent constructor
     * @access: public
     * @param:
     * @return:
     */
    function __construct() {
        parent::__construct();
        $this->check_module_status(12);
        $this->load->model(array('flag_model'));
    }

    public function index_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */
        $data = $this->post_data;
        /* Validation - starts */
        if ($this->form_validation->run('api/flag') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else { /* Validation - ends */
            $data['UserID'] = $this->UserID;
            $result = $this->flag_model->set_flag($data);
            $return['ResponseCode'] = $result['ResponseCode'];
            $return['Message'] = $result['Message'];
        }
        $this->response($return);
    }

    public function is_flagged_post() {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Message'] = lang('success');
        $return['Data'] = array();
        $return['ServiceName'] = 'api/is_flagged';

        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data['TypeID'])) {
            $type_id = $data['TypeID'];
        }

        if (isset($data['Type'])) {
            $type = $data['Type'];
        }

        if ($this->form_validation->required($type_id) == FALSE) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'Type ID is required.';
        } elseif ($this->form_validation->required($type) == FALSE) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'Type is required.';
        } else {
            $return['Data'] = $this->flag_model->is_flagged($user_id, $type_id, $type);
            $return['Message'] = 'Flag Added Successfully.';
        }
        $this->response($return);
    }

    public function remove_flag_post() {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Message'] = lang('success');
        $return['Data'] = array();
        $return['ServiceName'] = 'api/flag/remove_flag';

        $data = $this->post_data;
        $user_id = $this->UserID;

        $type_id = '';
        $type = '';

        if (isset($data['TypeID'])) {
            $type_id = $data['TypeID'];
        }

        if (isset($data['Type'])) {
            $type = $data['Type'];
        }

        if ($this->form_validation->required($type_id) == FALSE) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'Type ID is required.';
        } elseif ($this->form_validation->required($type) == FALSE) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'Type is required.';
        } else {
            $where = array('UserID' => $user_id, 'TypeID' => $type_id, 'Type' => $type);
            $this->db->where($where);
            $this->db->delete(FLAG);
            if (CACHE_ENABLE) {
                $this->cache->delete('user_flagged_activity' . $user_id);
            }
        }
        $this->response($return);
    }
}
