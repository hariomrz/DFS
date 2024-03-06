<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* All Analytics related views rendering functions
* @package    Analytics
* @author     Ashwin kumar soni : 01-10-2014
* @version    1.0
*/
class Analytics extends MY_Controller
{
    public $page_name = "";
    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/analytics_model');
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
    }
	
    /**
     * Function for show Login analytics page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index(){
    
    }

    /**
     * Function for show Login analytics page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function login_analytics(){
        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('login_analytics'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/analytics/login_analytics';
        $this->page_name = "login_analytics";
        
        $this->load->view($this->layout, $data);
    }

    /**
     * Function for show Signup analytics page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function signup_analytics(){
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('signup_analytics'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/analytics/signup_analytics';
        $this->page_name = "signup_analytics";
        
        $this->load->view($this->layout, $data);
    }

    /**
     * Function for show Email analytics page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function email_analytics_old(){
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('email_analytics'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/analytics/email_analytics_old';
        $this->page_name = "email_analytics_old";
        
        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for show Email analytics page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function email_analytics(){
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('email_analytics'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/analytics/email_analytics';
        $this->page_name = "email_analytics";
        
        $this->load->view($this->layout, $data);
    }

     /**
     * Function for show Email analytics page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function analytics_query(){
        $start_date="06-Jan-2014";
        $end_date ="06-Jan-2015";

        /* Query for select data from the AnalyticLogins */
        $this->db->select('LoginSourceID', FALSE);
        $this->db->select('DeviceTypeID', FALSE);
        $this->db->select('WeekdayID', FALSE);
        $this->db->select('TimeSlotID', FALSE);
        
        $this->db->select('IsEmail', FALSE);

        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        $this->db->where('DATE(CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'"', NULL, FALSE);
        $this->db->where('IsLoginSuccessfull',1);

        $this->db->group_by(array("LoginSourceId", "DeviceTypeID", "weekdayID", "timeslotid", "IsEmail")); 

        $this->db->from(" AnalyticLogins ");
        echo $this->db->count_all_results();
        exit;
        $query = $this->db->get();
        $query = $query->count_all_results();
        echo $this->db->last_query();
       /*SELECT CAST(CONVERT(VARCHAR,CreatedDate,101) AS DATETIME),  
            LoginSourceID,  
            DeviceTypeID,  
            WeekdayID,  
            TimeSlotID,  
            COUNT(*) ,          
            EmailCount=ISNULL(CASE isemail WHEN 1 THEN COUNT(IsEmail) END,0) ,
            UsernameCount=ISNULL(CASE isemail WHEN 0 THEN COUNT(IsEmail) END ,0)
            FROM AnalyticLogins NoLock  
            WHERE   
            IsLoginSuccessfull = 1 

            AND CAST(CONVERT(VARCHAR,CreatedDate,101) AS DATETIME) = CAST(CONVERT(VARCHAR,@Date,101) AS DATETIME) 
            GROUP BY CAST(CONVERT(VARCHAR,CreatedDate,101) AS DATETIME),LoginSourceId,DeviceTypeID,weekdayID,timeslotid ,IsEmail        
        END  */    
    }
    
    /**
     * Function for show email analytic communitcation
     * Parameters : 
     * Return : Load View files
     */
    public function emails(){
        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('email_analytics_emails'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/analytics/emails';
        $this->page_name = "emails";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for update analytic tool details
     * Parameters : 
     * Return : Load View files
     */
    public function analytictool(){
        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('analytics_tool'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/analytics/analytictool';
        $this->page_name = "analytictools";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for show Google analytics device information page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function google_analytics_deviceinfo(){
                
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/analytics/google_analytics_deviceinfo';
        $this->page_name = "google_analytics_device";
        
        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for show Google analytics page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function google_analytics(){
                
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/analytics/google_analytics';
        $this->page_name = "google_analytics";
        
        $this->load->view($this->layout, $data);
    }

    /**
     * Function for show login dashboard
     * Parameters : 
     * Return : Load View files
     */
    public function dashboard(){
        redirect('admin/analytics/dashboard2');
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('analytics_tool'), getUserRightsData($this->DeviceType))){
            redirect('admin/users/dummy_users');
        }

        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/analytics/login_dashboard';
        $this->page_name = "login_dashboard";
        
        $this->load->view($this->layout, $data);
    }

    /**
     * Function for show login dashboard
     * Parameters : 
     * Return : Load View files
     */
    public function dashboard2(){ 
        $this->show_date_filter = true;
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('analytics_tool'), getUserRightsData($this->DeviceType))){
            redirect('admin/users/dummy_users');
        }

        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/analytics/google_analytics_dash';
        $this->page_name = "google_analytics_dash";
        
        $this->load->view($this->layout, $data);
    }


}//End of file analytics.php
