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

function convert_to_client_timezone($datetime,$format)
{
    $date = new DateTime($datetime);
    $tz = new DateTimeZone(CLIENT_TIME_ZONE);
    $date->setTimezone($tz);
    //print_r($date);die;
   return $deadline_date   = $date->format($format);
}

function get_week_start_date($week, $year) {
    $dateTime = new DateTime('today');
    $dateTime->setISODate($year, $week);
    $start_date = $dateTime->format('Y-m-d');
    return $start_date;
  }

 function validate_date($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}

function get_banned_state(){
    $state_list = array();
    $state_list['2'] = "Andhra Pradesh";
    $state_list['4'] = "Assam";
    $state_list['26'] = "Nagaland";
    $state_list['29'] = "Odisha";
    $state_list['34'] = "Sikkim";
    $state_list['36'] = "Telangana";
    return $state_list;
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
    $prize_distribution_list[7][0] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17.5), array("min" => 4, "max" => 4, "per" => 12.5), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 6, "per" => 5), array("min" => 7, "max" => 7, "per" => 5));
    $prize_distribution_list[7][1] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 25), array("min" => 3, "max" => 3, "per" => 20), array("min" => 4, "max" => 4, "per" => 15), array("min" => 5, "max" => 5, "per" => 7), array("min" => 6, "max" => 6, "per" => 1.5), array("min" => 7, "max" => 7, "per" => 1.5));
    $prize_distribution_list[8][0] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17.5), array("min" => 4, "max" => 4, "per" => 12.5), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 6, "per" => 3.33), array("min" => 7, "max" => 7, "per" => 3.33), array("min" => 8, "max" => 8, "per" => 3.33));
    $prize_distribution_list[8][1] = array(array("min" => 1, "max" => 1, "per" => 25), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17.5), array("min" => 4, "max" => 4, "per" => 12.5), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 6, "per" => 5), array("min" => 7, "max" => 7, "per" => 5), array("min" => 8, "max" => 8, "per" => 5));
    $prize_distribution_list[9][0] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17.5), array("min" => 4, "max" => 4, "per" => 12.5), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 6, "per" => 2.5), array("min" => 7, "max" => 7, "per" => 2.5), array("min" => 8, "max" => 8, "per" => 2.5), array("min" => 9, "max" => 9, "per" => 2.5));
    $prize_distribution_list[9][1] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17), array("min" => 4, "max" => 4, "per" => 13), array("min" => 5, "max" => 5, "per" => 5), array("min" => 6, "max" => 6, "per" => 3.75), array("min" => 7, "max" => 7, "per" => 3.75), array("min" => 8, "max" => 8, "per" => 3.75), array("min" => 9, "max" => 9, "per" => 3.75));
    $prize_distribution_list[10][0] = array(array("min" => 1, "max" => 1, "per" => 30), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 17.5), array("min" => 4, "max" => 4, "per" => 12.5), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 6, "per" => 2), array("min" => 7, "max" => 7, "per" => 2), array("min" => 8, "max" => 8, "per" => 2), array("min" => 9, "max" => 9, "per" => 2), array("min" => 10, "max" => 10, "per" => 2));
    $prize_distribution_list[10][1] = array(array("min" => 1, "max" => 1, "per" => 25), array("min" => 2, "max" => 2, "per" => 20), array("min" => 3, "max" => 3, "per" => 15), array("min" => 4, "max" => 4, "per" => 13), array("min" => 5, "max" => 5, "per" => 10), array("min" => 6, "max" => 6, "per" => 3.4), array("min" => 7, "max" => 7, "per" => 3.4), array("min" => 8, "max" => 8, "per" => 3.4), array("min" => 9, "max" => 9, "per" => 3.4), array("min" => 10, "max" => 10, "per" => 3.4));
    if ($winners_number != '')
    {
        return $prize_distribution_list[$winners_number];
    }

    return $prize_distribution_list;
}

