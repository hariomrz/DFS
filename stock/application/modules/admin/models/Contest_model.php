<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contest_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_user	= $this->load->database('user_db', TRUE);
	}

    /**
	 * [save_contest description]
	 * @MethodName save_contest
	 * @Summary This function used to create new contest
	 * @param      array  data array
	 * @return     int
	 */
	public function save_contest($contest_data)
	{	
		$contest_data['added_date']			= format_date();
		$contest_data['modified_date']		= format_date();
		$this->db->insert(CONTEST,$contest_data);
		$contest_id = $this->db->insert_id();
        return $contest_id;
		
	}

	/**
	 * Used to get contest report
	 * @return     [array]
	 */
	public function get_completed_contest_report($post_data) { 
		$sort_field = 'C.scheduled_date';
		$sort_order = 'DESC';
		$limit      = 50;
		$page       = 0;
		
		if(isset($post_data['items_perpage'])) {
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page'])) {
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','size','entry_fee','scheduled_date','prize_pool','guaranteed_prize','total_user_joined','minimum_size','site_rake'))) {
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		$this->db->select("C.group_id, C.category_id, C.contest_id, C.contest_unique_id, C.contest_name, C.entry_fee, C.prize_pool, C.site_rake, C.total_user_joined, C.size, C.minimum_size, C.currency_type, C.max_bonus_allowed");
		$this->db->select("C.entry_fee*C.total_user_joined as total_entry_fee",false);
		$this->db->select("(CASE 
			WHEN C.guaranteed_prize=0 THEN 'No Guarantee'
		 	WHEN C.guaranteed_prize=1 THEN 'Guaranteed prize custom'
		 	WHEN C.guaranteed_prize=2 THEN 'Guaranteed'
		 	END
		 ) AS guaranteed_prize,CM.stock_type");
		$this->db->select("C.scheduled_date AS scheduled_date",false);
		$this->db->from(CONTEST." AS C");
		$this->db->join(COLLECTION." AS CM","CM.collection_id = C.collection_id","INNER");
		$this->db->where('C.status',3);
		
		if(isset($post_data['group_id']) && !empty($post_data['group_id'])) {
			$this->db->where('C.group_id',$post_data['group_id'], FALSE);
		}
		if(isset($post_data['category_id']) && !empty($post_data['category_id'])) {
			$this->db->where('C.category_id',$post_data['category_id'], FALSE);
		}

		if(isset($post_data['keyword']) && !empty($post_data['keyword'])) {
			$this->db->like('C.contest_name',$post_data['keyword']);
		}

	    if(!empty($post_data['from_date']) && !empty($post_data['to_date'])) {
			$this->db->where("DATE_FORMAT(C.scheduled_date,'%Y-%m-%d') >= '".format_date($post_data['from_date'],'Y-m-d')."' and DATE_FORMAT(C.scheduled_date,'%Y-%m-%d') <= '".format_date($post_data['to_date'],'Y-m-d')."'");
		}
			
		if(in_array($post_data['stock_type'], [1,2,3,4]))
		{
			$this->db->where('CM.stock_type',$post_data['stock_type']);
		}	

		$tempdb = clone $this->db;
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows(); 

		if(!empty($sort_field) && !empty($sort_order)) {
			$this->db->order_by($sort_field, $sort_order);
		}

		if(!empty($limit) && !$post_data["csv"]) {
			$this->db->limit($limit, $offset);
		}
		$query = $this->db->get();
		// echo $this->db->last_query();die;
		$result	= array();
		if($query->num_rows()) {
			$category_list = $this->get_category_list();
			$category_list = array_column($category_list, 'name', 'category_id');
			$result	= $query->result_array();
			foreach($result as $key=>$contest) {
				$group = $this->db->select('group_name')->from(MASTER_GROUP)->where('group_id',$contest['group_id'])->get()->row_array();
				$result[$key]['group_name'] = $group['group_name'];
				$result[$key]['category_name'] = $category_list[$contest['category_id']];
			}
		}
		return array('result'=>$result, 'total'=>$total);
	}

	/**
	 * Used to get contests prize details
	 */
	public function get_contest_prize_detail($contest_ids) {

		$this->db_user->select("(ROUND(IFNULL(sum(IF(ORD.source=460,ORD.real_amount,0)),'0'),2)+ROUND(IFNULL(sum(IF(ORD.source=460,ORD.winning_amount,0)),'0'),2)) as total_join_real_amount,			

		ROUND(IFNULL(sum(IF(ORD.source=460,ORD.bonus_amount,0)),'0'),2) as total_join_bonus_amount,			

		ROUND(IFNULL(sum(IF(ORD.source=460,ORD.points,0)),'0'),2) as total_join_coin_amount,
		ROUND(IFNULL(sum(IF(ORD.source=460,ORD.winning_amount,0)),'0'),2) as total_join_winning_amount,

		ROUND(IFNULL(sum(IF(ORD.source=462,ORD.winning_amount,0)),'0'),2) as total_win_winning_amount,
		ROUND(IFNULL(sum(IF(ORD.source=462,ORD.points,0)),'0'),2) as total_win_coins,
		ROUND(IFNULL(sum(IF(ORD.source=462,ORD.bonus_amount,0)),'0'),2) as total_win_bonus,
		ROUND(IFNULL(sum(IF(ORD.source=462 AND U.is_systemuser=0,ORD.winning_amount,0)),'0'),2) as total_win_amount_to_real_user,
		ORD.reference_id as contest_id",FALSE);
		$this->db_user->from(ORDER." AS ORD");
		$this->db_user->join(USER.' U','U.user_id=ORD.user_id',"INNER");
		$this->db_user->where('(ORD.source = 460 or ORD.source = 462) AND ORD.status = 1'); // join game  AND Debit and success		
		$this->db_user->where_in('ORD.reference_id',$contest_ids);	
		$this->db_user->group_by('ORD.reference_id');
		$query = $this->db_user->get();

		$result = array();
		if($query->num_rows()) {
			$result = $query->result_array();
		}
		return $result;					
	}

	/**
	 * Used to get contest promo code earning
	 */
	public function get_contest_promo_code_entry($contest_uids) {
		$query = $this->db_user->select("ROUND(IFNULL(sum(amount_received),'0'),2) as promocode_entry_fee_real,contest_unique_id",FALSE)
				->from(PROMO_CODE_EARNING)
				->where_in('contest_unique_id',$contest_uids)		
				->group_by('contest_unique_id')
				->get()->result_array();
		return $query;					
	}

	/**
	 * [get_contest_list Used to get contest list]
	 * @return [array] [contest list]
	 */
	public function get_contest_list($post_data) {
		$current_time = format_date();
		$sort_field = 'C.contest_id';
		$sort_order = 'DESC';
		$limit      = 50;
		$page       = 0;
		
		if(isset($post_data['items_perpage'])) {
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page'])) {
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_id','contest_name','entry_fee','minimum_size','size','total_user_joined','prize_pool','max_bonus_allowed','spot_left','system_teams','real_teams','current_earning','potential_earning'))) {
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;

		
		$status = isset($post_data['status'])?$post_data['status']:"";
		
		$this->db->select("C.category_id, C.status, C.contest_id, C.contest_unique_id, C.scheduled_date, CM.end_date, C.contest_name, C.entry_fee, C.guaranteed_prize, C.total_user_joined, C.size, C.minimum_size, C.currency_type, C.max_bonus_allowed, C.is_auto_recurring, C.multiple_lineup,C.site_rake");
		$this->db->select("(C.size-C.total_user_joined) as spot_left",FALSE);
		$this->db->select("(C.entry_fee*COUNT(DISTINCT LM.lineup_master_id)) as current_earning",FALSE);
		$this->db->select("(C.size-C.total_user_joined)*C.entry_fee as potential_earning",FALSE);
		$this->db->select("COUNT(DISTINCT LM.lineup_master_id) as team_count",FALSE);

		$this->db->from(CONTEST." AS C")
			->join(COLLECTION." AS CM", 'CM.collection_id = C.collection_id','INNER')
			->join(LINEUP_MASTER_CONTEST." AS LMC", 'LMC.contest_id = C.contest_id','LEFT')
			->join(LINEUP_MASTER." AS LM", 'LM.lineup_master_id = LMC.lineup_master_id','LEFT')
			->group_by('C.contest_id');

		
		if(isset($post_data['group_id']) && !empty($post_data['group_id'])) {
			$this->db->where('C.group_id',$post_data['group_id'], FALSE);
		}
		if(isset($post_data['category_id']) && !empty($post_data['category_id'])) {
			$this->db->where('C.category_id',$post_data['category_id'], FALSE);
		}

		if(isset($post_data['keyword']) && !empty($post_data['keyword'])) {
			$this->db->like('C.contest_name',$post_data['keyword']);
		}

		$stock_type = 1;
		if(!empty($post_data['stock_type']))
		{
			$stock_type = $post_data['stock_type'];
		}
		$this->db->where('CM.stock_type',$stock_type);
		switch ($status) {
			case 'current_game':
				$this->db->where('C.status','0');
				$this->db->where("C.scheduled_date < ", $current_time);
				break;
			case 'completed_game':
				$this->db->where('C.status >','1');
				break;
			case 'cancelled_game':
				$this->db->where('C.status','1');
				break;
			case 'upcoming_game':
				$this->db->where('C.status','0');
				$this->db->where("C.scheduled_date > ", $current_time);
				break;
			default:
				break;
		}
		$tempdb = clone $this->db;
        $temp_q = $tempdb->get();
		$total = $temp_q->num_rows();

		if(!empty($sort_field) && !empty($sort_order)) {
			$this->db->order_by($sort_field, $sort_order);
		}

		if(!empty($limit)) {
			$this->db->limit($limit, $offset);
		}
		
		$query = $this->db->get();
		$result	= array();
		if($query->num_rows()) {
			$category_list = $this->get_category_list();
			$category_list = array_column($category_list, 'name', 'category_id');
			$result	= $query->result_array();
			foreach($result as $key=>$contest) {
				$result[$key]['category_name'] = $category_list[$contest['category_id']];

				unset($result[$key]['category_id']);
			}
		}
		return array('result'=>$result, 'total'=>$total);
	}

	/**
	 * Used to get contest winners 
	 */
	public function export_contest_winner_data() {	
		$post = $this->input->get();		
		$contest_id = $post['contest_id'];

		$return = array(
						'contest_name'=> '',
						'winner_name'=> '',
						'rank'=> 0,
						'entry_fee'=> 0,
						'total_entries'=> 0,
						'winning_amount'=> 0,
						"winning_bonus" => 0,
						"winning_coins" => 0,
						"winning_merchandise" => ''
						);

		$this->db->select("C.contest_name, C.entry_fee, C.total_user_joined as total_entries");
		$this->db->select("LM.user_name as winner_name, LMC.game_rank as rank_value, LMC.total_score");
		$this->db->select("
		(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].amount')) ELSE 0 END) as winning_amount,
		(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].amount')) ELSE 0 END) as winning_bonus,
		(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].amount')) ELSE 0 END) as winning_coins,
		(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].name')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].name')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].name')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].name')) ELSE NULL END) as winning_merchandise
		",FALSE);
		$this->db->from(CONTEST . " AS C");
		$this->db->join(LINEUP_MASTER_CONTEST . " AS LMC", "LMC.contest_id = C.contest_id", "INNER");
		$this->db->join(LINEUP_MASTER.' LM', 'LM.lineup_master_id = LMC.lineup_master_id','LEFT');
		$this->db->where('C.contest_id',$contest_id, FALSE);
		$this->db->order_by('rank_value','ASC');
		$result = $this->db->get()->result_array();
				
		if(empty($result)) {
			return $return;
		}
		return $result;
	}

	/**
	 * [get_fixture_contest description]
	 * Summary :- 
	 * @return [type] [description]
	 */
	public function get_fixture_contest($post_data)
	{ 
		$sort_field = 'C.group_id';
		$sort_order = 'ASC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','minimum_size','size','entry_fee','prize_pool','is_auto_recurring','guaranteed_prize','max_bonus_allowed','added_date')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}


		$this->db->select('C.contest_id,C.status,C.contest_unique_id,C.template_id,C.collection_id,C.group_id,C.scheduled_date,C.minimum_size,C.size,C.contest_name,C.currency_type,C.entry_fee,C.prize_pool,C.total_user_joined,C.prize_distibution_detail,C.multiple_lineup,C.prize_type, C.guaranteed_prize,C.is_auto_recurring,C.is_pin_contest,MG.group_name,C.sponsor_name,C.sponsor_logo,C.sponsor_link,IFNULL(C.contest_title,"") as contest_title,C.max_bonus_allowed', FALSE)
			->from(CONTEST." AS C")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = C.group_id','INNER')
			->join(COLLECTION." AS CM", 'CM.collection_id = C.collection_id','INNER')
			->group_by('C.contest_id');
		if(isset($post_data['collection_id']) && $post_data['collection_id'] != "")
		{
			$this->db->where('CM.collection_id', $post_data['collection_id']);
		}
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('C.contest_name', $post_data['keyword']);
		}
		
		$this->db->order_by('C.group_id','DESC');
		$sql = $this->db->order_by($sort_field, $sort_order)
						->get();

		$result	= $sql->result_array();
		
		return array('result' => $result, 'total' => count($result));
	}

	/**
	 * Used to delete contest
	 */
	public function delete_contest($data) {
		$this->db->where("contest_id",$data['contest_id']);
		$this->db->where("total_user_joined","0");
		$this->db->delete(CONTEST);
		$is_deleted = $this->db->affected_rows();
		return $is_deleted;
	}

	public function get_match_paid_free_users($collection_id)
	{
		$rs = $this->db->select("COUNT(LMC.lineup_master_contest_id) as total_users,IFNULL(SUM(IF(C.entry_fee=0,1,0)),0) as free_users,
		IFNULL(SUM(IF(C.entry_fee>0,1,0)),0)  as paid_users", FALSE)
						->from(LINEUP_MASTER_CONTEST .' LMC')
						->join(CONTEST. ' C',"LMC.contest_id=C.contest_id")	
						->where("C.collection_id", $collection_id)
						->where_in("C.status", array(0,2,3))
						->where('C.total_user_joined>',0)
						->get();
		$result = $rs->row_array();
		return $result;
	}

	public function get_game_detail($post_data)
	{
		 $this->db->select('G.max_bonus_allowed,G.prize_distibution_detail,G.user_id,G.collection_id,G.scheduled_date,G.contest_id, G.contest_unique_id, G.contest_name, DATE_FORMAT(G.scheduled_date,"'.MYSQL_DATE_TIME_FORMAT.'") as scheduled_date, G.size, G.entry_fee,G.currency_type,G.prize_pool,G.status,DATE_FORMAT(G.added_date,"'.MYSQL_DATE_TIME_FORMAT.'") as added_date,G.site_rake, G.total_user_joined,G.guaranteed_prize,G.guaranteed_prize as prize_pool_type,G.is_pin_contest,G.prize_type,G.is_custom_prize_pool,G.multiple_lineup,IFNULL(DATE_FORMAT(G.completed_date,"'.MYSQL_DATE_TIME_FORMAT.'"),"") as completed_date,IFNULL(G.contest_title,"") as contest_title,G.is_tie_breaker,CM.name,CM.end_date,G.brokerage')
						->from(CONTEST . " AS G")
						->join(COLLECTION." AS CM","CM.collection_id = G.collection_id","LEFT");

		if(!empty($post_data['contest_unique_id']))		
		{
			$this->db->where('G.contest_unique_id', $post_data['contest_unique_id']);
		}		

		if(!empty($post_data['contest_id']))		
		{
			$this->db->where('G.contest_id', $post_data['contest_id']);
		}		
						
                // echo $this->db_fantasy->last_query();die;
		$result = $this->db->get()->row_array();
		return $result;
	}

	public function get_lineup_by_game()
	{
		$limit      = 10;
		$page       = 0;
		$post_data = $this->input->post();
		$sort_field = "LMC.total_score";
		$sort_order = "DESC";
		
		if(isset($post_data['sort_field']))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']))
		{
			$sort_order = $post_data['sort_order'];
		}

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}
		
		$offset	= $limit * $page;
		$this->db->select("LM.user_name,LM.team_name,LM.lineup_master_id,LM.user_id,(CASE WHEN C.stock_type=3 THEN LMC.percent_change  ELSE LMC.total_score END) as total_score,LMC.game_rank,LMC.lineup_master_contest_id,LMC.contest_id,LM.collection_id,LMC.prize_data,LMC.is_winner",FALSE)
						->from(LINEUP_MASTER_CONTEST." AS LMC")
						->join(LINEUP_MASTER.' LM','LMC.lineup_master_id = LM.lineup_master_id','LEFT')
						->join(COLLECTION.' C','C.collection_id = LM.collection_id','LEFT')
						->where("LMC.contest_id", $post_data['game_id']);
		if(isset($post_data['user_id']) && $post_data['user_id'] != ""){
			$this->db->where("LM.user_id", $post_data['user_id']);
		}
		
		$tempdb = clone $this->db;
		//$total = $this->get_total('LM.lineup_master_id');
		$query = $this->db->get();
		$total = $query->num_rows();

		$sql = $tempdb->limit($limit, $offset)
						->order_by($sort_field,$sort_order)
						->get();
		//echo $tempdb->last_query();die;
		$result	= $sql->result_array();

		$result = ($result) ? $result : array();
		return array('result'=>$result, 'total'=>$total);
	}

	function get_contest_collection_details_by_lmc_id($lineup_master_contest_id,$fields = "*")
	{
		return $this->db->select($fields)
				->from(LINEUP_MASTER_CONTEST." LMC")
				->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
				->join(CONTEST." C","LMC.contest_id = C.contest_id","INNER")
				->join(COLLECTION." CM","CM.collection_id = C.collection_id","INNER")
				->where("LMC.lineup_master_contest_id",$lineup_master_contest_id)
				->limit(1)
				->get()
				->row_array();
	}

	public function get_all_stocks($post_data)
	{
        /**
         * collection season stock name, lot size
         * stock history  stock history  last price(x) of publish date of collection
         * stocktable  => last price(y) as current price, 
         * x-y  = example +20.22
         * flag for wish list is_wish_list
         * 
         * market table=> give master configuration 
         * ***/

		$collection_id = $post_data['collection_id'];
		//(TRUNCATE((CASE WHEN C.status=1 THEN SH2.close_price ELSE S.last_price END) 
		$published_date = date('Y-m-d',strtotime($post_data['published_date']));
		$scheduled_date = date('Y-m-d',strtotime($post_data['scheduled_date']));
		$end_date = date('Y-m-d',strtotime($post_data['end_date']));
		$this->db->select('CS.stock_id,CS.stock_name,CS.lot_size,IFNULL(SH1.close_price,0) as last_price ,S.last_price as current_price,0 as is_wish,(IFNULL(S.last_price - SH1.close_price,0)) as price_diff,IFNULL(S.logo,"") as logo,S.display_name,
		TRUNCATE(IFNULL(SH1.close_price,0),2) as closing_rate,
		TRUNCATE((CASE WHEN C.status=1 THEN SH2.close_price ELSE S.last_price END),2) as result_rate,
		TRUNCATE(IFNULL(SH1.close_price,0),2) as joining_rate', FALSE)
			->from(COLLECTION_STOCK . " AS CS")
			->join(COLLECTION.' C','C.collection_id = CS.collection_id','INNER')
	        ->join(STOCK.' S', "S.stock_id = CS.stock_id", 'LEFT')
			->join(STOCK_HISTORY.' SH1',"SH1.stock_id=CS.stock_id AND SH1.schedule_date='{$published_date}'",'LEFT')
			->join(STOCK_HISTORY.' SH2',"SH2.stock_id=CS.stock_id AND SH2.schedule_date='{$end_date}'",'LEFT');
	   
            $date ='';//last working day before of publish date in collection
        $this->db->where("CS.collection_id",$collection_id)
                //->where("S.status",1)
		        ->group_by('CS.stock_id')
		        ->order_by('CS.stock_name','ASC');

		$sql = $this->db->get();
		$result	= $sql->result_array();
        //echo $this->db->last_query();die('dsds');
		return $result;
	}

	/**
     * used for get user team players list
     * @param int $lineup_master_contest_id
     * @param array $contest_info
     * @return array
     */
    public function get_lineup_with_score($lineup_master_contest_id, $contest_info) {
        if (!$lineup_master_contest_id || empty($contest_info)) {
            return false;
        }

        $result = array();
        if ( $contest_info['is_lineup_processed'] == 1) {
            $this->db->select("L.lineup_master_id,L.stock_id,L.type,ROUND(IFNULL(L.score,0),1) AS score,L.captain,L.user_lot_size", FALSE)
                    ->from(LINEUP_MASTER_CONTEST . " LMC")
                    ->join(LINEUP . " L", "LMC.lineup_master_id = L.lineup_master_id", "INNER")
                    ->where('LMC.lineup_master_contest_id', $lineup_master_contest_id);
            $result = $this->db->get()->result_array();
        }
        return $result;
    }

	/**
	 * Used to get total contest joined by user
	 */
	function get_contest_joined(){		
		$post = $this->input->post();		
		if (isset($post['from_date'])) {
			$startDate = date("Y-m-d", strtotime($post['from_date']));
		} else {
			$startDate = date('Y-m-d', strtotime('-10 day'));
		}
            
        if (isset($post['to_date'])) {
            $endDate = date("Y-m-d", strtotime($post['to_date']));
        } else {
			$endDate = date("Y-m-d");
		}	
			
		$this->db->select("count(lm.user_id) as contest_joined")
			->from(LINEUP_MASTER .' lm')
			->join(LINEUP_MASTER_CONTEST .' lmc','lmc.lineup_master_id=lm.lineup_master_id')
			->where('user_id', $post['user_id'], FALSE)
			->where("lm.added_date BETWEEN '{$startDate}' AND '{$endDate}'");

			$query = $this->db->get();
			$result = $query->result();
			return $result;
	}

	/**
	 * Used to get total contest won by user
	 */
	function get_contest_won(){		
		$post = $this->input->post();		
		if (isset($post['from_date'])) {
			$startDate = date("Y-m-d", strtotime($post['from_date']));
		} else {
			$startDate = date('Y-m-d', strtotime('-10 day'));
		}
            
        if (isset($post['to_date'])) {
            $endDate = date("Y-m-d", strtotime($post['to_date']));
        } else {
			$endDate = date("Y-m-d");
		}
			
		$this->db->select("count(lm.user_id) as contest_won")
						->from(LINEUP_MASTER .' lm')
						->join(LINEUP_MASTER_CONTEST .' lmc','lmc.lineup_master_id=lm.lineup_master_id')
						->where('lm.user_id', $post['user_id'], FALSE)
						->where('lmc.is_winner',1)
						->where("lm.added_date BETWEEN '{$startDate}' AND '{$endDate}'");
			
		$query = $this->db->get();  
		$result = $query->result();
		return $result;
	}

	/**
	 * Used to get total PAID & FREE contest JOINED by user
	 */
	function get_free_paid() {		
		$post = $this->input->post();		
		if (isset($post['from_date'])) {
			$startDate = date("Y-m-d", strtotime($post['from_date']));
		} else {
			$startDate = date('Y-m-d', strtotime('-10 day'));
		}
            
        if (isset($post['to_date'])) {
            $endDate = date("Y-m-d", strtotime($post['to_date']));
        } else {
			$endDate = date("Y-m-d");
		}
			
		$this->db->select("lm.added_date,COUNT(CASE WHEN c.entry_fee > 0 THEN 1 END) AS paid,
		COUNT(CASE WHEN c.entry_fee <= 0 THEN 1 END) AS free")
						->from(LINEUP_MASTER .' lm')
						->join(LINEUP_MASTER_CONTEST .' lmc','lmc.lineup_master_id=lm.lineup_master_id')
						->join(CONTEST .' c','c.contest_id=lmc.contest_id')
						->where('lm.user_id', $post['user_id'], FALSE)
						->where("lm.added_date BETWEEN '{$startDate}' AND '{$endDate}'")
						->group_by('lm.added_date');

		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	/**
	 * [get_promo_code_detail description]
	 * @MethodName get_promo_code_detail
	 * @Summary This function used for get promo code details by promocode
	 * @return     [array]
	 */
	public function get_promo_code_detail()
	{
		$sort_field	= 'PCE.added_date';
		$sort_order	= 'DESC';
		$limit		= 10;
		$page		= 0;

		$post_data	= $this->input->post();

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_full_name','promo_code','added_date','amount_received','type')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;

		$this->db_user->select("O.real_amount as deposit_amount,U.user_unique_id,U.user_name as user_full_name,PCE.contest_unique_id,PC.promo_code,PC.type,PC.cash_type,PCE. added_date,PCE.amount_received,PCE.user_id,PCE.is_processed as status",FALSE)
				->from(PROMO_CODE." PC")
				->join(PROMO_CODE_EARNING." AS PCE", "PCE.promo_code_id = PC.promo_code_id","inner")
				->join(USER." AS U", "U.user_id = PCE.user_id","inner")
				// ->join(TRANSACTION." AS T","T.order_id=PCE.order_id","inner")
				->join(ORDER." AS O","O.order_id=PCE.order_id","inner")
				->where('promo_code', $post_data['promo_code']);

		if(isset($post_data['keyword']) && $post_data['keyword'] != "") {
			$this->db_user->like('U.user_name',$post_data['keyword']);
		}		

		$tempdb = clone $this->db_user;
		$query = $this->db_user->get();
		$total = $query->num_rows();
		$sql = $tempdb->order_by($sort_field, $sort_order)
						->limit($limit,$offset)
						->get();
					
		$result	= $sql->result_array();
		$contest_ids = array_column($result, 'contest_unique_id');
		$final_result = array();
		if(!empty($contest_ids))
		{
		  	$responce = $this->db->select("C.contest_unique_id,C.contest_name,C.entry_fee,C.scheduled_date as season_scheduled_date")
								->from(CONTEST. " C")
								->where_in('C.contest_unique_id',$contest_ids)
								->get()->result_array();
		}

		foreach ($result as $key => $value) 
		{
			if($value['type'] == "5"){//stock contest code
				$contest_key = array_search( $value['contest_unique_id'], array_column($responce, 'contest_unique_id'));
				$value = array_merge($value,$responce[$contest_key]);
			}

			$final_result[] = $value;
		}
		
		return array('result'=>$final_result, 'total'=>$total);
	}

	/**
	 * function to get collection master id for dfs scheduled push
	 * @param collection_id
	 */
	public function get_cname($collection_id,$stock_type) {
/*		$stock_fantasy = $this->app_config['allow_stock_fantasy']['key_value'] ? $this->app_config['allow_stock_fantasy']['key_value'] : 0;
		$equity = $this->app_config['allow_equity']['key_value'] ? $this->app_config['allow_equity']['key_value'] : 0;
		$predict = $this->app_config['allow_stock_predict']['key_value'] ? $this->app_config['allow_stock_predict']['key_value'] : 0;
        if($equity==1 || $stock_fantasy==1 ||  $predict ==1)
        {
          if($stock_fantasy >= $equity)
          {
              if($equity==1)
              {
                $stock_type = [1,2];   // 1 : stock fantasy , 2 : stockz11 (equity)
              }else{
                $stock_type = [1];
              }
          }else{
              $stock_type = [2];
          }
        }*/
		// $current_date = format_date('today', 'Y-m-d');
        $cur_date_time = format_date('today', 'Y-m-d H:i:s'); //'2021-09-23 03:30:00';

		$result = $this->db->select("name,stock_type")
		->from(COLLECTION." as C")
		->where('published_date <',$cur_date_time)
        ->where('scheduled_date >',$cur_date_time)
		->where_in('stock_type',$stock_type)
		->where('C.collection_id', $collection_id)
		->get()->row_array();
		// echo $this->db->last_query();exit;
		return $result;
	}

	/**
	*@method mark_pin_contest
	*@uses this function used for mark contest as pin from admin panel
	**/
	public function mark_pin_contest($post_data)
	{
		if(empty($post_data)){
			return false;
		}

		$is_pin_contest = 1;
		if(isset($post_data['is_pin_contest'])){
			$is_pin_contest = $post_data['is_pin_contest'];
		}

		//update status in database
		$this->db->where('contest_id',$post_data['contest_id']);
		$this->db->set('is_pin_contest', $is_pin_contest);
		$this->db->update(CONTEST);

		return true;
	}

    function get_lineup_score_calculation_prediction($post_data)
    {
        $collection_id = $post_data['collection_id'];
        $lineup_master_id = $post_data['lineup_master_id'];

        $result = $this->db->select(' C.stock_id,C.close_price,C.open_price,IFNULL(S.logo,"") as logo,S.name,S.display_name,L.accuracy_percent,L.user_price',false) 
        ->from(COLLECTION_STOCK . " C")
        ->join(COLLECTION . " CM", "CM.collection_id=C.collection_id", "INNER")
        ->join(LINEUP . " L", "C.stock_id=L.stock_id", "INNER")
        ->join(STOCK . " S", "S.stock_id=C.stock_id", "INNER")
        ->where("C.collection_id",$collection_id)
        ->where("L.lineup_master_id",$lineup_master_id)
        ->get()
        ->result_array();
        
        if(empty($result))
        {
      		$sql = $this->db->query("SELECT 
				        JSON_UNQUOTE(JSON_EXTRACT(LM.team_data, '$.stocks')) as stock_id 
				        FROM  vi_lineup_master as LM 
				        where LM.`lineup_master_id` = $lineup_master_id");
			$query = $sql->row_array();	  
		
			//$stock_ids = str_replace(str_split('\\/:[*?"]<>|'), '', $query['stock_id']);
			$stocks = json_decode($query['stock_id'],true);
			$stock_ids =  array_keys($stocks);
			$result = $this->db->select('S.stock_id,IFNULL(S.logo,"") as logo,S.name,S.display_name',false) 
			        ->from(STOCK . " S")
			       ->where_in("S.stock_id",$stock_ids)
			        ->get()
			        ->result_array();	
			if(!empty($result)){
				foreach($result as $key=>$value){
						
						if(in_array($value['stock_id'],$stock_ids)){
							$result[$key]['user_price'] = $stocks[$value['stock_id']];

						}
				}
			}
        }
       
        return $result;
    }

    public function check_candel_exist($collection_id){
    	return $this->db->get_where(CONTEST,array('collection_id'=>$collection_id))->num_rows();

    }








  /**
	 * Used to get contest report
	 * @return     [array]
	 */
	public function get_completed_contest_data() { 

		$post_data = $this->input->post();
	
			$sort_field = 'C.schedule_date';
			$sort_order = 'DESC';
			$limit      = 50;
			$page       = 0;
			
			if(isset($post_data['items_perpage'])) {
				$limit = $post_data['items_perpage'];
			}
	
			if(isset($post_data['current_page'])) {
				$page = $post_data['current_page']-1;
			}
	
			if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','size','entry_fee','schedule_date','prize_pool','guaranteed_prize','total_user_joined','minimum_size','site_rake'))) {
				$sort_field = $post_data['sort_field'];
			}
	
			if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC'))) {
				$sort_order = $post_data['sort_order'];
			}
	
			$offset	= $limit * $page;
			$this->db_user->select("C.currency_type,C.group_id, C.contest_id, C.contest_unique_id, C.contest_name, C.entry_fee, C.prize_pool, C.site_rake, C.total_user_joined, C.size, C.minimum_size, C.currency_type, C.max_bonus_allowed");
			$this->db_user->select("C.entry_fee*C.total_user_joined as total_entry_fee",false);
			$this->db_user->select("C.guaranteed_prize,C.game_type");
			// $this->db_user->select("C.schedule_date AS scheduled_date",false);
			if (isset($post_data['csv']) && $post_data['csv'] == true) 	
				{		
					$tz_diff = get_tz_diff($this->app_config['timezone']);
					
					$this->db_user->select("CONVERT_TZ(C.schedule_date, '+00:00', '".$tz_diff."') AS scheduled_date");
				}else{
					$this->db_user->select("C.schedule_date as scheduled_date");
				}
			$this->db_user->from(CONTEST_REPORT." AS C");
	
			
			if(isset($post_data['group_id']) && !empty($post_data['group_id'])) {
				$this->db_user->where('C.group_id',$post_data['group_id'], FALSE);
			}
			if(isset($post_data['category_id']) && !empty($post_data['category_id'])) {
				$this->db_user->where('C.contest_type',$post_data['category_id'], FALSE);
			}
	
			if(isset($post_data['keyword']) && !empty($post_data['keyword'])) {
				$this->db_user->like('C.contest_name',$post_data['keyword']);
			}
	
			if(!empty($post_data['from_date']) && !empty($post_data['to_date'])) {
				//$this->db_user->where("DATE_FORMAT(C.schedule_date,'%Y-%m-%d %H:%i:%s') >= '".format_date($post_data['from_date'],'Y-m-d')."' and DATE_FORMAT(C.schedule_date,'%Y-%m-%d %H:%i:%s') <= '".format_date($post_data['to_date'],'Y-m-d')."'");
				$this->db_user->where("DATE_FORMAT(C.schedule_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(C.schedule_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");


			}
			
			if(!empty($post_data['stock_type'])) {
				if(in_array($post_data['stock_type'], [2,3,4,5]))
				{
					$this->db_user->where('C.game_type',$post_data['stock_type']);
				}
				else{ 
					$this->db_user->where_in('C.game_type',[2,3,4,5]);

				}

			}else{
				$this->db_user->where_in('C.game_type',[2,3,4,5]);
			}	
	
			$tempdb = clone $this->db_user;
			$temp_q = $tempdb->get();
			$total = $temp_q->num_rows(); 
	
			if(!empty($sort_field) && !empty($sort_order)) {
				$this->db_user->order_by($sort_field, $sort_order);
			}
	
			if(!empty($limit) && !$post_data["csv"]) {
				$this->db_user->limit($limit, $offset);
			}
			$query = $this->db_user->get();
			// echo $this->db->last_query();die;
			$result	= array();
			if($query->num_rows()) {
				$category_list = $this->get_category_list();
				$category_list = array_column($category_list, 'name', 'category_id');
				$result	= $query->result_array();
				foreach($result as $key=>$contest) {
					$group = $this->db->select('group_name')->from(MASTER_GROUP)->where('group_id',$contest['group_id'])->get()->row_array();
					$result[$key]['group_name'] = $group['group_name'];
					$result[$key]['category_name'] = $category_list[$contest['group_id']];
				}
			}
			return array('result'=>$result, 'total'=>$total);
		}
	

}