<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Open_predictor extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $allow_open_predictor = isset($this->app_config['allow_open_predictor'])?$this->app_config['allow_open_predictor']['key_value']:0;
        if(!$allow_open_predictor)
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
    public function refund_prediction_coins($prediction_master_id)
    {
        if(empty($prediction_master_id))
        {
            echo "Prediction id not set";
            exit();
        }

        $this->load->model('Open_prediction_model');
        $this->benchmark->mark('code_start');
            $this->Open_prediction_model->refund_prediction_coins();
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
            $this->http_post_request("open_prediction/get_lobby_fixture",$input_arr,1);
            echo "done";
       
        exit();
    }

}