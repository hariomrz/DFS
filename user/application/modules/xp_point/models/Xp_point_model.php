<?php

class Xp_point_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Used to get reward list.
     */
    function get_reward_list() {
        $sort_field	= 'level_number';
        $sort_order	= 'ASC';
        $result=  $this->db->select("R.reward_id,R.is_coin,R.coin_amt,R.is_cashback,R.cashback_amt,R.cashback_type,R.cashback_amt_cap,R.is_contest_discount,R.discount_percent, R.discount_type, R.discount_amt_cap, B.badge_id, B.badge_name,XLP.level_number,XLP.start_point,XLP.end_point,B.badge_icon
        ",FALSE)
        ->from(XP_LEVEL_POINTS.' XLP')
        ->join(XP_LEVEL_REWARDS.' R','R.level_number=XLP.level_number and R.is_deleted=0', 'LEFT')
        ->join(XP_BADGE_MASTER.' B','B.badge_id=R.badge_id', 'LEFT') 
		->order_by($sort_field, $sort_order)
        ->get()->result_array();
        return $result;
    }

    /**
     * Used to get user xp information.
     */
    function get_user_xp($user_id) {
        $this->db->select('XU.user_id,XU.point,XU.level_id,XLP.level_number,XLP.start_point')
        ->from(XP_USERS.' XU')
        ->join(XP_LEVEL_POINTS.' XLP','XLP.level_pt_id=XU.level_id','LEFT')
        ->where('user_id',$user_id);
        $result = $this->db->get()->row_array();
        if(empty($result)) {
            $result = $this->assign_level($user_id);
        }
        return $result;
    }

    function assign_level($user_id) {
        //prepare xp_user data

        $result = $this->db->select('level_pt_id')
        ->from(XP_LEVEL_POINTS)
        ->where('level_number', 1)
        ->order_by('level_pt_id','asc')
        ->limit(1)->get()->row_array();
        $xp_user_data = array();
        if($result) {
            $current_date = format_date();
            $custom_data  = array();
            $xp_user_data['user_id'] = $user_id;
            $xp_user_data['point'] = 0;            
            $xp_user_data['level_id'] = $result['level_pt_id'];

            $custom_data['level_ids'] = array((int)$result['level_pt_id']);
            $xp_user_data['custom_data'] = json_encode($custom_data);
            $xp_user_data['update_date'] = $current_date;
            $xp_user_data['added_date'] = $current_date; 
      
            $this->db->insert(XP_USERS,$xp_user_data);

            $xp_user_data['level_number'] = 1;
            unset($xp_user_data['update_date']);
            unset($xp_user_data['added_date']);
            unset($xp_user_data['custom_data']);
        }
        return $xp_user_data;
    }

    /**
     * Used to get user xp information.
     */
    function get_user_badge($level_number, $user_id) {
        $this->db->select('B.badge_id, B.badge_name,B.badge_icon')
        ->from(XP_LEVEL_REWARDS.' R')
        ->join(XP_REWARD_HISTORY.' RH','RH.reward_id=R.reward_id AND RH.user_id='.$user_id)
        ->join(XP_BADGE_MASTER.' B','B.badge_id=R.badge_id')
        ->where('R.level_number',$level_number);
        $this->db->order_by('RH.reward_history_id', 'DESC');
        $this->db->limit(1);
        //->where('R.is_deleted', 0);
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * Used to get activity list.
     */
    function get_activity_list() {
        $this->db->select('XA.activity_id,XA.recurrent_count,XA.xp_point,XAM.activity_type,XAM.activity_title,XAM.activity_master_id')
        ->from(XP_ACTIVITY_MASTER.' XAM')
        ->join(XP_ACTIVITIES.' XA','XAM.activity_master_id=XA.activity_master_id')
        ->where('XAM.status',1)
        ->where('XA.is_deleted',0)
        ->order_by('XA.xp_point','ASC');
        $result = $this->db->get()->result_array();
       
        return $result;
    }

    /**
     * Used to get next level infor for user.
     */
    function get_next_level($current_level_id) {
        $result = $this->db->select('start_point as next_level_start_point,level_number as next_level')
        ->from(XP_LEVEL_POINTS)
        ->where('level_pt_id>',$current_level_id)
        ->order_by('level_pt_id','asc')
        ->limit(1)->get()->row_array();

        return $result;
    }

    function get_max_level() {
        $result = $this->db->select('MAX(level_number) as max_level,MAX(end_point) as max_end_point',FALSE)
        ->from(XP_LEVEL_POINTS)
        ->limit(1)->get()->row_array();

        return $result;
    }

    /**
     * Used to get user earn point history
     */
    function get_user_xp_history($offset, $limit) {
        $this->db->select('XA.activity_id,XAM.activity_type,XAM.activity_title,XUH.point,XUH.added_date')
        ->from(XP_USER_HISTORY.' XUH')
        ->join(XP_ACTIVITIES.' XA','XA.activity_id=XUH.activity_id')
        ->join(XP_ACTIVITY_MASTER.' XAM','XAM.activity_master_id=XA.activity_master_id')
        ->where('XAM.status',1)
        ->where('XA.is_deleted',0)
        ->where('XUH.user_id',$this->user_id)
        ->order_by('history_id','DESC')
        ->limit($limit, $offset);
        $result = $this->db->get()->result_array();
       
        return $result;
    }
}