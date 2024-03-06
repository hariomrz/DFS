<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* Controller to manage Team page information
* @package    Team
* @author     V-INFOTECH
* @version    1.0
*/

class Team extends Admin_API_Controller
{
    
    /**
    * Class Constructor
    */    
    public function __construct()
	{
        parent::__construct();
        $this->load->model(array('group/group_model','admin/login_model'));
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
        
        $this->check_module_status(1);
    }
	
    /**
    * Function to list team pages
    * Parameters : From services.js(Angular file)
    */
    public function index_post()
    {
        $Return['ResponseCode'] =   '200';
        $Return['Message']      =   lang('success');
        $Return['ServiceName']  =   'admin_api/conference';
        $Return['Data']         =   array();
        $Data                   =   $this->post_data;
        
        //Set rights id by action(register,delete,blocked,waiting for approval users)
        //Set rights id by action(register,delete,blocked,waiting for approval users)
        if(!empty($Data['UserStatus']))  $user_status=$Data['UserStatus']; else $user_status= '2';
        if($user_status==2)//Status 2 for Register users
            $RightsId = getRightsId('registered_user');
        else if($user_status==3)//Status 3 for Deleted users
            $RightsId = getRightsId('deleted_user');
        else if($user_status==4)//Status 4 for Blocked users
            $RightsId = getRightsId('blocked_user');
        else if($user_status==1)//Status 2 for Waiting for Approval users
            $RightsId = getRightsId('waiting_for_approval');
        
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
            
        if(isset($Data) && $Data!=NULL )
        {
            $CurrentUser    = $this->UserID;
            $PageNo         = 0;
            $PageSize       = PAGE_SIZE;
            $SearchText     = "";   
            $SortBy     = "";
            $OrderBy    = ""; 
            if(isset($Data['Begin']) && $Data['Begin']!='') {
                $PageNo = $Data['Begin'];
            }
            if(isset($Data['End']) && $Data['End']!='') {
                $PageSize = $Data['End'];
            }
            
            $SortBy     = !empty($Data['SortBy'])?$Data['SortBy']:"GroupName";
            
            $CategoryID = !empty($Data['CategoryID'])?$Data['CategoryID']:"";
            
            $OrderBy = isset($Data['OrderBy'])?$Data['OrderBy']:"ASC";
            if(isset($Data['SearchKey']) && $Data['SearchKey']!='') 
            {
                $SearchText = $Data['SearchKey'];
            }

            $Input = array('SearchText'=>$SearchText,'CategoryID'=>$CategoryID,'UserID'=>$CurrentUser,'SortBy'=>$SortBy,'OrderBy'=>$OrderBy);
            $Return['Data']['results']          = $this->group_model->admin_group_listing($Input,$PageNo,$PageSize,0,FALSE,TRUE);
            $Return['Data']['total_records']    = $this->group_model->admin_group_listing($Input,'','',0,TRUE,TRUE);             
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
    * Function Name: delete_page
    * @param LoginSessionKey
    * @param GroupGUID
    * Description: 
    */
    public function delete_page_post() 
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('page_success_delete');
        $Return['ServiceName']='admin_api/team/delete_page';
        $Return['Data']=array(); 
        $Data=$this->post_data;
        
        /* Validation - starts */
        $validation_rule        =    $this->form_validation->_config_rules['api/team/delete_page'];
        
        $this->form_validation->set_rules($validation_rule); 
        
        if($this->form_validation->run() == FALSE) 
        {
          $error = $this->form_validation->rest_first_error_string();         
          $Return['ResponseCode']   = 511;
          $Return['Message']        = $error; 
        } 
        else 
        { 
            $page_guid = !empty($Data['GroupGUID'])?$Data['GroupGUID']:'';
            $action_type = 'Delete';
            $group_id = get_detail_by_guid($page_guid,1);
            $req_data = array('GroupID'=>$group_id,'ActionType'=>$action_type);
            $status = $this->group_model->delete($req_data, $this->UserID);
            
            //delete all group activities
            //$this->group_model->delete_group_activities($group_id);
        }
        
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);     
    }

    /**
    * Function Name: delete_pages(multiple)
    * @param LoginSessionKey
    * @param GroupGUIDS
    * Description: 
    */
    public function delete_pages_post() 
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success_delete');
        $Return['ServiceName']='admin_api/team/delete_pages';
        $Return['Data']=array(); 
        $Data=$this->post_data;
        
         /* Validation - starts */
        $validation_rule        =    $this->form_validation->_config_rules['api/team/delete_pages'];
        
        $this->form_validation->set_rules($validation_rule); 
        
