<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Archival controller to execute archival job */

class Archival extends Common_Controller {

    public function __construct() {
        parent::__construct();       
    }

    function index() {
    }
        
    function run() {
        $page_no = $this->uri->segment(3);        
        if (empty($page_no)) {
            $page_no = 1;
        }
        
        $this->benchmark->mark('execution_start');
        $this->load->model(array('archival/archival_model'));
        $this->archival_model->run($page_no);
        $this->benchmark->mark('execution_ends');
        log_message('error', "Archival Execution Time  => ".$this->benchmark->elapsed_time('execution_start', 'execution_ends'));
    } 

    function synch_user() {    
        $this->benchmark->mark('execution_start');    
        $this->load->model(array('archival/archival_model'));
        $this->archival_model->synch_user();     
        $this->benchmark->mark('execution_ends');
        log_message('error', "User Synch Execution Time  => ".$this->benchmark->elapsed_time('execution_start', 'execution_ends'));   
    }

    function synch_album() {    
        $this->benchmark->mark('execution_start');    
        $this->load->model(array('archival/archival_model'));
        $this->archival_model->synch_album();     
        $this->benchmark->mark('execution_ends');
        log_message('error', "Album Synch Execution Time  => ".$this->benchmark->elapsed_time('execution_start', 'execution_ends'));   
    }

    function synch_tag() {    
        $this->benchmark->mark('execution_start');    
        $this->load->model(array('archival/archival_model'));
        $this->archival_model->synch_tag();     
        $this->benchmark->mark('execution_ends');
        log_message('error', "Tag Synch Execution Time  => ".$this->benchmark->elapsed_time('execution_start', 'execution_ends'));   
    }

    function synch_tag_category() {    
        $this->benchmark->mark('execution_start');    
        $this->load->model(array('archival/archival_model'));
        $this->archival_model->synch_tag_category();     
        $this->benchmark->mark('execution_ends');
        log_message('error', "Tag Category Synch Execution Time  => ".$this->benchmark->elapsed_time('execution_start', 'execution_ends'));   
    }
    
}
