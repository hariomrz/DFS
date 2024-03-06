<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Report_model extends MY_Model{

  	function __construct()
  	{
	  	parent::__construct();
	}

	/**
	 * Function used for get user report list
	 * @param array $post_data
	 * @return array
	 */
	public function get_user_report($post_data)
	{
		$page = get_pagination_data($post_data);
		$sort_field	= 'real_entry';
		$sort_order	= 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('total_entries','real_entry','real_winning','real_profit','coin_entry','coin_winning','coin_profit')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		$sel_sql = "IFNULL(US.status,1) as user_status";
		if($post_data['csv'] == true){
			$sel_sql = "IF(IFNULL(US.status,1)=1,'Enabled','Disabled') as user_status";
		}
		$this->db->select("UT.user_id,UT.user_name,COUNT(DISTINCT UT.user_team_id) as total_team,SUM(CASE WHEN UT.currency_type=1 THEN entry_fee ELSE 0 END) as real_entry,SUM(CASE WHEN UT.currency_type=1 THEN UT.winning ELSE 0 END) as real_winning,(SUM(CASE WHEN UT.currency_type=1 THEN entry_fee ELSE 0 END) - SUM(CASE WHEN UT.currency_type=1 THEN winning ELSE 0 END)) as real_profit,SUM(CASE WHEN UT.currency_type=2 THEN entry_fee ELSE 0 END) as coin_entry,SUM(CASE WHEN UT.currency_type=2 THEN winning ELSE 0 END) as coin_winning,(SUM(CASE WHEN UT.currency_type=2 THEN entry_fee ELSE 0 END) - SUM(CASE WHEN UT.currency_type=2 THEN winning ELSE 0 END)) as coin_profit,IFNULL(US.winning_cap,0) as winning_cap,".$sel_sql)
			 	->from(USER_TEAM." AS UT")
			 	->join(USER_SETTING." AS US", "US.user_id = UT.user_id", 'LEFT')
			 	->where('UT.status',"2")
			 	->group_by('UT.user_id');

        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('IFNULL(UT.user_name,"")', $post_data['keyword']);
		}
		// if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
		// 	$this->db->where("DATE_FORMAT(UT.added_date,'%Y-%m-%d %H:%i:%s') >=",date("Y-m-d H:i", strtotime($post_data['from_date'])));
		// }
		// if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
		// 	$this->db->where("DATE_FORMAT(UT.added_date,'%Y-%m-%d %H:%i:%s') <= ",date("Y-m-d H:i", strtotime($post_data['to_date'])));
		// }

		if(!empty($post_data['from_date'])&&!empty($post_data['to_date'])){
		  $this->db->where("DATE_FORMAT(UT.added_date,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(UT.added_date,'%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."'");

		}
		$sql = $this->db->order_by($sort_field, $sort_order);
		$total = 0;
		if($post_data['csv'] == false){
			$tempdb = clone $this->db;
			$query = $this->db->get();
			$total = $query->num_rows();
			$sql = $tempdb->limit($page['limit'],$page['offset'])->get();
		}else{
			$sql = $this->db->get();
		}
		$result = $sql->result_array();
		//echo $this->db->last_query();die;
		return array('result'=>$result,'total'=>$total);
	}
}
