<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Collection_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		
	}

    /**
	* [get_fixtures description]
	* @Summary This function used for get all fixtures
	*/	
	public function get_fixtures() {
		$sort_field	= 'scheduled_date';
		$sort_order	= 'ASC';
		$limit		= 10;
		$page		= 0;

		$post_data = $this->input->post();
		if(isset($post_data['items_perpage'])) {
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page'])) {
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && ($post_data['sort_field']) && in_array($post_data['sort_field'],array('scheduled_date'))) {
			$sort_field = $this->input->post('sort_field');
		}

		if(isset($post_data['sort_order']) && ($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;

		$category_id	= $post_data['category_id'];
		$status		= isset($post_data['status']) ? $post_data['status'] : 0;

        $current_date_time = format_date();
        
        $this->db->select("CM.collection_id, CM.published_date, CM.scheduled_date, CM.end_date,CM.name", FALSE);
        $this->db->select('IFNULL(CM.custom_message,"") as custom_message', FALSE);        
        $this->db->from(COLLECTION . " as CM");
        //$this->db->join(CONTEST_CATEGORY . ' as CC', 'CC.category_id = CM.category_id', "INNER");
        $this->db->where('CM.category_id', $category_id, FALSE);

		$stock_type = 1;
		if(!empty($post_data['stock_type']))
		{
			$stock_type = $post_data['stock_type'];
		}

		$this->db->where('CM.stock_type',$stock_type);
        if($status == 2) {
            $this->db->where('CM.status', 1);
			$sort_order	= 'DESC';
        } else {
            $this->db->where('CM.status', 0);
            if($status == 1) {
                $this->db->where("CM.scheduled_date > '".$current_date_time."' ");
            } else {
				$sort_order	= 'DESC';
                $this->db->where("CM.scheduled_date <= '".$current_date_time."'"); //Live //AND CM.end_date > '".$current_date_time."'
            }
        }

        $this->db->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->limit($limit,$offset)->get()->result_array();

		return array('result' => $result,'total' => $total);        
	}

    /**
     * Used to update custom message for fixture
     */
    public function update_fixture_custom_message($post_data){
		$custom_message = $post_data['custom_message'];
		if(isset($post_data['is_remove']) && $post_data['is_remove'] == 1){
			$custom_message = "";
		}
		$upd_data = array('custom_message' => $custom_message); //,"notify_by_admin"=>1
        $this->db->where('collection_id', $post_data['collection_id'], FALSE);
        $this->db->update(COLLECTION, $upd_data);
		return true;
	}

	/**
	* [get_fixture_stats]
	* @Summary This function used to get fixtures stats
	*/	
	public function get_fixture_stats($post_data) {
		$sort_field	= 'S.name';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;

		$status = $post_data['status'];
        $collection_id = $post_data['collection_id'];

		if(isset($post_data['items_perpage'])) {
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page'])) {
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && ($post_data['sort_field']) && in_array($post_data['sort_field'],array('result_rate','closing_rate','joining_rate','display_name','lot_size','name', 'trading_symbol', 'exchange_token'))) {
			$sort_field = $this->input->post('sort_field');
			if($status == 1 && in_array($sort_field, array('result_rate'))) {
				if($sort_field == 'result_rate') {
					$sort_field = 'SH.close_price';
				} 
			} else {	
				if($sort_field == 'closing_rate') {
					$sort_field = 'SH.open_price';
				} else if($sort_field == 'result_rate') {
					$sort_field = 'S.last_price';
				} else if(in_array($sort_field, array('display_name','lot_size'))) {
					$sort_field = 'CS.'.$sort_field;
				} else {
					$sort_field = 'S.'.$sort_field;
				}
			}
		}

		if(isset($post_data['sort_order']) && ($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
				
		$this->db->select('CS.stock_name as display_name, CS.lot_size,CS.close_price,CS.open_price', FALSE);
		$this->db->select('S.name, S.trading_symbol,IFNULL(S.industry_id,0) as industry_id, S.cap_type,IFNULL(I.display_name,"") as industry_name',FALSE);
		$this->db->select('IFNULL(S.logo,"") as logo', FALSE);
		if($post_data['stock_type'] !=3)
		{
			$this->db->select('SH1.close_price as closing_rate');
			//$this->db->select('(CASE WHEN C.status=1 THEN SH.open_price ELSE S.open_price END) as closing_rate', FALSE);
			$this->db->select('(CASE WHEN C.status=1 THEN SH.close_price ELSE S.last_price END) as result_rate', FALSE);
		}

		
		$this->db->from(COLLECTION_STOCK . " AS CS");
		$this->db->join(COLLECTION.' C','C.collection_id = CS.collection_id','INNER');
		$this->db->join(STOCK.' S', "S.stock_id = CS.stock_id", 'INNER');
		if($post_data['stock_type'] !=3)
		{
		 $this->db->join(STOCK_HISTORY.' SH1','SH1.stock_id=CS.stock_id AND SH1.schedule_date=DATE_FORMAT(C.published_date,"%Y-%m-%d")','INNER');
		 $this->db->join(STOCK_HISTORY.' SH','SH.stock_id=CS.stock_id AND SH.schedule_date=DATE_FORMAT(C.end_date,"%Y-%m-%d")','LEFT');
		}
		$this->db->join(INDUSTRY.' I','S.industry_id=I.industry_id','LEFT');
			    	           
        $this->db->where("CS.collection_id",$collection_id, FALSE);
		$this->db->group_by('CS.stock_id');
        $this->db->order_by($sort_field, $sort_order);
        
        //$result = $this->db->limit($limit,$offset)->get()->result_array();
        $result = $this->db->get()->result_array();

		return $result;     
	}
}