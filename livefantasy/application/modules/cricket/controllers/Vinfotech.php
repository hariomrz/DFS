<?php defined('BASEPATH') OR exit('No direct script access allowed');
set_time_limit(0);
class Vinfotech extends MY_Controller
{
  public $sports_id = CRICKET_SPORTS_ID;
  function __construct()
    {
        parent::__construct();
        $this->load->model('cricket/vinfotech_model');
    }

    public function get_recent_league()
    {
       $this->vinfotech_model->get_recent_league($this->sports_id);
    }

    public function get_season()
    {
       $this->vinfotech_model->get_season($this->sports_id);
    }
    
    public function get_team()
    {
       $this->vinfotech_model->get_team($this->sports_id);
    }

    public function get_player()
    {
       $this->vinfotech_model->get_player($this->sports_id);
    }

    public function get_scores()
    {
       $this->vinfotech_model->get_scores($this->sports_id);
    }

}