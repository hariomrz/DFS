<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 * This Class used for REST API
 * (All THE API CAN BE USED THROUGH POST METHODS)
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Vinfotech Team
 */
class Build_network extends Common_API_Controller
{

    /**
     * @Summary: call parent constructor
     * @create_date: monday, July 14, 2014
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    function __construct()
    {
        parent::__construct();
        $this->check_module_status(4);
        $this->load->model(array('build_network_model', 'group/group_model'));
        $this->header['title'] = 'Build Network | ';
    }

    public function index_get()
    {
        $this->load->view('networks/build_network_main');
    }

    /**
     * @Summary: save native invitation
     * @create_date: monday, July 14, 2014
     * @last_update_date:
     * @access: public
     * @param:  UserSocialId[],Message
     * @return: Registered[], NewUsers[], AlreadyInvited[], Invited[]
     */
    public function send_native_invitations_post()
    {
        $this->load->library('email');
        //$this->load->helper('email');
        $this->load->helper('mail_helper');
        $res = array('new_email' => array(), 'old_email' => array());

        /* Define variables - starts */
        $Return = $this->return;
        /* Define variables - ends */

        $Data = $this->post_data;
        $LoginSessionKey = $Data[AUTH_KEY];
        $Return['Message'] = 'Invite Sent Successfully';
        if (!empty($LoginSessionKey))
        {
            /* Validation - starts */
            if ($this->form_validation->run('api/send_native_invitations') == FALSE)
            {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = $this->form_validation->rest_first_error_string();
            }
            else
            { /* Validation - ends */

                $groupid = 0;
                $entity_type = 0;
                $emails = $Data['UserSocialId'];
                $Message = $Data['Message'];
                $Message = nl2br($Message);

                //Check if data exists if yes then assign in variables
                if (isset($Data['groupid']) && !empty($Data['groupid']))
                {
                    $groupid = $Data['groupid'];
                }
                if (isset($Data['EntityType']) && !empty($Data['EntityType']))
                {
                    $entity_type = $Data['EntityType'];
                }

                //Get unique emails only
                $result = array_unique($emails);
                if (!empty($result))
                {
                    //Get registered users
                    $res = $this->build_network_model->get_registered_users($result);
                }
                
                // compare between new email and already invited emails and get difference between them
                $invited_users = array();
                $to_be_invite = array();
                if (!empty($res['new_email']))
                {
                    $already_invited = $this->build_network_model->get_all_invited_user($this->UserID, $res['new_email']);
                    foreach ($already_invited as $invited)
                    {
                        $invited_users[] = $invited['UserSocialID'];
                    }
                    $to_be_invite = array_diff($res['new_email'], $invited_users);
                }

                //Send Friend Request if user is already exists
                if (isset($res['old_email']) && !empty($res['old_email']))
                {
                    $this->load->model('users/friend_model');
                    foreach ($res['old_email'] as $km => $vm)
                    {
                        $this->friend_model->addFriendByLSK($ $km);
                    }
                }

                //If to be invited users is atleast 1 then run query to invite user and send email
                if (count($to_be_invite) > 0)
                {
                    $link = array();
                    $invite = array();
                    foreach ($to_be_invite as $new_email)
                    {
                        $temp = array();
                        $temp['user_id'] = $this->UserID;
                        $temp['invite_type'] = 1;
                        $temp['user_social_id'] = $new_email;
                        $temp['token'] = uniqid();
                        $temp['created_date'] = get_current_date('%Y-%m-%d %H:%i:%s');
                        $temp['EntityType'] = $entity_type;
                        $temp['EntityID'] = $groupid;
                        $temp['is_registered'] = 0;
                        $invite[] = $temp;
                        $link[] = site_url() . 'signup?Token=' . $temp['token'];
                    }
                    $this->build_network_model->save_native_invitation($invite);
                    $key = 0;
                    foreach ($to_be_invite as $mail)
                    {
                        /* Send Email - Starts */
                        /* $Subject = "Invitation";
                          $Template = THEAM_PATH."email/send-fiendrequest.html"; // Custom email template
                          $values = array("##EMAIL##"=>$mail,"##LINK##"=>$link[$key],"##MESSAGE##"=>$Message);
                          sendMail(EMAIL_NOREPLY_FROM,EMAIL_NOREPLY_NAME,$Template,$values,$mail,'Friend Invitation'); */

                        $emailDataArr = array();
                        $emailDataArr['IsResend'] = 0;
                        $emailDataArr['Subject'] = SITE_NAME . " Friend Invitation";
                        $emailDataArr['TemplateName'] = "emailer/send_friend_request";
                        $emailDataArr['Email'] = $mail;
                        $emailDataArr['EmailTypeID'] = FRIEND_REQUEST_EMAIL_TYPE_ID;
                        $emailDataArr['UserID'] = 0;
                        $emailDataArr['StatusMessage'] = "Friend Request";
                        $emailDataArr['Data'] = array("Email" => $mail, "Message" => $Message, "Link" => $link[$key]);
                        sendEmailAndSave($emailDataArr);


                        /* Send Email - Ends */
                        $key++;
                        if ($key % 10 == 0)
                        {
                            sleep(1);
                        }
                    }
                }
                $Return['Data']['Registered'] = $res['old_email'];
                $Return['Data']['NewUsers'] = $res['new_email'];
                $Return['Data']['AlreadyInvited'] = $invited_users;
                $Return['Data']['Invited'] = $to_be_invite;
                if (count($to_be_invite) <= 0)
                {
                    $Return['Message'] = 'No invites sent, all emails entered are either already registered or already invited.';
                }
            }
        }
        else
        {
            $Return['ResponseCode'] = 501;
            $Return['Message'] = lang('not_authorized');
        }
        $this->response($Return);
    }

