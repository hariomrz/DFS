<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* All process like : smtp_email_listing
* @package    EmailSetting
* @author     Girish Patidar(23-01-2015)
* @version    1.0
*/

class Ipsetting extends Admin_API_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/login_model','admin/ipsetting_model'));
        
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
        $Return['ServiceName']='admin_api/ipsetting';
        $Return['Data']=array();
        $Data = $this->post_data;

        if(isset($Data['IpFor']))  $IpTypes = $Data['IpFor']; else $IpTypes= 1;
        if($IpTypes==1)//For Admin IPs
            $RightsId = getRightsId('ips_admin');
        else if($IpTypes==0)//For Users IPs
            $RightsId = getRightsId('ips_user');
        else
            $RightsId = 0;
        
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message'] = lang('permission_denied');
            $Return['DeniedHtml'] = accessDeniedHtml();
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
            
            if(isset($Data['IpFor']))  $IpFor=$Data['IpFor']; else $IpFor= '';

            $smtpResults = $this->ipsetting_model->getIpsList($start_offset, $end_offset, $sort_by, $order_by, $IpFor);
            
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
     * Function for create ip details
     * Parameters : From services.js(Angular file)
     * 
     */
    public function create_ip_details_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/ipsetting/create_ip_details';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        if(isset($Data['AllowedIpID'])) $AllowedIpID = $Data['AllowedIpID']; else $AllowedIpID= '';
        
        if($AllowedIpID)//For Edit/update ip detail rights
            $RightsId = getRightsId('ips_edit_event');
        else
            $RightsId = getRightsId('ips_add_event');
        
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
            if ($this->form_validation->run('api/ip_setting') == FALSE) { // for web
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error; //Shows all error messages as a string
            } else {
                
                if(isset($Data['IpAddress'])) $IpAddress = $Data['IpAddress']; else $IpAddress= NULL;
                if(isset($Data['Description'])) $Description = $Data['Description']; else $Description= '';
                //if(isset($Data['Status'])) $Status = $Data['Status']; else $Status= 1;
                if(isset($Data['AllowedIpID'])) $AllowedIpID = $Data['AllowedIpID']; else $AllowedIpID= '';
                $IpFor = $Data['IpFor'];
                
                if ($this->ipsetting_model->checkIpAdressExist($IpAddress,$IpFor,$AllowedIpID) == 'exist') {
                    $Return['ResponseCode'] = 512;
                    $Return['Message'] = lang('ip_exists');
                    
                }else{
                
                    $dataArr = array();
                    $dataArr['IP'] = $IpAddress;
                    //$dataArr['StatusID'] = $Status;
                    $dataArr['IsForAdmin'] = $IpFor;
                    $dataArr['Description'] = $Description;

                    if(is_numeric($AllowedIpID)){
                        $this->ipsetting_model->updateAllowedIpAddress($dataArr,$AllowedIpID);
                        $Return['Message'] = lang('ip_edit_success');
                    }else{
                        $dataArr['StatusID'] = 2;
                        $this->ipsetting_model->addAllowedIpAddress($dataArr);
                        $Return['Message'] = lang('ip_added_success');
                    }
                    
                    //For delete exisitng ip setting cache data
                    deleteCacheData('IpSettings');
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
    
    /**
    * Function for Upate ip status : delete, active,inactive
    * Parameters : status, ip_id(s)
    * Return : Array
    */
    public function update_status_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/ipsetting/update_status';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if(isset($Data['status'])) $Status = $Data['status']; else $Status = 2;
        
        if($Status==1)//Status 1 for Make Inactive IP
            $RightsId = getRightsId('ips_inactive_event');
        else if($Status==2)//Status 2 for Make Active IP
            $RightsId = getRightsId('ips_active_event');
        else if($Status==3)//Status 3 for Delete IP
            $RightsId = getRightsId('delete_user_event');
       
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
            $status = isset($Data['status']) ? $Data['status'] : 2;
            $IpFor = isset($Data['IpFor']) ? $Data['IpFor'] : 1;
            $allowed_ip_ids = isset($Data['allowed_ip_ids']) ? $Data['allowed_ip_ids'] : array();
            
            if (!empty($allowed_ip_ids))
            {
                $allowedIpData = array();
                $atLeastOneIPActive = false;
                foreach ($allowed_ip_ids as $allowed_ip_id) {
                    $currentIpInfo = $this->ipsetting_model->getIpDetailByIpAddress($allowed_ip_id);
                    if($status != 2 && $IpFor == 1 && $this->ipsetting_model->getAdminActiveIpsCount() < 2 && $currentIpInfo['StatusID'] == 2){
                        $atLeastOneIPActive = true;
                    }else{
                        $allowedIpData[] = array('StatusID' => $status, 'AllowedIpID' => $allowed_ip_id);
                    }
                }
                
                if($atLeastOneIPActive){
                    $Return['ResponseCode']='519';
                    $Return['Message']= lang('atleast_one_ip_active_for_admin');
                }else{
                    /* update ip(s) Status */
                    $this->ipsetting_model->updateIpInfo($allowedIpData, 'AllowedIpID');

                    if($status == 1){
                        $Return['Message']= lang('ip_inactive_msg');
                    }else if($status == 2){
                        $Return['Message']= lang('ip_active_msg');
                    }else if($status == 3){
                        $Return['Message']= lang('ip_delete_msg');
                    }

                    //For delete exisitng ip setting cache file
                    deleteCacheData('IpSettings');
                }
            }else{
                $Return['ResponseCode']='519';
                $Return['Message']= lang('input_invalid_format');
            }
        }else{
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
        
}//End of file ipsetting.php