<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Private_contest_model extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
		$this->db_user    = $this->load->database('db_user', TRUE);
  }

  public function get_dashboard_data($post, $real_cash_only=FALSE)
  {
      $result = $this->db_fantasy->select('C.contest_id, C.contest_unique_id, C.league_id, C.contest_name, C.contest_description, C.season_scheduled_date, C.minimum_size, C.size, C.total_user_joined, C.multiple_lineup, C.entry_fee, C.site_rake, C.host_rake, C.prize_pool, C.prize_type, C.user_id')
         ->from(CONTEST.' as C')
         ->where('C.contest_access_type',1)
         ->where('C.status', 3);
      if ($real_cash_only === TRUE)
      {
          $this->db_fantasy->where('C.currency_type',1);
      }
      if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
      {
          $this->db_fantasy->where("DATE_FORMAT(C.added_date, '%Y-%m-%d %H:%i:%s') >= '".$post['from_date']."' and DATE_FORMAT(C.added_date, '%Y-%m-%d %H:%i:%s') <= '".$post['to_date']."' ");
      }

         $result = $this->db_fantasy->get()->result_array();
        //  if ($real_cash_only === TRUE) echo $this->db_fantasy->last_query();
         return $result;
  }

  public function get_top_creators_data()
  {
      $result = $this->db_fantasy->select('COUNT(C.contest_id) as total_contest_created, C.user_id')
                ->from(CONTEST.' as C')
                ->where('C.user_id !=', 0)
                ->where('C.contest_access_type',1)
                ->where('C.currency_type',1)
                ->where('C.status', 3) // prize-distributed
                ->group_by('C.user_id')
                ->order_by('total_contest_created', 'DESC')
                ->limit(5)
                ->get();
      $result_array = $result->result_array();
      return $result_array;
  }

  public function get_contest_commission($user_id)
  {
      $contest_data = $this->db_fantasy->select('C.contest_id, C.user_id, C.host_rake, C.entry_fee, C.total_user_joined, concat((C.entry_fee * C.host_rake)/100) * C.total_user_joined as commission')
                     ->from(CONTEST.' as C')
                     ->where('C.contest_access_type',1)
                     ->where('C.currency_type',1)
                     ->where('C.user_id', $user_id)
                     ->where('C.status', 3) // prize-distributed
                     ->get()->result_array();

      return $contest_data;
  }

  public function get_username($user_id)
  {
      $result = $this->db->select('U.user_name')
                ->from(USER.' as U')
                ->where("user_id",$user_id);
      $result = $this->db->get()->row_array();

      return $result['user_name'];
  }

  public function get_contests_by_dates($date,$start_time)
  {
      $start_date = $date.' '.$start_time;
      $end_date = date('Y-m-d H:i:s',strtotime($start_date.' +23 hours 59 minutes 59 seconds'));

      $this->db_fantasy->select('COUNT(C.contest_id) as total_contests')
          ->from(CONTEST.' as C')
          ->where('C.contest_access_type',1)
          ->where("DATE_FORMAT(C.added_date, '%Y-%m-%d %H:%i:%s') >= '".$start_date."' and DATE_FORMAT(C.added_date, '%Y-%m-%d %H:%i:%s')<= '".$end_date."'")
          ->where('C.status != ', 1);
      $result = $this->db_fantasy->get()->row_array();
    //   echo $this->db_fantasy->last_query();die;
      return $result;
  }

  public function update_private_contest_visibility($visibility)
  {
      $this->db->where('key_name', 'allow_private_contest')->update(APP_CONFIG,array('key_value'=> $visibility));
  }

  public function update_site_rake($rake)
  {
      $this->db->where('key_name', 'site_rake')->update(APP_CONFIG,array('key_value'=> $rake));
  }

  public function update_host_rake($rake)
  {
      $this->db->where('key_name', 'host_rake')->update(APP_CONFIG,array('key_value'=> $rake));
  }

}
?>