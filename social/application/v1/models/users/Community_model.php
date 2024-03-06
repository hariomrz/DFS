<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Community_model extends Common_Model {

    /**
     * [SetSession used to set user session]
     * @param [type] $output [user details]
     */
    public function setSession($existing_session_data) {    //print_r($existing_session_data); die;

        if(!empty($existing_session_data['Api_Email'])) {
            $data['Email'] = isset($existing_session_data['Api_Email']) ? $existing_session_data['Api_Email'] : '';
        } else {
            $data['Email'] = isset($existing_session_data['Email']) ? $existing_session_data['Email'] : '';
        }
        
        
        
        
        $data['UserSocialID'] = '';
        $data['DeviceID'] = DEFAULT_DEVICE_ID;
        $data['Latitude'] = '';
        $data['Longitude'] = '';
        $data['IPAddress'] = $_SERVER['REMOTE_ADDR'];
        
        $data['Username'] = '';
        $data['Password'] = '';
        $data['Resolution'] = DEFAULT_RESOLUTION;
        $data['Picture'] = '';
        $data['Token'] = '';
        $data['DeviceToken'] = '';
        $data['profileUrl'] = '';
        $data['SourceID'] = 1;
        $data['UserID'] = '';
        $data['AutoLogin'] = true;
        $data['DeviceTypeID'] = $this->DeviceTypeID;
        $UserData = $this->login_model->verify_login($data);

        if (isset($UserData['ResponseCode']) && $UserData['ResponseCode'] == 200) {
            $output = json_decode(json_encode($UserData));
            //$this->SetSession($output);
        } else {
            return;
        }

        $ctrl_obj = get_instance();

        $ctrl_obj->session->set_userdata('LoginSessionKey', $output->Data->LoginSessionKey);
        $ctrl_obj->session->set_userdata('UserID', $output->Data->UserID);
        $ctrl_obj->session->set_userdata('UserGUID', $output->Data->UserGUID);
        $ctrl_obj->session->set_userdata('FirstName', $output->Data->FirstName);
        $ctrl_obj->session->set_userdata('LastName', $output->Data->LastName);
        $ctrl_obj->session->set_userdata('Email', $output->Data->Email);
        //$ctrl_obj->session->set_userdata('LoginKeyword', $output->Data->LoginKeyword);
        $ctrl_obj->session->set_userdata('ProfilePicture', $output->Data->ProfilePicture);
        $ctrl_obj->session->set_userdata('TimeZoneOffset', $output->Data->TimeZoneOffset);
        $ctrl_obj->session->set_userdata('UserStatusID', $output->Data->StatusID);
        $ctrl_obj->session->set_userdata('language', $output->Data->Language);

        $ctrl_obj->session->set_userdata('community_logged_in_user', $data['Email']);


        if ($output->Data->FirstName != '') {
            $DisplayName = $output->Data->FirstName;
            if ($output->Data->LastName != '') {
                $DisplayName .= " " . $output->Data->LastName;
            }
        } else {
            $DisplayName = $output->Data->Email;
        }
        $ctrl_obj->session->set_userdata('DisplayName', $DisplayName);
    }

    public function isAllowedUrl() {        //return;
        $url = $this->getUrl();

        $allowed_urls = array(
            '',
            'forum'
        );

        $regexs = array(
            'forum/*/*',
            '*'
        );

        $not_allowed_urls = array(
            'pages',
            'events',
            'group',
            'wiki',
            'poll',
            'dashboard'
        );

        $not_allowed_regexs = array(
            'page/*',
            'pages/*',
            'events/*',
            'group/*',
            'article/*',
            'poll/*',
        );

        if (in_array($url, $not_allowed_urls)) {
            redirect(site_url(''));
        }

        foreach ($not_allowed_regexs as $regex) {
            if (fnmatch($regex, $url)) {
                redirect(site_url(''));
            }
        }


//        if(!in_array($url, $allowed_urls)) {
//            
//            // Check if any url is available
//            foreach($regexs as $regex) {
//                if(fnmatch($regex, $url)) {
//                    return;
//                }
//            }
//            
//            redirect(site_url(''));
//        }
    }

    public function isAllowedApi() {

        $url = $this->getUrl();

        $allowed_urls = array(
            'pages',
            'events',
            'group',
            'wiki',
            'poll',
            'dashboard'
        );

//        $not_allowed_regexs = array(
//            'page/*',
//            'pages/*',
//            'events/*',
//            'group/*',
//            'article/*',
//            'poll/*',
//            
//        );

        if (!in_array($url, $allowed_urls)) {
            //die;
        }

//        foreach($not_allowed_regexs as $regex) {
//            if(fnmatch($regex, $url)) {
//                redirect(site_url(''));
//            }
//        }
    }

    private function getUrl() {
        $this->load->helper('url');
        $base_url = base_url();
        $current_url = current_url();
        $url = str_replace($base_url, '', $current_url);

        return $url;
    }

    public function set_user_session_by_api_key($data) {

        $remote_ip = $this->getRealIpAddr(); //echo $remote_ip; die;
        $sent_key = isset($data['Api_Key']) ? $data['Api_Key'] : '';


        $allowed_ips = array(
            '::1',
            '127.0.0.1'
        );

        $allowed_keys = array(
            '123456'
        );

        if (!in_array($remote_ip, $allowed_ips)) {
            return false;
        }

        if (!in_array($sent_key, $allowed_keys)) {
            return false;
        }

        $this->setSession($data);

        $ctrl_obj = get_instance();
        $ctrl_obj->IsApp = 0;
        $ctrl_obj->UserID = $ctrl_obj->session->UserID;
        $ctrl_obj->LoggedInGUID = $ctrl_obj->session->LoggedInGUID;
        $ctrl_obj->LoggedInName = $ctrl_obj->session->LoggedInName;
        $ctrl_obj->LoggedInProfilePicture = $ctrl_obj->session->LoggedInProfilePicture;
        $ctrl_obj->DeviceTypeID = $ctrl_obj->session->DeviceTypeID;
        $ctrl_obj->SourceID = $ctrl_obj->session->SourceID;
        $ctrl_obj->RoleID = $ctrl_obj->session->RoleID;
        $ctrl_obj->RightIDs = $ctrl_obj->login_model->get_user_rights($ctrl_obj->session->UserID);

        return true;
    }

    function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Function for change status of a user
     * Parameters : UserID, Status
     * Return : true
     */
    public function changeStatus($user_id, $status) {
        $data = array('StatusID' => $status, 'ModifiedDate' => date('Y-m-d H:i:s'));


        $this->db->where('UserID', $user_id);



        $this->db->update(USERS, $data);

        if ($status == 3) {
            $data = array('StatusID' => $status, 'ModifiedDate' => date('Y-m-d H:i:s'));


            $this->db->where('EntityID', $user_id);


            $this->db->where('EntityType', 'User');
            $this->db->update(PROFILEURL, $data);
        }

        /* added by gautam starts */
        if ($status == 4) {
           
                $this->db->delete(ACTIVELOGINS, array('UserID' => $user_id));
            
        }
        /* added by gautam ends */
        return true;
    }
    
    
    
    public function get_user_id_by_email($email) {
        $this->db->select('UserID');
        $this->db->where("Email", $email);
        $query = $this->db->get(USERS);
        $result = $query->row_array();
        
        if(!empty($result['UserID'])) {
            return $result['UserID'];
        }
        
        return 0;
    }
    
    public function get_email_by_user_id($user_id) {
        $this->db->select('Email');
        $this->db->where("UserID", $user_id);
        $query = $this->db->get(USERS);
        $result = $query->row_array();
        
        if(!empty($result['Email'])) {
            return $result['Email'];
        }
        
        return '';
    }

}

?>
