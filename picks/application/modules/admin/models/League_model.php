<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class League_model extends MY_Model{

  	function __construct()
  	{
	  	parent::__construct();
	}


	public function get_league_list($post_data){
		$result = $this->db->select('league_id,sports_id,IFNULL(display_name,league_name) as league_name')
		->from(LEAGUE)
		->where(array("status"=>"1","sports_id"=>$post_data['sports_id']));
			if (!empty($post_data['search_text'])) 
					{
						$this->db->like('LOWER(CONCAT(IFNULL(league_name,""),IFNULL(display_name,"")))', strtolower($post_data['search_text']));
					}
		$result = $this->db->get()->result_array(); 
		return $result;
	}

	public function check_league_exit($league_id){
		$this->db->select("*", FALSE)
        ->from(SEASON." AS S")		
        ->where_in("S.league_id",$league_id); 	
	    return $this->db->get()->result_array();	  	
	}

	public function check_sports_exit($sports_id){
		$this->db->select("*", FALSE)
        ->from(CONTEST." AS C")		
        ->where_in("C.sports_id",$sports_id); 	
	    return $this->db->get()->result_array();	  	
	}

}
