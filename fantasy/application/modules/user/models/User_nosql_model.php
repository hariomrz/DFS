<?php

class User_nosql_model extends NOSQL_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used for send notification and email
     * @param array $data
     * @return int
     */
    public function send_notification($data) {
        $id = true;
        //email_template
        $notification = array();
        $notification['notification_type'] = $data['notification_type'];
        $notification['source_id'] = (int)$data['source_id'];
        $notification['user_id'] = (int) $data['user_id'];
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
            add_data_in_queue($notification, 'push');
        }

        /* Send Email Notifications */
        if (in_array($notification['notification_destination'], array(4, 5, 6, 7))) {
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

            $queue_name = 'email';
            if(isset($data['queue_name']) && $data['queue_name'] !=='')
            {
                $queue_name = $data['queue_name'];
            }
            add_data_in_queue($email_content, $queue_name);
        }

        return $id;
    }

    public function send_email($to, $subject = "", $message = "", $from_email = FROM_ADMIN_EMAIL, $from_name = FROM_EMAIL_NAME )
	{
        /* configuration and sending email */
        $config = array();
        $config['smtp_host'] = SMTP_HOST;
        $config['smtp_user'] = SMTP_USER;
        $config['smtp_pass'] = SMTP_PASS;
        $config['smtp_port'] = SMTP_PORT;
        $config['protocol']  = PROTOCOL;
        $config['smtp_crypto'] = SMTP_CRYPTO;
        $config['mailpath']  = '';
        $config['mailtype']  = 'html';
        $config['charset']   = 'utf-8';
        $config['wordwrap']  = TRUE;

		$this->load->library('email');
       // $email = new CI_Email();
        $this->email->initialize($config);
        $this->email->set_newline("\r\n");
        $this->email->clear();
        $this->email->from($from_email, $from_name);
        $this->email->to(trim($to));
        $this->email->subject($subject);
        $this->email->reply_to(NO_REPLY_EMAIL, $from_name);
        $this->email->message($message);

        if ($this->email->send()) {
            return TRUE;
        } else {
        	//$email->print_debugger(); //for debuging
            return FALSE;
        }
    }

}

?>