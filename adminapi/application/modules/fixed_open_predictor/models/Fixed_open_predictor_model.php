<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Fixed_open_predictor_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_open_predictor		= $this->load->database('db_fixed_open_predictor', TRUE);
		$this->db_user		= $this->load->database('db_user', TRUE);
		$this->db_fantasy		= $this->load->database('db_fantasy', TRUE);
		
    }

    public function add_category($category_data)
	{
		$this->db_open_predictor->insert(CATEGORY,$category_data);
		return $this->db_open_predictor->insert_id();
	}
	
	public function update_category($category_id,$category_data)
	{
		$this->db_open_predictor->where('category_id',$category_id);
		$this->db_open_predictor->update(CATEGORY,$category_data);
		return $this->db_open_predictor->affected_rows();
	}
	/**
	 * 
	 * @method get_all_categoty
	 * @uses This function used for get predictions
	 * @param      na
	 * @return     [array]
	 */
	public function get_all_category()
	{
		$sort_field = 'question_count';
		$sort_order = 'DESC';
		
		$post_data  = $this->input->post();
	
		if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']) && $post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$current_date = format_date();

		$pre_sql="(SELECT prediction_master_id ,IF(deadline_date<'{$current_date}' AND total_user_joined=0, 0, 1) as valid_prediction FROM  ".$this->db_open_predictor->dbprefix(PREDICTION_MASTER).")";

		$default_selected_columns = "C.*, SUM(IF((PM.status=0 AND COMMON.valid_prediction=1) OR (PM.status=3 AND COMMON.valid_prediction=1),1,0)) as question_count,SUM(IF(((PM.status=0 AND PM.deadline_date >'{$current_date}') OR (PM.status=0 AND PM.deadline_date <'{$current_date}' AND PM.total_user_joined>0)) OR PM.status=1 OR PM.status=3,1,0)) as open_count, PM.status as prediction_status,PM.prediction_master_id,SUM(IF(PM.status=2,1,0)) as completed_count,COMMON.valid_prediction";
		
		$select  = (isset($post_data['select'])) ? $post_data['select'] : $default_selected_columns;
		
      
		$sql = $this->db_open_predictor->select($select,FALSE)
						->from(CATEGORY." C");

		if($post_data['status'] ==1)
		{
			$this->db_open_predictor->join(PREDICTION_MASTER." PM",'PM.category_id=C.category_id',"INNER")
			->join($pre_sql." COMMON","COMMON.prediction_master_id=PM.prediction_master_id");
			$this->db_open_predictor->where_in('PM.status',array(0,3));
		}				
		else
		{
			$this->db_open_predictor->join(PREDICTION_MASTER." PM",'PM.category_id=C.category_id',"LEFT")
			->join($pre_sql." COMMON","COMMON.prediction_master_id=PM.prediction_master_id","LEFT");
		}

		$sort_field = 'question_count';
		$sort_order = 'DESC';
		if($post_data['status'] ==1)
		{
			$sort_field = 'open_count';
			$sort_order = 'DESC';
			$this->db_open_predictor->having('open_count>',0);
		}
		else
		{
			$sort_field = 'completed_count';
			$sort_order = 'DESC';
			$this->db_open_predictor->having('open_count',0);
		}

		$sql = $this->db_open_predictor->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db_open_predictor;
		$this->db_open_predictor->group_by('C.category_id');

		
		$query  = $this->db_open_predictor->get();
		$total  = $query->num_rows();
		
		$offset = 0;
        if(isset($limit) && isset($page))
        {
			$offset	= $limit * $page;
            $tempdb->limit($limit,$offset);
        }
		
		$sql = $tempdb->group_by('C.category_id')
		->order_by('C.added_date','DESC')
		->get();
		//echo $this->db_open_predictor->last_query();die('fdfd');
		$result = $sql->result_array();
		$next_offset = count($result) + $offset;

		return array('result'=>$result,'total'=>$total, 'next_offset' => $next_offset );
	}

	/**
	 * 
	 * @method get_all_categoty
	 * @uses This function used for get predictions
	 * @param      na
	 * @return     [array]
	 */
	public function get_category_by_status()
	{
		$sort_field = 'C.category_id';
		$sort_order = 'ASC';
		

		$post_data  = $this->input->post();
	
		if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']) && $post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}
		
		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$default_selected_columns = "C.*";
		
		$select  = (isset($post_data['select'])) ? $post_data['select'] : $default_selected_columns;
		
      
		$sql = $this->db_open_predictor->select($select,FALSE)
						->from(CATEGORY." C")
						->where('C.status',1);

		

		$sql = $this->db_open_predictor->order_by($sort_field, $sort_order);

		//$this->db_open_predictor->where("PM.status >",0);
		$tempdb = clone $this->db_open_predictor;
		$query  = $this->db_open_predictor->get();
		//echo $this->db_open_predictor->last_query();die('fdfd');
		$total  = $query->num_rows();

		$offset = 0;
        if(isset($limit) && isset($page))
        {
            $offset	= $limit * $page;
            $tempdb->limit($limit,$offset);
        }

		$sql = $tempdb->group_by('C.category_id')->order_by('C.added_date','DESC')
		->get();
		$result = $sql->result_array();
		$next_offset = count($result) + $offset;

		//echo $this->db->last_query();die();
		//  }//foreach
		return array('result'=>$result,'total'=>$total, 'next_offset' => $next_offset );
	}
	
	public function add_prediction($prediction_data)
	{
		$this->db_open_predictor->insert(PREDICTION_MASTER,$prediction_data);
		return $this->db_open_predictor->insert_id();
	}

	public function update_prediction($prediction_master_id,$prediction_data)
	{
		$this->db_open_predictor->where('prediction_master_id',$prediction_master_id);
		$this->db_open_predictor->update(PREDICTION_MASTER,$prediction_data);
		return $this->db_open_predictor->affected_rows();
	}

	public function update_prediction_option($prediction_master_id,$option_data)
	{
		$this->db_open_predictor->where('prediction_master_id', $prediction_master_id)
		->delete(PREDICTION_OPTION);
		$this->db_open_predictor->insert_batch(PREDICTION_OPTION,$option_data);
		return true;
	}

	public function insert_prediction_option($option_data)
	{
		$this->db_open_predictor->insert_batch(PREDICTION_OPTION,$option_data);
		return true;
	}

	function get_prediction_details($prediction_master_id)
	{
		$current_date = format_date();
		$result =  $this->db_open_predictor->select('PM.*')
		->from(PREDICTION_MASTER.' PM' )
		->where('prediction_master_id',$prediction_master_id)
		->get()->row_array();

		$result['option'] =$this->db_open_predictor->select('PO.*,
		0 as prediction_count,"" as user_id')
		->from(PREDICTION_OPTION.' PO' )
		->where('PO.prediction_master_id',$prediction_master_id)
		->group_by('PO.prediction_option_id')->get()->result_array();

		$result['deadline_time'] = strtotime($result['deadline_date'])*1000000 ;
		$result['today'] = strtotime($current_date)*1000000 ;

		return $result;	
	}
	
	public function get_season_question_count($category_ids)
	{
		$result = $this->db_open_predictor->select('COUNT(category_id) as question_count,category_id',FALSE)
		->from(PREDICTION_MASTER)
		->where_in('category_id',$category_ids)
		->group_by('category_id')->get()->result_array();
		return $result;
	}	

	/**
	 * 
	 * @method get_all_prediction
	 * @uses This function used for get predictions
	 * @param      na
	 * @return     [array]
	 */
	public function get_all_prediction()
	{
		$sort_field = 'deadline_date';
		$sort_order = 'ASC';
		
		$post_data  = $this->input->post();
	
		if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']) && $post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('team_name','team_abbr','sports_name','association_name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$current_date=format_date();
		$pre_sql="(SELECT prediction_master_id ,IF(deadline_date<'{$current_date}' AND total_user_joined=0, 0, 1) as valid_prediction FROM  ".$this->db_open_predictor->dbprefix(PREDICTION_MASTER).")";

						
		$default_selected_columns = "PM.prediction_master_id,PM.desc,PM.category_id,DATE_FORMAT(PM.deadline_date, '".MYSQL_DATE_TIME_FORMAT."') as deadline_date,PM.status,PM.total_user_joined,PM.is_pin,PM.source_desc,PM.source_url,PM.proof_desc,PM.proof_image,COMMON.valid_prediction";
		
		$select  = (isset($post_data['select'])) ? $post_data['select'] : $default_selected_columns;
		
      
		$sql = $this->db_open_predictor->select($select,FALSE)
						->from(PREDICTION_MASTER." PM")
						->join($pre_sql." COMMON","COMMON.prediction_master_id=PM.prediction_master_id")
						->order_by($sort_field, $sort_order);

		if(isset($post_data['category_id']) && $post_data['category_id']!="")
		{
			$this->db_open_predictor->where("PM.category_id",$post_data['category_id']);
		}

		if(isset($post_data['status']) && in_array($post_data['status'],array(0,1)))
		{
			if($post_data['status'] == 0)
			{
				$this->db_open_predictor->where_in("PM.status",array(0,3));
				$this->db_open_predictor->where("COMMON.valid_prediction",1);
			}
			else
			{
				$this->db_open_predictor->where("PM.status >",0);
			}	
		}

		if(isset($post_data['status']) && in_array($post_data['status'],array(2,4)))//for completed deleted
		{
			$this->db_open_predictor->where_in("PM.status ",array(2,4));
		}

		$tempdb = clone $this->db_open_predictor;
		$query  = $this->db_open_predictor->get();
		$total  = $query->num_rows();

		$offset = 0;
        if(isset($limit) && isset($page))
        {
            $offset	= $limit * $page;
            $tempdb->limit($limit,$offset);
        }

		$sql = $tempdb->order_by('PM.is_pin','DESC')
		->order_by('PM.added_date','DESC')
		->get();
		$result = $sql->result_array();
		if(!empty($post_data['is_debug']) && $post_data['is_debug'] ==1)
		{
			echo $this->db_open_predictor->last_query();die();
		}

		//fetch options for predictions
		if(!empty($result))
		{
			foreach ($result as $key => $value)
			{
				$selected_option_id      = "";
				$prediction_master_id    = $value['prediction_master_id'];
				$result[$key]['options'] = $this->get_prediction_options($prediction_master_id);
				if(!empty($result[$key]['options']))
				{
					foreach ($result[$key]['options'] as $okey => $ovalue)
					{
					    if(!empty($ovalue['is_correct']))
					    {
					    	$selected_option_id = $ovalue['prediction_option_id'];
					    }		
					}
				}	

				$result[$key]['selected_option_id'] = $selected_option_id;
			}
		}	

		$next_offset = count($result) + $offset;

		//echo $this->db->last_query();die();
		//  }//foreach
		return array('result'=>$result,'total'=>$total, 'next_offset' => $next_offset );
	}

	public function get_prediction_options($prediction_master_id)
	{

		$optionSql = $this->db_open_predictor->select("PO.prediction_master_id,PO.prediction_option_id,PO.`option`,PO.is_correct,COUNT(UP.user_id) as prediction_count",FALSE)
						->from(PREDICTION_OPTION.' PO')
						->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id",'LEFT')
						->where("PO.prediction_master_id",$prediction_master_id)
						->group_by('PO.prediction_option_id')
						->order_by("PO.prediction_option_id","ASC")
						->get();

						//echo $this->db_open_predictor->last_query();die;
		return ($optionSql->num_rows() > 0) ? $optionSql->result_array() : array();				

	}

	function get_one_prediction($prediction_master_id)
	{
		return $this->db_open_predictor->select('*')
		->from(PREDICTION_MASTER)
		->where('prediction_master_id',$prediction_master_id)->get()->row_array();
	}

	function pause_play_prediction($pause,$prediction_master_id)
    {
		if($pause)
		{
			$this->db_open_predictor->where('prediction_master_id',$prediction_master_id)
			->update(PREDICTION_MASTER,array('status'=> 3));//pause
		}
		else
		{
			$this->db_open_predictor->where('prediction_master_id',$prediction_master_id)
			->update(PREDICTION_MASTER,array('status'=> 0));//pause
		
		}
       
       return $this->db_open_predictor->affected_rows();    
	}

	function update_pin_prediction($status,$prediction_master_id)
    {
       $this->db_open_predictor->where('prediction_master_id',$prediction_master_id)
       ->update(PREDICTION_MASTER,array('is_pin'=> $status));
       return $this->db_open_predictor->affected_rows();    
	}

	function delete_prediction($prediction_master_id)
	{
		$this->db_open_predictor->where('prediction_master_id',$prediction_master_id)
			->update(PREDICTION_MASTER,array('status'=> 4));//delete
		return $this->db_open_predictor->affected_rows(); 
	}

	public function get_prediction_answer($prediction_master_id)
	{
		return  $this->db_open_predictor->select('PO.prediction_option_id,PO.is_correct')
		->from(PREDICTION_MASTER.' PM')
		->join(PREDICTION_OPTION.' PO','PM.prediction_master_id=PO.prediction_master_id')
		->where('PM.prediction_master_id',$prediction_master_id)
		->get()->row_array();
	}

	public function update_prediction_results($prediction_option_id)
	{

		$this->db_open_predictor->where('prediction_option_id',$prediction_option_id)
       ->update(PREDICTION_OPTION,array('is_correct'=> 1));
		return true;	
	}

	public function update_prediction_result_status($prediction_master_id,$update_data)
	{
		$this->db_open_predictor->where('prediction_master_id',$prediction_master_id)
       ->update(PREDICTION_MASTER,$update_data);
		//$this->db_open_predictor->update_batch(PREDICTION_MASTER, $prediction_status_data, 'prediction_master_id');
		return true;	
	}

	/**
	 * 
	 * @method get_one_prediction_details
	 * @uses This function used for get one prediction details
	 * @param      na
	 * @return     [array]
	 */
	function get_one_prediction_details($prediction_master_id)
	{
		$default_selected_columns = "prediction_master_id,category_id,`desc`,DATE_FORMAT(deadline_date, '".MYSQL_DATE_TIME_FORMAT."') as deadline_date,status,total_user_joined,is_pin";
		$sql = $this->db_open_predictor->select($default_selected_columns,FALSE)
		->from(PREDICTION_MASTER)
		->where('prediction_master_id',$prediction_master_id);

		$query  = $this->db_open_predictor->get();
		$result = $query->result_array();

		if(!empty($result))
		{
			foreach ($result as $key => $value)
			{
				$selected_option_id      = "";
				$prediction_master_id    = $value['prediction_master_id'];
				$result[$key]['options'] = $this->get_prediction_options($prediction_master_id);
				if(!empty($result[$key]['options']))
				{
					foreach ($result[$key]['options'] as $okey => $ovalue)
					{
					    if(!empty($ovalue['is_correct']))
					    {
					    	$selected_option_id = $ovalue['prediction_option_id'];
					    }		
					}
				}	

				$result[$key]['selected_option_id'] = $selected_option_id;
			}
		}	

		return $result;
	}

	function get_category_details($category_id)
	{
		return $this->db_open_predictor->select('name,image')
		->from(CATEGORY)
		->where('category_id',$category_id)
		->get()->row_array();	
	}

	function update_prediction_status($status)
    {
       $this->db_user->where('name','allow_open_predictor')
	   ->update(MODULE_SETTING,array('status'=> $status));
	   
	   $this->db_user->where('game_key','allow_open_predictor')
       ->update(SPORTS_HUB,array('status'=> $status));
       return $this->db_user->affected_rows();    
	}

	public function get_prediction_participants($prediction_master_id)
	{

		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}
		

         $offset	= $limit * $page;


		$pre_query ="(SELECT GROUP_CONCAT(UP.user_id) as user_ids,PO.prediction_option_id
FROM ".$this->db_open_predictor->dbprefix(PREDICTION_MASTER)." PM 
INNER JOIN ".$this->db_open_predictor->dbprefix(PREDICTION_OPTION)." PO ON PM.prediction_master_id=PO.prediction_master_id 
INNER JOIN ".$this->db_open_predictor->dbprefix(USER_PREDICTION)." UP ON UP.prediction_option_id=PO.prediction_option_id  
GROUP BY PO.prediction_option_id)";


		 $this->db_open_predictor->select("PM.prediction_master_id,UP.user_id,PO.`option`",FALSE)
		->from(PREDICTION_MASTER.' PM')
		->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
		->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
		->join($pre_query.' TR',"TR.prediction_option_id=UP.prediction_option_id")
		->where("PO.prediction_master_id",$prediction_master_id)
		->group_by('UP.user_id')
		->order_by("PO.prediction_option_id","ASC");

		$tempdb = clone $this->db_open_predictor; //to get rows for pagination
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		
		$sql = $this->db_open_predictor->limit($limit,$offset)->get();
		$prediction_data	= $sql->result_array();

		$next_offset = count($prediction_data) + $offset;

		return array('total' => $total,
		 'prediction_participants' => $prediction_data,
		 'next_offset' => $next_offset 	
		);
	}

	public function get_trending_prediction()
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}
		
		$current_date=format_date();
		$pre_sql="(SELECT prediction_master_id ,IF(deadline_date<'{$current_date}' AND total_user_joined=0, 0, 1) as valid_prediction FROM  ".$this->db_open_predictor->dbprefix(PREDICTION_MASTER).")";

		 $default_selected_columns = "PM.prediction_master_id,PM.`desc`,PM.category_id,C.name as category_name,DATE_FORMAT(PM.deadline_date, '".MYSQL_DATE_TIME_FORMAT."') as deadline_date,PM.status,PM.total_user_joined,PM.is_pin,COMMON.valid_prediction";
		
		 $sql = $this->db_open_predictor->select($default_selected_columns,FALSE)
						 ->from(PREDICTION_MASTER." PM")
						 ->join(CATEGORY." C","C.category_id=PM.category_id")
						 ->join($pre_sql." COMMON","COMMON.prediction_master_id=PM.prediction_master_id")
						 ->where('PM.status',0)
						 ->where("COMMON.valid_prediction",1);
						 
		if($post['tab_no'] ==2)
		{
			$this->db_open_predictor->where('PM.total_user_joined>',0);
		}
		$tempdb = clone $this->db_open_predictor;
		$query  = $this->db_open_predictor->get();
		$total  = $query->num_rows();

		$offset = 0;
        if(isset($limit) && isset($page))
        {
            $offset	= $limit * $page;
            $tempdb->limit($limit,$offset);
        }

		$this->config->load('fixed_open_predictor_config');
		$trending_types = $this->config->item('trending_prediction');
		
		$sql = $tempdb->order_by($trending_types[$post['tab_no']]['sort_key'],'DESC')
		->order_by('PM.added_date',"DESC")
		->get();
		$result = $sql->result_array();
		//echo $tempdb->last_query();die('df');
		//fetch options for predictions
		if(!empty($result))
		{
			foreach ($result as $key => $value)
			{
				$selected_option_id      = "";
				$prediction_master_id    = $value['prediction_master_id'];
				$result[$key]['options'] = $this->get_prediction_options($prediction_master_id);
				if(!empty($result[$key]['options']))
				{
					foreach ($result[$key]['options'] as $okey => $ovalue)
					{
					    if(!empty($ovalue['is_correct']))
					    {
					    	$selected_option_id = $ovalue['prediction_option_id'];
					    }		
					}
				}	

				$result[$key]['selected_option_id'] = $selected_option_id;
			}
		}	
 
		$next_offset = count($result) + $offset;

		return array('total' => $total,
		 'result' => $result,
		 'next_offset' => $next_offset 	
		);
	}

	public function get_bid_count_prediction($count=FALSE)
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}

		$this->config->load('fixed_open_predictor_config');
        $trending_types = $this->config->item('trending_prediction');
		
		$current_date=format_date();
		$pre_sql="(SELECT prediction_master_id ,IF(deadline_date<'{$current_date}' AND total_user_joined=0, 0, 1) as valid_prediction FROM  ".$this->db_open_predictor->dbprefix(PREDICTION_MASTER).")";

		 $default_selected_columns = "PM.prediction_master_id,PM.`desc`,PM.category_id,C.name as category_name,DATE_FORMAT(PM.deadline_date, '".MYSQL_DATE_TIME_FORMAT."') as deadline_date,PM.status,PM.total_user_joined,PM.is_pin,COMMON.valid_prediction";
		
		 $sql = $this->db_open_predictor->select($default_selected_columns,FALSE)
						 ->from(PREDICTION_MASTER." PM")
						 ->join(CATEGORY." C","C.category_id=PM.category_id")
						 ->join($pre_sql." COMMON","COMMON.prediction_master_id=PM.prediction_master_id")
						 ->where('PM.status',0)
						 ->where('PM.total_user_joined',$trending_types[$post['tab_no']]['bid_count'])
						 ->where("COMMON.valid_prediction",1);
						 

		$tempdb = clone $this->db_open_predictor;
		$query  = $this->db_open_predictor->get();
		$total  = $query->num_rows();
		if($count)
		{
			return $total;
		}

		$offset = 0;
        if(isset($limit) && isset($page))
        {
            $offset	= $limit * $page;
            $tempdb->limit($limit,$offset);
        }

		$sql = $tempdb->order_by('PM.added_date','DESC')
		->get();
		$result = $sql->result_array();

		//fetch options for predictions
		if(!empty($result))
		{
			foreach ($result as $key => $value)
			{
				$selected_option_id      = "";
				$prediction_master_id    = $value['prediction_master_id'];
				$result[$key]['options'] = $this->get_prediction_options($prediction_master_id);
				if(!empty($result[$key]['options']))
				{
					foreach ($result[$key]['options'] as $okey => $ovalue)
					{
					    if(!empty($ovalue['is_correct']))
					    {
					    	$selected_option_id = $ovalue['prediction_option_id'];
					    }		
					}
				}	

				$result[$key]['selected_option_id'] = $selected_option_id;
			}
		}	
 
		$next_offset = count($result) + $offset;

		return array('total' => $total,
		 'result' => $result,
		 'next_offset' => $next_offset 	
		);
	}

	/*
	*@
	*/
	function get_category_prediction($category_id)
	{
		return $this->db_open_predictor->select('PM.prediction_master_id')
		->from(CATEGORY." C")
		->join(PREDICTION_MASTER." PM","PM.category_id=C.category_id")
		->where('PM.category_id',$category_id)
		->get()->row_array();
	}

	function delete_category($category_id)
	{
		$this->db_open_predictor->where('category_id',$category_id);
		$this->db_open_predictor->delete(CATEGORY);
	}

	/**
	 * @method get_prediction_invested_coins_weekly
	 * @uses function to get data weekly
	 * @param Array
	 * **/
	public function get_prediction_attempts_weekly($post)
	{
	
		$this->db_open_predictor->select('COUNT( UP.user_prediction_id) as attempts,DATE_FORMAT(UP.added_date,"%u-%Y") as week_year,DATE_FORMAT(UP.added_date,"Week_%u_%y") as week_number,
		concat_ws(" ",DATE_FORMAT(UP.added_date,"Week %u"),
		DATE_FORMAT(DATE_ADD(UP.added_date, INTERVAL(1-DAYOFWEEK(UP.added_date)) DAY),"%Y-%m-%d") ,
		DATE_FORMAT(DATE_ADD(UP.added_date, INTERVAL(7-DAYOFWEEK(UP.added_date)) DAY),"%Y-%m-%d")) as created
		',FALSE)
        ->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2);

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_open_predictor->where("DATE_FORMAT(UP.added_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(UP.added_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_open_predictor->order_by('UP.added_date','ASC')
		->group_by("week_number")
		->order_by('week_number','ASC')
        ->get()
        ->result_array();

		//echo "<pre>";
		//var_dump($result);die('dff');
        //echo $this->db_user->last_query();die;
        return  array('result' => $result);  
	}

	/**
	 * @method get_prediction_invested_coins_monthly
	 * @uses function to get data weekly
	 * @param Array
	 * **/
	public function get_prediction_attempts_monthly($post)
	{
		$this->db_open_predictor->select('COUNT( UP.user_prediction_id) as attempts,DATE_FORMAT(UP.added_date,"%b-%Y") as month_year
		')
		->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2);

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_open_predictor->where("DATE_FORMAT(UP.added_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(UP.added_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_open_predictor->order_by('UP.added_date','ASC')
		->group_by("month_year")
		->order_by('month_year','ASC')
        ->get()
        ->result_array();

       // echo $this->db_user->last_query();die;
        return  array('result' => $result);  
	}

	public function get_prediction_attempts($post)
	{
		$count_result = $this->get_all_attempts_counts($post); 
        if(isset($count_result['total']))
        {
            $count = $count_result['total'];
        }
        
        $this->db_open_predictor->select('COUNT(UP.user_prediction_id) as attempts,DATE_FORMAT(UP.added_date,"%Y-%m-%d") as date_added')
        ->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2);


        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_open_predictor->where("DATE_FORMAT(UP.added_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(UP.added_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_open_predictor->order_by('UP.added_date','ASC')
        ->group_by("DATE_FORMAT(UP.added_date, '%Y-%m-%d')")
        ->get()
        ->result_array();

       // echo $this->db_user->last_query();die;
        return  array('result' => $result,'total' => $count);  
	}

	public function get_all_attempts_counts($post_data){
		
		$this->db_open_predictor->select("COUNT(UP.user_prediction_id) as total",FALSE)
		->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2);

        if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db_open_predictor->where("DATE_FORMAT(UP.added_date, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(UP.added_date, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}

        $query = $this->db_open_predictor->get();
        $result = $query->row_array();

        //echo $this->db->last_query(); die;
        return $result;
	}

	public function get_prediction_attempted_users($post)
	{
		$count_result = $this->get_all_user_attempts_counts($post); 
        if(isset($count_result['total']))
        {
            $count = $count_result['total'];
        }
        
        $this->db_open_predictor->select('count(DISTINCT UP.user_id ) as user_count ,DATE_FORMAT(UP.added_date,"%Y-%m-%d") as date_added')
		->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2);

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_open_predictor->where("DATE_FORMAT(UP.added_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(UP.added_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_open_predictor->order_by('UP.added_date','ASC')
        //->group_by("O.user_id")
        ->group_by("DATE_FORMAT(UP.added_date, '%Y-%m-%d')")
        ->get()
        ->result_array();

        //echo $this->db_user->last_query();die;
        return  array('result' => $result,'total' => $count);  
	}

	public function get_all_user_attempts_counts($post_data){
		
		$this->db_open_predictor->select("count(DISTINCT UP.user_id) as total",FALSE)
        ->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2);

        if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db_open_predictor->where("DATE_FORMAT(UP.added_date, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(UP.added_date, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}

        $query = $this->db_open_predictor->get();
        $result = $query->row_array();

        //echo $this->db->last_query(); die;
        return $result;
	}

	/**
	 * @method get_prediction_invested_users_weekly
	 * @uses function for weekly graph
	 * @param Array
	*/
	public function get_prediction_users_weekly($post)
	{
        $this->db_open_predictor->select('COUNT(DISTINCT UP.user_id) as user_count,DATE_FORMAT(UP.added_date,"%u-%Y") as week_year,DATE_FORMAT(UP.added_date,"Week_%u_%y") as week_number,
		concat_ws(" ",DATE_FORMAT(UP.added_date,"Week %u"),
		DATE_FORMAT(DATE_ADD(UP.added_date, INTERVAL(1-DAYOFWEEK(UP.added_date)) DAY),"%Y-%m-%d") ,
		DATE_FORMAT(DATE_ADD(UP.added_date, INTERVAL(7-DAYOFWEEK(UP.added_date)) DAY),"%Y-%m-%d")) as created
		',FALSE)
        ->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2);

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_open_predictor->where("DATE_FORMAT(UP.added_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(UP.added_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_open_predictor->order_by('UP.added_date','ASC')
		->group_by("week_number")
		->order_by('week_number','ASC')
        ->get()
        ->result_array();

		//echo "<pre>";
		//var_dump($result);die('dff');
        //echo $this->db_user->last_query();die;
        return  array('result' => $result);  
	}

	/**
	 * @method get_prediction_invested_users_monthly
	 * @uses function for weekly graph
	 * @param Array
	*/
	public function get_prediction_users_monthly($post)
	{
	
        $this->db_open_predictor->select('count(DISTINCT UP.user_id ) as user_count ,DATE_FORMAT(UP.added_date,"%b-%Y") as month_year')
		->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2);

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_open_predictor->where("DATE_FORMAT(UP.added_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(UP.added_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_open_predictor->order_by('UP.added_date','ASC')
        ->group_by("month_year")
        ->order_by("month_year",'ASC')
        ->get()
        ->result_array();

		return  array('result' => $result,
	
	);  
	}

	/**
	* @method get_top_category description
	* @uses This function used for get top predicted seasons
	* @param count
	*/	
	public function get_top_category()
	{
		$post = $this->input->post();
		$season_result = $this->db_open_predictor->select('C.name,C.image,PM.category_id ,COUNT(UP.user_id) as user_count')
		->from(PREDICTION_MASTER.' PM')
		->join(CATEGORY.' C','C.category_id=PM.category_id','INNER')
		->join(PREDICTION_OPTION.' PO','PM.prediction_master_id=PO.prediction_master_id','INNER')
		->join(USER_PREDICTION.' UP','UP.prediction_option_id=PO.prediction_option_id','INNER');

		if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_open_predictor->where("DATE_FORMAT(PM.added_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(PM.added_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

		$season_result = $this->db_open_predictor->group_by('PM.category_id')
		->order_by('user_count','DESC')->get()->result_array();

		return $season_result;
	}

	function get_most_correct_predictions_leaderboard($post)
	{
		$limit = 30;
        $offset = 0;

        $count = 0;
        if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        $page = $post['current_page']-1;
		$offset	= $limit * $page;

        $this->db_open_predictor->select("SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) as
		correct_answer,(RANK() OVER (ORDER BY SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) DESC,
		MAX(IF(PM.prediction_master_id and PO.is_correct=1, UP.added_date, NULL)) ASC,
		MAX(IF(PM.prediction_master_id, UP.added_date, NULL)) ASC
        )) user_rank,UP.user_id",FALSE)
        ->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2)
		->limit($limit,$offset);

		$query = $this->db_open_predictor
		->group_by('UP.user_id')
		->order_by('correct_answer','desc')->get();
        $result = $query->result_array();

       // echo $this->db_user->last_query(); die;
        return array('list' =>$result,'next_offset' =>$offset + count($result) );
	}

	function get_most_correct_counts()
    {

        $this->db_open_predictor->select("COUNT(DISTINCT UP.user_id) as total",FALSE)
        ->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2);

        // $this->db_user->select("COUNT(user_id) total",FALSE)
        // ->from(USER.' U');
        $query = $this->db_open_predictor->get();
        $result = $query->row_array();
        return $result;
	}
	
	function most_attempts_leaderboard($post)
	{
		$limit = 30;
        $offset = 0;

        $count = 0;
        if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        $page = $post['current_page']-1;
		$offset	= $limit * $page;

		$this->db_open_predictor->select("COUNT(UP.user_prediction_id) as attempt_count,UP.user_id,
		(RANK() OVER (ORDER BY COUNT(UP.user_prediction_id) DESC
        )) user_rank",FALSE)
        ->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2)
		->limit($limit,$offset);

		$query = $this->db_open_predictor->group_by('UP.user_id')
		->order_by('attempt_count','desc')->get();
        $result = $query->result_array();

        //echo $this->db->last_query(); die;
        return array('list' =>$result,'next_offset' =>$offset + count($result) );
	}

	function get_most_attempt_count()
    {
        $this->db_open_predictor->select("COUNT(UP.user_prediction_id) total",FALSE)
		->from(PREDICTION_MASTER.' PM')
        ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id=PM.prediction_master_id')
        ->join(USER_PREDICTION.' UP','UP.prediction_option_id=UP.prediction_option_id')
        ->where('PM.status',2);

        // $this->db_user->select("COUNT(user_id) total",FALSE)
        // ->from(USER.' U');
        $query = $this->db_open_predictor->get();
        $result = $query->row_array();
        return $result;
	}
	
	function update_prizes($update_data,$condition)
	{
		$this->db_open_predictor->where($condition);
		$this->db_open_predictor->update(PREDICTION_PRIZE,$update_data);
		return $this->db_open_predictor->affected_rows();

	}

	function get_prediction_prizes()
	{
		return $this->db_open_predictor->select("*")
		->from(PREDICTION_PRIZE)->get()->result_array();		
	}

	function get_day_leaderboard_count($day_number='',$day_date)
    {
		$this->db_open_predictor->select("COUNT(leaderboard_day_id) as total",FALSE)
	   ->from(LEADERBOARD_DAY)
	   ->where('day_number',$day_number)
	   ->where('day_date',$day_date);
	   $sql = $this->db_open_predictor->get();
	  
		$result = $sql->row_array();
		
		$total=0;
		if(!empty($result['total']))
		{
			$total=$result['total'];
		}
        return $total;
	}

	public function get_day_leaderboard($day_number='',$day_date)
	{
		$limit = 30;
        $offset = 0;
		$post = $this->input->post();
		$count = 0;
        if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        $page = $post['current_page']-1;
		$offset	= $limit * $page;
		$this->db_open_predictor->select("leaderboard_day_id,
		prize_distribution_history_id,
		user_id,
		rank_value,
		is_winner,
		prize_data,
		correct_answer,
		attempts,
		day_number,
		day_date",FALSE)
	   ->from(LEADERBOARD_DAY)
	   ->where('day_number',$day_number)
	   ->where('day_date',$day_date);
	   
	   $this->db_open_predictor->order_by("rank_value","ASC");

	   $this->db_open_predictor->limit($limit,$offset);
		
		$sql = $this->db_open_predictor->get();
	    $prediction_data	= $sql->result_array();
	   
	   return  $prediction_data;
	  
	}

	public function get_week_leaderboard($week_number='',$week_date)
	{
		$limit = 30;
        $offset = 0;
		$post = $this->input->post();
		$count = 0;
        if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        $page = $post['current_page']-1;
		$offset	= $limit * $page;
		$this->db_open_predictor->select("leaderboard_week_id,
		prize_distribution_history_id,
		user_id,
		rank_value,
		is_winner,
		prize_data,
		correct_answer,
		attempts,
		week_number,
		week_start_date",FALSE)
	   ->from(LEADERBOARD_WEEK)
	   ->where('week_number',$week_number)
	   ->where('week_start_date',$week_date);
	   
	   $this->db_open_predictor->order_by("rank_value","ASC");

	   $this->db_open_predictor->limit($limit,$offset);
		
		$sql = $this->db_open_predictor->get();
	   $prediction_data	= $sql->result_array();
	   
	   return $prediction_data;
		
	}

	public function get_week_leaderboard_count($week_number='',$week_date)
	{
		$this->db_open_predictor->select("COUNT(leaderboard_week_id) as total,
		",FALSE)
	   ->from(LEADERBOARD_WEEK)
	   ->where('week_number',$week_number)
	   ->where('week_start_date',$week_date);
	   
		$sql = $this->db_open_predictor->get();
	   $result	= $sql->row_array();
	   
	   $total=0;
	   if(!empty($result['total']))
	   {
		   $total=$result['total'];
	   }
	   return $total;
		
	}

	public function get_month_leaderboard_count($month_number='',$month_date)
	{
		$this->db_open_predictor->select("COUNT(leaderboard_month_id) as total",FALSE)
	   ->from(LEADERBOARD_MONTH)
	   ->where('month_number',$month_number)
	   ->where('month_start_date',$month_date);
		$result = $this->db_open_predictor->get()->row_array();
		$total=0;
		if(!empty($result['total']))
		{
			$total=$result['total'];
		}
        return $total;
	}
	public function get_month_leaderboard($month_number='',$month_date)
	{
		$limit = 30;
        $offset = 0;
		$post = $this->input->post();
		$count = 0;
        if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        $page = $post['current_page']-1;
		$offset	= $limit * $page;
		$this->db_open_predictor->select("leaderboard_month_id,
		prize_distribution_history_id,
		user_id,
		rank_value,
		is_winner,
		prize_data,
		correct_answer,
		attempts,
		month_number,
		month_start_date",FALSE)
	   ->from(LEADERBOARD_MONTH)
	   ->where('month_number',$month_number)
	   ->where('month_start_date',$month_date);
	  
	   $this->db_open_predictor->order_by("rank_value","ASC");
	   $this->db_open_predictor->limit($limit,$offset);

		$sql = $this->db_open_predictor->get();
	   $prediction_data	= $sql->result_array();
	  
	   return $prediction_data;
	}

}