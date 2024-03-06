<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cron_model extends Common_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('users/friend_model');
        $this->load->model('notification_model');
    }

    function get_already_mailed_user($email_type, $user_id = 0) { //return array();
        //SELECT * FROM (`Communications` C) LEFT JOIN `Users` U ON `U`.`UserID`=`C`.`UserID` WHERE DATE(C.CreatedDate)='15-12-23' AND  concat(' ', replace(C.FromUserID, ',', ' '), ' ') like concat('% ', '5', ' %') OR concat(' ', replace(C.FromUserID, ',', ' '), ' ') like concat('% ', '15', ' %')
        $this->db->select('IFNULL(GROUP_CONCAT(C.FromUserID),0) as UserIDs', false);
        $this->db->select('IFNULL(GROUP_CONCAT(C.UserID),0) as ToUserIDs', false);
        $this->db->from(COMMUNICATIONS . ' C');
        $this->db->join(USERS . ' U', 'U.UserID=C.UserID', 'left');
        $this->db->where("DATE(C.CreatedDate)='" . get_current_date('%y-%m-%d') . "'");
        $this->db->where('C.EmailTypeID', $email_type);
        $query = $this->db->get();
        $arr = array('FromUserIDs' => 0, 'ToUserIDs' => 0);
        if ($query->num_rows()) {
            $user_ids = $query->row()->UserIDs;
            $ToUserIDs = $query->row()->ToUserIDs;
            if ($user_ids) {
                $arr['FromUserIDs'] = explode(',', $user_ids);
            }
            if ($ToUserIDs) {
                $arr['ToUserIDs'] = explode(',', $ToUserIDs);
            }
        }
        return $arr;
    }

    /* function get_already_reminder()
      {
      $this->db->select('ToUserID');
      $this->db->select('IFNULL(GROUP_CONCAT(BirthdayUserID),0) as ToUserIDs')
      } */

    function check_contest_status()
    {
        $this->load->model('contest/contest_model');

        $this->db->select('A.ActivityID');
        $this->db->from(ACTIVITY.' A');
        $this->db->where('A.ActivityTypeID','37');
        $this->db->where('A.IsWinnerAnnounced','0');
        $this->db->where("A.ContestEndDate<'".get_current_date('%Y-%m-%d %H:%i:%s')."'");
        $query = $this->db->get();
        if($query->num_rows())
        {
            foreach($query->result_array() as $contest)
            {
                $this->contest_model->update_contest($contest['ActivityID']);
            }
        }
    }

    function birthday_notification() {
        $to_date = get_current_date('%Y-%m-%d', 7, 1);
        $last_login = get_current_date('%Y-%m-%d', 7);
        $from_date = get_current_date('%Y-%m-%d');
        $from_date_time = get_current_date('%Y-%m-%d %h:%i:%s');

        $email_data = array();

        $already_sent = $this->get_already_mailed_user(25);

        //SELECT `U`.`FirstName`, `U`.`LastName`, `U`.`UserGUID`, `U`.`ProfilePicture`, `UD`.`DOB`, IF(DATE_FORMAT(UD.DOB, '%m-%d')=DATE_FORMAT(CONVERT_TZ('2015-12-23 09-51-15', 'Etc/UTC', (SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID)), '%m-%d'), 'Today', DATE_FORMAT(UD.DOB, '%m-%d')) as BirthDate, `P`.`Url` as ProfileURL FROM (`Users` U) LEFT JOIN `UserDetails` UD ON `U`.`UserID`=`UD`.`UserID` LEFT JOIN `Friends` F ON `U`.`UserID`=`F`.`UserID` LEFT JOIN `ProfileUrl` as P ON `P`.`EntityID` = `U`.`UserID` and P.EntityType = 'User' WHERE `F`.`Status` = '1' AND `F`.`FriendID` = '1' AND DATE_FORMAT(UD.DOB,'%m-%d') BETWEEN DATE_FORMAT(CONVERT_TZ('2015-12-23 09-51-15','Etc/UTC',(SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID)),'%m-%d') AND DATE_FORMAT(CONVERT_TZ('2015-12-30','Etc/UTC',(SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID)),'%m-%d') AND `U`.`StatusID` = '2' ORDER BY BirthDate='Today' DESC
        //SELECT U.UserID, (SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID) as TimeZone, CONVERT_TZ(NOW(),'Etc/UTC',(SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID)) as CurrentTime FROM Users U LEFT JOIN UserDetails UD ON UD.UserID=U.UserID WHERE U.UserID IN(1,2,3)
        $this->db->select('FR.UserID,FR.FriendID, U.FirstName, U.LastName, U.Email, U.UserGUID');
        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);
        $this->db->from(FRIENDS . ' FR');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=FR.FriendID', 'left');
        $this->db->join(FRIENDS . ' FR2', ' FR2.UserID=UD.UserID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=FR.UserID', 'left');
        $this->db->where("DATE_FORMAT(UD.DOB,'%m-%d')=DATE_FORMAT(CONVERT_TZ('" . $from_date_time . "','Etc/UTC',(SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID)),'%m-%d')", NULL, FALSE);
        $this->db->where('FR.Status', '1');
        $this->db->where('FR2.Status', '1');
        /* if($already_sent)
          {
          $this->db->where_not_in('FR.FriendID',$already_sent['FromUserIDs']);
          $this->db->where_not_in('FR.UserID',$already_sent['ToUserIDs']);
          } */
        $this->db->where("FR.UserID NOT IN (SELECT ToUserID FROM BirthdayReminderSent WHERE BirthdayUserID=FR.FriendID AND DOB='" . get_current_date('%Y-%m-%d') . "')", null, false);
        $this->db->limit(30);
        $this->db->group_by('FR.UserID');
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if ($query->num_rows()) {
            foreach ($query->result_array() as $result) {
                $email_data = array('Today' => array(), 'OtherDate' => array(), 'FromUserID' => array());

                $this->db->select('U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,UD.DOB');
                $this->db->select('U.UserID as FromUserID', false);
                $this->db->select("IF(DATE_FORMAT(UD.DOB,'%m-%d')=DATE_FORMAT(CONVERT_TZ('" . $from_date_time . "','Etc/UTC',(SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID)),'%m-%d'),'Today',DATE_FORMAT(UD.DOB,'%m-%d')) as BirthDate", false);
                $this->db->select('P.Url as ProfileURL');
                $this->db->from(USERS . ' U');
                $this->db->join(USERDETAILS . ' UD', 'U.UserID=UD.UserID', 'left');
                $this->db->join(FRIENDS . ' F', 'U.UserID=F.UserID', 'left');
                $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
                $this->db->where('F.Status', '1');
                $this->db->where('F.FriendID', $result['UserID']);
                $this->db->where("DATE_FORMAT(UD.DOB,'%m-%d') BETWEEN DATE_FORMAT(DATE_FORMAT(CONVERT_TZ('" . $from_date_time . "','Etc/UTC',(SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID)),'%y-%m-%d'),'%m-%d') AND DATE_FORMAT(DATE_FORMAT(CONVERT_TZ('" . $to_date . "','Etc/UTC',(SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID)),'%y-%m-%d'),'%m-%d')", NULL, FALSE);
                $this->db->where('U.StatusID', '2');
                $this->db->where("UD.UserID NOT IN (SELECT BirthdayUserID FROM BirthdayReminderSent WHERE ToUserID='" . $result['UserID'] . "' AND DOB='" . get_current_date('%Y-%m-%d') . "')", null, false);
                $this->db->_protect_identifiers = FALSE;
                $this->db->order_by("BirthDate='Today'", 'DESC');
                $this->db->_protect_identifiers = TRUE;
                $friends_query = $this->db->get();
                /* echo $friends_query->num_rows();
                  echo '<br>';
                  echo $this->db->last_query();
                  echo '<br><br>'; */
                $num_rows = $friends_query->num_rows();
                if ($num_rows) {
                    $BirthdaySubjectMessgae = 'Send birthday wishes to ';
                    $i = 1;
                    foreach ($friends_query->result_array() as $friend) {
                        $email_data['FromUserID'][] = $friend['FromUserID'];
                        if ($friend['BirthDate'] == 'Today') {
                            $email_data['Today'][] = $friend;
                        } else {
                            $friend['BirthDate'] = date('M, d', strtotime($friend['DOB']));
                            $email_data['OtherDate'][] = $friend;
                        }
                        $i++;
                    }
                    $i = 1;
                    $num_rows = count($email_data['Today']);
                    if ($email_data['Today']) {
                        foreach ($email_data['Today'] as $frnd) {
                            if ($num_rows == 1 && $i == 1) {
                                $BirthdaySubjectMessgae .= $frnd['FirstName'] . ' ' . $frnd['LastName'] . '.';
                            }
                            if ($num_rows == 2 && $i == 1) {
                                $BirthdaySubjectMessgae .= $frnd['FirstName'] . ' ' . $frnd['LastName'] . ' and ';
                            }
                            if ($num_rows == 2 && $i == 2) {
                                $BirthdaySubjectMessgae .= $frnd['FirstName'] . ' ' . $frnd['LastName'] . '.';
                            }
                            if ($num_rows > 2 && $i == 1) {
                                $BirthdaySubjectMessgae .= $frnd['FirstName'] . ' ' . $frnd['LastName'] . ', ';
                            }
                            if ($num_rows > 2 && $i == 2) {
                                $BirthdaySubjectMessgae .= $frnd['FirstName'] . ' ' . $frnd['LastName'] . ' and ' . ($num_rows - 2) . ' others.';
                            }
                            $i++;
                        }
                    }
                }
                if ($email_data['FromUserID']) {
                    $email_data['FromUserID'] = implode(',', $email_data['FromUserID']);
                } else {
                    $email_data['FromUserID'] = 0;
                }
                if ($email_data['Today']) {
                    foreach ($email_data['Today'] as $today_friend) {
                        $this->db->insert('BirthdayReminderSent', array('BirthdayUserID' => $today_friend['FromUserID'], 'ToUserID' => $result['UserID'], 'DOB' => get_current_date('%Y-%m-%d')));
                    }

                    if ($email_data['Today'] || $email_data['OtherDate']) {
                        $to_user_detail = array('FirstName' => $result['FirstName'], 'LastName' => $result['LastName'], 'Email' => $result['Email'], 'UserGUID' => $result['UserGUID'], 'UserID' => $result['UserID'], 'ProfilePicture' => $result['ProfilePicture']);
                        $to_user_detail['FullName'] = $to_user_detail['FirstName'] . ' ' . $to_user_detail['LastName'];

                        $this->notification_model->send_email_notification(array('UserID' => '0', 'FromUserDetails' => array(), 'ToUserID' => $result['UserID'], 'ToUserDetails' => $to_user_detail, 'RefrenceID' => '0', 'NotificationTypeID' => '80', 'FriendsData' => $email_data, 'Subject' => $BirthdaySubjectMessgae));
                    }
                }
            }
        }
    }

    /*
     * Function to retrieve job id and update video length via zencoder api 
     * @param  :
     * @Output : Boolean
     */

    function get_job_detail_and_update_duration() {
        /* Retrieve video details from media table where duration is not set */
        $this->db->select('JobID,MediaID,ImageName');
        $this->db->from(MEDIA . ' AS M');
        $this->db->JOIN(MEDIAEXTENSIONS . ' AS ME', 'M.MediaExtensionID=ME.MediaExtensionID');
        $this->db->JOIN(MEDIATYPES . ' AS MT', 'MT.MediaTypeID=ME.MediaTypeID');
        $this->db->where('MT.MediaTypeID', 2);
        $this->db->where('M.ConversionStatus', 'Pending');
        $video_arr = $this->db->get()->result_array();
        if (!empty($video_arr)) {
            foreach ($video_arr as $key => $video) {
                /* Execute curl for zencoder api to retrieve details of specific job */
                $job_detail_url = "https://app.zencoder.com/api/v2/jobs/" . $video['JobID'] . ".json?api_key=" . ZENCODER_API_KEY;
                $resp = curl($job_detail_url);
                if (is_string($resp)) {
                    $resp = json_decode($resp, TRUE);
                    if (!empty($resp['job'])) {
                        $VideoLength = !empty($resp['job']['output_media_files'][0]['duration_in_ms']) ? $resp['job']['output_media_files'][0]['duration_in_ms'] : 0;
                        $state = !empty($resp['job']['state']) ? ucfirst($resp['job']['state']) : '';
                        if (!empty($VideoLength) && $state == 'Finished') {

                            $ext_arr = array('mp4', 'webm', 'ogg');
                            $extension = explode('.', $video['ImageName']);
                            $extension = end($extension);
                            $totalfilesize = 0;
                            if (!in_array($extension, $ext_arr)) {
                                $ext_arr[] = $extension;
                            }
                            $basefilename = basename($video['ImageName'], '.' . $extension);
                            foreach ($ext_arr as $e_err) {
                                $filesize = $this->get_remote_file_size(IMAGE_SERVER_PATH . 'upload/messages/' . $basefilename . '.' . $e_err);
                                if ($filesize) {
                                    $totalfilesize = $totalfilesize + $filesize;
                                }
                            }

                            /* update media with video Length while having valid value from zencoder api */
                            $this->db->where('JobID', $video['JobID']);
                            $this->db->where('MediaID', $video['MediaID']);
                            $this->db->update(MEDIA, array('VideoLength' => $VideoLength, 'ConversionStatus' => $state, 'Size' => $totalfilesize));
                        }
                    }
                }
            }
        }
        return TRUE;
    }

    function upcoming_event_notification() {
        $starting_date = get_current_date('%Y-%m-%d', 2, 1);
        $current_date = get_current_date('%Y-%m-%d');

        $this->load->model('events/event_model');


        $this->db->select('U.FirstName, U.LastName, U.UserGUID, U.Email, U.UserID');
        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);
        $this->db->select('P.Url as ProfileURL');
        $this->db->where("DATE(CONCAT(E.StartDate,' ',E.StartTime)) BETWEEN '" . $current_date . "' AND '" . $starting_date . "'", NULL, FALSE);
        $this->db->from(EVENTS . ' E');
        $this->db->join(EVENTUSERS . ' EU', 'E.EventID=EU.EventID', 'left');
        $this->db->join(USERS . " as U", "U.UserID = EU.UserID", "LEFT");
        $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
        $this->db->where('EU.Presence', 'ATTENDING');
        $this->db->group_by('EU.UserID');
        $user_query = $this->db->get();

        if ($user_query->num_rows()) {
            foreach ($user_query->result_array() as $user) {
                $this->db->select('E.*');
                $this->db->from(EVENTS . ' E');
                $this->db->join(EVENTUSERS . ' EU', 'E.EventID=EU.EventID', 'left');
                $this->db->where('EU.UserID', $user['UserID']);
                $this->db->where('EU.Presence', 'ATTENDING');
                $this->db->where("DATE(CONCAT(E.StartDate,' ',E.StartTime)) BETWEEN '" . $current_date . "' AND '" . $starting_date . "'", NULL, FALSE);
                $event_query = $this->db->get();
                if ($event_query->num_rows()) {
                    $d = array();
                    $this->load->helper('location');
                    foreach ($event_query->result_array() as $event) {
                        $event['Location'] = get_location_by_id($event['LocationID']);
                        $event['ProfilePicture'] = $this->event_model->get_event_profile_picture($event['ProfileImageID']);
                        
                        $event_url = $this->event_model->getViewEventUrl($event['EventGUID'], $event['Title'], false, 'wall');
                        
                        $this->db->select('U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture');
                        $this->db->select('P.Url as ProfileURL');
                        $this->db->from(USERS . ' U');
                        $this->db->join(EVENTUSERS . ' EU', 'EU.UserID=U.UserID', 'left');
                        $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
                        $this->db->join(FRIENDS . ' F', 'F.UserID=U.UserID', 'left');
                        $this->db->where('F.FriendID', $user['UserID']);
                        $this->db->where('F.Status', '1');
                        $this->db->where('EU.EventID', $event['EventID']);
                        $this->db->where('EU.Presence', 'ATTENDING');
                        $friend_query = $this->db->get();

                        $event['Tagline'] = '';
                        $num_rows = $friend_query->num_rows();
                        $i = 1;
                        if ($num_rows) {
                            foreach ($friend_query->result_array() as $friend) {
                                if ($num_rows == 1 && $i == 1) {
                                    $event['Tagline'] .= '<a href="' . site_url() . '/' . $friend['ProfileURL'] . '">' . $friend['FirstName'] . '</a> is also attending <a href="' . site_url($event_url) . '">' . $event['Title'] . '</a> you are attending.';
                                }
                                if ($num_rows == 2 && $i == 1) {
                                    $event['Tagline'] .= '<a href="' . site_url() . '/' . $friend['ProfileURL'] . '">' . $friend['FirstName'] . '</a> and ';
                                }
                                if ($num_rows == 2 && $i == 2) {
                                    $event['Tagline'] .= '<a href="' . site_url() . '/' . $friend['ProfileURL'] . '">' . $friend['FirstName'] . '</a> are also attending <a href="' . site_url($event_url) . '">' . $event['Title'] . '</a> you are attending.';
                                }
                                if ($num_rows > 2 && $i == 1) {
                                    $event['Tagline'] .= '<a href="' . site_url() . '/' . $friend['ProfileURL'] . '">' . $friend['FirstName'] . '</a>,';
                                }
                                if ($num_rows > 2 && $i == 2) {
                                    $event['Tagline'] .= '<a href="' . site_url() . '/' . $friend['ProfileURL'] . '">' . $friend['FirstName'] . '</a> and ' . ($num_rows - 2) . ' others are also attending <a href="' . site_url($event_url) . '">' . $event['Title'] . '</a> you are attending.';
                                }
                                $i++;
                            }
                        }
                        $d[] = $event;
                    }
                }
                //Send Mail Start
                $Subject = 'Upcoming Events';
                if (count($d) == 1) {
                    $Subject = 'Upcoming Event';
                }
                /* if($user['UserID'] == 5)
                  { */
                $to_user_detail = array('FirstName' => $user['FirstName'], 'LastName' => $user['LastName'], 'Email' => $user['Email'], 'UserGUID' => $user['UserGUID'], 'UserID' => $user['UserID'], 'ProfilePicture' => $user['ProfilePicture']);
                $to_user_detail['FullName'] = $to_user_detail['FirstName'] . ' ' . $to_user_detail['LastName'];

                $this->notification_model->send_email_notification(array('UserID' => '0', 'FromUserDetails' => array(), 'ToUserID' => $user['UserID'], 'ToUserDetails' => $to_user_detail, 'RefrenceID' => '0', 'NotificationTypeID' => '79', 'EventData' => $d, 'Subject' => $Subject));
                /* } */
                //Send Mail Ends
            }
        }
    }

    function daily_digest_notification() {
        $this->load->model('activity/activity_model');

        $this->db->select('U.UserID, U.FirstName, U.LastName, U.Email, U.UserGUID');
        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);

        $this->db->from(USERS . ' U');

        //$this->db->where('U.UserID','5');
        $this->db->where('U.StatusID', '2');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $user) {
                if ($this->is_inactive($user['UserID'])) {
                    $activities = $this->activity_model->getFeedActivities($user['UserID'], 1, 2, 'popular');
                    if ($activities) {
                        /* if($user['UserID'] == 5)
                          { */
                        $to_user_detail = array('FirstName' => $user['FirstName'], 'LastName' => $user['LastName'], 'Email' => $user['Email'], 'UserGUID' => $user['UserGUID'], 'UserID' => $user['UserID'], 'ProfilePicture' => $user['ProfilePicture']);
                        $to_user_detail['FullName'] = $to_user_detail['FirstName'] . ' ' . $to_user_detail['LastName'];

                        $this->notification_model->send_email_notification(array('UserID' => '0', 'FromUserDetails' => array(), 'ToUserID' => $user['UserID'], 'ToUserDetails' => $to_user_detail, 'RefrenceID' => '0', 'NotificationTypeID' => '81', 'ActivityData' => $activities));
                        /* } */
                    }
                }
            }
        }
    }

    function is_inactive($user_id) {
        $this->db->select('ActiveLoginID');
        $this->db->from(ACTIVELOGINS);
        $this->db->where('UserID', $user_id);
        $this->db->where("CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d', 7) . "' AND '" . get_current_date('%Y-%m-%d') . "'");
        $query = $this->db->get();
        if ($query->num_rows()) {
            return false;
        } else {
            return true;
        }
    }

    function _group_by($array, $key) {
        $return = array();
        foreach ($array as $val) {
            $return[$val[$key]][] = $val;
        }
        return $return;
    }

    function calculate_weightage() {
        //Calculate weightage

        $this->db->select('ActivityID,ModifiedDate');
        $this->db->from(ACTIVITY);
        $this->db->where('(ModifiedDate!=WeightTime OR WeightTime is null)', null, false);
        $this->db->limit(30);
        $query = $this->db->get();

        if ($query->num_rows()) {
            foreach ($query->result() as $activity) {
                $view_weight = 0;
                $comment_weight = 0;

                $this->db->select("COUNT(DISTINCT(UserID)) as CommentWeight", false);
                $this->db->from(POSTCOMMENTS);
                $this->db->where('EntityType', 'Activity');
                $this->db->where('EntityID', $activity->ActivityID);
                $comment_query = $this->db->get();
                if ($comment_query->num_rows()) {
                    $comment_weight = $comment_query->row()->CommentWeight;
                }

                $this->db->select("COUNT(EntityViewID) as ViewWeight", false);
                $this->db->from(ENTITYVIEW);
                $this->db->where('EntityType', 'Activity');
                $this->db->where('EntityID', $activity->ActivityID);
                $view_query = $this->db->get();
                if ($view_query->num_rows()) {
                    $view_weight = $view_query->row()->ViewWeight;
                }

                $total_weight = $comment_weight + $view_weight;

                $this->db->set('Weight', $total_weight);
                $this->db->set('WeightTime', $activity->ModifiedDate);
                $this->db->where('ActivityID', $activity->ActivityID);
                $this->db->update(ACTIVITY);
            }
        }
    }

    function check_activity_visibility($activity_guid, $env) {
        $users = array();
        $this->db->select('UserID,ModuleID,ModuleEntityID,ActivityTypeID');
        $this->db->from(ACTIVITY);
        $this->db->where('ActivityGUID', $activity_guid);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row_array();
            if ($row['ModuleID'] == '1') {
                $this->load->model('group/group_model');
                $users = $this->group_model->get_group_members_id_recursive($row['ModuleEntityID']);
            } else if ($row['ModuleID'] == '3') {
                $this->load->model('users/friend_model');
                $users = $this->friend_model->getFriendIDS($row['ModuleEntityID']);
            } else if ($row['ModuleID'] == '14') {
                $this->load->model('events/event_model');
                $users = $this->event_model->members_id($row['ModuleEntityID']);
            } else if ($row['ModuleID'] == '18') {
                $this->load->model('pages/page_model');
                $users = $this->page_model->get_page_members_id($row['ModuleEntityID']);
            }
        }
        if ($users) {
            foreach ($users as $user) {
                $user_guid = get_detail_by_id($user, 3, 'UserGUID', 1);
                if ($user_guid) {
                    $this->calculate_rank($user_guid, $env);
                }
            }
        }
    }

    function calculate_rank($user_guid, $env, $forcely_calculate = 0) {
        $user_id = get_detail_by_guid($user_guid, 3, 'UserID', 1);
        //$user_guid = '57a6eda8-178d-b86a-f71b-0074de328dd5';
        //echo $user_id."<br>";
        $day_diff = 1;

        $this->db->select('Rank');
        $this->db->from(USERACTIVITYRANK);
        $this->db->where('UserID', $user_id);
        $this->db->where("DATE(CreatedTime)='" . get_current_date('%Y-%m-%d') . "'", null, false);
        $query = $this->db->get();
        if ($query->num_rows() && !$forcely_calculate) {
            return;
        }

        $this->db->select('SessionLogID');
        $this->db->from(SESSIONLOGS);
        $this->db->where("DATE(StartDate)='" . get_current_date('%Y-%m-%d') . "'", NULL, FALSE);
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        if (!$query->num_rows()) {
            $this->db->select("DATE(StartDate) as StartDate", false);
            $this->db->from(SESSIONLOGS);
            $this->db->where('UserID', $user_id);
            $this->db->order_by('StartDate', 'DESC');
            $this->db->limit(3);
            $query = $this->db->get();
            $num_rows = $query->num_rows();
            if ($num_rows) {
                foreach ($query->result_array() as $arr) {
                    $diff = date_diff(date_create($arr['StartDate']), date_create(get_current_date('%Y-%m-%d')));
                    $day_diff = $diff->format("%a");
                    if ($day_diff < 1) {
                        $day_diff = 1;
                    }
                }
            }
        }

        $start_date = get_current_date('%Y-%m-%d', 1, 0);
        $end_date = get_current_date('%Y-%m-%d');

        $friend_followers_list = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $privacy_options = $this->privacy_model->news_feed_setting_details($user_id);
        $friends = $friend_followers_list['Friends'];
        $follow = $friend_followers_list['Follow'];
        $friends[] = 0;
        $follow[] = 0;
        $friend_followers_list = array_filter(array_unique(array_merge($friends, $follow)));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list)) {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list)) {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers) {
                $only_friend_followers[] = 0;
            }
        }
        $friend_followers_list = implode(',', array_filter($friend_followers_list));
        $group_list = $this->group_model->get_joined_groups($user_id, false, array(2));
        $event_list = $this->event_model->get_all_joined_events($user_id);
        $page_list = $this->page_model->get_liked_pages_list($user_id);

        $condition = "";
        $condition_part_one = "";
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = "";
        $condition_part_four = "";

        if ($friend_followers_list != '') {
            $condition = "(
                IF(A.ActivityTypeID=1 OR A.ActivityTypeID=5 OR A.ActivityTypeID=6, (
                    A.UserID IN(" . $friend_followers_list . ") OR A.ModuleEntityID IN(" . $friend_followers_list . ") OR " . $condition_part_two . "
                ), '' )
                OR
                IF(A.ActivityTypeID=2, (
                    (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')
                ), '' )
                OR
                IF(A.ActivityTypeID=3, (
                    A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'
                ), '' )
                OR            
                IF(A.ActivityTypeID=9 OR A.ActivityTypeID=10 OR A.ActivityTypeID=14 OR A.ActivityTypeID=15, (
                    (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "
                ), '' )
                OR
                IF(A.ActivityTypeID=8, (
                    A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'
                ), '' )";

            if ($friends) {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow) {
                $privacy_cond2 = "IF(A.Privacy='3',
                    A.UserID IN (" . implode(',', $follow) . "), true
                )";
            }
        }

        if (!empty($group_list)) {
            $condition_part_one = $condition_part_one . "IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7, (
                        A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ")
                    ), '' )";
        }
        if (!empty($page_list)) {
            $condition_part_three = $condition_part_three . "IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17, (
                        A.ModuleID=18 AND A.ModuleEntityID IN(" . $page_list . ")
                    ), '' )";
        }
        if (!empty($event_list)) {
            $condition_part_four = $condition_part_four . "IF(A.ActivityTypeID=11, (
                        A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")
                    ), '' )";
        }
        if (!empty($condition)) {
            if (!empty($condition_part_one)) {
                $condition = $condition . " OR " . $condition_part_one;
            }
            if (!empty($condition_part_three)) {
                $condition = $condition . " OR " . $condition_part_three;
            }
            if (!empty($condition_part_four)) {
                $condition = $condition . " OR " . $condition_part_four;
            }
            $condition = $condition . ")";
        } else {

            if (!empty($condition_part_one)) {
                $condition = $condition_part_one;
            }
            if (!empty($condition_part_three)) {
                if (empty($condition)) {
                    $condition = $condition_part_three;
                } else {
                    $condition = $condition . " OR " . $condition_part_three;
                }
            }

            if (empty($condition)) {
                $condition = $condition_part_two;
            } else {
                //$condition = $condition_part_two. " OR ".$condition_part_one; 
                $condition = "(" . $condition . ")";
            }
        }

        $this->db->select('A.ActivityID,A.ModuleID,A.ModuleEntityID');
        $this->db->select('IFNULL(RS.Score,0) as RelationshipScore', false);
        $this->db->select('IFNULL(A.Weight,0) as Weight', false);
        $this->db->select('IFNULL(A.ModifiedDate,A.CreatedDate) as RecentDate', false);
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->join(MODULES . ' M1', 'A.ModuleID=M1.ModuleID', 'left');
        $this->db->join(MODULES . ' M2', 'ATY.ModuleID=M2.ModuleID', 'left');
        $this->db->join(RELATIONSHIPSCORE . ' RS', 'RS.ModuleID=A.ModuleID AND RS.ModuleEntityID=A.ModuleEntityID AND RS.UserID="' . $user_id . '"', 'left');

        $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);

        $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);

        $this->db->where('M1.IsActive', '1');
        $this->db->where('M2.IsActive', '1');
        $this->db->where('ATY.StatusID', '2');

        if (!empty($condition)) {
            $this->db->where($condition, NULL, FALSE);
        } else {
            $this->db->where('A.ModuleID', '3');
            $this->db->where('A.ModuleEntityID', $user_id);
        }

        if ($start_date) {
            $this->db->where("DATE_FORMAT(A.CreatedDate,'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date) {
            $this->db->where("DATE_FORMAT(A.CreatedDate,'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        $this->db->where_not_in('U.StatusID', array(3, 4));

        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $recency = array();
            $weight = array();
            $score = array();
            $activities = array();
            foreach ($query->result_array() as $result) {
                $score[$result['ActivityID']] = $result['RelationshipScore'];
                $weight[$result['ActivityID']] = $result['Weight'];
                $recency[$result['ActivityID']] = strtotime($result['RecentDate']);
                $activities[] = $result['ActivityID'];
            }

            $score = percentile($score);
            $weight = percentile($weight);
            $recency = percentile($recency);

            $rank = array();
            foreach ($activities as $activity) {
                $rank[$activity] = ($score[$activity] * 5) + ($weight[$activity] * 3) + ($recency[$activity] * 2);
            }

            arsort($rank);

            $this->db->where('UserID', $user_id);
            $this->db->delete(USERACTIVITYRANK);

            $i = 1;
            $insert_data = array();
            foreach ($rank as $key => $value) {
                $insert_data[] = array('UserID' => $user_id, 'ActivityID' => $key, 'Rank' => $i, 'CreatedTime' => time(), 'Status' => 'ACTIVE');
                $i++;
            }
            $this->db->insert_batch(USERACTIVITYRANK, $insert_data);
        }
    }

    function calculate_rank_by_activity($activity_id, $env) {
        $activity_details = get_detail_by_id($activity_id, 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
        $users = array();
        switch ($activity_details['ModuleID']) {
            case '1':
                $this->load->model('group/group_model');
                $users = $this->group_model->get_group_members_id_recursive($activity_details['ModuleEntityID']);
                break;
            case '3':
                $this->load->model('users/friend_model');
                $users = $this->friend_model->getFriendIDS($activity_details['ModuleEntityID']);
                break;
            case '14':
                $this->load->model('events/event_model');
                $users = $this->page_model->get_all_members_id($activity_details['ModuleEntityID']);
                break;
            case '18':
                $this->load->model('pages/page_model');
                $users = $this->page_model->get_page_members_id($activity_details['ModuleEntityID']);
                break;
        }

        if ($users) {
            foreach ($users as $user) {
                $user_guid = get_detail_by_id($user, 3, 'UserGUID', 1);
                $this->calculate_rank($user_guid, $env, 1);
            }
        }
    }

    function check_activities() {

        /* $this->db->select('AL.UserID');
          $this->db->select('@TimeDiff := ROUND(IFNULL(TIMESTAMPDIFF(SECOND, MIN(AL.CreatedDate), MAX(AL.CreatedDate)) / NULLIF(COUNT(*) - 1, 0), 0)) as TimeDiff',false);
          $this->db->from(ACTIVELOGINS.' AL');
          $this->db->join(USERS.' U','U.UserID=AL.UserID','left');
          $this->db->where_in('U.StatusID',array(1,2));
          $this->db->where('@TimeDiff>0 AND @TimeDiff<86401',null,false);
          $this->db->group_by('AL.UserID');
          $query = $this->db->get(); */

        $sql = "SELECT * FROM (SELECT AL.UserID, Round(IFNULL(TIMESTAMPDIFF(SECOND, Min(AL.CreatedDate), Max(AL.CreatedDate)) / NULLIF(COUNT(*) - 1, 0), 0)) AS TimeDiff FROM " . ACTIVELOGINS . " AL LEFT JOIN " . USERS . " U ON U.UserID = AL.UserID WHERE  U.StatusID NOT IN (3,4) GROUP  BY UserID) tbl WHERE  TimeDiff > 0 AND TimeDiff < 86401";

        $query = $this->db->query($sql);

        //echo $this->db->last_query(); die;
        if ($query->num_rows()) {
            foreach ($query->result_array() as $user_list) {
                $rank = 0;
                $activities = $this->activity_model->getActivitiesForCron($user_list['UserID'], 3);
                if ($activities) {
                    $insert_data = array();
                    $activity_list = array();
                    foreach ($activities as $activity) {
                        $activity_id = $activity['ActivityID'];
                        $activity_list[] = $activity_id;
                        $this->db->select('Status,UserID,ActivityID');
                        $this->db->from(USERACTIVITYRANK);
                        $this->db->where('UserID', $user_list['UserID']);
                        $this->db->where('ActivityID', $activity_id);
                        $query = $this->db->get();
                        if ($query->num_rows()) {
                            $Status = $query->row()->Status;
                            if ($Status == 'ACTIVE') {
                                $this->db->set('Status', 'PENDING');
                                $this->db->where('UserID', $user_list['UserID']);
                                $this->db->where('ActivityID', $activity_id);
                                $this->db->update(USERACTIVITYRANK);
                            }
                        } else {
                            $insert_data[] = array('ActivityID' => $activity_id, 'UserID' => $user_list['UserID'], 'Rank' => 0, 'CreatedTime' => time(), 'Status' => 'PENDING');
                        }
                    }
                    if ($activity_list) {
                        $this->db->where('UserID', $user_list['UserID']);
                        $this->db->where_not_in('ActivityID', $activity_list);
                        $this->db->delete(USERACTIVITYRANK);
                    }
                    if ($insert_data) {
                        $this->db->insert_batch(USERACTIVITYRANK, $insert_data);
                    }
                }
            }
        }
    }

    function current_reminder() {
        $created_date = get_current_date('%Y-%m-%d %H:%i:00');
        $this->db->where('ReminderDateTime <=', $created_date);
        $this->db->where('Status', 'ARCHIVED');
        $this->db->update(REMINDER, array('Status' => 'ACTIVE', 'ModifiedDate' => $created_date));

        //echo $this->db->last_query(); die;

        $created_date = get_current_date('%Y-%m-%d %H:%i');

        $this->db->select("A.ActivityGUID, U.UserGUID, R.ReminderGUID");
        $this->db->from(REMINDER . ' R');
        $this->db->join(USERS . ' U', 'U.UserID=R.UserID');
        $this->db->join(ACTIVITY . " A", "A.ActivityID=R.ActivityID AND A.StatusID=2", 'JOIN');
        $this->db->where('R.Status !=', 'DELETED');
        $this->db->where('R.ReminderDateTime =', $created_date);
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $result = $query->result_array();
            // $result = array(array("UserGUID" => "e028d83c-352b-77e6-acaf-a44cd9fa836f", "ActivityGUID" => "634ff67c-352b-77e6-acaf-634ff67c"), array("UserGUID" => "e028d83c-352b-77e6-acaf-a44cd9fa836f", "ActivityGUID" => "634ff67c-352b-77e6-acaf-634ff67c"));
            $this->load->library('Node');
            $node = new node(array("route" => "recieveReminder", "postData" => $result));
            //print_r(json_encode($query->result_array()));
            //[{"ActivityGUID":"634ff67c-c3df-7efd-d90b-f8d9e76ca01c","UserGUID":"e028d83c-352b-77e6-acaf-a44cd9fa836f","ReminderGUID":"1737e60e-f559-9e41-1ef9-66b288bc8b0e"},{"ActivityGUID":"db95f22c-7a6c-a783-cf62-c9d9d30de78c","UserGUID":"e028d83c-352b-77e6-acaf-a44cd9fa836f","ReminderGUID":"01320ef4-fcb2-30d2-b923-96cf56cea4a6"}]
        }
        //echo $this->db->last_query();
    }

    function generate_picture_feed() {
        $this->db->where_in('ActivityTypeID', array(23, 24));
        $this->db->delete(ACTIVITY);

        $this->db->where_in('MediaSectionID', array(1, 5));
        $query = $this->db->get(MEDIA);

        if ($query->num_rows()) {
            $data = array();
            foreach ($query->result() as $result) {
                if ($result->MediaSectionID == 1) {
                    $activity_type_id = 23;
                } else {
                    $activity_type_id = 24;
                }
                if ($result->ModuleID && $result->ModuleEntityID) {
                    $d = array();
                    $d['ActivityGUID'] = get_guid();
                    $d['ActivityTypeID'] = $activity_type_id;
                    $d['UserID'] = $result->UserID;
                    $d['ModuleID'] = $result->ModuleID;
                    $d['ModuleEntityID'] = $result->ModuleEntityID;
                    $d['Params'] = json_encode(array('MediaGUID' => $result->MediaGUID));
                    $d['NoOfComments'] = 0;
                    $d['NoOfLikes'] = 0;
                    $d['NoOfFavourites'] = 0;
                    $d['Privacy'] = 1;
                    $d['IsSticky'] = 0;
                    $d['StickyBy'] = 0;
                    $d['IsCommentable'] = 1;
                    $d['PostContent'] = '';
                    $d['IsMediaExist'] = '0';
                    $d['Flaggable'] = 1;
                    $d['ParentActivityID'] = 0;
                    $d['Type'] = 0;
                    $d['ModuleEntityOwner'] = 0;
                    $d['NoOfShares'] = 0;
                    $d['NotifyAll'] = 0;
                    $d['DeletedBy'] = NULL;
                    $d['StatusID'] = 2;
                    $d['Weight'] = NULL;
                    $d['WeightTime'] = NULL;
                    $d['StickyDate'] = NULL;
                    $d['LastActionDate'] = $result->CreatedDate;
                    $d['ModifiedDate'] = $result->CreatedDate;
                    $d['CreatedDate'] = $result->CreatedDate;
                    $d['PostAsModuleID'] = $result->ModuleID;
                    $d['PostAsModuleEntityID'] = $result->ModuleEntityID;
                    $data[] = $d;
                }
                //$this->db->insert(ACTIVITY,$d);
            }
            //print_r($data); die; 
            if ($data) {
                $this->db->insert_batch(ACTIVITY, $data);
            }
        }
    }

    function calculate_group_popularity() {
        $this->db->select('GroupID');
        $this->db->from(GROUPS);
        $this->db->where('StatusID', '2');
        $query = $this->db->get();

        if ($query->num_rows()) {
            $this->load->model('group/group_model');
            foreach ($query->result_array() as $result) {
                $this->group_model->calculate_group_popularity($result['GroupID']);
            }
        }
    }

    function calculate_page_popularity() {
        $this->db->select('PageID');
        $this->db->from(PAGES);
        $this->db->where('StatusID', '2');
        $query = $this->db->get();

        if ($query->num_rows()) {
            $this->load->model('pages/page_model');
            foreach ($query->result_array() as $result) {
                $this->page_model->calculate_page_popularity($result['PageID']);
            }
        }
    }

    /**
     * Function Name: get_trending_post
     * Description: calculate and set trending post depends on comments + like + share + view + replies which is modified in past 24 hours as well
     */
    function set_trending_post() {
        $select = "
            ((SELECT COUNT(PostCommentID)+SUM(NoOfReplies) FROM " . POSTCOMMENTS . " WHERE EntityType='ACTIVITY' AND EntityID=A.ActivityID AND (StatusID='2' OR StatusID='22'))+(SELECT COUNT(PostLikeID) FROM " . POSTLIKE . " WHERE EntityType='ACTIVITY' AND EntityID=A.ActivityID)+A.NoOfShares+A.NoOfViews) as ActivityCount
        ";

        $condition = "
            IF(A.ModuleID=1,A.ModuleEntityID IN(SELECT GroupID FROM " . GROUPS . " WHERE IsPublic='1'),FALSE)
            OR
            IF(A.ModuleID=14,A.ModuleEntityID IN(SELECT EventID FROM " . EVENTS . " WHERE Privacy='PUBLIC'),FALSE)
        ";

        $this->db->select($select);
        $this->db->select('A.ActivityID');
        $this->db->from(ACTIVITY . ' A');
        $this->db->where('A.Privacy', '1');
        $this->db->where('A.StatusID', '2');
        $this->db->where($condition, null, false);
        $this->db->limit(1);
        $this->db->order_by('ActivityCount', 'DESC');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $activity_id = $query->row()->ActivityID;

            $this->db->_protect_identifiers = FALSE;
            $this->db->set('IsTrending', 'IF(ActivityID="' . $activity_id . '",1,0)');
            $this->db->_protect_identifiers = TRUE;
            $this->db->update(ACTIVITY);
        }
    }

    function update_group_member_count() {
        $this->db->select('GroupID');
        $this->db->from(GROUPS);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $group) {
                $this->db->select("COUNT(GroupMemeberID) as MemberCount", false);
                $this->db->from(GROUPMEMBERS);
                $this->db->where('GroupID', $group['GroupID']);
                $this->db->where('StatusID', '2');
                $member_query = $this->db->get();
                if ($member_query->num_rows()) {
                    $this->db->set('MemberCount', $member_query->row()->MemberCount);
                    $this->db->where('GroupID', $group['GroupID']);
                    $this->db->update(GROUPS);
                }
            }
        }
    }

    function update_user_privacy($key) {
        $insert_data = array();
        $this->db->select('LowPrivacyOption,MediumPrivacyOption,HighPrivacyOption');
        $this->db->from(PRIVACYLABEL);
        $this->db->where('Key', $key);
        $privacy_query = $this->db->get();
        $privacy_row = $privacy_query->row_array();

        $this->db->where('PrivacyLabelKey', $key);
        $this->db->delete(USERPRIVACY);

        $this->db->select('U.UserID,UD.Privacy');
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'U.UserID=UD.UserID', 'left');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $result) {
                $data = array('CreatedDate' => get_current_date('%y-%m-%d'), 'PrivacyLabelKey' => $key, 'UserID' => $result['UserID'], 'Value' => '');
                if ($result['Privacy'] == 'low') {
                    $data['Value'] = $privacy_row['LowPrivacyOption'];
                }
                if ($result['Privacy'] == 'medium') {
                    $data['Value'] = $privacy_row['MediumPrivacyOption'];
                }
                if ($result['Privacy'] == 'high') {
                    $data['Value'] = $privacy_row['HighPrivacyOption'];
                }
                if ($result['Privacy'] == 'customize') {
                    $data['Value'] = $privacy_row['MediumPrivacyOption'];
                }
                if (CACHE_ENABLE) {
                    $this->cache->delete('privacy_' . $result['UserID']);
                }
                $insert_data[] = $data;
            }

            if ($insert_data) {
                $this->db->insert_batch(USERPRIVACY, $insert_data);
            }
        }
    }

    /**
     * Function Name: early_reminder
     * Description: It will called by cron to get notification of reminders before 15 mins of arrival
     */
    function early_reminder() {
        $created_date = get_current_date('%Y-%m-%d %H:%i:00', '0.0104', 1);
        $this->db->where('ReminderDateTime <=', $created_date);
        $this->db->where('Status', 'ARCHIVED');
        $this->db->update(REMINDER, array('Status' => 'ACTIVE', 'ModifiedDate' => $created_date));

        $created_date = get_current_date('%Y-%m-%d %H:%i', '0.0104', 1);
        $this->db->select("A.ActivityGUID, U.UserID, U.UserGUID, R.ActivityID, R.ReminderGUID, R.ReminderDateTime");
        $this->db->from(REMINDER . ' R');
        $this->db->join(USERS . ' U', 'U.UserID=R.UserID');
        $this->db->join(ACTIVITY . " A", "A.ActivityID=R.ActivityID AND A.StatusID=2", 'JOIN');
        $this->db->where('R.Status !=', 'DELETED');
        $this->db->where('R.ReminderDateTime =', $created_date);
        $query = $this->db->get();
        // echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $result = $query->result_array();
            $this->load->model('notification_model');
            foreach ($result as $row) {
                $parameters[0]['ReferenceID'] = $row['ReminderDateTime'];
                $parameters[0]['Type'] = 'time';
                $this->notification_model->add_notification(131, $row['UserID'], array($row['UserID']), $row['ActivityID'], $parameters, true, 1);
            }
        }
    }

    /**
     * Function Name: early_reminder
     * Description: It will called by cron to get notification of reminders before 15 mins of arrival
     * @param user_id  int (in case of setting notification settins for any particular user for reminder's notification)
     */
    function set_reminder_notification_settings_for_all($user_id = '') {
        if ($user_id) {
            $this->load->model('notification_model');
            $this->notification_model->set_all_notification_on($user_id);
        } else {
            $this->db->select('UserID');
            $this->db->where('StatusID!=', 3);
            $users_list = $this->db->get(USERS)->result_array();
            foreach ($users_list as $user) {
                // $this->notification_model->set_all_notification_on($user['UserID']);       
                $user_id = $user['UserID'];
                //Only update ModuleNotification Settings for Reminders
                $this->db->where('UserID', $user_id);
                $this->db->where('ModuleID', 19);
                $this->db->delete(MODULENOTIFICATION);
                $this->db->where('UserID', $user_id);
                $this->db->where('NotificationTypeKey', 'activity_reminder');
                $this->db->delete(USERNOTIFICATIONSETTINGS);
                //Add Module Notification for Activity
                $module_query = array('UserID' => $user_id, 'ModuleID' => '19', 'IsEnabled' => '1', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->insert(MODULENOTIFICATION, $module_query);
                //Add Activity reminder key in userNotification Settings
                $notification_settings_query = array('UserID' => $user_id, 'NotificationTypeKey' => 'activity_reminder', 'Email' => '1', 'Mobile' => '1', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->insert(USERNOTIFICATIONSETTINGS, $notification_settings_query);
            }
        }
    }

    function user_tag_weightage() 
    {
        $from_date = get_current_date('%Y-%m-%d 00:00:00', 1);
        $to_date = get_current_date('%Y-%m-%d 23:59:59', 1);

        $this->db->select("EV.EntityViewID, EV.UserID, EV.EntityID");
       
        $this->db->from(ENTITYVIEW . ' EV');
        $this->db->where('EV.ModifiedDate >=', $from_date);
        $this->db->where('EV.ModifiedDate <=', $to_date);
        $this->db->where('EV.EntityType', 'ACTIVITY');
        $this->db->where('EV.StatusID', 1);
        $this->db->limit(50);
        $this->db->order_by("EV.ModifiedDate", "ASC");
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows()) {

            $views = $query->result_array();
            $all_entity_view_id = array();
            foreach($views as $view) {
                $all_entity_view_id[] = $view['EntityViewID'];
            }
            
            if(!empty($all_entity_view_id))
            {
                $this->db->where_in('EntityViewID',$all_entity_view_id);
                $this->db->update(ENTITYVIEW, array('StatusID' => 3)); //for processing
            }

            $this->load->model('tag/tag_model');                           
            foreach ($views as $row) 
            {
                $this->tag_model->add_tags_to_user_from_activity($row['UserID'], $row['EntityID']);
            }

            if(!empty($all_entity_view_id))
            {
                $this->db->where_in('EntityViewID',$all_entity_view_id);
                $this->db->update(ENTITYVIEW, array('StatusID' => 2)); //processing done
            }
        }
    }

    //archive communicatin table
    function archive_users_communications($user_id=0,$no_of_days='',$no_of_emails='')
    {
        if(!$no_of_emails)
            $no_of_emails = MOSTRECENTCOMMUNICATIONCOUNT;
        if(!$no_of_days)
            $no_of_days = MOSTRECENTCOMMUNICATIONDAYS;
        //select all users
        if(!$user_id)
        {
            $this->db->select('UserID');
            $this->db->order_by('UserID');
            $query = $this->db->get(USERS);
            if($query->num_rows())
            {
                foreach ($query->result_array() as $value) 
                {               
                    $this->archive_communication_queries($value['UserID'],$no_of_days,$no_of_emails);
                }
            }            
        }
        else
        {
            $this->archive_communication_queries($user_id,$no_of_days,$no_of_emails);
        }
        return true;
    }

    function archive_communication_queries($user_id,$no_of_days,$no_of_emails)
    {  
        // $this->db->simple_query('SET SESSION group_concat_max_len=300');
        //get last 15 Days Notification
        $query_most_recent_days = "SELECT CommunicationID
                FROM  `Communications` 
                WHERE CreatedDate > DATE_SUB( DATE( NOW( ) ) , INTERVAL ".$no_of_days." 
                DAY ) 
                AND UserID =$user_id
                ORDER BY CommunicationID DESC";
        $emails_most_recent_days = $this->db->query($query_most_recent_days);
        $communication_days=$communication_count=array();
        if($emails_most_recent_days->num_rows())
        {
            foreach ($emails_most_recent_days->result_array() as $value) 
            {
                $communication_days[] = $value['CommunicationID'];
            }
        }

        
        //get Last 300 Notifications
        $query_most_recent_count = "SELECT CommunicationID
                FROM  `Communications` 
                WHERE UserID =$user_id
                ORDER BY CommunicationID DESC 
                LIMIT ".$no_of_emails;
        $emails_most_recent_count = $this->db->query($query_most_recent_count);
        
        $most_recent_communication_count = $this->get_most_recent_communication_count($user_id,$no_of_days,$no_of_emails);

        if($emails_most_recent_count->num_rows())
        {
            foreach ($emails_most_recent_count->result_array() as $value) 
            {
                $communication_count[] = $value['CommunicationID'];
            }
        }
        if(empty($communication_days))
            $communication_days = 0;
        else
            $communication_days = implode(',',$communication_days);

        if(empty($communication_count))
            $communication_count = 0;
        else
            $communication_count = implode(',',$communication_count);

        //Archive all notifications of last 300 or given count
        if($most_recent_communication_count)
        {            
            if($most_recent_communication_count < $no_of_emails)
            {
                $insert = "INSERT INTO ".COMMUNICATIONS_ARCHIVE." (SELECT * 
                    FROM ".COMMUNICATIONS."
                    WHERE CommunicationID NOT 
                    IN (".$communication_count.")
                    AND UserID =$user_id)";
                $delete = "DELETE FROM ".COMMUNICATIONS."
                    WHERE CommunicationID NOT 
                    IN (".$communication_count.")
                    AND UserID =$user_id";
            }
            else
            {
                //Archive all notifications of last 15 or given days
                $insert = "INSERT INTO ".COMMUNICATIONS_ARCHIVE." (SELECT * 
                    FROM ".COMMUNICATIONS."
                    WHERE CommunicationID NOT 
                    IN (".$communication_days.")
                    AND UserID =$user_id)";
                $delete = "DELETE FROM ".COMMUNICATIONS."
                    WHERE CommunicationID NOT 
                    IN (".$communication_days.")
                    AND UserID =$user_id";
            }
            $this->db->query($insert);
            $this->db->query($delete);      
            // $total_noti = $this->get_total_user_notification($user_id);
            // echo $user_id.'**'.$most_recent_communication_count.'**'.$total_noti.'<br>';
        }
        
    }

    function get_most_recent_communication_count($user_id,$no_of_days,$no_of_emails)
    {
        $this->db->select('CommunicationID');
        $this->db->where("CreatedDate > DATE_SUB( DATE( NOW( ) ) , INTERVAL ".MOSTRECENTCOMMUNICATIONDAYS." DAY )",NULL,FALSE);
        $this->db->where('UserID',$user_id);
        $query = $this->db->get(COMMUNICATIONS);
        return $query->num_rows();
    }

    function get_total_user_communication($user_id)
    {
        $this->db->select('CommunicationID');
        // $this->db->where("CreatedDate > DATE_SUB( DATE( NOW( ) ) , INTERVAL ".MOSTRECENTNOTIFICATIONDAYS." DAY )",NULL,FALSE);
        $this->db->where('UserID',$user_id);
        $query = $this->db->get(COMMUNICATIONS);
        return $query->num_rows();
    }

    function send_contest_notification()
    {
        $this->db->select('UserID');
        $this->db->from(USERS);
        $this->db->where_not_in('StatusID',array('3'));
        $query = $this->db->get();
        
        if($query->num_rows())
        {
            foreach($query->result_array() as $user)
            {
                $this->db->select('A.ActivityID',false);
                $this->db->from(ACTIVITY.' A');
                $this->db->join(PARTICIPANTS.' P',"A.ActivityID=P.ActivityID AND P.ParticipantID='".$user['UserID']."'",'left');
                $this->db->where("P.ParticipationID is null",null,false);
                $this->db->where('A.ActivityTypeID','37');
                $this->db->where('A.StatusID','2');
                $this->db->where("A.ContestEndDate<='".get_current_date('%Y-%m-%d', 3, 1)."'");
                $this->db->where("A.ContestEndDate>'".get_current_date('%Y-%m-%d')."'");
                $contest_query = $this->db->get();
                if($contest_query->num_rows())
                {
                    foreach($contest_query->result_array() as $arr)
                    {
                        $parameters[0]['ReferenceID'] = $user['UserID'];
                        $parameters[0]['Type'] = 'User';
                        $this->notification_model->add_notification(149, 1, array($user['UserID']), $arr['ActivityID'], $parameters);
                    }
                }
            }
        }

    }
}
