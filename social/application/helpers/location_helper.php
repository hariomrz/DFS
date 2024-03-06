<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * [get_ip_location_details Get location details based on given IP Address]
 * @param  [string] $ip_address [IP Adress]
 * @return [array]           [location details]
 */
if ( ! function_exists('get_ip_location_details')) 
{    
    function get_ip_location_details($ip_address) 
    {
        /*$url = "http://api.ipinfodb.com/v3/ip-city/?key=" . IPINFODBKEY . "&ip=" . $ip_address . "&timezone=true&format=json";
        $location_data      = json_decode(ExecuteCurl($url), true);*/
        $ci = &get_instance();
        $ci->load->library('geoip');
        $location_data = $ci->geoip->info($ip_address);

        $country_id         = NULL;
        $state_id           = NULL;
        $city_id            = NULL;
        $country            = '';
        $state              = '';
        $city               = '';
        $latitude           = NULL;
        $longitude          = NULL;
        $status_code        = '';

        if(isset($location_data->city) && !empty($location_data->city) && isset($location_data->state_name) && !empty($location_data->state_name) && isset($location_data->country_name) && !empty($location_data->country_name) && isset($location_data->country_code) && !empty($location_data->country_code))
        {
            $country_code   = $location_data->country_code;
            $country        = $location_data->country_name;
            $state          = $location_data->state_name;
            $city           = $location_data->city;
            $latitude       = isset($location_data->latitude) ? $location_data->latitude : '' ;
            $longitude      = isset($location_data->longitude) ? $location_data->longitude : '' ;

            $location       = array("City" => $city, "Country" => $country, "CountryCode" => $country_code, "State" => $state, "StateCode" => '');
            $location       = update_location($location);
            $country_id     = $location['CountryID'];
            $state_id       = $location['StateID'];
            $city_id        = $location['CityID'];
            $status_code    = 'OK';
        }
        if(!$location_data)
        {
            $url = "http://api.ipinfodb.com/v3/ip-city/?key=" . IPINFODBKEY . "&ip=" . $ip_address . "&timezone=true&format=json";
            $location_data      = json_decode(ExecuteCurl($url), true);
            $status_code    = $location_data['statusCode'];
            if ($location_data['statusCode'] == 'OK') 
            {
                $country_code   = $location_data['countryCode'];
                $country        = $location_data['countryName'];
                $state          = $location_data['regionName'];
                $city           = $location_data['cityName'];
                $latitude       = $location_data['latitude'];
                $longitude      = $location_data['longitude'];

                $location       = array("City" => $city, "Country" => $country, "CountryCode" => $country_code, "State" => $state, "StateCode" => '');
                $location       = update_location($location);
                $country_id     = $location['CountryID'];
                $state_id       = $location['StateID'];
                $city_id        = $location['CityID'];           
            }
        }

        if(empty($latitude)) 
        {
            $latitude = NULL;
        }
        if(empty($longitude)) 
        {
            $longitude = NULL;
        }
        $result = array('statusCode' => $status_code, 'CountryID' => $country_id, 'StateID' => $state_id, 'CityID' => $city_id, 'Latitude' => $latitude, 'Longitude' => $longitude, 'CountryName' => $country, 'CityName' => $city, 'StateName' => $state);
        return ($result);
    }
}

/**
* [geocoding_location_details Get location details based on given geo coordinate]
* @param  [string] $latitude  [latitude]
* @param  [string] $longitude [longitude]
* @return [array]            [location details]
*/
if(!function_exists('geocoding_location_details'))
{    
    function geocoding_location_details($latitude, $longitude)
    {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$latitude.",".$longitude;

        $details        = json_decode(file_get_contents($url));
        $city           = '';
        $state          = '';
        $country        = '';
        $country_code   = '';
        $state_code     = '';
        if(isset($details->results[0]->address_components))
        {
            foreach($details->results[0]->address_components as $location_details)
            {
                if($location_details->types[0]=='locality')
                {
                    $city = $location_details->long_name;
                }
                if($location_details->types[0]=='administrative_area_level_1')
                {
                    $state = $location_details->long_name;
                    $state_code = $location_details->short_name;
                }
                if($location_details->types[0]=='country')
                {
                    $country = $location_details->long_name;
                    $country_code = $location_details->short_name;
                }
            }
        }

        $location       = array("City" => $city, "Country" => $country, "CountryCode" => $country_code, "State" => $state, "StateCode" => $state_code);
        $location       = update_location($location);
        $country_id      = $location['CountryID'];
        $state_id        = $location['StateID'];
        $city_id         = $location['CityID'];
        if(empty($latitude)) 
        {
            $latitude = NULL;
        }
        if(empty($longitude)) 
        {
            $longitude = NULL;
        }
        $result = array('CountryID' => $country_id, 'StateID' => $state_id, 'CityID' => $city_id, 'Latitude' => $latitude, 'Longitude' => $longitude);
        return $result;
    }
}

