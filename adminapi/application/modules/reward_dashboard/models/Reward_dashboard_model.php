<?php
/**
 * This model is used for getting and storing quiz related information
 * @package    Quiz_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Reward_dashboard_model extends MY_Model {
   
   
    function __construct() {
        parent::__construct();
    }

    function get_source_wise_data($post)
    {
       
        $this->db->select("O.source,SUM(O.points) as coins,SUM(O.bonus_amount) as bonus,SUM(O.real_amount) as cash, 
        GROUP_CONCAT(DISTINCT IF(O.points > 0,O.user_id,'')) as coin_user_ids,
        GROUP_CONCAT(DISTINCT IF(O.bonus_amount > 0,O.user_id,'')) as bonus_user_ids,
        GROUP_CONCAT(DISTINCT IF(O.real_amount > 0,O.user_id,'')) as cash_user_ids,
        MS.name",FALSE)
        ->from(ORDER.' O')
        ->join(MASTER_SOURCE.' MS','O.source=MS.source',"LEFT" )
        ->where('O.type',0)
        ->where("(O.points>0 OR O.bonus_amount>0 OR O.real_amount>0)");

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'].' 00:00:00';
            $to_date = $post['to_date'].' 23:59:59';
            $this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."'");
        }

        $result = $this->db->group_by('O.source')
        ->get()->result_array();

        //echo $this->db->last_query();die;

        return $result;



    }

}