<?php
class Lobby_model extends MY_Model {
    public function __construct() {
        parent::__construct();
    }

    public function get_sports_match_props($sports_id) {
        $current_date = format_date();
        $this->db->select("S.season_id,S.scheduled_date,S.home_id,S.away_id,SP.season_prop_id,SP.player_id,SP.prop_id,SP.team_id,SP.position,SP.points,P.full_name,P.display_name,IFNULL(P.image,'') as player_image,IFNULL(T1.jersey,T1.feed_jersey) as home_jersey,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.jersey,T2.feed_jersey) as away_jersey,IFNULL(T2.display_abbr,T2.team_abbr) as away,S.playing_announce,(CASE WHEN JSON_SEARCH(S.playing_list,'one',P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,(CASE WHEN JSON_SEARCH(S.substitute_list,'one',P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_sub,L.league_id,IFNULL(L.display_name,L.league_name) AS league_name,L.tournament_type",FALSE)
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
       // echo $this->db->last_query();die;
        return $result;
    }

    public function check_player_sports($pl=array()){
      $season_prop_id = array_column($pl, 'pid');
       return  $this->db->select('count(DISTINCT MP.sports_id) as sport_cnt,MIN(S.scheduled_date) as start_date')
                 ->from(SEASON_PROPS. " SP")
                 ->join(MASTER_PROPS. " MP","SP.prop_id=MP.prop_id")
                 ->join(SEASON. " S","S.season_id=SP.season_id")
                 ->where_in('season_prop_id',$season_prop_id)
                 ->get()->row_array();

    }

     public function get_player_detail($season_prop_id)
     {
     	return $this->db->select("SP.prop_id,SP.player_id,SP.team_id,SP.position,SP.points,S.scheduled_date,P.full_name,IFNULL(T1.jersey,T1.feed_jersey) as home_jersey,IFNULL(T2.jersey,T2.feed_jersey) as away_jersey,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away,S.home_id,S.away_id,S.format,SP.team_id,IFNULL(P.image,'') as player_image")
     		->from(SEASON_PROPS." AS SP" )
     		->join(SEASON." S","S.season_id=SP.season_id","INNER")
            ->join(PLAYER." AS P", "P.player_id = SP.player_id", "INNER")
            ->join(TEAM." AS T1", "T1.team_id = S.home_id", "INNER")
            ->join(TEAM." AS T2", "T2.team_id = S.away_id", "INNER")
            ->where('SP.season_prop_id',$season_prop_id)
            ->get()->row_array();
     }

    public function get_player_stats($post_data)
    {	
        $where_field ='';
        if($post_data['sports_id'] == CRICKET_SPORTS_ID){
    		$table = STATS_CRICKET;
            if($post_data['prop_id'] == 1 || $post_data['prop_id']== 3 || $post_data['prop_id'] ==4){
                $where_field = 'batting_balls_faced';
            }elseif($post_data['prop_id'] == 2){
                $where_field = 'bowling_overs';
            }
    	}elseif($post_data['sports_id'] == SOCCER_SPORTS_ID){
    		$table = STATS_SOCCER;
    	}elseif($post_data['sports_id'] == BASKETBALL_SPORTS_ID){
            $table = STATS_BASKETBALL;
        }elseif($post_data['sports_id'] == FOOTBALL_SPORTS_ID){
            $table = STATS_FOOTBALL;
        }
    	$field_name = $post_data['field_name'];
    	 $this->db->select("S.scheduled_date as match_date,(CASE WHEN ST.team_id =S.home_id THEN IFNULL(T2.display_abbr,T2.team_abbr)  WHEN ST.team_id =S.away_id THEN IFNULL(T1.display_abbr,T1.team_abbr) ELSE NULL END) as away,SUM(ST.$field_name) as score") //
    			->from($table." ST")
    			->join(SEASON. " S","S.season_id=ST.season_id","INNER")
				->join(TEAM." AS T1", "T1.team_id = S.home_id", "INNER")
                ->join(TEAM." AS T2", "T2.team_id = S.away_id", "INNER");
                if($post_data['sports_id'] == CRICKET_SPORTS_ID){
                    $this->db->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER");
                    if(in_array($post_data['tournament_type'], [1,2])){
                        $this->db->where_in('L.tournament_type',[1,2]);
                    }else{
                         $this->db->where('L.tournament_type',$post_data['tournament_type']); 
                    }
                    //$this->db->where("CASE WHEN L.tournament_type=1 OR L.tournament_type =2 THEN L.tournament_type IN(1,2) ELSE L.tournament_type END",NULL,FALSE);
                }
    			
				$this->db->where('ST.player_id',$post_data['player_id']);
                $this->db->where('S.format',$post_data['format']);
                $this->db->where('S.status',2);
                if(!empty($where_field)){
                    $this->db->where($where_field.'>',0);
                }
                if($post_data['sports_id'] == FOOTBALL_SPORTS_ID){
                    $this->db->where('S.type !=','PRE');
                }
				$this->db->group_by('ST.season_id');
				$this->db->order_by('S.scheduled_date','DESC');
				$this->db->limit(5);
	   $result= $this->db->get()->result_array();
		//echo $this->db->last_query();die;
		return $result;
    }

