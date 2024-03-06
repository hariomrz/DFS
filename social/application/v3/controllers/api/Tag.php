<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This Class used as REST API for Tag module 
 * @category     Controller
 * @author       Vinfotech Team
 */
class Tag extends Common_API_Controller {

    function __construct() {

        parent::__construct();
        $this->load->model(array('tag/tag_model', 'users/user_model'));
    }

    /**
     * [save_post Used to save tag]
     * @return [array] [Response details]
     */
    function save_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $return['Data']['EntityTagIDs'] = [];
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'EntityGUID',
                    'label' => 'entity guid',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'EntityType',
                    'label' => 'entity type',
                    'rules' => 'trim|required|in_list[ACTIVITY,USER]'
                ),
                array(
                    'field' => 'TagType',
                    'label' => 'tag type',
                    'rules' => 'trim|required|in_list[ACTIVITY,USER,READER,PROFESSION]'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {               

                $entity_type = $data['EntityType'];
                $entity_id   = 0;
                switch ($entity_type) {
                    case 'ACTIVITY':
                        $entity_id = get_detail_by_guid($data['EntityGUID']);
                        break;
                    case 'USER':
                        $entity_id = get_detail_by_guid($data['EntityGUID'], 3);
                        break;           
                    default:
                        $entity_id   = 0;
                        break;
                }
                if($entity_id) {
                    //Check permission of logged in user for this tag  
                    $is_admin = $this->tag_model->check_permission($user_id, $entity_type, $entity_id);
                    if ($is_admin) {
                        $tag_type = $data['TagType'];                
                        $tag_type_id = $this->tag_model->get_tag_type_id($tag_type);
                        $tag_list = safe_array_key($data, 'TagsList', array());  
                        $is_front_end = safe_array_key($data, 'IsFrontEnd', 1);  
                        $tag_ids = safe_array_key($data, 'TagsIDs', array()); //to delete
                        $forDummyUser = safe_array_key($data, 'ForDummyUser', 0); // for dummy users

                        if (!empty($tag_ids)) {
                            $this->tag_model->delete_entity_tag($entity_id, $entity_type, $tag_ids, [], $forDummyUser);
                        }
                        if (!empty($tag_list)) {
                            $tags_data = $this->tag_model->save_tag($tag_list, $tag_type_id, $user_id);
                            if (!empty($tags_data) && !empty($entity_id)) {
                                $return['Data'] = $this->tag_model->save_entity_tag($tags_data, $entity_type, $entity_id, $user_id, $is_front_end, true, $forDummyUser);
                            }
                        }
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "entity guid");
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [delete_entity_tag_post Used to delete entity tag]
     * @return [array] [Response details]
     */
    function delete_entity_tag_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            if (!empty($data['EntityTagIDs'])) {
                $validation_rule = array(
                    array(
                        'field' => 'EntityTagIDs[]',
                        'label' => 'EntityTagIDs',
                        'rules' => 'trim|required'
                    )
                );
            } else {
                $validation_rule = array(
                    array(
                        'field' => 'EntityGUID',
                        'label' => 'entity guid',
                        'rules' => 'trim|required'
                    ),
                    array(
                        'field' => 'EntityType',
                        'label' => 'Entity type',
                        'rules' => 'trim|required|in_list[ACTIVITY,USER]'
                    ),
                    array(
                        'field' => 'TagsIDs[]',
                        'label' => 'Tag ID',
                        'rules' => 'trim|required'
                    )
                );
            }


            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $entity_type = safe_array_key($data, 'EntityType', 'ACTIVITY');
                $entity_id   = 0;
                switch ($entity_type) {
                    case 'ACTIVITY':
                        $entity_id = get_detail_by_guid($data['EntityGUID']);
                        break;
                    case 'USER':
                        $entity_id = get_detail_by_guid($data['EntityGUID'], 3);
                        break;           
                    default:
                        $entity_id   = 0;
                        break;
                }

                $tag_ids = (isset($data['TagsIDs']) && !empty($data['TagsIDs'])) ? $data['TagsIDs'] : array();
                $entity_tag_ids = (!empty($data['EntityTagIDs'])) ? $data['EntityTagIDs'] : array();
                if (!empty($tag_ids) || !empty($entity_tag_ids)) {
                    $this->tag_model->delete_entity_tag($entity_id, $entity_type, $tag_ids, $entity_tag_ids);

                    $classified_tag_id = $this->tag_model->is_tag_exist('classified', 1);
                    $question_tag_id = $this->tag_model->is_tag_exist('question', 1);
                    if(in_array($classified_tag_id, $tag_ids)) {
                        $tag_category_ids = $this->tag_model->get_entity_tag_category($entity_type, $entity_id, $classified_tag_id, true);
                        if($tag_category_ids) {
                            $this->tag_model->delete_entity_tag_category($tag_category_ids, $entity_id, $entity_type, $classified_tag_id);                           
                        }

                        if($entity_type == 'ACTIVITY') {
                            $point_data = array('EntityID' => $entity_id, 'EntityType' => 1, 'ActivityTypeID' => 43);
                            initiate_worker_job('revert_point', $point_data,'','point');
                        }

                    } else if(in_array($question_tag_id, $tag_ids)) {
                        $tag_category_ids = $this->tag_model->get_entity_tag_category($entity_type, $entity_id, $question_tag_id, true);
                        if($tag_category_ids) {
                            $this->tag_model->delete_entity_tag_category($tag_category_ids, $entity_id, $entity_type, $question_tag_id);                           
                        }
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
                    $return['Message'] = lang('input_invalid_format');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    function get_entity_tags_get() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
            $tag_type = isset($data['TagType']) ? trim($data['TagType']) : 'LINK';
            $entity_type = isset($data['EntityType']) ? trim($data['EntityType']) : 'LINK';
            $entity_id = isset($data['EntityID']) ? trim($data['EntityID']) : 0;
            $page_no = (isset($data['PageNo']) && $data['PageNo'] > 0) ? $data['PageNo'] : '1';
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : '20';
            $forDummyUser = (!empty($data['ForDummyUser'])) ? $data['ForDummyUser'] : 0; // for dummy users

            $tag_type_id = $this->tag_model->get_tag_type_id($tag_type);
            $classified_tag_id = $this->tag_model->is_tag_exist('classified', 1);
            $question_tag_id = $this->tag_model->is_tag_exist('question', 1);
            if($classified_tag_id) {
                $data['ExcludeTag'] = array($classified_tag_id, $question_tag_id);
            }
            $return['Data'] = $this->tag_model->get_entity_tags($search_keyword, $page_no, $page_size, $tag_type_id, $entity_type, $entity_id, $user_id, $forDummyUser, $data);
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [get list of popular tags by entity type]
     * @return [array] [list of popular tags]
     */
    function get_popular_tags_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $return['Data'] = $this->tag_model->get_popular_tags($data);
        $this->response($return);
    }

    /**
     * [save_post Used to save tag]
     * @return [array] [Response details]
     */
    function save_tag_users_roles_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $return['Data']['EntityTagIDs'] = [];
        $onlyUsers = (!empty($data['OnlyUsers'])) ? $data['OnlyUsers'] : 0; // for dummy users
        if (!isset($data)) {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        $tag_type = 'USER';
        $tag_type_id = 5;
        $entity_type = 'USER';
        $entity_id = $user_id;
        $is_front_end = 0;
        $forDummyUser = 1;

        $this->load->model(array('admin/users_model'));
        $return['Data']['Tags'] = [];
        $return['Data']['Users'] = [];

        // In case only get users
        if ($onlyUsers) {
            $return['Data']['Users'] = $this->users_model->save_users_roles([], 1);
            $return['Data']['Tags'] = $this->tag_model->get_entity_tags_for_dummy_users();
            $this->response($return);
        }

        $tag_list = (isset($data['TagsList']) && !empty($data['TagsList'])) ? $data['TagsList'] : array();
        $tag_ids = (isset($data['TagsIDs']) && !empty($data['TagsIDs'])) ? $data['TagsIDs'] : array(); //to delete
        $user_ids = (isset($data['UserIDs']) && !empty($data['UserIDs'])) ? $data['UserIDs'] : array(); //to delete

        $new_added = [];
        if (!empty($tag_ids)) {
            $this->tag_model->delete_entity_tag($entity_id, $entity_type, $tag_ids, [], $forDummyUser);
        }
        if (!empty($tag_list)) {
            $tags_data = $this->tag_model->save_tag($tag_list, $tag_type_id, $user_id);
            if (!empty($tags_data) && !empty($entity_id)) {
                $new_added = $this->tag_model->save_entity_tag_dummy_users($tags_data);
            }
        }

        $return['Data']['Tags'] = $this->tag_model->get_entity_tags_for_dummy_users();
        $return['Data']['Users'] = $this->users_model->save_users_roles($user_ids);

        // Assign or Update tags to dummy users
        if (!empty($new_added) || !empty($tag_ids)) {
            $this->tag_model->assign_or_update_tags_to_dummy_users($return['Data']['Tags']);
        }
        $this->response($return);
    }
    
    /**
     * [save_tag_category_post used to add tag category]
     */
    function save_tag_category_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'Name',
                    'label' => 'category',
                    'rules' => 'trim|required|max_length[100]'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $is_admin = $this->user_model->is_super_admin($user_id);
                if ($is_admin) {
                    $name = safe_array_key($data, 'Name');
                    $tag_list = safe_array_key($data, 'TagsList', array());                 
                    $tag_category_id = $this->tag_model->save_tag_category($name);
                    if ($tag_category_id) {
                        if (!empty($tag_list)) {
                            $tags_data = $this->tag_model->save_tag($tag_list, 1, $user_id);
                            if (!empty($tags_data)) {
                               $this->tag_model->save_category_tag($tags_data, $tag_category_id);
                            }
                        }

                        $this->tag_model->delete_api_static_file('tag_categories');
                        if (CACHE_ENABLE) {
                            $this->cache->delete('tag_categories');
                        }
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
    
    /**
     * [delete_tag_category_post used to delete tag category]
     */
    function delete_tag_category_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'TagCategoryID',
                    'label' => 'category ID',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $tag_category_id = safe_array_key($data, 'TagCategoryID');                
                $is_admin = $this->user_model->is_super_admin($user_id);
                if ($is_admin) {
                    if ($tag_category_id) {
                        $this->tag_model->delete_tag_category($tag_category_id);                        
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
    
    /**
     * [get_tag_categories_post Used to get tag category]
     */
    function get_tag_categories_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $return['Data'] = $this->tag_model->get_tag_categories();
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    /**
     * change_category_tag_order_post: Change tag category display order  
     */
    function change_category_tag_order_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $is_super_admin = $this->user_model->is_super_admin($user_id);
            if (!$is_super_admin) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            $order_data = isset($data['OrderData']) ? $data['OrderData'] : '';

            if (!empty($order_data)) {
                $this->tag_model->change_category_tag_order($order_data);
                
                $this->tag_model->delete_api_static_file('tag_categories');
                if (CACHE_ENABLE) {
                    $this->cache->delete('tag_categories');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    function tag_categories_suggestion_get() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $tag_id = isset($data['TagID']) ? trim($data['TagID']) : 0;
            $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
            
            $page_no = (isset($data['PageNo']) && $data['PageNo'] > 0) ? $data['PageNo'] : '1';
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : '10';

            $return['Data'] = $this->tag_model->get_tag_categories_suggestion($tag_id, $search_keyword, $page_no, $page_size);
            
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [save_post Used to save tag]
     * @return [array] [Response details]
     */
    function save_entity_tag_category_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'EntityID',
                    'label' => 'Entity ID',
                    'rules' => 'trim|required|validate_entity_id[EntityType]'
                ),
                array(
                    'field' => 'EntityType',
                    'label' => 'Entity type',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'TagID',
                    'label' => 'Tag',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                
                $entity_type = $data['EntityType'];
                $entity_id = $data['EntityID'];
                $tag_id = $data['TagID'];
                $tag_category_list = safe_array_key($data, 'CategoryList', array());  
                
                $is_admin = $this->tag_model->check_permission($user_id, $entity_type, $entity_id);
                if ($is_admin) {
                    if (!empty($tag_category_list)) {
                        $return['Data'] = $this->tag_model->save_entity_tag_category($tag_category_list, $entity_type, $entity_id, $user_id, $tag_id);                        
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

    /**
     * [delete_entity_tag_category_post Used to delete entity tag ctegory]
     * @return [array] [Response details]
     */
    function delete_entity_tag_category_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {            
            $validation_rule = array(
                array(
                    'field' => 'EntityID',
                    'label' => 'Entity ID',
                    'rules' => 'trim|required|validate_entity_id[EntityType]'
                ),
                array(
                    'field' => 'EntityType',
                    'label' => 'Entity type',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'TagID',
                    'label' => 'Tag ID',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'TagsCategoryIDs[]',
                    'label' => 'Tag Category ID',
                    'rules' => 'trim|required'
                )
            );      


            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $entity_type = $data['EntityType'];
                $entity_id = $data['EntityID'];
                $tag_id = $data['TagID'];
                $tag_category_ids = safe_array_key($data, 'TagsCategoryIDs', array());  
                if (!empty($tag_category_ids)) {
                    $this->tag_model->delete_entity_tag_category($tag_category_ids, $entity_id, $entity_type, $tag_id);
                } else {
                    $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
                    $return['Message'] = lang('input_invalid_format');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [get list of trending tags by ward wise]
     * @return [array] [list of trending tags]
     */
    function get_trending_tags_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'WID',
                    'label' => 'ward ID',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $all = safe_array_key($data, 'All', 0);  
                $adt = safe_array_key($data, 'ADT', 0);  
                $data['UserID'] = $user_id;
                if(empty($all)) {                    
                    $return['Data'] = $this->tag_model->get_trending_tags($data);
                    $return['TopTags'] = $this->tag_model->get_top_tags($user_id);
                    if($adt == 1) {
                        $return['OtherTags'] = $this->tag_model->get_tag_list();
                    }
                } else {
                    $return['Data']['tt'] = $this->tag_model->get_trending_tags($data);
                    $return['Data']['ntt'] = $this->tag_model->get_non_trending_tags($data);                    
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

        /**
     * [save_post Used to save tag]
     * @return [array] [Response details]
     */
    function save_ward_trending_tag_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'TagID',
                    'label' => 'tag',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $ward_ids = isset($data['WardIds']) ? $data['WardIds'] : array();
                $tag_id = $data['TagID'];
                $eid = safe_array_key($data, 'EID', 0);  
                if(!empty($ward_ids)) {                          
                    $this->tag_model->save_ward_trending_tag($tag_id, $ward_ids, $eid);                        
                    $return['Message'] = 'Tag visibility updated successfully.';
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('ward_required');
                } 
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [delete_tag_category_post used to delete tag category]
     */
    function remove_tag_ward_visibility_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'TID',
                    'label' => 'tag id',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $ward_trending_tag_id = $data['TID'];                
                $is_admin = $this->user_model->is_super_admin($user_id);
                if ($is_admin) {
                    $this->tag_model->remove_tag_ward_visibility($ward_trending_tag_id);                                            
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
    
        /**
     * change_category_tag_order_post: Change tag category display order  
     */
    function change_ward_trending_tag_order_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $is_super_admin = $this->user_model->is_super_admin($user_id);
            if (!$is_super_admin) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            $order_data = isset($data['OrderData']) ? $data['OrderData'] : '';
            $ward_id = safe_array_key($data, 'WID', 0);  
            if (!empty($order_data)) {
                $this->tag_model->change_ward_trending_tag_order($order_data);
                $falg=FALSE;
                if($ward_id == 1) {
                    $falg=TRUE;
                }
                $this->tag_model->delete_cache_data($ward_id, $falg);                
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }


    function search_tag_categories_post() {
        $return         = $this->return;     
        $data           = $this->post_data;
        if (isset($data)) {            
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
                $search_keyword     = $data['SearchKeyword'];
                $tag_id = safe_array_key($data, 'TagID', 20);   

                $return['Data'] = $this->tag_model->get_categories_by_tag_id($tag_id, $search_keyword);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }


    function sync_user_profession_tag_as_user_tag_post() {
        $return         = $this->return;     
        $data           = $this->post_data;
        //$this->tag_model->sync_user_profession_tag_as_user_tag();
        $this->response($return);
    }

    /**
     * [top_contribution_tags used to get top contribution tags]
     */
    public function top_contribution_tags_post() {
      
        /* Define variables - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        $page_no = safe_array_key($data, 'PageNo', 1);
        $return['Data'] = $this->tag_model->top_contribution_tags($data); 
        
        $this->response($return);
    }


    /**
     * @method toggle_follow
     * @function Follow tag toggle function 
     * ** */
    public function toggle_follow_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'TagID',
                                'label' => 'tag id',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $tag = $this->tag_model->details($data['TagID'], $user_id, 0);
                if(!empty($tag)) {
                    $is_follow = $tag['IsFollow'];                    
                    if($is_follow){ // unfollow tag
                        $this->tag_model->unfollow($user_id, $data['TagID']);
                        $this->return['Message'] = sprintf(lang('unfollow_success'), $tag['Name']);
                    } else {// follow tag
                        $this->tag_model->follow($user_id, $data['TagID']);
                        $this->return['Message'] = sprintf(lang('follow_success'), $tag['Name']);
                    }
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = sprintf(lang('valid_value'), "tag id");
                }                
            }    
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }  
        $this->response($this->return);  // Final Output 
    }

    /**
     * @method toggle_mute
     * @function Mute tag toggle function 
     * ** */
    public function toggle_mute_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'TagID',
                                'label' => 'tag id',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $tag = $this->tag_model->details($data['TagID'], $user_id);
                if(!empty($tag)) {
                    $is_mute = $tag['IsMute'];                    
                    if($is_mute){ // unmute tag
                        $this->tag_model->unmute($user_id, $data['TagID']);
                    } else {// mute tag
                        $this->tag_model->mute($user_id, $data['TagID']);
                    }
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = sprintf(lang('valid_value'), "tag id");
                }                
            }    
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }  
        $this->response($this->return);  // Final Output 
    }
    
    /**
     * @method browse_topic
     * Used to get browse topic tag list
     */
    function browse_topic_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $return['Data'] = $this->tag_model->browse_topic($user_id);
        $this->response($return);
    }

    /**
     * @method top_followed
     * Used to get top followed tag list
     */
    public function top_followed_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (empty($data)) {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        $return['Data'] = (array) $this->tag_model->get_top_followed($data);
        
        
        
        $this->response($return);
    }

    /**
     * @method muted_list
     * Used to get muted tag list
     */
    public function muted_list_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (empty($data)) {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        $return['Data'] = (array) $this->tag_model->muted_list($data);
        
        
        
        $this->response($return);
    }


    /**
     * @method details
     * @function used to get tag details
     * ** */
    public function details_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'TagID',
                                'label' => 'tag id',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $tag = $this->tag_model->details($data['TagID'], $user_id, 1);
                if(!empty($tag)) {
                    $this->return['Data'] = $tag;                    
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = sprintf(lang('valid_value'), "tag id");
                }                
            }    
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }  
        $this->response($this->return);  // Final Output 
    }

}
