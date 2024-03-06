<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Cron controller to execute cron jobs
 * @package    cron
 * @author     V-INFOTECH
 * @version    1.0
 */

class Cron extends Common_Controller
{

    public function __construct()
    {
        parent::__construct();
        //print_r($this->session->userdata('LoginSessionKey'));
        if (!empty($this->login_session_key))
        {
            $this->AccessKey = $this->login_session_key;
        }
        $this->load->model(array('cron/cron_model', 'cron/cronrule_model'));
    }

    /*
     * Function to retrieve job id and update video duration via zencoder api 
     * @param  :
     * @Output : JSON
     */

    function update_video_duration()
    {
        $status = $this->cron_model->get_job_detail_and_update_duration();
        if ($status)
        {
            $msg = "Cron Succussfully Executed";
            $status = 200;
        }
        else
        {
            $msg = "Due to some technical issue, unable to execute cron right now !";
            $status = 511;
        }
        /* $emailDataArr = array();
          $emailDataArr['IsResend'] = 0;
          $emailDataArr['Subject'] = "CronJob Success";
          $emailDataArr['TemplateName'] = "emailer/CronEmail";
          $emailDataArr['Email'] = "piyushj@vinfotech.com";
          $emailDataArr['EmailTypeID'] = 1;
          $emailDataArr['UserID'] = '5';
          $emailDataArr['StatusMessage'] = "Cron Execution";
          $emailDataArr['Data'] = array("FullName" =>"Piyush Jain", "Message" => "Cron is Successfully executed at ".date('Y-m-d H:i:s')."");

          // Prepare Email Data
          sendEmailAndSave($emailDataArr); // send mail

          $emailDataArr['Email'] = "sureshp@vinfotech.com";
          $emailDataArr['Data']['FullName'] = 'Suresh Patidar';
          sendEmailAndSave($emailDataArr); // send mail */

        echo json_encode(array('status' => $status, 'Message' => $msg));
        die;
    }

    function check_contest_status()
    {
        $this->cron_model->check_contest_status();
    }

    function birthday_notification()
    {
        $this->benchmark->mark('execution_start');
        $data = $this->cron_model->birthday_notification();
        $this->benchmark->mark('execution_ends');
        $this->benchmark->elapsed_time('execution_start', 'execution_ends');
    }

    function upcoming_event_notification()
    {
        $this->benchmark->mark('execution_start');
        $this->cron_model->upcoming_event_notification();
        $this->benchmark->mark('execution_ends');
        $this->benchmark->elapsed_time('execution_start', 'execution_ends');
    }

    function daily_digest_notification()
    {
        $this->cron_model->daily_digest_notification();
    }

    /**
     * [zencoder_notification callback function and it is called by zencoder when job is converted]
     */
    function zencoder_notification()
    {
        $this->load->model('upload_file_model');
        $this->upload_file_model->zencoder_notification();
    }

    function create_image_thumb()
    {
        echo "media_section_id = " . $media_section_id = $this->uri->segment(3);
        echo "<br>page_no = " . $page_no = $this->uri->segment(4);
        echo "<br>page_size = " . $page_size = $this->uri->segment(5); //die;
        if (empty($page_no))
        {
            $page_no = 1;
        }
        if (empty($page_size))
        {
            $page_size = 30;
        }
        if (!empty($media_section_id))
        {
            $this->load->model('upload_file_model');
            $this->upload_file_model->create_image_thumb($media_section_id, $page_no, $page_size);
        }
    }

    function copy_video_thumb()
    {
        echo "media_section_id = " . $media_section_id = $this->uri->segment(3);
        echo "<br>page_no = " . $page_no = $this->uri->segment(4);
        echo "<br>page_size = " . $page_size = $this->uri->segment(5);
        if (empty($page_no))
        {
            $page_no = 1;
        }
        if (empty($page_size))
        {
            $page_size = 30;
        }
        if (!empty($media_section_id))
        {
            $this->load->model('upload_file_model');
            $this->upload_file_model->copy_video_thumb($media_section_id, $page_no, $page_size);
        }
    }

