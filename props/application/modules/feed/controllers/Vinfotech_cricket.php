<?php defined('BASEPATH') OR exit('No direct script access allowed');
set_time_limit(0);
class Vinfotech_cricket extends MY_Controller
{
    public $sports_id = CRICKET_SPORTS_ID;
    public $server_name;
    public $queue_name = "cron";
    public $league_queue = "props_league";
    public $team_queue = "props_team";
    public $season_queue = "props_season";
    public $player_queue = "props_player";
    public $score_queue = "props_score";
    public $lineup_queue = "props_lineup";

    function __construct()
    {
        parent::__construct();
        $this->load->model('vinfotech_cricket_model');
        $this->load->helper('queue');
        $this->server_name = get_server_host_name();
    }

    public function get_recent_league($hd='')
    {
       $this->vinfotech_cricket_model->get_recent_league($this->sports_id,$hd);
       $this->team();
       exit();
    }

    public function get_team($hd='',$league_id='')
    {
       $this->vinfotech_cricket_model->get_team($this->sports_id,$hd,$league_id);
       exit();
    }

    public function get_season($hd='')
    {
       $this->team('1');
       $this->vinfotech_cricket_model->get_season($this->sports_id,$hd);
       $this->player();
       exit();
    }

    public function get_players($hd='')
    {
       $this->vinfotech_cricket_model->get_players($this->sports_id,$hd);
       exit();
    }

    public function get_scores()
    {
       $this->vinfotech_cricket_model->get_scores($this->sports_id);
       exit();
    }

    public function get_season_details($season_game_uid = "")
    {
      if(isset($season_game_uid) && $season_game_uid != ""){
        $this->vinfotech_cricket_model->get_season_details($season_game_uid,$this->sports_id);
      }
      exit();
    }

    public function save_props($season_game_uid)
    {
      if(isset($season_game_uid) && $season_game_uid != ""){
        $this->vinfotech_cricket_model->save_props($this->sports_id,$season_game_uid);
      }  
      exit();
    }

    public function update_lineup()
    {
      $this->vinfotech_cricket_model->update_lineup($this->sports_id);
      exit();
    }

    public function league($hd='')
    {
        $content = array();
        $content['url'] = $this->server_name."/props/feed/vinfotech_cricket/get_recent_league/".$hd;
        add_data_in_queue($content, $this->league_queue);
        echo "Recent league url added in recent league queue";
        exit();
    }

    public function team($internal='',$hd='')
    {
        $content = array();
        $content['url'] = $this->server_name."/props/feed/vinfotech_cricket/get_team/".$hd;
        add_data_in_queue($content, $this->team_queue);
        echo "Team url added in team queue";
        if($internal == '1')
        {
            return ;
        }else{
            exit();
        }  
    }

    public function season($hd='')
    {
        $content = array();
        $content['url'] = $this->server_name."/props/feed/vinfotech_cricket/get_season/".$hd;
        add_data_in_queue($content, $this->season_queue);
        echo "Season url added in season queue";
        exit();
    }

    public function player($hd='')
    {
        $content                  = array();
        $content['url']           = $this->server_name."/props/feed/vinfotech_cricket/get_players/".$hd;
        add_data_in_queue($content, $this->player_queue);
        echo "Player url added in player queue";
        exit();
    }

    public function score()
    {
        $content = array();
        $content['url'] = $this->server_name."/props/feed/vinfotech_cricket/get_scores";
        add_data_in_queue($content, $this->score_queue);
        echo "Score url added in cricket score queue";
        exit();
    }

    public function lineup()
    {
        $content = array();
        $content['url'] = $this->server_name."/props/feed/vinfotech_cricket/update_lineup";
        add_data_in_queue($content, $this->lineup_queue);
        echo "Update lineup url added in lineup_queue queue";
        exit();
    }
}