<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'/libraries/REST_Controller.php';

class Master extends REST_Controller {

	function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->load->model('Master_model');
	}
	
	/**
	 * [get_master_duration description]
	 * Summary :- get master duration
	 * @return [type] [description]
	 */
	function get_master_duration_post()
	{
		$post 	= $this->post();
		$result	= $this->Master_model->get_master_duration($post);
		
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $result;
		$this->api_response();
	}

	/**
	 * [get_all_salary_cap description]
	 * Summary :- get all salary cap
	 * @return [type] [description]
	 */
	function get_all_salary_cap_post()
	{
		$post 	= $this->post();
		$result	= $this->Master_model->get_all_salary_cap($post);
		
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $result;
		$this->api_response();
	}

	/**
	 * [get_salary_cap_detail description]
	 * @Summary : get_ssalary_cap_detail
	 * @return  [type]
	 */	 
	public function get_salary_cap_detail_post()
	{
		
		$post_data = $this->post();
		
		$result = $this->Master_model->get_salary_cap_detail($post_data);
		
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

	/**
	 * [get_all_drafting_style description]
	 * Summary :- This function used to getting all drafting style 
	 * @return [type] [description]
	 */
	function get_all_drafting_style_post()
	{
		
		$result	= $this->Master_model->get_all_drafting_style();
		
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $result;
		$this->api_response();
	}


	public function get_all_prize_data_post()
	{
		
		$cache_key = CACHE_PREFIX.'contest_prize_data';
        $number_of_winner = $this->redis_cache->get(array('fn'=>$cache_key), TRUE);
        if(!$number_of_winner)
        {
			$number_of_winner	= $this->Master_model->get_all_number_of_winner();
            $this->redis_cache->set(array('fn'=>$cache_key, 'data'=>$number_of_winner),REDIS_30_DAYS);
        }

		$result = array(					
					'all_number_of_winner'			=> $number_of_winner['all_number_of_winner'],
					'number_of_winner_validation'	=> $number_of_winner['number_of_winner_validation']
				);
		
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $result;
		$this->api_response();
		
	}

	public function prize_details_by_size_fee_prizing_post()
	{
		$game_data = $this->post();

		$result = $this->Master_model->prize_details_by_size_fee_prizing($game_data);
		
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $result;
		$this->api_response();
	}


	/**
	 * [get_all_position description]
	 * @Summary : Used to get list of all positions by league id
	 * @param   : league_id
	 * @return  [type]
	 */
	public function get_all_position_post()
	{
		$_POST 	= $this->post();
		$this->form_validation->set_rules('sports_id', 'Sports Id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$sports_id = $this->input->post('sports_id');
		$result = $this->Master_model->get_master_position($sports_id);

		$this->api_response_arry['response_code'] = 200;
		$this->api_response_arry['data']          = $result;
		$this->api_response();
	}


	public function get_all_match_by_league_id_post()
	{
		$data_arr = $this->input->post();

		$this->form_validation->set_rules('league_id', 'league id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_target_url   = 'season/get_all_match_by_league_id';
		$post_api_response       = $this->http_post_request($post_target_url,$data_arr);

		if(is_array($post_api_response) && isset($post_api_response['data']) && count($post_api_response['data']) > 0)
		{
			/*$post_api_response['data'][count($post_api_response['data']) ] =  array(
				 	'home' => 'Entire Series',
                    'home_uid' => '',
                    'away' => '',
                    'away_uid' => '',
                    'api_week' => '',
                    'week' => '',
                    'season_game_uid' => 'entire_series',
                    'league_id' => '',
                    'season_scheduled_date' => '',
                    'format' => '',
				);*/
		}

		$this->api_response_arry = $post_api_response;
		$this->api_response();
	}

	public function send_validation_errors($return_only=FALSE)
	{
		$errors = $this->form_validation->error_array();

		$return['response_code'] = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
		$return['error'] = ($errors);
		$return['service_name'] = '';
		$return['message']      = '';
		$return['global_error'] = '';
		$return['data']         = '';

		if(!$this->input->post())
		{
			$return['global_error'] = 'Please input valid parameters.';
		}

		if($return_only===TRUE) return $return;

		$this->response($return, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
	}
    /**
	 * [get_all_roster_category description]
	 * @Summary : Used to get list of all roster categories by league id
	 * @param   : league_id
	 * @return  [type]
	 */    
    public function get_all_roster_category_post() 
    {
    	$_POST 	= $this->post();
       	$this->form_validation->set_rules('sports_id', 'Sports Id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

       	$sports_id = $this->input->post('sports_id');
       	
        $result = $this->Master_model->get_all_roster_category($sports_id);
        
        $this->api_response_arry['response_code'] = 200;
		$this->api_response_arry['data']          = $result;
		$this->api_response();
	}

	public function get_master_description_post()
	{
		$post_data	= $this->input->post();
		$rows		= true;

		if(!empty($post_data) && isset($post_data['rows']))
		{
			$rows = $post_data['rows'];
			unset($post_data['rows']);
		}
       	
        $result = $this->Master_model->get_master_description($post_data,$rows);
        
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']          = $result;
		$this->api_response();

	}
}