        if($this->form_validation->run() == FALSE) 
        {
          $error = $this->form_validation->rest_first_error_string();         
          $Return['ResponseCode']   = 511;
          $Return['Message']        = $error; 
        } 
        else 
        { 
            $page_guids     = !empty($Data['GroupGUIDS'])?$Data['GroupGUIDS']:'';
            $action_type    = !empty($Data['ActionType'])?$Data['ActionType']:'';
            $req_data       = array('GroupGUIDS'=>$page_guids,'ActionType'=>$action_type);
            $status         = $this->group_model->delete($req_data);
        }
        
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);     
    }


    /**
    * Function for download page list
    * Parameters : 
    */
    public function download_list_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/team/download_list';
        $Return['Data']=array();
        $Data = $this->post_data;

        $RightsId = getRightsId('download_users_event');
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            $status_id = 0;
            $CurrentUser    = $this->UserID;
            $PageNo         = 0;
            $PageSize       = PAGE_SIZE;
            $SearchText     = "";   
            $SortBy     = "";
            $OrderBy    = ""; 
            if(isset($Data['Begin']) && $Data['Begin']!='') {
                $PageNo = $Data['Begin'];
            }
            if(isset($Data['End']) && $Data['End']!='') {
                $PageSize = $Data['End'];
            }
            
            $SortBy     = !empty($Data['SortBy'])?$Data['SortBy']:"GroupName";
            
            $CategoryID = !empty($Data['CategoryID'])?$Data['CategoryID']:"";
            
            $OrderBy = isset($Data['OrderBy'])?$Data['OrderBy']:"ASC";
            if(isset($Data['SearchKey']) && $Data['SearchKey']!='') 
            {
                $SearchText = $Data['SearchKey'];
            }

            $Input = array('SearchText'=>$SearchText,'CategoryID'=>$CategoryID,'UserID'=>$CurrentUser,'SortBy'=>$SortBy,'OrderBy'=>$OrderBy);
            $userTemp['results'] = $this->group_model->admin_group_listing($Input,$PageNo,$PageSize,0,FALSE,TRUE);
            
            $excelInput = array();
            
            foreach($userTemp['results'] as $group)
            {
                $userArr['group_name']  = stripslashes($group['GroupName']);
                $userArr['privacy']     = stripslashes($group['IsPublic']);
                $userArr['member_count'] = stripslashes($group['MemberCount']);
                $userArr['created_date']= stripslashes($group['CreatedDate']);
                $userArr['owner']       = stripslashes($group['CreatedBy']['FirstName'].' '.$group['CreatedBy']['LastName']);
                $excelInput[]           = $userArr;
            }
            
            $sheetTitle ="Test";
            $excelArr = array();
            $excelArr['headerArray'] = array(
                'group_name'=>'Page Name',
                'privacy'=>'Privacy',
                'member_count'=>'Member Count',
                'created_date'=>'Created On',
                'owner'=>'Owner'
                );
            $excelArr['sheetTitle'] = $sheetTitle;
            $excelArr['fileName'] = "PageList.xls";
            $excelArr['folderPath'] = DOC_PATH.ROOT_FOLDER.'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/";            
            $excelArr['inputData'] = $excelInput;
            $excelArr['ReportHeader'] = array("ReportName" => $sheetTitle, "dateFilterText" => "");
            
            $result = $this->group_model->downloadExcelFile($excelArr);            
            if($result){
                $csv_url = base_url().'/admin/team/downloadpagelist';
                $Return['csv_url'] = $csv_url;
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message']      = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    /**
    * Function to change official page owner 
    * @param : owner_guid,page_guid
    * @output : JSON
    */
    public function change_owner_post()
    {
        $Return['ServiceName']  = 'admin_api/team/change_owner';
        $Return['Data']         = array(); 
        $Return['Message']      = lang('success'); 
        $Data                   = $this->post_data;
        
        /* Validation - starts */
        $validation_rule        =    $this->form_validation->_config_rules['api/team/change_owner'];
        
        $this->form_validation->set_rules($validation_rule); 
        
        if($this->form_validation->run() == FALSE) 
        {
          $error = $this->form_validation->rest_first_error_string();         
          $Return['ResponseCode']   = 511;
          $Return['Message']        = $error; 
        } 
        else 
        { 
            if(isset($Data['GroupGUID']))  $group_guid=$Data['GroupGUID']; else $group_guid= 0;
            if(isset($Data['OwnerGUID']))  $owner_guid=$Data['OwnerGUID']; else $owner_guid= 0;
            $status = $this->group_model->change_owner($owner_guid,$group_guid);
            $Return['ResponseCode']='200';
        }
        
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);  
    }

    /**
    * Callback Function to validate GroupGUID
    * Parameters: group_guid
    * Output    : boolean(true/false) 
    */
    function validate_groupguid($group_guid)
    {
        $res = get_detail_by_guid($group_guid,1,'GroupID',2);
        if(!empty($res))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
    * Function to fetch group owners
    * Parameters: @
    * Output    : boolean(true/false) 
    */
    function search_group_user_post()
    {
        $return['ServiceName']  = 'admin_api/team/search_group_user';
        $return['Data']         = array(); 
        $Data                   = $this->post_data;
        
        /* Validation - starts */
        $validation_rule        =    $this->form_validation->_config_rules['api/team/search_group_user'];
        
        $this->form_validation->set_rules($validation_rule); 
        
        if($this->form_validation->run() == FALSE) 
        {
          $error = $this->form_validation->rest_first_error_string();         
          $return['ResponseCode']   = 511;
          $return['Message']        = $error; 
        } 
        else 
        { 
            $page_no    = isset($Data['PageNo'])    ? $Data['PageNo']  : '' ;
            $page_size  = isset($Data['PageSize'])  ? $Data['PageSize']: PageSize ;
            $current_group_owner_guid  = isset($Data['GroupOwnerGUID'])  ? $Data['GroupOwnerGUID']: '' ;
            $group_guid  = isset($Data['GroupGUID'])  ? $Data['GroupGUID']: '' ;
            $search_key = isset($Data['SearchKeyword'])  ? $Data['SearchKeyword']: '' ;
            $return['Data']             = $this->group_model->search_group_user($search_key,$group_guid,$current_group_owner_guid,$page_no,$page_size);
            $return['ResponseCode']     = 200;
        }
        
        /* Final Output */
        $this->response($return);  
    }

}//End of file team.php
