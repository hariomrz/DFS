<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();

		$_POST = $this->input->post();
		$this->load->model('Setting_model');
		//Do your magic here
        $this->admin_lang = $this->lang->line('setting');
        $this->admin_roles_manage($this->admin_id,'settings');
	}

	public function flush_cache_post($return_only=FALSE)
	{
		$this->flush_cache_data();

		//for delete s3 bucket file
		$this->deleteS3BucketFile("app_version.json");
		$this->deleteS3BucketFile("app_master_data.json");
		$this->deleteS3BucketFile("lobby_banner_list_7.json");//cricket
		$this->deleteS3BucketFile("lobby_banner_list_5.json");//soccer
		$this->deleteS3BucketFile("lobby_banner_list_8.json");//kabaddi
		
		if($return_only)return $return_only;
		$this->api_response_arry['response_code'] = 200;
		$this->api_response_arry['message']  		= "Cache flushed successfully.";
		$this->api_response();
	}

	public function change_password_post()
	{
		$this->admin_roles_manage($this->admin_id,'change_password');
		$this->form_validation->set_rules('old_password', 'Old Password', 'trim|required');
		$this->form_validation->set_rules('new_password', 'New Password', 'trim|required|matches[confirm_password]|min_length[5]');
		$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|min_length[5]');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data_arr = $this->input->post();

		$result = $this->Setting_model->chnage_password($data_arr);
		if($result)
		{
			//Delete all active admin login
			// commented billow function becouse when admin will change password then other users should not be impected or logged out
			//$this->Setting_model->delete_all_active_login_admin();
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['message']  		= $this->admin_lang['change_password_success'];
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->admin_lang['change_password_error'];
			$this->api_response();
		}
	}

	public function change_date_time_post()
	{
		$date = '';
		$this->load->helper( 'file' );
		if ($this->input->post("date"))
		{
			$date = date("Y-m-d H:i:s", strtotime($this->input->post("date")));
		}
		$date_time = '';

		if ( $date )
			$date_time = $date;

		// switch (ENVIRONMENT) {
		// 	case 'development':
		// 		$path = ROOT_PATH.'date_time.php';
		// 		break;
		// 	case 'testing':
		// 	case 'production':
		// 	$path = ROOT_PATH.'date_time.php';
		// 		# code...
		// 		break;
		// 	default:
		// 	$path = ROOT_PATH.'../date_time.php';
				
		// 		break;
		// }

		$path = ROOT_PATH.'date_time.php';
		if(!file_exists($path))
			$path = ROOT_PATH.'../date_time.php';
			
		
		$data = '<?php $date_time = "'.$date_time.'";';

		$this->flush_cache_post(TRUE);

		write_file($path, $data);

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['message']  	= "Update date time successfully";
		$this->api_response();
	}
	/**
	 * [update_referral_amount_post description]
	 * Summary :- update referral_fund data 
	 * @return [type] [description]
	 */
	public function update_referral_amount_post()
	{	
		$this->form_validation->set_rules('invest_money', 'Invest money', 'trim|required|max_length[5]');
		$this->form_validation->set_rules('referral_amount', 'Referral amount', 'trim|required|max_length[5]');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data_arr =  $this->input->post();
		$result = $this->Setting_model->update_referral_amount($data_arr);
		if($result)
		{
			
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['message']  	= $this->admin_lang['rf_amount_updated_success'];
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->admin_lang['no_change'];
			$this->api_response();
		}
	}

	/* OLD FUNCTION FOR GET AFFILIATE MASTER DATA
	public function get_affiliate_master_data_post()
	{
		$result = $this->Setting_model->affiliate_master_data();
		if($result)
		{
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['data']  			= $result;
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->admin_lang['no_rf_amount'];
			$this->api_response();
		}
	}
	*/


	//NEW FUNCTION TO GET AFFILIATE MASTER DATA
	public function get_affiliate_master_data_post()
	{
		$result = $this->Setting_model->affiliate_master_data();
		if($result)
		{
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['data']  			= $result;
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->admin_lang['no_rf_amount'];
			$this->api_response();
		}
	}


	/*OLD FUNCTION FOR UPDATE AFFILIATE MASTER DATA*/
	/*public function update_affiliate_master_data_post()
	{
		$data =  $this->input->post();
		$user_bonus = $data['UserBonus'];
		$bonus_amount = $data['Bonus'];
		foreach($user_bonus as $user_amount=>$value)
		{
			$dataArr[]= array('affiliate_type'=>$user_amount,'user_bonus'=>$value,'bonus_amount'=>$bonus_amount[$user_amount]);
		}
		$result = $this->Setting_model->update_affiliate_amount($dataArr);
		if($result == TRUE)
		{
			//delete affiliate_master_data from cache
			$cache_key = "affiliate_master_data";
			$this->delete_cache_data($cache_key);

			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['message']  		= $this->admin_lang['rf_amount_updated_success'];
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= 'error';
			$this->api_response();
		}
	}
	*/

	/*NEW FUNCTION FOR UPDATE AFFILIATE MASTER DATA*/
	public function update_affiliate_master_data_post()
	{
		$affiliate_data =  $this->input->post();
		if(empty($affiliate_data)) {
                    $this->api_response_arry['response_code'] 	= 500;
                    $this->api_response_arry['message']  		= 'error';
                    $this->api_response();
		}	
		$update_data = array();
		foreach($affiliate_data as $key=>$value) {                    
                    
                    if(in_array($value['affiliate_type'], array(6,7,8,9,16,17,18))) {
                        $value['real_amount'] = 0;
                        $value['bonus_amount'] = 0;
                        $value['coin_amount'] = 0;
                    }
                    if($value['affiliate_type'] == 14) {
                        $value['user_real'] = 0;
                        $value['user_bonus'] = 0;
                        $value['user_coin'] = 0;
                    }
                    
                    if(isset($value['affiliate_type'])) {
                        unset($value['affiliate_type']);		
                    }

                    if(isset($value['is_referral'])) {
                        unset($value['is_referral']);		
                    }	
                    $value['last_update_date'] = format_date();
                    $update_data[] = $value;
		}

		$is_updated = $this->db->update_batch(AFFILIATE_MASTER,$update_data,'affiliate_master_id');		
                //delete cache
                foreach($affiliate_data as $key=>$value) {
                    $affiliate_type = '';
                    if(isset($value['affiliate_type'])) {
                        $affiliate_type = $value['affiliate_type'];		
                    }
                            
                    $aff_cache_key = "affiliate_master_".$value['affiliate_master_id'];
                    $this->delete_cache_data($aff_cache_key);
                    if(!empty($affiliate_type)) {
                        $aff_type_cache_key = "aff_master_type_".$affiliate_type;
                        $this->delete_cache_data($aff_type_cache_key);
                    }
		}
        $cache_key = "affiliate_master_data";
		$this->delete_cache_data($cache_key);

		$this->delete_cache_data('how_to_earn_coins');

		$sports_ids = array(CRICKET_SPORTS_ID,SOCCER_SPORTS_ID,KABADDI_SPORTS_ID,FOOTBALL_SPORTS_ID,BASKETBALL_SPORTS_ID,BASEBALL_SPORTS_ID);
		$input_arr = array();
		$input_arr['lang_file'] = '1';
		$input_arr['ignore_cache'] = '1';
		$input_arr['file_name'] = 'lobby_banner_list_';
		$input_arr['sports_ids'] = $sports_ids;
		$this->delete_cache_and_bucket_cache($input_arr);

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['message']  		= $this->admin_lang['rf_amount_updated_success'];
		$this->api_response();
		
	}

	/**
	 * [update_bonus_amount_post description]
	 * Summary :- update bonus_fund data 
	 * @return [type] [description]
	 */
	public function update_bonus_amount_post()
	{	
		$this->form_validation->set_rules('bonus_money', 'Bonus Money', 'trim|required|max_length[5]');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data_arr =  $this->input->post();
		$result = $this->Setting_model->update_bonus_amount($data_arr);
		if($result)
		{
			
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['message']  	= $this->admin_lang['rf_bonus_amount_updated_success'];
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->admin_lang['no_change'];
			$this->api_response();
		}
	}

	public function get_bonus_amount_post()
	{
		$result = $this->Setting_model->get_bonus_current_amount();
		if($result){
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['data']  			= $result;
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->admin_lang['no_rf_amount'];
			$this->api_response();
		}
	}

	public function get_payment_setting_post()
	{
		$result = $this->Setting_model->get_payment_setting();
		$result['payment_config'] = $this->Setting_model->get_payment_config();
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	public function update_payment_setting_post()
	{	
		$post_data = $this->input->post();
		if(empty($post_data)){
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->admin_lang['no_change'];
			$this->api_response();
		}
		$config_array = array();
		$count = 0;
		$payment_config_array = $this->admin_lang['payment_config_array'];
		foreach ($post_data as $key => $value) {
		    $this->form_validation->set_rules($key, $payment_config_array[$key], 'trim|required');
			$config_array[$count]['update_array'] = array('meta_value'=>$value,'modified_date'=>format_date());
			$config_array[$count]['where_array']  = array('meta_key'=>$key);
			$count++;
		}
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$result = $this->Setting_model->update_payment_config($config_array);
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['message']  		= "Payment setting updated successfully.";
		$this->api_response();
	}//function end.

	function get_sports_hub_list_post()
	{
		$this->form_validation->set_rules('language', 'Language', 'trim|required|max_length[5]');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post = $this->input->post();
		$hub_list= $this->Setting_model->get_sports_hub($post['language']);


		$module_data = $this->get_master_setting();
		$data = $this->filter_hub_data($hub_list,$module_data);
		$data = array_values($data);
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  		= $data;
		$this->api_response();
	}

	function get_hub_icon_banner_post()
	{
		$hub= $this->Setting_model->get_hub_icon_banner();
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  		= $hub;
		$this->api_response();
	}

	function update_sports_hub_post()
	{
		$this->form_validation->set_rules('language', 'Language', 'trim|required|max_length[5]');
		$this->form_validation->set_rules('title', 'Title', 'trim|required|max_length[100]');
		// $this->form_validation->set_rules('body', 'body', 'trim|max_length[250]');
		$this->form_validation->set_rules('image', 'image', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('game_key', 'game key', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post = $this->input->post();

		$update_data = array();
		$update_data[$post['language'].'_title'] =$post['title']; 
		$update_data[$post['language'].'_desc'] =$post['body']; 
		$update_data['image'] =$post['image']; 

		$updated= $this->Setting_model->update_sports_hub($update_data,$post['game_key']);
		
		if(!$updated)
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= "Problem while data update.";
			$this->api_response();
		}

		$config_cache_key = 'app_config';
		$this->delete_cache_data($config_cache_key);
		$this->push_s3_data_in_queue('app_master_data',array(),"delete");

		$this->deleteS3BucketFile("app_master_data");
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['message']  		= "Data updated successfully.";
		$this->api_response();

	}



	function update_sporthub_order_post()
	{

		
		$post_data = $this->input->post();

		// echo '<pre>';
		// print_r($post_data);die;

		

		$update_data = array();

		foreach ($post_data as $key => $value) {

			// echo "<pre>";
			// print_r($value);die;

		$update_data = array(

        'display_order' => $value['display_order'] 

        );

		$update_key = array(

        'sports_hub_id' => $value['sports_hub_id']  

        );	

		    $updated= $this->Setting_model->update(SPORTS_HUB,$update_data,$update_key);

		}		

		if(!$updated)
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= "Problem while data update.";
			$this->api_response();
		}

		$config_cache_key = 'app_config';
		$this->delete_cache_data($config_cache_key);
		$this->push_s3_data_in_queue('app_master_data',array(),"delete");

		$this->deleteS3BucketFile("app_master_data");
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['message']  		= "Data updated successfully.";
		$this->api_response();

	}



/**
	* @Summary: check if folder exists otherwise create new
	* @create_date: 24 july, 2015
	*/
	private function check_folder_exist($dir)
	{	
		if(!is_dir($dir))
			return mkdir($dir, 0777);
		return TRUE;
	}

	public function do_upload_post(){
		
		$file_field_name	= $this->post('name');
		$dir				= ROOT_PATH.UPLOAD_DIR;
		$subdir				= ROOT_PATH.NOTIFICATION_IMG_DIR;
		$temp_file			= $_FILES['file']['tmp_name'];
		$ext				= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$vals = 			@getimagesize($temp_file);
		$width = $vals[0];
		$height = $vals[1];
		//360x240
		if ($height != '240' || $width != '360') {
			
			$invalid_size = str_replace("{max_height}",'240',$this->admin_lang['ad_image_invalid_size']);
			$invalid_size = str_replace("{max_width}",'360',$invalid_size);
			$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>$invalid_size) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
			$this->check_folder_exist($subdir);
		}

		$file_name = time().".".$ext ;
		$filePath     = NOTIFICATION_IMG_DIR.$file_name;

		/*--Start amazon server upload code--*/
		if( strtolower( IMAGE_SERVER ) == 'remote' )
		{
			try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
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
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
		}
	 	else {

			$config['allowed_types']	= 'jpg|png|jpeg|gif';
			$config['max_size']			= '5000';
			$config['max_width']		= '360';
			$config['max_height']		= '240';
			$config['upload_path']		= $subdir;
			$config['file_name']		= time();

			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload('file'))
			{
				$error = $this->upload->display_errors();
				$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>strip_tags($error)) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
			}
			else
			{

				$upload_data = $this->upload->data();
				$this->response(
						array(
								config_item('rest_status_field_name')=>TRUE,
								'data'=>array('image_name' =>IMAGE_PATH.NOTIFICATION_IMG_DIR.$file_name ,'image_url'=> $subdir),
								rest_controller::HTTP_OK
							)
						);
			}
		}		
	}

	public function get_front_bg_image_post(){
		$image_url = IMAGE_PATH."upload/".FRONT_BG_IMAGE_PATH;
		$result = array('image_name'=>FRONT_BG_IMAGE_PATH,'image_url'=>'','is_uploaded'=>'0');
		$filePath = "upload/".FRONT_BG_IMAGE_PATH;
		try{
            $data_arr = array();
            $data_arr['file_path'] = $filePath;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_uploaded = $upload_lib->get_file_info($data_arr);
            if($is_uploaded){
                $result['image_url'] = $image_url;
				$result['is_uploaded'] = "1";
            }
        }catch(Exception $e){
        	$error_msg = 'Caught exception: '.  $e->getMessage(). "\n";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $error_msg;
            $this->api_response();
        }

		$this->api_response_arry['response_code'] = 200;
		$this->api_response_arry['data'] = $result;
		$this->api_response_arry['message'] = "";
		$this->api_response();
	}

	public function reset_front_bg_image_post()
	{
		$data_post = $this->post();
		$dir = ROOT_PATH.UPLOAD_DIR;
		$s3_dir = UPLOAD_DIR;
		$file_name = FRONT_BG_IMAGE_PATH;
		$temp_file	= ROOT_PATH."admin/assets/images/front_bg.png";
		$filePath = $s3_dir.$file_name; 
		//Start amazon server upload code
		if (strtolower(IMAGE_SERVER) == 'remote')
		{
			try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $data = array(
							'image_url' => IMAGE_PATH.$filePath,
							'image_name' => $file_name,
							'is_uploaded' => "1"
						);
					$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
					$this->api_response_arry['data']			= $data;
					$this->api_response_arry['message']			= "Image reset to default image.";
					$this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
		}
		else
		{
			$data = array(
						'image_url'=> WEBSITE_URL."admin/assets/images/front_bg.png",
						'file_name'=> "front_bg.png",
						'is_uploaded' => "1"
					);
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= $data;
			$this->api_response_arry['message']			= "Image reset to default image.";
			$this->api_response();
		}
	}

	//assets/img/open-predictor-leaderb-side-img.jpg
