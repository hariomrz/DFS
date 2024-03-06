<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Goalserve extends MY_Controller {

	public $sports_id  = TENNIS_SPORTS_ID;
	public $league_queue = 'recent_league_cron';
    public $season_queue = 'season_cron';
    public $player_queue = 'player_cron';
    public $score_queue = 'score_cron';
    public $sc_points_queue = 'point_update_cron';
	public function __construct() 
	{
		parent::__construct();
		$this->load->model("Goalserve_model");
		$this->load->helper('queue');
		$this->server_name = get_server_host_name();
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
	 * [get_players : This function used to get current year players]
	 * @param  [int] $league_id 
	 */
	public function get_players()
	{
		$this->Goalserve_model->get_players($this->sports_id);
	}

	/**
	 * [get_season : This function used to get current schedueled match list]
	 * @param  [int] $league_id 
	 */
	public function get_season()
	{
		$this->benchmark->mark('code_start');
		$this->Goalserve_model->get_season($this->sports_id);
		$this->benchmark->mark('code_end');
		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
		exit();
	}

	/**
	 * [get_scores : This function used to get current schedueled match scores]
	 * @param  [int] $league_id 
	 */
	public function get_scores()
	{
		$this->Goalserve_model->get_scores($this->sports_id);
	}

	public function get_player_stats_scores($season_id='',$match_id='')
	{
		$this->Goalserve_model->get_player_stats_scores($this->sports_id,$season_id,$match_id);
	}

	/**
	 * [calculated_fantasy_score : This function used to get current match score fantasy calculation]
	 * @param  [int] $league_id 
	 */
	public function calculated_fantasy_score()
	{
		$this->Goalserve_model->calculated_fantasy_score($this->sports_id);
	}

	public function calculated_fantasy_score_by_match_id($league_id,$season_game_uid,$season_match_id)
    {
      if(isset($season_game_uid) && $season_game_uid != "" && isset($season_match_id) && $season_match_id != ""){
        $this->Goalserve_model->calculated_fantasy_score_by_match_id($this->sports_id,$league_id,$season_game_uid,$season_match_id);
      }
    }

    public function get_players_image()
	{
		$this->Goalserve_model->get_players_image($this->sports_id);
	}

    //Cron

    //Tennis Cron
    public function tennis_recent_league()
    {
        $content          = array();
        $content['url']   = $this->server_name."/cron/tennis/goalserve/get_recent_league";
        add_data_in_queue($content, $this->league_queue);
        echo "Recent league url added in cron queue";
        exit();
    }

    public function tennis_players()
    {
        $content         = array();
        $content['url']  = $this->server_name."/cron/tennis/goalserve/get_players";
        add_data_in_queue($content, $this->player_queue);
        echo "Players url added in cron queue";
        exit();
    }

    public function tennis_season()
    {
        $content         = array();
        $content['url']  = $this->server_name."/cron/tennis/goalserve/get_season";
        add_data_in_queue($content, $this->season_queue);
        // $this->contest_rescheduled();
        echo "Season url added in cron queue";
        exit();
    }
   

    public function tennis_scores()
    {
        $content         = array();
        $content['url']  = $this->server_name."/cron/tennis/goalserve/get_scores";
        add_data_in_queue($content, $this->score_queue);
        $this->tennis_calculate_fantasy_score();
       	//update score in lineup
        $content = array();
        $content['url'] = $this->server_name."/cron/dfs/update_lineup_score/".TENNIS_SPORTS_ID;
        add_data_in_queue($content, $this->sc_points_queue);
        echo "Score url added in cron queue";
        exit();
    }

    public function tennis_calculate_fantasy_score()
    {
        $content         = array();
        $content['url']  = $this->server_name."/cron/tennis/goalserve/calculated_fantasy_score";
        add_data_in_queue($content, $this->sc_points_queue);
         echo "fantasy calculation url added in cron queue";
        return;
    }

}
