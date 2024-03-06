<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_model extends MY_Model {

	public function __construct() {
		parent::__construct();
	}

    /**
     * to get stock auto suggestion list
     * @param string $keyword
     * @return array
     */
    function auto_suggestion_list($keyword) {
        $this->db->select('MS.master_stock_id, MS.exchange_token, MS.instrument_token, MS.name, MS.trading_symbol, MS.lot_size');
        $this->db->from(MASTER_STOCK.' MS');
        $this->db->join(STOCK.' S', 'S.exchange_token=MS.exchange_token and S.status=1', 'LEFT');
        $keyword = $this->db->escape_like_str($keyword);
        $this->db->where("(MS.name like '%" . $keyword . "%' or MS.trading_symbol like '%" . $keyword . "%')");

        $this->db->where('S.stock_id IS NULL');
        $this->db->order_by('MS.name', 'ASC');
        $query = $this->db->get();
        $stocks = array();
        if($query->num_rows()) {
            $stocks = $query->result_array();
        }
        return $stocks;
    }

    /**
     * to save stock in nifty 50 list
     * @param array $data
     * @return array
     */
    function save($data) {
        $this->db->insert(STOCK,$data);
		return $this->db->insert_id();
    }

    /**
     * update stock data
     * @param $data
     * @param $stock_id
     * @return array
     */
	public function update_stock($data,$stock_id){
		$this->db->update(STOCK,$data,array('stock_id'=>$stock_id));
		return true;
	}

	/**
     * to get stock list
     * @return array
     */
	public function get_list(){
        $post = $this->input->post();
		$limit = 10;
		$offset = 0;
		$sort_field	= 'name';
    	$sort_order	= 'ASC';
		if(isset($post['items_perpage'])) {
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}

		if(isset($post['sort_field']) && in_array($post['sort_field'],array('name, display_name, trading_symbol, lot_size, exchange_token'))) {
			$sort_field = $post['sort_field'];
		}

		if(isset($post['sort_order']) && in_array($post['sort_order'],array('DESC','ASC'))) {
			$sort_order = $post['sort_order'];
		}

        if(isset($post['keyword']) && $post['keyword'] != "") {
            $keyword = $this->db->escape_like_str($post['keyword']);
            $this->db->where("(s.name like '%" . $keyword . "%' or s.display_name like '%" . $keyword . "%' or s.trading_symbol like '%" . $keyword . "%')");
        }

        if(isset($post['industry_id']) && $post['industry_id'] != "") {
            $this->db->where('industry_id',$post['industry_id']);
        }

        if(isset($post['cap_type']) && $post['cap_type'] != "") {
            $this->db->where('cap_type',$post['cap_type']);
        }


        $offset	= $limit * $page;

		$this->db->select('s.stock_id, s.name, s.display_name, s.trading_symbol, s.lot_size, s.exchange_token,s.cap_type,s.industry_id');
		$this->db->select('IFNULL(s.logo,"") as logo', FALSE);
        $this->db->from(STOCK.' s');
		$this->db->where('s.status', 1);

        if(isset($post['lot_size']) && $post['lot_size'] != "") {
            $this->db->where('s.lot_size', $post['lot_size']);
        }   

		$this->db->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->limit($limit,$offset)->get()->result_array();

		return array('result' => $result,'total' => $total);
	}

    /**
     * to get lot size list
     * @return array
     */
    function get_lot_size_list() {
        $this->db->select('s.lot_size');
		$this->db->from(STOCK.' s');
		$this->db->where('s.status', 1);
        $this->db->group_by('s.lot_size');
        $this->db->order_by('s.lot_size', 'ASC');
        $query = $this->db->get();
        $lot_sizes = array();
        if($query->num_rows()) {
            $lot_sizes = $query->result_array();
        }
        return $lot_sizes;
    }

    /**
     * Used to get stock list for publish fixture
     */
    function get_all_stocks($collection_id) {
        $this->db->select("IF(cs.collection_stock_id IS NULL, 0, 1) as is_published, s.stock_id, IFNULL(cs.stock_name,s.display_name) as display_name, IFNULL(s.name,s.display_name) as name, s.trading_symbol, IFNULL(cs.lot_size,s.lot_size) as lot_size, s.exchange_token, IFNULL(s.logo,'') as logo",FALSE);
		$this->db->from(STOCK.' s');
        $this->db->join(COLLECTION_STOCK.' cs', 'cs.stock_id=s.stock_id and cs.collection_id='.$collection_id, 'LEFT');
		
        $this->db->where('CASE 
                            WHEN cs.collection_stock_id IS NULL THEN s.status=1 
                            ELSE 1 
                        end');


        $post = $this->input->post();

        if(isset($post['keyword']) && $post['keyword'] != "") {
            $keyword = $this->db->escape_like_str($post['keyword']);
            $this->db->where("(s.name like '%" . $keyword . "%' or s.display_name like '%" . $keyword . "%' or s.trading_symbol like '%" . $keyword . "%')");
        }
        if(!empty($post['industry_id']))
        {
            $this->db->where('s.industry_id',$post['industry_id']);
        }

        if(!empty($post['cap_type']))
        {
            $this->db->where('s.cap_type',$post['cap_type']);
        }

        $this->db->order_by('s.name', 'ASC');
        $this->db->order_by('is_published', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }

    /**
	* [get_collection_by_id description]
	* Summary :- Get collection detail using collection id
	*/
	public function get_collection_by_id($collection_id)
	{
		$post_param = $this->input->post();
		
		$this->db->select("C.category_id,CC.name,C.scheduled_date,C.end_date")
				->from(COLLECTION.' C')
                ->join(CONTEST_CATEGORY.' CC','C.category_id=CC.category_id');
				
		if(!empty($collection_id))
		{
			$this->db->where("C.collection_id", $collection_id);
		}
		$sql = $this->db->get();
		$result = $sql->row_array();
					 
		return ($result) ? $result : array();
	}

    function check_fixture_exists($category_id,$date)
    {
        $stock_type = $this->input->post('stock_type');
        if(empty($stock_type))
        {
            $stock_type = 1;
        }
       $result = $this->db->select("CC.name")
				->from(COLLECTION.' C')
                ->join(CONTEST_CATEGORY.' CC',"C.category_id=CC.category_id")
                ->where('C.category_id',$category_id)
                ->where("DATE_FORMAT(C.scheduled_date,'%Y-%m-%d')",$date)
                ->where("C.stock_type",$stock_type)
                ->get()->row_array();

                //echo $this->db->last_query();die();
        return $result;            		
    }

    function publish_fixture($data) {
        $this->db->trans_start();

		$this->db->insert(COLLECTION,$data['collection_data']);

		$collection_id = $this->db->insert_id();
        $stock_data = array();
        foreach($data['stocks'] as $stock)
        {
            $stock_data[]= array(
                'stock_name' => $stock['name'],
                'collection_id' => $collection_id,
                'stock_id' => $stock['stock_id'],
                'lot_size' => 0
            );
            //$stock['lot_size']
        }

        //print_r($stock_data);die;
        //$this->db->table_name= COLLECTION_STOCK;
        $this->replace_into_batch(COLLECTION_STOCK,$stock_data);
		$this->db->trans_complete();
		$this->db->trans_strict(FALSE);

		if ($this->db->trans_status() === FALSE) {
		    // generate an error... or use the log_message() function to log your error
			$this->db->trans_rollback();
			return false;
		} else {
			$this->db->trans_commit();	
			return $collection_id;
		}
    }

    /**
     * Used to update fixture stock
     */
    function update_fixture_stocks($data) {
        $stock_data = array();
        $collection_id = $data['collection_id'];
        $this->db->trans_start();

        foreach($data['stocks'] as $stock) {
            $stock_data[]= array(
                'stock_name' => $stock['name'],
                'collection_id' => $collection_id,
                'stock_id' => $stock['stock_id'],
                'lot_size' => 0
            );
        }

        $this->replace_into_batch(COLLECTION_STOCK,$stock_data);
		$this->db->trans_complete();
		$this->db->trans_strict(FALSE);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return false;
		} else {
			$this->db->trans_commit();
		}
    }


    /**
     * to get stock list with close price
     * @return array
     */
	public function stock_list_with_close_price($schedule_date){        

		$sort_field	= 'name'; 
    	$sort_order	= 'ASC';
		
		$this->db->select('s.stock_id, sh.schedule_date,s.name, s.display_name, s.trading_symbol, s.lot_size, IFNULL(sh.close_price,0) as close_price, IFNULL(sh.status,0) as status');
		$this->db->select('IFNULL(s.logo,"") as logo', FALSE);
        $this->db->from(STOCK.' s');
        $this->db->join(STOCK_HISTORY.' sh','sh.stock_id=s.stock_id AND sh.schedule_date = "'.$schedule_date.'"', 'LEFT');
		$this->db->where('s.status',1);
		$this->db->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->get()->result_array();

		return array('result' => $result,'total' => $total);
	}	

    
    /**
     * Update stocks close price and status
     */
    public function update_close_price($data) {
        $stocks = $data['stocks'];
        $schedule_date = $data['schedule_date'];
        $stock_data = array();
        $stock_ids = array();
        $current_date = format_date();
        $this->db->trans_start();
        foreach($stocks as $stock) {
            $stock_ids[] = $stock['stock_id'];
            $status = isset($stock['status']) ? $stock['status'] : 2;
            if(!in_array($status, array(1,0))) {
                $status = 2;
            }
            if(empty($status)) {
                $status = 2;
            }
            $stock_data[]= array(
                'stock_id' => $stock['stock_id'],
                'close_price' => $stock['close_price'],
                'schedule_date' => $schedule_date,
                'status' => $status,
                'added_date' => $current_date
            );
        }
        if(!empty($stock_data)) {
            $this->replace_into_batch(STOCK_HISTORY,$stock_data);

            $this->db->where('schedule_date', $schedule_date);
            $this->db->where_not_in('stock_id', $stock_ids);
            $this->db->update(STOCK_HISTORY, array('status'=>2, 'added_date' => $current_date));

            $this->db->trans_complete();
            $this->db->trans_strict(FALSE);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            } else {
                $this->db->trans_commit();
                return true;
            }            
        }
        return false;
    }
    
    /**
     * Update stocks close price status
     */
    public function update_price_status($data) {
        $schedule_date = $data['schedule_date'];
        $result = $this->stock_list_with_close_price($schedule_date);
        $stocks = $result['result'];
        $current_date = format_date();
        $stock_data = array();
        $this->db->trans_start();    
        foreach($stocks as $stock) {
            $stock_data[]= array(
                'stock_id' => $stock['stock_id'],
                'schedule_date' => $schedule_date,
                'status' => 2,
                'added_date' => $current_date
            );
        }

        if(!empty($stock_data)) {
            $this->replace_into_batch(STOCK_HISTORY,$stock_data);

            $this->db->trans_complete();
            $this->db->trans_strict(FALSE);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            } else {
                $this->db->trans_commit();
                return true;
            }
        } 
        return false;       
    }    
}