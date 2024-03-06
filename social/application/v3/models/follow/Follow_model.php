<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Follow_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * [is_follow This is used to check the status of follow]
     * @param  [int] $user_id   [User Id]
     * @param  [int] $follow_id [following user Id]
     * @return [int]           [return follow Status]
     */
    function is_follow($user_id, $follow_id) {
        if($user_id == $follow_id) {
            return 2; //self 
        }
        $this->db->select('FollowID');
        $this->db->from(FOLLOW);
        $this->db->where('FollowingID', $follow_id);
        $this->db->where('UserID', $user_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return 1; // Following            
        } else {
            return 0; // Not following
        }
    }

    /**
     * follow user
     * @param array $data
     */
    public function follow($data){
        // insert entry in follow table
        $insert_data = array(
                "UserID" => $data['UserID'],
                "FollowingID" => $data['FollowingID'],
                "CreatedDate" => get_current_date('%Y-%m-%d %H:%i:%s')
            );
        $this->db->insert(FOLLOW, $insert_data);

        // update following count 
        $this->db->set('TotalFollowing', 'TotalFollowing+1', FALSE);
        $this->db->where('UserID',$data['UserID']);
        $this->db->update(USERDETAILS);

        //update followers of friend
        $this->db->set('TotalFollowers', 'TotalFollowers+1', FALSE);
        $this->db->where('UserID', $data['FollowingID']);
        $this->db->update(USERDETAILS);

        $parameters[0]['ReferenceID'] = $data['UserID'];
        $parameters[0]['Type'] = 'User';
        //follow push notification will be skipped for new user join to admin
        if(!isset($data['new_signup']) || $data['new_signup']!=1)
        {
            initiate_worker_job('add_notification', array('NotificationTypeID' => 5, 'SenderID' => $data['UserID'], 'ReceiverIDs' => array($data['FollowingID']), 'RefrenceID' => $data['FollowingID'], 'Parameters' => $parameters, 'ExtraParams' => array()), '', 'notification');
        }
                        
        if (CACHE_ENABLE) {
            $this->cache->delete('user_followers_' . $data['UserID']);
        }
    }

    /**
     * Unfollow user and delete entries
     * @param array $data
     */
    public function unfollow($data){
        // delete entry from follow table
        $this->db->where("UserID",$data['UserID']);
        $this->db->where("FollowingID",$data['FollowingID']);
        $this->db->delete(FOLLOW);

        // update following count 
        $this->db->set('TotalFollowing', 'TotalFollowing-1', FALSE);
        $this->db->where('UserID',$data['UserID']);
        $this->db->update(USERDETAILS);

        //update followers of friend
        $this->db->set('TotalFollowers', 'TotalFollowers-1', FALSE);
        $this->db->where('UserID', $data['FollowingID']);
        $this->db->update(USERDETAILS);


        $this->db->where('NotificationTypeID', 5);
        $this->db->where('ToUserID', $data['FollowingID']);
        $this->db->where('UserID', $data['UserID']);
        $this->db->delete(NOTIFICATIONS);

        if (CACHE_ENABLE) {
            $this->cache->delete('user_followers_' . $data['UserID']);
        }
    }

    /**
     * following used to get following user list
     * @param array $data
     * @return array
     */
    public function following($data) {
        $count_only = safe_array_key($data, 'CountOnly', 0);
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 10);
        $order_by = safe_array_key($data, 'OrderBy', 'Name');
        $sort_by = safe_array_key($data, 'SortBy', 'ASC');
        $search_keyword = safe_array_key($data, 'Keyword', '');
        $looged_in_user_id = safe_array_key($data, 'LoogedInUserID', 0);
        $user_id = $data['UserID'];

        $follow = $this->user_model->get_followers_list();

        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID, U.UserID, UD.LocalityID");
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
        $this->db->from(FOLLOW . ' F');  
        $this->db->join(USERS . ' U', 'U.UserID = F.FollowingID AND U.StatusID NOT IN (3,4)');         
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->where('F.UserID', $user_id, FALSE);

        if (!empty($search_keyword)) {
            $this->db->where("(U.FirstName like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_keyword) . "%' or concat(U.FirstName,' ',U.LastName) like '%" . $this->db->escape_like_str($search_keyword) . "%')");
        }

        if (!$count_only) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));

            if($order_by == 'Name') {
                $this->db->order_by('U.FirstName', $sort_by);
                $this->db->order_by('U.LastName', $sort_by);  
            } else {
                $this->db->order_by('U.LastLoginDate', $sort_by);
            }
        }       
        
        $query = $this->db->get();   
        if ($count_only) {
            return $query->num_rows();
        }  
        $users = array();  
        if($query->num_rows()) {
            $result = $query->result_array();
            $this->load->model(array('locality/locality_model'));
            foreach ($result as $user){
                $user['Locality'] = array('Name' => '', 'HindiName'=>'', 'ShortName'=>'',  'LocalityID' => 0, 'IsPollAllowed' => 1, 'WName'=>'', 'WNumber'=>'', 'WID'=>'', 'WDescription'=>'');
                if($user['LocalityID']) {
                    $user['Locality'] = $this->locality_model->get_locality($user['LocalityID']);
                } 
                
                $user['IsFollow'] = 0;
                if($user['UserID'] == $looged_in_user_id) {
                    $user['IsFollow'] = 2;
                } else if (in_array($user['UserID'], $follow)) {
                    $user['IsFollow'] = 1;
                }

                $IsAdmin = $this->user_model->is_super_admin($user['UserID']);
                $this->load->model('activity/activity_model');
                $IsAdminGuid = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID)); // admin set from config page
                if($IsAdmin || $user['UserID']==$IsAdminGuid )
                {
                    $user['IsFollow']=2;
                }

                unset($user['UserID']);
                unset($user['LocalityID']);
                $users[] = $user;
            }
        }
        return $users;
    }

    /**
     * followers used to get followers user list
     * @param array $data
     * @return array
     */
    public function followers($data) {
        $count_only = safe_array_key($data, 'CountOnly', 0);
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 10);
        $order_by = safe_array_key($data, 'OrderBy', 'Name');
        $sort_by = safe_array_key($data, 'SortBy', 'ASC');
        $search_keyword = safe_array_key($data, 'Keyword', '');
        $looged_in_user_id = safe_array_key($data, 'LoogedInUserID', 0);
        $user_id = $data['UserID'];

        $follow = $this->user_model->get_followers_list();

        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID, U.UserID, UD.LocalityID");
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
        $this->db->from(FOLLOW . ' F');  
        $this->db->join(USERS . ' U', 'U.UserID = F.UserID AND U.StatusID NOT IN (3,4)');         
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->where('F.FollowingID', $user_id, FALSE);

        if (!empty($search_keyword)) {
            $this->db->where("(U.FirstName like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_keyword) . "%' or concat(U.FirstName,' ',U.LastName) like '%" . $this->db->escape_like_str($search_keyword) . "%')");
        }

        if (!$count_only) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));

            if($order_by == 'Name') {
                $this->db->order_by('U.FirstName', $sort_by);
                $this->db->order_by('U.LastName', $sort_by);  
            } else {
                $this->db->order_by('U.LastLoginDate', $sort_by);
            }
        }

        
        $query = $this->db->get();   
        if ($count_only) {
            return $query->num_rows();
        }  
        $users = array();      
        if($query->num_rows()) {
            $result = $query->result_array();
            $this->load->model(array('locality/locality_model'));
            foreach ($result as $user){

                $user['Locality'] = array('Name' => '', 'HindiName'=>'', 'ShortName'=>'',  'LocalityID' => 0, 'IsPollAllowed' => 1, 'WName'=>'', 'WNumber'=>'', 'WID'=>'', 'WDescription'=>'');
                if($user['LocalityID']) {
                    $user['Locality'] = $this->locality_model->get_locality($user['LocalityID']);
                }                
                
                $user['IsFollow'] = 0;
                if($user['UserID'] == $looged_in_user_id) {
                    $user['IsFollow'] = 2;
                } else if (in_array($user['UserID'], $follow)) {
                    $user['IsFollow'] = 1;
                } 
                
                $IsAdmin = $this->user_model->is_super_admin($user['UserID']);
                $this->load->model('activity/activity_model');
                $IsAdminGuid = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID)); // admin set from config page
                if($IsAdmin || $user['UserID']==$IsAdminGuid )
                {
                    $user['IsFollow']=2;
                }
                
                unset($user['UserID']);                
                unset($user['LocalityID']);
                $users[] = $user;
            }
        }
        return $users;
    }

    /**
     * suggestion used to get user suggestion list for follow
     * @param array $data
     * @return array
     */
    function suggestion($data) {
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 10);
        $type = safe_array_key($data, 'Type', 0);
        $user_id = $data['UserID'];

        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $follow = safe_array_key($friend_followers_list, 'Follow', array());
        $follow[] = $user_id;

        $top_contributors = $this->user_model->get_top_contributors(); 
        $top_contributors[] = 0;

        $include_user_ids = array(0);
        $top_contributors = array_merge($top_contributors, $include_user_ids);
        $top_contributors = array_diff($top_contributors, $follow);
        $top_contributors[] = 0;
        $top_contributors = array_unique($top_contributors);

        $result = array();
        $this->db->select('U.UserID');
        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID, UD.LocalityID");
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
        $this->db->from(USERS.' U');   
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');        
        $this->db->where_not_in('U.StatusID ', array(3,4));
        if($type == 1) {
            $this->db->where('U.IsVIP', 1);
            $this->db->where_not_in('U.UserID', $follow);
        } else if($type == 2) {
            $this->db->where('U.IsAssociation', 1);
            $this->db->where_not_in('U.UserID', $follow);
        } else {
            $this->db->where_in('U.UserID', $top_contributors);
            $this->db->where("U.UserID != $user_id");
        } 
        

       // $this->db->order_by('U.FirstName', 'ASC');
      //  $this->db->order_by('U.LastName', 'ASC');  

        $this->db->order_by('RAND()');
        if ($page_size) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $query = $this->db->get();
        $results = $query->result_array();
        $this->load->model(array('locality/locality_model'));
        foreach ($results as $key => $result) {
            
            $result['Locality'] = array('Name' => '', 'HindiName'=>'', 'ShortName'=>'',  'LocalityID' => 0, 'IsPollAllowed' => 1, 'WName'=>'', 'WNumber'=>'', 'WID'=>'', 'WDescription'=>'');
            if($result['LocalityID']) {
                $result['Locality'] = $this->locality_model->get_locality($result['LocalityID']);
            }
            unset($result['LocalityID']);
            unset($result['UserID']);
            $results[$key] = $result;
        }
        return $results;
    }
}
