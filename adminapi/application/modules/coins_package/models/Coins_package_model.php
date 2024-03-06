<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Coins_package_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
		//Do your magic here
	}

	/**
	* [get_all_package description]
	* @Summary : Use to get list of all package
	* @return array
	*/
	public function get_all_package()
	{  	 
		$sort_field	= 'coins';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		$post_data = $this->input->post();
		if($post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if($post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}

		if(!empty($post_data['sort_field']) && in_array($post_data['sort_field'],array('coins','amount','package_name','status')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(!empty($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$user_keyword	= !empty($post_data['keyword']) ? $post_data['keyword'] : "";

		$offset	= $limit * $page;
		$sql = $this->db->select("CP.coin_package_id,CP.coins,CP.amount,CP.package_name,CP.status,CP.package_name,CP.created_date",FALSE)
				->select("IFNULL((select count(OD.source_id) from `vi_order` AS `OD` where `OD`.`source_id`=`CP`.`coin_package_id` AND  OD.source = 282 group by OD.source_id),0) as reddem_users",FALSE)
								->from(COIN_PACKAGE.' AS CP')
								->order_by('CP.'.$sort_field, $sort_order);

		if($user_keyword != '')
        {
            $this->db->like('CP.package_name', $user_keyword);
        }
		
	
		if(isset($post_data['status']) && $post_data['status']==0)
		{
			$this->db->where("CP.status",$post_data['status']);
		} else if(isset($post_data['status']) && $post_data['status']==1){
			$this->db->where("CP.status",$post_data['status']);
		}

	
		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows(); 

		$sql = $this->db->limit($limit,$offset)
						->get();
		$result	= $sql->result_array();
		$result=($result)?$result:array();
		return array('result'=>$result,'total'=>$total);
	}

	/**
	 * [update_package description]
	 * @Summary This function used to update package
	 * @param      [varchar]  [package_id]
	 * @return     [boolean]
	 */
	public function update_package($package_id,$data_arr)
	{
		$this->db->where("coin_package_id",$package_id)
				->update(COIN_PACKAGE,$data_arr);
		return true;
	}

	/**
	 * [add_package description]
	 * @MethodName registration
	 * @Summary This function used to add new package
 	 * @param  [array] [data_array]
	 * @return array
	*/
	public function add_package($post)
	{
		$post["created_date"] = format_date();
		$this->db->insert(COIN_PACKAGE, $post);
		return $this->db->insert_id();
	}


	/**
	 * [package_redeem_list description]
	 * @MethodName package redeem lists
 	 * @param  [array] [data_array]
	 * @return array
	*/
	public function package_redeem_list(){

		$sort_field	= 'CP.coins';
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

		if(!empty($post_data['sort_field']) && in_array($post_data['sort_field'],array('U.user_name','OD.date_added','redeem_time')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(!empty($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$user_keyword	= !empty($post_data['keyword']) ? $post_data['keyword'] : "";

		$offset	= $limit * $page;
		$sql = $this->db->select("U.user_name,CP.coin_package_id,CP.package_name,CP.coins,CP.amount,count(CP.package_name) as redeem_time",FALSE)

			->select("(select OD1.date_added from `vi_order` AS `OD1` where `OD1`.user_id  = `OD`.user_id AND `OD1`.source_id=`CP`.coin_package_id order by `OD1`.date_added DESC limit 1) as date_added ",FALSE)
								->from(COIN_PACKAGE.' AS CP')
								->join(ORDER . " AS OD", "OD.source_id=CP.coin_package_id", "INNER")
								->join(USER . " AS U", "U.user_id=OD.user_id", "INNER")
								->where("OD.source",'282')
								->where("CP.coin_package_id",$post_data['package_id'])
								//->order_by('OD.date_added','ASC')
								->order_by($sort_field, $sort_order)
								->group_by('U.user_name');

		if($user_keyword != '')
        {
            $this->db->like('U.user_name', $user_keyword);
        }

	
		$tempdb = clone $this->db;
        $total = 0;
        if(isset($post_data['csv']) && $post_data['csv'] == false)
        {
            $query = $this->db->get();
            $total = $query->num_rows();
        }

        if(isset($post_data['csv']) && $post_data['csv'] == false)
        {
            $tempdb->limit($limit,$offset);
        }

        $sql = $tempdb->get();
        //echo $tempdb->last_query(); die;
         //exit();//   
        $result	= $sql->result_array();

        $result = ($result) ? $result : array();
        return array('result'=>$result,'total'=>$total);
	}

}
/* End of file Roles_model.php */
/* Location: ./application/models/Coins_package_model.php */
