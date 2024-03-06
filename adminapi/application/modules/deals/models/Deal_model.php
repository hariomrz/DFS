<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Deal_model extends MY_Model{

  	function __construct()
  	{
		  parent::__construct();
		  $this->db_user		= $this->load->database('db_user', TRUE);
	  }



    public function get_deal_list($config = array(), $is_total = FALSE)
    {
        $sql = $this->db_user->select('*', FALSE)
          ->from(DEALS . ' as D')
          ->where("D.is_deleted","0");
       
        if (!isset($config['limit']))
        {
            $config['limit'] = 10;
        }

        if ($is_total === FALSE)
        {
            $this->db_user->limit($config['limit'], $config['limit']*($config['current_page']-1));
        }

        if ($is_total === FALSE)
        {
            return $this->db_user->get()->result_array();
        }
        else
        {
            return $this->db_user->get()->num_rows();
        }
        return $sql->result_array();
    }

    public function _generate_key() 
	{
		$this->load->helper('security');
		do {
			$salt = do_hash(time() . mt_rand());
			$new_key = substr($salt, 0, 10);
		}

		// Already in the DB? Fail. Try again
		while (self::_key_exists($new_key));

		return $new_key;
	}

	public function _key_exists($key)
	{
		$this->db->select('deal_id');
        $this->db->where('deal_unique_id', $key);
        $this->db->limit(1);
        $query = $this->db->get(DEALS);
        $num = $query->num_rows();
        if($num > 0){
            return true;
        }
		return false;

	}

     public function create_deal($data)
     {
         $current_date = format_date();
       	$post_data = array(
                  'deal_unique_id' => $this->_generate_key(),
                  'amount'           => $data['amount'], 
                  'cash'           => $data['real'], 
                  'bonus'           => $data['bonus'], 
                  'coin'           => $data['coins'], 
                  'amount'           => $data['amount'], 
                  'added_date' => $current_date,
                  'modified_date' => $current_date
                   );
                   
        $this->db_user->insert(DEALS,$post_data);
        return $this->db_user->insert_id();
     }
    
     public function update_deal_by_id($id,$data)
     {
        $this->db_user->where('deal_unique_id', $id)
        ->update(DEALS, $data); 
        return $this->db_user->affected_rows();
     }

     public function check_deal_used($key)
     {
        $this->db_user->select('D.deal_id')->from(DEALS.' D');
        $this->db_user->join(DEALS_EARNING.' DE','D.deal_id=DE.deal_id');
        $query = $this->db_user->where('deal_unique_id', $key)->get()->row_array();
    
        if(!empty($query)){
            return true;
        }
		return false;
     }

     public function get_deal_availiblity(){
      $query  = $this->db_user->select('D.deal_id')->from(DEALS.' D')->where('status',1)->where('is_deleted',0)->get();
      $count = $query->num_rows();

      if($count < 7){
        return true;
      }
      return false;
    }


  public function get_deals_detail($post_data){
      $this->db_user->select('*')
      ->from(DEALS.' D')
      ->where('deal_unique_id',$post_data['deal_unique_id']);     
       return $query = $this->db_user->get()->row_array();
  }

  public function get_user_by_deal_id($deal_id){

    $limit      = 50;
    $page       = 0;
    $post_data = $this->input->post();
    $sort_field = "";
    $sort_order = "DESC";
    
    if(isset($post_data['sort_field']))
    {
      $sort_field = $post_data['sort_field'];
    }

    if(isset($post_data['sort_order']))
    {
      $sort_order = $post_data['sort_order'];
    }

    if(isset($post_data['items_perpage']))
    {
      $limit = $post_data['items_perpage'];
    }

    if(isset($post_data['current_page']))
    {
      $page = $post_data['current_page']-1;
    }

    $offset = $limit * $page;

      $this->db_user->select("DE.deal_earning_id,DE.deal_id,DE.user_id,DE.order_id,IFNULl(U.user_name,'') as user_name,IFNULL(U.first_name,'') as first_name,IFNULL(U.last_name,'') as last_name,DE.added_date,D.bonus,D.cash,D.coin,IFNULL(D.amount,'') as amount_added,IFNULL(D.amount+D.cash,0) as actual_paid")
      ->from(DEALS.' D')
      ->join(DEALS_EARNING.' DE','DE.deal_id=D.deal_id',"LEFT")
      ->join(USER.' U','U.user_id=DE.user_id',"LEFT")
      // ->join(ORDER.' ORD','ORD.order_id=DE.order_id',"LEFT")
      ->where('DE.deal_id',$deal_id) 
      ->where('DE.is_processed',"1")
      ->where('D.is_deleted',"0"); 

      $tempdb = clone $this->db_user;     
      $query = $this->db_user->get();
      $total = $query->num_rows();
      
      $sql = $tempdb->limit($limit, $offset)
              ->order_by($sort_field,$sort_order)
              ->get();
      //echo $tempdb->last_query();
      $result = $sql->result_array();

      $result = ($result) ? $result : array();

      return array('result'=>$result, 'total'=>$total);
      
        // $query  = $this->db_user->get();
        // $data = $query->result_array();
        // $count = $query->num_rows();
        // echo $this->db_user->last_query(); die();
        // return $result = array('result' =>  $data,'total_count' => $count );
  }

}