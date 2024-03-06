<?php defined('BASEPATH') OR exit('No direct script access allowed');

class H2hchallenge_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
        $this->db_fantasy = $this->load->database('db_fantasy', TRUE);
		//Do your magic here
	}

    /**
     * used to update heh config setting data
     * @param array $data
     * @return array
    */
    public function update_setting($data)
    {
        $this->db->where('key_name', 'h2h_challenge');
        $this->db->where('key_value', 1);
        $this->db->update(APP_CONFIG,$data);
    }

    /**
     * used to get collection master details by id
     * @param int $collection_master_id
     * @return array
    */
    public function get_collection_details($collection_master_id)
    {
        $this->db_fantasy->select('collection_master_id,collection_name,season_scheduled_date',FALSE)
            ->from(COLLECTION_MASTER)
            ->where('collection_master_id',$collection_master_id);

        $result =$this->db_fantasy->get()->row_array();
        return $result;
    }

    /**
     * used to get h2h users list for dashboard
     * @param array $post_data
     * @return array
    */
    public function get_h2h_user_list($post_data)
    {
        if(empty($post_data))
        {
            return array();
        }

        $sort_field = 'user_name';
        $sort_order = 'ASC';
        $limit      = 50;
        $page       = 0;
        if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
        {
            $limit = $post_data['items_perpage'];
        }

        if(isset($post_data['current_page']) && $post_data['current_page'])
        {
            $page = $post_data['current_page']-1;
        }

        $offset = $limit * $page;

        $this->db_fantasy->select('LM.user_id,COUNT(DISTINCT LMC.contest_id) as total_contest,SUM(CASE WHEN LMC.is_winner = 1 THEN 1 ELSE 0 END) as total_won,SUM(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, "$[0].prize_type"))=1 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, "$[0].amount")) ELSE 0 END) as winning,SUM(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, "$[0].prize_type"))=0 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, "$[0].amount")) ELSE 0 END) as bonus,SUM(CASE WHEN JSON_UNQUOTE(json_extract(LMC.prize_data, "$[0].prize_type"))=2 THEN JSON_UNQUOTE(json_extract(LMC.prize_data, "$[0].amount")) ELSE 0 END) as coins,SUM(CASE WHEN C.entry_fee > 0 THEN 1 ELSE 0 END) as total_paid,SUM(CASE WHEN C.entry_fee = 0 THEN 1 ELSE 0 END) as total_free')
            ->from(CONTEST.' C')
            ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id = C.contest_id')
            ->join(LINEUP_MASTER.' LM','LM.lineup_master_id = LMC.lineup_master_id')
            ->where('C.group_id',$post_data['group_id'])
            ->where('C.status',3)
            ->group_by('LM.user_id')
            ->order_by($sort_field,$sort_order);

        $tempdb = clone $this->db_fantasy;
        $temp_q = $tempdb->get();
        $total = $temp_q->num_rows();

        $this->db_fantasy->limit($limit,$offset);
        $result = $this->db_fantasy->get()->result_array();
        return array('result'=>$result,'total'=>$total);
    }

    /**
     * used to get h2h contest ids by date
     * @param array $filter_date
     * @return array
    */
    public function get_h2h_contest_ids($filter_date="")
    {
        $this->db_fantasy->select('contest_id')
            ->from(CONTEST)
            ->where('status',3)
            ->where('group_id',$this->h2h_group_id);

        if(!empty($filter_date))
        {
            $this->db_fantasy->where('season_scheduled_date > ',$filter_date);
        }

        $result =$this->db_fantasy->get()->result_array();
        return $result;
    }

    /**
     * used to get h2h dashboard tracking data
     * @param array $filter_date
     * @return array
    */
    public function get_h2h_tracking_contest_data($filter_date)
    {
        $current_date = format_date();
        $this->db_fantasy->select('COUNT(DISTINCT contest_id) as total,SUM(CASE WHEN size = total_user_joined THEN 1 ELSE 0 END) as matched,SUM(CASE WHEN size > total_user_joined THEN 1 ELSE 0 END) as unmatched',FALSE)
            ->from(CONTEST)
            ->where_in('status',array(1,3))
            ->where('total_user_joined > ',"0")
            ->where('group_id',$this->h2h_group_id)
            ->where('season_scheduled_date > ',$filter_date);

        $result =$this->db_fantasy->get()->row_array();
        return $result;
    }

    /**
     * used to get h2h upcoming contest count data
     * @param void
     * @return array
    */
    public function get_h2h_upcoming_contest_data()
    {
        $current_date = format_date();
        $this->db_fantasy->select('COUNT(DISTINCT contest_id) as total,SUM(CASE WHEN size > total_user_joined THEN 1 ELSE 0 END) as unmatched',FALSE)
            ->from(CONTEST)
            ->where('total_user_joined > ',"0")
            ->where('group_id',$this->h2h_group_id)
            ->where('season_scheduled_date > ',$current_date);

        $result =$this->db_fantasy->get()->row_array();
        return $result;
    }

    /**
     * used to get h2h contest participation data count
     * @param array $contest_ids
     * @return array
    */
    public function get_h2h_user_participation($contest_ids=array())
    {
        if(empty($contest_ids))
        {
            return array();
        }
        $this->db->select("O.user_id,
        GROUP_CONCAT(DISTINCT (CASE WHEN O.source=1 THEN O.user_id ELSE '' END)) as user_ids,
        O.source,SUM((CASE WHEN O.source=1 THEN O.real_amount+O.winning_amount ELSE 0 END)) as total_entry_fee,
        SUM((CASE WHEN O.source=3 THEN O.winning_amount ELSE 0 END)) as total_winning,
        GROUP_CONCAT(DISTINCT O.reference_id) as contest_ids,DATE_FORMAT(O.date_added,'%Y-%m-%d') as main_date",FALSE)
        ->from(ORDER.' O')
        ->where_in('O.source',array(1,3));//join and won

        if(!empty($contest_ids))
        {   
            $this->db->where_in('O.reference_id',$contest_ids);
        }

        $result = $this->db->group_by('main_date')->get()->result_array();
        return $result;
    }

    /**
     * to get upcoming games by match list
     * @param array $post_data
     * @return array
     */
    public function get_upcoming_game_list($post_data)
    {
        if(empty($post_data))
        {
            return array();
        }
        $current_date = format_date();
        $sort_field = 'CM.season_scheduled_date';
        $sort_order = 'ASC';
        $limit      = 50;
        $page       = 0;
        if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
        {
            $limit = $post_data['items_perpage'];
        }

        if(isset($post_data['current_page']) && $post_data['current_page'])
        {
            $page = $post_data['current_page']-1;
        }
        $offset = $limit * $page;

        $this->db_fantasy->select("CM.collection_master_id,CM.collection_name,CM.season_scheduled_date,CT.contest_template_id,CT.template_name,COUNT(DISTINCT C.contest_id) as total,SUM(CASE WHEN C.total_user_joined < C.size AND C.total_user_joined > 0 THEN 1 ELSE 0 END) as unmatched,COUNT(DISTINCT LM.user_id) as total_users",FALSE)
            ->from(CONTEST.' C')
            ->join(COLLECTION_MASTER.' CM','CM.collection_master_id = C.collection_master_id')
            ->join(CONTEST_TEMPLATE.' CT','CT.contest_template_id = C.contest_template_id')
            ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id = C.contest_id')
            ->join(LINEUP_MASTER.' LM','LM.lineup_master_id = LMC.lineup_master_id')
            ->where('C.group_id',$post_data['group_id'])
            ->where('C.total_user_joined > ',"0")
            ->where('CM.season_scheduled_date > ',$current_date)
            ->group_by('CM.collection_master_id')
            ->group_by('C.contest_template_id')
            ->order_by($sort_field,$sort_order);

        if(isset($post_data['keyword']) && $post_data['keyword'] != ""){
            $this->db_fantasy->like('LOWER(CONCAT(CM.collection_name,CT.contest_template_id))', strtolower($post_data['keyword']) );
        }

        $tempdb = clone $this->db_fantasy;
        $temp_q = $tempdb->get();
        $total = $temp_q->num_rows();

        $this->db_fantasy->limit($limit,$offset);
        $result = $this->db_fantasy->get()->result_array();
        return array('result'=>$result,'total'=>$total);
    }

    /**
     * to get template joined users list
     * @param array $post_data
     * @return array
     */
    public function get_h2h_game_users($post_data)
    {
        if(empty($post_data))
        {
            return array();
        }
        $current_date = format_date();
        $sort_field = 'LM.user_name';
        $sort_order = 'ASC';
        $limit      = 50;
        $page       = 0;
        if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
        {
            $limit = $post_data['items_perpage'];
        }

        if(isset($post_data['current_page']) && $post_data['current_page'])
        {
            $page = $post_data['current_page']-1;
        }
        $offset = $limit * $page;

        $this->db_fantasy->select("LM.user_id,IFNULL(HU.total_win,0) as total_win,COUNT(DISTINCT LMC.lineup_master_contest_id) as join_coint",FALSE)
            ->from(CONTEST.' C')
            ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id = C.contest_id')
            ->join(LINEUP_MASTER.' LM','LM.lineup_master_id = LMC.lineup_master_id')
            ->join(H2H_USERS.' HU','HU.user_id = LM.user_id','LEFT')
            ->where('C.group_id',$post_data['group_id'])
            ->where('C.collection_master_id',$post_data['collection_master_id'])
            ->where('C.contest_template_id',$post_data['contest_template_id'])
            ->group_by('LM.user_id')
            ->order_by($sort_field,$sort_order);

        if(isset($post_data['keyword']) && $post_data['keyword'] != ""){
            $this->db_fantasy->like('LOWER(CONCAT(LM.user_name,LM.team_name))', strtolower($post_data['keyword']) );
        }

        $tempdb = clone $this->db_fantasy;
        $temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $this->db_fantasy->limit($limit,$offset);
        $result = $this->db_fantasy->get()->result_array();
        return array('result'=>$result,'total'=>$total);
    }

    /**
     * to get cms info by id
     * @param void
     * @return array
     */
    public function get_cms_by_id($post_data)
    {
        $result = $this->db_fantasy->select('*')
                        ->from(H2H_CMS)
                        ->where("id",$post_data['id'])
                        ->get()
                        ->row_array();

        return $result;
    }

    /**
     * update cms by id
     * @param void
     * @return array
     */
    public function update_cms_by_id($update_data,$id)
    {
        $update_data['updated_date'] = format_date();
        $this->db_fantasy->where("id",$id);
        return $this->db_fantasy->update(H2H_CMS, $update_data);
    }

    /**
     * save cms details
     * @param array $post_data
     * @return array
     */
    public function save_cms($post_data)
    {
        $post_data['added_date'] = format_date();
        $post_data['updated_date'] = format_date();
        return $this->db_fantasy->insert(H2H_CMS,$post_data);
    }

    /**
    * Function used for get cms list
    * @param void
    * @return result array
    */
    public function get_cms_list()
    {
      $this->db_fantasy->select('C.id,C.name,C.image_name,C.bg_image', FALSE)
            ->from(H2H_CMS.' as C')
            ->order_by("C.id","DESC");
      $result = $this->db_fantasy->get()->result_array();
      return $result;
    }

    /**
     * used to delete cms banner record
     * @param int $id
     * @return array
    */
    public function delete_cms_record($id)
    {
        $this->db_fantasy->where("id",$id)
                  ->delete(H2H_CMS); 
        return TRUE;
    }
}