<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All configuration setting related views rendering functions
 * @package    Users
 * @author     Girish PAtidar : 07-02-2015
 * @version    1.0
 */

class Configuration extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/configuration_model');
        
    }

    /**
     * Function for show configuration setting Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index() {
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/configuration/list';
        $this->page_name = "configuration";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for change website language
     */
    public function changelanguage(){
        $dataArr = $this->input->post();
        $languageArr = getLanguageList();
        
        if(isset($dataArr['languages']) && $dataArr['languages'] != "" && $languageArr[$dataArr['languages']]){
            set_cookie(array('name'=>'language','value'=>$languageArr[$dataArr['languages']],'expire'=>'86500','prefix' => 'site_'));
        }else{
            set_cookie(array('name'=>'language','value'=>'english','expire'=>'86500','prefix' => 'site_'));
        }
        
        redirect($dataArr['returnUrl']);
    }
    
    /**
     * Function for show all language and language code linting
     */
    public function cultureinfo(){
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        /* View File */
        $data['content_view'] = 'admin/configuration/cultureinfo';
        $this->page_name = "cultureinfo";

        $this->load->view($this->layout, $data);
    }

}

//End of file configuration.php