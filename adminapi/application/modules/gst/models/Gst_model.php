<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Gst_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
  	* Function used for get completed match list
  	* @param array $post_data
  	* @return array
  	*/
	public function get_gst_completed_fixture($post_data)
    {
		$this->db->select("GR.match_id,GR.match_name,GR.scheduled_date", FALSE);
        $this->db->from(GST_REPORT." AS GR");
        if(!empty($post_data['from_date']) && !empty($post_data['to_date'])){
			$this->db->where("GR.scheduled_date BETWEEN '{$post_data['from_date']} 00:00:00' AND '{$post_data['to_date']} 23:59:59'");
		}
        if(isset($post_data['module_type']) && $post_data['module_type'] != "")
		{
			$this->db->where('GR.module_type',$post_data['module_type']);
		}
        $this->db->group_by('GR.match_id');
        $this->db->order_by('GR.scheduled_date', 'ASC');
   		$sql = $this->db->get();
		$result	= $sql->result_array();
		return $result;
    }

    /**
  	* Function used for get completed contest list
  	* @param int $match_id
  	* @return array
  	*/
	public function get_gst_completed_contest($match_id,$module_type)
    {
		$this->db->select("GR.contest_id,GR.contest_name", FALSE);
        $this->db->from(GST_REPORT." AS GR");
        $this->db->where('GR.match_id',$match_id);
        $this->db->where('GR.module_type',$module_type);
        $this->db->group_by('GR.contest_id');
        $this->db->order_by('GR.contest_name','ASC');
   		$sql = $this->db->get();
		$result	= $sql->result_array();
		return $result;
    }

    /**
  	* Function used for get gst report list
  	* @param array $post_data
  	* @return array
  	*/
	public function get_gst_report($post_data)
    {
		$limit = 10;
		$page = 0;
		$sort_field	= 'txn_date';
		$sort_order	= 'DESC';

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		$offset	= $limit * $page;
		
		$sel = "GR.invoice_id,GR.user_id,GR.user_name,GR.pan_no,GR.state_name,GR.match_name,GR.contest_name,GR.match_id,GR.min_size,GR.max_size,GR.total_user_joined,GR.txn_amount,GR.site_rake,GR.entry_fee,GR.rake_amount,GR.cgst,GR.sgst,GR.igst";
		if($post_data['invoice_type'] == 1){
			$sel = "GR.invoice_id,GR.user_name,GR.pan_no,GR.state_name,GR.match_name as event,GR.txn_date,GR.txn_amount,GR.cgst,GR.sgst,GR.igst,(CASE WHEN txn_type = 2 THEN 'USER' WHEN txn_type = 3 OR txn_type = 4 OR txn_type = 5 THEN 'ADMIN' ELSE 'NA' END) as paid_by ,gst_number as user_gst";
		}

		$this->db->select($sel, FALSE);
        $this->db->from(GST_REPORT." AS GR");
		$this->db->where("GR.invoice_type",$post_data['invoice_type']);
        //$this->db->where("GR.site_rake > ",'0');
        $this->db->order_by($sort_field, $sort_order);

		if (isset($post_data['csv']) && $post_data['csv'] == true) 	
		{                
			$tz_diff = get_tz_diff($this->app_config['timezone']);                    
			$this->db->select("CONVERT_TZ(GR.txn_date, '+00:00', '".$tz_diff."') AS txn_date");
			if($post_data['invoice_type'] == 0){
				$this->db->select("CONVERT_TZ(GR.scheduled_date, '+00:00', '".$tz_diff."') AS scheduled_date");
			}
		}else{
			$this->db->select("GR.scheduled_date,GR.txn_date");
		}
        if(!empty($post_data['from_date']) && !empty($post_data['to_date'])){
			// $this->db->where("GR.txn_date BETWEEN '{$post_data['from_date']} 00:00:00' AND '{$post_data['to_date']} 23:59:59'");
			$this->db->where("DATE_FORMAT(GR.txn_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(GR.txn_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER(IFNULL(GR.user_name,""))',strtolower($post_data['keyword']));
		}
		if(isset($post_data['module_type']) && $post_data['module_type'] != "")
		{
			$this->db->where('GR.module_type',$post_data['module_type']);
		}
		if(isset($post_data['match_id']) && $post_data['match_id'] != "" && $post_data['match_id'] != "0" && $post_data['match_id'] != "undefined")
		{
			$this->db->where('GR.match_id',$post_data['match_id']);
		}
		if(isset($post_data['contest_id']) && $post_data['contest_id'] != "" && $post_data['contest_id'] != "0" && $post_data['contest_id'] != "undefined")
		{
			$this->db->where('GR.contest_id',$post_data['contest_id']);
		}
		if(isset($post_data['state_id']) && $post_data['state_id'] > "0")
		{
			$this->db->where('GR.state_id',$post_data['state_id']);
		}else if(isset($post_data['state_type']) && $post_data['state_type'] == "2" && $post_data['portal_state_id'] != "0")
		{
			$this->db->where('GR.state_id',$post_data['portal_state_id']);
		}else if(isset($post_data['state_type']) && $post_data['state_type'] == "3" && $post_data['portal_state_id'] != "0")
		{
			$this->db->where('GR.state_id != ',$post_data['portal_state_id']);
		}

		$tempdb = clone $this->db;
        $total = 0;
		if($post_data['csv'] == false || empty($post_data['csv'])){
			$total = $tempdb->get()->num_rows();
			$this->db->limit($limit,$offset);
		}
		$result	= $this->db->get()->result_array();
		$result = ($result) ? $result : array();
		return array('result'=>$result, 'total'=>$total);
    }

    public function get_invoice_data($invoice_id)
    {
    	$this->db->select('*')->from(GST_REPORT);
    	$this->db->where('invoice_id', $invoice_id);
		$result	= $this->db->get()->row_array();
		$result = ($result) ? $result : array();
		return $result;
    }

    public function get_winning_info($lmc_id)
    {
    	$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
		$this->db_fantasy->select('prize_data',FALSE);
		$this->db_fantasy->from(LINEUP_MASTER_CONTEST);
		$this->db_fantasy->where_in("lineup_master_contest_id", $lmc_id);
		$result = $this->db_fantasy->get()->row_array();
		return $result;	
    }
}
/* End of file Gst_model.php */
/* Location: ./application/models/Gst_model.php */
