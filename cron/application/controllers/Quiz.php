<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Quiz extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $allow_quiz = isset($this->app_config['allow_quiz'])?$this->app_config['allow_quiz']['key_value']:0;
        if(!$allow_quiz)
        {
            exit();
        } 
    }

    function update_dashboard_rank()
    {
        $this->load->model('Quiz_model');
        $this->benchmark->mark('code_start');
        $this->Quiz_model->update_dashboard_rank();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }
}