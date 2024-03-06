<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends Common_Api_Controller {
    public $market_id = 1;
	public function __construct() {
		parent::__construct();
		$_POST = $this->post();
		$this->load->model('admin/Stock_model');

		$this->contest_categoty_map= array();
		$this->contest_categoty_map[1] = array('func' => 'get_daily_category_dates');
		$this->contest_categoty_map[2] = array('func' => 'get_weekly_category_dates');
		$this->contest_categoty_map[3] = array('func' => 'get_montly_category_dates');
	}

    function auto_suggestion_list_post() {        
        $this->form_validation->set_rules('keyword', $this->lang->line('search_keyword'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post = $this->input->post();
        $result = $this->Stock_model->auto_suggestion_list($post['keyword']);
        $this->api_response_arry['data'] = $result;
        $this->api_response();			
    }

    /**
     * Function used for save stock in nifty 50's list
     * @param $post_data
     * @return json array
     */
	public function save_post() {
		$this->form_validation->set_rules('master_stock_id', $this->lang->line('stock_id'), 'trim|required');
		//$this->form_validation->set_rules('lot_size', $this->lang->line('lot_size'), 'trim|required|integer|greater_than[0]|less_than[100000]');
		$this->form_validation->set_rules('logo', $this->lang->line('logo'), 'trim');
		$this->form_validation->set_rules('display_name', $this->lang->line('display_name'), 'trim|required|min_length[3]|max_length[50]');
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();

        $stock_type = 1;
        if(!empty($post_data['stock_type']))
        {
            $stock_type =  $post_data['stock_type'];
        }

        $cap_type = 0;
        if(!empty($post_data['cap_type']))
        {
            $cap_type =  $post_data['cap_type'];
        }

        $industry_id = NULL;
        if(!empty($post_data['industry_id']))
        {
            $industry_id =  $post_data['industry_id'];
        }
        $stock_count = $this->Stock_model->get_single_row('count(stock_id) as cnt',STOCK,array('status' => 1));
        $stock_limit = 50;
        $stock_type_data = $this->Stock_model->get_single_row('stock_limit',STOCK_TYPE,array('market_id' => 1, 'status' => 1,'type' => $stock_type));

        if(!empty($stock_type_data['stock_limit']))
        {
            $stock_limit = $stock_type_data['stock_limit'];
        }
        if($stock_count['cnt'] >= $stock_limit) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('max_stock_limit');
            $this->api_response();
        }
        $row = $this->Stock_model->get_single_row('exchange_token, instrument_token, name, trading_symbol',MASTER_STOCK,array('master_stock_id' => $post_data['master_stock_id']));

        if(empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('invalid_stock');
            $this->api_response();
        }

        $stock = $this->Stock_model->get_single_row('stock_id, status',STOCK,array('trading_symbol' => $row['trading_symbol']));
        $current_date = format_date();
        $status = 1;
        $insert_data = array(
			//"lot_size"  => $post_data['lot_size'],
			"logo"      => $post_data['logo'],
			"display_name"      => trim($post_data['display_name']),
            "status"        => $status,
			"modified_date" => $current_date,
            "cap_type" => $cap_type,
            "industry_id" => $industry_id
		);
        if($stock) {
            $stock_id = $stock['stock_id'];
            if($stock['status'] == 1) {
                $status = 0;
            }
            $this->Stock_model->update_stock($insert_data, $stock_id);
        } else {
            $insert_data['name'] = $row['name'];
            $insert_data['exchange_token'] = $row['exchange_token'];
            $insert_data['instrument_token'] = $row['instrument_token'];
            $insert_data['trading_symbol'] = $row['trading_symbol'];
            $insert_data['added_date'] = $current_date;
            $insert_data['market_id'] = $this->market_id;
            $insert_data['cap_type'] = $cap_type;
            $insert_data['industry_id'] = $industry_id;
            $stock_id = $this->Stock_model->save($insert_data);           
        }

        if($status == 1) {
            $this->update_history($stock_id);

            $current_content = array(
                "notification_type"             => 563,
                "master_stock_id"			    => $post_data['master_stock_id'],
                "stock_name"                    => $row['name'],
                "stock_type"                    => $stock_type,
            );
            $this->load->helper('queue_helper');
            add_data_in_queue($current_content,'stock_push');
        }
        $nfstock_cache_key = "st_nfstock";
        $this->delete_cache_data($nfstock_cache_key);
		$this->api_response_arry['message'] = $this->lang->line('stock_added_success');
        $this->api_response_arry['data'] = array();
        $this->api_response();
	}

    function update_history($stock_id) {
        $this->load->helper('queue');

        add_data_in_queue(array('action' => 'update_stock_latest_quote', 'stock_id' => $stock_id),'stock_feed');

        add_data_in_queue(array('action' => 'stock_historical_data_day_wise', 'stock_id' => $stock_id),'stock_feed');

        $from_date = date('Y-m-d 09:14:00',strtotime(format_date().' 1 week ago'));
        $to_date = format_date('today', 'Y-m-d 15:40:00');
        add_data_in_queue(array('action' => 'stock_historical_data_minute_wise', 'stock_id' => $stock_id, 'from_date' => $from_date, 'to_date' => $to_date),'stock_feed'); 
    }

    /**
     * Function used for update stock
     * @param $post_data
     * @return json array
     */
	public function update_post() {
		$this->form_validation->set_rules('stock_id', $this->lang->line('stock_id'), 'trim|required');
		//$this->form_validation->set_rules('lot_size', $this->lang->line('lot_size'), 'trim|required|integer|greater_than[0]|less_than[100000]');
		$this->form_validation->set_rules('logo', $this->lang->line('logo'), 'trim');
		$this->form_validation->set_rules('display_name', $this->lang->line('display_name'), 'trim|required|min_length[3]|max_length[50]');
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();

        $stock = $this->Stock_model->get_single_row('stock_id',STOCK,array('stock_id' => $post_data['stock_id'], 'status' => 1));
        if(empty($stock)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('invalid_stock');
            $this->api_response();
        }
		$current_date = format_date();
        $update_data = array(
			//"lot_size"  => $post_data['lot_size'],
			"logo"      => $post_data['logo'],
			"display_name"      => trim($post_data['display_name']),
            "status"        => 1,
			"modified_date" => $current_date
		);	

        if(!empty($post_data['cap_type']))
        {
            $update_data['cap_type'] = $post_data['cap_type'];
        }

        if(!empty($post_data['industry_id']))
        {
            $update_data['industry_id'] = $post_data['industry_id'];
        }

        
        $this->Stock_model->update_stock($update_data, $stock['stock_id']);

        $nfstock_cache_key = "st_nfstock";
        $this->delete_cache_data($nfstock_cache_key);

        $current_content = array(
            "notification_type"             => 564, // notification on publishing the fixture
            "display_name"                  => $post_data['display_name'],
            //"lot_size"                      => $post_data['lot_size'],
            "master_stock_id"			    => $post_data['master_stock_id'],
            "stock_id"						=> $post_data['stock_id'],
        );
        $this->load->helper('queue_helper');
        add_data_in_queue($current_content,'stock_push');
        
		$this->api_response_arry['message'] = $this->lang->line('stock_update_success');
        $this->api_response_arry['data'] = array();
        $this->api_response();
		
	}

	/**
     * Function used for stock list
     * @param $post_data
     * @return json array
     */
	public function list_post(){
		$result = $this->Stock_model->get_list();

        $stock_type = 1;
        if(!empty($this->input->post('stock_type')))
        {
            $stock_type = $this->input->post('stock_type');
        }
        $stock_type_data = $this->Stock_model->get_single_row('stock_limit',STOCK_TYPE,array('market_id' => 1, 'status' => 1, 'type' => $stock_type));
		$this->api_response_arry['data']['stock_list'] = $result['result'];
        $this->api_response_arry['data']['total'] = $result['total'];
        $this->api_response_arry['data']['stock_limit'] = 0;
        if(isset($stock_type_data['stock_limit']))
        {
            $this->api_response_arry['data']['stock_limit'] = $stock_type_data['stock_limit'];
        }
		$this->api_response();
	}

	/**
     * Function used for upload stock icon
     * @param $post_data
     * @return json array
     */
	public function upload_stock_logo_post() {
		$post_data = $this->input->post();
		$file_field_name = 'userfile';
		if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
        }
		$dir = ROOT_PATH.STOCK_IMAGE_DIR;
		$s3_dir = STOCK_IMAGE_DIR;
		$temp_file	= $_FILES['userfile']['tmp_name'];
		$ext = pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals = @getimagesize($temp_file);
		$width = $vals[0];
		$height = $vals[1];

        if ($height > '200' || $width > '200'){
			$invalid_size = str_replace("{max_height}",'200',$this->lang->line('invalid_image_dimension'));
			$invalid_size = str_replace("{max_width}",'200',$invalid_size);
			$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry["message"] = $invalid_size;
			$this->api_response();
		}
		
		if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > '1048576') {
            $invalid_size = str_replace("{size}",'1MB',$this->lang->line('invalid_image_size'));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $invalid_size);
            $this->api_response();
        }

        $file_name = time() . "." . $ext;
        $allowed_ext = array('jpg', 'jpeg', 'png');
        if (!in_array(strtolower($ext), $allowed_ext)) {
            $error_msg = sprintf($this->lang->line('invalid_image_ext'), implode(', ', $allowed_ext));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $error_msg);
            $this->api_response();
        }

		if( strtolower( IMAGE_SERVER ) == 'local') {
			$this->check_folder_exist($dir);
		}
		
		$file_name = time() . "." . $ext;
		$filePath     = $s3_dir.$file_name;
		//Start amazon server upload code
		if (strtolower(IMAGE_SERVER) == 'remote') {
			try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $image_path = IMAGE_PATH.$filePath;
					$return_array = array('image_url' => $image_path, 'file_name' => $file_name);
					$this->api_response_arry['message'] = $this->lang->line('icon_upload_success');
					$this->api_response_arry['data'] = $return_array;
					$this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response(); 
            }
		} else {
			$config['allowed_types'] = 'jpg|png|jpeg|gif';
			$config['max_size'] = '1024'; //204800
			$config['max_width'] = '200';
			$config['max_height'] = '200';
			$config['upload_path'] = $dir;
			$config['file_name'] = $file_name;
			$this->load->library('upload', $config);
			if (!$this->upload->do_upload($file_field_name)) {
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = strip_tags($this->upload->display_errors());
				$this->api_response();
			} else {
				$uploaded_data = $this->upload->data();
				$image_path =  STOCK_IMAGE_DIR . $uploaded_data['file_name'];
				$data = array(
							'image_url'=> $image_path,
							'file_name'=> $uploaded_data['file_name']
						);
				$this->api_response_arry['message'] = $this->lang->line('icon_upload_success');
				$this->api_response_arry['data'] = $data;
				$this->api_response();
			}
		}		
	}

	/**
     * Function used for remove stock logo
     * @param $post_data
     * @return json array
     */
	public function remove_stock_logo_post() {
		$this->form_validation->set_rules('stock_id', $this->lang->line('stock_id'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$icon_name = $this->Stock_model->get_single_row ('logo', STOCK, array("stock_id"=>$post_data['stock_id']));
		$image_name = $icon_name['logo'];
		$dir = ROOT_PATH.STOCK_IMAGE_DIR;
		$s3_dir = STOCK_IMAGE_DIR;
		$dir_path = $s3_dir.$image_name;
		if( strtolower( IMAGE_SERVER ) == 'remote' ) {
			try{
                $data_arr = array();
                $data_arr['file_path'] = $dir_path;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_deleted = $upload_lib->delete_file($data_arr);
                if(!$is_deleted){
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['global_error'] = $this->lang->line('image_removed_error');
					$this->api_response();
                }
                
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['global_error'] = $this->lang->line('image_removed_error');
				$this->api_response();
            }
		}
		@unlink($dir.$image_name);
        $this->Stock_model->update(STOCK, array('logo'=>NULL), array('stock_id'=>$post_data['stock_id']));
		$this->api_response_arry['message'] = $this->lang->line('image_removed');
		$this->api_response();
	}


    /**
     * Function used to get lot size list
     * @param $post_data
     * @return json array
     */
	public function get_lot_size_list_post(){
		$result = $this->Stock_model->get_lot_size_list();
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}
	

	/**
     * Function used for update stock status
     * @param $post_data
     * @return json array
     */
 	public function delete_post(){
		$this->form_validation->set_rules('stock_id', $this->lang->line('stock_id'), 'trim|required');
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();

		$stock = $this->Stock_model->get_single_row('stock_id',STOCK,array('stock_id' => $post_data['stock_id'], 'status' => 1));
        if(empty($stock)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('invalid_stock');
            $this->api_response();
        }

		$current_date = format_date();
        $update_data = array(
            "status"        => 0,
			"modified_date" => $current_date
		);	

        $this->Stock_model->update_stock($update_data, $stock['stock_id']);
		$this->api_response_arry['message'] = $this->lang->line('delete_stock_success');
        $this->api_response_arry['data'] = array();
        $this->api_response();
	}

	 /**
    * Function used for get stock list to publish collection
	* @method get_stocks_to_publish
    * @param int $sports_id
    * @param int $league_id
    * @param string $season_game_uid
    * @return array
    */
    public function get_stocks_to_publish_post()
    {
		$this->form_validation->set_rules('collection_id', 'Collection ID', 'trim');
           
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

		$post_param = $this->input->post();
		$collection_data = array();
        $collection_id = isset($post_param['collection_id']) ? $post_param['collection_id'] : 0;
		if(!empty($collection_id)) {
			$collection_data = $this->Stock_model->get_collection_by_id($collection_id);
		}
       
        $collection_data['stocks'] = $this->Stock_model->get_all_stocks($collection_id);
        $this->api_response_arry['data'] = $collection_data;
        $this->api_response();
    }


    private function validate_collection_exists($category_id,$scheduled_date)
    {
        $collection_exits= $this->Stock_model->check_fixture_exists($category_id,$scheduled_date);
        // print_r($collection_exits);
        // die('fd');
        if(!empty($collection_exits))
        {
            $err_msg = "This Fixture already exists for ".$collection_exits['name'];
            $this->api_response_arry['global_error'] = $err_msg;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

    }

	private function get_daily_category_dates($post)
	{    
       
        if(!validate_date($post['value'],DATE_ONLY_FORMAT))
        {
            $this->api_response_arry['global_error'] = "Please provide a valid date";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        
		$scheduled_date = date(DATE_ONLY_FORMAT,strtotime($post['value']));
 
      
		$dayofweek = date('w', strtotime($scheduled_date));
  
       
		if(in_array($dayofweek,[0,6]))//0 =>sunday or 6=>saturday
		{
			$scheduled_date = date('Y-m-d '.CONTEST_START_TIME,strtotime($scheduled_date.' next monday'));


			$end_date = date('Y-m-d '.CONTEST_END_TIME,strtotime($scheduled_date));


		}
		else{
			$scheduled_date = date('Y-m-d '.CONTEST_START_TIME,strtotime($scheduled_date));
			$end_date = date('Y-m-d '.CONTEST_END_TIME,strtotime($scheduled_date));
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

        $this->validate_collection_exists($post['category_id'],$validate_date);

       
		return array($publish_date,$scheduled_date,$end_date);
	}

    
	private function get_weekly_category_dates($post)
	{
        $week = $post['value'];

        if (!ctype_digit((string)$week) || $week < 1 || $week > 52) 
        {
            $this->api_response_arry['global_error'] = "Please provide a valid week number";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $year = format_date('today','Y');
        
        $scheduled_date= get_week_start_date($week,$year);
		$scheduled_date = date(DATE_ONLY_FORMAT,strtotime($scheduled_date));

        $scheduled_date = $this->Stock_model->check_holiday_recursive($scheduled_date, $this->market_id);
		$scheduled_date = date('Y-m-d '.CONTEST_START_TIME,strtotime($scheduled_date));

        $end_date = date('Y-m-d',strtotime($scheduled_date.' next friday'));
        $end_date = $this->Stock_model->check_holiday_recursive($end_date, $this->market_id, '-1 day');
        $end_date = date('Y-m-d '.CONTEST_END_TIME,strtotime($end_date));


        $publish_date = date('Y-m-d '.CONTEST_PUBLISH_TIME,strtotime($scheduled_date.' -1 day'));
        $dayofweek = date('w', strtotime($scheduled_date));
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
        $this->validate_collection_exists($post['category_id'],$validate_date);
		return array($publish_date,$scheduled_date,$end_date);
		
	}

	private function get_montly_category_dates($post)
	{
		$month = $post['value'];
        if (!ctype_digit((string)$month) || $month < 1 || $month > 12) 
        {
            $this->api_response_arry['global_error'] = "Please provide a valid month number";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
		$scheduled_date = date("Y-$month-1 ".CONTEST_START_TIME,strtotime(' next month'));
		$dayofweek = date('w', strtotime($scheduled_date));
		if(in_array($dayofweek,[0,6]))//0 =>sunday, 6=>saturday
		{
			$scheduled_date = date('Y-m-d '.CONTEST_START_TIME,strtotime($scheduled_date.' next monday'));
		}

        $scheduled_date = date(DATE_ONLY_FORMAT,strtotime($scheduled_date));
        $scheduled_date = $this->Stock_model->check_holiday_recursive($scheduled_date, $this->market_id);
		$scheduled_date = date('Y-m-d '.CONTEST_START_TIME,strtotime($scheduled_date));

         // Last date of current month.
        $end_date = date("Y-m-t ".CONTEST_END_TIME, strtotime($scheduled_date) );
        $lastdayofweek = date('w', strtotime($end_date));
        if(in_array($lastdayofweek,[0,6]))//0 =>sunday, 6=>saturday
        {
            $end_date = date('Y-m-d '.CONTEST_END_TIME,strtotime($end_date.' last friday'));
        }
       
        $end_date = date('Y-m-d '.CONTEST_END_TIME,strtotime($scheduled_date.' last friday'));

        $end_date = date(DATE_ONLY_FORMAT,strtotime($end_date));
        $end_date = $this->Stock_model->check_holiday_recursive($end_date, $this->market_id, '-1 day');
        $end_date = date('Y-m-d '.CONTEST_END_TIME,strtotime($end_date));

         
        $publish_date = date('Y-m-d '.CONTEST_PUBLISH_TIME,strtotime($scheduled_date.' -1 day'));
        $dayofweek = date('w', strtotime($scheduled_date));
        if($dayofweek ==1) // monday
        {
            $publish_date = date('Y-m-d '.CONTEST_PUBLISH_TIME,strtotime($scheduled_date.' last friday'));
        }

        if(!in_array($dayofweek,[0,6]))//0 =>sunday or 6=>saturday
            {

            $k = $this->Stock_model->check_publish_date($publish_date , $this->market_id);

            $publish_date = date('Y-m-d '.CONTEST_PUBLISH_TIME,strtotime($k));            
        }

        $validate_date = date(DATE_ONLY_FORMAT,strtotime($scheduled_date));
        $this->validate_collection_exists($post['category_id'],$validate_date);
		return array($publish_date,$scheduled_date,$end_date);


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
      
        $this->form_validation->set_rules('category_id', 'Category ID', 'trim|required');
        $this->form_validation->set_rules('value', 'Value', 'trim|required');
        $this->form_validation->set_rules('name', 'name', 'trim|min_length[3]|max_length[20]');
		
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        
        $category_id = $post_data['category_id'];
        if(!isset($this->contest_categoty_map[$category_id]))
        {
            $this->api_response_arry['message'] = "Pleaser enter a valid category ID";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

		$this->validate_stock_details($post_data);

       
        
        $collection_stock_arr = array();
        $current_date = format_date();

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

        $stock_type = $this->input->post('stock_type');
        $insert_data['collection_data']['stock_type'] = !empty($stock_type)?$stock_type:1;
        $insert_data['stocks'] = $post_data['stocks'];    

        $result = $this->Stock_model->publish_fixture($insert_data);

        if($result)
        {
            $this->api_response_arry['message'] = "Fixuture published successfully";
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

    public function update_fixture_stocks_post() {
        $post_data = $this->input->post();  

        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        		
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $this->validate_stock_details($post_data);

        $this->Stock_model->update_fixture_stocks($post_data);

        $del_cache_key = 'st_collection_player_'.$post_data['collection_id'];
        $this->delete_cache_data($del_cache_key);

        $this->api_response_arry['message'] = $this->lang->line("fixture_stock_update_success");
        $this->api_response();
    }

    public function validate_fixture_post()
    {
        $post_data = $this->input->post();
      
        $this->form_validation->set_rules('category_id', 'Category ID', 'trim|required');
        $this->form_validation->set_rules('value', 'Value', 'trim|required');

        $category_id = $post_data['category_id'];   
        if(!isset($this->contest_categoty_map[$category_id]))
        {
            $this->api_response_arry['message'] = "Pleaser enter a valid category ID";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $categoty_func = $this->contest_categoty_map[$category_id]['func'];
        //get published date , start date and end date and call categoy wise method
        list($published_date,
        $scheduled_date,
        $end_date) = $this->$categoty_func($post_data);

        $stock_type = $this->input->post('stock_type');
        if(empty($stock_type))
        {
            $stock_type = 1;
        }
        $row = $this->Stock_model->get_single_row('collection_id',COLLECTION,array('category_id' => $category_id,'scheduled_date' => $scheduled_date,"stock_type" => $stock_type));

        if(!empty($row))
        {
            $this->api_response_arry['message'] = "Fixrure already exists.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $this->api_response_arry['message'] = "Valid fixture to create";
        $this->api_response_arry['data']['allow_new_fixture'] = 1;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
      
        $this->api_response();
    }


    /**
     * Function used for stock list
     * @param $post_data
     * @return json array
     */
	public function stock_list_with_close_price_post(){
        $post_data = $this->input->post();
        $schedule_date = format_date();
        $end_date = date('Y-m-d '.CONTEST_END_TIME,strtotime($schedule_date));
        $end_date = date('Y-m-d H:i:s',strtotime($end_date.' +10 minutes'));

        $current_time = strtotime($schedule_date);
        $end_time = strtotime($end_date);        

        $schedule_date = format_date('today', 'Y-m-d');
     
        $sbtn = 0;

        if(isset($post_data['date']) && $post_data['date'] != ""){

            $input_date = format_date($post_data['date'], 'Y-m-d');

            if($schedule_date  > $input_date) {

                $sbtn = 1;
            }
            else{

                $sbtn = 0;
            }  
        } 
       
        if($current_time > $end_time) {
            $sbtn = 1;
        }

        if(isset($post_data['date']) && $post_data['date'] != ""){
            $schedule_date = date("Y-m-d",strtotime($post_data['date']));
        }
		$result = $this->Stock_model->stock_list_with_close_price($schedule_date);
        $prize_status = array_column($result['result'], "status");
        
        if(in_array(1, $prize_status) || in_array(2, $prize_status)) {
            $sbtn = 0;
        }
        $this->api_response_arry['data']['sbtn'] = $sbtn;
		$this->api_response_arry['data']['stock_list'] = $result['result'];
        $this->api_response_arry['data']['total'] = $result['total'];
		$this->api_response();
	}
 
    /**
     * Update stocks today close price and close today match 
     */
    public function update_close_price_post() {

        $post_data = $this->input->post();

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

                if(!array_key_exists('close_price',$value) || $value['close_price']=='') {
                    $msg = 'Please provide stock close price';
                    break;
                }

                if(!is_numeric($value['close_price'])) {
                    $msg = sprintf($this->lang->line('only_number'), 'stock close price');
                    break;
                }

                if($value['close_price'] < 1) {
                    $msg = sprintf($this->lang->line('greater_than'), 'stock close price', 0);
                    break;
                }
            }
        }

        if(!empty($msg)) {
			$this->api_response_arry['message']       = $msg;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }      
        $schedule_date = format_date();
        $end_date = date('Y-m-d '.CONTEST_END_TIME,strtotime($schedule_date));
        $end_date = date('Y-m-d H:i:s',strtotime($end_date.' +10 minutes'));

        $current_time = strtotime($schedule_date);

        $end_time = strtotime($end_date);

        $schedule_date = format_date('today', 'Y-m-d');

        $sbtn = 0;

        if(isset($post_data['date']) && $post_data['date'] != ""){

            $input_date = format_date($post_data['date'], 'Y-m-d');

            if($schedule_date  > $input_date) {

                $sbtn = 1;
            }
            else{

                $sbtn = 0;
            }  
        } 

        if($current_time > $end_time) {
            $sbtn = 1;
        }   


        if(isset($post_data['date']) && $post_data['date'] != ""){
            $schedule_date = date("Y-m-d",strtotime($post_data['date']));
        }

        if(isset($post_data['type']) && $post_data['type'] != ""){
            $type = $post_data['type'];
        }

        $stock_count = $this->Stock_model->get_single_row('stock_id',STOCK_HISTORY,array('status > ' => 0, 'schedule_date' => $schedule_date));

        if(!empty($stock_count)) {
            $sbtn = 0;
        }


        if($sbtn == 1) {     

            $data = array();
            $data['schedule_date'] = $schedule_date;
            $data['stocks'] = $stocks;          
            $flag = $this->Stock_model->update_close_price($data);
            if($flag) {
                $collections = $this->Stock_model->get_collection_for_point_manual_update($schedule_date,$type);
                if(!empty($collections)) {
                    $this->load->helper('queue_helper');
                    $content = array();
                    $content['action'] = 'calculate_score_status';
                    $content['collections'] = $collections;
                    add_data_in_queue($content,'stock_calculate_score');    
                }
                $this->api_response_arry['message']       = "Match closed successfully.";
            }
            
        } else {
            $this->api_response_arry['message']       = "Today close price already updated.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->api_response();
    }

    /**
     * Update stocks close price status and close today match 
     */
    public function update_price_status_post() {    

        $post_data = $this->input->post(); 

        $stocks = $this->input->post("stocks");
        $schedule_date = format_date();
        $end_date = date('Y-m-d '.CONTEST_END_TIME,strtotime($schedule_date));
        $end_date = date('Y-m-d H:i:s',strtotime($end_date.' +10 minutes'));

        $current_time = strtotime($schedule_date);
        $end_time = strtotime($end_date);
        $schedule_date = format_date('today', 'Y-m-d');
        $sbtn = 0;

        if(isset($post_data['date']) && $post_data['date'] != ""){

            $input_date = format_date($post_data['date'], 'Y-m-d');

            if($schedule_date  > $input_date) {               

                $sbtn = 1;
            }
            else{              

                $sbtn = 0;
            }  
        } 

        if($current_time > $end_time) {
            $sbtn = 1;
        } 

   
        if(isset($post_data['date']) && $post_data['date'] != ""){
            $schedule_date = date("Y-m-d",strtotime($post_data['date']));
        }

        if(isset($post_data['type']) && $post_data['type'] != ""){
            $type = $post_data['type'];
        }


        $stock_count = $this->Stock_model->get_single_row('stock_id',STOCK_HISTORY,array('status > ' => 0, 'schedule_date' => $schedule_date));      


        if(!empty($stock_count)) {
            $sbtn = 0;
        }

        if($sbtn == 1) {
            $data = array();
            $data['schedule_date'] = $schedule_date;
            $flag = $this->Stock_model->update_price_status($data);
            if($flag) {
                $collections = $this->Stock_model->get_collection_for_point_manual_update($schedule_date,$type);             
                if(!empty($collections)) {
                    $this->load->helper('queue_helper');
                    $content = array();
                    $content['action'] = 'calculate_score_status';
                    $content['collections'] = $collections;
                   add_data_in_queue($content,'stock_calculate_score');    
                }
                $this->api_response_arry['message']       = "Match closed successfully.";
            }
        } else {
            $this->api_response_arry['message']       = "Today match already closed.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->api_response();
    }

    /**
     * Used to get holiday
     */
    function get_holiday_post() {
        $post_data = $this->input->post();
        $year = isset($post_data['year']) ? $post_data['year'] : format_date('today', 'Y');
        
        $result = $this->Stock_model->get_holiday($this->market_id, $year);
        $holiday_list = array();
        if(!empty($result)) {
            $holiday_list = array_column($result, 'holiday_date');
        }        
        $this->api_response_arry['data'] = $holiday_list;
        $this->api_response();			
    }
}