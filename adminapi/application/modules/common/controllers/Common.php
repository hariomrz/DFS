<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Common extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
		//Do your magic here
	}
	/**
	 * [get_sport_detail description]
	 * @Summary : get_sport_detail
	 * @return  [type]
	 */	 
	public function get_sport_detail_post()
	{
		$post_data = $this->post();

		$result = $this->Common_model->get_sport_detail($post_data);
		if(!empty($result)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['data']  		  = $result;
			$this->api_response();
		}

		$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response_arry['message']			= 'record not  found';
		$this->api_response_arry['data']			= $result;
		$this->api_response();

	}

	public function export_language_get($lang){
        // get data
       $path = IMAGE_PATH.'assets/i18n/translations/'.$lang.'.json';
		$json = file_get_contents($path);
		$lang_arr =json_decode($json,TRUE);
 
        // file name
        $filename = $lang.'.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
 
        // file creation
        $file = fopen('php://output', 'w');
 
        $header = array("Category","Label","Translation");
        fputcsv($file, $header);
 
        foreach ($lang_arr as $key => $value){

        	if(is_array($value))
			{
				foreach ($value as $key1 => $value1) {
					fputcsv($file,array($key,$key1,$value1));
				}

			}else{
				fputcsv($file,array('',$key,$value));
				
			}

        }
 
        fclose($file);
        exit;
    }

	function do_upload_master_file_post($lang='en')
	{
		$dir				= ROOT_PATH.UPLOAD_DIR;
		$subdir				= ROOT_PATH.LANGUAGE_DIR;
		$temp_file			= $_FILES['file']['tmp_name'];
		$ext				= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		
		$this->check_folder_exist($dir);
		$this->check_folder_exist($subdir);
		header( 'Content-Type: application/json; charset=utf-8' ); 
		$config['allowed_types']	= 'json';
		$config['max_size']			= '5000';
		//$config['max_width']		= '1024';
		//$config['max_height']		= '1000';
		$config['upload_path']		= $subdir;
		$config['file_name']		= $lang;

		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload('file'))
		{
			$error = $this->upload->display_errors();
			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message']       = strip_tags($error);
			$this->api_response();
		}
		else
		{
			$upload_data = $this->upload->data();

			$json_data = file_get_contents($temp_file);
			$lang_data = json_decode($json_data,TRUE);
			$filePath = LANGUAGE_FILE_UPLOAD_PATH.$lang.'.json';
			$this->upload_api_data_on_bucket($lang.'.json',$lang_data,$filePath);
			$this->api_response_arry['response_code'] = 200;
			$this->api_response_arry['data']       	  = array('file_name'=>$upload_data['raw_name']);
			$this->api_response();

		}
	}

	public function upload_api_data_on_bucket($file_name,$data_arr,$filePath){
		if( $file_name == ""){
			return false;
		}
		$json_data = json_encode($data_arr);
		$json_file_path = "/tmp/".$file_name;
		$new_json = fopen($json_file_path, "w");
		fwrite($new_json, $json_data);
		fclose($new_json);
		try{
            $data_arr = array();
            $data_arr['file_path'] = $filePath;
            $data_arr['source_path'] = $json_file_path;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_uploaded = $upload_lib->upload_file($data_arr);
            if($is_uploaded){
                return true;
            }else{
            	return false;
            }
        }catch(Exception $e){
            return false;
        }
	}

	/**
     * [get_all_group description]
     * Summary :- get all group list
     * @return [type] [description]
     */
    public function get_all_group_post()
    {
        $post   = $this->post();
        $result = $this->Common_model->get_all_group_list($post);
        
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
    }
	public function get_all_sport_post()
	{	
		$this->load->model('Common_model');
		$post_data = $this->input->post();
		$result = $this->Common_model->get_all_sport($post_data);
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $result;
		$this->api_response();
	
	}

	public function get_language_list_post()
	{
		$this->load->config('vconfig');
		$language = $this->config->item('language_list');
		//$is_logged_in = ($this->session->userdata('admin_id'))?TRUE:$this->_custom_prepare_basic_auth();
		//$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>array('language_list'=>$language, 'site_language'=>$this->session->userdata('language'), 'is_logged_in'=>$is_logged_in)) , rest_controller::HTTP_OK);
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = array('language_list'=>$language);
		$this->api_response();

	}

	private function check_folder_exist($dir)
	{	
		if(!is_dir($dir))
			return mkdir($dir, 0777);
		return TRUE;
	}

	function do_upload_lang_post($lang='en')
	{
		$file_field_name	= $this->post('name');
		$dir				= APP_ROOT_PATH.UPLOAD_DIR;
		$subdir				= APP_ROOT_PATH.LANGUAGE_DIR;
		$temp_file			= $_FILES['file']['tmp_name'];
		$ext				= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		
		$this->check_folder_exist($dir);
		$this->check_folder_exist($subdir);

		$config['allowed_types']	= 'csv|xlsx';
		$config['max_size']			= '5000';
		$config['max_width']		= '1024';
		$config['max_height']		= '1000';
		$config['upload_path']		= $subdir;
		$config['file_name']		= $lang;

		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload('file'))
		{
			$error = $this->upload->display_errors();
			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message']       = strip_tags($error);
			$this->api_response();
		}
		else
		{
			$upload_data = $this->upload->data();
			$this->csv_to_json_lang($upload_data['full_path'], $upload_data['raw_name'],$lang);
			$this->api_response_arry['response_code'] = 200;
			$this->api_response_arry['data']       	  = array('file_name'=>$upload_data['raw_name']);
			$this->api_response();

		}
	}

	private function csv_to_json_lang($file, $raw_name,$lang)
	{


		$this->load->library('CSVReader');
		$result =   $this->csvreader->parse_file($file);		
		//$result =   $csv = array_map('str_getcsv', file($file));;		
		
		$rs = $result[0];
		if(!isset($rs['Category']) OR !isset($rs['Label']) OR !isset($rs['Translation']))
		{

			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message']       = 'Invalid File';
			$this->api_response();
		}

		$json_arr = array();
		
		foreach ($result as $rs) 
		{
			if(!empty($rs['Category']))
			{
				$json_arr[$rs['Category']][$rs['Label']] = $rs['Translation'];
			}	
			else
			{
				$json_arr[$rs['Label']] = utf8_encode($rs['Transalation']);
			}
			
		}
		
		
		$dir = APP_ROOT_PATH.LANGUAGE_DIR;
		$this->load->helper('file');
		$data = json_encode($json_arr);
		 
		$json_file = $dir.$raw_name.'.json';
		 $response = write_file($json_file, $data);
		 unlink($file);

		 if( strtolower( IMAGE_SERVER ) == 'remote' )
		 {
		 	$filePath = LANGUAGE_FILE_UPLOAD_PATH.$lang.'.json';
		 	$file_name = $dir.$lang.'.json';
		 	try{
	            $data_arr = array();
	            $data_arr['file_path'] = $filePath;
	            $data_arr['source_path'] = $file_name;
	            $this->load->library('Uploadfile');
	            $upload_lib = new Uploadfile();
	            $is_uploaded = $upload_lib->upload_file($data_arr);
	            if($is_uploaded){
	                $data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
	 				$this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
	 				$this->api_response_arry['data'] = $data;
	 				$this->api_response();
	            }
	        }catch(Exception $e){
	            $result = 'Caught exception: '.  $e->getMessage(). "\n";
	 			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	 			$this->api_response_arry['global_error'] = $result;
	 			$this->api_response();
	        }
		 }

		 return $response;
	}


	public function update_site_language_post()
	{
		$response_code = rest_controller::HTTP_OK;
		$this->init_post_data();
		$this->load->config('vconfig');
		$language_list = $this->config->item('language_list');
		$language = $this->input->post('language');
		if(array_key_exists($language, $language_list))
		{
			$this->session->set_userdata('language', $language);
			$data = array('language'=>$language);
		
		}
		else
		{
			$response_code = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$data = array();
		}
		$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$data), $response_code);
	}


	public function rollback_prize_get()
	{
		exit(1);
		$contest_unique_ids = array('qEBjRrwnT','NpGx2lno0','jREwPOklM','sKbZV4MAf','axcgMXrTO','9K36m7fsz','sa60nw7oW','1tWiDMZpq');
		$this->db->select('*');
		$this->db->where_in('contest_unique_id', $contest_unique_ids);
		$this->db->from(CONTEST);
		$query = $this->db->get();
		$res1 = $query->result_array();


		$this->load->model('User_model');
		$a=0;
		foreach ($res1 as $key => $val)
		{
			$contest_unique_id = $val['contest_unique_id'];
			$contest_id = $val['contest_id'];

			$condition = array('contest_unique_id'=>$contest_unique_id, 'payment_type'=>CREDIT);
			$this->db->select('PHT.*,english_description');
			$this->db->from(PAYMENT_HISTORY_TRANSACTION.' AS PHT');
			$this->db->join(MASTERDESCRIPTION.' AS MD', 'MD.master_description_id = PHT.master_description_id');
			$this->db->where($condition);
			$query = $this->db->get();
			$result = $query->result_array();

			$a += count($result);
			if($result)
			{
				foreach ($result as $key => $value)
				{
					$this->db->select('*');
					$this->db->from(PAYMENT_HISTORY_TRANSACTION);
					$this->db->where(array('user_id'=> $value['user_id'],'contest_unique_id'=>$contest_unique_id,'master_description_id'=> '14'));
					$query = $this->db->get();
					$res = $query->row_array();

					if($res) continue;

					$this->db->select('balance');
					$this->db->from(USER);
					$this->db->where('user_id', $value['user_id']);
					$query = $this->db->get();
					$user_result = $query->row_array();
					if($user_result)
					{
						$balance = $user_result['balance'];
						$transaction_amount = $value['transaction_amount'];

						$new_balance = $balance - $transaction_amount;

						$condition = array('user_id'=>$value['user_id']);
						$data = array('balance'=>$new_balance);

						$this->db->where($condition);
						$this->db->update(USER, $data);
		
						$payment_data = array(
									'user_id'						=> $value['user_id'],
									'master_description_id'			=> '14',
									'contest_unique_id'				=> $contest_unique_id,
									'payment_type'					=> '1',
									'transaction_amount'			=> $transaction_amount,
									'user_balance_at_transaction'	=> $balance,
									'created_date'					=> format_date()
								);

						$this->db->insert(PAYMENT_HISTORY_TRANSACTION, $payment_data);

						$condition = array('user_id'=>$value['user_id'],'contest_unique_id'=>$contest_unique_id);
						$data = array('is_winner'=>'0');
						$this->db->where($condition);
						$this->db->update(LINEUP_MASTER, $data);

						$this->db->where(array('contest_id'=>$contest_id, 'user_id'=>$value['user_id']) );
						$this->db->delete(LEADERBOARD);
					}
				}

				$condition = array('contest_unique_id'=>$contest_unique_id);
				$data = array('prize_distributed'=>'0');
				$this->db->where($condition);
				$this->db->update(CONTEST, $data);
			}
		}
		debug($a);
	}

	public function sync_app_setting_fields_get(){
		
		$auth_key = $_REQUEST['auth_key'];
        if($auth_key && $auth_key == "VSPADMIN"){
			$this->load->model('Common_model');
            $this->Common_model->sync_app_setting_fields();
            echo "done";
        }else{
            echo "access denied";
        }
        exit();
	}

	/**
     * Used for get master country list
     * @param array $post_data
     * @return array
     */
	public function get_country_list_post(){
		$post_data = $this->input->post();
		$this->load->model('Common_model');
		$result = $this->Common_model->get_all_table_data('master_country_id,country_name as name',MASTER_COUNTRY,array());
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
     * Used for get master state list by country
     * @param array $post_data
     * @return array
     */
	public function get_state_list_post(){
		$this->form_validation->set_rules('master_country_id','Country ID','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
		$post_data = $this->input->post();
		$master_country_id = $post_data['master_country_id'];
		$this->load->model('Common_model');
		$result = $this->Common_model->get_all_table_data('master_state_id,name',MASTER_STATE,array('master_country_id'=> $master_country_id));
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
     * Used for save banned state
     * @param array $post_data
     * @return array
     */
	public function save_banned_state_post(){
		$this->form_validation->set_rules('master_state_id','State ID','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
		$post_data = $this->input->post();
		$master_state_id = $post_data['master_state_id'];
		$this->load->model('Common_model');
		$check_exist = $this->Common_model->get_single_row('*',BANNED_STATE,array('master_state_id' => $master_state_id));
		if(!empty($check_exist)){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']	= $this->lang->line('banned_state_already_added');
			$this->api_response();
		}

		//for save record
		$data_arr = array('master_state_id' => $master_state_id,'date_added' => format_date());
		$result = $this->Common_model->save_banned_state($data_arr);
		if($result){
			$this->delete_cache_data("banned_state");

			$this->api_response_arry['message'] = $this->lang->line('banned_state_save_success');
			$this->api_response();
		}else{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('banned_save_state_error');
			$this->api_response();
		}
	}

	/**
     * Used for remove banned state from list
     * @param array $post_data
     * @return array
     */
	public function remove_banned_state_post(){
		$this->form_validation->set_rules('master_state_id','State ID','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
		$post_data = $this->input->post();
		$master_state_id = $post_data['master_state_id'];
		$this->load->model('Common_model');
		$result = $this->Common_model->delete_banned_state($master_state_id);
		if($result){
			$this->delete_cache_data("banned_state");

			$this->api_response_arry['message'] = $this->lang->line('banned_state_remove_success');
			$this->api_response();
		}else{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('banned_save_state_error');
			$this->api_response();
		}
	}

	/**
     * Used for get banned state list
     * @param array $post_data
     * @return array
     */
	public function get_banned_state_list_post(){

		$post_data = $this->input->post();
		$this->load->model('Common_model');
		$result = $this->Common_model->get_banned_state_list($post_data);

		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}
	/**
     * Used for upload media file
     * @param array $post_data
     * @return json array
     */
	public function do_upload_post() 
	{
		$post_data = $this->input->post();
		$type = isset($post_data['type']) ? $post_data['type'] : "";
		$keep_name = isset($post_data['keep_name']) ? $post_data['keep_name'] : "0";
		$media_config = get_media_setting($type);
		if(empty($media_config)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid media config.";
            $this->api_response();
		}

		$field_name	= 'file_name';
		$dir = ROOT_PATH.$media_config['path']."/";
		$s3_dir = $media_config['path']."/";
		$temp_file = $_FILES[$field_name]['tmp_name'];
		$size = number_format(($_FILES[$field_name]['size'] / 1000000),"2",".","");
		$ext = pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);
		$width = $height = "";
		if($ext != "pdf"){
			$vals = @getimagesize($temp_file);
			$width = $vals[0];
			$height = $vals[1];
		}
		//media type validation
		if(!in_array($ext,$media_config['type'])){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{media_type}",implode(",",$media_config['type']),$this->lang->line('image_invalid_ext'));
            $this->api_response();
		}

		//media size
		if($height > $media_config['max_h'] || $width > $media_config['max_w']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{max_width}x{max_height}",$media_config['max_w']."x".$media_config['max_h'],$this->lang->line('image_invalid_dim'));
            $this->api_response();
		}

		if($height < $media_config['min_h'] || $width < $media_config['min_w']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{max_width}x{max_height}",$media_config['max_w']."x".$media_config['max_h'],$this->lang->line('image_invalid_dim'));
            $this->api_response();
		}

		if($size > $media_config['size']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{size}",$media_config['size'],$this->lang->line('image_invalid_size_error'));
            $this->api_response();
		}
		
		if($keep_name == 1){
			$org_file_name = pathinfo($_FILES[$field_name]['name'], PATHINFO_FILENAME);
			$file_name = str_replace(" ","_",$org_file_name);
			$file_name = $file_name."_".time().".".$ext;
		}else{
			$file_name = time().".".$ext;
		}
		$filePath = $s3_dir.$file_name;
		try{
            $data_arr = array();
            $data_arr['file_path'] = $filePath;
            $data_arr['source_path'] = $temp_file;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_uploaded = $upload_lib->upload_file($data_arr);
            if($is_uploaded){
	            $this->api_response_arry['data'] = array('image_url'=>  IMAGE_PATH.$filePath,'image_name'=> $file_name);
            $this->api_response_arry['message'] = "Upload Successfully.";
	            $this->api_response();
            }
        }catch(Exception $e){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('image_file_upload_error');
            $this->api_response();
        }		
	}

	/**
     * Used for remove media file from bucket
     * @param array $post_data
     * @return json array
     */
	public function remove_media_post()
	{
		$post_data = $this->input->post();
		$type = isset($post_data['type']) ? $post_data['type'] : "";
		$media_config = get_media_setting($type);
		if(empty($media_config)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid media config.";
            $this->api_response();
		}

		$file_name	= $post_data['file_name'];
		$file_path = $media_config['path']."/".$file_name;
		try{
            $data_arr = array();
            $data_arr['file_path'] = $file_path;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_deleted = $upload_lib->delete_file($data_arr);
            if($is_deleted){
	            $this->api_response_arry['message'] = $this->lang->line('media_removed');
	            $this->api_response();
            }
        }catch(Exception $e){
        	$error_msg = 'Caught exception: '.  $e->getMessage(). "\n";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $error_msg;
            $this->api_response();
        }
	}
}

/* End of file common.php */
/* Location: ./application/controllers/common.php */