<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
* This Class used as REST API for Reminder module
* @category	Controller
* @author		Vinfotech Team
*/
class Reminder extends Common_API_Controller 
{

     /**
     * @Summary: call parent constructor
     * @access: public
     * @param:
     * @return:
     */
    function __construct() 
    {
      parent::__construct();
      $this->check_module_status(28);
      $this->load->model(array('reminder/reminder_model'));
    }

    public function index_post()
    {
        $return = $this->return;
        $return['ResponseCode'] = 500;
        $return['Message'] = lang('input_invalid_format');   
        $this->response($return);   
    }

    /**
    * [add_post used to set entity as for reminder]
    */
    public function add_post()
    {         
        /* Define variables - starts */
        $return = $this->return;       
        /* Define variables - ends */
        $data = $this->post_data;
        if (isset($data)) 
        {               
            /* Validation - starts */
            if ($this->form_validation->run('api/reminder/add') == FALSE) 
            {
              $error = $this->form_validation->rest_first_error_string();         
              $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
              $return['Message']      = $error; 
            } 
            else /* Validation - ends */
            { 
              $data['UserID'] = $this->UserID; 
              $return         = $this->reminder_model->add($data);
            }
        } 
        else 
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return); 
    }

    /**
    * [edit_post used to update reminder details]
    */
    public function edit_post()
    {     
        /* Define variables - starts */
        $return = $this->return;       
        /* Define variables - ends */
        $data = $this->post_data;
        if (isset($data)) 
        {               
            /* Validation - starts */
            if ($this->form_validation->run('api/reminder/edit') == FALSE) 
            {
              $error = $this->form_validation->rest_first_error_string();         
              $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
              $return['Message']      = $error; 
            } 
            else /* Validation - ends */
            { 
              $data['UserID'] = $this->UserID; 
              $return         = $this->reminder_model->edit($data);
            }
        } 
        else 
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return); 
    }

    /**
    * [details_post used to get reminder details]
    */
    public function details_post()
    {         
        /* Define variables - starts */
        $return = $this->return;       
        /* Define variables - ends */
        $data = $this->post_data;
        if (isset($data)) 
        {               
            /* Validation - starts */
            if ($this->form_validation->run('api/reminder/details') == FALSE) 
            {
              $error = $this->form_validation->rest_first_error_string();         
              $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
              $return['Message']      = $error; 
            } 
            else /* Validation - ends */
            { 
              $return         = $this->reminder_model->details($data['ReminderGUID']);
            }
        } 
        else 
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return); 
    }

    /**
    * [get_reminder_count_by_date_post used to get date wise reminder count]
    */
    public function get_reminder_count_by_date_post()
    {         
        /* Define variables - starts */
        $return = $this->return;       
        /* Define variables - ends */
        $data = $this->post_data;
        if (isset($data)) 
        {   
          $return['Data']    = $this->reminder_model->get_reminder_count_by_date($this->UserID);
        } 
        else 
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return); 
    }

    /**
     * [delete_post Used to delete reminder]
     * @return [type] [description]
     */
    public function delete_post()
    {
        /* Define variables - starts */
        $return = $this->return;       
        /* Define variables - ends */
        $data = $this->post_data;
        if (isset($data)) 
        {
            /* Validation - starts */
            if ($this->form_validation->run('api/reminder/delete') == FALSE) 
            {
              $error = $this->form_validation->rest_first_error_string();         
              $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
              $return['Message']      = $error; 
            } 
            else /* Validation - ends */
            {    
                $return    = $this->reminder_model->delete($data['ReminderGUID'], $this->UserID);
            }
        } 
        else 
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);

    }

    /**
     * [change_status_post Used to update reminder status]
     * @return [type] [description]
     */
    public function change_status_post()
    {
        /* Define variables - starts */
        $return = $this->return;       
        /* Define variables - ends */
        $data = $this->post_data;
        if (isset($data)) 
        {   
            /* Validation - starts */
            if ($this->form_validation->run('api/reminder/change_status') == FALSE) 
            {
                $error = $this->form_validation->rest_first_error_string();         
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message']      = $error; 
            } 
            else /* Validation - ends */
            {  
                $data['UserID'] = $this->UserID; 
                $return    = $this->reminder_model->change_status($data);
            }
        } 
        else 
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);

    }
}