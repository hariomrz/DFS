<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Cd_worker extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        ini_set('memory_limit', '-1');
    }
    
    public function index() {
        
    }  

    public function bulk_email_process() {
        $queue_name = CD_BULK_EMAIL_QUEUE;
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            $emails = json_decode($msg->body, TRUE);  
            try{
                if(!empty($emails))
    		    {
    		        $connection2 = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
    		        $channel2 = $connection2->channel();

    		        foreach ($emails as $email_data) {
    		            $push_data = json_encode($email_data);
    		            $message = new AMQPMessage($push_data, array('delivery_mode' => 1, 'content_type' => 'application/json')); # make message persistent as 2
    		            $channel2->basic_publish($message,'', CD_EMAIL_QUEUE);
    		         }

    		        $channel2->close();
    		        $connection2->close();
    		    }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    public function email_queue_process() {
    	$queue_name = CD_EMAIL_QUEUE;
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
        	 //notification action maping
		    $notification_action_map = array();
		    $notification_action_map[120] ="deposit_promotion_notify" ;
		    $notification_action_map[121] ="contest_promotion_notify" ;
		    $notification_action_map[123] ="promotion_notify" ;
		    $notification_action_map[300] ="fixture_promotion_notify" ;//fixture promotion
		    $notification_action_map[127] ="send_common_email" ;
		    $notification_action_map[128] ="send_common_email" ;
		    $notification_action_map[129] ="send_common_email" ;
		    $notification_action_map[131] ="send_match_delay_email" ;
            $notification_action_map[132] ="send_lineup_announced_email" ;
            $notification_action_map[302] ="send_common_email" ;
		    
		    $data = json_decode($msg->body, TRUE);
		    if (!is_array($data['content'])) {
		        $data['content'] = json_decode($data['content'], TRUE);
		    }

            try{
    		    if (!empty($data)) {

                    $this->load->model('Nodb_model');
                    $email_template_cache = 'cron_email_template';
                    $email_temp = $this->Nodb_model->get_cache_data($email_template_cache);
                    if(!$email_temp){
                        $this->load->model('Cron_model');
                        $email_temp = $this->Cron_model->get_email_template_list();
                        $this->Nodb_model->set_cache_data($email_template_cache,$email_temp,REDIS_30_DAYS);
                    }

    		        $notification_type = array_column($email_temp, 'notification_type');

    		        $template_index = array_search($data['notification_type'],$notification_type);

    		        if($template_index !== false)
    		        {
    		            if($email_temp[$template_index]['status'] == 0)
    		            {
    		                $data['notification_type'] = 1000;
    		            }
    		        }

                   $func_name = $notification_action_map[$data['notification_type']];
                  
    		        $this->$func_name($data);

    		    }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

    		$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    function push_queue_process()
    {
    	$queue_name = 'cd_push';
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C push queue '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
        	//notification action maping
	    $notification_action_map = array();
	    $notification_action_map[120] ="push_deposit_promotion_notify" ;
	    $notification_action_map[121] ="push_contest_promotion_notify" ;
	    $notification_action_map[123] ="push_promotion_notify" ;
	    $notification_action_map[300] ="push_fixture_promotion_notify" ;//fixture promotion
	    $notification_action_map[127] ="push_send_common_email" ;
	    $notification_action_map[128] ="push_send_common_email" ;
	    $notification_action_map[129] ="push_send_common_email" ;
	    $notification_action_map[131] ="push_send_match_delay_email" ;
        $notification_action_map[132] ="push_send_lineup_announced_email" ;
        $notification_action_map[434] ="deal_detail" ;
        $notification_action_map[435] ="new_promocode_detail" ;
        $notification_action_map[301] ="admin_daily_earn_coin" ;
        $notification_title="";
	  	$data = json_decode($msg->body, TRUE);
         
        try{
            if(!empty($data))
    	  	{ 
                $android_device_ids = array();
                $ios_device_ids = array();

                foreach($data['push_notification_data'] as $key => $data_item)
                {

                    foreach($data_item['device_details'] as $device_detail )
                    {
                        if(isset($device_detail['device_type']) && $device_detail['device_type']=='1' )
                        {
                            $android_device_ids[] = $device_detail['device_id'];
                        }

                        if(isset($device_detail['device_type']) && $device_detail['device_type']=='2' )
                        {
                            $ios_device_ids[] = $device_detail['device_id'];
                        }
                    }
                   
                    if (isset($data_item['content']))
                    {
                        if (isset($data_item['content']['template_data']))
                        {
                            if($data['push_notification_data'][$key]['notification_type']==434 || $data['push_notification_data'][$key]['notification_type']==435)
                            {
                            unset($data['push_notification_data'][$key]['content']['template_data']);
                            $data['push_notification_data'][$key]['content']['template_data']=array();
                            $data['push_notification_data'][$key]['content']['template_data']['redirect_to'] = $data['push_notification_data'][$key]['redirect_to'];
                            }
                            $data['push_notification_data'][$key]['content']['template_data']['recent_communication_unique_id'] = $data['recent_communication_unique_id'];
                        }
                    }
                    $data['push_notification_data'][$key]['content'] = json_encode($data['push_notification_data'][$key]['content']);
                }
               
              
    			$notification_data = $data['push_notification_data'][0];
    			$notification_type=0;
    			$message = "";

    			if(isset($notification_data['content']))
    			{
    				$content = json_decode($notification_data['content'], TRUE);
    			}
    			if(isset($notification_data['notification_type']))
    			{
    				$notification_type = $notification_data['notification_type'];
    			}
                if(!empty($content['template_data']['notification_landing_page'])){
                    $content['template_data']['notification_landing_page'] = $content['template_data']['notification_landing_page'];
                }
    			$this->load->model('Communication_dashboard_model');
    		      //get notification descrition
    			$noti_data = $this->Communication_dashboard_model->get_notification_description( $notification_type);
                $notification_title = $noti_data['message'];
                if($notification_type == 135 || $notification_type == 440 || $notification_type == 441)
                {
                    $notification_title = $content['custom_notification_subject'];
                    $message=  $content['custom_notification_text'];

                    if(!empty($content["template_data"]))
                    {
                        $content["template_data"]['custom_notification_type'] = $content["template_data"]['custom_notification_landing_page'];
                    }
                } elseif($notification_type == 0)
                {
                    if(!empty($content['custom_notification_text'])){
                    $notification_title = $content['custom_notification_subject'];
                    $message=  $content['custom_notification_text'];
                    }
                    else{
                        $message = $content['notification_text'];
                        $notification_title = $content['notification_subject'];
                    }

                    if(!empty($content["template_data"]))
                    {
                        $content["template_data"]['custom_notification_type'] = $content["template_data"]['custom_notification_landing_page'];
                    }
                }   
                else{
                    $message = $content['notification_text'];
                    $notification_title = $content['notification_subject'];
                    if($notification_type!=434){
                    $func_name = $notification_action_map[(int)$notification_type];
                    $message= $this->$func_name($message ,$content);
                    }
                } 
             
    		      if(empty($content['template_data']))
    		      {
    		        $content['template_data'] = array();
    		      }

    	      		$content['template_data']['notification_type'] = $notification_type;

    		  	$fields = array();
    				unset($data['deviceIDS']);

    		    if($notification_type ==121)//for contest 
    		    {
    		       $contest_data = array();
    		       $contest_data['contest_unique_id'] = $content['template_data']['contest_unique_id'];
    		       $contest_data['sports_id'] = $content['template_data']['sports_id'];
                   $contest_data['notification_type'] = $content['template_data']['notification_type'];
                   $contest_data['header_image'] = !empty($content['template_data']['header_image'])?$content['template_data']['header_image']:"";
    		       $contest_data['body_image'] = !empty($content['template_data']['body_image'])?$content['template_data']['body_image']:"";
    		       $contest_data['ios_body_image'] = !empty($content['template_data']['ios_body_image'])?$content['template_data']['ios_body_image']:"";
    		       $contest_data['redirect_to'] = !empty($content['template_data']['redirect_to'])?$content['template_data']['redirect_to']:1 ;
    		       $content['template_data'] = array();
    		       $content['template_data'] =  $contest_data;
                }
              
                //$registatoin_ids = array(), $title = '', $message = '', $badge = 1, $Data
                $this->load->library('push_notification');
                //echo '<pre>';var_dump($android_device_ids);var_dump($ios_device_ids);echo $notification_title;echo '#message#'.$message;print_R($content);die('dfd');
                if(!empty($android_device_ids))
                {
                    $result = $this->push_notification->push_notification_android($android_device_ids,$notification_title,$message,1,$content['template_data'] );
                }
                
                if(!empty($ios_device_ids ))
                {
                    $result = $this->push_notification->push_notification_ios($ios_device_ids,$notification_title,$message,'1',$content['template_data'] );
                }
    	  	}
        }
        //catch exception
        catch(Exception $e) 
        {
          return false;
        }
  		$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }


    function push_contest_promotion_notify($message,$data)
	{
	    $contest_data = $data['template_data'];
	    $message = str_replace("{{contest_name}}", $contest_data['contest_name'], $message);
	    $message = str_replace("{{collection_name}}", $contest_data['collection_name'], $message);
	   	return $message;
	}

	function push_deposit_promotion_notify($message,$data)
	{
	    // $promocode_data = $data['template_data'];
	    // $message = str_replace("{{promo_code}}", $promocode_data['promo_code'], $message);
	    // $message = str_replace("{{offer_percentage}}", $promocode_data['discount'] , $message);
	   	return $message;
	}

	function push_send_lineup_announced_email($message,$data)
	{ 
	    $match_data = $data['template_data'];
	    $message = str_replace("{{home}}", $match_data['home'], $message);
	    $message = str_replace("{{away}}", $match_data['away'], $message);
	    $message = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message);
	    //$message = str_replace("{{user_name}}", $data['content']['user_name'], $message);
	    $message = str_replace("{{collection_name}}", $match_data['collection_name'], $message);
	    return $message;  
	}

	function push_send_match_delay_email($message,$data)
	{
	    $match_data = $data['template_data'];
	     $message = str_replace("{{home}}", $match_data['home'], $message);
	    $message = str_replace("{{away}}", $match_data['away'], $message);
	    $message = str_replace("{{collection_name}}", $match_data['collection_name'], $message);
	    $message = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message);
	    $message = str_replace("{{MINUTES}}", $match_data['delay_minute'], $message);
	    return $message;  
	}

	function push_fixture_promotion_notify($message,$data)
	{
	    $match_data = $data['template_data'];
	    $message = str_replace("{{home}}", $match_data['home'], $message);
	    $message = str_replace("{{away}}", $match_data['away'], $message);
	    $message = str_replace("{{home_flag}}", $match_data['home_flag'], $message);
	    $message = str_replace("{{away_flag}}", $match_data['away_flag'], $message);
	    $message = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message);
		return $message;  
	}

    function push_promotion_notify($message,$data)
	{
	    $template_data = $data['template_data'];
	    $message = str_replace("{{CURRENCY_CODE_HTML}}", CURRENCY_CODE_HTML, $message);
	    $message = str_replace("{{amount}}", $template_data['bonus_amount'], $message);
	     $message = str_replace("{{SITE_TITLE}}", SITE_TITLE, $message);
		return $message;           
    }
    
    function deal_detail($message,$data)
    {
    return $message;
    }

    function new_promocode_detail($message,$data)
    {
    return $message;
    }

    function admin_daily_earn_coin($message,$data)
    {
        return $message;
    }


    function promotion_notify($data)
	{
	    $this->load->model('Communication_dashboard_model');
	    $message_body = str_replace("{{user_name}}", $data['content']['user_name'], $data['content']['email_body']);

	    $template_data = $data['content']['template_data'];

	    $message_body = str_replace("{{CURRENCY_CODE_HTML}}", CURRENCY_CODE_HTML, $message_body);
	    $message_body = str_replace("{{amount}}", $template_data['bonus_amount'], $message_body);

	    $referral_url =$this->Communication_dashboard_model->get_short_url($data['tracking_url']);

        $tracking_url =TRACKING_URL.'?rcuid='.$data['recent_communication_unique_id'].'&uid='.$data['content']['user_id'].'&pn='.TRACKING_PROJECT.'&type=email_click&redirect_url='.urlencode(WEBSITE_URL.'?rcuid='.$data['recent_communication_unique_id']);
        $message_body = str_replace("{{WEBSITE_URL}}", $tracking_url, $message_body);

	    $message_body = str_replace("{{WEBSITE_REFER_URL}}",$referral_url, $message_body);
	    $this->send_promotion_email($data,$message_body);      
	}

function fixture_promotion_notify($data)
{
    $message_body = str_replace("{{WEBSITE_URL}}", $data['tracking_url'], $data['content']['email_body']);
    
    $this->send_promotion_email($data,$message_body);
}

function send_match_delay_email($data)
{
    $this->load->model('Communication_dashboard_model');
    $match_data = $data['content']['template_data'];
    $message_body = str_replace("{{collection_name}}", $match_data['collection_name'], $data['content']['email_body']);
    $message_body = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message_body);
    $message_body = str_replace("{{user_name}}", $data['content']['user_name'], $message_body);
    $message_body = str_replace("{{WEBSITE_URL}}", WEBSITE_URL, $message_body);
    $message_body = str_replace("{{WEBSITE_DOMAIN}}", WEBSITE_DOMAIN, $message_body);
    $message_body = str_replace("{{MINUTES}}", $match_data['delay_minute'], $message_body);
    $message_body = str_replace("{{LOBBY_URL}}", $data['tracking_url'], $message_body);
    $message_body = str_replace("{{FB_LINK}}", FB_LINK, $message_body);
    $message_body = str_replace("{{TWITTER_LINK}}", TWITTER_LINK, $message_body);
    $message_body = str_replace("{{INSTAGRAM_LINK}}", INSTAGRAM_LINK, $message_body);
    

    $data['subject'] = str_replace("{{collection_name}}", $match_data['collection_name'], $data['subject']);
    $this->send_promotion_email($data,$message_body);
}

