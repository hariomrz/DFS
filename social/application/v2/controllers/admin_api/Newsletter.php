<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All process like : users_listing,users_profile, users_edit
 * @package    Users
 * @author     Vinfotech Team
 * @version    1.0
 */

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
//require APPPATH.'/libraries/REST_Controller.php';



class Newsletter extends Admin_API_Controller {

    public $invalid_email_domains = array();
    public $subscriberGenders = array();

    function __construct() {
        parent::__construct();
        $this->load->model(array(
            'admin/newsletter/newsletter_model', 'admin/login_model',
            'settings_model', 'admin/newsletter/newsletter_users_model',
            'admin/newsletter/newsletter_mailchimp_model'
        ));

        if ($this->settings_model->isDisabled(35)) {
            $this->return['Message'] = lang('resource_is_blocked');
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }

        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
        $this->subscriberGenders = array("0" => "other", "1" => "male", "2" => "female");
        $this->lang->load('newsletter_lang');
    }

    /**
     * Function for add newsletter subscribers.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function add_newsletter_subscriber_post() {

        $return = $this->return;
        $user_id = $this->UserID;
        $Data = $this->post_data;

        $this->form_validation->set_rules('Email', 'Email', 'trim|required|valid_email|is_unique[' . NEWSLETTERSUBSCRIBER . '.Email]');
        $this->form_validation->set_rules('FirstName', 'FirstName', 'trim|max_length[150]');
        $this->form_validation->set_rules('LastName', 'LastName', 'trim|max_length[100]');
        $this->form_validation->set_rules('Gender', 'Gender', 'trim|numeric');

        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 511;
            $return['Message'] = $error;
        } else {

            $InsertSubscriber = array();
            $InsertSubscriber['Email'] = strtolower($Data['Email']);
            $InsertSubscriber['FirstName'] = (isset($Data['FirstName'])) ? $Data['FirstName'] : '';
            $InsertSubscriber['LastName'] = (isset($Data['LastName'])) ? $Data['LastName'] : '';
            $InsertSubscriber['DOB'] = (isset($Data['DOB']) && !empty($Data['DOB'])) ? date('Y-m-d', strtotime($Data['DOB'])) : NULL;
            $InsertSubscriber['Gender'] = (isset($Data['Gender'])) ? $Data['Gender'] : 0;
            $InsertSubscriber['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $InsertSubscriber['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $InsertSubscriber['NewsLetterSubscriberGUID'] = get_guid();
            $InsertSubscriber['UserID'] = (isset($Data['UserID']) && !empty($Data['UserID'])) ? $Data['UserID'] : NULL;


            if (isset($Data['IPAddress']))
                $IPAddress = $Data['IPAddress'];
            else
                $IPAddress = '';
            if (isset($Data['Latitude']))
                $Latitude = $Data['Latitude'];
            else
                $Latitude = '';
            if (isset($Data['Longitude']))
                $Longitude = $Data['Longitude'];
            else
                $Longitude = '';

            //Prepare CityID
            $this->load->helper('location');
            $locationDetails = get_location_details($IPAddress, $Latitude, $Longitude);
            $InsertSubscriber['CityID'] = (!empty($locationDetails['CityID'])) ? $locationDetails['CityID'] : NULL;


            $subscriber_id = $this->newsletter_model->add_newsletter_subscriber($InsertSubscriber);
            if ($subscriber_id) {
                $return['Message'] = lang('newsletter_subscriber_added');
            } else {
                $return['ResponseCode'] = 500;
                $return['Message'] = lang('newsletter_subscriber_not_added');
            }
        }
        $this->response($return);
    }

    /**
     * Function for Unsubscribe user on newsletter.
     * Parameters : From services.js(Angular file)
     */
    public function unsubscribe_newsletter_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $Data = $this->post_data;

