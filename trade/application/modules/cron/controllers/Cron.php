<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends MY_Controller
{
    public $user_id = 1;
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo "Welcome";die();
    }

    /**
     * Used for auto publish fixture with default question
     * @param int $sports_id
     * @return string
     */
    public function auto_publish_fixture($sports_id=7){
        $this->benchmark->mark('code_start');
        if($sports_id){
            $this->load->model('Cron_model');
            $this->Cron_model->auto_publish_fixture($sports_id);
        }
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Function used for cancel user entry push
     * @param void
     * @return string
     */
    public function game_cancellation()
    {   
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->game_cancellation();
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for auto publish fixture with default question
     * @param int $sports_id
     * @param int $type
     * @return string
     */
    public function cancel_question($question_id,$type=0){
        $this->benchmark->mark('code_start');
        if($question_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->cancel_question($question_id,$type);
        }
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for auto publish fixture with default question
     * @param int $sports_id
     * @return string
     */
    public function update_question_status($season_id=''){
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->update_question_status($season_id);
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for prize_distribution
     * @param void
     * @return string
     */
    public function prize_distribution()
    {
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value'] : 0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1"){
            $this->load->model('Cron_model');
            $this->Cron_model->prize_distribution();
        }
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for match prize distribution
     * @param int $season_id
     * @return string
     */
    public function match_prize_distribution($season_id=''){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value'] : 0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && isset($season_id) && $season_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->match_prize_distribution($season_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for notification
     * @param void
     * @return string
     */
    public function Notification($type=0)
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->notification($type);
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for matchup cron
     * @param void
     * @return string
     */
    public function trade_matchup()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->trade_matchup();
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }
    

    public function test_node(){
        $this->load->library('Node');
        
        $data['question_id'] = 12;
        $data['user_id'] = 12;
        
        $match_scores = array("question_id"=>$collection['question_id'],"user_id"=>$data['user_id']);
        $node = new node(array("route" => 'updateQestnTrade', "postData" => array("data" => $match_scores)));
        
    }
    
}
