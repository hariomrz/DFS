<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Contact extends Common_API_Controller {

    function __construct($bypass = false) {
        parent::__construct($bypass);
    }

    /**
     * Function Name: Contact us
     */
    public function index_post() {
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;  
         if ($data) {            
            $config = array(
                array(
                    'field' => 'Name',
                    'label' => 'name',
                    'rules' => 'trim|required|max_length[50]'
                ),
                array(
                    'field' => 'Mobile',
                    'label' => 'mobile',
                    'rules' => 'trim|required|numeric|min_length[10]|max_length[10]'
                ),
                array(
                    'field' => 'Message',
                    'label' => 'message',
                    'rules' => 'trim|required|max_length[200]'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $emailData = array("data" => $data);
                $emailData['content_view'] = 'emailer/contact';
                
                $layout = $this->config->item("email_layout");
                $message = $this->load->view($layout, $emailData, TRUE);            
                $to      	= VCA_SUPPORT_EMAIL;
                $subject 	= SITE_NAME.' locality enquiry';                
                @sendMail(array(), $to, $subject, $message);
            }
         } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }
}
?>