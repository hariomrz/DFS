<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Cron_queue extends MY_Controller
{
    public $server_name;
    public $team_score_queue            = "props_team_score";
    public $prize_distribution_queue    = "props_prize_distribution";
    public $prize_notification_queue    = "props_prize_notification";

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

    public function update_team_score()
    {
        $content = array();
        $content['url'] = $this->server_name."/props/cron/update_team_score";
        add_data_in_queue($content, $this->team_score_queue);
        echo "Lineup score update url added in cron queue";
        exit();
    }

    public function prize_distribution()
    {
        $content = array();
        $content['url'] = $this->server_name."/props/cron/prize_distribution";
        add_data_in_queue($content, $this->prize_distribution_queue);
        echo "Prize distribution url added in cron queue";
        exit();
    }

    public function prize_notification()
    {
        $content = array();
        $content['url'] = $this->server_name."/props/cron/prize_notification";
        add_data_in_queue($content, $this->prize_notification_queue);
        echo "Prize notification url added in cron queue";
        exit();
    }
}