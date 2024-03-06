<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
* All communication related process like : get_communications, send_communication
* @package    Communication
* @author     Ashwin kumar soni(09-11-2014)
* @version    1.0
*/
//require_once APPPATH . '/libraries/REST_Controller.php';

class Communication extends Admin_API_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(array('admin/users_model', 'admin/communication_model','admin/login_model'));
        
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID']; 
    }

    /**
     * Function for get communications data for user profile page
     * Parameters : From services.js(Angular file)
     * Return : Communication Array
     */
    public function index_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/communication';
        $Return['Data'] = array();

        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL) {
            if (isset($Data['Begin']))
                $start_offset = $Data['Begin'];
            else
                $start_offset = 0;

            if (isset($Data['End']))
                $end_offset = $Data['End'];
            else
                $end_offset = 10;

            /* Get Users data from communication_model */
            $Return['Data'] = $this->communication_model->getCommunications($Data['userId'],$start_offset, $end_offset);
        }else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }

        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for send communication mail and save in DB
     * Parameters : From services.js(Angular file)
     * Return : Status : success/error
     */
    public function send_communication_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/communication/send_communication';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if(isset($Data['permissiontype']) && $Data['permissiontype'] == "analytic_users"){
            $RightsId = getRightsId('communicate_user_event');
        }else{
            $RightsId = getRightsId('communicate_user_event');
        }
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(!empty($Data))
        {
            if(isset($Data['UserID'])) $user_id = $Data['UserID']; else $user_id = 0;
            if(isset($Data['subject'])) $subject = $Data['subject']; else $subject = 'Communication Mail';
            if(isset($Data['message'])) $message = $Data['message']; else $message = '';
            
            /* Get userdata from users_model */
            $user_data = $this->users_model->getProfileInfo($user_id, '', '');
            
            if(empty($user_data))
            {
                /* Error - If user not exist */
                $Return['ResponseCode'] = '672';
                $Return['Message'] = lang('not_valid_user');
            }else{
                
                /* Validation - starts */            
                if ($this->form_validation->run('api/validate_communication') == FALSE) { // for web
                    $error = $this->form_validation->rest_first_error_string();
                    $Return['ResponseCode'] = 511;
                    $Return['Message'] = $error; //Shows all error messages as a string
                } else {
                    $emailDataArr = array();
                    $emailDataArr['IsSave'] = EMAIL_ANALYTICS;//If you want to send email only not save in DB then set 1 otherwise set 0
                    $emailDataArr['IsResend'] = 0;
                    $emailDataArr['Subject'] = $subject;
                    $emailDataArr['TemplateName'] = "emailer/send_communication";
                    $emailDataArr['Email'] = $user_data['email'];
                    $emailDataArr['EmailTypeID'] = COMMUNICATION_EMAIL_TYPE_ID;
                    $emailDataArr['UserID'] = $user_id;
                    $emailDataArr['StatusMessage'] = "Communication";        
                    $emailDataArr['Data'] = array("FirstLastName" => $user_data['username'],"MainContent" => $message,"VCA_Info_Email" => VCA_INFO_EMAIL);

                    $result = sendEmailAndSave($emailDataArr, 1);
                    if($result=="invalid"){
                        $Return['ResponseCode'] = '104';
                        $Return['Message'] = lang('invalid_smtp_setting');                        
                    }else{
                        $Return['ResponseCode'] = '200';
                        $Return['Message'] = lang('success');
                        $Return['Data'] = array(
                               'status_message' => 'Message sent to user.',
                        );
                    }
                }
            }
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for send multiple users communication mail and save in DB
     * Parameters : From services.js(Angular file)
     * Return : Status : success/error
     */
    public function send_multiple_communication_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/communication/send_multiple_communication';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if(isset($Data['permissiontype']) && $Data['permissiontype'] == "analytic_users"){
            $RightsId = getRightsId('communicate_user_event');
        }else{
            $RightsId = getRightsId('communicate_user_event');
        }
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(!empty($Data))
        {
            if(isset($Data['users'])) $users = $Data['users']; else $users = '';
            if(isset($Data['subject'])) $subject = $Data['subject']; else $subject = 'Communication Mail';
            if(isset($Data['message'])) $message = $Data['message']; else $message = '';
            $userArr = explode(',', trim($users,','));
                        
            if(empty($userArr))
            {
                /* Error - If user not exist */
                $Return['ResponseCode'] = '672';
                $Return['Message'] = lang('not_valid_user');
            }else{
                /* Validation - starts */            
                if ($this->form_validation->run('api/validate_communication') == FALSE) { // for web
                    $error = $this->form_validation->rest_first_error_string();
                    $Return['ResponseCode'] = 511;
                    $Return['Message'] = $error; //Shows all error messages as a string
                } else {

                    $crm_filter = (!empty($Data['CRM_Filter'])) ? $Data['CRM_Filter'] : 0;
                    $crm_query = "";
                    if($crm_filter) {                        
                        $this->load->model(array('admin/users/crm_model'));
                        if($crm_filter) $crm_query = $this->crm_model->get_users($Data, true, true);
                    }

                    initiate_worker_job('send_multiple_communication', array('subject'=>$subject, 'message'=>$message, 'user_list'=>$userArr, 'crm_query' => $crm_query)); 
                    $Return['ResponseCode'] = '200';
                    $Return['Message'] = lang('success');
                    $Return['Data'] = array(
                           'status_message' => 'Message sent to user.',
                    );

                    /*$error = false;
                    foreach($userArr as $user_id){
                        if($user_id){
                            $userData = $this->users_model->getValueById(array('Email','FirstName','LastName'),$user_id);

                            $emailDataArr = array();
                            $emailDataArr['IsSave'] = EMAIL_ANALYTICS;//If you want to send email only not save in DB then set 1 otherwise set 0
                            $emailDataArr['IsResend'] = 0;
                            $emailDataArr['Subject'] = $subject;
                            $emailDataArr['TemplateName'] = "emailer/send_communication";
                            $emailDataArr['Email'] = $userData['Email'];
                            $emailDataArr['EmailTypeID'] = COMMUNICATION_EMAIL_TYPE_ID;
                            $emailDataArr['UserID'] = $user_id;
                            $emailDataArr['StatusMessage'] = "Communication";        
                            $emailDataArr['Data'] = array("FirstLastName" => stripslashes($userData['FirstName'].' '.$userData['LastName']),"MainContent" => $message,"VCA_Info_Email" => VCA_INFO_EMAIL);

                            $result = sendEmailAndSave($emailDataArr);
                            if($result=="invalid"){
                                $error = true;
                            }
                        }
                    }

                    if($error){
                        $Return['ResponseCode'] = '104';
                        $Return['Message'] = lang('invalid_smtp_setting');                        
                    }else{
                        $Return['ResponseCode'] = '200';
                        $Return['Message'] = lang('success');
                        $Return['Data'] = array(
                               'status_message' => 'Message sent to user.',
                        );
                    }*/
                }
            }
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for resend communication mail and save in DB
     * Parameters : From services.js(Angular file)
     * Return : Status : success/error
     */
    public function resend_communication_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/communication/resend_communication';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('email_analytics_emails_resend_event'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(!empty($Data))
        {
            if(isset($Data['CommunicationID'])){
            
                $CommunicationData = $this->communication_model->getCommunicationDetailById($Data['CommunicationID']);
                
                if($CommunicationData){
                    $emailDataArr = array();
                    $emailDataArr['IsSave'] = EMAIL_ANALYTICS;//If you want to send email only not save in DB then set 1 otherwise set 0
                    $emailDataArr['IsResend'] = 1;
                    $emailDataArr['Subject'] = $CommunicationData['Subject'];
                    $emailDataArr['TemplateName'] = "";
                    $emailDataArr['Email'] = $CommunicationData['EmailTo'];
                    $emailDataArr['EmailTypeID'] = $CommunicationData['EmailTypeID'];
                    $emailDataArr['UserID'] = $CommunicationData['UserID'];
                    $emailDataArr['StatusMessage'] = $CommunicationData['StatusMessage'];
                    $emailDataArr['Data'] = array();
                    $emailDataArr['Message'] = $CommunicationData['Body'];
                    
                    //For send email to user and save in DB
                    $result = sendEmailAndSave($emailDataArr);
                    if($result=="invalid"){
                        $Return['ResponseCode'] = '104';
                        $Return['Message'] = lang('invalid_smtp_setting');                        
                    }else{
                        $Return['ResponseCode'] = '200';
                        $Return['Message'] = lang('success');
                        $Return['Data'] = array(
                               'status_message' => 'Message sent to user.',
                        );
                    }
                }
                
            }else{
                $Return['ResponseCode'] = '672';
                $Return['Message'] = lang('invalid_communication_id');
            }
            
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
}//End of file communication.php
