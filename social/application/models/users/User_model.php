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
		$this->friend_followers_list = $this->gerFriendsFollowersList($current_user, true, 1);
	}

	/**
	 * [get_friend_followers_list used to return friend follower list]
	 * @return type
	 */
	function get_friend_followers_list() {
		return $this->friend_followers_list;
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
			/* $this->db->where('UserID', $user_id);
			$this->db->where('SourceID', '1');
			$this->db->update(USERLOGINS, array('LoginKeyword' => $username));
			*/
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

		

		if (strlen($AboutMe) > 500) {
                    $AboutMe = substr($AboutMe, 0, 500);
		}

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
        
        function updateProfileCompeteStatus($user_id) {
            $this->db->select('U.FirstName, U.LastName, U.Email, UD.CityID');
            $this->db->from(USERS . ' U');
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
            $this->db->where('U.UserID', $user_id);            
            $query = $this->db->get();
            $user = $query->row_array();            
            $user = is_array($user) ? $user : [];            
            
            foreach($user as $field => $value) {
                if(!$value) {
                    return;
                }
            }
            
            //Update profile complete status
            $UpdateArray = array('IsProfileSetup' => '1');
            $this->db->where('UserID', $user_id);
            $this->db->update(USERS, $UpdateArray);
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

	function get_page_user_list($user_id, $search) {
		$data = array();
                $search = $this->db->escape_like_str($search);
		$this->db->select('P.Title as Title,P.PageGUID as ModuleEntityGUID,"18" as ModuleID', false);
		$this->db->from(PAGES . ' P');
		$this->db->join(PAGEMEMBERS . ' PM', 'PM.PageID=P.PageID', 'left');
		$this->db->where('PM.UserID', $user_id);
		$this->db->where_in('PM.ModuleRoleID', array(7, 8));
		$this->db->where("P.Title LIKE '%" . $search . "%'", NULL, FALSE);
		$query = $this->db->get();
		if ($query->num_rows()) {
			$data = $query->result_array();
		}
		$this->db->select("CONCAT(FirstName,' ',LastName) as Title,UserGUID as ModuleEntityGUID,'3' as ModuleID", false);
		$this->db->from(USERS);
		$this->db->where('UserID', $user_id);
		$this->db->where("CONCAT(FirstName,' ',LastName) LIKE '%" . $search . "%'", NULL, FALSE);
		$query = $this->db->get();
		if ($query->num_rows()) {
			$data[] = $query->row_array();
		}
		return $data;
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

	function update_last_date($user_id, $type) {
		$field = '';
		if ($type == 'Announcement') {
			$field = 'LastAnnouncementDate';
		} else if ($type == 'Questions') {
			$field = 'LastQuestionDate';
		}

		if ($field) {
			$this->db->set($field, get_current_date('%Y-%m-%d'));
			$this->db->where('UserID', $user_id);
			$this->db->update(USERDETAILS);
		}
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
            unset($userdata['AndroidAppVersion']);
            unset($userdata['IOSAppVersion']);
            unset($userdata['MobileNotification']);
            
            $userdata['AllowedPostType'] = $this->get_post_permission_for_newsfeed($current_user_id);
            //$userdata['GroupAllowedPostType'] = $this->get_post_permission_for_newsfeed($current_user_id,1);

           /* $userdata['IsSubscribed'] = '';
            $userdata['IsSubscribed'] = $this->subscribe_model->is_subscribed($current_user_id, 'USER', $user_id);

            // Privacy check and set / unset keys according to it
            $userdata['Privacy']['post'] = TRUE;
            $can_endorse = TRUE;
            if ($current_user_id != $user_id) {
                $can_endorse = FALSE;
                $users_relation = get_user_relation($current_user_id, $user_id);
                $privacy_details = $this->privacy_model->details($user_id);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label']) {
                    foreach ($privacy_details['Label'] as $privacy_label) {
                        if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'view_dob' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $userdata['DOB'] = '';
                        }
                        if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'view_education' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $userdata['UserEducation'] = array();
                        }
                        if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $userdata['Location']['Location'] = '';
                            $userdata['HomeLocation'] = array('City' => '', 'State' => '', 'Country' => '', 'Location' => '', 'StateCode' => '', 'CountryCode' => '', 'Address' => '');
                        }
                        if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $userdata['ProfilePicture'] = 'user_default.jpg';
                        }
                        if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'view_relationship' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $userdata['MartialStatus'] = '';
                            unset($userdata['MartialStatusTxt']);
                            unset($userdata['RelationWithName']);
                            unset($userdata['RelationWithGUID']);
                            unset($userdata['RelationWithURL']);
                        }
                        if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'view_social_account' && !in_array($privacy_label[$privacy], $users_relation)) {
                            unset($userdata['SocialAccounts']);
                            $userdata['FacebookUrl'] = '';
                            $userdata['TwitterUrl'] = '';
                            $userdata['LinkedinUrl'] = '';
                            $userdata['GplusUrl'] = '';
                            $userdata['Facebook_profile_URL'] = '';
                            $userdata['Twitter_profile_URL'] = '';
                        }
                        if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'view_work' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $userdata['WorkExperience'] = array();
                        }
                        if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'endorse_skill' && in_array($privacy_label[$privacy], $users_relation)) {
                            $can_endorse = TRUE;
                        }
                        if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'post' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $userdata['Privacy']['post'] = FALSE;
                        }

                        if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'message' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $userdata['Privacy']['message'] = FALSE;
                        }
                    }
                }
            }
            $userdata['CanEndorse'] = $can_endorse;

            $this->load->model('skills/skills_model');
            $endorse_data = $this->skills_model->is_user_endorsement($user_id);
            if ($endorse_data) {
                $userdata['EndorseCount'] = true;
            }

            $userdata['UserInterests'] = $this->get_interest($user_id, FALSE, FALSE, array('Hierarchy' => 'sub', 'IsInterested' => '1'));

            //$data = $this->forum_model->get_category_detail_by_name(1);
            $userdata['IsSetIntro'] = 1;
//            if($is_super_admin || $this->forum_model->checkIfIntroductionPosted($current_user_id,$data)){
//                $userdata['IsSetIntro'] = 1;
//            }
		

		$userdata['VideoAutoplay'] = $this->get_video_settings($current_user_id);
                */

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
		/*$this->db->select("U.IsCollapse,U.FirstName, U.LastName, CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as EntityName, U.StickyPreference, U.Email, U.Gender, U.UserGUID,UD.TimeZoneID,UD.Introduction,UD.RelationWithID,UD.RelationWithName,UD.WhyYouHere,UD.ShowWelcomeMessage,UD.IsAllInterest,UD.IsWorldWide,UD.TagLine,U.StatusID");
		$this->db->select('IFNULL(UD.LastQuestionDate,"") as LastQuestionDate', FALSE);
                $this->db->select('IFNULL(UD.LastAnnouncementDate,"") as LastAnnouncementDate', FALSE);
                $this->db->select('IFNULL(U.DateOfLastIncomingRequest,"") as DateOfLastIncomingRequest', FALSE);
                $this->db->select('IFNULL(UD.ConnectWith,"") as ConnectWith', FALSE);
                $this->db->select('IFNULL(UD.ConnectFrom,"") as ConnectFrom', FALSE);
                $this->db->select('IFNULL(UD.FacebookUrl,"") as FacebookUrl', FALSE);
                $this->db->select('IFNULL(UD.TwitterUrl,"") as TwitterUrl', FALSE);
                $this->db->select('IFNULL(UD.LinkedinUrl,"") as LinkedinUrl', FALSE);
                $this->db->select('IFNULL(UD.GplusUrl,"") as GplusUrl', FALSE);                
		$this->db->select('IFNULL(U.ProfileCover,"") as ProfileCover', FALSE);
		$this->db->select("UD.UserWallStatus"); */
                $this->db->select("U.FirstName, U.LastName, CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as EntityName, U.Email, U.Gender, U.UserGUID,UD.TimeZoneID,U.StatusID");
		$this->db->select('IFNULL(U.PhoneNumber,"") as PhoneNumber', FALSE);
                $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
                $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
                $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
                //$this->db->select('IFNULL(UD.Address,"") as Address', FALSE);
                $this->db->select('IFNULL(UD.DOB,"") as DOB', FALSE);
		$this->db->select('IFNULL(TZ.StandardTime,"") as TimeZoneText', FALSE);
		$this->db->select('IFNULL(UD.AndroidAppVersion,"") as AndroidAppVersion', FALSE);
                $this->db->select('IFNULL(UD.IOSAppVersion,"") as IOSAppVersion', FALSE);
                $this->db->select('IFNULL(U.MobileNotification,"") as MobileNotification', FALSE);
		//$this->db->select('IFNULL(UD.MartialStatus,"") as MartialStatus', FALSE);
		//$this->db->select('p.Url as ProfileURL', FALSE);
		//$this->db->select('U.StickyPreference, U.MobileNotification');
		$this->db->from(USERS . ' U');
		$this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
		$this->db->join(TIMEZONES . ' TZ', 'TZ.TimeZoneID = UD.TimeZoneID', 'left');
		//$this->db->join(PROFILEURL . " as p", "p.EntityID = U.UserID and p.EntityType = 'User'", "LEFT");
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
			$userdata['Location'] = $this->get_user_location($user_id);
                        
			/* $userdata['IsCoverExists'] = 0;
			if (!empty($userdata['ProfileCover'])) {
				$userdata['IsCoverExists'] = 1;
			}

			$userdata['ProfileCover'] = $userdata['ProfileCover'];
			
			$userdata['TagLine'] = $userdata['TagLine'];                        
			$userdata['HomeLocation'] = $this->get_user_location($user_id, 1);
			$userdata['SocialAccounts'] = $this->check_social_accounts($user_id);
			$userdata['WorkExperience'] = $this->getWorkExperience($user_id);
			$userdata['UserEducation'] = $this->getUserEducation($user_id);
			//Customize Relation With Data
			$userdata['RelationWithName'] = $userdata['RelationWithName'];
			$userdata['RelationWithGUID'] = "";
			$userdata['RelationWithURL'] = "";
			$userdata['ConnectWith'] = $this->get_connect_with($userdata['ConnectWith']);
			$userdata['ConnectFrom'] = $this->get_connect_from($userdata['ConnectFrom']);
			if (!empty($userdata['RelationWithID'])) {
				$RelationWithDetail = get_detail_by_id($userdata['RelationWithID'], 3, 'FirstName, LastName, UserGUID', 2);
				$userdata['RelationWithName'] = trim($RelationWithDetail['FirstName'] . ' ' . $RelationWithDetail['LastName']);
				$userdata['RelationWithGUID'] = $RelationWithDetail['UserGUID'];

				$userdata['RelationWithURL'] = $this->get_profile_link($userdata['RelationWithID']);
			}
			unset($userdata['RelationWithID']);

			$userdata['MartialStatusTxt'] = "----";
			if ($userdata['MartialStatus'] == 1) {
				$userdata['MartialStatusTxt'] = 'Single';
			}

			if ($userdata['MartialStatus'] == 2) {
				$userdata['MartialStatusTxt'] = 'In a relationship';
			}

			if ($userdata['MartialStatus'] == 3) {
				$userdata['MartialStatusTxt'] = 'Engaged';
			}

			if ($userdata['MartialStatus'] == 4) {
				$userdata['MartialStatusTxt'] = 'Married';
			}

			if ($userdata['MartialStatus'] == 5) {
				$userdata['MartialStatusTxt'] = 'Its complicated';
			}

			if ($userdata['MartialStatus'] == 6) {
				$userdata['MartialStatusTxt'] = 'Separated';
			}

			if ($userdata['MartialStatus'] == 7) {
				$userdata['MartialStatusTxt'] = 'Divorced';
			}

			/*edited by gautam - starts*/
			/* if ($this->IsApp == 1) {
                            if (!empty($current_user_id) && $current_user_id != $user_id) {
                                    $userdata['FriendStatus'] = $this->friend_model->checkFriendStatus($current_user_id, $user_id);
                                    $userdata['FollowStatus'] = $this->friend_model->checkFollowStatus($current_user_id, $user_id);
                            }
			}
                         */
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
		$this->load->model('category/category_model');
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
	 * [check_social_accounts check user connected his social account or not]
	 * @param  [int] $user_id [User ID]
	 * @return [array]        [return details of all the associated socail account ]
	 */
	function check_social_accounts($user_id) {
		$this->db->select("UL.SourceID");
		$this->db->select('IFNULL(M.ImageName,"") as ProfilePicture', FALSE);
		$this->db->select('IFNULL(UL.ProfileURL,"") as ProfileURL', FALSE);
		$this->db->from(USERLOGINS . ' UL');
		$this->db->join(MEDIA . ' M', 'M.MediaID = UL.MediaID', 'left');
		$this->db->where('UL.UserID', $user_id);
		$this->db->where('UL.SourceID!=', '1', FALSE);
		$query = $this->db->get();
		if ($query->num_rows()) {
			return $query->result();
		} else {
			return '';
		}
	}

	/**
	 * [get_previous_profile_pictures Used to get all the Previously uploaded Profile Pictures of given module_entity_id based on module_id]
	 * @param  [int] $module_id       	[Module ID]
	 * @param  [int] $module_entity_id 	[ModuleEntity ID]
	 * @param  [int] $page_no 			[Page Number]
	 * @param  [int] $page_size 		[Page Size]
	 * @return [array]                	[array of Profile Pictures]
	 */
	function get_previous_profile_pictures($module_id, $module_entity_id, $page_no = 1, $page_size = 16) {
		$image = array();
		$this->db->select('MediaGUID,ImageName');
		$this->db->where('MediaSectionID', '1');
		$this->db->where('ModuleID', $module_id);
		$this->db->where('MediaSectionReferenceID', $module_entity_id);
		$this->db->where('StatusID', '2');
		$this->db->order_by('MediaID', 'DESC');
		$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
		$query = $this->db->get(MEDIA);
		if ($query->num_rows()) {
			//$s3         = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY);
			foreach ($query->result_array() as $img) {
				if (!empty($img)) {
					$image[] = $img;
					/* if (strtolower(IMAGE_SERVER) == 'remote' && $s3->getObjectInfo(BUCKET, PATH_IMG_UPLOAD_FOLDER.'profile/'.$img['ImageName'])) {
						                      $image[] = $img;
						                      } else if(file_exists(IMAGE_ROOT_PATH.'profile/'.$img['ImageName'])){
						                      $image[] = $img;
					*/
				}
			}
		}
		return $image;
	}

	/**
	 * [updateSocialMediaLinks Used to Update User social profile URL ]
	 * @param  [int] $user_id   [User ID]
	 * @param  [string] $Facebook [Facebook Profile URL]
	 * @param  [string] $Twitter  [Twitter Profile URL]
	 * @param  [string] $GPlus    [GPlus Profile URL]
	 * @param  [string] $LinkedIn [LinkedIn Profile URL]
	 */
	function updateSocialMediaLinks($user_id, $Facebook, $Twitter, $GPlus, $LinkedIn) {
		if ($Facebook !== '') {
			$this->db->where('UserID', $user_id);
			$this->db->where('SourceID', '2');
			$this->db->update(USERLOGINS, array('ProfileURL' => $Facebook));
		}
		if ($Twitter !== '') {
			$this->db->where('UserID', $user_id);
			$this->db->where('SourceID', '3');
			$this->db->update(USERLOGINS, array('ProfileURL' => $Twitter));
		}
		if ($GPlus !== '') {
			$this->db->where('UserID', $user_id);
			$this->db->where('SourceID', '4');
			$this->db->update(USERLOGINS, array('ProfileURL' => $GPlus));
		}
		if ($LinkedIn !== '') {
			$this->db->where('UserID', $user_id);
			$this->db->where('SourceID', '7');
			$this->db->update(USERLOGINS, array('ProfileURL' => $LinkedIn));
		}
	}

	/**
	 * [attach_social_account Used to attach user social account with his common socail account]
	 * @param  [int] $user_id         	[User ID]
	 * @param  [string] $social_type     [Social Type]
	 * @param  [string] $social_id       [Social Account Social ID]
	 * @param  [string] $profile_url     [Social Account Profile URL]
	 * @param  [string] $profile_picture [Social Account Profile Picture]
	 */
	function attach_social_account($user_id, $social_type, $social_id, $profile_url, $profile_picture) {
		$is_account_exists = $this->login_model->check_user_exist($social_type, $social_id, '');
		if ($is_account_exists) {
			if ($is_account_exists['UserID'] == $user_id) {
				return 201;
			} else {
				return 509;
			}
		} else {
			$data['UserID'] = $user_id;
			$data['LoginKeyword'] = $social_id;
			$data['Password'] = generate_password('');
			$data['SourceID'] = $social_type;
			$data['LoginType'] = '2';
			$data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
			$data['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
			$data['IsPasswordChange'] = '0';
			$data['SetPassword'] = '0';
			$data['ProfileURL'] = $profile_url;
			$this->load->model('upload_file_model');

			$user_guid = get_detail_by_id($user_id, 3, 'UserGUID', 1);
			$image_data = @file_get_contents($profile_picture);
			if ($image_data !== FALSE) {
				$media_data = array();
				$media_data['Type'] = 'profile';
				$media_data['DeviceType'] = $this->DeviceTypeID;
				$media_data['SourceID'] = $social_type;
				$media_data['ImageData'] = $image_data;
				$media_data['ModuleID'] = 3;
				$media_data['UserID'] = $user_id;
				$media_data['ModuleEntityGUID'] = $user_guid;
				$this->load->model(array('upload_file_model'));
				$result = $this->upload_file_model->saveFileFromUrl($media_data);
				if ($result['ResponseCode'] == 200 && isset($result['Data']['MediaGUID'])) {
					$data['MediaID'] = $result['Data']['MediaID'];
				}
			}
			$this->db->insert(USERLOGINS, $data);
			if (CACHE_ENABLE) {
				$this->cache->delete('user_profile_' . $user_id);
			}
			return 200;
		}
	}

	/**
	 * [detach_social_account Used to detach social account from common social profile]
	 * @param  [int] $user_id   [User ID]
	 * @param  [int] $source_id [Social Account Type]
	 * @return [int]           [Return Response Code]
	 */
	function detach_social_account($user_id, $source_id) {
		//Check total account atatched for this user_id
		$this->db->select('UserLoginID, MediaID');
		$this->db->where(array('UserID' => $user_id));
		$this->db->from(USERLOGINS);
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		$response_code = 501;
		if ($query->num_rows() > 1) {
			//Get the attach account details for which delete request make $source_id
			$this->db->select('UserLoginID, MediaID');
			$this->db->where(array('UserID' => $user_id, 'SourceID' => $source_id));
			$this->db->from(USERLOGINS);
			$query = $this->db->get();
			if ($query->num_rows()) {
				$row = $query->row();
				$media_id = $row->MediaID;
				$user_login_id = $row->UserLoginID;

				$this->db->where('UserLoginID', $user_login_id);
				$this->db->delete(USERLOGINS);
				if ($media_id) {
					// Delete media files
					$this->load->model('upload_file_model');
					$this->upload_file_model->delete_media($media_id);
				}
			}
			$response_code = 200;
		}
		if (CACHE_ENABLE) {
			$this->cache->delete('user_profile_' . $user_id);
		}
		return $response_code;
	}

	function reportActivityMedia($activity_id, $user_id, $Description) {
            
                $activity_id = $this->db->escape_str($activity_id);
                
		$query = $this->db->query("SELECT * FROM Media LEFT JOIN Post ON Post.PostID=Media.SourceID LEFT JOIN Activity ON Activity.EntityID=Post.PostID WHERE Activity.EntityType='Post' AND Activity.ActivityID=" . $activity_id . "");
		if ($query->num_rows()) {
			$m = array();
			$i = 0;
			foreach ($query->result() as $media) {
				$m[$i]['MediaID'] = $media->MediaID;
				$m[$i]['UserID'] = $user_id;
				$m[$i]['Description'] = $Description;
				$m[$i]['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
				$i++;
			}
			$this->db->insert_batch('MediaAbuse', $m);
		}
	}

	function reportGroupMedia($GroupID, $user_id, $Description) {
            
                $GroupID = $this->db->escape_str($GroupID);
            
		$query = $this->db->query("SELECT * FROM Media WHERE MediaSectionID='2' AND SourceId=" . $GroupID . " ORDER BY MediaID DESC LIMIT 1");
		if ($query->num_rows()) {

			$m['MediaID'] = $query->row()->MediaID;
			$m['UserID'] = $user_id;
			$m['Description'] = $Description;
			$m['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');

			$this->db->insert('MediaAbuse', $m);
		}
	}

	function report_user_media($uid, $user_id, $description) {
            
                $uid = $this->db->escape_str($uid);
            
		$query = $this->db->query("SELECT * FROM Media WHERE MediaSectionID='1' AND SourceId=" . $uid . " ORDER BY MediaID DESC LIMIT 1");
		if ($query->num_rows()) {
			$m['MediaID'] = $query->row()->MediaID;
			$m['UserID'] = $user_id;
			$m['Description'] = $description;
			$m['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
			$this->db->insert('MediaAbuse', $m);
		}
	}

	function deleteAccount($UserGUID) {
            
                $UserGUID = $this->db->escape_str($UserGUID);
            
		$update = "update " . USERS . " set StatusID='3' where UserGUID=" . $UserGUID . " ";
		$checkQuery = $this->db->query($update);
	}

	function getUserSessionHistory($user_id) {
		return $this->get_user_session_history($user_id);
	}

	function getUserRole($user_id) {
		return $this->get_user_role($user_id);
	}

	function getRoleRights($RoleID) {
		return $this->get_role_rights($RoleID);
	}

	function following($user_id, $page_no = PAGE_NO, $page_size = PAGE_SIZE) {

		$Limit = ' LIMIT ' . $this->get_pagination_offset($page_no, $page_size) . ',' . $page_size;

		$sql = "Select Follow.TypeEntityID,Users.ProfilePicture,Users.FirstName,Users.LastName  from " . FOLLOW . " inner join Users on Users.UserID = Follow.TypeEntityID where Users.StatusID='2' AND Follow.Type='user' AND Follow.UserID = " . $user_id . " " . $Limit; //$sql = "Select TypeEntityID , Type from ".FOLLOW." where UserID = ".$user_id. "" ;
		$rs = $this->db->query($sql);
		if ($rs->num_rows() > 0) {

			$Data['Connections'] = $rs->num_rows();
			foreach ($rs->result_array() as $value) {
				if ($value['ProfilePicture'] != 'user_default.jpg' && $value['ProfilePicture'] != '') {
					$value['profilePicture'] = get_full_path($type = 'profile_image', '', $value['ProfilePicture'], $height = '192', $width = '192', $size = '192');
				} else {
					$value['profilePicture'] = site_url() . "/assets/img/profiles/user_default.jpg"; // get_full_path($type = 'profile_image','', $value['ProfilePicture'], $height = '192', $width = '192', $size = '192');
				}
				$value['profileLink'] = get_entity_url($value['TypeEntityID']);
				$rows[] = $value;
			}
			$Data['Connection'] = $rows;
		} else {
			$Data['Connection'] = 0;
			$Data['for_connection_see_all'] = "display:none;";
		}
		$Data['TotalRecords'] = $this->db->query("Select Follow.TypeEntityID,Users.ProfilePicture,Users.FirstName,Users.LastName  from " . FOLLOW . " inner join Users on Users.UserID = Follow.TypeEntityID where Users.StatusID='2' AND Follow.UserID = " . $user_id)->num_rows();
		return $Data;
	}

	function followers($user_id, $page_no = PAGE_NO, $page_size = PAGE_SIZE, $type = 'user') {

		$Limit = ' LIMIT ' . $this->get_pagination_offset($page_no, $page_size) . ',' . $page_size;
                
                $type = $this->db->escape_str($type);

		$sql = "Select Follow.TypeEntityID,Follow.UserID,Users.ProfilePicture,Users.FirstName,Users.LastName  from " . FOLLOW . " inner join Users on Users.UserID = Follow.UserID where Users.StatusID='2' AND Follow.TypeEntityID = '" . $user_id . "' AND Follow.Type = " . $type . "  " . $Limit; //$sql = "Select TypeEntityID , Type from ".FOLLOW." where UserID = ".$user_id. "" ;
		$rs = $this->db->query($sql);

		if ($rs->num_rows() > 0) {

			$Data['Connections'] = $rs->num_rows();
			foreach ($rs->result_array() as $value) {
				if ($value['ProfilePicture'] != 'user_default.jpg' && $value['ProfilePicture'] != '') {
					$value['profilePicture'] = get_full_path($type = 'profile_image', '', $value['ProfilePicture'], $height = '192', $width = '192', $size = '192');
				} else {
					$value['profilePicture'] = site_url() . "/assets/img/profiles/user_default.jpg"; // get_full_path($type = 'profile_image','', $value['ProfilePicture'], $height = '192', $width = '192', $size = '192');
				}
				$value['profileLink'] = get_entity_url($value['UserID']);
				$rows[] = $value;
			}

			$Data['Connection'] = $rows;
		} else {
			$Data['Connection'] = 0;
			$Data['for_connection_see_all'] = "display:none;";
		}
		$Data['TotalRecords'] = $this->db->query("Select Follow.TypeEntityID,Follow.UserID,Users.ProfilePicture,Users.FirstName,Users.LastName  from " . FOLLOW . " inner join Users on Users.UserID = Follow.UserID where Users.StatusID='2' AND Follow.TypeEntityID = " . $user_id)->num_rows();

		return $Data;
	}

	function getWebsite($user_id) {
		return $this->db->query("select Website from " . USERDETAILS . " where UserID=" . $user_id)->row_array();
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
                $this->db->select(' GROUP_CONCAT(F.TypeEntityID) as TypeEntityID');
                $this->db->from(FOLLOW.' F');
                $this->db->join(USERS.' U','U.UserID=F.TypeEntityID','left');
                $this->db->where_not_in('U.StatusID',array(3,4));
                $this->db->where('F.Type', 'user');
                $this->db->where('F.StatusID', '2');
                $this->db->where('F.UserID', $user_id);
                $this->db->order_by('F.TypeEntityID', 'ASC');
                $followResult = $this->db->get();
                //echo $this->db->last_query(); die;
                $followers_data = -1;
                if ($followResult->num_rows()) {
                    $follow_row = $followResult->row_array();
                    if (!empty($follow_row['TypeEntityID'])) {
                        $followers_data = $follow_row['TypeEntityID'];                        
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
	 * [get_interest Used to get user interest]
	 * @param  [int] $user_id [User ID]
	 * @return [array]        [User interest list]
	 */
	function get_interest($user_id, $page_no = false, $page_size = false, $Data = array()) {
            $this->load->model('settings_model');
            if($this->settings_model->isDisabled(31)){
                return array();
            }

            /* Edited by Gautam - starts */
            $Data['ParentID'] = (isset($Data['ParentID']) ? $Data['ParentID'] : 0);
            $Data['IsPopular'] = (isset($Data['IsPopular']) ? $Data['IsPopular'] : 0);
            $Data['SearchKey'] = (isset($Data['SearchKey']) ? $Data['SearchKey'] : '');
            $Data['IsInterested'] = (isset($Data['IsInterested']) ? $Data['IsInterested'] : 0);
            $Data['Hierarchy'] = (isset($Data['Hierarchy']) ? $Data['Hierarchy'] : 'parent');
            /* Edited by Gautam - ends */

            $this->db->select('IFNULL(M.ImageName,"") as ImageName', FALSE);
            $this->db->select('IFNULL(CM.Description,"") as Description', FALSE);
            $this->db->select('IFNULL(CM.Icon,"") as Icon', FALSE);
            $this->db->select('IFNULL(CM.Name,"") as Name', FALSE);
                
            $this->db->select('CM.CategoryID,CM.ParentID');
            $this->db->select('if(EC.CategoryID is not NULL,1,0) as IsInterested', false);

            /* Edited by Gautam - starts */
            $this->db->select('(SELECT COUNT(CategoryID) FROM `EntityCategory` WHERE CategoryID=CM.CategoryID AND ModuleID="3" GROUP BY CategoryID) as InterestCount'); /*used to get most used category by orderby*/
            /* Get parent category Name if exists */
            $this->db->select('(SELECT Name FROM ' . CATEGORYMASTER . ' WHERE CategoryID=CM.ParentID) as ParentCategoryName');

            $this->db->from(CATEGORYMASTER . ' CM');
            $this->db->join(MEDIA . ' M', 'M.MediaID=CM.MediaID', 'left');
            $this->db->join(ENTITYCATEGORY . ' EC', "CM.CategoryID=EC.CategoryID AND EC.ModuleID=3 AND EC.ModuleEntityID='" . $user_id . "'", 'left outer');
            $this->db->where('CM.ModuleID', '31');
            $this->db->where('CM.StatusID', '2');
            /* Edited by Gautam - ends */

            /* Edited by Gautam - starts */
            if ($Data['IsInterested'] == 1) {
                $this->db->where('EC.CategoryID is NOT NULL', NULL, FALSE);
            }

            /*Only get data by searched keyword*/
            if ($Data['SearchKey'] != '') {
                $this->db->like('CM.Name', $Data['SearchKey']);
            }

            if (!empty($Data['ParentID'])) {
                /* get subcategory data by ParentID */
                $this->db->where('CM.ParentID', $Data['ParentID']);
            }

            /* if(!empty($Data['Hierarchy']) && $Data['Hierarchy']=='sub'){
                $this->db->where('CM.ParentID !=',0);
            }*/
            elseif (!empty($Data['Hierarchy']) && $Data['Hierarchy'] == 'parent') {
                    /*select records which have child category*/
                    $this->db->where('EXISTS(SELECT CM.CategoryID FROM ' . CATEGORYMASTER . ' WHERE ParentID = CM.CategoryID)');
                    $this->db->where('CM.ParentID =', 0);
            }
            /* Edited by Gautam - ends */

            $this->db->group_by('CM.CategoryID');
            //$this->db->where('EC.CategoryID is NULL',null,false);

            /* Edited by Gautam - starts */
            if ($Data['IsPopular'] == 1) {
                $this->db->order_by("InterestCount", 'DESC');
            } else {
                $this->db->order_by("CM.Name", 'ASC');
            }
            /* Edited by Gautam - ends */

            if ($page_no && $page_size) {
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }

            $query = $this->db->get();

            if ($query->num_rows()) {
                return $query->result_array();
            } else {
                return array();
            }
	}

	/**
	 * [save_interest Used to save user interest]
	 * @param  [int] $user_id    [User ID]
	 * @param  [array] $categories [CATEGORIES ID]
	 */
	function save_interest($user_id, $categories) {
		$this->db->where('ModuleID', '3');
		$this->db->where('ModuleEntityID', $user_id);
		$this->db->delete(ENTITYCATEGORY);
		if ($categories) {
			$insert_data = array();
			foreach ($categories as $category) {
				$insert_data[] = array('CategoryID' => $category, 'EntityCategoryGUID' => get_guid(), 'ModuleID' => '3', 'ModuleEntityID' => $user_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
			}
			$this->db->insert_batch(ENTITYCATEGORY, $insert_data);
		}
	}

	/**
	 * [save_interest Used to save user interest]
	 * @param  [int] $user_id    [User ID]
	 * @param  [array] $categories [CATEGORIES ID]
	 */
	function update_single_interest($user_id, $category_id, $action) {
		if ($action == 'remove') {
			$this->db->where('ModuleID', '3');
			$this->db->where('ModuleEntityID', $user_id);
			$this->db->where('CategoryID', $category_id);
			$this->db->delete(ENTITYCATEGORY);
		} else if ($action == 'add') {
			$this->db->where('ModuleID', '3');
			$this->db->where('ModuleEntityID', $user_id);
			$this->db->where('CategoryID', $category_id);
			$query = $this->db->get(ENTITYCATEGORY);
			if (!$query->num_rows()) {
				$data = array('CategoryID' => $category_id, 'EntityCategoryGUID' => get_guid(), 'ModuleID' => '3', 'ModuleEntityID' => $user_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
				$this->db->insert(ENTITYCATEGORY, $data);
			}
		}
	}

	/**
	 * [mute_source Used to mute any source]
	 * @param  [int] $user_id            [Logged in User ID]
	 * @param  [int] $module_entity_id 	[Module Entity ID]
	 * @param  [int] $module_id          [Module ID]
	 */
	function mute_source($user_id, $module_entity_id, $module_id) {
		$insert[] = array('UserID' => $user_id, 'ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
		//print_r($insert);die;
		$this->db->insert_on_duplicate_update_batch(MUTESOURCE, $insert);
	}

	/**
	 * [un_mute_source Used to un mute source]
	 * @param  [int] $user_id            [Logged in User ID]
	 * @param  [int] $module_entity_id 	[Module Entity ID]
	 * @param  [int] $module_id          [Module ID]
	 */
	function un_mute_source($user_id, $module_entity_id, $module_id) {
		$this->db->where(array('UserID' => $user_id, 'ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id));
		$this->db->delete(MUTESOURCE);
	}

	/**
	 * [mute_source_list Used to get mute source list]
	 * @param  [int] $user_id       [Logged in User ID]
	 * @param  [int] $page_no 			[Page Number]
	 * @param  [int] $page_size 		[Page Size]
	 * @return [array]        		[mute source list]
	 */
	function mute_source_list_old($user_id, $page_no = 1, $page_size = PAGE_SIZE) {
		$this->db->select('*');
		$this->db->where('UserID', $user_id);
		$this->db->order_by('ModifiedDate', 'DESC');
		$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
		$query = $this->db->get(MUTESOURCE);
		//echo $this->db->last_query();
		$data = array();
		if ($query->num_rows()) {
			foreach ($query->result_array() as $result) {
				$module_id = $result['ModuleID'];
				$module_entity_id = $result['ModuleEntityID'];
				$source = $this->source_details($module_entity_id, $module_id);
				//print_r($source);
				if (!empty($source)) {
					$source['ModuleID'] = $module_id;
					$data[] = $source;
				}
			}
		}
		return $data;
	}

	/**
	 * [mute_source_list Used to get mute source list]
	 * @param  [int] $user_id       [Logged in User ID]
	 * @param  [string] $keyword 			[Search Keyword]
	 * @param  [int] $count_only 		[Count only flag]
	 * @param  [int] $page_no 			[Page Number]
	 * @param  [int] $page_size 		[Page Size]
	 * @return [array]        		[mute source list]
	 */
	function mute_source_list($user_id, $keyword = '', $count_only = 0, $page_no = 1, $page_size = PAGE_SIZE) {
                $this->load->model('group/group_model');
                
		$this->db->select('(CASE MUT.ModuleID
							WHEN 3 THEN PU.Url
							WHEN 18 THEN P.PageURL
							ELSE "" END) AS ProfileURL', FALSE);

		$this->db->select('(CASE MUT.ModuleID
							WHEN 14 THEN CONCAT(E.StartDate, " ", DATE_FORMAT(E.StartTime,"%l:%i %p"))
							ELSE "" END) AS DateTime', FALSE);

		$this->db->select('(CASE MUT.ModuleID
							WHEN 1 THEN CM.Name
							WHEN 18 THEN CM1.Name ELSE "" END) AS Category', FALSE);

		$this->db->select('(CASE MUT.ModuleID
							WHEN 1 THEN G.GroupGUID
							WHEN 3 THEN U.UserGUID
							WHEN 14 THEN E.EventGUID
							WHEN 18 THEN P.PageGUID ELSE "" END) AS ModuleEntityGUID', FALSE);

		$this->db->select('(CASE MUT.ModuleID
							WHEN 1 THEN if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg")
							WHEN 3 THEN IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture)
							WHEN 14 THEN IFNULL(E.ProfileImageID,"")
							WHEN 18 THEN IF(P.ProfilePicture="",CM1.Icon,P.ProfilePicture)
							ELSE "" END) AS ProfilePicture', FALSE);
		$this->db->select('CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,""), " ",IFNULL(G.GroupName,""), " ",IFNULL(E.Title,""), " ",IFNULL(P.Title,"")) AS Name', FALSE);

		$this->db->select('MUT.ModuleID, MUT.ModuleEntityID', FALSE);
		$this->db->join(USERS . " U", "U.UserID=MUT.ModuleEntityID AND MUT.ModuleID=3", "LEFT");
		$this->db->join(GROUPS . " G", "G.GroupID=MUT.ModuleEntityID AND MUT.ModuleID=1", "LEFT");
		$this->db->join(EVENTS . " E", "E.EventID=MUT.ModuleEntityID AND MUT.ModuleID=14", "LEFT");
		$this->db->join(PAGES . " P", "P.PageID=MUT.ModuleEntityID AND MUT.ModuleID=18", "LEFT");

		$this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");

		$this->db->join(ENTITYCATEGORY . " EC", "EC.ModuleEntityID=G.GroupID AND EC.ModuleID=1", "LEFT");
		//$this->db->join(ENTITYCATEGORY." EC1", "EC1.ModuleEntityID=P.PageID AND EC1.ModuleID=18", "LEFT");

		$this->db->join(CATEGORYMASTER . " CM", "CM.CategoryID = EC.CategoryID", "LEFT");
		$this->db->join(CATEGORYMASTER . " CM1", "CM1.CategoryID = P.CategoryID", "LEFT");

		$this->db->where('MUT.UserID', $user_id);
		$this->db->order_by('MUT.ModifiedDate', 'DESC');

		if (!empty($keyword)) {
                    $keyword = $this->db->escape_like_str($keyword);
			$this->db->having("Name LIKE '%" . $keyword . "%'", NULL, FALSE);
		}
		if ($count_only) {
			$query = $this->db->get(MUTESOURCE . " MUT");
			return $query->num_rows();
		} else {
			$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
		}
		$query = $this->db->get(MUTESOURCE . " MUT");
		//echo $this->db->last_query();die;

		$response = array();
		if ($query->num_rows()) {
			foreach ($query->result_array() as $result) {
				$module_id = $result['ModuleID'];
				$module_entity_id = $result['ModuleEntityID'];

				$data['ModuleID'] = $module_id;
				$data['ModuleEntityGUID'] = $result['ModuleEntityGUID'];
				$data['Name'] = $result['Name'];
				$data['ProfilePicture'] = $result['ProfilePicture'];
				$data['ProfileURL'] = $result['ProfileURL'];
				$data['DateTime'] = $result['DateTime'];
				$data['Category'] = $result['Category'];
				$data['Location'] = '';

				if ($module_id == 3) {
					$data['Location'] = $this->get_user_location($module_entity_id);
				}

				if ($module_id == 1) {
                                    
                                    $group_url_details = $this->group_model->get_group_details_by_id($module_entity_id, '', array(
                                        'GroupName' => $result['Name'],
                                        'GroupGUID' => $result['ModuleEntityGUID'],
                                    ));
                                    $data['ProfileURL'] = $this->group_model->get_group_url($module_entity_id, $group_url_details['GroupNameTitle'], false, 'index');  
                                                                       
				}
				if ($module_id == 14) {

					if (!empty($data['ProfilePicture'])) {
						$this->db->select('ImageName');
						$ImageArr = $this->db->get_where(MEDIA, array('MediaID' => $data['ProfilePicture']))->row_array();
						$data['ProfilePicture'] = $ImageArr['ImageName'];
					} else {
						$data['ProfilePicture'] = 'event-placeholder.png';
					}
                                        $this->load->model('events/event_model');
                                        $url = $this->event_model->getViewEventUrl($result['ModuleEntityGUID'], $result['Name'], false,'wall');
                                        $data['ProfileURL'] = $url;
				}
				$response[] = $data;
			}
		}
		return $response;
	}

	/**
	 * [prioritize_source Used to prioritize any source]
	 * @param  [int] $user_id            [Logged in User ID]
	 * @param  [int] $module_entity_id 	[Module Entity ID]
	 * @param  [int] $module_id          [Module ID]
	 */
	function prioritize_source($user_id, $module_entity_id, $module_id) {
		$insert[] = array('UserID' => $user_id, 'ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
		//print_r($insert);die;
		$this->db->insert_on_duplicate_update_batch(PRIORITIZESOURCE, $insert);
	}

	/**
	 * [un_prioritize_source Used to un prioritize source]
	 * @param  [int] $user_id            [Logged in User ID]
	 * @param  [int] $module_entity_id 	[Module Entity ID]
	 * @param  [int] $module_id          [Module ID]
	 */
	function un_prioritize_source($user_id, $module_entity_id, $module_id) {
		$this->db->where(array('UserID' => $user_id, 'ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id));
		$this->db->delete(PRIORITIZESOURCE);
	}

	/**
	 * [prioritize_source_list Used to get prioritize source list]
	 * @param  [int] $user_id       [Logged in User ID]
	 * @param  [int] $page_no 			[Page Number]
	 * @param  [int] $page_size 		[Page Size]
	 * @return [array]        		[mute source list]
	 */
	function prioritize_source_list_old($user_id, $page_no = 1, $page_size = PAGE_SIZE) {
		$this->db->select('*');
		$this->db->where('UserID', $user_id);
		$this->db->order_by('ModifiedDate', 'DESC');
		$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
		$query = $this->db->get(PRIORITIZESOURCE);
		//echo $this->db->last_query();
		$data = array();
		if ($query->num_rows()) {
			foreach ($query->result_array() as $result) {
				$module_id = $result['ModuleID'];
				$module_entity_id = $result['ModuleEntityID'];
				$source = $this->source_details($module_entity_id, $module_id);
				//print_r($source);
				if (!empty($source)) {
					$source['ModuleID'] = $module_id;
					$data[] = $source;
				}
			}
		}
		return $data;
	}

	/**
	 * [prioritize_source_list Used to get prioritize source list]
	 * @param  [int] $user_id       [Logged in User ID]
	 * @param  [string] $keyword 			[Search Keyword]
	 * @param  [int] $count_only 		[Count only flag]
	 * @param  [int] $page_no 			[Page Number]
	 * @param  [int] $page_size 		[Page Size]
	 * @return [array]        		[prioritize source list]
	 */
	function prioritize_source_list($user_id, $keyword = '', $count_only = 0, $page_no = 1, $page_size = PAGE_SIZE) {
                $this->load->model('group/group_model');
                
		$this->db->select('(CASE MUT.ModuleID
							WHEN 3 THEN PU.Url
							WHEN 18 THEN P.PageURL
							ELSE "" END) AS ProfileURL', FALSE);

		$this->db->select('(CASE MUT.ModuleID
							WHEN 14 THEN CONCAT(E.StartDate, " ", DATE_FORMAT(StartTime,"%l:%i %p"))
							ELSE "" END) AS DateTime', FALSE);

		$this->db->select('(CASE MUT.ModuleID
							WHEN 1 THEN CM.Name
							WHEN 18 THEN CM1.Name ELSE "" END) AS Category', FALSE);

		$this->db->select('(CASE MUT.ModuleID
							WHEN 1 THEN G.GroupGUID
							WHEN 3 THEN U.UserGUID
							WHEN 14 THEN E.EventGUID
							WHEN 18 THEN P.PageGUID ELSE "" END) AS ModuleEntityGUID', FALSE);

		$this->db->select('(CASE MUT.ModuleID
							WHEN 1 THEN if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg")
							WHEN 3 THEN IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture)
							WHEN 14 THEN IFNULL(E.ProfileImageID,"")
							WHEN 18 THEN IF(P.ProfilePicture="",CM1.Icon,P.ProfilePicture)
							ELSE "" END) AS ProfilePicture', FALSE);
		$this->db->select('CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,""), " ",IFNULL(G.GroupName,""), " ",IFNULL(E.Title,""), " ",IFNULL(P.Title,"")) AS Name', FALSE);

		$this->db->select('MUT.ModuleID, MUT.ModuleEntityID', FALSE);
		$this->db->join(USERS . " U", "U.UserID=MUT.ModuleEntityID AND MUT.ModuleID=3", "LEFT");
		$this->db->join(GROUPS . " G", "G.GroupID=MUT.ModuleEntityID AND MUT.ModuleID=1", "LEFT");
		$this->db->join(EVENTS . " E", "E.EventID=MUT.ModuleEntityID AND MUT.ModuleID=14", "LEFT");
		$this->db->join(PAGES . " P", "P.PageID=MUT.ModuleEntityID AND MUT.ModuleID=18", "LEFT");

		$this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");

		$this->db->join(ENTITYCATEGORY . " EC", "EC.ModuleEntityID=G.GroupID AND EC.ModuleID=1", "LEFT");
		//$this->db->join(ENTITYCATEGORY." EC1", "EC1.ModuleEntityID=P.PageID AND EC1.ModuleID=18", "LEFT");

		$this->db->join(CATEGORYMASTER . " CM", "CM.CategoryID = EC.CategoryID", "LEFT");
		$this->db->join(CATEGORYMASTER . " CM1", "CM1.CategoryID = P.CategoryID", "LEFT");

		$this->db->where('MUT.UserID', $user_id);
		$this->db->order_by('MUT.ModifiedDate', 'DESC');

		if (!empty($keyword)) {
                    $keyword = $this->db->escape_like_str($keyword); 
			$this->db->having("Name LIKE '%" . $keyword . "%'", NULL, FALSE);
		}
		if ($count_only) {
			$query = $this->db->get(PRIORITIZESOURCE . " MUT");
			return $query->num_rows();
		} else {
			$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
		}
		$query = $this->db->get(PRIORITIZESOURCE . " MUT");
		//echo $this->db->last_query();die;

		$response = array();
		if ($query->num_rows()) {
			foreach ($query->result_array() as $result) {
				$module_id = $result['ModuleID'];
				$module_entity_id = $result['ModuleEntityID'];

				$data['ModuleID'] = $module_id;
				$data['ModuleEntityGUID'] = $result['ModuleEntityGUID'];
				$data['Name'] = $result['Name'];
				$data['ProfilePicture'] = $result['ProfilePicture'];
				$data['ProfileURL'] = $result['ProfileURL'];
				$data['DateTime'] = $result['DateTime'];
				$data['Category'] = $result['Category'];
				$data['Location'] = '';

				if ($module_id == 3) {
					$data['Location'] = $this->get_user_location($module_entity_id);
				}

				if ($module_id == 1) {
                                    
                                    $group_url_details = $this->group_model->get_group_details_by_id($result['ModuleEntityID'], '', array(
                                        'GroupName' => $result['Name'],
                                        'GroupGUID' => $result['ModuleEntityGUID'],
                                    ));
                                    $data['ProfileURL'] = $this->group_model->get_group_url($result['ModuleEntityID'], $group_url_details['GroupNameTitle'], false, 'index'); 
                                                                       
				}
				if ($module_id == 14) {

					if (!empty($data['ProfilePicture'])) {
						$this->db->select('ImageName');
						$ImageArr = $this->db->get_where(MEDIA, array('MediaID' => $data['ProfilePicture']))->row_array();
						$data['ProfilePicture'] = $ImageArr['ImageName'];
					} else {
						$data['ProfilePicture'] = 'event-placeholder.png';
					}
                                        $this->load->model('events/event_model');
                                        $url = $this->event_model->getViewEventUrl($result['ModuleEntityGUID'], $result['Name'], false,'wall');
                                        $data['ProfileURL'] = $url;
				}
				if ($module_id == 18) {
					$data['ProfileURL'] = 'page/' . $result['ProfileURL'];
				}
				$response[] = $data;
			}
		}
		return $response;
	}

	/**
	 * [source_details Used to get mute source details]
	 * @param  [int] $module_entity_id 	[Module Entity ID]
	 * @param  [int] $module_id          [Module ID]
	 * @return [type]                   [description]
	 */
	function source_details($module_entity_id, $module_id) {
            
            $this->load->model('group/group_model');
            
		$data = array();
		if ($module_id == 1) {
			$this->db->select('G.GroupGUID as ModuleEntityGUID, G.GroupName as FirstName, CM.Name as Category, if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg") as ProfilePicture', FALSE);
			$this->db->from(GROUPS . ' G');
			$this->db->join(ENTITYCATEGORY . " as EC", 'EC.ModuleEntityID = G.GroupID and EC.ModuleID = ' . $module_id);
			$this->db->join(CATEGORYMASTER . " as CM", 'CM.CategoryID = EC.CategoryID');
			$this->db->where('G.GroupID', $module_entity_id);
			$query = $this->db->get();
			if ($query->num_rows()) {
				$result = $query->row_array();
				$data['ModuleEntityGUID'] = $result['ModuleEntityGUID'];
				$data['Name'] = $result['FirstName'];
				$data['Category'] = $result['Category'];
				$data['ProfilePicture'] = $result['ProfilePicture'];
                                
                                
                                $group_url_details = $this->group_model->get_group_details_by_id($module_entity_id, '', array(
                                    'GroupName' => $result['FirstName'],
                                    'GroupGUID' => $result['ModuleEntityGUID'],
                                ));
                                $data['ProfileURL'] = $this->group_model->get_group_url($module_entity_id, $group_url_details['GroupNameTitle'], false, 'index'); 
                                                                
				$data['Location'] = '';
				$data['DateTime'] = '';
			}
		} else if ($module_id == 3) {
			$this->db->select("U.UserGUID as ModuleEntityGUID, U.FirstName, U.LastName");
			$this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);
			$this->db->select('p.Url as ProfileURL', FALSE);
			$this->db->from(USERS . ' U');
			$this->db->join(PROFILEURL . " as p", "p.EntityID = U.UserID and p.EntityType = 'User'", "LEFT");
			$this->db->where('U.UserID', $module_entity_id);
			$query = $this->db->get();
			if ($query->num_rows()) {
				$result = $query->row_array();
				$data['ModuleEntityGUID'] = $result['ModuleEntityGUID'];
				$data['Name'] = $result['FirstName'] . ' ' . $result['LastName'];
				$data['ProfilePicture'] = $result['ProfilePicture'];
				$data['ProfileURL'] = $result['ProfileURL'];
				$data['Location'] = $this->get_user_location($module_entity_id);
				$data['Category'] = '';
				$data['DateTime'] = '';
			}
		} else if ($module_id == 14) {
			$this->db->select('E.EventGUID as ModuleEntityGUID, E.Title as FirstName, E.ProfileImageID, E.StartDate, DATE_FORMAT(E.StartTime,"%l:%i %p") AS StartTime', false);
			$this->db->from(EVENTS . ' AS E');
			$this->db->where('E.EventID', $module_entity_id);
			$query = $this->db->get();
			if ($query->num_rows()) {
				$result = $query->row_array();
				$profile_image_id = $result['ProfileImageID'];
				$data['ProfilePicture'] = "event-placeholder.png";
				if (!empty($profile_image_id)) {
					$this->db->select('ImageName');
					$ImageArr = $this->db->get_where(MEDIA, array('MediaID' => $profile_image_id))->row_array();
					$data['ProfilePicture'] = $ImageArr['ImageName'];
				}
				$start_date = $result['StartDate'];
				$start_time = $result['StartTime'];

				$data['ModuleEntityGUID'] = $result['ModuleEntityGUID'];
				$data['Name'] = $result['FirstName'];
                                $this->load->model('events/event_model');
                                $url = $this->event_model->getViewEventUrl($result['ModuleEntityGUID'], $result['FirstName'], false,'wall');
                                $data['ProfileURL'] = $url;
				$data['DateTime'] = $start_date . ' ' . $start_time;
				$data['Category'] = '';
				$data['Location'] = '';
			}
		} else if ($module_id == 18) {
			$this->db->select('P.PageGUID as ModuleEntityGUID, P.Title as FirstName, P.PageURL, CM.Name as Category, if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture', false);
			$this->db->from(PAGES . " as P");
			$this->db->join(ENTITYCATEGORY . " as EC", 'EC.ModuleEntityID = P.PageID and EC.ModuleID = ' . $module_id);
			$this->db->join(CATEGORYMASTER . " as CM", 'CM.CategoryID = EC.CategoryID');
			$this->db->where('P.PageID', $module_entity_id);
			$query = $this->db->get();
			if ($query->num_rows()) {
				$result = $query->row_array();
				$data['ModuleEntityGUID'] = $result['ModuleEntityGUID'];
				$data['Name'] = $result['FirstName'];
				$data['Category'] = $result['Category'];
				$data['ProfilePicture'] = $result['ProfilePicture'];
				$data['ProfileURL'] = 'page/' . $result['PageURL'];
				$data['DateTime'] = '';
				$data['Location'] = '';
			}
		}
		return $data;
	}

	public function suggestion_list($user_id, $keyword, $type) {

		$friend_follower = $this->gerFriendsFollowersList($user_id, true, 1);
		$friends = $friend_follower['Friends'];
		$follow = $friend_follower['Follow'];
		$friends[] = 0;
		$follow[] = 0;
		$friend_follower = array_unique(array_merge($friends, $follow));
		$friend_follower[] = 0;
		$friend_follower = implode(',', $friend_follower);

		$group_list = $this->group_model->get_joined_groups($user_id, false, array(2));
		$event_list = $this->event_model->get_all_joined_events($user_id);
		$page_list = $this->page_model->get_liked_pages_list($user_id);

		switch ($type) {
		case 'Mute':
			$table_name = 'MuteSource';
			break;
		case 'Prioritize':
			$table_name = 'PrioritizeSource';
			break;
		}
                
                $union_queries = [];
                
                $user_query = "
				    SELECT CONCAT(FirstName,' ',LastName) as Title, UserID as ModuleEntityID,UserGUID as ModuleEntityGUID, IF(ProfilePicture='','user_default.jpg',ProfilePicture) as ProfilePicture, '3' as ModuleID, '1' as Category, '1' as StartDate, '1' as StartTime FROM Users
					    WHERE UserID IN ($friend_follower) AND StatusID IN (1,2)";
                $union_queries[] = $user_query;
                
                if($group_list) {
                    $group_query = "SELECT G.GroupName as Title, G.GroupID as ModuleEntityID, G.GroupGUID as ModuleEntityGUID, if(G.GroupImage!='',G.GroupImage,'group-no-img.jpg') as ProfilePicture, '1' as ModuleID, CM.Name as Category, '1' as StartDate, '1' as StartTime FROM Groups G
                                LEFT JOIN EntityCategory EC ON EC.ModuleEntityID = G.GroupID and EC.ModuleID = 1
                                LEFT JOIN CategoryMaster AS CM ON CM.CategoryID = EC.CategoryID
                                WHERE G.GroupID IN ($group_list)";
                    $union_queries[] = $group_query;
                }
                
                 if($event_list) {
                    $event_query = "SELECT Title, EventID as ModuleEntityID, EventGUID as ModuleEntityGUID,  ProfileImageID as ProfilePicture, '14' as ModuleID, '1' as Category, StartDate, DATE_FORMAT(StartTime,'%l:%i %p') AS StartTime 
                                    FROM Events
                                    WHERE EventID IN ($event_list)";
                    $union_queries[] = $event_query;
                 }
                 
                 if($page_list) {
                    $page_query = " SELECT P.Title, P.PageID as ModuleEntityID, P.PageGUID as ModuleEntityGUID, if(P.ProfilePicture='', CM.Icon, P.ProfilePicture) as ProfilePicture, '18' as ModuleID, CM.Name as Category, '0' as StartDate, '0' as StartTime FROM Pages P
                                            LEFT JOIN EntityCategory AS EC ON EC.ModuleEntityID = P.PageID and EC.ModuleID = 18
                                            LEFT JOIN CategoryMaster AS CM ON CM.CategoryID = P.CategoryID
                                            WHERE P.PageID IN ($page_list)";
                    $union_queries[] = $page_query;
                 }
                
                
                $union_queries = implode('  UNION   ', $union_queries);

		$sql = "SELECT t1.* FROM (
                        
                        $union_queries

				    

				) t1 LEFT JOIN " . $table_name . " t2 ON t1.ModuleID=t2.ModuleID AND t1.ModuleEntityID=t2.ModuleEntityID AND t2.UserID='" . $user_id . "' WHERE t2.ModuleEntityID is NULL AND Title Like '%" . $keyword . "%'";
		/* if($ignore_list)
			          {
			          $sql .= " AND (IF())";
		*/
		$response = array();
		$query = $this->db->query($sql);
		//echo $this->db->last_query(); die;
		if ($query->num_rows()) {
			foreach ($query->result_array() as $result) {
				$module_id = $result['ModuleID'];
				$module_entity_id = $result['ModuleEntityID'];
				$data['ModuleID'] = $module_id;
				$data['ModuleEntityGUID'] = $result['ModuleEntityGUID'];
				$data['Title'] = $result['Title'];
				$data['ProfilePicture'] = $result['ProfilePicture'];
				$data['Location'] = '';
				$data['DateTime'] = '';
				$data['Category'] = '';
				if ($module_id == 3) {
					$data['Location'] = $this->get_user_location($module_entity_id);
				}

				if ($module_id == 1) {
					$data['Category'] = $result['Category'];
				}
				if ($module_id == 14) {
					$data['Category'] = $result['Category'];
					//$data['ProfilePicture'] 	= "event-placeholder.png";

					if (!empty($data['ProfilePicture'])) {
						$this->db->select('ImageName');
						$ImageArr = $this->db->get_where(MEDIA, array('MediaID' => $data['ProfilePicture']))->row_array();
						$data['ProfilePicture'] = $ImageArr['ImageName'];
					} else {
						$data['ProfilePicture'] = "event-placeholder.png";
					}
					$start_date = $result['StartDate'];
					$start_time = $result['StartTime'];					
					$data['DateTime'] = $start_date . ' ' . $start_time;
				}
				if ($module_id == 18) {
					$data['Category'] = $result['Category'];
					//$data['ProfileURL'] 		= 'page/'.$result['PageURL'];
				}
				if (!$data['Category']) {
					$data['Category'] = '';
				}
				if (!$data['ProfilePicture']) {
					$data['ProfilePicture'] = 'user_default.jpg';
				}
				$response[] = $data;
			}
		}
		return $response;
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

	public function getProfileSections($Section = '', $Column = '', $term = '') {
		if (!empty($Section) && !empty($Column)) {
			switch ($Section) {
			case 'WorkExperience':
				$this->db->from(WORKEXPERIENCE);
				switch ($Column) {
				case 'OrganizationName':
					$this->db->like('OrganizationName', $term, 'after');
					$this->db->group_by('OrganizationName');
					break;
				case 'Designation':
					$this->db->like('Designation', $term, 'after');
					$this->db->group_by('Designation');
					break;
				default:
					# code...
					break;
				}
				$Data = $this->db->get()->result_array();
				break;
			case 'Education':
				$this->db->from(EDUCATION);
				switch ($Column) {
				case 'University':
					$this->db->like('University', $term, 'after');
					$this->db->group_by('University');
					break;
				case 'CourseName':
					$this->db->like('CourseName', $term, 'after');
					$this->db->group_by('CourseName');
					break;
				default:
					# code...
					break;
				}
				$Data = $this->db->get()->result_array();
				break;
			default:
				$Data = array();
				break;
			}
		}
		//$Data['Section'] = $Section;
		return $Data;
	}

	/**
	 * Function Name: search_user_n_group
	 * @param search_keyword
	 * @param user_id
	 * @param remove_users,remove_groups
	 * Description: get list of friends/joined groups with search filter
	 */
	function search_user_n_group($search_keyword, $user_id, $remove_users, $remove_groups, $group_id = '', $formal = 1) 
        {
            $this->load->model(array('activity/activity_model', 'groups/group_model'));
            $blocked_users  = $this->activity_model->block_user_list($user_id, 3);
            $group_ids      = $this->group_model->get_users_groups($user_id);
            if (!empty($group_id))
            {
                $blockedGroupUsers = $this->activity_model->block_user_list_group($group_id);
                if(!empty($blockedGroupUsers))
                {
                    $blocked_users= array_merge($blocked_users,$blockedGroupUsers);
                }            
                $group_members = $this->group_model->get_members_with_module($group_id);
                if (!empty($group_members))
                {
                    $remove_users = $group_members['Users'];
                    $remove_groups = $group_members['Groups'];
                }
            }

		/* --Search User-- */
		$privacy_condition = "
			IF(UP.Value='everyone',true,
				IF(UP.Value='network', U.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID),
				IF(UP.Value='friend',U.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
			)
		";

		if ($blocked_users) 
                {
			$privacy_condition .= " AND U.UserID NOT IN (" . implode(',', $blocked_users) . ") ";
		}
                $search_keyword = $this->db->escape_like_str($search_keyword); 
		$sql = "SELECT DISTINCT U.UserGUID AS ModuleEntityGUID, U.UserID AS ModuleEntityID, CONCAT(U.FirstName,' ',U.LastName) AS name, if(U.ProfilePicture!='',U.ProfilePicture,'') AS ProfilePicture, '' AS Privacy,3 AS ModuleID,'User' AS Type,'' AS GroupDescription
		FROM " . USERS . " U LEFT JOIN " . USERPRIVACY . " UP ON UP.UserID=U.UserID WHERE U.StatusID IN (1,2,6,7) AND U.UserID != '" . $user_id . "'  AND U.UserID!=$user_id
		AND (U.FirstName LIKE '%" . $search_keyword . "%' OR U.LastName LIKE '%" . $search_keyword . "%' OR CONCAT(U.FirstName,' ',U.LastName) LIKE '%" . $search_keyword . "%')";
		if (!empty($remove_users)) {
			$remove_users = implode("','", $remove_users);
			$sql .= " AND U.UserID NOT IN ('$remove_users')";
		}
		//$sql.=' AND UP.PrivacyLabelKey="tagged" AND ' . $privacy_condition;
		$sql .= ' AND UP.PrivacyLabelKey="add_in_group" AND ' . $privacy_condition;

                if(!$this->settings_model->isDisabled(1)) { // if group module is not disabled
                    $sql .= " UNION ALL";

                    $sql .= ' SELECT DISTINCT G.GroupGUID AS ModuleEntityGUID, G.GroupID AS ModuleEntityID, (CASE G.Type
                                                                WHEN "INFORMAL" THEN (
                                                                                                SELECT GROUP_CONCAT(CASE GMM.ModuleID WHEN 3 THEN CONCAT(US.FirstName," ",US.LastName) ELSE GG.GroupName END)
                                                                                                        FROM ' . GROUPMEMBERS . ' AS GMM LEFT JOIN ' . USERS . ' US ON US.UserID = GMM.ModuleEntityID AND GMM.ModuleID = 3
                                                                                                        LEFT JOIN ' . GROUPS . ' GG ON GG.GroupID = GMM.ModuleEntityID AND GMM.ModuleID = 1
                                                                                                        WHERE GMM.GroupID=G.GroupID AND US.UserID!=' . $this->UserID . '
                                                                                                        GROUP BY GMM.GroupID
                                                                                                        )
                                                                ELSE G.GroupName END) AS name';
                    $sql .= " , if(G.GroupImage!='',G.GroupImage,'group-no-img.jpg') AS ProfilePicture, G.IsPublic AS Privacy,1 AS ModuleID, G.Type, G.GroupDescription
                        FROM " . GROUPS . " AS G WHERE G.StatusID=2 ";

                    if (!empty($group_ids)) {
                        $sql .= " AND G.GroupID IN(" . implode(',', $group_ids) . ")";
                    }
                    if ($formal == 0) {
                        $sql .= " AND G.Type!='INFORMAL'";
                    }
                    if (!empty($remove_groups)) {
                        $remove_groups = implode("','", $remove_groups);
                        $sql .= " AND G.GroupID NOT IN ('$remove_groups')";
                    }
                    $sql .= " HAVING name LIKE '%" . $search_keyword . "%' OR G.GroupDescription LIKE '%" . $search_keyword . "%' ";
                }

		$sql .= " ORDER BY name ASC";
		$query = $this->db->query($sql);
		//echo $this->db->last_query();die;
		if ($query->num_rows()) {
			$result = $query->result_array();
			$return_array = array();
			foreach ($result as $key => $value) {
				$value['Location'] = '';
				$value['AllowedPostType'] = $this->get_post_permission_for_newsfeed($user_id);
				$value['IsAdmin'] = false;

				$entity_id = $value['ModuleEntityID'];

				if($value['ModuleID'] == 1) {
                                    if($group_id == $value['ModuleEntityID']) {
                                        continue;
                                    }
                                    if ($this->group_model->is_admin($user_id, $value['ModuleEntityID'])) {
                                            $value['IsAdmin'] = true;
                                    }
				}
				if ($value['ModuleID'] == 1) {
					$value['AllowedPostType'] = $this->group_model->get_post_permission($value['ModuleEntityID']);
				} else if ($user_id != $value['ModuleEntityID']) {

                                    /*user location*/
                                    $LocationArr = $this->user_model->get_user_location($entity_id);
                                    $Location = '';
                                    if (!empty($LocationArr['City']))
                                    {
                                        $Location .= $LocationArr['City'];
                                        if (!empty($LocationArr['StateCode']))
                                        {
                                            $Location .= ', ' . $LocationArr['StateCode'];
                                        }
                                        if (!empty($LocationArr['Country']))
                                        {
                                            $Location .= ', ' . $LocationArr['Country'];
                                        }
                                    }
                                    $value['Location']= $Location;
                                    /*user location*/

                                    $users_relation = get_user_relation($user_id, $value['ModuleEntityID']);
                                    $privacy_details = $this->privacy_model->details($value['ModuleEntityID']);
                                    $privacy = ucfirst($privacy_details['Privacy']);
                                    if ($privacy_details['Label']) {
                                        foreach ($privacy_details['Label'] as $privacy_label) {
                                            if (isset($privacy_label[$privacy])) {
                                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                                        $value['ProfilePicture'] = 'user_default.jpg';
                                                }
                                                if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                                                    $result[$key]['Location'] = '';
                                                }
                                            }
                                        }
                                    }
				}
				if ($value['Type'] == 'INFORMAL') {
                                    if ($value['ProfilePicture'] == 'group-no-img.jpg') {
                                            $value['ProfilePicture'] = '';
                                    }
                                    $Members = $this->get_group_members_details($value['ModuleEntityGUID']);
                                    $Name = '';
                                    if (!empty($Members)) {
                                            foreach ($Members as $key => $Member) {
                                                    if ($key > 3) {
                                                            $Name = trim($Name, ',');
                                                            $Name .= "...";
                                                    } else {
                                                            $Name .= $Member['name'] . ', ';
                                                    }
                                            }
                                            if (!empty($Name)) {
                                                    $Name = trim($Name, ', ');
                                            }
                                    }
                                    $value['Members'] = $Members;
                                    $value['name'] = $Name;
				}
				unset($value['ModuleEntityID']);
				$return_array[] = $value;
			}
			return $return_array;
		} else {
			return array();
		}
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
           $age_group_id = get_age_group_id($user_id);
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
		$is_super_admin = false;
		$roles = $this->cache->get('user_roles_' . $user_id);
                if($roles) {
                    if(in_array(1, $roles)) {
                        return 1;
                    } else if($is_sub_admin == 1 && in_array(6, $roles)) {
                        return 1;                        
                    } else {
                        return 0;
                    }
                }
		
                $where = array(1);
                if($is_sub_admin == 1) {
                    $where[] = 6;
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

	function get_recent_conversation($user_id, $post_as_module_id, $post_as_module_entity_id, $search) {
		$blocked_users = $this->activity_model->block_user_list($user_id, 3);
		$blocked_users[] = 0;
		$this->db->select('GROUP_CONCAT(ModuleEntityID) as ModuleEntityIDs');
		$this->db->from(BLOCKUSER);
		$this->db->where('ModuleID', 1);
		$this->db->where('EntityID', $user_id);
		$this->db->limit(1);
		$query = $this->db->get();
		// echo $this->db->last_query();die;
		$block_group_ids = '';
		if ($query->num_rows()) {
			$result = $query->row_array();
			if (!empty($result['ModuleEntityIDs'])) {
				$block_group_ids = $result['ModuleEntityIDs'];
			}
		}
		if (empty($block_group_ids)) {
			$block_group_ids = 0;
		}

		$this->load->model('group/group_model');
		$this->db->select('MAX(ID) as MyID,U.UserID');
		$this->db->select('UAL.ModuleID,UAL.ModuleEntityID,G.GroupDescription');

		$this->db->select('(CASE UAL.ModuleID WHEN 1 THEN G.GroupName WHEN 3 THEN U.FirstName END) AS FirstName', FALSE);
		$this->db->select('(CASE UAL.ModuleID WHEN 1 THEN "" WHEN 3 THEN U.LastName END) AS LastName', FALSE);
		$this->db->select('(CASE UAL.ModuleID WHEN 1 THEN G.GroupGUID WHEN 3 THEN U.UserGUID END) AS ModuleEntityGUID', FALSE);
		$this->db->select('(CASE UAL.ModuleID WHEN 1 THEN "Group" WHEN 3 THEN "User" END) AS EntityType', FALSE);
		$this->db->select('(CASE UAL.ModuleID WHEN 1 THEN IF(G.GroupImage!="",G.GroupImage,"group-no-img.jpg") WHEN 3 THEN IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) END) AS ProfilePicture', FALSE);
		$this->db->select('(CASE UAL.ModuleID WHEN 1 THEN G.Type WHEN 3 THEN "User" END) as Type', FALSE);
		$this->db->select('(CASE UAL.ModuleID WHEN 1 THEN G.IsPublic WHEN 3 THEN "" END) as IsPublic', FALSE);		

		$this->db->from(USERSACTIVITYLOG . ' UAL');
		$this->db->where('UAL.PostAsModuleID', $post_as_module_id);
		$this->db->where('UAL.PostAsModuleEntityID', $post_as_module_entity_id);
                
                $search = $this->db->escape_like_str($search);
                $this->db->where("(CASE UAL.ModuleID WHEN 1 THEN G.GroupName LIKE '%" . $search . "%'
                    OR
                    (CASE G.Type
                                    WHEN 'INFORMAL' THEN (
                                    SELECT GROUP_CONCAT(CASE GMM.ModuleID WHEN 3 THEN CONCAT(US.FirstName,' ',US.LastName) ELSE GG.GroupName END)
                                            FROM " . GROUPMEMBERS . " GMM
                                            LEFT JOIN " . USERS . " US ON `US`.`UserID` = `GMM`.`ModuleEntityID` AND `GMM`.`ModuleID` = 3
                                            LEFT JOIN " . GROUPS . " GG ON `GG`.`GroupID` = `GMM`.`ModuleEntityID` AND `GMM`.`ModuleID` = 1
                                            WHERE GMM.GroupID=G.GroupID
                                            GROUP BY GMM.GroupID
                                            )
                                    ELSE G.GroupName END)  LIKE '%" . $search . "%'

                    WHEN 3 THEN (U.FirstName LIKE '%" . $search . "%' OR U.LastName LIKE '%" . $search . "%' OR CONCAT(U.FirstName,' ',U.LastName) LIKE '%" . $search . "%') END)", NULL, FALSE);


                if (!$this->settings_model->isDisabled(1)) {
                    $this->db->where_in('UAL.ModuleID', array(1, 3));
                }else{
                    $this->db->where_in('UAL.ModuleID', array(3));
                }
		$this->db->where("IF(UAL.ModuleID=3,UAL.ModuleEntityID!='" . $user_id . "',true)", null, false);
		
		$this->db->_protect_identifiers = FALSE;
		$this->db->join(USERS . ' U', 'U.UserID=UAL.ModuleEntityID AND UAL.ModuleID=3 AND U.StatusID NOT IN (3,4)  AND U.UserID NOT IN(' . implode(',', $blocked_users) . ')', 'LEFT');
		$this->db->join(GROUPS . ' G', 'G.GroupID=UAL.ModuleEntityID AND UAL.ModuleID=1 AND G.GroupID NOT IN(' . $block_group_ids . ')', 'LEFT');
		/*if(!empty($block_group_ids))
			        {
			            $this->db->where_not_in('G.GroupID', explode(',', $block_group_ids));
		*/
                
		$this->db->where("IF(UAL.ModuleID=1,G.StatusID=2,true)",null,false);
		$this->db->group_by('UAL.ModuleID,UAL.ModuleEntityID');
		$this->db->order_by('MyID DESC');

		$this->db->_protect_identifiers = TRUE;
		$this->db->limit(5);
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		$data = array();

		if ($query->num_rows()) {
			foreach ($query->result_array() as $result) {
				if ($result['FirstName'] == '' && $result['ModuleID'] == 1) {
					$result['FirstName'] = $this->group_model->get_informal_group_name($result['ModuleEntityID'], $user_id);
				}
				$result['Members'] = array();
				$result['MembersCount'] = 0;
				$result['AllowedPostType'] = $this->get_post_permission_for_newsfeed($user_id);
				$result['IsAdmin'] = false;
				if ($result['EntityType'] == 'Group') {
                                    
                                    
                                    $group_url_details = $this->group_model->get_group_details_by_id($result['ModuleEntityID'], '', array(
                                        'GroupName' => $result['FirstName'],
                                        'GroupGUID' => $result['ModuleEntityGUID'],
                                    ));
                                    $result['ProfileURL'] = $this->group_model->get_group_url($result['ModuleEntityID'], $group_url_details['GroupNameTitle'], false, 'index');  
                                                            
                                    $result['AllowedPostType'] = $this->group_model->get_post_permission($result['ModuleEntityID']);

                                    if ($this->group_model->is_admin($user_id, $result['ModuleEntityID'])) {
                                            $result['IsAdmin'] = true;
                                    }
				} else {
                                    $result['ProfileURL'] = get_entity_url($result['ModuleEntityID'],'User',1);					
				}

				if ($result['Type'] == 'INFORMAL') {
					$Members = $this->get_group_members_details($result['ModuleEntityGUID']);
					$member_final_data = array();
					foreach ($Members as $Member) {
						if ($Member['ModuleID'] == 3) {
							if ($user_id != $Member['UserID']) {
								$users_relation = get_user_relation($user_id, $Member['UserID']);
								$privacy_details = $this->privacy_model->details($Member['UserID']);
								$privacy = ucfirst($privacy_details['Privacy']);
								if ($privacy_details['Label']) {
									foreach ($privacy_details['Label'] as $privacy_label) {
										if (isset($privacy_label[$privacy])) {
											if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
												$Member['ProfilePicture'] = 'user_default.jpg';
											}
										}
									}
								}
							}
						}
						$member_final_data[] = $Member;
					}
					$result['Members'] = $member_final_data;
				}

				if ($user_id != $result['UserID']) {
					$users_relation = get_user_relation($user_id, $result['UserID']);
					$privacy_details = $this->privacy_model->details($result['UserID']);
					$privacy = ucfirst($privacy_details['Privacy']);
					if ($privacy_details['Label']) {
						foreach ($privacy_details['Label'] as $privacy_label) {
							if (isset($privacy_label[$privacy])) {
								if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
									$result['ProfilePicture'] = 'user_default.jpg';
								}
							}
						}
					}
				}
				unset($result['UserID']);
				$data[] = $result;
			}
		}
		//print_r($data);
		return $data;
	}

	public function get_friends_and_group_for_invite($user_id, $page_no = 0, $page_size = 0) {
		$data = array();
		$privacy_condition = "
			IF(UP.Value='everyone',true,
				IF(UP.Value='network', U.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID),
				IF(UP.Value='friend',U.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
			)
		";
		$this->db->select('U.UserGUID AS ModuleEntityGUID, CONCAT(U.FirstName," ",U.LastName) AS FullName, ProfilePicture', FALSE);
		$this->db->from(USERS . ' AS U');
		$this->db->join(USERPRIVACY . ' UP', 'UP.UserID=U.UserID', 'LEFT');
		$this->db->where('U.StatusID=2 AND U.UserID != ' . $user_id . ' AND U.UserID!=' . $user_id . '', Null, FALSE);
		$this->db->where($privacy_condition, Null, FALSE);
		$this->db->distinct();
		$this->db->limit($page_size, $page_no);
		$res = $this->db->get();
		$data['Friends'] = $res->result_array();

		$this->db->select('G.GroupName,G.GroupImage AS ProfilePicture, G.IsPublic AS Privacy,1 AS ModuleID, G.Type, G.GroupDescription', FALSE);
		$this->db->from(GROUPS . ' AS G');
		$this->db->join(GROUPMEMBERS . ' AS GM', 'G.GroupID=GM.GroupID');
		$this->db->join(USERS . ' AS U', 'G.CreatedBy=U.UserID', 'INNER');
		$this->db->join(PROFILEURL . ' AS P', 'P.EntityID=U.UserID', 'LEFT');
		$this->db->where('P.EntityType="User" AND G.Type!="INFORMAL" AND G.CreatedBy=' . $user_id . '', Null, FALSE);
		$this->db->limit($page_size, $page_no);
		$res = $this->db->get();
		$data['Groups'] = $res->result_array();
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

	/**
	 * [get_user_interest Used to get user interest]
	 * @param  [int] $user_id [User ID]
	 * @return [array]        [User interest list]
	 */
	function get_user_interest($user_id, $page_no = 1, $page_size = 3, $count_only = false, $user_type = 0) {
        $this->load->model('settings_model');
        if($this->settings_model->isDisabled(31)){
            if($count_only == false)
                return array();
            else
                return 0;
        }
		$cache_data = array();
		if (CACHE_ENABLE && $page_size == 3 && $count_only == false) {
			$cache_data = $this->cache->get('user_interest_' . $user_id);
			if (!empty($cache_data)) {
				return $cache_data;
			}
		}
		$select_followers = "(SELECT COUNT(CategoryID) FROM " . ENTITYCATEGORY . " WHERE ModuleID='3' AND CategoryID=CM.CategoryID) as Followers";

		$this->db->select('CM.CategoryID,CM.Name, CM.Icon');
		$this->db->select('IFNULL(M.ImageName,"Interest-default.jpg") as ImageName', FALSE);
		$this->db->select('IFNULL(CM.Description,"") as Description', FALSE);
		$this->db->select('"1" as IsInterested, EC.ModuleEntityUserType', FALSE);
		$this->db->select($select_followers, FALSE);
		$this->db->from(CATEGORYMASTER . ' CM');
		$this->db->join(MEDIA . ' M', 'M.MediaID=CM.MediaID', 'left');
		$this->db->join(ENTITYCATEGORY . ' EC', "CM.CategoryID=EC.CategoryID AND EC.ModuleID=3 AND EC.ModuleEntityID='" . $user_id . "'", 'left outer');
		$this->db->where('CM.ModuleID', '31');
		$this->db->where('CM.StatusID', '2');
                if($user_type) {
                    $this->db->where('EC.ModuleEntityUserType', $user_type);
                }
		$this->db->where('EC.CategoryID is not NULL', null, false);
		$this->db->order_by('Followers', 'DESC');
		$this->db->group_by('CM.CategoryID');
		//$this->db->where('EC.CategoryID ',1);

		if ($page_no && $page_size && !$count_only) {
			$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
		}

		$query = $this->db->get();
                
		if ($count_only) {
			return $query->num_rows();
		}
		if ($query->num_rows()) {
			$result = $query->result_array();
			if (CACHE_ENABLE && $page_size == 3) {
				$this->cache->save('user_interest_' . $user_id, $result, 600);
			}
			return $result;
		} else {
			return array();
		}
	}

	/**
	 * [get_user_interest Used to get user interest]
	 * @param  [int] $user_id [User ID]
	 * @return [array]        [User interest list]
	 */
	function get_profile_fields($only_active = 0) {
		$this->db->select('pf.*');
		$this->db->from(PROFILEFIELDS . ' pf');
		if ($only_active == 1) {
			$this->db->where_in('pf.StatusID', array(2));
		} else {
			$this->db->where_in('pf.StatusID', array(2, 10));
		}

		$this->db->order_by('pf.PriorityOrder');
		$query = $this->db->get();
		if ($query->num_rows()) {
			return $query->result_array();
		}
	}

	/**
	 * [get_empty_profile_fields Used to get user empty field question]
	 * @return [array]        [User interest list]
	 */
	function get_empty_profile_fields($user_id) {
		$all_fields = $this->get_profile_fields(1);
		$profile_data = $this->profile($user_id, $user_id);
		if(isset($profile_data['LastQuestionDate']))
		{
			if ($profile_data['LastQuestionDate'] == get_current_date('%Y-%m-%d')) {
				return array();
			}
		}
//       print_r($profile_data);die;
		//check field emptyness, if field is not empty then remove it from list
		$questions = array();
		if($all_fields)
		{
			foreach ($all_fields as $key => $row) {
				//chech if empty or not fillup yet

				if(isset($profile_data[$row['FieldKey']]))
				{
					if ($row['FieldKey'] == 'Username' && (@$profile_data[$row['FieldKey']] == $profile_data['UserGUID'] || @$profile_data[$row['FieldKey']] == '')) {
						$questions[] = $row;
					} elseif ($row['FieldKey'] == 'RelationWithName' && $profile_data['MartialStatusTxt'] == '') {
						$questions[] = $row;
					} elseif ($row['FieldKey'] == 'Education' && empty($profile_data['UserEducation'])) {
						$row['mmmm'] = $profile_data['MartialStatusTxt'];
						$questions[] = $row;
					} elseif ($row['FieldKey'] == 'SocialProfile' && ($profile_data['FacebookUrl'] == '' && $profile_data['TwitterUrl'] == '' && $profile_data['LinkedinUrl'] == '' && $profile_data['GplusUrl'] == '')) {
						$row['mmmm'] = $profile_data['MartialStatusTxt'];
						$questions[] = $row;
					} elseif ((@$profile_data[$row['FieldKey']] == '' || empty(@$profile_data[$row['FieldKey']])) && $row['FieldKey'] != '') {
						if ($row['FieldKey'] != 'RelationWithName' && $row['FieldKey'] != 'Education' && $row['FieldKey'] != 'SocialProfile') {
							$questions[] = $row;
						}
					}
				}
			}
		}

		return $questions;
	}

	public function get_interest_suggestions($user_id, $keyword) {
		$this->db->select('CategoryID,Name');
		$this->db->from(CATEGORYMASTER);
		$this->db->where('ModuleID', '31');
		$this->db->where('StatusID', '2');
		$this->db->where('ParentID!="0"', NULL, FALSE);
		$query = $this->db->get();
		if ($query->num_rows()) {
			return $query->result_array();
		}
	}

	public function get_city_suggestions($user_id, $keyword) {
		$this->db->select('CityID,Name');
		$this->db->from(CITIES);
		$query = $this->db->get();
		if ($query->num_rows()) {
			return $query->result_array();
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

	public function get_popular_interest($user_id, $page_no, $page_size, $keyword = '', $exclude = array(), $interest_user_type = 0) {
		$condition = array('C.ModuleID' => '31', 'C.StatusID !=' => '3' ); // If only root category needed
		if (!empty($parent_category_id)) {
			$condition['C.ParentID'] = $parent_category_id; // If specific level of category needed
		}           
                

		if ($user_id) {
                    $interest_user_type_where = '';
                    if($interest_user_type) {
                        $interest_user_type_where = " AND ModuleEntityUserType = $interest_user_type";    
                    }
			$select_interested = "IF((SELECT CategoryID FROM " . ENTITYCATEGORY . " WHERE ModuleID='3' $interest_user_type_where  AND ModuleEntityID='" . $user_id . "' AND CategoryID=C.CategoryID) is not NULL,1,0) as IsInterested";
		} else {
			$select_interested = "'1' as IsInterested";
		}

		$select_followers = "(SELECT COUNT(CategoryID) FROM " . ENTITYCATEGORY . " WHERE ModuleID='3' AND CategoryID=C.CategoryID) as Followers";

		$this->db->select('C.CategoryID,C.ModuleID,C.ParentID,C.ParentID as Followers');
		$this->db->select('C.Name', FALSE);
		$this->db->select('C.Description', FALSE);
		$this->db->select('C.Icon', FALSE);
		$this->db->select('IF(MD.ImageName="" || MD.ImageName IS NULL || MD.ImageName=0,"",MD.ImageName) as ImageName', FALSE);
		$this->db->select('MD.MediaGUID AS MediaGUID', FALSE);
		$this->db->select('M.ModuleName', FALSE);
		$this->db->select($select_interested, FALSE);
		$this->db->select($select_followers, FALSE);
		$this->db->join(MEDIA . ' MD', 'MD.MediaID = C.MediaID', 'LEFT');
		$this->db->join(MODULES . ' M', 'M.ModuleID = C.ModuleID', 'LEFT');
		$this->db->from(CATEGORYMASTER . "  C");
		$this->db->select('S.StatusName as status, S.StatusID');
		$this->db->join(STATUS . ' S', 'S.StatusID=C.StatusID');
		if ($keyword) {
                    $keyword = $this->db->escape_like_str($keyword); 
			$this->db->where("C.Name LIKE '%" . $keyword . "%'", null, false);
		}
		if ($exclude) {
			$this->db->where_not_in('C.CategoryID', $exclude);
		}
                
                
                
		$this->db->where($condition);
		$this->db->order_by('Followers', 'DESC');
		$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
		$query = $this->db->get();
		return $query->result_array();
	}

	public function entities_i_follow($user_id, $page_no, $page_size) {
		$sql = "SELECT FirstName,LastName,ModuleID,ModuleEntityGUID,ProfilePicture,ProfileUrl,CreatedDate,1 as FollowStatus FROM (SELECT FirstName as 'FirstName',LastName as 'LastName',3 as ModuleID,UserGUID as ModuleEntityGUID,PU.Url as ProfileUrl,U.ProfilePicture,F.CreatedDate FROM `Users` as U JOIN Follow AS F ON U.UserID=F.TypeEntityID AND F.Type='User' AND F.UserID=" . $user_id . " JOIN ProfileUrl AS PU ON U.UserID=PU.EntityID AND PU.EntityType='User' WHERE U.StatusID=2 UNION ALL SELECT Title as 'FirstName','' as 'LastName',18 as ModuleID,PageGUID as ModuleEntityGUID,PageURL as ProfileUrl,if(P.ProfilePicture='',CM.Icon,P.ProfilePicture) as ProfilePicture ,F.CreatedDate FROM Pages as P JOIN Follow AS F ON P.PageID=F.TypeEntityID AND F.Type='Page' ANd P.StatusID=2 AND F.UserID=" . $user_id . " JOIN " . CATEGORYMASTER . " as CM ON P.CategoryID = CM.CategoryID) tbl order by createddate limit 5";
		$query = $this->db->query($sql);
		$result = array();
		if ($query->num_rows()) {
			$result = $query->result_array();
		}
		return $result;

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
	 * Function Name: get_dummy_users
	 * @param $page_no
         * @param $page_size
         * @param $only_users
         * @param $superAdminID
	 * Description: Used to get list of dummy/fake users.
	 */
        function get_dummy_users($page_no = 1, $page_size = 11, $only_users = false, $superAdminID = 0, $selectedUser = 0,$search='') {
            $users = [];
            $this->load->model(array(
                'notification_model', 
                'messages/messages_model'
            )); 
            
            $this->db->select("CONCAT(U.FirstName,' ',U.LastName) as Name, U.UserGUID as ModuleEntityGUID, '3' as ModuleID", false);
            $this->db->select("U.UserID as ModuleEntityID, U.ProfilePicture, PU.Url AS ProfileURL", false);
            $this->db->from(USERS . ' U');
            $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID AND PU.EntityType = 'User' ", "LEFT");
            
            $this->db->where('U.UserTypeID', 4);
            $this->db->where('U.StatusID', 2);
            if($search)
            {
                $search = $this->db->escape_like_str($search);
            	$this->db->where("(U.FirstName LIKE '%".$search."%' OR U.LastName LIKE '%".$search."%' OR CONCAT(U.FirstName,' ',U.LastName) LIKE '%".$search."%')",NULL,FALSE);
            }
            if($superAdminID) {
                $this->db->join(USERS . " as SAU", "SAU.UserID = U.UserID AND SAU.UserID = $superAdminID", "LEFT");
                 $this->db->or_where('U.UserID', $superAdminID);
                 $this->db->order_by(" FIELD(U.UserID, $selectedUser) DESC, FIELD(U.UserID, $superAdminID) DESC, U.FirstName ASC ", Null, false);
            }
                      
            if ($page_no && $page_size) {
		$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }
              
            $query = $this->db->get();
            //echo $this->db->last_query(); die;
            if ($query->num_rows()) {
                $users = $query->result_array();
            }
            
            if($only_users) {
                return $users;
            }
            
            foreach($users as $index => $user) {
                $user['TotalNotificationRecords'] = (int)$this->notification_model->get_new_notifications($user['ModuleEntityID'], 0, 0, true);
                $user['TotalMessageRecords']  = (int)$this->messages_model->get_total_unseen_count($user['ModuleEntityID']);
                
                $user['TotalNotificationRecords'] = $user['TotalNotificationRecords'] + $user['TotalMessageRecords'];
                $users[$index] = $user;
            }
            
            return $users;
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
        * [get_latest_users Get the list of users who signed up recently ]
        * @return [Array]
        */
        public function get_latest_users($post_data = [], $user_id = 0) {
            
            $page_no = !empty($post_data['page_no']) ? $post_data['page_no'] : 1;
            $page_size = !empty($post_data['page_size']) ? $post_data['page_size'] : 10;
            $max_days = (int)(!empty($post_data['max_days']) && $post_data['max_days'] > 0 ) ? $post_data['max_days'] : 5;
            $max_date_val = get_current_date('%Y-%m-%d', $max_days);
            
            $this->db->select("CONCAT(U.FirstName,' ',U.LastName) as Name, U.UserGUID as ModuleEntityGUID, '3' as ModuleID", false);
            $this->db->select("U.UserID as ModuleEntityID, U.ProfilePicture, PU.Url AS ProfileURL", false);
            $this->db->select("UD.TagLine");
            $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
            $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
            $this->db->from(USERS . ' U');
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
            $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID AND PU.EntityType = 'User' ", "LEFT");
            $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
            $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
            
            
            $this->db->where_in('U.StatusID', array(1,2,6,7));
            $this->db->where('U.FirstName != ""', NULL, FALSE);
            $this->db->where('U.LastName != ""', NULL, FALSE);
            $this->db->where('U.ProfilePicture != ""', NULL, FALSE);
            $this->db->where("U.CreatedDate >= '$max_date_val'", NULL, FALSE);
            $this->db->where('UD.TagLine != ""',NULL,FALSE);
            $this->db->where("U.UserID != $user_id", NULL, FALSE);
            $this->db->where('U.UserTypeID != 4', NULL, FALSE);
            
            $entities_total = $this->get_total_records();
            
            if ($page_no && $page_size) {
		$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }

            $this->db->order_by('U.CreatedDate','DESC');
            
            $query = $this->db->get();
            $entities = array();
            foreach($query->result_array() as $result)
            {
            	$result['Designation'] = $this->get_designation($result['ModuleEntityID']);
            	$entities[] = $result;
            }
            
            return array(
                'total' => $entities_total,
                'entities' => $entities
            );
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
            $total_count = $this->get_user_intrest_count($user_id);
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

    function get_user_intrest_count($user_id){
    	$this->db->select('CM.CategoryID');
		$this->db->from(CATEGORYMASTER . ' CM');
		$this->db->join(MEDIA . ' M', 'M.MediaID=CM.MediaID', 'left');
		$this->db->join(ENTITYCATEGORY . ' EC', "CM.CategoryID=EC.CategoryID AND EC.ModuleID=3 AND EC.ModuleEntityID='" . $user_id . "'", 'left outer');
		$this->db->where('CM.ModuleID', '31');
		$this->db->where('CM.StatusID', '2');
		$this->db->where('EC.CategoryID is not NULL', null, false);
		$this->db->group_by('CM.CategoryID');
		$query = $this->db->get();
		return $total_count = $query->num_rows();
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
        
    function directory($page_no, $page_size, $search_keyword, $is_admin, $count_only = 0, $user_id, $order_by = "Recent", $sort_by = "DESC") {
        $admin_data = array();
        if($page_no == 1 && $order_by == 'Recent' && $count_only == 0) {
            $admin_data = $this->directory_admin($search_keyword, $is_admin, $user_id, $order_by = "Recent", $sort_by = "DESC");
        }
        $admin_ids = array();
        if($order_by == 'Recent' && $count_only == 0) {
            $admin_ids = $this->directory_admin($search_keyword, $is_admin, $user_id, $order_by = "Recent", $sort_by = "DESC", TRUE);
        }
        
        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as EntityName, U.Email, U.Gender, U.UserGUID, U.UserID, UD.TimeZoneID,U.StatusID");
        $this->db->select('IFNULL(U.PhoneNumber,"") as PhoneNumber', FALSE);
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
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
            if($order_by == 'Recent') {
                //$this->db->where('UD.LocalityID', $this->LocalityID);
                if(!empty($admin_ids)) {
                    $this->db->where_not_in('U.UserID', $admin_ids);
                }
            } else {
                $this->db->join(USERROLES . ' UR', 'UR.UserID = U.UserID');   
                $this->db->where("(UR.RoleID IN (1, 2,6))");
            }        
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        
        if ($count_only) {
            $this->db->select('COUNT(DISTINCT U.UserID) as TotalRow ' );
            $this->db->join(USERROLES . ' UR', 'UR.UserID = U.UserID');   
            $this->db->where("(UR.RoleID IN (1,2,6))");
            
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
        
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $directory_data = array();
        
        $user_data = array();
        if($query->num_rows()) {
            foreach ($query->result_array() as $userdata) {
                if(!$is_admin) {
                    $userdata['PhoneNumber'] = "";
                }
                if($userdata['UserID'] == 145) {
                    $userdata['HouseNumber'] = ''; 
                 }
                $userdata['Location'] = $this->get_user_location($userdata['UserID']); 
               // $userdata['IsAdmin'] = 0;
                $userdata['IsAdmin'] = $this->is_super_admin($userdata['UserID'], 1);
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
    
    public function directory_admin($search_keyword, $is_admin, $user_id, $order_by = "Recent", $sort_by = "DESC", $only_ids=FALSE) {
                     
        
        $this->db->select("CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as FullName, CONCAT(IFNULL(U.FirstName,''), ' ',IFNULL(U.LastName,'')) as EntityName, U.Email, U.Gender, U.UserGUID, U.UserID, UD.TimeZoneID,U.StatusID");
        $this->db->select('IFNULL(U.PhoneNumber,"") as PhoneNumber', FALSE);
        $this->db->select('IFNULL(U.ProfilePicture,"") as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.HouseNumber,"") as HouseNumber', FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
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
                    if($userdata['UserID'] == 145) {
                       $userdata['HouseNumber'] = ''; 
                    }
                    $userdata['Location'] = $this->get_user_location($userdata['UserID']);                
                    $userdata['IsAdmin'] = $this->is_super_admin($userdata['UserID'], 1);               
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
                $userdata = "";
            }
        }
        if (empty($userdata)) {
            $this->db->select("U.FirstName, U.LastName, U.UserGUID");            
            $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
            $this->db->from(USERS . ' U');
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
            $this->db->where('U.UserID', $user_id);
            $query = $this->db->get();
            $userdata = $query->row_array();
            $userdata['Locality'] = array("Name" => "", "HindiName"=>"", "ShortName"=>"",  "LocalityID" => 0);
        }
        if(empty($userdata['Locality']['LocalityID'])) {
                $userdata['Locality']['LocalityID'] = 0;
            }
        return $userdata;
    }
}

?>
