<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Description of Pages
 *
 * @author nitins
 */
class Pages extends Admin_API_Controller
{

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/pages_model', 'admin/login_model'));
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200)
        {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
        
        $this->check_module_status(18);
    }

    /**
     * Function to list organization
     * Parameters : From services.js(Angular file)
     */
    public function List_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/organizationList';
        $Return['Data'] = array();
        $Data = $this->post_data;
        if (isset($Data) && $Data != NULL)
        {
            $CurrentUser = $this->UserID;
            $PageNo = 0;
            $PageSize = PAGE_SIZE;
            $SearchText = "";
            $SortBy = "";
            $OrderBy = "";
            if (isset($Data['Begin']))
                $start_offset = $Data['Begin'];
            else
                $start_offset = 0;
            if (isset($Data['End']))
                $end_offset = $Data['End'];
            else
                $end_offset = 10;
            if (isset($Data['PageType']))
                $PageType = $Data['PageType'];
            else
                $PageType = 1;



            $SortBy = !empty($Data['SortBy']) ? $Data['SortBy'] : "P.modifieddate";
            $OrderBy = isset($Data['OrderBy']) ? $Data['OrderBy'] : "DESC";
            if (isset($Data['SearchKey']) && $Data['SearchKey'] != '')
            {
                $SearchText = $Data['SearchKey'];
            }

            $Input = array('SearchText' => $SearchText, 'UserID' => $CurrentUser, 'SortBy' => $SortBy, 'OrderBy' => $OrderBy, 'PageType' => $PageType);
            //$sportPositionlist          = $this->sport_model->sport_position_list($Input,$start_offset,$end_offset,0,FALSE,TRUE);
            $organizationList = $this->pages_model->organization_list($Input, $start_offset, $end_offset, 0, FALSE, TRUE);
            if (!empty($organizationList))
            {
                foreach ($organizationList as $key => $value) {
                    $value['Name'] = stripcslashes(ucwords($value['Title']));
                    $value['CreatedBy'] = stripcslashes(ucwords($value['UserName']));
                    $tempResults[] = $value;
                }
                $Return['Data']['results'] = $tempResults;
            }

            $Return['Data']['total_records'] = $this->pages_model->organization_list($Input, '', '', 0, TRUE, TRUE);
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function to get organization member detail
     * Parameters : From services.js(Angular file)
     */
    public function getOrganizationMemberDetail_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/getOrganizationMemberDetail';
        $Return['Data'] = array();
        $Data = $this->post_data;
        if (isset($Data) && $Data != NULL)
        {
            $Return['Data'] = $this->pages_model->get_organization_members($Data['PageID']);
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function to get users tag
     * Parameters : From services.js(Angular file)
     */
    function get_users_tags_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $Data = $this->post_data;

        $search_key = isset($Data['query']) ? $Data['query'] : '';
        $PageNo = isset($Data['PageNo']) ? $Data['PageNo'] : 1;
        $PageSize = isset($Data['PageSize']) ? $Data['PageSize'] : 6;
        $PageID = isset($Data['PageID']) ? $Data['PageID'] : '';
        $PageMembers = isset($Data['PageAddedMembers']) ? $Data['PageAddedMembers'] : '';


        /* Gather Inputs - starts */
        $creative_fields = array();
        if (!empty($search_key))
        {

            $masterData = $this->pages_model->get_users_tag($search_key, $PageNo, $PageSize, $PageID, $PageMembers);
            foreach ($masterData['Members'] as $row) {
                //$CreativeFields[] = $row['Name'];
                $creative_fields[] = array('Name' => $row['UserName'], 'UserID' => $row['UserID']);
            }
            // $CreativeFields = $masterData;
        }
        else
        {
            $creative_fields['ResponseCode'] = 500;
            $creative_fields['Message'] = lang('input_invalid_format');
        }
        $this->response($creative_fields); /* Final Output */
    }

    /**
     * Function to add users as a admin
     * Parameters : From services.js(Angular file)
     */
    function add_users_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/add_users';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {

            $this->pages_model->add_users($Data);
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function to remove users as a admin
     * Parameters : From services.js(Angular file)
     */
    function remove_users_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/remove_users';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {

            $this->pages_model->remove_users($Data);
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    function remove_page_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/remove_page';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {

            $this->pages_model->remove_page($Data);
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function change_verify_status_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/pages/change_verify_status';
        $Return['Data'] = array();
        $Data = $this->post_data;
        if (isset($Data) && $Data != NULL)
        {
            if (isset($Data['PageID']))
                $PageID = $Data['PageID'];
            else
                $PageID = 0;
            if (isset($Data['IsVerified']))
                $IsVerified = $Data['IsVerified'];
            else
                $IsVerified = 0;
            if (isset($Data['Status']))
                $Status = $Data['Status'];
            else
                $Status = '';

            //Change status query for a user
            $this->pages_model->change_verify_status($PageID, $IsVerified);
        }else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

}
