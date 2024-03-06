<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of category
 *
 * @author nitins
 */
class Category extends Admin_API_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/category_model', 'admin/login_model','notification_model'));
        $this->load->language('category');
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200)
        {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
    }

    public function index_post()
    {
        $this->list_post();
    }
    public function list_post()
    {
        $return['ResponseCode'] = '200';
        $return['Message'] = lang('success');
        $return['ServiceName'] = 'admin_api/category';
        $return['Data'] = array();
        $data = $this->post_data;

        //Set rights id by action(register,delete,blocked,waiting for approval users)
        if (isset($data['IsActive']))
        {
            $is_active = $data['IsActive'];
        } else
        {
            $is_active = '';
        }
        $rights_id = getRightsId('category_admin');


        if (!in_array($rights_id, getUserRightsData($this->DeviceType)))
        {
            $return['ResponseCode'] = '598';
            $return['Message'] = lang('permission_denied');
            /* Final Output */
            $outputs = $return;
            $this->response($outputs);
        }

        if (isset($data) && $data != NULL)
        {
            if (isset($data['Begin']))
            {
                $start_offset = $data['Begin'];
            } else
            {
                $start_offset = 0;
            }

            if (isset($data['End']))
            {
                $end_offset = $data['End'];
            } else
            {
                $end_offset = 10;
            }

            if (isset($data['SearchKeyword']))
            {
                $search_keyword = $data['SearchKeyword'];
            } else
            {
                $search_keyword = '';
            }
            $search_keyword = str_replace("_", " ", $search_keyword);

            if (isset($data['StartDate']))
            {
                $start_date = $data['StartDate'];
            } else
            {
                $start_date = '';
            }

            if (isset($data['EndDate']))
            {
                $end_date = $data['EndDate'];
            } else
            {
                $end_date = '';
            }

            if (isset($data['IsActive']))
            {
                $is_active = $data['IsActive'];
            } else
            {
                $is_active = '';
            }

            if (isset($data['SortBy']))
            {
                $sort_by = $data['SortBy'];
            } else
            {
                $sort_by = '';
            }

            if (isset($data['OrderBy']))
            {
                $order_by = $data['OrderBy'];
            } else
            {
                $order_by = '';
            }

            $module_id = (!empty($data['ModuleID'])?$data['ModuleID']:'');
            $locality_id = (!empty($data['LocalityID'])?$data['LocalityID']:'');

            $temp_results = array();
            $commission_temp = $this->category_model->get_list($start_offset, $end_offset, $start_date, $end_date, $module_id, $search_keyword, $sort_by, $order_by, $locality_id);

            $return['Data']['total_records'] = $commission_temp['total_records'];
            $return['Data']['results'] = $commission_temp['results'];
        } else
        {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = '519';
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $outputs = $return;
        foreach ($outputs['Data']['results'] as $key => $value)
        {
           $outputs['Data']['results'][$key]['media'] = $this->category_model->get_cat_media($value['category_id']);
            // 
        }
        $this->response($outputs);
    }

    public function update_status_post()
    {
        $return['ResponseCode'] = '200';
        $return['Message'] = lang('success');
        $return['ServiceName'] = 'admin_api/update_status';
        $return['Data'] = array();
        $data = $this->post_data;

        //Set rights id by action(register,delete,blocked,waiting for approval users)
        $rights_id = getRightsId('category_active_inactive_event');

        if (!in_array($rights_id, getUserRightsData($this->DeviceType)))
        {
            $return['ResponseCode'] = '598';
            $return['Message'] = lang('permission_denied');
            /* Final Output */
            $outputs = $return;
            $this->response($outputs);
        }
        if (isset($data) && $data != NULL)
        {
            $category_id = isset($data['CategoryID']) ? $data['CategoryID'] : '';
            $status_id = isset($data['StatusID']) ? $data['StatusID'] : '';
            $commission = $this->category_model->update_status($category_id, $status_id);
            $return['Message'] = lang('Category_saved_success');
        } else
        {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = '519';
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * All category list
     */
    function all_post()
    {
        $return['ResponseCode'] = '200';
        $return['Message'] = lang('success');
        $return['ServiceName'] = 'admin_api/all';
        $data = $this->post_data;
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : '';
        $category_id = isset($data['CategoryId']) ? $data['CategoryId'] :"";
        $locality_id = isset($data['LocalityID']) ? $data['LocalityID'] :"";
        $results = $this->category_model->get_category_dropdown($module_id, $category_id);
        $return['Data'] = array(
            'results' => $results,
            'total_records' => count($results)
        );
        $this->response($return);
    }

    function add_post()
    {
        $return['ResponseCode'] = '200';
        $return['Message'] = lang('Category_saved_success');
        $return['ServiceName'] = 'admin_api/add';
        $data = $this->post_data;
         $user_id = $this->UserID;

        //Set rights id by action(register,delete,blocked,waiting for approval users)
        $rights_id = getRightsId('category_add_event');

        if (!in_array($rights_id, getUserRightsData($this->DeviceType)))
        {
            $return['ResponseCode'] = '598';
            $return['Message'] = lang('permission_denied');
            /* Final Output */
            $outputs = $return;
            $this->response($outputs);
        }
        if (isset($data) && $data != NULL)
        {
            /* Validation - starts */
            if ($this->form_validation->run('api/add_category') == FALSE)
            { // for web
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = 511;
                $return['Message'] = $error; //Shows all error messages as a string
            } 
            else
            {
                $media_id = NULL;
                if(!empty($data['MediaGUID'])){
                    $details = get_detail_by_guid($data['MediaGUID'],21,'MediaID',2);
                    $media_id = $details['MediaID'];
                }
                
                $category = array(                   
                    'ModuleID' => isset($data['ModuleID']) ? $data['ModuleID'] : 14,
                    'ParentID' => isset($data['ParentCategoryID']) ? (int) $data['ParentCategoryID'] : 0,
                    'Name' => $data['Name'],
                    'Description' => $data['Description'],
                    'Icon' => '',
                    'MediaID' => $media_id,
                    'LocalityID' => $data['LocalityID'],
                    'Address' => isset($data['Address']) ? $data['Address'] : '',
                    'Mobile' => isset($data['PhoneNumber']) ? $data['PhoneNumber'] : '',
                    'OwnerName' => isset($data['Owner']) ? $data['Owner'] : '',
                    'Miscellaneous' => isset($data['Miscellaneous']) ? $data['Miscellaneous'] : '',
                    'CreatedDate' => date("Y-m-d"),
                    'StatusID' => 2
                );

                $category = $this->category_model->save_category($category);

                $media = isset($data['Media']) ? $data['Media'] : array();
                $this->category_model->update_category_media($category, $media);

                $return['Message'] = lang('Category_saved_success');
            }
        } 
        else
        {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = '519';
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    function edit_post()
    {
        $return['ResponseCode'] = '200';
        $return['Message'] = lang('Category_update_success');
        $return['ServiceName'] = 'admin_api/edit';
        $data = $this->post_data;
         $user_id = $this->UserID;

        //Set rights id by action(register,delete,blocked,waiting for approval users)
        $rights_id = getRightsId('category_edit_event');

        if (!in_array($rights_id, getUserRightsData($this->DeviceType)))
        {
            $return['ResponseCode'] = '598';
            $return['Message'] = lang('permission_denied');
            /* Final Output */
            $outputs = $return;
            $this->response($outputs);
        }
        if (isset($data) && $data != NULL)
        {
            /* Validation - starts */
            if ($this->form_validation->run('api/edit_category') == FALSE)
            { // for web
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = 511;
                $return['Message'] = $error; //Shows all error messages as a string
            } 
            else
            {
                $media_id = NULL;
                if(!empty($data['MediaGUID'])){
                    $details = get_detail_by_guid($data['MediaGUID'],21,'MediaID',2);
                    $media_id = $details['MediaID'];
                }
                
                $category = array(
                    'CategoryID' => $data['CategoryID'],
                    'ModuleID' => isset($data['ModuleID']) ? $data['ModuleID'] : 14,
                    'ParentID' => isset($data['ParentCategoryID']) ? (int) $data['ParentCategoryID'] : 0,
                    'LocalityID' => $data['LocalityID'],
                    'Name' => $data['Name'],
                    'Description' => $data['Description'],
                    'Address' => isset($data['Address']) ? $data['Address'] : '',
                    'Mobile' => isset($data['PhoneNumber']) ? $data['PhoneNumber'] : '',
                    'OwnerName' => isset($data['Owner']) ? $data['Owner'] : '',
                    'Miscellaneous' => isset($data['Miscellaneous']) ? $data['Miscellaneous'] : '',
                    'Icon' => '',
                    'MediaID' => $media_id,
                    'StatusID' => 2
                );
                $category = $this->category_model->save_category($category,$user_id);

                $media = isset($data['Media']) ? $data['Media'] : array();
                $this->category_model->update_category_media($category, $media);

                $return['Message'] = lang('Category_update_success');
            }
        } else
        {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = '519';
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    
    /**
     * [upload_category uploads category data]
     * @return [json] [success / error message and response code]
     */
    function upload_category_post() {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Message'] = lang('success');

        $return['Data'] = array();
        $data = $this->post_data;
        
        $this->form_validation->set_rules('ModuleID', 'Module', 'trim|required');
        $this->form_validation->set_rules('LocalityID', 'Locality', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }

        $dir_name = PATH_IMG_UPLOAD_FOLDER;

        if (!is_dir($dir_name))
            mkdir($dir_name, 0777);

        $config['upload_path'] = './' . $dir_name;
        $config['allowed_types'] = 'xls|XLS|xlsx|XLSX'; //only excel in given format is allowed
        $config['max_size'] = 4000; //4MBs

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('qqfile')) {
            // $return['Error'] = array('error' => $this->upload->display_errors());    
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Errors = $this->upload->error_msg;
            $return['Message'] = $this->upload->display_errors(); // first message
        } else {
            $file_data = array('upload_data' => $this->upload->data());

            if (isset($file_data) && !empty($file_data)) {
                //debug start
                // ini_set("memory_limit",-1);
                //Run UPloaded File now
                $module_id = trim($data['ModuleID']);
                $locality_id = trim($data['LocalityID']);
                $return = $this->category_model->run_uploaded_profile($file_data, $module_id, $locality_id); //['upload_data']['file_name']);                
                //debug end
                // set_time_limit(0);

                if (isset($return['Error'])) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = "Error";
                } else {
                    $return['MessageShow'] = $return['Message'];
                    $return['ResponseCode'] = self::HTTP_OK;
                    $return['Message'] = "Success";   
                    $return['excel_errors_fixes'] = $return['excel_errors_fixes']; 
                    $return['ServiceName'] = "users/upload_users";
                }
                //Unlink uploaded file before proceeding                
                //unlink($file_data['upload_data']['full_path']);
            } else {
                $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
                ;
                $return['Error'] = TRUE;
                $return['Message'] = "Invalid File data!";
            }
        }

        /* Final Output */
        $this->response($return);
    }

}
