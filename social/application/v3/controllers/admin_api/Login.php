<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Example
 * This Class used for REST API
 * (All THE API CAN BE USED THROUGH POST METHODS)
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
 */
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
//require_once APPPATH . '/libraries/REST_Controller.php';

class Login extends REST_Controller {

    function __construct() {
        parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Loginsessionkey, Adminloginsessionkey, appversion");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }

        
        $this->load->model(array('admin/login_model'));

        $this->post_data = $this->post();

         /* Getting header information and set login session key in post data*/
         $headers = $this->input->request_headers();
         $admin_login_session_key = '';   
         $auth_key = ucfirst(strtolower('AdminLoginSessionKey'));
         $headers[$auth_key] = $this->input->get_request_header("AdminLoginSessionKey");
         //$auth_key = 'AdminLoginSessionKey';
         if(!empty($headers[$auth_key]) && $headers[$auth_key]!="[object HTMLInputElement]") {
             $admin_login_session_key = $headers[$auth_key];    
         } else if(!empty($this->post_data['AdminLoginSessionKey'])) {
             $admin_login_session_key = $this->post_data['AdminLoginSessionKey'];
         }
         $this->post_data['AdminLoginSessionKey'] = $admin_login_session_key;
    }

    function index_post() {
        /* Define variables - starts */
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/login';
        $Return['Data'] = array();
        /* Define variables - ends */
        
        $Data = $this->post_data;
        
        if (isset($Data)) {
            /* Check provided JSON format is valid */
            if (isset($Data['Username']))
                $Username = $Data['Username'];
            else
                $Username = $Data['Username'] = '';
            if (isset($Data['Password']))
                $Password = $Data['Password'];
            else
                $Password = $Data['Password'] = '';
            
            if (isset($Data['DeviceType']))
                $DeviceType = $Data['DeviceType'];
            else
                $DeviceType = "Native";
                        
            $DeviceTypeID = $this->login_model->GetDeviceTypeID($DeviceType);
            
            $Data['DeviceTypeID'] = $DeviceTypeID;
            if (isset($Data['SocialType']) && $Data['SocialType'] != '')
                $SocialType = $Data['SocialType'];
            else
                $SocialType = $Data['SocialType'] = 'Web';
            $SourceID = $this->login_model->GetSourceID($SocialType);
            $Data['SourceID'] = $SourceID;
            if (isset($Data['DeviceID']) && $Data['DeviceID'] != '')
                $DeviceID = $Data['DeviceID'];
            else
                $DeviceID = $Data['DeviceID'] = '1';
            if (isset($Data['IPAddress']))
                $IPAddress = $Data['IPAddress'];
            else
                $IPAddress = $Data['IPAddress'] = getRealIpAddr();
            if (isset($Data['Latitude']))
                $Latitude = $Data['Latitude'];
            else
                $Latitude = $Data['Latitude'] = '';
            if (isset($Data['Longitude']))
                $Longitude = $Data['Longitude'];
            else
                $Longitude = $Data['Longitude'] = '';
            if (isset($Data['UserSocialID']))
                $UserSocialID = $Data['UserSocialID'];
            else
                $UserSocialID = '';
            if (isset($Data['Email']))
                $Email = $Data['Email'];
            else
                $Email = '';

            if(isset($Data['Picture']))
                $Picture = $Data['Picture'];
            else
                $Picture = '';

            $Data['Picture'] = $Picture;

            $Token = '';
            if(isset($Data['Token'])){
                $Token = $Data['Token'];
            }
            
            $Data['Token'] = $Token;
            /* Gather Inputs - ends */
            
            /* Validation - starts */
            if ($this->form_validation->required($Username) == FALSE && $SourceID == 1) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('username_or_email_required');
            } elseif ($this->form_validation->required($Password) == FALSE && $SourceID == 1) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('password_login');
            } /* Validation - ends */ else {
               
                if ($SourceID != 1){//to check login
                    $Username = $UserSocialID;
                    if (isset($Data['Resolution']))
                            $Resolution = $Data['Resolution'];
                        else
                            $Resolution = 'Low';
                    $Data['ResolutionID'] = $this->login_model->GetResolutionID($Resolution); 
                    $Data['Username'] = $Username;
                    $Data['UserName'] = $Username;
                    $Data['EmailNotification'] = '0';
                    $Data['PhoneNumber'] = '';
                    $Data['Role'] = 2;
                }

                $Data['CityID'] = NULL;
                $locationData = array('statusCode' => '');
                if ($IPAddress != '')
                {                    
                    $this->load->helper('location');
                    $locationData = get_ip_location_details($IPAddress);
                }
                if ($locationData['statusCode'] == "OK") {
                    $Data['CityID'] = $locationData['CityID'];
                }

                if ($Data['Latitude'] == '' && $Data['Longitude'] == '') {//get these values from API here as per IPAddress
                    if ($locationData['statusCode'] == "OK") {
                        $Data['Latitude'] = $locationData['Latitude'];
                        $Data['Longitude'] = $locationData['Longitude'];
                    }
                }
                
                $UserData = $this->login_model->verifyLogin($Data);
                $Return['ResponseCode'] = $UserData['ResponseCode'];
                $Return['Message'] = $UserData['Message'];
                $Return['Data'] = $UserData['Data'];
                $Return['UserRights'] = $UserData['UserRights'];

                //Temporary code for testing
                //$this->session->set_userdata('AdminLoginSessionKey', $UserData['Data']['AdminLoginSessionKey']);
                //$this->session->set_userdata('UserID', $UserData['Data']['UserID']);
            }
        } else {
            $Return['ResponseCode'] = 500;
            $Return['Message'] = lang('input_invalid_format');
        }
        $Outputs = $Return;
        $this->response($Outputs); /* Final Output */
    }

    public function logout_post() {
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/login/logout';
        $Return['Data'] = array();

        $Data = $this->post_data;

        if (isset($Data)) {
            /* Check provided JSON format is valid */
            if (isset($Data['AdminLoginSessionKey']))
                $AdminLoginSessionKey = $Data['AdminLoginSessionKey'];
            else
                $AdminLoginSessionKey = '';
            /* Gather Inputs - ends */

            /* Validation - starts */
            if ($this->form_validation->required($AdminLoginSessionKey) == FALSE) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('not_authorized');
            }/* Validation - ends */ else {
                /* Delete AdminLoginSessionKey from DB - starts */
                $this->db->where(array('LoginSessionKey' => $AdminLoginSessionKey));
                $this->db->limit(1);
                $this->db->delete(ACTIVELOGINS);
                /* Delete AdminLoginSessionKey from DB - ends */
                
                
                $this->db->where('LoginSessionKey', $AdminLoginSessionKey);
                $query = $this->db->get(SESSIONLOGS);
                
                if ($query->num_rows()) {
                    $rw = $query->row();
                    $mins = ((strtotime(date('Y-m-d H:i:s')) - strtotime($rw->StartDate)) / 60);
                    
                    $this->db->where('LoginSessionKey', $AdminLoginSessionKey);
                    $this->db->update(SESSIONLOGS, array('EndTime' => date('Y-m-d H:i:s'), 'duration' => $mins));
                }  

                $is_admin = isset($data['isAdmin']) ? $data['isAdmin'] : 0 ;
                //code for unset session
                if($is_admin == 1) {
                    $this->session->unset_userdata('AdminLoginSessionKey');
                    $this->session->unset_userdata('AdminUserID');
                    $this->session->unset_userdata('UserRights');
                    $this->session->unset_userdata('startDate');
                    $this->session->unset_userdata('endDate');
                    $this->session->unset_userdata('dateFilterText');
                }
                $Return['Message'] = lang('success');
            }
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = 500;
            $Return['Message'] = lang('input_invalid_format');
        }

        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    public function getUsageData_post(){
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/login/getUsageData';
        $Return['Data'] = array();

        $Data = $this->post_data;
        $FromDate   = isset($Data['FromDate']) ? date('Y-m-d', strtotime($Data['FromDate'])) : '';
        $ToDate     = isset($Data['ToDate'])   ? date('Y-m-d', strtotime($Data['ToDate']))   : '';
        $Type       = isset($Data['Type'])   ? $Data['Type']   : '' ;
        $UserID     = isset($Data['UserID'])   ? $Data['UserID']   : '' ;

        $Return['Data'] = $this->login_model->getUsageData($FromDate,$ToDate,'Custom',$Type,$UserID);
        
        $this->response($Return);
    }

    public function updateadminusertime_post(){
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/login/updateloggedinusertime';
        $Return['Data'] = array();

        $Data = $this->post_data;

        if (isset($Data)) {
            /* Check provided JSON format is valid */
            if (isset($Data['AdminLoginSessionKey']))
                $AdminLoginSessionKey = $Data['AdminLoginSessionKey'];
            else
                $AdminLoginSessionKey = '';
            /* Gather Inputs - ends */

            /* Validation - starts */
            if ($this->form_validation->required($AdminLoginSessionKey) == FALSE) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('not_authorized');
            }else {
                
                $this->db->where('LoginSessionKey', $AdminLoginSessionKey);
                $query = $this->db->get(SESSIONLOGS);
                
                if ($query->num_rows()) {
                    $rw = $query->row();
                    $mins = ((strtotime(date('Y-m-d H:i:s')) - strtotime($rw->StartDate)) / 60);
                    
                    $this->db->where('LoginSessionKey', $AdminLoginSessionKey);
                    $this->db->update(SESSIONLOGS, array('EndTime' => date('Y-m-d H:i:s'), 'duration' => $mins));
                }
            }
        }else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = 500;
            $Return['Message'] = lang('input_invalid_format');
        }

        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function loginDashboard_post(){
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/login/loginDashboard';
        $Return['Data'] = array();
        
        $Data = $this->post_data;
        $FromDate   = isset($Data['FromDate']) ? date('Y-m-d', strtotime($Data['FromDate'])) : date('Y-m-d');
        $ToDate     = isset($Data['ToDate'])   ? date('Y-m-d', strtotime($Data['ToDate']))   : date('Y-m-d');
        $Type       = isset($Data['Type'])   ? $Data['Type']   : '' ;
        $TypeExtra       = isset($Data['TypeExtra'])   ? $Data['TypeExtra']   : 0 ;
        
        if($TypeExtra) {
            $Return['Data'] = $this->login_model->getLoginDashboardAnalyticsExtra($FromDate,$ToDate,'Custom',$Type);
        } else {
            $Return['Data'] = $this->login_model->getLoginDashboardAnalytics($FromDate,$ToDate,'Custom',$Type);
        }
        
        
        $this->response($Return);
    }

    public function cronEngagementScore_get(){
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/login/loginDashboard';
        $Return['Data'] = array();
        
        $this->login_model->cronEngagementScore();
        
        $this->response($Return);
    }

}
