<?php
class Predict_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get fixture contest list for Without login users, without private contests
     * @param array $post_data
     * @param int $group_id
     * @return array
    */
    public function get_contests_list($post_data) {
        $current_date = format_date();
        $user_where = array(0);
        $user_id = 0;
        if($this->user_id != ""){
            $user_id = $this->user_id;
            $user_where[] = $this->user_id;
        }
     
        $this->db->select("C.contest_id,C.contest_unique_id,C.collection_id,C.category_id,C.entry_fee,C.size,C.minimum_size,C.max_bonus_allowed,C.total_user_joined,C.prize_pool,C.guaranteed_prize,C.multiple_lineup,C.contest_access_type,C.prize_distibution_detail,C.prize_type,C.is_pin_contest,C.currency_type,IFNULL(C.contest_title,'') as contest_title,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link,CM.name as collection_name,CM.scheduled_date,CM.end_date,IFNULL(COUNT(LM.lineup_master_id), 0) as lm_count", FALSE);
        $this->db->from(COLLECTION." as CM");
        $this->db->join(CONTEST.' as C', 'C.collection_id = CM.collection_id AND C.user_id IN('.implode(",",$user_where).')', "INNER");
        $this->db->join(LINEUP_MASTER_CONTEST.' as LMC', 'LMC.contest_id = C.contest_id', "LEFT");
        $this->db->join(LINEUP_MASTER.' as LM', 'LM.lineup_master_id = LMC.lineup_master_id AND LM.collection_id=CM.collection_id AND LM.user_id='.$user_id, "LEFT");
        $this->db->where('CM.stock_type', $this->stock_type);
        $this->db->where('C.status', 0);
        $this->db->where('C.size > C.total_user_joined',NULL);
        $this->db->where("CM.scheduled_date > '{$current_date}'"); //DATE_ADD('{$current_date}', INTERVAL '{$this->deadline_time}' MINUTE)

        //{"min_time":"","max_time":"","min_fee":"","max_fee":"","min_entries":"","max_entries":"","min_winning":"","max_winning":""}
        //from_date , to_date
        if(!empty($post_data['from_date']) && !empty($post_data['to_date'])) {
            $from_date = format_date($post_data['from_date'],'Y-m-d').' 00:00:00';
            $to_date = format_date($post_data['to_date'],'Y-m-d').' 23:59:59';
			$this->db->where("C.scheduled_date >= '".$from_date."' and C.scheduled_date <= '".$to_date."'");
		}

        if(!empty($post_data['min_time']) && !empty($post_data['max_time'])) {
            if(!empty($post_data['from_date']) && !empty($post_data['to_date']))
            {
                $from_date = format_date($post_data['from_date'],'Y-m-d').' '.$post_data['min_time'];
                $to_date = format_date($post_data['to_date'],'Y-m-d').' '.$post_data['max_time'];
            }
            else{
                $from_date = format_date('today','Y-m-d').' '.$post_data['min_time'];
                $to_date = format_date('today','Y-m-d').' '.$post_data['max_time'];
            }
           
			$this->db->where("C.scheduled_date >= '".$from_date."' and C.scheduled_date <= '".$to_date."'");
		}
        
        if(!empty($post_data['min_fee']) && !empty($post_data['max_fee'])) {
           
            $min_fee = $post_data['min_fee'];
            $max_fee = $post_data['max_fee'];
			$this->db->where("C.entry_fee >= '".$min_fee."' and C.entry_fee <= '".$max_fee."'");
		}

        if(!empty($post_data['min_entries']) && !empty($post_data['max_entries'])) {
           
            $min_entries = $post_data['min_entries'];
            $max_entries = $post_data['max_entries'];
			$this->db->where("C.minimum_size >= '".$min_entries."' and C.size <= '".$max_entries."'");
		}

        if(!empty($post_data['min_winning']) && !empty($post_data['max_winning'])) {
           
            $min_winning = $post_data['min_winning'];
            $max_winning = $post_data['max_winning'];
			$this->db->where("C.prize_pool >= '".$min_winning."' and C.prize_pool <= '".$max_winning."'");
		}
        $this->db->group_by("C.contest_id");
        $this->db->having("lm_count < C.multiple_lineup");
        $this->db->order_by("C.is_pin_contest", "DESC");
        $this->db->order_by("C.scheduled_date", "ASC");
        $result = $this->db->get()->result_array();

        // echo '<pre>';
        // echo $this->db->last_query();die;
        return $result;
    }

    public function get_user_fixture_contest($status) {
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
        IFNULL(LMCC.lm_count, '0') as user_joined_count,MG.group_name,LMC.last_score,LMC.percent_change,LMC.last_percent_change,CM.scheduled_date,CM.end_date,CM.collection_id";
        $this->db->select($select, false)
                ->from(LINEUP_MASTER_CONTEST . ' LMC')
                ->join(LINEUP_MASTER . ' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
                ->join(CONTEST . ' C', 'C.contest_id = LMC.contest_id', 'INNER')
                ->join(COLLECTION . ' CM', 'CM.collection_id = C.collection_id', 'INNER')
                ->join(MASTER_GROUP . ' MG', 'MG.group_id = C.group_id', 'INNER')
                ->join($pre_query . ' as LMCC', 'LMCC.contest_id = C.contest_id', 'LEFT')
                ->where("LMC.fee_refund", 0)
                ->where("LM.user_id", $this->user_id, FALSE)
                ->where($w_cond);

        $this->db->where("CM.stock_type", 3);

        $this->db->where("C.category_id", 1);
        

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

   /**
     * get collection stock statics
     * @param string $data 
     */
    public function statics($data){
        $type = isset($data['type']) ? $data['type'] : 0; // 1 - top gainer, 2 - top loser
        $collection_id = $data['collection_id']; 
        $published_date = $data['published_date'];
        $published_date = date('Y-m-d',strtotime($published_date));
        $end_date = date('Y-m-d',strtotime($data['end_date']));
        $page = isset($data['page']) ? $data['page'] : 0;
       
        $limit = 5;
		$offset = 0;
        $offset	= $limit * $page;

        $current_date = format_date();
        $this->db->select('IFNULL(i.name,"") as industry_name', FALSE);
        $this->db->select('IFNULL(i.display_name,"") as industry_display_name', FALSE);
		$this->db->select('IFNULL(i.industry_id,"") as industry_id', FALSE);
        $this->db->select("(TRUNCATE(
                                (
                                    CASE 
                                    WHEN cm.scheduled_date <= '".$current_date."' AND cm.end_date >= '".$current_date."' THEN (s.last_price-IFNULL(sh.close_price,0)) 
                                    WHEN cm.end_date < '".$current_date."' THEN (sh1.close_price-sh.close_price) 
                                    ELSE (s.last_price-s.open_price) END
                                ),2)) as price_diff"
                        );
        $this->db->select("(
                                CASE 
                                WHEN cm.scheduled_date <= '".$current_date."' AND cm.end_date >= '".$current_date."' THEN s.last_price 
                                WHEN cm.end_date < '".$current_date."' THEN sh1.close_price 
                                ELSE sh.close_price END
                            ) as current_price"
                        );                
                
        $this->db->select('(
			TRUNCATE((CASE WHEN cm.scheduled_date <= "'.$current_date.'" AND cm.end_date >= "'.$current_date.'"  THEN ((s.last_price-sh.close_price)/sh.close_price)*100  
				 WHEN cm.end_date < "'.$current_date.'" THEN ((sh1.close_price-sh.close_price)/sh.close_price)*100 
				 ELSE ((s.last_price-s.open_price)/s.open_price)*100 END), 2)
		) as percent_change');                
        $this->db->select("s.stock_id, cs.stock_name as name, s.display_name, s.trading_symbol, IFNULL(s.logo,'') as logo, IFNULL(sh.close_price,0) as pr_price",FALSE); //, s.last_price as current_price, (s.last_price - IFNULL(sh.close_price,0)) as price_diff
		$this->db->from(COLLECTION_STOCK . " cs");
        $this->db->join(COLLECTION . " cm", "cm.collection_id=cs.collection_id");
        $this->db->join(STOCK.' s','s.stock_id=cs.stock_id');
        $this->db->join(STOCK_HISTORY.' sh','sh.stock_id=s.stock_id AND sh.schedule_date = "'.$published_date.'"');
        $this->db->join(STOCK_HISTORY.' sh1','sh1.stock_id=s.stock_id AND sh1.schedule_date = "'.$end_date.'"', 'LEFT');
        $this->db->join(INDUSTRY.' i','i.industry_id=s.industry_id', 'LEFT');
        $this->db->where('cs.collection_id', $collection_id, FALSE);
        if(empty($page)) {
            $this->db->limit($limit,$offset);
        }
        
        if($type == 1) {
            $this->db->having('price_diff > ', 0);
            $this->db->order_by('price_diff', 'DESC');
        } else if($type == 2) {
            $this->db->having('price_diff < ', 0);
            $this->db->order_by('price_diff', 'ASC');
        } else {
            $this->db->order_by('price_diff', 'DESC');
        }
        
        $results = $this->db->get()->result_array();

        $this->load->model("wishlist/Wishlist_model");
        $wishlist_stock_ids  = $this->Wishlist_model->fetch_wishlist_stock_ids($data['user_id'], TRUE);
		foreach($results as &$result) {
            $result['is_wish'] = 0;
			if(in_array($result['stock_id'], $wishlist_stock_ids)) {
				$result['is_wish'] = 1;
			}
           // unset($result['price_diff']);
        }

        return $results;
    }  

    public function get_all_user_lineup_list($collection_id) {
        $this->db->select("LM.lineup_master_id,LM.collection_id,LM.team_name,
	   	count(LMC.lineup_master_contest_id) as total_joined", FALSE);
        $this->db->from(LINEUP_MASTER . ' LM');
        $this->db->join(LINEUP_MASTER_CONTEST . ' as LMC', 'LMC.lineup_master_id = LM.lineup_master_id', "LEFT");
        $this->db->where('LM.collection_id', $collection_id);
        $this->db->where('LM.user_id', $this->user_id);
        $this->db->group_by('LM.lineup_master_id');
        $this->db->order_by('LM.lineup_master_id', "ASC");
        return $this->db->get()->result_array();
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
        if ($contest_info['is_lineup_processed'] == '1' || $contest_info['is_lineup_processed'] == '2') {
            $this->db->select("L.lineup_master_id,L.stock_id,L.accuracy_percent,L.user_price,CS.close_price,CS.open_price", FALSE)
                    ->from(LINEUP_MASTER_CONTEST . " LMC")
                    ->join(LINEUP . " L", "LMC.lineup_master_id = L.lineup_master_id", "INNER")
                    ->join(LINEUP_MASTER . " LM", "L.lineup_master_id = LM.lineup_master_id", "INNER")
                    ->join(COLLECTION_STOCK . " CS", "LM.collection_id = CS.collection_id AND L.stock_id=CS.stock_id", "INNER")
                    ->where('LMC.lineup_master_contest_id', $lineup_master_contest_id);
            $result = $this->db->get()->result_array();
        }
        return $result;
    }

     /**
     * used for get user joined fixture list
     * @param array $post_data
     * @return array
     */
    public function get_my_joined_contests($post_data) {
        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $offset = get_pagination_offset($page_no, $limit);
       
        $current_date = format_date();
        $past_date = date('Y-m-d H:i:s',strtotime($current_date.' -7 days'));
        $this->db->select('IFNULL(CM.custom_message,"") as custom_message', FALSE);        
        $this->db->select("CM.collection_id, CM.name as collection_name, CM.scheduled_date, CM.end_date, C.status, CM.status AS collection_status,C.prize_distibution_detail,
        (CASE WHEN C.status=0 AND C.scheduled_date <= '{$current_date}' THEN 1 ELSE 0 END) as is_live,
        (CASE WHEN C.status=0 AND C.scheduled_date > '{$current_date}' THEN 1 ELSE 0 END) as is_upcoming,
        COUNT(DISTINCT C.contest_id) as contest_count, COUNT(DISTINCT LMC.lineup_master_id) as team_count,LMC.lineup_master_contest_id,LM.lineup_master_id,LM.team_name,C.contest_id, C.contest_title", false)
                ->from(LINEUP_MASTER_CONTEST . ' LMC')
                ->join(LINEUP_MASTER . ' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
                ->join(COLLECTION . ' CM', 'LM.collection_id = CM.collection_id', 'INNER')
                ->join(CONTEST . ' C', 'C.contest_id = LMC.contest_id', 'INNER')
                ->where("LMC.fee_refund", "0")
                ->where("LM.user_id", $this->user_id, FALSE)
                ->where('C.scheduled_date >=', $past_date);
    
        

        $this->db->where('CM.stock_type', 3);


            $this->db->order_by("is_live", "DESC");
            $this->db->order_by("is_upcoming", "DESC");
            $this->db->order_by("C.scheduled_date", "ASC");
        
        $this->db->group_by("C.contest_id");
        if (isset($limit) && isset($offset)) {
            $this->db->limit($limit, $offset);
        }
        $result = $this->db->get()->result_array();
       // echo $this->db->last_query();die;
        return $result;
    }    


    function get_lineup_score_calculation_prediction($post_data)
    {
        $collection_id = $post_data['collection_id'];
        $lineup_master_id = $post_data['lineup_master_id'];


        $current_date_time = format_date();
        $result = $this->db->select(' C.stock_id,(CASE WHEN C.close_price <=0 THEN round(S.last_price,2) ELSE C.close_price END) as close_price,C.open_price,IFNULL(S.logo,"") as logo,S.name,S.display_name,L.accuracy_percent,L.user_price',false) 
        ->from(COLLECTION_STOCK . " C")
        ->join(COLLECTION . " CM", "CM.collection_id=C.collection_id", "INNER")
        ->join(LINEUP . " L", "C.stock_id=L.stock_id", "INNER")
        ->join(STOCK . " S", "S.stock_id=C.stock_id", "INNER")
        ->where("C.collection_id",$collection_id)
        ->where("L.lineup_master_id",$lineup_master_id)
        ->get()
        ->result_array();
      
        return $result;
    }

}
