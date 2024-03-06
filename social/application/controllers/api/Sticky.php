<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sticky extends Common_API_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $this->load->model(array('sticky/sticky_model', 'pages/page_model', 'users/user_model', 'group/group_model', 'media/media_model', 'users/friend_model', 'activity/activity_model', 'favourite_model', 'subscribe_model', 'notification_model'));
    }

    /**
     * Function Name: index
     * @param ProfileID,PageNomPageSize,ActivityTypeID,EntityID,WallType,ActivityGuID,AllActivity,LoginSessionKey
     * Description: Get list of activity according to input conditions
     */
    public function index_post()
    {
        //default api for sticky
        $this->get_all_sticky_post();
    }

    /**
     * [get_sticky_by_me used to get all sticky posts        
     * @return [JSON] [JSON Object]
     */
    public function get_sticky_by_me_post()
    {
        $this->get_all_sticky_post(1);
    }   
    
    /**
     * [get_sticky_by_others used to get all sticky posts        
     * @return [JSON] [JSON Object]
     */
    public function get_sticky_by_others_post()
    {
        $this->get_all_sticky_post(2);
    } 

    /**
     * [get_all_sticky used to get all sticky posts        
     * @return [JSON] [JSON Object]
     */
    public function get_all_sticky_post($sticky_by='0')
    { 
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if(isset($data))
        {
            $data['UserID'] = $this->UserID;
            $role_id = isset($this->RoleID) ? $this->RoleID : '';
            $data['ModuleID'] = isset($data['ModuleID']) ? $data['ModuleID'] : '3';
            $data['ModuleEntityGUID'] = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
            $data['ModuleEntityID'] = (isset($data['ModuleEntityGUID']) & !empty($data['ModuleEntityGUID'])) ? get_detail_by_guid($data['ModuleEntityGUID'], $data['ModuleID']) : '';
            $data['IncludeGroupSticky'] = isset($data['IncludeGroupSticky']) ? $data['IncludeGroupSticky'] : '';
            $data['PageNo'] = (isset($data['PageNo']) && $data['PageNo'] > 0) ? $data['PageNo'] : '1';
            $data['PageSize'] = isset($data['PageSize']) ? $data['PageSize'] : '20'; 

            //set user details            
            $this->user_model->set_user_profile_url($data['UserID']);
            $this->activity_model->set_block_user_list($data['ModuleEntityID'], $data['ModuleID']);            
            $this->user_model->set_friend_followers_list($data['UserID']);
            $this->group_model->set_user_group_list($data['UserID']);
            $this->activity_model->set_user_tagged($data['UserID']);            
            $this->subscribe_model->set_user_subscribed($data['UserID']);            
            $this->favourite_model->set_user_favourite($data['UserID']);            
            $this->flag_model->set_user_flagged($data['UserID']); 
            $this->activity_model->set_user_activity_archive($data['UserID']); 
            
            $this->privacy_model->set_privacy_options($data['UserID']);
            $this->event_model->set_user_joined_events($data['UserID']);
            //$this->page_model->set_user_pages_list($user_id);
            
            $this->page_model->set_feed_pages_condition($data['UserID']);  
            
            $FriendFollowersList=$this->user_model->get_friend_followers_list();
            if(!empty($FriendFollowersList)){
                $this->user_model->set_friends_of_friend_list($data['UserID'],$FriendFollowersList['Friends']);
            }

            $can_make_sticky = $this->sticky_model->can_make_sticky($data['UserID'], $role_id, $data['ModuleID'],$data['ModuleEntityID']);
            //check if the ModuleEntity is given
            if ($data['ModuleEntityID'])
            {            
                //Check Sticky Preference
                // $sticky_preference = get_detail_by_id($data['UserID'],3,'StickyPreference');
                $sticky_preference = $this->user_model->get_sticky_preference($data['UserID'],$sticky_by);
                if($data['IncludeGroupSticky'] != '')
                {
                    //Compare Sticky Preference with existing saved preference
                    if($data['IncludeGroupSticky'] == $sticky_preference)
                    {
                        $data['IncludeGroupSticky'] = $sticky_preference;                    
                    }
                    else
                    {
                        //Update Sticky Preference in User's Details
                        if($data['IncludeGroupSticky'])
                            $sticky_preference = '1';
                        else
                            $sticky_preference = '0';
                        $this->user_model->update_sticky_preference($data['UserID'],$sticky_preference,$sticky_by);
                    }                    
                }
                //get sticky posts
                $return['Data'] = $this->sticky_model->get_all_sticky($data['UserID'], $role_id, $data['ModuleEntityID'], $data['ModuleID'], $sticky_preference, $data['PageNo'], $data['PageSize'],'',$can_make_sticky,$sticky_by);
                $return['TotalRecords'] = (isset($return['Data']['TotalRecords']) && !empty($return['Data']['TotalRecords'])) ? $return['Data']['TotalRecords'] : 0;
                $return['IncludeGroupSticky'] = $sticky_preference;
                unset($return['Data']['TotalRecords']);
            } 
            else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('input_invalid_format');
            }
        }
        else
        {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }


    /**
     * [create_sticky will be used to make a post sticky on his/her wall/newsfeed    
     * @return [JSON] [JSON Object]
     */
    public function create_sticky_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        
        if (isset($data))
        {
            /* Validation - starts */
            if ($this->form_validation->run('api/create_sticky') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } 
            else /* Validation - ends */
            {
                $activity_guid = $data['ActivityGUID'];
                $activity_details = get_detail_by_guid($data['ActivityGUID'],0,"ActivityID,ActivityTypeID,UserID,ModuleID,ModuleEntityID,Privacy",2);
                $activity_id = $activity_details['ActivityID'];                
                $module_id = $activity_details['ModuleID'];//$data['ModuleID'];
                $module_entity_id = $activity_details['ModuleEntityID'];//get_detail_by_guid($data['ModuleEntityGUID'],$data['ModuleID']);
                $sticky_type = $data['StickyType'];                                
                $user_id = isset($this->UserID) ? $this->UserID : '';
                $role_id = isset($this->RoleID) ? $this->RoleID : '';
                $this->group_model->set_user_group_list($user_id);
                //check if user has the permission to make sticky for group            
                if($sticky_type==2 && $module_id==1)
                {     
                    //Check if logged in user can make sticky (Group Sticky) 
                    //$can_make_sticky = $this->sticky_model->can_make_sticky($user_id,$sticky_type, $module_id,$module_entity_id);
                    $permission =check_group_permissions($user_id, $module_entity_id, FALSE);
                    if($permission['IsAccess'] && ($permission['IsAdmin'] || $role_id ==1))
                    {
                        $group_members = $this->group_model->get_group_members_id_recursive($module_entity_id);
                        $group_admins = $this->group_model->get_group_members_id_recursive($module_entity_id,array(),array(),TRUE);
                        //create sticky for all group Members                               
                        $result = $this->sticky_model->create_sticky($user_id, $activity_id, $module_id, $module_entity_id, $sticky_type,$group_members,$role_id,$group_admins);
                        $return['Data'] = $result;      
                    }
                    else
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }                    
                }  
                else if($sticky_type==1)
                {   //Self sticky
                    switch ($module_id) 
                    {
                        case '1':
                            # code...
                            //now check if group is public
                            $is_public_group = get_detail_by_id($module_entity_id,$module_id,"IsPublic,StatusID",2);
                            if(isset($is_public_group['IsPublic'],$is_public_group['StatusID']) && $is_public_group['StatusID']=='2' && $is_public_group['IsPublic']=='1' )
                            {
                                $return['Data'] = $this->sticky_model->create_sticky($user_id, $activity_id, $module_id, $module_entity_id, $sticky_type,'',$role_id);                           
                            }
                            else
                            {
                                $group_list = $this->group_model->get_user_group_list();
                                if(in_array($module_entity_id, $group_list))
                                {
                                    $return['Data'] = $this->sticky_model->create_sticky($user_id, $activity_id, $module_id, $module_entity_id, $sticky_type,'',$role_id);                                  
                                }
                                else
                                {
                                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                    $return['Message'] = lang('permission_denied');//lang('input_invalid_format');       
                                }
                            }
                            break;                        
                        case '18':
                            # code...
                            $return['Data'] = $this->sticky_model->create_sticky($user_id, $activity_id, $module_id, $module_entity_id, $sticky_type,'',$role_id); 
                            break;
                        case '14':
                            # code...
                            $this->event_model->set_user_joined_events($user_id);
                            $event_list = $this->event_model->get_user_joined_events();
                            $event_list = explode(',', $event_list);
                            if(in_array($module_entity_id, $event_list))
                            {
                                $return['Data'] = $this->sticky_model->create_sticky($user_id, $activity_id, $module_id, $module_entity_id, $sticky_type,'',$role_id); 
                            }
                            else
                            {
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = lang('permission_denied');//lang('input_invalid_format');
                            }
                            break;                        
                        default:
                            # code...
                            $is_relation = $this->activity_model->isRelation($activity_details['UserID'],$user_id,true,$activity_guid);
                            if(in_array($activity_details['Privacy'], $is_relation))
                            {
                                $return['Data'] = $this->sticky_model->create_sticky($user_id, $activity_id, $module_id, $module_entity_id, $sticky_type,'',$role_id);                        
                            }
                            else
                            {
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = lang('permission_denied');//lang('input_invalid_format');
                            }
                            break;
                    }                    
                }
                else if($sticky_type==3)
                {   //Super Admin can create sticky for Everyone
                    //Check if logged in user can make sticky
                    $can_make_sticky = $this->sticky_model->can_make_sticky($user_id,$role_id);
                    if($can_make_sticky==1)
                    { 
                        //get all users for everyone
                        $everyone_userlist = $this->sticky_model->get_everyone_sticky_user($activity_guid,$activity_details['UserID'],$activity_details['Privacy'],$activity_details['ActivityTypeID'],$module_id,$module_entity_id);
                        if(!empty($everyone_userlist))
                        {  
                            $return['Data'] = $this->sticky_model->create_sticky($user_id, $activity_id, $module_id, $module_entity_id, $sticky_type,$everyone_userlist,$role_id);                        
                        } 
                    }
                    else
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                }              
            }
        } 
        else
        {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }

        $this->response($return);
    }

    /**
     * [remove_sticky used to remove sticky from sticky widget
     * @return [JSON] [JSON Object]
     */
    public function remove_sticky_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if (isset($data))
        {
            /* Validation - starts */
            if ($this->form_validation->run('api/remove_sticky') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else /* Validation - ends */
            {
                $activity_guid = $data['ActivityGUID'];
                $activity_details = get_detail_by_guid($data['ActivityGUID'],0,"ActivityID,ActivityTypeID,UserID,ModuleID,ModuleEntityID,Privacy",2);
                $activity_id = $activity_details['ActivityID'];                
                $module_id = $activity_details['ModuleID'];//$data['ModuleID'];
                $module_entity_id = $activity_details['ModuleEntityID'];//get_detail_by_guid($data['ModuleEntityGUID'],$data['ModuleID']);
                $sticky_type = $data['StickyType'];                
                $user_id = $this->UserID;
                $role_id = isset($this->RoleID) ? $this->RoleID : '';

                //check if user has the permission to remove sticky for group            
                if($sticky_type==2 && $module_id==1)
                {     
                    //Check if logged in user can make sticky
                    //$can_make_sticky = $this->sticky_model->can_make_sticky($user_id,$sticky_type, $module_id,$module_entity_id);
                    $permission =check_group_permissions($user_id, $module_entity_id, FALSE);
                    if($permission['IsAccess'] && ($permission['IsAdmin'] || $role_id ==1))
                    {
                        $is_admin = TRUE;
                        $group_members = $this->group_model->get_group_members_id_recursive($module_entity_id);
                        //remove sticky for all group Members                               
                        $result = $this->sticky_model->remove_sticky($user_id, $activity_id, $sticky_type, $group_members,$is_admin,$role_id,$module_id,$module_entity_id);
                        $return['Data'] = $result; 
                    }
                    else
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }                    
                }  
                else if($sticky_type==1)
                {   /* Self Sticky */
                    //Check if group admin or super admin is removing from self
                    $is_admin = FALSE;
                    if($module_id == 1)
                    {
                        $is_admin = $this->group_model->is_admin($user_id, $module_entity_id); 
                    }
                    else if($role_id == 1 )
                    {
                        $is_admin = TRUE;
                    }

                    $return['Data'] = $this->sticky_model->remove_sticky($user_id, $activity_id, $sticky_type, array(), $is_admin, $role_id,$module_id,$module_entity_id);
                }
                else if($sticky_type==3)
                {
                    //Check if logged in user can make sticky
                    $can_make_sticky = $this->sticky_model->can_make_sticky($user_id,$role_id);
                    if($can_make_sticky)
                    { 
                        //remove sticky for everyone
                        $return['Data'] = $this->sticky_model->remove_sticky($user_id, $activity_id, $sticky_type, array(), FALSE, $role_id, $module_id,$module_entity_id);                        
                    }
                    else
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                }                                
            }
        } else
        {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }

        $this->response($return);
    }
}

