<?php

// error_reporting(E_ALL);

if (!isset($argv[1])) {
    exit('Please set environment variable.');
}

$environment = $argv[1];

if (!in_array($environment, array('development', 'testing', 'production'))) {
    exit('Invalid environment set.');
}

//include FCPATH."../all_config/demaon_hosts.php";
include(__DIR__ . '/../../../all_config/demaon_hosts.php');

unset($argv);
$_SERVER['argv'] = array();
$_SERVER['argc'] = array();

$SERVER_NAME = 'localhost';

switch ($environment) {
    case 'development':
        $SERVER_NAME = CRON_DEVELOPMENT_HOST;
        break;
    case 'testing':
        $SERVER_NAME = CRON_TESTING_HOST;
        break;
    case 'production':
        $SERVER_NAME = CRON_PRODUCTION_HOST;
        break;
    default:
        break;
}



ob_start();
$_SERVER['SERVER_NAME'] = $SERVER_NAME;
$_SERVER['CI_ENV'] = $environment;
include(__DIR__ . '/../../index.php');
ob_end_clean();
$ci = & get_instance();
$ci->load->helper('cd_mail');
$ci->load->helper('default');


require_once FCPATH . '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
//check();
$queue_name = CD_EMAIL_QUEUE;

$connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);

$channel = $connection->channel();

// echo ' [*] Waiting for Email Messages. To exit press CTRL+C', "\n";

// print_r($notification_type);
$callback = function($msg) {

    //notification action maping
    $notification_action_map = array();
    $notification_action_map[120] ="deposit_promotion_notify" ;
    $notification_action_map[121] ="contest_promotion_notify" ;
    $notification_action_map[123] ="promotion_notify" ;
    $notification_action_map[124] ="fixture_promotion_notify" ;//fixture promotion
    $notification_action_map[127] ="send_common_email" ;
    $notification_action_map[128] ="send_common_email" ;
    $notification_action_map[129] ="send_common_email" ;
    $notification_action_map[131] ="send_match_delay_email" ;
    $notification_action_map[132] ="send_lineup_announced_email" ;
    
    $data = json_decode($msg->body, TRUE);
    if (!is_array($data['content'])) {
        $data['content'] = json_decode($data['content'], TRUE);
    }

    // print_r($data);die;
    // 
    
    try{
        if (!empty($data)) {
            $ci = & get_instance();
            $ci->load->model('Communication_dashboard_model');
            $email_temp = $ci->Communication_dashboard_model->get_all_emailtemplate();

            $notification_type = array_column($email_temp, 'notification_type');

            $template_index = array_search($data['notification_type'],$notification_type);

            if($template_index !== false)
            {
                if($email_temp[$template_index]['status'] == 0)
                {
                    $data['notification_type'] = 1000;
                }
            }

            $notification_action_map[(int)$data['notification_type']]($data);

        }
    
    }
    //catch exception
    catch(Exception $e) 
    {
      return false;
    }
    // echo'</br> End'; die;
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();


function promotion_notify($data)
{
    $ci = & get_instance();
    $ci->load->model('Communication_dashboard_model');
    $message_body = str_replace("{{user_name}}", $data['content']['user_name'], $data['content']['email_body']);

    $template_data = $data['content']['template_data'];

    $message_body = str_replace("{{CURRENCY_CODE_HTML}}", CURRENCY_CODE_HTML, $message_body);
    $message_body = str_replace("{{amount}}", $template_data['bonus_amount'], $message_body);

    $tracking_url =TRACKING_URL.'?rcuid='.$data['recent_communication_unique_id'].'&uid='.$data['content']['user_id'].'&pn='.TRACKING_PROJECT.'&type=email_click&redirect_url='.urlencode(WEBSITE_URL.'?rcuid='.$data['recent_communication_unique_id']);

    $tracking_url =$ci->Communication_dashboard_model->get_short_url($tracking_url);

    $referral_url =TRACKING_URL.'?rcuid='.$data['recent_communication_unique_id'].'&uid='.$data['content']['user_id'].'&pn='.TRACKING_PROJECT.'&type=email_click&redirect_url='.urlencode(WEBSITE_URL.'refer-friend?rcuid='.$data['recent_communication_unique_id']);

    $referral_url =$ci->Communication_dashboard_model->get_short_url($referral_url);

    $message_body = str_replace("{{WEBSITE_URL}}", $tracking_url, $message_body);
    $message_body = str_replace("{{WEBSITE_REFER_URL}}",$referral_url, $message_body);
    send_promotion_email($data,$message_body);
            
}

function fixture_promotion_notify($data)
{
     $ci = & get_instance();
    $ci->load->model('Communication_dashboard_model');
    $match_data = $data['content']['template_data'];
    $message_body = str_replace("{{home}}", $match_data['home'], $data['content']['email_body']);
    $message_body = str_replace("{{away}}", $match_data['away'], $message_body);
    $message_body = str_replace("{{home_flag}}", $match_data['home_flag'], $message_body);
    $message_body = str_replace("{{away_flag}}", $match_data['away_flag'], $message_body);
    $message_body = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message_body);

    $match_str =strtolower($match_data['home']).'-vs-'.strtolower($match_data['away']).'-'.date('d-m-Y',strtotime($match_data['season_scheduled_date'])).'?rcuid='.$data['recent_communication_unique_id'];

    $fixture_url = WEBSITE_URL.$match_data['sports_name'].'/contest-listing/'.$match_data['collection_master_id'].'/'.$match_str;

     $tracking_url =TRACKING_URL.'?rcuid='.$data['recent_communication_unique_id'].'&uid='.$data['content']['user_id'].'&pn='.TRACKING_PROJECT.'&type=email_click&redirect_url='.urlencode($fixture_url);

      $tracking_url =$ci->Communication_dashboard_model->get_short_url($tracking_url);

    $message_body = str_replace("{{WEBSITE_URL}}", $tracking_url, $message_body);
    send_promotion_email($data,$message_body);
}

