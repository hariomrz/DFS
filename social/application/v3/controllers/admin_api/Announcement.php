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
    public function add_post() {

        $data = $this->post_data;
         if ($data) {

            $type = safe_array_key($data, 'Type', 1);
            if($type == 1) {
                $config = array(
                    array(
                        'field' => 'Title',
                        'label' => 'title',
                        'rules' => 'trim|required|max_length[40]'
                    ),
                    array(
                        'field' => 'Description',
                        'label' => 'description',
                        'rules' => 'trim|required|max_length[100]'
                    )
                 );
            } else {
                $config = array(
                    array(
                        'field' => 'rawImage',
                        'label' => 'image',
                        'rules' => 'trim|required'
                    )
                 ); 
            }
            $config[] = array(
                            'field' => 'ActionText',
                            'label' => 'call to action',
                            'rules' => 'trim|max_length[20]'
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {  
                $title = safe_array_key($data, 'Title');
                $description = safe_array_key($data, 'Description');
                $status = safe_array_key($data, 'Status');
                $entity_type = safe_array_key($data, 'EntityType');
                $url = safe_array_key($data, 'Url');
                $action_text = safe_array_key($data, 'ActionText');
                
                $blog_guid = safe_array_key($data, 'BlogGUID');

                $tag_id = safe_array_key($data, 'TagID', 0);
                $activity_guid = safe_array_key($data, 'ActivityGUID');
                $user_guid = safe_array_key($data, 'UserGUID');
                $quiz_guid = safe_array_key($data, 'QuizGUID');
                $custom_url = safe_array_key($data, 'CustomUrl');

                $can_ignore = safe_array_key($data, 'CanIgnore', 0);

                $media_id = NULL;
                $media_guid = safe_array_key($data, 'MediaGUID');
                if(!empty($data['MediaGUID'])){
                    $media_id = get_detail_by_guid($media_guid, 21,'MediaID');
                }

                $ImageData['ImageData'] = safe_array_key($data, 'rawImage');
                if(!empty($ImageData['ImageData'])){
                    $media_id = $this->rawImage_convert($ImageData);
                }
                
                $insert_data['UserID'] = $this->UserID;

                $created_date =get_current_date('%Y-%m-%d %H:%i:%s');
                if ($blog_guid)
                {
                    $blog_data = get_data('Status', BLOG, array('BlogGUID' => $blog_guid), '1', '');

                    $this->db->where('BlogGUID', $blog_guid);
                    $update_array['Description'] = $description;
                    $update_array['Title'] = $title;
                    $update_array['Status'] = $status;
                    if ($blog_data)
                    {
                        if ($blog_data->Status == 'DRAFT' && $status == 'PUBLISHED')
                        {
                            $update_array['CreatedDate'] = $created_date;
                        }
                    }
                    $this->db->update(BLOG, $update_array);
                    $this->return['Message'] = 'Data updated successfully.';
                } else
                {
                   /*
                    $update_array['Status'] = 'DELETED';
                    $update_array['ModifiedDate'] = $created_date;
                    $this->db->where_in('Type', array(1,2));
                    $this->db->update(BLOG, $update_array);
                    */
                    $insert_data['Description'] = $description;
                    $insert_data['Title'] = $title;
                    $insert_data['Status'] = $status;
                    $insert_data['EntityType'] = $entity_type;
                    $insert_data['CanIgnore'] = $can_ignore;
                    $insert_data['Type'] = $type;
                    $insert_data['URL'] = $url;
                    $insert_data['ActionText'] = $action_text;
                    $insert_data['MediaID'] = $media_id;
                    if(!empty($media_id)) {
                        $insert_data['IsMediaExists'] = 1;
                    }

                    if($url=='POST') {
                        $insert_data['RedirectTo'] = $activity_guid;
                    } else if($url=='POST_TAG' || $url=='QUESTION_CATEGORY' || $url=='CLASSIFIED_CATEGORY') {
                        $insert_data['RedirectTo'] = $tag_id;
                    } else if($url=='CUSTOM_URL') {
                        $insert_data['RedirectTo'] = $custom_url;
                    } else if($url=='FEEDBACK') {
                        $insert_data['RedirectTo'] = $user_guid;
                    } else if($url=='QUIZ') {
                        $insert_data['RedirectTo'] = $quiz_guid;
                    }
                    $insert_data['BlogGUID'] = get_guid();
                    $insert_data['CreatedDate'] = $created_date;
                    $insert_data['ModifiedDate'] = $created_date;
                    $blog_id = $this->announcement_model->add($insert_data);
                    if(!empty($media_id)) {
                        $this->announcement_model->update_media_status($media_id, $blog_id);
                    }
                    
                    $this->return['Message'] = 'Data added successfully.';
                }

            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);        
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

        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = 511;
            $Return['Message'] = $error;
        } else {
            $BlogGUID = !empty($Data['BlogGUID']) ? $Data['BlogGUID'] : '';
            $this->db->where('BlogGUID', $BlogGUID);
            $this->db->update(BLOG, array('Status' => 'DELETED'));
        }

        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function rawImage_convert($data) {
        $this->load->model(array('upload_file_model'));
        $fileAllowedArray = array('png','jpg','jpeg','PNG','JPG','JPEG','GIF','gif');
        $image_data = $data['ImageData'];
        foreach($fileAllowedArray as $farr){
            $image_data = str_replace('data:image/'.$farr.';base64,', '', $image_data);
        }
        
        $data['ImageData'] = base64_decode($image_data); 
        $data['Type'] = 'blog';
        $data['DeviceType'] = 'native';
        $data['ModuleID'] = 24;
        $data['SourceID'] = 1;
        $result = $this->upload_file_model->saveFileFromUrl($data);
        $media = $result['Data'];
        return isset($media['MediaID']) ? $media['MediaID'] : 0;

    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
