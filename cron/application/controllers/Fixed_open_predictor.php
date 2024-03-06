<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Fixed_open_predictor extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $allow_fixed_open_predictor = isset($this->app_config['allow_fixed_open_predictor'])?$this->app_config['allow_fixed_open_predictor']['key_value']:0;
        if(!$allow_fixed_open_predictor)
        {
            exit();
        } 
    }

    public function index()
    {
        echo "Welcome";die();
    }

    
    /**
     * @uses function function to refund prediction coins in case of delete 
     * @method refund_prediction_coins
     * @since
     * */
    public function update_day_prize_status()
    {
    
        $this->load->model('Fixed_open_predictor_model');
        $this->benchmark->mark('code_start');
            $this->Fixed_open_predictor_model->update_day_prize_status(array('deadline_date' => '2020-04-29 18:30:00'));
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

     /**
     * @uses function function to refund prediction coins in case of delete 
     * @method refund_prediction_coins
     * @since
     * */
    public function update_week_prize_status()
    {
    
        $this->load->model('Fixed_open_predictor_model');
        $this->benchmark->mark('code_start');
            $this->Fixed_open_predictor_model->update_week_prize_status(array('deadline_date' => '2020-04-27 00:00:00'));
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }


    public function update_day_rank()
    {
    
        $this->load->model('Fixed_open_predictor_model');
        $this->benchmark->mark('code_start');

        //get _single row
        $row = $this->Fixed_open_predictor_model->get_one_prize_history_to_process(1);

        if(!empty($row))
        {
            $this->Fixed_open_predictor_model->update_day_rank($row['prize_date']);
            $this->Fixed_open_predictor_model->update_day_prize_status(array('deadline_date' => $row['prize_date']));
        }else{

            $this->Fixed_open_predictor_model->update_day_rank();
            $this->Fixed_open_predictor_model->update_day_prize_status(array('deadline_date' => format_date()));
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function update_week_rank()
    {
        $this->load->model('Fixed_open_predictor_model');
        $this->benchmark->mark('code_start');
        //get _single row
        $row = $this->Fixed_open_predictor_model->get_one_prize_history_to_process(2);
 
        if(!empty($row))
        {
            $this->Fixed_open_predictor_model->update_week_rank($row['prize_date']);
            $this->Fixed_open_predictor_model->update_week_prize_status(array('deadline_date' => $row['prize_date']));
        }
        else{
            $this->Fixed_open_predictor_model->update_week_rank();
            $this->Fixed_open_predictor_model->update_week_prize_status(array('deadline_date' => format_date()));
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }


    public function update_month_rank()
    {
        $this->load->model('Fixed_open_predictor_model');
        $this->benchmark->mark('code_start');
        //get _single row
        $row = $this->Fixed_open_predictor_model->get_one_prize_history_to_process(3);
        if(!empty($row))
        {
            $this->Fixed_open_predictor_model->update_month_rank($row['prize_date']);
            $this->Fixed_open_predictor_model->update_month_prize_status(array('deadline_date' => $row['prize_date']));
        }
        else
        {
            $this->Fixed_open_predictor_model->update_month_rank();
            $this->Fixed_open_predictor_model->update_month_prize_status(array('deadline_date' => format_date()));
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

      /**
     * Used for update lobby fixture list on s3
     * @param int $sports_id
     * @return string
     */
    public function update_lobby_fixture_data()
    {   
            $input_arr = array();
            $input_arr['is_cron_data'] = 1;
            $this->http_post_request("fixed_open_predictor/get_lobby_fixture",$input_arr,1);
            echo "done";
       
        exit();
    }

    /**
    * Function used for daily prediction prize distribution
    * @param
    * @return string
    */
    public function daily_prediction_prize_distribute()
    {
        $this->load->model('Fixed_open_predictor_model');
        $this->benchmark->mark('code_start');
            $this->Fixed_open_predictor_model->daily_prediction_prize_distribute();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * Function used for weekly prediction prize distribution
    * @param
    * @return string
    */
    public function weekly_prediction_prize_distribute()
    {
        $this->load->model('Fixed_open_predictor_model');
        $this->benchmark->mark('code_start');
            $this->Fixed_open_predictor_model->weekly_prediction_prize_distribute();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * Function used for monthly prediction prize distribution
    * @param
    * @return string
    */
    public function monthly_prediction_prize_distribute()
    {
        $this->load->model('Fixed_open_predictor_model');
        $this->benchmark->mark('code_start');
            $this->Fixed_open_predictor_model->monthly_prediction_prize_distribute();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * Function used for send prediction winning notification
    * @param
    * @return string
    */
    public function fixed_prediction_prize_notification()
    {
        $this->load->model('Fixed_open_predictor_model');
        $this->benchmark->mark('code_start');
            $this->Fixed_open_predictor_model->fixed_prediction_prize_notification();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

}