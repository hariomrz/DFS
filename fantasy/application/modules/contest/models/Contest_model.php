<?php
class Contest_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get scoring rules list
     * @param array $post_data
     * @return array
     */
    public function get_scoring_rules_by_category_format($post_data) {
        $lang_key = "en";
        if(isset($post_data['lang_key'])){
            $lang_key = $post_data['lang_key'];
        }
        $this->db->select('master_scoring_id,'.$lang_key.'_score_position as score_position, score_points, format, MSC.master_scoring_category_id, '.$lang_key.'_scoring_category_name as scoring_category_name,ROUND((CASE WHEN MSC.sports_id != "' . PBL_SPORTS_ID . '" AND meta_key="CAPTAIN" THEN ' . CAPTAIN_POINT . ' WHEN meta_key="VICE_CAPTAIN" THEN ' . VICE_CAPTAIN_POINT . ' ELSE score_points END),2) as score_points,ROUND(new_score_points,2) as new_score_points', FALSE)
            ->from(MASTER_SCORING_RULES.' AS MSR')
            ->join(MASTER_SCORING_CATEGORY.' AS MSC', 'MSC.master_scoring_category_id = MSR.master_scoring_category_id');

        if(!empty($post_data['format'])) {
            $this->db->where('MSR.format', $post_data['format']);
        }
        if(!empty($post_data['sports_id'])) {
            $this->db->where('MSC.sports_id', $post_data['sports_id']);
        }
        if (!empty($post_data['sports_id']) && $post_data['sports_id'] == TENNIS_SPORTS_ID && isset($post_data['no_of_sets'])) {
            $this->db->where('MSC.scoring_category_name', 'Best_of_'.$post_data['no_of_sets'].'_sets');
        }
        $this->db->order_by("MSR.master_scoring_id","ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * Used for get contest details
     * @param int $contest_id
     * @return array
     */
    public function get_contest_detail($contest_id) {
        if(!$contest_id){
            return false;
        }
        $this->db->select("C.contest_id,C.contest_unique_id,C.collection_master_id,C.sports_id,C.group_id,IFNULL(C.contest_title,'') as contest_title,IFNULL(NULLIF(C.contest_title, ''),C.contest_name) as contest_name,C.contest_description,C.season_scheduled_date,C.minimum_size,C.size,C.total_user_joined,C.multiple_lineup,C.entry_fee,C.site_rake,C.host_rake,C.prize_pool,C.max_bonus_allowed,C.currency_type,IFNULL(C.prize_distibution_detail,'[]') as prize_distibution_detail,IFNULL(C.current_prize,'[]') as current_prize,C.guaranteed_prize,C.is_auto_recurring,C.is_pin_contest,C.is_pdf_generated,C.user_id,IF(C.user_id > 0,'1','0') as is_private,C.status,C.is_tie_breaker,C.prize_value_type,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link,IFNULL(C.sponsor_contest_dtl_image,'') as sponsor_contest_dtl_image,C.is_scratchwin,CM.collection_name,CM.season_game_count,CM.is_tour_game,GROUP_CONCAT(DISTINCT CS.season_id) as season_ids,C.is_2nd_inning,L.no_of_sets,IFNULL(CM.setting,'[]') as setting", FALSE)
            ->from(CONTEST." AS C")
            ->join(COLLECTION_MASTER." AS CM", 'CM.collection_master_id = C.collection_master_id', 'INNER')
            ->join(COLLECTION_SEASON." AS CS", 'CS.collection_master_id = CM.collection_master_id', 'INNER')
            ->join(LEAGUE." AS L", 'L.league_id = C.league_id', 'INNER')
            ->where("C.contest_id",$contest_id)
            ->group_by("C.contest_id");
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used to get contest joined users list
     * @param array $post_data
     * @return array
     */
    public function get_contest_joined_users($post_data) {
        if(empty($post_data)) {
            return array();
        }

        $pagination = get_pagination_data($post_data);
        $this->db->select("LM.user_id,LM.user_name,count(LMC.lineup_master_contest_id) AS team_count")
            ->from(LINEUP_MASTER_CONTEST." LMC")
            ->join(LINEUP_MASTER." LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
            ->where("LMC.contest_id", $post_data['contest_id'])
            ->where("LMC.fee_refund", 0)
            ->order_by('LMC.lineup_master_contest_id','ASC')
            ->group_by('LM.user_id')
            ->limit($pagination['limit'], $pagination['offset']);

        if($this->user_id){
            $this->db->order_by("FIELD(user_id,".$this->user_id.") DESC");
        }
        $this->db->order_by("LMC.lineup_master_contest_id","ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get user contest joined count
     * @param int $contest_id
     * @return array
     */
    public function get_user_contest_join_count($contest_id) {

        $result = $this->db->select("IFNULL(COUNT(LMC.lineup_master_id), 0) as user_joined_count", FALSE)
                ->from(LINEUP_MASTER . " LM")
                ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.lineup_master_id = LM.lineup_master_id AND LMC.fee_refund=0 AND LMC.contest_id = ".$contest_id, "INNER")
                ->where("LM.user_id", $this->user_id)
                ->get()
                ->row_array();
        return $result;
    }

    /**
     * used to join contest
     * @param array $post_data
     * @return array
     */
    public function join_game($contest) {
        if(empty($contest)){
            return array();
        }
        try {
            //Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();

            $lmc = array();
            $lmc['lineup_master_id'] = $contest["lm_id"];
            $lmc['contest_id'] = $contest["contest_id"];
            $lmc['created_date'] = format_date();
            $this->db->insert(LINEUP_MASTER_CONTEST,$lmc);
            $lmc_id = $this->db->insert_id();
            if($lmc_id) {
                $contest["total_user_joined"] = $contest["total_user_joined"] + 1;

                //increment contest joined count
                $this->db->where('contest_id', $contest['contest_id']);
                $this->db->where('total_user_joined < size');
                $this->db->set('total_user_joined','total_user_joined+1', FALSE);

                //for update prize distribution details
                if ($contest["total_user_joined"] > $contest['minimum_size'] && $contest["total_user_joined"] <= $contest['size'] && $contest['entry_fee'] > 0) {
                    $prize_data = $this->get_contest_prize_distribution($contest);
                    if(!empty($prize_data) && isset($prize_data['prize_pool'])) {
                        $this->db->set('prize_pool', $prize_data['prize_pool']);
                    }
                    if(!empty($prize_data) && isset($prize_data['prize_distibution_detail'])) {
                        $this->db->set('prize_distibution_detail', $prize_data['prize_distibution_detail']);
                    }
                }
                if($contest["total_user_joined"] > $contest['minimum_size']){
                    $current_prize = reset_contest_prize_data($contest);
                    if(!empty($current_prize)){
                        $this->db->set('current_prize', json_encode($current_prize));
                    }
                }

                $this->db->update(CONTEST);
                $result = $this->db->affected_rows();
                if($result){
                    $txn_status = 1;
                    $txn_id = 0;
                    //entry fee deduct from user wallet
                    if($contest['entry_fee'] > 0){
                        $order_arr = array();
                        $order_arr['user_id'] = $this->user_id;
                        $order_arr['source_id'] = $lmc_id;
                        $order_arr['reference_id'] = $contest['contest_id'];
                        $order_arr['real_amount'] = $this->contest_entry['real'];
                        $order_arr['bonus_amount'] = $this->contest_entry['bonus'];
                        $order_arr['winning_amount'] = $this->contest_entry['winning'];
                        $order_arr['points'] = $this->contest_entry['coin'];
                        $order_arr['cb_amount'] = $this->contest_entry['cb_amount'];
                        $order_arr['custom_data'] = array("contest_name"=>$contest['contest_name']);
                        $this->load->model("user/User_model");
                        $join_result = $this->User_model->deduct_entry_fee($order_arr);
                        if(!isset($join_result['result']) || $join_result['result'] != "1"){
                            $txn_status = 0;
                        }else{
                            $txn_id = isset($join_result['order_id']) ? $join_result['order_id'] : 0;
                        }
                    }
                    if($txn_status == "1"){
                        $this->db->trans_complete();
                        if ($this->db->trans_status() === FALSE )
                        {
                            $this->db->trans_rollback();
                            return array();
                        }
                        else
                        {
                            $this->db->trans_commit();
                            return array("joined_count" => $contest["total_user_joined"], "lmc_id" => $lmc_id,"order_id"=>$txn_id);
                        }
                    }else{
                        $this->db->trans_rollback();
                        return array();
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
    }

    /**
     * used to get contest prize details for update
     * @param int $join_count
     * @param array $contest
     * @return array
     */
    public function get_contest_prize_distribution($contest) {
        if((isset($contest['guaranteed_prize']) && $contest['guaranteed_prize'] == '2')) {
            return array();
        }

        $total_amount = $contest['total_user_joined'] * $contest['entry_fee'];
        if(isset($contest['is_private']) && $contest['is_private'] == "1"){
            $prize_pool_percent = 100 - $contest['site_rake'] - $contest['host_rake'];
        }else{
            $prize_pool_percent = 100;
        }
        //new prize pool
        $prize_pool = truncate_number_only(($prize_pool_percent / 100) * $total_amount);
        $update_data = array();
        $update_data['prize_pool'] = $prize_pool;
        $prize_details = json_decode($contest['prize_distibution_detail'], TRUE);
        foreach($prize_details as $key => $value) {
            if(!isset($value['prize_type']) || $value['prize_type'] != 3){
                $person_count = ($value['max'] - $value['min']) + 1;
                $per_person = truncate_number_only((($prize_pool * $value['per']) / 100) / $person_count);
                if(isset($value['prize_type']) && $value['prize_type'] == 2){
                    $per_person = ceil($per_person);
                }
                $prize_details[$key]["amount"] = $per_person;
                $prize_details[$key]["min_value"] = number_format(($per_person * $person_count),"2",".","");
            }
        }
        $update_data['prize_distibution_detail'] = json_encode($prize_details);
        return $update_data;
    }

    /**
     * Method to count user contest join coints
     */
    public function get_user_total_contest_join_count() {
        $result = $this->db->select("IFNULL(COUNT(LMC.lineup_master_id), 0) as total_join_contest", FALSE)
                ->from(LINEUP_MASTER . " LM")
                ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.lineup_master_id = LM.lineup_master_id", "INNER")
                ->where("LM.user_id", $this->user_id)
                ->get()
                ->row_array();
        return $result;
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
     * used for get user joined fixture list
     * @param array $post_data
     * @return array
     */
    public function get_lobby_joined_fixtures($sports_id) {
        $offset = 0;
        $limit = RECORD_LIMIT;
        $current_date = format_date();
        $past_date = date('Y-m-d H:i:s',strtotime($current_date.' -7 days'));
        $this->db->select("CM.collection_master_id,CM.season_game_count,CM.collection_name,CM.season_scheduled_date,CM.status,C.league_id,C.sports_id,COUNT(DISTINCT C.contest_id) as contest_count,COUNT(DISTINCT LMC.lineup_master_id) as team_count,(CASE WHEN CM.status=0 AND C.season_scheduled_date <= '{$current_date}' THEN 1 ELSE 0 END) as is_live,(CASE WHEN CM.status=0 AND C.season_scheduled_date > '{$current_date}' THEN 1 ELSE 0 END) as is_upcoming",FALSE)
                ->from(LINEUP_MASTER.' LM')
                ->join(LINEUP_MASTER_CONTEST.' LMC', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
                ->join(CONTEST.' C', 'C.contest_id = LMC.contest_id', 'INNER')
                ->join(COLLECTION_MASTER.' CM', 'CM.collection_master_id = C.collection_master_id', 'INNER')
                ->where("LM.user_id", $this->user_id)
                ->where("LMC.fee_refund", "0")
                ->where("C.is_2nd_inning", "0")
                ->where("C.sports_id", $sports_id)
                ->where('C.season_scheduled_date >=', $past_date)
                ->order_by("is_live", "DESC")
                ->order_by("is_upcoming", "DESC")
                ->order_by("(CASE WHEN is_upcoming = 1 THEN C.season_scheduled_date ELSE 1 END)","ASC")
                ->order_by("(CASE WHEN is_upcoming = 0 THEN C.season_scheduled_date ELSE 1 END)","DESC")
                ->group_by("CM.collection_master_id");

        if (isset($limit) && isset($offset)) {
            //$this->db->limit($limit, $offset);
        }
        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    /**
     * used for get user joined fixture list
     * @param array $post_data
     * @return array
     */
    public function get_user_joined_fixtures($post_data) {
        $current_date = format_date();
        $pagination = get_pagination_data($post_data);
        $sports_id = $post_data['sports_id'];
        $status = $post_data['status'];
        $this->db->select("CM.collection_master_id,CM.collection_name,CM.season_game_count,CM.season_scheduled_date,IFNULL(CM.2nd_inning_date,'') as 2nd_inning_date,CM.is_gc,C.league_id,C.currency_type,COUNT(DISTINCT LMC.contest_id) as contest_count,COUNT(DISTINCT LMC.lineup_master_id) as team_count,SUM(IF(C.currency_type=1, C.entry_fee, 0)) as total_entry_fee,(SUM(LMC.amount)/COUNT(DISTINCT CS.season_id)) as won_amt,(SUM(LMC.bonus)/COUNT(DISTINCT CS.season_id)) as won_bonus,ROUND(SUM(LMC.coin)/COUNT(DISTINCT CS.season_id)) as won_coins,IFNULL(GROUP_CONCAT(NULLIF(LMC.merchandise,'')),'') as won_merchandise,IFNULL(L.league_display_name,L.league_name) AS league_name,GROUP_CONCAT(DISTINCT CS.season_id) as season_ids,CM.is_tour_game", false)
                ->from(LINEUP_MASTER_CONTEST.' LMC')
                ->join(LINEUP_MASTER.' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
                ->join(COLLECTION_MASTER.' CM', 'LM.collection_master_id = CM.collection_master_id', 'INNER')
                ->join(COLLECTION_SEASON . ' CS', 'CM.collection_master_id = CS.collection_master_id', 'INNER')
                ->join(CONTEST.' C', 'C.contest_id = LMC.contest_id', 'INNER')
                ->join(LEAGUE.' L', 'C.league_id = L.league_id', 'INNER')
                ->where("LMC.fee_refund", "0")
                ->where("LM.user_id", $this->user_id)
                ->where("C.sports_id", $sports_id);

        if($status == 0){
            $this->db->where('CM.status',0);
            $this->db->where('C.status',0);
            $this->db->where('(CM.season_scheduled_date >= "'.$current_date.'" OR (CM.2nd_inning_date >= "'.$current_date.'" AND C.is_2nd_inning=1))',NULL);
            $this->db->order_by("C.season_scheduled_date", "ASC");
        }else if($status == 1){
            $this->db->where('CM.status',0);
            $this->db->where('C.status',0);
            $this->db->where('C.season_scheduled_date < ',$current_date);
            $this->db->where('(CM.season_scheduled_date < "'.$current_date.'" OR (CM.2nd_inning_date < "'.$current_date.'" AND C.is_2nd_inning=1))',NULL);
            $this->db->order_by("C.season_scheduled_date", "DESC");
            $this->db->order_by("LMC.game_rank", "ASC");
        }else if($status == 2){
            $this->db->where('C.status > ',1);
            $this->db->where('C.season_scheduled_date < ',$current_date);
            $this->db->order_by("C.season_scheduled_date", "DESC");
            $this->db->order_by("LMC.game_rank", "ASC");
        }
        $this->db->group_by("CM.collection_master_id");
        if (isset($pagination['limit']) && isset($pagination['offset'])) {
            $this->db->limit($pagination['limit'], $pagination['offset']);
        }
        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    /**
     * Used for get user joined contest list by collection id
     * @param array $post_data
     * @return array
     */
    public function get_user_joined_contest($cm_id) {
        $post_data = $this->input->post();
        $pre_query = "(SELECT IFNULL(COUNT(LM.lineup_master_id), 0) as lm_count,LMC.contest_id FROM ".$this->db->dbprefix(LINEUP_MASTER)." AS LM INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." LMC  ON LM.lineup_master_id = LMC.lineup_master_id WHERE LMC.fee_refund = 0 AND LM.user_id= ".$this->user_id." AND LM.collection_master_id=".$cm_id." GROUP BY LMC.contest_id)";

        $this->db->select("C.contest_id,C.contest_unique_id,C.collection_master_id,C.group_id,C.season_scheduled_date,C.entry_fee,C.size,C.minimum_size,C.site_rake,C.max_bonus_allowed,C.total_user_joined,C.prize_pool,C.guaranteed_prize,C.multiple_lineup,C.prize_type,C.status,C.is_tie_breaker,C.currency_type,IFNULL(C.contest_title,'') as contest_title,IFNULL(NULLIF(C.contest_title, ''),C.contest_name) as contest_name,C.prize_distibution_detail,C.is_scratchwin,C.is_gst_report,C.user_id,IF(C.user_id > 0,'1','0') as is_private,LMC.lineup_master_contest_id,LMC.total_score,LMC.game_rank,LMC.is_winner,LMC.amount,LMC.bonus,LMC.coin,IFNULL(LMC.merchandise,'') as merchandise,LM.lineup_master_id,LM.team_name,LM.is_pl_team,LM.booster_id,IFNULL(LMCC.lm_count, '0') as user_joined_count,MG.group_name,C.is_2nd_inning", false)
                ->from(LINEUP_MASTER_CONTEST.' LMC')
                ->join(LINEUP_MASTER.' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
                ->join(CONTEST.' C', 'C.contest_id = LMC.contest_id', 'INNER')
                ->join(MASTER_GROUP.' MG', 'MG.group_id = C.group_id', 'INNER')
                ->join($pre_query.' as LMCC', 'LMCC.contest_id = C.contest_id', 'LEFT')
                ->where("LMC.fee_refund", "0")
                ->where("LM.user_id", $this->user_id)
                ->where("C.collection_master_id", $cm_id)
                ->where("C.status !=",1);

        if(isset($post_data['is_2nd_inning']) && in_array($post_data['is_2nd_inning'],[0,1])){
            $this->db->where('C.is_2nd_inning',$post_data['is_2nd_inning']);
        }
        $this->db->order_by("LMC.amount", "DESC");
        $this->db->order_by("LMC.bonus", "DESC");
        $this->db->order_by("LMC.coin", "DESC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used for get collection free teams
     * @param array $post_data
     * @return array
     */
    public function get_fixture_contest_free_teams($contest_id) {
        $result = $this->db->select("LM.lineup_master_id,LM.collection_master_id,LM.user_id,LM.team_name,LM.is_2nd_inning")
                ->from(CONTEST." C")
                ->join(LINEUP_MASTER." LM", "LM.collection_master_id=C.collection_master_id AND LM.user_id = ".$this->user_id, "INNER")
                ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.lineup_master_id = LM.lineup_master_id AND LMC.contest_id = C.contest_id", "LEFT")
                ->where("C.contest_id", $contest_id)                
                ->where("LMC.lineup_master_contest_id IS NULL")
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
        $result = $this->db->select("LM.lineup_master_id,LM.user_id,LMC.lineup_master_contest_id")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id AND LM.user_id=".$post_data['user_id'], "INNER")
                ->where("LMC.lineup_master_contest_id", $post_data['lineup_master_contest_id'])
                ->where("LMC.contest_id", $post_data['contest_id'])                
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
        $result = $this->db->select("LM.lineup_master_id,LM.user_id")
                ->from(CONTEST." C")
                ->join(LINEUP_MASTER." LM", "LM.collection_master_id=C.collection_master_id", "INNER")
                ->where("C.contest_id", $post_data['contest_id'])
                ->where("LM.lineup_master_id", $post_data['lineup_master_id'])
                ->where("LM.user_id", $post_data['user_id'])
                ->get()
                ->row_array();
        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * used for get user joined contest list by collection id
     * @param int $collection_master_id
     * @return array
     */
    public function get_user_match_contest($cm_id) {
        $current_date = format_date();
        $this->db->select("C.contest_id,C.sports_id,C.league_id,C.group_id,C.contest_unique_id,C.collection_master_id,IFNULL(C.contest_title,'') as contest_title,IFNULL(NULLIF(C.contest_title, ''),C.contest_name) as contest_name,C.season_scheduled_date,C.size,C.minimum_size,C.total_user_joined,C.entry_fee,C.site_rake,C.prize_pool,C.guaranteed_prize,C.multiple_lineup,C.prize_distibution_detail,C.currency_type,C.prize_type,C.contest_type,C.max_bonus_allowed,IF(C.user_id > 0, 1,0) as is_private_contest,C.is_tie_breaker,C.status,MG.group_name,LMC.lineup_master_contest_id,LMC.total_score,LMC.last_rank,LMC.is_winner,LMC.amount,LMC.bonus,LMC.coin,IFNULL(LMC.merchandise,'') as merchandise,LM.lineup_master_id,LM.team_name,LM.is_pl_team,LM.team_data,C.is_gst_report,IF(LMC.game_rank > 0,LMC.game_rank,RANK() OVER (ORDER BY LMC.total_score DESC,IF(C.is_tie_breaker > 0,LMC.lineup_master_contest_id,1) ASC)) as game_rank,C.is_2nd_inning", false)
            ->from(LINEUP_MASTER_CONTEST.' AS LMC')
            ->join(LINEUP_MASTER.' AS LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
            ->join(CONTEST.' AS C', 'C.contest_id = LMC.contest_id', 'INNER')
            ->join(MASTER_GROUP.' MG', 'MG.group_id = C.group_id', 'INNER')
            ->where("LMC.fee_refund", "0")
            ->where("LM.user_id", $this->user_id)
            ->where("C.collection_master_id", $cm_id)
            ->where("C.season_scheduled_date <= ",$current_date)
            ->order_by("amount", "DESC");
            
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function get_gst_contest_detail($lmc_id){
        $this->db->select("C.contest_id,C.entry_fee,C.season_scheduled_date,C.entry_fee,C.status,C.is_gst_report,LM.team_name", false)
                ->from(LINEUP_MASTER_CONTEST.' AS LMC')
                ->join(LINEUP_MASTER.' AS LM', 'LM.lineup_master_id = LMC.lineup_master_id', 'INNER')
                ->join(CONTEST.' AS C', 'C.contest_id = LMC.contest_id', 'INNER')
                ->where("LMC.fee_refund", "0")
                ->where("LMC.lineup_master_contest_id", $lmc_id)
                ->where("LM.user_id", $this->user_id);
        $result = $this->db->get()->row_array();
        return $result;
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
        $rank_str = "";
        if(isset($post_data['is_tie_breaker']) && $post_data['is_tie_breaker'] == "1"){
            $rank_str = ",LMC.lineup_master_contest_id ASC";
        }
        $contest_id = $post_data['contest_id'];
        $pagination = get_pagination_data($post_data);

        $this->db->select("LMC.lineup_master_contest_id,LMC.total_score,LMC.is_winner,LMC.amount,LMC.bonus,LMC.coin,IFNULL(LMC.merchandise,'') as merchandise,LM.lineup_master_id,LM.team_name,LM.is_pl_team,LM.user_id,LM.user_name,IF(LMC.game_rank > 0,LMC.game_rank,RANK() OVER (ORDER BY LMC.total_score DESC".$rank_str.")) as game_rank,LMC.booster_points,LM.booster_id",FALSE)
           ->from(LINEUP_MASTER_CONTEST.' LMC')
           ->join(LINEUP_MASTER.' LM',"LM.lineup_master_id = LMC.lineup_master_id","INNER")
           ->where("LMC.fee_refund", "0")
           ->where("LMC.contest_id", $contest_id)
           ->order_by("LMC.game_rank", "ASC")
           ->order_by("LMC.total_score","DESC");

        if(isset($post_data['user_id']) && $post_data['user_id'] != "")
        {
            $this->db->where('LM.user_id',$post_data['user_id']);
        }else{
            $this->db->where('LM.user_id<>',$this->user_id);
            $this->db->limit($pagination['limit'],$pagination['offset']);
        }
    
        $sql = $this->db->get();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * used for get user team info
     * @param array $post_data
     * @return array
     */
    public function get_contest_teams_by_lmc_ids($lmc_ids) {
        $this->db->select("LMC.lineup_master_contest_id,LM.lineup_master_id,LMC.total_score,LM.user_id,LM.user_name,LM.team_name,LM.team_data,LM.booster_id,LMC.booster_points",FALSE)
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
                ->where_in("LMC.lineup_master_contest_id", $lmc_ids);
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used for get logged in user joined contest leaderboard data
     * @param array $post_data
     * @return array
     */
    public function get_contest_user_leaderboard_teams($contest_id) {
        $this->db->select("LMC.lineup_master_contest_id,LM.lineup_master_id,LM.user_name,LM.team_name,LMC.is_winner,LMC.total_score,LMC.game_rank,LMC.amount,LMC.bonus,LMC.coin,IFNULL(LMC.merchandise,'') as merchandise", FALSE)
                ->from(LINEUP_MASTER_CONTEST." LMC")
                ->join(LINEUP_MASTER." LM", "LMC.lineup_master_id = LM.lineup_master_id AND LM.user_id = ".$this->user_id, "INNER")
                ->where("LMC.fee_refund", "0")                
                ->where("LMC.contest_id", $contest_id)                
                ->order_by("LMC.game_rank", "ASC", FALSE)
                ->order_by("LMC.total_score", "DESC", FALSE);
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get team wise bench player counts
     * @param array $lineup_master_ids
     * @return array
    */
    public function get_team_bench_players_count($lineup_master_ids) {
        $this->db->select("BP.lineup_master_id, IF(COUNT(BP.bench_player_id) > 0,1,0) as bench_applied", FALSE);
        $this->db->from(BENCH_PLAYER . " as BP");
        $this->db->where_in('BP.lineup_master_id', $lineup_master_ids);
        $this->db->group_by("BP.lineup_master_id");
        return $this->db->get()->result_array();
    }
}
