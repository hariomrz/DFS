<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Affiliate_users_model extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		// $this->db_fantasy		= $this->load->database('db_fantasy', TRUE);
		// $this->db_user		= $this->load->database('db_user', TRUE);
		
    }   
    
    /* method will update status of user as well commission and other values , also affiliate date in case when it is going to become an affiliate.*/
    public function update_affiliate(){
        $post_data = $this->input->post(); 
        $where = ['user_id'=>$post_data['user_id'],"is_systemuser"=>0];
        if($post_data['is_affiliate']!=2){
            if($post_data['is_affiliate']==1){
            $check_req_date = $this->db->select('aff_request_date')->from(USER)->where($where)->where('aff_request_date IS NOT NULL')->get()->row_array();
            if(!$check_req_date) {$post_data['aff_request_date'] = format_date('today');}
            }
            $post_data['affiliate_date'] = format_date('today');
        }else{
            $post_data['aff_request_date'] = format_date('today');
        }
        if(!isset($post_data['commission_type'])) {$post_data['commission_type'] = 4;}

        $post_data['modified_date'] =format_date('today');
         
        $result = $this->db->update(USER,$post_data,$where);
         return $result;
    }
    /**
     * Method is used to get pending affiliate request list
     * @param :  n/a
     * @return : array()
     */
   public function get_pending_affiliate_request(){
    $is_pending_affiliate = $this->get_single_row ('COUNT(user_id) AS count', USER, $where = ["is_affiliate >"=>0,"is_systemuser"=>0,"status"=>1]);

    $sort_field	= 'modified_date';
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

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name','modified_date')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

        $offset	= $limit * $page;
        if($post_data['csv']==1)
        {
            $select =  "is_affiliate,IFNULL(aff_request_date,added_date) as 'date_time(UTC)',IFNULL(user_name,'-') AS user_name,IFNULL(phone_no,'-') AS mobile_number,IFNULL(email,'-') AS email,
            (CASE 
            WHEN is_affiliate=1 THEN 'Approved'
            WHEN is_affiliate=2 THEN 'Pending'
            WHEN is_affiliate=3 THEN 'Blocked'
            WHEN is_affiliate=4 THEN 'Rejected'
            END) AS status,
            IF(is_affiliate=1,signup_commission,'-') AS sign_up_bonus,IF(is_affiliate=1,deposit_commission,'-') AS 'comission_%',referral_code,affiliate_narration AS reason,IFNULL(affiliate_date,'-') as 'status_updated_time(UTC)'";
        }else{
            $select = 'user_unique_id,user_id,user_name,IFNULL(aff_request_date,added_date) AS modified_date,phone_no,city,is_affiliate,expected_affiliated_user,user_affiliated_website';
        }
        
       $result = $this->db->select($select)
       ->from(USER)
       ->where('is_affiliate!=',0)
       ->where('is_systemuser',0)
       ->where('status',1)
       ->order_by($sort_field, $sort_order);

       if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(aff_request_date, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(aff_request_date, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
        }
        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
       {
            if(isset($post_data['action']) && $post_data['action'] ==2)
            {
                $this->db->where("user_unique_id = '{$post_data['keyword']}'"); 
            }else{
                //$this->db->where('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,"")))', strtolower($post_data['keyword']) );
                $this->db->like('CONCAT(user_unique_id,IFNULL(email,""),IFNULL(phone_no,""),IFNULL(user_name,""),CONCAT_WS(" ",first_name,last_name))', strtolower($post_data['keyword']));
            }
       }
       if(isset($post_data['is_affiliate']) && $post_data['is_affiliate']!='')
       {
        $this->db->where("is_affiliate = '{$post_data['is_affiliate']}'");
       }
        
        if($post_data['csv']==1)
        {
            $result	= $this->db->get()->result_array();
            foreach($result as $key=>$res)
            {
                if($result[$key]['is_affiliate']==1){
                    $result[$key]['affiliate_url']= WEBSITE_URL.'signup?affcd='.$res['referral_code'];
                }else{
                    $result[$key]['affiliate_url']= '';
                }
                    unset($result[$key]['referral_code'],$result[$key]['is_affiliate']);
            }
            return $result;
        }else{
            $tempdb = clone $this->db; //to get rows for pagination
            $temp_q = $tempdb->get();
            $total = $temp_q->num_rows();
            $result = $this->db->limit($limit,$offset)->get();
            $result	= $result->result_array();
            return array('result'=>$result,'total'=>$total,"is_pending_affiliate"=>($is_pending_affiliate['count'])? 1:0);
        }

   }

 

   public function get_users(){
       $post_data = $this->input->post();
       $result = $this->db->select('U.user_unique_id,U.user_id,CONCAT(IFNULL(first_name,""),IFNULL(last_name,"")) AS full_name,user_name,phone_no,referral_code,email,address,city,MS.name,U.signup_commission,U.deposit_commission,U.is_affiliate,U.affiliate_narration,U.commission_type,U.site_rake_commission,U.site_rake_status,U.user_affiliated_website,U.expected_affiliated_user')
       ->from(USER.' as U')
    //    ->where_in('U.is_affiliate',[0,2])
       ->where('U.status',1)
       ->where('U.is_systemuser',0)
       ->join(MASTER_STATE.' AS MS','MS.master_state_id = U.master_state_id','left');
       
       if(isset($post_data['keyword']) && $post_data['keyword'] != "")
       {
            if(isset($post_data['action']) && $post_data['action'] ==2)
            {
                $this->db->where("U.user_unique_id = '{$post_data['keyword']}'");
            }else{
                $this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,"")))', strtolower($post_data['keyword']) );
            }
       }
       $this->db->limit(1);
       $result = $this->db->get()->result_array();
    //    echo $this->db->last_query();exit;
       return $result;
   }
    /**
     * Method is used to get transaction records of a single user or all users  
     * @param :  user_id
     * @return : array()
     */
   public function get_affiliate_records(){
    $sort_field	= 'modified_date';
    $sort_order	= 'DESC';
    $limit		= 50;
    $page		= 0;
    $affiliate_detial=array();
    $post_data = $this->input->post();
    if(isset($post_data['user_id']) && $post_data['user_id']!=''){
        $affiliate_detial = $this->db->select('U.user_unique_id,MC.country_name AS country,CONCAT(IFNULL(first_name,""),IFNULL(last_name,"")) AS full_name,user_name,phone_no,referral_code,email,address,city,signup_commission,deposit_commission,is_affiliate,commission_type')
        ->from(USER.' U')
        ->join(MASTER_COUNTRY.' MC','MC.master_country_id=U.master_country_id','left')
        ->where("user_id",$post_data['user_id'])
        ->get()->row();
        // $affiliate_detial = $this->get_all_data('CONCAT(IFNULL(first_name,""),IFNULL(last_name,"")) AS full_name,user_name,phone_no,referral_code,email,address,city,signup_commission,deposit_commission,is_affiliate',USER,$where = array());
        
    }


    if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name','modified_date')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

        $offset	= $limit * $page;
        //-----------------------------------
        $friend_id = "JSON_UNQUOTE(JSON_EXTRACT(O.custom_data, '$.user_id'))";
        $friend_name = "JSON_UNQUOTE(JSON_EXTRACT(O.custom_data, '$.user_name'))";
        $friend_amount = "JSON_UNQUOTE(JSON_EXTRACT(O.custom_data, '$.amount'))";
        $friend_order_id = "JSON_UNQUOTE(JSON_EXTRACT(O.custom_data, '$.order_id'))";
        $result = $this->db->select("O.date_added,O.source,O.user_id,IFNULL($friend_id,'') as friend_id,IFNULL($friend_name,'') as friend_name,IFNULL($friend_amount,0) as friend_amount,IFNULL($friend_order_id,'') as friend_order_id,if(source =320,(O.winning_amount+O.real_amount),0) as signup_commission, if(source=321,(O.winning_amount+O.real_amount),0) as deposit_comission")
        ->from(ORDER.' AS O')
        ->where("type",0)
        ->group_start()
        ->where("O.real_amount >",0)
        ->or_where("O.winning_amount >",0)
        ->group_end();
        if(isset($post_data['source']) && $post_data['source']!=''){
            $this->db->where('source',$post_data['source']);
        }
        else{
            $this->db->where_in('source',[320,321]);
        }
        $this->db->order_by($sort_field, $sort_order);
        
        //-----------------------------------
        if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
            $this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
        }
        if(isset($post_data['user_id']) && $post_data['user_id']!=''){
            $this->db->where('O.user_id',$post_data['user_id']);
        }
        // $this->db->group_by('O.added_date');
        
        $tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        if(!isset($post_data['csv']) || $post_data['csv']==FALSE){
        $result = $this->db->limit($limit,$offset);
        }
        $result	= $result->get()->result_array();
        // echo $this->db->last_query();exit;
        foreach($result as $key=>$res){
            $frnd_data = $this->get_single_row('user_name,user_unique_id',USER, array("user_id"=>$res['friend_id']));
            $result[$key]['user_unique_id'] = $frnd_data['user_unique_id'];
            if(!empty($res['friend_id']) && empty($res['friend_name'])){
                $result[$key]['friend_name'] = $frnd_data['user_name'];
            }
            if($res['source']==320){
                $result[$key]['description']=$this->admin_lang["signup_desc"];
            }elseif($res['source']==321){
                $result[$key]['description']=$this->admin_lang["deposit_desc"];
            }
        }
        //echo $this->db->last_query();exit;
        return array('result'=>$result,'total'=>$total,'affiliate_detail'=>$affiliate_detial);

   }

   /**
      * Method si used to get values to represent a graph
      * @param : user_id;
      *@return : array();
      */
   public function get_commission_graph(){
       $post_data = $this->input->post();

    //$where = ["winning_amount >"=>0,"type"=>0];
    $result = $this->db->select("sum(if(source =320, (`O`.`real_amount`+ `O`.`winning_amount`), 0)) as signup_commission, sum(if(source=321, (`O`.`real_amount`+ `O`.`winning_amount`), 0)) as
    deposit_comission,sum(if(source in(320,321),(`O`.`real_amount`+ `O`.`winning_amount`),0)) as total_commission")
    ->from(ORDER.' as O')
    ->where("type",0)
    ->group_start()
    ->where("O.real_amount >",0)
    ->or_where("O.winning_amount >",0)
    ->group_end()
    ->where_in('source',[320,321]);
    if(isset($post_data['user_id']) && $post_data['user_id']!=''){
        $this->db->where('user_id',$post_data['user_id']);
    }
    if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
    {
        $this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
    }
    $result = $this->db->get()->result_array()[0];
    // print_r($result);exit;
    $series_records = array();
    if($result['total_commission']>0){
        $s_commission = ROUND(($result['signup_commission']*100)/$result['total_commission']);
        $d_commission = ROUND(($result['deposit_comission']*100)/$result['total_commission']);
    }
    else{
        $s_commission = 0;
        $d_commission = 0;
    }
    $signup_rec = array(
        "name"=>"signup",
        "y"=>$s_commission,
        "commission"=>$result['signup_commission'],
        "color"=>"#F08C42",
        "currency"=>CURRENCY_CODE_HTML
    );
    $deposit_rec = array(
        "name"=>"deposit",
        "y"=>$d_commission,
        "commission"=>$result['deposit_comission'],
        "color"=>"#5DBE7D",
        "currency"=>CURRENCY_CODE_HTML
    );
    $series_records[]= $signup_rec; 
    $series_records[]= $deposit_rec;
    unset($result['deposit_comission']);
    unset($result['signup_commission']);
    $result[]=array_values($series_records);
    return $result;
   }

	public function get_signup_graph($post)
	{
	
		$this->db->select('count(friend_id) as signup,
        DATE_FORMAT(created_date,"%Y-%m-%d") as date_added')

/*        DATE_FORMAT(created_date,"Week_%u_%y") as week_number,
        concat_ws(" ",DATE_FORMAT(created_date,"Week %u"),
        DATE_FORMAT(DATE_ADD(created_date, INTERVAL(1-DAYOFWEEK(created_date)) DAY),"%Y-%m-%d") ,
        DATE_FORMAT(DATE_ADD(created_date, INTERVAL(7-DAYOFWEEK(created_date)) DAY),"%Y-%m-%d")) as created ')*/

        ->from(USER_AFFILIATE_HISTORY.' UAH')
        ->where('UAH.is_affiliate',1)
        ->where('UAH.affiliate_type',6)
        ->where('UAH.status',1)
        ->where('UAH.source_type',3);
        if(isset($post['user_id']) && $post['user_id']!=''){
            $this->db->where('UAH.user_id',$post['user_id']);
        }

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(UAH.created_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(UAH.created_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
        }

        $result = $this->db->order_by('created_date','ASC')
		->group_by("date_added")
        ->order_by('date_added','ASC');
        
        $tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->get()
        ->result_array();
        // echo $this->db->last_query();die;

        return  array('total'=>$total,'result' => $result);  
    }

    public function get_deposit_graph($post)
	{
	
		$this->db->select('sum(O.real_amount) as deposit,
        DATE_FORMAT(O.date_added,"%Y-%m-%d") as deposit_date')
        ->from(ORDER.' O')
        ->join(USER_AFFILIATE_HISTORY.' UAH','UAH.friend_id = O.user_id','left')
        // ->where('O.source',321)
        ->where('UAH.is_affiliate',1)
        ->where('UAH.affiliate_type',6)
        ->where('O.status',1)
        ->where('O.source',7);
        if(isset($post['user_id']) && $post['user_id']!=''){
            $this->db->where('UAH.user_id',$post['user_id']);
        }

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db->order_by('O.date_added','ASC')
		->group_by("deposit_date")
		->order_by('deposit_date','ASC');
        
        $tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        // print_r($total);exit;
        
        $result = $this->db->get()
        ->result_array();
        // echo $this->db->last_query();die;
       return  array('total'=>$total,'result' => $result);  
    }

    public function update_affiliat_rake(){
        $data = $this->input->post(); 
        $where = ['user_unique_id'=>$data['user_unique_id']]; 
        $post_data['site_rake_status'] = $data['siterake_status'];   
        $post_data['site_rake_commission'] = $data['siterake_commission'];
        $result = $this->db->update(USER,$post_data,$where);
        return $result;
    }

  



     /**
	 * [contest_list description]
	 * @MethodName contest_list
	 * @Summary This function used for get all contest List
	 * @return     [array]
	 */
	public function affliate_match_report($post_data)
	{  
		$this->db = $this->load->database('db_user', TRUE);
		$sort_field = 'AR.schedule_date';
		$sort_order = 'DESC';
		$limit      = 50;
		$page       = 0;
		
		// $post_data = $post_params;

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array()))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		
	
		if (isset($post_data['csv']) && $post_data['csv'] == true) 	
		{
		$this->db->select("IFNULL(U.first_name,U.user_name) as first_name,U.phone_no,AR.league_name,AR.entity_name,AR.schedule_date,AR.total_user,AR.entry_fee,AR.rake_amount,AR.user_rake,AR.user_amount",false);

		
	
			$tz_diff = get_tz_diff($this->app_config['timezone']);
	
			$this->db->select("CONVERT_TZ(AR.schedule_date, '+00:00', '".$tz_diff."') AS schedule_date");
		}else{
			
			$this->db->select("IFNULL(U.first_name,U.user_name) as first_name ,U.phone_no,AR.league_name,AR.entity_name,AR.schedule_date,AR.total_user,AR.entry_fee,AR.rake_amount,AR.user_rake,AR.user_amount",false);			
		}

		$this->db->from(AFFILIATE_REPORT." AS AR")
        ->join(USER.' AS U', 'U.user_id = AR.user_id', 'INNER');	

		if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
		{
			$this->db->where('AR.sports_id',$post_data['sports_id']);
		}
		if(isset($post_data['league_id']) && $post_data['league_id'] != '')
		{
			$this->db->where('AR.league_id',$post_data['league_id']);
		}
	
	
		if(isset($post_data['entity_id']) && $post_data['entity_id']!="")
		{
			$this->db->where('AR.entity_id',$post_data['entity_id']);
		}		

		// if(isset($post_data['keyword']))
		// {
		// 	$this->db->like('G.contest_name',$post_data['keyword']);
		// }



	    if(!empty($post_data['from_date'])&&!empty($post_data['to_date']))
			$this->db->where("DATE_FORMAT(AR.schedule_date,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(AR.schedule_date,'%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."'");
		
		 $tempdb = clone $this->db;
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows(); 

		// echo $temp_q->last_query(); die;

		if(!empty($sort_field) && !empty($sort_order))
		{
			$this->db->order_by($sort_field, $sort_order);
		}

		if(!empty($limit) && !$post_data["csv"])
		{
			$this->db->limit($limit, $offset);
		}
		$sql = $this->db->get();
		$result	= $sql->result_array();
		// echo $this->db->last_query();die;
		return array('result'=>$result, 'total'=>$total);
	}

    public function get_affiliate_sport_leagues($post){

        
        $this->db = $this->load->database('db_user', TRUE);
        $this->db->select('AR.league_id,AR.league_name')
			->from(AFFILIATE_REPORT." AS AR")
            ->group_by("AR.league_id")		
			->where('AR.sports_id', $post['sports_id']);
		$result = $this->db->get()->result_array();
		return $result;
    }



    public function get_affiliate_match_by_leagues($post){

        
        $this->db = $this->load->database('db_user', TRUE);
        $this->db->select('AR.entity_id,AR.entity_name')
			->from(AFFILIATE_REPORT." AS AR")	
             ->group_by("AR.entity_id")		
			->where('AR.sports_id', $post['sports_id'])
			->where('AR.league_id', $post['league_id']);
		$result = $this->db->get()->result_array();
		return $result;
    }


       public function get_total_affiliate_site_rake(){        
        $this->db = $this->load->database('db_user', TRUE);
        $this->db->select('sum(U.site_rake_commission) as total_commision')
			->from(USER." AS U");       
		$result = $this->db->get()->row_array();
		return $result;
    }

  
    


}
?>