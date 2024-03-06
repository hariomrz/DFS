<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Newsletter_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
        $this->subscriberGenders = array("0" => "other", "1" => "male", "2" => "female");
    }

    /**
     * Function for add new subscriber in newsletter 
     * Parameters : Data array
     * Return : newly added subscriber's id
     */
    public function add_newsletter_subscriber($InsertSubscriber) {
        $this->db->insert(NEWSLETTERSUBSCRIBER, $InsertSubscriber);
        return $this->db->insert_id();
    }

    public function is_valid_subscriber($sub_condition) {
        $sql = $this->db->select("*")
                ->from(NEWSLETTERSUBSCRIBER)
                ->where($sub_condition)
                ->get();
        return ($sql->num_rows() > 0) ? $sql->row_array() : FALSE;
    }

    public function get_user_groups($data) {

        $NewsLetterSubscriberID = $data['NewsLetterSubscriberID'];

        $this->db->select("NG.*, NS.Email, NGM.MailchimpSubscriberID, NGM.NewsLetterSubscriberID")
                ->from(NEWSLETTERGROUP . ' NG')
                ->join(NEWSLETTERGROUPMEMBER . " NGM ", "NGM.NewsLetterGroupID = NG.NewsLetterGroupID ", "left")
                ->join(NEWSLETTERSUBSCRIBER . " NS ", "NS.NewsLetterSubscriberID = NGM.NewsLetterSubscriberID ", "left")
                ->group_by('NG.NewsLetterGroupID')
        ;

        if (is_array($NewsLetterSubscriberID)) {
            $this->db->where_in('NGM.NewsLetterSubscriberID', $NewsLetterSubscriberID);
        } else {
            $this->db->where('NGM.NewsLetterSubscriberID', $NewsLetterSubscriberID);
        }

        $compiled_query = $this->db->_compile_select();
        $this->db->reset_query();

        $query = $this->db->query($compiled_query);
        return $query->result_array();
    }

    public function get_groups($post_data) {

        $page_no = (int) isset($post_data['PageNo']) ? $post_data['PageNo'] : 1;
        $page_size = (int) isset($post_data['PageSize']) ? $post_data['PageSize'] : 20;
        $name = isset($post_data['Name']) ? $post_data['Name'] : '';
        $order_field = isset($post_data['OrderField']) ? $post_data['OrderField'] : 'Name';
        $order_by = isset($post_data['OrderBy']) ? $post_data['OrderBy'] : 'ASC';
        $list_type = isset($post_data['ListType']) ? $post_data['ListType'] : 0;

        $allwed_order_by_fields = array(
            'Name' => 'NG.Name',
            'TotalMember' => 'NG.TotalMember',
        );
        $allwed_order_by = array(
            'ASC',
            'DESC'
        );

        if (in_array($order_field, array_keys($allwed_order_by_fields))) {
            $order_field = $allwed_order_by_fields[$order_field];
        } else {
            $order_field = 'NG.Name';
        }

        if (!in_array($order_by, $allwed_order_by)) {
            $order_by = 'ASC';
        }

        if (!$page_no)
            $page_no = 1;
        $offset = ($page_no - 1) * $page_size;

        $global_settings = $this->config->item("global_settings");
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $select_array = array(
            'NG.*',
            'DATE_FORMAT(NG.CreatedDate, "' . $mysql_date . '") AS CreatedDateF',
            'DATE_FORMAT(NG.ModifiedDate, "' . $mysql_date . '") AS ModifiedDateF',
        );

        $this->db->select(implode(',', $select_array), FALSE)
                ->from(NEWSLETTERGROUP . ' NG')
                ->where('NG.StatusID', 2);
        if ($list_type) {
            if ($list_type == 1) {
                $this->db->where('NG.IsAutoUpdate', 0);
            } else if ($list_type == 2) {
                $this->db->where('NG.IsAutoUpdate', 1);
            }
        }

        $this->db->group_by('NG.NewsLetterGroupID');

        if ($name) {
            $this->db->like('NG.Name', $name);
        }

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $total_groups = $temp_q->num_rows();

        /* Start_offset, end_offset */
        if ($page_size) {
            $this->db->limit($page_size, $offset);
        }




        $this->db->order_by($order_field, $order_by);

        $compiled_query = $this->db->_compile_select(); //echo $compiled_query;
        $this->db->reset_query();

        $query = $this->db->query($compiled_query);

        $group_list = $query->result_array();
        foreach ($group_list as $index => $groupData) {
            if ($groupData['AutoUpdateFilter']) {
                $groupData['AutoUpdateFilter'] = json_decode($groupData['AutoUpdateFilter'], true);

                $userIncludeList = !empty($groupData['AutoUpdateFilter']['userIncludeList']) ? $groupData['AutoUpdateFilter']['userIncludeList'] : [];
                if (!empty($userIncludeList)) {
                    $groupData['AutoUpdateFilter']['includedUsers'] = $this->newsletter_users_model->search_users(['userIncludeList' => $userIncludeList]);
                }
            }
            $group_list[$index] = $groupData;
        }

        return array(
            'groups' => $group_list,
            'total' => $total_groups
        );
    }

    public function is_valid_newsletter_group($condition) {
        $sql = $this->db->select("NewsLetterGroupID,IFNULL(MailchimpListID,'') AS MailchimpListID", FALSE)
                ->from(NEWSLETTERGROUP)
                ->where($condition)
                ->get();
        return ($sql->num_rows() > 0) ? $sql->row_array() : FALSE;
    }

    public function unsubscribe_newsletter($UnsubscriberData = array(), $UnsubscriberWhere = array()) {
        if (empty($UnsubscriberWhere) || empty($UnsubscriberData))
            return false;

        $this->db->where($UnsubscriberWhere);
        $this->db->update(NEWSLETTERSUBSCRIBER, $UnsubscriberData);
        return true;
    }

    /**
     * Function for add new newsletter group 
     * Parameters : Data array
     * Return : newly added newsletter group id
     */
    public function create_newsletter_group($CreateNewsGroup) {
        $this->db->insert(NEWSLETTERGROUP, $CreateNewsGroup);
        return $this->db->insert_id();
    }

    public function update_newsletter_group($NewsGroupData = array(), $NewsGroupWhere = array()) {

        if (empty($NewsGroupWhere) || empty($NewsGroupData))
            return false;

        $userIncludeList = isset($NewsGroupData['userIncludeList']) ? $NewsGroupData['userIncludeList'] : [];
        unset($NewsGroupData['userIncludeList']);
        $deletedIncludedUsers = [];
        $NewsLetterGroupID = !empty($NewsGroupWhere['NewsLetterGroupID']) ? $NewsGroupWhere['NewsLetterGroupID'] : 0;
        if ($NewsLetterGroupID) {
            $this->db->select('*', FALSE)
                    ->from(NEWSLETTERGROUP . ' NG')
                    ->where('NG.NewsLetterGroupID', $NewsLetterGroupID);
            $compiled_query = $this->db->_compile_select();
            $this->db->reset_query();
            $query = $this->db->query($compiled_query);
            $group = $query->row_array();

            if (!empty($group['AutoUpdateFilter'])) {
                $NewsGroupData['AutoUpdateFilter'] = json_decode($group['AutoUpdateFilter'], true);

                $deletedIncludedUsers = array_diff($NewsGroupData['AutoUpdateFilter']['userIncludeList'], $userIncludeList);

                $NewsGroupData['AutoUpdateFilter']['userIncludeList'] = $userIncludeList;

                $NewsGroupData['AutoUpdateFilter'] = json_encode($NewsGroupData['AutoUpdateFilter']);

                if ($deletedIncludedUsers) {
                    // Delete deleted included users from mailchimp
                    $requestData['deletedIncludedUsers'] = $deletedIncludedUsers;
                    //$requestData['deletedIncludedUsers'] = $userIncludeList;
                    $requestData['NewsLetterGroupIDNew'] = $NewsLetterGroupID;

                    $NewsLetterSubscribersExcludedMembers = $this->newsletter_users_model->get_users($requestData);
                    $NewsLetterSubscribersExcludedMembers = isset($NewsLetterSubscribersExcludedMembers['users']) ? $NewsLetterSubscribersExcludedMembers['users'] : [];
                    $this->delete_members_from_mailchimp($NewsLetterSubscribersExcludedMembers, $group);
                }
            }
        }




        $this->db->where($NewsGroupWhere);
        $this->db->update(NEWSLETTERGROUP, $NewsGroupData);
        //echo $this->db->last_query();die;
        return true;
    }

    public function add_newsletter_group_subscribers($GroupSubscriberData = array()) {
        if (empty($GroupSubscriberData) || !is_array($GroupSubscriberData)) {
            return false;
        }

        // $this->db->insert_batch(NEWSLETTERGROUPMEMBER, $GroupSubscriberData); 
        $this->db->insert_on_duplicate_update_batch(NEWSLETTERGROUPMEMBER, $GroupSubscriberData);
    }

    public function update_group_total_member($NewsLetterGroupID) {

        $this->db->where('NewsLetterGroupID', $NewsLetterGroupID);
        $this->db->where('StatusID', 2);
        $this->db->from(NEWSLETTERGROUPMEMBER);
        $totalMemebers = $this->db->count_all_results();

        $this->db->set('TotalMember', $totalMemebers);
        $this->db->where('NewsLetterGroupID', $NewsLetterGroupID);
        $this->db->update(NEWSLETTERGROUP);

        return true;
    }

    public function get_group_subscribers($WhereCondition, $NewsLetterSubscriberID) {
        $this->db->select("IFNULL(NGM.MailchimpSubscriberID,0) AS MailchimpSubscriberID, NGM.NewsLetterSubscriberID, NGM.NewsLetterGroupID, NS.Email", FALSE);
        $this->db->from(NEWSLETTERGROUPMEMBER . ' NGM ');
        $this->db->join(NEWSLETTERSUBSCRIBER . " NS ", "NS.NewsLetterSubscriberID = NGM.NewsLetterSubscriberID ", "left");
        $this->db->where($WhereCondition);
        $this->db->group_start();
        if (count($NewsLetterSubscriberID) < 200) {
            $this->db->where_in('NGM.NewsLetterSubscriberID', $NewsLetterSubscriberID);
        } else {
            $chunk_arr = array_chunk($NewsLetterSubscriberID, 200);
            foreach ($chunk_arr as $key => $value) {
                $value = array_filter($value);
                if ($key === 0) {
                    $this->db->where_in('NGM.NewsLetterSubscriberID', $value);
                } else {
                    $this->db->or_where_in('NGM.NewsLetterSubscriberID', $value);
                }
            }
        }
        $this->db->group_end();
        $sql = $this->db->get();
        return ($sql->num_rows() > 0) ? $sql->result_array() : array();
    }

    public function remove_subscribers_from_group($WhereCondition, $NewsLetterSubscriberID) {
        $this->db->where($WhereCondition);
        $this->db->group_start();
        if (count($NewsLetterSubscriberID) < 200) {
            $this->db->where_in('NewsLetterSubscriberID', $NewsLetterSubscriberID);
        } else {
            $chunk_arr = array_chunk($NewsLetterSubscriberID, 200);
            foreach ($chunk_arr as $key => $value) {
                $value = array_filter($value);
                if ($key === 0) {
                    $this->db->where_in('NewsLetterSubscriberID', $value);
                } else {
                    $this->db->or_where_in('NewsLetterSubscriberID', $value);
                }
            }
        }
        $this->db->group_end();
        $this->db->delete(NEWSLETTERGROUPMEMBER);
        return true;
    }

    public function get_all_assigned_group($WhereCondition) {
        $sqlObj = $this->db->select("NGM.NewsLetterGroupID,NGM.NewsLetterSubscriberID,IFNULL(NGM.MailchimpSubscriberID,0) AS MailchimpSubscriberID,IFNULL(NG.MailchimpListID,0) AS MailchimpListID", FALSE)
                ->from(NEWSLETTERGROUPMEMBER . " AS NGM")
                ->join(NEWSLETTERGROUP . " AS NG", "NG.NewsLetterGroupID = NGM.NewsLetterGroupID", "INNER")
                ->where("NGM.NewsLetterSubscriberID", $WhereCondition['NewsLetterSubscriberID'])
                ->get();

        return ($sqlObj->num_rows() > 0) ? $sqlObj->result_array() : array();
    }

    public function remove_subscriber_from_all_groups($WhereCondition, $RemoveGroupArr) {
        $this->db->where($WhereCondition);
        $this->db->group_start();
        if (count($RemoveGroupArr) < 200) {
            $this->db->where_in('NewsLetterGroupID', $RemoveGroupArr);
        } else {
            $chunk_arr = array_chunk($RemoveGroupArr, 200);
            foreach ($chunk_arr as $key => $value) {
                $value = array_filter($value);
                if ($key === 0) {
                    $this->db->where_in('NewsLetterGroupID', $value);
                } else {
                    $this->db->or_where_in('NewsLetterGroupID', $value);
                }
            }
        }
        $this->db->group_end();
        $this->db->update(NEWSLETTERGROUPMEMBER, array('StatusID' => 3, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
        return true;
    }

    /* Cron Functions for sync mailchimp records */

    public function get_all_newsletter_group() {
        $sql = $this->db->select("NewsLetterGroupID,IFNULL(MailchimpListID,'') AS MailchimpListID", FALSE)
                ->from(NEWSLETTERGROUP)
                ->where("MailchimpListID IS NOT NULL", null, FALSE)
                ->where("MailchimpListID !=", 0, FALSE)
                ->where("StatusID", 2)
                ->order_by("CreatedDate", "DESC")
                ->get();
        return ($sql->num_rows() > 0) ? $sql->result_array() : array();
    }

    public function get_all_nonsynched_subscribers($WhereCondition) {
        $sql = $this->db->select("NGM.NewsLetterGroupID,NGM.NewsLetterSubscriberID,IFNULL(NGM.MailchimpSubscriberID,'') AS MailchimpSubscriberID,NS.*", FALSE)
                ->from(NEWSLETTERGROUPMEMBER . " AS NGM")
                ->join(NEWSLETTERSUBSCRIBER . " NS", "NS.NewsLetterSubscriberID = NGM.NewsLetterSubscriberID", "INNER")
                ->where("NGM.NewsLetterGroupID", $WhereCondition['NewsLetterGroupID'])
                ->where("NGM.StatusID", $WhereCondition['StatusID'])
                ->where("NGM.MailchimpStatus = 0", NULL, FALSE)
                ->group_start()
                ->where("NGM.MailchimpSubscriberID IS NULL", null, FALSE)
                ->or_where("NGM.MailchimpSubscriberID", 0, FALSE)
                ->group_end()
                ->order_by("NGM.CreatedDate", "DESC")
                ->get();
        //echo $this->db->last_query();die;            
        return ($sql->num_rows() > 0) ? $sql->result_array() : array();
    }

    public function autoUpdateToSpecificGroupList($newsletter_group_id) {
        $this->load->model(array(
            'admin/newsletter/newsletter_model', 'admin/login_model',
            'settings_model', 'admin/newsletter/newsletter_users_model',
            'admin/newsletter/newsletter_mailchimp_model'
        ));

        $this->newsletter_model->autoUpdateGroupLists($newsletter_group_id);
    }

    public function autoUpdateGroupLists($newsletter_group_id = 0) {

        $select_array = array(
            'NG.*'
        );

        $this->db->select(implode(',', $select_array), FALSE)
                ->from(NEWSLETTERGROUP . ' NG')
                ->where('NG.StatusID', 2)
                ->where('NG.IsAutoUpdate', 1)
        ;

        //Update to specific group
        if ($newsletter_group_id) {
            $this->db->where('NG.NewsLetterGroupID', $newsletter_group_id);
        }

        $compiled_query = $this->db->_compile_select();
        $this->db->reset_query();
        $query = $this->db->query($compiled_query);
        $group_list = $query->result_array();
        foreach ($group_list as $index => $groupData) {
            if ($groupData['AutoUpdateFilter']) {
                $groupData['AutoUpdateFilter'] = json_decode($groupData['AutoUpdateFilter'], TRUE);
            }
            $group_list[$index] = $groupData;

            $requestData = $groupData['AutoUpdateFilter'];
            if (empty($requestData)) {
                continue;
            }
            $requestData['CheckGrpMembers'] = 1;
            $requestData['CheckGrpMembersExcluded'] = 0;
            $requestData['NewsLetterGroupIDNew'] = $groupData['NewsLetterGroupID'];            //echo $requestData['NewsLetterGroupID']; die;

            $NewsLetterSubscribersNewMembers = $this->newsletter_users_model->get_users($requestData);
            $subscribers = $NewsLetterSubscribersNewMembers['users'];

            $this->deleteExcludeMembers($requestData, $groupData);


            if (empty($subscribers)) {
                continue;
            }

            $this->add_subscribers_to_mailchimp($subscribers, $groupData['NewsLetterGroupID'], $groupData['MailchimpListID']);
        }
    }

    public function deleteExcludeMembers($requestData, $groupData) {
        // Get excluded members from the rule
        $requestData['CheckGrpMembersExcluded'] = 1;
        $NewsLetterSubscribersExcludedMembers_query = $this->newsletter_users_model->get_users($requestData, 1, 1);
        $NewsLetterSubscribersExcludedMembers_query = "Select NewsLetterSubscriberID From " . NEWSLETTERGROUPMEMBER . " NLGME WHERE NLGME.NewsLetterSubscriberID NOT IN ($NewsLetterSubscribersExcludedMembers_query) AND NLGME.NewsLetterGroupID = " . $groupData['NewsLetterGroupID'];


        //echo $NewsLetterSubscribersExcludedMembers_query; die;

        $requestData['CheckGrpMembersExcludedQuery'] = $NewsLetterSubscribersExcludedMembers_query;
        $NewsLetterSubscribersExcludedMembers = $this->newsletter_users_model->get_users($requestData); //print_r($NewsLetterSubscribersExcludedMembers); die;

        $NewsLetterSubscribersExcludedMembers = isset($NewsLetterSubscribersExcludedMembers['users']) ? $NewsLetterSubscribersExcludedMembers['users'] : [];

        $this->delete_members_from_mailchimp($NewsLetterSubscribersExcludedMembers, $groupData);
    }

    public function delete_members_from_mailchimp($NewsLetterSubscribersExcludedMembers, $groupData) {
        $MemberDeleteBatch = array();
        foreach ($NewsLetterSubscribersExcludedMembers as $newsletter_subsribers_group) {
            // Remove subscriber from groups
            $WhereCondition = array('NewsLetterGroupID' => $groupData['NewsLetterGroupID']);
            $this->newsletter_model->remove_subscribers_from_group($WhereCondition, $newsletter_subsribers_group['NewsLetterSubscriberID']);
            $MailchimpListID = isset($groupData['MailchimpListID']) ? $groupData['MailchimpListID'] : '';
            $mailchimp_subscriber_id = !empty($newsletter_subsribers_group['MailchimpSubscriberID']) ? $newsletter_subsribers_group['MailchimpSubscriberID'] : md5(strtolower($newsletter_subsribers_group['Email']));
            if (!$MailchimpListID) {
                continue;
            }
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

    public function add_subscribers_to_mailchimp($subscribers_value, $NewsLetterGroupID, $MailchimpListID) {
        $this->load->model(array(
            'admin/newsletter/newsletter_mailchimp_model',
            'admin/newsletter/newsletter_users_model'
        ));


        $currentDate = get_current_date('%Y-%m-%d %H:%i:%s');
        //$subscriber_value = $this->newsletter_users_model->set_location_and_tags($subscriber_value);



        $MembersBatch = array();
        $CreateGroupSubscribers = array();
        $CreateGroupSubscriber = [];

        foreach ($subscribers_value as $subscriber_value) {

            $CreateGroupSubscriber['NewsLetterGroupID'] = $NewsLetterGroupID;
            $CreateGroupSubscriber['NewsLetterSubscriberID'] = $subscriber_value['NewsLetterSubscriberID'];
            $CreateGroupSubscriber['CreatedDate'] = $currentDate;
            $CreateGroupSubscriber['ModifiedDate'] = $currentDate;
            $CreateGroupSubscriber['StatusID'] = 2; //Active  

            $CreateGroupSubscribers[] = $CreateGroupSubscriber;


            $merge_fields = array(
                'FNAME' => $subscriber_value['FirstName'],
                "LNAME" => $subscriber_value['LastName'],
                "GENDER" => $this->subscriberGenders[$subscriber_value['Gender']],
                "DOB" => ($subscriber_value['DOB']) ? $subscriber_value['DOB'] : '',
                "LOCATION" => $subscriber_value["LocationStr"],
                "USERTYPE" => $subscriber_value["UserTypeTagsStr"],
                "TAGS" => $subscriber_value["TagsStr"],
            );


            $operationsArr = array(
                'method' => "POST",
                'path' => "lists/" . $MailchimpListID . "/members",
                'body' => (string) json_encode(array(
                    "email_address" => $subscriber_value['Email'],
                    'status' => "subscribed",
                    "timestamp_signup" => $currentDate,
                    "merge_fields" => $merge_fields
                ))
            );

            array_push($MembersBatch, $operationsArr);
        }

        //Create Batch operation on Mailchimp for add members on given newsletter group(list).
        if (!empty($MembersBatch)) {
            $merge_fields_batch_id = '';
            $this->newsletter_mailchimp_model->update_mailchimp_subscriber_list_members($MembersBatch, $merge_fields_batch_id, $MailchimpListID);
        }

        $this->newsletter_model->add_newsletter_group_subscribers($CreateGroupSubscribers);
        $this->newsletter_model->update_group_total_member($NewsLetterGroupID);
    }

    public function update_mailchimp_subscriber_ids() {

        $this->load->model(array(
            'admin/newsletter/newsletter_mailchimp_model',
            'admin/newsletter/newsletter_users_model'
        ));


        $valid_newsletter_group = $this->get_all_newsletter_group();
        //echo '<pre>';print_r($valid_newsletter_group);die;

        if (empty($valid_newsletter_group)) {
            exit("No record found for synching..");
        }

        $TotalUpdates = array();
        foreach ($valid_newsletter_group as $group_key => $group_value) {
            if (empty($group_value['NewsLetterGroupID']) || empty($group_value['MailchimpListID'])) {
                continue;
            }

            $NewsLetterGroupID = $group_value['NewsLetterGroupID'];
            $MailchimpListID = $group_value['MailchimpListID'];
            $UpdateSubscriberData = array();

            $WhereCondition = array('NewsLetterGroupID' => $NewsLetterGroupID, 'StatusID' => 2);
            $nonsynched_subscribers = $this->get_all_nonsynched_subscribers($WhereCondition);
            //echo '<pre>';print_r($nonsynched_subscribers);die;
            //if nonsynched subscribers empty then skip this group and continue with other records. 
            if (empty($nonsynched_subscribers)) {
                continue;
            }

            //loop through all subscribers and update.
            foreach ($nonsynched_subscribers as $suubscriber_key => $subscriber_value) {
                $subscriber_hash = md5(strtolower($subscriber_value['Email']));
                $MemberResponse = $this->newsletter_mailchimp_model->update_mailchimp_subscriber_id($MailchimpListID, $subscriber_hash);
                // echo '<pre>';print_r($ListResponse);die;
                if (!empty($MemberResponse['id']) && !empty($MemberResponse['unique_email_id'])) {
                    $updateArr = array('MailchimpSubscriberID' => $MemberResponse['id']);
                    $UpdateCondition = array('NewsLetterGroupID' => $NewsLetterGroupID, 'NewsLetterSubscriberID' => $subscriber_value['NewsLetterSubscriberID']);
                    $this->update(NEWSLETTERGROUPMEMBER, $updateArr, $UpdateCondition);
                }

                //Try to add to mailchimp
                if (isset($MemberResponse['status']) && $MemberResponse['status'] == 404) {
                    $this->add_subscriber_to_mailchimp($subscriber_value, $group_value['NewsLetterGroupID'], $MailchimpListID);
                }
            }
        }

        exit("Subscribers are synched with Mailchimp members..");
    }

    public function add_subscriber_to_mailchimp($subscriber_value, $NewsLetterGroupID, $MailchimpListID) {
        $currentDate = get_current_date('%Y-%m-%d %H:%i:%s');

        $subscriber_value = $this->newsletter_users_model->set_location_and_tags($subscriber_value);

        $merge_fields = array(
            'FNAME' => $subscriber_value['FirstName'],
            "LNAME" => $subscriber_value['LastName'],
            "GENDER" => $this->subscriberGenders[$subscriber_value['Gender']],
            "DOB" => ($subscriber_value['DOB']) ? $subscriber_value['DOB'] : '',
            "LOCATION" => $subscriber_value["LocationStr"],
            "USERTYPE" => $subscriber_value["UserTypeTagsStr"],
            "TAGS" => $subscriber_value["TagsStr"],
        );

        $operationsArr = array(
            'method' => "POST",
            'path' => "lists/" . $MailchimpListID . "/members",
            'body' => array(
                "email_address" => $subscriber_value['Email'],
                'status' => "subscribed",
                "timestamp_signup" => $currentDate,
                "merge_fields" => $merge_fields
            )
        );


        $batchResponse = $this->newsletter_mailchimp_model->add_subscriber_to_list($operationsArr);

        if (isset($batchResponse['status']) && $batchResponse['status'] == 'subscribed') {

//            $updateArr = array('MailchimpStatus' => $batchResponse['status']);
//            $UpdateCondition = array('NewsLetterGroupID' => $NewsLetterGroupID, 'NewsLetterSubscriberID' => $subscriber_value['NewsLetterSubscriberID']);
//            $this->update(NEWSLETTERGROUPMEMBER, $updateArr, $UpdateCondition);
        } else {

            if (!isset($batchResponse['status'])) {
                return;
            }

            $updateArr = array('MailchimpStatus' => $batchResponse['status']);
            $UpdateCondition = array('NewsLetterGroupID' => $NewsLetterGroupID, 'NewsLetterSubscriberID' => $subscriber_value['NewsLetterSubscriberID']);
            $this->update(NEWSLETTERGROUPMEMBER, $updateArr, $UpdateCondition);
        }
    }

    public function mailchimp_webhook($webhook_data) {

        $type = !empty($webhook_data['type']) ? $webhook_data['type'] : '';

        // On unsubscribe form list update data to system
        if ($type == 'unsubscribe' && !empty($webhook_data['data']) && !empty($webhook_data['data']['list_id'])) {

            $email = !empty($webhook_data['data']['email']) ? $webhook_data['data']['email'] : '';
            $list_id = !empty($webhook_data['data']['list_id']) ? $webhook_data['data']['list_id'] : '';

            if (!$email || !$list_id) {
                return;
            }

            $sqlObj = $this->db->select("NGM.NewsLetterGroupID, NS.NewsLetterSubscriberID, NGM.NewsLetterGroupMemberID", FALSE)
                    ->from(NEWSLETTERGROUPMEMBER . " AS NGM")
                    ->join(NEWSLETTERGROUP . " AS NG", "NG.NewsLetterGroupID = NGM.NewsLetterGroupID", "INNER")
                    ->join(NEWSLETTERSUBSCRIBER . " AS NS", "NS.NewsLetterSubscriberID = NGM.NewsLetterSubscriberID", "INNER")
                    ->where("NS.Email", $email)
                    //->where("NG.MailchimpListID", $list_id)
                    //->group_by("NG.MailchimpListID")
                    ->get();

            $group_members = $sqlObj->result_array();   
            
            $newsletter_group_member_ids = [];
            $newsletter_group_ids = [];
            $newsletter_subscriber_id = 0;
            foreach ($group_members as $data) {
                if (empty($data['NewsLetterGroupMemberID'])) {
                    continue;
                }
                
                $newsletter_group_member_ids[] = $data['NewsLetterGroupMemberID'];
                $newsletter_group_ids[] = $data['NewsLetterGroupID'];
                $newsletter_subscriber_id = $data['NewsLetterSubscriberID'];
            }
            
            if(empty($newsletter_group_member_ids)) {
                return;
            }
            
            // Remove subscriber from group
            $this->db->where_in('NewsLetterGroupMemberID', $newsletter_group_member_ids);
            $this->db->delete(NEWSLETTERGROUPMEMBER);

            // update status of subscriber
            $this->db->where('NewsLetterSubscriberID', $newsletter_subscriber_id);
            $this->db->update(NEWSLETTERSUBSCRIBER, array(
                'Status' => 3
            ));
            
            foreach ($newsletter_group_ids as $newsletter_group_id) {
                $this->newsletter_model->update_group_total_member($newsletter_group_id);
            }
            
            
            
        }
    }
    
    public function iterate_compaigns_reports() {
        $this->load->model(array('admin/newsletter/newsletter_mailchimp_model'));        
        $count = 10;
        $offset = 0;        
        
        do {
            
            $compaign_report_data = $this->newsletter_mailchimp_model->get_compaign_reports($offset);                                
            $compaign_report_list = !empty($compaign_report_data['reports']) ? $compaign_report_data['reports'] : [];            
            $offset = $offset + $count;
            
            foreach($compaign_report_list as $compaign_report) {
                $this->save_compaign_report($compaign_report);
            }
            
            
        } while(!empty($compaigns_list));
        
        
    }
    
    public function save_compaign_report($compaign_report) {
        
        $compaign_id = !empty($compaign_report['id']) ? $compaign_report['id'] : '';
        $list_id = !empty($compaign_report['list_id']) ? $compaign_report['list_id'] : '';
        
        if(!$compaign_id || !$list_id) {
            return;
        }
        
        
        // Get compaign list data
        $sql = $this->db->select("IFNULL(MailchimpListID,'') AS MailchimpListID", FALSE)
            ->from(NEWSLETTER_COMPAIGN_REPORT)            
            ->where("CompaignID", $compaign_id)             
            ->get();        
        if($sql->num_rows() > 0) {
            return;
        }                
        
        $sql = $this->db->select("NewsLetterGroupID, IFNULL(MailchimpListID,'') AS MailchimpListID", FALSE)
            ->from(NEWSLETTERGROUP)            
            ->where("MailchimpListID", $list_id)   
            ->limit(1)
            ->get();        
        $db_list = $sql->row_array();
        
        $newsletter_group_id = !empty($db_list['NewsLetterGroupID']) ? $db_list['NewsLetterGroupID'] : 0;
        if(!$newsletter_group_id) {
            return;
        }
        
        // Prepare inserting data
        $insertingListData = [];
        $compaign_data = json_encode($compaign_report);
         $currentDate = get_current_date('%Y-%m-%d %H:%i:%s');    
        
        $insertingListData[] = array(
            'CompaignID' => $compaign_id,
            'MailchimpListID' => $list_id,
            'NewsLetterGroupID' => $newsletter_group_id,
            'CompaignTitle' => !empty($compaign_report['campaign_title']) ? $compaign_report['campaign_title'] : '',
            
                      
            'EmailSent' => !empty($compaign_report['emails_sent']) ? $compaign_report['emails_sent'] : 0,
            'AbuseReports' => !empty($compaign_report['abuse_reports']) ? $compaign_report['abuse_reports'] : 0,
            'Unsubscribed' => !empty($compaign_report['unsubscribed']) ? $compaign_report['unsubscribed'] : 0,
            'SentTime' => !empty($compaign_report['send_time']) ? $compaign_report['send_time'] : '',
            
            'CompaignData' => $compaign_data,
            
            'CreatedDate' => $currentDate,
            'ModifiedDate' => $currentDate,

        );
        
        $this->db->insert_on_duplicate_update_batch(NEWSLETTER_COMPAIGN_REPORT, $insertingListData);
        
        
    }

}
