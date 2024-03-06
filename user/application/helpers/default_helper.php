<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed');}

/**
 * Get formated  date string.
 * @param string $date
 * @param string $format
 * @return string
 */
if (!function_exists('format_date')) {

    function format_date($date = 'today', $format = DATE_FORMAT) {
        if ($date == "today") {
            if (IS_LOCAL_TIME === TRUE) {
                $back_time = strtotime(BACK_YEAR);
                $dt = date($format, $back_time);
            } else {
                $dt = date($format);
            }
        } else {
            if (is_numeric($date)) {
                $dt = date($format, $date);
            } else {
                if ($date != null) {
                    $dt = date($format, strtotime($date));
                } else {
                    $dt = "--";
                }
            }
        }

        $path = APPPATH . '../../date_time.php';

        if (file_exists($path)) {
            include($path);
        }

        if (isset($date_time) && $date_time && (ENVIRONMENT !== 'production' )) {
            $dt = date($format, strtotime($date_time));
        }
        return $dt;
    }
}

function convert_normal_to_mongo($normal_date)
{
	return new MongoDB\BSON\UTCDateTime(strtotime($normal_date)*1000);
}
/**
 * Get pagination offset.
 * @param int $page_no
 * @param int $lmiit
 * @return int
 */
if (!function_exists('get_pagination_offset')) {
    function get_pagination_offset($page_no, $lmiit) {
        if(empty($page_no)) 
        {
            $page_no = 1;
        }
        return ($page_no-1)*$lmiit;
    }
}

function new_mongo_id($id ='')
{
	if(empty($id))
	{
		return new MongoDB\BSON\ObjectId();
	}
	else{

		return new MongoDB\BSON\ObjectId($id);
	}
	
}
/**
 * replace quotes by "".
 * @param string $string
 * @return string
 */
if (!function_exists('replace_quotes')) {

    function replace_quotes($string) {
        return preg_replace(array("/`/", "/'/", "/&acute;/"), "", $string);
    }

}

/**
 * generate random string based on given length.
 * @param int $length
 * @return string
 */
if (!function_exists('generateRandomString')) {

    function generateRandomString($length = 10) {
        return substr(md5(mt_rand() . uniqid()), 0, $length);
    }

}

function get_paytm_transaction_status($MID, $ORDERID = "", $CHECKSUMHASH = "",$PAYTM_ORDER_STATUS_API="") {
    // Prepare data for POST request
    $data = array('MID' => $MID, 'ORDERID' => $ORDERID, "CHECKSUMHASH" => $CHECKSUMHASH);
    $api_url = $PAYTM_ORDER_STATUS_API;

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'content-type: application/json'
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "JsonData=" . json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!empty($response)) {
        $response = json_decode($response, TRUE);
    }
    if (LOG_TX) {
        log_message('error', 'PayTm requestParamList : '.format_date().' : '.json_encode($data));
        log_message('error', 'PayTm responseParamList : '.format_date().' : '.json_encode($response));
    }
    return $response;
}

function get_paystack_transaction_status($txnid,$config){
    $result = array();
//The parameter after verify/ is the transaction reference to be verified
    $url = PAYSTACK_STATUS_URL. $txnid;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt(
        $ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$config['secret']]
        );
    $request = curl_exec($ch);
    curl_close($ch);
    if ($request) {
        $result = json_decode($request, true);
    }
        // print_r($result['data']['metadata']);exit;
    if (array_key_exists('data', $result) && array_key_exists('status', $result['data']) && ($result['data']['status'] === 'success')) {
        return array('status' => 'SUCCESS');
    } else{
        return array('status' => 'FAILURE');
    }
}

