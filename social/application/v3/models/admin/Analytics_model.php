<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class analytics_model extends Admin_Common_Model
{
	public function __construct()
	{
        parent::__construct();
	}
        
    /**
     * Function for get Email Analytics on Email Analytic Page
     * Parameters : $start_date, $end_date
     * Return : Email Analytics array
     */
    public function getEmailAnalyticsData($start_date, $end_date)
    {
        $email_analytics_result = array();
        
        $this->db->select('COUNT(C.CommunicationID) AS CommunicationCount', FALSE);
        $this->db->select('ET.EmailTypeID AS EmailTypeID', FALSE);
        $this->db->select('ET.Name AS EmailType', FALSE);

        $this->db->join(EMAILTYPES." AS ET", ' ET.EmailTypeID = C.EmailTypeID','left');
        $this->db->from(COMMUNICATIONS . " AS C");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(C.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('C.EmailTypeID');

        $query = $this->db->get();
        $results = $query->result_array();
        //Assign Result in : email analytics array
        $email_analytics_result = $results;

        return $email_analytics_result;
    }

    /**
     * Function for get Login Analytics on Login Analytic Page
     * Parameters : $start_date, $end_date, $filter
     * $filter : 1=Monthly, 2=Weekly, 3=Daily, 
     * Return : Login Analytics array
     */
    public function getLoginLineChartData($start_date, $end_date, $filter)
    {
        $login_line_chart_result = array();
        
        $this->db->select('SUM(LoginCount) AS LoginCount', FALSE);

        switch($filter)
        {
            case '1':
                $this->db->select('DATE_FORMAT(CreatedDate,"%M, %Y") AS MonthName', FALSE);
            break;

            case '2':
                $this->db->select('DATE_FORMAT(CreatedDate,"%M-%W") AS Weeks', FALSE);
                $this->db->select('DATE_FORMAT(CreatedDate,"%Y") AS Years', FALSE);
                $this->db->select('WEEK(CreatedDate) AS WeekNumber', FALSE);
            break;

            case '3':
                $this->db->select('DATE_FORMAT(CreatedDate,"%U") AS Daily', FALSE);

                /* Load Global settings */
                $global_settings = $this->config->item("global_settings");
                /* Change date_format into mysql date_format */
                $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
                
                $this->db->select('DATE_FORMAT(CreatedDate, "'.$mysql_date.'") AS CreatedDate', FALSE);
            break;
        }
        
        $this->db->from(ANALYTICLOGINCOUNTS);

        /* start_date, end_date for filters */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        switch($filter)
        {
            case '1':
                $this->db->group_by('MonthName');
            break;

            case '2':
                $this->db->group_by('WeekNumber');
                $this->db->group_by('Years');
            break;

            case '3':
                $this->db->group_by(array("DATE_FORMAT(CreatedDate, '%Y-%m-%d')"));
            break;
        }
        
        switch($filter)
        {
            case '1':
                $this->db->order_by('CreatedDate');
            break;

            case '2':
                $this->db->order_by('CreatedDate');
            break;

            case '3':
                //$this->db->order_by('CreatedDate');
            break;
        }

        $query = $this->db->get();
        //echo $this->db->last_query();
        $results = $query->result_array();
        //Assign Result in : login_line_chart array
        $login_line_chart_result = $results;

        return $login_line_chart_result;
    }

    /**
     * Function for get Source of Login Graph Data
     * Parameters : $start_date, $end_date
     * Return : Login Graph array
     */
    public function getLoginSourceLoginChartData($start_date, $end_date)
    {
        $source_login_graph = array();
        
        $this->db->select('SUM(A.LoginCount) AS LoginCount', FALSE);
        $this->db->select('S.Name AS SourceName', FALSE);
       
        $this->db->join(SOURCES." AS S", ' S.SourceID = A.LoginSourceID','left');
        $this->db->from(ANALYTICLOGINCOUNTS . " AS A");

        /* start_date, end_date for */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(A.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('SourceName');
        
        $query = $this->db->get();
        $source_login_results = $query->result_array();
        //Assign Result in : source_login_graph array
        $source_login_graph = $source_login_results;
        
        return $source_login_graph;
    }

    /**
     * Function for get Login Device Data
     * Parameters : $start_date, $end_date
     * Return : Login Device array
     */
    public function getLoginDeviceChartData($start_date, $end_date)
    {
        $device_graph = array();
        
        $this->db->select('SUM(A.LoginCount) AS LoginCount', FALSE);
        $this->db->select('D.Name AS DeviceTypeName', FALSE);
       
        $this->db->join(DEVICETYPES." AS D", ' D.DeviceTypeID = A.DeviceTypeID','left');
        $this->db->from(ANALYTICLOGINCOUNTS . " AS A");

        /* start_date, end_date for */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(A.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('DeviceTypeName');
        
        $query = $this->db->get();
        $device_results = $query->result_array();
        //Assign Result in : device_graph array
        $device_graph = $device_results;
        
        return $device_graph;
    }

    /**
     * Function for get username/email Data
     * Parameters : $start_date, $end_date
     * Return : Login Username/Email array
     */
    public function getLoginUsernameAndEmailChartData($start_date, $end_date)
    {
        $username_email_graph = array();
        
        //First get Email login count
        $this->db->select('SUM(A.CountByEmail) AS LoginCount', FALSE);

        $this->db->from(ANALYTICLOGINCOUNTS . " AS A");
        
        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(A.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }
        
        //$this->db->group_by('CountByEmail');
        $query = $this->db->get();
        $email_results = $query->result_array();
        
        $mofid_email_results = array();
        if(!empty($email_results))
        {
            foreach($email_results as $key=>$val)
            {
                if($val['LoginCount'] != '' && $val['LoginCount'] != 0)
                {
                    $mofid_email_results[$key]['LoginCount'] = $val['LoginCount'];
                    $mofid_email_results[$key]['UserNameVsEmail'] = "Email";
                }
            }
        }

        //Now get Username login count
        $this->db->select('SUM(A.CountByUsername) AS LoginCount', FALSE);
               
        $this->db->from(ANALYTICLOGINCOUNTS . " AS A");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(A.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }
        
        //$this->db->group_by('CountByUsername');
        $query = $this->db->get();
        $username_results = $query->result_array();
        
        $mofid_username_results = array();
        if(!empty($username_results))
        {
            foreach($username_results as $key=>$val)
            {
                if($val['LoginCount'] != '' && $val['LoginCount'] != '0')
                {
                    $mofid_username_results[$key]['LoginCount'] = $val['LoginCount'];
                    $mofid_username_results[$key]['UserNameVsEmail'] = "UserName";
                }
            }
        }
        //Assign Result in : username_email_graph array
        $username_email_graph = array_merge($mofid_email_results,$mofid_username_results);
        return $username_email_graph;
    }


    /**
     * Function for get FirstTime Data
     * Parameters : $start_date, $end_date
     * Return : Login FirstTime array
     */
    public function getLoginFirstTimeChartData($start_date, $end_date)
    {
        $first_time_graph = array();
        
        $this->db->select('SUM(A.FirstTimeLoginCount) AS LoginCount', FALSE);
        $this->db->select('S.Name AS Type', FALSE);
       
        $this->db->join(SOURCES." AS S", ' S.SourceID = A.LoginSourceID','left');
        $this->db->from(ANALYTICLOGINFIRSTCOUNT . " AS A");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(A.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }
        
        $this->db->group_by('Type');

        $query = $this->db->get();
        $first_time_results = $query->result_array();
        //Assign Result in : firsttime array
        $first_time_graph = $first_time_results;
        return $first_time_graph;
    }

    /**
     * Function for get Popular Days login Data
     * Parameters : $start_date, $end_date
     * Return : Popular Days array
     */
    public function getLoginPopularDaysChartData($start_date, $end_date)
    {
        $poopular_days_graph = array();
        
        $this->db->select('SUM(A.LoginCount) AS LoginCount', FALSE);
        $this->db->select('W.Name AS WeekDayName', FALSE);
       
        $this->db->join(WEEKDAYS." AS W", ' W.WeekdayID = A.WeekdayID','left');
        $this->db->from(ANALYTICLOGINCOUNTS . " AS A");

        /* start_date, end_date for */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(A.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->order_by('W.WeekdayID');
        $this->db->group_by('WeekDayName');        

        $query = $this->db->get();        
        $popular_days_results = $query->result_array();
        //Assign Result in : poopular_days_graph array
        $poopular_days_graph = $popular_days_results;
        
        return $poopular_days_graph;
    }

    /**
     * Function for get Popular Time login Data
     * Parameters : $start_date, $end_date
     * Return : Popular Time array
     */
    public function getLoginPopularTimeChartData($start_date, $end_date)
    {
        $popular_time_graph = array();
        
        $this->db->select('SUM(A.LoginCount) AS LoginCount', FALSE);
        $this->db->select('T.Name AS TimeSlotName', FALSE);
        $this->db->select('T.TimeSlotID', FALSE);
       
        $this->db->join(TIMESLOTS." AS T", ' T.TimeSlotID = A.TimeSlotID','left');
        $this->db->from(ANALYTICLOGINCOUNTS . " AS A");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(A.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('A.TimeSlotID');

        $query = $this->db->get();
        $popular_time_results = $query->result_array();
        //Assign Result in : popular_time_results array
        $popular_time_graph = $popular_time_results;
        
        return $popular_time_graph;
    }


    /**
     * Function for get login failure Data
     * Parameters : $start_date, $end_date
     * Return : login failure array
     */
    public function getLoginFailureChartData($start_date, $end_date)
    {
        $faliure_graph = array();
        
        $this->db->select('SUM(A.ErrorCount) AS ErrorCount', FALSE);
        $this->db->select('C.Description AS Description', FALSE);
       
        $this->db->join(CLIENTERRORS." AS C", ' C.ClientErrorID = A.ClientErrorID','left');
        $this->db->from(ANALYTICLOGINERRORS . " AS A");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(A.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('A.ClientErrorID');
        
        $query = $this->db->get();
        $faliure_results = $query->result_array();
        //Assign Result in : faliure_results array
        $faliure_graph = $faliure_results;
        
        return $faliure_graph;
    }

    /**
     * Function for get login Geo Data
     * Parameters : $start_date, $end_date
     * Return : Login Geo array
     */
    public function getLoginGeoChartData($start_date, $end_date)
    {
        $geo_graph = array();
        
        $this->db->select('SUM(A.LoginCount) AS LoginCount', FALSE);
        $this->db->select('A.CityStateCountry', FALSE);

        $this->db->from(ANALYTICLOGINGEOCOUNT . " AS A");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(A.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('A.CityStateCountry');
        
        $query = $this->db->get();
        $geo_results = $query->result_array();
        //Assign Result in : geo_results array
        $geo_graph = $geo_results;
        
        return $geo_graph;
    }

    /**
     * Function for get Signup Analytics on Signup Analytic Page
     * Parameters : $start_date, $end_date, $filter
     * $filter : 1=Monthly, 2=Weekly, 3=Daily, 
     * Return : Signup Analytics array
     */
    public function getSignupLineChartData($start_date, $end_date, $filter)
    {
        $signup_line_chart_result = array();
        
        $this->db->select('SUM(SignUpCount) AS SignUpCount', FALSE);
        
        switch($filter)
        {
            case '1':
                $this->db->select('DATE_FORMAT(CreatedDate,"%M, %Y") AS MonthName', FALSE);
            break;

            case '2':
                $this->db->select('DATE_FORMAT(CreatedDate,"%M-%W") AS Weeks', FALSE);
                $this->db->select('DATE_FORMAT(CreatedDate,"%Y") AS Years', FALSE);
                $this->db->select('WEEK(CreatedDate) AS WeekNumber', FALSE);
            break;

            case '3':
                $this->db->select('DATE_FORMAT(CreatedDate,"%U") AS Daily', FALSE);
                
                /* Load Global settings */
                $global_settings = $this->config->item("global_settings");
                /* Change date_format into mysql date_format */
                $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
                
                $this->db->select('DATE_FORMAT(CreatedDate, "'.$mysql_date.'") AS CreatedDate', FALSE);
            break;
        }
        
        $this->db->from(SIGNUPANALYTICLOGCOUNTS);

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }
        
        switch($filter)
        {
            case '1':
                $this->db->group_by('MonthName');
            break;

            case '2':
                $this->db->group_by('WeekNumber');
                $this->db->group_by('Years');
            break;

            case '3':
                $this->db->group_by(array("DATE_FORMAT(CreatedDate, '%Y-%m-%d')"));
            break;
        }
        
        switch($filter)
        {
            case '1':
                $this->db->order_by('CreatedDate');
            break;

            case '2':
                $this->db->order_by('CreatedDate');
            break;

            case '3':
                //$this->db->order_by('CreatedDate');
            break;
        }

        $query = $this->db->get();
        $results = $query->result_array();
        //echo $this->db->last_query();die;
        //Assign Result in : signup_line_chart array
        $signup_line_chart_result = $results;

        return $signup_line_chart_result;
    }

    /**
     * Function for get Source of Signup Graph Data
     * Parameters : $start_date, $end_date
     * Return : Signup Graph array
     */
    public function getSignupSourcesignupChartData($start_date, $end_date)
    {
        $source_signup_graph = array();
        
        $this->db->select('SUM(SA.SignUpCount) AS SignUpCount', FALSE);
        $this->db->select('S.Name AS SourceName', FALSE);
       
        $this->db->join(SOURCES." AS S", ' S.SourceID = SA.SignupSourceID','left');
        $this->db->from(SIGNUPANALYTICLOGCOUNTS . " AS SA");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(SA.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('SourceName');
        
        $query = $this->db->get();
        $source_signup_results = $query->result_array();
        //Assign Result in : source_signup_graph array
        $source_signup_graph = $source_signup_results;
        
        return $source_signup_graph;
    }

    /**
     * Function for get Type of Signup Graph Data
     * Parameters : $start_date, $end_date
     * Return : Signup Type Graph array
     */
    public function getSignupTypeChartData($start_date, $end_date)
    {
        $signup_type_graph = array();
        
        $this->db->select('SUM(SA.SignUpCount) AS SignUpCount', FALSE);
        $this->db->select('PT.Name AS TypeName', FALSE);
               
        $this->db->join(PLATEFORMDEVICES." AS PD", ' PD.DeviceTypeID = SA.DeviceTypeID','left');
        $this->db->join(PLATEFORMTYPES." AS PT", ' PT.PlatformTypeID = PD.PlatformTypeID','left');

        
        $this->db->from(SIGNUPANALYTICLOGCOUNTS . " AS SA");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(SA.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('TypeName');
        
        $query = $this->db->get();
        $signup_type_results = $query->result_array();
        //Assign Result in : type_graph array
        $signup_type_graph = $signup_type_results;
        
        return $signup_type_graph;
    }

    /**
     * Function for get signup Device Data
     * Parameters : $start_date, $end_date
     * Return : Signup Device array
     */
    public function getSignupDeviceChartData($start_date, $end_date)
    {
        $device_graph = array();
        
        $this->db->select('SUM(SA.SignUpCount) AS SignUpCount', FALSE);
        $this->db->select('D.Name AS DeviceTypeName', FALSE);
       
        $this->db->join(DEVICETYPES." AS D", ' D.DeviceTypeID = SA.DeviceTypeID','left');
        $this->db->from(SIGNUPANALYTICLOGCOUNTS . " AS SA");

        /* start_date, end_date for */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(SA.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('DeviceTypeName');
        
        $query = $this->db->get();
        $device_results = $query->result_array();
        //Assign Result in : device_graph array
        $device_graph = $device_results;
        
        return $device_graph;
    }

    /**
     * Function for get visits/signup Data
     * Parameters : $start_date, $end_date
     * Return : Signup visits/signup array
     */
    public function getSignupVisitsSignupChartData($start_date, $end_date)
    {
        $visit_signup_graph = array();
        
        //First get Visits count
        $this->db->select('SUM(SA.FirstTimeVisitCount) AS Counts', FALSE);
               
        $this->db->from(SIGNUPANALYTICVISITCOUNT . " AS SA");
        
        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(SA.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }
        
        //$this->db->group_by('FirstTimeVisitCount');

        $query = $this->db->get();
        $visit_results = $query->result_array();
      
        $mofid_visit_results = array();
        if(!empty($visit_results))
        {
            foreach($visit_results as $key=>$val)
            {
                if($val['Counts'] != '' && $val['Counts'] != 0)
                {
                    $mofid_visit_results[$key]['Counts'] = $val['Counts'];
                    $mofid_visit_results[$key]['Type'] = "Total_Visited";
                }
            }
        }

        //Now get Signup count
        $this->db->select('SUM(SA.SecondTimeVisitCount) AS Counts', FALSE);
               
        $this->db->from(SIGNUPANALYTICVISITCOUNT . " AS SA");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(SA.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }
        
        //$this->db->group_by('SecondTimeVisitCount');

        $query = $this->db->get();
        $signup_results = $query->result_array();
        
        $mofid_signup_results = array();
        if(!empty($signup_results))
        {
            foreach($signup_results as $key=>$val)
            {
                if($val['Counts'] != '' && $val['Counts'] != '0')
                {
                    $mofid_signup_results[$key]['Counts'] = $val['Counts'];
                    $mofid_signup_results[$key]['Type'] = "Total_Registered";
                }
            }
        }
        //Assign Result in : visit_signup_graph array
        $visit_signup_graph = array_merge($mofid_visit_results,$mofid_signup_results);
        return $visit_signup_graph;
    }

    /**
     * Function for get Signup Time Data
     * Parameters : $start_date, $end_date
     * Return : Signup Time array
     */
    public function getSignupTimeChartData($start_date, $end_date)
    {
        $signup_time_graph = array();
        
        $this->db->select('SUM(TTC.SignUpCount) AS SignUpCount', FALSE);
        $this->db->select('TTR.Name AS TimeRange', FALSE);
       
        $this->db->join(TIMETAKENRANGE." AS TTR", ' TTR.TimeTakenRangeID = TTC.TimeTakenRangeID','left');
        $this->db->from(TIMETAKENRANGECOUNT . " AS TTC");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(TTC.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }
        
        $this->db->group_by('TimeRange');

        $query = $this->db->get();
        $signup_time_results = $query->result_array();
        //Assign Result in : signup_time_graph array
        $signup_time_graph = $signup_time_results;
        return $signup_time_graph;
    }

    /**
     * Function for get Popular Days signup Data
     * Parameters : $start_date, $end_date
     * Return : Popular Days array
     */
    public function getSignupPopularDaysChartData($start_date, $end_date)
    {
        $poopular_days_graph = array();
        
        $this->db->select('SUM(SA.SignUpCount) AS SignUpCount', FALSE);
        $this->db->select('W.Name AS WeekDayName', FALSE);
       
        $this->db->join(WEEKDAYS." AS W", ' W.WeekdayID = SA.WeekdayID','left');
        $this->db->from(SIGNUPANALYTICLOGCOUNTS . " AS SA");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(SA.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->order_by('W.WeekdayID');
        $this->db->group_by('WeekDayName');

        $query = $this->db->get();
        $popular_days_results = $query->result_array();
        //Assign Result in : popular_days array
        $poopular_days_graph = $popular_days_results;
        
        return $poopular_days_graph;
    }

    /**
     * Function for get Popular Time Signup Data
     * Parameters : $start_date, $end_date
     * Return : Popular Time array
     */
    public function getSignupPopularTimeChartData($start_date, $end_date)
    {
        $popular_time_graph = array();
        
        $this->db->select('SUM(SA.SignUpCount) AS SignUpCount', FALSE);
        $this->db->select('T.Name AS TimeSlotName', FALSE);
        $this->db->select('T.TimeSlotID', FALSE);
       
        $this->db->join(TIMESLOTS." AS T", ' T.TimeSlotID = SA.TimeSlotID','left');
        $this->db->from(SIGNUPANALYTICLOGCOUNTS . " AS SA");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(SA.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('SA.TimeSlotID');

        $query = $this->db->get();
        $popular_time_results = $query->result_array();
        //Assign Result in : popular_time_results array
        $popular_time_graph = $popular_time_results;
        
        return $popular_time_graph;
    }

    /**
     * Function for get signup Geo Data
     * Parameters : $start_date, $end_date, $right_filter
     * Return : Signup Geo array
     */
    public function getSignupGeoChartData($start_date, $end_date, $right_filter)
    {
        $geo_graph = array();

        if($right_filter == 1)
        {
            $this->db->select('SUM(SA.SignUpCount) AS VisitCount', FALSE);
        }else{
            $this->db->select('SUM(SA.VisitCount) AS VisitCount', FALSE);
        }
        
        $this->db->select('SA.CityStateCountry', FALSE);

        $this->db->from(SIGNUPANALYTICGEOCOUNT . " AS SA");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(SA.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('SA.CityStateCountry');
        
        $query = $this->db->get();
        $geo_results = $query->result_array();
        //Assign Result in : geo_results array
        $geo_graph = $geo_results;
        
        return $geo_graph;
    }
    
    /**
     * Function to get analytic providers list
     * Parameters : 
     * Return : array
     */
    function getAnalyticsProviders() {
        
        $this->db->select('AP.AnalyticsProvidersID AS AnalyticsProvidersID', FALSE);
        $this->db->select('AP.Name AS ProviderName', FALSE);
        $this->db->select('APD.AnalyticsProvidersDataID as AnalyticsProvidersDataID', FALSE);
        $this->db->select('APD.Value as AnalyticsData', FALSE);
       
        $this->db->join(ANALYTICSPROVIDERSDATA." AS APD", ' APD.AnalyticsProvidersID = AP.AnalyticsProvidersID','inner');
        $this->db->from(ANALYTICSPROVIDERS . " AS AP");
        
        $this->db->where('AP.StatusID',2);
        
        $this->db->order_by('AP.Name');

        $query = $this->db->get();
        $results = $query->result_array();
        return $results;
    }
    
    /**
     * Function to get analytic provider detail
     * Parameters : 
     * Return : array
     */
    function getAnalyticsProvidersDetailById($AnalyticsProvidersID) {
        
        $this->db->select('AP.AnalyticsProvidersID AS AnalyticsProvidersID', FALSE);
        $this->db->select('AP.Name AS ProviderName', FALSE);
        $this->db->select('APD.AnalyticsProvidersDataID as AnalyticsProvidersDataID', FALSE);
        $this->db->select('APD.Value as AnalyticsData', FALSE);
       
        $this->db->join(ANALYTICSPROVIDERSDATA." AS APD", ' APD.AnalyticsProvidersID = AP.AnalyticsProvidersID','inner');
        $this->db->from(ANALYTICSPROVIDERS . " AS AP");
        
        $this->db->where('AP.StatusID',2);
        $this->db->where('AP.AnalyticsProvidersID',$AnalyticsProvidersID);

        $query = $this->db->get();
        $results = $query->row_array();
        return $results;
    }
    
    /**
     * Function for update analytic tools data
     * @param array $dataArr
     * @param integer $ProviderId
     * @return integer
     */
    function updateAnalyticToolsData($dataArr,$ProviderId){
        
        $this->db->where('AnalyticsProvidersID', $ProviderId);
        $this->db->update(ANALYTICSPROVIDERSDATA, $dataArr);
        return $this->db->affected_rows();        
    }
    
    
/****************** EMAIL ANALYTICS SECTION ***********************/
    /**
     * Function for get Mandrill Email Analytics on Email Analytic Page
     * Parameters : $start_date, $end_date
     * Return : Email Analytics array
     */
    public function getMandrillEmailAnalyticsChartData($start_date, $end_date){
        $this->db->select('COUNT(MT.MTID) AS MessageCount', FALSE);
        $this->db->select('T.TagID AS EmailTypeID', FALSE);
        $this->db->select('T.Name AS EmailType', FALSE);

        $this->db->join(MESSAGETAGS." AS MT", ' T.TagID = MT.TagID','inner');
        $this->db->from(MANDRILLTAGS . " AS T");

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(MT.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('MT.TagID');
        
        $query = $this->db->get();
        $results = $query->result_array();
        return $results;
    }
    
    /**
     * Function for get Mandrill Email analytics Analytics line chart data
     * Parameters : $start_date, $end_date, $filter
     * $filter : 1=Monthly, 2=Weekly, 3=Daily, 4=Hourly, 
     * Return : line chart Analytics array
     */
    public function getMandrillEmailAnalyticsLineChartData($start_date, $end_date, $EmailTypes, $Filter){
        
        switch($Filter)
        {
            case '1':
                $this->db->select('DATE_FORMAT(M.CreatedDate,"%M, %Y") AS MonthName', FALSE);
            break;

            case '2':
                $this->db->select('DATE_FORMAT(M.CreatedDate,"%M-%W") AS Weeks', FALSE);
                $this->db->select('DATE_FORMAT(M.CreatedDate,"%Y") AS Years', FALSE);
                $this->db->select('WEEK(M.CreatedDate) AS WeekNumber', FALSE);
            break;

            case '3':
                $this->db->select('DATE_FORMAT(M.CreatedDate,"%U") AS Daily', FALSE);

                /* Load Global settings */
                $global_settings = $this->config->item("global_settings");
                /* Change date_format into mysql date_format */
                $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
                
                $this->db->select('DATE_FORMAT(M.CreatedDate, "'.$mysql_date.'") AS CreatedDate', FALSE);
            break;
        
            case '4':
                $this->db->select('DATE_FORMAT(M.CreatedDate,"%W,%M %D,%Y %h %p") AS CreatedDate', FALSE);
            break;
        }
        
        $this->db->select('count(M.MessageID) AS MessageCount', FALSE);
        $this->db->join(MESSAGETAGS." AS MT", ' M.MessageID = MT.MessageID','inner');
        $this->db->from(MANDRILLMESSAGES.' as M');
        $this->db->where('MT.TagID',$EmailTypes);
        
        /* start_date, end_date for filters */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(M.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        switch($Filter)
        {
            case '1':
                $this->db->group_by('MonthName');
            break;

            case '2':
                $this->db->group_by('WeekNumber');
                $this->db->group_by('Years');
            break;

            case '3':
                $this->db->group_by(array("DATE_FORMAT(M.CreatedDate, '%Y-%m-%d')"));
            break;
            
            case '4':
                $this->db->group_by(array("DATE_FORMAT(M.CreatedDate, '%Y-%m-%d %h')"));
            break;
        }
        
        switch($Filter)
        {
            case '1':
                $this->db->order_by('M.CreatedDate');
            break;

            case '2':
                $this->db->order_by('M.CreatedDate');
            break;

            case '3':
                //$this->db->order_by('M.CreatedDate');
            break;
        
            case '4':
                $this->db->order_by('M.CreatedDate');
            break;
        }
        
        $query = $this->db->get();
        $results = $query->result_array();
        return $results;
    }
    
    
    /**
     * Function for get Email Analytics Statistcs list
     * Parameters : start_offset, end_offset, sort_by, order_by, email_types
     * Return : data array
     */
    public function getMandrillEmailAnalyticsStatistcs($start_date = '', $end_date = '', $StartOffset = 0, $EndOffset = "", $SortBy = "", $OrderBy = "", $EmailType = 2){

        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");
        /* Change date_format into mysql date_format */
        $mysql_date =  dateformat_php_to_mysql($global_settings['date_format']);

        $this->db->select('DATE_FORMAT(M.CreatedDate, "'.$mysql_date.'") AS CreatedDate', FALSE);
        $this->db->select('COUNT(M.MessageID) AS MessageSent', FALSE);
        $this->db->select('GROUP_CONCAT(M.MessageID) as MessageIDs',FALSE);
        $this->db->join(MESSAGETAGS." AS MT", ' M.MessageID = MT.MessageID','inner');
        $this->db->from(MANDRILLMESSAGES.' as M');
        $this->db->where('MT.TagID',$EmailType);        
        $this->db->group_by(array("DATE_FORMAT(M.CreatedDate, '%Y-%m-%d')"));
        
        /* start_date, end_date for filters */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(M.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }
        
        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Sort_by, Order_by */
        if ($SortBy == '')
            $SortBy = 'CreatedDate';

        if ($OrderBy == false || $OrderBy == '')
            $OrderBy = 'ASC';

        if ($OrderBy == 'true')
            $OrderBy = 'DESC';

        $this->db->order_by($SortBy, $OrderBy);
        
        /* Start_offset, end_offset */
        if (isset($StartOffset) && $EndOffset != '')
            $this->db->limit($EndOffset, $StartOffset);


        $query = $this->db->get();
        
        $msgArr = $query->result_array();
        $messageArr = array();
        foreach($msgArr as $msgData){
            $tempArr['CreatedDate'] = $msgData['CreatedDate'];
            $tempArr['MessageSent'] = $msgData['MessageSent'];
            
            $this->db->select('SUM(CASE ME.EventID WHEN 8 THEN 1 ELSE 0 END) AS HardBounce', FALSE);
            $this->db->select('SUM(CASE ME.EventID WHEN 7 THEN 1 ELSE 0 END) AS SoftBounce', FALSE);
            $this->db->select('SUM(CASE ME.EventID WHEN 1 THEN 1 ELSE 0 END) AS Open', FALSE);
            $this->db->select('SUM(CASE ME.EventID WHEN 2 THEN 1 ELSE 0 END) AS Click', FALSE);
            $this->db->join(EVENTS." AS E", ' E.EventID = ME.EventID','inner');
            $this->db->join(MANDRILLMESSAGES." AS M", ' M.MessageID = ME.MessageID','inner');
            $this->db->from(MESSAGEEVENTS.' as ME');
            $this->db->where_in('ME.MessageID',  explode(",", $msgData['MessageIDs']));
            
            $query = $this->db->get();
            $dataArr = $query->row_array();
            $tempArr['HardBounce'] = ($dataArr['HardBounce']) ? $dataArr['HardBounce'] : 0;
            $tempArr['SoftBounce'] = ($dataArr['SoftBounce']) ? $dataArr['SoftBounce'] : 0;
            $tempArr['Open'] = ($dataArr['Open']) ? $dataArr['Open'] : 0;
            $tempArr['Click'] = ($dataArr['Click']) ? $dataArr['Click'] : 0;
            $messageArr[] = $tempArr;
        }
        //echo $this->db->last_query();die;
        $results['results'] = $messageArr;
        return $results;
    }
        
    /**
     * Function for get Sent Email Statistcs list
     * Parameters : start_offset, end_offset, sort_by, order_by, email_types
     * Return : data array
     */
    public function getMandrillSentEmailStatistcs($StartOffset = 0, $EndOffset = "", $SortBy = "", $OrderBy = "", $EmailType = 2, $SentDate) {

        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        $this->db->select('M.CreatedDate AS CreatedDate', FALSE);
        $this->db->select('M.Sender AS SenderEmail', FALSE);
        $this->db->select('M.Email as ReceiverEmail',FALSE);
        $this->db->select('M.Subject as Subject',FALSE);
        $this->db->select('M.MessageID as MessageID',FALSE);
        $this->db->join(MESSAGETAGS." AS MT", ' MT.MessageID = M.MessageID','inner');
        $this->db->from(MANDRILLMESSAGES.' as M');
        $this->db->where('MT.TagID',$EmailType);
        if($SentDate != ""){
            $this->db->where("DATE_FORMAT(M.CreatedDate, '%Y-%m-%d') = '".date("Y-m-d",strtotime($SentDate))."' ");
        }
                
        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Sort_by, Order_by */
        if ($SortBy == '')
            $SortBy = 'CreatedDate';

        if ($OrderBy == false || $OrderBy == '')
            $OrderBy = 'ASC';

        if ($OrderBy == 'true')
            $OrderBy = 'DESC';

        $this->db->order_by($SortBy, $OrderBy);
        
        /* Start_offset, end_offset */
        if (isset($StartOffset) && $EndOffset != '')
            $this->db->limit($EndOffset, $StartOffset);


        $query = $this->db->get();
        $msgArr = $query->result_array();
        $messageArr = array();
        foreach($msgArr as $msgData){
            $tempArr['MessageID'] = $msgData['MessageID'];
            $tempArr['CreatedDate'] = date($global_settings['date_format'],strtotime($msgData['CreatedDate']));;
            $tempArr['SenderEmail'] = $msgData['SenderEmail'];
            $tempArr['ReceiverEmail'] = $msgData['ReceiverEmail'];
            $tempArr['Subject'] = $msgData['Subject'];
            
            $this->db->select('SUM(CASE ME.EventID WHEN 2 THEN 1 ELSE 0 END) AS Click', FALSE);
            $this->db->join(EVENTS." AS E", ' E.EventID = ME.EventID','inner');
            $this->db->from(MESSAGEEVENTS.' as ME');
            $this->db->where('ME.MessageID',  $msgData['MessageID']);
            
            $query = $this->db->get();
            $dataArr = $query->row_array();
            $tempArr['Click'] = ($dataArr['Click']) ? $dataArr['Click'] : 0;
            $messageArr[] = $tempArr;
        }
        
        //echo $this->db->last_query();die;
        $results['results'] = $messageArr;
        return $results;
    }
    
    /**
     * Function for get Email click urls list
     * Parameters : email_types, sent_date
     * Return : data array
     */
    public function getEmailClickUrlsList($email_types = 3, $sent_date) {
        
        $this->db->select('GROUP_CONCAT(M.MessageID) as MessageIDs',FALSE);
        $this->db->join(MESSAGETAGS." AS MT", ' MT.MessageID = M.MessageID','inner');
        $this->db->from(MANDRILLMESSAGES.' as M');
        $this->db->where('MT.TagID',$email_types);
        if($sent_date != ""){
            $this->db->where("DATE_FORMAT(M.CreatedDate, '%Y-%m-%d') = '".date("Y-m-d",strtotime($sent_date))."' ");
        }
        $query = $this->db->get();
        $msgArr = $query->row_array();            
        
        
        // For get Urls and it's count
        $this->db->select('ME.Url AS Url', FALSE);
        $this->db->select('COUNT(ME.MEID) AS ClickCount', FALSE);
        $this->db->from(MESSAGEEVENTS.' as ME');
        $this->db->where_in('ME.MessageID',  explode(",", $msgArr['MessageIDs']));
        $this->db->where('ME.Url != ', "");
        $this->db->where('ME.EventID', 2);
        $this->db->group_by(array("ME.Url"));
        
        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();
        
        $new_query = $this->db->get();
        $messageArr = $new_query->result_array();
        
        $results['results'] = $messageArr;
        return $results;
    }
    
    
    /**
     * Function for get SMTP Email Analytics on Email Analytic Page
     * Parameters : $start_date, $end_date
     * Return : Email Analytics array
     */
    public function getSmtpEmailAnalyticsChartData($start_date, $end_date)
    {
        $this->db->select('COUNT(C.CommunicationID) AS MessageCount', FALSE);
        $this->db->select('ET.EmailTypeID AS EmailTypeID', FALSE);
        $this->db->select('ET.Name AS EmailType', FALSE);

        $this->db->join(EMAILTYPES." AS ET", ' ET.EmailTypeID = C.EmailTypeID','left');
        $this->db->from(COMMUNICATIONS . " AS C");
        $this->db->where("C.ESPID",1);

        /* start_date, end_date */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(C.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        $this->db->group_by('C.EmailTypeID');
        
        $query = $this->db->get();
        $results = $query->result_array();
        return $results;
    }
    
    /**
     * Function for get Email analytics Analytics line chart data
     * Parameters : $start_date, $end_date, $filter
     * $filter : 1=Monthly, 2=Weekly, 3=Daily, 4=Hourly, 
     * Return : line chart Analytics array
     */
    public function getSmtpEmailAnalyticsLineChartData($start_date, $end_date, $EmailTypes, $Filter){
        
        switch($Filter)
        {
            case '1':
                $this->db->select('DATE_FORMAT(C.CreatedDate,"%M, %Y") AS MonthName', FALSE);
            break;

            case '2':
                $this->db->select('DATE_FORMAT(C.CreatedDate,"%M-%W") AS Weeks', FALSE);
                $this->db->select('DATE_FORMAT(C.CreatedDate,"%Y") AS Years', FALSE);
                $this->db->select('WEEK(C.CreatedDate) AS WeekNumber', FALSE);
            break;

            case '3':
                $this->db->select('DATE_FORMAT(C.CreatedDate,"%U") AS Daily', FALSE);
                /* Load Global settings */
                $global_settings = $this->config->item("global_settings");
                $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
                
                $this->db->select('DATE_FORMAT(C.CreatedDate, "'.$mysql_date.'") AS CreatedDate', FALSE);
            break;
        
            case '4':
                $this->db->select('DATE_FORMAT(C.CreatedDate,"%W,%M %D,%Y %h %p") AS CreatedDate', FALSE);
            break;
        }
        
        $this->db->select('count(C.CommunicationID) AS MessageCount', FALSE);
        $this->db->from(COMMUNICATIONS.' as C');
        $this->db->where('C.EmailTypeID',$EmailTypes);
        
        
        /* start_date, end_date for filters */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(C.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }

        switch($Filter)
        {
            case '1':
                $this->db->group_by('MonthName');
            break;

            case '2':
                $this->db->group_by('WeekNumber');
                $this->db->group_by('Years');
            break;

            case '3':
                $this->db->group_by(array("DATE_FORMAT(C.CreatedDate, '%Y-%m-%d')"));
            break;
            
            case '4':
                $this->db->group_by(array("DATE_FORMAT(C.CreatedDate, '%Y-%m-%d %h')"));
            break;
        }
        
        switch($Filter)
        {
            case '1':
                $this->db->order_by('C.CreatedDate');
            break;

            case '2':
                $this->db->order_by('C.CreatedDate');
            break;

            case '3':
                //$this->db->order_by('C.CreatedDate');
            break;
        
            case '4':
                $this->db->order_by('C.CreatedDate');
            break;
        }
        
        $query = $this->db->get();
        $results = $query->result_array();
        return $results;
    }
    
    /**
     * Function for get SMTP Email Analytics Statistcs list
     * Parameters : start_offset, end_offset, sort_by, order_by, email_types
     * Return : data array
     */
    public function getSmtpEmailAnalyticsStatistcs($start_date = '', $end_date = '', $StartOffset = 0, $EndOffset = "", $SortBy = "", $OrderBy = "", $EmailType = 2) {

        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");
        $mysql_date =  dateformat_php_to_mysql($global_settings['date_format']);

        $this->db->select('DATE_FORMAT(C.CreatedDate, "'.$mysql_date.'") AS CreatedDate', FALSE);
        $this->db->select('COUNT(C.CommunicationID) AS MessageSent', FALSE);
        $this->db->from(COMMUNICATIONS.' as C');
        $this->db->where('C.EmailTypeID',$EmailType);
        $this->db->group_by(array("DATE_FORMAT(C.CreatedDate, '%Y-%m-%d')"));
        
        /* start_date, end_date for filters */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
            
            $this->db->where('DATE(C.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'" ');
        }
        
        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Sort_by, Order_by */
        if ($SortBy == '')
            $SortBy = 'CreatedDate';

        if ($OrderBy == false || $OrderBy == '')
            $OrderBy = 'ASC';

        if ($OrderBy == 'true')
            $OrderBy = 'DESC';

        $this->db->order_by($SortBy, $OrderBy);
        
        /* Start_offset, end_offset */
        if (isset($StartOffset) && $EndOffset != '')
            $this->db->limit($EndOffset, $StartOffset);


        $query = $this->db->get();
        
        $msgArr = $query->result_array();
        $messageArr = array();
        foreach($msgArr as $msgData){
            $tempArr['CreatedDate'] = $msgData['CreatedDate'];
            $tempArr['MessageSent'] = $msgData['MessageSent'];
            $tempArr['HardBounce'] = 0;
            $tempArr['SoftBounce'] = 0;
            $tempArr['Open'] = 0;
            $tempArr['Click'] = 0;
            $messageArr[] = $tempArr;
        }
        //echo $this->db->last_query();die;
        $results['results'] = $messageArr;
        return $results;
    }
    
    /**
     * Function for get SMTP Sent Email Statistcs list
     * Parameters : start_offset, end_offset, sort_by, order_by, email_types
     * Return : data array
     */
    public function getSmtpSentEmailStatistcs($StartOffset = 0, $EndOffset = "", $SortBy = "", $OrderBy = "", $EmailType = 2, $SentDate) {

        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        $this->db->select('C.CreatedDate AS CreatedDate', FALSE);
        $this->db->select('C.FromEmail as SenderEmail',FALSE);
        $this->db->select('C.EmailTo as ReceiverEmail',FALSE);
        $this->db->select('C.Subject as Subject',FALSE);
        $this->db->select('C.Body as Body',FALSE);
        $this->db->select('C.CommunicationID as CommunicationID',FALSE);
        
        if($EmailType == 4){
            $this->db->select('BI.StatusID AS userstatusid', FALSE);
            $this->db->join(BETAINVITES." AS BI", ' BI.Email = C.EmailTo','inner');
        }else{
            $this->db->select('U.StatusID AS userstatusid', FALSE);
            $this->db->join(USERS." AS U", ' U.UserID = C.UserID','inner');
        }
        
        $this->db->from(COMMUNICATIONS.' as C');
        $this->db->where('C.EmailTypeID',$EmailType);
        if($SentDate != ""){
            $this->db->where("DATE_FORMAT(C.CreatedDate, '%Y-%m-%d') = '".date("Y-m-d",strtotime($SentDate))."' ");
        }

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Sort_by, Order_by */
        if ($SortBy == '')
            $SortBy = 'CreatedDate';

        if ($OrderBy == false || $OrderBy == '')
            $OrderBy = 'ASC';

        if ($OrderBy == 'true')
            $OrderBy = 'DESC';

        $this->db->order_by($SortBy, $OrderBy);
        
        /* Start_offset, end_offset */
        if (isset($StartOffset) && $EndOffset != '')
            $this->db->limit($EndOffset, $StartOffset);


        $query = $this->db->get();
        $msgArr = $query->result_array();
        $messageArr = array();
        foreach($msgArr as $msgData){
            $tempArr['MessageID'] = $msgData['CommunicationID'];
            $tempArr['CreatedDate'] = date($global_settings['date_format'],strtotime($msgData['CreatedDate']));
            $tempArr['SenderEmail'] = ($msgData['SenderEmail']) ? $msgData['SenderEmail'] : "NA";
            $tempArr['ReceiverEmail'] = $msgData['ReceiverEmail'];
            $tempArr['Subject'] = $msgData['Subject'];            
            $tempArr['Click'] = 0;
            $tempArr['Body'] = $msgData['Body'];
            $tempArr['userstatusid'] = $msgData['userstatusid'];
            $messageArr[] = $tempArr;
        }
        
        //echo $this->db->last_query();die;
        $results['results'] = $messageArr;
        return $results;
    }
    

}//End of file analytics_model.php
