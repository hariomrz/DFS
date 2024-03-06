<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manualpg_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
        $this->load->database();
    }

    public function update_type($data)
    {
       $this->db->where('key',$data['key'])->update(DEPOSIT_TYPE,array('custom_data'=> $data['custom_data']));
       return $this->db->affected_rows();    
    }

    public function add_type($data)
    {
        $this->db->insert(DEPOSIT_TYPE,$data);
        $type_id = $this->db->insert_id();
        return $type_id;
    }

    public function get_menual_txn()
    {	
        $sort_field	= 'DTXN.added_date';
		$sort_order	= 'DESC';
		$limit		= 10;
		$page		= 0;
		$post_data = $this->input->post();
		if($post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if($post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('added_date','modified_date','status')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
        if($post_data['csv']){
			$tz_diff = get_tz_diff($this->app_config['timezone']);                    
			$sql = $this->db->select("IFNULL(U.email,'-') email,IFNULL(U.phone_no,'-') phone_no,U.user_unique_id,DTXN.ref_id,DTXN.amount,
            CASE 
                                        WHEN DTXN.status = 0 THEN 'Pending'
                                        WHEN DTXN.status = 1 THEN 'Transferred' 
                                        WHEN DTXN.status = 2 THEN 'Fake Entry'
                                        ELSE 'Fake Entry' 
                                        END AS status,
            U.user_name,
                                        CASE 
                                        WHEN DTYPE.key = '".$this->modes[0]."' THEN '".$this->names[0]."' 
                                        WHEN DTYPE.key = '".$this->modes[1]."' THEN '".$this->names[1]."' 
                                        WHEN DTYPE.key = '".$this->modes[2]."' THEN '".$this->names[2]."'
                                        ELSE 'Other' 
                                        END AS payent_mode,
            DTXN.bank_ref,DTXN.reason,DTXN.receipt_image");
			$this->db->select("CONVERT_TZ(DTXN.added_date, '+00:00', '".$tz_diff."') AS added_date");
			$this->db->select("CONVERT_TZ(DTXN.modified_date, '+00:00', '".$tz_diff."') AS modified_date");
        }else{
			// $sql = $this->db->select('DTXN.ref_id,DTXN.amount,DTXN.status,U.user_name,DTYPE.key,DTXN.bank_ref,DTXN.reason,DTXN.receipt_image,DTXN.added_date,DTXN.modified_date');
			$sql = $this->db->select("IFNULL(U.email,'-') email,IFNULL(U.phone_no,'-') phone_no,U.user_unique_id,DTXN.ref_id,DTXN.amount,
            CASE 
                                        WHEN DTXN.status = 0 THEN 'Pending'
                                        WHEN DTXN.status = 1 THEN 'Transferred' 
                                        WHEN DTXN.status = 2 THEN 'Fake Entry'
                                        ELSE 'Fake Entry' 
                                        END AS status,
            U.user_name,
                                        CASE 
                                        WHEN DTYPE.key = '".$this->modes[0]."' THEN '".$this->names[0]."' 
                                        WHEN DTYPE.key = '".$this->modes[1]."' THEN '".$this->names[1]."' 
                                        WHEN DTYPE.key = '".$this->modes[2]."' THEN '".$this->names[2]."'
                                        ELSE 'Other' 
                                        END AS payent_mode,
            DTXN.bank_ref,DTXN.reason,DTXN.receipt_image,DTXN.added_date,DTXN.modified_date");
        }

        $sql = $this->db->from(DEPOSIT_TXN.' AS DTXN')
        ->join(USER.' AS U','U.user_id = DTXN.user_id  ','INNER')
        ->join(DEPOSIT_TYPE.' AS DTYPE','DTXN.type_id = DTYPE.type_id','INNER')
        ->order_by($sort_field, $sort_order);
        
        $status = array('0','1','2','3');
		if(in_array($post_data['status'],$status)){
			$this->db->where("DTXN.status",$post_data['status']);
		}

		if(in_array($post_data['mode'],$this->modes)){
			$this->db->where("DTYPE.key",$post_data['mode']);
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{	
			$this->db->like('LOWER( CONCAT(IFNULL(U.user_unique_id,""),IFNULL(U.phone_no,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),CONCAT_WS(" ",U.first_name,U.last_name)))', strtolower($post_data['keyword']));
		}

		if(isset($post_data['from_date']) && isset($post_data['to_date']) && $post_data['from_date'] != '' && $post_data['to_date'] != '')
		{
			$this->db->where("DATE_FORMAT(DTXN.added_date,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(DTXN.added_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}
		
		$tempdb = clone $this->db;
		$total = 0;
		$query = $this->db->get();

		if($this->input->post('csv') == false)
		{
			$total = $query->num_rows();
		}
		
		$detail_rs = $query->result_array();

        
		if($this->input->post('csv') == false)
		{
            $sql = $tempdb->limit($limit,$offset);
		}
        
		$sql = $tempdb->get();
		$result	= $sql->result_array();
        // echo $this->db->last_query();exit();
        return array('result'=>$result,'total'=>$total);
    }

    public function update_deposit_txn($data)
    {
        $ref_id = $data['ref_id'];
        unset($data['ref_id']);
       $this->db->update(DEPOSIT_TXN,$data,array('ref_id'=> $ref_id));
    //    echo $this->db->last_query();die;
       return $this->db->affected_rows();    
    }

    public function create_order($input) {

		$today 		= format_date();
		$orderData 	= array();
		$orderData["user_id"] 			= $input['user_id'];
		$orderData["source"] 			= $input['source'];
		$orderData["source_id"] 		= 0;
		$orderData["type"] 				= 0;
		$orderData["date_added"] 		= $today;
		$orderData["modified_date"] 	= $today;
		$orderData["status"] 			= 0;
		$orderData["real_amount"] 		= $input['amount'];
		$orderData["bonus_amount"] 		= 0;
		$orderData["winning_amount"]	= 0;
		$orderData["points"] 			= 0;
		$orderData["remark"]			= isset($input['remark'])?$input['remark']:'';
		$orderData["reason"] 			= isset($input['reason'])?$input['reason']:'';
        $orderData["status"]            = 1;	
		if(isset($input['custom_data']) && !empty($input['custom_data'])){
			$orderData["custom_data"] = $input['custom_data'];
		}

		$this->db->trans_start();
		$orderData['order_unique_id'] = $this->_generate_order_unique_key();
		$this->db->insert(ORDER, $orderData);
		$order_id = $this->db->insert_id();
		
		if (!$order_id) {            
			return FALSE;
		}

        $balance_arr = [];
        $balance_arr['real_amount'] = $input['amount'];
        $balance_arr['source'] = $input['source'];

		if($orderData["status"] == 1)
		{
			$this->credit_user_balance($orderData["user_id"], $balance_arr);
		}
		$this->db->trans_complete();
		return $order_id;
	}

}