<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Announcementpopup extends Admin_API_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/announcement_model', 'admin/login_model'));
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200)
        {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
    }

    /**
     * @Function - function to list blog
     * @Output   - JSON
     */
    public function list_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get post data       

        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'CreatedDate';
        $order_by = !empty($data['OrderBy']) ? $data['OrderBy'] : 'DESC';
        
        //$entity_type         = !empty($Data['EntityType'])?$Data['EntityType']:'1';  
        $return['Data'] = $this->announcement_model->announcement_popup_list($search_keyword, FALSE, $page_no, $page_size, $sort_by, $order_by);
        $return['TotalRecords'] = $this->announcement_model->announcement_popup_list($search_keyword, TRUE, '', '', '', '');
        $this->response($return);
    }

    /**
     * Function Name: save
     * Description: 
     */
    public function save_post()
    {
        $Return['ResponseCode'] = '200';

        $Return['ServiceName'] = 'admin_api/announcementpopup/save';

        $Return['Data'] = array();

        $Data = $this->post_data;

        /* Validation - starts */
        $validation_rule = $this->form_validation->_config_rules['admin_api/announcementpopup/save'];
        
        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = 511;
            $Return['Message'] = $error;
        } else
        {
            $popup_title   = !empty($Data['PopupTitle'])?$Data['PopupTitle']:'';
            $popup_content = !empty($Data['PopupContent']) ? $Data['PopupContent'] : '';
            $status        = (!empty($Data['Status']) && in_array($Data['Status'], array(1,2,3))) ? $Data['Status'] : '2';
            $is_imagedata  = (!empty($Data['IsImageData'])) ? $Data['IsImageData'] : '0';
            $announcement_popup_id  = !empty($Data['AnnouncementPopupID']) ? $Data['AnnouncementPopupID'] : '';
            $user_id = $this->UserID;
            $this->announcement_model->save_announcement_popup($user_id,$popup_title,$popup_content,$status,$announcement_popup_id,$is_imagedata);
        }

        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function Name: change_status_post
     * Description: Active/Inactive/Delete Popup
     */
    public function change_status_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = 'Information updated successfully.';
        $Return['ServiceName'] = 'admin_api/announcementpopup/change_status';
        $Return['Data'] = array();
        $Data = $this->post_data;

        /* Validation - starts */
        $validation_rule = $this->form_validation->_config_rules['admin_api/announcementpopup/change_status'];

        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = 511;
            $Return['Message'] = $error;
        } else
        {
            $announcement_popup_id = !empty($Data['AnnouncementPopupID']) ? $Data['AnnouncementPopupID'] : '';
            $status_id = !empty($Data['Status']) ? $Data['Status'] : '';
            if($announcement_popup_id)
            {
                $this->announcement_model->change_status_of_popup($announcement_popup_id,$status_id);
            }
        }

        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
