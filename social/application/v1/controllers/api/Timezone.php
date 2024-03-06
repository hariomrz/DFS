<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 * This Class used as REST API for Timezone module
 * @package		CodeIgniter
 * @category	Controller
 * @author		Vinfotech Team
 */
class Timezone extends Common_API_Controller {

    function __construct() {
        parent::__construct();
        $this->check_module_status(8);
        $this->load->model('timezone/timezone_model');
    }

    /**
     * [get_timezone_list_post Used to get the list of time zone]
     * @return [json] [timezone list object]
     */
    function get_timezone_list_post() {
        $return = $this->return;
        $return['Data'] = $this->timezone_model->get_timezone_list();
        $this->response($return);
    }
}
