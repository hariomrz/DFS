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
	* Function used for update fantasy points for tournament
	* @param void
	* @return string
	*/
 	public function update_tournament_score(){

 		$this->benchmark->mark('code_start');
 		$this->load->model('Tournament_model');
 		$this->Tournament_model->update_tournament_score();
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

	/**
	* Function used for update tournament status
	* @param void
	* @return string
	*/
   	public function update_tournament_status(){

		$this->benchmark->mark('code_start');
		$this->load->model('Tournament_model');
		$this->Tournament_model->update_tournament_status();
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
 	{
 		$this->benchmark->mark('code_start');
 		$this->load->model('Tournament_model');
 		$this->Tournament_model->process_prize_distribution();
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
 			$this->load->model('Tournament_model');
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
 		$this->load->model('Tournament_model');
 		$this->Tournament_model->prize_notification();
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

	/**
     * Used for auto cancel tournament
     * @return string
     */
    public function auto_cancel_tournament()
    {   
        $this->benchmark->mark('code_start');
		$this->load->model('Tournament_model');
        $this->Tournament_model->auto_cancel_tournament();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

	/**
	* Function used for tournament score queue process for the old data
	* @param void
	* @return string
	*/
	public function tournament_history_teams_queue(){
		
		$this->benchmark->mark('code_start');
		$this->load->model('Tournament_model');
		$this->Tournament_model->tournament_history_teams_queue();
		$this->benchmark->mark('code_end');
		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
	}

	/**
	* Function used for old tournament score update in the history team table
	* @param void
	* @return string
	*/
	public function update_tournament_history_teams($trnt_id=''){
		
		$this->benchmark->mark('code_start');
		if($trnt_id != ''){
			$this->load->model('Tournament_model');
			$this->Tournament_model->update_tournament_history_teams($trnt_id);
		}
		$this->benchmark->mark('code_end');
		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
		exit();
   	}

   	/**
     * Used for push existing tournament for tds
     * @param void
     * @return string
     */
    public function push_tournament_tds(){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1"){
        	//$this->load->model('Tournament_model');
            //$this->Tournament_model->push_tournament_tds();
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

   	/**
     * Used for process tournament tds
     * @param int $tournament_id
     * @return string
     */
    public function tds_process($tournament_id=''){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && isset($tournament_id) && $tournament_id != ""){
        	$this->load->model('Tournament_model');
            $this->Tournament_model->tds_process($tournament_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    
 }