<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_user = $this->load->database('user_db', TRUE);
	}
    
    /**
	 *  use for getting contest prize detail
	 * 
	 */
    public function get_contest_prize_detail($contest_ids)
	{
		$query = $this->db_user->select("(ROUND(IFNULL(sum(IF(ORD.source=524,ORD.real_amount,0)),'0'),2)+ROUND(IFNULL(sum(IF(ORD.source=524,ORD.winning_amount,0)),'0'),2)) as total_join_real_amount,			

		ROUND(IFNULL(sum(IF(ORD.source=524,ORD.bonus_amount,0)),'0'),2) as total_join_bonus_amount,			

		ROUND(IFNULL(sum(IF(ORD.source=524,ORD.points,0)),'0'),2) as total_join_coin_amount,
		ROUND(IFNULL(sum(IF(ORD.source=524,ORD.winning_amount,0)),'0'),2) as total_join_winning_amount,

		ROUND(IFNULL(sum(IF(ORD.source=526,ORD.winning_amount,0)),'0'),2) as total_win_winning_amount,
		ROUND(IFNULL(sum(IF(ORD.source=526,ORD.points,0)),'0'),2) as total_win_coins,
		ROUND(IFNULL(sum(IF(ORD.source=526,ORD.bonus_amount,0)),'0'),2) as total_win_bonus,
		ROUND(IFNULL(sum(IF(ORD.source=526 AND U.is_systemuser=0,ORD.winning_amount,0)),'0'),2) as total_win_amount_to_real_user,


			ORD.reference_id as contest_id",FALSE)
				->from(ORDER." AS ORD")
				->join(USER.' U','U.user_id=ORD.user_id',"INNER")
				->where('(ORD.source = 524 or ORD.source = 526) AND ORD.status = 1') // join game  AND Debit and success		
				->where_in('ORD.reference_id',$contest_ids)
				//->where('U.is_systemuser',0)		
				->group_by('ORD.reference_id')
				->get()->result_array();
		// echo $this->db->last_query();exit;
		return $query;					
	}
}