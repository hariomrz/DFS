<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Stock_feed_model extends MY_Model {
    
    public $market_id = 1;
    public $market = array('1' => 'NSE');
    public function __construct() {
      parent::__construct();
      $this->db_stock	= $this->load->database('stock_db',TRUE);
    }

    /**
     * Used to get all stock list from NSE
     */
    public function stock_list() {  
      $this->db->select('market_id, name');
      $this->db->from(MARKET);
      $this->db->order_by('market_id', 'ASC');
      $query = $this->db->get();
      if($query->num_rows() > 0) {
        $market_list = $query->result_array(); 
        foreach($market_list as $key => $market) {       
          $exchange = strtoupper($market['name']);
          $market_id = strtoupper($market['market_id']);   
          $post_data =  array(
            'exchange' => $exchange
          );
          $instruments = $this->stock_feed_query('stock/feed/stock_list', $post_data);

          $current_date = format_date();
          $final_instruments = array();
          foreach($instruments as $value) {    
              $symbol = trim($value['symbol']);
              if(!empty($symbol)) {     
                  $final_instruments[]= array(
                      'market_id' => $market_id,
                      'name' => $value['name'],
                      'trading_symbol' => $value['symbol'],
                      'added_date' => $current_date
                  );
              }
          }
          //print_r($final_instruments);die;
          if($final_instruments) {
              $this->replace_into_batch(MASTER_STOCK,$final_instruments);
          }
        }
      }        
    }

    /**
     * Used to update stock latest quote
     */
    public function update_stock_latest_quote($data=array()) {
        $stock_id = isset($data['stock_id']) ? $data['stock_id'] : '';
        $from_date = isset($data['from_date']) ? $data['from_date'] : format_date('today', 'Y-m-d');
        $stock_list = $this->get_stocks_with_close_price_status();
        if(!empty($stock_list)) {
          if(!empty($stock_id)) {
            $stock = array_column($stock_list, NULL, 'stock_id');
            $stock_list = array();
            $stock_list[] = $stock[$stock_id];          
          } 
  
          try {
              $stocks = array_column($stock_list, NULL, 'trading_symbol');
              $symbols = array_column($stock_list, 'trading_symbol');

              $current_date = format_date();
              if(!empty($symbols)) {   
                $post_data =  array(
                  'exchange' => 'NSE',
                  'from_date' => $from_date,
                  'symbols' => $symbols
                );
                $history_data = $this->stock_feed_query('stock/feed/quote', $post_data);
                
                $historical_data = array();
                $quotes_data = array();
                foreach($history_data as $value) { 
                    $stock_data     = $stocks[$value['symbol']];
                    $stock_id       = $stock_data['stock_id'];
                    $stock_status   = $stock_data['status'];
                    $close_price    = $stock_data['close_price'];

                    $meta_data = json_decode($value['meta_data'], TRUE);

                    $quote_data = array();                           
                    $quote_data['open_price']  = round($meta_data['open'], 2);
                    $quote_data['high_price']  = round($meta_data['high'], 2);
                    $quote_data['low_price']   = round($meta_data['low'], 2);
                    $quote_data['last_price']  = round($meta_data['close'], 2);
                    $quote_data['previous_close']  = round($meta_data['previous_close'], 2);
                    $quote_data['modified_date'] = $current_date;
                    $quote_data['price_updated_at'] = $current_date;                    
                    $quote_data['stock_id'] = $stock_id;
                    $quotes_data[] = $quote_data; 

                    $update_data = array();   
                    $history_date   = $meta_data['datetime'];     
                    $update_data['open_price']  = round($meta_data['open'], 2);
                    $update_data['high_price']  = round($meta_data['high'], 2);
                    $update_data['low_price']   = round($meta_data['low'], 2);
                    $update_data['close_price'] = $close_price;
                    if(empty($stock_status)) {
                      $update_data['close_price'] = round($meta_data['close'], 2);
                    }                      
                    $update_data['added_date']  = $current_date;
                    $update_data['schedule_date'] = $history_date; 
                    $update_data['volume'] = $meta_data['volume'];
                    $update_data['stock_id'] = $stock_id;
                    $historical_data[] = $update_data;
                }
               
                if($quotes_data) {
                  $this->replace_into_batch(STOCK,$quotes_data);
                } 
                if($historical_data) {
                  $this->replace_into_batch_with_if_condition(STOCK_HISTORY,$historical_data);                        
                }              
            }
          } catch(Exception $e){
            $response = $e->getMessage();
            log_message('error', 'Quote latest data error: '.$response);
          } 
        }      
    }

    /**
     * Used to update stock historical data minute wise
     */
    public function stock_historical_data_minute_wise($data=array()) {
        $stock_id = isset($data['stock_id']) ? $data['stock_id'] : 0;
            
        $start_date = isset($data['from_date']) ? $data['from_date'] : format_date('today', 'Y-m-d 09:14:00');
        $end_date = isset($data['to_date']) ? $data['to_date'] : format_date('today', 'Y-m-d 15:40:00');

        $stock_list = $this->get_nifty_fifty_stocks();
        if(!empty($stock_list)) {
          if(!empty($stock_id)) {
            $stock = array_column($stock_list, NULL, 'stock_id');
            $stock_list = array();
            $stock_list[] = $stock[$stock_id];          
          } 
        
          try {        
                $stocks = array_column($stock_list, 'stock_id', 'trading_symbol');        
                $symbols = array_column($stock_list, 'trading_symbol');
                $current_date = format_date();
                if(!empty($symbols)) {
                  $post_data =  array(
                    'exchange' => 'NSE',
                    'from_date' => $start_date,
                    'to_date' => $end_date,
                    'symbols' => $symbols
                  );
                  $history_data = $this->stock_feed_query('stock/feed/time_series', $post_data);

                  $historical_data = array();
                  foreach($history_data as $value) {              
                      $stock_id = $stocks[$value['symbol']];
                      $meta_data = json_decode($value['meta_data'], TRUE);
                      //print_r($history);die;
                      $history_data = array();
                      $history_date = $meta_data['datetime'];    
                      $history_date = date('Y-m-d H:i:s',strtotime($history_date)); //// need to calculate it based on return date from API        
                      $history_date_utc = convert_date_to_time_zone($history_date); //// need to calculate it based on return date from API        
                                      
                      $history_data['close_price'] = $meta_data['close'];
                      $history_data['added_date'] = $current_date;
                      $history_data['schedule_date'] = $history_date; 
                      $history_data['schedule_date_utc'] = $history_date_utc; 
                      $history_data['stock_id'] = $stock_id;
                      $historical_data[] = $history_data; 
                  }
                  if($historical_data) {
                    $this->replace_into_batch_with_if_condition(STOCK_HISTORY_DETAILS,$historical_data);
                  }
                 /* $data['action'] =  'update_last_close_price_minute_wise';        
                  $this->load->helper('queue');
                  add_data_in_queue($data,'stock_feed');    */                      
                }
            } catch(Exception $e){
                $response = $e->getMessage();
                log_message('error', 'Quote minute wise data error: '.$response);
            } 
        }
      }

    /**
     * Used to update stock historical data day wise
     */
    public function stock_historical_data_day_wise($data=array()) {
      $stock_id = isset($data['stock_id']) ? $data['stock_id'] : 0;
      $start_date = isset($data['from_date']) ? $data['from_date'] : date('Y-m-d',strtotime(format_date().' 1 year ago'));
      $end_date = isset($data['to_date']) ? $data['to_date'] : format_date('today', 'Y-m-d');
           
      $stock_list = $this->get_stocks_with_close_price_status();
      if(!empty($stock_list)) {
        if(!empty($stock_id)) {
          $stock = array_column($stock_list, NULL, 'stock_id');
          $stock_list = array();
          $stock_list[] = $stock[$stock_id];          
        } 
        try {                     
            $stocks = array_column($stock_list, NULL, 'trading_symbol');
            $symbols = array_column($stock_list, 'trading_symbol');
            if(!empty($symbols)) {
              $post_data =  array(
                'exchange' => 'NSE',
                'from_date' => $start_date,
                'to_date' => $end_date,
                'symbols' => $symbols
              );
              $history_data = $this->stock_feed_query('stock/feed/day_quote', $post_data);

              $current_date = format_date();              
              $historical_data = array();
              foreach($history_data as $key => $history) {
                $stock_data     = $stocks[$history['symbol']];
                $stock_id       = $stock_data['stock_id'];
                $stock_status   = $stock_data['status'];
                $close_price    = $stock_data['close_price'];
                $update_data    = array();
                $meta_data      = json_decode($history['meta_data'], TRUE);                
                $history_date   = $meta_data['datetime'];                             
                
                $update_data['open_price']  = $meta_data['open'];
                $update_data['high_price']  = $meta_data['high'];
                $update_data['low_price']   = $meta_data['low'];
                $update_data['close_price'] = $close_price;
                if(empty($stock_status)) {
                    $update_data['close_price'] = $meta_data['close'];
                }                      
                $update_data['added_date']  = $current_date;
                $update_data['schedule_date'] = $history_date; 
                $update_data['volume'] = $meta_data['volume'];
                $update_data['stock_id'] = $stock_id;
                $historical_data[] = $update_data;                                
              } 
              if($historical_data) {
                $this->replace_into_batch_with_if_condition(STOCK_HISTORY,$historical_data);                        
              }                                           
            }
        } catch(Exception $e){
          $response = $e->getMessage();
          log_message('error', 'Quotes Day wise data error: '.$response);
        }
      }
    }

    function get_nifty_fifty_stocks() {
      $nfstock_cache_key = "st_nfstock";
      $stock_list = $this->get_cache_data($nfstock_cache_key);
      if(empty($stock_list)) {
        $this->db->select('s.stock_id, s.trading_symbol');
        $this->db->from(STOCK.' s');
        $this->db->order_by('s.stock_id', 'ASC');
        $query = $this->db->get();
        if($query->num_rows() > 0) {
          $stock_list = $query->result_array(); 
          $this->set_cache_data($nfstock_cache_key, $stock_list, REDIS_24_HOUR);
        }
      }
      return $stock_list;
    }


    function get_stocks_with_close_price_status() {      
        $schedule_date = format_date('today', 'Y-m-d');
        $this->db->select('s.stock_id, s.trading_symbol, IFNULL(sh.status,0) as status, IFNULL(sh.close_price,0) as close_price');
        $this->db->from(STOCK.' s');
        $this->db->join(STOCK_HISTORY.' sh','sh.stock_id=s.stock_id AND sh.schedule_date = "'.$schedule_date.'"', 'LEFT');
        $this->db->order_by('s.stock_id', 'ASC');
        $query = $this->db->get();
        $stock_list = $query->result_array(); 
        return $stock_list;
    }


    /**
     * Used to get all market(exchange) holiday list
     */
    public function holiday_list() {
      $this->db->select('market_id, name');
      $this->db->from(MARKET);
      $this->db->order_by('market_id', 'ASC');
      $query = $this->db->get();
      if($query->num_rows() > 0) {
        $current_date = format_date();
        $year = format_date('today', 'Y');
        $market_list = $query->result_array(); 
        foreach($market_list as $key => $market) {       
          $exchange = strtoupper($market['name']);
          $market_id = strtoupper($market['market_id']);    
          $post_data =  array(
            'year' => $year,
            'exchange' => $exchange
          );
          $holidays = $this->stock_feed_query('stock/feed/holiday_list', $post_data);
                      
          $final_holiday_list = array();
          foreach($holidays as $key => $value) {                      
            $final_holiday_list[]= array(
                'market_id' => $market_id,
                'holiday_date' => $value['holiday_date'],
                'year' => $year,
                'description' => $value['description'],
                'added_date' => $current_date
            );               
          }
          //print_r($final_holiday_list);die;
          if($final_holiday_list) {
            $this->replace_into_batch(HOLIDAY,$final_holiday_list);

            $holiday_cache_key = "holiday_".$market_id."_".$year;
            $this->delete_cache_data($holiday_cache_key);
          }         
        }
      }
    }


    /**
     * Replace into Batch statement
     * Generates a replace into string from the supplied data
     * @param    string    the table name
     * @param    array    the update data
     * @return   string
     */
    function replace_into_batch_with_if_condition($table, $data, $if_column='close_price') {
      $column_name = array();
      $update_fields = array();
      $append = array();
      foreach ($data as $i => $outer) {
          $column_name = array_keys($outer);
          $coloumn_data = array();
          foreach ($outer as $key => $val) {
              if ($i == 0) {
                  if($key == $if_column) {
                    $update_fields[] = "`" . $key . "`" . '=IF(`status` > 0,' . $key . ', VALUES(`' . $key . '`))';
                  } else {
                    $update_fields[] = "`" . $key . "`" . '=VALUES(`' . $key . '`)';
                  }                  
              }

              if (is_numeric($val)) {
                  $coloumn_data[] = $val;
              } else {
                  $coloumn_data[] = "'" . replace_quotes($val) . "'";
              }
          }
          $append[] = " ( " . implode(', ', $coloumn_data) . " ) ";
      }

      $sql = "INSERT INTO " . $this->db->dbprefix($table) . " ( " . implode(", ", $column_name) . " ) VALUES " . implode(', ', $append) . " ON DUPLICATE KEY UPDATE " . implode(', ', $update_fields);
      
      $this->db->query($sql);
  }

  

  /**
   * This function used to fetch data from stock feed
   */
  public function stock_feed_query(string $uri, array $parameters) {
		
    $headers = array(
        'Content-Type: application/json',
    );

    $standard_parameters = [
        'token' => STOCK_FEED_TOKEN,
    ];

    $post_parameters = array_merge($standard_parameters, $parameters);
    $data_string = json_encode($post_parameters);
    $url = STOCK_FEED_URL . $uri;

    $curl        = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_POST           => 1,
      CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_POSTFIELDS     => $data_string,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL            => $url,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0
		));
		
    try {
        $response = curl_exec($curl);
        if ($response === FALSE) {
            throw new Exception('Error Stock Feed - ' . curl_error($curl));
        }
        curl_close($curl);
        $result = json_decode($response, true);
        $status_code = isset($result['response_code']) ? $result['response_code'] : '';
        if($status_code != 200) {
            $message = isset($result['message']) ? $result['message'] : '';
            $status = isset($result['response_code']) ? $result['response_code'] : '';
            throw new Exception('Error Stock Feed - ' . $status.': '.$message);
        }
        return $result['data'];
    } catch (Exception $e) {
      throw new Exception('Error Stock Feed - ' .  $e->getMessage());
    }
  }

  /**
   * This function used to fetch data from stock feed via socket
   */
  public function stock_data_socket($data=array()) {
        $stock_id = isset($data['stock_id']) ? $data['stock_id'] : 0;

        $start_date = isset($data['from_date']) ? $data['from_date'] : format_date('today', 'Y-m-d 09:14:00');
        $end_date = isset($data['to_date']) ? $data['to_date'] : format_date('today', 'Y-m-d 15:40:00');

        $stock_list = $this->get_nifty_fifty_stocks();
        if(!empty($stock_list)) {
          if(!empty($stock_id)) {
            $stock = array_column($stock_list, NULL, 'stock_id');
            $stock_list = array();
            $stock_list[] = $stock[$stock_id]; 
          } 

          try { 
                $stocks = array_column($stock_list, 'stock_id', 'trading_symbol'); 
                $symbols = array_column($stock_list, 'trading_symbol');
                $current_date = format_date();
                if(!empty($symbols)) {
                  $post_data =  array(
                    'exchange' => 'NSE',
                    'from_date' => $start_date,
                    'to_date' => $end_date,
                    'symbols' => $symbols
                  );
                  $history_data = $this->stock_feed_query('stock/feed/time_series_socket', $post_data);

                  $historical_data = array();
                  foreach($history_data as $value) {
                      $stock_id = $stocks[$value['symbol']];

                      //print_r($history);die;
                      $history_data = array();
                      $history_date = $value['datetime'];     
                      $history_date = date('Y-m-d H:i:s',strtotime($history_date)); //// need to calculate it based on return date from API 
                      $history_date_utc = convert_date_to_time_zone($history_date); //// need to calculate it based on return date from API 

                      $history_data['close_price'] = $value['price'];
                      $history_data['added_date'] = $current_date;
                      $history_data['schedule_date'] = $history_date; 
                      $history_data['schedule_date_utc'] = $history_date_utc; 
                      $history_data['stock_id'] = $stock_id;
                      $historical_data[] = $history_data; 
                  }
                  if($historical_data) {
                    $this->replace_into_batch_with_if_condition(STOCK_HISTORY_DETAILS,$historical_data);
                  }
                }
            } catch(Exception $e){
                $response = $e->getMessage();
                log_message('error', 'socket minute wise data error: '.$response);
            } 
        }
      }

  /**
  * Update Last close price for very minutes whose price not coming
  */
  public function update_last_close_price($data)
  {  
    

   /* $current_date   =   $d->format('Y-m-d H:i:00');
    $d->sub(new DateInterval('PT2M'));
    $from_date  = $d->format('Y-m-d H:i:00');
    $date = '2022-09-09 15:00:00';*/

    $current_date_time  =  $data['current_date_time'];//'2022-09-09 15:00:00';
    $sql = "SELECT stock_id from ".$this->db->dbprefix(STOCK)." where stock_id NOT IN  (SELECT stock_id from ".$this->db->dbprefix(STOCK_HISTORY_DETAILS)."  where schedule_date = '".$current_date_time."')";
      $res = $this->db->query($sql)->result_array();
      //echo  $this->db->last_query();die;
      
      if(!empty($res)){
          $stock_ids=array_column($res, 'stock_id');
         $historical_data = array();
          foreach ($stock_ids as $key => $value) {
            $current_date = date('Y-m-d');

            $stock_data = $this->db->select('stock_id,close_price,schedule_date')
                                ->from(STOCK_HISTORY_DETAILS)
                                ->where('stock_id',$value)
                                ->where("DATE_FORMAT(schedule_date ,'%Y-%m-%d') ='".$current_date."'")
                                ->order_by('schedule_date','DESC')
                                ->limit(1)
                                ->get()->row_array();

             $history_data = array();
             if(!empty($stock_data))
             {

                $history_date = $current_date_time; //// need to calculate it based on return date from API     
                $history_date_utc = convert_date_to_time_zone($history_date); //// need to calculate it based on return date from API                  
                $history_data['close_price'] = $stock_data['close_price'];
                $history_data['added_date'] = format_date();
                $history_data['schedule_date'] = $history_date; 
                $history_data['schedule_date_utc'] = $history_date_utc; 
                $history_data['stock_id'] = $value;
                $historical_data[] = $history_data; 
             }        
          }
           
            if($historical_data) {
                $this->replace_into_batch_with_if_condition(STOCK_HISTORY_DETAILS,$historical_data);
            }
           
                       
      }
        return true;
    }

}
