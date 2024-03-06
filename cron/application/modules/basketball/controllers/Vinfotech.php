<?php defined('BASEPATH') OR exit('No direct script access allowed');
set_time_limit(0);
class Vinfotech extends MY_Controller
{
  public $sports_id = BASKETBALL_SPORTS_ID;
  function __construct()
    {
        parent::__construct();
        $this->check_url_hit();
        $this->load->model('basketball/vinfotech_model');
    }

    public function get_recent_league()
    {
      $this->vinfotech_model->get_recent_league($this->sports_id);
    }

    public function get_team($league_id='')
    {
       $this->vinfotech_model->get_team($this->sports_id,$league_id);
    }

    public function get_season()
    {
       $this->vinfotech_model->get_season($this->sports_id);
    }

    public function get_players()
    {
       $this->vinfotech_model->get_players($this->sports_id);
    }

    public function get_scores()
    {
       $this->vinfotech_model->get_scores($this->sports_id);
    }

    public function calculated_fantasy_score()
    {
       $this->vinfotech_model->calculated_fantasy_score($this->sports_id);
    }

    public function get_season_details($season_game_uid = "")
    {
      if(isset($season_game_uid) && $season_game_uid != ""){
        $this->vinfotech_model->get_season_details($season_game_uid,$this->sports_id);
      }
    }

    public function calculated_fantasy_score_by_match_id($league_id,$season_game_uid)
    {
      if(isset($season_game_uid) && $season_game_uid != ""){
        $this->vinfotech_model->calculated_fantasy_score_by_match_id($this->sports_id,$league_id,$season_game_uid);
      }
    }


}