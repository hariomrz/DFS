<?php

class Affiliate_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }
    
        /**
     * This function used get affiliate user complete, joined and earning detail
     * @param      
     * @return     [array]
     */
    function get_affiliate_user_detail() {
        $result                     = array();
        $result["total_joined"]     = 0;
        $result["total_bonus_cash"] = 0;
        $result["total_real_cash"]  = 0;
        $result["total_coin_earned"]  = 0;
        $affiliate_type = array(1,19,20,21);
        $query = $this->db->select(
                "sum(case when status = 1 and affiliate_type IN(" . implode(',', $affiliate_type) . ") then 1 else 0 end) as total_joined,
                IFNULL(sum(user_real_cash),0) as total_real_cash,
                IFNULL(sum(user_bonus_cash),0) as total_bonus_cash,
                IFNULL(sum(user_coin),0) as total_coin_earned", FALSE)
                ->from(USER_AFFILIATE_HISTORY)
                ->where("user_id", $this->user_id)
                ->where("is_referral", 1)
                ->get();
        $num = $query->num_rows();
        if($num > 0) {
            $row= $query->row_array();
            $result["total_joined"]         = $row['total_joined'];
            $result["total_real_cash"]      = $row['total_real_cash'];
            $result["total_bonus_cash"]     = $row['total_bonus_cash'];
            $result["total_coin_earned"]    = $row['total_coin_earned'];
        }
        
       /* $query = $this->db->select("IFNULL(sum(friend_real_cash),0) as friend_real_cash,
                IFNULL(sum(friend_bonus_cash),0) as friend_bonus_cash,
                IFNULL(sum(friend_coin),0) as friend_coin_earned", FALSE)
                ->from(USER_AFFILIATE_HISTORY)
                ->where("friend_id", $this->user_id)
                ->where("is_referral", 1)
                ->get();
        $num = $query->num_rows();
        if($num > 0) {
            $row= $query->row_array();
            $result["total_real_cash"]      = $result['total_real_cash'] + $row['friend_real_cash'];
            $result["total_bonus_cash"]     = $result['total_bonus_cash'] + $row['friend_bonus_cash'];
            $result["total_coin_earned"]    = $result['total_coin_earned'] + $row['friend_coin_earned'];
        }
        */
        //echo $this->db->last_query();die;
        return $result;
    }
    
    /**
     * [get_affiliate_master_data description]
     * @MethodName get_affiliate_master_data
     * @Summary This function used get affiliate master data
     * @param      
     * @return     [array]
     */
    function get_affiliate_master_data() {
        $sql = $this->db->select("affiliate_type, invest_money, bonus_amount, real_amount, coin_amount, max_earning_amount, affiliate_description, user_bonus, user_real, user_coin")
                ->from(AFFILIATE_MASTER)
                ->get();
        return $sql->result_array();
    }

    /**
     * This function used get top referrers
     * @param      $post_value
     * @return     [array]
     */
    function get_top_referrers($post_value) {
        $page_no    = isset($post_value['page_no']) ? $post_value['page_no'] : 1;
        $limit      = isset($post_value['page_size']) ? $post_value['page_size'] : 20;
        $offset     = get_pagination_offset($page_no, $limit);
               
        $sort_field = 'total_earning';
        $sort_order = 'DESC';

        if (!empty($post_value['sort_field'])) {
            $sort_field = $post_value['sort_field'];
        }
        if (!empty($post_value['sort_order'])) {
            $sort_order = $post_value['sort_order'];
        }

        $this->db->select("IFNULL(U.first_name,'') as first_name, IFNULL(U.last_name,'') as last_name, U.user_name, sum(UAH.user_bonus_cash + UAH.user_real_cash) as total_earning, U.image", FALSE)
                ->from(USER . " U")
                ->join(USER_AFFILIATE_HISTORY . " UAH", "UAH.user_id = U.user_id", "INNER")
                ->having("total_earning > 0");

        $this->db->group_by("U.user_id");
        $this->db->order_by($sort_field, $sort_order);
        $this->db->limit($limit, $offset);
        $query = $this->db->get();

        return $query->result_array();   
    }

    /**
     * This function used get affiliate my referral list
     * @param      $post_value
     * @return     [array]
     */
    function get_my_referral_list($post_value) {
        $page_no    = isset($post_value['page_no']) ? $post_value['page_no'] : 1;
        $limit      = isset($post_value['page_size']) ? $post_value['page_size'] : 20;
        $offset     = get_pagination_offset($page_no, $limit);
          
        $sort_field = 'created_date';
        $sort_order = 'DESC';

        if (!empty($post_value['sort_field'])) {
            $sort_field = $post_value['sort_field'];
        }
        if (!empty($post_value['sort_order'])) {
            $sort_order = $post_value['sort_order'];
        }

        $this->db->select("UAH.user_affiliate_history_id,UAH.user_id,UAH.friend_id,IFNULL(U.first_name,'') as first_name, IFNULL(U.last_name,'') as last_name,IFNULL(U.user_name,'') as user_name, U.image, IFNULL(U.added_date,'') as added_date,IF(UAH.friend_email!='',UAH.friend_email,U.email) as friend_email,IFNULL(sum(user_real_cash),0) as total_cash_earned,IFNULL(sum(user_bonus_cash),0) as total_bonus_earned,IFNULL(sum(user_coin),0) as total_coin_earned, (CASE WHEN U.user_id IS NOT NULL THEN 1 ELSE 0 END) as is_joined,IFNULL(email_verified,0) as email_verified,IFNULL(pan_verified,0) as pan_verified,IFNULL((CASE WHEN COUNT(O.order_id) > 0 THEN 1 ELSE 0 END),0) as first_deposit,IFNULL(UAH.source_type,0) AS source_type", FALSE)
                ->from(USER_AFFILIATE_HISTORY . " UAH")
                ->join(USER . " U", "U.user_id = UAH.friend_id", "LEFT")
                ->join(ORDER . " O", "U.user_id = O.user_id AND O.source='7' AND O.status='1' AND O.type='0'", "LEFT")
                ->where("UAH.is_referral", 1)
                ->where("UAH.user_id", $this->user_id);
        $this->db->group_by("UAH.friend_id");

        $this->db->order_by($sort_field, $sort_order);
        $this->db->limit($limit, $offset);
        $query = $this->db->get();

        return $query->result_array();
    }
    
    
    /**
     * This function used get user referral list
     * @param      $post_value
     * @return     [array]
     */
    function get_referral_list($post_value) {
        $page_no    = isset($post_value['page_no']) ? $post_value['page_no'] : 1;
        $limit      = isset($post_value['page_size']) ? $post_value['page_size'] : 20;
        $offset     = get_pagination_offset($page_no, $limit);
          
        $sort_field = 'created_date';
        $sort_order = 'DESC';

        if (!empty($post_value['sort_field'])) {
            $sort_field = $post_value['sort_field'];
        }
        if (!empty($post_value['sort_order'])) {
            $sort_order = $post_value['sort_order'];
        }

        $this->db->select("
                UAH.user_affiliate_history_id, UAH.user_id, UAH.friend_id, IFNULL(U.first_name,'') as first_name, IFNULL(U.last_name,'') as last_name,IFNULL(U.user_name,'') as user_name, U.image, IFNULL(U.added_date,'') as added_date,
                IFNULL(sum(user_real_cash),0) as total_cash_earned,
                IFNULL(sum(user_bonus_cash),0) as total_bonus_earned,
                IFNULL(sum(user_coin),0) as total_coin_earned", FALSE)
                ->from(USER_AFFILIATE_HISTORY . " UAH")
                ->join(USER . " U", "U.user_id = UAH.friend_id")                
                ->where("UAH.is_referral", 1)
                ->where("UAH.user_id", $this->user_id);
        $this->db->group_by("UAH.friend_id");
        $this->db->order_by($sort_field, $sort_order);
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    
    /**
     * Used to insert affiliate data
     * @param array $data_array
     * @return int affiliate id
     */
    function add_affiliate_activity($data_array) {
        $data_array["created_date"] = format_date();
        $this->db->insert(USER_AFFILIATE_HISTORY, $data_array);
        return $this->db->insert_id();
    } 
    
    /**
     * Used to get refer by details
     * @param type $user_id
     * @return string
     */
    function get_refer_by_details($user_id) {
        $affiliate_type = array(1,19,20,21);
        $this->db->select("IFNULL(U.first_name,'') as first_name, IFNULL(U.last_name,'') as last_name,IFNULL(U.user_name,'') as user_name, U.image, UAH.user_real_cash, UAH.user_bonus_cash, UAH.user_coin", FALSE)
                ->from(USER_AFFILIATE_HISTORY . " UAH")
                ->join(USER . " U", "U.user_id = UAH.user_id")
                ->where('UAH.friend_id', $user_id)
                ->where('UAH.status', 1)
                ->where_in('UAH.affiliate_type', $affiliate_type);
        $query = $this->db->get();
        if($query->num_rows()) {
            return $query->row_array();
        }
        return '';
    }
    
    /**
     * Used to get user earning by friend wise
     * @param type $friend_id
     * @return array
     */
    function get_user_earning_by_friend($friend_id) {
        $result                     = array();        
        $affiliate_type = array(1,4,5,10,11,12,13,17,19,20,21);
        $sort_field = 'created_date';
        $sort_order = 'ASC';

        $this->db->select("
                 UAH.user_affiliate_history_id, UAH.affiliate_type, AM.affiliate_description, 
                 IFNULL(UAH.user_real_cash,0) as total_cash_earned, 
                 IFNULL(UAH.user_bonus_cash,0) as total_bonus_earned, 
                 IFNULL(UAH.user_coin,0) as total_coin_earned", FALSE)
                ->from(USER_AFFILIATE_HISTORY . " UAH")
                ->join(AFFILIATE_MASTER . " AM", "AM.affiliate_type = UAH.affiliate_type")
                ->where("UAH.is_referral", 1)
                ->where("UAH.user_id", $this->user_id)
                ->where("UAH.friend_id", $friend_id)
                ->where_in('UAH.affiliate_type', $affiliate_type);        
        $this->db->order_by($sort_field, $sort_order);
        $query = $this->db->get();
        $result["referral"] = $query->result_array();
        
        $this->db->select("
                IFNULL(sum(UAH.user_real_cash),0) as total_cash_earned,
                IFNULL(sum(UAH.user_bonus_cash),0) as total_bonus_earned,
                IFNULL(sum(UAH.user_coin),0) as total_coin_earned", FALSE)
                ->from(USER_AFFILIATE_HISTORY . " UAH")
               // ->join(AFFILIATE_MASTER . " AM", "AM.affiliate_type = UAH.affiliate_type")                
                ->where("UAH.is_referral", 1)
                ->where("UAH.user_id", $this->user_id)
                ->where("UAH.friend_id", $friend_id)
                ->where("UAH.affiliate_type", 14);
        $this->db->group_by("UAH.friend_id");
        $query = $this->db->get();
        
        $friends_deposit = array();
        $friends_deposit["total_bonus_cash"] = 0;
        $friends_deposit["total_cash_earned"]  = 0;
        $friends_deposit["total_coin_earned"]  = 0;
        if($query->num_rows()) {
            $row= $query->row_array();
            $friends_deposit["total_cash_earned"]    = $row['total_cash_earned'];
            $friends_deposit["total_bonus_cash"]     = $row['total_bonus_cash'];
            $friends_deposit["total_coin_earned"]    = $row['total_coin_earned'];
        }
        $result["friends_deposit"] = $friends_deposit;
        return $result;
    }
    
    /**
     * This function used get user referral count
     * @param      
     * @return     [int]
     */
    function get_user_referral_count($user_id) {
        $affiliate_type = array(1,19,20,21);
        $query = $this->db->select(
                "sum(case when status = 1 and affiliate_type IN(" . implode(',', $affiliate_type) . ") then 1 else 0 end) as total_joined", FALSE)
                ->from(USER_AFFILIATE_HISTORY)
                ->where("user_id", $user_id)
                ->where("is_referral", 1)
                ->get();
        $num = $query->num_rows();
        $total_joined = 0;
        if($num > 0) {
            $row= $query->row_array();
            $total_joined   = $row['total_joined'];            
        }        
        return $total_joined;
    }
    
    /**
     * Used to get friend deposit bonus by logged in user
     * @param type $friend_id
     * @return array
     */
    function get_friend_deposit_bonus_by_user($user_id, $friend_id) {
        $this->db->select("
                 IFNULL(sum(UAH.user_real_cash),0) as total_cash_earned, 
                 IFNULL(sum(UAH.user_bonus_cash),0) as total_bonus_earned, 
                 IFNULL(sum(UAH.user_coin),0) as total_coin_earned", FALSE)
                ->from(USER_AFFILIATE_HISTORY . " UAH")                
                ->where("UAH.is_referral", 1)
                ->where("UAH.user_id", $friend_id)
                ->where("UAH.friend_id", $user_id)
                ->where('UAH.affiliate_type', 14);
        $this->db->group_by("UAH.friend_id");
        $query = $this->db->get();
        return $query->row_array();
    }
}