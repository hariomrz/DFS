<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All User related views rendering functions
 * @package    Users
 * @author     Ashwin kumar soni : 01-10-2014
 * @version    1.0
 */

class Users extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/users_model');
            
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect('admin');
        }
    }

    /**
     * Function for show Users Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index() {
        
        redirect('admin/dashboard');
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* Call helper function for intilize CKEditor for comunicate popup */
        //$path = base_url('assets/admin/js') . '/ckfinder';
        //editor($path);

        /* View File */
        $data['content_view'] = 'admin/users/users';
        $this->page_name = "users";
        
        $user_type = $this->uri->segment(4);
        switch($user_type)
        {
            case 'waiting':
                $user_status = '1';
            break;

            case 'register':
                $user_status = '2';
            break;

            case 'deleted':
                $user_status = '3';
            break;

            case 'blocked':
                $user_status = '4';
            break;

            case 'verify':
                $user_status = '5';
            break;
            default;
                $user_status = '2';
        }
                
        $data['UserStatus'] = $user_status;        
        $this->show_date_filter = false;
        $this->load->view($this->layout, $data);
    }
    

    /**
     * Function for set session for selected date 
     * from Top Right Corner filter in admin
     * Parameters : Post_data
     * Return : Status, StartDate, EndDate
     */
   public function set_session() { 
        $global_settings = $this->config->item("global_settings");

        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $this->post_data = $this->input->post();
            } else {
                $Handle = fopen('php://input', 'r');
                $JSONInput = fgets($Handle);
                $this->post_data = @json_decode($JSONInput, true);
            }  

            $dateFilter = $this->post_data['filter']['DateFilter'];
            $startDate = '';
            $endDate = '';
            $dateFilterText = '';

            /* Different Cases */
            switch ($dateFilter) {
                case 'today':
                    $startDate = date($global_settings['date_format'], strtotime('today'));
                    $endDate = date($global_settings['date_format'], strtotime('today'));
                    //$dateFilterText = 'Today';
                    $dateFilterText =$startDate.'-'.$endDate;
                    $startDate1 = date($global_settings['date_format'], strtotime('-1 days'));
                    $endDate1 = date($global_settings['date_format'], strtotime('-1 days'));

                    break;

                case 'yesterday':
                    $startDate = date($global_settings['date_format'], strtotime('-1 days'));
                    $endDate = date($global_settings['date_format'], strtotime('-1 days'));

                    $startDate1 = date($global_settings['date_format'], strtotime('-2 days'));
                    $endDate1 = date($global_settings['date_format'], strtotime('-2 days'));

                    //$dateFilterText = 'Yesterday';
                    $dateFilterText =$startDate.'-'.$endDate;
                    break;

                case 'tomorrow':
                    $startDate = date($global_settings['date_format'], strtotime('today'));
                    $endDate = date($global_settings['date_format'], strtotime('tomorrow'));
                    //$dateFilterText = 'Tomorrow';
                    $dateFilterText =$startDate.'-'.$endDate;
                    break;

                case 'thisweek':
                    $startDate = date($global_settings['date_format'], strtotime('previous monday'));
                    $endDate = date($global_settings['date_format'], strtotime('today'));

                    $startDate1 = date($global_settings['date_format'], strtotime('-2 weeks monday'));
                    $endDate1 = date($global_settings['date_format'], strtotime('previous monday'));

                    //$dateFilterText = 'This week';
                    $dateFilterText =$startDate.'-'.$endDate;
                    break;

                case 'thismonth':
                    $startDate = date($global_settings['date_format'], strtotime(date('m') . '/01/' . date('Y')));
                    $endDate = date($global_settings['date_format'], strtotime('today'));

                    $startDate1 = date($global_settings['date_format'], strtotime('-1 month'));
                    $endDate1 = date($global_settings['date_format'], strtotime(date('m') . '/01/' . date('Y')));

                    //$dateFilterText = 'This month';
                    $dateFilterText =$startDate.'-'.$endDate;
                    break;

                case 'threemonths':
                    $startDate = date($global_settings['date_format'], strtotime('-3 months'));
                    $endDate = date($global_settings['date_format'], strtotime('today'));


                    $startDate1 = date($global_settings['date_format'], strtotime('-6 months'));
                    $endDate1 = date($global_settings['date_format'], strtotime('-3 months'));

                    //$dateFilterText = 'Three months';
                    $dateFilterText =$startDate.'-'.$endDate;
                    break;

                case 'thisyear':
                    $startDate = date($global_settings['date_format'], strtotime('01/01/' . date('Y')));
                    $endDate = date($global_settings['date_format'], strtotime('today'));

                    $startDate1 = date($global_settings['date_format'], strtotime('-1 year'));
                    $endDate1 = date($global_settings['date_format'], strtotime('01/01/' . date('Y')));

                    //$dateFilterText = 'This year';
                    $dateFilterText =$startDate.'-'.$endDate;
                    break;

                case 'custom':
                    $startDate = $this->post_data['filter']['startDate'];
                    $endDate = $this->post_data['filter']['endDate'];

                    $startDate1 = $this->post_data['filter']['startDate'];
                    $endDate1 = $this->post_data['filter']['endDate'];

                    $dateFilterText = date($global_settings['date_format'],strtotime($startDate)).' - '.date($global_settings['date_format'],strtotime($endDate));
                    break;
                
                case 'all':
                    $startDate = date($global_settings['date_format'], strtotime('01/01/2018'));
                    $endDate = date($global_settings['date_format'], strtotime('today'));

                    $startDate1 = date($global_settings['date_format'], strtotime('01/01/2018'));
                    $endDate1 = date($global_settings['date_format'], strtotime('today'));

                    $dateFilterText = 'All';
                    break;

                default;
            } 

            if ($this->session->userdata('startDate')) {
                /* Remove already make session */
                $this->session->unset_userdata('startDate');
                $this->session->unset_userdata('endDate');

                $this->session->unset_userdata('startDateCompare');
                $this->session->unset_userdata('endDateCompare');

                $this->session->unset_userdata('dateFilterText');

                /* Now set session for particular Date filter */
                $this->session->set_userdata('startDate', $startDate);
                $this->session->set_userdata('endDate', $endDate); 

                $this->session->set_userdata('startDateCompare', $startDate1);
                $this->session->set_userdata('endDateCompare', $endDate1);
                
                $this->session->set_userdata('dateFilterText', $dateFilterText);
            } else {
                /* Now set new session for particular Date filter */
                $this->session->set_userdata('startDate', $startDate);
                $this->session->set_userdata('endDate', $endDate);

                $this->session->set_userdata('startDateCompare', $startDate1);
                $this->session->set_userdata('endDateCompare', $endDate1);

                $this->session->set_userdata('dateFilterText', $dateFilterText);
            }
           
            $retrun = array('status' => 1, 'startDate' => $startDate, 'endDate' => $endDate, 'startDateCompare' => $startDate1, 'endDateCompare' => $endDate1, 'dateFilterText' => $dateFilterText);
            $retrun = json_encode($retrun);
            
            echo $retrun; exit();
        }
    }

    /**
     * Function for show user_profile in admin
     * Parameters : user_guid : Get from URL query string
     * Return : Load User profile view files
     */
    public function user_profle() {
        if ($this->uri->segment(4) == '') {
            redirect();
        }
                
        if(!in_array(getRightsId('user_profile'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }

        $data = array();
        $data['user_guid'] = $this->uri->segment(4);
        /* Get user_id using UserGUID and then use it further */
        $user_data = $this->users_model->getSingleProfileInfo($data['user_guid']);

        if (empty($user_data))
            redirect();

        /* Assign values to $data */
        $data['user_id'] = $user_data['UserID'];
        $data['user_status'] = $user_data['StatusID']; //$this->input->get('UserStatus');
        $data['userroleid'] = $user_data['RoleID'];
        $data['first_name'] = $user_data['FirstName'];
        $data['global_settings'] = $this->config->item("global_settings");

        /* Call helper function for intilize CKEditor for comunicate popup */
        //$path = base_url('assets/admin/js') . '/ckfinder';
        //editor($path);

        /* View File */
        $data['content_view'] = 'admin/users/user_profile';
        $this->page_name = "user_profile";

        $this->load->view($this->layout, $data);
    }

    /**
     * Function for Load change password view 
     * Parameters : AdminLoginSessionKey :Get from session
     * Return : Load Admin change password view files
     */
    public function change_password() {
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/users/change_password';

        $this->load->view($this->layout, $data);
    }

    /**
     * Function for show most active users list
     * Parameters : 
     * Return : Load View files
     */
    public function most_active_users() {
        
        if(!in_array(getRightsId('most_active_users'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* Call helper function for intilize CKEditor for comunicate popup */
        //$path = base_url('assets/admin/js') . '/ckfinder';
        //editor($path);

        /* View File */
        $data['content_view'] = 'admin/users/most_active_users';
        $this->page_name = "most_active_user";

        $this->load->view($this->layout, $data);
    }
    
    public function setuserrights(){
        $UserID = $this->session->userdata('AdminUserID');
        $this->load->model(array('admin/roles_model'));
        $rightsArr = $this->roles_model->getUserRightsByUserId($UserID);
        $this->session->unset_userdata('UserRights');
        $this->session->set_userdata('UserRights', $rightsArr);
    }
    
    public function downloaduserlist(){        
        $file_url = base_url().'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/UsersList.xls";

        header('Content-Type: application/octet-stream');

        header("Content-Transfer-Encoding: Binary");

        header("Content-disposition: attachment; filename=\"".basename($file_url)."\"");

        readfile($file_url);
    }
    
    public function downloaduserlisttagtypes(){     
        
        $file_url = base_url().'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/UsersListtagtypes.xls";

        header('Content-Type: application/octet-stream');

        header("Content-Transfer-Encoding: Binary");

        header("Content-disposition: attachment; filename=\"".basename($file_url)."\"");

        readfile($file_url);
    }
    
    public function downloadactiveuserlist(){        
        $file_url = base_url().'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/MostActiveUsers.xls";

        header('Content-Type: application/octet-stream');

        header("Content-Transfer-Encoding: Binary");

        header("Content-disposition: attachment; filename=\"".basename($file_url)."\"");

        readfile($file_url);
    }
    
    public function downloadmediaanalytics(){        
        $file_url = base_url().'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/MediaAnalytics.xls";

        header('Content-Type: application/octet-stream');

        header("Content-Transfer-Encoding: Binary");

        header("Content-disposition: attachment; filename=\"".basename($file_url)."\"");

        readfile($file_url);
    }
    
    public function downloaderrorlogs(){        
        $file_url = base_url().'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/ErrorLogs.xls";

        header('Content-Type: application/octet-stream');

        header("Content-Transfer-Encoding: Binary");

        header("Content-disposition: attachment; filename=\"".basename($file_url)."\"");

        readfile($file_url);
    }
    
    public function downloadbetainviteuser(){        
        $filename = $this->uri->segment(4);
        $file_url = base_url().'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/".$filename.".xls";

        header('Content-Type: application/octet-stream');

        header("Content-Transfer-Encoding: Binary");

        header("Content-disposition: attachment; filename=\"".basename($file_url)."\"");

        readfile($file_url);
    }
    
    public function onboarding()
    {
        $this->show_date_filter = false;
        $this->page_name = "users";
        $this->data['title'] = 'Onboarding';
        $this->data['content_view'] = 'admin/users/onboarding_list';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }
    
    public function dummy_users()
    {
        if(!in_array(getRightsId('dummy_user_manager'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        $this->show_date_filter = false;
        $this->page_name = "users";
        $this->data['title'] = 'Dummy Users';
        $this->data['content_view'] = 'admin/users/dummy_users';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

    public function activity()
    {
        $this->show_date_filter = false;
        $this->page_name = "users";
        $this->data['title'] = 'Users Activity';
        $this->data['content_view'] = 'admin/users/view_posts';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

    public function print_persona($user_id=1)
    {
        $this->show_date_filter = false;
        $this->page_name = "dashboard";
        $this->data['title'] = 'Users Persona';
        $details = get_detail_by_id($user_id,3,'FirstName,LastName,UserGUID',2);
        $this->data['UserID'] = $user_id;
        $this->data['UserGUID'] = $details['UserGUID'];
        $this->data['Name'] = $details['FirstName'].' '.$details['LastName'];
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view("admin/users/persona/print_persona", $this->data);
    }
}

//End of file users.php
