<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Banner_model extends MY_Model{

  	function __construct()
  	{
		  parent::__construct();
	  }
	   /*
    * function : get_banner_type
    * def: get banner type list
    * @params : int id
    * @return : int 0,1
    */
    public function get_banner_type()
    {
      $sql = $this->db->select('*')
                      ->from(BANNER_TYPE)
                      ->where('status', "1")
                      ->get();
      return $sql->result_array();
    }

    /*
      * function : get_banner_list
      * def: get all banners list
      * @params : 
      * @return : array banners
      */
    public function get_banner_list($config = array(), $is_total = FALSE)
    {     
       
        $sql = $this->db->select('BM.*,BT.banner_type,IFNULL(SH.en_title,"All") as sports_name,SH.game_key', FALSE)
          ->from(BANNER_MANAGEMENT . ' as BM')
          ->join(BANNER_TYPE . " as BT", "BT.banner_type_id = BM.banner_type_id", "inner")
          ->join(SPORTS_HUB . " as SH", "SH.sports_hub_id = BM.game_type_id", "LEFT")
          ->where("BM.is_deleted","0");

        if (isset($config['filter_name']))
        {

            $this->db->like('BM.name', $config['filter_name'], 'both');
        }

        $offset =0;

        
        if (!isset($config['items_perpage']))
        {
            $config['items_perpage'] = 50;
        }

        if(isset($config['current_page']))
        {
          $offset = $config['items_perpage']*($config['current_page']-1);
        }

        if ($is_total === FALSE)
        {
            $this->db->limit($config['items_perpage'], $offset);
        }

        
        if ($is_total === FALSE)
        {
          $sql =$this->db->get();
            return $sql->result_array();
        }
        else
        {
            return $this->db->get()->num_rows();
        }
        return $sql->result_array();
    }

     /*
      * function : create_banner
      * def: get all active positions
      * @params : 
      * @return : array positions
      */
     public function create_banner($data)
     {  
      // echo "<pre>";
      // print_r($data);die();
       	$post_data = array(
                  'banner_unique_id'  => random_string('alnum',9), 
                  'banner_type_id' => $data['banner_type_id'],
                  'game_type_id' => $data['game_type_id'],
                  'name'           => $data['name'], 
                  'target_url'     => isset($data['target_url']) ? $data['target_url'] : "",
                  'image'  => $data['image'],
                  'collection_master_id' => !empty($data['collection_master_id']) ? $data['collection_master_id'] : 0, 
                  'scheduled_date' => !empty($data['scheduled_date']) ? $data['scheduled_date'] : NULL, 
                  'created_date'   => format_date()
       				);
        $this->db->insert(BANNER_MANAGEMENT,$post_data);
        return $this->db->insert_id();
     }
     
   /*
    * function : update_banner_by_id
    * def: Update banner detail by id
    * @params : int id
    * @return : int 0,1
    */
     public function update_banner_by_id($id,$data)
     {
        $this->db->where('banner_id', $id)
        ->update(BANNER_MANAGEMENT, $data); 
        return $this->db->affected_rows();
     }

    public function get_lobby_banner_list()
    {
      $post_data = $this->input->post();
      $current_date = format_date();
      $time = strtotime($current_date);
      $time = $time + (CONTEST_DISABLE_INTERVAL_MINUTE * 60);//one hour
      $close_date = date("Y-m-d H:i:s",$time);
      $sql = $this->db->select('BM.banner_id,BM.banner_unique_id,BM.banner_type_id,BM.name,BM.target_url,BM.image,BM.collection_master_id,BT.banner_type', FALSE)
          ->from(BANNER_MANAGEMENT . ' as BM')
          ->join(BANNER_TYPE . " as BT", "BT.banner_type_id = BM.banner_type_id", "INNER")
          ->where("BM.is_deleted","0")
          ->where("BM.banner_type_id !=","5")
          ->where("BM.status","1");
      $this->db->order_by("BM.banner_type_id != ".LOBBY_WHYUS_BANNER_TYPE_ID,NULL,FALSE);
      $this->db->order_by("BM.banner_type_id");
      $this->db->order_by("BM.banner_id","ASC");
      $result_record = $this->db->get()->result_array();
      //echo $this->db->last_query();die;
      //echo "<pre>";print_r($result_record);die;
      return $result_record;
    }

    public function get_active_stock_key($stock_key)
    {
      if (!empty($stock_key)) {   
        $sql = $this->db->select('game_key')
                        ->from(SPORTS_HUB)
                        ->where('status', "1")
                        ->where_in('game_key', $stock_key)                   
                        ->get();
        return $sql->row_array()['game_key'];
      }else{
         return NULL;
      }
     
    }

    /*
    * function : get_banner_type
    * def: get banner type list
    * @params : int id
    * @return : int 0,1
    */
    public function get_sports_type($stock_key)
    {
      $game_new_key = array();

      if(isset($this->app_config['allow_dfs']['key_value']) && $this->app_config['allow_dfs']['key_value']==1)
      {
        array_push($game_new_key,"allow_dfs");
      }

      if(isset($this->app_config['allow_multigame']['key_value']) && $this->app_config['allow_multigame']['key_value']==1)
      {
        array_push($game_new_key,"allow_multigame");
      }

      if(isset($this->app_config['allow_pickem']['key_value']) && $this->app_config['allow_pickem']['key_value']==1)
      {
        array_push($game_new_key,"allow_pickem");
      }

      if(isset($this->app_config['allow_livefantasy']['key_value']) && $this->app_config['allow_livefantasy']['key_value']==1)
        {
           array_push($game_new_key,"live_fantasy");
      }
      if ($stock_key) {
       
        if(isset($this->app_config[$stock_key]['key_value']) && $this->app_config[$stock_key]['key_value']==1)
        {
          array_push($game_new_key,$stock_key);
        }
      }


      $sql = $this->db->select('sports_hub_id,en_title,game_key,status')
                      ->from(SPORTS_HUB)
                      ->where('status', "1")
                      ->where_in('game_key', $game_new_key)                                     
                      ->get();
      return $sql->result_array();
    }
}