<?php

/**
 * This model is used for getting and storing sports related information
 * @package    polls_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Polls_model extends Common_Model {

    public $module_id;

    function __construct() {
        parent::__construct();
        $this->module_id = 30;        
    }

    /**
     * 
     */
    public function can_create_poll() {    
        
        if($this->settings_model->isDisabled(30)){ //check for poll module          
            return 0;
        }
        if($this->LocalityID) {
            $this->load->model(array('locality/locality_model'));
            $locality = $this->locality_model->get_locality($this->LocalityID);            
            if(empty($locality['IsPollAllowed'])) {               
                $this->can_create_poll  = 0;
            }
        } else {
            $this->can_create_poll  = 0;
        }
        return $this->can_create_poll;
    }    

    /**
     * [create Used to insert poll details]
     * @param  [array]  $insert              [Poll details]
     * @param  [array]  $options             [Poll Options]     
     */
    public function create($insert, $options) {
        if (!empty($insert)) {
            $current_date = get_current_date('%Y-%m-%d %H:%i:%s'); // Get current date time in utc
            $insert['PollGUID'] = get_guid();            
            $insert['CreatedDate'] = $current_date;
            $insert['ModifiedDate'] = $current_date;
            if (empty($insert['ExpiryDateTime'])) {
                $insert['ExpiryDateTime'] = NULL; // No expiry date for this poll
            }

            // insert and get poll id
            $this->db->insert(POLL, $insert);
            $poll_id = $this->db->insert_id();

            // Add options for poll
            $this->add_options($options, $poll_id);
        } 
    }
    
       /**
     * @Function - add poll options
     * @Input 	- options(array),poll_id(int)
     * @Output 	- boolean
     */
    public function add_options($options, $poll_id) {
        $option_data = array();
        $option_data['PollID'] = $poll_id;
        $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');
        foreach ($options as $key => $option) {
            if (!empty($option)) {
                //Prepare data to insert in PollChoice
                $option_data['OptionGUID'] = get_guid();
                $option_data['Value'] = $option['OptionDescription'];
                $option_data['CreatedDate'] = $current_date_time;
                $option_data['ModifiedDate'] = $current_date_time;
                $option_data['IsMediaExist'] = 0;                
                $this->db->insert(POLLOPTION, $option_data);
            }
        }
        return TRUE;
    }

    
    /* [get_poll_by_activity_id Used to get poll details]
     * @param  [int] $activity_id           [Activity  ID]
     * @param  [int] $user_id               [User id]
     * @return array              [poll data]
     */
    public function get_poll_by_activity_id($activity_id, $user_id) {
        $this->db->select('P.PollID, P.PollGUID, P.Description, P.UserID', FALSE);        
        $this->db->from(POLL . ' P');
        $this->db->where('P.ActivityID', $activity_id);
        $this->db->where_in('P.Status', array('ACTIVE'));
        $this->db->limit(1);
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        $poll = array();
        if ($query->num_rows()) {            
            $poll = $query->row_array();
            $poll_id = $poll['PollID'];
            $is_owner = 0;
            if (($user_id == $poll['UserID'])) {
                $is_owner = 1;
            }
            $poll['IsOwner'] = $is_owner;
            $poll['IsVoted'] = 0;                     
            $voted_option = $this->is_voted($poll_id, 3, $user_id);
            if ($voted_option) {
                $poll['IsVoted'] = 1;
            }            
            $option_data = $this->get_poll_options($poll_id, 3, $user_id, 0, $voted_option);
            $poll['Options'] = $option_data['Options'];
            $poll['TotalVotes'] = $option_data['TotalVotes'];
                    
            unset($poll['UserID']);
            unset($poll['PollID']);   
        }
        return $poll;
    }
    
    /* [get_poll_description Used to get poll description]
     * @param  [int] $activity_id           [Activity  ID]
     * @return array              [poll data]
     */
    function get_poll_description($activity_id) {
        $this->db->select('P.Description', FALSE);        
        $this->db->from(POLL . ' P');
        $this->db->where('P.ActivityID', $activity_id);
        $this->db->where_in('P.Status', array('ACTIVE'));
        $this->db->limit(1);
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        $poll = array();
        $description = '';
        if ($query->num_rows()) {            
            $poll = $query->row_array();
            $description = $poll['Description'];  
        }
        return $description;
    }
    
    /**
     * [is_voted description]
     * @param  [int] $poll_id     			[Poll  ID]
     * @param  [int] $module_id       		[module id]
     * @param  [int] $module_entity_id  	[module entity id]
     * @return boolean              [description]
     */
    public function is_voted($poll_id, $module_id, $module_entity_id) {
        $this->db->select("OptionID");
        $this->db->from(POLLOPTIONVOTES);
        $this->db->where(array('PollID' => $poll_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id));
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $vote = $query->row_array();
            return $vote['OptionID'];             
        }
        return false;
    }  
    
        /**
     * [get_poll_options - Used to get poll options]
     * @param  [int] $poll_id     			[Poll  ID]
     * @param  [int] $module_id       		[module id]
     * @param  [int] $module_entity_id  	[module entity id]
     * @return boolean              [description]
     */
    public function get_poll_options($poll_id, $module_id, $module_entity_id, $is_anonymous = 0, $voted_option) {
        $this->db->select('PO.OptionGUID, PO.OptionID, PO.Value, PO.NoOfVotes');
        $this->db->from(POLLOPTION . ' PO');
        $this->db->where('PO.PollID', $poll_id);
        $this->db->where('PO.Status', 'ACTIVE');
        $query = $this->db->get();
        $poll_option = array();
        $option_data = array('Options'=>array(),'TotalVotes'=>0);
        $poll_total_votes = 0;
        if ($query->num_rows()) {
            foreach ($query->result_array() as $result) {
                $option_id = $result['OptionID'];
                $option_guid = $result['OptionGUID'];
                //$result['Members'] = array();                
                if (empty($is_anonymous)) {
                    //$result['Members'] = $this->get_poll_option_votes($option_id, FALSE, 1, 5, $module_id, $module_entity_id);
                }
                $result['IsVoted'] = 0;
                if($option_id == $voted_option) {
                    $result['IsVoted'] = 1;
                }
                $poll_total_votes = $poll_total_votes + $result['NoOfVotes'];
                unset($result['OptionID']);
                $poll_option[] = $result;
            }
            $option_data['Options'] = $poll_option;
            $option_data['TotalVotes'] = $poll_total_votes;
           /* foreach ($poll_option as $key => $csm) {
                $option_data[$key] = $csm;
                $option_data[$key]['pollTotalVotes'] = $poll_total_votes;
            }
            * 
            */            
        } 
        return $option_data;
    }
       

    /**
     * [is_poll_expired check if poll is expired or not]
     * @param  [string]             [Poll Option GUID]
     * @return [boolean]            [true / false]
     */
    public function is_poll_expired($option_guid) {
        $this->db->select('P.ExpiryDateTime');
        $this->db->from(POLL . ' P');
        $this->db->join(POLLOPTION . ' PO', 'PO.PollID=P.PollID', 'left');
        $this->db->where('PO.OptionGUID', $option_guid);
        $this->db->limit(1);
        $poll_query = $this->db->get();
        if ($poll_query->num_rows()) {
            $poll_expiry_date = $poll_query->row()->ExpiryDateTime;
            if ($poll_expiry_date) {
                if ($poll_expiry_date < get_current_date('%Y-%m-%d %H:%i:%s')) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * [vote Submit vote for particular poll]
     * @param  [string] $option_guid     		[Poll option GUID]
     * @param  [int] $module_id       		[module id]
     * @param  [int] $module_entity_id  	[module entity id]
     * @return [array]              [response code with message]
     */
    public function vote($option_guid, $module_entity_id, $module_id) {
        $this->db->select("OptionID, PollID");
        $this->db->from(POLLOPTION);
        $this->db->where(array('OptionGUID' => $option_guid));
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
            $result = $query->row_array();
            $option_id = $result['OptionID'];
            $poll_id = $result['PollID'];
            if (!$this->is_voted($poll_id, $module_id, $module_entity_id)) {
                $poll_data = $this->db->select('UserID, ActivityID')->from(POLL)->where('PollID', $poll_id)->limit(1)->get()->row();
                $created_user_id = $poll_data->UserID;
                $activity_id = $poll_data->ActivityID;
                
                $insert_vote = array();
                $insert_vote['OptionID'] = $option_id;
                $insert_vote['PollID'] = $poll_id;
                $insert_vote['ModuleEntityID'] = $module_entity_id;
                $insert_vote['ModuleID'] = $module_id;
                $insert_vote['CreatedDate'] = $current_date;
                $insert_vote['ModifiedDate'] = $current_date;

                $this->db->insert(POLLOPTIONVOTES, $insert_vote);
                $vote_id = $this->db->insert_id();
                $this->update_vote_count($option_id);

                $parameters[0]['ReferenceID'] = $module_entity_id;
                if ($module_id == '1') {
                    $parameters[0]['Type'] = 'Group';
                } elseif ($module_id == '3') {
                    $parameters[0]['Type'] = 'User';
                } elseif ($module_id == '14') {
                    $parameters[0]['Type'] = 'Event';
                } elseif ($module_id == '18') {
                    $parameters[0]['Type'] = 'Page';
                }
                initiate_worker_job('add_notification', array('NotificationTypeID' => 110, 'SenderID' => $this->UserID, 'ReceiverIDs' => array($created_user_id), 'RefrenceID' => $activity_id, 'Parameters' => $parameters, 'ExtraParams' => array()),'','notification');                
                //$return['Count'] = $this->get_poll_option_vote_count($poll_id, $option_id);
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * [update_vote_count description]
     * @param  [int]    $option_id   [Option Id]
     * @param  int      $count      [Poll option vote increment/decrement]
     * @return [type]               [description]
     */
    public function update_vote_count($option_id, $count = 1) {
        $set_field = "NoOfVotes";
        $table_name = POLLOPTION;
        $condition = array("OptionID" => $option_id);

        $this->db->where($condition);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->update($table_name);
    }
        
    /**
     * [get_poll_option_votes Used to get the poll option votes details]
     * @param  [int]  $option_id        [Poll option id]
     * @param  [boolean] $count_flag    [Count only flag]
     * @param  [int]  $page_no      	[Page Number]
     * @param  [int]  $page_size    	[Page Size]
     * @param  [int] $module_id       		[module id]
     * @param  [int] $module_entity_id  	[module entity id]
     * @return [type]                    [description]
     */
    function get_poll_option_votes($option_id, $count_flag = FALSE, $page_no = '', $page_size = '') {
        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as Name, U.UserGUID");
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('IFNULL(UD.Address,"") as Address', FALSE);
        $this->db->select('UD.LocalityID, POV.CreatedDate');
        $this->db->from(POLLOPTIONVOTES . " POV");
        $this->db->join(USERS . " U", "U.UserID=POV.ModuleEntityID AND POV.ModuleID=3");
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->where('POV.OptionID', $option_id);        
        $this->db->order_by('POV.CreatedDate', 'DESC');

        if (empty($count_flag)) { // check if array needed
            if (!empty($page_size)) { // Check for pagination
                $offset = ($page_no - 1) * $page_size;
                $this->db->limit($page_size, $offset);
            }
            $query = $this->db->get();
            $response = array();

            if ($query->num_rows()) {
                foreach ($query->result_array() as $result) {                    
                    $data['UserGUID'] = $result['UserGUID'];
                    $data['Name'] = $result['Name'];
                    $data['ProfilePicture'] = $result['ProfilePicture'];
                    $data['Locality'] = array("Name" => "", "HindiName"=>"", "ShortName"=>"",  "LocalityID" => 0,  "IsPollAllowed" => 1);
                    if($result['LocalityID']) {
                        $this->load->model(array('locality/locality_model'));
                        $data['Locality'] = $this->locality_model->get_locality($result['LocalityID']);
                    }
                    if(empty($data['Locality']['LocalityID'])) {
                        $data['Locality']['LocalityID'] = 0;
                    }
                    unset($data['Locality']['IsPollAllowed']);
                    
                    $response[] = $data;
                }
            }
            return $response;
        } else {
            return $this->db->get()->num_rows();
        }
    }

    /**  Used to delete activity poll    
     * @param int $activity_id
     */
    function delete_activity_poll($activity_id) {
        $this->db->set('Status', 'DELETED');
        $this->db->where('ActivityID', $activity_id);
        $this->db->update(POLL);
    }
    
    function poll_analytics_data() {
        $today_date = get_current_date('%Y-%m-%d', 1);
        $this->db->select('PollID');
        $this->db->from(POLL);
        $this->db->like('CreatedDate', $today_date);
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $total_poll = $query->num_rows();
        
        $this->db->select('VoteID');
        $this->db->from(POLLOPTIONVOTES);
        $this->db->like('CreatedDate', $today_date);
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $total_vote = $query->num_rows();
        
        $this->db->select('VoteID');
        $this->db->from(POLLOPTIONVOTES);
        $this->db->like('CreatedDate', $today_date);
        $this->db->group_by('ModuleEntityID,ModuleID');
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $total_unique_user = $query->num_rows();
                
        $this->db->select('PollAnalyticsID');
        $this->db->from(POLLANALYTICS);
        $this->db->where('AnalyticsDate', $today_date);
        $query = $this->db->get();
        if($query->num_rows() == 0 && ($total_poll || $total_vote)) {
            $analytics_data = array();
            $analytics_data['TotalPoll'] = $total_poll;
            $analytics_data['TotalVote'] = $total_vote;
            $analytics_data['TotalUniqueUser'] = $total_unique_user;
            $analytics_data['AnalyticsDate'] = $today_date;
            $analytics_data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $this->db->insert(POLLANALYTICS, $analytics_data);                
        }
    }
    
    /**
     * Used to send poll result notification
     */
    function send_poll_result_notificaton() {
        $select_date = get_current_date('%Y-%m-%d', 1);
        $this->db->select('PollID, ActivityID');
        $this->db->from(POLL);
        $this->db->where('Status', 'ACTIVE');
        $this->db->like('CreatedDate', $select_date);
        $query = $this->db->get();        
        //echo $this->db->last_query();die;
        if($query->num_rows()) {
            foreach ($query->result_array() as $result) {                    
                $activity_id = $result['ActivityID'];
                $poll_id = $result['PollID'];  
                
                $this->db->select("POV.ModuleEntityID"); 
                $this->db->select('GROUP_CONCAT(POV.ModuleEntityID) as UserIDs', FALSE);
                $this->db->from(POLLOPTIONVOTES . " POV");
                $this->db->where('POV.PollID', $poll_id);        
                $this->db->order_by('POV.CreatedDate', 'DESC');
                $voter_query = $this->db->get();
                //echo $this->db->last_query();die;
                if ($voter_query->num_rows()) {
                    $user_ids = array();
                    $voter_row = $voter_query->row_array();
                    if (!empty($voter_row['UserIDs'])) {
                        $user_ids = explode(',', $voter_row['UserIDs']);
                    }
                    if(!empty($user_ids)) {
                        initiate_worker_job('add_notification', array('NotificationTypeID' => 155, 'SenderID' => 1, 'ReceiverIDs' => $user_ids, 'RefrenceID' => $activity_id, 'Parameters' => array(), 'ExtraParams' => array()),'','notification');
                    }
                }        
            }                
        }
    }

    
    /**
     * [add_poll_for_users to add users/groups to whom only poll would visible]
     * @param [array] $poll_for     [users/groups to whom only poll would be visible]
     * @param [int] $activity_id    [Poll Activity ID]
     */
    public function add_poll_for_users($poll_for, $activity_id) {
        if (!empty($poll_for)) {
            $this->load->model('activity/activity_model');
            foreach ($poll_for as $mention) {
                $module_entity_id = get_detail_by_guid($mention['ModuleEntityGUID'], $mention['ModuleID']);
                $this->activity_model->add_mention($module_entity_id, $mention['ModuleID'], $activity_id, 'Poll For', 0, 2);
            }
        }
        return TRUE;
    }

    /**
     * @Function - Send poll create notification to tagged users
     * @Input 	- acitivty_id(int),post_content(string),media(array)
     * @Output 	- array
     */
    public function send_activity_notification($activity_id, $post_data, $media) {
        $this->load->model('activity/activity_model');
        $user_id = $this->UserID;
        $send_notification = 1;
        $post_content = $post_data['Description'];
        if ($post_data['PostAsModuleID'] == 3) {
            $usrs = array($post_data['PostAsModuleEntityID']);
        } else {
            $data = $this->post_data;
            $usrs = $this->page_model->get_page_owner($data['PostAsModuleEntityGUID'], $this->UserID, TRUE);
        }
        $usrs = array($user_id);
        preg_match_all('/{{([0-9a-zA-Z\s:]+)}}/', $post_content, $matches);
        $mentions = array();
        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $match_details = explode(':', $match);
                $mentions[] = $match_details[1];
                if ($match_details[2] == '3' && $match_details[1] != $user_id) {
                    $usrs[] = $match_details[1];
                    if ($match_details[1] == $post_data['PostAsModuleEntityID']) {
                        $send_notification = 0;
                    }
                    initiate_worker_job('add_update_relationship_score', array('UserID' => $user_id, 'ModuleID' => 3, 'ModuleEntityID' => $match_details[1], 'Score' => 8));
                }
                $mention_id = $this->activity_model->add_mention($match_details[1], $match_details[2], $activity_id, $match_details[0]);
                $post_content = str_replace($match, $mention_id, $post_content);
            }
            $this->db->set('PostContent', $post_content);
            $this->db->where('ActivityID', $activity_id);
            $this->db->update(ACTIVITY);
        }

        $subscribe_users = array();
        if (!empty($usrs)) {
            foreach ($usrs as $usr) {
                $subscribe_users[] = array('ModuleEntityID' => $usr, 'ModuleID' => 3);
            }
        }
        if (!empty($subscribe_users)) {
            $this->subscribe_model->addUpdate($subscribe_users, $activity_id);
        }

        $status_id = 2;
        if (!empty($media[0]['MediaGUID'])) {
            $album_name = DEFAULT_WALL_ALBUM;
            if (count($media) == 1) {
                $media_guid = $media[0]['MediaGUID'];
                $media_type = get_media_type($media_guid);
                if ($media_type == 2) {
                    $album_name = DEFAULT_WALL_ALBUM;
                }
            }

            if ($this->activity_model->check_media_pending_status($activity_id)) {
                $status_id = 1;
                $this->activity_model->change_activity_status($activity_id, 1);
            }
        }

        // Send notification only if activity status is active
        if ($status_id == 2) {
            initiate_worker_job('send_post_notification', array(
                'UserID' => $user_id,
                'PostContent' => $post_data['Description'],
                'ActivityTypeID' => 18,
                'ActivityID' => $activity_id,
                'ModuleID' => $post_data['PostAsModuleID'],
                'ModuleEntityID' => $post_data['PostAsModuleEntityID'],
                'AfterProcess' => 0,
                'PostAsModuleID' => 0,
                'PostAsModuleEntityID' => 0,
                'ExcludedUsers' => array(),
                'PostType' => 0,
                'NotifyAll' => 0
            ));
        }

        if ($post_data['PostAsModuleID'] != 3) {
            // Update last activity date
            $this->load->helper('activity');
            set_last_activity_date($activity_id);
        }
        return TRUE;
    }

 
    /**
     * [update_poll_media Update poll option media]
     * @param  [array]  $media                  [Poll media]
     * @param  [type]  $poll_id                 [Poll ID]
     * @param  [array]  $poll_data              [Poll Data]
     * @param  [boolean] $from_activity         [is activity]
     * @return [boolean]                        [true/false]
     */
    public function update_poll_media($media, $poll_id, $poll_data, $from_activity = FALSE) {
        if (!empty($media) && !empty($poll_id)) {
            $media_data = array();
            foreach ($media as $key => $m) {
                if (!empty($m)) {
                    $album_id = get_album_id($this->UserID, DEFAULT_WALL_ALBUM, $poll_data['PostAsModuleID'], $poll_data['PostAsModuleEntityID']);
                    if ($from_activity) {
                        $module_id = 19;
                    } else {
                        $module_id = $this->module_id;
                    }
                    $media_data[] = array('MediaGUID' => $m['MediaGUID'], 'MediaSectionReferenceID' => $poll_id, 'ModuleID' => $module_id, 'StatusID' => '2', 'AlbumID' => $album_id);
                }
            }
            $this->db->update_batch(MEDIA, $media_data, 'MediaGUID');
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @Function - add poll activity
     * @Input 	- poll_id(int)
     * @Output 	- boolean
     */
    public function add_poll_activity($poll_id, $visibility, $commentable, $module_entity_owner, $module_entity, $is_media_exist, $poll_media_count = 0, $poll_content = '') {
        $this->load->model('activity/activity_model');
        $activity_id = $this->activity_model->addActivity($module_entity['PostAsModuleID'], $module_entity['PostAsModuleEntityID'], 25, $this->UserID, 0, $poll_content, $commentable, $visibility, array('PollID' => $poll_id, 'count' => $poll_media_count), $is_media_exist, $module_entity_owner, $module_entity['PostAsModuleID'], $module_entity['PostAsModuleEntityID']);

        //Update activityid in poll table
        $this->db->set('ActivityID', $activity_id);
        $this->db->where('PollID', $poll_id);
        $this->db->update(POLL);
        $this->db->set('PostType', 3);
        $this->db->where('ActivityID', $activity_id);
        $this->db->update(ACTIVITY);
        return $activity_id;
    }

    public function get_poll_option_vote_count($poll_id, $option_id) {
        $this->db->select('NoOfVotes AS OptionVoted');
        $res = $this->db->get_where(POLLOPTION, array('OptionID' => $option_id))->row_array();

        $this->db->select('COUNT(VoteID) AS TotalVotes');
        $res1 = $this->db->get_where(POLLOPTIONVOTES, array('PollID' => $poll_id))->row_array();
        return array_merge($res, $res1);
    }

    

    /* [get_poll_by_id Used to get poll details]
     * @param  [int] $poll_id               [Poll  ID]
     * @param  [int] $module_id             [module id]
     * @param  [int] $module_entity_id      [module entity id]
     * @return boolean              [description]
     */

    public function get_poll_by_id($poll_id, $module_id, $module_entity_id, $is_list = FALSE) {
        $this->db->select('P.PostAsModuleID,P.ActivityID, P.PostAsModuleEntityID, P.UserID, P.PollGUID, P.Description, P.CreatedDate, P.ModifiedDate, P.Status, P.IsAnonymous, IFNULL(P.ExpiryDateTime,"NEVER") AS ExpiryDateTime', FALSE);
        $this->db->select("(CASE WHEN ('" . gmdate("Y-m-d H:i:s") . "' > P.ExpiryDateTime) THEN 1 ELSE 0 END) AS IsExpired", FALSE);
        $this->db->from(POLL . ' P');
        $this->db->where('P.PollID', $poll_id);
        $this->db->where_in('P.Status', array('ACTIVE'));

        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if ($query->num_rows()) {
            $data = array();
            $this->load->model(array('flag_model'));
            foreach ($query->result_array() as $result) {
                $poll = $result;
                $IsOwner = 0;
                if (($module_id == $poll['PostAsModuleID']) && ($module_entity_id == $poll['PostAsModuleEntityID'])) {
                    $IsOwner = 1;
                }
                $post_for_data = $this->get_post_for($poll['ActivityID']);
                $poll['PostFor'] = $post_for_data['Users'];
                $poll['PostForCount'] = $post_for_data['Count'];
                $poll['IsOwner'] = $IsOwner;
                $poll['IsFlagged'] = 0;
                $poll['IsVoted'] = 0;
                $poll['Flaggable'] = 1;
                if ($module_id && $module_entity_id) {
                    if ($this->flag_model->is_flagged($this->UserID, $poll_id, 'POLL')) {
                        $poll['IsFlagged'] = 1;
                    }
                    if ($this->is_voted($poll_id, $module_id, $module_entity_id)) {
                        $poll['IsVoted'] = 1;
                    }
                }
                if ($poll['Status'] == "APPROVED") {
                    $poll['Flaggable'] = 0;
                }
                $poll['Media'] = $this->get_poll_media($poll['ActivityID'], 19);

                $poll['Options'] = $this->get_poll_options($poll_id, $module_id, $module_entity_id, $poll['IsAnonymous']);

                $poll['CreatedBy'] = $this->created_by($poll_id);


                unset($poll['CreatedBy']['Email']);
                unset($poll['ActivityID']);
                unset($poll['Status']);
                unset($poll['UserID']);
                unset($poll['PostAsModuleID']);
                unset($poll['PostAsModuleEntityID']);
                if ($is_list == TRUE) {
                    return $poll;
                }
                $data[] = $poll;
            }
            return $data;
        } else {
            return array();
        }
    }

    public function get_post_for_by_poll_id($poll_id) {
        $this->db->select('ActivityID');
        $this->db->from(POLL);
        $this->db->where('PollID', $poll_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $this->get_post_for($query->row()->ActivityID);
        }
    }

    public function get_post_for($activity_id) {
        $data = array('Users' => array(), 'Count' => 0);
        $this->db->select('ModuleID,ModuleEntityID');
        $this->db->from(MENTION);
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('Type', '2');
        $query = $this->db->get();
        $data['Count'] = $query->num_rows();
        if ($data['Count']) {
            $this->load->model(array('users/user_model'));
            foreach ($query->result_array() as $result) {
                $data['Users'][] = $this->user_model->getUserName(0, $result['ModuleID'], $result['ModuleEntityID']);
            }
        }
        return $data;
    }


    /**
     * [get_poll_media - Used to get poll media]
     * @param  [int] $module_entity_id  	[module entity id]
     * @param  [int] $module_id       	[module id]
     * @return array
     */
    public function get_poll_media($module_entity_id, $module_id) {
        $this->db->select('M.MediaGUID,M.ConversionStatus,A.AlbumName,M.LocationID,M.MediaID, IFNULL(M.ImageName,"") ImageName, M.Caption, IFNULL(M.NoOfComments, 0) NoOfComments, IFNULL(M.NoOfLikes,0) NoOfLikes, IFNULL(M.NoOfDislikes,0) NoOfDislikes, IFNULL(M.CreatedDate,"") CreatedDate, MT.Name as MediaType, M.UserID', FALSE);
        $this->db->select('IFNULL(M.ConversionStatus,"") AS ConversionStatus, IFNULL(M.VideoLength,"") AS VideoLength', FALSE);
        $this->db->select('IFNULL(MS.MediaSectionAlias, "") as MediaSectionAlias', FALSE);
        $this->db->from(MEDIA . ' M');
        $this->db->join(ALBUMS . ' A', 'A.AlbumID=M.AlbumID', 'LEFT');
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID = M.MediaSectionID', 'LEFT');
        $this->db->where('M.MediaSectionReferenceID', $module_entity_id);
        $this->db->where('M.ModuleID', $module_id);
        $this->db->where('M.StatusID', 2);
        $res = $this->db->get()->result_array();
        return $res;
    }

    
    /**
     * [created_by details of poll owner]
     * @param  [type] $poll_id          [poll id]
     * @return [type]                   [description]
     */
    function created_by($poll_id) {
        $this->db->select('(CASE PO.PostAsModuleID 
                        WHEN 3 THEN PU.Url
                        WHEN 18 THEN P.PageURL   
                        ELSE "" END) AS ProfileURL', FALSE);

        $this->db->select('(CASE PO.PostAsModuleID 
                        WHEN 1 THEN G.GroupGUID 
                        WHEN 3 THEN U.UserGUID 
                        WHEN 18 THEN P.PageGUID ELSE "" END) AS ModuleEntityGUID', FALSE);

        $this->db->select('(CASE PO.PostAsModuleID  
                        WHEN 3 THEN U.Email 
                        ELSE "" END) AS Email', FALSE);

        $this->db->select('(CASE PO.PostAsModuleID 
                        WHEN 1 THEN if(G.GroupImage!="",G.GroupImage,"group-no-img.png")
                        WHEN 3 THEN IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture)
                        WHEN 18 THEN IF(P.ProfilePicture="",CM.Icon,P.ProfilePicture)   
                        ELSE "" END) AS ProfilePicture', FALSE);

        $this->db->select('CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,""), " ",IFNULL(G.GroupName,""), " ",IFNULL(P.Title,"")) AS Name', FALSE);

        $this->db->select('PO.PostAsModuleID as ModuleID');
        $this->db->from(POLL . " PO");

        $this->db->join(USERS . " U", "U.UserID=PO.PostAsModuleEntityID AND PO.PostAsModuleID=3", "LEFT");
        $this->db->join(GROUPS . " G", "G.GroupID=PO.PostAsModuleEntityID AND PO.PostAsModuleID=1", "LEFT");
        $this->db->join(PAGES . " P", "P.PageID=PO.PostAsModuleEntityID AND PO.PostAsModuleID=18", "LEFT");
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->join(CATEGORYMASTER . " CM", "CM.CategoryID = P.CategoryID", "LEFT");

        $this->db->where('PO.PollID', $poll_id);

        $query = $this->db->get();

        if ($query->num_rows()) {
            return $query->row_array();
        }
        return array();
    }

    /**
     * [get_poll_activity_id]
     * @param  [int] $poll_id          [poll id]
     * @return [int] $activity_id      [activity id]
     */
    public function get_poll_activity_id($poll_id) {
        $activity_id = 0;
        $this->db->select('ActivityID');
        $this->db->from(POLL);
        $this->db->where('PollID', $poll_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $activity_id = $query->row()->ActivityID;
        }
        return $activity_id;
    }

    /**
     * [Update Polls Expiry]
     * @param  [int] $poll_guid        [poll guid]
     * @return [int] $expire_date      [expire date]
     */
    public function update_poll_expire_date($poll_guid, $expire_date_time) {
        if (empty($expire_date_time)) {
            $this->db->set('ExpiryDateTime', Null);
            $this->db->where('PollGUID', $poll_guid);
            $this->db->update(POLL);
            return TRUE;
        } else {
            $res = $this->db->get_where(POLL, array('PollGUID' => $poll_guid))->row_array();
            if (!empty($res)) {
                $current_expire = $res['ExpiryDateTime'];
                $current_date_time = strtotime($current_expire);
                $expire_time = strtotime($expire_date_time);
                if ($expire_time != $current_date_time) {
                    $expire_date_arr = explode(' ', $expire_date_time);
                    $current_expire_arr = explode(' ', $current_expire);

                    $current_date1 = strtotime($expire_date_arr[0]);
                    $expire_date1 = strtotime($current_expire_arr[0]);

                    $this->db->set('ExpiryDateTime', $expire_date_time);
                    if ($current_date1 != $expire_date1) {
                        $this->db->set('IsReminderSent', 0);
                    }
                    $this->db->where('PollGUID', $poll_guid);
                    $this->db->update(POLL);

                    // Update last activity date
                    $this->load->helper('activity');
                    set_last_activity_date($res['ActivityID']);
                }
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /*
      | Function to get activity id of poll created by given user
      | @Param : module_entity_id(int),module_id(int)
      | @Output: Array
     */

    public function get_poll_activities($module_entity_id = '', $module_id = '', $my_voted = FALSE, $my_polls = FALSE, $is_expired = FALSE, $anonymous_polls = FALSE) {
        $activity_ids = array();
        if (!empty($module_entity_id) && !empty($module_id)) {
            $this->db->select('P.ActivityID');
            $this->db->from(POLL . ' AS P');
            if ($my_voted == TRUE) {
                $this->db->JOIN(POLLOPTIONVOTES . ' AS PV', 'P.PollID=PV.PollID');
                $this->db->where('PV.ModuleEntityID', $module_entity_id);
                $this->db->where('PV.ModuleID', $module_id);
            }
            if ($my_polls == TRUE) {
                $this->db->where('P.PostAsModuleEntityID', $module_entity_id);
                $this->db->where('P.PostAsModuleID', $module_id);
            }
            if ($anonymous_polls == TRUE) {
                $this->db->where('P.IsAnonymous', 1);
            }
            $this->db->where('P.ActivityID IS NOT NULL', NULL);
            if ($is_expired == TRUE) {
                $this->db->where("P.ExpiryDateTime < '" . gmdate("Y-m-d H:i:s") . "'", NULL, FALSE);
            }
            $res = $this->db->get()->result_array();
            if (!empty($res)) {
                foreach ($res as $k => $r) {
                    $activity_ids[] = $r['ActivityID'];
                }
            }
        }

        // To prevent error in db
        if (count($activity_ids) == 0) {
            $activity_ids[] = 0;
        } else {
            $this->db->select('ActivityID');
            $this->db->from(ACTIVITY);
            $this->db->where_in('ParentActivityID', $activity_ids);
            $query = $this->db->get();

            if ($query->num_rows()) {
                foreach ($query->result() as $act) {
                    if (!in_array($act->ActivityID, $activity_ids)) {
                        $activity_ids[] = $act->ActivityID;
                    }
                }
            }
        }

        return $activity_ids;
    }

    /*
      | Function to get poll id of polls voted by given user
      | @Param : module_entity_id(int),module_id(int)
      | @Output: Array
     */

    public function get_my_Voted_polls($module_entity_id = '', $module_id = '') {
        $poll_ids = array();
        if (!empty($module_entity_id) && !empty($module_id)) {
            $this->db->select('P.PollID');
            $this->db->from(POLL . ' AS P');
            $this->db->JOIN(POLLOPTIONVOTES . ' AS PV', 'P.PollID=PV.PollID');
            $this->db->where('PV.ModuleEntityID', $module_entity_id);
            $this->db->where('PV.ModuleID', $module_id);
            $this->db->where('P.ActivityID IS NOT NULL', NULL);
            $res = $this->db->get()->result_array();
            if (!empty($res)) {
                foreach ($res as $k => $r) {
                    $poll_ids[] = $r['PollID'];
                }
            }
        }
        return $poll_ids;
    }

    /*
      | Function to get activity id of poll whom given user voted
      | @Param : module_entity_id(int),module_id(int)
      | @Output: Array
     */

    public function my_voted_poll_activities($module_entity_id = '', $module_id = '') {
        $activity_ids = array();
        if (!empty($module_entity_id) && !empty($module_id)) {
            $this->db->select('P.ActivityID');
            $this->db->from(POLL . ' AS P');
            $this->db->JOIN(POLLOPTIONVOTES . ' AS PV', 'P.PollID=PV.PollID');
            $this->db->where('PV.ModuleEntityID', $module_entity_id);
            $this->db->where('PV.ModuleID', $module_id);
            $this->db->where('ActivityID IS NOT NULL', NULL);
            $res = $this->db->get()->result_array();
            if (!empty($res)) {
                foreach ($res as $k => $r) {
                    $activity_ids[] = $r['ActivityID'];
                }
            }
        }
        return $activity_ids;
    }

    /**
     * [voters_list ]     
     * @param  [int] $poll_guid   [poll id]
     * @param  [string] $count_flag       [count flag]
     * @param  [string] $page_no        [page number]
     * @param  [string] $page_size      [page size]
     * @return [array/int]              [endorsement list/count]
     */
    function voters_list($poll_guid, $count_flag = FALSE, $page_no = '', $page_size = '', $visitor_module_id = '', $visitor_module_entity_id = '') {
        $this->db->select('(CASE POV.ModuleID 
                            WHEN 3 THEN PU.Url
                            WHEN 18 THEN P.PageURL   
                            ELSE "" END) AS ProfileURL', FALSE);

        $this->db->select('(CASE POV.ModuleID 
                            WHEN 1 THEN G.GroupGUID 
                            WHEN 3 THEN U.UserGUID 
                            WHEN 18 THEN P.PageGUID ELSE "" END) AS ModuleEntityGUID', FALSE);

        $this->db->select('(CASE POV.ModuleID 
                            WHEN 3 THEN U.UserTypeID
                            ELSE "" END) AS UserTypeID', FALSE);

        $this->db->select('(CASE POV.ModuleID 
                            WHEN 1 THEN if(G.GroupImage!="",G.GroupImage,"group-no-img.png")
                            WHEN 3 THEN IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture)
                            WHEN 18 THEN IF(P.ProfilePicture="","user_default.jpg",P.ProfilePicture)   
                            ELSE "" END) AS ProfilePicture', FALSE);

        $this->db->select('(CASE POV.ModuleID 
                            WHEN 18 THEN IF(CM.Name="","",CM.Name)   
                            ELSE "" END) AS CategoryName', FALSE);

        $this->db->select('CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,""), " ",IFNULL(G.GroupName,""), " ",IFNULL(P.Title,"")) AS Name', FALSE);

        $this->db->select('POV.ModuleID, POV.ModuleEntityID, POV.CreatedDate');
        $this->db->from(POLLOPTIONVOTES . " POV");
        $this->db->join(POLL . " PL", "PL.PollID=POV.PollID");
        $this->db->join(USERS . " U", "U.UserID=POV.ModuleEntityID AND POV.ModuleID=3", "LEFT");
        $this->db->join(GROUPS . " G", "G.GroupID=POV.ModuleEntityID AND POV.ModuleID=1", "LEFT");
        $this->db->join(PAGES . " P", "P.PageID=POV.ModuleEntityID AND POV.ModuleID=18", "LEFT");
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->join(CATEGORYMASTER . " CM", "CM.CategoryID = P.CategoryID", "LEFT");

        $this->db->where('PL.PollGUID', $poll_guid);
        $this->db->order_by('PL.CreatedDate', 'DESC');
        if (empty($count_flag)) { // check if array needed
            if (!empty($page_size)) { // Check for pagination
                $offset = ($page_no - 1) * $page_size;
                $this->db->limit($page_size, $offset);
            }
            $query = $this->db->get();
            $response = array();
            if ($query->num_rows()) {
                foreach ($query->result_array() as $result) {
                    $module_id = $result['ModuleID'];
                    $module_entity_id = $result['ModuleEntityID'];

                    $data['ModuleID'] = $module_id;
                    $data['ModuleEntityGUID'] = $result['ModuleEntityGUID'];
                    $data['Name'] = $result['Name'];
                    $data['ProfilePicture'] = $result['ProfilePicture'];
                    $data['ProfileURL'] = $result['ProfileURL'];
                    $data['ProfileTypeName'] = '';
                    $data['Location'] = array();

                    if ($module_id == 3 && $visitor_module_id == 3) {
                        if ($module_entity_id != $visitor_module_entity_id) {
                            $users_relation = get_user_relation($module_entity_id, $visitor_module_entity_id);
                            $privacy_details = $this->privacy_model->details($visitor_module_entity_id);
                            $privacy = ucfirst($privacy_details['Privacy']);
                            if ($privacy_details['Label']) {
                                foreach ($privacy_details['Label'] as $privacy_label) {

                                    if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                                        unset($userdata['Location']);
                                    }
                                    if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                        $userdata['ProfilePicture'] = 'user_default.jpg';
                                    }
                                }
                            }
                        }
                    }



                    if ($module_id == 1) {
                        $this->load->model('group/group_model');
                        $data['ProfileURL'] = $this->group_model->get_group_url($result['ModuleEntityID'], $result['Name'], false, 'index');
                    }

                    if ($module_id == 18) {
                        $data['ProfileURL'] = 'page/' . $result['ProfileURL'];
                        $data['ProfileTypeName'] = $result['CategoryName'];
                        $data['Location'] = array('Location' => $result['Location']);
                    }

                    unset($result['ModuleEntityID']);
                    unset($result['UserTypeID']);
                    $response[] = $data;
                }
            }
            return $response;
        } else {
            return $this->db->get()->num_rows();
        }
    }

    /**
     * [getFeedActivities Get the activity for dashboard]
     * @param  [int]       $user_id        [Current User ID]
     * @param  [int]       $page_no        [Page No]
     * @param  [int]       $page_size      [Page Size]
     * @param  [int]       $feed_sort_by    [Sort By value]
     * @param  [int]       $feed_user      [POST only of this user]
     * @param  [int]       $filter_type    [Post Filter Type ]
     * @param  [int]       $is_media_exists [Is Media Exists]
     * @param  [string]    $search_key     [Search Keyword]
     * @param  [string]    $start_date     [Start Date]
     * @param  [string]    $end_date       [End Date]
     * @return [Array]                    [Activity array]
     */
    public function getFeedActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = array(), $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $show_archive = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $entity_id = '', $entity_module_id = '', $expired = '', $anonymous = '') {
        $this->load->model(array('activity/activity_model', 'category/category_model', 'flag_model', 'users/user_model'));
        $time_zone = $this->user_model->get_user_time_zone();

        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);

        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();
        $friends[] = 0;
        $follow[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
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
        if (!in_array($user_id, $follow)) {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends)) {
            $friends[] = $user_id;
        }

        $group_list = $this->group_model->get_users_groups($user_id);
        $group_list[] = 0;
        $group_list = implode(',', $group_list);

        $my_friends = implode(',', $friends);
        //$friend_of_friends = $this->user_model->get_friends_of_friend($user_id);
        $friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friend_of_friends[] = 0;
        $friend_of_friends = implode(',', $friend_of_friends);

        $activity_type_allow = array(9, 10, 25);
        $modules_allowed = array(3, 18);
        $show_suggestions = FALSE;
        $show_media = TRUE;

        /* --Filter by activity type id-- */
        $activity_ids = array();
        if (isset($filter_type)) {
            $show_suggestions = false;

            //10 = My Polls, 11= Expired
            if ($filter_type == '0' || $filter_type == '1' || $filter_type == '2') {
                $is_expired = FALSE;
                $anonymous_polls = FALSE;
                $my_polls = FALSE;
                $my_voted = FALSE;
                if ($filter_type == 1) {
                    $my_polls = TRUE;
                }
                if (!empty($expired)) {
                    $is_expired = TRUE;
                }
                if ($filter_type == 2) {
                    $my_voted = TRUE;
                }
                if (!empty($anonymous)) {
                    $anonymous_polls = TRUE;
                }

                if ($my_polls == TRUE || $is_expired == TRUE || $my_voted == TRUE || $anonymous_polls == TRUE) {
                    $activity_ids = $this->get_poll_activities($entity_id, $entity_module_id, $my_voted, $my_polls, $is_expired, $anonymous_polls);
                    if (empty($activity_ids)) {
                        return array();
                    }
                }
            } elseif ($filter_type == '15') {
                $activity_ids = $this->get_polls_about_to_close($user_id, $entity_id, $entity_module_id);
                if (empty($activity_ids)) {
                    return array();
                }
            }
        }

        $privacy_conditions = "(
            IF(A.Privacy=1,TRUE,'') 
            OR 
            IF(A.Privacy=2,(
                A.UserID IN(" . $friend_of_friends . ")
            ),'') 
            OR 
            IF(A.Privacy=3,(
                A.UserID IN(" . $my_friends . ")
            ),'') 
            OR 
            IF(A.Privacy=4,(A.UserID='" . $user_id . "' OR (SELECT ActivityID FROM " . MENTION . " WHERE ((ModuleID='3' AND ModuleEntityID='" . $user_id . "') OR (ModuleID='1' AND ModuleEntityID IN(" . $group_list . "))) AND ActivityID=A.ActivityID LIMIT 1) is not null),'')
        )";

        $privacy_share_condition = "
        IF(A.ActivityTypeID IN(9,10),
            A.ParentActivityID=(
                SELECT ActivityID FROM " . ACTIVITY . " WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                    (IF(Privacy=1 AND ActivityTypeID=25,true,false) OR
                    IF(Privacy=2 AND ActivityTypeID=25,A.UserID IN (" . $friend_of_friends . "),false) OR
                    IF(Privacy=3 AND ActivityTypeID=25,A.UserID IN (" . $my_friends . "),false) OR
                    IF(Privacy=4 AND ActivityTypeID=25,(A.UserID='" . $user_id . "' OR (SELECT ActivityID FROM " . MENTION . " WHERE ModuleID='3' AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null),false)
                    )
            ),true
        )";

        $this->db->select('A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture');
        $this->db->select('IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK', false);
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->join(MODULES . ' M1', 'A.ModuleID=M1.ModuleID', 'left');
        $this->db->join(MODULES . ' M2', 'ATY.ModuleID=M2.ModuleID', 'left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
        $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');
        if ($filter_type == 15) {
            $this->db->join(POLL . ' PL', 'PL.ActivityID=A.ActivityID');
        }
        $this->db->_protect_identifiers = TRUE;

        /* Join Activity Links Starts */
        $this->db->select('IF(URL is NULL,0,1) as IsLinkExists', false);
        $this->db->select('URL as LinkURL,Title as LinkTitle,MetaDescription as LinkDesc,ImageURL as LinkImgURL,TagsCollection as LinkTags');
        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        /* Join Activity Links Ends */

        if ($show_archive && !$this->settings_model->isDisabled(43)) {
            $this->db->_protect_identifiers = FALSE;
            $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
            $this->db->_protect_identifiers = TRUE;
        } else if (!$activity_guid && !$this->settings_model->isDisabled(43)) {
            $this->db->where("A.ActivityID NOT IN (SELECT ActivityID FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND UserID='" . $user_id . "')", NULL, FALSE);
        }

        if ($filter_type == 7) {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $user_id);
        } else {
            if ($filter_type == 4 && !$this->settings_model->isDisabled(43)) {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
                $this->db->_protect_identifiers = TRUE;
            } else if ($filter_type == 14) { //favourite
                $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID AND F.EntityType="ACTIVITY"');
                $this->db->where('F.UserID', $user_id);
                $this->db->where('F.StatusID', '2');
            }

            if ($mentions) {
                $join_condition = "MN.ActivityID=A.ActivityID AND (";
                foreach ($mentions as $mention) {
                    $join_cond[] = "(MN.ModuleEntityID='" . $mention['ModuleEntityID'] . "' AND MN.ModuleID='" . $mention['ModuleID'] . "')";
                }
                $join_cond = implode(' OR ', $join_cond);
                $join_condition .= $join_cond . ")";

                $this->db->_protect_identifiers = FALSE;
                $this->db->join(MENTION . " MN", $join_condition, "join");
                $this->db->_protect_identifiers = TRUE;
            }

            $this->db->_protect_identifiers = FALSE;
            $this->db->join(MUTESOURCE . ' MS', 'MS.UserID="' . $user_id . '" AND ((MS.ModuleID=A.ModuleID AND MS.ModuleEntityID=A.ModuleEntityID) OR (MS.ModuleID=3 AND MS.ModuleEntityID=A.UserID AND A.ModuleEntityOwner=0))', 'left');
            $this->db->where('MS.ModuleEntityID is NULL', null, false);
            $this->db->_protect_identifiers = TRUE;

            $this->db->where_in('A.ModuleID', $modules_allowed);
            $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
            // Only needed given activities

            if ($privacy_conditions) {
                $this->db->where($privacy_conditions, null, false);
            }

            if ($privacy_share_condition) {
                $this->db->where($privacy_share_condition, null, false);
            }

            if (!empty($activity_ids)) {
                $this->db->where_in('A.ActivityID', $activity_ids);
            }

            if ($activity_guid) {
                $this->db->where('A.ActivityGUID', $activity_guid);
            }
            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);
        }

        if ($feed_user) {
            $this->db->where('A.PostAsModuleID', '3');
            $this->db->where('A.PostAsModuleEntityID', $feed_user);
        }

        if (!$show_media) {
            if ($is_media_exists == 2) {
                $is_media_exists = '0';
            }
            if ($is_media_exists == 1) {
                $is_media_exists = '3';
            }
        }

        if ($is_media_exists != 2) {
            $this->db->where('A.IsMediaExist', $is_media_exists);
        }

        if (!empty($search_key)) {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }

        if (!empty($blocked_users) && empty($feed_user)) {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }
        $this->db->where('M1.IsActive', '1');
        $this->db->where('M2.IsActive', '1');
        $this->db->where('ATY.StatusID', '2');
        $this->db->where_in('A.ModuleID', $modules_allowed);

        if (!$this->settings_model->isDisabled(28)) {
            $this->db->select("R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus", FALSE);
            $this->db->select("IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder", false);

            $this->db->_protect_identifiers = FALSE;
            $jointype = 'left';
            $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            if ($filter_type == 3) {
                $jointype = 'join';
                $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            } else {
                if (!$activity_guid) {
                    $this->db->where("(R.Status IS NULL OR R.Status='ACTIVE')");
                }
            }

            $this->db->join(REMINDER . " R", $joincondition, $jointype);

            $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            $this->db->_protect_identifiers = TRUE;
        }

        if ($filter_type == 15) {
            $this->db->order_by('rand()');
        } else {
            if ($feed_sort_by == 'popular') {
                $this->db->where_in('A.ActivityTypeID', array(1, 7, 11, 12));
                $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
                $this->db->where('A.NoOfComments>1', null, false);
                $this->db->order_by('A.ActivityTypeID', 'ASC');
                $this->db->order_by('A.NoOfComments', 'DESC');
                $this->db->order_by('A.NoOfLikes', 'DESC');
            } elseif ($feed_sort_by == 1) {
                $this->db->order_by('A.ActivityID', 'DESC');
            } else {
                $this->db->order_by('A.ModifiedDate', 'DESC');
            }
        }

        if ($filter_type == 3) {
            if ($ReminderDate) {
                $this->db->where_in("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $ReminderDate, FALSE);
            }
        }

        if ($start_date) {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date) {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        $this->db->where_not_in('U.StatusID', array(3, 4));

        if (!$count_only) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $result = $this->db->get();
        if ($count_only) {
            return $result->num_rows();
        }
        $return = array();
        if ($result->num_rows()) {
            $cnt = 1;
            foreach ($result->result_array() as $res) {
                $activity = array();
                //Suggested Posts
                if (($cnt == 3 || $cnt == 6 || $cnt == 9) && $page_no == 1 && $show_suggestions) {
                    $activity['Album'] = array();
                    $ViewTemplate = '';
                    if ($cnt == 3) {
                        $ViewTemplate = 'SuggestedPages';
                    }
                    if ($cnt == 6) {
                        $ViewTemplate = 'UpcomingEvents';
                    }
                    if ($cnt == 9) {
                        $ViewTemplate = 'SuggestedGroups';
                    }
                    $activity['ViewTemplate'] = $ViewTemplate;
                    $return[] = $activity;
                }

                $activity_id = $res['ActivityID'];
                $activity_guid = $res['ActivityGUID'];
                $module_id = $res['ModuleID'];
                $activity_type_id = $res['ActivityTypeID'];
                $module_entity_id = $res['ModuleEntityID'];
                $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);
                if (in_array($res['UserID'], $BUsers)) {
                    continue;
                }

                $activity['IsDeleted'] = 0;
                if ($filter_type == 7) {
                    $activity['IsDeleted'] = 1;
                }
                $activity['RatingData'] = array();
                $activity['PollData'] = array();
                $activity['IsEntityOwner'] = 0;
                $activity['IsOwner'] = 0;
                $activity['IsFlagged'] = 0;
                $activity['CanShowSettings'] = 0;
                $activity['CanRemove'] = 0;
                $activity['CanMakeSticky'] = 0;
                $activity['ShowPrivacy'] = 0;
                $activity['IsMember'] = 1;
                $activity['Reminder'] = array();
                $activity['Files'] = array();
                $activity['Album'] = array();

                //Link Variable Assignment Starts
                $activity['IsLinkExists'] = $res['IsLinkExists'];
                $activity['LinkURL'] = $res['LinkURL'];
                $activity['LinkTitle'] = $res['LinkTitle'];
                $activity['LinkDesc'] = $res['LinkDesc'];
                $activity['LinkImgURL'] = $res['LinkImgURL'];
                $activity['LinkTags'] = $res['LinkTags'];
                $activity['PostAsModuleEntityID'] = $res['PostAsModuleEntityID'];
                $activity['PostAsModuleID'] = $res['PostAsModuleID'];
                //Link Variable Assignment Ends

                $activity['IsTagged'] = (in_array($res['ActivityID'], $this->activity_model->get_user_tagged())) ? 1 : 0;
                if (isset($res['ReminderGUID'])) {
                    $activity['Reminder'] = array('ReminderGUID' => $res['ReminderGUID'], 'ReminderDateTime' => $res['ReminderDateTime'], 'CreatedDate' => $res['ReminderCreatedDate'], 'Status' => $res['ReminderStatus']);
                }

                $activity['ModuleEntityID'] = $res['ModuleEntityID'];
                $activity['PostAsEntityOwner'] = 0;
                $activity['OriginalActivityGUID'] = '';

                $activity['ActivityGUID'] = $activity_guid;
                $activity['ModuleID'] = $module_id;
                $activity['UserGUID'] = $res['UserGUID'];
                $activity['ActivityType'] = $res['ActivityType'];
                $activity['NoOfFavourites'] = $res['NoOfFavourites'];

                if ($BUsers) {
                    if ($res['ActivityTypeID'] == '23' || $res['ActivityTypeID'] == '24') {
                        $params = json_decode($res['Params'], true);
                        $entity_id = get_detail_by_guid($params['MediaGUID'], 21, 'MediaID', 1);
                        $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count('Media', $entity_id, $BUsers);
                        $activity['NoOfLikes'] = $this->activity_model->get_like_count($entity_id, "MEDIA", $BUsers);
                    } else {
                        $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count('Activity', $activity_id, $BUsers);
                        $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, "ACTIVITY", $BUsers);
                    }
                } else {
                    $activity['NoOfComments'] = $res['NoOfComments'];
                    $activity['NoOfLikes'] = $res['NoOfLikes'];
                    $activity['NoOfDislikes'] = 0;
                }
                $activity['NoOfShares'] = $res['NoOfShares'];
                $activity['Message'] = $res['Template'];
                $activity['ViewTemplate'] = $res['ViewTemplate'];

                $activity['IsArchive'] = 0;
                $user_archive = $this->activity_model->get_user_activity_archive();
                if (isset($user_archive[$activity_id])) {
                    $activity['IsArchive'] = $user_archive[$activity_id];
                }

                $activity['CommentsAllowed'] = 0;
                if ($res['IsCommentable'] && $res['CommentsAllowed']) {
                    $activity['CommentsAllowed'] = 1;
                }

                $activity['LikeAllowed'] = $res['LikeAllowed'];
                $activity['FlagAllowed'] = $res['FlagAllowed'];
                $activity['ShareAllowed'] = $res['ShareAllowed'];
                $activity['FavouriteAllowed'] = $res['FavouriteAllowed'];

                if ($res['PostAsModuleID'] == 18) {
                    $activity['EntityModuleType'] = 'Page';
                } else {
                    $activity['EntityModuleType'] = 'User';
                }

                $activity['CreatedDate'] = $res['CreatedDate'];
                $activity['ModifiedDate'] = $res['ModifiedDate'];

                $activity['IsSticky'] = 0;

                $activity['Visibility'] = $res['Privacy'];
                $activity['PostContent'] = $res['PostContent'];

                $activity['Album'] = $this->activity_model->get_albums($activity_id, $res['UserID']);
                $activity['Params'] = json_decode($res['Params']);
                $activity['IsSubscribed'] = (in_array($activity_id, $this->subscribe_model->get_user_subscribed())) ? 1 : 0;
                $activity['IsFavourite'] = (in_array($activity_id, $this->favourite_model->get_user_favourite())) ? 1 : 0;


                $activity['Flaggable'] = $res['Flaggable'];
                $activity['FlaggedByAny'] = 0;
                $activity['ParentActivityID'] = $res['ParentActivityID'];

                $SharedActivityDetail = $this->activity_model->getSharedActivityDetail($res['ParentActivityID']);
                $activity['SharedActivityModule'] = $SharedActivityDetail['SharedActivityModule'];
                $activity['SharedEntityGUID'] = $SharedActivityDetail['SharedEntityGUID'];

                $activity['CanBlock'] = 0;

                if ($res['UserID'] == $user_id) {
                    $activity['IsOwner'] = 1;
                }

                $activity['IsFlagged'] = (in_array($activity_id, $this->flag_model->get_user_flagged())) ? 1 : 0;

                if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3) {
                    $activity['IsOwner'] = 1;
                    $activity['CanRemove'] = 1;
                }

                $activity['EntityName'] = '';
                $activity['EntityProfilePicture'] = '';

                $activity['UserName'] = $res['FirstName'] . ' ' . $res['LastName'];
                $activity['UserProfilePicture'] = $res['ProfilePicture'];
                $activity['UserProfileURL'] = get_entity_url($res['UserID'], 'User', 1);
                $activity['EntityType'] = '';

                if ($module_id == 1) {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "Type, GroupGUID, GroupName, GroupImage", 2);
                    if ($entity) {
                        $activity['EntityName'] = $entity['GroupName'];
                        $activity['EntityProfilePicture'] = $entity['GroupImage'];
                        $activity['EntityProfileURL'] = $module_entity_id;
                        $activity['EntityGUID'] = $entity['GroupGUID'];
                        if ($res['ModuleEntityOwner'] == 1) {
                            $activity['UserName'] = $activity['EntityName'];
                            $activity['UserProfilePicture'] = $activity['EntityProfilePicture'];
                            $activity['UserProfileURL'] = $activity['EntityProfileURL'];
                            $activity['UserGUID'] = $activity['EntityGUID'];
                        }

                        if ($entity['Type'] == 'INFORMAL') {
                            $activity['EntityMembersCount'] = $this->group_model->members($module_entity_id, $user_id, TRUE);
                            $activity['EntityMembers'] = $this->group_model->members($module_entity_id, $user_id);
                        }
                    }

                    if ($this->group_model->is_admin($user_id, $module_entity_id)) {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanRemove'] = 1;
                        $activity['CanBlock'] = 1;
                    }
                    if ($this->group_model->check_group_creator($res['UserID'], $module_entity_id)) {
                        $activity['CanBlock'] = 0;
                    }
                }
                if ($module_id == 3) {
                    $activity['EntityName'] = $activity['UserName'];
                    $activity['EntityProfilePicture'] = $activity['UserProfilePicture'];
                    $activity['EntityGUID'] = $activity['UserGUID'];

                    $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                    if ($entity) {
                        $entity['EntityName'] = trim($entity['FirstName'] . ' ' . $entity['LastName']);
                        $activity['EntityName'] = $entity['EntityName'];
                        $activity['EntityGUID'] = $entity['UserGUID'];
                    }

                    $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                    if ($user_id == $module_entity_id) {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanRemove'] = 1;
                        $activity['CanBlock'] = 1;
                    }
                }
                $activity['ShowBTNCommentsAllowed'] = 1;
                $activity['MuteAllowed'] = 1;
                $activity['ShowFlagBTN'] = 1;
                $activity['ShowInviteGraph'] = 0;

                if ($res['ActivityTypeID'] == 16 || $res['ActivityTypeID'] == 17) {
                    $params = json_decode($res['Params']);
                    $activity['RatingData'] = $this->rating_model->get_rating_by_id($params->RatingID, $user_id);
                    $activity['FavouriteAllowed'] = 1;
                    $activity['ShareAllowed'] = 1;
                    $activity['CommentsAllowed'] = 1;
                    $activity['ShowBTNCommentsAllowed'] = 0;
                    $activity['MuteAllowed'] = 0;
                    $activity['ShowFlagBTN'] = 0;
                } else if ($res['ActivityTypeID'] == 25) {
                    $params = json_decode($res['Params']);
                    $activity['PollData'] = $this->get_poll_by_id($params->PollID, $entity_module_id, $entity_id);
                    $activity['MuteAllowed'] = 0;
                    $activity['ShowFlagBTN'] = 0;

                    $user_details_invite = $this->get_invite_status('3', $user_id, $params->PollID);
                    if ($user_details_invite['TotalInvited'] > 0) {
                        $activity['ShowInviteGraph'] = 1;
                    }
                }
                if ($module_id == 14) {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title, ProfileImageID", 2);
                    if ($entity) {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfileImageID'];
                        $activity['EntityGUID'] = $entity['EventGUID'];
                    }

                    $activity['EntityProfileURL'] = get_guid_by_id($module_entity_id, 14);

                    if ($this->event_model->isEventOwner($module_entity_id, $user_id)) {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                        $activity['ShowPrivacy'] = 0;
                        $activity['CanBlock'] = 1;
                    }
                    if ($this->event_model->isEventOwner($module_entity_id, $res['UserID'])) {
                        $activity['CanBlock'] = 0;
                    }
                }
                if ($module_id == 18) {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                    if ($entity) {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        $activity['EntityProfileURL'] = $entity['PageURL'];
                        $activity['EntityGUID'] = $entity['PageGUID'];
                        $category_name = $this->category_model->get_category_by_id($entity['CategoryID']);
                        $category_icon = $category_name['Icon'];
                        if ($entity['ProfilePicture'] == '') {
                            $activity['EntityProfilePicture'] = "icon_" . $category_icon;
                        }
                        //$activity['UserProfilePicture'] = $activity['EntityProfilePicture'];
                        if ($res['ModuleEntityOwner'] == 1 || !empty($activity['RatingData'])) {
                            $activity['UserName'] = $activity['EntityName'];
                            $activity['UserProfilePicture'] = $activity['EntityProfilePicture'];
                            $activity['UserProfileURL'] = $activity['EntityProfileURL'];
                            $activity['UserGUID'] = $activity['EntityGUID'];
                        }

                        if ($res['ModuleEntityOwner'] == 0 && $res['ActivityTypeID'] == 12) {
                            $activity['Message'] = $activity['Message'] . ' posted in {{Entity}}';
                        }
                        $activity['ModuleEntityOwner'] = $res['ModuleEntityOwner'];
                    }
                    $activity['PostAsEntityOwner'] = $res['ModuleEntityOwner'];
                    if ($this->page_model->check_page_owner($user_id, $module_entity_id)) {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanBlock'] = 1;
                    }
                    if ($this->page_model->check_page_creator($res['UserID'], $module_entity_id)) {
                        $activity['CanBlock'] = 0;
                    }
                    if ($res['ModuleEntityOwner'] == 1) {
                        $activity['CanBlock'] = 0;
                    }
                }

                if ($res['UserID'] == $user_id) {
                    $activity['CanBlock'] = 0;
                }

                if (!isset($activity['EntityProfileURL'])) {
                    $activity['EntityProfileURL'] = $activity['UserProfileURL'];
                }

                if ($activity_type_id == 9 || $activity_type_id == 10 || $activity_type_id == 14 || $activity_type_id == 15) {
                    $originalActivity = $this->activity_model->get_activity_details($res['ParentActivityID'], $activity_type_id);
                    $activity['ActivityOwner'] = $this->user_model->getUserName($originalActivity['UserID'], $originalActivity['ModuleID'], $originalActivity['ModuleEntityID']);
                    $activity['ActivityOwnerLink'] = $activity['ActivityOwner']['ProfileURL'];
                    $activity['ActivityOwner'] = $activity['ActivityOwner']['FirstName'] . ' ' . $activity['ActivityOwner']['LastName'];
                    $activity['Album'] = $originalActivity['Album'];
                    $activity['SharePostContent'] = $activity['PostContent'];
                    $activity['PostContent'] = $originalActivity['PostContent'];
                    if ($activity_type_id == 10 || $activity_type_id == 15) {
                        if ($originalActivity['UserID'] == $res['UserID']) {
                            $activity['Message'] = str_replace("{{OBJECT}}'s", $this->notification_model->get_gender($originalActivity['UserID']), $activity['Message']);
                        } else {
                            if ($originalActivity['ParentActivityTypeID'] == '11' || $originalActivity['ParentActivityTypeID'] == '7') {
                                $u_d = get_detail_by_id($originalActivity['UserID'], 3, 'FirstName,LastName', 2);
                                if ($u_d) {
                                    $activity['Message'] = str_replace("{{OBJECT}}", $u_d['FirstName'] . ' ' . $u_d['LastName'], $activity['Message']);
                                }
                            }
                        }
                    }
                    if ($res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15') {
                        $activity['Album'] = $this->activity_model->get_albums($activity['ParentActivityID'], $res['UserID'], '', 'Media');
                        if (!empty($activity['Album']['AlbumType'])) {
                            $activity['EntityType'] = ucfirst(strtolower($activity['Album']['AlbumType']));
                        } else {
                            $activity['EntityType'] = 'Media';
                        }
                    } else {
                        $activity['EntityType'] = 'Post';
                        if ($originalActivity['ParentActivityTypeID'] == 5 || $originalActivity['ParentActivityTypeID'] == 6) {
                            $activity['EntityType'] = 'Album';
                        }
                        if (!empty($originalActivity['Album'])) {
                            $activity['EntityType'] = 'Media';
                        }
                        $activity['OriginalActivityGUID'] = $originalActivity['ActivityGUID'];
                    }

                    if (isset($originalActivity['ParentActivityTypeID']) && $originalActivity['ParentActivityTypeID'] == 25) {
                        $params = json_decode($originalActivity['Params']);
                        $activity['PollData'] = $this->get_poll_by_id($params->PollID, $entity_module_id, $entity_id);

                        $user_details_invite = $this->get_invite_status('3', $user_id, $params->PollID);
                        if ($user_details_invite['TotalInvited'] > 0) {
                            $activity['ShowInviteGraph'] = 1;
                        }
                    }
                }

                if ($activity_type_id == 5 || $activity_type_id == 6 || $activity_type_id == 10 || $activity_type_id == 9) {
                    $album_flag = TRUE;
                    if ($activity_type_id == 10 || $activity_type_id == 9) {
                        $album_flag = FALSE;
                        $parent_activity_detail = get_detail_by_id($activity['ParentActivityID'], '', 'ActivityTypeID,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        if (!empty($parent_activity_detail)) {
                            if (in_array($parent_activity_detail['ActivityTypeID'], array(5, 6))) {
                                if (!empty($parent_activity_detail['Params'])) {
                                    $album_detail = json_decode($parent_activity_detail['Params'], TRUE);
                                    if (!empty($album_detail['AlbumGUID'])) {
                                        @$activity['Params']->AlbumGUID = $album_detail['AlbumGUID'];
                                        $album_flag = TRUE;
                                    }
                                }
                            }
                        }
                    }
                    if ($album_flag) {
                        $count = 4;
                        if ($activity_type_id == 6) {
                            $count = $activity['Params']->count;
                        }
                        $album_details = $this->album_model->get_album_by_guid($activity['Params']->AlbumGUID);
                        $activity['AlbumEntityName'] = $activity['EntityName'];
                        $activity['EntityName'] = $album_details['AlbumName'];
                        $activity['Album'] = $this->activity_model->get_albums($activity_id, $res['UserID'], $activity['Params']->AlbumGUID, 'Activity', $count);
                    }
                }

                $activity['PostContent'] = $this->activity_model->parse_tag($activity['PostContent']);

                if ($activity['IsEntityOwner'] == 1) {
                    $activity['LikeName'] = $this->activity_model->getLikeName($activity_id, $user_id, $res['ModuleEntityOwner'], $BUsers);
                } else {
                    $activity['LikeName'] = $this->activity_model->getLikeName($activity_id, $user_id, 0, $BUsers);
                }

                //check is liked or not                
                $like_entity_type = 'ACTIVITY';
                $like_entity_id = $activity_id;
                if (in_array($activity_type_id, array(23, 24))) {
                    $params = json_decode($res['Params']);
                    if ($params->MediaGUID) {
                        $media_id = get_detail_by_guid($params->MediaGUID, 21, "MediaID", 1);
                        if ($media_id) {
                            $like_entity_type = 'MEDIA';
                            $like_entity_id = $media_id;
                        }
                    }
                }

                if ($activity['IsOwner'] == 1) {
                    $activity['IsLike'] = $this->activity_model->is_liked($like_entity_id, $like_entity_type, $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID']);
                    $activity['IsDislike'] = $this->activity_model->is_liked($like_entity_id, $like_entity_type, $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID'], 3);
                } else {
                    $activity['IsLike'] = $this->activity_model->is_liked($like_entity_id, $like_entity_type, $user_id, 3, $user_id);
                    $activity['IsDislike'] = $this->activity_model->is_liked($like_entity_id, $like_entity_type, $user_id, 3, $user_id, 3);
                }

                unset($activity['LikeName']['IsLike']);

                if ($res['ActivityTypeID'] == '1' || $res['ActivityTypeID'] == '8' || $res['ActivityTypeID'] == '9' || $res['ActivityTypeID'] == '10' || $res['ActivityTypeID'] == '7' || $res['ActivityTypeID'] == '11' || $res['ActivityTypeID'] == '12' || $res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15' || $res['ActivityTypeID'] == '5' || $res['ActivityTypeID'] == '6') {
                    $activity['CanShowSettings'] = 1;
                }


                if ($res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 8) {
                    //$activity['ShowSticky']         = 0;
                }


                if ($res['Privacy'] != 4 && ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 8 || $res['ActivityTypeID'] == 9 || $res['ActivityTypeID'] == 10 || $res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 11 || $res['ActivityTypeID'] == 12 || $res['ActivityTypeID'] == 14 || $res['ActivityTypeID'] == 15 || $res['ActivityTypeID'] == 5 || $res['ActivityTypeID'] == 6)) {
                    $activity['ShareAllowed'] = 1;
                }

                if ($user_id == $res['UserID']) {
                    $activity['ShareAllowed'] = 0; // do not show share likn for self post
                    $activity['ShowPrivacy'] = 0;
                    if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 8 || $res['ActivityTypeID'] == 9 || $res['ActivityTypeID'] == 10) {
                        $activity['ShowPrivacy'] = 1;
                    }
                }
                $activity['Comments'] = $this->activity_model->getActivityComments('Activity', $activity_id, '1', COMMENTPAGESIZE, $user_id, $activity['CanRemove'], 2, TRUE, $BUsers, FALSE, '', $res['PostAsModuleID'], $res['PostAsModuleEntityID']);

                if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 11 || $res['ActivityTypeID'] == 12) {
                    $activity['PostContent'] = str_replace('­', '', $activity['PostContent']);
                    if (empty($activity['PostContent'])) {
                        $pcnt = $this->activity_model->get_photos_count($res['ActivityID']);
                        if (isset($pcnt['Media'])) {
                            $activity['Message'] .= ' added ' . $pcnt['MediaCount'] . ' new ' . $pcnt['Media'];
                        }
                    }
                }
                $activity['LikeList'] = $this->activity_model->getLikeDetails($activity['ActivityGUID'], 'ACTIVITY', array(), 0, 12, FALSE, $user_id);

                if (isset($activity['RatingData']['CreatedBy']['ModuleID'])) {
                    $activity['UserProfileURL'] = $activity['RatingData']['CreatedBy']['ProfileURL'];
                    $activity['UserProfilePicture'] = $activity['RatingData']['CreatedBy']['ProfilePicture'];
                }

                $permission = $this->privacy_model->check_privacy($user_id, $res['UserID'], 'view_profile_picture');
                if (!$permission && $module_id == 3) {
                    $activity['UserProfilePicture'] = 'user_default.jpg';
                }
                $activity['PostContent'] = trim(str_replace('&nbsp;', ' ', $activity['PostContent']));

                $activity['ShowPrivacy'] = 0;
                if ($user_id == $res['UserID']) {
                    if (!$this->is_posted_for_other($res['ActivityID'])) {
                        $activity['ShowPrivacy'] = 1;
                    }
                }

                /* Share Details */
                $share_data = array();
                if ($res['ActivityTypeID'] == '9' || $res['ActivityTypeID'] == '10' || $res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15') {
                    $share_data = $this->activity_result_filter_model->getShareDetails($activity, $activity_type_id, $res['UserID']);

                    if ($activity_type_id == 10 || $activity_type_id == 15) {
                        if ($share_data['ModuleID'] == '1' && $share_data['PostType'] == '7') {
                            $activity['Message'] = str_replace("{{OBJECT}}", "{{ACTIVITYOWNER}}", $activity['Message']);
                        } else {
                            if ($share_data['UserID'] == $res['UserID']) {
                                $activity['Message'] = str_replace("{{OBJECT}}'s", $this->notification_model->get_gender($share_data['UserID']), $activity['Message']);
                            }
                        }
                    }
                    unset($share_data['ParentActivityTypeID']);
                }
                $activity['ShareDetails'] = $share_data;

                $return[] = $activity;
                $cnt++;
            }
        }
        return $return;
    }

    /**
     * Function to get polls about to close
     * @param $user_id
     * @param $entity_id
     * @param $module_id
     * @param int $page_no
     * @param int $page_size
     * @return array
     */
    public function get_polls_about_to_close($user_id, $entity_id, $module_id, $page_no = 0, $page_size = 0) {
        $my_voted_polls = $this->get_my_Voted_polls($entity_id, $module_id);
        $this->db->select('P.ActivityID');
        $this->db->from(POLL . ' AS P');
        $this->db->where('P.Status', 'ACTIVE');
        if (!empty($my_voted_polls)) {
            $this->db->where_not_in('P.PollID', $my_voted_polls);
        }
        $this->db->where("DATE_FORMAT(P.ExpiryDateTime,'%Y-%m-%d %H:%i:%s') > '" . get_current_date('%Y-%m-%d %H:%i:%s') . "' AND DATE_FORMAT(P.ExpiryDateTime,'%Y-%m-%d %H:%i:%s') <= '" . get_current_date('%Y-%m-%d %H:%i:%s', 2, 1) . "' AND P.ExpiryDateTime is not NULL", NULL, FALSE);
        //$this->db->order_by('P.ExpiryDateTime','ASC');
        $this->db->order_by('rand()');
        $result = $this->db->get();
        //echo $this->db->last_query();
        $res = $result->result_array();
        $res_activity = array();
        if (!empty($res)) {
            foreach ($res as $k => $r) {
                $res_activity[] = $r['ActivityID'];
            }
        }
        return $res_activity;
    }

    /**
     * [is_posted_for_other description]
     * @param  [int]  $activity_id [activity_id]
     * @return boolean              [true/false]
     */
    public function is_posted_for_other($activity_id) {
        $this->db->select('MentionID');
        $this->db->from(MENTION);
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('Type', '2');
        $query = $this->db->get();
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to invite friends and groups for poll
     * @param $poll_id
     * @param $invite_members
     * @param $invited_by_module_entity_id
     * @param $invited_by_module_id
     * @return bool
     */
    public function invite_friends_and_groups($poll_id, $invite_members, $invited_by_module_entity_id, $invited_by_module_id, $is_reminder = FALSE) {
        $invite_members_array = array();
        $status = TRUE;
        foreach ($invite_members as $key => $member) {
            $module_entity_id = get_detail_by_guid($member['ModuleEntityGUID'], $member['ModuleID']);
            if (empty($module_entity_id)) {
                $status = FALSE;
                break;
            }
            $invite_members_array[$key]['PollID'] = $poll_id;
            $invite_members_array[$key]['InvitedToModuleEntityID'] = $module_entity_id;
            $invite_members_array[$key]['InvitedToModuleID'] = $member['ModuleID'];
            $invite_members_array[$key]['InvitedByModuleEntityID'] = $invited_by_module_entity_id;
            $invite_members_array[$key]['InvitedByModuleID'] = $invited_by_module_id;
            $invite_members_array[$key]['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            if ($is_reminder) {
                $invite_members_array[$key]['InviteID'] = $member['InviteID'];
                $invite_members_array[$key]['LastReminderDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            }
        }
        if ($status) {
            if ($is_reminder) {
                $this->db->update_batch(POLLINVITE, $invite_members_array, 'InviteID');
                $this->send_poll_notificaton($invite_members_array, TRUE);
            } else {
                $this->db->batch_insert(POLLINVITE, $invite_members_array);
                $this->send_poll_notificaton($invite_members_array);
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Function to send poll notification
     * @param array $members_array
     * @param bool $is_reminder
     * @return bool
     */
    public function send_poll_notificaton($members_array = array(), $is_reminder = TRUE) {
        if (!empty($members_array)) {
            
        }
    }

    /**
     * Function to get_user_for_invite
     * @param array $user_id
     * @param bool $type
     * @param bool $keyword
     * @return array
     */
    public function get_user_for_invite($user_id, $poll_id, $type, $keyword, $page_no, $page_size, $count_only = 0) {

        $posted_for = $this->get_post_for_by_poll_id($poll_id);
        $users = array();
        if ($posted_for['Users']) {
            foreach ($posted_for['Users'] as $usr) {
                if ($usr['ModuleID'] == '3') {
                    $users[] = get_detail_by_guid($usr['ModuleEntityGUID'], 3);
                }
            }
        }

        $this->db->select('U.FirstName,U.LastName,U.ProfilePicture');
        $this->db->select('"3" as ModuleID,U.UserGUID as ModuleEntityGUID', false);
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
        $this->db->from(USERS . ' U');
        $this->db->join(FRIENDS . ' F', 'U.UserID=F.FriendID', 'left');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
        $this->db->where('F.UserID', $user_id);
        $this->db->where('F.Status', '1');
        $this->db->where_not_in('U.StatusID', array(3, 4));

        if ($users) {
            $this->db->where_in('F.FriendID', $users);
        }

        if ($keyword) {
            $keyword = $this->db->escape_like_str($keyword);
            $this->db->where("(U.FirstName LIKE '%" . $keyword . "%' OR U.LastName LIKE '%" . $keyword . "%' OR CONCAT(U.FirstName,' ',U.LastName) LIKE '%" . $keyword . "%')", NULL, FALSE);
        }

        $poll_id = $this->db->escape_str($poll_id);

        if ($type == 'INVITE') {
            $this->db->where("U.UserID NOT IN(SELECT InvitedToModuleEntityID FROM " . POLLINVITE . " WHERE InvitedToModuleID='3' AND InvitedByModuleID='3' AND InvitedByModuleEntityID='" . $user_id . "' AND PollID=" . $poll_id . ")", NULL, FALSE);
        } else {
            $this->db->where("U.UserID IN(SELECT InvitedToModuleEntityID FROM " . POLLINVITE . " WHERE InvitedToModuleID='3' AND InvitedByModuleID='3' AND InvitedByModuleEntityID='" . $user_id . "' AND IsReminder='1' AND PollID=" . $poll_id . ")", NULL, FALSE);
        }

        if (!$count_only) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }

        $query = $this->db->get();

        if ($count_only) {
            return $query->num_rows();
        }

        if ($query->num_rows()) {
            return $query->result_array();
        }
    }

    /**
     * [invite_entity description]
     * @param  [int] $invited_by_module_id        [invited by module_id]
     * @param  [int] $invited_by_module_entity_id [invited by module_entity_id]
     * @param  [int] $poll_id                     [poll id]
     * @param  [array] $members                   [members array to whom invitation being sent]
     * @param  [string] $select_all               [USER/GROUP]
     */
    public function invite_entity($invited_by_module_id, $invited_by_module_entity_id, $poll_id, $members, $select_all) {

        if ($select_all == 'User') {
            $result = $this->get_user_for_invite($invited_by_module_entity_id, $poll_id, 'INVITE', '', 1, 10000000);
            $members = array();
            if ($result) {
                foreach ($result as $r) {
                    $members[] = array('ModuleID' => '3', 'ModuleEntityGUID' => $r['ModuleEntityGUID']);
                }
            }
        }
        if ($select_all == 'Group') {
            $result = $this->get_group_for_invite($invited_by_module_entity_id, $poll_id, 'INVITE', '', 1, 10000000);
            $members = array();
            if ($result) {
                foreach ($result as $r) {
                    $members[] = array('ModuleID' => '1', 'ModuleEntityGUID' => $r['ModuleEntityGUID']);
                }
            }
        }
        if ($members) {
            foreach ($members as $member) {
                $invited_to_module_entity_id = get_detail_by_guid($member['ModuleEntityGUID'], $member['ModuleID']);
                $this->db->where('InvitedByModuleID', $invited_by_module_id);
                $this->db->where('InvitedByModuleEntityID', $invited_by_module_entity_id);
                $this->db->where('InvitedToModuleID', $member['ModuleID']);
                $this->db->where('InvitedToModuleEntityID', $invited_to_module_entity_id);
                $this->db->where('PollID', $poll_id);
                $query = $this->db->get(POLLINVITE);
                if (!$query->num_rows()) {
                    $data = array('InvitedByModuleID' => $invited_by_module_id, 'InvitedByModuleEntityID' => $invited_by_module_entity_id, 'InvitedToModuleID' => $member['ModuleID'], 'InvitedToModuleEntityID' => $invited_to_module_entity_id, 'PollID' => $poll_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                    //print_r($data);
                    $this->db->insert(POLLINVITE, $data);

                    $this->send_notification(112, $poll_id, $invited_by_module_id, $invited_by_module_entity_id, $member['ModuleID'], $invited_to_module_entity_id, $poll_id);
                }
            }
        }
    }

    /**
     * Function to send_notification
     * @param array $notification_type_id
     * @param array $from_module_id
     * @param array $from_entity_id
     * @param array $to_module_id
     * @param array $to_entity_id
     * @return boolean
     */
    public function send_notification($notification_type_id, $poll_id, $from_module_id, $from_entity_id, $to_module_id, $to_entity_id, $reference_id) {
        $this->load->model('group/group_model');
        $parameters[0]['ReferenceID'] = $from_entity_id;
        if ($from_module_id == '18') {
            $parameters[0]['Type'] = 'Page';
        } else {
            $parameters[0]['Type'] = 'User';
        }

        $users = array();
        if ($to_module_id == 3) {
            $users[] = $to_entity_id;
        } elseif ($to_module_id == 1) {
            $users = $this->group_model->get_group_members_id_recursive($to_entity_id);
        }

        $this->notification_model->add_notification($notification_type_id, $this->UserID, $users, $poll_id, $parameters);
    }

    public function remind($module_id, $module_entity_id, $poll_id, $to_module_id = '', $to_module_entity_id = '') {
        $this->db->select('InvitedToModuleID,InvitedToModuleEntityID');
        $this->db->from(POLLINVITE);
        $this->db->where('InvitedByModuleID', $module_id);
        $this->db->where('InvitedByModuleEntityID', $module_entity_id);
        $this->db->where('PollID', $poll_id);
        $this->db->where('IsReminder', 1);
        if ($to_module_id && $to_module_entity_id) {
            $this->db->where('InvitedToModuleID', $to_module_id);
            $this->db->where('InvitedToModuleEntityID', $to_module_entity_id);
        } else {
            $this->db->where('InvitedToModuleID', 3);
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $result) {
                $this->send_notification(112, $poll_id, $module_id, $module_entity_id, $result['InvitedToModuleID'], $result['InvitedToModuleEntityID'], $poll_id);
            }
        }
    }

    public function get_invite_status($module_id, $module_entity_id, $poll_id) {
        $data = array('TotalInvited' => '0', 'TotalVoted' => '0');

        $this->db->select("COUNT(PI.InviteID) as TotalInvited", false);
        $this->db->from(POLLINVITE . ' PI');
        $this->db->where('PI.PollID', $poll_id);
        $this->db->where('InvitedByModuleID', $module_id);
        $this->db->where('InvitedByModuleEntityID', $module_entity_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $data['TotalInvited'] = $query->row()->TotalInvited;
        }

        $this->db->select("SUM((SELECT COUNT(POV.VoteID) FROM " . POLLOPTIONVOTES . " POV WHERE POV.ModuleID=PI.InvitedToModuleID AND POV.ModuleEntityID=PI.InvitedToModuleEntityID AND POV.PollID=PI.PollID)) as TotalVoted", false);
        $this->db->from(POLLINVITE . ' PI');
        $this->db->where('PI.PollID', $poll_id);
        $this->db->where('InvitedByModuleID', $module_id);
        $this->db->where('InvitedByModuleEntityID', $module_entity_id);
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $data['TotalVoted'] = $query->row()->TotalVoted;
            if (is_null($data['TotalVoted'])) {
                $data['TotalVoted'] = 0;
            }
        }

        return $data;
    }

    public function remind_invite($user_id, $poll_id, $module_id, $module_entity_id) {
        // /
    }

    /**
     * Function to get_group_for_invite
     * @param array $user_id
     * @param bool $type
     * @param bool $keyword
     * @return array
     */
    public function get_group_for_invite($user_id, $poll_id, $type, $keyword, $page_no, $page_size, $count_only = 0) {

        $posted_for = $this->get_post_for_by_poll_id($poll_id);
        $groups = array();
        if ($posted_for['Users']) {
            foreach ($posted_for['Users'] as $usr) {
                if ($usr['ModuleID'] == '1') {
                    $groups[] = get_detail_by_guid($usr['ModuleEntityGUID'], 1);
                }
            }
        }
        $this->db->select('G.GroupName,G.GroupImage,G.GroupID');
        $this->db->select('"1" as ModuleID,G.GroupGUID as ModuleEntityGUID', false);
        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS CreatedBy,U.UserGUID as CreatorGUID', false);
        $this->db->from(GROUPS . ' G');
        $this->db->join(GROUPMEMBERS . ' GM', 'G.GroupID=GM.GroupID', 'left');
        $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID', 'inner');
        $this->db->where('GM.ModuleID', '3');
        $this->db->where('GM.ModuleEntityID', $user_id);
        $this->db->where('GM.StatusID', '2');
        $this->db->where('G.Type', 'FORMAL');

        if ($groups) {
            $this->db->where_in('G.GroupID', $groups);
        }

        if ($keyword) {
            $keyword = $this->db->escape_like_str($keyword);
            $this->db->where("G.GroupName LIKE '%" . $keyword . "%'", NULL, FALSE);
        }

        $poll_id = $this->db->escape_str($poll_id);

        if ($type == 'INVITE') {
            $this->db->where("G.GroupID NOT IN(SELECT InvitedToModuleEntityID FROM " . POLLINVITE . " WHERE InvitedToModuleID='1' AND InvitedByModuleID='3' AND InvitedByModuleEntityID='" . $user_id . "' AND PollID=" . $poll_id . ")", NULL, FALSE);
        } else {
            $this->db->where("G.GroupID NOT IN(SELECT InvitedToModuleEntityID FROM " . POLLINVITE . " WHERE InvitedToModuleID='1' AND InvitedByModuleID='3' AND InvitedByModuleEntityID='" . $user_id . "' AND PollID=" . $poll_id . ")", NULL, FALSE);
        }

        if (!$count_only) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }

        $query = $this->db->get();

        if ($count_only) {
            return $query->num_rows();
        }

        if ($query->num_rows()) {
            $this->load->model('group/group_model');
            $data = array();
            foreach ($query->result_array() as $res) {
                $res['MemberCount'] = $this->group_model->total_member($res['GroupID']);
                unset($res['GroupID']);
                $data[] = $res;
            }
            return $data;
        }
    }

    public function get_user_details($poll_id, $user_id, $type) {
        $this->db->select('U.FirstName,U.LastName,U.ProfilePicture');
        $this->db->select('"3" as ModuleID,U.UserGUID as ModuleEntityGUID', false);
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
        if ($type == 'Invited') {
            $this->db->select("IF((SELECT VoteID FROM " . POLLOPTIONVOTES . " WHERE ModuleID=PI.InvitedToModuleID AND ModuleEntityID=PI.InvitedToModuleEntityID AND PollID=PI.PollID) is null,1,0) as IsAwaited", false);
            $this->db->join(POLLINVITE . ' PI', 'PI.InvitedToModuleEntityID=U.UserID', 'left');
            $this->db->where('PI.InvitedToModuleID', '3');
            $this->db->where('PI.InvitedByModuleID', '3');
            $this->db->where('PI.InvitedByModuleEntityID', $user_id);
        }
        if ($type == 'Voted') {
            $this->db->select("'0' as IsAwaited", false);
            $this->db->join(POLLINVITE . ' PI', 'PI.InvitedToModuleEntityID=U.UserID', 'left');
            $this->db->where("(SELECT VoteID FROM " . POLLOPTIONVOTES . " WHERE ModuleID=PI.InvitedToModuleID AND ModuleEntityID=PI.InvitedToModuleEntityID AND PollID=PI.PollID) is not null", NULL, FALSE);
            $this->db->where('PI.InvitedToModuleID', '3');
            $this->db->where('PI.InvitedByModuleID', '3');
            $this->db->where('PI.InvitedByModuleEntityID', $user_id);
        }
        if ($type == 'Awaited') {
            $this->db->select("'1' as IsAwaited", false);
            $this->db->join(POLLINVITE . ' PI', 'PI.InvitedToModuleEntityID=U.UserID', 'left');
            $this->db->where("(SELECT VoteID FROM " . POLLOPTIONVOTES . " WHERE ModuleID=PI.InvitedToModuleID AND ModuleEntityID=PI.InvitedToModuleEntityID AND PollID=PI.PollID) is null", NULL, FALSE);
            $this->db->where('PI.InvitedToModuleID', '3');
            $this->db->where('PI.InvitedByModuleID', '3');
            $this->db->where('PI.InvitedByModuleEntityID', $user_id);
        }

        $this->db->group_by('U.UserGUID');

        $query = $this->db->get();
        if ($query->num_rows()) {
            $this->load->model('group/group_model');
            $data = array();
            foreach ($query->result_array() as $res) {
                $res['MemberCount'] = $this->group_model->total_member($res['GroupID']);
                unset($res['GroupID']);
                $data[] = $res;
            }
            return $data;
        }
    }

}
