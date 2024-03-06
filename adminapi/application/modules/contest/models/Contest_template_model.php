<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contest_template_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
	}

	/**
    * Function used for get fixture template list
    * @param array $post_data
    * @return array
    */
	public function get_fixture_template($post_data)
	{
		$sports_id = $post_data['sports_id'];
		$cm_id = $post_data['collection_master_id'];
		$is_h2h = isset($post_data['is_h2h']) ? $post_data['is_h2h'] : 0;

		$this->db_fantasy->select('CT.*,IFNULL(CT.template_title,"") as template_title,IFNULL(CT.sponsor_name,"") as sponsor_name,IFNULL(CT.sponsor_logo,"") as sponsor_logo,IFNULL(CT.sponsor_link,"") as sponsor_link,MG.group_name', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER')
			->where('CT.sports_id',$sports_id)
			->group_by('CT.contest_template_id')
			->order_by("CT.group_id", "ASC");

		if($is_h2h == "1" && $cm_id){
			$this->db_fantasy->join(COLLECTION_TEMPLATE." AS CT1", 'CT1.contest_template_id = CT.contest_template_id AND CT1.collection_master_id = "'.$cm_id.'"','LEFT');
			$this->db_fantasy->where('CT1.id IS NULL');
		}else{
			$this->db_fantasy->join(CONTEST." AS C", 'C.contest_template_id = CT.contest_template_id AND C.collection_master_id = "'.$cm_id.'"','LEFT');
			$this->db_fantasy->where('C.contest_id IS NULL');
		}

		//check if rookie OFF or ON
		$rookie_group_id = (isset($this->app_config['allow_rookie_contest']) && isset($this->app_config['allow_rookie_contest']['custom_data']['group_id'])) ? $this->app_config['allow_rookie_contest']['custom_data']['group_id'] : 0;
		if($rookie_group_id > 0)
		{
			$this->db_fantasy->where('CT.group_id<>',$rookie_group_id);
		}

		//h2h template
		$h2h_group_id = (isset($this->app_config['h2h_challenge']) && isset($this->app_config['h2h_challenge']['custom_data']['group_id'])) ? $this->app_config['h2h_challenge']['custom_data']['group_id'] : 0;
		if($is_h2h == "1" && $h2h_group_id > 0){
			$this->db_fantasy->where('CT.group_id',$h2h_group_id);
		}else{
			$this->db_fantasy->where('CT.group_id != ',$h2h_group_id);
		}
		$result	= $this->db_fantasy->get()->result_array();
		return $result;
	}

	/**
    * Function used for get template list for create contest
    * @param array $post_data
    * @return array
    */
	public function get_template_list_for_create_contest($post_data)
	{
		if(empty($post_data)){
			return false;
		}

		//remove h2h template
		$h2h_group_id = (isset($this->app_config['h2h_challenge']) && isset($this->app_config['h2h_challenge']['custom_data']['group_id'])) ? $this->app_config['h2h_challenge']['custom_data']['group_id'] : 0;

		$this->db_fantasy->select('CT.*', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(CONTEST." AS C", 'C.contest_template_id = CT.contest_template_id AND C.collection_master_id = "'.$post_data['collection_master_id'].'"','LEFT')
			->where('CT.sports_id',$post_data['sports_id'])
			->where('C.contest_id IS NULL')
			->where_in('CT.contest_template_id',$post_data['template_ids'])
			->group_by('CT.contest_template_id')
			->order_by("CT.contest_template_id","ASC");
		if($h2h_group_id > 0){
			$this->db_fantasy->where('CT.group_id != ',$h2h_group_id);
		}
		$result	= $this->db_fantasy->get()->result_array();
		return $result;
	}

	/**
    * Function used for create contest from template
    * @param array $contest_data
    * @return array
    */
	public function save_template_contest($contest_data)
	{
		$this->db_fantasy->trans_start();

		$this->db_fantasy->insert_batch(CONTEST,$contest_data);

		$this->db_fantasy->trans_complete();
		$this->db_fantasy->trans_strict(FALSE);
		if ($this->db_fantasy->trans_status() === FALSE)
		{
		    $this->db_fantasy->trans_rollback();
			return false;
		}
		else
		{
			$this->db_fantasy->trans_commit();
			return true;
		}
	}

	/**
    * Function used for get template list for create contest
    * @param array $post_data
    * @return array
    */
	public function get_fixture_h2h_template_for_create_contest($post_data)
	{
		//remove h2h template
		$h2h_group_id = (isset($this->app_config['h2h_challenge']) && isset($this->app_config['h2h_challenge']['custom_data']['group_id'])) ? $this->app_config['h2h_challenge']['custom_data']['group_id'] : 0;

		$this->db_fantasy->select('CT.*', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(COLLECTION_TEMPLATE." AS C", 'C.contest_template_id = CT.contest_template_id AND C.collection_master_id = "'.$post_data['collection_master_id'].'"','LEFT')
			->where('CT.sports_id',$post_data['sports_id'])
			->where('CT.group_id',$h2h_group_id)
			->where('C.id IS NULL')
			->where_in('CT.contest_template_id',$post_data['template_ids'])
			->group_by('CT.contest_template_id')
			->order_by("CT.contest_template_id","ASC");
		$result	= $this->db_fantasy->get()->result_array();
		return $result;
	}

	/**
    * Function used for create contest from template
    * @param array $contest_data
    * @return array
    */
	public function save_fixture_h2h_template($data,$h2h=array())
	{
		$this->db_fantasy->trans_start();

		$this->db_fantasy->insert_batch(COLLECTION_TEMPLATE,$data);

		if(!empty($h2h)){
			$this->db_fantasy->update(COLLECTION_MASTER,array("is_h2h"=>1),array('collection_master_id'=>$h2h['collection_master_id'],"is_h2h"=>0));
		}

		$this->db_fantasy->trans_complete();
		$this->db_fantasy->trans_strict(FALSE);
		if ($this->db_fantasy->trans_status() === FALSE)
		{
		    $this->db_fantasy->trans_rollback();
			return false;
		}
		else
		{
			$this->db_fantasy->trans_commit();
			return true;;
		}
	}

	/**
	 * [contest_template_list description]
	 * Summary :- 
	 * @return [type] [description]
	 */
	public function get_contest_template_list($post_data)
	{
		$sort_field = 'CT.group_id';
		$sort_order = 'ASC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('template_name','minimum_size','size','entry_fee','added_date','prize_pool','is_auto_recurring','guaranteed_prize','max_bonus_allowed')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		
		$allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
		$rookie_group_id = (isset($this->app_config['allow_rookie_contest']) && isset($this->app_config['allow_rookie_contest']['custom_data']['group_id'])) ? $this->app_config['allow_rookie_contest']['custom_data']['group_id'] : 0;
		
		$this->db_fantasy->select('CT.*,MG.group_name,DATE_FORMAT(CT.added_date, "%d-%b-%Y %H:%i") AS added_date,IFNULL(CT.template_title,"") as template_title,CT.is_scratchwin,CT.is_2nd_inning', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER')
			->where('CT.sports_id',$post_data['sports_id']);

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db_fantasy->like('CT.template_name', $post_data['keyword']);
		}

		if(!$allow_2nd_inning)
		{
			$this->db_fantasy->where('CT.is_2nd_inning', 0);
		}

		if($rookie_group_id > 0)
		{
			$this->db_fantasy->where('CT.group_id<>',$rookie_group_id);
		}

		$this->db_fantasy->group_by('CT.contest_template_id');
		$sql = $this->db_fantasy->order_by($sort_field, $sort_order)
						->get();

		$result	= $sql->result_array();
		return array('result' => $result, 'total' => count($result));
	}

	/**
	 * [save_contest description]
	 * @MethodName save_contest
	 * @Summary This function used to create new contest template
	 * @param      array  data array
	 * @return     int
	 */
	public function save_template($data)
	{	
		$this->db_fantasy->insert(CONTEST_TEMPLATE,$data);
		return $this->db_fantasy->insert_id();
	}

	

	/**
	 * [delete_template description]
	 * @MethodName delete_template
	 * @Summary This function used for delete template
	 * @param      [array]  [data_arr]
	 * @return     [boolean]
	 */
	public function delete_template($data_arr)
	{
		$this->db_fantasy->where("contest_template_id",$data_arr['contest_template_id']);
		$this->db_fantasy->delete(CONTEST_TEMPLATE);
		$is_deleted = $this->db_fantasy->affected_rows();
		return $is_deleted;
	}

	

	public function get_collection_match_info($season_game_uid,$league_id)
    {
        $this->db_fantasy->select('S.season_game_uid,S.season_scheduled_date,S.format,S.2nd_inning_date,S.tournament_name', FALSE)
                ->from(SEASON." AS S")
                ->where('S.season_game_uid',$season_game_uid)
                ->where('S.league_id',$league_id);

        $result = $this->db_fantasy->get()->row_array();
        return $result;
	}
	
	public function get_group(){
	    $post_data = $this->input->post();
		$sort_field	= 'group_id';
	    $sort_order	= 'DESC';
	    $limit		= 50;
	    $page		= 0;
	    if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('group_id')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

        $offset	= $limit * $page;
        
        $result = $this->db_fantasy->select('group_id,group_name,description,icon,is_private,is_default')
		       ->from(MASTER_GROUP)
		       ->where('status',1)
		       ->order_by($sort_field, $sort_order);

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
        
        $tempdb = clone $this->db_fantasy; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db_fantasy->limit($limit,$offset)->get();
        $result	= $result->result_array();
        return array('result'=>$result,'total'=>$total);
	}

	public function get_single_row($field='*',$table,$where='1'){
		$result = $this->db_fantasy->select($field)
		->from($table)
		->where($where)
		->get()->row_array();
		return $result;
	}

	public function update_group($post_data){
		$group_id = $this->input->post('group_id');
		$result = $this->db_fantasy->update(MASTER_GROUP,$post_data,['group_id'=>$group_id]);
		return true;
	}

	public function check_group_in_contest(){
		$group_id = $this->input->post('group_id');
		$result = $this->db_fantasy->select('count(group_id) as count')
		->from(CONTEST)
		->where('group_id',$group_id)
		->get()->row_array();
		return $result;
	}

	/**
	 * method to get contest details while creating copy template for a contest template
	 */

	public function get_contest_template_by_id()
	{
		$contest_template_id = $this->input->post('contest_template_id');
		$this->db_fantasy->select('CT.*,CT.guaranteed_prize AS prize_pool_type,CT.currency_type AS entry_fee_type,MG.group_name,DATE_FORMAT(CT.added_date, "%d-%b-%Y %H:%i") AS added_date,IFNULL(CT.template_title,"") as template_title', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER');

		if(isset($contest_template_id) && $contest_template_id!="")
		{
			$this->db_fantasy->where('CT.contest_template_id',$contest_template_id);
		}

		$result	= $this->db_fantasy->limit(1)->get()->row_array();
		$result['set_sponsor']=0;
		if(!empty($result['sponsor_name']) && $result['sponsor_name']!='')
		{
			$result['set_sponsor']=1;
		}
		return array('result' => $result);
	}


	/**
	 * Function used for update league featured status
	 * @param array $post_data
	 * @return array
	 */
	public function update_auto_publish_status($post_data)
	{

		// echo '<pre>';
		// print_r($post_data);die;
		$update_arr = array("auto_published" => $post_data['auto_published']);
		$this->db_fantasy->where("contest_template_id",$post_data['contest_template_id']);
		$this->db_fantasy->update(CONTEST_TEMPLATE, $update_arr);
		return $this->db->affected_rows() || true;
	}
}