<?php
/**
 * This model is used for getting and storing quiz related information
 * @package    Quiz_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Quiz_model extends MY_Model {
    protected $quiz_following_list = array();
    public $point_data = array();    
    function __construct() {
        parent::__construct();
        $this->point_data = array(
            'CORRECT' => 10,
            'INCORRECT' => -5        
        );
    }

    public function add_question($question_data)
	{
		$this->db->insert(QUIZ_QUESTION,$question_data);
		return $this->db->insert_id();
	}

    public function insert_question_option($option_data)
	{
		$this->db->insert_batch(QUIZ_OPTIONS,$option_data);
		return true;
	}

    function get_point_data() {
        return $this->point_data;
    }

    /**
     * Used to add/update quiz
     */
    function add($data,$quiz_id=0) {
     
        if(!empty($quiz_id)) {
            $this->db->where('quiz_id', $quiz_id);
            $this->db->update(QUIZ, $data);
        } else {
            $data['quiz_uid'] =get_guid();
            $this->db->insert(QUIZ, $data);
            $quiz_id = $this->db->insert_id();
        }
        return $quiz_id;
    }

    function change_question_visibility($visible,$question_id)
    {
		if($visible)
		{
			$this->db->where('question_id',$question_id)
			->update(QUIZ_QUESTION,array('is_hide'=> 0));//visible
		}
		else
		{
			$this->db->where('question_id',$question_id)
			->update(QUIZ_QUESTION,array('is_hide'=> 1));//hide
		
		}
       
       return $this->db->affected_rows();    
	}

    function check_valid_question($question_uid,$current_date)
    {
       $result = $this->db->select('Q.question_id,QZ.quiz_id,QZ.status as quiz_status,QZ.total_participants')
        ->from(QUIZ_QUESTION.' Q')
        ->join(QUIZ.' QZ','QZ.quiz_id=Q.quiz_id')
        ->where('QZ.scheduled_date>',$current_date)
        ->where('Q.status',0)
        ->where('Q.question_uid',$question_uid)
        ->get()->row_array();

        return $result;
    }

     /** 
     * Used to update question options
     */
    function delete_question($data) {
        $this->db->set('status', 1);
		$this->db->where('question_id', $data['question_id']);
		$this->db->update(QUIZ_QUESTION);

        // if($data['ActivityID']) {
        //     $this->db->set('StatusID', 3);
        //     $this->db->where('ActivityID', $data['ActivityID']);
        //     $this->db->update(ACTIVITY);
        // }

        $this->update_total_quiz_question($data['quiz_id'], -1);
		return true;
	}

    public function update_question_option($question_id,$option_data)
	{
		$this->db->where('question_id', $question_id)
		->delete(QUIZ_OPTIONS);
		$this->db->insert_batch(QUIZ_OPTIONS,$option_data);
		return true;
	}

    public function update_question($question_id,$question_data)
	{
		$this->db->where('question_id',$question_id);
		$this->db->update(QUIZ_QUESTION,$question_data);
		return $this->db->affected_rows();
	}

     /** 
     * Used to update question options
     */
    function update_option($options, $question_id) {
		$this->db->where('QuestionID', $question_id)
		->delete(QUIZ_OPTIONS);
		$this->insert_option($options, $question_id);
		return true;
	}

   

    function update_total_quiz_question($quiz_id, $count=1) {
        $set_field = 'total_questions';
        $this->db->where('quiz_id', $quiz_id);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->update(QUIZ);
    }
  

    /** 
     * Used to delete quiz
     */
    function delete($quiz_id) {
       /* $this->db->set('status', 1);
        $this->db->where('quiz_id', $quiz_id, FALSE);
        $this->db->update(QUIZ_QUESTION);
*/
$current_date = format_date();
        $this->db->set('status', 4);
        $this->db->set('updated_date', $current_date);
		$this->db->where('quiz_id', $quiz_id, FALSE);
		$this->db->update(QUIZ);
		return true;
	}

    /** 
     * Used to update hold status for quiz
     */
    function toggle_hold($quiz_id, $status) {
        $current_date = format_date();
        $this->db->set('status', $status);
        $this->db->set('updated_date', $current_date);
		$this->db->where('quiz_id', $quiz_id, FALSE);
		$this->db->update(QUIZ);
		return true;
	}

    function add_rules($quiz_id, $rules) {
        $rules = json_encode($rules);
        $this->db->set('Rules', $rules);
		$this->db->where('QuizID', $quiz_id);
		$this->db->update(QUIZ);
    } 


   


    
    /**
     * @param type $data
     * @return type quiz details
     */
    function get_quiz($data) {
        $offset = 0;
        $page_no = safe_array_key($data, 'current_page', 0);
        $page_size = safe_array_key($data, 'items_perpage', 20);
        $filter = safe_array_key($data, 'filter', 2);
        $count_only = safe_array_key($data, 'count_only', 0);
        if($page_no) {
			$page_no = $page_no-1;
		}
        
        $current_date = format_date('today', 'Y-m-d');
           
        $this->db->select('quiz_uid, quiz_id, scheduled_date, IF(visible_questions<total_questions,visible_questions,total_questions) as visible_questions, total_questions, added_date, updated_date,total_participants,status',FALSE);
        $this->db->from(QUIZ);
        if($filter == 2) { // Live
            $this->db->where_in('status',[0,1]);
            $this->db->where('scheduled_date', $current_date);      
            $this->db->order_by('scheduled_date', 'ASC');

            $page_size = 1;
        } else if($filter == 1) { // Completed
            $this->db->group_start();

            $this->db->group_start();
            $this->db->where('scheduled_date < ', $current_date);             
            $this->db->where_in('status',array(0,1,3));
            $this->db->group_end();
            
            $this->db->or_where('status', 2);
            $this->db->group_end();

            $this->db->order_by('scheduled_date', 'DESC');
            
        } else { // Upcoming
            $this->db->where('scheduled_date > ', $current_date);                  
            $this->db->where_in('status',array(0,1));

            $this->db->order_by('scheduled_date', 'ASC');
        }
              
        if (!$count_only) { 
            $offset	= $page_size * $page_no;
            $this->db->limit($page_size,$offset);
        }
         
        $query = $this->db->get();
        if ($count_only) {
            return $query->num_rows();
        }
        //echo $this->db->last_query();die;
        $results = $query->result_array();
        $i = 0;
        foreach($results as $key => $result) {
            $quiz_id = $result['quiz_id'];
            $result['questions'] = [];
            if(empty($page_no) && $i == 0) {
                $result['questions'] = $this->get_questions($quiz_id);
            }
            ++$i;
            unset($result['quiz_id']);
            $results[$key]= $result;       
        }
                
        return $results;
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


    
    /**
     * Used to add/update question
     */
    function add_prediction($data) {
        $description = safe_array_key($data, 'Description', '');
        $question_id = safe_array_key($data, 'QuestionID', 0);
        $current_date = '';//get_current_date('%Y-%m-%d %H:%i:%s'); 
        $insert_data = array(
                            'Title' => $data['Title'],
                            'Description' => $description,
                            'EndDate' => $this->convert_date_time_in_utc($data['EndDate']),
                            'ModifiedDate' => $current_date
                        );
        
        if(!empty($question_id)) {
            $this->db->where('QuestionID', $question_id);
            $this->db->update(QUIZ_QUESTION, $insert_data);
        } else {
           // $insert_data['QuestionGUID'] = get_guid();
            $insert_data['CreatedDate'] = $current_date;
            $insert_data['QuizID'] = $data['QuizID'];
            $this->db->insert(QUIZ_QUESTION, $insert_data);
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
        $current_date = '';//get_current_date('%Y-%m-%d %H:%i:%s');
        foreach ($options as $key => $value) {
            if(empty($value)) {
                continue;
            }    

            $option_data[] = array(
                'OptionGUID'         => '',//get_guid(),
                'Title'         => $value,
                'QuestionID'    => $question_id,
                'CreatedDate'   => $current_date,
                'ModifiedDate'  => $current_date
            );
        }
        if($option_data) {
            $this->db->insert_batch(QUIZ_OPTIONS,$option_data);
        }   		
		return true;
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


    function get_questions($quiz_id) {

        $this->db->select('question_uid, question_id, IFNULL(question_text,"") as question_text, IFNULL(question_image,"") as question_image, total_user_joined, is_hide, time_cap, prize_type, prize_value');
        $this->db->from(QUIZ_QUESTION);
        $this->db->where('quiz_id', $quiz_id, false);
        $this->db->where('status', 0);
        $this->db->order_by('added_date', 'DESC');             
       
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

     /* [get_prediction_by_id Used to get prediction details]
     * @param  [int] $question_id               [Question  ID]
     * @return boolean              [description]
     */

    public function get_prediction_by_id($question_id, $user_id) {
        $this->db->select('QuestionGUID, Title, EndDate, TotalParticipants, Status');
        $this->db->select('IFNULL(Description,"") as Description', FALSE);
        $this->db->select('IFNULL(ProofDescription,"") as ProofDescription', FALSE);
        $this->db->from(QUIZ_QUESTION);
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
        $this->db->select('option_uid, option_text, is_correct');
        $this->db->from(QUIZ_OPTIONS);
        $this->db->where('question_id', $question_id, false);
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

    // function get_prediction_participants($data) {
    //     $question_id = $data['QuestionID'];
    //     $page_no = safe_array_key($data, 'PageNo', 1);
    //     $page_size = safe_array_key($data, 'PageSize', 50);

    //     $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID");
    //     $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);

    //     $this->db->select('QO.OptionGUID, QO.Title');

    //     $this->db->from(USERANSWER." UA");
    //     $this->db->join(QUESIONOPTION . ' QO', 'QO.OptionID=UA.OptionID');
    //     $this->db->join(QUESION . ' Q', 'Q.QuestionID=QO.QuestionID AND Q.QuestionID='.$question_id); 
    //     $this->db->join(USERS . ' U', 'U.UserID = UA.UserID');        
    //     $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');

    //     if ($page_no && $page_size) {
    //         $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
    //     }
    //     $this->db->group_by('UA.UserID');
    //     $query = $this->db->get();
    //     $results = array();
    //     if ($query->num_rows() > 0) {
    //         $results = $query->result_array();
    //     }
    //     return $results;
    // }

    /** 
     * Used to get user predicted question for particular quiz
     */
   
      /**
     * @method get_coin_distributed_graph
     * @since Dec 2019
     * @uses function get reward history
     * @param Array $_POST status
     * @return json
     * ***/
    function get_quiz_participation_graph($post)
    {
      
        
        $this->db->select('DATE_FORMAT(Q.scheduled_date,"%Y-%m-%d") as main_date,COUNT(DISTINCT QA.user_id) as data_value',FALSE)
        ->from(QUIZ_OPTIONS.' QO')
        ->join(QUIZ_ANSWERS.' QA','QO.option_id=QA.option_id')
        ->join(QUIZ_QUESTION.' QQ','QQ.question_id=QO.question_id')
        ->join(QUIZ.' Q','Q.quiz_id=QQ.quiz_id')
        ->where_in('Q.status',[0,2]);

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(Q.scheduled_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(Q.scheduled_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db->group_by('main_date')
        ->order_by('main_date','ASC')
        ->get()
        ->result_array();

        if(!empty($post['is_debug']) && $post['is_debug']=='1')
        {
            echo "<pre>";
            echo $this->db->last_query();
            die('dfdf');
        }
        //echo $this->db->last_query();die;
        return  array('result' => $result,
        // 'total' => $count,
        // 'total_coins_distributed'=>$total_coins_distributed
    );    
    }
    

    function get_quiz_question_count($post)
    {
        $this->db->select('IF(Q.visible_questions<COUNT(DISTINCT QQ.question_id),Q.visible_questions,COUNT(DISTINCT QQ.question_id)) as question_count,Q.quiz_id',FALSE)
        ->from(QUIZ.' Q')
        ->join(QUIZ_QUESTION.' QQ','Q.quiz_id=QQ.quiz_id')
        ->where_in('Q.status',0);

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(Q.scheduled_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(Q.scheduled_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db->group_by('Q.quiz_id')
       
        ->get()
        ->result_array();

        if(!empty($post['is_debug']) && $post['is_debug']=='1')
        {
            echo "<pre>";
            echo $this->db->last_query();
            die('dfdf');
        }

        $question_count = 0;
        if(!empty($result))
        {
            $question_count = array_sum(array_column($result,'question_count'));
        }
        return $question_count;

    }

    function get_live_quiz_graph_c_vs_u()
    {
       
        $this->db->select('SUM(IF(QQ.prize_type =2 AND QO.is_correct=1 AND QO.option_id=QA.option_id,QQ.prize_value,0)) as value,QA.user_id,0 as data_value',FALSE)
        ->from(QUIZ_VISIT_BY_USER.' QV')
        ->join(QUIZ.' Q','Q.quiz_id=QV.quiz_id')
        ->join(QUIZ_QUESTION.' QQ','Q.quiz_id=QQ.quiz_id')
        ->join(QUIZ_OPTIONS.' QO','QQ.question_id=QO.question_id')
        ->join(QUIZ_ANSWERS.' QA','QO.option_id=QA.option_id AND QV.user_id=QA.user_id','LEFT')
        ->where('Q.status',0)
        ->where('QA.user_id IS NOT NULL')
        ->where('Q.scheduled_date',format_date('today','Y-m-d'));

       
        $result = $this->db
        ->order_by("QV.visited_date",'ASC')
        ->group_by("QA.user_id")
        ->get()
        ->result_array();

        $post=$this->input->post();
        if(!empty($post['is_debug']) && $post['is_debug']=='1')
        {
            echo "<pre>";
            echo $this->db->last_query();
            die('dfdf');
        }
        //echo $this->db->last_query();die;
        return  array('result' => $result,
        // 'total' => $count,
        // 'total_coins_distributed'=>$total_coins_distributed
    );    
    }

    function get_visible_questions()
    {
        return $this->db->select('Q.quiz_id,IF(Q.visible_questions<Q.total_questions,Q.visible_questions,IFNULL(Q.total_questions,0)) as visible_questions
        ',FALSE)
        ->from(QUIZ.' Q')
        ->where('Q.status',0)
        ->where('Q.scheduled_date',format_date('today','Y-m-d'))->get()
        ->row_array();
    }    


    function get_live_quiz_correct_answer_count()
    {
        $post=$this->input->post();
        $this->db->select('SUM(IF(QO.option_id=QA.option_id AND QO.is_correct=1,1,0)) as correct_questions,QV.user_id,Q.quiz_id,IF(visible_questions<total_questions,visible_questions,total_questions) as visible_questions
        ',FALSE)
        ->from(QUIZ_VISIT_BY_USER.' QV')
        ->join(QUIZ.' Q','Q.quiz_id=QV.quiz_id')
        ->join(QUIZ_QUESTION.' QQ','Q.quiz_id=QQ.quiz_id')
        ->join(QUIZ_OPTIONS.' QO','QQ.question_id=QO.question_id')
        ->join(QUIZ_ANSWERS.' QA','QO.option_id=QA.option_id AND QV.user_id=QA.user_id','LEFT')
        ->where('Q.status',0)
        ->where('Q.scheduled_date',format_date('today','Y-m-d'));

       
        $result = $this->db
        ->order_by("correct_questions",'ASC')
        ->group_by("QV.user_id")
        ->get()
        ->result_array();

        if(!empty($post['is_debug']) && $post['is_debug']=='1')
        {
            echo "<pre>";
            echo $this->db->last_query();
            die('dfdf');
        }
        // echo "<pre>";
        // echo $this->db->last_query();
        // die('dfdf');
        //echo $this->db->last_query();die;
        return  array('result' => $result,
        // 'total' => $count,
        // 'total_coins_distributed'=>$total_coins_distributed
    );    
    }

    function get_quiz_correct_answer_count($post)
    {
       /**
        SELECT QO.is_correct, QA.user_id,QQ.question_id
FROM `vi_quiz_options` `QO`
JOIN `vi_quiz_answers` `QA` ON `QO`.`option_id`=`QA`.`option_id`
JOIN `vi_quiz_question` `QQ` ON `QQ`.`question_id`=`QO`.`question_id`
JOIN `vi_quiz` `Q` ON `Q`.`quiz_id`=`QQ`.`quiz_id`
WHERE `Q`.`status` = 0
AND DATE_FORMAT(Q.scheduled_date, '%Y-%m-%d') >= '2021-10-01' and DATE_FORMAT(Q.scheduled_date, '%Y-%m-%d') <= '2021-10-18' 
GROUP BY `QA`.`user_id` 
        **/
        
        $this->db->select('QO.is_correct,QA.user_id,QQ.question_id,Q.visible_questions,Q.quiz_id,QA.prize_data
        ',FALSE)
        ->from(QUIZ_OPTIONS.' QO')
        ->join(QUIZ_ANSWERS.' QA','QO.option_id=QA.option_id')
        ->join(QUIZ_QUESTION.' QQ','QQ.question_id=QO.question_id')
        ->join(QUIZ.' Q','Q.quiz_id=QQ.quiz_id')
        ->where('Q.status',0);
        
        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(Q.scheduled_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(Q.scheduled_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}
       
        $result = $this->db
        ->get()
        ->result_array();

        // echo "<pre>";
        // echo $this->db->last_query();
        // die('dfdf');
        //echo $this->db->last_query();die;
        return  array('result' => $result,
        // 'total' => $count,
        // 'total_coins_distributed'=>$total_coins_distributed
    );    
    }

    function get_quiz_leaderboard_old($post)
    {
		$limit		= 50;
		$page		= 0;

        if(isset($post['items_perpage']) && $post['items_perpage'])
		{
			$limit = $post['items_perpage'];
		}

		if(isset($post['current_page']) && $post['current_page'])
		{
			$page = $post['current_page']-1;
		}

        $offset	= $limit * $page;

        $this->db->select('SUM(IF(QO.is_correct=1 AND QQ.prize_type=2,QQ.prize_value,0)) as winnings ,QA.user_id,COUNT(DISTINCT Q.quiz_id) as quiz_played,QQ.prize_type,QA.prize_data,
        RANK() OVER (ORDER BY SUM(IF(QO.is_correct=1 AND QQ.prize_type=2,QQ.prize_value,0)) DESC) as rank_value,U.user_name,CONCAT_WS(U.first_name," ",U.last_name) as full_name,U.user_unique_id
        ',FALSE)
        ->from(QUIZ_OPTIONS.' QO')
        ->join(QUIZ_ANSWERS.' QA','QO.option_id=QA.option_id')
        ->join(QUIZ_QUESTION.' QQ','QQ.question_id=QO.question_id')
        ->join(QUIZ.' Q','Q.quiz_id=QQ.quiz_id')
        ->join(USER.' U','QA.user_id=U.user_id')

        ->where('Q.status',0);
        
        // if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		// {
		// 	$this->db->where("DATE_FORMAT(Q.scheduled_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(Q.scheduled_date, '%Y-%m-%d') <= '".$post['to_date']."' ");
		// }

        if(isset($post['keyword']) && $post['keyword'] != "")
		{
			$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,"")))', strtolower($post['keyword']) );
		}
       
        $result = $this->db->group_by('QA.user_id')
        ->order_by('winnings','DESC');

        $tempdb = clone $this->db; //to get rows for pagination
		$tempdb = $tempdb->select("count(*) as total");
		$temp_q = $tempdb->get();
        $num_rows = $temp_q->num_rows(); 
        $total = isset($num_rows) ? $num_rows : 0; 

		$result = array();
		$sql = $this->db->limit($limit,$offset)
							->get();
			$result	= $sql->result_array();
			$result=($result)?$result:array();

           //echo $this->db->last_query();die();
		
		return array('result'=>$result,'total'=>$total);
    }

    function get_quiz_leaderboard($post)
    {
		$limit		= 50;
		$page		= 0;

        if(isset($post['items_perpage']) && $post['items_perpage'])
		{
			$limit = $post['items_perpage'];
		}

		if(isset($post['current_page']) && $post['current_page'])
		{
			$page = $post['current_page']-1;
		}

        $offset	= $limit * $page;

        $this->db->select('QL.coin_sum as winnings ,QL.user_id,QL.quiz_played,
         QL.rank_value,U.user_name,CONCAT_WS(U.first_name," ",U.last_name) as full_name,U.user_unique_id
        ',FALSE)
        ->from(QUIZ_LEADERBOARD.' QL')
        ->join(USER.' U','QL.user_id=U.user_id');

        if(isset($post['keyword']) && $post['keyword'] != "")
		{
			$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,"")))', strtolower($post['keyword']) );
		}
       
        $result = $this->db->group_by('QL.user_id')
        ->order_by('winnings','DESC');

        $tempdb = clone $this->db; //to get rows for pagination
		$tempdb = $tempdb->select("count(*) as total");
		$temp_q = $tempdb->get();
        $num_rows = $temp_q->num_rows(); 
        $total = isset($num_rows) ? $num_rows : 0; 

		$result = array();
		$sql = $this->db->limit($limit,$offset)
							->get();
			$result	= $sql->result_array();
			$result=($result)?$result:array();

           //echo $this->db->last_query();die();
		
		return array('result'=>$result,'total'=>$total);
    }
}