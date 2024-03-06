<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once 'Cron_model.php';
class Fixed_open_predictor_model extends Cron_model {
    
    public $db_user ;
    public $db_fantasy ;
    public $testingNode = FALSE;

    public function __construct() 
    {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
		$this->db_prediction	= $this->load->database('db_fixed_open_predictor', TRUE);
    }

    public function update_day_rank($current_date='')
    {
		$post =  array();
		if(empty($current_date))
		{
			$current_date = format_date();
		}
        $from_date = date('Y-m-d',strtotime($current_date)).' 00:00:00';
        $to_date = date('Y-m-d',strtotime($current_date)).' 23:59:59';

		$day_number = date("z",strtotime($from_date))+1;
		$day_date = date("Y-m-d",strtotime($from_date));

		$day_prize =$this->db_prediction->select('*')->from(PREDICTION_PRIZE)
		->where('prize_category',1)
		->where('status',1)
		->get()->row_array();

		if(empty($day_prize['prediction_prize_id']))
		{
			return false;
		}

		//check entry in prize_distribution history
		$prize_distribution_history =$this->db_prediction->select('*')->from(PRIZE_DISTRIBUTION_HISTORY)
		->where('prediction_prize_id',$day_prize['prediction_prize_id'])
		->where('prize_date',$day_date)
		->where('status',0)
		->get()->row_array();

		if(empty($prize_distribution_history))
		{
			//insert
			$prize_distribution_history = array();
			$prize_distribution_history['prediction_prize_id'] = $day_prize['prediction_prize_id'];
			$prize_distribution_history['name'] ="Day ".$day_number;
			$prize_distribution_history['prize_date'] =$day_date;
			$this->db_prediction->insert(PRIZE_DISTRIBUTION_HISTORY,$prize_distribution_history);
			$prize_distribution_history['prize_distribution_history_id']=$this->db_prediction->insert_id();

		}

    $prize_distribution_history_id = $prize_distribution_history['prize_distribution_history_id'];
    //PM.status=2 AND
		$sql ="SELECT UP.user_id,COUNT(DISTINCT PM.prediction_master_id) as attempts,
		SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) as
correct_answer,(RANK() OVER (ORDER BY SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) DESC,
MAX(IF(PM.prediction_master_id and PO.is_correct=1, UP.added_date, NULL)) ASC,
MAX(IF(PM.prediction_master_id, UP.added_date, NULL)) ASC,
UP.user_prediction_id DESC
)) AS rank_value,$day_number as day_number,'{$day_date}' as day_date,
'{$prize_distribution_history_id}' as prize_distribution_history_id
				FROM ".$this->db_prediction->dbprefix(PREDICTION_MASTER)." PM
				INNER JOIN 	".$this->db_prediction->dbprefix(PREDICTION_OPTION)." PO ON PO.prediction_master_id=PM.prediction_master_id
				INNER JOIN ".$this->db_prediction->dbprefix(USER_PREDICTION)." UP ON UP.prediction_option_id=PO.prediction_option_id
        WHERE 
         PM.deadline_date>='{$from_date}'
        AND PM.deadline_date<='{$to_date}'
        AND PM.status <> 4
				GROUP BY UP.user_id
				ORDER BY rank_value ASC
				";

		$insert_sql= "REPLACE INTO ".$this->db_prediction->dbprefix(LEADERBOARD_DAY)."(
			user_id,
			attempts,
			correct_answer,
			rank_value,
			day_number,
			day_date,
			prize_distribution_history_id
			)
			$sql";	
			
			$this->db_prediction->query($insert_sql);

	  // echo $insert_sql;die('dfd');
	  //exit();
	}
	
	public function update_week_rank($current_date='')
    {
		$post =  array();
		if(empty($current_date))
		{
			$current_date = format_date();
		}
		$date = date('Y-m-d',strtotime($current_date));
		list($from_date, $to_date) = x_week_range($date);
		$week_number = date("W",strtotime($from_date));
		$week_start_date = $from_date;
		$week_end_date = $to_date;
		
		$week_prize =$this->db_prediction->select('*')->from(PREDICTION_PRIZE)
		->where('prize_category',2)
		->where('status',1)
		->get()->row_array();
		
		if(empty($week_prize['prediction_prize_id']))
		{
			return false;
		}
	
		//check entry in prize_distribution history
		$prize_distribution_history =$this->db_prediction->select('*')->from(PRIZE_DISTRIBUTION_HISTORY)
		->where('prediction_prize_id',$week_prize['prediction_prize_id'])
		->where('prize_date',$from_date)
		->where('status',0)
		->get()->row_array();

		if(empty($prize_distribution_history))
		{
			//insert
			$prize_distribution_history = array();
			$prize_distribution_history['prediction_prize_id'] = $week_prize['prediction_prize_id'];
			$prize_distribution_history['name'] ="Week ".$week_number;
			$prize_distribution_history['prize_date'] =$from_date;
			$this->db_prediction->insert(PRIZE_DISTRIBUTION_HISTORY,$prize_distribution_history);
			$prize_distribution_history['prize_distribution_history_id']=$this->db_prediction->insert_id();

		}

		$prize_distribution_history_id = $prize_distribution_history['prize_distribution_history_id'];

//PM.status=2 AND
		$sql ="SELECT UP.user_id,COUNT(DISTINCT PM.prediction_master_id) as attempts,
		SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) as
