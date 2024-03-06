<?php

class Fixed_open_predictor_model extends MY_Model {

    public function __construct() {
		parent::__construct();
		$this->db_prediction		= $this->load->database('fixed_open_predictor_db', TRUE);
    }

	function get_active_category()
	{
		$result = $this->db_prediction->select('C.*')
        ->from(PREDICTION_MASTER.' PM')
        ->join(CATEGORY.' C','C.category_id=PM.category_id')
		->where('PM.status',0)
		->group_by('PM.category_id')
		->get()
		->result_array();


		return $result;

	}

	/**
     * [get_prediction_participants description]
     * @uses :- get participants
     * @param Number prediction master id,user_id
     */
	public function get_prediction_participants($prediction_master_id,$user_id="")
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['page_size']))
		{
			$limit = $post['page_size'];
		}

		$page = 0;
		if(isset($post['page_no'])) {
			$page = $post['page_no']-1;
		}

         $offset	= $limit * $page;
		$pre_query ="(SELECT GROUP_CONCAT(UP.user_id) as user_ids,PO.prediction_option_id
FROM ".$this->db_prediction->dbprefix(PREDICTION_MASTER)." PM 
INNER JOIN ".$this->db_prediction->dbprefix(PREDICTION_OPTION)." PO ON PM.prediction_master_id=PO.prediction_master_id 
INNER JOIN ".$this->db_prediction->dbprefix(USER_PREDICTION)." UP ON UP.prediction_option_id=PO.prediction_option_id  
GROUP BY PO.prediction_option_id)";

		 $this->db_prediction->select("PM.prediction_master_id,UP.user_id,PO.`option`",FALSE)
		->from(PREDICTION_MASTER.' PM')
		->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
		->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
		->join($pre_query.' TR',"TR.prediction_option_id=UP.prediction_option_id")
		->where("PO.prediction_master_id",$prediction_master_id);
		
		if(!empty($user_id))
		{
			$this->db_prediction->where('UP.user_id',$user_id);
		}
		else if($this->user_id)
		{
			$this->db_prediction->where('UP.user_id<>',$this->user_id);
		}
		
		$this->db_prediction->group_by('UP.user_id')
		->order_by("PO.prediction_option_id","ASC");

		if(empty($user_id))
		{
			$this->db_prediction->limit($limit,$offset);
		}
		$sql = $this->db_prediction->get();
		$prediction_data	= $sql->result_array();

		return array(
		 'other_list' => $prediction_data,
		);
	}

	


	function get_fixed_prediction_categories()
	{
		$this->db_prediction->select("C.*",FALSE)
		->from(PREDICTION_MASTER.' PM')
		->join(CATEGORY.' C',"C.category_id=PM.category_id")
		->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
		->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
		->where("PM.status",2)
		->group_by('PM.category_id');

		$this->db_prediction->order_by("PM.category_id","ASC");	 
		 $sql = $this->db_prediction->get();
		$data	= $sql->result_array();
 
		//echo $this->db_prediction->last_query();die();
		return $data;
	}

	public function get_fixed_leaderboard_top_three($category_id='')
	{
		$post = $this->input->post();
		$this->db_prediction->select("UP.user_id,PO.`option`,
		SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) as
correct_answer,COUNT(PM.prediction_master_id) as attempts,
(RANK() OVER (ORDER BY SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0)) DESC,
MAX(IF(PM.prediction_master_id and PO.is_correct=1, UP.added_date, NULL)) ASC,
MAX(UP.added_date) ASC,
UP.user_prediction_id DESC
)) AS rank_value",FALSE)
	   ->from(PREDICTION_MASTER.' PM')
	   ->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
	   ->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
	   ->where("PM.status",2)
	   ->group_by('UP.user_id');

	   if(!empty($category_id))
	   {
		   $this->db_prediction->where('PM.category_id',$category_id);
	   }

	   $this->db_prediction->where("PM.deadline_date>=",$post['from_date'])
			->where("PM.deadline_date<=",$post['to_date']);

			$this->db_prediction->limit(3,0);

		$sql = $this->db_prediction->get();
	    return $prediction_data	= $sql->result_array();

	}

	public function get_fixed_prediction_leaderboard($category_id='',$user_id='')
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['page_size']))
		{
			$limit = $post['page_size'];
		}

		$page = 0;
		if(isset($post['page_no'])) {
			$page = $post['page_no']-1;
		}

         $offset	= $limit * $page;
		$this->db_prediction->select("UP.user_id,PO.`option`,
		SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) as