    function calculate_weightage()
    {
        $this->cron_model->calculate_weightage();
    }

    function calculate_rank()
    {
        $this->cron_model->calculate_rank();
    }

    function check_activities()
    {
        $this->cron_model->check_activities();
    }

    function check_reminder()
    {
        if(!$this->settings_model->isDisabled(28)) {
            $this->cron_model->current_reminder();
        }
    }

    function delete_dangling_media()
    {
        $this->load->model('upload_file_model');
        $this->upload_file_model->delete_dangling_media();
    }

    function generate_picture_feed()
    {
        $this->cron_model->generate_picture_feed();
    }

    function insert_all_group_member()
    {
        $this->load->model('group/group_model');
        $this->group_model->get_all_groups_associate_user();
    }

    function update_user_privacy($key)
    {
        $this->cron_model->update_user_privacy($key);
    }

    public function do_action()
    {
        // expire all password reset token which is older XXX hours
        $this->load->model('users/login_model');
        $this->login_model->expire_password_reset_tokens();
    }

    public function remove_unused_tags()
    {
        //remove tags which are not associated with any entity
        $this->load->model('activty/Activity_model');
        $this->activity_model->remove_unused_tags();
    }

    public function calculate_group_popularity()
    {
        $this->cron_model->calculate_group_popularity();
    }

    public function calculate_page_popularity()
    {
        $this->cron_model->calculate_page_popularity();
    }
    
    public function cache_all_activity()
    {
        echo "<br>page_no = " . $page_no = $this->uri->segment(3);
        echo "<br>page_size = " . $page_size = $this->uri->segment(4); //die;
        $this->load->model('activty/Activity_model');
        $this->activity_model->set_activity_job($page_no, $page_size);
    }
    public function cache_all_profile()
    {
        echo "<br>page_no = " . $page_no = $this->uri->segment(3);
        echo "<br>page_size = " . $page_size = $this->uri->segment(4); //die;
        $this->load->model('users/User_model');
        $this->user_model->set_profile_job($page_no, $page_size);
    }

    /**
     * Function Name: trending_post
     * Description: calculate and set trending post depends on comments + like + share + view + replies which is modified in past 24 hours as well
     */
    public function trending()
    {
        $this->cron_model->set_trending_post();
    }

    public function update_group_member_count()
    {
        $this->cron_model->update_group_member_count();
    }

    /**
     * Function Name: check_reminder_early
     * Description: It will called by cron to get notification of reminders before 15 mins of arrival
     */
    function check_reminder_early()
    {
        if(!$this->settings_model->isDisabled(28)) {
            // $date = get_current_date('%Y-%m-%d %H:%i','0.0104',1);//get time with 15 mins of diff
            $this->cron_model->early_reminder();
        }
    }

    //reset all NOtification for all users
    public function set_notification_settings_for_all($user_id=0)
    {
        $this->cron_model->set_reminder_notification_settings_for_all($user_id);           
    }

    public function calculate_default_activity()
    {
        $this->cronrule_model->calculate_default_activity();
    }

    //calculate user tag weightage
    public function user_tag_weightage()
    {
        $this->cron_model->user_tag_weightage();
    }
    
    /**
     * Function Name: calculate_activity_score
     * Description: calculate activity score for user and set highly active user
     */
    public function calculate_activity_score() {
        //BROWSING  === CONTRIBUTION
        $this->load->model(array('log/user_activity_log_score_model'));
        //$data = $this->user_activity_log_score_model->get_users_for_calculation(1, 200, 'CONTRIBUTION');
        //$data = $this->user_activity_log_score_model->calculate_users_score_by_type(1);
        
        
        $this->user_activity_log_score_model->set_auto_feature_posts();  
        $this->user_activity_log_score_model->calculate_users_score('BROWSING');
        $this->user_activity_log_score_model->calculate_users_score('CONTRIBUTION');
        $this->user_activity_log_score_model->set_highly_active_users();
        
    }
    
    
    public function calculate_activity_score_fix() {
        //BROWSING  === CONTRIBUTION
        $this->load->model(array('log/user_activity_log_score_model'));
        $this->user_activity_log_score_model->update_scores_for_dates();          
        
    }
    
    
    /**
     * Function Name: set_suspend_to_active_status
     * Description: Function to set user status from suspend to active if user suspend date end
     */
    public function set_suspend_to_active_status() {
        $this->load->model(array('users/user_model'));
        $this->user_model->set_user_status_suspend_to_active();
    }

