<?php

class Wishlist_model extends MY_Model {
    protected $wishlist_stock_ids = array();
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Add to wishlist
     * @param int $user_id
     * @param int $stock_id 
     */
    public function add($user_id, $stock_id){
        $insert_data = array(
                "user_id" => $user_id,
                "stock_id" => $stock_id,
                "added_date" => format_date()
            );
        $this->db->insert(WISHLIST, $insert_data);
    }

    /**
     * Remove from  wishlist
     * @param int $user_id
     * @param int $stock_id 
     */
    public function remove($user_id, $stock_id){
        $this->db->where("user_id",$user_id, FALSE);
        $this->db->where("stock_id",$stock_id, FALSE);
        $this->db->delete(WISHLIST);
    }

    /**
     * Used to get user wishlist
     */
    public function list($data) {
        $from_date = $data['from_date']; 
        $day_filter = $data['day_filter'];
        $user_id = $data['user_id']; 

        $this->db->select("s.stock_id, IFNULL(s.name,s.display_name) as stock_name, s.display_name, s.trading_symbol, IFNULL(s.logo,'') as logo, s.last_price as current_price",FALSE);
		$this->db->from(WISHLIST.' w');
        $this->db->join(STOCK.' s', 's.stock_id=w.stock_id');

        if($day_filter == 1) {
            $this->db->select("IFNULL(s.open_price,0) as pr_price, (s.last_price - s.open_price) as price_diff, TRUNCATE(((s.last_price-IFNULL(s.open_price,0))/IFNULL(s.open_price,0))*100, 2) as percent_change",FALSE);
        } else {
            $this->db->select("IFNULL(sh.close_price,0) as pr_price, (s.last_price - IFNULL(sh.close_price,0)) as price_diff, TRUNCATE(((s.last_price-IFNULL(sh.close_price,0))/IFNULL(sh.close_price,0))*100, 2) as percent_change",FALSE);
            $this->db->join(STOCK_HISTORY.' sh','sh.stock_id=s.stock_id AND sh.schedule_date = "'.$from_date.'"');
        }
		$this->db->where('w.user_id', $user_id, FALSE);
        $this->db->order_by('percent_change', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    /**
     * [is_wishlist This is used to check the status of wishlist]
     * @param  [int] $user_id   [User Id]
     * @param  [int] $stock_id   [stock Id]
     * @return [int]           [return wishlist Status]
     */
    function is_wishlist($user_id, $stock_id) {
        $this->db->select('wishlist_id');
        $this->db->from(WISHLIST);
        $this->db->where("user_id",$user_id, FALSE);
        $this->db->where("stock_id",$stock_id, FALSE);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return 1; // in wishlist            
        } else {
            return 0; // Not in wishlist      
        }
    }

    /**
	 * [set_wishlist_stock_ids used to assign user wishlist stock ids ]
	 * @param type $user_id
	 */
	function set_wishlist_stock_ids($user_id) {
		$this->wishlist_stock_ids = $this->fetch_wishlist_stock_ids($user_id, true);
	}

	/**
	 * [get_wishlist_stock_ids used to return user wishlist stock ids]
	 * @return string
	 */
	function get_wishlist_stock_ids() {
		return $this->wishlist_stock_ids;
    }
    
    /** 
     * 
     * @param type $user_id Loggedin user id
     * @param type $array return response as array or not 
     * @return type
     */
	function fetch_wishlist_stock_ids($user_id, $array=false) {
        $wishlist_data = '';
        $wishlist_cache_key = "st_wishlist_".$user_id;
        $wishlist_data = $this->get_cache_data($wishlist_cache_key);  

        if (empty($wishlist_data)) {
            $this->db->simple_query('SET SESSION group_concat_max_len=150000');
            $this->db->select(' GROUP_CONCAT(stock_id) as stock_ids');
            $this->db->from(WISHLIST);           
            $this->db->where('user_id', $user_id);
            $this->db->order_by('stock_id', 'ASC');
            $result = $this->db->get();
            //echo $this->user_db->last_query(); die;
            if ($result->num_rows() > 0) {
                $row = $result->row_array();
                if (!empty($row['stock_ids'])) {
                    $wishlist_data = $row['stock_ids'];  
                    $wishlist_data = explode(',', $wishlist_data);                       
                    $this->set_cache_data($wishlist_cache_key,$wishlist_data,REDIS_7_DAYS);                   
                }
            }
        }

        if (empty($wishlist_data)) {
            $wishlist_data = array();
        }
        
        if ($array) {
            return $wishlist_data;                            
        } else {
            return implode(',', $wishlist_data);
        }
    } 
}
