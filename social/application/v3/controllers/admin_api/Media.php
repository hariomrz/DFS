<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
* All media related process like : total_count, media_list, media_count
* @package    Media
* @author     Ashwin kumar soni(09-11-2014)
* @version    1.0
*/
//require_once APPPATH . '/libraries/REST_Controller.php';

class Media extends Admin_API_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('admin/media_model','admin/login_model'));

        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
    }

    /**
    * Function for Count total media and sizes
    * Parameters : user_id,
    * Return : Array of total_count pics/videos
    */
    public function total_count_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/total_count';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if($Data['MediaPageName'] == "profile" && !in_array(getRightsId('user_profile_media'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }else if($Data['MediaPageName'] == "media" && !in_array(getRightsId('media_list'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL) {
            $userId = NULL;
            if(isset($Data['userId']) && !empty($Data['userId'])) {
                $userId = "M.UserId ='" . $Data['userId'] . "' ";
            }
            /* Count Pics*/
            /* Get Data from media_model */
            $pics = $this->media_model->getMediaTypeCount(1, $userId);
            $picsSize = array_sum(array_map(function($item) {
                        return $item['Size'];
                    }, $pics['Data']));

            
            /* Count Videos */
            /* Get Data from media_model */
            $videos = $this->media_model->getMediaTypeCount(2, $userId);
            $videoSize = array_sum(array_map(function($item) {
                        return $item['Size'];
                    }, $videos['Data']));
            
            $userIdCondition = NULL;
            if($userId) {
                $userIdCondition = $userId.' AND ';
            }
            //$userIdCondition = isset($Data['userId']) ? "M.UserId ='" . $Data['userId'] . "' AND " : NULL;
            
            /* Retrun Data */
            $Return['Data'] = array(
                'totalApproved' => $this->media_model->getTotal($userIdCondition . " M.IsAdminApproved='1' "),
                'totalUnapproved' => $this->media_model->getTotal($userIdCondition . " M.IsAdminApproved='0' "),
                'totalPictures' => $pics['total'],
                'totalVideos' => $videos['total'],
                'totalPictureSize' => formatSizeUnits($picsSize),
                'totalVideoSize' => formatSizeUnits($videoSize),
                'totalMedia' => ($pics['total'] + $videos['total']),
            );
        }
        /* Final output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
    * Function for show Listing of medias
    * Parameters : begin, end, sortby, orderby, user_id
    * Return : Array of medias
    */
    public function list_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/list';
        $Return['Data'] = array();
        
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if($Data['MediaPageName'] == "profile" && !in_array(getRightsId('user_profile_media'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }else if($Data['MediaPageName'] == "media" && !in_array(getRightsId('media_list'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL) {
            $global_settings = $this->config->item("global_settings");
            
            $start_offset = isset($Data['Begin']) ? $Data['Begin'] : 0;
            $end_offset = isset($Data['End']) ? $Data['End'] : 24;
            $sort_by = isset($Data['SortBy']) ? $Data['SortBy'] : '';
            $order_by = isset($Data['OrderBy']) ? $Data['OrderBy'] : '';
            $userId = isset($Data['userId']) ? $Data['userId'] : NULL;
            $IsAdminApproved = isset($Data['IsAdminApproved']) ? $Data['IsAdminApproved'] : 2 ;
            /* Get media from media_model */
            $media = array('results'=>array());
            $mediaResults = array();
            $filter = array('Extensions'=>array(),'Sizes'=>array(),'Sources'=>array(),'Sections'=>array(),'Devices'=>array());
            if(isset($Data['FilterData']['Extensions'])){
                $filter['Extensions'] = $Data['FilterData']['Extensions'];
            }
            if(isset($Data['FilterData']['Sizes'])){
                $filter['Sizes'] = $Data['FilterData']['Sizes'];
            }
            if(isset($Data['FilterData']['Sources'])){
                $filter['Sources'] = $Data['FilterData']['Sources'];
            }
            if(isset($Data['FilterData']['Types'])){
                $filter['Sections'] = $Data['FilterData']['Types'];
            }
            if(isset($Data['FilterData']['Devices'])){
                $filter['Devices'] = $Data['FilterData']['Devices'];
            }
            $mediaTemp = $this->media_model->getMedia($userId, $sort_by, $order_by, $start_offset, $end_offset,$IsAdminApproved,$filter);
            
            foreach ($mediaTemp['results'] as $temp) {
                $temp['UserName'] = stripslashes($temp['UserName']);
                $ImageUrl  = $temp['ImageUrl'];
                
                //For Image, Video and Youtube video URL path
                if($temp['MediaTypeId'] == VIDEO_MEDIA_TYPE_ID){
                    $temp['ImageUrl'] = get_image_path($temp['MediaSectionAlias'], $ImageUrl,'', '',$temp['MediaTypeId']);
                    $ThumbUrl = get_image_path($temp['MediaSectionAlias'], $ImageUrl,ADMIN_THUMB_WIDTH,ADMIN_THUMB_HEIGHT,$temp['MediaTypeId']);

                    $ext = pathinfo($ThumbUrl, PATHINFO_EXTENSION);

                    $temp['ThumbUrl'] = str_replace(".".$ext, ".jpg", $ThumbUrl);

                }else if($temp['MediaTypeId'] == YOUTUBE_MEDIA_TYPE_ID){
                    $temp['ImageUrl'] = $temp['ImageUrl'];
                    $temp['ThumbUrl'] = get_image_path($temp['MediaSectionAlias'], $ImageUrl,ADMIN_THUMB_WIDTH,ADMIN_THUMB_HEIGHT,$temp['MediaTypeId']);
                }else{
                    $temp['ImageUrl'] = get_image_path($temp['MediaSectionAlias'], $ImageUrl,'', '',$temp['MediaTypeId']);
                    $temp['ThumbUrl'] = get_image_path($temp['MediaSectionAlias'], $ImageUrl,220,220,$temp['MediaTypeId']);
                }
                
                $temp['MediaSize'] = formatSizeUnits($temp['Size']);
                $temp['Size'] = (int)$temp['Size'];
                $temp['AbuseCount'] = (int)$temp['AbuseCount'];
                $temp['MediaDate'] = date($global_settings['date_format'],strtotime($temp['CreatedDate']));
                $mediaResults[] = $temp;
            }
            
            $media['results'] = $mediaResults;
            $media['total_records'] = $mediaTemp['total_records'];
            
            //$userIdCondition = isset($Data['userId']) ? "M.UserId ='" . $Data['userId'] . "' AND " : NULL;
            $userIdCondition = NULL;
            if(isset($Data['userId']) && !empty($Data['userId'])) {
                $userIdCondition = "M.UserId ='" . $Data['userId'] . "' AND ";
            }
            /* Get totalApproved from media_model */
            $media['totalApproved'] = $this->media_model->getTotal($userIdCondition . " IsAdminApproved='1' ");
            
            /* Get totalUnapproved from media_model */
            $media['totalUnapproved'] = $this->media_model->getTotal($userIdCondition . " IsAdminApproved='0' ");
            
            /* Retrun Data */
            $Return['Data'] = $media;
        }
        /* Final output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function tempData_post(){
        $Data = $this->post_data;
        $this->response($this->media_model->getSectionCount(38));
    }
    
    /**
    * Function for get Media Count for media filters
    * Parameters : user_id, approved
    * Return : Array of media_count
    */
    public function media_count_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/media_count';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        $isApproved = isset($Data['approved']) ? $Data['approved'] : 0;
        $condition = "IsAdminApproved='" . $isApproved . "' ";
        
        $userId = NULL;
        if(isset($Data['userId']) && !empty($Data['userId'])) {
            $userId = $Data['userId'];
            $condition .= " AND UserId ='" . $userId . "'";
        }
        
        //$condition .= ($userId == NULL) ? NULL : " AND UserId ='" . $userId . "'";

        /* Get pics from media_model */
        $pics = $this->media_model->getMediaTypeCount(1, $condition);
        
        /* Get videos from media_model */
        $videos = $this->media_model->getMediaTypeCount(2, $condition);
        
        $extensionCounts = $this->media_model->getExtensionCount($userId, $isApproved);
        $MediaExtensions = array();
        foreach($extensionCounts as $extension){
            $MediaExtensions[$extension['MediaType']][] = $extension;
        }
        
        /* Retrun Data */
        $Return['Data'] = array(
            'upload_devices' => $this->media_model->getDeviceTypeCounts($userId, $isApproved),
            'image_extensions' => isset($MediaExtensions['Image']) ? $MediaExtensions['Image'] : array(),
            'video_extensions' => isset($MediaExtensions['Video']) ? $MediaExtensions['Video'] : array(),
            'youtube_extensions' => isset($MediaExtensions['Youtube']) ? $MediaExtensions['Youtube'] : array(),
            'media_sections' => $this->media_model->getSectionCount($userId, $isApproved),
            'media_size' => $this->media_model->getSizeCount($userId, $isApproved),
            'media_source' => $this->media_model->getSourceCount($userId,$isApproved),
            'totalPictures' => $pics['total'],
            'totalVideos' => $videos['total'],
        );
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function media_count_all_post(){
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/media_count_all';
        $Return['Data'] = array();
        $Data = $this->post_data;

        $extensionCounts = $this->media_model->getExtensionCountAll();
        $MediaExtensions = array();
        foreach($extensionCounts as $extension){
            $MediaExtensions[$extension['MediaType']][] = $extension;
        }
        
        $Return['Data'] = array(
            'upload_devices' => $this->media_model->getDeviceTypeCountsAll(),
            'image_extensions' => isset($MediaExtensions['Image']) ? $MediaExtensions['Image'] : array(),
            'video_extensions' => isset($MediaExtensions['Video']) ? $MediaExtensions['Video'] : array(),
            'youtube_extensions' => isset($MediaExtensions['Youtube']) ? $MediaExtensions['Youtube'] : array(),
            'media_sections' => $this->media_model->getSectionCountAll(),
            'media_size' => $this->media_model->getSizeCountAll(),
            'media_source' => $this->media_model->getSourceCountAll()
        );

        $this->response($Return);
    }
    
    /**
    * Function for Upate media like : delete, approve
    * Parameters : action, media_id(s)
    * Return : Array of media_count
    */
    public function update_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/update';
        $Return['Data'] = array();
        $Data = $this->post_data;
        $this->load->model('admin/users_model');
        
        $activity_ids = array();

        //For check permission and allow/denied action
        $action = isset($Data['action']) ? strtolower($Data['action']) : NULL;
        if($action == "delete"){
            $RightsId = getRightsId('media_delete_event');
        }else if($action == "approve"){
            $RightsId = getRightsId('media_approve_event');
        }else{
            $RightsId = 0;
        }
        
        //Check logged in user access right and allow/denied access
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        
        if (isset($Data) && $Data != NULL) {
            $action = isset($Data['action']) ? strtolower($Data['action']) : NULL;
            $medias = isset($Data['media']) ? $Data['media'] : array();
            
            if (!empty($medias)) {
                $medaData = array();
                $deleteMedia = array();
                $profileImageMedia = array();
                /* Different Cases : update Media*/
                switch ($action) {
                    case 'delete':
                        foreach ($medias as $media) {
                            $medaData[] = array('StatusID' => 3, 'MediaID' => $media);
                            $mediaArr = $this->media_model->getMediaDetailById($media);
                            
                            if(!$this->media_model->isAlreadyDeleted($mediaArr['MediaID'])){
                                //For user profile image update
                                if($mediaArr['MediaSectionID'] == PROFILE_SECTION_ID){
                                    $userData = $this->users_model->getValueById(array('UserID','ProfilePicture'),$mediaArr['UserID']);
                                    if($mediaArr['ImageUrl'] == $userData['ProfilePicture']){
                                        $profileImageMedia[] = array('ProfilePicture' => '', 'UserID' => $userData['UserID']);
                                    }
                                }                                
                                
                                if($mediaArr['MediaSectionID'] == 3) {
                                    if(!in_array($mediaArr['MediaSectionReferenceID'],$activity_ids)) {
                                        $activity_ids[] = $mediaArr['MediaSectionReferenceID'];
                                    }
                                }

                                if($mediaArr['MediaSectionID'] == 1 || $mediaArr['MediaSectionID'] == 5) {
                                    if($mediaArr['MediaSectionID'] == 1) {
                                        $activity_type_id = '23';
                                    } else {
                                        $activity_type_id = '24';
                                    }

                                    $this->db->select('ActivityID');
                                    $this->db->from(ACTIVITY);
                                    $this->db->where("Params LIKE '%".$mediaArr['MediaGUID']."%'",null,false);
                                    $this->db->where('ActivityTypeID',$activity_type_id);
                                    $query = $this->db->get();

                                    if($query->num_rows()) {
                                        foreach($query->result() as $res) {
                                            $activity_ids[] = $res->ActivityID;
                                        }
                                    }
                                }
                                //For delete media array
                                $deleteMedia[] = array('StatusID'=>$mediaArr['StatusID'],'MediaID'=>$mediaArr['MediaID'],'CreatedDate'=>$mediaArr['CreatedDate'],'DeletedDate'=>date('Y-m-d H:i:s'));
                            }
                        }
                    break;                    
                    case 'approve':
                        foreach ($medias as $media) {
                            $medaData[] = array('IsAdminApproved' => 1, 'MediaID' => $media);
                        }
                    break;
                }
                /* update Media : In media_model */
                $this->media_model->updateMedias($medaData, 'MediaID');
                
                //For update users profile images
                if(!empty($profileImageMedia)){                
                    $this->users_model->updateMultipleUsersInfo($profileImageMedia, 'UserID');
                }
                
                //For delete media
                if(!empty($deleteMedia)){
                    $this->media_model->deleteMediaFiles($deleteMedia);
                }

                if($activity_ids)
                {
                    $this->media_model->check_and_update_activity($activity_ids);
                }
            }
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
    * Function for Count Abused media total count
    * Parameters :  $DeviceId, $MediaExtensionId, $SourceId, $MediaSectionId, $MediaSizeId
    * Return : Array of abused_total_count
    */
    public function abused_total_count_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/abused_total_count';
        $Return['Data'] = array();
        
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('media_abusemedia'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        $DeviceId = $MediaExtensionId = $SourceId = $MediaSectionId = $MediaSizeId= '';
        if (isset($Data) && $Data != NULL) {
            if(!empty($Data['criteriaList'])) {
                foreach($Data['criteriaList'] as $criteria) {
                    /* Different cases for apply Filters */
                    switch($criteria['key']) {
                        case 'DeviceId':
                            $DeviceId .= $criteria['DeviceId'].',';
                        break;
                    
                        case 'MediaExtensionId':
                            $MediaExtensionId .= $criteria['MediaExtensionId'].',';
                        break;
                    
                        case 'SourceId':
                            $SourceId .= $criteria['SourceId'].',';
                        break;
                    
                        case 'MediaSectionId':
                            $MediaSectionId .= $criteria['MediaSectionId'].',';
                        break;
                    
                        case 'MediaSizeId':
                            $MediaSizeId .= $criteria['MediaSizeId'].',';
                        break;
                    }
                }
            }
            
            /* Count Pics :Get from media_model */
            $pics = $this->media_model->getAbusedMediaTotal($DeviceId,$MediaExtensionId,$SourceId,$MediaSectionId,$MediaSizeId);
            
            $Return['Data'] = array(
                'totalPictures' => $pics['total']
            );
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
    * Function for get Listing for abused media
    * Parameters :  begin, end, sortby, orderby
    * Return : Array of abused_medias
    */
    public function abused_list_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/abused_list';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('media_abusemedia'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL) {
            /* Load Global settings */
            $global_settings = $this->config->item("global_settings");
            
            $start_offset = isset($Data['Begin']) ? $Data['Begin'] : 0;
            $end_offset = isset($Data['End']) ? $Data['End'] : 20;
            $sort_by = isset($Data['SortBy']) ? $Data['SortBy'] : '';
            $order_by = isset($Data['OrderBy']) ? $Data['OrderBy'] : '';
            
            /* Get Abused media from media_model */
            $media = array('results'=>array());
            $mediaResults = array();
            $filter = array('Extensions'=>array(),'Sizes'=>array(),'Sources'=>array(),'Sections'=>array(),'Devices'=>array());
            if(isset($Data['FilterData']['Extensions'])){
                $filter['Extensions'] = $Data['FilterData']['Extensions'];
            }
            if(isset($Data['FilterData']['Sizes'])){
                $filter['Sizes'] = $Data['FilterData']['Sizes'];
            }
            if(isset($Data['FilterData']['Sources'])){
                $filter['Sources'] = $Data['FilterData']['Sources'];
            }
            if(isset($Data['FilterData']['Types'])){
                $filter['Sections'] = $Data['FilterData']['Types'];
            }
            if(isset($Data['FilterData']['Devices'])){
                $filter['Devices'] = $Data['FilterData']['Devices'];
            }
            $mediaTemp = $this->media_model->getAbusedMedia($sort_by, $order_by, $start_offset, $end_offset,$filter);

            /* Iterate array for make ImageURL */
            foreach ($mediaTemp['results'] as $temp){
                $temp['UserName'] = stripslashes($temp['UserName']);
                $ImageUrl  = $temp['ImageUrl'];
                $temp['ImageUrl'] = get_image_path($temp['MediaSectionAlias'], $temp['ImageUrl'],'', '');
                $temp['ThumbUrl'] = get_image_path($temp['MediaSectionAlias'], $ImageUrl,ADMIN_THUMB_WIDTH,ADMIN_THUMB_HEIGHT);
                $temp['MediaSize'] = formatSizeUnits($temp['Size']);
                $temp['Size'] = (int)$temp['Size'];
                $temp['AbuseCount'] = (int)$temp['AbuseCount'];
                $temp['AbuseDate'] = date($global_settings['date_format'],strtotime($temp['CreatedDate']));
                
                $mediaResults[] = $temp;
            }
            
            $media['results'] = $mediaResults;
            
            /* Get total_records from media_model */
            $media['total_records'] = $mediaTemp['total_records'];
            
            $Return['Data'] = $media;
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
    * Function for Media Count for abused_media filters
    * Parameters :  begin, end, sortby, orderby
    * Return : Array of abused_medias
    */
    public function abused_media_count_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/abused_media_count';
        $Return['Data'] = array();
        
        $Data = $this->post_data;
        
        $Return['Data'] = array(
            /* Get Data from media_model */
            'upload_devices' => $this->media_model->getAbusedDeviceTypeCounts(),
            'media_extensions' => $this->media_model->getAbusedExtensionCount(),
            'media_sections' => $this->media_model->getAbusedSectionCount(),
            'media_size' => $this->media_model->getAbusedSizeCount(),
            'media_source' => $this->media_model->getAbusedSourceCount(),
        );
        
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
    * Function for Upate abused media
    * Parameters :  action, media_id(s)
    * Return : Update abused medias
    */
    public function update_abuse_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/update_abuse';
        $Return['Data'] = array();
        
        $Data = $this->post_data;
        
        //For check permission and allow/denied action
        $action = isset($Data['action']) ? strtolower($Data['action']) : NULL;
        if($action == "delete"){
            $RightsId = getRightsId('media_delete_event');
        }else if($action == "approve"){
            $RightsId = getRightsId('media_approve_event');
        }else{
            $RightsId = 0;
        }
        
        //Check logged in user access right and allow/denied access
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL) {
            $action = isset($Data['action']) ? strtolower($Data['action']) : NULL;
            $medias = isset($Data['media']) ? $Data['media'] : array();
            
            if (!empty($medias)) {
                $medaData = array();
                
                /* Different Cases : update abused Media*/
                switch ($action) {
                    case 'delete':
                        foreach ($medias as $media) {
                            $medaData[] = array('StatusID' => 3, 'MediaID' => $media);
                            
                            /* Now delete record from mediaabuse table : In case of Delete */
                            $this->media_model->deleteMedia($media);
                            $mediaDetailArr = get_detail_by_id($media, 21, "MediaSectionID, MediaSectionReferenceID", 2);
                            if($mediaDetailArr['MediaSectionID']==3){
                                if(CACHE_ENABLE)
                                {
                                    $this->cache->delete('activity_'.$mediaDetailArr['MediaSectionReferenceID']);
                                }
                            }
                        }
                        break;
                    case 'approve':
                        foreach ($medias as $media) {
                            $medaData[] = array('IsAdminApproved' => 1, 'MediaID' => $media);
                            /* Now delete record from mediaabuse table : In case of Approve */
                            $this->media_model->deleteMedia($media);
                        }
                        break;
                }
                /* update abused Media : In media_model */
                $this->media_model->updateMedias($medaData, 'MediaID');
            }
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    
    /**
    * Function for approval/removal operation on media
    * Parameters :  action, media_id(s)
    * array of updated media counts
    */
    public function update_media_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/update_media';
        $Return['Data'] = array();
        
        $Data = $this->post_data;
        
        if (isset($Data) && $Data != NULL) {
            $action = isset($Data['action']) ? strtolower($Data['action']) : NULL;
            $medias = isset($Data['media']) ? $Data['media'] : array();
            $userId = NULL;
            if(isset($Data['userId']) && !empty($Data['userId'])) {
                $userId = $Data['userId'];
            }
                        
            if (!empty($medias)) {
                $medaData = array();
                
                /* Different Cases : update Media*/
                switch ($action) {
                    case 'delete':
                        foreach ($medias as $media) {
                            $medaData[] = array('StatusID' => 3, 'MediaID' => $media);
                            $mediaDetailArr = get_detail_by_id($media, 21, "MediaSectionID, MediaSectionReferenceID", 2);
                            if($mediaDetailArr['MediaSectionID']==3){
                                if(CACHE_ENABLE) {
                                    $this->cache->delete('activity_'.$mediaDetailArr['MediaSectionReferenceID']);
                                }
                            }                            
                        }
                        break;
                    case 'approve':
                        foreach ($medias as $media) {
                            $medaData[] = array('IsAdminApproved' => 1, 'MediaID' => $media);
                        }
                        break;
                }
                /* update Media : In media_model */
                $this->media_model->updateMedias($medaData, 'MediaID');
                
                /*get media counts*/
                $responseArray = array();
                $mediaDetails = $this->media_model->getMediaDetails($medias);
                $i=0;
                foreach ($mediaDetails as $mediaDetail) {
                      $paramArray = array(
                        'DeviceID' =>  $mediaDetail['DeviceID'],
                        'SourceID' => $mediaDetail['SourceID'],
                        'MediaExtensionID' => $mediaDetail['MediaExtensionID'],
                        'MediaSectionID' => $mediaDetail['MediaSectionID'],
                        'MediaSizeID' => $mediaDetail['MediaSizeID'],
                    );
                    
                    if($userId!=NULL){
                        $paramArray['userId'] = $userId;
                    }
                    $temDetailArray = $this->media_model->checkMediaCounts($paramArray,true);
                    $responseArray['upload_devices'][$i] = array('DeviceID'=>$mediaDetail['DeviceID'],'approved_count'=>$temDetailArray['admin_approved_for_device'],'yet_to_approve_count'=>$temDetailArray['admin_yet_to_approve_for_device']);
                    $responseArray['media_extensions'][$i] = array('MediaExtensionID'=>$mediaDetail['MediaExtensionID'],'approved_count'=>$temDetailArray['admin_approved_for_mediaext'],'yet_to_approve_count'=>$temDetailArray['admin_yet_to_approve_for_mediaext']);
                    $responseArray['media_sections'][$i] = array('MediaSectionID'=>$mediaDetail['MediaSectionID'],'approved_count'=>$temDetailArray['admin_approved_for_media_sec_ref'],'yet_to_approve_count'=>$temDetailArray['admin_yet_to_approve_for_media_sec_ref']);
                    $responseArray['media_size'][$i] = array('MediaSizeID'=>$mediaDetail['MediaSizeID'],'approved_count'=>$temDetailArray['admin_approved_for_media_size'],'yet_to_approve_count'=>$temDetailArray['admin_yet_to_approve_for_media_size']);
                    $responseArray['media_source'][$i] = array('SourceID'=>$mediaDetail['SourceID'],'approved_count'=>$temDetailArray['admin_approved_for_source'],'yet_to_approve_count'=>$temDetailArray['admin_yet_to_approve_for_source']);
                    $i++;
                }                
                /*end get media counts*/
                $Return['Data'] = $responseArray;
            }
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    
    /**
    * Function for get abused media details
    * Parameters :  media_id
    * Return : Array of abused_media details
    */
    public function abused_media_detail_post(){
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/abused_list';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('media_abusemedia_viewdetail'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL) {
            $media = array();
            $mediaDetailArr = array();
            
            $MediaId = isset($Data['MediaId']) ? $Data['MediaId'] : '';
            
            if(is_numeric($MediaId)) {                
                $mediaDetailArr = $this->media_model->getAbusedMediaDetailById($MediaId);
                $mediaDetailArr['SpamCount'] = 0;
                $mediaDetailArr['AbuseContent'] = 0;
                
                $ImageUrl  = $mediaDetailArr['ImageUrl'];
                $mediaDetailArr['ImageUrl'] = get_image_path($mediaDetailArr['MediaSectionAlias'], $mediaDetailArr['ImageUrl'],'', '');
                $mediaDetailArr['ThumbUrl'] = get_image_path($mediaDetailArr['MediaSectionAlias'], $ImageUrl,ADMIN_THUMB_WIDTH,ADMIN_THUMB_HEIGHT);  
                
                $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
                $mediaDetailArr['profilepicture'] = get_image_path($profileSection, $mediaDetailArr['profilepicture'],ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);
            }            
            $media['results'] = $mediaDetailArr;            
            $Return['Data'] = $media;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }        
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
    * Function for get abused media comments
    * Parameters :  media_id
    * Return : Array of abused_media comments
    */
    public function abused_media_comments_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/media/abused_list';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('media_abusemedia_viewdetail'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        if (isset($Data) && $Data != NULL) {
            $media = array();
            $mediaArr = array();            
            $MediaId = isset($Data['MediaId']) ? $Data['MediaId'] : '';
            
            if(is_numeric($MediaId)){                
                $mediaDetailArr = $this->media_model->getAbusedMediaCommnetsById($MediaId);                
                foreach($mediaDetailArr as $mediaTemp){
                    $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
                    $mediaTemp['profilepicture'] = get_image_path($profileSection, $mediaTemp['profilepicture'],ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);
                    $mediaArr[] = $mediaTemp;
                }                
            }
            
            $media['results'] = $mediaArr;            
            $Return['Data'] = $media;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }        
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    
    /**
     * Function for media analytics listings and report data.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function media_analytics_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/media/media_analytics';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('media_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL ) {
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['SearchKey']))  $search_keyword=$Data['SearchKey']; else $search_keyword='';
            $search_keyword=str_replace("_"," ",$search_keyword);

            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';

            //For Media Analytics users listing data
            $tempMediaAnalytics = array();
            $mediaTemp = $this->media_model->getMediaAnalytics($start_offset, $end_offset, $start_date, $end_date, $search_keyword, $sort_by, $order_by);                
           
            foreach ($mediaTemp['results'] as $temp) {
                $temp['username'] = stripslashes($temp['username']);
                $temp['firstname'] = stripslashes($temp['firstname']);
                $temp['lastname'] = stripslashes($temp['lastname']);
                
                $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
                $temp['profilepicture'] = get_image_path($profileSection, $temp['profilepicture'],ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);
                $temp['size'] = formatSizeUnits($temp['size']);
                
                if(!empty($temp['userid'])){
                    $temp['userroleid'] = $this->media_model->getUserRoles($temp['userid']);
                }
            
                $tempMediaAnalytics[] = $temp;
            }
            $Return['Data']['total_records'] = $mediaTemp['total_records'];
            $Return['Data']['results'] = $tempMediaAnalytics;
            
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for media analytics report data.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function media_analytics_report_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/media/media_analytics_report';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('media_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL ) {            
            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';

            //For Media Analytics Report
            $mediaReportTemp = $this->media_model->getMediaAnalyticsReport($start_date, $end_date);
            
            $mediaReportTemp['total_size'] = formatSizeUnits($mediaReportTemp['total_size']);
            $mediaReportTemp['picture_size'] = formatSizeUnits($mediaReportTemp['picture_size']);
            $mediaReportTemp['video_size'] = formatSizeUnits($mediaReportTemp['video_size']);
            $mediaReportTemp['abuse_size'] = formatSizeUnits($mediaReportTemp['abuse_size']);
            $mediaReportTemp['abuse_picture_size'] = formatSizeUnits($mediaReportTemp['abuse_picture_size']);
            $mediaReportTemp['abuse_video_size'] = formatSizeUnits($mediaReportTemp['abuse_video_size']);
            
            $mediaReportTemp['abuse_count'] = ($mediaReportTemp['abuse_count']) ? $mediaReportTemp['abuse_count'] : 0;
            $mediaReportTemp['picture_count'] = ($mediaReportTemp['picture_count']) ? $mediaReportTemp['picture_count'] : 0;
            $mediaReportTemp['video_count'] = ($mediaReportTemp['video_count']) ? $mediaReportTemp['video_count'] : 0;
            $mediaReportTemp['abuse_picture_count'] = ($mediaReportTemp['abuse_picture_count']) ? $mediaReportTemp['abuse_picture_count'] : 0;
            $mediaReportTemp['abuse_video_count'] = ($mediaReportTemp['abuse_video_count']) ? $mediaReportTemp['abuse_video_count'] : 0;

            $Return['Data']['media_report'] = $mediaReportTemp;            
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for media analytics data download
     * Parameters : From services.js(Angular file)
     * 
     */
    public function download_media_analytics_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/media/download_media_analytics';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('analytic_download_event'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL ) {
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['SearchKey']))  $search_keyword=$Data['SearchKey']; else $search_keyword='';
            $search_keyword=str_replace("_"," ",$search_keyword);

            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';
            if(isset($Data['dateFilterText'])) $dateFilterText = $Data['dateFilterText']; else $dateFilterText = "All";

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';

            //For Media Analytics users listing data
            $mediaTemp = $this->media_model->getMediaAnalytics($start_offset, $end_offset, $start_date, $end_date, $search_keyword, $sort_by, $order_by);                

            $excelInput = array();
            foreach($mediaTemp['results'] as $row){
                $inputArr['name'] = stripslashes($row['username']);
                $inputArr['location'] = $row['location'];
                $inputArr['size'] = $row['size'];
                $inputArr['uploaded'] = $row['uploaded'];
                $inputArr['flagged'] = $row['flagged'];
                $inputArr['deleted'] = $row['deleted'];
                
                $excelInput[] = $inputArr;
            }
            
            $excelArr = array();
            $excelArr['headerArray'] = array('name'=>'Name','location'=>'Location','size'=>'Size', 'uploaded'=>'Uploaded','flagged'=>'Flagged','deleted'=>'Deleted');
            $excelArr['sheetTitle'] = 'Media Analytics';
            $excelArr['fileName'] = "MediaAnalytics.xls";
            $excelArr['folderPath'] = DOC_PATH.ROOT_FOLDER.'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/";            
            $excelArr['inputData'] = $excelInput;
            $excelArr['ReportHeader'] = array("ReportName" => "Media Analytics", "dateFilterText" => $dateFilterText);
            
            $result = $this->media_model->downloadExcelFile($excelArr);            
            if($result){
                $csv_url = base_url().'/admin/users/downloadmediaanalytics';
                $Return['csv_url'] = $csv_url;
            }                        
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
}//End of file media.php