function filter_blank_values($str)
{
    $arr = explode(',',$str);

   $updated_arr = array_filter($arr,function($value){ return !empty($value); });
   return implode(',',$updated_arr);
}

function get_miliseconds($date1,$date2)
{
	$date2 = strtotime($date2);
	$date1 = strtotime($date1);
	return $differenceInSeconds = ($date2 - $date1)*1000;
	//return round(abs($date2 - $date1) / 60,2);
}

 /**
 * [convert_date_to_time_zone used to convert date from one time zone to another time zone]
 * @param  [Date]   $time           [Date, which being converted]
 * @param  [string] $from_time_zone [From time zone]
 * @param  [string] $to_time_zone   [To time zone]
 * @param  [string] $format         [Reuired date time format]
 * @return [Date]                   [Converted Date]
 */        
function convert_date_to_time_zone($time, $from_time_zone="Asia/Kolkata", $to_time_zone="UTC", $format = 'Y-m-d H:i:s') 
{
    // create timeZone object , with from_time_zone
    $from = new DateTimeZone($from_time_zone);
    // create timeZone object , with to_time_zone
    $to = new DateTimeZone($to_time_zone);
    // read given time into ,from_time_zone
    $orignal_time = new DateTime($time, $from);    
    //print_r($orignal_time);       
    // fromte input date to ISO 8601 date (added in PHP 5). the create new date time object
    $to_time = new DateTime($orignal_time->format("c"));

    // set target time zone to $toTme ojbect.
    $to_time->setTimezone($to);
    //print_r($to_time);  
    // return reuslt.
    return $to_time->format($format);
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

function manual_feed_extra_values($is_full="All"){
    $extra = array(
                array("id"=>1,"name"=>"NB0","value"=>0),array("id"=>2,"name"=>"NB1","value"=>1),array("id"=>3,"name"=>"NB2","value"=>2),array("id"=>4,"name"=>"NB3","value"=>3),array("id"=>5,"name"=>"NB4","value"=>4),array("id"=>6,"name"=>"NB5","value"=>5),array("id"=>7,"name"=>"NB6","value"=>6),
                array("id"=>8,"name"=>"WD0","value"=>0),array("id"=>9,"name"=>"WD1","value"=>1),array("id"=>10,"name"=>"WD2","value"=>2),array("id"=>11,"name"=>"WD3","value"=>3),array("id"=>12,"name"=>"WD4","value"=>4),array("id"=>13,"name"=>"WD5","value"=>5),array("id"=>14,"name"=>"WD6","value"=>6),
                array("id"=>15,"name"=>"B0","value"=>0),array("id"=>16,"name"=>"B1","value"=>1),array("id"=>17,"name"=>"B2","value"=>2),array("id"=>18,"name"=>"B3","value"=>3),array("id"=>19,"name"=>"B4","value"=>4),array("id"=>20,"name"=>"B5","value"=>5),array("id"=>21,"name"=>"B6","value"=>6),
                array("id"=>22,"name"=>"LB0","value"=>0),array("id"=>23,"name"=>"LB1","value"=>1),array("id"=>24,"name"=>"LB2","value"=>2),array("id"=>25,"name"=>"LB3","value"=>3),array("id"=>26,"name"=>"LB4","value"=>4),array("id"=>27,"name"=>"LB5","value"=>5),array("id"=>28,"name"=>"LB6","value"=>6)
            );
    return $extra;
}

function get_extra_ball_name($extra_id){
    $extra = "";
    if($extra_id >= 1 && $extra_id <= 7){
        $extra = "NB";
    }else if($extra_id >= 8 && $extra_id <= 14){
        $extra = "WD";
    }else if($extra_id >= 15 && $extra_id <= 21){
        $extra = "B";
    }else if($extra_id >= 22 && $extra_id <= 28){
        $extra = "LB";
    }
    return $extra;
}

function manual_feed_other_values(){
    $others = array(
                array("id"=>1,"name"=>"3","value"=>3),
                array("id"=>2,"name"=>"5","value"=>5),
                array("id"=>3,"name"=>"7","value"=>7),
            );
    return $others;
}

function trim_trailing_zeroes($nbr) {
    if(strpos($nbr,'.')!==false) $nbr = rtrim($nbr,'0');
    return rtrim($nbr,'.') ?: '0';
}

function get_default_odds($inn_over){
    $odds = array();
    $odds['1_1'] = array("1"=>"14","2"=>"35","3"=>"255","4"=>"80","5"=>"500","6"=>"140","7"=>"350","8"=>"600");
    $odds['1_2'] = array("1"=>"16","2"=>"40","3"=>"265","4"=>"60","5"=>"450","6"=>"150","7"=>"300","8"=>"600");
    $odds['1_3'] = array("1"=>"18","2"=>"30","3"=>"230","4"=>"55","5"=>"300","6"=>"175","7"=>"250","8"=>"600");
    $odds['1_4'] = array("1"=>"16","2"=>"25","3"=>"240","4"=>"58","5"=>"280","6"=>"175","7"=>"230","8"=>"600");
    $odds['1_5'] = array("1"=>"20","2"=>"22","3"=>"200","4"=>"55","5"=>"235","6"=>"185","7"=>"240","8"=>"600");
    $odds['1_6'] = array("1"=>"22","2"=>"25","3"=>"235","4"=>"50","5"=>"240","6"=>"190","7"=>"225","8"=>"600");
    $odds['1_7'] = array("1"=>"15","2"=>"10","3"=>"180","4"=>"120","5"=>"430","6"=>"220","7"=>"300","8"=>"600");
    $odds['1_8'] = array("1"=>"20","2"=>"12","3"=>"170","4"=>"110","5"=>"280","6"=>"250","7"=>"310","8"=>"600");
    $odds['1_9'] = array("1"=>"22","2"=>"14","3"=>"160","4"=>"105","5"=>"255","6"=>"230","7"=>"225","8"=>"600");
    $odds['1_10'] = array("1"=>"24","2"=>"15","3"=>"130","4"=>"110","5"=>"265","6"=>"230","7"=>"315","8"=>"600");
    $odds['1_11'] = array("1"=>"22","2"=>"12","3"=>"125","4"=>"105","5"=>"245","6"=>"260","7"=>"245","8"=>"600");
    $odds['1_12'] = array("1"=>"20","2"=>"15","3"=>"150","4"=>"100","5"=>"220","6"=>"255","7"=>"230","8"=>"600");
    $odds['1_13'] = array("1"=>"18","2"=>"14","3"=>"130","4"=>"115","5"=>"205","6"=>"220","7"=>"245","8"=>"600");
    $odds['1_14'] = array("1"=>"22","2"=>"12","3"=>"120","4"=>"100","5"=>"175","6"=>"230","7"=>"210","8"=>"600");
    $odds['1_15'] = array("1"=>"20","2"=>"15","3"=>"125","4"=>"80","5"=>"165","6"=>"200","7"=>"185","8"=>"600");
    $odds['1_16'] = array("1"=>"20","2"=>"12","3"=>"110","4"=>"90","5"=>"155","6"=>"180","7"=>"175","8"=>"600");
    $odds['1_17'] = array("1"=>"22","2"=>"14","3"=>"105","4"=>"75","5"=>"140","6"=>"155","7"=>"155","8"=>"600");
    $odds['1_18'] = array("1"=>"20","2"=>"15","3"=>"100","4"=>"72","5"=>"125","6"=>"140","7"=>"110","8"=>"600");
    $odds['1_19'] = array("1"=>"22","2"=>"17","3"=>"90","4"=>"70","5"=>"105","6"=>"120","7"=>"110","8"=>"600");
    $odds['1_20'] = array("1"=>"20","2"=>"21","3"=>"75","4"=>"65","5"=>"90","6"=>"100","7"=>"65","8"=>"600");

    $odds['2_1'] = array("1"=>"18","2"=>"35","3"=>"265","4"=>"70","5"=>"450","6"=>"140","7"=>"285","8"=>"600");
    $odds['2_2'] = array("1"=>"15","2"=>"35","3"=>"250","4"=>"60","5"=>"350","6"=>"135","7"=>"240","8"=>"600");
    $odds['2_3'] = array("1"=>"14","2"=>"30","3"=>"200","4"=>"55","5"=>"240","6"=>"160","7"=>"240","8"=>"600");
    $odds['2_4'] = array("1"=>"16","2"=>"28","3"=>"190","4"=>"50","5"=>"220","6"=>"180","7"=>"245","8"=>"600");
    $odds['2_5'] = array("1"=>"14","2"=>"25","3"=>"250","4"=>"55","5"=>"210","6"=>"200","7"=>"220","8"=>"600");
    $odds['2_6'] = array("1"=>"16","2"=>"28","3"=>"245","4"=>"52","5"=>"225","6"=>"190","7"=>"250","8"=>"600");
    $odds['2_7'] = array("1"=>"15","2"=>"12","3"=>"175","4"=>"120","5"=>"330","6"=>"200","7"=>"290","8"=>"600");
    $odds['2_8'] = array("1"=>"17","2"=>"14","3"=>"150","4"=>"115","5"=>"320","6"=>"210","7"=>"250","8"=>"600");
    $odds['2_9'] = array("1"=>"16","2"=>"13","3"=>"160","4"=>"100","5"=>"250","6"=>"200","7"=>"260","8"=>"600");
    $odds['2_10'] = array("1"=>"17","2"=>"12","3"=>"140","4"=>"110","5"=>"310","6"=>"230","7"=>"225","8"=>"600");
    $odds['2_11'] = array("1"=>"18","2"=>"13","3"=>"135","4"=>"100","5"=>"230","6"=>"225","7"=>"210","8"=>"600");
    $odds['2_12'] = array("1"=>"19","2"=>"12","3"=>"135","4"=>"105","5"=>"240","6"=>"230","7"=>"225","8"=>"600");
    $odds['2_13'] = array("1"=>"18","2"=>"14","3"=>"120","4"=>"110","5"=>"180","6"=>"200","7"=>"220","8"=>"600");
    $odds['2_14'] = array("1"=>"20","2"=>"13","3"=>"130","4"=>"100","5"=>"195","6"=>"225","7"=>"190","8"=>"600");
    $odds['2_15'] = array("1"=>"19","2"=>"14","3"=>"120","4"=>"90","5"=>"180","6"=>"200","7"=>"200","8"=>"600");
    $odds['2_16'] = array("1"=>"20","2"=>"14","3"=>"110","4"=>"95","5"=>"150","6"=>"185","7"=>"155","8"=>"600");
    $odds['2_17'] = array("1"=>"22","2"=>"15","3"=>"125","4"=>"75","5"=>"155","6"=>"155","7"=>"160","8"=>"600");
    $odds['2_18'] = array("1"=>"20","2"=>"16","3"=>"110","4"=>"80","5"=>"115","6"=>"150","7"=>"120","8"=>"600");
    $odds['2_19'] = array("1"=>"18","2"=>"17","3"=>"95","4"=>"90","5"=>"125","6"=>"135","7"=>"95","8"=>"600");
    $odds['2_20'] = array("1"=>"20","2"=>"22","3"=>"85","4"=>"70","5"=>"110","6"=>"130","7"=>"70","8"=>"600");

    if(isset($odds[$inn_over])){
        return $odds[$inn_over];
    }else{
        return array();
    }
}

// Function to get all the dates in given range 
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

function camelCaseString($key){
    $key = ucfirst(implode('', array_map('ucfirst', explode('_', $key))));
    return $key;
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