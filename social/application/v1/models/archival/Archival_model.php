<?php
/**
 * This model is used to for Archival process
 * @package    Archival_model
 * @author     Vinfotech Team
 * @version    1.0
 * 
 *
 */
class Archival_model extends Common_Model {
    
    /**
     * Class constructor
     * load archive db database.
     * @return	void
     */
    function __construct() {
        parent::__construct();
        $this->db_archive	= $this->load->database('db_archive', TRUE);
        $this->can_delete_from_source_db = 1;
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

    function run($page_no, $page_size=30) {
        
        try {
            ini_set('max_execution_time', 3000);
            $this->db->query('SET foreign_key_checks = 0');
            $this->db_archive->query('SET foreign_key_checks = 0');
            $archive_date = get_current_date('%Y-%m-%d', 180);
            $this->db->trans_start();
            $this->db->select('*');
            $this->db->from(ACTIVITY);
            $this->db->where("ModifiedDate <= '" . $archive_date . " 00:00:00'", NULL, FALSE);
            $this->db->order_by('ModifiedDate', 'ASC');
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            $query = $this->db->get();
            //echo $this->db->last_query();die;
      
            $results = $query->result_array();
            $activity_ids = array();
            foreach ($results as $result) {     
                $activity_id =  $result['ActivityID'];       
                if($result['NoOfViews'] > 0) {
                    $this->views('ACTIVITY', $activity_id);
                }

                if($result['NoOfLikes'] > 0) {
                    $this->likes('ACTIVITY', $activity_id);
                }

                if($result['NoOfComments'] > 0) {
                    $this->comments('ACTIVITY', $activity_id);
                }

                if($result['IsMediaExist'] == 1) {
                    $this->media(3, $activity_id);
                }

                if($result['IsPollExist'] == 1) {
                    $this->poll($activity_id);
                }

                if($result['IsCityNews'] == 1) {
                    $this->city_news($activity_id);
                }
                $this->user_activity_log($activity_id);
                $this->daily_digest($activity_id);
                $this->story($activity_id);
                $this->tags('ACTIVITY', $activity_id);
                $this->tag_category('ACTIVITY', $activity_id);
                $this->subscribe('ACTIVITY', $activity_id);
                $this->links('ACTIVITY', $activity_id);
                $this->favourite('ACTIVITY', $activity_id);
                $this->flag('ACTIVITY', $activity_id);
                $this->mentions($activity_id);
                $this->activity_history($activity_id);
                $this->admin_communication($activity_id);
                $this->notification($activity_id, array(2, 3, 18, 19, 20, 21, 50, 54, 55, 63, 73, 82, 84, 85, 110, 127, 113, 114, 132, 140, 155, 158, 159, 160, 161, 162));

                $this->activity_ward($activity_id);
                
                 
                $activity_ids[] =  $activity_id;                            
            }

            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(ACTIVITY, $results);

                if($this->can_delete_from_source_db && !empty($activity_ids)) {
                    $this->db->where_in('ActivityID', $activity_ids);
                    $this->db->delete(PINTOTOP);

                    $this->db->where_in('ActivityID', $activity_ids);
                    $this->db->delete(ACTIVITY);
                }   
            }
            $query->free_result();
            $this->check_transaction($this->db);   
            $this->db_archive->query('SET foreign_key_checks = 1');        
            $this->db->query('SET foreign_key_checks = 1');
           // print_r($activity_ids);die;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Archival Program Issue: '.$e->getMessage());
        }             
    }

