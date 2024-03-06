<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Cron_queue extends MY_Controller
{
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
}