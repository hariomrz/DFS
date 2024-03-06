<?php
/**
 * This model is used to for restore entity
 * @package    Restore_model
 * @author     Vinfotech Team
 * @version    1.0
 * 
 *
 */
class Restore_model extends Common_Model {
    
    /**
     * Class constructor
     * load archive db database.
     * @return	void
     */
    function __construct() {
        parent::__construct();
        $this->db_archive	= $this->load->database('db_archive', TRUE);
        $this->can_delete_from_source_db = 1;
        $this->is_queue = 1;
    }

     /**
     * Class destructor
     * Closes the connection to archive db if present
     * @return	void
     */
    function __destruct() {
        if (isset($this->db_archive->conn_id)) {
            $this->db_archive->close();
        }
    }

    function check_transaction($db_obj) {
        $db_obj->trans_complete();
        $db_obj->trans_strict(FALSE);
        if ($db_obj->trans_status() === FALSE) {
            $db_obj->trans_rollback();
        } else {
            $db_obj->trans_commit();
        }
    }

    function sync_entity($entity_type, $entity_id) {
        if($entity_type == "ACTIVITY") {
            $this->activity($entity_id);
        }
        if($entity_type == "COMMENT") {
            $this->comment($entity_id);
        }

        if($entity_type == "MEDIA") {
            $this->entity_media($entity_id);
        }
    }

    function restore_entity($result) {
        ini_set('max_execution_time', 3000);
        $entity_type    = $result['EntityType'];
        if($entity_type == "ACTIVITY") {
            $activity_id =  $result['EntityID']; 

            if($result['IsPollExist'] == 1) {
                $this->poll($activity_id);
            }

            if($result['IsMediaExist'] == 1) {
                $this->media(3, $activity_id);
            }

            $this->activity_details($activity_id);
            $this->mentions($activity_id);
            $this->subscribe('ACTIVITY', $activity_id);
            $this->links('ACTIVITY', $activity_id);
            $this->tags('ACTIVITY', $activity_id);
            $this->tag_category('ACTIVITY', $activity_id);

            if($result['NoOfLikes'] > 0) {
                $this->likes('ACTIVITY', $activity_id);
            }

            if($result['NoOfComments'] > 0) {
                $this->entity_comment('ACTIVITY', $activity_id);
            }

            if($result['NoOfViews'] > 0) {
                $this->views('ACTIVITY', $activity_id);
            }

            if($result['IsCityNews'] == 1) {
                $this->city_news($activity_id);
            }
                        
            $this->favourite('ACTIVITY', $activity_id);
            $this->flag('ACTIVITY', $activity_id);
            $this->notification($activity_id, array(2, 3, 18, 19, 20, 21, 50, 54, 55, 63, 73, 82, 84, 85, 110, 127, 113, 114, 132, 140, 155, 158, 159, 160, 161, 162));
            $this->user_activity_log($activity_id);
            $this->daily_digest($activity_id);
            $this->story($activity_id);
            $this->activity_history($activity_id);
            $this->admin_communication($activity_id);
        }

        if($entity_type == "COMMENT") {
            $comment_id =  $result['EntityID']; 
            
            if($result['IsMediaExists'] > 0) {
                $this->media(6, $comment_id);
            }
            $this->mentions(0, $comment_id);
            $this->subscribe('COMMENT', $comment_id);
            $this->links('COMMENT', $comment_id);     

            if($result['NoOfLikes'] > 0) {
                $this->likes('COMMENT', $comment_id);
            }
            $this->comment_history($comment_id);
            $this->user_activity_log($comment_id, 20);                       
            $this->notification($comment_id, array(121, 120));
        }

        if($entity_type == "MEDIA") {
            $media_id =  $result['MediaID']; 
            if($result['NoOfLikes'] > 0) {
                $this->likes('MEDIA', $media_id);
            }
            if($result['NoOfComments'] > 0) {
                $this->entity_comment('MEDIA', $media_id);
            }
            $this->subscribe('MEDIA', $media_id);
            $this->notification($media_id, array(49, 51));
        }
    }

