<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Cron_queue extends MY_Controller
{
    public $queue_name = 'cron';
    public $league_queue = 'recent_league_cron';
    public $team_queue = 'team_cron';
    public $season_queue = 'season_cron';
    public $player_queue = 'player_cron';
    public $score_cricket_queue = 'score_cricket';
    public $score_soccer_queue = 'score_soccer';
    public $score_baseball_queue = 'score_baseball';
    public $score_queue = 'score_cron';
    public $sc_points_queue = 'point_update_cron';
    public $contest_close_queue = 'contest_close';
    public $prize_queue = 'prize_distribution';
    public $prize_notify_queue = 'prize_notification';
    public $lineupout_game_process_queue = 'lineupout_game_process';
    public $leaderboard_queue = 'leaderboard';
    public $gst_queue = 'gst';
    public $prediction_feed_queue = 'prediction_feed';
    public $server_name;
    public $feed = array("vinfotech" => "vinfotech","entitysports" => "entitysport","cricketapi" => "cricketapi","goalserve" => "goalserve");
    private $cricket_provider;
    private $kabaddi_provider;
    private $soccer_provider;
    private $baseball_provider;
    private $basketball_provider;
    private $football_provider;
    private $ncaa_basketball_provider;
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('queue');
        $this->config->load('sports_config');
        $this->server_name = get_server_host_name();
        $this->cricket_provider = $this->config->item("cricket_feed_providers");
        $this->kabaddi_provider = $this->config->item("kabaddi_feed_providers");
        $this->soccer_provider = $this->config->item("soccer_feed_providers");
        $this->baseball_provider = $this->config->item("baseball_feed_providers");
        $this->basketball_provider = $this->config->item("basketball_feed_providers");
        $this->ncaa_basketball_provider = $this->config->item("ncaa_basketball_feed_providers");
        $this->cricket_controller = $this->feed[$this->cricket_provider];
        $this->kabaddi_controller = $this->feed[$this->kabaddi_provider];
        $this->soccer_controller  = $this->feed[$this->soccer_provider];
        $this->baseball_controller  = $this->feed[$this->baseball_provider];
        $this->basketball_controller  = $this->feed[$this->basketball_provider];
        $this->ncaa_basketball_controller  = $this->feed[$this->ncaa_basketball_provider];
        $this->football_controller = $this->config->item("nfl_feed_providers");
        
    }

    public function index($value='')
    {
        exit();
    }

    public function game_cancellation()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/dfs/game_cancellation";
        add_data_in_queue($content, $this->queue_name);
        echo "Game cancellation url added in cron queue";
        exit();
    }

    public function match_cancel_notification()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/dfs/match_cancel_notification";
        add_data_in_queue($content, $this->queue_name);
        echo "Game cancellation url added in cron queue";
        exit();
    }

    public function match_contest_pdf()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/dfs/push_live_collection_for_pdf";
        add_data_in_queue($content, $this->queue_name);
        echo "Game cancellation url added in cron queue";
        exit();
    }

    public function update_scores_in_lineup($sports_id)
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/dfs/update_lineup_score/".$sports_id;
        add_data_in_queue($content, $this->sc_points_queue);
        return;
    }

    public function update_contest_status($sports_id)
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/dfs/update_contest_status/".$sports_id;
        add_data_in_queue($content, $this->sc_points_queue);
        return;
    }

    public function contest_rescheduled()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/dfs/contest_rescheduled";
        add_data_in_queue($content, $this->season_queue);
        return;
    }

    public function prize_distribution()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/dfs/prize_distribution";
        add_data_in_queue($content, $this->prize_queue);
        echo "Prize distribution url added in cron queue";
        exit();
    }

    public function match_prize_notification()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/dfs/match_prize_distribute_notification";
        add_data_in_queue($content, $this->prize_notify_queue);
        echo "Match Prize distribution notification url added in cron queue";
        exit();
    }

    public function update_gst_report()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/dfs/push_contest_for_gst_report";
        add_data_in_queue($content, $this->gst_queue);
        exit();
    }

    public function update_leaderboard($category_id=1)
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/leaderboard/save_referral_leaderboard";
        if($category_id == "2"){
            $content['url'] = $this->server_name."/cron/leaderboard/save_fantasy_leaderboard";
        }
        add_data_in_queue($content, $this->leaderboard_queue);
        exit();
    }

    public function update_leaderboard_status($category_id=1)
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/leaderboard/update_referral_leaderboard_status";
        if($category_id == "2"){
            $content['url'] = $this->server_name."/cron/leaderboard/update_fantasy_leaderboard_status";
        }
        add_data_in_queue($content, $this->leaderboard_queue);
        exit();
    }
    

    //For cricket
    public function cricket_recent_league()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cricket/".$this->cricket_controller."/get_recent_league";
        add_data_in_queue($content, $this->league_queue);
        echo "Recent league url added in cron queue";
        exit();
    }

    public function cricket_team()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cricket/".$this->cricket_controller."/get_team";
        add_data_in_queue($content, $this->team_queue);
        echo "Season and Team url added in cron queue";
        exit();
    }

    public function cricket_season()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cricket/".$this->cricket_controller."/get_season";
        add_data_in_queue($content, $this->season_queue);
        $this->contest_rescheduled();
        echo "Season and Team url added in cron queue";
        exit();
    }

    public function cricket_players()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cricket/".$this->cricket_controller."/get_players";
        add_data_in_queue($content, $this->player_queue);
        echo "Players url added in cron queue";
        exit();
    }

    public function cricket_scores()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cricket/".$this->cricket_controller."/get_scores";
        add_data_in_queue($content, $this->score_cricket_queue);

        $this->cricket_calculate_fantasy_score();

        echo "Score url added in cron queue";
        exit();
    }

    public function cricket_calculate_fantasy_score()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/cricket/".$this->cricket_controller."/calculated_fantasy_score";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup(CRICKET_SPORTS_ID);
        return;
    }

    public function game_abandoned()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/dfs/game_abandoned";
        add_data_in_queue($content, $this->queue_name);
        exit();
    }

    // Below both are set when not use in vinfotech feed. 
    public function cricket_update_season_status()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cricket/".$this->cricket_controller."/update_season_status";
        add_data_in_queue($content, $this->season_queue);
        echo "Update season status url added in cron queue";
        exit();
    }

    public function cricket_playing_elevan()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cricket/".$this->cricket_controller."/get_playing_elevan";
        add_data_in_queue($content, $this->season_queue);
        echo "Playing eleven url added in cron queue";
        exit();
    }

    public function cricket_get_season_details($season_game_uid)
    {
        if($season_game_uid != '')
        {
            $content                  = array();
            $content['url']           = $this->server_name."/cron/cricket/".$this->cricket_controller."/get_season_details/".$season_game_uid;
            add_data_in_queue($content, $this->season_queue);
        }    
        exit();
    }


    //For kabaddi
    public function kabaddi_recent_league()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/kabaddi/".$this->kabaddi_controller."/get_recent_league";
        add_data_in_queue($content, $this->league_queue);
        echo "Recent league url added in cron queue";
        exit();
    }

    public function kabaddi_team()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/kabaddi/".$this->kabaddi_controller."/get_team";
        add_data_in_queue($content, $this->team_queue);
        exit();
    }

    public function kabaddi_season()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/kabaddi/".$this->kabaddi_controller."/get_season";
        add_data_in_queue($content, $this->season_queue);
        $this->contest_rescheduled();
        exit();
    }

    public function kabaddi_players()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/kabaddi/".$this->kabaddi_controller."/get_players";
        add_data_in_queue($content, $this->player_queue);
        exit();
    }

    public function kabaddi_scores()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/kabaddi/".$this->kabaddi_controller."/get_scores";
        add_data_in_queue($content, $this->score_queue);

        $this->kabaddi_calculate_fantasy_score();
        exit();
    }

    public function kabaddi_calculate_fantasy_score()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/kabaddi/".$this->kabaddi_controller."/calculated_fantasy_score";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup(KABADDI_SPORTS_ID);
        return;
    }

     // Below both are set when not use in vinfotech feed. 
     public function kabaddi_update_season_status()
     {
         $content                  = array();
         $content['url']           = $this->server_name."/cron/kabaddi/".$this->kabaddi_controller."/update_season_status";
         add_data_in_queue($content, $this->queue_name);
         echo "Update season status url added in cron queue";
         exit();
     }
 
     public function kabaddi_playing_seven()
     {
         $content                  = array();
         $content['url']           = $this->server_name."/cron/kabaddi/".$this->kabaddi_controller."/get_playing_seven";
         add_data_in_queue($content, $this->season_queue);
         echo "Playing eleven url added in cron queue";
         exit();
     }

    //For soccer
    public function soccer_recent_league()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/soccer/".$this->soccer_controller."/get_recent_league";
        add_data_in_queue($content, $this->league_queue);
    }

    public function soccer_team()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/soccer/".$this->soccer_controller."/get_team";
        add_data_in_queue($content, $this->team_queue);
    }

    public function soccer_season()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/soccer/".$this->soccer_controller."/get_season";
        add_data_in_queue($content, $this->season_queue);
         $this->contest_rescheduled();
        exit();
    }

    public function soccer_players()
    {    
        $content                  = array();
        $content['url']           = $this->server_name."/cron/soccer/".$this->soccer_controller."/get_players";
        add_data_in_queue($content, $this->player_queue);
        exit();
    }
    

    public function soccer_scores()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/soccer/".$this->soccer_controller."/get_scores";
        add_data_in_queue($content, $this->score_soccer_queue);
        $this->soccer_save_calculated_scores();
        exit();
    }

    public function soccer_save_calculated_scores()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/soccer/".$this->soccer_controller."/calculated_fantasy_score";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup(SOCCER_SPORTS_ID);
        return;
    }

    //Basketball Cron
    public function basketball_recent_league()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/basketball/".$this->basketball_controller."/get_recent_league";
        add_data_in_queue($content, $this->league_queue);
        echo "Recent league url added in cron queue";
        exit();
    }

    public function basketball_team()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/basketball/".$this->basketball_controller."/get_team";
        add_data_in_queue($content, $this->team_queue);
        echo "Team url added in cron queue";
        exit();
    }

    public function basketball_season()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/basketball/".$this->basketball_controller."/get_season";
        add_data_in_queue($content, $this->season_queue);
        $this->contest_rescheduled();
        echo "Season url added in cron queue";
        exit();
    }

    public function basketball_players()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/basketball/".$this->basketball_controller."/get_players";
        add_data_in_queue($content, $this->player_queue);
        echo "Players url added in cron queue";
        exit();
    }

    public function basketball_scores()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/basketball/".$this->basketball_controller."/get_scores";
        add_data_in_queue($content, $this->score_queue);
        $this->basketball_calculate_fantasy_score();
        echo "Score url added in cron queue";
        exit();
    }

    public function basketball_calculate_fantasy_score()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/basketball/".$this->basketball_controller."/calculated_fantasy_score";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup(BASKETBALL_SPORTS_ID);
        return;
    }

    //NFL - National Football League Cron
    public function nfl_recent_league()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/nfl/".$this->football_controller."/get_recent_league";
        add_data_in_queue($content, $this->league_queue);
        echo "Recent league url added in cron queue";
        exit();
    }
    public function nfl_team()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/nfl/".$this->football_controller."/get_team";
        add_data_in_queue($content, $this->team_queue);
        echo "Team url added in cron queue";
        exit();
    }

    public function nfl_season()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/nfl/".$this->football_controller."/get_season";
        add_data_in_queue($content, $this->season_queue);
         $this->contest_rescheduled();
        echo "Season url added in cron queue";
        exit();
    }

    public function nfl_players()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/nfl/".$this->football_controller."/get_players";
        add_data_in_queue($content, $this->player_queue);
        echo "Players url added in cron queue";
        exit();
    }

    public function nfl_scores()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/nfl/".$this->football_controller."/get_scores";
        add_data_in_queue($content, $this->score_queue);
        $this->nfl_calculate_fantasy_score();
        echo "Score url added in cron queue";
        exit();
    }

    public function nfl_calculate_fantasy_score()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/nfl/".$this->football_controller."/save_calculated_scores";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup(NFL_SPORTS_ID);
        return;
    }

    //FOR BASEBALL
    public function baseball_recent_league()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/baseball/".$this->baseball_controller."/get_recent_league";
        add_data_in_queue($content, $this->league_queue);
        echo "Recent league url added in cron queue";
        exit();
    }

    public function baseball_team()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/baseball/".$this->baseball_controller."/get_team";
        add_data_in_queue($content, $this->team_queue);
        echo "Season and Team url added in cron queue";
        exit();
    }

    public function baseball_season()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/baseball/".$this->baseball_controller."/get_season";
        add_data_in_queue($content, $this->season_queue);
         $this->contest_rescheduled();
        echo "Season and Team url added in cron queue";
        exit();
    }

    public function baseball_players()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/baseball/".$this->baseball_controller."/get_players";
        add_data_in_queue($content, $this->player_queue);
        echo "Players url added in cron queue";
        exit();
    }

    public function baseball_scores()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/baseball/".$this->baseball_controller."/get_scores";
        add_data_in_queue($content, $this->score_baseball_queue);
        $this->baseball_calculate_fantasy_score();
        echo "Score url added in cron queue";
        exit();
    }

    public function baseball_calculate_fantasy_score()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/baseball/".$this->baseball_controller."/calculated_fantasy_score";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup(BASEBALL_SPORTS_ID);
        return;
    }
    
    public function baseball_get_season_details($season_game_uid)
    {
        if($season_game_uid != '')
        {
            $content                  = array();
            $content['url']           = $this->server_name."/cron/baseball/".$this->baseball_controller."/get_season_details/".$season_game_uid;
            add_data_in_queue($content, $this->season_queue);
        }    
        exit();
    }


    //Motorsport- Formula 1 League Cron
    public function motorsport_recent_league()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/motorsport/goalserve/get_recent_league";
        add_data_in_queue($content, $this->league_queue);
        echo "Recent league url added in cron queue";
        exit();
    }
    public function motorsport_team()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/motorsport/goalserve/get_team";
        add_data_in_queue($content, $this->team_queue);
        echo "Team url added in cron queue";
        exit();
    }

    public function motorsport_season()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/motorsport/goalserve/get_season";
        add_data_in_queue($content, $this->season_queue);
         $this->contest_rescheduled();
        echo "Season url added in cron queue";
        exit();
    }

    public function motorsport_players()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/motorsport/goalserve/get_players";
        add_data_in_queue($content, $this->player_queue);
        echo "Players url added in cron queue";
        exit();
    }

    public function motorsport_scores()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/motorsport/goalserve/get_scores";
        add_data_in_queue($content, $this->score_queue);
        $this->motorsport_calculate_fantasy_score();
        echo "Score url added in cron queue";
        exit();
    }

    public function motorsport_calculate_fantasy_score()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/motorsport/goalserve/calculated_fantasy_score";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup(MOTORSPORT_SPORTS_ID);
        return;
    }

    


    public function prediction_feed()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/prediction_feed/pull_prediction_details";
        add_data_in_queue($content, $this->$prediction_feed_queue);
        exit();
    }

    public function auto_cancel_tournament()
    {
        $content = array();
        $content['url'] = $this->server_name."/cron/tournament/auto_cancel_tournament";
        add_data_in_queue($content, $this->queue_name);
        echo "Tournament cancellation url added in cron queue";
        exit();
    }




















    




