<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    signup
 
 * @version    1.0
 */

class Dashboard extends Common_Controller {

    public $page_name = 'dashboard';
    public $dashboard = '';

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function __construct() {
        parent::__construct();
    }

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function index() {
        $this->data['title'] = 'Profile';
        $this->page_name = 'dashboard';
        $this->dashboard = '';
        $this->data['content_view'] = 'dashboard';
        $this->load->view($this->layout, $this->data);
    }

}
