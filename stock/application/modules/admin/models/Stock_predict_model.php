<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_predict_model extends MY_Model {

	public function __construct() {
		parent::__construct();
	}

    function check_fixture_exists($category_id,$start_time,$end_time)
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
                ->where("C.scheduled_date",$start_time)
                ->where("C.end_date",$end_time)
                ->where("C.stock_type",$stock_type)
                ->get()->row_array();

                //echo $this->db->last_query();die();
        return $result;            		
    }


    function get_candle_time_list($fixture_date)
    {
        $result = $this->db->select("MIN(DATE_FORMAT(scheduled_date,'%H:%i:%s')) as start_time,MAX(DATE_FORMAT(end_date,'%H:%i:%s')) as end_time",false)
        ->from(COLLECTION)
        ->where("scheduled_date>",$fixture_date.' 00:00:00')
        ->where("scheduled_date<",$fixture_date.' 23:59:59')
        ->where("stock_type",3)
        ->get()->row_array();

        //echo $this->db->last_query();die('dfd');
        return $result;
    }

     /**
     * to get stock list with close price
     * @return array
     */
	public function stock_closing_rate_for_time($schedule_date){        

		$sort_field	= 'name';
    	$sort_order	= 'ASC';
		
		$this->db->select('s.stock_id, s.name, s.display_name, s.trading_symbol, IFNULL(shd.close_price,0) as close_price,IFNULL(shd.added_date,"") as added_date,IFNULL(shd.schedule_date_utc,"") as schedule_date');
		$this->db->select('IFNULL(s.logo,"") as logo', FALSE);
        $this->db->from(STOCK.' s');
        $this->db->join(STOCK_HISTORY_DETAILS.' shd',"shd.stock_id=s.stock_id AND shd.schedule_date_utc = '{$schedule_date}'", 'LEFT');
		
		$this->db->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->get()->result_array();

        $sql_last_time =  $this->db->query("SELECT MAX(schedule_date_utc) as last_updated_time FROM " . $this->db->dbprefix(STOCK_HISTORY_DETAILS) . "");
        $last_updated = $sql_last_time->row_array();


		return array('result' => $result,'total' => $total,'last_updated'=>$last_updated['last_updated_time']);
	}	

    function update_time_price($data)
    {
        $stocks = $data['stocks'];
        $schedule_date_utc = $data['schedule_date'];
        $stock_data = array();
        $stock_ids = array();
        $current_date = format_date();
        
        $schedule_date = convert_date_to_time_zone($schedule_date,"UTC","Asia/Kolkata");
        foreach($stocks as $stock) {

            $stock_data[]= array(
                'stock_id' => $stock['stock_id'],
                'close_price' => $stock['price'],
                'schedule_date' => $schedule_date,
                'schedule_date_utc' => $schedule_date_utc,
                'status'        =>        1,
                'added_date' => $current_date
            );
        }

        $this->insert_ignore_into_batch(STOCK_HISTORY_DETAILS,$stock_data);

        return true;
    }

    public function get_collection_stocks($post_data)
    {
       $result =  $this->db->select('C.scheduled_date as open_date,C.end_date as close_date,GROUP_CONCAT(CS.stock_id) as stock_id',FALSE)
        ->from(COLLECTION. " C")
        ->join(COLLECTION_STOCK." CS","C.collection_id=CS.collection_id")
        ->where('C.stock_type',3)
        ->where('C.collection_id',$post_data['collection_id'])
        ->where('C.status',0)
       /* ->where('CS.open_price <=',0)*/  
        ->get()->row_array();
       
        return $result;

    }

    public function update_candle_price($data)
    {  
        $update_data = !empty($data['close_price']) ? ['close_price'=>$data['close_price']] : ['open_price'=>$data['open_price']];

        $this->db->where('collection_id',$data['collection_id']);
        $this->db->where('stock_id',$data['stock_id']);
        $this->db->update(COLLECTION_STOCK,$update_data);

        return $this->db->affected_rows();
        
    }

}