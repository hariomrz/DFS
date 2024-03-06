<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Goalserve extends MY_Controller {

	public $sports_id  = MOTORSPORT_SPORTS_ID;
	public function __construct() 
	{
		parent::__construct();
		$this->load->model("Goalserve_model");
	}

	public function index()
	{
		redirect("");
	}

	/**
	 * [get_teams : This function used to get league]
	 * @param  [int] $league_id 
	 */
	public function get_recent_league()
	{
		$this->Goalserve_model->get_recent_league($this->sports_id);
	}

	/**
	 * [get_teams : This function used to get current year teams]
	 * @param  [int] $league_id 
	 */
	public function get_team()
	{
		$this->Goalserve_model->get_team($this->sports_id);
	}

	/**
	 * [get_season : This function used to get current schedueled match list]
	 * @param  [int] $league_id 
	 */
	public function get_season()
	{
		$this->Goalserve_model->get_season($this->sports_id);
	}

	/**
	 * [get_players : This function used to get current year players]
	 * @param  [int] $league_id 
	 */
	public function get_players()
	{
		$this->Goalserve_model->get_players($this->sports_id);
	}

	public function get_team_players()
	{
		$this->Goalserve_model->get_team_players($this->sports_id);
	}

	/**
	 * [get_scores : This function used to get current schedueled match scores]
	 * @param  [int] $league_id 
	 */
	public function get_scores()
	{
		$this->Goalserve_model->get_scores($this->sports_id);
	}

	public function get_old_scores()
	{
		$this->Goalserve_model->get_old_scores($this->sports_id);
	}

	/**
	 * [calculated_fantasy_score : This function used to get current match score fantasy calculation]
	 * @param  [int] $league_id 
	 */
	public function calculated_fantasy_score()
	{
		$this->Goalserve_model->calculated_fantasy_score($this->sports_id);
	}

	public function calculated_fantasy_score_by_match_id($league_id,$season_game_uid)
    {
      if(isset($season_game_uid) && $season_game_uid != ""){
        $this->Goalserve_model->calculated_fantasy_score_by_match_id($this->sports_id,$league_id,$season_game_uid);
      }
    }

}
