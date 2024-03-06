<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

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

function camelCaseString($key){
    $key = ucfirst(implode('', array_map('ucfirst', explode('_', $key))));
    return $key;
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

function get_user_ip_address(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function get_exclude_class_methods(){
    $class = array();
    $method = array("lobby/get_lobby_fixture");
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
*This Helper will sort asosciative array based on another index array*
* @param main array and index array 
*/
function ksort_arr (&$arr, $index_arr) {
    $arr_t=array();
    foreach($index_arr as $i=>$v) {
        foreach($arr as $k=>$b) {
           if ($k==$v) $arr_t[$k]=$b;
        }
    }
    $arr=$arr_t;
}

/**
 * Get pagination offset.
 * @param int $page_no
 * @param int $lmiit
 * @return int
 */
if (!function_exists('get_pagination_data')) {
    function get_pagination_data($post_data) {
        $page = 1;
        $limit = RECORD_LIMIT;
        if(isset($post_data['limit']) && $post_data['limit'] != "") 
        {
            $limit = $post_data['limit'];
        }else if(isset($post_data['page_size']) && $post_data['page_size'] != "") 
        {
            $limit = $post_data['page_size'];
        }

        if(isset($post_data['page']) && $post_data['page'] != "")
        {
            $page = $post_data['page'];
        }else if(isset($post_data['page_no']) && $post_data['page_no'] != "")
        {
            $page = $post_data['page_no'];
        }

        if(empty($page)) {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;
        return array("offset"=>$offset,"limit"=>$limit);
    }
}
function get_tz_diff($tz_arr=array())
{
    if(empty($tz_arr)) return ;
    $timezone_list = array(
        "IST"   =>"+05:30",
        "UTC"   =>"+00:00",
        "ET"    =>"-04:00",
        "GMT"   =>"+01:00",
        "JST"   =>"+09:00",
        "AEST"  =>"+10:00",
        "EET"   =>"+03:00",
        "HST"   =>"-10:00",
        "CET"   =>"+01:00"
);

return $timezone_list[$tz_arr['key_value']];
}

/**
 * get converted date acc to client time zone.
 * @return 
 */
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


function get_media_setting($type){
    $media_arr = array();
    $media_arr['flag'] = array("min_h"=>"128","max_h"=>"128","min_w"=>"128","max_w"=>"128","size"=>"2","type"=>array("jpg","png","jpeg"),"path"=>"upload/flag");
    $media_arr['jersey'] = array("min_h"=>"161","max_h"=>"161","min_w"=>"155","max_w"=>"155","size"=>"2","type"=>array("jpg","png","jpeg"),"path"=>"upload/jersey");
    $media_arr['player'] = array("min_h"=>"161","max_h"=>"200","min_w"=>"155","max_w"=>"188","size"=>"2","type"=>array("jpg","png","jpeg"),"path"=>"upload/jersey");
    if(isset($media_arr[$type])){
        return $media_arr[$type];
    }else{
        return array();
    }
}

function get_payout_list($type="",$pick=""){
    $payout = array();
    //powerplay
    $payout["2"]["6"]["6"] = 20;
    $payout["2"]["5"]["5"] = 15;
    $payout["2"]["4"]["4"] = 10;
    $payout["2"]["3"]["3"] = 5;
    $payout["2"]["2"]["2"] = 3;

    //flexplay
    $payout["1"]["6"]["6"] = "25";
    $payout["1"]["6"]["5"] = "2";
    $payout["1"]["6"]["4"] = "0.4";
    $payout["1"]["5"]["5"] = "10";
    $payout["1"]["5"]["4"] = "2";
    $payout["1"]["5"]["3"] = "0.4";
    $payout["1"]["4"]["4"] = "5";
    $payout["1"]["4"]["3"] = "1.5";
    $payout["1"]["3"]["3"] = "2.25";
    $payout["1"]["3"]["2"] = "1.25";
    $result = $payout;
    if($type != "" && isset($payout[$type])){
        $result = $payout[$type];
        if($pick != "" && isset($payout[$type][$pick])){
            $result = $payout[$type][$pick];
        }
    }
    return $result;
}


/**
 * Get pagination offset.
 * @param int $page_no
 * @param int $lmiit
 * @return int
 */
if (!function_exists('get_pagination_offset')) {

    function get_pagination_offset($page_no, $lmiit) {
        if (empty($page_no)) {
            $page_no = 1;
        }
        return ($page_no - 1) * $lmiit;
    }

}

function roundDown($number, $nearest){
    //round number
    return $number - fmod($number, $nearest);
}

function calculate_question_price($post_data){
    $total_val = 10.0;
    $prices = array('opt1' => $post_data['opt1'],'opt2' => $post_data['opt2']);
    $option_bids = array('opt1' => $post_data['opt1_bid'],'opt2' => $post_data['opt2_bid']);
    $total_bids = array_sum($option_bids);

    //Calculate the ratio of bids for each option
    $opt1_ratio = $option_bids['opt1'] / $total_bids;
    $opt2_ratio = $option_bids['opt2'] / $total_bids;

    //Calculate the updated prices based on the ratios
    $opt1_price = $total_val - number_format(($opt1_ratio * $total_val),1,".","");
    $opt1_price = roundDown($opt1_price,"0.5");
    $opt2_price = $total_val - $opt1_price;
    if($opt1_price > 9){
        $opt1_price = 9.0;
        $opt2_price = 1.0;
    }else if($opt2_price > 9){
        $opt1_price = 1.0;
        $opt2_price = 9.0;
    }
    $prices['opt1'] = $opt1_price;
    $prices['opt2'] = $opt2_price;
    return $prices;
}

/**
 * this function used for the create trade range
 * @params: cap
 */
function create_trade_range($cap=10){
    $low = 0.5;
    $step = 0.5;
    $high = $cap-$step;
    $trade_data = range($low,$high,$step);
    $res = array();
    foreach($trade_data as $val){
        $val = number_format($val,1,".","");
        $res[] = $val;
    }
    return $res;
}
function convert_hour_minute_to_minute($hours,$minutes){
    $hour_minute = $hours * 60;
    return intval($hour_minute + $minutes);
}