function send_lineup_announced_email($data)
{
    $this->load->model('Communication_dashboard_model');
    $match_data = $data['content']['template_data'];
    $message_body = str_replace("{{home}}", $match_data['home'], $data['content']['email_body']);
    $message_body = str_replace("{{away}}", $match_data['away'], $message_body);
    $message_body = str_replace("{{home_flag}}", $match_data['home_flag'], $message_body);
    $message_body = str_replace("{{away_flag}}", $match_data['away_flag'], $message_body);
    $message_body = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message_body);
    
    $match_str =strtolower($match_data['home']).'-vs-'.strtolower($match_data['away']).'-'.date('d-m-Y',strtotime($match_data['season_scheduled_date'])).'?rcuid='.$data['recent_communication_unique_id'];
    $message_body = str_replace("{{user_name}}", $data['content']['user_name'], $message_body);
    $message_body = str_replace("{{WEBSITE_URL}}", WEBSITE_URL, $message_body);
    //$message_body = str_replace("{{WEBSITE_DOMAIN}}", WEBSITE_DOMAIN, $message_body);
    $message_body = str_replace("{{MY_CONTEST_URL}}", $data['tracking_url'], $message_body);
    $message_body = str_replace("{{FB_LINK}}", FB_LINK, $message_body);
    $message_body = str_replace("{{TWITTER_LINK}}", TWITTER_LINK, $message_body);
    $message_body = str_replace("{{INSTAGRAM_LINK}}", INSTAGRAM_LINK, $message_body);
    $message_body = str_replace("{{collection_name}}", $match_data['collection_name'], $message_body);
    
    $data['subject'] = str_replace("{{collection_name}}", $match_data['collection_name'], $data['subject']);
    $this->send_promotion_email($data,$message_body);
}

