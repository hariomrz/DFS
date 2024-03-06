<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * All Betainvite related api services
 * @package    BetaInvite
 * @author     Girish Patidar : 11-03-2015
 * @version    1.0
 */

class Betainvite extends Admin_API_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/betainvite_model','admin/login_model'));
        
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];

    }
        
    /**
     * Function for show invited users listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function index_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/betainvite';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        $global_settings = $this->config->item("global_settings");
        
        
        //Set rights id by action(register,delete,blocked,waiting for approval users)
        if(isset($Data['UserStatus']))  $UserStatus = $Data['UserStatus']; else $UserStatus = '2';
        
        if($UserStatus==2)//Status 2 for Joined users
            $RightsId = getRightsId('beta_invite_invited_users');
        else if($UserStatus==1)//Status 1 for not joined users
            $RightsId = getRightsId('beta_invite_not_joined_yet');
        else if($UserStatus==3)//Status 3 for Deleted users
            $RightsId = getRightsId('beta_invite_deleted_users');
        else if($UserStatus==4)//Status 4 for Removed access users
            $RightsId = getRightsId('beta_invite_removed_access_users');
        else
            $RightsId = 0;

        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            $Return['DeniedHtml'] = accessDeniedHtml();
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
            
        if(isset($Data) && $Data!=NULL ){
            
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['SearchKey']))  $search_keyword=$Data['SearchKey']; else $search_keyword='';
            $search_keyword=str_replace("_"," ",$search_keyword);

            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';

            if(isset($Data['UserStatus']))  $user_status=$Data['UserStatus']; else $user_status= '2';

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';            
            
            $tempResults = array();
            $userTemp = $this->betainvite_model->getBetaInviteUsers($start_offset, $end_offset, $start_date, $end_date, $user_status, $search_keyword, $sort_by, $order_by);

            foreach ($userTemp['results'] as $temp)
            {
                $temp['name'] = stripslashes($temp['name']);
                $temp['created_date'] = date($global_settings['date_format'],  strtotime($temp['created_date']));
                $temp['modified_date'] = date($global_settings['date_format'],  strtotime($temp['modified_date']));
                $tempResults[] = $temp;
            }
            $Return['Data']['total_records'] = $userTemp['total_records'];
            $Return['Data']['results'] = $tempResults;
            
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
     * Function for download beta invite users
     * Parameters : From services.js(Angular file)
     */
    public function download_beta_users_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/betainvite/download_beta_users';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        if(!in_array(getRightsId('beta_invite_download_event'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL ){
            
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['SearchKey']))  $search_keyword=$Data['SearchKey']; else $search_keyword='';
            $search_keyword=str_replace("_"," ",$search_keyword);

            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';
            if(isset($Data['dateFilterText'])) $dateFilterText = $Data['dateFilterText']; else $dateFilterText = "All";

            if(isset($Data['UserStatus']))  $user_status=$Data['UserStatus']; else $user_status= '2';

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';            
            
            $userTemp = $this->betainvite_model->getBetaInviteUsers($start_offset, $end_offset, $start_date, $end_date, $user_status, $search_keyword, $sort_by, $order_by);
            
            if($Data['UserStatus'] == 2){
                $file_name = "InvitedUsers-".date("dMY").".xls";
                $excelInput = array();
                foreach($userTemp['results'] as $row){
                    $userArr['username'] = stripslashes($row['name']);
                    $userArr['email'] = $row['email'];
                    $userArr['invite_date'] = $row['created_date'];
                    $userArr['code'] = $row['code'];
                    $userArr['last_login'] = $row['lastlogindate'];
                    $userArr['register_email'] = $row['register_email'];
                    $excelInput[] = $userArr;
                }
            
                $excelArr = array();
                $excelArr['headerArray'] = array('username'=>'User Name','email'=>'Email','invite_date'=>'Invite Date', 'code'=>'Code', 'last_login'=>'Last Login','register_email'=>'Register Email');
                $excelArr['sheetTitle'] = 'Invited Users';
                $excelArr['fileName'] = $file_name;
                $excelArr['folderPath'] = DOC_PATH.ROOT_FOLDER.'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/";            
                $excelArr['inputData'] = $excelInput;
                $excelArr['ReportHeader'] = array("ReportName" => "Invited Users", "dateFilterText" => $dateFilterText);
                
            }else if($Data['UserStatus'] == 1){
                $file_name = "NotJoinedYetUsers-".date("dMY").".xls";
                $excelInput = array();
                foreach($userTemp['results'] as $row){
                    $userArr['username'] = stripslashes($row['name']);
                    $userArr['email'] = $row['email'];
                    $userArr['invite_date'] = $row['created_date'];
                    $userArr['code'] = $row['code'];
                    $excelInput[] = $userArr;
                }
            
                $excelArr = array();
                $excelArr['headerArray'] = array('username'=>'User Name','email'=>'Email','invite_date'=>'Invite Date', 'code'=>'Code');
                $excelArr['sheetTitle'] = 'Not Joined Yet Users';
                $excelArr['fileName'] = $file_name;
                $excelArr['folderPath'] = DOC_PATH.ROOT_FOLDER.'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/";
                $excelArr['inputData'] = $excelInput;
                $excelArr['ReportHeader'] = array("ReportName" => "Not Joined Yet Users", "dateFilterText" => $dateFilterText);
                
            }else if($Data['UserStatus'] == 3){
                $file_name = "DeletedUsers-".date("dMY").".xls";
                $excelInput = array();
                foreach($userTemp['results'] as $row){
                    $userArr['username'] = stripslashes($row['name']);
                    $userArr['email'] = $row['email'];
                    $userArr['invite_date'] = $row['created_date'];
                    $userArr['code'] = $row['code'];
                    $userArr['last_login'] = $row['lastlogindate'];
                    $userArr['deleted_date'] = $row['modified_date'];
                    $excelInput[] = $userArr;
                }
            
                $excelArr = array();
                $excelArr['headerArray'] = array('username'=>'User Name','email'=>'Email','invite_date'=>'Invite Date', 'code'=>'Code', 'last_login'=>'Last Login','deleted_date'=>'Deleted Date');
                $excelArr['sheetTitle'] = 'Deleted Users';
                $excelArr['fileName'] = $file_name;
                $excelArr['folderPath'] = DOC_PATH.ROOT_FOLDER.'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/";            
                $excelArr['inputData'] = $excelInput;
                $excelArr['ReportHeader'] = array("ReportName" => "Deleted Users", "dateFilterText" => $dateFilterText);
                
            }else if($Data['UserStatus'] == 4){
                $file_name = "RemovedAccessUsers-".date("dMY").".xls";
                $excelInput = array();
                foreach($userTemp['results'] as $row){
                    $userArr['username'] = stripslashes($row['name']);
                    $userArr['email'] = $row['email'];
                    $userArr['invite_date'] = $row['created_date'];
                    $userArr['code'] = $row['code'];
                    $userArr['remove_access_date'] = $row['modified_date'];
                    $userArr['last_login'] = $row['lastlogindate'];
                    $userArr['register_email'] = $row['register_email'];
                    $excelInput[] = $userArr;
                }
            
                $excelArr = array();
                $excelArr['headerArray'] = array('username'=>'User Name','email'=>'Email','invite_date'=>'Invite Date', 'code'=>'Code', 'remove_access_date'=>'Remove Access Date', 'last_login'=>'Last Login','register_email'=>'Register Email');
                $excelArr['sheetTitle'] = 'Removed Access Users';
                $excelArr['fileName'] = $file_name;
                $excelArr['folderPath'] = DOC_PATH.ROOT_FOLDER.'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/";            
                $excelArr['inputData'] = $excelInput;
                $excelArr['ReportHeader'] = array("ReportName" => "Removed Access Users", "dateFilterText" => $dateFilterText);
                
            }
                     
            $result = downloadExcelFile($excelArr);            
            if($result){
                $csv_filepath = DOC_PATH.ROOT_FOLDER.'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/".$file_name;
                $csv_url = base_url().'/admin/users/downloadbetainviteuser/'.str_replace('.xls', '', $file_name);
                $Return['csv_url'] = $csv_url;
                $Return['csv_path'] = $csv_filepath;
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
     * Function for delete downloaded csv file from folder
     */
    public function delete_csv_file_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/betainvite/delete_csv_file';
        $Data = $this->post_data;
        
        if(isset($Data) && $Data!=NULL ){
            if(isset($Data['FilePath']) && $Data['FilePath'] != ""){
                if(file_exists($Data['FilePath'])){
                    @unlink($Data['FilePath']);
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
    * Function for Upate error log status : delete, complete, ignore
    * Parameters : status, errorLogId(s)
    * Return : Array
    */
    public function update_status_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/betainvite/update_status';
        $Return['Data'] = array();
        $Data = $this->post_data;
                
        //Set rights id by action(delete,complete,ignore,unignore)
        if(isset($Data['status'])) $Status = $Data['status']; else $Status = '';
        
        if($Status==1)//Status 1 for reinvite user
            $RightsId = getRightsId('beta_invite_reinvite_event');
        else if($Status==2)//Status 2 for grant access to user
            $RightsId = getRightsId('beta_invite_grant_access_event');
        else if($Status==3)//Status 3 for Delete invited user
            $RightsId = getRightsId('beta_invite_delete_event');
        else if($Status==4)//Status 4 for remove access
            $RightsId = getRightsId('beta_invite_remove_access_event');
        else
            $RightsId = 0;
        
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL){
            
            $status = isset($Data['status']) ? $Data['status'] : NULL;
            $inviteaction = isset($Data['inviteaction']) ? $Data['inviteaction'] : NULL;
            $BetaInviteIds = isset($Data['BetaInviteIds']) ? $Data['BetaInviteIds'] : array();
            
            if($inviteaction == "reinvite"){
                foreach ($BetaInviteIds as $BetaInviteId) {
                    if($BetaInviteId != ""){
                        $BetaInviteInfo = $this->betainvite_model->getBetaInviteById($BetaInviteId);
                        
                        if(is_numeric($BetaInviteInfo['BetaInviteID'])){
                            $InviteGUID = $BetaInviteInfo['BetaInviteGUID'];
                            $InviteCode = $BetaInviteInfo['Code'];
                            $InviteUrl = $url = site_url('usersite/sitemap').'?BetaInviteKey='.$InviteGUID;
                            
                            $emailDataArr = array();
                            $emailDataArr['IsSave'] = EMAIL_ANALYTICS;//If you want to send email only not save in DB then set 1 otherwise set 0
                            $emailDataArr['IsResend'] = 0;
                            $emailDataArr['Subject'] = "Beta Invite";
                            $emailDataArr['TemplateName'] = "emailer/emailer_betainvite";
                            $emailDataArr['Email'] = $BetaInviteInfo['Email'];
                            $emailDataArr['UserID'] = '';
                            $emailDataArr['EmailTypeID'] = BETAINVITE_EMAIL_TYPE_ID;
                            $emailDataArr['StatusMessage'] = "BetaInvite";
                            $emailDataArr['Data'] = array("FirstLastName" => $BetaInviteInfo['Name'],"Code" => $InviteCode,'InviteUrl' => $InviteUrl);

                            sendEmailAndSave($emailDataArr);
                        }
                    }
                }
            }else{
                if (!empty($BetaInviteIds)){
                    $BetaInviteData = array();

                    foreach ($BetaInviteIds as $BetaInviteId) {
                        $BetaInviteData[] = array('StatusID' => $status, 'ModifiedDate' => date("Y-m-d H:i:s"), 'BetaInviteID' => $BetaInviteId);
                    }

                    /* update beta invite Status */
                    if(!empty($BetaInviteData)){
                        $this->betainvite_model->updateBatchBetaInviteInfo($BetaInviteData, 'BetaInviteID');
                    }else{
                        $Return['ResponseCode']='519';
                        $Return['Message']= lang('input_invalid_format');
                    }

                }else{
                    $Return['ResponseCode']='519';
                    $Return['Message']= lang('input_invalid_format');
                }
            }
        }else{
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for check entered email exist or not.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function checkemailexist_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/betainvite/checkemailexist';
        $Data = $this->post_data;
                    
        if(isset($Data) && $Data!=NULL ){
            
            if(isset($Data['Email'])) $Email = $Data['Email']; else $Email = '';
            if($Email == ''){
                $Return['ResponseCode']='519';
                $Return['Message']= lang('email_required');
            }else{
                $exist = $this->betainvite_model->checkBetaInviteEmailExist($Email);
                if($exist == "exist"){
                    $Return['Message'] = lang('beta_email_exist');
                }
                $Return['results'] = $exist;
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
     * Function for send beta invite to users.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function sendbetainvite_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('beta_invite_send_success');
        $Return['ServiceName']='admin_api/betainvite/checkemailexist';
        $Data = $this->post_data;
                    
        if(!in_array(getRightsId('send_beta_invite_manual_invite'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL ){            
            //echo "<pre>";print_r($Data);die;
            if(is_array($Data['UserArr']) && !empty($Data['UserArr'])){
                
                foreach($Data['UserArr'] as $user){
                    if($this->betainvite_model->checkBetaInviteEmailExist($user['email']) == "notexist"){
                        $dataArr = array();
                        $InviteCode = generateRandomCode();
                        $InviteGUID = get_guid();//uniqid();
                        $dataArr['Name'] = $user['name'];
                        $dataArr['Email'] = $user['email'];
                        $dataArr['Code'] = $InviteCode;
                        $dataArr['BetaInviteGUID'] = $InviteGUID;
                        $dataArr['StatusID'] = '1';
                        $dataArr['CreatedDate'] = date("Y-m-d H:i:s");
                        $result = $this->betainvite_model->saveBetaInviteDetails($dataArr);
                        if(is_numeric($result)){
                            $InviteUrl = $url = site_url('usersite/sitemap').'?BetaInviteKey='.$InviteGUID;
                            $emailDataArr = array();
                            $emailDataArr['IsSave'] = EMAIL_ANALYTICS;//If you want to send email only not save in DB then set 1 otherwise set 0
                            $emailDataArr['IsResend'] = 0;
                            $emailDataArr['Subject'] = "Beta Invite";
                            $emailDataArr['TemplateName'] = "emailer/emailer_betainvite";
                            $emailDataArr['Email'] = $user['email'];
                            $emailDataArr['UserID'] = '';
                            $emailDataArr['EmailTypeID'] = BETAINVITE_EMAIL_TYPE_ID;
                            $emailDataArr['StatusMessage'] = "BetaInvite";
                            $emailDataArr['Data'] = array("FirstLastName" => $user['name'],"Code" => $InviteCode,'InviteUrl' => $InviteUrl);

                            sendEmailAndSave($emailDataArr);
                        }                        
                    }
                }
            }else{
                $Return['ResponseCode']='519';
                $Return['Message']= lang('input_invalid_format');
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
        
}//End of file betainvite.php