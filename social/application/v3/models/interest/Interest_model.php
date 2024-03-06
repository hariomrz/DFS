<?php
/**
 * This model is used for getting and storing interest related information
 * @package    Interest_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Interest_model extends Common_Model {

    function __construct() {
        parent::__construct();
    }
    
    /**
     * 
     * @param type $parent_category_id
     * @param type $user_id
     * @return type
     */
    function get_interests($user_id=0, $parent_interest_id=0, $page_no = 1, $page_size = 10) {
        if($this->settings_model->isDisabled(31)) {
            return array();            
        }
        $data=array();            
        $this->db->select('I.InterestID'); //, "interest" as MediaSectionAlias
        $this->db->select('I.Name', FALSE);
        $this->db->select('IFNULL(I.Icon,"") as Icon', FALSE);
        $this->db->select("'0' as IsInterested",false);
        $this->db->from(INTEREST . "  I");
        $this->db->where('I.ParentID', $parent_interest_id, FALSE);
        if(!empty($parent_interest_id)) {
           // $this->db->where('I.ParentID', $parent_interest_id);
        }            
        $this->db->where('I.Status',2);
        /* if ($page_no && $page_size) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        */
        $this->db->order_by('I.Name', 'ASC');
        $query = $this->db->get();
        $data = $query->result_array();
        
        $temp_data=array();
        if($user_id) {
            foreach($data as $item) {
                if(!empty($parent_interest_id)) {
                    $item['IsInterested'] =$this->is_user_intrested($user_id,$item['InterestID']);
                } else {
                    $item['SubInterest'] = $this->get_interests($user_id, $item['InterestID'], 1, 100);
                }               
                $temp_data[]= $item;       
            }
            $data=$temp_data;
        }        
        return $data;
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $interest_id
     * @return int
     */
    function is_user_intrested($user_id, $interest_id) {
        $this->db->select("IF(InterestID is not NULL,1,0) as IsInterested",false);
        $this->db->from(USERINTEREST);
        $this->db->where('InterestID', $interest_id, FALSE);
        $this->db->where('UserID', $user_id, FALSE);
        $this->db->limit(1);
        $query = $this->db->get(); 
        $data=$query->row_array();
        if(!empty($data)) {
            return $data['IsInterested'];
        } else {
            return 0;
        }
    }    
	
    /**
     * [insert_update_user_interest Used to insert/update user interest]
     * @param  [array] $interest_ids    [array of interest id]
     * @param  [int] $user_id           [user id]
     * @return [bool]                   [true/false]
     */
    function insert_update_user_interest($interest_ids, $user_id, $action='update') {
        if (CACHE_ENABLE) {
            $this->cache->delete('usrint_' . $user_id);
        }
        if(is_array($interest_ids) && count($interest_ids) > 0 && !empty($interest_ids)) {
            $user_interests = array();
            $insert_data = array();
            $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');
            foreach($interest_ids as $key=>$interest_id) {
                $this->db->select("UserInterestID");
                $this->db->from(USERINTEREST);
                $this->db->where(array('InterestID' => $interest_id, 'UserID'=>$user_id));
                $query = $this->db->get();
                $user_interests[] = $interest_id;
 
                if( $query->num_rows() == 0 ) {               
                    $insert_data[$key]['InterestID']        = $interest_id;
                    $insert_data[$key]['UserID']            = $user_id;
                    $insert_data[$key]['CreatedDate']       = $current_date_time;
                }         
            } 
               
            if(!empty($insert_data)) {
            	$data = $this->db->insert_batch(USERINTEREST,$insert_data);	
            }
            
            if(count($user_interests) > 0 && $action=='update') {
                $this->db->where('UserID', $user_id, FALSE);
                $this->db->where_not_in('InterestID', $user_interests);
                $this->db->delete(USERINTEREST);       
            }
        } else  {   // if NO Interest ID SUBMITTED THEN DELETE ALL THE EXISTING Interest FOR GIVEN USER ID
            $this->db->where('UserID', $user_id, FALSE);
            $this->db->delete(USERINTEREST);
        }
        initiate_worker_job('profile_compete_status', array('UserID' => $user_id),'','profile_compete_status');
    }

    /**
     * Used to remove user interest
     * @param type $interest_id
     * @param type $user_id
     */
    function remove_user_interest($interest_id,  $user_id) {
        if (CACHE_ENABLE) {
            $this->cache->delete('usrint_' . $user_id);
        }
        $this->db->where('InterestID', $interest_id, FALSE);
        $this->db->where('UserID', $user_id, FALSE);
        $this->db->delete(USERINTEREST);            
    }

    /**
     * [get_user_interest Used to get user interest]
     * @param  [int] $user_id [User ID]
     * @param  [int] $page_no [Page number]
     * @param  [int] $page_size [Page size]
     * @return [array]        [User interest list]
     */
    function get_user_interest($user_id, $page_no = 1, $page_size = 10, $count_only = false) {        
        if($this->settings_model->isDisabled(31)) {
            if($count_only == false) {
                return array();
            } else {
                return 0;
            }
        }
        $user_interest = array();
        if (CACHE_ENABLE && !$count_only) {
            $user_interest = $this->cache->get('usrint_' . $user_id);
        }
        
        if(empty($user_interest)) {
            $this->db->select('I.InterestID');
            $this->db->select('I.Name', FALSE);
            $this->db->select('IFNULL(I.Icon,"") as Icon', FALSE);
            $this->db->from(USERINTEREST . ' UI');
            $this->db->join(INTEREST . ' I', 'I.InterestID=UI.InterestID AND I.Status = 2');
            $this->db->where('UI.UserID',$user_id, FALSE);

            /* if ($page_no && $page_size && !$count_only) {
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }
            */
            $this->db->order_by('I.Name', 'ASC');
            $query = $this->db->get();
            
            if ($count_only) {
                return $query->num_rows();
            }
            $user_interest = $query->result_array();
            if (CACHE_ENABLE) {
                $this->cache->save('usrint_' . $user_id, $user_interest, CACHE_EXPIRATION);
            }
        }
        return $user_interest;
    }
    
    /**
     * [get_popular_interest Used to get popular interest]
     * @param  [int] $user_id [User ID]
     * @param  [int] $page_no [Page number]
     * @param  [int] $page_size [Page size]
     * @return [array]        [User interest list]
     */
    public function get_popular_interest($user_id, $page_no, $page_size, $keyword = '', $exclude = array()) {
        if($this->settings_model->isDisabled(31)) {
            return array();            
        }
        $select_followers = "(SELECT COUNT(InterestID) FROM " . USERINTEREST . " InterestID=I.InterestID) as Followers";

        $this->db->select('I.InterestID');
        $this->db->select('I.Name', FALSE);
        $this->db->select('IFNULL(I.Icon,"") as Icon', FALSE);
        $this->db->select($select_followers, FALSE);
        $this->db->from(INTEREST . "  I");
        if ($keyword) {
            $keyword = $this->db->escape_like_str($keyword); 
            $this->db->where("I.Name LIKE '%" . $keyword . "%'", null, false);
        }
        if ($exclude) {
            $this->db->where_not_in('I.InterestID', $exclude);
        }
        
        $this->db->where('I.Status',2);
        $this->db->order_by('Followers', 'DESC');
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $query = $this->db->get();
        return $query->result_array();
    }
    
}