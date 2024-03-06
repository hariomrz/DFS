<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Cron_queue extends MY_Controller
{
    public $queue_name = 'picks_cron';
    public $game_cancel_queue = 'picks_game_cancel';
    public $score_queue   = 'picks_score_update_cron';
    public $contest_close_queue   = 'picks_contest_close';
    public $prize_queue = 'picks_prize_distribution';
    public $prize_notify_queue = 'picks_prize_notification';
    public $server_name;
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('queue');
        $this->server_name = get_server_host_name();
    }

    public function index($value='')
    {
        exit();
    }

    public function game_cancellation()
    {
        $content                  = array();
        $content['action']           = 'game_cancellation';
        add_data_in_queue($content, $this->game_cancel_queue);
        echo "Game cancellation url added in cron queue";
        exit();
    }

    public function update_scores_in_lineup()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/picks/cron/update_scores_in_picks_by_season";
        add_data_in_queue($content, $this->score_queue);
        echo "Score Calculation url added in cron queue";
        exit();
    }

    public function update_contest_status()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/picks/cron/update_contest_status";
        add_data_in_queue($content, $this->contest_close_queue);
        exit();
    }

    public function prize_distribution()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/picks/cron/prize_distribution";
        add_data_in_queue($content, $this->prize_queue);
        echo "Prize distribution url added in cron queue";
        exit();
    }

    public function prize_distribution_notification()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/picks/cron/match_prize_distribute_notification";
        add_data_in_queue($content, $this->prize_notify_queue);
        echo "Prize distribution notification url added in cron queue";
        exit();
    }

}