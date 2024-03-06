<?php if (!defined('BASEPATH')) {  exit('No direct script access allowed'); }

class FEED_Model extends MY_Model {

	/**
    * Class constructor
    * load fantasy db database.
    * @return	void
    */
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
		$this->load->database();
	}

    /**
    * Class destructor
    * Closes the connection to user db if present
    * @return	void
    */
    function __destruct() {
        if(isset($this->db->conn_id)) {
            $this->db->close();
        }
    }
    
    /**
     * [get_config_detail description]
     * Summary :-
     * @param  [type] $sport_name,$feed_providers 
     * @return [type]            [description]
     */
    public function get_sports_config_detail($sport_name= '',$feed_providers = '') 
    {
        $this->config->load('sports_config');
        $feed_config = $this->config->item($sport_name."_config");
        //echo "<pre>";print_r($feed_config);die;
        if (isset($feed_providers) && isset($feed_config[$feed_providers])) 
        {
            return $feed = $feed_config[$feed_providers];
        }else 
        {
            exit("Invalid League");
        }
    }

    //Insert update function for sports data inswertion 
    public function insert_or_update_on_exist($key_array,$data,$concat_key,$table,$update_key,$update_ignore = array(),$where = '')
    {
        //echo "<pre>";print_r($insert_data);die;
        $db_obj = $this->db;

        $update_data = array();
        $insert_data = array();
         //check if league already exist
        $db_obj->select("$update_key,$concat_key as data_key",FALSE)
            ->from($table);
        if(!empty($where)){
            $db_obj->where($where);
        }else{
            $db_obj->where_in("$concat_key", $key_array);
        }
        $sql = $db_obj->get();
        $existing_data = $sql->result_array();

        if(!empty($existing_data))
        {
            $existing_data = array_column($existing_data,NULL,'data_key');
            foreach($data as $key => $value)
            {
                if(isset($existing_data[$key]))
                {
                    $value[$update_key] = $existing_data[$key][$update_key];
                    //for ignore update manual info
                    if(!empty(($update_ignore)))
                    {
                        foreach ($update_ignore as $ingore_key => $ingore_value) 
                        {
                            unset($value[$ingore_value]);
                        }
                    } 
                    $update_data[] = $value; 
                }else
                {
                    $insert_data[] = $value;
                }
            }
        }else
        {
            $insert_data = $data;
        }
        //echo "<pre>";print_r($update_data);
        //echo "<pre>";print_r($insert_data);die;
        if(!empty($update_data))
        {
            $update_data = array_values($update_data);
            $db_obj->update_batch($table, $update_data, $update_key);
        }
        if(!empty($insert_data))
        {
            $insert_data = array_values($insert_data);
            //echo "<pre>";print_r($insert_data);die;
            $this->insert_ignore_into_batch($table, $insert_data);
       }

        return true;
    }

    /**
     * Used for get match details by id
     * @param int $season_game_uid
     * @param int $league_id
     * @return array
     */
    public function get_season_match_details($season_id)
    {
        $this->db->select("S.home_id,S.away_id,S.year,S.season_id,S.season_game_uid, S.league_id,S.type,S.feed_date_time,S.scheduled_date,S.format")
            ->from(SEASON." AS S")
            ->where("S.season_id", $season_id);
        $this->db->group_by("S.season_id");
        $sql = $this->db->get();
        //echo $this->db->last_query();die;
        $matches = $sql->row_array();
        return $matches;
    }

    /**
     * function used for get season list for score update
     * @param int $sports_id
     * @return array
     */
    public function get_season_match_for_score($sports_id)
    {
        $current_date_time = format_date();
        $this->db->select("S.home_id,S.away_id,S.year,S.season_id,S.season_game_uid, S.league_id,S.type, S.feed_date_time, S.scheduled_date, S.format, L.sports_id,L.league_abbr,T1.team_uid AS home_uid,T2.team_uid AS away_uid")
            ->from(SEASON . " AS S")
            ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER")
            ->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
            ->join(TEAM." AS T1", "T1.team_id = S.home_id", "INNER")
            ->join(TEAM." AS T2", "T2.team_id = S.away_id", "INNER")
            ->where("S.scheduled_date <='" . $current_date_time . "'")
            ->where("L.sports_id", $sports_id)
            ->where("L.status", '1')
            ->where("S.status < ", '2')
            ->limit("50")
            ->order_by("S.scheduled_date","ASC")
            ->group_by("S.season_id");
        $sql = $this->db->get();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * This function upload team's flags and jersys from feed s3 to project s3
     * @param array $team_img_arr
     * @return boolean
     */
    public function process_team_image_data_from_feed($team_img_arr)
    {
        if(empty($team_img_arr))
        {
            return TRUE;
        }

        $default_img_names = array("flag_default.jpg","jersey_default.png");
        $uploaded_img_arr  = array();
        //process all flags and jerseys from feed 
        foreach ($team_img_arr as $img_key => $img_arr)
        {
            $feed_img_name = basename($img_arr['url']);
            try
            {   
                $local_dir = ROOT_PATH.UPLOAD_DIR;
                //Create temp file on local -> /upload
                $temp_file_path = $local_dir. $feed_img_name;
                $temp_file = fopen($temp_file_path, "w");
                $feed_file_contents = file_get_contents($img_arr['url']);
                $tempFile = file_put_contents($temp_file_path, $feed_file_contents);

                //upload local file to s3
                $s3_dir = ($img_arr['type'] == 'flag') ? FLAG_DIR : JERSEY_DIR;
                $filePath = $s3_dir.$feed_img_name;
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file_path;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $uploaded_img_arr[] = IMAGE_PATH.$filePath;
                    @unlink($temp_file_path);
                }
            } 
            catch (Exception $e)
            {
                return TRUE;
            }
        }
        return TRUE;
    }

    public function get_team_id_with_team_uid($sports_id,$team_uid = '',$team_uid_array = array())
    {
        $this->db->select("team_id,team_uid");
        $this->db->from(TEAM);
        if($team_uid != "") 
        {
            $this->db->where("team_uid",$team_uid);
        }
        elseif(!empty($team_uid_array)) 
        {
            $this->db->where_in("team_uid",$team_uid_array);
        }
        $this->db->where("sports_id",$sports_id);
        $query = $this->db->get();
        $team_data = $query->result_array();
        //echo "<pre>";print_r($team_data);die;
        $team_ids = array();
        if(!empty($team_data))
        {
            $team_ids = array_column($team_data, "team_id","team_uid");
        }
        return $team_ids;
    }

     public function get_player_data_by_player_uids($sports_id,$player_uids = array())
    {
         $this->db->select("P.player_id,P.player_uid", FALSE)
                ->from(PLAYER." AS P")
                ->where("P.sports_id", $sports_id);
                if(!empty($player_uids)){
                    $this->db->where_in("P.player_uid", $player_uids);
                }
        $rs = $this->db->get();
        $player_data = $rs->result_array();
        $player_ids = array();
        if(!empty($player_data))
        {
            $player_ids = array_column($player_data, "player_id","player_uid");
        }
        return $player_ids;
    }

    public function get_match_for_update_lineup($sports_id) 
    {
        $current_date_time = format_date();
        $this->db->select("S.season_game_uid,S.season_id,S.league_id,S.scheduled_date, S.format,L.sports_id,S.status")
                ->from(SEASON." AS S")
                ->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER")
                ->join(MASTER_SPORTS." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
                ->where("L.status", '1')
                ->where("S.scheduled_date <='".$current_date_time."'")
                ->where("L.sports_id", $sports_id);
        $this->db->group_by("S.season_id");
        $sql = $this->db->get();
        $matches = $sql->result_array();
        return $matches;
    }
}
/* End of file FEED_Model.php */
/* Location: application/core/FEED_Model.php */