<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* This Class used as REST API for Quiz module
* @category		Controller
* @author		Vinfotech Team
*/
class Quiz extends Common_API_Controller {
    // Class Constructor
    function __construct() {
        parent::__construct();
        $this->load->model(array('quiz/quiz_model'));
    }
	
    /**
     * Used to get Quiz  list
     */
    public function index_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 50);
        $data['UserID'] = $user_id;
        $this->return['Data'] = $this->quiz_model->get_quiz($data);     
        $this->return['Points'] = $this->quiz_model->get_point_data();     
        $this->response($this->return);  // Final Output 
    } 
    
     /**
     * Used to get Quiz  list
     */
    public function details_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'QuizGUID',
                                'label' => 'quiz guid',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $quiz_id = get_detail_by_guid($data['QuizGUID'], 47);
                if($quiz_id) {
                    $data['UserID'] = $user_id;
                    $data['QuizID'] = $quiz_id;
                    $this->return['Data'] = $this->quiz_model->details($data);
                    $this->return['Points'] = $this->quiz_model->get_point_data();
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = sprintf(lang('valid_value'), "quiz guid");
                }                
            }    
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }  
        $this->response($this->return);  // Final Output 
    } 

    /**
     * Used to get quiz short link
     */
    public function short_link_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'QuizGUID',
                    'label' => 'quiz guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                // $short_url = '';
                $short_url = '';
                $quiz = get_detail_by_guid($data['QuizGUID'], 47, 'QuizGUID, Title, Description, SponsorID', 2);
                if($quiz) {
                    $quiz['SponsorProfileURL'] = get_entity_url($quiz['SponsorID'], "User", 1);
                    
                    $short_url = $this->quiz_model->get_short_url($quiz);
                    $short_url = DOMAIN."/".$short_url;               
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                }
                // $return['Data'] = array('url' => $short_url);
                $return['Data'] = array('url' => $short_url);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    function get_predictions_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'QuizGUID',
                                'label' => 'quiz guid',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $quiz_id = get_detail_by_guid($data['QuizGUID'], 47);
                if($quiz_id) {
                    $data['UserID'] = $user_id;
                    $data['QuizID'] = $quiz_id;

                    $page_no = safe_array_key($data, 'PageNo', 1);
                    if($page_no == 1) {
                        $this->quiz_model->update_quiz_visit_date($quiz_id, $user_id);
                    }                   

                    $this->return['Data'] = $this->quiz_model->get_predictions($data);
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = sprintf(lang('valid_value'), "quiz guid");
                }                
            }    
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }   
        $this->response($this->return);  // Final Output 
    }

    /**
     * @method make_prediction_post
     * @uses function to make prediction
     * **/
    function make_prediction_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
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
            )
        );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->return['Message'] = $error;
        } else {  
            $row = $this->quiz_model->get_single_row("Status, QuestionID, EndDate", QUESION, array('QuestionGUID' => $data['QuestionGUID'], 'Status' => 0));
            if($row) {
                if($row['Status'] != 0) {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = 'Sorry, this prediction is closed. Predict other questions.';
                } else {
                    $option_row = $this->quiz_model->get_single_row("OptionID, IsCorrect", QUESIONOPTION, array('OptionGUID' => $data['OptionGUID']));
                    if($option_row) {
                        $user_predicted = $this->quiz_model->get_user_predicted($row['QuestionID'], $user_id);
                        if(!empty($user_predicted)) {
                            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $this->return['Message'] = 'You already predicted for this prediction.';
                        } else {
                            $this->quiz_model->make_user_prediction($option_row, $user_id, $row['EndDate']);
                            $this->quiz_model->update_total_participants($row['QuestionID']);
                        }                        
                    } else {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = sprintf(lang('valid_value'), "option guid");
                    }
                }
            } else {
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = sprintf(lang('valid_value'), "question guid");
            } 
        }   
        $this->response($this->return);  // Final Output 
    }

    /**
     * used to get city news unread count
     */
    function get_unread_prediction_count_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'QuizGUID',
                                'label' => 'quiz guid',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $quiz_id = get_detail_by_guid($data['QuizGUID'], 47);
                $this->return['TotalRecords'] = 0;
                if($quiz_id) {         
                    $this->return['TotalRecords'] = $this->quiz_model->get_unread_question_count($quiz_id, $user_id);    
                }
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }        
        
        $this->response($this->return);
    }
    

    /**
     * @method toggle_follow
     * @function Follow quiz toggle function 
     * ** */
    public function toggle_follow_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'QuizGUID',
                                'label' => 'quiz guid',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $quiz_id = get_detail_by_guid($data['QuizGUID'], 47);
                if($quiz_id) {
                    $is_follow = $this->quiz_model->is_follow($user_id, $quiz_id);                    
                    if($is_follow){ // unfollow 
                        $this->quiz_model->unfollow($user_id, $quiz_id);
                    } else {// follow user
                        $this->quiz_model->follow($user_id, $quiz_id);
                    }
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = sprintf(lang('valid_value'), "quiz guid");
                }                
            }    
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }  
        $this->response($this->return);  // Final Output 
    }

    /**
     * [get quiz leaderboard]
     * @return [array] [list of  user]
     */
    function leaderboard_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'QuizGUID',
                                'label' => 'quiz guid',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $quiz_id = get_detail_by_guid($data['QuizGUID'], 47);
                if($quiz_id) {
                    $this->return['Data'] = $this->quiz_model->leaderboard($quiz_id, $data);
                    $data['UserID'] = $user_id;
                    $self_data = $this->quiz_model->leaderboard($quiz_id, $data);
                    $this->return['SelfData'] = array();
                    if(!empty($self_data)) {
                        $this->return['SelfData'] = $self_data[0];
                    }
                    
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = sprintf(lang('valid_value'), "quiz guid");
                }    
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }  
        $this->response($this->return);  // Final Output 
    }

    /**
     * [get use predicted prediction]
     * @return [array] [list of  user]
     */
    function user_predicted_prediction_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'QuizGUID',
                                'label' => 'quiz guid',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'UserGUID',
                                'label' => 'user guid',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $quiz_id = get_detail_by_guid($data['QuizGUID'], 47);
                if($quiz_id) {
                    $user_id = get_detail_by_guid($data['UserGUID'], 3);
                    if($user_id) {
                        $this->return['Data'] = $this->quiz_model->user_predicted_prediction($quiz_id, $user_id);                        
                    } else {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = sprintf(lang('valid_value'), "user guid");
                    }
                    
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = sprintf(lang('valid_value'), "quiz guid");
                }    
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }  
        $this->response($this->return);  // Final Output 
    }
}