correct_answer,COUNT(PM.prediction_master_id) as attempts,
(RANK() OVER (ORDER BY SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0)) DESC,
MAX(IF(PM.prediction_master_id and PO.is_correct=1, UP.added_date, NULL)) ASC,
MAX(UP.added_date) ASC,
UP.user_prediction_id DESC
)) AS rank_value",FALSE)
	   ->from(PREDICTION_MASTER.' PM')
	   ->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
	   ->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
	   ->where("PM.status<>",4)
	   ->group_by('UP.user_id');

	   if(!empty($category_id))
	   {
		   $this->db_prediction->where('PM.category_id',$category_id);
	   }

	   if(!empty($user_id))
	   {
		   $this->db_prediction->order_by("FIELD ( UP.user_id, ".$this->user_id." ) DESC");	  
		   //$this->db_prediction->where('UP.user_id<>',$this->user_id);
	   }
		

	   if(!empty($post['from_date']) && !empty($post['to_date']))
	   {
			$this->db_prediction->where("PM.deadline_date>=",$post['from_date'])
			->where("PM.deadline_date<=",$post['to_date']);
	
	   }

	   $this->db_prediction->order_by("rank_value","ASC");

		if(empty($user_id))
		{
			if($offset > 0)
			{
				$this->db_prediction->limit($limit,$offset);
			}
			else
			{
				$this->db_prediction->limit($limit,3);
			}
		}
		else{
			$this->db_prediction->limit(1,0);

		}
	
		$sql = $this->db_prediction->get();
	   $prediction_data	= $sql->result_array();

	//    if(!empty($user_id))
	//    {	
	// 	   echo $this->db_prediction->last_query();die();
	// 	 //log_message('error', $this->db_prediction->last_query());
	// 	}
	   $total = 0;
	   
	   //print_r($prediction_data);
	   return array(
		'other_list' => $prediction_data,
		'total' => $total
	   );
	}

	public function get_day_leaderboard($day_number='',$day_date,$user_id='')
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['page_size']))
		{
			$limit = $post['page_size'];
		}

		$page = 0;
		if(isset($post['page_no'])) {
			$page = $post['page_no']-1;
		}

         $offset	= $limit * $page;
		$this->db_prediction->select("leaderboard_day_id,
		prize_distribution_history_id,
		user_id,
		rank_value,
		is_winner,
		prize_data,
		correct_answer,
		attempts,
		day_number,
		day_date",FALSE)
	   ->from(LEADERBOARD_DAY)
	   ->where('day_number',$day_number)
	   ->where('day_date',$day_date);
	   
	   if(!empty($user_id))
	   {
		   $this->db_prediction->where('user_id',$user_id);	  
		   //$this->db_prediction->where('UP.user_id<>',$this->user_id);
		}
		else{

			$this->db_prediction->where('rank_value>',3);	  
		}
		

	  
	   $this->db_prediction->order_by("rank_value","ASC");

		if(empty($user_id))
		{
			$this->db_prediction->limit($limit,$offset);
		}
		else{
			$this->db_prediction->limit(1,0);

		}
	
		$sql = $this->db_prediction->get();
	   $prediction_data	= $sql->result_array();
	   $total = 0;
	   //echo $this->db_prediction->last_query();die('dfd');
	   //print_r($prediction_data);
	   return array(
		'other_list' => $prediction_data,
		'total' => $total
	   );
	}

	function get_day_top_three($day_number='',$day_date)
	{
		$this->db_prediction->select("leaderboard_day_id,
		prize_distribution_history_id,
		user_id,
		rank_value,
		is_winner,
		prize_data,
		correct_answer,
		attempts,
		day_number,
		day_date",FALSE)
	   ->from(LEADERBOARD_DAY)
	   ->where('day_number',$day_number)
	   ->where('day_date',$day_date);

	   $this->db_prediction->limit(3,0);

	   $this->db_prediction->order_by("rank_value","ASC");
	   $sql = $this->db_prediction->get();
	   $prediction_data	= $sql->result_array();

	   return $prediction_data;
	}

	function get_week_top_three($week_number='',$week_date)
	{
		$this->db_prediction->select("leaderboard_week_id,
		prize_distribution_history_id,
		user_id,
		rank_value,
		is_winner,
		prize_data,
		correct_answer,
		attempts,
		week_number,
		week_start_date",FALSE)
	   ->from(LEADERBOARD_WEEK)
	   ->where('week_number',$week_number)
	   ->where('week_start_date',$week_date);

	   $this->db_prediction->limit(3,0);

	   $this->db_prediction->order_by("rank_value","ASC");
	   $sql = $this->db_prediction->get();
	   $prediction_data	= $sql->result_array();

	   return $prediction_data;
	}

	function get_month_top_three($month_number='',$month_date)
	{
		$this->db_prediction->select("leaderboard_month_id,
		prize_distribution_history_id,
		user_id,
		rank_value,
		is_winner,
		prize_data,
		correct_answer,
		attempts,
		month_number,
		month_start_date",FALSE)
	   ->from(LEADERBOARD_MONTH)
	   ->where('month_number',$month_number)
	   ->where('month_start_date',$month_date);

	   $this->db_prediction->limit(3,0);

	   $this->db_prediction->order_by("rank_value","ASC");
	   $sql = $this->db_prediction->get();
	   $prediction_data	= $sql->result_array();

	   return $prediction_data;
	}

	public function get_week_leaderboard($week_number='',$week_date,$user_id='')
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['page_size']))
		{
			$limit = $post['page_size'];
		}

		$page = 0;
		if(isset($post['page_no'])) {
			$page = $post['page_no']-1;
		}

         $offset	= $limit * $page;
		$this->db_prediction->select("leaderboard_week_id,
		prize_distribution_history_id,
		user_id,
		rank_value,
		is_winner,
		prize_data,
		correct_answer,
		attempts,
		week_number,
		week_start_date",FALSE)
	   ->from(LEADERBOARD_WEEK)
	   ->where('week_number',$week_number)
	   ->where('week_start_date',$week_date);
	   
	   if(!empty($user_id))
	   {
		   $this->db_prediction->where('user_id',$user_id);	  
		   //$this->db_prediction->where('UP.user_id<>',$this->user_id);
		}
		else{

			$this->db_prediction->where('rank_value>',3);	  
		}
		

	  
	   $this->db_prediction->order_by("rank_value","ASC");

		if(empty($user_id))
		{
			$this->db_prediction->limit($limit,$offset);
		}
		else{
			$this->db_prediction->limit(1,0);

		}
	
		$sql = $this->db_prediction->get();
	   $prediction_data	= $sql->result_array();
	   $total = 0;
	   //echo $this->db_prediction->last_query();die('dfd');
	   //print_r($prediction_data);
	   return array(
		'other_list' => $prediction_data,
		'total' => $total
	   );
	}

	public function get_month_leaderboard($month_number='',$month_date,$user_id='')
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['page_size']))
		{
			$limit = $post['page_size'];
		}

		$page = 0;
		if(isset($post['page_no'])) {
			$page = $post['page_no']-1;
		}

         $offset	= $limit * $page;
		$this->db_prediction->select("leaderboard_month_id,
		prize_distribution_history_id,
		user_id,
		rank_value,
		is_winner,
		prize_data,
		correct_answer,
		attempts,
		month_number,
		month_start_date",FALSE)
	   ->from(LEADERBOARD_MONTH)
	   ->where('month_number',$month_number)
	   ->where('month_start_date',$month_date);
	   
	   if(!empty($user_id))
	   {
		   $this->db_prediction->where('user_id',$user_id);	  
		   //$this->db_prediction->where('UP.user_id<>',$this->user_id);
		}
		else{

			$this->db_prediction->where('rank_value>',3);	  
		}
		

	  
	   $this->db_prediction->order_by("rank_value","ASC");

		if(empty($user_id))
		{
			$this->db_prediction->limit($limit,$offset);
		}
		else{
			$this->db_prediction->limit(1,0);

		}
	
		$sql = $this->db_prediction->get();
	   $prediction_data	= $sql->result_array();
	   $total = 0;
	   //echo $this->db_prediction->last_query();die('dfd');
	   //print_r($prediction_data);
	   return array(
		'other_list' => $prediction_data,
		'total' => $total
	   );
	}

	public function prediction_sponsors()
	{
		$result = $this->db_prediction->select("*")
		->from(PREDICTION_PRIZE)
		->get()->result_array();

		foreach($result as &$row)
		{
			$row['prize_distribution_detail'] = json_decode($row['prize_distribution_detail'],TRUE); 
		}
		return $result;
	}

	public function get_leaderboard_status($prize_category,$date)
	{
		$result = $this->db_prediction->select("PDH.status")
		->from(PREDICTION_PRIZE.' P')
		->join(PRIZE_DISTRIBUTION_HISTORY.' PDH','PDH.prediction_prize_id=P.prediction_prize_id')
		->where('P.prize_category',$prize_category)
		->where('PDH.prize_date',$date)
		->get()->row_array();
		return $result;
	}

	public function get_user_predicted($prediction_master_id)
	{
		/**
		 * var sql_query=`SELECT PM.* 
                FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
                INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
                INNER JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id
                WHERE PM.prediction_master_id=`+req.body.prediction_master_id+` AND UP.user_id=`+req.body.currect_user_id+` GROUP BY PM.prediction_master_id`;
		 * 
		 * 
		 * 
		*/
	  return $result = $this->db_prediction->select("PM.*")
		->from(PREDICTION_MASTER.' PM')
	   ->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
	   ->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
	   ->where("PM.prediction_master_id",$prediction_master_id)
	   ->where("UP.user_id",$this->user_id)
	   ->group_by('PM.prediction_master_id')->get()->row_array();



	}

	public function make_user_prediction($save_data)
	{
		$this->db_prediction->insert(USER_PREDICTION,$save_data);
		return $this->db_prediction->insert_id();
	}

	public function update_prediction_master($prediction_master_id)
	{
		/**
		 *   var prediction_master_sql = `UPDATE `+CONSTANTS.PREDICTION_MASTER+ ` SET total_user_joined=total_user_joined+1   WHERE prediction_master_id =`+req.body.prediction_master_id;
        console.log('logsql:',prediction_master_sql);
		 * ***/
		$this->db_prediction->set('total_user_joined', 'total_user_joined + 1', FALSE);
		$this->db_prediction->where('prediction_master_id', $prediction_master_id);
        $this->db_prediction->update(PREDICTION_MASTER);
        return $this->db_prediction->affected_rows(); 
	}

}