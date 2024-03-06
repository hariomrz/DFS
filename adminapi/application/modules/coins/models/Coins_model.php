<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Coins_model extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->db_fantasy		= $this->load->database('db_fantasy', TRUE);
		$this->db_user		= $this->load->database('db_user', TRUE);
		
	}
    
   

     /**
     * @method get_coin_distributed_list
     * @since Dec 2019
     * @uses function get reward history
     * @param Array $_POST status
     * @return json
     * ***/
    function get_coin_distributed_list()
    {
        $post = $this->input->post();
        $limit = 30;
        $offset = 0;     

        $count = 0;
        $total_coins_distributed =0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        if($post['current_page'])
		{
			$page = $post['current_page']-1;
			// if($post['current_page']==1) {
                $count_result = $this->get_all_coins_distributed_counts($post); 
                if(isset($count_result['total']))
                {
                    $count = $count_result['total'];
                    $total_coins_distributed = $count_result['total_coins_distributed'];
                }
			// }
        }
        
         
        $page = $post['current_page']-1;
		$offset	= $limit * $page;

        $this->db_user->select('O.points, O.status,O.source,U.user_name,O.date_added,O.custom_data')
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.status>',0);
        
        //    $this->db_user->limit($limit,$offset);
            if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
            {
                $this->db_user->where("DATE_FORMAT(U.added_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(U.added_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
            }


        $tempdb = clone $this->db_user;
		$query  = $this->db_user->get();
		$total  = $query->num_rows();

        if(isset($limit) && isset($page))
        {
            $offset	= $limit * $page;
            
            $tempdb->limit($limit,$offset);
        }
        $sql = $tempdb->get();
		$result = $sql->result_array();
		return array('result'=>$result,'total'=>$total,'total_coins_distributed'=>$total_coins_distributed);
        


        // $result = $this->db_user->get()
        // ->result_array();
        // return  array('result' => $result,'total' => $count,'total_coins_distributed'=>$total_coins_distributed);    
    }

    public function get_all_coins_distributed_counts($post_data){
		
		$this->db_user->select("count(O.order_id) as total,SUM(IF(O.type=0,O.points,0)) as total_coins_distributed",FALSE)
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.source>',0)
        ->where('O.status>',0);

        if(!empty($post_data['user_unique_id']))
        {
            $this->db_user->where('U.user_unique_id',$post_data['user_unique_id']);
        }

        if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}

        $query = $this->db_user->get();
        $result = $query->row_array();

        //echo $this->db->last_query(); die;
        return $result;
	}

    function get_coin_configuration_details()
    {
        $result = $this->db_user->select('MS.name as module_name,MS.status as module_status,SM.*')
        ->from(MODULE_SETTING.' MS')
        ->join(SUBMODULE_SETTING.' SM','MS.module_setting_id=SM.module_setting_id')
        ->where('MS.module_setting_id',1)
        ->get()
        ->result_array();

        return $result;    
    }

    function update_coin_status($status)
    {
       $this->db_user->where('module_setting_id',1)
       ->update(MODULE_SETTING,array('status'=> $status));
       return $this->db_user->affected_rows();    
    }

    function update_coins_setting($submodule_data)
    {
        $this->db_user->update_batch(SUBMODULE_SETTING,$submodule_data, 'submodule_setting_id'); 
        return true;
    }

    function get_top_earner($post)
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
        $pre_query =" (SELECT USER.user_id,SUM(O.points) as total_points from ".$this->db_user->dbprefix(USER)." USER INNER JOIN ".$this->db_user->dbprefix(ORDER)." O ON O.user_id=USER.user_id where O.status=1 AND O.type=0 GROUP BY USER.user_id )  ";

        $this->db_user->select("OO.total_points as coin_earned ,U.user_name,U.user_unique_id,RANK() OVER (
            ORDER BY OO.total_points DESC
        ) user_rank",FALSE)
        ->from(USER.' U')
        ->join($pre_query.' OO','U.user_id=OO.user_id')->limit($limit,$offset);

        $query = $this->db_user->order_by('coin_earned','desc')->get();
        $result = $query->result_array();

        //echo $this->db->last_query(); die;
        return array('list' =>$result,'next_offset' =>$offset + count($result) );
    }

    function get_top_earner_count()
    {

        $pre_query =" (SELECT USER.user_id,SUM(O.points) as total_points from ".$this->db_user->dbprefix(USER)." USER INNER JOIN ".$this->db_user->dbprefix(ORDER)." O ON O.user_id=USER.user_id where O.status=1 AND O.type=0 GROUP BY USER.user_id )  ";

        $this->db_user->select("COUNT(U.user_id) total",FALSE)
        ->from(USER.' U')
        ->join($pre_query.' OO','U.user_id=OO.user_id');

        // $this->db_user->select("COUNT(user_id) total",FALSE)
        // ->from(USER.' U');
        $query = $this->db_user->get();
        $result = $query->row_array();
        return $result;
    }

    function export_top_earner()
    {
        $pre_query =" (SELECT USER.user_id,SUM(O.points) as total_points from ".$this->db_user->dbprefix(USER)." USER INNER JOIN ".$this->db_user->dbprefix(ORDER)." O ON O.user_id=USER.user_id where O.status=1  AND O.type=0 GROUP BY USER.user_id )  ";

        $this->db_user->select("OO.total_points as coin_earned ,U.user_name,U.user_unique_id,RANK() OVER (
            ORDER BY OO.total_points DESC
        ) user_rank",FALSE)
        ->from(USER.' U')
        ->join($pre_query.' OO','U.user_id=OO.user_id');

        $query = $this->db_user->order_by('coin_earned','desc')->get();
        $result = $query->result_array();
        return $result;
    }

    function get_top_redeemer($post)
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
        $pre_query =" (SELECT USER.user_id,SUM(O.points) as total_points from ".$this->db_user->dbprefix(USER)." USER INNER JOIN ".$this->db_user->dbprefix(ORDER)." O ON O.user_id=USER.user_id where O.status=1 AND O.type=1 AND O.source=147  GROUP BY USER.user_id )  ";

        $this->db_user->select("OO.total_points as coin_earned ,U.user_name,U.user_unique_id,RANK() OVER (
            ORDER BY OO.total_points DESC
        ) user_rank",FALSE)
        ->from(USER.' U')
        ->join($pre_query.' OO','U.user_id=OO.user_id')->limit($limit,$offset);

        $query = $this->db_user->order_by('coin_earned','desc')->get();
        $result = $query->result_array();

        // echo  $this->db_user->last_query();
        // die('fdf');
        return array('list' =>$result,'next_offset' =>$offset + count($result) );
    }

    function get_top_redeemer_count()
    {
        $pre_query =" (SELECT USER.user_id,SUM(O.points) as total_points from ".$this->db_user->dbprefix(USER)." USER INNER JOIN ".$this->db_user->dbprefix(ORDER)." O ON O.user_id=USER.user_id where O.status=1 AND O.type=1 AND O.source=147  GROUP BY USER.user_id )  ";

        $this->db_user->select("COUNT(U.user_id) as total",FALSE)
        ->from(USER.' U')
        ->join($pre_query.' OO','U.user_id=OO.user_id');

        $query = $this->db_user->get();
        $result = $query->row_array();
        return $result;
    }

    function export_top_redeemer()
    {
      
        $pre_query =" (SELECT USER.user_id,SUM(O.points) as total_points from ".$this->db_user->dbprefix(USER)." USER INNER JOIN ".$this->db_user->dbprefix(ORDER)." O ON O.user_id=USER.user_id where O.status=1 AND O.type=1 AND O.source=147  GROUP BY USER.user_id )  ";

        $this->db_user->select("OO.total_points as coin_earned ,U.user_name,U.user_unique_id,RANK() OVER (
            ORDER BY OO.total_points DESC
        ) user_rank",FALSE)
        ->from(USER.' U')
        ->join($pre_query.' OO','U.user_id=OO.user_id');

        $query = $this->db_user->order_by('coin_earned','desc')->get();
        $result = $query->result_array();

        // echo  $this->db_user->last_query();
        // die('fdf');
        return $result;
    }

     /**
     * @method get_coin_distributed_graph
     * @since Dec 2019
     * @uses function get reward history
     * @param Array $_POST status
     * @return json
     * ***/
    function get_coin_distributed_graph($post)
    {
        $count_result = $this->get_all_coins_distributed_counts($post); 
        if(isset($count_result['total']))
        {
            $count = $count_result['total'];
            $total_coins_distributed = $count_result['total_coins_distributed'];
        }
        
        $this->db_user->select('O.points, O.status,O.source,U.user_name,DATE_FORMAT(O.date_added,"%Y-%m-%d") as date_added',FALSE)
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.type',0)
        ->where('O.source>',0)
        ->where('O.status',1);

        if(!empty($post['user_unique_id']))
        {
            $this->db_user->where('U.user_unique_id',$post['user_unique_id']);
        }

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_user->order_by('date_added','ASC')
        //->group_by("O.source")
        //->group_by("DATE_FORMAT(O.date_added, '%Y-%m-%d')")
        ->get()
        ->result_array();

        //echo $this->db_user->last_query();die;
        return  array('result' => $result,'total' => $count,'total_coins_distributed'=>$total_coins_distributed);    
    }

   public function get_expired_coins($post)
   {
        $this->db_user->select('IFNULL(SUM(UB.total_coins-UB.used_coins),0) as expired_coins')
        ->from(USER_BONUS_CASH." UB")
        ->where('UB.is_coin_exp', 2);
        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
            {
                $this->db_user->where("DATE_FORMAT(UB.bonus_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(UB.bonus_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
            }
        $query = $this->db_user->get()->row_array();
        // echo $this->db_user->last_query();exit;
        return $query;
    }
    function get_entity_counts($post)
    {

        $this->db->select("COUNT(DISTINCT O.user_id) as user_count,SUM(points) as coins_total
        ",FALSE )
        ->from(ORDER.' O')
        ->join(USER.' U','O.user_id=U.user_id')
        ->where('source',$this->dailycheckin_source);
        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'].' 00:00:00';
            $to_date = $post['to_date'].' 23:59:59';
			$this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ");
		}

        $result = $this->db->get()->row_array();
        return $result;
    }

    function get_top_gainers($post)
    {
       
        $this->db->select("
        SUM(points) as coins,U.user_name,IFNULL(TRIM(CONCAT_WS(U.first_name,' ',U.last_name)),U.user_name) as full_name,U.user_id
        ",FALSE )
        ->from(ORDER.' O')
        ->join(USER.' U','O.user_id=U.user_id')
        ->where('source',$this->dailycheckin_source);
        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'].' 00:00:00';
            $to_date = $post['to_date'].' 23:59:59';
			$this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ");
		}

        $result = $this->db->group_by('user_id')
        ->order_by("coins","DESC")->limit(5)
        ->get()->result_array();
        return $result;
    }

    function get_leaderboard_dailycheckin($post)
    {

        $limit		= 50;
		$page		= 0;

        if(isset($post['items_perpage']) && $post['items_perpage'])
		{
			$limit = $post['items_perpage'];
		}

		if(isset($post['current_page']) && $post['current_page'])
		{
			$page = $post['current_page']-1;
		}

        $offset	= $limit * $page;
        

      

        $date_str = "";

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'].' 00:00:00';
            $to_date = $post['to_date'].' 23:59:59';
            $date_str="AND DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."'";
        }

        $pre_sql = "(SELECT SUM(O.points) as coins,U.user_name,IFNULL(CONCAT_WS(U.first_name,' ',U.last_name),U.user_name) as full_name,U.user_id,MAX(O.date_added) as date_added,U.first_name,U.last_name,U.phone_no,U.email,U.pan_no,
        (RANK() OVER (ORDER BY SUM(O.points) DESC)) as rank_value,U.user_unique_id
        FROM ".$this->db->dbprefix(ORDER)." O 
        INNER JOIN ".$this->db->dbprefix(USER)." U ON U.user_id=O.user_id 
        WHERE O.source=$this->dailycheckin_source
        $date_str
        GROUP BY O.user_id
        ORDER BY rank_value ASC) 
        ";

        $sql = "SELECT RR.coins,RR.user_name,IFNULL(CONCAT_WS(RR.first_name,' ',RR.last_name),RR.user_name) as full_name,RR.user_id,RR.rank_value,RR.date_added,RR.user_unique_id
        FROM $pre_sql RR  ";

        // $this->db->select("RR.cash,
        // RR.bonus,RR.coins,RR.user_name,IFNULL(CONCAT_WS(RR.first_name,' ',RR.last_name),RR.user_name) as full_name,RR.user_id
        // ",FALSE )
        // ->from($pre_sql.' AS RR');
       $where =array();
        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'].' 00:00:00';
            $to_date = $post['to_date'].' 23:59:59';
			//$this->db->where("DATE_FORMAT(RR.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(RR.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ");

            $where[] = "DATE_FORMAT(RR.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(RR.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ";
		}

        if(!empty($where))
        {
            $sql.=" WHERE ".implode(' AND  ',$where);
        }

        if(isset($post['keyword']) && $post['keyword'] != "")
		{
			//$this->db->like('LOWER( CONCAT(IFNULL(RR.email,""),IFNULL(RR.first_name,""),IFNULL(RR.last_name,""),IFNULL(RR.user_name,""),IFNULL(RR.phone_no,""),CONCAT_WS(" ",RR.first_name,RR.last_name),IFNULL(RR.pan_no,"")))', strtolower($post['keyword']) );
            $keyword =strtolower($post['keyword']);
            $sql.=" AND LOWER( CONCAT(IFNULL(RR.email,''),IFNULL(RR.first_name,''),IFNULL(RR.last_name,''),IFNULL(RR.user_name,''),IFNULL(RR.phone_no,''),CONCAT_WS(' ',RR.first_name,RR.last_name),IFNULL(RR.pan_no,''))) LIKE '%".$keyword."%' ";
		}
        
        $tempsql = $sql; //to get rows for pagination
		$tempdb = $this->db->query($tempsql);
		$temp_q = $tempdb->result_array();
        $num_rows = $tempdb->num_rows(); 
        $total = isset($num_rows) ? $num_rows : 0; 

		$result = array();
        //$sql.=" GROUP BY RR.user_id ORDER BY RR.$filter_by DESC  ";
        $sql.=" LIMIT $offset,$limit";
        $result = $this->db->query($sql)->result_array();
		//$sql = $this->db->limit($limit,$offset)->get();
			//$result	= $sql->result_array();
			$result=($result)?$result:array();

          // echo $sql;die();


           return array('result'=>$result,'total'=>$total);
		


    }


     /**
     * @method get_export_coin_distributed_list
     * @since Dec 2019
     * @uses function get reward history
     * @param Array $_POST status
     * @return json
     * ***/
    function get_export_coin_distributed_list($post)
    {    

        $this->db_user->select('O.points, O.status,O.source,U.user_name,O.date_added,O.custom_data')
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.status>',0);       
           
        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
        {
            $this->db_user->where("DATE_FORMAT(U.added_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(U.added_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
        }
        $result = $this->db_user->get()
        ->result_array();
        return  array('result' => $result);    
    }  
	
}
