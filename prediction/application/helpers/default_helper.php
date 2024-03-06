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
                    $img .= ($image && $image != "jersey_default.png") ? $image : 'kabaddi_jersey.png';
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
 * add jersey
 * @param array $arr
 * @return array
 */
if (!function_exists('add_jersey')) {

    function add_jersey($arr) {
        if (!empty($arr['jersey'])) {
            $arr['jersey'] = get_image(1, $arr['jersey']);
        } else {
            $arr['jersey'] = get_image(1, '');
        }
        return $arr;
    }

}

/**
 * date sort
 * @param string $a
 * @param string $b
 * @return int
 */
if (!function_exists('date_sort')) {

    function date_sort($a, $b) {
        return strtotime($a) - strtotime($b);
    }

}

/**
 * prize distibution detail
 * @param array $prize_pool_data
 * @param int $total
 * @return array
 */
if (!function_exists('prize_distibution_detail')) {

    function prize_distibution_detail($prize_pool_data, $total) {
        $data = array();
        $total_prize_pool = 0;
        foreach ($prize_pool_data as $key => $value) {
            $total_prize_pool += $value;
            $data[] = array(
                "min" => $key + 1,
                "max" => $key + 1,
                "per" => ceil((($value * 100) / $total) * 2) / 2,
                "amount" => $value
            );
        }

        $data['prize_pool_data'] = $data;
        $data['total_prize_pool'] = $total_prize_pool;
        return $data;
    }

}

/**
 * get group list data
 * @return array
 */
if (!function_exists('get_group_list_data')) {

    function get_group_list_data(){
        $CI =& get_instance();
        $group_list = array();
        $group_list[] = array("group_id"=>"1","group_name"=>$CI->lang->line("group_name_1"),"description"=>$CI->lang->line("group_description_1"),"icon"=>"mega_contest.png");
        $group_list[] = array("group_id"=>"4","group_name"=>$CI->lang->line("group_name_4"),"description"=>$CI->lang->line("group_description_4"),"icon"=>"new_user_challenge.png");
        $group_list[] = array("group_id"=>"9","group_name"=>$CI->lang->line("group_name_9"),"description"=>$CI->lang->line("group_description_9"),"icon"=>"hotcontest.png");
        $group_list[] = array("group_id"=>"2","group_name"=>$CI->lang->line("group_name_2"),"description"=>$CI->lang->line("group_description_2"),"icon"=>"head_to_head.png");
        $group_list[] = array("group_id"=>"8","group_name"=>$CI->lang->line("group_name_8"),"description"=>$CI->lang->line("group_description_8"),"icon"=>"gangwar.png");
        $group_list[] = array("group_id"=>"12","group_name"=>$CI->lang->line("group_name_12"),"description"=>$CI->lang->line("group_description_12"),"icon"=>"contest_champions.png");
        $group_list[] = array("group_id"=>"10","group_name"=>$CI->lang->line("group_name_10"),"description"=>$CI->lang->line("group_description_10"),"icon"=>"winnertakesall.png");
        $group_list[] = array("group_id"=>"5","group_name"=>$CI->lang->line("group_name_5"),"description"=>$CI->lang->line("group_description_5"),"icon"=>"more_contest.png");
        $group_list[] = array("group_id"=>"3","group_name"=>$CI->lang->line("group_name_3"),"description"=>$CI->lang->line("group_description_3"),"icon"=>"double_money.png");
        $group_list[] = array("group_id"=>"11","group_name"=>$CI->lang->line("group_name_11"),"description"=>$CI->lang->line("group_description_11"),"icon"=>"everyoneWins.png");
        $group_list[] = array("group_id"=>"6","group_name"=>$CI->lang->line("group_name_6"),"description"=>$CI->lang->line("group_description_6"),"icon"=>"free_contest.png");
        $group_list[] = array("group_id"=>"7","group_name"=>$CI->lang->line("group_name_7"),"description"=>$CI->lang->line("group_description_7"),"icon"=>"private_contest.png");
        return $group_list;
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

function get_exclude_class_methods(){
    $class = array();
    $method = array("lobby/get_lobby_filter","lobby/get_fixture_list","lobby/get_filter_leagues","lobby/get_pickem_detail","prediction/get_lobby_fixture",'prediction/get_prediction_participants','prediction/get_prediction_leaderboard','open_predictor/get_lobby_fixture','fixed_open_predictor/get_lobby_fixture');
    $event_arr = array();
    $event_arr['class'] = $class;
    $event_arr['method'] = $method;
    return $event_arr;
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