    function activity($activity_id) {
        try {
            $this->db->query('SET foreign_key_checks = 0');
            $this->db_archive->query('SET foreign_key_checks = 0');

            $this->db_archive->select('*');
            $this->db_archive->from(ACTIVITY);
            $this->db_archive->where("ActivityID", $activity_id);
            $this->db_archive->limit(1);
            $query = $this->db_archive->get();
            //echo $this->db->last_query();die;
      
            $results = $query->result_array();
            
            if($results) {
                $this->db->insert_on_duplicate_update_batch(ACTIVITY, $results);

                $this->db_archive->select('*');
                $this->db_archive->from(ACTIVITYWARD);
                $this->db_archive->where('ActivityID', $activity_id);
                $this->db_archive->order_by('ActivityWardID', 'ASC');
                $query_ward = $this->db_archive->get();
                //print_r($query->result_array());die;
                $ward_results = $query_ward->result_array();
                if($ward_results) {
                    $this->db->insert_on_duplicate_update_batch(ACTIVITYWARD, $ward_results);
                }
                $query_ward->free_result();

                if($this->can_delete_from_source_db) {
                    $this->db_archive->where('ActivityID', $activity_id);
                    $this->db_archive->delete(ACTIVITY);

                    $this->db_archive->where('ActivityID', $activity_id);
                    $this->db_archive->delete(ACTIVITYWARD);
                }  
                $result = $results[0];
                $result['EntityType'] = "ACTIVITY";
                $result['EntityID'] = $activity_id;
                if($this->is_queue == 1) {
                    initiate_worker_job('restore_entity', $result,'','restore_entity');
                } else {
                    $this->restore_entity($result);
                }
            }
            $query->free_result();  
            $this->db_archive->query('SET foreign_key_checks = 1');        
            $this->db->query('SET foreign_key_checks = 1');
           // print_r($activity_ids);die;
        } catch (Exception $e) {
            log_message('error', 'Restore activity Issue: '.$activity_id);
        }      
    }

    function comment($comment_id) {
        try {
            $this->db->query('SET foreign_key_checks = 0');
            $this->db_archive->query('SET foreign_key_checks = 0');

            $this->db_archive->select('*');
            $this->db_archive->from(POSTCOMMENTS);
            $this->db_archive->where("PostCommentID", $comment_id);
            $this->db_archive->limit(1);
            $query = $this->db_archive->get();
            //echo $this->db->last_query();die;
      
            $results = $query->result_array();
            if($results) {
                $result = $results[0];
                $entity_type = ''; 
                $entity_id = 0;
                $this->db->insert_on_duplicate_update_batch(POSTCOMMENTS, $results);
                                
                $entity_type = $result['EntityType']; 
                $entity_id = $result['EntityID'];                                   

                if($this->can_delete_from_source_db) {
                    $this->db_archive->where('PostCommentID', $comment_id);
                    $this->db_archive->delete(POSTCOMMENTS);
                } 
                
                $result['EntityType'] = "COMMENT";
                $result['EntityID'] = $comment_id;
                if($this->is_queue == 1) {
                    initiate_worker_job('restore_entity', $result,'','restore_entity');
                } else {
                    $this->restore_entity($result);
                }
                

                if($entity_type == 'ACTIVITY') {
                    $this->activity($entity_id);
                }

                if($entity_type == 'MEDIA') {
                    $this->entity_media($entity_id);
                }
            }
            $query->free_result();  
            $this->db_archive->query('SET foreign_key_checks = 1');        
            $this->db->query('SET foreign_key_checks = 1');
           // print_r($activity_ids);die;
        } catch (Exception $e) {
            log_message('error', 'Restore comment Issue: '.$comment_id);
        }      
    }

