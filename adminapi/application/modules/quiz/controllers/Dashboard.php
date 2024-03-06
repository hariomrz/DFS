<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* This Class used as REST API for Quiz module
* @category		Controller
* @author		Vinfotech Team
*/
class Dashboard extends MYREST_Controller {
    function __construct() { 
        parent::__construct();    
        $this->admin_lang = $this->lang->line('quiz');
     }

     /**
      series: [{
                    data: [
                        {
                            "name": "0 Ans. Correct",
                            "color": "#FFE74C",
                            "total_coins": 0,
                            "coins_user": 2,
                            "y": 100,
                        },
                        {
                            "name": "<2 Ans. Correct",
                            "color": "#FF5964",
                            "total_coins": 67,
                            "coins_user": 2,
                            "y": 100,
                        },
                        {
                            "name": "2-5 Ans. Correct",
                            "color": "#6BF178",
                            "total_coins": 67,
                            "coins_user": 2,
                            "y": 100,
                        },
                        {
                            "name": "5-7 Ans. Correct",
                            "color": "#35A7FF",
                            "total_coins": 67,
                            "coins_user": 2,
                            "y": 100,
                        },
                        {
                            "name": ">7 Ans. Correct",
                            "color": "#4F4789",
                            "total_coins": 67,
                            "coins_user": 2,
                            "y": 100,
                        },
                    ]
                }], 
      
      **/
     function get_live_quiz_graph_post()
     {
        $this->load->model('Quiz_model');
        $result =  $this->Quiz_model->get_live_quiz_graph_c_vs_u();

        $coin_distributed = 0;
        $data = array('main_values' => array(), 'series' => array());
        if(!empty($result['result']))
        {
            $coin_distributed =array_sum(array_column($result['result'],'value')) ;
            $last_value = 0;
            $main_value =1;
            foreach($result['result'] as &$row)
            {
                $row['data_value'] = $row['value']+$last_value;
                $last_value =  $row['data_value'];
                $row['main_value'] = $main_value;
                $main_value++;
            }
            $data =  get_lineup_graph_data_value(1,count($result['result']),$result['result']);

            // if(isset($data['series']['data']))
            // {
            //     $zero_count = array_count_values($data['series']['data']);
            //     if($zero_count[0]>1)
            //     {
            //         $zero_value = $zero_count[0]; 
            //        foreach($data['series']['data'] as $key => &$value)
            //        {
            //             if($zero_value>1)
            //             {
            //                  array_shift($data['series']['data']);
            //                  array_shift($data['main_values']);
            //                 $zero_value--;
            //             }
            //        }
            //     }
            // }
        }

        $this->api_response_arry['data']['live_quiz_graph'] = $data ; 
        $this->api_response_arry['data']['user_played'] = count($result['result']);

       
        $this->api_response_arry['data']['coin_distributed'] = $coin_distributed ;

        $correct_answer_graph = $this->get_live_quiz_correct_answer_graph();
        $this->api_response_arry['data']['correct_answer_graph'] = $correct_answer_graph['data'];

        $quiz = $this->Quiz_model->get_visible_questions();
        $this->api_response_arry['data']['visible_questions'] = $quiz['visible_questions'];
        $this->api_response();

     }

     private function get_live_quiz_correct_answer_graph_old()
     {
        $this->load->model('Quiz_model');
        $result = $this->Quiz_model->get_live_quiz_correct_answer_count();

        $final = array(
            '0_correct' => array('name' => '0 Ans. Correct','y' =>0,"color"=> "#FFE74C"),
            'less_2_correct' => array('name' => '<2 Ans. Correct','y' =>0, "color" => "#FF5964"),
            '2_5_correct' => array('name' => '2-5 Ans. Correct','y' =>0, "color" =>  "#6BF178"),
            '5_7_correct' => array('name' => '5-7 Ans. Correct','y' =>0, "color" => "#35A7FF"),
            'more_7_correct' => array('name' => '>7 Ans. Correct','y' =>0,"color" => "#4F4789"),
        );
        foreach($result['result'] as $row)
        {
            if($row['correct_questions'] == 0)
            {
                $final['0_correct']['y']++;
            }

            if($row['correct_questions'] < 2)
            {
                $final['less_2_correct']['y']++;
            }

            if($row['correct_questions'] >= 2 && $row['correct_questions'] < 5 ) 
            {
                $final['2_5_correct']['y']++;
            }

            if($row['correct_questions'] >= 5 && $row['correct_questions'] < 7 ) 
            {
                $final['5_7_correct']['y']++;
            }

            if($row['correct_questions'] >= 7 ) 
            {
                $final['more_7_correct']['y']++;
            }
        }
              
        $visible_questions = 0;

        if(!empty($result['result']))
        {
            $visible_questions = $result['result'][0]['visible_questions'];
        }
        return array("data" => array_values($final), "visible_questions" => $visible_questions) ;

     }

