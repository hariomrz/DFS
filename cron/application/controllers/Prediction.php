<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Prediction extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $allow_prediction_system = isset($this->app_config['allow_prediction_system'])?$this->app_config['allow_prediction_system']['key_value']:0;
        if(!$allow_prediction_system)
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

        $this->load->model('Prediction_model');
        $this->benchmark->mark('code_start');
            $this->Prediction_model->refund_prediction_coins();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

      /**
     * Used for update lobby fixture list on s3
     * @param int $sports_id
     * @return string
     */
    public function update_lobby_fixture_data($sports_id='')
    {   
        if (!empty($sports_id))
        {
            $input_arr = array();
            $input_arr['sports_id'] = $sports_id;
            $input_arr['is_cron_data'] = 1;
            $this->http_post_request("prediction/get_lobby_fixture",$input_arr,3);
            echo "done";
        }
        exit();
    }

}