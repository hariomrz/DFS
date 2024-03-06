<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Timezone_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
    * Generate an array of time zones with UTC offset.
    *
    */
    function get_timezone_list() 
    {
        //$zonelist = timezone_identifiers_list();
        $zonelist = array();
        $this->db->select('TimeZoneID,StandardTime');
        $this->db->order_by('StandardTime','ASC');
        $this->db->group_by('StandardTime');
        $query = $this->db->get(TIMEZONES);
        if($query->num_rows())
        {
            $zonelist = $query->result_array();
        }

        $zones = array();
        $REQUEST_TIME = (int) $_SERVER ['REQUEST_TIME']; 
        foreach ($zonelist as $zone) 
        {
            // Because many time zones exist in PHP only for backward compatibility
            // reasons and should not be used, the list is filtered by a regular expression.
            if (preg_match('!^((Africa|America|Antarctica|Arctic|Asia|Atlantic|Australia|Europe|Indian|Pacific)/|UTC$)!', $zone['StandardTime'])) 
            {
                $zones[] =	array('TimeZoneID' => $zone['TimeZoneID'],
		                      	'TimeZoneName' => "(GMT ".$this->format_date($REQUEST_TIME, 'P', $zone['StandardTime']).") ". str_replace('_', ' ', $zone['StandardTime']),
		                      	'Sorting' => $this->format_date($REQUEST_TIME, 'Compare', $zone['StandardTime'])
		                   	);
            }
        }
        
        $FinalZones = array();
        if($zones)
        {
            usort($zones, function($a, $b) 
            {
                return $a['Sorting'] - $b['Sorting'];
            });
            foreach($zones as $zone)
            {
                unset($zone['Sorting']);
                $FinalZones[] = $zone;
            }
        }
        // Only keep one city (the first and also most important) for each set of possibilities. 
        //$zones = array_unique( $zones );

        // Sort by area/city name.
        return $FinalZones;           
    }

    /**
     * [format_date description]
     * @param  [type] $timestamp [currect request time]
     * @param  string $format    [date format]
     * @param  [object] $timezone  [timezone object]
     * @return [string]            [formatted string]
     */
    function format_date($timestamp, $format = '', $timezone = NULL) 
    {
        if (!isset($timezone)) 
        {
            $timezone = date_default_timezone_get();
        }
        // Create a DateTime object from the timestamp.
        $date_time = date_create('@' . $timestamp);

        $from = new DateTimeZone($timezone);

        // Set the time zone for the DateTime object.
        date_timezone_set($date_time, $from);

        if($format == 'Compare')
        {
            $date_format = date_format($date_time, 'P');
            $date_format = explode(':', $date_format);
            $date_format = $date_format[0].''.$date_format[1];
        } 
        else 
        {
            $date_format = date_format($date_time, $format);
        }            
        return $date_format;
    }

    /**
     * [get_time_zone_offset used to get the timezone offset]
     * @param  [string] $time_zone [time zone]
     * @return [string]            [time zone offset]
     */
    function get_time_zone_offset($time_zone)
    {
        $request_time = (int) $_SERVER['REQUEST_TIME']; 
        $date_time = date_create('@' . $request_time);
        $from = new DateTimeZone($time_zone);
        return $from->getOffset($date_time);
    }

    /**
     * [get_time_zone_id Used to get timezone based on latitude/longitude and return itS ID]
     * @param  [string] $latitude  [latitude]
     * @param  [string] $longitude [longitude]
     * @return [int]                [timezone id]
     */
    function get_time_zone_id($latitude, $longitude)
    {
        $time_zone_id = 419;
        if(!empty($latitude) && !empty($longitude)) 
        {
            $content = file_get_contents('https://maps.googleapis.com/maps/api/timezone/json?location='.$latitude.','.$longitude.'&timestamp='.time().'&language=en&key='.GOOGLE_API_KEY);
            $content = json_decode($content);
            if($content)
            {
                if(isset($content->status) && $content->status == 'OK')
                {
                    $this->db->select('TimeZoneID');
                    $this->db->where('StandardTime',$content->timeZoneId);
                    $query = $this->db->get(TIMEZONES);

                    $this->db->set('Name',$content->timeZoneName);
                    $this->db->where('StandardTime',$content->timeZoneId);
                    $this->db->update(TIMEZONES);

                    if($query->num_rows())
                    {
                        $row = $query->row();
                        $time_zone_id = $row->TimeZoneID;
                    } 
                    else 
                    {
                        $this->db->insert(TIMEZONES,array('Name'=>$content->timeZoneName,'StandardTime'=>$content->timeZoneId,'UTCOffSet'=>($content->rawOffset/3600),'DSTOffSet'=>($content->dstOffset/3600)));
                        $time_zone_id = $this->db->insert_id();
                    }
                }
            }
        }
        return $time_zone_id;
    }

    function convert_event_date_time($date, $time, $user_id, $type)
    {
        $time = date('H:i:s',strtotime($time));
        $datetime = date('Y-m-d H:i:s',strtotime($date.' '.$time));

        $this->db->select('TZ.StandardTime');
        $this->db->from(TIMEZONES.' TZ');
        $this->db->join(USERDETAILS.' UD','UD.TimeZoneID=TZ.TimeZoneID','left');
        $this->db->where('UD.UserID',$user_id);
        $query = $this->db->get();
        if($query->num_rows())
        {
            $user_time_zone = $query->row()->StandardTime;
            if($type == 'Date')
            {
                $format = "Y-m-d";
            }
            elseif($type == 'Time')
            {
                $format = "h:i A";
            }
            return $this->convert_date_to_time_zone($datetime,'UTC',$user_time_zone,$format);
        }
    }

    /**
     * [convert_date_to_time_zone used to convert date from one time zone to another time zone]
     * @param  [Date]   $time           [Date, which being converted]
     * @param  [string] $from_time_zone [From time zone]
     * @param  [string] $to_time_zone   [To time zone]
     * @param  [string] $format         [Reuired date time format]
     * @return [Date]                   [Converted Date]
     */        
    function convert_date_to_time_zone($time, $from_time_zone, $to_time_zone, $format = 'Y-m-d H:i:s') 
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

    /**
     * [get_time_zone Used to get Time zone based on given time zone ID]
     * @param  [int] $time_zone_id [time zone ID]
     * @return [string]               [time zone name]
     */
    function get_time_zone_name($time_zone_id)
    {
        if($time_zone_id)
        {            
            $this->db->select('IFNULL(TZ.StandardTime,"UTC") as TimeZoneText', FALSE);
            $this->db->from(TIMEZONES.' TZ');       
            $this->db->where('TZ.TimeZoneID',$time_zone_id);
            $query = $this->db->get();
            $time_zone = $query->row_array();
            return $time_zone['TimeZoneText'];
        }
        else
        {
           return "UTC"; 
        }
    }

    /**
     * [getUserTimeZone Used to get User Timezone]
     * @param  [int] $user_id [User ID]
     * @return [string]      [User Timezone name]
     */
    function getUserTimeZone($user_id){
        $this->db->select('TZ.StandardTime');
        $this->db->from(TIMEZONES.' TZ');
        $this->db->join(USERDETAILS.' UD','UD.TimeZoneID=TZ.TimeZoneID');
        $this->db->where('UD.UserID',$user_id);
        $query = $this->db->get();
        if($query->num_rows()){
            $StandardTime = $query->row()->StandardTime;
            if($StandardTime){
                return $StandardTime;
            }
        }
        return 'UTC';
    }
}