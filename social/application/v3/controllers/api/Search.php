<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Example
* This Class used as REST API for Search module
* @category	Controller
* @author		Vinfotech Team
*/
class Search extends Common_API_Controller 
{   
    /**
     * @Summary: call parent constructor
     * @access: public
     * @param:
     * @return:
     */
    function __construct() 
    {
        parent::__construct();
        $this->load->model(array('users/friend_model','search/search_model'));
        
    }

    /**
     * [index used to serach user]
     * @return [json] [return json boject]
     */
    public function index_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if ($data) {
            $config = array(
                array(
                    'field' => 'Keyword',
                    'label' => 'keyword',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $page_no = safe_array_key($data, 'PageNo', PAGE_NO);
                $page_size = safe_array_key($data, 'PageSize', PAGE_SIZE);

                $keyword = $data['Keyword'];
                $return['Data']['Interest'] = $this->search_model->interest($user_id, $page_no, $page_size, $keyword);
                $return['Data']['Skills'] = $this->search_model->skills($user_id, $page_no, $page_size, $keyword);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * [interest]
     * @return [json] [return json boject]
     */
    public function interest_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if ($data) {
            $config = array(
                array(
                    'field' => 'Keyword',
                    'label' => 'keyword',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $page_no = safe_array_key($data, 'PageNo', PAGE_NO);
                $page_size = safe_array_key($data, 'PageSize', PAGE_SIZE);

                $keyword = $data['Keyword'];
                $return['Data'] = $this->search_model->interest($user_id, $page_no, $page_size, $keyword);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * [get_skills]
     * @return [json] [return json boject]
     */
    public function skills_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if ($data) {
            $config = array(
                array(
                    'field' => 'Keyword',
                    'label' => 'keyword',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $page_no = safe_array_key($data, 'PageNo', PAGE_NO);
                $page_size = safe_array_key($data, 'PageSize', PAGE_SIZE);

                $keyword = $data['Keyword'];
                $return['Data'] = $this->search_model->skills($user_id, $page_no, $page_size, $keyword);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
	 * [user_post used to serach user]
	 * @return [json] [return json boject]
	 */
	public function user_post() {
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;  
        if ($data) {       
            $data   = $this->search_model->user($user_id, $data);
            $return['Data'] = $data;
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
	}

    /**
	 * [profession used to serach profession with user]
	 * @return [json] [return json boject]
	 */
	public function profession_post() {
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;  
        if ($data) { 
            
            $this->load->model(array('users/user_model'));
            $this->user_model->set_friend_followers_list($user_id);
            $followers = array();
            $is_follow_disabled = $this->settings_model->isDisabled(11);
            if(!$is_follow_disabled) {
                $followers = $this->user_model->get_followers_list();  
            }
            $data['IsFollowDisabled'] = $is_follow_disabled;
            $data['Followers'] = $followers;
            $result   = $this->search_model->profession($user_id, $data);
            $return['Data'] = $result;
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
	}

    /**
	 * [profession_users used to serach user based on profession]
	 * @return [json] [return json boject]
	 */
	public function profession_users_post() {
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;  
        if ($data) { 
            $config = array(
                array(
                    'field' => 'ProfessionID',
                    'label' => 'profession id',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $this->load->model(array('users/user_model'));
                $this->user_model->set_friend_followers_list($user_id);
                $followers = array();
                $is_follow_disabled = $this->settings_model->isDisabled(11);
                if(!$is_follow_disabled) {
                    $followers = $this->user_model->get_followers_list();  
                }
                $data['IsFollowDisabled'] = $is_follow_disabled;
                $data['Followers'] = $followers;
                $result   = $this->search_model->profession_users($user_id, $data);
                $return['Data'] = $result;
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
	}




    /**
     * [user_post used to serach user]
     * @return [json] [return json boject]
     */
    public function web_search_post()
    {
        /* Define variables - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        $role_id    = isset($this->RoleID) ? $this->RoleID : '';
        if ($this->form_validation->run('api/activity') == FALSE)
        {
            $error  = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        }
        else
        {
            $entity_guid    = $data['EntityGUID'];
            $module_id      = $data['ModuleID'];
            if ($module_id == 3 && $this->LoggedInGUID == $entity_guid)
            {
                $entity_id = $user_id;
            }
            else
            {
                $entity_id = get_detail_by_guid($entity_guid, $module_id);
            }
            $feed_sort_by       = isset($data['FeedSortBy']) ? $data['FeedSortBy'] : 1;
            $is_media_exists    = isset($data['IsMediaExists']) ? $data['IsMediaExists'] : 2;
            $page_no            = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $page_size          = isset($data['PageSize']) ? $data['PageSize'] : ACTIVITY_PAGE_SIZE;
            $filter_type        = isset($data['ActivityFilterType']) ? $data['ActivityFilterType'] : 0;
            $feed_user          = isset($data['FeedUser']) ? get_detail_by_guid($data['FeedUser'], 3) : 0;
            $search_keyword     = isset($data['SearchKey']) ? $data['SearchKey'] : '';
            $start_date         = (isset($data['StartDate']) && !empty($data['StartDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['StartDate'])) : '';

            $end_date           = (isset($data['EndDate']) && !empty($data['EndDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['EndDate'])) : '';
            $reminder_date      = (isset($data['ReminderFilterDate']) && !empty($data['ReminderFilterDate'])) ? $data['ReminderFilterDate'] : array();

            $all_activity       = isset($data['AllActivity']) ? $data['AllActivity'] : 0;
            $activity_guid      = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
            $mention_module_id  = isset($data['MentionModuleID']) ? $data['MentionModuleID'] : 0;
            $mention_module_entity_id = isset($data['MentionModuleEntityID']) ? $data['MentionModuleEntityID'] : 0;
            $mentions           = isset($data['Mentions']) ? $data['Mentions'] : array();
            $as_owner           = isset($data['AsOwner']) ? $data['AsOwner'] : 0;
            $activity_type_filter = !empty($data['ActivityFilter']) ? $data['ActivityFilter'] : array();
            $comment_guid       = isset($data['CommentGUID']) ? $data['CommentGUID'] : '';
            //View Entity Tags
            $view_entity_tags = isset($data['ViewEntityTags']) ? $data['ViewEntityTags'] : '';
            $comment_id         = '';

            //Advance Search Filters
            $posted_by  = isset($data['PostedBy']) ? $data['PostedBy'] : '0';            
            if(is_array($posted_by))
            {   
                $posted_by_users = array();
                foreach ($posted_by as $userguid) 
                {                                        
                    $posted_user_id = get_detail_by_guid($userguid, 3);
                    if(!empty($posted_user_id))
                        $posted_by_users[] = $posted_user_id;
                }
                $posted_by = $posted_by_users;
            }
            //$tagged  = isset($data['Tagged']) ? $data['Tagged'] : '';
            //$created_date_range_initial  = isset($data['CreatedDateRangeInitial']) ? $data['CreatedDateRangeInitial'] : '';
            //$created_date_range_final  = isset($data['CreatedDateRangeFinal']) ? $data['CreatedDateRangeFinal'] : '';
            $updated_start_date  = isset($data['UpdatedStartDate']) ? $data['UpdatedStartDate'] : '';
            $updated_end_date  = isset($data['UpdatedEndDate']) ? $data['UpdatedEndDate'] : '';
            $include_entity['Archive']  =  isset($data['IncludeArchive']) ? $data['IncludeArchive'] : '0';
            $include_entity['Attachment']  =  isset($data['IncludeAttachment']) ? $data['IncludeAttachment'] : '0';
            $include_entity['UserAndGroup']  =  isset($data['IncludeUserAndGroup']) ? $data['IncludeUserAndGroup'] : '0';
            $search_only_for = isset($data['SearchOnlyFor']) ? $data['SearchOnlyFor'] : '0';//0->ALL, 1-post,2-comment,3-meetings,4-wiki            
            $selected_groups = (isset($data['SelectedGroups']) && $data['SelectedGroups']) ? '1' : '0';
            // $selected_groups = (isset($data['SelectedGroups']) && !empty($data['SelectedGroups'])) ? $data['SelectedGroups'] : array();
            $selected_group_ids = array();
            //Look in for Groups            
            if(isset($selected_groups) && $selected_groups == '1')
            {                   
                /*foreach ($selected_groups as $group_guid) 
                {
                    $group_id = get_detail_by_guid($group_guid,1); 
                    if($group_id)
                    {
                        $selected_group_ids[] = $group_id;
                    }
                }*/
                $selected_group_ids[] = $entity_id;
            }
            //instead use IsMediaExist option
            //$has_attachment  = isset($data['HasAttachment']) ? $data['HasAttachment'] : '1';

            if(!empty($comment_guid))
            {
                $comment_id     = get_detail_by_guid($comment_guid, 20);
            }
            if ($mentions)
            {
                foreach ($mentions as $key => $value)
                {
                    $mentions[$key]['ModuleEntityID'] = get_detail_by_guid($value['ModuleEntityGUID'], $value['ModuleID']);
                }
            }
            
            $this->user_model->set_user_time_zone($user_id);
            $this->user_model->set_user_profile_url($user_id);
            $this->activity_model->set_block_user_list($entity_id, $module_id);            
            $this->user_model->set_friend_followers_list($user_id);
            $this->group_model->set_user_group_list($user_id);
            $this->activity_model->set_user_tagged($user_id);            
            $this->subscribe_model->set_user_subscribed($user_id);            
            $this->favourite_model->set_user_favourite($user_id);            
            $this->flag_model->set_user_flagged($user_id); 
            $this->activity_model->set_user_activity_archive($user_id); 
            $this->forum_model->set_user_category_list($user_id);
            //$this->group_model->set_user_categoty_group_list($user_id);
            
            if($entity_id == $user_id)
            {
                $this->privacy_model->set_privacy_options($user_id);
                $this->event_model->set_user_joined_events($user_id);
                //$this->page_model->set_user_pages_list($user_id);
                
                $this->page_model->set_feed_pages_condition($user_id);  
                
                $FriendFollowersList=$this->user_model->get_friend_followers_list();
                if(!empty($FriendFollowersList)){
                    $this->user_model->set_friends_of_friend_list($user_id,$FriendFollowersList['Friends']);
                }
                   
                //for searching
                $activity = $this->activity_model->search_feed_activities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, $include_entity, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter,array(),$view_entity_tags,$role_id,$posted_by,$updated_start_date,$updated_end_date,$selected_group_ids,$search_only_for);                                        
                
                
                $return['TotalRecords'] = $this->activity_model->search_feed_activities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, $include_entity, 1, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter,array(),$view_entity_tags,$role_id,$posted_by,$updated_start_date,$updated_end_date,$selected_group_ids,$search_only_for);                
            }
            else
            {   
                $module_entity_guid = $this->LoggedInGUID;
                $login_type = 'user';
                $return['TotalFlagRecords'] = 0;
                $entity_module_id = 1;
                if ($login_type == 'user')
                {
                    $entity_module_id = 3;
                }
                $module_entity_id = get_detail_by_guid($module_entity_guid, $entity_module_id);
                $activity = $this->activity_model->search_entity_activities($entity_id, $module_id, $page_no, $page_size, $user_id, $feed_sort_by, $filter_type, $is_media_exists, $activity_guid, $search_keyword, $start_date, $end_date, $feed_user, $as_owner, false, 'ALL', $activity_type_filter, $module_entity_id, $entity_module_id,$comment_id,$view_entity_tags,$role_id,$include_entity,$posted_by,$updated_start_date,$updated_end_date,$selected_group_ids,$search_only_for);
               
                $return['TotalRecords'] = $this->activity_model->search_entity_activities($entity_id, $module_id, 0, 0, $user_id, $feed_sort_by, $filter_type, $is_media_exists, $activity_guid, $search_keyword, $start_date, $end_date, $feed_user, $as_owner, true, 'A.ActivityID', $activity_type_filter, $module_entity_id, $entity_module_id,$comment_id,$view_entity_tags,$role_id,$include_entity,$posted_by,$updated_start_date,$updated_end_date,$selected_group_ids,$search_only_for);
            }
            

            /* Define variables - ends */

            if (count($activity) > 0)
                $return['Data'] = $activity;
            
        }
        $return['LoggedInProfilePicture'] = $this->LoggedInProfilePicture;
        $return['LoggedInName'] = $this->LoggedInName;

        //$this->output->enable_profiler(true);
        $this->response($return);
    } 

    /** added by gautam (moorus search)
    * [used to serach user]
    * @return [json] [return json boject]
    */
    public function index_new_post()
    {
        /* Define variables - starts */
        $return         = $this->return;     
        $data           = $this->post_data;
        /* Define variables - ends */

        /* Validation - starts */
        $validation_rule[]  =   array('field' => 'Type', 'label' => 'Search Type', 'rules' => 'trim|required');
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) 
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } /* Validation - ends */ else{    

     
            /*Define post variables - ends*/

            
            /*Get Final Records*/
            $return['TotalRecords'] = 0;
            $Records = $this->search_model->search($this->UserID, $data);
            if($Records){
                $return['TotalRecords'] = $Records['TotalRecords'];
                $return['Data'] = $Records['Records'];
            }
        }

        $this->response($return);
    }

    /**
    * [use to add/edit/delete search filters]
    * @return [json] [return json boject]
    */
    public function AddEditDeleteFilter_post()
    {
        /* Define variables - starts */
        $return         = $this->return;     
        $data           = $this->post_data;
        /* Define variables - ends */

        /* Validation - starts */
        $validation_rule[]  =   array('field' => 'FilterName', 'label' => 'Filter Name', 'rules' => 'required');

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) 
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } /* Validation - ends */ else{    

            /*Define post variables - starts*/
            $Input['UserID']    = $this->UserID;
            $Input['FilterGUID']  = isset($data['FilterGUID']) ? $data['FilterGUID'] : '' ;        
            $Input['FilterName']  = isset($data['FilterName']) ? $data['FilterName'] : '' ;
            $Input['FilterValues']      = isset($data['FilterValues']) ? $data['FilterValues'] : '' ;
            /*Define post variables - ends*/

            /*Get Final Records*/
            $Records = $this->search_model->AddEditDeleteFilter($Input);
            if($Records){
                $return['Data'] = $Records;
            }
        }

        $this->response($return);
    }


    public function search_user_post($limit=5)
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        
        $validation_rule[]      =    array(
            'field' => 'SearchKeyword',
            'label' => 'Search Keyword',
            'rules' => 'trim|required'
        );
       
        
        $this->form_validation->set_rules($validation_rule); 
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE) 
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } 
        else 
        {
            $search_keyword     = $data['SearchKeyword'];
            $show_friend        = isset($data['ShowFriend'])    ? $data['ShowFriend']  : 0 ;
            if($limit==16)
            {
                $limit         = isset($data['Limit']) ? $data['Limit'] : 16 ;                
            }
            $offset            = isset($data['Offset'])        ? $data['Offset']      : 1 ;
            $location          = isset($data['Location'])      ? $data['Location']    : '' ;
            $age_group         = isset($data['AgeGroup'])      ? $data['AgeGroup']    : '' ;
            $gender            = isset($data['Gender'])        ? $data['Gender']      : '' ;

            $interest          = isset($data['Interest'])       ? $data['Interest'] : array() ;
            
            $skills          = isset($data['Skills'])       ? $data['Skills'] : array() ;

            $education          = isset($data['Education'])       ? $data['Education'] : array() ;

            $workexp          = isset($data['WorkExp'])       ? $data['WorkExp'] : array() ;

            $advance_search = array('Location'=>$location,'AgeGroup'=>$age_group,'Gender'=>$gender,'Interest'=>$interest,'Skills'=>$skills,'Education'=>$education,'WorkExp'=>$workexp);
            $return['Data']    = $this->friend_model->get_user_list($search_keyword, $user_id, $show_friend, array(), $limit, $offset,$advance_search);
        }
        $this->response($return);
    }

    public function tag_post()
    {
        /* Define variables - starts */
        $return         = $this->return;     
        $data           = $this->post_data;
        /* Define variables - ends */
        
        $validation_rule[]      =    array(
            'field' => 'SearchKeyword',
            'label' => 'Search Keyword',
            'rules' => 'trim|required'
        );
        
        $this->form_validation->set_rules($validation_rule); 
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else {
            $type    = safe_array_key($data, 'type', 0);
            $search_keyword     = $data['SearchKeyword'];
            $user_id = $this->UserID;
            if($type==1) {
                $return['Data'] = $this->search_model->get_all_tags($search_keyword);
            } else {
                $return['Data'] = $this->search_model->get_tags($search_keyword,$user_id);
            }
            
        }
        $this->response($return);
    }	

    /**
     * [group_post used to serach groups]
     * @return [json] [return json boject]
     */
    public function group_post()
    {
        /* Defined variables - starts */
        $return         = $this->return;
        $data           = $this->post_data;
        $user_id        = $this->UserID;
        /* Defined variables - ends */
        
        if ($this->form_validation->run('api/search/group') == FALSE) 
        { // for web
            $error = $this->form_validation->rest_first_error_string();         
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } 
        else 
        {
            $listing_type = $data['ListingType'];
            $search_keyword  = isset($data['SearchText'])   ? $data['SearchText']   : '' ;
            $category_id  = isset($data['CategoryID'])   ? $data['CategoryID']   : '' ;
            $privacy_type = isset($data['PrivacyType'])  ? $data['PrivacyType']  : '-1' ;
            $order_by     = isset($data['OrderBy'])      ? $data['OrderBy']      : 'DESC' ;
            $sort_by     = isset($data['SortBy'])      ? $data['SortBy']      : '' ;
            $page_no      = isset($data['PageNo'])       ? $data['PageNo']       : 1 ;
            $page_size    = isset($data['PageSize'])        ? $data['PageSize']        : CONST_PAGE_SIZE ;
           
            $this->load->model('group/group_model');
           
            $return['Data']['TotalRecords'] = $this->group_model->lists($user_id, TRUE, $search_keyword, $page_no, $page_size, 'All', $sort_by, $order_by , $category_id, $privacy_type, 1);

            $return['Data']['Groups'] = $this->group_model->lists($user_id, FALSE, $search_keyword, $page_no, $page_size, 'All', $sort_by, $order_by, $category_id, $privacy_type, 1);

        }
        $this->response($return);
    }

    /**
     * [event_post used to serach events]
     * @return [json] [return json boject]
     */
    public function event_post()
    {
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        /* Defined varibles - ends */

        if($this->form_validation->run('api/search/event') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $search_text = $data['SearchKeyword'];
            $location = isset($data['Location']) ? $data['Location'] : '' ;
            $cities = isset($data['Cities']) ? $data['Cities'] : '' ;
            $from_date = isset($data['DateFrom']) ? date('Y-m-d',strtotime($data['DateFrom'])) : '' ;
            $to_date = isset($data['DateTo']) ? date('Y-m-d',strtotime($data['DateTo'])) : '' ;
            $limit = isset($data['Limit']) ? $data['Limit'] : '' ;
            $offset = isset($data['Offset']) ? $data['Offset'] : '' ;
            $posted_by = isset($data['PostedBy']) ? $data['PostedBy'] : 'Anyone' ;
            $posted_by_users = isset($data['PostedByUsers']) ? $data['PostedByUsers'] : array() ;
            $sort_by = isset($data['SortBy']) ? $data['SortBy'] : '' ;
            $this->load->model('events/event_model');
            $return['Data'] = $this->event_model->get_filtered_events($user_id, $search_text, $location, $from_date, $to_date, $offset,$limit,0,$posted_by,$sort_by,$posted_by_users,$cities);
            $return['TotalRecords'] = $this->event_model->get_filtered_events($user_id, $search_text, $location, $from_date, $to_date, $offset,$limit,  1,$posted_by,$sort_by,$posted_by_users,$cities);
        }
        $this->response($return);
    }

    /**
     * [event_post used to serach events]
     * @return [json] [return json boject]
     */
    public function photo_post() {
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        /* Defined varibles - ends */

        if($this->form_validation->run('api/search/photo') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $this->load->model('users/user_model');
            $this->user_model->set_friend_followers_list($user_id);
            $search_text = $data['SearchKeyword'];            
            $posted_by = isset($data['PostedBy']) ? $data['PostedBy'] : 'Anyone' ;
            $posted_by_users = isset($data['PostedByUsers']) ? $data['PostedByUsers'] : array() ;
            $tag = isset($data['Tag']) ? $data['Tag'] : '' ;
            $tag_in_users = isset($data['TaggedInUsers']) ? $data['TaggedInUsers'] : array() ;
            $order_by     = isset($data['OrderBy']) ? $data['OrderBy']      : 'DESC' ;
            $sort_by     = isset($data['SortBy'])   ? $data['SortBy']      : '' ;
            $page_no      = isset($data['PageNo'])  ? $data['PageNo']       : 1 ;
            $page_size    = isset($data['PageSize']) ? $data['PageSize']        : CONST_PAGE_SIZE ;
            $this->load->model('media/media_model');
            $return['Data'] = $this->media_model->get_search_photo($user_id, $search_text, $posted_by, $tag, $posted_by_users, $tag_in_users, $page_no, $page_size, false, $sort_by, $order_by);
            $return['TotalRecords'] = $this->media_model->get_search_photo($user_id, $search_text, $posted_by, $tag, $posted_by_users, $tag_in_users, $page_no, $page_size, true, $sort_by, $order_by);
        }
        $this->response($return);
    }

    /**
     * [event_post used to serach events]
     * @return [json] [return json boject]
     */
    public function video_post()
    {
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        /* Defined varibles - ends */

        if($this->form_validation->run('api/search/video') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $this->load->model('users/user_model');
            $this->user_model->set_friend_followers_list($user_id);
            $search_text = $data['SearchKeyword'];
            
            $posted_by = isset($data['PostedBy']) ? $data['PostedBy'] : 'Anyone' ;
            $posted_by_users = isset($data['PostedByUsers']) ? $data['PostedByUsers'] : array() ;
            $tag = isset($data['Tag']) ? $data['Tag'] : '' ;
            $tag_in_users = isset($data['TaggedInUsers']) ? $data['TaggedInUsers'] : array() ;

            $order_by     = isset($data['OrderBy']) ? $data['OrderBy']      : 'DESC' ;
            $sort_by     = isset($data['SortBy'])   ? $data['SortBy']      : '' ;
            $page_no      = isset($data['PageNo'])       ? $data['PageNo']       : 1 ;
            $page_size    = isset($data['PageSize'])        ? $data['PageSize']        : CONST_PAGE_SIZE ;

            $this->load->model('media/media_model');
            $return['Data'] = $this->media_model->get_search_video($user_id,$search_text,$posted_by,$tag,$posted_by_users,$tag_in_users,$page_no,$page_size,false,$sort_by, $order_by);
            $return['TotalRecords'] = $this->media_model->get_search_video($user_id,$search_text,$posted_by,$tag,$posted_by_users,$tag_in_users,$page_no,$page_size,true,$sort_by, $order_by);
        }
        $this->response($return);
    }

    /**
     * [page_post used to serach events]
     * @return [json] [return json boject]
     */
    public function page_post()
    {
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id     = $this->UserID;
        /* Defined varibles - ends */

        $search_text = isset($data['SearchText']) ? $data['SearchText'] : '' ;
        $category_id = isset($data['CategoryID']) ? $data['CategoryID'] : 0 ;
        $limit = isset($data['PageSize']) ? $data['PageSize'] : CONST_PAGE_SIZE ;
        $offset = isset($data['PageNo']) ? $data['PageNo'] : 1 ;
        $city_id = isset($data['CityID']) ? $data['CityID'] : 0 ;
        $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : '' ; 
        $this->load->model('pages/page_model');
        $return['Data'] = $this->page_model->get_filtered_pages($user_id, $search_text, $category_id, $limit, $offset,0,$city_id,$order_by);
        $return['TotalRecords'] = $this->page_model->get_filtered_pages($user_id, $search_text, $category_id, 0, 0, 1,$city_id,$order_by);
        $this->response($return);
    }

    public function all_get()
    {
        $this->all_post();
    }

    public function all_post($limit=16) { 
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        /* Defined varibles - ends */

        $search_keyword     = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $show_friend        = isset($data['ShowFriend']) ? $data['ShowFriend'] : 0 ;
        if($limit==16) {
            $limit          = isset($data['Limit']) ? $data['Limit'] : 16 ;                
        }
        $offset             = isset($data['Offset']) ? $data['Offset'] : 1 ;
        $location           = isset($data['Location']) ? $data['Location'] : '' ;
        $age_group          = isset($data['AgeGroup']) ? $data['AgeGroup'] : '' ;
        $gender             = isset($data['Gender']) ? $data['Gender'] : '' ;
        $advance_search     = array('Location'=>$location,'AgeGroup'=>$age_group,'Gender'=>$gender);
        //$return['Data']   = $this->friend_model->get_user_list($search_keyword, $user_id, $show_friend, array(), $limit, $offset, $advance_search);
        $return['Data']     = $this->search_model->top_search($user_id, $search_keyword);
       /* if(!$return['Data'])
        {
            $this->load->model('pages/page_model');
            $return['Data'] = $this->page_model->get_filtered_pages($user_id,$search_keyword,0,$limit,$offset);
            if(!$return['Data'])
            {
                $search_text = $search_keyword;
                $location = isset($data['Location']) ? $data['Location'] : '' ;
                $from_date = isset($data['DateFrom']) ? date('Y-m-d',strtotime($data['DateFrom'])) : '' ;
                $to_date = isset($data['DateTo']) ? date('Y-m-d',strtotime($data['DateTo'])) : '' ;
                $limit = isset($data['Limit']) ? $data['Limit'] : 5 ;
                $offset = isset($data['Offset']) ? $data['Offset'] : 1 ;
                $this->load->model('events/event_model');
                $return['Data'] = $this->event_model->get_filtered_events($user_id, $search_text, $location, $from_date, $to_date, $limit, $offset);
                if($return['Data'])
                {
                    $data = array();
                    foreach ($return['Data'] as $key => $value) 
                    {
                        $value->ModuleID = 14;
                        $data[] = $value;
                    }
                    $return['Data'] = $data;    
                }
            } 
            else 
            {
                $data = array();
                foreach ($return['Data'] as $key => $value) 
                {
                    $value['ModuleID'] = 18;
                    $data[] = $value;
                }
                $return['Data'] = $data;
            }
        } 
        else 
        {
            /*$data = array();
            foreach ($return['Data'] as $key => $value) 
            {
                $value['ModuleID'] = 3;
                $data[] = $value;
            }
            $return['Data'] = $data;
            * /
        } */
        $this->response($return);
    }

    public function user_n_group_post()
    {
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        /* Defined varibles - ends */

        $search_keyword = $data['SearchKeyword'];
        $page_no    = isset($data['PageNo']) ? $data['PageNo'] : '';
        $page_size  = isset($data['PageSize']) ? $data['PageSize'] : '';
        $return['Data'] = $this->search_model->user_n_group($search_keyword, $page_no, $page_size);
        $this->response($return);
    }


    /**
     * [get_city_list]
     * @return [json] [return json boject]
     */
    public function get_city_list_post() {
        $this->get_cities();
    }
    
    /**
     * [get_cities]
     * @return [json] [return json boject]
     */
    public function get_cities() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        $validation_rule[]      =    array(
            'field' => 'SearchKeyword',
            'label' => 'Search Keyword',
            'rules' => 'trim|required'
        );
        
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else {
            $search_keyword  = safe_array_key($data, 'SearchKeyword'); 
            $return['Data'] = $this->search_model->get_city_list($search_keyword);
        }
        $this->response($return);
    }

    /**
     * [get_city_list]
     * @return [json] [return json boject]
     */
    public function get_school_list_post()
    {
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        /* Defined varibles - ends */

        $keyword = isset($data['Keyword']) ? $data['Keyword'] : '' ;
        $return['Data'] = $this->search_model->get_school_list($keyword);
        $this->response($return);
    }

    /**
     * [get_city_list]
     * @return [json] [return json boject]
     */
    public function get_company_list_post()
    {
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        /* Defined varibles - ends */

        $keyword = isset($data['Keyword']) ? $data['Keyword'] : '' ;
        $return['Data'] = $this->search_model->get_company_list($keyword);
        $this->response($return);
    }

    /**
     * [get_category]
     * @return [json] [return json boject]
     */
    public function get_category_post()
    {
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        /* Defined varibles - ends */

        $keyword = isset($data['Keyword']) ? $data['Keyword'] : '' ;
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : '' ;
        $return['Data'] = $this->search_model->get_categories($keyword,0,$module_id);
        $this->response($return);
    }

    /**
     * [get_user_details]
     * @return [json] [return json boject]
     */
    public function get_user_details_post()
    {
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        /* Defined varibles - ends */

        $keyword = isset($data['Keyword']) ? $data['Keyword'] : '' ;

        $return['Data'] = $this->search_model->get_user_details($user_id,$keyword);
        $this->response($return);
    }
    
    public function entity_home_get()
    {
        $this->entity_home_post();
    }

    public function entity_home_post($limit=16)
    {
        /* Defined varibles - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        /* Defined varibles - ends */

        $search_keyword    = $data['SearchKeyword'];
        $show_friend       = isset($data['ShowFriend']) ? $data['ShowFriend'] : 0 ;
        if($limit==16){
            $limit         = isset($data['Limit']) ? $data['Limit'] : 16 ;                
        }
        $offset            = isset($data['Offset']) ? $data['Offset'] : 1 ;
        $location          = isset($data['Location']) ? $data['Location'] : '' ;
        $age_group         = isset($data['AgeGroup']) ? $data['AgeGroup'] : '' ;
        $gender            = isset($data['Gender']) ? $data['Gender'] : '' ;
        $advance_search = array('Location'=>$location,'AgeGroup'=>$age_group,'Gender'=>$gender);
        //$return['Data']    = $this->friend_model->get_user_list($search_keyword, $user_id, $show_friend, array(), $limit, $offset, $advance_search);
        $return['Data']    = $this->search_model->top_search_home($user_id, $search_keyword);
        
        $this->response($return);
    }
}
