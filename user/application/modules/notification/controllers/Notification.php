<?php

class Notification extends Common_Api_Controller {

    function __construct() {
        parent::__construct();
    }

    /**
     * get_unread_notification to get unread notification count
     * @param
     * @return json array
     */
    public function get_unread_notification_post() {
        $this->load->model('notification/Notify_nosql_model');
        $result = $this->Notify_nosql_model->get_unread_notification($this->user_id);
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * get_notification to get notification list
     * @param
     * @return json array
     */
    public function get_notification_post() {
        $post_data = $this->post();
        $post_data['user_id'] = $this->user_id;

        $this->load->model('notification/Notify_nosql_model');
        $result = $this->Notify_nosql_model->get_notifications($post_data);
        $message_key = $this->lang_abbr."_message";
        $subject_key = $this->lang_abbr."_subject";
        if (!empty($result)) {
            $notify_master_cache = 'notification_master';
            $list_arr = $this->get_cache_data($notify_master_cache);

            if (empty($list_arr)) {
                $master_list = $this->Notify_nosql_model->select_nosql(NOTIFICATION_DESCRIPTION);
                $list_arr = array_column($master_list, null, 'notification_type');
                $this->set_cache_data($notify_master_cache, $list_arr, REDIS_30_DAYS);
            }
            foreach ($result as $key => $value) {
                if(isset($list_arr[$value['notification_type']][$message_key]))
                {
                    $result[$key]['message'] = $list_arr[$value['notification_type']][$message_key];
                }

                if(isset($value['notification_type']) && in_array($value['notification_type'], array(136,142,143,144,145,146,147,148,149,150)))
                {
                $result[$key]['content']  = json_decode($result[$key]['content'],true);
                $result[$key]['content']['b_to_c']= $this->lang->line("bank");
                if($this->app_config['allow_crypto']['key_value']==1)
                {
                    $result[$key]['content']['b_to_c']= $this->lang->line("crypto_wallet");
                }
                $result[$key]['content'] = json_encode($result[$key]['content']);
                } 

                if(isset($list_arr[$value['notification_type']][$subject_key]))
                {
                    $result[$key]['subject'] = $list_arr[$value['notification_type']][$subject_key];
                }
                unset($result[$key]['deviceIDS']);
                unset($result[$key]['modified_date']);
                unset($result[$key]['notification_destination']);
                unset($result[$key]['source_id']);
                unset($result[$key]['user_id']);
                unset($result[$key]['_id']);
            }
            //update read count
            if(isset($post_data['page_no']) && $post_data['page_no'] == 1){
                $this->Notify_nosql_model->update_all_nosql(NOTIFICATION, array('user_id' => (int)$this->user_id, 'notification_status' => 1), array('notification_status' => 2));
            }
        }

        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }
    
    function sync_notification_description_post() {
        $this->load->model('notification/notification_model');
        $this->notification_model->sync_notification_description();
    }

    /**
     * update_notification_status to report delivery and view status of Notifications 
     * @param type: 'delivery || view', recent_communication_unique_id 
     * @return Null
     */
    public function update_notification_status_post()
    {
        $post_data = $this->post();
        if (!empty($post_data) && isset($post_data['type']) && isset($post_data['recent_communication_unique_id']))
        {
            $this->load->model('notification/notification_model');
            $count_detail = $this->notification_model->get_single_row('notification_delivered_count, notification_viewed_count', CD_RECENT_COMMUNICATION, array("recent_communication_unique_id" => $post_data['recent_communication_unique_id']));
            $update_data = array();
            if ($post_data['type'] == 'delivery')
            {
                $update_data['notification_delivered_count'] = $count_detail['notification_delivered_count'] + 1;
            }
            else if ($post_data['type'] == 'view')
            {
                $update_data['notification_viewed_count'] = $count_detail['notification_viewed_count'] + 1;
            }

            $this->notification_model->update(CD_RECENT_COMMUNICATION, $update_data, array("recent_communication_unique_id" => $post_data['recent_communication_unique_id']));

            $this->api_response_arry['data']    = TRUE;
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("input_invalid_format");
            $this->api_response();
        }
    }

}
