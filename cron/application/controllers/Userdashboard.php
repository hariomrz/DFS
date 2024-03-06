<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Userdashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Userdashboard_model'); 
    }
	/**
     * Used for calculate ranks and total deposit,winning,referrals
     * @param 
     * @return string
     */
    
    public function index()
	{	
		$this->benchmark->mark('code_start');
		//if this cron will take time to execute 
	   ini_set('max_execution_time','0');	
	   $ret = array();
	   $post=array();
	   $allusers =  $this->Userdashboard_model->get_all_users($post);
	   //print_r($allusers); die;
	   $allusers_contest_join_rank =  $this->Userdashboard_model->get_all_user_contest_join($post);
	   $allusers_referral_rank =  $this->Userdashboard_model->get_all_user_referral_count($post);
		
		$contest_rank = array_column($allusers_contest_join_rank,NULL,'user_id');
		$referral_rank = array_column($allusers_referral_rank,NULL,'user_id');
		$allusers_chunk = array_chunk($allusers,1000);
		$userdetail =array();
		$this->load->model('Cron_nosql_model');
		
		foreach($allusers_chunk as $chunk_users)
		{
			foreach($chunk_users as $value)
			{	
				//contest rank insert
				if(!empty($contest_rank[$value['user_id']])){
					$value['total_joined'] = $contest_rank[$value['user_id']]['total_joined'];
					$value['total_joined_rank'] = $contest_rank[$value['user_id']]['total_joined_rank'];
				} else{
					$value['total_joined'] = 0;
					$value['total_joined_rank'] = 0;
				}
				//referral  rank insert
				if(!empty($referral_rank[$value['user_id']])){
					$value['total_referral'] = $referral_rank[$value['user_id']]['total_referral'];
					$value['total_referral_rank'] = $referral_rank[$value['user_id']]['total_referral_rank'];
					$value['total_referral_amount'] = $referral_rank[$value['user_id']]['total_referral_amount'];
				} else{
					$value['total_referral'] = 0;
					$value['total_referral_rank'] = 0;
					$value['total_referral_amount'] = 0;
				}
				//total withdraw
				$withdraw = $this->Userdashboard_model->get_user_withdraw($value['user_id']);
				//$amount_deposited = $this->Userdashboard_model->get_user_amount_deposited($value['user_id']);
				//$winning_balance = $this->Userdashboard_model->get_user_winning($value['user_id']);
				$value['total_withdraw'] = $withdraw;
				//$value['balance'] = $amount_deposited;
				//$value['balance'] = $value['total_deposit'];
				//$value['winning_balance'] = $winning_balance;

				$userdetail[] = $value;
				//print_r($value); die;
				//insert user  ranks  data into mongo 
				$userExist = 	$this->Cron_nosql_model->select_one_nosql($table='userdashboard',$where=array('user_id'=>$value['user_id']),$sort_by="",$order_by="");
				
				if(!$userExist){ 
					$this->Cron_nosql_model->insert_nosql($table='userdashboard',$value);
				} else{
					$this->Cron_nosql_model->update_nosql($table='userdashboard',$where=array('user_id'=>$value['user_id']),$value);
				}
				
			}
		}
		
		echo "Userdashboard Data Updated ".date("d/m/Y");
		$this->benchmark->mark('code_end');
		echo " Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');

		//echo "<pre>"; print_r($ret);
		die;

       
	}
	/**
     * Used for calculate gamestats
     * @param 
     * @return string
     */
	public function gamestats()
	{	
		//no need now

       
	}

	/**
     * Used for calculate gamestats
     * @param 
     * @return string
     */
	public function winning()
	{	
		$post =array();
		$post['startDate'] = date('Y-m-d', strtotime('-9 days'));
		$users_winning =  $this->Userdashboard_model->get_users_winning($post);
		
		foreach($users_winning as $usr_win){
			$data=array();
			$winning_amount = $usr_win['winning_amount'];
			$user_id = $usr_win['user_id'];
			$res  =$this->Userdashboard_model->update_winning_amount($user_id,$winning_amount);
			print_r($res );die;
		}

       
	}
	

    
}
