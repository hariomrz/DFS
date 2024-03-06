<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* All process like : smtp_email_listing
* @package    EmailSetting
* @author     Girish Patidar(23-01-2015)
* @version    1.0
*/

class Emailsetting extends Admin_API_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/login_model','admin/emailsetting_model'));
        
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];

    }
        
    /**
     * Function for show smtp setting listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function index_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/emailsetting';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('smtp_settings'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';

            $smtpResults = $this->emailsetting_model->getSmtpSettings($start_offset, $end_offset, $sort_by, $order_by);
            
            $Return['Data']['total_records'] = $smtpResults['total_records'];
            $Return['Data']['results'] = $smtpResults['results'];
            
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
     * Function for create smtp setting.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function create_smtp_setting_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('smtp_create_success');
        $Return['ServiceName']='admin_api/emailsetting/create_smtp_setting';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        if(isset($Data['EmailSettingId'])) $EmailSettingId = $Data['EmailSettingId']; else $EmailSettingId = '';
        
        if($EmailSettingId)//For Edit/update smtp setting detail rights
            $RightsId = getRightsId('smtp_settings_save_edit_event');
        else
            $RightsId = getRightsId('smtp_settings_save_add_event');
        
        //Check logged in user access right and allow/denied access
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {   
            /* Validation - starts */            
            if ($this->form_validation->run('api/smtp_setting') == FALSE) { // for web
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error; //Shows all error messages as a string
            } else {     
                $IsSSLRequire = 0;
                $IsUseLocalSMTP = 0;
                if(isset($Data['IsSSLRequire'])){
                    $IsSSLRequire = $Data['IsSSLRequire'];
                }
                
                if(isset($Data['EmailSettingId'])) $EmailSettingId = $Data['EmailSettingId']; else $EmailSettingId= '';
                
                /*if ($this->emailsetting_model->checkSmtpEmailExist($Data['FromEmail'],$EmailSettingId) == 'exist') {
                    $Return['ResponseCode'] = 512;
                    $Return['Message'] = lang('smtp_email_exists');
                    
                }else{*/
                
                    $emailDataArr = array();
                    $emailDataArr['Subject'] = "SMTP Check Email";
                    $emailDataArr['TemplateName'] = "emailer/smtp_check_email";
                    $emailDataArr['Email'] = ADMIN_EMAIL;
                    $emailDataArr['ServerName'] = $Data['ServerName'];
                    $emailDataArr['SPortNo'] = $Data['SPortNo'];
                    $emailDataArr['UserName'] = $Data['UserName'];
                    $emailDataArr['Password'] = $Data['Password'];
                    $emailDataArr['FromEmail'] = $Data['FromEmail'];
                    $emailDataArr['FromName'] = $Data['FromName'];
                    $emailDataArr['ReplyTo'] = $Data['ReplyTo'];

                    $result = checkSMTPSettingViaSendEmail($emailDataArr);
                    if($result=="invalid"){
                        $Return['ResponseCode'] = '104';
                        $Return['Message'] = lang('invalid_smtp_setting');                        
                    }else{
                        $dataArr = array();
                        $dataArr['Name'] = $Data['Name'];
                        $dataArr['IsUseLocalSMTP'] = $IsUseLocalSMTP;
                        $dataArr['FromEmail'] = $Data['FromEmail'];
                        $dataArr['FromName'] = $Data['FromName'];
                        $dataArr['ServerName'] = $Data['ServerName'];
                        $dataArr['SPortNo'] = $Data['SPortNo'];
                        $dataArr['UserName'] = $Data['UserName'];
                        $dataArr['Password'] = $Data['Password'];
                        $dataArr['IsSSLRequire'] = $IsSSLRequire;
                        $dataArr['ReplyTo'] = $Data['ReplyTo'];
                        $dataArr['StatusID'] = '2';

                        if(is_numeric($EmailSettingId)){
                            $this->emailsetting_model->updateSmtpSetting($dataArr,$EmailSettingId);
                        }else{
                            $this->emailsetting_model->createSmtpSetting($dataArr);
                        }

                        //For delete exisitng ip setting cache data
                        deleteCacheData('SmtpSettings');
                    }
                //}
                
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
    
    /**
     * Function for get smtp setting details.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function get_smtp_setting_details_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('smtp_create_success');
        $Return['ServiceName']='admin_api/emailsetting/create_smtp_setting';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        if(isset($Data) && $Data!=NULL )
        {       
            if(isset($Data['EmailSettingId'])) $EmailSettingId = $Data['EmailSettingId']; else $EmailSettingId= '';
            
            if($EmailSettingId && is_numeric($EmailSettingId)){
                
                $emailSettingData = $this->emailsetting_model->getEmailSettingById($EmailSettingId);
                //echo "<pre>";print_r($emailSettingData);
                $Return['Data']['results'] = $emailSettingData;
                
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
    
    
    /**
    * Function for Upate smtp setting status : active/inactive
    * Parameters : status, setting_id(s)
    * Return : Array
    */
    public function update_status_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/emailsetting/update_status';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if(isset($Data['status'])) $status = $Data['status']; else $status = '';
        
        if($status == 1)//For make inactive event
            $RightsId = getRightsId('smtp_settings_make_inactive_event');
        else if($status == 2)//For make active event
            $RightsId = getRightsId('smtp_settings_make_active_event');
        else if($status == 3)//For delete event
            $RightsId = getRightsId('smtp_settings_delete_event');
        else
            $RightsId = 0;
        
        //Check logged in user access right and allow/denied access
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL)
        {
            $status = isset($Data['status']) ? $Data['status'] : NULL;
            $setting_ids = isset($Data['setting_ids']) ? $Data['setting_ids'] : array();
            
            if (!empty($setting_ids))
            {
                $settingData = array();
                
                foreach ($setting_ids as $setting_id) {
                    $settingData[] = array('StatusID' => $status, 'EmailSettingID' => $setting_id);
                }
                
                /* update setting(s) Status */
                $this->emailsetting_model->updateMultipleSmtpSettingInfo($settingData, 'EmailSettingID');  
                
                //For delete exisitng ip setting cache data
                deleteCacheData('SmtpSettings');
            }
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    
    /**
     * Function for show smtp emails type listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function emailtype_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/emailsetting/emailtype';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('smtp_emails'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';

            $smtpResults = $this->emailsetting_model->getSmtpEmailsType($start_offset, $end_offset, $sort_by, $order_by);
            
            $Return['Data']['total_records'] = $smtpResults['total_records'];
            $Return['Data']['results'] = $smtpResults['results'];
            
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
    * Function for Upate smtp email type status : active/inactive
    * Parameters : status, emailtype_id(s)
    * Return : Array
    */
    public function update_emailtype_status_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/emailsetting/update_emailtype_status';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if(isset($Data['status'])) $status = $Data['status']; else $status = '';
        
        if($status == 1)//For make inactive event
            $RightsId = getRightsId('smtp_emails_make_inactive_event');
        else if($status == 2)//For make active event
            $RightsId = getRightsId('smtp_emails_make_active_event');
        else
            $RightsId = 0;
        
        //Check logged in user access right and allow/denied access
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL)
        {
            $status = isset($Data['status']) ? $Data['status'] : NULL;
            $emailtype_ids = isset($Data['emailtype_ids']) ? $Data['emailtype_ids'] : array();
            
            if (!empty($emailtype_ids))
            {
                $emailTypeData = array();
                
                foreach ($emailtype_ids as $emailtype_id) {
                    $emailTypeData[] = array('StatusID' => $status, 'EmailTypeID' => $emailtype_id);
                }
                
                /* update email type(s) Status */
                $this->emailsetting_model->updateSmtpEmailTypeInfo($emailTypeData, 'EmailTypeID');    
                
                //For delete exisitng ip setting cache data
                deleteCacheData('SmtpSettings');
            }
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
    * Function for Upate smtp email type details
    * Parameters : 
    * Return : Array
    */
    public function update_emailtype_details_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/emailsetting/update_emailtype_details';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('smtp_emails_edit_event'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL)
        {
            $Subject = isset($Data['Subject']) ? $Data['Subject'] : '';
            $EmailSettingID = isset($Data['EmailSettingID']) ? $Data['EmailSettingID'] : '1';
            $EmailTypeId = isset($Data['EmailTypeId']) ? $Data['EmailTypeId'] : NULL;
            
            if ($EmailTypeId && is_numeric($EmailTypeId))
            {
                $emailTypeData = array();
                $emailTypeData['Subject'] = $Subject;
                $emailTypeData['EmailSettingID'] = $EmailSettingID;
                
                /* update email type(s) details */
               $this->emailsetting_model->updateSmtpEmailTypaDetail($emailTypeData, $EmailTypeId);   
               
               //For delete exisitng ip setting cache data
                deleteCacheData('SmtpSettings');
            }
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for default media settings, media section and user list
     * Parameters : ,
     * Return : Array of media parameter
     */
    public function email_setting_param_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'email_setting_param';
        $Return['Data'] = array();
        
        $data['result'] = $this->emailsetting_model->getEmailSettingParamData();
        
        /* Retrun Data */
        $Return['Data'] = $data;
        //}
        /* Final output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
        
}//End of file emailsetting.php