<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Xp_module extends MY_Controller
{
    var $xp_func_map = array();
    public $signup_interval = 1;//time in hours
    public $cash_contest_interval = 1;//time in hours
    public $free_contest_interval = 1;//time in hours
    public $coin_contest_interval = 1;//time in hours
    public $invite_interval = 1;//time in hours
    public $first_deposit_interval = 1;//time in hours
    public $post_1st_deposit_interval = 1;//time in hours
    public $winning_zone_interval = 1;//time in hours
    public $kyc_interval = 1;//time in hours
    public $deposit_cashback_interval = 1;//time in hours
    public $contest_joined_cashback_interval = 1;//time in hours
    public $level_list = array();
    public $allow_xp_point = false;
    public $xp_point_start_date = NULL;
    public function __construct()
    {
        parent::__construct();
        $this->xp_func_map[1] = array('func' => "process_signup_xp_points");
        $this->xp_func_map[2] = array('func' => "process_play_cash_contest_xp_points") ;
        $this->xp_func_map[4] = array('func' => "process_play_free_contest_xp_points") ;
        $this->xp_func_map[3] = array('func' => "process_play_coin_contest_xp_points") ;
        $this->xp_func_map[5] = array('func' => "process_invite_friends_xp_points") ;
        $this->xp_func_map[7] = array('func' => "process_first_deposit_xp_points") ;
        $this->xp_func_map[8] = array('func' => "process_post_first_deposit_xp_points") ;
        $this->xp_func_map[9] = array('func' => "process_winning_zone_xp_points") ;
        $this->xp_func_map[6] = array('func' => "process_kyc_xp_points") ;
        $this->level_list = $this->get_level_list();
        $this->allow_xp_point= $this->app_config['allow_xp_point']['key_value'];
        if(isset($this->app_config['allow_xp_point']['custom_data']['start_date']) && $this->app_config['allow_xp_point']['custom_data']['start_date']!=='')
        {
            $this->xp_point_start_date = $this->app_config['allow_xp_point']['custom_data']['start_date'];
        }

        $a_xp_point = isset($this->app_config['allow_xp_point'])?$this->app_config['allow_xp_point']['key_value']:0;
        $allow_coin_system = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;

        if(!$allow_coin_system || !$a_xp_point)
        {
            echo "Module not Activated.";exit();
        }
    }

    public function index()
    {
        echo "Welcome";die();
    }

    public function process_xp_activity_points($activity_master_id="")
    {

        if(empty($activity_master_id))
        {
            exit();
        }
        $this->benchmark->mark('code_start');
        if(isset($this->xp_func_map[$activity_master_id]))
        {
            $func_name =$this->xp_func_map[$activity_master_id]['func'];
            $this->$func_name($activity_master_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');

    }

    private function process_common_activity($user_ids,$activities,$user_count_map)
    {
        $users = $this->Xp_module_model->get_xp_users($user_ids);
        if(!empty($users))
        {
            $users = array_column($users,NULL,'user_id');
        }
        foreach($activities as $activity){ 
            //get user activity credig xp point count
            $user_credit_activity_counts =  $this->Xp_module_model->user_activity_xp_credit_count($user_ids,$activity['activity_id']);
            $user_xp_credit_map  = array();
            if(!empty($user_credit_activity_counts))
            {
                $user_xp_credit_map  = array_column($user_credit_activity_counts,'xp_credit_count','user_id');

            }

            //get user id with actual count to execute process
            $final_data =  $this->render_user_xp_credit_arr($user_count_map,$user_xp_credit_map,$activity);
         
            foreach($final_data as $user_id => $count_to_execute)
            {
                $this->xp_point_add_to_queue($user_id,$users,$activity,$count_to_execute);
            }

        }
    }

     /**
     * @iteration 2 hour
     * @since June 2021
     * @uses function to process play cash contest point to xp_user table
     * Play Cash Contest – Recurrent, with count
	 * cron for every 2 hour to fetch completed contest from vi_contest with condition of paid and public contest group by user is
	 * fetch users joined total cash contest count from fantasy db
	 * check users activity eligibility for activity reward from history and history table
     * **/
   private function process_play_cash_contest_xp_points($play_cash_contest_aid)
   {
        $this->load->model('Xp_module_model');
        $activities= $this->Xp_module_model->get_activity_by_id($play_cash_contest_aid);

        $users =  $this->Xp_module_model->get_completed_cash_contest(array());
      
        if(!empty($users) && !empty($activities))
        {
            $user_ids = array_column($users,'user_id');
            //get all details from start
            $total_user_contest =  $this->Xp_module_model->get_completed_cash_contest($user_ids,0);
            $user_contest_map  = array_column($total_user_contest,'contest_count','user_id');
            //get total cach contest count
            $this->process_common_activity($user_ids,$activities,$user_contest_map);
        } 
   }

   private function process_play_free_contest_xp_points($play_free_contest_aid)
   {
        $this->load->model('Xp_module_model');
        $activities= $this->Xp_module_model->get_activity_by_id($play_free_contest_aid);

        $users =  $this->Xp_module_model->get_completed_free_contest(array());
       
        if(!empty($users) && !empty($activities))
        {
            $user_ids = array_column($users,'user_id');
            $total_user_contest =  $this->Xp_module_model->get_completed_free_contest($user_ids,0);
            $user_contest_map  = array_column($total_user_contest,'contest_count','user_id');
            //get total cach contest count
            $this->process_common_activity($user_ids,$activities,$user_contest_map);
        } 
  
   }

   private function process_play_coin_contest_xp_points($play_coin_contest_aid)
   {
        $this->load->model('Xp_module_model');
        $activities= $this->Xp_module_model->get_activity_by_id($play_coin_contest_aid);

        $users =  $this->Xp_module_model->get_completed_coin_contest(array());

        if(!empty($users) && !empty($activities))
        {
            $user_ids = array_column($users,'user_id');
            $total_user_contest =  $this->Xp_module_model->get_completed_coin_contest($user_ids,0);
            $user_contest_map  = array_column($total_user_contest,'contest_count','user_id');
            //get total cach contest count
            $this->process_common_activity($user_ids,$activities,$user_contest_map);
        } 
   }

   private function process_winning_zone_xp_points($activity_master_id)
   {
        $this->load->model('Xp_module_model');
        $activities= $this->Xp_module_model->get_activity_by_id($activity_master_id);

        $users =  $this->Xp_module_model->get_winning_zone_users(array());
        if(!empty($users) && !empty($activities))
        {
            $user_ids = array_column($users,'user_id');
            $total_user_contest =  $this->Xp_module_model->get_winning_zone_users($user_ids,0);
            $user_contest_map  = array_column($total_user_contest,'count_value','user_id');
            //get total contest count
            $this->process_common_activity($user_ids,$activities,$user_contest_map);
        } 

        return ;
   }

   private function process_invite_friends_xp_points($activity_master_id)
   {
        $this->load->model('Xp_module_model');
        $activities= $this->Xp_module_model->get_activity_by_id($activity_master_id);

        $users =  $this->Xp_module_model->get_user_who_send_invites(array());
        
        if(!empty($users) && !empty($activities))
        {
            $user_ids = array_column($users,'user_id');
            $total_user_count =  $this->Xp_module_model->get_user_who_send_invites($user_ids,0);
            $user_count_map  = array_column($total_user_count,'count_value','user_id');
            //get total cach contest count
            $this->process_common_activity($user_ids,$activities,$user_count_map);
        } 
   }

   private function process_first_deposit_xp_points($activity_master_id)
   {
        $this->load->model('Xp_module_model');
        $activities= $this->Xp_module_model->get_activity_by_id($activity_master_id);
        $users =  $this->Xp_module_model->get_first_deposit_user(array());

        if(!empty($users) && !empty($activities))
        {
            $user_ids = array_column($users,'user_id');
            $total_user_count =  $users;
            $user_count_map  = array_column($total_user_count,'count_value','user_id');
            //get total cach contest count
            
            $this->process_common_activity($user_ids,$activities,$user_count_map);
        } 
   }

   private function process_post_first_deposit_xp_points($activity_master_id)
   {
        $this->load->model('Xp_module_model');
        $activities= $this->Xp_module_model->get_activity_by_id($activity_master_id);
        $users =  $this->Xp_module_model->get_post_first_deposit_user(array());

       
        if(!empty($users) && !empty($activities))
        {
            $user_ids = array_column($users,'user_id');
            $total_user_count =  $this->Xp_module_model->get_post_first_deposit_user($user_ids,0);
            $user_count_map  = array_column($total_user_count,'count_value','user_id');
            //get total cach contest count
            foreach($user_count_map as $key => $value)
            {
                if($value > 0)
                {
                    $user_count_map[$key] = $value -1;  
                }
            }
            
            $this->process_common_activity($user_ids,$activities,$user_count_map);
        } 

   }

   private function process_kyc_xp_points($activity_master_id)
   {
        $this->load->model('Xp_module_model');
        $activities= $this->Xp_module_model->get_activity_by_id($activity_master_id);
        $users =  $this->Xp_module_model->get_kyc_user(array());

        if(!empty($users) && !empty($activities))
        {
            $user_ids = array_column($users,'user_id');
            $total_user_count =  $this->Xp_module_model->get_kyc_user($user_ids,0);
            $user_count_map  = array_column($total_user_count,'count_value','user_id');
            //get total cach contest count
            $this->process_common_activity($user_ids,$activities,$user_count_map);
        } 
   }

   private function render_user_xp_credit_arr($user_total_count,$user_xp_credit_count=array(),$activity)
   {
        $final_arr = array();
        foreach($user_total_count as $user_id => $total_count)
        {
            $credited_count = 0;
            $now_to_be_credit_count = 0;
            if(isset($user_xp_credit_count[$user_id]))
            {
                $credited_count = $user_xp_credit_count[$user_id];
            }

            if($activity['activity_type'] == 1 && $credited_count ==0)
            {
                $final_arr[$user_id] = 1;
            }
            else if($activity['activity_type'] == 2){
                $total_to_be_credit_count = 0;  
                if($activity['recurrent_count']>0)
                {
                    $total_to_be_credit_count = intval($total_count/$activity['recurrent_count']) ;
                }
                if($total_to_be_credit_count > 0)
                {
                    $now_to_be_credit_count = $total_to_be_credit_count - $credited_count; 
                }
    
                $final_arr[$user_id] = $now_to_be_credit_count;
            }
           
        }

        return $final_arr;

   }
    /**
     * @iteration 1 hour
     * @since June 2021
     * @uses function to process signup xp point to xp_user table
     * Sign Up – One Time, no count, value
	 * every 1 hour cron
	 * fetch last 1 hour signup user
	 * vi_user with left join on xp_user_history table with history_id is null
	 * where activity_id of signup
     * **/
    private function process_signup_xp_points($signup_activity_id)
    {
        $this->load->model('Xp_module_model');
        $activities= $this->Xp_module_model->get_activity_by_id($signup_activity_id,1);


        if(!empty($activities))
        {
            //get last one hour user
           $user_ids =  $this->Xp_module_model->get_signup_users($activities['activity_id']);
           //prepare data to insert in xp_user_history and update point in xp_users
           //get existing users
           if(!empty($user_ids))
           {
               $user_ids = array_column($user_ids,'user_id');
            
                $users = $this->Xp_module_model->get_xp_users($user_ids);
                if(!empty($users))
                {
                    $users = array_column($users,NULL,'user_id');
                }

               
                foreach($user_ids as $user_id)
                {
                    $tmp = array();
                    $tmp['activity_id'] =$activities['activity_id'] ;
                    $tmp['point'] =$activities['xp_point'] ;
                    $tmp['user_id'] =$user_id ;

                    if(isset($users[$user_id]))
                    {
                        $total_points = $users[$user_id]['point'] + $activities['xp_point'];
                        $tmp['level_id'] = $this->get_next_level($total_points);
                        $custom_data = json_decode($users[$user_id]['custom_data'],TRUE);

                        if(!in_array($tmp['level_id'],$custom_data['level_ids']))
                        {
                            $custom_data['level_ids'][] = $tmp['level_id'];
                            $tmp['custom_data'] =json_encode($custom_data);
                        }

                    }
                    else
                    {
                        $tmp['level_id'] = $this->get_next_level($activities['xp_point']);
                        $custom_data = array();
                        $custom_data['level_ids'] = array();
                        $custom_data['level_ids'][] = $tmp['level_id'];
                        $tmp['custom_data'] =json_encode($custom_data);

                    }

                    //add to queue
                    $this->load->helper('queue');
                    add_data_in_queue($tmp,'credit_xp_point');
                }

           } 
        }
    }

private function xp_point_add_to_queue($user_id,$users,$activities,$count_to_execute=1)
{

    for($i=0;$i < $count_to_execute;$i++)
    {
        $tmp = array();
        $tmp['activity_id'] =$activities['activity_id'] ;
        $tmp['point'] =$activities['xp_point'] ;
        $tmp['user_id'] =$user_id ;

        if(isset($users[$user_id]))
        {
            $total_points = $users[$user_id]['point'] + $activities['xp_point'];
            $tmp['level_id'] = $this->get_next_level($total_points);
            $tmp['custom_data'] =$users[$user_id]['custom_data'];
        }
        else
        {
            $tmp['level_id'] = $this->get_next_level($activities['xp_point']);
            $custom_data = array();
            $custom_data['level_ids'] = array();
            if($tmp['level_id'] > 0)
            {
                $custom_data['level_ids'][] = $tmp['level_id'];
            }
            $tmp['custom_data'] =json_encode($custom_data);

        }

        //add to queue
        $this->load->helper('queue');
        add_data_in_queue($tmp,'credit_xp_point');
    }
}


    private function get_next_level($points)
    {
        $last_element = end($this->level_list);
        if(isset($last_element['end_point']) && $last_element['end_point'] < $points)
        {
            return $last_element['level_pt_id'];
        }

        foreach($this->level_list as $level_id => $value)
        {
            if($points >= $value['start_point'] && $points <= $value['end_point'])
            {
                return $level_id;
            }
        }

        $first_level = reset($this->level_list);
        
        if(isset($first_level['level_pt_id']))
        {
            return $first_level['level_pt_id'];
        }
        else
        {
            return 0;
        }


    }

    public function process_deposit_cashback()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Xp_module_model');
        $this->Xp_module_model->process_deposit_cashback();    
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        
    }

    public function process_contest_joined_cashback()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Xp_module_model');
        $this->Xp_module_model->process_contest_joined_cashback();    
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        
    }

  

    

    



}