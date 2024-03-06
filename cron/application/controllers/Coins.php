<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Coins extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $allow_coin_system = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        if(!$allow_coin_system)
        {
            exit();
        } 
    }

    public function index()
    {
        echo "Welcome";die();
    }

    public function daily_streak_notification()
    {
        $this->load->model('Coins_model');
        $this->benchmark->mark('code_start');
        $this->Coins_model->daily_streak_notification();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
    }

    public function daily_streak_sms()
    {
        $this->load->model('Coins_model');
        $this->benchmark->mark('code_start');
        $this->Coins_model->daily_streak_sms();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function coin_redeem_notification()
    {
        $this->load->model('Coins_model');
        $this->benchmark->mark('code_start');
        $this->Coins_model->coin_redeem_notification();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function coin_redeem_sms()
    {
        $this->load->model('Coins_model');
        $this->benchmark->mark('code_start');
        $this->Coins_model->coin_redeem_sms();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }
}