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

function get_dates_from_range($start, $end, $format = 'Y-m-d') { 
        
    // Declare an empty array 
    $array = array(); 
    
    // Variable that store the date interval 
    // of period 1 day 
    $interval = new DateInterval('P1D'); 

    $real_end = new DateTime($end); 
    //$real_end->add($interval); 

    $period = new DatePeriod(new DateTime($start), $interval, $real_end); 

    // Use loop to store date into array 
    foreach($period as $date) {                  
        $array[] = $date->format($format);  
    } 

    $array[] = $real_end->format($format); 
    // Return the array elements 
    return $array; 
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
        }

        if(isset($post_data['page']) && $post_data['page'] != "")
        {
            $page = $post_data['page'];
        }

        if(empty($page)) {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;
        return array("offset"=>$offset,"limit"=>$limit);
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

/**
 * convert minute to hour minute
 * @param int $minutes
 * @return array
 */
if (!function_exists('convert_minute_to_hour_minute')) {
    function convert_minute_to_hour_minute($minutes = 0) {
        $hour = intdiv($minutes, 60);
        $minute = ($minutes % 60);
        return array("hour" => $hour, "minute" => $minute);
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

function get_date_diff_in_months($current_date,$past_date)
{ 
    $d1 = new DateTime($past_date);
    $d2 = new DateTime($current_date);
    $interval = $d2->diff($d1);
    return $interval->format('%m');
}

function get_exclude_class_methods(){
    $class = array();
    $method = array("lobby/get_lobby_fixture","league/get_sport_list","league/get_sport_leagues","league/get_league_detail","tournament/get_lobby_tournament_list");
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

if(!function_exists('generateUniqueID')) {
    function generate_uid($len=12) {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $id_str = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $len; $i++) {
            $n = rand(0, $alphaLength);
            $id_str[] = $alphabet[$n];
        }
        return implode($id_str);
    }
}

function convert_hour_minute_to_minute($hours,$minutes){
    $hour_minute = $hours * 60;
    return intval($hour_minute + $minutes);
}

function convert_minute_to_hour_minute($minutes = 0){
    $hour = intdiv($minutes, 60);
    $minute = ($minutes % 60);
    return array("hour"=>$hour,"minute"=>$minute);
}

function get_media_setting($type){
    $media_arr = array();
    $media_arr['sponsor'] = array("min_h"=>"240","max_h"=>"240","min_w"=>"1300","max_w"=>"1300","size"=>"2","type"=>array("jpg","png","jpeg"),"path"=>"upload/pickem");
      $media_arr['logo'] = array("min_h"=>"100","max_h"=>"200","min_w"=>"200","max_w"=>"470","size"=>"1","type"=>array("jpg","png","jpeg"),"path"=>"upload/pickem");
    if(isset($media_arr[$type])){
        return $media_arr[$type];
    }else{
        return array();
    }
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