correct_answer,(RANK() OVER (ORDER BY SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) DESC,
MAX(IF(PM.prediction_master_id and PO.is_correct=1, UP.added_date, NULL)) ASC,
MAX(IF(PM.prediction_master_id, UP.added_date, NULL)) ASC,
UP.user_prediction_id DESC
)) AS rank_value,$week_number as week_number,'{$week_start_date}' as week_start_date,'{$week_end_date}' as week_end_date,
$prize_distribution_history_id as prize_distribution_history_id
				FROM ".$this->db_prediction->dbprefix(PREDICTION_MASTER)." PM
				INNER JOIN 	".$this->db_prediction->dbprefix(PREDICTION_OPTION)." PO ON PO.prediction_master_id=PM.prediction_master_id
				INNER JOIN ".$this->db_prediction->dbprefix(USER_PREDICTION)." UP ON UP.prediction_option_id=PO.prediction_option_id
				WHERE  PM.deadline_date>='{$from_date}'
        AND PM.deadline_date<='{$to_date}'
        AND PM.status <> 4
				GROUP BY UP.user_id
				ORDER BY rank_value ASC
				";

		$insert_sql= "REPLACE INTO ".$this->db_prediction->dbprefix(LEADERBOARD_WEEK)."(
			user_id,
			attempts,
			correct_answer,
			rank_value,
			week_number,
			week_start_date,
			week_end_date,
			prize_distribution_history_id
			)
			$sql";	
			
			$this->db_prediction->query($insert_sql);
			//exit();

	}
	
	public function update_month_rank($current_date='')
    {
		$post =  array();
		if(empty($current_date))
		{
			$current_date = format_date();
		}
		$from_date = date('Y-m-01',strtotime($current_date)).' 00:00:00';
		$to_date = date('Y-m-t',strtotime($current_date)).' 23:59:59';
		
		$month_number = date("m",strtotime($from_date));
		$month_start_date = $from_date;
		$month_end_date = $to_date;

		$month_prize =$this->db_prediction->select('*')->from(PREDICTION_PRIZE)
		->where('prize_category',3)
		->where('status',1)
		->get()->row_array();
		
		if(empty($month_prize['prediction_prize_id']))
		{
			return false;
		}
	
		//check entry in prize_distribution history
		$prize_distribution_history =$this->db_prediction->select('*')->from(PRIZE_DISTRIBUTION_HISTORY)
		->where('prediction_prize_id',$month_prize['prediction_prize_id'])
		->where('prize_date',$from_date)
		->where('status',0)
		->get()->row_array();

		if(empty($prize_distribution_history))
		{
			//insert
			$prize_distribution_history = array();
			$prize_distribution_history['prediction_prize_id'] = $month_prize['prediction_prize_id'];
			$prize_distribution_history['name'] ="Month ".$month_number;
			$prize_distribution_history['prize_date'] =$from_date;
			$this->db_prediction->insert(PRIZE_DISTRIBUTION_HISTORY,$prize_distribution_history);
			$prize_distribution_history['prize_distribution_history_id']=$this->db_prediction->insert_id();

		}

		$prize_distribution_history_id = $prize_distribution_history['prize_distribution_history_id'];

    //PM.status=2 AND
		$sql ="SELECT UP.user_id,COUNT(DISTINCT PM.prediction_master_id) as attempts,
		SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) as
