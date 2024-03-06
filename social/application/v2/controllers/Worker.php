<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once getcwd(). '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Worker extends CI_Controller
{
    public $DeviceTypeID = 1;
    public $job_server_fun = array();
    function __construct()
    {
        parent::__construct();
        $this->job_server_fun['Rabbitmq'] = 'process_rabbitmq_jobs';
        $this->job_server_fun['Gearman'] = 'process_gearman_jobs';
         //Load cache driver
        $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
        /*if(php_sapi_name() != 'cli'){
            echo "Invalid Request";die;
            return false;
        }*/

    }
    
    public function index() {
        $process_job = $this->job_server_fun[JOBSERVER];        
        $this->$process_job();
    }  

    /*
    Funtion to process backgroun job using Gearman
     */
    public function process_gearman_jobs() 
    {
        $worker = new GearmanWorker();
        $worker->addServer();

        $worker->addFunction("calculate_activity_rank", function(GearmanJob $Job) {
            $data = json_decode($Job->workload(), true);
            if(!empty($data['UserGUID'])) {
                $this->db->close();
                $this->db->initialize();
                $this->load->model('cron/cron_model');
                $this->cron_model->calculate_rank($data['UserGUID'],$data['ENVIRONMENT']);
            }
        });

        $worker->addFunction("check_activity_visibility", function(GearmanJob $Job) {
            $data = json_decode($Job->workload(), true);

            if(!empty($data['ActivityGUID']) && !empty($data['ENVIRONMENT'])) {
                $this->db->close();
                $this->db->initialize();
                $this->load->model('cron/cron_model');
                $this->cron_model->check_activity_visibility($data['ActivityGUID'],$data['data']);
            }
        });
        $worker->addFunction("activity_cache", function(GearmanJob $Job) {
            $this->db->close();
            $this->db->initialize();
            $this->load->model('activity/activity_model');
            $data = json_decode($Job->workload(), true);
            $this->activity_model->activity_cache($data['ActivityID']);   
        });  
        
        $worker->addFunction("profile_cache", function(GearmanJob $Job) {
            $this->db->close();
            $this->db->initialize();
            $this->load->model('users/user_model');
            $data = json_decode($Job->workload(), true);
            $this->user_model->profile_cache($data['user_id']);   
        });        
       
        $worker->addFunction("update_login_analytics", function(GearmanJob $Job) {
            $this->db->close();
            $this->db->initialize();
            $this->load->model('users/login_model');
            $data = json_decode($Job->workload(), true);
            $this->login_model->update_login_analytics($data);
        });        
        $worker->addFunction("calculate_default_activity", function(GearmanJob $Job) {
            $this->db->close();
            $this->db->initialize();
            $data = json_decode($Job->workload(), true);
            $this->load->model(array('cron/cronrule_model'));
            $this->cronrule_model->calculate_default_activity($data['rule_id']);
            
        });
        $worker->addFunction("add_update_relationship_score", function(GearmanJob $Job) {
            $this->db->close();
            $this->db->initialize();
            $this->load->model(array('log/user_activity_log_score_model'));
            $data = json_decode($Job->workload(), true);
            $this->user_activity_log_score_model->add_update_relationship_score($data['UserID'], $data['ModuleID'], $data['ModuleEntityID'], $data['Score']);            
        });
        $worker->addFunction("save_log", function(GearmanJob $Job) {
            $this->db->close();
            $this->db->initialize();
            $data = json_decode($Job->workload(), true);
            $this->load->helper('activity');
            save_log($data['UserID'], $data['LogType'], $data['EntityGUID'], $data['Flag'], $data['DeviceTypeID']);            
        });
        $worker->addFunction("set_promotion", function(GearmanJob $Job) {
            $this->db->close();
            $this->db->initialize();
            $data = json_decode($Job->workload(), true);
            $this->load->model(array('activity/activity_front_helper_model'));
            $this->activity_front_helper_model->set_promotion($data['ActivityID'], $data['EntityID'], $data['EntityType']);            
        });
        $worker->addFunction("add_activity_log", function(GearmanJob $Job) {
            $this->db->close();
            $this->db->initialize();
            $data = json_decode($Job->workload(), true);
            $this->load->model(array('log/user_activity_log_score_model'));
            $score = $this->user_activity_log_score_model->get_score_for_activity($data['ActivityTypeID'], $data['ModuleID'], $data['ParentCommentID'], $data['UserID']);
            $data['Score'] = $score;
            $data['ActivityDate'] = get_current_date('%Y-%m-%d');
            unset($data['ParentCommentID']);
            $log_type = '';
            if(isset($data['LogType'])) {
                $log_type = $data['LogType'];
                unset($data['LogType']);
            }
            $this->user_activity_log_score_model->add_activity_log($data, $log_type);            
        });
        $worker->addFunction("upload_api_data_on_bucket", function(GearmanJob $Job) {
            $this->db->close();
            $this->db->initialize();
            $data = json_decode($Job->workload(), true);
            $this->load->model(array('users/login_model'));
            $this->login_model->upload_api_data_on_bucket($data['FileName'], $data['FileData']);
        });
        while ($worker->work());
    }
    
    public function process_notification() {
        $worker = new GearmanWorker();
        $worker->addServer();
        
        $worker->addFunction("SendPushMsg",function(GearmanJob $Job){
            $data = json_decode($Job->workload(), true);
            $this->db->close();
            $this->db->initialize();
            SendPushMsg($data['ToUserID'], $data['Subject'],$data['notifications']);
        });
        $worker->addFunction("send_post_notification",function(GearmanJob $Job){
            $data = json_decode($Job->workload(), true);
            $this->db->close();
            $this->db->initialize();
            $this->load->model(array('notification_model'));   
            $is_edit = 0;
            if(isset($data['IsEdit'])) {
                $is_edit = $data['IsEdit'];
            }                           
            $this->notification_model->send_post_notifications($data['UserID'], $data['PostContent'], $data['ActivityTypeID'], $data['ActivityID'], $data['ModuleID'], $data['ModuleEntityID'], $data['AfterProcess'], $data['PostAsModuleID'], $data['PostAsModuleEntityID'], $data['ExcludedUsers'], $data['PostType'], $data['NotifyAll'], $is_edit);
        });
        $worker->addFunction("post_notification",function(GearmanJob $Job){
            $data = json_decode($Job->workload(), true);
            $this->db->close();
            $this->db->initialize();
            $this->load->model(array('notification_model'));
            $this->notification_model->post_notification($data); 
        });
        //medthod owner : trilok umath 
        $worker->addFunction("send_notification",function(GearmanJob $Job){
            $data = json_decode($Job->workload(), true);
            $this->db->close();
            $this->db->initialize();
            $this->load->model(array('admin/users/crm_model'));
            $this->crm_model->send_notification($data); 
        });
        $worker->addFunction("add_notification",function(GearmanJob $Job){
            $data = json_decode($Job->workload(), true);
            $this->db->close();
            $this->db->initialize();
            $this->load->model(array('notification_model')); 
            $extra_param = $data['ExtraParams'];
            $forcely_add = 0;
            $send_email = true;
            if(isset($extra_param['ForcelyAdd'])) {
                $forcely_add = $extra_param['ForcelyAdd'];
                unset($extra_param['ForcelyAdd']);
            }
            if(isset($extra_param['SendEmail'])) {
                $send_email = $extra_param['SendEmail'];
                unset($extra_param['SendEmail']);
            }
            $this->notification_model->add_notification($data['NotificationTypeID'], $data['SenderID'], $data['ReceiverIDs'], $data['RefrenceID'], $data['Parameters'], $send_email, $forcely_add, $extra_param);
        });
        $worker->addFunction("send_like_notification",function(GearmanJob $Job){
            $data = json_decode($Job->workload(), true);
            $this->db->close();
            $this->db->initialize();
            $this->load->model(array('activity/activity_model'));
            $this->activity_model->send_like_notification($data);
        });
        $worker->addFunction("send_comment_notification",function(GearmanJob $Job){
            $data = json_decode($Job->workload(), true);
            $this->db->close();
            $this->db->initialize();
            $this->load->model(array('activity/activity_model'));
            $this->activity_model->send_comment_notification($data);
        });
        $worker->addFunction("subscribe_email",function(GearmanJob $Job){
            $data = json_decode($Job->workload(), true);
            $this->db->close();
            $this->db->initialize();
            $tagged_entity = 0;
            $send_email = true;
            $comment_id = 0;
            if(isset($data['TaggedEntity'])) {
                $tagged_entity = $data['TaggedEntity'];
            }
            if(isset($data['SendEmail'])) {
                $send_email = $data['SendEmail'];
            }
            if(isset($data['CommentID'])) {
                $comment_id = $data['CommentID'];
            }
            $this->load->model(array('subscribe_model'));
            $this->subscribe_model->subscribe_email($data['UserID'], $data['EntityID'], $data['SubscribeAction'], $send_email, $tagged_entity, $comment_id);
        });
        while ($worker->work());
    }
    
    public function process_image_thumb() {
        $worker = new GearmanWorker();
        $worker->addServer();
        
        $worker->addFunction("create_thumb", function(GearmanJob $Job) {
            $this->db->close();
            $this->db->initialize();
            $this->load->model(array('upload_file_model')); 
            $data = json_decode($Job->workload(), true);
            $this->upload_file_model->uploadImageInBg($data);
        });
        while ($worker->work());
    }
    
    public function process_notification_r() {
        $market_arr = array();
        //Live AMQP server
        $connection = new PhpAmqpLib\Connection\AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();

        $channel->queue_declare("notification", false, true, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";

        $callback = function($msg)
        {
            $data = json_decode($msg->body,TRUE);
            if(!empty($data['method']))
            {
                switch ($data['method'])  {                   

                    case 'SendPushMsg':
                        $this->db->close();
                        $this->db->initialize();
                        SendPushMsg($data['data']['ToUserID'], $data['data']['Subject'],$data['data']['notifications']);
                        break;                   
                    case 'post_notification':
                            $this->db->close();
                            $this->db->initialize();
                            $this->load->model(array('notification_model'));
                            $this->notification_model->post_notification($data['data']); 
                        break;
                    case 'send_notification': 
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data']; 
                            $this->load->model(array('admin/users/crm_model'));
                            $this->crm_model->send_notification($data); 
                        break;
                    case 'add_notification': 
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data']; 
                            $this->load->model(array('notification_model')); 
                            $extra_param = $data['ExtraParams'];
                            $forcely_add = 0;
                            $send_email = true;
                            if(isset($extra_param['ForcelyAdd'])) {
                                $forcely_add = $extra_param['ForcelyAdd'];
                                unset($extra_param['ForcelyAdd']);
                            }
                            if(isset($extra_param['SendEmail'])) {
                                $send_email = $extra_param['SendEmail'];
                                unset($extra_param['SendEmail']);
                            }
                            $this->notification_model->add_notification($data['NotificationTypeID'], $data['SenderID'], $data['ReceiverIDs'], $data['RefrenceID'], $data['Parameters'], $send_email, $forcely_add, $extra_param);
                        break;                    
                    case 'send_like_notification': 
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data']; 
                            $this->load->model(array('activity/activity_model'));
                            $this->activity_model->send_like_notification($data);
                        break;
                    case 'send_comment_notification': 
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data']; 
                            $this->load->model(array('activity/activity_model'));
                            $this->activity_model->send_comment_notification($data);
                        break;
                    case 'send_post_notification': 
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data']; 
                            $this->load->model(array('notification_model'));   
                            $is_edit = 0;
                            if(isset($data['IsEdit'])) {
                                $is_edit = $data['IsEdit'];
                            }
                            $this->notification_model->send_post_notifications($data['UserID'], $data['PostContent'], $data['ActivityTypeID'], $data['ActivityID'], $data['ModuleID'], $data['ModuleEntityID'], $data['AfterProcess'], $data['PostAsModuleID'], $data['PostAsModuleEntityID'], $data['ExcludedUsers'], $data['PostType'], $data['NotifyAll'], $is_edit);
                        break;
                    case 'subscribe_email':
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data']; 
                            $tagged_entity = 0;
                            $send_email = true;
                            $comment_id = 0;
                            if(isset($data['TaggedEntity'])) {
                                $tagged_entity = $data['TaggedEntity'];
                            }
                            if(isset($data['SendEmail'])) {
                                $send_email = $data['SendEmail'];
                            }
                            if(isset($data['CommentID'])) {
                                $comment_id = $data['CommentID'];
                            }
                            $this->load->model(array('subscribe_model'));
                            $this->subscribe_model->subscribe_email($data['UserID'], $data['EntityID'], $data['SubscribeAction'], $send_email, $tagged_entity, $comment_id);
                        break;
                    default:
                        # code...
                        break;
                }
            }
            //echo " [x] Received ", $msg->body, "\n";
            //sleep(substr_count($msg->body, '.'));
            //echo " [x] Done", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('notification', '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
    
    public function process_post_notification() {
        $market_arr = array();
        //Live AMQP server
        $connection = new PhpAmqpLib\Connection\AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();

        $channel->queue_declare("post_notification", false, true, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";

        $callback = function($msg)
        {
            $data = json_decode($msg->body,TRUE);
            if(!empty($data['method']))
            {
                switch ($data['method'])  {                   

                    case 'SendPushMsg':
                        $this->db->close();
                        $this->db->initialize();
                        SendPushMsg($data['data']['ToUserID'], $data['data']['Subject'],$data['data']['notifications']);
                        break;                    
                    default:
                        # code...
                        break;
                }
            }
            //echo " [x] Received ", $msg->body, "\n";
            //sleep(substr_count($msg->body, '.'));
            //echo " [x] Done", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('post_notification', '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
    
    public function process_image_thumb_r() {
        $market_arr = array();
        //Live AMQP server
        $connection = new PhpAmqpLib\Connection\AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();

        $channel->queue_declare("process_image", false, true, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";

        $callback = function($msg)
        {
            $data = json_decode($msg->body,TRUE);
            if(!empty($data['method']))
            {
                switch ($data['method'])  {
                    case 'create_thumb': 
                            $this->db->close();
                            $this->db->initialize();
                            $this->load->model(array('upload_file_model')); 
                            $this->upload_file_model->uploadImageInBg($data['data']);
                        break; 
                    
                    case 'update_media_analytics':
                            $this->db->close();
                            $this->db->initialize();
                            $this->load->model(array('upload_file_model'));
                            $this->upload_file_model->check_media_counts($data['data'], TRUE);      
                         break;
                    default:
                        # code...
                        break;
                }
            }
            //echo " [x] Received ", $msg->body, "\n";
            //sleep(substr_count($msg->body, '.'));
            //echo " [x] Done", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('process_image', '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
    
    /*
    Funtion to process backgroun job using Rabbitmq
     */
    public function process_rabbitmq_jobs() {
        $market_arr = array();
        //Live AMQP server
        $connection = new PhpAmqpLib\Connection\AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();

        $channel->queue_declare(ENVIRONMENT, false, true, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";

        $callback = function($msg)
        {
            $data = json_decode($msg->body,TRUE);
            if(!empty($data['method']))
            {
                switch ($data['method']) 
                {
                    case 'calculate_activity_rank':

                        $this->db->close();
                        $this->db->initialize();

                        if(!empty($data['data']['UserGUID']))
                        {
                            $this->load->model('cron/cron_model');
                            $this->cron_model->calculate_rank($data['data']['UserGUID'],$data['data']['ENVIRONMENT']);
                        }
                        break;
                    case 'check_activity_visibility':

                        $this->db->close();
                        $this->db->initialize();

                        if(!empty($data['data']['ActivityGUID']) && !empty($data['data']['ENVIRONMENT']))
                        {
                            $this->load->model('cron/cron_model');
                            $this->cron_model->check_activity_visibility($data['data']['ActivityGUID'],$data['data']['ENVIRONMENT']);
                        }
                        break;
                    case 'activity_cache':
                            $this->db->close();
                            $this->db->initialize();
                            $this->load->model('activity/activity_model');
                            $this->activity_model->activity_cache($data['data']['ActivityID']);                            
                        break;
                    case 'profile_cache':
                            $this->db->close();
                            $this->db->initialize();
                            $this->load->model('users/user_model');
                            $this->user_model->profile($data['data']['user_id'],0);                            
                        break;
                    case 'update_login_analytics':
                            $this->db->close();
                            $this->db->initialize();
                            $this->load->model('users/login_model');
                            $this->login_model->update_login_analytics($data['data']);
                        break;
                    
                    case 'update_signup_analytics':
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data'];
                            $session_id = $data['SessionID']; 
                            $is_sign_up = $data['IsSignUp'];
                            $user_id = $data['UserID'];
                            $client_error = $data['ClientError'];
                            $source_id = $data['SourceID'];
                            $device_type_id = $data['DeviceTypeID'];
            
                            $this->load->model('users/signup_model');
                            $this->signup_model->update_analytics($source_id, $device_type_id, $is_sign_up, $client_error, $session_id, $user_id);
                        break;                     
                    case 'calculate_default_activity': 
                            $this->db->close();
                            $this->db->initialize();
                            $this->load->model(array('cron/cronrule_model'));
                            $this->cronrule_model->calculate_default_activity($data['data']['rule_id']);
                        break; 
                    case 'add_update_relationship_score': 
                            $this->db->close();
                            $this->db->initialize();
                            $this->load->model(array('log/user_activity_log_score_model'));
                            $data = $data['data']; 
                            $this->user_activity_log_score_model->add_update_relationship_score($data['UserID'], $data['ModuleID'], $data['ModuleEntityID'], $data['Score']);
                        break;
                    case 'save_log': 
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data']; 
                            $this->load->model(array('log/user_activity_log_score_model'));
                            $this->user_activity_log_score_model->save_log($data['UserID'], $data['LogType'], $data['EntityGUID'], $data['Flag'], $data['DeviceTypeID']);
                        break;
                    case 'set_promotion': 
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data']; 
                            $this->load->model(array('activity/activity_front_helper_model'));
                            $this->activity_front_helper_model->set_promotion($data['ActivityID'], $data['EntityID'], $data['EntityType']);
                        break;                    
                    case 'add_activity_log': 
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data']; 
                            $this->load->model(array('log/user_activity_log_score_model'));
                            $score = $this->user_activity_log_score_model->get_score_for_activity($data['ActivityTypeID'], $data['ModuleID'], $data['ParentCommentID'], $data['UserID']);
                            $data['Score'] = $score;
                            $data['ActivityDate'] = get_current_date('%Y-%m-%d');
                            unset($data['ParentCommentID']);
                            $log_type = '';
                            if(isset($data['LogType'])) {
                                $log_type = $data['LogType'];
                                unset($data['LogType']);
                            }
                            $this->user_activity_log_score_model->add_activity_log($data, $log_type);
                        break;
                    case 'upload_api_data_on_bucket': 
                            $this->db->close();
                            $this->db->initialize();
                            $data = $data['data']; 
                            $this->load->model(array('users/login_model'));
                            $this->login_model->upload_api_data_on_bucket($data['FileName'], $data['FileData']);
                        break; 
                    case 'update_poll_analytics':
                            $this->db->close();
                            $this->db->initialize();
                            $this->load->model(array('polls/polls_model'));
                            $this->polls_model->poll_analytics_data();
                        break;
                    case 'send_poll_result_notificaton':
                            $this->db->close();
                            $this->db->initialize();
                            $this->load->model(array('polls/polls_model'));
                            $this->polls_model->send_poll_result_notificaton();
                        break;
                    default:
                        # code...
                        break;
                }
            }
            //echo " [x] Received ", $msg->body, "\n";
            //sleep(substr_count($msg->body, '.'));
            //echo " [x] Done", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume(ENVIRONMENT, '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }  	
}
