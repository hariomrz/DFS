<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_helper extends Common_API_Controller {

    function __construct($bypass = false) {    
        parent::__construct($bypass);
        
        $this->load->model(array(
            'users/user_model', 
            'activity/activity_front_helper_model', 
        ));
    }
    
    /**
    * Function Name: set_promoted_status
    * @param ActivityID
    * Description: Set activity as promoted status
    */
    public function set_promotion_status_post() {
        /* Define variables - starts */
        $return     = $this->return;
        //$return['TotalRecords'] = 0;/* added by gautam*/
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        
        $is_admin = $this->user_model->is_super_admin($user_id, 1);
        
        if(!$is_admin) {
            $error  = lang('permission_denied');
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            //$this->response($return);
        }
        
        $validation_rule =array(
            array(
                'field' => 'ActivityGUID',
                'label' => 'Activity GUID',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'IsPromoted',
                'label' => 'IsPromoted',
                'rules' => 'trim|required|integer',
            ),
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error  = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
        $activity_id = get_detail_by_guid($data['ActivityGUID'],0,'ActivityID');
                    
        if($activity_id) {
            $is_promoted = (int)isset($data['IsPromoted']) ? $data['IsPromoted'] : 0;
            $this->activity_front_helper_model->set_promotion($activity_id, 0, '', $is_promoted);
            
            $return['Message'] = ($is_promoted) ? 'Promoted.' : 'Unpromoted.';
        } else {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = sprintf(lang('valid_value'), "activity guid");
        }
        $this->response($return);
    }
    
    public function get_entity_bradcrumbs_post() {
        /* Define variables - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        
        $validation_rule =array(
            array(
                'field' => 'ModuleID',
                'label' => 'ModuleID',
                'rules' => 'trim|required|integer',
            ),
            array(
                'field' => 'ModuleEntityID',
                'label' => 'ModuleEntityID',
                'rules' => 'trim|required|integer',
            ),
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error  = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
        
        $return['Data'] = $this->activity_front_helper_model->get_entity_bradcrumbs($data['ModuleID'], $data['ModuleEntityID']);
        $this->response($return);
    }

    public function set_activity_title_post() {
         /* Define variables - starts */
         $return     = $this->return;
         $data       = $this->post_data;
         
         $validation_rule =array(
            array(
                'field' => 'ActivityGUID',
                'label' => 'activity guid',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'Title',
                'label' => 'title',
                'rules' => 'trim|required|max_length[60]',
            ),
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error  = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
        $user_id    = $this->UserID;
        $this->load->model(array(                    
            'users/user_model'
        ));
        $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
        if($is_super_admin) {
            $this->activity_front_helper_model->set_activity_title($data);        
        } else {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('permission_denied');
        }
        $this->response($return);

        
    }
    
    public function delete_activity_title_post() {
        /* Define variables - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        
        $validation_rule =array(
           array(
               'field' => 'ActivityGUID',
               'label' => 'activity guid',
               'rules' => 'trim|required',
           )
       ) ;
       $this->form_validation->set_rules($validation_rule);
       if ($this->form_validation->run() == FALSE) {
           $error  = $this->form_validation->rest_first_error_string();
           $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
           $return['Message'] = $error;
           $this->response($return);
       }
       $user_id    = $this->UserID;
       $this->load->model(array(                    
           'users/user_model'
       ));
       $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
       if($is_super_admin) {
            $data['Title'] = '';
            $this->activity_front_helper_model->set_activity_title($data);        
       } else {
           $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
           $return['Message'] = lang('permission_denied');
       }
       $this->response($return);

       
   }

   /**
     * [top_contributor Get top top contributor]
     */
    public function top_contributor_post() {
      
        /* Define variables - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        $page_no = safe_array_key($data, 'PageNo', 1);

        
        if($page_no == 1) {
            $return['ht'] = "<div>Help Text</div>";
            $point_table = array();
            $point_table[] = array(
                "Title" => "Points when you post",
                "Rules" => array(
                    "When user creates a post", 
                    "When user's post is moved to Classified",
                    "When user's post is moved to City news",
                    "When user's post visibility is moved to All Wards",
                    "Number of likes on a post",
                    "Number of comments on a post for unique people",
                    "On post deletion points will be deleted",
                    "Restriction on maximum points a user can get everyday",
                )
            );
            $point_table[] = array(
                "Title" => "Users get points for the following when a user comments on a post",
                "Rules" => array(
                    "When a comment is created  (self comment on self post not counted and comment owner gets point only for one comment on the same post.)", 
                    "When a comment is marked as possible solution for a post within a ward",
                    "When a comment is marked as  solution for a post within a ward",
                    "When a comment is marked as possible solution for a post within a city",
                    "When a comment is marked as solution for a post within a city",
                    "Number of likes on a comment (self like on self comment not counted)",
                    "On comment deletion points will be deleted"
                )
            );
            $point_table[] = array(
                "Title" => "Photo Album",
                "Rules" => array(
                    "On uploading a pic in an album", 
                    "Max limit per day"
                )
            );
            $return['PointTable'] = $point_table;
        }    
        if(API_VERSION == "v5" ){
            $return['Data'] = $this->activity_front_helper_model->top_contributor($user_id, $data); 
        } else {
            $return['Data'] = array();//$this->activity_front_helper_model->top_contributor($user_id, $data); 
        } 
        $this->response($return);
    }

    /**
     * Used to get similar post
     */
    public function similar_activity_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $activity = get_detail_by_guid($data['ActivityGUID'],0,'ActivityID, CreatedDate', 2);
                if(!empty($activity)) {
                    $data['UserID'] = $user_id;
                    $data['ActivityID'] = $activity['ActivityID'];
                    $data['CreatedDate'] = $activity['CreatedDate'];
                    $this->load->model(array('tag/tag_model'));
                    $entity_tags    = $this->tag_model->get_activity_tags(1, 'ACTIVITY', $data['ActivityID']);
                    $tag_ids = array();
                    foreach($entity_tags as $entity_tag) {
                        $tag_ids[]=$entity_tag['TagID'];
                    }
                    if(!empty($tag_ids)) {
                        $data['TagIDs'] = $tag_ids;
                        $return['Data'] = $this->activity_front_helper_model->similar_posts($data);
                    }
                                         
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * Used to get admin tool details for an activity
     */
    public function admin_tool_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                if($is_super_admin) {
                    $activity = get_detail_by_guid($data['ActivityGUID'],0,'ActivityID, UserID, IsPromoted, IsShowOnNewsFeed, IsCityNews', 2);
                    if(!empty($activity)) {
                        $activity_id = $activity['ActivityID'];
                        $user_id = $activity['UserID'];
                        $this->load->model(array('admin/dashboard/dashboard_activity_model'));
                        $activity['IsPined']      = $this->dashboard_activity_model->is_pined($activity_id);
                        

                        $activity_details = $this->activity_front_helper_model->get_activity_details($activity_id);
                        $activity = array_merge($activity, $activity_details);

                        $activity['ActivityTags'] = (array)$this->dashboard_activity_model->get_entity_tags($activity_id, 'ACTIVITY');        
                        
                        
                        $user_details = $this->user_model->profile($user_id);
                        $activity['UserDetails'] = array(
                            'DOB' => $user_details['DOB'],
                            'IsDOBApprox' => $user_details['IsDOBApprox'],
                            'IncomeLevel' => $user_details['IncomeLevel'],                        
                            'Gender' => $user_details['Gender']
                        );
                        $activity['UserTags']     = (array)$this->dashboard_activity_model->get_entity_tags($user_id, 'USER');
                        
                        
                        unset($activity['UserID']);
                        unset($activity['ActivityID']);
                        $return['Data'] = $activity;
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
        
    }

    /**
     * Function Name: hide_activity
     * Description: Update STATUS TO SHOW ACTIVITY ON NEWSFEED OR NOT
     */
    function update_activity_newsfeed_status_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $validation_rule =array(
            array(
                'field' => 'ActivityGUID',
                'label' => 'activity guid',
                'rules' => 'trim|required',
            )
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
       
        $activity_guid = safe_array_key($data, 'ActivityGUID');      
        $activity = get_detail_by_guid($activity_guid,0,'ActivityID, IsShowOnNewsFeed', 2);
        if(!empty($activity)) {  
            $this->activity_front_helper_model->update_activity_newsfeed_status($activity);
            $return['Message'] = 'Status updated successfully.';   
        } else {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = sprintf(lang('valid_value'), "activity guid");
        }     
        $this->response($return);
    }

    public function move_to_city_news_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'IsCityNews',
                    'label' => 'city news',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                if($is_super_admin) {
                    $activity_id = get_detail_by_guid($data['ActivityGUID']);                    
                    if($activity_id) { 
                        $is_city_news = $data['IsCityNews'];
                        $is_show_on_news_feed = $data['IsShowOnNewsFeed'];
                        if($is_city_news == 1) {
                            $this->load->model(array('activity/activity_model'));
                            $this->activity_model->mark_city_news($activity_id);

                            if(!in_array($is_show_on_news_feed,array(0,1))) {
                                $is_show_on_news_feed = 0;
                            }
                            if(!in_array($is_city_news,array(0,1))) {
                                $is_city_news = 0;
                            }
                            $this->db->set('IsShowOnNewsFeed', $is_show_on_news_feed);
                            $this->db->set('IsCityNews', $is_city_news);                                       
                            $this->db->where('ActivityID', $activity_id);
                            $this->db->update(ACTIVITY);
                        }
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return);        
    }

    public function remove_from_city_news_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                if($is_super_admin) {

                    $activity_id = get_detail_by_guid($data['ActivityGUID']);                    
                    if($activity_id) {            
                        $this->load->model(array(
                            'admin/dashboard/dashboard_activity_model'
                        ));
                        $this->dashboard_activity_model->remove_from_city_news($activity_id);
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return);
    }

    public function pin_to_top_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {      
                $activity_id = get_detail_by_guid($data['ActivityGUID']);
                if(!empty($activity_id)) {  
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                    if($is_super_admin) {
                        $this->activity_front_helper_model->pin_to_top($activity_id);
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                } 
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return);        
    }

    public function remove_pin_to_top_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $activity_id = get_detail_by_guid($data['ActivityGUID']);
                if(!empty($activity_id)) {
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                    if($is_super_admin) {
                        $this->activity_front_helper_model->remove_pin_to_top($activity_id);
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                } 
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return);        
    }

    public function related_to_indore_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'IsRelated',
                    'label' => 'related',
                    'rules' => 'trim|in_list[0,1]'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $activity_id = get_detail_by_guid($data['ActivityGUID']);
                if(!empty($activity_id)) {
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                    if($is_super_admin) {
                        $data['ActivityID'] = $activity_id;
                        $this->activity_front_helper_model->related_to_indore($data);
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                } 
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return);        
    }

    public function idea_for_better_indore_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'IsIdea',
                    'label' => 'idea flag',
                    'rules' => 'trim|in_list[0,1]'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $activity_id = get_detail_by_guid($data['ActivityGUID']);
                if(!empty($activity_id)) {
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                    if($is_super_admin) {
                        $data['ActivityID'] = $activity_id;
                        $this->activity_front_helper_model->idea_for_better_indore($data);
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                } 
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return);        
    }

    public function mark_as_similar_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'SimilarActivityGUID',
                    'label' => 'similar activity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $activity_data = get_detail_by_guid($data['ActivityGUID'], 0, 'ActivityID, PostType', 2);
                if(!empty($activity_data)) {
                    $activity_id = $activity_data['ActivityID'];
                    $post_type = $activity_data['PostType'];
                    $similar_activity = get_detail_by_guid($data['SimilarActivityGUID'], 0, 'ActivityID, PostTitle, PostContent, IsMediaExist', 2);
                    if(!empty($similar_activity)) {
                        $this->load->model(array(                    
                            'users/user_model'
                        ));
                        $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                        if($is_super_admin) {  
                            $this->load->model(array('activity/activity_model'));
                            $short_url = DOMAIN."/activity/".$data['SimilarActivityGUID'];
                            //$long_url_new = get_seo_friendly_activity_url($activity_details);
                            //$long_url_new = DOMAIN."/".$long_url_new;

                            //$short_url = get_short_url($short_url);

                            $similar_activity['PostContent'];
                            $links =  array();
                            $links['URL'] = $short_url;
                            $links['Title'] = trim(strip_tags($similar_activity['PostTitle']));
                            $links['MetaDescription'] = trim(strip_tags($similar_activity['PostContent']));

                            if(empty($links['Title'])) {
                                $links['Title'] = 'Bhopu | Question and Answer app of Indore';
                            }
                            if(empty($links['MetaDescription'])) {
                                $links['MetaDescription'] = 'Bhopu is the question and answer app of Indore, where people get answers to their questions of any kind by 60,000 people, experts, government departments and politicians.';
                            }
                            $links['ImageURL'] = '';
                            $links['IsCrawledURL'] = 0;
                            if($similar_activity['IsMediaExist'] == 1) {
                                
                                $album = $this->activity_model->get_albums($similar_activity['ActivityID'], '0', '', 'Activity', 1);
                                $share_image = '';
                                if (isset($album[0]['Media'][0])) {
                                    $image_name = $album[0]['Media'][0]['ImageName'];
                                    if($album[0]['Media'][0]['MediaType'] == 'Video') {
                                        $image_name = substr($image_name, 0, strrpos($image_name,'.')).'.jpg';
                                        
                                    }
                                    $share_image = IMAGE_SERVER_PATH . 'upload/';
                                    $share_image .= 'wall';                                   
                                    $share_image .= '/' . $image_name;

                                    $links['ImageURL'] = $share_image;
                                }
                            }

                            $UserID = ADMIN_USER_ID;                          
                            $EntityGUID = $data['ActivityGUID'];
                            $Comment = $short_url;
                            if($post_type == 2) {
                                $Comment = "सवाल पूछने के लिए आपका धन्यवाद। भोपू पर इस तरह का सवाल या इस सवाल पर काम आने वाली जानकारी पहले शेयर की जा चुकी है। देखने के लिए इस लिंक को क्लिक करें। \n".$short_url;
                            }
                           
                            $parent_comment_id = 0;
                            $comment_id = 0;
                            $is_anonymous = 0;
                            $comment_guid = 0;

                            $Media =  array();
                            $SourceID =  DEFAULT_SOURCE_ID;
                            $DeviceID = DEFAULT_SOURCE_ID;
                            $taged_user = '';
                            
                            $delete_link    =  0;
                            
                            $entity_owner = 0;
                            $PostAsModuleID =  3;
                            $PostAsModuleEntityGUID =  0;
                            $PostAsModuleEntityID =  $UserID;
                            $entity_type =  'ACTIVITY';
                            $is_media_exists = 0;
                            $CommentData = $this->activity_model->addComment($EntityGUID, $Comment, $Media, $UserID, $is_media_exists, $SourceID, $DeviceID, $entity_owner, $entity_type, $PostAsModuleID, $PostAsModuleEntityID, $parent_comment_id, $is_anonymous, $comment_id, $taged_user, array($links));
                            
                        } else {
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = lang('permission_denied');
                        }
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = sprintf(lang('valid_value'), "similar activity guid");
                    } 
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                } 
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return); 
    }

    public function bump_up_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {      
                $activity_id = get_detail_by_guid($data['ActivityGUID']);
                if(!empty($activity_id)) {  
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                    if($is_super_admin) {
                        $this->activity_front_helper_model->bump_up($activity_id);
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                } 
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return); 
    }

    public function not_require_answer_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'IsAnswerRequired',
                    'label' => 'answer flag',
                    'rules' => 'trim|in_list[0,1]'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {      
                $activity_id = get_detail_by_guid($data['ActivityGUID']);
                if(!empty($activity_id)) {  
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                    if($is_super_admin) {
                        $data['ActivityID'] = $activity_id;
                        $this->activity_front_helper_model->not_require_answer($data);
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                } 
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return); 
    }
}
