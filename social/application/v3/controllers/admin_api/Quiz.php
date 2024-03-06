<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* This Class used as REST API for Quiz module
* @category		Controller
* @author		Vinfotech Team
*/
class Quiz extends Admin_API_Controller {
    function __construct() { 
        parent::__construct();
        
        $this->load->model(array('admin/login_model', 'quiz/quiz_model'));
        $this->post_data['quz'] = 1;
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
        $this->UserTypeID    = $logged_user_data['Data']['UserTypeID'];

        $this->post_data[AUTH_KEY] = $this->post_data['AdminLoginSessionKey'];
    }
   
	
    /**
     * Used to get Quiz  list
     */
    public function index_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        
        $data['UserID'] = $user_id;
        $data['FromAdmin'] = 1;
        $data['Filter'] = safe_array_key($data, 'Filter', 2);
        $this->load->model(array(                    
            'users/user_model'
        ));
        $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
        if(!$is_super_admin) {
            $data['SponsorID'] = $user_id;
        }
        $this->return['Data'] = $this->quiz_model->get_quiz($data);        
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
                    $data['FromAdmin'] = 1;
                    $data['UserID'] = $user_id;
                    $data['QuizID'] = $quiz_id;
                    $this->return['Data'] = $this->quiz_model->details($data);
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
     * Used to add quiz
     */
    public function add_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'Title',
                                'label' => 'title',
                                'rules' => 'trim|required|max_length[140]'
                            ),
                            array(
                                'field' => 'Description',
                                'label' => 'description',
                                'rules' => 'trim|max_length[250]'
                            ),
                            array(
                                'field' => 'StartDate',
                                'label' => 'start date',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'EndDate',
                                'label' => 'end date',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'SponsorGUID',
                                'label' => 'sponsor guid',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'SponsorAbout',
                                'label' => 'sponsor about',
                                'rules' => 'trim|max_length[250]'
                            ),
                            array(
                                'field' => 'MaximumQuestion',
                                'label' => 'maximum question',
                                'rules' => 'trim|required|integer'
                            ),
                            array(
                                'field' => 'MaximumPost',
                                'label' => 'maximum post',
                                'rules' => 'trim|required|integer'
                            ),
                            array(
                                'field' => 'LogoID',
                                'label' => 'logo image',
                                'rules' => 'trim|required|integer'
                            ),
                            array(
                                'field' => 'PreviewID',
                                'label' => 'preview image',
                                'rules' => 'trim|required|integer'
                            ),
                            array(
                                'field' => 'BannerID',
                                'label' => 'banner image',
                                'rules' => 'trim|required|integer'
                            ),
                            array(
                                'field' => 'AboutImageID',
                                'label' => 'about image',
                                'rules' => 'trim|required|integer'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {               
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $sponser_id = get_detail_by_guid($data['SponsorGUID'], 3);
                    if($sponser_id) {
                        
                        $media_ids = array();
                        $data['LogoID'] = safe_array_key($data, 'LogoID', NULL);
                        if(!empty($data['LogoID'])){
                            $media_ids[] = $data['LogoID'];
                        }

                        $data['PreviewID'] = safe_array_key($data, 'PreviewID', NULL);
                        if(!empty($data['PreviewID'])){
                            $media_ids[] = $data['PreviewID'];
                        }

                        $data['BannerID'] = safe_array_key($data, 'BannerID', NULL);
                        if(!empty($data['BannerID'])){
                            $media_ids[] = $data['BannerID'];
                        }

                        $data['AboutImageID'] = safe_array_key($data, 'AboutImageID', NULL);
                        if(!empty($data['AboutImageID'])){
                            $media_ids[] = $data['AboutImageID'];
                        }
                        
                        $data['SponsorID'] = $sponser_id;
                        $quiz_id = $this->quiz_model->add($data);

                        if(!empty($media_ids)) {
                            $this->quiz_model->update_media_status($media_ids, $quiz_id);
                        }

                    } else {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = sprintf(lang('valid_value'), "sponser guid");
                    }   
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = lang('permission_denied');
                }             
            }    
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }   
        $this->response($this->return);  // Final Output 
    }

    /**
     * Used to update quiz
     */
    public function update_post() {
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
                                'field' => 'Title',
                                'label' => 'title',
                                'rules' => 'trim|required|max_length[140]'
                            ),
                            array(
                                'field' => 'Description',
                                'label' => 'description',
                                'rules' => 'trim|max_length[250]'
                            ),
                            array(
                                'field' => 'StartDate',
                                'label' => 'start date',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'EndDate',
                                'label' => 'end date',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'SponsorAbout',
                                'label' => 'sponsor about',
                                'rules' => 'trim|max_length[250]'
                            ),
                            array(
                                'field' => 'MaximumQuestion',
                                'label' => 'maximum question',
                                'rules' => 'trim|required|integer'
                            ),
                            array(
                                'field' => 'MaximumPost',
                                'label' => 'maximum post',
                                'rules' => 'trim|required|integer'
                            ),
                            array(
                                'field' => 'LogoID',
                                'label' => 'logo image',
                                'rules' => 'trim|required|integer'
                            ),
                            array(
                                'field' => 'PreviewID',
                                'label' => 'preview image',
                                'rules' => 'trim|required|integer'
                            ),
                            array(
                                'field' => 'BannerID',
                                'label' => 'banner image',
                                'rules' => 'trim|required|integer'
                            ),
                            array(
                                'field' => 'AboutImageID',
                                'label' => 'about image',
                                'rules' => 'trim|required|integer'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {               
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $row = $this->quiz_model->get_single_row("QuizID, LogoID, PreviewID, BannerID, AboutImageID, TotalQuestion, TotalPost", QUIZ, array('QuizGUID' => $data['QuizGUID']));
                    if($row) {
                        $quiz_id = $row['QuizID'];
                        $row = $this->quiz_model->get_single_row("TotalParticipants", QUESION, array('QuizID' => $quiz_id, 'TotalParticipants > ' => 0));
                        $total_participants = $row['TotalParticipants'];
                        if($total_participants == 0) {
                            $max_question = safe_array_key($data, 'MaximumQuestion', 0);
                            $max_post = safe_array_key($data, 'MaximumPost', 0); 
                            if($max_question <= $row['TotalQuestion']) {
                                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $this->return['Message'] = "You can not update maximum questions value for this quiz, You have already added ".$row['TotalQuestion']." questions.";
                            } else if($max_post <= $row['TotalPost']) {
                                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $this->return['Message'] = "You can not update maximum post value for this quiz, You have already added ".$row['TotalPost']." post.";
                            } else {

                                $media_ids = array();
                                $data['LogoID'] = safe_array_key($data, 'LogoID', NULL);
                                if(!empty($data['LogoID'])){
                                    $media_ids[] = $data['LogoID'];
                                }

                                $data['PreviewID'] = safe_array_key($data, 'PreviewID', NULL);
                                if(!empty($data['PreviewID'])){
                                    $media_ids[] = $data['PreviewID'];
                                }

                                $data['BannerID'] = safe_array_key($data, 'BannerID', NULL);
                                if(!empty($data['BannerID'])){
                                    $media_ids[] = $data['BannerID'];
                                }

                                $data['AboutImageID'] = safe_array_key($data, 'AboutImageID', NULL);
                                if(!empty($data['AboutImageID'])){
                                    $media_ids[] = $data['AboutImageID'];
                                }

                                $data['QuizID'] = $quiz_id;
                                $this->quiz_model->add($data);

                            
                                $this->quiz_model->update_media_status($media_ids, $quiz_id, 2, 1);
                                if (CACHE_ENABLE) {
                                    $this->cache->delete('quzm_' . $quiz_id);
                                }
                            }

                        } else {
                            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $this->return['Message'] = "You can not update this quiz, It is already started and predicted by few users.";
                        } 
                    } else {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = sprintf(lang('valid_value'), "quiz guid");
                    }   
                } else {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = lang('permission_denied');
                }             
            }    
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }   
        $this->response($this->return);  // Final Output 
    }

    /**
     * Used to delete quiz question
     */
    public function delete_post() {
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
                $row = $this->quiz_model->get_single_row("QuizID", QUIZ, array('QuizGUID' => $data['QuizGUID']));
                if($row) {
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                    if($is_super_admin) {
                        $quiz_id = $row['QuizID'];
                        $row = $this->quiz_model->get_single_row("TotalParticipants, QuestionID", QUESION, array('QuizID' => $quiz_id, 'TotalParticipants > ' => 0));
                        $total_participants = $row['TotalParticipants'];
                        if($total_participants == 0) {
                            $data['UserID'] = $user_id;
                            $data['QuizID'] = $quiz_id;
                            $this->quiz_model->delete($data);
                        } else {
                            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $this->return['Message'] = "You can not delete this quiz, It's question predicted by few users.";
                        }   
                    } else {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = lang('permission_denied');
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

    public function upload_image_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'ImageData',
                                'label' => 'image data',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $image_data['ImageData'] = $data['ImageData'];
                $this->return['Data'] = $this->rawImage_convert($image_data);
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
        }   
        $this->response($this->return);  // Final Output 
    }

    public function rawImage_convert($data) {
        $this->load->model(array('upload_file_model'));
        $fileAllowedArray = array('png','jpg','jpeg','PNG','JPG','JPEG','GIF','gif');
        $image_data = $data['ImageData'];
        foreach($fileAllowedArray as $farr){
            $image_data = str_replace('data:image/'.$farr.';base64,', '', $image_data);
        }
        
        $data['ImageData'] = base64_decode($image_data); 
        $data['Type'] = 'quiz';
        $data['DeviceType'] = 'native';
        $data['ModuleID'] = 47;
        $data['SourceID'] = 1;
        $result = $this->upload_file_model->saveFileFromUrl($data);
        $media = $result['Data'];
        
        return $media;

    }

    /**
     * Used to add quiz rules
     */
    public function add_rules_post() {
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
                                'field' => 'Rules[]',
                                'label' => 'rules(s)',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $rules    = $data['Rules'];
                foreach($rules as $key =>  $rule) {
                    if(strlen($rule['Title']) > 140) {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = 'Rule title length can not be greater than 140 characters.';
                        $this->response($this->return);
                    }

                    if(strlen($rule['Description']) > 500) {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = 'Rule description length can not be greater than 500 characters.';
                        $this->response($this->return);
                    }

                    if(empty(trim($rule['Title']))) {
                        unset($rules[$key]);
                    }
                }

                $rule_count = count($rules);
                if(empty($rule_count)) {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = 'Invalid rules';
                    $this->response($this->return);
                }

                $quiz = get_detail_by_guid($data['QuizGUID'], 47, 'QuizID,SponsorID', 2);
                if($quiz) {
                    $this->quiz_model->add_rules($quiz['QuizID'], $rules);                                        
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

    public function set_prizes_post() {
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
                                'field' => 'AllowPrize',
                                'label' => 'allow prize',
                                'rules' => 'trim|in_list[0,1]'
                            ),
                            array(
                                'field' => 'DistributionDetail[]',
                                'label' => 'distribution detail(s)',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                if($data['AllowPrize'] == 1) {
                    if(empty($data['DistributionDetail'])) {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message']     = "Please provide prize distribution details.";
                        $this->response($this->return);
                    }
                }
                
                $quiz = get_detail_by_guid($data['QuizGUID'], 47, 'QuizID,SponsorID', 2);
                if($quiz) {
                    $this->quiz_model->set_prizes($quiz['QuizID'], $data);                                         
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

    public function announce_winner_post() {
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
                $row = $this->quiz_model->get_single_row("QuizID, Title, SponsorID", QUIZ, array('QuizGUID' => $data['QuizGUID']));
                if($row) {
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                    $sponser_id = $row['SponsorID'];
                    if($is_super_admin || $sponser_id == $user_id) {
                        $quiz_id = $row['QuizID'];
                        if($this->quiz_model->check_all_prediction_for_correct_answer($quiz_id)) {
                            $flag = $this->quiz_model->quiz_prize_distribute($quiz_id);
                            if($flag == 1) {
                                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $this->return['Message'] = "Please set prizes for this quiz.";
                            } else if($flag == 2) {
                                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $this->return['Message'] = "No prediction made for this quiz.";
                            } else {
                                $queue_data = array('QuizID' => $quiz_id);
                                //$this->quiz_model->send_winner_notification($queue_data);
                                initiate_worker_job('winner_notification', $queue_data, '', 'quiz');
                            }
                        } else {
                            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $this->return['Message'] = "First, please mark correct answer for all prediction.";
                        }   
                    } else {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = lang('permission_denied');
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
     * Used to add quiz question
     */
    public function add_prediction_post() {
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
                                'field' => 'Title',
                                'label' => 'title',
                                'rules' => 'trim|required|max_length[140]'
                            ),
                            array(
                                'field' => 'Description',
                                'label' => 'description',
                                'rules' => 'trim|max_length[250]'
                            ),
                            array(
                                'field' => 'Options[]',
                                'label' => 'option(s)',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'EndDate',
                                'label' => 'end date',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {

                $options    = array_column($data['Options'],"text");
                
                foreach($options as $key =>  $option_text) {
                    if(strlen($option_text) > 100) {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = lang('max_option_length');
                        $this->response($this->return);
                    }

                    if(empty(trim($option_text))) {
                        unset($options[$key]);
                    }
                }

                $option_count = count($options);
                if(empty($options) || $option_count < 2 || $option_count > 4 ) {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = 'Invalid options';
                    $this->response($this->return);
                }

                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $quiz = get_detail_by_guid($data['QuizGUID'], 47, 'QuizID,Title,SponsorID,TotalQuestion,MaximumQuestion,Status', 2);
                    if($quiz) {
                        $sponser_id = $quiz['SponsorID'];
                        $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                        if($is_super_admin || $sponser_id == $user_id) { 
                            if($quiz['Status'] == 3) {
                                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $this->return['Message'] = sprintf(lang('valid_value'), "quiz guid");
                            } else if($quiz['Status'] == 2) {
                                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $this->return['Message'] = 'This quiz is completed, So you can not add question.';
                            } else {
                                $total_question = $quiz['TotalQuestion']+1;
                                $maximum_question = $quiz['MaximumQuestion'];
                                if($total_question <= $maximum_question) {
                                    $data['UserID'] = $user_id;
                                    $data['QuizID'] = $quiz['QuizID'];
                                    $question_id = $this->quiz_model->add_prediction($data);
                                    $this->quiz_model->insert_option($options, $question_id);

                                    $queue_data = array('QuizID' => $quiz['QuizID'], 'QuizGUID' => $data['QuizGUID'], 'QuizTitle' => $quiz['Title'], 'QuestionID' => $question_id, 'QuestionTitle' => $data['Title'], 'Type' => 1);
                                    //$this->quiz_model->send_quiz_new_question_notification($queue_data);
                                    initiate_worker_job('new_question_notification', $queue_data, '', 'quiz');

                                } else {
                                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                    $this->return['Message'] = lang('max_allowed_quiz');
                                }
                            }                       
                            
                        } else {
                            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $this->return['Message'] = lang('quiz_permission_deny');
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
     * Used to update quiz question
     */
    public function update_prediction_post() {
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
                                'field' => 'Title',
                                'label' => 'title',
                                'rules' => 'trim|required|max_length[140]'
                            ),
                            array(
                                'field' => 'Description',
                                'label' => 'description',
                                'rules' => 'trim|max_length[250]'
                            ),
                            array(
                                'field' => 'Options[]',
                                'label' => 'option(s)',
                                'rules' => 'trim|required'
                            ),
                            array(
                                'field' => 'EndDate',
                                'label' => 'end date',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {

                $options    = array_column($data['Options'],"text");
                
                foreach($options as $key =>  $option_text) {
                    if(strlen($option_text) > 100) {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = lang('max_option_length');
                        $this->response($this->return);
                    }

                    if(empty(trim($option_text))) {
                        unset($options[$key]);
                    }
                }

                $option_count = count($options);
                if(empty($options) || $option_count < 2 || $option_count > 4 ) {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = 'Invalid options';
                    $this->response($this->return);
                }
                $row = $this->quiz_model->get_single_row("TotalParticipants, QuizID, QuestionID", QUESION, array('QuestionGUID' => $data['QuestionGUID']));
                if($row) {
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $sponser_id = $this->quiz_model->get_quiz_sponser($row['QuizID']);
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                    if($is_super_admin || $sponser_id == $user_id) {
                        $total_participants = $row['TotalParticipants'];
                        if($total_participants == 0) {
                            $data['UserID'] = $user_id;
                            $data['QuestionID'] = $row['QuestionID'];
                            $question_id = $this->quiz_model->add_prediction($data);
                            $this->quiz_model->update_option($options, $question_id);
                        } else {
                            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $this->return['Message'] = "You can not update this question, It is predicted by few users.";
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
     * Used to get prediction
     */
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
                    $data['FromAdmin'] = 1;
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
     * Used to delete quiz question
     */
    public function delete_prediction_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'QuestionGUID',
                                'label' => 'question guid',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $row = $this->quiz_model->get_single_row("TotalParticipants, QuizID, ActivityID, QuestionID, Status", QUESION, array('QuestionGUID' => $data['QuestionGUID']));
                if($row) {
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $sponser_id = $this->quiz_model->get_quiz_sponser($row['QuizID']);
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                    if($is_super_admin || $sponser_id == $user_id) {
                        if($row['Status'] == 2) {
                            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $this->return['Message'] = "You can not delete this question, It is predicted by few users.";
                        } else {
                            $total_participants = $row['TotalParticipants'];
                            if($total_participants == 0) {
                                $data['UserID'] = $user_id;
                                $data['QuestionID'] = $row['QuestionID'];
                                $data['ActivityID'] = $row['ActivityID'];
                                $data['QuizID'] = $row['QuizID'];
                                $this->quiz_model->delete_prediction($data);
                            } else {
                                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $this->return['Message'] = "You can not delete this question, It is predicted by few users.";
                            }   
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
                $row = $this->quiz_model->get_single_row("QuestionID, Title, QuizID", QUESION, array('QuestionGUID' => $data['QuestionGUID'], 'Status' => 0));
                if($row) {
                    $sponser_id = $this->quiz_model->get_quiz_sponser($row['QuizID']);
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                    if($is_super_admin || $sponser_id == $user_id) {
                        $option_row = $this->quiz_model->get_single_row("OptionID", QUESIONOPTION, array('OptionGUID' => $data['OptionGUID'], 'QuestionID' => $row['QuestionID']));
                        if($option_row) {
                            $correct_option = $this->quiz_model->get_single_row("OptionID", QUESIONOPTION, array('QuestionID' => $row['QuestionID'], 'IsCorrect' => 1));
                            if($correct_option) {
                                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $this->return['Message'] = lang('prediction_already_processed');
                            } else {
                                $this->quiz_model->update_prediction_results($option_row['OptionID'], $row['QuestionID']);
                                $this->quiz_model->update_prediction_result_status($row['QuestionID'], $data);

                                $queue_data = array('QuizID' => $row['QuizID'], 'QuestionID' => $row['QuestionID'], 'QuestionTitle' => $row['Title'], 'OptionID' => $option_row['OptionID']);
                                //$this->quiz_model->send_notification($queue_data);
                                initiate_worker_job('send_notification', $queue_data, '', 'quiz');

                                $queue_data = array('QuizID' => $row['QuizID']);
                                //$this->quiz_model->update_quiz_rank($queue_data);
                                initiate_worker_job('update_quiz_rank', $queue_data, '', 'quiz');   
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
            $row = $this->quiz_model->get_single_row("QuestionID", QUESION, array('QuestionGUID' => $data['QuestionGUID']));
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