<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Userdashboard_model extends MY_Model {
    
    public $db_user ;
    public $db_fantasy ;
    public $db_analytics ;
    public $testingNode =FALSE;

    public function __construct() 
    {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
		$this->db_fantasy	= $this->load->database('db_fantasy', TRUE);

    }

    public function get_all_users($post)
	{
			$this->db_user->select("user_id,
			total_deposit as balance,
			total_winning as winning_balance,
			RANK() OVER ( ORDER BY total_deposit DESC ) deposit_rank,
			RANK() OVER ( ORDER BY total_winning DESC ) winning_rank")
			->from(USER)
			->where(array('status'=>1)); 
			
			$query = $this->db_user->get();
			$result = $query->result_array();
			
			return $result;

	}
	public function get_all_user_contest_join($post)
	{
		
            $this->db_fantasy->select("lm.user_id,count(lm.user_id) as total_joined ,
			RANK() OVER ( ORDER BY count(lm.user_id) DESC ) total_joined_rank")
			->from(LINEUP_MASTER .' lm')
			->join(LINEUP_MASTER_CONTEST .' lmc','lmc.lineup_master_id=lm.lineup_master_id')
			->join(CONTEST .' C','C.contest_id=lmc.contest_id')
			->where_not_in('C.status',array(1))
			->group_by('lm.user_id');
			
			$query = $this->db_fantasy->get();
			$result = $query->result_array();
			return $result;

	}
	public function get_all_user_referral_count($post)
	{
			$this->db_user->select("
			user_id,
			COUNT(IF(affiliate_type = 1, 1, NULL)) total_referral,
			RANK() OVER (ORDER BY COUNT(IF(affiliate_type = 1, 1, NULL)) DESC) as total_referral_rank,
			sum(user_real_cash) as total_referral_amount, 
			")
			->from(USER_AFFILIATE_HISTORY)
			->group_by('user_id');

			$query = $this->db_user->get();
			$result = $query->result_array();
			
			return $result;

	}
	public function get_user_withdraw($user_id)
	{
		
		$this->db_user
		->select("sum(winning_amount) as total_withdraw")
		->from(ORDER)
		->where(array('source'=>8, 'status'=>1 ,'user_id'=>$user_id));
		$query = $this->db_user->get();
		$result = $query->result();
		
		if (!empty($result[0]->total_withdraw) && $result[0]->total_withdraw!=NULL){
			return $result[0]->total_withdraw;
		}	else{
			return 0;
		}		
	}
	public function get_user_amount_deposited($user_id)
	{
		
		$this->db_user
		->select("sum(real_amount) as amount_deposited")
		->from(ORDER)
		->where(array('source'=>7,'status'=>1,'type'=>0,'user_id'=>$user_id));
		
		$query = $this->db_user->get();
		$result = $query->result();
		if (!empty($result[0]->amount_deposited) && $result[0]->amount_deposited!=NULL){
			return $result[0]->amount_deposited;
		}	else{
			return 0;
		}		
	}
	public function get_users_winning($post) {
		$startDate =$post['startDate'];
		$this->db_user
		->select("user_id,sum(winning_amount) as winning_amount")
		->from(ORDER)
		->where("date_added  BETWEEN '{$startDate}' AND '{$startDate} 23:59:59'")
		->where(array('source'=>3,'status'=>1,'type'=>0))
		->group_by('user_id');

		$query = $this->db_user->get();
		$result = $query->result_array();
		return ($result)?$result:array();
	}

	public function update_winning_amount($user_id,$winning_amount) {
		
		$query = $this->db_user->query("Update vi_".USER." SET total_winning=total_winning+".$winning_amount." Where user_id=$user_id");
		return ($query)?$query:array();
	}
	
	
	
}
