<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Controller class for user related function
 * @category     Controller
 * @author       Vinfotech Team
 */

class User_profile extends Common_Controller
{

    public $page_name = 'userprofile';
    public $dashboard = '';
    public $meta_keywords = '';
    public $meta_description = '';
    public $title = 'VSocial';

    public function __construct()
    {
        parent::__construct();        
        $this->load->model(array('users/login_model', 'users/friend_model', 'users/user_model'));
    }

    /**
     * [entity_profile used to check if the url exist then show the respective entity type page]
     */
    public function entity_profile()
    {
        $profile_url = $this->uri->segment(1);
        $activity_guid = $this->uri->segment(3);
        $other_param = $this->uri->segment(4);
        
        $posted_profile_url = $profile_url;
        $profile_url = $this->get_profile_url($profile_url, $activity_guid);
        
        $this->data['OGImage'] = get_activity_image_url($activity_guid);
        if ($profile_url && $row = $this->login_model->check_profile_url($profile_url))
        {           
            $entity_type = $row['EntityType'];
            $entity_id = $row['EntityID'];
            $module = $this->uri->segment(2);
            $module = (!empty($module)) ? ucfirst($module) : 'Profile';
            if($activity_guid)
            {
                $this->db->data['HideMedia'] = 1;
            }
            if (empty($entity_id))
            {
                        redirect();
            }            
            if ($entity_type == 'User')
            {
                if (!empty($this->login_session_key))
                {
                    $this->load_user_module($entity_id, $module, $activity_guid, $other_param, $posted_profile_url);
                }
                else
                {
                    $this->load_user_module_guest($entity_id, $module, $activity_guid, $posted_profile_url);
                }
            }                
        }
        else
        {
            $this->data['title'] = '404 Page Not Found';
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }

    /**
     * [load_user_module description]
     * @param  [type] $user_id [User Id]
     * @param  [type] $module [Moduel name like, profile, friend, followers]
     */
    public function load_user_module($user_id, $module, $activity_guid = '', $other_param = '', $posted_profile_url = '')
    {
        if (is_entity_exists(3, $user_id, FALSE))
        {
            if (!empty($user_id))
            {
                $user_guid = get_detail_by_id($user_id, 3, 'UserGUID', 1);
                $this->data['CommentGUID']  = $this->input->get('cguid', TRUE);
                $this->data['title'] = $module;
                $this->data['Type'] = $module;
                $this->data['UID'] = $user_id;
                $this->data['UserID'] = $user_id;
                $this->data['EntityID'] = $user_guid;
                $this->data['ModuleID'] = '3';
                $this->data['ModuleEntityGUID'] = $user_guid;
                $this->data['ModuleEntityID'] = $user_id;
                $this->data['ActivityGUID'] = $activity_guid;
                $activity_id = '';
                $activity_user_id='';

                if ($module == 'Cover')
                {
                    $this->data['ActivityGUID'] = '';
                }
                $this->data['ShowRecentActivity'] = 0;
                $current_user_id = $this->session->userdata('UserID');
                $this->load->model('notification_model');
                if ($user_id != $current_user_id)
                {
                    $this->data['ShowRecentActivity'] = 1;
                    $this->data['wall_url'] = get_entity_url($user_id);
                    $this->notification_model->mark_notifications_as_read($current_user_id, $user_id, 'USER');
                }
                if (check_blocked_user($current_user_id, 3, $user_id))
                {
                    redirect(site_url('dashboard'));
                }

                $show_404 = false;
                $this->data['albumMod'] = 'list';
                if ($activity_guid)
                {
                    $show_404 = true;
                    $status_id = 0;

                    if ($module == 'Media')
                    {
                        if ($this->settings_model->isDisabled(13) && ($activity_guid == 'create' || $activity_guid == 'edit')) {
                            // If Album module is disable then return
                            $this->data['content_view'] = 'module_disabled';
                            $this->load->view($this->layout,$this->data);
                            $this->output->_display();
                            exit();
                        }
                        if ($activity_guid == 'create')
                        {
                            $this->data['albumMod'] = 'create';
                            $this->data['albumHeading'] = 'Create Album';
                            $this->data['albumAddMedia'] = 'Add media';
                            $status_id = 2;
                            $album_guid = '';
                        }
                        else if ($activity_guid == 'edit')
                        {
                            if($user_id==$this->session->userdata('UserID'))
                            {
                                $this->data['albumMod'] = 'edit';
                                $this->data['albumHeading'] = 'Edit Album';
                                $this->data['albumAddMedia'] = 'Add more media';
                                $activity_guid = $other_param;
                                $album_guid = $other_param;
                                $status_id = 2;
                            }
                            else
                            {
                                redirect(site_url().$this->uri->segment(1).'/'.$this->uri->segment(2).'/'.$this->uri->segment(4));
                            }
                        }
                        else
                        {
                            $status_id = get_detail_by_guid($activity_guid, 13, "StatusID");
                            $album_guid = $activity_guid;
                            $this->data['albumMod'] = 'detail';
                        }
                    }
                    else if ($module == 'Cover')
                    {
                        $status_id = get_detail_by_guid($activity_guid, 21, "StatusID");
                        if($status_id!='2')
                        {
                            redirect(get_entity_url($user_id));
                        }
                    }
                    else if ($module == 'Endorsment')
                    {
                        $status_id =2;
                    }
                    else
                    {
                        $entity = get_detail_by_guid($activity_guid, 0, "UserID,StatusID, ActivityID", 2);
                        if (!empty($entity))
                        {
                            $status_id = $entity['StatusID'];
                            $activity_id = $entity['ActivityID'];

                            $activity_user_id=$entity['UserID'];
                        }


                    }

                    if ($status_id == 3)
                    {
                        $this->data['content_view'] = 'deleteCont';
                        $this->data['title'] = "Content not available";
                        return $this->load->view($this->layout, $this->data);
                    }
                    else if ($status_id == 2 || ($status_id==10 && $activity_user_id==$current_user_id))
                    {
                        $show_404 = false;
                    }
                    /* if(is_entity_exists(19,get_detail_by_guid($activity_guid))){
                      $show_404 = false;
                      } */
                }

                $this->data['isFileTab'] = false;
                $this->data['isLinkTab'] = false;
                switch ($module)
                {
                    case 'Links':
                        $this->data['isLinkTab'] = true;
                        $this->data['pname'] = 'links';
                        $this->data['isStickyFilterAllowed'] = TRUE;
                        $this->data['content_view'] = 'users/links_user';
                        break;
                    case 'Files':
                        $this->data['isFileTab'] = true;
                        $this->data['pname'] = 'files';
                        $this->data['isStickyFilterAllowed'] = TRUE;
                        $this->data['content_view'] = 'users/files_user';
                        break;
                    case 'Friends':
                        $this->data['content_view'] = 'users/friend_list';
                        break;
                    case 'Followers':
                        $this->data['content_view'] = 'users/followers_list';
                        break;
                    case 'Following':
                        $this->data['content_view'] = 'users/following_list';
                        break;
                    case 'About':
                        $this->data['IsAdmin'] = 0;
                        if ($user_id == $current_user_id)
                        {
                            $this->data['IsAdmin'] = 1;
                        }
                        $this->data['pname'] = 'about';
                        $this->data['content_view'] = 'about/about';
                        break;
                    case 'Endorsment':
                        $this->data['pname'] = 'endorsment';
                        $this->data['content_view'] = 'users/endorsment';
                        break;
                    case 'Connections':
                        $this->data['pname'] = 'connections';
                        $this->data['content_view'] = 'users/connections';
                        break;
                    case 'Cover':
                        $this->load->model('media/media_model');
                        $this->data['pname'] = 'wall';
                        $this->data['content_view'] = 'profile/user_wall_new';
                        $this->data['SetCover'] = 1;
                        $this->data['RedirectPage'] = 1;
                        $this->data['IsFriend'] = 1;
                        $filename = $this->media_model->get_filepath_by_guid($activity_guid);
                        $this->data['FilePath'] = $filename;
                        break;
                    case 'Groups':
                        
                        if ($this->settings_model->isDisabled(1)) { // If group module is disabled
                            $this->data['content_view'] = 'module_disabled';
                            $this->load->view($this->layout, $this->data);
                            $this->output->_display();
                            exit();
                        }        
                        
                        $this->data['pname'] = 'groups';
                        $this->data['content_view'] = 'groups/group_list_other';
                        break;
                    case 'Media':
                        $this->data['IsMedia'] = 1;
                        $this->data['no_permission'] = false;
                        if (($user_id != $current_user_id) && !empty($album_guid))
                        {
                            $relation = $this->activity_model->isRelation($user_id, $current_user_id, true);
                            $visibility = get_detail_by_guid($album_guid, 13, 'Visibility', 1);
                            if (!in_array($visibility, $relation))
                            {
                                $this->data['no_permission'] = true;
                            }
                        }
                        //Check current user's permissions
                        $permissions = checkPermission($current_user_id, 3, $user_id, 'IsAccess');
                        if (!$permissions)
                        {
                            $this->data['WallTypeID'] = 3;
                            $this->data['IsFriend'] = $this->privacy_model->check_privacy($this->session->userdata('UserID'), $user_id, 'post');
                            $this->data['IsAdmin'] = 0;
                            $this->data['IsCreator'] = 0;
                        }
                        else
                        {
                            $this->data['IsAdmin'] = 1;
                            $this->data['IsFriend'] = 1;
                            $this->data['IsCreator'] = 1;
                        }
                        $this->data['IsActiveMember'] = 1; //checkPermission($current_user_id, 3, $UserID, 'IsActive'); 

                        $this->data['Active'] = 'media';

                        $this->data['pname'] = 'media';
                        $this->data['moduleSection'] = 'user';
                        $this->data['sectionGUID'] = $user_guid;
                        $this->data['UserGUID'] = $user_guid;
                        //$this->load->model('users/user_model');
                        $this->data['AlbumGUID'] = $activity_guid;
                        $this->data['title'] = 'Media';
                        $this->data['content_view'] = 'profile/media';
                        if (!empty($album_guid))
                        {
                            $activity_id = get_detail_by_guid($album_guid, 13, "ActivityID");
                            if (!empty($activity_id))
                            {
                                $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'ALBUM');
                            }
                        }
                        break;
                    default:
                        $this->load->model('privacy/privacy_model');
                        $this->data['pname'] = 'wall';
                        if($activity_guid)
                        {
                            $this->data['hidemedia'] = true;
                        }
                        $this->data['isStickyFilterAllowed'] = TRUE;
                        
                        $this->data['IsFriend'] = 1;
                        $this->data['WallTypeID'] = 4;
                        $this->data['DefaultPrivacy'] = 1;
                        $default_privacy = $this->privacy_model->get_value($user_id, 'default_post_privacy');
                        if ($default_privacy == 'friend')
                        {
                            $this->data['DefaultPrivacy'] = 3;
                        }
                        else if ($default_privacy == 'network')
                        {
                            $this->data['DefaultPrivacy'] = 2;
                        }
                        else if ($default_privacy == 'everyone')
                        {
                            $this->data['DefaultPrivacy'] = 1;
                        }
                        else if ($default_privacy == 'self')
                        {
                            $this->data['DefaultPrivacy'] = 4;
                        }
                        if ($user_id != $current_user_id)
                        {
                            $this->data['WallTypeID'] = 3;
                            $this->data['IsFriend'] = $this->privacy_model->check_privacy($this->session->userdata('UserID'), $user_id, 'post',1);
                        }
                        else
                        {
                            $this->data['IsAdmin'] = 1;
                            $this->data['IsFriend'] = 1;
                        }

                        if ($activity_id)
                        {
                            $this->notification_model->mark_notifications_as_read($current_user_id, $activity_id, 'POST');
                        }
                        
                        $this->load->model(array('users/friend_model'));
                        $this->data['IsFriend'] = 0;
                        if($current_user_id == $user_id) {
                            $this->data['IsFriend'] = 1;
                        }
                        
                        $this->data['title'] = 'Wall';
                        $this->data['content_view'] = 'profile/user_wall_new';

                        break;
                }

                $this->load->model('subscribe_model');
                $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($current_user_id, 'USER', $user_id);

                if ($show_404)
                {
                    $this->data['title'] = '404 Page Not Found';
                    $this->data['content_view'] = '404-page';
                }
                
                $this->post_details_extra_params($posted_profile_url,$activity_guid);
                
                $this->data['CoverImageState'] = get_cover_image_state($current_user_id, $user_id, 3);
                $this->load->view($this->layout, $this->data);
            }
            else
            {
                redirect();
            }
        }
        else
        {
            $this->data['title'] = '404 Page Not Found';
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }
    public function load_user_module_guest($user_id, $module, $activity_guid='', $posted_profile_url = '')
    {
        if (is_entity_exists(3, $user_id, FALSE))
        {
                $user_guid = get_detail_by_id($user_id, 3, 'UserGUID', 1);
                $this->data['CommentGUID']  = $this->input->get('cguid', TRUE);
                $this->data['title'] = $module;
                $this->data['Type'] = $module;
                $this->data['UID'] = $user_id;
                $this->data['UserID'] = $user_id;
                $this->data['EntityID'] = $user_guid;
                $this->data['ModuleID'] = '3';
                $this->data['ModuleEntityGUID'] = $user_guid;
                $this->data['ActivityGUID'] = $activity_guid;
                $activity_id = '';

                $this->data['ShowRecentActivity'] = 0;
                $current_user_id = $this->session->userdata('UserID');
                $this->load->model('notification_model');

                $show_404 = false;
                $this->data['albumMod'] = 'list';


                switch ($module)
                {
                    default:
                        $this->data['pname'] = 'wall';
                        $this->data['isStickyFilterAllowed'] = FALSE;
                        $this->data['IsFriend'] = 1;
                        $this->data['WallTypeID'] = 4;
                        $this->data['DefaultPrivacy'] = 1;

                        $this->data['WallTypeID'] = 3;
                        $this->data['IsFriend'] = 0;

                        $this->data['title'] = 'Wall';
                        $this->data['content_view'] = 'profile/user_wall_new';

                        break;
                }

                $this->data['IsSubscribed'] =0;

                if ($show_404)
                {
                    $this->data['title'] = '404 Page Not Found';
                    $this->data['content_view'] = '404-page';
                }
                $this->data['CoverImageState'] = 1;
                
                $this->post_details_extra_params($posted_profile_url,$activity_guid);
                
                $this->load->view($this->layout, $this->data);
            
        }
        else
        {
            $this->data['title'] = '404 Page Not Found';
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
    }
    
    protected function post_details_extra_params($posted_profile_url,$activity_guid=0) {
        if(!in_array(strtolower($posted_profile_url), ['post', 'article'])) {
            return;
        }

        //$details = get_detail_by_guid($activity_guid,19,'ActivityTypeID,PostType','2');
        $details = $this->activity_model->get_basic_post_details($activity_guid);
        $activity_type_id = $details['ActivityTypeID'];
        $post_type = $details['PostType'];
        if(!$this->activity_model->is_valid_activity_type($activity_type_id))
        {
            $this->data['title'] = '404 Page Not Found';
            $this->data['content_view'] = '404-page';
            return;
        }

        if($post_type == 4)
        {
            $this->data['body_class'] = 'white-container';
            $this->data['content_view'] = 'wall/article_detail';
        }
        else
        {
            $this->data['body_class'] = 'container-medium';
            $this->data['content_view'] = 'wall/detail';
        }
        $this->data['ForumID'] = 0;
        $this->data['ForumCategoryID'] = 0;
        $this->data['IsForum'] = 1;
        $this->data['whiteBG'] = 0;
        $this->data['IsForumPost'] = 1;
        //$this->data['ModuleID'] = '19';
        $this->data['EntityType'] = 'Activity';
        $this->data['ModuleEntityGUID'] = $activity_guid;
        //$this->data['pname']      = 'wall';
        $this->page_name = 'activity_details';
        $this->data['title'] = $this->data['title_activity'];
        $this->data['OGHeight'] = 500;
        $this->data['OGWidth'] = 750;
        $this->meta_description = substr($details['PostSearchContent'],0,320);
        if($this->meta_keywords!='')
        {
            $this->meta_keywords = $this->meta_keywords.','.$details['Tags'];
        }
        else
        {
            $this->meta_keywords = $details['Tags'];
        }
        
    }

    protected function get_profile_url($profile_url, $activity_guid) {
        if(!in_array(strtolower($profile_url), ['post', 'article'])) {
            return $profile_url;
        }
        
        $this->db->select('PU.Url, A.ModuleID, A.ModuleEntityID, A.PostTitle, A.PostContent');
        $this->db->from(PROFILEURL.' PU');
        $this->db->join(ACTIVITY.' A', 'A.UserID = PU.EntityID AND PU.EntityType = "User"', 'Inner');
        $this->db->where('A.ActivityGUID',$activity_guid);
        //$this->db->where('A.StatusID','2');
        $this->db->where("IF(A.UserID='".$this->session->userdata('UserID')."',A.StatusID IN('2','10'),A.StatusID='2')",null,false);
        $this->db->limit(1);
        $query = $this->db->get();
        
        $row = $query->row_array();
        if(!empty($row['Url'])) {
            $this->data['Activity_ModuleID'] = (int)$row['ModuleID'];
            $this->data['Activity_ModuleEntityID'] = (int)$row['ModuleEntityID'];
            $this->data['title_activity'] = getPostTitle($row);
            
            return $row['Url'];
        }
        
        return $profile_url;
    }

    
    public function set_cover($media_guid)
    {
        $this->data['title'] = 'Set Cover';
        $this->data['Type'] = 'Set Cover';
        $this->data['UID'] = $this->session->userdata('UserID');
        $this->data['UserID'] = $this->session->userdata('UserID');
        $this->data['EntityID'] = $this->session->userdata('UserGUID');
        $this->data['ModuleID'] = '3';
        $this->data['ModuleEntityGUID'] = $this->session->userdata('UserGUID');
        $this->data['ActivityGUID'] = '';
        $this->data['ShowRecentActivity'] = 0;
        $this->data['SetCover'] = 1;
        $this->load->model('media/media_model');
        $filename = $this->media_model->get_filename_by_guid($media_guid);
        $this->data['FilePath'] = IMAGE_SERVER_PATH . 'upload/profilebanner/' . $filename;
        $this->data['content_view'] = 'profile/user_wall_new';
        $this->load->view($this->layout, $this->data);
    }

    public function index($activity_guid = '')
    {
        if (empty($this->login_session_key))
        {
            redirect('/');
        }
        $this->data['IsFriend'] = '1';
        $this->data['title'] = 'Profile';
        $this->dashboard = 'profile';
        $this->data['ActivityGuID'] = $activity_guid;
        $this->data['UserID'] = $this->session->userdata('UserID');
        $this->data['WallType'] = '';
        $this->data['EntityID'] = $this->session->userdata('UserID');
        $this->data['profile_url'] = get_entity_url($this->data['EntityID']);
        $this->data['content_view'] = 'profile/user_wall_new';
        $this->load->view($this->layout, $this->data);
    }

    /**
     * [dashboard used to show news feed for logged in user.]
     */
    public function dashboard($slug='')
    {
        $user_id = get_user_id_by_loginsessionkey($this->login_session_key);
        if(!$user_id && $slug=='')
        {
            $this->community();
        }
        else
        {
            $this->dashboard = 'profile';
            $this->data['IsFriend'] = '1';
            $this->data['WallTypeID'] = 4;
            $this->data['title'] = 'News Feed';
            $this->data['IsAdmin'] = 1;
            $this->data['IsNewsFeed'] = 1;
            $this->data['isStickyFilterAllowed'] = TRUE;
            $this->data['IsNewUser'] = 0;
            $this->data['UserID'] = $user_id;
            $this->data['EntityID'] = $user_id;
            $this->data['ModuleEntityGUID'] = get_guid_by_id($user_id, 3);
            $this->data['AllActivity'] = '1';
            $this->data['ModuleID'] = '3';
            $this->data['pname'] = 'dashboard';
            $this->data['content_view'] = 'profile/user_wall_new';
            $this->data['DefaultPrivacy'] = 1;
            $this->load->model(array('privacy/privacy_model','activity/activity_model'));
            if(isset($_GET['files'])) {
                $this->data['pname'] = 'files';
                $this->data['title'] = 'Files';
                $this->data['isFileTab'] = true;
            } else {
                $this->data['isFileTab'] = false;
            }
            if(isset($_GET['links'])) {
                $this->data['pname'] = 'links';
                $this->data['title'] = 'Links';
                $this->data['isLinkTab'] = true;
            } else {
                $this->data['isLinkTab'] = false;
            }
            if($user_id)
            {
                $this->data['IsLoggedIn'] = true;
                /*if($this->activity_model->is_new_user($this->session->userdata('UserID')))
                {
                    $this->data['IsNewUser'] = 1;
                }*/
            }
            else
            {
                $this->data['IsLoggedIn'] = false;

                $this->load->library('geoip');
                $ip_address = getRealIpAddr();
                /*if(ENVIRONMENT!='production')
                {
                    $ip_address = '103.21.54.66';
                }*/
                $record = $this->geoip->info($ip_address);

                $this->data['location'] = false;
                if(isset($record->city) && !empty($record->city) && isset($record->state_name) && !empty($record->state_name) && isset($record->country_name) && !empty($record->country_name) && isset($record->country_code) && !empty($record->country_code))
                {
                    $this->data['location'] = true;
                    $this->data['City'] = $record->city;
                    $this->data['State'] = $record->state_name;
                    $this->data['Country'] = $record->country_name;
                    $this->data['CountryCode'] = $record->country_code;
                    $this->data['Lat'] = isset($record->latitude) ? $record->latitude : '0';
                    $this->data['Lng'] = isset($record->longitude) ? $record->longitude : '0' ;
                }
            }
            
            $this->data['DefaultPrivacy'] = $this->privacy_model->get_default_privacy($this->session->userdata('UserID'));
            $this->load->view($this->layout, $this->data);
        }
    }

    public function wiki($type='all')
    {
        $user_id = get_user_id_by_loginsessionkey($this->login_session_key);
        /*if (!$user_id)
        {
            redirect('/');
        }*/
        $this->dashboard = 'profile';
        $this->data['IsFriend'] = '1';
        $this->data['WallTypeID'] = 4;
        $this->data['title'] = 'Article';
        $this->data['IsAdmin'] = 1;
        $this->data['ShowFilter'] = 1;
        $this->data['isStickyFilterAllowed'] = TRUE;
        $this->data['IsNewUser'] = 0;
        $this->data['UserID'] = $user_id;
        $this->data['EntityID'] = $user_id;
        $this->data['ModuleEntityGUID'] = get_guid_by_id($user_id, 3);
        $this->data['AllActivity'] = '1';
        $this->data['ModuleID'] = '3';
        $this->data['pname'] = 'wiki';
        $this->data['ShowClass'] = 1;
        $this->data['content_view'] = 'wiki/wiki';
        $this->data['DefaultPrivacy'] = 1;
        $this->load->model(array('privacy/privacy_model','activity/activity_model'));
        $this->data['isFileTab'] = false;
        $this->data['isLinkTab'] = false;
        $this->data['ArticleType'] = $type;
        $this->data['MainWiki'] = 1;
        
        if($type == 'favourite')
        {
            $type = 'fav';
        }

        $label = 'NEW ARTICLES';
        if($type == 'fav')
        {
            $label = 'MY FAVOURITES';
        }
        if($type == 'recommended')
        {
            $label = 'POPULAR ARTICLE';
        }
        if($type == 'suggested')
        {
            $label = 'SUGGESTED ARTICLE';
        }
        
        $this->data['label'] = $label;

        if($user_id)
        {
            $this->data['IsLoggedIn'] = true;
        }
        else
        {
            $this->data['IsLoggedIn'] = false;
        }
        $this->load->view($this->layout, $this->data);
    }

    /**
     * [notifications Used to show all notification for logged in user]
     */
    public function notifications()
    {
        if (empty($this->login_session_key))
        {
            redirect('/');
        }
        $this->dashboard = 'profile';
        $this->data['title'] = 'Notifications';
        $this->data['UserID'] = $this->session->userdata('UserID');
        $this->data['content_view'] = 'notifications/view-notifications';
        $this->load->view($this->layout, $this->data);
    }

    public function media($group_guid)
    {
        $this->data['Active'] = 'media';
        $group_id = get_detail_by_guid($group_guid, 1);
        $user_id = $this->session->userdata('UserID');        
        if (check_blocked_user($user_id, 1, $group_id))
        {
            redirect(site_url('dashboard'));
        }

        //Check current user's permissions
        $Permissions = checkPermission($user_id, 1, $group_id, 'IsAccess');
        if (!$Permissions['IsAccess'])
        {
            redirect('/');
        }

        $this->data['IsAdmin'] = checkPermission($user_id, 1, $group_id, 'IsOwner', 3, $user_id);
        $this->data['IsCreator'] = checkPermission($user_id, 1, $group_id, 'IsCreator', 3, $user_id);
        $this->data['IsActiveMember'] = checkPermission($user_id, 1, $group_id, 'IsActive', 3, $user_id);

        // Merging permissions with preset data.
        $this->data = array_merge($Permissions, $this->data);

        $user_guid = get_guid_by_id($user_id, 3);
        $this->data['GroupGUID'] = $group_guid;
        $this->data['UserGUID'] = $user_guid;
        $this->data['UserID'] = $this->group_model->get_group_owner($group_id);
        $this->data['title'] = 'Group Media Gallary';
        $this->data['GroupID'] = $group_id;

        $this->data['EntityID'] = $group_guid;
        $this->data['ModuleEntityGUID'] = $group_guid;
        $this->data['type'] = '';
        $this->data['ModuleID'] = '1';
        $this->data['IsTeam'] = '1';
        //$this->group_page_name = 'media';
        $this->data['content_view'] = 'groups/media';
        $this->load->view($this->layout, $this->data);
    }

    /**
     * [dashboard used to show news feed for logged in user.]
     */
    public function community()
    {
        $user_id = get_user_id_by_loginsessionkey($this->login_session_key);
        $this->dashboard = 'profile';
        $this->data['IsFriend'] = '1';
        $this->data['WallTypeID'] = 4;
        $this->data['title'] = 'Community';
        $this->data['IsAdmin'] = 1;
        $this->data['IsNewsFeed'] = 1;
        $this->data['isStickyFilterAllowed'] = TRUE;
        $this->data['IsNewUser'] = 0;
        $this->data['UserID'] = $user_id;
        $this->data['EntityID'] = $user_id;
        $this->data['ModuleEntityGUID'] = '';
        $this->data['AllActivity'] = '1';
        $this->data['ModuleID'] = '34';
        $this->data['pname'] = 'dashboard';
        $this->data['sub_name'] = 'forum';
        $this->data['content_view'] = 'community/index';
        $this->data['DefaultPrivacy'] = 1;
        $this->load->model(array('privacy/privacy_model','activity/activity_model'));
        $this->data['isFileTab'] = false;
        $this->data['isLinkTab'] = false;

        if($user_id)
        {
            $this->data['IsLoggedIn'] = true;
            /*if($this->activity_model->is_new_user($this->session->userdata('UserID')))
            {
                $this->data['IsNewUser'] = 1;
            }*/
        }
        else
        {
            $this->data['IsLoggedIn'] = false;

            $this->load->library('geoip');
            $ip_address = getRealIpAddr();
            /*if(ENVIRONMENT!='production')
            {
                $ip_address = '103.21.54.66';
            }*/
            $record = $this->geoip->info($ip_address);

            $this->data['location'] = false;
            if(isset($record->city) && !empty($record->city) && isset($record->state_name) && !empty($record->state_name) && isset($record->country_name) && !empty($record->country_name) && isset($record->country_code) && !empty($record->country_code))
            {
                $this->data['location'] = true;
                $this->data['City'] = $record->city;
                $this->data['State'] = $record->state_name;
                $this->data['Country'] = $record->country_name;
                $this->data['CountryCode'] = $record->country_code;
                $this->data['Lat'] = isset($record->latitude) ? $record->latitude : '0';
                $this->data['Lng'] = isset($record->longitude) ? $record->longitude : '0' ;
            }
        }

        $default_privacy = $this->privacy_model->get_value($this->session->userdata('UserID'), 'default_post_privacy');
        if ($default_privacy == 'everyone')
        {
            $this->data['DefaultPrivacy'] = 1;
        }
        else if ($default_privacy == 'self')
        {
            $this->data['DefaultPrivacy'] = 4;
        }
        $this->load->view($this->layout, $this->data);
    }


    public function post_article($module_id=3,$module_entity_guid='',$activity_guid='')
    {
        if (empty($this->login_session_key))
        {
            redirect('/');
        }
        $user_id = $this->session->userdata('UserID');
        $user_guid = get_detail_by_id($user_id, 3, 'UserGUID', 1);
        $this->data['UserID'] = $user_id;
        $this->data['EntityID'] = $user_guid;
        $this->data['ModuleID'] = ($module_id) ? $module_id : 3 ;
        $this->data['ModuleEntityGUID'] = ($module_id!=3) ? $module_entity_guid : $user_guid ;
        $this->data['ActivityGUID'] = $activity_guid;
        $this->data['ForumID'] = 0;
        $this->data['ForumName'] = '';
        $module_name = '';

        $get_module_data = false;
        if($module_id == 1)
        {
            $field = 'GroupName, GroupGUID';
            $name = 'GroupName';
            $guid = 'GroupGUID';
            $get_module_data = true;
        }
        else if($module_id == 34)
        {
            $field = 'Name, ForumCategoryGUID, ForumID';
            $get_module_data = true;
            $name = 'Name';
            $guid = 'ForumCategoryGUID';
        }
        else if($module_id == 14)
        {
            $field = 'Title, EventGUID';
            $get_module_data = true;
            $name = 'Title';
            $guid = 'EventGUID';
        }
//        else if($module_id == 33)
//        {
//            $field = 'ForumID';
//                        
//            $entity_data = get_detail_by_guid($module_entity_guid,$module_id,$field,2);
//            $forum_id = isset($entity_data[$field]) ? $entity_data[$field] : '';
//            
//            if($forum_id) {
//                $this->load->model(array('forum/forum_model', 'users/user_model', 'activity/activity_model', 'notification_model'));                            
//                $permissions = $this->forum_model->check_forum_permissions($user_id, $forum_id, FALSE);
//                $categories = $this->forum_model->forum_category_name($forum_id, $user_id, $permissions);
//                
//            }
//            
//        }

        if($get_module_data)
        {
            $entity_data = get_detail_by_guid($module_entity_guid,$module_id,$field,2);
            $module_name = isset($entity_data[$name]) ? $entity_data[$name] : '';
            $entity_guid = isset($entity_data[$guid]) ? $entity_data[$guid] : '';            
            //$this->data['ModuleID'] = ($module_id && $entity_guid) ? $module_id : 3 ;
            
            if($module_id == 34) {                
                $this->data['ForumID'] = isset($entity_data['ForumID']) ? $entity_data['ForumID'] : 0;  
                
                if($activity_guid) {
                    $entity_data = get_detail_by_id($this->data['ForumID'], 33, 'Name', 2);
                    $this->data['ForumName'] = isset($entity_data['Name']) ? $entity_data['Name'] : '';
                }
            }
            
        }
        
        $this->data['ModuleEntityName'] = $module_name;
        $this->dashboard = 'profile';
        $this->data['title'] = 'Post an article';
        $this->data['UserID'] = $this->session->userdata('UserID');
        $this->data['content_view'] = 'wall/post_article';
        $this->data['body_class'] = 'white-container';
        $this->load->view($this->layout, $this->data);
    }
}
