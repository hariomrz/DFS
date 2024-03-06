<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_nosql_model extends NOSQL_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	/**
     * get userdashbaord of specific user 
     * @param  $user_id
     * @return string
     */
	public function get_user_nosql_data(){
		$post = $this->input->post();
        $result = $this->select_one_nosql('userdashboard',array('user_id'=>$post['user_id']));
        $result['balance']= number_format($result['balance'],2,'.',',');
        $result['total_referral_amount']= number_format($result['total_referral_amount'],2,'.',',');
        $result['winning_balance']= number_format($result['winning_balance'],2,'.',',');
        $result['total_withdraw']= number_format($result['total_withdraw'],2,'.',',');
		return ($result)?$result:array();
	}
	/**
     * add note for specific user 
     * @param  $user_id
     * @return string
     */
	public function add_note(){
		$post = $this->input->post();
		$result = $this->insert_nosql('usernotes',$post);
		return ($result)?$result:array();
	}
	/**
     * get note of specific user 
     * @param  $user_id
     * @return string
     */
	public function get_notes(){
		$post = $this->input->post();
		$result = $this->select_nosql('usernotes',array('user_unique_id'=>$post['user_unique_id']));
		return ($result)?$result:array();
	}

	public function send_notification($data)
    {
        $id = 1;
        //email_template
        $notification = array();
        $notification['notification_type']        	= $data['notification_type']; 
        $notification['source_id']                	= (int)$data['source_id']; 
        $notification['user_id']                  	= (int)$data['user_id'];
        $notification['notification_status']      	= 1;
        $notification['content']                  	= $data['content'];
        $notification['notification_destination'] 	= $data['notification_destination'];
        $notification['added_date']               	= $data['added_date'];
        $notification['modified_date']            	= $data['modified_date'];
        $notification['deviceIDS'] 			  		= isset($data['device_ids'])?$data['device_ids']:'';

        if(in_array($notification['notification_destination'], array(1,3,5,7)))
        {   
            $this->insert_nosql(NOTIFICATION,$notification);
            //uncomment below code if last inserted id needed from mongo.
            //$id = $this->insert_id_nosql(NOTIFICATION);
        }

        /* Send Push Notifications*/
        $this->load->helper('queue_helper');
        
        if(in_array($notification['notification_destination'], array(2,3,6,7)) && !empty($notification['deviceIDS']))
        {
            add_data_in_queue($notification,'push');
        }

        /* Send Email Notifications*/
        if(in_array($notification['notification_destination'], array(4,5,6,7)))
        {
            $email_content                      = array();
            $email_content['email']             = $data["to"];
            $email_content['subject']           = $data["subject"];
            $email_content['user_name']         = $data["user_name"];
            $email_content['content']           = $data["content"];
            $email_content['notification_type'] = $notification['notification_type'];

            
                    $email_content['subject'] = $data["subject"];
                

            add_data_in_queue($email_content, 'email');
        }

        return $id;
    }

    /**
     * get contest lineup of specific game
     * @param  $data
     * @return string
     */
	public function get_contest_data($collection,$find_condition){
		
		$result = $this->select_nosql($collection,$find_condition);
		return isset($result[0])?$result[0]:array();
    }

    /**
     * get delete_nosql_active_login_data of specific user 
     * @param  $user_id
     * @return string
     */
	public function delete_nosql_active_login_data($user_id){
		// $result = $this->delete_nosql('active_login',array('user_id'=>$user_id));
         $result = $this->mongo_db->delete_all('active_login', ['user_id'=>$user_id]);
		return ($result)?$result:array();
	}
   


}
?>
