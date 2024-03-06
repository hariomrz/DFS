<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Nw_contest_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_fantasy		= $this->load->database('db_fantasy', TRUE);
		
	}

	
	/**
	 * [get_all_network_contest description]
	 * Summary :- 
	 * @return [type] [description]
	 */
	public function get_all_network_contest($post_data)
	{
		$current_date = format_date();
		$limit = 2;
		$page = 1;
		$status = isset($post_data['status'])?$post_data['status']:"";
		$sort_field = 'C.season_scheduled_date';
		$sort_order = 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('season_scheduled_date','active','status','network_prize_pool')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		if(!empty($post_data['pageSize']) && $post_data['pageSize'])
		{
			$limit = $post_data['pageSize'];
		}

		if(!empty($post_data['currentPage']) && $post_data['currentPage'])
		{
			$page = $post_data['currentPage'];
		}
		$offset	= $limit * ($page-1);

		$this->db_fantasy->select('C.*,L.league_uid,L.league_display_name', FALSE)
			->from(NETWORK_CONTEST." AS C")
			->join(LEAGUE . " AS L", "L.league_id = C.league_id AND L.sports_id=C.sports_id", "INNER")
			->where('C.sports_id',$post_data['sports_id'])
			->where('C.network_contest_id > ',0)
			->where('C.network_collection_master_id > ',0)
			 ->group_start()
                ->where('C.active', 1)
                ->or_group_start()
                        ->where('C.active',0)
                        ->where("C.season_scheduled_date >= DATE_ADD('{$current_date}', INTERVAL 0 MINUTE)")
                ->group_end()
        ->group_end();
		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db_fantasy->where('C.league_id', $post_data['league_id']);
		}

		switch ($status)
		{
			case 'current_game':
				$this->db_fantasy->where('C.status','0');
				$this->db_fantasy->where("C.season_scheduled_date < DATE_ADD('{$current_date}', INTERVAL 0 MINUTE)");
				break;
			case 'completed_game':
				$this->db_fantasy->where('C.status >','1');
				break;
			case 'cancelled_game':
				$this->db_fantasy->where('C.status','1');
				break;
			case 'upcoming_game':
				$this->db_fantasy->where('C.status','0');
				$this->db_fantasy->where("C.season_scheduled_date > DATE_ADD('{$current_date}', INTERVAL 0 MINUTE)");
				break;
			default:
				break;
		}
		$tempdb = clone $this->db_fantasy;
        $temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		
		$this->db_fantasy->order_by($sort_field, $sort_order);
		$result = $this->db_fantasy->limit($limit,$offset)->get()->result_array();
		return array('result' => $result, 'total' => $total);
	}

	/**
     * used for get user joined fixture list
     * @param array $post_data
     * @return array
     */
    public function check_collection_exist($post_data) 
    {
       $current_date = format_date();
        $this->db_fantasy->select("CM.collection_master_id,CM.season_scheduled_date,CM.league_id,CM.season_game_count,CM.collection_name,CS.season_game_uid", false)
                ->from(COLLECTION_MASTER . ' CM')
                ->join(COLLECTION_SEASON . ' CS', 'CM.collection_master_id = CS.collection_master_id', 'INNER')
                ->where('CM.season_game_count',1)
                ->where('CM.league_id',$post_data['league_id'])
                ->where('CS.season_game_uid',$post_data['season_game_uid'])
                ->where('CM.status',0)
                ->where('CS.season_scheduled_date',$post_data['season_scheduled_date'])
                ->where("CM.season_scheduled_date >= DATE_ADD('{$current_date}', INTERVAL CM.deadline_time MINUTE)")
                ->where('CM.season_game_count',1);

        $result = $this->db_fantasy->get()->row_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    public function save_network_collection($collection_arr,$contest_arr)
    {
        
        $this->db_fantasy->insert(COLLECTION_MASTER,$collection_arr);
        $collection_master_id = $this->db_fantasy->insert_id();

        if($collection_master_id)
        {
            $collection_season_data = array();
            $collection_season_data['collection_master_id'] = $collection_master_id;
            $collection_season_data['season_game_uid'] = $contest_arr['season_game_uid'];
            $collection_season_data['season_scheduled_date'] = $contest_arr['season_scheduled_date'];
            $collection_season_data['added_date'] =format_date();
            $collection_season_data['modified_date'] = format_date();
            $this->db_fantasy->insert(COLLECTION_SEASON,$collection_season_data);
        }

        return $collection_master_id;
    }

      /**
     * used to get contest details
     * @param array $post_data
     * @return array
     */
    public function get_network_contest_detail($where_arr) {
        $sql = $this->db_fantasy->select("NC.*", FALSE)
                ->from(NETWORK_CONTEST . " AS NC");
        $this->db_fantasy->where($where_arr);
        
        $result = $sql->get()->row_array();
        return $result;
    }

	/*#########################################################################*/

	
	
	/**
	*@method update_network_contest_details
	*@uses this function used for publish/update network contest
	**/
	public function update_network_contest_details($update_data,$where_data,$nc_detail_arr)
	{
		if(empty($update_data) || empty($where_data)){
			return false;
		}
		$this->db_fantasy->where($where_data);
		$this->db_fantasy->update(NETWORK_CONTEST,$update_data);
		return true;
	}
	



	



	public function get_notification_details($collection_master_id)
	{
		$result = $this->db_fantasy->select("home,away")
		->from(COLLECTION_MASTER." CM")
		->join(COLLECTION_SEASON." CS","CM.collection_master_id = CS.collection_master_id","INNER")
		->join(SEASON." S","S.season_game_uid = CS.season_game_uid","INNER")
		->where("CM.collection_master_id",$collection_master_id)
		->get()->row_array();
		return $result;
	}
}