        $this->form_validation->set_rules('NewsLetterSubscriberGUID', 'Subscriber ID', 'trim|required|callback_is_valid_subscriber');
        if ($this->form_validation->run() == FALSE) {

            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 511;
            $return['Message'] = $error;
        } else {
            $UnsubscriberData['Status'] = NEWSLETTER_UNSUBSCRIBER;
            $UnsubscriberData['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $UnsubscriberWhere['NewsLetterSubscriberGUID'] = $Data['NewsLetterSubscriberGUID'];
            $this->newsletter_model->unsubscribe_newsletter($UnsubscriberData, $UnsubscriberWhere);

            //Remove subscriber from all newsletter groups and update totalMember for group.
            $subscriberInfo = $this->newsletter_model->is_valid_subscriber($UnsubscriberWhere);
            if ($subscriberInfo) {
                $NewsGroupList = $this->newsletter_model->get_all_assigned_group($subscriberInfo);
                if ($NewsGroupList) {
                    $RemoveGroupArr = array_column($NewsGroupList, "NewsLetterGroupID");
                    $WhereCondition = array('NewsLetterSubscriberID' => $subscriberInfo['NewsLetterSubscriberID']);
                    $this->newsletter_model->remove_subscriber_from_all_groups($WhereCondition, $RemoveGroupArr);

                    foreach ($NewsGroupList as $GroupKey => $GroupValue) {
                        $this->newsletter_model->update_group_total_member($GroupValue['NewsLetterGroupID']);
                    }
                }
            }

            $return['Message'] = lang('newsletter_unsubscribed_success');
        }

        $this->response($return);
    }
    
    
    
    
    /**
     * Function for newsletter group(list).
     * Parameters : From services.js(Angular file)
     */
    public function create_newsletter_group_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $Data = $this->post_data;
        
        if(!empty($Data['UserID'])) {
            $user_details = get_detail_by_id($Data['UserID'], 3, "*");  
            $subscriber_details = $this->newsletter_users_model->add_subscriber_from_excel($user_details, $user_id, true);  
            $this->post_data['NewsLetterSubscriberID'] = (int)isset($subscriber_details['NewsLetterSubscriberID']) ? $subscriber_details['NewsLetterSubscriberID'] : 0;
            $Data['userListReqObj']['NewsLetterSubscriberID'][] = $this->post_data['NewsLetterSubscriberID'];
        }
        
        
        
        $this->post_data['NewsLetterSubscriberID'] = [];
        $requestData = isset($Data['userListReqObj']) ? $Data['userListReqObj'] : [];
        //$requestData['NewsLetterSubscriberID'] = !empty($Data['NewsLetterSubscriberID']) ? $Data['NewsLetterSubscriberID'] : [];
        if ($requestData) {
            $this->post_data['NewsLetterSubscriberID'] = $this->newsletter_users_model->get_users($requestData, true);  //print_r($this->post_data['NewsLetterSubscriberID']); die;
        }


        $Data = $this->post_data;