function get_ipay_transaction_status($txnid,$getdata,$config=array()){

if(empty($txnid)){
    return false;
}

                $vendor=$config['IPAY_MERCHANT_KEY'];
                $updateTransectionData = array();
                if($getdata):
                    $updateTransectionData['id']= $getdata['id'];
                    $updateTransectionData['ivm']= $getdata['ivm'];
                    $updateTransectionData['qwh']= $getdata['qwh'];
                    $updateTransectionData['afd']= $getdata['afd'];
                    $updateTransectionData['poi']= $getdata['poi'];
                    $updateTransectionData['uyt']= $getdata['uyt'];
                    $updateTransectionData['ifd']= $getdata['ifd'];
                    $updateTransectionData['status']= $getdata['status'];
                    $updateTransectionData['amount']= $getdata['mc'];
                    //$updateTransectionData['txncd']= $getdata('txncd');
                    $updateTransectionData['phone']= $getdata['msisdn_idnum'];
                else:
                    $updateTransectionData['id']= 0;
                    $updateTransectionData['ivm']= 0;
                    $updateTransectionData['qwh']= 0;
                    $updateTransectionData['afd']= 0;
                    $updateTransectionData['poi']= 0;
                    $updateTransectionData['uyt']= 0;
                    $updateTransectionData['ifd']= 0;
                    $updateTransectionData['status']= 0;
                    $updateTransectionData['amount']= 0;
                    $updateTransectionData['txncd']= 0;
                    $updateTransectionData['phone']= 0;
                endif;

        $ipnurl = "https://www.ipayafrica.com/ipn/?vendor=".$vendor."&id=".$updateTransectionData['id']."&ivm=".$updateTransectionData['ivm']."&qwh=".$updateTransectionData['qwh']."&afd=".$updateTransectionData['afd']."&poi=".$updateTransectionData['poi']."&uyt=".$updateTransectionData['uyt']."&ifd=".$updateTransectionData['ifd'];
        $fp = fopen($ipnurl, "rb");
        $status = stream_get_contents($fp, -1, -1);
        fclose($fp);
        if($status == $config['SUCCESS_STATUS'] || $status == $config['ALREADY_STATUS']){
            return array('status' => 'SUCCESS');
        }
        else if($status == $config['FAIL_STATUS']){
            return array('status' => 'FAILURE');
        }

}

function payu_validate_transaction($txnid,$config=array()) {
    if (empty($txnid)) {
        return false;
    }

    $postData = array();
    $postData['merchantKey'] = $config['MERCHANT_KEY'];
    $postData['merchantTransactionIds'] = $txnid;
    $postNow = http_build_query($postData);
    $post_url = $config['TXN_VALIDATE_BASE_URL'] . "/payment/payment/chkMerchantTxnStatus?" . $postNow;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, TRUE);
    curl_setopt($ch, CURLOPT_URL, $post_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $header = array(
        'Authorization: ' . $config['AUTH_HEADER']
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $out = curl_exec($ch);
    //if got error
    if (curl_errno($ch)) {
        $c_error = curl_error($ch);
        if (empty($c_error)) {
            $c_error = 'Some server error';
        }
        return array('status' => 'FAILURE', 'error' => $c_error);
    }
    $out = (array) json_decode(trim($out));

    if (isset($out['status']) && $out['status'] == 0) {
        return array('status' => 'SUCCESS', 'result' => $out);
    }

    $c_error = "";
    if (isset($out['message'])) {
        $c_error = $out['message'];
    }

    return array('status' => 'FAILURE', 'error' => $c_error);
}

function get_user_ip_address(){
    if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else if(isset($_SERVER['HTTP_X_FORWARDED']) && !empty($_SERVER['HTTP_X_FORWARDED'])){
        $ip = $_SERVER['HTTP_X_FORWARDED'];
    }else if(isset($_SERVER['HTTP_FORWARDED_FOR']) && !empty($_SERVER['HTTP_FORWARDED_FOR'])){
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }else if(isset($_SERVER['HTTP_FORWARDED']) && !empty($_SERVER['HTTP_FORWARDED'])){
        $ip = $_SERVER['HTTP_FORWARDED'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

//paypal methods
function signature_paypal_validate_transaction($txnid,$config){
    if($config['p_mode']=='TEST'){
        $url = 'https://api-3t.sandbox.paypal.com/nvp';
    }
    else{
        $url = 'https://api-3t.paypal.com/nvp';
    }
    
    $data = array(
    'USER' =>$config['p_username'], 
    'PWD' =>$config['p_password'], 
    'SIGNATURE' =>$config['p_signature'], 
    'METHOD' => 'GetTransactionDetails', 
    'VERSION' => '123', 
    'TransactionID' => $txnid
);
$data = http_build_query($data);
return make_post_call($url,$data,$config);
}

function make_post_call($url, $postdata,$config) {
    global $token;
    $token = generate_access_token($config);
    $curl = curl_init($url); 
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($curl, CURLOPT_SSLVERSION , 6);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer '.$token,
        'Accept: application/json',
        'Content-Type: application/json'
    ));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata); 
    $response = curl_exec( $curl );
    $decode_response = urldecode($response);
    
    $info = curl_getinfo($curl);
    curl_close($curl); // close cURL handler
    return $decode_response;
}


function secret_paypal_validate_transaction($txnid){
        if (empty($txnid)) {
            return false;
        }

        $token = generate_access_token();
        $start_date =date('Y-m-d', strtotime("-7 day"));
        $end_date =date('Y-m-d');
        $headers = array();
        $headers[] = "Content-Type:application/json";
        $headers[]="Authorization:Bearer ".$token;
        if(PAYPAL_PG_MODE=='TEST'){
            $url ="https://api.sandbox.paypal.com/v1/reporting/transactions?transaction_id=".$txnid."&start_date=".$start_date."T00:00:00-0700&end_date=".$end_date."T23:59:59-0700";
        }
        else{
            $url ="https://api.paypal.com/v1/reporting/transactions?transaction_id=".$txnid."&start_date=".$start_date."T00:00:00-0700&end_date=".$end_date."T23:59:59-0700";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION , 6); //NEW ADDITION
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        echo '<pre>';
        print_r($result); die;
        //if got error
        if (curl_errno($ch)) {
            $c_error = curl_error($ch);
            if (empty($c_error)) {
                $c_error = 'Some server error';
            }
            return array('status' => 'FAILURE', 'error' => $c_error);
        }
        $out = (array) json_decode(trim($result),true);
        curl_close($ch);
        if($out['transaction_details'][0]['transaction_info']){ //THIS CODE IS NOW WORKING!
        return array('status' => 'SUCCESS', 'result' => $out['transaction_details'][0]['transaction_info']);
    }
    elseif($out['account_number']=='K8XQ97WHABRKG'){
        return array('status' => 'PENDING','result'=>$out);
    }

    }

    function generate_access_token($config){

        $ch = curl_init();
        $clientId = $config['p_client_id']; // PAYPAL_CLIENT_ID;
        $secret = $config['p_secret']; // PAYPAL_SECRET_KEY;
        if($config['p_mode']=='TEST'){
        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        }
        else{
        curl_setopt($ch, CURLOPT_URL, "https://api.paypal.com/v1/oauth2/token");
        }

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION , 6); //NEW ADDITION
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $clientId.":".$secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $result = curl_exec($ch);
        if(empty($result))die("Error: No response.");
        else
        {
            //print_r($result);exit;
            $json = json_decode($result);
            return $json->access_token;
            //print_r($json->access_token);
        }

        curl_close($ch); //THIS CODE IS NOW WORKING!
    }

