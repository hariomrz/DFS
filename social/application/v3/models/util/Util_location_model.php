<?php
class Util_location_model extends Common_Model
{

    function __construct() 
    {
        $this->load->helper('location');
        parent::__construct();        
    }
	
    public function get_city_id($filter) {
        $city_id = 0;
        // Get location if exists and get city id from that
        $city = isset($filter['City']) ? trim($filter['City']) : '';
        $state = isset($filter['State']) ? trim($filter['State']) : '';
        $country = isset($filter['Country']) ? trim($filter['Country']) : '';
        $country_code = isset($filter['CountryCode']) ? trim($filter['CountryCode']) : '';
        $short_code = isset($filter['StateCode']) ? trim($filter['StateCode']) : '';
        $city_id = isset($filter['CityID']) ? $filter['CityID'] : 0;
        
        if($city_id) {
            return $city_id;
        }
        
        if ($city && $state && $country) {
            $location = array(
                'City' => $city,
                'State' => $state,
                'Country' => $country,
                'StateCode' => $short_code,
                'CountryCode' => $country_code,
            );
            $location = update_location($location);
            if (!empty($location['CityID'])) {
                $city_id = $location['CityID'];
            }
        }
        
        return $city_id;
    }

    public function location_auto_suggest($search_keyword, $page_no, $page_size) {
        $this->db->select("CT.CityID");
        $this->db->select('IFNULL(CT.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(S.Name,"") as StateName', FALSE);
        $this->db->select('IFNULL(S.ShortCode,"") as StateCode', FALSE);
        $this->db->select('IFNULL(C.CountryName,"") as CountryName', FALSE);
        $this->db->select('IFNULL(C.CountryCode,"") as CountryCode', FALSE);        
        $this->db->from(CITIES . ' CT');
        $this->db->join(USERDETAILS . ' UD', 'UD.CityID = CT.CityID');
        $this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = UD.CountryID', 'left');       
        $this->db->join(STATES . ' S', 'CT.StateID = S.StateID', 'left');
        if ($search_keyword) {
            $this->db->like('CT.Name', $search_keyword);
        }
        if (isset($page_size) && !empty($page_size) && isset($page_no)) {
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }
        $this->db->order_by("CT.Name", "ASC");
        $this->db->group_by("CT.CityID");
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $location_arr = array();
        if ($query->num_rows()) {
            $result = $query->result_array();
            foreach ($result as $row) {               
                $city       = trim($row['CityName']);
                $state      = trim($row['StateName']);
                $state_code = trim($row['StateCode']);
                $country    = trim($row['CountryName']);
                $country_code = trim($row['CountryCode']);
                $location   = '';
                if (!empty($city) && $city != null) {
                    $city = ucfirst(strtolower($city));
                    $location .= $city . ', ';
                }
                if (!empty($state) && $state != null) {
                    $location .= $state . ', ';
                } else if (!empty($state_code) && $state_code != null) {
                    $state_code = strtoupper($state_code);
                    $location .= $state_code . ', ';
                }
                if (!empty($country) && $country != null) {
                    $country = ucfirst(strtolower($country));
                    $location .= $country . ', ';
                }
                if ($location) {
                    $location = substr($location, 0, -2);
                    if ($location == '-') {
                        $location = '';
                    }
                }
                $location_arr[] = array('City' => $city, 'State' => $state, 'Country' => $country, 'Location' => $location, 'StateCode' => $state_code, 'CountryCode' => $country_code);
            }
        }
        return $location_arr;
    }
}
?>