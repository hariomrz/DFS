<?php
/**
 * This model is used for getting and storing Event related information
 * @package    Media_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Media_model extends Common_Model {

    function __construct() {
        parent::__construct();
        $this->load->model(array('activity/activity_model','notification_model','subscribe_model'));
    }

    function get_entity_media($module_id,$module_entity_id,$current_user=0, $page_no=1, $page_size=4)
    {   
        $limit = '';
        if ($page_size) { // Check for pagination
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $limit.= " LIMIT ".$offset.",".$page_size." ";
        }
        if ($module_id == 3)
        {
            $this->load->model('activity/activity_model');
            $is_relation = $this->activity_model->isRelation($module_entity_id, $current_user, true); // Visibility
        } else
        {
            $is_relation = array(1, 2, 3, 4);
        }
        
        $module_id = $this->db->escape_str($module_id);
        $module_entity_id = $this->db->escape_str($module_entity_id);
        
        $query =  "
            SELECT `MT`.`Name` as `MediaType`, `M`.`ModuleID`, `M`.`Resolution`, U.UserGUID, U.FirstName, U.LastName, IF(U.ProfilePicture='', 'user_default.jpg', U.ProfilePicture) as ProfilePicture, PU.Url as ProfileURL, `M`.`ConversionStatus`, `M`.`AlbumID`, `M`.`MediaGUID`, `M`.`MediaID`, `M`.`ImageName`, `M`.`Caption`, `M`.`MediaSectionID`, `M`.`MediaSectionReferenceID`, `M`.`CreatedDate`, `M`.`NoOfComments`, `M`.`NoOfLikes`, `M`.`NoOfShares`, `M`.`UserID`, `M`.`Flaggable`, `M`.`IsCommentable`, `MS`.`MediaSectionAlias` as `MediaFolder`
            FROM `Media` `M`
            LEFT JOIN `MediaExtensions` `ME` ON `ME`.`MediaExtensionID`=`M`.`MediaExtensionID`
            LEFT JOIN `MediaTypes` `MT` ON `MT`.`MediaTypeID`=`ME`.`MediaTypeID`
            LEFT JOIN `MediaSections` `MS` ON `MS`.`MediaSectionID`=`M`.`MediaSectionID`
            LEFT JOIN `Users` `U` ON `U`.`UserID`=`M`.`UserID`
            LEFT JOIN `Albums` `AL` ON `AL`.`AlbumID`=`M`.`AlbumID`
            JOIN `ProfileUrl` `PU` ON `PU`.`EntityID`=`U`.`UserID` AND `PU`.`EntityType`='User'
            ";
            $where = " WHERE  `M`.`StatusID` = '2' ";

            if($module_id!='3')
            {
                $where .= " AND A.ModuleID=".$module_id."";
            }
            if($module_id=='3')
            {
                if($is_relation)
                {
                    $where .= " AND A.Privacy IN (".implode(',', $is_relation).") AND M.UserID=".$module_entity_id."";
                }
            }
            else
            {
                $where .= " AND A.ModuleID='".$module_id."' AND A.ModuleEntityID=".$module_entity_id."";
            }

            $where_3 = " AND (SELECT Privacy FROM ".ACTIVITY." WHERE ActivityID=M.MediaSectionReferenceID AND M.MediaSectionID=3 AND Privacy IN (".implode(',', $is_relation).")) is not null ";

            $part1 = " LEFT JOIN `Activity` `A` ON A.Params LIKE '%M.MediaGUID%' AND A.StatusID NOT IN (3,19) ";
            $part2 = " LEFT JOIN `Activity` `A` ON A.ActivityID=M.MediaSectionReferenceID AND M.MediaSectionID='3' AND A.StatusID NOT IN (3,19)";
            $part3 = " LEFT JOIN `Activity` `A` ON A.ActivityID=AL.ActivityID AND A.StatusID NOT IN (3,19) ";

            $query1 = $query.$part1.$where;
            $query2 = $query.$part2.$where;
            $query3 = $query.$part3.$where.$where_3;

            $sql = "
                SELECT MediaType,ModuleID,UserGUID,FirstName,LastName,ProfilePicture, ProfileURL, ConversionStatus, AlbumID, MediaGUID, MediaID, ImageName, Caption, MediaSectionID, MediaSectionReferenceID, CreatedDate, NoOfComments, NoOfLikes, NoOfShares, UserID, Flaggable, IsCommentable, MediaFolder  FROM (
                    ".$query1."
                    UNION ALL
                    ".$query2."
                    UNION ALL
                    ".$query3."
                ) tbl WHERE MediaType IN('Image','Video')
                GROUP BY MediaGUID
                ORDER BY CreatedDate DESC
                ".$limit;
        $query = $this->db->query($sql);
        //echo $this->db->last_query();
        if($query->num_rows())
        {
            return $query->result_array();
        }
        else
        {
            return array();
        }
    }

    function get_event_media($module_id,$module_entity_id,$current_user=0,$page_no = '', $page_size = '')
    {
        $result = array("TotalRecords" => 0, "MediaList" => array());
        if ($module_id == 3)
        {
            $this->load->model('activity/activity_model');
            $is_relation = $this->activity_model->isRelation($module_entity_id, $current_user, true); // Visibility
        } else
        {
            $is_relation = array(1, 2, 3, 4);
        }
        
        $module_entity_id = $this->db->escape_str($module_entity_id);
        
        $query =  "
            SELECT `MT`.`Name` as `MediaType`, `M`.`ModuleID`, `M`.`Resolution`, U.UserGUID, U.FirstName, U.LastName, IF(U.ProfilePicture='', 'user_default.jpg', U.ProfilePicture) as ProfilePicture, PU.Url as ProfileURL, `M`.`ConversionStatus`, `M`.`AlbumID`, `M`.`MediaGUID`, `M`.`MediaID`, `M`.`ImageName`, `M`.`Caption`, `M`.`MediaSectionID`, `M`.`MediaSectionReferenceID`, `M`.`CreatedDate`,`ACT`.`ActivityGUID`,`ACT`.`Params`, `ACT`.`NoOfComments` as ANoOfComments, `ACT`.`NoOfLikes` as ANoOfLikes, `ACT`.`NoOfShares` as ANoOfShares,`M`.`NoOfComments` as MNoOfComments, `M`.`NoOfLikes` as MNoOfLikes, `M`.`NoOfShares` as MNoOfShares, `M`.`UserID`, `M`.`Flaggable`, `M`.`IsCommentable`, `MS`.`MediaSectionAlias` as `MediaFolder`
            FROM `Media` `M`
            LEFT JOIN `MediaExtensions` `ME` ON `ME`.`MediaExtensionID`=`M`.`MediaExtensionID`
            LEFT JOIN `MediaTypes` `MT` ON `MT`.`MediaTypeID`=`ME`.`MediaTypeID`
            LEFT JOIN `MediaSections` `MS` ON `MS`.`MediaSectionID`=`M`.`MediaSectionID`
            LEFT JOIN `Users` `U` ON `U`.`UserID`=`M`.`UserID`
            LEFT JOIN `Albums` `AL` ON `AL`.`AlbumID`=`M`.`AlbumID`
            LEFT JOIN ".ACTIVITY." ACT ON ACT.ActivityID=`M`.`MediaSectionReferenceID`
            JOIN `ProfileUrl` `PU` ON `PU`.`EntityID`=`U`.`UserID` AND `PU`.`EntityType`='User'
            ";
            $where = " WHERE  `M`.`StatusID` = '2' ";

            if($module_id!='3')
            {
                $where .= " AND A.ModuleID='".$module_id."'";
            }
            if($module_id=='3')
            {
                if($is_relation)
                {
                    $where .= " AND A.Privacy IN (".implode(',', $is_relation).") AND M.UserID=".$module_entity_id."";
                }
            }
            else
            {
                $where .= " AND A.ModuleID='".$module_id."' AND A.ModuleEntityID=".$module_entity_id."";
            }

            $where_3 = " AND (SELECT Privacy FROM ".ACTIVITY." WHERE ActivityID=M.MediaSectionReferenceID AND M.MediaSectionID=3 AND Privacy IN (".implode(',', $is_relation).")) is not null ";

            $part1 = " LEFT JOIN `Activity` `A` ON A.Params LIKE '%M.MediaGUID%' AND A.StatusID NOT IN (3,19) ";
            $part2 = " LEFT JOIN `Activity` `A` ON A.ActivityID=M.MediaSectionReferenceID AND M.MediaSectionID='3' AND A.StatusID NOT IN (3,19)";
            $part3 = " LEFT JOIN `Activity` `A` ON A.ActivityID=AL.ActivityID AND A.StatusID NOT IN (3,19) ";

            $query1 = $query.$part1.$where;
            $query2 = $query.$part2.$where;
            $query3 = $query.$part3.$where.$where_3;

            $sql = "
                SELECT MediaType,ModuleID,UserGUID,FirstName,LastName,ProfilePicture, ProfileURL, ConversionStatus, AlbumID, MediaGUID, MediaID, ImageName, Caption, MediaSectionID, MediaSectionReferenceID, CreatedDate,ActivityGUID,Params, ANoOfComments, ANoOfLikes, ANoOfShares,MNoOfComments, MNoOfLikes, MNoOfShares, UserID, Flaggable, IsCommentable, MediaFolder  FROM (
                    ".$query1."
                    UNION ALL
                    ".$query2."
                    UNION ALL
                    ".$query3."
                ) tbl WHERE MediaType IN('Image','Video')
                GROUP BY MediaGUID
                ORDER BY CreatedDate DESC";
        /*if ($page_size) { // Check for pagination
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }*/



        $query = $this->db->query($sql);
        //echo $this->db->last_query();
        if($query->num_rows())
        {
            $result['TotalRecords'] = $query->num_rows();

            if ($page_size) { // Check for pagination
                $offset = $this->get_pagination_offset($page_no, $page_size);
                $sql.= " LIMIT ".$offset.",".$page_size." ";
            }
            $query1 = $this->db->query($sql); 
            $media_list = array();
            foreach($query1->result_array() as $media)
            {
                $params = json_decode($media['Params'], true);
                if($params['count'] == 1)
                {
                    $media['NoOfComments']  = $media['ANoOfComments'];
                    $media['NoOfLikes']     = $media['ANoOfLikes'];
                    $media['NoOfShares']    = $media['ANoOfShares'];
                    $media['IsLike']        = $this->activity_model->checkLike($media['ActivityGUID'], 'Activity', $current_user, 0);
                }
                else
                {
                    $media['NoOfComments']  = $media['MNoOfComments'];
                    $media['NoOfLikes']     = $media['MNoOfLikes'];
                    $media['NoOfShares']    = $media['MNoOfShares'];
                    $media['IsLike']        = $this->activity_model->checkLike($media['MediaGUID'], 'Media', $current_user, 0);
                }
                
                unset($media['ANoOfComments']);
                unset($media['ANoOfLikes']);
                unset($media['ANoOfShares']);
                unset($media['MNoOfComments']);
                unset($media['MNoOfLikes']);
                unset($media['MNoOfShares']);

                $media['ShareAllowed']  = ($media['MediaSectionID'] == '1' || $media['MediaSectionID'] == '5') ? 0 : 1 ;
                $media_list[] = $media;
            }
            $result['MediaList'] = $media_list;
        }
        return $result;
    }

    function get_category_media($module_id,$module_entity_id,$current_user=0,$page_no = '', $page_size = '')
    {
        $result = array("TotalRecords" => 0, "MediaList" => array());
        if ($module_id == 3)
        {
            $this->load->model('activity/activity_model');
            $is_relation = $this->activity_model->isRelation($module_entity_id, $current_user, true); // Visibility
        } else
        {
            $is_relation = array(1, 2, 3, 4);
        }

        $query =  "
            SELECT `MT`.`Name` as `MediaType`, `M`.`ModuleID`, `M`.`Resolution`, U.UserGUID, U.FirstName, U.LastName, IF(U.ProfilePicture='', 'user_default.jpg', U.ProfilePicture) as ProfilePicture, PU.Url as ProfileURL, `M`.`ConversionStatus`, `M`.`AlbumID`, `M`.`MediaGUID`, `M`.`MediaID`, `M`.`ImageName`, `M`.`Caption`, `M`.`MediaSectionID`, `M`.`MediaSectionReferenceID`, `M`.`CreatedDate`,`ACT`.`ActivityGUID`,`ACT`.`Params`, `ACT`.`NoOfComments` as ANoOfComments, `ACT`.`NoOfLikes` as ANoOfLikes, `ACT`.`NoOfShares` as ANoOfShares,`M`.`NoOfComments` as MNoOfComments, `M`.`NoOfLikes` as MNoOfLikes, `M`.`NoOfShares` as MNoOfShares, `M`.`UserID`, `M`.`Flaggable`, `M`.`IsCommentable`, `MS`.`MediaSectionAlias` as `MediaFolder`
            FROM `Media` `M`
            LEFT JOIN `MediaExtensions` `ME` ON `ME`.`MediaExtensionID`=`M`.`MediaExtensionID`
            LEFT JOIN `MediaTypes` `MT` ON `MT`.`MediaTypeID`=`ME`.`MediaTypeID`
            LEFT JOIN `MediaSections` `MS` ON `MS`.`MediaSectionID`=`M`.`MediaSectionID`
            LEFT JOIN `Users` `U` ON `U`.`UserID`=`M`.`UserID`
            LEFT JOIN `Albums` `AL` ON `AL`.`AlbumID`=`M`.`AlbumID`
            LEFT JOIN ".ACTIVITY." ACT ON ACT.ActivityID=`M`.`MediaSectionReferenceID`
            JOIN `ProfileUrl` `PU` ON `PU`.`EntityID`=`U`.`UserID` AND `PU`.`EntityType`='User'
            ";
            $where = " WHERE  `M`.`StatusID` = '2' ";

            if($module_id!='3')
            {
                $where .= " AND A.ModuleID='".$module_id."'";
            }
            if($module_id=='3')
            {
                if($is_relation)
                {
                    $where .= " AND A.Privacy IN (".implode(',', $is_relation).") AND M.UserID='".$module_entity_id."'";
                }
            }
            else
            {
                $where .= " AND A.ModuleID='".$module_id."' AND A.ModuleEntityID='".$module_entity_id."'";
            }

            $where_3 = " AND (SELECT Privacy FROM ".ACTIVITY." WHERE ActivityID=M.MediaSectionReferenceID AND M.MediaSectionID=3 AND Privacy IN (".implode(',', $is_relation).")) is not null ";

            $part1 = " LEFT JOIN `Activity` `A` ON A.Params LIKE '%M.MediaGUID%' AND A.StatusID NOT IN (3,19) ";
            $part2 = " LEFT JOIN `Activity` `A` ON A.ActivityID=M.MediaSectionReferenceID AND M.MediaSectionID='3' AND A.StatusID NOT IN (3,19)";
            $part3 = " LEFT JOIN `Activity` `A` ON A.ActivityID=AL.ActivityID AND A.StatusID NOT IN (3,19) ";

            $query1 = $query.$part1.$where;
            $query2 = $query.$part2.$where;
            $query3 = $query.$part3.$where.$where_3;

            $sql = "
                SELECT MediaType,ModuleID,UserGUID,FirstName,LastName,ProfilePicture, ProfileURL, ConversionStatus, AlbumID, MediaGUID, MediaID, ImageName, Caption, MediaSectionID, MediaSectionReferenceID, CreatedDate,ActivityGUID,Params, ANoOfComments, ANoOfLikes, ANoOfShares,MNoOfComments, MNoOfLikes, MNoOfShares, UserID, Flaggable, IsCommentable, MediaFolder  FROM (
                    ".$query1."
                    UNION ALL
                    ".$query2."
                    UNION ALL
                    ".$query3."
                ) tbl WHERE MediaType IN('Image','Video')
                GROUP BY MediaGUID
                ORDER BY CreatedDate DESC";



        $query = $this->db->query($sql);
        //echo $this->db->last_query();die;
        if($query->num_rows())
        {
            $result['TotalRecords'] = $query->num_rows();

            if ($page_size) { // Check for pagination
                $offset = $this->get_pagination_offset($page_no, $page_size);
                $sql.= " LIMIT ".$offset.",".$page_size." ";
            }
            $query1 = $this->db->query($sql); 
            $media_list = array();
            foreach($query1->result_array() as $media)
            {
                $params = json_decode($media['Params'], true);
                if($params['count'] == 1)
                {
                    $media['NoOfComments']  = $media['ANoOfComments'];
                    $media['NoOfLikes']     = $media['ANoOfLikes'];
                    $media['NoOfShares']    = $media['ANoOfShares'];
                    $media['IsLike']        = $this->activity_model->checkLike($media['ActivityGUID'], 'Activity', $current_user, 0);
                }
                else
                {
                    $media['NoOfComments']  = $media['MNoOfComments'];
                    $media['NoOfLikes']     = $media['MNoOfLikes'];
                    $media['NoOfShares']    = $media['MNoOfShares'];
                    $media['IsLike']        = $this->activity_model->checkLike($media['MediaGUID'], 'Media', $current_user, 0);
                }
                
                unset($media['ANoOfComments']);
                unset($media['ANoOfLikes']);
                unset($media['ANoOfShares']);
                unset($media['MNoOfComments']);
                unset($media['MNoOfLikes']);
                unset($media['MNoOfShares']);

                $media['ShareAllowed']  = ($media['MediaSectionID'] == '1' || $media['MediaSectionID'] == '5') ? 0 : 1 ;
                $media_list[] = $media;
            }
            $result['MediaList'] = $media_list;
        }
        return $result;
    }

    function getEventMediaCount($module_id,$module_entity_id)
    {
        
        $module_entity_id = $this->db->escape_str($module_entity_id);
        $module_id = $this->db->escape_str($module_id);
        
        $is_relation = array(1, 2, 3, 4);
        $query =  "
            SELECT `MT`.`Name` as `MediaType`, `M`.`ModuleID`, U.UserGUID, U.FirstName, U.LastName, IF(U.ProfilePicture='', 'user_default.jpg', U.ProfilePicture) as ProfilePicture, PU.Url as ProfileURL, `M`.`ConversionStatus`, `M`.`AlbumID`, `M`.`MediaGUID`, `M`.`MediaID`, `M`.`ImageName`, `M`.`Caption`, `M`.`MediaSectionID`, `M`.`MediaSectionReferenceID`, `M`.`CreatedDate`, `M`.`NoOfComments`, `M`.`NoOfLikes`, `M`.`NoOfShares`, `M`.`UserID`, `M`.`Flaggable`, `M`.`IsCommentable`, `MS`.`MediaSectionAlias` as `MediaFolder`
            FROM `Media` `M`
            LEFT JOIN `MediaExtensions` `ME` ON `ME`.`MediaExtensionID`=`M`.`MediaExtensionID`
            LEFT JOIN `MediaTypes` `MT` ON `MT`.`MediaTypeID`=`ME`.`MediaTypeID`
            LEFT JOIN `MediaSections` `MS` ON `MS`.`MediaSectionID`=`M`.`MediaSectionID`
            LEFT JOIN `Users` `U` ON `U`.`UserID`=`M`.`UserID`
            LEFT JOIN `Albums` `AL` ON `AL`.`AlbumID`=`M`.`AlbumID`
            JOIN `ProfileUrl` `PU` ON `PU`.`EntityID`=`U`.`UserID` AND `PU`.`EntityType`='User'
            ";
            $where = " WHERE  `M`.`StatusID` = '2' ";

            if($module_id!='3')
            {
                $where .= " AND A.ModuleID=".$module_id."";
            }
            if($module_id=='3')
            {
                if($is_relation)
                {
                    $where .= " AND A.Privacy IN (".implode(',', $is_relation).") AND M.UserID=".$module_entity_id."";
                }
            }
            else
            {
                $where .= " AND A.ModuleID=".$module_id." AND A.ModuleEntityID=".$module_entity_id."";
            }

            $where_3 = " AND (SELECT Privacy FROM ".ACTIVITY." WHERE ActivityID=M.MediaSectionReferenceID AND M.MediaSectionID=3 AND Privacy IN (".implode(',', $is_relation).")) is not null ";

            $part1 = " LEFT JOIN `Activity` `A` ON A.Params LIKE '%M.MediaGUID%' AND A.StatusID NOT IN (3,19) ";
            $part2 = " LEFT JOIN `Activity` `A` ON A.ActivityID=M.MediaSectionReferenceID AND M.MediaSectionID='3' AND A.StatusID NOT IN (3,19)";
            $part3 = " LEFT JOIN `Activity` `A` ON A.ActivityID=AL.ActivityID AND A.StatusID NOT IN (3,19) ";

            $query1 = $query.$part1.$where;
            $query2 = $query.$part2.$where;
            $query3 = $query.$part3.$where.$where_3;

            $sql = "
                SELECT MediaType,ModuleID,UserGUID,FirstName,LastName,ProfilePicture, ProfileURL, ConversionStatus, AlbumID, MediaGUID, MediaID, ImageName, Caption, MediaSectionID, MediaSectionReferenceID, CreatedDate, NoOfComments, NoOfLikes, NoOfShares, UserID, Flaggable, IsCommentable, MediaFolder  FROM (
                    ".$query1."
                    UNION ALL
                    ".$query2."
                    UNION ALL
                    ".$query3."
                ) tbl WHERE MediaType IN('Image','Video')
                GROUP BY MediaGUID
                ORDER BY CreatedDate DESC";

        $query = $this->db->query($sql);
        return $query->num_rows();
    }



    function get_media_guid($image_name)
    {
        $this->db->select('MediaGUID');
        $this->db->from(MEDIA);
        $this->db->where('ImageName',$image_name);
        $query = $this->db->get();
        if($query->num_rows())
        {
            return $query->row()->MediaGUID;
        }
        else
        {
            return '';
        }
    }

    function get_activity_id_by_media_guid($media_guid)
    {
        $this->db->select('MediaSectionReferenceID');
        $this->db->from(MEDIA);
        $this->db->where('MediaSectionID',3);
        $this->db->where('MediaGUID',$media_guid);
        $query = $this->db->get();
        if($query->num_rows())
        {
            return $query->row()->MediaSectionReferenceID;
        }
        else
        {
            return 0;
        }
    }
    
       function get_media_section_details($media_guid)
       {
           $this->db->select('MediaSectionID,MediaSectionReferenceID');
           $this->db->from(MEDIA);
           $this->db->where('MediaGUID',$media_guid);
           $query = $this->db->get();
           if($query->num_rows())
           {
               return $query->row_array();
           }
           return 0;
       }
    
    /**
     * [get_media_details description]
     * @param  [int] $user_id    	[User ID]
     * @param  [string] $media_guid [Media GUID]
     * @param  [string] $paging     [Paging value Next/Previous]
     * @param  [string] $type       [Used to get details from an album or activity]
     * @return [array]             	[Media details]
     */
    function get_media_details($user_id, $media_guid, $paging='', $type='Any',$activity_id=0)
    {
    	$data = array();
    	$this->load->model(array('favourite_model','flag_model','activity/activity_model','users/user_model'));
    	/* Start */
    	$get_album = $this->db->select('AlbumID')->from(MEDIA)->where('MediaGUID',$media_guid)->get();
    	$media_section_details = $this->get_media_section_details($media_guid);
    	$old_media_guid     = $media_guid;
        $mguid              = $media_guid;
        $album_id           = 0;        
        $media_count        = 0;   
        $data['MediaIndex'] = 1;
        $is_wall_album      = FALSE;
    	if($get_album->num_rows()){
    		$album = $get_album->row_array();
    		$album_id = $album['AlbumID'];
    	}
        $data['Album'] = array('ActivityGUID'=>$activity_id,'AlbumName'=>'','AlbumGUID'=>'','AlbumType'=>'PHOTO','MediaCount'=> 0, 'Owner'=>array());
        
        if($album_id)
        {
            $album_detail = get_detail_by_id($album_id, 13, "AlbumType, AlbumName, AlbumGUID, ModuleID, ModuleEntityID, UserID", 2);
            
            if($album_detail['ModuleID'] == 3 && $album_detail['AlbumName'] == DEFAULT_WALL_ALBUM)
            {
                $module_entity_id = $album_detail['ModuleEntityID'];
                $is_relation = $this->activity_model->isRelation($module_entity_id, $user_id, true);
                $is_wall_album = TRUE;
            }
            $data['Album'] = array('AlbumName'=>$album_detail['AlbumName'],'AlbumGUID'=>$album_detail['AlbumGUID'],'AlbumType'=>$album_detail['AlbumType'],'MediaCount'=>$media_count);   
            $data['Album']['Owner']  = $this->user_model->getUserName($album_detail['UserID'],$album_detail['ModuleID'],$album_detail['ModuleEntityID']);
            $this->db->where(array('M.AlbumID'=>$album_id));
        }
        else if($media_section_details['MediaSectionID']=='6')
        {
            $this->db->where('M.MediaSectionReferenceID',$media_section_details['MediaSectionReferenceID']);
            $this->db->join(MEDIAEXTENSIONS.' ME','M.MediaExtensionID=ME.MediaExtensionID','left');
            $this->db->where_in('ME.MediaTypeID',array(1,2));
        }
        else if(!empty($media_guid))
        {
            $this->db->where(array('M.MediaGUID'=>$media_guid));
        }        

        $this->db->select('M.MediaSectionID,M.Resolution,M.MediaGUID,M.MediaID, M.UserID', FALSE);
        if($is_wall_album)
        {
            $this->db->join(ACTIVITY.' ACT','ACT.ActivityID = M.MediaSectionReferenceID AND M.MediaSectionID=3','left');
            $this->db->where_in('ACT.Privacy',$is_relation);
        }
        if($activity_id)
        {
            $this->db->where('M.MediaSectionID','3');
            $this->db->where('M.MediaSectionReferenceID',$activity_id);
        }
        $this->db->where_in('M.StatusID',array('2','10'));
        $this->db->order_by('M.CreatedDate','DESC');
        $mq = $this->db->get(MEDIA.' M');
        $media_count = $mq->num_rows();
        //echo $this->db->last_query();
        $data['Album']['MediaCount'] = $media_count;
        if($media_count > 1){
			$data['PrevMediaGUID'] = '';
			$data['NextMediaGUID'] = '';
			$data['Source'] = 'Album';			
			$IsMediaGUID = 0;
			$i=0;
			$all_media_guid = array();
            foreach ($mq->result() as $m) {
                /*if($m->MediaSectionID == 3)
                {
    				$get_visibility = $this->get_media_privacy($m->MediaGUID);
                    $get_relationship = $this->activity_model->isRelation($m->UserID,$user_id,true);
                    if(in_array($get_visibility,$get_relationship)){
                        $i++;
    				    $all_media_guid[] = $m->MediaGUID;
                        //echo $i.' - ';
                    }
                }
                else
                {
                    $i++;
                    $all_media_guid[] = $m->MediaGUID;
                }*/
                $all_media_guid[] = $m->MediaGUID;
                $i++;

                //echo $i.' - ';
				if($IsMediaGUID==1){
					$IsMediaGUID = 2;
					$data['NextMediaGUID'] = $m->MediaGUID;
				}
				if($m->MediaGUID == $media_guid){
					$IsMediaGUID = 1;
					$data['MediaIndex'] = $i;
				}
				if($IsMediaGUID==0){
					$data['PrevMediaGUID'] = $m->MediaGUID;	
				}
			}
			//echo $paging.' '.$data['NextMediaGUID'];
			if($paging == 'Next'){
				if($data['NextMediaGUID']){
					$mguid = $data['NextMediaGUID'];
				} else {
					if($all_media_guid){
						$mguid = $all_media_guid[0];
					}
				}
			} else if($paging == 'Prev'){
				if($data['PrevMediaGUID']){
					$mguid = $data['PrevMediaGUID'];
				} else {
					if($all_media_guid){
						$mguid = $all_media_guid[count($all_media_guid)-1];
					}
				}
			} else {
				$mguid = $media_guid;
			}
			$data['MediaIndex'] = array_search($mguid, $all_media_guid);
			$data['MediaIndex'] = $data['MediaIndex']+1;
            
            $data['PrevImgName'] = $this->get_filename_by_guid($data['PrevMediaGUID']);
            $data['NextImgName'] = $this->get_filename_by_guid($data['NextMediaGUID']);

            //unset($data['PrevMediaGUID']);
            //unset($data['NextMediaGUID']);            
		}
    	/* End */
        $this->db->select('MT.Name as MediaType,M.ModuleID');
        /*if($album_id)
        {
    	   $this->db->select('A.AlbumType,A.AlbumName,A.AlbumGUID,A.ModuleID,A.ModuleEntityID');
           
        } 
        else
        {
            $this->db->select('"" as AlbumType,"" as AlbumName,"" as AlbumGUID, "3" as ModuleID,"0" as ModuleEntityID',false);
            
        }*/

        $this->db->select('U.UserGUID, U.FirstName, U.LastName, U.ProfilePicture',FALSE);

        $this->db->select('PU.Url as ProfileURL',FALSE);

        $this->db->select('M.AlbumID,M.Resolution,M.MediaID,M.ImageName,M.Caption,M.MediaSectionID,M.MediaSectionReferenceID,M.CreatedDate, M.NoOfComments,M.NoOfLikes,M.NoOfShares,M.UserID, M.Flaggable,M.IsCommentable');
        $this->db->select('MS.MediaSectionAlias as MediaFolder');
        
        $this->db->from(MEDIA.' M');
        /*if($album_id)
        {
    	   $this->db->join(ALBUMS.' A','M.AlbumID=A.AlbumID');
        }*/
    	$this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID=M.MediaSectionID', 'LEFT');
        $this->db->join(USERS.' U','U.UserID=M.UserID','left');

        $this->db->join(PROFILEURL.' PU', "PU.EntityID=U.UserID AND PU.EntityType='User'");

        $this->db->where('M.MediaGUID',$mguid);
        $this->db->where_in('M.StatusID',array('2','10'));
        $this->db->order_by('M.CreatedDate','DESC');
        $query = $this->db->get();
        if($query->num_rows()){
        	$media = $query->row_array();
	    	
	    	$IsMedia = 1;
	    	if($media['MediaSectionID'] == 3){
	    		$countQ = $this->db->where(array('MediaSectionID'=>$media['MediaSectionID'],'MediaSectionReferenceID'=>$media['MediaSectionReferenceID'], 'StatusID'=>2))->order_by('CreatedDate','DESC')->get(MEDIA);
	    		$media_count = $countQ->num_rows();
	    		$this->db->select('Privacy as Visibility',false);
    			$this->db->select('A.PostContent,A.ActivityGUID,A.ModuleEntityOwner,A.ActivityID,A.CreatedDate,A.NoOfComments,A.NoOfLikes,A.NoOfShares,A.UserID,A.IsCommentable,A.Flaggable');
    			$this->db->select('ATY.FlagAllowed');
    			$this->db->from(ACTIVITY.' A');
    			$this->db->join(ACTIVITYTYPE.' ATY','A.ActivityTypeID=ATY.ActivityTypeID','left');
    			$this->db->where('A.ActivityID',$media['MediaSectionReferenceID']);
    			$actQ = $this->db->get();
    			if($actQ->num_rows()){
    				$activity = $actQ->row_array();
    			}
	    		if($media_count==1){
	    			$IsMedia = 0;
	    		}
	    	}

	    	$data['MediaGUID'] 				= $mguid;
	    	$data['ImageName'] 				= $media['ImageName'];
                $data['Resolution'] 				= $media['Resolution'];
	    	$data['Caption'] 				= $media['Caption'];
            $data['MediaType']              = $media['MediaType'];
	    	$data['ModuleID'] 				= $media['ModuleID'];
            $data['ShareAllowed']           = ($media['MediaSectionID'] == '1' || $media['MediaSectionID'] == '5') ? 0 : 1 ;

	    	//$data['UserName'] 				= $media['UserName'];
            //$data['UserProfilePicture']     = $media['UserProfilePicture'];

            $data['CreatedBy']                  = array('UserGUID'=>$media['UserGUID'],'FirstName'=>$media['FirstName'],'LastName'=>$media['LastName'],'ProfilePicture'=>$media['ProfilePicture'],'ProfileURL'=>$media['ProfileURL']);

	    	$data['MediaFolder'] 	        = $media['MediaFolder'];
	    	//$data['Album'] 					= array('AlbumName'=>$media['AlbumName'],'AlbumGUID'=>$media['AlbumGUID'],'AlbumType'=>$media['AlbumType'],'MediaCount'=>$media_count);
		
            //$data['Album']['Owner']         = $this->user_model->getUserName($media['UserID'],$media['ModuleID'],$media['ModuleEntityID']);
            if(isset($activity) && !empty($activity)){
                $data['Visibility'] 			= $activity['Visibility'];
    		    $data['Flaggable'] 				= $activity['Flaggable'];
    		    $data['FlagAllowed'] 			= $activity['FlagAllowed'];
    		    $data['IsCommentable'] 			= $activity['IsCommentable'];
                if(isset($activity['ModuleID']) && $activity['ModuleID']== 3)
                {
                    $data['ShowPrivacy']            = 1;
                }
            } else {

                $data['Visibility']             = 1;
                $data['Flaggable']              = 1;
                $data['FlagAllowed']            = 1;
                $data['ShowPrivacy']            = 0;
            }

            if($media['MediaSectionID'] == '1' || $media['MediaSectionID'] == '5')
            {
                $data['ShowPrivacy']            = 0;
            }
            else
            {
                $data['ShowPrivacy']            = 1;
            }

            $data['IsCommentable'] = $media['IsCommentable'];
            
            // Privacy Check
            $privacy_status = $this->activity_model->isRelation($media['UserID'],$user_id,true);
            if(!in_array($data['Visibility'],$privacy_status)){
                if($mguid != $old_media_guid)
                {
                    return $this->get_media_details($user_id,$mguid,$paging,$type);
                }
            }
            //mediaDetails.IsOwner=='1' && mediaDetails.ShowPrivacy=='1'

	    	if(!$IsMedia){
	    		$data['CreatedDate'] 	= $activity['CreatedDate'];
		    	$data['NoOfComments'] 	= $activity['NoOfComments'];
		    	$data['NoOfLikes'] 		= $activity['NoOfLikes'];
		    	$data['NoOfShares'] 	= $activity['NoOfShares'];
		    	$data['PostContent'] 	= $activity['PostContent'];
		    	$data['IsOwner'] 		= 0;
                        $data['IsFlagged'] 		= 0;
                //$data['IsFavourite']    = $this->favourite_model->is_favourite($activity['ActivityID'],$user_id);
		    	$data['IsFavourite'] 	= 0;
		    	if($user_id == $activity['UserID']){
		    		$data['IsOwner'] = 1;
		    	}
		    	if($activity['ModuleEntityOwner'] != 0) {
                    $data['LikeName']               = $this->activity_model->getLikeName($activity['ActivityID'], $user_id, $activity['ModuleEntityOwner']);
                } else {
                    $data['LikeName']               = $this->activity_model->getLikeName($activity['ActivityID'], $user_id, 0);  
                }
		    	$data['IsLike'] 		= $this->activity_model->checkLike($activity['ActivityGUID'], 'Activity', $user_id, $activity['ModuleEntityOwner']);
		    	//$data['Comments'] 		= $this->activity_model->getActivityComments('Activity', $activity['ActivityID'], '1', 10, $user_id, $data['IsOwner'], 2, TRUE);
		    	if($this->flag_model->is_flagged($user_id, $activity['ActivityID'], 'Activity')){
                    $data['IsFlagged'] = 1;
                }
                $data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id,'Activity',$activity['ActivityID']);
	    	} else {
		    	$data['CreatedDate'] 	= $media['CreatedDate'];
		    	$data['NoOfComments'] 	= $media['NoOfComments'];
		    	$data['NoOfLikes'] 		= $media['NoOfLikes'];
		    	$data['NoOfShares'] 	= $media['NoOfShares'];
		    	$data['PostContent'] 	= $media['Caption'];
                $data['Flaggable']      = $media['Flaggable'];
		    	$data['IsOwner'] 		= 0;
                $data['IsFlagged'] 		= 0;
		    	$data['IsFavourite'] 	= 0;
		    	$data['IsLike'] 		= $this->activity_model->checkLike($mguid, 'Media', $user_id, 0);
		    	$data['LikeName'] 		= $this->activity_model->getLikeName($media['MediaID'], $user_id, 0,array(),'Media');
	    		if($user_id == $media['UserID']){
		    		$data['IsOwner'] = 1;
		    	}
		    	//$data['Comments'] 		= $this->activity_model->getActivityComments('Media', $media['MediaID'], '1', 10, $user_id, $data['IsOwner'], 2, TRUE);
                

                $this->load->model(array('admin/media_model'));
                if($this->check_abuse_status($media['MediaID'], $user_id)){
                    $data['IsFlagged'] = 1;
                }
                $data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id,'Media',$media['MediaID']);

                $this->notification_model->mark_notifications_as_read($user_id, $media['MediaID'], 'MEDIA');
            }
        }
        if(isset($data['PostContent']))
        {
            $data['PostContent'] = $this->activity_model->parse_tag_html($data['PostContent']);
        }

	    return $data;
    }
    /**
     * [add_comment Used to post comment for an media]
     * @param [int] 	$user_id    [User ID]
     * @param [string] 	$media_guid [Media GUID]
     * @param [string] 	$comment    [Comment Text]
     * @param array        [Comment Details]
     */
    function add_comment($user_id,$media_guid,$comment,$media=array())
    {
    	$data = $this->get_media_entity($media_guid);
        extract($data);
        return $this->activity_model->addComment($entity_guid, $comment, $media, $user_id, count($media), 1, 1, 0, $entity_type,3,0);
    }

    /**
     * [toggle_like Used to like/unlike an media]
     * @param  [int] 	$user_id    [User ID]
     * @param  [string] $media_guid [Media GUID]
     * @return [array]             	[Like Details]
     */
    function toggle_like($user_id,$media_guid)
    {
    	$entity = $this->get_media_entity($media_guid);
    	$data = array('EntityType'=>$entity['entity_type'],'EntityGUID'=>$entity['entity_guid'],'UserID'=>$user_id);
    	return $this->activity_model->toggleLike($data);
    }

    function get_filepath_by_guid($media_guid){
        $this->db->select('M.ImageName,MS.MediaSectionAlias');
        $this->db->from(MEDIA.' M');
        $this->db->join(MEDIASECTIONS.' MS','M.MediaSectionID=MS.MediaSectionID','left');
        $this->db->where('M.MediaGUID',$media_guid);
        $query = $this->db->get();
        //echo $this->db->last_query();
        if($query->num_rows()){
            $row = $query->row();
            return IMAGE_SERVER_PATH.'upload/'.$row->MediaSectionAlias.'/'.$row->ImageName;
        } else {
            return '';
        }
    }

    function get_filename_by_guid($media_guid){
        $this->db->select('ImageName');
        $this->db->from(MEDIA);
        $this->db->where('MediaGUID',$media_guid);
        $query = $this->db->get();
        if($query->num_rows()){
            return $query->row()->ImageName;
        } else {
            return '';
        }
    }

    /**
     * [like_details Used to get media like user details]
     * @param  [int]    $user_id    [User ID]
     * @param  [string] $media_guid [Media GUID]
     * @param  [int]    $page_no    [Page Number]
     * @param  [int]    $page_size  [Page Size]
     * @return [array]              [like user details]
     */
    function like_details($user_id,$media_guid,$page_no,$page_size)
    {
    	$data = $this->get_media_entity($media_guid);
    	extract($data);
    	$data = $this->activity_model->getLikeDetails($entity_guid, $entity_type, array(),$page_no, $page_size);
        $total_records = $this->activity_model->getLikeDetails($entity_guid, $entity_type, array(),$page_no, $page_size,true);
        return array('data'=>$data,'total_records'=>$total_records);
    }

    /**
     * [flag Used to flag media]
     * @param  [int]    $user_id    [User ID]
     * @param  [string] $media_guid [Media GUID]
     * @param  [string] $flag_reason [Flag Reason]
     * @return [array]              [Success Message]
     */
    function flag($user_id,$media_guid,$flag_reason)
    {
    	$this->load->model('flag_model');
    	$data          = $this->get_media_entity($media_guid);
        $entity_type   = $data['entity_type'];
        if($entity_type == "MEDIA") 
        {
            /* Check if this entiy is Flaggable or not */ 
            $Flaggable = $data['Flaggable'];       
            if(empty($Flaggable)){
                $return['ResponseCode'] = 412;
                $return['Message'] = lang('flaggable');
                return $return;
            }

            $return['Message']      = lang('success');
            $return['ResponseCode'] = 200;
            if(!$this->check_abuse_status($data['MediaID'], $user_id)){
                //For insert abuse record in MediaAbuse table
                $mediaData = array();
                $mediaData['UserID'] = $user_id;
                $mediaData['MediaID'] = $data['MediaID'];
                $mediaData['Description'] = $flag_reason;
                $mediaData['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $this->mark_as_abuse($mediaData);
            }
            return $return;
        } 
        else
        {
            $d = array('EntityType'=>$data['entity_type'],'EntityGUID'=>$data['entity_guid'],'FlagReason'=>$flag_reason,'UserID'=>$user_id);

            return $this->flag_model->set_flag($d);    
        }
    	
    }

    function comments($user_id, $media_guid, $page_no,$page_size, $count_only=FALSE)
    {
    	$data = $this->get_media_entity($media_guid,1);
    	if($data['entity_type'] == 'MEDIA'){
    		$entity_id = get_detail_by_guid($data['entity_guid'],21);
    	} else {
    		$entity_id = get_detail_by_guid($data['entity_guid']);
    	}
    	$is_owner = 0;
    	if($data['user_id'] == $user_id){
    		$is_owner = 1;
    	}
        if($count_only) {
            return $this->activity_model->getActivityComments($data['entity_type'], $entity_id, $page_no, $page_size, $user_id, $is_owner, 2, TRUE,array(),TRUE,'',3,0);
        }  
    	return $this->activity_model->getActivityComments($data['entity_type'], $entity_id, $page_no, $page_size, $user_id, $is_owner, 2, TRUE,array(),FALSE,'',3,0);
    }

    /**
     * [toggle_subscribe Used to subscribe/unsubscribe for media updates]
     * @param  [int]    $user_id    [User ID]
     * @param  [string] $media_guid [Media GUID]
     * @return [int]              [Subscribed flag]
     */
    function toggle_subscribe($user_id,$media_guid)
    {
    	$this->load->model('subscribe_model');
    	$data = $this->get_media_entity($media_guid);
    	if($data['entity_type'] == 'MEDIA'){
    		$entity_id = get_detail_by_guid($data['entity_guid'],21);
    	} else {
    		$entity_id = get_detail_by_guid($data['entity_guid']);
    	}
    	return $this->subscribe_model->toggle_subscribe($user_id,$data['entity_type'],$entity_id);
    }

    /**
     * [delete Used to mark media as deleted]
     * @param  [type] $user_id    [User ID]
     * @param  [type] $media_guid [Media GUID]
     */
    function delete($user_id,$media_guid)
    {
        $entity_details = $this->get_media_entity($media_guid,0,1);
        
        $profile_pic = $this->session->userdata('ProfilePicture');
        if($profile_pic)
        {
            $this->db->select('MediaGUID');
            $this->db->from(MEDIA);
            $this->db->where('ImageName',$profile_pic);
            $query = $this->db->get();
            if($query->num_rows())
            {
                $row = $query->row();
                if($row->MediaGUID == $media_guid)
                {
                    $this->session->set_userdata('ProfilePicture','');
                }
            }
        }

        if($entity_details['MediaSectionID'] == 1 || $entity_details['MediaSectionID'] == 5)
        {
            if($entity_details['MediaSectionID'] == 1)
            {
                $activity_type_id = '23';
            }
            else
            {
                $activity_type_id = '24';
            }
            
            $media_guid = $this->db->escape_like_str($media_guid); 
            
            $this->db->set('StatusID','3');
            $this->db->where("Params LIKE '%".$media_guid."%'",null,false);
            $this->db->where('ActivityTypeID',$activity_type_id);
            $this->db->update(ACTIVITY);
        }

        $this->db->set('StatusID','3');
        $this->db->where('MediaGUID',$media_guid);
        $this->db->update(MEDIA);

        if($entity_details['entity_type'] == 'ACTIVITY'){
            $this->db->set('StatusID','3');
            $this->db->where('ActivityGUID',$entity_details['entity_guid']);
            $this->db->update(ACTIVITY);
        } else {
            $activity_query = $this->db->get_where(ACTIVITY,array('ActivityGUID'=>$entity_details['entity_guid']));
            if($activity_query->num_rows()){
                $activity_data = $activity_query->row_array();
                if($activity_data['Params']){
                    $params = json_decode($activity_data['Params']);
                    if($params['count']){
                        $params['count'] = $params['count']-1;
                        $params = json_encode($params);
                        $this->db->set('Params',$params);
                        $this->db->where('ActivityGUID',$entity_details['entity_guid']);
                        $this->db->update(ACTIVITY);
                    }
                }
            }
        }
        $this->db->set('MediaCount', 'MediaCount-1', FALSE);
        $this->db->where('AlbumID',$entity_details['album_id']);
        $this->db->update(ALBUMS);
        return $entity_details['album_id'];
    }

    function privacy($user_id,$media_guid,$privacy){
        $this->db->select('MediaSectionID,MediaSectionReferenceID');
        $this->db->from(MEDIA);
        $this->db->where('MediaGUID',$media_guid);
        $result = $this->db->get();
        if($result->num_rows()){
            $row = $result->row();
            if($row->MediaSectionID == 3){
                $this->db->set('Privacy',$privacy);
                $this->db->where('ActivityID',$row->MediaSectionReferenceID);
                $this->db->update(ACTIVITY);
            }
            
            $media_guid = $this->db->escape_like_str($media_guid); 
            
            if($row->MediaSectionID == 1)
            {
                $this->db->set('Privacy',$privacy);
                $this->db->where("Params LIKE '%".$media_guid."%'",null,false);
                $this->db->where('ActivityTypeID',23);
                $this->db->update(ACTIVITY);
            }

            if($row->MediaSectionID == 5)
            {
                $this->db->set('Privacy',$privacy);
                $this->db->where("Params LIKE '%".$media_guid."%'",null,false);
                $this->db->where('ActivityTypeID',24);
                $this->db->update(ACTIVITY);
            }
        }
    }

    function created_by($user_id){
        $this->db->select("CONCAT(U.FirstName,' ',U.LastName) as EntityName,U.UserGUID as EntityGUID,IF(U.ProfilePicture='','user_default.jpg',U.ProfilePicture) as ProfilePicture,P.Url as ProfileURL, '3' as ModuleID",false);
        $this->db->from(USERS.' U');
        $this->db->join(PROFILEURL.' P','U.UserID=P.EntityID','left');
        $this->db->where('P.EntityType','User');
        $this->db->where('U.UserID',$user_id);
        $query = $this->db->get();
        if($query->num_rows()){
            return $query->row_array();
        } else {
            return array();
        }
    }

    function share_details($media_guid){
        $post_content = '';
        $media = array();
        $created_by = array();

        $this->db->select('M.ImageName,M.Caption,M.Resolution');
        $this->db->select('MS.MediaSectionAlias as MediaFolder');
        $this->db->from(MEDIA.' M');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID=M.MediaSectionID', 'LEFT');
        $this->db->where('M.MediaGUID',$media_guid);
        $media_query = $this->db->get();
        if($media_query->num_rows()){
            $media = $media_query->row_array();
        }
        $entity_details = $this->get_media_entity($media_guid);
        if($entity_details['entity_type'] == 'ACTIVITY'){
            $activity_query = $this->db->get_where(ACTIVITY,array('ActivityGUID'=>$entity_details['entity_guid']));
            if($activity_query->num_rows()){
                $activity_data = $activity_query->row_array();
                $post_content = $activity_data['PostContent'];
                $created_by = $this->created_by($activity_data['UserID']);
            }
        } else {
            $media_query = $this->db->get_where(MEDIA,array('MediaGUID'=>$entity_details['entity_guid']));
            if($media_query->num_rows()){
                $media_data = $media_query->row_array();
                $post_content = $media_data['Caption'];
                $created_by = $this->created_by($media_data['UserID']);
            }
        }
        $data = array('EntityType'=>$entity_details['entity_type'],'EntityGUID'=>$entity_details['entity_guid'],'Media'=>$media,'PostContent'=>$post_content,'CreatedBy'=>$created_by);
        return $data;
    }

    public function get_media_privacy($media_guid){
        $entity_details = $this->get_media_entity($media_guid);
        if($entity_details['entity_type'] == 'ACTIVITY'){
            $query = $this->db->select('Privacy as Visibility')->from(ACTIVITY)->where('ActivityGUID',$entity_details['entity_guid'])->get();
        } else {
            if($entity_details['entity_type'] == 'MEDIA'){
                $query = $this->db->select('A.Visibility')->from(ALBUMS.' A')->join(MEDIA.' M','M.AlbumID=A.AlbumID','left')->where('M.MediaGUID',$entity_details['entity_guid'])->get();
            }
        }
        if($query->num_rows()){
            return $query->row()->Visibility;
        }
    }

    public function get_media_owner_details($media_id){
        $Name = '';
        $user_id = '';
        $this->db->select('U.FirstName,U.LastName,U.UserID');
        $this->db->from(USERS.' U');
        $this->db->join(MEDIA.' M','M.UserID=U.UserID','left');
        $this->db->where('M.MediaID',$media_id);
        $query = $this->db->get();
        if($query->num_rows()){
            $row = $query->row();
            $user_id = $row->UserID;
            $Name = $row->FirstName.' '.$row->LastName;            
        }
        return array('Name' => $Name, 'UserID' => $user_id);
    }

    function get_media_entity($media_guid,$user=0,$album=0)
    {
        $return = array('entity_type'=>'MEDIA','entity_guid'=>$media_guid);
        $this->db->select('M.MediaID, M.Resolution, M.Flaggable, M.MediaSectionID,M.MediaSectionReferenceID,M.UserID,M.AlbumID');
        $this->db->where('M.MediaGUID',$media_guid);
        $this->db->limit(1);
        $query = $this->db->get(MEDIA.' M');
        if($query->num_rows()){
            $row = $query->row_array();
            $count_query = $this->db->get_where(MEDIA,array('MediaSectionID'=>$row['MediaSectionID'],'MediaSectionReferenceID'=>$row['MediaSectionReferenceID'], "StatusID"=>2));
            $media_count = $count_query->num_rows();
            if($media_count==1 && $row['MediaSectionID']==3){
                $return = array('entity_type'=>'ACTIVITY','entity_guid'=>get_detail_by_id($row['MediaSectionReferenceID'],0,'ActivityGUID',1));
            }

            $return['MediaID']      = $row['MediaID'];
            $return['Resolution']      = $row['Resolution'];
            $return['Flaggable']      = $row['Flaggable'];
            $return['MediaSectionID'] = $row['MediaSectionID'];
            $return['MediaSectionReferenceID'] = $row['MediaSectionReferenceID'];

            if($user){
                $return['user_id']  = $row['UserID'];
            }
            if($album){
                $return['album_id'] = $row['AlbumID'];
            }
        }
        return $return;
    }

    function check_permission($user_id,$media_guid){
        $this->db->select('UserID');
        $this->db->from(MEDIA);
        $this->db->where('MediaGUID',$media_guid);
        $this->db->where('StatusID!=3',NULL,FALSE);
        $query = $this->db->get();
        if($query->num_rows()){
            $row = $query->row_array();
            if($row['UserID'] == $user_id){
                return 1; // Have Permission
            } else {
               return 2; // Permission for access only
            }
        } else {
            return 0; // Media not exists
        }
    }

    /**
     * Function for check Media already mark abuse by user
     * @param integer $MediaID
     * @param integer $user_id
     * @return string
     */
    function check_abuse_status($MediaID,$user_id){
        
        $this->db->select('*');
        $this->db->from(MEDIAABUSE);
        $this->db->where('MediaID',$MediaID);
        $this->db->where('UserID',$user_id);
                
        $query = $this->db->get();
        if ($query->num_rows()) {
            return TRUE;
        } else {
            return FALSE;
        }
    } 

    /**
     * Function for mark media as abuse from frontend website
     * @param array $dataArr
     * @return integer
     */
    function mark_as_abuse($dataArr){
        
        $this->db->insert(MEDIAABUSE, $dataArr);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    /**
     * [updateMedia Used to update media info in database]
     * @param [array] $data [media info]
     */
    function updateMedia($Media, $entity_id, $user_id=0, $AlbumID=0, $deleteRemaining=false, $MediaSectionID=0,$status=FALSE,$commentable=1){
        $update = array();
        //$AlbumID = 0;     
        if($Media){       

            $StatusID = 2;
            if($status==10)
            {
                $StatusID = 10;
            }  
            //$AlbumID = get_album_id($user_id, $AlbumName, $module_id, $module_entity_id);
            $Count = 0 ;
            $media_guids = array();
            foreach($Media as $m){                
                if(isset($m['MediaGUID']) && !empty($m['MediaGUID']))
                {
                    $media_guids[] = $m['MediaGUID'];
                    $u = array('UserID'=>$user_id,'MediaGUID'=>$m['MediaGUID'],'MediaSectionReferenceID'=>$entity_id,'StatusID'=>$StatusID,'Caption'=>isset($m['Caption']) ? $m['Caption'] : '','AlbumID'=> $AlbumID,'IsCommentable'=>$commentable);
                    $update[] = $u;                    
                }
                ++$Count;               
            }
            
            if($deleteRemaining){   
                $this->db->set('StatusID','3');
                $this->db->where('MediaSectionID',$MediaSectionID);
                $this->db->where('MediaSectionReferenceID',$entity_id);
                $this->db->where_not_in('MediaGUID',$media_guids);
                $this->db->update(MEDIA);
            }

                     
            if(!empty($AlbumID) && $Count > 0) {
                $Album = get_detail_by_id($AlbumID, 13, "MediaID, AlbumName", 2);
                $cover_media_id   = $Album['MediaID'];
                $AlbumName      = $Album['AlbumName'];
                if(empty($cover_media_id) || $AlbumName==DEFAULT_PROFILE_ALBUM || $AlbumName==DEFAULT_PROFILECOVER_ALBUM || $AlbumName==DEFAULT_WALL_ALBUM) {
                    $index          = $Count - 1;
                    $MediaGUID      = $Media[$index]['MediaGUID'];
                    $media_details  = get_detail_by_guid($MediaGUID, 21, "MediaID, AlbumID", 2);
                    $cover_media_id   = $media_details['MediaID'];
                    if(!empty($media_details['AlbumID']))
                    {
                        --$Count;    
                    }
                    if($cover_media_id)
                    {
                        $this->db->set("MediaID", $cover_media_id, FALSE);
                    }
                } 
                
                $set_field = "MediaCount";
                $this->db->set($set_field, "$set_field+($Count)", FALSE);
                $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->where('AlbumID',$AlbumID);        
                $this->db->update(ALBUMS);
                //echo $this->db->last_query();die;
            } 

            $this->db->update_batch(MEDIA, $update, 'MediaGUID');         
        }
        return $AlbumID;
    }

    /**
     * [getMedia used to get media name]
     * @param  [int] $SourceId [description]
     * @return [array]       [media name]
     */
    function getMedia($SourceId) {
        $this->db->select('ImageName');
        $this->db->from(MEDIA);
        $this->db->where('SourceId',$SourceId);
        $sql = $this->db->get();
        return $sql->result_array();
    }

    function getMediaSizeID($size){
        
        $size = $this->db->escape_str($size);
        
        $query = $this->db->query("SELECT MediaSizeID FROM MediaSizes WHERE ".$size." BETWEEN MinSize and MaxSize");
        if($query->num_rows()){
            return $query->row()->MediaSizeID;
        } else {
            return '1';
        }
    }
    
    /*
        Update profile picture and Profile cover when media get deleted
    */
    function remove_profile_pic($media_arr = array()){
        $section_id = $media_arr['MediaSectionID'];
        $module_id  = $media_arr['ModuleID'];
        $media_id   = $media_arr['MediaID'];
        $image_name = $media_arr['ImageName'];
        switch($module_id){
            //User
            case 3:
                if($section_id==1){
                    $this->db->set('ProfilePicture','');
                    $this->db->where('ProfilePicture',$image_name);
                    $this->db->update(USERS);
                }else if($section_id==5){
                    $this->db->set('ProfileCover','');
                    $this->db->where('ProfileCover',$image_name);
                    $this->db->update(USERS);
                }
                break;

            //group
            case 1:
                if($section_id==1){
                    $this->db->set('GroupImage','');
                    $this->db->where('GroupImage',$image_name);
                    $this->db->update(GROUPS);
                }else if($section_id==5){
                    $this->db->set('GroupCoverImage','');
                    $this->db->where('GroupCoverImage',$image_name);
                    $this->db->update(GROUPS);
                }
                break;
            
            //Event
            case 14:
                if($section_id==1){
                    $this->db->set('ProfileImageID',0);
                    $this->db->where('ProfileImageID',$media_id);
                    $this->db->update(EVENTS);
                }else if($section_id==5){
                    $this->db->set('ProfileBannerID',0);
                    $this->db->where('ProfileBannerID',$media_id);
                    $this->db->update(EVENTS);
                }
                break;
            
            //Page
            case 18:
                if($section_id==1){
                    $this->db->set('ProfilePicture','');
                    $this->db->where('ProfilePicture',$image_name);
                    $this->db->update(PAGES);
                }else if($section_id==5){
                    $this->db->set('CoverPicture','');
                    $this->db->where('CoverPicture',$image_name);
                    $this->db->update(PAGES);
                }
                break;
        }
    }

    /******* Start All Media Details *******/
    /******* Ends All Media Details *******/
    /**
     * [get_media_details description]
     * @param  [int] $user_id       [User ID]
     * @param  [string] $media_guid [Media GUID]
     * @param  [string] $paging     [Paging value Next/Previous]
     * @param  [string] $type       [Used to get details from an album or activity]
     * @return [array]              [Media details]
     */
    function get_all_media_details($user_id, $media_guid, $paging='', $type='Any',$activity_id=0)
    {
        $data = array();
        $this->load->model(array('favourite_model','flag_model','activity/activity_model','users/user_model'));
        /* Start */
        
        $mguid              = $media_guid;
        $album_id           = 0;        
        $media_count        = 0;   
        $data['MediaIndex'] = 1;
        $is_wall_album      = FALSE;   

        $owner_user_id = $this->db->select('UserID')->get_where(MEDIA,array('MediaGUID'=>$media_guid))->row()->UserID;

        $this->db->select('M.MediaGUID,M.MediaID, M.UserID, M.Resolution', FALSE);
        $this->db->where(array('M.StatusID'=>'2'));
        $this->db->where(array('M.UserID'=>$owner_user_id));
        $this->db->where('M.AlbumID!=0',NULL,FALSE);
        $this->db->order_by('M.CreatedDate','DESC');
        $mq = $this->db->get(MEDIA.' M');
        $media_count = $mq->num_rows();
        $mcount = $media_count;
        if($media_count > 1){
            $data['PrevMediaGUID'] = '';
            $data['NextMediaGUID'] = '';
            $data['Source'] = 'Album';          
            $IsMediaGUID = 0;
            $i=0;
            $all_media_guid = array();
            foreach ($mq->result() as $m) {
                $all_media_guid[] = $m->MediaGUID;
                $i++;

                //echo $i.' - ';
                if($IsMediaGUID==1){
                    $IsMediaGUID = 2;
                    $data['NextMediaGUID'] = $m->MediaGUID;
                }
                if($m->MediaGUID == $media_guid){
                    $IsMediaGUID = 1;
                    $data['MediaIndex'] = $i;
                }
                if($IsMediaGUID==0){
                    $data['PrevMediaGUID'] = $m->MediaGUID; 
                }
            }
            if($paging == 'Next'){
                if($data['NextMediaGUID']){
                    $mguid = $data['NextMediaGUID'];
                } else {
                    if($all_media_guid){
                        $mguid = $all_media_guid[0];
                    }
                }
            } else if($paging == 'Prev'){
                if($data['PrevMediaGUID']){
                    $mguid = $data['PrevMediaGUID'];
                } else {
                    if($all_media_guid){
                        $mguid = $all_media_guid[count($all_media_guid)-1];
                    }
                }
            } else {
                $mguid = $media_guid;
            }
            $data['MediaIndex'] = array_search($mguid, $all_media_guid);
            $data['MediaIndex'] = $data['MediaIndex']+1;
            
            $data['PrevImgName'] = $this->get_filename_by_guid($data['PrevMediaGUID']);
            $data['NextImgName'] = $this->get_filename_by_guid($data['NextMediaGUID']);
        }
        /* End */

        $this->db->select('MT.Name as MediaType,M.ModuleID,M.AlbumID,M.Resolution');
        $this->db->select('U.UserGUID, U.FirstName, U.LastName, U.ProfilePicture',FALSE);
        $this->db->select('PU.Url as ProfileURL',FALSE);
        $this->db->select('M.AlbumID,M.MediaID,M.ImageName,M.Caption,M.MediaSectionID,M.MediaSectionReferenceID,M.CreatedDate, M.NoOfComments,M.NoOfLikes,M.NoOfShares,M.UserID, M.Flaggable,M.IsCommentable');
        $this->db->select('MS.MediaSectionAlias as MediaFolder');
        $this->db->from(MEDIA.' M');
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID=M.MediaSectionID', 'LEFT');
        $this->db->join(USERS.' U','U.UserID=M.UserID','left');
        $this->db->join(PROFILEURL.' PU', "PU.EntityID=U.UserID AND PU.EntityType='User'");
        $this->db->where('M.MediaGUID',$mguid);
        $this->db->where('M.StatusID','2');
        $this->db->where('M.AlbumID!=0',NULL,FALSE);
        $this->db->order_by('M.CreatedDate','DESC');
        $query = $this->db->get();
        if($query->num_rows()){
            $media = $query->row_array();
            
            $IsMedia = 1;
            if($media['MediaSectionID'] == 3){
                $countQ = $this->db->where(array('MediaSectionID'=>$media['MediaSectionID'],'MediaSectionReferenceID'=>$media['MediaSectionReferenceID'], 'StatusID'=>2))->order_by('CreatedDate','DESC')->get(MEDIA);
                $this->db->select('Privacy as Visibility',false);
                $this->db->select('A.PostContent,A.ActivityGUID,A.ModuleEntityOwner,A.ActivityID,A.CreatedDate,A.NoOfComments,A.NoOfLikes,A.NoOfShares,A.UserID,A.IsCommentable,A.Flaggable');
                $this->db->select('ATY.FlagAllowed');
                $this->db->from(ACTIVITY.' A');
                $this->db->join(ACTIVITYTYPE.' ATY','A.ActivityTypeID=ATY.ActivityTypeID','left');
                $this->db->where('A.ActivityID',$media['MediaSectionReferenceID']);
                $actQ = $this->db->get();
                if($actQ->num_rows()){
                    $activity = $actQ->row_array();
                }
                if($media_count==1){
                    $IsMedia = 0;
                }
            }

            $data['MediaGUID']              = $mguid;
            $data['Resolution']              = $media['Resolution'];
            $data['ImageName']              = $media['ImageName'];
            $data['Caption']                = $media['Caption'];
            $data['MediaType']              = $media['MediaType'];
            $data['ModuleID']               = $media['ModuleID'];

            $data['CreatedBy']                  = array('UserGUID'=>$media['UserGUID'],'FirstName'=>$media['FirstName'],'LastName'=>$media['LastName'],'ProfilePicture'=>$media['ProfilePicture'],'ProfileURL'=>$media['ProfileURL']);

            $data['MediaFolder']            = $media['MediaFolder'];
            if(isset($activity) && !empty($activity)){
                $data['Visibility']             = $activity['Visibility'];
                $data['Flaggable']              = $activity['Flaggable'];
                $data['FlagAllowed']            = $activity['FlagAllowed'];
                $data['IsCommentable']          = $activity['IsCommentable'];
                if(isset($activity['ModuleID']) && $activity['ModuleID']== 3)
                {
                    $data['ShowPrivacy']            = 1;
                }
            } else {

                $data['Visibility']             = 1;
                $data['Flaggable']              = 1;
                $data['FlagAllowed']            = 1;
                $data['ShowPrivacy']            = 0;
            }

            $data['ShareAllowed'] = 1;
            $data['ShowPrivacy']            = 1;
            if($media['MediaSectionID'] == '1' || $media['MediaSectionID'] == '5') {
                $data['ShowPrivacy']            = 0;
                $data['ShareAllowed']           = 0;
            }
            
            $data['IsCommentable'] = $media['IsCommentable'];
            
            // Privacy Check
            $privacy_status = $this->activity_model->isRelation($media['UserID'],$user_id,true);
            if(!in_array($data['Visibility'],$privacy_status)){
                return $this->get_media_details($user_id,$mguid,$paging,$type);
            }

            if(!$IsMedia){
                $data['CreatedDate']    = $activity['CreatedDate'];
                $data['NoOfComments']   = $activity['NoOfComments'];
                $data['NoOfLikes']      = $activity['NoOfLikes'];
                $data['NoOfShares']     = $activity['NoOfShares'];
                $data['PostContent']    = $activity['PostContent'];
                $data['IsOwner']        = 0;
                $data['IsFlagged']      = 0;
                $data['IsFavourite']    = 0;
                if($user_id == $activity['UserID']){
                    $data['IsOwner'] = 1;
                }
                if($activity['ModuleEntityOwner'] != 0) {
                    $data['LikeName']               = $this->activity_model->getLikeName($activity['ActivityID'], $user_id, $activity['ModuleEntityOwner']);
                } else {
                    $data['LikeName']               = $this->activity_model->getLikeName($activity['ActivityID'], $user_id, 0);  
                }
                $data['IsLike']         = $this->activity_model->checkLike($activity['ActivityGUID'], 'Activity', $user_id, $activity['ModuleEntityOwner']);
                if($this->flag_model->is_flagged($user_id, $activity['ActivityID'], 'Activity')){
                    $data['IsFlagged'] = 1;
                }
                $data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id,'Activity',$activity['ActivityID']);
            } else {
                $data['CreatedDate']    = $media['CreatedDate'];
                $data['NoOfComments']   = $media['NoOfComments'];
                $data['NoOfLikes']      = $media['NoOfLikes'];
                $data['NoOfShares']     = $media['NoOfShares'];
                $data['PostContent']    = $media['Caption'];
                $data['Flaggable']      = $media['Flaggable'];
                $data['IsOwner']        = 0;
                $data['IsFlagged']      = 0;
                $data['IsFavourite']    = 0;
                $data['IsLike']         = $this->activity_model->checkLike($mguid, 'Media', $user_id, 0);
                $data['LikeName']       = $this->activity_model->getLikeName($media['MediaID'], $user_id, 0,array(),'Media');
                if($user_id == $media['UserID']){
                    $data['IsOwner'] = 1;
                }
                
                $this->load->model(array('admin/media_model'));
                if($this->check_abuse_status($media['MediaID'], $user_id)){
                    $data['IsFlagged'] = 1;
                }
                $data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id,'Media',$media['MediaID']);

                $this->notification_model->mark_notifications_as_read($user_id, $media['MediaID'], 'MEDIA');
            }
        }
        if(isset($data['PostContent']))
        {
            $data['PostContent'] = $this->activity_model->parse_tag_html($data['PostContent']);
        }

        $album_id = $media['AlbumID'];
        //echo $album_id;
        if($album_id)
        {
            $album_detail = get_detail_by_id($album_id, 13, "AlbumType, AlbumName, AlbumGUID, ModuleID, ModuleEntityID, UserID", 2);
            
            if($album_detail['ModuleID'] == 3 && $album_detail['AlbumName'] == DEFAULT_WALL_ALBUM)
            {
                $module_entity_id = $album_detail['ModuleEntityID'];
                $is_relation = $this->activity_model->isRelation($module_entity_id, $user_id, true);
                $is_wall_album = TRUE;
            }
            $data['Album'] = array('AlbumName'=>$album_detail['AlbumName'],'AlbumGUID'=>$album_detail['AlbumGUID'],'AlbumType'=>$album_detail['AlbumType'],'MediaCount'=>$mcount);   
            $data['Album']['Owner']  = $this->user_model->getUserName($album_detail['UserID'],$album_detail['ModuleID'],$album_detail['ModuleEntityID']);
            $this->db->where(array('M.AlbumID'=>$album_id));
        }

        return $data;
    }

	/*added by gautam - starts*/

    /**
    * [use to get MutualFriend records]
    * @param  [int]    $Input [loggedin user User ID]
    * @param  [Array]  $Input [post data]
    */
    function get_all_media($EntityID, $data=array(), $UserID=''){
        /* Define variables - starts */
        $Return= array();
        $CaseWhere ='';  
        $Input['PageNo'] = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO ;
        $Input['PageSize'] = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE ;
        /* Define variables - ends */  
        if($data['ModuleID']==3){
            $isRelation = $this->activity_model->isRelation($UserID, $this->UserID, true);
        }
        $this->db->select('M.MediaGUID,M.ImageName,M.VideoLength,M.Resolution', FALSE);
        $this->db->from(MEDIA. ' M');
        $this->db->where('M.MediaSectionID', 3);
        $this->db->where('M.StatusID', 2,  FALSE);

        if($data['ModuleID']==3){
            $this->db->where('M.AlbumID', $EntityID,FALSE);
            $this->db->where('M.ModuleID', 3, FALSE);

            $this->db->join(ACTIVITY.' A','A.ActivityID = M.MediaSectionReferenceID AND M.MediaSectionID=3','left');
            $this->db->where_in('A.Privacy',$isRelation);


            //$where ="AlbumID = '".$EntityID."' AND ModuleID='3' AND ";
        }elseif($data['ModuleID']==18){
            $this->db->where('M.AlbumID', $EntityID, FALSE);
            $this->db->where('M.ModuleID', 18, FALSE);
            //$where ="AlbumID = '".$EntityID."' AND ModuleID='18' AND ";
        }elseif($data['ModuleID']==1){
            $this->db->where('M.AlbumID', $EntityID, FALSE);
            $this->db->where('M.ModuleID', 1, FALSE);
            //$where ="AlbumID = '".$EntityID."' AND ModuleID='1' AND  ";
        }

        if(!empty($data['MediaType']) && $data['MediaType']==1){/*only display image*/
                //$sql .=" AND VideoLength IS NULL ";
                $this->db->where('M.VideoLength IS NULL', null, false);
        }

        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        /* Count Total Records - starts */
        $Return['TotalRecords'] = $temp_q->num_rows();
        /* Count Total Records - ends */
        $this->db->order_by('M.MediaID', 'DESC');
        //$sql .= " ORDER BY MediaID DESC ";
        
        /* Add Limit - starts */
        $Offset = get_pagination_offset($Input['PageNo'],$Input['PageSize']);
        $this->db->limit($Input['PageSize'], $Offset);

        /*Get Data - starts*/
        $users = $this->db->get()->result_array();
        /*Get Data - ends*/
        //echo $this->db->last_query();

        $output= array();
        foreach($users as $value) {

            $value['MediaType']='Photo';
            if($value['VideoLength']!=''){
                $value['MediaType']='Video';
            }
            unset($value['VideoLength']);
            $output[] = $value ;          
        }
        $Return = $output;
        return $Return;
    }
	/*added by gautam - ends*/

    /**
     * [get_search_photo]
     * @param  [int] $user_id       [User ID]
     * @param  [int] $keyword       [Keyword]
     */
    public function get_search_photo($user_id,$keyword,$posted_by,$tag,$posted_by_users,$tag_in_users,$page_no,$page_size,$count_only=false,$sort_by='',$order_by='')
    {
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $friends = $friend_followers_list['Friends'];
        $follow = $friend_followers_list['Follow'];
        $friends[] = $user_id;
        $follow[] = $user_id;
        $friends_follow = array_merge($friends,$follow);
        $friends_implode = implode(',', $friends);
        $follow_implode = implode(',', $follow);
        $friends_follow_implode = implode(',',$friends_follow);

        $sort_friends = $friends;
        $sort_network = array();
        if($sort_by == 'Network')
        {
            $sort_friends = $this->user_model->gerFriendsFollowersList($user_id,true,1,true);
            if($sort_friends)
            {
                $sort_network = $this->user_model->get_friends_of_friend($user_id,$sort_friends);
            }
        }

        $posted_by_users_id = array();
        $tag_in_users_id = array();

        if($posted_by_users)
        {
            foreach($posted_by_users as $pbu)
            {
                $posted_by_users_id[] = get_detail_by_guid($pbu,3,'UserID',1);
            }
        }

        if($tag_in_users_id)
        {
            foreach($tag_in_users_id as $tiu)
            {
                $tag_in_users_id[] = get_detail_by_guid($tiu,3,'UserID',1);
            }
        }

        $this->db->select('M.CreatedDate,M.Resolution,U.FirstName,U.LastName,U.UserGUID,MS.MediaSectionAlias as MediaFolder,M.MediaGUID,M.ImageName,M.NoOfLikes,M.NoOfComments,M.OriginalName,M.Caption,M.Description',false);
        $this->db->from(MEDIA.' M');
        $this->db->join(MEDIAEXTENSIONS.' ME','M.MediaExtensionID=ME.MediaExtensionID','left');
        $this->db->join(MEDIASECTIONS.' MS','MS.MediaSectionID=M.MediaSectionID','left');
        $this->db->join(USERS.' U','U.UserID=M.UserID','left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->select("IF(A.ActivityID is null,M.NoOfLikes,A.NoOfLikes) as NoOfLikes");
        $this->db->join(ACTIVITY.' A','A.ActivityID=M.MediaSectionReferenceID AND M.MediaSectionID="3"','left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->where_in('ME.MediaTypeID',1);
        $this->db->where('M.StatusID','2');
        $this->db->where("(M.OriginalName LIKE '%".$keyword."%' OR M.Caption LIKE '%".$keyword."%' OR IF(M.MediaSectionID=3,(SELECT PostContent FROM ".ACTIVITY." WHERE ActivityID=M.MediaSectionReferenceID) LIKE '%".$keyword."%',false) OR IF(M.MediaSectionID=4,(SELECT AlbumName FROM ".ALBUMS." WHERE AlbumID=M.MediaSectionReferenceID) LIKE '%".$keyword."%',false))",null,false);

        if($posted_by_users_id)
        {
            
            $this->db->where_in('M.UserID',$posted_by_users_id);
        }
        else if($posted_by == 'Friend')
        {
            $this->db->where("M.UserID IN(SELECT FriendID FROM ".FRIENDS." WHERE UserID='".$user_id."' AND Status='1')",null,false);
        }
        else if($posted_by == 'My Follows')
        {
            $this->db->where("(M.UserID IN(SELECT FriendID FROM ".FRIENDS." WHERE UserID='".$user_id."' AND Status='1') OR M.UserID IN(SELECT TypeEntityID  FROM ".FOLLOW." WHERE Type='User' AND UserID='".$user_id."'))",null,false);
        }

        if($tag_in_users_id)
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->join(MENTION.' MA','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MA.ActivityID AND M.MediaSectionID="3" AND MA.ModuleID="3" AND MA.ModuleEntityID IN('.implode(',',$tag_in_users_id).')','left');
            $this->db->join(MENTION.' MC','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MC.PostCommentID AND M.MediaSectionID="6" AND MC.ModuleID="3" AND MC.ModuleEntityID IN('.implode(',',$tag_in_users_id).')','left');
            $this->db->where("(MA.MentionID is not null OR MC.MentionID is not null)",null,false);
            $this->db->_protect_identifiers = TRUE;
        }
        else if($tag)
        {
            if($tag == 'Me')
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(MENTION.' MA','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MA.ActivityID AND M.MediaSectionID="3" AND MA.ModuleID="3" AND MA.ModuleEntityID="'.$user_id.'"','left');
                $this->db->join(MENTION.' MC','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MC.PostCommentID AND M.MediaSectionID="6" AND MC.ModuleID="3" AND MC.ModuleEntityID="'.$user_id.'"','left');
                $this->db->where("(MA.MentionID is not null OR MC.MentionID is not null)",null,false);
                $this->db->_protect_identifiers = TRUE;
            }
            else if($tag == 'My Friends')
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(MENTION.' MA','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MA.ActivityID AND M.MediaSectionID="3" AND MA.ModuleID="3" AND MA.ModuleEntityID IN('.$friends_implode.')','left');
                $this->db->join(MENTION.' MC','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MC.PostCommentID AND M.MediaSectionID="6" AND MC.ModuleID="3" AND MC.ModuleEntityID IN('.$friends_implode.')','left');
                $this->db->where("(MA.MentionID is not null OR MC.MentionID is not null)",null,false);
                $this->db->_protect_identifiers = TRUE;
            }
            else if($tag == 'My Follows')
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(MENTION.' MA','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MA.ActivityID AND M.MediaSectionID="3" AND MA.ModuleID="3" AND MA.ModuleEntityID IN('.$friends_follow_implode.')','left');
                $this->db->join(MENTION.' MC','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MC.PostCommentID AND M.MediaSectionID="6" AND MC.ModuleID="3" AND MC.ModuleEntityID IN('.$friends_follow_implode.')','left');
                $this->db->where("(MA.MentionID is not null OR MC.MentionID is not null)",null,false);
                $this->db->_protect_identifiers = TRUE;
            }
        }

        if($sort_by)
        {
            if($sort_by == 'Name')
            {
                $this->db->order_by('M.OriginalName',$order_by);
            }
            if($sort_by == 'Recent Updated')
            {
                $this->db->order_by('M.CreatedDate','DESC');   
            }
            if($sort_by == 'Size')
            {
                $this->db->order_by('M.Size','DESC');   
            }
            if($sort_by=='Network')
            {
                $this->db->_protect_identifiers = FALSE;
                if($sort_friends)
                {
                    $this->db->order_by("FIELD(M.UserID,".implode(',',$sort_friends).") DESC");
                    if($sort_network)
                    {
                        $this->db->order_by("FIELD(M.UserID,".implode(',',$sort_network).") DESC");
                    }
                }
                $this->db->_protect_identifiers = TRUE;
            }
        }

        if(!$count_only)
        {

            $this->db->limit($page_size,$this->get_pagination_offset($page_no, $page_size));
        }
        $query = $this->db->get();
        //echo $this->db->last_query().' ';
        $num_rows = $query->num_rows();
        
        if($count_only)
        {
            return $num_rows;
        }

        if($num_rows)
        {
            return $query->result_array();
        }
        else
        {
            return array();
        }
    }


    /**
     * [get_search_video]
     * @param  [int] $user_id       [User ID]
     * @param  [int] $keyword       [Keyword]
     */
    public function get_search_video($user_id,$keyword,$posted_by,$tag,$posted_by_users,$tag_in_users,$page_no,$page_size,$count_only=false,$sort_by='',$order_by='')
    {
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $friends = $friend_followers_list['Friends'];
        $follow = $friend_followers_list['Follow'];
        $friends[] = $user_id;
        $follow[] = $user_id;
        $friends_follow = array_merge($friends,$follow);
        $friends_implode = implode(',', $friends);
        $follow_implode = implode(',', $follow);
        $friends_follow_implode = implode(',',$friends_follow);
        
        $sort_friends = $friends;
        $sort_network = array();
        if($sort_by == 'Network')
        {
            $sort_friends = $this->user_model->gerFriendsFollowersList($user_id,true,1,true);
            if($sort_friends)
            {
                $sort_network = $this->user_model->get_friends_of_friend($user_id,$sort_friends);
            }
        }
        
        $posted_by_users_id = array();
        $tag_in_users_id = array();

        if($posted_by_users)
        {
            foreach($posted_by_users as $pbu)
            {
                $posted_by_users_id[] = get_detail_by_guid($pbu,3,'UserID',1);
            }
        }

        if($tag_in_users_id)
        {
            foreach($tag_in_users_id as $tiu)
            {
                $tag_in_users_id[] = get_detail_by_guid($tiu,3,'UserID',1);
            }
        }
        
        $keyword = $this->db->escape_like_str($keyword); 
        
        $this->db->select('M.CreatedDate,M.Resolution,U.FirstName,U.LastName,U.UserGUID,MS.MediaSectionAlias as MediaFolder,M.MediaGUID,M.ImageName,M.NoOfLikes,M.NoOfComments,M.OriginalName,M.Caption,M.Description,M.VideoLength',false);
        $this->db->from(MEDIA.' M');
        $this->db->join(MEDIAEXTENSIONS.' ME','M.MediaExtensionID=ME.MediaExtensionID','left');
        $this->db->join(MEDIASECTIONS.' MS','MS.MediaSectionID=M.MediaSectionID','left');
        $this->db->join(USERS.' U','U.UserID=M.UserID','left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->select("IF(A.ActivityID is null,M.NoOfLikes,A.NoOfLikes) as NoOfLikes");
        $this->db->join(ACTIVITY.' A','A.ActivityID=M.MediaSectionReferenceID AND M.MediaSectionID="3"','left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->where_in('ME.MediaTypeID',2);
        $this->db->where('M.StatusID','2');
        $this->db->where("(M.OriginalName LIKE '%".$keyword."%' OR M.Caption LIKE '%".$keyword."%' OR IF(M.MediaSectionID=3,(SELECT PostContent FROM ".ACTIVITY." WHERE ActivityID=M.MediaSectionReferenceID) LIKE '%".$keyword."%',false) OR IF(M.MediaSectionID=4,(SELECT AlbumName FROM ".ALBUMS." WHERE AlbumID=M.MediaSectionReferenceID) LIKE '%".$keyword."%',false))",null,false);
        $this->db->where('M.ConversionStatus','Finished');
        
        if($posted_by_users_id)
        {
            
            $this->db->where_in('M.UserID',$posted_by_users_id);
        }
        else if($posted_by == 'Friend')
        {
            $this->db->where("M.UserID IN(SELECT FriendID FROM ".FRIENDS." WHERE UserID='".$user_id."' AND Status='1')",null,false);
        }
        else if($posted_by == 'My Follows')
        {
            $this->db->where("(M.UserID IN(SELECT FriendID FROM ".FRIENDS." WHERE UserID='".$user_id."' AND Status='1') OR M.UserID IN(SELECT TypeEntityID  FROM ".FOLLOW." WHERE Type='User' AND UserID='".$user_id."'))",null,false);
        }

        if($tag_in_users_id)
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->join(MENTION.' MA','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MA.ActivityID AND M.MediaSectionID="3" AND MA.ModuleID="3" AND MA.ModuleEntityID IN('.implode(',',$tag_in_users_id).')','left');
            $this->db->join(MENTION.' MC','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MC.PostCommentID AND M.MediaSectionID="6" AND MC.ModuleID="3" AND MC.ModuleEntityID IN('.implode(',',$tag_in_users_id).')','left');
            $this->db->where("(MA.MentionID is not null OR MC.MentionID is not null)",null,false);
            $this->db->_protect_identifiers = TRUE;
        }
        else if($tag)
        {
            if($tag == 'Me')
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(MENTION.' MA','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MA.ActivityID AND M.MediaSectionID="3" AND MA.ModuleID="3" AND MA.ModuleEntityID="'.$user_id.'"','left');
                $this->db->join(MENTION.' MC','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MC.PostCommentID AND M.MediaSectionID="6" AND MC.ModuleID="3" AND MC.ModuleEntityID="'.$user_id.'"','left');
                $this->db->where("(MA.MentionID is not null OR MC.MentionID is not null)",null,false);
                $this->db->_protect_identifiers = TRUE;
            }
            else if($tag == 'My Friends')
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(MENTION.' MA','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MA.ActivityID AND M.MediaSectionID="3" AND MA.ModuleID="3" AND MA.ModuleEntityID IN('.$friends_implode.')','left');
                $this->db->join(MENTION.' MC','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MC.PostCommentID AND M.MediaSectionID="6" AND MC.ModuleID="3" AND MC.ModuleEntityID IN('.$friends_implode.')','left');
                $this->db->where("(MA.MentionID is not null OR MC.MentionID is not null)",null,false);
                $this->db->_protect_identifiers = TRUE;
            }
            else if($tag == 'My Follows')
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(MENTION.' MA','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MA.ActivityID AND M.MediaSectionID="3" AND MA.ModuleID="3" AND MA.ModuleEntityID IN('.$friends_follow_implode.')','left');
                $this->db->join(MENTION.' MC','MA.Type="1" AND MA.StatusID="2" AND M.MediaSectionReferenceID=MC.PostCommentID AND M.MediaSectionID="6" AND MC.ModuleID="3" AND MC.ModuleEntityID IN('.$friends_follow_implode.')','left');
                $this->db->where("(MA.MentionID is not null OR MC.MentionID is not null)",null,false);
                $this->db->_protect_identifiers = TRUE;
            }
        }

        if($sort_by)
        {
            if($sort_by == 'Name')
            {
                $this->db->order_by('M.OriginalName',$order_by);
            }
            if($sort_by == 'Recent Updated')
            {
                $this->db->order_by('M.CreatedDate','DESC');   
            }
            if($sort_by=='Network')
            {
                $this->db->_protect_identifiers = FALSE;
                if($sort_friends)
                {
                    $this->db->order_by("FIELD(M.UserID,".implode(',',$sort_friends).") DESC");
                    if($sort_network)
                    {
                        $this->db->order_by("FIELD(M.UserID,".implode(',',$sort_network).") DESC");
                    }
                }
                $this->db->_protect_identifiers = TRUE;
            }
        }

        if(!$count_only)
        {
            $this->db->limit($page_size,$this->get_pagination_offset($page_no, $page_size));
        }
        $query = $this->db->get();
        $num_rows = $query->num_rows();
        
        if($count_only)
        {
            return $num_rows;
        }
        
        if($num_rows)
        {
            return $query->result_array();
        }
        else
        {
            return array();
        }
    }

}
