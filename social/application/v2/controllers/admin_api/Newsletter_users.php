<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Newsletter_users extends Admin_API_Controller {

    public function __construct() {
        parent::__construct();

        if ($this->settings_model->isDisabled(35)) {
            $this->return['Message'] = lang('resource_is_blocked');
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }

        $this->load->model(array(
            'admin/media_model',
            'admin/newsletter/newsletter_users_model',
            'admin/newsletter/newsletter_model'
        ));
    }

    public function get_user_list_post() { 
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        $return['Data'] = (array) $this->newsletter_users_model->get_users($data);

        $download = (int) isset($data['Download']) ? $data['Download'] : 0;
        if ($download && !empty($return['Data']['users'])) {
            $return['Data'] = $this->newsletter_users_model->download_users($return['Data']['users']);
        }

        $this->response($return);
    }
    
    public function search_users_get() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $_GET;
        if (empty($data['Name'])) {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        $return['Data'] = (array) $this->newsletter_users_model->search_users($data);        

        $this->response($return);
    }

    /**
     * Function for change status of particular user.
     * Parameters : 1-waitingforApproval, 2-unblock,approve, 3-delete, 4-block
     */
    public function change_status_post() {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Message'] = lang('success');
        $return['ServiceName'] = 'admin_api/newsletter_users/change_status';
        $return['Data'] = array();
        $data = $this->post_data;


        if (!$data) {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($Outputs);
        }

        //Change status query for a user
        $this->newsletter_users_model->change_user_status($data);

        /* Final Output */
        $Outputs = $return;
        $this->response($Outputs);
    }

    /**
     * Function for change status of particular user.
     * Parameters : 1-waitingforApproval, 2-unblock,approve, 3-delete, 4-block
     */
    public function edit_user_post() {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Message'] = lang('success');
        $return['Data'] = array();
        $data = $this->post_data;

        if (!$data) {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($Outputs);
        }


        $this->form_validation->set_rules('NewsLetterSubscriberID', 'NewsLetterSubscriberID', 'trim|required');
        $this->form_validation->set_rules('FirstName', 'FirstName', 'trim|required');
        $this->form_validation->set_rules('LastName', 'LastName', 'trim|required');
        $this->form_validation->set_rules('DOB', 'DOB', 'trim|required');
        $this->form_validation->set_rules('Gender', 'Gender', 'trim|required');
        //$this->form_validation->set_rules('CityID', 'CityID', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($Outputs);
        }

        //Change status query for a user
        $this->newsletter_users_model->update_newsletter_subscriber_details($data, array(
            'NewsLetterSubscriberID' => $data['NewsLetterSubscriberID']
        ));

        $this->response($return);
    }

    /**
     * Function for change status of particular user.
     * Parameters : 1-waitingforApproval, 2-unblock,approve, 3-delete, 4-block
     */
    public function get_user_groups_post() {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Message'] = lang('success');
        $return['Data'] = array();
        $data = $this->post_data;

        if (!$data) {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($Outputs);
        }


        $this->form_validation->set_rules('NewsLetterSubscriberID', 'NewsLetterSubscriberID', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($Outputs);
        }

        //Change status query for a user
        $return['Data'] = $this->newsletter_model->get_user_groups($data);

        $this->response($return);
    }

    public function location_auto_suggest_get() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (isset($data)) {
            $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
            $page_no = (isset($data['PageNo']) && $data['PageNo'] > 0) ? $data['PageNo'] : '1';
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : '20';
            $this->load->model(array('util/util_location_model'));
            $return['Data'] = $this->util_location_model->location_auto_suggest($search_keyword, $page_no, $page_size);
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [upload_users uploads user profile data for workhigh from Admin]
     * @return [json] [success / error message and response code]
     */
    public function upload_users_post() {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Message'] = lang('success');

        $return['Data'] = array();
        $data = $this->post_data;

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
                $return = $this->newsletter_users_model->run_uploaded_profile($file_data); //['upload_data']['file_name']);                
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

//End of file ipsetting.php