    public function archive_notification($user_id=0,$no_of_days=15,$no_of_notification=300)
    {
        $this->load->model('notification_model');
        $this->notification_model->archive_users_notifications($user_id,$no_of_days,$no_of_notification);
    }

    public function archive_communications($user_id=0,$no_of_days=15,$no_of_emails=300)
    {
        $this->load->model(array('cron/cron_model'));
        $this->cron_model->archive_users_communications($user_id,$no_of_days,$no_of_emails);           
    }
    
    public function update_mailchimp_subscribers() {
        $this->load->model(array(
            'admin/newsletter/newsletter_model'
        ));
        
        $this->newsletter_model->update_mailchimp_subscriber_ids();
    }
    

    public function send_contest_notification()
    {
        $this->cron_model->send_contest_notification();
    }

    
    public function run_auto_update_group_lists() {
        
        $this->load->model(array(
            'admin/newsletter/newsletter_model', 'admin/login_model',
            'settings_model', 'admin/newsletter/newsletter_users_model',
            'admin/newsletter/newsletter_mailchimp_model'
        ));
        
        $this->load->model(array(
            'admin/newsletter/newsletter_model'
        ));
        
        $this->newsletter_model->autoUpdateGroupLists();
    }
    
    public function run_user_updates_incomplete_profile_notifications() {
        $this->load->model(array(
            'user_updates/user_updates_model'        
        ));                
        $this->user_updates_model->send_email_notification_to_incomplete_profile_users();        
    }
    
    public function send_email_notification_to_inactive_users() {
        $this->load->model(array(
            'user_updates/user_updates_model'        
        ));                
        $this->user_updates_model->send_email_notification_to_inactive_users();        
    }
    
    public function send_notification_daily_digest() {
        $this->load->model(array(
            'user_updates/user_updates_model'        
        ));                
        $this->user_updates_model->send_notification_daily_digest();        
    }
    
    public function mailchimp_webhook() {
        
        $this->load->config('mailchimp');
        $webhook_api_key = $this->config->item('api_key');        
        if($webhook_api_key != $_GET['api_key']) {
            return;
        }
        
        $this->load->model(array(
            'admin/newsletter/newsletter_model'
        ));                
        
        $this->newsletter_model->mailchimp_webhook($_POST);
         
    }
    
    
    public function mailchimp_compaign_report_synch() {
        
        $this->load->model(array('admin/newsletter/newsletter_mailchimp_model'));                  
        $this->load->model(array(
            'admin/newsletter/newsletter_model'
        ));                
        
        $this->newsletter_model->iterate_compaigns_reports();
        
        //$this->newsletter_mailchimp_model->get_compaigns();
        //$this->newsletter_mailchimp_model->get_link_data('/reports/ea37030f0c/sub-reports');
         
    }
    
    public function set_activity_data_temp() {
        $this->load->model(array('activity/activity_front_helper_model'));  
        $this->activity_front_helper_model->set_activities_data_temp();
    }
    