function save_user_session_key($session_id){
    $session_id = str_replace(".","_",$session_id);
    $file_path =  ROOT_PATH.UPLOAD_DIR."userdata/".$session_id.".json";
    $user_data = array();
    if (file_exists($file_path)) {
        $user_data = file_get_contents($file_path);
        $user_data = json_decode($user_data,TRUE);
    }

    $user_data[$session_id] = time();
    file_put_contents($file_path,json_encode($user_data));
    return true;
}

function get_user_access_time($session_id){
    $session_id = str_replace(".","_",$session_id);
    $file_path =  ROOT_PATH.UPLOAD_DIR."userdata/".$session_id.".json";
    $user_data = array();
    if (file_exists($file_path)) {
        $user_data = file_get_contents($file_path);
        $user_data = json_decode($user_data,TRUE);
    }
    $last_access_time = 0;
    if(isset($user_data[$session_id])){
        $last_access_time = $user_data[$session_id];
    }

    return $last_access_time;
}

function get_server_cpu_usage(){
    $load = sys_getloadavg();
    return $load[0];
}

function get_server_memory_usage(){
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2]/$mem[1]*100;

    return $memory_usage;
}

function get_server_disk_usage(){
    $diskfree = round(disk_free_space("/") / 1000000000);
    $disktotal = round(disk_total_space("/") / 1000000000);
    $diskused = round($disktotal - $diskfree);
    $diskusage = round($diskused / $disktotal * 100);
    return $diskusage;
}

function get_razorpay_txn_status($txnid,$config){
    $api_key = $config['r_key'];
    $secret_key = $config['r_secret'];
    $order_id = $txnid;
    $url = 'https://api.razorpay.com/v1/orders/'.$order_id.'/payments';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_USERPWD, $api_key.":".$secret_key);
    $response = curl_exec ($ch);
    curl_getinfo($ch, CURLINFO_HTTP_CODE);//get status code
    curl_close ($ch);
    if (!empty($response)) {
        $response = json_decode($response, TRUE);
    }

    return $response;
}

