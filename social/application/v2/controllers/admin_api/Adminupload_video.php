<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include_once APPPATH . 'controllers/api/Upload_video.php';

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


// error_reporting(0);
class Adminupload_video extends Upload_video {

    function __construct() {
        parent::__construct(true);
        
        $this->load->model(array(    
            'admin/activity/activity_helper_model'
        ));
        $this->activity_helper_model->setUserSessionData();
    }
}