<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends MY_Model {

	public function __construct() {
		parent::__construct();
		$this->db_user	= $this->load->database('user_db', TRUE);
		$_POST = $this->input->post();

	}

	/**
	 * Used to get user contest list 
	 */
	public function contest_list_by_user_id() {	
		$sort_field = 'scheduled_date';
		$sort_order = 'DESC';
		$limit      = 10;
		$page       = 0;

		$post_data = $this->input->post();
		
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

		$this->db->select("C.status, C.contest_id, C.contest_unique_id, C.contest_name, C.entry_fee, C.guaranteed_prize, C.total_user_joined, C.size, C.currency_type, C.prize_pool, C.prize_type, C.site_rake");
		//$this->db->select("SUM(C.contest_id) as constest_played",FALSE);
		$this->db->select("(C.entry_fee*COUNT(DISTINCT LM.lineup_master_id)) as total_entry_fee",FALSE);
		$this->db->select("LM.lineup_master_id, LM.user_id, IFNULL(LMC.prize_data,'') as prize_data, LMC.lineup_master_contest_id, LM.collection_id, group_concat(LMC.lineup_master_contest_id) as lineup_master_contest_ids,CM.stock_type",FALSE); //, LMC.is_winner, SUM(LMC.is_winner) as constest_won, group_concat(LMC.lineup_master_id) as lineup_master_ids, max(LMC.game_rank) as game_rank,

		$this->db->select("MG.group_name,C.group_id, CC.name AS category_name, CM.name AS collection_name", FALSE);
		
		if (isset($post_data['csv']) && $post_data['csv'] == true) 	
						{							
							$tz_diff = get_tz_diff($this->app_config['timezone']);							
							$this->db->select("CONVERT_TZ(C.scheduled_date, '+00:00', '".$tz_diff."') AS scheduled_date");
						}else{
							$this->db->select("C.scheduled_date");
						}
						$this->db->from(LINEUP_MASTER . " AS LM")
						->join(LINEUP_MASTER_CONTEST." AS LMC","LMC.lineup_master_id=LM.lineup_master_id","INNER")
						->join(CONTEST." AS C","LMC.contest_id=C.contest_id","INNER")
						->join(COLLECTION." AS CM","C.collection_id=CM.collection_id","LEFT")	
						->join(MASTER_GROUP." AS MG","MG.group_id=C.group_id","LEFT")						
						->join(CONTEST_CATEGORY." AS CC","CC.category_id=C.category_id","LEFT")
						->where('LM.user_id',$post_data["user_id"])
						->group_by('LMC.contest_id');
         
		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' ) {
			$this->db->where("DATE_FORMAT(C.scheduled_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(C.scheduled_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}

		if(isset($post_data['stock_type']) && in_array($post_data['stock_type'],[1,2,3,4])) {
			$this->db->where('CM.stock_type',$post_data['stock_type'], FALSE);
		}

		if(isset($post_data['group_id']) && !empty($post_data['group_id'])) {
			$this->db->where('C.group_id',$post_data['group_id'], FALSE);
		}
		if(isset($post_data['category_id']) && !empty($post_data['category_id'])) {
			$this->db->where('C.category_id',$post_data['category_id'], FALSE);
		}

		if(isset($post_data['keyword']) && !empty($post_data['keyword'])) {
			$this->db->like('C.contest_name',$post_data['keyword']);
		}
		
		$tempdb = clone $this->db;
		$temp_q = $tempdb->get();
		//echo $tempdb->last_query(); die;
		$total = $temp_q->num_rows();	
		

		if(!empty($sort_field) && !empty($sort_order)) {
			$this->db->order_by('C.'.$sort_field, $sort_order);
		}

		if(!empty($limit)) {
			$this->db->limit($limit, $offset);
		}
		$sql = $this->db->get();
		
		$result	= $sql->result_array();
		return array('result'=>$result, 'total'=>($total)?$total:0);		
	}

	/**
	 * Used to get user contest list for export
	 */
	public function get_contest_list_by_user_id($is_user_history = 0) {	
		// $sort_field = 'scheduled_date';
		// $sort_order = 'DESC';
		// $limit      = 10;
		// $page       = 0;
		

		// $post_data = $this->input->get();

		// if(isset($post_data['items_perpage'])) {
		// 	$limit = $post_data['items_perpage'];
		// }

		// if(isset($post_data['current_page'])) {
		// 	$page = $post_data['current_page']-1;
		// }

		// if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','size','entry_fee','scheduled_date','prize_pool','guaranteed_prize','total_user_joined','minimum_size','site_rake'))) {
		// 	$sort_field = $post_data['sort_field'];
		// }

		// if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC'))) {
		// 	$sort_order = $post_data['sort_order'];
		// }

		
		// $offset	= $limit * $page;

		// $this->db->select("C.status, C.contest_unique_id, C.contest_name, C.entry_fee, C.guaranteed_prize, C.total_user_joined, C.size, C.currency_type, C.is_auto_recurring, C.contest_access_type, C.prize_pool, C.prize_type, C.site_rake");
		// $this->db->select("SUM(C.contest_id) as constest_played",FALSE);
		// $this->db->select("(C.entry_fee*COUNT(DISTINCT LM.lineup_master_id)) as total_entry_fee",FALSE);
		// $this->db->select("LM.lineup_master_id, LM.user_id, max(LMC.game_rank) as game_rank, IFNULL(LMC.prize_data,'') as prize_data, LMC.lineup_master_contest_id, LM.collection_id, LMC.is_winner, SUM(LMC.is_winner) as constest_won, group_concat(LMC.lineup_master_contest_id) as lineup_master_contest_ids,CM.stock_type",FALSE); // group_concat(LMC.lineup_master_id) as lineup_master_ids,

		// $this->db->select("MG.group_name, CC.name AS category_name, CM.name AS collection_name", FALSE);


		// 		if (isset($post_data['csv']) && $post_data['csv'] == true) 	
		// 		{					
					
		// 			$tz_diff = get_tz_diff($this->app_config['timezone']);
					
		// 			$this->db->select("CONVERT_TZ(C.scheduled_date, '+00:00', '".$tz_diff."') AS scheduled_date");
		// 		}else{
		// 			$this->db->select("C.scheduled_date");
		// 		}
		// 				$this->db->from(LINEUP_MASTER . " AS LM")
		// 				->join(LINEUP_MASTER_CONTEST." AS LMC","LMC.lineup_master_id=LM.lineup_master_id","INNER")
		// 				->join(CONTEST." AS C","LMC.contest_id=C.contest_id","INNER")
		// 				->join(COLLECTION." AS CM","C.collection_id=CM.collection_id","LEFT")						
		// 				->join(MASTER_GROUP." AS MG","MG.group_id=C.group_id","LEFT")												
		// 				->join(CONTEST_CATEGORY." AS CC","CC.category_id=C.category_id","LEFT")
		// 				->where('LM.user_id',$post_data["user_id"])
		// 				->group_by('LMC.contest_id');

		// if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' ) {
		// 	$this->db->where("DATE_FORMAT(C.scheduled_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(C.scheduled_date, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		// }	
		
		// if(isset($post_data['group_id']) && !empty($post_data['group_id'])) {
		// 	$this->db->where('C.group_id',$post_data['group_id'], FALSE);
		// }
		// if(isset($post_data['category_id']) && !empty($post_data['category_id'])) {
		// 	$this->db->where('C.category_id',$post_data['category_id'], FALSE);
		// }

		// if(isset($post_data['stock_type']) && !empty($post_data['stock_type']) && $post_data['stock_type'] != "undefined")
		// {
		// 	$this->db->where('CM.stock_type',$post_data['stock_type']);
		// }

		// if(isset($post_data['keyword']) && !empty($post_data['keyword'])) {
		// 	$this->db->like('C.contest_name',$post_data['keyword']);
		// }

		// if($is_user_history == 1){
		// 	$this->db->group_by('LMC.lineup_master_contest_id');
		// }
		// //$this->db->group_by('C.contest_unique_id');
		// $tempdb = clone $this->db;
		// $temp_q = $tempdb->get();
		// $total = $temp_q->num_rows();

		// if(!empty($sort_field) && !empty($sort_order)) {
		// 	$this->db->order_by('C.'.$sort_field, $sort_order);
		// }

		// if(empty($is_user_history) ) {
		// 	$this->db->limit($limit, $offset);
		// }
		// $sql = $this->db->get();
		
		// $result	= $sql->result_array();
		
		// return array('result'=>$result, 'total'=>($total)?$total:0);	
		
		


		$sort_field = 'scheduled_date';
		$sort_order = 'DESC';
		$limit      = 10;
		$page       = 0;

		$post_data = $this->input->get();
		
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

		$this->db->select("C.status, C.contest_id, C.contest_unique_id, C.contest_name, C.entry_fee, C.guaranteed_prize, C.total_user_joined, C.size, C.currency_type, C.prize_pool, C.prize_type, C.site_rake");
		//$this->db->select("SUM(C.contest_id) as constest_played",FALSE);
		$this->db->select("(C.entry_fee*COUNT(DISTINCT LM.lineup_master_id)) as total_entry_fee",FALSE);
		$this->db->select("LM.lineup_master_id, LM.user_id, IFNULL(LMC.prize_data,'') as prize_data, LMC.lineup_master_contest_id, LM.collection_id, group_concat(LMC.lineup_master_contest_id) as lineup_master_contest_ids,CM.stock_type",FALSE); //, LMC.is_winner, SUM(LMC.is_winner) as constest_won, group_concat(LMC.lineup_master_id) as lineup_master_ids, max(LMC.game_rank) as game_rank,

		$this->db->select("MG.group_name,C.group_id, CC.name AS category_name, CM.name AS collection_name", FALSE);
		
		$tz_diff = get_tz_diff($this->app_config['timezone']);							
							$this->db->select("CONVERT_TZ(C.scheduled_date, '+00:00', '".$tz_diff."') AS scheduled_date");

						$this->db->from(LINEUP_MASTER . " AS LM")
						->join(LINEUP_MASTER_CONTEST." AS LMC","LMC.lineup_master_id=LM.lineup_master_id","INNER")
						->join(CONTEST." AS C","LMC.contest_id=C.contest_id","INNER")
						->join(COLLECTION." AS CM","C.collection_id=CM.collection_id","LEFT")	
						->join(MASTER_GROUP." AS MG","MG.group_id=C.group_id","LEFT")						
						->join(CONTEST_CATEGORY." AS CC","CC.category_id=C.category_id","LEFT")
						->where('LM.user_id',$post_data["user_id"])
						->group_by('LMC.contest_id');

		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' ) {
			$this->db->where("DATE_FORMAT(C.scheduled_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(C.scheduled_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}

		if(isset($post_data['stock_type']) && in_array($post_data['stock_type'],[1,2,3,4])) {
			$this->db->where('CM.stock_type',$post_data['stock_type'], FALSE);
		}

		if(isset($post_data['group_id']) && !empty($post_data['group_id'])) {
			$this->db->where('C.group_id',$post_data['group_id'], FALSE);
		}
		if(isset($post_data['category_id']) && !empty($post_data['category_id'])) {
			$this->db->where('C.category_id',$post_data['category_id'], FALSE);
		}

		if(isset($post_data['keyword']) && !empty($post_data['keyword'])) {
			$this->db->like('C.contest_name',$post_data['keyword']);
		}
		
		$tempdb = clone $this->db;
		$temp_q = $tempdb->get();
		//echo $tempdb->last_query(); die;
		$total = $temp_q->num_rows();	
		

		if(!empty($sort_field) && !empty($sort_order)) {
			$this->db->order_by('C.'.$sort_field, $sort_order);
		}

		if(!empty($limit)) {
			$this->db->limit($limit, $offset);
		}
		$sql = $this->db->get();
		
		$result	= $sql->result_array();
		return array('result'=>$result, 'total'=>($total)?$total:0);	
	}

	/**
	 * Used to get user winning amount for his lineup
	 */
	public function get_contest_winning_amount($lineup_master_contest_ids,$user_id) {
		$result = $this->db_user->select('sum(winning_amount) as winning_amount, SUM(bonus_amount) as winning_bonus, SUM(points) as winning_coin')
						   ->from(ORDER)
						   ->where('user_id', $user_id)
						   ->where('status', 1)
						   ->where('type', '0')
						   ->where('source', 462)
						   ->where("source_id in($lineup_master_contest_ids)")
						   ->get()
						   ->row_array();
						   //echo $this->db->last_query();
		return (!empty($result)) ? $result : array("winning_amount" => 0,"winning_bonus" => 0,"winning_coin" => 0);
	}
}

/* End of file User_model.php */
/* Location: ./application/models/User_model.php */
