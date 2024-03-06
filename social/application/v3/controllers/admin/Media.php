<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All Media related views rendering functions
 * @package    Users
 * @author     Ashwin kumar soni : 04-11-2014
 * @version    1.0
 */

class Media extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/media_model');
        
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
    }

    /**
     * Function for show users listing page in admin section
     * Parameters : 
     * Return : Load Users View files
     */
    public function index() {
        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('media_list'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }        
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/media/media';
        $this->page_name = "media";

        $this->load->view($this->layout, $data);
    }

    /**
     * Function for show view for media abused section
     * Parameters : 
     * Return : Load Media Abused View files
     */
    public function media_abused() {
        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('media_abusemedia'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }

        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/media/media_abused';
        $this->page_name = "media_abuse";

        $this->load->view($this->layout, $data);
    }

    /**
     * Function for show abused media details
     * Parameters : 
     * Return : Load Media Abused View files
     */
    public function media_abused_detail() {
        if ($this->uri->segment(5) == '') {
            redirect('admin/media/media_abused');
        }
        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('media_abusemedia_viewdetail'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $media_id = $this->uri->segment(5);

        $data = array();
        $data['media_id'] = $media_id;
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/media/abused_detail';
        $this->page_name = "media_abuse";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for show media analytics
     * Parameters : 
     * Return : Load Media analytics data
     */
    public function media_analytics() {        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('media_analytics'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/media/media_analytics';
        $this->page_name = "media_analytics";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for reset media counts
     * Parameters : 
     * 
     */
    public function resetmediacount(){
        $result  = $this->media_model->resetMediaCounts();
    }
    
}

//End of file media.php