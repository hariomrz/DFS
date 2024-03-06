<?php

/**
 * This model is used for getting and storing sports related information
 * @package    blog_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Announcement_model extends Common_Model
{

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function add($insert_data = array())
    {
        if (!empty($insert_data))
        {
            $this->db->insert(BLOG, $insert_data);
            return $this->db->insert_id();
        } else
        {
            return FALSE;
        }
    }

    public function announcement_list($search_keyword = "", $count_flag = FALSE, $page_no = 1, $page_size = PAGE_SIZE, $sort_by = "CreatedDate", $order_by = "DESC", $list_type = '')
    {
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $time_format = dateformat_php_to_mysql($global_settings['time_format']);

        $this->db->select('B.BlogID,B.Status, B.Title,B.BlogGUID,  IFNULL(B.Description,"") AS Description, B.EntityType', FALSE);
        $this->db->select('IFNULL(CONCAT(U.FirstName," ",U.LastName),"") AS Author', FALSE);
        $this->db->select('DATE_FORMAT(B.CreatedDate, "' . $mysql_date . '") AS CreatedDate', FALSE);
        $this->db->select('DATE_FORMAT(B.ModifiedDate, "' . $mysql_date . '") AS ModifiedDate', FALSE);
        $this->db->from(BLOG . ' AS B');
        $this->db->join(USERS . ' AS U', 'U.UserID = B.UserID', 'inner');
        $this->db->where_in('B.EntityType', array(2, 3,4));
        $this->db->where('B.Status !=', 'DELETED');
        if (empty($list_type))
        {
            $this->db->where('B.Status', 'PUBLISHED');
        }

        if (!empty($search_keyword))
        {
            $search_keyword = strtolower($search_keyword);

            $SearchStr = "( LOWER(B.Title) like '" . $this->db->escape_like_str($search_keyword) . "%'";
            $SearchStr.= " or LOWER(B.Description) like '%" . $this->db->escape_like_str($search_keyword) . "%'";
            $SearchStr .= ')';
            $this->db->where($SearchStr);
        }

        if ($count_flag)
        {
            $res = $this->db->get();
            return $res->num_rows();
        }

        $this->db->order_by('B.' . $sort_by, $order_by);
        if (!empty($page_size))
        {
            if (empty($page_no))
            {
                $page_no = 1;
            }
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }
        $res = $this->db->get();
        //echo $this->db->last_query();die;
        $res = $res->result_array();
        $return_array = array();
        if (!empty($res))
        {
            foreach ($res as $key => $value)
            {
                unset($value['IsMediaExists']);
                unset($value['MediaID']);
                unset($value['BlogID']);
                unset($value['UserID']);
                $return_array[] = $value;
            }
        }
        return $return_array;
    }


    /**
     * Function Name: announcement_popup_list
     * @param search_keyword int
     * @param count_only string
     * @param page_no string
     * @param page_size int
     * @param sort_by int
     * @param order_by int
     * Description: List Announcemnet popups
     */
    public function announcement_popup_list($search_keyword='', $count_flag=FALSE, $page_no, $page_size, $sort_by, $order_by)
    {
        $global_settings = $this->config->item("global_settings");
        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $this->db->select('P.AnnouncementPopupID,P.PopupTitle,P.PopupContent,P.StatusID as Status,P.CreatedBy,P.IsImageData, ,IFNULL(CONCAT(U.FirstName," ",U.LastName),"") AS CreatorName, P.CreatedDate,P.PublishedDate', FALSE);        
        $this->db->select('DATE_FORMAT(P.CreatedDate, "' . $mysql_date . '") AS CreatedDate', FALSE);
        $this->db->select('DATE_FORMAT(P.PublishedDate, "' . $mysql_date . '") AS PublishedDate', FALSE);
        $this->db->select('DATE_FORMAT(P.ModifiedDate, "' . $mysql_date . '") AS ModifiedDate', FALSE);
        $this->db->from(ANNOUCEMENTPOPUPS . ' AS P');
        $this->db->join(USERS . ' AS U', 'U.UserID = P.CreatedBy');        
        $this->db->where('P.StatusID !=', '3');        
        if (!empty($search_keyword))
        {
            $search_keyword = strtolower($search_keyword);

            $SearchStr = "( LOWER(P.PopupTitle) like '" . $this->db->escape_like_str($search_keyword) . "%'";
            $SearchStr .= ')';
            $this->db->where($SearchStr);
        }
        if ($count_flag)
        {
            $res = $this->db->get();
            return $res->num_rows();
        }

        if($sort_by=='CreatedBy')
            $this->db->order_by('CreatorName', $order_by);    
        else
            $this->db->order_by('P.' . $sort_by, $order_by);
        if (!empty($page_size))
        {
            if (empty($page_no))
            {
                $page_no = 1;
            }
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }
        $res = $this->db->get();
        //echo $this->db->last_query();die;
        return $res->result_array();        
        
    }

    /**
     * Function Name: save_announcement_popup
     * @param user_id int
     * @param popup_title string
     * @param popup_content string
     * @param status int
     * @param announcement_popup_id int
     * Description: To Add/Edit Announcemnet popups
     */
    public function save_announcement_popup($user_id,$popup_title,$popup_content,$status,$announcement_popup_id='',$is_imagedata='0')
    {
        if($announcement_popup_id)
        {
            $insert_data = array(
                'PopupTitle'=>$popup_title,
                'PopupContent'=>$popup_content,
                'StatusID'=>$status,
                'IsImageData' => $is_imagedata,
                'ModifiedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->where('AnnouncementPopupID',$announcement_popup_id);
            $this->db->update(ANNOUCEMENTPOPUPS,$insert_data);
        }
        else
        {
            $insert_data = array(
                'PopupTitle'=>$popup_title,
                'PopupContent'=>$popup_content,
                'StatusID'=>$status,
                'IsImageData' => $is_imagedata,
                'CreatedBy'=>$user_id,
                'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'),
                'PublishedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'));    
            $this->db->insert(ANNOUCEMENTPOPUPS,$insert_data);
        }
        return true;
    }

    /**
     * Function Name: save_announcement_popup          
     * @param announcement_popup_id int
     * @param status int [1->inactive,2->active,3->delete]
     * Description: To Add/Edit Announcemnet popups
     */
    public function change_status_of_popup($announcement_popup_id,$status)
    {        
        $this->db->where('AnnouncementPopupID',$announcement_popup_id);
        $this->db->update(ANNOUCEMENTPOPUPS,array('StatusID'=>$status,'ModifiedDate'=>get_current_date('%Y-%m-%d %H:%i:%s')));  
        return true;
    }

}
