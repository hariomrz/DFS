<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of Flag
 *
 * @author nitins
 */
class Flags extends Admin_API_Controller
{
    public $UserID=0;
    public function __construct()
    {
        parent::__construct();

        $this->return['ServiceName'] = 'admin_api/' . $this->router->class . '/' . $this->router->method;
        $this->load->model(array('admin/login_model', 'admin/flags_model','media/media_model'));


        //check loggin key
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200)
        {
            $this->response($logged_user_data);
        }
        $this->user_id = $logged_user_data['Data']['UserID'];
    }

    /**
     * List all flags
     */
    public function list_post()
    {
        $data = $this->post_data;
        if (isset($data) && $data != NULL)
        {
            /* Validation - starts */
            if ($this->form_validation->run('admin_api/flags/list') == FALSE)
            { // for web
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = 511;
                $this->return['Message'] = $error; //Shows all error messages as a string
            }
            else
            {
                $entity_type = isset($this->post_data['EntityType']) ? $this->post_data['EntityType'] : "";
                $search_string = isset($this->post_data['SearchKey']) ? $this->post_data['SearchKey'] : "";
                $start_offset = isset($this->post_data['Begin']) ? $this->post_data['Begin'] : 0;
                $end_offset = isset($this->post_data['End']) ? $this->post_data['End'] : 10;
                $sort_by = isset($this->post_data['SortBy']) ? $this->post_data['SortBy'] : '';
                $order_by = isset($this->post_data['OrderBy']) ? $this->post_data['OrderBy'] : '';

                $start_date = isset($this->post_data['StartDate']) ? $this->post_data['StartDate'] : '';
                $end_date = isset($this->post_data['EndDate']) ? $this->post_data['EndDate'] : '';
                
                if($entity_type == 'Page') {
                    $this->check_module_status(18);
                }
                

                $records = $this->flags_model->get_list($entity_type, $start_offset, $end_offset, $start_date, $end_date, $search_string, $sort_by, $order_by);

                $this->return['Data']['total_records'] = $records['total_records'];
                
                foreach ($records['results'] as $key => $value) {
                    
                   $module_entity_id= $value['ActivityID']; 
                   $images =  $this->media_model->get_media_type_posts_media($module_entity_id);
                    foreach ($images as $key1 => $value1) {
                    
                         if($value1['ImageName']){
                            $image =    get_image_path('wall',$value1['ImageName']);
                            $images[$key1]['ImageUrl']=$image;
                         }    
                    }
                    $records['results'][$key]['images']=$images;
                    
                }
                $this->return['Data']['results'] = $records['results'];
                //----------------------

               
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $this->return['ResponseCode'] = '519';
            $this->return['Message'] = lang('input_invalid_format');
        }
        //send response
        $this->response($this->return);
    }

    /**
     * Remove flag
     */
    function remove_post()
    {
        $data = $this->post_data;
        if (isset($data) && $data != NULL)
        {
            /* Validation - starts */
            if ($this->form_validation->run('admin_api/flag/remove') == FALSE)
            { // for web
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = 511;
                $this->return['Message'] = $error; //Shows all error messages as a string
            }
            else
            {
                $entity_id = 0;
                $entity_type = isset($this->post_data['EntityType']) ? strtoupper($this->post_data['EntityType']) : "";
                $entity_guid = isset($this->post_data['EntityGUID']) ? $this->post_data['EntityGUID'] : "";
                switch ($entity_type):
                    case 'ACTIVITY':
                        $entity = get_detail_by_guid($data['EntityGUID'], 19, "ActivityID", 2);
                        $entity_id = isset($entity['ActivityID']) ? $entity['ActivityID'] : 0;
                        break;
                    case 'BLOG':
                        $entity = get_detail_by_guidyyy($data['EntityGUID'], 24, "BlogID", 2);
                        $entity_id = isset($entity['BlogID']) ? $entity['BlogID'] : 0;
                        break;
                    case 'PAGE':
                        $entity = get_detail_by_guid($data['EntityGUID'], '18', "PageID", 2);
                        $entity_id = isset($entity['PageID']) ? $entity['PageID'] : 0;
                        break;
                    case 'USER':
                        $entity = get_detail_by_guid($data['EntityGUID'], '3', "UserID", 2);
                        $entity_id = isset($entity['UserID']) ? $entity['UserID'] : 0;
                endswitch;
                $this->flags_model->remove_flag($entity_type, $entity_id);
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $this->return['ResponseCode'] = '519';
            $this->return['Message'] = lang('input_invalid_format');
        }
        //send response
        $this->response($this->return);
    }

    public function entityflags_post()
    {
        $data = $this->post_data;
        if (isset($data) && $data != NULL)
        {
            /* Validation - starts */
            if ($this->form_validation->run('admin_api/flag/entityflags') == FALSE)
            { // for web
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = 511;
                $this->return['Message'] = $error; //Shows all error messages as a string
            }
            else
            {
                $entity_id = 0;
                $entity_type = isset($this->post_data['EntityType']) ? strtoupper($this->post_data['EntityType']) : "";
                $entity_guid = isset($this->post_data['EntityGUID']) ? $this->post_data['EntityGUID'] : "";
                switch ($entity_type):
                    case 'ACTIVITY': 
                        $entity = get_detail_by_guid($data['EntityGUID'], '19', "ActivityID", 2);
                        $entity_id = isset($entity['ActivityID']) ? $entity['ActivityID'] : 0;
                        break;
                    case 'BLOG':
                        $entity = get_detail_by_guid($data['EntityGUID'], '24', "BlogID", 2);
                        $entity_id = isset($entity['BlogID']) ? $entity['BlogID'] : 0;
                        break;
                    case 'PAGE':
                        $entity = get_detail_by_guid($data['EntityGUID'], '18', "PageID", 2);
                        $entity_id = isset($entity['PageID']) ? $entity['PageID'] : 0;
                        break;
                    case 'USER':
                        $entity = get_detail_by_guid($data['EntityGUID'], '3', "UserID", 2);
                        $entity_id = isset($entity['UserID']) ? $entity['UserID'] : 0;
                        break;
                endswitch;
                $this->return['Data'] = $this->flags_model->entity_flags($entity_type, $entity_id);
                if(!empty($this->return['Data'][0]['ImageName'])){
                $image =    get_image_path($type = 'wall',$this->return['Data'][0]['ImageName']);
                $this->return['Data'][0]['completeImage'] =$image;
                }
                
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $this->return['ResponseCode'] = '519';
            $this->return['Message'] = lang('input_invalid_format');
        }
        //send response
        $this->response($this->return);
    }

    /**
     * Delete Entity
     */
    function delete_post()
    {
        $data = $this->post_data;
        if (isset($data) && $data != NULL)
        {
            /* Validation - starts */
            if ($this->form_validation->run('admin_api/flag/remove') == FALSE)
            { // for web
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = 511;
                $this->return['Message'] = $error; //Shows all error messages as a string
            }
            else
            {
                $entity_id = 0;
                $entity_type = isset($this->post_data['EntityType']) ? strtoupper($this->post_data['EntityType']) : "";
                $entity_guid = isset($this->post_data['EntityGUID']) ? $this->post_data['EntityGUID'] : "";
                switch ($entity_type):
                    case 'ACTIVITY':
                        $entity = get_detail_by_guid($data['EntityGUID'], 19, "ActivityID", 2);
                        $entity_id = isset($entity['ActivityID']) ? $entity['ActivityID'] : 0;
                        
                        $data = array('StatusID' => 3, 'ModifiedDate' => date('Y-m-d H:i:s'));
                        $this->db->where('ActivityID', $entity_id);
                        $this->db->update(ACTIVITY, $data);
                        break;
                    case 'PAGE':
                        $entity = get_detail_by_guid($data['EntityGUID'], '18', "PageID", 2);
                        $entity_id = isset($entity['PageID']) ? $entity['PageID'] : 0;
                        
                        $data = array('StatusID' => 3, 'ModifiedDate' => date('Y-m-d H:i:s'));
                        $this->db->where('PageID', $entity_id);
                        $this->db->update(PAGES, $data);
                        break;
                    case 'USER':
                        $entity = get_detail_by_guid($data['EntityGUID'], '3', "UserID", 2);
                        $entity_id = isset($entity['UserID']) ? $entity['UserID'] : 0;
                        
                        $data = array('StatusID' => 3, 'ModifiedDate' => date('Y-m-d H:i:s'));
                        $this->db->where('UserID', $entity_id);
                        $this->db->update(USERS, $data);
                        break;
                endswitch;
                //$this->flags_model->remove_flag($entity_type, $entity_id);
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $this->return['ResponseCode'] = '519';
            $this->return['Message'] = lang('input_invalid_format');
        }
        //send response
        $this->response($this->return);
    }
    
    /**
     * block Entity
     */
    function block_post()
    {
        $data = $this->post_data;
        if (isset($data) && $data != NULL)
        {
            /* Validation - starts */
            if ($this->form_validation->run('admin_api/flag/remove') == FALSE)
            { // for web
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = 511;
                $this->return['Message'] = $error; //Shows all error messages as a string
            }
            else
            {
                $entity_id = 0;
                $entity_type = isset($this->post_data['EntityType']) ? strtoupper($this->post_data['EntityType']) : "";
                $entity_guid = isset($this->post_data['EntityGUID']) ? $this->post_data['EntityGUID'] : "";
                switch ($entity_type):
                    case 'USER':
                        $entity = get_detail_by_guid($data['EntityGUID'], '3', "UserID", 2);
                        $entity_id = isset($entity['UserID']) ? $entity['UserID'] : 0;
                        
                        $data = array('StatusID' => 4, 'ModifiedDate' => date('Y-m-d H:i:s'));
                        $this->db->where('UserID', $entity_id);
                        $this->db->update(USERS, $data);
                        break;
                endswitch;
                //$this->flags_model->remove_flag($entity_type, $entity_id);
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $this->return['ResponseCode'] = '519';
            $this->return['Message'] = lang('input_invalid_format');
        }
        //send response
        $this->response($this->return);
    }
}
