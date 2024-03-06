<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Dashboard_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get unverified entities ( User,Group , Page, Event )
     * @param  [integer]  [$page_no]
     * @param  [integer]  [$page_size]
     * @param  [string]  [$search]
     * @param  [string]  [$entityType] ("ALL", "PAGES", "GROUPS", "EVENTS", "USERS")
     * @param  [integer]  [$module_entity_id] To get specific entity
     */
    public function get_unverified_entities($page_no = '', $page_size = 20, $search = '', $entityType = 'ALL', $module_entity_id = 0) {
        
        //$entities_queries_arr = $this->get_entity_queries($search, $count_only, $main_list, $module_entity_id);
        
        $featured_tag_name = 'feature';
        
        $offset = ($page_no - 1) * $page_size;
        
        $entityTypes = array(
            'USERS' ,            
        );
        
        if (!$this->settings_model->isDisabled(1)) {
            $entityTypes[] = 'GROUPS';
        }
        
        if (!$this->settings_model->isDisabled(18)) {
            $entityTypes[] = 'PAGES';
        }
        
        if (!$this->settings_model->isDisabled(14) && $module_entity_id) {
            $entityTypes[] = 'EVENTS';
        }
        
        
        
        
        
        
        // Check $entityType has valid value
        if($entityType != 'ALL' && !in_array($entityType, $entityTypes)) {
            return array(
                'Data' => [],
                'TotalRecords' => 0,
            );
        }
        
        $main_list = true;
        if($entityType != 'ALL') {
            $entityTypes = array(
                $entityType
            );
            
            $main_list = false;
        }
        
        $main_list = false;
        $entitiesData = array(
            'Data' => [],
            'TotalRecords' => 0
        );
        foreach($entityTypes as $entityTypeTmp) {
            
            $function_name = "get_".strtolower($entityTypeTmp)."_entities";
            $entityData = $this->$function_name($page_size, $offset, $featured_tag_name, $search, $main_list, $module_entity_id);
            
            if($entityType != 'ALL') {
                return $entityData;
            }
            
            $entitiesData['Data'] =  array_merge($entitiesData['Data'], $entityData['Data']);
            $entitiesData['TotalRecords'] += $entityData['TotalRecords'];
        }
        
        // Get latest record first
        $entitiesData = $this->filter_entities_data($entitiesData, $page_size);
        
        return $entitiesData;
        
    }
    
    
    // Helper functions to get unverified entities
    protected function get_users_entities($page_size, $offset, $featured_tag_name, $search = '', $main_list = false, $module_entity_id = 0) {
        
        $select_array = array();
        $select_array[] = "SQL_CALC_FOUND_ROWS 3 AS ModuleID, U.UserID AS ModuleEntityID, CONCAT(U.FirstName, ' ', U.LastName) AS Name, IF(U.ProfilePicture='','',U.ProfilePicture) as ProfilePicture ";
        $select_array[] = "'' AS CoverImage, '' AS CreatorName, U.CreatedDate  CreatedDate, 0 AS PostCount";
        $select_array[] = "IFNULL(EN.Description, '') AS Note, 0 AS IsPublic, 0 AS Popularity, 0 AS MemberCount, IFNULL(CT.Name, '') AS CityName, IFNULL(CTR.CountryName, '') AS CountryName";
        $select_array[] = "U.Email, IFNULL(UD.DOB,'') as DOB, IFNULL(UD.Facebook_profile_URL, '') Facebook_profile_URL, U.Gender, U.Verified, U.VerifiedDate,U.SourceID,U.UserGUID";
        
        if($main_list) {
           $select_array = array();
           $select_array[] =  "SQL_CALC_FOUND_ROWS 3 AS ModuleID, U.UserID AS ModuleEntityID,  CONCAT(U.FirstName, ' ', U.LastName) AS Name, IF(U.ProfilePicture='','',U.ProfilePicture) as ProfilePicture ";
           $select_array[] =  "IFNULL(CT.Name, '') AS CityName, IFNULL(CTR.CountryName, '') AS CountryName, U.CreatedDate  CreatedDate";
        }
        
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(USERS .' U ');
        $this->db->join(USERDETAILS . ' UD', "UD.UserID = U.UserID", 'LEFT');
        $this->db->join(CITIES . ' CT', "CT.CityID = UD.CityID", 'LEFT');
        $this->db->join(COUNTRYMASTER . ' CTR', "CTR.CountryID = UD.CountryID", 'LEFT');
        
        if(!$main_list) {
            //$this->db->join(ACTIVITY . ' A', "A ON A.ModuleEntityID = U.UserID AND A.ModuleID = 3", 'LEFT');
            //$this->db->join(ENTITYTAGS . ' ET', "ET.EntityID=U.UserID AND ET.EntityType = 'USER' AND ET.StatusID = 2", 'LEFT');
            //$this->db->join(TAGS . ' T', "T.TagID = ET.TagID", 'LEFT');
            $this->db->join(ENTITYNOTE . ' EN', "EN.ModuleEntityID = U.UserID AND EN.ModuleID=3 AND EN.Status = 2", 'LEFT');
        }
        
        
        $this->db->where_not_in('U.StatusID', [3, 4]);
        if($module_entity_id) {
            $this->db->where('U.UserID', $module_entity_id);
        } else {
            $this->db->where('U.Verified', 0);
        }
        
        if($search) {
            $search = $this->db->escape_like_str($search);
            $this->db->where(" (U.FirstName Like '$search%' OR U.LastName Like '$search%') ", NULL, FALSE);
        }
        
        $this->db->group_by('U.UserID');
        $this->db->order_by('U.UserID', 'DESC');
        $this->db->limit($page_size, $offset);
        $query = $this->db->get();
        
        $entities = $query->result_array();
        
        
        
        $this->db->select('FOUND_ROWS() AS Count',false);
        $query = $this->db->get();
        $total_entities = $query->row_array();
        $total_entities = (int)isset($total_entities['Count']) ? $total_entities['Count'] : 0;
        
        $entities = $this->populate_other_data($entities, 3, 'USER');
        
        return array(
            'Data' => $entities,
            'TotalRecords' => $total_entities,
        );
    }
    
    protected function get_groups_entities($page_size, $offset, $featured_tag_name, $search = '', $main_list = false, $module_entity_id = 0) {
        
        $select_array = [];
        $select_array[] = "SQL_CALC_FOUND_ROWS 1 AS ModuleID, G.GroupID AS ModuleEntityID, G.GroupName AS Name, IF(G.GroupImage='','',G.GroupImage) as ProfilePicture";
        $select_array[] = "IF(G.GroupCoverImage='','',G.GroupCoverImage) as CoverImage, CONCAT(U.FirstName, ' ', U.LastName) AS CreatorName";
        $select_array[] = "G.CreatedDate, 0 AS PostCount , G.GroupDescription";
        $select_array[] = " IFNULL(EN.Description, '') AS Note, G.IsPublic, G.Popularity, G.MemberCount";
        $select_array[] = "'' AS CityName, '' AS CountryName, '' AS Email, '' as DOB, '' AS Facebook_profile_URL, 1 AS Gender, G.Type, G.Verified, G.VerifiedDate";
                       
        if($main_list) {
            $select_array = [];
            $select_array[] = "SQL_CALC_FOUND_ROWS 1 AS ModuleID, G.GroupID AS ModuleEntityID, G.GroupName AS Name, G.Type";
            $select_array[] = "IF(G.GroupImage='','',G.GroupImage) as ProfilePicture,'' AS CityName, '' AS CountryName, G.CreatedDate";
        }
        
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(GROUPS .' G ');
        
        
        if(!$main_list) {
            //$this->db->join(ACTIVITY . ' A', "A ON A.ModuleEntityID = G.GroupID AND A.ModuleID = 1", 'LEFT');
            $this->db->join(ENTITYNOTE . ' EN', "EN.ModuleEntityID = G.GroupID AND EN.ModuleID=1 AND EN.Status = 2", 'LEFT');
            $this->db->join(USERS . ' U', "U.UserID = G.CreatedBy", 'LEFT');
        }
        
        if($search) {
            $this->db->join(GROUPMEMBERS . ' GM', " ON GM.GroupID = G.GroupID ", 'LEFT');
            $this->db->join(USERS . ' GUM', " ON GUM.UserID = GM.ModuleEntityID ", 'LEFT');
        }
        
        
        $this->db->where('G.StatusID', 2);
        if($module_entity_id) {
            $this->db->where('G.GroupID', $module_entity_id);
        } else {
            $this->db->where('G.Verified', 0);
        }
        
        if($search) {
            $search = $this->db->escape_like_str($search);
            $this->db->where(" IF( G.Type = 'INFORMAL',  (GUM.FirstName Like '$search%' OR GUM.LastName Like '$search%' ),  (G.GroupName  Like '$search%' )) ", NULL, FALSE);
        }
        
        $this->db->group_by('G.GroupID');
        $this->db->order_by('G.GroupID', 'DESC');
        $this->db->limit($page_size, $offset);
        $query = $this->db->get();
        
        $entities = $query->result_array();
        
        //echo $this->db->last_query(); die;
        
        $this->db->select('FOUND_ROWS() AS Count',false);
        $query = $this->db->get();
        $total_entities = $query->row_array();
        $total_entities = (int)isset($total_entities['Count']) ? $total_entities['Count'] : 0;
        
        
        $populate_entities = array('GROUP' => 1);
        if(!$main_list) {
            $populate_entities = array();
        }
        
        $entities = $this->populate_other_data($entities, 1, 'GROUP', $populate_entities);


        //print_r(($entities[0]['Tags']));die;
        
        return array(
            'Data' => $entities,
            'TotalRecords' => $total_entities,
        );
    }

    protected function get_pages_entities($page_size, $offset, $featured_tag_name, $search = '', $main_list = false, $module_entity_id = 0) {  
                
        $select_array = [];
        $select_array[] = "SQL_CALC_FOUND_ROWS 18 AS ModuleID, P.PageID AS ModuleEntityID,  P.Title AS Name, IF(P.ProfilePicture='','',P.ProfilePicture) as ProfilePicture";
        $select_array[] = ", IF(P.CoverPicture='','',P.CoverPicture) as CoverImage, CONCAT(U.FirstName, ' ', U.LastName) AS CreatorName, P.CreatedDate";
        $select_array[] = "0 AS PostCount, IFNULL(EN.Description, '') AS Note,  1 AS IsPublic, P.Popularity, P.NoOfFollowers AS  MemberCount,";        
        $select_array[] = " '' AS CityName, '' AS CountryName, '' AS Email, '' as DOB, '' AS Facebook_profile_URL"
                . ", 1 AS Gender, P.Verified, CM.Name AS Category, P.VerifiedDate";
                
        if($main_list) {
            $select_array = [];
            $select_array[] = "SQL_CALC_FOUND_ROWS 18 AS ModuleID, P.PageID AS ModuleEntityID,  P.Title AS Name, IF(P.ProfilePicture='','',P.ProfilePicture) as ProfilePicture";
            $select_array[] = " '' AS CityName, '' AS CountryName, P.CreatedDate";
        }
        
        
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(PAGES .' P ');
        $this->db->join(CATEGORYMASTER . " CM", "CM.CategoryID = P.CategoryID", "LEFT");
        
        if(!$main_list) {
            //$this->db->join(ACTIVITY . ' A', "A.ModuleEntityID = P.PageID AND A.ModuleID = 18", 'LEFT');
            $this->db->join(ENTITYNOTE . ' EN', "EN.ModuleEntityID = P.PageID AND EN.ModuleID=18 AND EN.Status = 2", 'LEFT');
            $this->db->join(USERS . ' U', "U.UserID = P.UserID", 'LEFT');
        }
        
        
        $this->db->where('P.StatusID', 2);
        if($module_entity_id) {
            $this->db->where('P.PageID', $module_entity_id);
        } else {
            $this->db->where('P.Verified', 0);
        }
        
        if($search) {
            $search = $this->db->escape_like_str($search);
            $this->db->where(" P.Title LIKE '$search%' ", NULL, FALSE);
        }
        
        $this->db->group_by('P.PageID');
        $this->db->order_by('P.PageID', 'DESC');
        $this->db->limit($page_size, $offset);
        $query = $this->db->get();
        
        //echo $this->db->last_query(); die;
        
        $entities = $query->result_array();
        
        $this->db->select('FOUND_ROWS() AS Count',false);
        $query = $this->db->get();
        $total_entities = $query->row_array();
        $total_entities = (int)isset($total_entities['Count']) ? $total_entities['Count'] : 0;
        
        if(!$main_list) {
            $entities = $this->populate_other_data($entities, 18, 'PAGE');
        }
        
        
        return array(
            'Data' => $entities,
            'TotalRecords' => $total_entities,
        );
    }
    
    protected function get_events_entities($page_size, $offset, $featured_tag_name, $search = '', $main_list = false, $module_entity_id = 0) {
        
        $select_array = [];
        $select_array[] = "SQL_CALC_FOUND_ROWS 14 AS ModuleID, E.EventID AS ModuleEntityID,  E.Title AS Name, IF(MPI.ImageName='','',MPI.ImageName) as ProfilePicture";
        $select_array[] = "IF(MCI.ImageName='','',MCI.ImageName) as CoverImage, CONCAT(U.FirstName, ' ', U.LastName) AS CreatorName, E.CreatedDate";
        $select_array[] = "0 AS PostCount";
        $select_array[] = "IFNULL(EN.Description, '') AS Note, E.Visibility AS IsPublic, 0 AS Popularity";
        $select_array[] = "'' AS CityName, '' AS CountryName, '' AS Email, '' as DOB, '' AS Facebook_profile_URL, 1 AS Gender";
        $select_array[] = "E.LocationID, E.StartDate, E.StartTime, E.EndDate, E.EndTime, E.Venue, E.Verified";
       
        
        if($main_list) {
            $select_array = [];
            $select_array[] = "SQL_CALC_FOUND_ROWS 14 AS ModuleID, E.EventID AS ModuleEntityID,  E.Title AS Name, IF(MPI.ImageName='','',MPI.ImageName) as ProfilePicture, '' AS CityName, '' AS CountryName";
            $select_array[] = "1 AS Gender, E.CreatedDate";
        }
        
        
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(EVENTS .' E ');
        $this->db->join(MEDIA . ' MPI', "MPI.MediaID = E.ProfileImageID ", 'LEFT');
        
        
        if(!$main_list) {
            //$this->db->join(ACTIVITY . ' A', "A.ModuleEntityID = E.EventID AND A.ModuleID = 14", 'LEFT');
            $this->db->join(ENTITYNOTE . ' EN', "EN.ModuleEntityID = E.EventID AND EN.ModuleID=14 AND EN.Status = 2", 'LEFT');
            $this->db->join(USERS . ' U', "U.UserID = E.CreatedBy", 'LEFT');
            $this->db->join(MEDIA . ' MCI', "MCI.MediaID = E.ProfileBannerID ", 'LEFT');
        }
        
        
        $this->db->where('E.IsDeleted', 0);
        if($module_entity_id) {
            $this->db->where('E.EventID', $module_entity_id);
        } else {
            $this->db->where('E.Verified', 0);
        }
        
        if($search) {
            $search = $this->db->escape_like_str($search);
            $this->db->where(" E.Title LIKE '$search%' ", NULL, FALSE);
        }
        
        $this->db->group_by('E.EventID');
        $this->db->order_by('E.EventID', 'DESC');
        $this->db->limit($page_size, $offset);
        $query = $this->db->get();
        
        $entities = $query->result_array();
        
        $this->db->select('FOUND_ROWS() AS Count',false);
        $query = $this->db->get();
        $total_entities = $query->row_array();
        $total_entities = (int)isset($total_entities['Count']) ? $total_entities['Count'] : 0;
        
        
        if(!$main_list) {
            $entities = $this->populate_other_data($entities, 14, 'EVENT');
        }
        
        // Get extra data in case of event details api call
        if($module_entity_id) {
            $this->load->helper('location');
            $this->load->model(array('events/event_model'));
            foreach ($entities as $index => $entity){
                
                $location = get_location_by_id($entity['LocationID']);
                $entities[$index]['Location'] = $location;
                
                $time_zone_id = $location['TimeZoneID'];
                $this->load->model('timezone/timezone_model');
                if (empty($time_zone_id) || is_null($time_zone_id)) {
                    $time_zone_id = $this->timezone_model->get_time_zone_id($location['Latitude'], $location['Longitude']);

                    $this->db->where('LocationID', $location['LocationID']);
                    $this->db->update(LOCATIONS, array('TimeZoneID' => $time_zone_id));

                    unset($location['LocationID']);
                }
                $time_zone = $this->timezone_model->get_time_zone_name($time_zone_id);

                $start_date = $entity['StartDate'];
                $start_time = $entity['StartTime'];
                $end_date = $entity['EndDate'];
                $end_time = $entity['EndTime'];


                $start_date_time = $this->timezone_model->convert_date_to_time_zone($start_date . ' ' . $start_time, 'UTC', $time_zone);
                $end_date_time = $this->timezone_model->convert_date_to_time_zone($end_date . ' ' . $end_time, 'UTC', $time_zone);
                $start_date_time = explode(' ', $start_date_time);
                $end_date_time = explode(' ', $end_date_time);

                $entities[$index]['StartDate'] = $start_date_time[0];
                $entities[$index]['StartTime'] = $start_date_time[1];
                $entities[$index]['EndDate'] = $end_date_time[0];
                $entities[$index]['EndTime'] = $end_date_time[1];
                $entities[$index]['TimeZone'] = $time_zone;
                
                $entities[$index]['Presence'] = $this->event_model->getEventMemberCount($entities[$index]['ModuleEntityID']);
                $entities[$index]['Presence'] = ($entities[$index]['Presence']) ? $entities[$index]['Presence'] : [];
            }
        }
               
        return array(
            'Data' => $entities,
            'TotalRecords' => $total_entities,
        );
    }
    
    
       
    protected function populate_other_data($entities, $module_id, $entity_type, $populate_entities = array(), $featured_tag_name = 'feature') {
        $entity_ids = array();
        foreach($entities as $entity) {
            $entity_ids[] = $entity['ModuleEntityID'];
        }
        
        $is_specific_entity = (bool)count($populate_entities);
        
        if((!$is_specific_entity || !empty($populate_entities['CATEGORY'])) && $module_id != 18 ) {
            $category_data = $this->get_entities_categories($entity_ids, $module_id);
        }
        
        if(!$is_specific_entity || !empty($populate_entities['TAG'])) {
            $tag_data = $this->get_entities_tags($entity_ids, $entity_type);
        }
                
        if($module_id == 1 && (!$is_specific_entity || !empty($populate_entities['GROUP']))) {
            $groups_data = $this->get_entities_group_members($entity_ids);
        }
        
        if($module_id == 14 && (!$is_specific_entity || !empty($populate_entities['EVENT']))) {
            $event_members = $this->get_entities_event_members($entity_ids);
        }
        
        $entities_members_activities = $this->get_entities_members_activities_data($entity_ids, $module_id);
        
        foreach($entities as  $index => $entity) {
            if((!$is_specific_entity || !empty($populate_entities['CATEGORY'])) &&  $module_id != 18 ) {
                $entity['Categories'] = isset($category_data[$entity['ModuleEntityID']]) ? $category_data[$entity['ModuleEntityID']] : array();
            }
            
            if($module_id == 18) {
                $entity['Categories'] = array($entity['Category']);
            }
            
            if(!$is_specific_entity || !empty($populate_entities['TAG'])) {                
                $entity['Tags'] = isset($tag_data[$entity['ModuleEntityID']]) ? $tag_data[$entity['ModuleEntityID']] : array();                
                $entity['Featured_TagID'] = 0;
                foreach ($entity['Tags'] as $key => $tag_data_single) {
                    if(strtolower($tag_data_single['Name']) == strtolower($featured_tag_name)) {
                        $entity['Featured_TagID'] = $tag_data_single['EntityTagID'];
                        break;
                    }
                }
            }
            
            
            if($module_id == 1 && (!$is_specific_entity || !empty($populate_entities['GROUP']))) {
                $entity['MemberCount'] = isset($groups_data[$entity['ModuleEntityID']]['Count_Users']) ? $groups_data[$entity['ModuleEntityID']]['Count_Users'] : 0;
                if($entity['Type'] == 'INFORMAL' && empty($entity['Name'])) {
                    $entity['Name'] = isset($groups_data[$entity['ModuleEntityID']]['Names']) ? $groups_data[$entity['ModuleEntityID']]['Names'] : '';
                }
            }
            
            if($module_id == 14 && (!$is_specific_entity || !empty($populate_entities['EVENT']))) {
                $entity['MemberCount'] = isset($event_members[$entity['ModuleEntityID']]['Count_Users']) ? $event_members[$entity['ModuleEntityID']]['Count_Users'] : 0;
            }
            
            $entity['PostCount'] = isset($entities_members_activities[$entity['ModuleEntityID']]['ActivityCount']) ? $entities_members_activities[$entity['ModuleEntityID']]['ActivityCount'] : 0;
                        
            $entities[$index] = $entity;
        }
        
        return $entities;
    }
    
    protected function filter_entities_data($entitiesData, $page_size) {
        $entities = array();
        $entities = $entitiesData['Data'];  
        usort($entities, function($a, $b) {
            return  strtotime($b['CreatedDate']) - strtotime($a['CreatedDate']);
        });
        $entities = array_slice($entities, 0, $page_size);
        $entitiesData['Data'] = $entities;
        return $entitiesData;
    }

    



    protected function get_entities_event_members($moduele_entity_ids) {
        if(!$moduele_entity_ids) {
            return array();
        }
        
        $select_array = [];
        $select_array[] = "COUNT(EUM.UserID) AS Count_Users, EU.EventID";
        
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(EVENTUSERS .' EU ');
        $this->db->join(USERS . ' EUM', " ON EUM.UserID = EU.UserID ", 'LEFT');
        //$this->db->where('EC.ModuleID', $module_id);
        //$this->db->where('CM.StatusID', 2);
        
        $this->db->where_in('EU.EventID', $moduele_entity_ids);
        $this->db->group_by('EU.EventID');
        $this->db->order_by('EU.EventID', 'ASC');
        
        $query = $this->db->get();
        $entities = $query->result_array();
        
        $entity_data = array();
        foreach($entities as $entity) {
            $entity_data[$entity['EventID']] = $entity;
        }
        
        return $entity_data;
    }
    
    protected function get_entities_members_activities_data($moduele_entity_ids, $module_id) {
        if(!$moduele_entity_ids) {
            return array();
        }
        
        $select_array = [];
        $select_array[] = "COUNT(A.ActivityID) AS ActivityCount, A.ModuleEntityID";
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(ACTIVITY .' A ');
        $this->db->where('A.ModuleID', $module_id);
        //$this->db->where('CM.StatusID', 2);
        
        $this->db->where_in('A.ModuleEntityID', $moduele_entity_ids);
        $this->db->group_by('A.ModuleEntityID');
        
        $query = $this->db->get();
        $entities = $query->result_array();
        
        $entity_data = array();
        foreach($entities as $entity) {
            $entity_data[$entity['ModuleEntityID']] = $entity;
        }
        
        return $entity_data;
    }

    protected function get_entities_group_members($moduele_entity_ids) {
        if(!$moduele_entity_ids) {
            return array();
        }
        $select_array = [];
        $select_array[] = "Group_concat(CONCAT(GUM.FirstName, ' ', GUM.LastName)) AS Names, COUNT(GUM.UserID) AS Count_Users, GM.GroupID";
        
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(GROUPMEMBERS .' GM ');
        $this->db->join(USERS . ' GUM', " ON GUM.UserID = GM.ModuleEntityID ", 'LEFT');
        //$this->db->where('EC.ModuleID', $module_id);
        //$this->db->where('CM.StatusID', 2);
        
        $this->db->where_in('GM.GroupID', $moduele_entity_ids);
        $this->db->group_by('GM.GroupID');
        $this->db->order_by('GM.GroupID', 'ASC');
        
        $query = $this->db->get();
        $entities = $query->result_array();
        
        $entity_data = array();
        foreach($entities as $entity) {
            $entity_data[$entity['GroupID']] = $entity;
        }
        
        return $entity_data;
        
    }

    protected function get_entities_categories($moduele_entity_ids, $module_id) {
        if(!$moduele_entity_ids) {
            return array();
        }
        $select_array = [];
        $select_array[] = "CM.Name, EC.ModuleEntityID";
        
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(ENTITYCATEGORY .' EC ');
        $this->db->join(CATEGORYMASTER . ' CM', " CM.CategoryID=EC.CategoryID ", 'LEFT');
        $this->db->where('EC.ModuleID', $module_id);
        $this->db->where('CM.StatusID', 2);
        
        $this->db->where_in('EC.ModuleEntityID', $moduele_entity_ids);
        //$this->db->group_by('EC.ModuleEntityID');
        $this->db->order_by('EC.ModuleEntityID', 'ASC');
        
        $query = $this->db->get();
        $entities = $query->result_array();
        
        //echo $this->db->last_query(); die;
        $entity_categories = array();
        foreach($entities as $entity) {
            $entity_categories[$entity['ModuleEntityID']][] = $entity['Name'];
        }
        
        return $entity_categories;
    }

    protected function get_entities_tags($entity_ids, $entity_type) {
        if(!$entity_ids) {
            return array();
        }
        $select_array = [];
        $select_array[] = "ET.EntityID, T.TagID, T.Name, T.Name as TooltipTitle, ET.AddedBy, ET.EntityTagID";
        
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(ENTITYTAGS .' ET ');
        $this->db->join(TAGS . ' T', "T.TagID = ET.TagID ", 'LEFT');
        $this->db->where('ET.EntityType', $entity_type);
        //$this->db->where('ET.AddedBy', 1);
        $this->db->where('ET.StatusID', 2);
        
        $this->db->where_in('ET.EntityID', $entity_ids);
        //$this->db->group_by('ET.EntityID');
        $this->db->order_by('ET.EntityID', 'ASC');
        
        $query = $this->db->get();
        $entities = $query->result_array();
        
        //echo $this->db->last_query(); die;
        
        $entity_data = array();
        foreach($entities as $entity) {
            $entity_data[$entity['EntityID']][] = $entity;
        }
        return $entity_data;
    }

    
    
    /**
     * To Update entity data
     * @param  [integer]  [$module_id]
     * @param  [integer]  [$module_entity_id]
     * @param  [integer]  [$entity_column_val]  (0, 1)
     * @param  [string]  [$entity_column] ("Verified", "StatusID")
     * @param  [integer]  [$user_id] In case of ACTIVITY status change
     */
    public function update_entity($module_id, $module_entity_id, $entity_column_val = 1, $entity_column = 'Verified', $user_id = 0, $reason='') {
        $update_table = ''; 
        $where_column = '';
        switch ($module_id) {
            case 1:
                $update_table = GROUPS;
                $where_column = 'GroupID';
            break;
            case 18:
                $update_table = PAGES;
                $where_column = 'PageID';
            break;
            case 14:
                $update_table = EVENTS;
                $where_column = 'EventID';
            break;
            case 3:
                $update_table = USERS;
                $where_column = 'UserID';
            break;
            case 19:
                $update_table = ACTIVITY;
                $where_column = 'ActivityID';
            break;
            case 20:
                $update_table = POSTCOMMENTS;
                $where_column = 'PostCommentID';
            break;
        }
        
        
        if($module_id == 19 && $entity_column == 'StatusID') {
            $this->load->model(array('activity/activity_model'));
            $this->activity_model->removeActivity($module_entity_id, $user_id, $entity_column_val, $reason);
            return;
        }
        
        if($module_id == 20 && $entity_column == 'StatusID') {
            $comment_guid = get_detail_by_id($module_entity_id, 20, 'PostCommentGUID', 1);
            $this->load->model(array('activity/activity_model'));
            $this->activity_model->deleteComment($comment_guid, $user_id, 'ACTIVITY', $reason);
            return;
        }
        
        
        if(!$update_table) {
            return false;
        }
        
        if($entity_column == 'StatusID') {
            $update_data = array(
                'StatusID' => $entity_column_val
            );
        } else {
            $update_data = array(
                'Verified' => $entity_column_val,
                'VerifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')
            );
        }
        
        if($module_id == 19 && $entity_column == 'Verified') {
            $update_data['Flaggable'] = 0;
        }
        
        $this->db->where($where_column, $module_entity_id); 
        $this->db->update($update_table, $update_data); 
        
        return true;
    }

    public function send_activity_notification($module_id, $module_entity_id) {
        if($module_id == 19) {
            $this->db->select('A.UserID, A.ActivityGUID, A.LocalityID, A.PostContent, A.PostType');
            $this->db->select('IFNULL(A.PostTitle,"") as PostTitle', FALSE);
            $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as SenderName");
            $this->db->from(ACTIVITY .' A ');
            $this->db->join(USERS . ' U', "U.UserID = A.UserID");
            $this->db->where('A.ActivityID', $module_entity_id); 
            $this->db->where('A.StatusID', 2);
            $this->db->limit(1);
            $query = $this->db->get();
            $activity_data = $query->row_array();
            if (!empty($activity_data)) {
                $push_notification = array("EntityID" => $module_entity_id, "EntityGUID" => $activity_data['ActivityGUID'], "Refer" => "ACTIVITY");
                $notification_data = array('ActivityID' => $module_entity_id, 'PostType' => $activity_data['PostType'], 'UserID' => $activity_data['UserID'], 'SenderName' => $activity_data['SenderName'], 'NotificationTypeKey' => 'post_message', 'Mentions' => array(), 'LocalityID' => $activity_data['LocalityID'], 'PostContent' => $activity_data['PostContent'], 'PostTitle' => $activity_data['PostTitle'], 'PushNotification' => $push_notification);
                //print_r($notification_data);
                initiate_worker_job('post_notification', $notification_data, '', 'notification');
            }
        }
    }
    
    
    /**
     * To Get entity members(admins of entities) for different entities
     * @param  [integer]  [$ModuleID]
     * @param  [integer]  [$ModuleEntityID]
     */
    public function getEntitiesMessageMembers($ModuleID, $ModuleEntityID) {
        $members = array();
        if($ModuleID == 1) {
            $this->load->model('group/group_model');
            $members = $this->group_model->get_all_group_admins($ModuleEntityID);
        } else if($ModuleID == 18) {
            $this->load->model('pages/page_model');
            $members = $this->page_model->get_all_admins($ModuleEntityID);
        } else if($ModuleID == 14) {
            $this->load->model('events/event_model');
            $members = $this->event_model->getEventAdmins($ModuleEntityID);
        } else {
            $members = array($ModuleEntityID);
        }
        
        $recepients = array();
        foreach ($members as $member) {
            $recepients[] = array(
                'UserID' => $member
            );
        }
        return $recepients;
    }
    
    
    
}