<?php
/**
 * This model is used for getting and storing quiz related information
 * @package    Quiz_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Quiz_model extends Common_Model {
    protected $quiz_following_list = array();
    public $point_data = array();    
    function __construct() {
        parent::__construct();
        $this->point_data = array(
            'CORRECT' => 10,
            'INCORRECT' => -5        
        );
    }

    function get_point_data() {
        return $this->point_data;
    }

    /**
     * Used to add/update quiz
     */
    function add($data) {
        $quiz_guid = safe_array_key($data, 'QuizGUID', '');
        $quiz_id = safe_array_key($data, 'QuizID', 0);
        $sponsor_about = safe_array_key($data, 'SponsorAbout', '');  
        $description = safe_array_key($data, 'Description', '');
        
        $max_question = safe_array_key($data, 'MaximumQuestion', 0);
        $max_post = safe_array_key($data, 'MaximumPost', 0);      
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s'); 
        $insert_data = array(
                            'Title' => $data['Title'],
                            'StartDate' => $this->convert_date_time_in_utc($data['StartDate']),
                            'EndDate' => $this->convert_date_time_in_utc($data['EndDate']),
                            'Description' => $description,
                            'About' => $sponsor_about,
                            'LogoID' => $data['LogoID'],
                            'PreviewID' => $data['PreviewID'],
                            'BannerID' => $data['BannerID'],
                            'AboutImageID' => $data['AboutImageID'],
                            'MaximumQuestion' => $max_question,
                            'MaximumPost' => $max_post,
                            'ModifiedDate' => $current_date
                        );
        
        if(!empty($quiz_id)) {
            $this->db->where('QuizID', $quiz_id);
            $this->db->update(QUIZ, $insert_data);
        } else {
            $insert_data['QuizGUID'] = $quiz_guid = get_guid();
            $insert_data['CreatedDate'] = $current_date;
            $insert_data['SponsorID'] = $data['SponsorID'];
            $this->db->insert(QUIZ, $insert_data);
            $quiz_id = $this->db->insert_id();
        }
        return $quiz_id;
    }

    /** 
     * Used to delete quiz
     */
    function delete($data) {
        $this->db->set('Status', 3);
		$this->db->where('QuizID', $data['QuizID']);
		$this->db->update(QUIZ);

        $this->db->set('StatusID', 3);
        $this->db->where('ModuleID', 47);
        $this->db->where('ModuleEntityID', $data['QuizID'], FALSE);
        $this->db->update(ACTIVITY);

		return true;
	}

    function add_rules($quiz_id, $rules) {
        $rules = json_encode($rules);
        $this->db->set('Rules', $rules);
		$this->db->where('QuizID', $quiz_id);
		$this->db->update(QUIZ);
    } 


    function set_prizes($quiz_id, $data) {
        $distribution_details    = json_encode($data['DistributionDetail']); //prize_type 1-Real Cash, 2-Voucher, 3- Certificate
        $allow_prize    = $data['AllowPrize'];
        $prize_data = array(
            'DistributionDetail' => $distribution_details,
            'AllowPrize' => $allow_prize,
        );


        $this->db->select('QuizPrizeID');
        $this->db->from(QUIZPRIZE);
        $this->db->where('QuizID', $quiz_id, false);
        $this->db->limit(1);        
        $query = $this->db->get();
        
        if ($query->num_rows()) {
            $result = $query->row_array();
            $quiz_prize_id = $result['QuizPrizeID'];
            $this->db->where('QuizPrizeID', $quiz_prize_id);
            $this->db->update(QUIZPRIZE, $prize_data);
        } else {
            $prize_data['QuizID'] = $quiz_id;
            $this->db->insert(QUIZPRIZE, $prize_data);
        }
    } 



    public function update_media_status($media_ids, $quiz_id=0, $status=2, $delete_existing=0) {    
        if($delete_existing == 1) {
            $update_data = array();
            $update_data['StatusID'] = 3;
            $this->db->where('MediaSectionReferenceID', $quiz_id);
            $this->db->where('MediaSectionID', 15);            
            $this->db->update(MEDIA,$update_data);  
        }   

        if(!empty($media_ids)) {
            $this->db->where_in('MediaID',$media_ids);
            $update_data = array();
            $update_data['StatusID'] = $status;
            if($quiz_id) {
                $update_data['MediaSectionReferenceID'] = $quiz_id;
            }
            
            $this->db->update(MEDIA,$update_data);  
        }
        return true;
    }
    
    /**
     * @param type $data
     * @return type quiz details
     */
    function get_quiz($data) {
        if($this->settings_model->isDisabled(47)) {
            return array();            
        }
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 50);
        $filter = safe_array_key($data, 'Filter', 0);
        $from_admin = safe_array_key($data, 'FromAdmin', 0);
        $sponser_id = safe_array_key($data, 'SponsorID', 0);
        
        $user_id = $data['UserID'];
        $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');
           
        $this->db->select('QuizID, QuizGUID, Title, StartDate, EndDate, SponsorID, About, Status, LogoID, PreviewID, BannerID, AboutImageID, MaximumPost, MaximumQuestion, TotalPost, TotalQuestion, TotalFollowers');
        $this->db->select('IFNULL(Rules,"") as Rules', FALSE);
        $this->db->select('IFNULL(Description,"") as Description', FALSE);
        $this->db->from(QUIZ);

        if(!empty($sponser_id)) {
            $this->db->where('SponsorID', $sponser_id, FALSE);
        }
       
        if($filter == 2) {
            $this->db->where('Status != ',3);
        } else if($filter == 1) {
            $this->db->group_start();

            $this->db->group_start();
            $this->db->where('EndDate <= ', $current_date_time); 
            $this->db->where('Status != ',3);
            $this->db->group_end();
            
            $this->db->or_where('Status', 2);
            $this->db->group_end();
            
        } else {
            $this->db->where('EndDate > ', $current_date_time);                  
            $this->db->where('Status',0);
        }
        

        if ($page_no && $page_size) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        
        $this->db->order_by('StartDate', 'ASC');
        
        
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results = $query->result_array();
        
        foreach($results as $key => $result) {
            $quiz_id = $result['QuizID'];
            $result['cp'] = 0;
            $result['Logo'] = array();
            $result['Preview'] = array();
            $result['Banner'] = array();
            $result['AboutBanner'] = array();
            if(!empty($result['LogoID']) || !empty($result['PreviewID']) || !empty($result['BannerID']) || !empty($result['AboutImageID'])) {
                $quiz_media = $this->quiz_media($quiz_id);
                if(!empty($quiz_media)) {
                    if(!empty($result['LogoID']) && array_key_exists($result['LogoID'], $quiz_media)) {
                        $result['Logo'] = $quiz_media[$result['LogoID']];
                    }
                    if(!empty($result['PreviewID']) && array_key_exists($result['PreviewID'], $quiz_media)) {
                        $result['Preview'] = $quiz_media[$result['PreviewID']];
                    }
                    if(!empty($result['BannerID']) && array_key_exists($result['BannerID'], $quiz_media)) {
                        $result['Banner'] = $quiz_media[$result['BannerID']];
                    }
                    if(!empty($result['AboutImageID']) && array_key_exists($result['AboutImageID'], $quiz_media)) {
                        $result['AboutBanner'] = $quiz_media[$result['AboutImageID']];
                    }
                }
            }
            
            if($user_id == $result['SponsorID'] && $result['TotalPost'] < $result['MaximumPost']) {
                $result['cp'] = 1;
            }

            $result['Prizes'] = $this->get_prediction_prizes($quiz_id);
            $rules = array();
            if(!empty($result['Rules'])) {
                $rules = json_decode($result['Rules'],TRUE);
            }
            $result['Rules'] = $rules;

            if($from_admin == 1) {
                $sponser = get_detail_by_id($result['SponsorID'], 3,'UserGUID, FirstName, LastName', 2);
                $result['Sponsor'] = array('GUID' => $sponser['UserGUID'], 'Name' => $sponser['FirstName'].' '.$sponser['LastName']);
            
                $popup_data = $this->get_notification_popup_data($quiz_id);
                $result['SC'] = $popup_data['SC'];
                $result['NC'] = $popup_data['NC'];
                $result['smsText']            = empty($popup_data['smsText']) ? $result['Title'] : $popup_data['smsText'];
                $result['notificationText']   = empty($popup_data['notificationText']) ? $result['Description'] : $popup_data['notificationText'];
                $result['notificationTitle']  = empty($popup_data['notificationTitle']) ? $result['Title'] : $popup_data['notificationTitle'];
                
            } else {
                $rank_data = $this->user_rank_with_point($user_id, $quiz_id);
                $result['Rank'] = $rank_data['Rank'];
                $result['Point'] = $rank_data['Point'];
                $result['IsFollow'] = 0;
                if($result['TotalFollowers'] > 0) {
                    $result['IsFollow'] = $this->is_follow($user_id, $quiz_id);
                }

                unset($result['LogoID']);
                unset($result['PreviewID']);
                unset($result['BannerID']);
                unset($result['AboutImageID']);
            }           
            
            unset($result['SponsorID']);
            unset($result['QuizID']);
            
            $results[$key]= $result;       
        }
                
        return $results;
    }

    /**
     * @param type $data
     * @return type quiz details
     */
    function details($data) {
        if($this->settings_model->isDisabled(47)) {
            return array();            
        }
        $quiz_id = $data['QuizID'];
        $user_id = $data['UserID'];

        $from_admin = safe_array_key($data, 'FromAdmin', 0);
           
        $this->db->select('QuizID, QuizGUID, Title, StartDate, EndDate, SponsorID, About, Status, LogoID, PreviewID, BannerID, AboutImageID, MaximumPost, MaximumQuestion, TotalPost, TotalQuestion, TotalFollowers');
        $this->db->select('IFNULL(Rules,"") as Rules', FALSE);
        $this->db->select('IFNULL(Description,"") as Description', FALSE);
        $this->db->from(QUIZ);
        $this->db->where('QuizID', $quiz_id, false);
        $this->db->limit(1);
        
        $query = $this->db->get();
        $result = $query->row_array();
        
        if($result) {
            $quiz_id = $result['QuizID'];
            $result['cp'] = 0;
            $result['Logo'] = array();
            $result['Preview'] = array();
            $result['Banner'] = array();
            $result['AboutBanner'] = array();
            if(!empty($result['LogoID']) || !empty($result['PreviewID']) || !empty($result['BannerID']) || !empty($result['AboutImageID'])) {
                $quiz_media = $this->quiz_media($quiz_id);
                if(!empty($quiz_media)) {
                    if(!empty($result['LogoID']) && array_key_exists($result['LogoID'], $quiz_media)) {
                        $result['Logo'] = $quiz_media[$result['LogoID']];
                    }
                    if(!empty($result['PreviewID']) && array_key_exists($result['PreviewID'], $quiz_media)) {
                        $result['Preview'] = $quiz_media[$result['PreviewID']];
                    }
                    if(!empty($result['BannerID']) && array_key_exists($result['BannerID'], $quiz_media)) {
                        $result['Banner'] = $quiz_media[$result['BannerID']];
                    }
                    if(!empty($result['AboutImageID']) && array_key_exists($result['AboutImageID'], $quiz_media)) {
                        $result['AboutBanner'] = $quiz_media[$result['AboutImageID']];
                    }
                }                
            }
            
            

            $result['Prizes'] = $this->get_prediction_prizes($quiz_id);
            $rules = array();
            if(!empty($result['Rules'])) {
                $rules = json_decode($result['Rules'],TRUE);
            }
            $result['Rules'] = $rules;
            $result['IsFollow'] = 0;
            $result['Rank'] = 0;
            $result['Point'] = 0;
            if($user_id) {
                if($user_id == $result['SponsorID'] && $result['TotalPost'] < $result['MaximumPost']) {
                    $result['cp'] = 1;
                }

                $rank_data = $this->user_rank_with_point($user_id, $quiz_id);
                $result['Rank'] = $rank_data['Rank'];
                $result['Point'] = $rank_data['Point'];
                
                if($result['TotalFollowers'] > 0) {
                    $result['IsFollow'] = $this->is_follow($user_id, $quiz_id);
                }
            }
            
            if($from_admin == 1) {
                $sponser = get_detail_by_id($result['SponsorID'], 3,'UserGUID, FirstName, LastName', 2);
                $result['Sponsor'] = array('GUID' => $sponser['UserGUID'], 'Name' => $sponser['FirstName'].' '.$sponser['LastName']);
            
                $popup_data = $this->get_notification_popup_data($quiz_id);
                $result['SC'] = $popup_data['SC'];
                $result['NC'] = $popup_data['NC'];
                $result['smsText']            = empty($popup_data['smsText']) ? $result['Title'] : $popup_data['smsText'];
                $result['notificationText']   = empty($popup_data['notificationText']) ? $result['Description'] : $popup_data['notificationText'];
                $result['notificationTitle']  = empty($popup_data['notificationTitle']) ? $result['Title'] : $popup_data['notificationTitle'];
            } else {
                unset($result['LogoID']);
                unset($result['PreviewID']);
                unset($result['BannerID']);
                unset($result['AboutImageID']);
            }

            unset($result['SponsorID']);
            unset($result['QuizID']);
                  
        }
                
        return $result;
    }

    function get_quiz_sponser($quiz_id) {
        $this->db->select('SponsorID');
        $this->db->from(QUIZ);
        $this->db->where('QuizID', $quiz_id, false);
        $this->db->limit(1);
        
        $query = $this->db->get();
        $result = $query->row_array();
        $sponser_id = 0;
        if($result) {
            $sponser_id = $result['SponsorID'];
        }
        return $sponser_id;
    }

    function get_prediction_prizes($quiz_id) {
        $this->db->select('QuizPrizeID, Name, AllowPrize, DistributionDetail');
        $this->db->from(QUIZPRIZE);
        $this->db->where('QuizID', $quiz_id, false);
        $this->db->limit(1);        
        $query = $this->db->get();
        $result = $query->row_array();
        if($result) {
            if(!empty($result['DistributionDetail'])) {
                $result['DistributionDetail'] = json_decode($result['DistributionDetail'],TRUE);
            }
        }
        return $result;       
    }

    /**
     * Get quiz media
     * @param int $quiz_id
     */
    function quiz_media($quiz_id) {
        $data = array();
        if (CACHE_ENABLE) {
            $data = $this->cache->get('quzm_' . $quiz_id);
        }

        if(empty($data)) {
            $this->db->select('MediaID, MediaGUID, Resolution, ImageName');
            $this->db->from(MEDIA);
            $this->db->where('MediaSectionID', 15);
            $this->db->where('MediaSectionReferenceID', $quiz_id, FALSE);
            $this->db->where('StatusID', 2);
            $query = $this->db->get();
           
            if ($query->num_rows()) {
                $data = $query->result_array();
                $data = array_column($data, null, 'MediaID');
            }
            if (CACHE_ENABLE) {
                $this->cache->save('quzm_' . $quiz_id, $data, CACHE_EXPIRATION);
            }
        }
        return $data;
    }
    
    /**
     * Used to add/update question
     */
    function add_prediction($data) {
        $description = safe_array_key($data, 'Description', '');
        $question_id = safe_array_key($data, 'QuestionID', 0);
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s'); 
        $insert_data = array(
                            'Title' => $data['Title'],
                            'Description' => $description,
                            'EndDate' => $this->convert_date_time_in_utc($data['EndDate']),
                            'ModifiedDate' => $current_date
                        );
        
        if(!empty($question_id)) {
            $this->db->where('QuestionID', $question_id);
            $this->db->update(QUESION, $insert_data);
        } else {
            $insert_data['QuestionGUID'] = get_guid();
            $insert_data['CreatedDate'] = $current_date;
            $insert_data['QuizID'] = $data['QuizID'];
            $this->db->insert(QUESION, $insert_data);
            $question_id = $this->db->insert_id();

            $this->update_total_quiz_question($data['QuizID']);

            $user_data['PostAsModuleID'] = 3;
            $user_data['PostAsModuleEntityID'] = $data['UserID'];
            $user_data['QuestionID'] = $question_id;

            $activity_id = $this->add_quiz_activity($data['QuizID'], $data['UserID'], $user_data);

        }
        return $question_id;
    }
    

    /** 
     * Used to insert question option
     */
	function insert_option($options, $question_id) {
        $option_data = array();
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        foreach ($options as $key => $value) {
            if(empty($value)) {
                continue;
            }    

            $option_data[] = array(
                'OptionGUID'         => get_guid(),
                'Title'         => $value,
                'QuestionID'    => $question_id,
                'CreatedDate'   => $current_date,
                'ModifiedDate'  => $current_date
            );
        }
        if($option_data) {
            $this->db->insert_batch(QUESIONOPTION,$option_data);
        }   		
		return true;
	}

    /** 
     * Used to update question options
     */
    function update_option($options, $question_id) {
		$this->db->where('QuestionID', $question_id)
		->delete(QUESIONOPTION);
		$this->insert_option($options, $question_id);
		return true;
	}

    /** 
     * Used to update question options
     */
    function delete_prediction($data) {
        $this->db->set('Status', 3);
		$this->db->where('QuestionID', $data['QuestionID']);
		$this->db->update(QUESION);

        if($data['ActivityID']) {
            $this->db->set('StatusID', 3);
            $this->db->where('ActivityID', $data['ActivityID']);
            $this->db->update(ACTIVITY);
        }

        $this->update_total_quiz_question($data['QuizID'], -1);
		return true;
	}

    function update_total_quiz_question($quiz_id, $count=1) {
        $set_field = 'TotalQuestion';
        $this->db->where('QuizID', $quiz_id);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->update(QUIZ);
    }

    function convert_date_time_in_utc($date_time) {
        $date_time = $date_time." Asia/Kolkata";
        $date = new DateTime($date_time);
        $tz = new DateTimeZone('UTC');
        $date->setTimezone($tz);
        //print_r($date);die;
        $deadline_date   = $date->format('Y-m-d H:i:s');
        return $deadline_date;
    }


    /**
     * @Function - add quiz activity
     * @Input 	- question_id(int)
     * @Output 	- boolean
     */
    public function add_quiz_activity($quiz_id, $user_id, $module_entity, $content = '') {  
        $question_id = $module_entity['QuestionID'];
        $this->load->model('activity/activity_model');
        $activity_id = $this->activity_model->addActivity(47, $quiz_id, 50, $user_id, 0, $content, 1, 1, array('QuestionID' => $question_id, 'count' => 0), 0, 0, $module_entity['PostAsModuleID'], $module_entity['PostAsModuleEntityID']);

        //Update activityid in poll table
        $this->db->set('ActivityID', $activity_id);
        $this->db->where('QuestionID', $question_id);
        $this->db->update(QUESION);

        return $activity_id;
    }

    function get_predictions($data) {
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 50);
        $from_admin = safe_array_key($data, 'FromAdmin', 0);

        $this->db->select('QuestionGUID, QuestionID, Title, EndDate, TotalParticipants, Status');
        $this->db->select('IFNULL(Description,"") as Description', FALSE);
        $this->db->select('IFNULL(ProofDescription,"") as ProofDescription', FALSE);
        $this->db->from(QUESION);
        $this->db->where('QuizID', $data['QuizID'], false);
        $this->db->where('Status !=', 3);
        $this->db->order_by('EndDate', 'ASC');             
        if ($page_no && $page_size) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $query = $this->db->get();
        
        $results = $query->result_array();
        
        foreach($results as $key => $result) {
            
            $options = $this->get_question_options($result['QuestionID']);

            if(empty($from_admin)) {
                $result['IsPredicted'] = 0;
                $result['Point'] = $this->point_data['CORRECT'];
                $row = $this->get_user_predicted($result['QuestionID'], $data['UserID']);
                if(!empty($row)) {
                    $result['IsPredicted'] = 1;
                }

                foreach ($options as $k => $option) {
                    if($result['IsPredicted'] == 1 && $option['OptionID'] == $row['OptionID']) {
                        $option['YourSelection'] = 1;
                    }
                    if(empty($result['Status'])) {
                        $option['IsCorrect'] = 0;
                    } else if($row['Point']) {
                        $result['Point'] = $row['Point'];
                    }
                    unset($option['OptionID']);
                    $options[$k] = $option;
                }
            }            
            
            $result['Options'] = $options;

            unset($result['QuestionID']);
            $results[$key] = $result;
        }
        return $results;
    }

     /* [get_prediction_by_id Used to get prediction details]
     * @param  [int] $question_id               [Question  ID]
     * @return boolean              [description]
     */

    public function get_prediction_by_id($question_id, $user_id) {
        $this->db->select('QuestionGUID, Title, EndDate, TotalParticipants, Status');
        $this->db->select('IFNULL(Description,"") as Description', FALSE);
        $this->db->select('IFNULL(ProofDescription,"") as ProofDescription', FALSE);
        $this->db->from(QUESION);
        $this->db->where('QuestionID', $question_id, false);
        $this->db->limit(1);
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        $result = array();
        if ($query->num_rows()) {
            $result = $query->row_array();
            $result['Point'] = $this->point_data['CORRECT'];
            $options = $this->get_question_options($question_id);
            $row = $this->get_user_predicted($question_id, $user_id);
            $result['IsPredicted'] = 0;
            if(!empty($row)) {
                $result['IsPredicted'] = 1;
            }
            foreach ($options as $k => $option) {
                if($result['IsPredicted'] == 1 && $option['OptionID'] == $row['OptionID']) {
                    $option['YourSelection'] = 1;
                }
                if(empty($result['Status'])) {
                    $option['IsCorrect'] = 0;
                } else if($row['Point']) {
                    $result['Point'] = $row['Point'];
                }
                unset($option['OptionID']);
                $options[$k] = $option;
            }
            $result['Options'] = $options;
            
        } 
        return $result;
    }

    /**
     * [get_question_options - Used to get question options]
     * @param  [int] $question_id     			[Question  ID]
     * @return boolean              [description]
     */
    public function get_question_options($question_id) {
        $this->db->select('OptionID, OptionGUID, Title, IsCorrect, 0 as YourSelection');
        $this->db->from(QUESIONOPTION);
        $this->db->where('QuestionID', $question_id, false);
        $query = $this->db->get();
        if ($query->num_rows()) {            
            return $query->result_array();
        } else {
            return array();
        }
    }

    /** 
     * Used to check user already predicted answer or not
     */
    public function get_user_predicted($question_id, $user_id) {
	  return $result = $this->db->select("Q.QuestionID, UA.OptionID, UA.Point")
		->from(QUESION.' Q')
	   ->join(QUESIONOPTION.' QO',"QO.QuestionID=Q.QuestionID")
	   ->join(USERANSWER.' UA',"UA.OptionID=QO.OptionID AND UA.UserID=".$user_id)
	   ->where("Q.QuestionID",$question_id)
       ->limit(1)->get()->row_array();
	}

    /**
     * Used to make prediction
     */
	public function make_user_prediction($option, $user_id, $deadline_date) {
        $option_id  = $option['OptionID'];
        $is_correct = $option['IsCorrect'];
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');

        //$point = ($is_correct==1)?$this->point_data['CORRECT']:$this->point_data['INCORRECT'];
        $t1 = strtotime($deadline_date);
        $t2 = strtotime($current_date);

        $t = $t1 - $t2;
        $minutes = floor($t / 60);

        $save_data = array(
            'UserID' => $user_id,
            'OptionID' => $option_id,
            'BonusPoint' => $minutes,
            //'Point' => $point,
            'CreatedDate' => $current_date
        );

		$this->db->insert(USERANSWER,$save_data);
		return $this->db->insert_id();
	}

    /** 
     * Used to update total participant
     */
    public function update_total_participants($question_id) {
		$this->db->set('TotalParticipants', 'TotalParticipants + 1', FALSE);
		$this->db->where('QuestionID', $question_id);
        $this->db->update(QUESION);
        return $this->db->affected_rows(); 
	}


    /**
     * [get_unread_question_count get question unread count]
     * @param  [int]       $user_id    [User ID]
     */
    function get_unread_question_count($quiz_id, $user_id) {
        $this->db->select('VisitedDate');
        $this->db->from(QUIZVISITBYUSER);
        $this->db->where('UserID', $user_id);
        $this->db->where('QuizID', $quiz_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $visited_date_time = '';
        if ($query->num_rows() > 0) {
            $query_data = $query->row_array();
            $visited_date_time = $query_data['VisitedDate'];
        }
                  
        $this->db->select('QuizID');
        $this->db->from(QUESION);
        $this->db->where('QuizID', $quiz_id);
        if(!empty($visited_date_time)) {
            $this->db->where('CreatedDate > ', $visited_date_time);
        }        
        $query = $this->db->get();
        return $query->num_rows();
         
    }

    /**
     * [update_quiz_visit_date update visit date]
     * @param  [int]       $user_id    [User ID]
     */
    function update_quiz_visit_date($quiz_id,$user_id) {
        $this->db->select('VisitedDate');
        $this->db->from(QUIZVISITBYUSER);
        $this->db->where('UserID', $user_id);
        $this->db->where('QuizID', $quiz_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $visited_date_time = get_current_date('%Y-%m-%d %H:%i:%s');  
        if ($query->num_rows() > 0) {
            $this->db->set('VisitedDate', $visited_date_time);
            $this->db->where('UserID', $user_id);
            $this->db->where('QuizID', $quiz_id);
            $this->db->update(QUIZVISITBYUSER);
        } else {
            $quiz = array();
            $quiz['UserID'] = $user_id;
            $quiz['QuizID'] = $quiz_id;
            $quiz['VisitedDate'] = $visited_date_time;                    
            $this->db->insert(QUIZVISITBYUSER, $quiz);
        }
         
    }

    /**
     * Used to set correct option for prediction 
     */
    public function update_prediction_results($option_id, $question_id) {
		$this->db->where('OptionID',$option_id)
       ->update(QUESIONOPTION,array('IsCorrect'=> 1));

       $this->db->where('OptionID',$option_id)
       ->update(USERANSWER,array('Point'=> $this->point_data['CORRECT']));

       $this->db->select('GROUP_CONCAT(OptionID) as OptionIDs');
       $this->db->where('OptionID !=',$option_id);
       $this->db->where('QuestionID',$question_id);
       $query = $this->db->get(QUESIONOPTION);
       $row = $query->row_array();
       if(!empty($row['OptionIDs'])) {
           $option_ids = explode(",",$row['OptionIDs']);
           if(!empty($option_ids)) {
                $this->db->where_in('OptionID',$option_ids)
                ->update(USERANSWER,array('Point'=> $this->point_data['INCORRECT']));
           }
       }
	}
	
    /**
     * Used to update prediction status
     */
    public function update_prediction_result_status($question_id, $data) {
        $description = safe_array_key($data, 'ProofDescription', '');

        $update_data = array(
            "Status"                 => 2,
            "ProofDescription" => $description
        );

		$this->db->where('QuestionID',$question_id)->update(QUESION,$update_data);
	}

    public function send_notification($data) {
        $quiz_id        = safe_array_key($data, 'QuizID', 0);
        $quiz_guid      = get_detail_by_id($quiz_id, 47);
        $question_id        = safe_array_key($data, 'QuestionID', 0);
        $question_title        = safe_array_key($data, 'QuestionTitle', 0);
        $correct_option_id  = safe_array_key($data, 'OptionID', 0);
        if($question_id) {
            $notification_data = array();
            $notification_data['PushNotification'] = array("EntityID" => $quiz_id, "EntityGUID" => $quiz_guid, "Refer" => "QUIZ");
            $notification_data['Summary'] = '';

            $this->db->select('OptionID, Title');
            $this->db->where('QuestionID',$question_id);
            $query = $this->db->get(QUESIONOPTION);
            $results = $query->result_array();                    
            foreach($results as $key => $row) {
                $option_id = $row['OptionID'];
                $this->db->select('GROUP_CONCAT(UserID) as UserIDs');
                $this->db->where('OptionID',$option_id);
                $query = $this->db->get(USERANSWER);
                $result = $query->row_array();
                if(!empty($result['UserIDs'])) {  
                    $user_ids = explode(",",$result['UserIDs']);
                    if(!empty($user_ids)) {
                        $message = "Your pick ".$row['Title']." is wrong for the game ".$question_title.".";
                        
                        if($option_id == $correct_option_id) {
                            $message = "Your pick ".$row['Title']." is correct for the game ".$question_title.".";
                        }


                        $user_ids_chunk = array_chunk($user_ids,500);            
                        $this->db->select("AL.UserID, AL.DeviceToken, AL.DeviceTypeID");
                        $this->db->from(ACTIVELOGINS . ' AL');
                        foreach($user_ids_chunk as $user_ids) {
                            $this->db->or_where_in('AL.UserID', $user_ids);
                        }
                        $this->db->where('AL.IsValidToken', 1);
                        $this->db->where('AL.DeviceToken!=', '');
                        $this->db->group_by('AL.DeviceToken');
                        $this->db->group_by('AL.DeviceTypeID');
                        $this->db->order_by('AL.ActiveLoginID', 'DESC');
                        $query = $this->db->get();
                        if ($query->num_rows() > 0) {
                            $users = $query->result_array();
                            foreach ($users as $notifications) {
                                $token = $notifications['DeviceToken'];                                
                                $notification_data['ToUserID'] = $notifications['UserID'];  
                                $notification_data['DeviceTypeID'] = $notifications['DeviceTypeID'];
                                           
                                send_push_notification($token, $message, 1, $notification_data);                                
                            }  
                        }


                      /*  $notification_type_id = 164;
                        if($option_id == $correct_option_id) {
                            $notification_type_id = 163;
                        }
                        $parameters[0]['ReferenceID'] = $option_id;
                        $parameters[0]['Type'] = 'QuestionOption';
                        $parameters[1]['ReferenceID'] = $question_id;
                        $parameters[1]['Type'] = 'QuizQuestion';

                        initiate_worker_job('add_notification', array(
                            'NotificationTypeID' => $notification_type_id, 
                            'SenderID' => ADMIN_USER_ID, 'ReceiverIDs' => $user_ids, 
                            'RefrenceID' => $question_id, 
                            'Parameters' => $parameters, 
                            'ExtraParams' => array('QuizID' => $quiz_id)),'','notification');
                            */
                    }
                }                
            }
        }
    }

    public function update_quiz_rank($data) { 
        $quiz_id        = safe_array_key($data, 'QuizID', 0);
        $current_date   = safe_array_key($data, 'CurrentDate', '');

        if(empty($current_date)) {
			$current_date = get_current_date('%Y-%m-%d %H:%i:%s');
		}
        $from_date = date('Y-m-d',strtotime($current_date)).' 00:00:00';  //16-03-2021 00:00:00
        $to_date = date('Y-m-d',strtotime($current_date)).' 23:59:59'; //16-03-2021 23:59:59
		        
        $sql = "SELECT UA.UserID, COUNT(DISTINCT Q.QuestionID) as Attempts,
        SUM(IF(Q.QuestionID and QO.IsCorrect=1,1,0) ) as CorrectAnswer, SUM(UA.Point) as TotalPoint, SUM(UA.BonusPoint) as BonusPoint, MIN(AnswerID) as MinAnswerID 
        FROM ".USERANSWER." UA
        INNER JOIN ".QUESIONOPTION." QO ON QO.OptionID=UA.OptionID
        INNER JOIN ".QUESION." Q ON Q.QuestionID=QO.QuestionID  AND Q.QuizID=".$quiz_id." AND Q.Status <> 3     
        GROUP BY UA.UserID
        ORDER BY TotalPoint DESC, BonusPoint DESC, MinAnswerID ASC";
        $query = $this->db->query($sql);
        //AND Q.EndDate>='{$from_date}' AND Q.EndDate<='{$to_date}'
       // echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $results = $query->result_array();
            $rank = 0;
            foreach ($results as $key => $result) {
                ++$rank;
                $result['RankValue'] = $rank;
                $result['QuizID']    = $quiz_id;

                //unset($result['TotalPoint']);
                unset($result['BonusPoint']);
                unset($result['MinAnswerID']);

                $results[$key] = $result;
            }
            if($results) {
                $this->db->insert_on_duplicate_update_batch(LEADERBOARD, $results);
            }            
        }
    } 

    /**
     * used to get user rank
     * @param int $user_id
     * @param int $quiz_id
     * @return int
     */
    function user_rank_with_point($user_id, $quiz_id) {
        $this->db->select('LB.TotalPoint, LB.RankValue');        
        $this->db->from(LEADERBOARD . ' LB'); 
        $this->db->where('LB.QuizID', $quiz_id, FALSE);
        $this->db->where('LB.UserID', $user_id, FALSE);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array();
        $return_data = array('Rank' => '', 'Point' => 0);
        if($result) {
            $return_data['Rank'] = $result['RankValue'];
            $return_data['Point'] = $result['TotalPoint'];
        }
        return $return_data;
    } 

    public function leaderboard($quiz_id, $data) {
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 50);

        $user_id = safe_array_key($data, 'UserID', 0);

        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID, U.UserID");
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
        
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID, LB.TotalPoint as Point, LB.RankValue AS Rank');
        $this->db->select('IFNULL(LB.PrizeData,"") as PrizeData', FALSE);
        $this->db->from(LEADERBOARD . ' LB');  
        $this->db->join(USERS . ' U', 'U.UserID = LB.UserID');        
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID');
       
        $this->db->where('LB.QuizID', $quiz_id, FALSE);
        $this->db->order_by('LB.RankValue', 'ASC');
        
        if(!empty($user_id)) {
            $this->db->where('LB.UserID', $user_id, FALSE);
            $this->db->limit(1);
        } else if ($page_no && $page_size) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }

        $query = $this->db->get();  
        $users = array();     
        if($query->num_rows()) {
            $result = $query->result_array();
            
            foreach ($result as $user){
                if(!empty($user['PrizeData'])) {
                    $user['PrizeData'] = json_decode($user['PrizeData'],TRUE);
                }

                $user['Locality'] = array(
                    "Name" => $user['Name'], 
                    "HindiName"=> $user['HindiName'], 
                    "ShortName"=> $user['ShortName'],  
                    "LocalityID" => $user['LocalityID']);
                
                unset($user['UserID']);
                unset($user['Name']);
                unset($user['HindiName']);
                unset($user['ShortName']);
                unset($user['LocalityID']);
                $users[] = $user;
            }
        }
        return  $users;
    }

    public function update_quiz_rank_old($current_date='', $quiz_id) { 
        if(empty($current_date)) {
			$current_date = get_current_date('%Y-%m-%d %H:%i:%s');
		}
        $from_date = date('Y-m-d',strtotime($current_date)).' 00:00:00';  //16-03-2021 00:00:00
        $to_date = date('Y-m-d',strtotime($current_date)).' 23:59:59'; //16-03-2021 23:59:59

		$day_number = date("z",strtotime($from_date))+1;
		$day_date = date("Y-m-d",strtotime($from_date));

		$day_prize =$this->db->select('*')->from(QUIZPRIZE)
		->where('PrizeCategory',1)
		->where('Status',1)
        ->where('QuizID', $quiz_id, false)
        ->limit(1)
		->get()->row_array();

		if(empty($day_prize['QuizPrizeID'])) {
			return false;
		}

        //check entry in prize_distribution history
		$prize_distribution_history =$this->db->select('*')->from(QUIZPRIZEDISTRIBUTIONHISTORY)
		->where('QuizPrizeID',$day_prize['QuizPrizeID'])
		->where('PrizeDate',$day_date)
		->where('Status',0)
        ->limit(1)
		->get()->row_array();

		if(empty($prize_distribution_history)) {
			//insert
			$prize_distribution_history = array();
			$prize_distribution_history['QuizPrizeID'] = $day_prize['QuizPrizeID'];
			$prize_distribution_history['Name'] = "Day ".$day_number;
			$prize_distribution_history['PrizeDate'] = $day_date;
			$this->db->insert(QUIZPRIZEDISTRIBUTIONHISTORY, $prize_distribution_history);
			$prize_distribution_history['QuizPrizeDistributionHistoryID'] = $this->db->insert_id();
		}

        $prize_distribution_history_id = $prize_distribution_history['QuizPrizeDistributionHistoryID'];
        
        $sql = "SELECT UA.UserID, COUNT(DISTINCT Q.QuestionID) as Attempts,
        SUM(IF(Q.QuestionID and QO.IsCorrect=1,1,0) ) as CorrectAnswer, SUM(UA.Point) as TotalPoint, SUM(UA.BonusPoint) as BonusPoint, MIN(AnswerID) as MinAnswerID, '{$prize_distribution_history_id}' as QuizPrizeDistributionHistoryID 
        FROM ".USERANSWER." UA
        INNER JOIN ".QUESIONOPTION." QO ON QO.OptionID=UA.OptionID
        INNER JOIN ".QUESION." Q ON Q.QuestionID=QO.QuestionID AND Q.EndDate>='{$from_date}' AND Q.EndDate<='{$to_date}' AND Q.QuizID=".$quiz_id." AND Q.Status <> 3     
        GROUP BY UA.UserID
        ORDER BY TotalPoint DESC,BonusPoint DESC,MinAnswerID ASC";
        $query = $this->db->query($sql);
        // echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $results = $query->result_array();
            $rank = 0;
            foreach ($results as $key => $result) {
                ++$rank;
                $result['RankValue'] = $rank;
                $result['QuizID'] = $quiz_id;

                unset($result['TotalPoint']);
                unset($result['BonusPoint']);
                unset($result['MinAnswerID']);

                $results[$key] = $result;
            }
            if($results) {
                $this->db->insert_on_duplicate_update_batch(LEADERBOARD, $results);
            }            
        }
    } 
    
    
    /**
     * Unfollow quiz
     * @param int $user_id
     * @param int $quiz_id 
     */
    public function unfollow($user_id, $quiz_id){
        // delete entry from follow table
        $this->db->where("UserID",$user_id);
        $this->db->where("QuizID",$quiz_id);
        $this->db->delete(QUIZFOLLOW);

        //update followers count for quiz
        $this->db->set('TotalFollowers', 'TotalFollowers-1', FALSE);
        $this->db->where('QuizID',$quiz_id);
        $this->db->update(QUIZ);

        if (CACHE_ENABLE) {
            $this->cache->delete('uqzflw_' . $user_id);
        }
    }

    /**
     * follow quiz
     * @param int $user_id
     * @param int $quiz_id 
     */
    public function follow($user_id, $quiz_id){
        // insert entry in follow table
        $insert_data = array(
                "UserID" => $user_id,
                "QuizID" => $quiz_id,
                "CreatedDate" => get_current_date('%Y-%m-%d %H:%i:%s')
            );
        $this->db->insert(QUIZFOLLOW, $insert_data);

        //update followers count for quiz
        $this->db->set('TotalFollowers', 'TotalFollowers+1', FALSE);
        $this->db->where('QuizID',$quiz_id);
        $this->db->update(QUIZ);
        
        if (CACHE_ENABLE) {
            $this->cache->delete('uqzflw_' . $user_id);
        }
    }

    /**
     * [is_follow This is used to check the status of follow]
     * @param  [int] $user_id   [User Id]
     * @param  [int] $quiz_id   [following quiz Id]
     * @return [int]           [return follow Status]
     */
    function is_follow($user_id, $quiz_id) {
        $this->db->select('QuizFollowID');
        $this->db->from(QUIZFOLLOW);
        $this->db->where("UserID",$user_id);
        $this->db->where("QuizID",$quiz_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return 1; // Following            
        } else {
            return 0; // Not following
        }
    }

    /**
	 * [set_following_quiz_list used to assign following quiz list in variable]
	 * @param type $user_id
	 */
	function set_following_quiz_list($user_id) {
		$this->quiz_following_list = $this->fetch_quiz_following_list($user_id, true);
	}

	/**
	 * [get_following_quiz_list used to return following quiz list]
	 * @return type
	 */
	function get_following_quiz_list() {
		return $this->quiz_following_list;
    }
    
    /** 
     * 
     * @param type $user_id Loggedin user id
     * @param type $array return response as array or not 
     * @return type
     */
	function fetch_quiz_following_list($user_id, $array=false) {
        $following_data = '';
        if (CACHE_ENABLE) {
            $following_data = $this->cache->get('uqzflw_' . $user_id);
            $following_data = trim($following_data);   
        }
                

        $this->db->simple_query('SET SESSION group_concat_max_len=150000');
        
        if (empty($following_data)) {
            //Get Following List
            $this->db->select(' GROUP_CONCAT(QF.QuizID) as following_id');
            $this->db->from(QUIZFOLLOW.' QF');
            $this->db->join(QUIZ.' Q','Q.QuizID=QF.QuizID and Q.Status!=3');
            $this->db->where('QF.Status', 2);
            $this->db->where('QF.UserID', $user_id);
            $this->db->order_by('QF.QuizID', 'ASC');
            $result = $this->db->get();
            //echo $this->user_db->last_query(); die;
            $following_data = -1;
            if ($result->num_rows()) {
                $follow_row = $result->row_array();
                if (!empty($follow_row['following_id'])) {
                    $following_data = $follow_row['following_id'];                        
                }
            }
            if (CACHE_ENABLE) {
                $this->cache->save('uqzflw_' . $user_id, $following_data, CACHE_EXPIRATION);
            }
        }

        $arr = array();
        if($following_data == -1) {
            $following_data = '';
        } else if (!empty($following_data)) {
            $arr = explode(',', $following_data);
            $arr = array_unique($arr);
        }
        
        if ($array) {
            return $arr;                            
        } else {
            return implode(',', $arr);
        }
    }   
    
    function quiz_prize_distribute($quiz_id) {
        
        $prize = $this->db->select('*')->from(QUIZPRIZE)
                    ->where('AllowPrize',1)
                    ->where('Status',1)
                    ->where('QuizID', $quiz_id, false)
                    ->limit(1)
                    ->get()->row_array();

        if(empty($prize['QuizPrizeID'])) {
            return 1;
        }

        if (empty($prize['DistributionDetail'])) {
            return 1;
        }
    
        $wining_amount = (array) json_decode($prize['DistributionDetail'], TRUE);
        $wining_max = array_column($wining_amount, 'max');
        $winner_places = max($wining_max);
        if(empty($winner_places) || $winner_places == NULL || $winner_places == 0){
            return 1;
        }

        $winning_amount_arr = array();
        if(!empty($wining_amount)) {
            foreach($wining_amount as $win_amt) {
                for($i=$win_amt['min']; $i<=$win_amt['max']; $i++) {
                    $winning_amount_arr[$i-1] = array("prize_type"=>$win_amt['prize_type'],"amount"=>$win_amt['amount']);                        
                }
            }
        }

        //check entry in prize_distribution history
        $prize_distribution_history = $this->db->select('*')->from(QUIZPRIZEDISTRIBUTIONHISTORY)
                                        ->where('QuizPrizeID',$prize['QuizPrizeID'])
                                        ->where('Status',0)
                                        ->limit(1)
                                        ->get()->row_array();

        $current_date = get_current_date('%Y-%m-%d %H:%i:%s'); 
        if(empty($prize_distribution_history)) {
            //insert
            $prize_distribution_history = array();
            $prize_distribution_history['QuizPrizeID'] = $prize['QuizPrizeID'];
            $prize_distribution_history['PrizeDate'] = $current_date;
            $this->db->insert(QUIZPRIZEDISTRIBUTIONHISTORY, $prize_distribution_history);
            $prize_distribution_history['QuizPrizeDistributionHistoryID'] = $this->db->insert_id();
        }
        $prize_distribution_history_id = $prize_distribution_history['QuizPrizeDistributionHistoryID'];

        $winners = $this->db->select('L.*', FALSE)
            ->from(LEADERBOARD.' AS L')
            ->where('L.QuizID', $quiz_id, false)
            ->where('L.CorrectAnswer > ', 0)
            ->order_by("L.RankValue","ASC")
            ->limit($winner_places)
            ->get()
            ->result_array();
        //echo "<pre>";print_r($winners);die;
        $is_success = 0;            
        if(!empty($winners)){
            foreach($winners as $key=>$winner){
                if(isset($winning_amount_arr[$key])){
                    $prize_obj = $winning_amount_arr[$key];                       
                    
                    $is_success = 1;
                    //leaderboard table update is_winner 1
                    $this->db->where(array('LeaderBoardID' => $winner['LeaderBoardID']));
                    $this->db->update(LEADERBOARD, array('IsWinner' => '1','PrizeData' => json_encode($prize_obj), 'QuizPrizeDistributionHistoryID' => $prize_distribution_history_id));
                    
                }
            }

            $this->db->where('QuizPrizeDistributionHistoryID', $prize_distribution_history_id);
            $this->db->update(QUIZPRIZEDISTRIBUTIONHISTORY, array('Status' => 3));

            $this->db->where('QuizID', $quiz_id);
            $this->db->update(QUIZ, array('Status' => 2));
            
        } else {
            return 2;
        }
        return 3;        
    }

    function check_all_prediction_for_correct_answer($quiz_id) {
        $this->db->select('QuestionID');
        $this->db->from(QUESION);
        $this->db->where('QuizID', $quiz_id, false);
        $this->db->where_in('Status', array(0,1));
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return 0;
        }
        return 1;
    }

    function get_short_url($quiz) {
        $url = '';
        $default_title = 'title';
        $post_type = 'quiz';
        $quiz_guid = $quiz['QuizGUID'];

        $title = $quiz['Title'];
        if(!$title) {
            $title = strip_tags($quiz['Description']);
            $title = trim(substr($title,0,140), ' ');
        } else {
            $title = trim(substr($title,0,140), ' ');
        }
        if(!$title) {
            $title = $default_title;
        }

        $string = strtolower($title);

        //Make alphanumeric (removes all other characters)
        // $string = preg_replace("/[^a-z0-9_@~.:\s-]/", "", $string);
        $string = preg_replace("/[^a-z0-9\s-]/", "", $string);
        // $string = preg_replace("/[^a-z0-9]+/i", "", $string);

        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);

        $string = preg_replace("/\s+/", "", $string);
        $string = trim($string, "-");
        if (strlen($string) < 6) {
            $string = $quiz['SponsorProfileURL'];
        }

        $url = "$post_type/$string/$quiz_guid";
        return $url;
    }

    function get_prediction_participants($data) {
        $question_id = $data['QuestionID'];
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 50);

        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID");
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);

        $this->db->select('QO.OptionGUID, QO.Title');

        $this->db->from(USERANSWER." UA");
        $this->db->join(QUESIONOPTION . ' QO', 'QO.OptionID=UA.OptionID');
        $this->db->join(QUESION . ' Q', 'Q.QuestionID=QO.QuestionID AND Q.QuestionID='.$question_id); 
        $this->db->join(USERS . ' U', 'U.UserID = UA.UserID');        
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');

        if ($page_no && $page_size) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $this->db->group_by('UA.UserID');
        $query = $this->db->get();
        $results = array();
        if ($query->num_rows() > 0) {
            $results = $query->result_array();
        }
        return $results;
    }

    /** 
     * Used to get user predicted question for particular quiz
     */
    public function user_predicted_prediction($quiz_id, $user_id) {
        $this->db->select('Q.QuestionGUID, Q.QuestionID, Q.Title, Q.EndDate, Q.TotalParticipants, Q.Status');
        $this->db->select('IFNULL(Q.Description,"") as Description', FALSE);
        $this->db->select('IFNULL(Q.ProofDescription,"") as ProofDescription', FALSE);
        $this->db->select("Q.QuestionID, UA.OptionID, UA.Point");
        $this->db->from(QUESION.' Q');
        $this->db->join(QUESIONOPTION.' QO',"QO.QuestionID=Q.QuestionID");
        $this->db->join(USERANSWER.' UA',"UA.OptionID=QO.OptionID AND UA.UserID=".$user_id);
        $this->db->where("Q.QuizID",$quiz_id);
        
        $this->db->order_by('UA.CreatedDate', 'DESC');

        $query = $this->db->get();
        
        $results = $query->result_array();
        
        foreach($results as $key => $result) {
            
            $options = $this->get_question_options($result['QuestionID']);

            $result['IsPredicted'] = 1;
            foreach ($options as $k => $option) {
                if($result['IsPredicted'] == 1 && $option['OptionID'] == $result['OptionID']) {
                    $option['YourSelection'] = 1;
                }
                if(empty($result['Status'])) {
                    $option['IsCorrect'] = 0;
                }
                unset($option['OptionID']);
                $options[$k] = $option;
            }
                        
            
            $result['Options'] = $options;

            unset($result['QuestionID']);
            unset($result['OptionID']);
            $results[$key] = $result;
        }
        return $results;
    }

    /** 
     * Used to get quiz participant
     */
    public function quiz_participant_ids($quiz_id) {
        $this->db->select('GROUP_CONCAT(DISTINCT UA.UserID) AS UserIDs');
        $this->db->from(QUESION.' Q');
        $this->db->join(QUESIONOPTION.' QO',"QO.QuestionID=Q.QuestionID");
        $this->db->join(USERANSWER.' UA',"UA.OptionID=QO.OptionID");
        $this->db->where("Q.QuizID", $quiz_id, false);  
        $query = $this->db->get();  
        $user_ids = array();   
        //log_message('error', "quiz_id: ".$quiz_id);           
        if ($query->num_rows()) {
            $row = $query->row_array();
            if (!empty($row['UserIDs'])) {
                $user_ids_str = $row['UserIDs'];   
                log_message('error', "quiz_participant_ids: ".$user_ids_str);
                $user_ids = explode(',', $user_ids_str);                     
            }
        }
        return $user_ids;
    }

    /**
     * Used to send quiz new question announcement notification
     */
    function send_quiz_new_question_notification($data) {
        $quiz_id        = safe_array_key($data, 'QuizID', 0);
        if($quiz_id) {
            $user_ids = $this->quiz_participant_ids($quiz_id);
            if(!empty($user_ids)) {
                $quiz_guid      = $data['QuizGUID'];
                $quiz_title     = $data['QuizTitle'];
                $type     = $data['Type'];
                $question_id    = safe_array_key($data, 'QuestionID', 0);
                $question_title = safe_array_key($data, 'QuestionTitle', 0);

                $notification_data = array();
                $notification_data['PushNotification'] = array("EntityID" => $quiz_id, "EntityGUID" => $quiz_guid, "Refer" => "QUIZ");
                $notification_data['Summary'] = '';

                $message = "New question is added in the quiz ".$quiz_title." you participated.";
                                
                $user_ids_chunk = array_chunk($user_ids,500);            
                $this->db->select("AL.UserID, AL.DeviceToken, AL.DeviceTypeID");
                $this->db->from(ACTIVELOGINS . ' AL');
                foreach($user_ids_chunk as $user_ids) {
                    $this->db->or_where_in('AL.UserID', $user_ids);
                }
                $this->db->where('AL.IsValidToken', 1);
                $this->db->where('AL.DeviceToken!=', '');
                $this->db->group_by('AL.DeviceToken');
                $this->db->group_by('AL.DeviceTypeID');
                $this->db->order_by('AL.ActiveLoginID', 'DESC');
                $query = $this->db->get();
                //log_message('error', "Query: ".$this->db->last_query());
               // log_message('error', "Message: ".$message);
                if ($query->num_rows() > 0) {
                    $users = $query->result_array();
                    foreach ($users as $notifications) {
                        $token = $notifications['DeviceToken'];                                
                        $notification_data['ToUserID'] = $notifications['UserID'];  
                        $notification_data['DeviceTypeID'] = $notifications['DeviceTypeID'];
                                    
                        send_push_notification($token, $message, 1, $notification_data);                                
                    }  
                }
            }
        }
    }

    /**
     * Used to send quiz winner announcement notification
     */
    function send_winner_notification($data) {
        $quiz_id        = safe_array_key($data, 'QuizID', 0);
        if($quiz_id) {
            $user_ids = $this->quiz_participant_ids($quiz_id);
            if(!empty($user_ids)) {                    
                $notification_type_id = 165;
                $parameters[0]['ReferenceID'] = $quiz_id;
                $parameters[0]['Type'] = 'Quiz';

                initiate_worker_job('add_notification', array(
                    'NotificationTypeID' => $notification_type_id, 
                    'SenderID' => ADMIN_USER_ID, 'ReceiverIDs' => $user_ids, 
                    'RefrenceID' => $quiz_id, 
                    'Parameters' => $parameters, 
                    'ExtraParams' => array()),'','notification');
            }
        }
    }

    function get_notification_popup_data($quiz_id) {        

        $activity['SC'] = 0; 
        $activity['NC'] = 0;        
        $activity['smsText']            = '';
        $activity['notificationText']   = '';
        $activity['notificationTitle']  = '';

        $this->db->select('SmsCount, PushNotificationCount, PushNotificationTitle, PushNotificationText, SmsText, IsReady');
        $this->db->from(ADMINCOMMUNICATION);
        $this->db->where('QuizID', $quiz_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $row = $query->row_array();
            $activity['SC'] = $row['SmsCount']; 
            $activity['NC'] = $row['PushNotificationCount']; 
            $activity['smsText']            = $row['SmsText'];
            $activity['notificationText']   = $row['PushNotificationText'];
            $activity['notificationTitle']  = $row['PushNotificationTitle'];
        }   
        return $activity;     
    }

    /**
     * @param array $data
     * @return array use to get quiz list
     */
    function suggestion($data) {
        if($this->settings_model->isDisabled(47)) {
            return array();            
        }
        $search_keyword = safe_array_key($data, 'SearchKeyword', '');
        
        $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');
           
        $this->db->select('QuizID, QuizGUID, Title as Name, Title as TooltipTitle');
        $this->db->from(QUIZ);
        
        $this->db->where('EndDate > ', $current_date_time);                  
        $this->db->where('Status',0);
       
        if ($search_keyword) {
            $this->current_db->like('Title', $search_keyword);
        }
        
        $this->db->order_by('Title', 'ASC');
        
        
        $query = $this->db->get();
       // echo $this->db->last_query();die;
        $results = $query->result_array();
                
        return $results;
    }
    
    function prediction_participation_reminder() {
        $question_end_date = get_current_date('%Y-%m-%d %H:%i:00', 0.04167, 1); // 1 hour
        $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');
        //log_message('error', "prediction_participation_reminder: ".$current_date_time);
        $this->db->select('GROUP_CONCAT(Q.QuestionID) AS QuestionIDs, Q.QuizID');
        $this->db->from(QUESION.' Q');
        $this->db->JOIN(QUIZ.' QZ',"QZ.QuizID=Q.QuizID AND QZ.Status=0 AND QZ.EndDate > '".$current_date_time."'");
        $this->db->where('Q.EndDate', $question_end_date);
        $this->db->where('Q.Status', 0);
        $this->db->where('Q.NRS', 0);
        $this->db->group_by('Q.QuizID'); 
        $this->db->order_by('Q.QuestionID', 'ASC'); 
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $results = $query->result_array();
            initiate_worker_job('process_prediction_reminder', $results, '', 'pp_reminder'); 
            //$this->process_prediction_reminder($results);
        }
    }

    function process_prediction_reminder($data) {
        log_message('error', "process_prediction_reminder called");
        foreach ($data as $row) {
            $question_ids = $row['QuestionIDs'];
            $quiz_id = $row['QuizID'];
            $user_ids = $this->quiz_participant_ids($quiz_id);
           
            if(!empty($user_ids)) {
                if(!empty($question_ids)) {
                    $question_ids = explode(',', $question_ids);      

                    $this->db->select('Q.QuestionID, Q.QuestionGUID, Q.Title, QZ.QuizGUID, QZ.Title as QuizTitle');
                    $this->db->from(QUESION.' Q');
                    $this->db->JOIN(QUIZ.' QZ',"QZ.QuizID=Q.QuizID AND QZ.Status=0");
                    $this->db->where_in('Q.QuestionID', $question_ids);
                    $this->db->where('Q.Status', 0);
                    $this->db->where('Q.NRS', 0);
                    $this->db->order_by('Q.QuestionID', 'ASC'); 
                    $query = $this->db->get();
                    if ($query->num_rows() > 0) {
                        $questions = $query->result_array();

                        $this->db->where_in('QuestionID',$question_ids);
                        $this->db->update(QUESION, array('NRS' => 1)); //for processing

                        foreach ($questions as $question) {

                            $notification_data = array();
                            $notification_data['PushNotification'] = array("EntityID" => $quiz_id, "EntityGUID" => $question['QuizGUID'], "Refer" => "QUIZ");
                            $notification_data['Summary'] = '';

                            $message = "Predict the answer to the question ".$question['Title']." in quiz ".$question['QuizTitle']." before the deadline.";

                            $notify_users = array();
                            foreach ($user_ids as $key => $value) {
                                $result = $this->get_user_predicted($question['QuestionID'], $value);
                                if(empty($result)) {
                                    $notify_users[] = $value;  
                                }
                            }
                            if(!empty($notify_users)) {
                                $this->db->select("AL.UserID, AL.DeviceToken, AL.DeviceTypeID");
                                $this->db->from(ACTIVELOGINS . ' AL');
                                $this->db->where_in('AL.UserID', $notify_users);
                                
                                $this->db->where('AL.IsValidToken', 1);
                                $this->db->where('AL.DeviceToken!=', '');
                                $this->db->group_by('AL.DeviceToken');
                                $this->db->group_by('AL.DeviceTypeID');
                                $this->db->order_by('AL.ActiveLoginID', 'DESC');
                                $query = $this->db->get();
                                if ($query->num_rows() > 0) {
                                    $users = $query->result_array();
                                    foreach ($users as $notifications) {
                                        $token = $notifications['DeviceToken'];                                
                                        $notification_data['ToUserID'] = $notifications['UserID'];  
                                        $notification_data['DeviceTypeID'] = $notifications['DeviceTypeID'];
                                                    
                                        send_push_notification($token, $message, 1, $notification_data);                                
                                    }  
                                }
                            }
                        }

                        $this->db->where_in('QuestionID',$question_ids);
                        $this->db->update(QUESION, array('NRS' => 2)); //for success
                    }
                }
            }
        }
    }
}