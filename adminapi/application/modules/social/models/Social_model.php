<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Social_model extends MY_Model {

	public function __construct() {
		parent::__construct();
		$this->load->database('user_db');
		//Do your magic here
	}
	
	/**
	 * [admin_login description]
	 * @param      [array]  [for login email and password]
	 * @return     [array]
	 */
	public function login($user_id) {

		$user_record = $this->db->select("user_id,user_unique_id,referral_code,phone_no,status,IFNULL(user_name,'') AS user_name,IFNULL(email,'') AS email,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,phone_verfied,bs_status")
                    ->from(USER)
                    ->where("user_id", $user_id)
                    ->limit(1)
                    ->get()
                    ->row_array();
		$key = '';
		if($user_record) {
			$key = $this->generate_active_login_key($user_record);
		}
		return $key;
	}


	/**
     * [generate_active_login_key description]
     * @return     [key]
     */
    public function generate_active_login_key($user_data, $device_type = "1", $device_id = "0") {
        $key = random_string('unique');
        $user_id = $user_data['user_id'];
        $insert_data = array(
            'key' => $key,
            'role' => 1,
            'user_id' => $user_id,
            'device_type' => $device_type,
            'date_created' => format_date()
        );

        if (!empty($device_id)) {
            $this->db->where('device_id', $device_id)->delete(ACTIVE_LOGIN);
            $insert_data['device_id'] = $device_id;
        }

        $this->db->insert(ACTIVE_LOGIN, $insert_data);

        unset($insert_data['key']);
        $this->load->model("auth/Auth_nosql_model");
        $insert_data[AUTH_KEY] = $key;
        $insert_data["user_unique_id"] = $user_data['user_unique_id'];
        $insert_data["email"] = $user_data['email'];
        $insert_data["user_name"] = $user_data['user_name'];
        $insert_data["referral_code"] = $user_data['referral_code'];
        $insert_data["phone_no"] = $user_data['phone_no'];
        $insert_data["bs_status"] = $user_data['bs_status'];
        $this->Auth_nosql_model->insert_nosql(ACTIVE_LOGIN, $insert_data);
        return $key;
    }
}

/* End of file User_model.php */
/* Location: ./application/models/User_model.php */
