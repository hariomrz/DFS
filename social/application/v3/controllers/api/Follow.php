<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This Class used as REST API for Follow module 
 * @category     Controller
 * @author       Vinfotech Team
 */
class Follow extends Common_API_Controller {

    function __construct() {
        parent::__construct();
        $this->check_module_status(11);
    }

    function index_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'UserGUID',
                    'label' => 'user guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $module_id = 3;
                $following_data = get_detail_by_guid($data['UserGUID'], $module_id, 'UserID, Concat(FirstName, " ",  LastName) AS Name', 2);
                $following_id = $following_data['UserID'];
                $following_name = $following_data['Name'];
                if($following_id && $following_id != $user_id) {
                    $data = array('FollowingID' => $following_id, 'UserID' => $user_id);
                    $this->load->model('follow/follow_model');
                    $is_follow = $this->follow_model->is_follow($user_id, $following_id);
                    $return['follow'] = $is_follow ? 1:0;
                    if($this->input->post('is_follow'))
                    {
                        $this->response($return);
                    }
                    if($is_follow == 1) {
                        // $this->load->model(['activity/activity_model','user/user_model']);
                        // $IsAdmin = $this->user_model->is_super_admin($following_id);
                        // $IsAdminGuid  = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID)); // admin set from config page
                        // if($IsAdmin == "1" || $following_id==$IsAdminGuid ){
                        //     $error = "You can't unfollow the admin user";
                        //     $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        //     $return['Message'] = $error;
                        // }else{
                            $this->follow_model->unfollow($data);
                            $return['Message'] = sprintf(lang('unfollow_success'), $following_name);
                            $return['follow'] = 0;
                        //}
                    } else {
                        $this->follow_model->follow($data);
                        $return['Message'] = sprintf(lang('follow_success'), $following_name);
                        $return['follow'] = 1;
                    }                                        
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "user guid");
                }
            }
        }
        $this->response($return);
    }    

    function following_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'UserGUID',
                    'label' => 'user guid',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'OrderBy',
                    'label' => 'order by',
                    'rules' => 'trim|in_list[Name,Recent]'
                ),
                array(
                    'field' => 'SortBy',
                    'label' => 'sort by',
                    'rules' => 'trim|in_list[ASC,DESC]'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $module_id = 3;
                $following_id = get_detail_by_guid($data['UserGUID'], $module_id);
                if($following_id) {
                    $data['LoogedInUserID'] = $user_id;
                    $data['UserID'] = $following_id;
                    $this->load->model(array('follow/follow_model', 'users/user_model'));
                    $this->user_model->set_friend_followers_list($user_id);                    
                    $return['Data'] = $this->follow_model->following($data);                                                            
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "user guid");
                }
            }
        }
        $this->response($return);
    }

    function followers_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'UserGUID',
                    'label' => 'user guid',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'OrderBy',
                    'label' => 'order by',
                    'rules' => 'trim|in_list[Name,Recent]'
                ),
                array(
                    'field' => 'SortBy',
                    'label' => 'sort by',
                    'rules' => 'trim|in_list[ASC,DESC]'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $module_id = 3;
                $following_id = get_detail_by_guid($data['UserGUID'], $module_id);
                if($following_id) {
                    $data['LoogedInUserID'] = $user_id;
                    $data['UserID'] = $following_id;
                    $this->load->model(array('follow/follow_model', 'users/user_model'));
                    $this->user_model->set_friend_followers_list($user_id);
                    $return['Data'] = $this->follow_model->followers($data);                                                            
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "user guid");
                }
            }
        }
        $this->response($return);
    }

    function suggestion_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        $data['UserID'] = $user_id;
        $tag_id = safe_array_key($data, 'TagID', 0);
        $this->load->model(array('follow/follow_model', 'users/user_model', 'ward/ward_model'));
        $this->user_model->set_friend_followers_list($user_id);
        $this->user_model->set_top_contributors($tag_id);
        $return['Data'] = $this->ward_model->who_to_follow($data);
        // $return['Data'] = $this->follow_model->suggestion($data);  
        $this->response($return);
    }
}