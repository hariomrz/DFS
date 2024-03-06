<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
include_once APPPATH . 'controllers/api/Subscribe.php';

class Adminsubscribe extends Subscribe 
{

    function __construct() 
    {
        parent::__construct(true);
        
        $this->load->model(array(
            'admin/activity/activity_helper_model'
        ));
        
        $this->activity_helper_model->setUserSessionData();
    }
}
?>