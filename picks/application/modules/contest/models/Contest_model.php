<?php

class Contest_model extends MY_Model {

    public function __construct() {
        parent::__construct();
       
    }

    /**
     * used to get contest details
     * @param array $post_data
     * @return array
     */
    public function get_contest_detail($post_data) {
  
        $sql = $this->db->select("C.prize_type,C.contest_id,C.sports_id,C.league_id,C.contest_unique_id,C.season_id,C.contest_name,C.contest_description,C.scheduled_date,(UNIX_TIMESTAMP(S.scheduled_date) * 1000 ) as game_starts_in,C.minimum_size,C.size,C.total_user_joined,C.multiple_lineup,C.entry_fee,C.site_rake,C.prize_pool,C.max_bonus_allowed,C.currency_type,C.prize_type,IFNULL(C.prize_distibution_detail,'[]') as prize_distibution_detail,C.guaranteed_prize,C.status,C.is_auto_recurring,C.is_custom_prize_pool,C.is_pin_contest,C.group_id,C.is_tie_breaker,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link,C.prize_value_type,IF(C.user_id > 0,'1','0') as is_private,C.user_id as contest_creater,C.host_rake,IFNULL(C.contest_title,'') as contest_title,IFNULL(C.sponsor_contest_dtl_image,'') as sponsor_contest_dtl_image,C.current_prize,S.match,S.question,S.correct,S.wrong", FALSE)
                ->from(CONTEST . " AS C")
                ->join(SEASON . " AS S", 'S.season_id = C.season_id', 'INNER'); //


        if (isset($post_data['contest_id']) && $post_data['contest_id'] != "") {
            $this->db->where('C.contest_id', $post_data['contest_id']);
        }
        $result = $sql->get()->row_array();
        return $result;
    }

