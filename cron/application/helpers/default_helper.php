<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

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

function get_data_by_curl($url, $params, $headers = array()) {
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function http_get_request($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/**
 * truncate number
 * @param int $number
 * @param int $decimals
 * @return string
 */
if (!function_exists('truncate_number')) {

    function truncate_number($number = 0, $decimals = 2) {
        $point_index = strrpos($number, '.');
        if ($point_index === FALSE)
            return $number;
        return substr($number, 0, $point_index + $decimals + 1);
    }

}

/**
 * truncate number only
 * @param int $number
 * @param int $precision
 * @return int
 */
if (!function_exists('truncate_number_only')) {

    function truncate_number_only($number, $precision = 2) {
        // Zero causes issues, and no need to truncate
        if (0 == (int) $number) {
            return $number;
        }
        // Are we negative?
        $negative = $number / abs($number);
        // Cast the number to a positive to solve rounding
        $number = abs($number);
        // Calculate precision number for dividing / multiplying
        $precision = pow(10, $precision);
        // Run the math, re-applying the negative value to ensure returns correctly negative / positive
        return floor($number * $precision) / $precision * $negative;
    }

}
//Time Interval for game closed for when we will proccess prize distribution
if (!function_exists('game_interval')) {

    function game_interval($sports_id) {
        switch ($sports_id) {
            // In case of Soccer
            case '5':
                $interval = 8;
                break;
            // In case of Cricket (OneDay)
            case '7_1':
                $interval = 15;
                break;
            // In case of Cricket (Test)
            case '7_2':
                $interval = 144;    // 24*6
                break;
            // In case of Cricket (T20)
            case '7_3':
                $interval = 8;
                break;
            // In case of Cricket (T10)
            case '7_4':
                $interval = 8;
                break;
            case '2':
                $interval = 6;
                break;
            // In case of Golf
            case '9':
                $interval = 100;
                break;
            case '10':
                $interval = 1;
                break;
            default:
                $interval = 6;
        }
        return $interval;
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
 * get image name
 * @param int $type, 0 = flag , 1= jersey
 * @param string $image
 * @param int $sports_id
 * @return string
 */
if (!function_exists('get_image')) {

    function get_image($type, $image, $sports_id = 7) {
        $img = IMAGE_PATH;
        switch ($type) {
            case 0 :
                $img = $img . FLAG_CONTEST_DIR;
                $img .= ($image) ? $image : 'flag_default.jpg';
                break;
            case 1:
                $img = $img . JERSEY_CONTEST_DIR;
                if ($sports_id == 8) {
                    $img .= ($image) ? $image : 'kabaddi_jersey.png';
                } else {
                    $img .= ($image) ? $image : 'jersey_default.png';
                }
                break;
            case 2:
                $img = $img . LEAGUE_IMAGE_DIR;
                $img .= ($image) ? $image : 'league-1.png';
                break;
        }
        return $img;
    }

}

/**
 * send bulk premium sms
 * @param array $post_data
 * @return array
 */
if (!function_exists('send_bulksmspremium_sms')) {

    function send_bulksmspremium_sms($post_data = array(),$config=array()) {
        $curl = curl_init();
        $post_array = array(
            "smsContent" => isset($post_data['message']) ? $post_data['message'] : "",
            "routeId" => $config['sms_gateway_route_id'],
            "mobileNumbers" => $post_data['mobile'],
            "senderId" => $config['sms_gateway_sender_id'],
            "signature" => "signature",
            "smsContentType" => "english"
        );
        //http://websms.bulksmspremium.com/ $config['']
        curl_setopt_array($curl, array(
            CURLOPT_URL => $config['sms_gateway_api_endpoint']."rest/services/sendSMS/sendGroupSms?AUTH_KEY=" . $config['sms_gateway_auth_key'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($post_array),
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        //echo $response;die;
        if ($err) {
            return array("responseCode" => "3017", "response" => $err);
        } else {
            return json_decode($response, true);
        }
    }

}

/**
 * send msg91 ms
 * @param array $post_data
 * @return array
 */
if (!function_exists('send_msg91_sms')) {

    function send_msg91_sms($post_data = array(),$config=array()) {
        //http://api.msg91.com/
        $url = $config['sms_gateway_api_endpoint'] . "api/sendhttp.php";
        $country_code = DEFAULT_PHONE_CODE;
        if(isset($post_data['phone_code']) && $post_data['phone_code'] != ""){
            $country_code = $post_data['phone_code'];
        }
        $route = $config['sms_gateway_route_id'];
        if(isset($post_data['route']) && $post_data['route'] != ""){
            $route = $post_data['route'];
        }
        $post_array = array(
            "route" => $route,
            "sender" => $config['sms_gateway_sender_id'],
            "authkey" => $config['sms_gateway_auth_key'],
            "country" => $country_code,
            "mobiles" => $post_data['mobile'],
            "message" => isset($post_data['message']) ? $post_data['message'] : "",
            "encrypt" => "",
            "flash" => "",
            "unicode" => '1',
            "afterminutes" => "",
            "response" => "",
            "campaign" => "",
        );

        $template_id = $config['sms_gateway_template'];
        if(isset($post_data['template_id']) && $post_data['template_id'] != ""){
            $template_id = $post_data['template_id'];
        }

        if($template_id != ""){
            $post_array['DLT_TE_ID'] = $template_id;
        }

        $query = http_build_query($post_array);
        $url = $url . "?" . $query;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return array("response" => $err);
        } else {
            return $response;
        }
    }

}

/**
 * send onnsms msg
 * @param array $post_data
 * @return array
 */
if (!function_exists('send_onnsms_sms')) {
    function send_onnsms_sms($post_data = array(),$config=[]) {
        //http://api.onnsms.in/
        $message = isset($post_data['message']) ? $post_data['message'] : "";
        $url = $config['sms_gateway_api_endpoint'] . "api/".$post_data['mobile']."/".$message;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return array("response" => $err);
        } else {
            return $response;
        }
    }

}

/**
 * send kaleyra sms
 * @param array $post_data
 * @return array
 */
if (!function_exists('send_kaleyra_sms')) {

    function send_kaleyra_sms($post_data = array(),$config=[]) {
        if (!isset($post_data['otp']) || $post_data['otp'] == "") {
            return true;
        }
        $sms_txt = '{OTP_CODE} is your OTP for '.SITE_TITLE;
        $otp_msg = str_replace("{OTP_CODE}", $post_data['otp'], $sms_txt);
        $url = $config['sms_gateway_api_endpoint'] . "?api_key=" . $config['sms_gateway_auth_key'] . "&method=sms&message=" . $otp_msg . "&to=" . $post_data['mobile'] . "&sender=" . $config['sms_gateway_sender_id'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return array("response" => $err);
        } else {
            return $response;
        }
    }

}

/**
 * send two factor sms
 * @param array $post_data
 * @param int $sms_type
 * @return array
 */
if (!function_exists('send_two_factor_sms')) {

    function send_two_factor_sms($post_data, $sms_type = 1,$config=[]) {
        $url = $config['sms_gateway_api_endpoint'];
        $input_var = array();
        $input_var['module'] = "TRANS_SMS";
        $input_var['apikey'] = $config['sms_gateway_auth_key'];
        $input_var['to'] = $post_data['mobile'];
        $input_var['from'] = $config['sms_gateway_sender_id'];
        $input_var['templatename'] = $config['sms_gateway_template'];
        $input_var['var1'] = $post_data['otp'];
        $input_var['var2'] = "";

        $query = http_build_query($input_var);
        $url = $url . "?" . $query;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return array("response" => $err);
        } else {
            return $response;
        }
    }

    // For soccer goalserver and othere where we required xml2array
    function xml2array($contents, $get_attributes = 1, $priority = 'attribute') {
        if (!$contents)
            return array();

        if (!function_exists('xml_parser_create')) {
            //print "'xml_parser_create()' function not found!";
            return array();
        }

        //Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);

        if (!$xml_values)
            return; //Hmm...

            
//Initializations
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();

        $current = & $xml_array; //Refference
        //Go through the tags.
        $repeated_tag_index = array(); //Multiple tags with same name will be turned into an array
        foreach ($xml_values as $data) {
            unset($attributes, $value); //Remove existing values, or there will be trouble
            //This command will extract these variables into the foreach scope
            // tag(string), type(string), level(int), attributes(array).
            extract($data); //We could use the array by itself, but this cooler.

            $result = array();
            $attributes_data = array();

            if (isset($value)) {
                if ($priority == 'tag')
                    $result = $value;
                else
                    $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
            }

            //Set the attributes too.
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag')
                        $attributes_data[$attr] = $val;
                    else
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }

            //See tag status and do the needed.
            if ($type == "open") { //The starting of the tag '<tag>'
                $parent[$level - 1] = & $current;
                if (!is_array($current) or ( !in_array($tag, array_keys($current)))) { //Insert New tag
                    $current[$tag] = $result;
                    if ($attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag . '_' . $level] = 1;

                    $current = & $current[$tag];
                } else { //There was another element with the same tag name
                    if (isset($current[$tag][0])) { //If there is a 0th element it is already an array
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level] ++;
                    } else { //This section will make the value an array if multiple tags with the same name appear together
                        $current[$tag] = array($current[$tag], $result); //This will combine the existing item and the new item together to make an array
                        $repeated_tag_index[$tag . '_' . $level] = 2;

                        if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            } elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
                //See if the key is already taken.
                if (!isset($current[$tag])) { //New Key
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                } else { //If taken, put all things inside a list(array)
                    if (isset($current[$tag][0]) and is_array($current[$tag])) { //If it is already an array...
                        // ...push the new element into that array.
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;

                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level] ++;
                    } else { //If it is not an array...
                        $current[$tag] = array($current[$tag], $result); //...Make it an array using using the existing value and the new value
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }

                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level] ++; //0 and 1 index is already taken
                    }
                }
            } elseif ($type == 'close') { //End of tag '</tag>'
                $current = & $parent[$level - 1];
            }
        }

        return ($xml_array);
    }

    function check_key_exist($array = array(), $key = "", $value_type = 'string') {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            if ($value_type == 'string')
                return "";
            if ($value_type == 'int')
                return 0;
            if ($value_type == 'array')
                return array();
        }
    }

    function get_paytm_transaction_status($MID, $ORDERID = "", $CHECKSUMHASH = "",$PAYTM_ORDER_STATUS_API="") {
        // Prepare data for POST request
        $data = array('MID' => $MID, 'ORDERID' => $ORDERID, "CHECKSUMHASH" => $CHECKSUMHASH);
        $json = "JsonData=" . json_encode($data);
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
        if (!empty($response))
            ; {
            $response = json_decode($response, TRUE);
        }
        return $response;
    }

    function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}

}

