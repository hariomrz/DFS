<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Newsletter_mailchimp_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();

        $this->load->library('mailchimp');
        $this->invalid_email_domains = array('mailinator.com', 'yopmail.com');
        $this->subscriberGenders = array("0" => "other", "1" => "male", "2" => "female");      

        $this->load->config('mailchimp');
        $this->webhook_api_key = $this->config->item('api_key');
    }

    public function create_mailchimp_subscriber() {

        $MCSubscribers = array();

        foreach ($SubscriberIDs as $key => $subscriber) {


//            $CreateGroupSubscribers[$key]['NewsLetterGroupID'] = $NewsLetterGroupID;
//            $CreateGroupSubscribers[$key]['NewsLetterSubscriberID'] = $subscriber;
//            $CreateGroupSubscribers[$key]['CreatedDate'] = $currentDate;
//            $CreateGroupSubscribers[$key]['ModifiedDate'] = $currentDate;
//            $CreateGroupSubscribers[$key]['StatusID'] = 2; //Active  


            if (!empty($MCSubscribers[$subscriber]['Email']) && !empty($MailchimpListID)) {

                $merge_fields = array(
                    'FNAME' => $MCSubscribers[$subscriber]['FirstName'],
                    "LNAME" => $MCSubscribers[$subscriber]['LastName'],
                    "GENDER" => $this->subscriberGenders[$MCSubscribers[$subscriber]['Gender']],
                    "DOB" => $MCSubscribers[$subscriber]['DOB'],
                    "LOCATION" => "",
                    "USERTYPE" => "",
                    "TAGS" => ""
                );



                $operationsArr = array(
                    'method' => "POST",
                    'path' => "lists/" . $MailchimpListID . "/members",
                    'body' => (string) json_encode(array("email_address" => $MCSubscribers[$subscriber]['Email'], 'status' => "subscribed", "timestamp_signup" => $currentDate, "merge_fields" => $merge_fields))
                );
                array_push($MembersBatch, $operationsArr);
            }
        }
    }

    /* Function for create mailchimp list using api. */

    public function mc_create_list($Data) {
        $contactInfo = $this->config->item('list_contact_info');
        $campaign_defaults = $this->config->item('campaign_defaults');
        $parameters = array(
            'name' => $Data['Name'],
            'contact' => $contactInfo,
            'permission_reminder' => MC_PERMISSION_REMINDER,
            'visibility' => 'pub', //not mendetory
            'campaign_defaults' => $campaign_defaults,
            'email_type_option' => true
        );

        $ListResponse = $this->mailchimp->call('POST', '/lists', $parameters);
        $list_id = (!empty($ListResponse['id'])) ? $ListResponse['id'] : NULL;

        $this->add_list_webhook($list_id);

        return $list_id;
    }

    protected function add_list_webhook($list_id) {

        if (!$list_id) {
            return;
        }

        $webhook_url = SITE_HOST . '/cron/mailchimp_webhook?api_key='.$this->webhook_api_key;

        $parameters = array(
            'url' => $webhook_url,
            'events' => array(
                "subscribe" => false,
                "unsubscribe" => true,
                "profile" => false,
                "cleaned" => false,
                "upemail" => false,
                "campaign" => false,
            ),
            'sources' => array(
                'user' => TRUE,
                'admin' => TRUE,
                'api' => TRUE,
            )
        );

        $ListResponse = $this->mailchimp->call('POST', "/lists/$list_id/webhooks", $parameters);
    }

    /* Function for create list merge fields */

    public function mc_create_merge_fields($MailchimpListID) {
        $MergeFieldsBatch = array(
            //For Gender
            array(
                'method' => "POST",
                'path' => "lists/" . $MailchimpListID . "/merge-fields",
                'body' => (string) json_encode(array("tag" => "GENDER", 'name' => "GENDER", "type" => "text", "required" => FALSE))
            ),
            //For DOB (Y-m-d)
            array(
                'method' => "POST",
                'path' => "lists/" . $MailchimpListID . "/merge-fields",
                'body' => (string) json_encode(array("tag" => "DOB", 'name' => "DOB", "type" => "date", "required" => FALSE))
            ),
            //For Location
            array(
                'method' => "POST",
                'path' => "lists/" . $MailchimpListID . "/merge-fields",
                'body' => (string) json_encode(array("tag" => "LOCATION", 'name' => "LOCATION", "type" => "address", "required" => FALSE))
            ),
            //For User Type
            array(
                'method' => "POST",
                'path' => "lists/" . $MailchimpListID . "/merge-fields",
                'body' => (string) json_encode(array("tag" => "USERTYPE", 'name' => "USERTYPE", "type" => "text", "required" => FALSE))
            ),
            //For Tags
            array(
                'method' => "POST",
                'path' => "lists/" . $MailchimpListID . "/merge-fields",
                'body' => (string) json_encode(array("tag" => "TAGS", 'name' => "TAGS", "type" => "text", "required" => FALSE))
            ),
        );

        $batchResponse = $this->mailchimp->call('POST', '/batches', array('operations' => $MergeFieldsBatch));
        return (!empty($batchResponse['id'])) ? $batchResponse['id'] : NULL;
    }

    public function update_mailchimp_subscriber_list_members($MembersBatch, $merge_fields_batch_id, $MailchimpListID = '') {

        if (!$merge_fields_batch_id) {
            $batchResponse = $this->mailchimp->call('POST', '/batches', array('operations' => $MembersBatch));
            return $batchResponse;
        }

        $total_checks = 0;

        $merge_field_status = false;
        while (!$merge_field_status) {

            if ($MailchimpListID) {
                $merge_fields_response = $this->mailchimp->call('GET', "/lists/$MailchimpListID/merge-fields");

                $fields_response_merge_fields = (isset($merge_fields_response['merge_fields']) && is_array($merge_fields_response['merge_fields'])) ? $merge_fields_response['merge_fields'] : [];
                $merge_inner_fields_status = 1;
                foreach ($fields_response_merge_fields as $fieldKey => $fieldVal) {
                    if (!isset($fieldVal['public']) || $fieldVal['public'] != 1) {
                        $merge_inner_fields_status = 0;
                    }
                }


                if ($merge_inner_fields_status == 1) {
                    break;
                }
            }


            if ($total_checks == 5) {
                break;
            }

            $total_checks++;
        }

        $batchResponse = $this->mailchimp->call('POST', '/batches', array('operations' => $MembersBatch));

        return $batchResponse;
    }

    public function remove_group_subscribers($MemberDeleteBatch) {
        $batchResponse = $this->mailchimp->call('POST', '/batches', array('operations' => $MemberDeleteBatch));

        return $batchResponse;
    }

    public function remove_group($MailchimpListID) {
        $batchResponse = $this->mailchimp->call("DELETE", '/lists/' . $MailchimpListID, array());

        return $batchResponse;
    }

    public function update_mailchimp_subscriber_id($MailchimpListID, $subscriber_hash) {
        $MemberResponse = $this->mailchimp->call('GET', '/lists/' . $MailchimpListID . '/members/' . $subscriber_hash);

        return $MemberResponse;
    }

    public function add_subscriber_to_list($oneSubscriber) {
        $batchResponse = $this->mailchimp->call($oneSubscriber['method'], $oneSubscriber['path'], $oneSubscriber['body']);
        return $batchResponse;
    }

    public function demo() {


        $merge_fields_response = $this->mailchimp->call('GET', "/campaigns/db47682639");

        print_r($merge_fields_response);

        die;
        echo '<pre>';
        print_r($batchResponse);
        die;

        if ($this->is_email_real('rahulp@yopmail.com')) {
            echo "Valid Email";
        } else {
            echo "Invalid Email";
        }
        die;



        $currentDate = get_current_date('%Y-%m-%d %H:%i:%s');
        $createBatch = array(
            array(
                'method' => "POST",
                'path' => "lists/859c47d338/members",
                'body' => (string) json_encode(array("email_address" => "rahulp@gmail.com", 'status' => "subscribed", "timestamp_signup" => $currentDate, "merge_fields" => array('FNAME' => "Rahul", "LNAME" => "Parmar", "DOB" => "1990-09-22")))
            ),
        );

        $batchResponse = $this->mailchimp->call('POST', '/batches', array('operations' => $createBatch));
        echo json_encode($batchResponse);
        die;





        // $ListID = $this->mc_create_list(array('Name'=>"Non Active Users"));
        //echo 'List Id '.$ListID;die;

        $contactInfo = array(
            'company' => "Vinfotech",
            'address1' => 'Indore MP',
            'city' => "Indore",
            'state' => "Madhya Pradesh",
            'zip' => '452001',
            'country' => "India",
            'phone' => '1234568585'
        );
        $defaultCampaign = array(
            'from_name' => FROM_EMAIL_TITLE,
            'from_email' => FROM_EMAIL,
            'subject' => "Website Updates",
            'language' => 'english',
        );
        $creatList = array(
            'name' => "Active Users",
            'contact' => $contactInfo,
            'permission_reminder' => "Subscribed via Website",
            'visibility' => 'pub', //not mendetory
            'campaign_defaults' => $defaultCampaign,
            'email_type_option' => true
        );



        $listid = '1f63cc3a52';
        $campaigns = $this->mailchimp->call('POST', '/lists', $creatList);
        echo json_encode($campaigns);
        die;
        //  echo '<pre>';
        print_r($campaigns);
    }
    
    
    public function get_compaign_reports($offset = 0, $count = 10) {
        
        $queryParams = array(
            'offset' => $offset,
            'count' => $count,
        );
        
        $queryParams = "?offset=$offset&count=$count";  
        
        //campaigns
        
        $compaigns = $this->mailchimp->call('GET', '/reports' . $queryParams);  //print_r($compaigns); die;
        
        //$compaigns = $this->mailchimp->call('GET', '/campaigns/9680c180a3');  print_r($compaigns); die;

        return $compaigns;
    }
    
    public function get_link_data($link) {
        $compaigns = $this->mailchimp->call('GET', $link);  print_r($compaigns); die;                
        return $compaigns;
    }

}
