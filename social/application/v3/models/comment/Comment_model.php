<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Comment_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }

    
    /**
     * [mark_question_solution Set solution for activity]
     * @param  [int]       $activity_id  [Activity ID]
     */
    function mark_question_solution($activity_id, $solution) {
        $this->db->select('PC.Solution');
        $this->db->from(POSTCOMMENTS . ' PC');
        $this->db->where('PC.EntityType', 'ACTIVITY');
        $this->db->where('PC.EntityID', $activity_id);  
        $this->db->where('PC.StatusID', 2);    
        $this->db->order_by('PC.Solution', 'DESC');    
        $this->db->limit(1);
        $query = $this->db->get();
        if($query->num_rows()) {
            $row = $query->row_array();
            $solution = $row['Solution'];
        } 

        $this->db->set('Solution', $solution);
        $this->db->where('ActivityID', $activity_id);
        $this->db->update(ACTIVITY);  
        if (CACHE_ENABLE)  {
            $this->cache->delete('activity_'.$activity_id);
        }       
    }

    /**
     * [set_solution Set solution for comment]
     * @param  [array]       $comment_details  [comment details]
     */
    function set_solution($comment_details, $user_id) {
        $comment_id = $comment_details['PostCommentID']; 
        $solution = $comment_details['Solution'];
        $from_admin = $comment_details['FromAdmin'];
        $entity_id   = $comment_details['EntityID'];
        $is_point_allowed = $comment_details['IsPointAllowed'];
        $entity_details = get_detail_by_id($entity_id, 0, 'UserID, PostType', 2);
        
        $post_type = $entity_details['PostType'];
        $entity_owner_id = $entity_details['UserID'];
        if($post_type == 2) {
            $this->db->set('Solution', $solution);
            $this->db->where('PostCommentID', $comment_id);
            $this->db->update(POSTCOMMENTS);  
            if(in_array($solution, array(1, 2))) {
                $comment_owner_id = $comment_details['UserID'];
                if(!in_array($user_id, array($comment_owner_id, $entity_owner_id))) {
                    $notification_type_id = 159;
                    if($solution == 2) {
                        $notification_type_id = 160;
                    }
                    
                    $row = $this->get_single_row("NotificationID", NOTIFICATIONS, array('NotificationTypeID' => $notification_type_id, 'ToUserID' => $comment_owner_id, 'RefrenceID' => $entity_id));
                    if (isset($row['NotificationID']) && !empty($row['NotificationID'])) {
                        
                    } else {           
                        $parameters[0]['ReferenceID'] = $comment_owner_id;
                        $parameters[0]['Type'] = 'User';
                        $parameters[1]['ReferenceID'] = 1;
                        $parameters[1]['Type'] = 'EntityType';
                        initiate_worker_job('add_notification', array('NotificationTypeID' => $notification_type_id, 'SenderID' => $user_id, 'ReceiverIDs' => array($comment_owner_id), 'RefrenceID' => $entity_id, 'Parameters' => $parameters, 'ExtraParams' => array('Comment' => $comment_id, 'FromAdmin' => $from_admin)),'','notification');                
                    }  
                }     
                
                if(empty($is_point_allowed)) {
                    $point_data = array('EntityID' => $comment_id, 'EntityType' => 2, 'ActivityTypeID' => 46);
                    if($solution == 2) {
                        $point_data['ActivityTypeID'] = 47;
                    }
                    $point_data['ActivityID'] = $entity_id;
                    $point_data['OID'] = 0;
                    $point_data['UserID'] = $comment_owner_id;
                    $point_data['PT'] = 2;
                    initiate_worker_job('add_point', $point_data,'','point');
                }
                
            } else {  
                $point_data = array('EntityID' => $comment_id, 'EntityType' => 2, 'ActivityTypeID' => 46, 'ParentID' => -1);          
                initiate_worker_job('revert_point', $point_data,'','point');
            }  
        }  
    }

    /**
     * [get_responses Get all comments/replys for an entity]     
     * @param  [int]    $user_id        [User ID]
     * @param  [int]    $data           [Request data]
     * @param  [int]    $count_only     [return only count]     
     * @param  [array]    $blocked_users    [Array of Blocked Users ID]
     * @return [type]               [List of all Comments]
     */
    public function get_responses($user_id, $data, $count_only = 0, $blocked_users = array()) {
        
        $module_id = $data['ModuleID'];
        $module_entity_id = $data['ModuleEntityID'];
        $entity_id = $data['EntityID'];
        $entity_type = $data['EntityType'];

        $page_no        = safe_array_key($data, 'PageNo', PAGE_NO);
        $page_size      = safe_array_key($data, 'PageSize', PAGE_SIZE);
        $only_solutions = safe_array_key($data, 'OnlySolutions', 0);
        $filter         = safe_array_key($data, 'Filter', '');
        $is_owner = $data['IsOwner'];
        
        $postas_module_id = 3;
        $postas_module_entity_id=$user_id;   

        $this->db->select('PC.*,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,U.UserID, U.IsVIP, U.IsAssociation, PU.Url as UserProfileUrl');
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
        $this->db->from(POSTCOMMENTS . ' PC');
        $this->db->join(USERS . ' U', 'PC.UserID=U.UserID', 'left');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
        $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID', 'left');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User" ', 'LEFT');
        $this->db->where('PC.EntityType', $entity_type);        
        $this->db->where('PC.EntityID', $entity_id);
        $this->db->where_in('PC.StatusID', array(2,22));
        if($only_solutions == 1) {
            $this->db->where('PC.Solution >', 0);  
        }

        if(!empty($filter)) {
            if($filter=='Recent') {
                $this->db->order_by('PC.ModifiedDate', 'DESC');   
            } elseif($filter=='Network') {
                $result = array();
                $this->db->order_by("PC.PostCommentID DESC");
            }
        } else {   
            $this->db->order_by("PC.PostCommentID DESC");             
        }
        
        if (empty($count_only)) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        
       
        $sql = $this->db->get();
        //echo $this->db->last_query();die;
        if ($count_only) {
            return $sql->num_rows();
        }
        $comments = array();
        if ($sql->num_rows()) {
            $this->load->model(array(
                'activity/activity_model',
                'activity/activity_result_filter_model'
            ));
            $is_super_admin = $this->user_model->is_super_admin($user_id);
            foreach ($sql->result() as $comment) {
                $comment_postas_module_id = $comment->PostAsModuleID;
                $comment_postas_module_entity_id = $comment->PostAsModuleEntityID;

                $cmnt['IsAdmin'] = 0;                                
                $cmnt['IsAdmin'] = $this->user_model->is_super_admin($comment->UserID, 1);
                
                $cmnt['IsVIP'] =$comment->IsVIP;
                $cmnt['IsAssociation'] =$comment->IsAssociation;
                $cmnt['Solution'] =$comment->Solution;
                $cmnt['PostCommentID'] =$comment->PostCommentID;
                $cmnt['NoOfReplies'] =$comment->NoOfReplies;
                $cmnt['ProfilePicture'] = $comment->ProfilePicture;
                $cmnt['CommentGUID'] = $comment->PostCommentGUID;                
                
                //$edit_post_comment = $comment->PostComment;
                //$cmnt['EditPostComment'] = $this->parse_tag_edit($edit_post_comment);
                $cmnt['PostComment'] = $comment->PostComment;
                $cmnt['PostComment'] = $this->activity_model->parse_tag($cmnt['PostComment']);
                $cmnt['PostComment'] = trim(str_replace('&nbsp;', ' ', $cmnt['PostComment']));
                /*parsed html*/
                if($this->IsApp == 1) {                    
                    $cmnt['PostComment'] = $this->activity_result_filter_model->get_description($cmnt['PostComment']);
                } else {
                    $cmnt['PostComment'] = html_entity_decode($cmnt['PostComment']);
                }
                /*parsed html*/
                $cmnt['UserID'] = $comment->UserID;
                $cmnt['Name'] = $comment->FirstName . ' ' . $comment->LastName;
                $cmnt['CreatedDate'] = $comment->CreatedDate;
                $cmnt['UserGUID'] = $comment->UserGUID;
                $cmnt['Occupation'] = $comment->Occupation;
                $cmnt['About'] = $comment->About;
                
                $cmnt['Locality'] = array(
                    "Name" => $comment->Name, 
                    "HindiName"=>$comment->HindiName, 
                    "ShortName"=>$comment->ShortName,  
                    "LocalityID" => $comment->LocalityID);
                
                $cmnt['CanDelete'] = 0;
                $cmnt['ProfileLink'] = $comment->UserProfileUrl; //get_entity_url($comment->UserID, "User", 1); 
                
                $cmnt['NoOfLikes'] = $this->activity_model->get_like_count($comment->PostCommentID, "COMMENT", $blocked_users); //$comment->NoOfLikes;
                $cmnt['IsMediaExists'] = $comment->IsMediaExists;
                $cmnt['IsFileExists'] = $comment->IsFileExists;
                $cmnt['ModuleID'] = 3;
                
                $cmnt['Replies'] = array();                
                $cmnt['IsOwner'] = 0;
                
               

                $cmnt['Media'] = array();
                if ($cmnt['IsMediaExists']) {
                    $cmnt['Media'] = $this->activity_model->get_comment_media($comment->PostCommentID, FALSE, TRUE); //get all comment media
                    //reverse array for media details page issue
                    $cmnt['Media'] = (!empty($cmnt['Media'])) ? array_reverse($cmnt['Media']) : $cmnt['Media'];
                }
                $cmnt['Files'] = array();
                if ($cmnt['IsFileExists']) {
                    $cmnt['Files'] = $this->activity_model->get_activity_files($comment->PostCommentID);
                }

                if ($is_owner == 1 || $user_id == $cmnt['UserID']) {
                    $cmnt['CanDelete'] = 1;
                }
                if ($user_id == $cmnt['UserID']) {
                    $cmnt['IsOwner'] = 1;
                }
                
                if($cmnt['IsOwner']) {
                    $cmnt['IsLike'] = $this->activity_model->is_liked($comment->PostCommentID, 'Comment', $user_id, $comment_postas_module_id, $comment_postas_module_entity_id);
                    
                } else if($is_owner) {
                    $cmnt['IsLike'] = $this->activity_model->is_liked($comment->PostCommentID, 'Comment', $user_id, $postas_module_id, $postas_module_entity_id);
                    
                } else {
                    $cmnt['IsLike'] = $this->activity_model->is_liked($comment->PostCommentID, 'Comment', $user_id);
                    
                }
                $cmnt['Links'] = array();
                $parent_comment_id = $comment->ParentCommentID;
                if (!empty($parent_comment_id)) {
                    $parent_comment_owner_id = $this->activity_model->get_comment_owner_id($parent_comment_id);
                    if ($is_owner == 1 || $user_id == $parent_comment_owner_id) {
                        $cmnt['CanDelete'] = 1;
                    }
                } 
                $cmnt['Links'] = $this->activity_model->get_comment_links($comment->PostCommentID);
                

                if($is_super_admin) {
                   $cmnt['CanDelete'] = 1; 
                }

                unset($cmnt['UserID']);
                $comments[] = $cmnt;
            }
        }
        return array_reverse($comments);
    }

    /**
     * [point_allowed allow/disallow point for comment]
     * @param  [array]       $comment_details  [comment details]
     */
    function point_allowed($comment_details, $user_id) {
        $parent_comment_id = $comment_details['ParentCommentID']; 
        $comment_id = $comment_details['PostCommentID']; 
        $is_point_allowed = $comment_details['IsPointAllowed'];

        $this->db->set('IsPointAllowed', $is_point_allowed);
        $this->db->where('PostCommentID', $comment_id);
        $this->db->update(POSTCOMMENTS);  
        
        if($is_point_allowed == 1) {
            $point_data = array('EntityID' => $comment_id, 'EntityType' => 2, 'ParentID' => $parent_comment_id);
            initiate_worker_job('revert_point', $point_data,'','point');
        }
    }

    /**
     * Used to get is amazing comment
     */
    function is_amazing($comment_id) {
        $this->current_db->select('IsAmazing');
        $this->current_db->from(COMMENTDETAILS);
        $this->current_db->where('PostCommentID', $comment_id);
        $this->current_db->limit(1);
        $query = $this->current_db->get();
        $is_amazing = 0; //amazing comment
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $is_amazing = $row['IsAmazing'];
        }
        return $is_amazing;        
    }

    /**
     * used to update is amazing flag for an comment
     */
    function toggle_amazing($data) {
        $is_amazing = safe_array_key($data, 'IsAmazing', 0);
        $this->db->select('CommentDetailsID');
        $this->db->from(COMMENTDETAILS);
        $this->db->where('PostCommentID', $data['CommentID']);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $this->db->set('IsAmazing', $is_amazing);
            $this->db->where('CommentDetailsID', $row['CommentDetailsID']);
            $this->db->update(COMMENTDETAILS);
        } else {
            $insert_data = array();
            $insert_data['PostCommentID'] = $data['CommentID'];
            $insert_data['IsAmazing'] = $is_amazing;                    
            $this->db->insert(COMMENTDETAILS, $insert_data);
        }

        $is_point_allowed   = $data['IsPointAllowed'];
        $comment_owner_id   = $data['UserID'];
        $entity_id          = $data['EntityID'];
        if($is_amazing) {
            if(empty($is_point_allowed)) {
                $point_data = array('EntityID' =>  $data['CommentID'], 'EntityType' => 2, 'ActivityTypeID' => 51);
                
                $point_data['ActivityID'] = $entity_id;
                $point_data['OID'] = 0;
                $point_data['UserID'] = $comment_owner_id;
                $point_data['PT'] = 2;
                initiate_worker_job('add_point', $point_data,'','point');
            }
        } else {  
            $point_data = array('EntityID' => $data['CommentID'], 'EntityType' => 2, 'ActivityTypeID' => 51, 'ParentID' => -1);          
            initiate_worker_job('revert_point', $point_data,'','point');
        }
        
    }

    function amazing($data) {
        $user_id = $data['UserID'];
        $page_no = $data['PageNo'];
        $page_size = $data['PageSize'];
        $count_only = $data['CountOnly'];

        $this->db->select('PC.*,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,U.UserID, U.IsVIP, U.IsAssociation');
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
        $this->db->from(COMMENTDETAILS . ' CD');
        $this->db->join(POSTCOMMENTS . ' PC', 'PC.PostCommentID=CD.PostCommentID AND PC.StatusID IN (2,22)');
        $this->db->join(USERS . ' U', 'PC.UserID=U.UserID', 'left');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
        $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID', 'left');
        
        $this->db->where('CD.IsAmazing', 1);    
        
        $this->db->where("IF(PC.EntityType='ACTIVITY',PC.EntityID=(SELECT A.ActivityID FROM ".ACTIVITY." A WHERE A.ActivityID=PC.EntityID AND A.StatusID=2),true)", null, false);
        $this->db->where("IF(PC.EntityType='MEDIA',PC.EntityID=(SELECT M.MediaID FROM ".MEDIA." M WHERE M.MediaID=PC.EntityID AND M.StatusID=2),true)", null, false);

        $this->db->order_by('PC.PostCommentID', 'DESC');       
        if (!$count_only && !empty($page_size)) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }

        $query = $this->db->get();
       // echo $this->db->last_query();die;
        if($count_only) {
            return $query->num_rows();
        }

        $comments = array();
        if ($query->num_rows()) {
            $is_super_admin = $this->user_model->is_super_admin($user_id);
            $this->load->model(array(
                'activity/activity_model',
                'activity/activity_result_filter_model'
            ));
            foreach ($query->result() as $comment) {
                
                $cmnt['EntityType'] = $comment->EntityType;
                $IsOwner = 0;
                if($cmnt['EntityType'] == 'ACTIVITY') {
                    $entity = get_detail_by_id($comment->EntityID, 0, "ActivityGUID, UserID, ModuleID, ModuleEntityID", 2);
                    if ($entity['UserID'] == $user_id) {
                        $IsOwner = 1;
                    } else if ($entity['ModuleID'] == 3 && $entity['ModuleEntityID'] == $user_id) {
                        $IsOwner = 1;
                    }
                    $cmnt['EntityGUID'] = $entity['ActivityGUID'];
                } else if($cmnt['EntityType'] == 'MEDIA') {
                    $entity = get_detail_by_id($comment->EntityID, 21, 'MediaGUID, UserID', 2);
                    if ($entity['UserID'] == $user_id) {
                        $IsOwner = 1;
                    }
                    $cmnt['EntityGUID'] = $entity['MediaGUID'];
                }

                $cmnt['CommentGUID'] = $comment->PostCommentGUID;
                $cmnt['CreatedDate'] = $comment->CreatedDate;
                $edit_post_comment = $comment->PostComment;
                $cmnt['PostComment'] = $comment->PostComment;//nl2br($comment->PostComment,FALSE);
                $cmnt['EditPostComment'] = $this->activity_model->parse_tag_edit($edit_post_comment);
                $cmnt['PostComment'] = $this->activity_model->parse_tag($cmnt['PostComment']);
                $cmnt['PostComment'] = trim(str_replace('&nbsp;', ' ', $cmnt['PostComment']));

                /*parsed html*/
                if($this->IsApp == 1) {
                    $cmnt['PostComment'] = $this->activity_result_filter_model->get_description($cmnt['PostComment']);                    
                } else {
                    $cmnt['PostComment'] = html_entity_decode($cmnt['PostComment']);
                }
                /*parsed html*/

                $cmnt['IsMediaExists'] = $comment->IsMediaExists;
                $cmnt['Media'] = array();
                if ($cmnt['IsMediaExists']) {
                    $cmnt['Media'] = $this->activity_model->get_comment_media($comment->PostCommentID, FALSE, TRUE); //get all comment media
                    //reverse array for media details page issue
                    $cmnt['Media'] = (!empty($cmnt['Media'])) ? array_reverse($cmnt['Media']) : $cmnt['Media'];
                }
                
                $cmnt['Name'] = $comment->FirstName . ' ' . $comment->LastName;                
                $cmnt['UserID'] = $comment->UserID;
                $cmnt['UserGUID'] = $comment->UserGUID;
                $cmnt['Occupation'] = $comment->Occupation;
                $cmnt['IsVIP']          = $comment->IsVIP;
                $cmnt['IsAssociation']  = $comment->IsAssociation;
                $cmnt['ProfilePicture'] = $comment->ProfilePicture;
                $cmnt['Locality'] = array(
                    "Name" => $comment->Name, 
                    "HindiName"=>$comment->HindiName, 
                    "ShortName"=>$comment->ShortName,  
                    "LocalityID" => $comment->LocalityID);
                
                $cmnt['IsAdmin'] = $this->user_model->is_super_admin($comment->UserID, 1);
                $cmnt['CanDelete'] = 0;             
                $cmnt['IsOwner'] = 0;                
                
                if ($IsOwner == 1 || $user_id == $cmnt['UserID']) {
                    $cmnt['CanDelete'] = 1;
                }
                if ($user_id == $cmnt['UserID']) {
                    $cmnt['IsOwner'] = 1;
                }
                
                
                $cmnt['Links'] = array();
                $parent_comment_id = $comment->ParentCommentID;
                if (!empty($parent_comment_id)) {
                    $parent_comment_owner_id = $this->activity_model->get_comment_owner_id($parent_comment_id);
                    if ($IsOwner == 1 || $user_id == $parent_comment_owner_id) {
                        $cmnt['CanDelete'] = 1;
                    }
                } 
                $cmnt['Links'] = $this->activity_model->get_comment_links($comment->PostCommentID);
                
                if($is_super_admin) {
                   $cmnt['CanDelete'] = 1; 
                }

                unset($cmnt['UserID']);
                $comments[] = $cmnt;
            }
        }
        return $comments;

    }
}
