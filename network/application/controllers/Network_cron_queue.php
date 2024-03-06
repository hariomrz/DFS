<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Network_cron_queue extends CI_Controller
{
    public $queue_name = NETWORK_COMMON_QUEUE;
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

    public function update_nw_contest_status()
    {
        $content        = array();
        $content['url'] = $this->server_name."/network/network_cron/update_nw_contest_status";
        add_data_in_queue($content, $this->queue_name);
        exit("Cron has been added to queue.");
    }

    public function nw_contest_cancellation()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/network/network_cron/nw_contest_cancellation";
        add_data_in_queue($content, $this->queue_name);
        exit("Network game cancellation url added in cron queue");
    }

    

    public function nw_contest_prize_distribution()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/network/network_cron/nw_contest_prize_distribution";
        add_data_in_queue($content, $this->queue_name);
        exit("Prize distribution url added in cron queue");
    }

   

    public function nw_contest_notification()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/network/network_cron/nw_contest_notification";
        add_data_in_queue($content, $this->queue_name);
        exit("Contest notification url added in cron queue");
    }

}