     private function get_live_quiz_correct_answer_graph()
     {
        $this->load->model('Quiz_model');
        $result = $this->Quiz_model->get_live_quiz_correct_answer_count();

      
        $user_percentage = array();
        foreach($result['result'] as $row)
            {
               
               $user_percentage[] = ($row['correct_questions']/$row['visible_questions'])*100;
             
            }

            $final = array(
                '0_20_per' => array('name' => '0%-20%','y' =>0,"color"=> "#FFE74C"),
                '21_40_per' => array('name' => '21%-40%','y' =>0, "color" => "#FF5964"),
                '41_60_per' => array('name' => '41%-60%','y' =>0, "color" =>  "#6BF178"),
                '61_80_per' => array('name' => '61%-80%','y' =>0, "color" => "#35A7FF"),
                '81_100_per' => array('name' => '81%-100%','y' =>0,"color" => "#4F4789"),
            );
     
            foreach($user_percentage as $per)
            {
                     if($per >= 0 && $per <= 20)
                     {
                         $final['0_20_per']['y']++;
                     }
         
                     if($per >= 21 && $per <= 40)
                     {
                         $final['21_40_per']['y']++;
                     }
         
                     if($per >= 41 && $per <= 60 ) 
                     {
                         $final['41_60_per']['y']++;
                     }
         
                     if($per >= 61 && $per <= 80 ) 
                     {
                         $final['61_80_per']['y']++;
                     }
         
                     if($per >= 81 && $per <= 100 ) 
                     {
                         $final['81_100_per']['y']++;
                     }
            }
              
        $visible_questions = 0;

        if(!empty($result['result']))
        {
            $visible_questions = $result['result'][0]['visible_questions'];
        }
        return array("data" => array_values($final), "visible_questions" => $visible_questions) ;

     }

