 <?php if(!defined('BASEPATH')) exit('No direct script access allowed');
 set_time_limit(0);

 class Leaderboard extends MY_Controller{

 	public $referral_category = REFERRAL_LEADERBOARD_ID;
 	public $fantasy_category = FANTASY_LEADERBOARD_ID;
	public $stock_category = STOCK_LEADERBOARD_ID;
	public $stock_equity_category = STOCK_EQUITY_LEADERBOARD_ID;
	public $stock_predict_category = STOCK_PREDICT_LEADERBOARD_ID;
	public $live_stock_fantasy_category = LIVE_STOCK_FANTASY_LEADERBOARD_ID;
 	public $type_daily = 1;
 	public $type_weekly = 2;
 	public $type_month = 3;
 	public $type_league = 4;
 	public function __construct(){
 		parent::__construct();
 	}

 	public function index(){
 		echo "Welcome"; die(); 
 	}

 	/**
	* Function used for update referral count for leaderboard
	* @param void
	* @return string
	*/
 	public function save_referral_leaderboard(){

 		$this->benchmark->mark('code_start');
 		$this->load->model('Leaderboard_model');
 		$this->Leaderboard_model->save_referral_leaderboard($this->referral_category);
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

 	/**
	* Function used for update referral leaderboard status
	* @param void
	* @return string
	*/
 	public function update_referral_leaderboard_status(){

 		$this->benchmark->mark('code_start');
 		$this->load->model('Leaderboard_model');
 		$this->Leaderboard_model->update_referral_leaderboard_status($this->referral_category);
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

 	/**
	* Function used for update fantasy points for leaderboard
	* @param void
	* @return string
	*/
 	public function save_fantasy_leaderboard(){

 		$this->benchmark->mark('code_start');
 		$this->load->model('Leaderboard_model');
 		$this->Leaderboard_model->save_fantasy_leaderboard($this->fantasy_category);
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

   /**
   * Function used for update referral leaderboard status
   * @param void
   * @return string
   */
   public function update_fantasy_leaderboard_status(){

      $this->benchmark->mark('code_start');
      $this->load->model('Leaderboard_model');
      $this->Leaderboard_model->update_fantasy_leaderboard_status($this->fantasy_category);
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
 		$this->load->model('Leaderboard_model');
 		$this->Leaderboard_model->process_prize_distribution();
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

 	/**
	* Function used for leaderboard prize distribution
	* @param int $leaderboard_id
	* @return string
	*/
 	public function prize_distribution($leaderboard_id)
 	{
 		$this->benchmark->mark('code_start');
 		if($leaderboard_id != ""){
 			$this->load->model('Leaderboard_model');
 			$this->Leaderboard_model->prize_distribution($leaderboard_id);
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
 		$this->load->model('Leaderboard_model');
 		$this->Leaderboard_model->prize_notification();
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

 }