public function hub_image_do_upload_post(){

	// echo "here"; die;
		
		$file_field_name	= $this->post('name');
		$dir				= ROOT_PATH.UPLOAD_DIR;
		$subdir				= ROOT_PATH.SETTING_IMG_DIR;
		$temp_file			= $_FILES['name']['tmp_name'];
		$ext				= pathinfo($_FILES['name']['name'], PATHINFO_EXTENSION);
		$vals = 			@getimagesize($temp_file);
		$width = $vals[0];
		$height = $vals[1];

		$image_type	= $this->post('type');
		$post_data = $this->input->post();
		$allowed_height = 576;
		$allowed_width = 670;
		//  $allowed_height = 288;
// +        $allowed_width = 335;

		if(isset($post_data['game_key']) && $post_data['game_key'] == "allow_dfs"){
			 $allowed_height = 576;
+            $allowed_width = 670;

		}
		if($image_type==1)//featured
		{
			$allowed_height = 300;
			$allowed_width = 278;
		}

		if($image_type==2)//icon
		{
			$allowed_height = 150;
			$allowed_width = 150	;
		}

		if($image_type==3)//hub banner
		{
			$allowed_height = 375;
			$allowed_width = 1024;
		}
		//360x240
		if ($height != $allowed_height || $width != $allowed_width) {
			
			$invalid_size = str_replace("{max_height}",$allowed_height,$this->admin_lang['ad_image_invalid_size']);
			$invalid_size = str_replace("{max_width}",$allowed_width,$invalid_size);
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']		   = $invalid_size;
			$this->api_response();
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
			$this->check_folder_exist($subdir);
		}

		$file_name = time().".".$ext ;
		$filePath     = SETTING_IMG_DIR.$file_name;

		/*--Start amazon server upload code--*/
		if( strtolower( IMAGE_SERVER ) == 'remote' )
		{
			try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                	if($image_type==2)
					{
						$this->Setting_model->update_hub_icon($file_name,'allow_hub_icon');
					}

					if($image_type==3)
					{
						$this->Setting_model->update_hub_icon($file_name,'allow_hub_banner');
					}

					$data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
					$config_cache_key = 'app_config';
					$this->delete_cache_data($config_cache_key);
					$this->push_s3_data_in_queue('app_master_data',array(),"delete");

					$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
					$this->api_response_arry['data']			= $data;
					$this->api_response_arry['message']			= "Image Uploaded.";
					$this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
		}
		 else {

			$config['allowed_types']	= 'jpg|png|jpeg|gif';
			$config['max_size']			= '5000';
			$config['max_width']		= $allowed_width;
			$config['max_height']		= $allowed_height;
			$config['upload_path']		= $subdir;
			$config['file_name']		= time();

			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload('file'))
			{
				$error = $this->upload->display_errors();
				
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['global_error']		   = strip_tags($error);
				$this->api_response();
			}
			else
			{

				$upload_data = $this->upload->data();
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= array('image_name' =>IMAGE_PATH.SETTING_IMG_DIR.$file_name ,'image_url'=> $subdir);
				$this->api_response_arry['message']		   = "Image Uploaded";
				$this->api_response();
			}
		}		
	}

	public function front_bg_upload_post() 
	{
		$data_post = $this->post();
		$file_field_name = 'userfile';
		$dir = ROOT_PATH.UPLOAD_DIR;
		$s3_dir = UPLOAD_DIR;
		$file_name = FRONT_BG_IMAGE_PATH;
		$min_width = "1280";
		$min_height = "800";
		$temp_file	= $_FILES['userfile']['tmp_name'];
		$ext		= pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals		= @getimagesize($temp_file);
		$width		= $vals[0];
		$height		= $vals[1];
		
		if(strtolower($ext) != "png"){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $this->admin_lang['front_img_invalid_img_type'];
			$this->api_response();
		}

		if ($height < $min_height || $width < $min_width){

			$invalid_size = str_replace("{max_height}",$min_height,$this->admin_lang['front_bg_image_invalid_size']);
			$invalid_size = str_replace("{max_width}",$min_width,$invalid_size);

			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $invalid_size;
			$this->api_response();
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
		}
		
		$filePath     = $s3_dir.$file_name;
		//Start amazon server upload code
		if (strtolower(IMAGE_SERVER) == 'remote')
		{
			try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $data = array(
						'image_url' => IMAGE_PATH.$filePath,
						'image_name' => $file_name,
						'is_uploaded' => "1");
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
                    $this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
		}
		else
		{
			$config['allowed_types'] = 'png';
			$config['max_size'] = '2048';
			$config['max_width'] = '2000';
			$config['max_height'] = '1200';
			$config['upload_path'] = $dir;
			$config['file_name'] = $file_name;

			$this->load->library('upload', $config);

			if (!$this->upload->do_upload($file_field_name))
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = strip_tags($this->upload->display_errors());
				$this->api_response();
			}
			else
			{
				$uploaded_data = $this->upload->data();
				$image_path =  UPLOAD_DIR . $uploaded_data['file_name'];
				
				$data = array(
							'image_url'=> $image_path,
							'file_name'=> $uploaded_data['file_name'],
							'is_uploaded' => "1"
					);
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $data;
				$this->api_response();
				
			}
			
		}		
	}

	/**
	 * function to update lobby page in admin 
	 * in which sports display name and banner will be updated
	 */

	 public function get_sports_display_name_post(){
		 $this->form_validation->set_rules('language', 'Language', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$this->load->model('league/League_model');
		$result = $this->League_model->get_sports_list($post_data);
		if (!$result)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']		   = $this->admin_lang['get_sport_detail_error'];
			$this->api_response();
		}
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $result;
		$this->api_response_arry['message']		   = $this->admin_lang['get_sport_detail_success'];
		$this->api_response();
	 }

	 /**
	  * function to update the sports display name as well their sequence
	  */

	public function update_sports_display_name_post(){ 
		
		$data_arr = $this->input->post();
		
		$this->load->model('league/League_model');
		$result = $this->League_model->update_sports_list($data_arr);
		if (!$result)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']		   = $this->admin_lang['update_sports_order_error'];
			$this->api_response();
		}
		$update_arr = array();
		foreach($data_arr as $key=>$data){
			$update_arr[$key]['order'] = $data['order'];
			$update_arr[$key]['sports_id'] = $data['sports_id'];
		}
		// print_r($update_arr);exit;
		$update = $this->Setting_model->update_sports_hub_order($update_arr);
		if(!$update){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']		   = $this->admin_lang['update_sports_order_error'];
			$this->api_response();
		}
		$this->flush_cache_data();
		$this->deleteS3BucketFile("app_master_data.json");
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= array();
		$this->api_response_arry['message']		   = $this->admin_lang['update_sports_order_success'];
		$this->api_response();
	}
	
	/**
	 * method to upload a banner image
	 */

	public function banner_upload_post() 
	{
		$data_post = $this->post();
		$key_name = $data_post['key_name'];
		$file_field_name = 'userfile';
		$dir = ROOT_PATH.BANNER_UPLOAD_DIR;
		$s3_dir = BANNER_UPLOAD_DIR;
		
		$max_width = "1024";
		$max_height = "375";
		// $temp_file	= __DIR__.'/f1.png';
		// $ext = 'png';

		$temp_file	= $_FILES['userfile']['tmp_name'];
		$ext		= pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals		= @getimagesize($temp_file);
		$width		= $vals[0];
		$height		= $vals[1];
		
		// if(strtolower($ext) != "png"){
		// 	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		// 	$this->api_response_arry['message']			= $this->admin_lang['front_img_invalid_img_type'];
		// 	$this->api_response();
		// }
		
		if ($height != $max_height || $width != $max_width){
			
			$invalid_size = str_replace("{max_height}",$max_height,$this->admin_lang['lobby_banner_image_invalid_size']);
			$invalid_size = str_replace("{max_width}",$max_width,$invalid_size);
			
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $invalid_size;
			$this->api_response();
		}
		
		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
		}
		
		$file_name = time() . "." . $ext;
		$filePath     = $s3_dir.$file_name;
		
		//Start amazon server upload code
		if (strtolower(IMAGE_SERVER) == 'remote')
		{
			try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $data = array(
						'image_url' => IMAGE_PATH.$filePath,
						'image_name' => $file_name,
						'is_uploaded' => "1"
					);
					$update_image_data = $this->_update_banner_image_data_post($file_name,$key_name);
					if(!$update_image_data){
						$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
						$this->api_response_arry['message']		   = $this->admin_lang['img_data_error'];
						$this->api_response();	
					}
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
                    $this->api_response_arry['message'] = $this->admin_lang['image_upload_success'];
                    $this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
		}
		else
		{
			$config['allowed_types'] = 'png';
			$config['max_size'] = '2048';
			$config['max_width'] = '2000';
			$config['max_height'] = '1200';
			$config['upload_path'] = $dir;
			$config['file_name'] = $file_name;

			$this->load->library('upload', $config);

			if (!$this->upload->do_upload($file_field_name))
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = strip_tags($this->upload->display_errors());
				$this->api_response();
			}
			else
			{
				$uploaded_data = $this->upload->data();
				$image_path =  UPLOAD_DIR .'/setting'. $uploaded_data['file_name'];
				
				$data = array(
							'image_url'=> $image_path,
							'file_name'=> $uploaded_data['file_name'],
							'is_uploaded' => "1"
					);
				$update_image_data = $this->_update_banner_image_data_post($uploaded_data['file_name'],$key_name);
				if(!$update_image_data){
					$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['message']		   = $this->admin_lang['img_data_error'];
					$this->api_response();	
				}
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $data;
				$this->api_response_arry['message'] = $this->admin_lang['image_upload_success'];
				$this->api_response();
				
			}
			
		}		
	}

	/**
	 * delete banner method to delete banner image so that it will be reset as original.
	 */

	public function remove_banner_post()
	{
		$image_name = $this->input->post('image_name');
		$key_name = $this->input->post('key_name');
		if(!empty($key_name)){
			$update_data = $this->_update_banner_image_data_post("",$key_name);
		}
		
		$dir = ROOT_PATH.BANNER_UPLOAD_DIR;
		$s3_dir = BANNER_UPLOAD_DIR;
		$dir_path    = $s3_dir.$image_name;
		if( strtolower( IMAGE_SERVER ) == 'remote' )
		{
			try{
	            $data_arr = array();
	            $data_arr['file_path'] = $dir_path;
	            $this->load->library('Uploadfile');
	            $upload_lib = new Uploadfile();
	            $is_deleted = $upload_lib->delete_file($data_arr);
	            if($is_deleted){
	                $this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
					$this->api_response_arry['message']	= $this->admin_lang['image_remove_success'];
					$this->api_response();
	            }else{
			        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			        $this->api_response_arry['global_error'] = "Error while delete file from server.";
			        $this->api_response();
	            }
	        }catch(Exception $e){
	            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		        $this->api_response_arry['global_error'] = "Error while delete file from server.";
		        $this->api_response();
	        }
		}
		@unlink($dir. $image_name);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['message']			= $this->admin_lang['image_remove_success'];
		$this->api_response();
	}

	/**
	 * update banner image in app_config
	 */
	private function _update_banner_image_data_post($image='',$key_name){
		$this->flush_cache_data();
		$this->deleteS3BucketFile("app_master_data.json");
		$update_data['custom_data'] = json_encode(['image'=>$image]);
		$result = $this->Setting_model->update_app_config_data($update_data,$key_name);
		if (!$result)
			{
				return FALSE;
			}
			return TRUE;
	}

	/**
	 * get banner image data
	 */

	 public function get_banner_image_data_post(){
		$banner_array = array(
			"allow_sports_prediction_bnr",
			"allow_dfs_bnr",
			"allow_prize_bnr"
		);  
		if(!$this->app_config['allow_coin_system']['key_value'] || ! $this->app_config['allow_prediction_system']['key_value']){
			$banner_array = array(
			"allow_dfs_bnr",
			"allow_prize_bnr"
			);
		}
		$result = $this->Setting_model->get_app_config_data($banner_array);
		if (!$result)
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = $this->admin_lang['get_img_data_error'];
				$this->api_response();
			}
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $result;
		$this->api_response_arry['message']		   = $this->admin_lang['get_img_data_success'];
		$this->api_response();
	 }

	 /**
	 * get contest join email flag 
	 */

	public function get_email_setting_post(){
		$email_setting = array(
			"allow_join_email"		);  
		$result = $this->Setting_model->get_app_config_data($email_setting);
		if (!$result)
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = "No Data Found";
				$this->api_response();
			}
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	 }

	 public function save_email_setting_status_post(){
		$this->form_validation->set_rules('key_name', 'Key Name', 'trim|required');
		$this->form_validation->set_rules('status', 'Status Value', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$result = $this->Setting_model->update_app_config_item_status();
		if (!$result)
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = $this->admin_lang['problem_while_save_email_status'];
				$this->api_response();
			}
		$this->delete_cache_data('app_config');
		$this->deleteS3BucketFile("app_master_data.json");
		//$this->flush_cache_data();
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['message']		   = $this->admin_lang['status_updated'];
		$this->api_response();
	  }


	 /**
	  * to update status of banner as on / off 
	  */

	  public function toggle_banner_image_status_post(){
		$this->form_validation->set_rules('key_name', 'Banner Key Name', 'trim|required');
		$this->form_validation->set_rules('status', 'Status Value', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$result = $this->Setting_model->toggle_banner_image_status();
		if (!$result)
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = $this->admin_lang['banner_switch_error'];
				$this->api_response();
			}
		$this->flush_cache_data();
		$this->deleteS3BucketFile("app_master_data.json");
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $final_result;
		$this->api_response_arry['message']		   = $this->admin_lang['banner_switch_success'];
		$this->api_response();
	  }

	  function revert_to_orignal_hub_post()
	  {
		  //type 1 for hublist, 2 for hub  icon or hub banner
		$this->form_validation->set_rules('key_name', 'Key Name', 'trim|required');
		$this->form_validation->set_rules('type', 'Type', 'trim|required');
  
		$post = $this->input->post();
		
		if(!empty($key_name)){
			$update_data = $this->_update_banner_image_data_post("",$key_name);
		}
		
		if($post['type']==1)
		{
			//delete image path from sports hub table
			$hub_item= $this->Setting_model->get_sport_hub_item($post['key_name']);
			$this->deleteS3BucketImageFile($hub_item['image']);
			$this->Setting_model->update_sports_hub(array('image' => ""),$post['key_name']);
		}
		
		if($post['type']==2)
		{
			//delete image path from sports hub table
			$hub_item =  $this->Setting_model->get_hub_icon_banner(array($post['key_name']));
			$this->deleteS3BucketImageFile($hub_item['image']);
			$this->Setting_model->update_hub_icon("",$post['key_name']);
		}
		$this->flush_cache_data();
		$this->deleteS3BucketFile("app_master_data.json");
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['message']		   = "Image Removed";
		$this->api_response();

	  }

	  /**
	   * get wallet header and body content
	   */
	  public function get_content_post(){
		  $this->form_validation->set_rules('language', 'Language', 'trim|required');
		  // $this->form_validation->set_rules('content_key', 'Content Key', 'trim|required');
		  
		  if (!$this->form_validation->run()) 
		  {
			  $this->send_validation_errors();
			}
			$post_data = $this->input->post();
			if(empty($post_data['content_key'])){
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = 'Atleast one content key is required';
				$this->api_response();
			}
		// $this->load->model('contest/Contest_model','cm');
		$result = $this->Setting_model->get_content();
		if (!$result)
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = $this->admin_lang['get_sport_detail_error'];
				$this->api_response();
			}
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $result;
		$this->api_response_arry['message']		   = $this->admin_lang['get_sport_detail_success'];
		$this->api_response();
	  }

	  /**
	   * to update the content 
	   */
	  public function update_content_post(){
		$this->form_validation->set_rules('content_key', 'Content Key', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$result = $this->Setting_model->update_content();
		if (!$result)
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = $this->admin_lang['content_update_error'];
				$this->api_response();
			}
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $result;
		$this->api_response_arry['message']		   = $this->admin_lang['content_update_success'];
		$this->api_response();

	  }

	  /**
	   * get min max withdrawal list
	   */
	  public function get_min_max_withdrawl_limit_post(){ 
		$data_array = array(
			"min_deposit",
			"max_deposit",
			"min_withdrawl",
			"max_withdrawl",
			"auto_withdrawal",
		); 
		$result = $this->Setting_model->get_app_config_data($data_array);
		$final_result= array();
		foreach($result as $key=>$result){
			if($result['key_name']=='auto_withdrawal' && $result['key_value']==1)
			{
				$final_result['pg_fee'] = json_decode($result['custom_data'],true)['pg_fee'];
				$final_result['auto_withdrawal_limit'] = json_decode($result['custom_data'],true)['auto_withdrawal_limit'];
			}else{
				$final_result[$result['key_name']] = $result['key_value'];
			}
		}
		
		if (!$result)
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = $this->admin_lang['get_error'];
				$this->api_response();
			}
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $final_result;
		$this->api_response_arry['message']		   = $this->admin_lang['get_success'];
		$this->api_response();
	 }

	 /**
	  * update min max limit
	  */

	  public function update_min_max_withdrawl_limit_post(){
		$this->form_validation->set_rules('min_deposit', 'Minimum Deposit Amount', 'trim|required');
		$this->form_validation->set_rules('max_deposit', 'Minimum Deposit Amount', 'trim|required');
		$this->form_validation->set_rules('min_withdrawl', 'Minimum Withdrawal Amount', 'trim|required');
		$this->form_validation->set_rules('max_withdrawl', 'Minimum Withdrawal Amount', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		  
		  if(empty($this->input->post())){
			  $this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			  $this->api_response_arry['message']		   = $this->admin_lang['min_max_error'];
			  $this->api_response();
			}
				
			  $post_data = $this->input->post();

			if(isset($post_data['auto_withdrawal_limit']) && ($post_data['auto_withdrawal_limit'] < $post_data['min_withdrawl']) && $post_data['auto_withdrawal_limit']!="")
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   	= $this->admin_lang['wlimit_less_then_minwamt'];
				$this->api_response();
			}
			  $withdrawal_data = $this->Setting_model->get_app_config_data(["auto_withdrawal"]);
			  $withdrawal_data['custom_data'] = json_decode($withdrawal_data[0]['custom_data'],true);
			  $withdrawal_data['custom_data']['pg_fee'] = $post_data['pg_fee'];
			  $withdrawal_data['custom_data']['auto_withdrawal_limit'] = $post_data['auto_withdrawal_limit'];
			  $withdrawal_data[0]['custom_data'] =  json_encode($withdrawal_data['custom_data']);
			  unset($withdrawal_data['custom_data']);
			
			$final_data = array();
			foreach($post_data as $key=>$data){
				$key_name = $key;
				$final_data['key_value'] = $data;

				if((isset($post_data['pg_fee']) && $key=='pg_fee') || (isset($post_data['auto_withdrawal_limit']) && $key=='auto_withdrawal_limit'))
				  {
					unset($final_data['key_value']);
					$key_name = 'auto_withdrawal';
					$final_data['custom_data'] = $withdrawal_data[0]['custom_data'];
				  }

				$result = $this->Setting_model->update_app_config_data($final_data,$key_name);
			}
			$this->flush_cache_data();
			$this->deleteS3BucketFile("app_master_data.json");
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= array();
			$this->api_response_arry['message']		   = $this->admin_lang['min_max_success'];
			$this->api_response();
	  }

	public function update_self_exclusion_limit_post(){
		$this->form_validation->set_rules('default_limit', 'default limit', 'trim|required|integer');
		$this->form_validation->set_rules('max_limit', 'max limit', 'trim|required|integer');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}  
			  
		$post_data = $this->input->post();
		$final_data = array();
		$final_data['default_limit'] = $post_data['default_limit'];
		$final_data['max_limit'] = $post_data['max_limit'];
		$key_name = 'allow_self_exclusion';
		$update_data = array('custom_data' => json_encode($final_data));
		$result = $this->Setting_model->update_app_config_data($update_data,$key_name);		
		$this->delete_cache_data('app_config');
		$this->deleteS3BucketFile("app_master_data.json");
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= array();
		$this->api_response_arry['message']		   = $this->lang->line('self_exclusion_success');
		$this->api_response();
	}

	 

  	public function get_app_admin_config_post(){
        $form_data = app_config_form_setting(1);
        foreach($form_data as $key=>&$row){
			if($key=='auto_withdrawal') {
				unset($form_data[$key]['child']['auto_withdrawal_limit']);
			}				
        	$row['value'] = "";
        	if(isset($this->app_config[$key])){
        		$item_arr = $this->app_config[$key];
        		$row['value'] = $item_arr['key_value'];
        		if(isset($row['child']) && !empty($row['child'])){
        			foreach($row['child'] as $ch_key=>&$ch_row){
        				$ch_row['value'] = "";
        				if(isset($item_arr['custom_data'][$ch_key])){
        					$ch_row['value'] = $item_arr['custom_data'][$ch_key];
        				}
        			}
        		}
        	}
        }
        $this->api_response_arry['data'] = $form_data;
		$this->api_response();
	}

	/**
	* save app config
	*/
  	public function save_config_post(){
		$post_data = $this->input->post();
		if(empty($post_data)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Setting data can't be empty.";
			$this->api_response();
		}
		
		$config_data = array();
		foreach($post_data as $key=>$row){
			if(!isset($config_data[$key])){
				$tmp_arr = array();
				$tmp_arr['key_name'] = $key;
				$tmp_arr['key_value'] = $row['value'];
				$tmp_arr['custom_data'] = array();
				if(isset($row['child'])){
					foreach($row['child'] as $ch_key=>$ch_row){
						$tmp_arr['custom_data'][$ch_key] = $ch_row['value'];
					}
					if($key=='auto_withdrawal') {
						$old_auto_withdrawal_limit = $this->Setting_model->get_single_row ('custom_data', APP_CONFIG, ["key_name"=>'auto_withdrawal']);
						$old_auto_withdrawal_limit = json_decode($old_auto_withdrawal_limit['custom_data'],true);
						$tmp_arr['custom_data']['auto_withdrawal_limit'] = $old_auto_withdrawal_limit['auto_withdrawal_limit'];
					}
				}
				$tmp_arr['custom_data'] = json_encode($tmp_arr['custom_data']);
				$config_data[$key] = $tmp_arr;
			} 
		}

		//echo "<pre>";print_r($config_data);die;
		if(!empty($config_data)){
			$this->Setting_model->replace_into_batch(APP_CONFIG,$config_data);

			$config_cache_key = 'app_config';
			$this->delete_cache_data($config_cache_key);

			$this->push_s3_data_in_queue('app_version',array(),"delete");
			$this->push_s3_data_in_queue('app_master_data',array(),"delete");

			$this->flush_cache_data();
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['data'] = array();
			$this->api_response_arry['message'] = "Configuration setting updated successfully.";
			$this->api_response();
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Please update setting properly.";
			$this->api_response();
		}
	}

	/**
     * Used for get prize distribution cron status
     * @param 
     * @return string
     */
	public function get_prize_cron_setting_post(){
		$key_arr = array("prize_cron");  
		$result = $this->Setting_model->get_app_config_data($key_arr);
		if (!$result){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "No Data Found";
			$this->api_response();
		}
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	 }

	/**
     * Used for save prize distribution cron status
     * @param 
     * @return string
     */
	public function save_prize_cron_status_post(){
		$this->form_validation->set_rules('key_name', 'Key Name', 'trim|required');
		$this->form_validation->set_rules('status', 'Status Value', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		if($post_data['key_name'] != "prize_cron"){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->admin_lang['invalid_setting_key'];
			$this->api_response();
		}
		$result = $this->Setting_model->update_app_config_item_status();
		if (!$result)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->admin_lang['save_prize_cron_status_error'];
			$this->api_response();
		}
		$this->delete_cache_data('app_config');
		$this->deleteS3BucketFile("app_master_data.json");

		$this->api_response_arry['message'] = $this->admin_lang['status_updated'];
		$this->api_response();
  	}

	 /**
	   * get wallet header and body content
	   */
	  public function get_active_payment_gateway_post(){
		
			$post_data = $this->input->post();
		
		// $this->load->model('contest/Contest_model','cm');
		$result = $this->Setting_model->get_active_payment_gateway();
		
		$this->api_response_arry['response_code']	=  rest_controller::HTTP_OK;
		$this->api_response_arry['data']['result']			=  $result;
		$this->api_response_arry['message']		   =  "active payment get success";
		$this->api_response();
	  }



	  /**
	   * to update the content 
	   */
	  public function update_paymentgatway_detail_post(){
		// $this->form_validation->set_rules('content_key', 'Content Key', 'trim|required');

		// if (!$this->form_validation->run()) 
		// {
		// 	$this->send_validation_errors();
		// }
		$post_data = $this->input->post();
	
		$result = $this->Setting_model->update_paymentgatway_detail($post_data);	

		$config_cache_key = 'app_config';
		$this->delete_cache_data($config_cache_key);

		$this->push_s3_data_in_queue('app_version',array(),"delete");
		$this->push_s3_data_in_queue('app_master_data',array(),"delete");

		$this->flush_cache_data();
		
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		// $this->api_response_arry['data']			= $result;
		$this->api_response_arry['message']		   = 'Payment detail update Successfully';
		$this->api_response();

	  }

	    /**
	   * to update the content 
	   */
	  public function generate_qr_post(){
         $post_data = $this->input->post();
		 $url = $post_data['url'];
		 $qrCodeUrl = get_qr_code($url,200,200);
		 $data = file_get_contents($qrCodeUrl);
		//  $data = $this->file_get_contents_curl($qrCodeUrl);
		$fp = ROOT_PATH.'upload/appqr.png';
		//  echo $data ;die;
		file_put_contents($fp,$data);
		// $filePath = QR_IMAGE_PATH.'upload/appqrs.png';
		$file_name = 'appqr.png' ;	

		$dir = ROOT_PATH.UPLOAD_DIR;
		$s3_dir = UPLOAD_DIR;
		$temp_file	= ROOT_PATH.UPLOAD_DIR.$file_name;
		$filePath = $s3_dir.$file_name; 
		//Start amazon server upload code
		if (strtolower(IMAGE_SERVER) == 'remote')
		{
			try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $data = array(
							'image_url' => IMAGE_PATH.$filePath,
							'image_name' => $file_name,
							'is_uploaded' => "1"
						);
					$this->api_response_arry['data']			= $data;
					$this->api_response_arry['message']			= "QR Generated successfully.";
					$this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
		}
		
	 }

	 function file_get_contents_curl($url) { 
		$ch = curl_init(); 
	
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_URL, $url); 
	
		$data = curl_exec($ch); 
		curl_close($ch); 
	
		return $data; 
} 
		
	 




}

/* End of file Setting.php */
/* Location: ./application/controllers/Setting.php */