    /**
     * used to get user contest joined count
     * @param array $post_data
     * @return array
     */
    public function get_user_contest_join_count($post_data) {

        $result = $this->db->select("IFNULL(COUNT(UC.user_team_id), 0) as user_joined_count,UT.user_team_id,UT.team_name", FALSE)
                ->from(USER_TEAM . " UT")
                ->join(USER_CONTEST . " UC", "UC.user_team_id = UT.user_team_id AND UC.fee_refund=0 AND UC.contest_id = ".$post_data["contest_id"], "INNER")
                ->where("UT.user_id", $this->user_id)
                ->get()
                ->row_array();
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
        $this->db->select("UT.user_id,UT.user_name,count(UT.user_id) AS user_count")
                ->from(USER_CONTEST . " UC")
                ->join(USER_TEAM . " UT", "UT.user_team_id=UC.user_team_id AND UT.season_id = ".$post_data['season_id'], "INNER")
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
        $this->db->select("GROUP_CONCAT(DISTINCT C.contest_id) as contest_ids")
                ->from(USER_TEAM . " UT")
                ->join(USER_CONTEST . " UC", "UC.user_team_id = UT.user_team_id", "INNER")
                ->join(CONTEST . " C", "C.contest_id = UC.contest_id", "INNER")
                ->where("UT.user_id", $user_id);
        $this->db->where("DATE_FORMAT(C.scheduled_date,'%Y-%m')", $current_date);
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
     * @param array $post_data
     * @return array
     */
    public function join_game($contest, $post_data) {
        $contest_data = $this->get_single_row("total_user_joined,size", CONTEST, array("contest_id" => $contest["contest_id"]));
        if(!empty($contest_data) && $contest_data['total_user_joined'] < $contest_data['size']){
            $user_contest = array();
            $user_contest['user_team_id'] = $post_data["user_team_id"];
            $user_contest['contest_id'] = $contest["contest_id"];
            $user_contest['added_date'] = format_date();
            try {
                
                //Start Transaction
                $this->db->trans_strict(TRUE);
                $this->db->trans_start();

                $this->db->insert(USER_CONTEST, $user_contest);
                $user_contest_id = $this->db->insert_id();
                if ($user_contest_id) {
                    $joined_count = $contest_data["total_user_joined"] + 1;

                    //increment contest joined count
                    $this->db->where('contest_id', $contest['contest_id']);
                    $this->db->where('total_user_joined < size');
                    $this->db->set('total_user_joined', 'total_user_joined+1', FALSE);

                    //for update prize distribution details
                    if (!empty($joined_count) && $joined_count > $contest['minimum_size'] && $joined_count <= $contest['size'] && $contest['entry_fee'] > 0) {
                        $prize_pool = $this->get_contest_prize_distribution_for_update($joined_count, $contest);
                        if (!empty($prize_pool) && isset($prize_pool['prize_pool'])) {
                            $this->db->set('prize_pool', $prize_pool['prize_pool']);
                        }
                        if (!empty($prize_pool) && isset($prize_pool['prize_distibution_detail'])) {
                            $this->db->set('prize_distibution_detail', $prize_pool['prize_distibution_detail']);
                        }
                    }
                    if($joined_count > $contest['minimum_size']){
                        $contest['total_user_joined'] = $joined_count;
                        $current_prize = reset_contest_prize_data($contest);
                        if(!empty($current_prize)){
                            $this->db->set('current_prize', json_encode($current_prize));
                        }
                    }

                    $this->db->update(CONTEST);
                    $is_contest_updated = $this->db->affected_rows();
                    if($is_contest_updated){
                        $this->db->trans_complete();
                        if ($this->db->trans_status() === FALSE )
                        {
                            $this->db->trans_rollback();
                            return array();
                        }
                        else
                        {
                            $this->db->trans_commit();
                            return array("joined_count" => $joined_count, "user_contest_id" => $user_contest_id);
                        }
                    }else{
                        $this->db->trans_rollback();
                        return array();
                    }

                    
                } else {
                    $this->db->trans_rollback();
                    return array();
                }
            }catch(Exception $e){
                $this->db->trans_rollback();
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
            $prize_pool_details = json_decode($contest['prize_distibution_detail'], TRUE);
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
        if ($USER_TEAM_contest_id) {
            $this->db->where("user_contest_id",$user_contest_id);
            $this->db->delete(USER_CONTEST);

            $contest_data = $this->get_single_row("total_user_joined", CONTEST, array("contest_id" => $contest["contest_id"]));
            $joined_count = $contest_data["total_user_joined"] - 1;
            
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

            if($joined_count < $contest['minimum_size']){
                $contest['total_user_joined'] = $joined_count;
                $current_prize = reset_contest_prize_data($contest);
                if(!empty($current_prize)){
                    $this->db->set('current_prize', json_encode($current_prize));
                }
            }

            $this->db->update(CONTEST);

            return array("joined_count" => $joined_count, "user_contest_id" => $user_contest_id);
        } else {
            return array();
        }
    }

    /**
     * Method to count user contest join coints
     */
    public function get_user_total_contest_join_count() {
        $result = $this->db->select("IFNULL(COUNT(UC.user_team_id), 0) as total_join_contest", FALSE)
                ->from(USER_TEAM . " UT")
                ->join(USER_CONTEST . " UC", "UC.user_team_id = UT.user_team_id", "INNER")
                ->where("UT.user_id", $this->user_id)
                ->get()
                ->row_array();
        return $result;
    }


    /**
     * used for get user joined fixture list
     * @param array $post_data
     * @return array
     */
    public function get_user_joined_fixtures($post_data) {
        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $offset = get_pagination_offset($page_no, $limit);
        $sports_id = $post_data['sports_id'];
        $status = $post_data['status'];
        $current_date = format_date();

        if ($status == 0 || $status == 1) {
            $w_cond = array('C.status' => 0);
        } else if ($status == 2) {
            $w_cond = array('C.status >' => 1, 'C.scheduled_date <' => $current_date);
        }

        $this->db->select("S.season_id,S.match,S.scheduled_date,(UNIX_TIMESTAMP(S.scheduled_date) * 1000) as game_starts_in,C.league_id,S.season_game_uid,UT.user_id,C.currency_type,C.sports_id,COUNT(DISTINCT UC.contest_id) as contest_count,SUM(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as won_amt,SUM(IF(C.currency_type=1, C.entry_fee, 0)) as total_entry_fee,UC.prize_data,
        SUM(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as won_bonus,
        SUM(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as won_coins,
        GROUP_CONCAT(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=3 THEN UC.game_rank WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=3 THEN UC.game_rank WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=3 THEN UC.game_rank WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=3 THEN UC.game_rank ELSE NULL END) as won_rank_list", false)
        ->select("COUNT(DISTINCT UC.user_team_id) as team_count",FALSE)
                ->from(USER_CONTEST . ' UC')
                ->join(CONTEST . ' C', 'C.contest_id = UC.contest_id', 'INNER')
                ->join(SEASON . ' S', 'S.season_id = C.season_id', 'INNER')
                ->join(USER_TEAM . ' UT', 'UC.user_team_id = UT.user_team_id', 'INNER')
                ->where("UC.fee_refund", "0")
                ->where("UT.user_id", $this->user_id)
                ->where($w_cond);
                if(!empty($sports_id)){
                    $this->db->where("C.sports_id", $sports_id);
                }else{
                    $this->db->where('S.is_pin_fixture',1);
                }
               

        if ($status == 0) {
            $this->db->where('S.status',0);
            $this->db->where('S.scheduled_date >=',$current_date);

        } else if ($status == 1) {
            $this->db->where('S.status',0);
           $this->db->where('S.scheduled_date <',$current_date);
        }else if ($status == 2) {
            $this->db->where('S.status > ',1);
           $this->db->where('S.scheduled_date <',$current_date);
        }
        

        if ($status == 2 || $status == 1) {
            $this->db->order_by("C.scheduled_date", "DESC");
            $this->db->order_by("UC.game_rank", "ASC");
        } else {
            $this->db->order_by("C.scheduled_date", "ASC");
        }
        $this->db->group_by("S.season_id");
        if (isset($limit) && isset($offset)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        if($query->num_rows() == 0){
            $result = [];
        }
       // echo $this->db->last_query();die;
        return $result;
    }


     /**
     * used for get user joined contest list by collection id
     * @param int $season_id
     * @param int $status
     * @param int $sports_id
     * @param int $deadline_time
     * @return array
     */
    public function get_user_fixture_contest($season_id, $status) {
        $post_data = $this->input->post();
        $current_date = format_date();
        $time = date(DATE_FORMAT, strtotime($current_date));

        if ($status == 0) {
            // UPCOMING
            $w_cond = array('C.status' => 0, 'C.scheduled_date >=' => $time);
        } else if ($status == 1) {
            // For contest is LIVE
            $w_cond = array('C.status' => 0, 'C.scheduled_date <' => $time);
        } else if ($status == 2) {
            // COMPLETED
            $w_cond = array('C.status >' => 1, 'C.scheduled_date <' => $current_date);
        }

        $pre_query = "(SELECT IFNULL(COUNT(UT.user_team_id), 0) as UT_count, G.contest_id FROM " . $this->db->dbprefix(USER_TEAM) . " AS UT 
                INNER JOIN  " . $this->db->dbprefix(USER_CONTEST) . " UC  ON UT.user_team_id = UC.user_team_id 
                INNER JOIN " . $this->db->dbprefix(CONTEST) . " G ON G.contest_id = UC.contest_id 
                WHERE UT.user_id= " . $this->user_id . " AND UT.season_id=".$season_id." GROUP BY G.contest_id)";


        $select = "C.prize_type,C.contest_id,C.contest_unique_id,C.guaranteed_prize,C.season_id,C.sports_id,C.league_id,C.group_id,C.scheduled_date,C.size,C.minimum_size,C.contest_name,C.site_rake,C.entry_fee,C.prize_pool,C.total_user_joined,C.prize_distibution_detail,C.multiple_lineup,C.prize_type,C.status,UC.user_contest_id,UC.total_score,UC.game_rank,UC.is_winner,UT.user_team_id,UT.user_id,UT.team_name,IFNULL(UCC.UT_count, '0') as user_joined_count,C.max_bonus_allowed,UC.prize_data,C.currency_type,IFNULL(C.contest_title,'') as contest_title,MG.group_name,C.is_tie_breaker,FLOOR(JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount'))) as prize_amount,L.league_name";
        $this->db->select($select, false)
                ->from(USER_CONTEST . ' UC')
                ->join(USER_TEAM . ' UT', 'UC.user_team_id = UT.user_team_id', 'INNER')
                ->join(CONTEST . ' C', 'C.contest_id = UC.contest_id', 'INNER')
                ->join(LEAGUE . ' L', 'L.league_id = C.league_id', 'INNER')
                ->join(MASTER_GROUP . ' MG', 'MG.group_id = C.group_id', 'INNER')
                ->join($pre_query . ' as UCC', 'UCC.contest_id = C.contest_id', 'INNER')
                ->where("UC.fee_refund", "0")
                ->where("UT.user_id", $this->user_id)
                ->where($w_cond);

        $this->db->where("C.season_id", $season_id);

        if (isset($post_data['league_id']) && $post_data['league_id'] != "") {
            $this->db->where("C.league_id", $post_data['league_id']);
        }


        if ($status == 2) {
            $this->db->order_by("prize_amount", "DESC");
        } else {
            $this->db->order_by("C.scheduled_date", "ASC");
        }

        if ($status == 2 && MYCONTEST_CONTEST_TEAMS_LIMIT > 0) {
            $this->db->limit(MYCONTEST_CONTEST_TEAMS_LIMIT, "0");
        }

        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        return $result;
    }

   /**
    * Get Contest Leaderboard for top three user
    */
   public function get_contest_leaderboard_top_three($post_data)
   {
        $total_score_sort = "DESC";

        $this->db->select("UT.user_team_id,UT.user_name,UT.team_name,UC.is_winner,IFNULL(UC.total_score, '') AS total_score,IF(UC.game_rank > 0,UC.game_rank,RANK() OVER (ORDER BY UC.total_score $total_score_sort)) as game_rank,UC.user_contest_id,UC.prize_data,UT.user_id",FALSE)
        ->from(USER_CONTEST.' UC')
        ->join(USER_TEAM.' UT',"UC.user_team_id = UT.user_team_id")
        ->where("UC.fee_refund", "0")
        ->where("UC.contest_id", $post_data["contest_id"]);

        $this->db->order_by("UC.game_rank", "ASC", FALSE);
            $this->db->limit(3,0);

        $leaderboard_data = $this->db->get()->result_array();
        if(!empty($leaderboard_data))
        {
            $user_ids =  array_column($leaderboard_data, 'user_id');
            $this->load->model('user/User_model');
            $user_details = $this->User_model->get_users_by_ids($user_ids);
            $user_details =  array_column($user_details, NULL, 'user_id');

            foreach ($leaderboard_data as $key => $value) {
                $leaderboard_data[$key]['user_name'] = $user_details[$value['user_id']]['user_name'];
                $leaderboard_data[$key]['image'] = $user_details[$value['user_id']]['image'];
            }
           
       }
       return $leaderboard_data;

    
   }

   /**
    * Get Contest Leaderboard for Won list anf others list
    * @param contest_id and user_id
    */
    public function get_contest_leaderboard($post_data='',$user_id='')
    {
        $limit = 10;
        $offset = 0;
        $page = 0;
        if(isset($post['page_size'])){
            $limit = $post['page_size'];
        }

        if(isset($post['page_no'])) {
            $page = $post['page_no']-1;
        }
        
        $offset    = $limit * $page;
        $total_score_sort = "DESC";
  
        $this->db->select("UT.user_team_id,UT.user_name,UT.team_name,UC.is_winner,IFNULL(UC.total_score, '') AS total_score,IF(UC.game_rank > 0,UC.game_rank,RANK() OVER (ORDER BY UC.total_score $total_score_sort)) as game_rank,UC.user_contest_id,UC.prize_data,UT.user_id",FALSE)
       ->from(USER_CONTEST.' UC')
       ->join(USER_TEAM.' UT',"UC.user_team_id = UT.user_team_id")
       ->where("UC.fee_refund", "0")
       ->where("UC.contest_id", $post_data["contest_id"]);

        if(!empty($user_id)){
           $this->db->order_by("FIELD ( UT.user_id, ".$this->user_id." ) DESC");      
        }
        
        $this->db->order_by("UC.game_rank", "ASC", FALSE);
        $this->db->order_by("UC.total_score", $total_score_sort, FALSE);

        if(empty($user_id)){
            $this->db->where('UT.user_id<>',$this->user_id);
            $this->db->limit($limit,$offset);
        }
    
       $sql = $this->db->get();
       $leaderboard_data    = $sql->result_array();
       if(!empty($leaderboard_data))
       {
            $user_ids =  array_column($leaderboard_data, 'user_id');
            $this->load->model('user/User_model');
            $user_details = $this->User_model->get_users_by_ids($user_ids);
            $user_details =  array_column($user_details, NULL, 'user_id');

            foreach ($leaderboard_data as $key => $value) {
                $leaderboard_data[$key]['user_name'] = $user_details[$value['user_id']]['user_name'];
                $leaderboard_data[$key]['image'] = $user_details[$value['user_id']]['image'];
            }
           
       }

       return  $leaderboard_data;
    }

    /*Get Contest team count data*/
    function get_my_contest_team_count($post_data)
    {
        $season_id = $post_data['season_id'];
        $current_date = format_date();
        $this->db->select("UT.season_id,COUNT(DISTINCT UC.contest_id) as contest_count,COUNT(DISTINCT UT.user_team_id) as team_count", false)
                ->from(USER_TEAM . ' UT')
                ->join(USER_CONTEST . ' UC', 'UC.user_team_id = UT.user_team_id AND UC.fee_refund=0', 'LEFT')
                ->where("UT.user_id", $this->user_id)
                ->where("UT.season_id", $season_id);
                
        $this->db->group_by("UT.season_id");
        $result = $this->db->get()->row_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    /**
     * used for generate contest join code
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
     * used for save contest invite code
     * @param array $invites
     * @return boolean
     */
    public function save_invites($invites) {
        $this->db->insert_batch(INVITE, $invites);
        return TRUE;
    }

    /**
     * used for get user joined fixture list
     * @param array $post_data
     * @return array
     */
    public function get_my_joined_fixtures($post_data) {
        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $offset = get_pagination_offset($page_no, $limit);
        $sports_id = $post_data['sports_id'];
       
        $current_date = format_date();
        $past_date = date('Y-m-d H:i:s',strtotime($current_date.' -7 days'));


        $this->db->select("S.season_id,S.scheduled_date,(UNIX_TIMESTAMP(S.scheduled_date)* 1000) as game_starts_in,C.league_id,UT.user_id,C.status as contest_status,C.sports_id,S.match,
        (CASE WHEN C.status=0 AND C.scheduled_date <= '{$current_date}' THEN 1 ELSE 0 END) as is_live,
        (CASE WHEN C.status=0 AND C.scheduled_date > '{$current_date}' THEN 1 ELSE 0 END) as is_upcoming,
        COUNT(DISTINCT C.contest_id) as contest_count,COUNT(DISTINCT UC.user_team_id) as team_count,C.entry_fee,prize_data,UC.is_winner,
        SUM(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as won_amt,SUM(C.entry_fee) as total_entry_fee,
        SUM(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as won_coins,
        SUM(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as won_bonus_sum,UC.game_rank", false)
     
        
                ->from(USER_CONTEST . ' UC')
                ->join(CONTEST . ' C', 'C.contest_id = UC.contest_id', 'INNER')
                ->join(SEASON . ' S', 'S.season_id = C.season_id', 'INNER')
                ->join(USER_TEAM . ' UT', 'UC.user_team_id = UT.user_team_id', 'INNER')
                ->where("UC.fee_refund", "0")
                ->where("UT.user_id", $this->user_id);
                //->where("C.sports_id", $sports_id)
                if(!empty($sports_id)){
                    $this->db->where("C.sports_id", $sports_id);        
                }else{
                    $this->db->where("S.is_pin_fixture", 1);        
                }
                $this->db->where('C.scheduled_date >=', $past_date);

    
            $this->db->order_by("is_live", "DESC");
            $this->db->order_by("is_upcoming", "DESC");
            $this->db->order_by("C.scheduled_date", "ASC");
        
        $this->db->group_by("S.season_id");
        if (isset($limit) && isset($offset)) {
            $this->db->limit($limit, $offset);
        }
        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    /**
     * used for get season free teams
     * @param array $post_data
     * @return array
     */
    public function get_season_contest_free_teams($post_data) {

        $result = $this->db->select("UT.user_team_id,UT.season_id,UT.user_id,UT.team_name")
                ->from(CONTEST . " C")
                ->join(USER_TEAM . " UT", "UT.season_id=C.season_id AND UT.user_id = ".$post_data['user_id'], "INNER")
                ->join(USER_CONTEST . " UC", "UC.user_team_id = UT.user_team_id AND UC.contest_id = C.contest_id", "LEFT")
                ->where("C.sports_id", $post_data['sports_id'])
                ->where("C.contest_id", $post_data['contest_id'])                
                ->where("UC.user_contest_id IS NULL")
                ->get()
                ->result_array();

        return $result;
    }

     /**
     * used for switch joined team
     * @param array $post_data
     * @return array
     */
    public function check_valid_user_previous_team($post_data) {
        $result = $this->db->select("UT.user_team_id,UT.user_id,UC.user_contest_id")
                ->from(USER_CONTEST . " UC")
                ->join(USER_TEAM . " UT", "UT.user_team_id=UC.user_team_id AND UT.user_id=".$post_data['user_id'], "INNER")
                ->where("UC.user_contest_id", $post_data['user_contest_id'])
                ->where("UC.contest_id", $post_data['contest_id'])                
                ->get()
                ->row_array();
        return $result;
    }

    /**
     * used for validate team for contest
     * @param array $post_data
     * @return boolean
     */
    public function check_valid_team_for_contest($post_data) {
        $result = $this->db->select("UT.user_team_id,UT.user_id")
                ->from(CONTEST . " C")
                ->join(USER_TEAM . " UT", "UT.season_id=C.season_id", "INNER")
                ->where("C.sports_id", $post_data['sports_id'])
                ->where("C.contest_id", $post_data['contest_id'])
                ->where("UT.user_team_id", $post_data['user_team_id'])
                ->where("UT.user_id", $post_data['user_id'])
                ->get()
                ->row_array();
        //echo "<pre>";print_r($result);die;
        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }

}