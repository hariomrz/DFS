<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Goalserve extends MY_Controller 
{
	public $sports_id  = BASKETBALL_SPORTS_ID;
	public $league_id  = 0;
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
	 * [get_teams : This function used to get current year teams]
	 * @param  [int] $league_id 
	 */
	public function get_team()
	{
		$this->Goalserve_model->get_team($this->sports_id,$this->league_id);
	}

	/**
	 * [get_season : This function used to get current schedueled match list]
	 * @param  [int] $league_id 
	 */
	public function get_season()
	{
		$this->Goalserve_model->get_season($this->sports_id,$this->league_id);
	}

	/**
	 * [get_players : This function used to get current year players]
	 * @param  [int] $league_id 
	 */
	public function get_players()
	{
		$this->Goalserve_model->get_players($this->sports_id,$this->league_id);
	}

	/**
	 * [get_scores : This function used to get current schedueled match scores]
	 * @param  [int] $league_id 
	 */
	public function get_scores()
	{
		$this->Goalserve_model->get_scores($this->sports_id,$this->league_id);
	}

	/**
	 * [save_calculated_scores : This function used to get current match score fantasy calculation]
	 * @param  [int] $league_id 
	 */
	public function save_calculated_scores()
	{
		$this->Goalserve_model->save_calculated_scores($this->sports_id,$this->league_id);
	}

	/**
	 * [save_calculated_scores : This function used to get current match score fantasy calculation]
	 * @param  [int] $league_id 
	 */
	public function save_calculated_scores_by_match_id($season_game_uid="")
	{

		if(empty($season_game_uid))
		{
			exit("Invalid access!");
		}	

		$this->Goalserve_model->save_calculated_scores_by_match_id($this->sports_id,$this->league_id,$season_game_uid);
	}

}