      /**
     * @method get_quiz_participation_graph
     * @since Dec 2019
     * @uses function to  quiz participation graph
     * @param Array $_POST 
     * @return json
     * ***/
    function get_quiz_participation_graph_post()
    {
        $post = $this->input->post();
        
        $this->load->model('Quiz_model');
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date(' -70 days')));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $post['from_date'] = date('Y-m-d',strtotime($post['from_date']));
        $post['to_date'] = date('Y-m-d',strtotime($post['to_date']));

        $question_count = $this->Quiz_model->get_quiz_question_count($post);

        $result =  $this->Quiz_model->get_quiz_participation_graph($post);

        $final_data['quiz_participation']['graph_data'] = get_lineup_graph_data($post['from_date'],$post['to_date'],$result['result']);

        $correc_graph_data = $this->get_quiz_correct_answer_graph($post);
        $final_data['correct_answer']['graph_data']  = $correc_graph_data['data'];
        $final_data['coin_distributed']  = $correc_graph_data['coin_distributed'];
        $final_data['winners']  = $correc_graph_data['winner_count'];

        $participants = array_sum(array_column($result['result'],'data_value'));

        $final_data['participants']  = $participants;//count(array_unique($correc_graph_data['participant_user_ids']));
        $final_data['questions']  = $question_count;
        $this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $final_data;
		$this->api_response();
    }

   

    private function get_quiz_correct_answer_graph($post)
    {
       $this->load->model('Quiz_model');
       $result = $this->Quiz_model->get_quiz_correct_answer_count($post);
       $quiz_list = array();

      
       foreach($result['result'] as $row)
       {
           if(!isset($quiz_list[$row['quiz_id']]))
           {
            $quiz_list[$row['quiz_id']] = array();
           }

           if(!isset($quiz_list[$row['quiz_id']][$row['question_id']]))
           {
            $quiz_list[$row['quiz_id']][$row['question_id']] = array();
           }
            $quiz_list[$row['quiz_id']][$row['question_id']][] = $row; 
       }

     $user_correct_answers = array();
     $user_percentage = array();
     $visible_questions = 0;
     $winner_user_ids = array();
     $participant_user_ids = array();
     $coin_distributed = 0;
     $question_id_arr = array();
     $question_count = 0;
       foreach($quiz_list as $quiz_id => $quiz)
       {
            foreach($quiz as $question_id => $questions)
            {
                $question_id_arr[]=$question_id;
                foreach($questions as $user_response)
                {
                    $visible_questions = $user_response['visible_questions'];

                    if($user_response['is_correct'] =='1')
                    {
                        if(!isset($user_correct_answers[$quiz_id]))
                        {
                            $user_correct_answers[$quiz_id] = array();
                        }
    
                        if(!isset($user_correct_answers[$quiz_id][$user_response['user_id']]))
                        {
                            $user_correct_answers[$quiz_id][$user_response['user_id']] = 1;
                        }else{
                            $user_correct_answers[$quiz_id][$user_response['user_id']]++;
                        }
    
                        $winner_user_ids[$quiz_id][] = $user_response['user_id'];

                        if(isset($user_response['prize_data']))
                        {
                            $prize_data = json_decode($user_response['prize_data'],TRUE);
                            if(isset($prize_data['prize_type']) && $prize_data['prize_type']=='2')
                            {
                                $coin_distributed+=$prize_data['amount'];
                            }
                            
                        }
                    }

                    $participant_user_ids[] = $user_response['user_id'];
                    
                }
            }

            foreach($user_correct_answers[$quiz_id] as $user_id => $correct_answer)
            {
               if($visible_questions> 0)
               {
                   $user_percentage[$quiz_id][] = ($correct_answer/$visible_questions)*100;
               }
            }
       }

       $final = array(
           '0_20_per' => array('name' => '0%-20%','y' =>0,"color"=> "#FFE74C"),
           '21_40_per' => array('name' => '21%-40%','y' =>0, "color" => "#FF5964"),
           '41_60_per' => array('name' => '41%-60%','y' =>0, "color" =>  "#6BF178"),
           '61_80_per' => array('name' => '61%-80%','y' =>0, "color" => "#35A7FF"),
           '81_100_per' => array('name' => '81%-100%','y' =>0,"color" => "#4F4789"),
       );

       foreach($user_percentage as $quiz_per)
       {
            foreach($quiz_per as $per)
            {
                if($per >= 0 && $per <= 20)
                {
                    $final['0_20_per']['y']++;
                }
    
                if($per >= 21 && $per <= 40)
                {
                    $final['21_40_per']['y']++;
                }
    
                if($per >= 41 && $per <= 60 ) 
                {
                    $final['41_60_per']['y']++;
                }
    
                if($per >= 61 && $per <= 80 ) 
                {
                    $final['61_80_per']['y']++;
                }
    
                if($per >= 81 && $per <= 100 ) 
                {
                    $final['81_100_per']['y']++;
                }
            }
       }
       
             
      if(!empty($question_id_arr))
      {
          $question_count = count(array_unique($question_id_arr));
      }

      $winner_count =0;
      foreach($winner_user_ids as $quiz_winner)
      {
        $winner_count+=count(array_unique($quiz_winner));
      }
       return array(
           "data" => array_values($final),
           "winner_count" => $winner_count,
           "participant_user_ids" => $participant_user_ids,
           "coin_distributed" => $coin_distributed,
           "question_count"=>$question_count
           ) ;

    }

    function get_quiz_leaderboard_post()
    {
        $post = $this->input->post();
        
        $this->load->model('Quiz_model');
        $result =  $this->Quiz_model->get_quiz_leaderboard($post);
        $this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
    }

}