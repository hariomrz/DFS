<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Rules_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }

    function add_rule($name,$city_ids,$registration_from_date,$registration_to_date,$gender,$age_group_id,$interset_ids=NULL,$user_ids=NULL,$tag_ids=NULL, $location, $specific_users, $activity_rule_id='')
    {
        
        $insert_data['Name']=$name;
        $insert_data['Gender']=$gender;
        $insert_data['AgeGroupID']=$age_group_id;
        /*$insert_data['RegistrationFromDate']=$registration_from_date;
        $insert_data['RegistrationToDate']=$registration_to_date;*/
        $insert_data['CityIDs']=$city_ids;
        $insert_data['IntersetIDs']=$interset_ids;
        $insert_data['UserIDs']=$user_ids;
        $insert_data['TagIDs']=$tag_ids;
        $insert_data['ModifiedDate']=get_current_date('%Y-%m-%d %H:%i:%s');

        if($activity_rule_id)
        {
           $insert_data['LastCalculationDate'] = get_current_date('%Y-%m-%d 00:00:00', 5); // Get 5 days before date
           $this->db->where('ActivityRuleID',$activity_rule_id); 
           $this->db->update(DEFAULTACTIVITYRULE,$insert_data); 
        }
        else
        {
            $display_order = 0;
            $get_display_order = get_data('MAX(DisplayOrder) as DisplayOrder', DEFAULTACTIVITYRULE,array(), '1', '');
            if ($get_display_order)
            {
                $display_order = $get_display_order->DisplayOrder;
                
            }
            $insert_data['DisplayOrder']=$display_order+1;        
            $insert_data['CreatedDate']=get_current_date('%Y-%m-%d %H:%i:%s');
            
            //Set initial values
            $insert_data = $this->setInitialRuleValues($insert_data, $location, $specific_users, $interset_ids);
            
            $this->db->insert(DEFAULTACTIVITYRULE,$insert_data);
            $activity_rule_id = $this->db->insert_id();
        }
        return $activity_rule_id;
    }
    
    function setInitialRuleValues($insert_data, $location, $specific_users, $interset_ids) {
        $basic_set = array(
            'Gender' => $insert_data['Gender'],
            'AgeGroupID' => $insert_data['AgeGroupID'],
            'Location' => $location
        );
        
        $tags = array();
        if (!empty($specific_users)) {
            foreach ($specific_users as $specific_user) {
                if ($specific_user['EntityType'] == 'Tag') {
                    $tags[] = array(
                        'TagID' => $specific_user['EntityID'],
                        'Name' => !empty($specific_user['EntityName']) ? $specific_user['EntityName'] : ''
                    );
                }
            }
        }
        
        $interest_arr = array();
        if(!empty($interset_ids)) {
            $interset_ids = explode(',', $interset_ids);
            foreach ($interset_ids as $interset_id) {
                $interest_arr[] = array(
                    'ID' => $interset_id,
                    'Name' => '',
                );
            }
        }
        
        // Populate initial data for posts
        $post_with_tags = $basic_set;
        $post_with_tags['Tag'] = $tags;
        
        $popular_post = $basic_set;
        $popular_post['Tag'] = $tags;
        $popular_post['Interests'] = $interest_arr;
        
        $insert_data['PostWithTags'] = json_encode($post_with_tags);
        $insert_data['PopularPost'] = json_encode($popular_post);
        //$insert_data['PostFromUser']=$user_ids;
        //$insert_data['PostFromGroup']=$group_ids;
        //$insert_data['CustomizePostIDs']=$customize_post_ids;
        $insert_data['AllPublicPost'] = 1;
        $insert_data['PublicPost'] = json_encode($basic_set);
        
        
        // Populate initial data for profiles
        $insert_data['ProfilesWithTags'] = json_encode($post_with_tags);
        $insert_data['PopularProfiles'] = json_encode($popular_post);
        //$update_array['CustomizeProfiles'] = $customize_profiles;
        
        $basic_set['IsTranding'] = 1;
        $insert_data['TrendingTags'] = json_encode($basic_set);
        
        return $insert_data;
    }
    
    function get_rule($sort_by,$order_by,$page_no,$page_size)
    {
        $select_fields = 'DAR.UserIDs,AG.Name as AgeGroupName,DAR.CreatedDate,DAR.ActivityRuleID,DAR.Name,IF(DAR.Gender=1,'
                . '"Male",IF(DAR.Gender=2,"Female","")) as Gender,DAR.AgeGroupID,IFNULL(CityIDs,"")as CityIDs,'
                . 'IFNULL(IntersetIDs,"")as IntersetIDs,IsEditable, TagIDs';
        
        $result=array();
        $result['Data'] = array();
        $result['TotalRecords'] = '';
        $this->db->select($select_fields, false);
        $this->db->from(DEFAULTACTIVITYRULE.' as DAR');
        $this->db->join(AGEGROUPS.' AG','AG.AgeGroupID=DAR.AgeGroupID','left');
        $this->db->where('StatusID',2);
        $this->db->order_by('DAR.'.$sort_by,$order_by);
        if ($page_size)
        {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }
        if($page_no=='' || $page_no==1)
        {
            $tempdb = clone $this->db;
            $temp_q = $tempdb->get();
            $result['TotalRecords'] = $temp_q->num_rows(); 
        }
        $query = $this->db->get();
        $final_array=array();
        if($query->num_rows())
        {
            foreach($query->result_array() as $row)
            {
                $row['Location']=array();
                $row['InterestData']=array();
                $row['UserData']=array();
                $row['CreatedDate']=get_current_date("%d %M %Y",0,0,strtotime($row['CreatedDate']));
                if(!empty($row['CityIDs']))
                {
                    $city_ids= explode(',', $row['CityIDs']);
                    $city_data=array();
                    foreach($city_ids as $city_id)
                    {
                        $city_data[]=$this->get_location_by_city($city_id);
                    }
                    $row['Location']=$city_data;
                }
                if(!empty($row['IntersetIDs']))
                {
                    $row['InterestData']=$this->get_Interset($row['IntersetIDs']);
                }
//                if(!empty($row['UserIDs']))
//                {
//                    $row['UserData']=$this->get_User($row['UserIDs']);
//                }
                
                if(!empty($row['UserIDs']) || !empty($row['TagIDs']))
                {
                    $user_ids = explode(',', $row['UserIDs']);
                    $tag_ids = explode(',', $row['TagIDs']);
                    $row['UserData']=$this->get_users('', false, $user_ids, $tag_ids);
                }
                
                unset($row['CityIDs']);
                unset($row['IntersetIDs']);
                $final_array[]=$row;
            }
           
        }
        
        $result['Data']=$final_array;
        return $result;
    }
    
    function get_city($city_ids)
    {
        $result=array();
        $this->db->select('CityID,Name');
        $this->db->from(CITIES);
        $this->db->where_in('CityID',$city_ids);
        $query = $this->db->get();
        if($query->num_rows())
        {
           $result=$query->result_array(); 
        }
       return  $result;
    }
    
    function get_Interset($interset_ids)
    {
        $result=array();
        $this->db->select('CategoryID,Name');
        $this->db->from(CATEGORYMASTER);
        $this->db->where_in('CategoryID', explode(',', $interset_ids));
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if($query->num_rows())
        {
           $result=$query->result_array(); 
        }
       return  $result;
    }

    function get_User($user_ids)
    {
        $result = array();
        if($user_ids)
        {
            $user_ids = explode(',', $user_ids);
            $this->load->model('admin/users_model');
            foreach($user_ids as $user)
            {
                $result[] = $this->users_model->getUserDetails($user);
            }
        }
        return $result;
    }
    
    function get_location_by_city($city_id) {
		$this->db->select('IFNULL(S.Name,"") as StateName', FALSE);
		$this->db->select('IFNULL(S.ShortCode,"") as StateCode', FALSE);
		$this->db->select('IFNULL(CT.Name,"") as CityName', FALSE);
		$this->db->select('IFNULL(C.CountryName,"") as CountryName', FALSE);
		$this->db->select('IFNULL(C.CountryCode,"") as CountryCode', FALSE);
		$this->db->from(CITIES . ' CT');
		$this->db->join(STATES . ' S', 'CT.StateID = S.StateID', 'left');
                $this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = S.CountryID', 'left');
		$this->db->where('CT.CityID', $city_id);
		$query = $this->db->get();
		//echo $this->db->last_query();
		if ($query->num_rows()) {
			$row = $query->row();
			$city = trim($row->CityName);
			$State = trim($row->StateName);
			$StateCode = trim($row->StateCode);
			$Country = trim($row->CountryName);
			$CountryCode = trim($row->CountryCode);
			$Location = '';
			if (!empty($city) && $city != null) {
				$city = ucfirst(strtolower($city));
				$Location .= $city . ', ';
			}
			if (!empty($State) && $State != null) {
				$Location .= $State . ', ';
			} else if (!empty($StateCode) && $StateCode != null) {
				$StateCode = strtoupper($StateCode);
				$Location .= $StateCode . ', ';
			}
			if (!empty($Country) && $Country != null) {
				$Country = ucfirst(strtolower($Country));
				$Location .= $Country . ', ';
			}
			if ($Location) {
				$Location = substr($Location, 0, -2);
				if ($Location == '-') {
					$Location = '';
				}
			}
			return array('City' => $city, 'State' => $State, 'Country' => $Country, 'Location' => $Location, 'StateCode' => $StateCode, 'CountryCode' => $CountryCode);
		}
	}
    
    function rule_welcome($activity_rule_id,$welcome)
    {        
        $this->db->where('ActivityRuleID',$activity_rule_id);
        $this->db->update(DEFAULTACTIVITYRULE,array('Welcome'=>$welcome));
    }

    /**
     *  Function Name: rule_question
     * Description: Update Rule Questions
     * @param int $activity_rule_id
     * @param array $welcome_questions     
     * @return 
     */
    function rule_question($activity_rule_id,$welcome_questions)
    {
        $existing_questions = array();
        $display_order = 1;
        //get already assigned Questions
        $this->db->select('RuleQuestionID,ActivityID');
        $this->db->where('ActivityRuleID',$activity_rule_id);
        $this->db->order_by('RuleQuestionID ASC');
        $query = $this->db->get(RULEQUESIONS);
        if($query->num_rows())
        {
            foreach ($query->result_array() as $rule_question) 
            {
                # check if already exists
                if(!in_array($rule_question['ActivityID'], $welcome_questions))
                {
                    $this->db->where('ActivityID',$rule_question['ActivityID']);
                    $this->db->where('ActivityRuleID',$activity_rule_id);
                    $this->db->delete(RULEQUESIONS);
                }
                else
                {
                    $this->db->where('RuleQuestionID',$rule_question['RuleQuestionID']);
                    $this->db->set('DisplayOrder', $display_order++);
                    $this->db->update(RULEQUESIONS);
                    $existing_questions[] = $rule_question['ActivityID'];
                }
            }
        }
        
        //Add New Questions
        foreach ($welcome_questions as $new_question) 
        {   
            # check for existing entries
            if(!in_array($new_question, $existing_questions))
            {
                $insert_data = array(
                    'ActivityID'=>$new_question,
                    'ActivityRuleID'=>$activity_rule_id,
                    'DisplayOrder'=> $display_order++,
                    'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s')
                    );
                $this->db->insert(RULEQUESIONS,$insert_data);
            }
        }
    }

    /**
     *  Function Name: get_welcome_questions
     * Description: get details of welcome Questions
     * @param int $activity_rule_id 
     * @return Array
     */
    function get_welcome_questions($activity_rule_id,$user_id)
    {
        $result = $activity = array();
        $this->load->model(array('activity/activity_model','events/event_model','group/group_model','pages/page_model','ratings/rating_model','polls/polls_model','tag/tag_model'));
        $this->load->model('category/category_model');
        $this->db->select('A.ActivityID,A.ActivityGUID,A.PostContent,A.ActivityTypeID,A.UserID,A.ModuleID,A.ModuleEntityID,A.NoOfLikes,A.NoOfComments,A.Privacy,A.IsCommentable,A.IsMediaExist,A.ParentActivityID,A.ModuleEntityOwner,A.StatusID,A.CreatedDate,A.IsFileExists,A.PostAsModuleID,A.Params,A.PostAsModuleEntityID,A.NoOfViews,A.PostType,A.PostTitle,ATY.ViewTemplate, ATY.Template,U.UserID,U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->join(RULEQUESIONS . ' RQ', 'RQ.ActivityID=A.ActivityID', 'left');
        $this->db->where('RQ.ActivityRuleID',$activity_rule_id);
        $this->db->order_by('RQ.DisplayOrder');
        $query = $this->db->get(ACTIVITY.' A');

        if($query->num_rows())
        {   $res_arr = $query->result_array();
            foreach ($res_arr as $question) 
            {
                $result = $question;
                $result['PostContent'] = $this->activity_model->parse_tag($result['PostContent']);
                $result['PostTitle'] = $this->activity_model->parse_tag($result['PostTitle'],$question['ActivityID']);  
                $result['Album'] = $this->activity_model->get_albums($question['ActivityID'], $question['UserID']);
                $result['EntityTagged'] = $this->activity_model->get_tagged_entity($question['ActivityID']);
                $result['Links'] = $this->activity_model->get_activity_links($question['ActivityID']);
                $result['Message'] = $question['Template'];
                $result['IsOwner'] = 0;
                $result['CanRemove'] = 0;
                if($question['UserID'] == $user_id)
                {
                    $result['IsOwner'] = 0;
                }
                if ($user_id == $question['ModuleEntityID'] && $question['ModuleID'] == 3) {
                    $result['IsOwner'] = 1;
                    $result['CanRemove'] = 1;
                }
                if($result['IsFileExists'])
                {
                    $result['Files'] = $this->activity_model->get_activity_files($result['ActivityID']);
                }
                $result['EntityName'] = '';
                $result['EntityProfilePicture'] = '';
                $result['UserName'] = $result['FirstName'] . ' ' . $result['LastName'];
                $result['UserProfileURL']     = get_entity_url($result['UserID'], 'User', 1);
                $result['EntityType'] = '';                
                if($result['ModuleID'] == 1)
                {
                    $group_details = get_detail_by_id($question['ModuleEntityID'],1,'Type, GroupGUID, GroupName,if(GroupImage!="",GroupImage,"group-no-img.jpg") as ProfilePicture',2);
                    $result['EntityProfileURL']   = $result['ModuleEntityID'];
                    $result['EntityGUID']         = $group_details['GroupGUID'];
                    $result['EntityName']         = $group_details['GroupName'];
                    $result['EntityProfilePicture'] = $group_details['ProfilePicture'];
                    if ($group_details['Type'] == 'INFORMAL')
                    {
                        $result['EntityMembersCount'] = $this->group_model->members($result['ModuleEntityID'], $question['UserID'], TRUE);
                        $result['EntityMembers'] = $this->group_model->members($result['ModuleEntityID'], $question['UserID']);
                    }
                }
                elseif($result['ModuleID']==14)
                {                  
                    $entity = get_detail_by_id($result['ModuleEntityID'], $result['ModuleID'], "EventGUID, Title, ProfileImageID", 2);
                    if ($entity)
                    {
                        $result['EntityName'] = $entity['Title'];
                        $result['EntityProfilePicture'] = $entity['ProfileImageID'];
                        $result['EntityGUID'] = $entity['EventGUID'];
                    }
                    $result['EntityProfileURL'] = get_guid_by_id($result['ModuleEntityID'], 14);                                   
                }
                elseif($result['ModuleID']==18)
                {
                    $entity = get_detail_by_id($result['ModuleEntityID'], $result['ModuleID'], "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                    if ($entity)
                    {
                        $result['EntityName'] = $entity['Title'];
                        $result['EntityProfilePicture'] = $entity['ProfilePicture'];
                        $result['EntityProfileURL'] = $entity['PageURL'];
                        $result['EntityGUID'] = $entity['PageGUID'];
                        
                        $category_name=$this->category_model->get_category_by_id($entity['CategoryID']);
                        $category_icon = $category_name['Icon'];
                        if ($entity['ProfilePicture'] == '')
                        {
                            $result['EntityProfilePicture'] = $category_icon;
                        }                        
                        
                        if ($result['PostAsModuleID'] == 18 )
                        {
                            $PostAs=$this->page_model->get_page_detail_cache($result['PostAsModuleEntityID']);
                            $result['ModuleEntityOwner'] = 1;
                            $result['UserName'] = $PostAs['Title'];
                            $result['UserProfilePicture'] = $PostAs['ProfilePicture'];
                            $result['UserProfileURL'] = $PostAs['PageURL'];
                            $result['UserGUID'] = $PostAs['PageGUID'];
                        }
                        if ($result['PostAsModuleEntityID']!=$result['ModuleEntityID']  && $result['ActivityTypeID'] == 12)

                        {
                            $result['Message'] = $result['Message'] . ' posted in {{Entity}}';
                        }
                    }                                        
                }
                elseif($result['ModuleID'] == 34)
                {
                    $entity = get_detail_by_id($result['ModuleEntityID'], $result['ModuleID'], "ForumCategoryGUID, Name, MediaID, URL", 2);
                    if ($entity)
                    {
                        $result['EntityName'] = $entity['Name'];
                        $result['EntityProfilePicture'] = $entity['MediaID'];
                        $result['EntityGUID'] = $entity['ForumCategoryGUID'];
                        $result['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                    }  
                    if ($result['ActivityTypeID'] == 26)
                    {
                        $result['Message'] = $result['Message'] . ' posted in {{Entity}}';
                    }                                     
                }
                elseif($result['ModuleID'] == 3)
                {
                    $result['EntityName'] = $result['UserName'];
                    $result['EntityProfilePicture'] = $result['ProfilePicture'];
                    $result['EntityGUID'] = $result['UserGUID'];

                    $entity = get_detail_by_id($result['ModuleEntityID'], $result['ModuleID'], 'FirstName,LastName, UserGUID', 2);
                    if ($entity)
                    {
                        $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                        $result['EntityName'] = $entity['EntityName'];
                        $result['EntityGUID'] = $entity['UserGUID'];
                    }

                    $result['EntityProfileURL'] = get_entity_url($result['ModuleEntityID'], 'User', 1);                    
                }
                //ratings data
                if ($result['ActivityTypeID'] == 16 || $result['ActivityTypeID'] == 17)
                {
                    $params = json_decode($result['Params']);
                    $result['RatingData'] = $this->rating_model->get_rating_by_id($params->RatingID, $result['UserID']);                   
                } 
                else if ($result['ActivityTypeID'] == 25)
                {   //Poll Data onhold
                    $params = json_decode($result['Params']);
                    $result['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, 3, $user_id);                    
                }
                //Share Activity Data
                if ($result['ActivityTypeID'] == 9 || $result['ActivityTypeID'] == 10 || $result['ActivityTypeID'] == 14 || $result['ActivityTypeID'] == 15)
                {
                    $originalActivity = $this->activity_model->get_activity_details($result['ParentActivityID'], $result['ActivityTypeID']);
                    $result['ActivityOwner'] = $this->user_model->getUserName($originalActivity['UserID'], $originalActivity['ModuleID'], $originalActivity['ModuleEntityID']);
                    $result['ActivityOwnerLink'] = $result['ActivityOwner']['ProfileURL'];
                    $result['OriginalPostType'] = $originalActivity['PostType'];
                    $result['ActivityOwner'] = $result['ActivityOwner']['FirstName'] . ' ' . $result['ActivityOwner']['LastName'];
                    $result['Album'] = $originalActivity['Album'];
                    $result['Files'] = $originalActivity['Files'];
                    $result['SharePostContent'] = $result['PostContent'];
                    $result['PostContent'] = $originalActivity['PostContent'];
                    $result['SharedActivityModule'] = $originalActivity['SharedActivityModule'];
                    $result['SharedEntityGUID'] = $originalActivity['SharedEntityGUID'];

                    /*added by gautam*/
                    if ($this->IsApp == 1) 
                    { /*For Mobile */
                        /*Get tagged users*/
                        $result['SharePostContent'] = $this->parse_tag($result['SharePostContent']);
                        $result['ShareEntityTagged'] = $this->get_tagged_entity($result['ParentActivityID']);
                        $result['Links'] = $this->get_activity_links($result['ParentActivityID']);
                    }

                    if ($result['ActivityTypeID'] == 10 || $result['ActivityTypeID'] == 15)
                    {
                        if($originalActivity['ModuleID'] == '1' && $originalActivity['PostType']=='7')
                        {
                            //$activity['Message'] = str_replace("{{OBJECT}}", $activity['ActivityOwner'], $activity['Message']);
                            $result['Message'] = str_replace("{{OBJECT}}", "{{ACTIVITYOWNER}}", $result['Message']);
                        }
                        else
                        {
                            if ($originalActivity['UserID'] == $result['UserID'])
                            {
                                $result['Message'] = str_replace("{{OBJECT}}'s", $this->notification_model->get_gender($originalActivity['UserID']), $result['Message']);
                            } else
                            {
                                if ($originalActivity['ParentActivityTypeID'] == '11' || $originalActivity['ParentActivityTypeID'] == '7' || $originalActivity['ParentActivityTypeID'] == '26')
                                {
                                    $u_d = get_detail_by_id($originalActivity['UserID'], 3, 'FirstName,LastName', 2);
                                    if ($u_d)
                                    {
                                        $result['Message'] = str_replace("{{OBJECT}}", $u_d['FirstName'] . ' ' . $u_d['LastName'], $result['Message']);
                                    }
                                }
                            }
                        }
                    }

                    if ($result['ActivityTypeID'] == '14' || $result['ActivityTypeID'] == '15')
                    {
                        $result['Album'] = $this->get_albums($result['ParentActivityID'], $result['UserID'], '', 'Media');
                        if (!empty($result['Album']['AlbumType']))
                        {
                            $result['EntityType'] = ucfirst(strtolower($result['Album']['AlbumType']));
                        } else
                        {
                            $result['EntityType'] = 'Media';
                        }
                    } else
                    {
                        $result['EntityType'] = 'Post';
                        if ($originalActivity['ParentActivityTypeID'] == 5 || $originalActivity['ParentActivityTypeID'] == 6)
                        {
                            $result['EntityType'] = 'Album';
                        }
                        if (!empty($originalActivity['Album']))
                        {
                            $result['EntityType'] = 'Media';
                        }
                        $result['OriginalActivityGUID'] = $originalActivity['ActivityGUID'];
                        $result['OriginalActivityType'] = $originalActivity['ActivityType'];
                        $result['OriginalActivityFirstName'] = $originalActivity['ActivityOwnerFirstName'];
                        $result['OriginalActivityLastName'] = $originalActivity['ActivityOwnerLastName'];
                        $result['OriginalActivityUserGUID'] = $originalActivity['ActivityOwnerUserGUID'];
                        $result['OriginalActivityProfileURL'] = $originalActivity['ActivityOwnerProfileURL'];
                    }
                    if (isset($originalActivity['ParentActivityTypeID']) && $originalActivity['ParentActivityTypeID'] == 25)
                    {
                        $params = json_decode($originalActivity['Params']);
                        $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, 3, $user_id);
                    }                    
                }
                if ($result['ActivityTypeID'] == 1 || $result['ActivityTypeID'] == 7 || $result['ActivityTypeID'] == 11 || $result['ActivityTypeID'] == 12)
                {
                    $result['PostContent'] = str_replace('­', '', $result['PostContent']);
                    if (empty($result['PostContent']))
                    {
                        $pcnt = $this->get_photos_count($result['ActivityID']);
                        if (isset($pcnt['Media']))
                        {
                            $result['Message'] .= ' added ' . $pcnt['MediaCount'] . ' new ' . $pcnt['Media'];
                        }
                    }
                }
                $result['Comments'] = array();
                if($result['NoOfComments'] > 0)
                {
                    if($result['IsOwner'])
                    {
                        $result['CanRemove'] = 1;
                    }
                    $result['Comments'] = $this->activity_model->getActivityComments('Activity', $result['ActivityID'], '1', COMMENTPAGESIZE, $user_id, $result['CanRemove'], 2, TRUE, array(), FALSE,'',$result['PostAsModuleID'],$result['PostAsModuleEntityID']);
                }
                $result['EntityTags'] = $this->tag_model->get_entity_tags("", 1, 30, 1, 'ACTIVITY', $result['ActivityID'], $user_id);
                $result['ActivityURL'] = get_single_post_url($result);
                $activity[]=$result;   
            }
        }       
        return $activity;
    }
  
    function rule_posts($activity_rule_id,$post_with_tags,$popular_post,$specific_users,$customize_post_ids, $all_public_post, $public_post)
    {
        $user_ids=array();
        $group_ids=array();
        $user_tag_ids = [];
        if(!empty($specific_users))
        {
            foreach($specific_users as $specific_user)
            {
                if($specific_user['EntityType']=='User')
                {
                    $user_ids[]=$specific_user['EntityID'];
                }
                elseif($specific_user['EntityType']=='Group')
                {
                    $group_ids[]=$specific_user['EntityID'];
                }
                elseif($specific_user['EntityType']=='Tag')
                {
                    $user_tag_ids[]=$specific_user['EntityID'];
                }
            }
        }
        $user_tag_ids = implode(',', $user_tag_ids);
        if(!empty($user_ids))
        {
            $user_ids= implode(',', $user_ids);
        }
        else
        {
            $user_ids='';
        }
        if(!empty($user_ids))
        {
            $group_ids= implode(',', $group_ids);
        }
        else
        {
            $group_ids='';
        }

        if(!empty($post_with_tags) && isset($post_with_tags['Location']))
        {
           $temp_post_with_tags =array();
           foreach($post_with_tags['Location'] as $item)
            {
               $LocationData = update_location($item); 
               $item['CityID']= $LocationData['CityID'];
               $temp_post_with_tags[]=$item;
            }
            $post_with_tags['Location']= $temp_post_with_tags;
        }
        $post_with_tags= json_encode($post_with_tags);

        if(!empty($popular_post) && isset($popular_post['Location']))
        {
           $temp_popular_post =array();
           foreach($popular_post['Location'] as $item)
            {
               $LocationData = update_location($item); 
               $item['CityID']= $LocationData['CityID'];
               $temp_popular_post[]=$item;
            }
            $popular_post['Location']=$temp_popular_post;
        }
        $popular_post=json_encode($popular_post);
        
        if(!empty($public_post) && isset($public_post['Location']))
        {
           $temp_public_post =array();
           foreach($public_post['Location'] as $item)
            {
               $LocationData = update_location($item); 
               $item['CityID']= $LocationData['CityID'];
               $temp_public_post[]=$item;
            }
            $public_post['Location']=$temp_public_post;
        }
        $public_post=json_encode($public_post);

        $update_array['PostWithTags']=$post_with_tags;
        $update_array['PopularPost']=$popular_post;
        $update_array['PostFromUser']=$user_ids;
        $update_array['PostFromGroup']=$group_ids;
        $update_array['PostFromUserTag']=$user_tag_ids;
        $update_array['CustomizePostIDs']=($customize_post_ids) ? implode(',',$customize_post_ids) : '' ;
        $update_array['AllPublicPost']=$all_public_post;
        $update_array['PublicPost']=$public_post;
        $update_array['LastCalculationDate'] = get_current_date('%Y-%m-%d 00:00:00', 5); // Get 5 days before date
        $this->db->where('ActivityRuleID',$activity_rule_id);
        $this->db->update(DEFAULTACTIVITYRULE,$update_array);
        
        $this->on_rule_update($activity_rule_id);
  }
  
    function rule_profile($activity_rule_id,$profiles_with_tags,$popular_profiles,$customize_profiles)
    {
        if(!empty($profiles_with_tags) && isset($profiles_with_tags['Location'])) {
            $temp_profiles_with_tags = array();
            foreach ($profiles_with_tags['Location'] as $item) {
                $LocationData = update_location($item);
                $item['CityID'] = $LocationData['CityID'];
                $temp_profiles_with_tags[] = $item;
            }
            $profiles_with_tags['Location'] = $temp_profiles_with_tags;
        }
        $profiles_with_tags = json_encode($profiles_with_tags);

        if (!empty($popular_profiles) && isset($popular_profiles['Location'])) {
            $temp_popular_profiles = array();
            foreach ($popular_profiles['Location'] as $item) {
                $LocationData = update_location($item);
                $item['CityID'] = $LocationData['CityID'];
                $temp_popular_profiles[] = $item;
            }
            $popular_profiles['Location'] = $temp_popular_profiles;
        }
        $popular_profiles = json_encode($popular_profiles);

        $update_array['ProfilesWithTags'] = $profiles_with_tags;
        $update_array['PopularProfiles'] = $popular_profiles;
        $update_array['CustomizeProfiles'] = ($customize_profiles) ? implode(',',$customize_profiles) : '' ;
        $update_array['LastCalculationDate'] = get_current_date('%Y-%m-%d 00:00:00', 5); // Get 5 days before date
        $this->db->where('ActivityRuleID', $activity_rule_id);
        $this->db->update(DEFAULTACTIVITYRULE,$update_array);
        
        $this->on_rule_update($activity_rule_id);
  }
  
    function rule_tags($activity_rule_id,$trending_tags, $customize_tags)
    {
  
        if(!empty($customize_tags) && isset($customize_tags['Location'])) {
           $temp_popular_tags =array();
           foreach($customize_tags['Location'] as $item)
            {
               $LocationData = update_location($item); 
               $item['CityID']= $LocationData['CityID'];
               $temp_popular_tags[]=$item;
            }
            $customize_tags['Location']= $temp_popular_tags;
        }
        $customize_tags= json_encode($customize_tags);
        
        // Process location for tranding tags
        if(!empty($trending_tags) && isset($trending_tags['Location'])) {
           $temp_tags =array();
           foreach($trending_tags['Location'] as $item)
            {
               $LocationData = update_location($item); 
               $item['CityID']= $LocationData['CityID'];
               $temp_tags[]=$item;
            }
            $trending_tags['Location']= $temp_tags;
        }
        $trending_tags= json_encode($trending_tags);
                
        $update_array['TrendingTags']=$trending_tags;
        $update_array['CustomizeTags']=$customize_tags;
        $update_array['LastCalculationDate'] = get_current_date('%Y-%m-%d 00:00:00', 5); // Get 5 days before date
        //$update_array['PopularTags']=$customize_tags;
        $this->db->where('ActivityRuleID',$activity_rule_id);
        $this->db->update(DEFAULTACTIVITYRULE,$update_array);
        
        $this->on_rule_update($activity_rule_id);
  }

    /**
     *  Function Name: change_order
     * Description: update rule display order
     * @param type $order_data
     * @return boolean
     */
    function change_order($order_data) 
    {
        $orderValue = array();
        foreach ($order_data as $items) {
            $itemArray = array();
            $itemArray['ActivityRuleID'] = $items['ActivityRuleID'];
            $itemArray['DisplayOrder'] = $items['DisplayOrder'];
            $orderValue[] = $itemArray;
        }

        $this->db->update_batch(DEFAULTACTIVITYRULE, $orderValue, 'ActivityRuleID');
        return true;
    }

    function get_age_group()
    {
        $data = array();
        $this->db->select('AgeGroupID,Name');
        $this->db->from(AGEGROUPS);
        $query = $this->db->get();
        if($query->num_rows())
        {
            $data = $query->result_array();
        }
        return $data;
    }

    public function get_interest_suggestions($user_id, $keyword) {
        $data = array();
        $this->db->select('CategoryID,Name');
        $this->db->from(CATEGORYMASTER);
        $this->db->where('ModuleID', '31');
        $this->db->where('StatusID', '2');
        $this->db->where('ParentID!="0"', NULL, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $data = $query->result_array();
        }
        return $data;
    }

    public function get_users($keyword, $only_users = true, $user_ids = NULL, $tag_ids = NULL)
    {
        
        if($user_ids !== NULL && $tag_ids !== NULL && empty($user_ids) && empty($tag_ids)) {
            return [];
        }
        
        $final_query = '';
        $data = array();
        $this->db->select("U.UserID, U.FirstName, U.LastName, CONCAT(U.FirstName,' ',U.LastName) as Name, U.UserGUID, U.ProfilePicture, 'USER' AS EntityType, 0 As EntityID",false);
        $this->db->from(USERS.' U');
        if($user_ids === NULL) {
            $this->db->where("FirstName LIKE '%".$this->db->escape_like_str($keyword)."%' OR LastName LIKE '%".$keyword."%' OR CONCAT(FirstName,' ',LastName) LIKE '%".$this->db->escape_like_str($keyword)."%'",null,false);
            $final_query = $this->db->_compile_select();
        } else if(!empty($user_ids)){
            $this->db->where_in('U.UserID', $user_ids);
            $final_query = $this->db->_compile_select();
        }
        
        $this->db->reset_query();
        
        if(!$only_users) {
            $this->db->select("'0' AS UserID, , '' AS FirstName, '' AS LastName, T.Name as Name,'' AS UserGUID, '' AS ProfilePicture, 'Tag' AS EntityType, T.TagID EntityID",false);
            $this->db->from(TAGS.' T');
            
            $tags_query = '';
            if($tag_ids === NULL) {
                $this->db->where("T.Name LIKE '".$this->db->escape_like_str($keyword)."%' ",null,false);
                $tags_query = $this->db->_compile_select();
            } else if(!empty($tag_ids)) {
                $this->db->where_in('T.TagID', $tag_ids);
                $tags_query = $this->db->_compile_select();
            }
            
            $this->db->reset_query();
            
            $final_query_arr = [];
            if($final_query) {
                $final_query_arr[] = $final_query;
            }
            
            if($tags_query) {
                $final_query_arr[] = $tags_query;
            }
            
            if(empty($final_query_arr)) {
                return [];
            }
            
            $final_query = implode(") Union All (", $final_query_arr);
            $final_query = "($final_query)";
            
        }
        
        $query = $this->db->query($final_query);
        $data = $query->result_array();
        
        return $data;
    }

    public function get_tags($keyword, $tag_type='ACTIVITY')
    {
        $data = array();
        $this->db->select("T.Name,T.TagID",false);
        $this->db->from(TAGS.' T');
        $this->db->where("Name LIKE '%".$this->db->escape_like_str($keyword)."%'",null,false);
        if($tag_type == 'ACTIVITY')
        {
            $this->db->where_in("TagType", array(1,3,4,5));
        }
        else
        {
            $this->db->where_in("TagType", array(5,6,7));    
        }
        $query = $this->db->get();
        if($query->num_rows())
        {
            $data = $query->result_array();
        }
        return $data;
    }

    public function get_activity_details($activity_guid,$id='guid')
    {
        $post_type = array('1'=>'Discussion','2'=>'Q & A','3'=>'Polls','4'=>'Article','5'=>'Tasks & Lists','6'=>'Ideas','7'=>'Announcements');

        $data = array();
        $this->db->select("A.ActivityID,A.ActivityGUID,A.PostContent,A.PostTitle,A.NoOfLikes,A.NoOfComments,A.Privacy,A.PostType,A.CreatedDate");
        $this->db->select("U.FirstName,U.LastName,U.ProfilePicture");
        $this->db->select("P.URL as ProfileURL",false);
        $this->db->from(ACTIVITY." A");
        $this->db->join(USERS." U","A.UserID=U.UserID","left");
        $this->db->join(PROFILEURL . " P", "P.EntityID=A.UserID AND P.EntityType='User'","left");
        $this->db->where("A.StatusID","2");
        if($id == 'guid')
        {
            $this->db->where('A.ActivityGUID',$activity_guid);
        }
        else
        {
            $this->db->where('A.ActivityID',$activity_guid);
        }
        $query = $this->db->get();
        if($query->num_rows())
        {
            $data = $query->row_array();
            $data['ImageServerPath'] = IMAGE_SERVER_PATH;
            $data['PostTypeName'] = $post_type[$data['PostType']];
        }
        return $data;
    }

    public function get_rule_details($rule_id)
    {
        $this->load->model('admin/users_model');
        $data = array();
        $query = $this->db->get_where(DEFAULTACTIVITYRULE,array('ActivityRuleID'=>$rule_id));
        if($query->num_rows())
        {
            $data = $query->row_array();
            $data['PostFromUserList'] = array();
            if($data['PostFromUser'] || $data['PostFromUserTag'])
            {
                $data['PostFromUser'] = explode(',', $data['PostFromUser']);
                $data['PostFromUserTag'] = explode(',', $data['PostFromUserTag']);
                $data['PostFromUserList'] = $this->get_users('', false, $data['PostFromUser'], $data['PostFromUserTag']);
//                foreach($data['PostFromUser'] as $profile)
//                {
//                    $data['PostFromUserList'][] = $this->users_model->getUserDetails($profile);
//                }
            }

            $data['CustomizeProfilesList'] = array();
            if($data['CustomizeProfiles'])
            {
                $data['CustomizeProfiles'] = explode(',', $data['CustomizeProfiles']);
                foreach($data['CustomizeProfiles'] as $profile)
                {
                    $data['CustomizeProfilesList'][] = $this->users_model->getUserDetails($profile);
                }
            }

            $data['CustomizePostList'] = array();
            if($data['CustomizePostIDs'])
            {
                $data['CustomizePostIDs'] = explode(',', $data['CustomizePostIDs']);
                foreach($data['CustomizePostIDs'] as $activity)
                {
                    $data['CustomizePostList'][] = $this->get_activity_details($activity,'id');
                }
            }
        }
        return $data;
    }
    
    /*
    * [Run on rule update to calculate data for new rule data]
    * @param  rule_id
    * @return void
    */
    protected function on_rule_update($rule_id) {
        //$this->cache->delete('activity_'.$entity_id);
        //if(CACHE_ENABLE) { $this->cache->get('rule_user_'.$userId);}
        
        initiate_worker_job('calculate_default_activity', array('rule_id' => $rule_id));
        //$this->load->model(array('cron/cronrule_model'));
        //$this->cronrule_model->calculate_default_activity($rule_id);
    }
}
//End of file configuration_model.php
