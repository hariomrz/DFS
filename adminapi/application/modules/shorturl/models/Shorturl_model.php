<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Shorturl_model extends MY_Model 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
	}

	/**
	 * [get_shortened_url description]
	 * @MethodName get_shortened_url
	 * @Summary This function used get shortened_url
	 * @param      
	 * @return     [array]
	 */
	public function get_shortened_url()
	{	
		$post_value = $this->input->post();

		$this->db->select('SU.*');
		$this->db->from(SHORT_URLS.' SU');
		$this->db->where('SU.user_id',0);

		if(!empty($post_value['url_type']))
		{
			$this->db->where('SU.url_type',$post_value['url_type']);
		}
		if(!empty($post_value['url_type_id']))
		{
			$this->db->where('SU.url_type_id',$post_value['url_type_id']);
		}

		if(!empty($post_value['source_type']))
		{
			$this->db->where('SU.source_type',$post_value['source_type']);
		}

		if(!empty($post_value['shortened_id']))
		{
			$this->db->where('SU.shortened_id',$post_value['shortened_id']);
		}

		$result = array();


		$Query =  $this->db->get();

		if($Query->num_rows()>0)
		{
			$result = $Query->result_array();
		}
		
		return $result;

	}


	public function get_shortened_url_by_id()
	{	
		$post_value = $this->input->post();

		$cache['fn'] = 'shorten_url_'.$post_value['shortened_id'];
		$reponse = $this->redis_cache->get($cache, TRUE);

		if(!$reponse)
		{
			$this->db->select('SU.*');
			$this->db->from(SHORT_URLS.' SU');
			$this->db->where('SU.shortened_id',$post_value['shortened_id']);

			$reponse = new stdClass();

			$Query =  $this->db->get();

			if($Query->num_rows()>0)
			{
				$reponse = $Query->row_array();

				$this->redis_cache->set(array('fn'=>'shorten_url_'.$post_value['shortened_id'],'data' => $reponse),REDIS_7_DAYS);
			}

		}
		
		return $reponse;

	}


	public function save_shortened_url()
	{	
		$post_value = $this->input->post();

		$url_data  = $post_value['url_data'];

		$return = array();

		foreach ($url_data as $key => $value) {
			
			$url_data = $this->is_url_exist($value['url'],0);

			if(!$url_data)
			{
				$ins_data['shortened_id'] 	= generateRandomString(6);
				$ins_data['url'] 			= $value['url'];
				$ins_data['user_id'] 		= 0;
				$ins_data['url_type'] 		= isset($value['url_type'])?$value['url_type']:'';
				$ins_data['url_type_id'] 	= isset($value['url_type_id'])?$value['url_type_id']:'';
				$ins_data['source_type'] 	= isset($value['source_type'])?$value['source_type']:'';
				$ins_data['added_date']		= format_date();

				$insert_arr[] = $ins_data;

				$return[] = $ins_data;
			}
			else
			{
				$return[] = $url_data;
			}


		}

		if(!empty($insert_arr))
		{
			$this->db->insert_batch(SHORT_URLS,$insert_arr);
		}

		return $return;
	}
	
	function is_url_exist($url,$user_id)
	{
		$cache['fn'] = $user_id.'is_url_exist_'.$url;
        $reponse = $this->redis_cache->get($cache, TRUE);

        if(!$reponse)
        {
        	$this->db->select('SU.user_id,SU.shortened_id,SU.url,SU.url_type,SU.url_type_id,SU.source_type');
			$this->db->from(SHORT_URLS.' SU');
			$this->db->where('SU.url',$url);
			$this->db->where('SU.user_id',$user_id);
			$Query = $this->db->get();

			if($Query->num_rows()>0)
			{
				$reponse = $Query->row_array();
			}

			 $this->redis_cache->set(array('fn'=>$user_id.'is_url_exist_'.$url,'data' => $reponse),REDIS_7_DAYS);
        }

        return $reponse;
	}


}