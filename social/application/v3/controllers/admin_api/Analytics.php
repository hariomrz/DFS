<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* All analytics related process like : email_analytics, login_analytics, signup_analytics
* @package    Analytics
* @author     ashwin kumar soni(05-01-2015)
* @version    1.0
*/

//require APPPATH.'/libraries/REST_Controller.php';

class Analytics extends Admin_API_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('admin/analytics_model','admin/login_model'));

        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID']; 
          
    }
	
    public function index()
    {
    }
      
    /**
     * Function for get email analytics data
     * Parameters : $start_date, $end_date
     * Return : Array of email analytics data
     */
    public function email_analytics_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/email_analytics';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('email_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get data from analytics_model */
            $Return['Data'] = $this->analytics_model->getEmailAnalyticsData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get login analytics data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of login analytics data
     */
    public function login_line_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/login_analytics';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('login_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            if(isset($Data['Filter'])) $filter = $Data['Filter']; else $filter = '';
            
            /* Get data from analytics_model */
            $Return['Data'] = $this->analytics_model->getLoginLineChartData($start_date, $end_date, $filter);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get login analytics source of logins data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of source of logins data
     */
    public function login_sourcelogin_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/login_sourcelogin_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('login_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getLoginSourceLoginChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get login device data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of login device data
     */
    public function login_device_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/login_device_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('login_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get data from analytics_model */
            $Return['Data'] = $this->analytics_model->getLoginDeviceChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get username v/s email data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of Username/email data
     */
    public function login_username_email_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/login_username_email_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('login_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getLoginUsernameAndEmailChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }


    /**
     * Function for get FirstTime Login data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of FirstTime Login data
     */
    public function login_first_time_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/login_first_time_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('login_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getLoginFirstTimeChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get Popular Days Login
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of Popular Days Login data
     */
    public function login_popular_days_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/login_popular_days_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('login_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getLoginPopularDaysChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get Popular Time Login
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of Popular Time Login data
     */
    public function login_popular_time_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/login_popular_time_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('login_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getLoginPopularTimeChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get Login Failure Data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of Login Failure Data
     */
    public function login_failure_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/login_failure_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('login_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getLoginFailureChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get Login Geo Chart Data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of Login Geo Data
     */
    public function login_geo_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/login_geo_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('login_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getLoginGeoChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get signup analytics data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of signup analytics data
     */
    public function signup_line_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/signup_line_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('signup_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            if(isset($Data['Filter'])) $filter = $Data['Filter']; else $filter = '';
            
            /* Get data from analytics_model */
            $Return['Data'] = $this->analytics_model->getSignupLineChartData($start_date, $end_date, $filter);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for get signup analytics source of signups data
     * Parameters : $start_date, $end_date
     * Return : Array of source of logins data
     */
    public function signup_sourcesignup_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/signup_sourcesignup_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('signup_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getSignupSourcesignupChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get type signups data
     * Parameters : $start_date, $end_date
     * Return : Array of type of signup data
     */
    public function signup_type_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/signup_type_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('signup_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getSignupTypeChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get signup device data
     * Parameters : $start_date, $end_date
     * Return : Array of signup device data
     */
    public function signup_device_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/signup_device_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('signup_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get data from analytics_model */
            $Return['Data'] = $this->analytics_model->getSignupDeviceChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get visits v/s signups data
     * Parameters : $start_date, $end_date
     * Return : Array of visits v/s signups data
     */
    public function signup_visits_signup_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/signup_visits_signup_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('signup_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getSignupVisitsSignupChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get Signup Time data
     * Parameters : $start_date, $end_date
     * Return : Array of Signup Time data
     */
    public function signup_time_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/signup_time_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('signup_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getSignupTimeChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get Popular Days Signup
     * Parameters : $start_date, $end_date
     * Return : Array of Popular Days Signup data
     */
    public function signup_popular_days_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/signup_popular_days_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('signup_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getSignupPopularDaysChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get Popular Time Signup
     * Parameters : $start_date, $end_date
     * Return : Array of Popular Time Signup data
     */
    public function signup_popular_time_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/signup_popular_time_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('signup_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getSignupPopularTimeChartData($start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    /**
     * Function for get Signup Geo Chart Data
     * Parameters : $start_date, $end_date, $right_filter
     * Return : Array of Signup Geo Data
     */
    public function signup_geo_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/analytics/signup_geo_chart';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('signup_analytics'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            if(isset($Data['RightFilter'])) $right_filter = $Data['RightFilter']; else $right_filter = '';
            
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getSignupGeoChartData($start_date, $end_date, $right_filter);
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    
/******************** For Analytic Tools Section ***********************/    
    /**
     * Function for get analytics provider list
     * Parameters : ,
     * Return : Array of providers
     */
    public function get_analytics_providers_post() {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['Data']=array();
        
        $Data = $this->post_data;

        if(isset($Data) && $Data!=NULL )
        {
                        
            /* Get Data from analytics_model */
            $Return['Data'] = $this->analytics_model->getAnalyticsProviders();
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);        
    }
    
    /**
     * Function for get analytics provider details by id
     * Parameters : analytics_provider_id
     * Return : Array of providers
     */
    public function get_analytics_provider_detail_post() {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['Data']=array();
        
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('analytics_tool'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
                        
            if(isset($Data['analytics_provider_id'])) $analytics_provider_id = $Data['analytics_provider_id']; else $analytics_provider_id = '';
            
            /* Get Data from analytics_model */
            if($analytics_provider_id){
                $Return['Data'] = $this->analytics_model->getAnalyticsProvidersDetailById($analytics_provider_id);
            }
            
            if(empty($Return['Data']))
            {
                /* If result not found */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('no_result');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);        
    }
    
    /**
     * Function for save analytics tools data
     * Parameters : From services.js(Angular file)
     * 
     */
    public function save_analyticstools_info_post()
    {
        $Return['ResponseCode']='200';
        $Return['ServiceName']='admin_api/roles/save_analyticstools_info';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('analytics_tool_save_edit_event'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {   
            /* Validation - starts */            
            if ($this->form_validation->run('api/validate_analytic_tools_data') == FALSE) { // for web
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error; //Shows all error messages as a string
            } else {                
                if(isset($Data['AnalyticProviderID'])) $AnalyticProviderID = $Data['AnalyticProviderID']; else $AnalyticProviderID = '';
                if($AnalyticProviderID != ''){
                    $dataArr = array();
                    $dataArr['Value'] = $Data['AnalyticsCode'];
                    $result = $this->analytics_model->updateAnalyticToolsData($dataArr,$AnalyticProviderID);
                    if(!is_numeric($result)){
                        $Return['ResponseCode']='519';
                        $Return['Message'] = lang('try_again');
                    }
                }else{
                    $Return['ResponseCode']='519';
                    $Return['Message']= lang('input_invalid_format');
                }                
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

    public function message_post() {
        $return = $this->return;
        $data = $this->post_data;
        $this->load->model(array('messages/messages_model'));
        $result = array();
        $result['TotalTodayMessage'] = $this->messages_model->get_total_message('today');
        $result['TotalMessage'] = $this->messages_model->get_total_message();

        $result['TotalTodayUser'] = $this->messages_model->get_total_message_user('today');
        $result['TotalUser'] = $this->messages_model->get_total_message_user();

        $result['TotalUserSentMessage'] = $this->messages_model->get_total_unique_user_sent_message('today');
        $return['Data'] = $result;
        $this->response($return);
    }

    public function user_post() {
        $return = $this->return;
        $data = $this->post_data;
        $this->load->model(array('activity/activity_hide_model', 'admin/users_model'));
        $result = array();
        $result['TotalTodayUser'] = $this->activity_hide_model->get_total_user_used_hide_post('today');
        $result['TotalUser'] = $this->activity_hide_model->get_total_user_used_hide_post();
        
        $result['TotalCategoryUser'] = $this->users_model->get_total_user_who_mentioned_prefered_category();
        $result['PopularCategory'] = $this->users_model->get_popular_prefered_category();

        $return['Data'] = $result;
        $this->response($return);
    }

/******************** For Analytic Tools Section end ***********************/

}//End of file analytics.php