<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include_once APPPATH . 'controllers/api/Activity.php';

class Adminactivity extends Activity {

    function __construct() {
        parent::__construct(true);
        
        $this->load->model(array(
            'admin/activity/activity_entities_model',
            'admin/activity/activity_helper_model'
        ));
        $this->activity_helper_model->setUserSessionData();
    }
    

    public function get_user_activity_entities_post() 
    {
        $return['ResponseCode'] = '200';
        $return['Message'] = lang('success');
        $return['ServiceName'] = 'admin_api/activity/get_user_activity_entities';
        $return['Data'] = array();
        $data = $this->post_data;

        // Check data posted
        if (!isset($data) || !$data) {
            $return['ResponseCode'] = '519';
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        /* Validation - starts */
        if ($this->form_validation->run('api/admin/activity/get_activity_entities') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 511;
            $return['Message'] = $error; //Shows all error messages as a string
            $this->response($return);
        }

        $page_no = (int) isset($data['page_no']) ? $data['page_no'] : 1;
        $page_size = (int) isset($data['page_size']) ? $data['page_size'] : 20;

        $return['Data'] = $this->activity_entities_model->get_user_activity_entities($data['UserID'], $page_no, $page_size);
        $this->response($return);
    }

    public function dummy_activities_post()
    {
        $this->activity_model->getDummyUserActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter,array(),$view_entity_tags,$role_id,$post_type,$tags, $rules);
    }

    public function is_dummy_user_like_post()
    {
        $return['ResponseCode'] = '200';
        $return['Message'] = lang('success');
        $return['ServiceName'] = 'admin_api/activity/get_user_activity_entities';
        $return['Data'] = array();
        $data = $this->post_data;

        $user_id = isset($data['UserID']) ? $data['UserID'] : '' ;
        $activity_id = isset($data['ActivityID']) ? $data['ActivityID'] : '' ;

        if($user_id && $activity_id)
        {
            $return['Data'] = $this->activity_model->is_liked($activity_id, 'ACTIVITY', $user_id, 3, $user_id);
        }

        $this->response($return);
    }
}

//End of file ipsetting.php