/**
 * single function for zoom auto kyc : verify pan, bank and ifsc
 * @param mixed $reqData
 * @param mixed $custom_data
 * @return mixed
 */
function validate_kyc_info($reqData,$custom_data=array())
{
    
    $pan            = ['TEST'=>'https://test.zoop.one/api/v1/in/identity/pan/lite', 'PROD'=>'https://live.zoop.one/api/v1/in/identity/pan/lite'];
    $bank           = ['TEST'=>'https://test.zoop.one/api/v1/in/financial/bav/lite','PROD'=>'https://live.zoop.one/api/v1/in/financial/bav/lite'];
    $ifsc           = ['TEST'=>'https://test.zoop.one/api/v1/in/utility/ifsc/lite', 'PROD'=>'https://live.zoop.one/api/v1/in/utility/ifsc/lite'];
    $aadhar_otp     = ['TEST'=>'https://test.zoop.one/in/identity/okyc/otp/request', 'PROD'=>'https://live.zoop.one/in/identity/okyc/otp/request'];
    $aadhar_verify  = ['TEST'=>'https://test.zoop.one/in/identity/okyc/otp/verify', 'PROD'=>'https://live.zoop.one/in/identity/okyc/otp/verify'];
   
    if($custom_data['type'] == 1) {
        $url = $pan[$custom_data['mode']];
    }elseif($custom_data['type'] == 2){
        $url = $bank[$custom_data['mode']];
    }elseif($custom_data['type'] == 3){
        $url = $ifsc[$custom_data['mode']];
    }elseif($custom_data['type'] == 4){
        $url = $aadhar_otp[$custom_data['mode']];
    }elseif($custom_data['type'] == 5){
        $url = $aadhar_verify[$custom_data['mode']];
    }

    $api_key = $custom_data['kyc_key'];
    $api_id = $custom_data['kyc_id'];
    $headers = array('Content-Type: application/json', 'api-key: '.$api_key.'', 'app-id: '.$api_id.'');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $reqData);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch))
    {
        $c_error = curl_error($ch);
        if (empty($c_error)) {
            $c_error = 'Some server error';
        }
        return(array('status' => 'FAILURE', 'error' => $c_error));
    }
    curl_close ($ch);
    $verification_data = json_decode($result, TRUE);

    return $verification_data;
}

function convert_to_client_timezone($datetime,$format)
{
    $date = new DateTime($datetime);
    $tz = new DateTimeZone(CLIENT_TIME_ZONE);
    $date->setTimezone($tz);
    //print_r($date);die;
   return $deadline_date   = $date->format($format);
}

function minimize_array_keys($array,$replacable_key,$replace_with='')
    {
        foreach($array as &$inner_array)
        {
           foreach($inner_array as $key => $value)
           {
               $newkey = str_replace($replacable_key,$replace_with,$key);
               unset($inner_array[$key]);
               $inner_array[$newkey] = $value;
           }
        }

        return $array;
    }

function get_stripe_txn_status($data)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.stripe.com/v1/charges/".$data['charge_id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST=> false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array(
            "content-type: application/json",
            "Authorization: Bearer ".$data['key'],
        ),
    ));

    $response = curl_exec($curl);
    $response = json_decode($response,true);
    //  echo "<pre>";print_r($response);exit;
    $err = curl_error($curl);
    curl_close($curl);

    if($response['paid']==true && $response['amount']==$data['amount'] && $response['metadata']['user_id'] == $data['user_id'])
    {
        $result = array(
            "status"=>"SUCCESS",
        );
    }
    else{
        $result = array(
            "status"=>"FAILURE",
        );
    }
    return $result;
}
function get_exclude_class_methods(){
    $class = array();
    $method = array("payumoney/success","payumoney/failure","payumoney/callback_success","payumoney/callback_failure","paypal/express_checkout","paypal/cancel","paypal/process_ipn","paytm/payment_callback","paytm/paytm_s2s_callback","paytm/payout_callback","ipay/payment_callback","paystack/express_checkout","mpesa/callback","mpesa/withdraw_callback","razorpay/callback","razorpay/success","razorpay/failure","notification/sync_notification_description","notification/update_notification_status","finance/sync_transaction_messages","coins/sync_earn_coins","auth/get_app_version");
    $event_arr = array();
    $event_arr['class'] = $class;
    $event_arr['method'] = $method;
    return $event_arr;
}

