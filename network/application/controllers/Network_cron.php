<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Network_cron extends MY_Controller
{
   
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('queue');
        $this->server_name = get_server_host_name();
    }

    public function index()
    {
        echo "Welcome";die();
    }

    public function update_nw_contest_status(){
        $this->benchmark->mark('code_start');
        
        $this->load->model('Network_cron_model');
        $this->Network_cron_model->update_nw_contest_status();
        
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function update_nw_contest_status_by_id($contest_id="")
    {

        if(empty($contest_id))
        {
            exit("Invalid request!");
        }

        $post_data['client_id']  = NETWORK_CLIENT_ID;
        $post_data['contest_id'] = $contest_id;
        // echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_detail";
        $api_response =  $this->http_post_request($url,$post_data);
          $master_response = $this->master_response_array($api_response);
          //echo "<pre>";print_r($master_response);die;
          if(!empty($master_response['data']['contest_id']))
          {

            $contest_details = $master_response['data'];
            $contest_update_array = array(

                "status"                => $contest_details['status'],
                "season_scheduled_date" => $contest_details['season_scheduled_date'],
                "date_added"            => format_date()
            );

            $where_array = array(

                "network_collection_master_id" => $contest_details['collection_master_id'],
                "network_contest_id"           => $contest_id
            ); 


            $this->load->model("Network_cron_model");
            $this->Network_cron_model->update_network_contest($contest_update_array,$where_array);
            echo "<br>Contest status updated <br>";    
        }
        exit("Cron processed!");
    }

    public function nw_contest_prize_distribution(){
        $this->benchmark->mark('code_start');
        $this->load->model('Network_cron_model');
        $this->Network_cron_model->nw_contest_prize_distribution();
        
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function nw_contest_prize_distribution_by_id($contest_id="")
    {

        if(empty($contest_id))
        {
            exit("Invalid request!");
        }

        $where_condition = array(
          "status"               => 3,
          "is_prize_distributed" => 0,
          "network_contest_id" => $contest_id,
          "season_scheduled_date < "=> format_date()
       );

        
       $this->load->model('Network_cron_model'); 

       $completed_game = $this->Network_cron_model->get_single_nw_contest($where_condition);
       if(empty($completed_game))
       {
            exit("Contest not found!");
       }
       //echo "<pre>";print_r($completed_game['contest_details']);die;
       $post_data['client_id']  = NETWORK_CLIENT_ID;
       $post_data['contest_id'] = $contest_id;
        // echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_prize_distribution_detail";
        $api_response =  $this->http_post_request($url,$post_data);
        $master_response = $this->master_response_array($api_response);
        //echo "<pre>";print_r($master_response);die;
        if(empty($master_response['data']['contest_detail']) || empty($master_response['data']['prize_distribution_details']))
        {
            $update_nw_contest_arr = array(

                "is_prize_distributed" => 1,
                "is_win_notify"        => 1, 
                "date_added"           => format_date()
            );

            $nw_contest_condition = array(
                "network_contest_id"          => $completed_game['network_contest_id'],
                "network_collection_master_id"=> $completed_game['network_collection_master_id'],
                "status"                      => 3,
                "is_prize_distributed"        => 0,
                "active"                      => 1,
            );

            //update network contest entry
            $this->Network_cron_model->update_network_contest($update_nw_contest_arr,$nw_contest_condition);

            exit("Prize distribution details not found from Master server!");
        }

        $contest_detail = $master_response['data']['contest_detail'];
        $prize_detail   = $master_response['data']['prize_distribution_details'];
        if(!is_array($prize_detail) || count($prize_detail) <= 0)
        {
            exit("Prize distribution details not found from Master server");
        }

        $updated_count = 0;
        foreach ($prize_detail as $pkey => $pvalue) 
        {
            //echo "<pre>";print_r($pvalue);continue;die("in pvalue");
            if(empty($pvalue['user_id']) || empty($pvalue['lineup_master_contest_id']) || !isset($pvalue['amount'])
                || !isset($pvalue['prize_data']))
            {
                continue;
            }

            $prize_data = json_decode($pvalue['prize_data'],TRUE);

            //echo "<pre>";print_r($prize_data);continue;die;
            $prize_image = null;
            if(isset($prize_data[0]['prize_type']) && $prize_data[0]['prize_type'] == 3)
            {
                $prize_image = $prize_data[0]['image'];
            }    

            //echo "<pre>";print_r($pvalue);continue;die("in pvalue");

            //check if already credited amount 
            $condition = array(
                "source"       => NW_WON_GAME_SOURCE,
                "user_id"      => $pvalue['user_id'],
                "source_id"    => $pvalue['lineup_master_contest_id'],
                "season_type"  => 1,
            );

            //echo "<pre>";print_r($condition);die("in pvalue");

            $order_info = $this->Network_cron_model->get_order_detail_by_condition($condition);
            //echo "<pre>";print_r($order_info);die("in order value");

            if(!empty($order_info))
            {
                continue;
            }  

            /*if($pvalue['amount'] <= 0 || !isset($pvalue['prize_data']))
            {
                continue;
            } */

            $new_custom_data = json_decode($completed_game['contest_details'],TRUE);
            $prize_data[0]["contest_name"] = $new_custom_data["contest_name"];
            //array_push($prize_data,array("contest_name"=>$new_custom_data['contest_name']));
            //echo "<pre>";print_r($prize_data);continue;die("custom_data");

            $cash_type = 0;
            $order_params = array(
                                    'user_id' => $pvalue['user_id'],
                                    'amount' => $pvalue['amount'],
                                    'cash_type' => $cash_type,
                                    'contest_id' => (isset($pvalue['contest_id'])) ? $pvalue['contest_id'] : $contest_detail['contest_id'] ,
                                    'plateform' => NW_FANTASY_PLATEFORM,
                                    'source' => NW_WON_GAME_SOURCE,
                                    'source_id' => $pvalue['lineup_master_contest_id'],
                                    'reference_id' => $contest_id,
                                    'reason' => NW_PRIZE_MONEY_NOTI,
                                    'season_type' => 1,
                                    'ignore_deposit_noty' => 1,
                                    'custom_data'     => json_encode($prize_data),
                                    'prize_image' => $prize_image
                                );
            //echo "<pre>";print_r($order_params);continue;die("in order params");
                    
            $is_deposited = $this->Network_cron_model->deposit($order_params); 
            if(!empty($is_deposited))
            {
                $updated_count++;
            }        
        }

        //update prize distribution flag in network contest db table.
        if(!empty($updated_count))
        {
            $update_nw_contest_arr = array(

                "is_prize_distributed" => 1,
                "date_added"           => format_date()
            );

            $nw_contest_condition = array(
                "network_contest_id"          => $completed_game['network_contest_id'],
                "network_collection_master_id"=> $completed_game['network_collection_master_id'],
                "status"                      => 3,
                "is_prize_distributed"        => 0,
                "active"                      => 1,
            );

            //update network contest entry
            $this->Network_cron_model->update_network_contest($update_nw_contest_arr,$nw_contest_condition);
            //send notification
            $this->send_nw_contest_notifications_by_id($contest_id);
        }    

        exit("Cron processed!");
    }

    public function nw_contest_cancellation(){
        $this->benchmark->mark('code_start');
        
        $this->load->model('Network_cron_model');
        $this->Network_cron_model->nw_contest_cancellation();
        
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function cancel_nw_contest_by_id($contest_id="")
    {

        if(empty($contest_id))
        {
            exit("Invalid request!");
        }

        $where_condition = array(
          "status"               => 1,
          "is_fee_refunded"      => 0,
          "network_contest_id"   => $contest_id,
          //"season_scheduled_date < "=> format_date()
       );
       $this->load->model('Network_cron_model'); 

       $cancelled_game = $this->Network_cron_model->get_single_nw_contest($where_condition);
       if(empty($cancelled_game))
       {
            exit("Contest not found!");
       }
       //echo "<pre>";print_r($cancelled_game['contest_details']);die;
       $post_data['client_id']  = NETWORK_CLIENT_ID;
       $post_data['contest_id'] = $contest_id;
        // echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_cancellation_detail";
        $api_response =  $this->http_post_request($url,$post_data);
        $master_response = $this->master_response_array($api_response);
         
        if(empty($master_response['data']['contest_detail']) || empty($master_response['data']['contest_cancellation_details']))
        {
           //echo "<pre>";print_r($master_response['data']['contest_cancellation_details']);die;
            $update_nw_contest_arr = array(

                "is_fee_refunded"      => 1,
                "is_win_notify"        => 1, 
                "date_added"           => format_date()
            );

            $nw_contest_condition = array(
                "network_contest_id"          => $cancelled_game['network_contest_id'],
                "network_collection_master_id"=> $cancelled_game['network_collection_master_id'],
                "status"                      => 1,
                "is_fee_refunded"             => 0,
                "active"                      => 1,
            );

            //update network contest entry
            $this->Network_cron_model->update_network_contest($update_nw_contest_arr,$nw_contest_condition);
            exit("Contest refund details not found from Master server!");
        }

        $contest_detail = $master_response['data']['contest_detail'];
        $refund_detail   = $master_response['data']['contest_cancellation_details'];
        //echo "<pre>";print_r($refund_detail);die;
        if(!is_array($refund_detail) || count($refund_detail) <= 0)
        {
            exit("Contest refund details not found from Master server");
        }

        $updated_count = 0;
        foreach ($refund_detail as $pkey => $pvalue) 
        {
            
            if(empty($pvalue['user_id']) || empty($pvalue['lineup_master_contest_id']) || !isset($pvalue['amount']))
            {
                continue;
            }


            //check if already refunded amount 
            $condition = array(
                "source"       => NW_GAME_CANCEL_SOURCE,
                "user_id"      => $pvalue['user_id'],
                "source_id"    => $pvalue['lineup_master_contest_id'],
                "season_type"  => 1
            );
            $order_info = $this->Network_cron_model->get_order_detail_by_condition($condition);
            
            //echo "123<pre>";print_r($order_info);die;
            if(!empty($order_info))
            {
                continue;
            }  

            //get join game entry for the user
            $join_condition = array(
                "source"       => NW_JOIN_GAME_SOURCE,
                "user_id"      => $pvalue['user_id'],
                "source_id"    => $pvalue['lineup_master_contest_id'],
                "season_type"  => 1,
                "status"       => 1,
            );
            //echo "<pre>";print_r($join_condition);die;
            $join_order_info = $this->Network_cron_model->get_order_detail_by_condition($join_condition);
            
            //echo "<pre>";print_r($join_order_info);die;
            if(empty($join_order_info))
            {
                continue;
            }

            $current_date = format_date(); 
            $orderData    = array();  
            $orderData["user_id"]        = $join_order_info["user_id"];
            $orderData["source"]         = NW_GAME_CANCEL_SOURCE;
            $orderData["source_id"]      = $join_order_info["source_id"];
            $orderData["reference_id"]   = $contest_id;
            $orderData["season_type"]    = $join_order_info["season_type"];
            /* type                      = 0 For creadit amount */
            $orderData["type"]           = 0;
            $orderData["date_added"]     = $current_date;
            $orderData["modified_date"]  = $current_date;
            $orderData["plateform"]      = $join_order_info["plateform"];
            $orderData["status"]         = 1;
            $orderData["real_amount"]    = $join_order_info["real_amount"];
            $orderData["bonus_amount"]   = $join_order_info["bonus_amount"];
            $orderData["winning_amount"] = $join_order_info["winning_amount"];
            $orderData["points"]         = $join_order_info["points"];
            $orderData["custom_data"]    = $cancelled_game['contest_details'];   
            $orderData['order_unique_id'] = $this->Network_cron_model->_generate_order_key(); 

            
                    
            $is_created = $this->Network_cron_model->create_refund_order($orderData); 
            if(!empty($is_created))
            {
                $updated_count++;

                $balance_updated = $this->Network_cron_model->update_user_balance($orderData["user_id"], $orderData, "add");
                if($balance_updated)
                {

                    $user_detail = $this->Network_cron_model->get_user_detail_with_balance($join_order_info["user_id"]);
                    if(!empty($user_detail))
                    {

                        $notify_data = array();
                        $notify_data['notification_type'] = (empty($contest_detail['season_cancel_status'])) ? NW_GAME_CANCEL_NOTI_TYPE : NW_GAME_ABANDONED_NOTI_TYPE; //243-GameAbandoned
                        $notify_data['notification_destination'] = 7; //Web,Push,Email
                        $notify_data["source_id"] = 1;
                        $notify_data["user_id"] = $user_detail['user_id'];
                        $notify_data["to"] = $user_detail['email'];
                        $notify_data["user_name"] = $user_detail['user_name'];
                        $notify_data["added_date"] = $current_date;
                        $notify_data["modified_date"] = $current_date;
                        $notify_data["subject"] = (empty($contest_detail['season_cancel_status'])) ? $contest_detail['contest_name'].' Sorry! Your room did not fill up :(' : "Oops! Match Cancelled!"; 
                        
                        
 
                        $content = array(
                            'contest_id'       => $contest_detail['contest_id'],
                            'contest_unique_id'=> $contest_detail['contest_unique_id'],
                            'contest_name'     => $contest_detail['contest_name'],
                            'size'             => $contest_detail['size'],
                            'entry_fee'        => $contest_detail['entry_fee'],
                            'prize_pool'       => $contest_detail['prize_pool'],                      
                            'prize_type'        => $contest_detail['prize_type'],
                            'collection_name'   => (isset($contest_detail['collection_name'])) ? $contest_detail['collection_name'] : '',
                            'cancel_reason'     => '',
                            'season_scheduled_date' => $contest_detail['season_scheduled_date'],
                        );
 
                        $notify_data["content"] = json_encode($content);

                        try {
                            $this->load->model('user/User_nosql_model');
                            $this->User_nosql_model->send_notification($notify_data); 
                        } catch(Exception $e) {
                            //echo 'Message: ' .$e->getMessage();
                        }
                    }//user detail if end.    
                }//balance updated if end.    
            }        
        }

        //update fee refunded flag in network contest db table.
        if(!empty($updated_count))
        {
            $update_nw_contest_arr = array(

                "is_fee_refunded"      => 1,
                "is_win_notify"      => 1,
                "date_added"           => format_date()
            );

            $nw_contest_condition = array(
                "network_contest_id"          => $cancelled_game['network_contest_id'],
                "network_collection_master_id"=> $cancelled_game['network_collection_master_id'],
                "status"                      => 1,
                "is_fee_refunded"             => 0,
                "active"                      => 1,
            );

            //update network contest entry
            $this->Network_cron_model->update_network_contest($update_nw_contest_arr,$nw_contest_condition);
        }    

        exit("Cron processed..");
    }

    public function nw_contest_notification(){
        $this->benchmark->mark('code_start');
        
        $this->load->model('Network_cron_model');
        $this->Network_cron_model->nw_contest_notification();
        
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function send_nw_contest_notifications_by_id($contest_id="")
    {

        if(empty($contest_id))
        {
            exit("Invalid request!");
        }

        $where_condition = array(
          "status"               => 3,
          "is_prize_distributed" => 1,
          "is_win_notify"        => 0,
          "network_contest_id"   => $contest_id,
          "season_scheduled_date < "=> format_date()
       );
       $this->load->model('Network_cron_model'); 

       $completed_game = $this->Network_cron_model->get_single_nw_contest($where_condition);
       if(empty($completed_game))
       {
            exit("Contest not found!");
       }
       //echo "<pre>";print_r($completed_game);die;
       $post_data['client_id']  = NETWORK_CLIENT_ID;
       $post_data['contest_id'] = $contest_id;
        // echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_notification_detail";
        $api_response =  $this->http_post_request($url,$post_data);
        $master_response = $this->master_response_array($api_response);
       //echo "<pre>";print_r($master_response);die;
        if(empty($master_response['data']['contest_detail']) || empty($master_response['data']['contest_notification_details']))
        {
            $update_nw_contest_arr = array(

                    "is_win_notify"      => 1,
                    "date_added"         => format_date()
                );

                $nw_contest_condition = array(
                    "network_contest_id"          => $completed_game['network_contest_id'],
                    "network_collection_master_id"=> $completed_game['network_collection_master_id'],
                    "status"                      => 3,
                    "is_win_notify"               => 0,
                    "is_prize_distributed"        => 1,
                    "active"                      => 1,
                );

                //update network contest entry
                $this->Network_cron_model->update_network_contest($update_nw_contest_arr,$nw_contest_condition);
            exit("Contest refund details not found from Master server!");
        }

        //echo "<pre>";print_r($master_response);die;

        $contest_detail = $master_response['data']['contest_detail'];
        $notification_detail   = $master_response['data']['contest_notification_details'];
        if(!is_array($notification_detail) || count($notification_detail) <= 0)
        {
                $update_nw_contest_arr = array(

                    "is_win_notify"      => 1,
                    "date_added"         => format_date()
                );

                $nw_contest_condition = array(
                    "network_contest_id"          => $completed_game['network_contest_id'],
                    "network_collection_master_id"=> $completed_game['network_collection_master_id'],
                    "status"                      => 3,
                    "is_win_notify"               => 0,
                    "is_prize_distributed"        => 1,
                    "active"                      => 1,
                );

                //update network contest entry
                $this->Network_cron_model->update_network_contest($update_nw_contest_arr,$nw_contest_condition);
            exit("Contest notification details not found from Master server");
        }

        $updated_count = 0;
        $current_date = format_date();
        //echo "<pre>1";print_r($notification_detail);die;
        foreach ($notification_detail as $nkey => $notification) 
        {
            //echo "<pre>";print_r($notification);die;
            if(empty($notification['user_id']) || empty($notification['source_id']) || !isset($notification['match_data']) || !isset($notification['contest_data']))
            {
                continue;
            }

            $user_detail = $this->Network_cron_model->get_user_detail_with_balance($notification['user_id']);
            if(empty($user_detail))
            {
                continue;
            }    
            $updated_count++;

            /* Send Notification */
            $notify_data = array();
            $notify_data['notification_type'] = NW_WON_GAME_NOTI_TYPE; //241-network contest won
            $notify_data['notification_destination'] = 7; //web, push, email
            $notify_data["source_id"] = $notification['source_id'];
            $notify_data["user_id"] = $notification['user_id'];
            $notify_data["to"] = $user_detail['email'];
            $notify_data["user_name"] = $user_detail['user_name'];
            $notify_data['network_contest'] = 1;
            $notify_data["added_date"] = $current_date;
            $notify_data["modified_date"] = $current_date;
            $notify_data["subject"] = "Wohoo! You just WON!";

            $content = array();
            $content['match_data'] = $notification['match_data'];
            $content['collection_name'] = $notification['collection_name'];
            //$content['contest_name'] = $notification['contest_name'];
            $content['season_game_count'] = $notification['season_game_count'];
            $content['contest_data'] = $notification['contest_data'];
            $notify_data["content"] = json_encode($content);
            //echo "<pre>1";print_r($notify_data); die;   

            $this->load->model('user/User_nosql_model');
            $this->User_nosql_model->send_notification($notify_data);
        }

        //update fee refunded flag in network contest db table.
        if(!empty($updated_count))
        {
            $update_nw_contest_arr = array(

                "is_win_notify"      => 1,
                "date_added"         => format_date()
            );

            $nw_contest_condition = array(
                "network_contest_id"          => $completed_game['network_contest_id'],
                "network_collection_master_id"=> $completed_game['network_collection_master_id'],
                "status"                      => 3,
                "is_win_notify"               => 0,
                "is_prize_distributed"        => 1,
                "active"                      => 1,
            );

            //update network contest entry
            $this->Network_cron_model->update_network_contest($update_nw_contest_arr,$nw_contest_condition);
        }    

        exit("Cron processed..");
    }
    


}