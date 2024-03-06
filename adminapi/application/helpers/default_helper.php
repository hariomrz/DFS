<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


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










































































/** OLD METHOD NEED TO FLTER AKR */

function format_date( $date = 'today' , $format = 'Y-m-d H:i:s' )
{
	if( $date=="today" )
	{
		if ( IS_LOCAL_TIME === TRUE )
		{
			$back_time = strtotime( BACK_YEAR );
			$dt = date( $format , $back_time );
		}
		else
		{
			$dt = date( $format );
		}
	}
	else
	{
		if( is_numeric ( $date ) )
		{
			$dt = date( $format , $date );
		}
		else
		{
			if( $date != null )
			{
				$dt = date( $format , strtotime ( $date ) );
			}
			else
			{
				$dt="--";
			}
		}
	}

// $path = ROOT_PATH.'date_time.php';
	
	$path = ROOT_PATH.'date_time.php';
	if(file_exists($path))
	{
		include($path);
	}
	else{
		$path = ROOT_PATH.'../date_time.php';
		if(file_exists($path))
		{
			include($path);
		}
	}
 
	
	if ( isset( $date_time ) && $date_time && (ENVIRONMENT !== 'production' ) )
	{
		$dt = date( $format , strtotime( $date_time ) );
	}
	return $dt;
	//return "2015-05-24 14:00:00";
}

function convert_mongo_to_normal_date($mongo_date)
{
	/********************retrieve time in UTC**********************************/
	$datetime = $mongo_date->toDateTime();
	return $datetime->format(DATE_RSS);
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

function prize_json()
{
    $json = '{  "Top 1":{"1":100},
    			"Top 3":{"1":55,"2":30,"3":15},
    			"Top 10":{"1":30,"2":20,"3":12,"4":9.25,"5":7.50,"6":6.25,"7":5.25,"8":4.25,"9":3.25,"10":2.25},
    			"Top 20":{"1":27.50,"2":17.50,"3":11.50,"4":8.50,"5":7.25,"6":5.75,"7":4.50,"8":3.00,"9":2.00,"10":1.50,"11":1.20,"12":1.20,"13":1.20,"14":1.20,"15":1.20,"16":1.00,"17":1.00,"18":1.00,"19":1.00,"20":1.00},
    			"Top 30%":{"30":100},
    			"Top 50%":{"50":100}
    		 }';
    return $json;
}
//[{"min":1,"max":1,"per":100,"amount":"7.04","row":""}]
function prize_distibution_detail($prize_pool_data,$total)
{
	$data = array();
	$total_prize_pool = 0; 
	foreach ($prize_pool_data as $key => $value) 
	{
		$total_prize_pool += $value;
		$data[] = array(
			"min" =>$key+1,
			"max" =>$key+1,
			"per" =>ceil((($value*100)/$total)*2)/2, 
			"amount" => $value
			);
	}

	$data['prize_pool_data'] = $data;
	$data['total_prize_pool'] = $total_prize_pool;
	return $data;
}

function format_num_callback($n)
{
    return floatval( str_replace(',', '', $n) );
}

function truncate_number( $number = 0 , $decimals = 2 )
{
	$point_index = strrpos( $number , '.' );
	if($point_index===FALSE) return $number;
	return substr( $number , 0 , $point_index + $decimals + 1 );
}

function debug( $msg , $die=FALSE )
{
	echo ( "<style> pre{background-color:chocolate;font-weight:bolder;} .debug{color: black;text-align:center;background-color:yellow;font-weight:bolder;padding:10px;font-size:14px;}</style>" );

	echo ("\n<p class='debug'>\n");

	echo ("MSG".time().": ");

	if ( is_array ( $msg ) )
	{
		echo ( "\n<pre>\n" );
		print_r ( $msg );
		echo ( "\n</pre>\n" );
	}
	elseif ( is_object ( $msg ) )
	{
		echo ( "\n<pre>\n" );
		var_dump ( $msg );
		echo ( "\n</pre>\n" );
	}
	else
	{
		echo ( $msg );
	}

	echo ( "\n</p>\n" );

	if ( $die )
	{
		die;
	}
}
if (!function_exists('array_column')) {
	function array_column($input, $column_key, $index_key = null)
	{
		if ( empty( $input ) )
		{
			return array();
		}

		if ($index_key !== null) {
			// Collect the keys
			$keys = array();
			$i = 0; // Counter for numerical keys when key does not exist

			foreach ($input as $row) {
				if (array_key_exists($index_key, $row)) {
					// Update counter for numerical keys
					if (is_numeric($row[$index_key]) || is_bool($row[$index_key])) {
						$i = max($i, (int)$row[$index_key] + 1);
					}

					// Get the key from a single column of the array
					$keys[] = $row[$index_key];
				} else {
					// The key does not exist, use numerical indexing
					$keys[] = $i++;
				}
			}
		}

		if ($column_key !== null) {
			// Collect the values
			$values = array();
			$i = 0; // Counter for removing keys

			foreach ($input as $row) {
				if (array_key_exists($column_key, $row)) {
					// Get the values from a single column of the input array
					$values[] = $row[$column_key];
					$i++;
				} elseif (isset($keys)) {
					// Values does not exist, also drop the key for it
					array_splice($keys, $i, 1);
				}
			}
		} else {
			// Get the full arrays
			$values = array_values($input);
		}

		if ($index_key !== null) {
			return array_combine($keys, $values);
		}

		return $values;
	}

}
function array_pluck($key, $array)
{
    if (is_array($key) || !is_array($array)) return array();
    $funct = create_function('$e', 'return is_array($e) && array_key_exists("'.$key.'",$e) ? $e["'. $key .'"] : null;');
    return array_map($funct, $array);
}

function replace_quotes($string)
{
	return preg_replace(array("/`/", "/'/", "/&acute;/"), "",$string);
}
function unique_multidim_array($array, $key) { 
    $temp_array = array(); 
    $i = 0; 
    $key_array = array(); 
    
    foreach($array as $val) { 
        if (!in_array($val[$key], $key_array)) { 
            $key_array[$i] = $val[$key]; 
            $temp_array[$i] = $val; 
        } 
        $i++; 
    } 
    return $temp_array; 
}
// sort for multipal array  
/**
 * [$arr1 description]
 * @var array
 * Example
 $arr1 = array(
    array('id'=>1,'name'=>'aA','cat'=>'cc'),
    array('id'=>2,'name'=>'aa','cat'=>'dd')
    );
 $arr2 = array_msort($arr1, array('name'=>SORT_DESC, 'cat'=>SORT_ASC));
// */
function array_msort($array, $cols)
{
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;

}

function add_quotes($str) {
    return sprintf("'%s'", $str);
	}

/**
 * [array_orderby description]
 * Summary :- 
 * @return [type] [description]
 * Example
$data[] = array('volume' => 67, 'edition' => 2);
$data[] = array('volume' => 86, 'edition' => 1);
$data[] = array('volume' => 85, 'edition' => 6);
$data[] = array('volume' => 98, 'edition' => 2);
$data[] = array('volume' => 86, 'edition' => 6);
$data[] = array('volume' => 67, 'edition' => 7);

// Pass the array, followed by the column names and sort flags
$sorted = array_orderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
 */
function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
            }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

function camelCaseString($key){
$key = ucfirst(implode('', array_map('ucfirst', explode('_', $key))));
return $key;
}


function getYouTubeIdFromURL($url) 
{
 $pattern = 
        '%^# Match any youtube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch\?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
        $%x'
        ;
    $result = preg_match($pattern, $url, $matches);


    if ($result) {
        return $matches[1];
    }
    return false;
}

