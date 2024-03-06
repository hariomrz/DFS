<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contest_template_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * used for get contest template list
	 * @param array $post_data
	 * @return array
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
		
		$this->db->select('CT.*,MG.group_name,CT.added_date,GROUP_CONCAT(CTL.league_id) as template_leagues,COUNT(CTL.id) as total_league,IFNULL(CT.template_title,"") as template_title', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER')
			->join(CONTEST_TEMPLATE_LEAGUE." AS CTL", 'CTL.contest_template_id = CT.contest_template_id','LEFT');

		if(isset($post_data['sports_id'])&&$post_data['sports_id']!="")
		{
			$this->db->where('CT.sports_id',$post_data['sports_id']);
		}

		if(isset($post_data['league_id'])&&$post_data['league_id']!="")
		{
			$this->db->where('CTL.league_id',$post_data['league_id']);
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CT.template_name', $post_data['keyword']);
		}

		$this->db->group_by('CT.contest_template_id');
		$sql = $this->db->order_by($sort_field, $sort_order)
					->get();

		$result	= $sql->result_array();
		return array('result' => $result, 'total' => count($result));
	}

	/**
	 * Used for save contest template data
	 * @param array $data
	 * @return int
	 */
	public function save_template($data)
	{	
		$this->db->insert(CONTEST_TEMPLATE,$data);
		return $this->db->insert_id();
	}

	/**
	 * Used for save contest template league data
	 * @param array $data
	 * @return int
	 */
	public function save_template_league($data)
	{
		$this->db->insert(CONTEST_TEMPLATE_LEAGUE,$data);
		return $this->db->insert_id();
	}

	/**
	 * Used for delete created template
	 * @param int $contest_template_id
	 * @return boolean
	 */
	public function delete_template($contest_template_id)
	{
		$this->db->where("contest_template_id",$contest_template_id);
		$this->db->delete(CONTEST_TEMPLATE);
		$is_deleted = $this->db->affected_rows();
		if($is_deleted){
			$this->db->where("contest_template_id",$contest_template_id);
			$this->db->delete(CONTEST_TEMPLATE_LEAGUE);
		}
		return $is_deleted;
	}

	/**
	 * This function used for delete previous template and save new selected templates
	 * @param array $post_data
	 * @return boolean
	 */
	public function apply_template_to_league($post_data)
	{
		$this->db->where("league_id",$post_data['league_id']);
		$this->db->delete(CONTEST_TEMPLATE_LEAGUE);

		foreach($post_data['selected_templates'] as $contest_template_id){
			$league_data = array();
			$league_data['contest_template_id'] = $contest_template_id;
			$league_data['league_id'] = $post_data['league_id'];
			$league_data['date_created'] = format_date();
			$this->db->insert(CONTEST_TEMPLATE_LEAGUE,$league_data);
		}

		return true;
	}

	/**
	 * Used for get contest template details while creating copy template for a contest template
	 * @param $contest_template_id
	 * @return array
	 */
	public function get_contest_template_by_id($contest_template_id)
	{
		$this->db->select('CT.*,CT.guaranteed_prize AS prize_pool_type,CT.currency_type AS entry_fee_type,MG.group_name,DATE_FORMAT(CT.added_date, "%d-%b-%Y %H:%i") AS added_date,GROUP_CONCAT(CTL.league_id) as template_leagues,COUNT(CTL.id) as total_league,IFNULL(CT.template_title,"") as template_title', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER')
			->join(CONTEST_TEMPLATE_LEAGUE." AS CTL", 'CTL.contest_template_id = CT.contest_template_id','LEFT')
			->where('CT.contest_template_id',$contest_template_id)
			->group_by("CT.contest_template_id");

		$result	= $this->db->limit(1)->get()->row_array();
		if(!empty($result)){
			$result['set_sponsor'] = 0;
			if(!empty($result['sponsor_name']) && $result['sponsor_name'] != '')
			{
				$result['set_sponsor'] = 1;
			}
		}
		return $result;
	}

	public function get_template_details_for_create_contest($post_data = array())
	{
		if(empty($post_data)){
			return false;
		}

		$this->db->select('CT.*', FALSE)
		->from(CONTEST_TEMPLATE." AS CT")
			->join(CONTEST." AS C", 'C.contest_template_id = CT.contest_template_id','LEFT')
		    ->where('CT.sports_id',$post_data['sports_id'])
			->where_in('CT.contest_template_id',$post_data['template'])
			->group_by('CT.contest_template_id')
			->order_by("CT.contest_template_id", "ASC");

		$result	= $this->db->get()->result_array();
		return $result;
	}

	/**
	 * This function used to create template contest
	 * @param array $contest_data
	 * @return int
	 */
	public function save_template_contest($contest_data)
	{
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

	/**
	 * [get_fixture_template description]
	 * Summary :- 
	 * @return [type] [description]
	 */
	public function get_fixture_template($post_data)
	{
		$collection_id = isset($post_data['collection_id']) ? $post_data['collection_id'] : 0;
		$this->db->select('CT.*,MG.group_name,IFNULL(CT.template_title,"") as template_title', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")			
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER')
			// ->where('CTL.league_id',$post_data['league_id']);
			->where('CT.sports_id',$post_data['sports_id']);

			if($collection_id != 0){
				$this->db->join(CONTEST." AS C", 'C.contest_template_id = CT.contest_template_id AND C.collection_id = "'.$collection_id.'"','LEFT');
				$this->db->where('C.contest_id IS NULL');
			}

			$this->db->group_by('CT.contest_template_id');
			$this->db->order_by("CT.group_id", "ASC");

		$result	= $this->db->get()->result_array();
		return $result;
	}

	public function get_group(){
		$sort_field	= 'group_id';
	    $sort_order	= 'DESC';
	    $limit		= 50;
	    $page		= 0;
	    $post_data = $this->input->post();
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
        
       $result = $this->db->select('group_id,group_name,description,icon,is_private,is_default')
       	->from(MASTER_GROUP)
       	->where('status',1)
       	->order_by($sort_field, $sort_order);
        
        $tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->limit($limit,$offset)->get();
        $result	= $result->result_array();
        return array('result'=>$result,'total'=>$total);
	}

	public function check_group_in_contest(){
		$group_id = $this->input->post('group_id');
		$result = $this->db->select('count(group_id) as count')
		->from(CONTEST)
		->where('group_id',$group_id)
		->get()->row_array();
		return $result;
	}

	public function update_group($post_data){
		$group_id = $this->input->post('group_id');
		$result = $this->db->update(MASTER_GROUP,$post_data,['group_id'=>$group_id]);
		return true;
	}
}