<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* All analytics related process like : email_analytics, login_analytics, signup_analytics
* @package    Analytics
* @author     ashwin kumar soni(05-01-2015)
* @version    1.0
*/

//require APPPATH.'/libraries/REST_Controller.php';

class Emailanalytics extends Admin_API_Controller
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
        $Return['ServiceName']='admin_api/emailanalytics/email_analytics';
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
            if(isset($Data['StartDate'])) $StartDate = $Data['StartDate']; else $StartDate = '';
            if(isset($Data['EndDate'])) $EndDate = $Data['EndDate']; else $EndDate = '';
            if(isset($Data['AnalyticType'])) $AnalyticType = $Data['AnalyticType']; else $AnalyticType = 'mandrill';
            
            /* Get data from analytics_model */
            if($AnalyticType == "smtp"){
                $Return['Data'] = $this->analytics_model->getSmtpEmailAnalyticsChartData($StartDate, $EndDate);
            }else{
                $Return['Data'] = $this->analytics_model->getMandrillEmailAnalyticsChartData($StartDate, $EndDate);
            }
            
            if(empty($Return['Data'])){
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
     * Function for get email analytics data
     * Parameters : $start_date, $end_date, $day_filter
     * Return : Array of email analytics line chart data
     */
    public function line_chart_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/emailanalytics/line_chart';
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
            if(isset($Data['StartDate'])) $StartDate = $Data['StartDate']; else $StartDate = '';
            if(isset($Data['EndDate'])) $EndDate = $Data['EndDate']; else $EndDate = '';
            if(isset($Data['Filter'])) $Filter = $Data['Filter']; else $Filter = 3;
            if(isset($Data['EmailTypes'])) $EmailTypes = $Data['EmailTypes']; else $EmailTypes = 2;
            if(isset($Data['AnalyticType'])) $AnalyticType = $Data['AnalyticType']; else $AnalyticType = 'mandrill';
            
            /* Get data from analytics_model */
            if($AnalyticType == "smtp"){
                $Return['Data'] = $this->analytics_model->getSmtpEmailAnalyticsLineChartData($StartDate, $EndDate, $EmailTypes, $Filter);
            }else{
                $Return['Data'] = $this->analytics_model->getMandrillEmailAnalyticsLineChartData($StartDate, $EndDate, $EmailTypes, $Filter);
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
     * Function for show Email analytics statistcs listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function statistcs_list_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/emailanalytics/statistcs_list';
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
            
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';
            
            if(isset($Data['EmailTypes'])) $email_types = $Data['EmailTypes']; else $email_types = 2;
            if(isset($Data['AnalyticType'])) $AnalyticType = $Data['AnalyticType']; else $AnalyticType = 'mandrill';

            if($AnalyticType == "smtp"){
                $emailResults = $this->analytics_model->getSmtpEmailAnalyticsStatistcs($start_date, $end_date, $start_offset, $end_offset, $sort_by, $order_by, $email_types);
            }else{
                $emailResults = $this->analytics_model->getMandrillEmailAnalyticsStatistcs($start_date, $end_date, $start_offset, $end_offset, $sort_by, $order_by, $email_types);
            }
            
            $Return['Data']['total_records'] = $emailResults['total_records'];
            $Return['Data']['results'] = $emailResults['results'];
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for show Sent Emails listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function sent_emails_statistcs_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/emailanalytics/sent_emails_statistcs';
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
            if(isset($Data['Begin'])) $StartOffset = $Data['Begin']; else $StartOffset = 0;
            if(isset($Data['End']))  $EndOffset = $Data['End']; else $EndOffset = 10;

            if(isset($Data['SortBy']))  $SortBy = $Data['SortBy']; else $SortBy = '';
            if(isset($Data['OrderBy']))  $OrderBy = $Data['OrderBy']; else $OrderBy = '';
            
            if(isset($Data['EmailTypes'])) $EmailType = $Data['EmailTypes']; else $EmailType = 2;
            if(isset($Data['AnalyticType'])) $AnalyticType = $Data['AnalyticType']; else $AnalyticType = 'mandrill';
            if(isset($Data['SentDate'])) $SentDate = $Data['SentDate']; else $SentDate = '';

            if($AnalyticType == "smtp"){
                $emailResults = $this->analytics_model->getSmtpSentEmailStatistcs($StartOffset, $EndOffset, $SortBy, $OrderBy, $EmailType, $SentDate);
            }else{
                $emailResults = $this->analytics_model->getMandrillSentEmailStatistcs($StartOffset, $EndOffset, $SortBy, $OrderBy, $EmailType, $SentDate);
            }
            
            $Return['Data']['total_records'] = $emailResults['total_records'];
            $Return['Data']['results'] = $emailResults['results'];
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for show Emails click URL listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function email_click_url_list_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/emailanalytics/email_click_url_list';
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
            
            if(isset($Data['EmailTypes'])) $email_types = $Data['EmailTypes']; else $email_types = 2;
            if(isset($Data['SentDate'])) $sent_date = $Data['SentDate']; else $sent_date = '';

            $emailResults = $this->analytics_model->getEmailClickUrlsList($email_types, $sent_date);
            
            $Return['Data']['total_records'] = $emailResults['total_records'];
            $Return['Data']['results'] = $emailResults['results'];
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }


}//End of file analytics.php