<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Xp_point extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();

        $allow_coin = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        $allow_xp_point = isset($this->app_config['allow_xp_point'])?$this->app_config['allow_xp_point']['key_value']:0;
        if($allow_coin == 0) {
            $allow_xp_point = 0;
        }
        if(!$allow_xp_point) {
            
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  ="Module not activated." ;
            $this->api_response();
        } 

		$this->load->model('Xp_point_model');
		$_POST = $this->input->post();	
        $this->admin_lang = $this->lang->line('xp_level');
        
		//1=> join game
        
	}

    function get_level_list_post() {
        $result = $this->Xp_point_model->get_level_list();

        $this->load->helper('xppoint_helper');
        $levels = get_master_levels();

        foreach($result['result'] as &$row)
        {
            $row['level_str'] = $levels[$row['level_number']];
        }
        $this->api_response_arry['data']['level_list'] = $result['result'];
        $this->api_response_arry['data']['total'] = $result['total'];
		$this->api_response();	
    }

    function get_reward_list_post() {
        $result = $this->Xp_point_model->get_reward_list();

        $this->load->helper('xppoint_helper');
        $levels = get_master_levels();

        foreach($result['result'] as &$row)
        {
            $row['level_str'] = $levels[$row['level_number']];
        }
        $this->api_response_arry['data']['reward_list'] = $result['result'];
        $this->api_response_arry['data']['total'] = $result['total'];
		$this->api_response();	
    }

    function get_add_master_data_post() { 
        //get added level numbers
        $saved_levels = $this->Xp_point_model->get_added_levels();
        $added_levels = array();
        $level_start_point_map = array();
        if(!empty($saved_levels))
        {
            $added_levels= array_column($saved_levels,'level_number');
            $level_start_point_map = array_column($saved_levels,'end_point','level_number');
            sort($added_levels);
        }

        $this->load->helper('xppoint_helper');
        //get all master levels
        $levels = get_master_levels();

        $level_numbers = array_keys($levels);
        sort($level_numbers);
        
        //get unsaves levels
        $level_diff = array_diff($level_numbers,$added_levels);
        $level_diff = array_values($level_diff);
    
        $start_point = 0;
        //get next level start points
        if($level_diff[0]> 1)
        {
            $start_point =$level_start_point_map[$level_diff[0]-1]+1; 
        }
        $this->api_response_arry['data']['next_level'] =$level_diff[0] ;
        $this->api_response_arry['data']['start_point'] = $start_point;
		$this->api_response();	   
    }

    function add_level_post()
    {
        $this->form_validation->set_rules('level_number', 'Level','trim|required');
        $this->form_validation->set_rules('start_point', 'Start Point', 'trim|required');
        $this->form_validation->set_rules('end_point', 'End Point', 'trim|required');
       
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        //check if level already exists
        $row = $this->Xp_point_model->get_single_row('level_number',XP_LEVEL_POINTS,array('level_number' => $post['level_number']));

        if(!empty($row))
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['level_already_exists'];
            $this->api_response();
        }

        if($post['start_point'] >= $post['end_point'])
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['start_point_cannot_greater_than_end_point'];
            $this->api_response();
        }

        //check for correct start point
        if($post['level_number'] > 1)
        {
            $row = $this->Xp_point_model->get_single_row('end_point',XP_LEVEL_POINTS,array('level_number' => $post['level_number']-1));

            if(empty($row))
            {
                $last_level = $post['level_number']-1;
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	=str_replace('#last_level#',$last_level,$this->admin_lang['you_cannot_create_this_level']);
                $this->api_response();
            }

            if($post['start_point'] != ($row['end_point']+1))
            {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->admin_lang['enter_valid_start_point'];
                $this->api_response();
            }

        }

        $current_date =format_date();
        $save_data = array();
        $save_data['level_number'] = $post['level_number'];
        $save_data['start_point'] = $post['start_point'];
        $save_data['end_point'] = $post['end_point'];
        $save_data['added_date'] = $current_date;
        $save_data['updated_date'] = $current_date;
        $level_id= $this->Xp_point_model->add_level($save_data);

        //xp_levels
        $this->delete_cache_data('xp_levels'); //delete cache
        $this->api_response_arry['data']['level_id'] = $level_id;
        $this->api_response_arry['message'] = $this->admin_lang['level_add_success'];
		$this->api_response();	   
        
    }


    function update_levels_post()
    {
        $post = $this->input->post();
        $post = $this->update_last_node($post);
        $msg = $this->validate_level_arr($post);

        if($msg !='')
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $msg;
            $this->api_response();
        }

        $update_data = array();
        $current_date = format_date();
        $count = count($post);
        foreach($post as $key => $level)
        {
            $update_data[]=array(
                "level_number" => $level['level_number'],
                "start_point" => $level['start_point'],
                "end_point" => $level['end_point'],
                "updated_date" => $current_date
            );
        }

        if(!empty($update_data))
        {
            $this->Xp_point_model->update_all_levels($update_data);
        }

         //xp_levels
         $this->delete_cache_data('xp_levels'); //delete cache
        $this->api_response_arry['message'] = "Level's has been updated successfully";
		$this->api_response();	   


    }

    public function update_last_node($data)
    {
        if(empty($data))
        {
            $msg = "Please enter data to update";
            return ;
        }

        $last_node_end =  $data[sizeof($data)-1]['end_point'];
        $last_node_level = $data[sizeof($data)-1]['level_number'];
        $last_node_db_data = $this->Xp_point_model->get_single_row('*',XP_LEVEL_POINTS,["level_number"=>$last_node_level]);
        
        $next_node_db_data = $this->Xp_point_model->get_single_row('*',XP_LEVEL_POINTS,["level_number"=>$last_node_level+1]);
        // echo $this->db->last_query();die;
        if(empty($next_node_db_data))
        {
            return $data; // if next record is not available.
        }
       
            if($last_node_end!=$last_node_db_data['end_point']) // edited last point
            {
                if($last_node_end==$next_node_db_data['start_point'])
                {
                    // if($next_node_db_data['start_point'] == $next_node_db_data['end_point'])
                    // {
                    //     $data[sizeof($data)+1] = array(
                    //         "level_number"=> $next_node_db_data['level_number'],
                    //         "start_point"=> $next_node_db_data['start_point']+1,
                    //         "end_point"=> $next_node_db_data['end_point']+1
                    //     );

                    // }else{
                    $data[sizeof($data)] = array(
                        "level_number"=> $next_node_db_data['level_number'],
                        "start_point"=> $next_node_db_data['start_point']+1,
                        "end_point"=> $next_node_db_data['end_point']
                    );
                    // }
                }elseif($last_node_end > $next_node_db_data['start_point'])
                {
                    // if(($last_node_end+1) == $next_node_db_data['end_point'])
                    // {
                    //     $data[sizeof($data)+1] = array(
                    //         "level_number"=> $next_node_db_data['level_number'],
                    //         "start_point"=> $last_node_end+1,
                    //         "end_point"=> $next_node_db_data['end_point']+1
                    //     );
                    // }else{
                        $data[sizeof($data)] = array(
                            "level_number"=> $next_node_db_data['level_number'],
                            "start_point"=> $last_node_end+1,
                            "end_point"=> $next_node_db_data['end_point']
                        );
                    // }
                }elseif(($next_node_db_data['start_point'] - $last_node_end) > 1)
                {
                        $data[sizeof($data)] = array(
                            "level_number"=> $next_node_db_data['level_number'],
                            "start_point"=> $last_node_end+1,
                            "end_point"=> $next_node_db_data['end_point']
                        );
                }
            }
        return $data;
    }

    function validate_level_arr($data)
    {
        $msg = "";

        if(empty($data))
        {
            $msg = "Please enter data to update";
        }

        foreach ($data as $key => $value)
        {
            
            if(!array_key_exists('level_number',$value) || $value['level_number']=='' )
            {
                $msg = "Please enter level number.";
                break;
            }

            if(!array_key_exists('start_point',$value) || $value['start_point']=='' )
            {
                $msg = "Please enter valid start point.";
                break;
            }

            if(!array_key_exists('end_point',$value) || empty($value['end_point']) )
            {
                $msg = "Please enter valid end point.";
                break;
            }

            if($value['level_number'] ==1 && $value['start_point'] > 0)
            {
                $msg= "Please enter 0 start point for Level 1";
                break;
            }

            if($value['start_point'] >= $value['end_point']) {
                $msg   	= $this->admin_lang['start_point_cannot_greater_than_end_point'];
                break;
            }

            //check the correct sequence
            if($key > 0)
            {
                $diff= $data[$key]['start_point'] - $data[$key-1]['end_point'];
                if($diff!=1)
                {
                    $msg= "Please enter valid start and end point in proper sequence.";
                    break;
                }

            }
        }

        return $msg;
    }

    /**
     * This api used to delete level point
     */
    function delete_level_post() {
        $post = $this->input->post();
        $this->form_validation->set_rules('level_pt_id', 'Level ID','trim|required');       
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        //check if level already assign to user 
        $row = $this->Xp_point_model->get_single_row('xp_user_id',XP_USERS,array('level_id' => $post['level_pt_id']));

        if(!empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['level_already_assign'];
            $this->api_response();
        }

        $row = $this->Xp_point_model->get_single_row('level_number, level_pt_id',XP_LEVEL_POINTS,array('level_pt_id' => $post['level_pt_id']));

        if($row) {
            //get max level number
            $max_row = $this->Xp_point_model->get_single_row('MAX(level_number) AS level_number',XP_LEVEL_POINTS);
            if($row['level_number'] == $max_row['level_number']) {
                $this->Xp_point_model->delete_level($row);
                 //xp_levels
                $this->delete_cache_data('xp_levels'); //delete cache
                $this->api_response_arry['message'] = $this->admin_lang['level_deleted'];
            } else {
                $this->api_response_arry['message'] = $this->admin_lang['highest_level_deleted'];
            }
            
        } else {
            $this->api_response_arry['message'] = $this->admin_lang['invalid_level'];
        }
       
		$this->api_response();	
    }

    /**
     * This api used to get badge master list
     */
    function get_badge_master_list_post() {
        $result = $this->Xp_point_model->get_badge_master_list();

        $this->api_response_arry['data'] = $result;
		$this->api_response();	
    }

    /**
     * This api used to add reward
     */
    function add_reward_post() {
        $this->form_validation->set_rules('level_number', 'Level','trim|required');
        $this->form_validation->set_rules('badge_id', 'Badge', 'trim|required');
       
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        //check if level exists
        $row = $this->Xp_point_model->get_single_row('level_number',XP_LEVEL_POINTS,array('level_number' => $post['level_number']));

        if(empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_level'];
            $this->api_response();
        }

        //check if badge exists
        $row = $this->Xp_point_model->get_single_row('badge_id',XP_BADGE_MASTER,array('badge_id' => $post['badge_id']));

        if(empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_badge'];
            $this->api_response();
        }

        //check if rewards already exists for level
        $row = $this->Xp_point_model->get_single_row('reward_id',XP_LEVEL_REWARDS,array('level_number' => $post['level_number'], 'is_deleted' => 0));

        if(!empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['reward_already_exists'];
            $this->api_response();
        }

        $coins              = isset($post['coins']) ? $post['coins'] : array();
        $deposit_cashback   = isset($post['deposit_cashback']) ? $post['deposit_cashback'] : array();
        $joining_cashback   = isset($post['joining_cashback']) ? $post['joining_cashback'] : array();
        
        //check for correct start point
        if($coins || $deposit_cashback || $joining_cashback) {

            $insert_data        = array();
            
            $insert_flag = 0 ;
            if(isset($coins['allow']) && $coins['allow'] == 1) {
                $amt = isset($coins['amt']) ? $coins['amt'] : 0;
                if(!empty($amt) && is_numeric($amt)) {
                    if($amt < 1 || $amt > 99999) {
                        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['global_error']  	= $this->admin_lang['min_max_coin_amount'];
                        $this->api_response();
                    }
                    $insert_data['is_coin']        = $coins['allow'];
                    $insert_data['coin_amt']       = $amt;
                    $insert_flag = 1;
                } else {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_amount'];
                    $this->api_response();
                }
            }

            if(isset($deposit_cashback['allow']) && $deposit_cashback['allow'] == 1) {
                $amt = isset($deposit_cashback['amt']) ? $deposit_cashback['amt'] : 0;
                $type = isset($deposit_cashback['type']) ? $deposit_cashback['type'] : 0;
                $cap = isset($deposit_cashback['cap']) ? $deposit_cashback['cap'] : 0;
                if(!in_array($type, array(0,1))) {
                    $type = 0;
                }
                if(!empty($amt) && is_numeric($amt)) {
                    if($amt < 1 || $amt > 100) {
                        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['global_error']  	= $this->admin_lang['min_max_deposit_amount'];
                        $this->api_response();
                    }                    
                } else {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_amount'];
                    $this->api_response();
                }

                if($cap < 1 || $cap > 99999) {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= sprintf($this->admin_lang['min_max_cap_amount'], 'Cashback');
                    $this->api_response();
                }

                $insert_data['is_cashback']         = $deposit_cashback['allow'];
                $insert_data['cashback_amt']        = $amt;
                $insert_data['cashback_type']       = $type;
                $insert_data['cashback_amt_cap']    = $cap;
                $insert_flag = 1;
            }

            if(isset($joining_cashback['allow']) && $joining_cashback['allow'] == 1) {
                $amt = isset($joining_cashback['amt']) ? $joining_cashback['amt'] : 0;
                $type = isset($joining_cashback['type']) ? $joining_cashback['type'] : 0;
                $cap = isset($joining_cashback['cap']) ? $joining_cashback['cap'] : 0;
                if(!in_array($type, array(0,1))) {
                    $type = 0;
                }
                if(!empty($amt) && is_numeric($amt)) {
                    if($amt < 1 || $amt > 100) {
                        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['global_error']  	= $this->admin_lang['min_max_joining_amount'];
                        $this->api_response();
                    }                    
                } else {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_amount'];
                    $this->api_response();
                }

                if($cap < 1 || $cap > 99999) {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= sprintf($this->admin_lang['min_max_cap_amount'], 'Contest');
                    $this->api_response();
                }

                $insert_data['is_contest_discount'] = $joining_cashback['allow'];
                $insert_data['discount_percent']    = $amt;
                $insert_data['discount_type']       = $type;
                $insert_data['discount_amt_cap']    = $cap;
                $insert_flag = 1;
            }

            if($insert_flag) {
                $current_date =format_date();
                $insert_data['level_number']    = $post['level_number'];
                $insert_data['badge_id']        = $post['badge_id'];

                $insert_data['added_date'] = $current_date;
                $insert_data['modified_date'] = $current_date;
                $reward_id = $this->Xp_point_model->add_reward($insert_data);

                $this->api_response_arry['data']['reward_id'] = $reward_id;

                $this->delete_cache_data('xp_reward_list'); //delete cache

                $this->api_response_arry['message'] = $this->admin_lang['reward_add_success'];
                $this->api_response();	 

            } else {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_form_value'];
                $this->api_response();
            }            

        } else {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_form_value'];
            $this->api_response();
        }         
    }

    /**
     * This api used to update reward
     */
    function update_reward_post() {
        $this->form_validation->set_rules('reward_id', 'Reward id','trim|required');
        $this->form_validation->set_rules('badge_id', 'Badge', 'trim|required');
       
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        //check if level exists
        $row = $this->Xp_point_model->get_single_row('reward_id',XP_LEVEL_REWARDS,array('reward_id' => $post['reward_id']));

        if(empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_reward'];
            $this->api_response();
        }

        //check if badge exists
        $row = $this->Xp_point_model->get_single_row('badge_id',XP_BADGE_MASTER,array('badge_id' => $post['badge_id']));

        if(empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_badge'];
            $this->api_response();
        }

        
        $coins              = isset($post['coins']) ? $post['coins'] : array();
        $deposit_cashback   = isset($post['deposit_cashback']) ? $post['deposit_cashback'] : array();
        $joining_cashback   = isset($post['joining_cashback']) ? $post['joining_cashback'] : array();
        
        //check for correct start point
        if($coins || $deposit_cashback || $joining_cashback) {

            $insert_data        = array();
            
            $insert_data['is_coin']             = 0;
            $insert_data['coin_amt']            = 0;

            $insert_data['is_cashback']         = 0;
            $insert_data['cashback_amt']        = 0;
            $insert_data['cashback_type']       = 0;
            $insert_data['cashback_amt_cap']    = 0;

            $insert_data['is_contest_discount'] = 0;
            $insert_data['discount_percent']    = 0;
            $insert_data['discount_type']       = 0;
            $insert_data['discount_amt_cap']    = 0;


            $insert_flag = 0 ;
            if(isset($coins['allow']) && $coins['allow'] == 1) {
                $amt = isset($coins['amt']) ? $coins['amt'] : 0;
                if(!empty($amt) && is_numeric($amt)) {
                    if($amt < 1 || $amt > 99999) {
                        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['global_error']  	= $this->admin_lang['min_max_coin_amount'];
                        $this->api_response();
                    }
                    $insert_data['is_coin']        = $coins['allow'];
                    $insert_data['coin_amt']       = $amt;
                    $insert_flag = 1;
                } else {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_amount'];
                    $this->api_response();
                }
            }

            if(isset($deposit_cashback['allow']) && $deposit_cashback['allow'] == 1) {
                $amt = isset($deposit_cashback['amt']) ? $deposit_cashback['amt'] : 0;
                $type = isset($deposit_cashback['type']) ? $deposit_cashback['type'] : 0;
                $cap = isset($deposit_cashback['cap']) ? $deposit_cashback['cap'] : 0;
                if(!in_array($type, array(0,1))) {
                    $type = 0;
                }
                if(!empty($amt) && is_numeric($amt)) {
                    if($amt < 1 || $amt > 100) {
                        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['global_error']  	= $this->admin_lang['min_max_deposit_amount'];
                        $this->api_response();
                    }                    
                } else {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_amount'];
                    $this->api_response();
                }

                if($cap < 1 || $cap > 99999) {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= sprintf($this->admin_lang['min_max_cap_amount'], 'Cashback');
                    $this->api_response();
                }

                $insert_data['is_cashback']         = $deposit_cashback['allow'];
                $insert_data['cashback_amt']        = $amt;
                $insert_data['cashback_type']       = $type;
                $insert_data['cashback_amt_cap']    = $cap;
                $insert_flag = 1;
            }

            if(isset($joining_cashback['allow']) && $joining_cashback['allow'] == 1) {
                $amt = isset($joining_cashback['amt']) ? $joining_cashback['amt'] : 0;
                $type = isset($joining_cashback['type']) ? $joining_cashback['type'] : 0;
                $cap = isset($joining_cashback['cap']) ? $joining_cashback['cap'] : 0;
                if(!in_array($type, array(0,1))) {
                    $type = 0;
                }
                if(!empty($amt) && is_numeric($amt)) {
                    if($amt < 1 || $amt > 100) {
                        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['global_error']  	= $this->admin_lang['min_max_joining_amount'];
                        $this->api_response();
                    }                    
                } else {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_amount'];
                    $this->api_response();
                }

                if($cap < 1 || $cap > 99999) {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= sprintf($this->admin_lang['min_max_cap_amount'], 'Contest');
                    $this->api_response();
                }

                $insert_data['is_contest_discount'] = $joining_cashback['allow'];
                $insert_data['discount_percent']    = $amt;
                $insert_data['discount_type']       = $type;
                $insert_data['discount_amt_cap']    = $cap;
                $insert_flag = 1;
            }

            if($insert_flag) {
                $current_date =format_date();
                $insert_data['badge_id']        = $post['badge_id'];

                $insert_data['modified_date'] = $current_date;
                $this->Xp_point_model->update_reward($insert_data, $post['reward_id']);

                $this->api_response_arry['data']['reward_id'] = $post['reward_id'];

                $this->delete_cache_data('xp_reward_list'); //delete cache

                $this->api_response_arry['message'] = $this->admin_lang['reward_update_success'];
                $this->api_response();	 

            } else {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_form_value'];
                $this->api_response();
            }            

        } else {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_form_value'];
            $this->api_response();
        }         
    }


    /**
     * This api used to delete reward
     */
    function delete_reward_post() {
        $post = $this->input->post();
        $this->form_validation->set_rules('reward_id', 'Reward ID','trim|required');       
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        //check if level already assign to user 
        $flag = $this->Xp_point_model->check_rewards_deletion($post['reward_id']);

        if($flag) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['reward_already_assign'];
            $this->api_response();
        }

        $this->Xp_point_model->delete_reward($post['reward_id']);
        $this->delete_cache_data('xp_reward_list'); //delete cache
        $this->api_response_arry['message'] = $this->admin_lang['reward_deleted'];
		$this->api_response();	
    }

    /**
     * This api used to get activities master list
     */
    function get_activities_master_list_post() {
        $result = $this->Xp_point_model->get_activities_master_list();

        $this->api_response_arry['data'] = $result;
		$this->api_response();	
    }

    /**
     * This api used to add activity
     */
    function add_activity_post() {
        $this->form_validation->set_rules('activity_master_id', 'Activity','trim|required|integer');
        $this->form_validation->set_rules('xp_point', 'Earn Points', 'trim|required|integer');
        $this->form_validation->set_rules('recurrent_count', 'Count', 'trim|integer');
       
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        
        
        //check if master activity exists
        $row = $this->Xp_point_model->get_single_row('activity_master_id, activity_type',XP_ACTIVITY_MASTER,array('activity_master_id' => $post['activity_master_id']));

        if(empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_activity'];
            $this->api_response();
        }

        $recurrent_count   = isset($post['recurrent_count']) ? $post['recurrent_count'] : 0;
        
        //check for activity type
        if($row['activity_type'] == 2) { 

            //check if activity exists for this master activity
            $row = $this->Xp_point_model->get_single_row('activity_id',XP_ACTIVITIES,array('activity_master_id' => $post['activity_master_id'], 'recurrent_count' => $recurrent_count, 'is_deleted' => 0));

            if(!empty($row)) {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->admin_lang['activity_already_exists'];
                $this->api_response();
            }

            if($recurrent_count < 1 || $recurrent_count > 101) {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->admin_lang['min_max_count'];
                $this->api_response();
            }
        } else {
            $recurrent_count = 0;
            //check if activity exists for this master activity
            $row = $this->Xp_point_model->get_single_row('activity_id',XP_ACTIVITIES,array('activity_master_id' => $post['activity_master_id'], 'is_deleted' => 0));

            if(!empty($row)) {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->admin_lang['activity_already_exists'];
                $this->api_response();
            }
        }

        $insert_data        = array();        
        $current_date =format_date();
        $insert_data['activity_master_id']  = $post['activity_master_id'];
        $insert_data['xp_point']            = $post['xp_point'];
        $insert_data['recurrent_count']     = $recurrent_count;
        $insert_data['added_date'] = $current_date;
        $insert_data['modified_date'] = $current_date;
        $activity_id = $this->Xp_point_model->add_activity($insert_data);

        $this->delete_cache_data('xp_activity_list'); //delete cache

        $this->api_response_arry['data']['activity_id'] = $activity_id;
        $this->api_response_arry['message'] = $this->admin_lang['activity_add_success'];
        $this->api_response();       
    }

    /**
     * This api used to update activity
     */
    function update_activity_post() {
        $this->form_validation->set_rules('activity_id', 'Activity','trim|required|integer');
        $this->form_validation->set_rules('xp_point', 'Earn Points', 'trim|required|integer');
        $this->form_validation->set_rules('recurrent_count', 'Count', 'trim|integer');
       
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        
        //check if activity exists for this master activity
        $row = $this->Xp_point_model->get_single_row('activity_master_id',XP_ACTIVITIES,array('activity_id' => $post['activity_id'], 'is_deleted' => 0));

        if(empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_activity'];
            $this->api_response();
        }

        //check if master activity exists
        $row = $this->Xp_point_model->get_single_row('activity_master_id, activity_type',XP_ACTIVITY_MASTER,array('activity_master_id' => $row['activity_master_id']));

        if(empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['invalid_activity'];
            $this->api_response();
        }

        $recurrent_count   = isset($post['recurrent_count']) ? $post['recurrent_count'] : 0;
        
        //check for activity type
        if($row['activity_type'] == 2) {
            
            //check if activity exists for this master activity
            $row = $this->Xp_point_model->get_single_row('activity_id',XP_ACTIVITIES,array('activity_master_id' => $row['activity_master_id'], 'recurrent_count' => $recurrent_count, 'is_deleted' => 0, 'activity_id != ' => $post['activity_id']));

            if(!empty($row)) {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->admin_lang['activity_already_exists'];
                $this->api_response();
            }

            if($recurrent_count < 1 || $recurrent_count > 101) {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->admin_lang['min_max_count'];
                $this->api_response();
            }
        } else {
            $recurrent_count = 0;
        }

        $insert_data        = array();        
        $current_date       = format_date();
        $insert_data['xp_point']            = $post['xp_point'];
        $insert_data['recurrent_count']     = $recurrent_count;
        $insert_data['modified_date']       = $current_date;
        $this->Xp_point_model->update_activity($insert_data, $post['activity_id']);

        $this->delete_cache_data('xp_activity_list'); //delete cache

        $this->api_response_arry['data']['activity_id'] = $post['activity_id'];
        $this->api_response_arry['message'] = $this->admin_lang['activity_update_success'];
        $this->api_response();       
    }

    /**
     * This api used to get activities list
     */
    function get_activities_list_post() {
        $result = $this->Xp_point_model->get_activities_list();
        $this->api_response_arry['data']['activities_list'] = $result['result'];
        $this->api_response_arry['data']['total'] = $result['total'];
		$this->api_response();	
    }

    /**
     * This api used to delete activity
     */
    function delete_activity_post() {
        $post = $this->input->post();
        $this->form_validation->set_rules('activity_id', 'Activity ID','trim|required');       
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        //check if activity exists for this master activity
        $row = $this->Xp_point_model->get_single_row('history_id',XP_USER_HISTORY,array('activity_id' => $post['activity_id']));

        if(!empty($row)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['activity_not_deleted'];
            $this->api_response();
        }
        
        $this->Xp_point_model->delete_activity($post['activity_id']);

        $this->delete_cache_data('xp_activity_list'); //delete cache
        $this->api_response_arry['message'] = $this->admin_lang['activity_deleted'];
		$this->api_response();	
    }

    /**
     * This api used to get level leaderboard
     */
    function level_leaderboard_post() {
        $result = $this->Xp_point_model->level_leaderboard();
        $this->api_response_arry['data']['user_list'] = $result['result'];
        $this->api_response_arry['data']['total'] = $result['total'];
		$this->api_response();	
    }

    /**
     * This api used to get level leaderboard
     */
    function activities_leaderboard_post() {
        $this->form_validation->set_rules('activity_id', 'Activity','trim|required|integer');       
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $result = $this->Xp_point_model->activities_leaderboard();
        $this->api_response_arry['data']['user_list'] = $result['result'];
        $this->api_response_arry['data']['total'] = $result['total'];
		$this->api_response();	
    }

    function get_user_xp_history_post() {
        $this->form_validation->set_rules('user_id', 'User ID','trim|required|integer');       
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();
        $result_data  =$this->Xp_point_model->get_user_xp_history($post_data['user_id']);
        $total_point_arr= $this->Xp_point_model->get_user_xp($post_data['user_id']);
        $result_data['total_point']  = $total_point_arr['point'];
        $this->api_response_arry['data'] = $result_data;
        $this->api_response();
    }
}

