<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Avatars_model extends MY_Model{

	function __construct()
	{
		parent::__construct();
		// $this->db_fantasy		= $this->load->database('db_fantasy', TRUE);
	}

/**
	 * [change_avatar_status description]
	 * @MethodName change_avatar_status
	 * @Summary function to change the status of avatar as 1 for active, 0 for hidden avatars 
	 * as well set a new avatar for user profile if admin hide the pre set avatar in user profile
	 * @param      int id, tinyint status
	 * @return     true/false
	 */
public function change_avatar_status($id,$status){
	if($status==1){
		$status=0;
	}
	else{
		$status=1;
	}
	
	$status = $this->db->update(AVATARS,['status'=>$status],['id'=>$id]);
	if($status==TRUE){
		return true;
	}
	else{
		return false;
	}
}

/**
	 * [get_all_avatars description]
	 * @MethodName get_all_avatars
	 * @Summary function to get all avatars according to status to show on admin dashboard as active and hiddedn avatars
	 * @param      tinyint status 0 for hidden avatars and 1 for active avatars
	 * @return     array
	 */
public function get_all_avatars(){
		$sort_field	= 'added_date';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		$status =1;
		$post_data = $this->input->post();
		if($post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['status']))
		{
			$status = $post_data['status'];
		}

		if($post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
			// if($post_data['current_page']==1) {
			// 	$total = $this->get_all_user_counts($post_data); 
			// }
		}
		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('id','name','added_date')))
		{
			$sort_field = $post_data['sort_field'];
		}
		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;

		$avatar_list = $this->db->select('*')
		->from(AVATARS)
		->where('status',$status)
		->where('is_default',0)
		->order_by($sort_field, $sort_order);


		$total_count = clone $this->db; //to get rows for pagination
		$temp_q = $total_count->get();
		$total = $temp_q->num_rows();
		
		$avatar_list = $this->db->limit($limit,$offset)
		->get()->result_array();
		// echo $this->db->last_query();exit;
		return array('result'=>$avatar_list,'total'=>$total);
	}

//function to insert new avatars by admin 
/**
	 * [insert_avatar_image_post description]
	 * @MethodName insert_avatar_image_post
	 * @Summary function to insert new avatars by admin 
	 * @param      varchar image_name,tinyint is_default,tinyint status,datetime added_date
	 * @return     updated id 
	 */
public function insert_avatar_image_post($image_name){
	$current_date = date('Y-m-d H:i:s');
	$avatar_data=array(
		"name"=>$image_name,
		"is_default"=>0,
		"status"=>1,
		"added_date"=>$current_date,
	);
	$insert = $this->db->insert(AVATARS,$avatar_data);
	return $this->db->insert_id();
}

	/**
     * method to get a random avatar image fro default 10
     * @return     [array]
     */
 public function get_first_rendom_avatar(){
        $avatar = $this->db->select('name')
        ->from(AVATARS)
        ->where('is_default',1)
        ->where('status',1)
        ->order_by('rand()')->limit(1)->get()->row_array();
        return $avatar;
    }
	/**
     * this method is used to select all users have setted a particular avatar as profile picture
	 * @param bigint user_id
     * @return     [array]
     */
    public function select_users($name){
    	$userid = $this->db->select('user_id')
    	->from(USER)
    	->where('image',$name['name'])
    	->get()->result_array();
    	return $userid;
    	// echo $this->db->last_query();exit;
    }
/**
     * update profile image when no profile pucture 
     * @return     [array]
     */
public function update_user_profile($update_data){
	$update = $this->db->update_batch(USER,$update_data,'user_id');
}

}
/* End of file Avatars_model.php */
/* Location: ./application/models/Avatars_model.php */
?>