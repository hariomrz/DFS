<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* All login related views rendering functions
* @package    Login
* @author     Ashwin kumar soni : 29-09-2014
* @version    1.0
*/
class Login extends MY_Controller
{
    public $page_name = "";
    public function __construct(){
        parent::__construct();
        $this->base_controller = get_class($this);
    }	
        
    /**
     * Function for load login panel in for admin section
     * Parameters : 
     * Return : Load login view files
     */
    public function index(){
        if($this->session->userdata('AdminLoginSessionKey') != ''){
            redirect('admin/users');
        }

        //$captcha = $this->get_captcha();
        
        $data = array();
        /* Get global settings */
        $data['global_settings'] = $this->config->item("global_settings");

        $data['content_view'] = 'admin/login/login';
        $this->page_name = "login";
        //$data['captcha_img'] = $captcha['image'];

        $this->load->view($this->layout, $data);
    }
    
    public function get_captcha(){
        $this->load->helper('captcha');        
        $vals = array(
            'img_path'      => ROOT_PATH."/".PATH_IMG_UPLOAD_FOLDER."captcha/",
            'img_url'       => base_url().PATH_IMG_UPLOAD_FOLDER."captcha/",
            'font_path'     => './path/to/fonts/texb.ttf',
            'img_width'     => '150',
            'img_height'    => 40,
            'expiration'    => 7200,
            'font_size'     => 20,
            'img_id'        => 'Imageid',
            'pool'          => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
        );
       // print_r($vals);
        $captcha = create_captcha($vals);
        $this->session->set_userdata('captcha_word', $captcha['word']);
        return $captcha;
    }
    
    public function refreshCaptcha(){
        $captcha = $this->get_captcha();
       // echo json_encode(array("image"=>$captcha['image']));
        $this->output->set_output(json_encode(array("image"=>$captcha['image'])));
    }

    /**
     * Function for login in admin section
     * Parameters : Post Data
     * Return : Load login view files
     */
    public function LogIn() {
        /* Gather Inputs - starts */
        if ($this->input->post()) {
            $JSONInput = $this->post();
        } else {
            $Handle = fopen('php://input', 'r');
            $JSONInput = fgets($Handle);
        }
        $url = base_url() . 'admin_api/login';
        $jsondata = $JSONInput;
        $data = json_decode($JSONInput);
        
        if(isset($data->CaptchaVal) && $this->session->userdata('LoginAttempts') >= 3 && ($data->CaptchaVal != $this->session->userdata('captcha_word'))){
            $Return['ResponseCode'] = 511;
            $Return['Message'] = "Invalid captcha value. Please try again.";
            $result= json_encode($Return);
            $this->output->set_output($result);
        }else{
        
            $result = ExecuteCurl($url, $jsondata);
            $output = json_decode($result);

            if (isset($output->ResponseCode) && $output->ResponseCode == 200) {

                /* Code for show admin panel only autorized admin otherwise show autorization error */
                if(!in_array(getRightsId('admin_site_view'), $output->UserRights)){
                    $newresult = json_decode($result);
                    unset($result);
                    $newresult->ResponseCode = '700';
                    $newresult->Message = 'You have no authorization for login';
                    $newresult->ServiceName = 'admin_api/login';
                    $newresult->Data = '';
                    $result= json_encode($newresult);
                }else{
                    if($output){
                        $this->SetSession($output);
                    }
                    $this->UnsetIPSession();
                }
            }

            /* Code for show captcha if login attempts fail 3 times */
            if(isset($output->ResponseCode) && $output->ResponseCode != 200)
            {
                    $this->session->set_userdata('IPAddress', $this->input->ip_address());
                    if($this->session->userdata('LoginAttempts')){
                           $SessionCount = $this->session->userdata('LoginAttempts');
                           $SessionCount = $SessionCount + 1;
                           $this->session->set_userdata('LoginAttempts', $SessionCount);
                    }else{
                          $this->session->set_userdata('LoginAttempts', 1);
                    }

                    if($this->session->userdata('LoginAttempts') >= 3){
                        $newresult = json_decode($result);
                        $newresult->ShowCaptcha = 'true';
                        $result= json_encode($newresult);
                    }
            }
            /* Code End : show captcha if login attempts fail 3 times */
            $this->output->set_output($result);
        }
    }
        
        
    /**
     * Function for SetSession after successfull login
     * Parameters : $output
     * Return : Setsession :AdminLoginSessionKey, UserID, UserGUID, FirstName, LastName etc.
     */
    public function SetSession($output) {
        $this->session->set_userdata('UserRights', $output->UserRights);
        $this->session->set_userdata('AdminLoginSessionKey', $output->Data->AdminLoginSessionKey);
        $this->session->set_userdata('AdminUserID', $output->Data->UserID);
        $this->session->set_userdata('AdminGUID', $output->Data->UserGUID);
        //$this->session->set_userdata('FirstName', $output->Data->FirstName);
        //$this->session->set_userdata('LastName', $output->Data->LastName);
        //$this->session->set_userdata('Email', $output->Data->Email);
        //$this->session->set_userdata('LoginKeyword', $output->Data->LoginKeyword);
        //$this->session->set_userdata('ProfilePicture', $output->Data->ProfilePicture);
        //$this->session->set_userdata('RoleID', $output->Data->RoleID);
        if ($output->Data->FirstName != '') {
            $DisplayName = $output->Data->FirstName;
            if ($output->Data->LastName != '') {
                $DisplayName.=" " . $output->Data->LastName;
            }
        } else {
            $DisplayName = $output->Data->Email;
        }
        //$this->session->set_userdata('DisplayName', $DisplayName);
    }
        
