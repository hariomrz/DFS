<?php
/**
 * This model is used to for ward
 * @package    Ward_model
 * @author     Vinfotech Team
 * @version    1.0
 * 
 *
 */
class Ward_model extends Common_Model {
    
    function __construct() {
        parent::__construct();
    }

    public function get_ward_list($data=array()) {        
       $ward_list = array();
        if (CACHE_ENABLE) {
            $ward_list = $this->cache->get('ward_list');            
        }
        if(empty($ward_list)) {
            /* $page_no    = safe_array_key($data, 'PageNo', 1);
            $page_size  = safe_array_key($data, 'PageSize', '');
            $search_keyword = safe_array_key($data, 'Keyword', '');
           */     
            $this->db->select('IFNULL(W.Name,"") as WName', FALSE);
            $this->db->select('IFNULL(W.WardID,"") as WID', FALSE);
            $this->db->select('IFNULL(W.Number,"") as WNumber', FALSE);
            $this->db->select('IFNULL(W.Description,"") as WDescription', FALSE);
            $this->db->from(WARD . ' W');
            $this->db->order_by('W.Number', 'ASC');
            /* if (!empty($search_keyword)) {
                $this->db->where("(W.Name like '%" . $this->db->escape_like_str($search_keyword) . "%')");
            }        
            if(!empty($page_size)) {
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }
             * 
             */
            $query = $this->db->get();
            if ($query->num_rows()) {
                $ward_list = $query->result_array();
                if (CACHE_ENABLE) {
                    $this->cache->save('ward_list', $ward_list, CACHE_EXPIRATION);
                }
            }
        }
        if(!empty($ward_list)) {
            initiate_worker_job('upload_api_data_on_bucket', array('FileName' => "ward_list.json", "FileData" => $ward_list));
        }
        return $ward_list;
    }
    
    public function get_trending_ward_list($data) {        
       $trending_ward_list = array();
        if (CACHE_ENABLE) {
            $trending_ward_list = $this->cache->get('trending_ward_list');            
        }
        if(empty($trending_ward_list)) {
            /* $page_no    = safe_array_key($data, 'PageNo', 1);
            $page_size  = safe_array_key($data, 'PageSize', '');
            $search_keyword = safe_array_key($data, 'Keyword', '');
           */     
            $this->db->select('IFNULL(W.Name,"") as WName', FALSE);
            $this->db->select('IFNULL(W.WardID,"") as WID', FALSE);
            $this->db->select('IFNULL(W.Number,"") as WNumber', FALSE);
            $this->db->select('IFNULL(W.Description,"") as WDescription', FALSE);
            $this->db->from(WARD . ' W');
            $this->db->where('IsTrending',1);
            $this->db->order_by('W.Number', 'ASC');
            /* if (!empty($search_keyword)) {
                $this->db->where("(W.Name like '%" . $this->db->escape_like_str($search_keyword) . "%')");
            }        
            if(!empty($page_size)) {
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }
             * 
             */
            $query = $this->db->get();
            if ($query->num_rows()) {
                $trending_ward_list = $query->result_array();
                if (CACHE_ENABLE) {
                    $this->cache->save('trending_ward_list', $trending_ward_list, CACHE_EXPIRATION);
                }
            }
        }
        if(!empty($trending_ward_list)) {
            initiate_worker_job('upload_api_data_on_bucket', array('FileName' => "trending_ward_list.json", "FileData" => $trending_ward_list));
        }
        return $trending_ward_list;
    }
    
    
    function is_ward_exist($ward_id) {
        $this->db->select('W.WardID');
        $this->db->from(WARD. ' W');
        $this->db->where('W.WardID', $ward_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return TRUE;
        }
        return FALSE;
    }
    
