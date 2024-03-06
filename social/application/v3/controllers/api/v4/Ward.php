<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ward extends Common_API_Controller {

    function __construct($bypass = false) {
        parent::__construct($bypass);
        $this->load->model(array('ward/ward_model'));
    }

    
    /**
     * [get list of feature user by ward wise]
     * @return [array] [list of feature user]
     */
    function get_featured_user_post() {
        $return = $this->return;
        /* if(!in_array($this->UserID, array(1, 563, 2342, 10161, 11003))) {
            $this->response($return);
            die;
        }
        */
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'WID',
                    'label' => 'ward ID',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id = $this->UserID;
                $this->load->model(array('users/user_model'));
                $this->user_model->set_friend_followers_list($user_id);
                $data['UserID'] = $user_id;
                $return['Data'] = $this->ward_model->get_featured_user($data);
                $pinned = $this->ward_model->get_pinned_feature_user($data);
                if(!empty($pinned)) {
                    $return['PinnedUser'] = $pinned;
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }    
}
?>