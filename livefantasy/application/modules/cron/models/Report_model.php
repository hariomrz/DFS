<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_model extends MY_Model {
    
    public $db_user ;
    public $db_fantasy ;
    public $testingNode = FALSE;

    public function __construct() 
    {
       	parent::__construct();
		$this->db_user		    = $this->load->database('db_user', TRUE);
		$this->db_fantasy	    = $this->load->database('db_fantasy', TRUE);
        $this->db_livefantasy   = $this->load->database('livefantasy_db', TRUE);
	}

    function contest_report($post_data)
	{
		$sort_field = 'C.season_scheduled_date';
		$sort_order = 'DESC';

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','collection_name','size','entry_fee','season_scheduled_date','prize_pool','guaranteed_prize','total_user_joined','minimum_size','site_rake')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$this->db_livefantasy->select("CM.collection_name,C.season_scheduled_date,C.contest_id,C.contest_unique_id, C.contest_name, C.entry_fee,C.site_rake,C.group_id,C.minimum_size,C.size,C.total_user_joined,
		C.max_bonus_allowed,C.prize_pool, C.entry_fee*C.total_user_joined as total_entry_fee",false)
		->from(CONTEST." AS C")
		->join(COLLECTION." AS CM","CM.collection_id = C.collection_id","LEFT")
		->join(USER_CONTEST." AS UC", 'UC.contest_id = C.contest_id','LEFT')
		->join(USER_TEAM." AS UT", 'UT.user_team_id = UC.user_team_id','LEFT')
		->where('C.status','3');
		
		// $game_type = isset($post_data['game_type'])?$post_data['game_type']:"";

		if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
		{
			$this->db->where('C.sports_id',$post_data['sports_id']);
		}
		if(isset($post_data['league_id']) && $post_data['league_id'] != '')
		{
			$this->db->where('C.league_id',$post_data['league_id']);
		}
		if(isset($post_data['contest_name']))
		{
			$this->db->like('C.contest_name',$post_data['contest_name']);
		}
		if(isset($post_data['group_id']))
		{
		$this->db->like('C.group_id',$post_data['group_id']);
		}
		if(isset($post_data['collection_master_id']) && $post_data['collection_master_id']!="")
		{
			$this->db->where('C.collection_id',$post_data['collection_master_id']);
		}

	    if(!empty($post_data['from_date'])&&!empty($post_data['to_date']))
		{
			$this->db->where("DATE_FORMAT(C.season_scheduled_date,'%Y-%m-%d') >= '".format_date($post_data['from_date'],'Y-m-d')."' and DATE_FORMAT(C.season_scheduled_date,'%Y-%m-%d') <= '".format_date($post_data['to_date'],'Y-m-d')."'");
		}
		
		$this->db->group_by('C.contest_unique_id');
		
		if(!empty($sort_field) && !empty($sort_order))
		{
			$this->db_livefantasy->order_by($sort_field, $sort_order);
		}

		$sql = $this->db_livefantasy->get();
		$result	= $sql->result_array();
		
		$result_array = [];
		if(!empty($result))
		{
			$this->load->model("admin/Report_model");
			$contest_ids =  array_column($result, 'contest_id');
			$temp_prize_detail = $this->Report_model->get_contest_prize_detail($contest_ids);
			$contest_prize_detail = array_column($temp_prize_detail,NULL,'contest_id');
			
			$contest_unique_ids =  array_column($result, 'contest_unique_id');
			$promocode_entry_result = $this->Report_model->get_contest_promo_code_entry($contest_unique_ids);

			$promocode_entry = array();
			if(!empty($promocode_entry_result))
			{
				$promocode_entry = array_column($promocode_entry_result,'promocode_entry_fee_real','contest_unique_id');
			}

			$result_array["sum_join_real_amount"] = 0;
			$result_array["sum_join_bonus_amount"] = 0;
			$result_array["sum_join_winning_amount"] = 0;
			$result_array["sum_join_coin_amount"]=0;
			$result_array["sum_win_amount"] = 0;
			//$result_array["sum_total_entery_fee"]=0;
			$result_array["sum_profit_loss"] = 0;
			$result_array["sum_entry_fee"] = 0;
			$result_array["sum_site_rake"] = 0;
			$result_array["sum_min"] = 0;
			$result_array["sum_max"] = 0;
			$result_array["sum_total_user_joined"] = 0;
			$result_array["sum_system_teams"] = 0;
			$result_array["sum_real_teams"] = 0;
			$result_array["sum_max_bonus_allowed"] = 0;
			$result_array["sum_prize_pool"] = 0;
			$result_array["sum_total_entry_fee"] = 0;
			$result_array["sum_total_entry_fee_real"] = 0;
			$result_array["sum_botuser_total_real_entry_fee"] = 0;
			$result_array["sum_promocode_entry_fee_real"] = 0;
			$result_array["sum_total_win_coins"] = 0;
			$result_array["sum_total_win_bonus"] = 0;

			foreach($result as $contest)
			{
				$contest['siterake_amount'] = number_format((($contest['prize_pool']*$contest['site_rake'])/100),2,'.',',');

				if(isset($contest_prize_detail[$contest['contest_id']]))
				{
					$result_array["sum_total_entry_fee_real"]+=$contest_prize_detail[$contest['contest_id']]['total_join_real_amount'];
					$contest = array_merge($contest, $contest_prize_detail[$contest['contest_id']]);
				}
				$contest["profit_loss"]	= number_format((($contest["total_join_real_amount"]+$contest["total_join_winning_amount"]) - $contest["total_win_winning_amount"]),2,'.','');
				
				$contest["total_entry_fee"]		= $contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"];
				unset($contest['contest_id'],$contest['contest_unique_id']);
				$res[] = $contest;

			}
			
		}
		return $result_array;
	}

}