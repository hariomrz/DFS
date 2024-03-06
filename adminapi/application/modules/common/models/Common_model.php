<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->db_fantasy		= $this->load->database('db_fantasy', TRUE);
		
	}
	public function get_sport_detail_by_id($con = array())
	{
		$data = $this->db_fantasy->select('sports_id,sports_name')
				->order_by('order', 'ASC')
				->where('active', '1')
				->where($con)
				->get(MASTER_SPORTS)
				->row_array();
		return $data;
	}
	/**
	 * @Summary: This function for use get scoring category wich is store in master_scoring_category table. Its return record on condition.
	 * @access: public
	 * @param: $sports_id 
	 * @return: result array
	 */
	public function get_scoring_categories($sports_id = NULL)
	{
		$result = array();

		$this->db_fantasy->select('MSC.master_scoring_category_id,MSC.scoring_category_name')
				->from(MASTER_SCORING_CATEGORY . " AS MSC");

		if(!is_null($sports_id))
		{
			$this->db_fantasy->where('MSC.sports_id', $sports_id);
		}
		
		$result = $this->db_fantasy->get()->result_array();
		
		return $result;
	}
	/** 
     * common function used to get group list
     * @param array $data
     * @return	array
     */
	public function get_all_group_list($data = array())
	{
		$is_private = "";
		if(isset($data['list_type']) && $data['list_type'] != ""){
			$is_private = $data['list_type'];
		}

		$sql = $this->db_fantasy->select('*')
						->from(MASTER_GROUP)
						->where('status','1')
						->order_by('sort_order','ASC');
		if(isset($is_private) && $is_private != ""){
			$sql->where_in('is_private',[0,$is_private]);
		}else{
			$sql->where('is_private','0');
		}

		//check if rookie OFF or ON
		$allow_rookie_contest= isset($this->app_config['allow_rookie_contest'])?$this->app_config['allow_rookie_contest']['key_value']:0;
		if(!$allow_rookie_contest)
		{
			$rookie_group_id = $this->app_config['allow_rookie_contest']['custom_data']['group_id'];
			$this->db_fantasy->where('group_id<>',$rookie_group_id);
		}

		//check head 2 head
		$h2h_challenge= isset($this->app_config['h2h_challenge'])?$this->app_config['h2h_challenge']['key_value']:0;
		if(!$h2h_challenge)
		{
			$h2h_group_id = $this->app_config['h2h_challenge']['custom_data']['group_id'];
			$this->db_fantasy->where('group_id<>',$h2h_group_id);
		}


		$sql = $sql->get();
		$result = $sql->result_array();
		return ($result) ? $result : array();
	}
	/**
	 * [get_all_sport]
	 * @Summary : Used to get list of all active sports
	 * @return  [array]
	 * @addedBy Trilochan<trilochan.umath@vinfotech.com>
	 */
	public function get_all_sport($post_data)
	{    
		$result = array();
		$sql = $this->db_fantasy->select('MS.sports_id, MS.sports_name, MS.team_player_count,MS.max_player_per_team')
				->from(MASTER_SPORTS . " MS")
				//->join(LEAGUE . " L", "MS.sports_id = L.sports_id", "INNER")
				->where('MS.active', '1')
				->group_by('MS.sports_id')
				->order_by('MS.order', 'ASC')
				->get();

		$result = $sql->result_array();
		return $result;
	}

	public function sync_app_setting_fields(){
	  	$form_data = app_config_form_setting("1");
	  	$setting = array();
	  	foreach($form_data as $key_name => $row){
			$tmp_arr = array();
			$tmp_arr['name'] = ucfirst(str_replace("_"," ",$key_name));
			$tmp_arr['key_name'] = $key_name;
			if($row['type'] == "radio"){
				$tmp_arr['key_value'] = "0";
			}else if($row['type'] == "select"){
				$tmp_arr['key_value'] = "payumoney";
			}else{
				$tmp_arr['key_value'] = "";
			}
			$custom_data = array();
			if(isset($row['child'])){
				foreach($row['child'] as $child_key=>$child){
					if($child['type'] == "select"){
						$custom_data[$child_key] = "TEST";
					}else{
						$custom_data[$child_key] = "";
					}
				}
			}
	      	$tmp_arr['custom_data'] = json_encode($custom_data);
	      	$setting[] = $tmp_arr;
	  	}
	  
		$this->replace_into_batch(APP_CONFIG, $setting);
	  	return true;
 	}

 	/**
  	* Function used for save banned states
  	* @param array $data_arr
  	* @return int
  	*/
 	public function save_banned_state($data_arr)
 	{
       	$this->db->insert(BANNED_STATE,$data_arr);
        return $this->db->insert_id();
 	}

 	/**
  	* Function used for delete banned states
  	* @param int $master_state_id
  	* @return boolen
  	*/
    public function delete_banned_state($master_state_id) {
        $this->db->where("master_state_id",$master_state_id);
        $this->db->delete(BANNED_STATE);
        return true;
    }

 	/**
  	* Function used for get banned state list
  	* @param array $post_data
  	* @return array
  	*/
	public function get_banned_state_list($post_data)
    {
		$limit = 10;
		$page = 0;
		$sort_field	= 'id';
		$sort_order	= 'ASC';
		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		$offset	= $limit * $page;
		$this->db->select("BS.id,MS.master_state_id,MS.name as state_name,MC.country_name,BS.date_added", FALSE);
        $this->db->from(BANNED_STATE." AS BS");
        $this->db->join(MASTER_STATE." AS MS","MS.master_state_id = BS.master_state_id","INNER");
        $this->db->join(MASTER_COUNTRY." AS MC","MC.master_country_id = MS.master_country_id","INNER");
        $this->db->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db;
		$total = $tempdb->get()->num_rows();
		$this->db->limit($limit,$offset);
		$result	= $this->db->get()->result_array();
		$result = ($result) ? $result : array();
		return array('result'=>$result, 'total'=>$total);
    }
}
