<?php
if (!defined('BASEPATH'))  exit('No direct script access allowed');

class Rating_model extends Common_Model 
{

    public function __construct() 
    {
        parent::__construct();
        $this->load->language('rating');
    }

    /**
     * [add_edit_rating Add new / Edit existing rating]
     * @param  $user_id
     * @param  $rating_guid
     * @param  $module_id
     * @param  $module_entity_id
     * @param  $rate_value
     * @param  $title
     * @param  $description
     * @param  $media
     * @param  $post_as_module_id
     * @param  $post_as_module_entity_id
     * @param  $rating_parameter_value
     * @return [RatingGUID, ReviewGUID, CreatedDate, ModifiedDate]
     */
    public function add_edit_rating($user_id, $rating_guid, $module_id, $module_entity_id, $rate_value, $title, $description, $media, $post_as_module_id, $post_as_module_entity_id, $rating_parameter_value)
    {
        if(!$rating_guid)
        {
    		// Edit Rating
    		$new_rating = true;
    		$rating_guid = get_guid();
        } 
        else 
        {
            // Add Rating
            $new_rating = false;
            $rating_query = $this->db->get_where(RATINGS,array('RatingGUID'=>$rating_guid));
            $rating_row = $rating_query->row_array();
            
            $this->db->set('StatusID',3);
            $this->db->where('MediaSectionID',8);
            $this->db->where('MediaSectionReferenceID',$rating_row['RatingID']);
            $this->db->update(MEDIA);
        }

    	// Entry in rating
        if($new_rating)
        {
    	   $data['RatingGUID'] = $rating_guid;
    	   $data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        }
        if($rating_parameter_value)
        {
            $rate_value = 0;
            $count = 0;
            foreach ($rating_parameter_value as $val) 
            {
                $rate_value = $rate_value+$val['RateValue'];
                $count++;
            }
            $rate_value = ($rate_value/$count);
        }
        $data['ModuleID']               = $module_id;
        $data['ModuleEntityID']         = $module_entity_id;
        $data['RateValue']              = $rate_value;
        $data['PostAsModuleID']         = NULL;
        $data['PostAsModuleEntityID']   = NULL;
        $data['AgeGroupID']             = NULL;
        $data['CityID']                 = NULL;
        $data['UserID']                 = $user_id;
        if(!empty($post_as_module_id) && !empty($post_as_module_entity_id))
        {
            $data['PostAsModuleID'] = $post_as_module_id;
            $data['PostAsModuleEntityID'] = $post_as_module_entity_id;
        } 
        else 
        {
            $this->db->select("U.Gender,UD.CityID,DATE_FORMAT(FROM_DAYS(DATEDIFF('".get_current_date('%Y-%m-%d')."',UD.DOB)), '%Y')+0 as Age",false);
            $this->db->from(USERS.' U');
            $this->db->join(USERDETAILS.' UD','U.UserID=UD.UserID','left');
            $this->db->where('U.UserID',$user_id);
            $user_details_query = $this->db->get();
            $user_details_result = $user_details_query->row_array();
            if(isset($user_details_result['Age']))
            {
                $this->db->select('AgeGroupID');
                $this->db->from(AGEGROUPS);
                $this->db->where($user_details_result['Age'].' BETWEEN ValueRangeFrom AND ValueRangeTo',NULL,FALSE);
                $age_query = $this->db->get();
                if($age_query->num_rows())
                {
                    $user_details_result['AgeGroupID'] = $age_query->row()->AgeGroupID;
                } 
                else 
                {
                    $user_details_result['AgeGroupID'] = NULL; 
                }
            } 
            else 
            {
                $user_details_result['AgeGroupID'] = NULL;   
            }
            $data['AgeGroupID'] = $user_details_result['AgeGroupID'];
            $data['CityID'] = $user_details_result['CityID'];
        }
        $data['Status'] = 'ACTIVE';
        $data['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $activity_id = false;
        if($new_rating)
        {
            $this->db->insert(RATINGS,$data);
            $rating_id = $this->db->insert_id();
        } 
        else 
        {
            unset($data['ModuleID']);            
            unset($data['ModuleEntityID']);            
            unset($data['PostAsModuleID']);            
            unset($data['PostAsModuleEntityID']);            
            $data['IsEdited'] = 1;
            $this->db->where('RatingGUID',$rating_guid);
            $this->db->update(RATINGS,$data);
            $this->db->select('RatingID, CreatedDate, ActivityID');
            $lastUpdateRecord = $this->db->get_where(RATINGS,array('RatingGUID'=>$rating_guid));
            $rating_id = $lastUpdateRecord->row()->RatingID;
            $activity_id  = $lastUpdateRecord->row()->ActivityID;
            $data['CreatedDate'] = $lastUpdateRecord->row()->CreatedDate;
        }

        if($rating_parameter_value)
        {
            $rating_parameter_data = array();
            foreach($rating_parameter_value as $value)
            {
                $rpd = array('RatingID'=>$rating_id,'RateValue'=>$value['RateValue'],'ModifiedDate'=>$data['ModifiedDate']);
                if($new_rating)
                {
                    $rpd['CreatedDate'] = $data['CreatedDate'];
                    $rpd['RatingParameterID'] = $value['RatingParameterID'];
                } 
                else 
                {
                    $this->db->where('RatingID',$rating_id);
                    $this->db->where('RatingParameterID',$value['RatingParameterID']);
                    $this->db->update(RATINGPARAMETERVALUE,$rpd);
                }
                $rating_parameter_data[] = $rpd;
            }
            if($new_rating)
            {
                $this->db->insert_batch(RATINGPARAMETERVALUE,$rating_parameter_data);
            }
        }

        $review['Title'] = $title;
        $review['Description'] = $description;
        $review['ModifiedDate'] = $data['ModifiedDate'];
        if($new_rating)
        {
            $review['RatingID'] = $rating_id;
            $review['ReviewGUID'] = get_guid();
            $review['CreatedDate'] = $data['CreatedDate'];
            $this->db->insert(REVIEWS,$review);
            $this->load->model('activity/activity_model');
            $activity_id = $this->activity_model->addActivity($data['ModuleID'], $data['ModuleEntityID'], 16, $user_id,0,'','','',array('RatingID'=>$rating_id),0,0,$post_as_module_id,$post_as_module_entity_id);

            //Update activityid in rating table
            $this->db->set('ActivityID',$activity_id);
            $this->db->where('RatingID',$rating_id);
            $this->db->update(RATINGS);
        } 
        else 
        {
            $this->db->where('RatingID',$rating_id);
            $this->db->update(REVIEWS,$review);
            $this->db->select('ReviewGUID');
            $last_update_record = $this->db->get_where(REVIEWS,array('RatingID'=>$rating_id));
            $review['ReviewGUID'] = $last_update_record->row()->ReviewGUID;
            
            //update activity table
            if($activity_id){
                $this->db->set('ActivityTypeID',17);
                $this->db->set('ModifiedDate',get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->where('ActivityID',$activity_id);
                $this->db->update(ACTIVITY);
            }
        }

        if($media)
        {
            $mediaData = array();
            foreach ($media as $mData) 
            {
                $mediaData[] = array('MediaGUID'=>$mData['MediaGUID'],'StatusID'=>'2');
            }
            $this->db->update_batch(MEDIA,$mediaData,'MediaGUID');
        }

        if(!empty($post_as_module_id) && !empty($post_as_module_entity_id))
        {
            $notification_users_one = array(); //get_entity_users($post_as_module_id, $post_as_module_entity_id);
        } 
        else 
        {
            $notification_users_one = array(); //get_entity_users(3, $user_id);
        }
        $notification_users_two = get_entity_admin($module_id, $module_entity_id);
        $notification_users = array_merge($notification_users_one, $notification_users_two);
        if(in_array($user_id, $notification_users))
        {
            //unset($notification_users[array_search($user_id, $notification_users)]);
            //unset($notification_users_one[array_search($user_id, $notification_users_one)]);
            //unset($notification_users_two[array_search($user_id, $notification_users_two)]);
        }

        if(!empty($media))
        {
            $album_name = DEFAULT_WALL_ALBUM;
            if(count($media)==1)
            {
                $media_guid      = $media[0]['MediaGUID'];
                $media_type     = get_media_type($media_guid);
                if($media_type==2)
                {
                    $album_name     = DEFAULT_WALL_ALBUM;    
                }
            }
            $album_id = 0; 
            $this->load->model('media/media_model');
            if(!$new_rating)
            {
                $module_id = $rating_row['ModuleID'];
                $module_entity_id = $rating_row['ModuleEntityID'];
            }
            //$this->media_model->updateMedia($media, $rating_id, $user_id, $module_id, $module_entity_id, $album_id, true, 8);
            $this->media_model->updateMedia($media, $rating_id, $user_id, $album_id);
        } 
        else 
        {
            if(!$new_rating)
            {
                $this->db->where('MediaSectionID',8);
                $this->db->where('MediaSectionReferenceID',$rating_id);
                $this->db->delete(MEDIA);
            }
        }

        if($notification_users)
        {
            $parameters[0]['ReferenceID'] = $user_id;
            $parameters[0]['Type'] = 'User';
            
            if($post_as_module_id)
            {
                switch ($post_as_module_id) 
                {
                    case '1':
                        $parameters[0]['Type'] = 'Group';
                    break;
                    case '3':
                        $parameters[0]['Type'] = 'User';
                    break;
                    case '14':
                        $parameters[0]['Type'] = 'Event';
                    break;
                    case '18':
                        $parameters[0]['Type'] = 'Page';
                    break;
                }
                $parameters[0]['ReferenceID'] = $post_as_module_entity_id;
            }

            switch ($module_id) 
            {
                case '1':
                    $parameters[1]['Type'] = 'Group';
                break;
                case '3':
                    $parameters[1]['Type'] = 'User';
                break;
                case '14':
                    $parameters[1]['Type'] = 'Event';
                break;
                case '18':
                    $parameters[1]['Type'] = 'Page';
                break;
            }
            $parameters[1]['ReferenceID'] = $module_entity_id;

            $this->load->model('notification_model');
            if($new_rating)
            {
                if($notification_users_two)
                {
                    foreach($notification_users_two as $nt1)
                    {
                        unset($notification_users_one[array_search($nt1, $notification_users_one)]);
                    }
                }

                if($notification_users_one)
                {
                    $this->notification_model->add_notification(52, $user_id, $notification_users_one, $rating_id, $parameters,false);
                }
                if($notification_users_two)
                {
                    $this->notification_model->add_notification(52, $user_id, $notification_users_two, $rating_id, $parameters);
                }
            } 
            else 
            {
                if(in_array($user_id, $notification_users_two))
                {
                    unset($notification_users_two[array_search($user_id, $notification_users_two)]);
                }
                if($notification_users_two)
                {
                    $this->notification_model->add_notification(53, $user_id, $notification_users_two, $rating_id, $parameters);
                }
            }
        }
        return array('RatingGUID'=>$rating_guid, 'ReviewGUID'=>$review['ReviewGUID'], 'CreatedDate'=>$data['CreatedDate'], 'ModifiedDate'=>$data['ModifiedDate']);
    }

    /**
     * [vote - vote if rating is useful or not]
     * @param  $user_id
     * @param  $vote
     * @param  $entity_guid
     * @param  $entity_type
     * @return Boolean
     */
    public function vote($user_id, $entity_guid, $entity_type, $vote)
    {
        $entity_id = get_detail_by_guid($entity_guid,23);
        $query = $this->db->get_where(VOTES,array('UserID'=>$user_id,'EntityID'=>$entity_id,'EntityType'=>$entity_type));
        if($query->num_rows() <= 0)
        {
            $this->db->insert(VOTES,array(
                'UserID'=>$user_id,
                'EntityID'=>$entity_id,
                'EntityType'=>$entity_type,
                'Status'=>$vote,
                'CreatedDate'=>get_current_date('%Y-%m-%d')
                ));
            $this->update_vote_count($vote, $entity_id, $entity_type);
            if($entity_type == 'RATING' && $vote == 'YES')
            {
                $query = $this->db->select('UserID,RatingID')->from(RATINGS)->where('RatingGUID',$entity_guid)->get();
                if($query->num_rows())
                {
                    $row = $query->row_array();
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $this->load->model('notification_model');
                    $this->notification_model->add_notification(60, $user_id, array($row['UserID']), $row['RatingID'], $parameters, 'review_marked_helpful');
                }
            }
            return true;
        }
        return false;
    }

    /**
     * [update_vote_count description]
     * @param  [int]    $vote   [Vote value]
     * @param  [int]    $entity_id   [Entity Id]
     * @param  [string] $entity_type [RATING]
     * @return [type]               [description]
     */
    public function update_vote_count($vote, $entity_id, $entity_type)
    {
        $set_field = "PositiveVoteCount"; 
        $table_name = RATINGS;
        $condition  = array("RatingID" => $entity_id);
        $count = 1;
        switch ($entity_type) 
        {
            case 'RATING':
                $table_name = RATINGS;
                $condition  = array("RatingID" => $entity_id);
                if($vote=='NO')
                {
                    $set_field = "NegativeVoteCount"; 
                }
                break;           
            default:
                return false;
                break;
        } 
        $this->db->where($condition);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->update($table_name);  
    }

    /**
     * [is_voted description]
     * @param  [int]  $user_id     [User ID]
     * @param  [int]  $entity_id   [Entity ID]
     * @param  [string]  $entity_type [Entity Type]
     * @return boolean              [description]
     */
    public function is_voted($user_id, $entity_id, $entity_type)
    {
        $query = $this->db->get_where(VOTES, array('UserID'=>$user_id, 'EntityID'=>$entity_id, 'EntityType'=>$entity_type));
        if($query->num_rows() > 0)
        {
            return true;
        }
        return false;
    }

    /**
     * [get_rating_parameters - list of rating parameters]
     * @param  $category_id
     * @return [list of rating parameters]
     */
    public function get_rating_parameters($category_id)
    {
        $this->db->select('RatingParameterID,ParameterKey,ParameterName');
        $this->db->from(RATINGPARAMETER);
        $this->db->where('Status','ACTIVE');
        $this->db->where('CategoryID',$category_id);
        $this->db->order_by('RatingParameterID');
        $query = $this->db->get();
        if($query->num_rows())
        {
            return $query->result_array();
        } 
        else 
        {
            return array();
        }
    }

    /**
     * [get_rating_parameter_value - rating parameter values]
     * @param  $rating_id
     * @return [list of rating parameters value]
     */
    public function get_rating_parameter_value($rating_id)
    {
        $this->db->select('RP.RatingParameterID,RP.ParameterKey,RP.ParameterName,RPV.RateValue');
        $this->db->from(RATINGPARAMETER.' RP');
        $this->db->join(RATINGPARAMETERVALUE.' RPV','RP.RatingParameterID=RPV.RatingParameterID','left');
        $this->db->where('RPV.RatingID',$rating_id);
        $this->db->where('RP.Status','ACTIVE');
        $query = $this->db->get();
        if($query->num_rows())
        {
            return $query->result_array();
        } 
        else 
        {
            return array();
        }
    }

    /**
     * [created_by - details of rating owner]
     * @param  $user_id
     * @param  $post_as_module_id
     * @param  $post_as_module_entity_id
     * @return [user or entity details - EntityName,EntityGUID,ProfilePicture,ProfileURL]
     */
    function created_by($user_id, $post_as_module_id='', $post_as_module_entity_id='')
    {
        if(!empty($post_as_module_id) && !empty($post_as_module_entity_id))
        {
            $result=array();
            switch ($post_as_module_id) 
            {
                case 18:
                    
                    if (CACHE_ENABLE) 
                    {
                      $cache_data = $this->cache->get('page_'.$post_as_module_entity_id);
                      if(!empty($cache_data))
                      {
                        $result['EntityName']=$cache_data['Title'];
                        $result['EntityGUID']=$cache_data['PageGUID'];
                        $result['ProfilePicture']=$cache_data['ProfilePicture'];
                        $result['ProfileURL']='page/'.$cache_data['PageURL'];
                        $result['ModuleID']=18;
                      }
                    }
                    if(empty($result))
                    {
                        $this->db->select("P.Title as EntityName, P.PageGUID as EntityGUID, if(P.ProfilePicture='',CM.Icon,P.ProfilePicture) as ProfilePicture, CONCAT('page/',P.PageURL) as ProfileURL, '18' as ModuleID",false);
                        $this->db->from(PAGES.' P');
                        $this->db->join(CATEGORYMASTER.' CM','CM.CategoryID=P.CategoryID','left');
                        $this->db->where('P.PageID',$post_as_module_entity_id);
                        $query = $this->db->get();
                        //echo $this->db->last_query();
                        if($query->num_rows())
                        {
                            $result=$query->row_array();
                        }
                    }
                    return $result;
                    
                break;
                
                default:
                    return array();
                break;
            }
        } 
        else 
        {
            $this->db->select("CONCAT(U.FirstName,' ',U.LastName) as EntityName,U.UserGUID as EntityGUID,IF(U.ProfilePicture='','user_default.jpg',U.ProfilePicture) as ProfilePicture,P.Url as ProfileURL, '3' as ModuleID",false);
            $this->db->from(USERS.' U');
            $this->db->join(PROFILEURL.' P','U.UserID=P.EntityID','left');
            $this->db->where('P.EntityType','User');
            $this->db->where('U.UserID',$user_id);
            $query = $this->db->get();
            if($query->num_rows())
            {
                return $query->row_array();
            } 
            else 
            {
                return array();
            }
        }
    }

    /**
     * [get_rating_entity_admin Used to get the owner of entity for given rating id]
     * @param  [int] $rating_id [rating id]
     * @return [int]            [Owner id]
     */
    public function get_rating_entity_admin($rating_id)
    {
        $admin_id = '';
        $this->db->select('ModuleID,ModuleEntityID');
        $this->db->from(RATINGS);
        $this->db->where('RatingID',$rating_id);
        $query = $this->db->get();
        if($query->num_rows())
        {
            $data = $query->row_array();
            switch ($data['ModuleID']) {
                case 1:
                    $this->load->model('group/group_model');
                    $admin_id = $this->group_model->getGroupOwner($data['ModuleEntityID']);
                break;
                
                case 14:
                    $this->load->model('events/event_model');
                    $admin_id = $this->event_model->getEventOwner($data['ModuleEntityID']);
                break;
                
                case 18:
                    $this->load->model('pages/page_model');
                    $admin_id = $this->page_model->get_page_owner(get_detail_by_id($data['ModuleEntityID'],$data['ModuleID'],'PageGUID',1));
                break;
            }
        }
        if($admin_id)
        {
            return array($admin_id);
        }
        else
        {
            return false;
        }
    }

    /**
     * [get_rating_owner - get user id of rating owner]
     * @param  $rating_id
     * @return [UserID]
     */
    public function get_rating_owner($rating_id)
    {
        $this->db->select('UserID');
        $this->db->from(RATINGS);
        $this->db->where('RatingID',$rating_id);
        $query = $this->db->get();
        if($query->num_rows())
        {
            return $query->row()->UserID;
        }
        else 
        {
            return false;
        }
    }

    /**
     * [get_user_relationship - get list of mutual friend of Current User and Rating Owner]
     * @param  $user_id
     * @param  $current_user_id
     * @return [MutualFriend list and TotalMutualFriendCount]
     */
    public function get_user_relationship($user_id, $current_user_id)
    {
        $this->load->model('users/friend_model');
        $total_count = 0;
        $friends = array();
        if($this->friend_model->checkFriendStatus($user_id, $current_user_id)==1)
        {
            $f = $this->created_by($current_user_id);
            $friends[] = array('UserGUID'=>$f['EntityGUID'],'Name'=>'You','ProfilePicture'=>$f['ProfilePicture'],'ProfileURL'=>$f['ProfileURL']);
            $total_count = 1;
        }
        $limit = 2;
        if($total_count)
        {
            $total_count = $this->friend_model->get_mutual_friend($user_id, $current_user_id, '', 1);
            $total_count = $total_count + 1;

            $limit = 1;
        }
        $friend_list = $this->friend_model->get_mutual_friend($user_id, $current_user_id, '', 0, 1, $limit); 
        if(isset($friend_list['Friends']) && !empty($friend_list['Friends']))
        {
            foreach ($friend_list['Friends'] as $arr) 
            {
                $f = array('UserGUID'=>$arr['UserGUID'],'Name'=>$arr['FirstName'].' '.$arr['LastName'],'ProfilePicture'=>$arr['ProfilePicture'],'ProfileURL'=>$arr['ProfileURL']);
                $friends[] = $f;
            }
        }
        return array('TotalRecords'=>$total_count,'Friends'=>$friends);
    }

    /**
     * [get_rating_list - get list of ratings of particular entity with filters]
     * @param  $user_id
     * @param  $module_id
     * @param  $module_entity_id
     * @param  $location
     * @param  $start_date
     * @param  $end_date
     * @param  $age_group
     * @param  $gender
     * @param  $sort_by
     * @param  $page_no
     * @param  $page_size
     * @param  $count_only
     * @param  $rating_guid
     * @return [list of ratings]
     */
    public function get_rating_list($user_id, $module_id, $module_entity_id, $location, $start_date, $end_date, $age_group, $gender,$admin_only, $sort_by=1, $page_no=1, $page_size=10, $count_only=0, $rating_guid='')
    {
        
        $this->load->model(array('activity/activity_model','flag_model','users/user_model'));
        
        $friend_followers_list    = $this->user_model->gerFriendsFollowersList($user_id,true,1);
        $friends                = $friend_followers_list['Friends'];
        $friends[]              = 0;

        $this->db->select('RT.PostAsModuleID,RT.PostAsModuleEntityID,ACT.PostAsModuleID as A_PostAsModuleID,ACT.PostAsModuleEntityID as A_PostAsModuleEntityID,RT.UserID,RT.RatingID,RT.RatingGUID,RT.RateValue,RT.PositiveVoteCount,RT.ActivityID,RT.NegativeVoteCount,ACT.NoOfComments,RT.IsCommentable,RT.CreatedDate,RT.ModifiedDate,RT.Status,RT.IsEdited,ATY.ActivityType,ACT.ActivityGUID');
        $this->db->select('RV.ReviewGUID,RV.Title,RV.Description');
        $this->db->from(RATINGS.' RT');
        $this->db->join(ACTIVITY.' ACT','ACT.ActivityID=RT.ActivityID','left');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'ACT.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(REVIEWS.' RV','RT.RatingID=RV.RatingID','left');
        if($rating_guid)
        {
            $this->db->where('RT.RatingGUID',$rating_guid);
        } 
        else 
        {    
            $this->db->where('RT.ModuleID',$module_id);
            $this->db->where('RT.ModuleEntityID',$module_entity_id);
            
            if(isset($location['City']) && !empty($location['City']))
            {
                $this->db->join(CITIES.' CT','CT.CityID=RT.CityID','left');
                $this->db->where('CT.Name',$location['City']);
            }

            if($admin_only)
            {
                $this->db->where("RT.PostAsModuleID","18");
            }

            if($start_date)
            {
                if(!$end_date)
                {
                    $end_date = $start_date;
                }
                $this->db->where("RT.ModifiedDate BETWEEN '".$start_date."' AND '".$end_date."'",NULL,FALSE);
            }

            if($gender)
            {
                $this->db->join(USERS.' U','U.UserID=RT.UserID','left');
                $this->db->where('Gender',$gender);
                $this->db->where("RT.PostAsModuleID is NULL",NULL,FALSE);
            }

            if($age_group)
            {
                $this->db->where('RT.AgeGroupID',$age_group);
            }
        }
        $this->db->where_in('RT.Status',array('ACTIVE','APPROVED'));

        $this->db->_protect_identifiers = FALSE;
        $this->db->order_by("RT.UserID='".$user_id."'","DESC");
        $this->db->_protect_identifiers = TRUE;

        if($sort_by == 1)
        {
            $this->db->order_by('RT.ModifiedDate','DESC');
        }
        else if($sort_by == 2)
        {
            $this->db->order_by('RT.PositiveVoteCount','DESC');
        } 
        else if($sort_by == 3)
        {
            //Get friend USER ID IN COMMA SEPARATED
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD( RT.UserID,  '.implode(',', $friends).' ) DESC', NULL, FALSE);
            $this->db->order_by('RT.ModifiedDate','DESC');
            $this->db->_protect_identifiers = TRUE;
        } 
        else 
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by("IF(RT.PostAsModuleEntityID is NULL,RT.UserID='".$user_id."',false)","DESC");
            $this->db->_protect_identifiers = TRUE;
            
            $this->db->order_by('RT.ModifiedDate','DESC');
        }

        if($count_only)
        {
            $query = $this->db->get();
            return $query->num_rows();
        } 
        else 
        {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        
        
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if($query->num_rows())
        {
            $data = array();
            foreach($query->result_array() as $result)
            {
                $rating = $result;
                unset($rating['Title']);
                unset($rating['Description']);
                unset($rating['ReviewGUID']);
                unset($rating['RatingID']);
                unset($rating['UserID']);
               

                $rating_id = $result['RatingID'];                
                $IsOwner = 0;
                if($user_id == $result['UserID']){
                    $IsOwner = 1;
                }                
                $rating['IsOwner']      = $IsOwner;
                $rating['IsFlagged']    = 0;
                $rating['IsVoted']      = 0;
                $rating['Flaggable']    = 1;
                if($this->flag_model->is_flagged($user_id, $rating_id,'RATING'))
                {
                    $rating['IsFlagged'] = 1;
                }
                if($this->is_voted($user_id, $rating_id,'RATING'))
                {
                    $rating['IsVoted'] = 1;
                }
                if($rating['Status'] == "APPROVED") {
                    $rating['Flaggable']      = 0;
                    unset($rating['Status']);
                }

                $rating['Review'] = array('ReviewGUID'=>$result['ReviewGUID'],'Title'=>$result['Title'],'Description'=>$result['Description']);

                $rating['RatingParameterValue'] = $this->get_rating_parameter_value($rating_id);

                $rating['Comments'] = $this->activity_model->getActivityComments('ACTIVITY', $result['ActivityID'], '1', COMMENTPAGESIZE, $user_id, $IsOwner,2,TRUE,array(),FALSE,'',$result['A_PostAsModuleID'],$result['A_PostAsModuleEntityID']);
                
                $rating['Album'] = $this->activity_model->get_albums($rating_id, $result['UserID'], '', 'Ratings', 10);

                $rating['CreatedBy'] = $this->created_by($result['UserID'], $result['PostAsModuleID'], $result['PostAsModuleEntityID']);

                if($result['UserID'] == $user_id)
                {
                    $rating['MutualFriends'] = array('TotalCount'=>'0','Friends'=>array());
                } 
                else 
                {
                    $rating['MutualFriends'] = $this->get_user_relationship($result['UserID'],$user_id);
                }
                $data[] = $rating;
            }
            if($rating_guid)
            {
                if($data)
                {
                    return $data[0];
                }
            }
            return $data;
        } 
        else 
        {
            return array();
        }
    }

    public function get_rating_by_id($rating_id,$user_id)
    {
        
        $this->load->model(array('activity/activity_model','flag_model'));
        
        $this->db->select('RT.PostAsModuleID,RT.PostAsModuleEntityID,RT.UserID,RT.RatingID,RT.RatingGUID,RT.RateValue,RT.PositiveVoteCount,RT.NegativeVoteCount,RT.NoOfComments,RT.IsCommentable,RT.CreatedDate,RT.ModifiedDate,RT.Status,RT.IsEdited');
        $this->db->select('RV.ReviewGUID,RV.Title,RV.Description');
        $this->db->from(RATINGS.' RT');
        $this->db->join(REVIEWS.' RV','RT.RatingID=RV.RatingID','left');
        $this->db->where('RT.RatingID',$rating_id);
        $this->db->where_in('RT.Status',array('ACTIVE','APPROVED'));
        
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if($query->num_rows())
        {
            $data = array();
            foreach($query->result_array() as $result)
            {
                $rating = $result;
                unset($rating['Title']);
                unset($rating['Description']);
                unset($rating['ReviewGUID']);
                unset($rating['RatingID']);
                unset($rating['UserID']);
                unset($rating['PostAsModuleID']);
                unset($rating['PostAsModuleEntityID']);

                $rating_id = $result['RatingID'];                
                $IsOwner = 0;
                if($user_id == $result['UserID']){
                    $IsOwner = 1;
                }                
                $rating['IsOwner']      = $IsOwner;
                $rating['IsFlagged']    = 0;
                $rating['IsVoted']      = 0;
                $rating['Flaggable']    = 1;
                if($this->flag_model->is_flagged($user_id, $rating_id,'RATING'))
                {
                    $rating['IsFlagged'] = 1;
                }
                if($this->is_voted($user_id, $rating_id,'RATING'))
                {
                    $rating['IsVoted'] = 1;
                }
                if($rating['Status'] == "APPROVED") {
                    $rating['Flaggable']      = 0;
                    unset($rating['Status']);
                }

                $rating['Review'] = array('ReviewGUID'=>$result['ReviewGUID'],'Title'=>$result['Title'],'Description'=>$result['Description']);

                $rating['RatingParameterValue'] = $this->get_rating_parameter_value($rating_id);
                
                $rating['Comments'] = array(); //$this->activity_model->getActivityComments('RATING', $rating_id, '1', COMMENTPAGESIZE, $user_id, $IsOwner,2,TRUE,array(),FALSE,'');
                
                $rating['Album'] = $this->activity_model->get_albums($rating_id, $result['UserID'], '', 'Ratings', 10);

                $rating['CreatedBy'] = $this->created_by($result['UserID'], $result['PostAsModuleID'], $result['PostAsModuleEntityID']);

                if($result['UserID'] == $user_id)
                {
                    $rating['MutualFriends'] = array('TotalCount'=>'0','Friends'=>array());
                } 
                else 
                {
                    $rating['MutualFriends'] = $this->get_user_relationship($result['UserID'],$user_id);
                }
                return $rating;
            }
        }
    }

    function get_review($rating_id)
    {
        $query = $this->db->get_where(REVIEWS,array('RatingID'=>$rating_id));
        if($query->num_rows())
        {
            return $query->row_array();
        }
    }

    /**
     * [get_overall_ratings - get overall rating of particular entity]
     * @param  $module_id
     * @param  $module_entity_guid
     * @return [TotalRecords and TotalRateValue]
     */
    public function get_overall_ratings($module_id, $module_entity_guid)
    {
        $module_entity_id = get_detail_by_guid($module_entity_guid,$module_id);

        $this->db->select("COUNT(RatingID) as TotalRecords, SUM(RateValue) as TotalRateValue",false);
        $this->db->from(RATINGS);
        $this->db->where('ModuleID',$module_id);
        $this->db->where('ModuleEntityID',$module_entity_id);
        $this->db->where_in('Status',array('ACTIVE','APPROVED'));
        $query = $this->db->get();
        if($query->num_rows())
        {
            return $query->row_array();
        } 
        else 
        {
            return array('TotalRecords'=>'0','TotalRateValue'=>'0');
        }
    }

    /**
     * [get_parameter_summary - get summary of parameters value of particular entity]   
     * @param  $module_id
     * @param  $module_entity_guid
     * @return [Parameter List and Average Value]
     */
    public function get_parameter_summary($module_id, $module_entity_guid)
    {
        $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);

        $this->db->select('RP.RatingParameterID,RP.ParameterKey,RP.ParameterName,ROUND(AVG(RPV.RateValue),1) as RateValue',false);
        $this->db->from(RATINGPARAMETER.' RP');
        $this->db->join(RATINGPARAMETERVALUE.' RPV','RP.RatingParameterID=RPV.RatingParameterID','left');
        $this->db->join(RATINGS.' R','R.RatingID=RPV.RatingID','left');
        $this->db->where('R.ModuleID',$module_id);
        $this->db->where('R.ModuleEntityID',$module_entity_id);
        $this->db->group_by('RP.RatingParameterID');
        $query = $this->db->get();
        $data = array();
        if($query->num_rows())
        {
            $data = $query->result_array();
        }
        return $data;
    }

    /**
     * [get_star_count - get no of star counts in rating]
     * @param  $module_id
     * @param  $module_entity_guid
     * @return [OneStarRating = 3,TwoStarRating = 2, ...]
     */
    public function get_star_count($module_id,$module_entity_guid)
    {
        $module_entity_id = get_detail_by_guid($module_entity_guid,$module_id);

        $this->db->select("COUNT(RatingID) as TotalRecords, 
                CASE WHEN RateValue BETWEEN 0 AND 1.5   THEN 'OneStarRating'
                WHEN RateValue BETWEEN 1.6 AND 2.5 THEN 'TwoStarRating'
                WHEN RateValue BETWEEN 2.6 AND 3.5 THEN 'ThreeStarRating'
                WHEN RateValue BETWEEN 3.6 AND 4.5 THEN 'FourStarRating'
                WHEN RateValue BETWEEN 4.6 AND 5.5 THEN 'FiveStarRating'
                END AS RatedValue",false);
        $this->db->from(RATINGS);
        $this->db->where('ModuleID',$module_id);
        $this->db->where('ModuleEntityID',$module_entity_id);
        $this->db->where_in('Status',array('ACTIVE','APPROVED'));
        $this->db->group_by('RatedValue');
        $query = $this->db->get();
        $this->db->_protect_identifiers = TRUE;
        //echo $this->db->last_query();
        $data = array('OneStarRating'=>0,'TwoStarRating'=>0,'ThreeStarRating'=>0,'FourStarRating'=>0,'FiveStarRating'=>0);
        if($query->num_rows())
        {
            $result = $query->result_array();
            foreach ($result as $value) 
            {
                $data[$value['RatedValue']] = $value['TotalRecords'];
            }
        }
        return $data;
    }

    /**
     * [check_permission - check if user have permission to submit rating or not]
     * @param  $user_id
     * @param  $module_id
     * @param  $module_entity_id
     * @param  $rating_guid
     * @return Boolean
     */
    public function check_permission($user_id, $module_id, $module_entity_id, $rating_guid='')
    {
        if($rating_guid)
        {
            $this->db->select('RatingID');
            $this->db->from(RATINGS);
            $this->db->where('RatingGUID',$rating_guid);
            $this->db->where('UserID',$user_id);
            $query = $this->db->get();
            if($query->num_rows())
            {
                return true;
            } 
            else 
            {
                return false;
            }
        } 
        else 
        {
            switch ($module_id) 
            {
                case 1:
                    return true;
                break;
                
                case 3:
                    $this->load->model('group/group_model');
                    $status = $this->group_model->check_membership($module_entity_id, $user_id);
                    if($status)
                    {
                        return true;
                    } else 
                    {
                        return false;
                    }
                break;
                
                case 14:
                    $this->load->model('events/event_model');
                    $presence = $this->event_model->get_user_presence($user_id, $module_entity_id);
                    if($presence == 'ARRIVED' || $presence == 'ATTENDING')
                    {
                        return true;
                    } 
                    else 
                    {
                        return false;
                    }
                break;
                
                case 18:
                    $this->load->model('pages/page_model');
                    $status = $this->page_model->check_member($module_entity_id, $user_id);
                    if($status)
                    {
                        return true;
                    } 
                    else 
                    {
                        return false;
                    }
                break;
                
                default:
                    return false;
                break;
            }
        }
    }
    /**
     * [entity_list used to get the entity list, which are used for post as drop down]
     * @param  [int] $user_id          [User id]
     * @param  [int] $module_id        [Module id]
     * @param  [int] $module_entity_id [Module entity id]
     * @return [array]                   [entity list]
     */
    public function entity_list($user_id, $module_id, $module_entity_id)
    {
        
        $module_id = $this->db->escape_str($module_id);
        $module_entity_id = $this->db->escape_str($module_entity_id);
        
        $entity = array();
        $this->db->select('P.PageGUID as ModuleEntityGUID,P.Title as Name,  if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture, "18" as ModuleID',false);
        $this->db->from(PAGES.' P');
        $this->db->join(CATEGORYMASTER.' CM','CM.CategoryID=P.CategoryID','left');
        $this->db->where('P.UserID',$user_id);
        $this->db->where('P.StatusID',2);
        $this->db->where("(SELECT RatingID FROM ".RATINGS." WHERE PostAsModuleID='18' AND Status !='DELETED' AND PostAsModuleEntityID=P.PageID AND ModuleID=".$module_id." AND ModuleEntityID=".$module_entity_id.") is NULL",NULL,FALSE);
        if($module_id == 18)
        {
            $this->db->where('PageID!='.$module_entity_id,NULL,FALSE);
        }
        $query = $this->db->get();
        if($query->num_rows())
        {
            $page_list = $query->result_array();
        }

        $this->db->select('UserGUID as ModuleEntityGUID,CONCAT(FirstName," ",LastName) as Name, IF(ProfilePicture="","user_default.jpg",ProfilePicture) as ProfilePicture, "3" as ModuleID',false);
        $this->db->from(USERS);
        $this->db->where('UserID',$user_id);
        $this->db->where("(SELECT RatingID FROM ".RATINGS." WHERE UserID='".$user_id."' AND Status !='DELETED' AND ModuleID=".$module_id." AND ModuleEntityID=".$module_entity_id." AND PostAsModuleID is NULL AND PostAsModuleEntityID is NULL) is NULL",NULL,FALSE);
        $query = $this->db->get();
        if($query->num_rows())
        {
            $user_detail = $query->row_array();
        }

        if(isset($page_list))
        {
            $entity = $page_list;
        }
        if(isset($user_detail))
        {
            $entity[] = $user_detail;
        }
        return $entity;
    }

    /**
     * [is_rated check user already rated an entity or not]
     * @param  [int]  $user_id                  [User ID]
     * @param  [int]  $module_id                [Module ID]
     * @param  [int]  $module_entity_id         [Module Entity ID]
     * @param  [int]  $post_as_module_id        [Post as Module ID]
     * @param  [int]  $post_as_module_entity_id [Post as Module Entity ID]
     * @return boolean                           [description]
     */
    public function is_rated($user_id, $module_id, $module_entity_id, $post_as_module_id, $post_as_module_entity_id) 
    {

        $this->db->where('ModuleID',$module_id);
        $this->db->where('ModuleEntityID',$module_entity_id);
         $this->db->where('Status !=','DELETED');
        if(!empty($post_as_module_id) || !empty($post_as_module_entity_id))
        {
            $this->db->where('PostAsModuleID',$post_as_module_id);
            $this->db->where('PostAsModuleEntityID',$post_as_module_entity_id);
        } 
        else 
        {
            $this->db->where('UserID',$user_id);
            $this->db->where('PostAsModuleID is NULL',NULL,FALSE);
            $this->db->where('PostAsModuleEntityID is NULL',NULL,FALSE);
        }
        $query = $this->db->get(RATINGS);
        if($query->num_rows())
        {
            return true;
        } 
        else 
        {
            return false;
        }
    }

    public function delete($user_id, $rating_guid)
    {
        $this->db->set('Status','DELETED');
        $this->db->where('RatingGUID',$rating_guid);
        $this->db->update(RATINGS);
    }
    
    function get_flag_data($rating_id,$user_id)
    {
        $query = $this->db->get_where(FLAG,array('EntityID'=>$rating_id,'EntityType'=>'RATING','UserID'=>$user_id));
        //echo $this->db->last_query();
        if($query->num_rows())
        {
            return $query->row_array();
        }
    }
}
