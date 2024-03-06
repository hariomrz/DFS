<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* This Class used as REST API for Quiz module
* @category		Controller
* @author		Vinfotech Team
*/
class Reward_dashboard extends MYREST_Controller {

    var $source_color_map=array();
    function __construct() { 
        parent::__construct();    
        $this->source_color_map = array(
                471 => '#C84C09',//download_app
                470 => '#35A7FF',//quiz won,
                322=> '#FCE44B',//spin the wheel
                144 => '#FF5964',//daily checkin
                181 => '#44D7B6',//pickem
                41 => '#B620E0',
                'referral' => '#4F4789',//referral
                'other' => '#6D7278'
            

        );
        
     }

     function graph_post()
     {
         $post = $this->input->post();
         $this->load->model('Reward_dashboard_model');

         $refereal_sources = array(53,54,55,56,57,58,62,63,64,65,66,67,68,69,71,72,74,75,77,78,80,81,83,84,86,87,88,89,90,91,98,99,100,132,133,134,141,142,143,156,157,158,159,160,161);
         $source_arr = array(
             471,//download_app
             470,//quiz won,
             322,//Spon the wheel
             144,//daily checkin
             181,//pickem
             41//prediction
             
 
         );

         
         $result = $this->Reward_dashboard_model->get_source_wise_data($post);

         $final_arr = array();
         $final_arr['coin_graph'] = array();
         $final_arr['bonus_graph'] = array();
         $final_arr['cash_graph'] = array();
         $coin_sum =0;
         $bonus_sum =0;
         $cash_sum =0;
         $coin_user_count =0;
         $bonus_user_count =0;
         $cash_user_count =0;
         $table_data = array();
         $referral_arr = array();
         $other_arr = array();
         $referral_user_ids = array();
         $other_user_ids = array();
         $coin_user_ids = array();
         $bonus_user_ids = array();
         $cash_user_ids = array();
         foreach($result as $row)
         {

            $row['coins'] = intval($row['coins']);
            $row['bonus'] = intval($row['bonus']);
            $row['cash'] = intval($row['cash']);
            $coin_sum+=$row['coins'];
            $bonus_sum+=$row['bonus'];
            $cash_sum+=$row['cash'];

            $coin_user_ids_tmp = array_filter(explode(',',$row['coin_user_ids']), function($value) {return !is_null($value) && $value !== '';} );
            $bonus_user_ids_tmp =array_filter(explode(',',$row['bonus_user_ids']), function($value) {return !is_null($value) && $value !== '';} );
            $cash_user_ids_tmp =array_filter(explode(',',$row['cash_user_ids']), function($value) {return !is_null($value) && $value !== '';} );

            
            $all_source_users = array_merge($coin_user_ids_tmp,$bonus_user_ids_tmp,$cash_user_ids_tmp);
            $row['user_count'] = count(array_unique($all_source_users));


            $coin_user_ids = array_merge($coin_user_ids,$coin_user_ids_tmp);
            $bonus_user_ids = array_merge($bonus_user_ids,$bonus_user_ids_tmp);
            $cash_user_ids = array_merge($cash_user_ids,$cash_user_ids_tmp);
            
            unset($row['coin_user_ids']);
            unset($row['bonus_user_ids']);
            unset($row['cash_user_ids']);

             if(in_array($row['source'],$source_arr))
             {
                $final_arr['coin_graph'][$row['source']] = array(
                    "name" => $row['name'],
                    "y" => $row['coins'],
                    "color" => $this->source_color_map[$row['source']]
                );
    
                $final_arr['bonus_graph'][$row['source']] = array(
                    "name" => $row['name'],
                    "y" => $row['bonus'],
                    "color" => $this->source_color_map[$row['source']]
                );
    
                $final_arr['cash_graph'][$row['source']] = array(
                    "name" => $row['name'],
                    "y" => $row['cash'],
                    "color" => $this->source_color_map[$row['source']]
                );

                $table_data[] = $row;
               
             }
             else if(in_array($row['source'],$refereal_sources))
             {
                $final_arr['coin_graph']['referral'] = array(
                    "name" => "Refer a Friend",
                    "y" => $row['coins'],
                    "color" => $this->source_color_map['referral']
                );
    
                $final_arr['bonus_graph']["referral"] = array(
                    "name" => "Refer a Friend",
                    "y" => $row['bonus'],
                    "color" => $this->source_color_map['referral']
                );
    
                $final_arr['cash_graph']["referral"] = array(
                    "name" => "Refer a Friend",
                    "y" => $row['cash'],
                    "color" => $this->source_color_map['referral']
                );

                $row["name"] = "Refer a Friend";
                $referral_arr[] = $row;
                $referral_user_ids = array_merge($referral_user_ids,$all_source_users);
             }
             else {
                 if(!isset($final_arr['coin_graph']['other']))
                 {
                    $final_arr['coin_graph']['other'] = array(
                        "name" => "Other",
                        "y" => $row['coins'],
                        "color" => $this->source_color_map['other']
                    );
                 }
                 else{
                    $final_arr['coin_graph']['other']['y']+=$row['coins'];
                 }
                
                 if(!isset($final_arr['bonus_graph']['other']))
                 {
                    $final_arr['bonus_graph']["other"] = array(
                        "name" => "Other",
                        "y" => $row['bonus'],
                        "color" => $this->source_color_map['other']
                    );
                 }
                 else
                 {
                    $final_arr['bonus_graph']["other"]['y']+=$row['bonus'];
                 }
               
                 if(!isset($final_arr['cash_graph']['other']))
                 {
                    $final_arr['cash_graph']["other"] = array(
                        "name" => "Other",
                        "y" => $row['cash'],
                        "color" => $this->source_color_map['other']
                    );
                 }
                 else
                 {
                    $final_arr['cash_graph']["other"]['y']+=$row['cash'];
                 }
              
                $row["name"] = "Other";
                $other_arr[] = $row;
                $other_user_ids = array_merge($other_user_ids,$all_source_users);
             }
          
         }

        $coin_user_count=count(array_unique($coin_user_ids) );
        $bonus_user_count=count(array_unique($bonus_user_ids));
        $cash_user_count=count(array_unique($cash_user_ids));

         $referral = array('source' => 0,
         'coins' => 0,
         'bonus' => 0,
         'cash' => 0,
         'name' => 'Referral',
         'user_count' => count(array_unique($referral_user_ids)) 
        );
         $other = array('source' => 0,
         'coins' => 0,
         'bonus' => 0,
         'cash' => 0,
         'name' => 'Other',
         'user_count' => count(array_unique($other_user_ids))
        );

         foreach($referral_arr as $row)
         {
            $referral['coins']+=$row['coins'];
            $referral['bonus']+=$row['bonus'];
            $referral['cash']+=$row['cash'];

            
         }

         foreach($other_arr as $row)
         {
            $other['coins']+=$row['coins'];
            $other['bonus']+=$row['bonus'];
            $other['cash']+=$row['cash'];
         }
         
         $table_data[] = $referral;
         $table_data[] = $other;

         $response = array();
         $response['coin_sum'] = $coin_sum;
         $response['bonus_sum'] = $bonus_sum;
         $response['cash_sum'] = $cash_sum;
         $response['coin_user_count'] = $coin_user_count;
         $response['bonus_user_count'] = $bonus_user_count;
         $response['cash_user_count'] = $cash_user_count;
         $response['table_data'] = $table_data;
         $response['graphs'] = array();

          foreach ($final_arr as $key =>  $graph)
          {
            $response['graphs'][$key] =array_values($graph) ;
          }

        //  echo '<pre>';
        //  print_r($final_arr);die();

         $this->api_response_arry['data']  =$response;
         $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
         $this->api_response();
     }
    
}