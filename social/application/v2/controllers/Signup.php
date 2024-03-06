<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    signup
 * @version    1.0
 */

class Signup extends Common_Controller {

    public $page_name = 'signup';
    public $AccessKey = '';
    public $dashboard = '';

    public function __construct() {
        parent::__construct();
        if ($this->session->userdata('LoginSessionKey') != '') {
            $this->AccessKey = $this->session->userdata('LoginSessionKey');
        }
    }

    /**
     * [index use to redirect at Sign up page]
     */
    public function index() {
       
        if ($this->AccessKey == '') {
            $this->SignIn();
        } else {
//            if(!$this->login_model->is_user_details_exists($this->session->userdata('UserID'))){
//                redirect(site_url('myaccount/profilesetup'));
//            }
            $loginCount = $this->login_model->get_login_count($this->session->userdata('UserID'));
            if ($loginCount > 1) {
                redirect(site_url('dashboard'));   
            }
            $this->session->set_flashdata('msg', 'You are successfully registered, please set your profile.');
            redirect(site_url('myaccount'));
        }
    }

    /**
     * [SignIn Check accesskey if not exist then redirect at wall page otherwise load sign in form]
     */
    public function SignIn() {   
        //redirect(site_url());
        if ($this->AccessKey != '') {
            redirect(site_url());
        }        
        $this->data['title'] = 'Login';
        $this->page_name = 'signin';
        $this->data['content_view'] = 'signin';
        $this->load->view($this->layout, $this->data);        
    }

    public function thanks($UserGUID=''){
        //redirect(site_url()); exit(1);
        if(!empty($UserGUID))
        {
            $UserDetail = get_detail_by_guid($UserGUID,3,'*',2);
            if(!empty($UserDetail))
            {
                $this->data['title']        = 'Thanks for SignUp';
                $this->data['UserDetail']     = $UserDetail;
                $this->page_name            = 'signin';
                $this->sub_page             = 'thanks';
                $this->data['content_view'] = 'thanks';
                $this->load->view($this->layout, $this->data);    
            }
            else
            {
                $this->session->set_flashdata('msg', 'Invalid URL.');
                redirect(site_url());
            }   
        }
        else
        {
            $this->session->set_flashdata('msg', 'Invalid URL.');
            redirect(site_url());
        }
    }

    /**
     * [LogIn used to handle sign in form POST request]
     */
    public function LogIn() {
        /* Gather Inputs - starts */
        //echo $this->session->userdata('redirect_back_url');die;
        if ($this->input->post()) { 
            $JSONInput = json_encode($this->input->post());
        } else {
            $JSONInput = $this->security->xss_clean($this->input->raw_input_stream);
        }

        $url = base_url() . 'api/login';
        $jsondata = $JSONInput;
        $data = json_decode($JSONInput);
        $data->IPAddress = getRealIpAddr();

        $data->Token = '';
        $data->profileUrl = '';
        if($this->session->userdata('Token')){
          $data->Token = $this->session->userdata('Token');
        }
        if($this->session->userdata('profileUrl')){
          $data->profileUrl = $this->session->userdata('profileUrl');
        }
        $jsondata = json_encode($data);

        $result = ExecuteCurl($url, $jsondata);
        $output = json_decode($result); 
        
        if ($output->ResponseCode == 200) {   

            $output->Data->redirect_back_url = base_url();            
            // user is authenticated, lets see if there is a redirect:
            if( $this->session->userdata('redirect_back') ) {
                $output->Data->redirect_back_url = $this->session->userdata('redirect_back');  // grab value and put into a temp variable so we unset the session value
                $this->session->unset_userdata('redirect_back');            
            }

            $this->load->model('users/friend_model');
           /* if($this->friend_model->check_low_connection(get_detail_by_guid($output->Data->UserGUID,3)))
            {
                $output->Data->redirect_back_url = site_url('network/grow_your_network');
            }*/

            $this->SetSession($output);
            //delete_cookie('dische');
            //Comment because remember me parameter is also removed
            /*if (($data->RememberMe == true || $data->RememberMe == 1 || $data->RememberMe == '1')) {
                $this->login_model->set_remember_me($output->Data->UserID);
            }*/
            //$activity_rank_url = site_url('home').'/run_queue/'.$output->Data->UserGUID;
            //ExecuteCurl($activity_rank_url);
        }        
        //echo $output->Data->redirect_back_url;
        
        //echo json_encode($output); 
        $this->output->set_output(json_encode($output));
    }

