<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends MY_Model {

	public function __construct()
	{

		parent::__construct();
		$this->load->database('user_db');
				
	}

	/**
	* Method : get_summary 
	* Output : All Counts of visitors, signup,Wallet balance and users of diposited users, firsttime deposite users count and amount 
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 30 May 2019
	**/
	public function get_summary($post)
	{
			
        $Return['Data']= array();
       

        if (isset($post['StartDate']))
            $startDate = date("Y-m-d", strtotime($post['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 month'));
        if (isset($post['EndDate']))
            $endDate = date("Y-m-d", strtotime($post['EndDate']));
        else
            $endDate = date("Y-m-d");
		 $this->db->select("(select COUNT(DISTINCT(U.user_id)) AS clicks 
                                        FROM  vi_".USER." U  WHERE   U.added_date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59' ) as TotalActiveUsers",false);
		 $query = $this->db->get();
		 $result = $query->result();
         if(!empty($result)){
         	$Return['Data']= $result['0']->TotalActiveUsers;	
         }else{
			$Return['Data']= array();
         }
         
        
		/*$this->db->select("player_unique_id")
		->from(FAVOURITE_PLAYER)
		->where("user_id",$this->user_id);

		if(!empty($player_uid) && is_array($player_uid))
		{
			$this->db->where_in("player_unique_id",$player_uid);
		}

		return $result = $this->db->get()->result_array();*/
		return $Return;

	}

}