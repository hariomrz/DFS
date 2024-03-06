<?php
class Lobby_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get lobby fixture list
     * @param array $post_data
     * @return array
    */
    public function get_lobby_fixture_list($sports_id) {
        $current_date = format_date();
        $this->db->select("CM.collection_id,CM.league_id,CM.season_game_uid,CM.collection_name,CM.season_scheduled_date,CM.status,SUM(CASE WHEN C.prize_type=0 THEN C.max_prize_pool WHEN C.prize_type=1 THEN C.max_prize_pool ELSE 0 END) AS prize_pool,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,CONCAT_WS('_',CM.league_id,CM.season_game_uid) as game_key,IFNULL(CM.timer_date,'') as timer_date", FALSE);
        $this->db->from(COLLECTION." as CM");
        $this->db->join(CONTEST.' as C', 'C.collection_id = CM.collection_id AND C.status=0', "INNER");
        //$this->db->where("CM.season_scheduled_date >",$current_date);
        $this->db->where('CM.status', '0');        
        $this->db->where("C.sports_id", $sports_id);
        $this->db->where('C.contest_access_type', '0');
        $this->db->group_by("CM.collection_id");
        $this->db->order_by("CM.season_scheduled_date", "ASC");
        $this->db->order_by("inning", "ASC");
        $this->db->order_by("overs", "ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get user joined contest
     * @param int $collection_id
     * @return array
    */
    public function get_user_joined_contests($collection_id,$contest_id='') {
        $this->db->select("GROUP_CONCAT(DISTINCT UC.contest_id) as ids", FALSE);
        $this->db->from(USER_CONTEST." as UC");
        $this->db->join(USER_TEAM." as UT", "UT.user_team_id = UC.user_team_id", "INNER");
        $this->db->where("UT.user_id",$this->user_id);
        $this->db->where('UT.collection_id', $collection_id);
        if(isset($contest_id) && $contest_id != ""){
            $this->db->where('UC.contest_id', $contest_id);
        }
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used to get fixture contest list for Without login users, without private contests
     * @param array $post_data
     * @param int $group_id
     * @return array
    */
    public function get_collection_contests($post_data) {
        $current_date = format_date();
        $sports_id = $post_data['sports_id'];
        $collection_id = $post_data['collection_id'];
        $user_where = array(0);
        if($this->user_id != ""){
            $user_where[] = $this->user_id;
        }
     
        $this->db->select("C.contest_id,C.contest_unique_id,C.collection_id,C.group_id,C.league_id,C.entry_fee,C.size,C.minimum_size,C.max_bonus_allowed,C.season_scheduled_date,C.total_user_joined,C.prize_pool,C.guaranteed_prize,C.multiple_lineup,C.contest_access_type,C.prize_distibution_detail as prize_detail,C.prize_type,C.is_pin_contest,C.is_tie_breaker,C.currency_type,IFNULL(C.contest_title,'') as contest_title,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link", FALSE);
        $this->db->from(CONTEST." as C");
        $this->db->join(COLLECTION." as CM", "CM.collection_id = C.collection_id", "INNER");
        $this->db->where('CM.status', 0);
        $this->db->where('C.status', 0);
        $this->db->where_in("C.user_id",$user_where);
        $this->db->where('C.size > C.total_user_joined',NULL);
        $this->db->where('C.collection_id', $collection_id);
        if (isset($post_data['pin_contest']) && $post_data['pin_contest'] == 1) {
            $this->db->where('C.is_pin_contest', 1);
        } else {
            $this->db->where('C.is_pin_contest', 0);
        }
        //$this->db->where("C.season_scheduled_date > ",$current_date);
        $this->db->order_by("C.is_pin_contest", "DESC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get collection match details
     * @param int $collection_id
     * @return array
    */
    public function get_collection_fixture_details($collection_id){

        $this->db->select("CM.collection_id,CM.league_id,CM.collection_name,S.home_uid,S.away_uid,S.season_game_uid,S.season_scheduled_date,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.feed_flag,T1.flag) AS home_flag,IFNULL(T2.feed_flag,T2.flag) AS away_flag,S.format,S.delay_minute as dm,S.delay_message as dmsg,S.custom_message as cmsg,S.scoring_alert as sa,S.is_pin_fixture as pin,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,S.status,IFNULL(S.score_data,'{}') as score_data,S.is_live_score,IFNULL(CM.timer_date,'') as timer_date",FALSE);
        $this->db->from(COLLECTION." as CM");
        $this->db->join(SEASON.' as S', 'S.season_game_uid = CM.season_game_uid AND S.league_id = CM.league_id',"INNER");
        $this->db->join(TEAM.' as T1', 'T1.team_uid = S.home_uid',"INNER");
        $this->db->join(TEAM.' as T2', 'T2.team_uid = S.away_uid',"INNER");
        $this->db->where('CM.collection_id', $collection_id);
        $this->db->group_by("CM.collection_id");
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used to get contest details
     * @param array $post_data
     * @return array
     */
    public function get_contest_detail($post_data) {

        $sql = $this->db->select("C.contest_id,C.sports_id,C.league_id,C.contest_unique_id,C.collection_id,C.contest_name,C.contest_description,C.season_scheduled_date,C.minimum_size,C.size,C.total_user_joined,C.multiple_lineup,C.entry_fee,C.site_rake,C.prize_pool,C.max_bonus_allowed,C.currency_type,C.prize_type,IFNULL(C.prize_distibution_detail,'[]') as prize_detail,C.guaranteed_prize,C.status,CM.collection_name,IFNULL(C.consolation_prize,'') as consolation_prize,C.is_pin_contest,C.group_id,C.is_tie_breaker,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link,C.prize_value_type,IF(C.user_id > 0,'1','0') as is_private,C.user_id as contest_creater,C.host_rake,IFNULL(C.video_link,'') as video_link,IFNULL(C.contest_title,'') as contest_title,C.is_prize_reset,0 is_joined,CM.status as over_status,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,C.is_custom_prize_pool,C.is_auto_recurring", FALSE)
                ->from(CONTEST." AS C")
                ->join(COLLECTION." AS CM", 'CM.collection_id = C.collection_id', 'INNER');

        if (isset($post_data['contest_unique_id']) && $post_data['contest_unique_id'] != "") {
            $this->db->where('C.contest_unique_id', $post_data['contest_unique_id']);
        }
        if (isset($post_data['contest_id']) && $post_data['contest_id'] != "") {
            $this->db->where('C.contest_id', $post_data['contest_id']);
        }
        $result = $sql->get()->row_array();
        if(!empty($result)){
            $joined = $this->get_user_joined_contests($result['collection_id'],$result['contest_id']);
            if(isset($joined['ids']) && !empty($joined['ids'])){
                $ids = explode(",",$joined['ids']);
                if(in_array($result['contest_id'],$ids)){
                    $result['is_joined'] = 1;
                }
            }
        }
        return $result;
    }

    /**
     * used to get contest joined users list
     * @param array $post_data
     * @return array
     */
    public function get_contest_joined_users($post_data) {
        if (empty($post_data)) {
            return array();
        }

        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $offset = get_pagination_offset($page_no, $limit);
        $this->db->select("UT.user_id,UT.user_name,1 as user_count")
                ->from(USER_CONTEST." UC")
                ->join(USER_TEAM." AS UT", 'UT.user_team_id = UC.user_team_id', 'INNER')
                ->where("UC.contest_id", $post_data['contest_id'])
                ->where("UC.fee_refund", 0)
                ->order_by('UC.user_contest_id','ASC')
                ->group_by('UT.user_id')
                ->limit($limit, $offset);
        if($this->user_id){
            $this->db->order_by("FIELD(user_id,".$this->user_id.") DESC");
        }
        $this->db->order_by("UC.user_contest_id","ASC");

        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used for get user total invested amount and and check it with self exlusion limit
     * @param int $user_id
     * @return boolean
     */
    public function user_join_contest_ids($user_id) {
        $current_date = format_date("today", "Y-m");
        $this->db->select("GROUP_CONCAT(DISTINCT UC.contest_id) as contest_ids")
                ->from(USER_CONTEST." UC")
                ->join(USER_TEAM." AS UT", 'UT.user_team_id = UC.user_team_id', 'INNER')
                ->join(CONTEST." AS C", 'C.contest_id = UC.contest_id', 'INNER')
                ->where("UT.user_id", $user_id);
        $this->db->where("DATE_FORMAT(C.season_scheduled_date,'%Y-%m')", $current_date);
        $result = $this->db->get()->row_array();
        $contest_ids_arr = array();
        if(!empty($result)) {
            $contest_ids = $result['contest_ids'];
            $contest_ids_arr = explode(',', $contest_ids);
        }
        return $contest_ids_arr;
    }

    /**
     * used to join contest
     * @param array $contest
     * @return array
     */
    public function join_game($contest) {
        $contest_data = $this->get_single_row("total_user_joined,size,collection_id", CONTEST, array("contest_id" => $contest["contest_id"]));
        if(!empty($contest_data) && $contest_data['total_user_joined'] < $contest_data['size']){
            try{
                //save user team
                $check_team = $this->get_single_row("user_team_id", USER_TEAM, array("collection_id" => $contest_data["collection_id"],"user_id"=>$this->user_id));
                if(!empty($check_team) && isset($check_team['user_team_id'])){
                    $user_team_id = $check_team['user_team_id'];
                }else{
                    $team_arr = array();
                    $team_arr['collection_id'] = $contest_data['collection_id'];
                    $team_arr['user_id'] = $this->user_id;
                    $team_arr['user_name'] = $this->user_name;
                    $team_arr['team_name'] = "Team 1";
                    $team_arr['date_added'] = format_date();
                    $team_arr['date_modified'] = format_date();
                    $user_team_id = $this->save_record(USER_TEAM,$team_arr);
                }

                //user team record
                $uc_arr = array();
                $uc_arr['contest_id'] = $contest["contest_id"];
                $uc_arr['user_team_id'] = $user_team_id;
                $uc_arr['created_date'] = format_date();
                $this->db->insert(USER_CONTEST, $uc_arr);
                $user_contest_id = $this->db->insert_id();
                if ($user_contest_id) {
                    //increment contest joined count
                    $this->db->where('contest_id', $contest['contest_id']);
                    $this->db->set('total_user_joined', 'total_user_joined+1', FALSE);
                    //for update prize distribution details
                    $joined_count = $contest_data["total_user_joined"] + 1;
                    if (!empty($joined_count) && $joined_count > $contest['minimum_size'] && $joined_count <= $contest['size'] && $contest['entry_fee'] > 0) {
                        $prize_pool = $this->get_contest_prize_distribution_for_update($joined_count, $contest);
                        if (!empty($prize_pool) && isset($prize_pool['prize_pool'])) {
                            $this->db->set('prize_pool', $prize_pool['prize_pool']);
                        }
                        if (!empty($prize_pool) && isset($prize_pool['prize_distibution_detail'])) {
                            $this->db->set('prize_distibution_detail', $prize_pool['prize_distibution_detail']);
                        }
                    }

                    $this->db->update(CONTEST);
                    return array("joined_count" => $joined_count, "user_contest_id" => $user_contest_id);
                } else {
                    return array();
                }
            }catch(Exception $e){
                return array();
            }
        }else{
            return array();
        }
    }

    /**
     * used to get contest prize details for update
     * @param int $join_count
     * @param array $contest
     * @return array
     */
    public function get_contest_prize_distribution_for_update($join_count, $contest) {
        if ((isset($contest['guaranteed_prize']) && $contest['guaranteed_prize'] == '2')) {
            return array();
        }

        $total_amount = $join_count * $contest['entry_fee'];
        if(isset($contest['is_private']) && $contest['is_private'] == "1"){
            $prize_pool_percent = 100 - $contest['site_rake'] - $contest['host_rake'];
        }else{
            $prize_pool_percent = 100;// - $contest['site_rake'];
        }
        $prize_pool = truncate_number_only(($prize_pool_percent / 100) * $total_amount); //new prize pool
        $update_data = array();
        $update_data['prize_pool'] = $prize_pool;
        //check for auto prize pool 
        if ($contest["is_custom_prize_pool"] == '1') {
            $prize_pool_details = json_decode($contest['prize_detail'], TRUE);
            //update prize pool
            foreach ($prize_pool_details as $key => $value) {
                if(!isset($value['prize_type']) || $value['prize_type'] != 3){
                    $person_count = ($value['max'] - $value['min']) + 1;
                    $per_person = truncate_number_only((($prize_pool * $value['per']) / 100) / $person_count);
                    if(isset($value['prize_type']) && $value['prize_type'] == 2){
                        $per_person = ceil($per_person);
                    }
                    $prize_pool_details[$key]["amount"] = $per_person;
                    $prize_pool_details[$key]["min_value"] = number_format(($per_person * $person_count),"2",".","");
                }
            }
            $update_data['prize_distibution_detail'] = json_encode($prize_pool_details);
        }

        return $update_data;
    }

    /**
     * used to join contest
     * @param array $post_data
     * @return array
     */
    public function remove_joined_game($contest, $user_contest_id) {
        if ($user_contest_id) {
            $this->db->where("user_contest_id",$user_contest_id);
            $this->db->delete(USER_CONTEST);

            //increment contest joined count
            $this->db->where('contest_id', $contest['contest_id']);
            $this->db->set('total_user_joined', 'total_user_joined-1', FALSE);

            //for update prize distribution details
            if ($contest['total_user_joined'] > $contest['minimum_size'] && $contest['total_user_joined'] <= $contest['size'] && $contest['entry_fee'] > 0) {
                $prize_pool = $this->get_contest_prize_distribution_for_update($joined_count, $contest);
                if (!empty($prize_pool) && isset($prize_pool['prize_pool'])) {
                    $this->db->set('prize_pool', $prize_pool['prize_pool']);
                }
                if (!empty($prize_pool) && isset($prize_pool['prize_distibution_detail'])) {
                    $this->db->set('prize_distibution_detail', $prize_pool['prize_distibution_detail']);
                }
            }

            $this->db->update(CONTEST);

            $joined_count = $contest["total_user_joined"] - 1;
            return array("joined_count" => $joined_count, "user_contest_id" => $user_contest_id);
        } else {
            return array();
        }
    }

    /**
     * used to get lobby fixture list
     * @param array $post_data
     * @return array
    */
    public function get_my_joined_fixtures($post_data) {
        $current_date = format_date();
        $past_date = date('Y-m-d H:i:s',strtotime($current_date.' -7 days'));
        $sports_id = $post_data['sports_id'];
        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $offset = get_pagination_offset($page_no, $limit);
        
        $this->db->select("CM.collection_id,CM.league_id,CM.season_game_uid,CM.season_scheduled_date,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,CONCAT_WS('_',CM.league_id,CM.season_game_uid) as game_key,CM.status,UC.total_score,(CASE WHEN ((CM.status=1 AND C.total_user_joined >= C.minimum_size) OR CM.status=0) THEN 1 ELSE 0 END) as tmp,IFNULL(CM.timer_date,'') as timer_date", FALSE);
        $this->db->from(USER_TEAM." as UT");
        $this->db->join(USER_CONTEST.' as UC', 'UC.user_team_id = UT.user_team_id', "INNER");
        $this->db->join(CONTEST.' as C', 'C.contest_id = UC.contest_id', "INNER");
        $this->db->join(COLLECTION.' as CM', 'CM.collection_id = C.collection_id', "INNER");
        $this->db->where_in("CM.status",array("0","1"));
        $this->db->where("C.status !=","1");
        $this->db->where("UT.user_id", $this->user_id);
        $this->db->where('C.season_scheduled_date >=', $past_date);
        $this->db->where("C.sports_id", $sports_id);
        $this->db->group_by("C.collection_id");
        $this->db->having("tmp","1");
        $this->db->order_by("CM.season_scheduled_date", "ASC");
        $this->db->order_by("FIELD(CM.status,1,0,2) ASC");
        $this->db->order_by("inning", "ASC");
        $this->db->order_by("overs", "ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get lobby fixture list
     * @param array $post_data
     * @return array
    */
    public function get_user_joined_fixtures($post_data) {
        $current_date = format_date();
        $sports_id = $post_data['sports_id'];
        $status = $post_data['status'];
        $date_sort = "ASC";
        if($status == 0){
            $w_cond = array('CM.status' => 0);
        }else if($status == 1){
            $date_sort = "DESC";
            $w_cond = array('CM.status' => 1, 'C.season_scheduled_date <' => $current_date,"C.total_user_joined >= C.minimum_size"=>NULL);
        }else if($status == 2){
            $date_sort = "DESC";
            $w_cond = array('CM.status' => 2,'C.status !=' => 1, 'C.season_scheduled_date <' => $current_date,"C.total_user_joined >= C.minimum_size"=>NULL);
        }
        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $offset = get_pagination_offset($page_no, $limit);
        $prize_sel = "0 as won_amt,0 as won_bonus,0 as won_coins,'' as won_marchandise";
        if($status == "2"){
            $prize_sel = "SUM(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as won_amt,SUM(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as won_bonus,SUM(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as won_coins,GROUP_CONCAT(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].name')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].name')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].name')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].name')) ELSE NULL END) as won_marchandise";
        }
        $this->db->select("CM.collection_id,CM.league_id,CM.season_game_uid,CM.season_scheduled_date,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,CONCAT_WS('_',CM.league_id,CM.season_game_uid) as game_key,CM.status,UC.total_score,".$prize_sel, FALSE);
        $this->db->select("COUNT(DISTINCT C.contest_id) as contest_count",FALSE);
        $this->db->from(USER_TEAM." as UT");
        $this->db->join(USER_CONTEST.' as UC', 'UC.user_team_id = UT.user_team_id', "INNER");
        $this->db->join(CONTEST.' as C', 'C.contest_id = UC.contest_id', "INNER");
        $this->db->join(COLLECTION.' as CM', 'CM.collection_id = C.collection_id', "INNER");
        $this->db->where("UT.user_id", $this->user_id);
        $this->db->where("C.sports_id", $sports_id);
        $this->db->where("C.status !=", "1");
        $this->db->where($w_cond);
        $this->db->group_by("C.collection_id");
        $this->db->order_by("CM.season_scheduled_date", $date_sort);
        $this->db->order_by("inning", "ASC");
        $this->db->order_by("overs", "ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used for get user joined contest list by collection id
     * @param int $collection_id
     * @param int $status
     * @param int $sports_id
     * @return array
     */
    public function get_user_fixture_contest($collection_id, $status, $sports_id = 7) {
        $current_date = format_date();
        if ($status == 0) {
            // UPCOMING
            $w_cond = array('CM.status' => 0);
        } else if ($status == 1) {
            // For contest is LIVE
            $w_cond = array('CM.status' => 1, 'CM.season_scheduled_date <' => $current_date);
        } else if ($status == 2) {
            // COMPLETED
            $w_cond = array('CM.status' => 2, 'C.season_scheduled_date <' => $current_date);
        }

        $this->db->select("C.contest_id,C.contest_unique_id,C.collection_id,C.group_id,C.league_id,C.sports_id,C.entry_fee,C.size,C.minimum_size,C.max_bonus_allowed,C.season_scheduled_date,C.total_user_joined,C.prize_pool,C.guaranteed_prize,C.multiple_lineup,C.contest_access_type,C.prize_distibution_detail as prize_detail,C.prize_type,C.is_pin_contest,C.is_tie_breaker,C.currency_type,IFNULL(C.contest_title,'') as contest_title,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link,C.contest_name,C.site_rake,C.status,UC.total_score,UC.game_rank,UC.is_winner,IF(C.user_id > 0, 1,0) as is_private,MG.group_name,UC.prize_data,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,IFNULL(CM.timer_date,'') as timer_date,C.user_id as contest_creater", false)
                ->from(USER_TEAM." as UT")
                ->join(USER_CONTEST.' as UC', 'UC.user_team_id = UT.user_team_id', "INNER")
                ->join(CONTEST.' C', 'C.contest_id = UC.contest_id AND C.status != 1', 'INNER')
                ->join(COLLECTION.' as CM', 'CM.collection_id = C.collection_id', 'INNER')
                ->join(MASTER_GROUP . ' MG', 'MG.group_id = C.group_id', 'INNER')
                ->where("UC.fee_refund", "0")
                ->where("UT.user_id", $this->user_id)
                ->where("C.sports_id", $sports_id)
                ->where($w_cond);

        if(is_array($collection_id)){
            $this->db->where_in("C.collection_id", $collection_id);
        }else{
            $this->db->where("C.collection_id", $collection_id);
        }

        if ($status == 1 || $status == 2) {
            $this->db->order_by("inning", "ASC");
            $this->db->order_by("overs", "ASC");
            $this->db->order_by("UC.game_rank", "ASC");
            $this->db->order_by("C.season_scheduled_date", "DESC");
        } else {
            $this->db->order_by("inning", "ASC");
            $this->db->order_by("overs", "ASC");
            $this->db->order_by("C.season_scheduled_date", "ASC");
        }

        if ($status == 2) {
            $this->db->limit(500,"0");
        }

        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used for save contest invite code
     * @param array $invites
     * @return boolean
     */
    public function save_invites($invites) {
        $this->db->insert_batch(INVITE, $invites);
        return TRUE;
    }

    /**
     * used for generate contest promo code
     * @return string
     */
    public function _generate_contest_code() {
        $this->load->helper('security');

        do {
            $salt = do_hash(time() . mt_rand());
            $new_key = substr($salt, 0, 6);
            $new_key = strtoupper($new_key);
        }
        // Already in the DB? Fail. Try again
        while (self::_contest_code_exists($new_key));
        return $new_key;
    }

    /**
     * used for check contest code exist or not
     * @param string $key
     * @return boolean
     */
    private function _contest_code_exists($key) {
        $this->db->select('invite_id');
        $this->db->where('code', $key);
        $this->db->limit(1);
        $query = $this->db->get(INVITE);
        $num = $query->num_rows();
        if ($num > 0) {
            return true;
        }
        return false;
    }

    /**
     * used for get contest leaderboard
     * @param array $post_data
     * @return array
     */
    public function get_contest_leaderboard($post_data)
    {
        if (empty($post_data)) {
            return array();
        }

        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $offset = get_pagination_offset($page_no, $limit);
        $this->db->select("UC.user_contest_id,UC.user_team_id,UC.total_score,UC.is_winner,IFNULL(UC.prize_data,'[]') as prize_data,IF(UC.game_rank > 0,UC.game_rank,RANK() OVER (ORDER BY UC.total_score DESC,UC.total_time ASC)) as game_rank,UT.user_id,UT.user_name,UT.team_name,'' as image,IF(UT.user_id=".$this->user_id.",1,0) as user_team",FALSE)
           ->from(USER_CONTEST.' UC')
           ->join(USER_TEAM.' UT',"UT.user_team_id = UC.user_team_id")
           ->where("UC.fee_refund", "0")
           ->where("UC.contest_id", $post_data["contest_id"]);

        $this->db->order_by("FIELD(user_team,1,0)");
        $this->db->order_by("UC.game_rank", "ASC", FALSE);
        $this->db->order_by("UC.total_score","DESC", FALSE);

        if($page_no != 1)
        {
            $this->db->where('UT.user_id<>',$this->user_id);
        }
    
        $this->db->limit($limit,$offset);
        $sql = $this->db->get();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * used for get user team details
     * @param array $post_data
     * @return array
     */
    public function get_linpeup_with_score($user_contest_id)
    {
        if (empty($user_contest_id)) {
            return array();
        }

        $this->db->select("UP.predict_id,UP.market_id,UP.odds_id,UP.second_odds_id,UP.is_correct,UP.score,UP.points,UP.score,MO.name",FALSE)
           ->from(USER_CONTEST.' UC')
           ->join(USER_PREDICTION.' UP',"UP.user_team_id = UC.user_team_id")
           ->join(MASTER_ODDS.' MO',"MO.odds_id = UP.odds_id")
           ->where("UC.user_contest_id", $user_contest_id);

        $this->db->order_by("UP.over_ball", "ASC", FALSE);
        $sql = $this->db->get();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * used for get user team details
     * @param array $post_data
     * @return array
     */
    public function get_match_over_ball($collection_id){
        $this->db->select("C.collection_id,C.season_game_uid,C.league_id,C.inn_over,MO.market_id,MO.over_ball,IF(MO.market_status='cls',MO.result,0) as result,IF(MO.market_status='cls',MO.score,0) as score,REPLACE(MO.over_ball,'.','_') as ball,MO.extra_score_id",FALSE)
            ->from(COLLECTION." as C")
            ->join(MARKET_ODDS.' MO',"MO.season_game_uid = C.season_game_uid AND MO.league_id=C.league_id AND MO.inn_over=C.inn_over AND MO.market_status!='ctd'","LEFT")
            ->where("C.collection_id",$collection_id);
        $this->db->order_by('display_order','ASC');
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used for get user team details
     * @param int $collection_id
     * @return array
     */
    public function get_user_predict_data($user_team_id)
    {
        if (empty($user_team_id)) {
            return array();
        }

        $this->db->select("UP.predict_id,UP.market_id,UP.odds_id,UP.is_correct,UP.score,UP.points,UP.score",FALSE)
           ->from(USER_PREDICTION.' UP')
           ->where("UP.user_team_id", $user_team_id)
           ->order_by("UP.over_ball", "ASC");
        $sql = $this->db->get();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * used for get match over odds list
     * @param array $post_data
     * @return array
     */
    public function get_match_over_ball_odds($post_data){
        $this->db->select("C.collection_id,C.season_game_uid,C.league_id,C.inn_over,C.over_time,MO.market_id,MO.over_ball,MO.result,MO.score,MO.market_odds,IFNULL(UP.predict_id,0) as predict_id,IFNULL(UP.odds_id,0) as odds_id,IFNULL(UP.second_odds_id,0) as second_odds_id,MO.bat_player_id,MO.bow_player_id,MO.market_date",FALSE)
            ->from(COLLECTION." as C")
            ->join(MARKET_ODDS.' MO',"MO.season_game_uid = C.season_game_uid AND MO.league_id=C.league_id AND MO.inn_over=C.inn_over","INNER")
            ->join(USER_PREDICTION.' UP',"UP.market_id = MO.market_id AND UP.user_team_id='".$post_data['user_team_id']."'","LEFT")
            ->where("C.collection_id",$post_data['collection_id'])
            ->where("MO.market_id",$post_data['market_id']);
        $this->db->group_by('MO.market_id');
        $result = $this->db->get()->row_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    public function get_match_players($season_game_uid,$league_id){
        $this->db->select('PT.player_team_id,PT.season_game_uid,PT.position,P.name,P.display_name,S.home_uid,S.away_uid,IFNULL(S.batting_team_uid,home_uid) AS batting_team_uid',false)
            ->from(PLAYER_TEAM.' as PT')
            ->join(PLAYER.' as P','PT.player_id=P.player_id')
            ->join(SEASON.' as S','S.season_game_uid=PT.season_game_uid')
            ->where('PT.is_deleted',0)
            ->where('PT.season_game_uid',$season_game_uid)
            ->where('S.league_id',$league_id);
        return $this->db->get()->result_array();
    }

    /**
     * used for get user joined contest list by collection id
     * @param int $collection_id
     * @return array
     */
    public function get_user_match_joined_contest($collection_id) {
        $this->db->select("C.contest_id,C.contest_unique_id,C.collection_id,C.entry_fee,C.size,C.minimum_size,C.total_user_joined,C.prize_pool,C.prize_distibution_detail as prize_detail,C.currency_type,C.contest_name,C.site_rake,C.status,UC.total_score,UC.game_rank,UC.is_winner,UC.prize_data", false)
                ->from(USER_TEAM." as UT")
                ->join(USER_CONTEST.' as UC', 'UC.user_team_id = UT.user_team_id', "INNER")
                ->join(CONTEST.' C', 'C.contest_id = UC.contest_id', 'INNER')
                ->where("C.status != ", "1")
                ->where("C.total_user_joined >= C.minimum_size",NULL)
                ->where("UC.fee_refund", "0")
                ->where("UT.collection_id", $collection_id)
                ->where("UT.user_id", $this->user_id);

        $this->db->order_by("UC.game_rank", "ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used for get next over details
     * @param array $post_data
     * @return array
     */
    public function get_next_over($post_data) {
        $this->db->select("CM.collection_id,CM.collection_name,CM.status,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,CONVERT(REGEXP_REPLACE(CM.inn_over,'[^0-9]',''),UNSIGNED) as inn_over_val", false)
                ->from(COLLECTION." as CM")
                ->where_in("CM.status",array("0","1"))
                ->where("CM.league_id", $post_data['league_id'])
                ->where("CM.season_game_uid", $post_data['season_game_uid'])
                ->where("CM.inn_over != ", $post_data['inn_over']);
        $this->db->order_by("inning", "ASC");
        $this->db->order_by("overs", "ASC");
        $this->db->order_by("inn_over_val", "ASC");
        $this->db->limit(1);
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used for get user team details
     * @param int $collection_id
     * @return array
     */
    public function get_user_team_stats($user_team_id)
    {
        if (empty($user_team_id)) {
            return array();
        }

        $this->db->select("UP.predict_id,UP.market_id,UP.odds_id,UP.second_odds_id,UP.is_correct,UP.score,UP.points,UP.score,UT.user_team_id,UT.collection_id",FALSE)
           ->from(USER_TEAM.' UT')
           ->join(USER_PREDICTION.' UP',"UP.user_team_id = UT.user_team_id","LEFT")
           ->where("UT.user_team_id", $user_team_id);

        $this->db->order_by("UP.over_ball", "ASC", FALSE);
        $sql = $this->db->get();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * used to get user live overs list
     * @param array $post_data
     * @return array
    */
    public function get_user_live_overs($post_data) {
        $current_date = format_date();
        //$past_date = date('Y-m-d H:i:s',strtotime($current_date.' -7 days'));
        $sports_id = $post_data['sports_id'];
        $this->db->select("CM.collection_id,CM.league_id,CM.season_game_uid,CM.season_scheduled_date,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,CONCAT_WS('_',CM.league_id,CM.season_game_uid) as game_key,CM.status", FALSE);
        $this->db->from(USER_TEAM." as UT");
        $this->db->join(USER_CONTEST.' as UC', 'UC.user_team_id = UT.user_team_id', "INNER");
        $this->db->join(CONTEST.' as C', 'C.contest_id = UC.contest_id', "INNER");
        $this->db->join(COLLECTION.' as CM', 'CM.collection_id = C.collection_id', "INNER");
        $this->db->where("UT.user_id", $this->user_id);
        $this->db->where("CM.status","1");
        $this->db->where("C.sports_id", $sports_id);
        //$this->db->where('C.season_scheduled_date >=', $past_date);
        $this->db->where("C.status !=","1");
        $this->db->where("C.total_user_joined >= C.minimum_size",NULL);
        $this->db->group_by("C.collection_id");
        $this->db->order_by("CM.season_scheduled_date", "ASC");
        $this->db->order_by("FIELD(CM.status,1,0,2) ASC");
        $this->db->order_by("inning", "ASC");
        $this->db->order_by("overs", "ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }
}
