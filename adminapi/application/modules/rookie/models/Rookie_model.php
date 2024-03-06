<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rookie_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
        $this->db_fantasy		= $this->load->database('db_fantasy', TRUE);
		//Do your magic here
	}

    function get_all_user_count()
    {
        return $this->db->select('COUNT(user_id) as total_users')
        ->from(USER)
        ->where('is_systemuser',0)
        ->get()->row_array();
        
    }

    function get_rookie_user_count()
    {
        $date = format_date();
         $this->db->select("U.user_id, DATEDIFF('{$date}',U.added_date)/30 as months,U.total_winning as total_winning_amount")
        ->from(USER.' U')
        ->where('U.is_systemuser',0)
        ->group_by('U.user_id');

        $having = array();

        if($this->rookie_winning_amount > 0)
        {
            $having[]= " total_winning_amount<= ".$this->rookie_winning_amount;
        }

        if($this->rookie_month_number > 0)
        {
            $having[]= " months<= ".$this->rookie_month_number;
        }

        if(!empty($having))
        {
            $this->db->having(implode(' AND ',$having));
        }
        $result  =  $this->db->get()->result_array();
        
        //echo $this->db->last_query();
        return array('rookie_users' => count($result));
    }

    public function get_rookie_user_list($post_data)
    {
        $sort_field	= 'added_date';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
        $date = format_date();

        if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']) && $post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}

        $offset	= $limit * $page;

        $contest_str = "";
        // if(!empty($post_data['contest_ids']))
        // {
        //     $contest_ids = $post_data['contest_ids'];
        //     $contest_str = ' AND O.reference_id IN ('.implode(',',$contest_ids).')';
        // }

        $pre_sql ="(SELECT SUM((CASE WHEN source=3 AND  winning_amount> 0  THEN winning_amount ELSE 0 END)) as winnings, user_id from ".$this->db->dbprefix(ORDER)." where source=3 group by user_id)";
        $this->db->select("U.user_id,U.user_unique_id,CONCAT_WS(U.first_name,' ',U.last_name) as full_name, DATEDIFF('{$date}',U.added_date)/30 as months,U.added_date,
        W_ORD.winnings,
        COUNT(DISTINCT (CASE WHEN O.source=1 AND (O.real_amount > 0 OR O.winning_amount> 0 OR O.points>0 OR O.bonus_amount > 0)  THEN O.reference_id ELSE null END)) as paid_contests,IFNULL(U.user_name,U.phone_no) as user_name,U.email,U.phone_no
        ")
       ->from(USER.' U')
       ->join(ORDER.' O',"U.user_id=O.user_id AND O.source=1 $contest_str",'LEFT')
       ->join($pre_sql.' W_ORD',' U.user_id=W_ORD.user_id','LEFT');

       

       $this->db->group_by('U.user_id');

       $having = array();

       if($this->rookie_winning_amount > 0)
       {
           $having[]= " W_ORD.winnings<= ".$this->rookie_winning_amount;
       }

       if($this->rookie_month_number > 0)
       {
           $having[]= " months<= ".$this->rookie_month_number;
       }

       if(!empty($having))
       {
           $this->db->having(implode(' AND ',$having));
       }

       $tempdb = clone $this->db; //to get rows for pagination
		$tempdb = $tempdb->select("count(*) as total");
		$temp_q = $tempdb->get();
        $num_rows = $temp_q->num_rows(); 
        $total = isset($num_rows) ? $num_rows : 0; 

		$result = array();
		$sql = $this->db->limit($limit,$offset)
							->get();
			$result	= $sql->result_array();
			$result=($result)?$result:array();
		
		return array('result'=>$result,'total'=>$total);

       //$result  =  $this->db->get()->result_array();
        //echo $this->db->last_query();
    }

    function update_setting($data)
    {
        $this->db->where('key_name', 'allow_rookie_contest');
        $this->db->where('key_value', 1);
        $this->db->update(APP_CONFIG,$data);
    }

    function get_rookie_user_participation($filter_date='',$contest_ids=array())
    {
        if(empty($contest_ids))
        {
            return array();
        }

        $current_date = format_date();
        $rookie_date = date('Y-m-d',strtotime($current_date." -$this->rookie_month_number months"));
        
        $this->db->select("U.user_id,COUNT(CASE WHEN O.source=1 THEN 1 ELSE 0 END) as data_value,
        GROUP_CONCAT(DISTINCT (CASE WHEN O.source=1 THEN O.user_id ELSE '' END)) as user_ids,
        O.source,SUM((CASE WHEN O.source=1 THEN O.real_amount+O.winning_amount ELSE 0 END)) as total_entry_fee,
        SUM((CASE WHEN O.source=3 THEN O.winning_amount ELSE 0 END)) as total_winning,
        GROUP_CONCAT(DISTINCT O.reference_id) as contest_ids ,
        U.added_date,DATE_FORMAT(O.date_added,'%Y-%m-%d') as main_date",FALSE)
        ->from(USER.' U')
        ->join(ORDER.' O','O.user_id=U.user_id')
        //->where('U.is_systemuser',0)
        ->where('total_winning<=',$this->rookie_winning_amount)
        ->where('U.added_date>',$rookie_date)
        ->where_in('O.source',[1,3]);//join and won
        
        if(!empty($filter_date))
        {
            $this->db->where('O.date_added>',$filter_date);
        }

        //$contest_ids = array(12383,12439,12456);
        if(!empty($contest_ids))
        {   
            $this->db->where_in('O.reference_id',$contest_ids);
        }

        $result = $this->db->group_by('main_date')->get()->result_array();

        //echo $this->db->last_query();die('***');echo $this->db->last_query();die('***');
        
        return $result;
        
    }

    function get_graduated_rookie_data($filter_date='',$contest_ids=array(),$user_ids=array())
    {
        if(empty($user_ids))
        {
            return array();
        }
       
        $current_date = format_date();
        $rookie_date = date('Y-m-d',strtotime($current_date." -$this->rookie_month_number months"));
        $this->db->select("U.user_id,SUM(DISTINCT (CASE WHEN O.source=1 THEN 1 ELSE 0 END)) as data_value,
        GROUP_CONCAT(DISTINCT (CASE WHEN O.source=1 THEN O.user_id ELSE '' END)) as user_ids,O.source,
        SUM((CASE WHEN O.source=1 THEN O.real_amount+O.winning_amount ELSE 0 END)) as total_entry_fee,
        SUM((CASE WHEN O.source=3 THEN O.winning_amount ELSE 0 END)) as total_winning,
        COUNT(DISTINCT O.reference_id) as total_contest_ids ,
        U.added_date,
        U.total_winning,DATE_FORMAT(O.date_added,'%Y-%m-%d') as main_date,
        GROUP_CONCAT(DISTINCT (CASE WHEN O.source=3 AND O.winning_amount > 0 THEN O.user_id ELSE '' END)) as won_users
        ",FALSE)
        ->from(USER.' U')
        ->join(ORDER.' O','O.user_id=U.user_id')
        //->where('U.is_systemuser',0)
        ->where('U.total_winning<=',$this->rookie_winning_amount)
        ->where('U.added_date>',$rookie_date)
        ->where_in('O.user_id',$user_ids)
        ->where_in('O.source',[1,3]);//join and won
        
        if(!empty($filter_date))
        {
            $this->db->where('O.date_added>',$filter_date);
        }
        
        if(!empty($contest_ids))
        {   
            $this->db->where_not_in('O.reference_id',$contest_ids);
        }

        $result = $this->db->group_by('main_date')->get()->result_array();

        //echo $this->db->last_query();die('***');
        return $result;
        
    }
    
    function get_free_contest_users($users_ids=array())
    {
        if(empty($users_ids))
        {
            return array();
        }
        $this->db_fantasy->select('LM.user_id,COUNT(DISTINCT LMC.contest_id) as free_contests')
        ->from(LINEUP_MASTER_CONTEST.' LMC')
        ->join(CONTEST.' C','LMC.contest_id=C.contest_id')
        ->join(LINEUP_MASTER.' LM','LM.lineup_master_id=LMC.lineup_master_id')
        ->where_in('LM.user_id',$users_ids)
        ->where('C.entry_fee',0)
        ->where('C.status',3)
        ->group_by('LM.user_id');

        
        $result =$this->db_fantasy->get()->result_array();

        //echo $this->db_fantasy->last_query();die('***123');
        return $result;

    }

    function get_rookie_contest_ids($filter_date="")
    {
        $this->db_fantasy->select('contest_id')
        ->from(CONTEST)
        ->where('status',3)
        ->where('group_id',$this->rookie_group_id);

        if(!empty($filter_date))
        {
            $this->db_fantasy->where('season_scheduled_date>',$filter_date);
        }

        $result =$this->db_fantasy->get()->result_array();

        //echo $this->db_fantasy->last_query();die('***123');
        return $result;

    }



    function get_graduated_rookie_participation($filter_date='',$contest_ids=array(),$user_ids)
    {
        if(empty($contest_ids))
        {
            return array();
        }

        $current_date = format_date();
        $rookie_date = date('Y-m-d',strtotime($current_date." -$this->rookie_month_number months"));
        
        $this->db->select("COUNT(DISTINCT U.user_id) as with_win")
        ->from(USER.' U')
        ->join(ORDER.' O','O.user_id=U.user_id')
        //->where('U.is_systemuser',0)
        ->where('O.winning_amount>',0)
        ->where_in('O.user_id',$user_ids)
        ->where('O.source',3);//join and won
        
        if(!empty($filter_date))
        {
            $this->db->where('O.date_added>',$filter_date);
        }

        //$contest_ids = array(12383,12439,12456);
        if(!empty($contest_ids))
        {   
            $this->db->where_in('O.reference_id',$contest_ids);
        }

        $this->db->where('U.added_date>',$rookie_date);

        $result = $this->db->get()->row_array();

        //echo $this->db->last_query();die('***');echo $this->db->last_query();die('***');
        
        return $result;
        
    }
}