    /**
     * Used to archive activity ward
     */
    protected function activity_ward($activity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->delete(ACTIVITYWARD);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(ACTIVITYWARD);
            $this->db->where('ActivityID', $activity_id);
            $this->db->order_by('ActivityWardID', 'ASC');
            $query = $this->db->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(ACTIVITYWARD, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) { 
                $this->db->where('ActivityID', $activity_id);
                $this->db->delete(ACTIVITYWARD);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive likes for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to archive views
     */
    protected function views($entity_type, $entity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->select('EntityViewID');
            $this->db_archive->from(ENTITYVIEW);
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->order_by('EntityViewID', 'DESC');
            $this->db_archive->limit(1);
            $query = $this->db_archive->get();
            $row = $query->row_array();

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(ENTITYVIEW);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->order_by('EntityViewID', 'ASC');
            if(isset($row['EntityViewID']) && !empty($row['EntityViewID'])) {
                $this->db->where('EntityViewID >', $row['EntityViewID']);
            }
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(ENTITYVIEW, $results);
            }
            foreach ($results as $result) {                
                $this->db->select('*');
                $this->db->from(ENTITYVIEWLOG);
                $this->db->where('EntityViewID', $result['EntityViewID']);
                $this->db->order_by('EntityViewLogID', 'ASC');
                $view_log_query = $this->db->get();
                $view_log_results = $view_log_query->result_array();
                if($view_log_results) {
                    $this->db_archive->insert_on_duplicate_update_batch(ENTITYVIEWLOG, $view_log_results);
                }                
                $view_log_query->free_result();

                if($this->can_delete_from_source_db) {
                    $this->db->where('EntityViewID', $result['EntityViewID']);
                    $this->db->delete(ENTITYVIEWLOG);
                }
            }
            
            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where('EntityType', $entity_type);
                $this->db->where('EntityID', $entity_id);
                $this->db->delete(ENTITYVIEW);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive views for ".$entity_type.' and its id: '.$entity_id);
        }
    }
    
