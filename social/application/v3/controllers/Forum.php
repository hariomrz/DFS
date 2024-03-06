<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Group Controller class for create/update/list group
 * @package    Group
 * @author     Vinfotech Team
 * @version    1.0
 */

class Forum extends Common_Controller {

    public $page_name = 'forum';
    public $dashboard = 'forum';

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function __construct() {       
        parent::__construct();
        $this->data['IsForum']  = 1;
        $this->data['pname']  = 'forum';
        $this->data['IsBlocked'] = 0;
        $this->data['ModuleID']     = '34';
        if($this->settings_model->isDisabled(34)){
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout,$this->data);
            $this->output->_display();
            exit();
        }
        $this->load->model(array('flag_model','forum/forum_model','notification_model'));
    }

    public function index($forum_url='',$category_url='',$sub_category_url='',$activity_guid='')
    {
//        if(empty($forum_url)) {
//            redirect();
//        }

        if(in_array($sub_category_url,array('discussions','articles','questions','announcements')))
        {
            $sub_category_url = '';
        }
        if(in_array($activity_guid,array('discussions','articles','questions','announcements')))
        {
            $activity_guid = '';
        }
//echo $forum_url.' - '.$category_url.' - '.$sub_category_url.' - '.$activity_guid; die;
        $this->data['whiteBG'] = FALSE;
        if($sub_category_url)
        {
            if(!$this->forum_model->check_url($sub_category_url, 34))
            {
                if($this->activity_model->check_valid_activity_guid($sub_category_url))
                {
                    $activity_guid = $sub_category_url;
                    $sub_category_url = '';
                }                
            }
        }

        $user_id = $this->session->userdata('UserID');
        $not_found = FALSE;
        $is_wall = FALSE;
        $is_sub_cat = 0;
        if(!empty($forum_url))
        {
            if ($row = $this->forum_model->check_url($forum_url, 33))
            {
                $forum_id   = $row['ForumID'];
                $forum_guid = $row['ForumGUID'];
                $forum_name = $row['Name'];
                
                $this->data['ModuleID']         = 33;
                $this->data['ModuleEntityID']   = $forum_id;        
                $this->data['ModuleEntityGUID'] = $forum_guid;
                $this->data['ForumVisibility'] = get_detail_by_id($forum_id,33,'Visible');

                if(!empty($category_url)) // check category url
                {
                    if ($row = $this->forum_model->check_url($category_url, 34,$forum_id))
                    {
                        $this->data['whiteBG'] = False;

                        $category_id   = $row['ForumCategoryID'];
                        $category_guid = $row['ForumCategoryGUID'];
                        $category_name = $row['Name'];

                        $this->data['ModuleID']         = 33;
                        $this->data['ModuleEntityID']   = $category_id;        
                        $this->data['ModuleEntityGUID'] = $category_guid;

                        if(!empty($sub_category_url))
                        { // check sub category url
                            if ($row = $this->forum_model->check_url($sub_category_url, 34,$forum_id))
                            {
                                $category_id   = $row['ForumCategoryID'];
                                $category_guid = $row['ForumCategoryGUID'];
                                $category_name = $row['Name'];
                                $is_sub_cat    = 1;
                                $this->data['ModuleID']         = 33;
                                $this->data['ModuleEntityID']   = $category_id;        
                                $this->data['ModuleEntityGUID'] = $category_guid;    
                            }
                            else
                            {
                                $not_found = TRUE;
                            }   
                        }

                        $is_wall = TRUE;

                        $permissions = $this->forum_model->check_forum_category_permissions($user_id, $category_id);

                        $this->data['IsSubCat'] = $is_sub_cat;
                        $this->data['ForumID']  = $forum_id;
                        $this->data['ForumCategoryID']  = $category_id;
                        $this->data['ModuleEntityGUID']  = $category_guid;
                        $this->data['ModuleEntityID']  = $category_id;
                        $this->data['ActivityGUID']  = $activity_guid;
                        $this->data['title']            = 'Wall';
                        
                        $this->data['body_class']     = 'container-medium';
                        $this->data['content_view']     = 'forum/wall';
                        
                        $this->data['pname']            = 'wall';
                        $this->data['ModuleID']     = '34';
                        $this->data['page_name']        = 'forum';
                        $this->data['IsAdmin'] = $permissions['IsAdmin'];
                        $this->load->view($this->layout, $this->data);
                    }
                    else
                    {
                        $not_found = TRUE;  
                    }
                }
            }
            else
            {
                $not_found = TRUE;                
            }  
        }

        if($not_found)
        {
            $this->data['title'] = '404 Page Not Found';
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        else if(!$is_wall)
        {
            $this->data['title'] = 'Discover';
            $this->data['type'] = 'dashboard';
            $this->data['ModuleID']     = '34';
            $this->data['pname']        = 'forum';
            $this->data['content_view'] = 'forum/discover';

            $this->load->view($this->layout, $this->data);
        }        
    }

    public function media($action='list',$forum_url='',$category_url='',$sub_category_url='',$album_guid='')
    {
        if($sub_category_url)
        {
            if(!$this->forum_model->check_url($sub_category_url, 34))
            {
                if($this->album_model->is_valid_album($sub_category_url))
                {
                    $album_guid = $sub_category_url;
                    $sub_category_url = '';
                }                
            }
        }

        $user_id = $this->session->userdata('UserID');
        $not_found = FALSE;
        $is_wall = FALSE;
        if(!empty($forum_url))
        {
            if ($row = $this->forum_model->check_url($forum_url, 33))
            {
                $forum_id   = $row['ForumID'];
                $forum_guid = $row['ForumGUID'];
                $forum_name = $row['Name'];
                
                $this->data['ModuleID']         = 33;
                $this->data['ModuleEntityID']   = $forum_id;        
                $this->data['ModuleEntityGUID'] = $forum_guid;

                if(!empty($category_url)) // check category url
                {
                    if ($row = $this->forum_model->check_url($category_url, 34))
                    {
                        $category_id   = $row['ForumCategoryID'];
                        $category_guid = $row['ForumCategoryGUID'];
                        $category_name = $row['Name'];

                        $this->data['ModuleID']         = 33;
                        $this->data['ModuleEntityID']   = $category_id;        
                        $this->data['ModuleEntityGUID'] = $category_guid;

                        if(!empty($sub_category_url))
                        { // check sub category url
                            if ($row = $this->forum_model->check_url($sub_category_url, 34))
                            {
                                $category_id   = $row['ForumCategoryID'];
                                $category_guid = $row['ForumCategoryGUID'];
                                $category_name = $row['Name'];

                                $this->data['ModuleID']         = 33;
                                $this->data['ModuleEntityID']   = $category_id;        
                                $this->data['ModuleEntityGUID'] = $category_guid;    
                            }
                            else
                            {
                                $not_found = TRUE;
                            }   
                        }
                    }
                    else
                    {
                        $not_found = TRUE;  
                    }

                    if(!$not_found)
                    {
                        $is_wall = TRUE;

                        $permissions = $this->forum_model->check_forum_category_permissions($user_id, $category_id);

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
                            $status_id = get_detail_by_guid($action, 13, "StatusID");
                            $this->data['albumMod']    = 'detail';
                        }

                        $this->data['CatURL'] = $this->forum_model->get_category_url($category_id);
                        $this->data['ForumID']  = $forum_id;
                        $this->data['ForumCategoryID']  = $category_id;
                        $this->data['ModuleEntityGUID']  = $category_guid;
                        $this->data['ModuleEntityID']  = $category_id;
                        $this->data['AlbumGUID'] = $album_guid;
                        $this->data['title']            = 'Wall';
                        $this->data['moduleSection'] = 'forumcategory';
                        $this->data['sectionGUID']  = $category_guid;
                        $this->data['albumMod']    = $action;
                        $this->data['content_view']     = 'forum/media';
                        $this->data['pname']            = 'wall';
                        $this->data['ModuleID']     = '34';
                        $this->data['page_name']        = 'Media';
                        $this->data['IsAdmin']        = ($permissions['IsAdmin']) ? '1' : '0' ;
                        $this->load->view($this->layout, $this->data);
                    }


                }
            }
            else
            {
                $not_found = TRUE;                
            }  
        }
}

    public function links($forum_url='',$category_url='',$sub_category_url='')
    {

        $user_id = $this->session->userdata('UserID');
        $not_found = FALSE;
        $is_wall = FALSE;
        if(!empty($forum_url))
        {
            if ($row = $this->forum_model->check_url($forum_url, 33))
            {
                $forum_id   = $row['ForumID'];
                $forum_guid = $row['ForumGUID'];
                $forum_name = $row['Name'];
                
                $this->data['ModuleID']         = 33;
                $this->data['ModuleEntityID']   = $forum_id;        
                $this->data['ModuleEntityGUID'] = $forum_guid;

                if(!empty($category_url)) // check category url
                {
                    if ($row = $this->forum_model->check_url($category_url, 34))
                    {
                        $category_id   = $row['ForumCategoryID'];
                        $category_guid = $row['ForumCategoryGUID'];
                        $category_name = $row['Name'];

                        $this->data['ModuleID']         = 33;
                        $this->data['ModuleEntityID']   = $category_id;        
                        $this->data['ModuleEntityGUID'] = $category_guid;

                        if(!empty($sub_category_url))
                        { // check sub category url
                            if ($row = $this->forum_model->check_url($sub_category_url, 34))
                            {
                                $category_id   = $row['ForumCategoryID'];
                                $category_guid = $row['ForumCategoryGUID'];
                                $category_name = $row['Name'];

                                $this->data['ModuleID']         = 33;
                                $this->data['ModuleEntityID']   = $category_id;        
                                $this->data['ModuleEntityGUID'] = $category_guid;    
                            }
                            else
                            {
                                $not_found = TRUE;
                            }   
                        }
                    }
                    else
                    {
                        $not_found = TRUE;  
                    }

                    if(!$not_found)
                    {
                        $is_wall = TRUE;

                        $permissions = $this->forum_model->check_forum_category_permissions($user_id, $category_id);

                        $this->data['CatURL'] = $this->forum_model->get_category_url($category_id);
                        $this->data['ForumID']  = $forum_id;
                        $this->data['ForumCategoryID']  = $category_id;
                        $this->data['ModuleEntityGUID']  = $category_guid;
                        $this->data['ModuleEntityID']  = $category_id;
                        $this->data['title']            = 'Wall';
                        $this->data['moduleSection'] = 'forumcategory';
                        $this->data['sectionGUID']  = $category_guid;
                        $this->data['content_view']     = 'forum/links';
                        $this->data['pname']            = 'links';
                        $this->data['ModuleID']     = '34';
                        $this->data['page_name']        = 'forum';
                        $this->data['UserID']        = $user_id;
                        $this->data['IsAdmin']        = ($permissions['IsAdmin']) ? '1' : '0' ;
                        $this->data['isLinkTab'] = 1;
                        $this->load->view($this->layout, $this->data);
                    }


                }
            }
            else
            {
                $not_found = TRUE;                
            }  
        }

        if($not_found)
        {
            $this->data['title'] = '404 Page Not Found';
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        else if(!$is_wall)
        {
            $this->data['title'] = 'Forum';
            $this->data['type'] = 'dashboard';
            $this->data['ModuleID']     = '34';
            $this->data['pname']        = 'forum';
            $this->data['content_view'] = 'forum/discover';

            $this->load->view($this->layout, $this->data);
        }        
    }

    public function files($forum_url='',$category_url='',$sub_category_url='')
    {

        $user_id = $this->session->userdata('UserID');
        $not_found = FALSE;
        $is_wall = FALSE;
        if(!empty($forum_url))
        {
            if ($row = $this->forum_model->check_url($forum_url, 33))
            {
                $forum_id   = $row['ForumID'];
                $forum_guid = $row['ForumGUID'];
                $forum_name = $row['Name'];
                
                $this->data['ModuleID']         = 33;
                $this->data['ModuleEntityID']   = $forum_id;        
                $this->data['ModuleEntityGUID'] = $forum_guid;

                if(!empty($category_url)) // check category url
                {
                    if ($row = $this->forum_model->check_url($category_url, 34))
                    {
                        $category_id   = $row['ForumCategoryID'];
                        $category_guid = $row['ForumCategoryGUID'];
                        $category_name = $row['Name'];

                        $this->data['ModuleID']         = 33;
                        $this->data['ModuleEntityID']   = $category_id;        
                        $this->data['ModuleEntityGUID'] = $category_guid;

                        if(!empty($sub_category_url))
                        { // check sub category url
                            if ($row = $this->forum_model->check_url($sub_category_url, 34))
                            {
                                $category_id   = $row['ForumCategoryID'];
                                $category_guid = $row['ForumCategoryGUID'];
                                $category_name = $row['Name'];

                                $this->data['ModuleID']         = 33;
                                $this->data['ModuleEntityID']   = $category_id;        
                                $this->data['ModuleEntityGUID'] = $category_guid;    
                            }
                            else
                            {
                                $not_found = TRUE;
                            }   
                        }
                    }
                    else
                    {
                        $not_found = TRUE;  
                    }

                    if(!$not_found)
                    {
                        $is_wall = TRUE;

                        $permissions = $this->forum_model->check_forum_category_permissions($user_id, $category_id);

                        $this->data['CatURL'] = $this->forum_model->get_category_url($category_id);
                        $this->data['ForumID']  = $forum_id;
                        $this->data['ForumCategoryID']  = $category_id;
                        $this->data['ModuleEntityGUID']  = $category_guid;
                        $this->data['ModuleEntityID']  = $category_id;
                        $this->data['title']            = 'Wall';
                        $this->data['moduleSection'] = 'forumcategory';
                        $this->data['sectionGUID']  = $category_guid;
                        $this->data['content_view']     = 'forum/files';
                        $this->data['pname']            = 'files';
                        $this->data['ModuleID']     = '34';
                        $this->data['page_name']        = 'forum';
                        $this->data['UserID']        = $user_id;
                        $this->data['IsAdmin']        = ($permissions['IsAdmin']) ? '1' : '0' ;
                        $this->data['isFileTab'] = 1;
                        $this->load->view($this->layout, $this->data);
                    }


                }
            }
            else
            {
                $not_found = TRUE;                
            }  
        }

        if($not_found)
        {
            $this->data['title'] = '404 Page Not Found';
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        else if(!$is_wall)
        {
            $this->data['title'] = 'Forum';
            $this->data['type'] = 'dashboard';
            $this->data['ModuleID']     = '34';
            $this->data['pname']        = 'forum';
            $this->data['content_view'] = 'forum/discover';

            $this->load->view($this->layout, $this->data);
        }        
    }
    
    public function members($forum_url='',$category_url='',$sub_category_url='')
    {

        $user_id = $this->session->userdata('UserID');
        $not_found = FALSE;
        $is_wall = FALSE;
        if(!empty($forum_url))
        {
            if ($row = $this->forum_model->check_url($forum_url, 33))
            {
                $forum_id   = $row['ForumID'];
                $forum_guid = $row['ForumGUID'];
                $forum_name = $row['Name'];
                
                $this->data['ModuleID']         = 33;
                $this->data['ModuleEntityID']   = $forum_id;        
                $this->data['ModuleEntityGUID'] = $forum_guid;

                if(!empty($category_url)) // check category url
                {
                    if ($row = $this->forum_model->check_url($category_url, 34))
                    {
                        $category_id   = $row['ForumCategoryID'];
                        $category_guid = $row['ForumCategoryGUID'];
                        $category_name = $row['Name'];

                        $this->data['ModuleID']         = 33;
                        $this->data['ModuleEntityID']   = $category_id;        
                        $this->data['ModuleEntityGUID'] = $category_guid;

                        if(!empty($sub_category_url))
                        { // check sub category url
                            if ($row = $this->forum_model->check_url($sub_category_url, 34))
                            {
                                $category_id   = $row['ForumCategoryID'];
                                $category_guid = $row['ForumCategoryGUID'];
                                $category_name = $row['Name'];

                                $this->data['ModuleID']         = 33;
                                $this->data['ModuleEntityID']   = $category_id;        
                                $this->data['ModuleEntityGUID'] = $category_guid;    
                            }
                            else
                            {
                                $not_found = TRUE;
                            }   
                        }
                    }
                    else
                    {
                        $not_found = TRUE;  
                    }

                    if(!$not_found)
                    {
                        $is_wall = TRUE;

                        $permissions = $this->forum_model->check_forum_category_permissions($user_id, $category_id);

                        $this->data['CatURL'] = $this->forum_model->get_category_url($category_id);
                        $this->data['ForumID']  = $forum_id;
                        $this->data['ForumCategoryID']  = $category_id;
                        $this->data['ModuleEntityGUID']  = $category_guid;
                        $this->data['ModuleEntityID']  = $category_id;
                        $this->data['title']            = 'Wall';
                        $this->data['moduleSection'] = 'forumcategory';
                        $this->data['sectionGUID']  = $category_guid;
                        $this->data['content_view']     = 'forum/members';
                        $this->data['pname']            = 'wall';
                        $this->data['ModuleID']     = '34';
                        $this->data['page_name']        = 'forum';
                        $this->page_name        = 'manage_admin';
                        $this->data['IsAdmin']        = ($permissions['IsAdmin']) ? '1' : '0' ;
                        $this->load->view($this->layout, $this->data);
                    }


                }
            }
            else
            {
                $not_found = TRUE;                
            }  
        }

        if($not_found)
        {
            $this->data['title'] = '404 Page Not Found';
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        else if(!$is_wall)
        {
            $this->data['title'] = 'Forum';
            $this->data['type'] = 'dashboard';
            $this->data['ModuleID']     = '34';
            $this->data['pname']        = 'forum';
            $this->data['content_view'] = 'forum/discover';

            $this->load->view($this->layout, $this->data);
        }        
    }

    public function wiki($forum_url='',$category_url='',$sub_category_url='')
    {

        $user_id = $this->session->userdata('UserID');
        $not_found = FALSE;
        $is_wall = FALSE;
        if(!empty($forum_url))
        {
            if ($row = $this->forum_model->check_url($forum_url, 33))
            {
                $forum_id   = $row['ForumID'];
                $forum_guid = $row['ForumGUID'];
                $forum_name = $row['Name'];
                
                $this->data['ModuleID']         = 33;
                $this->data['ModuleEntityID']   = $forum_id;        
                $this->data['ModuleEntityGUID'] = $forum_guid;

                if(!empty($category_url)) // check category url
                {
                    if ($row = $this->forum_model->check_url($category_url, 34))
                    {
                        $category_id   = $row['ForumCategoryID'];
                        $category_guid = $row['ForumCategoryGUID'];
                        $category_name = $row['Name'];

                        $this->data['ModuleID']         = 33;
                        $this->data['ModuleEntityID']   = $category_id;        
                        $this->data['ModuleEntityGUID'] = $category_guid;

                        if(!empty($sub_category_url))
                        { // check sub category url
                            if ($row = $this->forum_model->check_url($sub_category_url, 34))
                            {
                                $category_id   = $row['ForumCategoryID'];
                                $category_guid = $row['ForumCategoryGUID'];
                                $category_name = $row['Name'];

                                $this->data['ModuleID']         = 33;
                                $this->data['ModuleEntityID']   = $category_id;        
                                $this->data['ModuleEntityGUID'] = $category_guid;    
                            }
                            else
                            {
                                $not_found = TRUE;
                            }   
                        }
                    }
                    else
                    {
                        $not_found = TRUE;  
                    }

                    if(!$not_found)
                    {
                        $is_wall = TRUE;

                        $permissions = $this->forum_model->check_forum_category_permissions($user_id, $category_id);

                        $this->data['CatURL'] = $this->forum_model->get_category_url($category_id);
                        $this->data['ForumID']  = $forum_id;
                        $this->data['ForumCategoryID']  = $category_id;
                        $this->data['ModuleEntityGUID']  = $category_guid;
                        $this->data['ModuleEntityID']  = $category_id;
                        $this->data['title']            = 'Wall';
                        $this->data['moduleSection'] = 'forumcategory';
                        $this->data['sectionGUID']  = $category_guid;
                        $this->data['content_view']     = 'forum/wiki';
                        $this->data['pname']            = 'wall';
                        $this->data['ModuleID']     = '34';
                        $this->data['page_name']        = 'forum';
                        $this->data['IsAdmin']        = ($permissions['IsAdmin']) ? '1' : '0' ;
                        $this->data['CanCreateWiki']    = can_create_wiki($user_id,34,$category_id);
                        $this->load->view($this->layout, $this->data);
                    }


                }
            }
            else
            {
                $not_found = TRUE;                
            }  
        }

        if($not_found)
        {
            $this->data['title'] = '404 Page Not Found';
            $this->data['content_view'] = '404-page';
            $this->load->view($this->layout, $this->data);
        }
        else if(!$is_wall)
        {
            $this->data['title'] = 'Forum';
            $this->data['type'] = 'dashboard';
            $this->data['ModuleID']     = '34';
            $this->data['pname']        = 'forum';
            $this->data['content_view'] = 'forum/discover';

            $this->load->view($this->layout, $this->data);
        }        
    }

    public function manage_admin($forum_id)
    {
        $this->data['title'] = 'Forum';
        $this->data['type'] = 'dashboard';
        $this->data['ModuleID']     = '34';
        $this->data['pname']        = 'manage_admin';
        $this->data['ForumID']  = $forum_id;
        $this->data['content_view'] = 'forum/manage_admin';
        $this->page_name        = 'manage_admin';
        $this->load->view($this->layout, $this->data);
    }

    public function members_settings($forum_id,$category_id)
    {
        $this->data['title'] = 'Forum';
        $this->data['type'] = 'dashboard';
        $this->data['ModuleID']     = '34';
        $this->data['pname']        = 'forum';
        $this->data['ForumID']  = $forum_id;
        $this->data['ForumCategoryID']  = $category_id;
        $this->data['content_view'] = 'forum/members_settings';
        $this->page_name        = 'manage_admin';
        $this->load->view($this->layout, $this->data);
    }

    public function wall($forum_id,$category_id) 
    {    
        $this->data['title']            = 'Wall';
        $this->data['content_view']     = 'forum/wall';
        $this->data['pname']            = 'wall';
        $this->data['ModuleID']     = '34';
        $this->data['page_name']        = 'forum';
        $this->data['ForumID']  = $forum_id;
        $this->data['ForumCategoryID']  = $category_id;
        $this->data['ModuleEntityGUID']  = get_detail_by_id($category_id,34);
        $this->data['ModuleEntityID']  = $category_id;

        $this->load->view($this->layout, $this->data);
    }
}
