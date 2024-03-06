<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Follow_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | Use get follow users
    |@Inputs: (Defined in follow DB Table)
    |--------------------------------------------------------------------------
    */  
    function follow($data)
    {
        if(constant('ALLOW_'.strtoupper($data['Type']).'_FOLLOW') == 1) { //if follow is enabled for this entity
        
            $ntype = '';
            $notify_node = false;

            $follow_details = $this->get_single_row('FollowID,StatusID',FOLLOW,array('UserID' => $data['UserID'], 'TypeEntityID' =>$data['TypeEntityID'], 'Type' => $data['Type']));//get details to check if this already exists            
                        
            $module_id = 0;
            $select_field = "'' AS Name";
            switch($data['Type']) {
                case'user':
                    $module_id = 3;
                    $select_field = "Concat(FirstName, ' ',  LastName) AS Name";
                    break;
                case'category':
                    $module_id = 27;
                    $select_field = "Name";
                    break;
                case'page':
                    $module_id = 18;
                    $select_field = "Title AS Name";
                    break;
            }
            
            $entity_details = get_detail_by_id($data['TypeEntityID'], $module_id, $select_field, 2);
            $entity_name = isset($entity_details['Name']) ? $entity_details['Name'] : '';
            
            if($data['Type'] == 'page') {
                $entity_name = ' page '.$entity_name;
            }
            
            
            
            if(count($follow_details) > 0) { //already following             

                if($follow_details['StatusID']==1) {
                    //$result['msg'] = 'You have already sent request to follow this '.$data['Type'].'.';
                    $result['msg'] = sprintf(lang('already_sent_follow_request'), $entity_name);
                    $result['msg_type'] = 'success';
                    $result['result'] = 'success';
                } else {
                    $this->db->where('UserID',$data['UserID'])
                             ->where('TypeEntityID',$data['TypeEntityID'])
                             ->where('Type',$data['Type'])
                             ->delete(FOLLOW);

                    $module_id = 3;         
                    if($data['Type'] == 'user') {//generate activity and notification only if the follow is for user entity
                        $this->db->where('ActivityTypeID','3')
                             ->where('UserID',$data['UserID'])
                             ->where('ModuleEntityID',$data['TypeEntityID'])
                             ->where('ModuleID', $module_id)
                             ->update(ACTIVITY,array('StatusID'=>'3', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
                    
                        if (CACHE_ENABLE) 
                        {
                            $this->cache->delete('user_followers_'.$data['UserID']);
                        }    
                    }
                    elseif($data['Type'] == 'page')
                    {
                        $module_id = 18;
                        $this->db->where('ActivityTypeID','3')
                             ->where('UserID',$data['UserID'])
                             ->where('ModuleEntityID',$data['TypeEntityID'])
                             ->where('ModuleID', $module_id)
                             ->update(ACTIVITY,array('StatusID'=>'3', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));

                        // Update Follower count in page table by 1
                        $this->db->where('PageID',$data['TypeEntityID']);
                        $this->db->set('NoOfFollowers', 'NoOfFollowers-1', FALSE);
                        $this->db->update(PAGES);
                        $status_id = 3;             
                        $this->load->model('subscribe_model');
                        $this->subscribe_model->update_subscription($data['UserID'], 'PAGE', $data['TypeEntityID'], $status_id);
     
                    }elseif($data['Type'] == 'category'){ //un follow category / sub category group
                        $module_id = 27;
                        $this->db->where('ActivityTypeID','3')
                             ->where('UserID',$data['UserID'])
                             ->where('ModuleEntityID',$data['TypeEntityID'])
                             ->where('ModuleID', $module_id)
                             ->update(ACTIVITY,array('StatusID'=>'3', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));

                        // get all categorys / sub-categorys of category for group
                        $this->load->model('category/category_model');
                        $categoryIDs = $this->category_model->get_all_subcategories($data['TypeEntityID'], 1);
                        $categoryIDs[] =  $data['TypeEntityID'];

                            // Unfollow all the sub categories if parent category unfollows

                            if(!empty($categoryIDs))
                            {

                                 $this->db->where('UserID',$data['UserID'])
                                 ->where_in('TypeEntityID',$categoryIDs)
                                 ->where('Type',$data['Type'])
                                 ->delete(FOLLOW);
                            }
                        // get all groupids wrt to category ids
                       
/*                        $this->load->model('group/group_model');
                        $get_category_groupIDs = $this->group_model->get_category_groups($categoryIDs, FALSE, $data['UserID']);
                        if(isset($get_category_groupIDs) && !empty($get_category_groupIDs)){
                            // leave member to group
                            foreach ($get_category_groupIDs as $key => $category_group) {
                                $leave_group_data = array('GroupID' => $category_group['GroupID'], 'ModuleEntityID' => $data['UserID'], 'ModuleID' => 3,'memberGroupRole' => 6);
                                $this->group_model->leave($leave_group_data);
                            }
                        }*/


                    }

                    if(!$this->settings_model->isDisabled(28))
                    {
                        $this->load->model(array('reminder/reminder_model'));
                        $this->reminder_model->delete_all($data['UserID'], $module_id, $data['TypeEntityID']);
                    }

                    $result['msg'] = sprintf(lang('unfollow_success'), $entity_name);
                    $result['msg_type'] = 'success';
                    $result['result'] = 'success';            
                }
            } else { //insert in follow and reeturn success message      

                $privacyKey     = 'follow_request';
                $privacy_status = 0;  // Get Privacy Setting status for follow Request if 1 then request will be sent
                if($privacy_status!=1) { 

                    $this->insert(FOLLOW, array('UserID' =>$data['UserID'], 'TypeEntityID' =>$data['TypeEntityID'] ,'EntityOwnerID' =>$data['TypeEntityID'], 'Type' =>$data['Type'],'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'), 'StatusID'=>'2'));
                    //$result['msg'] = 'You are now following this '.$data['Type'].'.';
                    $result['msg'] = sprintf(lang('follow_success'), $entity_name);
                    $result['msg_type'] = 'success';
                    $result['result']   = 'success';
                                    
                    if($data['Type'] == 'user') {//generate activity and notification only if the follow is for user entity
                        $ntype = 'FU';
                        $notify_node = true;

                        $activity['UserID'] = $data['UserID'];
                        $activity['EntityID'] = $data['TypeEntityID'];
                        $activity['EntityType'] = 'User';

                        $this->activity_model->addActivity(3,$data['TypeEntityID'],3,$data['UserID']);

                        $parameters[0]['ReferenceID'] = $data['UserID'];
                        $parameters[0]['Type'] = 'User';
                        $this->notification_model->add_notification(5,$data['UserID'],array($data['TypeEntityID']),$data['TypeEntityID'],$parameters);
                        $this->subscribe_model->subscribe_email($data['UserID'],$data['TypeEntityID'],'following_user');
                        if (CACHE_ENABLE) 
                        {
                            $this->cache->delete('user_followers_'.$data['UserID']);
                        } 
                    }elseif($data['Type'] == 'page'){ //generate activity and notification only if the follow is for page entity
                        $ntype = 'FP';
                        $notify_node = true;

                        $activity['UserID']     = $data['UserID'];
                        $activity['EntityID']   = $data['TypeEntityID'];
                        $activity['EntityType'] = 'User';
                        $this->activity_model->addActivity(18,$data['TypeEntityID'],3,$data['UserID']);

                        // Update Follower count in follow table by 1
                        $this->db->where('PageID',$data['TypeEntityID']);
                        $this->db->set('NoOfFollowers', 'NoOfFollowers+1', FALSE);
                        $this->db->update(PAGES);

                        $parameters[0]['ReferenceID'] = $data['UserID'];
                        $parameters[0]['Type'] = 'User';
                        $parameters[1]['ReferenceID'] = $data['TypeEntityID'];
                        $parameters[1]['Type'] = 'Page';
                        $this->load->model('page_model');
                        $page_owner = $this->page_model->get_page_owner(get_detail_by_id($data['TypeEntityID'],18,'PageGUID'));
                        $this->notification_model->add_notification(45,$data['UserID'],array($page_owner),$data['TypeEntityID'],$parameters);
                        $this->subscribe_model->subscribe_email($data['UserID'],$data['TypeEntityID'],'following_page');
                    }elseif($data['Type'] == 'category'){ //generate activity and notification only if the follow is for category entity
                        $ntype = 'FC';
                        $notify_node = true;

                        $activity['UserID']     = $data['UserID'];
                        $activity['EntityID']   = $data['TypeEntityID'];
                        $activity['EntityType'] = 'User';
                        $this->activity_model->addActivity(27,$data['TypeEntityID'],3,$data['UserID']);

                        // get all categorys / sub-categorys of category for group
                        $this->load->model('category/category_model');
                        $categoryIDs = $this->category_model->get_all_subcategories($data['TypeEntityID'], 1);
                        $categoryIDs[] =  $data['TypeEntityID'];

                   
                            if(!empty($categoryIDs))
                            {
                                 $this->db->where('UserID',$data['UserID'])
                                 ->where_in('TypeEntityID',$categoryIDs)
                                 ->where('Type',$data['Type'])
                                 ->delete(FOLLOW);
                     

                                $insertData = array();

                                foreach ($categoryIDs as $val) {
                                    $cat = array();
                                    $cat['UserID'] = $data['UserID'];
                                    $cat['TypeEntityID'] = $val;
                                    $cat['EntityOwnerID'] = $val;
                                    $cat['Type'] = $data['Type'];
                                    $cat['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                                    $cat['StatusID'] = 2;
                                
                                    $insertData[] = $cat;
                                }

                                $this->db->insert_batch(FOLLOW,$insertData);
                            }




                        // get all groupids wrt to category ids
                        /*$this->load->model('group/group_model');
                        $get_category_groupIDs = $this->group_model->get_category_groups($categoryIDs, TRUE);
                        if(isset($get_category_groupIDs) && !empty($get_category_groupIDs)){
                            // add member to group
                            $status_id = 2;
                            $params = "";
                            $added_as = 2;
                            $added_by = $data['UserID'];
                            $member = array(array('ModuleEntityID' => $data['UserID'], 'ModuleID' => 3));
                            foreach ($get_category_groupIDs as $key => $category_group) {
                                $this->group_model->add_members($category_group['GroupID'], $member, FALSE, $status_id, $params, $added_as, $added_by, FALSE, TRUE);
                            }
                        }*/


                    }
                } else {
                    $this->insert(FOLLOW, array('UserID' =>$data['UserID'], 'TypeEntityID' =>$data['TypeEntityID'] ,'EntityOwnerID' =>$data['TypeEntityID'],'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'),'Type' =>$data['Type'],'StatusID' =>'1'));
                    //$result['msg'] = 'Your request has been sent to follow this '.$data['Type'].'.';
                    $result['msg'] = sprintf(lang('follow_request_sent'), $entity_name);
                    $result['msg_type'] = 'success';
                    $result['result'] = 'success';
                                    
                    if($data['Type'] == 'user') {//generate notification only if the follow is for user entity and activity will be generated when user accepts the request
                        $parameters[0]['ReferenceID'] = $data['UserID'];
                        $parameters[0]['Type'] = 'User';
                    }
                }            
            }

            // call insert_page_member function to insert follow page userid 
            if($data['Type'] == 'page'){ 
                $this->load->model('pages/page_model');
                $this->page_model->insert_page_member($data['TypeEntityID'],$data['UserID']);
                if (CACHE_ENABLE) 
                {
                    $this->cache->delete('top_page_'.$data['UserID']);
                }
                initiate_worker_job('page_cache', array('PageID'=>$data['TypeEntityID'] ));
            }

            if($notify_node)
            {
                notify_node('liveFeed',array('Type'=>$ntype,'UserID'=>$data['UserID'],'EntityGUID'=>''));
            }

            return $result;
        }
    }

    /**
     * [auto_follow Used to auto follow user when become friend.]
     * @param  [type] $data [Follow user data]
     */
    function auto_follow($data)
    {
        if(constant('ALLOW_'.strtoupper($data['Type']).'_FOLLOW') == 1) 
        { //if follow is enabled for this entity
        
            $follow_details = $this->get_single_row('FollowID, StatusID',FOLLOW,array('UserID' => $data['UserID'], 'TypeEntityID' =>$data['TypeEntityID'], 'Type' => $data['Type']));//get details to check if this already exists
                        
            if(count($follow_details) > 0) 
            { 
                if($follow_details['StatusID']!=2) 
                {
                    $this->db->where('FollowID',$follow_details['FollowID']);
                    $this->db->set('StatusID', '2', FALSE);
                    $this->db->set('CreatedDate', get_current_date('%Y-%m-%d %H:%i:%s'), FALSE);
                    $this->db->update(FOLLOW);
                }
            }
            else
            {
                $this->insert(FOLLOW, array('UserID' =>$data['UserID'], 'TypeEntityID' =>$data['TypeEntityID'] ,'EntityOwnerID' =>$data['TypeEntityID'], 'Type' =>$data['Type'],'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'), 'StatusID'=>'2'));
                                                        
                if($data['Type'] == 'user') 
                {//generate activity and notification only if the follow is for user entity

                    $activity['UserID'] = $data['UserID'];
                    $activity['EntityID'] = $data['TypeEntityID'];
                    $activity['EntityType'] = 'User';

                    $this->activity_model->addActivity(3,$data['TypeEntityID'],3,$data['UserID']);
                }
            }
        }
    }

    /**
     * [auto_unfollow Used to auto un-follow user when friendship remove.]
     * @param  [type] $data [un-Follow user data]
     */
    function auto_unfollow($data)
    {
        if(constant('ALLOW_'.strtoupper($data['Type']).'_FOLLOW') == 1) 
        { //if follow is enabled for this entity
        
            $follow_details = $this->get_single_row('FollowID, StatusID',FOLLOW,array('UserID' => $data['UserID'], 'TypeEntityID' =>$data['TypeEntityID'], 'Type' => $data['Type']));//get details to check if this already exists
                        
            if(count($follow_details) > 0) 
            { 
                if($follow_details['StatusID']!=1) 
                {
                    $this->db->where('UserID',$data['UserID'])
                             ->where('TypeEntityID',$data['TypeEntityID'])
                             ->where('Type',$data['Type'])
                             ->delete(FOLLOW);

                    $module_id = 3;         
                    if($data['Type'] == 'user') 
                    {//generate activity and notification only if the follow is for user entity
                        $this->db->where('ActivityTypeID','3')
                             ->where('UserID',$data['UserID'])
                             ->where('ModuleEntityID',$data['TypeEntityID'])
                             ->where('ModuleID', $module_id)
                             ->update(ACTIVITY,array('StatusID'=>'3', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
                    }                    

                    if(!$this->settings_model->isDisabled(28))
                    {
                        $this->load->model(array('reminder/reminder_model'));
                        $this->reminder_model->delete_all($data['UserID'], $module_id, $data['TypeEntityID']);
                    }
                }
            }
        }
    }    

    /**
     * @Summary: Accept/Reject follow request 
     * @create_date: Wed, Dec 31, 2014
     * @last_update_date:
     * @access: public
     * @inputs:  (Defined in follow DB Table)
     * @return:
     */
    function action_request($data) {            

        $follow_details = $this->get_single_row('FollowID,StatusID',FOLLOW,array('UserID' => $data['RequesterID'], 'EntityOwnerID' =>$data['EntityOwnerID'], 'Type' => $data['Type'],'StatusID'=>1));//get details to check if this already exists
                
        if(count($follow_details) > 0) { // if pending request exists               

            if($data['RequestType']=='approved') {

                $this->db->where('FollowID',$follow_details['FollowID'])->update(FOLLOW,array('StatusID'=>'2'));

                $result['msg'] = 'Approved successfully';
                $result['msg_type'] = 'success';
                $result['result']   = 'success';
                    
                if($data['Type'] == 'user') {//generate activity and notification only if the follow is for user entity

                    $activity['UserID'] = $data['UserID'];
                    $activity['EntityID'] = $data['TypeEntityID'];
                    $activity['EntityType'] = 'User';

                    $this->activity_model->addActivity(3,$data['TypeEntityID'],3,$Data['UserID']);

                    $parameters[0]['ReferenceID'] = $data['UserID'];
                    $parameters[0]['Type'] = 'User';
                    $this->notification_model->add_notification(5,$data['UserID'],array($data['TypeEntityID']),$data['TypeEntityID'],$parameters);
                }
            } else {
                $this->db->where('FollowID',$follow_details['FollowID'])->delete(FOLLOW);

                if($data['Type'] == 'user') {//generate activity and notification only if the follow is for user entity
                    $this->db->where('ActivityTypeID','3')
                    ->where('UserID',$data['UserID'])
                    ->where('EntityID',$data['TypeEntityID'])
                    ->update(ACTIVITY,array('StatusID'=>'3'));
                }
                $result['msg'] = 'Rejected successfully';
                $result['msg_type'] = 'success';
                $result['result']   = 'success';
            } 
        } else { //insert in follow and reeturn success message                           
            $result['ResponseCode'] = 504;
            $result['msg']          = 'Record not found';
            $result['msg_type']     = 'success';
            $result['result']       = 'success';
        }
        return $result;
    }


    function cancelFollowRequest($data) {
        $follow_details = $this->get_single_row('FollowID,StatusID',FOLLOW,array('UserID' => $data['UserID'], 'TypeEntityID' =>$data['TypeEntityID'], 'Type' => $data['Type']));//get details to check if this already exists
                
                
        if(count($follow_details) > 0) {  //already following
            if($follow_details['StatusID']==1) {
                //$sql = "delete from Follow where UserID=".$data['UserID']." and TypeEntityID =  ".$data['TypeEntityID']."  and Type = '".$data['Type']."'";
                //$this->db->query("UPDATE ".ACTIVITY." SET StatusID='3' WHERE ActivityTypeID='3' AND UserID='".$data['UserID']."' AND EntityID='".$data['TypeEntityID']."'");
                //$this->db->query($sql);

                $this->db->where('UserID',$data['UserID'])
                         ->where('TypeEntityID',$data['TypeEntityID'])
                         ->where('Type',$data['Type'])
                         ->delete(FOLLOW);

                                    if($data['Type'] == 'user') {//generate activity and notification only if the follow is for user entity
                $this->db->where('ActivityTypeID','3')
                         ->where('UserID',$data['UserID'])
                         ->where('EntityID',$data['TypeEntityID'])
                         ->update(ACTIVITY,array('StatusID'=>'3'));
                                    }

                $result['msg'] = 'Successfully deleted';
                $result['msg_type'] = 'success';
                $result['result'] = 'success';
            } else {    
                $result['ResponseCode'] = 504;
                $result['msg'] = 'Record not found';
                $result['msg_type'] = 'success';
                $result['result'] = 'success';
    
            }
        } else {
            $result['ResponseCode'] = 504;
            $result['msg']          = 'Record not found';
            $result['msg_type']     = 'success';
            $result['result']       = 'success';
        }
        return $result;
    }
}