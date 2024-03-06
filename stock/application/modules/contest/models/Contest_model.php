<?php

class Contest_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->user_db = $this->load->database('user_db', TRUE);
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
       
        $current_date = format_date();
        $past_date = date('Y-m-d H:i:s',strtotime($current_date.' -7 days'));
        $this->db->select('IFNULL(CM.custom_message,"") as custom_message', FALSE);        
        $this->db->select("CM.collection_id, CM.name as collection_name, CM.scheduled_date, CM.end_date, C.category_id, C.status, CM.status AS collection_status,
        (CASE WHEN C.status=0 AND C.scheduled_date <= '{$current_date}' THEN 1 ELSE 0 END) as is_live,
        (CASE WHEN C.status=0 AND C.scheduled_date > '{$current_date}' THEN 1 ELSE 0 END) as is_upcoming,
        COUNT(DISTINCT C.contest_id) as contest_count, COUNT(DISTINCT LMC.lineup_master_id) as team_count", false)
                ->from(LINEUP_MASTER_CONTEST . ' LMC')
                ->join(LINEUP_MASTER . ' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
                ->join(COLLECTION . ' CM', 'LM.collection_id = CM.collection_id', 'INNER')
                ->join(CONTEST . ' C', 'C.contest_id = LMC.contest_id', 'INNER')
                ->where("LMC.fee_refund", "0")
                ->where("LM.user_id", $this->user_id, FALSE)
                ->where('C.scheduled_date >=', $past_date);
    
        $stock_type = 1;
        if(!empty($post_data['stock_type']))
        {
            $stock_type = $post_data['stock_type'];
        }

        $this->db->where('CM.stock_type', $stock_type);


            $this->db->order_by("is_live", "DESC");
            $this->db->order_by("is_upcoming", "DESC");
            $this->db->order_by("C.scheduled_date", "ASC");
        
        $this->db->group_by("LM.collection_id");
        if (isset($limit) && isset($offset)) {
            $this->db->limit($limit, $offset);
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
        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $offset = get_pagination_offset($page_no, $limit);
        
        $status = $post_data['status'];
        $current_date = format_date();
        if ($status == 0 || $status == 1) {
            $w_cond = array('C.status' => 0);
        } else if ($status == 2) {
            $w_cond = array('C.status >' => 1, 'C.scheduled_date <' => $current_date);
        }

        $this->db->select("CM.collection_id,C.scheduled_date,C.category_id,C.status,C.currency_type,CM.name as collection_name,COUNT(DISTINCT LMC.contest_id) as contest_count,CM.end_date");
        $this->db->select('IFNULL(CM.custom_message,"") as custom_message', FALSE);
        $this->db->select('IFNULL(LMC.prize_data,"") as prize_data', FALSE);
        $this->db->select('IFNULL(CM.score_updated_date,"") as score_updated_date', FALSE);
        
        $this->db->select("SUM(IF(C.currency_type=1, C.entry_fee, 0)) as total_entry_fee,
        SUM(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].amount')) ELSE 0 END) as won_amt,        
        SUM(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].amount')) ELSE 0 END) as won_bonus,
        SUM(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].amount')) ELSE 0 END) as won_coins,
        GROUP_CONCAT(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].name')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].name')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].name')) WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].prize_type'))=3 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].name')) ELSE '' END) as won_marchandise_list,
        GROUP_CONCAT(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=3 THEN LMC.game_rank WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[1].prize_type'))=3 THEN LMC.game_rank WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[2].prize_type'))=3 THEN LMC.game_rank WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, '$[3].prize_type'))=3 THEN LMC.game_rank ELSE '' END) as won_rank_list", false)
                ->from(LINEUP_MASTER_CONTEST . ' LMC')
                ->join(LINEUP_MASTER . ' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
                ->join(COLLECTION . ' CM', 'LM.collection_id = CM.collection_id', 'INNER')
                ->join(CONTEST . ' C', 'C.contest_id = LMC.contest_id', 'INNER')
                ->where("LMC.fee_refund", "0")
                ->where("LM.user_id", $this->user_id, FALSE)
                ->where($w_cond);

        if ($status == 0) {
            $this->db->where('C.scheduled_date >=', $current_date);
        } else if ($status == 1) {
            $this->db->where('C.scheduled_date <', $current_date);
        }

        $stock_type = 1;
		if(!empty($post_data['stock_type']))
		{
			$stock_type = $post_data['stock_type'];
		}

        $this->db->where('CM.stock_type', $stock_type);

        if ($status == 2 || $status == 1) {
            $this->db->order_by("C.scheduled_date", "DESC");
            $this->db->order_by("LMC.game_rank", "ASC");
        } else {
            $this->db->order_by("C.scheduled_date", "ASC");
        }
        $this->db->group_by("LM.collection_id");
        if (isset($limit) && isset($offset)) {
            $this->db->limit($limit, $offset);
        }
        $result = $this->db->get()->result_array();

        if(isset($post_data['is_debug']))
        {
            echo $this->db->last_query();die;
        }
        return $result;
    }

    /**
     * used for get user joined contest list by collection id
     * @param int $collection_id
     * @param int $status
     * @param int $deadline_time
     * @return array
     */
    public function get_user_fixture_contest($collection_id, $status) {
        $current_date = format_date();
        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
        $time = date(DATE_FORMAT, strtotime($current_date . " +" . $deadline_time . " minute"));
        if ($status == 0) { // UPCOMING
            $w_cond = array('C.status' => 0, 'C.scheduled_date >=' => $time);
        } else if ($status == 1) { // For contest is LIVE
            $w_cond = array('C.status' => 0, 'C.scheduled_date <' => $time);
        } else if ($status == 2) { // COMPLETED
            $w_cond = array('C.status >' => 1, 'C.scheduled_date <' => $current_date);
        }

        $pre_query = "(SELECT IFNULL(COUNT(LM.lineup_master_id), 0) as lm_count, G.contest_id FROM " . $this->db->dbprefix(LINEUP_MASTER) . " AS LM 
				INNER JOIN  " . $this->db->dbprefix(LINEUP_MASTER_CONTEST) . " LMC  ON LM.lineup_master_id = LMC.lineup_master_id 
				INNER JOIN " . $this->db->dbprefix(CONTEST) . " G ON G.contest_id = LMC.contest_id 
				WHERE LM.user_id= " . $this->user_id . " GROUP BY G.contest_id)";

        $select = "C.contest_id,C.contest_unique_id,C.category_id,C.prize_type,C.guaranteed_prize,C.group_id,C.scheduled_date,C.size,C.minimum_size,C.contest_name,C.site_rake,C.entry_fee,C.prize_pool,C.total_user_joined,C.prize_distibution_detail,C.multiple_lineup,C.status,C.currency_type,IFNULL(C.contest_title,'') as contest_title,C.contest_access_type,C.user_id as contest_creater,C.max_bonus_allowed,IF(C.user_id > 0, 1,0) as is_private_contest,
        LMC.lineup_master_contest_id,LMC.total_score,LMC.game_rank,LMC.is_winner,LMC.prize_data,
        LM.lineup_master_id,LM.user_id,LM.team_name,
        IFNULL(LMCC.lm_count, '0') as user_joined_count,MG.group_name,LMC.last_score,LMC.percent_change";
        $this->db->select($select, false)
                ->from(LINEUP_MASTER_CONTEST . ' LMC')
                ->join(LINEUP_MASTER . ' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
                ->join(CONTEST . ' C', 'C.contest_id = LMC.contest_id', 'INNER')
                ->join(MASTER_GROUP . ' MG', 'MG.group_id = C.group_id', 'INNER')
                ->join($pre_query . ' as LMCC', 'LMCC.contest_id = C.contest_id', 'LEFT')
                ->where("LMC.fee_refund", 0)
                ->where("LM.user_id", $this->user_id, FALSE)
                ->where($w_cond);

        $this->db->where("C.collection_id", $collection_id);

        $category_id = $this->input->post("category_id");
        if (!empty($category_id)) {
            $this->db->where("C.category_id", $category_id);
        }

        if ($status == 2) {
            $this->db->order_by("C.scheduled_date", "DESC");
            $this->db->order_by("LMC.game_rank", "ASC");
        } else {
            $this->db->order_by("C.scheduled_date", "ASC");
        }

        if ($status == 2 && MYCONTEST_CONTEST_TEAMS_LIMIT > 0) {
            $this->db->limit(MYCONTEST_CONTEST_TEAMS_LIMIT, "0");
        }

        $result = $this->db->get()->result_array();
        return $result;
    }

    function get_my_contest_team_count($post_data)
    {
        $collection_id = $post_data['collection_id'];
        $current_date = format_date();
        $this->db->select("LM.collection_id,COUNT(DISTINCT LMC.contest_id) as contest_count,COUNT(DISTINCT LM.lineup_master_id) as team_count", false)
                ->from(LINEUP_MASTER . ' LM')
                ->join(LINEUP_MASTER_CONTEST . ' LMC', 'LMC.lineup_master_id = LM.lineup_master_id AND LMC.fee_refund=0', 'LEFT')
                ->where("LM.user_id", $this->user_id)
                ->where("LM.collection_id", $collection_id);
                
        $this->db->group_by("LM.collection_id");
        $result = $this->db->get()->row_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    /**
     * used to get contest details
     * @param array $post_data
     * @return array
     */
    public function get_contest_detail($post_data) {
        $this->db->select("C.prize_type,C.contest_id,C.contest_unique_id,C.collection_id,C.contest_name,C.contest_description,C.scheduled_date,C.minimum_size,C.size,C.total_user_joined,C.multiple_lineup,C.entry_fee,C.site_rake,C.prize_pool,C.max_bonus_allowed,C.currency_type,C.prize_type,IFNULL(C.prize_distibution_detail,'[]') as prize_distibution_detail,C.guaranteed_prize,C.status,C.is_auto_recurring,C.is_custom_prize_pool,CM.name as collection_name,C.is_pdf_generated,C.is_pin_contest,C.group_id,C.is_tie_breaker,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link,C.prize_value_type,IF(C.user_id > 0,'1','0') as is_private,C.user_id as contest_creater,IFNULL(C.contest_title,'') as contest_title,IFNULL(C.sponsor_contest_dtl_image,'') as sponsor_contest_dtl_image,MG.group_name,C.category_id,CM.end_date,CM.stock_type", FALSE);
        $this->db->select('IFNULL(CM.score_updated_date,"") as score_updated_date', FALSE);
        $sql = $this->db->from(CONTEST . " AS C")
                        ->join(COLLECTION . " AS CM", 'CM.collection_id = C.collection_id', 'INNER')
                        ->join(MASTER_GROUP . " AS MG", 'C.group_id = MG.group_id', 'INNER');
        
        if (isset($post_data['contest_unique_id']) && $post_data['contest_unique_id'] != "") {
            $this->db->where('C.contest_unique_id', $post_data['contest_unique_id']);
        }
        if (isset($post_data['contest_id']) && $post_data['contest_id'] != "") {
            $this->db->where('C.contest_id', $post_data['contest_id']);
        }
        $result = $sql->get()->row_array();

        //echo $this->db->last_query();
        return $result;
    }

     /**
     * used to get user contest joined count
     * @param array $post_data
     * @return array
     */
    public function get_user_contest_join_count($post_data) {

        $result = $this->db->select("IFNULL(COUNT(LMC.lineup_master_id), 0) as user_joined_count,LM.lineup_master_id,LM.team_name", FALSE)
                ->from(LINEUP_MASTER . " LM")
                ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.lineup_master_id = LM.lineup_master_id AND LMC.fee_refund=0 AND LMC.contest_id = ".$post_data["contest_id"], "INNER")
                ->where("LM.user_id", $this->user_id)
                ->get()
                ->row_array();
        return $result;
    }

      /**
     * used for get user total invested amount and and check it with self exlusion limit
     * @param int $user_id
     * @return boolean
     */
    public function user_join_contest_ids($user_id) {
        #get user joined contest ids
      
        $current_date = format_date("today", "Y-m");
        $this->db->select("GROUP_CONCAT(DISTINCT C.contest_id) as contest_ids")
                ->from(LINEUP_MASTER . " LM")
                ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.lineup_master_id = LM.lineup_master_id", "INNER")
                ->join(CONTEST . " C", "C.contest_id = LMC.contest_id", "INNER")
                ->where("LM.user_id", $user_id);
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
            $lineup_master_contest = array();
            $lineup_master_contest['lineup_master_id'] = $post_data["lineup_master_id"];
            $lineup_master_contest['contest_id'] = $contest["contest_id"];
            $lineup_master_contest['added_date'] = format_date();

            $this->db->insert(LINEUP_MASTER_CONTEST, $lineup_master_contest);
            $lineup_master_contest_id = $this->db->insert_id();
            if ($lineup_master_contest_id) {
                $joined_count = $contest_data["total_user_joined"] + 1;

                //increment contest joined count
                $this->db->where('contest_id', $contest['contest_id']);
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

                $this->db->update(CONTEST);

                return array("joined_count" => $joined_count, "lineup_master_contest_id" => $lineup_master_contest_id);
            } else {
                return array();
            }
        }else{
            return array();
        }
    }

     /**
     * used to join contest
     * @param array $post_data
     * @return array
     */
    public function remove_joined_game($contest, $lineup_master_contest_id) {
        if ($lineup_master_contest_id) {
            $this->db->where("lineup_master_contest_id",$lineup_master_contest_id);
            $this->db->delete(LINEUP_MASTER_CONTEST);

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

            $this->db->update(CONTEST);

            return array("joined_count" => $joined_count, "lineup_master_contest_id" => $lineup_master_contest_id);
        } else {
            return array();
        }
    }

    function get_user_device_ids($user_id)
    {
        $this->user_db->select("device_type,device_id,date_created", FALSE);
        $this->user_db->from(ACTIVE_LOGIN);
        $this->user_db->where('device_id !=', '');
        $this->user_db->where('user_id',$user_id);
        $this->user_db->order_by("date_created", "DESC");
        $sql = $this->user_db->get();

        $result = $sql->result_array();
        return $result;
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
        $this->db->select("LM.user_id,LM.user_name,count(LM.user_id) AS user_count")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id AND LM.collection_id = ".$post_data['collection_id'], "INNER")
                ->where("LMC.contest_id", $post_data['contest_id'])
                ->where("LMC.fee_refund", 0)
                ->order_by('LMC.lineup_master_contest_id','ASC')
                ->group_by('LM.user_id')
                ->limit($limit, $offset);
        if($this->user_id){
            $this->db->order_by("FIELD(user_id,".$this->user_id.") DESC");
        }
        $this->db->order_by("LMC.lineup_master_contest_id","ASC");

        $result = $this->db->get()->result_array();
        return $result;
    }

     /**
     * used for get user team info
     * @param array $post_data
     * @return array
     */
    public function get_contest_collection_details_by_lmc_id($lineup_master_contest_id) {
        $result = $this->db->select("LM.lineup_master_id,LM.collection_id,CM.scheduled_date,CM.is_lineup_processed,0 as deadline_time,LMC.total_score,LM.user_id,LM.user_name,LM.team_name,LM.team_data,CM.category_id,CM.published_date,CM.end_date,CM.name as collection_name,LMC.game_rank,CM.score_updated_date,LM.team_short_name,LM.remaining_cap")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
                ->join(COLLECTION . " CM", "CM.collection_id = LM.collection_id", "INNER")
                ->where("LMC.lineup_master_contest_id", $lineup_master_contest_id)
                ->limit(1)
                ->get()
                ->row_array();
        return $result;
    }

    /**
     * used for get user team players list
     * @param int $lineup_master_contest_id
     * @param array $contest_info
     * @return array
     */
    public function get_lineup_with_score($lineup_master_contest_id, $contest_info) {
        if (!$lineup_master_contest_id || empty($contest_info)) {
            return false;
        }

        $result = array();
        $lineup_table = LINEUP;
        if ($contest_info['is_lineup_processed'] == '1' || $contest_info['is_lineup_processed'] == '2') {
            $this->db->select("L.lineup_master_id,L.stock_id,ROUND(IFNULL(L.score,0),1) AS score,L.captain,L.type,L.user_lot_size", FALSE)
                    ->from(LINEUP_MASTER_CONTEST . " LMC")
                    ->join($lineup_table . " L", "LMC.lineup_master_id = L.lineup_master_id", "INNER")
                    ->where('LMC.lineup_master_contest_id', $lineup_master_contest_id);
            $result = $this->db->get()->result_array();
        }
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
     * used for save contest invite code
     * @param array $invites
     * @return boolean
     */
    public function save_invites($invites) {
        $this->db->insert_batch(INVITE, $invites);
        return TRUE;
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
                ->from(CONTEST . " C")
                ->join(LINEUP_MASTER . " LM", "LM.collection_id=C.collection_id", "INNER")
                ->where("C.contest_id", $post_data['contest_id'])
                ->where("LM.lineup_master_id", $post_data['lineup_master_id'])
                ->where("LM.user_id", $post_data['user_id'])
                ->get()
                ->row_array();
        //echo "<pre>";print_r($result);die;
        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function get_new_contest_leaderboard_top_three($post_data ='')
	{
        $post = $this->input->post();
        
        $total_score_sort = "DESC";
        if(!empty($post['is_reverse']))
        {
            $total_score_sort = "ASC";
        }
        $this->db->select("LM.user_name,LM.team_name,LMC.is_winner,IFNULL(LMC.total_score, '') AS total_score,IF(LMC.game_rank > 0,LMC.game_rank,RANK() OVER (ORDER BY LMC.total_score $total_score_sort)) as game_rank,LMC.lineup_master_contest_id,LMC.prize_data,LM.team_short_name,LM.user_id",FALSE)
        ->from(LINEUP_MASTER_CONTEST.' LMC')
        ->join(LINEUP_MASTER.' LM',"LMC.lineup_master_id = LM.lineup_master_id")
        ->where("LMC.fee_refund", "0")
        ->where("LMC.contest_id", $post_data["contest_id"]);

        $this->db->order_by("LMC.game_rank", "ASC", FALSE);
			$this->db->limit(3,0);

		$leaderboard_data = $this->db->get()->result_array();
	    return $leaderboard_data;

    }
    
    public function get_new_contest_leaderboard($post_data='',$user_id='')
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

          $total_score_sort = "DESC";
         
		$this->db->select("LM.user_name,LM.team_name,LMC.is_winner,IFNULL(LMC.total_score, '') AS total_score,IF(LMC.game_rank > 0,LMC.game_rank,RANK() OVER (ORDER BY LMC.total_score $total_score_sort)) as game_rank,LMC.lineup_master_contest_id,LMC.prize_data,LM.team_short_name,LM.user_id",FALSE)
	   ->from(LINEUP_MASTER_CONTEST.' LMC')
	   ->join(LINEUP_MASTER.' LM',"LMC.lineup_master_id = LM.lineup_master_id")
	   ->where("LMC.fee_refund", "0")
       ->where("LMC.contest_id", $post_data["contest_id"]);

        //    if ($this->user_id) {
        //     $this->db->where("LM.user_id != ", $this->user_id);
    

	   if(!empty($user_id))
	   {
		   $this->db->order_by("FIELD ( LM.user_id, ".$this->user_id." ) DESC");	  
		   //$this->db->where('UP.user_id<>',$this->user_id);
	   }
		

       $this->db->order_by("LMC.game_rank", "ASC", FALSE);
       $this->db->order_by("LMC.total_score", $total_score_sort, FALSE);

		if(empty($user_id))
		{
            $this->db->where('LM.user_id<>',$this->user_id);
            $this->db->limit($limit,$offset);
			// if($offset > 0)
			// {
			// }
			// else
			// {
			// 	$this->db->limit($limit,10);
			// }
		}
		else{
			//$this->db->limit(1,0);

		}
	
		$sql = $this->db->get();
	   $leaderboard_data	= $sql->result_array();

	   if(!empty($user_id))
	   {	
	 	 // echo $this->db->last_query();die();
		 //log_message('error', $this->db->last_query());
        }
        else{
           // echo $this->db->last_query();die();
        }
	   $total = 0;
	   
	   //print_r($prediction_data);
	   return array(
		'other_list' => $leaderboard_data,
		'total' => $total
	   );
	}

    /**
     * used for get collection free teams
     * @param array $post_data
     * @return array
     */
    public function get_collection_contest_free_teams($post_data) {

        $result = $this->db->select("LM.lineup_master_id,LM.collection_id,LM.user_id,LM.team_name")
                ->from(CONTEST . " C")
                ->join(LINEUP_MASTER . " LM", "LM.collection_id=C.collection_id AND LM.user_id = ".$post_data['user_id'], "INNER")
                ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.lineup_master_id = LM.lineup_master_id AND LMC.contest_id = C.contest_id", "LEFT")
                ->where("C.contest_id", $post_data['contest_id'])                
                ->where("LMC.lineup_master_contest_id IS NULL")
                ->get()
                ->result_array();

        return $result;
    }

    function get_lineup_score_calculation($post_data)
    {
        /**
          SELECT CS.lot_size, CS.stock_id,SH1.close_price as closing_rate,SH2.close_price as result_rate,(SH1.close_price-SH2.close_price)*CS.lot_size as score
                                                FROM ".$this->db->dbprefix(COLLECTION_STOCK )." AS CS
                                                INNER JOIN ".$this->db->dbprefix(STOCK_HISTORY)." SH1
                                                ON CS.stock_id=SH1.stock_id 
                                                INNER JOIN  ".$this->db->dbprefix(STOCK_HISTORY)." SH2
                                                ON CS.stock_id=SH2.stock_id
                                                WHERE 
                                                SH1.schedule_date ='".$published_date."' 
                                                AND SH2.schedule_date ='".$end_date."'
                                                AND CS.collection_id=$collection_id
                                                GROUP BY CS.stock_id

                                                (TRUNCATE((CASE WHEN CM.status=1 AND SH2.close_price IS NOT NULL THEN SH2.close_price ELSE S.last_price END), 2) - IFNULL(SH1.close_price,0)) as price_diff,
         * 
         * **/

        $scheduled_date_time = $post_data['scheduled_date'];	
		$scheduled_date = date('Y-m-d',strtotime($scheduled_date_time));
        $end_date = date('Y-m-d',strtotime($post_data['end_date']));
        $collection_id = $post_data['collection_id'];
        $lineup_master_id = $post_data['lineup_master_id'];

        /* $current_date = date('Y-m-d',strtotime(format_date()));
        if(strtotime($current_date) > strtotime($end_date))
        {
            $current_date = date('Y-m-d',strtotime($post_data['scheduled_date']));
        }
        */

        

        $current_date_time = format_date();
        $result = $this->db->select('C.lot_size, C.stock_id,SH1.close_price as closing_rate,SH2.close_price as result_rate,(SH1.close_price-SH2.close_price)*C.lot_size as score,IFNULL(S.logo,"") as logo,S.name,S.display_name,        
        (
			TRUNCATE((CASE WHEN CM.scheduled_date > "'.$current_date_time.'" THEN S.last_price-S.open_price
			 	 WHEN CM.scheduled_date <= "'.$current_date_time.'" AND CM.end_date >= "'.$current_date_time.'"  AND CM.category_id=1 THEN S.last_price-S.open_price 
				 WHEN CM.scheduled_date <= "'.$current_date_time.'" AND CM.end_date >= "'.$current_date_time.'"  AND CM.category_id IN (2,3) THEN S.last_price-SH1.open_price 
				 WHEN CM.end_date < "'.$current_date_time.'" THEN SH2.close_price-SH1.open_price 
				 ELSE S.last_price-S.open_price END), 2)
		) as price_diff, L.type,SH1.open_price')
        ->from(COLLECTION_STOCK . " C")
        ->join(COLLECTION . " CM", "CM.collection_id=C.collection_id", "INNER")
        ->join(LINEUP . " L", "C.stock_id=L.stock_id", "INNER")
        ->join(STOCK . " S", "S.stock_id=C.stock_id", "INNER")
        ->join(STOCK_HISTORY.' SH1',"SH1.stock_id=C.stock_id AND SH1.schedule_date='{$scheduled_date}'",'LEFT')
       // ->join(STOCK_HISTORY . " SH1", "C.stock_id=SH1.stock_id", "INNER")
        ->join(STOCK_HISTORY . " SH2", "C.stock_id=SH2.stock_id AND SH2.schedule_date='{$end_date}'", "LEFT")
       // ->where("SH1.schedule_date", "$current_date")                
        ->where("C.collection_id","$collection_id")
        ->where("L.lineup_master_id","$lineup_master_id")
        ->get()
        ->result_array();
        

        return $result;
    }

    public function get_contest_leaderboard($post_data='',$user_id='')
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

          $total_score_sort = "DESC";
         
		$this->db->select("LM.user_name,LM.team_name,LMC.is_winner,IFNULL(LMC.total_score, '') AS total_score,IF(LMC.game_rank > 0,LMC.game_rank,RANK() OVER (ORDER BY LMC.total_score $total_score_sort)) as game_rank,LMC.lineup_master_contest_id,LMC.prize_data,LM.team_short_name,LM.user_id,LMC.percent_change,LMC.lineup_master_id,LMC.last_percent_change",FALSE)
	   ->from(LINEUP_MASTER_CONTEST.' LMC')
	   ->join(LINEUP_MASTER.' LM',"LMC.lineup_master_id = LM.lineup_master_id")
	   ->where("LMC.fee_refund", "0")
       ->where("LMC.contest_id", $post_data["contest_id"]);

	   if(!empty($user_id))
	   {
		   $this->db->order_by("FIELD ( LM.user_id, ".$this->user_id." ) DESC");	  
		  
	   }
		

       $this->db->order_by("LMC.game_rank", "ASC", FALSE);
       $this->db->order_by("LMC.total_score", $total_score_sort, FALSE);

		if(empty($user_id))
		{
            $this->db->where('LM.user_id<>',$this->user_id);
            $this->db->limit($limit,$offset);
			
		}
	
		$sql = $this->db->get();
	   $leaderboard_data	= $sql->result_array();

	   if(!empty($user_id))
	   {	
	 	 // echo $this->db->last_query();die();
		 //log_message('error', $this->db->last_query());
        }
        else{
           // echo $this->db->last_query();die();
        }
	   $total = 0;
	   
	   //print_r($prediction_data);
	   return array(
		'other_list' => $leaderboard_data,
		'total' => $total
	   );
	}

    function get_prize_and_updated_date($contest_id)
    {
       return  $this->db->select("C.prize_distibution_detail,IFNULL(CM.score_updated_date,'') as score_updated_date",FALSE)
	   ->from(CONTEST.' C')
	   ->join(COLLECTION.' CM',"C.collection_id = CM.collection_id")
	   ->where("C.contest_id", $contest_id)->limit(1)->get()->row_array();

    }

    function get_lineup_score_calculation_equity($post_data)
    {
        $published_date_time = $post_data['published_date'];	
        $scheduled_date_time = $post_data['scheduled_date'];	
		$published_date = date('Y-m-d',strtotime($published_date_time));
		$scheduled_date = date('Y-m-d',strtotime($scheduled_date_time));
        $end_date = date('Y-m-d',strtotime($post_data['end_date']));
        $collection_id = $post_data['collection_id'];
        $lineup_master_id = $post_data['lineup_master_id'];


        $current_date_time = format_date();
        $result = $this->db->select('C.lot_size, C.stock_id,SH1.close_price as publish_closing_rate,SH2.close_price as result_rate,IFNULL(S.logo,"") as logo,S.name,S.display_name,        
        (
			TRUNCATE((CASE WHEN CM.scheduled_date > "'.$current_date_time.'" THEN S.last_price-S.open_price
			 	 WHEN CM.scheduled_date <= "'.$current_date_time.'" AND CM.end_date >= "'.$current_date_time.'"  AND CM.category_id=1 THEN S.last_price-SH1.close_price 
				 WHEN CM.scheduled_date <= "'.$current_date_time.'" AND CM.end_date >= "'.$current_date_time.'"  AND CM.category_id IN (2,3) THEN S.last_price-SH1.close_price 
				 WHEN CM.end_date < "'.$current_date_time.'" THEN SH2.close_price-SH1.close_price 
				 ELSE S.last_price-SH1.close_price END), 2)
		) as price_diff, L.type,SH1.close_price as open_price,L.user_lot_size,L.captain,
        (CASE WHEN CM.scheduled_date > "'.$current_date_time.'" THEN ((IFNULL(S.last_price,0)-IFNULL(SH1.close_price,0))/SH1.close_price)*100
			 	 WHEN CM.scheduled_date <= "'.$current_date_time.'" AND CM.end_date >= "'.$current_date_time.'"  AND CM.category_id=1 THEN ((IFNULL(S.last_price,0)-SH1.close_price)/SH1.close_price)*100 
				 WHEN CM.scheduled_date <= "'.$current_date_time.'" AND CM.end_date >= "'.$current_date_time.'"  AND CM.category_id IN (2,3) THEN ((IFNULL(S.last_price,0)-SH1.close_price)/SH1.close_price)*100 
				 WHEN CM.end_date < "'.$current_date_time.'" THEN ((IFNULL(SH2.close_price,0)-SH1.close_price)/SH1.close_price)*100 
				 ELSE ((IFNULL(S.last_price,0)-SH1.close_price)/SH1.close_price)*100 END) as percentage,L.captain as player_role,L.gain_loss
        ')
        ->from(COLLECTION_STOCK . " C")
        ->join(COLLECTION . " CM", "CM.collection_id=C.collection_id", "INNER")
        ->join(LINEUP . " L", "C.stock_id=L.stock_id", "INNER")
        ->join(STOCK . " S", "S.stock_id=C.stock_id", "INNER")
        ->join(STOCK_HISTORY.' SH1',"SH1.stock_id=C.stock_id AND SH1.schedule_date='{$published_date}'",'LEFT')
       // ->join(STOCK_HISTORY . " SH1", "C.stock_id=SH1.stock_id", "INNER")
        ->join(STOCK_HISTORY . " SH2", "C.stock_id=SH2.stock_id AND SH2.schedule_date='{$end_date}'", "LEFT")
       // ->where("SH1.schedule_date", "$current_date")                
        ->where("C.collection_id","$collection_id")
        ->where("L.lineup_master_id","$lineup_master_id")
        ->get()
        ->result_array();
        

        return $result;
    }

    function get_team_percent_score($collection_id,$lineup_master_id,$contest_id)
    {
       $result = $this->db->select("CM.collection_id,CM.published_date,CM.end_date,CM.scheduled_date,LMC.percent_change,LMC.total_score,LM.remaining_cap")
	   ->from(COLLECTION.' CM')
	   ->join(LINEUP_MASTER.' LM',"CM.collection_id = LM.collection_id")
	   ->join(LINEUP_MASTER_CONTEST.' LMC',"LMC.lineup_master_id = LM.lineup_master_id")
	   ->where("CM.collection_id", $collection_id)
	   ->where("LM.lineup_master_id", $lineup_master_id)
       ->where("LMC.contest_id",$contest_id)
       ->limit(1)->get()->row_array();

       return $result;

    }

    function get_contest_won($user_id) {
        $this->db->select("SUM(IF(LMC.is_winner=1,1,0)) as won_contest,COUNT(LMC.lineup_master_contest_id) as total_contest")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LMC.lineup_master_id = LM.lineup_master_id", "INNER")
                ->join(CONTEST . " C", "C.contest_id = LMC.contest_id", "INNER");
                
        $this->db->where_in("C.status", array(2,3));        
        $this->db->where("LM.user_id", $user_id);
        $this->db->limit(1);
        $won_result = $this->db->get()->row_array();
       
        return $won_result;
    }

    
    
}