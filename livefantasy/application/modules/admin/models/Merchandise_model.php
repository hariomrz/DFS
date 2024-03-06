<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Merchandise_model extends MY_Model{

  	function __construct()
  	{
	  	parent::__construct();
	}

	/**
     * to get merchandise list
     * @param void
     * @return array
     */
	public function get_list()
	{
		$this->db->select('merchandise_id,name,image_name,price')
				->from(MERCHANDISE)
				->where('status','1')
				->order_by("merchandise_id","DESC");
		$result = $this->db->get()->result_array();
		return $result;
	}

	/**
     * insert in merchandise table
     * @param void
     * @return array
     */
	public function insert_merchandise($post_data)
	{
		$post_data['added_date'] = format_date();
		$post_data['updated_date'] = format_date();
		return $this->db->insert(MERCHANDISE,$post_data);
	}

	/**
     * to get merchandise info by id
     * @param void
     * @return array
     */
	public function get_merchandise_by_id($post_data)
	{
		$result = $this->db->select('name,price,image_name')
						->from(MERCHANDISE)
						->where("merchandise_id",$post_data['merchandise_id'])
						->get()
						->row_array();
		return $result;
	}

	/**
     * to all merchandise
     * @param void
     * @return array
     */
	public function get_all_merchandise($post_data)
	{
		$sort_field	= 'added_date';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		$total      = 0;

		if($post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if($post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
			if($post_data['current_page']==1) {
				$total = $this->get_all_merchandise_counts($post_data); 
			}
		}

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('price','name','added_date')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		$offset	= $limit * $page;
		$sql = $this->db->select("merchandise_id,name,image_name,price",FALSE)
					->from(MERCHANDISE)
					->order_by($sort_field, $sort_order);
		
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER(IFNULL(name,""))',strtolower($post_data['keyword']) );
		}

		$sql = $this->db->limit($limit,$offset)
					->get();
		$result	= $sql->result_array();
		$result=($result)?$result:array();
		return array('merchandise_list'=>$result,'total'=>$total);
	}

	/**
     * to get merchandise count
     * @param void
     * @return array
     */
	public function get_all_merchandise_counts($post_data){
		$this->db->select("count(merchandise_id) as total",FALSE)
        		->from(MERCHANDISE);
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER(IFNULL(name,""))',strtolower($post_data['keyword']));
		}

        $query = $this->db->get();
        $result = $query->result_array();
        return ($result[0]['total'])?$result[0]['total']:0;
	}

	/**
     * update merchandise by id
     * @param void
     * @return array
     */
	public function update_merchandise_by_id($update_data,$id)
	{
		$this->db->where("merchandise_id",$id);
		return $this->db->update(MERCHANDISE, $update_data);
	}	 
}
