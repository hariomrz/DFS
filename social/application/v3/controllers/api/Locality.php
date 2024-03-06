<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Locality extends Common_API_Controller {

    function __construct($bypass = false) {
        parent::__construct($bypass);
        $this->load->model(array('locality/locality_model'));
    }

    /**
     * Function Name: locality list
     */
    public function index_get() {
        $return = $this->return;
        $return['Data'] = $this->locality_model->get_locality_list();
        $this->response($return);
    }
    
    /**
     * Function Name: locality list
     */
    public function list_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {            
            $return['Data'] = $this->locality_model->get_locality_list($data);
        } else {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
        }    
        $this->response($return);
    }
    
    /**
     * Function Name: ward User Count
     */
    function ward_user_count_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {   
            $user_id = $this->UserID;
            $order_by = 'Recent';
            $sort_by = 'DESC';
            $ward_id = safe_array_key($data, 'WID', 0);
            if(empty($ward_id) && $this->LocalityID) {
                $localty = $this->locality_model->get_locality($this->LocalityID);
                if(!empty($localty["WID"])) {
                    $ward_id = $localty["WID"];
                }
            }       
        
            $this->load->model(array('users/user_model'));
            $is_admin = $this->user_model->is_super_admin($user_id, 1);
            
            $total_records = $this->user_model->directory(1, 10, '', $is_admin, 1, $user_id, $order_by, $sort_by, $ward_id);
            $return['TotalRecords'] = $total_records;
        } else {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
        }    
        $this->response($return);
    }
    
    function add_locality_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) { 
            $config = array(
                array(
                    'field' => 'WID',
                    'label' => 'ward id',
                    'rules' => 'trim|required|integer'
                ),
                array(
                    'field' => 'Name',
                    'label' => 'locality name',
                    'rules' => 'trim|required|max_length[50]'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $ward_id = $data['WID'];
                $locality_name = $data['Name'];
                $this->load->model(array('ward/ward_model'));
                $is_ward_exist = $this->ward_model->is_ward_exist($ward_id);
                if($is_ward_exist) {
                    $locality_id = $this->locality_model->is_locality_name_exist($locality_name, $ward_id);
                    if(empty($locality_id)) {
                        $data['StatusID'] = 4;
                        $locality_id = $this->locality_model->add_locality_name($data);
                    }
                    $return['Data']['LocalityID'] = $locality_id;
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = 'Invalid ward id';
                }
            }
        } else {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
        }    
        $this->response($return);
    }

    public function user_list_post() {
        $return = $this->return;
        $return['Data'] = $this->locality_model->user_list();
        $this->response($return);
    }
}

?>