correct_answer,(RANK() OVER (ORDER BY SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) DESC,
MAX(IF(PM.prediction_master_id and PO.is_correct=1, UP.added_date, NULL)) ASC,
MAX(IF(PM.prediction_master_id, UP.added_date, NULL)) ASC,
UP.user_prediction_id DESC
)) AS rank_value,$month_number as month_number,'{$month_start_date}' as month_start_date,'{$month_end_date}' as month_end_date,
$prize_distribution_history_id as prize_distribution_history_id
				FROM ".$this->db_prediction->dbprefix(PREDICTION_MASTER)." PM
				INNER JOIN 	".$this->db_prediction->dbprefix(PREDICTION_OPTION)." PO ON PO.prediction_master_id=PM.prediction_master_id
				INNER JOIN ".$this->db_prediction->dbprefix(USER_PREDICTION)." UP ON UP.prediction_option_id=PO.prediction_option_id
				WHERE PM.deadline_date>='{$from_date}'
        AND PM.deadline_date<='{$to_date}'
        AND PM.status <> 4
				GROUP BY UP.user_id
				ORDER BY rank_value ASC
				";

		$insert_sql= "REPLACE INTO ".$this->db_prediction->dbprefix(LEADERBOARD_MONTH)."(
			user_id,
			attempts,
			correct_answer,
			rank_value,
			month_number,
			month_start_date,
			month_end_date,
			prize_distribution_history_id
			)
			$sql";	
			
			$this->db_prediction->query($insert_sql);

			
    }

    /**
   * function used for daily prediction prize distribution
   * @param void
   * @return boolean
   */
  public function daily_prediction_prize_distribute()
  {
    $current_date = format_date();
    $yesterday_date = date('Y-m-d', strtotime($current_date. ' -1 day'));
    $prize = $this->db_prediction->select('PP.*,PDH.prize_distribution_history_id', FALSE)
                ->from(PREDICTION_PRIZE.' AS PP')
                ->join(PRIZE_DISTRIBUTION_HISTORY." AS PDH", "PDH.prediction_prize_id=PP.prediction_prize_id", "INNER")
                ->where('PP.prize_category', 1)
                ->where('PP.status', 1)
                ->where('PP.allow_prize', 1)
                ->where("JSON_EXTRACT(PP.prize_distribution_detail, '$[0]') is not null")
                //->where("DATE_FORMAT(PDH.prize_date, '%Y-%m-%d')='".$yesterday_date."'",NULL)
                ->where('PDH.status', 2)
                ->order_by('PDH.prize_date','ASC')
                ->limit(1)
                ->get()
                ->row_array();
    //echo "=====<pre>";print_r($prize);die;
    if(!empty($prize)){
      if (empty($prize['prize_distribution_detail']))
      {
        return true;
      }

      $wining_amount = (array) json_decode($prize['prize_distribution_detail'], TRUE);
      $wining_max = array_column($wining_amount, 'max');
      $winner_places = max($wining_max);
      if(empty($winner_places) || $winner_places == NULL || $winner_places == 0){
          return true;
      }

      $winning_amount_arr = array();
      if(!empty($wining_amount)) 
      {
        foreach($wining_amount as $win_amt) 
        {
          for($i=$win_amt['min']; $i<=$win_amt['max']; $i++)
          {
            if($win_amt['prize_type']==3)
            {
              $image = "";
              $mname = "";
              if(isset($win_amt['amount'])){
                $mname = $win_amt['amount'];
              }
              $winning_amount_arr[$i-1] = array("prize_type"=>$win_amt['prize_type'],"name"=>$mname,"image"=>$image);
            }
            else
            {   
              $winning_amount_arr[$i-1] = array("prize_type"=>$win_amt['prize_type'],"amount"=>$win_amt['amount']);
            }
          }
        }
      }
      //echo "<pre>";print_r($winning_amount_arr);die;
      //get winners
      $winners = $this->db_prediction->select('LD.*', FALSE)
              ->from(LEADERBOARD_DAY.' AS LD')
              ->where('LD.prize_distribution_history_id', $prize['prize_distribution_history_id'])
              ->where('LD.correct_answer > ', 0)
              ->where("DATE_FORMAT(LD.day_date, '%Y-%m-%d')='".$yesterday_date."'",NULL)
              ->order_by("LD.rank_value","ASC")
              ->limit($winner_places)
              ->get()
              ->result_array();
      //echo "<pre>";print_r($winners);die;
      $is_success = 0;            
      if(!empty($winners)){
        foreach($winners as $key=>$winner){
          if(isset($winning_amount_arr[$key])){
            $prize_obj = $winning_amount_arr[$key];
            $real_amount = $bonus_amount = $winning_amount = $points = 0;
            if($prize_obj['prize_type'] == 0){
              $bonus_amount = $prize_obj['amount'];
            }else if($prize_obj['prize_type'] == 1){
              $winning_amount = $prize_obj['amount'];
            }else if($prize_obj['prize_type'] == 2){
              $prize_obj['amount'] = ceil($prize_obj['amount']);
              $points = $prize_obj['amount'];
            }
            $prize_obj_tmp = array();
            $prize_obj_tmp[] = $prize_obj;
            $tmp_arr = array();
            $tmp_arr['user_id'] = $winner['user_id'];
            $tmp_arr['source'] = 225;
            $tmp_arr['source_id'] = $winner['leaderboard_day_id'];
            $tmp_arr['season_type'] = 1;
            $tmp_arr['status'] = 1;
            $tmp_arr['real_amount'] = $real_amount;
            $tmp_arr['bonus_amount'] = $bonus_amount;
            $tmp_arr['winning_amount'] = $winning_amount;
            $tmp_arr['points'] = ceil($points);
            $tmp_arr['custom_data'] = json_encode($prize_obj_tmp);
            $tmp_arr['plateform'] = PLATEFORM_FANTASY;
            //echo "<pre>";print_r($tmp_arr);die;
            $result = $this->winning_deposit($tmp_arr);
            if($result){
              $is_success = 1;
              //leaderboard table update is_winner 1
              $this->db_prediction->where(array('leaderboard_day_id' => $winner['leaderboard_day_id']));
              $this->db_prediction->update(LEADERBOARD_DAY, array('is_winner' => '1','prize_data' => json_encode($prize_obj_tmp)));
            }
          }
        }
      }

      if(empty($winners) || $is_success == 1){
        //prize history table status update
        $this->db_prediction->where('prize_distribution_history_id', $prize['prize_distribution_history_id']);
        $this->db_prediction->update(PRIZE_DISTRIBUTION_HISTORY, array('status' => '3'));
      }

    }
    return true;
  }

  /**
   * function used for weekly prediction prize distribution
   * @param void
   * @return boolean
   */
  public function weekly_prediction_prize_distribute()
  {
    $current_date = format_date();
    $previous_week = strtotime("-1 week +1 day",strtotime($current_date));
    $start_week = strtotime("last monday midnight",$previous_week);
    $week_date = date("Y-m-d",$start_week);
    $prize = $this->db_prediction->select('PP.*,PDH.prize_distribution_history_id', FALSE)
                ->from(PREDICTION_PRIZE.' AS PP')
                ->join(PRIZE_DISTRIBUTION_HISTORY." AS PDH", "PDH.prediction_prize_id=PP.prediction_prize_id", "INNER")
                ->where('PP.prize_category', 2)
                ->where('PP.status', 1)
                ->where('PP.allow_prize', 1)
                ->where("JSON_EXTRACT(PP.prize_distribution_detail, '$[0]') is not null")
                //->where("DATE_FORMAT(PDH.prize_date, '%Y-%m-%d')='".$week_date."'",NULL)
                ->where('PDH.status', 2)
                ->order_by('PDH.prize_date','ASC')
                ->limit(1)
                ->get()
                ->row_array();
    //echo "=====<pre>";print_r($prize);die;
    if(!empty($prize)){
      if (empty($prize['prize_distribution_detail']))
      {
        return true;
      }

      $wining_amount = (array) json_decode($prize['prize_distribution_detail'], TRUE);
      $wining_max = array_column($wining_amount, 'max');
      $winner_places = max($wining_max);
      if(empty($winner_places) || $winner_places == NULL || $winner_places == 0){
          return true;
      }

      $winning_amount_arr = array();
      if(!empty($wining_amount)) 
      {
        foreach($wining_amount as $win_amt) 
        {
          for($i=$win_amt['min']; $i<=$win_amt['max']; $i++)
          {
            if($win_amt['prize_type']==3)
            {
              $image = "";
              $mname = "";
              if(isset($win_amt['amount'])){
                $mname = $win_amt['amount'];
              }
              $winning_amount_arr[$i-1] = array("prize_type"=>$win_amt['prize_type'],"name"=>$mname,"image"=>$image);
            }
            else
            {   
              $winning_amount_arr[$i-1] = array("prize_type"=>$win_amt['prize_type'],"amount"=>$win_amt['amount']);
            }
          }
        }
      }
      //echo "<pre>";print_r($winning_amount_arr);die;
      //get winners
      $winners = $this->db_prediction->select('LW.*', FALSE)
              ->from(LEADERBOARD_WEEK.' AS LW')
              ->where('LW.prize_distribution_history_id', $prize['prize_distribution_history_id'])
              ->where('LW.correct_answer > ', 0)
              ->where("DATE_FORMAT(LW.week_start_date, '%Y-%m-%d')='".$week_date."'",NULL)
              ->order_by("LW.rank_value","ASC")
              ->limit($winner_places)
              ->get()
              ->result_array();
      //echo "<pre>";print_r($winners);die;
      $is_success = 0;            
      if(!empty($winners)){
        foreach($winners as $key=>$winner){
          if(isset($winning_amount_arr[$key])){
            $prize_obj = $winning_amount_arr[$key];
            $real_amount = $bonus_amount = $winning_amount = $points = 0;
            if($prize_obj['prize_type'] == 0){
              $bonus_amount = $prize_obj['amount'];
            }else if($prize_obj['prize_type'] == 1){
              $winning_amount = $prize_obj['amount'];
            }else if($prize_obj['prize_type'] == 2){
              $prize_obj['amount'] = ceil($prize_obj['amount']);
              $points = $prize_obj['amount'];
            }
            $prize_obj_tmp = array();
            $prize_obj_tmp[] = $prize_obj;
            $tmp_arr = array();
            $tmp_arr['user_id'] = $winner['user_id'];
            $tmp_arr['source'] = 226;
            $tmp_arr['source_id'] = $winner['leaderboard_week_id'];
            $tmp_arr['season_type'] = 1;
            $tmp_arr['status'] = 1;
            $tmp_arr['real_amount'] = $real_amount;
            $tmp_arr['bonus_amount'] = $bonus_amount;
            $tmp_arr['winning_amount'] = $winning_amount;
            $tmp_arr['points'] = ceil($points);
            $tmp_arr['custom_data'] = json_encode($prize_obj_tmp);
            $tmp_arr['plateform'] = PLATEFORM_FANTASY;
            //echo "<pre>";print_r($tmp_arr);die;
            $result = $this->winning_deposit($tmp_arr);
            if($result){
              $is_success = 1;
              //leaderboard table update is_winner 1
              $this->db_prediction->where(array('leaderboard_week_id' => $winner['leaderboard_week_id']));
              $this->db_prediction->update(LEADERBOARD_WEEK, array('is_winner' => '1','prize_data' => json_encode($prize_obj_tmp)));
            }
          }
        }
      }

      if(empty($winners) || $is_success == 1){
        //prize history table status update
        $this->db_prediction->where('prize_distribution_history_id', $prize['prize_distribution_history_id']);
        $this->db_prediction->update(PRIZE_DISTRIBUTION_HISTORY, array('status' => '3'));
      }

    }
    return true;
  }

  /**
   * function used for monthly prediction prize distribution
   * @param void
   * @return boolean
   */
  public function monthly_prediction_prize_distribute()
  {
    $current_date = format_date();
    $previous_month = strtotime("-1 month",strtotime($current_date));
    $month_date = date('Y-m-01', $previous_month);
    $prize = $this->db_prediction->select('PP.*,PDH.prize_distribution_history_id', FALSE)
                ->from(PREDICTION_PRIZE.' AS PP')
                ->join(PRIZE_DISTRIBUTION_HISTORY." AS PDH", "PDH.prediction_prize_id=PP.prediction_prize_id", "INNER")
                ->where('PP.prize_category', 3)
                ->where('PP.status', 1)
                ->where('PP.allow_prize', 1)
                ->where("JSON_EXTRACT(PP.prize_distribution_detail, '$[0]') is not null")
                //->where("DATE_FORMAT(PDH.prize_date, '%Y-%m-%d')='".$month_date."'",NULL)
                ->where('PDH.status', 2)
                ->order_by('PDH.prize_date','ASC')
                ->limit(1)
                ->get()
                ->row_array();
    //echo "=====<pre>";print_r($prize);die;
    if(!empty($prize)){
      if (empty($prize['prize_distribution_detail']))
      {
        return true;
      }

      $wining_amount = (array) json_decode($prize['prize_distribution_detail'], TRUE);
      $wining_max = array_column($wining_amount, 'max');
      $winner_places = max($wining_max);
      if(empty($winner_places) || $winner_places == NULL || $winner_places == 0){
          return true;
      }

      $winning_amount_arr = array();
      if(!empty($wining_amount)) 
      {
        foreach($wining_amount as $win_amt) 
        {
          for($i=$win_amt['min']; $i<=$win_amt['max']; $i++)
          {
            if($win_amt['prize_type']==3)
            {
              $image = "";
              $mname = "";
              if(isset($win_amt['amount'])){
                $mname = $win_amt['amount'];
              }
              $winning_amount_arr[$i-1] = array("prize_type"=>$win_amt['prize_type'],"name"=>$mname,"image"=>$image);
            }
            else
            {   
              $winning_amount_arr[$i-1] = array("prize_type"=>$win_amt['prize_type'],"amount"=>$win_amt['amount']);
            }
          }
        }
      }
      //echo "<pre>";print_r($winning_amount_arr);die;
      //get winners
      $winners = $this->db_prediction->select('LM.*', FALSE)
              ->from(LEADERBOARD_MONTH.' AS LM')
              ->where('LM.prize_distribution_history_id', $prize['prize_distribution_history_id'])
              ->where('LM.correct_answer > ', 0)
              ->where("DATE_FORMAT(LM.month_start_date, '%Y-%m-%d')='".$month_date."'",NULL)
              ->order_by("LM.rank_value","ASC")
              ->limit($winner_places)
              ->get()
              ->result_array();
      //echo "<pre>";print_r($winners);die;
      $is_success = 0;            
      if(!empty($winners)){
        foreach($winners as $key=>$winner){
          if(isset($winning_amount_arr[$key])){
            $prize_obj = $winning_amount_arr[$key];
            $real_amount = $bonus_amount = $winning_amount = $points = 0;
            if($prize_obj['prize_type'] == 0){
              $bonus_amount = $prize_obj['amount'];
            }else if($prize_obj['prize_type'] == 1){
              $winning_amount = $prize_obj['amount'];
            }else if($prize_obj['prize_type'] == 2){
              $prize_obj['amount'] = ceil($prize_obj['amount']);
              $points = $prize_obj['amount'];
            }
            $prize_obj_tmp = array();
            $prize_obj_tmp[] = $prize_obj;
            $tmp_arr = array();
            $tmp_arr['user_id'] = $winner['user_id'];
            $tmp_arr['source'] = 227;
            $tmp_arr['source_id'] = $winner['leaderboard_month_id'];
            $tmp_arr['season_type'] = 1;
            $tmp_arr['status'] = 1;
            $tmp_arr['real_amount'] = $real_amount;
            $tmp_arr['bonus_amount'] = $bonus_amount;
            $tmp_arr['winning_amount'] = $winning_amount;
            $tmp_arr['points'] = ceil($points);
            $tmp_arr['custom_data'] = json_encode($prize_obj_tmp);
            $tmp_arr['plateform'] = PLATEFORM_FANTASY;
            //echo "<pre>";print_r($tmp_arr);die;
            $result = $this->winning_deposit($tmp_arr);
            if($result){
              $is_success = 1;
              //leaderboard table update is_winner 1
              $this->db_prediction->where(array('leaderboard_month_id' => $winner['leaderboard_month_id']));
              $this->db_prediction->update(LEADERBOARD_MONTH, array('is_winner' => '1','prize_data' => json_encode($prize_obj_tmp)));
            }
          }
        }
      }

      if(empty($winners) || $is_success == 1){
        //prize history table status update
        $this->db_prediction->where('prize_distribution_history_id', $prize['prize_distribution_history_id']);
        $this->db_prediction->update(PRIZE_DISTRIBUTION_HISTORY, array('status' => '3'));
      }

    }
    return true;
  }

	/**
	* Function used for winning deposit
	* @param array $input_data
	*/
	public function winning_deposit($input_data) 
	{
		try
		{   
		    $this->db = $this->db_user;
		    $order_info = $this->get_single_row('order_id', ORDER, array('user_id' => $input_data['user_id'], 'source' => $input_data['source'], 'source_id' => $input_data['source_id'], "season_type" => $input_data['season_type']));
		    // If prize is not alloted to user for the selected lineup contest 
		    if(empty($order_info))
		    {
		      $orderData                   = array();
		      $orderData["user_id"]        = $input_data['user_id'];
		      $orderData["source"]         = $input_data['source'];
		      $orderData["source_id"]      = $input_data['source_id'];
		      $orderData["season_type"]    = $input_data['season_type'];
		      $orderData["type"]           = 0;
		      $orderData["status"]         = $input_data['status'];
		      $orderData["real_amount"]    = $input_data['real_amount'];
		      $orderData["bonus_amount"]   = $input_data['bonus_amount'];
		      $orderData["winning_amount"] = $input_data['winning_amount'];
		      $orderData["points"] = $input_data['points'];
		      $orderData["custom_data"] = isset($input_data['custom_data']) ? $input_data['custom_data'] : array();
		      $orderData["plateform"]      = $input_data['plateform'];
		      $orderData["date_added"]     = format_date();
		      $orderData["modified_date"]  = format_date();

		      $orderData['order_unique_id'] = $this->_generate_order_key();
		      $this->db_user->insert(ORDER, $orderData);
		      $order_id = $this->db_user->insert_id();
		      if (!$order_id) 
		      {            
		          return false;
		      }
		      // Update User balance for order with completed status .
		      if(($input_data['real_amount'] > 0 || $input_data['bonus_amount'] > 0 || $input_data['winning_amount'] > 0 || $input_data['points'] > 0) && $orderData["status"] == 1){
		        $this->update_user_balance($orderData["user_id"], $orderData, "add");
		      }
		      return $order_id;
		    }
		} catch (Exception $e)
		{
		    //echo 'Caught exception: ',  $e->getMessage(), "\n";
		} 
	}

  	/**
     * Function to Update user balance
     *  Params: $user_id,$real_balance,$bonus_balance
     *  
     */
    function update_user_balance($user_id,$balance_arr,$oprator='add')
    {
        if(empty($balance_arr)){
            return false;
        }
        if(isset($balance_arr['real_amount']) && $balance_arr['real_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('balance', 'balance - '.$balance_arr['real_amount'], FALSE);
            }else{
                $this->db_user->set('balance', 'balance + '.$balance_arr['real_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "7" && $oprator == 'add'){
                $this->db->set('total_deposit', 'total_deposit + '.$balance_arr['real_amount'], FALSE);
            }
        }
        if(isset($balance_arr['bonus_amount']) && $balance_arr['bonus_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('bonus_balance', 'bonus_balance - '.$balance_arr['bonus_amount'], FALSE);
            }else{
                $this->db_user->set('bonus_balance', 'bonus_balance + '.$balance_arr['bonus_amount'], FALSE);
            }

            $this->load->helper('queue_helper');
            $bonus_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_bonus' => $balance_arr['bonus_amount'], 'bonus_date' => format_date("today", "Y-m-d"));
            add_data_in_queue($bonus_data, 'user_bonus');
        }
        if(isset($balance_arr['winning_amount']) && $balance_arr['winning_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('winning_balance', 'winning_balance - '.$balance_arr['winning_amount'], FALSE);
            }else{
                $this->db_user->set('winning_balance', 'winning_balance + '.$balance_arr['winning_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "3" && $oprator == 'add'){
                $this->db_user->set('total_winning', 'total_winning + '.$balance_arr['winning_amount'], FALSE);
            }
        }
        if(isset($balance_arr['points']) && $balance_arr['points'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('point_balance', 'point_balance - '.$balance_arr['points'], FALSE);
            }else{
                $this->db_user->set('point_balance', 'point_balance + '.$balance_arr['points'], FALSE);
            }
            $this->load->helper('queue_helper');
            $coin_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_coins' => $balance_arr['points'], 'bonus_date' => format_date("today", "Y-m-d"));
            add_data_in_queue($coin_data, 'user_coins');
        }
        $this->db_user->where('user_id', $user_id);
        $this->db_user->update(USER);
        return $this->db_user->affected_rows();  
    }

  /**
   * function used for send mini-league winning notification
   * @param void
   * @return boolean
   */
  public function fixed_prediction_prize_notification() 
  {
    $daily_source = 225;
    $week_source = 226;
    $month_source = 227;
    $result = $this->db_prediction->select('PP.*,PDH.prize_distribution_history_id', FALSE)
                ->from(PREDICTION_PRIZE.' AS PP')
                ->join(PRIZE_DISTRIBUTION_HISTORY." AS PDH", "PDH.prediction_prize_id=PP.prediction_prize_id", "INNER")
                ->where('PDH.status', 3)
                ->where('PDH.is_win_notify', 0)
                ->limit(1)
                ->get()
                ->result_array();
    //echo "<pre>";print_r($result);die;
    if (!empty($result)) 
    {   
      foreach ($result as $res) 
      {
        $is_success = 0;
        $table_name = LEADERBOARD_DAY;
        $primary_key = "leaderboard_day_id";
        $source = $daily_source;
        if(isset($res['prize_category']) && $res['prize_category'] == 2){
          $table_name = LEADERBOARD_WEEK;
          $primary_key = "leaderboard_week_id";
          $source = $week_source;
        }else if(isset($res['prize_category']) && $res['prize_category'] == 3){
          $table_name = LEADERBOARD_MONTH;
          $primary_key = "leaderboard_month_id";
          $source = $month_source;
        }
        $winners = $this->db_prediction->select('TBL.*,TBL.'.$primary_key.' AS leaderboard_id', FALSE)
              ->from($table_name.' AS TBL')
              ->where("TBL.is_winner", 1)
              ->where('TBL.prize_distribution_history_id', $res['prize_distribution_history_id'])
              ->order_by("TBL.".$primary_key,"ASC")
              ->get()
              ->result_array();
        //echo "<pre>";print_r($winners);die;
        foreach($winners as $winner){
          $sql = $this->db_user->select("O.order_id,O.user_id,O.source,O.source_id,O.real_amount,O.bonus_amount,O.winning_amount,O.points,O.custom_data,U.first_name,U.email,U.user_name")
                ->from(ORDER . " O")
                ->join(USER . " U", "U.user_id = O.user_id", "INNER")
                ->where("O.user_id", $winner['user_id'])
                ->where("O.source", $source)
                ->where("O.source_id", $winner['leaderboard_id'])
                ->get();
          $order_info = $sql->row_array();
          if (!empty($order_info)) 
          {
            $order_info['custom_data'] = json_decode($order_info['custom_data'],TRUE);
            if(!empty($order_info))
            {
              $is_success = 1;
              /* Send Notification */
              $notify_data = array();
              $notify_data['notification_type'] = $source;//mini-league winning
              $notify_data['notification_destination'] = 7; //web, push, email
              $notify_data["source_id"] = $order_info['source_id'];
              $notify_data["user_id"] = $order_info['user_id'];
              $notify_data["to"] = $order_info['email'];
              $notify_data["user_name"] = !empty($order_info['user_name']) ? $order_info['user_name'] : $order_info['email'];
              $notify_data["added_date"] = date("Y-m-d H:i:s");
              $notify_data["modified_date"] = date("Y-m-d H:i:s");
              $notify_data["subject"] = "Prediction Leaderboard Winnings";
              $content = array(
                            'user_id' => $order_info['user_id'],
                            'order_id' => $order_info['order_id'],
                            'source' => $order_info['source'],
                            'source_id' => $order_info['source_id'],
                            'prize_name' => $res['name'],
                            'leaderboard_id' => $winner['leaderboard_id'],
                            'rank_value' => $winner['rank_value'],
                            'custom_data' => $order_info['custom_data'],
                            'start_date' => "",
                            'end_date' => ""
                          );
              if($source == $daily_source){
                $content['start_date'] = $winner['day_date'];
              }else if($source == $week_source){
                $content['start_date'] = $winner['week_start_date'];
                $content['end_date'] = $winner['week_end_date'];
              }else if($source == $month_source){
                $content['start_date'] = $winner['month_start_date'];
                $content['end_date'] = $winner['month_end_date'];
              }
              $notify_data["content"] = json_encode($content);
              //echo "<pre>";print_r($notify_data);die;
              $this->load->model('notification/Notify_nosql_model');
              $this->Notify_nosql_model->send_notification($notify_data);
            }
          }
        }
        if($is_success == 1){
          $this->db_prediction->where('prize_distribution_history_id', $res['prize_distribution_history_id']);
          $this->db_prediction->update(PRIZE_DISTRIBUTION_HISTORY, array('is_win_notify' => '1'));
        }
      }
    }
    return;
  }

  function update_day_prize_status($data)
  {
      if(empty($data['deadline_date']))
      {
        return false;
      }

      $deadline_date = date('Y-m-d',strtotime($data['deadline_date']));
      $current_date = format_date();
      if(strtotime($current_date) < strtotime($deadline_date.' 23:59:59'))
      {
         return false;
      }
      //get prediction count with 0,1, and 3 status
      $open_predictions = $this->db_prediction->select("COUNT(prediction_master_id) as open_predictions")
      ->from(PREDICTION_MASTER)
      ->where('DATE_FORMAT(deadline_date,"%Y-%m-%d")',$deadline_date)
      ->where_in('status',array(0,1,3))
      ->get()->row_array();

      if(!empty($open_predictions) && $open_predictions['open_predictions'] > 0 )
      {
        return false;
      }

      if(isset($open_predictions['open_predictions']) && $open_predictions['open_predictions'] == 0)
      {
        $this->db_prediction->where('prize_date',$deadline_date.' 00:00:00');
        $this->db_prediction->where('prediction_prize_id',1);
        $this->db_prediction->update(PRIZE_DISTRIBUTION_HISTORY,array('status' => 2));
      }
  }

  function update_week_prize_status($data)
  {
      if(empty($data['deadline_date']))
      {
        return false;
      }

      $deadline_date = date('Y-m-d',strtotime($data['deadline_date']));
      list($from_date, $to_date) = x_week_range($deadline_date);
      $week_number = date("W",strtotime($from_date));
      $week_start_date = $from_date;
      $week_end_date = $to_date;
      $current_date = format_date();
      
      if(strtotime($current_date) < strtotime($week_end_date))
      {
        return;
      }

      // echo '<pre>';
      // echo strtotime($current_date).'*****';
      // echo $current_date;
      // echo '****'.strtotime($week_end_date);

      // var_dump(strtotime($current_date) < strtotime($week_end_date.' 23:59:59'));
      // echo $week_end_date;die('dfd');
      //get prediction count with 0,1, and 3 status
      $open_predictions = $this->db_prediction->select("COUNT(prediction_master_id) as open_predictions")
      ->from(PREDICTION_MASTER)
      ->where("deadline_date>=",$week_start_date)
			->where("deadline_date<=",$week_end_date)
      ->where_in('status',array(0,1,3))
      ->get()->row_array(); 

      if(!empty($open_predictions) && $open_predictions['open_predictions'] > 0 )
      {
        return false;
      }

      if(isset($open_predictions['open_predictions']) && $open_predictions['open_predictions'] == 0)
      {
        $this->db_prediction->where('prize_date',$deadline_date.' 00:00:00');
        $this->db_prediction->where('prediction_prize_id',2);
        $this->db_prediction->update(PRIZE_DISTRIBUTION_HISTORY,array('status' => 2));
      }
  }

  function update_month_prize_status($data)
  {
      if(empty($data['deadline_date']))
      {
        return false;
      }

      $deadline_date = date('Y-m-d',strtotime($data['deadline_date']));
      $from_date = date('Y-m-01',strtotime($deadline_date)).' 00:00:00';
      $to_date = date('Y-m-t',strtotime($deadline_date)).' 23:59:59';
      
      $month_number = date("m",strtotime($from_date));
      $month_start_date = $from_date;
      $month_end_date = $to_date;

      $current_date = format_date();

      if(strtotime($current_date) < strtotime($month_end_date))
      {
         return false;
      }
      
      //get prediction count with 0,1, and 3 status
      $open_predictions = $this->db_prediction->select("COUNT(prediction_master_id) as open_predictions")
      ->from(PREDICTION_MASTER)
      ->where("deadline_date>=",$month_start_date)
			->where("deadline_date<=",$month_end_date)
      ->where_in('status',array(0,1,3))
      ->get()->row_array();

      if(!empty($open_predictions) && $open_predictions['open_predictions'] > 0 )
      {
        return false;
      }

      if(isset($open_predictions['open_predictions']) && $open_predictions['open_predictions'] == 0)
      {
        $this->db_prediction->where('prize_date',$deadline_date.' 00:00:00');
        $this->db_prediction->where('prediction_prize_id',3);
        $this->db_prediction->update(PRIZE_DISTRIBUTION_HISTORY,array('status' => 2));
      }
  }

  function get_one_prize_history_to_process($prediction_prize_id=1)
  {
     return $this->db_prediction->select('prize_date')
    ->from(PRIZE_DISTRIBUTION_HISTORY)
    ->where('prediction_prize_id',$prediction_prize_id )
    ->where('status',0)
    ->order_by('prize_date','ASC')
    ->get()->row_array();
  }


}