<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* This Class used as REST API for Quiz module
* @category		Controller
* @author		Vinfotech Team
*/
class Quiz extends Common_Api_Controller {
    function __construct() { 
        parent::__construct();
     }
   
    /**
     * Used to get toady Quiz
     */
    public function today_post() {        
        $this->load->model(array(                    
            'quiz/Quiz_model'
        ));
        $user_id = $this->user_id;
        $this->api_response_arry['data'] = $this->Quiz_model->get_quiz($user_id);
		$this->api_response();
    } 

    /**
     * Used to get Quiz questions
     */
    function get_questions_post() {
        $data = $this->input->post();
        if ($data) {
            $config = array(
                            array(
                                'field' => 'quiz_uid',
                                'label' => 'quiz uid',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $this->send_validation_errors();
            } else {
                $user_id = $this->user_id;
                $this->load->model(array(                    
                    'quiz/Quiz_model'
                ));
                $row = $this->Quiz_model->get_single_row('quiz_id, visible_questions',QUIZ,array('quiz_uid' => $data['quiz_uid'], 'status' => 0));
                if(!empty($row)) {
                    $is_visited = $this->Quiz_model->is_quiz_visit_by_user($user_id, $row['quiz_id']);
                    if($is_visited == 1) {
                        $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['global_error'] = $this->lang->line('quiz_already_claimed');
                    } else {
                        $this->Quiz_model->update_quiz_visit_date($row['quiz_id'], $user_id);
                        $this->api_response_arry['data'] = $this->Quiz_model->get_questions($row['quiz_id'], 1, $row['visible_questions']);
                    }                    
                } else {
                    $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = sprintf($this->lang->line('valid_value'), "quiz uid");
                }                
            }    
        } else {
            $this->api_response_arry['response_code'] = self::HTTP_BAD_REQUEST;
            $this->api_response_arry['global_error'] = $this->lang->line('input_invalid_format');
        }   
        $this->api_response();  // Final Output 
    }

    /**
     * Used to check questions answer
     */
    function check_answer_post() {
        $data = $this->input->post();
        if ($data) {
            $config = array(
                            array(
                                'field' => 'question_uid',
                                'label' => 'question uid',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'option_uid',
                                'label' => 'option uid',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $this->send_validation_errors();
            } else {
                $user_id = $this->user_id;
                $this->load->model(array(                    
                    'quiz/Quiz_model'
                ));
                $row = $this->Quiz_model->get_single_row('quiz_id, question_id',QUIZ_QUESTION,array('question_uid' => $data['question_uid'], 'status' => 0, 'is_hide' => 0));
                if(!empty($row)) {
                    $is_visited = $this->Quiz_model->is_quiz_visit_by_user($user_id, $row['quiz_id']);
                    $is_correct = 0;
                    if($is_visited == 1) {
                        $is_correct = $this->Quiz_model->get_single_row('is_correct',QUIZ_OPTIONS,array('option_uid' => $data['option_uid'], 'question_id' => $row['question_id']));

                         //if wrong then return correct answer
                        if(empty($is_correct['is_correct']))
                        {
                            //get correct answers
                            $correct = $this->Quiz_model->get_single_row('option_uid',QUIZ_OPTIONS,array('is_correct' => 1, 'question_id' => $row['question_id']));
                            $this->api_response_arry['data']['correct_option_uid'] = $correct['option_uid'] ;

                        }
                    }  
                    $this->api_response_arry['data']['is_correct'] = $is_correct['is_correct']; 
                    
                   
                } else {
                    $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = sprintf($this->lang->line('valid_value'), "quiz uid");
                }                
            }    
        } else {
            $this->api_response_arry['response_code'] = self::HTTP_BAD_REQUEST;
            $this->api_response_arry['global_error'] = $this->lang->line('input_invalid_format');
        }   
        $this->api_response();  // Final Output 
    }

    /**
     * @since Oct 2021
     * @uses function to claim quiz quiz reward after attempting all questions
     * @param quiz_uid string  
     * @param questions Array   
     *    {
  "quiz_uid": "",
  "questions": [
    {
      "question_uid": "",
      "option_uid": ""
    },
    {
      "question_uid": "",
      "option_uid": ""
    },
    {
      "question_uid": "",
      "option_uid": ""
    }
  ]
}
     * 
     * ***/
    function claim_quiz_post()
    {
       
        $data = $this->input->post();
        if ($data) {
            $config = array(
                            array(
                                'field' => 'quiz_uid',
                                'label' => $this->lang->line('quiz_uid'),
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'questions[]',
              
                                'label' => $this->lang->line('question_selected_options'),
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $this->send_validation_errors();
            } else {
                
            foreach($data['questions'] as $question)
            {
                if(empty($question['question_uid']) || empty($question['option_uid']))
                {
                    $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] =  $this->lang->line('err_enter_valid_question_answer');
                    $this->api_response();

                }    
            }

            $this->load->model(array(                    
                'quiz/Quiz_model'
            ));
            //get Quiz id and date
            $quiz = $this->Quiz_model->get_single_row('quiz_id,scheduled_date',QUIZ,array('quiz_uid' => $data['quiz_uid']));
            $question_uids = array_column($data['questions'],'question_uid');
            $option_uids = array_column($data['questions'],'option_uid');

            //get question options with answers
           
            $question_options = $this->Quiz_model->get_question_correct_options($question_uids,$option_uids);
           
            $bonus =0;
            $real= 0;
            $coins = 0;
            $quiz_answers = array();
            $current_date = format_date();
            foreach($question_options as $val)
            {
                $prize_data = array('prize_type' => $val['prize_type'],"amount" => $val['prize_value'] );

                $quiz_answers[] = array('user_id' => $this->user_id,
                'option_id' => $val['option_id'],
                'prize_data' => json_encode($prize_data),
                'added_date' => $current_date,
                'updated_date' =>  $current_date);


                if($val['is_correct'] =='0')
                {
                    continue;
                }

                switch($val['prize_type'])
                {
                    case 2: //coins
                        $coins+=$val['prize_value'];
                        
                    break;
                    case 0:
                        $bonus+=$val['prize_value'];
                    break;
                    case 1:
                        $real+=$val['prize_value'];
                    break;
                }
                
               
            }

            $user_id = $this->user_id;

            $this->load->model("finance/Finance_model");
            $source = 470;
            //check if already claimed previously
            $order_exists = $this->Finance_model->get_single_row('order_id',ORDER,array('source' => $source,"user_id" => $user_id,"reference_id" => $quiz['quiz_id']));

            if(!empty($order_exists))
            {
                $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('quiz_already_claimed');
                $this->api_response();
            }
            $order_data = array();
            
            $source_id = $quiz['quiz_id'];
            $order_data["order_unique_id"] = $this->Finance_model->_generate_order_key();
            $order_data["user_id"]        = $user_id;
            $order_data["source"]         = 470;
            $order_data["source_id"]      = $quiz['quiz_id'];
            $order_data["reference_id"]   = $quiz['quiz_id'];
            $order_data["season_type"]    = 1;
            $order_data["type"]           = 0;
            $order_data["status"]         = 1;
            $order_data["real_amount"]    = $real;
            $order_data["bonus_amount"]   = $bonus;
            $order_data["winning_amount"] = 0;
            $order_data["points"] = ceil($coins);
            $order_data["plateform"]      = PLATEFORM_FANTASY;
            $order_data["date_added"]     = format_date();
            $order_data["modified_date"]  = format_date();
            $order_data["custom_data"]  = json_encode(array('scheduled_date' => $quiz['scheduled_date']));
           
            $notify = 0;
            if($coins > 0 || $real > 0 || $bonus > 0)
            {
                $notify = 1;
                $order_id = $this->Finance_model->insert_order($order_data);
                if ($order_data["status"] == 1) {
                    $this->Finance_model->update_user_balance($order_data["user_id"], $order_data, 'add');

                    if($order_data["points"] > 0) {
                        $this->load->helper('queue_helper');
                        $coin_data = array(
                            'oprator' => 'add', 
                            'user_id' => $user_id, 
                            'total_coins' => $order_data["points"], 
                            'bonus_date' => format_date("today", "Y-m-d")
                        );
                        add_data_in_queue($coin_data, 'user_coins');    
                    }

                    $user_cache_key = "user_balance_" . $order_data["user_id"];
                    $this->delete_cache_data($user_cache_key);        
                }
            }

            //add entries to quiz_answers
            $this->Quiz_model->insert_quiz_answers($quiz_answers);

            //update participants
            $this->Quiz_model->update_quiz_participants($quiz['quiz_id']);            
            if($notify)
            {
                $this->load->model('notification/Notify_nosql_model');
                $today = format_date();
                $tmp = array();
                $input = array();
                $user_detail = $this->Finance_model->get_single_row('email, user_name', USER, array("user_id" => $user_id));
                $input["user_name"] = $user_detail['user_name'];
                $input["amount"] = $order_data["points"];
                $tmp["source_id"] = $quiz['quiz_id'];
                $tmp["user_id"] = $user_id;
                $tmp["to"] = $user_detail['email'];
                $tmp["user_name"] = $user_detail['user_name'];
                $tmp["added_date"] = $today;
                $tmp["modified_date"] = $today;
                $tmp["content"] = json_encode($input);
                $notify_data = $this->Finance_model->notify_type_by_source($source);
                $tmp['notification_type'] = $notify_data['notification_type'];
                $tmp['subject'] = $notify_data['subject'];
                $tmp['notification_destination'] = $notify_data['notification_destination'];
                $this->Notify_nosql_model->send_notification($tmp);
                $this->api_response_arry['response_code'] = self::HTTP_OK;
                $this->api_response_arry['message'] = $this->lang->line('succ_quiz_claimed');  
            }
                
            }    
        } else {
            $this->api_response_arry['response_code'] = self::HTTP_BAD_REQUEST;
            $this->api_response_arry['global_error'] = $this->lang->line('input_invalid_format');
        }   
        $this->api_response();  // Final Output 

    }

   
}