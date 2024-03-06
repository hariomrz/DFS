<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Feedback_model extends MY_Model {
    
    public $db_user ;
    public function __construct()  {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
    }

    function notify_user_on_new_feedback($data) {
        $this->load->model('notification/Notify_nosql_model');
        
        $push_notification_title = array(
            'We love your Feedbacks 🧡',
            'Can you answer this❓'
        );
        $push_notification_text = array(
            'Jack, our admin guy, is getting greedy for some more from you 🤗 Answer him here 📝',
            'Because we love your feedbacks. 😍 See what\'s in here 📩'
        );            
        
        $current_date = format_date();
        $notify_data = array();
        $notify_data["notification_type"] = 581; 
        $notify_data["source_id"] = 0;
        $notify_data["notification_destination"] = 2; //Push
        $notify_data["added_date"] = $current_date;
        $notify_data["modified_date"] = $current_date;

        // for android users
        // ,'ios_device_ids' => implode(',',$user['ios_device_ids'])
        $android_user_details = $this->get_all_user_with_android_device_ids();
        foreach($android_user_details as $user) {            
            $notify_data["user_id"] = $user['user_id'];
            $notify_data["device_ids"] = $user['device_ids'];            
            // $notify_data["ios_device_ids"] = implode(',',$user['ios_device_ids']);            
            $notify_data["content"] 		= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids'], 'banner_image' => ''));
            $notify_data['custom_notification_subject'] = $push_notification_title[array_rand($push_notification_title)];;
            $notify_data['custom_notification_text'] = $push_notification_text[array_rand($push_notification_text)];
            $this->Notify_nosql_model->send_notification($notify_data);
        }  
        
        $ios_user_details = $this->get_all_user_with_ios_device_ids();
        foreach($ios_user_details as $user) {            
            $notify_data["user_id"] = $user['user_id'];
            // $notify_data["device_ids"] = implode(',',$user['device_ids']);            
            $notify_data["ios_device_ids"] = $user['ios_device_ids'];            
            $notify_data["content"] 		= json_encode(array('user_id' => $user['user_id'], 'ios_device_ids' => $user['ios_device_ids'], 'banner_image' => ''));
            $notify_data['custom_notification_subject'] = $push_notification_title[array_rand($push_notification_title)];;
            $notify_data['custom_notification_text'] = $push_notification_text[array_rand($push_notification_text)];
            $this->Notify_nosql_model->send_notification($notify_data);
        } 
              
    }
}