        //if group id is empty then apply validations for new group(list).
        if (empty($Data['NewsLetterGroupID'])) {
            $this->form_validation->set_rules('Name', 'Group Name', 'trim|required');
            $this->form_validation->set_rules('Description', 'Group Description', 'trim|required');
            $this->form_validation->set_rules('NewsLetterSubscriberID[]', 'Subscriber ID', 'trim|callback_is_valid_subscriber_list');
        } else {
            $this->form_validation->set_rules('NewsLetterGroupID', 'NewsLetterGroupID', 'trim|callback_is_valid_newsletter_group');
        }


        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 511;
            $return['Message'] = $error;
        } else {


            //Prepare valid subscriber list for insert/update into group.
            $SubscriberIDs = array();
            $MCSubscribers = array();
            $MailchimpListID = '';
            if (!empty($Data['NewsLetterSubscriberID']) && is_array($Data['NewsLetterSubscriberID'])) {
                $SubscribersArr = array_filter($Data['NewsLetterSubscriberID']);
                foreach ($SubscribersArr as $SubscriberID) {
                    $is_valid = $this->is_valid_subscriber_email(array('NewsLetterSubscriberID' => trim($SubscriberID)));
                    if ($is_valid) {
                        array_push($SubscriberIDs, $SubscriberID);
                        $MCSubscribers[$SubscriberID] = $is_valid;
                        //array_push($MailchimpSubscribers,$is_valid);
                    }
                }
            }

            $NewsLetterGroupID = 0;
            $UpdateTotalMemeber = FALSE;
            $merge_fields_batch_id = NULL;
            //if group id not provided then create new group.
            if (empty($Data['NewsLetterGroupID'])) {
                //first create mailchimp list using api
                $MC_ListID = $this->newsletter_mailchimp_model->mc_create_list($Data);

                //create merge fields for mailchimp list
                $merge_fields_batch_id = $this->newsletter_mailchimp_model->mc_create_merge_fields($MC_ListID);

                $MailchimpListID = $MC_ListID;
                $CreateNewsGroup = array();
                $CreateNewsGroup['Name'] = $Data['Name'];
                $CreateNewsGroup['Description'] = $Data['Description'];
                $CreateNewsGroup['MailchimpListID'] = $MC_ListID;
                $CreateNewsGroup['StatusID'] = 2; //Active
                $CreateNewsGroup['TotalMember'] = count($SubscriberIDs);
                $CreateNewsGroup['TotalEmailSent'] = 0; //initial value
                $CreateNewsGroup['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $CreateNewsGroup['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $CreateNewsGroup['IsAutoUpdate'] = (int)$Data['isAutoUpdate'];
                
                if(!empty($CreateNewsGroup['IsAutoUpdate'])) {
                    unset($Data['userListReqObj']['NewsLetterSubscriberID']);
                    $Data['userListReqObj']['PageNo'] = 1;
                    $CreateNewsGroup['AutoUpdateFilter'] = json_encode($Data['userListReqObj']);
                }
                
                
                
                $is_created = $this->newsletter_model->create_newsletter_group($CreateNewsGroup);
                if ($is_created) {
                    $NewsLetterGroupID = $is_created;
                    
                    
                    
                    $return['Message'] = lang('newsletter_group_added');
                } else {
                    $return['ResponseCode'] = 500;
                    $return['Message'] = lang('newsletter_group_not_added');
                }
            }
            //if group id provided then update group details
            else {
                $NewsLetterGroupID = $Data['NewsLetterGroupID'];
                $UpdateNewsGroup = array();
                $UpdateNewsGroup['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $UpdateCondition = array('NewsLetterGroupID' => $NewsLetterGroupID);

                if (!empty($Data['Name'])) {
                    $UpdateNewsGroup['Name'] = $Data['Name'];
                }

                if (!empty($Data['userListReqObj']['userIncludeList'])) {
                    $UpdateNewsGroup['userIncludeList'] = $Data['userListReqObj']['userIncludeList'];
                }
                
                if (!empty($Data['Description'])) {
                    $UpdateNewsGroup['Description'] = $Data['Description'];
                }

                $deletedIncludedUsers = $this->newsletter_model->update_newsletter_group($UpdateNewsGroup, $UpdateCondition);
                
                
                
                $return['Message'] = lang('newsletter_group_updated');
                $UpdateTotalMemeber = TRUE;
                if (!empty($this->post_data['newsletterGroupinfo']['MailchimpListID'])) {
                    $MailchimpListID = $this->post_data['newsletterGroupinfo']['MailchimpListID'];
                }
            }

            //create entries of subscribers for given group.
            if (!empty($SubscriberIDs)) {
                $CreateGroupSubscribers = array();
                $MembersBatch = array();
                $currentDate = get_current_date('%Y-%m-%d %H:%i:%s');
                foreach ($SubscriberIDs as $key => $subscriber) {
                    $CreateGroupSubscribers[$key]['NewsLetterGroupID'] = $NewsLetterGroupID;
                    $CreateGroupSubscribers[$key]['NewsLetterSubscriberID'] = $subscriber;
                    $CreateGroupSubscribers[$key]['CreatedDate'] = $currentDate;
                    $CreateGroupSubscribers[$key]['ModifiedDate'] = $currentDate;
                    $CreateGroupSubscribers[$key]['StatusID'] = 2; //Active      
                    if (!empty($MCSubscribers[$subscriber]['Email']) && !empty($MailchimpListID)) {
                        
                        $MCSubscribers[$subscriber] = $this->newsletter_users_model->set_location_and_tags($MCSubscribers[$subscriber]);
                        
                        $merge_fields = array(
                            'FNAME' => $MCSubscribers[$subscriber]['FirstName'],
                            "LNAME" => $MCSubscribers[$subscriber]['LastName'],
                            "GENDER" => $this->subscriberGenders[$MCSubscribers[$subscriber]['Gender']],
                            "DOB" => ($MCSubscribers[$subscriber]['DOB']) ? $MCSubscribers[$subscriber]['DOB'] : '',
                            "LOCATION" => $MCSubscribers[$subscriber]["LocationStr"],
                            "USERTYPE" => $MCSubscribers[$subscriber]["UserTypeTagsStr"],
                            "TAGS" => $MCSubscribers[$subscriber]["TagsStr"]
                        );



                        $operationsArr = array(
                            'method' => "POST",
                            'path' => "lists/" . $MailchimpListID . "/members",
                            'body' => (string) json_encode(array("email_address" => $MCSubscribers[$subscriber]['Email'], 'status' => "subscribed", "timestamp_signup" => $currentDate, "merge_fields" => $merge_fields))
                        );
                        array_push($MembersBatch, $operationsArr);
                    }
                }
                $this->newsletter_model->add_newsletter_group_subscribers($CreateGroupSubscribers);


                if ($UpdateTotalMemeber) {
                    $this->newsletter_model->update_group_total_member($NewsLetterGroupID);
                }

                //Create Batch operation on Mailchimp for add members on given newsletter group(list).
                if (!empty($MembersBatch)) {

                    $this->newsletter_mailchimp_model->update_mailchimp_subscriber_list_members($MembersBatch, $merge_fields_batch_id, $MailchimpListID);
                }
            }
            
            
            if(!empty($CreateNewsGroup['IsAutoUpdate']) && $is_created && $NewsLetterGroupID) {                        
                $this->load->model(array('admin/newsletter/newsletter_model'));
                $this->newsletter_model->autoUpdateToSpecificGroupList($NewsLetterGroupID);
            }
            
            
        }

        $this->response($return);
    }
    
    
    
    public function remove_subscribers_from_group_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $Data = $this->post_data;

        $this->post_data['NewsLetterSubscriberID'] = [];
        $requestData = isset($Data['userListReqObj']) ? $Data['userListReqObj'] : [];
        //$requestData['NewsLetterSubscriberID'] = !empty($Data['NewsLetterSubscriberID']) ? $Data['NewsLetterSubscriberID'] : [];
        if ($requestData) {
            $this->post_data['NewsLetterSubscriberID'] = $this->newsletter_users_model->get_users($requestData, true);  //print_r($this->post_data['NewsLetterSubscriberID']); die;
        }
        $Data = $this->post_data;

        $this->form_validation->set_rules('NewsLetterGroupID', 'NewsLetterGroupID', 'trim|required|callback_is_valid_newsletter_group');
        $this->form_validation->set_rules('NewsLetterSubscriberID[]', 'Subscriber ID', 'trim|callback_is_valid_subscriber_list');
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 511;
            $return['Message'] = $error;
        } else {
            if (!empty($Data['NewsLetterSubscriberID']) && !empty($Data['NewsLetterGroupID'])) {
                $WhereCondition = array('NewsLetterGroupID' => $Data['NewsLetterGroupID']);
                //get all selected subscriber info for removing on mailchimp.
                $GroupSubscribers = $this->newsletter_model->get_group_subscribers($WhereCondition, $Data['NewsLetterSubscriberID']);
                $this->newsletter_model->remove_subscribers_from_group($WhereCondition, $Data['NewsLetterSubscriberID']);
                $resp = $this->newsletter_model->update_group_total_member($Data['NewsLetterGroupID']);

                //Remove subscribers(members) from mailchimp list
                if (!empty($this->post_data['newsletterGroupinfo']['MailchimpListID']) && !empty($GroupSubscribers)) {
                    $MailchimpListID = $this->post_data['newsletterGroupinfo']['MailchimpListID'];
                    $MemberDeleteBatch = array();
                    foreach ($GroupSubscribers as $subcriber_key => $subcriber_value) {

                        $mailchimp_subscriber_id = !empty($subcriber_value['MailchimpSubscriberID']) ? $subcriber_value['MailchimpSubscriberID'] : md5(strtolower($subcriber_value['Email']));  
                        
                        $operationsArr = array(
                            'method' => "DELETE",
                            'path' => "lists/" . $MailchimpListID . "/members/" . $mailchimp_subscriber_id
                        );
                        array_push($MemberDeleteBatch, $operationsArr);
                    }

                    //call mailchimp batch operation for delete selected subscribers from list
                    if (!empty($MemberDeleteBatch)) {
                        $batchResponse = $this->newsletter_mailchimp_model->remove_group_subscribers($MemberDeleteBatch);
                    }
                }




                // $return['Message'] = $resp;
                $return['Message'] = lang('subscribers_removed');
            } else {
                $return['ResponseCode'] = 511;
                $return['Message'] = lang('subscribers_not_removed');
            }
        }
        $this->response($return);
    }

    public function remove_newsletter_group_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $Data = $this->post_data;
        $this->form_validation->set_rules('NewsLetterGroupID', 'NewsLetterGroupID', 'trim|required|callback_is_valid_newsletter_group');
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 511;
            $return['Message'] = $error;
        } else {

            $NewsLetterGroupID = $Data['NewsLetterGroupID'];
            $UpdateData = array('StatusID' => 3);
            $UpdateWhere = array('NewsLetterGroupID' => $NewsLetterGroupID);

            //update group member table
            $this->newsletter_model->update(NEWSLETTERGROUPMEMBER, $UpdateData, $UpdateWhere);

            //update group table
            $UpdateData['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $this->newsletter_model->update(NEWSLETTERGROUP, $UpdateData, $UpdateWhere);

            //if Mailchimp List Id associated with this group then remove list from mailchimp.
            if (!empty($this->post_data['newsletterGroupinfo']['MailchimpListID'])) {
                $MailchimpListID = $this->post_data['newsletterGroupinfo']['MailchimpListID'];
                $batchResponse = $this->newsletter_mailchimp_model->remove_group($MailchimpListID);
            }
            $return['Message'] = lang('newsletter_group_deleted');
        }

        $this->response($return);
    }

    /* Function to get list of groups */
    public function get_groups_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        $return['Data'] = (array) $this->newsletter_model->get_groups($data);
        $this->response($return);
    }

    /* Function for update mailchimp member id into newsletter group subscriber */
    public function update_mailchimp_subscriber_id_post() {
        $this->newsletter_model->update_mailchimp_subscriber_ids();
    }

    private function is_valid_subscriber_email($Subscriber) {

        $is_valid = $this->newsletter_model->is_valid_subscriber($Subscriber);
        if (!empty($is_valid['Email']) && $this->is_email_real($is_valid['Email'])) {
            return $is_valid;
        } else {
            return FALSE;
        }
    }

    private function is_email_real($email) {
        $email_part = explode('@', $email);
        $email_domain = end($email_part);
        $email_domain = strtolower($email_domain);
        if (in_array($email_domain, $this->invalid_email_domains)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Callback Function for check given Subscriber GUID is valid or not.
     */
    public function is_valid_subscriber($input_string) {
        if (empty($input_string)) {
            $this->form_validation->set_message('is_valid_subscriber', lang('invalid_subscriber_detail'));
            return FALSE;
        }

        $sub_condition = array('NewsLetterSubscriberGUID' => $input_string);
        $subscribers_exists = $this->newsletter_model->is_valid_subscriber($sub_condition);
        if (!$subscribers_exists) {
            $this->form_validation->set_message('is_valid_subscriber', lang('invalid_subscriber_detail'));
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /* Callback function for validate given array of subscriber ids */

    public function is_valid_subscriber_list($input) {

        if (!empty($this->post_data['NewsLetterGroupID']) && empty($this->post_data['NewsLetterSubscriberID'])) {
            $this->form_validation->set_message('is_valid_subscriber_list', lang('subsriber_list_required'));
            return FALSE;
        }
        $NewsLetterSubscriberID = $this->post_data['NewsLetterSubscriberID'];

        if (!empty($NewsLetterSubscriberID) && !is_array($NewsLetterSubscriberID)) {
            $this->form_validation->set_message('is_valid_subscriber_list', lang('invalid_subscriber_list'));
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /* Callback function for validate given newsletter group id. */

    public function is_valid_newsletter_group($input) {
        $condition = array('NewsLetterGroupID' => $input);
        $subscribers_exists = $this->newsletter_model->is_valid_newsletter_group($condition);
        if (!$subscribers_exists) {
            $this->form_validation->set_message('is_valid_newsletter_group', lang('invalid_newsletter_group_error'));
            return FALSE;
        } else {
            $this->post_data['newsletterGroupinfo'] = $subscribers_exists;
            return TRUE;
        }
    }

    public function demo_post() {
        
        $this->newsletter_model->autoUpdateGroupLists();
        
        //$this->newsletter_mailchimp_model->demo();
    }

}

//End of file users.php
