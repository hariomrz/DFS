<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
require_once 'Cron.php';
class Stock_predict_cron extends Cron {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        echo "Welcome";die();
    }

    /**
     * @since Dec 2021
     * @uses cron to update collection stock open_price and close_price for candles
     * @param type int 1 => 
     * To update open price if current_time > start time and current time < end_time 
     * type =>  2 to update close price if current_time > end_time and C.status= 0
     ***/
    function update_collection_stock_rates_get($type=1)
    {
        $this->load->model('Stock_predict_model');
        $this->benchmark->mark('code_start');
        $this->Stock_predict_model->update_collection_stock_rates($type);
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');   
        exit();
    }

    /**
     * Used for update score in user team lineup table
     * @param int $sports_id
     * @return string
     */
    public function update_scores_in_portfolio_by_collection_get($collection_id="")
    {
            $this->load->model('Stock_predict_model');
            $this->benchmark->mark('code_start');
            $this->Stock_predict_model->update_scores_in_portfolio_by_collection($collection_id);
            $this->benchmark->mark('code_end');
            echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

        /**
     * Update upcoming collection team data format
     * @param void
     * @return string
     */
    public function move_completed_collection_team_get(){
        $this->benchmark->mark('code_start');
        $this->load->model('Stock_predict_model');
        $this->Stock_predict_model->move_completed_collection_team();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for update match contest status
     * @param int 
     * @return string print output
     */
    public function update_predict_contest_status_get()
    {
        
        $this->load->model('Stock_predict_model');
        $this->benchmark->mark('code_start');
        $this->Stock_predict_model->update_predict_contest_status();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');   
        exit();
    }

    /**
     * Close candle taking nearest value of 5 Minutes on both the sides
     * This function use when stock data not come from feed for a partular time
     * @param type 1=Open price,2=Close price 
     */
    public function update_stock_rate_with_nearest_value_get($type=1)
    {
        $this->load->model('Stock_predict_model');
        $this->benchmark->mark('code_start');
        $this->Stock_predict_model->update_stock_rates_narest_value($type);
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');   
        exit();
    }



}