function deposit_promotion_notify($data)
{
     
    $this->load->model('Communication_dashboard_model');
    $promocode_data = $data['content']['template_data'];
    $message_body = str_replace("{{promo_code}}", $promocode_data['promo_code'], $data['content']['email_body']);
    $message_body = str_replace("{{offer_percentage}}", $promocode_data['discount'] , $message_body);

    $tracking_url =TRACKING_URL.'?rcuid='.$data['recent_communication_unique_id'].'&uid='.$data['content']['user_id'].'&pn='.TRACKING_PROJECT.'&type=email_click&redirect_url='.urlencode(WEBSITE_URL.'?rcuid='.$data['recent_communication_unique_id']);

    $message_body = str_replace("{{WEBSITE_URL}}", $data['tracking_url'], $message_body);
    $this->send_promotion_email($data,$message_body);
}

function contest_promotion_notify($data)
{
     $ci = & get_instance();
    $ci->load->model('Communication_dashboard_model');
    $contest_data = $data['content']['template_data'];
    $message_body = str_replace("{{contest_name}}", $contest_data['contest_name'], $data['content']['email_body']);
    $message_body = str_replace("{{WEBSITE_URL}}", $data['tracking_url'], $message_body);
    //$message_body =$data['content']['email_body'];
    $this->send_promotion_email($data,$message_body);
}

