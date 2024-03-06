<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Spinthewheel_model extends MY_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database('user_db');
        //Do your magic here
    }

    /**
    * [get_wheel_slices_list description]
    * @Summary : Use to get list of all slices
    * @return array
    */
    public function get_wheel_slices_list()
    {    
        $sort_field = 'slice_name';
        $sort_order = 'DESC';
        $limit      = 50;
        $page       = 0;
        $post_data = $this->input->post();
        if(!empty($post_data['items_perpage']))
        {
            $limit = $post_data['items_perpage'];
        }

        if(!empty($post_data['current_page']))
        {
            $page = $post_data['current_page']-1;
        }

        if(!empty($post_data['sort_field']) && in_array($post_data['sort_field'],array('slice_name')))
        {
            $sort_field = $post_data['sort_field'];
        }

        if(!empty($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
        {
            $sort_order = $post_data['sort_order'];
        }

        $user_keyword   = !empty($post_data['keyword']) ? $post_data['keyword'] : "";

        $offset = $limit * $page;
        $sql = $this->db->select("STW.spinthewheel_id ,STW.amount,STW.slice_name,STW.win,STW.probability,STW.result_text,STW.created_date,STW.type,STW.cash_type,STW.status,STW.created_date",FALSE)
                //->select("IFNULL((select count(OD.source_id) from `vi_order` AS `OD` where `OD`.`source_id`=`STW`.`spinthewheel_id` AND  OD.source = 282 group by OD.source_id),0) as reddem_users",FALSE)
                                ->from(SPIN_THE_WHEEL.' AS STW')
                                ->order_by('STW.'.$sort_field, $sort_order);

        if($user_keyword != '')
        {
            $this->db->like('STW.slice_name', $user_keyword);
        }
        
    
        if(isset($post_data['status']) && $post_data['status']==0)
        {
            $this->db->where("STW.status",$post_data['status']);
        } else if(isset($post_data['status']) && $post_data['status']==1){
            $this->db->where("STW.status",$post_data['status']);
        }

    
        $tempdb = clone $this->db; //to get rows for pagination
        $temp_q = $tempdb->get();
        $total = $temp_q->num_rows(); 

        $sql = $this->db->limit($limit,$offset)
                        ->get();
        $result = $sql->result_array();
        $result=($result)?$result:array();
        return array('result'=>$result,'total'=>$total);
    }

    /**
     * [update_slice description]
     * @Summary This function used to update slice
     * @param      [varchar]  [spinthewheel_id]
     * @return     [boolean]
     */
    public function update_slice($spinthewheel_id,$data_arr)
    {
        $this->db->where("spinthewheel_id",$spinthewheel_id)
                ->update(SPIN_THE_WHEEL,$data_arr);
        return true;
    }

    /**
     * [add_slice description]
     * @MethodName 
     * @Summary This function used to add new slice
     * @param  [array] [data_array]
     * @return array
    */
    public function add_slice($post)
    {
        $post["created_date"] = format_date();
        $this->db->insert(SPIN_THE_WHEEL, $post);
        return $this->db->insert_id();
    }

    function get_entity_counts($post)
    {

        $this->db->select("COUNT(DISTINCT O.user_id) as user_count,SUM(O.real_amount) as real_total,
        SUM(O.bonus_amount) as bonus_total,SUM(points) as coins_total
        ",FALSE )
        ->from(ORDER.' O')
        ->join(USER.' U','O.user_id=U.user_id')
        ->where('source',$this->spinthewheel_source);
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
        $filter_by = 'coins';
        if(empty($post['filter_by']))
        {
            $filter_by = $post['filter_by'];
        }
        $this->db->select("SUM(O.real_amount) as cash,
        SUM(O.bonus_amount) as bonus,SUM(points) as coins,U.user_name,IFNULL(CONCAT_WS(U.first_name,' ',U.last_name),U.user_name) as full_name,U.user_id
        ",FALSE )
        ->from(ORDER.' O')
        ->join(USER.' U','O.user_id=U.user_id')
        ->where('source',$this->spinthewheel_source);
        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'].' 00:00:00';
            $to_date = $post['to_date'].' 23:59:59';
			$this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ");
		}

        $result = $this->db->group_by('user_id')
        ->order_by($filter_by,"DESC")->limit(5)
        ->get()->result_array();
        return $result;
    }

    function get_leaderboard_by_category($post)
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
        $filter_by = 'coins';
        if(!empty($post['filter_by']))
        {
            $filter_by = $post['filter_by'];
        }

        $rank_by ="O.points";
        switch($filter_by)
        {
            case 'coins':
                $rank_by ="O.points";
            break;
            case 'bonus':
                $rank_by ="O.bonus_amount";
            break;
            default:
            {
                $rank_by ="O.real_amount";
            }
        }

        $date_str = "";

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'];
            $to_date = $post['to_date'];
            $date_str="AND DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."'";
        }

        $pre_sql = "(SELECT SUM(O.real_amount) as cash,
        SUM(O.bonus_amount) as bonus,SUM(O.points) as coins,U.user_name,IFNULL(CONCAT_WS(U.first_name,' ',U.last_name),U.user_name) as full_name,U.user_id,O.date_added,COUNT(O.user_id) as spins,U.first_name,U.last_name,U.phone_no,U.email,U.pan_no,
        (RANK() OVER (ORDER BY SUM($rank_by) DESC)) as rank_value,U.user_unique_id
        FROM ".$this->db->dbprefix(ORDER)." O 
        INNER JOIN ".$this->db->dbprefix(USER)." U ON U.user_id=O.user_id 
        WHERE O.source=$this->spinthewheel_source
        $date_str
        GROUP BY O.user_id
        ORDER BY rank_value ASC) 
        ";

        $sql = "SELECT RR.cash,
        RR.bonus,RR.coins,RR.user_name,IFNULL(CONCAT_WS(RR.first_name,' ',RR.last_name),RR.user_name) as full_name,RR.user_id,RR.spins,RR.rank_value,RR.user_unique_id
        FROM $pre_sql RR  ";

        // $this->db->select("RR.cash,
        // RR.bonus,RR.coins,RR.user_name,IFNULL(CONCAT_WS(RR.first_name,' ',RR.last_name),RR.user_name) as full_name,RR.user_id
        // ",FALSE )
        // ->from($pre_sql.' AS RR');
       $where =array();
        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'];
            $to_date = $post['to_date'];
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

  

}
/* End of file Spinthewheel_model.php */
/* Location: ./application/models/Spinthewheel_model.php */