/**
 * [get_location_details Get location related data ip_adress,latitude, longitude, CityID ]
 * @param  [array] $ip_adress [IP Address]
 * @param  [array] $latitude [IP Address]
 * @param  [array] $longitude [IP Address]
 * @return [array]           [array of ip_adress, latitude,longitude, city id]
 */
if ( ! function_exists('get_location_details')) 
{
    function get_location_details($ip_adress, $latitude, $longitude)
    {
        $location_details['CityID'] = NULL;
        if(!empty($latitude) && !empty($longitude))
        {
            $location_data = geocoding_location_details($latitude, $longitude);
            $location_details['CityID'] = $location_data['CityID'];
        } else {
            $location_data = array('statusCode' => '');

            if(empty($ip_adress))
            {
                $ip_adress = getRealIpAddr();
            }
            if($ip_adress != '')
            {                
                $location_data = get_ip_location_details($ip_adress);
            }
            if($location_data['statusCode'] == "OK") 
            {
                $location_details['CityID'] = $location_data['CityID'];            
                $latitude = $location_data['Latitude'];
                $longitude = $location_data['Longitude'];
            }
        }

        $location_details['IPAddress']   = $ip_adress;
        $location_details['Latitude']    = $latitude;
        $location_details['Longitude']   = $longitude;
        return $location_details;
    }
}

/**
 * [update_location description]
 * @param  [array] $location [Location data]
 * @return [array]           [array of country, state and city id]
 */
if ( ! function_exists('update_location')) 
{
    function update_location($location)
    {
        $CI             = &get_instance();
        $city           = trim($location['City']);
        $state          = trim($location['State']);
        $country        = trim($location['Country']);
        $country_code   = trim($location['CountryCode']);
        $short_code     = trim($location['StateCode']);
        $country_id     = NULL;
        $state_id       = NULL;
        $city_id        = NULL;

        if($country=="-") {$country = "";}
        if($state=="-") {$state = "";}
        if($city=="-") {$city = "";}

        if(!empty($country) || !empty($country_code)) 
        {
            $CI->db->select('CM.CountryID');
            if(!empty($country_code)) 
            {
                $CI->db->where('LOWER(CountryCode)', strtolower($country_code),NULL,FALSE);
                $country_code = strtoupper($country_code);
            }
            if(!empty($country)) 
            {
                $CI->db->where('LOWER(CountryName)', strtolower($country),NULL,FALSE);
                $country = ucfirst(strtolower($country));
            }
            $CI->db->limit('1');
            $query = $CI->db->get(COUNTRYMASTER.' CM');
            //country master handling starts here        
            if ($query->num_rows() > 0) 
            {
                $country_data = $query->row_array();
                $country_id = $country_data['CountryID'];
            } 
            else 
            {
                $CI->db->insert(COUNTRYMASTER,array('CountryCode' => $country_code,'CountryName' => $country));
                $country_id = $CI->db->insert_id();
            }
        }

        if($country_id > 0 && !empty($state)) 
        {
            //states master handling start here            
            $CI->db->select('StateID');
            $CI->db->where('LOWER(Name)', strtolower($state),NULL,FALSE);
            $CI->db->where('CountryID', $country_id);
            $CI->db->limit('1');
            $query = $CI->db->get(STATES);
            if ($query->num_rows() > 0) 
            {
                $state_data = $query->row_array();
                $state_id = $state_data['StateID'];
            } 
            else 
            {
                $state = ucfirst(strtolower($state));
                $insert_state = array('Name' => $state,'CountryID' => $country_id);
                if(!empty($short_code)) 
                {
                  $insert_state['ShortCode']  = strtoupper($short_code); 
                }                
                $CI->db->insert(STATES, $insert_state);
                $state_id = $CI->db->insert_id();
            }
        }
        //city master handling start here
        if($state_id > 0  && !empty($city)) 
        {
            $CI->db->select('CityID');
            $CI->db->where('LOWER(Name)', strtolower($city),NULL,FALSE);
            $CI->db->where('StateID', $state_id);
            $CI->db->limit('1');
            $query = $CI->db->get(CITIES);
            if ($query->num_rows() > 0) 
            {
                //echo 'afasfas';die;
                $city_data = $query->row_array();
                $city_id = $city_data['CityID'];
            } 
            else 
            {
                $city = ucfirst(strtolower($city));
                $CI->db->insert(CITIES,array('Name' => $city,'StateID' => $state_id));
                $city_id = $CI->db->insert_id();
            }
        }
        return array('CityID' => $city_id,'CountryID' => $country_id,'StateID' => $state_id);
    }
}


