<?php 

class Private_contest_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		// $this->db_user = $this->load->database('db_user', TRUE);
		$this->user_db = $this->load->database('user_db', TRUE);
	}

	/** 
     * common function used to get single record from any table
     * @param string    $select
     * @param string    $table
     * @param array/string $where
     * @return	array
     */
    function get_single_row_from_user($select = '*', $table, $where = "") {
        $this->user_db->select($select, FALSE);
        $this->user_db->from($table);
        if ($where != "") {
            $this->user_db->where($where);
        }
        $this->user_db->limit(1);
        $query = $this->user_db->get();
        return $query->row_array();
    }

	/**
     * used to get league list for create contest
     * @param int $sports_id
     * @return array
    */
	public function get_sport_recent_leagues($sports_id)
	{
		$current_date = format_date();
		$sql = $this->db->select('L.league_id,L.league_display_name as league_name,L.league_abbr')
					->from(LEAGUE." AS L")
					->join(SEASON . " AS S", "L.league_id = S.league_id", 'left')
					->where('L.active', 1)
					->where('L.archive_status', 0)
					->where('L.sports_id', $sports_id)
					->where('S.is_published',"1")
					->where('S.status',"0")
					->where('S.season_scheduled_date >',$current_date)
					->order_by('league_name', 'ASC')
					->group_by("L.league_id");

		$result = $sql->get()->result_array();
		return $result;
	}

	/**
     * used to get match list by league id for create contest
     * @param int $league_id
     * @return array
    */
	public function get_all_match_by_league_id($league_id) {
		$current_date = format_date();
        $this->db->select("S.home_uid,S.away_uid,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,S.season_game_uid,S.season_scheduled_date,CS.collection_master_id", FALSE);
        $this->db->from(COLLECTION_SEASON . " AS CS");
        $this->db->join(COLLECTION_MASTER.' CM', 'CM.collection_master_id = CS.collection_master_id AND CM.league_id="'.$league_id.'"', 'INNER');
        $this->db->join(SEASON.' S', 'S.season_game_uid = CS.season_game_uid', 'INNER');
        $this->db->join(TEAM_LEAGUE.' TL1', 'TL1.team_uid = S.home_uid AND TL1.league_id = S.league_id', 'INNER');
        $this->db->join(TEAM.' as T1', 'T1.team_id = TL1.team_id', "INNER");
        $this->db->join(TEAM_LEAGUE.' TL2', 'TL2.team_uid = S.away_uid AND TL2.league_id = S.league_id', "LEFT");
        $this->db->join(TEAM.' as T2', 'T2.team_id = TL2.team_id', "LEFT");
        $this->db->where("CM.season_game_count", "1");
        $this->db->where("S.is_published", "1");
        $this->db->where("S.status", "0");
        $this->db->where("S.league_id", $league_id);
        $this->db->where('S.season_scheduled_date >',$current_date);
        $this->db->group_by("S.season_game_uid");
        $this->db->order_by("S.season_scheduled_date", "ASC");
        $sql = $this->db->get();
        return $sql->result_array();
    }

    /**
     * used to save user contest
     * @param array $contest_data
     * @return int
    */
    public function save_contest($contest_data)
	{
		$contest_data['added_date'] = format_date();
		$contest_data['modified_date'] = format_date();

		$this->db->trans_start();
		$this->db->insert(CONTEST,$contest_data);
		$contest_id = $this->db->insert_id();
		$this->db->trans_complete();
		$this->db->trans_strict(FALSE);

		if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
			return false;
		}
		else
		{
			$this->db->trans_commit();
			return $contest_id;
		}
	}
}