     /**
     * Save team first time
     * @param post_data array
     * @return array
     */
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
            $lm['sports_id']  =$post_data['sports_id'];
            $lm['probable_winning'] = $post_data['probable_winning'];
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

                $post_data['bonus_amount'] =$post_data['real_amount']=$post_data['points']=$post_data['winning_amount']=0;
                if($post_data['currency_type'] == 0){
                    $post_data['bonus_amount'] = $post_data['entry_fee'];
                }if($post_data['currency_type'] == 1){
                    $post_data['real_amount'] = $post_data['entry_fee'];
                }if($post_data['currency_type'] == 2){
                    $post_data['points'] = $post_data['entry_fee'];
                }

                $post_data["source"] = JOIN_ENTRY_SOURCE;
                $post_data["reason"] =  'for picks entry';
                $post_data["source_id"]    = $user_team_id;   
                $post_data["reference_id"] =$user_team_id;
                $post_data["custom_data"]  = array('team_name'=>$post_data['team_name']);
                $this->load->model("user/User_model");
                $this->User_model->deduct_entry_fee($post_data);

            	$this->db->trans_complete();
                if ($this->db->trans_status() === FALSE )
                {
                    $this->db->trans_rollback();
                    return array();
                }
                else
                {
                    //delete user balance data
                    $balance_cache_key = 'user_balance_'.$this->user_id;
                    $this->delete_cache_data($balance_cache_key);
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
     * Update team picks
     * @param post_data array
     * @return array
     */
	public function update_team($post_data,$team)
	{
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



            $user_team_id = $post_data['user_team_id'];
            $lm = array();
            $lm['team_name'] = $post_data["team_name"];
            $lm['payout_type'] = $post_data["payout_type"];
            $lm['currency_type'] = $post_data["currency_type"];
            $lm['entry_fee'] = $post_data["entry_fee"];
            $lm['total_pick'] = count($post_data['pl']);
            $lm['probable_winning'] = $post_data['probable_winning'];
            $lm['modified_date'] = format_date();
            $update = $this->update(USER_TEAM,$lm,array('user_team_id'=>$user_team_id));

            $lineup = $this->get_all_table_data('*',LINEUP,array('user_team_id'=>$user_team_id));
            $season_prop_id = array_column($lineup, 'season_prop_id');
            $diff_prop_id = array_diff($season_prop_id,array_column($post_data['pl'], 'pid'));
            if(!empty($diff_prop_id)){
            	$this->db->where_in('season_prop_id',$diff_prop_id);
            	$this->db->where('user_team_id',$user_team_id);
            	$this->db->delete(LINEUP);
            }

            $update = [];
            foreach ($post_data['pl'] as  $pl_row) {

            	$value = isset($sp_data[$pl_row['pid']]) ? $sp_data[$pl_row['pid']] : 0;
            	$update[] = array(
            		'user_team_id'=>$user_team_id,
            		'season_prop_id'=>$pl_row['pid'],
            		'type'=>$pl_row['type'],
            		'value'=>$value
            	); 
            }
          	$this->replace_into_batch(LINEUP,$update); //Update and insert rows

            //Generate Order entry amount is greater or less than original
            if(trim($post_data['entry_fee']) != trim($team['entry_fee'])) {

                $post_data['bonus_amount'] =$post_data['real_amount']=$post_data['points']=$post_data['winning_amount']=0;

                $post_data["source_id"]    = $user_team_id;   
                $post_data["reference_id"] = $user_team_id;
                $post_data["custom_data"]  = array('team_name'=>$post_data['team_name']);
                if($post_data['entry_fee'] > $team['entry_fee']){
                    $post_data['entry_fee']   = $post_data['entry_fee'] - $team['entry_fee'];
                    if($post_data['currency_type'] == 0){
                        $post_data['bonus_amount'] = $post_data['entry_fee'];
                    }if($post_data['currency_type'] == 1){
                        $post_data['real_amount'] = $post_data['entry_fee'];
                    }if($post_data['currency_type'] == 2){
                        $post_data['points'] = $post_data['entry_fee'];
                    }
                    $post_data["source"]      = ADDITIONAL_ENTRY_SOURCE;
                    $post_data["reason"]      = 'for addition picks entry';
                   
                    $this->load->model("user/User_model");
                    $this->User_model->deduct_entry_fee($post_data);
                }elseif ($post_data['entry_fee'] < $team['entry_fee']) {
                    $post_data['entry_fee']   = $team['entry_fee'] - $post_data['entry_fee'];
                    if($post_data['currency_type'] == 0){
                        $post_data['bonus_amount'] = $post_data['entry_fee'];
                    }if($post_data['currency_type'] == 1){
                        $post_data['real_amount'] = $post_data['entry_fee'];
                    }if($post_data['currency_type'] == 2){
                        $post_data['points'] = $post_data['entry_fee'];
                    }
                    $post_data["source"]      = REFUND_ENTRY_SOURCE;
                    $post_data["reason"]      = 'for refund picks entry';

                    $this->load->model("user/User_model");
                    $this->User_model->credit_user_amount($post_data);
                }
            }

            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE ){
                $this->db->trans_rollback();
                return array();
            }
            else{
                //delete user balance data
                $balance_cache_key = 'user_balance_'.$this->user_id;
                $this->delete_cache_data($balance_cache_key);

                $this->db->trans_commit();
                return array("user_team_id" => $user_team_id);
            }

        }catch(Exception $e){
            $this->db->trans_rollback();
            return array();
        }
	}

    /**
     * Get probable winning plus alreadin winning amount
     * @param user_id
     */
    public function check_winning_cap($user_team_id = '')
    {  
        $current_date = format_date();
        $start_date = date('Y-m-01 00:00:01'); 
        $end_date  = date('Y-m-t 23:59:59');
         $this->db->select("SUM(CASE WHEN UT.status=0 || UT.status=1 THEN  UT.entry_fee * MP.points 
                                        ELSE 0 END) as probable_winning, SUM(CASE  WHEN UT.status=2 THEN UT.winning ELSE 0 END ) as winning,US.winning_cap,IFNULL(US.status,1) as status,US.note as reason"
                                )
            ->from(USER_TEAM. " UT")
            ->join(USER_SETTING. " US","UT.user_id=US.user_id")
            ->join(MASTER_PAYOUT. " MP","UT.payout_type=MP.payout_type AND UT.total_pick=MP.picks AND UT.total_pick=MP.correct")
            ->where('UT.user_id',$this->user_id)
            ->where('UT.modified_date >=',$start_date)
            ->where('UT.modified_date <=',$end_date);
            if($user_team_id){
                $this->db->where('user_team_id !=',$user_team_id);
            }
          return  $this->db->get()->row_array();
    }


     /**
     * Get User Joined team player detail
     * @param user_team_ids array
     * @return array
     */
    public function get_team_player_detail($user_team_ids)
    {
        return $result = $this->db->select('UT.user_team_id,SP.prop_id,L.status,P.full_name,L.type,L.value as projection_points,L.stats as score,S.scheduled_date,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away') //MP.short_name as prop_name     
            ->from(USER_TEAM . ' UT')
            ->join(LINEUP." AS L", "L.user_team_id = UT.user_team_id", "INNER")
            ->join(SEASON_PROPS." AS SP", "SP.season_prop_id = L.season_prop_id", "INNER")
            /*->join(MASTER_PROPS." AS MP", "SP.prop_id = MP.prop_id", "INNER")*/
            ->join(PLAYER." AS P", "P.player_id = SP.player_id", "INNER")
            ->join(SEASON." AS S", "S.season_id = SP.season_id", "INNER")
            ->join(TEAM." AS T1", "T1.team_id = S.home_id", "INNER")
            ->join(TEAM." AS T2", "T2.team_id = S.away_id", "INNER")
            ->where("UT.fee_refund", "0")
            ->where("UT.user_id", $this->user_id)
            ->where_in('UT.user_team_id',$user_team_ids)
            /*->group_by('SP.season_prop_id')*/
            ->get()->result_array();
        
    }


     /**
     * Get my contest data
     * @param post_data array
     * @return array
     */
     public function get_user_joined_teams($post_data)
     {
        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $offset = get_pagination_offset($page_no, $limit);
        $sports_id = $post_data['sports_id'];
        $status = $post_data['status'];
        $current_date = format_date();

        if ($status == 0 || $status == 1) {
            $w_cond = array('UT.status' => 0);
        } else if ($status == 2) {
            $w_cond = array('UT.status >' => 1);
        }
            
        $this->db->select('UT.user_team_id,UT.team_name,UT.winning,UT.payout_type,UT.entry_fee,UT.status,UT.total_pick,CASE
        WHEN COUNT(CASE WHEN L.status IN (2, 3) THEN 0 ELSE NULL END) > 0 THEN 0
        ELSE UT.probable_winning
        END AS probable_winning,GROUP_CONCAT(P.display_name) as display_name,MIN(S.scheduled_date) as start_date')     
            ->from(USER_TEAM . ' UT')
            ->join(LINEUP." AS L", "L.user_team_id = UT.user_team_id", "INNER")
            ->join(SEASON_PROPS." AS SP", "SP.season_prop_id = L.season_prop_id", "INNER")
            ->join(PLAYER." AS P", "P.player_id = SP.player_id", "INNER")
            ->join(SEASON." AS S", "S.season_id = SP.season_id", "INNER")
        
            ->where("UT.fee_refund", "0")
            ->where("UT.user_id", $this->user_id)
            ->where("UT.sports_id", $sports_id)
            ->where($w_cond);
           
        $this->db->group_by("UT.user_team_id");
        if ($status == 0) {
           $this->db->having('start_date >=',$current_date);
           $this->db->order_by('start_date','ASC');
        } else if ($status == 1) {
           $this->db->having('start_date <',$current_date);
           $this->db->order_by('start_date','DESC');
        }else if ($status == 2) {
           $this->db->having('start_date <',$current_date);
           $this->db->order_by('start_date','DESC');
        }
        if (isset($limit) && isset($offset)) {
            $this->db->limit($limit, $offset);
        }
        $result = $this->db->get()->result_array();

        $team_player = [];
        if(($status == 1 || $status == 0) && !empty($result)) {
            $user_team_ids = array_column($result, 'user_team_id');
            $team_player = $this->get_team_player_detail($user_team_ids);
        }
        //echo $this->db->last_query();die;
           
       return array('team_list'=>$result,'team_player'=>$team_player);         
     }


   
    /**
     * Get Live and completed team detail
     * @param user_team_id
     */
    function get_user_team_detail($user_team_id)
    {
        return $this->db->select('UT.payout_type,UT.sports_id,UT.team_name,UT.entry_fee,CASE
        WHEN COUNT(CASE WHEN L.status IN (2, 3) THEN 0 ELSE NULL END) > 0 THEN 0
        ELSE UT.probable_winning
    END AS probable_winning,UT.winning,MP.points as payout_points')
                ->from(USER_TEAM. " UT")
                ->join(LINEUP. " L","UT.user_team_id=L.user_team_id")
                ->join(MASTER_PAYOUT. " MP","UT.payout_type=MP.payout_type AND UT.total_pick=MP.picks AND UT.total_pick=MP.correct")
                ->where('UT.user_team_id',$user_team_id)
                ->get()->row_array();
    }

     /**
     * Get Live and completed player detail
     * @param user_team_id
     */
      function get_user_joined_player_detail($user_team_id)
    {
        return $result = $this->db->select("SP.prop_id,SP.season_prop_id,SP.team_id,SP.position,UT.payout_type,UT.sports_id,L.status,P.full_name,P.display_name,IFNULL(P.image,'') as player_image,L.type,L.value as projection_points,L.stats as score,S.scheduled_date,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away,IFNULL(T1.jersey,T1.feed_jersey) as home_jersey,IFNULL(T2.jersey,T2.feed_jersey) as away_jersey,S.home_id,S.away_id,S.playing_announce,(CASE WHEN JSON_SEARCH(S.playing_list,'one',P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,(CASE WHEN JSON_SEARCH(S.substitute_list,'one',P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_sub,LE.tournament_type")     
            ->from(USER_TEAM . ' UT')
            ->join(LINEUP." AS L", "L.user_team_id = UT.user_team_id", "INNER")
            ->join(SEASON_PROPS." AS SP", "SP.season_prop_id = L.season_prop_id", "INNER")
            ->join(PLAYER." AS P", "P.player_id = SP.player_id", "INNER")
            ->join(SEASON." AS S", "S.season_id = SP.season_id", "INNER")
            ->join(LEAGUE." AS LE", "LE.league_id = S.league_id", "INNER")
            ->join(TEAM." AS T1", "T1.team_id = S.home_id", "INNER")
            ->join(TEAM." AS T2", "T2.team_id = S.away_id", "INNER")
            ->where('UT.user_team_id',$user_team_id)
            ->group_by('SP.season_prop_id')
            ->get()->result_array();
    }
}
