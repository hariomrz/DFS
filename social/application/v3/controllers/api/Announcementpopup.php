<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Announcementpopup extends Common_API_Controller {

    function __construct() {
        parent::__construct();
        //$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $this->load->model(array('activity/popup_model'));
    }

    /**
     * Function Name: index
     * @param SearchKeyword,PageNo,PageSize,SortBy,OrderBy,ShowPopup
     * Description: Get announcement Popups
     */
    public function index_post() {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $user_id = $this->UserID; // Get post data

        $page_size = isset($data['PageSize']) ? $data['PageSize'] : ''; //PAGE_SIZE;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'CreatedDate';
        $order_by = !empty($data['OrderBy']) ? $data['OrderBy'] : 'DESC';
        $show_popup = isset($data['ShowPopup']) ? $data['ShowPopup'] : TRUE;
        if ($show_popup) {
            $this->load->model('activity/popup_model');
            $return['Data'] = $this->popup_model->announcement_popup_list($search_keyword, FALSE, $page_no, $page_size, $sort_by, $order_by, TRUE, $user_id);
            $return['TotalRecords'] = $this->popup_model->announcement_popup_list($search_keyword, TRUE, '', '', '', '');
        } else {
            $return['Data'] = array();
            $return['TotalRecords'] = 0;
        }
        $this->response($return);
    }

    /**
     * Function Name: skip_popup
     * @param AnnouncementPopupID
     * Description: Get list of activity according to input conditions
     */
    public function skip_popup_post() {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $user_id = $this->UserID; // Get post data
        $announcement_popup_id = !empty($data['AnnouncementPopupID']) ? $data['AnnouncementPopupID'] : 0;
        if (!$announcement_popup_id) {
            $error = 'AnnouncementPopupID is required!'; //$this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $this->load->model('activity/popup_model');
            $return['Data'] = $this->popup_model->skip_announcement_popup($announcement_popup_id, $user_id);
        }
        $this->response($return);
    }
}
