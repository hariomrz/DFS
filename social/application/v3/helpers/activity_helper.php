<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 *
 * @access  public
 * @param   entity_id  // Entity ID
 * @param   module_id  // Module ID 
 * @return           // 
 */
if (!function_exists('set_last_activity_date')) {

    function set_last_activity_date($entity_id, $module_id = 0, $is_update=TRUE, $is_promoted=0) {
        if (!empty($entity_id)) {
            $CI = & get_instance();
            $table_name = ACTIVITY;
            $set_field = "ModifiedDate";
            $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
            $condition = array("ActivityID" => $entity_id);
            switch ($module_id) {
                case '1':
                    $table_name = GROUPS;
                    $set_field = "LastActivity";
                    $condition = array("GroupID" => $entity_id);
                    break;
                case '18':
                    $table_name = PAGES;
                    $set_field = "LastActionDate";
                    $condition = array("PageID" => $entity_id);
                    break;
                case '14':
                    $table_name = EVENTS;
                    $set_field = "LastActivity";
                    $condition = array("EventID" => $entity_id);
                    break;
                case '23':
                    $table_name = RATINGS;
                    $set_field = "ModifiedDate";
                    $condition = array("RatingID" => $entity_id);
                    break;
                default:
                    //$CI->load->model('activity/activity_model');
                    //$ward_ids = $CI->activity_model->get_activity_ward_ids($entity_id);
                    //$current_date = get_current_date('%Y-%m-%d %H:%i:%s', '0.25', 1);
                    //if(in_array(1, $ward_ids)) {
                        //$current_date = get_current_date('%Y-%m-%d %H:%i:%s');            
                    //}
                    //$current_date = get_current_date('%Y-%m-%d %H:%i:%s');
                    if($is_promoted) {
                        $is_update = FALSE;
                    }
                    $table_name = ACTIVITY;
                    $set_field = "ModifiedDate";
                    $condition = array("ActivityID" => $entity_id);
                    if($is_update) {
                        $CI->db->where('ModifiedDate <= ', $current_date);
                    }
                    break;
            }
            if($is_update) {
                $CI->db->where($condition);
                $CI->db->set($set_field, $current_date);
                $CI->db->update($table_name);
            }
            //echo $CI->db->last_query();
            initiate_worker_job('check_activity_visibility', array('ActivityGUID' => get_detail_by_id($entity_id), 'ENVIRONMENT' => ENVIRONMENT));
        }
        return true;
    }

}

/**
 * Get single activity url.
 * @param int $activity_id
 * @return URL
 */