function send_common_email($data)
{
    $this->Nodb_model->send_email_cd($data['email'], $data['subject'],$data['content']['email_body'] );
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

        $this->load->model('Communication_dashboard_model');   

      $send_email_response = $this->Nodb_model->send_email_cd($data['email'], $data['subject'],$message_body );
        
}

        public function get_user_base_list(){
            $this->load->model('Communication_dashboard_model');
        $lists = $this->Communication_dashboard_model->get_user_base_list();
    }


     public function get_user_base_count(){
        $this->load->model("Communication_dashboard_model","CDM");
        $user_base_list = $this->CDM->get_user_base_list();
        foreach($user_base_list as $key=>$list){
            $list['sport_id']= json_decode($list['sport_id'],true);
            $list['location']= json_decode($list['location'],true);
            $list['age_group']= json_decode($list['age_group'],true);
            $list['profile_status']= json_decode($list['profile_status'],true);
            $list['gender']= json_decode($list['gender'],true);
            $list['admin_created_contest_join']= json_decode($list['admin_created_contest_join'],true);
            $list['admin_created_contest_won']= json_decode($list['admin_created_contest_won'],true);
            $list['admin_created_contest_lost']= json_decode($list['admin_created_contest_lost'],true);
            $list['private_contest_join']= json_decode($list['private_contest_join'],true);
            $list['private_contest_won']= json_decode($list['private_contest_won'],true);
            $list['private_contest_lost']= json_decode($list['private_contest_lost'],true);
            $list['money_deposit']= json_decode($list['money_deposit'],true);
            $list['money_won']= json_decode($list['money_won'],true);
            $list['money_lost']= json_decode($list['money_lost'],true);
            $list['coin_earn']= json_decode($list['coin_earn'],true);
            $list['coin_lost']= json_decode($list['coin_lost'],true);
            $list['coin_redeem']= json_decode($list['coin_redeem'],true);
            $list['referral']= json_decode($list['referral'],true);

            $update_count = $this->CDM->get_user_base_count($list);
            $filter_user_ids = $this->CDM->filter_system_users($update_count);
            if(empty($filter_user_ids['user_ids'])){
                $filter_user_ids['user_ids']='';
            }
            $update = $this->CDM->update_user_base_list($filter_user_ids);

        }
        echo "updated successfully";exit;
    }

    function delete_deduct_balance_history()
    {
       
        $current_date = format_date();
        $before_one_month_date = date('Y-m-d',strtotime($current_date.' -1 month'));
        $this->load->model("Communication_dashboard_model","CDM");
        $this->CDM->delete_deduct_balance_history($before_one_month_date);
        exit();
    } 

    public function normal_push_queueu_process()
    {
        $queue_name = CD_NORMAL_PUSH_QUEUE;
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
                    $data = json_decode($msg->body, TRUE);
            try{
                if (!empty($data)) {
                    $this->load->helper('queue_helper');
                    add_data_in_queue($data, CD_PUSH_QUEUE);
                    }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;

    }

    public function scheduled_push_queue_process()
    {
        $queue_name = CD_SCHEDULED_PUSH_QUEUE;
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
                    $data = json_decode($msg->body, TRUE);
            try{
                if (!empty($data)) {
                    $this->load->model('cron_model');
                    $this->cron_model->update_scheduler_status($data['recent_communication_unique_id']);
                    $this->load->helper('queue_helper');
                    add_data_in_queue($data, CD_PUSH_QUEUE);
                    }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;

    }
}