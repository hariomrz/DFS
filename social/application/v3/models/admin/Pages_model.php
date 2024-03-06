<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Description of Pages_model
 *
 * @author nitins
 */
class Pages_model extends Admin_Common_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * [organization_list  used to get list of groups ]
     * @param [sring]    $SearchText         [Search Keyword]   
     * @param [int]      $Offset             [Offset]
     * @param [int]      $PageSize           [Limit]
     * @param [int]      $input              [sortby,orderby,searchkey]
     */
    function organization_list($input, $start_offset = 0, $end_offset = "", $search = 0, $count_only = 0, $all = FALSE)
    {
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);

        $this->db->select('P.PageGUID,P.UserID,P.Title,P.PageID,P.IsVerified,P.VerificationRequest');
        $this->db->select('DATE_FORMAT(P.CreatedDate, "' . $mysql_date . '") AS createddate', FALSE);
        $this->db->select('CONCAT(U.FirstName, " ", U.LastName)AS UserName', FALSE);
        $this->db->join(USERS . " AS U", ' U.UserID = P.UserID', 'left');
        $this->db->where('P.StatusID', 2);
        //$this->db->where('PageType', $input['PageType']);
        $this->db->from(PAGES . " P ");


        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset, $start_offset);

        if (isset($input['SearchText']) && $input['SearchText'] != '')
        {

            $this->db->where("(P.Title like '%" . $this->db->escape_like_str($input['SearchText']) . "%')");
        }

        // Added Sorting by type and order
        if (isset($input['SortBy']) && isset($input['OrderBy']))
        {
            if (empty($input['OrderBy']))
            {
                $input['OrderBy'] = "DESC";
            }
            else
            {
                $input['OrderBy'] = "ASC";
            }
            if ($input['SortBy'] == 'CreatedDate')
            {
                $input['SortBy'] = 'P.CreatedDate';
            }
            $this->db->order_by($input['SortBy'], $input['OrderBy']);
        }
        $query = $this->db->get();

        if ($count_only)
        {
            return $query->num_rows();
        }
        $results['total_records'] = $query->num_rows();
        $val['TotalRecords'] = $results['total_records'];

        $results = $query->result_array();
        return $results;
    }

    /**
     * [get_organization_members  used to get list of groups ]
     * @param [sring]    $PageID         [PageID]
     */
    function get_organization_members($PageID, $IsMember = 0)
    {
        $results = array();
        if ($PageID)
        {
            $this->db->where("PageMembers.PageID", $PageID);
        }

        $this->db->select('Pages.PageGUID,PageMembers.CanPostOnWall,PageMembers.PageMemeberID, PageMembers.PageID,PageMembers.ModuleRoleID,PageMembers.UserID ,Users.UserGUID, Users.FirstName,Users.UserID,Users.ProfilePicture,Users.LastName');
        $this->db->select('CONCAT(Users.FirstName, " ", Users.LastName)AS UserName', FALSE);
        $this->db->select('p.Url as ProfileLink');
        $this->db->from(PAGES);
        $this->db->join(PAGEMEMBERS, 'Pages.PageID = PageMembers.PageID', 'left');
        $this->db->join(USERS, 'PageMembers.UserID = Users.UserID', 'inner');
        $this->db->join(USERDETAILS . ' ud', 'ud.UserID = Users.UserID');
        $this->db->join(PROFILEURL . " as p", "p.EntityID = Users.UserID and p.EntityType = 'User'", "LEFT");
        $this->db->where("PageMembers.StatusID", '2');
        $sql = $this->db->_compile_select();

        // fetch creator and admin list
        if ($IsMember == 0)
        {
            $creator_query = $sql . " AND PageMembers.ModuleRoleID IN (7,8,9) ORDER BY PageMembers.PageMemeberID  ";
        }
        else
        {
            $creator_query = $sql . " AND PageMembers.ModuleRoleID IN (8,9) ORDER BY PageMembers.PageMemeberID  ";
        }

        $creator_list = $this->db->query($creator_query)->result_array();

        //fetch user list
        $user_query = $sql . " AND PageMembers.ModuleRoleID = '9' ORDER BY PageMembers.PageMemeberID  ";
        $user_list = $this->db->query($user_query)->result_array();


        $results['Creator'] = $creator_list;
        $results['Users'] = $user_list;
        return $results;
    }

    /**
     * [get_users_tag Get list of active users]
     */
    function get_users_tag($search_key, $page_no, $page_size, $page_id, $page_members)
    {

        $page_member_id = array();
        if (!empty($page_members))
        {
            foreach ($page_members as $key => $value) {
                if ($value['ModuleRoleID'] == 9)
                {
                    continue;
                }
                $page_member_id[] = $value['UserID'];
            }
        }



        $sql_condition = array();

        if (!empty($page_member_id))
        {
            $sql_condition = ('u.UserID NOT IN(' . implode(',', $page_member_id) . ')');
        }

        $this->db->select('u.FirstName, u.ProfilePicture, u.UserID, u.UserGUID, u.LastName');
        $this->db->select('CONCAT(u.FirstName, " ", u.LastName)AS UserName', FALSE);
        $this->db->from(USERS . " u");
        
        $this->db->where_not_in('u.StatusID', array(3,4));

        if (!empty($search_key))
        {
            $this->db->where("(u.FirstName like '" . $this->db->escape_like_str($search_key) . "%' or u.LastName like '" . $this->db->escape_like_str($search_key) . "%' or concat(u.FirstName,' ',u.LastName) like '" . $this->db->escape_like_str($search_key) . "%')");
        }

        $this->db->where($sql_condition);



        /* ----------- Cloning database object before adding pagination to get total count from query-------------- */
        $tempdb = clone $this->db;
        $num_results = $tempdb->count_all_results();
        /* -------------------------------------- */

        $this->db->group_by('u.UserID');

        /* --------Added pagination-------- */
        $offset = ($page_no - 1) * $page_size;
        $this->db->limit($page_size, $offset);
        /* -------------------------------------- */

        $Query = $this->db->get();

        //echo $this->db->last_query();die;
        $users = $Query->result_array();

        $module_settings = get_module_settings(true);
        $row = array();
        $i = 0;
        foreach ($users as $value) {

            unset($value['IsActive']);
            $value['ProfilePicture'] = $value['ProfilePicture'];

            $row[$i] = $value;
            unset($row[$i]['Status']);
            $i++;
        }
        $r['Members'] = $row;
        $r['TotalRecords'] = $num_results;
        return $r;
    }

    /**
     * [add_users add users as a admin]
     */
    function add_users($data)
    {

        foreach ($data['Tags'] as $key => $value) {

            // Update Follower count in follow table by 1
            $this->db->where('PageID', $data['PageID']);
            $this->db->set('NoOfFollowers', 'NoOfFollowers+1', FALSE);
            $this->db->update(PAGES);
            $this->load->model('pages/page_model');
            //remove old relation
            $this->db->where('PageID', $data['PageID']);
            $this->db->where('UserID', $value['UserID']);
            $this->db->delete(PAGEMEMBERS);
            //addd new relation
            $this->db->insert(PAGEMEMBERS, array('PageID' => $data['PageID'], 'UserID' => $value['UserID'], 'ModuleRoleID' => 8, 'StatusID' => 2));
        }
    }

    /**
     * [remove_user remove user from an organization]
     */
    function remove_users($data)
    {


        if ($data['ModuleRoleID'] == 7)
        {
            $PageMembers = $this->get_organization_members($data['PageID'], 1);

            $creator_data = array('ModuleRoleID' => 7);
            $this->db->where('UserID', $PageMembers['Creator'][0]['UserID']);
            $this->db->where('PageID', $data['PageID']);
            $this->db->update(PAGEMEMBERS, $creator_data);
        }
        $page_member_data = array('StatusID' => 3);
        $this->db->where('UserID', $data['UserID']);
        $this->db->where('PageID', $data['PageID']);
        $this->db->update(PAGEMEMBERS, $page_member_data);
        return true;
    }

    /**
     * [remove_user remove user from an organization]
     */
    function remove_page($data)
    {
        $page_member_data = array('StatusID' => 3);
        $this->db->where('PageID', $data['PageID']);
        $this->db->update(PAGES, $page_member_data);
        return true;
    }

    /**
     * Function for change Verification status
     * Parameters : $user_id
     * Return : true
     */
    public function change_verify_status($PageID, $IsVerified)
    {
        $data = array('IsVerified' => $IsVerified);
        $this->db->where('PageID', $PageID);
        $this->db->update(PAGES, $data);
        return true;
    }

}