function camelCaseString($key){
$key = ucfirst(implode('', array_map('ucfirst', explode('_', $key))));
return $key;
}

function x_week_range($date) {
    $ts = strtotime($date);

    if(date('D', $ts) === 'Mon')
    {
        $start = $ts;
    }
    else
    {
        $start = (date('w', $ts) == 0) ? $ts : strtotime('last monday', $ts);
    }
    
    if(date('D', $ts) === 'Sun')
    {
        $start = (date('w', $ts) == 0) ? $ts : strtotime('last monday', $ts);
    }
    return array(date('Y-m-d', $start).' 00:00:00',
                 date('Y-m-d', strtotime('next sunday', $start)).' 23:59:59');
}

function payu_validate_transaction($txnid,$config=array()) {
    if (empty($txnid)) {
        return false;
    }

    $ch = curl_init();

    if($config['VERSION']=='NEW')
    {
        $post_url = NEW_PAYU_TXN_VALIDATE_BASE_URL_PRO.'/merchant/postservice?form=2';
        $data = $config['MERCHANT_KEY'].'|verify_payment|'.$txnid.'|'.$config['AUTH_HEADER'];
        $hash = urlencode(hash("sha512", $data));
        $data = 'key='.$config['MERCHANT_KEY'].'&command=verify_payment&var1='.$txnid.'&hash='.$hash;
        
        $header = array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded'
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    else{
        $postData = array();
        $postData['merchantKey'] = $config['MERCHANT_KEY'];
        $postData['merchantTransactionIds'] = $txnid;
        $postNow = http_build_query($postData);
        $post_url = $config['TXN_VALIDATE_BASE_URL'] . "/payment/payment/chkMerchantTxnStatus?" . $postNow;

        $header = array(
            'Authorization: ' . $config['AUTH_HEADER']
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, TRUE);

    }
    
    curl_setopt($ch, CURLOPT_URL, $post_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $out = curl_exec($ch);
    // print_r(json_decode($out,true));die;
    //if got error
    if (curl_errno($ch)) {
        $c_error = curl_error($ch);
        if (empty($c_error)) {
            $c_error = 'Some server error';
        }
        return array('status' => 'FAILURE', 'error' => $c_error);
    }
    $out = json_decode(trim($out),true);

    if (isset($out['status']) && in_array($out['status'],[0,1])) {
        return array('status' => 'SUCCESS', 'result' => $out);
    }

    $c_error = "";
    if (isset($out['message'])) {
        $c_error = $out['message'];
    }

    return array('status' => 'FAILURE', 'error' => $c_error);
}

function vpay_validate_transaction($txnid,$config) {
    if (empty($txnid)) {
        return false;
    }

    $postData = array();
    $postData['mid'] = $config['v_mid'];
    $postData['txnid'] = $txnid;
    $post_url = $config['v_base_url'].'/api/get_txn';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_URL, $post_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
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
    return array('status' => 'SUCCESS', 'result' => $out);
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
        $updateTransectionData = array();
        $updateTransectionData['authorization_code'] = $result['data']['authorization']['authorization_code'];
        $updateTransectionData['bank_name'] = $result['data']['authorization']['bank'];
        $updateTransectionData['int_status'] = $result['status'];
        $updateTransectionData['text_status'] =$result['data']['status'];
        $updateTransectionData['transaction_id'] =$result['data']['reference'];;
        $updateTransectionData['amount'] =($result['data']['amount']/100);
        return array('status' => 'SUCCESS','result'=>$updateTransectionData);
    } else{
        return array('status' => 'FAILURE');
    }
}

function convert_to_client_timezone($datetime,$format)
{
    $date = new DateTime($datetime);
    $tz = new DateTimeZone(CLIENT_TIME_ZONE);
    $date->setTimezone($tz);
    //print_r($date);die;
   return $deadline_date   = $date->format($format);
}


function get_razorpay_txn_status($txnid,$config){
    $api_key = $config['r_key']; 
    $secret_key = $config['r_secret']; 
    $order_id = $txnid;
    $url = 'https://api.razorpay.com/v1/orders/'.$order_id."/payments";
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

function get_prize_distribution_data_for_PC($winners_number='')
{
    $prize_distribution_list = array();
    $prize_distribution_list[1] = array("min" => 1, "max" => 1, "per" => 100);
    $prize_distribution_list[2][0] = array(array("min" => 1, "max" => 1, "per" => 60), array("min" => 2, "max" => 2, "per" => 40));
    $prize_distribution_list[2][1] = array(array("min" => 1, "max" => 1, "per" => 70), array("min" => 2, "max" => 2, "per" => 30));
    $prize_distribution_list[3][0] = array(array("min" => 1, "max" => 1, "per" => 50), array("min" => 2, "max" => 2, "per" => 30), array("min" => 3, "max" => 3, "per" => 20));
    $prize_distribution_list[3][1] = array(array("min" => 1, "max" => 1, "per" => 60), array("min" => 2, "max" => 2, "per" => 30), array("min" => 3, "max" => 3, "per" => 10));
    $prize_distribution_list[4][0] = array(array("min" => 1, "max" => 1, "per" => 45), array("min" => 2, "max" => 2, "per" => 30), array("min" => 3, "max" => 3, "per" => 20), array("min" => 4, "max" => 4, "per" => 5));
    $prize_distribution_list[4][1] = array(array("min" => 1, "max" => 1, "per" => 50), array("min" => 2, "max" => 2, "per" => 25), array("min" => 3, "max" => 3, "per" => 20), array("min" => 4, "max" => 4, "per" => 5));
    $prize_distribution_list[5][0] = array(array("min" => 1, "max" => 1, "per" => 45), array("min" => 2, "max" => 2, "per" => 25), array("min" => 3, "max" => 3, "per" => 15), array("min" => 4, "max" => 4, "per" => 10), array("min" => 5, "max" => 5, "per" => 5));
    $prize_distribution_list[5][1] = array(array("min" => 1, "max" => 1, "per" => 50), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 15), array("min" => 4, "max" => 4, "per" => 10), array("min" => 5, "max" => 5, "per" => 5));
    $prize_distribution_list[6][0] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 18), array("min" => 4, "max" => 4, "per" => 15), array("min" => 5, "max" => 5, "per" => 12), array("min" => 6, "max" => 6, "per" => 5));
    $prize_distribution_list[6][1] = array(array("min" => 1, "max" => 1, "per" => 32), array("min" => 2, "max" => 2, "per" => 22), array("min" => 3, "max" => 3, "per" => 18), array("min" => 4, "max" => 4, "per" => 13), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 6, "per" => 5));
    $prize_distribution_list[7][0] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17.5), array("min" => 4, "max" => 4, "per" => 12.5), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 7, "per" => 10));
    $prize_distribution_list[7][1] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 25), array("min" => 3, "max" => 3, "per" => 20), array("min" => 4, "max" => 4, "per" => 15), array("min" => 5, "max" => 5, "per" => 7), array("min" => 6, "max" => 7, "per" => 3));
    $prize_distribution_list[8][0] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17.5), array("min" => 4, "max" => 4, "per" => 12.5), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 8, "per" => 10));
    $prize_distribution_list[8][1] = array(array("min" => 1, "max" => 1, "per" => 25), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17.5), array("min" => 4, "max" => 4, "per" => 12.5), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 8, "per" => 15));
    $prize_distribution_list[9][0] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17.5), array("min" => 4, "max" => 4, "per" => 12.5), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 9, "per" => 10));
    $prize_distribution_list[9][1] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17), array("min" => 4, "max" => 4, "per" => 13), array("min" => 5, "max" => 5, "per" => 5), array("min" => 6, "max" => 9, "per" => 15));
    $prize_distribution_list[10][0] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17.5), array("min" => 4, "max" => 4, "per" => 12.5), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 10, "per" => 10));
    $prize_distribution_list[10][1] = array(array("min" => 1, "max" => 1, "per" => 25), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 15), array("min" => 4, "max" => 4, "per" => 13), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 10, "per" => 17));
    if ($winners_number != '')
    {
        return $prize_distribution_list[$winners_number][0];
    }

    return $prize_distribution_list;
}