    /**
     * Used to archive likes
     */
    protected function likes($entity_type, $entity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->delete(POSTLIKE);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(POSTLIKE);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->order_by('PostLikeID', 'ASC');
            $query = $this->db->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(POSTLIKE, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) { 
                $this->db->where('EntityType', $entity_type);
                $this->db->where('EntityID', $entity_id);
                $this->db->delete(POSTLIKE);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive likes for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to archive comments
     */
    protected function comments($entity_type, $entity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->delete(POSTCOMMENTS);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(POSTCOMMENTS);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->order_by('PostCommentID', 'ASC');
            $query = $this->db->get();
            $results = $query->result_array();
            foreach ($results as $result) {                
                if($result['NoOfLikes'] > 0) {
                    $this->likes('COMMENT', $result['PostCommentID']);
                }
                if($result['IsMediaExists'] > 0) {
                    $this->media(6, $result['PostCommentID']);
                }
                $this->user_activity_log($result['PostCommentID'], 20);
                $this->subscribe('COMMENT', $result['PostCommentID']);
                $this->links('COMMENT', $result['PostCommentID']);
                $this->mentions(0, $result['PostCommentID']);
                $this->comment_history($result['PostCommentID']);
                $this->notification($result['PostCommentID'], array(121, 120));
            }
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(POSTCOMMENTS, $results);
            }           
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where('EntityType', $entity_type);
                $this->db->where('EntityID', $entity_id);
                $this->db->delete(POSTCOMMENTS);
            }

            $this->check_transaction($this->db_archive);

        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive comment for ".$entity_type.' and its id: '.$entity_id.' '.$e->getMessage());
        }
    }
    /**
     * Used to archive media
     */
    protected function media($media_section_id, $media_section_reference_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->where('MediaSectionID', $media_section_id);
            $this->db_archive->where('MediaSectionReferenceID', $media_section_reference_id);
            $this->db_archive->delete(MEDIA);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(MEDIA);
            $this->db->where('MediaSectionID', $media_section_id);
            $this->db->where('MediaSectionReferenceID', $media_section_reference_id);
            $this->db->order_by('MediaID', 'ASC');
            $query = $this->db->get();
            $results = $query->result_array();
            foreach ($results as $result) {                
                if($result['NoOfLikes'] > 0) {
                    $this->likes('MEDIA', $result['MediaID']);
                }
                if($result['NoOfComments'] > 0) {
                    $this->comments('MEDIA', $result['MediaID']);
                }
                $this->subscribe('MEDIA', $result['MediaID']);
                $this->notification($result['MediaID'], array(49, 51));
            }
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(MEDIA, $results); 
            }           
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where('MediaSectionID', $media_section_id);
                $this->db->where('MediaSectionReferenceID', $media_section_reference_id);
                $this->db->delete(MEDIA);
            }

            $this->check_transaction($this->db_archive);
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive media for media section ".$media_section_id." and its id: ".$media_section_reference_id.' '.$e->getMessage());
        }
    }    

    /**
     * Used to archive poll
     */
    protected function poll($activity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->delete(POLL);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(POLL);
            $this->db->where('ActivityID', $activity_id);
            $this->db->limit(1);
            $query = $this->db->get();
            $results = $query->result_array();
            foreach ($results as $result) {  
                $poll_id = $result['PollID'];

                $this->db_archive->where('PollID', $poll_id);
                $this->db_archive->delete(POLLOPTION);

                $this->db->select('*');
                $this->db->from(POLLOPTION);
                $this->db->where('PollID', $poll_id);
                $poll_option_query = $this->db->get();
                $poll_option_results = $poll_option_query->result_array();
                if($poll_option_results) {
                    $this->db_archive->insert_on_duplicate_update_batch(POLLOPTION, $poll_option_results);   
                }
                $poll_option_query->free_result();

                $this->db->select('*');
                $this->db->from(POLLOPTIONVOTES);
                $this->db->where('PollID', $poll_id);
                $poll_option_vote_query = $this->db->get();
                $poll_option_vote_results = $poll_option_vote_query->result_array();

                if($poll_option_vote_results) {
                    $this->db_archive->insert_on_duplicate_update_batch(POLLOPTIONVOTES, $poll_option_vote_results);
                }
                $poll_option_vote_query->free_result();

                if($this->can_delete_from_source_db) {
                    $this->db->where('PollID', $poll_id);
                    $this->db->delete(POLLOPTIONVOTES);

                    $this->db->where('PollID', $poll_id);
                    $this->db->delete(POLLOPTION);
                }

            }
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(POLL, $results);  
            }                      
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where('ActivityID', $activity_id);
                $this->db->delete(POLL);
            }

            $this->check_transaction($this->db_archive);

        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception('Issue with archive poll for activity and its id: '.$activity_id.' '.$e->getMessage());
        }
    }

    /**
     * Used to archive post tags
     */
    protected function tags($entity_type, $entity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->delete(ENTITYTAGS);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(ENTITYTAGS);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->order_by('EntityTagID', 'ASC');
            $query = $this->db->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(ENTITYTAGS, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where('EntityType', $entity_type);
                $this->db->where('EntityID', $entity_id);
                $this->db->delete(ENTITYTAGS);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive tags for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to archive post tag category
     */
    protected function tag_category($entity_type, $entity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->delete(ENTITYTAGSCATEGORY);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(ENTITYTAGSCATEGORY);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->order_by('EntityTagCategoryID', 'ASC');
            $query = $this->db->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(ENTITYTAGSCATEGORY, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where('EntityType', $entity_type);
                $this->db->where('EntityID', $entity_id);
                $this->db->delete(ENTITYTAGSCATEGORY);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive tags category for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to archive user activity log
    */
    protected function user_activity_log($entity_id, $activity_type_id=0) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->select('ID');
            $this->db_archive->from(USERSACTIVITYLOG);
            if(empty($activity_type_id)) {
                $this->db_archive->where('ActivityID', $entity_id);
            } else {
                $this->db_archive->where('ActivityTypeID', $activity_type_id);
                $this->db_archive->where('EntityID', $entity_id);
            }            
            $this->db_archive->order_by('ID', 'DESC');
            $this->db_archive->limit(1);
            $query = $this->db_archive->get();
            $row = $query->row_array();

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(USERSACTIVITYLOG);
            if(empty($activity_type_id)) {
                $this->db->where('ActivityID', $entity_id);
            } else {
                $this->db->where('ActivityTypeID', $activity_type_id);
                $this->db->where('EntityID', $entity_id);
            }      
            $this->db->order_by('ID', 'ASC');
            if(isset($row['ID']) && !empty($row['ID'])) {
                $this->db->where('ID >', $row['ID']);
            }
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(USERSACTIVITYLOG, $results);
            }
            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                if(empty($activity_type_id)) {
                    $this->db->where('ActivityID', $entity_id);
                } else {
                    $this->db->where('ActivityTypeID', $activity_type_id);
                    $this->db->where('EntityID', $entity_id);
                }   
                $this->db->delete(USERSACTIVITYLOG);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive user activity log entity id: ".$entity_id.' and its activity type id: '.$activity_type_id);
        }
    }

    /**
     * Used to archive city news
     */
    protected function city_news($activity_id) {
        try {            
            /* Delete existing records from archival DB*/
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->delete(CITYNEWS);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(CITYNEWS);
            $this->db->where('ActivityID', $activity_id);
            $this->db->limit(1);           
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(CITYNEWS, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db->where('ActivityID', $activity_id);            
                $this->db->delete(CITYNEWS);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive city news for activity ".$activity_id);
        }
    }

    /**
     * Used to archive daily digest
     */
    protected function daily_digest($activity_id) {
        try {            
            /* Delete existing records from archival DB*/
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->delete(DIALYDIGEST);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(DIALYDIGEST);
            $this->db->where('ActivityID', $activity_id);
            $this->db->order_by('DailyDigestID', 'ASC');           
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(DIALYDIGEST, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db->where('ActivityID', $activity_id);            
                $this->db->delete(DIALYDIGEST);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive daily digest for activity ".$activity_id);
        }
    }

     /**
     * Used to archive story
     */
    protected function story($activity_id) {
        try {            
            /* Delete existing records from archival DB*/
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->delete(STORY);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(STORY);
            $this->db->where('ActivityID', $activity_id);
            $this->db->order_by('StoryID', 'ASC');           
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(STORY, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db->where('ActivityID', $activity_id);            
                $this->db->delete(STORY);
            }

            /* Delete existing records from archival DB*/
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->delete(STORYWARD);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(STORYWARD);
            $this->db->where('ActivityID', $activity_id);
            $this->db->order_by('StoryWardID', 'ASC');           
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(STORYWARD, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db->where('ActivityID', $activity_id);            
                $this->db->delete(STORYWARD);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive story for activity ".$activity_id);
        }
    }

    /**
     * Used to archive user orientation
     */
    protected function user_orientation($activity_id) {
        try {            
            /* Delete existing records from archival DB*/
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->delete(USERORIENTATION);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(USERORIENTATION);
            $this->db->where('ActivityID', $activity_id);
            $this->db->order_by('UserOrientationID', 'ASC');           
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(USERORIENTATION, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db->where('ActivityID', $activity_id);            
                $this->db->delete(USERORIENTATION);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive user orientation for activity ".$activity_id);
        }
    }

    /**
     * Used to archive subscribe
     */
    protected function subscribe($entity_type, $entity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->select('SubscribeID');
            $this->db_archive->from(SUBSCRIBE);
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->order_by('SubscribeID', 'DESC');
            $this->db_archive->limit(1);
            $query = $this->db_archive->get();
            $row = $query->row_array();

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(SUBSCRIBE);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->order_by('SubscribeID', 'ASC');
            if(isset($row['SubscribeID']) && !empty($row['SubscribeID'])) {
                $this->db->where('SubscribeID >', $row['SubscribeID']);
            }
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(SUBSCRIBE, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where('EntityType', $entity_type);
                $this->db->where('EntityID', $entity_id);
                $this->db->delete(SUBSCRIBE);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive subscribe for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to archive mentions
     */
    protected function mentions($activity_id, $comment_id=0) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->select('MentionID');
            $this->db_archive->from(MENTION);
            if(!empty($activity_id)) {
                $this->db_archive->where('ActivityID', $activity_id);
            }
            
            $this->db_archive->where('PostCommentID', $comment_id);
            $this->db_archive->order_by('MentionID', 'DESC');
            $this->db_archive->limit(1);
            $query = $this->db_archive->get();
            $row = $query->row_array();

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(MENTION);
            if(!empty($activity_id)) {
                $this->db->where('ActivityID', $activity_id);
            }
            $this->db->where('PostCommentID', $comment_id);
            $this->db->order_by('MentionID', 'ASC');
            if(isset($row['MentionID']) && !empty($row['MentionID'])) {
                $this->db->where('MentionID >', $row['MentionID']);
            }
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(MENTION, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) { 
                if(!empty($activity_id)) {
                    $this->db->where('ActivityID', $activity_id);
                }
                $this->db->where('PostCommentID', $comment_id);
                $this->db->delete(MENTION);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive mention for activity ".$activity_id.' and comment : '.$comment_id);
        }
    }

     /**
     * Used to archive links
     */
    protected function links($entity_type, $entity_id) {
        try {
            $table_name = ($entity_type == 'ACTIVITY') ? ACTIVITYLINKS : COMMENTLINKS;
            $column_name = ($entity_type == 'ACTIVITY') ? 'ActivityID' : 'CommentID';
            /* Delete existing records from archival DB*/
            $this->db_archive->where($column_name, $entity_id);
            $this->db_archive->delete($table_name);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from($table_name);
            $this->db->where($column_name, $entity_id);
            $query = $this->db->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch($table_name, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where($column_name, $entity_id);
                $this->db->delete($table_name);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive links for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to archive activity history
     */
    protected function activity_history($activity_id) {
        try {            
            /* Delete existing records from archival DB*/
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->delete(ACTIVITYHISTORY);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(ACTIVITYHISTORY);
            $this->db->where('ActivityID', $activity_id);
            $this->db->order_by('HistoryID', 'ASC');           
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(ACTIVITYHISTORY, $results);
            }            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {             
                $this->db->where('ActivityID', $activity_id);            
                $this->db->delete(ACTIVITYHISTORY);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive history for activity ".$activity_id);
        }
    }

    /**
     * Used to archive comment history
     */
    protected function comment_history($comment_id) {
        try {            
            /* Delete existing records from archival DB*/
            $this->db_archive->where('CommentID', $comment_id);
            $this->db_archive->delete(COMMENTHISTORY);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(COMMENTHISTORY);
            $this->db->where('CommentID', $comment_id);
            $this->db->order_by('HistoryID', 'ASC');           
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(COMMENTHISTORY, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {            
                $this->db->where('CommentID', $comment_id);            
                $this->db->delete(COMMENTHISTORY);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive history for comment ".$comment_id);
        }
    }

    /**
     * Used to archive admin communication
     */
    protected function admin_communication($activity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->select('AdminCommunicationID');
            $this->db_archive->from(ADMINCOMMUNICATION);
            $this->db_archive->where('ActivityID', $activity_id);
            $this->db_archive->order_by('AdminCommunicationID', 'DESC');
            $this->db_archive->limit(1);
            $query = $this->db_archive->get();
            $row = $query->row_array();

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(ADMINCOMMUNICATION);
            $this->db->where('ActivityID', $activity_id);
            $this->db->order_by('AdminCommunicationID', 'ASC');
            if(isset($row['AdminCommunicationID']) && !empty($row['AdminCommunicationID'])) {
                $this->db->where('AdminCommunicationID >', $row['AdminCommunicationID']);
            }
            $query = $this->db->get();
            $results = $query->result_array();
            foreach ($results as $result) {                
                $this->db->select('*');
                $this->db->from(ADMINCOMMUNICATIONHISTORY);
                $this->db->where('AdminCommunicationID', $result['AdminCommunicationID']);
                $this->db->order_by('AdminCommunicationHistoryID', 'ASC');
                $communication_history_query = $this->db->get();
                $communication_history_results = $communication_history_query->result_array();
                if($communication_history_results) {
                    $this->db_archive->insert_on_duplicate_update_batch(ADMINCOMMUNICATIONHISTORY, $communication_history_results);
                }                
                $communication_history_query->free_result();

                if($this->can_delete_from_source_db) {
                    $this->db->where('AdminCommunicationID', $result['AdminCommunicationID']);
                    $this->db->delete(ADMINCOMMUNICATIONHISTORY);
                }
            }
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(ADMINCOMMUNICATION, $results);
            }
            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where('ActivityID', $activity_id);
                $this->db->delete(ADMINCOMMUNICATION);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception('Issue with archive admin communication for activity and its id: '.$activity_id);
        }
    }

    /**
     * Used to archive Favourite activity
     */
    protected function favourite($entity_type, $entity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->delete(FAVOURITE);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(FAVOURITE);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->order_by('FavouriteID', 'ASC');
            $query = $this->db->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(FAVOURITE, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where('EntityType', $entity_type);
                $this->db->where('EntityID', $entity_id);
                $this->db->delete(FAVOURITE);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive favourite for ".$entity_type.' and its id: '.$entity_id);
        }
    }

    /**
     * Used to archive flag activity
     */
    protected function flag($entity_type, $entity_id) {
        try {
            /* Delete existing records from archival DB*/
            $this->db_archive->where('EntityType', $entity_type);
            $this->db_archive->where('EntityID', $entity_id);
            $this->db_archive->delete(FLAG);

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(FLAG);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->order_by('FlagID', 'ASC');
            $query = $this->db->get();
            //print_r($query->result_array());die;
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(FLAG, $results);
            }
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where('EntityType', $entity_type);
                $this->db->where('EntityID', $entity_id);
                $this->db->delete(FLAG);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive flag for ".$entity_type.' and its id: '.$entity_id);
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
            /* Delete existing records from archival DB*/
            $this->db_archive->select('NotificationID');
            $this->db_archive->from(NOTIFICATIONS);
            $this->db_archive->where_in('NotificationTypeID', $notification_type_ids);
            $this->db_archive->where('RefrenceID', $refrence_id);
            $this->db_archive->order_by('NotificationID', 'DESC');
            $this->db_archive->limit(1);
            $query = $this->db_archive->get();
            $row = $query->row_array();

            /** Select records from production DB and insert into archival DB */
            $this->db->select('*');
            $this->db->from(NOTIFICATIONS);
            $this->db->where_in('NotificationTypeID', $notification_type_ids);
            $this->db->where('RefrenceID', $refrence_id);
            $this->db->order_by('NotificationID', 'ASC');
            if(isset($row['NotificationID']) && !empty($row['NotificationID'])) {
                $this->db->where('NotificationID >', $row['NotificationID']);
            }
            $query = $this->db->get();
            $results = $query->result_array();
            if($results) {
                $this->db_archive->insert_on_duplicate_update_batch(NOTIFICATIONS, $results);
            }
            
            foreach ($results as $result) {                
                $this->db->select('*');
                $this->db->from(NOTIFICATIONPARAMS);
                $this->db->where('NotificationID', $result['NotificationID']);
                $this->db->order_by('NotificationParamID', 'ASC');
                $notification_param_query = $this->db->get();
                $notification_param_results = $notification_param_query->result_array();
                if($notification_param_results) {
                    $this->db_archive->insert_on_duplicate_update_batch(NOTIFICATIONPARAMS, $notification_param_results);
                }                
                $notification_param_query->free_result();

                if($this->can_delete_from_source_db) {
                    $this->db->where('NotificationID', $result['NotificationID']);
                    $this->db->delete(NOTIFICATIONPARAMS);
                }
            }
            
            $query->free_result();

            /* Delete existing records from production DB*/
            if($this->can_delete_from_source_db) {
                $this->db->where_in('NotificationTypeID', $notification_type_ids);
                $this->db->where('RefrenceID', $refrence_id);
                $this->db->delete(NOTIFICATIONS);
            }
            $this->check_transaction($this->db_archive);
            
        } catch (Exception $e) {
            $this->db_archive->trans_rollback();
            throw new Exception("Issue with archive notification for refrence_id ".$refrence_id.' and its notification type: '.json_encode($notification_type_ids));
        }
    }

    public function synch_user($page_no=1, $page_size=500) {
       
        try {              
            $this->db->select('*');
            $this->db->from(USERS);
            $this->db->order_by('UserID', 'DESC');
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            $query = $this->db->get();
            //echo $this->db->last_query();die;
            if ($query->num_rows() > 0) {
                $results = $query->result_array();
                if($results) {
                    $this->db_archive->insert_on_duplicate_update_batch(USERS, $results);
                }
                $query->free_result();

                $this->db->select('*');
                $this->db->from(PROFILEURL);
                $this->db->where('EntityType', 'User');
                $this->db->order_by('EntityID', 'DESC');
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $results = $query->result_array();
                    if($results) {
                        $this->db_archive->insert_on_duplicate_update_batch(PROFILEURL, $results);
                    }
                }
                $query->free_result();


                $this->db->select('*');
                $this->db->from(USERDETAILS);
                $this->db->order_by('UserID', 'DESC');
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $results = $query->result_array();
                    if($results) {
                        $this->db_archive->insert_on_duplicate_update_batch(USERDETAILS, $results);
                    }
                }
                $query->free_result();

                $this->synch_user(++$page_no);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Issue with user synching');
        }
        
    }

    public function synch_album($page_no=1, $page_size=500) {
       
        try {              
            $this->db->select('*');
            $this->db->from(ALBUMS);
            $this->db->order_by('AlbumID', 'DESC');
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            $query = $this->db->get();
            //echo $this->db->last_query();die;
            if ($query->num_rows() > 0) {
                $results = $query->result_array();
                if($results) {
                    $this->db_archive->insert_on_duplicate_update_batch(ALBUMS, $results);
                }
                $query->free_result();
                $this->synch_album(++$page_no);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Issue with album synching');
        }
        
    }

    public function synch_tag($page_no=1, $page_size=500) {
       
        try {              
            $this->db->select('*');
            $this->db->from(TAGS);
            $this->db->order_by('TagID', 'DESC');
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            $query = $this->db->get();
            //echo $this->db->last_query();die;
            if ($query->num_rows() > 0) {
                $results = $query->result_array();
                if($results) {
                    $this->db_archive->insert_on_duplicate_update_batch(TAGS, $results);
                }
                $query->free_result();
                $this->synch_tag(++$page_no);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Issue with tag synching');
        }
        
    }

    public function synch_tag_category($page_no=1, $page_size=500) {
       
        try {              
            $this->db->select('*');
            $this->db->from(TAGCATEGORY);
            $this->db->order_by('TagCategoryID', 'DESC');
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            $query = $this->db->get();
            //echo $this->db->last_query();die;
            if ($query->num_rows() > 0) {
                $results = $query->result_array();
                foreach ($results as $result) {  
                    $this->db->select('*');
                    $this->db->from(TAGSOFTAGCATEGORY);
                    $this->db->where('TagCategoryID', $result['TagCategoryID']);
                    $this->db->order_by('TagCategoryID', 'ASC');
                    $tag_category_query = $this->db->get();
                    $tag_category_results = $tag_category_query->result_array();
                    if($tag_category_results) {
                        $this->db_archive->insert_on_duplicate_update_batch(TAGSOFTAGCATEGORY, $tag_category_results);
                    }                
                    $tag_category_query->free_result();
                }
                if($results) {
                    $this->db_archive->insert_on_duplicate_update_batch(TAGCATEGORY, $results);
                }
                $query->free_result();
                $this->synch_tag_category(++$page_no);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Issue with tag category synching');
        }
        
    }

}
?>
