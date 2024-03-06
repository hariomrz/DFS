<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Popup_model extends Common_Model
{
    public function __construct()
    {
        parent::__construct();        
    }

    /**
     * Function Name: announcement_popup_list
     * @param search_keyword int
     * @param count_only string
     * @param page_no string
     * @param page_size int
     * @param sort_by int
     * @param order_by int
     * @param is_frontend boolean
     * @param user_id current user
     * Description: List Announcemnet popups
     */
    public function announcement_popup_list($search_keyword='', $count_flag=FALSE, $page_no, $page_size, $sort_by, $order_by,$is_frontend=false,$user_id=0)
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
        if($is_frontend)
        {
            $this->db->where("P.AnnouncementPopupID NOT IN (SELECT AnnouncementPopupID FROM ".USERIGNOREDPOPUPS." WHERE UserID='".$user_id."' AND AnnouncementPopupID=P.AnnouncementPopupID)",null,false);
            $this->db->where('P.StatusID', '2'); 

        }
        else
        {
            $this->db->where('P.StatusID !=', '3');                    
        }
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
        // echo $this->db->last_query();die;
        return $res->result_array();        
        
    }

    /**
     * Function Name: skip_announcement_popup          
     * @param announcement_popup_id int
     * @param user_id int     
     * Description: To skip Announcemnet popup for a user
     */
    public function skip_announcement_popup($announcement_popup_id,$user_id)
    {
        if($announcement_popup_id && $user_id)
        {
            $insert_data = array(
                'AnnouncementPopupID'=>$announcement_popup_id,
                'UserID'=>$user_id,
                'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'));            
            $this->db->insert(USERIGNOREDPOPUPS,$insert_data);
        }
        return true;
    }
}
    