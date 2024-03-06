<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Cron_queue extends MY_Controller
{
    public $server_name;
    public $score_queue = 'pickem_score';
    public $prize_queue = 'pickem_prize_distribution';
    public $prize_notify_queue = 'pickem_prize_notification';
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

    public function pickem_score()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/pickem/cron/tournament/update_tournament_score";
        add_data_in_queue($content, $this->score_queue);
        echo "Pickem Score url added in cron queue";
        exit();
    }

    public function prize_distribution()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/pickem/cron/tournament/process_prize_distribution";
        add_data_in_queue($content, $this->prize_queue);
        echo "Prize distribution url added in cron queue";
        exit();
    }

    public function prize_notification()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/pickem/cron/tournament/prize_notification";
        add_data_in_queue($content, $this->prize_notify_queue);
        echo "Prize distribution url added in cron queue";
        exit();
    }

    public function auto_cancel_tournament()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/pickem/cron/tournament/auto_cancel_tournament";
        add_data_in_queue($content, $this->prize_notify_queue);
        echo "Auto cancel tournament url added in cron queue";
        exit();
    }
}