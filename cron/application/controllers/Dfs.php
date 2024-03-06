<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Dfs extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Dfs_model');
    }

    public function index()
    {
        echo "Welcome";die();
    }

    /**
     * Used for game abandoned
     * @param int $sports_id
     * @return string
     */
    public function game_abandoned() {
        $this->load->model('Cron_model');
        $this->Dfs_model->game_abandoned();
        exit();
    }

    /**
     * Used for contest reschedule
     * @param void
     * @return string
     */
    public function contest_rescheduled()
    {
        $this->benchmark->mark('code_start');
        $this->Dfs_model->contest_rescheduled();
        $this->benchmark->mark('code_end');
       echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
       exit(); 
    }

    /**
     * [game_cancellation description]
     * @Summary :- This function will cancell the games which is not full till drafting.
     * @return  [type]
     */
    public function game_cancellation()
    {   
        $this->benchmark->mark('code_start');
        $this->Dfs_model->game_cancellation();
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * Function used for send cancel notification
    * @param
    * @return string
    */
    public function match_cancel_notification()
    {
        $this->benchmark->mark('code_start');
        $this->Dfs_model->match_cancel_notification();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for generate tax invoice for a particular invoice and send to respective customer
     * @param void
     * @return string
     */
    public function process_bench()
    {
        $bench_player = isset($this->app_config['bench_player'])?$this->app_config['bench_player']['key_value']:0;

        $this->benchmark->mark('code_start');
        if($bench_player == "1")
        {
            $this->Dfs_model->process_bench();
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for push contest list in queue for pdf
     * @param 
     * @return string
     */
    public function push_lineup_move()
    {   
        $this->benchmark->mark('code_start');
        $this->Dfs_model->push_lineup_move();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for process collection teams
     * @param int $collection_master_id
     * @return string
     */
    public function lineup_move($collection_master_id)
    {   
        $this->benchmark->mark('code_start');
        if($collection_master_id != ""){
            ini_set('memory_limit', '-1');
            $this->Dfs_model->lineup_move($collection_master_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Update upcoming collection team data format
     * @param void
     * @return string
     */
    public function move_completed_collection_team(){
        $this->benchmark->mark('code_start');
        $this->Dfs_model->move_completed_collection_team();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Update upcoming collection team data format
     * @param void
     * @return string
     */
    public function archive_collection_team_table(){
        $this->benchmark->mark('code_start');
        $this->Dfs_model->archive_collection_team_table();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for push contest list in queue for pdf
     * @param 
     * @return string
     */
    public function push_live_collection_for_pdf()
    {   
        $this->benchmark->mark('code_start');
        $this->Dfs_model->push_live_collection_for_pdf();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for generate contest pdf and upload on s3
     * @param int $contest_id
     * @return string
     */
    public function generate_contest_pdf($contest_id)
    {   
        $this->benchmark->mark('code_start');
        if($contest_id != ""){
            ini_set('memory_limit', '-1');
            $this->Dfs_model->generate_contest_pdf($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for update score in user team lineup table
     * @param void
     * @return string
     */
    public function update_lineup_score($sports_id='')
    {
        $this->benchmark->mark('code_start');
        $this->Dfs_model->update_lineup_score($sports_id);
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for update fixture score in user team lineup table
     * @param int $collection_master_id
     * @return string
     */
    public function update_collection_score($collection_master_id='')
    {
        $this->benchmark->mark('code_start');
        if($collection_master_id != ""){
            $this->Dfs_model->update_collection_score($collection_master_id);
        }
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for update match contest status
     * @param int $sports_id
     * @return string print output
     */
    public function update_contest_status($sports_id='')
    {
        $prize_cron = isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1"){
            $this->Dfs_model->update_contest_status($sports_id);
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
    public function prize_distribution()
    {   
        $prize_cron = isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1"){
            $this->Dfs_model->prize_distribution();
        }
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for match prize distribution
     * @param int $collection_master_id
     * @return string
     */
    public function match_prize_distribution($collection_master_id=''){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && isset($collection_master_id) && $collection_master_id != ""){
            $this->Dfs_model->match_prize_distribution($collection_master_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for process financial year tds
     * @param int $fy
     * @return string
     */
    public function fy_tds_process($fy='2023'){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && isset($fy) && $fy != ""){
            $this->Dfs_model->fy_tds_process($fy);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for process match tds
     * @param int $collection_master_id
     * @return string
     */
    public function tds_process($collection_master_id=''){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && isset($collection_master_id) && $collection_master_id != ""){
            $this->Dfs_model->tds_process($collection_master_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for process financial year net winning settlement
     * @param 
     * @return string
     */
    public function fy_settlement(){
        $this->benchmark->mark('code_start');
        $this->Dfs_model->fy_settlement();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * Function used for send winning notification
    * @param
    * @return string
    */
    public function match_prize_distribute_notification()
    {
        $this->benchmark->mark('code_start');
        $this->Dfs_model->match_prize_distribute_notification();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for revert contest prize distribution
     * @param int $contest_id
     * @return string
     */
    public function revert_contest_prize($contest_id = ""){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "0" && $contest_id != ""){
            $this->Dfs_model->revert_contest_prize($contest_id);
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
            $this->Dfs_model->push_contest_for_gst_report();
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
            $this->Dfs_model->generate_gst_report($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * This function used for validation match users team and players count
    * @param array $post_data
    */
    public function validate_match_team_data()
    {
        $post_data = $_REQUEST;
        if(isset($post_data['collection_id']) && $post_data['collection_id'] != ""){
            $this->Dfs_model->validate_match_team_data($post_data);
            echo "done";
        }else{
            echo "access denied";
        }
        exit();
    }

    /**
     * Used for auto publish dfs matches
     * @param int $sports_id
     * @return string
     */
    public function auto_publish_fixture($sports_id='')
    {
        if (!empty($sports_id))
        {
            $auto_publish = isset($this->app_config['allow_dfs']['custom_data']['auto_publish']) ? $this->app_config['allow_dfs']['custom_data']['auto_publish'] : 0;
            $this->benchmark->mark('code_start');
            if($auto_publish == "1" && !in_array($sports_id,[11,15])){
                $result = $this->Dfs_model->auto_publish_fixture($sports_id);
                if($result){
                    //delete lobby upcoming section file
                    $this->Dfs_model->push_s3_data_in_queue('lobby_fixture_list_'.$sports_id,array(),"delete");
                }
            }
            $this->benchmark->mark('code_end');
            echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        }    
        exit();
    }

    /**
     * Process affiliate match wiese cashback
     * @param int $cm_id
     * @return string
     */
    public function process_affiliate_cb($cm_id='')
    {
        if(!empty($cm_id))
        {
            $this->benchmark->mark('code_start');
            $af_cb = isset($this->app_config['affiliate_module']['custom_data']['site_commission']) ? $this->app_config['affiliate_module']['custom_data']['site_commission'] : 0;
            if($af_cb){
                $this->Dfs_model->process_affiliate_cb($cm_id);
            }
            $this->benchmark->mark('code_end');
            echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        }
        exit();
    }

    
}
