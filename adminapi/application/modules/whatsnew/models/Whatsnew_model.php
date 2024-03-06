<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Whatsnew_model extends MY_Model{

  	function __construct()
  	{
		  parent::__construct();
	  }

    /**
     * Used for get all whats new record list
     * @param array $post_data
     * @return json array
     */
    public function get_record_list($post_data = array())
    {     
      $pagination = get_pagination_data($post_data);
      $sql = $this->db->select('WN.*', FALSE)
          ->from(WHATS_NEW.' as WN')
          ->order_by("WN.id","ASC");

      $tempdb = clone $this->db;
      $query = $this->db->get();
      $total = $query->num_rows();

      $sql = $tempdb->limit($pagination['limit'],$pagination['offset'])->get();
      $result = $sql->result_array();
      // echo $this->db_fantasy->last_query(); die;
      $result = isset($result) ? $result : array();
      return array('result' => $result,'total' => $total);
    }

    /**
    * Function used for save whats new data
    * @param array $post_data
    * @return array
    */
    public function save_record($data_arr,$id="")
    {
      if($id != ""){
        $this->db->where('id', $id);
        $this->db->update(WHATS_NEW, $data_arr);
        $result = $id;
      }else{
        $this->db->insert(WHATS_NEW,$data_arr);
        $result = $this->db->insert_id();
      }

      if($result){
        return $result;
      }else{
        return false;
      }
    }

    /**
    * Function used for delete whats new data
    * @param id $id
    * @return array
    */
    public function delete_record($id)
    {
      if(!$id){
        return false;
      }
      $this->db->where("id",$id);
      $this->db->delete(WHATS_NEW); 
      return TRUE;
    }


    /**
     * Used for get all whats new record list
     * @param array $post_data
     * @return json array
     */
    public function count_record()
    {     
    
      $sql = $this->db->select('count(WN.id) as total_record', FALSE)
          ->from(WHATS_NEW.' as WN');       
      $query = $this->db->get();  
      $result = $query->row_array();
    
      return $result = isset($result) ? $result : array();
    
    }
}