/**
 * [insert_location check and insert location]
 * @param  [array] $location [Location data]
 * @return [array]           [array of timezone anme and id]
 */
if ( ! function_exists('insert_location')) 
{
    //check and insert location
    function insert_location($data) 
    {
        $CI =& get_instance();
        $CI->load->model('timezone/timezone_model');
        $CI->db->select('L.LocationID, L.TimeZoneID');
        $CI->db->from(LOCATIONS.' L');        
        $CI->db->where('L.UniqueID',$data['UniqueID']);
        $CI->db->limit('1');
        $query = $CI->db->get();
        $location = $query->row_array();
        
        $TimeZoneID = "";
        if (!empty($location)) 
        {
            $TimeZoneID = $location['TimeZoneID'];
            if(empty($TimeZoneID) || is_null($TimeZoneID))
            {
                $TimeZoneID = $CI->timezone_model->get_time_zone_id($data['Latitude'], $data['Longitude']);

                $CI->db->where('LocationID',$location['LocationID']);
                $CI->db->update(LOCATIONS, array('TimeZoneID' => $TimeZoneID));
            }            
        } 
        else 
        {
            $d = update_location($data);
            unset($data['City']); unset($data['Country']); unset($data['State']); unset($data['StateCode']); unset($data['CountryCode']);

            $TimeZoneID = $CI->timezone_model->get_time_zone_id($data['Latitude'], $data['Longitude']);
            $data['TimeZoneID'] = $TimeZoneID;

            $data['CityID'] = $d['CityID'];
            $data['StateID'] = $d['StateID'];
            $data['CountryID'] = $d['CountryID'];

            $CI->db->insert(LOCATIONS, $data);
            $location['LocationID'] = $CI->db->insert_id();

        }
        $location['TimeZone'] = $CI->timezone_model->get_time_zone_name($TimeZoneID);
        $location['TimeZoneID'] = $TimeZoneID;   
        return $location;
    }  
}

/**
 * [get_location_by_id Get location data by locationID]
 * @param  [int] $location [Location id]
 * @return [array]           [array of location data]
 */
if ( ! function_exists('get_location_by_id')) 
{
    function get_location_by_id($location_id, $extraFields = [])
    {
        if(!empty($location_id))
        {
            $CI =& get_instance();
            $CI->db->select('LocationID, UniqueID, FormattedAddress,FormattedAddress as Location, Latitude, Longitude, StreetNumber, Route, PostalCode, TimeZoneID');
            $CI->db->from(LOCATIONS.' L'); 
            
            if(!empty($extraFields['City'])) {
                $CI->db->select('IFNULL(CT.Name,"") as City', FALSE);
                $CI->db->join(CITIES . ' CT', 'CT.CityID = L.CityID', 'left'); 
            }
            
            if(!empty($extraFields['State'])) {
                $CI->db->select('IFNULL(STT.Name,"") as State', FALSE);
                $CI->db->join(STATES . ' STT', 'STT.StateID = L.StateID', 'left'); 
            }
            
            if(!empty($extraFields['Country'])) {
                $CI->db->select('IFNULL(CM.CountryName,"") as Country', FALSE);
                $CI->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID = L.CountryID', 'left'); 
            }
            
            $CI->db->where(array('LocationID'=>$location_id));
            $CI->db->limit('1');
            $query = $CI->db->get();    //echo $CI->db->last_query(); die;
            $location = $query->row_array();
            if(!empty($location['City']) && !empty($location['State']) && !empty($location['Country'])){
                $location['CityStateCountry'] = $location['City'].", ".$location['State'].", ".$location['Country'];
            }
            return $location;  
        }
        else
        {
            return (object) [];
        }
        
    }
}