if (!function_exists('get_single_activity_url_phone')) {

    function get_single_activity_url_phone($activity_id, $type = '') {

        $CI = &get_instance();
       // $CI->load->model('group/group_model');
        
        if ($type == 'Polls') {
            $CI->db->select('ActivityID');
            $CI->db->from(POLL);
            $CI->db->where('PollID', $activity_id);
            $CI->db->limit('1');
            $poll_query = $CI->db->get();
            if ($poll_query->num_rows()) {
                $activity_id = $poll_query->row()->ActivityID;
            }
        }

        $CI->db->select('A.ActivityID,A.ActivityGUID, A.ActivityTypeID, A.ModuleID, A.ModuleEntityID, A.Params');    //, G.GroupGUID, G.GroupName
        $CI->db->from(ACTIVITY. '  A');
        
        //$CI->db->join(GROUPS . " G", "G.GroupID=A.ModuleEntityID AND A.ModuleID=1", "LEFT");
        
        $CI->db->where('ActivityID', $activity_id);
        $CI->db->limit('1');
        $query = $CI->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            $url = '';
            if ($row->ModuleID == 1 && $row->ActivityTypeID != '5' && $row->ActivityTypeID != '6' && $row->ActivityTypeID != '13') {
                $ModuleEntityGUID = get_guid_by_id($row->ModuleEntityID, 1);
                $returnArray = array("ModuleID" => 1, "ModuleEntityGUID" => $ModuleEntityGUID, "EntityID" => $row->ModuleEntityID, "EntityGUID" => $row->ActivityGUID, "Refer" => "ACTIVITY_GROUP");            
            } else if ($row->ModuleID == 18 && ($row->ActivityTypeID == '16' || $row->ActivityTypeID == '17')) {
                $entity = get_detail_by_id($row->ModuleEntityID, 18, "PageURL,PageGUID", 2);
                $page_url = $entity['PageURL'];
                $ModuleEntityGUID = $entity['PageGUID'];
                $rating_params = json_decode($row->Params, true);
                $rating_id = $rating_params['RatingID'];
                $rating_guid = get_detail_by_id($rating_id, 23, 'RatingGUID', 1);
                $returnArray = array("ModuleID" => 18, "ModuleEntityGUID" => $ModuleEntityGUID, "EntityID" => $rating_id, "EntityGUID" => $rating_guid, "Refer" => "RATING_PAGE");            
            } else if ($row->ModuleID == 18 && $row->ActivityTypeID != '5' && $row->ActivityTypeID != '6' && $row->ActivityTypeID != '13') {
                $entity = get_detail_by_id($row->ModuleEntityID, 18, "PageURL,PageGUID", 2);
                $page_url = $entity['PageURL'];
                $ModuleEntityGUID = $entity['PageGUID'];
                $returnArray = array("ModuleID" => 18,"ModuleEntityGUID" => $ModuleEntityGUID, "EntityID" => $row->ModuleEntityID, "EntityGUID" => $row->ActivityGUID, "Refer" => "ACTIVITY_PAGE");           
            } else if ($row->ModuleID == 14 && $row->ActivityTypeID != '5' && $row->ActivityTypeID != '6' && $row->ActivityTypeID != '13') {
                $event_guid = get_detail_by_id($row->ModuleEntityID, 14, 'EventGUID');
                $returnArray = array("ModuleID" => 14,"ModuleEntityGUID" => $event_guid, "EntityID" => $row->ModuleEntityID, "EntityGUID" => $row->ActivityGUID, "Refer" => "ACTIVITY_EVENT");            
            } else if ($row->ModuleID == 3 && $row->ActivityTypeID != '5' && $row->ActivityTypeID != '6' && $row->ActivityTypeID != '13') {
                $ModuleEntityGUID = get_detail_by_id($row->ModuleEntityID, 3, 'UserGUID');
                $returnArray = array("ModuleID" => 3,"ModuleEntityGUID" => $ModuleEntityGUID, "EntityID" => $row->ActivityID, "EntityGUID" => $row->ActivityGUID, "Refer" => "ACTIVITY");            
            } else if ($row->ModuleID == 1 && ($row->ActivityTypeID == '5' || $row->ActivityTypeID == '6' || $row->ActivityTypeID == '13')) {
                $ModuleEntityGUID = get_guid_by_id($row->ModuleEntityID, 1);
                $activityGUID = album_guid_by_activity($activity_id);
                $returnArray = array("ModuleID" => 1,"ModuleEntityGUID" =>$ModuleEntityGUID , "EntityID" => $row->ModuleEntityID, "EntityGUID" => $activityGUID, "Refer" => "ALBUM_GROUP");            
            } else if ($row->ModuleID == 18 && ($row->ActivityTypeID == '5' || $row->ActivityTypeID == '6' || $row->ActivityTypeID == '13')) {
                $EntityGUID = album_guid_by_activity($activity_id);
                $page_url = get_detail_by_id($row->ModuleEntityID, 18, "PageURL, PageGUID", 1);
                $returnArray = array("ModuleID" => 18,"ModuleEntityGUID" => $page_url['PageGUID'], "EntityID" => $row->ModuleEntityID, "EntityGUID" => $EntityGUID, "Refer" => "ALBUM_PAGE");            
            } else if ($row->ModuleID == 14 && ($row->ActivityTypeID == '5' || $row->ActivityTypeID == '6' || $row->ActivityTypeID == '13')) {
                $EventGUID = get_detail_by_id($row->ModuleEntityID, 14, 'EventGUID');
                $album_guid = album_guid_by_activity($activity_id);
                $returnArray = array("ModuleID" => 14,"ModuleEntityGUID" => $EventGUID, "EntityID" => $row->ModuleEntityID, "EntityGUID" => $album_guid, "Refer" => "ALBUM_EVENT");            
            } else if ($row->ModuleID == 3 && ($row->ActivityTypeID == '5' || $row->ActivityTypeID == '6' || $row->ActivityTypeID == '13')) {
                $EntityGUID = album_guid_by_activity($activity_id);
                $ModuleEntityGUID = get_detail_by_id($row->ModuleEntityID, 3, 'UserGUID');
                $returnArray = array("ModuleID" => 3,"ModuleEntityGUID" => $ModuleEntityGUID, "EntityID" => $row->ModuleEntityID, "EntityGUID" => $EntityGUID, "Refer" => "ALBUM");            
            } else if ($row->ModuleID == 34) {
                $ModuleEntityGUID = get_guid_by_id($row->ModuleEntityID, 34);
                $returnArray = array("ModuleID" => 34,"ModuleEntityGUID" => $ModuleEntityGUID, "EntityID" => $row->ModuleEntityID, "EntityGUID" => $row->ActivityGUID, "Refer" => "ACTIVITY_FORUM");
            } else if ($row->ModuleID == 47) {
                $ModuleEntityGUID = get_guid_by_id($row->ModuleEntityID, 47);
                $returnArray = array("ModuleID" => 47,"ModuleEntityGUID" => $ModuleEntityGUID, "EntityID" => $row->ModuleEntityID, "EntityGUID" => $row->ActivityGUID, "Refer" => "ACTIVITY_QUIZ");
            }
            return $returnArray;
        }
    }
}