/*********************************OLD************************** */

    

    public function prize_distribute_notification()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cron/prize_distribute_notification";
        //print_r($content);die;
        add_data_in_queue($content, $this->queue_name);
        echo "Prize distribution notification url added in cron queue";
        exit();
    }
    
    public function notify_lineup_announced_notification_cricket()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cron/notify_lineup_announced_notification/".CRICKET_SPORTS_ID;
        add_data_in_queue($content, $this->queue_name);
        exit();
    }

    public function notify_lineup_announced_notification_soccer()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cron/notify_lineup_announced_notification/".SOCCER_SPORTS_ID;
        add_data_in_queue($content, $this->queue_name);
        exit();
    }

     public function notify_lineup_announced_notification_kabaddi()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cron/notify_lineup_announced_notification/".KABADDI_SPORTS_ID;
        add_data_in_queue($content, $this->queue_name);
        exit();
    }

    // For manual

    public function manual_mark_fixture_complete($sports_id = '', $league_id='', $season_game_uid='')
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cron/manual_mark_fixture_complete/$sports_id/$league_id/$season_game_uid";
        add_data_in_queue($content, $this->queue_name);
        exit();
    }  
    public function manual_update_scores_in_lineup($sports_id = '', $league_id='', $season_game_uid='')
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cron/manual_update_scores_in_lineup/$sports_id/$league_id/$season_game_uid";
        add_data_in_queue($content, $this->queue_name);
        exit();
    }

    //NCAA - National College Football League Cron
    public function ncaa_team()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/ncaa/goalserve/get_team";
        add_data_in_queue($content, $this->team_queue);
        echo "Team url added in cron queue";
        exit();
    }

    public function ncaa_season()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/ncaa/goalserve/get_season";
        add_data_in_queue($content, $this->season_queue);
        echo "Season url added in cron queue";
        exit();
    }

    public function ncaa_players()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/ncaa/goalserve/get_players";
        add_data_in_queue($content, $this->player_queue);
        echo "Players url added in cron queue";
        exit();
    }

    public function ncaa_scores()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/ncaa/goalserve/get_scores";
        add_data_in_queue($content, $this->score_queue);
        $this->ncaa_calculate_fantasy_score();
        echo "Score url added in cron queue";
        exit();
    }

    public function ncaa_calculate_fantasy_score()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/ncaa/goalserve/save_calculated_scores";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup(NCAA_SPORTS_ID);
        return;
    }

    //CFL - Canadian Football League Cron
    public function cfl_team()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cfl/goalserve/get_team";
        add_data_in_queue($content, $this->team_queue);
        echo "Team url added in cron queue";
        exit();
    }

    public function cfl_season()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cfl/goalserve/get_season";
        add_data_in_queue($content, $this->season_queue);
        echo "Season url added in cron queue";
        exit();
    }

    public function cfl_players()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cfl/goalserve/get_players";
        add_data_in_queue($content, $this->player_queue);
        echo "Players url added in cron queue";
        exit();
    }

    public function cfl_scores()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cfl/goalserve/get_scores";
        add_data_in_queue($content, $this->score_queue);
        $this->cfl_calculate_fantasy_score();
        echo "Score url added in cron queue";
        exit();
    }

    public function cfl_calculate_fantasy_score()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/cfl/goalserve/save_calculated_scores";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup(CFL_SPORTS_ID);
        return;
    }
    
    //NCAA Baskeball - NCAA Basbetball League Cron
    public function ncaa_basketball_team()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/ncaabasketball/".$this->ncaa_basketball_controller."/get_team";
        add_data_in_queue($content, $this->team_queue);
        echo "Team url added in cron queue";
        exit();
    }

    public function ncaa_basketball_season()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/ncaabasketball/".$this->ncaa_basketball_controller."/get_season";
        add_data_in_queue($content, $this->season_queue);
        echo "Season url added in cron queue";
        exit();
    }

    public function ncaa_basketball_players()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/ncaabasketball/".$this->ncaa_basketball_controller."/get_players";
        add_data_in_queue($content, $this->player_queue);
        echo "Players url added in cron queue";
        exit();
    }

    public function ncaa_basketball_scores()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/ncaabasketball/".$this->ncaa_basketball_controller."/get_scores";
        add_data_in_queue($content, $this->score_queue);
        $this->ncaa_basketball_calculate_fantasy_score();
        echo "Score url added in cron queue";
        exit();
    }

    public function ncaa_basketball_calculate_fantasy_score()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/ncaabasketball/".$this->ncaa_basketball_controller."/save_calculated_scores";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup(NCAA_BASKETBALL_SPORTS_ID);
        return;
    }
        
    /**
     * This function used to inform about referal program to earning bonus / cash
     */
    public function send_more_referral_push() {
        $push_data = array('action' => 'more_referral');
        add_data_in_queue($push_data, 'notification');
    }

    /**
     * This function used to inform about referal program to earning bonus / cash
     */
    public function send_referral_push() {
        $push_data = array('action' => 'referral');
        add_data_in_queue($push_data, 'notification');
    }

    /**
     * This function used to inform about affiliate program 
     */
    public function send_affiliate_push() {
        $affiliate_module = isset($this->app_config['affiliate_module'])?$this->app_config['affiliate_module']['key_value']:0;
        if($affiliate_module) {
            $push_data = array('action' => 'affiliate');
            add_data_in_queue($push_data, 'notification');
        }        
    }

    /**
     * This function used to inform about today quiz
     */
    public function send_daily_quiz_push() {
        $allow_quiz = isset($this->app_config['allow_quiz'])?$this->app_config['allow_quiz']['key_value']:0;
        if($allow_quiz) {
            $push_data = array('action' => 'daily_quiz', 'is_start' => 0);
            add_data_in_queue($push_data, 'notification');
        }        
    }

    /**
     * This function used to inform about today quiz before the day ends.
     */
    public function send_daily_quiz_end_push() {
        $allow_quiz = isset($this->app_config['allow_quiz'])?$this->app_config['allow_quiz']['key_value']:0;
        if($allow_quiz) {
            $push_data = array('action' => 'daily_quiz', 'is_start' => 1);
            add_data_in_queue($push_data, 'notification');
        }        
    }

    /**
     * push function for weekly push to user regardng next level claim fwe coins more required.
     * wfmc stands for weekly few more coins
     */
    public function weekly_few_coins_more_push()
    {
        $allow_coin = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        if($allow_coin) {
            $push_data = array('action' => 'wfmc'); //wfmc stands for "weekly few more coins"
            add_data_in_queue($push_data, 'notification');
        }
    }

     /**
     * push function for claim merchandise.
     * 
     */
    public function gift_claim_push()
    {
        $allow_coin = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        if($allow_coin) {
            $push_data = array('action' => 'gift_claim');
            add_data_in_queue($push_data, 'notification');
        }
    }

    /**
     * push function for user engamement noon push.
     * 
     */
    public function user_engage_noon_push()
    {
        $push_data = array('action' => 'user_engage_noon');
        add_data_in_queue($push_data, 'notification');
    }

    /**
     * push function for user engamement push.
     * 
     */
    public function user_engage_evening_push()
    {
        // put the condition for module enable check as per the list provided by pallavi
        $push_data = array('action' => 'user_engage_evening');
        add_data_in_queue($push_data, 'notification');
    }

    /**
     * push function for spin push.
     * 
     */
    public function spin_user_engage_push()
    {
        // put the condition for module enable check as per the list provided by pallavi
        $push_data = array('action' => 'spin_user_engage');
        add_data_in_queue($push_data, 'notification');
    }

    public function user_not_played_thrice()
    {
        $push_data = array('action' => 'not_played_thrice');
        add_data_in_queue($push_data, 'notification');
    }

    public function coin_expiry_reminder()
    {
        // $this->load->model('User_bonus_cash_model','ubcm');
        // $this->ubcm->send_coins_expiry_notification();
        // die("done");
        $push_data = array('action' => 'coin_exp_reminder');
        add_data_in_queue($push_data, 'notification');

    }
    
    public function sports_predictor_live_push()
    {
    if($this->app_config['allow_prediction_system']['key_value']==1)
    {
        $push_data = array('action' => 's_predictor_live');
        add_data_in_queue($push_data, 'notification');
    }
    }
    
}