function send_match_delay_email($data)
{
     $ci = & get_instance();
    $ci->load->model('Communication_dashboard_model');
    $match_data = $data['content']['template_data'];
    $message_body = str_replace("{{collection_name}}", $match_data['collection_name'], $data['content']['email_body']);
    $message_body = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message_body);

    $tracking_url =TRACKING_URL.'?rcuid='.$data['recent_communication_unique_id'].'&uid='.$data['content']['user_id'].'&pn='.TRACKING_PROJECT.'&type=email_click&redirect_url='.urlencode(WEBSITE_URL.'lobby#'.$match_data['sports_name'].'/?rcuid='.$data['recent_communication_unique_id']);

    $tracking_url =$ci->Communication_dashboard_model->get_short_url($tracking_url);
    $message_body = str_replace("{{user_name}}", $data['content']['user_name'], $message_body);
    $message_body = str_replace("{{WEBSITE_URL}}", WEBSITE_URL, $message_body);
    $message_body = str_replace("{{WEBSITE_DOMAIN}}", WEBSITE_DOMAIN, $message_body);
    $message_body = str_replace("{{MINUTES}}", $match_data['delay_minute'], $message_body);
    $message_body = str_replace("{{LOBBY_URL}}", $tracking_url, $message_body);
    $message_body = str_replace("{{FB_LINK}}", FB_LINK, $message_body);
    $message_body = str_replace("{{TWITTER_LINK}}", TWITTER_LINK, $message_body);
    $message_body = str_replace("{{INSTAGRAM_LINK}}", INSTAGRAM_LINK, $message_body);
    

    $data['subject'] = str_replace("{{collection_name}}", $match_data['collection_name'], $data['subject']);
    send_promotion_email($data,$message_body);
}

function send_lineup_announced_email($data)
{
     $ci = & get_instance();
    $ci->load->model('Communication_dashboard_model');
    $match_data = $data['content']['template_data'];
    $message_body = str_replace("{{home}}", $match_data['home'], $data['content']['email_body']);
    $message_body = str_replace("{{away}}", $match_data['away'], $message_body);
    $message_body = str_replace("{{home_flag}}", $match_data['home_flag'], $message_body);
    $message_body = str_replace("{{away_flag}}", $match_data['away_flag'], $message_body);
    $message_body = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message_body);
    
   // $data['subject'] = str_replace("{{home}}",$match_data['home'].' vs '.$match_data['away'] , $data['subject']);

     $match_str =strtolower($match_data['home']).'-vs-'.strtolower($match_data['away']).'-'.date('d-m-Y',strtotime($match_data['season_scheduled_date'])).'?rcuid='.$data['recent_communication_unique_id'];

    $fixture_url = WEBSITE_URL.$match_data['sports_name'].'/my-teams/'.$match_data["collection_master_id"].'/'.$match_str;

    $tracking_url =TRACKING_URL.'?rcuid='.$data['recent_communication_unique_id'].'&uid='.$data['content']['user_id'].'&pn='.TRACKING_PROJECT.'&type=email_click&redirect_url='.urlencode($fixture_url);

    $tracking_url =$ci->Communication_dashboard_model->get_short_url($tracking_url);

    $message_body = str_replace("{{user_name}}", $data['content']['user_name'], $message_body);
    $message_body = str_replace("{{WEBSITE_URL}}", WEBSITE_URL, $message_body);
    //$message_body = str_replace("{{WEBSITE_DOMAIN}}", WEBSITE_DOMAIN, $message_body);
    $message_body = str_replace("{{MY_CONTEST_URL}}", $tracking_url, $message_body);
    $message_body = str_replace("{{FB_LINK}}", FB_LINK, $message_body);
    $message_body = str_replace("{{TWITTER_LINK}}", TWITTER_LINK, $message_body);
    $message_body = str_replace("{{INSTAGRAM_LINK}}", INSTAGRAM_LINK, $message_body);
    $message_body = str_replace("{{collection_name}}", $match_data['collection_name'], $message_body);
    
    $data['subject'] = str_replace("{{collection_name}}", $match_data['collection_name'], $data['subject']);
    send_promotion_email($data,$message_body);
}

