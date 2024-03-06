<?php

defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH . 'controllers/api/Favourite.php';

/**
 * This Class used as REST API for Favourit module
 * @category	Controller
 * @author		Vinfotech Team
 */
class Adminfavourite extends Favourite {

    /**
     * @Summary: call parent constructor
     * @access: public
     * @param:
     * @return:
     */
    function __construct() {
        parent::__construct(true);
        
        $this->load->model(array(
            'admin/activity/activity_helper_model'
        ));

        $this->activity_helper_model->setUserSessionData();
    }

}
