<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once 'Cron_model.php';
class Quiz_model extends Cron_model {
    
    public $db_user ;
   
    

    public function __construct() 
    {
       	parent::__construct();
		$this->db	= $this->load->database('db_user', TRUE);
	}

    function update_dashboard_rank()
    {
        $current_date = format_date();
        $this->db->select("
        SUM(IF(QO.is_correct=1 AND QQ.prize_type=2,QQ.prize_value,0)) as coin_sum ,
        SUM(IF(QO.is_correct=1 AND QQ.prize_type=0,QQ.prize_value,0)) as bonus_sum ,
        SUM(IF(QO.is_correct=1 AND QQ.prize_type=1,QQ.prize_value,0)) as real_sum ,
        COUNT(DISTINCT QV.quiz_id) as quiz_played,
        QA.user_id,
        RANK() OVER (ORDER BY SUM(IF(QO.is_correct=1 AND QQ.prize_type=2,QQ.prize_value,0)) DESC) as rank_value,
        '{$current_date}' as added_date,
        '{$current_date}' as updated_date

        ",FALSE)
        ->from(QUIZ_OPTIONS.' QO')
        ->join(QUIZ_ANSWERS.' QA','QO.option_id=QA.option_id')
        ->join(QUIZ_QUESTION.' QQ','QQ.question_id=QO.question_id')
        ->join(QUIZ.' Q','Q.quiz_id=QQ.quiz_id')
        ->join(QUIZ_VISIT_BY_USER.' QV','QV.user_id=QA.user_id')
        ->where_in('Q.status',[0,2]);

        $result = $this->db->group_by('QA.user_id')
        ->order_by('coin_sum','DESC')->get()->result_array();

        if(empty($result))
        {
            return 0;
        }
        // echo $this->db->last_query();
        // die('fd');

        $record_list = array_chunk($result,999);

        foreach($record_list as $users)
        {
            $this->replace_into_batch(QUIZ_LEADERBOARD,$users);
        }

        return 1;
    }

    /**
     * This is used to inform user about today quiz
     */
    function notify_user_for_quiz($data) {
        $is_start = $data['is_start'];
        $current_date = format_date('today', 'Y-m-d');
           
        $this->db->select('QZ.quiz_id, QZ.quiz_uid, QZ.scheduled_date, GROUP_CONCAT(IFNULL(QV.user_id,"")) as user_ids',FALSE);
        $this->db->from(QUIZ.' QZ'); 
        $this->db->join(QUIZ_VISIT_BY_USER.' QV','QV.quiz_id=QZ.quiz_id', 'LEFT');
        $this->db->where('QZ.status',0);
        $this->db->where('QZ.scheduled_date', $current_date);      
        $this->db->order_by('QZ.scheduled_date', 'ASC');
        $this->db->limit(1);
         
        $query = $this->db->get();
        $result = array();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $quiz_uid = $result['quiz_uid'];
            $scheduled_date = $result['scheduled_date'];
            $user_ids = trim($result['user_ids']);
            
            if(!empty($quiz_uid)) {
                $this->load->model('notification/Notify_nosql_model');
                $current_date = format_date();
                $this->db->select('U.user_id, U.user_name, IF(AL.device_type=1,GROUP_CONCAT(AL.device_id),"") device_ids,IF(AL.device_type=2,GROUP_CONCAT(AL.device_id),"") ios_device_ids,AL.device_type',false);
                $this->db->from(USER.' U');
                $this->db->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id AND AL.device_id IS NOT NULL');            
                $this->db->where('U.status',1);
                if(!empty($user_ids)) {
                    $user_ids = explode(',', $user_ids);
                    $user_ids[] = 0;
                    $this->db->where_not_in('U.user_id',$user_ids);
                }

                $this->db->group_by('U.user_id');
                $user_details = $this->db->get()->result_array();
                
                $notify_data = array();            
                $notify_data["source_id"] = 0;
                $notify_data["notification_destination"] = 2; //Push
                $notify_data["added_date"] = $current_date;
                $notify_data["modified_date"] = $current_date;
                $notify_data["notification_type"] = 582;     
                $notify_data['custom_notification_subject'] = 'Today\'s Quiz is Live ‼️🕙';
                $notify_data['custom_notification_text'] = 'Click here to play and win rewards. 🪙🪙'; 
                
                $push_notification_data = array(
                    array('title' => 'Challenge them 😈', 'text' => '157 users earned 100% in today\'s Quiz. Can you match them?🤔 Let\'s see 😎😎'),
                    array('title' => 'Challenge them 🤓', 'text' => '2079 users played the quiz already. You are missing out the fun. Play now 😎😎'),
                    array('title' => 'Are you busy? 🤨🤨', 'text' => 'Just checking as you missed your quiz rewards today. Play here. 😇😊')
                );
                $banner_image = 'quiz.png';
                foreach($user_details as $user) {    
                    if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                        continue;
                    } 

                    $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
                    $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';

                    if($is_start == 1) {
                        $push_data = $push_notification_data[array_rand($push_notification_data)];
                        $notify_data['custom_notification_subject'] = $push_data['title'];
                        $notify_data['custom_notification_text'] = $push_data['text'];
                    }
                    
                    $notify_data["user_id"]     = $user['user_id'];
                    $notify_data["content"] 	= json_encode(array('scheduled_date' => $scheduled_date, 'quiz_uid' => $quiz_uid, 'user_id' => $user['user_id'], 'device_ids' => $user['device_ids'], 'banner_image' => $banner_image));
                                    
                    $this->Notify_nosql_model->send_notification($notify_data);
                }  
            }
        }
    }
}