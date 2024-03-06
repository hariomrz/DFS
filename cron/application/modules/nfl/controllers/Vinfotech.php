<?php defined('BASEPATH') OR exit('No direct script access allowed');
set_time_limit(0);
class vinfotech extends MY_Controller
{
    public $sports_id = NFL_SPORTS_ID;
            
    function __construct()
    {
        parent::__construct();
        $this->load->model('vinfotech_model');
    }

    public function get_recent_league()
    {
       $this->vinfotech_model->get_recent_league($this->sports_id);
       $this->team();
       exit();
    }

    /**
     * [get_teams : This function used to get current year teams]
     * @param  [int] $league_id 
     */
    public function get_team($league_id="")
    {
        $this->vinfotech_model->get_team($this->sports_id,$league_id);
    }

    /**
     * [get_season : This function used to get current schedueled match list]
     * @param  [int] $league_id 
     */
    public function get_season()
    {
        $this->vinfotech_model->get_season($this->sports_id);
    }

    /**
     * [get_players : This function used to get current year players]
     * @param  [int] $league_id 
     */
    public function get_players()
    {
        $this->vinfotech_model->get_players($this->sports_id);
    }

    /**
     * [get_scores : This function used to get current schedueled match scores]
     * @param  [int] $league_id 
     */
    public function get_scores()
    {
        $this->vinfotech_model->get_scores($this->sports_id);
    }

    /**
     * [save_calculated_scores : This function used to get current match score fantasy calculation]
     * @param  [int] $league_id 
     */
    public function save_calculated_scores()
    {
        $this->vinfotech_model->save_calculated_scores($this->sports_id);
    }

    public function save_calculated_scores_by_match_id($league_id,$season_game_uid)
    {
      if(isset($season_game_uid) && $season_game_uid != ""){
        $this->vinfotech_model->save_calculated_scores_by_match_id($this->sports_id,$league_id,$season_game_uid);
      }
    }

}