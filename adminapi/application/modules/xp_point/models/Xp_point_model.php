<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Xp_point_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
		//Do your magic here
	}

    public function get_level_list() {
		$post = $this->input->post();
		$limit = 100;
		$offset = 0;
		$sort_field	= 'level_number';
    	$sort_order	= 'ASC';
		if(isset($post['items_perpage'])) {
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}

		if(isset($post['sort_field']) && in_array($post['sort_field'],array('level_number', 'start_point', 'end_point'))) {
			$sort_field = $post['sort_field'];
		}

		if(isset($post['sort_order']) && in_array($post['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post['sort_order'];
		}

         $offset	= $limit * $page;
		 $this->db->select('level_pt_id,level_number,start_point,end_point,added_date,updated_date',FALSE)
		->from(XP_LEVEL_POINTS)
		->where("end_point>",0)
		->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->limit($limit,$offset)->get()->result_array();		

		return array('result' => $result,'total' => $total);
	}

    public function get_reward_list() {
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		$sort_field	= 'level_number';
    	$sort_order	= 'ASC';
		if(isset($post['items_perpage'])) {
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}

		if(isset($post['sort_field']) && in_array($post['sort_field'],array('level_number'))) {
			$sort_field = $post['sort_field'];
		}

		if(isset($post['sort_order']) && in_array($post['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post['sort_order'];
		}

        $offset	= $limit * $page;
		$this->db->select("R.reward_id,R.level_number,R.is_coin,R.coin_amt,R.is_cashback,R.cashback_amt,R.cashback_type,R.cashback_amt_cap,R.is_contest_discount,R.discount_percent, R.discount_type, R.discount_amt_cap, B.badge_id, B.badge_name
        ",FALSE)
		->from(XP_LEVEL_REWARDS.' R')
        ->join(XP_BADGE_MASTER.' B','B.badge_id=R.badge_id')
		->where("R.is_deleted",0)
		->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $rewards = $this->db->limit($limit,$offset)->get()->result_array();

		$result = array();
		foreach ($rewards as $key => $value) {
			$value['coins'] = array('allow' => $value['is_coin'], 'amt' => $value['coin_amt']);
			$value['deposit_cashback'] = array('allow' => $value['is_cashback'], 'amt' => $value['cashback_amt'], 'type' => $value['cashback_type'], 'cap' => $value['cashback_amt_cap']);
			$value['joining_cashback'] = array('allow' => $value['is_contest_discount'], 'amt' => $value['discount_percent'], 'type' => $value['discount_type'], 'cap' => $value['discount_amt_cap']);         
			
			unset($value['is_coin']);
			unset($value['coin_amt']);
			unset($value['is_cashback']);
			unset($value['cashback_amt']);
			unset($value['cashback_type']);
			unset($value['cashback_amt_cap']);

			unset($value['is_contest_discount']);
			unset($value['discount_percent']);
			unset($value['discount_type']);
			unset($value['discount_amt_cap']);

			$result[] = $value;
		}

		return array('result' => $result,'total' => $total);
	}

	function get_added_levels()
	{
		$result =$this->db->select('level_number,end_point',FALSE)
		->from(XP_LEVEL_POINTS)
		->where("end_point>",0)->get()->result_array();

		return $result;
	}

	public function add_level($data)
	{
		$this->db->insert(XP_LEVEL_POINTS,$data);
		return $this->db->insert_id();
	}

	public function update_all_levels($update_data)
	{
		$this->db->update_batch(XP_LEVEL_POINTS,$update_data, 'level_number'); 
	}

	/**
     * Used to delete level point
     */
	function delete_level($level) {
		
		//delete level reward
		$this->db->set('is_deleted', 1, FALSE);
        $this->db->where('level_number',$level['level_number']);
		$this->db->update(XP_LEVEL_REWARDS);

		$this->db->where("level_pt_id",$level['level_pt_id'])
				->delete(XP_LEVEL_POINTS); 
		
		
        return TRUE;
	}

	/**
     * Used to get badge master list
     */
	function get_badge_master_list() {
		$result =$this->db->select('badge_id, badge_name, badge_icon',FALSE)
		->from(XP_BADGE_MASTER)
		->get()->result_array();

		return $result;
	}

	/**
     * Used to add rewards
     */
	function add_reward($data) {
		$this->db->insert(XP_LEVEL_REWARDS,$data);
		return $this->db->insert_id();
	}

	/**
	 * Used to update reward
	 */
	function update_reward($data, $reward_id) {
		$where = ['reward_id' => $reward_id];
        $this->db->update(XP_LEVEL_REWARDS,$data,$where);
	}

	/**
     * Used to delete reward 
     */
	function delete_reward($reward_id) {
		$this->db->set('is_deleted', 1, FALSE);
        $this->db->where('reward_id',$reward_id);
        $this->db->update(XP_LEVEL_REWARDS);	
        return TRUE;
	}

	/**
     * Used to check reward deletion check
     */
	function check_rewards_deletion($reward_id) {
		$this->db->select('R.reward_id');
		$this->db->from(XP_LEVEL_REWARDS.' R');
        $this->db->join(XP_LEVEL_POINTS.' XLP','XLP.level_number=R.level_number');
		$this->db->join(XP_USERS.' XU','XU.level_id=XLP.level_pt_id');
		$this->db->where('R.reward_id',$reward_id);
		$this->db->where('R.is_deleted',0);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
     * Used to get activities master list
     */
	function get_activities_master_list() {
		$this->db->select('am.activity_master_id, am.activity_title, am.activity_type');
		$this->db->from(XP_ACTIVITY_MASTER.' am');
		//$this->db->join(XP_ACTIVITIES . ' a', 'a.activity_master_id = am.activity_master_id and a.is_deleted=0','LEFT');
		//$this->db->where('a.activity_id IS NULL', NULL, FALSE);
		$query = $this->db->get();
		return $query->result_array();
	}

	/**
     * Used to add activity
     */
	function add_activity($data) {
		$this->db->insert(XP_ACTIVITIES,$data);
		return $this->db->insert_id();
	}

	/**
	 * Used to update activity
	 */
	function update_activity($data, $activity_id) {
		$where = ['activity_id' => $activity_id];
        $result = $this->db->update(XP_ACTIVITIES,$data,$where);
	}

	/**
	 * Used to get activities list
	 */
	function get_activities_list() {
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		$sort_field	= 'activity_title';
    	$sort_order	= 'ASC';
		if(isset($post['items_perpage'])) {
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}

		if(isset($post['sort_field']) && in_array($post['sort_field'],array('activity_title'))) {
			$sort_field = $post['sort_field'];
		}

		if(isset($post['sort_order']) && in_array($post['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post['sort_order'];
		}

        $offset	= $limit * $page;

		$this->db->select('a.activity_id, am.activity_title, am.activity_type, a.xp_point, a.recurrent_count');
		$this->db->from(XP_ACTIVITIES.' a');
		$this->db->join(XP_ACTIVITY_MASTER . ' am', 'am.activity_master_id = a.activity_master_id');
		$this->db->where('a.is_deleted', 0);
		$this->db->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->limit($limit,$offset)->get()->result_array();

		return array('result' => $result,'total' => $total);
	}

	/**
     * Used to delete activity 
     */
	function delete_activity($activity_id) {
		$this->db->set('is_deleted', 1, FALSE);
        $this->db->where('activity_id',$activity_id);
        $this->db->update(XP_ACTIVITIES);
        return TRUE;
	}

	/**
     * Used to get level leaderboard 
     */
	function level_leaderboard() {
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		$sort_field	= 'user_rank';
    	$sort_order	= 'ASC';
		if(isset($post['items_perpage'])) {
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}

		if(isset($post['sort_field']) && in_array($post['sort_field'],array('user_rank', 'user_name','level_number','points'))) {
			$sort_field = $post['sort_field'];
		}

		if(isset($post['sort_order']) && in_array($post['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post['sort_order'];
		}

        $offset	= $limit * $page;

		$this->db->select('sum(xu.point) as points');
		$this->db->select('xlp.level_number, u.user_name,u.user_id');
		$this->db->select("RANK() OVER (ORDER BY sum(xu.point) DESC) user_rank",FALSE);
		$this->db->from(XP_USERS.' xu');
		$this->db->join(XP_LEVEL_POINTS.' xlp','xlp.level_pt_id=xu.level_id');
		$this->db->join(USER . ' u', 'u.user_id = xu.user_id');
		if(isset($post['level_number']) && !empty($post['level_number'])) {
			$this->db->where('xlp.level_number', $post['level_number']);
		}
		$this->db->group_by('xu.user_id');
		$this->db->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->limit($limit,$offset)->get()->result_array();

		if($total > 0) {
			$this->load->helper('xppoint_helper');
			$levels = get_master_levels();
			foreach($result as &$row) {
				$row['level_str'] = $levels[$row['level_number']];
			}
		}

		return array('result' => $result,'total' => $total);
	}

	/**
     * Used to get activities leaderboard 
     */
	function activities_leaderboard() {
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		$sort_field	= 'user_rank';
    	$sort_order	= 'ASC';
		if(isset($post['items_perpage'])) {
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}

		if(isset($post['sort_field']) && in_array($post['sort_field'],array('user_rank', 'user_name','points'))) {
			$sort_field = $post['sort_field'];
		}

		if(isset($post['sort_order']) && in_array($post['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post['sort_order'];
		}

        $offset	= $limit * $page;

		$this->db->select('sum(xuh.point) as points');
		$this->db->select('u.user_name');
		$this->db->select("RANK() OVER (ORDER BY sum(xuh.point) DESC) user_rank",FALSE);
		$this->db->from(XP_USER_HISTORY.' xuh');
		$this->db->join(USER . ' u', 'u.user_id = xuh.user_id');
		$this->db->where('xuh.activity_id', $post['activity_id']);		
		$this->db->group_by('xuh.user_id');
		$this->db->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->limit($limit,$offset)->get()->result_array();

		return array('result' => $result,'total' => $total);
	}

	function get_user_xp_history($user_id) {
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		$sort_field	= 'XUH.added_date';
    	$sort_order	= 'DESC';
		if(isset($post['items_perpage'])) {
			$limit = $post['items_perpage'];
		}

		if(isset($post['sort_field']) && in_array($post['sort_field'],array('added_date','point'))) {
			$sort_field = 'XUH.'.$post['sort_field'];
		}

		if(isset($post['sort_order']) && in_array($post['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post['sort_order'];
		}


		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}

        $offset	= $limit * $page;
        $this->db->select('XA.activity_id,XAM.activity_type,XAM.activity_title,XUH.point,XUH.added_date')
        ->from(XP_USER_HISTORY.' XUH')
        ->join(XP_ACTIVITIES.' XA','XA.activity_id=XUH.activity_id')
        ->join(XP_ACTIVITY_MASTER.' XAM','XAM.activity_master_id=XA.activity_master_id')
        ->where('XAM.status',1)
        ->where('XA.is_deleted',0)
        ->where('XUH.user_id',$user_id)
        ->order_by($sort_field,$sort_order);
		

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->limit($limit,$offset)->get()->result_array();

		return array('result' => $result,'total' => $total);
    }

	function get_user_xp($user_id)
	{
		return $result = $this->db->select('point')
        ->from(XP_USERS)
		->where('user_id',$user_id)
		->get()->row_array();

	}
}