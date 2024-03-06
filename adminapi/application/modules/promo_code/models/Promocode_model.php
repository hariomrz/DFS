<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Promocode_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		//Do your magic here
		//$this->admin_id = $this->session->userdata('admin_id');
	}

	/**
	 * [new_promo_code description]
	 * @MethodName new_promo_code
	 * @Summary This function used to create new promo code
	 * @param      [array]  [data_array]
	 * @return     [boolean]
	 */
	public function new_promo_code($data_array)
	{
		$this->db->insert(PROMO_CODE,$data_array);
		return $this->db->insert_id();
	}

	/**
	 * [get_promo_codes description]
	 * @MethodName get_promo_codes
	 * @Summary This function used for get all promo code list
	 * @return [data]
	 */
	public function get_promo_codes() {
		$sort_field	= 'added_date';
		$sort_order	= 'DESC';
		$limit		= 10;
		$page		= 0;

		$post_data	= $this->input->post();
		$mode = isset($post_data['mode']) ? $post_data['mode'] : '';

		$status = isset($post_data['status']) ? $post_data['status'] : 1;
		if(!in_array($status, array(0,1))) {
			$status = 1;
		}
		if(isset($post_data['items_perpage'])) {
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page'])) {
			$page = $post_data['current_page']-1;
		}
		
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('type','cash_type','value_type','promo_code','discount','benefit_cap','start_date','expiry_date','min_amount','max_amount','per_user_allowed','status'))) {
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		$from_date = isset($post_data['from_date']) ? $post_data['from_date'] : '';
		$to_date = isset($post_data['to_date']) ? $post_data['to_date'] : '' ;
		$this->db->select("PC.*, COALESCE(SUM(PCE.amount_received),0) as amount_received,IFNULL(min_amount,'-') as min_amount,IFNULL(max_amount,'-') as max_amount,COUNT(PCE.promo_code_earning_id) as total_used",FALSE)
				->from(PROMO_CODE." AS PC")
				->join(PROMO_CODE_EARNING." PCE", "PCE.promo_code_id = PC.promo_code_id and PCE.is_processed = '1'", "left");

		if(isset($post_data['keyword']) && $post_data['keyword'] != "") {
			$this->db->like('PC.promo_code',$post_data['keyword']);
		}

		if($mode != "") {
			$this->db->where('PC.mode',$mode, FALSE);
		}

		if(isset($post_data['type']) && $post_data['type'] != "") {
			$this->db->where('PC.type',$post_data['type'], FALSE);
		}

		if($from_date && $to_date) {
			$this->db->where("DATE_FORMAT(PC.start_date, '%Y-%m-%d %H:%i:%s') BETWEEN '".$from_date."' AND '".$to_date."'");
		}
		
		$today_date = format_date('today', 'Y-m-d H:i:s');
		if($status == 1) {			
			$this->db->where('PC.status="'.$status.'"');
			$this->db->where('PC.expiry_date >=', $today_date);
		} else {
			$this->db->group_start();
				$this->db->where('PC.status="'.$status.'"');
				$this->db->or_where('PC.expiry_date <', $today_date);
			$this->db->group_end();
		}
		

		$this->db->order_by($sort_field, $sort_order)
			  	->group_by("PC.promo_code_id");


		//to get total records
		$tempdb = clone $this->db;
        $temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		
		if($post_data['csv'] == false)	
		{		  
			$this->db->limit($limit,$offset);
		}
					  
		$result	= $this->db->get()->result_array();
		//echo $this->db->last_query();die;
		
		$result = ($result) ? $result : array();
		return array('result' => $result,'total' => $total);
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

		$this->db->select("O.real_amount as deposit_amount,U.user_unique_id,U.user_name as user_full_name,PCE.contest_unique_id,PC.promo_code,PC.promo_code_id,PC.type,PC.cash_type,PC.max_usage_limit,PCE. added_date,PCE.amount_received,PCE.user_id,PCE.is_processed as status",FALSE)
				->from(PROMO_CODE." PC")
				->join(PROMO_CODE_EARNING." AS PCE", "PCE.promo_code_id = PC.promo_code_id","inner")
				->join(USER." AS U", "U.user_id = PCE.user_id","inner")
				// ->join(TRANSACTION." AS T","T.order_id=PCE.order_id","inner")
				->join(ORDER." AS O","O.order_id=PCE.order_id","inner")
				->where('promo_code', $post_data['promo_code']);

		if(isset($post_data['keyword']) && $post_data['keyword'] != "") {
			$this->db->like('U.user_name',$post_data['keyword']);
		}		

		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();
		$sql = $tempdb->order_by($sort_field, $sort_order)
						->limit($limit,$offset)
						->get();
					
		$result	= $sql->result_array();
		$promo_type =3;  //dfs
		if(!empty($result)){
			$promo_type =  $result[0]['type'];			
		}		
		$contest_ids = array_column($result, 'contest_unique_id');
		$final_result = array();
		$responce = array();
		if(!empty($contest_ids))
		{
			if($promo_type ==6){

				$post_target_url	= SERVER_IP.'/livefantasy/admin/contest/get_contest_detail';
				$data_post = array('contest_unique_id'=>$contest_ids);
 				$curl_response = $this->http_post_request($post_target_url,$data_post,3);
		 		$responce  = $curl_response["data"];
			}
			elseif($promo_type ==5){
				$responce = $this->stock_db->select("C.contest_unique_id,C.contest_name,C.entry_fee,C.season_scheduled_date")
					->from(CONTEST. " C")
					->where_in('C.contest_unique_id',$contest_ids)
					->get()->result_array();
			}else{
				$responce = $this->db_fantasy->select("C.contest_unique_id,C.contest_name,C.entry_fee,C.season_scheduled_date")
					->from(CONTEST. " C")
					->where_in('C.contest_unique_id',$contest_ids)
					->get()->result_array();
			}	
		}

		foreach ($result as $key => $value) 
		{
			if(!empty($responce) && ($value['type'] == "3" || $value['type']==5 || $value['type']==6)) {
				$contest_key = array_search( $value['contest_unique_id'], array_column($responce, 'contest_unique_id'));
				$value = array_merge($value,$responce[$contest_key]);
			}

			$final_result[] = $value;
		}
		$total_applied = array('total_used' => "0");
		if (count($result) > 0)
		{
			$total_applied = $this->get_promo_used_count($result[0]['promo_code_id']);
		}
		
		return array('result'=>$final_result, 'total'=>$total, 'total_applied' => $total_applied['total_used']);
	}

	/**
	 * Used to get promo code analytics
	 */
	public function get_promo_code_analytics() {
		$post_data	= $this->input->post();
		$this->db->select("COUNT(DISTINCT PCE.user_id) AS u_total, SUM(IFNULL(O.real_amount,0)) as d_amt, SUM(IFNULL(PCE.amount_received,0)) as r_amt, PC.promo_code_id, PC.promo_code, PC.type, PC.cash_type, PC.value_type, PC.discount, PC.benefit_cap, PC.start_date, PC.expiry_date, PC.min_amount, PC.max_amount, PC.mode, PC.description",FALSE)
				->from(PROMO_CODE." PC")
				->join(PROMO_CODE_EARNING." AS PCE", "PCE.promo_code_id = PC.promo_code_id and PCE.is_processed = '1'","LEFT")
				->join(USER." AS U", "U.user_id = PCE.user_id","LEFT")
				->join(ORDER." AS O","O.order_id=PCE.order_id","LEFT")
				->where('PC.promo_code', $post_data['promo_code']);
		$this->db->order_by('PCE.promo_code_id', 'ASC');		
		$query = $this->db->get();
		$result	= $query->row_array();		
		return $result;
	}

	/**
	 * Used to get promo code usage data for graph
	 */
	public function get_promo_code_usage_graph($promo_code_id) {
		$this->db->select("SUM(O.real_amount) as d_amt, SUM(PCE.amount_received) as r_amt, DATE_FORMAT(PCE.added_date, '%Y-%m-%d') as added_date",FALSE)
				->from(PROMO_CODE." PC")
				->join(PROMO_CODE_EARNING." AS PCE", "PCE.promo_code_id = PC.promo_code_id and PCE.is_processed = '1'","inner")
				->join(USER." AS U", "U.user_id = PCE.user_id","inner")
				->join(ORDER." AS O","O.order_id=PCE.order_id","inner")
				->where('PC.promo_code_id', $promo_code_id);
		$this->db->group_by('added_date');
		$this->db->order_by('added_date', 'ASC');		
		$query = $this->db->get();
		$result	= $query->result_array();		
		return $result;
	}
	
	public function check_contest_validity($contest_unique_id)
	{
		$current_date = format_date();
		$contest_data = $this->db_fantasy->select("C.contest_unique_id, C.season_scheduled_date")
							->from(CONTEST. " C")
							->where('C.contest_unique_id', $contest_unique_id)
							->where('C.status', '0')
							->where('C.season_scheduled_date > ', $current_date)
							->where('C.total_user_joined < C.size ',NULL)
							->get()->result_array();

		if (empty($contest_data))
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	public function get_promo_used_count($promoid){
        $sql = $this->db->select('count(promo_code_earning_id) as total_used')
                        ->from(PROMO_CODE_EARNING)
                        ->where('promo_code_id', $promoid)
                        ->where('is_processed', "1")
                        ->get();
        $rs = $sql->row_array();
        return $rs;
    }

	
}
/* End of file Promocode_model.php */
/* Location: ./application/models/Promocode_model.php */