<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ward extends Admin_API_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(array('ward/ward_model', 'admin/login_model'));        
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
        $this->UserTypeID    = $logged_user_data['Data']['UserTypeID'];
    }

    public function index() {
        $this->user_count();
    }
    
    /**
     * Function Name: WARD list
     */
    public function user_count_post() {
        $return = $this->return;
        
        $last_five_day = array();
        for($i=5; $i>=1; $i--) {
            $day_date = get_current_date('%Y-%m-%d', $i);
            $last_five_day[$day_date]['name'] = date("d M", strtotime($day_date));
            $last_five_day[$day_date]['total'] = 0;
        }
        $result = $this->ward_model->ward_user_count($last_five_day);
        $return['Data'] = $result['ward_list'];
        $return['TotalUser'] = $result['total_user'];
        $return['LastFiveDay'] = $result['last_five_day'];
        $this->response($return);
    }

     /**
     * Function Name: engagement list
     */
    public function engagement_post() {
        $return = $this->return;
        
        $last_five_day = array();
        for($i=5; $i>=1; $i--) {
            $day_date = get_current_date('%Y-%m-%d', $i);
            $last_five_day[$day_date]['name'] = date("d M", strtotime($day_date));
            $last_five_day[$day_date]['total_post'] = 0;
            $last_five_day[$day_date]['total_comment'] = 0;
            $last_five_day[$day_date]['total_like'] = 0;
        }
        $result = $this->ward_model->ward_engagement($last_five_day);
        $return['Data'] = $result['ward_list'];
        $return['TotalPost'] = $result['total_post'];
        $return['LastFiveDay'] = $result['last_five_day'];
        $this->response($return);
    }

    /**
     * Function Name: locality list
     */
    public function locality_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {           
            $page_no    = safe_array_key($data, 'PageNo', 1); 
            $this->load->model(array('locality/locality_model'));
            if($page_no == 1) {
                $data['Count'] = 1;
                $return['TotalRecord'] = $this->locality_model->admin_locality_list($data);
            }
            $data['Count'] = 0;
            $return['Data'] = $this->locality_model->admin_locality_list($data);
            
        } else {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
        }    
        $this->response($return);
    }

    /**
     * Function Name: save locality
     */
    public function save_locality_post() {
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
                    'label' => 'locality english name',
                    'rules' => 'trim|required|max_length[50]'
                ),
                array(
                    'field' => 'HindiName',
                    'label' => 'locality hindi name',
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
                $locality_id = safe_array_key($data, 'LocalityID', 0);
                $this->load->model(array('locality/locality_model')); 
                $locality_id = $this->locality_model->is_locality_name_exist($locality_name, $ward_id, $locality_id);
                if(empty($locality_id)) {
                    $this->locality_model->add_locality_name($data);  
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = "This locality name already exist.";
                }  
            }       
        } else {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
        }    
        $this->response($return);
    }

    /**
     * Function Name: ward list
     */
    public function list_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $ward_list = $this->ward_model->get_ward_list($data);     
           /* $ward_list = array_column($ward_list,null,'WID');    
            if(isset($ward_list[1])) {  
                unset($ward_list[1]);
            }
            $ward_list = array_values($ward_list);
            */
            $return['Data'] = $ward_list ;
        } else {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
        }    
        $this->response($return);
    }
    
    /**
     * [mark_user_as_feature Used to save feature user]
     * @return [array] [Response details]
    */
    function mark_user_as_feature_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'UserID',
                    'label' => 'user id',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'About',
                    'label' => 'about',
                    'rules' => 'trim'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $ward_ids = isset($data['WardIds']) ? $data['WardIds'] : array();
                $user_id = $data['UserID'];
                $eid = safe_array_key($data, 'EID', 0);  
                if(!empty($ward_ids)) {                          
                    $this->ward_model->save_feature_user($user_id, $ward_ids, $eid);
                    $about = safe_array_key($data, 'About');
                    if(!empty($about))  {
                        $this->load->model(array('admin/users_model'));
                        $this->users_model->set_user_value($user_id, 'UserWallStatus', $about);
                    }
                    
                    $return['Message'] = 'User marked as feature successfully.';
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('ward_required');
                } 
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    /**
     * [remove_user_as_feature Used to remove featured user]
     * @return [array] [Response details]
    */
    function remove_user_as_feature_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'UserID',
                    'label' => 'user id',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $ward_id = 0;//safe_array_key($data, 'WID', 0);
                $user_id = $data['UserID'];                          
                $this->ward_model->remove_feature_user($user_id, $ward_id);                        
                $return['Message'] = 'User removed as feature successfully.';               
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    /**
     * [set_pinned_feature_user Used to set featured user as pinned]
     * @return [array] [Response details]
    */
    function set_pinned_feature_user_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'WFUID',
                    'label' => 'ward feature user id',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $wf_uid = $data['WFUID'];                          
                $this->ward_model->set_pinned_feature_user($wf_uid);                        
                $return['Message'] = 'User pinned successfully.';               
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    /**
     * [set_pinned_feature_user Used to set featured user as pinned]
     * @return [array] [Response details]
    */
    function remove_pinned_feature_user_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'WFUID',
                    'label' => 'ward feature user id',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $wf_uid = $data['WFUID'];                          
                $this->ward_model->remove_pinned_feature_user($wf_uid);                        
                $return['Message'] = 'User unpinned successfully.';               
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    /**
     * [feature_user_ward Used to get ward list for user for which it is marked as feature]
     * @return [array] [Response details]
    */
    function feature_user_ward_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'UserID',
                    'label' => 'user id',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id = $data['UserID'];                         
                $return['Data'] = $this->ward_model->feature_user_ward($user_id);                
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

 


}
?>