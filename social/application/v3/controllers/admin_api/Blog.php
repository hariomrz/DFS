<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* Controller to manage Blog page information
* @package    Blog
* @author     V-INFOTECH
* @version    1.0
*/

class Blog extends Admin_API_Controller
{
    
    /**
    * Class Constructor
    */    
    public function __construct()
	{
        parent::__construct();
        $this->load->model(array('blog/blog_model','admin/login_model'));
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
        $this->RoleID = $logged_user_data['Data']['RoleID'];
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
            $Return['Data']['results']          = $this->group_model->list_group($Input,$PageNo,$PageSize,0,FALSE,TRUE);
            $Return['Data']['total_records']    = $this->group_model->list_group($Input,'','',0,TRUE,TRUE);             
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
    * Function Name: add
    * @param LoginSessionKey
    * @param 
    * Description: 
    */
    public function add_post() 
    {
        $Return['ResponseCode']='200';
        
        $Return['Message']= lang('page_success_delete');
        
        $Return['ServiceName']='admin_api/blog/create';
        
        $Return['Data']=array(); 

        $Data=$this->post_data;
        
        /* Validation - starts */
        $validation_rule        =    $this->form_validation->_config_rules['api/blog/create'];
        
        $this->form_validation->set_rules($validation_rule); 
        
        if($this->form_validation->run() == FALSE) 
        {
          $error = $this->form_validation->rest_first_error_string();         
          $Return['ResponseCode']   = 511;
          $Return['Message']        = $error; 
        } 
        else 
        { 
            if(in_array($this->RoleID, array(11,5,4,1)))
            {
                $insert_data['Title']          = !empty($Data['Title'])?$Data['Title']:'';
                
                $insert_data['Description']    = !empty($Data['Description'])?$Data['Description']:'';

                $insert_data['Status']         = !empty($Data['Status'])?$Data['Status']:'';  
                $insert_data['EntityType']         = !empty($Data['EntityType'])?$Data['EntityType']:'1';  

                $insert_data['UserID']         = $this->UserID; 

                $blog_id = $this->blog_model->add($insert_data);    

                if(!empty($Data['Media']))
                {
                    $this->blog_model->update_media($Data['Media'],$blog_id);
                }
                $Return['Message']        = lang('blog_add_success');
            }
            else
            {
                $Return['ResponseCode']   = 511;
                $Return['Message']        = lang('permission_denied');
            }
        }
        
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);     
    }

    /**
    * Function Name: edit
    * @param 
    * @param 
    * Description: 
    */
    public function edit_post() 
    {
        $Return['ResponseCode']='200';
        
        $Return['Message']= lang('page_success_delete');
        
        $Return['ServiceName']='admin_api/blog/edit';
        
        $Return['Data']=array(); 

        $Data=$this->post_data;
        
        /* Validation - starts */
        $validation_rule        =    $this->form_validation->_config_rules['api/blog/edit'];
        
        $this->form_validation->set_rules($validation_rule); 
        
        if($this->form_validation->run() == FALSE) 
        {
          $error = $this->form_validation->rest_first_error_string();         
          $Return['ResponseCode']   = 511;
          $Return['Message']        = $error; 
        } 
        else 
        { 
            if(in_array($this->RoleID, array(11,5,4,1)))
            {
                $update_data['Title']          = !empty($Data['Title'])?$Data['Title']:'';
                
                $update_data['Description']    = !empty($Data['Description'])?$Data['Description']:'';

                $update_data['Status']         = !empty($Data['Status'])?$Data['Status']:'';  

                $blog_id                       = get_detail_by_guid($Data['BlogGUID'],24);  

                $this->blog_model->edit($update_data,$blog_id);    

                if(!empty($Data['Media']))
                {
                    $this->blog_model->update_media($Data['Media'],$blog_id);
                }
                $Return['Message']        = lang('blog_update_success');
            }
            else
            {
                $Return['ResponseCode']   = 511;
                $Return['Message']        = lang('permission_denied');
            }
        }
        
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);     
    }

    /**
    * Function Name: delete
    * @param LoginSessionKey
    * @param 
    * Description: 
    */
    public function delete_post() 
    {
        $Return['ResponseCode'] ='200';
        $Return['Message']      = lang('blog_success_delete');
        $Return['ServiceName']  = 'admin_api/blog/delete';
        $Return['Data']         = array(); 
        $Data=$this->post_data;
        
        /* Validation - starts */
        $validation_rule        =    $this->form_validation->_config_rules['api/blog/delete'];
        
        $this->form_validation->set_rules($validation_rule); 
        
        if($this->form_validation->run() == FALSE) 
        {
          $error = $this->form_validation->rest_first_error_string();         
          $Return['ResponseCode']   = 511;
          $Return['Message']        = $error; 
        } 
        else 
        { 
            if(in_array($this->RoleID, array(5,4,1)))
            {
                $blog_id      = get_detail_by_guid($Data['BlogGUID'],24);  
                $status       = $this->blog_model->delete($blog_id);
            }
            elseif($this->RoleID==11)
            {
                $blog_detail      = get_detail_by_guid($Data['BlogGUID'],24,'BlogID,UserID,Status',2);
                if($blog_detail['UserID']==$this->UserID && $blog_detail['Status']=="DRAFT")
                {
                    $status       = $this->blog_model->delete($blog_id);    
                }
                else
                {
                    $Return['ResponseCode']   = 511;
                    $Return['Message']        = lang('operation_not_permitted');
                }
            }
        }
        
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);     
    }
}//End of file team.php