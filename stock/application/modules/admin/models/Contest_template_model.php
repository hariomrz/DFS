<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contest_template_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
     * to get group list
     * @param array $post_data
     * @return array
     */
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

       	$result = $this->db->select('group_id,group_name,description,icon,is_private')
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

	/**
     * save contest group data
     * @param $post_data
     * @return array
     */
	public function save_group($post_data)
	{
		$this->db->insert(MASTER_GROUP,$post_data);
		return $this->db->insert_id();
	}

	/**
     * update contest group data
     * @param $post_data
     * @return array
     */
	public function update_group($post_data,$group_id){
		$result = $this->db->update(MASTER_GROUP,$post_data,array('group_id'=>$group_id));
		return true;
	}

	/**
     * used for check group contest exist or not
     * @param int $group_id
     * @return array
     */
	public function check_group_in_contest($group_id){
		$result = $this->db->select('count(group_id) as count')
					->from(CONTEST)
					->where('group_id',$group_id)
					->get()->row_array();
		return $result;
	}

	/**
	 * [save_contest description]
	 * @MethodName save_template
	 * @Summary This function used to create new contest template
	 * @param      array  data array
	 * @return     int
	 */
	public function save_template($data)
	{	
		$this->db->insert(CONTEST_TEMPLATE,$data);
		
		return $this->db->insert_id();
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
		
		$this->db->select('CT.*,MG.group_name,DATE_FORMAT(CT.added_date, "%d-%b-%Y %H:%i") AS added_date,GROUP_CONCAT(CTL.category_id) as template_categories,COUNT(CTL.template_category_id) as total_category,IFNULL(CT.contest_title,"") as template_title', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER')
			->join(CONTEST_TEMPLATE_CATEGORY." AS CTL", 'CT.template_id = CTL.template_id','LEFT');

		
		if(isset($post_data['category_id'])&&$post_data['category_id']!="")
		{
			$this->db->where('CTL.category_id',$post_data['category_id']);
		}

		if(isset($post_data['stock_type'])&&$post_data['stock_type']!="")
		{
			$this->db->where('CT.stock_type',$post_data['stock_type']);
		}
		else{
			$this->db->where('CT.stock_type',1);
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CT.contest_name', $post_data['keyword']);
		}

		$this->db->group_by('CT.template_id');
		$sql = $this->db->order_by($sort_field, $sort_order)
						->get();

		$result	= $sql->result_array();
		
		return array('result' => $result, 'total' => count($result));
	}

	/**
	 * [apply_template_to_category description]
	 * @MethodName apply_template_to_category
	 * @Summary This function used for delete previous template and save new selected templates
	 * @param      [array]  [data_arr]
	 * @return     [boolean]
	 */
	public function apply_template_to_category($data_arr)
	{
		$this->db->where("category_id",$data_arr['category_id']);
		$this->db->delete(CONTEST_TEMPLATE_CATEGORY);

		foreach($data_arr['selected_templates'] as $contest_template_id){
			$league_data = array();
			$league_data['template_id'] = $contest_template_id;
			$league_data['category_id'] = $data_arr['category_id'];
			$league_data['added_date'] = format_date();
			$this->db->insert(CONTEST_TEMPLATE_CATEGORY,$league_data);
		}

		return true;
	}

	/**
	 * [get_fixture_template description]
	 * Summary :- 
	 * @return [type] [description]
	 */
	public function get_fixture_template($post_data)
	{
		$collection_id = $post_data['collection_id'];

		$this->db->select('CT.*,MG.group_name,IFNULL(CT.contest_title,"") as template_title,CT.max_bonus_allowed', FALSE)			
			->from(CONTEST_TEMPLATE." AS CT")			
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER')
			->join(CONTEST." AS C", 'C.template_id = CT.template_id AND C.collection_id = "'.$collection_id.'"','LEFT')
			->where('C.contest_id IS NULL')
			->group_by('CT.template_id')
			->order_by("CT.group_id", "ASC");

			if(isset($post_data['keyword']) && !empty($post_data['keyword'])) {
				$this->db->like('CT.contest_name',$post_data['keyword']);
			}

		$result	= $this->db->get()->result_array();
		return $result;
	}

	public function get_template_details_for_create_contest($post_data = array())
	{
		if(empty($post_data))
			return false;

		$this->db->select('CT.*', FALSE)
			
			->from(CONTEST_TEMPLATE." AS CT")
			->where_in('CT.template_id',$post_data['selected_templates'])
			->group_by('CT.template_id')
			->order_by("CT.template_id", "ASC");

		$result	= $this->db->get()->result_array();
		//echo $this->db->last_query();die;
		return $result;
	}

	/**
	 * method to get contest details while creating copy template for a contest template
	 */

	public function get_contest_template_by_id()
	{
		$contest_template_id = $this->input->post('contest_template_id');
		$this->db->select('CT.*,CT.guaranteed_prize AS prize_pool_type,CT.currency_type AS entry_fee_type,MG.group_name,DATE_FORMAT(CT.added_date, "%d-%b-%Y %H:%i") AS added_date,GROUP_CONCAT(CTC.category_id) as template_categories,COUNT(CTC.template_category_id) as total_category,IFNULL(CT.contest_title,"") as template_title', FALSE)
			->from(CONTEST_TEMPLATE." AS CT")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = CT.group_id','INNER')
			->join(CONTEST_TEMPLATE_CATEGORY." AS CTC", 'CTC.template_id = CT.template_id','LEFT');

		if(isset($contest_template_id) && $contest_template_id!="")
		{
			$this->db->where('CT.template_id',$contest_template_id);
		}

		$result	= $this->db->limit(1)->get()->row_array();
		$result['set_sponsor']=0;
		if(!empty($result['sponsor_name']) && $result['sponsor_name']!='')
		{
			$result['set_sponsor']=1;
		}
		return array('result' => $result);
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
		$this->db->where("template_id",$data_arr['contest_template_id']);
		$this->db->delete(CONTEST_TEMPLATE);
		$is_deleted = $this->db->affected_rows();
		if($is_deleted){
			$this->db->where("template_id",$data_arr['contest_template_id']);
			$this->db->delete(CONTEST_TEMPLATE_CATEGORY);
		}
		return $is_deleted;
	}

	/**
	 * [save_template_category description]
	 * @MethodName save_template_category
	 * @Summary This function used add mapping for category
	 * @param      array  data array
	 * @return     int
	 */
	public function save_template_category($data)
	{
		$this->db->insert(CONTEST_TEMPLATE_CATEGORY,$data);
		return $this->db->insert_id();
	}




}