if (!function_exists('get_single_post_url')) {
    function get_single_post_url($activity) {
        $url = '';
        $activity_guid = $activity['ActivityGUID'];        
        
        $title = getPostTitle($activity);
        $post_type = (isset($activity['PostType']) && $activity['PostType'] == 4) ?'blog'  : 'post';
        $title_url = seoUrl($title);
        if(!$title_url) {
            $title_url = 'title';
        }
        $post_guid = $activity['ActivityGUID'];
        $url = "$post_type/$title_url/$post_guid";
        
        return $url;        
    }

    function seoUrl($string) {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_@~.:\s-]/", "", $string);  
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }
    
    function getPostTitle($activity, $default = 'title') {
        $title = $activity['PostTitle'];
        
        if(!$title) {
            $title = strip_tags($activity['PostContent']);
            $title = trim(substr($title,0,140), ' ');
        }
        
        if(!$title) {
            $title = $default;
        }
        
        return $title;
    }

}

/**
 * Get single activity url.
 * @param int $activity_id
 * @return URL
 */
if (!function_exists('get_single_activity_url')) {

    function get_single_activity_url($activity_id, $type = '') {

        $CI = &get_instance();

        if ($type == 'Polls') {
            $CI->db->select('ActivityID');
            $CI->db->from(POLL);
            $CI->db->where('PollID', $activity_id);
            $CI->db->limit('1');
            $poll_query = $CI->db->get();
            if ($poll_query->num_rows()) {
                $activity_id = $poll_query->row()->ActivityID;
            }
        }

        $CI->db->select('*');
        $CI->db->from(ACTIVITY);
        $CI->db->where('ActivityID', $activity_id);
        $CI->db->limit('1');
        $query = $CI->db->get();
        if ($query->num_rows()) {
            //$row = $query->row();
            
            $activity = $query->row_array();
            return site_url() . get_single_post_url($activity);
            
            $url = '';
            if ($row->ModuleID == 1 && $row->ActivityTypeID != '5' && $row->ActivityTypeID != '6' && $row->ActivityTypeID != '13') {
                $url = site_url('group/wall') . '/' . $row->ModuleEntityID . '/activity/' . $row->ActivityGUID;
            } else if ($row->ModuleID == 18 && ($row->ActivityTypeID == '16' || $row->ActivityTypeID == '17')) {
                $page_url = get_detail_by_id($row->ModuleEntityID, 18, "PageURL", 1);
                $rating_id = json_decode($row->Params, true);
                $rating_id = $rating_id['RatingID'];
                $rating_guid = get_detail_by_id($rating_id, 23, 'RatingGUID', 1);
                $url = site_url('page') . '/' . $page_url . '/ratings/' . $rating_guid;
            } else if ($row->ModuleID == 18 && $row->ActivityTypeID != '5' && $row->ActivityTypeID != '6' && $row->ActivityTypeID != '13') {
                $page_url = get_detail_by_id($row->ModuleEntityID, 18, "PageURL", 1);
                $url = site_url('page') . '/' . $page_url . '/activity/' . $row->ActivityGUID;
            } else if ($row->ModuleID == 14 && $row->ActivityTypeID != '5' && $row->ActivityTypeID != '6' && $row->ActivityTypeID != '13') {
                $url = site_url('events') . '/' . get_detail_by_id($row->ModuleEntityID, 14, 'EventGUID') . '/wall' . '/activity/' . $row->ActivityGUID;
            } else if ($row->ModuleID == 3 && $row->ActivityTypeID != '5' && $row->ActivityTypeID != '6' && $row->ActivityTypeID != '13') {
                $url = get_entity_url($row->ModuleEntityID) . '/activity/' . $row->ActivityGUID;
            } else if ($row->ModuleID == 1 && ($row->ActivityTypeID == '5' || $row->ActivityTypeID == '6' || $row->ActivityTypeID == '13')) {
                $url = site_url('group/media') . '/' . $row->ModuleEntityID . '/' . album_guid_by_activity($activity_id);
            } else if ($row->ModuleID == 18 && ($row->ActivityTypeID == '5' || $row->ActivityTypeID == '6' || $row->ActivityTypeID == '13')) {
                $page_url = get_detail_by_id($row->ModuleEntityID, 18, "PageURL", 1);
                $url = site_url('page') . '/' . $page_url . '/media/' . album_guid_by_activity($activity_id);
            } else if ($row->ModuleID == 14 && ($row->ActivityTypeID == '5' || $row->ActivityTypeID == '6' || $row->ActivityTypeID == '13')) {
                $url = site_url('events/media') . '/' . get_detail_by_id($row->ModuleEntityID, 14, 'EventGUID') . '/' . album_guid_by_activity($activity_id);
            } else if ($row->ModuleID == 3 && ($row->ActivityTypeID == '5' || $row->ActivityTypeID == '6' || $row->ActivityTypeID == '13')) {
                $url = get_entity_url($row->ModuleEntityID) . '/media/' . album_guid_by_activity($activity_id);
            } else if ($row->ModuleID == 34) {
                $CI->load->model('forum/forum_model');
                $url = site_url() . $CI->forum_model->get_category_url($row->ModuleEntityID) . '/' . $row->ActivityGUID;
            }
            return $url;
        }
    }

}

/**
 * Get album guid of aprticulr activity.
 * @param int $activity_id
 * @return string Album GUID
 */
if (!function_exists('album_guid_by_activity')) {

    function album_guid_by_activity($activity_id) {
        $CI = &get_instance();
        $CI->db->select('AlbumGUID');
        $CI->db->from(ALBUMS);
        $CI->db->where('ActivityID', $activity_id);
        $CI->db->limit('1');
        $query = $CI->db->get();
        if ($query->num_rows()) {
            return $query->row()->AlbumGUID;
        }
    }

}

// Sort MultiDimension Array
function array_sort_by_column(&$array, $column, $direction = SORT_ASC) {
    $reference_array = array();
    foreach ($array as $key => $row) {
        $reference_array[$key] = $row[$column];
    }
    array_multisort($reference_array, $direction, $array);
}
