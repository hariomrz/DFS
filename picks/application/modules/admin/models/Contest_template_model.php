<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contest_template_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
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
	 * Used for delete created template
	 * @param int $contest_template_id
	 * @return boolean
	 */
	public function delete_template($contest_template_id)
	{
		$this->db->where("contest_template_id",$contest_template_id);
		$this->db->delete(CONTEST_TEMPLATE);
		$is_deleted = $this->db->affected_rows();		
		return $is_deleted;
	}
	

	/**
	 * Used for get contest template details while creating copy template for a contest template
	 * @param $contest_template_id
	 * @return array
	 */
	public function get_contest_template_by_id($contest_template_id)
	{
		$this->db->select('CT.*,CT.guaranteed_prize AS prize_pool_type,CT.currency_type AS entry_fee_type,MG.group_name,DATE_FORMAT(CT.added_date, "%d-%b-%Y %H:%i") AS added_date,IFNULL(CT.template_title,"") as template_title', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER')
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

    /**
	 * get template deatil for create contest
	 * @param array $contest_data
	 * @return int
	 */
	public function get_template_details_for_create_contest($post_data = array())
	{	
		if(empty($post_data))
			return false;

		$this->db->select('CT.*', FALSE)		
			->from(CONTEST_TEMPLATE." AS CT")		
			->where_in('CT.contest_template_id',$post_data['selected_templates'])
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
	 * This function used to create template contest
	 * @param array $contest_data
	 * @return int
	 */

	public function get_group_list(){		
		$result = $this->db->select('*')
		->from(MASTER_GROUP)
		->where('status',1)
		->get()->result_array();
		return $result;
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
		
		$this->db->select('CT.*,MG.group_name,DATE_FORMAT(CT.added_date, "%d-%b-%Y %H:%i") AS added_date,IFNULL(CT.template_title,"") as template_title', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER');
			
		if(isset($post_data['sports_id'])&&$post_data['sports_id']!="")
		{
			$this->db->where('CT.sports_id',$post_data['sports_id']);
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
	 * [get_fixture_template description]
	 * Summary :- 
	 * @return [type] [description]
	 */
	public function get_fixture_template($post_data)
	{
		$season_id = $post_data['season_id'];
		$sports_id = $post_data['sports_id'];
		$this->db->select('CT.*,MG.group_name,IFNULL(CT.template_title,"") as template_title,CT.max_bonus_allowed', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER')
			->where('CT.sports_id',$sports_id);

			$this->db->join(CONTEST." AS C", 'C.contest_template_id = CT.contest_template_id AND C.season_id = "'.$season_id.'"','LEFT');
			$this->db->where('C.contest_id IS NULL');


			$this->db->group_by('CT.contest_template_id')
			->order_by("CT.group_id", "ASC");

		$result	= $this->db->get()->result_array();
		return $result;
	}

}