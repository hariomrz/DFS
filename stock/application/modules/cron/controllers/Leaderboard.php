 <?php if(!defined('BASEPATH')) exit('No direct script access allowed');
 set_time_limit(0);

 class Leaderboard extends Common_Api_Controller {
 	
 	public function __construct(){
 		parent::__construct();
 	}

 	public function index(){
 		echo "Welcome"; die(); 
 	}



 	/**
	* Function used for update fantasy points for leaderboard
	* @param void
	* @return string
	*/
 	public function save_stock_leaderboard_get(){

 		$this->benchmark->mark('code_start');
 		$this->load->model('cron/Leaderboard_model');
 		$this->Leaderboard_model->save_stock_leaderboard();
 		$this->benchmark->mark('code_end');
 		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
 		exit();
 	}

   /**
   * Function used for update referral leaderboard status
   * @param void
   * @return string
   */
   public function update_stock_leaderboard_status_get(){

      $this->benchmark->mark('code_start');
      $this->load->model('cron/Leaderboard_model');
      $this->Leaderboard_model->update_stock_leaderboard_status();
      $this->benchmark->mark('code_end');
      echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
      exit();
   }  

 }