function get_time_diff_secs($date1,$date2)
{
    $date2 = strtotime($date2);
    $date1 = strtotime($date1);
    return $differenceInSeconds = $date2 - $date1;
    //return round(abs($date2 - $date1) / 60,2);
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
            "result"=>$response,
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

function convert_normal_to_mongo($normal_date)
{
	return new MongoDB\BSON\UTCDateTime(strtotime($normal_date)*1000);
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
 * veryfying ifantasy transactions
 */
function get_ifantasy_txn_status($data){
    
    $key = $data['key'];
    $member_id = $data['member_id'];
    $txnid = $data['txnid'];
    $fields = array(
        "order_id"  => 'IFNTSY'.$txnid,
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
    error_log("\n".format_date().' Ifantasy_cron: '.$response.'<br>',3,'/var/www/html/cron/application/logs/payment.log');
    curl_close($ch);
    $response = json_decode($response, true);
    if(strtoupper($response['status'])=='SUCCESSFUL') 
    {
        return array('status' => 'SUCCESS', 'result' => $response);
    }elseif(strtoupper($response['status'])=='FAILED')
    {
        return array('status' => 'FAILURE', 'result' => $response);
    }
    }
    catch(Exception $e){
        $this->api_response_arry['error'] = 'Error : '.$e;
        error_log("\n".format_date().' Ifantasy_cron_error: '.json_encode($this->api_response_arry['error']).'<br>',3,'/var/www/html/cron/application/logs/payment.log');
        return ;
    }  
}

/**
     * function for cashfree payment verification
     * @param txnid
     */

    function get_cashfree_txn_status($txnid,$data)
    {
        
       $paymentUrl = CASHFREE_TESTPAY_URL.'orders/'.$txnid.'/payments';
       $orderUrl = CASHFREE_TESTPAY_URL.'orders/'.$txnid;
       if($data['mode'] == 'PRODUCTION') {
           $paymentUrl = CASHFREE_PRODPAY_URL.'orders/'.$txnid.'/payments';
           $orderUrl = CASHFREE_PRODPAY_URL.'orders/'.$txnid;
       }

       try {
           $curl = curl_init();
           curl_setopt_array($curl, array(
           CURLOPT_URL => $paymentUrl, 
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
           $is_success = false;
        //    error_log("\n".'Cron Payment cashfree : '.json_encode($response).'<br>',3,'/var/www/html/cron/application/logs/cashfree.log');
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

           // order dtails check 
           if(!$response && $header == 200)
           {
               try {
                   $curl = curl_init();
                   curl_setopt_array($curl, array(
                   CURLOPT_URL => $orderUrl, 
                   CURLOPT_RETURNTRANSFER => true,
                   CURLOPT_ENCODING => '',
                   CURLOPT_MAXREDIRS => 10,
                   CURLOPT_TIMEOUT => 0,
                   CURLOPT_FOLLOWLOCATION => true,
                   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                   CURLOPT_CUSTOMREQUEST => 'GET',
                   CURLOPT_HTTPHEADER => array(
                    'X-Client-Id: '.$data['app_id'],
                    'X-Client-Secret: '.$data['secret_key'],
                       'x-api-version: '.$data['app_version'],
                       'Content-Type: application/json'
                   ),
                   ));
       
                   $response = curl_exec($curl);
                   
                //    error_log("\n".'Cron Order cashfree : '.$response.'<br>',3,'/var/www/html/cron/application/logs/cashfree.log');
                   $response = json_decode($response,true);
                   curl_close($curl);
                   if($response['order_status']=='ACTIVE')
                   {
                       return array('response'=>$response,'is_active'=>1);
                   }else if($response['order_status']=='EXPIRED')
                   {
                       return array('response'=>$response,'is_active'=>2);
                   }
               }catch(Exception $e){
                  return false;
               }
           }
           return array('response'=>$response,'is_active'=>3);

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
        if (LOG_TX) {
            error_log("\n\n".format_date().' CRON STATUS CHECK DATA : '.json_encode($response).'<br>',3,'/var/www/html/cron/application/logs/cashierpay.log');
        }
        // echo " status api response <pre>";print_r($response);exit;
        return $response;
        }
        catch(Exception $e){
            $this->api_response_arry['error'] = 'Error : '.$e;
            $this->api_response();
        } 

}

function get_paylogic_txn_status($data,$app_id)
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

     /**
     * response decryption
     * @param $data, $key
     * @return Array
     */
    function paylogic_decrypt($data,$key){
        $iv = substr($key,0,16);
        $cipher="aes-256-cbc";
        if(strlen($iv) >0){   
            $data=openssl_decrypt($data, $cipher, $key, 0, $iv); 
            $final = json_decode($data,true);
        }
        return $final;
    }

function reset_contest_prize_data($contest)
{
    if(empty($contest)){
        return false;
    }

    $prize_data = json_decode($contest['prize_distibution_detail'],TRUE);
    if(isset($contest['guaranteed_prize']) && $contest['guaranteed_prize'] == "2"){
        return $prize_data;
    }
    $per_user_prize = isset($contest['per_user_prize']) ? $contest['per_user_prize'] : 0;
    $total_user = $contest['total_user_joined'];
    $total_per = array_sum(array_column($prize_data,"per"));
    $min_prize = $total_user * $contest['entry_fee'];
    $host_rake = 0;
    if(isset($contest['host_rake'])){
        $host_rake = $contest['host_rake'];
    }
    $contest['site_rake'] = $contest['site_rake'] + $host_rake;
    if(isset($contest['is_private']) && $contest['is_private'] == "1"){
        $min_prize = $min_prize - (($min_prize * $contest['site_rake']) / 100);
    }

    $max_prize = $contest['size'] * $contest['entry_fee'];
    $max_prize = $max_prize - (($max_prize * $contest['site_rake']) / 100);
    $winner_per = 0;
    $prize_arr = array();
    $per_arr = array("0"=>"0","1"=>"0","2"=>"0","3"=>"0");
    $win_per_arr = array("0"=>"0","1"=>"0","2"=>"0","3"=>"0");
    foreach($prize_data as $row){
        if(!isset($row['prize_type'])){
            $row['prize_type'] = "1";
        }
        $per_arr[$row['prize_type']] = $per_arr[$row['prize_type']] + $row['per'];
        if($total_user >= $row['max']){
            $prize_arr[] = $row;
            $win_per_arr[$row['prize_type']] = $win_per_arr[$row['prize_type']] + $row['per'];
        }else if($total_user >= $row['min'] && $total_user < $row['max']){
            $winner = $row['max'] - $row['min'] + 1;
            $per = number_format(($row['per'] / $winner),2,".","");
            $user_per = (($total_user - $row['min'] + 1) * $per);
            $win_per_arr[$row['prize_type']] = $win_per_arr[$row['prize_type']] + $user_per;
            $row['per'] = $user_per;
            $row['max'] = $total_user;
            $prize_arr[] = $row;
        }
    }
    //echo "<pre>";print_r($prize_arr);die;
    foreach($prize_arr as &$row){
        if($row['prize_type'] != "3"){
            $remain_per = $per_arr[$row['prize_type']] - $win_per_arr[$row['prize_type']];
            $win_per_val = $win_per_arr[$row['prize_type']];
            if($win_per_val == 0){
                $win_per_val = 1;
            }
            $row['per'] = number_format($row['per'] + (($row['per'] / $win_per_val) * $remain_per),2,".","");
            $amount = number_format((($min_prize * $row['per']) / 100),2,".","");
            if($per_user_prize == "1"){
                $person_count = ($row['max'] - $row['min']) + 1;
                $amount = truncate_number_only($amount / $person_count);

            }
            if(isset($row['prize_type']) && $row['prize_type'] == 2){
                $amount = ceil($amount);
            }
            $row['amount'] = $row['min_value'] = $amount;
            $row['max_value'] = number_format((($max_prize * $row['per']) / 100),2,".","");
        }
    }
    //echo "<pre>";print_r($prize_arr);die;
    return $prize_arr;
}

//paypal methods
function signature_paypal_validate_transaction($txnid){
    if(PAYPAL_PG_MODE=='TEST'){
        $url = 'https://api-3t.sandbox.paypal.com/nvp';
    }
    else{
        $url = 'https://api-3t.paypal.com/nvp';
    }

    $data = array(
        'USER' => PAYPAL_USERNAME, 
        //'USER' => 'amit.sharma_api1.vinfotech.com', 
        'PWD' => PAYPAL_PASSWORD, 
        'SIGNATURE' => PAYPAL_SIGNATURE, 
        'METHOD' =>'GetTransactionDetails', 
        'VERSION' => '123', 
        'TransactionID' => $txnid
    );
    $data = http_build_query($data);
    return $transactionInfo = make_post_call($url,$data);
}

function make_post_call($url, $postdata) {
    $token = generate_access_token();
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
    print_r($decode_response);die;
    $info = curl_getinfo($curl);
    curl_close($curl); // close cURL handler

    return $decode_response;
}

function generate_access_token(){

    $ch = curl_init();
    $clientId = PAYPAL_CLIENT_ID;
    $secret = PAYPAL_SECRET_KEY;
    if(PAYPAL_PG_MODE=='TEST'){
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
        $json = json_decode($result);
        return $json->access_token;
    }

    curl_close($ch); //THIS CODE IS NOW WORKING!
}

/**
 * send twilio sms
 * @param array $post_data
 * @return array
 */
if (!function_exists('send_twilio_sms')) {

    function send_twilio_sms($post_data,$config=[]) {
        $sid    = $config['sms_gateway_route_id'];
        $url    = $config['sms_gateway_api_endpoint'];
        $token  = $config['sms_gateway_auth_key'];
        $from   = $config['sms_gateway_sender_id'];
        $message= isset($post_data['message']) ? $post_data['message'] : "";
        $phone_code= isset($post_data['phone_code']) ? $post_data['phone_code'] : "";
        $to     = '+'.$phone_code.$post_data['mobile'];

        
        $body   = $message;
        $data   = array (
            'From'  => $from,
            'To'    => $to,
            'Body'  => $body,
        );
        $post = http_build_query($data);
        $curl = curl_init($url );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD,$sid.":".$token);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $curl_exec = curl_exec($curl);
        curl_close($curl);

        $response_array=json_decode($curl_exec,true);

        if(isset($response_array['code']) && !empty($response_array['code'])){
            // print_R($response_array);die;
            if(LOG_TX){
                log_message('error', ' twillo  '.json_encode($response_array));
            }
            return array("response" => $response_array['message']);
        }else{
           return $response_array;
        }
    }
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

function get_timezone($date='',$format='',$tz_arr=array())
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
                    $date = date('Y-m-d H:i:s',$date);
                    $date = new DateTime($date, new DateTimeZone('UTC'));
                    // $date = new DateTime(strtotime($date), new DateTimeZone('UTC'));
                    $date->setTimezone(new DateTimeZone($tz_name));
                    $converted_date = $date->format($format);
                    return array("date"=>$converted_date,"tz"=>$time_zone);
            }else{
                    return $timezone_list;
            }
}

if (!function_exists('second_inning_game_interval')) {
    function second_inning_game_interval($format) 
    {
        switch ($format) 
        { 
            //ODI  interval in minutes
            case '1':
                $interval = 210;
                break;
            // In case of Cricket (T20)
            case '3':
                $interval = 80;
                break;
            default:
                $interval = 80;
        }
        return $interval;
    }
}    