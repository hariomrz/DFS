<?php

/**
 * This model is used for getting and storing Event related information
 * @package    Event_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Event_model extends Common_Model {

    protected $joined_event_list = array();

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * [set_user_events used to set user joined / invite event in variable]
     * @param type $user_id
     */
    function set_user_events($user_id) {
        if ($this->settings_model->isDisabled(14)) {
            $this->joined_event_list = array();
        } else {
            $joined_event_list = $this->get_all_joined_events($user_id);
            $invited_event_list = $this->get_all_invited_events($user_id);
            $joined_event_list = explode(',', $joined_event_list);
            $invited_event_list = explode(',', $invited_event_list);
            $this->joined_event_list = array_unique(array_merge($joined_event_list, $invited_event_list));
            if ($this->joined_event_list) {
                $this->joined_event_list = implode(',', $this->joined_event_list);
            }
        }
    }

    /**
     * [set_user_joined_events used to set user joined event in variable]
     * @param type $user_id
     */
    function set_user_joined_events($user_id) {
        $this->joined_event_list = $this->event_model->get_all_joined_events($user_id);
    }

    /**
     * [get_user_joined_events used to return user joined event data]
     * @return type
     */
    function get_user_joined_events() {
        if (!empty($this->joined_event_list)) {
            if (is_string($this->joined_event_list)) {
                $this->joined_event_list = rtrim($this->joined_event_list, ',');
                $this->joined_event_list = ltrim($this->joined_event_list, ',');
            }
        }
        return $this->joined_event_list;
    }

    function get_visible_events($user_id) {
        $data = array(0);
        $this->db->select('E.EventID');
        $this->db->from(EVENTS . ' E');
        $this->db->join(EVENTUSERS . ' EU', 'EU.EventID=E.EventID AND EU.UserID="' . $user_id . '"', "left");
        $this->db->where("IF(E.Privacy='PRIVATE' OR E.Privacy='INVITE_ONLY',EU.EventID is not null,true)", null, false);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $r) {
                $data[] = $r['EventID'];
            }
        }
        return $data;
    }

    /**
     * [get_locations Used to get event locations]
     * @param  [int] $event_id    [event id]
     * @return [array]            [Location object]
     */
    public function get_locations($event_id, $all_details = TRUE, $is_state = FALSE) {
        $locations = array();
        $this->db->select('LocationID');
        $this->db->from(EVENTS_PLACES);
        $this->db->where('EventID', $event_id);
        $this->db->order_by('DisplayOrder');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $result) {
                $this->load->helper('location');
                $location = get_location_by_id($result['LocationID'], ['City' => 1, 'State' => 1]);
                if ($all_details) {
                    $locations[] = $location;
                } else {
                    if(!empty($location['Latitude']) && !empty($location['Longitude'])) {
                        $location_details = array('City' => $location['City'], 'Lat' => $location['Latitude'], 'Long' => $location['Longitude']);
                        if($is_state) {
                            $location_details['State'] = $location['State']; 
                        }
                        $locations[] = $location_details;
                    }
                }
            }
        }
        return $locations;
    }

    public function save_locations($locations, $event_id) {



        $this->db->where(['EventID' => $event_id]);
        $this->db->delete(EVENTS_PLACES);

        if (!is_array($locations) || empty($locations)) {
            return;
        }

        $this->load->helper('location');
        $event_locations = [];
        $display_order = 1;
        $save_first_location = 0;
        foreach ($locations as $location) {
            $insert_location = array(
                'UniqueID' => isset($location['UniqueID']) ? $location['UniqueID'] : "",
                'FormattedAddress' => isset($location['FormattedAddress']) ? $location['FormattedAddress'] : "",
                'Latitude' => isset($location['Latitude']) ? $location['Latitude'] : "",
                'Longitude' => isset($location['Longitude']) ? $location['Longitude'] : "",
                'StreetNumber' => isset($location['StreetNumber']) ? $location['StreetNumber'] : "",
                'Route' => isset($location['Route']) ? $location['Route'] : "",
                'City' => isset($location['City']) ? $location['City'] : "",
                'State' => isset($location['State']) ? $location['State'] : "",
                'Country' => isset($location['Country']) ? $location['Country'] : "",
                'PostalCode' => isset($location['PostalCode']) ? $location['PostalCode'] : "",
                'StateCode' => isset($location['StateCode']) ? $location['StateCode'] : "",
                'CountryCode' => isset($location['CountryCode']) ? $location['CountryCode'] : "",
            );

            $location = insert_location($insert_location);

            $location_id = !empty($location['LocationID']) ? $location['LocationID'] : 0;

            if (!$location_id) {
                continue;
            }

            if (!$save_first_location) {
                $save_first_location = $location;
            }

            $event_locations[] = array(
                'EventID' => $event_id,
                'LocationID' => $location_id,
                'DisplayOrder' => $display_order,
            );
            $display_order++;
        }

        $this->db->insert_on_duplicate_update_batch(EVENTS_PLACES, $event_locations); 


        // Update event for first location
        $event_data = [];
        $event_data['LocationID'] = $save_first_location['LocationID'];        
        $this->db->where('EventID', $event_id);
        $this->db->update(EVENTS, $event_data);


        return $save_first_location;
    }

    /**
     * Function Name: save_event
     * @param event_data
     * @param location
     * @param category_id
     * @param user_id
     * @param event_guid
     * Description: Save added / updated event
     */
    function save_event($event_data = '', $locations = [], $category_id = array(), $user_id = 0, $event_guid = 0) {
        if (!empty($event_data)) {
            // To Save Users Location and get Location ID - Start
            $time_zone = "UTC";
            $event_data['LocationID'] = 0;
            if (is_array($locations) && count($locations)) {
                $location = $locations[0];

                $insert_location = array(
                    'UniqueID' => isset($location['UniqueID']) ? $location['UniqueID'] : "",
                    'FormattedAddress' => isset($location['FormattedAddress']) ? $location['FormattedAddress'] : "",
                    'Latitude' => isset($location['Latitude']) ? $location['Latitude'] : "",
                    'Longitude' => isset($location['Longitude']) ? $location['Longitude'] : "",
                    'StreetNumber' => isset($location['StreetNumber']) ? $location['StreetNumber'] : "",
                    'Route' => isset($location['Route']) ? $location['Route'] : "",
                    'City' => isset($location['City']) ? $location['City'] : "",
                    'State' => isset($location['State']) ? $location['State'] : "",
                    'Country' => isset($location['Country']) ? $location['Country'] : "",
                    'PostalCode' => isset($location['PostalCode']) ? $location['PostalCode'] : "",
                    'StateCode' => isset($location['StateCode']) ? $location['StateCode'] : "",
                    'CountryCode' => isset($location['CountryCode']) ? $location['CountryCode'] : "",
                );

                $location = insert_location($insert_location);
                $time_zone = $location['TimeZone'];
                $event_data['LocationID'] = $location['LocationID'];
            }




            $start_date = $event_data['StartDate'];
            $start_time = $event_data['StartTime'];
            $end_date = $event_data['EndDate'];
            $end_time = $event_data['EndTime'];

            $this->load->model('timezone/timezone_model');
            $start_date_time = $this->timezone_model->convert_date_to_time_zone($start_date . ' ' . $start_time, $time_zone, 'UTC');
            $end_date_time = $this->timezone_model->convert_date_to_time_zone($end_date . ' ' . $end_time, $time_zone, 'UTC');
            $e_d_t = $end_date_time;
            $start_date_time = explode(' ', $start_date_time);
            $end_date_time = explode(' ', $end_date_time);

            $event_data['StartDate'] = $start_date_time[0];
            $event_data['StartTime'] = $start_date_time[1];
            $event_data['EndDate'] = $end_date_time[0];
            $event_data['EndTime'] = $end_date_time[1];
            $event_data['ArchiveThreshold'] = date('Y-m-d H:i:s', (strtotime($e_d_t) + $event_data['ArchiveThreshold'] * 86400));
            $event_data['LastActivity'] = get_current_date('%Y-%m-%d %H:%i:%s');

            if (!empty($event_guid)) {
                $old_query = $this->db->get_where(EVENTS, array('EventGUID' => $event_guid));

                $this->db->where('EventGUID', $event_guid);
                $this->db->update(EVENTS, $event_data);
                $event_id = get_detail_by_guid($event_guid, 14);
                // Inserting user's selected categories
                if (!empty($category_id)) {
                    $this->load->model("category/category_model");
                    $this->category_model->insert_update_category($category_id, 14, $event_id);
                }

                if ($old_query->num_rows()) {
                    $old_data = $old_query->row_array();

                    $changed_data = 0;
                    if ($old_data['StartDate'] != $event_data['StartDate'] || $old_data['StartTime'] != $event_data['StartTime'] || $old_data['EndDate'] != $event_data['EndDate'] || $old_data['EndTime'] != $event_data['EndTime']) {
                        // Send event notification
                        $this->sendEditEventNotification($event_id, $user_id);
                        $changed_data = 1;
                    }

                    if (($old_data['LocationID'] != $event_data['LocationID'] || $old_data['Venue'] != $event_data['Venue']) && $changed_data == 0) {
                        // Send event notification
                        $this->sendEditEventNotification($event_id, $user_id);
                    }
                }

                $this->save_locations($locations, $event_id);

                return true;
            } else {
                // To Save Users Location and get Location ID - End

                $this->db->insert(EVENTS, $event_data);
                $event_id = $this->db->insert_id();
                // Inserting user's selected categories
                if (!empty($category_id)) {
                    $this->load->model("category/category_model");
                    $this->category_model->insert_update_category($category_id, 14, $event_id);
                }
                // Adding Event Owner in Event Users table
                $this->addEventUsers($event_id, array(array('UserID' => $user_id, 'ModuleRoleID' => 1)), $user_id, "ATTENDING");

                // Update Last Activity date time
                $this->setUserPermission($user_id, $event_id);

                //Create Defualt album
                create_default_album($user_id, 14, $event_id, array(
                    'is_add_log' => 1,
                    'activity_type_id' => 30,
                ));

                // insert one more Event owner entry in Event Users table
                if (!empty($event_data['ModuleID']) && !empty($event_data['ModuleEntityID']) && $event_data['ModuleID'] != 3) {
                    if ($event_data['ModuleID'] == 1) {
                        $permission = check_group_permissions($user_id, $event_data['ModuleEntityID']);
                        $is_creator = $permission['IsCreator'];
                        if (!$is_creator) {
                            $this->addEventUsers($event_id, array(array('UserID' => $permission['Details']['CreatorID'], 'ModuleRoleID' => 1)), $user_id, "ATTENDING");
                        }
                    } else if ($event_data['ModuleID'] == 18) {
                        $this->load->model("pages/page_model");
                        $page_creator_id = $this->page_model->get_page_creator_id($event_data['ModuleEntityID']);
                        if ($page_creator_id != $user_id) {
                            $this->addEventUsers($event_id, array(array('UserID' => $page_creator_id, 'ModuleRoleID' => 1)), $user_id, "ATTENDING");
                        }
                    }
                }

                $this->load->model('subscribe_model');
                $this->subscribe_model->toggle_subscribe($user_id, 'EVENT', $event_id);

                $this->subscribe_model->subscribe_email($user_id, $event_id, 'create_event');

                $this->save_locations($locations, $event_id);

                return $this->getEvents(array('EventID' => $event_id));
            }
        } else {
            return false;
        }
    }

    function event_near_you($lat = '', $lng = '', $user_id = 0) {
        $data = array();

        $this->db->select("E.*");
        $this->db->select('U.FirstName,U.LastName');
        if ($lat && $lng) {
            $this->db->select("(3959*acos(cos(radians(" . $lat . "))*cos(radians(L.Latitude))*cos(radians(L.Longitude)-radians(" . $lng . "))+sin(radians(" . $lat . "))*sin(radians(L.Latitude)))) as Distance", false);
            $this->db->join(LOCATIONS . ' L', 'L.LocationID=E.LocationID', 'left');
            $this->db->having('Distance < 50', null, false);
        }
        $this->db->from(EVENTS . ' E');
        $this->db->join(EVENTUSERS . ' EU', 'E.EventID=EU.EventID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=E.CreatedBy');
        $this->db->where("DATE_FORMAT(CONCAT(E.EndDate,' ',E.EndTime),'%Y-%m-%d %H:%i:%s')>'" . get_current_date('%Y-%m-%d %H:%i:%s') . "'", null, false);
        $this->db->where('EU.EventID is not NULL', NULL, FALSE);
        $this->db->where("E.IsDeleted!=1", NULL, FALSE);
        $this->db->where("E.Privacy", 'PUBLIC');
        $this->db->_protect_identifiers = FALSE;
        $this->db->order_by("DATE_FORMAT(CONCAT(E.EndDate,' ',E.EndTime),'%Y-%m-%d %H:%i:%s')", 'ASC');
        $this->db->_protect_identifiers = TRUE;
        $this->db->limit(1);
        $query = $this->db->get();
        //echo $this->db->last_query();
        $time_zone = get_user_time_zone($user_id);

        if ($query->num_rows()) {
            foreach ($query->result_array() as $key => $val) {
                $start_date_time = $this->timezone_model->convert_date_to_time_zone($val['StartDate'] . ' ' . $val['StartTime'], 'UTC', $time_zone);
                $end_date_time = $this->timezone_model->convert_date_to_time_zone($val['EndDate'] . ' ' . $val['EndTime'], 'UTC', $time_zone);
                $start_date_time = explode(' ', $start_date_time);
                $end_date_time = explode(' ', $end_date_time);

                $val['StartDate'] = $start_date_time[0];
                $val['StartTime'] = $start_date_time[1];
                $val['EndDate'] = $end_date_time[0];
                $val['EndTime'] = $end_date_time[1];

                $val['DisplayStartTime'] = date('h:i A', strtotime($start_date_time[1]));
                $val['DisplayEndTime'] = date('h:i A', strtotime($end_date_time[1]));

                $val['Location'] = get_location_by_id($val['LocationID']);
                $val['EventStatus'] = 'NOT_ATTENDING';
                $val['ProfilePicture'] = $this->get_event_media($val['ProfileImageID'], $val['ProfileBannerID'], 'ProfilePicture');
                $val['ProfileURL'] = $this->getViewEventUrl($val['EventGUID'], $val['Title']);
                $data[] = $val;
            }
        }
        return $data;
    }

    function invited_events_list($user_id) {
        $this->db->select('E.*,DATE_FORMAT(E.LastActivity,"%d %b,%Y") AS LastActivity, DATE_FORMAT(E.StartTime,"%l:%i %p") AS StartTime,DATE_FORMAT(E.EndTime,"%l:%i %p") AS EndTime', false);
        $this->db->select('IFNULL( CM.Name, "") AS CategoryName, CM.CategoryID,U.UserGUID', false);
        $this->db->from(EVENTS . ' AS E');
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT");
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");
        $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID');
        $this->db->join(USERS . ' AS U', 'U.UserID=E.CreatedBy');
        $this->db->where('EU.UserID', $user_id);
        $this->db->where('EU.Presence', 'INVITED');
        $this->db->where('E.IsDeleted', '0');
        $this->db->where("DATE_FORMAT(CONCAT(E.EndDate,' ',E.EndTime),'%Y-%m-%d %H:%i:%s')>'" . get_current_date('%Y-%m-%d %H:%i:%s') . "'", null, false);
        $this->db->limit(5);
        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();
        $events = array();
        if ($result) {
            $this->load->helper('location');
            foreach ($result as $key => $val) {
                $event_users = $this->members($val['EventID'], $user_id, FALSE, '', '', '', '', true);
                $creator_info = get_detail_by_id($val['CreatedBy'], 3, 'FirstName,LastName', 2);

                $events[] = $val;
                $events[$key]['loggedUserPresence'] = $this->get_user_presence($user_id, $val['EventID']);
                $events[$key]['EventUsers'] = $event_users;
                $events[$key]['UsersCount'] = count($event_users);
                $events[$key]['MemberCount'] = count($event_users);
                $events[$key]['Location'] = get_location_by_id($val['LocationID']);
                $events[$key]['ProfilePicture'] = $this->get_event_media($val['ProfileImageID'], $val['ProfileBannerID'], 'ProfilePicture');
                $events[$key]['ProfileBanner'] = $this->get_event_media($val['ProfileImageID'], $val['ProfileBannerID'], 'ProfileBanner');

                if (isset($events[$key]['Media']['ProfileImage']['MediaName']) && ($events[$key]['Media']['ProfileImage']['MediaName'])) {
                    $events[$key]['Media']['ProfileImage']['MediaName'] = 'event-placeholder.png';
                }

                $events[$key]['CreatedBy'] = $creator_info['FirstName'] . ' ' . $creator_info['LastName'];
                $events[$key]['CreatedByURL'] = get_entity_url($val['CreatedBy'], 'User', 1);
                $events[$key]['EventUrl'] = $this->getViewEventUrl($val['EventGUID'], $val['Title']);

                $events[$key]['IsAdmin'] = 0;

                if ($user_id) {
                    $events[$key]['IsAdmin'] = $this->is_admin($val['EventID'], $user_id);
                }
                $events[$key]['IsCoverExists'] = 0;
                $events[$key]['EventCoverImage'] = "";
                $event_banner = '';
                if (!empty($events[$key]['ProfileBanner'])) {
                    $event_banner = $events[$key]['ProfileBanner'];
                    $events[$key]['IsCoverExists'] = 1;

                    $events[$key]['ProfileBanner'] = get_profile_cover($event_banner);
                }


                if (!empty($input['EventID'])) {
                    $events[$key]['Presence'] = $this->getEventMemberCount($input['EventID']);
                }
                unset($events[$key]['EventID']);
            }
        }
        return $events;
    }

    function has_access($user_id, $event_id) {
        $this->load->model('users/user_model');
        $friend_follower = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $friends = $friend_follower['Friends'];
        $friends[] = 0;
        $frnd = implode(',', $friends);

        $sql = "SELECT E.EventID FROM " . EVENTUSERS . " EU
                LEFT JOIN " . EVENTS . " E ON EU.EventID=E.EventID
                WHERE E.IsDeleted='0' AND (EU.Presence='ATTENDING' OR EU.Presence='INVITED')
                AND EU.UserID='" . $user_id . "' AND E.EventID='" . $event_id . "'";
        $query = $this->db->query($sql);
        if ($query->num_rows()) {
            return true;
        } else {
            $sql = "SELECT E.EventID FROM " . EVENTS . " E
				WHERE E.IsDeleted='0' AND E.EventID='" . $event_id . "'
				AND E.Privacy='PUBLIC'
			";
            $query = $this->db->query($sql);
            if ($query->num_rows()) {
                return true;
            }
        }
        return false;
    }

    /* Common condition for event list page */

    public function common_event_list_condition($input, $user_id = '', $event_type = 'AllPublicEvents', $friend_ids = array(), $current_user_group_ids = array()) {
        $filter = isset($input['Filter']) ? $input['Filter'] : '';

        $isDeleted = "E.IsDeleted='0'";
        if ($filter) {
            if (isset($filter['StartDate']) && $filter['StartDate']) {
                $this->db->where('E.StartDate >=', $filter['StartDate']);
            }

            if (isset($filter['EndDate']) && $filter['EndDate']) {
                $this->db->where('E.EndDate <=', $filter['EndDate']);
            }

            if (isset($filter['Keyword']) && $filter['Keyword']) {
                $this->db->like('E.Title', $filter['Keyword']);
            }

            if (isset($filter['OrderBy']) && isset($filter['OrderType']) && $filter['OrderBy'] && $filter['OrderType']) {
                $order_by = 'E.' . $filter['OrderBy'] . ' ' . $filter['OrderType'];
            }
        }

        if (!isset($order_by)) {
            $order_by = 'E.LastActivity DESC ';
        }


        // GROUP JOIN
        $this->db->join(GROUPS . ' AS G', 'G.GroupID=E.ModuleEntityID AND E.ModuleID = 1', 'left');
        /* if(!empty($user_id)){
          $this->db->join(GROUPMEMBERS . ' AS GM', 'GM.GroupID=G.GroupID','left');

          }
         */

        //------------------------------------------------------------
        // Check for specific event
        if (isset($input['EventID'])) {
            $this->db->where('E.EventID', $input['EventID']);
            $isDeleted = "E.IsDeleted!='1'";
        } else if ($event_type == 'Suggested') {
            if ($friend_ids) {
                $this->db->where_in('EU.UserID', $friend_ids);
            }

            $where2 = " IF((E.ModuleID='1' AND G.IsPublic='1'),TRUE, IF(G.GroupID is null,TRUE,FALSE)) ";
            $this->db->where($where2, FALSE, FALSE);

            $this->db->where('EU.UserID !=' . $user_id . ' AND E.Privacy="PUBLIC" AND EU.EventID NOT IN ( SELECT EventID FROM ' . EVENTUSERS . ' WHERE ' . EVENTUSERS . '.UserID=' . $user_id . ')', NULL, FALSE);
            $this->db->where(' EU.EventID NOT IN ( SELECT I.EntityID FROM ' . IGNORE . ' AS I WHERE I.UserID=' . $user_id . ' AND I.EntityType="Event")', NULL, FALSE);
            
            $this->db->where("DATE_FORMAT(CONCAT(E.StartDate,' ',E.StartTime),'%Y-%m-%d %H:%i:%s')>='" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
        } else if ($event_type == 'MyPastEvent') {
            $this->db->where('EU.UserID', $user_id);
            $this->db->where('EU.Presence', 'ATTENDING');
        } else {
            if (!empty($user_id)) {
                $where = '';
                if ($event_type == "AllPublicEvents") {
                    //$where = "E.Privacy = 'PUBLIC'";
                    $where = '((E.CreatedBy = ' . $user_id . ') OR (EU.Presence IN("INVITED","ATTENDING") AND EU.UserID = ' . $user_id . ') OR (E.Privacy = "PUBLIC"))';
                } else if ($event_type == "AllMyEvents") {
                    $where = '((E.CreatedBy = ' . $user_id . ') OR (EU.Presence IN("INVITED","ATTENDING") AND EU.UserID = ' . $user_id . ') )';
                } else if ($event_type == "EventICreated") {
                    $where = 'E.CreatedBy = ' . $user_id . '';
                } else if ($event_type == "EventIJoined") {
                    $where = '(EU.Presence = "ATTENDING" AND EU.UserID = ' . $user_id . ') AND E.CreatedBy != ' . $user_id . ' ';
                } else if ($event_type == "EventIInvited") {
                    $this->db->join(EVENTINVITES . ' AS EI', 'EI.EventID=E.EventID and `EU`.`EventID` = `EI`.`EventID` and EI.UserID = "' . $user_id . '"', 'LEFT');
                    $where = '(EI.Status = "INVITED" AND EI.UserID = ' . $user_id . ') AND E.CreatedBy != ' . $user_id . ' ';
                }
                if (!empty($where)) {
                    $this->db->where($where, FALSE, FALSE);
                }

                //$where2 = " IF((E.ModuleID='1' AND G.IsPublic='1'),TRUE, if((E.ModuleID='1' AND G.IsPublic!='1'),GM.ModuleEntityID = ".$user_id." AND GM.StatusID = 2,TRUE) ) ";                
                //$this->db->where($where2,FALSE,FALSE);
            } else {
                if ($event_type == "AllMyEvents" || $event_type == "AllPublicEvents") {
                    $this->db->where('E.Privacy', 'PUBLIC');

                    // $where2 = " IF((E.ModuleID='1' AND G.IsPublic='1'),TRUE, if(E.ModuleID='18',TRUE,if(E.ModuleID='3',TRUE,FALSE)) )  ";
                    // $this->db->where($where2,FALSE,FALSE);
                }
            }
            $this->db->where("IF((G.IsPublic=0 || G.IsPublic=2),G.GroupID IN(" . implode(',', $current_user_group_ids) . "),TRUE)", null, false);
        }

        if (empty($input['EventID'])) {
            if ($event_type != 'MyPastEvent') {
                $this->db->where("DATE_FORMAT(CONCAT(E.StartDate,' ',E.StartTime),'%Y-%m-%d %H:%i:%s')>'" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            } else {
                $this->db->where("DATE_FORMAT(CONCAT(E.StartDate,' ',E.StartTime),'%Y-%m-%d %H:%i:%s')<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            }
        }
        $this->db->where($isDeleted, NULL, FALSE);
        $this->db->order_by($order_by);
    }

    /**
     * [get_events description]
     * @param  [array] $input      [Request data]
     * @param  [string] $user_id   [user id]
     * @param  string $event_type  [event type]
     * @param  string $num_rows    [num rows flag]
     * @param  string $page_no     [page no]
     * @param  string $page_size   [page size]
     * @return [array]             [description]
     */
    public function get_events($input, $user_id = '', $event_type = 'AllPublicEvents', $num_rows = '', $page_no = '', $page_size = '') {
        $filter = isset($input['Filter']) ? $input['Filter'] : '';
        $friend_ids = array();
        if (isset($event_type) && $event_type == 'Suggested') {
            $this->load->model('users/user_model');
            $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        }

        $current_user_group_ids = array();
        if (!empty($user_id)) {
            $this->load->model('group/group_model');
            $current_user_group_ids = $this->group_model->get_users_groups($user_id);
        }
        $current_user_group_ids[] = 0;

        $this->db->select('DATE_FORMAT(E.LastActivity,"%d %b,%Y") AS LastActivity, DATE_FORMAT(E.StartTime,"%l:%i %p") AS StartTime,DATE_FORMAT(E.EndTime,"%l:%i %p") AS EndTime', false);

        $this->db->select('E.EventID, E.EventGUID, E.CreatedBy, E.Title, E.Description, E.Privacy, E.IsDeleted, E.StartDate, E.EndDate, E.ModuleID, E.ModuleEntityID', false);
        $this->db->select('IFNULL(E.Venue,"") as Venue', FALSE);
        //$this->db->select('IFNULL(E.EventURL,"") as EventURL', FALSE); 
        $this->db->select('IFNULL(E.LocationID,"") as LocationID', FALSE);
        $this->db->select('IFNULL(E.ProfileImageID,"") as ProfileImageID', FALSE);
        $this->db->select('IFNULL(E.ProfileBannerID,"") as ProfileBannerID', FALSE);

        /* if($user_id) {
          $this->db->select('if(EI.Status = "INVITED" and EI.UserID = "'.$user_id.'", 1, 0) IsInvited', FALSE);
          $this->db->select('if(EU.Presence != "" and EU.UserID = "'.$user_id.'", EU.Presence, "") EventUserPresence', FALSE);
          $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID and EU.UserID = "'.$user_id.'"','LEFT');
          $this->db->join(EVENTINVITES . ' AS EI', 'EI.EventID=E.EventID and EU.UserID = "'.$user_id.'"','LEFT');
          }
         */

        $this->db->select('IFNULL( CM.Name, "") AS CategoryName, CM.CategoryID,U.UserGUID', false);
        $this->db->from(EVENTS . ' AS E');
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT");
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");

        $this->db->join(USERS . ' AS U', 'U.UserID=E.CreatedBy');

        /* if($user_id) { */
        /* $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID and EU.UserID = "'.$user_id.'"','LEFT');
          $this->db->join(EVENTINVITES . ' AS EI', 'EI.EventID=E.EventID and `EU`.`EventID` = `EI`.`EventID` and EI.UserID = "'.$user_id.'"','LEFT'); */
        $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID', 'LEFT');
        /* } */

        if (isset($input['CityID']) && !empty($input['CityID'])) {
            $citys = $input['CityID'];
            $city_ids = implode($citys, ",");

            $this->db->join(LOCATIONS . ' AS L', 'L.LocationID=E.LocationID');
            $this->db->join(CITIES . ' AS C', 'C.CityID=L.CityID AND C.CityID IN (' . $city_ids . ')');

            /* if(is_array($input['CityID']))
              {
              $this->db->where_in('E.LocationID', $input['LocationID']);
              } else {
              $this->db->where('E.LocationID', $input['LocationID']);
              }
             * 
             */
        }

        if (!empty($input['CategoryIDs'])) {
            if (is_array($input['CategoryIDs'])) {
                $this->db->where_in('CM.CategoryID', $input['CategoryIDs']);
            } else {
                $this->db->where('CM.CategoryID', $input['CategoryIDs']);
            }
        }

        $this->common_event_list_condition($input, $user_id, $event_type, $friend_ids, $current_user_group_ids);
        $this->db->group_by('E.EventID');

        if ($page_size) { // Check for pagination
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }

        if ($num_rows) {
            return $this->db->get()->num_rows();
        } else {
            $result = $this->db->get()->result_array();
            //echo $this->db->last_query();die;
            $events = array();
            if ($result) {
                $this->load->helper('location');
                foreach ($result as $key => $val) {
                    //$event_users = $this->members($val['EventID'], $user_id);
                    $event_users = $this->event_members($val['EventID'], $user_id);
                    $creator_info = get_detail_by_id($val['CreatedBy'], 3, 'FirstName,LastName,ProfilePicture', 2);

                    $events[] = $val;
                    $events[$key]['loggedUserPresence'] = $this->get_user_presence($user_id, $val['EventID']);
                    $events[$key]['IsPast'] = (strtotime($val['StartDate']) <= strtotime(date('Y-m-d'))) ? 1 : 0;

                    // $events[$key]['IsInvited'] = $events[$key]['loggedUserPresence'];
                    // $events[$key]['EventUserPresence'] = $events[$key]['loggedUserPresence'];

                    $events[$key]['EventUsers'] = $event_users;
                    $events[$key]['UsersCount'] = $events[$key]['MemberCount'] = $this->event_members($val['EventID'], $user_id, TRUE);
                    $events[$key]['Location'] = get_location_by_id($val['LocationID'],['City' => 1, 'State' => 1,'Country'=>1]);
                    $events[$key]['ProfilePicture'] = $this->get_event_media($val['ProfileImageID'], $val['ProfileBannerID'], 'ProfilePicture');
                    $events[$key]['ProfileBanner'] = $this->get_event_media($val['ProfileImageID'], $val['ProfileBannerID'], 'ProfileBanner');

                    if (isset($events[$key]['Media']['ProfileImage']['MediaName']) && ($events[$key]['Media']['ProfileImage']['MediaName'])) {
                        $events[$key]['Media']['ProfileImage']['MediaName'] = 'event-placeholder.png';
                    }

                    $events[$key]['CreatedBy'] = $creator_info['FirstName'] . ' ' . $creator_info['LastName'];
                    $events[$key]['CreatedByProfilePicture'] = $creator_info['ProfilePicture'];
                    $events[$key]['CreatedByURL'] = get_entity_url($val['CreatedBy'], 'User', 1);
                    $events[$key]['EventUrl'] = $this->getViewEventUrl($val['EventGUID'], $val['Title']);

                    $events[$key]['IsAdmin'] = 0;
                    $events[$key]['ModuleRoleID'] = 0;

                    if ($user_id) {
                        $events[$key]['IsAdmin'] = $this->is_admin($val['EventID'], $user_id);
                        $events[$key]['ModuleRoleID'] = $this->get_module_role_id($val['EventID'], $user_id);
                    }
                    $events[$key]['EventDay'] = date('l', strtotime($events[$key]['StartDate']));
                    $events[$key]['EventShortDayTime'] = date('D, d M, Y', strtotime($events[$key]['StartDate']));
                    $events[$key]['EventStartDate'] = date('d', strtotime($events[$key]['StartDate']));
                    $events[$key]['EventStartMothnYear'] = date('M, Y', strtotime($events[$key]['StartDate']));
                    $events[$key]['EventStartMonth'] = date('M', strtotime($events[$key]['StartDate']));
                    if(strlen($events[$key]['Title'])>50)
                        $events[$key]['ShortTitle'] = substr($events[$key]['Title'],0,46)."...";
                    else
                        $events[$key]['ShortTitle'] = $events[$key]['Title'];

                    $events[$key]['IsCoverExists'] = 0;
                    $events[$key]['EventCoverImage'] = "";
                    $event_banner = '';
                    if (!empty($events[$key]['ProfileBanner'])) {
                        $event_banner = $events[$key]['ProfileBanner'];
                        $events[$key]['IsCoverExists'] = 1;

                        //$events[$key]['ProfileBanner'] = get_profile_cover($event_banner);
                    }


                    if (!empty($input['EventID'])) {
                        $events[$key]['Presence'] = $this->getEventMemberCount($input['EventID']);
                    }
                    unset($events[$key]['EventID']);
                    unset($events[$key]['ProfileBannerID']);
                    unset($events[$key]['ProfileImageID']);
                    unset($events[$key]['LocationID']);
                    unset($events[$key]['CategoryID']);
                }
            }
            return $events;
        }
    }

    /**
     * [get_event_locations description]
     * @param  [array] $input      [Request data]
     * @param  [string] $user_id   [user id]
     * @param  string $event_type  [event type]
     * @return [array]             [description]
     */
    public function get_event_locations($input, $user_id = '', $event_type = 'AllPublicEvents') {
        $filter = isset($input['Filter']) ? $input['Filter'] : '';
        $friend_ids = array();
        if (isset($event_type) && $event_type == 'Suggested' && !empty($user_id)) {
            $this->load->model('users/user_model');
            $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        }

        $current_user_group_ids = array();
        if (!empty($user_id)) {
            $this->load->model('group/group_model');
            $current_user_group_ids = $this->group_model->get_users_groups($user_id);
        }
        $current_user_group_ids[] = 0;

        $this->db->select('L.CityID, C.Name as CityName, E.LocationID, L.Latitude, L.Longitude, COALESCE(0) UserLocation', false);
        $this->db->from(EVENTS . ' AS E');
        $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID');
        $this->db->join(LOCATIONS . ' AS L', 'L.LocationID=E.LocationID');
        $this->db->join(CITIES . ' AS C', 'L.CityID=C.CityID', 'LEFT');
        $this->db->join(STATES . ' AS S', 'S.StateID=C.StateID', 'LEFT');
        $this->db->join(COUNTRYMASTER . ' AS CTM', 'L.CountryID=CTM.CountryID', 'LEFT');

        if (!empty($input['CategoryIDs'])) {
            $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT");
            $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");
            if (is_array($input['CategoryIDs'])) {
                $this->db->where_in('CM.CategoryID', $input['CategoryIDs']);
            } else {
                $this->db->where('CM.CategoryID', $input['CategoryIDs']);
            }
        }

        $this->common_event_list_condition($input, $user_id, $event_type, $friend_ids, $current_user_group_ids);

        $this->db->group_by('L.CityID');

        $res = $this->db->get();
        $result = array();
        if ($res->num_rows() > 0) {
            $result = $res->result_array();
        }
        return $result;
    }

    /**
     * [get_event_categories description]
     * @param  [array] $input      [Request data]
     * @param  [string] $user_id   [user id]
     * @param  string $event_type  [event type]
     * @return [array]             [description]
     */
    public function get_event_categories($input, $user_id = '', $event_type = 'AllPublicEvents') {
        $filter = isset($input['Filter']) ? $input['Filter'] : '';
        $friend_ids = array();
        if (isset($event_type) && $event_type == 'Suggested' && !empty($user_id)) {
            $this->load->model('users/user_model');
            $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        }

        $current_user_group_ids = array();
        if (!empty($user_id)) {
            $this->load->model('group/group_model');
            $current_user_group_ids = $this->group_model->get_users_groups($user_id);
        }
        $current_user_group_ids[] = 0;

        $this->db->select('IFNULL( CM.Name, "") AS CategoryName, CM.CategoryID', false);
        $this->db->from(EVENTS . ' AS E');
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT");
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");
        $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID');
        $this->db->join(LOCATIONS . ' AS L', 'L.LocationID=E.LocationID');

        if (isset($input['CityID']) && !empty($input['CityID'])) {
            $citys = $input['CityID'];
            $city_ids = implode($citys, ",");
            $this->db->join(CITIES . ' AS C', 'C.CityID=L.CityID AND C.CityID IN (' . $city_ids . ')');
        }
        $this->common_event_list_condition($input, $user_id, $event_type, $friend_ids, $current_user_group_ids);
        $this->db->group_by('CM.CategoryID');

        $res = $this->db->get();
        $result = array();
        if ($res->num_rows() > 0) {
            //echo $this->db->last_query();die;
            $result = $res->result_array();
        }
        return $result;
    }

    /**
     * [get_events_by_user_location description]
     * @param  [array] $input      [Request data]
     * @param  [string] $user_id   [user id]
     * @param  string $event_type  [event type]
     * @param  string $num_rows    [num rows flag]
     * @param  string $page_no     [page no]
     * @param  string $page_size   [page size]
     * @return [array]             [description]
     */
    public function get_events_by_user_location($input, $user_id = '', $event_type = 'AllPublicEvents', $num_rows = '', $page_no = '', $page_size = '') {
        $filter = isset($input['Filter']) ? $input['Filter'] : '';
        $friend_ids = array();
        if (isset($event_type) && $event_type == 'Suggested') {
            $this->load->model('users/user_model');
            $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        }

        $current_user_group_ids = array();
        if (!empty($user_id)) {
            $this->load->model('group/group_model');
            $current_user_group_ids = $this->group_model->get_users_groups($user_id);
        }
        $current_user_group_ids[] = 0;

        $this->db->select('E.*,if(EU.Presence = "INVITED", 1, 0) IsInvited, DATE_FORMAT(E.LastActivity,"%d %b,%Y") AS LastActivity, DATE_FORMAT(E.StartTime,"%l:%i %p") AS StartTime,DATE_FORMAT(E.EndTime,"%l:%i %p") AS EndTime', false);
        $this->db->select('IFNULL( CM.Name, "") AS CategoryName, CM.CategoryID,U.UserGUID', false);
        $this->db->from(EVENTS . ' AS E');
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT");
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");
        $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID');
        $this->db->join(USERS . ' AS U', 'U.UserID=E.CreatedBy');
        $this->db->join(USERDETAILS . ' AS UD', 'U.UserID=UD.UserID');
        $this->db->join(CITIES . ' AS C', 'UD.CityID=C.CityID');
        $this->db->join(COUNTRYMASTER . ' AS CTM', 'UD.CountryID=CTM.CountryID');
        $this->db->join(LOCATIONS . ' AS L', 'L.LocationID=E.LocationID AND CTM.CountryID=L.CountryID AND C.CityID=L.CityID');

        if (!empty($input['CategoryIDs'])) {
            if (is_array($input['CategoryIDs'])) {
                $this->db->where_in('CM.CategoryID', $input['CategoryIDs']);
            } else {
                $this->db->where('CM.CategoryID', $input['CategoryIDs']);
            }
        }

        $this->common_event_list_condition($input, $user_id, $event_type, $friend_ids, $current_user_group_ids);
        $this->db->group_by('E.EventID');

        if ($page_size) { // Check for pagination
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }

        if ($num_rows) {
            return $this->db->get()->num_rows();
        } else {
            $result = $this->db->get()->result_array();
            //echo $this->db->last_query();die;
            $events = array();
            if ($result) {
                $this->load->helper('location');
                foreach ($result as $key => $val) {
                    //$event_users = $this->members($val['EventID'], $user_id);
                    $event_users = $this->event_members($val['EventID'], $user_id);
                    $creator_info = get_detail_by_id($val['CreatedBy'], 3, 'FirstName,LastName,ProfilePicture', 2);

                    $events[] = $val;
                    $events[$key]['loggedUserPresence'] = $this->get_user_presence($user_id, $val['EventID']);
                    $events[$key]['EventUsers'] = $event_users;
                    $events[$key]['UsersCount'] = $events[$key]['MemberCount'] = $this->event_members($val['EventID'], $user_id, TRUE);
                    $events[$key]['Location'] = get_location_by_id($val['LocationID']);
                    $events[$key]['ProfilePicture'] = $this->get_event_media($val['ProfileImageID'], $val['ProfileBannerID'], 'ProfilePicture');
                    $events[$key]['ProfileBanner'] = $this->get_event_media($val['ProfileImageID'], $val['ProfileBannerID'], 'ProfileBanner');

                    if (isset($events[$key]['Media']['ProfileImage']['MediaName']) && ($events[$key]['Media']['ProfileImage']['MediaName'])) {
                        $events[$key]['Media']['ProfileImage']['MediaName'] = 'event-placeholder.png';
                    }

                    $events[$key]['CreatedBy'] = $creator_info['FirstName'] . ' ' . $creator_info['LastName'];
                    $events[$key]['CreatedByProfilePicture'] = $creator_info['ProfilePicture'];
                    $events[$key]['CreatedByURL'] = get_entity_url($val['CreatedBy'], 'User', 1);
                    $events[$key]['EventUrl'] = $this->getViewEventUrl($val['EventGUID'], $val['Title']);

                    $events[$key]['IsAdmin'] = 0;

                    if ($user_id) {
                        $events[$key]['IsAdmin'] = $this->is_admin($val['EventID'], $user_id);
                    }
                    $events[$key]['EventDay'] = date('l', strtotime($events[$key]['StartDate']));
                    $events[$key]['EventStartDate'] = date('d', strtotime($events[$key]['StartDate']));
                    $events[$key]['EventStartMothnYear'] = date('M, Y', strtotime($events[$key]['StartDate']));
                    $events[$key]['EventStartMonth'] = date('M', strtotime($events[$key]['StartDate']));
                    if(strlen($events[$key]['Title'])>50)
                        $events[$key]['ShortTitle'] = substr($events[$key]['Title'],0,46)."...";
                    else
                        $events[$key]['ShortTitle'] = $events[$key]['Title'];

                    $events[$key]['IsCoverExists'] = 0;
                    $events[$key]['EventCoverImage'] = "";
                    $event_banner = '';
                    if (!empty($events[$key]['ProfileBanner'])) {
                        $event_banner = $events[$key]['ProfileBanner'];
                        $events[$key]['IsCoverExists'] = 1;

                        $events[$key]['ProfileBanner'] = get_profile_cover($event_banner);
                    }


                    if (!empty($input['EventID'])) {
                        $events[$key]['Presence'] = $this->getEventMemberCount($input['EventID']);
                    }
                    unset($events[$key]['EventID']);
                }
            }
            return $events;
        }
    }

    /**
     * [get_events_location_by_user description]
     * @param  [array] $input      [Request data]
     * @param  [string] $user_id   [user id]
     * @param  string $event_type  [event type]
     * @return [array]             [description]
     */
    public function get_events_location_by_user_location($input, $user_id = '', $event_type = 'AllPublicEvents') {
        $filter = isset($input['Filter']) ? $input['Filter'] : '';
        $friend_ids = array();
        if (isset($event_type) && $event_type == 'Suggested') {
            $this->load->model('users/user_model');
            $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        }

        $current_user_group_ids = array();
        if (!empty($user_id)) {
            $this->load->model('group/group_model');
            $current_user_group_ids = $this->group_model->get_users_groups($user_id);
        }
        $current_user_group_ids[] = 0;

        $this->db->select('L.CityID, L.StateID, L.CountryID,C.Name as CityName, S.Name as StateName,CTM.CountryName,E.LocationID,L.Latitude,L.Longitude, COALESCE(1) UserLocation', false);
        $this->db->from(EVENTS . ' AS E');
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT");
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");
        $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID');
        $this->db->join(USERS . ' AS U', 'U.UserID=E.CreatedBy');
        $this->db->join(USERDETAILS . ' AS UD', 'U.UserID=UD.UserID');
        $this->db->join(CITIES . ' AS C', 'UD.CityID=C.CityID');
        $this->db->join(COUNTRYMASTER . ' AS CTM', 'UD.CountryID=CTM.CountryID');
        $this->db->join(LOCATIONS . ' AS L', 'L.LocationID=E.LocationID AND CTM.CountryID=L.CountryID AND C.CityID=L.CityID');
        $this->db->join(STATES . ' AS S', 'L.StateID=C.StateID', 'LEFT');

        if (!empty($input['CategoryIDs'])) {
            if (is_array($input['CategoryIDs'])) {
                $this->db->where_in('CM.CategoryID', $input['CategoryIDs']);
            } else {
                $this->db->where('CM.CategoryID', $input['CategoryIDs']);
            }
        }
        $this->common_event_list_condition($input, $user_id, $event_type, $friend_ids, $current_user_group_ids);
        $this->db->group_by('L.CityID');

        $res = $this->db->get();
        $result = array();
        if ($res->num_rows() > 0) {
            $result = $res->result_array();
        }
        return $result;
    }

    /**
     * [get_event_categories description]
     * @param  [array] $input      [Request data]
     * @param  [string] $user_id   [user id]
     * @param  string $event_type  [event type]
     * @return [array]             [description]
     */
    public function get_events_categories_by_user_location($input, $user_id = '', $event_type = 'AllPublicEvents') {
        $filter = isset($input['Filter']) ? $input['Filter'] : '';
        $friend_ids = array();
        if (isset($event_type) && $event_type == 'Suggested') {
            $this->load->model('users/user_model');
            $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        }

        $current_user_group_ids = array();
        if (!empty($user_id)) {
            $this->load->model('group/group_model');
            $current_user_group_ids = $this->group_model->get_users_groups($user_id);
        }
        $current_user_group_ids[] = 0;

        $this->db->select('IFNULL( CM.Name, "") AS CategoryName, CM.CategoryID', false);
        $this->db->from(EVENTS . ' AS E');
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT");
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");
        $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID');
        $this->db->join(USERS . ' AS U', 'U.UserID=E.CreatedBy');
        $this->db->join(USERDETAILS . ' AS UD', 'U.UserID=UD.UserID');
        $this->db->join(CITIES . ' AS C', 'UD.CityID=C.CityID');
        $this->db->join(COUNTRYMASTER . ' AS CTM', 'UD.CountryID=CTM.CountryID');
        $this->db->join(LOCATIONS . ' AS L', 'L.LocationID=E.LocationID AND CTM.CountryID=L.CountryID AND C.CityID=L.CityID');
        $this->db->join(STATES . ' AS S', 'L.StateID=C.StateID', 'LEFT');

        /* if (isset($input['LocationID']) && !empty($input['LocationID'])) 
          {
          if(is_array($input['LocationID']))
          {
          $this->db->where_in('E.LocationID', $input['LocationID']);
          }
          else
          {
          $this->db->where('E.LocationID', $input['LocationID']);
          }
          } */
        if (isset($input['CityID']) && !empty($input['CityID'])) {
            //$citys = $input['CityID'];
            //$city_ids = implode($citys, ","); 
            $this->db->where_in('C.CityID', $input['CityID']);
            //$this->db->join(CITIES . ' AS C', 'C.CityID=L.CityID AND C.CityID IN ('.$city_ids.')');
        }

        $this->common_event_list_condition($input, $user_id, $event_type, $friend_ids, $current_user_group_ids);
        $this->db->group_by('CM.CategoryID');

        $res = $this->db->get();
        $result = array();
        if ($res->num_rows() > 0) {
            //echo $this->db->last_query();die;
            $result = $res->result_array();
        }
        return $result;
    }

    /**
     * [details Used to get event details]
     * @param  [String] $event_guid [Event GUID]
     * @param  [user_id] $user_id [Logged in user id]
     * @return [array]          [Event details]
     */
    function details($event_guid, $user_id, $is_edit = 0, $loggeinUserID = 0) {
        $this->load->helper('location');
        //$this->db->select('E.*,DATE_FORMAT(E.LastActivity,"%b %d,%Y") AS LastActivity, DATE_FORMAT(E.StartTime,"%l:%i %p") AS StartTime,DATE_FORMAT(E.EndTime,"%l:%i %p") AS EndTime', false);

        $this->db->select('DATE_FORMAT(E.LastActivity,"%d %b,%Y") AS LastActivity, DATE_FORMAT(E.StartTime,"%l:%i %p") AS StartTime,DATE_FORMAT(E.EndTime,"%l:%i %p") AS EndTime', false);

        $this->db->select('E.EventID, E.EventGUID, E.CreatedBy, E.Title, E.Summary, E.Description, E.Privacy, E.IsDeleted, E.StartDate, E.EndDate, E.ArchiveThreshold, E.ModuleID, E.ModuleEntityID', false);
        $this->db->select('IFNULL(E.Venue,"") as Venue', FALSE);
        $this->db->select('IFNULL(E.EventURL,"") as EventURL', FALSE);
        $this->db->select('IFNULL(E.LocationID,"") as LocationID', FALSE);
        $this->db->select('IFNULL(E.ProfileImageID,"") as ProfileImageID', FALSE);
        $this->db->select('IFNULL(E.ProfileBannerID,"") as ProfileBannerID', FALSE);

        $this->db->select('IFNULL( CM.Name, "") AS CategoryName, CM.CategoryID', false);
        $this->db->from(EVENTS . ' AS E');
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT"); // join to Get Event Category
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");
        //$this->db->join(EVENTUSERS.' AS EU','EU.EventID=E.EventID');

        $this->db->where('E.EventGUID', $event_guid);
        $this->db->where("E.IsDeleted!=1", NULL, FALSE);
        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        $events = array();
        if (!empty($result)) {
            foreach ($result as $Key => $event) {
                if(empty($is_edit)) {
                    $event['Description'] = nl2br($event['Description']);
                }
                
                $event['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'EVENT', $event['EventID']);
                $events[] = $event;
                $event_id = $event['EventID'];
                $location = get_location_by_id($event['LocationID']);
                if ($is_edit && !empty($location) && isset($location['TimeZoneID'])) {
                    $time_zone_id = $location['TimeZoneID'];
                    $this->load->model('timezone/timezone_model');
                    if (empty($time_zone_id) || is_null($time_zone_id)) {
                        $time_zone_id = $this->timezone_model->get_time_zone_id($location['Latitude'], $location['Longitude']);

                        $this->db->where('LocationID', $location['LocationID']);
                        $this->db->update(LOCATIONS, array('TimeZoneID' => $time_zone_id));

                        unset($location['LocationID']);
                    }
                    $time_zone = $this->timezone_model->get_time_zone_name($time_zone_id);
                } else {
                    $time_zone = get_user_time_zone($user_id);
                }

                $start_date_time = $this->timezone_model->convert_date_to_time_zone($event['StartDate'] . ' ' . $event['StartTime'], 'UTC', $time_zone);
                $end_date_time = $this->timezone_model->convert_date_to_time_zone($event['EndDate'] . ' ' . $event['EndTime'], 'UTC', $time_zone);
                $start_date_time = explode(' ', $start_date_time);
                $end_date_time = explode(' ', $end_date_time);
                $events[$Key]['IsPast'] = (strtotime($event['StartDate']) <= strtotime(date('Y-m-d'))) ? 1 : 0;
                $events[$Key]['StartDate'] = $start_date_time[0];
                $events[$Key]['StartTime'] = $start_date_time[1];
                $events[$Key]['EndDate'] = $end_date_time[0];
                $events[$Key]['EndTime'] = $end_date_time[1];

                // covert event datetime to user datetime
                if ($is_edit) {
                    $user_time_zone = get_user_time_zone($user_id);
                    if ($user_time_zone != $time_zone) {
                        $start_date_time = $this->timezone_model->convert_date_to_time_zone($event['StartDate'] . ' ' . $event['StartTime'], 'UTC', $user_time_zone);
                        $end_date_time = $this->timezone_model->convert_date_to_time_zone($event['EndDate'] . ' ' . $event['EndTime'], 'UTC', $user_time_zone);
                        $start_date_time = explode(' ', $start_date_time);
                        $end_date_time = explode(' ', $end_date_time);
                    }
                }

                $events[$Key]['DisplayStartTime'] = date('h:i A', strtotime($start_date_time[1]));
                $events[$Key]['DisplayEndTime'] = date('h:i A', strtotime($end_date_time[1]));

                // calculate time difference 
                $events[$Key]['TimeDifference'] = '';
                $start_date_time = $event['StartDate'] . " " . $event['StartTime'];
                $from = new DateTimeZone('UTC');
                $then = new DateTime($start_date_time, $from);

                $now = get_current_date('%Y-%m-%d %H:%i:%s');
                $to = new DateTimeZone('UTC');
                $now = new DateTime($now, $to);

                $diff = $then->diff($now);
                if (!empty($diff) && $diff->invert == 1) {
                    $str = '';
                    if ($diff->y != '') {
                        $y_str = ' Year ';
                        if ($diff->y > 1) {
                            $y_str = ' Years ';
                        }
                        $str .= $diff->y . $y_str;
                    }

                    if ($diff->m != '') {
                        $m_str = ' Month ';
                        if ($diff->m > 1) {
                            $m_str = ' Months ';
                        }
                        $str .= $diff->m . $m_str;
                    }

                    if ($diff->d != '') {
                        $d_str = ' Day ';
                        if ($diff->d > 1) {
                            $d_str = ' Days ';
                        }
                        $str .= $diff->d . $d_str;
                    }

                    if ($diff->h > 1) {

                        $str .= ($diff->h > 9 ? $diff->h : "0" . $diff->h) . ":" . ($diff->i > 9 ? $diff->i : "0" . $diff->i) . " Hours Remaining";
                    } else {
                        $str .= "0" . $diff->h . ":" . ($diff->i > 9 ? $diff->i : "0" . $diff->i) . " Hour Remaining";
                    }

                    $events[$Key]['TimeDifference'] = $str;
                }

                $events[$Key]['EventUsers'] = $this->event_members($event['EventID'], $user_id);
                $events[$Key]['EventDay'] = date('l', strtotime($events[$Key]['StartDate']));
                $events[$Key]['EventStartDate'] = date('d F, Y', strtotime($events[$Key]['StartDate']));
                $events[$Key]['EventEndDate'] = date('d F, Y', strtotime($events[$Key]['EndDate']));
                $events[$Key]['ProfilePicture'] = $this->get_event_media($event['ProfileImageID'], $event['ProfileBannerID'], 'ProfilePicture');
                $events[$Key]['ProfileURL'] = $this->getViewEventUrl($event['EventGUID'], $event['Title']);
                $events[$Key]['ProfileBanner'] = $this->get_event_media($event['ProfileImageID'], $event['ProfileBannerID'], 'ProfileBanner');

                if ($this->can_post_on_wall($event['EventID'], $user_id)) {
                    $events[$Key]['CanPostOnWall'] = 1;
                } else {
                    $events[$Key]['CanPostOnWall'] = 0;
                }

                $events[$Key]['IsCoverExists'] = 0;
                $event_banner = '';
                if (!empty($events[$Key]['ProfileBanner'])) {
                    $event_banner = $events[$Key]['ProfileBanner'];
                    $events[$Key]['IsCoverExists'] = 1;
                    //$events[$Key]['ProfileBanner'] = get_profile_cover($event_banner);
                }

                $events[$Key]['Location'] = $location;
                $events[$Key]['CreatedBy'] = $this->created_by($event['CreatedBy']);
                $events[$Key]['CreatedByUserID'] = $event['CreatedBy'];
                $events[$Key]['LoggedInUserDefaultPrivacy'] = $this->privacy_model->get_default_privacy($loggeinUserID);
                $events[$Key]['ModuleRoleID'] = 0;

                $events[$Key]['loggedUserPresence'] = $this->get_user_presence($user_id, $event_id);
                $events[$Key]['IsAdmin'] = 0;
                if ($user_id) {
                    $events[$Key]['IsAdmin'] = $this->is_admin($event_id, $user_id);
                    $events[$Key]['ModuleRoleID'] = $this->get_module_role_id($event_id, $user_id);
                }
                $MemberCount = $this->members($event_id, $user_id, TRUE);

                $this->load->model('media/media_model');
                $events[$Key]['TotalMediaCount'] = $this->media_model->getEventMediaCount(14, $event_id);

                $events[$Key]['MemberCount'] = $MemberCount;
                $events[$Key]['Locations'] = $this->get_locations($event_id);
                $events[$Key]['Presence'] = $this->getEventMemberCount($event_id);
                $events[$Key]['CoverImageState'] = get_cover_image_state($user_id, $event_id, 14);
                unset($events[$Key]['EventID']);
                unset($events[$Key]['ProfileBannerID']);
                unset($events[$Key]['ProfileImageID']);
                unset($events[$Key]['LocationID']);
                //unset($events[$Key]['CategoryID']);
            }
        }
        return $events;
    }

    public function get_all_members_id($event_id) {
        $users = array();
        $this->db->select('UserID');
        $this->db->from(EVENTUSERS);
        $this->db->where('EventID', $event_id);
        $this->db->where_in('Presence', array('ATTENDING', 'ARRIVED', 'MAY_BE'));
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $arr) {
                $users[] = $arr['UserID'];
            }
        }
        return $users;
    }

    /**
     * [members_id get event member id list]
     * @param  [type]  $event_id       [event id]
     * @param  boolean $count_flag     [count flag]
     * @param  string  $search_keyword [search keyword]
     * @param  string  $page_no        [page no]
     * @param  string  $page_size      [page size]
     * @param  string  $filter         [filter]
     * @return [array/int]             [based on count flag]
     */
    public function members_id($event_id, $count_flag = FALSE, $search_keyword = '', $page_no = '', $page_size = '', $filter = '') {
        $this->db->select('U.UserID', False);
        $this->db->from(EVENTUSERS . " AS EU");
        $this->db->join(USERS . " AS U", "EU.UserID=U.UserID");
        $this->db->where("EventID", $event_id);
        $this->db->where("EU.IsDeleted", 0);
        if ($filter) {
            if ($filter == 'Member') {
                $this->db->where('ModuleRoleID', '3');
            } else if ($filter == 'Admin') {
                $this->db->where_in('ModuleRoleID', array('1', '2'));
            }
        }
        if (!empty($page_size)) { // Check for pagination
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }

        if (!empty($search_keyword)) {
            $this->db->where('(FirstName LIKE "' . $search_keyword . '%" OR U.LastName LIKE "' . $search_keyword . '%" OR EU.Presence LIKE "' . strtoupper($search_keyword) . '%")');
        }

        if (empty($count_flag)) { // check if array needed
            return $this->db->get()->result();
        } else {
            return $this->db->get()->num_rows();
        }
    }

    public function check_event_current_status($event_id) {
        $this->db->select('EventID');
        $this->db->from(EVENTS);
        $this->db->where('EventID', $event_id);
        $this->db->where("concat(EndDate,' ',EndTime)>='" . get_current_date('%Y-%m-%d %H:%i:%s') . "'", null, false);
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [update_invite_status used to update user presence]
     * @param  [type]  $user_id         [user id]
     * @param  [type]  $event_id        [Event ID]
     * @param  [type]  $old_status      [Old Status]
     * @param  [type]  $target_status   [Target Status]
     * @return [type]                  [description]
     */
    public function update_invite_status($user_id, $event_id, $old_status, $target_status) {
        if ($old_status != $target_status) {
            $this->db->where(array('UserID' => $user_id, 'EventID' => $event_id));
            $this->db->update(EVENTINVITES, array('Status' => $target_status)); // Update Flag in table
        }

        $previous_status = $this->db->get_where(EVENTUSERS, array('UserID' => $user_id, 'EventID' => $event_id));
        if ($previous_status->num_rows() == 0) {

            $invite_by = $user_id;
            $Presence = $this->db->get_where(EVENTINVITES, array(
                        'UserID' => $user_id,
                        "EventID" => $event_id
                    ))->row_array();

            if (!empty($Presence)) {
                $invite_by = $Presence['InviteBy'];
            }

            // insert data into eventuser table
            $InsertData['EventID'] = $event_id;
            $InsertData['UserID'] = $user_id;
            $InsertData['Presence'] = $old_status;
            $InsertData['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $InsertData['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $InsertData['ModifiedBy'] = $user_id;
            $InsertData['ModuleRoleID'] = 3;
            $InsertData['CreatedBy'] = $invite_by;
            $this->db->insert(EVENTUSERS, $InsertData); // Update Flag in table
            // Update Last Activity date time
            set_last_activity_date($event_id, 14);
        }
    }

    /**
     * [update_presence used to update user presence]
     * @param  [type]  $user_id         [user id]
     * @param  [type]  $event_id        [Event ID]
     * @param  [type]  $target_presence [Target Presence]
     * @param  boolean $is_insert       [is insert flag]
     * @return [type]                  [description]
     */
    public function update_presence($user_id, $event_id, $target_presence, $is_insert = false) {
        $this->load->helper('activity');
        $previous_status = '';
        if ($is_insert) {

            // insert data into eventuser table
            $InsertData['EventID'] = $event_id;
            $InsertData['UserID'] = $user_id;
            $InsertData['Presence'] = $target_presence;
            $InsertData['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $InsertData['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $InsertData['ModifiedBy'] = $user_id;
            $InsertData['ModuleRoleID'] = 3;
            $InsertData['CreatedBy'] = $user_id;
            $this->db->insert(EVENTUSERS, $InsertData); // Update Flag in table
            // Update Last Activity date time
            set_last_activity_date($event_id, 14);
        } else {
            if ($target_presence == 'NOT_ATTENDING') {
                $this->db->delete(EVENTUSERS, array('UserID' => $user_id, 'EventID' => $event_id)); // Update Flag in table
            } else {
                $previous_status = $this->db->get_where(EVENTUSERS, array('UserID' => $user_id, 'EventID' => $event_id))->row()->Presence;

                $this->db->where(array('UserID' => $user_id, 'EventID' => $event_id));
                $this->db->update(EVENTUSERS, array('Presence' => $target_presence)); // Update Flag in table
                // Update Last Activity date time
                set_last_activity_date($event_id, 14);
            }
        }

        if ($target_presence == 'ATTENDING' && $previous_status == 'INVITED') {
            $this->load->model('notification_model');
            $parameters = array();
            $parameters[0]['ReferenceID'] = $user_id;
            $parameters[0]['Type'] = 'User';
            $parameters[1]['ReferenceID'] = $event_id;
            $parameters[1]['Type'] = 'Event';

            $EventOwner = $this->getEventOwner($event_id);
            $EventAdmins = $this->getEventAdmins($event_id);

            $this->notification_model->add_notification(31, $user_id, array($EventOwner), $event_id, $parameters);
            if ($EventAdmins) {
                $this->notification_model->add_notification(32, $user_id, $EventAdmins, $event_id, $parameters);
            }
        } else {
            $this->SendUpdatePresenceNotification($user_id, $event_id, $target_presence, $is_insert); // Send Notification to event users
        }

        if ($target_presence == 'NOT_ATTENDING') {
            $this->load->model(array('reminder/reminder_model'));
            $this->reminder_model->delete_all($user_id, 14, $event_id);

            $status_id = 3;
            $this->load->model('subscribe_model');
            $this->subscribe_model->update_subscription($user_id, 'EVENT', $event_id, $status_id);
        }

        if ($target_presence == 'ATTENDING') {
            notify_node('liveFeed', array('Type' => 'EJ', 'UserID' => $user_id, 'EntityGUID' => get_detail_by_id($event_id, 14, 'EventGUID', 1)));

            // Save score on event attend
            $this->load->model(array('log/user_activity_log_score_model'));
            $score = $this->user_activity_log_score_model->get_score_for_activity(31, 14, 0, $user_id);
            $data = array(
                'ModuleID' => 14, 'ModuleEntityID' => $event_id, 'UserID' => $user_id, 'ActivityTypeID' => 31,
                'ActivityDate' => get_current_date('%Y-%m-%d'), 'PostAsModuleID' => '3', 'PostAsModuleEntityID' => $user_id, 'EntityID' => $event_id, 'Score' => $score,
            );
            $this->user_activity_log_score_model->add_activity_log($data);
        }

        if ($target_presence == 'ATTENDING' || $target_presence == 'MAY_BE') {
            $this->subscribe_model->subscribe_email($user_id, $event_id, 'event_attending');
        }

        return true;
    }

    /**
     * [members To get users of an event]
     * @param  [int] $event_id       [event id]
     * @param  string $count_flag     [count flag]
     * @param  string $search_keyword [search keyword]
     * @param  [string] $page_no        [page number]
     * @param  [string] $page_size      [page size]
     * @return [type]                 [description]
     */
    public function members($event_id, $user_id, $count_flag = FALSE, $search_keyword = '', $page_no = '', $page_size = '', $filter = '', $attending_only = false) {

        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        $friend_ids = array();
        if (!empty($user_id) && ($filter == 'Member' || $filter == 'FriendCount')) {
            /* $this->load->model('users/friend_model');
              $friend_ids = $this->friend_model->getFriendIDS($user_id);
             */

            $this->load->model('users/user_model');
            $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);

            $user_id_arr = implode(',', $friend_ids);
            $order_by = " (CASE when FIND_IN_SET(EU.UserID, '" . $user_id_arr . "') THEN 1 else 0 END) DESC ";
        }

        $this->db->select('CONCAT(Firstname," ",Lastname) AS FullName,EU.CanPostOnWall,ProfilePicture,Email,U.UserID,UserGUID,ModuleRoleID,Presence,P.Url as ProfileLink', False);
        $this->db->from(EVENTUSERS . " AS EU");
        $this->db->join(USERS . " AS U", "EU.UserID=U.UserID");
        $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
        $this->db->where("EventID", $event_id);
        $this->db->where("EU.IsDeleted", 0);
        $this->db->where_not_in("U.StatusID", array(3, 4));
        if ($attending_only) {
            $this->db->where("EU.Presence='ATTENDING'", NULL, FALSE);
        } else {
            $this->db->where("EU.Presence!='NOT_ATTENDING'", NULL, FALSE);
        }
        if ($filter) {
            if ($filter == 'Member') {
                $this->db->where('ModuleRoleID', '3');
            } else if ($filter == 'Admin') {
                $this->db->where_in('ModuleRoleID', array('1', '2'));
            } else if ($filter == 'FriendCount') {
                $this->db->where('ModuleRoleID', '3');
                if (!empty($friend_ids)) {
                    $this->db->where_in('EU.UserID', $friend_ids);
                }
            }
        }

        if (!empty($search_keyword)) {
            $this->db->where('(FirstName LIKE "' . $search_keyword . '%" OR U.LastName LIKE "' . $search_keyword . '%" OR EU.Presence LIKE "' . strtoupper($search_keyword) . '%")');
        }

        if (!empty($blocked_users)) {
            $this->db->where("IF(U.UserID is not null,U.UserID NOT IN (" . implode(',', $blocked_users) . "),true)", null, false);
        }

        if (empty($count_flag)) { // check if array needed
            if (!empty($friend_ids)) {
                $this->db->order_by($order_by, FALSE);
            }

            if (!empty($page_size)) { // Check for pagination
                $offset = ($page_no - 1) * $page_size;
                $this->db->limit($page_size, $offset);
            }
            $query = $this->db->get();
            $result = $query->result_array();
            foreach ($result as $key => $val) {
                $permission = $this->privacy_model->check_privacy($user_id, $val['UserID'], 'view_profile_picture');
                if (!$permission) {
                    $result[$key]['ProfilePicture'] = '';
                }
                unset($result[$key]['UserID']);
            }
            return $result;
        } else {
            return $this->db->get()->num_rows();
        }
    }

    /**
     * [invitees_list To get users of an event]
     * @param  [int] $event_id       [event id]
     * @param  string $count_flag     [count flag]
     * @param  string $search_keyword [search keyword]
     * @param  [string] $page_no        [page number]
     * @param  [string] $page_size      [page size]
     * @return [type]                 [description]
     */
    public function invitees_list($event_id, $user_id, $count_flag = FALSE, $search_keyword = '', $page_no = '', $page_size = '') {
        $this->db->select('CONCAT(Firstname," ",Lastname) AS FullName,ProfilePicture,Email,U.UserID,UserGUID,Status,P.Url as ProfileLink', False);
        $this->db->from(EVENTINVITES . " AS EI");
        $this->db->join(USERS . " AS U", "EI.UserID=U.UserID");
        $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
        $this->db->where("EI.EventID", $event_id);
        $this->db->where("EI.InviteBy", $user_id);

        if (!empty($search_keyword)) {
            $this->db->where('(FirstName LIKE "' . $search_keyword . '%" OR U.LastName LIKE "' . $search_keyword . '%" OR EU.Presence LIKE "' . strtoupper($search_keyword) . '%")');
        }

        if (empty($count_flag)) { // check if array needed
            if (!empty($page_size)) { // Check for pagination
                $offset = ($page_no - 1) * $page_size;
                $this->db->limit($page_size, $offset);
            }
            $query = $this->db->get();
            $result = $query->result_array();
            return $result;
        } else {
            return $this->db->get()->num_rows();
        }
    }

    /**
     * [event_members To get users of an event]
     * @param  [int] $event_id       [event id]
     * @param  string $count_flag     [count flag]
     * @param  string $search_keyword [search keyword]
     * @param  [string] $page_no        [page number]
     * @param  [string] $page_size      [page size]
     * @return [type]                 [description]
     */
    public function event_members_id($event_id, $user_id, $count_flag = FALSE, $page_size = PAGE_SIZE) {
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        $friend_ids = array();
        if (!empty($user_id)) {
            $this->load->model('users/user_model');
            $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE);
        }

        $this->db->select('CONCAT(Firstname," ",Lastname) AS FullName,EU.CanPostOnWall,ProfilePicture,Email,U.UserID,UserGUID,ModuleRoleID,Presence,P.Url as ProfileLink', False);
        $this->db->from(EVENTUSERS . " AS EU");
        $this->db->join(USERS . " AS U", "EU.UserID=U.UserID");
        $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
        $this->db->where("EventID", $event_id);
        $this->db->where("EU.IsDeleted", 0);
        $this->db->where("EU.Presence='ATTENDING'", NULL, FALSE);
        $this->db->where_not_in("U.StatusID", array(3, 4));
        //$this->db->where('EU.UserID !=', $user_id);
        //$this->db->where('ModuleRoleID !=', '1');
        if (!empty($blocked_users)) {
            $this->db->where("IF(U.UserID is not null,U.UserID NOT IN (" . implode(',', $blocked_users) . "),true)", null, false);
        }
        $this->db->order_by('ModuleRoleID', 'DESC');

        if (empty($count_flag)) { // check if array needed
            if (!empty($friend_ids)) {
                $user_id_arr = implode(',', $friend_ids);
                $order_by = " (CASE when FIND_IN_SET(EU.UserID, '" . $user_id_arr . "') THEN 1 else 0 END) DESC ";
                $this->db->order_by($order_by, FALSE);
            }
            $this->db->limit($page_size);
            $query = $this->db->get();
            $result = $query->result_array();
            $data = array();
            foreach ($result as $key => $val) {
                $data[] = $result[$key]['UserID'];
                //unset($result[$key]['UserID']);
            }
            return $data;
        } else {
            return $this->db->get()->num_rows();
        }
    }

    /**
     * [event_members To get users of an event]
     * @param  [int] $event_id       [event id]
     * @param  string $count_flag     [count flag]
     * @param  string $search_keyword [search keyword]
     * @param  [string] $page_no        [page number]
     * @param  [string] $page_size      [page size]
     * @return [type]                 [description]
     */
    public function event_members($event_id, $user_id, $count_flag = FALSE, $page_size = PAGE_SIZE) {
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        $friend_ids = array();
        if (!empty($user_id)) {
            $this->load->model('users/user_model');
            $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE);
        }

        $this->db->select('CONCAT(Firstname," ",Lastname) AS FullName,EU.CanPostOnWall,ProfilePicture,Email,U.UserID,UserGUID,ModuleRoleID,Presence,P.Url as ProfileLink', False);
        $this->db->from(EVENTUSERS . " AS EU");
        $this->db->join(USERS . " AS U", "EU.UserID=U.UserID");
        $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
        $this->db->where("EventID", $event_id);
        $this->db->where("EU.IsDeleted", 0);
        $this->db->where("EU.Presence='ATTENDING'", NULL, FALSE);
        $this->db->where_not_in("U.StatusID", array(3, 4));
        //$this->db->where('EU.UserID !=', $user_id);
        //$this->db->where('ModuleRoleID !=', '1');
        if (!empty($blocked_users)) {
            $this->db->where("IF(U.UserID is not null,U.UserID NOT IN (" . implode(',', $blocked_users) . "),true)", null, false);
        }
        $this->db->order_by('ModuleRoleID', 'DESC');

        if (empty($count_flag)) { // check if array needed
            if (!empty($friend_ids)) {
                $user_id_arr = implode(',', $friend_ids);
                $order_by = " (CASE when FIND_IN_SET(EU.UserID, '" . $user_id_arr . "') THEN 1 else 0 END) DESC ";
                $this->db->order_by($order_by, FALSE);
            }
            $this->db->limit($page_size);
            $query = $this->db->get();
            $result = $query->result_array();
            foreach ($result as $key => $val) {
                $permission = $this->privacy_model->check_privacy($user_id, $val['UserID'], 'view_profile_picture');
                if (!$permission) {
                    $result[$key]['ProfilePicture'] = '';
                }
                unset($result[$key]['UserID']);
            }
            return $result;
        } else {
            return $this->db->get()->num_rows();
        }
    }

    /**
     * [created_by - details of event owner]
     * @param  $user_id
     * @return [user details - Name,UserGUID,ProfilePicture,ProfileURL]
     */
    function created_by($user_id) {

        $this->db->select("CONCAT(U.FirstName,' ',U.LastName) as Name, U.UserGUID, IF(U.ProfilePicture='','user_default.jpg', U.ProfilePicture) as ProfilePicture, P.Url as ProfileURL", false);
        $this->db->from(USERS . ' U');
        $this->db->join(PROFILEURL . ' P', 'U.UserID=P.EntityID', 'left');
        $this->db->where('P.EntityType', 'User');
        $this->db->where('U.UserID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $result = $query->row_array();
            return $result;
        } else {
            return array();
        }
    }

    /**
     * [get_event_owner_detail to get event user detail]
     * @param  [type]  $event_guid  [description]
     * @return number          
     */
    public function get_event_owner_detail($event_guid, $user_id = '') {
        $this->db->select("CONCAT(U.FirstName,' ',U.LastName) as Name, U.UserGUID,U.UserID,IF(U.ProfilePicture='','', U.ProfilePicture) as ProfilePicture, P.Url as ProfileURL", false);
        $this->db->from(USERS . ' U');
        $this->db->join(PROFILEURL . ' P', 'U.UserID=P.EntityID', 'left');
        $this->db->join(EVENTS . ' E', 'U.UserID=E.CreatedBy');
        $this->db->where('P.EntityType', 'User');
        $this->db->where('E.EventGUID', $event_guid);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $result = $query->row_array();
            // get user location
            $this->load->model('users/user_model');
            $result['location'] = $this->user_model->get_user_location($result['UserID']);
            $result['past_event_count'] = $this->get_user_event_count($result['UserID'], 'past');
            $result['upcoming_event_count'] = $this->get_user_event_count($result['UserID'], 'upcoming');

            $permission = $this->privacy_model->check_privacy($user_id, $result['UserID'], 'view_profile_picture');
            if (!$permission) {
                $result['ProfilePicture'] = '';
            }

            return $result;
        } else {
            return array();
        }
    }

    /**
     * [get_event_user_detail to get event user detail]
     * @param  [type]  $event_guid  [description]
     * @return number          
     */
    public function get_event_user_detail($event_guid) {
        /* $this->db->select("CONCAT(U.FirstName,' ',U.LastName) as Name, U.UserGUID,U.UserID,IF(U.ProfilePicture='','user_default.jpg', U.ProfilePicture) as ProfilePicture, P.Url as ProfileURL", false);
          $this->db->from(USERS . ' U');
          $this->db->join(PROFILEURL . ' P', 'U.UserID=P.EntityID', 'left');
          $this->db->join(EVENTS . ' E', 'U.UserID=E.CreatedBy');
          $this->db->where('P.EntityType', 'User');
          $this->db->where('E.EventGUID', $event_guid);
          $query = $this->db->get();
          if ($query->num_rows()) {
          $result = $query->row_array();
          // get user location
          $this->load->model('users/user_model');
          $result['location'] = $this->user_model->get_user_location($result['UserID']);
          $result['past_event_count'] = $this->get_user_event_count($result['UserID'],'past');
          $result['upcoming_event_count'] = $this->get_user_event_count($result['UserID'],'upcoming');

          return $result;
          } else {
          return array();
          } */
    }

    /**
     * [get_user_event_count to get count of event]
     * @param  [type]  $user_id  [description]
     * @return number          
     */
    public function get_user_event_count($user_id, $type = '') {
        $result = 0;
        $this->db->select('count(E.EventID) as total_event');
        $this->db->from(EVENTS . " as E");
        $this->db->where('E.CreatedBy', $user_id);
        if ($type == 'past') {
            $this->db->where("DATE_FORMAT(CONCAT(E.StartDate,' ',E.StartTime),'%Y-%m-%d %H:%i:%s') < '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
        } elseif ($type == 'upcoming') {
            $this->db->where("DATE_FORMAT(CONCAT(E.StartDate,' ',E.StartTime),'%Y-%m-%d %H:%i:%s') > '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
        }
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $res = $query->row_array();
            $result = $res['total_event'];
        }
        return $result;
    }

    /**
     * [is_admin used to check user is admin/owner of event]
     * @param  [type]  $EventID [description]
     * @param  [type]  $user_id  [description]
     * @return boolean          [description]
     */
    public function is_admin($event_id, $user_id) {
        $this->db->select('ModuleRoleID');
        $this->db->from(EVENTUSERS);
        $this->db->where('UserID', $user_id);
        $this->db->where('EventID', $event_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row_array();
            if ($row['ModuleRoleID'] == '1' || $row['ModuleRoleID'] == '2') {
                return 1;
            }
        }
        return 0;
    }

    /**
     * [module_role_id used to check user is admin/owner of event]
     * @param  [type]  $EventID [description]
     * @param  [type]  $user_id  [description]
     * @return boolean          [description]
     */
    public function get_module_role_id($event_id, $user_id) {
        $this->db->select('ModuleRoleID');
        $this->db->from(EVENTUSERS);
        $this->db->where('UserID', $user_id);
        $this->db->where('EventID', $event_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row_array();
            return $row['ModuleRoleID'];
        }
        return 0;
    }

    /**
     * [is_admin used to check user is admin/owner of event]
     * @param  [type]  $EventID [description]
     * @param  [type]  $user_id  [description]
     * @return boolean          [description]
     */
    public function get_member_role($event_id, $user_id, $role = 'Admin') {
        $this->db->select('ModuleRoleID,Presence');
        $this->db->from(EVENTUSERS);
        $this->db->where('UserID', $user_id);
        $this->db->where('EventID', $event_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row_array();
            if ($role == 'Admin' && ($row['ModuleRoleID'] == '1' || $row['ModuleRoleID'] == '2')) {
                return true;
            } else if ($role == 'Member') {
                if ($row['Presence'] == 'ATTENDING' || $row['Presence'] == 'MAY_BE') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * [invite_users Used To add event users]
     * @param [int] $event_id [event id]
     * @param [int] $user_id  [user id]
     * @param [int] $user_role_id  [Role id 3 - Member, 2 - Admin, 1 - Creator]
     * @param [int] $owner_id [owner id]
     * @param string $presence [presence for event like INVITED, MAYBE ATTENDING]
     * @param [bool] $update_flag [Used to update member details]
     */
    function invite_users($event_id, $user_id, $user_role_id = 3, $owner_id, $presence = "INVITED", $update_flag = FALSE) {
        $insert_data['EventID'] = $event_id;
        $insert_data['UserID'] = $user_id;
        $insert_data['Presence'] = $presence;
        $insert_data['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $insert_data['ModifiedBy'] = $owner_id;
        $insert_data['ModuleRoleID'] = $user_role_id;
        $insert_data['CreatedBy'] = $owner_id;
        if ($update_flag) {
            $insert_data['IsDeleted'] = 1;
            $this->db->update(EVENTUSERS, $insert_data);
        } else {
            $insert_data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $this->db->insert(EVENTUSERS, $insert_data);
        }

        $this->SendEventInviteNotification($owner_id, $event_id, array($insert_data));

        // Update Last Activity date time
        $this->load->helper('activity');
        set_last_activity_date($event_id, 14);
    }

    /* -------------------------------------------------
      | @Method - Get User's presence for particualr event
      | @Params - EventID(int),UserID(int)
      | @Output - array
      -------------------------------------------------- */

    function get_user_presence($user_id, $event_id) {
        $presence = $this->db->get_where(EVENTUSERS, array(
                    'UserID' => $user_id,
                    "EventID" => $event_id
                ))->row_array();

        if (!empty($presence)) {
            return $presence['Presence'];
        } else {
            $presence = $this->db->get_where(EVENTINVITES, array(
                        'UserID' => $user_id,
                        "EventID" => $event_id
                    ))->row_array();

            if (!empty($presence)) {
                return $presence['Status'];
            }
            return "NOT_ATTENDING";
        }
    }

    /**
     * [get_user_event_role Get user's role in particular event]
     * @param  [int] $user_id  [user id]
     * @param  [int] $event_id [event id]
     * @return [type]          [description]
     */
    function get_user_event_role($user_id, $event_id) {
        if (!empty($event_id) && !empty($user_id)) {
            $this->db->select('MR.ModuleRoleID');
            $this->db->from(MODULEROLES . " AS MR");
            $this->db->join(EVENTUSERS . " AS EU", 'EU.ModuleRoleID=MR.ModuleRoleID');
            $row = $this->db->where(array('EventID' => $event_id, "UserID" => $user_id))->get()->row();
            if (!empty($row)) {
                return $row->ModuleRoleID;
            }
        }
        return false;
    }

    /**
     * [friend_suggestion Get List of friends to make them evemt member or to Send Invitation]
     * @param  [string] $search_keyword [Search Keyword]
     * @param  [int] $user_id    [user id]
     * @param  [int] $event_id   [event id]
     * @return [array]          [description]
     */
    function friend_suggestion($search_keyword, $user_id, $event_id, $module_id = 3, $module_entity_id = 0) {

        $blockedUsers = $this->activity_model->block_user_list($user_id, 3);

        $this->load->model('users/user_model');
        $friend_ids = array(0);
        if ($module_id == 3) {
            $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE);
        }
        // old code
        /* $this->db->select('U.UserGUID, U.FirstName, U.LastName');
          $this->db->from(FRIENDS . ' F');
          $this->db->join(USERS . ' U', 'U.UserID = F.FriendID');
          $this->db->where('F.UserID', $user_id);
          $this->db->where(' (F.FriendID not in (select UserID from ' . EVENTUSERS . ' EU where EU.EventID = ' . $event_id . ' AND EU.IsDeleted=0)) ', NUll, FALSE);
          $this->db->where("(U.FirstName like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_keyword) . "%' or concat(U.FirstName,' ',	U.LastName) like '" . $this->db->escape_like_str($search_keyword) . "%')", NULL, FALSE); */

        // new code
        $this->db->select('U.UserGUID, U.FirstName, U.LastName');
        $this->db->from(USERS . ' U');
        $this->db->where('U.UserID !=', $user_id);
        $this->db->where(' (U.UserID not in (select UserID from ' . EVENTUSERS . ' EU where EU.EventID = ' . $event_id . ' AND EU.IsDeleted=0)) ', NUll, FALSE);
        $this->db->where("(U.FirstName like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_keyword) . "%' or concat(U.FirstName,' ',   U.LastName) like '" . $this->db->escape_like_str($search_keyword) . "%')", NULL, FALSE);



        if ($module_id == 18) {
            $this->db->join(PAGEMEMBERS . " as PM", "U.UserID = PM.UserID");
            $this->db->where('PM.PageID', $module_entity_id);
        } else if ($module_id == 1) {
            $this->db->join(GROUPMEMBERS . " as GM", "U.UserID = GM.ModuleEntityID AND GM.ModuleID = 3");
            $this->db->where('GM.GroupID', $module_entity_id);
        } else if ($module_id == 3) {
            $this->db->where_in('U.UserID', $friend_ids);
        }

        if ($blockedUsers) {
            $this->db->where_not_in('U.UserID', $blockedUsers);
        }
        $this->db->group_by('U.UserID');
        $query = $this->db->get();
        $result = array();  //echo $this->db->last_query(); die;

        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            foreach ($result as $key => $value) {
                $result[$key]['Name'] = stripcslashes($result[$key]['FirstName']) . ' ' . stripcslashes($result[$key]['LastName']);
                unset($result[$key]['FirstName']);
                unset($result[$key]['LastName']);
            }
        }
        return $result;
    }

    /**
     * [toggle_user_role ,Owner/Creator/Admin of event can assign/remove user as a admin]
     * @param  [int] 		$data    		[Event details]
     */
    function toggle_user_role($data, $user_id = 0) {
        if (!empty($data)) {
            if ($data['RoleAction'] == 'Add') {
                $update_data = array('ModuleRoleID' => $data['RoleID']);
                if ($data['RoleID'] == 2) {
                    $update_data['CanPostOnWall'] = 1;
                }
                $this->db->where('EventID', $data['EventID']);
                $this->db->where('UserID', $data['UserID']);
                $this->db->update(EVENTUSERS, $update_data);
                $result['Message'] = lang('event_role_changed');
                if ($data['RoleID'] == 2) {
                    $parameters = array();
                    $parameters[0]['ReferenceID'] = $data['EventID'];
                    $parameters[0]['Type'] = 'Event';
                    $this->notification_model->add_notification(67, $user_id, array($data['UserID']), $data['EventID'], $parameters);
                }
            } elseif ($data['RoleAction'] == 'Remove') {
                $this->db->where('EventID', $data['EventID']);
                $this->db->where('UserID', $data['UserID']);
                $this->db->update(EVENTUSERS, array('ModuleRoleID' => 3));
                $result['Message'] = lang('event_role_changed');
            }
        }
        return $result;
    }

    /**
     * [toggle_can_post_on_wall to change user's event wall post permission]
     * @param  [int] 	$event_id    	[Event Id]
     * @param  [int] 	$user_id  		[User Id]
     * @param [int] 	$can_post_on_wall		[0/1]		
     */
    function toggle_can_post_on_wall($event_id, $user_id, $can_post_on_wall) {
        $this->db->where('EventID', $event_id);
        $this->db->where('UserID', $user_id);
        $this->db->update(EVENTUSERS, array('CanPostOnWall' => $can_post_on_wall));
    }

    /**
     * [can_post_on_wall Used to Check If user have rights to post on event's wall]
     * @param  [int] $event_id    	[Event ID]
     * @param  [int] $user_id       [User id]
     * @return [bool]          		[true/false]
     */
    function can_post_on_wall($event_id, $user_id) {
        $this->db->where('EventID', $event_id);
        $this->db->where('UserID', $user_id);
        $this->db->where('CanPostOnWall', '1');
        $query = $this->db->get(EVENTUSERS);
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [leave allows user to leave a event]
     * @param  [int] 	$EventID    	[Event Id]
     * @param  [int] 	$user_id  		[User ID]
     */
    function leave($data) {
        if (!empty($data)) {

            $this->db->where('EventID', $data['EventID']);
            $this->db->where('UserID', $data['UserID']);
            $this->db->delete(EVENTUSERS);

            $this->db->where('EventID', $data['EventID']);
            $this->db->where('UserID', $data['UserID']);
            $this->db->delete(EVENTINVITES);
            $result['Message'] = lang('member_remove_event');

            if (!$this->settings_model->isDisabled(28)) {
                $this->load->model(array('reminder/reminder_model'));
                $this->reminder_model->delete_all($data['UserID'], 14, $data['EventID']);
            }

            return $result;
        }
    }

    /**
     * [check_member Used to Check member already exist in a event or not]
     * @param  [int] 	$event_id    	[Event Id]
     * @param  [int] 	$user_id  		[User ID]
     * @param  [int] 	$flag  			[flag to get details or only true/false]
     * @return [array/bool] 				[based on flag value]       	
     */
    function check_member($event_id, $user_id, $flag = false, $attending_only = false) {
        $this->db->where('EventID', $event_id);
        $this->db->where('UserID', $user_id);
        if ($attending_only) {
            $this->db->where('Presence', 'ATTENDING');
        }

        if ($flag) {
            $result = $this->db->get(EVENTUSERS);
            return $result->row_array();
        } else {
            $this->db->where('IsDeleted', '0');
            $result = $this->db->get(EVENTUSERS);
            if ($result->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * [is_invited Used to Check member is invited or not]
     * @param  [int]    $event_id       [Event Id]
     * @param  [int]    $user_id        [User ID]
     * @param  [int]    $flag           [flag to get details or only true/false]
     * @return [array/bool]                 [based on flag value]           
     */
    function is_invited($event_id, $user_id) {
        $this->db->where('EventID', $event_id);
        $this->db->where('UserID', $user_id);
        $this->db->where('Status', 'INVITED');
        $this->db->limit(1);
        $result = $this->db->get(EVENTINVITES);
        return $result->row_array();
    }

    /**
     * [get_threshold_date Used to get event threshold date]
     * @param  [string] $event_guid [Event GUID]
     * @return [string]             [event threshold date]
     */
    function get_threshold_date($event_guid) {
        $this->db->select('ArchiveThreshold');
        $this->db->from(EVENTS);
        $this->db->where('EventGUID', $event_guid);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->ArchiveThreshold;
        } else {
            return "";
        }
    }

    function get_all_joined_events($user_id, $array = false) {
        $data = array(0);
        if ($this->settings_model->isDisabled(14)) {
            return ($array) ? $data : '0';
        }


        $this->db->select('EventID');
        $this->db->from(EVENTUSERS);
        $this->db->where_in('Presence', array('ATTENDING', 'ARRIVED', 'MAY_BE'));
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $evnt) {
                $data[] = $evnt->EventID;
            }
        }
        $data = array_filter($data);
        if ($array) {
            return $data;
        } else {
            return implode(',', $data);
        }
    }

    function get_all_invited_events($user_id, $array = false) {
        $data = array(0);
        $this->db->select('EventID');
        $this->db->from(EVENTINVITES);
        $this->db->where('Status', 'INVITED');
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $evnt) {
                $data[] = $evnt->EventID;
            }
        }
        $data = array_filter($data);
        if ($array) {
            return $data;
        } else {
            return implode(',', $data);
        }
    }

    /**
     * [get_event_members_details description]
     * @param  [int]  $event_id     [Event ID]
     * @param  [int]  $page_no      [Page Number]
     * @param  [int]  $page_size    [Page Size]
     * @param  boolean $count_flag   [Count only flag]
     * @param  array   $only_friends [Return only firends]
     * @return [array]                [Event Member details]
     */
    function get_event_members_details($event_id, $page_no, $page_size, $count_flag = FALSE, $only_friends = array()) {
        $this->db->select('U.FirstName,U.LastName,U.ProfilePicture,P.Url as ProfileURL');
        $this->db->from(EVENTUSERS . " AS EU");
        $this->db->join(USERS . ' U', 'U.UserID=EU.UserID', 'left');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID AND P.EntityType="User"', 'left');
        $this->db->where("EU.EventID", $event_id);
        $this->db->where("EU.IsDeleted", 0);
        $this->db->where("EU.Presence!='NOT_ATTENDING'", NULL, FALSE);

        if ($only_friends) {
            $this->db->where_in('EU.UserID', $only_friends);
        }

        if ($count_flag) { // check if array needed
            return $this->db->get()->num_rows();
        }

        if (!empty($page_size)) {
            $Offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $Offset);
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
    }

    /* -------------------------------------------------
      | @Method - To check existance of Event Group Name
      | @Params - EventGroupTitle(string)
      | @Output - bool
      -------------------------------------------------- */

    function checkEventGroup($where = '', $returnEventId = false, $EventGroupTitle = '') {
        $res = $this->db->get_where(EVENTGROUPS, $where)->row();
        if (empty($res)) {
            return false;
        } else {
            if ($returnEventId) {
                $this->db->where($where);
                $this->db->update(EVENTGROUPS, array('Title' => $EventGroupTitle));
                return $res->EventGroupID;
            }
            return true;
        }
    }

    /* -------------------------------------------------
      | @Method - To insert event group title
      | @Params - eventGroupData(array)
      | @Output - int/bool
      -------------------------------------------------- */

    function insertEventGroup($EventGroupData = '') {
        if (!empty($EventGroupData)) {
            $this->db->insert(EVENTGROUPS, $EventGroupData);
            return $this->db->insert_id();
        } else {
            return false;
        }
    }

    /* -------------------------------------------------
      | @Method - To Save Event data
      | @Params - eventData(array)
      | @Output - int/bool
      -------------------------------------------------- */

    function get_filtered_events($user_id, $SearchText, $Location, $DateFrom, $DateTo, $Offset, $Limit, $CountOnly = 0, $posted_by = 'Anyone', $sort_by = '', $posted_by_users = array(), $cities = array()) {
        $data = array();
        /* $friend_followers_list = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
          $friends = $friend_followers_list['Friends'];
          $friends[] = $user_id;
         */
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $friends[] = $user_id;

        if ($Location) {
            $this->load->helper('location');
            $LocationData = update_location($Location);
        }

        $posted_by_users_id = array();

        if ($posted_by_users) {
            foreach ($posted_by_users as $pbu) {
                $posted_by_users_id[] = get_detail_by_guid($pbu, 3, 'UserID', 1);
            }
        }

        $condition = "(
			IF(E.Privacy='PRIVATE',(
				SELECT Presence FROM " . EVENTUSERS . " WHERE E.EventID=EventID AND UserID='" . $user_id . "'
			)='ATTENDING','') OR
			IF(E.Privacy='INVITE_ONLY',(
				SELECT Presence FROM " . EVENTUSERS . " WHERE E.EventID=EventID AND UserID='" . $user_id . "'
			) IN('ATTENDING','INVITED'),'') OR
			IF(E.Privacy='PUBLIC',true,'')
			)";

        $this->db->select('E.Privacy,E.EventID,E.Title,if(M.ImageName is NULL,"event-placeholder.png",M.ImageName) as ProfilePicture,E.EventGUID', FALSE);
        $this->db->select('PU.Url as CreatedProfileUrl,E.Description,CONCAT(U.FirstName," ",U.LastName) as CreatedBy,U.UserGUID AS CreatorGUID', FALSE);
        $this->db->select('E.CreatedDate,E.StartDate,E.StartTime,E.EndDate,E.EndTime,E.CreatedDate,E.LastActivity, L.FormattedAddress');
        // $this->db->select('CM.Name as Category');
        $this->db->select('IFNULL(CT.Name,"") as CityName,IFNULL(CM2.CountryName,"") as CountryName', false);
        $this->db->from(EVENTS . ' E');
        $this->db->join(MEDIA . ' M', 'M.MediaID=E.ProfileImageID', 'left');
        $this->db->join(USERS . ' U', 'E.CreatedBy=U.UserID', 'left');
        // $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=E.EventID', 'left');
        // $this->db->join(CATEGORYMASTER . ' CM', 'CM.CategoryID=EC.CategoryID', 'left');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID', 'left');
        $this->db->join(LOCATIONS . ' L', 'L.LocationID=E.LocationID', 'left');
        $this->db->join(CITIES . ' CT', 'CT.CityID=L.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CM2', 'CM2.CountryID=L.CountryID', 'left');

        $this->db->where('E.IsDeleted', '0');

        $this->db->where($condition, NULL, FALSE);

        //$this->db->where('EC.ModuleID', '14');
        $this->db->where('PU.EntityType', 'User');
        if ($friends) {
            $this->db->select('(SELECT COUNT(UserID) FROM ' . EVENTUSERS . ' WHERE EventID=E.EventID AND (Presence="ATTENDING" OR Presence="ARRIVED") AND UserID IN (' . implode(',', $friends) . ')) as FriendsCount', false);
        } else {
            $this->db->select('"0" as FriendsCount', false);
        }
        $this->db->select('(SELECT COUNT(UserID) FROM ' . EVENTUSERS . ' WHERE EventID=E.EventID AND (Presence="ATTENDING" OR Presence="ARRIVED")) as NoOfGuests', false);

        // $this->db->select("IFNULL(EUP.Presence,'NA') as UserPresence",false);
        //  $this->db->_protect_identifiers = FALSE;
        //  $this->db->join(EVENTUSERS.' EUP','EUP.EventID=E.EventID AND EUP.UserID="'.$user_id.'"','left');
        //  $this->db->_protect_identifiers = TRUE;

        if ($posted_by_users_id) {
            $this->db->where_in('E.CreatedBy', $posted_by_users_id);
        }

        if ($posted_by == 'Friends') {
            $this->db->_protect_identifiers = FALSE;
            $this->db->join(FRIENDS . ' F', 'F.UserID=E.CreatedBy AND F.FriendID="' . $user_id . '" AND F.Status="1"', 'join');
            $this->db->_protect_identifiers = TRUE;
        } else if ($posted_by == 'My Follows') {
            $this->db->_protect_identifiers = FALSE;
            $this->db->join(FOLLOW . ' F', 'F.TypeEntityID=E.CreatedBy AND F.Type="user" AND F.StatusID="2" AND F.UserID="' . $user_id . '"', 'join');
            $this->db->_protect_identifiers = TRUE;
        }

        if ($cities) {
            $this->db->where_in('L.CityID', $cities);
        }

        // $this->db->_protect_identifiers = FALSE;
        // $this->db->join(USERS . ' U1', 'U1.UserID=' . $user_id, 'left');
        // $this->db->_protect_identifiers = TRUE;

        if ($DateFrom || $DateTo) {
            $this->db->where('((E.StartDate BETWEEN "' . $DateFrom . '" AND "' . $DateTo . '") OR (E.EndDate BETWEEN "' . $DateFrom . '" AND "' . $DateTo . '"))', NULL, FALSE);
        }

        if ($Location) {
            $this->db->where('L.CityID', $LocationData['CityID']);
        }

        $this->db->where("(E.Title LIKE '%" . $SearchText . "%' OR E.Description LIKE '%" . $SearchText . "%' OR L.FormattedAddress LIKE '%" . $SearchText . "%')");
        //$this->db->join(USERDETAILS . ' UD', 'UD.UserID=U1.UserID', 'left');

        if ($sort_by == 'Friends') {
            $this->db->order_by('FriendsCount');
        } elseif ($sort_by == 'NameAsc') {
            $this->db->order_by('Title', 'ASC');
        } elseif ($sort_by == 'NameDesc') {
            $this->db->order_by('Title', 'DESC');
        } elseif ($sort_by == 'Most Members') {
            $this->db->order_by('NoOfGuests', 'DESC');
        } elseif ($sort_by == 'Event Date') {
            $this->db->select("IF(DATE_FORMAT(CONCAT(E.StartDate,' ',E.StartTime),'%Y-%m-%d %H:%i:%s')>'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as IsUpcoming", false);
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('IsUpcoming', 'DESC');
            $this->db->order_by("DATE_FORMAT(CONCAT(E.StartDate,' ',E.StartTime),'%Y-%m-%d %H:%i:%s')", 'ASC');
            $this->db->_protect_identifiers = TRUE;
        } elseif ($sort_by == 'Recent Updated') {
            $this->db->order_by('E.ModifiedDate', 'DESC');
        } else {
            $this->db->_protect_identifiers = FALSE;
            //$this->db->order_by('FIELD(UserPresence,"ATTENDING","ATTENDED","ARRIVED","MAY_BE","INVITED","NOT_ATTENDING","NA")');
            $this->db->order_by('FriendsCount', 'DESC');
            $this->db->_protect_identifiers = TRUE;
        }

        //$this->db->_protect_identifiers = FALSE;
        // $this->db->order_by('L.CityID=UD.CityID', 'DESC');
        // $this->db->_protect_identifiers = TRUE;
        if (!$CountOnly) {
            $this->db->limit($Limit, $this->get_pagination_offset($Offset, $Limit));
        }
        $query = $this->db->get();
        // echo $this->db->last_query(); die;
        if ($query->num_rows()) {
            if ($CountOnly) {
                return $query->num_rows();
            }
            if ($query->num_rows()) {
                foreach ($query->result_array() as $event) {
                    $event['loggedUserPresence'] = $this->get_user_presence($user_id, $event['EventID']);
                    $event['ModuleRoleID'] = $this->get_user_event_role($user_id, $event['EventID']);
                    $is_past = $this->check_event_current_status($event['EventID']);
                    $url = $this->getViewEventUrl($event['EventGUID'], $event['Title'], false, 'wall');
                    $event['ProfileURL'] = $url;
                    $event['EventStatus'] = 1;
                    if ($is_past) {
                        $event['EventStatus'] = 0;
                    }
                    $data[] = $event;
                }
            }
        }
        return $data;
    }

    function get_event_profile_picture($profile_banner_id) {
        $this->db->select('if(ImageName is NULL,"event-placeholder.png",ImageName) as ProfilePicture', false);
        $this->db->from(MEDIA);
        $this->db->where('MediaID', $profile_banner_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->ProfilePicture;
        } else {
            return "event-placeholder.png";
        }
    }

    /* -------------------------------------------------
      | @Method - To Get All Events
      | @Params - eventData(array)
      | @Output - int/bool
      -------------------------------------------------- */

    function getEvents($Input, $user_id = '', $EventType = 'HOST', $NumRows = false, $page_no = '', $page_size = '') {

        $this->db->select('DATE_FORMAT(E.LastActivity,"%d %b,%Y") AS LastActivity, DATE_FORMAT(E.StartTime,"%l:%i %p") AS StartTime,DATE_FORMAT(E.EndTime,"%l:%i %p") AS EndTime', false);

        $this->db->select('E.EventID, E.EventGUID, E.CreatedBy, E.Title, E.Description, E.Privacy, E.IsDeleted, E.StartDate, E.EndDate, E.ModuleID, E.ModuleEntityID', false);
        $this->db->select('IFNULL(E.Venue,"") as Venue', FALSE);
        $this->db->select('IFNULL(E.EventURL,"") as EventURL', FALSE);
        $this->db->select('IFNULL(E.LocationID,"") as LocationID', FALSE);
        $this->db->select('IFNULL(E.ProfileImageID,"") as ProfileImageID', FALSE);
        $this->db->select('IFNULL(E.ProfileBannerID,"") as ProfileBannerID', FALSE);

        //$this->db->select('IFNULL( EG.Title, "") AS GroupTitle',false);
        $this->db->select('IFNULL( CM.Name, "") AS CategoryName, CM.CategoryID', false);

        $this->db->from(EVENTS . ' AS E');

        //$this->db->join(EVENTGROUPS.' AS EG',"E.EventGroupID=EG.EventGroupID","LEFT");// join to Get Event Group
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT"); // join to Get Event Category
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");

        $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID');



        // Introducing Filters -----------------------------------------
        $Filter = (!empty($Input['Filter']) ? $Input['Filter'] : '');

        if (!empty($Filter)) {

            if (!empty($Filter['StartDate'])) {
                $this->db->where('E.StartDate', $Filter['StartDate']);
            }

            if (!empty($Filter['EndDate'])) {
                $this->db->where('E.EndDate', $Filter['EndDate']);
            }

            if (!empty($Filter['Keyword'])) {
                $this->db->like('E.Title', $Filter['Keyword']);
            }

            if (!empty($Filter['OrderBy'])) {
                $OrderBy = $Filter['OrderBy'] . ' ' . $Filter['OrderType'];
            }
        }
        if (empty($OrderBy)) {
            $OrderBy = 'E.EventID DESC ';
        }
        //------------------------------------------------------------
        // Check for specific event
        if (!empty($Input['EventID'])) {
            $this->db->where('E.EventID', $Input['EventID']);
        } else if (!empty($Input['Suggested'])) {
            $this->load->model('users/friend_model');
            $FriendIDS = $this->friend_model->getFriendIDS($user_id);

            if (!empty($FriendIDS)) {
                $this->db->where_in('EU.UserID', $FriendIDS);
            }

            $this->db->where('EU.UserID !=' . $user_id . ' AND E.Privacy="PUBLIC" AND EU.EventID NOT IN ( SELECT EventID FROM ' . EVENTUSERS . ' WHERE ' . EVENTUSERS . '.UserID=' . $user_id . ')', NULL, FALSE);
            $this->db->where(' EU.EventID NOT IN ( SELECT I.EntityID FROM ' . IGNORE . ' AS I WHERE I.UserID=' . $this->UserID . ' AND I.EntityType="Event")', NULL, FALSE);
        } else {
            // Users Event -----------------------------------------
            if (!empty($user_id)) {
                $this->db->where(array('EU.UserID' => $user_id));
                if ($EventType == "HOST") {
                    $this->db->where_in('EU.ModuleRoleID', array(1, 2));
                } else if ($EventType == "JOINED") {
                    $this->db->where('EU.ModuleRoleID', 3);
                    $this->db->where('EU.Presence', 'ATTENDING');
                }
            }
        }
        //-----------------------------------------------------------

        $this->db->where("E.IsDeleted", '0');

        $this->db->group_by('E.EventID');

        if (!empty($page_size)) { // Check for pagination
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }
        $this->db->order_by($OrderBy);

        if ($NumRows) {
            return $this->db->get()->num_rows();
        } else {
            $Result = $this->db->get()->result_array();
            //echo $this->db->last_query();die;
            $Events = array();
            if (!empty($Result)) {
                $this->load->helper('location');
                foreach ($Result as $Key => $Res) {
                    $Events[] = $Res;
                    $EventUsers = $this->members($Res['EventID'], $user_id);
                    $Events[$Key]['EventUsers'] = $EventUsers;
                    $Events[$Key]['UsersCount'] = count($EventUsers);
                    $Events[$Key]['Location'] = get_location_by_id($Res['LocationID']);
                    $Events[$Key]['ProfilePicture'] = $this->get_event_media($Res['ProfileImageID'], $Res['ProfileBannerID'], 'ProfilePicture');
                    $Events[$Key]['ProfileBanner'] = $this->get_event_media($Res['ProfileImageID'], $Res['ProfileBannerID'], 'ProfileBanner');

                    if (isset($Events[$Key]['Media']['ProfileImage']['MediaName'])) {
                        if (empty($Events[$Key]['Media']['ProfileImage']['MediaName'])) {
                            $Events[$Key]['Media']['ProfileImage']['MediaName'] = 'event-placeholder.png';
                        }
                    }

                    $CreatedByInfo = get_detail_by_id($Res['CreatedBy'], 3, 'FirstName,LastName', 2);
                    $Events[$Key]['CreatedBy'] = $CreatedByInfo['FirstName'] . ' ' . $CreatedByInfo['LastName'];
                    $Events[$Key]['CreatedByURL'] = get_entity_url($Res['CreatedBy'], 'User', 1);
                    $Events[$Key]['EventUrl'] = $this->getViewEventUrl($Res['EventGUID'], $Res['Title']);

                    $Events[$Key]['IsAdmin'] = 0;

                    if ($user_id) {
                        $Events[$Key]['IsAdmin'] = $this->is_admin($Res['EventID'], $user_id);
                    }
                    $Events[$Key]['IsCoverExists'] = 0;
                    $Events[$Key]['EventCoverImage'] = "";
                    $EventBanner = '';
                    if (!empty($Events[$Key]['ProfileBanner'])) {
                        $EventBanner = $Events[$Key]['ProfileBanner'];
                        $Events[$Key]['IsCoverExists'] = 1;

                        //$Events[$Key]['ProfileBanner'] = get_profile_cover($EventBanner);
                    }


                    if (!empty($Input['EventID'])) {
                        $Events[$Key]['Presence'] = $this->getEventMemberCount($Input['EventID']);
                    }
                    unset($Events[$Key]['EventID']);
                }
            }
            return $Events;
        }
    }

    /* -------------------------------------------------
      | @Method - To get event media
      | @Params - ProfileImageID(int),ProfileBannerID(int)
      | @Output - array
      -------------------------------------------------- */

    public function get_event_media($ProfileImageID, $ProfileBannerID, $Response = 0) {
        $ProfileImageArr = array();
        $ProfileBannerArr = array();

        if ($Response == 0 || $Response == 'ProfilePicture') {
            if (!empty($ProfileImageID)) {
                $this->db->select('MediaGUID,ImageName');
                $ImageArr = $this->db->get_where(MEDIA, array('MediaID' => $ProfileImageID))->row_array();
                $ProfileImageArr = array('MediaName' => $ImageArr['ImageName'], "MediaGUID" => $ImageArr['MediaGUID']);
            } else {
                $ProfileImageArr = array('MediaName' => "event-placeholder.png", "MediaGUID" => "");
            }
        }

        if ($Response == 0 || $Response == 'ProfileBanner') {
            if (!empty($ProfileBannerID)) {
                $this->db->select('MediaGUID,ImageName');
                $BannerArr = $this->db->get_where(MEDIA, array('MediaID' => $ProfileBannerID))->row_array();
                $ProfileBannerArr = array('MediaName' => $BannerArr['ImageName'], "MediaGUID" => $BannerArr['MediaGUID']);
            } else {
                $ProfileBannerArr = array('MediaName' => "", "MediaGUID" => "");
            }
        }

        if ($Response == 'ProfilePicture') {
            return $ProfileImageArr['MediaName'];
        } else if ($Response == 'ProfileBanner') {
            return $ProfileBannerArr['MediaName'];
        }
        return array('ProfileBanner' => $ProfileBannerArr, 'ProfileImage' => $ProfileImageArr);
    }

    public function getActiveEventUsers($EventID, $TotalCount = '', $KeyWord = '', $page_no = '', $page_size = '') {
        $this->db->select('CONCAT(Firstname," ",Lastname) AS FullName, EU.CanPostOnWall, ProfilePicture, Email, UserGUID, ModuleRoleID, Presence, P.Url as ProfileURL', False);
        $this->db->from(EVENTUSERS . " AS EU");
        $this->db->join(USERS . " AS U", "EU.UserID=U.UserID");
        $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
        $this->db->where("(Presence='ARRIVED' OR Presence='ATTENDING')", NULL, FALSE);
        $this->db->where("EventID", $EventID);
        $this->db->where("EU.IsDeleted", 0);
        if (!empty($page_size)) { // Check for pagination
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }

        if (!empty($KeyWord)) {
            $this->db->where('(FirstName LIKE "' . $KeyWord . '%" OR U.LastName LIKE "' . $KeyWord . '%" OR EU.Presence LIKE "' . strtoupper($KeyWord) . '%")');
        }

        if (empty($TotalCount)) { // check if array needed
            return $this->db->get()->result();
        } else {
            return $this->db->get()->num_rows();
        }
    }

    /* -------------------------------------------------
      | @Method - To add event users
      | @Params - EventID(Int),UserID(int)
      | @Output - array
      -------------------------------------------------- */

    function addEventInvites($EventID, $UserData, $OwnerID, $Presence = "INVITED") {
        if (!empty($UserData) && !empty($UserData)) {
            $this->db->select('UserID,Status'); // Get already attending/invited users
            $InvitedUsers = $this->db->get_where(EVENTINVITES, array('EventID' => $EventID, 'InviteBy' => $OwnerID))->result_array();
            $InvitedUserArr = array();
            if (!empty($InvitedUsers)) {
                foreach ($InvitedUsers as $invited) { // Create array of already invited or attending users
                    $InvitedUserArr[] = $invited['UserID'];
                }
            }
            $AlreadyMembers = array();
            $InsertData = array();
            $flag = true;
            foreach ($UserData as $key => $User) {
                if (empty($User['UserID']) || empty($User['ModuleRoleID'])) {
                    $flag = false;
                    break;
                }
                if (!in_array($User['UserID'], $InvitedUserArr)) { // Check for already invited users
                    $InsertData[$key]['EventID'] = $EventID;
                    $InsertData[$key]['UserID'] = $User['UserID'];
                    $InsertData[$key]['Status'] = $Presence;
                    $InsertData[$key]['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $InsertData[$key]['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $InsertData[$key]['InviteBy'] = $OwnerID;
                } else {
                    foreach ($InvitedUsers as $details) {
                        if ($details['UserID'] == $User['UserID']) {
                            if ($details['Status'] == 'NOT_ATTENDING') {

                                $this->db->where('EventID', $EventID);
                                $this->db->where('UserID', $User['UserID']);
                                $this->db->update(EVENTINVITES, array('Status' => $Presence, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
                            } else {
                                $AlreadyMembers[] = $User['UserGUID']; // Create log of already invited users
                            }
                        }
                    }
                }
            }
            if (!empty($InsertData) && $flag == true) { // Insert user's 
                $this->db->insert_batch(EVENTINVITES, $InsertData);

                //$this->SendEventInviteNotification($OwnerID,$EventID,$InsertData);

                $ToUserID = array();
                foreach ($InsertData as $user_data) {
                    $ToUserID[] = $user_data['UserID'];
                }
                if ($ToUserID) {
                    $this->load->model('notification_model');
                    $parameters = array();
                    $parameters[0]['ReferenceID'] = $OwnerID;
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $EventID;
                    $parameters[1]['Type'] = 'Event';
                    $this->notification_model->add_notification(30, $OwnerID, $ToUserID, $EventID, $parameters);
                }

                // Update Last Activity date time
                $this->load->helper('activity');
                set_last_activity_date($EventID, 14);

                return array('status' => true, 'AlreadyMembers' => $AlreadyMembers, 'res_status' => 1);
            } elseif ($flag == false) {
                return array('status' => false, 'res_status' => 3);
            } else {
                return array('status' => false, 'AlreadyMembers' => $AlreadyMembers, 'res_status' => 2);
            }
        } else {
            return array('status' => false, 'res_status' => 3);
        }
    }

    function addEventUsers($EventID, $UserData, $OwnerID, $Presence = "INVITED") {
        if (!empty($UserData) && !empty($UserData)) {
            $this->db->select('UserID,IsDeleted'); // Get already attending/invited users
            $InvitedUsers = $this->db->get_where(EVENTUSERS, array('EventID' => $EventID))->result_array();
            $InvitedUserArr = array();
            if (!empty($InvitedUsers)) {
                foreach ($InvitedUsers as $invited) { // Create array of already invited or attending users
                    $InvitedUserArr[] = $invited['UserID'];
                }
            }
            $AlreadyMembers = array();
            $InsertData = array();
            $flag = true;
            foreach ($UserData as $key => $User) {
                if (empty($User['UserID']) || empty($User['ModuleRoleID'])) {
                    $flag = false;
                    break;
                }
                if (!in_array($User['UserID'], $InvitedUserArr)) { // Check for already invited users
                    $InsertData[$key]['EventID'] = $EventID;
                    $InsertData[$key]['UserID'] = $User['UserID'];
                    $InsertData[$key]['Presence'] = $Presence;
                    $InsertData[$key]['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $InsertData[$key]['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $InsertData[$key]['ModifiedBy'] = $OwnerID;
                    $InsertData[$key]['ModuleRoleID'] = $User['ModuleRoleID'];
                    $InsertData[$key]['CreatedBy'] = $OwnerID;
                } else {
                    foreach ($InvitedUsers as $details) {
                        if ($details['UserID'] == $User['UserID']) {
                            if ($details['IsDeleted'] == 1) {
                                $this->db->where('EventID', $EventID);
                                $this->db->where('UserID', $User['UserID']);
                                $this->db->delete(EVENTUSERS);

                                $InsertData[$key]['EventID'] = $EventID;
                                $InsertData[$key]['UserID'] = $User['UserID'];
                                $InsertData[$key]['Presence'] = $Presence;
                                $InsertData[$key]['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                                $InsertData[$key]['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                                $InsertData[$key]['ModifiedBy'] = $OwnerID;
                                $InsertData[$key]['ModuleRoleID'] = $User['ModuleRoleID'];
                                $InsertData[$key]['CreatedBy'] = $OwnerID;
                            } else {
                                $AlreadyMembers[] = $User['UserGUID']; // Create log of already invited users
                            }
                        }
                    }
                }
            }
            if (!empty($InsertData) && $flag == true) { // Insert user's 
                $this->db->insert_batch(EVENTUSERS, $InsertData);

                //$this->SendEventInviteNotification($OwnerID,$EventID,$InsertData);

                $ToUserID = array();
                foreach ($InsertData as $user_data) {
                    $ToUserID[] = $user_data['UserID'];
                }
                if ($ToUserID) {
                    $this->load->model('notification_model');
                    $parameters = array();
                    $parameters[0]['ReferenceID'] = $OwnerID;
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $EventID;
                    $parameters[1]['Type'] = 'Event';
                    $this->notification_model->add_notification(30, $OwnerID, $ToUserID, $EventID, $parameters);
                }

                // Update Last Activity date time
                $this->load->helper('activity');
                set_last_activity_date($EventID, 14);

                return array('status' => true, 'AlreadyMembers' => $AlreadyMembers, 'res_status' => 1);
            } elseif ($flag == false) {
                return array('status' => false, 'res_status' => 3);
            } else {
                return array('status' => false, 'AlreadyMembers' => $AlreadyMembers, 'res_status' => 2);
            }
        } else {
            return array('status' => false, 'res_status' => 3);
        }
    }

    /* -------------------------------------------------
      | @Method - Manage event users
      | @Params - UserData(array),UserID(int)
      | @Output - array
      -------------------------------------------------- */

    public function ManageEventUsers($EventID, $UserData, $OwnerID) {
        if (!empty($UserData) && !empty($EventID)) {
            $flag = true;
            foreach ($UserData as $User) {
                $Action = (!empty($User['Action']) ? strtolower($User['Action']) : '');

                if (!empty($Action)) {
                    $ModuleRoleID = 2;

                    switch ($Action) {
                        case "block":
                            //BlockUser($user_id,14,$EventID,1);
                            break;

                        case "delete":
                            $this->db->where(array('UserID' => $User['UserID'], 'EventID' => $EventID));
                            $this->db->update(EVENTUSERS, array('IsDeleted' => 1)); // Update Flag in table
                            break;

                        case "presence":
                            $Presence = (!empty($User['Presence']) ? $User['Presence'] : '');
                            if (!empty($Presence)) {
                                $this->db->where(array('UserID' => $User['UserID'], 'EventID' => $EventID));
                                $this->db->update(EVENTUSERS, array('Presence' => $Presence)); // Update Flag in table
                            } else {
                                $flag = false;
                            }
                            break;

                        case "changerole":
                            //$RoleGUID	= (!empty($User['RoleGUID'])?$User['RoleGUID']:'');
                            //$ModuleRoleID = $this->getRoleIDByGUID($RoleGUID);
                            $ModuleRoleID = (!empty($User['RoleID']) ? $User['RoleID'] : '');
                            if (!empty($ModuleRoleID) && in_array($ModuleRoleID, array(1, 2, 3))) {
                                $this->db->where(array('UserID' => $User['UserID'], 'EventID' => $EventID));
                                $this->db->update(EVENTUSERS, array('ModuleRoleID' => $ModuleRoleID)); // Update Flag in table
                            } else {
                                $flag = false;
                            }
                            break;

                        default :
                            $flag = false;
                    }
                } else { // Break loop if action not provided.
                    $flag = false;
                    break;
                }
            }

            if (!$flag) {
                return array('status' => false);
            } else {
                // Update Last Activity date time
                $this->load->helper('activity');
                set_last_activity_date($EventID, 14);

                return array('status' => true);
            }
        } else {
            return array('status' => false);
        }
    }

    /* -------------------------------------------------
      | @Method - Get Role ID by RoleGUID
      | @Params - RoleGUID(String)
      | @Output - int/bool
      -------------------------------------------------- */

    function getRoleIDByGUID($RoleGUID) {
        if (!empty($RoleGUID)) {
            $this->db->select('RoleID');
            $row = $this->db->get_where(ROLES, array('RoleGUID' => $RoleGUID))->row();
            if (!empty($row)) {
                return $row->RoleID;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /* -------------------------------------------------
      | @Method - To set user permission
      | @Params - EventID(Int),UserID(int)
      | @Output - int/bool
      -------------------------------------------------- */

    function setUserPermission($user_id, $EventID) {
        if (!empty($user_id) && !empty($EventID)) {
            $InsertData = array('EntityID' => $EventID, 'UserID' => $user_id, 'ModuleID' => 14, 'ModuleRoleID' => 1, 'ModuleRoleRightID' => 1);
            return $this->db->insert(MODULEUSERPERMISSIONS, $InsertData);
        } else {
            return false;
        }
    }

    /**
     * [delete used to delete an event]
     * @param  [int] $event_id [event id]
     * @return [bool]           [true/false]
     */
    public function delete($event_id, $is_delete, $user_id) {
        if ($event_id) {
            $this->db->where('EventID', $event_id);
            $this->db->update(EVENTS, array('IsDeleted' => $is_delete));

            //if ($is_delete == 2) {
            if ($is_delete == 1) {
                $members_list = $this->members_id($event_id);
                $notify_users_attending = array();
                $notify_users_maybe = array();
                $notify_users_admin = array();
                $notify_users_hosting = array();
                $notify_users = array();
                foreach ($members_list as $member) {
                    if ($member->UserID != $user_id) {
                        if ($this->get_member_role($event_id, $member->UserID, 'Admin')) {
                            $notify_users[] = $member->UserID;
                            $notify_users_admin[] = $member->UserID;
                        } else if ($this->get_member_role($event_id, $member->UserID, 'Member')) {
                            $notify_users[] = $member->UserID;
                            $notify_users_attending[] = $member->UserID;
                        }
                    }
                }
                if ($notify_users) {
                    $this->load->model('notification_model');
                    $parameters = array();
                    $parameters[0]['ReferenceID'] = $event_id;
                    $parameters[0]['Type'] = 'Event';
                    $parameters[1]['ReferenceID'] = $user_id;
                    $parameters[1]['Type'] = 'User';

                    $current_user_details = get_detail_by_id($user_id, 3, 'FirstName,LastName', 2);
                    $event_details = get_detail_by_id($event_id, 14, 'Title,EventGUID', 2);
                    if ($notify_users_attending) {
                        foreach ($notify_users_attending as $attending) {
                            $user_details = get_detail_by_id($attending, 3, 'Email,FirstName,LastName', 2);
                            $full_name = $user_details['FirstName'] . ' ' . $user_details['LastName'];
                            $subject = 'Event Cancelled';
                            $message = $event_details['Title'] . ' has been cancelled';
                            //$this->sendEventEmail($attending,$user_details['Email'],'events/EventEmail',$message,$subject,$full_name,'');
                        }
                        $this->notification_model->add_notification(56, $user_id, $notify_users_attending, $event_id, array($parameters[0]));
                    }
                    if ($notify_users_admin) {
                        foreach ($notify_users_admin as $admin) {
                            $user_details = get_detail_by_id($admin, 3, 'Email,FirstName,LastName', 2);
                            $full_name = $user_details['FirstName'] . ' ' . $user_details['LastName'];
                            $subject = 'Event Cancelled';
                            $message = $event_details['Title'] . ' has been cancelled by ' . $current_user_details['FirstName'] . ' ' . $current_user_details['LastName'];
                            //$this->sendEventEmail($admin,$user_details['Email'],'events/EventEmail',$message,$subject,$full_name,'');
                        }
                        $this->notification_model->add_notification(58, $user_id, $notify_users_admin, $event_id, $parameters);
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * [feature used to feature an event]
     * @param  [int] $event_id [event id]
     * @return [bool]           [true/false]
     */
    public function feature($event_id, $is_featured, $user_id) {
        if ($event_id) {
            $this->db->where('EventID', $event_id);
            $this->db->update(EVENTS, array('IsFeatured' => $is_featured));
            return true;
        } else {
            return false;
        }
    }

    /* -------------------------------------------------
      | @Method - To add event media
      | @Params - Data(Array),
      | @Output - Boolean
      -------------------------------------------------- */

    public function AddEventMedia($Data = array()) {
        if (!empty($Data)) {
            if (!empty($Data['TargetModule'])) {
                $MediaID = 0;
                if (!empty($Data['MediaGUID'])) {
                    $this->db->select('MediaID');
                    $MediaData = $this->db->get_where(MEDIA, array('MediaGUID' => $Data['MediaGUID']))->row_array();
                    if (!empty($MediaData['MediaID'])) {
                        $MediaID = $MediaData['MediaID'];

                        $this->db->where('MediaID', $MediaID);
                        $this->db->update(MEDIA, array('StatusID' => 2));
                    }
                }


                $this->db->where('EventGUID', $Data['EventGUID']);

                if (strtolower($Data['TargetModule']) == "cover") {
                    $this->db->update(EVENTS, array('ProfileBannerID' => $MediaID));
                }

                if (strtolower($Data['TargetModule']) == "profile") {
                    $this->db->update(EVENTS, array('ProfileImageID' => $MediaID));
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /* -------------------------------------------------
      | @Method - To delete media
      | @Params - Data(Array)
      | @Output - Boolean
      -------------------------------------------------- */

    public function DeleteMedia($Data = array()) {
        if (!empty($Data)) {
            if (!empty($Data['TargetModule'])) {
                $MediaID = 0;
                if (!empty($Data['MediaGUID'])) {
                    $this->db->select('MediaID');
                    $MediaData = $this->db->get_where(MEDIA, array('MediaGUID' => $Data['MediaGUID']))->row_array();
                    if (!empty($MediaData['MediaID'])) {
                        $MediaID = $MediaData['MediaID'];

                        $this->db->where('MediaID', $MediaID);
                        $this->db->update(MEDIA, array('StatusID' => 1));
                    }
                }


                $this->db->where('EventGUID', $Data['EventGUID']);

                if (strtolower($Data['TargetModule']) == "cover") {
                    $this->db->update(EVENTS, array('ProfileBannerID' => 0));
                }

                if (strtolower($Data['TargetModule']) == "profile") {
                    $this->db->update(EVENTS, array('ProfileImageID' => 0));
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /* --------------------------------------------------------------------
      | @Method - To send update presence notification to event users
      | @Params - EventID(int),UserID(int),TargetPresence(string),IsInsert(int)
      | @Output - bool
      -------------------------------------------------------------------- */

    public function SendUpdatePresenceNotification($user_id, $EventID, $TargetPresence, $IsInsert) {
        if (!empty($EventID) && !empty($user_id)):
            $EventDetail = $this->getEvents(array('EventID' => $EventID));
            $EventTitle = $EventDetail[0]['Title'];
            $EventUsers = $this->members($EventID, $user_id);
            $UpdateByDetail = get_detail_by_id($user_id, 3, 'UserGUID,FirstName,LastName', 2);
            $UpdatedByName = $UpdateByDetail['FirstName'] . ' ' . $UpdateByDetail['LastName'];

            $UserGUID = $UpdateByDetail['UserGUID'];

            $Subject = "Users presence updated for event '" . $EventTitle . "'.";
            if (!empty($EventUsers)):
                foreach ($EventUsers AS $EventUser):
                    if ($EventUser['UserGUID'] != $UserGUID):
                        $flag = true;
                        switch ($TargetPresence) { //Switching updated presence by user
                            case 'ATTENDING':
                                switch ($EventUser['ModuleRoleID']) {
                                    case '1':
                                        $Message = "" . $UpdatedByName . " also attending the event " . $EventTitle . " you were hosting.";
                                        $NotificationTypeID = "31";
                                        break;

                                    case '2':
                                        $Message = "" . $UpdatedByName . " also attending the event " . $EventTitle . " you are the admin.";
                                        $NotificationTypeID = "32";
                                        break;

                                    case '3':
                                        if ($EventUser['Presence'] == 'ATTENDING'):
                                            $Message = "" . $UpdatedByName . " also attending the event " . $EventTitle . " you were attending.";
                                            $NotificationTypeID = "33";
                                        elseif ($EventUser['Presence'] == 'MAY_BE'):
                                            $Message = "" . $UpdatedByName . " also attending the event " . $EventTitle . " you may attend.";
                                            $NotificationTypeID = "34";
                                        else:
                                            $flag = false;
                                        endif;
                                        break;

                                    default:
                                        $flag = false;
                                        break;
                                }
                                break;

                            case 'MAY_BE':
                                switch ($EventUser['ModuleRoleID']) {
                                    case '1':
                                        $Message = "" . $UpdatedByName . " may also attend the event " . $EventTitle . " you were hosting.";
                                        $NotificationTypeID = "35";
                                        break;

                                    case '2':
                                        $Message = "" . $UpdatedByName . " may also attend the event " . $EventTitle . " you are the admin.";
                                        $NotificationTypeID = "36";
                                        break;

                                    case '3':
                                        if ($EventUser['Presence'] == 'ATTENDING'):
                                            $Message = "" . $UpdatedByName . " may also attend the event " . $EventTitle . " you were attending.";
                                            $NotificationTypeID = "37";
                                        elseif ($EventUser['Presence'] == 'MAY_BE'):
                                            $Message = "" . $UpdatedByName . " may also attend the event " . $EventTitle . " you may attend.";
                                            $NotificationTypeID = "38";
                                        else:
                                            $flag = false;
                                        endif;
                                        break;

                                    default:
                                        $flag = false;
                                        break;
                                }
                                break;


                            case 'ARRIVED':
                                switch ($EventUser['ModuleRoleID']) {
                                    case '1':
                                        $Message = "" . $UpdatedByName . " has arrived at the event " . $EventTitle . " you were hosting.";
                                        $NotificationTypeID = "39";
                                        break;

                                    case '2':
                                        $Message = "" . $UpdatedByName . " has arrived at the event " . $EventTitle . " you are the admin.";
                                        $NotificationTypeID = "40";
                                        break;

                                    case '3':
                                        if ($EventUser['Presence'] == 'ATTENDING'):
                                            $Message = "" . $UpdatedByName . " has arrived at the event " . $EventTitle . " you were attending.";
                                            $NotificationTypeID = "41";
                                        elseif ($EventUser['Presence'] == 'MAY_BE'):
                                            $Message = "" . $UpdatedByName . " has arrived at the event " . $EventTitle . " you may attend.";
                                            $NotificationTypeID = "42";
                                        else:
                                            $flag = false;
                                        endif;
                                        break;

                                    default:
                                        $flag = false;
                                        break;
                                }
                                break;

                            default:
                                $flag = false;
                                break;
                        }
                        if ($flag):
                            $ToUserID = get_detail_by_guid($EventUser['UserGUID'], 3);
                            //----------------------------Email Notification--------------------------------//
                            //$this->sendEventEmail($ToUserID,$EventUser->Email,'events/EventEmail',$Message,$Subject,$EventUser->FullName,'');
                            //----------------------------Email Notification--------------------------------//
                            //----------------------------Site Notification--------------------------------//

                            $this->sendEventSiteNotification($user_id, $ToUserID, $NotificationTypeID, $EventID, false);
                        //----------------------------Site Notification--------------------------------//
                        //----------------------------Push Notification--------------------------------//
                        //$this->sendEventPush($user_id,$EventUser->Email,'events/EditEventEmail',$Message,$Subject,$EventUser->FullName,'');
                        //----------------------------Push Notification--------------------------------//
                        endif;
                    endif;
                endforeach;
            endif;
        endif;

        return true;
    }

    /* -------------------------------------------------
      | @Method - To send event invite nofication
      | @Params - EventID(int),UserID(int)
      | @Output - bool
      -------------------------------------------------- */

    public function SendEventInviteNotification($user_id, $EventID, $UserData = array()) {
        if (!empty($UserData)):

            $EventName = get_detail_by_id($EventID, 14, "Title"); // Get Event Name

            $InvitedByDetail = get_detail_by_id($user_id, 3, 'FirstName,LastName', 2);

            $InvitedByName = $InvitedByDetail['FirstName'] . ' ' . $InvitedByDetail['LastName'];

            $Subject = "Invitation to attend '" . $EventName . "' event.";

            foreach ($UserData as $key => $User):
                if ($User['UserID'] != $user_id):
                    $UserInfo = get_detail_by_id($User['UserID'], 3, 'Email,FirstName,LastName', 2); // Get invitee's info

                    $Email = $UserInfo['Email'];

                    $FullName = $UserInfo['FirstName'] . ' ' . $UserInfo['LastName'];

                    $Message = "" . $InvitedByName . " invited you to attend an event  " . $EventName . ".";

                    $ToUserID = $User['UserID'];

                    //----------------------------Email Notification--------------------------------//
                    $this->sendEventEmail($user_id, $Email, 'events/EventEmail', $Message, $Subject, $FullName, '');
                    //----------------------------Email Notification--------------------------------//
                    //----------------------------Site Notification--------------------------------//
                    $this->sendEventSiteNotification($user_id, $ToUserID, 30, $EventID);
                //----------------------------Site Notification--------------------------------//
                //----------------------------Push Notification--------------------------------//
                //$this->sendEventPush($user_id,$EventUser->Email,'events/EditEventEmail',$Message,$Subject,$EventUser->FullName,'');
                //----------------------------Push Notification--------------------------------//	
                endif;
            endforeach;

            return true;

        endif;

        return false;
    }

    /* -------------------------------------------------
      | @Method - To send event update nofication
      | @Params - EventID(int),UserID(int)
      | @Output - bool
      -------------------------------------------------- */

    public function sendEditEventNotification($EventID, $user_id) {
        if (!empty($EventID) && !empty($user_id)):
            $EventDetail = $this->getEvents(array('EventID' => $EventID));

            $EventTitle = $EventDetail[0]['Title'];
            $EventUsers = $this->members($EventID, $user_id);

            $UpdateByDetail = get_detail_by_id($user_id, 3, 'UserGUID,FirstName,LastName', 2);
            $UpdatedByName = $UpdateByDetail['FirstName'] . ' ' . $UpdateByDetail['LastName'];
            $UserGUID = $UpdateByDetail['UserGUID'];
            if (!empty($EventUsers)):
                foreach ($EventUsers AS $EventUser):
                    if ($EventUser['UserGUID'] != $UserGUID):
                        $flag = true;
                        switch ($EventUser['ModuleRoleID']) {
                            case '1':
                                $Message = "" . $UpdatedByName . " edited the event " . $EventTitle . " you were hosting.";
                                $NotificationTypeID = "26";
                                break;

                            case '2':
                                $Message = "" . $UpdatedByName . " edited the event " . $EventTitle . " you are the admin.";
                                $NotificationTypeID = "27";
                                break;

                            case '3':
                                if ($EventUser['Presence'] == 'ATTENDING'):
                                    $Message = "" . $UpdatedByName . " edited the event " . $EventTitle . " you were attending.";
                                    $NotificationTypeID = "28";
                                elseif ($EventUser['Presence'] == 'MAY_BE'):
                                    $Message = "" . $UpdatedByName . " edited the event " . $EventTitle . " you may attend.";
                                    $NotificationTypeID = "29";
                                else:
                                    $flag = false;
                                endif;
                                break;

                            default:
                                # code...
                                break;
                        }
                        if ($flag):
                            //----------------------------Email Notification--------------------------------//
                            $Subject = "Event '" . $EventTitle . "' Updated.";
                            //$this->sendEventEmail($user_id,$EventUser->Email,'events/EventEmail',$Message,$Subject,$EventUser->FullName,'');
                            //----------------------------Email Notification--------------------------------//
                            //----------------------------Site Notification--------------------------------//
                            $ToUserID = get_detail_by_guid($EventUser['UserGUID'], 3);
                            $this->sendEventSiteNotification($user_id, $ToUserID, $NotificationTypeID, $EventID);
                        //----------------------------Site Notification--------------------------------//
                        //----------------------------Push Notification--------------------------------//
                        //$this->sendEventPush($user_id,$EventUser->Email,'events/EditEventEmail',$Message,$Subject,$EventUser->FullName,'');
                        //----------------------------Push Notification--------------------------------//
                        endif;
                    endif;
                endforeach;
            endif;
        endif;
    }

    /* -------------------------------------------------
      | @Method - To Send Event's Site Notifications
      | @Params - UserID(int),Email(string),TemplateName(string),Message(string),Subject(string),FullName(string),Url(string)
      | @Output - Boolean
      -------------------------------------------------- */

    public function sendEventEmail($user_id, $Email, $TemplateName, $Message, $Subject, $FullName, $Url = '') {
        // Prepare Email Data
        $emailDataArr = array();
        $emailDataArr['IsResend'] = 0;
        $emailDataArr['Subject'] = $Subject;
        $emailDataArr['TemplateName'] = $TemplateName;
        $emailDataArr['Email'] = $Email;
        $emailDataArr['EmailTypeID'] = 1;
        $emailDataArr['UserID'] = $user_id;
        $emailDataArr['StatusMessage'] = "EventEdit";
        $emailDataArr['Data'] = array("FullName" => $FullName, "Message" => $Message);
        if (!empty($Url)) {
            $emailDataArr['Data']['Url'] = $Url;
        }
        // Prepare Email Data
        sendEmailAndSave($emailDataArr); // send mail
        return true;
    }

    /* -------------------------------------------------
      | @Method - To Send Event's Email Notifications
      | @Params - UserID(int),Email(string),TemplateName(string),Message(string),Subject(string),FullName(string),Url(string)
      | @Output - Boolean
      -------------------------------------------------- */

    public function sendEventSiteNotification($user_id, $ToUserID, $NotificationTypeID, $EventID, $send_email = true) {
        $this->load->model('notification_model');
        $parameters = array();
        $parameters[0]['ReferenceID'] = $user_id;
        $parameters[0]['Type'] = 'User';
        $parameters[1]['ReferenceID'] = $EventID;
        $parameters[1]['Type'] = 'Event';
        $this->notification_model->add_notification($NotificationTypeID, $user_id, array($ToUserID), $EventID, $parameters, $send_email);
        return true;
    }

    /* -------------------------------------------------
      | @Method - To Send Event's Push Notifications
      | @Params - UserID(int),Email(string),TemplateName(string),Message(string),Subject(string),FullName(string),Url(string)
      | @Output - Boolean
      -------------------------------------------------- */

    public function sendEventPush($user_id, $Email, $TemplateName, $Message, $Subject, $FullName, $Url = '') {
        
    }

    /* -----------------------------------------------------------------------
      | @Method - To get count of event members on the basis of their presence
      | @Params - EventID(Int)
      | @Output - Array/Boolean
      ----------------------------------------------------------------------- */

    public function getEventMemberCount($EventID = "") {
        if (!empty($EventID)) {
            $this->db->select("Presence");
            $PresenceData = $this->db->get_where(EVENTUSERS, array('EventID' => $EventID, 'IsDeleted' => 0))->result_array();
            $Presence = array('ATTENDING' => 0, 'MAY_BE' => 0, 'INVITED' => 0);
            if (!empty($PresenceData)) {
                foreach ($PresenceData as $key => $value) {
                    switch ($value['Presence']) {
                        case 'ATTENDING':
                            $Presence['ATTENDING'] = $Presence['ATTENDING'] + 1;
                            break;

                        case 'ARRIVED':
                            $Presence['ATTENDING'] = $Presence['ATTENDING'] + 1;
                            break;

                        case 'MAY_BE':
                            $Presence['MAY_BE'] = $Presence['MAY_BE'] + 1;
                            break;

                        case 'INVITED':
                            $Presence['INVITED'] = $Presence['INVITED'] + 1;
                            break;

                        default:
                            # code...
                            break;
                    }
                }
            }
            return $Presence;
        } else {
            return false;
        }
    }

    /* -----------------------------------------------------------------------
      | @Method - To get id of event owner
      | @Params - EventID(Int)
      | @Output - Array/Boolean
      ----------------------------------------------------------------------- */

    public function isEventOwner($EventID = 0, $user_id = 0) {
        if (!empty($EventID) && !empty($user_id)) {
            $Row = $this->db->get_where(EVENTS, array('EventID' => $EventID, 'CreatedBy' => $user_id))->num_rows();
            if (!empty($Row)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /* -----------------------------------------------------------------------
      | @Method - To get id of event owner
      | @Params - EventID(Int)
      | @Output - Array/Boolean
      ----------------------------------------------------------------------- */

    public function getEventOwner($EventID = 0) {
        if (!empty($EventID)) {
            $this->db->select('CreatedBy');
            $Row = $this->db->get_where(EVENTS, array('EventID' => $EventID))->row_array();
            if (!empty($Row)) {
                return $Row['CreatedBy'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getEventAdmins($EventID = 0) {
        $arr = array();
        if (!empty($EventID)) {
            $this->db->select('UserID');
            $Row = $this->db->get_where(EVENTUSERS, array('EventID' => $EventID, 'ModuleRoleID' => '2'))->result_array();
            if (!empty($Row)) {
                foreach ($Row as $r) {
                    $arr[] = $r['UserID'];
                }
                return $arr;
            } else {
                return $arr;
            }
        } else {
            return $arr;
        }
    }

    /**
     * [checkPermission Used to check permission of user for Event ]
     * @param  [int] $user_id  [User ID]
     * @param  [int] $EventID [Event ID]
     * @return [boolean]          [true/false]
     */
    function checkPermissionWithDetail($user_id, $EventID) {
        $EventDetails = $this->get_events(array('EventID' => $EventID));

        if (!empty($EventDetails)) {
            $EventDetails = $EventDetails[0];
            if ($EventDetails['IsDeleted'] == 1) {
                return false;
            }

            if ($this->isInvited($EventID, $user_id)) {
                return $EventDetails;
            }

            if ($this->check_member($EventID, $user_id)) {
                return $EventDetails;
            }

            if ($EventDetails['Privacy'] == 'PUBLIC' || $EventDetails['Privacy'] == 'INVITE_ONLY') {
                return $EventDetails;
            }

            if ($EventDetails['Privacy'] == 'PRIVATE') {
                $this->db->select('Presence');
                $this->db->from(EVENTUSERS);
                $this->db->where('EventID', $EventID);
                $this->db->where('UserID', $user_id);
                $this->db->where_in('Presence', array('ATTENDING', 'INVITED'));
                $query = $this->db->get();
                if ($query->num_rows()) {
                    return $EventDetails;
                }
            }

            if ($EventDetails['Privacy'] == 'PRIVATE') {
                $this->db->select('Status');
                $this->db->from(EVENTINVITES);
                $this->db->where('EventID', $EventID);
                $this->db->where('UserID', $user_id);
                $this->db->where_in('Status', array('ATTENDING', 'INVITED'));
                $query = $this->db->get();
                if ($query->num_rows()) {
                    return $EventDetails;
                }
            }
        }
        return false;
    }

    /**
     * [isInvited Used to Check member is invited to join this event or not]
     * @param  [int] 	$EventID    	[Event Id]
     * @param  [int] 	$user_id  		[User ID]
     * @return [bool] 	[true/False]       	
     */
    function isInvited($EventID, $MemberID) {
        $this->db->where('EventID', $EventID);
        $this->db->where('UserID', $MemberID);
        $this->db->where('Presence', 'INVITED');
        $result = $this->db->get(EVENTUSERS);
        if ($result->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @Method : Method to prepare event url
     * @access  public
     * @param   EventGUID  
     * @return  
     */
    function getViewEventUrl($EventGUID, $title, $only_title_url = false, $url_type = 'about') {

        $title_url = str_replace(' ', '-', trim(get_valid_url_str($title)));
        if (!$title_url) { // To handle 404 error
            $title_url = $EventGUID;
        }

        if ($only_title_url) {
            return $title_url;
        }

        if (!empty($EventGUID)) {
            //return "events/" . $EventGUID . '/wall';
            return 'events/' . $title_url . '/' . $url_type . '/' . $EventGUID . '';
        } else {
            return false;
        }
    }

    public function getEventTitleUrl($url_type = '', $guid = '', $title_url) {

        return site_url('events') . '/' . $title_url . '/' . $url_type . '/' . $guid;
    }

    public function get_event_url_by_id($event_id) {
        $details = get_detail_by_id($event_id, 14, 'EventGUID, Title', 2);
        $event_guid = $details['EventGUID'];
        $title = $details['Title'];
        return $this->getViewEventUrl($event_guid, $title);
    }

    function get_upcoming_events($user_id, $current_user) {
        $event_list = array(0);
        if ($user_id != $current_user) {
            $this->db->select('EventID');
            $this->db->from(EVENTUSERS);
            $this->db->where('UserID', $current_user);
            $query = $this->db->get();
            if ($query->num_rows()) {
                foreach ($query->result_array() as $event) {
                    $event_list[] = $event['EventID'];
                }
            }
        }

        $data = array();

        $this->db->select("E.*");
        $this->db->select('U.FirstName,U.LastName');
        $this->db->from(EVENTS . ' E');
        $this->db->join(EVENTUSERS . ' EU', 'E.EventID=EU.EventID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=E.CreatedBy');
        $this->db->where('EU.UserID', $user_id);
        $this->db->where("DATE_FORMAT(CONCAT(E.EndDate,' ',E.EndTime),'%Y-%m-%d %H:%i:%s')>'" . get_current_date('%Y-%m-%d %H:%i:%s') . "'", null, false);
        $this->db->where('EU.EventID is not NULL', NULL, FALSE);
        if ($user_id != $current_user) {
            $this->db->where("IF(E.Privacy!='PUBLIC',(
                E.EventID IN('" . implode(',', $event_list) . "')
                ),true)", null, false);
        }
        $this->db->_protect_identifiers = FALSE;
        $this->db->order_by("DATE_FORMAT(CONCAT(E.EndDate,' ',E.EndTime),'%Y-%m-%d %H:%i:%s')", 'ASC');
        $this->db->_protect_identifiers = TRUE;
        $this->db->limit(1);
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $key => $val) {
                $val['Location'] = get_location_by_id($val['LocationID']);
                $val['EventStatus'] = $this->get_user_presence($current_user, $val['EventID']);
                $val['ProfilePicture'] = $this->get_event_media($val['ProfileImageID'], $val['ProfileBannerID'], 'ProfilePicture');
                $url = $this->getViewEventUrl($val['EventGUID'], $val['Title'], false, 'wall');
                $val['ProfileURL'] = $url;

                $data[] = $val;
            }
            return $data;
        }
    }

    function similar_event($event_id, $user_id, $count_flag = FALSE, $page_no = '', $page_size = '') {
        $events = array();

        $event_id = $this->db->escape_str($event_id);

        $sub_query = '(SELECT CM.CategoryID FROM ' . CATEGORYMASTER . ' CM INNER JOIN ' . ENTITYCATEGORY . ' EC ON CM.CategoryID=EC.CategoryID WHERE EC.ModuleEntityID=' . $event_id . ' AND EC.ModuleID=14)';

        $this->db->select('E.EventGUID,E.Title,E.LocationID, E.StartDate,E.ProfileImageID,E.ProfileBannerID,DATE_FORMAT(E.StartTime,"%l:%i %p") AS StartTime,E.CreatedBy,E.Venue,E.EventID');
        $this->db->from(EVENTS . ' AS E');
        $this->db->from(EVENTUSERS . ' AS EU', 'EU.EventID = E.EventID');
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT"); // join to Get Event Category
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");
        $this->db->where('CM.CategoryID', $sub_query, FALSE);
        $this->db->where('E.EventID !=', $event_id);
        $this->db->where('E.IsDeleted', '0');
        $this->db->where('E.Privacy', 'PUBLIC');
        $this->db->where('E.CreatedBy != ', $user_id);
        $this->db->where('EU.UserID != ', $user_id);
        $this->db->where("DATE_FORMAT(CONCAT(E.StartDate,' ',E.StartTime),'%Y-%m-%d %H:%i:%s')>'" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
        $this->db->group_by('E.EventID');
        if ($count_flag) {
            return $this->db->get()->num_rows();
        }

        if ($page_size) { // Check for pagination
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }

        $result = $this->db->get()->result_array();
        if (!empty($result)) {
            foreach ($result as $Key => $event) {
                $events[] = $event;
                $location = get_location_by_id($event['LocationID']);
                $time_zone = get_user_time_zone($user_id);
                $start_date = $event['StartDate'];
                $start_time = $event['StartTime'];
                $start_date_time = $this->timezone_model->convert_date_to_time_zone($start_date . ' ' . $start_time, 'UTC', $time_zone, 'Y-m-d h:i A');
                $start_date_month = $this->timezone_model->convert_date_to_time_zone($start_date . ' ' . $start_time, 'UTC', $time_zone, 'j M');
                $startDateArr = explode(' ', $start_date_month);
                $start_date_time = explode(' ', $start_date_time);

                $events[$Key]['StartDate'] = $start_date_time[0];
                $events[$Key]['StartTime'] = $start_date_time[1] . " " . $start_date_time[2];
                $events[$Key]['TimeZone'] = $time_zone;
                $events[$Key]['CreatedBy'] = $this->created_by($events[$Key]['CreatedBy']);


                $events[$Key]['StartDate'] = date('D, d M, Y', strtotime($events[$Key]['StartDate']));
                $events[$Key]['StartDay'] = $startDateArr[0];
                $events[$Key]['StartMonth'] = $startDateArr[1];
                $events[$Key]['ProfilePicture'] = $this->get_event_media($event['ProfileImageID'], $event['ProfileBannerID'], 'ProfilePicture');
                $events[$Key]['Location'] = $location;
                $events[$Key]['loggedUserPresence'] = $this->get_user_presence($user_id, $events[$Key]['EventID']);

                $events[$Key]['ProfileURL'] = $this->getViewEventUrl($event['EventGUID'], $event['Title'], false);

                unset($events[$Key]['EventID']);
                unset($events[$Key]['ProfileImageID']);
                unset($events[$Key]['ProfileBannerID']);
            }
        }
        return $events;
    }

    function past_event($event_id, $user_id, $count_flag = FALSE, $page_no = '', $page_size = '') {
        $events = array();

        $this->db->select('E.EventGUID,E.Title,E.Description,E.LocationID, E.StartDate,E.ProfileImageID,E.ProfileBannerID,DATE_FORMAT(E.StartTime,"%l:%i %p") AS StartTime,E.CreatedBy,E.Venue,E.EventID,E.Summary,DATE_FORMAT(CONCAT(E.EndDate," ",E.EndTime),"%Y-%m-%d %H:%i:%s") AS EndTime');
        $this->db->select('COUNT(M.MediaID) AS MediaCount');
        $this->db->from(EVENTS . ' AS E');
        $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID = E.EventID');
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT"); // join to Get Event Category
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");
        $this->db->join(ACTIVITY.' AS A',"A.ModuleID=14 AND A.ModuleEntityID=E.EventID AND A.StatusID = 2",'inner');
        $this->db->join(MEDIA.' AS M',"M.MediaSectionReferenceID=A.ActivityID AND M.StatusID=2 AND M.MediaSectionID='3'",'inner');
        $this->db->join(MEDIAEXTENSIONS.' AS ME',"ME.MediaExtensionID=M.MediaExtensionID",'inner');
        $this->db->join(MEDIATYPES.' AS MT',"MT.MediaTypeID=ME.MediaTypeID AND MT.MediaTypeID IN (1,2) ",'inner');
        
        $sub_query ='';
        if(!empty($event_id))
        {
            $sub_query = '(SELECT CM.CategoryID FROM ' . CATEGORYMASTER . ' CM INNER JOIN ' . ENTITYCATEGORY . ' EC ON CM.CategoryID=EC.CategoryID WHERE EC.ModuleEntityID=' . $event_id . ' AND EC.ModuleID=14)';
            $event_id = $this->db->escape_str($event_id);
            $this->db->where('E.EventID !=', $event_id);
        }
        if(!empty($sub_query)){
            $this->db->where('CM.CategoryID', $sub_query, FALSE);
        }
        $this->db->where('E.IsDeleted', '0');
        $this->db->where('E.Privacy', 'PUBLIC');
        
        $this->db->where("DATE_FORMAT(CONCAT(E.EndDate,' ',E.EndTime),'%Y-%m-%d %H:%i:%s')<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
        $this->db->group_by('E.EventID');
        $this->db->_protect_identifiers = FALSE;
        $this->db->order_by("DATE_FORMAT(CONCAT(E.EndDate,' ',E.EndTime),'%Y-%m-%d %H:%i:%s')", 'DESC');
        $this->db->_protect_identifiers = TRUE;
               
        if ($count_flag) {
            return $this->db->get()->num_rows();
        }

        if ($page_size) { // Check for pagination
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }

        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        $events = array();
        if (!empty($result)) {
            foreach ($result as $Key => $event) {
                $this->load->model(array('media/media_model'));
                $mediaList = $this->media_model->get_event_media('14', $event['EventID'], $user_id, 1, 6);
                
                $events[$Key] = $event;
                $events[$Key]['MediaList'] = $mediaList['MediaList'];

                $location = get_location_by_id($event['LocationID'],['City' => 1, 'State' => 1,'Country'=>1]);
                $time_zone = get_user_time_zone($user_id);
                $start_date = $event['StartDate'];
                $start_time = $event['StartTime'];
                $start_date_time = $this->timezone_model->convert_date_to_time_zone($start_date . ' ' . $start_time, 'UTC', $time_zone, 'Y-m-d h:i A');
                $start_date_month = $this->timezone_model->convert_date_to_time_zone($start_date . ' ' . $start_time, 'UTC', $time_zone, 'j M');

                $end_date_month = $this->timezone_model->convert_date_to_time_zone($events[$Key]['EndTime'], 'UTC', $time_zone, 'j M');

                $startDateArr = explode(' ', $start_date_month);
                $endDateArr = explode(' ', $end_date_month);
                $start_date_time = explode(' ', $start_date_time);

                $events[$Key]['StartDate'] = $start_date_time[0];
                $events[$Key]['StartTime'] = $start_date_time[1] . " " . $start_date_time[2];
                $events[$Key]['TimeZone'] = $time_zone;
               // $events[$Key]['CreatedBy'] = $this->created_by($events[$Key]['CreatedBy']);


                $events[$Key]['StartDate'] = date('D, d M, Y', strtotime($events[$Key]['StartDate']));
                $events[$Key]['StartDay'] = beliefmedia_ordinal($startDateArr[0]);
                $events[$Key]['EndDay'] = beliefmedia_ordinal($endDateArr[0]);
                $events[$Key]['StartMonth'] = $startDateArr[1];
                $events[$Key]['EndMonth'] = $endDateArr[1];
                $events[$Key]['ProfilePicture'] = $this->get_event_media($event['ProfileImageID'], $event['ProfileBannerID'], 'ProfilePicture');
                $events[$Key]['Location'] = $location;
                //$events[$Key]['loggedUserPresence'] = $this->get_user_presence($user_id, $events[$Key]['EventID']);
                $events[$Key]['Locations'] = $this->get_locations($events[$Key]['EventID'], FALSE);

                $events[$Key]['ProfileURL'] = $this->getViewEventUrl($event['EventGUID'], $event['Title'], false);
                $title_url = $this->getViewEventUrl($events[$Key]['EventID'], $events[$Key]['Title'], 1);
                $events[$Key]['MediaURL'] = $this->getEventTitleUrl('media', $events[$Key]['EventGUID'], $title_url);

                unset($events[$Key]['EventID']);
                unset($events[$Key]['ProfileImageID']);
                unset($events[$Key]['ProfileBannerID']);
            }
        }
        return $events;
    }

    function recent_invites($event_id, $user_id, $count_flag = FALSE, $page_no = '', $page_size = '') {
        $result = array();
        $friend_ids = array();

        $this->load->model('users/user_model');
        $friend_ids = $this->user_model->gerFriendsFollowersList($user_id, TRUE);
        if (!empty($friend_ids)) {
            /* $user_id_arr = implode( ',' , $friend_ids);
              $order_by = " (CASE when FIND_IN_SET(U.UserID, '".$user_id_arr."') THEN 1 else 0 END) DESC ";
              $this->db->order_by($order_by, FALSE); */

            $this->db->select("U.UserGUID,U.Firstname, U.Lastname, ,IF(U.ProfilePicture='','user_default.jpg', U.ProfilePicture) as ProfilePicture, P.Url as ProfileURL");
            $this->db->from(EVENTINVITES . ' AS EI');
            $this->db->join(USERS . ' AS U', 'U.UserID=EI.UserID');
            $this->db->join(PROFILEURL . ' P', 'U.UserID=P.EntityID', 'left');
            $this->db->where('EI.EventID !=', $event_id);
            //$this->db->where('EI.InviteBy', $user_id);

            $event_id = $this->db->escape_str($event_id);

            $this->db->where_in('EI.UserID', $friend_ids);
            $this->db->where(' EI.UserID NOT IN ( SELECT UserID FROM ' . EVENTUSERS . ' WHERE Presence= "ATTENDING" AND EventID=' . $event_id . ')', NULL, FALSE);
            $this->db->where(' EI.UserID NOT IN ( SELECT UserID FROM ' . EVENTINVITES . ' WHERE EventID=' . $event_id . ')', NULL, FALSE);
            $this->db->group_by('EI.UserID');
            $this->db->order_by('EI.ModifiedDate', 'DESC');

            if ($count_flag) {
                return $this->db->get()->num_rows();
            }
            //echo $this->db->last_query();die;

            if ($page_size) { // Check for pagination
                $offset = $this->get_pagination_offset($page_no, $page_size);
                $this->db->limit($page_size, $offset);
            }

            $result = $this->db->get()->result_array();
            if (!empty($result)) {
                foreach ($result as $key => $user) {
                    $result[$key]['Name'] = stripcslashes($result[$key]['Firstname']) . ' ' . stripcslashes($result[$key]['Lastname']);
                    unset($result[$key]['Firstname']);
                    unset($result[$key]['Lastname']);
                }
            }
        }
        return $result;
    }

    function get_user_event_status($event_id, $user_id) {
        $result = '';
        $this->db->select("EI.Status");
        $this->db->from(EVENTINVITES . ' AS EI');
        $this->db->where('EI.EventID', $event_id);
        $this->db->where('EI.UserID', $user_id);
        $this->db->limit(1);
        $res = $this->db->get();
        if ($res->row_array() > 0) {
            $record = $res->row_array();
            $result = $record['Status'];
        } else {
            $this->db->select("EU.Presence");
            $this->db->from(EVENTUSERS . ' AS EU');
            $this->db->where('EU.EventID', $event_id);
            $this->db->where('EU.UserID', $user_id);
            $this->db->limit(1);
            $res = $this->db->get();
            if ($res->row_array() > 0) {
                $record = $res->row_array();
                $result = $record['Presence'];
            }
        }
        return $result;
    }

    function get_invite_list($event_id, $user_id, $count_flag = FALSE, $page_no = '', $page_size = '') {
        $result = array();
        $this->db->select("U.UserGUID,U.Firstname, U.Lastname, ,IF(U.ProfilePicture='','user_default.jpg', U.ProfilePicture) as ProfilePicture, P.Url as ProfileURL,U.UserID");
        $this->db->from(EVENTINVITES . ' AS EI');
        $this->db->join(USERS . ' AS U', 'U.UserID=EI.InviteBy');
        $this->db->join(PROFILEURL . ' P', 'U.UserID=P.EntityID', 'left');
        $this->db->where('EI.EventID', $event_id);
        $this->db->where('EI.UserID', $user_id);
        $this->db->group_by('EI.InviteBy');

        if ($count_flag) {
            return $this->db->get()->num_rows();
        }

        if ($page_size) { // Check for pagination
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }

        $result = $this->db->get()->result_array();

        if (!empty($result)) {
            $this->load->model('users/user_model');
            foreach ($result as $key => $user) {
                //$result[$key]['location'] = $this->user_model->get_user_location($result[$key]['UserID']);
                $result[$key]['location'] = '';
                $location = $this->user_model->get_user_location($result[$key]['UserID']);
                if (!empty($location)) {
                    if (!empty($location['City'])) {
                        $result[$key]['location'] .= $location['City'];
                    }

                    if (!empty($location['Country'])) {
                        $result[$key]['location'] .= ", " . $location['Country'];
                    }
                }


                $result[$key]['Name'] = stripcslashes($result[$key]['Firstname']) . ' ' . stripcslashes($result[$key]['Lastname']);
                unset($result[$key]['UserID']);
                unset($result[$key]['Firstname']);
                unset($result[$key]['Lastname']);
            }
        }
        return $result;
    }

    /**
     * [module_event description]
     * @param  [array] $input      [Request data]
     * @param  [string] $user_id   [user id]
     * @param  string $event_type  [event type]
     * @param  string $num_rows    [num rows flag]
     * @param  string $page_no     [page no]
     * @param  string $page_size   [page size]
     * @return [array]             [description]
     */
    public function module_event($input, $user_id = '', $event_type, $num_rows = '', $page_no = '', $page_size = '') {

        $filter = isset($input['Filter']) ? $input['Filter'] : '';
        $this->db->select('E.*,if(EI.Status = "INVITED" and EI.UserID = ' . $user_id . ', 1, 0) IsInvited, DATE_FORMAT(E.LastActivity,"%d %b,%Y") AS LastActivity, DATE_FORMAT(E.StartTime,"%l:%i %p") AS StartTime,DATE_FORMAT(E.EndTime,"%l:%i %p") AS EndTime', false);
        $this->db->select('IFNULL( CM.Name, "") AS CategoryName, CM.CategoryID,U.UserGUID', false);
        $this->db->from(EVENTS . ' AS E');
        $this->db->join(ENTITYCATEGORY . ' AS EC', "EC.ModuleEntityID=E.EventID AND EC.ModuleID=14", "LEFT");
        $this->db->join(CATEGORYMASTER . ' AS CM', "CM.CategoryID=EC.CategoryID", "LEFT");
        $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID');
        $this->db->join(EVENTINVITES . ' AS EI', 'EI.EventID=E.EventID', 'left');
        $this->db->join(USERS . ' AS U', 'U.UserID=E.CreatedBy');

        if ($filter) {
            if (isset($filter['Keyword']) && $filter['Keyword']) {
                $this->db->like('E.Title', $filter['Keyword']);
            }

            if (isset($filter['OrderBy']) && isset($filter['OrderType']) && $filter['OrderBy'] && $filter['OrderType']) {
                $order_by = 'E.' . $filter['OrderBy'] . ' ' . $filter['OrderType'];
            }
        }

        if (!isset($order_by)) {
            $order_by = 'E.LastActivity DESC ';
        }

        if ($event_type == 'create') {
            $this->db->where('E.CreatedBy', $user_id);
        } else if ($event_type == 'attend') {
            $where = '(EU.Presence = "ATTENDING" AND EU.UserID = ' . $user_id . ') AND E.CreatedBy != ' . $user_id . ' ';
            $this->db->where($where, FALSE, FALSE);
        }/* else if($event_type == 'all') {
          $where = '(EU.Presence = "INVITED" AND EU.UserID = '.$user_id.') AND E.CreatedBy != '.$user_id.' ';
          $this->db->where($where,FALSE,FALSE);
          } */


        $this->db->where("DATE_FORMAT(CONCAT(E.StartDate,' ',E.StartTime),'%Y-%m-%d %H:%i:%s')>'" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
        $this->db->where('E.IsDeleted', '0');
        $this->db->where('E.ModuleID', $input['ModuleID']);
        $this->db->where('E.ModuleEntityID', $input['ModuleEntityID']);
        $this->db->group_by('E.EventID');
        $this->db->order_by($order_by);

        if ($num_rows) {
            return $this->db->get()->num_rows();
        } else {
            if ($page_size) { // Check for pagination
                $offset = $this->get_pagination_offset($page_no, $page_size);
                $this->db->limit($page_size, $offset);
            }

            $result = $this->db->get()->result_array();
            //echo $this->db->last_query();die;
            $events = array();
            if ($result) {
                $this->load->helper('location');
                foreach ($result as $key => $val) {
                    //$event_users = $this->members($val['EventID'], $user_id);
                    $event_users = $this->event_members($val['EventID'], $user_id);
                    $creator_info = get_detail_by_id($val['CreatedBy'], 3, 'FirstName,LastName', 2);

                    $events[] = $val;
                    $events[$key]['loggedUserPresence'] = $this->get_user_presence($user_id, $val['EventID']);
                    $events[$key]['EventUsers'] = $event_users;
                    $events[$key]['UsersCount'] = $events[$key]['MemberCount'] = $this->event_members($val['EventID'], $user_id, TRUE);
                    $events[$key]['Location'] = get_location_by_id($val['LocationID']);
                    $events[$key]['ProfilePicture'] = $this->get_event_media($val['ProfileImageID'], $val['ProfileBannerID'], 'ProfilePicture');
                    $events[$key]['ProfileBanner'] = $this->get_event_media($val['ProfileImageID'], $val['ProfileBannerID'], 'ProfileBanner');

                    if (isset($events[$key]['Media']['ProfileImage']['MediaName']) && ($events[$key]['Media']['ProfileImage']['MediaName'])) {
                        $events[$key]['Media']['ProfileImage']['MediaName'] = 'event-placeholder.png';
                    }

                    $events[$key]['CreatedBy'] = $creator_info['FirstName'] . ' ' . $creator_info['LastName'];
                    $events[$key]['CreatedByURL'] = get_entity_url($val['CreatedBy'], 'User', 1);
                    $events[$key]['EventUrl'] = $this->getViewEventUrl($val['EventGUID'], $val['Title']);

                    $events[$key]['IsAdmin'] = 0;

                    if ($user_id) {
                        $events[$key]['IsAdmin'] = $this->is_admin($val['EventID'], $user_id);
                    }
                    $events[$key]['EventDateTime'] = date('D, d M, Y', strtotime($events[$key]['StartDate']));
                    $events[$key]['IsCoverExists'] = 0;
                    $events[$key]['EventCoverImage'] = "";
                    $event_banner = '';
                    if (!empty($events[$key]['ProfileBanner'])) {
                        $event_banner = $events[$key]['ProfileBanner'];
                        $events[$key]['IsCoverExists'] = 1;

                        $events[$key]['ProfileBanner'] = get_profile_cover($event_banner);
                    }


                    if (!empty($input['EventID'])) {
                        $events[$key]['Presence'] = $this->getEventMemberCount($input['EventID']);
                    }
                    unset($events[$key]['EventID']);
                }
            }
            return $events;
        }
    }

    /**
     * [admin_event_listing  used to get list of groups ]
     * @param [sring]   $SearchText         [Search Keyword (group name)]   
     * @param [sring]   $ListingType        [MyGroup | Joined | Invited]        
     * @param [int]         $Offset             [Offset]
     * @param [int]         $page_size          [Limit]
     * @param [int]         $user_id            [Logged in User ID]
     */
    function admin_event_listing($input, $offset, $page_size = PAGE_SIZE, $search = 0, $count_only = 0, $all = FALSE) {
        /* $this->load->model('users/user_model');
          $friend_followers_list = $this->user_model->gerFriendsFollowersList($input['UserID'], true, 1);
          $friends = $friend_followers_list['Friends'];
          $friends[] = $input['UserID']; */
        $results = array();

        $this->db->select('E.*', FALSE);
        $this->db->select('U.FirstName,U.LastName,U.UserGUID');
        $this->db->from(EVENTS . ' E');
        $this->db->join(USERS . ' U', 'U.UserID=E.CreatedBy');

        /* if (isset($input['CategoryID']) && !empty($input['CategoryID']))
          {
          $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=G.GroupID', 'left');
          $this->db->where('EC.ModuleID', 1);
          $this->db->where('EC.CategoryID', $input['CategoryID']);
          }
          if (!$all)
          {
          $this->db->where('(CASE WHEN (Gm.ModuleRoleID=4) THEN G.StatusID IN (2,1) ELSE G.StatusID=2 END)', NULL, FALSE);
          $this->db->where('Gm.StatusID', 2);
          $this->db->where('GM.ModuleEntityID', $this->UserID);
          } else
          {
          $this->db->where('G.StatusID', 2);
          }

          $this->db->group_by('G.GroupID'); */
        $this->db->where('E.IsDeleted', 0);

        if (!empty($page_size)) {
            if ($offset == 0) {
                $offset = 1;
            }
            $offset = ($offset - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }

        /* if (isset($input['PrivacyType']) && $input['PrivacyType'] != '-1')
          {
          $this->db->where('G.IsPublic', $input['PrivacyType']);
          } */

        if (trim($input['SearchText'])) {
            $terms = explode(' ', trim($input['SearchText']));
            $bits = array();
            foreach ($terms as $term) {
                $bits[] = " E.Title LIKE '%" . $this->db->escape_like_str($term) . "%'";
                //$bits[] = " G.GroupDescription LIKE '%".$this->db->escape_like_str($term)."%'";
            }
            $this->db->select("IF(E.Title LIKE '%" . $this->db->escape_like_str($input['SearchText']) . "%',1,2) as OrdBy", FALSE);
            $this->db->where(" (" . implode(' OR ', $bits) . ")", NULL, FALSE);
        }

        // Added Sorting by type and order
        if (isset($input['SortBy']) && isset($input['OrderBy'])) {
            if (empty($input['OrderBy'])) {
                $input['OrderBy'] = "DESC";
            } else {
                $input['OrderBy'] = "ASC";
            }
            $this->db->order_by($input['SortBy'], $input['OrderBy']);
        }


        $res = $this->db->get();
        if ($count_only) {
            return $res->num_rows();
        }
        $result_data = array();
        $results['total_records'] = $res->num_rows();
        $val['TotalRecords'] = $results['total_records'];
        $result = $res->result_array();
        $result_data = array();

        foreach ($result as $key => $value) {
            /* $value['Category'] = $this->get_group_categories($value['GroupID']);
              unset($value['GroupID']);
              unset($value['OrdBy']); */
            $result_data[$key] = $value;


            if (!empty($value['ProfileBannerID'])) {
                $image = $this->get_event_media($value['ProfileImageID'], $value['ProfileBannerID'], 'ProfileBanner');
                $result_data[$key]['EventCover'] = IMAGE_SERVER_PATH . 'upload/profilebanner/220x220/' . $image;
            } else {
                $result_data[$key]['EventCover'] = IMAGE_SERVER_PATH . 'upload/profilebanner/220x220/event_banner.jpg';
            }

            /* set Created by data */
            $result_data[$key]['CreatedBy'] = array(
                'FirstName' => $value['FirstName'],
                'LastName' => $value['LastName'],
                'UserGUID' => $value['UserGUID']
            );
            /* set Created by data */

            /* Unset variables not required */
            unset($result_data[$key]['FirstName']);
            unset($result_data[$key]['LastName']);
            unset($result_data[$key]['UserGUID']);
            /* Unset variables not required */
        }
        $results['data'] = $result_data;
        return $result_data;
    }

}
