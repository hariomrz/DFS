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
     * [game_cancellation description]
     * @Summary :- This function will cancell the games which is not full till drafting.
     * @return  [type]
     */
    public function game_cancellation()
    {   
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
        $this->Cron_model->game_cancellation();
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for update score in user prediction table
     * @param int $sports_id
     * @return string
     */
    public function update_scores_by_collection($sports_id = '')
    {
        if (!empty($sports_id))
        {
            $this->load->model('Cron_model');
            $this->benchmark->mark('code_start');
            $this->Cron_model->update_scores_by_collection($sports_id);
            $this->benchmark->mark('code_end');
            echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        }
        exit();
    }

    /**
     * Used for update match contest status
     * @param int $sports_id
     * @return string print output
     */
    public function update_contest_status($sports_id='')
    {
        if (!empty($sports_id))
        {
            $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
            $this->benchmark->mark('code_start');
            if($prize_cron == "1"){
                $this->load->model('Cron_model');
                $this->Cron_model->update_contest_status($sports_id);
            }
            $this->benchmark->mark('code_end');
            echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        }    
        exit();
    }

    /**
    * Function used for contest prize distribution data push
    * @param
    * @return string
    */
    public function prize_distribution($type='0')
    {   
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1"){
            $this->load->model('Cron_model');
            $this->Cron_model->prize_distribution($type);
        }
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for match prize distribution
     * @param int $contest_id
     * @return string
     */
    public function match_prize_distribution($collection_id='',$group_id=''){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && isset($collection_id) && $collection_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->match_prize_distribution($collection_id,$group_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * Function used for contest prize distribution data push
    * @param
    * @return string
    */
    public function merchandise_prize_distribution()
    {   
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1"){
            $this->load->model('Cron_model');
            $this->Cron_model->merchandise_prize_distribution();
        }
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * Function used for contest merchandise prize distribution
    * @param
    * @return string
    */
    public function contest_merchandise_distribution($contest_id='')
    {   
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && isset($contest_id) && $contest_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->contest_merchandise_distribution($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for deduct tds
     * @param int $contest_id
     * @return string
     */
    public function deduct_tds_from_winning($contest_id = ""){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && $contest_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->deduct_tds_from_winning($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
    }

    /**
     * Used for deduct tds
     * @param int $contest_id
     * @return string
     */
    public function add_cash_contest_referral_bonus($contest_id = ""){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && $contest_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->add_cash_contest_referral_bonus($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
    }

    /**
     * Used for deduct tds
     * @param int $contest_id
     * @return string
     */
    public function add_every_cash_contest_referral_benefits($contest_id = ""){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && $contest_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->add_every_cash_contest_referral_benefits($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
    }
    /**
    * Function used for send winning notification
    * @param
    * @return string
    */
    public function match_prize_distribute_notification()
    {
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
            $this->Cron_model->match_prize_distribute_notification();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for push contest for gst report
     * @param
     * @return string
     */
    public function push_contest_for_gst_report(){
        $this->benchmark->mark('code_start');
        $allow_gst = isset($this->app_config['allow_gst']) ? $this->app_config['allow_gst']['key_value'] : "0";
        if($allow_gst == "1"){
            $this->load->model('Cron_model');
            $this->Cron_model->push_contest_for_gst_report();
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for generate gst report
     * @param int $contest_id
     * @return string
     */
    public function generate_gst_report($contest_id=''){
        $this->benchmark->mark('code_start');
        $allow_gst = isset($this->app_config['allow_gst']) ? $this->app_config['allow_gst']['key_value'] : "0";
        if($allow_gst == "1" && isset($contest_id) && $contest_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->generate_gst_report($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

}
 