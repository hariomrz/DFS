<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class App_banner_model extends MY_Model{

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
     public function set_all_banners_inactive()
     {
        $this->db->where("status",1)->update(APP_BANNER,array("status"=>0));
        return $this->db->affected_rows();

     }

     /*
      * function : get_positions
      * def: get all active positions
      * @params : 
      * @return : array positions
      */
     public function create_app_banner($data)
     {
       	$post_data = array(
                  'banner_title'    => $data['banner_title'], 
                  'banner_link'     => $data['banner_link'], 
                  'banner_image'    => $data['image_name'],
                  'status'          => 1,
                  'created_date'    => format_date()
       				);
        $this->db->insert(APP_BANNER,$post_data);
        return $this->db->insert_id();
     }
     
     /*
      * function : get_advertisement
      * def: get all active positions
      * @params : 
      * @return : array positions
      */
    public function get_app_banners($config = array(), $is_total = FALSE)
    {
        $sql = $this->db->select('AB.app_banner_id,AB.banner_title,AB.banner_image,AB.banner_link,AB.status')
          ->from(APP_BANNER . ' as AB');

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
            $this->db->order_by("AB.created_date", 'DESC');
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
     
     public function update_banner_status_by_id($id,$data)
     {
        $this->db->where('app_banner_id', $id)
        ->update(APP_BANNER, $data); 
        return $this->db->affected_rows();
     }

     public function delete_banner($id)
     {
        $this->db->where("app_banner_id",$id)
                  ->delete(APP_BANNER); 
        return TRUE;          
     }
     
}