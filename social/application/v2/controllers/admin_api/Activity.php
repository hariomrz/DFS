<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* All process like : smtp_email_listing
* @package    EmailSetting
* @author     Girish Patidar(23-01-2015)
* @version    1.0
*/

class Activity extends Admin_API_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/activity/activity_entities_model'));
    }
        
    public function get_user_activity_entities_post() {
        $return['ResponseCode']='200';
        $return['Message']= lang('success');
        $return['ServiceName']='admin_api/activity/get_user_activity_entities';
        $return['Data']=array();
        $data = $this->post_data;
        
        // Check data posted
        if (!isset($data) || !$data) {
            $return['ResponseCode'] = '519';
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }
        
        /* Validation - starts */
        if ($this->form_validation->run('api/admin/activity/get_activity_entities') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 511;
            $return['Message'] = $error; //Shows all error messages as a string
            $this->response($return);
        } 
        
        $page_no = (int)isset($data['page_no']) ? $data['page_no'] : 1;
        $page_size = (int)isset($data['page_size']) ? $data['page_size'] : 20;
        
        $return['Data'] = $this->activity_entities_model->get_user_activity_entities($data['UserID'], $page_no, $page_size);
        $this->response($return);
    }
    
        
}//End of file ipsetting.php