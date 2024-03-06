<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Cron_queue extends MY_Controller
{
    public $sc_points_queue = 'stock_point_update_cron';
    public $contest_close_queue = 'stock_contest_close';
    public $prize_queue = 'stock_prize_distribution';
    public $prize_notify_queue = 'stock_prize_notification';
    public $game_cancel_queue = 'stock_game_cancel';
    public $leaderboard_queue = 'stock_leaderboard';
    public $sc_feed_queue = 'stock_feed';
    public $lsf_feed_queue = 'live_stock_fantasy_update_cron';
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('queue');
        $this->server_name = get_server_host_name(); 
    }
    public function game_cancellation()
    {
        $content                  = array();
        $content['action']           = "game_cancellation";
        add_data_in_queue($content, $this->game_cancel_queue);
        echo "Game cancellation added in cron queue";
        exit();
    }

    public function prize_distribution()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/stock/cron/cron/prize_distribution";
        add_data_in_queue($content, $this->prize_queue);
        echo "Prize distribution url added in cron queue";
        exit();
    }

    public function max_user_join_revert()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/stock/cron/cron/max_user_join_revert";
        add_data_in_queue($content, $this->queue_name);
        exit();
    }

    public function update_scores_in_lineup()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/stock/cron/cron/update_scores_in_lineup_by_collection";
        add_data_in_queue($content, $this->sc_points_queue);

        $this->update_scores_in_lineup_predict();
        return;
        //exit();
    }

    public function update_scores_in_lineup_predict()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/stock/cron/stock_predict_cron/update_scores_in_portfolio_by_collection";
        add_data_in_queue($content, $this->sc_points_queue);
        return;
        //exit();
    }

    public function update_contest_status()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/stock/cron/cron/update_contest_status";
        add_data_in_queue($content, $this->contest_close_queue);
        exit();
    }

    public function collection_prize_distribute_notification()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/stock/cron/cron/collection_prize_distribute_notification";
        add_data_in_queue($content, $this->prize_notify_queue);
        echo "Collection Prize distribution notification url added in cron queue";
        exit();
    }


    public function update_leaderboard() {
        $content = array();
        $content['action']           = "save_stock_leaderboard";       
        add_data_in_queue($content, $this->leaderboard_queue);
        exit();
    }

    public function update_leaderboard_status() {
        $content = array();
        $content['action']           = "update_stock_leaderboard";
        add_data_in_queue($content, $this->leaderboard_queue);
        exit();
    }

    public function update_predict_collection_stock_rates($type=1) {
        $content                  = array();
        $content['action']          = 'update_collection_stock_rates';
        $content['url']           = $this->server_name."/stock/cron/stock_predict_cron/update_collection_stock_rates/".$type;
        add_data_in_queue($content, $this->sc_feed_queue);
        return;
    }

    public function update_live_stock_fantasy_trade_value($type=1)
    {
        $content                  = array();
        $content['url']           = $this->server_name."/stock/cron/live_stock_fantasy_cron/update_trade_value/".$type;
        add_data_in_queue($content, $this->lsf_feed_queue);
        return;
        //exit();
    }

    public function update_rank_in_live_stock_fantasy()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/stock/cron/live_stock_fantasy_cron/update_rank_in_portfolio_by_collection";
        add_data_in_queue($content, $this->lsf_feed_queue);
        return;
        //exit();
    }


}