    function ward_user_count($last_five_day) {        
        $ward_list = array();
        $result_arr = array("ward_list"=>$ward_list,"last_five_day"=>$last_five_day,"total_user"=>0);
        if (CACHE_ENABLE) {
            $result_arr = $this->cache->get('wuc');            
        }
        if(empty($result_arr['ward_list'])) {   
            $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
            $sql = 'SELECT L.WardID, W.Number, W.Name, COUNT(UD.UserDetailID) AS TotalUser, IFNULL(temp1.date_data,"") AS DateData 
                    FROM Locality AS L
                    LEFT JOIN UserDetails AS UD ON L.LocalityID = UD.LocalityID
                    LEFT JOIN Ward AS W ON W.WardID = L.WardID
                    LEFT JOIN (
                        SELECT WardID,CONCAT("{",GROUP_CONCAT(DISTINCT final_data),"}") AS date_data, days_ago
                        FROM (
                            SELECT L1.WardID,CONCAT(\'"\',DATE_FORMAT(U1.CreatedDate, "%Y-%m-%d"),\'":"\',COUNT(U1.UserID),\'"\') AS final_data, DATE_FORMAT(U1.CreatedDate, "%Y-%m-%d") AS register_date,DATEDIFF("'.$current_date.'", U1.CreatedDate) AS days_ago, COUNT(UD1.UserID) AS total
                            FROM UserDetails AS UD1
                            INNER JOIN Users AS U1 ON U1.UserID=UD1.UserID
                            INNER JOIN Locality AS L1 ON L1.LocalityID=UD1.LocalityID
                            GROUP BY L1.WardID,DATE_FORMAT(U1.CreatedDate, "%Y-%m-%d") HAVING days_ago < 6 AND days_ago >= 1
                        ) AS temp GROUP BY WardID
                    ) AS temp1 ON temp1.WardID=L.WardID
                    WHERE L.WardID IS NOT NULL
                    GROUP BY L.WardID ORDER BY W.Number';
            $query = $this->db->query($sql);
            //echo $this->db->last_query();die;
            $num_rows = $query->num_rows();
            if ($num_rows) {
                $ward_list = $query->result_array();
                $total_user = 0;
                foreach ($ward_list as $key => $ward) {
                    $date_data = $ward['DateData'];
                    $total_user =  $total_user + $ward['TotalUser'];
                    if(empty($date_data)) {
                        $date_data = array();
                    } else {
                        $date_data = json_decode($date_data);
                        foreach($date_data as $dkey=>$value){
                            if(isset($last_five_day[$dkey])){
                                $last_five_day[$dkey]['total']+=$value;
                            }
                        }
                    }
                    $ward_list[$key]['DateData'] = $date_data;
                }
                $result_arr = array("ward_list" => $ward_list, "last_five_day" => $last_five_day, "total_user" => $total_user);
                if (CACHE_ENABLE) {
                    $this->cache->save('wuc', $result_arr, 14400);
                }
            }
        }
        return $result_arr;
    }

    function ward_engagement($last_five_day, $order_by = 1) {        
        $ward_list = array();
        $result_arr = array("ward_list"=>$ward_list,"last_five_day"=>$last_five_day,"total_post"=>0);
        if (CACHE_ENABLE) {
            //$result_arr = $this->cache->get('weng');            
        }
        if(empty($result_arr['ward_list'])) {   
            $current_date = get_current_date('%Y-%m-%d', 5);
            $sql = "SELECT W.WardID, W.Name, W.Number, IFNULL(SUM(WE1.TotalPost),0) as TotalPost, IFNULL(SUM(WE1.TotalComment),0) as TotalComment, IFNULL(SUM(WE1.TotalPostLike),0) as TotalPostLike, 
            IFNULL(GROUP_CONCAT(DISTINCT WE1.report_data SEPARATOR '|'),'') as final_data 
            FROM `Ward` as W 
            LEFT JOIN (
                    SELECT WE.WardID, WE.TotalPost, WE.TotalComment, WE.TotalPostLike, CONCAT(WE.CreatedDate,':',WE.TotalPost,':',WE.TotalComment,':',WE.TotalPostLike) as report_data 
                    FROM `WardEngagement` as WE 
                    WHERE WE.CreatedDate >= '".$current_date."'
                ) as WE1 ON W.WardID=WE1.WardID          
             
            GROUP BY W.WardID ORDER BY TotalPost DESC";                   
            $query = $this->db->query($sql);
            //WHERE W.WardID!=1 
            //echo $this->db->last_query();die;
            $num_rows = $query->num_rows();
            if ($num_rows) {
                $ward_list = $query->result_array();
                $total_post = 0;
                foreach ($ward_list as $key => $ward) {
                    $final_data = $ward['final_data'];
                    $total_post =  $total_post + $ward['TotalPost'];
                    $engagement_array = array();
                    $ward_list[$key]['Number'] = (int)$ward['Number'];
                    $ward_list[$key]['TotalPost'] = (int)$ward['TotalPost'];
                    $ward_list[$key]['TotalComment'] = (int)$ward['TotalComment'];
                    $ward_list[$key]['TotalPostLike'] = (int)$ward['TotalPostLike'];
                    $ward_list[$key]['DateData'] = $engagement_array;
                    if(!empty($final_data)) {
                        $final_data = explode('|',$final_data);
                        foreach($final_data as $ky=>$value){
                            $engagement_data = explode(':',$value);
                            $dkey = isset($engagement_data[0]) ? $engagement_data[0] : '';
                            if($dkey) {
                                $engagement_array[$dkey] = array('total_post'=>0, 'total_comment'=>0,'total_like'=>0);
                                if(isset($last_five_day[$dkey])){
                                    $total_post = isset($engagement_data[1]) ? $engagement_data[1] : 0;
                                    $total_comment = isset($engagement_data[2]) ? $engagement_data[2] : 0;
                                    $total_like = isset($engagement_data[3]) ? $engagement_data[3] : 0;

                                    $last_five_day[$dkey]['total_post'] +=$total_post;
                                    $last_five_day[$dkey]['total_comment'] +=$total_comment;
                                    $last_five_day[$dkey]['total_like']+=$total_like;

                                    $engagement_array[$dkey] = array('total_post'=>$total_post, 'total_comment'=>$total_comment,'total_like'=>$total_like);

                                }
                            }
                        }                        
                        $ward_list[$key]['DateData'] = $engagement_array;                        
                    }
                    unset($ward_list[$key]['final_data']);
                }
                $result_arr = array("ward_list" => $ward_list, "last_five_day" => $last_five_day, "total_post" => $total_post);
                if (CACHE_ENABLE) {
                    $this->cache->save('weng', $result_arr, 14400);
                }
            }
        }
        return $result_arr;
    }
    
    /**
     * [To save ward feature user]
     * @param [int] $user_id   [user id]
     * @param [array] $ward_ids   [ward ids]
     */
    function save_feature_user($user_id, $ward_ids, $edit_id=0) {
        
        if(in_array(1, $ward_ids)) {
            $ward_ids = array(1);
        
            $this->db->select('WardID');
            $this->db->from(WARDFEATUREUSER);
            $this->db->where('UserID', $user_id);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $this->db->where('UserID', $user_id);
                $this->db->delete(WARDFEATUREUSER);
                foreach ($query->result_array() as $row) {
                    $edit_ward_id = $row['WardID'];
                    //$this->delete_cache_data($edit_ward_id);
                }
            }
        } else if(!empty($edit_id)) {
            $this->db->select('WardID');
            $this->db->from(WARDFEATUREUSER);
            $this->db->where('WardFeatureUserID', $edit_id);
            $this->db->limit(1);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $edit_ward_id = $query->row()->WardID;

                $this->db->where('WardFeatureUserID', $edit_id);
                $this->db->delete(WARDFEATUREUSER);

                //$this->delete_cache_data($edit_ward_id, TRUE);                
            }
        }

        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        if (!empty($ward_ids)) {            
            
            $this->db->where_in('WardID', $ward_ids);
            $this->db->where('UserID', $user_id);
            $this->db->delete(WARDFEATUREUSER);
        
            foreach ($ward_ids as $ward_id) {
                $feature_user_ward = array();
                $feature_user_ward['UserID'] = $user_id;
                $feature_user_ward['WardID'] = $ward_id;
                $feature_user_ward['CreatedDate'] = $current_date;

                $this->db->insert(WARDFEATUREUSER, $feature_user_ward);
                //$this->delete_cache_data($ward_id);
            }
        }
        
    }

    /**
     * [To remove feature user]
     * @param [int] $user_id   [user id]
     * @param [int] $ward_id   [ward id]
     */
    function remove_feature_user($user_id, $ward_id) {
        $this->db->select('WardID');
        $this->db->select('WardFeatureUserID');
        $this->db->from(WARDFEATUREUSER);
        if(!empty($ward_id)) {
            $this->db->where('WardID', $ward_id);
        }
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ward_list = $query->result_array();
            foreach ($ward_list as $key => $ward) {
                $ward_id = $ward['WardID'];
                $ward_featured_user_id = $ward['WardFeatureUserID'];
                
                $this->db->where('WardFeatureUserID', $ward_featured_user_id);
                $this->db->delete(WARDFEATUREUSER);
                $falg=FALSE;
                if($ward_id == 1) {
                    $falg=TRUE;
                }
                //$this->delete_cache_data($ward_id, $falg);
            }
        }
    }
    
    function set_pinned_feature_user($wf_uid) {
        $this->db->select('WF.WardID, WF.IsPinned');
        $this->db->from(WARDFEATUREUSER . ' WF');                
        $this->db->where('WF.WardFeatureUserID', $wf_uid);
        $this->db->limit(1);    
        $query = $this->db->get();
        if($query->num_rows()) {
            $row = $query->row_array();
            $is_pinned = $row['IsPinned'];
            if(empty($is_pinned)) {
                $this->db->set('IsPinned',1,false);
                $this->db->where('WardFeatureUserID', $wf_uid);        
                $this->db->update(WARDFEATUREUSER);
            }
        }
    }
    
    function remove_pinned_feature_user($wf_uid) {
        $this->db->select('WF.WardID');
        $this->db->from(WARDFEATUREUSER . ' WF');                
        $this->db->where('WF.WardFeatureUserID', $wf_uid);
        $this->db->limit(1);    
        $query = $this->db->get();
        if($query->num_rows()) {
            $this->db->set('IsPinned',0,false);
            $this->db->where('WardFeatureUserID', $wf_uid);        
            $this->db->update(WARDFEATUREUSER);
        }
    }

        
    function delete_cache_data($ward_id, $falg=FALSE) {
        if($ward_id == 1 && $falg) {            
            $ward_list = $this->get_ward_list();
            foreach ($ward_list as $ward) {
                $ward_id = $ward['WID'];
                if (CACHE_ENABLE) {
                    $this->cache->delete('wfu_'.$ward_id);
                }
            }
        } else {
            if (CACHE_ENABLE) {
                $this->cache->delete('wfu_'.$ward_id);
            }
        }        
    }
    
    /**
     * [To get list of features users]
     * @param [array] $data   [posted data]
     * @return [array]      [users result]
     */
    public function get_featured_user($data) {         
        $ward_ids[] = $ward_id = $data['WID'];
        //$page_no    = safe_array_key($data, 'PageNo', 1);
        //$page_size  = safe_array_key($data, 'PageSize', 20);
        $order_by = safe_array_key($data, 'OrderBy', 'Name');
        //$sort_by = safe_array_key($data, 'SortBy', 'ASC');
        $only_user_id = safe_array_key($data, 'OnlyID', 0);
        $user_id = safe_array_key($data, 'UserID', 0);
        if(!in_array(1, $ward_ids)) {
            $ward_ids[] = 1;
        }  
        
        $users = array();
        /* if (CACHE_ENABLE && empty($only_user_id) && $order_by == 'Name') {
            $users = $this->cache->get('wfu_'.$ward_id);   
            if (!is_array($users)) {
                $users = array();
            }         
        }
        */
        
        if(empty($users)) {       
            $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID, U.UserID");
            $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
            $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
            $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
            $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
            $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
            $this->db->from(WARDFEATUREUSER . ' WF');
            $this->db->join(USERS . ' U', 'U.UserID = WF.UserID AND U.StatusID NOT IN (3,4)');            
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
            $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID');
            $this->db->where_in('WF.WardID', $ward_ids);
            if($order_by == 'Name') {
                $this->db->order_by('U.FirstName', 'ASC');
                $this->db->order_by('U.LastName', 'ASC');  
            } else {
                 $this->db->order_by('U.LastLoginDate', 'DESC');
            }
            /*if(!empty($page_size)) {
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }
             * 
             */
            $query = $this->db->get();       
            //echo $this->db->last_query();die;
            if($query->num_rows()) {
                $result = $query->result_array();
                if($only_user_id) {
                    $user_ids = [0];
                    foreach ($result as $user){
                        $user_ids[] = $user['UserID'];
                    }            
                    return $user_ids;
                }

                $followers = array();
                $is_follow_disabled = $this->settings_model->isDisabled(11);
                if(!$is_follow_disabled) {
                    $followers = $this->user_model->get_followers_list();  
                } 
                foreach ($result as $user){

                    if(!$is_follow_disabled) {
                        $user['IsFollow'] = 0;
                        if ($user['UserID'] == $user_id) {
                            $user['IsFollow'] = 2;
                        } else if (in_array($user['UserID'], $followers)) {
                            $user['IsFollow'] = 1;
                        }
                        
                        $IsAdmin = $this->user_model->is_super_admin($user['UserID']);
                        $this->load->model('activity/activity_model');
                        $IsAdminGuid = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID)); // admin set from config page
                        if($IsAdmin || $user['UserID']==$IsAdminGuid )
                        {
                            $user['IsFollow']=2;
                        }
                    }

                    $user['Locality'] = array(
                        "Name" => $user['Name'], 
                        "HindiName"=> $user['HindiName'], 
                        "ShortName"=> $user['ShortName'],  
                        "LocalityID" => $user['LocalityID']);
                    
                    unset($user['UserID']);
                    unset($user['Name']);
                    unset($user['HindiName']);
                    unset($user['ShortName']);
                    unset($user['LocalityID']);
                    $users[] = $user;
                }
            }

            /*if (CACHE_ENABLE && $order_by == 'Name') {
                $this->cache->save('wfu_'.$ward_id, $users, 300);
            } 
            */           
        }
        return $users;
    }
    
    
    /**
     * [To get pinned feature user]
     * @param [array] $data   [posted data]
     * @return [array]      [users result]
     */
    public function get_pinned_feature_user($data) {         
        $ward_ids[] = $ward_id = $data['WID'];
        $user_id = safe_array_key($data, 'UserID', 0);
        if(!in_array(1, $ward_ids)) {
            $ward_ids[] = 1;
        }  
             
        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID, U.UserID");
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
        $this->db->from(WARDFEATUREUSER . ' WF');
        $this->db->join(USERS . ' U', 'U.UserID = WF.UserID AND U.StatusID NOT IN (3,4)');            
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID');
        $this->db->where('WF.IsPinned', 1);
        $this->db->where_in('WF.WardID', $ward_ids);
        //$this->db->order_by('U.FirstName', 'ASC');
        //$this->db->order_by('U.LastName', 'ASC');
        $this->db->order_by('U.UserID', 'RANDOM');
        //$this->db->limit(1);  
        $query = $this->db->get();
        $users = array();
        if($query->num_rows()) {
            $result = $query->result_array();

            $followers = array();
            $is_follow_disabled = $this->settings_model->isDisabled(11);
            if(!$is_follow_disabled) {
                $followers = $this->user_model->get_followers_list();  
            } 

            foreach ($result as $user){

                if(!$is_follow_disabled) {
                    $user['IsFollow'] = 0;
                    if ($user['UserID'] == $user_id) {
                        $user['IsFollow'] = 2;
                    } else if (in_array($user['UserID'], $followers)) {
                        $user['IsFollow'] = 1;
                    } 

                    $IsAdmin = $this->user_model->is_super_admin($user['UserID']);
                    $this->load->model('activity/activity_model');
                    $IsAdminGuid = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID)); // admin set from config page
                    if($IsAdmin || $user['UserID']==$IsAdminGuid )
                    {
                        $user['IsFollow']=2;
                    }
                }

                $user['Locality'] = array(
                    "Name" => $user['Name'], 
                    "HindiName"=> $user['HindiName'], 
                    "ShortName"=> $user['ShortName'],  
                    "LocalityID" => $user['LocalityID']);
                
                unset($user['UserID']);
                unset($user['Name']);
                unset($user['HindiName']);
                unset($user['ShortName']);
                unset($user['LocalityID']);            
                $users[] = $user;
            }
        }          
       
        return $users;
    }


    public function get_pinned_feature_user_old($data) {         
        $ward_ids[] = $ward_id = $data['WID'];
        $user_id = safe_array_key($data, 'UserID', 0);
        if(!in_array(1, $ward_ids)) {
            $ward_ids[] = 1;
        }  
             
        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID, U.UserID");
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
        $this->db->from(WARDFEATUREUSER . ' WF');
        $this->db->join(USERS . ' U', 'U.UserID = WF.UserID AND U.StatusID NOT IN (3,4)');            
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID');
        $this->db->where('WF.IsPinned', 1);
        $this->db->where_in('WF.WardID', $ward_ids);
        $this->db->order_by('U.FirstName', 'ASC');
        $this->db->order_by('U.LastName', 'ASC');
        $this->db->limit(1);  
        $query = $this->db->get();
        $user = array();
        if($query->num_rows()) {
            $user = $query->row_array();

            $followers = array();
            $is_follow_disabled = $this->settings_model->isDisabled(11);
            if(!$is_follow_disabled) {
                $followers = $this->user_model->get_followers_list();  
            } 

            if(!$is_follow_disabled) {
                $user['IsFollow'] = 0;
                if ($user['UserID'] == $user_id) {
                    $user['IsFollow'] = 2;
                } else if (in_array($user['UserID'], $followers)) {
                    $user['IsFollow'] = 1;
                }
                
                $IsAdmin = $this->user_model->is_super_admin($user['UserID']);
                $this->load->model('activity/activity_model');
                $IsAdminGuid = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID)); // admin set from config page
                if($IsAdmin || $user['UserID']==$IsAdminGuid )
                {
                    $user['IsFollow']=2;
                }
            }

            $user['Locality'] = array(
                "Name" => $user['Name'], 
                "HindiName"=> $user['HindiName'], 
                "ShortName"=> $user['ShortName'],  
                "LocalityID" => $user['LocalityID']);
            
            unset($user['UserID']);
            unset($user['Name']);
            unset($user['HindiName']);
            unset($user['ShortName']);
            unset($user['LocalityID']);            
        }          
       
        return $user;
    }
    
    
    public function is_featured_user($data, $flag=0) {
        $ward_id = safe_array_key($data, 'WID', 0);
        $ward_ids[] = $ward_id;       
        if(!in_array(1, $ward_ids)) {
            $ward_ids[] = 1;
        }
        $this->db->select('WF.WardFeatureUserID, WF.IsPinned');
        $this->db->from(WARDFEATUREUSER . ' WF');
        if (!empty($ward_id)) {
            $this->db->where_in('WF.WardID', $ward_ids);
        }        
        $this->db->where('WF.UserID', $data['UserID']);
        $this->db->limit(1);    
        $query = $this->db->get();
        if($query->num_rows()) {
            if(empty($flag)) {
                return 1;
            } else {
                $row = $query->row_array();
                return $row;
            }
        }            
        return 0;    
    }
    
    public function feature_user_ward($user_id) {
        $this->db->select('WF.WardID');
        $this->db->from(WARDFEATUREUSER . ' WF');
        $this->db->where('WF.UserID', $user_id);
        $query = $this->db->get();
        return $result = $query->result_array();               
    }

    /**
     * [To get list of features users]
     * @param [array] $data   [posted data]
     * @return [array]      [users result]
     */
    public function who_to_follow($data) {  
        $ward_ids[] = $ward_id = $data['WID'];
        $page_no    = safe_array_key($data, 'PageNo', 1);
        $page_size  = safe_array_key($data, 'PageSize', 10);
        $order_by = safe_array_key($data, 'OrderBy', 'Name');
        $sort_by = safe_array_key($data, 'SortBy', 'ASC');
        $only_user_id = safe_array_key($data, 'OnlyID', 0);
        $user_id = safe_array_key($data, 'UserID', 0);
        if(!in_array(1, $ward_ids)) {
            $ward_ids[] = 1;
        }  
        
        $users = array();
        /* if (CACHE_ENABLE && empty($only_user_id) && $order_by == 'Name') {
            $users = $this->cache->get('wfu_'.$ward_id);   
            if (!is_array($users)) {
                $users = array();
            }         
        }
        */
        
        if(empty($users)) { 
            if(isset($data['UserID']))
            {
                $followers = $this->db->select("FollowingID")
                ->from(FOLLOW)
                ->where("UserID",$data['UserID'])
                ->get()->result_array();

                $excluding_users = array_unique(array_column($followers,'FollowingID'));
                
                //adding self userid
                array_push($excluding_users,$data['UserID']);
            }
            

            $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID, U.UserID");
            $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
            $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
            $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
            $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
            $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
            $this->db->from(USERS . ' U');
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
            $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID');
            $this->db->where_not_in('U.StatusID',[3,4]);
            $this->db->where('U.FirstName!=','');
            
            //excluding following users
            if(!empty($excluding_users))
            {
                $this->db->where_not_in("U.UserID",$excluding_users);          
            }

            /*if(isset($ward_ids))
            {
                $this->db->where_in('WF.WardID',$ward_ids);
            }*/


            if($order_by == 'Name') {
                $this->db->order_by('U.FirstName', 'ASC');
                $this->db->order_by('U.LastName', 'ASC');  
            } else {
                 $this->db->order_by('U.LastLoginDate', 'DESC');
            }
            // $this->db->limit (10);
            if(!empty($page_size)) {
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }
            
            $query = $this->db->get();       
            // echo $this->db->last_query();die;
            if($query->num_rows()) {
                $result = $query->result_array();
                // echo $this->db->last_query();die;
                if($only_user_id) {
                    $user_ids = [0];
                    foreach ($result as $user){
                        $user_ids[] = $user['UserID'];
                    }            
                    return $user_ids;
                }

                $followers = array();
                $is_follow_disabled = $this->settings_model->isDisabled(11);
                if(!$is_follow_disabled) {
                    $followers = $this->user_model->get_followers_list();  
                } 
                foreach ($result as $user){

                    if(!$is_follow_disabled) {
                        $user['IsFollow'] = 0;
                        if ($user['UserID'] == $user_id) {
                            $user['IsFollow'] = 2;
                        } else if (in_array($user['UserID'], $followers)) {
                            $user['IsFollow'] = 1;
                        }
                        
                        $IsAdmin = $this->user_model->is_super_admin($user['UserID']);
                        $this->load->model('activity/activity_model');
                        $IsAdminGuid = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID)); // admin set from config page
                        if($IsAdmin || $user['UserID']==$IsAdminGuid )
                        {
                            $user['IsFollow']=2;
                        }
                    }

                    $user['Locality'] = array(
                        "Name" => $user['Name'], 
                        "HindiName"=> $user['HindiName'], 
                        "ShortName"=> $user['ShortName'],  
                        "LocalityID" => $user['LocalityID']);
                    
                    unset($user['UserID']);
                    unset($user['Name']);
                    unset($user['HindiName']);
                    unset($user['ShortName']);
                    unset($user['LocalityID']);
                    $users[] = $user;
                }
            }

            /*if (CACHE_ENABLE && $order_by == 'Name') {
                $this->cache->save('wfu_'.$ward_id, $users, 300);
            } 
            */           
        }
        return $users;
    }
}
?>