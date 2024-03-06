<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Scratchwin_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
    }

    /**
	  * method to get all scratch card list
	  */
    public function get_scratch_card_list(){
        $sort_field	= 'created_date';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		$post_data = $this->input->post();

		if(($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(($post_data['sort_field']) && in_array($post_data['sort_field'],array('scratch_card_id','amount','prize_type','status','updated_date')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		        
        $result = $this->db->select("scratch_card_id,prize_type,amount,result_text,status")
        ->from(SCRATCH_WIN);

        $tempdb = clone $this->db;
        $total = $tempdb->get()->num_rows();

        $result = $this->db->order_by($sort_field, $sort_order)
        ->limit($limit,$offset)->get()->result_array();

        return ["result"=>$result,"total"=>$total];
    }

    /**
     * to add a new scratch card 
     */
    public function add_scratch_card($post_data){
        if($post_data['amount']==0){
        $post_data['result_text'] = "Better Luck Next time";
        }
        $result = $this->db->insert(SCRATCH_WIN,$post_data);
        return $this->db->insert_id();
    }

    /**
     * to delete the scratch card 
     */

     public function delete_scratch_card(){
        $post_data['scratch_card_id'] = $this->input->post('scratch_card_id');
        $this->db->delete(SCRATCH_WIN,$post_data);
        return true;
     }

     /**
      * method to enable and disable the scratch card
      */
      public function update_scratch_card(){
        $post_data = $this->input->post();
        $update_data['updated_date'] =  format_date('today');
        $update_data['prize_type'] = $post_data['prize_type'];
        $update_data['amount'] = $post_data['amount'];
        $update_data['status'] = $post_data['status'];
        if($post_data['amount']==0){
        $update_data['result_text'] = "Better Luck Next time";
        }else{
        $update_data['result_text'] = $post_data['result_text'];
        }
        $scratch_card_id = $post_data['scratch_card_id'];
        if(in_array($update_data['status'],[0,1])){
            $this->db->update(SCRATCH_WIN,$update_data,["scratch_card_id"=>$scratch_card_id]);
            return $this->db->affected_rows();
        }
        return false;
      }
}
?>
