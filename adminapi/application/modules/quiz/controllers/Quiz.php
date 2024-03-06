<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* This Class used as REST API for Quiz module
* @category		Controller
* @author		Vinfotech Team
*/
class Quiz extends MYREST_Controller {
    function __construct() { 
        parent::__construct();    
        $this->admin_lang = $this->lang->line('quiz');
     }
   
     /**
     * Used to get Quiz  list
     */
    public function list_post() {
        $data = $this->input->post();
        
        $this->load->model(array(                    
            'quiz/Quiz_model'
        ));
        
        $this->api_response_arry['data']['result'] = $this->Quiz_model->get_quiz($data); 
        $this->api_response_arry['data']['total'] = 0;
        $page_no = safe_array_key($data, 'current_page', 1);
        if ($page_no == '1') {
            $data['count_only'] = 1;
            $total = $this->Quiz_model->get_quiz($data); 
            $this->api_response_arry['data']['total'] = $total;
        }

		$this->api_response();
    } 

     /**
     * Used to get Quiz  list
     */
    public function details_post() {
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
                $this->load->model(array(                    
                    'quiz/Quiz_model'
                ));
                $row = $this->Quiz_model->get_single_row('quiz_uid, scheduled_date, visible_questions',QUIZ,array('quiz_uid' => $data['quiz_uid']));
                if(!empty($row)) {
                    $this->api_response_arry['data'] = $row;
                } else {
                    $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = sprintf($this->admin_lang['valid_value'], "quiz uid");
                }                
            }    
        } else {
            $this->api_response_arry['response_code'] = self::HTTP_BAD_REQUEST;
            $this->api_response_arry['global_error'] = $this->admin_lang['input_invalid_format'];
        }   
        $this->api_response();  // Final Output 
    }
    

    /**
     * Used to check quiz exist for given date or not
     */
    public function check_quiz_exist_post() {
        $data = $this->input->post();
        if ($data) {
            $config = array(
                            array(
                                'field' => 'scheduled_date',
                                'label' => $this->admin_lang['scheduled_date'],
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $this->send_validation_errors();
            } else {
                $this->load->model(array(                    
                    'quiz/Quiz_model'
                ));
                $row = $this->Quiz_model->get_single_row('visible_questions',QUIZ,array('scheduled_date' => $data['scheduled_date'], "status !=" => 4));
                $this->api_response_arry['data']['visible_questions'] = 0;
                if(!empty($row)) {
                    $this->api_response_arry['data']['visible_questions'] = $row['visible_questions'];
                }               
            }    
        } else {
            $this->api_response_arry['response_code'] = self::HTTP_BAD_REQUEST;
            $this->api_response_arry['global_error'] = $this->admin_lang['input_invalid_format'];
        }   
        $this->api_response();  // Final Output 
    }
    
    /**
     * Used to add quiz and question 
     */
    public function add_post() {
        
        $post_data = $this->input->post(); 
        
        if ($post_data) {
            $config = array(
                            array(
                                'field' => 'scheduled_date',
                                'label' => $this->admin_lang['scheduled_date'],
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'visible_questions',
                                'label' => $this->admin_lang['visible_questions'],
                                'rules' => 'trim|required|is_natural_no_zero'
                            ),
                            array(
                                'field' => 'question_text',
                                'label' =>  $this->admin_lang['question'],
                                'rules' => 'trim|min_length[6]|max_length[250]'
                            ),
                            array(
                                'field' => 'options[]',
                                'label' =>  $this->admin_lang['options'],
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'prize_value',
                                'label' =>  $this->admin_lang['prize'],
                                'rules' => 'trim|required|is_natural_no_zero'
                            ),
                            array(
                                'field' => 'prize_type',
                                'label' =>  $this->admin_lang['prize_type'],
                                'rules' => 'trim|required|min_length[1]|max_length[1]'
                            ),
                            array(
                                'field' => 'time_cap',
                                'label' =>  $this->admin_lang['time_cap'],
                                'rules' => 'trim|required|is_natural_no_zero'
                            )
                        );
            $this->form_validation->set_rules($config);
            if($this->form_validation->run() == FALSE) {
                $this->send_validation_errors();
            } else {
                if(empty($post_data['question_text']) && empty($post_data['question_image'])){
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_BAD_REQUEST;
                    $this->api_response_arry['message'] = $this->admin_lang['question_image_required'];
                    $this->api_response();
                }    

                $options = array_column($post_data['options'],"text");
                $option_count = count($options);
                foreach($options as $key =>  $option_text)
                {
                    if(strlen($option_text) > 30)
                    {
                        $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => $this->admin_lang['err_option_min_char'],'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                    }
    
                    if(empty(trim($option_text)))
                    {
                        unset($options[$key]);
                    }
                }
    
                if(empty($options) || $option_count < 2 || $option_count > 4 )
                {
                    $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => $this->admin_lang['err_invalid_option'],'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                }    
                
                $option_answers = array_column($post_data['options'],"is_correct");
                $correct_sum = array_sum($option_answers);

                if($correct_sum != 1)
                {
                    $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => $this->admin_lang['err_add_correct_answer'],'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR); 
                }
                $this->load->model(array('quiz/Quiz_model'));
              
                //check quiz exists
                $quiz_id = 0;
                $current_date = format_date();
                $quiz = $this->Quiz_model->get_single_row('quiz_id,status',QUIZ,array('scheduled_date' => $post_data['scheduled_date'],"status < " => 2));

                $this->db->trans_begin();
                if(!empty($quiz['quiz_id']))
                {
                    //update
                    $quiz_id = $quiz['quiz_id'];
                    $quiz_data = array();
                    $quiz_data['updated_date']= $current_date;
                    $quiz_data['visible_questions']=$post_data['visible_questions'];
                    $quiz_id = $this->Quiz_model->add($quiz_data,$quiz_id);
                }
                else{
                    //add quiz
                    $quiz_data = array();
                    $quiz_data['scheduled_date'] = $post_data['scheduled_date'];
                    $quiz_data['visible_questions'] = $post_data['visible_questions'];
                    $quiz_data['added_date']  = $current_date;
                    $quiz_data['updated_date'] = $current_date;
                    $quiz_data['quiz_uid']= get_guid();
                    $quiz_id = $this->Quiz_model->add($quiz_data);
                }

                $question_text = isset($post_data['question_text']) ? $post_data['question_text'] : "";
                $question_image = isset($post_data['question_image']) ? $post_data['question_image'] : "";
                //Now Add question
                $question_arr = array(
                    'quiz_id'               =>  $quiz_id,
                    'question_text'         => $question_text,
                    'question_image'        => $question_image,
                    'added_date'            => format_date(),
                    'updated_date'          => format_date(),
                    'prize_value'           => $post_data['prize_value'],
                    'time_cap'              => $post_data['time_cap'],
                    'prize_type'            => $post_data['prize_type'],
                    'question_uid'          => get_guid()
                );
          
                if($quiz['status'] == '1')
                {
                    $question_arr['is_hide'] = 1;
                }
                $question_id = $this->Quiz_model->add_question($question_arr);
                //update question count in quiz
                $this->Quiz_model->update_total_quiz_question($quiz_id);

                //insert options in db
                $options_arr = array();
                foreach ($options as $key => $value)
                {
                    if(empty($value))
                    {
                     continue;
                    }    

                    $options_arr[] = array(
                        'question_id'      => $question_id,
                        'option_text'      => $value,
                        'option_uid'     => get_guid(),
                        'is_correct'    => $option_answers[$key],
                        'added_date'       => format_date(),
                        'updated_date'     => format_date()
                    );
                }
 
                if(!empty($options_arr))
                {
                    $this->Quiz_model->insert_question_option($options_arr);
                }   

                if ($this->db->trans_status() === FALSE)
                {
                    $this->db->trans_rollback();
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->admin_lang['err_process_request'];
                    $this->api_response();
                }
                else
                {
                    $this->db->trans_commit();
                }
                $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                $this->api_response_arry['message']         = $this->admin_lang['succ_add_question'];
            }    
        } else {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_BAD_REQUEST;
            $this->api_response_arry['message']         = $this->admin_lang['input_invalid_format'];
        }
        $this->api_response();
    }

   /**
     * @since Oct 2021
     * @uses function to hide show
     * @method change_question_visibility 
     * 
    */
    function change_question_visibility_post()
    {
        $this->form_validation->set_rules('visible', $this->admin_lang['visible'], 'trim|required');
        $this->form_validation->set_rules('question_uid', $this->admin_lang['question_uid'], 'trim|required');
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }

        $post= $this->input->post();

        if(!in_array($post['visible'],array(0,1)))
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['err_value_invalid'];
            $this->api_response();
        }

        $this->load->model('Quiz_model');

        $question = $this->Quiz_model->get_single_row('question_id,status',QUIZ_QUESTION,array('question_uid' => $post['question_uid']));

        if(!empty($question))
        {
            if(in_array($question['status'],array(1)))//question deleted
            {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	=  $this->admin_lang['err_question_already_deleted'];
                $this->api_response();
            }
        }

        $result = $this->Quiz_model->change_question_visibility($post['visible'],$question['question_id']);

       
        if($result)
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            
            $this->api_response_arry['message']  	= $this->admin_lang['succ_question_visibility_change'];
            $this->api_response();
        }
        else{
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->admin_lang['err_question_visibility_change'];
            $this->api_response();
        }

    }

    /**
     * [delete_question_post description]
     * @uses :- delete question
     * @param Number 1,2,3,4 for tab_no, sports id
     */
    function delete_question_post()
    {
        $this->form_validation->set_rules('question_uid', 'Question UID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $question_uid = $this->input->post('question_uid');
        $this->load->model('Quiz_model');
        $current_date = format_date('today','Y-m-d');
        $question_result = $this->Quiz_model->check_valid_question($question_uid,$current_date);

        if(empty($question_result))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Not a valid question.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
       
        if( $question_result['quiz_status'] > 0)
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Only open question can be deleted.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }   

        $delete_result = $this->Quiz_model->delete_question($question_result);

        $this->response(array(
        'message' => 'Question Deleted.',
        'response_code'=>rest_controller::HTTP_OK),
         rest_controller::HTTP_OK);
    }

     /**
     *@method  update_question_post
     * @uses function to Update predictions
     * @param Array
     */

    public function update_question_post()
    {
         if ($this->input->post()) {
            $this->form_validation->set_rules('question_uid', $this->admin_lang['question_uid'], 'trim|required');
            $this->form_validation->set_rules('question_text', $this->admin_lang['question'], 'trim|min_length[6]|max_length[250]');
            $this->form_validation->set_rules('options[]', 'Option(s)', 'trim|required');
            $this->form_validation->set_rules('prize_value', $this->admin_lang['prize'], 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('prize_type', $this->admin_lang['prize_type'], 'trim|required');
            $this->form_validation->set_rules('time_cap',$this->admin_lang['time_cap'] , 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('visible_questions',$this->admin_lang['visible_questions'] , 'trim|required|is_natural_no_zero');
            
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $post_data = $this->input->post();
            if(empty($post_data['question_text']) && empty($post_data['question_image'])){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_BAD_REQUEST;
                $this->api_response_arry['message'] = $this->admin_lang['question_image_required'];
                $this->api_response();
            }
            $this->load->model('Quiz_model');
            $current_date = format_date('today','Y-m-d');
            $question_result = $this->Quiz_model->check_valid_question($post_data['question_uid'],$current_date);

            if(empty($question_result))
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Not a valid question.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            }

            if(isset($question_result['total_participants']) && $question_result['total_participants'] > 0)
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "You can not update this question, It is joined by few users.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            }

           // $this->check_deadline_time();

       
            $prediction_arr = array();

            $options    = array_column($post_data['options'],"text");
            $option_count = count($options);

            foreach($options as $key =>  $option_text)
            {
                if(strlen($option_text) > 30)
                {
                    $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Option length can not be greater than 30 characters",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                }

                if(empty(trim($option_text)))
                {
                    unset($options[$key]);
                }

            }

            if(empty($options) || $option_count < 2 || $option_count > 4 )
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Invalid options",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            }    

            $option_answers = array_column($post_data['options'],"is_correct");
            $correct_sum = array_sum($option_answers);

            if($correct_sum != 1)
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => $this->admin_lang['err_add_correct_answer'],'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR); 
            }

            if(!empty($question_result['quiz_id']))
            {
                //update
                $quiz_id = $question_result['quiz_id'];
                $quiz_data = array();
                $quiz_data['visible_questions']=$post_data['visible_questions'];
                $quiz_data['updated_date']=format_date();
                $quiz_id = $this->Quiz_model->add($quiz_data,$quiz_id);
            }
            
            $question_text = isset($post_data['question_text']) ? $post_data['question_text'] : "";
            $question_image = isset($post_data['question_image']) ? $post_data['question_image'] : "";
            $current_date = format_date();
            $question_arr = array();
            $question_arr['quiz_id']= $question_result['quiz_id'];
            $question_arr['question_text']= $question_text;
            $question_arr['question_image']= $question_image;
            $question_arr['prize_value']= $post_data['prize_value'];
            $question_arr['time_cap']= $post_data['time_cap'];
            $question_arr['prize_type']= $post_data['prize_type'];
            $question_arr['added_date']= $current_date;
            $question_arr['updated_date']= $current_date;

            $affected_count = $this->Quiz_model->update_question($question_result['question_id'],$question_arr);

            if(empty($affected_count))
            {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']         = "Question Not updated!Try again.";
                $this->api_response_arry['service_name']    = 'create_prediction';
                $this->api_response();
            }    
    
             //insert options in db
            $options_arr = array();
            foreach ($options as $key => $value)
            {
                if(empty($value))
                {
                    continue;
                }    

                $options_arr[] = array(
                    'question_id'      => $question_result['question_id'],
                    'option_text'      => $value,
                    'option_uid'     => get_guid(),
                    'is_correct'    => $option_answers[$key],
                    'added_date'       => format_date(),
                    'updated_date'     => format_date()
                ); 
            }

            if(!empty($options_arr))
            {
                $this->Quiz_model->update_question_option($question_result['question_id'],$options_arr);
            }  

            //$this->push_s3_data_in_queue("lobby_category_list_open_predictor",array(),"delete"); 

                $this->response(array(config_item('rest_status_field_name') => TRUE, 'message' =>"Question has been updated successfully." ,'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
           
        } else {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Question not updated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
         
    }

    //---------------------------------------------------------------------------------

    /**
     * Used to delete quiz question
     */
    public function delete_post() {
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
                $this->load->model(array(                    
                    'quiz/Quiz_model'
                ));
                $current_date = format_date('today', 'Y-m-d');
                $row = $this->Quiz_model->get_single_row('quiz_id, status',QUIZ,array('quiz_uid' => $data['quiz_uid'], 'scheduled_date > ' => $current_date));
                if($row) {
                    $quiz_id = $row['quiz_id'];
                    $status = $row['status'];
                    if(in_array($status, array(0,1))) {
                        $this->Quiz_model->delete($quiz_id);
                        $this->api_response_arry['message'] = $this->admin_lang['deleted'];
                    } else {
                        $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['global_error'] = $this->admin_lang['can_not_delete'];
                    }                   
                } else {
                    $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = sprintf($this->admin_lang['valid_value'], "quiz uid");
                }                
            }    
        } else {
            $this->api_response_arry['response_code'] = self::HTTP_BAD_REQUEST;
            $this->api_response_arry['global_error'] = $this->admin_lang['input_invalid_format'];
        }   
        $this->api_response();  // Final Output 
    }

    /**
     * Used to hold/unhold quiz 
     */
    public function toggle_hold_post() {
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
                $this->load->model(array(                    
                    'quiz/Quiz_model'
                ));
                $current_date = format_date('today', 'Y-m-d');
                $row = $this->Quiz_model->get_single_row('quiz_id, status',QUIZ,array('quiz_uid' => $data['quiz_uid'], 'scheduled_date >= ' => $current_date));
                if($row) {
                    $quiz_id = $row['quiz_id'];
                    $status = $row['status'];
                    if(in_array($status, array(0,1))) {
                        $status = ($status == 1) ? 0 : 1;
                        $this->Quiz_model->toggle_hold($quiz_id, $status);
                        $this->api_response_arry['message'] = $this->admin_lang['play_status'];
                    } else {
                        $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['global_error'] = $this->admin_lang['can_not_hold'];
                    }                   
                } else {
                    $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = sprintf($this->admin_lang['valid_value'], "quiz uid");
                }                
            }    
        } else {
            $this->api_response_arry['response_code'] = self::HTTP_BAD_REQUEST;
            $this->api_response_arry['global_error'] = $this->admin_lang['input_invalid_format'];
        }   
        $this->api_response();  // Final Output 
    }

    /**
     * Used to get questions
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
                $this->load->model(array(                    
                    'quiz/Quiz_model'
                ));
                $row = $this->Quiz_model->get_single_row('quiz_id',QUIZ,array('quiz_uid' => $data['quiz_uid']));
                if(!empty($row)) {
                    $quiz_id = $row['quiz_id'];
                    $this->api_response_arry['data'] = $this->Quiz_model->get_questions($quiz_id);
                } else {
                    $this->api_response_arry['response_code'] = self::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = sprintf($this->admin_lang['valid_value'], "quiz uid");
                }                
            }    
        } else {
            $this->api_response_arry['response_code'] = self::HTTP_BAD_REQUEST;
            $this->api_response_arry['global_error'] = $this->admin_lang['input_invalid_format'];
        }   
        $this->api_response();  // Final Output 
    }

    

    /**
     * [submit_prediction_answer_post description]
     * @uses :- submit prediction answer
     * @param  
     */
    public function submit_prediction_answer_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'QuestionGUID',
                                'label' => 'question guid',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'OptionGUID',
                                'label' => 'option guid',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'ProofDescription',
                                'label' => 'proof description',
                                'rules' => 'trim|min_length[3]|max_length[250]'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $row = $this->quiz_model->get_single_row("QuestionID, Title, QuizID", QUIZ_OPTIONS, array('QuestionGUID' => $data['QuestionGUID'], 'Status' => 0));
                if($row) {
                    $sponser_id = $this->quiz_model->get_quiz_sponser($row['QuizID']);
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                    if($is_super_admin || $sponser_id == $user_id) {
                        $option_row = $this->quiz_model->get_single_row("OptionID", QUIZ_OPTIONS, array('OptionGUID' => $data['OptionGUID'], 'QuestionID' => $row['QuestionID']));
                        if($option_row) {
                            $correct_option = $this->quiz_model->get_single_row("OptionID", QUIZ_OPTIONS, array('QuestionID' => $row['QuestionID'], 'IsCorrect' => 1));
                            if($correct_option) {
                                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $this->return['Message'] = lang('prediction_already_processed');
                            } else {
                                $this->quiz_model->update_prediction_results($option_row['OptionID'], $row['QuestionID']);
                                $this->quiz_model->update_prediction_result_status($row['QuestionID'], $data);

                                $queue_data = array('QuizID' => $row['QuizID'], 'QuestionID' => $row['QuestionID'], 'QuestionTitle' => $row['Title'], 'OptionID' => $option_row['OptionID']);
                                //$this->quiz_model->send_notification($queue_data);
                               // initiate_worker_job('send_notification', $queue_data, '', 'quiz');

                                $queue_data = array('QuizID' => $row['QuizID']);
                                //$this->quiz_model->update_quiz_rank($queue_data);
                               // initiate_worker_job('update_quiz_rank', $queue_data, '', 'quiz');   
                            }                        
                        } else {
                            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $this->return['Message'] = sprintf(lang('valid_value'), "option guid");
                        }
                    } else {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = lang('quiz_permission_deny');
                    }  
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = sprintf(lang('valid_value'), "question guid");
                } 
            }    
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }   
        $this->response($this->return);  // Final Output 
    }

    /**
     * Used to get prediction participants
     */
    public function get_prediction_participants_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        $config = array(
            array(
                'field' => 'QuestionGUID',
                'label' => 'question guid',
                'rules' => 'trim|required'
            ),
        );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->return['Message'] = $error;
        } else {  
            $row = $this->quiz_model->get_single_row("QuestionID", QUIZ_QUESTION, array('QuestionGUID' => $data['QuestionGUID']));
            if($row) {  
                $data['QuestionID'] = $row['QuestionID'];
                $this->return['Data'] = $this->quiz_model->get_prediction_participants($data);
            } else {
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = sprintf(lang('valid_value'), "question guid");
            } 
        }   
        $this->response($this->return);  // Final Output 
    }

    function suggestion_get() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        $this->return['Data'] = $this->quiz_model->suggestion($data);
        $this->response($this->return);  // Final Output 
    }
}