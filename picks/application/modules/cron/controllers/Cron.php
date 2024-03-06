<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
    * Function used for send cancel notification
    * @param
    * @return string
    */
    public function match_cancel_notification() {
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
            $this->Cron_model->match_cancel_notification();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }
  

   /**
    * Update Picks Score 
    * after admin successfullly mark question answer
    * @param void
    * @return string
    */
    public function update_scores_in_picks_by_season()
	{
		$this->benchmark->mark('code_start');

		$this->load->model('Cron_model');
		$this->Cron_model->update_scores_in_picks_by_season();
		$this->benchmark->mark('code_end');

		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
		exit();
   }

   /**
    * Used for update match contest status
    * @param int 
    * @return string print output
    */
    public function update_contest_status()
    {   
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->update_contest_status();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');   
        exit();
    }

    /**
    * Function used for contest multi currency prize distribution
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
    public function contest_prize_distribution($contest_id=''){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && isset($contest_id) && $contest_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->contest_prize_distribution($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
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
    * Removed Unpulished Fixture through Cron
    * @param void
    * @return string
    */
    public function remove_unpublished_fixture()
    {
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
        $this->Cron_model->remove_unpublished_fixture();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
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

    /**
    * Function used for fetch list of invoices which have not been sent to users.
    * @param 
    * @return boolean
    */
    public function process_tax_invoices()
    {
        $allow_gst = isset($this->app_config['allow_gst']) ? $this->app_config['allow_gst']['key_value'] : "0";

        $this->benchmark->mark('code_start');
        if($allow_gst == "1" && !$this->app_config['int_version']['key_value'])
        {
            $this->load->model('Cron_model');
            $this->Cron_model->process_tax_invoices();
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for generate tax invoice for a particular invoice and send to respective customer
     * @param int $invoice_id
     * @return string
     */
    public function generate_tax_invoice($invoice_id='')
    {
        $allow_gst = isset($this->app_config['allow_gst']) ? $this->app_config['allow_gst']['key_value'] : "0";

        $this->benchmark->mark('code_start');
        if($allow_gst == "1" && isset($invoice_id) && $invoice_id != "" && !$this->app_config['int_version']['key_value'])
        {
            $this->load->model('Cron_model');
            $this->Cron_model->generate_tax_invoice($invoice_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }  
}
 