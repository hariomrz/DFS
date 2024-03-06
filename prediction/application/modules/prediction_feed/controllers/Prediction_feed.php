<?php
use PhpAmqpLib\Connection\AMQPStreamConnection;
class Prediction_feed extends Common_Api_Controller 
{

	function __construct()
	{
        parent::__construct();
        $allow_prediction_system = isset($this->app_config['allow_prediction_system'])?$this->app_config['allow_prediction_system']['key_value']:0;
        if(empty($allow_prediction_system)){
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['message'] = 'Module is not activated';
            $this->api_response();
        }

        if($allow_prediction_system ==  1 && $this->app_config['allow_prediction_system']['custom_data']['allow_feed'] == 1){
           //DO nothing
        }else{
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['message'] = 'Feed configuration is not activated';
            $this->api_response();
        }
        $this->feed_url = 'https://framework.vinfotech.org/';
        $this->table_name = PREDICTION_MASTER;
    }

   /**
    * Get prediction push details for feed
    * @type GET
    */
    public function push_question_get()
    {
    	$this->load->model('Prediction_feed_model');
    	$result = $this->Prediction_feed_model->get_all_prediction();
    	$this->api_response_arry['data']   = $result;
        $this->api_response();
    }

    /**
    * Pull prediction details for feed
    * @type GET
    */
    public function pull_question_get()
    {   
        $this->benchmark->mark('code_start');   
        $url = $this->feed_url.'prediction/prediction_feed/push_question';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        $result = json_decode($result,1);
        if(!empty($result['data'])){
           $data = $result['data'];
           $this->load->model('Prediction_feed_model');
           $this->Prediction_feed_model->save_question($data);   
        }

        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();

    }

   /**
    * push prediction details
    * @type GET
    */
    public function push_answer_post()
    { 
        $post_data = $this->input->post();
        if(empty($post_data)){
            return true;
        }
        $this->load->model('Prediction_feed_model');
        $result = $this->Prediction_feed_model->get_all_prediction_answer($post_data);
        $this->api_response_arry['data']   = !empty($result)?$result:[];
        $this->api_response();
    }


    /**
    * pull details for feed
    * @type GET
    */
    public function pull_answer_get()
    {   
        $this->benchmark->mark('code_start');   
        $this->load->model('Prediction_feed_model');
        $records = $this->Prediction_feed_model->get_all_table_data('feed_id',PREDICTION_MASTER,['status'=>0,'feed_id >'=>0]); //prediction_master_id

        if(!empty($records)){
            $post_param = json_encode(array_column($records, 'feed_id'));
          
            $url = $this->feed_url.'prediction/prediction_feed/push_answer';
            $header = array("Content-Type:application/json", "Accept:application/json");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_param);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $result = json_decode($result,1);
            if(!empty($result['data'])){
               $data = $result['data'];
               $this->Prediction_feed_model->save_answer($data);   
              
            }
        }

        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /* Used for process_prediction_refund
     * @param 
     * @return boolean
     */
    public function process_prediction_feed_get() {
        $exchange = "prediction_exchange";
        $consumer_id = isset($argv[1]) ? $argv[1] : 'c1';
        $connection = new AMQPStreamConnection(FEED_MQ_HOST, FEED_MQ_PORT, FEED_MQ_USER, FEED_MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->exchange_declare($exchange, 'fanout', false, true, false);
        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
        $channel->queue_bind($queue_name, $exchange);
        echo " [*] Consumer {$consumer_id} is waiting for messages. To exit press CTRL+C\n";
        $callback = function ($msg) use ($consumer_id) 
        {
            try{
                $params = json_decode($msg->body, TRUE);
                $this->load->model('Prediction_feed_model');
                $data[0] = $params;
                if($data[0]['action'] == 1){ 
                    $this->Prediction_feed_model->save_question($data);
                }elseif($data[0]['action'] == 2){
                    $this->Prediction_feed_model->save_answer($data);
                }elseif($params['action'] == 3){
                    $this->Prediction_feed_model->update_pause_play($params);
                }elseif($params['action'] == 4){
                    $this->Prediction_feed_model->update_pin_prediction($params);
                }elseif($params['action'] == 5){
                    $this->Prediction_feed_model->update_prediction($params);
                }elseif($params['action'] == 6){
                    $this->Prediction_feed_model->delete_prediction($params);
                }  
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
        };
        
        //$channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            try {
                $channel->wait(null, false, 10);
            } catch (AMQPTimeoutException $e) {
                $connection->checkHeartBeat();
                continue;
            } catch (Exception $e) {
                $logger->error((string) $e);
                exit;
            }
        }

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    public function test_get()
    {   echo '111';die;
         $this->load->model('Prediction_feed_model');
         $data[0] = array('prediction_master_id'=>'180','prediction_option_id'=>581);
        $this->Prediction_feed_model->save_answer($data);
        //$this->Prediction_feed_model->process_prediction_winning(163);
    }
    
}                                                                  