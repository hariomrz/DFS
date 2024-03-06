<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All IPs setting related views rendering functions
 * @package    IPs
 * @author     Girish Patidar : 04-01-2015
 * @version    1.0
 */

class Ips extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
    }

    /**
     * Function for show IP Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index() {
        $this->show_date_filter = false;
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('ips_admin'), getUserRightsData($this->DeviceType)) && !in_array(getRightsId('ips_user'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/ips/iplist';
        $this->page_name = "ips";

        $this->load->view($this->layout, $data);
    }

}
//End of file ips.php