    /**
     * [SetSession used to set user session]
     * @param [type] $output [user details]
     */
    public function SetSession($output) {

        $this->session->set_userdata('LoginSessionKey', $output->Data->LoginSessionKey);
        $this->session->set_userdata('UserID', $output->Data->UserID);
        $this->session->set_userdata('UserGUID', $output->Data->UserGUID);
        $this->session->set_userdata('FirstName', $output->Data->FirstName);
        $this->session->set_userdata('LastName', $output->Data->LastName);
        $this->session->set_userdata('Email', $output->Data->Email);
        //$this->session->set_userdata('LoginKeyword', $output->Data->LoginKeyword);
        $this->session->set_userdata('ProfilePicture', $output->Data->ProfilePicture);
        $this->session->set_userdata('TimeZoneOffset',$output->Data->TimeZoneOffset);
        $this->session->set_userdata('UserStatusID',$output->Data->StatusID);
        $this->session->set_userdata('language',$output->Data->Language); 
        
        $profile_url = isset($output->Data->ProfileURL) ? $output->Data->ProfileURL : '';        
        $this->session->set_userdata('ProfileURL', $profile_url);
        
        //$this->session->set_userdata('RoleGuID', $output->Data->RoleGuID);
        if ($output->Data->FirstName != '') {
            $DisplayName = $output->Data->FirstName;
            if ($output->Data->LastName != '') {
                $DisplayName.=" " . $output->Data->LastName;
            }
        } else {
            $DisplayName = $output->Data->Email;
        }
        $this->session->set_userdata('DisplayName', $DisplayName);

        $this->load->model('users/user_model');
        $is_super_admin=$this->user_model->is_super_admin($output->Data->UserID);
        if($is_super_admin)
        {
            $this->session->set_userdata('isSuperAdmin',TRUE); 
            $this->session->set_userdata('superAdminID', $output->Data->UserID); 
        }
    }

    /**
     * [SignUp used to load sign up form]
     */
    public function SignUp() {
        if ($this->AccessKey != '') {
            redirect(site_url());
        }
        $this->data['show_form'] = false;
        if($this->input->get('type')){
          $this->data['show_form'] = true;
        }

        $this->load->model('users/friend_model');
        $Token = $this->input->get('Token');        
        $ProfileUrl = $this->input->get('profileUrl');
        $RequestIds = $this->input->get('request_ids');
        $Email = '';
        if(!empty($Token)){
          $Email = $this->login_model->get_email_from_token($Token);
        }
        if(!empty($ProfileUrl)){
            $this->session->set_userdata('profileUrl',$ProfileUrl);
        }
        if(!empty($RequestIds)){
          $Token = $RequestIds;
        }
        if(!empty($Token)){
          $this->session->set_userdata('Token',$Token);
          if($this->session->userdata('UserID')){
            $this->friend_model->addFriendByToken($Token);
            redirect('/userprofile');
          }
        }

        if($this->session->userdata('Token')){
          $Token = $this->session->userdata('Token');
        }
        if($this->session->userdata('profileUrl')){
          $ProfileUrl = $this->session->userdata('profileUrl');
        }
        if ($this->AccessKey != '') {
          redirect('/wall');
        }
        
        $this->data['Email'] = $Email;
        $this->data['token'] = $Token;
        $this->data['profileUrl'] = $ProfileUrl;
        $this->data['title'] = 'Signup';
        $this->page_name = 'signup';
        $this->data['content_view'] = 'signup';
        $this->load->view($this->layout, $this->data);
    }

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function Step1() {
        if ($this->AccessKey != '') {
            redirect('/wall');
        }
        if (isset($_GET['type']) && $_GET['type'] != '1') {
            $this->CheckUser($_GET['id'], $_GET['type']);
        }

        $this->data['title'] = 'Signup';
        $this->page_name = 'signupstep1';
        $this->dashboard = 'profile';
        $this->data['content_view'] = 'step1';
        $this->load->view($this->layout, $this->data);
    }

