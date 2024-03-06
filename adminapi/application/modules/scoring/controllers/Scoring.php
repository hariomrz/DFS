<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Scoring extends MYREST_Controller 
{
	public function __construct()
	{
		parent::__construct(); 
		$this->admin_lang = $this->lang->line('scoring');
		$this->load->model('Scoring_model');
	}

	public function get_scoring_filters_post()
	{
		$this->data = array();
		$this->load->model('common/Common_model');
		$sports = $this->Common_model->get_all_sport(array());
		$this->data["filters"] = array();
		if(isset($sports) && !empty($sports))
		{			
			$this->data["master_sports"] = array();
			foreach ($sports as $sport) 
			{

				$this->data["master_sports"][$sport["sports_id"]] = $sport;
				if(!array_key_exists($sport["sports_id"], $this->data["filters"]))
			    {
			    	$this->data["filters"][$sport["sports_id"]] = array();
			    }

			    $score_categories = $this->Common_model->get_scoring_categories($sport["sports_id"]);
				if(isset($score_categories) && !empty($score_categories))
			    {
			    	$this->data["filters"][$sport["sports_id"]]["scoring_cat"] = $score_categories;	
			    }
			}
		}
		
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $this->data;
		$this->api_response();
	}

	public function get_scoring_rules_post()
	{  
		$post = $this->post();

		if(!empty($post))
		{
			//$scoring_rules = $this->Scoring_model->get_scoring_rules(7, '');
			$cat_id = $this->post('cat_id');

			$format = $this->post('format');

			$sports_id = $this->post('sport_id');
			
			$con1 = array();
			$con2 = array();
			$con3 = array();

			if(!empty($cat_id))
			$con1 = array('MSC.master_scoring_category_id' => $cat_id);

			if(!empty($format))
			$con2 = array('format' => $format);

			if(!empty($format))
			$con3 = array('sports_id' => $sports_id);

			

			$con = array_merge($con1,$con2,$con3);

			
			$scoring_rules = $this->Scoring_model->get_scoring_rules_by_con($con);
			if(isset($scoring_rules) && !empty($scoring_rules))
			{
				$this->data["master_scoring_rules"] = $scoring_rules;
				$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['data']  		  = $this->data;
				$this->api_response();

			}

			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response();
		}
	}

	public function update_master_scoring_points_post()
	{
		$post = $this->post();

		if(!empty($post['scoring']))
		{
			if(ALLOW_NETWORK_FANTASY == 0)
    		{
				$sports_id = CRICKET_SPORTS_ID;
				if(isset($post['sports_id']) && $post['sports_id'] != ""){
					$sports_id = $post['sports_id'];
				}
				if($this->Scoring_model->update_batch(MASTER_SCORING_RULES, $post['scoring'], 'master_scoring_id'))
				{
					$where_con = array('MSR.master_scoring_id' => $post['scoring']['0']['master_scoring_id']);
					$scoring_rules = $this->Scoring_model->get_scoring_rules_by_con($where_con);
					if(!empty($scoring_rules) && isset($post['sports_id'])){
						$format = $scoring_rules['0']['format'];
						$format_key = "";
						if($sports_id == CRICKET_SPORTS_ID){
							$format_key = "_".$format;
						}
						$del_cache_key = "en" . "_scoring_rules_" . $sports_id;
						$this->delete_cache_data($del_cache_key);
						$del_cache_key_format = $del_cache_key.$format_key;
						$this->delete_cache_data($del_cache_key_format);

						$allow_hindi = isset($this->app_config['allow_hindi'])?$this->app_config['allow_hindi']['key_value']:0;
						$allow_gujrati = isset($this->app_config['allow_gujrati'])?$this->app_config['allow_gujrati']['key_value']:0;
						$allow_french = isset($this->app_config['allow_french'])?$this->app_config['allow_french']['key_value']:0;
	        
						if($allow_hindi == 1){
							$del_cache_key1 = "hi" . "_scoring_rules_" . $sports_id;
							$this->delete_cache_data($del_cache_key1);

							$del_cache_key1_format = $del_cache_key1.$format_key;
							$this->delete_cache_data($del_cache_key1_format);
						}
						
						if($allow_gujrati == 1){
							$del_cache_key2 = "guj" . "_scoring_rules_" . $sports_id;
							$this->delete_cache_data($del_cache_key2);

							$del_cache_key2_format = $del_cache_key2.$format_key;
							$this->delete_cache_data($del_cache_key2_format);
						}

						if($allow_french == 1){
							$del_cache_key3 = "fr" . "_scoring_rules_" . $sports_id;
							$this->delete_cache_data($del_cache_key3);

							$del_cache_key3_format = $del_cache_key3.$format_key;
							$this->delete_cache_data($del_cache_key3_format);
						}
						$sports_ids = array($post['sports_id']);
						$input_arr = array();
						$input_arr['lang_file'] = '1';
						$input_arr['file_name'] = 'scoring_master_data_';
						$input_arr['sports_ids'] = $sports_ids;
						$this->delete_cache_and_bucket_cache($input_arr);
					}

					$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
					$this->api_response_arry['message'] 	  =  $this->admin_lang['score_updated_success'];
					$this->api_response();
				}
			}	
			else
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] 	  =  $this->admin_lang['score_updated_error'];
				$this->api_response();
			}
		}
		else
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] 	  =  $this->admin_lang['score_updated_error'];
			$this->api_response();
		}


	}

	/*Update old score points with new score points */
	public function update_new_master_scoring_points_post()
	{
		if(ALLOW_NETWORK_FANTASY == 1)
    	{
			$post = $this->post();
			$sports_id = $post['sports_id'];
			if(isset($sports_id) && $sports_id != "")
			{
				$this->db	= $this->load->database('db_fantasy', TRUE);
				//New score updated
				$update_sql = "UPDATE 
								".$this->db->dbprefix(MASTER_SCORING_RULES)." AS MSR, 
								".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." AS MSC 
								SET 
									MSR.score_points = MSR.new_score_points
								WHERE 
									MSR.master_scoring_id = MSR.master_scoring_id
								AND 
									MSR.master_scoring_category_id = MSC.master_scoring_category_id
								AND
									MSR.score_points != MSR.new_score_points	
								AND
									MSC.sports_id = $sports_id
								";
				$this->db->query($update_sql);
				//echo $update_sql;die;	
				
				//Delete cache key
				$where_con = array('MSC.sports_id' => $sports_id);
				$scoring_rules = $this->Scoring_model->get_scoring_rules_by_con($where_con);
				$format_value = array_unique(array_column($scoring_rules, 'format'));
				$lang_list = unserialize(LANGUAGE_LIST);
				foreach ($format_value as $key => $format_key) 
				{
					foreach ($lang_list as $lang_key => $lang) 
					{
						//fw_hi_scoring_rules_7_1
						$del_cache_key = $lang_key."_scoring_rules_".$sports_id."_".$format_key;
						$this->delete_cache_data($del_cache_key);
						$scoring_rules_cache_key = $lang_key."_scoring_rules_".$sports_id;
        				$this->delete_cache_data($scoring_rules_cache_key);
					}	
				}	

				$sports_ids = array($post['sports_id']);
				$input_arr = array();
				$input_arr['lang_file'] = '1';
				$input_arr['file_name'] = 'scoring_master_data_';
				$input_arr['sports_ids'] = $sports_ids;
				$this->delete_cache_and_bucket_cache($input_arr);

				$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['message'] 	  =  $this->admin_lang['score_updated_success'];
				$this->api_response();
			}
			else
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] 	  =  $this->admin_lang['score_updated_error'];
				$this->api_response();
			}
		}	

	}



}	
