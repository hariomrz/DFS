<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 * This Class used for Login API
 * (All THE API CAN BE USED THROUGH POST METHODS)
 * @package   CodeIgniter
 * @subpackage  Rest Server
 * @category  Controller
 * @author    Vinfotech Team
 */

class Login extends Common_API_Controller 
{

    function __construct() {
        parent::__construct();
    }

    /**
     * Function Name: index
     * @param Username
     * @param Password
     * @param DeviceType
     * @param DeviceTypeID
     * @param IPAddress
     * @param Latitude
     * @param Longitude
     * @param UserSocialID
     * @param Email
     * @param Picture
     * @param Token
     * Description: Verify login and activate session
     */
    function index_post() {
        /* Define variables - starts */
        $return = $this->return;       
        /* Define variables - ends */
        
        $data = $this->post_data;
        if (isset($data)) 
        {
          /* Check Social Type and get the Source ID  */
          $social_type = (isset($data['SocialType']) && !empty($data['SocialType'])) ? $data['SocialType'] : DEFAULT_SOCIAL_TYPE;
          $data['DeviceType']     = isset($data['DeviceType']) ? $data['DeviceType'] : DEFAULT_DEVICE_TYPE;
          $source_id = $this->login_model->get_source_id($social_type);

          $validation_rule         =    $this->form_validation->_config_rules['api/login'];
          $is_device = isset($data['IsDevice']) ? $data['IsDevice'] : "0";
          if ($data['DeviceType'] != "Native" && $is_device == "1") 
          {
            $validation_rule[] = array(
                              'field' => 'DeviceID',
                              'label' => 'device ID',
                              'rules' => 'trim|required'
                              );
          }
           /* Validation - starts */
          $this->form_validation->set_rules($validation_rule); 
          if ($this->form_validation->run() == FALSE && $source_id == 1) 
          {
              $error = $this->form_validation->rest_first_error_string();         
              $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
              $return['Message'] = $error;
          } 
          else /* Validation - ends */
          {                 
              /* Define variables and Gather Inputs*/
              $data['UserSocialID']   = isset($data['UserSocialID']) ? $data['UserSocialID'] : '';
              $data['DeviceID']       = isset($data['DeviceID']) ? $data['DeviceID'] : DEFAULT_DEVICE_ID;
              $data['DeviceToken']    = isset($data['DeviceToken']) ? $data['DeviceToken'] : $data['DeviceID'];
              $data['Latitude']       = isset($data['Latitude']) ? $data['Latitude'] : '';
              $data['Longitude']      = isset($data['Longitude']) ? $data['Longitude'] : '';
              $data['IPAddress']      = isset($data['IPAddress']) ? $data['IPAddress'] : getRealIpAddr();                
              $data['Email']          = isset($data['Email']) ? $data['Email'] : '';
              $data['Resolution']     = isset($data['Resolution']) ? $data['Resolution'] : DEFAULT_RESOLUTION;
              $data['Picture']        = isset($data['Picture']) ? $data['Picture'] : '';
              $data['Token']          = isset($data['Token']) ? $data['Token'] : '';
              $data['profileUrl']     = isset($data['profileUrl']) ? $data['profileUrl'] : '';
              $data['PhoneNumber']    = isset($data['Mobile']) ? $data['Mobile'] : '';
              $data['IsApp']          = $is_device;
              $data['SourceID']       = $source_id;
              if ($source_id != 1) { //to check login
                  $data['Username']   = $data['UserSocialID'];
              }
              $data['DeviceTypeID'] 	= $this->login_model->get_device_type_id($data['DeviceType']);

              $user_data              = $this->login_model->verify_login($data);
              if($is_device && $user_data['ResponseCode']==200) {
                $this->load->model('users/user_model');
                $is_completed           = $this->user_model->check_interest_category($user_data['Data']['UserID'], $is_device);
                $user_data['Data']['IsOnboardingCompleted'] = $is_completed;
              }
              $return['ResponseCode'] = $user_data['ResponseCode'];
              $return['Message']      = $user_data['Message'];
              $return['Data']         = $user_data['Data'];
          }
        } 
        else 
        {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: logout     
     * Description: Destroy user session
     */
    public function logout_post() {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Message'] = lang('success');
        $return['ServiceName'] = 'api/login/logout';
        $return['Data'] = array();
        $data = $this->post_data;

        /* Check provided JSON format is valid */
        if ($data != NULL && isset($data)) {
            $login_session_key = '';
            if (isset($data[AUTH_KEY])) {
                $login_session_key = $data[AUTH_KEY];
            }

            if ($this->form_validation->required($login_session_key) == FALSE) {
                $return['ResponseCode'] = 501;
                $return['Message'] = lang('not_authorized');
            } else {
                /* Delete LoginSessionKey from DB - starts */
                $this->db->where(array('LoginSessionKey' => $login_session_key));
                $this->db->limit(1);
                $this->db->delete(ACTIVELOGINS);
                /* Delete LoginSessionKey from DB - ends */
                $this->db->select('StartDate, UserID');
                $this->db->where('LoginSessionKey', $login_session_key);
                $query = $this->db->get(SESSIONLOGS);
                $user_id = 0;
                if ($query->num_rows()) {
                    $rw = $query->row();
                    $user_id = $rw->UserID;
                    $mins = ((strtotime(get_current_date('%Y-%m-%d %H:%i:%s')) - 
                    strtotime($rw->StartDate)) / 60);

                    $this->db->where('LoginSessionKey', $login_session_key);
                    $this->db->update(SESSIONLOGS, array('EndTime' => 
                    get_current_date('%Y-%m-%d %H:%i:%s'), 'duration' => $mins));
                }

                //Temporary code for testing
                $this->session->unset_userdata('LoginSessionKey');
                $this->session->unset_userdata('UserID');

                $this->session->sess_destroy();
                        
                $this->login_model->delete_mongo_db_record('active_user_login', array(AUTH_KEY => $login_session_key));                                
            }
        } else  {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [update_session_log_post used to update user session log]
     */
    function update_session_log_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data[AUTH_KEY])) {
            $login_session_key = $data[AUTH_KEY];
            $this->db->where('LoginSessionKey', $login_session_key);
            $query = $this->db->get(SESSIONLOGS);
            if ($query->num_rows()) {
                $rw = $query->row();
                $mins = ((strtotime(get_current_date('%Y-%m-%d %H:%i:%s')) - 
                strtotime($rw->StartDate)) / 60);

                $this->db->where('LoginSessionKey', $login_session_key);
                $this->db->update(SESSIONLOGS, array('EndTime' => 
                get_current_date('%Y-%m-%d %H:%i:%s'), 'duration' => $mins));
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     *[city updation on login - api]
     */
    function update_login_analytic_data_post()
    {
      /*define variables start*/
      $data = $this->post_data;
      $return     = $this->return;
      /*define variables end*/

      if (isset($data)) 
      {
        $data['IPAddress'] = isset($data['IPAddress']) ? $data['IPAddress'] : '';

        $config = array(
            array(
                'field' => 'Longitude',
                'label' => 'Longitude',
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'Latitude',
                'label' => 'Latitude',
                'rules' => 'trim|required'
            )
        );    

        /*validation start*/
        $this->form_validation->set_rules($config); 
        if ($this->form_validation->run() == FALSE) 
        {
          $error = $this->form_validation->rest_first_error_string();         
          $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
          $return['Message'] = $error;

          $this->response($return);
        } 
        /*validation end*/

          $user_data = $this->login_model->update_city($data);

          $return['ResponseCode'] = $user_data['ResponseCode'];
          $return['Message']      = $user_data['Message'];
      }
      else
      {
        $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
        $return['Message']      = lang('input_invalid_format');
      }

      $this->response($return);/*Final output*/
    }
    
    public function send_otp_post() {
        /*define variables start*/
        $data = $this->post_data;
        $return     = $this->return;
        /*define variables end*/

        if (isset($data))  {
            $config = array(
                array(
                    'field' => 'Mobile',
                    'label' => 'mobile',
                    'rules' => 'trim|required|numeric'
                )
            );
            
            $this->form_validation->set_rules($config); 
            if ($this->form_validation->run() == FALSE) {
              $error = $this->form_validation->rest_first_error_string();         
              $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
              $return['Message'] = $error;
            } else {
                $mobile    = isset($data['Mobile']) ? $data['Mobile'] : '';
                $this->login_model->send_otp($mobile);
                $return['Message'] = "OTP send successfully";
            }
        } else {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message']      = lang('input_invalid_format');
        }

        $this->response($return);/*Final output*/
    }

    public function resend_otp_post() {
        /*define variables start*/
        $data = $this->post_data;
        $return     = $this->return;
        /*define variables end*/

        if (isset($data))  {
            $config = array(
                array(
                    'field' => 'Mobile',
                    'label' => 'mobile',
                    'rules' => 'trim|required|numeric'
                )
            );
            
            $this->form_validation->set_rules($config); 
            if ($this->form_validation->run() == FALSE) {
              $error = $this->form_validation->rest_first_error_string();         
              $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
              $return['Message'] = $error;
            } else {
                $mobile    = $data['Mobile'];
                $status = $this->login_model->resend_otp($mobile);
                switch ($status) {
                    case '1':
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = "Mobile number already been verified";
                    break;
                    case '2':
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = "Mobile number not exist";
                    break;
                    case '3':
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = "You can not send multiple request at same time";
                    break;
                    default:
                        $return['Message'] = "OTP send successfully";
                    break;
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message']      = lang('input_invalid_format');
        }
        $this->response($return);/*Final output*/
    }

    public function validate_otp_post() {
        /*define variables start*/
        $data = $this->post_data;
        $return     = $this->return;
        /*define variables end*/

        if (isset($data))  {
            $config = array(
                array(
                    'field' => 'OTP',
                    'label' => 'confirmation code',
                    'rules' => 'trim|required|max_length[6]'
                ),
                array(
                    'field' => 'Mobile',
                    'label' => 'mobile',
                    'rules' => 'trim|required|numeric'
                )
            );
            
            $this->form_validation->set_rules($config); 
            if ($this->form_validation->run() == FALSE) {
              $error = $this->form_validation->rest_first_error_string();         
              $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
              $return['Message'] = $error;
            } else {
                $otp    = $data['OTP'];
                $mobile    = $data['Mobile'];
                $verification = $this->login_model->check_otp($otp, $mobile);
                if(!$verification) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('invalid_otp');
                }
            }
        } else {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message']      = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    function check_apk_ver_post() { 
        $data = $this->post_data;
        $return     = $this->return;
        
        $version = isset($data['current_ver']) ? $data['current_ver'] : '';
        $device_type = isset($data['device_type']) ? $data['device_type'] : '';
        
        $response['clean_cache'] = 0; //
        $response['image_name'] = 'version11.jpg'; //new_version1.png
        $response['upgrade_required'] = 0;
        $response['upgrade_optional'] = 0;
        $response['apk_url'] = 0;
        $response['device_type'] = $device_type;
        $response['upgrade_required_msg'] = "अपना टैलेंट इंदौर को दिखाने के लिए और टैलंटेड इंदौरवासियों को Follow करने के लिए ऐप अपडेट करें";
        $response['upgrade_optional_msg'] = ""; //दिनभर इंदौर न्यूज़ के लिए भोपू तुरंत अपडेट करे

        if($device_type==1){
            $app_version = ANDROID_VERSION;
        } elseif($device_type==2){
            $app_version = IOS_VERSION;
        }

        $response['latest_version'] = $app_version;
        if(!empty($version) && $version < $app_version){
            $response['upgrade_required'] = 1; 
            $response['upgrade_optional'] = 0; 
        }        
        $return['Data'] = $response;        
        $this->response($return);
        exit;
    }
  }
