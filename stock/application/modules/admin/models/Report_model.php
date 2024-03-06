<?php

class Report_model extends MY_Model {
    public function __construct() {
        parent::__construct();
    }
    
    /**
	 * Used to get contest report
	 * @return     [array]
	 */
    function contest_report($post_data) {
		$sort_field = 'C.scheduled_date';
		$sort_order = 'DESC';
		
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','size','entry_fee','scheduled_date','prize_pool','guaranteed_prize','total_user_joined','minimum_size','site_rake'))) {
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post_data['sort_order'];
		}

        $this->db->select("C.group_id, C.category_id, C.contest_id, C.contest_unique_id, C.contest_name, C.entry_fee, C.prize_pool, C.site_rake, C.total_user_joined, C.size, C.minimum_size, C.max_bonus_allowed");
		$this->db->select("C.entry_fee*C.total_user_joined as total_entry_fee",false);
		$this->db->select("(CASE 
			WHEN C.guaranteed_prize=0 THEN 'No Guarantee'
		 	WHEN C.guaranteed_prize=1 THEN 'Guaranteed prize custom'
		 	WHEN C.guaranteed_prize=2 THEN 'Guaranteed'
		 	END
		 ) AS guaranteed_prize");
		$this->db->select("DATE_FORMAT(C.scheduled_date, '".MYSQL_DATE_TIME_FORMAT."') AS scheduled_date",false);
		$this->db->from(CONTEST." AS C");
		//$this->db->join(COLLECTION." AS CM","CM.collection_id = C.collection_id","LEFT");
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

        if(!empty($sort_field) && !empty($sort_order)) {
			$this->db->order_by($sort_field, $sort_order);
		}
		
		$query = $this->db->get();
		$result	= array();
		if($query->num_rows()) {
			$category_list = $this->get_category_list();
			$category_list = array_column($category_list, 'name', 'category_id');
			$result	= $query->result_array();
			foreach($result as $key=>$contest) {
				$group = $this->db->select('group_name')->from(MASTER_GROUP)->where('group_id',$contest['group_id'])->get()->row_array();
				$result[$key]['group_name'] = $group['group_name'];
				$result[$key]['category_name'] = $category_list[$contest['category_id']];

                unset($result[$key]['category_id'],$result[$key]['group_id']);
			}
		}

		//$result_array = [];
		if(!empty($result)) {
            $this->load->model('admin/Contest_model');
            $contest_ids =  array_column($result, 'contest_id');
			$temp_prize_detail = $this->Contest_model->get_contest_prize_detail($contest_ids);

			$contest_prize_detail = array_column($temp_prize_detail,NULL,'contest_id');
			$contest_unique_ids =  array_column($result, 'contest_unique_id');
			$promocode_entry_result = $this->Contest_model->get_contest_promo_code_entry($contest_unique_ids);

			$promocode_entry = array();
			if(!empty($promocode_entry_result)) {
				$promocode_entry = array_column($promocode_entry_result,'promocode_entry_fee_real','contest_unique_id');
			}

		/*	$result_array["sum_join_real_amount"] = 0;
			$result_array["sum_join_bonus_amount"] = 0;
			$result_array["sum_join_winning_amount"] = 0;
			$result_array["sum_join_coin_amount"]=0;
			$result_array["sum_win_amount"] = 0;
			$result_array["sum_total_entery_fee"]=0;
			$result_array["sum_profit_loss"] = 0;
			$result_array["sum_entry_fee"] = 0;
			$result_array["sum_site_rake"] = 0;
			$result_array["sum_min"] = 0;
			$result_array["sum_max"] = 0;
			$result_array["sum_total_user_joined"] = 0;
            $result_array["sum_max_bonus_allowed"] = 0;
			$result_array["sum_prize_pool"] = 0;
			$result_array["sum_total_entry_fee"] = 0;

			$result_array["sum_total_entry_fee_real"] = 0;
			$result_array["sum_promocode_entry_fee_real"] = 0;
			$result_array["sum_total_win_coins"] = 0;
			$result_array["sum_total_win_bonus"] = 0;
			$result_array["sum_total_win_amount_to_real_user"] = 0;
            */

			foreach($result as $key => $contest) {
				//$contest['siterake_amount'] = number_format((($contest['prize_pool']*$contest['site_rake'])/100),2,'.',',');

				if(isset($contest_prize_detail[$contest['contest_id']])) {
					//$result_array["sum_total_entry_fee_real"]+=$contest_prize_detail[$contest['contest_id']]['total_join_real_amount'];
					$contest = array_merge($contest, $contest_prize_detail[$contest['contest_id']]);
				}
				$contest["profit_loss"]	= number_format((($contest["total_join_real_amount"]+$contest["total_join_winning_amount"]) - $contest["total_win_winning_amount"]),2,'.','');
				
                $contest["promocode_entry_fee_real"] = 0;
				if(isset($promocode_entry[$contest['contest_unique_id']])) {
					$contest["promocode_entry_fee_real"] = $promocode_entry[$contest['contest_unique_id']];
				}

				$contest["total_entry_fee"]		= $contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"];
				
                unset($contest['contest_id'],$contest['contest_unique_id']);

                $result[$key] = $contest;
			}
		}
		return $result;
	}
}