    function entity_comment($entity_type, $entity_id) {
        try {
            $this->db->query('SET foreign_key_checks = 0');
            $this->db_archive->query('SET foreign_key_checks = 0');

            $this->db_archive->select('*');
            $this->db_archive->from(POSTCOMMENTS);
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->order_by('PostCommentID', 'ASC');
            $query = $this->db_archive->get();      
            $results = $query->result_array();
            foreach ($results as $result) {                
                if($result['NoOfLikes'] > 0) {
                    $this->likes('COMMENT', $result['PostCommentID']);
                }
                if($result['IsMediaExists'] > 0) {
                    $this->media(6, $result['PostCommentID']);
                }
                $this->mentions(0, $result['PostCommentID']);
                $this->comment_history($result['PostCommentID']);
                $this->user_activity_log($result['PostCommentID'], 20);
                $this->subscribe('COMMENT', $result['PostCommentID']);
                $this->links('COMMENT', $result['PostCommentID']);                
                $this->notification($result['PostCommentID'], array(121, 120));
            }
            if($results) {
                $this->db->insert_on_duplicate_update_batch(POSTCOMMENTS, $results);
            }  

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where('EntityType', $entity_type);
                $this->db_archive->where('EntityID', $entity_id);
                $this->db_archive->delete(POSTCOMMENTS);
            }

            $query->free_result();  
            $this->db_archive->query('SET foreign_key_checks = 1');        
            $this->db->query('SET foreign_key_checks = 1');
           // print_r($activity_ids);die;
        } catch (Exception $e) {
            log_message('error', 'Restore comment Issue: '.$comment_id);
        }      
    }

    /**
     * Used to restore views
     */
    protected function views($entity_type, $entity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(ENTITYVIEW);
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->order_by('EntityViewID', 'ASC');            
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(ENTITYVIEW, $results);
            }

            foreach ($results as $result) {                
                $this->db_archive->select('*');
                $this->db_archive->from(ENTITYVIEWLOG);
                $this->db_archive->where('EntityViewID', $result['EntityViewID']);
                $this->db_archive->order_by('EntityViewLogID', 'ASC');
                $view_log_query = $this->db_archive->get();
                $view_log_results = $view_log_query->result_array();
                if($view_log_results) {
                    $this->db->insert_on_duplicate_update_batch(ENTITYVIEWLOG, $view_log_results);
                }                
                $view_log_query->free_result();

                if($this->can_delete_from_source_db) {
                    $this->db_archive->where('EntityViewID', $result['EntityViewID']);
                    $this->db_archive->delete(ENTITYVIEWLOG);
                }
            }
            
            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where('EntityType', $entity_type);
                $this->db_archive->where('EntityID', $entity_id);
                $this->db_archive->delete(ENTITYVIEW);
            }
            
        } catch (Exception $e) {
            throw new Exception("Issue with archive views for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to restore likes
     */
    public function likes($entity_type, $entity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(POSTLIKE);
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->order_by('PostLikeID', 'ASC');
            $query = $this->db_archive->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(POSTLIKE, $results);

                /* Delete existing records from production DB*/
                if($this->can_delete_from_source_db) { 
                    $this->db_archive->where('EntityType', $entity_type);
                    $this->db_archive->where('EntityID', $entity_id);
                    $this->db_archive->delete(POSTLIKE);
                }
            }
            $query->free_result();                        
        } catch (Exception $e) {
            throw new Exception("Issue with restore likes for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to restore media
     */
    protected function media($media_section_id, $media_section_reference_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(MEDIA);
            $this->db_archive->where('MediaSectionID', $media_section_id);
            $this->db_archive->where('MediaSectionReferenceID', $media_section_reference_id);
            $this->db_archive->order_by('MediaID', 'ASC');
            $query = $this->db_archive->get();
            $results = $query->result_array();
            foreach ($results as $result) {                
                $result['EntityType'] = "MEDIA";
                $result['EntityID'] = $result['MediaID'];
                if($this->is_queue == 1) {
                    initiate_worker_job('restore_entity', $result,'','restore_entity');
                } else {
                    $this->restore_entity($result);
                }                
            }
            if($results) {
                $this->db->insert_on_duplicate_update_batch(MEDIA, $results); 

                /* Delete existing records from Archive DB*/
                if($this->can_delete_from_source_db) {
                    $this->db_archive->where('MediaSectionID', $media_section_id);
                    $this->db_archive->where('MediaSectionReferenceID', $media_section_reference_id);
                    $this->db_archive->delete(MEDIA);
                }
            }           
            $query->free_result();

            
        } catch (Exception $e) {
            throw new Exception("Issue with restore media for media section ".$media_section_id." and its id: ".$media_section_reference_id.' '.$e->getMessage());
        }
    } 

    /**
     * Used to restore media
     */
    protected function entity_media($media_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(MEDIA);
            $this->db_archive->where('MediaID', $media_id);
            $this->db_archive->limit('1');
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $result = $results[0];
            
                $this->db->insert_on_duplicate_update_batch(MEDIA, $results); 

                 /* Delete existing records from production DB*/
                if($this->can_delete_from_source_db) {
                    $this->db_archive->where('MediaID', $media_id);
                    $this->db_archive->delete(MEDIA);
                }

                $result['EntityType'] = "MEDIA";
                $result['EntityID'] = $media_id;
                if($this->is_queue == 1) {
                    initiate_worker_job('restore_entity', $result,'','restore_entity');
                } else {
                    $this->restore_entity($result);
                }
                
            }           
            $query->free_result();
        } catch (Exception $e) {
            throw new Exception("Issue with restore media for media section ".$media_section_id." and its id: ".$media_section_reference_id.' '.$e->getMessage());
        }
    } 

    /**
     * Used to restore mentions
     */
    protected function mentions($activity_id, $comment_id=0) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(MENTION);
            if(!empty($activity_id)) {
                $this->db_archive->where('ActivityID', $activity_id);
            }
            $this->db_archive->where('PostCommentID', $comment_id);
            $this->db_archive->order_by('MentionID', 'ASC');           
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(MENTION, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) { 
                if(!empty($activity_id)) {
                    $this->db_archive->where('ActivityID', $activity_id);
                }
                $this->db_archive->where('PostCommentID', $comment_id);
                $this->db_archive->delete(MENTION);
            }
            
        } catch (Exception $e) {
            throw new Exception("Issue with restore mention for activity ".$activity_id.' and comment : '.$comment_id);
        }
    }

    /**
     * Used to restore activity history
     */
    protected function activity_history($activity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(ACTIVITYHISTORY);
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->order_by('HistoryID', 'ASC');           
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(ACTIVITYHISTORY, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db_archive->where('ActivityID', $activity_id);            
                $this->db_archive->delete(ACTIVITYHISTORY);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore history for activity ".$activity_id);
        }
    }

    /**
     * Used to restore activity details
     */
    protected function activity_details($activity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(ACTIVITYDETAILS);
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->order_by('ActivityDetailsID', 'ASC');           
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(ACTIVITYDETAILS, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db_archive->where('ActivityID', $activity_id);            
                $this->db_archive->delete(ACTIVITYDETAILS);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore details for activity ".$activity_id);
        }
    }

    /**
     * Used to restore comment history
     */
    protected function comment_history($comment_id) {
        try {            
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(COMMENTHISTORY);
            $this->db_archive->where('CommentID', $comment_id);
            $this->db_archive->order_by('HistoryID', 'ASC');           
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(COMMENTHISTORY, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {            
                $this->db_archive->where('CommentID', $comment_id);            
                $this->db_archive->delete(COMMENTHISTORY);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore history for comment ".$comment_id);
        }
    }

    /**
     * Used to restore poll
     */
    public function poll($activity_id) {
        try {

            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(POLL);
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->limit(1);
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(POLL, $results);  
            }  
            foreach ($results as $result) {  
                $poll_id = $result['PollID'];

                $this->db_archive->select('*');
                $this->db_archive->from(POLLOPTION);
                $this->db_archive->where('PollID', $poll_id);
                $poll_option_query = $this->db_archive->get();
                $poll_option_results = $poll_option_query->result_array();
                if($poll_option_results) {
                    $this->db->insert_on_duplicate_update_batch(POLLOPTION, $poll_option_results);   
                }
                $poll_option_query->free_result();

                $this->db_archive->select('*');
                $this->db_archive->from(POLLOPTIONVOTES);
                $this->db_archive->where('PollID', $poll_id);
                $poll_option_vote_query = $this->db_archive->get();
                $poll_option_vote_results = $poll_option_vote_query->result_array();

                if($poll_option_vote_results) {
                    $this->db->insert_on_duplicate_update_batch(POLLOPTIONVOTES, $poll_option_vote_results);
                }
                $poll_option_vote_query->free_result();

                if($this->can_delete_from_source_db) {
                    $this->db_archive->where('PollID', $poll_id);
                    $this->db_archive->delete(POLLOPTIONVOTES);

                    $this->db_archive->where('PollID', $poll_id);
                    $this->db_archive->delete(POLLOPTION);
                }

            }
                                
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where('ActivityID', $activity_id);
                $this->db_archive->delete(POLL);
            }
        } catch (Exception $e) {
            throw new Exception('Issue with restore poll for activity and its id: '.$activity_id.' '.$e->getMessage());
        }
    }

    /**
     * Used to archive post tags
     */
    protected function tags($entity_type, $entity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(ENTITYTAGS);
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->order_by('EntityTagID', 'ASC');
            $query = $this->db_archive->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(ENTITYTAGS, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where('EntityType', $entity_type);
                $this->db_archive->where('EntityID', $entity_id);
                $this->db_archive->delete(ENTITYTAGS);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore tags for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to archive post tag category
     */
    protected function tag_category($entity_type, $entity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(ENTITYTAGSCATEGORY);
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->order_by('EntityTagCategoryID', 'ASC');
            $query = $this->db_archive->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                foreach ($results as $key => $value) {
                    if($value['TagID'] == 20) {
                        $tag_category_id = $value['TagCategoryID'];
                        $this->db->select('Name, CreatedDate');
                        $this->db->from(TAGCATEGORY);
                        $this->db->where('TagCategoryID', $tag_category_id);
                        $this->db->limit(1);
                        $query = $this->db->get();
                        if ($query->num_rows()) {
                            $tag_category_data = $query->row_array();
                            $tag_category_name = trim($tag_category_data['Name']);
                            $entity_tag_category_created_date = $tag_category_data['CreatedDate'];

                            //check tag category name exit in tag table or not
                            $this->db->select('TagID');
                            $this->db->from(TAGS);
                            $this->db->where('LOWER(Name)', strtolower($tag_category_name),NULL,FALSE);
                            $this->db->where('TagType', 1);
                            $query = $this->db->get();
                            if ($query->num_rows()) {
                                $tag_data = $query->row_array();
                                $tag_id = $tag_data['TagID'];
                            } else {
                                $tag_data = array('Name' => $tag_category_name, 'TagType' => 1, 'CreatedBy' => 1, 'ModifiedBy' => 1, 'CreatedDate' => $entity_tag_category_created_date, 'ModifiedDate' => $entity_tag_category_created_date);
                                $this->db->insert(TAGS, $tag_data);
                                $tag_id = $this->db->insert_id();
                            }

                            //check tag_id assign to activity_id or not
                            $this->db->select('TagID');
                            $this->db->from(ENTITYTAGS);
                            $this->db->where('EntityType', $entity_type);
                            $this->db->where('EntityID', $entity_id, FALSE);
                            $this->db->where('TagID', $tag_id, FALSE);
                            $query = $this->db->get();
                            if ($query->num_rows()) {
                                $tag_data = $query->row_array();
                                $tag_id = $tag_data['TagID'];
                            } else {
                                $tag_data = array('EntityType' => $entity_type, 'EntityID' => $entity_id, 'TagID' => $tag_id, 'UserID' => 1, 'CreatedDate' => $entity_tag_category_created_date, 'StatusID' => '2', 'AddedBy' => 1);
                                $this->db->insert(ENTITYTAGS, $tag_data);
                            }
                        }
                        unset($results[$key]);
                    }
                }
                if($results) {
                    $this->db->insert_on_duplicate_update_batch(ENTITYTAGSCATEGORY, $results);
                }
                
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where('EntityType', $entity_type);
                $this->db_archive->where('EntityID', $entity_id);
                $this->db_archive->delete(ENTITYTAGSCATEGORY);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore tag category for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to restore user activity log
    */
    protected function user_activity_log($entity_id, $activity_type_id=0) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(USERSACTIVITYLOG);
            if(empty($activity_type_id)) {
                $this->db_archive->where('ActivityID', $entity_id);
            } else {
                $this->db_archive->where('ActivityTypeID', $activity_type_id);
                $this->db_archive->where('EntityID', $entity_id);
            }      
            $this->db_archive->order_by('ID', 'ASC');
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(USERSACTIVITYLOG, $results);
            }
            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                if(empty($activity_type_id)) {
                    $this->db_archive->where('ActivityID', $entity_id);
                } else {
                    $this->db_archive->where('ActivityTypeID', $activity_type_id);
                    $this->db_archive->where('EntityID', $entity_id);
                }   
                $this->db_archive->delete(USERSACTIVITYLOG);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore user activity log entity id: ".$entity_id.' and its activity type id: '.$activity_type_id);
        }
    }

    /**
     * Used to restore subscribe
     */
    protected function subscribe($entity_type, $entity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(SUBSCRIBE);
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->order_by('SubscribeID', 'ASC');
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(SUBSCRIBE, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where('EntityType', $entity_type);
                $this->db_archive->where('EntityID', $entity_id);
                $this->db_archive->delete(SUBSCRIBE);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore subscribe for ".$entity_type.' and its id: '.$entity_id);
        }
    }

     /**
     * Used to restore links
     */
    protected function links($entity_type, $entity_id) {
        try {
            $table_name = ($entity_type == 'ACTIVITY') ? ACTIVITYLINKS : COMMENTLINKS;
            $column_name = ($entity_type == 'ACTIVITY') ? 'ActivityID' : 'CommentID';
            
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from($table_name);
            $this->db_archive->where($column_name, $entity_id);
            $query = $this->db_archive->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch($table_name, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where($column_name, $entity_id);
                $this->db_archive->delete($table_name);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore links for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to archive notification
     */
    protected function notification($refrence_id, $notification_type_ids) {
        try {
            if(empty($notification_type_ids)) {
                return;
            }

            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(NOTIFICATIONS);
            $this->db_archive->where_in('NotificationTypeID', $notification_type_ids);
            $this->db_archive->where('RefrenceID', $refrence_id);
            $this->db_archive->order_by('NotificationID', 'ASC');            
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(NOTIFICATIONS, $results);
            }
            foreach ($results as $result) {                
                $this->db_archive->select('*');
                $this->db_archive->from(NOTIFICATIONPARAMS);
                $this->db_archive->where('NotificationID', $result['NotificationID']);
                $this->db_archive->order_by('NotificationParamID', 'ASC');
                $notification_param_query = $this->db_archive->get();
                $notification_param_results = $notification_param_query->result_array();
                if($notification_param_results) {
                    $this->db->insert_on_duplicate_update_batch(NOTIFICATIONPARAMS, $notification_param_results);
                }                
                $notification_param_query->free_result();

                if($this->can_delete_from_source_db) {
                    $this->db_archive->where('NotificationID', $result['NotificationID']);
                    $this->db_archive->delete(NOTIFICATIONPARAMS);
                }
            }
            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where_in('NotificationTypeID', $notification_type_ids);
                $this->db_archive->where('RefrenceID', $refrence_id);
                $this->db_archive->delete(NOTIFICATIONS);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore notification for refrence_id ".$refrence_id.' and its notification type: '.json_encode($notification_type_ids));
        }
    }

    /**
     * Used to restore admin communication
     */
    protected function admin_communication($activity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(ADMINCOMMUNICATION);
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->order_by('AdminCommunicationID', 'ASC');            
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(ADMINCOMMUNICATION, $results);
            }
            foreach ($results as $result) {                
                $this->db_archive->select('*');
                $this->db_archive->from(ADMINCOMMUNICATIONHISTORY);
                $this->db_archive->where('AdminCommunicationID', $result['AdminCommunicationID']);
                $this->db_archive->order_by('AdminCommunicationHistoryID', 'ASC');
                $communication_history_query = $this->db_archive->get();
                $communication_history_results = $communication_history_query->result_array();
                if($communication_history_results) {
                    $this->db->insert_on_duplicate_update_batch(ADMINCOMMUNICATIONHISTORY, $communication_history_results);
                }                
                $communication_history_query->free_result();

                if($this->can_delete_from_source_db) {
                    $this->db_archive->where('AdminCommunicationID', $result['AdminCommunicationID']);
                    $this->db_archive->delete(ADMINCOMMUNICATIONHISTORY);
                }
            }
            
            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where('ActivityID', $activity_id);
                $this->db_archive->delete(ADMINCOMMUNICATION);
            }            
        } catch (Exception $e) {
            throw new Exception('Issue with restore admin communication for activity and its id: '.$activity_id);
        }
    }

    /**
     * Used to archive Favourite activity
     */
    protected function favourite($entity_type, $entity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(FAVOURITE);
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->order_by('FavouriteID', 'ASC');
            $query = $this->db_archive->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(FAVOURITE, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where('EntityType', $entity_type);
                $this->db_archive->where('EntityID', $entity_id);
                $this->db_archive->delete(FAVOURITE);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore favourite for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to archive flag activity
     */
    protected function flag($entity_type, $entity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(FLAG);
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->order_by('FlagID', 'ASC');
            $query = $this->db_archive->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(FLAG, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db_archive->where('EntityType', $entity_type);
                $this->db_archive->where('EntityID', $entity_id);
                $this->db_archive->delete(FLAG);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore flag for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to restore city news
     */
    protected function city_news($activity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(CITYNEWS);
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->limit(1);           
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(CITYNEWS, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db_archive->where('ActivityID', $activity_id);            
                $this->db_archive->delete(CITYNEWS);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore city news for activity ".$activity_id);
        }
    }

    /**
     * Used to restore daily digest
     */
    protected function daily_digest($activity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(DIALYDIGEST);
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->order_by('DailyDigestID', 'ASC');           
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(DIALYDIGEST, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db_archive->where('ActivityID', $activity_id);            
                $this->db_archive->delete(DIALYDIGEST);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore daily digest for activity ".$activity_id);
        }
    }

     /**
     * Used to archive story
     */
    protected function story($activity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(STORY);
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->order_by('StoryID', 'ASC');           
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(STORY, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db_archive->where('ActivityID', $activity_id);            
                $this->db_archive->delete(STORY);
            }

            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(STORYWARD);
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->order_by('StoryWardID', 'ASC');           
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(STORYWARD, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db_archive->where('ActivityID', $activity_id);            
                $this->db_archive->delete(STORYWARD);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with restore story for activity ".$activity_id);
        }
    }

    /**
     * Used to restore user orientation
     */
    protected function user_orientation($activity_id) {
        try {
            /** Select records from production DB and insert into archival DB */
            $this->db_archive->select('*');
            $this->db_archive->from(USERORIENTATION);
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->order_by('UserOrientationID', 'ASC');           
            $query = $this->db_archive->get();
            $results = $query->result_array();
            if($results) {
                $this->db->insert_on_duplicate_update_batch(USERORIENTATION, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db_archive->where('ActivityID', $activity_id);            
                $this->db_archive->delete(USERORIENTATION);
            }            
        } catch (Exception $e) {
            throw new Exception("Issue with retore user orientation for activity ".$activity_id);
        }
    }

    
}
?>