<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    Home
 
 * @version    1.0
 */
class Home extends Common_Controller {

// $page_name variable help to identify page in the header section and according to page javascript file will load
    public $AccessKey = '';

    public function __construct() {
        parent::__construct();
        //print_r($this->session->userdata('LoginSessionKey'));
        if ($this->session->userdata('LoginSessionKey') != '') {
            $this->AccessKey = $this->session->userdata('LoginSessionKey');
        }
    }

    public function get_recent_conversation() {
        $this->load->model('users/user_model');
        $this->user_model->get_recent_conversation(5, 3, 5);
    }

    public function run_queue($user_guid) {
        initiate_worker_job('calculate_activity_rank', array('UserGUID' => $user_guid, 'ENVIRONMENT' => ENVIRONMENT));
    }

    public function update_edited() {
        $this->db->query("UPDATE Activity SET IsEdited='1' WHERE ActivityID IN (SELECT ActivityID FROM ActivityHistory GROUP BY ActivityID)");
    }

    public function calculate_rank_by_activity() {
        $this->load->model('cron/cron_model');
        $this->cron_model->calculate_rank_by_activity(4428, 'development');
    }

    public function check_cache() {
        $data = $this->cache->get('nfs_5');
        print_r($data);
    }

    public function notification_check() {
        $this->load->model('notification_model');
        $this->notification_model->add_notification(82, 5, 5, 2763, array(), true, 1);
    }

    public function check_featured_activity() {
        $this->load->model('activity/activity_model');
        $result = $this->activity_model->get_featured_post(1, 1, 368, 1, 3);
    }

    public function set_privacy() {
        $this->load->model('privacy/privacy_model');
        $query = $this->db->get(USERS);
        if ($query->num_rows()) {
            foreach ($query->result() as $user) {
                $this->privacy_model->save($user->UserID, 'low');
            }
        }
    }

