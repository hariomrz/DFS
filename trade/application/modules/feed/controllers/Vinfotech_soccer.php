<?php defined('BASEPATH') OR exit('No direct script access allowed');
set_time_limit(0);
class Vinfotech_soccer extends MY_Controller
{
    public $sports_id = SOCCER_SPORTS_ID;
    public $server_name;
    public $queue_name = "cron";
    public $league_queue = "league";
    public $team_queue = "team";
    public $season_queue = "season";
    public $player_queue = "player";
    public $score_queue = "score";

    function __construct()
    {
        parent::__construct();
        $this->load->model('vinfotech_soccer_model');
        $this->load->helper('queue');
        $this->server_name = get_server_host_name();
    }

    public function get_recent_league()
    {
       $this->vinfotech_soccer_model->get_recent_league($this->sports_id);
       $this->team();
       exit();
    }

    public function get_team($league_id='')
    {
       $this->vinfotech_soccer_model->get_team($this->sports_id,$league_id);
       exit();
    }

    public function get_season()
    {
       $this->team('1');
       $this->vinfotech_soccer_model->get_season($this->sports_id);
       $this->player();
       exit();
    }

    public function get_players()
    {
       $this->vinfotech_soccer_model->get_players($this->sports_id);
       exit();
    }

    public function get_scores()
    {
       $this->vinfotech_soccer_model->get_scores($this->sports_id);
       exit();
    }

    public function get_season_details($season_game_uid = "")
    {
      if(isset($season_game_uid) && $season_game_uid != ""){
        $this->vinfotech_soccer_model->get_season_details($season_game_uid,$this->sports_id);
      }
      exit();
    }

    public function league()
    {
        $content = array();
        $content['url'] = $this->server_name."/trade/feed/vinfotech_soccer/get_recent_league";
        add_data_in_queue($content, $this->league_queue);
        echo "Recent league url added in recent league queue";
        exit();
    }

    public function team($internal='')
    {
        $content = array();
        $content['url'] = $this->server_name."/trade/feed/vinfotech_soccer/get_team";
        add_data_in_queue($content, $this->team_queue);
        echo "Team url added in team queue";
        if($internal == '1')
        {
            return ;
        }else{
            exit();
        }  
    }

    public function season()
    {
        $content = array();
        $content['url'] = $this->server_name."/trade/feed/vinfotech_soccer/get_season";
        add_data_in_queue($content, $this->season_queue);
        echo "Season url added in season queue";
        exit();
    }

    public function player()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/trade/feed/vinfotech_soccer/get_players";
        add_data_in_queue($content, $this->player_queue);
        echo "Player url added in player queue";
        exit();
    }

    public function score()
    {
        $content = array();
        $content['url'] = $this->server_name."/props/feed/vinfotech_soccer/get_scores";
        add_data_in_queue($content, $this->score_queue);
        echo "Score url added in cricket score queue";
        exit();
    }
}