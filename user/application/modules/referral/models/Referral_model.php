<?php
class Referral_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_referral_data($user_id, $offset = 0, $limit = 10) {
        $sql = " SELECT R.refferal_id, R.user_id , R.friend_id, R.status, R.date, R.user_bonus_cash, R.friend_bonus_cash, 
		IFNULL(RU.first_name,'') as first_name,IFNULL(RU.last_name,'') as last_name,0 AS invested,R.friend_email as email,IFNULL(RU.user_name,R.friend_email) as user_name,
				CASE  R.status 
					WHEN '1' THEN 'Active' 
					WHEN '2' THEN 'Complete' 
					ELSE 'Pending' 
				END   AS status_str
				FROM " . $this->db->dbprefix(REFFERAL) . " AS R
				INNER JOIN " . $this->db->dbprefix(USER) . " AS U ON U.user_id = R.user_id
				LEFT JOIN " . $this->db->dbprefix(USER) . " AS RU ON RU.user_id = R.friend_id
				WHERE R.user_id  = $user_id
				ORDER BY  R.date DESC
				LIMIT $offset ,$limit ";

        $output = $this->db->query($sql)->result_array();

        return $output;
    }

    public function get_invested_amonunt($lineup_master_contest_ids, $user_id) {

        return $this->db->select("user_id,source,sum(real_amount) as deposit_invest,sum(winning_amount) as winning_invest,sum(bonus_amount) as bonus_invest, sum(real_amount)+sum(winning_amount) as total_real_invest ")
                        ->from(ORDER . " O")
                        ->where_in($lineup_master_contest_ids)
                        ->where("user_id", $user_id)
                        ->where("source", 1)
                        ->where("status", 1)
                        ->where("type", 1)
                        ->group_by("user_id")
                        ->get()
                        ->limit(1)
                        ->row_array();
    }

    public function get_refferal_graph_data($post) {
        $resultTotal = $this->db->select("COUNT(refferal_id) as total_reff,", FALSE)
                ->from(REFFERAL)
                ->where("user_id", $post["user_id"])
                //->where("status",0)
                ->get()
                ->limit(1)
                ->row_array();

        $total_reff = $resultTotal['total_reff'];
        $result = $this->db->select("(COUNT(CASE WHEN status = 0 THEN 1 END)*100)/$total_reff as total_pending,
			(COUNT(CASE WHEN status = 1 THEN 1 END)*100)/$total_reff as total_registered, (COUNT(CASE WHEN status = 2 THEN 1 END)*100)/$total_reff as total_deposit_initiated,SUM(friend_bonus_cash) as investments,SUM(user_bonus_cash) as earnings", FALSE)
                ->from(REFFERAL)
                ->where("user_id", $post["user_id"])
                //->where("status",0)
                ->get()
                ->limit(1)
                ->row_array();

        $result['total_refferal'] = $total_reff;
        //echo $this->db->last_query();die;
        return $result;
    }

    public function get_invited_emails($emails) {
        $result = $this->db->select("friend_email as email")
                ->from(REFFERAL)
                ->where("user_id", $this->user_id)
                ->where_in("friend_email", $emails)
                ->where("status", 0)
                ->get()
                ->result_array();

        if (!empty($result)) {
            $result = array_column($result, "email");
        } else {
            $result = array();
        }

        return $result;
    }
    
    public function check_invited_emails($emails) {
        $result = $this->db->select("user_id,email")
                ->from(USER)
                ->where_in("email", $emails)
                ->get()
                ->result_array();

        return $result;
    }
}