    public function set_mention() {
        $this->load->model('activity/activity_model');
        $this->db->select('A.PostContent,A.ActivityID');
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(MENTION . ' M', 'A.ActivityID=M.ActivityID', 'left');
        $this->db->where('M.ActivityID is NULL', NULL, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $result) {
                preg_match_all('/{{([0-9]+)}}/', $result['PostContent'], $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $match) {
                        $tagged_title = get_detail_by_id($match, 3, 'FirstName,LastName', 2);
                        $tagged_title = $tagged_title['FirstName'] . ' ' . $tagged_title['LastName'];
                        $mention_id = $this->activity_model->add_mention($match, 3, $result['ActivityID'], $tagged_title);
                        $result['PostContent'] = str_replace('{{' . $match . '}}', '{{' . $mention_id . '}}', $result['PostContent']);
                    }
                    $this->db->set('PostContent', $result['PostContent']);
                    $this->db->where('ActivityID', $result['ActivityID']);
                    $this->db->update(ACTIVITY);
                }
            }
        }
    }

    public function show_emailer($key) {
        $this->lang->load('notification');
        $layout = $this->config->item("email_layout");
        $emailData['content_view'] = 'emailer/n_' . $key;
        $this->load->view($layout, $emailData);
    }

    public function grow_your_network() {
        $this->load->view('networks/build_network_main');
    }

    public function reset_particular_notification($notification_type_key, $module_id) {
        ini_set("memory_limit",-1);
        ini_set('max_execution_time', 3600);
        $this->load->model('notification_model');
        $query = $this->db->get(USERS);
        if ($query->num_rows()) {
            foreach ($query->result() as $user) {
                $this->notification_model->reset_particular_notification($user->UserID, $notification_type_key, $module_id);
            }
        }
    }

    public function set_notifications() {
        $this->load->model('notification_model');
        $query = $this->db->get(USERS);
        if ($query->num_rows()) {
            foreach ($query->result() as $user) {
                $this->notification_model->set_all_notification_on($user->UserID);
            }
        }
    }

    public function language_file() {
        /* ob_start();
          $lang_keys = $this->lang->language;
          echo 'var lang = [];';
          foreach ($lang_keys as $key => $val)
          {
          echo 'lang["' . $key . '"] = "' . $val . '";';
          }
          header('Content-Type: application/javascript');
          header("Cache-Control: max-age=2538000");
          header("Expires: ".date('D, d M Y H:i:s',strtotime("+1 month", time()))." GMT"); */

        $lang_keys = $this->lang->language;
        $result = 'var lang = [];';
        foreach ($lang_keys as $key => $val) {
            $result .= 'lang["' . $key . '"] = "' . $val . '";';
        }
        //header('Content-Type: application/javascript');
        $this->output->set_header("Cache-Control: public, max-age=2538000, proxy-revalidate");
        $this->output->set_header("Pragma: cache");
        $this->output->set_header("Expires: " . date('D, d M Y H:i:s', strtotime("+1 month", time())) . " GMT");
        $this->output->set_content_type('application/javascript')
                ->set_output($result);
    }  
    
    
    public function settings_file() {        
        $lang_keys = $this->lang->language;
        $result = 'var lang = [];';
        foreach ($lang_keys as $key => $val) {
            $result .= 'lang["' . $key . '"] = "' . $val . '";';
        }
        
        $this->isJSRequest = 1;
        $result = $this->load->view('home/settings_file', '', true);
        
        //header('Content-Type: application/javascript');
        $this->output->set_header("Cache-Control: no-cache");
        $this->output->set_header("Pragma: no-cache");
        //$this->output->set_header("Expires: " . date('D, d M Y H:i:s', strtotime("+1 month", time())) . " GMT");
        $this->output->set_content_type('application/javascript')
                ->set_output($result);
    }    

    public function userSettings() {
         echo $result = $this->load->view('home/settings_file', '', true);
    }

    public function index() {
        redirect('/');
    }

    
    /* End of file signup.php */
    /* Location: ./application/controllers/signup.php */

    public function download_csv() {
        $SQL = $this->input->post('hdnQuery');
        $query = $this->db->query($SQL);
        $results['results'] = $query->result_array();

        $table = "test";
        $csv = 'User Name, Email, Register Date, Last Login' . "\n";

//BUILD CSV ROWS
        foreach ($query->result_array() as $result) {
//$csv .= $result['firstname'].' '.$result['lastname'].','.$result['email'].','.$result['resgisdate'].','.$result['lastlogindate']. "\n";
            $csv .= $result['firstname'] . ' ' . $result['lastname'];
            $csv .= ',' . $result['email'];
            $csv .= ',' . $result['resgisdate'];
            $csv .= ',' . $result['lastlogindate'] . "\n";
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$table.csv\";");
        header("Content-Transfer-Encoding: binary");

        echo($csv);
    }

    function download($MediaGUID, $type = 'messages') {
        //$details = get_detail_by_guid($MediaGUID, 21, 'OriginalName,ImageName', 2);
        $this->db->select('M.ImageName, M.OriginalName, MT.Name as MediaType');

        $this->db->from(MEDIA . ' M');
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->db->where('M.MediaGUID', $MediaGUID);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $result = $query->row();
            if ($result->ImageName) {
                //echo $result->MediaType;
                $url = IMAGE_SERVER_PATH . 'upload/' . $type . '/org_' . $result->ImageName;
                if ($result->MediaType == 'Video') {
                    $url = IMAGE_SERVER_PATH . 'upload/' . $type . '/' . $result->ImageName;
                }

                set_time_limit(0);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $r = curl_exec($ch);
                curl_close($ch);

                $this->load->helper('download'); //load helper
                force_download($result->OriginalName, $r);

                /* header('Expires: 0'); // no cache
                  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                  header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
                  header('Cache-Control: private', false);
                  header('Content-Type: application/force-download');
                  header('Content-Disposition: attachment; filename="' . basename($details['OriginalName']) . '"');
                  header('Content-Transfer-Encoding: binary');
                  header('Content-Length: ' . strlen($r)); // provide file size
                  header('Connection: close');
                  echo $r; */
            }
        }
    }

    function download22222222222($MediaGUID, $type = 'messages') {
        $this->load->model('activity/activity_model');
        $details = get_detail_by_guid($MediaGUID, 21, 'OriginalName,ImageName,MediaSectionReferenceID,UserID', 2);
        //check if the result is not violating privacy of user        
        $loggedin_user = $this->session->userdata();
        $flag = TRUE;
        if (isset($loggedin_user['UserID'], $details['UserID'])) {
            if (isset($details['MediaSectionReferenceID']) && !empty($details['MediaSectionReferenceID'])) {
                $activity_details = get_detail_by_id($details['MediaSectionReferenceID'], 0, 'ActivityGUID,Privacy', 2);
            }
            $activity_guid = (isset($activity_details['ActivityGUID']) && $activity_details['ActivityGUID']) ? $activity_details['ActivityGUID'] : '0';
            $is_relation = $this->activity_model->isRelation($details['UserID'], $loggedin_user['UserID'], true, $activity_guid);
            if (!in_array($activity_details['Privacy'], $is_relation)) {
                $flag = FALSE;
            }
        }

        if ($details && $flag) {
            $url = IMAGE_SERVER_PATH . 'upload/' . $type . '/org_' . $details['ImageName'];
            set_time_limit(0);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $r = curl_exec($ch);
            curl_close($ch);
            header('Expires: 0'); // no cache
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
            header('Cache-Control: private', false);
            header('Content-Type: application/force-download');
            header('Content-Disposition: attachment; filename="' . basename($details['OriginalName']) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . strlen($r)); // provide file size
            header('Connection: close');
            echo $r;
        } else {

            $this->load->view('404-page');
        }
    }
    
    function set_search_content() {
        $this->load->model(array('activity/activity_front_helper_model'));            
        $this->activity_front_helper_model->set_search_contents();  
    }
    

    function mailTemplate() {
        $this->lang->load('notification');
        $Email = $this->uri->segment(3);
        $url = site_url('confirm/email') . '/253253563dsfggd';
        $FullName = "Suresh Patidar";
        if (empty($Email)) {
            $Email = "sureshp.vinfotech@gmail.com";
        }
        $Subject = "Thank you for Your Registration";
        //echo $Email;
        /* $Subject = "Thank you for Your Registration";
          $Template = THEAM_PATH . "email/emailer-registration.html"; // Custom email template
          $values = array("##FIRST_LAST_NAME##" => $FullName,"##URL##"=>$url);
          sendMail(EMAIL_NOREPLY_FROM, EMAIL_NOREPLY_NAME, $Template, $values, $Data['Email'], $Subject); */


        $emailDataArr['Data'] = array("FirstLastName" => $FullName, "Link" => $url, "Password" => 'asfsaf', "Email" => $Email, "Message" => 'dsgdsgdfg<br>dsgdsgdfg');

        $emailData = array("data" => $emailDataArr['Data']);
        $emailData['content_view'] = "emailer/registration";
        $layout = $this->config->item("email_layout");
        echo $email_html = $this->load->view($layout, $emailData, TRUE);

        //sendMail(EMAIL_NOREPLY_FROM, EMAIL_NOREPLY_NAME, $Email, $Subject, $email_html);
    }

    function check_browser() {
        $this->load->library('user_agent');
        $version = $this->agent->version();
        if ($this->agent->is_browser()) {
            $agent = $this->agent->browser();
        } elseif ($this->agent->is_robot()) {
            $agent = $this->agent->robot();
        } elseif ($this->agent->is_mobile()) {
            $agent = $this->agent->mobile();
        } else {
            $agent = 'Unidentified User Agent';
        }
        $browser = array("browser" => $agent, "version" => $version, "platform" => $this->agent->platform());
        /* echo "<pre>";
          echo $agent;
          echo "<br>";
          echo $this->agent->platform(); */ // Platform info (Windows, Linux, Mac, etc.) 
        echo json_encode($browser);
    }
   

    function recursive_group_member_test() { die;
        $this->load->model('group/group_model');
        $members = $this->group_model->get_group_members_id_recursive(194);
        print_r($members);
    }

    function time_diff() {

        $group_ids = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        if (!empty($group_ids)) {
            $first_record = $group_ids[0];
            unset($group_ids[0]);
            $inner_query = "select " . $first_record . " as GroupID";

            if (count($group_ids) > 0) {
                $sub_query = implode(' union all select ', $group_ids);
                $inner_query = $inner_query . " union all select " . $sub_query;
            }

            $sql .= "INNER JOIN (" . $inner_query . ") as X on G.GroupID = X.GroupID";
        }
        die;

        //echo $tim = date_default_timezone_get();
        //date_default_timezone_set('UTC');
        $t1 = strtotime("5:00 pm");
        $t2 = strtotime("9:35 am");

        echo $t = $t1 - $t2;

        $minutes = round(abs($t) / 60, 2);
        echo "<br>" . gmdate("H:i", ($minutes * 60)) . " hour";

        //echo "<br>".round(abs($t) / 3600,2). " hour";
        //$t =  $t * (60*60);
        //echo "<br>".date( "h:i", $t ); 
        //date_default_timezone_set($tim);
    }

    function activity($GuID = '') {

        $get_data = get_data('*', ACTIVITY, array('ActivityGUID' => $GuID), '1', '');
        $this->data['activity'] = $get_data;
        $this->data['title'] = 'Wall';
        $this->page_name = 'userprofile';
        $this->data['content_view'] = 'profile/demo_user_wall_new';
        $this->load->view($this->layout, $this->data);
    }

    function get_short_link() {
        $link = "http://google.com";
        echo get_short_link($link);
    }

    function save_news_feed_settings_for_all() {
        $this->load->model('privacy/privacy_model');

        $news_feed_settings = array(array("Key" => "e", "Value" => "0"), array("Key" => "es", "Value" => "0"), array("Key" => "g", "Value" => "0"), array("Key" => "gs", "Value" => "0"), array("Key" => "m", "Value" => "0"), array("Key" => "p", "Value" => "0"), array("Key" => "ps", "Value" => "0"), array("Key" => "r", "Value" => "0"), array("Key" => "rm", "Value" => "1"), array("Key" => "s", "Value" => "0"), array("Key" => "se", "Value" => "0"));

        $this->db->select('UserID');
        $this->db->from(USERS);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $user) {
                $this->privacy_model->save_news_feed_setting($user->UserID, $news_feed_settings);
            }
        }
    }

    function popular_profiles() {
        $query = "
            SELECT COUNT(Popularity) as Popularity,ModuleID,ModuleEntityID,ActivityDate FROM (
                SELECT UAL.ID as Popularity,
                    IF(UAL.ModuleID=19,
                        (SELECT PostAsModuleID FROM " . ACTIVITY . " WHERE ActivityID=UAL.ModuleEntityID)
                    ,UAL.ModuleID) as ModuleID,
                    IF(UAL.ModuleID=19,
                        (SELECT PostAsModuleEntityID FROM " . ACTIVITY . " WHERE ActivityID=UAL.ModuleEntityID)
                    ,UAL.ModuleEntityID) as ModuleEntityID,
                    ActivityDate 
                FROM " . USERSACTIVITYLOG . " UAL
            ) tbl
            WHERE ModuleID IN(3,18)
            AND DATE(ActivityDate)>='" . get_current_date('%Y-%m-%d', 7) . "'
            GROUP BY ModuleID,ModuleEntityID
            ORDER BY Popularity DESC
        ";
        $result = $this->db->query($query);
        if ($result->num_rows()) {
            print_r($result->result());
        }
    }

    /* public function popular_activity()
      {
      $this->load->model('activity/activity_model');
      $limit = 10;
      $offset = 0;
      $page_no = 1;
      $user_id = 224;
      $this->db->select("UAL.ModuleEntityID as ActivityID,COUNT(UAL.ID) as Popularity");
      $this->db->from(USERSACTIVITYLOG.' UAL');
      $this->db->join(ACTIVITY.' A','A.ActivityID=UAL.ModuleEntityID');
      $this->db->where('UAL.ModuleID','19');
      $this->db->where('A.Privacy','1');
      $this->db->where('A.StatusID','2');
      $this->db->where_in('A.ActivityTypeID',array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 23, 24, 25));
      $this->db->limit($limit,$offset);
      $this->db->group_by('UAL.ModuleEntityID');
      $query = $this->db->get();
      if($query->num_rows())
      {
      $activity_ids = array();
      $result = $query->result_array();
      foreach($result as $val)
      {
      $activity_ids[] = $val['ActivityID'];
      }
      $activities = $this->activity_model->getFeedActivities($user_id, $page_no, $limit, 'ActivityIDS',0,0,2,false,false,false,0,0,array(),'',array(),'','',array(),$activity_ids);
      print_r($activities);
      }
      } */

    public function short_url() {
        $url = "http://dev.vcommonsocial.com/abc5";
        echo getShortUrl($url);
    }

    public function long_url() {
        $url = "http://localhost/inclusify/r/MYZddJ";
        echo getLongUrl($url);
    }

    public function update_seen_count() {
        $this->db->select('EntityID,COUNT(EntityID) as Count,EntityType', false);
        $this->db->from(ENTITYVIEW);
        $this->db->where_in('EntityType', array('Activity', 'Album'));
        $this->db->group_by('EntityType');
        $this->db->group_by('EntityID');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $result) {
                if ($result['EntityType'] == 'Album') {
                    $this->db->select('ActivityID');
                    $this->db->from(ALBUMS);
                    $this->db->where('AlbumID', $result['EntityID']);
                    $qry = $this->db->get();
                    if ($qry->num_rows()) {
                        $result['EntityID'] = $qry->row()->ActivityID;
                    }
                }
                $this->db->set('NoOfViews', $result['Count']);
                $this->db->where('ActivityID', $result['EntityID']);
                $this->db->update(ACTIVITY);
            }
        }
    }

    public function friends_of_friends() {
        $this->load->model('users/friend_model');
        $this->friend_model->get_friends_of_friends(6218, 0, false);
    }

    public function get_group_permission() {
        $this->load->model('group/group_model');
        $data = $this->group_model->get_group_permission(5);
        print_r($data);
    }

    public function get_users_categories($user_id) {
        $this->load->model('forum/forum_model');
        $category_ids = $this->forum_model->get_users_categories($user_id);
        print_r($category_ids);
    }

    public function test_rule() {
        $this->load->model('activity/activity_model');
        $result = $this->activity_model->get_welcome_question(188, 16);
        var_dump($result);
        die;
    }

    public function configure_user_notification($user_id = 0, $notification_type_key = '') {
        //check notification key exists
        $this->db->select('NotificationTypeID');
        $this->db->where('NotificationTypeKey', $notification_type_key);
        $notification_type_exists = $this->db->get(NOTIFICATIONTYPES)->num_rows();
        if (!$notification_type_key || $notification_type_exists) {           
            if ($user_id && !$notification_type_key) {
                $this->load->model('notification_model');
                $this->notification_model->set_all_notification_on($user_id);
            } elseif ($user_id && $notification_type_key) {
                $this->db->select('UserID');
                $this->db->where('NotificationTypeKey', $notification_type_key);
                $this->db->where('UserID', $user_id);
                $result = $this->db->get(USERNOTIFICATIONSETTINGS)->num_rows();
                if (!$result) {
                    $notification_settings_query = array('UserID' => $user_id, 'NotificationTypeKey' => $notification_type_key, 'Email' => '1', 'Mobile' => '1', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                    $this->db->insert(USERNOTIFICATIONSETTINGS, $notification_settings_query);
                    if (CACHE_ENABLE) {
                        $this->cache->delete('user_notification_setting' . $user_id);
                    }
                }
            } elseif ($notification_type_key && !$user_id) {   
                set_time_limit(0);
                $this->db->select('UserID');
                $this->db->where_not_in('StatusID', array(3, 4));
                $result = $this->db->get(USERS)->result_array();
                $count = 0;
                $updated_ids = array();
                foreach ($result as $value) {
                    $this->db->select('UserID');
                    $this->db->where('NotificationTypeKey', $notification_type_key);
                    $this->db->where('UserID', $value['UserID']);
                    $result = $this->db->get(USERNOTIFICATIONSETTINGS)->num_rows();

                    if (!$result) {
                        $notification_settings_query = array('UserID' => $value['UserID'], 'NotificationTypeKey' => $notification_type_key, 'Email' => '1', 'Mobile' => '1', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                        $this->db->insert(USERNOTIFICATIONSETTINGS, $notification_settings_query);
                        $count++;
                        $updated_ids[] = $value['UserID'];
                        if (CACHE_ENABLE) {
                            $this->cache->delete('user_notification_setting' . $value['UserID']);
                        }
                    }
                }
                echo 'Records Updated:' . $count . '<br>';
                echo '<pre>';
                print_r($updated_ids);
                die;
            } else {
                $this->db->select('GROUP_CONCAT(UserID) as UserID');
                $this->db->where_not_in('StatusID', array(3, 4));
                $this->db->where('UserID<', 31);
                $result = $this->db->get(USERS)->row_array();
                $result = explode(',', $result['UserID']);
                foreach ($result as $value) {
                    $this->notification_model->set_all_notification_on($value);
                }
            }
        }
        /* $this->db->where_in('NotificationTypeID',array(140));
          $query = $this->db->get('NotificationTypes');
          var_dump($query->result_array());die; */
    }    
}
