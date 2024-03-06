<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Group Controller class for create/update/list group
 * @package    Group
 * @author     Vinfotech Team
 * @version    1.0
 */

class Group extends Common_Controller {

    public $page_name = 'group';
    public $dashboard = 'group';

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function __construct() {
        parent::__construct();
        $this->data['IsGroup']  = 1;
        $this->data['pname']  = 'groups';
        $this->data['IsBlocked'] = 0;
        if($this->settings_model->isDisabled(1)){
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout,$this->data);
            $this->output->_display();
            exit();
        }
        $this->load->model(array('flag_model','group/group_model','notification_model'));
    }

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function index($group_title_url = '', $group_id = null) {    
        
        if($group_id === null) {
            $group_id = $group_title_url;
        }
        
        if($group_id)
        {
            $group_details = $this->get_group_details($group_id);

            if(!empty($group_details['GroupGUID']))
            {
                $group_title_url = $this->data['GroupNameTitle'];
                $landing_page           = $group_details['LandingPage'];
                if($landing_page == 'Member')
                {
                    $this->members($group_title_url, $group_id, $group_details);
                }
                else if($landing_page == 'Media')
                {
                    $this->media($group_title_url,  $group_id, 'list', '', $group_details);
                }
                else if($landing_page == 'Files')
                {
                    $this->files($group_title_url,  $group_id, $group_details);
                }
                else if($landing_page == 'Links')
                {
                    $this->links($group_title_url,  $group_id, $group_details);
                }
                else
                {
                    $this->wall($group_title_url,  $group_id, '', '', $group_details);
                }
            }
            else
            {
                $this->data['title'] = '404 Page Not Found';        
                $this->data['content_view'] = '404-page';
                $this->load->view($this->layout, $this->data);
            }            
        }
        else
        {
            $this->group();
        }
    }

    public function wall($group_title_url, $group_id, $is_activity='', $activity_guid='', $group_details='') 
    {
        if(empty($group_details))
        {
            $group_details = $this->get_group_details($group_id);
        }
        
        if($group_details)
        {
            $group_id               = $group_details['GroupID'];
            $group_guid             = $group_details['GroupGUID'];
            $is_public              = $group_details['IsPublic'];
            $created_by             = $group_details['CreatedBy']; 
            $landing_page           = $group_details['LandingPage']; 
            $user_id                = $this->session->userdata('UserID');   

            $permissions =check_group_permissions($user_id, $group_id, FALSE);
            if($permissions['IsAccess'])
            {
            
                $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);

                $this->data['UserGUID']         = get_detail_by_id($user_id, 3, "UserGUID");
                $this->data['UserID']           = $created_by;        
                            
                $this->data['type']             = '';
                $this->data['ModuleID']         = '1';
                $this->data['ModuleEntityID']   = $group_id;        
                $this->data['ModuleEntityGUID'] = $group_guid;                   
                $this->data['IsGroup']          = '1';
                $this->data['ActivityGUID']     = $activity_guid;   
                $this->data['LandingPage']      = $landing_page;          

                if(!empty($activity_guid))
                {
                    $activity_id = get_detail_by_guid($activity_guid, 0, "ActivityID");
                    if(!empty($activity_id))
                    {
                        $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'GROUP_POST');
                    }
                    $this->data['hidemedia'] = true; 
                }
                
                $this->data['title']            = 'Group Wall';
                $this->data['content_view']     = 'groups/group_wall';
                $this->data['pname']            = 'wall';
                $this->notification_model->mark_notifications_as_read($user_id, $group_id, 'GROUP');            

                $this->load->view($this->layout, $this->data);
            }
            else
            {
                $this->data['no_permission'] = true;
                $this->session->set_flashdata('errMsg', lang('permission_denied_page'));
                redirect(site_url());               
            }    
        }
        else
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        
    }

    public function article($group_title_url, $group_id, $group_details='') 
    {
        if(empty($group_details))
        {
            $group_details = $this->get_group_details($group_id);    
        }
        
        if($group_details)
        {
            $group_id               = $group_details['GroupID'];
            $group_guid             = $group_details['GroupGUID'];
            $is_public              = $group_details['IsPublic'];
            $created_by             = $group_details['CreatedBy'];
            $landing_page           = $group_details['LandingPage']; 
            $user_id                = $this->session->userdata('UserID');

            $permissions =check_group_permissions($user_id, $group_id, FALSE);
            if($permissions['IsAccess'])
            {
                $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
                $this->data['UserGUID']         = get_detail_by_id($user_id, 3, "UserGUID");
                $this->data['UserID']           = $created_by;        
                            
                $this->data['type']             = '';
                $this->data['ModuleID']         = '1';
                $this->data['ModuleEntityID']   = $group_id;        
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['GroupGUID'] = $group_guid;
                $this->data['LandingPage']      = $landing_page;                    
                $this->data['IsGroup']          = '1';                    
                           
                $this->data['title']            = 'Group Article';            
                $this->data['content_view']     = 'groups/wiki';
                $this->data['pname']            = 'wiki';
                $this->data['CanCreateWiki']    = can_create_wiki($user_id,1,$group_id);
                $this->notification_model->mark_notifications_as_read($user_id, $group_id, 'GROUP_MEMBER');

                $this->load->view($this->layout, $this->data);
            }
            else
            {
                $this->data['title'] = '404 Page Not Found';        
                $this->data['content_view'] = '404-page';
                $this->load->view($this->layout, $this->data);
            }
        }
        else
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        
    }

    public function members($group_title_url, $group_id, $group_details='') 
    {
        if(empty($group_details))
        {
            $group_details = $this->get_group_details($group_id);
        }
        
        if($group_details)
        {
            $group_id               = $group_details['GroupID'];
            $group_guid             = $group_details['GroupGUID'];
            $is_public              = $group_details['IsPublic'];
            $created_by             = $group_details['CreatedBy'];
            $landing_page           = $group_details['LandingPage']; 
            $user_id                = $this->session->userdata('UserID');   
            $permissions =check_group_permissions($user_id, $group_id, FALSE);
            if($permissions['IsAccess'])
            {
                $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
                $this->data['UserGUID']         = get_detail_by_id($user_id, 3, "UserGUID");
                $this->data['UserID']           = $created_by;        
                            
                $this->data['type']             = '';
                $this->data['ModuleID']         = '1';
                $this->data['ModuleEntityID']   = $group_id;        
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['GroupGUID'] = $group_guid;
                $this->data['LandingPage']      = $landing_page;                    
                $this->data['IsGroup']          = '1';                    
                           
                $this->data['title']            = 'Group Members';            
                $this->data['content_view']     = 'groups/group_member';
                $this->data['pname']            = 'members';
                $this->notification_model->mark_notifications_as_read($user_id, $group_id, 'GROUP_MEMBER');

                $this->load->view($this->layout, $this->data);
            }
            else
            {
                $this->data['title'] = '404 Page Not Found';        
                $this->data['content_view'] = '404-page';
                $this->load->view($this->layout, $this->data);
            }
        }
        else
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        
    }

    public function event($group_title_url, $group_id, $group_details='') 
    {
        if($this->settings_model->isDisabled(14)){ // Check if event module is disabled
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout,$this->data);
            $this->output->_display();
            exit();
        }
        
        if(empty($group_details))
        {
            $group_details = $this->get_group_details($group_id);
        }
        
        if($group_details)
        {
            $group_id               = $group_details['GroupID'];
            $group_guid             = $group_details['GroupGUID'];
            $is_public              = $group_details['IsPublic'];
            $created_by             = $group_details['CreatedBy'];
            $landing_page           = $group_details['LandingPage']; 
            $user_id                = $this->session->userdata('UserID');   
            $permissions =check_group_permissions($user_id, $group_id, FALSE);
            if($permissions['IsAccess'])
            {
                $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
                $this->data['UserGUID']         = get_detail_by_id($user_id, 3, "UserGUID");
                $this->data['UserID']           = $created_by;        
                            
                $this->data['type']             = '';
                $this->data['ModuleID']         = '1';
                $this->data['ModuleEntityID']   = $group_id;        
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['GroupGUID'] = $group_guid;
                $this->data['LandingPage']      = $landing_page;                    
                $this->data['IsGroup']          = '1';                    
                           
                $this->data['title']            = 'Group Events';            
                $this->data['content_view']     = 'groups/group_event';
                $this->data['pname']            = 'event';
                //$this->notification_model->mark_notifications_as_read($user_id, $group_id, 'GROUP_MEMBER');

                $this->load->view($this->layout, $this->data);
            }
            else
            {
                $this->data['title'] = '404 Page Not Found';        
                $this->data['content_view'] = '404-page';
                $this->load->view($this->layout, $this->data);
            }
        }
        else
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        
    }

    public function media($group_title_url, $group_id, $action='list', $album_guid='', $group_details='') 
    {
        if(empty($group_details))
        {
            $group_details = $this->get_group_details($group_id);
        }
        if($group_details)
        {
            $group_id               = $group_details['GroupID'];
            $group_guid             = $group_details['GroupGUID'];
            $is_public              = $group_details['IsPublic'];
            $created_by             = $group_details['CreatedBy']; 
            $landing_page           = $group_details['LandingPage']; 
            $user_id                = $this->session->userdata('UserID');   
                       
            $permissions =check_group_permissions($user_id, $group_id, FALSE);
            if($permissions['IsAccess'])
            {
                $this->data = array_merge($this->data, $permissions); 
                $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
                $this->data['UserGUID']         = get_detail_by_id($user_id, 3, "UserGUID");
                $this->data['UserID']           = $created_by;        
                            
                $this->data['type']             = '';
                $this->data['ModuleID']         = '1';
                $this->data['ModuleEntityID']   = $group_id;        
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['LandingPage']      = $landing_page;                    
                $this->data['IsGroup']          = '1';
                
                $this->data['IsMedia']  = 1;
                $this->data['Active']   = 'media'; 
                $this->data['albumMod']    = 'list';
                $this->data['moduleSection'] = 'group';
                
                if(($action=='create' || $action=='edit') && !$permissions['IsAdmin'])
                {
                    $this->data['no_permission'] = true;
                }     
                
                if($action=='create' && $permissions['IsAdmin'])
                {
                    $this->data['albumMod']    = 'create';
                    $this->data['albumHeading']    = 'Create Album';
                    $this->data['albumAddMedia']    = 'Add media';
                    $status_id= 2;           
                }
                else if($action=='edit' && $permissions['IsAdmin'])
                {
                    $this->data['albumMod']    = 'edit';
                    $this->data['albumHeading']    = 'Edit Album';
                    $this->data['albumAddMedia']    = 'Add more media';
                    $status_id= 2;
                }
                else if($action!='list')
                {
                    $album_guid = $action;
                    $status_id = get_detail_by_guid($action, 13, "StatusID");
                    $this->data['albumMod']    = 'detail';
                }

                //var_dump($this->data['no_permission']);die;
                $this->data['AlbumGUID']    = $album_guid;                
                $this->data['sectionGUID']  = $group_guid;               
                $this->data['title']        = 'Group Media';
                $this->data['pname']        = 'media';
                $this->data['content_view'] = 'groups/media';

                $this->notification_model->mark_notifications_as_read($user_id, $group_id, 'GROUP_MEMBER');

                if(!empty($album_guid))
                {
                    $activity_id = get_detail_by_guid($album_guid, 13, "ActivityID");
                    if(!empty($activity_id))
                    {
                        $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'ALBUM');
                    } 
                }            

                $this->load->view($this->layout, $this->data);
            }
            else
            {
                $this->data['no_permission'] = true;
                $this->session->set_flashdata('errMsg', lang('permission_denied_page'));
                redirect(site_url());
            }
        }
        else
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        
    }

    public function files($group_title_url, $group_id, $group_details='') 
    {
        if(empty($group_details))
        {
            $group_details = $this->get_group_details($group_id);
        }
        
        if($group_details)
        {
            $group_id               = $group_details['GroupID'];
            $group_guid             = $group_details['GroupGUID'];
            $is_public              = $group_details['IsPublic'];
            $created_by             = $group_details['CreatedBy']; 
            $landing_page           = $group_details['LandingPage']; 
            $user_id                = $this->session->userdata('UserID');   
            $permissions =check_group_permissions($user_id, $group_id, FALSE);
            if($permissions['IsAccess'])
            {
                $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
                $this->data['UserGUID']         = get_detail_by_id($user_id, 3, "UserGUID");
                $this->data['UserID']           = $created_by;        
                            
                $this->data['type']             = '';
                $this->data['ModuleID']         = '1';
                $this->data['ModuleEntityID']   = $group_id;        
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['LandingPage']      = $landing_page;                    
                $this->data['IsGroup']          = '1';
                
                $this->data['Active']           = 'files';
                $this->data['isFileTab']        = true;
                $this->data['pname']            = 'files';                
                $this->data['title']            = 'Group File';
                $this->data['content_view']     = 'groups/files';            

                $this->load->view($this->layout, $this->data);
            }
            else
            {
                $this->data['no_permission'] = true;
                $this->session->set_flashdata('errMsg', lang('permission_denied_page'));
                redirect(site_url());
            }
        }
        else
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        
    }

    public function links($group_title_url, $group_id, $group_details='') 
    {
        if(empty($group_details))
        {
            $group_details = $this->get_group_details($group_id);
        }
        
        if($group_details)
        {
            $group_id               = $group_details['GroupID'];
            $group_guid             = $group_details['GroupGUID'];
            $is_public              = $group_details['IsPublic'];
            $created_by             = $group_details['CreatedBy']; 
            $landing_page           = $group_details['LandingPage']; 
            $user_id                = $this->session->userdata('UserID');   
            
            $permissions =check_group_permissions($user_id, $group_id, FALSE);
            if($permissions['IsAccess'])
            {
                $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
                $this->data['UserGUID']         = get_detail_by_id($user_id, 3, "UserGUID");
                $this->data['UserID']           = $created_by;        
                            
                $this->data['type']             = '';
                $this->data['ModuleID']         = '1';
                $this->data['ModuleEntityID']   = $group_id;        
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['LandingPage']      = $landing_page;                    
                $this->data['IsGroup']          = '1';
                
                $this->data['Active']   = 'links';
                $this->data['isLinkTab'] = true;
                $this->data['moduleSection'] = 'group';
                $this->data['pname']        = 'links';
                $this->data['title']        = 'Group Link';
                $this->data['content_view'] = 'groups/links';                       

                $this->load->view($this->layout, $this->data);
            }
            else
            {
                $this->data['no_permission'] = true;
                $this->session->set_flashdata('errMsg', lang('permission_denied_page'));
                redirect(site_url());
            }
        }
        else
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        
    }

    public function myzone($type = 'dashboard') {
        $this->group('myzone');
    }

    public function group($type = 'dashboard') {
        $this->data['title'] = 'Groups';
        $this->data['type'] = $type;
        $this->data['content_view'] = 'groups/index';
        $this->load->view($this->layout, $this->data);
    }

    public function settings($group_title_url, $group_id, $group_details="") 
    {
        if(empty($group_details))
        {            
            $group_details = $this->get_group_details($group_id);
        }

        $group_id               = $group_details['GroupID'];
        $IsPublic               = $group_details['IsPublic'];        
        $user_id                = $this->session->userdata('UserID');
        //$this->data['IsPublic'] = $IsPublic;        
        if ($group_id != '') 
        {
            $permissions =check_group_permissions($user_id, $group_id, FALSE);
            if($permissions['IsAccess'])
            {
                $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
                $user_guid                      =  get_guid_by_id($user_id, 3);
                $this->data['UserID']           = $this->group_model->get_group_owner($group_id);
                $this->data['UserGUID']         = $user_guid;
                $this->data['title']            = 'Group Setting';
                //$this->data['IsActiveMember']   = $IsActiveMember;
                $this->data['ModuleEntityID']   = $group_id;
                $this->data['type']             = '';
                $this->data['ModuleID']         = 1;
                $this->data['ModuleEntityGUID'] = $group_details['GroupGUID'];
                $this->data['content_view']     = 'groups/group_setting';
                $this->data['pname']            = 'setting';
                $this->load->view($this->layout, $this->data); 
            }
            else
            {
                $this->data['no_permission'] = true;
                $this->session->set_flashdata('errMsg', lang('permission_denied_page'));
                redirect(site_url());
            }           
        } 
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }


    public function group_member_setting($group_id,$group_details="") 
    {

        if(empty($group_details))
        {
            $group_details          = get_detail_by_id($group_id, 1, 'GroupID,GroupGUID,IsPublic,LandingPage,CreatedBy', 2);    
        }

        //$group_details          = get_detail_by_guid($group_guid, 1, 'GroupID,IsPublic', 2);
        $group_id               = $group_details['GroupID'];
        $IsPublic               = $group_details['IsPublic'];        
        $user_id                = $this->session->userdata('UserID');
        //$this->data['IsPublic'] = $IsPublic;        
        if ($group_id != '') 
        {            
            $permissions =check_group_permissions($user_id, $group_id, FALSE);
            if($permissions['IsAccess'])
            {
                $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
                $user_guid                      =  get_guid_by_id($user_id, 3);
                $this->data['UserID']           = $this->group_model->get_group_owner($group_id);
                $this->data['GroupGUID']        = $group_details['GroupGUID'];
                $this->data['ModuleEntityID']   = $group_id; 
                $this->data['UserGUID']         = $user_guid;
                $this->data['title']            = 'Group Setting';
                //$this->data['IsActiveMember']   = $IsActiveMember;
                $this->data['GroupID']          = $group_id;
                $this->data['type']             = '';
                $this->data['ModuleID']         = 1;
                $this->data['ModuleEntityGUID'] = $group_details['GroupGUID'];
                $this->data['content_view']     = 'groups/group_member_setting';
                $this->data['pname']            = 'setting';            
                $this->load->view($this->layout, $this->data);
            }
            else 
            {
                $this->data['title'] = '404 Page Not Found';        
                $this->data['content_view'] = '404-page';
                $this->load->view($this->layout, $this->data);
            }
            
        } 
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }

    public function media_old($group_guid, $action='list', $album_guid='') 
    {
        $this->data['IsMedia']  = 1;
        $this->data['Active']   = 'media';
        $group_details          = get_detail_by_guid($group_guid,1,'GroupID,IsPublic',2);
        $group_id               = $group_details['GroupID'];
        $IsPublic               = $group_details['IsPublic'];
        $user_id                = $this->session->userdata('UserID');
        $this->data['IsPublic'] = $IsPublic;
        if ($group_id != '') 
        {
            if(check_blocked_user($user_id, 1, $group_id))
            {
                redirect(site_url('dashboard'));
            }
            
            //Check current user's permissions
            $this->data['no_permission'] = false;
            $permissions = checkPermission($user_id, 1, $group_id,'IsAccess');

            if(!$permissions['IsAccess'])
            {
                $this->data['no_permission'] = true;
                //redirect('/');
            }
            else
            {
                $this->data['IsInviteSent'] = $this->group_model->is_sent_invite($group_id,$user_id);
                // Merging permissions with preset data.
                $this->data = array_merge($this->data, $permissions); 
                $this->data['albumMod']    = 'list';
                $this->data['moduleSection'] = 'group';
                
                if(($action=='create' || $action=='edit') && $this->data['IsAdmin']!=1)
                {
                    $this->data['no_permission'] = true;
                }     
                
                if($action=='create' && $permissions['IsAdmin']==1)
                {
                    $this->data['albumMod']    = 'create';
                    $this->data['albumHeading']    = 'Create Album';
                    $this->data['albumAddMedia']    = 'Add media';
                    $status_id= 2;           
                }
                else if($action=='edit' && $permissions['IsAdmin']==1)
                {
                    $this->data['albumMod']    = 'edit';
                    $this->data['albumHeading']    = 'Edit Album';
                    $this->data['albumAddMedia']    = 'Add more media';
                    $status_id= 2;
                }
                else if($action!='list')
                {
                    $album_guid = $action;
                    $status_id = get_detail_by_guid($action, 13, "StatusID");
                    $this->data['albumMod']    = 'detail';
                }

                //var_dump($this->data['no_permission']);die;
                $this->data['AlbumGUID']    = $album_guid;
                $this->data['pname']        = 'media';
                $user_guid                  =  get_guid_by_id($user_id, 3);
                $this->data['sectionGUID']  = $group_guid;
                $this->data['UserGUID']     = $user_guid;
                $this->data['UserID']       = $this->group_model->get_group_owner($group_id);
                $this->data['title']        = 'Media';
                //$this->data['IsActiveMember']  = checkPermission($user_id, 1, $group_id, 'IsActive');
                $this->data['ModuleEntityID'] = $group_id;
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['ModuleID']     = '1';

                $this->notification_model->mark_notifications_as_read($user_id, $group_id, 'GROUP_MEMBER');

                if(!empty($album_guid))
                {
                    $activity_id = get_detail_by_guid($album_guid, 13, "ActivityID");
                    if(!empty($activity_id))
                    {
                        $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'ALBUM');
                    } 
                }
            }

            $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
            
            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'GROUP', $group_id);
            
            $this->data['content_view'] = 'groups/media';
            $this->load->view($this->layout, $this->data);
        }
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }                
        
    }

    public function files_old($group_guid) 
    {
        $this->data['IsMedia']  = 1;
        $this->data['Active']   = 'files';
        $this->data['isFileTab'] = true;
        $group_details          = get_detail_by_guid($group_guid,1,'GroupID,IsPublic',2);
        $group_id               = $group_details['GroupID'];
        $IsPublic               = $group_details['IsPublic'];
        $user_id                = $this->session->userdata('UserID');
        $this->data['IsPublic'] = $IsPublic;
        if ($group_id != '') 
        {
            if(check_blocked_user($user_id, 1, $group_id))
            {
                redirect(site_url('dashboard'));
            }
            
            //Check current user's permissions
            $this->data['no_permission'] = false;
            $permissions = checkPermission($user_id, 1, $group_id,'IsAccess');

            if(!$permissions['IsAccess'])
            {
                $this->data['no_permission'] = true;
                //redirect('/');
            }
            else
            {
                $this->data['IsInviteSent'] = $this->group_model->is_sent_invite($group_id,$user_id);
                // Merging permissions with preset data.
                $this->data = array_merge($this->data, $permissions); 
                
                $this->data['pname']        = 'files';
                $user_guid                  =  get_guid_by_id($user_id, 3);
                $this->data['UserGUID']     = $user_guid;
                $this->data['UserID']       = $this->group_model->get_group_owner($group_id);
                $this->data['title']        = 'File';
                //$this->data['IsActiveMember']  = checkPermission($user_id, 1, $group_id, 'IsActive');
                $this->data['ModuleEntityID']      = $group_id;
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['ModuleID']     = '1';                
            }

            $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
            
            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'GROUP', $group_id);
            
            $this->data['content_view'] = 'groups/files';
            $this->load->view($this->layout, $this->data);
        }
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }                
        
    }
    
    public function links_old($group_guid) 
    {
        $this->data['Active']   = 'links';
        $this->data['isLinkTab'] = true;
        $group_details          = get_detail_by_guid($group_guid,1,'GroupID,IsPublic',2);
        $group_id               = $group_details['GroupID'];
        $IsPublic               = $group_details['IsPublic'];
        $user_id                = $this->session->userdata('UserID');
        $this->data['IsPublic'] = $IsPublic;
        if ($group_id != '') 
        {
            if(check_blocked_user($user_id, 1, $group_id))
            {
                redirect(site_url('dashboard'));
            }
            
            //Check current user's permissions
            $this->data['no_permission'] = false;
            $permissions = checkPermission($user_id, 1, $group_id,'IsAccess');

            if(!$permissions['IsAccess'])
            {
                $this->data['no_permission'] = true;
                //redirect('/');
            }
            else
            {
                $this->data['IsInviteSent'] = $this->group_model->is_sent_invite($group_id,$user_id);
                // Merging permissions with preset data.
                $this->data = array_merge($this->data, $permissions); 
                $this->data['moduleSection'] = 'group';

                $this->data['pname']        = 'links';
                $user_guid                  =  get_guid_by_id($user_id, 3);
                $this->data['UserGUID']     = $user_guid;
                $this->data['UserID']       = $this->group_model->get_group_owner($group_id);
                $this->data['title']        = 'Link';
                $this->data['ModuleEntityID']      = $group_id;
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['ModuleID']     = '1';
            }

            $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
            
            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'GROUP', $group_id);
            
            $this->data['content_view'] = 'groups/links';
            $this->load->view($this->layout, $this->data);
        }
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }                
        
    }

    public function invite_member($GroupId) 
    {
        $GroupD    = get_detail_by_id($GroupId,1,'IsPublic',2);
        $IsPublic = $GroupD['IsPublic'];
        $this->data['IsPublic'] = $IsPublic;
        if(!is_entity_exists(1,$GroupId)){
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        } else {
            $UserID     = $this->session->userdata('UserID');
            if(checkPermission($UserID, 1, $GroupId, 'IsBlocked'))
            {
                $this->data['IsBlocked'] = 1;
            }
            $res = $this->login_model->check_member($GroupId, $UserID);
            if ($res != 1) {
                redirect('group');
            }
            $this->data['CanReport']    = '1';
            $this->data['GroupOwner']   = '0';
            if($this->flag_model->is_flagged($UserID, $GroupId, 'Group')){
                $this->data['CanReport'] = '0';
            }
            $this->data['IsInviteSent'] = $this->group_model->is_sent_invite($GroupId,$UserID);
            if($this->group_model->is_admin($UserID, $GroupId)){
                $this->data['GroupOwner'] = '1';
                $this->data['CanReport'] = '0';
            }

            $this->data['title'] = 'Invite Members';
            $this->data['ModuleEntityID'] = $GroupId;
            $this->data['type'] = '';
            $this->data['content_view'] = 'groups/invite_member';
            $this->load->view($this->layout, $this->data);
        }
    }
   
    public function about($group_guid, $action='list', $album_guid='') 
    {
        $this->data['IsMedia']  = 1;
        $this->data['Active']   = 'media';
        $group_details          = get_detail_by_guid($group_guid,1,'GroupID,IsPublic',2);
        $group_id               = $group_details['GroupID'];
        $IsPublic               = $group_details['IsPublic'];
        $user_id                = $this->session->userdata('UserID');
        $this->data['IsPublic'] = $IsPublic;
        if ($group_id != '') 
        {
            if(check_blocked_user($user_id, 1, $group_id))
            {
                redirect(site_url('dashboard'));
            }
            
            //Check current user's permissions
            $this->data['no_permission'] = false;
            $permissions = checkPermission($user_id, 1, $group_id,'IsAccess');

            if(!$permissions['IsAccess'])
            {
                $this->data['no_permission'] = true;
                //redirect('/');
            }
            else
            {
                $this->data['IsInviteSent'] = $this->group_model->is_sent_invite($group_id,$user_id);
                // Merging permissions with preset data.
                $this->data = array_merge($this->data, $permissions); 
                $this->data['moduleSection'] = 'group';
                
                if($this->data['IsAdmin']!=1)
                {
                    $this->data['no_permission'] = true;
                }     

                //var_dump($this->data['no_permission']);die;
                $this->data['pname']        = 'about';
                $user_guid                  =  get_guid_by_id($user_id, 3);
                $this->data['sectionGUID']  = $group_guid;
                $this->data['UserGUID']     = $user_guid;
                $this->data['UserID']       = $this->group_model->get_group_owner($group_id);
                $this->data['title']        = 'About';
                //$this->data['IsActiveMember']  = checkPermission($user_id, 1, $group_id, 'IsActive');
                $this->data['ModuleEntityID']      = $group_id;
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['ModuleID']     = '1';

                $this->notification_model->mark_notifications_as_read($user_id, $group_id, 'GROUP_MEMBER');

                if(!empty($album_guid))
                {
                    $activity_id = get_detail_by_guid($album_guid, 13, "ActivityID");
                    if(!empty($activity_id))
                    {
                        $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'ALBUM');
                    } 
                }
            }
            
            $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
            
            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'GROUP', $group_id);
            
            $this->data['content_view'] = 'groups/about';
            $this->load->view($this->layout, $this->data);
        }
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }                
        
    }
    public function endorsment($group_guid, $action='list', $album_guid='') 
    {
        $this->data['IsMedia']  = 1;
        $this->data['Active']   = 'media';
        $group_details          = get_detail_by_guid($group_guid,1,'GroupID,IsPublic',2);
        $group_id               = $group_details['GroupID'];
        $IsPublic               = $group_details['IsPublic'];
        $user_id                = $this->session->userdata('UserID');
        $this->data['IsPublic'] = $IsPublic;
        if ($group_id != '') 
        {
            if(check_blocked_user($user_id, 1, $group_id))
            {
                redirect(site_url('dashboard'));
            }
            
            //Check current user's permissions
            $this->data['no_permission'] = false;
            $permissions = checkPermission($user_id, 1, $group_id,'IsAccess');

            if(!$permissions['IsAccess'])
            {
                $this->data['no_permission'] = true;
                //redirect('/');
            }
            else
            {
                $this->data['IsInviteSent'] = $this->group_model->is_sent_invite($group_id,$user_id);
                // Merging permissions with preset data.
                $this->data = array_merge($this->data, $permissions); 
                $this->data['moduleSection'] = 'group';
                
                if($this->data['IsAdmin']!=1)
                {
                    $this->data['no_permission'] = true;
                }     

                //var_dump($this->data['no_permission']);die;
                $this->data['pname']        = 'about';
                $user_guid                  =  get_guid_by_id($user_id, 3);
                $this->data['sectionGUID']  = $group_guid;
                $this->data['UserGUID']     = $user_guid;
                $this->data['UserID']       = $this->group_model->get_group_owner($group_id);
                $this->data['title']        = 'About';
                //$this->data['IsActiveMember']  = checkPermission($user_id, 1, $group_id, 'IsActive');
                $this->data['ModuleEntityID']      = $group_id;
                $this->data['ModuleEntityGUID'] = $group_guid;
                $this->data['ModuleID']     = '1';

                $this->notification_model->mark_notifications_as_read($user_id, $group_id, 'GROUP_MEMBER');

                if(!empty($album_guid))
                {
                    $activity_id = get_detail_by_guid($album_guid, 13, "ActivityID");
                    if(!empty($activity_id))
                    {
                        $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'ALBUM');
                    } 
                }
            }
            
            $this->data['CoverImageState']  = get_cover_image_state($user_id, $group_id, 1);
            
            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'GROUP', $group_id);
            
            $this->data['content_view'] = 'groups/endorsment';
            $this->load->view($this->layout, $this->data);
        }
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }                
        
    }

    /* public function discover_categories($type = 'dashboard') {

        $this->data['title'] = 'Discover';
        $this->data['type'] = $type;
        $this->data['pname']        = 'discover';
        $this->data['content_view'] = 'groups/discover';
        $this->load->view($this->layout, $this->data);
    }
     * 
     */
    
    public function get_group_details($group_id) {
        $this->UserID  = $this->session->userdata('UserID');   
        
        $group_details = $this->group_model->get_group_details_by_id($group_id);           
        $this->data['GroupNameTitle'] = isset($group_details['GroupNameTitle']) ? $group_details['GroupNameTitle'] : '';
                        
        return $group_details;
        
    }


}