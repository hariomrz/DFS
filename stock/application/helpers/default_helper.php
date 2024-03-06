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

function get_minutes($date1,$date2)
{
    return $minutes = get_miliseconds($date1,$date2)/(1000*60);
}    

function get_cap_types()
{
    return array(1=> 'Large Cap',
                2=> 'Mid Cap',
                3=> 'Small Cap');
}

function get_team_name($schedule_date,$end_date,$team_short_name)
{
    $pl_name = date("dMy",strtotime($schedule_date))."-".date("Hi",strtotime($schedule_date))."-".date("Hi",strtotime($end_date)).$team_short_name;

    return $pl_name;
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

function reset_contest_prize_data($contest)
{
    if(empty($contest)){
        return false;
    }

    $prize_data = json_decode($contest['prize_distibution_detail'],TRUE);
    if(isset($contest['guaranteed_prize']) && $contest['guaranteed_prize'] == "2"){
        return $prize_data;
    }
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
    foreach($prize_arr as &$row){
        if($row['prize_type'] != "3"){
            $remain_per = $per_arr[$row['prize_type']] - $win_per_arr[$row['prize_type']];
            $row['per'] = number_format($row['per'] + (($row['per'] / $win_per_arr[$row['prize_type']]) * $remain_per),2,".","");
            $row['amount'] = $row['min_value'] = number_format((($min_prize * $row['per']) / 100),2,".","");
            $row['max_value'] = number_format((($max_prize * $row['per']) / 100),2,".","");
        }
    }
    return $prize_arr;
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

function get_exclude_class_methods(){
    $class = array();
    $method = array("lobby/get_lobby_fixture",
                    "lobby/get_fixture_contest",
                    "lobby/get_lobby_filter",
                    "lobby/get_fixture_details",
                    "lobby/stock_setting",
                    "predict/get_lobby_filter",
                    "predict/get_contest_list",
                    "livestockfantasy/get_contest_list",
                    "livestockfantasy/get_lobby_filter",
                    "stock/card",
                    "stock/statics",
                    "livestockfantasy/get_contest_list",
                    "livestockfantasy/get_lobby_filter"
                );
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



function camelCaseString($key){
    $key = ucfirst(implode('', array_map('ucfirst', explode('_', $key))));
    return $key;
    }
    