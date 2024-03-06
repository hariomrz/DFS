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

    function ward_engagement($last_five_day) {        
        $ward_list = array();
        $result_arr = array("ward_list"=>$ward_list,"last_five_day"=>$last_five_day,"total_post"=>0);
        if (CACHE_ENABLE) {
            $result_arr = $this->cache->get('weng');            
        }
        if(empty($result_arr['ward_list'])) {   
            $current_date = get_current_date('%Y-%m-%d', 5);
            $sql = "SELECT W.WardID, W.Name, W.Number, IFNULL(SUM(WE2.TotalPost),0) as TotalPost, IFNULL(SUM(WE2.TotalComment),0) as TotalComment, IFNULL(SUM(WE2.TotalPostLike),0) as TotalPostLike, 
            IFNULL(GROUP_CONCAT(DISTINCT WE1.report_data SEPARATOR '|'),'') as final_data 
            FROM `Ward` as W 
            LEFT JOIN (
                    SELECT WE.WardID, CONCAT(WE.CreatedDate,':',WE.TotalPost,':',WE.TotalComment,':',WE.TotalPostLike) as report_data 
                    FROM `WardEngagement` as WE 
                    WHERE WE.CreatedDate >= '".$current_date."'
                ) as WE1 ON W.WardID=WE1.WardID 
            LEFT JOIN WardEngagement as WE2 ON W.WardID=WE2.WardID AND WE2.CreatedDate >= '".$current_date."'
            WHERE W.WardID!=1 AND 
            GROUP BY W.WardID ORDER BY TotalPost DESC";                   
            $query = $this->db->query($sql);
            //echo $this->db->last_query();die;
            $num_rows = $query->num_rows();
            if ($num_rows) {
                $ward_list = $query->result_array();
                $total_post = 0;
                foreach ($ward_list as $key => $ward) {
                    $final_data = $ward['final_data'];
                    $total_post =  $total_post + $ward['TotalPost'];
                    $engagement_array = array();
                    if(!empty($final_data)) {
                        $date_data = explode('|',$final_data);                        
                        foreach($date_data as $dkey=>$value){
                            $engagement_data = explode(':',$value);
                            $engagement_array[$dkey] = array('total_post'=>0, 'total_comment'=>0,'total_like'=>0);
                            if(isset($last_five_day[$dkey])){
                                $total_post = isset($engagement_data[0]) ? $engagement_data[0] : 0;
                                $total_comment = isset($engagement_data[1]) ? $engagement_data[1] : 0;
                                $total_like = isset($engagement_data[2]) ? $engagement_data[2] : 0;

                                $last_five_day[$dkey]['total_post'] +=$total_post;
                                $last_five_day[$dkey]['total_comment'] +=$total_comment;
                                $last_five_day[$dkey]['total_like']+=$total_like;

                                $engagement_array[$dkey] = array('total_post'=>$total_post, 'total_comment'=>$total_comment,'total_like'=>$total_like);

                            }
                        }
                    }
                    $ward_list[$key]['DateData'] = $engagement_array;
                }
                $result_arr = array("ward_list" => $ward_list, "last_five_day" => $last_five_day, "total_post" => $total_post);
                if (CACHE_ENABLE) {
                    $this->cache->save('weng', $result_arr, 14400);
                }
            }
        }
        return $result_arr;
    }
}
?>
