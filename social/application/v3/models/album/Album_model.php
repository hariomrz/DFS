<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of album_model
 * @version 1.0
 */
class Album_model extends Common_Model {

    private $module_id = 13;

    function __construct() {
        parent::__construct();
        $this->load->helper('location');
    }
    /**
     * Get album list
     * @param type $user_id
     * @param type $album_type
     * @param type $page_no
     * @param type $page_size
     * @param type $sort_by
     * @param type $order_by
     * @return type
     */

    public function album_list($data, $count_only=0)  {
        $page_no    = safe_array_key($data, 'PageNo', 1);
        $page_size  = safe_array_key($data, 'PageSize', PAGE_SIZE);
        $search_keyword = safe_array_key($data, 'Keyword', '');
        $is_featured = safe_array_key($data, 'IsFeatured', 0);
        $user_id = $data['UserID'];
        $sort_by = 1;
        $order_by = 'DESC';
        if (isset($data['SortBy']) && $data['SortBy'] != '') {
            $sort_by = $data['SortBy'];
        }
        if (isset($data['OrderBy']) && $data['OrderBy'] != '') {
            $order_by = $data['OrderBy'];
        }

        $visibility = array(1,2,3);
        if($this->IsApp==1) {
            $visibility = array(1);
            $this->load->model(array(                    
                'users/user_model'
            ));
            $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
            if($is_super_admin) {
                $visibility[] = 2;
            }
        }

        $this->current_db->select('A.AlbumID, A.AlbumGUID, A.AlbumName, A.Description, A.Visibility, A.MediaCount, A.CreatedDate, A.MediaLikeCount as TotalLikes, A.MediaCommentCount as TotalComments, A.IsFeatured, A.MediaID, SUM(A.MediaLikeCount+A.MediaCommentCount) as TLC');
        $this->current_db->select('IFNULL(A.Location, "") as Location', FALSE);

        if($this->IsApp!=1) {
            $this->current_db->select('IFNULL(M.ImageName, "") as CoverMedia', FALSE);
            $this->current_db->select('IFNULL(M.Resolution, "") as Resolution', FALSE);        
            $this->current_db->select('IFNULL(MS.MediaSectionAlias, "") as MediaSectionAlias', FALSE);
            $this->current_db->join(MEDIA . ' M', 'M.MediaID=A.MediaID', 'LEFT');
            $this->current_db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID = M.MediaSectionID AND MS.MediaSectionID=4', 'LEFT');
        } else {
            $this->current_db->select('A.AlbumID, A.MediaID');
        }
        $this->current_db->where('A.StatusID', 2);  
        $this->current_db->where('A.ModuleID', 3);      
        $this->current_db->where_in('A.Visibility',$visibility);
        $this->current_db->where_not_in('A.AlbumName', array(DEFAULT_PROFILE_ALBUM, DEFAULT_PROFILECOVER_ALBUM, DEFAULT_WALL_ALBUM, DEFAULT_FILE_ALBUM));
        if($is_featured == 1) {
            $this->current_db->where('A.IsFeatured', 1); 
        }
        if (!empty($search_keyword)) {
            $this->current_db->where("(A.AlbumName like '%" . $this->db->escape_like_str($search_keyword) . "%')");
        }

        if($sort_by == 1) {
            $this->current_db->order_by('A.CreatedDate', $order_by);
        } else if($sort_by == 2) {
            $this->current_db->order_by('TLC', $order_by);
            //$this->current_db->Having('TLC >', 0);            
        }
        $this->current_db->group_by('A.AlbumID');

        if (!$count_only) {      
            $this->current_db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        
        $query = $this->current_db->get(ALBUMS . ' A');
        //echo $this->current_db->last_query();die;
        if ($count_only) {
            return $query->num_rows();
        } else {
            if($this->IsApp!=1) {
                return $query->result_array();
            } else {
                $retrun_data = array();
                foreach($query->result_array() as $result) {             
                    
                    $media_data['MediaID'] = $result['MediaID'];
                    $media_data['CoverID'] = $result['MediaID'];
                    $media_data['AlbumID'] = $result['AlbumID'];   
                    $media_data['LoggedInUserID'] = $user_id;             
                    $result['CoverMedia'] = $this->get_album_media($media_data);
                    unset($result['MediaID']);
                    unset($result['AlbumID']);
                    unset($result['TLC']);
                    $retrun_data[] = $result;
                }
                return $retrun_data;
            }                                    
        }
    }
    
    public function is_valid_album($album_guid) {
        $this->db->select('AlbumGUID');
        $this->db->from(ALBUMS);
        $this->db->where('AlbumGUID',$album_guid);
        $query = $this->db->get();
        if($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    public function update_activity_for_album($album_id,$commentable,$visibility) {
        $activity_id = get_detail_by_id($album_id,13,'ActivityID',1);
        $this->db->set('Privacy',$visibility);
        $this->db->set('IsCommentable',$commentable);
        $this->db->where('ActivityID',$activity_id);
        $this->db->update(ACTIVITY);
        $this->db->last_query();
    }

    /**
     * Save album 
     * Add new album if guid not available else update album
     * @param type $album
     * @return type
     */
    public function save_album($album) {
        $album_id = FALSE;
        if (!empty($album['AlbumGUID'])) {
            $this->db->where('AlbumGUID', $album['AlbumGUID']);
            $this->db->update(ALBUMS, $album);
            //$album_id = TRUE;
            $album_id = get_detail_by_guid($album['AlbumGUID'], 13, "", 1);
            $album['AlbumID'] = $album_id;
            $album_id = $album;
        } else {
            $album['AlbumGUID'] = get_guid();
            $this->db->insert(ALBUMS, $album);
            $album_id = $this->db->insert_id();
            if ($album_id) {
                $album['AlbumID'] = $album_id;
                $album_id = $album;
            }
        }
        return $album_id;
    }

    /**
     * set media status 
     * Set statusid of the media as per album id passed
     * @param int $album_id
     * @param int $status_id
     * @return null
     */
    public function set_media_status_by_album_id($album_id='',$status_id=''){
        $this->db->where('AlbumID',$album_id);
        $this->db->set('StatusID',$status_id);
        $this->db->update(MEDIA);
    }
    
    /**
     * set media status 
     * Set statusid of the media as per album id passed
     * @param int $album_id
     * @param array $deleted_media
     * @param int $status_id
     * @return null
     */
    public function set_media_status_by_media_guid($album_id='',$deleted_media=array(),$status_id=''){
        $this->db->where('AlbumID',$album_id);
        $this->db->where_in('MediaGUID',$deleted_media);
        $this->db->set('StatusID',$status_id);
        $this->db->update(MEDIA);
    }
    
    /**
     * Update album activity id
     * @param guid $AlbumGUID
     * @param integer $activity_id
     * @return boolean
     */
    public function update_album_activity_id($AlbumGUID,$activity_id)
    {
        $this->db->set('ActivityID',$activity_id);
        $this->db->where('AlbumGUID',$AlbumGUID);
        $this->db->update(ALBUMS);
    }

    /**
     * Check that album name exist for user
     * @param Int $user_guid
     * @param String $album_name
     * @return mix
     */
    public function album_name_exist($album_name) {
        $this->db->where('AlbumName', $album_name);
        $this->db->where('StatusID !=', 3);
        $this->db->from(ALBUMS);
        $query = $this->db->get();
        $count = $query->num_rows();

        //return $count == 0;
        if($count > 0){
            return FALSE;
        }else{
            return TRUE;
        }
    }
    
    /**
     * get album by guid
     * @param type $album_guid
     * @return array album
     */    
    public function get_album_by_guid($album_guid, $logged_user_id='') {
        $this->current_db->select('A.AlbumGUID, A.MediaID, A.AlbumID, A.UserID, U.UserGUID, A.AlbumName, A.Description, A.Visibility, A.AlbumType, A.MediaCount, A.CreatedDate, A.ModifiedDate, A.ActivityID, A.UserID, A.Location');
        if(!empty($logged_user_id)) {
            $this->current_db->select("(CASE WHEN A.UserID='".$logged_user_id."' THEN A.IsEditable ELSE 0 END) AS IsEditable",false);
        } else {
            $this->current_db->select("A.IsEditable",false);
        }
        $this->current_db->select('IFNULL(M.Resolution, "") as Resolution', FALSE);
        $this->current_db->select('IFNULL(M.ImageName, "") as CoverMedia', FALSE);
        $this->current_db->select('IFNULL(MS.MediaSectionAlias, "") as MediaSectionAlias', FALSE);
        $this->current_db->join(MEDIA . ' M', 'M.MediaId=A.MediaID', 'LEFT');
        $this->current_db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID = M.MediaSectionID', 'LEFT');
        $this->current_db->join(USERS . ' U', 'U.UserID = A.UserID', 'LEFT');
        $this->current_db->limit(1);
        $this->current_db->where('A.AlbumGUID', $album_guid);
        $query = $this->current_db->get(ALBUMS . ' A');
        if($query->num_rows()){
            return $query->row_array();
        }
        else
        {
            return false;
        }
    }

    /**
     * Get album media list
     * @param int $album_id
     * @return array
     */
    public function get_album_media($data, $count_only=0) {
        
        $page_no    = safe_array_key($data, 'PageNo', 1);
        $page_size  = safe_array_key($data, 'PageSize', PAGE_SIZE);
        $media_guid = safe_array_key($data, 'MediaGUID', '');
        $media_id = safe_array_key($data, 'MediaID', 0);
        $cover_id   = safe_array_key($data, 'CoverID', 0);
        $is_verified   = safe_array_key($data, 'Verified', 2);
        $filter_user_id   = safe_array_key($data, 'UserID', array());

        $start_date = (isset($data['StartDate']) && !empty($data['StartDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['StartDate'])) : '';
        $end_date = (isset($data['EndDate']) && !empty($data['EndDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['EndDate'])) : '';
        
        
        $user_id = $data['LoggedInUserID'];
        $album_id = $data['AlbumID'];
        $sort_by = 1;
        $order_by = 'DESC';
        if (isset($data['SortBy']) && $data['SortBy'] != '') {
            $sort_by = $data['SortBy'];
        }
        if (isset($data['OrderBy']) && $data['OrderBy'] != '') {
            $order_by = $data['OrderBy'];
        }

        if($this->IsApp!=1) {
            $this->current_db->select('M.Verified');
        }

        $this->current_db->select('M.MediaGUID, M.Location, M.MediaID, IFNULL(M.ImageName,"") ImageName, IFNULL(M.NoOfComments, 0) NoOfComments, IFNULL(M.NoOfLikes,0) NoOfLikes, IFNULL(M.CreatedDate,"") CreatedDate, MT.Name as MediaType, , SUM(M.NoOfComments+M.NoOfLikes) as TLC', FALSE);
        $this->current_db->select('U.FirstName, U.LastName, U.UserID, U.UserGUID, U.IsVIP, U.IsAssociation');
        $this->current_db->select('IFNULL(U.ProfilePicture, "") as ProfilePicture', FALSE);
        $this->current_db->select('IFNULL(M.ConversionStatus,"") AS ConversionStatus, IFNULL(M.VideoLength,"") AS VideoLength', FALSE);
        $this->current_db->select('IFNULL(M.Description, "") as Description', FALSE);
        $this->current_db->select('IFNULL(M.Resolution, "") as Resolution', FALSE);
        $this->current_db->select('IF(M.MediaID=' . $cover_id . ', 1,0) IsCoverMedia ', FALSE);
        $this->current_db->select('IFNULL(MS.MediaSectionAlias, "") as MediaSectionAlias', FALSE);
        $this->current_db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->current_db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->current_db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID = M.MediaSectionID', 'LEFT');
        $this->current_db->join(USERS . ' U', 'U.UserID=M.UserID');

        if($media_id) {
            $this->current_db->where('M.MediaID', $media_id);
        } else if($media_guid) {
            $this->current_db->where('M.MediaGUID', $media_guid);
        }

        if ($filter_user_id) {
            if(is_array($filter_user_id)) {
                $this->current_db->where_in('M.UserID', $filter_user_id);
            } else {
                $this->current_db->where('M.UserID', $filter_user_id);
            }
        }

        if($is_verified != 2) {
            $this->current_db->where('M.Verified', $is_verified);
        }

        $this->current_db->where('M.AlbumID', $album_id);
        $this->current_db->where('M.StatusID', 2);

        $time_zone = 'Asia/Calcutta';
        if ($start_date) {
            $start_date = date('Y-m-d', strtotime($start_date));    
            $this->db->where("DATE_FORMAT(CONVERT_TZ(M.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }

        if ($end_date) {
            $end_date = date('Y-m-d', strtotime($end_date)); 
            $this->db->where("DATE_FORMAT(CONVERT_TZ(M.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        if($sort_by == 1) {
            $this->current_db->order_by('M.CreatedDate', $order_by);
        } else if($sort_by == 2) {
            $this->current_db->order_by('TLC', $order_by);
            //$this->current_db->Having('TLC >', 0);            
        }
        $this->current_db->group_by('M.MediaID');

        if (!$count_only) {      
            $this->current_db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        
        $query = $this->current_db->get(MEDIA . ' M');
        //echo $this->current_db->last_query();die;
        if ($count_only) {
            return $query->num_rows();
        } else {            
            $retrun_data = array();
            $this->load->model(array(                    
                'users/user_model'
            ));
            foreach($query->result_array() as $result) {              
                
                $result['MediaType'] = strtoupper($result['MediaType']);
                
                $result['Locality']               = (object) [];
                $entity = $this->user_model->get_user_details($result['UserID']);
                if ($entity) {
                    $result['Locality']   = $entity['Locality'];
                }

                if($result['NoOfComments'] > 0) {
                    $result['NoOfComments'] = $result['NoOfComments'] + $this->activity_model->get_entity_comment_reply_count($result['MediaID'], 'MEDIA');
                }

                if($this->IsApp==1) {
                    $result['IsLike'] = $this->activity_model->is_liked($result['MediaID'], 'Media', $user_id, 3, $user_id);
                    unset($result['UserID']);
                } else {
                    $result['IsNotificationSent'] = $this->is_notification_sent($result['MediaID']);
                }
                unset($result['MediaID']);
                
                unset($result['TLC']);
                $retrun_data[] = $result;

            }
            return $retrun_data;    
        }
    }
    
    /**
     * common function used to get single row from any table
     * @param String $select
     * @param String $table
     * @param Array/String $where
     */
    function get_row($select = '*', $table, $where = "") {
        $this->current_db->select($select);
        $this->current_db->from($table);
        if ($where != "") {
            $this->current_db->where($where, NULL, FALSE);
        }
        $query = $this->current_db->get();
        return $query->row_array();
    }

    /**
     * Delete album
     * @param type $AlbumGUID
     * @return boolean
     */
    public function delete_album($album_id) {
        
        //$activity_id=get_detail_by_id($album_id, 13, "ActivityID", 1);
        $this->db->set('StatusID', 3);
        $this->db->where('AlbumID', $album_id);
        $this->db->update(ALBUMS);

        //mark media deleted
        $this->db->set('StatusID', 3);
        $this->db->where('AlbumID', $album_id);
        $this->db->update(MEDIA);

        $point_data = array('EntityID' => 0, 'EntityType' => 4, 'AlbumID' => $album_id, 'ActivityTypeID' => 48, 'ParentID' => -1);          
        initiate_worker_job('revert_point', $point_data,'','point');

        return TRUE;
    }

    /**
     * update single media
     * @param type $data
     * @param type $media_id
     * @return boolean
     */
    public function update_media($data, $media_id) {
        $this->db->where('MediaID', $media_id);
        $this->db->update(MEDIA, $data);
        return TRUE;
    }
    
    /**
     * get last media of album
     * @param type $album_id
     */
    public function get_album_last_media($album_id) {
        $this->db->select('M.MediaGUID, M.MediaID');
        $this->db->where('AlbumID', $album_id);
        $this->db->where('StatusID', 2);
        $this->db->order_by('CreatedDate', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get(MEDIA . ' M');
        return $query->row_array();
    }

    /**
     * get Media by guid
     * @param type $media_guid
     * @return array Media
     */
    public function get_media_by_guid($media_guid) {
        $this->db->select('M.MediaGUID, IFNULL(M.ImageName,"") ImageName, IFNULL(M.UserID,"0") UserID, IFNULL(M.NoOfComments, 0) NoOfComments, IFNULL(M.NoOfLikes,0) NoOfLikes, IFNULL(M.CreatedDate,"") CreatedDate, MT.Name as MediaType', FALSE);
        $this->db->select('IFNULL(M.ConversionStatus,"") AS ConversionStatus, IFNULL(M.VideoLength,"") AS VideoLength', FALSE);
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');

        $this->db->limit(1);
        $this->db->where('MediaGUID', $media_guid);

        $query = $this->db->get(MEDIA . ' M');
        return $query->row_array();
    }
    
    /**
     * for update album media count
     * @param type $album_id
     */
    public function update_album_media_count($album_id, $count=0, $update_type = 'add', $media_like_count=0, $media_comment_count=0) {
        
        $this->db->where('AlbumID', $album_id);
        if($update_type == "add"){
            $this->db->set("MediaCount", "MediaCount+($count)", FALSE);
            $this->db->set("MediaLikeCount", "MediaLikeCount+($media_like_count)", FALSE);
            $this->db->set("MediaCommentCount", "MediaCommentCount+($media_comment_count)", FALSE);
        } else if($update_type == 'reduce'){
            $this->db->set("MediaCount", "MediaCount-($count)", FALSE);
            $this->db->set("MediaLikeCount", "MediaLikeCount-($media_like_count)", FALSE);
            $this->db->set("MediaCommentCount", "MediaCommentCount-($media_comment_count)", FALSE);
        } else if($update_type == 'update'){
            $this->db->set("MediaCount", $count, FALSE);
        }
        $this->db->update(ALBUMS);
        return true;
    }
    
    /**
     * Add album media
     * @param array $data
     * @param integer $album_details
     * @return boolean
     */
    public function add_album_media($data, $album_details, $user_id) {
        $result = 0;
        if (empty($data) === FALSE) {
            $media = array();
            $media_guid = array();
            $album_id = $album_details['AlbumID'];
            //$album_details      = get_detail_by_id($album_id,13,'LocationID,ActivityID',2);
            $album_location = $album_details['Location'];
            $IsCommentable      = 1;//get_detail_by_id($album_details['ActivityID'],0,'IsCommentable',1);
            foreach ($data as $temp) {
                //store media album_location
                $location = $album_location;
                if(!empty($temp['Location'])) {
                    $location 	 = $temp['Location'];
                }

                if(isset($temp['IsCommentable'])) {
                    $IsCommentable = $temp['IsCommentable'];
                }

                $media_array = array(
                    'AlbumID' => $album_id,
                    'MediaGUID' => $temp['MediaGUID'],
                    'Caption' => isset($temp['Caption'])?$temp['Caption']:"",
                    'SportsID' => isset($temp['SportsID']) ? $temp['SportsID'] : 0,
                    'ModuleEntityID' => $album_id,
                    'MediaSectionReferenceID' => $album_id,
                    'ModuleID' => 13,
                    'StatusID' => 2,
                    'Description' => isset($temp['Description'])?$temp['Description']:"",
                    'Location' => $location,
                    'IsCommentable' => $IsCommentable,
                );
                
                $media_guid[] = $media_array['MediaGUID'];
                $media[] = $media_array;                
            }
            
            $point_data = array('MediaGUIDs' => $media_guid, 'UserID' => $user_id, 'AlbumID' => $album_id);
            initiate_worker_job('add_media_point', $point_data,'','point');

            $this->db->update_batch(MEDIA, $media, 'MediaGUID');                        
            $result = $this->db->affected_rows();
            
        }
        return $result;
    }
    
    /**
     * Update album visibility
     * @param integer $album_id
     * @param integer $visibility
     * @return boolean
     */
    public function set_privacy($album_id, $visibility){
        $this->db->set('Visibility', $visibility);
        $this->db->where('AlbumID', $album_id);
        $this->db->update(ALBUMS);
    }

    function is_notification_sent($media_id) {
        $this->db->select('IsActivityDashboard');
        $this->db->from(ADMINCOMMUNICATION);
        $this->db->where('MediaID', $media_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row_array();
            return (int)$row['IsActivityDashboard'];
        }        
        return 0;
    }

    public function send_notification($media_id) {

        if(empty($this->is_notification_sent($media_id))) {
            $this->db->select('A.AlbumName, A.AlbumGUID, M.MediaGUID, IFNULL(M.ImageName,"") ImageName', FALSE);
            $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as SenderName");
            $this->db->select('U.UserID');
            $this->db->join(ALBUMS . ' A', 'A.AlbumID = M.AlbumID');
            $this->db->join(USERS . ' U', 'U.UserID=M.UserID');
            $this->db->where('M.MediaID', $media_id);       
            $this->db->where('M.StatusID', 2);
            $this->db->limit(1);
            $query = $this->db->get(MEDIA . ' M');
            $media_data = $query->row_array();
            if (!empty($media_data)) {
                $notification_data = array('MediaGUID' => $media_data['MediaGUID'], 'MediaID' => $media_id, 'UserID' => $media_data['UserID'], 'SenderName' => $media_data['SenderName'], 'NotificationTypeKey' => 'media_message', 'Mentions' => array(), 'AlbumGUID' => $media_data['AlbumGUID'], 'AlbumName' => $media_data['AlbumName'], 'ImageName' => $media_data['ImageName']);
                //print_r($notification_data);
                initiate_worker_job('media_notification', $notification_data, '', 'notification');
            }
        }
    }


    public function media_notification($data) {
        $sender_id = !empty($data['UserID']) ? $data['UserID'] : 0;
        $album_guid = !empty($data['AlbumGUID']) ? $data['AlbumGUID'] : '';
        $album_name = !empty($data['AlbumName']) ? $data['AlbumName'] : '';
        $sender_name = !empty($data['SenderName']) ? $data['SenderName'] : '';
        $media_id = !empty($data['MediaID']) ? $data['MediaID'] : 0;
        $media_guid = !empty($data['MediaGUID']) ? $data['MediaGUID'] : '';
        $mentions = !empty($data['Mentions']) ? $data['Mentions'] : array();
        $mentions[] = $sender_id;
        if($this->is_notification_sent($media_id)) {
            return true;
        }
        if ($sender_id && $sender_name && $album_guid) {
            $notification_type_key = $data['NotificationTypeKey'];
            $notification_type_id = 0;
            $subject = $sender_name . " added a new media in album ".$album_name;
            
            $notification_data = array(
                                    "NotificationTypeKey" => $notification_type_key, 
                                    "UserID" => $sender_id, 
                                    "FromUserDetails" => "", 
                                    "NotificationTypeID" => $notification_type_id, 
                                    "RefrenceID" => $media_id, 
                                    "Params" => "",
                                    "AlbumGUID" => $album_guid,
                                    "MediaGUID" => $media_guid, 
                                );

            $notification_data['Summary'] = '';
            
            $this->db->select("AL.UserID");
            $this->db->from(ACTIVELOGINS . ' AL');
            $this->db->join(USERS . ' U', 'U.UserID=AL.UserID AND U.StatusID NOT IN (3,4)');
            
            if(ENVIRONMENT!="production") {
                $this->db->where_in('AL.UserID', array(1441, 10662, 145, 10404, 10409));
            }

            $this->db->where('AL.IsValidToken', 1);
            $this->db->where('AL.DeviceToken!=', '');
            $this->db->group_by('AL.DeviceToken');
            $this->db->group_by('AL.DeviceTypeID');
            $this->db->order_by('AL.ActiveLoginID', 'DESC');
            $query = $this->db->get();
            $total_notification = $query->num_rows();
            if ($total_notification > 0) {

                $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');  
                $this->load->model('admin/communication/communication_model');
                $communication_history_data = array();
                
                $communication_data = array(
                    'Type' => 1,
                    'IsActivityDashboard' => 1,
                    'MediaID' => $media_id,
                    'CreatedDate' => $current_date_time,
                    'ModifiedDate' => $current_date_time
                ); 
                $communication_id = $this->communication_model->add_communication($communication_data, $total_notification);
                
                foreach ($query->result_array() as $userdata) {
                    if($userdata['UserID'] == $sender_id) {
                        continue;
                    }
                    $notification_data['ToUserID'] = $userdata['UserID'];
                    $notification_data['ToUserDetails'] = "";
                    initiate_worker_job('SendPushMsg', array('ToUserID' => $notification_data['ToUserID'], 'Subject' => $subject, 'notifications' => $notification_data), '', 'media_notification');

                }
            }
        }
    }


    /**
     * [updateAlbumDate Update Album Activity Type and Date]
     * @param  [int]       $activity_id      [Activity ID]
     * @param  [array]     $Params          [Parameters]
     */
    public function updateAlbumDate($activity_id,$Params){
        $this->db->set('ActivityTypeID','6');
        $this->db->set('LastActionDate',getCurrentDate('%Y-%m-%d %H:%i:%s'));
        $this->db->set('ModifiedDate',getCurrentDate('%Y-%m-%d %H:%i:%s'));
        $this->db->set('Params',json_encode($Params));
        $this->db->where('ActivityID',$activity_id);
        $this->db->update(ACTIVITY);
    }

    /**
     * [get_album_activity_id Get activity id of album from album guid]
     * @param  [string]       $AlbumGUID      [Album ID]
     */
    public function get_album_activity_id($album_guid,$is_id=false)
    {
        $this->db->select('ActivityID');
        $this->db->from(ALBUMS);
        if($is_id){
            $this->db->where('AlbumID',$album_guid);
        }
        else
        {
            $this->db->where('AlbumGUID',$album_guid);
        }
        $query = $this->db->get();
        if($query->num_rows())
        {
            return $query->row()->ActivityID;
        }
        else
        {
            return 0;
        }
    }

    public function get_album_activity_guid($album_guid,$is_id=false)
    {
        $this->db->select('ActivityID');
        $this->db->from(ALBUMS);
        if($is_id){
            $this->db->where('AlbumID',$album_guid);
        }
        else
        {
            $this->db->where('AlbumGUID',$album_guid);
        }
        $query = $this->db->get();
        if($query->num_rows())
        {
            return get_detail_by_id($query->row()->ActivityID,0,'ActivityGUID',1);
        }
        else
        {
            return 0;
        }
    }
    
    /**
     * Add album youtube media
     * @param array $medias
     * @param integer $album_id
     * @param integer $module_id
     * @return boolean
     */
    public function add_album_youtube_media($data, $album_id, $module_id) {
        $result = 0;
        if (!empty($data)) {
            $totalSize = 0;
            foreach ($data as $temp) {
                $created_date = gmdate('Y-m-d H:i:s');
                $media_guid = get_guid();
                $media = array(
                    'UserID' => $this->UserID,
                    'ImageUrl' => isset($temp['Url'])?$temp['Url']:"",
                    'ImageName' => isset($temp['Url'])?$temp['Url']:"",
                    'AlbumID' => $album_id,
                    'MediaGUID' => $media_guid,
                    'Caption' => isset($temp['Caption'])?$temp['Caption']:"",
                    'SportsID' => isset($temp['SportsID'])?$temp['SportsID']:0,
                    'ModuleEntityID' => $album_id,
                    'StatusID' => 2,
                    'Description' => isset($temp['Description'])?$temp['Description']:"",
                    'MediaSectionID' => 4,
                    'DeviceID' => isset($temp['DeviceID']) ? $temp['DeviceID'] : 1,
                    'SourceID' => isset($temp['SourceID']) ? $temp['SourceID'] : 1,
                    'MediaExtensionID' => 9,
                    'MediaSectionReferenceID' => $album_id,
                    'ModuleID' => $module_id,
                    'CreatedDate' => $created_date,
                    'SourceID'          => $this->SourceID,
                    'Size'              => $totalSize, //The file size in kilobytes
                    'MediaSizeID'       => 0,
                    'IsAdminApproved'   => 1,
                    'VideoLength'       => isset($temp['VideoLength'])?$temp['VideoLength']:0,
                    'ConversionStatus'  => 'Finished'
                        //'ModifiedDate' => gmdate('Y-m-d H:i:s'),
                );

                if(!empty($temp['IsCommentable'])){
                    $media['IsCommentable'] = $temp['IsCommentable'];
                }

                $this->db->insert(MEDIA, $media);
                $media_id = $this->db->insert_id();                
                //save keyword
                if (isset($temp['Keyword']) && empty($temp['Keyword']) === FALSE) {
                    $this->save_keywords($temp['Keyword'], $media_id);
                }
                $result = $media_id;
            }
        }
        return $result;
    }

    /**
     * save keywords
     * @param type $keyword
     * @param type $media_id
     * @return boolean
     */
    public function save_keywords($keyword, $media_id) {
        $words = explode(',', $keyword);
        if (!empty($words)) {
            $keywords = array();
            foreach ($words as $word) {
                $keywords[] = array(
                    'MediaID' => $media_id,
                    'Keyword' => $word,
                );
            }
            $this->db->insert_batch(MEDIA_KEYWORD, $keywords);
            return TRUE;
        }
        return FALSE;
    }
    
    function set_album_visibility($album_id,$visibility){
        $this->db->set('Visibility',$visibility);
        $this->db->where('AlbumID',$album_id);
        $this->db->update(ALBUMS);
    }
    
    function remove_album_cover($album_id){
        $this->db->set('MediaID',0);
        $this->db->where('AlbumID',$album_id);
        $this->db->update(ALBUMS);
    }
    
    function is_cover_pic($album_id,$media_id){
        $this->db->select('MediaID');
        $this->db->from(ALBUMS);
        $this->db->where('AlbumID',$album_id);
        $this->db->where('MediaID',$media_id);
        $res = $this->db->get();
        if($res->num_rows() > 0){
            return TRUE;
        }
        return FALSE;
    }
    
    function set_activity_visibility($activity_id,$visibility){
        $this->db->set('Privacy',$visibility);
        $this->db->where('ActivityID',$activity_id);
        $this->db->update(ACTIVITY);
    }
    
    function check_cover_exist($album_guid){
        $this->db->select('MediaID');
        $this->db->from(ALBUMS);
        $this->db->where('AlbumGUID',$album_guid);
        $res = $this->db->get();
        if($res->num_rows() > 0){
            $row = $res->row();
            if(!empty($row->MediaID)){
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * save featured album
     * @param int $album_id
     */
    function save_featured_album($album_id){
        $this->db->where('AlbumID',$album_id);
        $this->db->set('IsFeatured',1);
        $this->db->update(ALBUMS);
    }

    /**
     * remove featured album
     * @param int $album_id
     */
    function remove_featured_album($album_id){
        $this->db->where('AlbumID',$album_id);
        $this->db->set('IsFeatured',0);
        $this->db->update(ALBUMS);
    }

    /**
     * save featured album
     * @param int $album_id
     */
    function show_on_newsfeed($album_id){
        $this->db->where('AlbumID',$album_id);
        $this->db->set('ShowOnNewsfeed',1);
        $this->db->update(ALBUMS);
    }

    /**
     * remove featured album
     * @param int $album_id
     */
    function remove_from_newsfeed($album_id){
        $this->db->where('AlbumID',$album_id);
        $this->db->set('ShowOnNewsfeed',0);
        $this->db->update(ALBUMS);
    }

}
