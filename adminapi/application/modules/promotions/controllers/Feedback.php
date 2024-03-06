<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Feedback extends MYREST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Feedback_model');
        
    }

/**
     * @method add_feedback_question
     * @since Dec 2019
     * @uses function to add question
     * @param Array $_POST question ,coins
     * @return json
     * ***/
    public function add_feedback_question_post()
    {
        
        $this->form_validation->set_rules('question', 'Question', 'trim|required');
        $this->form_validation->set_rules('coins', 'Coins', 'trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        $data = array();
        $data['feedback_question_id'] = new_mongo_id();
        $data['question'] = $post['question'];
        $data['coins'] = $post['coins'];
        $data['status'] = 1;
        $data['added_date'] = convert_normal_to_mongo(format_date());
        $data['update_date'] = convert_normal_to_mongo(format_date());

        $this->load->model('auth/Auth_nosql_model');
        $this->Auth_nosql_model->insert_nosql(COLL_FEEDBACK_QUESTIONS,$data);
        
        $this->load->helper('queue_helper');
        $push_data = array('action' => 'feedback');
        add_data_in_queue($push_data, 'notification');

        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['message']     = $this->lang->line('feedback_question_added_success_msg');
        $this->api_response();

    }

    /**
     * @method get_feedback_questions_by_status
     * @since Dec 2019
     * @uses function to get question list
     * @param Array $_POST status
     * @return json
     * ***/
    public function get_feedback_questions_by_status_post()
    {
        $this->form_validation->set_rules('status', 'Status', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }   
        $post = $this->input->post();
        $limit = 30;
        $offset = 0;

        $count = 0;
        $from_date='';
        $question_cond = array();
        if(isset($post['items_perpage']))
        {
            $limit = $post['items_perpage'];
        }

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        if(!empty($post['from_date']))
        {
            $from_date = convert_normal_to_mongo(format_date($post['from_date']));
            // $from_date = date('Y-m-d',strtotime($current_date)).' 00:00:00';
            $to_date = date('Y-m-d',strtotime($post['from_date'])).' 23:59:59';
            $to_date = convert_normal_to_mongo($to_date);
            $question_cond['added_date']['$gte'] = $from_date;
            $question_cond['added_date']['$lte'] = $to_date;
        }

        $page = $post['current_page']-1;
        $offset = $limit * $page;

        $this->load->model('auth/Auth_nosql_model');
         //get history
        $question_cond['status'] = (int)$post['status'];
        $count = $this->Auth_nosql_model->count(COLL_FEEDBACK_QUESTIONS,$question_cond); 
        $questions= $this->Auth_nosql_model->select_nosql(COLL_FEEDBACK_QUESTIONS,$question_cond,$limit,$offset);

        $this->api_response_arry['data']['questions'] = $questions;
        $this->api_response_arry['data']['total']   = $count;
        $this->api_response_arry['data']['next_offset'] = $offset + count($questions);
        $this->api_response();   
    }

     /**
     * @method update_question_status
     * @since Dec 2019
     * @uses function to update question status
     * @param Array $_POST status,feedback_question_id
     * @return json
     * ***/
    public function update_feedback_question_status_post()
    {
        $this->form_validation->set_rules('status', 'status', 'trim|required');
        $this->form_validation->set_rules('feedback_question_id', 'feedback question id', 'trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }   

        $post = $this->input->post();
        $this->load->model('auth/Auth_nosql_model');
        $this->Auth_nosql_model->update_nosql(COLL_FEEDBACK_QUESTIONS,array('feedback_question_id' =>   new_mongo_id($post['feedback_question_id'])),
        array('status' => (int)$post['status'])); 
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = $this->lang->line('question_status_update_success_msg');//"Question status updated.";
        $this->api_response();   

    }

     /**
     * @method update_question
     * @since Dec 2019
     * @uses function to update question 
     * @param Array $_POST status,feedback_question_id
     * @return json
     * ***/
    public function update_feedback_question_post()
    {
        $this->form_validation->set_rules('question', 'Question', 'trim|required');
        $this->form_validation->set_rules('coins', 'coins', 'trim|required');
        $this->form_validation->set_rules('feedback_question_id', 'feedback question id', 'trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }   

        $post = $this->input->post();
        $data = array();
        $data['question'] = $post['question'];
        $data['coins'] = $post['coins'];
        $data['update_date'] = convert_normal_to_mongo(format_date());

        $this->load->model('auth/Auth_nosql_model');
        $this->Auth_nosql_model->update_nosql(COLL_FEEDBACK_QUESTIONS,array('feedback_question_id' =>   new_mongo_id($post['feedback_question_id'])),$data); 
        
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = $this->lang->line('question_update_success_msg');//"Question status updated.";
        $this->api_response();   

    }

    /**
     * @method get_feedback_question_details
     * @since Dec 2019
     * @uses function to get feedback question details with commets
     * @param Array $_POST status,feedback_question_id
     * @return json
     * ***/
    public function get_feedback_question_details_post()
    {
        $this->form_validation->set_rules('feedback_question_id', 'feedback question id', 'trim|required');
        $this->form_validation->set_rules('sort_rating', 'sort rating', 'trim');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }   

        $post = $this->input->post();
        $data = array();

        $this->load->model('auth/Auth_nosql_model');
        $response_data = array();
        $question_cond = array('feedback_question_id' =>   new_mongo_id($post['feedback_question_id']));
        $response_data['question']= $this->Auth_nosql_model->select_one_nosql(COLL_FEEDBACK_QUESTIONS,$question_cond);

        if(empty($response_data['question']))
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('no_valid_question');//"Question status updated.";
            $this->api_response();   
        }

        $limit = 30;
        $offset = 0;

        $count = 0;
        if(isset($post['items_perpage']))
        {
            $limit = $post['items_perpage'];
        }

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        $page = $post['current_page']-1;
        $offset = $limit * $page;
        $question_cond['status'] = 1;

        $sort_field ='update_date' ;
        if(!empty($post['sort_rating']) && in_array($post['sort_rating'],array(5,4,3,2,1)))
        {
            $sort_field ='rating' ;
            $question_cond['rating'] = (int)$post['sort_rating'];
        }
    
        //get qusetion comments
        $response_data['total'] = $this->Auth_nosql_model->count(COLL_FEEDBACK_QUESTION_ANSWERS,$question_cond); 
        $response_data['total_coins_distributed'] =$response_data['total']*$response_data['question']['coins'];
        $response_data['comments']= $this->Auth_nosql_model->select_nosql(COLL_FEEDBACK_QUESTION_ANSWERS,$question_cond,$limit,$offset,array($sort_field=>'desc'));

        if(!empty($response_data['comments']))
        {
            $this->load->model('auth/Auth_model');
            $user_ids = array_unique(array_column($response_data['comments'],'user_id'));
            $user_details = $this->Auth_model->get_users_by_ids($user_ids);
            $user_details = array_column($user_details,NULL,'user_id');
            foreach($response_data['comments'] as &$row)
            {
                $row['username'] = $user_details[$row['user_id']]['user_name'];
                $row['update_date'] = strtotime(convert_mongo_to_normal_date($row['update_date']))*1000 ;
                $row['added_date'] = strtotime(convert_mongo_to_normal_date($row['added_date']))*1000 ;
            }

        }
        $response_data['next_offset'] = $offset + count($response_data['comments']); 
        $this->api_response_arry['data'] = $response_data;
     
        $this->api_response();   
    }

     /**
     * @method get_feedbacks_by_status
     * @since Dec 2019
     * @uses function to get pending commets
     * @param Array $_POST items_perpage,current_page
     * @return json
     * ***/
    public function get_feedbacks_by_status_post()
    {
        $this->form_validation->set_rules('status', 'Status', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }   
        $limit = 30;
        $offset = 0;

        $count = 0;
        $from_date='';
        $question_cond = array();
        $post = $this->input->post();
        if(isset($post['items_perpage']))
        {
            $limit = $post['items_perpage'];
        }

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        if(!empty($post['from_date']))
        {
            $from_date = date('Y-m-d',strtotime($post['from_date'])).' 00:00:00';
            $from_date = convert_normal_to_mongo($from_date);
            $to_date = date('Y-m-d',strtotime($post['from_date'])).' 23:59:59';
            $to_date = convert_normal_to_mongo($to_date);
            $question_cond['added_date']['$gte'] = $from_date;
            $question_cond['added_date']['$lte'] = $to_date;
        }

        $page = $post['current_page']-1;
        $offset = $limit * $page;

        $status_list = explode(',',$post['status']);

        if(count($status_list)>1)
        {
              $in_clause = array();
              foreach ($status_list as $val)
              {
                  if(!empty($val))
                  {
                      $in_clause[]= (int)$val;
                  }
              }              

              $question_cond['status']['$in'] =$in_clause; 

        }
        else{
            $question_cond['status'] = (int)$post['status'];
        }

        //get qusetion comments
        $response_data['total'] = $this->Auth_nosql_model->count(COLL_FEEDBACK_QUESTION_ANSWERS,$question_cond); 
        
        $ops = array(
            array(
                '$match' => $question_cond
            ),
            array(
                '$lookup' => array(
                    'from' => COLL_FEEDBACK_QUESTIONS,
                    'localField' => 'feedback_question_id',
                    'foreignField' => 'feedback_question_id',
                    'as' => 'question_detail'
                )
            ),
            array(
                '$sort' => array('update_date' => -1)// Other example option
            ),
            array('$skip' => (int)$offset),  
            array(
                '$limit' => (int)$limit,// Example option  
            ),
             
        );

        $this->load->model('auth/Auth_nosql_model');
      
        $response_data['comments']= $this->Auth_nosql_model->aggregate(COLL_FEEDBACK_QUESTION_ANSWERS,$ops);

       

        if(!empty($response_data['comments']))
        {
            $this->load->model('auth/Auth_model');
            $user_ids = array_unique(array_column($response_data['comments'],'user_id'));
            $user_details = $this->Auth_model->get_users_by_ids($user_ids);
            $user_details = array_column($user_details,NULL,'user_id');
            foreach($response_data['comments'] as &$row)
            {
                $row['username'] = $user_details[$row['user_id']]['user_name'];
                $row['added_date'] =strtotime(convert_mongo_to_normal_date($row['added_date']))  ;
                $row['update_date'] = strtotime(convert_mongo_to_normal_date($row['update_date'])) ;
            }

        }
       
        $response_data['next_offset'] = $offset + count($response_data['comments']); 
        $this->api_response_arry['data'] = $response_data;
     
        $this->api_response();   
    }


    /**
     * @method rate_feedback
     * @since Dec 2019
     * @uses function to rate feedback commet
     * @param Array $_POST feedback_question_answer_id,rating
     * @return json
     **/
    public function rate_feedback_post()
    {
        $this->form_validation->set_rules('feedback_question_answer_id', 'feedback question answer id', 'trim|required');
        $this->form_validation->set_rules('rating', 'rating', 'trim|required|callback_validate_rating');

        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }   

        $post = $this->input->post();
        $data = array();

        $this->load->model('auth/Auth_nosql_model');
        $this->Auth_nosql_model->update_nosql(COLL_FEEDBACK_QUESTION_ANSWERS,array('feedback_question_answer_id' =>   new_mongo_id($post['feedback_question_answer_id'])),
        array('rating' => (int)$post['rating'])); 

        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = $this->lang->line('feedback_rating_success_msg');//"Question status updated.";
        $this->api_response();   
    }

     /**
     * @method validate_rating
     * @since Dec 2019
     * @uses function to validate rating
     * @param Array $_POST rating
     * @return Boolean
     **/
    public function validate_rating()
    {
        $rating = $this->input->post('rating');
        if(ctype_digit($rating) && in_array($rating,array(1,2,3,4,5)))
        {
            return TRUE;
        }

        $this->form_validation->set_message('validate_rating', 'Please enter valid rating value');
        return FALSE;
    }

    /**
     * @method update_feedback_status
     * @since Dec 2019
     * @uses function to update feedback status
     * @param Array $_POST rating
     * @return Boolean
     **/
    function update_feedback_status_post()
    {
        $this->form_validation->set_rules('feedback_question_answer_id', 'feedback question answer id', 'trim|required');
        $this->form_validation->set_rules('status', 'Status', 'trim|required');

        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }   

        $post = $this->input->post();
        $data = array();

        $feedback_cond = array('feedback_question_answer_id' =>   new_mongo_id($post['feedback_question_answer_id']));
        $feedback= $this->Auth_nosql_model->select_one_nosql(COLL_FEEDBACK_QUESTION_ANSWERS,$feedback_cond);

        if(empty($feedback))
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('no_valid_feedback');
            $this->api_response();   
        }

        if($feedback['status'] == 1)
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('feedback_already_approved');
            $this->api_response();   
        }

        //get question details
        $question_cond = array('feedback_question_id' =>   new_mongo_id($feedback['feedback_question_id']));
        $question= $this->Auth_nosql_model->select_one_nosql(COLL_FEEDBACK_QUESTIONS,$question_cond);
        if(empty($question))
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('no_valid_question');
            $this->api_response();   
        }

        //update status and add coins
        $this->Auth_nosql_model->update_nosql(COLL_FEEDBACK_QUESTION_ANSWERS,array('feedback_question_answer_id' =>   new_mongo_id($post['feedback_question_answer_id'])
        ),
        array('status' => (int)$post['status'],
        'update_date' =>  convert_normal_to_mongo(format_date())
    )); 

        if($post['status'] == 1 && $question['coins'] > 0)
        {
            $this->load->model('userfinance/Userfinance_model');
            $user_detail = $this->Userfinance_model->get_single_row('user_name,user_id,email',USER,array('user_id' => $feedback['user_id']));
            $order_object = array();
            $order_object['user_id']    = $user_detail['user_id'];
            $order_object['user_name']  = $user_detail['user_name'];
            $order_object['email']  = $user_detail['email'];
            $order_object['amount']     = $question['coins'];
            
            $order_object['cash_type'] = 2;
            $order_object['source']     = 151;
            $order_object['source_id']  = 0;
            $order_object['plateform']  = 1;
            $order_object['reason'] = '';

            
            $push_notification_text = array(
                    'We never let our users go empty handed. Thank you for your feedback. We credited your wallet. 🪙',
                    'We need you more. Thank you for reporting the bug and making our app better. We left something for you in your wallet. 🪙'
                );
            $order_object['banner_image'] = 'feedback_approved.png';    
            $order_object['custom_notification_subject'] = 'Approved!';
            $order_object['custom_notification_text'] = $push_notification_text[array_rand($push_notification_text)];
            $order_object['notification_destination'] = 3;
            $this->load->model('notification/Notification_model');
            $device_ids = $this->Notification_model->get_all_device_id(array($user_detail['user_id']));
            $android_device_ids = $ios_device_ids = array();
            foreach ($device_ids as $key => $single_id) {
                if (isset($single_id['device_type']) && $single_id['device_type'] == '1') {
                    $android_device_ids[] = $single_id['device_id'];
                }

                if (isset($single_id['device_type']) && $single_id['device_type'] == '2') {
                    $ios_device_ids[] = $single_id['device_id'];
                }
            }
            $order_object['device_ids'] = $android_device_ids;
            $order_object["ios_device_ids"] = $ios_device_ids;            
            $order_id =  $this->Userfinance_model->deposit_any_fund($order_object);
            $user_cache_key = "user_balance_".$user_detail['user_id'];
            $this->delete_cache_data($user_cache_key);
        }

        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = $this->lang->line('feedback_approve_success_msg');
        $this->api_response();   
    }

}