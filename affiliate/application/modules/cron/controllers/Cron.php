<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Cron extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo "Welcome";die();
    }

    /**
     * method to update status of expired cron
     */
    public function update_expired_campaign()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->update_expired_campaign();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit;
    }
}
 