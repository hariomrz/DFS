<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pages extends Common_Controller
{

    public $page_name = 'pages';
    public $dashboard = 'Pages';

    public function __construct()
    {
        parent::__construct();
        $this->data['pname'] = 'pages';
        $this->lang->load('page');
        $this->load->model('ratings/rating_model');
        $this->data['IsRatingPage'] = 0;
        $this->data['IsPage'] = 1;
        if ($this->settings_model->isDisabled(18))
        {
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout, $this->data);
            $this->output->_display();
            exit();
        }        
        $this->load->model(array('users/login_model', 'flag_model', 'pages/page_model'));
    }

    /* this function is used to get user page list using loginSessionKey */

    public function index()
    {
        $this->data['title'] = 'Pages';
        $this->data['content_view'] = 'pages/userPageList';
        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'));
        $this->load->view($this->layout, $this->data);
    }

    /* used to get page types from database */

    public function types()
    {
        $this->data['title'] = 'Pages';
        $this->data['content_view'] = 'pages/PageList';
        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'));
        $this->load->view($this->layout, $this->data);
    }

    /* used to create page
     * Input Parameter : CategoryID
     */

    public function createPage()
    {
        $segment = $this->uri->uri_to_assoc(1);
        $this->data['CategoryID'] = $segment['pages'];
        $this->data['title'] = 'Pages';
        $this->data['content_view'] = 'pages/createPage';
        $this->data['CategoryDetails'] = $this->page_model->get_category_details($this->data['CategoryID']);
        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'));
        $this->load->view($this->layout, $this->data);
    }

    /* used to edit page details using PageGuID
     * Input Parameter : PageGuID
     */

    public function edit_page()
    {
        $segment = $this->uri->uri_to_assoc(2);
        $PageGUID = $segment['edit_page'];
        $UserID = $this->session->userdata('UserID');

        $ModuleRoleID = CheckPermission($UserID, 18, $PageGUID, 'user');

        if (!$ModuleRoleID)
        {
            redirect('/pages');
        }

        $this->data['PageGUID'] = $PageGUID;

        $this->data['MainCatID'] = $this->page_model->get_page_category($PageGUID);
        $pageRateValue = $this->get_page_rate_value($PageGUID);
        $this->data['RatingValue'] = $pageRateValue['Value'];
        $this->data['RatingClass'] = $pageRateValue['RatingClass'];

        $this->data['title'] = 'Pages';
        $this->data['content_view'] = 'pages/edit_page';
        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'), 'PageGUID' => $this->data['PageGUID']);
        $this->load->view($this->layout, $this->data);
    }

    /* used to display page details using PageGUID
     * Input Parameter : PageGuID
     */

    public function pageDetails($page_url, $is_activity = '', $activity_guid = '')
    {
        $this->data['PageURL'] = $page_url;
        $this->data['title'] = 'Wall';
        $this->data['content_view'] = 'pages/page_detail';

        $page_details = $this->page_model->get_page_detail_by_page_url($page_url);
        if (!$page_details)
        {
            redirect(site_url('404'));
        }

        $page_guid = $page_details['PageGUID'];
        $page_id = $page_details['PageID'];
        $user_id = $this->session->userdata('UserID');
        
        if (check_blocked_user($user_id, 18, $page_id))
        {
            redirect(site_url('dashboard'));
        }

        $this->load->model('notification_model');
        $this->notification_model->mark_notifications_as_read($user_id, $page_id, 'PAGE');

        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'), 'PageGUID' => $page_guid);

        // Wall parameters start    

        $page_rate_value = $this->get_page_rate_value($page_guid);
        $this->data['RatingValue'] = $page_rate_value['Value'];
        $this->data['RatingClass'] = $page_rate_value['RatingClass'];
        $this->data['UserID'] = $this->page_model->get_page_owner($page_guid);
        $this->data['IsAdmin'] = $this->page_model->get_user_page_permission($page_guid, $user_id);
        $this->data['IsFriend'] = $this->page_model->get_page_post_permission($user_id, $page_guid);
        $this->data['EntityID'] = $page_guid;
        $this->data['PageGUID'] = $page_guid;
        $this->data['ModuleEntityGUID'] = $page_guid;
        $this->data['ModuleEntityID'] = $page_id;
        /* $this->data['WallTypeID'] = 2; */
        $this->data['type'] = '';
        $this->data['ModuleID'] = '18';
        $this->data['IsFollowerPage'] = 0;
        $this->data['ActivityGUID'] = $activity_guid;

        $this->data['CoverImageState'] = get_cover_image_state($user_id, $page_id, 18);

        $this->load->model('subscribe_model');
        $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'PAGE', $page_id);

        $this->data['pname'] = 'wall';
        // Wall parameters End

        if (!empty($activity_guid))
        {
            $activity_id = get_detail_by_guid($activity_guid, 0, "ActivityID");
            if (!empty($activity_id))
            {
                $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'PAGE_POST');
            }
            $this->data['hidemedia'] = true;
        }
        $this->load->view($this->layout, $this->data);
    }

    public function ratings($PageURL, $RatingGUID = '')
    {
        $this->load->language('rating');
        $this->data['PageURL'] = $PageURL;
        $this->data['title'] = 'Ratings';
        $this->data['content_view'] = 'rating/ratings';
        $this->data['IsRating'] = 1;
        $this->data['IsRatingPage'] = 1;

        if($this->settings_model->isDisabled(23)){
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout,$this->data);
            $this->output->_display();
            exit();
        }

        $pageInfo = $this->page_model->get_page_detail_by_page_url($PageURL);
        if (!$pageInfo)
        {
            redirect(site_url('404'));
        }
        $PageGUID = $pageInfo['PageGUID'];

        $pageRateValue = $this->get_page_rate_value($PageGUID);
        $this->data['RatingValue'] = $pageRateValue['Value'];
        $this->data['RatingClass'] = $pageRateValue['RatingClass'];

        $PageID = $pageInfo['PageID'];
        $UserID = $this->session->userdata('UserID');
        if (check_blocked_user($UserID, 18, $PageID))
        {
            redirect(site_url('dashboard'));
        }

        if (!empty($RatingGUID))
        {
            $rating_id = get_detail_by_guid($RatingGUID, 23, 'RatingID');
            if ($rating_id)
            {
                $this->load->model('notification_model');
                $this->notification_model->mark_notifications_as_read($UserID, $rating_id, 'RATING');
            }
        }

        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'), 'PageGUID' => $PageGUID);

        // Wall parameters start
        $this->data['PageGUID'] = $PageGUID;
        $this->data['UserID'] = $this->page_model->get_page_owner($PageGUID);
        $this->data['IsAdmin'] = $this->page_model->get_user_page_permission($PageGUID, $UserID);
        $this->data['IsFriend'] = $this->page_model->get_page_post_permission($UserID, $PageGUID);
        $this->data['EntityID'] = $PageGUID;
        $this->data['ModuleEntityGUID'] = $PageGUID;
        /* $this->data['WallTypeID'] = 2; */
        $this->data['type'] = '';
        $this->data['ModuleID'] = '18';
        $this->data['IsFollowerPage'] = 0;
        $this->data['RatingGUID'] = $RatingGUID;
        $this->data['pname'] = 'ratings';

        $this->data['CoverImageState'] = get_cover_image_state($UserID, $PageID, 18);

        $this->load->model('subscribe_model');
        $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($UserID, 'PAGE', $PageID);
        // Wall parameters End

        $this->load->view($this->layout, $this->data);
    }

    /* used to display page followers list using PageGUID
     * Input Parameter : PageGuID
     */

    public function followers($page_url)
    {       
        
        $page_info = $this->page_model->get_page_detail_by_page_url($page_url);
        if (!$page_info) {
            redirect(site_url('404'));
        }
        $PageGUID = $page_info['PageGUID'];
        
        $this->data['IsFollowerPage'] = 1;
        $this->data['PageGUID'] = $PageGUID;
        $this->data['title'] = 'Followers';

        $PageID = get_detail_by_guid($PageGUID, 18, 'PageID');
        $UserID = $this->session->userdata('UserID');        
        if (check_blocked_user($UserID, 18, $PageID))
        {
            redirect(site_url('dashboard'));
        }

        $this->load->model('notification_model');
        $this->notification_model->mark_notifications_as_read($UserID, $PageID, 'PAGE');

        $pageRateValue = $this->get_page_rate_value($PageGUID);
        $this->data['RatingValue'] = $pageRateValue['Value'];
        $this->data['RatingClass'] = $pageRateValue['RatingClass'];

        // Wall parameters start
        $this->data['UserID'] = $this->page_model->get_page_owner($PageGUID);
        $this->data['IsAdmin'] = $this->page_model->get_user_page_permission($PageGUID, $UserID);
        $this->data['IsFriend'] = $this->page_model->get_page_post_permission($UserID, $PageGUID);
        $this->data['EntityID'] = $PageGUID;
        $this->data['ModuleEntityGUID'] = $PageGUID;
        $this->data['WallTypeID'] = 2;
        $this->data['type'] = '';
        $this->data['ModuleID'] = '18';
        $this->data['pname'] = 'followers';

        $this->data['CoverImageState'] = get_cover_image_state($UserID, $PageID, 18);

        $this->load->model('subscribe_model');
        $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($UserID, 'PAGE', $PageID);
        // Wall parameters End

        $this->data['content_view'] = 'pages/page_followers';
        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'), 'PageGUID' => $this->data['PageGUID']);
        $this->load->view($this->layout, $this->data);
    }

    public function event($page_url)
    {
        if($this->settings_model->isDisabled(14)){ // Check if event module is disabled
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout,$this->data);
            $this->output->_display();
            exit();
        }

        $page_info = $this->page_model->get_page_detail_by_page_url($page_url);
        if (!$page_info)
        {
            redirect(site_url('404'));
        }
        $PageGUID = $page_info['PageGUID'];

        $this->data['IsFollowerPage'] = 1;
        $this->data['PageGUID'] = $PageGUID;
        $this->data['title'] = 'Event';

        $PageID = get_detail_by_guid($PageGUID, 18, 'PageID');
        $UserID = $this->session->userdata('UserID');        
        if (check_blocked_user($UserID, 18, $PageID))
        {
            redirect(site_url('dashboard'));
        }

        $this->load->model('notification_model');
        $this->notification_model->mark_notifications_as_read($UserID, $PageID, 'PAGE');

        $pageRateValue = $this->get_page_rate_value($PageGUID);
        $this->data['RatingValue'] = $pageRateValue['Value'];
        $this->data['RatingClass'] = $pageRateValue['RatingClass'];

        // Wall parameters start
        $this->data['UserID'] = $this->page_model->get_page_owner($PageGUID);
        $this->data['IsAdmin'] = $this->page_model->get_user_page_permission($PageGUID, $UserID);
        $this->data['IsFriend'] = $this->page_model->get_page_post_permission($UserID, $PageGUID);
        $this->data['EntityID'] = $PageGUID;
        $this->data['ModuleEntityGUID'] = $PageGUID;
        $this->data['ModuleEntityID'] = $PageID;
        $this->data['WallTypeID'] = 2;
        $this->data['type'] = '';
        $this->data['ModuleID'] = '18';
        $this->data['pname'] = 'event';

        $this->data['CoverImageState'] = get_cover_image_state($UserID, $PageID, 18);

        $this->load->model('subscribe_model');
        $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($UserID, 'PAGE', $PageID);
        // Wall parameters End

        $this->data['content_view'] = 'pages/page_event';
        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'), 'PageGUID' => $this->data['PageGUID']);
        $this->load->view($this->layout, $this->data);
    }

    public function get_page_rate_value($PageGUID)
    {
        $getRatingDetails = $this->rating_model->get_overall_ratings(18, $PageGUID);
        $RateValue = 0;
        if ($getRatingDetails)
        {
            if ($getRatingDetails['TotalRateValue'] > 0 && $getRatingDetails['TotalRecords'] > 0)
            {
                $RateValue = $getRatingDetails['TotalRateValue'] / $getRatingDetails['TotalRecords'];
            }
        }
        $value = 0;
        $RatingClass = 'badgerate-1';
        if ($RateValue >= 0.26 && $RateValue <= 0.5)
        {
            $value = 0.5;
            $RatingClass = 'badgerate-1';
        }
        else if ($RateValue > 0.5 && $RateValue <= 1.25)
        {
            $value = 1;
            $RatingClass = 'badgerate-1';
        }
        else if ($RateValue >= 1.26 && $RateValue <= 1.75)
        {
            $value = 1.5;
            $RatingClass = 'badgerate-1';
        }
        else if ($RateValue >= 1.76 && $RateValue <= 2.25)
        {
            $value = 2;
            $RatingClass = 'badgerate-2';
        }
        else if ($RateValue >= 2.26 && $RateValue <= 2.75)
        {
            $value = 2.5;
            $RatingClass = 'badgerate-2';
        }
        else if ($RateValue >= 2.76 && $RateValue <= 3.25)
        {
            $value = 3;
            $RatingClass = 'badgerate-3';
        }
        else if ($RateValue >= 3.26 && $RateValue <= 3.75)
        {
            $value = 3.5;
            $RatingClass = 'badgerate-3';
        }
        else if ($RateValue >= 3.76 && $RateValue <= 4.25)
        {
            $value = 4;
            $RatingClass = 'badgerate-4';
        }
        else if ($RateValue >= 4.26 && $RateValue <= 4.75)
        {
            $value = 4.5;
            $RatingClass = 'badgerate-4';
        }
        else if ($RateValue >= 4.76)
        {
            $value = 5;
            $RatingClass = 'badgerate-5';
        }
        return array('Value' => $value, 'RatingClass' => $RatingClass);
    }

    public function media($page_url, $action = 'list', $album_guid = '')
    {
        $this->data['IsMedia'] = 1;
        $page_info = $this->page_model->get_page_detail_by_page_url($page_url);
        if (!$page_info)
        {
            redirect(site_url('404'));
        }
        $page_guid = $page_info['PageGUID'];

        $this->data['IsFollowerPage'] = 0;
        $pageRateValue = $this->get_page_rate_value($page_guid);
        $this->data['RatingValue'] = $pageRateValue['Value'];
        $this->data['RatingClass'] = $pageRateValue['RatingClass'];
        $page_id = get_detail_by_guid($page_guid, 18, 'PageID');
        $user_id = $this->session->userdata('UserID');        
        if (check_blocked_user($user_id, 18, $page_id))
        {
            redirect(site_url('dashboard'));
        }

        $this->load->model('notification_model');
        $this->notification_model->mark_notifications_as_read($user_id, $page_id, 'PAGE');

        //Check current user's permissions
        $this->data['no_permission'] = false;
        $permissions = $this->page_model->check_page_owner($user_id, $page_id);

        // Merging permissions with preset data.
        $this->data['IsAdmin'] = 0;
        $this->data['IsCreator'] = 0;
        if ($permissions)
        {
            $this->data['IsAdmin'] = 1;
            $this->data['IsCreator'] = 1;
        }
        $this->data['albumMod'] = 'list';
        $this->data['moduleSection'] = 'page';
        //check page 

        if (($action == 'create' || $action == 'edit') && $this->data['IsAdmin'] != 1)
        {
            $this->data['no_permission'] = true;
        }

        if ($action == 'create' && $this->data['IsAdmin'] == 1)
        {
            $this->data['albumMod'] = 'create';
            $this->data['albumHeading'] = 'Create Album';
            $this->data['albumAddMedia'] = 'Add media';
            $status_id = 2;
        }
        else if ($action == 'edit' && $this->data['IsAdmin'] == 1)
        {
            $this->data['albumMod'] = 'edit';
            $this->data['albumHeading'] = 'Edit Album';
            $this->data['albumAddMedia'] = 'Add more media';
            $status_id = 2;
        }
        else if ($action != 'list')
        {
            $album_guid = $action;
            $status_id = get_detail_by_guid($action, 13, "StatusID");
            $this->data['albumMod'] = 'detail';
        }

        $this->data['IsFriend'] = $this->page_model->get_page_post_permission($user_id, $page_guid);

        $this->data['AlbumGUID'] = $album_guid;
        $this->data['pname'] = 'media';
        $user_guid = get_guid_by_id($user_id, 3);
        $this->data['PageGUID'] = $page_guid;
        $this->data['sectionGUID'] = $page_url;
        $this->data['UserGUID'] = $user_guid;
        $this->data['UserID'] = $this->page_model->get_page_owner($page_id);
        $this->data['title'] = 'Media';
        $this->data['PageID'] = $page_id;
        $this->data['EntityID'] = $page_guid;
        $this->data['ModuleEntityGUID'] = $page_guid;
        $this->data['ModuleID'] = '18';
        $this->data['content_view'] = 'pages/media';

        $this->data['CoverImageState'] = get_cover_image_state($user_id, $page_id, 18);

        $this->load->model('subscribe_model');
        $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'PAGE', $page_id);

        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'), 'PageGUID' => $this->data['PageGUID']);

        if (!empty($album_guid))
        {
            $activity_id = get_detail_by_guid($album_guid, 13, "ActivityID");
            if (!empty($activity_id))
            {
                $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'ALBUM');
            }
        }
        $this->load->view($this->layout, $this->data);
    }

    public function files($page_url, $action = 'list', $album_guid = '')
    {
        $this->data['IsMedia'] = 1;
        $this->data['isFileTab'] = true;
        $page_info = $this->page_model->get_page_detail_by_page_url($page_url);
        if (!$page_info)
        {
            redirect(site_url('404'));
        }
        $page_guid = $page_info['PageGUID'];

        $this->data['IsFollowerPage'] = 0;
        $pageRateValue = $this->get_page_rate_value($page_guid);
        $this->data['RatingValue'] = $pageRateValue['Value'];
        $this->data['RatingClass'] = $pageRateValue['RatingClass'];
        $page_id = get_detail_by_guid($page_guid, 18, 'PageID');
        $user_id = $this->session->userdata('UserID');        
        if (check_blocked_user($user_id, 18, $page_id))
        {
            redirect(site_url('dashboard'));
        }

        $this->load->model('notification_model');
        $this->notification_model->mark_notifications_as_read($user_id, $page_id, 'PAGE');

        //Check current user's permissions
        $this->data['no_permission'] = false;
        $permissions = $this->page_model->check_page_owner($user_id, $page_id);

        // Merging permissions with preset data.
        $this->data['IsAdmin'] = 0;
        $this->data['IsCreator'] = 0;
        if ($permissions)
        {
            $this->data['IsAdmin'] = 1;
            $this->data['IsCreator'] = 1;
        }
        $this->data['albumMod'] = 'list';
        $this->data['moduleSection'] = 'page';
        //check page 

        if (($action == 'create' || $action == 'edit') && $this->data['IsAdmin'] != 1)
        {
            $this->data['no_permission'] = true;
        }

        if ($action == 'create' && $this->data['IsAdmin'] == 1)
        {
            $this->data['albumMod'] = 'create';
            $this->data['albumHeading'] = 'Create Album';
            $this->data['albumAddMedia'] = 'Add media';
            $status_id = 2;
        }
        else if ($action == 'edit' && $this->data['IsAdmin'] == 1)
        {
            $this->data['albumMod'] = 'edit';
            $this->data['albumHeading'] = 'Edit Album';
            $this->data['albumAddMedia'] = 'Add more media';
            $status_id = 2;
        }
        else if ($action != 'list')
        {
            $album_guid = $action;
            $status_id = get_detail_by_guid($action, 13, "StatusID");
            $this->data['albumMod'] = 'detail';
        }

        $this->data['IsFriend'] = $this->page_model->get_page_post_permission($user_id, $page_guid);

        $this->data['AlbumGUID'] = $album_guid;
        $this->data['pname'] = 'files';
        $user_guid = get_guid_by_id($user_id, 3);
        $this->data['PageGUID'] = $page_guid;
        $this->data['sectionGUID'] = $page_url;
        $this->data['UserGUID'] = $user_guid;
        $this->data['UserID'] = $this->page_model->get_page_owner($page_id);
        $this->data['title'] = 'Media';
        $this->data['PageID'] = $page_id;
        $this->data['EntityID'] = $page_guid;
        $this->data['ModuleEntityGUID'] = $page_guid;
        $this->data['ModuleID'] = '18';
        $this->data['content_view'] = 'pages/files';

        $this->data['CoverImageState'] = get_cover_image_state($user_id, $page_id, 18);

        $this->load->model('subscribe_model');
        $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'PAGE', $page_id);

        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'), 'PageGUID' => $this->data['PageGUID']);

        if (!empty($album_guid))
        {
            $activity_id = get_detail_by_guid($album_guid, 13, "ActivityID");
            if (!empty($activity_id))
            {
                $this->notification_model->mark_notifications_as_read($user_id, $activity_id, 'ALBUM');
            }
        }
        $this->load->view($this->layout, $this->data);
    }

    public function links($page_url, $action = 'list') 
    {
        $this->data['isLinkTab'] = true;
        $page_info = $this->page_model->get_page_detail_by_page_url($page_url);
        if ( !$page_info ) {
            redirect(site_url('404'));
        }
        $page_guid = $page_info['PageGUID'];
        $this->data['IsFollowerPage'] = 0;
        $pageRateValue = $this->get_page_rate_value($page_guid);
        $this->data['RatingValue'] = $pageRateValue['Value'];
        $this->data['RatingClass'] = $pageRateValue['RatingClass'];
        $page_id = get_detail_by_guid($page_guid, 18, 'PageID');
        $user_id = $this->session->userdata('UserID');
        if (check_blocked_user($user_id, 18, $page_id))
        {
            redirect(site_url('dashboard'));
        }

        $this->load->model('notification_model');
        $this->notification_model->mark_notifications_as_read($user_id, $page_id, 'PAGE');

        //Check current user's permissions
        $this->data['no_permission'] = false;
        $permissions = $this->page_model->check_page_owner($user_id, $page_id);

        // Merging permissions with preset data.
        $this->data['IsAdmin'] = 0;
        $this->data['IsCreator'] = 0;
        if ($permissions)
        {
            $this->data['IsAdmin'] = 1;
            $this->data['IsCreator'] = 1;
        }
        $this->data['albumMod'] = 'list';
        $this->data['moduleSection'] = 'page';
        //check page 

        if (($action == 'create' || $action == 'edit') && $this->data['IsAdmin'] != 1)
        {
            $this->data['no_permission'] = true;
        }

        $this->data['IsFriend'] = $this->page_model->get_page_post_permission($user_id, $page_guid);

        $this->data['pname'] = 'links';
        $user_guid = get_guid_by_id($user_id, 3);
        $this->data['PageGUID'] = $page_guid;
        $this->data['sectionGUID'] = $page_url;
        $this->data['UserGUID'] = $user_guid;
        $this->data['UserID'] = $this->page_model->get_page_owner($page_id);
        $this->data['title'] = 'Link';
        $this->data['PageID'] = $page_id;
        $this->data['EntityID'] = $page_guid;
        $this->data['ModuleEntityGUID'] = $page_guid;
        $this->data['ModuleID'] = '18';
        $this->data['content_view'] = 'pages/links';

        $this->data['CoverImageState'] = get_cover_image_state($user_id, $page_id, 18);

        $this->load->model('subscribe_model');
        $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'PAGE', $page_id);

        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'), 'PageGUID' => $this->data['PageGUID']);

        $this->load->view($this->layout, $this->data);
    }

    public function about($page_url, $action = 'list', $album_guid = '')
    {
        $page_info = $this->page_model->get_page_detail_by_page_url($page_url);
        if (!$page_info)
        {
            redirect(site_url('404'));
        }
        $page_guid = $page_info['PageGUID'];

        $this->data['IsFollowerPage'] = 0;
        $pageRateValue = $this->get_page_rate_value($page_guid);
        $this->data['RatingValue'] = $pageRateValue['Value'];
        $this->data['RatingClass'] = $pageRateValue['RatingClass'];
        $page_id = get_detail_by_guid($page_guid, 18, 'PageID');
        $user_id = $this->session->userdata('UserID');
        if (check_blocked_user($user_id, 18, $page_id))
        {
            redirect(site_url('dashboard'));
        }

        $this->load->model('notification_model');
        $this->notification_model->mark_notifications_as_read($user_id, $page_id, 'PAGE');

        //Check current user's permissions
        $this->data['no_permission'] = false;
        $permissions = $this->page_model->check_page_owner($user_id, $page_id);

        // Merging permissions with preset data.
        $this->data['IsAdmin'] = 0;
        $this->data['IsCreator'] = 0;
        if ($permissions)
        {
            $this->data['IsAdmin'] = 1;
            $this->data['IsCreator'] = 1;
        }
        $this->data['albumMod'] = 'list';
        $this->data['moduleSection'] = 'page';


        $this->data['IsFriend'] = $this->page_model->get_page_post_permission($user_id, $page_guid);

        $this->data['pname'] = 'media';
        $user_guid = get_guid_by_id($user_id, 3);
        $this->data['PageGUID'] = $page_guid;
        $this->data['sectionGUID'] = $page_url;
        $this->data['UserGUID'] = $user_guid;
        $this->data['UserID'] = $this->page_model->get_page_owner($page_id);
        $this->data['title'] = 'Media';
        $this->data['PageID'] = $page_id;
        $this->data['EntityID'] = $page_guid;
        $this->data['ModuleEntityGUID'] = $page_guid;
        $this->data['ModuleID'] = '18';
        $this->data['content_view'] = 'pages/about';

        $this->data['CoverImageState'] = get_cover_image_state($user_id, $page_id, 18);

        $this->load->model('subscribe_model');
        $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'PAGE', $page_id);

        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'), 'PageGUID' => $this->data['PageGUID']);

        $this->load->view($this->layout, $this->data);
    }
    public function endorsment($page_url, $action = 'list', $album_guid = '')
    {
        $page_info = $this->page_model->get_page_detail_by_page_url($page_url);
        if (!$page_info)
        {
            redirect(site_url('404'));
        }
        $page_guid = $page_info['PageGUID'];

        $this->data['IsFollowerPage'] = 0;
        $pageRateValue = $this->get_page_rate_value($page_guid);
        $this->data['RatingValue'] = $pageRateValue['Value'];
        $this->data['RatingClass'] = $pageRateValue['RatingClass'];
        $page_id = get_detail_by_guid($page_guid, 18, 'PageID');
        $user_id = $this->session->userdata('UserID');
        if (check_blocked_user($user_id, 18, $page_id))
        {
            redirect(site_url('dashboard'));
        }

        $this->load->model('notification_model');
        $this->notification_model->mark_notifications_as_read($user_id, $page_id, 'PAGE');

        //Check current user's permissions
        $this->data['no_permission'] = false;
        $permissions = $this->page_model->check_page_owner($user_id, $page_id);

        // Merging permissions with preset data.
        $this->data['IsAdmin'] = 0;
        $this->data['IsCreator'] = 0;
        if ($permissions)
        {
            $this->data['IsAdmin'] = 1;
            $this->data['IsCreator'] = 1;
        }
        $this->data['albumMod'] = 'list';
        $this->data['moduleSection'] = 'page';


        $this->data['IsFriend'] = $this->page_model->get_page_post_permission($user_id, $page_guid);

        $this->data['pname'] = 'media';
        $user_guid = get_guid_by_id($user_id, 3);
        $this->data['PageGUID'] = $page_guid;
        $this->data['sectionGUID'] = $page_url;
        $this->data['UserGUID'] = $user_guid;
        $this->data['UserID'] = $this->page_model->get_page_owner($page_id);
        $this->data['title'] = 'Media';
        $this->data['PageID'] = $page_id;
        $this->data['EntityID'] = $page_guid;
        $this->data['ModuleEntityGUID'] = $page_guid;
        $this->data['ModuleID'] = '18';
        $this->data['content_view'] = 'pages/endorsment';

        $this->data['CoverImageState'] = get_cover_image_state($user_id, $page_id, 18);

        $this->load->model('subscribe_model');
        $this->data['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'PAGE', $page_id);

        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'), 'PageGUID' => $this->data['PageGUID']);

        $this->load->view($this->layout, $this->data);
    }

}
