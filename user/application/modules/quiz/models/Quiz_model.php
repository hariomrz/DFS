<?php
/**
 * This model is used for getting and storing quiz related information
 * @package    Quiz_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Quiz_model extends MY_Model {
    public $point_data = array();    
    function __construct() {
        parent::__construct();
        $this->point_data = array(
            'CORRECT' => 10,
            'INCORRECT' => -5        
        );
    }

    
    /**
     * @param type $data
     * @return type quiz details
     */
    function get_quiz($user_id) {      
        
        $current_date = format_date('today', 'Y-m-d');
           
        $this->db->select('QZ.quiz_id, QZ.quiz_uid, QZ.scheduled_date,
        GROUP_CONCAT(IF(QQ.prize_type=2,QQ.prize_value,0)) as qq_coins,QZ.visible_questions',FALSE);
        $this->db->from(QUIZ.' QZ'); 
        $this->db->join(QUIZ_QUESTION.' QQ','QQ.quiz_id=QZ.quiz_id');
        $this->db->where('QZ.status',0);
        $this->db->where('QQ.is_hide',0);
        $this->db->where('QZ.scheduled_date', $current_date);      
        $this->db->order_by('QZ.scheduled_date', 'ASC');
        $this->db->limit(1);
         
        $query = $this->db->get();
        $result = array();
        if ($query->num_rows()) {
            $result = $query->row_array();
            $quiz_id = $result['quiz_id'];
            $result['is_visited'] = $this->is_quiz_visit_by_user($user_id, $quiz_id);
     
            unset($result['quiz_id']);    
        }
                
        return $result;
    }


    function get_questions($quiz_id, $page_no, $page_size) {
        $offset     = get_pagination_offset($page_no, $page_size);

        $this->db->select('question_uid, question_id, IFNULL(question_text,"") as question_text, IFNULL(question_image,"") as question_image, time_cap,prize_type,prize_value');
        $this->db->from(QUIZ_QUESTION);
        $this->db->where('quiz_id', $quiz_id, false);
        $this->db->where('status', 0);
        $this->db->where('is_hide', 0);
        $this->db->order_by('question_id', 'RANDOM');         
        $this->db->limit($page_size, $offset);
        $query = $this->db->get();
        
        $results = $query->result_array();
        
        foreach($results as $key => $result) {            
            $options = $this->get_question_options($result['question_id']);
            $result['options'] = $options;
            unset($result['question_id']);
            $results[$key] = $result;
        }
        return $results;
    }

    /**
     * [get_question_options - Used to get question options]
     * @param  [int] $question_id     			[Question  ID]
     * @return boolean              [description]
     */
    public function get_question_options($question_id) {
        $this->db->select('option_uid, option_text');
        $this->db->from(QUIZ_OPTIONS);
        $this->db->where('question_id', $question_id, false);
        $query = $this->db->get();
        $result = array();
        if ($query->num_rows()) {            
            $result = $query->result_array();
        }
        return $result;        
    }

    /**
     * Used to check if user visited or not particular quiz 
     */
    function is_quiz_visit_by_user($user_id, $quiz_id) {
        $this->db->select('visited_date');
        $this->db->from(QUIZ_VISIT_BY_USER);
        $this->db->where('user_id', $user_id);
        $this->db->where('quiz_id', $quiz_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return 1;
        }
        return 0;
    }


    /**
     * [update_quiz_visit_date update visit date]
     * @param  [int]       $user_id    [User ID]
     */
    function update_quiz_visit_date($quiz_id,$user_id) {
        $this->db->select('visited_date');
        $this->db->from(QUIZ_VISIT_BY_USER);
        $this->db->where('user_id', $user_id);
        $this->db->where('quiz_id', $quiz_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $visited_date_time = format_date();  
        if ($query->num_rows() == 0) {
            $quiz = array();
            $quiz['user_id'] = $user_id;
            $quiz['quiz_id'] = $quiz_id;
            $quiz['visited_date'] = $visited_date_time;                    
            $this->db->insert(QUIZ_VISIT_BY_USER, $quiz);
        }         
    }

    function insert_quiz_answers($quiz_answers)
    {
        $this->table_name = QUIZ_ANSWERS;
        $this->insert_batch($quiz_answers);
    }

    function update_quiz_participants($quiz_id) {
        $this->db->set('total_participants', 'total_participants+1',FALSE);
        $this->db->where('quiz_id', $quiz_id);
        $this->db->update(QUIZ);
        $updated= $this->db->affected_rows();
        return $updated;
    }
    /**
     * @param type $quiz_id
     * @return type quiz details
     */
    function details($quiz_id) {           
        $this->db->select('quiz_uid, scheduled_date, visible_questions, status, total_participants, total_questions, added_date, updated_date');
        $this->db->from(QUIZ);
        $this->db->where('quiz_id', $quiz_id, false);
        $this->db->limit(1);        
        $query = $this->db->get();
        $result = $query->row_array();                
        return $result;
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
     * Used to check user already predicted answer or not
     */
    public function get_user_predicted($question_id, $user_id) {
	  return $result = $this->db->select("Q.QuestionID, UA.OptionID, UA.Point")
		->from(QUIZ_QUESTION.' Q')
	   ->join(QUIZ_OPTIONS.' QO',"QO.QuestionID=Q.QuestionID")
	   ->join(QUIZ_ANSWERS.' UA',"UA.OptionID=QO.OptionID AND UA.UserID=".$user_id)
	   ->where("Q.QuestionID",$question_id)
       ->limit(1)->get()->row_array();
	}

    /**
     * Used to make prediction
     */
	public function make_user_prediction($option, $user_id, $deadline_date) {
        $option_id  = $option['OptionID'];
        $is_correct = $option['IsCorrect'];
        $current_date = '';//get_current_date('%Y-%m-%d %H:%i:%s');

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

		$this->db->insert(QUIZ_ANSWERS,$save_data);
		return $this->db->insert_id();
	}

    /** 
     * Used to update total participant
     */
    public function update_total_participants($question_id) {
		$this->db->set('TotalParticipants', 'TotalParticipants + 1', FALSE);
		$this->db->where('QuestionID', $question_id);
        $this->db->update(QUIZ_QUESTION);
        return $this->db->affected_rows(); 
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

		$this->db->where('QuestionID',$question_id)->update(QUIZ_QUESTION,$update_data);
	}   

    public function get_question_correct_options($question_uids,$option_uids) {
        $all_question_uids_str = implode(',', array_map( function( $n ){ return "'{$n}'"; } ,  $question_uids) );

        $option_uids_uids_str = implode(',', array_map( function( $n ){ return "'{$n}'"; } ,  $option_uids) );
        $this->db->select('QO.question_id,QO.option_id,QO.option_uid,QO.is_correct,QQ.prize_value,QQ.prize_type');
        $this->db->from(QUIZ_OPTIONS.' QO');
        $this->db->join(QUIZ_QUESTION.' QQ','QQ.question_id=QO.question_id');
        $this->db->where_in('QQ.question_uid', $all_question_uids_str,FALSE);
        $this->db->where_in('QO.option_uid', $option_uids_uids_str,FALSE);
       // $this->db->where('QO.is_correct',1);
        $query = $this->db->get();
        $result = array();
        if ($query->num_rows()) {            
            $result = $query->result_array();
        }

        //echo $this->db->last_query();die;
        return $result;        
    }

}