<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* All process like : Support and Error log section
* @package    Support
* @author     Girish Patidar : 04-03-2015
* @version    1.0
*/

class Support extends Admin_API_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/support_model','admin/login_model'));
        
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];

    }
        
    /**
     * Function for show user listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function index_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/support';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        //For error types listing(pending,ignored and completed)
        if(isset($Data['ErrorStatus']))  $error_status = $Data['ErrorStatus']; else $error_status = '1';
        if($error_status==1)//Status 1 for Penging error logs
            $RightsId = getRightsId('support_request_listing_pending');
        else if($error_status==2)//Status 2 for Completed error logs
            $RightsId = getRightsId('support_request_listing_completed');
        else if($error_status==4)//Status 4 for ignored error logs
            $RightsId = getRightsId('support_request_listing_ignored');
        else
            $RightsId = 0;
        
        //Check logged in user access right and allow/denied access
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            $Return['DeniedHtml'] = accessDeniedHtml();
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
                  
        $global_settings = $this->config->item("global_settings");
        
        if(isset($Data) && $Data!=NULL ){
            
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';
            
            if(isset($Data['SearchKey']))  $search_keyword=$Data['SearchKey']; else $search_keyword='';
            $search_keyword=str_replace("_"," ",$search_keyword);

            if(isset($Data['ErrorStatus']))  $error_status = $Data['ErrorStatus']; else $error_status = '1';

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';
            if(isset($Data['ErrorTypeId'])) $ErrorTypeId = $Data['ErrorTypeId']; else $ErrorTypeId = '0';
            
            
            $tempResults = array();
            $errorlogTemp = $this->support_model->getSupportErrorLogList($start_offset, $end_offset, $start_date, $end_date, $error_status, $sort_by, $order_by, $ErrorTypeId,$search_keyword);

            foreach ($errorlogTemp['results'] as $temp){
                $temp['CreatedDate'] = date($global_settings['date_format'],  strtotime($temp['CreatedDate']));
                if(strlen($temp['ErrorDescription']) > 20){
                    $temp['sort_description'] = substr($temp['ErrorDescription'], 0, 20).' ...';
                }else{
                    $temp['sort_description'] = $temp['ErrorDescription'];
                }
                
                $tempResults[] = $temp;
            }
            $Return['Data']['total_records'] = $errorlogTemp['total_records'];
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
    * Function for Upate error log status : delete, complete, ignore
    * Parameters : status, errorLogId(s)
    * Return : Array
    */
    public function update_status_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/support/update_status';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        //Set rights id by action(delete,complete,ignore,unignore)
        if(isset($Data['status'])) $Status = $Data['status']; else $Status = '';
        
        if($Status==1)//Status 1 for unignore error log
            $RightsId = getRightsId('support_request_listing_unignore_event');
        else if($Status==2)//Status 2 for mark complete error log
            $RightsId = getRightsId('support_request_listing_complete_event');
        else if($Status==3)//Status 3 for Delete error log
            $RightsId = getRightsId('support_request_listing_delete_event');
        else if($Status==4)//Status 4 for ignore error log
            $RightsId = getRightsId('support_request_listing_ignore_event');
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
            
            $status = isset($Data['status']) ? $Data['status'] : 2;
            $errorLogIds = isset($Data['errorLogIds']) ? $Data['errorLogIds'] : array();
            
            if (!empty($errorLogIds)){
                $errorLogData = array();
                
                foreach ($errorLogIds as $errorLogId) {
                    $errorLogData[] = array('StatusID' => $status, 'ErrorLogID' => $errorLogId);
                }
                
                /* update error log(s) Status */
                $this->support_model->updateBatchErrorLogInfo($errorLogData, 'ErrorLogID');   
                
                if($status == 3){
                    $Return['Message']= lang('deleted_msg');
                }else if($status == 2){
                    $Return['Message']= lang('completed_msg');
                }else if($status == 4){
                    $Return['Message']= lang('ignored_msg');
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
    
    /**
     * Function for download error logs
     * Parameters : From services.js(Angular file)
     * 
     */
    public function download_error_logs_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/support/download_error_logs';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        if(!in_array(getRightsId('support_request_listing_export_to_excel_event'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL ){
            
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';
            if(isset($Data['dateFilterText'])) $dateFilterText = $Data['dateFilterText']; else $dateFilterText = "All";

            if(isset($Data['ErrorStatus']))  $error_status = $Data['ErrorStatus']; else $error_status = '1';

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';
            if(isset($Data['ErrorTypeId'])) $ErrorTypeId = $Data['ErrorTypeId']; else $ErrorTypeId = '0';
                       
            $errorlogTemp = $this->support_model->getSupportErrorLogList($start_offset, $end_offset, $start_date, $end_date, $error_status, $sort_by, $order_by, $ErrorTypeId);
            
            $excelInput = array();
            foreach($errorlogTemp['results'] as $row){
                $inputArr['title'] = stripslashes($row['Title']);
                $inputArr['type'] = $row['ErrorType'];
                $inputArr['created_date'] = $row['CreatedDate'];
                $inputArr['operating_system'] = $row['OperatingSystem'];
                $inputArr['ip_address'] = $row['IPAddress'];
                $inputArr['reporter'] = $row['Reporter'];
                $inputArr['reporter_email'] = $row['ReporterEmail'];
                $inputArr['description'] = $row['ErrorDescription'];
                $excelInput[] = $inputArr;
            }
            
            $excelArr = array();
            $excelArr['headerArray'] = array('title'=>'Title','type'=>'Type','created_date'=>'Created Date','operating_system'=>'Operating System','ip_address'=>'IP Address','reporter'=>'Reporter','reporter_email'=>'Reporter Email','description'=>'Description');
            $excelArr['sheetTitle'] = 'Error Logs';
            $excelArr['fileName'] = "ErrorLogs.xls";
            $excelArr['folderPath'] = DOC_PATH.ROOT_FOLDER.'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/";            
            $excelArr['inputData'] = $excelInput;
            $excelArr['ReportHeader'] = array("ReportName" => "Support Error Logs", "dateFilterText" => $dateFilterText);
            
            $result = $this->support_model->downloadExcelFile($excelArr);            
            if($result){
                $csv_url = base_url().'/admin/users/downloaderrorlogs';
                $Return['csv_url'] = $csv_url;
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
    * Function for get error log details
    * Parameters :  media_id
    * Return : Array of error log details
    */
    public function error_log_detail_post(){
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/support/error_log_detail';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('support_request_listing_suppport_request_view'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            $Return['DeniedHtml'] = accessDeniedHtml();
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL) {
            $errorLogDetailArr = array();
            $errorLogFiles = array();
            
            $ErrorLogID = isset($Data['ErrorLogID']) ? $Data['ErrorLogID'] : '';
            
            if(is_numeric($ErrorLogID)){
                
                $errorLogDetailArr = $this->support_model->getErrorLogDetailById($ErrorLogID);
                
                if($errorLogDetailArr['Reporter'])$errorLogDetailArr['Reporter'] = $errorLogDetailArr['Reporter']; else $errorLogDetailArr['Reporter'] = "N/A";
                if($errorLogDetailArr['ReporterEmail'])$errorLogDetailArr['ReporterEmail'] = $errorLogDetailArr['ReporterEmail']; else $errorLogDetailArr['ReporterEmail'] = "N/A";
                
                if(trim($errorLogDetailArr['Path']) != ""){
                    $fileArr = explode(",", $errorLogDetailArr['Path']);
                    if(!empty($fileArr)){
                        foreach($fileArr as $file){
                            $path = IMAGE_SERVER_PATH.PATH_IMG_UPLOAD_FOLDER.'support/'.trim($file);
                            $errorLogFiles[] = $path;
                        }
                    }
                }
                $errorLogDetailArr['QueryTime'] = ($errorLogDetailArr['QueryTime']) ? $errorLogDetailArr['QueryTime'] : '0';
                $errorLogDetailArr['errorLogAttachments'] = $errorLogFiles;
            }
            
            $Return['Data'] = $errorLogDetailArr;
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
        
}//End of file support.php