<?php

class Player_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->db_fantasy = $this->load->database('db_fantasy', TRUE);
    }

    /** 
     * Function used to get single record from any table
     * @param array $post_data
     * @return array
     */
    public function get_player_list($post_data)
    {   $limit = 50;
        $page = 0;
        if(isset($post_data['items_perpage'])){
            $limit = $post_data['items_perpage'];
        }

        if(isset($post_data['current_page'])){
            $page = $post_data['current_page']-1;
        }
        $offset = $limit * $page;

        $this->db_fantasy->select('PL.full_name,PL.display_name,PL.player_uid,PL.country,PL.position,PL.image,PL.player_id,PL.sports_id');
        $this->db_fantasy->from(PLAYER. " PL");
        if(!empty($post_data['search']))
        {
            $this->db_fantasy->group_start();  //group start
            $this->db_fantasy->like('full_name',$post_data['search']);
            $this->db_fantasy->or_like('display_name', $post_data['search']);
            $this->db_fantasy->or_like('country', $post_data['search']);
            $this->db_fantasy->group_end();
         }
        $this->db_fantasy->where('PL.sports_id',$post_data['sports_id']);
        $tempdb = clone $this->db_fantasy;
        $total = $tempdb->get()->num_rows();
        $this->db_fantasy->limit($limit,$offset);

        $sql = $this->db_fantasy->get();
        $player_list = $sql->result_array();

        return ["player_list"=>$player_list,"total"=>$total];
    }

    /** 
     * Function used for update player image
     * @param array $post_data
     * @return array
     */
    public function save_player_image($post_data)
    {     
        $update_data = ['image'=>$post_data['image']];
        $this->db_fantasy->where('player_id', $post_data["player_id"]);
        $this->db_fantasy->update(PLAYER, $update_data);
        return true;
    }
}
