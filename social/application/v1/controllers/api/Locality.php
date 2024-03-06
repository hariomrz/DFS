<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Locality extends Common_API_Controller {

    function __construct($bypass = false) {
        parent::__construct($bypass);
        $this->load->model(array('locality/locality_model'));
    }

    /**
     * Function Name: locality list
     */
    public function index_get() {
        $return = $this->return;
        $return['Data'] = $this->locality_model->get_locality_list();
        $this->response($return);
    }
}

?>