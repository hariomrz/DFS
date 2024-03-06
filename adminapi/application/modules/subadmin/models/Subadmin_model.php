<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Subadmin_model extends MY_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }   

    /**
     * This method return all sub admin list 
     * @param array $config
     * @param bool $is_total
     * @return array
     */
    public function get_admin_list($config = array(), $is_total = false)
    {
        $this->db->select('A.admin_id as id,A.admin_id as user_unique_id, A.firstname, A.username, A.email, A.status, A.privilege', FALSE)
                ->from(ADMIN . ' AS A');
        
        $this->db->where('A.role', SUBADMIN_ROLE);
        $this->db->where('A.status != ', '2');

        if ($config['filter_name'] != '')
        {
            $this->db->where('(A.firstname LIKE "%'.$config['filter_name'].'%"');
            $this->db->or_where('A.username LIKE "%'.$config['filter_name'].'%"');
            $this->db->or_where('A.email LIKE "%'.$config['filter_name'].'%")');
        }

        if($config['dataparam']['items_perpage'])
        {
          $limit = $config['dataparam']['items_perpage'];
        }

        if($config['dataparam']['current_page'])
        {
          $page = $config['dataparam']['current_page']-1;
        }
        $offset = $limit * $page;        

        $tempDb = clone $this->db;

        $total = $tempDb->get()->num_rows();

        if ($is_total === FALSE)
        {
            $this->db->limit($limit,$offset);
        }

        if ($config['fieldname'] != '' && $config['order'] != '')
        {
            $this->db->order_by($config['fieldname'], $config['order']);
        }
        else
        {
            $this->db->order_by("A.firstname", 'ASC');
        }

        $sql = $this->db->get();
        if ($config['is_csv'] == FALSE)
        {
                if ($is_total === FALSE)
                {
                        return  array("total" => $total,"result" => $sql->result_array())  ;
                }
                else
                {
                        return $sql->num_rows();
                }
        } else
        {
            $this->load->helper('download');
            //
            $data = $this->csv_from_result_for_admin($sql);
            //$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
            $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . html_entity_decode($data);
            $name = 'SubAdminReport'.time().'.csv';
            force_download($name, $data);
            return exit();
        }
    }

    /*
      * function : update_admin_by_id
      * def: Update admin detail by di
      * @params : int id, array data
      * @return : int 0,1
      */
     public function update_admin_by_id($id,$data)
     {
         $this->db->where('admin_id', $id)
                  ->update(ADMIN, $data); 
         return $this->db->affected_rows();
     }
     
     /*
      * function : get_admin_by_id
      * def: get admin detial by id
      * @params : int id
      * @return : array admin detail
      */
     public function get_admin_by_id($id)
     {
         $sql = $this->db->select('admin_id, email, firstname, lastname, username,privilege',FALSE)
                         ->from(ADMIN.' as A')
                         ->where('A.admin_id',$id)
                         ->get();
         return $sql->row_array();
     }
     
     /**
	 * Generate CSV from a admin result object
	 *
	 * @access	public
	 * @param	object	The query result object
	 * @param	string	The delimiter - comma by default
	 * @param	string	The newline character - \n by default
	 * @param	string	The enclosure - double quote by default
	 * @return	string
	 */
	function csv_from_result_for_admin($query, $delim = ",", $newline = "\n", $enclosure = '"')
	{
		if ( ! is_object($query) OR ! method_exists($query, 'list_fields'))
		{
			show_error('You must submit a valid result object');
		}

		$out = '';

		// First generate the headings from the table column names
		foreach ($query->list_fields() as $name)
		{
        if($name == 'privilege')
        {
            //{"game":1,"date":0,"roster":1,"advertise":1,"user":1,"report":1}
            $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'game management').$enclosure.$delim;
            $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'roster management').$enclosure.$delim;
            $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'advertise management').$enclosure.$delim;
            $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'user management').$enclosure.$delim;
            $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'report management').$enclosure.$delim;
            $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, 'trending player management').$enclosure.$delim;
        } else 
        {
            $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $name).$enclosure.$delim;
        }
		}

		$out = rtrim($out);
		$out .= $newline;

		// Next blast through the result array and build out the rows
		foreach ($query->result_array() as $row)
		{
			foreach ($row as $item_key=>$item)
			{ 
          if($item_key == 'privilege'){
                  $privilege = json_decode($item);
                  
                  $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, ($privilege->game) ? 'Yes': 'No').$enclosure.$delim;
                  $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, ($privilege->roster) ? 'Yes': 'No').$enclosure.$delim;
                  $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, ($privilege->advertise) ? 'Yes': 'No').$enclosure.$delim;
                  $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, ($privilege->user) ? 'Yes': 'No').$enclosure.$delim;
                  $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, ($privilege->report) ? 'Yes': 'No').$enclosure.$delim;
                  $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, ($privilege->trending) ? 'Yes': 'No').$enclosure.$delim;
                  
          } else {
                  $out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
          }
			}
			$out = rtrim($out);
			$out .= $newline;
		}

		return $out;
	}

  /* 
  * Method to get admin group list
  */
  public function get_subadmin_group_list()
  {
     $sql = "SELECT C.category_name,C.category_id 
              FROM 
                vi_admin_group_category as C              
              WHERE 
                C.status ='1'
                ";
      $query  = $this->db->query($sql);
      $result = $query->result_array();
      return $result;      
  }

   /* 
    * Method to get admin group list
    */
    public function group_detail_by_id($category_id)
    {
      
       $sql = "SELECT SC.sub_category_id,SC.sub_category_name,SC.name_abbr  
                FROM 
                  vi_admin_group_sub_category as SC              
                WHERE 
                  SC.status ='1' 
                AND SC.category_id IN(".$category_id.")
                  ";
        $query  = $this->db->query($sql);
        $result = $query->result_array();
        $arr    = array();          
        foreach ($result as $key => $value) 
        {
          $arr[] = $value['name_abbr'];
        }
        
        return $arr;        
    }

    public function group_detail_by_abbr($name_abbr)
    {

       $sql = "SELECT 
                  group_concat(SC.category_id) as category_id
                FROM 
                  vi_admin_group_sub_category as SC           
                WHERE 
                  SC.status ='1' 
                AND SC.name_abbr IN(".$name_abbr.")
                  ";

        $query  = $this->db->query($sql);
        $result = $query->row_array();
        return $result['category_id'];        
    }

}
