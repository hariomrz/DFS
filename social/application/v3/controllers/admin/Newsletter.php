<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All User related views rendering functions
 * @package    Users
 * @author     Ashwin kumar soni : 01-10-2014
 * @version    1.0
 */

class Newsletter extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/users_model');
            
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
        
        if ($this->settings_model->isDisabled(35)) {
            redirect('/admin');
        }
        
        $this->show_date_filter = false;
    }

    /**
     * Function for show subscribers Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index() {
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/newsletter/users';
        $this->page_name = "newsletter_users";
        $this->load->view($this->layout, $data);
    }
        
    public function group_list() {
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/newsletter/group_list';
        $this->page_name = "newsletter_users";
        $this->load->view($this->layout, $data);
    }
    
    public function download_user_format() {
        $fileName = 'SubscriberDataSampleFormat.xls';
        //echo $result->MediaType;
        $url = base_url() . 'upload/csv_file/' . $fileName;
        set_time_limit(0);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $r = curl_exec($ch);
        curl_close($ch);
        $this->load->helper('download'); //load helper
        force_download($fileName, $r);
    }
    
    
    public function downloadsubscribers(){     
        
        $file_url = base_url().'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/Subscribers_list.xls";

        header('Content-Type: application/octet-stream');

        header("Content-Transfer-Encoding: Binary");

        header("Content-disposition: attachment; filename=\"".basename($file_url)."\"");

        readfile($file_url);
    }
    
}

//End of file users.php
