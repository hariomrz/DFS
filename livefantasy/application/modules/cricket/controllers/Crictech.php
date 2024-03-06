<?php defined('BASEPATH') OR exit('No direct script access allowed');
set_time_limit(0);
class Crictech extends MY_Controller
{
  public $sports_id = CRICKET_SPORTS_ID;
  function __construct()
    {
        parent::__construct();
        $this->load->model('cricket/crictech_model');
    }

    public function get_recent_league()
    {
       $this->crictech_model->get_recent_league($this->sports_id);
    }

    public function get_season()
    {
       $this->crictech_model->get_season($this->sports_id);
    }
    
    

}