function getVimeoThumb($videoLink)
{
	$image_url = parse_url($videoLink);

	$videoId = "";

	if (preg_match("/https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/", $videoLink, $id)) {
	    $videoId = $id[3];
	}

	if(!empty($videoId))
	{	
		$ch = curl_init('http://vimeo.com/api/v2/video/'.$videoId.'.php');
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		$a = curl_exec($ch);
		$hash = unserialize($a);

		if(!empty($hash[0]["thumbnail_large"]))
		{
			return $hash[0]["thumbnail_large"];
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

/**
*below Function for update email_balance via hashtable
*/
function email_bal_update($row,$post)
{
	$row["email_balance"] = $row["email_balance"]+ $post['value'];
	return $row;
}

function notification_bal_update($row,$post)
{
	$row["notification_balance"] = $row["notification_balance"]+ $post['value'];
	return $row;
}

function get_active_payment_list($data){
	$gateway_list = array();
	if($data['allow_payumoney']['key_value'] == 1){
		$gateway_list[1] = "PayUMoney";
	}
	if($data['allow_paytm']['key_value'] == 1){
		$gateway_list[2] = "PayTM";
	}
	if($data['allow_mpesa']['key_value'] == 1){
		$gateway_list[3] = "M-Pesa";
	}
	if($data['allow_ipay']['key_value'] == 1){
		$gateway_list[5] = "Ipay";
	}
	if($data['allow_paypal']['key_value'] == 1){
		$gateway_list[6] = "PayPal";
	}
	if($data['allow_paystack']['key_value'] == 1){
		$gateway_list[7] = "Paystack";
	}
	if($data['allow_razorpay']['key_value'] == 1){
		$gateway_list[8] = "Razorepay";
	}
	if($data['allow_stripe']['key_value'] == 1){
		$gateway_list[10] = "Stripe";
	}
	if($data['allow_vpay']['key_value'] == 1){
		$gateway_list[13] = "vPay";
	}
	if($data['allow_ifantasy']['key_value'] == 1){
		$gateway_list[14] = "iFantasy";
	}
	if($data['allow_crypto']['key_value'] == 1){
		$gateway_list[15] = "Crypto";
	}
	if($data['allow_cashierpay']['key_value'] == 1){
		$gateway_list[16] = "Cashierpay";
	}
	if($data['allow_cashfree']['key_value'] == 1){
		$gateway_list[17] = "Cashfree";
	}
	if($data['allow_paylogic']['key_value'] == 1){
		$gateway_list[18] = "Paylogic";
	}
	if($data['allow_btcpay']['key_value'] == 1){
		$gateway_list[19] = "BTCpay";
	}
	if($data['allow_directpay']['key_value'] == 1){
		$gateway_list[27] = "Directpay";
	}
    if($data['allow_phonepe']['key_value'] == 1){
		$gateway_list[33] = "PhonePe";
	}
    if($data['allow_juspay']['key_value'] == 1){
		$gateway_list[34] = "Juspay";
	}

	return $gateway_list;

}

/**
 * [get_image description]
 * @Summary
 * @param   Type - 0 = flag , 1= jersey
 * @param   
 
 */
 function get_image($type, $image,$sports_id=7)
{
	$img = IMAGE_PATH;
	switch($type)
	{
		case 0 :
		 	$img = $img.FLAG_CONTEST_DIR ;
			$img .=  ($image)?$image:'flag_default.jpg';
		break;
		case 1:
			$img = $img.JERSEY_CONTEST_DIR ;
			if($sports_id == 8){
				$img .=  ($image && $image != "jersey_default.png")?$image:'kabaddi_jersey.png';
			}else{
				$img .=  ($image)?$image:'jersey_default.png';
			}			
		break;
		case 2:
	
			$img = $img.LEAGUE_IMAGE_DIR ;
			$img .=  ($image)?$image:'league-1.png';
		break;
		case 3 :
		 	$img = $img.FLAG_CONTEST_DIR ;
			$img .=  ($image)?$image:'flag_default.jpg';
		break;
		case 4 :
		 	$img = $img.FLAG_CONTEST_DIR ;
			$img .=  ($image)?$image:'flag_default.jpg';
		break;

	}

	return $img;

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

function week_text_alter(&$item1, $key, $prefix)
{
	if($item1 < 10)
	{
		$item1 ='0'.$item1;
	}
  $item1 = $prefix['pre']. $item1 . $prefix['post'];
}


function getNoOfWeek($startDate, $endDate){
	// convert date in valid format
	$startDate = date("Y-m-d", strtotime($startDate));
	$endDate = date("Y-m-d", strtotime($endDate));
	$yearEndDay = 31;
	$weekArr = array();
	$startYear = date("Y", strtotime($startDate));
	$endYear = date("Y", strtotime($endDate));
  
	if($startYear != $endYear) {
	  $newStartDate = $startDate;
  
	  for($i = $startYear; $i <= $endYear; $i++) {
		if($endYear == $i) {
		  $newEndDate = $endDate;
		} else {
		  $newEndDate = $i."-12-".$yearEndDay;
		}
		$startWeek = date("W", strtotime($newStartDate));
		$endWeek = date("W", strtotime($newEndDate));
		if($endWeek == 1){
		  $endWeek = date("W", strtotime($i."-12-".($yearEndDay-7)));
		}
		$tempWeekArr = range($startWeek, $endWeek);
		array_walk($tempWeekArr, "week_text_alter", 
		   array('pre' => 'Week_', 'post' => "_". substr($i, 2, 2) ));
		$weekArr = array_merge($weekArr, $tempWeekArr);
  
		$newStartDate = date("Y-m-d", strtotime($newEndDate . "+1 days"));
	  }
	} else {
	  $startWeek = date("W", strtotime($startDate));
	  $endWeek = date("W", strtotime($endDate));
	  $endWeekMonth = date("m", strtotime($endDate));
	  if($endWeek == 1 && $endWeekMonth == 12){
		$endWeek = date("W", strtotime($endYear."-12-".($yearEndDay-7)));
	  }
	  $weekArr = range($startWeek, $endWeek);
	  array_walk($weekArr, "week_text_alter", 
		 array('pre' => 'Week ', 'post' => " ". substr($startYear, 2, 2)));
	}
	$weekArr = array_fill_keys($weekArr, 0);
	return array_keys($weekArr);
  }

function aksort(&$array,$valrev=false,$keyrev=false) {
	if ($valrev) { arsort($array); } else { asort($array); }
	  $vals = array_count_values($array);
	  $i = 0;
	  foreach ($vals AS $val=>$num) {
		  $first = array_splice($array,0,$i);
		  $tmp = array_splice($array,0,$num);
		  if ($keyrev) { krsort($tmp); } else { ksort($tmp); }
		  $array = array_merge($first,$tmp,$array);
		  unset($tmp);
		  $i = $num;
	  }
  }

  function date_sort($a, $b) {
    return strtotime($a) - strtotime($b);
}

  function numbers_only($value)
  {
	return ctype_digit(strval($value));
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

function get_sports_player_config($sports_id)
{
	$config_data = array();
	$config_data[CRICKET_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"20","position"=>array("WK"=>"1","BAT"=>"2","AR"=>"1","BOW"=>"2"));
	$config_data[SOCCER_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"20","position"=>array("FW"=>"1","MF"=>"3","DF"=>"3","GK"=>"1"));
	$config_data[KABADDI_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"20","position"=>array("RAID"=>"1","AR"=>"1","DEF"=>"2"));
	$config_data[BASKETBALL_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"50","position"=>array("SG"=>"1","SF"=>"1","PF"=>"1","PG"=>"1","C"=>"1"));
	$config_data[NFL_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"20","position"=>array("QB"=>"1","RB"=>"2","WR"=>"3","TE"=>"1","DEF"=>"1","K"=>"1"));
	$config_data[BASEBALL_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"20","position"=>array("OF"=>"1","IF"=>"1","C"=>"1","P"=>"1"));

	$config_data[NCAA_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"20","position"=>array("QB"=>"1","RB"=>"3","WR"=>"3"));
	$config_data[CFL_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"20","position"=>array("QB"=>"1","RB"=>"1","WR"=>"1","DEF"=>"1"));
	$config_data[NCAA_BASKETBALL_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"20","position"=>array("C"=>"1","G"=>"1","F"=>"1"));
	$config_data[MOTORSPORT_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"40","position"=>array("DR"=>"1","CR"=>"1"));
	$config_data[TENNIS_SPORTS_ID] = array("min_sal"=>"1","max_sal"=>"40","position"=>array("ALL"=>"1"));


	if(isset($config_data[$sports_id])){
		return $config_data[$sports_id];
	}else{
		return array();
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


function random_password( $length = 8, $characters = true, $numbers = true, $case_sensitive = true, $hash = true ) {

    $password = '';

    if($characters)
    {
        $charLength = $length;
        if($numbers) $charLength-=2;
        if($case_sensitive) $charLength-=2;
        if($hash) $charLength-=2;
        $chars = "abcdefghijklmnopqrstuvwxyz";
        $password.= substr( str_shuffle( $chars ), 0, $charLength );
    }

    if($numbers)
    {
        $numbersLength = $length;
        if($characters) $numbersLength-=2;
        if($case_sensitive) $numbersLength-=2;
        if($hash) $numbersLength-=2;
        $chars = "0123456789";
        $password.= substr( str_shuffle( $chars ), 0, $numbersLength );
    }

    if($case_sensitive)
    {
        $UpperCaseLength = $length;
        if($characters) $UpperCaseLength-=2;
        if($numbers) $UpperCaseLength-=2;
        if($hash) $UpperCaseLength-=2;
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $password.= substr( str_shuffle( $chars ), 0, $UpperCaseLength );
    }

    if($hash)
    {
        $hashLength = $length;
        if($characters) $hashLength-=2;
        if($numbers) $hashLength-=2;
        if($case_sensitive) $hashLength-=2;
        $chars = "!@#$%^&*()_-=+;:,.?";
        $password.= substr( str_shuffle( $chars ), 0, $hashLength );
    }

    $password = str_shuffle( $password );
	return $password;
}

/**
 * get_admin_menu_keys
 * Summary :- Use for get all admin roles
 * @return [array]
 * ALLOW_NETWORK_FANTASY -> network_game
 */
if (!function_exists('get_admin_menu_keys')) {
	function get_admin_menu_keys($config){
		// $ci =& get_instance();
		// $config = $ci->app_config;
		// print_r($config);exit;
		$module_arr=array("accounting","dashboard","dfs","admin_role_management","marketing","user_management","user_wallet_manage","content_management","report","manage_finance","settings","leaderboard","contest_template","network_game","finance_erp","live_fantasy");
		//multigame, pickem,affiliate,deals,coin system,sports predictor, open predictor, fixed open predictor,distributor, private contest, self exclusion,prop fantasy, ref leaderboard
		
		if(isset($config['allow_multigame']['key_value']) && $config['allow_multigame']['key_value']==1)
		{
			array_push($module_arr,"multigame");
		}
		if(isset($config['allow_pickem_tournament']['key_value']) && $config['allow_pickem_tournament']['key_value']==1)
		{
			array_push($module_arr,"pickem_tournament");
		}
		
		if(isset($config['allow_deal']['key_value']) && $config['allow_deal']['key_value']==1)
		{
			array_push($module_arr,"deals");
		}
		if(isset($config['allow_coin_system']['key_value']) && $config['allow_coin_system']['key_value']==1)
		{
			array_push($module_arr,"coins");
		}
		if(isset($config['allow_spin']['key_value']) && $config['allow_spin']['key_value']==1)
		{
			array_push($module_arr,"spinthewheel");
		}
		if(isset($config['allow_prediction_system']['key_value']) && $config['allow_prediction_system']['key_value']==1)
		{
			array_push($module_arr,"sports_predictor");
		}
		if(isset($config['allow_open_predictor']['key_value']) && $config['allow_open_predictor']['key_value']==1)
		{
			array_push($module_arr,"open_predictor_with_pool");
		}
		if(isset($config['allow_fixed_open_predictor']['key_value']) && $config['allow_fixed_open_predictor']['key_value']==1)
		{
			array_push($module_arr,"open_predictor_with_prize");
		}
		if(isset($config['allow_distributor']['key_value']) && $config['allow_distributor']['key_value']==1)
		{
			array_push($module_arr,"distributors");
		}
		if(isset($config['allow_private_contest']['key_value']) && $config['allow_private_contest']['key_value']==1)
		{
			array_push($module_arr,"private_contest");
		}
		if(isset($config['allow_self_exclusion']['key_value']) && $config['allow_self_exclusion']['key_value']==1)
		{
			array_push($module_arr,"self_exclusion");
		}
		if(isset($config['allow_stock_fantasy']['key_value']) && $config['allow_stock_fantasy']['key_value']==1)
		{
			array_push($module_arr,"stock_fantasy");
		}

		if(isset($config['allow_equity']['key_value']) && $config['allow_equity']['key_value']==1)
		{
			array_push($module_arr,"equity_stock_fantasy");
		}
		if(isset($config['allow_rookie_contest']['key_value']) && $config['allow_rookie_contest']['key_value']==1)
		{
			array_push($module_arr,"rookie_contest");
		}

		if(isset($config['allow_quiz']['key_value']) && $config['allow_quiz']['key_value']==1)
		{
			array_push($module_arr,"quiz");
		}

		if(isset($config['allow_stock_predict']['key_value']) && $config['allow_stock_predict']['key_value']==1)
		{
			array_push($module_arr,"stock_predict");
		}
		if(isset($config['allow_referral_leaderboard']['key_value']) && $config['allow_referral_leaderboard']['key_value']==1)
		{
			array_push($module_arr,"referral_leaderboard");
		}
		if(isset($config['allow_livefantasy']['key_value']) && $config['allow_livefantasy']['key_value']==1)
        {
			array_push($module_arr,"live_fantasy");
		}
		if(isset($config['new_affiliate']['key_value']) && $config['new_affiliate']['key_value']==1)
		{
			array_push($module_arr,"new_affiliate");
		}
		if(isset($config['affiliate_module']['key_value']) && $config['affiliate_module']['key_value']==1)
		{
			array_push($module_arr,"affiliate");
		}
		if(isset($config['allow_live_stock_fantasy']['key_value']) && $config['allow_live_stock_fantasy']['key_value']==1)
		{
			array_push($module_arr,"live_stock_fantasy");
		}

		if(isset($config['allow_picks']['key_value']) && $config['allow_picks']['key_value']==1)
        {
			array_push($module_arr,"picks_fantasy");
		}

        if(isset($config['allow_offpg']['key_value']) && $config['allow_offpg']['key_value']==1)
		{
			array_push($module_arr,"manual_payment");
		}

		if(isset($config['allow_props']['key_value']) && $config['allow_props']['key_value']==1)
		{
			array_push($module_arr,"props_fantasy");
		}

        if(isset($config['opinion_trade']['key_value']) && $config['opinion_trade']['key_value']==1)
		{
			array_push($module_arr,"opinion_trade");
		}

		return $module_arr;
	}
}

/**
 * get_sub_admin_menu_keys
 * Summary :- Use for get all admin roles which roles will show in admin dashboard
 * @return [array]
 */
if (!function_exists('get_sub_admin_menu_keys')) {
	function get_sub_admin_menu_keys($config){
		// $module_arr = array("multigame","pickem","affiliate","dashboard","dfs","admin_role_management","marketing","deals","user_management","user_wallet_manage","content_management","report","manage_finance","settings","coins","sports_predictor","distributors","open_predictor_with_pool","leaderboard");
		// $ci =& get_instance();
		// $config = $ci->app_config;
       
		$module_arr=array("accounting","dashboard","admin_role_management","marketing","user_management","user_wallet_manage","content_management","report","manage_finance","settings","leaderboard","contest_template","network_game","finance_erp");
		//multigame, pickem,affiliate,deals,coin system,sports predictor, open predictor, fixed open predictor,distributor, private contest, self exclusion,prop fantasy, ref leaderboard
		if(isset($config['allow_dfs']['key_value']) && $config['allow_dfs']['key_value']==1)
		{
			array_push($module_arr,"dfs");
		}
		if(isset($config['allow_multigame']['key_value']) && $config['allow_multigame']['key_value']==1)
		{
			array_push($module_arr,"multigame");
		}
		if(isset($config['allow_pickem_tournament']['key_value']) && $config['allow_pickem_tournament']['key_value']==1)
		{
			array_push($module_arr,"pickem_tournament");
		}
		
		if(isset($config['allow_deal']['key_value']) && $config['allow_deal']['key_value']==1)
		{
			array_push($module_arr,"deals");
		}
		if(isset($config['allow_coin_system']['key_value']) && $config['allow_coin_system']['key_value']==1)
		{
			array_push($module_arr,"coins");
		}
		if(isset($config['allow_spin']['key_value']) && $config['allow_spin']['key_value']==1)
		{
			array_push($module_arr,"spinthewheel");
		}
		if(isset($config['allow_prediction_system']['key_value']) && $config['allow_prediction_system']['key_value']==1)
		{
			array_push($module_arr,"sports_predictor");
		}
		if(isset($config['allow_open_predictor']['key_value']) && $config['allow_open_predictor']['key_value']==1)
		{
			array_push($module_arr,"open_predictor_with_pool");
		}
		if(isset($config['allow_fixed_open_predictor']['key_value']) && $config['allow_fixed_open_predictor']['key_value']==1)
		{
			array_push($module_arr,"open_predictor_with_prize");
		}
		if(isset($config['allow_distributor']['key_value']) && $config['allow_distributor']['key_value']==1)
		{
			array_push($module_arr,"distributors");
		}
		if(isset($config['allow_private_contest']['key_value']) && $config['allow_private_contest']['key_value']==1)
		{
			array_push($module_arr,"private_contest");
		}
		if(isset($config['allow_self_exclusion']['key_value']) && $config['allow_self_exclusion']['key_value']==1)
		{
			array_push($module_arr,"self_exclusion");
		}

		if(isset($config['allow_xp_point']['key_value']) && $config['allow_xp_point']['key_value']==1)
		{
			array_push($module_arr,"xp_module");
		}
		if(isset($config['allow_stock_fantasy']['key_value']) && $config['allow_stock_fantasy']['key_value']==1)
		{
			array_push($module_arr,"stock_fantasy");
		}

		if(isset($config['allow_equity']['key_value']) && $config['allow_equity']['key_value']==1)
		{
			array_push($module_arr,"equity_stock_fantasy");
		}

		if(isset($config['allow_stock_predict']['key_value']) && $config['allow_stock_predict']['key_value']==1)
		{
			array_push($module_arr,"stock_predict");
		}
		if(isset($config['allow_livefantasy']['key_value']) && $config['allow_livefantasy']['key_value']==1)
        {
			array_push($module_arr,"live_fantasy");
		}
		if(isset($config['new_affiliate']['key_value']) && $config['new_affiliate']['key_value']==1)
		{
			array_push($module_arr,"new_affiliate");
		}
		if(isset($config['affiliate_module']['key_value']) && $config['affiliate_module']['key_value']==1)
		{
			array_push($module_arr,"affiliate");
		}

		if(isset($config['allow_live_stock_fantasy']['key_value']) && $config['allow_live_stock_fantasy']['key_value']==1)
		{
			array_push($module_arr,"live_stock_fantasy");
		}

		if(isset($config['allow_picks']['key_value']) && $config['allow_picks']['key_value']==1)
        {
			array_push($module_arr,"picks_fantasy");
		}

        if(isset($config['allow_offpg']['key_value']) && $config['allow_offpg']['key_value']==1)
		{
			array_push($module_arr,"manual_payment");
		}

		if(isset($config['allow_props']['key_value']) && $config['allow_props']['key_value']==1)
		{
			array_push($module_arr,"props_fantasy");
		}

        if(isset($config['opinion_trade']['key_value']) && $config['opinion_trade']['key_value']==1)
		{
			array_push($module_arr,"opinion_trade");
		}
		return $module_arr;
	}
}

if (!function_exists('app_config_form_setting')) {
    function app_config_form_setting($is_super_admin=0){
		$pg_list = array("payumoney","paytm","mpesa","paypal","paystack","ipay","razorpay","cashfree","vpay","ifantasy","cashierpay","paylogic","stripe","siriuspay","directpay","phonepe","juspay");
        $tz_list = get_timezone();
        //client
        $admin_form_arr = array();
        $form_arr = array();

        $admin_form_arr['site_title'] = array("type"=>"text");
        $admin_form_arr['support_id'] = array("type"=>"text");
		$admin_form_arr['timezone'] = array("type"=>"select","options"=>array_keys($tz_list));
        $admin_form_arr['coins_balance_claim'] = array("type"=>"text");
        $admin_form_arr['min_bet_coins'] = array("type"=>"text");
        $admin_form_arr['max_bet_coins'] = array("type"=>"text");
        $admin_form_arr['max_contest_bonus'] = array("type"=>"text");
        $admin_form_arr['report_admin_email'] = array("type"=>"text");
        $form_arr['fcm_key'] = array("type"=>"text");
		$form_arr['currency_code'] = array("type"=>"text");
        $form_arr['currency_abbr'] = array("type"=>"text");
		$form_arr['otp_message'] = array("type"=>"text");
		$form_arr['apk_sms'] = array("type"=>"radio");
        $form_arr['apk_sms']['child'] = array();
        $form_arr['apk_sms']['child']['template_id'] = array("type"=>"text");
        $form_arr['apk_sms']['child']['sms_text'] = array("type"=>"text");
		$form_arr['single_country'] = array("type"=>"radio");
        $form_arr['phone_code'] 	= array("type"=>"text");
        $form_arr['country_code'] 	= array("type"=>"text");
        $form_arr['country_id'] 	= array("type"=>"text");
		$form_arr['m_e_p_b'] = array("type"=>"text");
		
		
        $admin_form_arr['fb_link'] = array("type"=>"text");
        $admin_form_arr['twitter_link'] = array("type"=>"text");
        $admin_form_arr['instagram_link'] = array("type"=>"text");

        $form_arr['allow_english'] = array("type"=>"radio");
        $form_arr['allow_hindi'] = array("type"=>"radio");
        $form_arr['allow_gujrati'] = array("type"=>"radio");
        $form_arr['allow_french'] = array("type"=>"radio");
        $form_arr['allow_bengali'] = array("type"=>"radio");
        $form_arr['allow_punjabi'] = array("type"=>"radio");
        $form_arr['allow_tamil'] = array("type"=>"radio");
        $form_arr['allow_thai'] = array("type"=>"radio");
        $form_arr['allow_russian'] = array("type"=>"radio");
        $form_arr['allow_indonesian'] = array("type"=>"radio");
        $form_arr['allow_tagalog'] = array("type"=>"radio");
        $form_arr['allow_chinese'] = array("type"=>"radio");
        $form_arr['allow_kannada'] = array("type"=>"radio");
        $form_arr['allow_spanish'] = array("type"=>"radio");
		
        //vinfotech
		$form_arr['allow_bank_flow'] = array("type"=>"radio");
        $form_arr['allow_pan_flow'] = array("type"=>"radio");
        $form_arr['allow_deal'] = array("type"=>"radio");
        $form_arr['allow_age_limit'] = array("type"=>"radio");
        $form_arr['allow_dfs'] = array("type"=>"radio");
        $form_arr['allow_dfs']['child'] = array();
        $form_arr['allow_dfs']['child']['auto_publish'] = array("type"=>"radio","value"=>"0");

        $form_arr['pl_allow'] = array("type"=>"radio");
        $form_arr['pl_allow']['child'] = array();
        $form_arr['pl_allow']['child']['version'] = array("type"=>"select","options"=>array("v1","v2"));
        $form_arr['pl_allow']['child']['website_id'] = array("type"=>"text");
        $form_arr['pl_allow']['child']['token'] = array("type"=>"text");
        $form_arr['pl_allow']['child']['api'] = array("type"=>"text");
        $form_arr['pl_allow']['child']['error_email'] = array("type"=>"text");
        $form_arr['pl_allow']['child']['match_limit'] = array("type"=>"text");
        $form_arr['pl_allow']['child']['contest_limit'] = array("type"=>"text");
		$form_arr['pl_allow']['child']['allow_guru'] = array("type"=>"radio");

		$form_arr['allow_guru'] = array("type"=>"radio");
        $form_arr['allow_guru']['child'] = array();
        $form_arr['allow_guru']['child']['website_id'] = array("type"=>"text");
        $form_arr['allow_guru']['child']['website_token'] = array("type"=>"text");
        $form_arr['allow_guru']['child']['website_api'] = array("type"=>"text");
        
        $form_arr['android_app'] = array("type"=>"radio");
        $form_arr['android_app']['child'] = array();
        $form_arr['android_app']['child']['android_app_link'] = array("type"=>"text");
        $form_arr['android_app']['child']['android_app_page'] = array("type"=>"text");
        $form_arr['android_app']['child']['android_min_ver'] = array("type"=>"text");
        $form_arr['android_app']['child']['android_current_ver'] = array("type"=>"text");
        $form_arr['ios_app'] = array("type"=>"radio");
        $form_arr['ios_app']['child'] = array();
        $form_arr['ios_app']['child']['ios_app_link'] = array("type"=>"text");
        $form_arr['ios_app']['child']['ios_min_ver'] = array("type"=>"text");
        $form_arr['ios_app']['child']['ios_current_ver'] = array("type"=>"text");
        $form_arr['default_sports_id'] = array("type"=>"text");
        $form_arr['allow_coin_system'] = array("type"=>"radio");
        $form_arr['allow_spin'] = array("type"=>"radio");
		$form_arr['allow_pickem_tournament'] = array("type"=>"radio");
		$form_arr['allow_pickem_tournament']['child'] = array();
        $form_arr['allow_pickem_tournament']['child']['correct'] = array("type"=>"text");
        $form_arr['allow_pickem_tournament']['child']['wrong'] = array("type"=>"text");
        $form_arr['allow_pickem_tournament']['child']['score_predictor'] = array("type"=>"radio");
		$form_arr['allow_pickem_tournament']['child']['max_goals'] = array("type"=>"text");
		$form_arr['allow_pickem_tournament']['child']['winning_and_goal'] = array("type"=>"text");
		$form_arr['allow_pickem_tournament']['child']['winning_and_goal_difference'] = array("type"=>"text");
		$form_arr['allow_pickem_tournament']['child']['winning_only'] = array("type"=>"text");
        $form_arr['allow_prediction_system'] = array("type"=>"radio");
        $form_arr['allow_prediction_system']['child'] = array();
        $form_arr['allow_prediction_system']['child']['allow_feed'] = array("type"=>"radio");
        $form_arr['allow_open_predictor'] = array("type"=>"radio");
        $form_arr['allow_fixed_open_predictor'] = array("type"=>"radio");

        $form_arr['allow_bank_transfer'] = array("type"=>"radio");
        $form_arr['allow_aadhar'] = array("type"=>"radio");
        $form_arr['allow_aadhar']['child']['is_auto_mode'] = array("type"=>"radio");
        $form_arr['allow_aadhar']['child']['deposit'] = array("type"=>"radio");
        
        $form_arr['allow_private_contest'] = array("type"=>"radio");
        $form_arr['allow_multigame'] = array("type"=>"radio");
        $form_arr['allow_distributor'] = array("type"=>"radio");
        $form_arr['allow_free_to_play'] = array("type"=>"radio");
        $form_arr['affiliate_module'] = array("type"=>"radio");
        $form_arr['affiliate_module']['child'] = array();
        $form_arr['affiliate_module']['child']['site_commission'] = array("type"=>"radio");



        $form_arr['allow_reverse_contest'] = array("type"=>"radio");
        $form_arr['allow_2nd_inning'] = array("type"=>"radio");
        $form_arr['allow_xp_point'] = array("type"=>"radio");
		$form_arr['allow_xp_point']['child'] = array();
        $form_arr['allow_xp_point']['child']['start_date'] = array("type"=>"text");

        $form_arr['bucket_static_data_allowed'] = array("type"=>"radio");
        $form_arr['bucket_data_prefix'] = array("type"=>"text");
        $form_arr['int_version'] = array("type"=>"radio");
        $form_arr['state_tagging'] = array("type"=>"radio");
		$form_arr['allow_bonus_cash_expiry'] = array("type"=>"radio");
		$form_arr['coin_only'] = array("type"=>"radio");
        $form_arr['allow_referral_leaderboard'] = array("type"=>"radio");
        $form_arr['allow_scratchwin'] = array("type"=>"radio");
		$form_arr['is_ga4'] = array("type"=>"radio");
		$form_arr['GA3_file'] = array("type"=>"text");
		$form_arr['GA4_credentials'] = array("type"=>"text");
		$form_arr['GA4_cloud_project_id'] = array("type"=>"text");
		$form_arr['allow_buy_coin'] = array("type"=>"radio");
		$form_arr['allow_dfs_tournament'] = array("type"=>"radio");
		$form_arr['allow_score_stats'] = array("type"=>"radio");
		$form_arr['allow_self_exclusion'] = array("type"=>"radio");
		$form_arr['allow_self_exclusion']['child'] = array();
        $form_arr['allow_self_exclusion']['child']['default_limit'] = array("type"=>"text");
		$form_arr['allow_self_exclusion']['child']['max_limit'] = array("type"=>"text");

        $form_arr['api_check'] = array("type"=>"radio");
        $form_arr['api_check']['child'] = array();
        $form_arr['api_check']['child']['key'] = array("type"=>"text");
        $form_arr['api_check']['child']['valid_time'] = array("type"=>"text");
        $form_arr['api_check']['child']['format'] = array("type"=>"text");
        $form_arr['api_check']['child']['origin'] = array("type"=>"text");
		
		$form_arr['allow_quiz'] = array("type"=>"radio");
        //tds
        $form_arr['allow_tds'] = array("type"=>"radio");
        $form_arr['allow_tds']['child'] = array();
        $form_arr['allow_tds']['child']['indian'] = array("type"=>"radio");
        $form_arr['allow_tds']['child']['percent'] = array("type"=>"text");
        $form_arr['allow_tds']['child']['amount'] = array("type"=>"text");

        //gst
        $form_arr['allow_gst'] = array("type"=>"radio");
        $form_arr['allow_gst']['child'] = array();
        $form_arr['allow_gst']['child']['type'] = array("type"=>"select","options"=>array("old","new"));
        $form_arr['allow_gst']['child']['gst_rate'] = array("type"=>"text");
        $form_arr['allow_gst']['child']['gst_bonus'] = array("type"=>"text");
        $form_arr['allow_gst']['child']['state_id'] = array("type"=>"text");
        $form_arr['allow_gst']['child']['pan'] = array("type"=>"text");
        $form_arr['allow_gst']['child']['cin'] = array("type"=>"text");
        $form_arr['allow_gst']['child']['tan'] = array("type"=>"text");
        $form_arr['allow_gst']['child']['gstin'] = array("type"=>"text");
        $form_arr['allow_gst']['child']['hsn_code'] = array("type"=>"text");
        $form_arr['allow_gst']['child']['firm_address'] = array("type"=>"text");
        $form_arr['allow_gst']['child']['firm_name'] = array("type"=>"text");
        $form_arr['allow_gst']['child']['contact_no'] = array("type"=>"text");
        
		//Rookie contest
		$form_arr['allow_rookie_contest'] = array("type"=>"radio");
		$form_arr['allow_rookie_contest']['child'] = array();
		$form_arr['allow_rookie_contest']['child']['winning_amount'] = array("type"=>"text");
		$form_arr['allow_rookie_contest']['child']['month_number'] = array("type"=>"text");
		$form_arr['allow_rookie_contest']['child']['group_id'] = array("type"=>"text");
        $form_arr['prize_cron'] = array("type"=>"radio");
        

		$admin_form_arr['credit_debit_card'] = array("type"=>"radio");
        $admin_form_arr['credit_debit_card']['child'] = array();
        $admin_form_arr['credit_debit_card']['child']['gateway'] = array("type"=>"select","options"=>$pg_list);
        $admin_form_arr['paytm_wallet'] = array("type"=>"radio");
        $admin_form_arr['paytm_wallet']['child'] = array();
        $admin_form_arr['paytm_wallet']['child']['gateway'] = array("type"=>"select","options"=>$pg_list);
        $admin_form_arr['payment_upi'] = array("type"=>"radio");
        $admin_form_arr['payment_upi']['child'] = array();
        $admin_form_arr['payment_upi']['child']['gateway'] = array("type"=>"select","options"=>$pg_list);
        $admin_form_arr['net_banking'] = array("type"=>"radio");
        $admin_form_arr['net_banking']['child'] = array();
        $admin_form_arr['net_banking']['child']['gateway'] = array("type"=>"select","options"=>$pg_list);
		$form_arr['order_prefix'] = array("type"=>"text");


        //payumoney 1
        $form_arr['allow_payumoney'] = array("type"=>"radio");
        $form_arr['allow_payumoney']['child'] = array();
        $form_arr['allow_payumoney']['child']['pg_mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_payumoney']['child']['version'] = array("type"=>"select","options"=>array("OLD","NEW"));
        $form_arr['allow_payumoney']['child']['merchant_key'] = array("type"=>"text");
        $form_arr['allow_payumoney']['child']['salt'] = array("type"=>"text");
        $form_arr['allow_payumoney']['child']['auth_header'] = array("type"=>"text");

        //paytm2
        $form_arr['allow_paytm'] = array("type"=>"radio");
        $form_arr['allow_paytm']['child'] = array();
        $form_arr['allow_paytm']['child']['pg_mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_paytm']['child']['merchant_key'] = array("type"=>"text");
        $form_arr['allow_paytm']['child']['merchant_mid'] = array("type"=>"text");
        $form_arr['allow_paytm']['child']['merchant_website'] = array("type"=>"text");
        $form_arr['allow_paytm']['child']['industry_type_id'] = array("type"=>"text");
        $form_arr['allow_paytm']['child']['channel_id'] = array("type"=>"text");
		
		//mpesa3
        $form_arr['allow_mpesa'] = array("type"=>"radio");
        $form_arr['allow_mpesa']['child'] = array();
        $form_arr['allow_mpesa']['child']['mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_mpesa']['child']['consumer_key'] = array("type"=>"text");
        $form_arr['allow_mpesa']['child']['consumer_secret'] = array("type"=>"text");
        $form_arr['allow_mpesa']['child']['shortcode'] = array("type"=>"text");
        $form_arr['allow_mpesa']['child']['passkey'] = array("type"=>"text");
        $form_arr['allow_mpesa']['child']['password'] = array("type"=>"text");
        $form_arr['allow_mpesa']['child']['initiator'] = array("type"=>"text");

		
		
		
		//ipay5
        $form_arr['allow_ipay'] = array("type"=>"radio");
        $form_arr['allow_ipay']['child'] = array();
        $form_arr['allow_ipay']['child']['pg_mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_ipay']['child']['merchant_key'] = array("type"=>"text");
        $form_arr['allow_ipay']['child']['hashkey'] = array("type"=>"text");

        //paypal6
        $form_arr['allow_paypal'] = array("type"=>"radio");
        $form_arr['allow_paypal']['child'] = array();
        $form_arr['allow_paypal']['child']['pg_mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_paypal']['child']['client_id'] = array("type"=>"text");
        $form_arr['allow_paypal']['child']['secret_key'] = array("type"=>"text");
        $form_arr['allow_paypal']['child']['method'] = array("type"=>"select","options"=>array("Signature","Secret"));
        $form_arr['allow_paypal']['child']['email'] = array("type"=>"text");
        $form_arr['allow_paypal']['child']['username'] = array("type"=>"text");
        $form_arr['allow_paypal']['child']['password'] = array("type"=>"text");
        $form_arr['allow_paypal']['child']['signature'] = array("type"=>"text");

        //paystack7
        $form_arr['allow_paystack'] = array("type"=>"radio");
        $form_arr['allow_paystack']['child'] = array();
        $form_arr['allow_paystack']['child']['pg_mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_paystack']['child']['secret'] = array("type"=>"text");
        $form_arr['allow_paystack']['child']['public'] = array("type"=>"text");

		//Razorpay 8
		$form_arr['allow_razorpay'] = array("type"=>"radio");
        $form_arr['allow_razorpay']['child'] = array();
        $form_arr['allow_razorpay']['child']['mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_razorpay']['child']['key'] = array("type"=>"text");
        $form_arr['allow_razorpay']['child']['secret'] = array("type"=>"text");

		//IN APP PURCHASE 9
		$form_arr['inapp_purchase'] = array("type"=>"radio");
        $form_arr['inapp_purchase']['child'] = array();
        $form_arr['inapp_purchase']['child']['g_app_name'] = array("type"=>"text");
        $form_arr['inapp_purchase']['child']['shared_secret'] = array("type"=>"text");
		//Stripe10
		$curr_list = array("INR","USD","MXN");
		$form_arr['allow_stripe'] = array("type"=>"radio");
        $form_arr['allow_stripe']['child'] = array();
        $form_arr['allow_stripe']['child']['p_key'] = array("type"=>"text");
        $form_arr['allow_stripe']['child']['s_key'] = array("type"=>"text");
        $form_arr['allow_stripe']['child']['s_currency'] = array("type"=>"select","options"=>$curr_list);

		//vpay13
        $form_arr['allow_vpay'] = array("type"=>"radio");
        $form_arr['allow_vpay']['child'] = array();
        $form_arr['allow_vpay']['child']['pg_mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_vpay']['child']['key'] = array("type"=>"text");
        $form_arr['allow_vpay']['child']['mid'] = array("type"=>"text");

		//Ifantasy 14
		$form_arr['allow_ifantasy'] = array("type"=>"radio");
        $form_arr['allow_ifantasy']['child'] = array();
        $form_arr['allow_ifantasy']['child']['key'] = array("type"=>"text");
        $form_arr['allow_ifantasy']['child']['member_id'] = array("type"=>"text");
        $form_arr['allow_ifantasy']['child']['mode'] = array("type"=>"text");

        //crypto PG15
        $form_arr['allow_crypto'] = array("type"=>"radio");
        $form_arr['allow_crypto']['child'] = array();
        $form_arr['allow_crypto']['child']['crypto_endpoint'] = array("type"=>"text");
        $form_arr['allow_crypto']['child']['dp'] = array("type"=>"text");
        $form_arr['allow_crypto']['child']['wd'] = array("type"=>"text");

		//cashierpay16
        $form_arr['allow_cashierpay'] = array("type"=>"radio");
		$form_arr['allow_cashierpay']['child'] = array();
		$form_arr['allow_cashierpay']['child']['mode'] = array("type"=>"select","options"=>array("TEST","PROD"));
        $form_arr['allow_cashierpay']['child']['payId'] = array("type"=>"text");
        $form_arr['allow_cashierpay']['child']['secretKey'] = array("type"=>"text");
		$form_arr['allow_cashierpay']['child']['currency'] = array("type"=>"select","options"=>array("356","826","840","978"));

		//cashfree PG17
        $form_arr['allow_cashfree'] = array("type"=>"radio");
        $form_arr['allow_cashfree']['child'] = array();
        $form_arr['allow_cashfree']['child']['mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_cashfree']['child']['app_id'] = array("type"=>"text");
        $form_arr['allow_cashfree']['child']['secret_key'] = array("type"=>"text");
        $form_arr['allow_cashfree']['child']['app_version'] = array("type"=>"text");

		//paylogic18
        $form_arr['allow_paylogic'] = array("type"=>"radio");
        $form_arr['allow_paylogic']['child'] = array();
		$form_arr['allow_paylogic']['child']['mode'] = array("type"=>"select","options"=>array("TEST","PROD"));
        $form_arr['allow_paylogic']['child']['app_id'] = array("type"=>"text");
        $form_arr['allow_paylogic']['child']['salt'] = array("type"=>"text");
        $form_arr['allow_paylogic']['child']['currency'] = array("type"=>"text");

		//BTCPAY19
        $form_arr['allow_btcpay'] = array("type"=>"radio");
        $form_arr['allow_btcpay']['child'] = array();
        $form_arr['allow_btcpay']['child']['dp'] = array("type"=>"text");
        $form_arr['allow_btcpay']['child']['app_id'] = array("type"=>"text");
        $form_arr['allow_btcpay']['child']['store_id'] = array("type"=>"text");
		$form_arr['allow_btcpay']['child']['order_expiry_minutes'] = array("type"=>"text");

        //directpay PG27
        $form_arr['allow_directpay'] = array("type"=>"radio");
        $form_arr['allow_directpay']['child'] = array();
        $form_arr['allow_directpay']['child']['mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_directpay']['child']['app_id'] = array("type"=>"text");
        $form_arr['allow_directpay']['child']['secret_key'] = array("type"=>"text");

        $form_arr['allow_offpg'] = array("type"=>"radio");

		$form_arr['allow_subscription'] = array("type"=>"radio");
		$form_arr['allow_subscription']['child'] = array();
        $form_arr['allow_subscription']['child']['app_name'] = array("type"=>"text");
        $form_arr['allow_subscription']['child']['json_name'] = array("type"=>"text");
        $form_arr['allow_subscription']['child']['package_name'] = array("type"=>"text");

		$form_arr['allow_private_contest'] 	= array("type"=>"text");//0 = disable, 1 = show_as_button, 2 = show_as_big_banner
		$form_arr['site_rake'] 				= array("type"=>"text");
		$form_arr['host_rake'] 				= array("type"=>"text");
		
		$form_arr['lf_private_contest'] 	= array("type"=>"text");//0 = disable, 1 = show_as_button, 2 = show_as_big_banner
		$form_arr['lf_site_rake'] 			= array("type"=>"text");
		$form_arr['lf_host_rake'] 			= array("type"=>"text");

		//Allow Stock fantasy 0 = disable, 1 = enable
		$form_arr['allow_stock_fantasy'] = array("type"=>"radio");
		$form_arr['allow_stock_fantasy']['child'] = array();
        $form_arr['allow_stock_fantasy']['child']['contest_publish_time'] = array("type"=>"text");
		$form_arr['allow_stock_fantasy']['child']['contest_start_time'] = array("type"=>"text");
		$form_arr['allow_stock_fantasy']['child']['contest_end_time'] = array("type"=>"text");

		$form_arr['allow_equity'] = array("type"=>"radio");
		$form_arr['allow_equity']['child'] = array();
        $form_arr['allow_equity']['child']['salary_cap'] = array("type"=>"text");//5 lac
        $form_arr['allow_equity']['child']['min_cap_per_stock'] = array("type"=>"text");//25k lac
        $form_arr['allow_equity']['child']['max_cap_per_stock'] = array("type"=>"text");//1 lac
        $form_arr['allow_equity']['child']['currency_symbol'] = array("type"=>"text");//₹
        $form_arr['allow_equity']['child']['contest_publish_time'] = array("type"=>"text");//"10:45:00"(UTC)
		$form_arr['allow_equity']['child']['contest_start_time'] = array("type"=>"text");//"03:45:00"(UTC)
		$form_arr['allow_equity']['child']['contest_end_time'] = array("type"=>"text");//"10:00:00"(UTC)

		$form_arr['allow_booster'] = array("type"=>"radio");
		$form_arr['bench_player'] = array("type"=>"radio");

		$form_arr['allow_stock_predict'] = array("type"=>"radio");
		$form_arr['allow_stock_predict']['child'] = array();
        $form_arr['allow_stock_predict']['child']['min_candle_minutes'] = array("type"=>"text");
        $form_arr['allow_stock_predict']['child']['max_candle_minutes'] = array("type"=>"text");

		$form_arr['allow_coin_expiry'] = array("type"=>"radio");
		$form_arr['allow_coin_expiry']['child'] = array();
        $form_arr['allow_coin_expiry']['child']['push_before_days'] = array("type"=>"text");
		
        $form_arr['h2h_challenge'] = array("type"=>"radio");
        $form_arr['h2h_challenge']['child'] = array();
        $form_arr['h2h_challenge']['child']['group_id'] = array("type"=>"text");
        $form_arr['h2h_challenge']['child']['contest_limit'] = array("type"=>"text");
        $form_arr['h2h_challenge']['child']['amateur_min'] = array("type"=>"text");
        $form_arr['h2h_challenge']['child']['amateur_max'] = array("type"=>"text");
        $form_arr['h2h_challenge']['child']['mid_min'] = array("type"=>"text");
        $form_arr['h2h_challenge']['child']['mid_max'] = array("type"=>"text");
        $form_arr['h2h_challenge']['child']['pro_min'] = array("type"=>"text");
        $form_arr['h2h_challenge']['child']['pro_max'] = array("type"=>"text");

        //phonepe 33
        $form_arr['allow_phonepe'] = array("type"=>"radio");
        $form_arr['allow_phonepe']['child'] = array();
        $form_arr['allow_phonepe']['child']['mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_phonepe']['child']['merchent_key'] = array("type"=>"text");
        $form_arr['allow_phonepe']['child']['salt'] = array("type"=>"text");
        $form_arr['allow_phonepe']['child']['key_index'] = array("type"=>"text");

        //juspay PG34
        $form_arr['allow_juspay'] = array("type"=>"radio");
        $form_arr['allow_juspay']['child'] = array();
        $form_arr['allow_juspay']['child']['mode'] = array("type"=>"select","options"=>array("TEST","PRODUCTION"));
        $form_arr['allow_juspay']['child']['api_key'] = array("type"=>"text");
        $form_arr['allow_juspay']['child']['client_id'] = array("type"=>"text");

		//zoop auto kyc
		$form_arr['auto_kyc'] = array("type"=>"radio");
		$form_arr['auto_kyc']['child'] = array();
		$form_arr['auto_kyc']['child']['mode'] = array("type"=>"select","options"=>array("TEST","PROD"));
        $form_arr['auto_kyc']['child']['kyc_id'] = array("type"=>"text");
        $form_arr['auto_kyc']['child']['kyc_key'] = array("type"=>"text");
        $form_arr['auto_kyc']['child']['attempt'] = array("type"=>"text");

		//cashfree payout
		$payout = array("Cashfree","Mpesa","Payumoney","Razorpayx","Juspay");
		$form_arr['auto_withdrawal'] = array("type"=>"radio");
		$form_arr['auto_withdrawal']['child'] = array();
        $form_arr['auto_withdrawal']['child']['payout'] = array("type"=>"select","options"=>$payout);
		$form_arr['auto_withdrawal']['child']['mode'] = array("type"=>"select","options"=>array("TEST","PROD"));
		$form_arr['auto_withdrawal']['child']['c_id'] 		= array("type"=>"text");
		$form_arr['auto_withdrawal']['child']['s_id'] 		= array("type"=>"text");
		$form_arr['auto_withdrawal']['child']['shortcode'] 		= array("type"=>"text");
		$form_arr['auto_withdrawal']['child']['secret_cred'] 		= array("type"=>"text");
		$form_arr['auto_withdrawal']['child']['initiator'] 		= array("type"=>"text");
		$form_arr['auto_withdrawal']['child']['password'] 		= array("type"=>"text");
		$form_arr['auto_withdrawal']['child']['daily_txn'] 		= array("type"=>"text");
		$form_arr['auto_withdrawal']['child']['pg_fee'] 		= array("type"=>"text");
		$form_arr['auto_withdrawal']['child']['auto_withdrawal_limit']= array("type"=>"text");
		
        
		if(ALLOW_NETWORK_FANTASY == 1)
		{
			$form_arr['network_game_auto_publish'] = array("type"=>"radio");
			$form_arr['allow_ngn'] = array("type"=>"radio");
		}

		$form_arr['allow_bs'] = array("type"=>"radio");//banned state
        $form_arr['allow_bs']['child'] = array();
        $form_arr['allow_bs']['child']['site_access'] = array("type"=>"radio");
        $form_arr['allow_bs']['child']['country_code_allowed'] = array("type"=>"text");
        $form_arr['allow_bs']['child']['loc_time'] = array("type"=>"text");
        $form_arr['allow_bs']['child']['force_loc'] = array("type"=>"text");
        $form_arr['allow_bs']['child']['api_key'] = array("type"=>"text");//position stack
        $form_arr['allow_bs']['child']['support_id'] = array("type"=>"text");

		$form_arr['allow_social'] = array("type"=>"radio");
        $form_arr['allow_social']['child'] = array();
        $form_arr['allow_social']['child']['admin_user_id'] = array("type"=>"text");
        $form_arr['allow_social']['child']['website_url'] = array("type"=>"text");
		
		$form_arr['allow_game_center'] = array("type"=>"radio");

        $form_arr['allow_email_mbl'] = array("type"=>"radio");
        $form_arr['allow_mobile_email'] = array("type"=>"radio");

        //live fantasy
        $form_arr['allow_livefantasy'] = array("type"=>"radio");
        $form_arr['allow_livefantasy']['child']['predict_time'] = array("type"=>"text");
		
        $form_arr['allow_multi_team'] = array("type"=>"radio");

        $form_arr['new_affiliate'] = array("type"=>"radio");

		//ENABLE FACEBOOK LOGIN
        $form_arr['FB_Login'] = array("type"=>"radio");
        $form_arr['FB_Login']['child'] = array();
        $form_arr['FB_Login']['child']['api_key'] = array("type"=>"text");
        $form_arr['FB_Login']['child']['secret_key'] = array("type"=>"text");

		//ENABLE GOOGLE LOGIN
        $form_arr['G_Login'] = array("type"=>"radio");
        $form_arr['G_Login']['child'] = array();
        $form_arr['G_Login']['child']['app_name'] = array("type"=>"text");
        $form_arr['G_Login']['child']['web_client_id'] = array("type"=>"text");

		$form_arr['allow_google_captcha'] = array("type"=>"radio");
        $form_arr['allow_google_captcha']['child'] = array();
        $form_arr['allow_google_captcha']['child']['google_captcha_secret'] = array("type"=>"text");
		
		//SMS GATEWAY
		$form_arr['sms_config'] = array("type"=>"radio");
        $form_arr['sms_config']['child'] = array();
        $form_arr['sms_config']['child']['active_sms_gateway'] = array("type"=>"select","options"=>array("msg91","bulksmspremium","kaleyra","onnsms","two_factor","twilio"));
        $form_arr['sms_config']['child']['sms_gateway_api_endpoint'] = array("type"=>"text");
        $form_arr['sms_config']['child']['sms_gateway_auth_key'] = array("type"=>"text");
        $form_arr['sms_config']['child']['sms_gateway_sender_id'] = array("type"=>"text");
        $form_arr['sms_config']['child']['sms_gateway_route_id'] = array("type"=>"text");
        $form_arr['sms_config']['child']['sms_gateway_template'] = array("type"=>"text");

		$form_arr['bonus_expiry_limit'] = array("type"=>"text");

		$form_arr['allow_live_stock_fantasy'] = array("type"=>"radio");
		$form_arr['allow_live_stock_fantasy']['child'] = array();
        $form_arr['allow_live_stock_fantasy']['child']['salary_cap'] = array("type"=>"text");//5 lac
        $form_arr['allow_live_stock_fantasy']['child']['min_cap_per_stock'] = array("type"=>"text");//25k lac
        $form_arr['allow_live_stock_fantasy']['child']['max_cap_per_stock'] = array("type"=>"text");//1 lac
        $form_arr['allow_live_stock_fantasy']['child']['currency_symbol'] = array("type"=>"text");//₹
        $form_arr['allow_live_stock_fantasy']['child']['min_candle_minutes'] = array("type"=>"text");

		//picks_fantasy
        $form_arr['allow_picks'] = array("type"=>"radio");
        $form_arr['allow_picks']['child']['question'] = array("type"=>"text");
		$form_arr['allow_picks']['child']['correct'] = array("type"=>"text");
		$form_arr['allow_picks']['child']['wrong'] = array("type"=>"text");
		
		//wdl 2fa
		$form_arr['allow_withdrawal_2fa'] = array("type"=>"radio");
        
		//SMS GATEWAY
		$form_arr['cd_sms_config'] = array("type"=>"radio");
        $form_arr['cd_sms_config']['child'] = array();
        $form_arr['cd_sms_config']['child']['active_sms_gateway'] = array("type"=>"select","options"=>array("msg91","bulksmspremium","kaleyra","onnsms","two_factor"));
        $form_arr['cd_sms_config']['child']['sms_gateway_api_endpoint'] = array("type"=>"text");
        $form_arr['cd_sms_config']['child']['sms_gateway_auth_key'] = array("type"=>"text");
        $form_arr['cd_sms_config']['child']['sms_gateway_sender_id'] = array("type"=>"text");
        $form_arr['cd_sms_config']['child']['sms_gateway_route_id'] = array("type"=>"text");
        $form_arr['cd_sms_config']['child']['sms_gateway_template'] = array("type"=>"text");

        //Props_fantasy
        $picks = ["2","3","4","5","6","7","8","9","10"];
        $form_arr['allow_props'] = array("type"=>"radio");
        $form_arr['allow_props']['child']['min_bet'] = array("type"=>"text");
		$form_arr['allow_props']['child']['max_bet'] = array("type"=>"text");
		$form_arr['allow_props']['child']['min_picks'] =  array("type"=>"select","options"=>$picks);
		$form_arr['allow_props']['child']['max_picks'] =array("type"=>"select","options"=>$picks);
		$form_arr['allow_props']['child']['default_sports'] =array("type"=>"text");
		$form_arr['allow_props']['child']['coins'] = array("type"=>"radio");
		$form_arr['allow_props']['child']['real_cash'] = array("type"=>"radio");
		$form_arr['allow_props']['child']['coins'] = array("type"=>"radio");
		$form_arr['allow_props']['child']['flexplay'] = array("type"=>"radio");
		$form_arr['allow_props']['child']['powerplay'] = array("type"=>"radio");

        //opinion_trade fantasy
        $form_arr['opinion_trade'] = array("type"=>"radio");
        $form_arr['opinion_trade']['child']['currency'] =  array("type"=>"select","options"=>array("realcash","coins"));

        if($is_super_admin == "1"){
            $final_arr = array_merge($admin_form_arr,$form_arr);
            return $final_arr;
        }else{
            return $admin_form_arr;
        }
	}
	
	function get_miliseconds($date1,$date2)
	{
		$date2 = strtotime($date2);
		$date1 = strtotime($date1);
		return $differenceInSeconds = ($date2 - $date1)*1000;
		//return round(abs($date2 - $date1) / 60,2);
	}
}

if (!function_exists('second_inning_game_interval')) {

    function second_inning_game_interval($format) {
        switch ($format) { 
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

function get_lineup_graph_data($from_date,$to_date,$data_to_operate)
{
        $Dates = get_dates_from_range($from_date,$to_date,'Y-m-d');
        $Dates_show = get_dates_from_range($from_date, $to_date,'d M');
        $data = array();
        $str_date = array();

        if(!$data_to_operate){ 
            $data_to_operate = array(
            "data_value"=>0,
            "main_date"=>date('Y-m-d',strtotime($to_date))
            );
        }
        foreach($Dates as $oneDate){
            $date = strtotime($oneDate);
            $str_date[]=$date;
            $data['data'][$date] = 0;
            foreach($data_to_operate as $row){
                $main_date = strtotime($row['main_date']);
                if(in_array($main_date,$str_date))
               {
                    $data['data'][$main_date] = $row['data_value'];
               }
            }
        }
        if(!empty($data['data']))
       {
           $data['data'] = array_values($data['data']);
           foreach($data['data'] as &$val)
           {
               $val = (int)$val;
            }
        }
            
		$response = array();
        $response['series'] = array('data'=>$data['data']);
        $response['dates'] 	= $Dates_show;
        return $response;
    }

	function get_lineup_graph_data_value($start_number,$end_number,$data_to_operate)
	{
		$numbers = array();
		foreach (range($start_number, $end_number) as $number)
		{
			$numbers[] =$number;
		}

        $data = array();
        $str_date = array();
       
        if(!$data_to_operate){ 
            $data_to_operate = array(
            "data_value"=>0,
            "main_value"=>$end_number
            );
        }
        foreach($numbers as $oneDate){
           
            $str_date[]=$oneDate;
            $data['data'][$oneDate] = 0;
            foreach($data_to_operate as $row){
                $value = $row['main_value'];
                if(in_array($value,$str_date))
               {
                    $data['data'][$value] = $row['data_value'];
               }
            }
        }
        if(!empty($data['data']))
       {
           $data['data'] = array_values($data['data']);
           foreach($data['data'] as &$val)
           {
               $val = (int)$val;
            }
        }
            
		$response = array();
        $response['series'] = array('data'=>$data['data']);
        $response['main_values'] 	= $numbers;
        return $response;
    }

	/**
 * safe_array_key
 * @return string
 */
if (!function_exists('safe_array_key')) {

    function safe_array_key($array, $key, $default = "")
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}

/**
 * Create GUID
 * @return string
 */
if (!function_exists('get_guid')) {

    function get_guid()
    {
        if (function_exists('com_create_guid')) {
            return strtolower(com_create_guid());
        } else {
            mt_srand((float) microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return strtolower($uuid);
        }
    }
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
	        $remain_per_amt = 0;
	        if($win_per_arr[$row['prize_type']] > 0){
	        	$remain_per = $per_arr[$row['prize_type']] - $win_per_arr[$row['prize_type']];
	        	$remain_per_amt = (($row['per'] / $win_per_arr[$row['prize_type']]) * $remain_per);
	        }
	        $row['per'] = number_format($row['per'] + $remain_per_amt,2,".","");
	        $row['amount'] = $row['min_value'] = number_format((($min_prize * $row['per']) / 100),2,".","");
	        $row['max_value'] = number_format((($max_prize * $row['per']) / 100),2,".","");
	    }
    }
    return $prize_arr;
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

function get_media_setting($type){
	$media_arr = array();
	$media_arr['dfstournament'] = array("min_h"=>"240","max_h"=>"240","min_w"=>"1300","max_w"=>"1300","size"=>"2","type"=>array("jpg","png","jpeg","gif"),"path"=>"upload/dfstournament");
	$media_arr['dfstlogo'] = array("min_h"=>"113","max_h"=>"200","min_w"=>"270","max_w"=>"470","size"=>"2","type"=>array("jpg","png","jpeg"),"path"=>"upload/dfstournament");
	$media_arr['whatsnew'] = array("min_h"=>"476","max_h"=>"476","min_w"=>"400","max_w"=>"400","size"=>"2","type"=>array("jpg","png","jpeg"),"path"=>"upload/whatsnew");
	$media_arr['tds'] = array("min_h"=>"","max_h"=>"","min_w"=>"","max_w"=>"","size"=>"2","type"=>array("pdf"),"path"=>"upload/tds");
    $media_arr['mpg'] = array("min_h"=>"164","max_h"=>"5000","min_w"=>"164","max_w"=>"5000","size"=>"20","type"=>array("jpg","png","jpeg","JPEG","JPG","PNG"),"path"=>"upload/mpg");
    $media_arr['quiz'] = array("min_h"=>"376","max_h"=>"376","min_w"=>"670","max_w"=>"670","size"=>"2","type"=>array("jpg","png","jpeg"),"path"=>"upload/quiz");
    $media_arr['paymentgetway'] = array("min_h"=>"200","max_h"=>"200","min_w"=>"300","max_w"=>"300","size"=>"2","type"=>array("jpg","png","jpeg"),"path"=>"upload/paymentgetway");
	if(isset($media_arr[$type])){
		return $media_arr[$type];
	}else{
		return array();
	}
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
            $response['message'] = "Please provide all parameters.";
            return $response;
        }
        $hash_key = generate_data_hash($hash,"d");
        if(!$hash_key){
            $response['status'] = 500;
            $response['message'] = 'Invalid OTP code. Please enter correct OTP.';
        }else{
            $hash_arr = explode("_",$hash_key);
            $time_stamp = strtotime($current_time);
            $expire_time = $hash_arr['2'];
            $phone_str = $entity_no;
            if($hash_arr['0'] != $phone_str){
                $response['status'] = 500;
                //$response['message'] = "Invalid mobile number.";
                $response['message'] = 'Invalid OTP code. Please enter correct OTP.';
            }else if($hash_arr['1'] != $otp){
                $response['status'] = 500;
                $response['otp_attempt'] = 1;
                $response['message']= 'Invalid OTP code. Please enter correct OTP.';
            }else if($expire_time < $time_stamp){
                $response['status'] = 500;
                $response['message'] = 'OTP is expired.';
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
function get_financial_years($start_year = 2022)
{
    $current_year = (int) date('Y');
    $current_month = (int) date('m');
    $financial_years = [];
    if ($current_month < 4) {
        $current_year--;
    }

    for ($year = $start_year; $year <= $current_year; $year++) {
        $next_year = $year + 1;
        $year_key = $year."-".substr($next_year,-2);
        $financial_years[$year_key] = array("start"=>$year."-04-01","end"=>$next_year."-03-31");
    }

    return $financial_years;
}

function get_sports_team_config($sports_id,$type){
	$config = array();
	$config[TENNIS_SPORTS_ID]['QF'] = array("max_player_per_team"=>"5","team_player_count"=>"5","c"=>"1","vc"=>"0","pos"=>array("all_min"=>"5","all_max"=>"5"));
	$config[TENNIS_SPORTS_ID]['SF'] = array("max_player_per_team"=>"2","team_player_count"=>"2","c"=>"0","vc"=>"0","pos"=>array("all_min"=>"2","all_max"=>"2"));
	$config[TENNIS_SPORTS_ID]['FL'] = array("max_player_per_team"=>"1","team_player_count"=>"1","c"=>"0","vc"=>"0","pos"=>array("all_min"=>"1","all_max"=>"1"));
	return isset($config[$sports_id][$type]) ? $config[$sports_id][$type] : array();
}

function get_qr_code($data, $width = 200, $height = 200, $charset = 'utf-8', $error = 'H')
{
  // Google chart api url
  $uri = 'https://chart.googleapis.com/chart?';

  // url queries
  $query = array(
    'cht' => 'qr',
    'chs' => $width .'x'. $height,
    'choe' => $charset,
    'chld' => $error,
    'chl' => $data
  );

  // full url
  $uri = $uri .= http_build_query($query);

  return $uri;
}