function deposit_promotion_notify($data)
{
     $ci = & get_instance();
    $ci->load->model('Communication_dashboard_model');
    $promocode_data = $data['content']['template_data'];
    $message_body = str_replace("{{promo_code}}", $promocode_data['promo_code'], $data['content']['email_body']);
    $message_body = str_replace("{{offer_percentage}}", $promocode_data['discount'] , $message_body);

    $tracking_url =TRACKING_URL.'?rcuid='.$data['recent_communication_unique_id'].'&uid='.$data['content']['user_id'].'&pn='.TRACKING_PROJECT.'&type=email_click&redirect_url='.urlencode(WEBSITE_URL.'?rcuid='.$data['recent_communication_unique_id']);

    $tracking_url =$ci->Communication_dashboard_model->get_short_url($tracking_url);

    $message_body = str_replace("{{WEBSITE_URL}}", $tracking_url, $message_body);
    send_promotion_email($data,$message_body);
}

function contest_promotion_notify($data)
{
     $ci = & get_instance();
    $ci->load->model('Communication_dashboard_model');
    $contest_data = $data['content']['template_data'];
    $message_body = str_replace("{{contest_name}}", $contest_data['contest_name'], $data['content']['email_body']);

    //prepare url for contest 

    $contest_url = WEBSITE_URL.$contest_data['sports_name'].'/contest/'.$contest_data['contest_unique_id'].'?rcuid='.$data['recent_communication_unique_id'];

    $tracking_url =TRACKING_URL.'?rcuid='.$data['recent_communication_unique_id'].'&uid='.$data['content']['user_id'].'&pn='.TRACKING_PROJECT.'&type=email_click&redirect_url='.urlencode($contest_url);

    $tracking_url =$ci->Communication_dashboard_model->get_short_url($tracking_url);

    $message_body = str_replace("{{WEBSITE_URL}}", $tracking_url, $message_body);
    //$message_body =$data['content']['email_body'];
    send_promotion_email($data,$message_body);
}

function send_common_email($data)
{
    send_email($data['email'], $data['subject'],$data['content']['email_body'] );
}


function send_promotion_email($data,$message_body)
{
      $message_body = str_replace("{{SITE_TITLE}}", SITE_TITLE, $message_body);
      $message_body = str_replace("{{year}}", date('Y'), $message_body);
      $message_body = str_replace("{{BUCKET_URL}}", IMAGE_PATH, $message_body);
      $message_body = str_replace("{{SITE_URL}}", WEBSITE_URL, $message_body);
      $message_body = str_replace("{{WEBSITE_DOMAIN}}", WEBSITE_DOMAIN, $message_body);
      
      $data['subject'] = str_replace("{{SITE_TITLE}}", '['.SITE_TITLE.']', $data['subject']);

      $message_body .= '<img src="'.TRACKING_URL.'?rcuid='.$data['recent_communication_unique_id'].'&uid='.$data['content']['user_id'].'&pn='.TRACKING_PROJECT.'&type=email" width="1" height="1" />';

        $ci = & get_instance();
        $ci->load->model('Communication_dashboard_model');   

       $email = $ci->Communication_dashboard_model->get_email_history_record($data);

       if(empty($email))
        {
            exit();
        }

      $send_email_response = send_email($data['email'], $data['subject'],$message_body );


        if($send_email_response)
        {
            $ci->Communication_dashboard_model->update_email_history_status($data,1); 
        
            //count for the recent_communication_id will be sent email for this campgain

            $traking_data_status = get_data_by_curl(TRACKING_SERVER_URL.'make_tracking_entry',array(
                    "pn" => TRACKING_PROJECT,
                    'recent_communication_unique_id' => $data['recent_communication_unique_id'],
                    'user_id' => $data['content']['user_id'],
                    'autk_token' => TRACKING_AUTH_KEY
                ));
        }
        else
        {
            $ci->Communication_dashboard_model->update_email_history_status($value,2); //failed
            $ci->Communication_dashboard_model->refund_email_balance($sms_count);//refund balance
        }
}

/* End of file mnotificationque.php */
/* Location: ./application/controllers/mnotificationque.php */