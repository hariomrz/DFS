<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Description of Pages
 *
 * @author nitins
 */
class Pages extends MY_Controller
{

    public $page_name = 'blog';
    public $dashboard = 'blog';

    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);
        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect();
        }
        
        if($this->settings_model->isDisabled(18)){
           redirect();
           //exit();
        }
    }

    public function index()
    {
        $data = array();
        
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/pages/list';
        $this->page_name = "pages";

        $this->load->view($this->layout, $data);
    }
 
    /* this function is used to get business request list */
    public function request() {
        
        $this->data['title'] = 'Business Request';
        $this->data['content_view'] = 'admin/pages/request';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }
    
    /* this function is used to get business request list */
    public function verifyrequest() {
        
        $this->data['title'] = 'Business Verification Request';
        $this->data['content_view'] = 'admin/pages/verify_request';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }
    
    /* this function is used to get business request detail */
    public function requestdetail($RequestID ='') {
        
        if(!isset($RequestID) || $RequestID == ''){
            redirect('admin/pages/request');
        }
        $this->data['title']            = 'Request Detail';
        $this->data['communication_id'] = $RequestID;
        $this->data['content_view']     = 'admin/pages/request_detail';
        $this->data['global_settings']  = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }
    
    /* this function is used to get classified list */
    public function classifieds() {
        //Check logged in access right and allow/denied access
        /*if(!in_array(getRightsId('song_list'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }*/

        $this->data['title'] = 'Manage Classified';
        $this->data['content_view'] = 'admin/pages/classifieds';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }
    
    /* used to create page
    * Input Parameter : CategoryID
    */
    public function createclassified(){
        $this->data['title'] = 'Create Classified';
        $this->data['content_view'] = 'admin/pages/createclassified';
        $this->data['global_settings'] = $this->config->item("global_settings");
        
        $this->load->view($this->layout, $this->data);
    }
    
    /* used to edit page details using PageGuID
    * Input Parameter : PageGuID
    */
    public function editclassified($PageGUID= '') {
        
        if($PageGUID == ''){
            redirect('admin/pages/classifieds');
        }
        
        $this->data['title']            = 'Edit Classified';
        $this->data['content_view']     = 'admin/pages/createclassified';
        $this->data['global_settings']  = $this->config->item("global_settings");
        $this->data['page_guid']        = $PageGUID;
        $this->load->view($this->layout, $this->data);
    }
    
}
