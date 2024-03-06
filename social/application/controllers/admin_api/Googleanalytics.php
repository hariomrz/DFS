<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All google analytics related process like
 * @package    Analytics
 * @author     Girish Patidar(19-03-2015)
 * @version    1.0
 */

class Googleanalytics extends Admin_API_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model(array('admin/analytics_model', 'admin/login_model'));
        $this->load->library('GoogleAnalyticsAPI');

        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];


        $this->client = new Google_Client();
        $this->client->setApplicationName("CommonConfig"); // name of your app
        // set assertion credentials
        $this->client->setAssertionCredentials(
                new Google_Auth_AssertionCredentials(
                GA_EMAIL, // email you added to GA
                array('https://www.googleapis.com/auth/analytics.readonly'), file_get_contents(GA_PRIVATE_KEY_LOCATION)  // keyfile you downloaded
        ));

        // other settings
        $this->client->setClientId(GA_CLIENT_ID);           // from API console
        $this->client->setAccessType('offline_access');  // this may be unnecessary?
        $this->service = new Google_Service_Analytics($this->client);
    }

    public function index() {
        
    }

    /**
     * Function for get google analytics line chart data data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of google analytics data
     */
    public function line_chart_post() 
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/line_chart';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL) {

            if (isset($Data['StartDate']))
                $startDate = date("Y-m-d", strtotime($Data['StartDate']));
            else
                $startDate = date('Y-m-d', strtotime('-1 month'));
            if (isset($Data['EndDate']))
                $endDate = date("Y-m-d", strtotime($Data['EndDate']));
            else
                $endDate = date("Y-m-d");
            if (isset($Data['Filter']))
                $filter = $Data['Filter'];
            else
                $filter = 'newUsers';
            if (isset($Data['SubFilter']))
                $sub_filter = $Data['SubFilter'];
            else
                $sub_filter = 'month';

            if (date("Y", strtotime($startDate)) < 2005) {
                $startDate = "2005-01-01";
            }

            if ($sub_filter == "month") {
                $dimensions = 'ga:year,ga:month';
            } else if ($sub_filter == "week") {
                $dimensions = 'ga:year,ga:week';
            } else if ($sub_filter == "day") {
                $dimensions = 'ga:year,ga:month,ga:day';
            } else if ($sub_filter == "hour") {
                $dimensions = 'ga:year,ga:month,ga:day,ga:hour';
            }
            
            $DataArr = array();
            
            if($filter=='registeredUsers')
            {
                $signup_filter = ($sub_filter=="month"?1:($sub_filter=="week"?2:3));
                /* Get data from analytics_model */
                $res = $this->analytics_model->getSignupLineChartData($startDate, $endDate, $signup_filter);
                foreach ($res as $key => $value) 
                {
                    if($sub_filter=='month')
                    {
                        $DataArr[$key]['date'] = $value['MonthName'];
                    }
                    else if($sub_filter=='week')
                    {
                        $DataArr[$key]['date'] = 'week '.$value['WeekNumber'].' ('.$value['Years'].')';
                    }
                    else
                    {
                        $DataArr[$key]['date'] = $value['CreatedDate'];
                    }
                    $DataArr[$key]['pageview'] = $value['SignUpCount'];
                }
                $Return['Data']['lineData'] = $DataArr;
                $Outputs = $Return;
                $this->response($Outputs);
            }

            //For other GA data
            $linemetrics = 'ga:' . $filter . ',ga:visits,ga:sessions,ga:bounces,ga:bounceRate';
            $optParams = array('dimensions' => $dimensions, 'sort' => 'ga:year',);
            $ga_line_data = $this->service->data_ga->get(GA_ACCOUNT_ID, $startDate, $endDate, $linemetrics, $optParams);
            if (isset($ga_line_data['rows'])) {
                foreach ($ga_line_data['rows'] as $row) {
                    if ($sub_filter == "month") {
                        $date = date('M, Y', strtotime($row[0] . '-' . $row[1]));
                        $DataArr[] = array("date" => $date, "pageview" => $row[2], "visits" => $row[3], "sessions" => $row[4], "bounces" => $row[5], "bounceRate" => $row[6]);
                    } else if ($sub_filter == "week") {
                        $date = "week " . $row[1] . " (" . $row[0] . ")";
                        $DataArr[] = array("date" => $date, "pageview" => $row[2], "visits" => $row[3], "sessions" => $row[4], "bounces" => $row[5], "bounceRate" => $row[6]);
                    } else if ($sub_filter == "day") {
                        $date = date('D, M, Y', strtotime($row[0] . '-' . $row[1] . '-' . $row[2]));
                        $DataArr[] = array("date" => $date, "pageview" => $row[3], "visits" => $row[4], "sessions" => $row[5], "bounces" => $row[6], "bounceRate" => $row[7]);
                    } else if ($sub_filter == "hour") {
                        $date = date('D, M, Y h:i A', strtotime($row[0] . '-' . $row[1] . '-' . $row[2] . ' ' . $row[3] . ":00:00"));
                        $DataArr[] = array("date" => $date, "pageview" => $row[4], "visits" => $row[5], "sessions" => $row[6], "bounces" => $row[7], "bounceRate" => $row[8]);
                    }
                }
            }

            $Return['Data']['lineData'] = $DataArr;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function for get google analytics report data
     * Parameters : $start_date, $end_date, $filter
     * Return : Array of google analytics data
     */
    public function report_data_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/report_data';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL) {

            if (isset($Data['StartDate']))
                $startDate = date("Y-m-d", strtotime($Data['StartDate']));
            else
                $startDate = date('Y-m-d', strtotime('-1 month'));
            if (isset($Data['EndDate']))
                $endDate = date("Y-m-d", strtotime($Data['EndDate']));
            else
                $endDate = date("Y-m-d");
            if (isset($Data['Filter']))
                $filter = $Data['Filter'];
            else
                $filter = 'newUsers';

            if (date("Y", strtotime($startDate)) < 2005) {
                $startDate = "2005-01-01";
            }

            //For Overview report
            $reportmetrics = 'ga:visits,ga:visitors,ga:pageviews,ga:bounceRate,ga:percentNewSessions';
            $reportOptParams = array('dimensions' => '', 'sort' => '-ga:pageviews',);
            $ga_basic_data = $this->service->data_ga->get(GA_ACCOUNT_ID, $startDate, $endDate, $reportmetrics, $reportOptParams);
            //print_r($ga_basic_data); die;
            $rowArr = $ga_basic_data['rows'][0];
            $visits = ($rowArr[0]) ? $rowArr[0] : 0;
            $visitors = ($rowArr[1]) ? $rowArr[1] : 0;
            $pageviews = ($rowArr[2]) ? $rowArr[2] : 0;
            $GaDataArr = array("visits" => $visits, "visitors" => $visitors, "pageviews" => $pageviews, "bounceRate" => number_format($rowArr[3], 2, '.', ''), "percentNewSessions" => number_format($rowArr[4], 2, '.', ''));

            $Return['Data']['reportData'] = $GaDataArr;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function for get google analytics popular pages chart data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of google analytics data
     */
    public function popular_pages_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/popular_pages';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL) {

            if (isset($Data['StartDate']))
                $startDate = date("Y-m-d", strtotime($Data['StartDate']));
            else
                $startDate = date('Y-m-d', strtotime('-1 month'));
            if (isset($Data['EndDate']))
                $endDate = date("Y-m-d", strtotime($Data['EndDate']));
            else
                $endDate = date("Y-m-d");
            if (isset($Data['Filter']))
                $filter = $Data['Filter'];
            else
                $filter = 'newUsers';

            if (date("Y", strtotime($startDate)) < 2005) {
                $startDate = "2005-01-01";
            }

            //For popular pages
            $pageMetrics = 'ga:pageviews,ga:visitors';
            $optPageParams = array('dimensions' => 'ga:pagePath', 'sort' => '-ga:pageviews',);
            $ga_page_data = $this->service->data_ga->get(GA_ACCOUNT_ID, $startDate, $endDate, $pageMetrics, $optPageParams);
            $visitPageArr = array();
            if ($ga_page_data['rows']) {
                foreach ($ga_page_data['rows'] as $row) {
                    $visitPageArr[] = array("path" => $row[0], "pageview" => $row[1], "uniqueVisits" => $row[2]);
                }
            }

            $Return['Data']['popularPages'] = $visitPageArr;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function for get google analytics geo chart data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of google analytics data
     */
    public function geo_location_data_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/geo_location_data';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL) {

            if (isset($Data['StartDate']))
                $startDate = date("Y-m-d", strtotime($Data['StartDate']));
            else
                $startDate = date('Y-m-d', strtotime('-1 month'));
            if (isset($Data['EndDate']))
                $endDate = date("Y-m-d", strtotime($Data['EndDate']));
            else
                $endDate = date("Y-m-d");
            if (isset($Data['Filter']))
                $filter = 'Users';// $Data['Filter'];
            else
                $filter = 'Users';

            if (date("Y", strtotime($startDate)) < 2005) {
                $startDate = "2005-01-01";
            }
            if($filter=='registeredUsers')
            {
                $Return['Data']['geoLocation'] = array();
                $Outputs = $Return;
                $this->response($Outputs);
            }
            //For geo location data
            $geoMetrics = 'ga:'. $filter;
            $geoParams = array('dimensions' => 'ga:country,ga:countryIsoCode', 'sort' => 'ga:country',);
            $ga_geo_data = $this->service->data_ga->get(GA_ACCOUNT_ID, $startDate, $endDate, $geoMetrics, $geoParams);
           
            $geoDataArr = array();
            if ($ga_geo_data['rows']) {
                foreach ($ga_geo_data['rows'] as $row) {
                   
                    $geoDataArr[$row[1]] = $row[2];
                }
            }
            //echo "<pre>";print_r($geoDataArr);die;
            $Return['Data']['geoLocation'] =  $geoDataArr;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function for get google analytics device OS chart data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of Analytics OS data
     */
    public function device_os_chart_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/device_os_chart';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL) {

            if (isset($Data['StartDate']))
                $startDate = date("Y-m-d", strtotime($Data['StartDate']));
            else
                $startDate = date('Y-m-d', strtotime('-1 month'));
            if (isset($Data['EndDate']))
                $endDate = date("Y-m-d", strtotime($Data['EndDate']));
            else
                $endDate = date("Y-m-d");
            if (isset($Data['Filter']))
                $filter = $Data['Filter'];
            else
                $filter = 'newUsers';

            if (date("Y", strtotime($startDate)) < 2005) {
                $startDate = "2005-01-01";
            }
            if($filter=='registeredUsers')
            {
                $Return['Data']['geoLocation'] = array();
                $Outputs = $Return;
                $this->response($Outputs);
            }
            //For geo location data
            $Metrics = 'ga:' . $filter;
            $optParams = array('dimensions' => 'ga:operatingSystem', 'sort' => 'ga:operatingSystem',);
            $ga_data = $this->service->data_ga->get(GA_ACCOUNT_ID, $startDate, $endDate, $Metrics, $optParams);

            $DataArr = array();
            if ($ga_data['rows']) {
                foreach ($ga_data['rows'] as $row) {
                    $OS = $row[0];// . ' ' . $row[1];
                    $DataArr[] = array("operatingsystem" => $OS, "Count" => $row[1]);
                }
            }
            $Return['Data']['osData'] = $DataArr;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function for get google analytics browser chart data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of Analytics Browser data
     */
    public function browser_analytics_chart_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/browser_analytics_chart';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL) {

            if (isset($Data['StartDate']))
                $startDate = date("Y-m-d", strtotime($Data['StartDate']));
            else
                $startDate = date('Y-m-d', strtotime('-1 month'));
            if (isset($Data['EndDate']))
                $endDate = date("Y-m-d", strtotime($Data['EndDate']));
            else
                $endDate = date("Y-m-d");
            if (isset($Data['Filter']))
                $filter = $Data['Filter'];
            else
                $filter = 'newUsers';

            if (date("Y", strtotime($startDate)) < 2005) {
                $startDate = "2005-01-01";
            }
            if($filter=='registeredUsers')
            {
                $Return['Data']['geoLocation'] = array();
                $Outputs = $Return;
                $this->response($Outputs);
            }
            //For geo location data
            $Metrics = 'ga:' . $filter;
            $optParams = array('dimensions' => 'ga:browser', 'sort' => 'ga:browser',); //,ga:browserVersion
            $ga_data = $this->service->data_ga->get(GA_ACCOUNT_ID, $startDate, $endDate, $Metrics, $optParams);

            $DataArr = array();
            if ($ga_data['rows']) {
                foreach ($ga_data['rows'] as $row) {
                    $Browser = $row[0];// . ' ' . $row[1];
                    $DataArr[] = array("Browser" => $Browser, "Count" => $row[1]);
                }
            }
            $Return['Data']['BrowserData'] = $DataArr;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function for get google analytics devices report data
     * Parameters : $start_date, $end_date, $filter
     * Return : Array of google analytics devices data
     */
    public function devices_report_data_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/devices_report_data';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL) {

            if (isset($Data['StartDate']))
                $startDate = date("Y-m-d", strtotime($Data['StartDate']));
            else
                $startDate = date('Y-m-d', strtotime('-1 month'));
            if (isset($Data['EndDate']))
                $endDate = date("Y-m-d", strtotime($Data['EndDate']));
            else
                $endDate = date("Y-m-d");
            if (isset($Data['Filter']))
                $filter = $Data['Filter'];
            else
                $filter = 'newUsers';

            if (date("Y", strtotime($startDate)) < 2005) {
                $startDate = "2005-01-01";
            }

            //For Overview report
            $reportmetrics = 'ga:visits,ga:pageviews,ga:users,ga:newUsers,ga:sessions';
            $reportOptParams = array('dimensions' => '', 'sort' => '-ga:pageviews',);
            $ga_basic_data = $this->service->data_ga->get(GA_ACCOUNT_ID, $startDate, $endDate, $reportmetrics, $reportOptParams);
            $rowArr = $ga_basic_data['rows'][0];
            $GaDataArr = array("visits" => $rowArr[0], "pageviews" => $rowArr[1], "users" => $rowArr[2], "newusers" => $rowArr[3], "sessions" => $rowArr[4]);

            $Return['Data']['reportData'] = $GaDataArr;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function for get google analytics device type chart data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of Analytics Browser data
     */
    public function devices_type_chart_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/devices_type_chart';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL) {

            if (isset($Data['StartDate']))
                $startDate = date("Y-m-d", strtotime($Data['StartDate']));
            else
                $startDate = date('Y-m-d', strtotime('-1 month'));
            if (isset($Data['EndDate']))
                $endDate = date("Y-m-d", strtotime($Data['EndDate']));
            else
                $endDate = date("Y-m-d");
            if (isset($Data['Filter']))
                $filter = $Data['Filter'];
            else
                $filter = 'newUsers';

            if (date("Y", strtotime($startDate)) < 2005) {
                $startDate = "2005-01-01";
            }
            $DataArr = array();
            
            if($filter=='registeredUsers')
            {
                /* Get data from analytics_model */
                $res = $this->analytics_model->getSignupDeviceChartData($startDate, $endDate);
                foreach ($res as $key => $value) 
                {
                    $DataArr[$key]['Count'] = $value['SignUpCount'];
                    $DataArr[$key]['Device'] = $value['DeviceTypeName'];
                }
                $Return['Data']['DeviceData'] = $DataArr;
                $Outputs = $Return;
                $this->response($Outputs);
            }

            //For geo location data
            $Metrics = 'ga:' . $filter;
            $optParams = array('dimensions' => 'ga:deviceCategory', 'filters' => 'ga:isMobile==No');
            $optMobileParams = array('dimensions' => 'ga:mobileDeviceInfo', 'filters' => 'ga:isMobile==Yes');
            $ga_data = $this->service->data_ga->get(GA_ACCOUNT_ID, $startDate, $endDate, $Metrics, $optParams);
            $ga_mobile_data = $this->service->data_ga->get(GA_ACCOUNT_ID, $startDate, $endDate, $Metrics, $optMobileParams);


            if ($ga_mobile_data['rows']) {
                foreach ($ga_mobile_data['rows'] as $row) {
                    $DataArr[] = array("Device" => ucwords($row[0]), "Count" => $row[1]);
                }
            }

            if ($ga_data['rows']) {
                foreach ($ga_data['rows'] as $row) {
                    $DataArr[] = array("Device" => ucwords($row[0]), "Count" => $row[1]);
                }
            }
            $Return['Data']['DeviceData'] = $DataArr;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function get_total_users_count_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/line_chart';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if(empty($Data)) {            
            $this->response($Return);
        }

        if (isset($Data['StartDate']))
            $startDate = date("Y-m-d", strtotime($Data['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 month'));
        if (isset($Data['EndDate']))
            $endDate = date("Y-m-d", strtotime($Data['EndDate']));
        else
            $endDate = date("Y-m-d");
        
        $select_array[] = "U.UserID";
        $this->db->select(implode(',', $select_array), FALSE);
            
        $this->db->from(USERS . "  U ");
        ///$this->db->join(PROFILEURL . ' PU ', "U.UserID=PU.EntityID AND PU.EntityType='User'", "left");
        //$this->db->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID', 'left');
       // $this->db->join(CITIES . ' CT ', 'CT.CityID = UD.HomeCityID', 'left');
        
        $this->db->where('DATE(U.CreatedDate) BETWEEN "' . $startDate . '"  AND "' . $endDate . '"', NULL, FALSE);
        
        $this->db->where_not_in('U.StatusID', array(3,4));

        /*$this->db->select("(SELECT COUNT(A.ActivityID) FROM ".ACTIVITY." A WHERE  A.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."') as TotalPosts",false);
         $this->db->select("(SELECT COUNT(L.PostLikeID) FROM ".POSTLIKE." L  WHERE L.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."') as TotalLikes",false);
         $this->db->select("(SELECT COUNT(C.PostCommentID) FROM ".POSTCOMMENTS." C  WHERE C.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."') as TotalComments",false);

          $this->db->select("(SELECT COUNT(DISTINCT(A.UserID)) FROM ".ACTIVITY." A  WHERE A.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."' ) as TotalActiveUsers",false);*/

          $this->db->select("(SELECT COUNT(A.ActivityID) FROM ".ACTIVITY." A Left Join ".USERS." U  ON U.UserID=A.UserID WHERE U.StatusID NOT IN (3,4) AND  A.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."') as TotalPosts",false);
         $this->db->select("(SELECT COUNT(L.PostLikeID) FROM ".POSTLIKE." L  Left Join ".USERS." U  ON U.UserID=L.UserID WHERE U.StatusID NOT IN (3,4) AND L.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."') as TotalLikes",false);
         $this->db->select("(SELECT COUNT(C.PostCommentID) FROM ".POSTCOMMENTS." C  Left Join ".USERS." U  ON U.UserID=C.UserID WHERE U.StatusID NOT IN (3,4) AND C.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."') as TotalComments",false);

          $this->db->select("(SELECT COUNT(DISTINCT(A.UserID)) FROM ".ACTIVITY." A  Left Join ".USERS." U  ON U.UserID=A.UserID WHERE U.StatusID NOT IN (3,4) AND ActivityTypeID !=13 AND  A.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."' ) as TotalActiveUsers",false);

           $this->db->select("(SELECT COUNT(DISTINCT(AL.UserID)) FROM ". ANALYTICLOGINS." AL  Left Join ".USERS." U  ON U.UserID=AL.UserID WHERE U.StatusID NOT IN (3,4) AND AL.IsLoginSuccessfull =1 AND  AL.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."' ) as TotalVisiters",false);

        
        $q = $this->db->get();

        $total_users = $q->num_rows();
        //echo $this->db->last_query();
        $result = $q->result();
        
        $Return['Data']= array(
            'registeredNumberOfUsers' => $total_users
        );
        if(!empty($result)){
        
        $Return['Data']['TotalPosts'] = $result[0]->TotalPosts;
        $Return['Data']['TotalLikes'] = $result[0]->TotalLikes;
        $Return['Data']['TotalComments'] = $result[0]->TotalComments;
        $Return['Data']['TotalActiveUsers'] = $result[0]->TotalActiveUsers;
        $Return['Data']['TotalVisiters'] = $result[0]->TotalVisiters;
        
        } else{

        $Return['Data']['TotalPosts'] = 0;
        $Return['Data']['TotalLikes'] = 0;
        $Return['Data']['TotalComments'] = 0;
        $Return['Data']['TotalActiveUsers'] = 0;
        $Return['Data']['TotalVisiters'] = 0;
        
        } 

        $this->response($Return); die;
    }

   
    public function get_contributors_post() {
        
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/line_chart';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if(empty($Data)) {            
            $this->response($Return);
        }

        if (isset($Data['StartDate']))
            $startDate = date("Y-m-d", strtotime($Data['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 month'));
        if (isset($Data['EndDate']))
            $endDate = date("Y-m-d", strtotime($Data['EndDate']));
        else
            $endDate = date("Y-m-d");
        
       /* $select_array[] = "count(ActivityID) as ActivityCount,A.UserID,U.FirstName,U.LastName,U.ProfilePicture";
        $this->db->select(implode(',', $select_array), FALSE);

        $this->db->select("(SELECT COUNT(L.PostLikeID) As  maxlikes FROM ".POSTLIKE." L  Left Join ".USERS." U  ON U.UserID=L.UserID WHERE U.StatusID NOT IN (3,4) AND L.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."' order by maxlikes ) as TotalLikes",false);
         $this->db->select("(SELECT COUNT(C.PostCommentID) FROM ".POSTCOMMENTS." C  Left Join ".USERS." U  ON U.UserID=C.UserID WHERE U.StatusID NOT IN (3,4) AND C.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."') as TotalComments",false);
            
        $this->db->from(ACTIVITY . "  A ");
        $this->db->join(USERS . ' U ', 'U.UserID = A.UserID', 'left');
        
        $this->db->where('DATE(A.CreatedDate) BETWEEN "' . $startDate . '"  AND "' . $endDate . '"', NULL, FALSE);
        $this->db->order_by('ActivityCount', 'desc');
        $this->db->group_by('UserID');
        $this->db->limit(5,0);
        
        $q = $this->db->get();

        $result = $q->result();*/
         $query = $this->db->query("SELECT EntityID,t.UserID, SUM( total_count ) AS Total,FirstName,LastName,ProfilePicture,sum(total_comment_count) AS NoOfComments,sum(total_like_count) as NoOfLikes
                                    FROM (
                                    (

                                    SELECT COUNT( a.EntityID ) AS total_count, u1.UserID,u1.FirstName,u1.LastName,u1.ProfilePicture ,0 as total_like_count,a.EntityID,COUNT( a.PostCommentID ) AS total_comment_count
                                    FROM PostComments AS a
                                    JOIN Users AS u1 ON u1.UserID = a.UserID
                                    WHERE DATE(a.CreatedDate) BETWEEN '" . $startDate . "'  AND '" . $endDate . "'
                                    GROUP BY a.UserID
                                    ORDER BY a.total_count DESC
                                    )
                                    UNION ALL (

                                    SELECT COUNT( b.EntityID ) AS total_count, u2.UserID,u2.FirstName,u2.LastName,u2.ProfilePicture,COUNT( b.PostLikeID ) AS total_like_count,b.EntityID, 0 as total_comment_count
                                    FROM PostLike AS b
                                    JOIN Users AS u2 ON u2.UserID = b.UserID
                                     WHERE DATE(b.CreatedDate) BETWEEN '" . $startDate . "'  AND '" . $endDate . "'
                                    GROUP BY b.UserID
                                    ORDER BY b.total_count DESC
                                    )
                                    ) AS t
                                    GROUP BY UserID
                                    ORDER BY Total DESC LIMIT 0,30");


         

        $result =$query->result();
        //echo $this->db->last_query();
        
       
         $new=array();
        $records=array();
        // sort with in_array()
        for ($i=0; $i < count($result); $i++) {

            if (!in_array($result[$i]->UserID, $new)) {
                $new[] = $result[$i]->UserID;
                if(count($records)<5) {
                $records[] = $result[$i];
                } else{
                break;    
                }

            }
        }

        if(!empty($records)){
        
        $Return['Data'] = $records;
        
        
        } else{

        $Return['Data'] = 0;
        
        } 

        $this->response($Return); die;
    }
    //top 10 influencer
    public function get_influencers_post() {
        
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/line_chart';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if(empty($Data)) {            
            $this->response($Return);
        }

        if (isset($Data['StartDate']))
            $startDate = date("Y-m-d", strtotime($Data['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 month'));
        if (isset($Data['EndDate']))
            $endDate = date("Y-m-d", strtotime($Data['EndDate']));
        else
            $endDate = date("Y-m-d");
        
        $select_array[] = "DISTINCT(A.UserID),U.FirstName,U.LastName,U.ProfilePicture,(A.NoOfComments+A.NoOfLikes) as TOTALACT,A.NoOfLikes,A.NoOfComments,A.ActivityID";
        $this->db->select(implode(',', $select_array), FALSE);
            
        $this->db->from(ACTIVITY . "  A ");
        $this->db->join(USERS . ' U ', 'U.UserID = A.UserID', 'left');
        
        $this->db->where('DATE(A.CreatedDate) BETWEEN "' . $startDate . '"  AND "' . $endDate . '"', NULL, FALSE);
        $this->db->order_by('(A.NoOfComments+A.NoOfLikes)', 'DESC');
        $this->db->limit(20,0);
        
        $q = $this->db->get();

        $result = $q->result();
        $new=array();
        $records=array();
        // sort with in_array()
        for ($i=0; $i < count($result); $i++) {

            if (!in_array($result[$i]->UserID, $new)) {
                $new[] = $result[$i]->UserID;
                if(count($records)<5) {
                $records[] = $result[$i];
                } else{
                break;    
                }

            }
        }

        if(!empty($records)){
        
        $Return['Data'] = $records;
        
        
        } else{

        $Return['Data'] = 0;
        
        } 

        $this->response($Return); die;
    }
    /** Get Dashboard chart Summary
    **/
    public function get_summary_post() { 
        
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/googleanalytics/get_summary';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if(empty($Data)) {            
            $this->response($Return);
        }

        if (isset($Data['StartDate']))
            $startDate = date("Y-m-d", strtotime($Data['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 month'));
        if (isset($Data['EndDate']))
            $endDate = date("Y-m-d", strtotime($Data['EndDate']));
        else
            $endDate = date("Y-m-d");
            //echo base_url(); die;
        $alexarank=0;    
        $xml = simplexml_load_file("http://data.alexa.com/data?cli=10&url=".base_url());
        if(isset($xml->SD)) {
         $alexarank =     $xml->SD->REACH->attributes();
         }
        
       $q = $this->db->query("SELECT AL.TimeSlotID, Count(*) as UsersCount FROM ". ANALYTICLOGINS." AL  Left Join ".USERS." U  ON U.UserID=AL.UserID WHERE U.StatusID NOT IN (3,4) AND AL.IsLoginSuccessfull = 1 AND  AL.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."' group by AL.TimeSlotID");

       $q1 = $this->db->query("SELECT UD.AndroidAppVersion, Count(*) as UsersCount FROM ". USERS." U Left Join ".USERDETAILS." UD  ON UD.UserID=U.UserID WHERE U.StatusID NOT IN (3,4) AND  U.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."' AND UD.AndroidAppVersion IS NOT NULL AND UD.AndroidAppVersion!='' group by UD.AndroidAppVersion");

        $q2 = $this->db->query("SELECT UD.IOSAppVersion, Count(*) as UsersCount FROM ". USERS." U Left Join ".USERDETAILS." UD  ON UD.UserID=U.UserID WHERE U.StatusID NOT IN (3,4) AND  U.CreatedDate BETWEEN '".$startDate."' AND '".$endDate."' AND UD.IOSAppVersion IS NOT NULL AND UD.IOSAppVersion!='' group by UD.IOSAppVersion");

        $Timeslots = $q->result();
        $AndroidAppVersion = $q1->result();
        $IOSAppVersion = $q2->result();
       

        if(!empty($Timeslots) || !empty($AndroidAppVersion) ||  !empty($IOSAppVersion)){
        $TimeslotsArr =array(); 
        foreach ($Timeslots as $key => $value) {
            $TimeslotsArr[] =$value->UsersCount; 
        }

        $Return['Data']['Timeslots']=$TimeslotsArr;
        $Return['Data']['AndroidAppVersion']=$AndroidAppVersion;
        $Return['Data']['IOSAppVersion']=$IOSAppVersion;
        $Return['Data']['alexarank']=$alexarank;
        
        
        } else{

        $Return['Data']['Timeslots']=0;
        $Return['Data']['AndroidAppVersion']=0;
        $Return['Data']['IOSAppVersion']=0;
        $Return['Data']['alexarank']=0;
        
        } 

        $this->response($Return); die;
    }


}

//End of file googleanalytics.php