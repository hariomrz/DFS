<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Name of Class as mentioned in $hook['post_controller]
class Db_log {
        
    function __construct() 
    {
       // Anything except exit() :P
    }
// Name of function same as mentioned in Hooks Config
    function logQueries() 
    {
        //$CI = CI_Controller::get_instance();
        $CI = & get_instance();
        $times = $CI->db->query_times;                   // Get execution time of all the queries executed by controller
        $query_log_array = array();
        foreach ($CI->db->queries as $key => $query) 
        { 
            $query_excution_time = number_format($times[$key] *1000,3);
            $query_log_array = array("time" => $query_excution_time,"uri" => $CI->uri->uri_string,"query" => $query,"updated_at" => format_date());
           // print_r(json_encode($query_log_array));die;
            //Mail if query times grater than && ENVIRONMENT == 'production'
            if($query_excution_time > QUERY_EXCUTION_TIME && QUERY_LOG_SENDER_EMAIL != '' && ENVIRONMENT == 'production')
            {    
                //send_email( QUERY_LOG_SENDER_EMAIL , $subject = SITE_TITLE." - Query taken more then standard excution time" , $message = json_encode($query_log_array) );
                        $notify_data = array();
                        $notify_data['notification_type'] = 126; 
                        $notify_data['notification_destination'] = 7; 
                        $notify_data['email'] = QUERY_LOG_SENDER_EMAIL;
                        $notify_data['subject'] = SITE_TITLE." - Cron: Query taken more then standard excution time";
                        $notify_data['message'] = $query_log_array;
                        $notify_data['content'] = '';


                        $message_details = $notify_data;

                $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
                $channel = $connection->channel();
                $push_data = json_encode($message_details);
                $message = new AMQPMessage($push_data, array('delivery_mode' => 1, 'content_type' => 'application/json')); # make message persistent as 2
                $channel->basic_publish($message, '', 'email');
                $channel->close();
                $connection->close();
                return true;
            }    
        }

    }
}