    function CheckUser($id, $type) {
        if ($type == 1) {
            $api = 'Web';
            $signupvia = 'Email';
        } elseif ($type == 2) {
            $api = 'Facebook API';
            $signupvia = 'Facebook';
        } elseif ($type == 4) {
            $api = 'Google API';
            $signupvia = 'Gmail';
        } elseif ($type == 7) {
            $api = 'LinkedIN API';
            $signupvia = 'LinkedIn';
        } elseif ($type == 3) {
            $api = 'Twitter API';
            $signupvia = 'Twitter';
        }
        $requeset = array("SocialType" => $api, "UserSocialID" => $id, "RememberMe" => '');
        $JSONInput = json_encode($requeset);
        $url = base_url() . 'api_login/login.json';
        $jsondata = $JSONInput;
        $data = json_decode($JSONInput);
        $result = ExecuteCurl($url, $jsondata);
        $output = json_decode($result);

        if ($output->ResponseCode == 200) {
            $this->SetSession($output);
            if (($data->RememberMe == true || $data->RememberMe == 1 || $data->RememberMe == '1')) {
                $this->login_model->set_remember_me($output->Data->UserID);
            }
            redirect('/wall');
        }
    }

    
    /**
     * [setPassword Used to check the forgot password link and show set padssword form or error message]
     * @param string $UserGuID [description]
     */
    public function setPassword($UserGuID=''){
        
        if($this->session->userdata('UserID')){
            redirect(site_url('dashboard'));
        }

        $requeset = array("OTP" => $UserGuID);
        $JSONInput = json_encode($requeset);
        $url = base_url() . 'api/recovery_password/validate_forgot_password_token';
        $result = ExecuteCurl($url, $JSONInput);
        $output = json_decode($result);

        $this->data['UserGuID'] = $UserGuID;
        $this->data['title'] = 'Set Password';
        $this->page_name = 'forgotpassword';
        $this->dashboard = '';
        if($output->ResponseCode != 200){
          $this->data['content_view'] = 'linkexpired';
        } else { 
          $this->data['content_view'] = 'setpassword';
        }
        $this->load->view($this->layout, $this->data);
    }

    /**
     * [forgotpassword Load the forgot password form]
     */
    public function forgotpassword() {
        if ($this->session->userdata('LoginSessionKey') == '') {
            $this->data['title'] = 'Forgot Password';
            $this->page_name = 'forgotpassword';
            $this->dashboard = '';
            $this->data['content_view'] = 'forgotpassword';
            $this->load->view($this->layout, $this->data);
        } else {
            redirect();
        }
    }
    /**
     * [SignOut Used to expire user session]
     */
    function SignOut() {
        $this->AccessKey = '';
        $url = base_url() . 'api/signout';
        $JSONInput = json_encode(array("LoginSessionKey" => $this->session->userdata('LoginSessionKey')));
        $result = ExecuteCurl($url, $JSONInput);
        $output = json_decode($result);
        $this->session->userdata('LoginSessionKey', '');
        $this->session->sess_destroy();
        
        $cookie = array(
            'name' => 'remember_me',
            'value' => '',
            'expire' => '-99999999'  // 90 days expiration time
        );
        $this->input->set_cookie($cookie);
        //if($output->ResponseCode==200){
         redirect(base_url());
        //}
    }

    /*
    | @Function - Load template for inactive user
    | @Param    - UserGUID
    */
    function AccountInactive($UserGUID)
    {
        if ($this->session->userdata('LoginSessionKey') == '') {
            $this->data['title'] = 'Forgot Password';
            $this->page_name = 'forgotpassword';
            $this->dashboard = '';
            $this->data['UserGUID']     =  $UserGUID;
            $this->data['content_view'] = 'AccountInactive';
            $this->load->view($this->layout, $this->data);
        } else {
            redirect();
        }
    }

    function switchProfile()
    {
        if ($this->input->post()) { 
            $jsondata = json_encode($this->input->post());
        } else {
            $jsondata = $this->security->xss_clean($this->input->raw_input_stream);
        }
        $user_data = json_decode($jsondata);
        //print_r($user_data);       
        $user_id = $user_data->UserID; 

       /* if(!empty($user_id) && $this->session->userdata('isSuperAdmin'))
        {*/
            /* Delete LoginSessionKey from DB - starts */
            $this->db->where(array('LoginSessionKey' => $user_data->LoginSessionKey));
            $this->db->limit(1);
            $this->db->delete(ACTIVELOGINS);
            /* Delete LoginSessionKey from DB - ends */
            
            $data['UserSocialID']   = '';
            $data['DeviceID']       = DEFAULT_DEVICE_ID;
            $data['Latitude']       = '';
            $data['Longitude']      = '';
            $data['IPAddress']      = $_SERVER['REMOTE_ADDR'];                
            $data['Email']          = '';
            $data['Username']       = '';
            $data['Password']       = '';
            $data['Resolution']     = DEFAULT_RESOLUTION;
            $data['Picture']        = '';
            $data['Token']          = '';
            $data['DeviceToken']    = '';
            $data['profileUrl']     = '';
            $data['SourceID']       = 1;
            $data['UserID']         = $user_id;
            $data['AutoLogin']      = true;            
            $data['DeviceTypeID']   = $this->DeviceTypeID;            
            $UserData               = $this->login_model->verify_login($data);

            if($UserData['ResponseCode'] == 200) 
            {            
                $output = json_decode(json_encode($UserData));
                $this->SetSession($output);
            }
        /*}*/

    }
}
