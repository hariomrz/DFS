<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Description of Activity_model
 *
 * 
 */
class Activity_helper_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_user_session_details($user_id) {
        $query = $this->db->select(
                        USERS . '.UserID,' . USERS . '.UserGUID as LoggedInGUID,' . USERS . '.UserTypeID, CONCAT(' . USERS . '.FirstName," ",'
                        . USERS . '.LastName) as LoggedInName,if(' . USERS . '.ProfilePicture="","user_default.jpg",'
                        . USERS . '.ProfilePicture) as LoggedInProfilePicture, 1 AS DeviceTypeID,'
                        . '1 AS SourceID, ' . USERROLES . '.RoleID'
                        , false
                )
                ->from(USERS)
                ->join(USERROLES, USERROLES . '.UserID=' . USERS . '.UserID', 'left')
                ->where(USERS . '.UserID', $user_id)
                ->get();

        return $query->row_array();
    }

    public function setUserSessionData($UserID = 0) {
        $ctrlObj = &get_instance();

        // Check Admin is logged in
        $admin_details = $this->checkAdminLoggedIn($ctrlObj->post_data, $ctrlObj);

        if (!$UserID) {
            if(isset($ctrlObj->post_data['PostAsModuleID']) && $ctrlObj->post_data['PostAsModuleID']==3 && !empty($ctrlObj->post_data['PostAsModuleEntityGUID']))
            {
                $UserID = get_detail_by_guid($ctrlObj->post_data['PostAsModuleEntityGUID'], $ctrlObj->post_data['PostAsModuleID']);                
            }
        }

        if(empty($UserID)) {
            $userSessionDetails = $admin_details;
        } else {            
            // Get user session data.
            $userSessionDetails = $this->get_user_session_details($UserID);
        }
        $role_id = isset($userSessionDetails['RoleID']) ? $userSessionDetails['RoleID'] : '';

        $ctrlObj->LoggedInGUID = isset($userSessionDetails['LoggedInGUID']) ? $userSessionDetails['LoggedInGUID'] : '';
        $ctrlObj->UserID = $userSessionDetails['UserID'];
        $ctrlObj->RoleID = $role_id;
        $ctrlObj->LoggedInProfilePicture = isset($userSessionDetails['LoggedInProfilePicture']) ? $userSessionDetails['LoggedInProfilePicture'] : '';
        $ctrlObj->LoggedInName = isset($userSessionDetails['LoggedInName']) ? $userSessionDetails['LoggedInName'] : '';
        $ctrlObj->DeviceTypeID = isset($userSessionDetails['DeviceTypeID']) ? $userSessionDetails['DeviceTypeID'] : 1;

    }

    protected function checkAdminLoggedIn($Input, $ctrlObj) {
        $sql = '';
        /* Define variables - starts */
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        /* Define variables - ends */
        
        $headers = $ctrlObj->input->request_headers();
        
        /* Gather Inputs - starts */
        if (isset($Input['DeviceTypeID']))
            $DeviceTypeID = trim($Input['DeviceTypeID']);
        else
            $DeviceTypeID = '1';
        if (isset($Input['AdminLoginSessionKey']))
            $AdminLoginSessionKey = trim($Input['AdminLoginSessionKey']);
        else if (!empty($headers['AdminLoginSessionKey']))
            $AdminLoginSessionKey = trim($headers['AdminLoginSessionKey']);
        else 
            $AdminLoginSessionKey = '';
        /* Gather Inputs - ends */

        /* Validation - starts */
        if ($this->form_validation->required($AdminLoginSessionKey) == '') {
            $Return['ResponseCode'] = 501;
            $Return['Message'] = lang('not_authorized');
            $ctrlObj->response($Return);
        } else {
            $query = $this->db->select(
                    USERS . '.UserID,' . USERS . '.UserGUID as LoggedInGUID,' . USERS . '.UserTypeID, CONCAT(' . USERS . '.FirstName," ",'
                    . USERS . '.LastName) as LoggedInName,if(' . USERS . '.ProfilePicture="","user_default.jpg",'
                    . USERS . '.ProfilePicture) as LoggedInProfilePicture, 1 AS DeviceTypeID,'
                    . '1 AS SourceID, ' . USERROLES . '.RoleID'
                    )
                    ->from(USERS)
                    ->join(ACTIVELOGINS, ACTIVELOGINS . '.UserID=' . USERS . '.UserID', 'inner')
                    ->join(USERROLES, USERROLES . '.UserID=' . USERS . '.UserID', 'inner')
                    ->where('LoginSessionKey', $AdminLoginSessionKey)
                    //->where(USERROLES.'.RoleID',ADMIN_ROLE_ID)
                    ->get();

            //Check logged in user access right and allow/denied access
            $result = $query->row_array();
            //var_dump(in_array(getRightsId('admin_site_view'), getUserRightsData($DeviceTypeID,$result['UserID'])));die;
            if (!empty($result) && in_array(getRightsId('admin_site_view'), getUserRightsData($DeviceTypeID, $result['UserID']))) {
                $Return['Data'] = $result;
                return $result;
            } else {
                $Return['ResponseCode'] = 502;
                $Return['Message'] = lang('invalid_key');
                $ctrlObj->response($Return);
            }
        }
    }

}
