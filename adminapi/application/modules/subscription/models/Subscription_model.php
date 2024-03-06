<?php
if (!defined('BASEPATH'))    exit('No direct script access allowed');

class Subscription_model extends MY_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function add_package($post_data)
    {
        $data = array(
            "android_id"          =>$post_data['android_id'] ? $post_data['android_id']:null,
            "ios_id"        =>$post_data['ios_id'] ? $post_data['ios_id']:null,
            "name"      =>$post_data['name'],
            "amount"        =>$post_data['amount'],
            "coins"             =>$post_data['coins'],
            "status"             =>'1',
            "added_date"      =>format_date(),
        );

        $this->db->insert(SUBSCRIPTION,$data);
        if ($this->db->affected_rows() > 0 )
		{
			return TRUE;
		}
		
		return FALSE;    
    }

    public function remove_package($post)
    {
        $this->db->update(SUBSCRIPTION,['is_deleted'=>'1'],['subscription_id'=>$post['subscription_id']]);
        if ($this->db->affected_rows() > 0 )
		{
			return TRUE;
		}
		
		return FALSE;
    }

    public function get_packages($post_data)
    {
        $sort_field	= 'subscription_id';
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
		}

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('subscription_id','name','amount','coins','status','added_date','modified_date')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

        $offset	= $limit * $page;

        $result = $this->db->select('subscription_id,name,amount,coins,android_id,ios_id,added_date,modified_date')
        ->from(SUBSCRIPTION)
        ->where('is_deleted','0')
        ->where('status','1')
        ->order_by($sort_field, $sort_order);
        
        if(isset($post_data['name']) && $post_data['name']!='')
        {
            $this->db->like("name",$post_data['name']);
        }

        if(isset($post_data['subscription_id']) && $post_data['subscription_id']!='')
        {
            $this->db->where("subscription_id",$post_data['subscription_id']);
        }

        if(isset($post_data['from_date']) && isset($post_data['to_date']) && $post_data['from_date'] != '' && $post_data['to_date'] != '')
		{
			$this->db->where("DATE_FORMAT(added_date, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(added_date, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}

		$tempdb = clone $this->db;
		if($this->input->post('csv') == false)
		{
            $total = $tempdb->get()->num_rows();
            $result = $this->db->limit($limit,$offset);
		}
		
        $result = $this->db->get()->result_array();
        // echo $this->db->last_query();exit;
        if($this->input->post('csv') == false)
		{
            return ["result"=>$result,"total"=>$total];
		}else{
            return ["result"=>$result];
        }
        
    }

    public function check_exist($post_data)
    {
        $where = array(
            "android_id"=>$post_data['android_id'],
            "ios_id"=>$post_data['ios_id'],
            "is_deleted"=>0,
            "status"=>1
        );

        if(empty($post_data['ios_id']))
        {
        $result = $this->db->select('*')
        ->from(SUBSCRIPTION)
        ->where('is_deleted','0')
        ->where('status','1')
        ->where('android_id',$post_data['android_id'])
        ->get()->result_array();
        }
        else if(empty($post_data['android_id']))
        {
        $result = $this->db->select('*')
        ->from(SUBSCRIPTION)
        ->where('is_deleted','0')
        ->where('status','1')
        ->where('ios_id',$post_data['ios_id'])
        ->get()->result_array();
        }
        else if(isset($post_data['ios_id']) && isset($post_data['android_id']))
        {
            $sql = "select * from vi_subscription WHERE is_deleted='0' and status='1' and (android_id = '".$post_data['android_id']."' OR ios_id = '".$post_data['ios_id']."' )";
            $result = $this->db->query($sql)->result();
        }
        // echo $this->db->last_query();exit;
        if(empty($result))
        {
            return false;
        }
        return true;
    }

    public function get_subscription_report($post_data)
    {
        $sort_field	= 'O.date_added';
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
		}

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('subscription_id','user_name','coins','amount')))
		{
            if($post_data['sort_field']=='coins') $post_data['sort_field']='O.points';
            if($post_data['sort_field']=='subscription_id') $post_data['sort_field']='S.subscription_id';
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

        $offset	= $limit * $page;
        // $amount = "custom_data->>'$.amount'";
        // $coins = "custom_data->>'$.coins'";
        //I have taken transaction table txn amount insted of custome data from order table because of sorting issue as json decode of custom data is a string in query.
        $result = $this->db->select("T.transaction_id,max(O.date_added) AS order_date,S.name,U.user_name,U.email,US.expiry_date,T.txn_amount AS amount, O.points AS coins")
        ->from(USER_SUBSCRIPTION." US")
        ->Join(SUBSCRIPTION." S","S.subscription_id = US.subscription_id and S.status='1' AND S.is_deleted='0'","INNER")
        ->join(USER." U","U.user_id = US.user_id and U.status=1 and U.is_systemuser=0","INNER")
        ->join(ORDER." O","O.user_id = U.user_id and O.status=1 and source = 437","INNER")
        ->join(TRANSACTION.' T','T.order_id = O.order_id',"INNER")
        ->where(["US.status"=>'1'])
        ->group_by("O.user_id");
        
        if(isset($post_data['from_date']) && isset($post_data['to_date']) && $post_data['from_date'] != '' && $post_data['to_date'] != '')
		{
			$this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
        }
        
        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(U.user_unique_id,IFNULL(U.email,""),IFNULL(U.phone_no,""),IFNULL(U.user_name,""),CONCAT_WS(" ",U.first_name,U.last_name))', $post_data['keyword']);
        }
        
        if(isset($post_data['subscription_id']) && $post_data['subscription_id'] != "")
		{
			$this->db->where('S.subscription_id',$post_data['subscription_id']);
		}

		$tempdb = clone $this->db;
		if($this->input->post('csv') == false)
		{
            $total = $tempdb->get()->num_rows();
            $result = $this->db->limit($limit,$offset);
		}
		
        $result = $this->db->order_by($sort_field,$sort_order)->get()->result_array();
        // echo $this->db->last_query();exit;
        if($this->input->post('csv') == false)
		{
            $total_earn = $this->calculate_total_earning($post_data);
            return ["result"=>$result,"total"=>$total,"total_earn"=>$total_earn];
		}else{
            return ["result"=>$result];
        }


    }

    public function calculate_total_earning($post_data)
    {
        $amount = "custom_data->>'$.amount'";
        $result = $this->db->select("sum(".$amount.") AS total_earn")
        ->from(USER_SUBSCRIPTION." US")
        ->Join(SUBSCRIPTION." S","S.subscription_id = US.subscription_id and S.status='1' AND S.is_deleted='0'","INNER")
        ->join(USER." U","U.user_id = US.user_id and U.status=1 and U.is_systemuser=0","INNER")
        ->join(ORDER." O","O.user_id = U.user_id and O.status=1 and source = 437","INNER")
        ->where(["US.status"=>'1']);
        if(isset($post_data['from_date']) && isset($post_data['to_date']) && $post_data['from_date'] != '' && $post_data['to_date'] != '')
		{
			$this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
        }
        
        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(U.user_unique_id,IFNULL(U.email,""),IFNULL(U.phone_no,""),IFNULL(U.user_name,""),CONCAT_WS(" ",U.first_name,U.last_name))', $post_data['keyword']);
        }
        
        if(isset($post_data['subscription_id']) && $post_data['subscription_id'] != "")
		{
			$this->db->where('S.subscription_id',$post_data['subscription_id']);
		}
        $result = $this->db->get()->row_array();
        return $result['total_earn'];
    }

}

?>