function validate_location_api($post_data){
    if(empty($post_data)){
        return true;
    }
    $key = $post_data['key'];
    $query_str = $post_data['query'];
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'http://api.positionstack.com/v1/reverse?limit=1&access_key='.$key.'&query='.$query_str,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    if (!empty($response)) {
        $response = json_decode($response, TRUE);
    }
    return $response;
}


/**
 * veryfying ifantasy transactions
 */
function get_ifantasy_txn_status($data){
    
        $key = $data['key'];
        $member_id = $data['member_id'];
        $txnid = $data['txnid'];
        $fields = array(
            "order_id"  => $txnid,
            "me_id"     => $member_id,
        );

      //CURL EXECUTION
      try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, IFANTASY_VERIFY_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = [
            'APIKey: '.$key,
            'Content-Type: application/json',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        return $response;
        }
        catch(Exception $e){
            $this->api_response_arry['error'] = 'Error : '.$e;
            $this->api_response();
        }  
}

function get_crypto_transaction_status($clientTranId,$cryptoTranId,$client_endpoint){    

    $url_params = "?tran_id=$cryptoTranId&client_tran_id=$clientTranId";
    $url = $client_endpoint.'deposit_status_check.php'.$url_params;

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $res_arr = json_decode($response,true);
    
    return $res_arr['status'];
    
}

function get_crypto_currencies()
{
    return array(
        "BNB"=>"BINANCE COIN",
        "BNB.BSC"=>"SMART CHAIN",
        "ETH"=>"ETHEREUM",
        "TRX"=>"TRON"
    );
}

