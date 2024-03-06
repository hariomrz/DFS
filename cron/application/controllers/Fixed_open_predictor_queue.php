<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Fixed_open_predictor_queue extends MY_Controller
{
    public $queue_name = 'cron';
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

    public function update_day_rank()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/fixed_open_predictor/update_day_rank";
        add_data_in_queue($content, $this->queue_name);
        echo "update_day_rank added in cron queue";
        exit();
    }

    public function update_week_rank()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/fixed_open_predictor/update_week_rank";
        add_data_in_queue($content, $this->queue_name);
        echo "update_week_rank added in cron queue";
        exit();
    }

    public function update_month_rank()
    {
        $content                  = array();
        $content['url']           = $this->server_name."/cron/fixed_open_predictor/update_month_rank";
        add_data_in_queue($content, $this->queue_name);
        echo "update_month_rank added in cron queue";
        exit();
    }

}