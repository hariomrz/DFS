<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cron_model extends Admin_Common_Model
{
    public function __construct()
    {
        parent::__construct();
    }
        

    /**
     * Function for get user login count data and insert user login count data in login count analytics table
     * Parameters : string $last_day_date
     * Return : boolean
     */
    public function getLoginCountAnalytics($last_day_date)
    {
        $rtn = 0;

        $this->db->select('AL.CreatedDate',FALSE);
        $this->db->select('AL.LoginSourceID', FALSE);
        $this->db->select('AL.DeviceTypeID', FALSE);
        $this->db->select('AL.WeekdayID', FALSE);
        $this->db->select('AL.TimeSlotID', FALSE);
        $this->db->select('COUNT(AL.AnalyticLoginID) AS LoginCount', FALSE);
        $this->db->select('(CASE IsEmail WHEN 1 THEN COUNT(AL.IsEmail) ELSE 0 END) AS CountByEmail', FALSE);
        $this->db->select('(CASE IsEmail WHEN 0 THEN COUNT(AL.IsEmail) ELSE 0 END) AS CountByUsername', FALSE);

        $this->db->from(ANALYTICLOGINS . " AS AL");

        $this->db->where('DATE_FORMAT(AL.CreatedDate,"%Y-%m-%d")',$last_day_date);
        $this->db->where('AL.IsLoginSuccessfull',1);

        $this->db->group_by(array("DATE_FORMAT(AL.CreatedDate, '%Y-%m-%d')"));
        $this->db->group_by('AL.LoginSourceId');
        $this->db->group_by('AL.DeviceTypeID');
        $this->db->group_by('AL.weekdayID');
        $this->db->group_by('AL.TimeSlotID');
        $this->db->group_by('AL.IsEmail');

        $query = $this->db->get();
        $record_count = $query->num_rows();        
        $results = $query->result();        
        
        if($record_count > 0){
            //For delete last day date record
            $this->db->where('DATE_FORMAT(CreatedDate,"%Y-%m-%d")',$last_day_date);
            $this->db->delete(ANALYTICLOGINCOUNTS);             

            //For insert user login count data
            foreach($results as $logincountData){
                $UserAnalyticCountData = (array)$logincountData;
                $rtn = $this->db->insert(ANALYTICLOGINCOUNTS, $UserAnalyticCountData);
            } 
        }

        return $rtn;        
    }

    /**
     * Function for get user first time login count data and insert data
     * Parameters : string $last_day_date
     * Return : boolean
     */
    public function getFirstLoginAnalytics($last_day_date)
    {
        $rtn = 0;

        $this->db->select('AL.CreatedDate',FALSE);
        $this->db->select('COUNT( DISTINCT(AL.userId)) AS FirstTimeLoginCount',FALSE);
        $this->db->select('AL.LoginSourceID', FALSE);

        $this->db->from(ANALYTICLOGINS . " AS AL");

        $this->db->where('DATE_FORMAT(AL.CreatedDate,"%Y-%m-%d")',$last_day_date);
        $this->db->where('AL.IsLoginSuccessfull',1);
        $this->db->group_by('AL.LoginSourceID');

        $query = $this->db->get();   
        $record_count = $query->num_rows();
        $results = $query->result();    

        if($record_count > 0){
            //For delete last day date record
            $this->db->where('DATE_FORMAT(CreatedDate,"%Y-%m-%d")',$last_day_date);
            $this->db->delete(ANALYTICLOGINFIRSTCOUNT);             

            //For insert user first time login data
            foreach($results as $loginfirstcountData){
                $FisrtLoginCountData = (array)$loginfirstcountData;
                $rtn = $this->db->insert(ANALYTICLOGINFIRSTCOUNT, $FisrtLoginCountData);
            }
        }
        return $rtn;        
    }

    /**
     * Function for get user login geo data and insert user geo login count data
     * Parameters : string $last_day_date
     * Return : boolean
     */
    public function getLoginGeoAnalytics($last_day_date)
    {
        $rtn = 0;

        $this->db->select('CONCAT(C.Name,",",S.Name,",",CO.CountryName) as CityStateCountry', FALSE);
        $this->db->select('COUNT(AL.AnalyticLoginID) AS LoginCount',FALSE);
        $this->db->select('AL.CreatedDate',FALSE);

        $this->db->from(ANALYTICLOGINS . " AS AL");
        $this->db->join(CITIES." AS C", ' AL.CityID = C.CityID','inner');
        $this->db->join(STATES." AS S", ' C.StateID = S.StateID','inner');
        $this->db->join(COUNTRYMASTER." AS CO", ' S.CountryID = CO.CountryID','inner');

        $this->db->where('DATE_FORMAT(AL.CreatedDate,"%Y-%m-%d")',$last_day_date);
        $this->db->where('AL.IsLoginSuccessfull',1);
        $this->db->where('AL.CityID != ','');
        $this->db->where('C.Name != ','');
        $this->db->where('S.Name != ','');
        $this->db->where('CO.CountryName != ','');
        
        $this->db->group_by(array("DATE_FORMAT(AL.CreatedDate, '%Y-%m-%d')"));
        $this->db->group_by('C.Name');
        $this->db->group_by('S.Name');
        $this->db->group_by('CO.CountryName');

        $query = $this->db->get();     
        $record_count = $query->num_rows();
        $results = $query->result();
        
        if($record_count > 0){
            //For delete last day date record
            $this->db->where('DATE_FORMAT(CreatedDate,"%Y-%m-%d")',$last_day_date);
            $this->db->delete(ANALYTICLOGINGEOCOUNT);             
            
            //For insert user geo data
            foreach($results as $logingeoData){
                $UserLoginGeoData = (array)$logingeoData;
                $rtn = $this->db->insert(ANALYTICLOGINGEOCOUNT, $UserLoginGeoData);
            } 
        }
        return $rtn;        
    }
    
    /**
     * Function for get register user log count data and insert user signup count data in signup analytics table
     * Parameters : string $last_day_date
     * Return : boolean
     */
    public function getSignupAnalyticsLogCount($last_day_date)
    {
        $rtn = 0;
        
        $this->db->select('SAL.SignupSourceID', FALSE);
        $this->db->select('SAL.DeviceTypeID', FALSE);
        $this->db->select('SAL.WeekdayID', FALSE);
        $this->db->select('SAL.TimeSlotID', FALSE);
        $this->db->select('COUNT(SAL.SignupAnalyticLogID) AS SignUpCount', FALSE);
        $this->db->select('SAL.CreatedDate',FALSE);

        $this->db->from(SIGNUPANALYTICLOGS . " AS SAL");

        $this->db->where('DATE_FORMAT(SAL.CreatedDate,"%Y-%m-%d")',$last_day_date);
        $this->db->where('SAL.IsSignup',1);

        $this->db->group_by(array("DATE_FORMAT(SAL.CreatedDate, '%Y-%m-%d')"));
        $this->db->group_by('SAL.SignupSourceID');
        $this->db->group_by('SAL.DeviceTypeID');
        $this->db->group_by('SAL.weekdayID');
        $this->db->group_by('SAL.TimeSlotID');

        $query = $this->db->get();
        $record_count = $query->num_rows();  
        //echo $this->db->last_query();die;
        $results = $query->result();        
        
        if($record_count > 0){
            //For delete last day date record
            $this->db->where('DATE_FORMAT(CreatedDate,"%Y-%m-%d")',$last_day_date);
            $this->db->delete(SIGNUPANALYTICLOGCOUNTS);             

            //For insert user login count data
            foreach($results as $signuplogData){
                $UserSignupLogAnalyticData = (array)$signuplogData;
                $rtn = $this->db->insert(SIGNUPANALYTICLOGCOUNTS, $UserSignupLogAnalyticData);
            } 
        }

        return $rtn;        
    }
    
    /**
     * Function for get all register user geo data and insert user geo data in signup analytic tables
     * Parameters : string $last_day_date
     * Return : boolean
     */
    public function getSignupGeoAnalytics($last_day_date)
    {
        $rtn = 0;

        $this->db->select('CONCAT(C.Name,",",S.Name,",",CO.CountryName) as CityStateCountry', FALSE);
        $this->db->select('SUM(CASE WHEN SAL.IsSignup=1 THEN 1 ELSE 0 END) AS SignUpCount', FALSE);
        $this->db->select('SUM(CASE WHEN SAL.IsSignup=0 THEN 1 ELSE 0 END) AS VisitCount', FALSE);
        $this->db->select('SAL.CreatedDate',FALSE);

        $this->db->from(SIGNUPANALYTICLOGS . " AS SAL");
        $this->db->join(CITIES." AS C", ' SAL.CityID = C.CityID','inner');
        $this->db->join(STATES." AS S", ' C.StateID = S.StateID','inner');
        $this->db->join(COUNTRYMASTER." AS CO", ' S.CountryID = CO.CountryID','inner');

        $this->db->where('DATE_FORMAT(SAL.CreatedDate,"%Y-%m-%d")',$last_day_date);
        $this->db->where('SAL.CityID != ','');
        $this->db->where('C.Name != ','');
        $this->db->where('S.Name != ','');
        $this->db->where('CO.CountryName != ','');
                
        $this->db->group_by('SAL.CityID');
        $this->db->group_by(array("DATE_FORMAT(SAL.CreatedDate, '%Y-%m-%d')"));

        $query = $this->db->get();     
        $record_count = $query->num_rows();        
        $results = $query->result();
        
        if($record_count > 0){
            //For delete last day date record
            $this->db->where('DATE_FORMAT(CreatedDate,"%Y-%m-%d")',$last_day_date);
            $this->db->delete(SIGNUPANALYTICGEOCOUNT);             
            
            //For insert user geo data
            foreach($results as $signupgeoData){
                $UserSignupGeoData = (array)$signupgeoData;
                $rtn = $this->db->insert(SIGNUPANALYTICGEOCOUNT, $UserSignupGeoData);
            } 
        }
        return $rtn;        
    }
    
    /**
     * Function for get all register user visit count and insert data in analytics user visit count table
     * Parameters : string $last_day_date
     * Return : boolean
     */
    public function getSignupVisitCountAnalytics($last_day_date)
    {
        $rtn = 0;

        $this->db->select('SUM(CASE WHEN SAL.IsSignup=0 THEN 1 ELSE 0 END) AS FirstTimeVisitCount', FALSE);
        $this->db->select('SUM(CASE WHEN SAL.IsSignup=1 THEN 1 ELSE 0 END) AS SecondTimeVisitCount', FALSE);
        $this->db->select('SAL.CreatedDate',FALSE);

        $this->db->from(SIGNUPANALYTICLOGS . " AS SAL");

        $this->db->where('DATE_FORMAT(SAL.CreatedDate,"%Y-%m-%d")',$last_day_date);
        $this->db->group_by('SAL.IsSignup');

        $query = $this->db->get();     
        $record_count = $query->num_rows();        
        $results = $query->result();
        
        if($record_count > 0){
            //For delete given date record
            $this->db->where('DATE_FORMAT(CreatedDate,"%Y-%m-%d")',$last_day_date);
            $this->db->delete(SIGNUPANALYTICVISITCOUNT);             
            
            //For insert user visit count data
            foreach($results as $signupvisitcountData){
                $UserSignupVisitCountData = (array)$signupvisitcountData;
                $rtn = $this->db->insert(SIGNUPANALYTICVISITCOUNT, $UserSignupVisitCountData);
            } 
        }
        return $rtn;        
    }
        
    /**
     * Function for get all register user time taken for signup process and insert data
     * Parameters : string $last_day_date
     * Return : boolean
     */
    public function getSignupTimeTakenRangeAnalytic($last_day_date)
    {
        $rtn = 0;
        
        $this->db->select('TTR.TimeTakenRangeID',FALSE);
        $this->db->select('COUNT(SAL.SignupAnalyticLogID) AS SignupCount', FALSE);
        $this->db->select('SAL.CreatedDate',FALSE);

        $this->db->from(SIGNUPANALYTICLOGS . " AS SAL");
        $this->db->join(TIMETAKENRANGE." AS TTR", ' TTR.RangeFrom <= SAL.TimeTaken and TTR.RangeTo >= SAL.TimeTaken','inner');
        
        $this->db->where('DATE_FORMAT(SAL.CreatedDate,"%Y-%m-%d")',$last_day_date);
        $this->db->where('SAL.IsSignup','1');
        $this->db->group_by('TTR.TimeTakenRangeID');
        
        $query = $this->db->get();
        $record_count = $query->num_rows();
        $results = $query->result();
        
        if($record_count > 0){
            $this->db->where('DATE_FORMAT(CreatedDate,"%Y-%m-%d")',$last_day_date);
            $this->db->delete(TIMETAKENRANGECOUNT);

            foreach($results as $tima_range_arr){
                $TimeRangeArr = (array)$tima_range_arr;
                $rtn = $this->db->insert(TIMETAKENRANGECOUNT, $TimeRangeArr);
            }
        }
        
        return $rtn;        
    }

        
}//End of file cron_model.php
