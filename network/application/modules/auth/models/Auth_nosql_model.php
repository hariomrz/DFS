<?php

class Auth_nosql_model extends NOSQL_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * for store and validate otp in db
     * @param int $phone_no
     * @param boolean $send_email_otp
     * @param int $is_bot
     * @return string
     */
    public function send_otp($data, $send_email_otp = FALSE, $is_bot = 0) {
        if (!empty($data['phone_no'])) {
            $phone_no = $data['phone_no'];
        } else {
            $phone_no = $data['email'];
        }

        $current_date = format_date();
        $otpData = array();
        if (isset($is_bot) && $is_bot == 1) {
            $otp = "1234";
        } else {
            $otp = random_string('nozero', OTP_LENGTH);
        }

        $otpData["otp_code"] = (int) ($phone_no == DEMO_USER_PHONE_NO) ? DEMO_USER_OTP : $otp;
        $otpData["phone_no"] = $phone_no;
        $otpData["user_detail"] = isset($data['user_detail']) ? $data['user_detail'] : array();
        $otpData["created_date"] = $current_date;
        $otpData["updated_at"] = $current_date;
        $condition = array("phone_no" => $phone_no);
        if ($send_email_otp) {
            $otpData["type"] = "2"; //if email verification using email OTP.
        }
        //Check if phone number exists
        $record = $this->select_one_nosql(MANAGE_OTP, $condition);
        if (empty($record)) {
            $this->insert_nosql(MANAGE_OTP, $otpData);
        } else {
            unset($otpData['phone_no']);
            unset($otpData['created_date']);
            $now = strtotime($current_date);
            $created_date = $record['created_date'];
            $future_time = strtotime($created_date . ' +15 minutes');
            if ($future_time > $now) {
                $otp = $record['otp_code'];
                $this->update_nosql(MANAGE_OTP, array('phone_no' => $phone_no), array('updated_at' => $current_date));
            } else {
                $this->update_nosql(MANAGE_OTP, array('phone_no' => $phone_no), $otpData);
            }
        }
        return $otp;
    }
}