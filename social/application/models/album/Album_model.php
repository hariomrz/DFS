<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of album_model
 * @copyright (c) 2015, Vinfotech
 * @author nitins <nitins@vinfotech.com>
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

    public function album_list($logged_user_id, $module_id, $module_entity_id, $album_type = 'PHOTO', $page_no = 0, $page_size = PAGE_SIZE, $sort_by = 'CreatedDate', $order_by = 'DESC',$show_private=TRUE, $total_rows=FALSE) 
    {
        $this->load->model('activity/activity_model');

        $permissions = TRUE;
        switch ($module_id) 
        {
            case '1':
                $is_permissions = checkPermission($logged_user_id, $module_id, $module_entity_id, 'IsOwner',3,$logged_user_id);
                if($is_permissions)
                {
                    $permissions = FALSE;
                }
                break;
            case '14':
                $this->load->model('events/event_model');
                $is_permissions = $this->event_model->is_admin($module_entity_id, $logged_user_id);
                if($is_permissions)
                {
                    $permissions = FALSE;
                }
                break;
            case '18':
                $this->load->model('pages/page_model');
                $is_permissions = $this->page_model->check_page_owner($logged_user_id, $module_entity_id);
                if($is_permissions)
                {
                    $permissions = FALSE;
                }
                break;
            case '34':
                $this->load->model('forum/forum_model');
                $is_permissions=$this->forum_model->check_forum_category_permissions($logged_user_id, $module_entity_id,FALSE);
                if(!$is_permissions['IsAdmin'])
                {
                    $permissions = FALSE;
                }
                break;
            default:
                if($logged_user_id==$module_entity_id){
                    $permissions = FALSE;                    
                }
                break;
        }

        $is_relation = $this->activity_model->isRelation($module_entity_id,$logged_user_id,true);
        $this->db->select('A.AlbumGUID, A.AlbumName, A.Description, A.Visibility, A.MediaCount as MediaCount, A.CreatedDate,A.UserID,A.ModuleID,A.ModuleEntityID,A.LocationID,A.ActivityID,A.PhotoCount,A.VideoCount');
        $this->db->select('IFNULL(M.ImageName, "") as CoverMedia', FALSE);
        $this->db->select('IFNULL(MS.MediaSectionAlias, "") as MediaSectionAlias', FALSE);
        $this->db->select("(CASE WHEN A.UserID='".$logged_user_id."' THEN A.IsEditable ELSE 0 END) AS IsEditable",false);
        $this->db->join(MEDIA . ' M', 'M.MediaID=A.MediaID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID = M.MediaSectionID', 'LEFT');

        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->db->where_in('MS.MediaSectionID',array(1,3,5));
        }

        if($total_rows===FALSE)
        {
            $page_no = ($page_no) * $page_size;
            $this->db->limit($page_size, $page_no);
        }
        

        if($album_type!=''){
            $this->db->where('A.AlbumType', $album_type);            
        }
        $this->db->where('A.AlbumName !=', DEFAULT_FILE_ALBUM);//excluding files from media tab
        $this->db->where('A.ModuleID', $module_id);
        $this->db->where('A.ModuleEntityID', $module_entity_id);
        $this->db->where('A.StatusID', 2);
        if($permissions)
        {
            $this->db->where('IF(A.IsEditable=0, TRUE, A.MediaCount>0)', NULL, FALSE);
        }
        $this->db->where_in('A.Visibility',$is_relation);


        $this->db->order_by($sort_by, $order_by);


        $query = $this->db->get(ALBUMS . ' A');
        //echo $this->db->last_query();die;
        if($total_rows===FALSE)
        {
            return $query->result_array();    
        }
        else
        {
            return $query->num_rows();
        }
    }
    
    public function is_valid_album($album_guid)
    {
        $this->db->select('AlbumGUID');
        $this->db->from(ALBUMS);
        $this->db->where('AlbumGUID',$album_guid);
        $query = $this->db->get();
        if($query->num_rows())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function update_activity_for_album($album_id,$commentable,$visibility)
    {
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
    public function save_album($album) 
    {
        $album_id = FALSE;
        if (!empty($album['AlbumGUID'])) 
        {
            $this->db->where('AlbumGUID', $album['AlbumGUID']);
            $this->db->update(ALBUMS, $album);
            //$album_id = TRUE;
            $album_id = get_detail_by_guid($album['AlbumGUID'], 13, "", 1);
            $album['AlbumID'] = $album_id;
            $album_id = $album;
        } 
        else 
        {
            $album['AlbumGUID'] = get_guid();
            $this->db->insert(ALBUMS, $album);
            $album_id = $this->db->insert_id();
            if ($album_id) 
            {
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
    public function album_name_exist($module_id, $module_entity_id, $album_name, $album_type = NULL) {
        $this->db->where('AlbumName', $album_name);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $this->db->where('StatusID !=', 3);
        $this->db->from(ALBUMS);
        if ($album_type !== NULL) {
            $this->db->where('AlbumType', $album_type);
        }
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
    public function get_album_by_guid($album_guid,$logged_user_id='') {
        $this->db->select('A.AlbumGUID, A.UserID,U.UserGUID, A.AlbumName, A.Description, A.Visibility,A.AlbumType, A.MediaCount as MediaCount, A.CreatedDate, A.ModifiedDate,A.ActivityID,A.UserID, A.LocationID,A.PhotoCount,A.VideoCount,');
        if(!empty($logged_user_id))
        {
            $this->db->select("(CASE WHEN A.UserID='".$logged_user_id."' THEN A.IsEditable ELSE 0 END) AS IsEditable",false);
        }
        else
        {
            $this->db->select("A.IsEditable",false);
        }
        $this->db->select('IFNULL(M.ImageName, "") as CoverMedia', FALSE);
        $this->db->select('IFNULL(MS.MediaSectionAlias, "") as MediaSectionAlias', FALSE);
        $this->db->join(MEDIA . ' M', 'M.MediaId=A.MediaID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID = M.MediaSectionID', 'LEFT');
        $this->db->join(USERS . ' U', 'U.UserID = A.UserID', 'LEFT');
        $this->db->limit(1);
        $this->db->where('A.AlbumGUID', $album_guid);
        $query = $this->db->get(ALBUMS . ' A');
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
    public function get_album_media($album_id, $page_no = 0, $page_size = PAGE_SIZE, $sort_by = 'CreatedDate', $order_by = 'DESC', $cover_id = 0, $total_rows=FALSE, $logged_user_id=0,$MediaGUID='',$pageType='') {

         
        $album_detail = get_detail_by_id($album_id, 13, "AlbumName, ModuleID, ModuleEntityID, ActivityID", 2);
        $activityId = $album_detail['ActivityID'];
        $allowedArray = array();
        if(!empty($activityId)){
            $ActivityTypeID = get_detail_by_id($activityId,0,'ActivityTypeID');
            $this->db->select('CommentsAllowed,LikeAllowed,FlagAllowed,ShareAllowed,FavouriteAllowed');
            $this->db->where('ActivityTypeID',$ActivityTypeID);
            $query = $this->db->get(ACTIVITYTYPE);
            $allowedArray = $query->row_array();
        }
        $is_wall_album = FALSE;      
        if($album_detail['ModuleID'] == 3 && $album_detail['AlbumName'] == DEFAULT_WALL_ALBUM)
        {
            $this->load->model('activity_model');
            $module_entity_id = $album_detail['ModuleEntityID'];
            $is_relation = $this->activity_model->isRelation($module_entity_id, $logged_user_id, true);
            $is_wall_album = TRUE;
        }
        
        
        $this->db->select('M.MediaGUID,M.MediaSectionID,M.MediaSectionReferenceID,M.LocationID,M.MediaID, IFNULL(M.ImageName,"") ImageName, M.Caption, IFNULL(M.NoOfComments, 0) NoOfComments, IFNULL(M.NoOfLikes,0) NoOfLikes, IFNULL(M.CreatedDate,"") CreatedDate, MT.Name as MediaType, M.UserID', FALSE);
        $this->db->select('IFNULL(M.ConversionStatus,"") AS ConversionStatus, IFNULL(M.VideoLength,"") AS VideoLength', FALSE);
        $this->db->select('IF(M.MediaID=' . $cover_id . ', 1,0) IsCoverMedia ', FALSE);
        $this->db->select('IFNULL(MS.MediaSectionAlias, "") as MediaSectionAlias', FALSE);
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID = M.MediaSectionID', 'LEFT');
        if($MediaGUID)
        {
            $this->db->where('M.MediaGUID',$MediaGUID);
        }

        if($is_wall_album)
        {
            $this->db->join(ACTIVITY.' ACT','ACT.ActivityID = M.MediaSectionReferenceID AND M.MediaSectionID=3','left');
            $this->db->where_in('ACT.Privacy',$is_relation);
        }
        //
        $this->db->where('M.AlbumID', $album_id);
        $this->db->where('M.StatusID', 2);
        $this->db->where('M.AddedBy', 1);
        //var_dump($page_size);die;
        if($total_rows===FALSE && $pageType!='edit')
        {
            $page_no = ($page_no) * $page_size;
            $this->db->limit($page_size, $page_no);
        }
        
        $this->db->order_by($sort_by, $order_by);
        
        $query = $this->db->get(MEDIA . ' M');
        //echo $this->db->last_query();die;
        if($total_rows===FALSE)
        {
            $queryData = array();
            foreach($query->result_array() as $result){
               // $result['IsMediaOwner'] = 0;
               /* if($result['UserID'] == $user_id){
                    $result['IsMediaOwner'] = 1;
                }*/
                //$result['IsLike'] = $this->activity_model->checkLike($result['MediaGUID'], 'Media', $user_id);
                //$result['ViewCount'] = get_entity_view_count($result['MediaID'], 'Media');
                unset($result['MediaID']);
                $result['Location'] = array();
                if(!empty($result['LocationID'])){
                    $result['Location'] = get_location_by_id($result['LocationID']);
                }
                $result['FileName'] = pathinfo($result['ImageName'], PATHINFO_FILENAME);
                
                if(strtolower($result['MediaType'])=='image'){
                    $result['MediaType'] = 'PHOTO';
                }else{
                    $result['MediaType'] = strtoupper($result['MediaType']);
                }
                
                $IsMedia = 1;
                if($result['MediaSectionID'] == 3){
                        $countQ = $this->db->where(array('MediaSectionID'=>$result['MediaSectionID'],'MediaSectionReferenceID'=>$result['MediaSectionReferenceID'], 'StatusID'=>2))->order_by('CreatedDate','DESC')->get(MEDIA);
                        $media_count = $countQ->num_rows();
                        $this->db->select('Privacy as Visibility',false);
                        $this->db->select('A.ActivityGUID,A.ModuleEntityOwner,A.ActivityID,A.NoOfComments,A.NoOfLikes,A.NoOfShares,A.UserID,A.IsCommentable,A.Flaggable');
                        $this->db->select('ATY.FlagAllowed,ATY.CommentsAllowed,ATY.ShareAllowed,ATY.LikeAllowed');
                        $this->db->from(ACTIVITY.' A');
                        $this->db->join(ACTIVITYTYPE.' ATY','A.ActivityTypeID=ATY.ActivityTypeID','left');
                        $this->db->where('A.ActivityID',$result['MediaSectionReferenceID']);
                        $actQ = $this->db->get();
                        if($actQ->num_rows()){
                            $activity = $actQ->row_array();
                        }
                        if($media_count==1){
                            $IsMedia = 0;
                        }
                }
                if(!$IsMedia){
                    $result['CommentsAllowed'] = $activity['IsCommentable'];
                    $result['ShareAllowed'] = $activity['ShareAllowed'];
                    $result['LikeAllowed'] = $activity['LikeAllowed'];
                    if($this->IsApp == 1){
                        $result['IsLike'] = $this->activity_model->checkLike($activity['ActivityGUID'], 'Activity', $logged_user_id, $activity['ModuleEntityOwner']);
                    }
                    $result['NoOfComments'] = $activity['NoOfComments'];
                    $result['NoOfLikes'] = $activity['NoOfLikes'];
                    $result['NoOfShares'] = $activity['NoOfShares'];
                }
                else{
                    $result['CommentsAllowed'] = isset($allowedArray['CommentsAllowed'])?$allowedArray['CommentsAllowed']:1;
                    $result['ShareAllowed'] = isset($allowedArray['ShareAllowed'])?$allowedArray['ShareAllowed']:1;
                    $result['LikeAllowed'] = isset($allowedArray['LikeAllowed'])?$allowedArray['LikeAllowed']:1;
                    if($this->IsApp == 1){
                        $result['IsLike'] = $this->activity_model->checkLike($result['MediaGUID'], 'Media', $logged_user_id);
                    }
                }
                
                $queryData[] = $result;
            }
            return $queryData;    
        }
        else
        {
            return $query->num_rows();                        
        }
    }
    
    /**
     * common function used to get single row from any table
     * @param String $select
     * @param String $table
     * @param Array/String $where
     */
    function get_row($select = '*', $table, $where = "") {
        $this->db->select($select);
        $this->db->from($table);
        if ($where != "") {
            $this->db->where($where, NULL, FALSE);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Delete album
     * @param type $AlbumGUID
     * @return boolean
     */
    public function delete_album($album_id) {
        
        $activity_id=get_detail_by_id($album_id, 13, "ActivityID", 1);
        $this->db->set('StatusID', 3);
        $this->db->where('AlbumID', $album_id);
        $this->db->update(ALBUMS);

        /*$query = $this->db->get_where(MEDIA,array('AlbumID'=>$album_id));
        if($query->num_rows()){
            $this->load->model('activity_model');
            foreach($query->result() as $media){
                $this->activity_model->removeChildActivity($media->MediaID,array('14','15'));
            }
        }*/

        //mark media deleted
        $this->db->set('StatusID', 3);
        $this->db->where('AlbumID', $album_id);
        $this->db->update(MEDIA);
        return TRUE;
    }

    /**
     * update single media
     * @param type $media
     * @param type $album_id
     * @return boolean
     */
    public function update_media($media, $album_id) {
        $this->db->where('MediaGUID', $media['MediaGUID']);
        $this->db->where('AlbumID', $album_id);
        $this->db->update(MEDIA, $media);
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
     * @param type $album_guid
     */
    public function update_album_media_count($album_guid, $count=0, $update_type = 'add') {
        
        $this->db->where('AlbumGUID', $album_guid);
        if($update_type == "add"){
            $this->db->set("MediaCount", "MediaCount+($count)", FALSE);
        }else if($update_type == 'reduce'){
            $this->db->set("MediaCount", "MediaCount-($count)", FALSE);
        }else if($update_type == 'update'){
            $this->db->set("MediaCount", $count, FALSE);
        }
        $this->db->update(ALBUMS);
        return true;
    }
    
    /**
     * Add album media
     * @param array $medias
     * @param integer $album_id
     * @param integer $module_id
     * @return boolean
     */
    public function add_album_media($data, $album_id, $module_id) 
    {
        $result = 0;
        if (empty($data) === FALSE) 
        {
            $media = array();
            $album_details      = get_detail_by_id($album_id,13,'LocationID,ActivityID',2);
            $album_location_id  = $album_details['LocationID'];
            $IsCommentable      = get_detail_by_id($album_details['ActivityID'],0,'IsCommentable',1);
            foreach ($data as $temp) 
            {
                //store media location
                $location_id = $album_location_id;
                if(!empty($temp['Location']))
                {
                    $post_location = $temp['Location'];

                    $insert_location = array(
                        'LocationGUID' 		=> get_guid(),
                        'UniqueID' 			=> isset($post_location['UniqueID']) 		? $post_location['UniqueID'] 		: "",
                        'FormattedAddress' 	=> isset($post_location['FormattedAddress'])? $post_location['FormattedAddress']: "",
                        'Latitude' 			=> isset($post_location['Latitude']) 		? $post_location['Latitude'] 		: "",
                        'Longitude' 		=> isset($post_location['Longitude']) 		? $post_location['Longitude'] 		: "",
                        'StreetNumber' 		=> isset($post_location['StreetNumber']) 	? $post_location['StreetNumber'] 	: "",
                        'Route' 			=> isset($post_location['Route']) 			? $post_location['Route'] 			: "",
                        'City' 				=> isset($post_location['City']) 			? $post_location['City'] 			: "",
                        'State' 			=> isset($post_location['State']) 			? $post_location['State'] 			: "",
                        'Country' 			=> isset($post_location['Country']) 		? $post_location['Country'] 		: "",
                        'PostalCode' 		=> isset($post_location['PostalCode']) 		? $post_location['PostalCode'] 		: "",
                        'StateCode' 		=> isset($post_location['StateCode']) 		? $post_location['StateCode'] 		: "",
                        'CountryCode' 		=> isset($post_location['CountryCode']) 	? $post_location['CountryCode'] 	: "",
                    );

                    $location 	 = insert_location($insert_location);
                    $location_id =  $location['LocationID'];
                }
                if(isset($temp['IsCommentable']))
                {
                    $IsCommentable = $temp['IsCommentable'];
                }
                $media_array = array(
                    'AlbumID' => $album_id,
                    'MediaGUID' => $temp['MediaGUID'],
                    'Caption' => isset($temp['Caption'])?$temp['Caption']:"",
                    'SportsID' => isset($temp['SportsID']) ? $temp['SportsID'] : 0,
                    'ModuleEntityID' => $album_id,
                    'MediaSectionReferenceID' => $album_id,
                    'ModuleID' => $module_id,
                    'StatusID' => 2,
                    'Description' => isset($temp['Description'])?$temp['Description']:"",
                    'LocationID' => $location_id,
                    'IsCommentable' => $IsCommentable,
                );
                
                $media[] = $media_array;                
            }
            $this->db->update_batch(MEDIA, $media, 'MediaGUID');

            if($this->IsApp == 1){
            /*added by gautam starts*/
//                        $mediaA = array(
//                            'MediaID' => get_detail_by_guid($temp['MediaGUID'],21,'MediaID'),
//                            'AlbumGUID' => get_detail_by_id($album_id,13,'AlbumGUID')
//                        );
//                        $this->album_model->save_album($mediaA);
            /*added by gautam ends*/
            }
                        
            $result = $this->db->affected_rows();
        }
        return $result;
    }
    
    /**
     * Update album activity id
     * @param integer $user_id
     * @param guid $AlbumGUID
     * @param integer $Privacy
     * @return boolean
     */
    public function set_privacy($user_id,$AlbumGUID,$Privacy){
        $Return = array('Data','ResponseCode');
        $query = $this->db->get_where(ALBUMS,array('AlbumGUID'=>$AlbumGUID));
        if($query->num_rows()){
            $row = $query->row_array();
            if($row['UserID']!=$user_id){
                $Return['Data'] = lang('permission_denied');
                $Return['ResponseCode'] = 412;
            } else {
                $this->db->set('Visibility',$Privacy);
                $this->db->where('AlbumGUID',$AlbumGUID);
                $this->db->update(ALBUMS);
                $Return['Data'] = lang('success');
                $Return['ResponseCode'] = 200;
            }
        } else {
            $Return['Data'] = lang('no_record');
            $Return['ResponseCode'] = 412;
        }
        return $Return;
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

}
