<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
class Forum_user_categories extends Common_API_Controller {

    function __construct() {
        parent::__construct();
        $this->module_id = 33;
        $this->check_module_status(1);
        $this->load->model(array('forum/forum_user_categories_model'));
        $this->reserve_forum_url = array('index', 'manage_admin', 'members_settings', 'media', 'files', 'links', 'members');
    }

    /**
     * Function Name: list of parent categories
     * Description: Get all list of forum categories and their status ( Whether user selected them ) 
     */
    function list_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;

        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : 24;
        $only_selected = isset($data['OnlySelected']) ? $data['OnlySelected'] : 0;
        $search = isset($data['search']) ? $data['search'] : '';

        $result = $this->forum_user_categories_model->lists($user_id, $page_no, $page_size, $only_selected, $search);
        $return['Data'] = $result;
        //$return['TotalRecords']=$result['TotalRecords'];

        $this->response($return);
    }

    function save_categories_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;

        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;


        $forum_category_ids = isset($data['ForumCategoryIDs']) ? $data['ForumCategoryIDs'] : [];
        $onlyRemove = isset($data['OnlyRemove']) ? $data['OnlyRemove'] : 0;

        $result = $this->forum_user_categories_model->save_user_categories($user_id, $forum_category_ids, $onlyRemove);
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Message'] = 'Category Saved.';
        $this->response($return);
    }

}