    public function send_new_release_notification() {die;
        //$token = 'f6I3ePDRQQM:APA91bFaY1kWAp-UYmUuc9bDpvUp2kisjNsrPOPopTiW-_eJG6ayYgIX7KJ9Oj_VrdBAhoVKCAgyY43Cqhb57CUcym39Y_PbZyYOfYYntiNyECbSoXdqH1yvo8RfXStIL1XPsWYAsYoI';
                
        ini_set("memory_limit",-1);
        ini_set('max_execution_time', 3600);
       
       
        $Query = $this->db->query("SELECT DeviceToken, DeviceTypeID FROM `ActiveLogins` WHERE DeviceToken!='' GROUP BY DeviceToken, DeviceTypeID ORDER BY ActiveLoginID DESC");
        if ($Query->num_rows() > 0) {
            foreach ($Query->result_array() as $Notifications) { 
                //$token = 'f6I3ePDRQQM:APA91bFaY1kWAp-UYmUuc9bDpvUp2kisjNsrPOPopTiW-_eJG6ayYgIX7KJ9Oj_VrdBAhoVKCAgyY43Cqhb57CUcym39Y_PbZyYOfYYntiNyECbSoXdqH1yvo8RfXStIL1XPsWYAsYoI';
                $token = $Notifications['DeviceToken'];
                $push_notification = array("EntityID" => "", "ModuleID" => "", "ModuleEntityGUID" => "", "Refer" => "UPDATE_APP", "EntityGUID" => "");
                $message = "तकनीकी ख़राबी ठीक करने के लिए ऐप अप्डेट करें";
                $msg = push_notification_android(array($token), $message, 0, array("PushNotification" => $push_notification));
                //$message = "Settings->Apps->Bhopu->Storage->Clear Data & Cache";
                //$msg = push_notification_android(array($token), $message, 0, array("PushNotification" => $push_notification));
            }
        }
    }  
    
    public function poll_analytics() {        
        initiate_worker_job('update_poll_analytics');
        //$this->load->model(array('polls/polls_model'));
        //$this->polls_model->poll_analytics_data();
    }
    
    public function send_poll_result_notificaton() {
        initiate_worker_job('send_poll_result_notificaton');
        //$this->load->model(array('polls/polls_model'));
        //$this->polls_model->send_poll_result_notificaton();
        exit;
    }
    
    public function delete_api_static_file() {
        $file_name = $this->uri->segment(3);
        if(!empty($file_name)) {
            $this->cron_model->delete_api_static_file($file_name);
            if (CACHE_ENABLE) {
                $this->cache->delete($file_name);
            }
        }
    }
    
    public function ward_user_count() {        
        //$this->cron_model->ward_user_count();
        exit;
    }

    public function ward_engagement() {     
        $no_of_day = $this->uri->segment(3);   
        $no_of_day = (int)$no_of_day;
        if(empty($no_of_day)) {
            $no_of_day = 1;
        }
        $this->cron_model->ward_engagement($no_of_day);
        exit;
    }

    public function send_message_to_all_user() { 
        //$this->load->model('messages/messages_model');
       // $this->messages_model->send_message_to_all_user();
       initiate_worker_job('sendmsg', array(), '', 'send_message');
        exit;
    }

    public function user_subscribe_to_topic() { 
        //$this->cron_model->ward_user_subscribe_to_topic();
        initiate_worker_job('ward_user_subscribe_to_topic', array(), '', 'topic');  
        exit;  
    }

    public function sync_tag_category_as_tag() { //die('hi');
        $this->load->model(array('tag/tag_model'));
        $this->tag_model->sync_tag_category_as_tag();
        exit;
    }

    public function calculate_user_point() {
        initiate_worker_job('calculate_users_points', array(), '', 'point');  
        //$this->load->model(array('log/user_activity_log_score_model'));
        //$this->user_activity_log_score_model->calculate_users_points();
    }

    public function send_prediction_participation_reminder() {
        initiate_worker_job('prediction_participation_reminder', array(), '', 'pp_reminder');  
        //$this->load->model(array('quiz/quiz_model'));
       // $this->quiz_model->prediction_participation_reminder();
        exit;
    }
    
    public function calculate_top_contributor() {
        $all = $this->uri->segment(3);
        if(empty($all)) {
            $all = 0;
        }
        initiate_worker_job('calculate_top_contributor', array('all' => $all), '', 'top_contributor');   
        //$this->load->model(array('log/user_activity_log_score_model'));
        //$this->user_activity_log_score_model->calculate_top_contributor();
    }
    
}
