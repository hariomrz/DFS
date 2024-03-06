<?php

class Common_Model extends CI_Model {
    public $_tablePrefix = "";
    public $suggested_users;
    function __construct() {
        parent::__construct();
        $this->_tableprefix = $this->db->dbprefix;
    }
    
    function __destruct() {
        $this->db->close();
    }


    /**
     * [get_single_row description]
     * @param  [string] $select [select field]
     * @param  [string] $table  [Table name]
     * @param  [array] $where  [where condition]
     * @return [array]         [description]
     */
    function get_single_row($select = '*', $table, $where = "") {
        $this->db->select($select);
        $this->db->from($table);
        if ($where != "") {
            $this->db->where($where);
        }
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * [insert Used to insert data and retrun ID]
     * @param  [string] $table_name [table name]
     * @param  [array]  $data       [data]
     * @return [int]                [Inserted ID]
     */
    function insert($table_name, $data) {
        $this->db->insert($table_name, $data);
        $id = $this->db->insert_id();
        return $id;
    }

    function update_row($table, $where, $data) {
        $this->db->where($where);
        $this->db->update($table, $data);
    }

    function delete_row($table, $where) {
        $this->db->where($where);
        $this->db->delete($table);
    }

    /**
     * [set_remember_me Used to set remember me cookie]
     * @param [int] $user_id [User ID]
     */
    function set_remember_me($user_id) {
        $cookie = array(
            'name' => 'remember_me',
            'value' => $user_id,
            'expire' => '7776000'  // 90 days expiration time
        );
        $this->input->set_cookie($cookie);
    }

    /**
     * [remember_me Used to check remember me cookie]
     * @return [type] [description]
     */
    function check_remember_me() {
        if ($this->input->cookie('remember_me')) {
            $user_id = $this->input->cookie('remember_me');
            $result = $this->get_user_data($user_id);
            $user_session = array();
            $LoginSessionKey = random_string('unique', 8);
            if (!empty($result)) {
                if ($result['StatusID'] == 0) {
                    die;
                    $user_session = array('inactive_user_id' => $result['UserID'], 'inactive_name' => $result['FirstName'], 'inactive_email' => $result['Email']);
                } else {
                    $user_session['UserID'] = $result['UserID'];
                    $user_session['LoginSessionKey'] = $LoginSessionKey;
                    $user_session['UserGUID'] = $result['UserGUID'];
                    $user_session['FirstName'] = $result['FirstName'];
                    $user_session['LastName'] = $result['LastName'];
                    $user_session['Email'] = $result['Email'];
                }

                $this->db->where(array('UserID' => $result['UserID']));
                $this->db->limit(1);
                $this->db->delete(ACTIVELOGINS);

                $user_login_data = array();
                $user_login_data['LastLoginDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $this->db->where('UserID', $result['UserID']);
                $this->db->update(USERS, $user_login_data);

                $this->db->insert(ACTIVELOGINS, array('UserID' => $result['UserID'], 'LoginSessionKey' => $LoginSessionKey, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
                $this->session->set_userdata($user_session);
            }
        }
    }

    /**
     * [get_device_type_id Used to get device type ID]
     * @param  [string] $device_type  [Device Type]
     * @return [int]                 [Device type ID]
     */
    function get_device_type_id($device_type) {
        $device_type_id = '';

        $this->db->select('DeviceTypeID');
        $this->db->where('Name', $device_type);
        $this->db->limit('1');
        $query = $this->db->get(DEVICETYPES);

        if ($query->num_rows() > 0) {
            $data = $query->row_array();
            $device_type_id = $data['DeviceTypeID'];
        } else {
            $device_type = DEFAULT_DEVICE_TYPE;
            $this->db->select('DeviceTypeID');
            $this->db->where('Name', $device_type);
            $this->db->limit('1');
            $query = $this->db->get(DEVICETYPES);
            if ($query->num_rows() > 0) {
                $data = $query->row_array();
                $device_type_id = $data['DeviceTypeID'];
            }
        }
        return $device_type_id;
    }

    /**
     * [get_source_id Used to get source ID]
     * @param  [string] $source_type [source type]
     * @return [int]                 [source ID]
     */
    function get_source_id($source_type) {
        $source_id = '';
        $this->db->select('SourceID');
        $this->db->where('Name', $source_type);
        $this->db->limit('1');
        $query = $this->db->get(SOURCES);

        if ($query->num_rows() > 0) {
            $data = $query->row_array();
            $source_id = $data['SourceID'];
        }
        return $source_id;
    }

    /**
     * [get_resolution_id Used to get resolution ID]
     * @param  [string] $resolution [resolution]
     * @return [int]             [resolution ID]
     */
    function get_resolution_id($resolution) {
        $resolution_id = '';
        $this->db->select('ResolutionID');
        $this->db->where('Name', $resolution);
        $this->db->limit('1');
        $query = $this->db->get(RESOLUTION);
        if ($query->num_rows() > 0) {
            $data = $query->row_array();
            $resolution_id = $data['ResolutionID'];
        } else {
            $resolution = DEFAULT_RESOLUTION;
            $this->db->select('ResolutionID');
            $this->db->where('Name', $resolution);
            $this->db->limit('1');
            $query = $this->db->get(RESOLUTION);
            if ($query->num_rows() > 0) {
                $data = $query->row_array();
                $resolution_id = $data['ResolutionID'];
            }
        }
        return $resolution_id;
    }

    /**
     * Function Name: get_user_session_history
     * @param UserId
     * Description: Check user session history
     */
    function get_user_session_history($UserId) {
        $this->db->select("*");
        $this->db->from(USERSESSIONHISTORY);
        $this->db->where('UserID', $UserId);
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
      |--------------------------------------------------------------------------
      | Use get right of the  roles
      |@Inputs: (Defined in user role DB Table)
      |--------------------------------------------------------------------------
     */

    function get_role_rights($RoleID) {
        $query = $this->db->select('Roles.Name ,Rights.Name, Rights.Description')
                ->from('Roles')
                ->join('RoleRights', 'Roles.RoleID=RoleRights.RoleID', 'left')
                ->join('Rights', 'RoleRights.RightID=Rights.RightID', 'right')
                ->where('Roles.RoleID', $RoleID)
                ->where('Rights.IsActive', '1')
                ->get();
        return $query->result_array();
    }

    /*
      |--------------------------------------------------------------------------
      | Use get role of the  roles
      |@Inputs: (Defined in user role DB Table)
      |--------------------------------------------------------------------------
     */

    function get_user_role($userid) {
        $query = $this->db->select('Roles.Name')
                ->from('Roles')
                ->join('UserRoles', 'UserRoles.RoleID=Roles.RoleID')
                ->where('UserRoles.UserID', $userid)
                ->get();
        return $query->result_array();
    }

    /**
     * Function Name: get_user_data
     * @param user_id
     * Description: Get User Data
     */
    public function get_user_data($user_id) {
        $rs = $this->db->select('UserID, UserGUID, StatusID, FirstName, LastName, Email')->where('UserID', $user_id)->limit('1')->get(USERS);
        return $rs->row_array();
    }

    /**
     * [get_profile_link description]
     * @param  [int] $UserID [User id]
     * @return [string]         [profile url]
     */
    function get_profile_link($UserID) {
        $query = $this->db->select(USERS . '.UserGUID,' . USERDETAILS . '.ProfileName, ' . PROFILEURL . '.Url')
                ->from(USERS)
                ->join(USERDETAILS, USERS . '.UserID=' . USERDETAILS . '.UserID', 'left')
                ->join(PROFILEURL, USERS . ".UserID=" . PROFILEURL . ".EntityID AND " . PROFILEURL . ".EntityType='User'", 'left')
                ->where(USERS . '.UserID', $UserID)
                ->limit('1')
                ->get();
        if ($query->num_rows()) {
            $result = $query->row();
            if ($result->Url) {
                return $result->Url;
            } else if ($result->ProfileName == '') {
                return $result->UserGUID;
            } else {
                return $result->ProfileName;
            }
        } else {
            return '';
        }
    }

    /**
     * [get_pagination_offset Used to calculate pagination offset]
     * @param  [int] $PageNo [current page number]
     * @param  [int] $Limit  [number of records]
     * @return [int]         [pagination offset]
     */
    function get_pagination_offset($PageNo, $Limit) {
        if (empty($PageNo)) {
            $PageNo = 1;
        }
        $offset = ($PageNo - 1) * $Limit;
        return $offset;
    }

    /**
     * [get_week_day_id Used to get week day id]
     * @param  [string] $day [day name]
     * @return [int]         [day id]
     */
    function get_week_day_id($day) {
        $this->db->where('Name', $day);
        $this->db->limit('1');
        $week = $this->db->get(WEEKDAYS);
        if ($week->num_rows()) {
            $result = $week->row();
            return $result->WeekdayID;
        }
        return '';
    }

    /**
     * [get_extension_id Get Extension ID from Extension]
     * @param  [string] $ext [Extension]
     * @return [int]        [Extension ID]
     */
    function get_extension_id($extension) {
        $this->db->where('Name', $extension);
        $this->db->limit('1');
        $query = $this->db->get(MEDIAEXTENSIONS);
        if ($query->num_rows()) {
            $result = $query->row();
            return $result->MediaExtensionID;
        }
        return 0;
    }

    /**
     * [is_user_activated Check if user is activated or not]
     * @param  [string]  $login_session_key   [String]
     * @return boolean                      [true/false]
     */
    function is_user_activated($login_session_key) {
        $query = $this->db->select('*')
                ->from(USERS)
                ->join(ACTIVELOGINS, ACTIVELOGINS . '.UserID=' . USERS . '.UserID', 'left')
                ->where(ACTIVELOGINS . '.LoginSessionKey', $login_session_key)
                ->where_in(USERS . '.StatusID', array(1, 2, 6, 7))
                ->limit('1')
                ->get();
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [get_time_slot Get current timeslot]
     * @return [int] [timeslot id]
     */
    function get_time_slot() {
        $d = date('H i');
        $d = explode(" ", $d);
        $min = $d[1] / 60;
        $dec = $d[0] + $min;

        $query = $this->db->query("SELECT TimeSlotID FROM TimeSlots WHERE '" . $dec . "' BETWEEN ValueRangeFrom AND ValueRangeTo");
        if ($query->num_rows()) {
            return $query->row()->TimeSlotID;
        } else {
            return '';
        }
    }

    /**
     * [set_profile_url insert/update profile URL]
     * @param [array] $profileUrlData [profile Url Data array]
     */
    function set_profile_url($profile_url_data) {
        if (!empty($profile_url_data)) {
            $this->db->insert_on_duplicate_update_batch(PROFILEURL, $profile_url_data);
        }
    }

    /**
     * [check_profile_url used to check profile URL]
     * @param [string] $url [profile Url Data array]
     */
    function check_profile_url($url) {
        if (!empty($url)) {
            $this->db->select('*');
            $this->db->from(PROFILEURL);
            $this->db->where('Url', $url);
            $this->db->where('StatusID', '2');
            $this->db->limit(1);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->row_array();
            } else {
                $this->db->select("UserID as EntityID, 'User' as EntityType, StatusID");
                $this->db->from(USERS);
                $this->db->where('UserGUID', $url);
                $this->db->where_not_in('StatusID', array(3, 4));
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    return $query->row_array();
                }
                return false;
            }
        }
    }

    /**
     * [get_remote_file_size Used get file size]
     * @param  [string] $url [file url]
     * @return [double]      [file size]
     */
    function get_remote_file_size($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }

    function get_total_records() {
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        return $temp_q->num_rows();
    }

    /**
     * [delete_mongo_db_record delete record from mongo db]
     * @param  [string] $collection_name  [collection name]
     * @param [$condition]           [array of condition]
     */    
    function delete_mongo_db_record($collection_name, $condition) {
        $this->load->library('mongo_db');
        $this->mongo_db->where($condition)->delete($collection_name);
    }
    
    /**
     * [upload_api_data_on_bucket upload api data on bucket]
     * @param  [string] $file_name  [file name]
     * @param [$data_arr]           [api data]
     */
    public function upload_api_data_on_bucket($file_name, $data_arr){
        if(empty(BUCKET_STATIC_DATA_ALLOWED) || empty($file_name)){
            return false;
        }
        try {
            $json_data = json_encode($data_arr);
            $this->load->library('S3');
            $s3_credential = array("access_key"=>AWS_ACCESS_KEY,"secret_key"=>AWS_SECRET_KEY,"region"=>BUCKET_ZONE,"use_ssl"=>false,"verify_peer"=>true);
            $s3 = new S3($s3_credential);
            $file_path = BUCKET_STATIC_DATA_PATH.BUCKET_DATA_PREFIX.$file_name;
            
            $json_file_path = PATH_IMG_UPLOAD_FOLDER.$file_name;
            $new_json = fopen($json_file_path, "w");
            fwrite($new_json, $json_data);
            fclose($new_json);
            $is_s3_upload = $s3->putObjectFile($json_file_path, BUCKET, $file_path, S3::ACL_PUBLIC_READ);
            @unlink($json_file_path);
            if($is_s3_upload) {
                //log_message("error", "Upload api data on bucket done");
                return true;
            } else {
                //log_message("error", "Upload api data on bucket failed");
                return false;
            }
        } catch (Exception $e) {
            log_message("error", "Unable to upload api data on bucket: {$e->getMessage()}");
        }
    }
    
    /**
     * [delete_api_static_file delete api data static file from bucket]
     * @param  [string] $file_name  [file name]
     */
    function delete_api_static_file($file_name) {
        if(empty(BUCKET_STATIC_DATA_ALLOWED) || empty($file_name)){
            return false;
        }
        $this->load->library('S3');
        $s3_credential = array("access_key"=>AWS_ACCESS_KEY,"secret_key"=>AWS_SECRET_KEY,"region"=>BUCKET_ZONE,"use_ssl"=>false,"verify_peer"=>true);
        $s3 = new S3($s3_credential);
        $file_path = BUCKET_STATIC_DATA_PATH.BUCKET_DATA_PREFIX.$file_name.'.json';
        if($s3->getObjectInfo(BUCKET, $file_path)) {
            // delete file
            $s3->deleteObject(BUCKET, $file_path);                
        }
    }
    
} // Class close