    /**
     * Function for UnsetIPSession IP session and LoginAttemptsSession
     * After successfull login
     * Parameters : $output
     * Return : Unset IPsession/LoginAttemepts Session
     */
    public function UnsetIPSession()
    {
        $this->session->unset_userdata('IPAddress');
        $this->session->unset_userdata('LoginAttempts');
    }
    
    public function jserror(){
        $data = array();
        /* Get global settings */
        $data['global_settings'] = $this->config->item("global_settings");
        $this->load->view('admin/error/jserror', $data);
        
    }

    function auth() {
        $auth_code = $this->uri->segment(4);
        if(empty($auth_code)) {
            redirect('admin');        
        } else {
            $this->load->library('mongo_db');
            $key_detail = array();
            try {
                $key_detail = $this->mongo_db->where(array( DFS_AUTH_KEY => $auth_code))->find_one('active_login');
                if(!empty($key_detail)){
                    $key_detail = $key_detail[0];
                }
            } catch (Exception $e) {
                log_message("error", "Unable to connect to MongoDB: {$e->getMessage()}");
            }
            
            if(!empty($key_detail)) {
                $this->load->model(array('admin/roles_model'));
                $UserRights = $this->roles_model->getUserRightsByUserId($key_detail['user_id']);
                if(in_array(getRightsId('admin_site_view'), $UserRights)){

                    $this->db->where(array('LoginSessionKey' => $auth_code));
                    $this->db->limit(1);
                    $this->db->delete(ACTIVELOGINS);

                    $activelogin['UserID'] = $key_detail['user_id'];
                    $activelogin['LoginSessionKey'] = $auth_code;
                    $activelogin['DeviceID'] = '';
                    $activelogin['IPAddress'] = getRealIpAddr();
                    $activelogin['ResolutionID'] = '1';
                    $activelogin['LoginSourceID'] = 1;
                    $activelogin['DeviceTypeID'] = 1;
                    $activelogin['Latitude'] = '';
                    $activelogin['Longitude'] = '';
                    $activelogin['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $this->db->insert(ACTIVELOGINS,$activelogin);
                    /* Save session to activelogins table - ends */

                    

                    $this->session->set_userdata('UserRights', $UserRights);
                    $this->session->set_userdata('AdminLoginSessionKey', $auth_code);
                    $this->session->set_userdata('AdminUserID', $key_detail['user_id']);
                    $this->session->set_userdata('AdminGUID', $key_detail['user_unique_id']);
                    redirect('admin/dashboard'); 
                    exit;
                }                    
            } 
            redirect('admin');
        }
    }

}//End of file login.php
