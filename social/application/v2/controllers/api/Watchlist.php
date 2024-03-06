<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
* This Class used as REST API for Favourit module
* @category	Controller
* @author		Vinfotech Team
*/
class Watchlist extends Common_API_Controller 
{

     /**
     * @Summary: call parent constructor
     * @access: public
     * @param:
     * @return:
     */
    function __construct($bypass = false) 
    {
      parent::__construct($bypass);
      $this->check_module_status(16);
      $this->load->model(array('activity/watchlist_model'));
    }

    public function index_post()
    {
        $return = $this->return;
        $return['ResponseCode'] = 500;
        $return['Message'] = lang('input_invalid_format');   
        $this->response($return);   
    }
    /**
    * [toggle_watchlist_post used to set entity in watchlist ]
    */
    public function toggle_watchlist_post()
    {         
      /* Define variables - starts */
      $return = $this->return;       
      /* Define variables - ends */
      $data = $this->post_data;
      if (isset($data)) {               
        /* Validation - starts */
        if ($this->form_validation->run('api/watchlist/toggle_watchlist') == FALSE) 
        {
          $error = $this->form_validation->rest_first_error_string();         
          $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
          $return['Message']      = $error; 
        } 
        else /* Validation - ends */
        { 
          $data['UserID'] = $this->UserID; 
          $result         = $this->watchlist_model->toggle_watchlist($data); 
          $return['ResponseCode'] = $result['ResponseCode'];
          $return['Message'] = $result['Message']; 
          $return['Data'] = $result['Data']; 
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