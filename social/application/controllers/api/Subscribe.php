<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Subscribe extends Common_API_Controller {

    function __construct($bypass = false) {
        parent::__construct($bypass);
        $this->check_module_status(17);
        $this->load->model(array('subscribe_model'));
    }

    /**
     * Function Name: toggle_subscribe
     * @param user_id,EntityType,EntityID
     * Description: subscribe to activity
     */
    public function toggle_subscribe_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($this->form_validation->run('api/subscribe/toggle_subscribe') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $entity_id = '';
            $entity_type = $data['EntityType'];
            $entity_guid = $data['EntityGUID'];
            if ($entity_type == 'ACTIVITY' || $entity_type == 'ALBUM' || $entity_type == 'USER' || $entity_type == 'GROUP' || $entity_type == 'PAGE' || $entity_type == 'EVENT') {

                if ($entity_type == 'USER') {
                    $entity_id = get_detail_by_guid($entity_guid, 3);
                } else if ($entity_type == 'GROUP') {
                    $entity_id = get_detail_by_guid($entity_guid, 1);
                } else if ($entity_type == 'PAGE') {
                    $entity_id = get_detail_by_guid($entity_guid, 18);
                } else if ($entity_type == 'EVENT') {
                    $entity_id = get_detail_by_guid($entity_guid, 14);
                } else if ($entity_type == 'ALBUM') {
                    $entity_id = get_detail_by_guid($entity_guid, 13);
                } else {
                    $entity_id = get_detail_by_guid($entity_guid);
                }

                if (empty($entity_id) || empty($user_id)) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "entity GUID and User ID");
                } else {
                    $return['Data']['IsSubscribed'] = $this->subscribe_model->toggle_subscribe($user_id, $entity_type, $entity_id);
                }
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "Entity Type");
            }
        }
        $this->response($return);
    }
}

?>