/**
     * Used for validate transaction data
     * @param int $txnid
     * @return json array
     */
    function cashfree_validate_transaction($data) {
        if (empty($data['txnid'])) {
            return false;
        }

        $tx_staus_url = CASHFREE_TESTPAY_URL . 'orders/'.$data['txnid'].'/payments';
        if ($data['mode'] == 'PRODUCTION') {
            $tx_staus_url = CASHFREE_PRODPAY_URL . 'orders/'.$data['txnid'].'/payments';
        }

        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $tx_staus_url, 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HEADER =>1,
            CURLOPT_HTTPHEADER => array(
                'X-Client-Id: '.$data['app_id'],
                'X-Client-Secret: '.$data['secret_key'],
                'x-api-version: '.$data['app_version'],
                'Content-Type: application/json'
            ),
            ));

            $response = curl_exec($curl);
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $header = (int)substr($response, 9, 3);
            $body = substr($response, $header_size);
            $response = json_decode($body,true);
            $is_success= false;
            error_log("\n".'Cashfree txn status : '.json_encode($response).'<br>',3,'/var/www/html/cron/application/logs/cashfree.log');
            if(!empty($response[0]))
            {
                if(count($response) > 1)
                {
                    foreach($response as $key=>$res)
                    {
                        if($res['payment_status']=='SUCCESS')
                        {
                            $is_success=true;
                            $response = $response[$key];
                            break;
                        }
                    }
                    if($is_success==false)
                    {
                        $response = $response[0];
                    }
                }
                else{
                    $response = $response[0];
                }
            }
            
            curl_close($curl);
            return $response;

        }catch(Exception $e){
           return false;
        }
    }
    function get_cashierpay_hash($req_data,$salt)
    {
        ksort($req_data);
        $final_data = '';
        foreach($req_data as $x=>$x_value)
        {
        $final_data = $final_data.$x.'='.$x_value.'~';
        }
        $final_data =  substr($final_data,0,-1).$salt;
        // echo $final_data;
        $hash = strtoupper(hash('sha256',$final_data));
        return $hash;
    }

    function get_cashierpay_txn_status($data,$url)
    {
        //CURL EXECUTION
        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
            $headers = [
                'Content-Type: application/json',
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($response, true);
            // echo " status api response <pre>";print_r($response);exit;
            return $response;
            }
            catch(Exception $e){
                $this->data['data'] = $this->load->view('paystack/deposit', $this->data, true);
                $this->api_response_arry['error'] = 'Error : '.$e;
                $this->api_response();
            } 

    }

    function get_paylogic_txn_status($data, $app_id)
    {
        $url = PAYLOGIC_STATUS_TURL;
        if(ENVIRONMENT=='production')
        {
            $url = PAYLOGIC_STATUS_URL;
        }
        $curl = curl_init();
        //echo $data['txn_id'];exit;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,

            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'merchantId=' . $app_id . '&txnId=' . $data['txn_id'],
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent:PostmanRuntime/7.29.0'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

function generate_data_hash($string, $action = 'e' )
{
    //Please update hash keys
    $secret_key = ENC_KEY;
    $secret_iv  = ENC_IV;

    $output = "";
    $encrypt_method = "AES-256-CBC";
    $key = hash( 'sha256', $secret_key );
    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

    if( $action == 'e' ) {
        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }
    else if( $action == 'd' ){
        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }

    return $output;
}

function generate_verify_otp($post_data) {
    
    $response = array();
    $time_limit = OTP_EXPIRY_TIME;//time in minute
    $current_time = date("Y-m-d H:i:s");
    $hash = isset($post_data['hash']) ? $post_data['hash'] : "";
    //$entity_code = isset($post_data['entity_code']) ? $post_data['entity_code'] : "91";
    $entity_no = isset($post_data['entity_no']) ? $post_data['entity_no'] : "";
    $otp = isset($post_data['otp']) ? $post_data['otp'] : "";
    $type = "e";
    if(isset($post_data['type']) && in_array($post_data['type'],array("e","d"))){
        $type = $post_data['type'];
    }
    $CI =& get_instance();
    $lang_finance=$CI->lang->line('finance');
    if($type == "e"){
        
        $default_otp=0;
        $otp_length=4;
        if($default_otp == 1){
           $otp = 0;
           for($i=1;$i<=$otp_length;$i++){
               if($otp == 0){
                    $otp=$i;
               }else{
                    $otp= $otp.$i;           
               }
           }
        }else{
            $otp = sprintf( "%0".$otp_length."d", rand(0,9999));
        }
        $expiry_date = date("Y-m-d H:i:s",strtotime($current_time." +".$time_limit." minutes"));
        $time = strtotime($expiry_date);
        $input_str = $entity_no."_".$otp."_".$time;
        $enc_key = generate_data_hash($input_str,"e");

        $response['hash'] = $enc_key;
        $response['otp'] = $otp;
        $response['entity_no'] = $entity_no;
        
    }else if($type == "d"){

        if(empty($hash) || empty($entity_no) || empty($otp)) {
            $response['status'] = 500;
            $response['message'] = $lang_finance['provide_all_parameter'];
            return $response;
        }
        $hash_key = generate_data_hash($hash,"d");
        if(!$hash_key){
            $response['status'] = 500;
            $response['message'] = $lang_finance['invalid_otp'];
        }else{
            $hash_arr = explode("_",$hash_key);
            $time_stamp = strtotime($current_time);
            $expire_time = $hash_arr['2'];
            $phone_str = $entity_no;
            if($hash_arr['0'] != $phone_str){
                $response['status'] = 500;
                //$response['message'] = "Invalid mobile number.";
                $response['message'] = $lang_finance['invalid_otp'];
            }else if($hash_arr['1'] != $otp){
                $response['status'] = 500;
                $response['otp_attempt'] = 1;
                $response['message']= $lang_finance['invalid_otp'];
            }else if($expire_time < $time_stamp){
                $response['status'] = 500;
                $response['message'] = $lang_finance['otp_expired'];
            }else if($hash_arr['0'] == $phone_str && $hash_arr['1'] == $otp){
                $response['status'] =  200;
                $response['message'] = "OTP verified successfully.";    
            }
        }
    }else{
        $response['status'] =  500;
        $response['message'] = $lang_finance['provide_all_parameter'];
    }
    return $response;
}

function generate_task_id($key_format = "8-4-4-4-12")
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $key_arr = explode("-", $key_format);
    $device_id = array();
    foreach ($key_arr as $length) {
        $charactersLength = strlen($characters);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[random_int(0, $charactersLength - 1)];
        }
    }
    return implode("-", $device_id);
}

