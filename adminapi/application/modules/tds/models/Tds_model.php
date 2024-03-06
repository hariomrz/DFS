<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tds_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

    /**
  	* Function used for get tds report list
  	* @param array $post_data
  	* @return array
  	*/
	public function get_tds_report($post_data)
    {
    	$withdrawal = 8;
    	$settlement = 535;
    	$tds_deduction = 130;
    	$section = "194BA";
		$source_arr = array($withdrawal,$settlement);
    	if(isset($post_data['tds_type']) && $post_data['tds_type'] == 2){
			$source_arr = array($withdrawal);
    	}else if(isset($post_data['tds_type']) && $post_data['tds_type'] == 3){
    		$source_arr = array($settlement);
    	}else if(isset($post_data['tds_type']) && $post_data['tds_type'] == 4){
    		$source_arr = array($tds_deduction);
    	}
    	$pagination = get_pagination_data($post_data);
    	$sel_str = "";
    	$fy_str = "";
    	if(isset($post_data['csv']) && $post_data['csv'] == false || empty($post_data['csv'])){
    		$fy_str = "{{fy}} ";
    		$sel_str = "O.order_id,";
    	}
		$this->db->select($sel_str."U.user_name,CONCAT_WS(' ',U.first_name,U.last_name) as name,IFNULL(U.email,'') as email,(CASE WHEN O.source = ".$withdrawal." THEN 'Withdrawal' WHEN O.source=".$settlement." THEN 'FY ".$fy_str."Settlement' WHEN O.source = ".$tds_deduction." THEN 'Winning TDS' ELSE '' END) as type,,IFNULL(JSON_UNQUOTE(json_extract(O.custom_data, '$.fy')),'') AS fy,O.winning_amount as amount,IFNULL(JSON_UNQUOTE(json_extract(O.custom_data, '$.net_winning')),'0.00') AS net_winning,IFNULL(JSON_UNQUOTE(json_extract(O.custom_data, '$.tds_rate')),'0.00') AS tds_rate,O.tds,(O.winning_amount-O.tds) as txn_amount,IFNULL(UPPER(U.pan_no),'') as pan_no,'".$section."' as section,O.date_added", FALSE);
		if (isset($post_data['csv']) && $post_data['csv'] == true)      
		{
				$tz_diff = get_tz_diff($this->app_config['timezone']);
				$this->db->select("CONVERT_TZ(O.date_added, '+00:00', '".$tz_diff."') AS date_added");
		}else{
				$this->db->select("O.date_added");
		}

		$this->db->from(ORDER." AS O");
        $this->db->join(USER.' AS U','U.user_id=O.user_id',"INNER");
        $this->db->where('O.status',"1");
        $this->db->where('O.tds !=',"0");
        $this->db->where_in('O.source',$source_arr);
        $this->db->order_by("O.order_id","DESC");
        if(!empty($post_data['from_date']) && !empty($post_data['to_date'])){
			// $this->db->where("O.date_added BETWEEN '{$post_data['from_date']} 00:00:00' AND '{$post_data['to_date']} 23:59:59'");
			$this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER(CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),IFNULL(U.pan_no,"")))', strtolower($post_data['keyword']) );
		}

		if(isset($post_data['type']) && $post_data['type'] > 1)
		{
			if($post_data['type'] == "2"){
				$this->db->where('U.pan_no !=',"");
			}else if($post_data['type'] == "3"){
				$this->db->where('U.pan_no IS NULL');
			}
		}

		$tempdb = clone $this->db;
        $total = 0;
		if(isset($post_data['csv']) && $post_data['csv'] == false || empty($post_data['csv'])){
			$total = $tempdb->get()->num_rows();
			$this->db->limit($pagination['limit'],$pagination['offset']);
		}
		$result	= $this->db->get()->result_array();
		$result = ($result) ? $result : array();
		return array('result'=>$result, 'total'=>$total);
    }

    /**
  	* Function used for get tds documents list
  	* @param array $post_data
  	* @return array
  	*/
	public function get_tds_document($post_data)
    {
    	$pagination = get_pagination_data($post_data);
    	$sel_str = "";
    	if(isset($post_data['csv']) && $post_data['csv'] == false || empty($post_data['csv'])){
    		$sel_str = "UT.id,UT.file_name,";
    	}
		$this->db->select($sel_str."IFNULL(U.user_name,'') as user_name,IFNULL(CONCAT_WS(' ',U.first_name,U.last_name),'') as name,IFNULL(UT.gov_id,'') as gov_id,IFNULL(U.phone_no,'') as phone_no,UT.fy,UT.date_added", FALSE);
        $this->db->from(USER_TDS_CERTIFICATE." AS UT");
        $this->db->join(USER.' AS U','U.user_id=UT.user_id',"INNER");
        $this->db->order_by("UT.id","DESC");
        $this->db->where("UT.fy",$post_data['fy']);
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER(CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),IFNULL(UT.gov_id,"")))', strtolower($post_data['keyword']) );
		}

		$tempdb = clone $this->db;
        $total = 0;
		if(isset($post_data['csv']) && $post_data['csv'] == false || empty($post_data['csv'])){
			$total = $tempdb->get()->num_rows();
			$this->db->limit($pagination['limit'],$pagination['offset']);
		}
		$result	= $this->db->get()->result_array();
		$result = ($result) ? $result : array();
		return array('result'=>$result, 'total'=>$total);
    }

    /**
  	* Function used for get tds documents list
  	* @param array $post_data
  	* @return array
  	*/
	public function delete_tds_document($id)
	{
		if(!$id){
			return false;
		}

		$this->db->where("id",$id);
		$this->db->delete(USER_TDS_CERTIFICATE);
		$result = $this->db->affected_rows();
		return $result;
	}

    /**
  	* Function used for save tds document in db
  	* @param array $post_data
  	* @return array
  	*/
	public function save_tds_document($post_data)
	{
		if(empty($post_data)){
			return false;
		}

		$this->db->insert(USER_TDS_CERTIFICATE,$post_data);
        return $this->db->insert_id();
	}
}
/* End of file Gst_model.php */
/* Location: ./application/models/Gst_model.php */
