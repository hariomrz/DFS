<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Events extends Common_Controller {

    public $page_name = 'events';
    public $dashboard = 'Events';

    public function __construct() {
        parent::__construct();
        $this->data['pname']  = 'events';
        $this->data['sub_pname'] = '';
		$this->data['IsEvent'] = 1;
        $this->lang->load('event');
        if($this->settings_model->isDisabled(14)){
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout,$this->data);
            $this->output->_display();
            exit();
        }

        $this->load->model(array('users/login_model','flag_model','events/event_model'));
    }


    public function index($EventGUID=NULL) {
        $this->events();    
    }

   

    public function events($type = 'listEvent') {
        $this->data['title'] = 'Events Listing';
        $this->data['type'] = $type;
        
        $this->data['location']['City'] = '';
        $this->data['location']['Lat']  = '0';
        $this->data['location']['Lng']  = '0' ;
        
        /*
        $this->load->library('geoip');
        $ip_address = getRealIpAddr();
        if (CACHE_ENABLE) {
            $record = $this->cache->get('IP_Address' . $ip_address);
        }

        if(empty($record))
        {
            $record = $this->geoip->info($ip_address);
            if (CACHE_ENABLE) {
                $this->cache->save('IP_Address' . $ip_address, $record, CACHE_EXPIRATION);
            }
        }       

        if(isset($record->latitude) && !empty($record->latitude) && isset($record->longitude) && !empty($record->longitude))
        {
            $this->data['location']['City'] = $record->city;
            $this->data['location']['State'] = isset($record->state_name) ? $record->state_name : '';
            $this->data['location']['Country'] = isset($record->country_name) ? $record->country_name : '';
            $this->data['location']['CountryCode'] = isset($record->country_code) ?  $record->country_code : '';
            $this->data['location']['Lat'] = isset($record->latitude) ? $record->latitude : '0';
            $this->data['location']['Lng'] = isset($record->longitude) ? $record->longitude : '0' ;
        }
        */
        
        $this->data['content_view'] = 'events/EventList';        
        $Logged = $this->session->userdata('LoogedInData');        
        $this->data['auth'] = array('LoginSessionKey' => $Logged['LoginSessionKey'], 'UserGUID' => $Logged['UserGUID']);
        $this->load->view($this->layout, $this->data);
    }


    public function about($title_url = '', $event_guid = '', $activity_guid='') 
    {
        //Variables for wall
        $event_id       = $this->setTitleUrl($event_guid);
        
        $user_id        = $this->session->userdata('UserID');

        if ($event_id) {
            if(check_blocked_user($user_id, 14, $event_id)) {
                redirect(site_url('dashboard'));
            }
            
            //Check current user's permissions
            $permissions = checkPermission($user_id, 14, $event_id,'IsAccess');        
            if(!$permissions['IsAccess']) {
                $this->session->set_flashdata('errMsg', lang('permission_denied_page'));
                redirect(site_url('dashboard'));
            }

            $this->data['IsFriend'] = 0;
            $this->data['IsAdmin']  = 0;
            if($this->event_model->can_post_on_wall($event_id, $user_id)) {
                $this->data['IsFriend'] = 1;
            }
            
            if($this->event_model->get_member_role($event_id, $user_id, 'Admin')) {
                $this->data['IsAdmin'] = 1;
            }

            $this->load->model('notification_model');
            $this->notification_model->mark_notifications_as_read($user_id, $event_id, 'EVENT');

            $this->data['EntityID']         = $event_guid;
            $this->data['ModuleEntityGUID'] = $event_guid;            
            $this->data['ModuleID']         = 14;
            $this->data['Section']          = "members";
            $this->data['UserID']           = $this->event_model->getEventOwner($event_id);
            $this->data['title']            = isset($this->data['EventTitle']) ? $this->data['EventTitle'] : 'Event Details';
            $this->data['sub_pname']        = 'about';
            $this->data['ActivityGUID']     = '';
            $this->data['CoverImageState']  = get_cover_image_state($user_id, $event_id, 14);

            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'EVENT', $event_id);
            
            $Logged = $this->session->userdata('LoogedInData');
            $this->data['auth'] = array('LoginSessionKey' => $Logged['LoginSessionKey'], 'UserGUID' => $Logged['UserGUID'],"EventGUID"=>$event_guid);

            $this->data['content_view']     = 'events/ViewEventAbout';
            $this->load->view($this->layout, $this->data);
        } else {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }
    
    public function setTitleUrl($event_guid) {
        $event_id       = get_detail_by_guid($event_guid,14, 'EventID, EventGUID, Title', 2);
        
        $title_url      = '';
        $title          = '';
        if(!empty($event_id['EventID'])) {
            
            $title = $event_id['Title'];
            $title_url = $this->event_model->getViewEventUrl($event_id, $title, 1);
            
            $event_id = $event_id['EventID'];
        }
        
        $this->data['EventTitleUrl']    = $title_url;
        $this->data['EventTitle']    = $title;
        
        return $event_id;
    }
    
    public function checkSpecificEvent() {
        
    }

    /*
    | @Function to Access Event's Wall
    | @Params : event_guid(string), Type(string)
    */
    public function wall($title_url = '', $event_guid = '', $activity_guid='') 
    {
        //Variables for wall
        $event_id       = $this->setTitleUrl($event_guid);
        $user_id        = $this->session->userdata('UserID');
        
        if ($event_id) {
            if(check_blocked_user($user_id, 14, $event_id)) {
                redirect(site_url('dashboard'));
            }
            
            //Check current user's permissions
            $permissions = checkPermission($user_id, 14, $event_id,'IsAccess');        
            if(!$permissions['IsAccess'])
            {
                $this->session->set_flashdata('errMsg', lang('permission_denied_page'));
                redirect(site_url('dashboard'));
            }

            $this->load->model('notification_model');
            $this->notification_model->mark_notifications_as_read($user_id, $event_id, 'EVENT');

            // Merging permissions with preset data.
            $this->data = array_merge($this->data, $permissions);
                        
            $user_guid                      = get_guid_by_id($user_id, 3);
            $this->data['EventGUID']        = $event_guid;
            $this->data['UserGUID']         = $user_guid;
            $this->data['UserID']           = $this->event_model->getEventOwner($event_id);
            $this->data['title']            = 'Event Wall';
            $this->data['EventID']          = $event_id;
            $this->data['Section']          = "wall";
            $this->data['EntityID']         = $event_guid;
            $this->data['ModuleEntityGUID'] = $event_guid;            
            $this->data['ModuleEntityID'] = $event_id;
            $this->data['type']             = '';
            $this->data['ModuleID']         = '14';
            $this->data['IsEvent']          = '1';
            $this->data['ActivityGUID']     = $activity_guid;
            $this->data['sub_pname']            = 'wall';

            $this->data['CoverImageState']  = get_cover_image_state($user_id, $event_id, 14);

            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'EVENT', $event_id);
            
            $event_threshold_date = $this->event_model->get_threshold_date($event_guid);
            if(get_current_date('%Y-%m-%d %H:%i:%s') > $event_threshold_date)
            {
                $this->data['IsFriend'] = 0;
            }
                
            $Logged = $this->session->userdata('LoogedInData');
            $this->data['auth'] = array('LoginSessionKey' => $Logged['LoginSessionKey'], 'UserGUID' => $Logged['UserGUID'],"EventGUID"=>$event_guid);

            $this->data['content_view'] = 'events/ViewEvent';

            if(!empty($activity_guid))
            {
                $activity_id = get_detail_by_guid($activity_guid, 0, "ActivityID");
                if(!empty($activity_id))
                {
                    $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'EVENT_POST');
                } 
                $this->data['hidemedia'] = true;
            }
            
            $this->load->view($this->layout, $this->data);
        }
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }
    
    public function members($title_url = '', $event_guid = '',$type = 'ViewEventMembers')
    {
        //Variables for wall
        $event_id       = $this->setTitleUrl($event_guid);
        $user_id                = $this->session->userdata('UserID');
        if ($event_id) {
            if(check_blocked_user($user_id, 14, $event_id)) {
                redirect(site_url('dashboard'));
            }
            
            //Check current user's permissions
            $permissions = checkPermission($user_id, 14, $event_id,'IsAccess');        
            if(!$permissions['IsAccess'])
            {
                $this->session->set_flashdata('errMsg', lang('permission_denied_page'));
                redirect(site_url('dashboard'));
            }

            $this->data['IsFriend'] = 0;
            $this->data['IsAdmin']  = 0;
            if($this->event_model->can_post_on_wall($event_id, $user_id))
            {
                $this->data['IsFriend'] = 1;
            }
            
            if($this->event_model->get_member_role($event_id, $user_id, 'Admin'))
            {
                $this->data['IsAdmin'] = 1;
            }

            $this->load->model('notification_model');
            $this->notification_model->mark_notifications_as_read($user_id, $event_id, 'EVENT');

            $this->data['EntityID']         = $event_guid;
            $this->data['ModuleEntityGUID'] = $event_guid;
            $this->data['ModuleID']         = 14;
            $this->data['Section']          = "members";
            $this->data['UserID']           = $this->event_model->getEventOwner($event_id);
            $this->data['title']            = 'Event Members';
            $this->data['type']             = $type;
            $this->data['sub_pname']        = 'members';
            $this->data['ActivityGUID']     = '';
            $this->data['CoverImageState']  = get_cover_image_state($user_id, $event_id, 14);

            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'EVENT', $event_id);
            
            $Logged = $this->session->userdata('LoogedInData');
            $this->data['auth'] = array('LoginSessionKey' => $Logged['LoginSessionKey'], 'UserGUID' => $Logged['UserGUID'],"EventGUID"=>$event_guid);

            $this->data['content_view']     = 'events/ViewEventMembers';
            $this->load->view($this->layout, $this->data);
        }
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }

    public function media($title_url = '', $event_guid, $action='list', $album_guid='') 
    {
        $this->data['IsMedia']  = 1;
        $this->data['Active'] = 'media';
        $event_id       = $this->setTitleUrl($event_guid);
        $user_id        = $this->session->userdata('UserID');

        if ($event_id) 
        {
            if(check_blocked_user($user_id, 14, $event_id))
            {
                redirect(site_url('dashboard'));
            }

            //Check current user's permissions
            $this->data['no_permission'] = false;
            $permissions = checkPermission($user_id, 14, $event_id,'IsAccess');
            if(!$permissions['IsAccess'])
            {
                $this->session->set_flashdata('errMsg', lang('permission_denied_page'));
                redirect(site_url('dashboard'));
            }
            else
            {
                $this->load->model('notification_model');
                $this->notification_model->mark_notifications_as_read($user_id, $event_id, 'EVENT');

                // Merging permissions with preset data.
                $this->data = array_merge($this->data, $permissions);
                
                $this->data['albumMod']    = 'list';
                $this->data['moduleSection'] = 'event';
                //check page 

                if(($action=='create' || $action=='edit') && $this->data['IsAdmin']!=1)
                {
                    $this->data['no_permission'] = true;
                }
                
                if($action=='create')
                {
                    $this->data['albumMod']         = 'create';
                    $this->data['albumHeading']     = 'Create Album';
                    $this->data['albumAddMedia']    = 'Add media';
                    $status_id                      = 2;           
                }
                else if($action=='edit')
                {
                    $this->data['albumMod']         = 'edit';
                    $this->data['albumHeading']     = 'Edit Album';
                    $this->data['albumAddMedia']    = 'Add more media';
                    $status_id                      = 2;
                }
                else if($action!='list')
                {
                    $album_guid                     = $action;
                    $status_id                      = get_detail_by_guid($action, 13, "StatusID");
                    $this->data['albumMod']         = 'detail';
                }

                $this->data['AlbumGUID']        = $album_guid;
                $this->data['sub_pname']        = 'media';
                $user_guid                      =  get_guid_by_id($user_id, 3);
                $this->data['EventGUID']        = $event_guid;
                $this->data['sectionGUID']      = $event_guid;
                $this->data['UserGUID']         = $user_guid;
                $this->data['UserID']           = $this->event_model->getEventOwner($event_id);
                $this->data['title']            = 'Event Media';
                $this->data['EventID']          = $event_id;
                $this->data['EntityID']         = $event_guid;
                $this->data['ModuleEntityGUID'] = $event_guid;
                $this->data['ModuleID']         = 14;
                $this->data['ActivityGUID']     = '';
                if(!empty($album_guid))
                {
                    $activity_id = get_detail_by_guid($album_guid, 13, "ActivityID");
                    if(!empty($activity_id))
                    {
                        $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'ALBUM');
                    } 
                }    
            } 

            $this->data['CoverImageState']  = get_cover_image_state($user_id, $event_id, 14);

            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'EVENT', $event_id);
            
            $this->data['Section']              = "media";
            $Logged                             = $this->session->userdata('LoogedInData');
            $this->data['auth']                 = array('LoginSessionKey' => $Logged['LoginSessionKey'], 'UserGUID' => $Logged['UserGUID'],"EventGUID"=>$event_guid);
            //$this->group_page_name = 'media';
            $this->data['content_view']         = 'events/media';
            $this->load->view($this->layout, $this->data);
        }
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }    

    public function files($title_url = '', $event_guid, $action='list', $album_guid='') 
     {
        $this->data['IsMedia']  = 1;
        $this->data['Active'] = 'files';
        $this->data['isFileTab'] = true;
        $event_id       = $this->setTitleUrl($event_guid);
        $user_id        = $this->session->userdata('UserID');

        if ($event_id) 
        {
            if(check_blocked_user($user_id, 14, $event_id))
            {
                redirect(site_url('dashboard'));
            }

            //Check current user's permissions
            $this->data['no_permission'] = false;
            $permissions = checkPermission($user_id, 14, $event_id,'IsAccess');
            if(!$permissions['IsAccess'])
            {
                $this->data['no_permission'] = true;
            }
            else
            {
                $this->load->model('notification_model');
                $this->notification_model->mark_notifications_as_read($user_id, $event_id, 'EVENT');

                // Merging permissions with preset data.
                $this->data = array_merge($this->data, $permissions);
                
                $this->data['albumMod']    = 'list';
                $this->data['moduleSection'] = 'event';
                //check page 

                if(($action=='create' || $action=='edit') && $this->data['IsAdmin']!=1)
                {
                    $this->data['no_permission'] = true;
                }
                
                if($action=='create')
                {
                    $this->data['albumMod']         = 'create';
                    $this->data['albumHeading']     = 'Create Album';
                    $this->data['albumAddMedia']    = 'Add media';
                    $status_id                      = 2;           
                }
                else if($action=='edit')
                {
                    $this->data['albumMod']         = 'edit';
                    $this->data['albumHeading']     = 'Edit Album';
                    $this->data['albumAddMedia']    = 'Add more media';
                    $status_id                      = 2;
                }
                else if($action!='list')
                {
                    $album_guid                     = $action;
                    $status_id                      = get_detail_by_guid($action, 13, "StatusID");
                    $this->data['albumMod']         = 'detail';
                }

                $this->data['AlbumGUID']        = $album_guid;
                $this->data['pname']            = 'files';
                $user_guid                      =  get_guid_by_id($user_id, 3);
                $this->data['EventGUID']        = $event_guid;
                $this->data['sectionGUID']      = $event_guid;
                $this->data['UserGUID']         = $user_guid;
                $this->data['UserID']           = $this->event_model->getEventOwner($event_id);
                $this->data['title']            = 'Event Files';
                $this->data['EventID']          = $event_id;
                $this->data['EntityID']         = $event_guid;
                $this->data['ModuleEntityGUID'] = $event_guid;
                $this->data['ModuleID']         = 14;
                $this->data['ActivityGUID']     = '';
                if(!empty($album_guid))
                {
                    $activity_id = get_detail_by_guid($album_guid, 13, "ActivityID");
                    if(!empty($activity_id))
                    {
                        $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'ALBUM');
                    } 
                }    
            } 

            $this->data['CoverImageState']  = get_cover_image_state($user_id, $event_id, 14);

            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'EVENT', $event_id);
            
            $this->data['Section']              = "files";
            $Logged                             = $this->session->userdata('LoogedInData');
            $this->data['auth']                 = array('LoginSessionKey' => $Logged['LoginSessionKey'], 'UserGUID' => $Logged['UserGUID'],"EventGUID"=>$event_guid);
            //$this->group_page_name = 'media';
            $this->data['content_view']         = 'events/files';
            $this->load->view($this->layout, $this->data);
        }
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }
    
    public function links($title_url = '', $event_guid, $action='list', $album_guid='') {
        $this->data['Active'] = 'links';
        $this->data['isLinkTab'] = true;
        $event_id       = $this->setTitleUrl($event_guid);
        $user_id = $this->session->userdata('UserID');
        if ( !empty($event_id) ) {
            if(check_blocked_user($user_id, 14, $event_id))
            {
                redirect(site_url('dashboard'));
            }

            //Check current user's permissions
            $this->data['no_permission'] = false;
            $permissions = checkPermission($user_id, 14, $event_id,'IsAccess');
            if(!$permissions['IsAccess'])
            {
                $this->data['no_permission'] = true;
            }
            else
            {
                $this->load->model('notification_model');
                $this->notification_model->mark_notifications_as_read($user_id, $event_id, 'EVENT');

                // Merging permissions with preset data.
                $this->data = array_merge($this->data, $permissions);
                $this->data['moduleSection'] = 'event';
                $this->data['pname'] = 'links';
                $user_guid =  get_guid_by_id($user_id, 3);
                $this->data['EventGUID']        = $event_guid;
                $this->data['sectionGUID']      = $event_guid;
                $this->data['UserGUID']         = $user_guid;
                $this->data['UserID']           = $this->event_model->getEventOwner($event_id);
                $this->data['title']            = 'Event Links';
                $this->data['EventID']          = $event_id;
                $this->data['EntityID']         = $event_guid;
                $this->data['ModuleEntityGUID'] = $event_guid;
                $this->data['ModuleID']         = 14;   
                $this->data['ActivityGUID']     = '';
            } 

            $this->data['CoverImageState']  = get_cover_image_state($user_id, $event_id, 14);

            $this->load->model('subscribe_model');
            $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'EVENT', $event_id);
            
            $this->data['Section'] = "files";
            $Logged = $this->session->userdata('LoogedInData');
            $this->data['auth'] = array('LoginSessionKey' => $Logged['LoginSessionKey'], 'UserGUID' => $Logged['UserGUID'],"EventGUID"=>$event_guid);
            $this->data['content_view'] = 'events/links';
            $this->load->view($this->layout, $this->data);
        }
        else 
        {
            $this->data['title'] = '404 Page Not Found';        
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }
}