<?php

/**
 * Description of Dashboard
 *
 * @author tusharg
 */
class Dashboard extends MY_Controller
{

    public $page_name = "";

    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->show_date_filter = false;
        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect('admin');
        }
    }

    public function index()
    {
        //Check logged in access right and allow/denied access
        /*if (!in_array(getRightsId('category_admin'), getUserRightsData($this->DeviceType)))
        {
            redirect('access_denied');
        }*/

        $data = array();
        $data['global_settings']    = $this->config->item("global_settings");

        /* View File */
        $data['content_view']       = 'admin/dashboard/landing_page';
        $this->page_name            = "dashboard";

        $this->load->view($this->layout, $data);
    }

    public function detail()
    {
        //Check logged in access right and allow/denied access
        /*if (!in_array(getRightsId('category_admin'), getUserRightsData($this->DeviceType)))
        {
            redirect('access_denied');
        }*/

        $data = array();
        $data['global_settings']    = $this->config->item("global_settings");

        /* View File */
        $data['content_view']       = 'admin/dashboard/dashboard_detail';
        $this->page_name            = "dashboard_detail";
//        echo '<pre>';
//        print_r($data);
//        echo '</pre>';  die;
        $this->load->view($this->layout, $data);
    }
    
}
