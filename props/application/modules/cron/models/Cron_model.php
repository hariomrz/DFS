<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron_model extends MY_Model {

    public $sp_table = array("7"=>STATS_CRICKET,"5"=>STATS_SOCCER);
    public function __construct() 
    {
       	parent::__construct();
        $this->user_db = $this->load->database('user_db', TRUE);
    }

    function __destruct() {
        $this->user_db->close();
    }

    /**
    * Used for update fixture score in user teams
    * @param int $collection_master_id
    * @return boolean
    */
    public function update_team_score()
    {
        $current_date = format_date();
        //Update match wise team correct picks data
        $sql = "UPDATE ".$this->db->dbprefix(USER_TEAM)."  AS UT 
                INNER JOIN(
                    SELECT L.user_team_id,COUNT(L.lineup_id) as total_pick,SUM(CASE L.status WHEN 1 THEN 1 ELSE 0 END) as total_correct,SUM(CASE L.status WHEN 0 THEN 1 ELSE 0 END) as total_pending,count(S.season_id) as season_cnt,SUM(CASE S.status WHEN 2 THEN 1 WHEN 4 THEN 1 ELSE 0 END) as match_status
                    FROM ".$this->db->dbprefix(USER_TEAM)." AS UT 
                    INNER JOIN ".$this->db->dbprefix(LINEUP)." AS L ON L.user_team_id=UT.user_team_id 
                    INNER JOIN ".$this->db->dbprefix(SEASON_PROPS)." AS SP ON SP.season_prop_id=L.season_prop_id 
                    INNER JOIN ".$this->db->dbprefix(SEASON)." AS S ON S.season_id=SP.season_id 
                    WHERE UT.status=0 GROUP BY L.user_team_id
                  ) AS LP ON LP.user_team_id = UT.user_team_id 
                  SET UT.total_pick = LP.total_pick,UT.total_correct = LP.total_correct,UT.status=(IF(LP.total_pending=0,1,0))
                  WHERE UT.status=0 and LP.season_cnt=LP.match_status";
        $this->db->query($sql);
        return true;
    }

    public function prize_distribution()
    {
        $result = $this->db->select('   
                    UT.user_team_id,UT.user_id,UT.currency_type,UT.team_name,UT.payout_type,UT.entry_fee,UT.total_pick,UT.total_correct,MP.points
                                ')
                            ->from(USER_TEAM. " UT")
                            ->join(MASTER_PAYOUT. " MP","UT.payout_type=MP.payout_type AND UT.total_pick=MP.picks AND UT.total_pick=MP.correct")
                            ->where('UT.status',1)
                            ->where('UT.fee_refund',0)
                            ->get()->result_array();

        //echo '<pre>';print_r($result);die;

        if(!empty($result)){
            foreach ($result as $key => $value) {
                $update =[];
                $update['status'] = 2;
                if($value['total_pick'] == $value['total_correct']){
                    $payout = $value['entry_fee'] * $value['points'];    

                    $update['winning'] = $payout;
                    $update['is_winner'] = 1;

                    $winning = array();
                    $winning['bonus_amount'] =$winning['points']=$winning['real_amount']=$winning['winning_amount']=0;
                    $winning["source_id"]    = $value['user_team_id'];   
                    $winning["user_id"]      = $value['user_id'];   
                    $winning["reference_id"] = $value['user_team_id'];
                    $winning["custom_data"]  = array('team_name'=>$value['team_name']);
                    $winning['real_amount'] = 0;
                    

                    if($value['currency_type'] == 0){
                        $winning['bonus_amount'] = $payout;
                    }
                    if($value['currency_type'] == 1){
                        $winning['winning_amount'] = $payout;  
                    }
                    if($value['currency_type'] == 2){
                        $winning['points'] = ceil($payout);
                        $update['winning'] = ceil($payout);
                    }
                    $winning["source"]      =  PICKS_WON_SOURCE;
                    $winning["reason"]      = 'for Winning props team';
                   
                    $this->load->model("user/User_model");
                    $this->User_model->credit_user_amount($winning);



                }

                $this->db->where('user_team_id',$value['user_team_id']);
                $this->db->update(USER_TEAM,$update);
                
                
            }
        }else{
            echo 'No team for prize distibution.';


        }
        return true;
    }

    /**
     * Prize notification
     * @param void
     * @return boolean
     */
    public function prize_notification()
    {
        $result = $this->db->select('UT.sports_id,UT.user_id,UT.user_team_id,UT.entry_fee,UT.currency_type,UT.team_name,UT.winning,MIN(S.scheduled_date) as start_date,MAX(S.scheduled_date) as end_date')
                        ->from(USER_TEAM. " UT")
                        ->join(LINEUP." L","L.user_team_id=UT.user_team_id")
                        ->join(SEASON_PROPS." SP","SP.season_prop_id=L.season_prop_id")
                        ->join(SEASON." S","S.season_id=SP.season_id")
                        ->where('UT.status',2)
                        ->where('UT.is_winner',1)
                        ->where('UT.is_win_notify',0)
                        ->get()->result_array();
       
        if(!empty($result[0]['user_team_id'])){
            foreach ($result as  $res) {
                
                $pre_query ="(SELECT user_id,GROUP_CONCAT(device_id ORDER BY keys_id DESC) as device_ids,GROUP_CONCAT(device_type ORDER BY keys_id DESC) as device_types ,keys_id,device_id,device_type FROM ".$this->db->dbprefix(ACTIVE_LOGIN)."  WHERE user_id =". $res['user_id']." AND device_id IS NOT NULL ORDER BY keys_id DESC)";

                $sql = $this->user_db->select("O.order_id,O.real_amount, O.bonus_amount, O.winning_amount,O.points, U.email,U.user_name,O.source_id,O.prize_image,O.custom_data,AL.device_id,AL.device_type,AL.device_ids,AL.device_types")
                        ->from(ORDER . " O")
                        ->join(USER . " U", "U.user_id = O.user_id", "INNER")
                        ->join($pre_query.' AL','AL.user_id=U.user_id','LEFT')
                        ->where("O.user_id", $res['user_id'])
                        ->where("O.source", PICKS_WON_SOURCE)
                        ->where("O.source_id", $res['user_team_id'])
                        ->get();
                $order_info = $sql->row_array();
                //echo '<pre>';print_r($order_info);die;

                if(!empty($order_info)){
                    $notify_data = [];
                    $notify_data['notification_type'] = PICKS_WON_NOTIFY; //554-GameWon
                    $notify_data['notification_destination'] = 7; //web, email
                    $notify_data["source_id"] = $order_info['source_id'];
                    $notify_data["user_id"] = $res['user_id'];
                    $notify_data["to"] = $order_info['email'];
                    $notify_data["user_name"] = $order_info['user_name'];
                    $notify_data["added_date"] = format_date();
                    $notify_data["modified_date"] = format_date();
                    $notify_data["subject"] = "Wohoo! You just WON!";
                    $notify_data['device_details']=array();
 
                    if($res['currency_type'] == 2){
                        $res['winning'] = (int)$res['winning'];
                    }
                    $contest = array();
                    $content['team_name'] = $res['team_name'];
                    $content['currency_type'] = $res['currency_type'];
                    $content['entry_fee'] = $res['entry_fee'];
                    $content['user_team_id'] = $res['user_team_id'];
                    $content["start_date"] = $res['start_date'];
                    $content["end_date"] = $res['end_date'];
                    $content["winning"] = $res['winning'];
                    $content["sports_id"] = $res['sports_id'];
                    $notify_data["content"] = json_encode($content);
                    //echo '<pre>';print_r($notify_data);die;
                    $this->load->model('user/User_nosql_model');
                    $this->User_nosql_model->send_notification($notify_data); 

                    $this->db->where('user_team_id', $res['user_team_id']);
                    $this->db->update(USER_TEAM, array('is_win_notify' => '1','modified_date' => format_date()));

                }
            }
        }
    }
	public function get_match_list($sports_id,$status=0) {
		$current_date = format_date();
 		$this->db->select("S.season_id,S.season_game_uid,S.scheduled_date,S.is_published,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,IFNULL(L.display_name,L.league_name) as league_name,S.status",FALSE)
			->from(SEASON." AS S")
			->join(LEAGUE." L", "L.league_id = S.league_id", "INNER")
			->join(TEAM." T1", "T1.team_id = S.home_id AND T1.sports_id=L.sports_id", "INNER")
			->join(TEAM." T2", "T2.team_id = S.away_id AND T2.sports_id=L.sports_id", "INNER")
			->where("S.is_published","1")
            ->where("L.sports_id",$sports_id)
			->group_by("S.season_id");
        if(isset($status) && $status == "1"){
            $table = $this->sp_table[$sports_id];
            $this->db->join($table." as SC","SC.season_id=S.season_id");
            $this->db->where("S.scheduled_date < ",$current_date);
            $this->db->order_by("S.scheduled_date","DESC");
        }else{
            $this->db->where("S.scheduled_date > ",$current_date);
            $this->db->order_by("S.scheduled_date","ASC");
        }
		$match_list = $this->db->get()->result_array();
		//echo "<pre>";print_r($match_list);die;
		return $match_list;
	}

	public function get_match_detail($season_id){
 		$this->db->select("S.season_id,S.season_game_uid,S.scheduled_date,S.is_published,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,IFNULL(L.display_name,L.league_name) as league_name,L.sports_id",FALSE)
			->from(SEASON." AS S")
			->join(LEAGUE." L", "L.league_id = S.league_id", "INNER")
			->join(TEAM." T1", "T1.team_id = S.home_id AND T1.sports_id=L.sports_id", "INNER")
			->join(TEAM." T2", "T2.team_id = S.away_id AND T2.sports_id=L.sports_id", "INNER")
			->where("S.season_id",$season_id)
			->order_by("S.scheduled_date","ASC");
		$result = $this->db->get()->row_array();
		//echo "<pre>";print_r($match_list);die;
		return $result;
	}

    public function get_completed_match_props($post_data) {
        $table = $this->sp_table[$post_data['sports_id']];
        $this->db->select("SP.season_id,SP.season_prop_id,SP.player_id,SP.prop_id,SP.team_id,SP.position,SP.points,P.full_name,P.display_name,IFNULL(P.image,IFNULL(T.jersey,T.feed_jersey)) as jersey,IFNULL(T.display_abbr,T.team_abbr) as team,".$post_data['fields'],FALSE)
            ->from(SEASON_PROPS." AS SP")
            ->join(PLAYER." AS P", "P.player_id = SP.player_id", "INNER")
            ->join(TEAM." AS T", "T.team_id = SP.team_id", "INNER")
            ->join($table." as SC","SC.season_id=SP.season_id AND SC.player_id=SP.player_id","LEFT")
            ->where("SP.status",1)
            ->where("SP.season_id",$post_data['season_id'])
            ->order_by("SP.season_prop_id")
            ->order_by("P.display_name","ASC");
    
        $result = $this->db->get()->result_array();
        return $result;
    }

	public function get_match_props($season_id) {
 		$this->db->select("SP.season_id,SP.season_prop_id,SP.player_id,SP.prop_id,SP.team_id,SP.position,SP.points,P.full_name,P.display_name,IFNULL(P.image,IFNULL(T.jersey,T.feed_jersey)) as jersey,IFNULL(T.display_abbr,T.team_abbr) as team",FALSE)
			->from(SEASON_PROPS." AS SP")
            ->join(PLAYER." AS P", "P.player_id = SP.player_id", "INNER")
			->join(TEAM." AS T", "T.team_id = SP.team_id", "INNER")
			->where("SP.status",1)
			->where("SP.season_id",$season_id)
			->order_by("P.display_name","ASC");
	
		$result = $this->db->get()->result_array();
		return $result;
	}

    public function get_sports_match_props($sports_id) {
        $current_date = format_date();
        $this->db->select("S.season_id,S.scheduled_date,S.home_id,S.away_id,SP.season_prop_id,SP.player_id,SP.prop_id,SP.team_id,SP.position,SP.points,P.full_name,P.display_name,IFNULL(P.image,'') as player_image,IFNULL(T1.jersey,T1.feed_jersey) as home_jersey,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.jersey,T2.feed_jersey) as away_jersey,IFNULL(T2.display_abbr,T2.team_abbr) as away",FALSE)
            ->from(SEASON." AS S")
            ->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER")
            ->join(SEASON_PROPS." AS SP", "SP.season_id = S.season_id", "INNER")
            ->join(PLAYER." AS P", "P.player_id = SP.player_id", "INNER")
            ->join(TEAM." AS T1", "T1.team_id = S.home_id", "INNER")
            ->join(TEAM." AS T2", "T2.team_id = S.away_id", "INNER")
            ->where("SP.status",1)
            ->where("L.sports_id",$sports_id)
            ->where("S.scheduled_date > ",$current_date)
            ->group_by("SP.season_prop_id")
            ->order_by("S.scheduled_date","ASC")
            ->order_by("P.display_name","ASC");
    
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function get_user_teams($post_data) {
        $this->db->select("UT.user_team_id,UT.user_name,UT.team_name,UT.payout_type,UT.currency_type,UT.entry_fee,UT.total_pick,UT.total_correct,UT.is_winner,UT.winning,UT.status,UT.added_date,GROUP_CONCAT(P.display_name) as players,MIN(S.scheduled_date) as scheduled_date,COUNT(L.lineup_id) as l_picks",FALSE)
            ->from(USER_TEAM." AS UT")
            ->join(LINEUP." AS L", "L.user_team_id = UT.user_team_id", "INNER")
            ->join(SEASON_PROPS." AS SP", "SP.season_prop_id = L.season_prop_id", "INNER")
            ->join(PLAYER." AS P", "P.player_id = SP.player_id", "INNER")
            ->join(SEASON." AS S", "S.season_id = SP.season_id", "INNER")
            ->where("SP.status",1)
            ->where("UT.user_id",$post_data['user_id'])
            ->where("P.sports_id",$post_data['sports_id'])
            ->group_by("UT.user_team_id")
            ->order_by("UT.user_team_id","DESC");
        if(isset($post_data['season_id']) && $post_data['season_id'] != ""){
            $this->db->where("SP.season_id",$post_data['season_id']);
            $this->db->having("l_picks = total_pick");
        }
        $result = $this->db->get()->result_array();
        return $result;
    }

	public function save_team($post_data) {
		//echo "<pre>";print_r($post_data);die;
		try {
            //Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();

            $sp_ids = array_column($post_data['pl'],"pid");
            $sp_ids = implode(",",$sp_ids);
            $sp_data = $this->get_all_table_data("season_prop_id,points",SEASON_PROPS,array("season_prop_id IN(".$sp_ids.")"=>NULL));
            if(!empty($sp_data)){
                $sp_data = array_column($sp_data,"points","season_prop_id");
            }
            //echo "<pre>";print_r($sp_data);die;
            $lm = array();
            $lm['user_id'] = $post_data["user_id"];
            $lm['user_name'] = $post_data["user_name"];
            $lm['team_name'] = $post_data["team_name"];
            $lm['payout_type'] = $post_data["payout_type"];
            $lm['currency_type'] = $post_data["currency_type"];
            $lm['entry_fee'] = $post_data["entry_fee"];
            $lm['total_pick'] = count($post_data['pl']);
            $lm['added_date'] = format_date();
            $lm['modified_date'] = format_date();
            $this->db->insert(USER_TEAM,$lm);
            $user_team_id = $this->db->insert_id();
            if($user_team_id){
            	foreach($post_data['pl'] as $prow){
                    $value = isset($sp_data[$prow['pid']]) ? $sp_data[$prow['pid']] : 0;
            		$tmp_arr = array();
            		$tmp_arr['user_team_id'] = $user_team_id;
            		$tmp_arr['season_prop_id'] = $prow['pid'];
            		$tmp_arr['type'] = $prow['type'];
            		$tmp_arr['value'] = $value;
            		$tmp_arr['added_date'] = format_date();
            		$this->db->insert(LINEUP,$tmp_arr);
            	}

            	$this->db->trans_complete();
                if ($this->db->trans_status() === FALSE )
                {
                    $this->db->trans_rollback();
                    return array();
                }
                else
                {
                    $this->db->trans_commit();
                    return array("user_team_id" => $user_team_id);
                }
            } else {
                $this->db->trans_rollback();
                return array();
            }
        }catch(Exception $e){
            $this->db->trans_rollback();
            return array();
        }
	}
        /**
         * Get ALl email template list
         */
        public function get_email_template_list(){

        $result = $this->user_db->select("template_name,template_path,notification_type,status,subject")
                    ->from(EMAIL_TEMPLATE)
                    ->where("status",1)
                    ->get()
                    ->result_array();

        return $result;
    }

    public function get_playing_upcoming_match($sports_id = '')
   {
       $current_time = format_date();
       $interval = 60;//minutes
       $this->db->select("S.season_game_uid,L.sports_id")
                        ->from(SEASON." AS S")
                        ->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER")
                        ->where("L.status","1")
                        ->where("S.playing_announce","0")
                        ->where("S.scheduled_date > DATE_SUB('{$current_time}', INTERVAL S.delay_minute MINUTE)")
                        ->where("S.scheduled_date <= DATE_ADD('{$current_time}', INTERVAL ".$interval." MINUTE)");

       if(!empty($sports_id))
       {
           $this->db->where("L.sports_id", $sports_id);
       }
       $this->db->group_by("S.season_game_uid");
       $sql = $this->db->get();
       $result = $sql->result_array();
       return $result;
   }

}
