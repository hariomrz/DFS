<?php

class Notify_nosql_model extends NOSQL_Model {

    public function __construct() {
        parent::__construct();
    }

    public function send_notification($data) {
        $id = true;
        //email_template
        $notification = array();
        $notification['notification_type'] = $data['notification_type'];
        $notification['source_id'] = (int)$data['source_id'];
        $notification['user_id'] = (int)$data['user_id'];
        $notification['notification_status'] = 1;
        $notification['content'] = $data['content'];
        $notification['notification_destination'] = $data['notification_destination'];
        $notification['added_date'] = $data['added_date'];
        $notification['modified_date'] = $data['modified_date'];
        $notification['deviceIDS'] = isset($data['device_ids']) ? $data['device_ids'] : '';
        $notification['ios_device_ids'] = isset($data['ios_device_ids']) ? $data['ios_device_ids'] : '';
        if (in_array($notification['notification_destination'], array(1, 3, 5, 7))) {
            $this->insert_nosql(NOTIFICATION, $notification);
            //uncomment below code if last inserted id needed from mongo.
            //$id = $this->insert_id_nosql(NOTIFICATION);
        }

        /* Send Push Notifications */
        $this->load->helper('queue_helper');

        if (in_array($notification['notification_destination'], array(2, 3, 6, 7)) && (!empty($notification['deviceIDS']) || !empty($notification['ios_device_ids']))) {
            if(!empty($data['custom_notification_text'])) {
                $notification['custom_notification_text'] = $data['custom_notification_text'];
            }
            $notification['custom_notification_subject'] = isset($data["custom_notification_subject"]) ? $data["custom_notification_subject"] : '';
            
            
            add_data_in_queue($notification, 'push');
        }

        /* Send Email Notifications */
        if (in_array($notification['notification_destination'], array(4, 5, 6, 7)) && $data["to"] != "") {
            $email_content = array();
            $email_content['email'] = $data["to"];
            $email_content['subject'] = $data["subject"];
            $email_content['user_name'] = $data["user_name"];
            $email_content['content'] = $data["content"];
            $email_content['notification_type'] = $notification['notification_type'];

            switch ($notification["notification_type"]) {
                case '14':
                    $email_content['subject'] = $this->lang->line('welcome_email_subject');
                    break;
                case '15':
                    $email_content['subject'] = $this->lang->line('forgot_password_email_subject');
                    break;
            }

            add_data_in_queue($email_content, 'email');
        }

        return $id;
    }
}