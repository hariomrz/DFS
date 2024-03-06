<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class User_model extends Common_Model {

	protected $time_zone = '';
	protected $friend_followers_list = array('Friends' => array(), 'Follow' => array());
	protected $friend_of_friends = array();
	protected $user_profile_url = '';

	function __construct() {
            parent::__construct();
	}

	/**
	 * [set_user_time_zome used to assign user user time zone in variable]
	 * @param type $current_user
	 */
	function set_user_time_zone($current_user) {
		$this->time_zone = get_user_time_zone($current_user);
	}

	/**
	 * [get_user_time_zone used to return user time zone]
	 * @return type
	 */
	function get_user_time_zone() {
		return $this->time_zone;
	}

	/**
	 * [set_friend_followers_list used to assign friend follower list in variable]
	 * @param type $current_user
	 */
	function set_friend_followers_list($current_user) {
        if(!$this->settings_model->isDisabled(11)) {
            $this->friend_followers_list = $this->gerFriendsFollowersList($current_user, true, 1);
        }		
	}

	/**
	 * [get_friend_followers_list used to return friend follower list]
	 * @return type
	 */
	function get_friend_followers_list() {
		return $this->friend_followers_list;
	}

    /**
	 * [get_followers_list used to return follower list]
	 * @return type
	 */
	function get_followers_list() {
        $follow = safe_array_key($this->friend_followers_list, 'Follow', array());
        $follow[] = 0;
		return $follow;
	}

	/**
	 * [set_friends_of_friend_list used to assign friend of friends in variable]
	 * @param type $user_id
	 * @param type $friends
	 */
	function set_friends_of_friend_list($user_id, $friends) {
		$this->friend_of_friends = $this->get_friends_of_friend($user_id, $friends);
	}

	/**
	 * [get_friends_of_friend_list used to return friend of friends]
	 * @return type
	 */
	function get_friends_of_friend_list() {
		return $this->friend_of_friends;
	}

	/**
	 * [set_user_time_zome used to assign user user time zone in variable]
	 * @param type $current_user
	 */
	function set_user_profile_url($user_id) {
		$this->user_profile_url = get_entity_url($user_id, "User", 1);
	}

	/**
	 * [get_user_time_zone used to return user time zone]
	 * @return type
	 */
	function get_user_profile_url() {
		return $this->user_profile_url;
	}

	function update_username($user_id, $username) {
		if (CACHE_ENABLE) {
			$this->cache->delete('user_profile_' . $user_id);
		}
		$this->db->where('EntityType', 'User');
		$this->db->where('EntityID', $user_id);
		$this->db->update('ProfileUrl', array('Url' => $username, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));

		/* check sourceid 1 exists or not */
		$this->db->where('UserID', $user_id);
		$this->db->where('SourceID', '1');
		$query = $this->db->get(USERLOGINS);
		if ($query->num_rows()) {
//update
			//$this->db->where('UserID', $user_id);
			//$this->db->where('SourceID', '1');
			//$this->db->update(USERLOGINS, array('LoginKeyword' => $username));
		} else {
//insert
			$this->db->where('UserID', $user_id);
			$query = $this->db->get(USERLOGINS);
			$tempData = $query->row_array();
			unset($tempData['UserLoginID']);
			$tempData['LoginKeyword'] = $username;
			$tempData['SourceID'] = 1;
			$tempData['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
			$tempData['ProfileURL'] = '';
			$this->db->insert(USERLOGINS, $tempData);
		}
	}

	/**
	 * [update_profile Update user profile]
	 * @param  [string] $FirstName [User First Name]
	 * @param  [string] $LastName  [User Last Name]
	 * @param  [string] $Email     [User Email]
	 * @param  [string] $AboutMe   [About Me Description]
	 * @param  [array]  $Location  [User Location data]
	 * @param  [int] 	$user_id    [User ID]
	 * @param  [array] 	$Expertise [User Expertise]
	 * @param  [string] $Username  [User Name]
	 * @param  [string] $Gender    [User Gender]
	 * @param  [string] $MartialStatus  [User MartialStatus]
	 * @param  [string] $DOB  [User DOB]
	 * @param  [string] $TimeZoneID  [User TimeZone ID]
	 * @param  [string] RelationWithID [User Relationship with]
	 */
	function update_profile($FirstName, $LastName, $Email, $AboutMe, $Location, $user_id, $Expertise, $Username, $Gender, $MartialStatus, $DOB, $TimeZoneID, $RelationWithID = 0, $HomeLocation = false, $RelationWithName = '', $introduction = '', $profile_setup_step = '',$tagline = '',$house_number = '',$address = '',$occupation = '') {
		$Email = strtolower(trim($Email));
        $Username = strtolower(trim($Username));
        $FirstName = ucwords(trim($FirstName));
        $LastName = ucwords(trim($LastName));
        $this->load->helper('location');
		
        $LocationData = array();
		if (!empty($Location['City']) || !empty($Location['State']) || !empty($Location['Country'])) {
            $LocationData = update_location($Location);
		}
        if (empty($HomeLocation['City']) || empty($HomeLocation['State']) || empty($HomeLocation['Country'])) {
            $HomeLocation = false;
		}

		if ($HomeLocation) {
            $HomeLocationData = update_location($HomeLocation);
		}

		$update_status = 2;
        /* $email_check_query = $this->db->select('Email,StatusID,UserGUID')->from(USERS)->where('UserID', $user_id)->get();		
            if ($email_check_query->num_rows()) {

                $row = $email_check_query->row();
                $update_status = $row->StatusID;
                if ($row->StatusID == 6) {
                    $update_status = '1';
                } elseif ($row->StatusID == 7) {
                    $update_status = '2';
                }
                if (strtolower($row->Email) != $Email && $Email != '') {
                    $this->load->model('users/signup_model');
                    $update_email_data = array('Email' => $Email, 'UserGUID' => $row->UserGUID);
                    $this->signup_model->update_user_email($update_email_data);
                                    
                                    $this->email_updated_and_link_sent = 1;
                }
            }
        * 
        */

		$this->db->where('UserID', $user_id);
		$UpdateArray = array('FirstName' => $FirstName, 'LastName' => $LastName, 'Email' => $Email, 'Gender' => $Gender, 'StatusID' => $update_status);
		$this->db->update(USERS, $UpdateArray);
		
		$UserDetails = array(
            'UserWallStatus' => $AboutMe,
            'introduction' => $introduction,
            'MartialStatus' => $MartialStatus,
            'DOB' => $DOB,
            'RelationWithID' => $RelationWithID,
            'RelationWithName' => $RelationWithName,
            'TagLine' => $tagline,
            'HouseNumber' => $house_number,
            'Address' => $address,
            'Occupation' => $occupation
		);
        if(isset($LocationData['CityID']) && isset($LocationData['CountryID'])) {
            $UserDetails['CityID'] = $LocationData['CityID'];
            $UserDetails['CountryID'] = $LocationData['CountryID'];
        }
		if ($TimeZoneID) {
            $UserDetails['TimeZoneID'] = $TimeZoneID;
		}
		if ($HomeLocation) {
            $UserDetails['HomeCityID'] = $HomeLocationData['CityID'];
            $UserDetails['HomeCountryID'] = $HomeLocationData['CountryID'];
		}
		$this->db->where('UserID', $user_id);
		$this->db->update(USERDETAILS, $UserDetails);
                
        // Update newsletter details if user exists.
        /* $this->load->model('settings_model');
        if (!$this->settings_model->isDisabled(35)) {
            $this->load->model(array('admin/newsletter/newsletter_users_model'));
            $this->newsletter_users_model->update_newsletter_user_data($Email);
        }         
        
        $this->update_verify_status_on_profile_change($user_id);

		if (!empty($Username)) {
            $this->db->where('EntityType', 'User');
            $this->db->where('EntityID', $user_id);
            $this->db->update('ProfileUrl', array('Url' => $Username, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));

            // check sourceid 1 exists or not 
            $this->db->where('UserID', $user_id);
            $this->db->where('SourceID', '1');
            $query = $this->db->get(USERLOGINS);
            if ($query->num_rows()) { //update
                $this->db->where('UserID', $user_id);
                $this->db->where('SourceID', '1');
                $this->db->update(USERLOGINS, array('LoginKeyword' => $Username));
            } else {//insert
                $this->db->where('UserID', $user_id);
                $query = $this->db->get(USERLOGINS);
                $tempData = $query->row_array();
                unset($tempData['UserLoginID']);
                $tempData['LoginKeyword'] = $Username;
                $tempData['SourceID'] = 1;
                $tempData['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $tempData['ProfileURL'] = '';
                $this->db->insert(USERLOGINS, $tempData);
            }
		}

		// ===== update user persona ====
		$user_persona_array = array('Location' => $HomeLocation, 'DOB' => $DOB, 'RelationWithName' => $RelationWithName, 'Gender' => $Gender, 'MartialStatus' => $MartialStatus, 'RelationWithID' => $RelationWithID);
		$this->update_user_persona($user_persona_array,$user_id);
		//===============================
        $this->updateProfileCompeteStatus($user_id);
        */
		if (CACHE_ENABLE) {
			$this->cache->delete('user_profile_' . $user_id);
		}       
	}
        
    function isProfileCompleted($user_id) {
        $this->db->select('U.IsProfileSetup');
        $this->db->from(USERS . ' U');            
        $this->db->where('U.UserID', $user_id);            
        $query = $this->db->get();
        $user = $query->row_array();   
        
        if(isset($user['IsProfileSetup'])) {
            return (int)$user['IsProfileSetup'];
        }
        
        return 0;
    }
    
    function update_profile_compete_status($user_id) {
        $is_profile_setup = 0;
        $this->db->select('I.InterestID');
        $this->db->from(USERINTEREST . ' UI');
        $this->db->join(INTEREST . ' I', 'I.InterestID=UI.InterestID AND I.Status = 2');
        $this->db->where('UI.UserID',$user_id, FALSE);
        $this->db->limit(1);
        $query = $this->db->get();
        $is_interest_set = 0;
        if($query->num_rows() > 0) {
            $is_interest_set = 1;
        }

        $this->db->select('ES.SkillID');
        $this->db->from(ENTITYSKILLS . ' ES');
        $this->db->join(SKILLSMASTER . ' SM', 'SM.SkillID=ES.SkillID and SM.StatusID=2');
        $this->db->where('ES.ModuleID', 3);
        $this->db->where('ES.ModuleEntityID', $user_id, FALSE);
        $this->db->where('ES.StatusID', 2);
        $this->db->limit(1);
        $query = $this->db->get();
        $is_skill_set = 0;
        if($query->num_rows() > 0) {
            $is_skill_set = 2;
        }

        $this->db->select('M.MediaID');
        $this->db->from(MEDIA . ' M');
        $this->db->where('M.UserID',$user_id, FALSE);
        $this->db->where('M.MediaSectionID', 14);
        $this->db->where('M.StatusID', 2);
        $this->db->limit(1);
        $query = $this->db->get();
        $is_gallery_set = 0;
        if($query->num_rows() > 0) {
            $is_gallery_set = 3;
        }

        if(!empty($is_interest_set) && !empty($is_skill_set) && !empty($is_gallery_set)) {
            $is_profile_setup = 7;
        } else if(!empty($is_skill_set) && !empty($is_gallery_set)) {
            $is_profile_setup = 6;
        } else if(!empty($is_interest_set) && !empty($is_gallery_set)) {
            $is_profile_setup = 5;
        } else if(!empty($is_interest_set) && !empty($is_skill_set)) {
            $is_profile_setup = 4;
        } else if(!empty($is_gallery_set)) {
            $is_profile_setup = 3;
        } else if(!empty($is_skill_set)) {
            $is_profile_setup = 2;
        } else if(!empty($is_interest_set)) {
            $is_profile_setup = 1;
        }
        
        //Update profile complete status
        $update_data = array('IsProfileSetup' => $is_profile_setup);
        $this->db->where('UserID', $user_id);
        $this->db->update(USERS, $update_data);

        $this->delete_mongo_db_record('active_user_login', array('UserID' => (string)$user_id));
    }

	function update_user_persona($userdata,$user_id)
    {
        //User Data
        if(!empty($userdata['Gender']))
        {
            $user_data['AdminGender'] = $userdata['Gender'];
        }

        if(!empty($userdata['RelationWithID']))
        {
            $profile_data['AdminRelationWithID'] = $userdata['RelationWithID'];
        }

        if(!empty($userdata['PhoneNumber']))
        {
            $user_data['AdminPhoneNumber'] = $userdata['PhoneNumber'];
        }
        
        //User Profile Data
        if(!empty($userdata['DOB']))
        {
            $profile_data['AdminDOB'] = $userdata['DOB'];    
        }
        
        if(!empty($userdata['MartialStatus']))
        {
            $profile_data['AdminMartialStatus'] = $userdata['MartialStatus'];
        }
        
        if(!empty($userdata['RelationWithName']))
        {
            $profile_data['AdminRelationWithName']  = $userdata['RelationWithName'];    
        }

        if(!empty($userdata['ProfilePicture']))
        {
            $profile_data['AdminProfilePicture']  = $userdata['ProfilePicture'];    
        }
        
        if(!empty($userdata['Location']))
        {
            $location = $userdata['Location'];
            $updated_location = update_location($location);
            $profile_data['AdminHomeCityID'] = $updated_location['CityID'];
            $profile_data['AdminHomeCountryID'] = $updated_location['CountryID'];
            //$profile_data['AdminRelationWithID'] = "";
        }
        
        if(!empty($user_data))
        {
            //Update user
            $this->db->where('UserID',$user_id);
            $this->db->update(USERS,$user_data);    
        }
        
        if(!empty($profile_data))
        {
            //Update user details
            $this->db->where('UserID',$user_id);
            $this->db->update(USERDETAILS,$profile_data);    
        }
        return TRUE;
    }

	function get_friends_of_friend($user_id, $friends = array()) {
		if (!empty($friends)) {
			$this->db->select('F.UserID');
			$this->db->from(FRIENDS . ' F');
			$this->db->where_in('F.FriendID', $friends);
			$this->db->where('F.Status', '1');
			$this->db->group_by('F.UserID');
			$query = $this->db->get();
			if ($query->num_rows()) {
				foreach ($query->result() as $frnd) {
					if (!in_array($frnd->UserID, $friends)) {
						$friends[] = $frnd->UserID;
					}
				}
			}
			$friends = array_unique($friends);
			return $friends;
		} else {
			$friends = array();
			$this->db->select('F.UserID');
			$this->db->from(FRIENDS . ' F');
			$this->db->where('F.FriendID', $user_id);
			$this->db->where('F.Status', '1');
			$query = $this->db->get();
			if ($query->num_rows()) {
				foreach ($query->result() as $frnd) {
					if (!in_array($frnd->UserID, $friends)) {
						$friends[] = $frnd->UserID;
					}

					$this->db->select('F.UserID');
					$this->db->from(FRIENDS . ' F');
					$this->db->where('F.FriendID', $frnd->UserID);
					$this->db->where('F.Status', '1');
					$qry = $this->db->get();
					if ($qry->num_rows()) {
						foreach ($qry->result() as $frn) {
							if (!in_array($frn->UserID, $friends)) {
								$friends[] = $frn->UserID;
							}
						}
					}
				}
			}
			return $friends;
		}
	}
	
	/**
	 * [update_work_experience used to update user WorkExperience details]
	 * @param  [type] $user_id         [User ID]
	 * @param  [type] $WorkExperience [User Work Experience]
	 * @return [type]                 [description]
	 */
	function update_work_experience($user_id, $WorkExperience) {
            $NewWorkExperience = array();
            $ExistingWorkExperience = array();
            $ii = 0;
            $iu = 0;
            $WorkExpGUID = array();
            if (CACHE_ENABLE) {
                $this->cache->delete('user_profile_' . $user_id);
            }
            if ($WorkExperience) {
                foreach ($WorkExperience as $exp) {
                    if (isset($exp['OrganizationName'])) {
                        if (isset($exp['WorkExperienceGUID']) && !empty($exp['WorkExperienceGUID'])) {
                            if (isset($exp['OrganizationName']) && isset($exp['Designation']) && !empty($exp['OrganizationName']) && !empty($exp['Designation'])) {
                                $WorkExpGUID[] = $exp['WorkExperienceGUID'];
                                $ExistingWorkExperience[$iu]['WorkExperienceGUID'] = $exp['WorkExperienceGUID'];
                                $ExistingWorkExperience[$iu]['UserID'] = $user_id;
                                $ExistingWorkExperience[$iu]['OrganizationName'] = $exp['OrganizationName'];
                                $ExistingWorkExperience[$iu]['Designation'] = $exp['Designation'];
                                $ExistingWorkExperience[$iu]['StartMonth'] = $exp['StartMonth'];
                                $ExistingWorkExperience[$iu]['StartYear'] = $exp['StartYear'];
                                $ExistingWorkExperience[$iu]['EndMonth'] = $exp['EndMonth'];
                                $ExistingWorkExperience[$iu]['EndYear'] = $exp['EndYear'];
                                $ExistingWorkExperience[$iu]['CurrentlyWorkHere'] = trim($exp['CurrentlyWorkHere']);
                                $ExistingWorkExperience[$iu]['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                                $ExistingWorkExperience[$iu]['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                                $iu++;
                            }
                        } else {
                            if (isset($exp['OrganizationName']) && isset($exp['Designation']) && !empty($exp['OrganizationName']) && !empty($exp['Designation'])) {
                                $NewWorkExperience[$ii]['WorkExperienceGUID'] = get_guid();
                                $NewWorkExperience[$ii]['UserID'] = $user_id;
                                $NewWorkExperience[$ii]['OrganizationName'] = $exp['OrganizationName'];
                                $NewWorkExperience[$ii]['Designation'] = $exp['Designation'];
                                $NewWorkExperience[$ii]['StartMonth'] = $exp['StartMonth'];
                                $NewWorkExperience[$ii]['StartYear'] = $exp['StartYear'];
                                $NewWorkExperience[$ii]['EndMonth'] = $exp['EndMonth'];
                                $NewWorkExperience[$ii]['EndYear'] = $exp['EndYear'];
                                $NewWorkExperience[$ii]['CurrentlyWorkHere'] = trim($exp['CurrentlyWorkHere']);
                                $NewWorkExperience[$ii]['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                                $NewWorkExperience[$ii]['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                                $ii++;
                            }
                        }
                    }
                }
            }
            $this->db->where('UserID', $user_id);
            if ($WorkExpGUID) {
                    $this->db->where_not_in('WorkExperienceGUID', $WorkExpGUID);
            }
            $this->db->delete(WORKEXPERIENCE);
            if (!empty($ExistingWorkExperience)) {

                    $this->db->update_batch(WORKEXPERIENCE, $ExistingWorkExperience, 'WorkExperienceGUID');
            }
            if (!empty($NewWorkExperience)) {
                    $this->db->insert_batch(WORKEXPERIENCE, $NewWorkExperience);
            }

            $this->update_verify_status_on_profile_change($user_id);
	}
        
        
    function update_verify_status_on_profile_change($user_id) {
        $this->db->where('UserID', $user_id);
        $this->db->update(USERS, array(
            'Verified' => 0   // Change status to unverified on profile update.
        ));
    }

	/**
	 * [update_education used to update user Education details]
	 * @param  [type] $user_id         [User ID]
	 * @param  [type] $Education 	  [User Education details]
	 * @return [type]                 [description]
	 */
	function update_education($user_id, $Education) {
		$NewEducation = array();
		$ExistingEducation = array();
		$ii = 0;
		$iu = 0;
		$EduGUID = array();
		if ($Education) {
			foreach ($Education as $ed) {
				if (isset($ed['University'])) {
					if (isset($ed['EducationGUID']) && !empty($ed['EducationGUID'])) {
						if (isset($ed['University']) && isset($ed['CourseName']) && !empty($ed['University']) && !empty($ed['CourseName'])) {
							$EduGUID[] = $ed['EducationGUID'];
							$ExistingEducation[$iu]['EducationGUID'] = $ed['EducationGUID'];
							$ExistingEducation[$iu]['UserID'] = $user_id;
							$ExistingEducation[$iu]['University'] = $ed['University'];
							$ExistingEducation[$iu]['CourseName'] = $ed['CourseName'];
							$ExistingEducation[$iu]['StartYear'] = $ed['StartYear'];
							$ExistingEducation[$iu]['EndYear'] = $ed['EndYear'];
							$ExistingEducation[$iu]['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
							$ExistingEducation[$iu]['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
							$iu++;
						}
					} else {
						if (isset($ed['University']) && isset($ed['CourseName']) && !empty($ed['University']) && !empty($ed['CourseName'])) {
							$NewEducation[$ii]['EducationGUID'] = get_guid();
							$NewEducation[$ii]['UserID'] = $user_id;
							$NewEducation[$ii]['University'] = $ed['University'];
							$NewEducation[$ii]['CourseName'] = $ed['CourseName'];
							$NewEducation[$ii]['StartYear'] = $ed['StartYear'];
							$NewEducation[$ii]['EndYear'] = $ed['EndYear'];
							$NewEducation[$ii]['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
							$NewEducation[$ii]['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
							$ii++;
						}
					}
				}
			}
		}
		$this->db->where('UserID', $user_id);
		if ($EduGUID) {
			$this->db->where_not_in('EducationGUID', $EduGUID);
		}
		$this->db->delete(EDUCATION);
		if (!empty($ExistingEducation)) {
			$this->db->update_batch(EDUCATION, $ExistingEducation, 'EducationGUID');
		}
		if (!empty($NewEducation)) {
			$this->db->insert_batch(EDUCATION, $NewEducation);
		}
                
                $this->update_verify_status_on_profile_change($user_id);

		$this->cache->delete('user_profile_' . $user_id);
	}

	/**
	 * [get_user_location Used to get user location information]
	 * @param  [int] $user_id [User ID]
	 * @return [array]       [Array of User location information]
	 */
	function get_user_location($user_id, $home = 0) {
		$this->db->select('IFNULL(S.Name,"") as StateName', FALSE);
		$this->db->select('IFNULL(S.ShortCode,"") as StateCode', FALSE);
		$this->db->select('IFNULL(CT.Name,"") as CityName', FALSE);
		$this->db->select('IFNULL(C.CountryName,"") as CountryName', FALSE);
		$this->db->select('IFNULL(C.CountryCode,"") as CountryCode', FALSE);
		$this->db->select('IFNULL(UD.Address,"") as Address', FALSE); /*added by gautam*/
		$this->db->from(USERDETAILS . ' UD');
		if ($home) {
			$this->db->join(CITIES . ' CT', 'CT.CityID = UD.HomeCityID', 'left');
			//$this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = UD.HomeCountryID', 'left');
		} else {
			$this->db->join(CITIES . ' CT', 'CT.CityID = UD.CityID', 'left');
			//$this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = UD.CountryID', 'left');
		}
		$this->db->join(STATES . ' S', 'CT.StateID = S.StateID', 'left');
                $this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = S.CountryID', 'left');
		$this->db->where('UD.UserID', $user_id);
		$query = $this->db->get();
		//echo $this->db->last_query();
		if ($query->num_rows()) {
			$row = $query->row();
			$city = trim($row->CityName);
			$State = trim($row->StateName);
			$StateCode = trim($row->StateCode);
			$Country = trim($row->CountryName);
			$CountryCode = trim($row->CountryCode);
			$Address = trim($row->Address);
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
			return array('City' => $city, 'State' => $State, 'Country' => $Country, 'Location' => $Location, 'StateCode' => $StateCode, 'CountryCode' => $CountryCode, 'Address' => $Address);
		}
                return array('City' => '', 'State' => '', 'Country' => '', 'Location' => '', 'StateCode' => '', 'CountryCode' => '', 'Address' => '');
	}

	/**
	 * [get_user_location_admin Used to get user location admin information]
	 * @param  [int] $user_id [User ID]
	 * @return [array]       [Array of User location information]
	 */
	function get_user_location_admin($user_id, $home = 0, $user_city = 0, $is_newsletter_subscriber = 0) {
		$this->db->select('IFNULL(S.Name,"") as StateName', FALSE);
		$this->db->select('IFNULL(S.ShortCode,"") as StateCode', FALSE);
		$this->db->select('IFNULL(CT.Name,"") as CityName', FALSE);
		$this->db->select('IFNULL(C.CountryName,"") as CountryName', FALSE);
		$this->db->select('IFNULL(C.CountryCode,"") as CountryCode', FALSE);
                if(!$is_newsletter_subscriber) {
                    $this->db->select('IFNULL(UD.Address,"") as Address', FALSE); /*added by gautam*/
                }
		
		
                
                if($is_newsletter_subscriber) {
                    $this->db->from(NEWSLETTERSUBSCRIBER . ' UD');
                } else {
                    $this->db->from(USERDETAILS . ' UD');
                }
                
                
                if($user_city) {
                    $this->db->join(CITIES . ' CT', 'CT.CityID = UD.CityID', 'left');
                    if(!$is_newsletter_subscriber) {
                        $this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = UD.CountryID', 'left');
                    }
                    
                    $this->db->join(STATES . ' S', 'CT.StateID = S.StateID', 'left');
                    
                } else {
                    if ($home) {
                            $this->db->join(CITIES . ' CT', 'CT.CityID = UD.AdminHomeCityID', 'left');
                            //$this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = UD.AdminHomeCountryID', 'left');
                    } else {
                            $this->db->join(CITIES . ' CT', 'CT.CityID = UD.AdminCityID', 'left');
                            //$this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = UD.AdminCountryID', 'left');
                    }
                    
                    $this->db->join(STATES . ' S', 'CT.StateID = S.StateID', 'left');
                    
                    $this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = S.CountryID', 'left');
                }
                
		
		
                
                if($is_newsletter_subscriber) {
                    $this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = S.CountryID', 'left');
                    $this->db->where('UD.NewsLetterSubscriberID', $user_id);
                } else {
                    $this->db->where('UD.UserID', $user_id);
                }
                
		
		$query = $this->db->get();
		//echo $this->db->last_query();
		if ($query->num_rows()) {
			$row = $query->row();
			$city = trim($row->CityName);
			$State = trim($row->StateName);
			$StateCode = trim($row->StateCode);
			$Country = trim($row->CountryName);
			$CountryCode = trim($row->CountryCode);
			$Address = isset($row->Address) ? trim($row->Address) : '';
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
			return array('City' => $city, 'State' => $State, 'Country' => $Country, 'Location' => $Location, 'StateCode' => $StateCode, 'CountryCode' => $CountryCode, 'Address' => $Address);
		}
                return array();
	}

	function get_connect_with($data) {
            $this->db->select('CategoryID,Name');
            $this->db->from(CATEGORYMASTER);
            $this->db->where_in('CategoryID', explode(',', $data));
            $query = $this->db->get();
            if ($query->num_rows()) {
                return $query->result_array();
            }
            return array();
	}

	function get_connect_from($data) {
            $this->db->select('CityID,Name');
            $this->db->from(CITIES);
            $this->db->where_in('CityID', explode(',', $data));
            $query = $this->db->get();
            if ($query->num_rows()) {
                return $query->result_array();
            }
            return array();
	}

	/**
	 * [profile Get user information]
	 * @param  [int]  $user_id      		[User ID]
	 * @param  [int]  $current_user_id  [Current User ID]
	 * @return [Array]             		[User information array]
	 */
	function profile($user_id, $current_user_id = 0, $is_super_admin = 0) {
        $userdata = array();
        if (CACHE_ENABLE) {
            $userdata = $this->cache->get('user_profile_' . $user_id);
            if(!is_array($userdata)){ 
                $userdata = "";
            }
        }
        if (empty($userdata)) {
            $userdata = $this->profile_cache($user_id, $current_user_id);
        }
        switch($this->DeviceTypeID) {
            case '2':
                $userdata['AppVersion'] = $userdata['IOSAppVersion'];
                $userdata['PushNotification'] = $userdata['MobileNotification'];
                break;
            case '3':                    
                $userdata['AppVersion'] = $userdata['AndroidAppVersion'];
                $userdata['PushNotification'] = $userdata['MobileNotification'];
                break;
            default:
                # code...
                break; 
        }     
        $userdata['Profession'] = $this->get_user_profession($user_id);       
        unset($userdata['AndroidAppVersion']);
        unset($userdata['IOSAppVersion']);
        unset($userdata['MobileNotification']);
        
        //$userdata['AllowedPostType'] = $this->get_post_permission_for_newsfeed($current_user_id);
        return $userdata;
	}

	/**
	 * [get_video_settings]
	 * @param  [int]  $user_id      		[User ID]
	 * @return [integer]
	 */
	function get_video_settings($user_id)
	{
		$autoplay = 0;
		$this->db->select('VideoAutoplay');
		$this->db->from(USERDETAILS);
		$this->db->where('UserID',$user_id);
		$query = $this->db->get();
		if($query->num_rows())
		{
			$autoplay = $query->row()->VideoAutoplay;
		}
		return $autoplay;
	}

	/**
	 * [profile Get user information]
	 * @param  [int]  $user_id      		[User ID]
	 * @param  [int]  $current_user_id  [Current User ID]
	 * @return [Array]             		[User information array]
	 */
	function profile_cache($user_id, $current_user_id = 0) {            
            $this->db->select("U.FirstName, U.LastName, CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as EntityName, U.Email, U.Gender, U.UserGUID, UD.TimeZoneID, UD.LocalityID, U.StatusID, U.CanCreatePoll, U.IsVIP, U.IsAssociation");
            $this->db->select('IFNULL(U.PhoneNumber,"") as PhoneNumber', FALSE);
            $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
            $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
            $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
            $this->db->select('IFNULL(UD.Address,"") as Address', FALSE);
            $this->db->select('IFNULL(UD.DOB,"") as DOB', FALSE);
            $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
            $this->db->select('IFNULL(TZ.StandardTime,"") as TimeZoneText', FALSE);
            $this->db->select('IFNULL(UD.AndroidAppVersion,"") as AndroidAppVersion', FALSE);
            $this->db->select('IFNULL(UD.IOSAppVersion,"") as IOSAppVersion', FALSE);
            $this->db->select('IFNULL(U.MobileNotification,"") as MobileNotification', FALSE);
            $this->db->select('UD.IncomeLevel, UD.IsDOBApprox');
            $this->db->from(USERS . ' U');
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
            $this->db->join(TIMEZONES . ' TZ', 'TZ.TimeZoneID = UD.TimeZoneID', 'left');
            $this->db->where('U.UserID', $user_id);
            $this->db->limit(1);
            $query = $this->db->get();
            $userdata = $query->row_array();
            if (!empty($userdata)) {
                $this->db->select('LoginKeyword as Username,SetPassword,SourceID');
                $this->db->from(USERLOGINS);
                $this->db->where('UserID', $user_id);
                $this->db->order_by('SourceID', 'ASC');
                $this->db->limit(1);
                $Qry = $this->db->get();
                if ($Qry->num_rows()) {
                        $QryRow = $Qry->row();
                        if ($QryRow->SourceID == 1) {
                                $userdata['Username'] = $QryRow->Username;
                        } else {
                                $userdata['Username'] = '';
                        }
                        $userdata['SetPassword'] = $QryRow->SetPassword;
                }
                $dob = $userdata['DOB'];
                $userdata['DOB'] = "";
                if (!empty($dob) && $dob != "0000-00-00") {
                        $dob = explode('-', $dob);
                        $userdata['DOB'] = $dob[1] . '/' . $dob[2] . '/' . $dob[0];
                }

                $userdata['ProfilePicture'] = $userdata['ProfilePicture'];
                $userdata['Location'] = array();//$this->get_user_location($user_id);
                
                $userdata['Locality'] = array('Name' => '', 'HindiName'=>'', 'ShortName'=>'',  'LocalityID' => 0, 'IsPollAllowed' => 1, 'WName'=>'', 'WNumber'=>'', 'WID'=>'', 'WDescription'=>'');
                if($userdata['LocalityID']) {
                    $this->load->model(array('locality/locality_model'));
                    $userdata['Locality'] = $this->locality_model->get_locality($userdata['LocalityID']);
                }
                if(empty($userdata['Locality']['LocalityID'])) {
                    $userdata['Locality']['LocalityID'] = 0;
                }
                unset($userdata['LocalityID']);
                
                if(empty($userdata['Locality']['IsPollAllowed'])) {
                    $userdata['CanCreatePoll']          = 0;
                }
                if($this->settings_model->isDisabled(30)){ //check for poll module
                    $userdata['CanCreatePoll']          = 0;
                }

                if (CACHE_ENABLE) {
                    $this->cache->save('user_profile_' . $user_id, $userdata, CACHE_EXPIRATION);
                }
            }
            return $userdata;
	}

	/**
	 * [update_collapse]
	 * @param  [Int] $user_id [User ID]
	 * @param  [Int] $is_collapse
	 * @return [Array]         [Array of user name]
	 */
	function update_collapse($user_id, $is_collapse) {
		$this->db->set('IsCollapse', $is_collapse);
		$this->db->where('UserID', $user_id);
		$this->db->update(USERS);

		$this->cache->delete('user_profile_' . $user_id);
	}

	/**
	 * [getUserName Used to get User Name]
	 * @param  [Int] $user_id [User ID]
	 * @return [Array]         [Array of user name]
	 */
	function getUserName($user_id, $module_id = 3, $module_entity_id = 0, $ignore_logged_in_user = FALSE, $informal_only = 0) {
            $this->load->model(array('category/category_model', 'privacy/privacy_model'));
            
		if ($module_id == 3 && $module_entity_id == 0) {
			$module_entity_id = $user_id;
		}
		if ($module_id == 3) {
			$details = get_detail_by_id($module_entity_id, $module_id, 'UserGUID,FirstName,LastName,ProfilePicture', 2);
		} else if ($module_id == 1) {
			$details = get_detail_by_id($module_entity_id, $module_id, 'Type,GroupName,GroupImage,GroupID,GroupGUID', 2);
		} else {
			$details = get_detail_by_id($module_entity_id, $module_id, '*', 2);
		}
		$data = array('FirstName' => '', 'LastName' => '', 'ProfilePicture' => '', 'ProfileURL' => '', 'ModuleID' => 0, 'ModuleEntityGUID' => 0);
                
                if(!$details) {
                    return $data;
                }
                
		if ($module_id == 1) {
			if (isset($details['Type']) && $details['Type'] == 'FORMAL' && $informal_only == 0) {
				$data['FirstName'] = $details['GroupName'];
			} else {
				$this->load->model('group/group_model');
				if ($ignore_logged_in_user) {
					$data['FirstName'] = $this->group_model->get_informal_group_name($module_entity_id, $user_id, 0, false, array($user_id));
				} else {
					$data['FirstName'] = $this->group_model->get_informal_group_name($module_entity_id, $user_id);
				}
			}
			$data['ProfilePicture'] = $details['GroupImage'];
                        
                        $group_url_details = $this->group_model->get_group_details_by_id($details['GroupID'], '', $details);
                        $data['ProfileURL'] = $this->group_model->get_group_url($details['GroupID'], $group_url_details['GroupNameTitle'], false, 'index');  
                        
			$data['ModuleID'] = 1;
			$data['ModuleEntityGUID'] = $details['GroupGUID'];
		} elseif ($module_id == 29) {
			$get_skill = get_data('Name', SKILLSMASTER, array('SkillID' => $module_entity_id), '1', '');
			$data['FirstName'] = '';
			if ($get_skill) {
				$data['FirstName'] = $get_skill->Name;
			}

			$data['LastName'] = ' ';
			$data['ProfilePicture'] = ' ';
			$data['ProfileURL'] = ' ';
			$data['ModuleID'] = 29;
			$data['ModuleEntityGUID'] = ' ';
		} elseif ($module_id == 27) {
			$get_category = $this->category_model->get_category_by_id($module_entity_id);
			$data['FirstName'] = $get_category['Name'];
			$data['LastName'] = ' ';
			$data['ProfilePicture'] = ' ';
			$data['ProfileURL'] = ' ';
			$data['ModuleID'] = 29;
			$data['ModuleEntityGUID'] = ' ';
		} else if ($module_id == 3) {
			$data['FirstName'] = $details['FirstName'];
			$data['LastName'] = $details['LastName'];
			$data['ProfilePicture'] = $details['ProfilePicture'];
			$data['ProfileURL'] = get_entity_url($module_entity_id, "User", 1);
			$data['ModuleID'] = 3;
			$data['ModuleEntityGUID'] = $details['UserGUID'];
			if (!empty($user_id) && $user_id != $module_entity_id) {
				$users_relation = get_user_relation($user_id, $module_entity_id);
				$privacy_details = $this->privacy_model->details($module_entity_id);
				$privacy = ucfirst($privacy_details['Privacy']);
				if ($privacy_details['Label']) {
					foreach ($privacy_details['Label'] as $privacy_label) {
						if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
							$data['ProfilePicture'] = '';
						}
					}
				}
			}
		} else if ($module_id == 14) {
			$data['FirstName'] = $details['Title'];
			$data['ProfilePicture'] = "event-placeholder.png";
                        
                        $this->load->model('events/event_model');
                        $url = $this->event_model->getViewEventUrl($details['EventGUID'], $details['Title'], false,'wall');
            
			$data['ProfileURL'] = $url; 
			$data['ModuleID'] = 14;
			$data['ModuleEntityGUID'] = $details['EventGUID'];

			if (!empty($details['ProfileImageID'])) {
				$this->db->select('ImageName');
				$ImageArr = $this->db->get_where(MEDIA, array('MediaID' => $details['ProfileImageID']))->row_array();
				$data['ProfilePicture'] = $ImageArr['ImageName'];
			}
		} else if ($module_id == 18) {
			$data['FirstName'] = $details['Title'];
			$data['ProfilePicture'] = $details['ProfilePicture'];
			$data['ProfileURL'] = 'page/' . $details['PageURL'];
			$data['ModuleID'] = 18;
			$data['ModuleEntityGUID'] = $details['PageGUID'];
		} else if ($module_id == 33) {
			$details = get_detail_by_id($module_entity_id, $module_id, 'Name,ForumGUID', 2);
			$data['FirstName'] = $details['Name'];
			$data['ProfilePicture'] = '';
			$data['ProfileURL'] = '';
			$data['ModuleID'] = 33;
			$data['ModuleEntityGUID'] = '';
		} else if ($module_id == 34) {
			$this->load->model('forum/forum_model');

			$details = get_detail_by_id($module_entity_id, $module_id, 'Name, ForumCategoryGUID, MediaID', 2);
			$data['FirstName'] = $details['Name'];
			$data['ModuleID'] = 34;
			$data['ModuleEntityGUID'] = $details['ForumCategoryGUID'];
			$data['ProfilePicture'] = 'category_default.png';
			$data['ProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
			if (!empty($details['MediaID'])) {
				$this->db->select('ImageName');
				$ImageArr = $this->db->get_where(MEDIA, array('MediaID' => $details['MediaID']))->row_array();
				$data['ProfilePicture'] = $ImageArr['ImageName'];
			}
		}
		return $data;

		/* $this->db->select('FirstName,LastName,ProfilePicture');
			          $this->db->from(USERS);
			          $this->db->where('UserID',$user_id);
			          $query = $this->db->get();
			          if($query->num_rows()){
			          return $query->row_array();
			          } else {
			          return array('FirstName'=>'','LastName'=>'','ProfilePicture'=>'');
		*/
	}

	/**
	 * [getWorkExperience Used to get User Work Experience Details]
	 * @param  [int] $user_id [User ID]
	 * @return [array]       [array of User Work Experience]
	 */
	function getWorkExperience($user_id) {
		$this->db->where('UserID', $user_id)->order_by('StartYear', 'DESC')->order_by('StartMonth', 'DESC');
		$query = $this->db->get(WORKEXPERIENCE);
		if ($query->num_rows()) {
			return $query->result_array();
		} else {
			return array();
		}
	}

	/**
	 * [getUserEducation Used to get User Education Details]
	 * @param  [int] $user_id [User ID]
	 * @return [array]       [array of User Education]
	 */
	function getUserEducation($user_id) {
		$this->db->where('UserID', $user_id)->order_by('StartYear', 'DESC');
		$query = $this->db->get(EDUCATION);
		if ($query->num_rows()) {
			return $query->result_array();
		} else {
			return array();
		}
	}
	
        /** 
         * 
         * @param type $user_id Loggedin user id
         * @param type $array return response as array or not 
         * @param type $sep return separate response of friends, follow
         * @param type $friends_only return only friends
         * @param type $count_only return count
         * @return type
         */
	function gerFriendsFollowersList($user_id, $array = false, $sep = 0, $friends_only = false, $count_only=FALSE) {
            $friend_disabled = $this->settings_model->isDisabled(10);

            $arr = array();
            if ($sep) {
                $arr = array('Friends' => array(), 'Follow' => array());
            }
            $friends_data = '';
            $followers_data = '';
            if (CACHE_ENABLE) {
                $followers_data = $this->cache->get('user_followers_' . $user_id);
                $followers_data = trim($followers_data);
                if($friend_disabled) {
                    $friends_data = $followers_data;
                } else {
                    $friends_data = $this->cache->get('user_friends_' . $user_id);
                    $friends_data = trim($friends_data);
                }
            }

            $this->db->simple_query('SET SESSION group_concat_max_len=150000');
            if (empty($friends_data) &&  !$friend_disabled) {
                //Get Friend List
                $this->db->select('GROUP_CONCAT(FRD.FriendID) as FriendID');
                $this->db->from(FRIENDS. ' FRD ');
                $this->db->join(USERS.' U','U.UserID=FRD.FriendID','left');
                $this->db->where_not_in('U.StatusID',array(3,4));
                $this->db->where('FRD.UserID', $user_id);
                $this->db->where('FRD.Status', '1');
                $this->db->order_by('FRD.FriendID', 'ASC');
                $friendResult = $this->db->get();
                //echo $this->db->last_query();die;
                $friends_data = -1;
                if ($friendResult->num_rows()) {
                    $friend_row = $friendResult->row_array();
                    if (!empty($friend_row['FriendID'])) {
                        $friends_data = $friend_row['FriendID'];                        
                    }
                }
                if (CACHE_ENABLE) {
                    $this->cache->save('user_friends_' . $user_id, $friends_data, CACHE_EXPIRATION);
                }
            }

            if(!$friend_disabled && $friends_only) {
                if (!empty($friends_data) && $friends_data != '-1') {
                    return explode(',', $friends_data);
                }
                return array();
            }

            if (empty($followers_data)) {
                //Get Following List
                $this->db->select(' GROUP_CONCAT(F.FollowingID) as FollowingID');
                $this->db->from(FOLLOW.' F');
                $this->db->join(USERS.' U','U.UserID=F.FollowingID AND U.StatusID NOT IN (3,4)');
                $this->db->where('F.UserID', $user_id);
                $this->db->order_by('F.FollowingID', 'ASC');
                $followResult = $this->db->get();
                //echo $this->db->last_query(); die;
                $followers_data = -1;
                if ($followResult->num_rows()) {
                    $follow_row = $followResult->row_array();
                    if (!empty($follow_row['FollowingID'])) {
                        $followers_data = $follow_row['FollowingID'];                        
                    }
                }
                if (CACHE_ENABLE) {
                    $this->cache->save('user_followers_' . $user_id, $followers_data, CACHE_EXPIRATION);
                }
            }

            if($friend_disabled) {
                $friends_data = $followers_data;
            }
            
            if($friends_data == -1) {
                $friends_data = '';
            }
            if($followers_data == -1) {
                $followers_data = '';
            }
            if ($sep) {
                if (!empty($friends_data)) {
                    $arr['Friends'] = explode(',', $friends_data);	
                    if($count_only) {
                        $arr['Friends'] = count($arr['Friends']);
                    }
                }
                if (!empty($followers_data)) {
                    $arr['Follow'] = explode(',', $followers_data);
                    if($count_only) {
                        $arr['Follow'] = count($arr['Follow']);                        
                    }
                }
            } else {
                $temp_data = '';
                if (!empty($friends_data) && !empty($followers_data) && !$friends_only) {
                    $temp_data = $friends_data . ',' . $followers_data;
                } elseif (!empty($friends_data)) {
                    $temp_data = $friends_data;
                } elseif (!empty($followers_data)) {
                    $temp_data = $followers_data;
                }
                if (!empty($temp_data)) {
                    $arr = explode(',', $temp_data);
                    $arr = array_unique($arr);
                }
            }

            if ($array) {
                 if ($sep && $friends_only) {
                     return $arr['Friends'];
                 } else {
                     return $arr;
                 }                
            } else {
                return implode(',', $arr);
            }
	}

	/**
	 * [remove_follow Used to remove follow user.]
	 * @param  [int] $user_id        [user_id]
	 * @param  [int] $remove_user_id [remove_user_id]
	 */
	function remove_follow($user_id, $remove_user_id) {
            $this->db->where('Type', 'user');
            $this->db->where('UserID', $remove_user_id);
            $this->db->where('TypeEntityID', $user_id);
            $this->db->delete(FOLLOW);

            if (CACHE_ENABLE) {
                    $this->cache->delete('user_followers_' . $remove_user_id);
            }
	}

	/**
	 * [get_age_group_list Get age group list]
	 * @return [array] [age group list]
	 */
	function get_age_group_list() {
            $query = $this->db->get(AGEGROUPS);
            if ($query->num_rows()) {
                return $query->result_array();
            }
	}

		
	/**
	 * [cover_image_state Used to SAVE cover image state for any source]
	 * @param  [int] $user_id            [Logged in User ID]
	 * @param  [int] $module_entity_id 	 [Module Entity ID]
	 * @param  [int] $module_id          [Module ID]
	 * @param  [int] $status             [Cover Image State]
	 */
	function cover_image_state($user_id, $module_entity_id, $module_id, $status) {
		$insert[] = array('UserID' => $user_id, 'ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id, 'Status' => $status, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
		//print_r($insert);die;
		$this->db->insert_on_duplicate_update_batch(COVERIMAGESTATE, $insert);
	}

	
	function get_group_members_details($group_guid) {
		$this->db->select("U.UserGUID AS ModuleEntityGUID,U.UserID,CONCAT(U.FirstName,' ',U.LastName) AS name,U.ProfilePicture,'' AS Privacy, GM.ModuleID, 'User' AS Type ,'' AS GroupDescription", FALSE);
		$this->db->from(GROUPMEMBERS . ' GM');
		$this->db->join(GROUPS . ' G', 'G.GroupID=GM.GroupID AND GM.ModuleID=3');
		$this->db->join(USERS . ' U', 'U.UserID=GM.ModuleEntityID');
		$this->db->where('G.GroupGUID', $group_guid);
		$this->db->where('GM.StatusID', '2');
		$result1 = $this->db->get()->result_array();

		$this->db->select('G2.GroupGUID AS ModuleEntityGUID,G2.GroupName AS name,G2.GroupImage AS ProfilePicture,G2.IsPublic AS Privacy,GM.ModuleID, G2.Type, G2.GroupDescription', FALSE);
		$this->db->from(GROUPMEMBERS . ' GM');
		$this->db->join(GROUPS . ' G', 'G.GroupID=GM.GroupID');
		$this->db->join(GROUPS . ' G2', 'G2.GroupID=GM.ModuleEntityID AND GM.ModuleID=1');
		$this->db->where('G.GroupGUID', $group_guid);
		$this->db->where('GM.StatusID', '2');
		$result2 = $this->db->get()->result_array();

		return array_merge($result1, $result2);
	}
	
        /**
        * [update_login_analytic_age_group Used to update login analytic age group]
        * @param  [int] $user_id            [Logged in User ID]
        * @param  [string] $login_session_key 	 [login session key]
        */
        function update_login_analytic_age_group($user_id, $login_session_key) {
           $age_group_id = $this->get_age_group_id($user_id);
           $this->db->where('UserID', $user_id);
           $this->db->where('LoginSessionKey', $login_session_key);
           $this->db->order_by('AnalyticLoginID','DESC');
           $this->db->limit(1);                    
           $this->db->update(ANALYTICLOGINS, array('AgeGroupID' => $age_group_id));
           if (CACHE_ENABLE) {
               $this->cache->delete('rule_user_' . $user_id);
           }
        }
	function is_super_admin($user_id, $is_sub_admin=0) {		
		$roles = $this->cache->get('user_roles_' . $user_id);
                if($roles && is_array($roles)) {
                    if(in_array(1, $roles)) {
                        return 1;
                    } else if($is_sub_admin == 1 && in_array(6, $roles)) {
                        return 1;                        
                    } else if($is_sub_admin == 2 && in_array(3, $roles)) {
                        return 1;                        
                    } else {
                        return 0;
                    }
                }
		
                $where = array(1);
                if($is_sub_admin == 1) {
                    $where[] = 6;
                }
                if($is_sub_admin == 2) {
                    $where[] = 3;
                }

                $this->db->select('RoleID');
                $this->db->from(USERROLES);
                $this->db->where('UserID', $user_id);
                $this->db->where_in('RoleID', $where);
                $query = $this->db->get();
                if ($query->num_rows()) {
                    return 1;  
                }
		
		return 0;
	}

	function get_post_permission_for_newsfeed($user_id = 0,$all=0) {
		$data = array();
		$data[] = array('Value' => '1', 'Label' => 'Discussion');
		//$data[] = array('Value' => '2', 'Label' => 'Question');
		return $data;
                $is_admin = 0;
		$visual_post_disbaled = $this->settings_model->isDisabled(37);
		$contest_disbaled = $this->settings_model->isDisabled(36);
                $article_disbaled = $this->settings_model->isDisabled(38);
                $announcements_disbaled = $this->settings_model->isDisabled(44);
		if(!$all)
		{
			$roles = $this->cache->get('user_roles_' . $user_id);
			if ($roles && in_array(1, $roles)) {
				$is_admin = 1;
			} else {
				$this->db->select('RoleID');
				$this->db->from(USERROLES);
				$this->db->where('UserID', $user_id);
				$this->db->where('RoleID', '1');
				$query = $this->db->get();
				if ($query->num_rows()) {
					
					$is_admin = 1;
				}
			}
			if($is_admin == 1)
			{
                            if(!$announcements_disbaled){
                                $data[] = array('Value' => '7', 'Label' => 'Announcement');
                            }
                            $this->db->select('ActivityTypeID');
                            $this->db->from(ACTIVITYTYPE);
                            $this->db->where_in('ActivityTypeID',[36, 37]);
                            $this->db->where('StatusID','2');
                            $activity_type_query = $this->db->get();
                            $activity_types = $activity_type_query->result_array();
                            foreach($activity_types as $activity_type)
                            {
                                if(!empty($activity_type['ActivityTypeID']) && $activity_type['ActivityTypeID'] == 36 && !$visual_post_disbaled) {
                                    $data[] = array('Value' => '8', 'Label' => 'Visual Post');
                                }

                                if(!empty($activity_type['ActivityTypeID']) && $activity_type['ActivityTypeID'] == 37 && !$contest_disbaled) {
                                    $data[] = array('Value' => '9', 'Label' => 'Contest');
                                }

                            }
				
			}
		}
		else
		{
                    if(!$announcements_disbaled){
                        $data[] = array('Value' => '7', 'Label' => 'Announcement');
                    }
                    $data[] = array('Value' => '4', 'Label' => 'Article');
                    $data[] = array('Value' => '8', 'Label' => 'Visual Post');
		}
		if($article_disbaled) {
                    $key = array_search('4', array_column($data, 'Value'));
                    if ($key !== FALSE) {
                        array_splice($data, $key, 1);
                    }
                }
		return $data;
	}

	/**
	 * [save_introduction Used to SAVE introduction of the user]
	 * @param  [int] $user_id            [Logged in User ID]
	 * @param  [array] $user_details 	 [array of user introduction]
	 */
	public function save_user_info($user_id, $user_details, $table = USERDETAILS) {
		if (CACHE_ENABLE) {
			$this->cache->delete('user_profile_' . $user_id);
		}
		$data = array();
		foreach ($user_details as $key => $val) {
			if (is_array($val)) {
				$value = implode(',', $val);
				$data[$key] = $value;
			} else {
				$data[$key] = $val;
			}
		}
		$this->db->where('UserID', $user_id);
		$this->db->update($table, $data);
                
                //Update newsletter subscriber details
                $this->load->model('settings_model');
                if (!$this->settings_model->isDisabled(35)) {
                    $this->load->model(array('admin/newsletter/newsletter_users_model'));
                    $this->newsletter_users_model->save_user_info($user_id, $user_details);
                }
	}

	
	//get user by unique attributes like username email etc.
	/**
	 *  @param array $request_data
	 *  @param string|array $user_fields
	 */
	public function get_user_by_attribute($request_data, $user_fields = '') {
		if (isset($request_data)) {
			$user_fields = (!empty($user_fields)) ? $user_fields : 'UserID';
			$this->db->select($user_fields);
			$this->db->from(USERS);
			foreach ($request_data as $key => $value) {
				# code...
				$this->db->where($key, $value);
			}
			$query = $this->db->get();
			return $query->row_array();
		}
	}

	/**
	 * @name: update_sticky_preference [To update sticky preference of user]
	 * @param $user_id int [logged in user]
	 * @param $sticky_preference int [Can be '0' or '1' ]
	 */
	function update_sticky_preference($user_id, $sticky_preference,$sticky_by=0) {
        if ($user_id)
        {
            if (CACHE_ENABLE) 
            {
                // $this->cache->delete('user_profile_new_'.$user_id);
                $this->cache->delete('user_profile_'.$user_id);
            } 
            
//            if($sticky_by==1)
//                $this->db->set('MyStickyPreference', $sticky_preference);
//            elseif($sticky_by==2)
//                $this->db->set('OthersStickyPreference', $sticky_preference);
//            else
                $this->db->set('StickyPreference', $sticky_preference);//StickyPreference
            $this->db->where('UserID', $user_id);
            $this->db->update(USERS);
        }
    }

	/**
	 * @name: get_sticky_preference [To get sticky preference of user]
	 * @param $user_id int [logged in user]
	 * @return [Int] [User Sticky Preference]
	 */
	function get_sticky_preference($user_id,$sticky_by=0) {
		if ($user_id) {
			$this->db->select('StickyPreference'); //StickyPreference
			$this->db->where('UserID', $user_id);
			$result = $this->db->get(USERS)->row_array();
			if($sticky_by == 1)                
                return isset($result['MyStickyPreference']) ? $result['MyStickyPreference'] : '1';
            elseif($sticky_by == 2)
                return isset($result['OthersStickyPreference']) ? $result['OthersStickyPreference'] : '1';
            else
                return isset($result['StickyPreference']) ? $result['StickyPreference'] : '1';
		}
	}


	/**
	 * [delete_user_rights_cache Used to delete users rights cache file ]
	 * @return []                  []
	 */
	public function delete_user_rights_cache() {
		if (CACHE_ENABLE) {
			$this->db->select('U.UserID AS userid', FALSE);
			$this->db->from(USERS . "  U ");
            $this->db->where("StatusID", 2);
            $this->db->order_by('U.LastLoginDate', 'DESC');
            
			$query = $this->db->get();
			$result = $query->result_array();
			foreach ($result as $key => $res) {
				$this->cache->delete('user_rights_' . $res['userid']);
			}
		}
	}

	public function set_profile_job($page_no = 1, $page_size = 100) {
		initiate_worker_job('cache_all_profile', array('page_no' => $page_no, 'page_size' => $page_size, 'user_id' => ''));
	}
	public function cache_all_profile($page_no, $page_size, $user_id) {
		$this->db->select('UserID');
		$this->db->from(USERS);
		$this->db->where_in('StatusID', array(1, 2));
		if (!empty($user_id)) {
			if (CACHE_ENABLE) {
				$this->cache->delete('user_profile_' . $user_id);
			}
			$this->db->where('UserID', $user_id);
		}
		$this->db->order_by('UserID', 'DESC');
		if (!empty($page_no) && !empty($page_size) && empty($activity_id)) {
			$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
		}
		$result = $this->db->get();
		$q = $this->db->last_query();

		if ($result->num_rows()) {
			foreach ($result->result_array() as $res) {
				$user_id = $res['UserID'];
				$profile_cache = array();
				if (CACHE_ENABLE) {
                                    $profile_cache = $this->cache->get('user_profile_' . $user_id);
                                    if(!is_array($profile_cache)){ 
                                        $profile_cache = "";
                                    }
				}
				if (empty($profile_cache)) {
					initiate_worker_job('profile_cache', array('user_id' => $user_id));
				}
			}
		}
	}

	/**
	 * Function Name: deactivated_account
	 * @param user_id
	 * Description: used to deactivate user account by self.
	 */
	function deactivated_account($user_id) {
		/* Update user status to deactivated */
		$this->db->limit(1);
		$this->db->where('UserID', $user_id);
		$this->db->update(USERS, array('StatusID' => 20));
	}
        
        /**
	 * Function to set user status from suspend to active if user suspend date end
	 * 
	 */
        public function set_user_status_suspend_to_active() {
            $today = get_current_date('%Y-%m-%d');
            //$suspend_end_user_query = "SELECT U.UserID FROM ".USERS." U WHERE U.StatusID = 23 AND U.AccountSuspendTill < '$today' ";
            
            $user_ids = [];
            $this->db->select("GROUP_CONCAT(U.UserID) AS UserIDs", false);
            $this->db->from(USERS . ' U');
            $this->db->where(" U.AccountSuspendTill < '$today' ", NULL, FALSE);
            $this->db->where('U.StatusID', 23);
            $query = $this->db->get();
            $userIds = $query->row_array();
            
            $userIds = isset($userIds['UserIDs']) ? $userIds['UserIDs'] : '';
            if(!$userIds) {
                return;
            }
            $this->db->where("UserID IN( $userIds )", NULL, FALSE);
            $this->db->update(USERS, array('StatusID' => 2));
        }

    /**
    * [check and redirect user to page according to the no of follower and interest ]
    * @return [Array]
    */

    function check_interest_category($user_id="", $is_app=0){
    	
    	$minimum_interest = MINIMUM_SELECTION;
    	$redirect_url = '';
        $is_completed = 0;
    	$this->load->model('settings_model');
        if(!$this->settings_model->isDisabled(31)){
            $is_category = false;
            $this->load->model(array('interest/interest_model'));
            $total_count = $this->interest_model->get_user_interest($user_id,1,10,TRUE);
            if($total_count < $minimum_interest){
                $redirect_url = 'profilesetting/interest';
                $is_completed = 1;
            }
        } else {
            $is_category = true;
            $total_count = $this->check_category_membership($user_id);
            if($total_count < $minimum_interest){
                $redirect_url = 'profilesetting/categories';
                $is_completed = 2;
            }
        }

        if($redirect_url==''){
            $following_count = $this->following_count($user_id);
            if($following_count < $minimum_interest){
                if($is_category){
                        $redirect_url = 'profilesetting/top_contributors';	
                        $is_completed = 4;
                } else {
                        $redirect_url = 'profilesetting/follow_people';
                        $is_completed = 3;
                }
            }
        }
        if ($is_app == 1) {
            return $is_completed;
        } else {
            return $redirect_url;
        }
        
    }

    /**
     * Function: check_category_membership
     * Description : Check category permission
     * @param type $forum_category_id
     * @param type $user_id
     * @return type
     */
    function check_category_membership($user_id)
    {
		$this->db->select('ForumCategoryMemberID'); 
		$this->db->from(FORUMCATEGORYMEMBER.' as FCM'); 
		$this->db->join(FORUMCATEGORY.' FC ','FC.ForumCategoryID = FCM.ForumCategoryID');
        $this->db->join(FORUM. ' F','FC.ForumID = F.ForumID AND F.StatusID=2');
		$this->db->where('ModuleID',3); 
		$this->db->where('FC.StatusID',2);
        $this->db->where('FC.ParentCategoryID', 0);
		$this->db->where('FCM.ModuleEntityID',$user_id); 
		$query = $this->db->get();
		return $query->num_rows();
    }

    function following_count($user_id) {
		$TotalRecords = $this->db->query("SELECT COUNT(F.UserID) as count FROM Follow F WHERE F.UserID= $user_id AND F.Type='User' AND F.StatusID='2'")->row()->count;
		return $TotalRecords;
	}

	function get_designation($user_id)
	{
		$designation = '';
		$this->db->select('Designation');
		$this->db->from(WORKEXPERIENCE);
		$this->db->where('UserID',$user_id);
		$this->db->order_by('CurrentlyWorkHere','DESC');
		$this->db->order_by('ModifiedDate','DESC');
		$this->db->limit(1);
		$query = $this->db->get();
		if($query->num_rows())
		{
			$row = $query->row();
			$designation = $row->Designation;
		}
		return $designation;
	}
        
    function directory($page_no, $page_size, $search_keyword, $is_admin, $count_only = 0, $user_id, $order_by = "Recent", $sort_by = "DESC", $ward_id=0) {
        $admin_data = array();
       /* if($page_no == 1 && $order_by == 'Recent' && $count_only == 0) {
            //$admin_data = $this->directory_admin($search_keyword, $is_admin, $user_id, $order_by = "Recent", $sort_by = "DESC");
        }
        $admin_ids = array();
        if($order_by == 'Recent' && $count_only == 0) {
            //$admin_ids = $this->directory_admin($search_keyword, $is_admin, $user_id, $order_by = "Recent", $sort_by = "DESC", TRUE);
        }
        * 
        */
        
        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as EntityName, U.Email, U.Gender, U.UserGUID, U.UserID, UD.TimeZoneID,U.StatusID");
        $this->db->select('IFNULL(U.PhoneNumber,"") as PhoneNumber', FALSE);
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID');
        if(!empty($ward_id)) {
            $this->db->where('L.WardID', $ward_id);
        }
       //$this->db->where('U.UserID !=', $user_id);
        if($is_admin) {
            $this->db->where_not_in('U.StatusID', array(3));
        } else {
            $this->db->where_not_in('U.StatusID', array(3, 4));
        }
        if (!empty($search_keyword)) {
            $this->db->where("(UD.Occupation like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.FirstName like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_keyword) . "%' or concat(U.FirstName,' ',U.LastName) like '%" . $this->db->escape_like_str($search_keyword) . "%')");
        }
        
        if (!$count_only && $page_no && $page_size) {            
           /* if($order_by == 'Recent') {
                //$this->db->where('UD.LocalityID', $this->LocalityID);
                if(!empty($admin_ids)) {
                    $this->db->where_not_in('U.UserID', $admin_ids);
                }
            } else { */
                //$this->db->join(USERROLES . ' UR', 'UR.UserID = U.UserID');   
                //$this->db->where("(UR.RoleID IN (1, 2,6))");
            //}        
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        
        
        if ($count_only) {
            $this->db->select('COUNT(DISTINCT U.UserID) as TotalRow ' );
            //$this->db->join(USERROLES . ' UR', 'UR.UserID = U.UserID');   
            //$this->db->where("(UR.RoleID IN (1,2,6))");
            
            $query = $this->db->get();
            $count_data=$query->row_array();
            return $count_data['TotalRow'];
        }
        if($order_by == 'Name') {
            $this->db->order_by('U.FirstName', $sort_by);
            $this->db->order_by('U.LastName', $sort_by);
        } else {
            $this->db->order_by('U.CreatedDate', $sort_by);
        }
        $this->db->group_by('U.UserID');
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $directory_data = array();
        
        $user_data = array();
        if($query->num_rows()) {
            foreach ($query->result_array() as $userdata) {
                if(!$is_admin) {
                    $userdata['PhoneNumber'] = "";
                }
                if($userdata['UserID'] == ADMIN_USER_ID) {
                    $userdata['HouseNumber'] = ''; 
                 }
                // $userdata['Location'] = $this->get_user_location($userdata['UserID']); 
                // $userdata['IsAdmin'] = 0;
                $userdata['IsAdmin'] = $this->is_super_admin($userdata['UserID'], 1);
                if(empty($userdata['LocalityID'])) {
                    $userdata['LocalityID'] = 0;
                }
                $userdata['Locality'] = array(
                        "Name" => $userdata['Name'], 
                        "HindiName"=> $userdata['HindiName'], 
                        "ShortName"=> $userdata['ShortName'],  
                        "LocalityID" => $userdata['LocalityID']);
                    
                    unset($userdata['Name']);
                    unset($userdata['HindiName']);
                    unset($userdata['ShortName']);
                    unset($userdata['LocalityID']);
                /* if($userdata['IsAdmin'] && $order_by != 'Name') {
                    $admin_data[] = $userdata;
                } else { */
                    $user_data[] = $userdata;
               // }
                //$directory_data[] = $userdata;
            }
        }
        $directory_data = array_merge($admin_data, $user_data);
       // print_r($admin_data);
        //print_r($user_data);
        return $directory_data;
    } 
    

    function directory_tc($page_no, $page_size, $search_keyword, $is_admin, $count_only = 0, $user_id, $order_by = "Recent", $sort_by = "DESC", $ward_id=0) {
        $admin_data = array();
       
        
        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as EntityName, U.Email, U.Gender, U.UserGUID, U.UserID, UD.TimeZoneID,U.StatusID");
        $this->db->select('IFNULL(U.PhoneNumber,"") as PhoneNumber', FALSE);
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID');
        if(!empty($ward_id)) {
            $this->db->where('L.WardID', $ward_id);
        }
        if($is_admin) {
            $this->db->where_not_in('U.StatusID', array(3));
        } else {
            $this->db->where_not_in('U.StatusID', array(3, 4));
        }
        if (!empty($search_keyword)) {
            $this->db->where("(UD.Occupation like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.FirstName like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_keyword) . "%' or concat(U.FirstName,' ',U.LastName) like '%" . $this->db->escape_like_str($search_keyword) . "%')");
        }
        
        if (!$count_only && $page_no && $page_size) {           
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        
        
        if ($count_only) {
            $this->db->select('COUNT(DISTINCT U.UserID) as TotalRow ' );
            
            $query = $this->db->get();
            $count_data=$query->row_array();
            return $count_data['TotalRow'];
        }
        if($order_by == 'Name') {
            $this->db->order_by('U.FirstName', $sort_by);
            $this->db->order_by('U.LastName', $sort_by);
        } else {
            $this->db->order_by('U.CreatedDate', $sort_by);
        }
        $this->db->group_by('U.UserID');
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $directory_data = array();
        
        $user_data = array();
        if($query->num_rows()) {
            foreach ($query->result_array() as $userdata) {
                if(!$is_admin) {
                    //$userdata['PhoneNumber'] = "";
                }
                if($userdata['UserID'] == ADMIN_USER_ID) {
                    $userdata['HouseNumber'] = ''; 
                 }
                // $userdata['Location'] = $this->get_user_location($userdata['UserID']); 
                // $userdata['IsAdmin'] = 0;
                $userdata['IsAdmin'] = $this->is_super_admin($userdata['UserID'], 1);
                if(empty($userdata['LocalityID'])) {
                    $userdata['LocalityID'] = 0;
                }
                $userdata['Locality'] = array(
                        "Name" => $userdata['Name'], 
                        "HindiName"=> $userdata['HindiName'], 
                        "ShortName"=> $userdata['ShortName'],  
                        "LocalityID" => $userdata['LocalityID']);
                    
                    unset($userdata['Name']);
                    unset($userdata['HindiName']);
                    unset($userdata['ShortName']);
                    unset($userdata['LocalityID']);
                
                    $user_data[] = $userdata;
               
            }
        }
        $directory_data = $user_data;
        return $directory_data;
    } 

    public function directory_admin($search_keyword, $is_admin, $user_id, $order_by = "Recent", $sort_by = "DESC", $only_ids=FALSE) {
                     
        
        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as EntityName, U.Email, U.Gender, U.UserGUID, U.UserID, UD.TimeZoneID,U.StatusID");
        $this->db->select('IFNULL(U.PhoneNumber,"") as PhoneNumber', FALSE);
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID', 'left');
        $this->db->join(USERROLES . ' UR', 'UR.UserID = U.UserID');        
        $this->db->where_not_in('U.StatusID', array(3));
        $this->db->where_in('RoleID', array(1,6));
        //$this->db->where("((UR.RoleID = 6 AND UD.LocalityID=".$this->LocalityID.") OR (RoleID = 1))");
        if (!empty($search_keyword)) {
            $this->db->where("(UD.Occupation like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.FirstName like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_keyword) . "%' or concat(U.FirstName,' ',U.LastName) like '%" . $this->db->escape_like_str($search_keyword) . "%')");
        }
        $this->db->group_by('U.UserID');
        $query = $this->db->get();        
        $admin_data = array();
        if($query->num_rows()) {
            foreach ($query->result_array() as $userdata) {
                if($only_ids) {
                    $admin_data[] = $userdata['UserID'];   
                } else {
                    if(!$is_admin) {
                        $userdata['PhoneNumber'] = "";
                    }
                    if($userdata['UserID'] == ADMIN_USER_ID) {
                       $userdata['HouseNumber'] = ''; 
                    }
                    //$userdata['Location'] = $this->get_user_location($userdata['UserID']);                
                    $userdata['IsAdmin'] = $this->is_super_admin($userdata['UserID'], 1);               
                    if(empty($userdata['LocalityID'])) {
                        $userdata['LocalityID'] = 0;
                    }
                    $userdata['Locality'] = array(
                        "Name" => $userdata['Name'], 
                        "HindiName"=> $userdata['HindiName'], 
                        "ShortName"=> $userdata['ShortName'],  
                        "LocalityID" => $userdata['LocalityID']);
                    
                    unset($userdata['Name']);
                    unset($userdata['HindiName']);
                    unset($userdata['ShortName']);
                    unset($userdata['LocalityID']);
                    $admin_data[] = $userdata;
                }                
            }
        }
        return $admin_data;
    }
    
    /**
     * Function for change status of a user
     * Parameters : UserID, Status
     * Return : true
     */
    public function changeStatus($user_id, $status, $posted_data = array()) {
        $data = array('StatusID' => $status, 'ModifiedDate' => date('Y-m-d H:i:s'));       
        
        $this->db->where('UserID', $user_id);
        if($status == 4) {
            $this->db->where('StatusID != 3', NULL, FALSE);
        }        
        $this->db->update(USERS, $data);

        if ($status == 3) {
            $data = array('StatusID' => $status, 'ModifiedDate' => date('Y-m-d H:i:s'));
            
            $this->db->where('EntityID', $user_id);
            $this->db->where('EntityType', 'User');
            $this->db->update(PROFILEURL, $data);
        }

        if ($status == 4) {
            $this->db->delete(ACTIVELOGINS, array('UserID' => $user_id));            
        }
        return true;
    }
    
    function make_admin($user_id, $role_id=6) {        
        $this->db->select('UserRoleID');
        $this->db->from(USERROLES);
        $this->db->where('UserID',$user_id);
        $this->db->where('RoleID',$role_id);
        $this->db->where('BusinessUnitID',1);
        $query = $this->db->get();
        if($query->num_rows() == 0) {
            if (CACHE_ENABLE) {
                $this->cache->delete('user_rights_'.$user_id);
                $this->cache->delete('user_roles_' . $user_id);
            }
            $data = array('UserID' => $user_id, 'RoleID' => $role_id, 'BusinessUnitID' => 1);
            $this->db->insert(USERROLES, $data);
        }
    }
    
    function remove_admin($user_id, $role_id=6) {        
        $this->db->select('UserRoleID');
        $this->db->from(USERROLES);
        $this->db->where('UserID',$user_id);
        $this->db->where('RoleID',$role_id);
        $this->db->where('BusinessUnitID',1);
        $query = $this->db->get();
        if($query->num_rows()) {
            $this->db->where('UserID', $user_id);
            $this->db->where('RoleID', $role_id);
            $this->db->where('BusinessUnitID', 1);
            $this->db->delete(USERROLES);
            
            if (CACHE_ENABLE) {
                $this->cache->delete('user_rights_'.$user_id);
                $this->cache->delete('user_roles_' . $user_id);
            }
        }
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $device_type_id
     * @param type $app_version
     */
    function update_app_version($user_id, $device_type_id, $app_version) {        
        switch($device_type_id) {
            case '2':
                $this->db->set('IOSAppVersion', $app_version);
                break;
            case '3':
                $this->db->set('AndroidAppVersion', $app_version);        
                break;
        }
        $this->db->where('UserID', $user_id);
        $this->db->update(USERDETAILS);
        if (CACHE_ENABLE) {
            $this->cache->delete('user_profile_' . $user_id);
        }
    }
    
    function get_user_details($user_id) {
        if (CACHE_ENABLE) {
            $userdata = $this->cache->get('user_profile_' . $user_id);
            if(!is_array($userdata)){ 
                $userdata = '';
            }
        }
        if (empty($userdata)) {
            $this->db->select("U.FirstName, U.LastName, U.UserGUID, UD.LocalityID, U.CanCreatePoll");            
            $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
            $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
            $this->db->from(USERS . ' U');
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
            $this->db->where('U.UserID', $user_id);
            $query = $this->db->get();
            $userdata = $query->row_array();
            $userdata['Locality'] = array('Name' => '', 'HindiName'=>'', 'ShortName'=>'',  'LocalityID' => 0, 'IsPollAllowed' => 1, 'WName'=>'', 'WNumber'=>'', 'WID'=>'');
            if(isset($userdata['LocalityID'])) {
                $this->load->model(array('locality/locality_model'));
                $userdata['Locality'] = $this->locality_model->get_locality($userdata['LocalityID']);
            }  
            if(empty($userdata['Locality']['IsPollAllowed'])) {
                $userdata['CanCreatePoll']  = 0;
            }
            if($this->settings_model->isDisabled(30)){ //check for poll module
                $userdata['CanCreatePoll']  = 0;
            }
            unset($userdata['LocalityID']);
        }
        if(empty($userdata['Locality']['LocalityID'])) {
            $userdata['Locality']['LocalityID'] = 0;
        }
        return $userdata;
    }
    
    /**
     * Used to change user locality
     * @param type $user_id
     * @param type $locality_id
     * @return boolean
     */
    function change_locality($user_id, $locality_id) {        
        $data = array('LocalityID' => $locality_id);               
        $this->db->where('UserID', $user_id);
        $this->db->update(USERDETAILS, $data);        
        if (CACHE_ENABLE) {
            $this->cache->delete('user_profile_' . $user_id);
        }
        return true;
    }

    /**
     * [To get list of vip users]
     * @param [array] $data   [posted data]
     * @return [array]      [users result]
     */
    public function get_vip_user($data) {         
        //$ward_id = $data['WID'];
        $order_by = safe_array_key($data, 'OrderBy', 'Name');
        $user_id = safe_array_key($data, 'UserID', 0);          
        $users = array();
        /* if (CACHE_ENABLE && $order_by == 'Name') {
            $users = $this->cache->get('vpu');   
            if (!is_array($users)) {
                $users = array();
            }         
        }
        */
        $followers = array();
        $is_follow_disabled = $this->settings_model->isDisabled(11);
        if(!$is_follow_disabled) {
            $followers = $this->user_model->get_followers_list();              
        }
        $followers[] = 0;
        
        if(empty($users)) {       
            $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID, U.UserID");
            $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
            $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
            $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
            $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
            $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
            $this->db->from(USERS . ' U');            
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
            $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID');
            $this->db->where_not_in('U.StatusID', array(3,4));
            $this->db->where('U.IsVIP', 1);
            $this->db->where_not_in('U.UserID', $followers);
            if($order_by == 'Name') {
                $this->db->order_by('U.FirstName', 'ASC');
                $this->db->order_by('U.LastName', 'ASC');  
            } else {
                 $this->db->order_by('U.LastLoginDate', 'DESC');
            }

            $query = $this->db->get();       
            if($query->num_rows()) {
                $result = $query->result_array();
                foreach ($result as $user){
                    if(!$is_follow_disabled) {
                        $user['IsFollow'] = 0;
                        if ($user['UserID'] == $user_id) {
                            $user['IsFollow'] = 2;
                        } else if (in_array($user['UserID'], $followers)) {
                            $user['IsFollow'] = 1;
                        } 
                        $IsAdmin = $this->user_model->is_super_admin($user['UserID']);
                        $this->load->model('activity/activity_model');
                        $IsAdminGuid = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID)); // admin set from config page
                        if($IsAdmin || $user['UserID']==$IsAdminGuid )
                        {
                            $user['IsFollow']=2;
                        }
                    }
                    
                    $user['Locality'] = array(
                        "Name" => $user['Name'], 
                        "HindiName"=> $user['HindiName'], 
                        "ShortName"=> $user['ShortName'],  
                        "LocalityID" => $user['LocalityID']);
                    
                    unset($user['Name']);
                    unset($user['HindiName']);
                    unset($user['ShortName']);
                    unset($user['LocalityID']);
                    $users[] = $user;
                }
            }

           /* if (CACHE_ENABLE && $order_by == 'Name') {
                $this->cache->save('vpu', $users, 300);
            }
            */            
        }
        return $users;
    }

    /**
     * [To get list of vip users]
     * @param [array] $data   [posted data]
     * @return [array]      [users result]
     */
    public function get_association_user($data) {         
        //$ward_id = $data['WID'];
        $order_by = safe_array_key($data, 'OrderBy', 'Name');
        $user_id = safe_array_key($data, 'UserID', 0);        
        $users = array();
       /* if (CACHE_ENABLE && $order_by == 'Name') {
            $users = $this->cache->get('assu');   
            if (!is_array($users)) {
                $users = array();
            }         
        }
        */
        $followers = array();
        $is_follow_disabled = $this->settings_model->isDisabled(11);
        if(!$is_follow_disabled) {
            $followers = $this->user_model->get_followers_list();  
        } 
        $followers[] = 0;
        if(empty($users)) {       
            $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, U.UserGUID, U.UserID");
            $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
            $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
            $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
            $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
            $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
            $this->db->from(USERS . ' U');            
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
            $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID');
            $this->db->where_not_in('U.StatusID', array(3,4));
            $this->db->where('U.IsAssociation', 1);
            $this->db->where_not_in('U.UserID', $followers);
            if($order_by == 'Name') {
                $this->db->order_by('U.FirstName', 'ASC');
                $this->db->order_by('U.LastName', 'ASC');  
            } else {
                 $this->db->order_by('U.LastLoginDate', 'DESC');
            }

            $query = $this->db->get();       
            if($query->num_rows()) {
                $result = $query->result_array();
                foreach ($result as $user){
                    if(!$is_follow_disabled) {
                        $user['IsFollow'] = 0;
                        if ($user['UserID'] == $user_id) {
                            $user['IsFollow'] = 2;
                        } else if (in_array($user['UserID'], $followers)) {
                            $user['IsFollow'] = 1;
                        } 

                        $IsAdmin = $this->user_model->is_super_admin($user['UserID']);
                        $this->load->model('activity/activity_model');
                        $IsAdminGuid = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID)); // admin set from config page
                        if($IsAdmin || $user['UserID']==$IsAdminGuid )
                        {
                            $user['IsFollow']=2;
                        }
                    }

                    $user['Locality'] = array(
                        "Name" => $user['Name'], 
                        "HindiName"=> $user['HindiName'], 
                        "ShortName"=> $user['ShortName'],  
                        "LocalityID" => $user['LocalityID']);
                    
                    unset($user['UserID']);
                    unset($user['Name']);
                    unset($user['HindiName']);
                    unset($user['ShortName']);
                    unset($user['LocalityID']);
                    $users[] = $user;
                }
            }

          /*  if (CACHE_ENABLE && $order_by == 'Name') {
                $this->cache->save('assu', $users, 300);
            }
            */            
        }
        return $users;
    }

    /** 
     * toggle_block_user used to block/unblock user
     * @param int $user_id
     * @param int $entity_id
     * @return int
     */

    function toggle_block_user($user_id, $entity_id, $module_id, $module_entity_id)
    {
        $this->db->where('EntityID', $entity_id);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $result = $this->db->get(BLOCKUSER);
        $return = 2;
        if ($result->num_rows()) {
            $row = $result->row();
            $this->db->where('ModuleID', $module_id);
            $this->db->where('ModuleEntityID', $module_entity_id);
            $this->db->where('UserID', $user_id);
            $this->db->where('EntityID', $entity_id);
            $this->db->delete(BLOCKUSER);
            $return = 1;
        } else {
            $data = array('BlockUserGUID' => get_guid(), 'UserID' => $user_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'EntityID' => $entity_id, 'StatusID' => '2', 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->insert(BLOCKUSER, $data); 
        }
        if ($module_id == 3)
        {
            if (CACHE_ENABLE) {
               $this->cache->delete('block_users_'.$user_id);
               $this->cache->delete('block_users_'.$module_entity_id);
            }
        }       
        return $return;    
    }

    /**
     * [get_blocked_user_list Get list of blocked user list]
     * @param  [int]  $user_id   [User ID]
     * @param  [string]  $search_key [Search Keyword]
     * @param  [int]       $page_no        [Page No]
     * @param  [int]       $page_size      [Page Size]    
     * @return [array]          [block user list]
     */
    function get_blocked_user_list($user_id, $search_key, $page_no, $page_size) {
        $this->db->select('U.UserID, U.ProfilePicture, U.UserGUID,CONCAT(U.FirstName," ",U.LastName) as Name',FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->from(BLOCKUSER.' BU');
        $this->db->join(USERS.' U','BU.EntityID=U.UserID AND BU.ModuleID=3');        
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->where_not_in('U.StatusID',array(3,4));
        if(!empty($search_key)) {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%'.$search_key.'%" OR U.LastName LIKE "%'.$search_key.'%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%'.$search_key.'%")',NULL,FALSE);
        }
        $this->db->where('BU.UserID',$user_id);

        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();

        $returnArr['Data'] = array();
        $returnArr['total_records'] = $temp_q->num_rows();

        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $query = $this->db->get();

        //echo $this->db->last_query();
        if($query->num_rows()>0) {
            $returnArr['Data'] = $query->result_array();          
        }
        return $returnArr;
    }

    function check_blocked_status($user_id, $block_user_id) {
        $this->db->select('StatusID');
        $this->db->where('UserID', $user_id);
        $this->db->where('EntityID', $block_user_id);
        $this->db->limit('1');
        $sql = $this->db->get(BLOCKUSER);
        $return_flag = 0;
        if ($sql->num_rows() > 0) {
            $return_flag = 1;
        }
        return $return_flag;
    }

    /**
     * Used to get preferred category
     */
    function get_preferred_category($user_id) {
        if(API_VERSION == "v4"){
            $this->load->model(array('tag/tag_model'));
            $category_data = $this->tag_model->top_preferred_tags(20);
        } else {
            $category_data = $this->get_preferred_category_by_tag_id(20);            
        }

        foreach ($category_data as $key => $category) {
            $category['IsPreferred'] = $this->is_preferred_categories($user_id, $category);
            $category_data[$key] = $category;
        }
        return $category_data;
    }

     /**
     * Used to get preferred category for tag
     */
    function get_preferred_category_by_tag_id($tag_id, $search_keyword='') {
        $tag_categories = array();        

        $this->db->select('DISTINCT T.TagCategoryID, T.Name, TC.TagID', false);
        $this->db->select('IFNULL(T.Icon,"") as Icon', FALSE);
        $this->db->from(TAGSOFTAGCATEGORY.' TC');
        $this->db->join(TAGCATEGORY . ' T', 'T.TagCategoryID = TC.TagCategoryID');
        $this->db->where('TC.TagID', $tag_id);
        if ($search_keyword) {
            $this->db->select('(CASE WHEN Name LIKE "'.$search_keyword.'%" THEN 1 ELSE 2 END) AS sortpreference');
            $this->db->like('T.Name', $search_keyword);
            $this->db->order_by('sortpreference', 'ASC');
        } else {
            if($tag_id == 20) {
                $this->db->where_in('T.TagCategoryID', array(30, 42, 40, 25, 24, 22, 29, 21, 33, 60));
            }
            $this->db->order_by('T.DisplayOrder', 'ASC');
        }
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $tag_categories = $query->result_array();
        }
        return $tag_categories;
    }

    /**
     * Function: save_preferred_categories
     * Description : Save user categories
     * @param type $user_id
     * @param type $categories
     * @return array
     */
    function save_preferred_categories($user_id, $categories) {
        $this->db->select('TagCategoryID');
        $this->db->from(USERTAGCATEGORY);
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        
        $db_categories = [];
        foreach ($query->result_array() as $cat) {
            $db_categories[] = $cat['TagCategoryID'];
        }
        
        $new_categories = array_diff($categories, $db_categories);
        $deleted_categories = array_diff($db_categories, $categories);
        
        
        if(count($deleted_categories)) {
            $this->db->where_in('TagCategoryID', $deleted_categories); 
            $this->db->where('UserID', $user_id); 
            $this->db->delete(USERTAGCATEGORY);
        }
        
        
        $insert_arr = [];
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        foreach ($new_categories as $new_category) {
            $insert_arr[] = array(
                'TagCategoryID' => $new_category,
                'UserID' => $user_id,
                'ModifiedDate' => $current_date,
            );
        }
        
        if(count($insert_arr)) {
            $this->db->insert_batch(USERTAGCATEGORY, $insert_arr);
        }
        
    }

    /**
     * Function: save_preferred_categories
     * Description : Save user categories
     * @param type $user_id
     * @param type $tags
     * @return array
     */
    function save_preferred_tags($user_id, $tags) {
        $this->db->select('TagID');
        $this->db->from(USERTAGCATEGORY);
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        
        $db_tags = [];
        foreach ($query->result_array() as $cat) {
            $db_tags[] = $cat['TagID'];
        }
        
        $new_tags = array_diff($tags, $db_tags);
        $deleted_tags = array_diff($db_tags, $tags);
        
        
        if(count($deleted_tags)) {
            $this->db->where_in('TagID', $deleted_tags); 
            $this->db->where('UserID', $user_id); 
            $this->db->delete(USERTAGCATEGORY);
        }        
        
        $insert_arr = [];
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        foreach ($new_tags as $new_tag) {
            $insert_arr[] = array(
                'TagID' => $new_tag,
                'UserID' => $user_id,
                'ModifiedDate' => $current_date,
            );
        }
        
        if(count($insert_arr)) {
            $this->db->insert_batch(USERTAGCATEGORY, $insert_arr);
        }        
    }

    function is_preferred_categories($user_id, $category) {
        $this->db->select('UserID');
        $this->db->from(USERTAGCATEGORY);
        $this->db->where('UserID', $user_id);
        if(isset($category['TagID'])) {
            $this->db->where('TagID', $category['TagID']);
        } else {
            $this->db->where('TagCategoryID', $category['TagCategoryID']);
        }
        
        $this->db->limit(1);
        $query = $this->db->get();        
        if($query->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
        
    }

     /**
     * [check message button status ]
     * @param  [type]  [$current_user_id]
     * @param  [type] $user_id    [Logged in user ID]
     */
    public function check_message_button_status($current_user_id, $user_id) {        
        $status = 1;
        if($current_user_id == $user_id) {
            $status = 0;
        }

        if($this->settings_model->isDisabled(25)){
            $status = 0;
        }

        if ($current_user_id != $user_id) {
            $this->load->model(array('privacy/privacy_model'));
            $status = $this->privacy_model->check_privacy($current_user_id, $user_id, 'message');            
        }
        return $status;
    } 
    
    public function save_user_profession($profession_id, $user_id) {
        if (CACHE_ENABLE) {
            $this->cache->delete('usrprof_' . $user_id);
        }
        $this->db->select('UP.UserProfessionID');
        $this->db->from(USERPROFESSION . ' UP');
        $this->db->where('UP.UserID',$user_id, FALSE);
        $this->db->limit(1);
        $query = $this->db->get();
        $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $insert_data = array(
            "ProfessionID" => $profession_id,
            "CreatedDate" => $created_date
        );
    
        if($query->num_rows() == 0) {
            $insert_data['UserID'] = $user_id;
            $this->db->insert(USERPROFESSION, $insert_data);
        } else {
            $row = $query->row_array();
            $this->db->where('UserProfessionID', $row['UserProfessionID']);
            $this->db->update(USERPROFESSION, $insert_data);
        }
    }

    /**
     * Used to remove user profession
     * @param type $profession_id
     * @param type $user_id
     */
    function remove_user_profession($profession_id,  $user_id) {
        if (CACHE_ENABLE) {
            $this->cache->delete('usrprof_' . $user_id);
        }
        //$this->db->where('ProfessionID', $profession_id, FALSE);
        $this->db->where('UserID', $user_id, FALSE);
        $this->db->delete(USERPROFESSION);            
    }

    /**
     * [get_user_profession Used to get user profession]
     * @param  [int] $user_id [User ID]
     */
    function get_user_profession($user_id) {
        $user_profession = array();
        if (CACHE_ENABLE) {
            $user_profession = $this->cache->get('usrprof_' . $user_id);
        }
        
        if(empty($user_profession)) {
            $this->db->select('P.ProfessionID');
            $this->db->select('P.Name', FALSE);
            $this->db->select('IFNULL(P.Icon,"") as Icon', FALSE);
            $this->db->from(USERPROFESSION . ' UP');
            $this->db->join(PROFESSION . ' P', 'P.ProfessionID=UP.ProfessionID AND P.Status = 2');
            $this->db->where('UP.UserID',$user_id, FALSE);
            $this->db->limit(1);
            $query = $this->db->get();
            
            $user_profession = $query->result_array();
            if (CACHE_ENABLE) {
                $this->cache->save('usrprof_' . $user_id, $user_profession, CACHE_EXPIRATION);
            }
        }
        return $user_profession;
    }

    function profession_list($data) {
        $this->db->select('P.ProfessionID');
        $this->db->select('P.Name', FALSE);
        $this->db->select('IFNULL(P.Icon,"") as Icon', FALSE);
        $this->db->from(PROFESSION . ' P');
        $this->db->where('P.Status',2);
        $this->db->order_by('Name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }


    public function user_signup_sync($input_data) {
    	$user_detail = $this->get_single_row('*',USERS, array("UserID" => $input_data["UserID"]));
    	// if user doesnt exist
        $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $user_id = $input_data["UserID"];
        if(isset($input_data['Gender'])) {
            $input_data['Gender'] = 0;
            if($input_data['Gender'] == 'male') {
                $input_data['Gender'] = 1;
            } else if($input_data['Gender'] == 'female') {
                $input_data['Gender'] = 2; 
            }
        }
        
        $dob = null;
        if(isset($input_data['DOB'])) {
            $dob = $input_data['DOB'];
            unset($input_data['DOB']);
        }

    	if(empty($user_detail)) {
            $WeekDayID = $this->get_week_day_id(date('l'));
            $TimeSlot = $this->get_time_slot();
            if(isset($input_data["DeviceTypeID"]) && in_array($input_data["DeviceTypeID"], array(1,2))) {
                $input_data["DeviceTypeID"] = ($input_data["DeviceTypeID"] == 1) ? 3 : 2;
            } else {
                $input_data["DeviceTypeID"] = 1;
            }

            $input_data['WeekDayID'] = $WeekDayID;
            $input_data['TimeSlotID'] = $TimeSlot;
            $input_data['CreatedDate'] = $created_date;
            $input_data['SourceID'] = 1;
            $input_data['UserTypeID'] = 3;
            $input_data['CanCreatePoll'] = 1;
            $input_data['StatusID'] = 2;
            
           

    		$this->db->insert(USERS, $input_data);

            $this->db->insert(USERROLES, array('UserID' => $user_id, 'RoleID' => 3));
    	
            $profilename = '';
            $locality_id = 1;
            $user_details = array(
                'RelationWithName' => '',
                'UserID' => $user_id,
                'ProfileName' => $profilename,
                'TimeZoneID' => 195,
                'DOB' => $dob,
                'LocalityID' => $locality_id);
            $this->db->insert(USERDETAILS, $user_details);
            /*  User role entry end here */
            
            $this->load->model(array('notification_model','privacy/privacy_model'));
            // Add privacy settings to low
            $this->privacy_model->save($user_id, 'low');            
            $this->notification_model->set_all_notification_on($user_id);

            //Create Default album
            create_default_album($user_id, 3, $user_id);

            //Making admin as default user to follow
            $follow_data = array('FollowingID' => getenv('ADMIN_USER_ID'), 'UserID' => $user_id,"new_signup"=>1);
			$this->load->model('follow/follow_model');
			$this->follow_model->follow($follow_data);

        } else {
            unset($input_data["DeviceTypeID"]);
            unset($input_data["IPAddress"]);
    		$this->db->where('UserID',$input_data["UserID"]);
			$this->db->update(USERS, $input_data);

            if(!empty($dob)) {
                $user_details = array(
                    'DOB' => $dob
                );
                $this->db->where('UserID',$input_data["UserID"]);
			    $this->db->update(USERDETAILS, $user_details);
            }

            if (CACHE_ENABLE) {
                $this->cache->delete('user_profile_' . $input_data["UserID"]);
            }
    	}
    	return true;
    }

    public function user_login_sync($user_data) {
        $isApp = 0;
        if(isset($user_data["DeviceTypeID"]) && in_array($user_data["DeviceTypeID"], array(1,2))) {
            $user_data["DeviceTypeID"] = ($user_data["DeviceTypeID"] == 1) ? 3 : 2;
            $isApp = 1;
        } else {
            $user_data["DeviceTypeID"] = 1;
        }

        $user_detail = $this->get_single_row('*',ACTIVELOGINS, array("UserID" => $user_data["UserID"], 'LoginSessionKey' => $user_data["LoginSessionKey"], 'DeviceTypeID' => $user_data["DeviceTypeID"]));
        $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
        if(empty($user_detail)) {
            $user_data['ResolutionID'] = 1;
            $user_data['LoginSourceID'] = 1;
            $user_data['CreatedDate'] = $created_date;
            $user_data['IsApp'] = $isApp;
            $user_data['BrowserID'] = check_browser();
            $this->db->insert(ACTIVELOGINS, $user_data);
        } else {
            $this->db->where('UserID',$user_data["UserID"]);
            $this->db->where('LoginSessionKey',$user_data["LoginSessionKey"]);
            $this->db->where('DeviceTypeID',$user_data["DeviceTypeID"]);
			$this->db->update(ACTIVELOGINS, $user_data);
        }
    }

    public function user_logout($user_data) {
        $this->db->where(array('LoginSessionKey' => $user_data['LoginSessionKey']));
        $this->db->delete(ACTIVELOGINS);
    }


    public function get_all_users()
    {
        $admin_id = getenv('ADMIN_USER_ID');

        $query = "select U.UserID from ".USERS." U LEFT JOIN ".FOLLOW." F ON F.UserID = U.UserID where U.UserID not in(select FL.UserID from ".FOLLOW." FL WHERE FL.FollowingID=$admin_id) and U.UserID !=$admin_id GROUP BY U.UserID";
        $result = $this->db->query($query);
        // echo $this->db->last_query();exit;
        return $result->result_array();
    }

    /**
     * this function is used to sync username in social db of user while signup.
     */

    public function sync_username($data)
    {
        if(isset($data) && isset($data['FirstName']))
        {
        $this->db->where('UserID',$data["UserID"]);
        $this->db->update(USERS, ['FirstName'=>$data['FirstName']]);
        $this->cache->delete('user_profile_' . $data["UserID"]);
        return true;
        }
    }

    public function old_user_sync($data) {
        if(!empty($data))
        {
            foreach($data as $key=>$input_data)
            {
                        $user_detail = $this->get_single_row('*',USERS, array("UserID" => $input_data["UserID"]));
                // if user doesnt exist
                $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
                $user_id = $input_data["UserID"];
                if(isset($input_data['Gender'])) {
                    $input_data['Gender'] = 0;
                    if($input_data['Gender'] == 'male') {
                        $input_data['Gender'] = 1;
                    } else if($input_data['Gender'] == 'female') {
                        $input_data['Gender'] = 2; 
                    }
                }
                
                $dob = null;
                if(isset($input_data['DOB'])) {
                    $dob = $input_data['DOB'];
                    unset($input_data['DOB']);
                }

                if(empty($user_detail)) {
                    $WeekDayID = $this->get_week_day_id(date('l'));
                    $TimeSlot = $this->get_time_slot();
                    if(isset($input_data["DeviceTypeID"]) && in_array($input_data["DeviceTypeID"], array(1,2))) {
                        $input_data["DeviceTypeID"] = ($input_data["DeviceTypeID"] == 1) ? 3 : 2;
                    } else {
                        $input_data["DeviceTypeID"] = 1;
                    }

                    $input_data['WeekDayID'] = $WeekDayID;
                    $input_data['TimeSlotID'] = $TimeSlot;
                    $input_data['CreatedDate'] = $created_date;
                    $input_data['SourceID'] = 1;
                    $input_data['UserTypeID'] = 3;
                    $input_data['CanCreatePoll'] = 1;
                    $input_data['StatusID'] = 2;
                    
                

                    $this->db->insert(USERS, $input_data);

                    $this->db->insert(USERROLES, array('UserID' => $user_id, 'RoleID' => 3));
                
                    $profilename = '';
                    $locality_id = 1;
                    $user_details = array(
                        'RelationWithName' => '',
                        'UserID' => $user_id,
                        'ProfileName' => $profilename,
                        'TimeZoneID' => 195,
                        'DOB' => $dob,
                        'LocalityID' => $locality_id);
                    $this->db->insert(USERDETAILS, $user_details);
                    /*  User role entry end here */
                    
                    $this->load->model(array('notification_model','privacy/privacy_model'));
                    // Add privacy settings to low
                    $this->privacy_model->save($user_id, 'low');            
                    $this->notification_model->set_all_notification_on($user_id);

                    //Create Default album
                    create_default_album($user_id, 3, $user_id);

                    //Making admin as default user to follow
                    $follow_data = array('FollowingID' => getenv('ADMIN_USER_ID'), 'UserID' => $user_id);
                    $this->load->model('follow/follow_model');
                    $this->follow_model->follow($follow_data);

                } else {
                    unset($input_data["DeviceTypeID"]);
                    unset($input_data["IPAddress"]);
                    $this->db->where('UserID',$input_data["UserID"]);
                    $this->db->update(USERS, $input_data);

                    if(!empty($dob)) {
                        $user_details = array(
                            'DOB' => $dob
                        );
                        $this->db->where('UserID',$input_data["UserID"]);
                        $this->db->update(USERDETAILS, $user_details);
                    }

                    if (CACHE_ENABLE) {
                        $this->cache->delete('user_profile_' . $input_data["UserID"]);
                    }
                }
            }
        }
    	return true;
    }
}

?>