if (!function_exists('array_to_csv')) {
    function array_to_csv($array, $download = "") {
        if ($download != "") {
            header('Content-Type: application/csv');
            header('Content-Disposition: attachement; filename="' . $download . '"');
        }

        ob_start();
        $f = fopen('php://output', 'w') or show_error("Can't open php://output");
        $n = 0;

        foreach ($array as $line) {
            if(isset($line['secondary_referral'])){
                unset($line['secondary_referral']);
            }
            $n++;
            if (!fputcsv($f, $line)) {
                show_error("Can't write line $n: $line");
            }
        }
        fclose($f) or show_error("Can't close php://output");
        $str = ob_get_contents();
        echo $str;
        ob_end_clean();

        if ($download == "") {
            return $str;
        } else {
            echo $str;
            exit;
        }
    }
}


function get_financial_year($key="current")
{
    $current_date = format_date();
    $current_year = date("Y",strtotime($current_date));
    $current_month = date("m",strtotime($current_date));
    if($current_month < 4) {
        $current_year--;
    }
    if($key == "last"){
        $start_year = ($current_year - 1);
        $end_year = $current_year;
    }else{
        $start_year = $current_year;
        $end_year = ($current_year + 1);
    }
    $start_date = $start_year."-04-01 00:00:00";
    $end_date = $end_year."-03-31 23:59:59";
    $fy = $start_year."-".substr($end_year,-2);
    return array("fy"=>$fy,"start_date"=>$start_date,"end_date"=>$end_date);
}

function get_directpay_txn_status($encode_payload,$signature,$mode="TEST")
{
    $url = DIRECT_STATUS_TURL;
    if($mode=='PRODUCTION')
    {
        $url = str_replace("test-","",DIRECT_STATUS_TURL);
    }
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>$encode_payload,
    CURLOPT_HTTPHEADER => array(
        'Authorization: hmac '.$signature,
        'Content-Type: application/json'
    ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function get_media_setting($type){
	$media_arr = array();
	$media_arr['mpg'] = array("min_h"=>"164","max_h"=>"5000","min_w"=>"164","max_w"=>"5000","size"=>"20","type"=>array("jpg","png","jpeg","JPEG","JPG","PNG"),"path"=>"upload/mpg_receipt");
	if(isset($media_arr[$type])){
		return $media_arr[$type];
	}else{
		return array();
	}
}

function get_timezone($date='',$format='',$tz_arr=array(),$type=1,$to_utc=2)
{
        $timezone_list = array(
                "IST"   =>"Asia/Kolkata",
                "UTC"   =>"UTC",
                "ET"    =>"America/New_York",
                "GMT"   =>"Europe/London",
                "JST"   =>"Asia/Tokyo",
                "AEST"  =>"Australia/Sydney",
                "EET"   =>"Africa/Cairo",
                "HST"   =>"Pacific/Honolulu",
                "CET"   =>"Africa/Algiers"
        );

        if(isset($tz_arr) && !empty($tz_arr) && $date != "")
        {
            $time_zone = isset($tz_arr['key_value']) ? $tz_arr['key_value']:"UTC";
            if(!isset($format))
            {
                    $format = "d M Y";
            }
            $tz_name = isset($timezone_list[$time_zone]) ? $timezone_list[$time_zone] : "UTC";
            if($type==2)
            {
                date_default_timezone_set($tz_name);
                $converted_date = date('Y-m-d H:i:s', $date);
            }else{
				if($to_utc==1)
				{
					$from_timezone = $tz_name;
					$to_timezone = 'UTC';
				}else{
					$from_timezone = 'UTC';
					$to_timezone = $tz_name;
				}
				$date = date('Y-m-d H:i:s',$date);
                $date = new DateTime($date, new DateTimeZone($from_timezone));
                $date->setTimezone(new DateTimeZone($to_timezone));
                $converted_date = $date->format($format);
            }
            
            return array("date"=>$converted_date,"tz"=>$to_utc==1 ? 'UTC' :$time_zone);
        }else{
                return $timezone_list;
        }
}

function gst_calculate($amount,$config){
    $res = array('amount'=>$amount,'gst'=>0,'gst_rate'=>0);
    if(isset($config['allow_gst']['key_value']) && $config['allow_gst']['key_value'] == 1) {
        $type =  $config['allow_gst']['custom_data']['type'];
        $gst_rate =  $config['allow_gst']['custom_data']['gst_rate'];
        if(strtolower($type) == 'new' && $gst_rate > 0 && $amount > 0){
            $gst_amount = number_format(($gst_rate/100)*$amount, 2, '.', '');
            $res['amount'] = $amount + $gst_amount;
            $res['gst'] = $gst_amount;
            $res['gst_rate'] = $gst_rate;
        }
    }
    return $res;
}
 
