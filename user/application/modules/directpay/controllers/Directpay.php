<?php

class Directpay extends Common_Api_Controller
{

    public $pg_id = 27;
    public $success_code = array('SUCCESS', 'success');
    public $fail_code = array('FAILED', 'failed');
    public $pending_code = array('PENDING', 'pending');
    public $order_prefix = '';
    public $mode = "";
    public $appId = "";
    public $secretKey = "";
    public $currency = "";

    function __construct()
    {
        parent::__construct();

        if (!$this->app_config['allow_directpay']['key_value']) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, directpay not enabled. please contact admin.";
            $this->api_response();
        }
        $this->finance_lang = $this->lang->line("finance");
        $this->order_prefix = isset($this->app_config['order_prefix']) ? $this->app_config['order_prefix']['key_value'] : '';
        $this->mode = $this->app_config['allow_directpay']['custom_data']['mode'];
        $this->appId = $this->app_config['allow_directpay']['custom_data']['app_id'];
        $this->secretKey = $this->app_config['allow_directpay']['custom_data']['secret_key'];
        $this->currency = $this->app_config['currency_abbr']['key_value'];

    }

    /**
     * @Method deposit_post
     * @uses deposti money using ifantasy
     * @since December 2021
     * *** */
    public function deposit_post()
    {
        $this->load->helper('form');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|callback_decimal_numeric|callback_validate_deposit_amount');
        $this->form_validation->set_rules('furl', 'furl', 'required');
        $this->form_validation->set_rules('surl', 'surl', 'required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();
        $user_id = $this->user_id;
        $surl = $post_data['surl'];
        $furl = $post_data['furl'];
        $product_info = SITE_TITLE . ' deposit via directpay';
        $email = isset($this->email) ? $this->email : 'user@vinfotech.com';
        $phoneno = isset($this->phone_no) ? $this->phone_no : '1234567890';
        $firstname = isset($this->user_name) ? $this->user_name : 'User';
        $amount = $post_data['amount'];
        $amount = number_format($amount, 2, '.', '');
        $this->load->model("finance/Finance_model");
        $user_info = $this->Finance_model->get_single_row('first_name,last_name', USER, array('user_id' => $user_id));
        $promo_code = array();
        if (!empty($post_data['promo_code'])) {
            $promo_code = $this->validate_promo($post_data);
        }

        $deal = array();
        if (!empty($post_data['deal_id'])) {
            $deal = $this->validate_deal($post_data);
        }
        $_POST['payment_gateway_id'] = $this->pg_id;
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $product_info, $surl, $furl, $promo_code, $deal);
        $txnid = $this->order_prefix . $txnid;

        $req_data = array(
            "merchant_id" => $this->appId,
            "amount" => number_format(($amount * 100), "2", ".", ""),
            "type" => 'ONE_TIME',
            "order_id" => $txnid,
            "currency" => $this->currency,
            "return_url" => USER_API_URL . 'directpay/callback',
            "response_url" => USER_API_URL . 'directpay/callback',
            "first_name" => $user_info['first_name'],
            "last_name" => $user_info['last_name'],
            "phone" => $phoneno,
            "email" => $email,
            "page_type" => 'IN_APP',
            "logo" => "https://play-lh.googleusercontent.com/ILppXH03AzHGYdVBluPtMWjBjEk-miJp04Os1PZAqz9egt4Ol6vdDHS5ZoX2oUYEvIU=w240-h480-rw",
        );
        $encode_payload = base64_encode(json_encode($req_data));
        $signature = hash_hmac('sha256', $encode_payload, $this->secretKey);
        $req_data['stage'] = $this->mode;
        $req_data['encoded_payload'] = $encode_payload;
        $req_data['signature'] = $signature;
        $this->api_response_arry['data'] = $req_data;
        $this->api_response();
    }

    /**
     * call back function
     */
    public function callback_post()
    {
        $furl = FRONT_APP_PATH;
        $post_data = $this->input->post();
        // $post_data = "eyJjaGFubmVsIjoiTUFTVEVSQ0FSRCIsInR5cGUiOiJPTkVfVElNRSIsIm9yZGVyX2lkIjoiVklORk8xODA2MyIsInRyYW5zYWN0aW9uX2lkIjoiMTQ2NTI1Iiwic3RhdHVzIjoiU1VDQ0VTUyIsImNhcmRfaWQiOm51bGwsImRlc2NyaXB0aW9uIjpudWxsLCJjYXJkX21hc2siOiI1NTU1NTV4eHh4eHgwMDE4IiwiY3VzdG9tZXIiOnsibmFtZSI6InJhdGhvcmVha2hpbGVzaDc0MSByYXRob3JlYWtoaWxlc2g3NDEiLCJlbWFpbCI6ImFraGlsZXNoLnJhdGhvcmVAdmluZm90ZWNoLmNvbSIsIm1vYmlsZSI6Ijg5ODk0NDg2NjgifSwidHJhbnNhY3Rpb24iOnsiaWQiOiIxNDY1MjUiLCJzdGF0dXMiOiJTVUNDRVNTIiwiZGVzY3JpcHRpb24iOiJBcHByb3ZlZCIsIm1lc3NhZ2UiOiJTVUNDRVNTIiwiYW1vdW50IjoiMzAwLjAwIiwiY3VycmVuY3kiOiJMS1IiLCJwcm9tb3Rpb25fYW1vdW50IjpudWxsfSwicHJvbW90aW9uIjp7ImFwcGx5IjpmYWxzZSwibmFtZSI6IiIsInBlcmNlbnRhZ2UiOiIiLCJzdGFydF9kYXRlIjoiIiwiZW5kX2RhdGUiOiIiLCJwYXlfdHlwZSI6IiIsImN1cnJlbmN5IjoiIiwiYmFua19uYW1lIjoiIiwiY2FyZF90eXBlIjoiIiwidHlwZSI6IiJ9fQ==";
        if (LOG_TX) {
            error_log("\n\n".format_date().'### 1'.json_encode($post_data),3,'/var/www/html/cron/application/logs/log.log');
        }
        $post_data = json_decode(base64_decode($post_data[0]), true);
        // print_r($post_data);die;
        if (empty($post_data) || !isset($post_data['order_id'])) {
            redirect($furl, 'location', 301);
        }
        // print_r($post_data);exit;
        $this->load->model("finance/Finance_model");
        $order_id = substr($post_data['order_id'], 5);
        $txn_info = $this->Finance_model->get_single_row('*', TRANSACTION, array('transaction_id' => $order_id));
        if (empty($txn_info)) {
            redirect($furl, 'location', 301);
        } else if ($txn_info['transaction_status'] == "1") {
            redirect($txn_info['surl'], 'location', 301);
        } else if ($txn_info['transaction_status'] == "2") {
            redirect($txn_info['furl'], 'location', 301);
        }

        if (isset($post_data['transaction']['id']) && in_array(strtoupper($post_data['transaction']['status']), $this->success_code) && strtoupper($post_data['transaction']['description']) == 'APPROVED') {
            $txnid = $txn_info['transaction_id'];

            $post_data['transaction']['amount'] = number_format(($post_data['transaction']['amount'] / 100), 2, '.', '');
            $transaction_details = $this->Finance_model->get_transaction_info($txnid);

            if ($post_data['transaction']['amount'] == number_format($transaction_details['real_amount'], 2, '.', '')) {
                $req_data = array(
                    "merchant_id" => $this->appId,
                    "order_id" => $post_data['order_id'],
                );
                $encode_payload = base64_encode(json_encode($req_data));
                $signature = hash_hmac('sha256', $encode_payload, $this->secretKey);
                $response_data = get_directpay_txn_status($encode_payload, $signature,$this->mode);
                $response_data = json_decode($response_data, true);
                if ($response_data['status'] == 200) {
                    $response_data = $response_data['data']['transaction'];
                    if (in_array(strtoupper($response_data['status']), $this->success_code)) {
                        $transaction_record = $this->_update_tx_status($order_id, 1, $response_data);
                        redirect($transaction_record['surl'], 'location', 301);
                    }else{
                        $transaction_record = $this->_update_tx_status($order_id, 2, $response_data);
                        redirect($txn_info['furl'], 'location', 301);
                    }
                } else {
                    $transaction_record = $this->_update_tx_status($order_id, 2, $response_data);
                    redirect($txn_info['furl'], 'location', 301);
                }
            } else {
                $transaction_record = $this->_update_tx_status($order_id, 2, []);
                redirect($txn_info['furl'], 'location', 301);
            }
        }
        else {
            $transaction_record = $this->_update_tx_status($txn_info['transaction_id'], 2, $post_data);
            redirect($transaction_record['furl'], 'location', 301);
        }
    }

    public function callback_get()
    {
        $furl = FRONT_APP_PATH;
        $post_data = $this->input->get();
        if (LOG_TX) {
        }
        if (empty($post_data) || !isset($post_data['orderId'])) {
            redirect($furl, 'location', 301);
        }
        $this->load->model("finance/Finance_model");
        $order_id = substr($post_data['orderId'], 5);
        $txn_info = $this->Finance_model->get_single_row('*', TRANSACTION, array('transaction_id' => $order_id));
        if (empty($txn_info)) {
            redirect($furl, 'location', 301);
        } else if ($txn_info['transaction_status'] == "1") {
            redirect($txn_info['surl'], 'location', 301);
        } else if ($txn_info['transaction_status'] == "2") {
            redirect($txn_info['furl'], 'location', 301);
        }
        if (isset($post_data['status']) && in_array($post_data['status'], $this->fail_code)) {
            $transaction_record = $this->_update_tx_status($order_id, 2, $post_data);
            redirect($txn_info['furl'], 'location', 301);
        } elseif (isset($post_data['status']) && in_array($post_data['status'], $this->success_code)) {
            $req_data = array(
                "merchant_id" => $this->appId,
                "order_id" => $this->order_prefix . $order_id,
            );
            $encode_payload = base64_encode(json_encode($req_data));
            $signature = hash_hmac('sha256', $encode_payload, $this->secretKey);
            $response_data = get_directpay_txn_status($encode_payload, $signature,$this->mode);
            $response_data = json_decode($response_data, true);
            if ($response_data['status'] == 200) {
                $response_data = $response_data['data']['transaction'];
                if (in_array(strtoupper($response_data['status']), $this->success_code)) {
                    // $transaction_record = $this->_update_tx_status($order_id, 1, $response_data);
                    redirect($txn_info['surl'], 'location', 301);
                }
            } else {
                $transaction_record = $this->_update_tx_status($order_id, 2, $response_data);
                redirect($txn_info['furl'], 'location', 301);
            }
        }
    }

    /**
     * update order status to platform
     * 
     * @param int $transaction_id Transaction ID
     * @param int $status_type Status Type
     * @return bool Status updated or not
     */
    private function _update_tx_status($transaction_id, $status_type, $pg_response)
    {
        $trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
        if ($status_type == 1) {
            if (!empty($trnxn_rec)) {
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', $this->pg_id);
            }
        }
        // CALL Platform API to update transaction
        $amount = isset($pg_response['amount']) ? ($pg_response['amount'] / 100) : 0;
        $data['pg_order_id'] = $this->order_prefix . $transaction_id;
        $data['bank_txn_id'] = (isset($pg_response['id'])) ? $pg_response['id'] : "";
        $data['txn_id'] = (isset($pg_response['id'])) ? $pg_response['id'] : "";
        $data['txn_amount'] = $amount;
        $data['txn_date'] = format_date();
        $data['gate_way_name'] = "Directpay";
        $data['transaction_status'] = $status_type;
        $data['currency'] = $this->currency;
        $data['payment_mode'] = isset($pg_response['channel']) ? $pg_response['channel'] : "";
        $data['transaction_message'] = isset($pg_response['description']) ? $pg_response['description'] : "";
        // print_r($data);die;
        $res = $this->Finance_model->update_transaction($data, $transaction_id);
        $order_detail = $this->Finance_model->get_single_row("user_id,real_amount", ORDER, array("order_id" => $trnxn_rec["order_id"]));
        $user_data = $this->Finance_model->get_single_row("user_name,email", USER, array("user_id" => $order_detail["user_id"]));

        // When Transaction has been failed , order status will also become fails
        if ($status_type == 2) {
            $sql = "UPDATE " . $this->db->dbprefix(ORDER) . " AS O
                    INNER JOIN " . $this->db->dbprefix(TRANSACTION) . " AS T ON T.order_id = O.order_id
                    SET O.status = T.transaction_status
                    WHERE T.transaction_id = $transaction_id";

            $this->db->query($sql);
            $tmp = array();
            $tmp["notification_type"] = 42;
            $tmp["source_id"] = 0;
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["user_id"] = $order_detail["user_id"];
            $tmp["to"] = $user_data["email"];
            $tmp["user_name"] = $user_data["user_name"];
            $tmp["added_date"] = format_date();
            $tmp["modified_date"] = format_date();
            $tmp["subject"] = "Deposit amount Failed to credit";
            $input = array("amount" => $order_detail["real_amount"]);
            $tmp["content"] = json_encode($input);
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);
        }
        if ($res) {
            return $trnxn_rec;
        }
        return FALSE;
    }
}
?>