    /**
     * @Summary: save google invitation
     * @create_date: monday, July 14, 2014
     * @last_update_date:
     * @access: public
     * @param:  Token
     * @return: 
     */
    public function googleInvitation_post()
    {
        $UserID = $this->UserID;
        $Data = $this->post_data;
        $Token = $Data['Token'];
        $this->build_network_model->addGoogleInvitation($UserID, $Token);
    }

    /**
     * @Summary: save social invitation request
     * @create_date: monday, July 14, 2014
     * @last_update_date:
     * @access: public
     * @param:  invite_type, social_id
     * @return: uid, link
     */
    public function save_social_invitation_request_post()
    {
        /* For social invitations only
          Invite_type  --> 1: Facebook, 2 : Twitter, 3: Google plus, 4: Linkedin
         *  social_id --> May be email address in case of native invite type or unique social id of social network
         */

        $Return = $this->return;

        $Data = $this->post_data;

        $user_data = array();
        $user_data['user_id'] = $this->UserID;
        $user_data['invite_type'] = $Data['invite_type'];
        $user_data['user_social_id'] = $Data['social_id'];
        $user_data['token'] = uniqid();
        $user_data['created_date'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $user_data['is_registered'] = 0;
        $user_data['EntityType'] = '';
        $user_data['EntityID'] = '';
        $data[] = $user_data;
        $this->build_network_model->save_native_invitation($data);
        $Return['Data']['uid'] = $Data['social_id'];
        $Return['Data']['link'] = site_url() . "signup?Token=" . $user_data['token'];
        $this->response($Return);
    }

    /**
     * @Summary: check friends list
     * @create_date: monday, July 14, 2014
     * @last_update_date:
     * @access: public
     * @param:  social_type, friend_ids[]
     * @return: Status (Array)
     */
    public function check_friends_list_post()
    {
        $Return = $this->return;

        $Data = $this->post_data;
        $user_id = $this->UserID;
        $social_type = '';
        if (isset($Data['social_type']))
        {
            $social_type = $Data['social_type'];
        }
        $friend_id = $Data['friend_ids'];
        if ($friend_id)
        {
            foreach ($friend_id as $fid)
            {
                $fid = (string) $fid;
                $checkData = $this->login_model->check_social_user_exists($fid, $social_type,$user_id);
                if ($checkData)
                {
                    $checkData['Status'] = ($checkData['Status'] != '1' ? '3' : '1');
                    $return_array = array('id'=>$fid,'status'=>$checkData['Status'],'user_guid'=>$checkData['UserGUID']);
                        if($this->IsApp == 1){ /*added by gautam*/
                            $return_array = array_merge($return_array, array('FirstName'=>$checkData['FirstName'], 'LastName'=>$checkData['LastName'],'ProfilePicture'=>$checkData['ProfilePicture']));
                        }
                }
                else
                {
                   $return_array = array('id'=>$fid,'status'=>'2','user_guid'=>'');
                        if($this->IsApp == 1){ /*added by gautam*/
                            $return_array = array_merge($return_array, array('FirstName'=>'', 'LastName'=>'','ProfilePicture'=>''));
                        }
                }
                $Return['Data'][] = $return_array;
            }
        }
        $this->response($Return);
    }

    /**
     * @Summary: Save FB Invites
     * @create_date: monday, July 14, 2014
     * @last_update_date:
     * @access: public
     * @param:  Invites[]
     * @return:
     */
    public function save_fb_invites_post()
    {
        $Return = $this->return;

        $Data = $this->post_data;
        $UserID = $this->UserID;
        $invites = array();
        $token = '';
        if (isset($Data['Invites']))
        {
            $invites = explode(',', $Data['Invites']);
        }
        if (isset($Data['request']))
        {
            $token = $Data['request'];
        }

        if ($invites)
        {
            $data = array();
            foreach ($invites as $invite)
            {
                $temp = array();
                $temp['user_id'] = $UserID;
                $temp['invite_type'] = 2;
                $temp['user_social_id'] = $invite;
                $temp['token'] = $token;
                $temp['created_date'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $temp['EntityType'] = '0';
                $temp['EntityID'] = '0';
                $temp['is_registered'] = '0';
                $data[] = $temp;
            }
            $this->build_network_model->save_native_invitation($data, $UserID);
        }
        $this->response($Return);
    }

    public function send_invitation_post()
    {
        $Return = $this->return;
        $user_data = array();
        $userid = $this->UserID;
        $token = uniqid();
        $Data = $this->post_data;
        $Return['Link'] = '';
        if ($Data['user_data'])
        {

            $social_id = $Data['user_data']['email'];
            $invite_type = $Data['user_data']['invite_type'];
            if ($social_id)
            {
                $insert_data = array(array(
                        'user_id' => $userid,
                        'invite_type' => $invite_type,
                        'user_social_id' => $social_id,
                        'token' => $token,
                        'created_date' => get_current_date('%Y-%m-%d %H:%i:%s'),
                        'is_registered' => 0,
                        'EntityType' => 0,
                        'EntityID' => 0
                ));
                $token_ar = $this->build_network_model->save_native_invitation($insert_data);
                $token = $token_ar[0];
                $link = site_url() . 'signup?Token=' . ( $token );
                $Return['Link'] = $Data['user_data']['link'] = $link;

                $to_user_data = $Data['user_data'];
                
                 $email_data['IsResend'] = 0;
                 $email_data['UserID'] = $userid;
                $email_data['Subject'] = " Your friend invite to join";
                $email_data['TemplateName'] = "emailer/yahoo_invite";
                $email_data['Data']['To'] = $to_user_data;
                $email_data['Email'] = $social_id;
                $email_data['EmailTypeID'] ='31';
                 $email_data['StatusMessage'] = "Invite";      
               $check_mail= sendEmailAndSave($email_data, 0);
               $Return['send_mail'] =$check_mail;
           }
        }

        return $this->response($Return);
        //$return=$this->google_url_shortner($link);
        //return " join ".SITE_NAME." ".  $return ;
    }

}
