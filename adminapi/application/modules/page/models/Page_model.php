<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Page_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		//Do your magic here
		//$this->admin_id = $this->session->userdata('admin_id');
	}

	public function get_all_page_list()
	{
		$sort_field = 'sort_order';
		$sort_order = 'ASC';
		$limit      = 50;
		$page       = 0;
		
		$post_data = $this->input->post();

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('page_title','page_alias', 'meta_keyword', 'meta_desc','status', 'modified_date','sort_order')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		
		$offset	= $limit * $page;
		$this->db->select('page_id, en_page_title as page_title,page_alias, en_meta_keyword as meta_keyword, en_meta_desc as meta_desc, LEFT(en_page_content, 100) AS page_content , status, DATE_FORMAT(modified_date, "%d-%b-%Y %H:%i") AS modified_date', FALSE)
				->from(CMS_PAGES)				
						->where('sort_order IS NOT NULL');
						
		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();

		$sql = $tempdb->order_by($sort_field, $sort_order)
						->limit($limit, $offset)
						->get();
		// echo $tempdb->last_query();die;
		$result	= $sql->result_array();

		$result = ($result) ? $result : array();
		return array('result'=>$result, 'total'=>$total);
	}
        
        public function get_page_detail($page_id,$language='en')
	{
		$sql = $this->db->select("page_id,
									page_alias,
									page_url,
									status,
									modified_by,
									added_date,
									modified_date,
									custom_data,
									".$language."_meta_keyword as meta_keyword,
									".$language."_page_title as page_title,
									".$language."_meta_desc as meta_desc,
									".$language."_page_content as page_content
									")
						->from(CMS_PAGES)
						->where("page_id", $page_id)
						->get();
		$result = $sql->row_array();

		//echo $this->db->last_query();die();
		return $result; 
	}

        public function update_page($data, $page_id)
	{
		$this->db->where("page_id", $page_id)
				->update(CMS_PAGES, $data);
		return $this->db->affected_rows();
	}

        
        
        
        
	public function update_blog_status($blog_id,$data)
	{
		$this->db->where("blog_id", $blog_id)
				->update(BLOG, $data);
		return $this->db->affected_rows();
	}

	public function delete_blog($blog_id)
	{
		$this->db->where("blog_id", $blog_id)
				->delete(BLOG);

		$this->db->where("blog_id", $blog_id)
				->delete(BLOG_COMMENTS);
		return TRUE;
	}

	

	
	public function get_all_blog_comments($blog_id)
	{
		$sort_field = 'BC.created_date';
		$sort_order = 'DESC';
		$limit      = 10;
		$page       = 0;
		
		$post_data = $this->input->post();

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}
		
		$offset	= $limit * $page;
		$this->db->select("BC.*", FALSE)
				 ->from(BLOG_COMMENTS." BC")
				 ->where("blog_id", $blog_id);

	    $tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();

		$sql = $tempdb->limit($limit, $offset)
						->get();
		$commnet_rs	= $sql->result_array();

		if(!empty($commnet_rs))
		{				
			$users_id = array_column($commnet_rs,'user_id');
		}
		
		$user_rs = array();
		if(!empty($users_id)){
			$user_rs = $this->user_db->select("U.user_name,U.image", FALSE)
					 ->from(USER." U")
					 ->where_in("U.user_id", $users_id)->get()->result_array();
	    }

	    $result = array();
		foreach ($commnet_rs as $key => $value) 
		{
			$user_key			= array_search( $value['user_id'], array_column($user_rs, 'user_id'));
			$value				= array_merge($value,$user_rs[$user_key]);
			if($value['image']	== "")
			{
				$value['image']		= base_url()."assets/images/default_user.png";
			}
			
			$result[]			= $value;
		}

		return array('result'=>$result, 'total'=>$total);
	}

	public function delete_blog_comment($blog_comment_id,$blog_id)
	{
		$this->db->where("blog_comment_id", $blog_comment_id)
				->delete(BLOG_COMMENTS);

		$delete_check = $this->db->affected_rows();
		$blog_detail =  $this->db->select("no_of_comments")->from(BLOG)->where("blog_id", $blog_id)->get()->row_array();
		
		if(!empty($blog_detail))
		{
			$no_of_comments = $blog_detail['no_of_comments'];
			if($delete_check>0)
			{
				$no_of_comments = 0;
				if($blog_detail['no_of_comments']>0)
				{
					$no_of_comments = $blog_detail['no_of_comments']-1;
				}
				$this->db->where("blog_id", $blog_id)->update(BLOG,array("no_of_comments"=>$no_of_comments));
			
			}
		}
		return TRUE;
	}

	 public function get_faq_category($language='en')
	{
		$sql = $this->db->select("category_id,
									category_alias,
									".$language."_category as category")
						->from(FAQ_CATEGORY)
						->where("status", 1)
						->get();
		$result = $sql->result_array();

		// echo $this->db->last_query();die();
		return $result; 
	}

	public function get_faq_question_answer($category_id='',$language='en'){
		$sql = $this->db->select("question_id,
									".$language."_question as question,"
									.$language."_answer as answer,
									added_date"
								)
						->from(FAQ_QUESTIONS)
						->where('status',1)
						->where($language.'_question!=',NULL);
						if($category_id!='' && !empty($category_id)){
							$sql = $this->db->where('category_id',$category_id);
						}
						$sql = $this->db->get();
		$result = $sql->result_array();
		return $result; 
	}

	public function get_question_count($language='en'){
		$result = $this->db->select("FC.".$language."_category as category_name,count(FQA.".$language."_question) as question_count,FC.category_id")
		->from(FAQ_QUESTIONS." FQA")
		->join(FAQ_CATEGORY." FC","FC.category_id=FQA.category_id","INNER")
		->where($language.'_question!=',NULL)
		->where('FQA.status',1)
		->group_by("FQA.category_id")
		->get()->result_array();
		// echo $this->db->last_query();die();
		return $result;
	}

	public function add_question_answer($post_data){
		// print_r($post_data);exit;
		$language = 'en';
		
		if(isset($post_data['language']) && !empty($post_data['language'])){
		$language = $post_data['language'];
		}
		$insert_data = array();
		$current_date = format_date('today','Y-m-d');
		foreach($post_data['questions'] as $key=>$question){
			if(!empty($post_data['category_id']) && !empty($question['question']) && !empty($question['answer'])){

				$insert_data[$key] = array(
					"category_id"=>$post_data['category_id'],
					$language."_question"=>$question['question'],
					$language."_answer"=>$question['answer'],
					"added_date"=>$current_date,
				);
			}
		}
		$inserted = $this->db->insert_batch(FAQ_QUESTIONS,$insert_data);
		if($inserted){
			$inserted_id = $this->db->insert_id();
			$modified_date['modified_date']	= format_date('today');
			$this->db->update(CMS_PAGES,$modified_date,['page_id'=>5]);
		}
			return $inserted_id;

	}

	public function delete_question_answer($question_id){
		$where =array('question_id'=>$question_id);
		$deleted = $this->db->delete(FAQ_QUESTIONS,$where);
		if($deleted){
			$modified_date['modified_date']	= format_date('today');
			$this->db->update(CMS_PAGES,$modified_date,['page_id'=>5]);
		return TRUE;
		}
		return FALSE;
	}

	public function update_question_answer($post_data){
		$language = 'en';

		if(isset($post_data['language']) && !empty($post_data['language'])){
		$language = $post_data['language'];
		}

		$update_data = array();
		foreach($post_data['questions'] as $key=>$question){
			if(!empty($question['question_id']) && !empty($question['question']) && !empty($question['answer'])){
				$update_data[$key] = array(
					'question_id'=>$question['question_id'],
					$language."_question"=>$question['question'],
					$language."_answer"=>$question['answer'],
				);
			}
		}
		$update = $this->db->update_batch(FAQ_QUESTIONS,$update_data,'question_id');
		if($update==true){
			$modified_date['modified_date']	= format_date('today');
			$date_update = $this->db->update(CMS_PAGES,$modified_date,['page_id'=>5]);
		}
			return $this->db->affected_rows();
		
	}

	/**
     * Used for delete file from s3 bucket
     * @return 
     */
    public function delete_s3_bucket_file($file_name) {

        $json_file_name = BUCKET_STATIC_DATA_PATH . BUCKET_DATA_PREFIX . $file_name;
        try{
            $data_arr = array();
            $data_arr['file_path'] = $json_file_name;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_deleted = $upload_lib->delete_file($data_arr);
            if($is_deleted){
                return true;
            }else {
                return false;
            }

        }catch(Exception $e){
            return false;
        }
    }
}
