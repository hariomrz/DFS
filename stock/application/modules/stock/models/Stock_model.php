<?php

class Stock_model extends MY_Model {
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * get stock details
     * @param int $stock_id 
     */
    public function detail($stock_id){
        $this->db->select("IFNULL(s.name,s.display_name) as name, s.display_name, s.trading_symbol, IFNULL(s.logo,'') as logo, s.high_price as t_max_price, s.low_price as t_min_price, s.last_price as current_price, IF(s.previous_close > 0,s.previous_close,s.open_price  ) as pr_price, s.price_updated_at",FALSE);
		$this->db->from(STOCK.' s');

		$this->db->where('s.stock_id', $stock_id, FALSE);
        $query = $this->db->get();
        $result = $query->row_array();
        return $result;
    }    

    /**
     * get 52 week high/low price
     * @param int $stock_id 
     */
    public function fifty_two_week_min_max_price($stock_id) {
        $from_date = date('Y-m-d',strtotime(format_date().' 1 year ago'));

        $this->db->select("MIN(close_price) AS min_price, MAX(close_price) AS max_price",FALSE);
		$this->db->from(STOCK_HISTORY);
		$this->db->where('stock_id', $stock_id, FALSE);
        $this->db->where('schedule_date > ', $from_date);
        $this->db->where('close_price > ', 0);
        $query = $this->db->get();
        $result = $query->row_array();
        return $result;
    }

    /**
     * used to get stock histroy data
     * @param int $stock_id 
     */
    public function get_history($stock_id, $from_date) {
        $this->db->select("close_price AS price, schedule_date",FALSE);
		$this->db->from(STOCK_HISTORY);
		$this->db->where('stock_id', $stock_id, FALSE);
        $this->db->where('schedule_date >= ', $from_date);
        $this->db->order_by('schedule_date', 'ASC');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    /**
     * used to get stock histroy data
     * @param int $stock_id 
     */
    public function get_time_wise_history($stock_id, $from_date) {
        $this->db->select("close_price AS price, schedule_date",FALSE);
		$this->db->from(STOCK_HISTORY_DETAILS);
		$this->db->where('stock_id', $stock_id, FALSE);
        $this->db->where('schedule_date >= ', $from_date);
        $this->db->order_by('schedule_date', 'ASC');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }


    /**
     * get stock statics
     * @param int $stock_id 
     */
    public function statics($data){
        $type = $data['type']; // 1 - top gainer, 2 - top loser
       
        $page = isset($data['page']) ? $data['page'] : 0;
        $from_date = $data['from_date']; 
        $day_filter = $data['day_filter'];

        $limit = 5;
		$offset = 0;
        $offset	= $limit * $page;
                
        $this->db->select("s.stock_id, IFNULL(s.name,s.display_name) as name, s.display_name, s.trading_symbol, IFNULL(s.logo,'') as logo, s.last_price as current_price",FALSE);
		$this->db->from(STOCK.' s');
        if($day_filter == 1) {
            //$this->db->select("IFNULL(s.open_price,0) as pr_price, (s.last_price - s.open_price) as price_diff, TRUNCATE(((s.last_price-IFNULL(s.open_price,0))/IFNULL(s.open_price,0))*100, 2) as percent_change",FALSE);
             $this->db->select("IFNULL(s.previous_close,0) as pr_price, (s.last_price - s.previous_close) as price_diff, TRUNCATE(((s.last_price-IFNULL(s.previous_close,0))/IFNULL(s.previous_close,0))*100, 2) as percent_change",FALSE);
        } else {
            $this->db->select("IFNULL(sh.close_price,0) as pr_price, (s.last_price - IFNULL(sh.close_price,0)) as price_diff, TRUNCATE(((s.last_price-IFNULL(sh.close_price,0))/IFNULL(sh.close_price,0))*100, 2) as percent_change",FALSE);
            $this->db->join(STOCK_HISTORY.' sh','sh.stock_id=s.stock_id AND sh.schedule_date = "'.$from_date.'"');
        }
        
        $this->db->where('s.status', 1);
        if(empty($page)) {
            $this->db->limit($limit,$offset);
        }
        
        if($type == 1) {
            $this->db->having('price_diff > ', 0);
            $this->db->order_by('percent_change', 'DESC');
        } else if($type == 2) {
            $this->db->having('price_diff < ', 0);
            $this->db->order_by('percent_change', 'ASC');
        }
        
        $results = $this->db->get()->result_array();

        $this->load->model("wishlist/Wishlist_model");
        $wishlist_stock_ids  = $this->Wishlist_model->fetch_wishlist_stock_ids($data['user_id'], TRUE);
		foreach($results as &$result) {
            $result['is_wish'] = 0;
			if(in_array($result['stock_id'], $wishlist_stock_ids)) {
				$result['is_wish'] = 1;
			}
            
        }

        return $results;
    }  


    /**
     * used to get stock histroy data
     * @param int $stock_id 
     */
    public function get_history_price($stock_id, $from_date) {
        $this->db->select("IFNULL(close_price,0) as close_price",FALSE);
		$this->db->from(STOCK_HISTORY);
		$this->db->where('stock_id', $stock_id, FALSE);
        $this->db->where('schedule_date', $from_date);
        $this->db->order_by('schedule_date', 'ASC');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array();
        return $result['close_price'];
    }

}
