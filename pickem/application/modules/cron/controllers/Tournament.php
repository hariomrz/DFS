<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Tournament extends MY_Controller
{

 	public function __construct(){
 		parent::__construct();
 	}

 	public function index()
    {
        echo "Welcome";die();
    }

    /**
    * Function used for auto match addition
    * @param void
    * @return string
    */
    public function auto_match_addition(){

        $this->benchmark->mark('code_start');
        $this->load->model('cron/Tournament_model');
        $this->Tournament_model->auto_match_addition();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

 	/**
	* Function used for update fantasy points for tournament
	* @param void
	* @return string
	*/
 	public function update_tournament_score($season_id=''){

 		$this->benchmark->mark('code_start');
 		$this->load->model('cron/Tournament_model');
		if(!empty($season_id)){
			$this->Tournament_model->update_user_team_score($season_id);
		}else{
			$this->Tournament_model->update_tournament_score();
		}
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}


 	/**
	* Function used for push prize distribution request into queue
	* @param void
	* @return string
	*/
 	public function process_prize_distribution()
 	{	$prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
	 	$this->benchmark->mark('code_start');
 		if($prize_cron == "1"){
	 		$this->load->model('cron/Tournament_model');
	 		$this->Tournament_model->process_prize_distribution();
	 	}
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

 	/**
	* Function used for tournament prize distribution
	* @param int $tournament_id
	* @return string
	*/
 	public function prize_distribution($tournament_id)
 	{
 		$this->benchmark->mark('code_start');
 		if($tournament_id != ""){
 			$this->load->model('cron/Tournament_model');
 			$this->Tournament_model->prize_distribution($tournament_id);
 		}
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

 	/**
	* Function used for sent winning notification to users
	* @param void
	* @return string
	*/
 	public function prize_notification()
 	{
 		$this->benchmark->mark('code_start');
 		$this->load->model('cron/Tournament_model');
 		$this->Tournament_model->prize_notification();
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

 	/**
     * Used for deduct tds
     * @param int $tournament_id
     * @return string
     */
    public function deduct_tds_from_winning($tournament_id = ""){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && $tournament_id != ""){
            $this->load->model('cron/Tournament_model');
            $this->Tournament_model->deduct_tds_from_winning($tournament_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
    }

    /**
     * Used for contest reschedule
     * @param void
     * @return string
     */
    public function perfect_score_distribution($tournament_id= "")
    {
        $this->benchmark->mark('code_start');
        if($tournament_id != ""){
            $this->load->model('cron/Tournament_model');
            $this->Tournament_model->perfect_score_distribution($tournament_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
       exit(); 
    }

    /**
     * Used for contest reschedule
     * @param void
     * @return string
     */
    public function tournament_rescheduled()
    {
         $this->load->model('cron/Tournament_model');
        $this->benchmark->mark('code_start');
        $this->Tournament_model->tournament_rescheduled();
        $this->benchmark->mark('code_end');
       echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
       exit(); 
    }

	/**
     * Used for contest reschedule
     * @param void
     * @return string
     */
    public function auto_cancel_tournament()
    {
        $this->load->model('cron/Tournament_model');
        $this->benchmark->mark('code_start');
        $this->Tournament_model->auto_cancel_tournament();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit(); 
    }
 }