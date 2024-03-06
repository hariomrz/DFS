<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_predict extends Common_Api_Controller {
    public $market_id = 1;
    public $stock_type=3;
	public function __construct() {
		parent::__construct();
		$_POST = $this->post();
		$this->load->model('admin/Stock_predict_model');
        $this->contest_categoty_map= array();
		$this->contest_categoty_map[1] = array('func' => 'get_daily_category_dates');
		
	}

    	 /**
    * Function used for publish fixture for create contest
    * @param string $season_game_uid
    * @param array $stock_list
    * @return array
    */
    public function publish_fixture_post()
    {
        $post_data = $this->input->post();
      
        $this->form_validation->set_rules('fixture_date', 'Fixture Date', 'trim|required');
        $this->form_validation->set_rules('start_time', 'Start Time', 'trim|required');
        $this->form_validation->set_rules('end_time', 'End Time', 'trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        
        $category_id = 1;
		$this->validate_stock_details($post_data);
        
        
        $collection_stock_arr = array();
        $current_date = format_date();
        $this->load->model('admin/Stock_model');
        $category = $this->Stock_model->get_single_row('name',CONTEST_CATEGORY,array('category_id'=> $category_id));
		//prepare collection details
		$insert_data = array();
        $insert_data['collection_data'] = array();
        $insert_data['collection_data']['category_id'] = $category_id;
        $insert_data['collection_data']['name'] = !empty($post_data['name'])?$post_data['name']:$category['name'];
        $insert_data['collection_data']['added_date'] = $current_date;
        $insert_data['collection_data']['modified_date'] = $current_date;
        
        $categoty_func = $this->contest_categoty_map[$category_id]['func'];
        //get published date , start date and end date and call categoy wise method
        list($insert_data['collection_data']['published_date'],
        $insert_data['collection_data']['scheduled_date'],
        $insert_data['collection_data']['end_date']) = $this->$categoty_func($post_data);

        $insert_data['collection_data']['stock_type'] = $this->stock_type;
        $insert_data['collection_data']['is_notified'] = 1;
        $insert_data['stocks'] = $post_data['stocks'];        
        $result = $this->Stock_model->publish_fixture($insert_data);

        if($result)
        {
            $this->api_response_arry['message'] = "Fixture published successfully";
            $this->api_response_arry['data']['collection_id'] = $result;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response();
        }
        else{
            $this->api_response_arry['global_error'] = "Problem while publish fixture";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
       
       

    }

    private function get_daily_category_dates($post)
	{
        if(!validate_date($post['fixture_date'],DATE_ONLY_FORMAT))
        {
            $this->api_response_arry['global_error'] = "Please provide a valid date";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        
		$scheduled_date = date(DATE_ONLY_FORMAT,strtotime($post['fixture_date']));
      
		$dayofweek = date('w', strtotime($scheduled_date));
       
		if(in_array($dayofweek,[0,6]))//0 =>sunday or 6=>saturday
		{
            $this->api_response_arry['global_error'] = "You can not create candle on weekend days.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
		}

        $publish_date = date('Y-m-d '.CONTEST_PUBLISH_TIME,strtotime($scheduled_date.' -1 day'));
        
        if($dayofweek ==1)// monday
        {
            $publish_date = date('Y-m-d '.CONTEST_PUBLISH_TIME,strtotime($scheduled_date.' last friday'));
        }


        if(!in_array($dayofweek,[0,6]))//0 =>sunday or 6=>saturday
        {

        $k = $this->Stock_model->check_publish_date($publish_date , $this->market_id);

        $publish_date = date('Y-m-d '.CONTEST_PUBLISH_TIME,strtotime($k));
        
        }  


        $validate_date = date(DATE_ONLY_FORMAT,strtotime($scheduled_date));

        if(!$this->Stock_model->check_holiday($validate_date, $this->market_id)) {
            $this->api_response_arry['global_error'] = "You can not create fixture for holiday";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $start_time = $validate_date.' '.$post['start_time'];
        $end_time = $validate_date.' '.$post['end_time'];

        $custom_data = !empty($this->app_config['allow_stock_predict'])?$this->app_config['allow_stock_predict']['custom_data']:array();

        $min_candle = MIN_CANDLE_MINUTES;
        $max_candle = MAX_CANDLE_MINUTES;
        if(!empty($custom_data))
        {
            $min_candle = $custom_data['min_candle_minutes'];
            $max_candle = $custom_data['max_candle_minutes']; 
        }

        $minutes = get_minutes($start_time,$end_time);

        if($minutes < $min_candle || $minutes > $max_candle)
        {
            $msg = "Candle time should be between #min# to #max# minutes";
            $msg = str_replace('#min#',$min_candle,$msg);
            $msg = str_replace('#max#',$max_candle,$msg);
            $this->api_response_arry['global_error'] =  $msg;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        } 

        $this->validate_collection_exists(1,$start_time,$end_time);

		return array($publish_date,$start_time,$end_time);
	}

    private function validate_collection_exists($category_id,$start_time,$end_time)
    {
        $collection_exits= $this->Stock_predict_model->check_fixture_exists($category_id,$start_time,$end_time);
        // print_r($collection_exits);
        // die('fd');
        if(!empty($collection_exits))
        {
            $err_msg = "This Fixture already exist";
            $this->api_response_arry['global_error'] = $err_msg;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

    }

    function get_industry_list_post()
    {
        $industry_list= $this->Stock_predict_model->get_industry_list();
        // print_r($collection_exits);
        // die('fd');
        $this->api_response_arry['data']['industry_list'] = $industry_list;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    }

    function get_master_data_post()
    {
        $caps = get_cap_types();
        $this->api_response_arry['data']['cap_types'] = $caps;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    }

    function get_candle_time_list_post()
    {
        $post_data = $this->input->post();
      
        $this->form_validation->set_rules('fixture_date', 'Fixture Date', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $times= $this->Stock_predict_model->get_candle_time_list($post_data['fixture_date']);

        // $start_times = array();
        // $end_times=array();
        // if(!empty($candle_list))
        // {
        //     $start_times = array_column($candle_list,'start_time');
        //     $end_times = array_column($candle_list,'end_time');
        // }

        $this->api_response_arry['data']['start_time'] = $times['start_time'];
        $this->api_response_arry['data']['end_time'] = $times['end_time'];
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();


    }

    function stock_closing_rate_for_time_post()
    {
        $post_data = $this->input->post();
      
        $this->form_validation->set_rules('rate_date', 'Rate Date', 'trim|required');
        $this->form_validation->set_rules('rate_time', 'Time', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $schedule_date = $post_data['rate_date'].' '.$post_data['rate_time'];
		$result = $this->Stock_predict_model->stock_closing_rate_for_time($schedule_date);

        $this->api_response_arry['data']['stock_list'] = $result;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    }

    function update_stock_rate_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('price_date', 'Price Date', 'trim|required');
        $this->form_validation->set_rules('price_time', 'Time', 'trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $current_date = format_date();
        $past_date = date('Y-m-d H:i:s',strtotime($current_date.' -7 days' ));

        $schedule_date = $post_data['price_date'].' '.$post_data['price_time'];

        if(strtotime($schedule_date) < strtotime($past_date))
        {
            $this->api_response_arry['global_error']       = "You can update update price for less then past 7 days";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $stocks = $this->input->post("stocks");
        $msg = "";
        if(empty($stocks)) {
            $msg = $this->lang->line("stock_required") ;
        } else {
            foreach ($stocks as $key => $value) {
                if(!array_key_exists('stock_id',$value) || $value['stock_id']=='' ) {
                    $msg = $this->lang->line('stock_id_rquired');
                    break;
                }               

                if(!array_key_exists('price',$value) || $value['price']=='') {
                    $msg = 'Please provide stock close price';
                    break;
                }

                if(!is_numeric($value['price'])) {
                    $msg = $this->lang->line('only_number');
                    $msg = str_replace('{field}','stock price',$msg);
                    break;
                }

                if($value['price'] <= 0) {
                    $msg = $this->lang->line('greater_than');
                    $msg = str_replace('{field}','stock price',$msg);
                    $msg = str_replace('{param}',0,$msg);
                    break;
                }
            }
        }

        if(!empty($msg)) {
			$this->api_response_arry['global_error']       = $msg;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        

        $data = array();
        $data['schedule_date'] = $schedule_date;
        $data['stocks'] = $stocks;
        $flag = $this->Stock_predict_model->update_time_price($data);

        if($flag)
        {
            $this->api_response_arry['message'] = "Price updated successfully.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response();
        }
    }

    /**
    * Update Opening and Closing Rates of selected Candle
    * @param Collection_id
    * @return string
    */

   function update_candle_opening_closing_rates_post()
   {    
        $postdata = $this->input->post();
        
        $this->form_validation->set_rules('collection_id', 'Collection ID', 'trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        
       $current_date = format_date();

       $collection_data =  $this->Stock_predict_model->get_collection_stocks($postdata);
       if(!empty($collection_data['stock_id'])){

            $this->load->helper('default_helper');
            $dt_o = new DateTime($collection_data['open_date'], new DateTimeZone('UTC'));
            $dt_o->setTimezone(new DateTimeZone('Asia/Kolkata'));

            $dt_c = new DateTime($collection_data['close_date'], new DateTimeZone('UTC'));
            $dt_c->setTimezone(new DateTimeZone('Asia/Kolkata'));
            
            $open_date  =  $dt_o->format('Y-m-d H:i:s');
            $close_date =  $dt_c->format('Y-m-d H:i:s');

            //check if candle time is 3:30

            $time =  $dt_c->format('H:i');
            if($time == '15:30'){
             $close_date =  $dt_c->format('Y-m-d 15:29:00');   
            }
            

            $collection_data['open_date']  = $open_date;
            $collection_data['close_date'] = $close_date;

            $stock_list = $this->get_stock_data(explode(',',$collection_data['stock_id']));
          
            if(!empty($stock_list))
            {
                $stocks = array_column($stock_list, 'stock_id', 'trading_symbol');        
                    $symbols = array_column($stock_list, 'trading_symbol');
                    $current_date = format_date();
                    
                    if(!empty($symbols)) {
                      $post_data =  array(
                        'exchange' => 'NSE',
                        'from_date' => $collection_data['open_date'],
                        'to_date' =>  $collection_data['close_date'],
                        'symbols' => $symbols,
                        'candle_update'=>true
                      );

                    /* $result =  $this->time_series($post_data);
                     print_r($result);die;*/
                      $this->load->model('cron/Stock_feed_model');
                      $stock_socket_enable = STOCK_DATA_SOCKET;

                     
                      if(!empty($stock_socket_enable)){
                        $history_data = $this->Stock_feed_model->stock_feed_query('stock/feed/time_series_socket', $post_data);

                        foreach($history_data as $value) {              
                              $stock_id = $stocks[$value['symbol']];
                                
                             
                              $history_data = array();
                              $history_date = $value['datetime'];  

                              $history_date = date('Y-m-d H:i:s',strtotime($history_date)); //// need to calculate it based on return date from API        
                              $history_date_utc = convert_date_to_time_zone($history_date); //// need to calculate it based on return date from API        
                               
                              if($history_date == $post_data['from_date']) {
                                $history_data['open_price'] =  $value['price'];
                              } 
                             elseif($history_date == $post_data['to_date']) {
                                $history_data['close_price'] =  $value['price'];
                             }            
                              $history_data['added_date'] = $current_date;
                              $history_data['schedule_date'] = $history_date; 
                              $history_data['schedule_date_utc'] = $history_date_utc; 
                              $history_data['stock_id'] = $stock_id;
                              $history_data['collection_id'] = $postdata['collection_id'];
                              $historical_data[] = $history_data; 
                              
                             
                             if($history_data) {
                              $this->Stock_predict_model->update_candle_price($history_data);
                             }  

                          }
                      }
                      else{
                         $history_data = $this->Stock_feed_model->stock_feed_query('stock/feed/time_series', $post_data);
                           $historical_data = array();
                          foreach($history_data as $value) {              
                              $stock_id = $stocks[$value['symbol']];
                              $meta_data = json_decode($value['meta_data'], TRUE);
                              //print_r($history);die;
                              $history_data = array();
                              $history_date = $meta_data['datetime'];    
                              $history_date = date('Y-m-d H:i:s',strtotime($history_date)); //// need to calculate it based on return date from API        
                              $history_date_utc = convert_date_to_time_zone($history_date); //// need to calculate it based on return date from API        
                                  
                              if($history_date == $post_data['from_date']) {
                                $history_data['open_price'] =  $meta_data['close'];
                              } 
                             elseif($history_date == $post_data['to_date']) {
                                $history_data['close_price'] =  $meta_data['close'];
                             }            
                              $history_data['added_date'] = $current_date;
                              $history_data['schedule_date'] = $history_date; 
                              $history_data['schedule_date_utc'] = $history_date_utc; 
                              $history_data['stock_id'] = $stock_id;
                              $history_data['collection_id'] = $postdata['collection_id'];
                              $historical_data[] = $history_data; 
                             
                             if($history_data) {
                              $this->Stock_predict_model->update_candle_price($history_data);
                             }  

                          }
                      }

                     
                    $this->api_response_arry['message'] = "Price updated successfully.";
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                     
                }
            }

       }else{ 
                $this->api_response_arry['message'] = "Price already updated.";
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
       }

                
                $this->api_response();
   }



   function get_stock_data($stock_id)
   {    $stock_list = [];

        $this->db->select('s.stock_id, s.trading_symbol');
        $this->db->from(STOCK.' s');
        $this->db->where_in('stock_id',$stock_id);
        $this->db->order_by('s.stock_id', 'ASC');
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if($query->num_rows() > 0) {
          $stock_list = $query->result_array(); 
        }
        return $stock_list;
   }
}