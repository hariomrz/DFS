<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Announcement extends Admin_API_Controller
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
        $list_type = !empty($data['ListType']) ? $data['ListType'] : '';
        //$entity_type         = !empty($Data['EntityType'])?$Data['EntityType']:'1';  
        $return['Data'] = $this->announcement_model->announcement_list($search_keyword, FALSE, $page_no, $page_size, $sort_by, $order_by, $list_type);
        $return['TotalRecords'] = $this->announcement_model->announcement_list($search_keyword, TRUE, '', '', '', '', $list_type);
        $this->response($return);
    }

    /**
     * Function Name: add
     * @param LoginSessionKey
     * @param 
     * Description: 
     */
    public function add_post()
    {
        $Return['ResponseCode'] = '200';

        $Return['ServiceName'] = 'admin_api/announcement/add';

        $Return['Data'] = array();

        $Data = $this->post_data;

        /* Validation - starts */
        $validation_rule = $this->form_validation->_config_rules['api/announcement/add'];

        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = 511;
            $Return['Message'] = $error;
        } else
        {
            $Title          = !empty($Data['Title'])?$Data['Title']:'';
            $BlogGUID = !empty($Data['BlogGUID']) ? $Data['BlogGUID'] : '';
            $Description = !empty($Data['Description']) ? $Data['Description'] : '';
            $Status = !empty($Data['Status']) ? $Data['Status'] : '';
            $entity_type = !empty($Data['EntityType']) ? $Data['EntityType'] : '3';


            $insert_data['UserID'] = $this->UserID;
            if ($BlogGUID)
            {
                $blog_data = get_data('Status', BLOG, array('BlogGUID' => $BlogGUID), '1', '');

                $this->db->where('BlogGUID', $BlogGUID);
                $update_array['Description'] = $Description;
                $update_array['Title'] = $Title;
                $update_array['Status'] = $Status;
                if ($blog_data)
                {
                    if ($blog_data->Status == 'DRAFT' && $Status == 'PUBLISHED')
                    {
                        $update_array['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    }
                }
                $this->db->update(BLOG, $update_array);
                $Return['Message'] = 'Data updated successfully.';
            } else
            {
                $insert_data['Description'] = $Description;
                $insert_data['Title'] = $Title;
                $insert_data['Status'] = $Status;
                $insert_data['EntityType'] = $entity_type;
                $insert_data['BlogGUID'] = get_guid();
                $insert_data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $insert_data['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $blog_id = $this->announcement_model->add($insert_data);
                $Return['Message'] = 'Data added successfully.';
            }
        }

        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * Function Name: delete
     * @param LoginSessionKey
     * @param 
     * Description: 
     */
    public function delete_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = 'Data successfully deleted.';
        $Return['ServiceName'] = 'admin_api/announcement/delete';
        $Return['Data'] = array();
        $Data = $this->post_data;

        /* Validation - starts */
        $validation_rule = $this->form_validation->_config_rules['api/blog/delete'];

        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = 511;
            $Return['Message'] = $error;
        } else
        {
            $BlogGUID = !empty($Data['BlogGUID']) ? $Data['BlogGUID'] : '';
            $this->db->where('BlogGUID', $BlogGUID);
            $this->db->update(BLOG, array('Status' => 'DELETED'));
        }

        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
