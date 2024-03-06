<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Point_system_model extends MY_Model{

  	function __construct()
  	{
  		parent::__construct();
  	}

	 /*----------------------------------Advertisement management -------------------------------------------------*/
     /*
      * function : get_positions
      * def: get all active positions
      * @params : 
      * @return : array positions
      */
     public function get_positions()
     {
         $sql = $this->db->select('*')
                         ->from(ADS_POSITION)
                         ->where('status',1)
                         ->get();
         return $sql->result_array();
     }

     /*
      * function : get_positions
      * def: get all active positions
      * @params : 
      * @return : array positions
      */
     public function create_ads($data)
     {
       	$post_data = array(
                  'ads_unique_id'  => random_string('alnum',9), 
                  'name'           => $data['name'], 
                  'target_url'     => $data['target_url'], 
                  'image_adsense'  => $data['ads_image'],
                  'ad_position_id' => $data['position_type'], 
                  'created_date'   => format_date()
       				);
        $this->db->insert(ADS_MANAGEMENT,$post_data);
        return $this->db->insert_id();
     }
     
     /*
      * function : get_advertisement
      * def: get all active positions
      * @params : 
      * @return : array positions
      */
    public function get_advertisement($config = array(), $is_total = FALSE)
    {
        $sql = $this->db->select('ADM.name,ADM.ad_management_id,ADM.ads_unique_id,ADM.target_url,ADP.type as ads_position ,CONCAT(ADP.height,"*",ADP.width) as ads_size,ADM.view,ADM.click,ADM.status', FALSE)
          ->from(ADS_MANAGEMENT . ' as ADM')
          ->join(ADS_POSITION . " as ADP", "ADP.ad_position_id = ADM.ad_position_id", "inner");

        if ($config['filter_name'] != '')
        {

            $this->db->like('U.first_name', $config['filter_name'], 'both');
            $this->db->like_or('U.last_name', $config['filter_name'], 'both');
        }

        if ($config['limit'] == 'null')
        {
            $config['limit'] = 10;
        }

        if ($is_total === FALSE)
        {
            $this->db->limit($config['limit'], $config['start']);
        }

        if ($config['fieldname'] != '' && $config['order'] != '')
        {
            $this->db->order_by($config['fieldname'], $config['order']);
        }
        else
        {
            $this->db->order_by("ADM.name", 'ASC');
        }

        if ($is_total === FALSE)
        {
            return $this->db->get()->result_array();
        }
        else
        {
            return $this->db->get()->num_rows();
        }
        return $sql->result_array();
    }
     
     /*
      * function : get_advertisement_by_id
      * def: get advertiment detial by id
      * @params : int id
      * @return : array advertisement
      */
     public function get_advertisement_by_id($id)
     {
         $sql = $this->db->select('ADM.name,ADM.ad_management_id,ADP.ad_position_id ,ADM.target_url,ADP.type as ads_position ,CONCAT(ADP.height,"*",ADP.width) as ads_size,ADM.view,ADM.click,ADM.status,ADM.image',FALSE)
                         ->from(ADS_MANAGEMENT.' as ADM')
                         ->join(ADS_POSITION. " as ADP","ADP.ad_position_id = ADM.ad_position_id","inner")
                         ->where('ADM.ad_management_id',$id)
                         ->get();
         return $sql->row_array();
     }
     
   /*
    * function : update_advertisement_by_id
    * def: Update advertisement detail by di
    * @params : int id
    * @return : int 0,1
    */
     public function update_advertisement_by_id($id,$data)
     {
        $this->db->where('ad_management_id', $id)
        ->update(ADS_MANAGEMENT, $data); 
        return $this->db->affected_rows();
     }
     
   /*
    * function : update_advertisement_by_id
    * def: Update advertisement detail by di
    * @params : int id
    * @return : int 0,1
    */
    public function get_position_type($id)
    {
      $sql = $this->db->select('*')
                      ->from(ADS_POSITION)
                      ->where('ad_position_id', $id)
                      ->get();
      return $sql->row_array();
    }
}