<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Notification_model extends Common_Model {

	public $email_type;

	function __construct() {
		parent::__construct();
		//$this->email_type = array("comment" => 6, "media_comment" => 15, "rating_comments" => 16,"group_member_added"=>7,"tagged"=>8,"tagged_in_comment"=>9,"group_request_received"=>10,"group_request_accepted"=>11,"group_block"=>12,"share_post"=>13,"make_group_admin"=>14,"make_event_admin"=>14,"make_page_admin"=>14,'event_invitation'=>2,'event_request_accept_host'=>2,'event_cancel'=>2,'event_request_accept_admin'=>2,'friend_request_received'=>4,'friend_request_accepted'=>4,'new_message'=>4,'added_in_conversation'=>4,'event_edited_host'=>2,'event_post_added'=>2,'group_post'=>2,'page_post'=>2);
		$this->email_type = array('announcement_post' => 32, 'announcement_update' => 32, "question" => 6, "comment" => 6, "media_comment" => 6, "rating_comments" => 6,
			"group_member_added" => 7, "tagged" => 8, "tagged_in_comment" => 9, 'page_tagged' => 8, "group_request_received" => 10,
			"group_request_accepted" => 11, "group_block" => 12, "share_post" => 13, "make_group_admin" => 14, "make_forum_admin" => 14, "make_forum_category_admin" => 14, "make_forum_category_member" => 14, "make_group_admin_for_category" => 14, "forum_category_parent_admin" => 14,
			"make_event_admin" => 14, "make_page_admin" => 14, 'event_invitation' => 15, 'event_request_accept_host' => 18,
			'event_cancel' => 19, 'event_request_accept_admin' => 18, 'friend_request_received' => 4, 'friend_request_accepted' => 16,
			'new_message' => 20, 'added_in_conversation' => 21, 'event_edited_host' => 22, 'group_left' => 23,
			"flag_a_review" => 24, 'birthday_reminder' => 25, 'upcoming_events' => 26, 'weekly_digest_feed' => 27, 'share_your_post' => 13, 'review_marked_helpful' => 13, 
                        'rating_added' => 13, 'Skills' => 28, 'Endorsement' => 29, "tagged_in_media_comment" => 33,'activity_reminder'=>34, 'comment_on_tagged_post'=>36,
                        'inactive_user' => 40, 'incomplete_profile' => 41, 'winning_contest' => 42, 'contest_winner_announced' => 43, 'contest_end' => 44
                );
		$this->email_notification_modules = array('1', '2', '10', '14', '18','19', '23', '29', '30', '33', '34', '36');

	}

	/**
	 * Function Name: mark_as_seen
	 * @param UserID
	 * Description: Mark notification status as seen
	 */
	public function mark_as_seen($user_id) {
            $this->db->where('ToUserID', $user_id);
            $this->db->where('StatusID', '15');
            //$this->db->where('N.LocalityID', $this->LocalityID);
            $this->db->update(NOTIFICATIONS, array('StatusID' => '16'));
	}

	public function bubble_notifications($user_id, $notification_type, $notification_guid, $current_url) {
		$this->load->model('activity/activity_model');

		$this->db->select('RefrenceID,Params');
		$this->db->from(NOTIFICATIONS);
		$this->db->where('NotificationGUID', $notification_guid);
		$query = $this->db->get();

		$type = 'Bubble';

		if ($query->num_rows()) {
			$row_data = $query->row_array();

			$activity_url = get_single_activity_url($row_data['RefrenceID']);
			$activity_url = explode(site_url(), $activity_url);
			//$activity_url = "";
			//echo $current_url.' '.$activity_url[1];
			if (isset($activity_url[1])) {
				$activity_url = $activity_url[1];
			}

			$my_new_url = $current_url;
			$pos = strpos($current_url, '?');
			if ($pos !== false) {
				$my_new_url = explode('?', $current_url);
				$my_new_url = $my_new_url[0];
			}

			if ($activity_url == $my_new_url) {
				$type = 'Append';
				switch ($notification_type) {
				case '2':
				case '21':
				case '55':
				case '127':
				case '132':
					$params = json_decode($row_data['Params'], 1);
					$comment_id = $params['Comment'];
					$data = $this->activity_model->getSingleComment($comment_id, $user_id);
					break;
				case '3':
				case '54':
					$data = get_detail_by_id($row_data['RefrenceID'], 0, 'ActivityGUID', 1);
					break;
				case '131':
                    $data = $this->get_single_notifications($user_id, $notification_guid);
                    break;
				}
			} else {
				$data = $this->get_single_notifications($user_id, $notification_guid);
			}
		}
		$return_data = array('Data' => $data, 'Type' => $type);
		return $return_data;
	}

	/**
	 * Function Name: get_notification_username
	 * @param NotificationTypeID, RefrenceID, UserID
	 * Description: Get list of pagination or unreaded notification count depends on CountFlag
	 */
	function get_notification_username($user_id, $NotificationTypeID, $RefrenceID, $NotificationID = '') {
		$this->db->select('U.FirstName,U.LastName,U.ProfilePicture, U.UserID');
		$this->db->from(USERS . ' U');
		$this->db->join(NOTIFICATIONS . ' N', 'N.UserID=U.UserID');
		$this->db->where('N.ToUserID', $user_id);
		$this->db->where('N.NotificationTypeID', $NotificationTypeID);
		$this->db->where('N.RefrenceID', $RefrenceID);
		if (!empty($NotificationID)) {
			$this->db->where('N.NotificationID', $NotificationID);
		} else {
			$this->db->where('N.NotificationID = (SELECT max(NS.NotificationID) FROM Notifications NS WHERE N.NotificationTypeID=NS.NotificationTypeID AND N.RefrenceID=NS.RefrenceID AND N.UserID=NS.UserID AND NS.ToUserID="' . $user_id . '")', NULL, FALSE);
		}

		//$this->db->where('N.NotificationID = (SELECT max(NS.NotificationID) FROM Notifications NS WHERE N.NotificationTypeID=NS.NotificationTypeID AND N.RefrenceID=NS.RefrenceID AND N.UserID=NS.UserID AND NS.ToUserID='.$user_id.')',NULL,FALSE);
		$this->db->group_by('N.UserID');
		$this->db->order_by('N.NotificationID', 'desc');
		//$this->db->limit('1');
		$query = $this->db->get();
		$UserData = array('ProfilePicture' => '', 'UserName' => '');
		$NumRows = $query->num_rows();
		if ($NumRows) {
			$i = 1;
			foreach ($query->result_array() as $result) {

				if ($i == 1) {
					$UserData['ProfilePicture'] = $result['ProfilePicture'];
					$UserData['UserName'] = trim($result['FirstName'] . ' ' . $result['LastName']);

					if (!empty($user_id) && $user_id != $result['UserID']) {
						$users_relation = get_user_relation($user_id, $result['UserID']);
						$privacy_details = $this->privacy_model->details($result['UserID']);
						$privacy = ucfirst($privacy_details['Privacy']);
						if ($privacy_details['Label']) {
							foreach ($privacy_details['Label'] as $privacy_label) {
								if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
									$UserData['ProfilePicture'] = 'user_default.jpg';
								}
							}
						}
					}
				} else {
					if ($NumRows == 2) {
						$UserData['UserName'] .= ' and ' . trim($result['FirstName'] . ' ' . $result['LastName']);
					} else if ($NumRows == 3) {
						$UserData['UserName'] .= ', ' . trim($result['FirstName'] . ' ' . $result['LastName']) . ' and one other';
						return $UserData;
					} else {
						$UserData['UserName'] .= ', ' . trim($result['FirstName'] . ' ' . $result['LastName']) . ' and ' . ($NumRows - 2) . ' others';
						return $UserData;
					}
				}

				$i++;
			}
		}
		return $UserData;
	}

	/**
	 * Function Name: mark_as_read
	 * @param NotificationGUID, UserID
	 * Description: Mark notification as readed
	 */
	function mark_as_read($NotificationGUID, $user_id) {

		$query = $this->db->get_where(NOTIFICATIONS, array('NotificationGUID' => $NotificationGUID));
		if ($query->num_rows()) {
			$row = $query->row();
			$NotificationTypeID = $row->NotificationTypeID;
			$RefrenceID = $row->RefrenceID;
			$this->db->set('StatusID', '17');
			$this->db->where('NotificationTypeID', $NotificationTypeID);
			$this->db->where('RefrenceID', $RefrenceID);
			$this->db->where('ToUserID', $user_id);
			$this->db->update(NOTIFICATIONS);
		}
	}

	/**
	 * [get_gender Used to get user gender passive terms]
	 * @param  [int] $user_id [User ID]
	 * @return [string]       [his/her/their]
	 */
	function get_gender($user_id) {
            $return = 'their';
            $gender = get_detail_by_id($user_id, 3, 'Gender', 1);
            if (!empty($gender)) {
                if ($gender == '1') {
                        $return = 'his';
                } else if ($gender == '2') {
                        $return = 'her';
                }
            }
            return $return;
	}

	/**
	 * Function Name: get_activity_summary
	 * @param NotificationTypeID, RefrenceID
	 * Description: Generate text for notification
	 */
	function get_activity_summary($NotificationTypeID, $RefrenceID, $CustomParams, $user_id = 0) {
		$this->load->model('users/user_model');
		$data = array('Summary' => '', 'Album' => array());
		switch ($NotificationTypeID) {
		case 2:
		case 21:
		case 20:
		case 55:
			if ($CustomParams) {
				$CustomParams = json_decode($CustomParams, true);
				if (isset($CustomParams['Comment'])) {
					$RefrenceID = $CustomParams['Comment'];
				}
			}
			$this->db->select('PC.PostComment as Summary');
			$this->db->from(POSTCOMMENTS . ' PC');
			//$this->db->join(ACTIVITY.' A','A.ActivityID=PC.EntityID');
			//this->db->where('PC.EntityType','Activity');
			$this->db->where('PC.PostCommentID', $RefrenceID);
			$this->db->order_by('PC.CreatedDate', 'DESC');
			$this->db->limit(1);
			$query = $this->db->get();
			//echo $this->db->last_query();
			break;
		case 3:
		case 19:
		case 18:
		case 54:
                case 153:
			$this->load->model('activity/activity_model');
			$data['Album'] = $this->activity_model->get_albums($RefrenceID, $user_id);

			$post_content = get_detail_by_id($RefrenceID, 0, 'PostContent');
                        $data['Summary'] = $this->activity_model->parse_tag_html($post_content);
                        return $data;
			/* $this->db->select('PostContent as Summary');
			$this->db->from(ACTIVITY);
			$this->db->where('ActivityID', $RefrenceID);
			$this->db->limit(1);
			$query = $this->db->get(); */
			break;
		case 47:
		case 48:                
			$this->db->select('O.PostContent as Summary');
			$this->db->from(ACTIVITY . ' S');
			$this->db->join(ACTIVITY . ' O', 'O.ActivityID=S.ParentActivityID', 'left');
			$this->db->where('S.ActivityID', $RefrenceID);
			$this->db->limit(1);
			$query = $this->db->get();
			break;
		default:
			return $data;
			break;
		}
		if ($query->num_rows()) {
			$row = $query->row();
			$Summary = $row->Summary;
			/* preg_match_all('/{{([0-9]+)}}/', $Summary, $matches);
				              if(!empty($matches[1])){
				              foreach($matches[1] as $match){
				              $name = $this->user_model->getUserName($match);
				              $name = $name['FirstName'].' '.$name['LastName'];
				              $Summary    = str_replace('{{'.$match.'}}', $name, $Summary);
				              }
			*/
			$data['Summary'] = $this->activity_model->parse_tag_html($Summary);
			/* if(strlen($Summary)>50){
				              $Summary = substr($Summary, 0,50).'...';
			*/
		}
		return $data;
	}

	/**
	 * Function Name: get_notification_link
	 * @param NotificationTypeID, RefrenceID, UserID
	 * Description: Generate notification link according to its type
	 */
	function get_notification_link_phone($NotificationTypeID, $ReferenceID, $user_id, $ToUserID, $PageID = 0, $NotificationTypeKey, $entity_type, $CustomParams='') {
            //$this->load->model('activity/activity_model');
            if ($NotificationTypeKey == 'make_group_admin' || $NotificationTypeKey == 'make_event_admin' || $NotificationTypeKey == 'make_page_admin') {
                if ($entity_type == 'Page') {
                    $EntityGUID = get_detail_by_id($ReferenceID, 18, 'PageGUID');
                    return array("ModuleID" => 18, "ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "PAGE");                    
                } else if ($entity_type == 'Group') {				                                
                    // Get entity url
                    $this->load->model(array('group/group_model'));
                    $group_details = $this->group_model->get_group_details_by_id($ReferenceID);                    
                    $EntityGUID = $group_details['GroupGUID'];
                    return array("ModuleID" => 1, "ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "GROUP");
                } else if ($entity_type == 'Event') {                    
                    $entity = get_detail_by_id($ReferenceID, 14, "EventGUID, Title", 2);
                    $EntityGUID = $entity['EventGUID'];                    
                    return array("ModuleID" => 14, "ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "EVENTS");				
                }
            } else if ($NotificationTypeID == '22' || $NotificationTypeID == '23' || $NotificationTypeID == '25' || $NotificationTypeID == '43' || $NotificationTypeID == '44' || $NotificationTypeID == '86' || $NotificationTypeID == '124'|| $NotificationTypeID == '143') {
                $EntityGUID = get_guid_by_id($ReferenceID, 1);
                return array("ModuleID" => 1, "ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "GROUP");
            } else if ($NotificationTypeID == '4' || $NotificationTypeID == '24' || $NotificationTypeID == '83') {			                        
                // Get entity url
                $this->load->model(array('group/group_model'));
                $group_details = $this->group_model->get_group_details_by_id($ReferenceID);                    
                $EntityGUID = $group_details['GroupGUID'];
                return array("ModuleID" => 1, "ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "GROUP");			
            } elseif ($NotificationTypeID == '16') {
                $EntityGUID = get_guid_by_id($ReferenceID, 3);
                return array("ModuleID" => 3, "ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "USER");                
            } elseif ($NotificationTypeID == '5' || $NotificationTypeID == '17') {
                $EntityGUID = get_guid_by_id($ReferenceID, 3);                    
                return array("ModuleID" => 3, "ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "USER");			
            } elseif ($NotificationTypeID == '110' || $NotificationTypeID == '112') {
                $EntityGUID = get_guid_by_id($ReferenceID, 30); 
                return array("ModuleID" => 30, "ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "POLL");
            } elseif ($NotificationTypeID == '84' || $NotificationTypeID == '85' || $NotificationTypeID == '82' || $NotificationTypeID == '1' || $NotificationTypeID == '2' || $NotificationTypeID == '3' || $NotificationTypeID == '18' || $NotificationTypeID == '19' || $NotificationTypeID == '20' || $NotificationTypeID == '21' || $NotificationTypeID == '65' || $NotificationTypeID == '55' || $NotificationTypeID == '54' || $NotificationTypeID == '90' || $NotificationTypeID == '87') {
                    $this->load->helper('activity');

                    /*changed by gautam*/
                    $url = get_single_activity_url_phone($ReferenceID);

                    /* if (($NotificationTypeID == 2 || $NotificationTypeID == 55) && !is_array($url)) {
                            if ($CustomParams) {
                                    $comment_temp = json_decode($CustomParams);
                                    $comment_id = $comment_temp->Comment;
                                    if (!empty($comment_id)) {
                                            $url .= '?cguid=' . get_detail_by_id($comment_id, 20);
                                    }
                            }
                    } */
                    return $url;
            } elseif ($NotificationTypeID == '46') {

                $PageID = json_decode($PageID);
                if (isset($PageID->ReferenceID)) {
                   $PageID = $PageID->ReferenceID;
                }
                $EntityGUID = get_guid_by_id($ReferenceID,18);
                return array("ModuleID" => 18,"ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "ACTIVITY_PAGE");			
            } elseif (($NotificationTypeID > '25' && $NotificationTypeID < '43') || $NotificationTypeID == '56' || $NotificationTypeID == '57' || $NotificationTypeID == '58' || $NotificationTypeID == '59') {
                $entity = get_detail_by_id($ReferenceID, 14, "EventGUID, Title", 2);
                $EntityGUID = $entity['EventGUID'];
                return array("ModuleID" => 14,"ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "EVENTS");

            } elseif ($NotificationTypeID == '45') {
                    $EntityGUID = get_detail_by_id($ReferenceID, 18, 'PageGUID');
                    return array("ModuleID" => 18,"ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "PAGE");

            } elseif ($NotificationTypeID == '61') {
                    $PageID = json_decode($PageID);
                    if (isset($PageID->ReferenceID)) {
                            $PageID = $PageID->ReferenceID;
                    }			
                    $activity = get_detail_by_id($ReferenceID, 0, '*', 2);
                    $url = get_single_post_url($activity);
                    $EntityURL = site_url($url); 
            } elseif ($NotificationTypeID == '52' || $NotificationTypeID == '53') {
                    $PageID = json_decode($PageID);
                    if (isset($PageID->ReferenceID)) {
                            $PageID = $PageID->ReferenceID;
                    }
                    $EntityGUID = get_detail_by_id($ReferenceID, 23, 'RatingGUID', 1);
                    $page_guid = get_detail_by_id($PageID, 18, 'PageGUID') ;
                    return array("ModuleID"=>18,"ModuleEntityGUID" => $page_guid, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "RATINGS");

            } elseif ($NotificationTypeKey == 'review_marked_helpful' || $NotificationTypeID == '63' || $NotificationTypeID == '64') {
                    $EntityGUID = get_detail_by_id($ReferenceID, 23, 'RatingGUID', 1);
                    $page_guid = get_detail_by_id($PageID, 18, 'PageGUID') ;
                    return array("ModuleID"=>18,"ModuleEntityGUID" => $page_guid, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "RATINGS");			
            } elseif ($NotificationTypeID == '89' || $NotificationTypeID == '97' || $NotificationTypeID == '100' || $NotificationTypeID == '103' || $NotificationTypeID == '104' || $NotificationTypeID == '105' || $NotificationTypeID == '106' || $NotificationTypeID == '107' || $NotificationTypeID == '108' || $NotificationTypeID == '109') {
                    if ($NotificationTypeID == '97') {
                            return get_entity_url($ToUserID) . '/endorsment/' . get_guid_by_id($ReferenceID, 3);

                    } else {
                            return get_entity_url($ToUserID) . '/about';
                    }
            } elseif ($NotificationTypeID == '49' || $NotificationTypeID == '50' || $NotificationTypeID == '51') {
                $EntityGUID = get_detail_by_id($ReferenceID, 21);
                return array("ModuleID"=>21,"ModuleEntityGUID" => $EntityGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "Refer" => "MEDIA");
            } elseif($NotificationTypeID == '154') {
                return array("ModuleEntityGUID" => "", "EntityID" => "", "EntityGUID" => "", "EntityURL" => "", "Refer" => "BADGE_COUNT");			
            } else {
                    if ($NotificationTypeID == '47') {
                            $user_id = $ToUserID;
                    } else if ($NotificationTypeID == '48') {
                            $PageID = json_decode($PageID);
                            if (isset($PageID->ReferenceID)) {
                                    $PageID = $PageID->ReferenceID;
                            }
                            $user_id = $PageID;
                    }

                    $EntityGUID = get_guid_by_id($ReferenceID);
                    $EntityURL = get_entity_url($user_id) . '/activity/' . $EntityGUID;
                    $UserGUID = get_guid_by_id($user_id, 3, 'UserGUID');
                    return array("ModuleEntityGUID" => $UserGUID, "EntityID" => $ReferenceID, "EntityGUID" => $EntityGUID, "EntityURL" => $EntityURL, "Refer" => "ACTIVITY");			
            }
	}

	/**
	 * Function Name: get_notification_link
	 * @param NotificationTypeID, RefrenceID, UserID
	 * Description: Generate notification link according to its type
	 */
	function get_notification_link($NotificationTypeID, $ReferenceID, $user_id, $ToUserID, $PageID = 0, $NotificationTypeKey, $entity_type, $CustomParams) {
            
            if ($NotificationTypeKey == 'make_group_admin' || $NotificationTypeKey == 'make_event_admin' || $NotificationTypeKey == 'make_page_admin') {
                if ($entity_type == 'Page') {
                    return site_url('page') . '/' . get_detail_by_id($ReferenceID, 18, 'PageURL');
                } else if ($entity_type == 'Group') {
                    
                    // Get entity url
                    $this->load->model(array('group/group_model'));
                    $group_details = $this->group_model->get_group_details_by_id($ReferenceID);
                    $EntityURL = $this->group_model->get_group_url($ReferenceID, $group_details['GroupNameTitle'], false, 'index');
                    
                    return $EntityURL;
                } else if ($entity_type == 'Event') {   
                    $this->load->model(array('events/event_model'));
                    $url = $this->event_model->get_event_url_by_id($ReferenceID);
                    return site_url($url);
                }
            } else if ($NotificationTypeID == '22' || $NotificationTypeID == '23' || $NotificationTypeID == '25' || $NotificationTypeID == '43' || $NotificationTypeID == '44' || $NotificationTypeID == '86' || $NotificationTypeID == '124' || $NotificationTypeID == '143') {
                    return site_url('group') . '/' . $ReferenceID;
            } else if ($NotificationTypeID == '4' || $NotificationTypeID == '24' || $NotificationTypeID == '83') {
                
                // Get entity url
                $this->load->model(array('group/group_model'));
                $group_details = $this->group_model->get_group_details_by_id($ReferenceID);
                $EntityURL = $this->group_model->get_group_url($ReferenceID, $group_details['GroupNameTitle'], false, 'members');
                
                return $EntityURL;
            } elseif ($NotificationTypeID == '16') {
                    return get_entity_url($ReferenceID);
            } elseif ($NotificationTypeID == '5' || $NotificationTypeID == '17') {
                    return get_entity_url($ReferenceID) . '/connections';
            } elseif ($NotificationTypeID == '110' || $NotificationTypeID == '112') {
                    $this->load->helper('activity');
                    return get_single_activity_url($ReferenceID, 'Polls');
            } elseif ($NotificationTypeID == '84' || $NotificationTypeID == '85' || $NotificationTypeID == '82' || $NotificationTypeID == '1' || $NotificationTypeID == '2' || $NotificationTypeID == '3' || $NotificationTypeID == '18' || $NotificationTypeID == '19' || $NotificationTypeID == '20' || $NotificationTypeID == '21' || $NotificationTypeID == '65' || $NotificationTypeID == '55' || $NotificationTypeID == '54' || $NotificationTypeID == '90' || $NotificationTypeID == '87') {
                    $this->load->helper('activity');
                    $url = get_single_activity_url($ReferenceID);
                    if ($NotificationTypeID == 2 || $NotificationTypeID == 55) {
                            if ($CustomParams) {
                                    $comment_temp = json_decode($CustomParams);
                                    $comment_id = $comment_temp->Comment;
                                    if (!empty($comment_id)) {
                                            $url .= '?cguid=' . get_detail_by_id($comment_id, 20);
                                    }
                            }
                    }
                    return $url;
            } elseif ($NotificationTypeID == '46') {

                    $PageID = json_decode($PageID);
                    if (isset($PageID->ReferenceID)) {
                            $PageID = $PageID->ReferenceID;
                    }
                    return site_url('page') . '/' . get_detail_by_id($PageID, 18, 'PageURL') . '/activity/' . get_guid_by_id($ReferenceID);
            } elseif (($NotificationTypeID > '25' && $NotificationTypeID < '43') || $NotificationTypeID == '56' || $NotificationTypeID == '57' || $NotificationTypeID == '58' || $NotificationTypeID == '59') {
                $this->load->model(array('events/event_model'));    
                $url = $this->event_model->get_event_url_by_id($ReferenceID);
                    return site_url($url);
            } elseif ($NotificationTypeID == '45') {
                    $pageUrl = 'page/' . get_detail_by_id($ReferenceID, 18, 'PageURL') . '/followers';                
                    return site_url($pageUrl);
            } elseif ($NotificationTypeID == '61') {
                    $PageID = json_decode($PageID);
                    if (isset($PageID->ReferenceID)) {
                            $PageID = $PageID->ReferenceID;
                    }
                    return get_single_activity_url($ReferenceID);
            } elseif ($NotificationTypeID == '52' || $NotificationTypeID == '53') {
                    $PageID = json_decode($PageID);
                    if (isset($PageID->ReferenceID)) {
                            $PageID = $PageID->ReferenceID;
                    }
                    return site_url('page') . '/' . get_detail_by_id($PageID, 18, 'PageURL') . '/ratings/' . get_detail_by_id($ReferenceID, 23, 'RatingGUID', 1);
            } elseif ($NotificationTypeKey == 'review_marked_helpful' || $NotificationTypeID == '63' || $NotificationTypeID == '64') {
                    return site_url('page') . '/' . get_detail_by_id($PageID, 18, 'PageURL') . '/ratings/' . get_detail_by_id($ReferenceID, 23, 'RatingGUID', 1);
            } elseif ($NotificationTypeID == '89' || $NotificationTypeID == '97' || $NotificationTypeID == '100' || $NotificationTypeID == '103' || $NotificationTypeID == '104' || $NotificationTypeID == '105' || $NotificationTypeID == '106' || $NotificationTypeID == '107' || $NotificationTypeID == '108' || $NotificationTypeID == '109') {
                    if ($NotificationTypeID == '97') {
                            return get_entity_url($ToUserID) . '/endorsment/' . get_guid_by_id($ReferenceID, 3);

                    } else {
                            return get_entity_url($ToUserID) . '/about';
                    }
            } else if ($NotificationTypeID == 130 || $NotificationTypeID == 133 || $NotificationTypeID == 134 || $NotificationTypeID == 135 || $NotificationTypeID == 136) {
                    return site_url('forum');
            } else {
                if ($NotificationTypeID == '47') {
                        $user_id = $ToUserID;
                } else if ($NotificationTypeID == '48') {
                    $PageID = json_decode($PageID);
                    if (isset($PageID->ReferenceID)) {
                            $PageID = $PageID->ReferenceID;
                    }
                    $user_id = $PageID;
                } else if ($NotificationTypeID == 120) {
                        $ReferenceID = get_detail_by_id($ReferenceID, 20, 'EntityID', 1);
                }
                return get_single_activity_url($ReferenceID);
                //return get_entity_url($user_id) . '/activity/' . get_guid_by_id($ReferenceID);
            }
	}

	/**
	 * [add_notification description]
	 * @param [int] $NotificationTypeID [Notification Type ID]
	 * @param [int] $user_id             [From User ID]
	 * @param [array] $ToUser             [To User ID Array]
	 * @param [int] $RefrenceID         [It may be User ID, Group ID, Event ID AND it is depend on Notification Type ID]
	 * @param [array] $Parameters         [Notification details parameter which is used to convert notification template in readable form]
	 */
	function add_notification($NotificationTypeID, $user_id, $ToUser, $RefrenceID, $Parameters, $send_email = true, $forcely_add = 0, $params = array(), $locality_id=0) {

            if ($this->settings_model->isDisabled(9)) {
                    return;
            }
            $from_user_detail = get_detail_by_id($user_id, 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
            $from_user_detail['FullName'] = $from_user_detail['FirstName'] . ' ' . $from_user_detail['LastName'];
            if(empty($locality_id)) {
                $locality_id = $this->LocalityID;
            }
            
                    
            foreach ($ToUser as $ToUserID) {
                if ($NotificationTypeID == 2) {
                    $this->db->where('ModuleEntityID', $ToUserID);
                    $this->db->where('ModuleID', '3');
                    $this->db->where('EntityType', 'Activity');
                    $this->db->where('StatusID', '2');
                    $this->db->where('EntityID', $RefrenceID);
                    $this->db->where('MaxNotifications >', '0');
                    $this->db->set('MaxNotifications', 'MaxNotifications-1', FALSE);
                    $this->db->update(SUBSCRIBE);
                }
                if ($ToUserID) {
			//on notification add/update UNDONE Mydesk task
                    if(isset($this->return))
                    {
                        $this->load->model('activity/mydesk_model');
                        $this->mydesk_model->toggle_mydesk_task($RefrenceID,$ToUserID,'NOTDONE');
                    }

                    if ($user_id != $ToUserID || $forcely_add == 1) {
                        $GuID = get_guid();
                        $NotificationData = array(
                            'NotificationGUID' => $GuID,
                            'NotificationTypeID' => $NotificationTypeID,
                            'UserID' => $user_id,
                            'ToUserID' => $ToUserID,
                            'RefrenceID' => $RefrenceID,
                            'StatusID' => 15,
                            'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                            'Params' => '',
                            'LocalityID' => $locality_id
                        );
                        if ($params) {
                            $NotificationData['Params'] = json_encode($params);
                        }
                        $NotificationID = $this->insert(NOTIFICATIONS, $NotificationData);

                        $to_user_detail = get_detail_by_id($ToUserID, 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
                        $to_user_detail['FullName'] = $to_user_detail['FirstName'] . ' ' . $to_user_detail['LastName'];
                        $ToUserGUID = $to_user_detail['UserGUID'];
                        $NotificationData['ToUserDetails'] = $to_user_detail;
                        $NotificationData['FromUserDetails'] = $from_user_detail;

			notify_node('sendNotification', array('UserGUID' => $ToUserGUID, 'NotificationCount' => 1, 'NotificationTypeID' => $NotificationTypeID, 'NotificationGUID' => $GuID));

                        /*notify_node('notificationCount', array('UserGUID' => $ToUserGUID, 'NotificationCount' => 1));

                        notify_node('bubbleNotifications', array('NotificationTypeID' => $NotificationTypeID, 'NotificationGUID' => $GuID, 'UserGUID' => $ToUserGUID));*/
                        //use to send push notification for total unread count
                        //initiate_worker_job('SendPushMsg', array('ToUserID'=>$ToUserID, 'Subject'=>"Total Unread Count", 'notifications'=>array('UserID'=>$user_id, 'ToUserID'=>$ToUserGUID, 'UserID'=>$user_id, 'NotificationTypeID'=>154, 'NotificationTypeKey'=>"badge_count", 'RefrenceID'=>0, 'Params'=>'')),'','notification');
                        
                        $i = 1;
                        if ($Parameters) {
                            foreach ($Parameters as $Value) {
                                $Data = json_encode($Value);
                                $NotificationParamData = array(
                                        'NotificationID' => $NotificationID,
                                        'NotificationParamName' => $i,
                                        'NotificationParamValue' => $Data,
                                );
                                $this->insert(NOTIFICATIONPARAMS, $NotificationParamData);
                                $i++;
                            }
                        }

                        /* For email notification */
                        //var_dump($send_email);
                        if ($send_email) {
                            if (in_array($NotificationTypeID, array(1, 2, 4, 16, 17, 18, 19, 21, 47, 22, 23, 25, 26, 27, 28, 29, 30, 31, 32, 46, 48, 51, 52, 55, 56, 58, 60, 61, 62, 63, 64, 65, 67, 68, 70, 81, 82, 84, 85, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 115, 116, 118, 125, 126, 130, 131, 132, 133, 134, 135, 136, 137, 138, 145, 146, 149,152,155))) {
                                $NotificationData['NotificationID'] = $NotificationID;
                                $NotificationData['Parameters'] = $Parameters;
                                $n_type_id = $NotificationData['NotificationTypeID'];
                                if ($n_type_id == 32) {
                                    $n_type_id = 31;
                                }
                                if ($n_type_id == 63) {
                                    $n_type_id = 2;
                                }
                                if ($n_type_id == 58) {
                                    $n_type_id = 56;
                                }
                                if ($n_type_id == 26 || $n_type_id == 27) {
                                        $n_type_id = 28;
                                }    
                                $this->send_email_notification($NotificationData, $NotificationData['NotificationTypeID'], $n_type_id);
                            }
			} 
                    }
                }
            }
	}

	/**
	 * Function to send email notification
	 * @param notification_data(array)
	 */
	function send_email_notification($notification_data, $original_type = 0, $n_type_id = '') {
            $this->load->model('activity/activity_model');            
            
            $notification_type_data = $this->get_notification_type_data($notification_data['NotificationTypeID']);
            $template_key = isset($notification_type_data['NotificationTypeKey']) ? trim($notification_type_data['NotificationTypeKey']) : '';

            $notification_settings = $this->check_user_notification_settings($notification_data['ToUserID'], $template_key, $notification_type_data['ModuleID']);

            if (!empty($template_key) && $notification_settings['send_notification']) {
                $language = strtolower($this->config->item("language"));
                    
                $user_detail = array();
                $from_user_detail = array();
                if ($notification_data['ToUserID']) {                            
                    $user_detail = $notification_data['ToUserDetails'];
                }

                if ($notification_data['UserID']) {
                    $from_user_detail = $notification_data['FromUserDetails'];
                }

                $email_data = array();
                $email_data['IsResend'] = 0;
                $email_data['TemplateName'] = "emailer/n_" . $template_key;
                $email_data['Email'] = $user_detail['Email'];
                $email_data['UserID'] = $notification_data['ToUserID'];
                $testing = false;

                $notification_data['NotificationTypeKey']=$template_key;
                $notification_data['Summary'] = '';

                /*notification type key ends here*/
                switch ($template_key) {                        
                    case 'announcement_post':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['announcement_post'];
                        $email_data['StatusMessage'] = "";
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID,PostType', 2);                                    
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);              
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $owner_data_arr = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], 'GroupID,GroupName,GroupImage', 2);
                        $owner_data = array('UserID' => $owner_data_arr['GroupID'], 'FirstName' => $owner_data_arr['GroupName'], 'LastName' => '', 'ProfilePicture' => $owner_data_arr['GroupImage']);

                        $email_data['Data'] = array('activity_data' => $activity_data, 'owner_data' => $owner_data);
                        $email_data['Subject'] = "Announcement in " . $owner_data['FirstName'] . " " . $owner_data['LastName'];
                        break;
                    case 'announcement_update':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['announcement_update'];
                        $email_data['StatusMessage'] = "";
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID,PostType', 2);                        
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $owner_data_arr = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], 'GroupID,GroupName,GroupImage', 2);
                        $owner_data = array('UserID' => $owner_data_arr['GroupID'], 'FirstName' => $owner_data_arr['GroupName'], 'LastName' => '', 'ProfilePicture' => $owner_data_arr['GroupImage']);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'owner_data' => $owner_data);
                        $email_data['Subject'] = "Announcement updated in " . $owner_data['FirstName'] . " " . $owner_data['LastName'];
                        break;
                    case 'wall_post':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $notification_data['Summary'] = $activity_data['PostContent'];                            
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'owner_data' => $owner_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " posted on your wall";
                        break;
                    case 'video_process';
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'owner_data' => $owner_data);
                        $email_data['Subject'] = "Your video is ready to view. You can now watch it.";
                        break;
                    case 'post_message';
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'owner_data' => $owner_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " posted a new message.";
                        break;
                    case 'flag_post':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);                            
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'owner_data' => $owner_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " flagged a post";
                        break;
                    case 'activity_restored':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $parameters = $notification_data['Parameters'];
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);                        
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " restored a deleted post you were tagged in.";
                        $email_data['Data'] = array('activity_data' => $activity_data, 'owner_data' => $owner_data);
                        break;
                    case 'activity_restored_owner':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";

                        $parameters = $notification_data['Parameters'];
                        $module_id = 3;
                        if ($parameters[1]['Type'] == 'Event') {
                                $module_id = 14;
                        } else if ($parameters[1]['Type'] == 'Group') {
                                $module_id = 1;
                        } else if ($parameters[1]['Type'] == 'Page') {
                                $module_id = 18;
                        }
                        $module_entity_id = $parameters[1]['ReferenceID'];
                        $entity_details = $this->user_model->getUserName($from_user_detail['UserID'], $module_id, $module_entity_id);
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);                        
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " restored a deleted post on " . $entity_details['FirstName'] . ' ' . $entity_details['LastName'] . "'s wall.";
                        $email_data['Data'] = array('activity_data' => $activity_data, 'entity_data' => $entity_details, 'owner_data' => $owner_data);
                        break;
                    case 'comment':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $CustomParams = $notification_data['Params'];
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'ActivityTypeID,PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);                        
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        if ($CustomParams) {
                            $comment_temp = json_decode($CustomParams);
                            $comment_id = $comment_temp->Comment;
                            if (!empty($comment_id)) {
                                $cmnt = get_detail_by_id($comment_id, 20, 'PostCommentGUID', 2);
                                $activity_data['Link'] = $activity_data['Link'] . '?cguid=' . $cmnt['PostCommentGUID'];                                                
                            }
                        }

                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $entity_data = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], '*', 2);
                        if($activity_data['ModuleID'] == 14){
                            $this->load->model('events/event_model');
                            $entity_data['ProfileURL'] = $this->event_model->getViewEventUrl($entity_data['EventGUID'], $entity_data['Title'], false, 'wall');
                        }
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $comment_data = $this->activity_model->get_last_comment($notification_data['RefrenceID'], $from_user_detail['UserID']);
                        $comment_data['PostComment'] = $this->activity_model->parse_tag_html($comment_data['PostComment']);
                        $notification_data['Summary'] = $comment_data['PostComment'];
                        $comment_data['MediaImage'] = '';
                        if ($comment_data['IsMediaExists'] == 1) {
                            $media_data = $this->activity_model->get_comment_media($comment_data['PostCommentID']);
                            $comment_data['MediaImage'] = $media_data['ImageName'];
                        }

                        $is_owner = 0;
                        $entity_type = 'post';
                        if ($activity_data['ActivityTypeID'] == 5 || $activity_data['ActivityTypeID'] == 6 || $activity_data['ActivityTypeID'] == 13) {
                            $entity_type = 'album';
                        }
                        if ($owner_data['UserID'] == $user_detail['UserID']) {
                            $is_owner = 1;
                        }
                        if ($activity_data['ActivityTypeID'] == 16 || $activity_data['ActivityTypeID'] == 17) {
                            $entity_type = 'review';
                        }
                        $email_data['Data'] = array('is_owner' => $is_owner, 'activity_data' => $activity_data, 'entity_data' => $entity_data, 'owner_data' => $owner_data, 'comment_data' => $comment_data, 'entity_type' => $entity_type);

                        if ($is_owner) {
                            $email_data['Subject'] = $from_user_detail['FullName'] . " commented on your " . $entity_type;
                        } else {
                            $email_data['Subject'] = $from_user_detail['FullName'] . " commented on a " . $entity_type;
                        }
                        break;
                    case 'page_tagged':
                            /* $testing = true;
                                $email_data['EmailTypeID']    = $this->email_type['page_tagged'];
                                $email_data['StatusMessage']  = "";

                                $params = $notification_data['Parameters'];
                                $page_details = get_detail_by_id($notification_data['Parameters'][1]['ReferenceID'],18,'*',2);

                                $this->load->model('activity/activity_model');
                                $this->load->helper('activity');
                                $other_details          = $this->activity_model->getSingleUserActivity($notification_data['ToUserID'],$notification_data['RefrenceID']);
                                $activity_data          = get_detail_by_id($notification_data['RefrenceID'],0,'*',2);
                                $activity_data['Link']  = get_single_activity_url($notification_data['RefrenceID']);
                                $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'],$user_detail['UserID']);

                                $this->load->model('users/user_model');

                                $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);

                                $entity_data            = get_detail_by_id($activity_data['ModuleEntityID'],$activity_data['ModuleID'],'*',2);
                                if($activity_data['ModuleID'] == 14){
                                    $this->load->model('events/event_model');
                                    $entity_data['ProfileURL'] = $this->event_model->getViewEventUrl($entity_data['EventGUID'], $entity_data['Title'], false, 'wall');
                                }
                                $owner_data             = get_detail_by_id($activity_data['UserID'],3,'*',2);
                                $comment_data           = $this->activity_model->get_last_comment($activity_data['ActivityID'],$from_user_detail['UserID']);

                                $comment_data['PostComment'] = $this->activity_model->parse_tag_html($comment_data['PostComment']);
                                //$comment_data['PostComment'] = str_replace('Â­', '', $comment_data['PostComment']);
                                $comment_data['PostComment'] = html_entity_decode($comment_data['PostComment'], ENT_QUOTES, "UTF-8");
                                $email_data['Data']     = array('activity_data'=>$activity_data,'entity_data'=>$entity_data,'owner_data'=>$owner_data,'comment_data'=>$comment_data);

                            */
                            break;
                    case 'page_post':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        //$activity_data['PostTitle'] = $this->get_post_title($notification_data['RefrenceID'], $activity_data);
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        if ($activity_data['IsMediaExist'] == 1) {
                                $media_data = $this->activity_model->get_albums($activity_data['ActivityID'], $from_user_detail['UserID']);
                        } else {
                                $media_data = array();
                        }
                        $entity_data = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], '*', 2);
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'entity_data' => $entity_data, 'owner_data' => $owner_data, 'media_data' => $media_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " added new post in " . $entity_data['Title'];
                        break;
                    case 'event_post_added':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        if ($activity_data['IsMediaExist'] == 1) {
                                $media_data = $this->activity_model->get_albums($activity_data['ActivityID'], $from_user_detail['UserID']);
                        } else {
                                $media_data = array();
                        }
                        $entity_data = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], '*', 2);
                        $this->load->model('events/event_model');
                        $entity_data['ProfileURL'] = $this->event_model->getViewEventUrl($entity_data['EventGUID'], $entity_data['Title'], false, 'wall');

                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'entity_data' => $entity_data, 'owner_data' => $owner_data, 'media_data' => $media_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " added new post in " . $entity_data['Title'];
                        break;
                    case 'event_post':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        if ($activity_data['IsMediaExist'] == 1) {
                            $media_data = $this->activity_model->get_albums($activity_data['ActivityID'], $from_user_detail['UserID']);
                        } else {
                            $media_data = array();
                        }
                        $entity_data = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], '*', 2);
                        $this->load->model('events/event_model');
                        $entity_data['ProfileURL'] = $this->event_model->getViewEventUrl($entity_data['EventGUID'], $entity_data['Title'], false, 'wall');
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'entity_data' => $entity_data, 'owner_data' => $owner_data, 'media_data' => $media_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " added new post in " . $entity_data['Title'];
                        break;
                    case 'group_post':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        if ($activity_data['IsMediaExist'] == 1) {
                            $media_data = $this->activity_model->get_albums($activity_data['ActivityID'], $from_user_detail['UserID']);
                        } else {
                            $media_data = array();
                        }
                        $entity_data = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], '*', 2);
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'entity_data' => $entity_data, 'owner_data' => $owner_data, 'media_data' => $media_data);
                        if(!isset($entity_data['GroupName'])) {
                            return;
                        }
                        $email_data['Subject'] = $from_user_detail['FullName'] . " added new post in " . $entity_data['GroupName'];
                        break;
                    case 'tagged':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['tagged'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $other_details = $this->activity_model->getSingleUserActivity($notification_data['ToUserID'], $notification_data['RefrenceID']);
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);                           
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        $entity_data = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], '*', 2);

                        if($activity_data['ModuleID'] == 14){
                            $this->load->model('events/event_model');
                            $entity_data['ProfileURL'] = $this->event_model->getViewEventUrl($entity_data['EventGUID'], $entity_data['Title'], false, 'wall');
                        }
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $comment_data = $this->activity_model->get_last_comment($notification_data['RefrenceID'], $from_user_detail['UserID']);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'entity_data' => $entity_data, 'owner_data' => $owner_data, 'comment_data' => $comment_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " tagged you in " . $this->get_gender($from_user_detail['UserID']) . " post";
                        break;
                    case 'tagged_in_comment':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['tagged_in_comment'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $other_details = $this->activity_model->getSingleUserActivity($notification_data['ToUserID'], $notification_data['RefrenceID']);
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);                            
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $BUsers = $this->activity_model->block_user_list($user_detail['UserID'], 3);
                        $activity_data['NoOfComments'] = $this->activity_model->get_activity_comment_count('Activity', $notification_data['RefrenceID'], $BUsers); //$res['NoOfComments'];
                        $activity_data['NoOfLikes'] = $this->activity_model->get_like_count($notification_data['RefrenceID'], 'Activity', $BUsers); //
                        $this->load->model('users/user_model');
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);

                        $entity_data = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], '*', 2);
                        if($activity_data['ModuleID'] == 14){
                            $this->load->model('events/event_model');
                            $entity_data['ProfileURL'] = $this->event_model->getViewEventUrl($entity_data['EventGUID'], $entity_data['Title'], false, 'wall');
                        }
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $comment_data = $this->activity_model->get_last_comment($activity_data['ActivityID'], $from_user_detail['UserID']);
                        $comment_data['MediaImage'] = '';
                        if ($comment_data['IsMediaExists'] == 1) {
                                $media_data = $this->activity_model->get_comment_media($comment_data['PostCommentID']);
                                $comment_data['MediaImage'] = $media_data['ImageName'];
                        }
                        $comment_data['PostComment'] = $this->activity_model->parse_tag_html($comment_data['PostComment']);                        
                        $comment_data['PostComment'] = html_entity_decode($comment_data['PostComment'], ENT_QUOTES, "UTF-8");
                        $notification_data['Summary'] = $comment_data['PostComment'];
                        $email_data['Data'] = array('activity_data' => $activity_data, 'entity_data' => $entity_data, 'owner_data' => $owner_data, 'comment_data' => $comment_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " tagged you in " . $this->get_gender($from_user_detail['UserID']) . " comment";
                        break;
                    case 'tagged_in_media_comment':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['tagged_in_comment'];
                        $email_data['StatusMessage'] = "";
                        $this->load->helper('activity');
                        $other_details = $this->activity_model->getSingleUserActivity($notification_data['ToUserID'], $notification_data['RefrenceID']);
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $params = json_decode($activity_data['Params'], true);
                        $media_id = '';
                        if (!empty($params)) {
                            $media_guid = $params['MediaGUID'];
                            $media_data = get_detail_by_guid($media_guid, 21, 'MediaID', 2);
                            $media_id = $media_data['MediaID'];
                        }                            
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $this->load->model('users/user_model');
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $entity_data = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], '*', 2);
                        if($activity_data['ModuleID'] == 14){
                            $this->load->model('events/event_model');
                            $entity_data['ProfileURL'] = $this->event_model->getViewEventUrl($entity_data['EventGUID'], $entity_data['Title'], false, 'wall');
                        }
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $comment_data = array();
                        $comment_data = $this->activity_model->get_last_comment($media_id, $from_user_detail['UserID'], 'MEDIA');
                        if (!empty($comment_data)) {
                            $comment_data['MediaImage'] = '';
                            if ($comment_data['IsMediaExists'] == 1) {
                                    $media_data = $this->activity_model->get_comment_media($comment_data['PostCommentID']);
                                    $comment_data['MediaImage'] = $media_data['ImageName'];
                            }
                            $comment_data['PostComment'] = $this->activity_model->parse_tag_html($comment_data['PostComment']);                                
                            $comment_data['PostComment'] = html_entity_decode($comment_data['PostComment'], ENT_QUOTES, "UTF-8");
                            $notification_data['Summary'] = $comment_data['PostComment'];
                        }
                        $email_data['Data'] = array('activity_data' => $activity_data, 'entity_data' => $entity_data, 'owner_data' => $owner_data, 'comment_data' => $comment_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " tagged you in " . $this->get_gender($from_user_detail['UserID']) . " comment";
                        break;
                    case 'media_comment':
                        $email_data['EmailTypeID'] = $this->email_type['media_comment'];
                        $email_data['StatusMessage'] = "";
                        $media_data = get_detail_by_id($notification_data['RefrenceID'], 21, '*', 2);
                        $email_data['Data'] = array('media_data' => $media_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " Comment on your post";
                        break;
                    case 'rating_comments':
                        $email_data['EmailTypeID'] = $this->email_type['rating_comments'];
                        $email_data['StatusMessage'] = "";
                        $rating_data = get_detail_by_id($notification_data['RefrenceID'], 23, '*', 2);
                        $email_data['Data'] = array('rating_data' => $rating_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " Comment on your review";
                        break;
                    case 'group_member_added':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['group_member_added'];
                        $email_data['StatusMessage'] = "";                                                                        

                        // Get entity data
                        $this->load->model(array('group/group_model'));
                        $group_data = $this->group_model->get_group_details_by_id($notification_data['RefrenceID'], 'GroupImage, GroupDescription');
                        $group_data['EntityURL'] = $this->group_model->get_group_url($notification_data['RefrenceID'], $group_data['GroupNameTitle'], false, 'wall');
                        $email_data['Data'] = array('group_data' => $group_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " added you to " . $group_data['GroupName'];
                        break;
                    case 'group_request_received':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['group_request_received'];
                        $email_data['StatusMessage'] = "";
                        $this->load->model(array('group/group_model'));
                        $group_data = $this->group_model->get_group_details_by_id($notification_data['RefrenceID'], 'GroupImage, GroupDescription');  
                        $group_data['EntityURL'] = $this->group_model->get_group_url($notification_data['RefrenceID'], $group_data['GroupNameTitle'], false, 'wall');
                        $email_data['Data'] = array('group_data' => $group_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " invited you to join " . $group_data['GroupName'];
                        break;
                    case 'group_request_accepted':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['group_request_accepted'];
                        $email_data['StatusMessage'] = "";
                        $this->load->model(array('group/group_model'));
                        $group_data = $this->group_model->get_group_details_by_id($notification_data['RefrenceID'], 'GroupImage, GroupDescription');  
                        $group_data['EntityURL'] = $this->group_model->get_group_url($notification_data['RefrenceID'], $group_data['GroupNameTitle'], false, 'wall');
                        $email_data['Data'] = array('group_data' => $group_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " has accepted your request for " . $group_data['GroupName'];
                        break;
                    case 'group_block':
                        $email_data['EmailTypeID'] = $this->email_type['group_block'];
                        $email_data['StatusMessage'] = "";
                        $this->load->model(array('group/group_model'));
                        $group_data = $this->group_model->get_group_details_by_id($notification_data['RefrenceID'], 'GroupImage, GroupDescription');   
                        $group_data['EntityURL'] = $this->group_model->get_group_url($notification_data['RefrenceID'], $group_data['GroupNameTitle'], false, 'wall');
                        $email_data['Data'] = array('group_data' => $group_data);
                        $email_data['Subject'] = $group_data['GroupName'] . " is blocked";
                        break;
                    case 'share_post':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['share_post'];
                        $email_data['StatusMessage'] = "";
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $entity_type = 'post';
                        $parent_activity_type_id = get_detail_by_id($activity_data['ParentActivityID'], 0, 'ActivityTypeID', 1);
                        if ($parent_activity_type_id == 5 || $parent_activity_type_id == 6 || $parent_activity_type_id == 13) {
                            $entity_type = 'album';
                        }

                        if ($activity_data['IsMediaExist'] == 1) {
                            $parent_activity_data = get_detail_by_id($activity_data['ParentActivityID'], 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                            $media_data = $this->activity_model->get_albums($parent_activity_data['ActivityID'], $parent_activity_data['UserID']);
                            if (isset($media_data[0]['Media'])) {
                                $media_data = $media_data[0]['Media'];
                            } else {
                                $media_data = array();
                            }
                        } else {
                            $parent_activity_data = $activity_data;
                            $media_data = array();
                        }

                        $activity_data['Album'] = $this->activity_model->get_albums($activity_data['ParentActivityID'], $user_detail['UserID']);
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $parent_activity_data['PostContent'] = $this->activity_model->parse_tag_html($parent_activity_data['PostContent']);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        $email_data['Data'] = array('activity_data' => $activity_data, 'media_data' => $media_data, 'parent_activity' => $parent_activity_data, 'entity_type' => $entity_type);

                        $email_data['Subject'] = $from_user_detail['FullName'] . " shared a " . $entity_type . " with you";
                        break;
                    case 'share_your_post':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['share_your_post'];
                        $email_data['StatusMessage'] = "";
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $entity_type = 'post';
                        $parent_activity_type_id = get_detail_by_id($activity_data['ParentActivityID'], 0, 'ActivityTypeID', 1);
                        if ($parent_activity_type_id == 5 || $parent_activity_type_id == 6 || $parent_activity_type_id == 13) {
                            $entity_type = 'album';
                        }
                        $parent_activity_data = get_detail_by_id($activity_data['ParentActivityID'], 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        if ($activity_data['IsMediaExist'] == 1) {
                            $media_data = $this->activity_model->get_albums($parent_activity_data['ActivityID'], $parent_activity_data['UserID']);
                            $media_data = isset($media_data[0]['Media'])?$media_data[0]['Media']:array();
                        } else {
                            $media_data = array();
                        }

                        $shared_on_user = $notification_data['Parameters'][1]['ReferenceID'];
                        if ($from_user_detail['UserID'] == $shared_on_user) {
                            $gender = $this->get_gender($from_user_detail['UserID']);
                        } else {
                            $share_user_detail = get_detail_by_id($shared_on_user, 3, 'FirstName,LastName', 2);
                            $gender = $share_user_detail['FirstName'] . ' ' . $share_user_detail['LastName'] . "'s";
                        }

                        $activity_data['Album'] = $this->activity_model->get_albums($activity_data['ParentActivityID'], $user_detail['UserID']);
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $parent_activity_data['PostContent'] = $this->activity_model->parse_tag_html($parent_activity_data['PostContent']);
                        $notification_data['Summary'] = $activity_data['PostContent'];
                        $email_data['Data'] = array('activity_data' => $activity_data, 'media_data' => $media_data, 'parent_activity' => $parent_activity_data, 'from_gender' => $gender, 'entity_type' => $entity_type);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " shared your " . $entity_type . " on " . $gender . " wall";
                        break;
                    case 'make_group_admin':
                    case 'make_event_admin':
                    case 'make_page_admin':
                    case 'make_forum_admin':
                    case 'make_forum_category_admin':
                    case 'make_forum_category_member':
                    case 'make_group_admin_for_category':
                    case 'forum_category_parent_admin':
                    case 'forum_subcategory_parent_admin':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type[$template_key];
                        $email_data['StatusMessage'] = "";
                        $params = $notification_data['Parameters'];
                        if ($notification_data['NotificationTypeID'] == 133 || $notification_data['NotificationTypeID'] == 137 || $notification_data['NotificationTypeID'] == 138) {
                                $entity_type = $params[1]['Type'];
                        } else if ($notification_data['NotificationTypeID'] == 135 || $notification_data['NotificationTypeID'] == 136) {
                                $entity_type = $params[2]['Type'];
                        } else {
                                $entity_type = $params[0]['Type'];
                        }
                        if ($entity_type == 'Group') {
                            // Get entity data
                            $this->load->model(array('group/group_model'));
                            $group_data = $this->group_model->get_group_details_by_id($notification_data['RefrenceID'], 'GroupImage, GroupDescription');
                            $group_data['EntityURL'] = $this->group_model->get_group_url($notification_data['RefrenceID'], $group_data['GroupNameTitle'], false, 'wall');                                                                                                     
                            $email_data['Data'] = array('group_data' => $group_data);
                            $email_data['Subject'] = lang('notify_promoted_as_admin_of') . ' ' . $group_data['GroupName'];
                        } else if ($entity_type == 'Event') {
                            $this->load->helper('location');
                            $event_data = get_detail_by_id($notification_data['RefrenceID'], 14, '*', 2);
                            $this->load->model('events/event_model');
                            $event_data['ProfileURL'] = $this->event_model->getViewEventUrl($event_data['EventGUID'], $event_data['Title'], false, 'wall');
                            $event_data['ProfilePicture'] = $this->event_model->get_event_profile_picture($event_data['ProfileImageID']);
                            $event_data['Location'] = get_location_by_id($event_data['LocationID']);
                            $email_data['Data'] = array('event_data' => $event_data);
                            $email_data['Subject'] = "You are promoted as admin of " . $event_data['Title'];
                        } else if ($entity_type == 'Page') {
                            $page_data = get_detail_by_id($notification_data['RefrenceID'], 18, '*', 2);
                            $this->load->model('pages/page_model');
                            $category_details = $this->page_model->get_category_details($page_data['CategoryID']);
                            $page_data['CategoryName'] = $category_details['Name'];
                            $page_data['CategoryIcon'] = $category_details['Icon'];
                            $email_data['Data'] = array('page_data' => $page_data);
                            $email_data['Subject'] = "You are promoted as admin of " . $page_data['Title'];
                        } else if ($entity_type == 'Forum') {
                            $forum_data = get_detail_by_id($notification_data['RefrenceID'], 33, 'ForumID,ForumGUID,Name,Description', 2);
                            $email_data['Data'] = array('forum_data' => $forum_data);
                            $email_data['Subject'] = "You have been added as admin for forum " . $forum_data['Name'];
                        } else if ($entity_type == 'ForumCategory') {
                            if ($notification_data['NotificationTypeID'] == 133) {
                                $this->load->model(array('forum/forum_model'));
                                $forum_category_data = $this->forum_model->get_category_detail($notification_data['RefrenceID']);
                                $email_data['Data'] = array('ForumCategoryData' => $forum_category_data);
                                $email_data['Subject'] = "You have been added as admin for category " . $forum_category_data['Name'];
                            } else if ($notification_data['NotificationTypeID'] == 134) {
                                $this->load->model(array('forum/forum_model'));
                                $forum_category_data = $this->forum_model->get_category_detail($notification_data['RefrenceID']);
                                $email_data['Data'] = array('ForumCategoryData' => $forum_category_data);
                                $email_data['Subject'] = "You have been added as member for category " . $forum_category_data['Name'];
                            } else if ($notification_data['NotificationTypeID'] == 135) {
                                $this->load->model(array('forum/forum_model'));
                                $forum_category_data = $this->forum_model->get_category_detail($notification_data['RefrenceID']);
                                $group_data = get_detail_by_id($params[1]['ReferenceID'], 1, 'GroupName', 2);
                                $forum_category_data['GroupName'] = $group_data['GroupName'];
                                $email_data['Data'] = array('group_data' => $group_data);
                                $email_data['Data'] = array('ForumCategoryData' => $forum_category_data);
                                $email_data['Subject'] = "Your group " . $forum_category_data['GroupName'] . " has been added admin for category " . $forum_category_data['Name'];
                            } else if ($notification_data['NotificationTypeID'] == 137) {
                                $this->load->model(array('forum/forum_model'));
                                $forum_category_data = $this->forum_model->get_category_detail($notification_data['RefrenceID']);
                                $email_data['Data'] = array('ForumCategoryData' => $forum_category_data);
                                $email_data['Subject'] = $from_user_detail['FullName'] . " created a new category " . $forum_category_data['Name'];
                            } else if ($notification_data['NotificationTypeID'] == 138) {
                                $this->load->model(array('forum/forum_model'));
                                $forum_category_data = $this->forum_model->get_category_detail($notification_data['RefrenceID']);
                                $email_data['Data'] = array('ForumCategoryData' => $forum_category_data);
                                $email_data['Subject'] = $from_user_detail['FullName'] . " created a new subcategory " . $forum_category_data['Name'];
                            }
                        }
                        break;
                    case 'event_invitation':
                        $this->load->helper('location');
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['event_invitation'];
                        $email_data['StatusMessage'] = "";
                        $this->load->model('events/event_model');
                        $event_data = get_detail_by_id($notification_data['RefrenceID'], 14, '*', 2);
                        $event_data['Location'] = get_location_by_id($event_data['LocationID']);
                        $event_data['ProfilePicture'] = $this->event_model->get_event_profile_picture($event_data['ProfileImageID']);
                        $this->load->model('timezone/timezone_model');
                        $event_data['StartDate'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Date');
                        $event_data['StartTime'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Time');
                        $event_data['EndDate'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Date');
                        $event_data['EndTime'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Time');
                        $event_data['ProfileURL'] = $this->event_model->getViewEventUrl($event_data['EventGUID'], $event_data['Title'], false, 'wall');
                        $email_data['Data'] = array('event_data' => $event_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " invited you to " . $event_data['Title'];
                        break;
                    case 'event_cancel':
                    case 'event_cancel_to_admin':
                        $this->load->helper('location');
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['event_cancel'];
                        $email_data['StatusMessage'] = "";
                        $this->load->model('events/event_model');
                        $event_data = get_detail_by_id($notification_data['RefrenceID'], 14, '*', 2);
                        $event_data['Location'] = get_location_by_id($event_data['LocationID']);
                        $event_data['ProfilePicture'] = $this->event_model->get_event_profile_picture($event_data['ProfileImageID']);
                        $this->load->model('timezone/timezone_model');
                        $event_data['StartDate'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Date');
                        $event_data['StartTime'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Time');
                        $event_data['EndDate'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Date');
                        $event_data['EndTime'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Time');
                        $event_data['ProfileURL'] = $this->event_model->getViewEventUrl($event_data['EventGUID'], $event_data['Title'], false, 'wall');
                        $email_data['Data'] = array('event_data' => $event_data);
                        $email_data['Subject'] = "Event " . $event_data['Title'] . " is cancelled";
                        break;
                    case 'event_request_accept_host':
                    case 'event_request_accept_admin':
                        $this->load->helper('location');
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['event_request_accept_host'];
                        $email_data['StatusMessage'] = "";
                        $this->load->model('events/event_model');
                        $event_data = get_detail_by_id($notification_data['RefrenceID'], 14, '*', 2);
                        $event_data['Location'] = get_location_by_id($event_data['LocationID']);
                        $event_data['ProfilePicture'] = $this->event_model->get_event_profile_picture($event_data['ProfileImageID']);
                        $this->load->model('timezone/timezone_model');
                        $event_data['StartDate'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Date');
                        $event_data['StartTime'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Time');
                        $event_data['EndDate'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Date');
                        $event_data['EndTime'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Time');
                        $event_data['ProfileURL'] = $this->event_model->getViewEventUrl($event_data['EventGUID'], $event_data['Title'], false, 'wall');
                        $email_data['Data'] = array('event_data' => $event_data);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " has accepted the invitation to attend " . $event_data['Title'];
                        break;
                    case 'event_edited_host':
                    case 'event_edited_admin':
                    case 'event_edited_attending':
                    case 'event_edited_may_attending':
                        $this->load->helper('location');
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['event_edited_host'];
                        $email_data['StatusMessage'] = "";

                        $this->load->model('events/event_model');

                        $event_data = get_detail_by_id($notification_data['RefrenceID'], 14, '*', 2);
                        $event_data['Location'] = get_location_by_id($event_data['LocationID']);
                        $event_data['ProfilePicture'] = $this->event_model->get_event_profile_picture($event_data['ProfileImageID']);

                        if ($notification_data['NotificationTypeID'] == 26) {
                                $event_data['Status'] = 'hosting';
                        } elseif ($notification_data['NotificationTypeID'] == 27) {
                                $event_data['Status'] = 'admin';
                        } elseif ($notification_data['NotificationTypeID'] == 28) {
                                $event_data['Status'] = 'attending';
                        } elseif ($notification_data['NotificationTypeID'] == 29) {
                                $event_data['Status'] = 'maybe attending';
                        }
                        $this->load->model('timezone/timezone_model');
                        $event_data['StartDate'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Date');
                        $event_data['StartTime'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Time');
                        $event_data['EndDate'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Date');
                        $event_data['EndTime'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Time');
                        $event_data['ProfileURL'] = $this->event_model->getViewEventUrl($event_data['EventGUID'], $event_data['Title'], false, 'wall');
                        $email_data['Data'] = array('event_data' => $event_data);
                        $email_data['Subject'] = $event_data['Title'] . " is updated";
                        break;
                    /*case 'event_cancel_to_admin':
                            $email_data['EmailTypeID'] = $this->email_type['event_cancel_to_admin'];
                            $email_data['StatusMessage'] = "";

                            $event_data = get_detail_by_id($notification_data['RefrenceID'], 14, '*', 2);
                            $this->load->model('timezone/timezone_model');
                            $this->load->model('events/event_model');
                            $event_data['StartDate'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Date');
                            $event_data['StartTime'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Time');
                            $event_data['EndDate'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Date');
                            $event_data['EndTime'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Time');

                            $event_data['ProfileURL'] = $this->event_model->getViewEventUrl($event_data['EventGUID'], $event_data['Title'], false, 'wall');

                            $email_data['Data'] = array('event_data' => $event_data);

                            $email_data['Subject'] = "Event " . $event_data['Title'] . " is cancelled";
                            break;
                     case 'event_post_added':
                            $email_data['EmailTypeID'] = $this->email_type['event_post_added'];
                            $email_data['StatusMessage'] = "";

                            $event_data = get_detail_by_id($notification_data['RefrenceID'], 14, '*', 2);
                            $this->load->model('timezone/timezone_model');
                            $event_data['StartDate'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Date');
                            $event_data['StartTime'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_detail['UserID'], 'Time');
                            $event_data['EndDate'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Date');
                            $event_data['EndTime'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_detail['UserID'], 'Time');
                            $email_data['Data'] = array('event_data' => $event_data);

                            $email_data['Subject'] = $from_user_detail['FullName'] . " added a new post on " . $event_data['Title'] . " wall";
                            break;    **/

                    case 'friend_request_received':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['friend_request_received'];
                        $email_data['StatusMessage'] = "";
                        $this->load->model('users/friend_model');
                        $mutual_friend = $this->friend_model->get_mutual_friend($from_user_detail['UserID'], $user_detail['UserID'], '', 0, 1, 1);
                        if (isset($mutual_friend['Friends'][0])) {
                            $mutual_friend = $mutual_friend['Friends'][0];
                        } else {
                            $mutual_friend = false;
                        }
                        $email_data['Data'] = array('mutual_friend' => $mutual_friend);
                        $email_data['Subject'] = $from_user_detail['FullName'] . " sent you friend request";
                        break;
                    case 'friend_request_accepted':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['friend_request_accepted'];
                        $email_data['StatusMessage'] = "";
                        $email_data['Data'] = array();
                        $email_data['Subject'] = $from_user_detail['FullName'] . " accepted your friend request";
                        break;
                    case 'rating_added':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['rating_added'];
                        $email_data['StatusMessage'] = "";
                        $this->load->model('ratings/rating_model');
                        $rating_data = get_detail_by_id($notification_data['RefrenceID'], 23, '*', 2);
                        $page_data = get_detail_by_id($rating_data['ModuleEntityID'], $rating_data['ModuleID'], '*', 2);
                        $review_data = $this->rating_model->get_review($rating_data['RatingID']);
                        $entity_data['Name'] = $from_user_detail['FirstName'] . ' ' . $from_user_detail['LastName'];
                        $entity_data['ProfilePicture'] = $from_user_detail['ProfilePicture'];
                        $entity_data['ProfileURL'] = get_entity_url($from_user_detail['UserID'], "User", 1);

                        if (isset($notification_data['Parameters'][0]['Type'])) {
                            $module_id = 3;
                            switch ($notification_data['Parameters'][0]['Type']) {
                            case 'User':
                                $module_id = 3;
                                break;
                            case 'Page':
                                $module_id = 18;
                                break;
                            }
                            $entity_data_details = get_detail_by_id($notification_data['Parameters'][0]['ReferenceID'], $module_id, '*', 2);
                            if ($module_id == 18) {
                                $entity_data['Name'] = $entity_data_details['Title'];
                                $entity_data['ProfilePicture'] = $entity_data_details['ProfilePicture'];
                                $entity_data['ProfileURL'] = 'page/' . $entity_data_details['PageURL'];
                            }
                        }
                        $email_data['Data'] = array('rating_data' => $rating_data, 'page_data' => $page_data, 'review_data' => $review_data, 'entity_data' => $entity_data);
                        $email_data['Subject'] = $entity_data['Name'] . " posted a review on " . $page_data['Title'];
                        break;
                    case 'new_message':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['new_message'];
                        $email_data['StatusMessage'] = "";

                        $message_data = get_detail_by_id($notification_data['RefrenceID'], 25, '*', 2);
                        $thread_guid = $this->db->get_where(N_MESSAGE_THREAD, array('ThreadID' => $message_data['ThreadID']))->row()->ThreadGUID;
                        $email_data['Data'] = array('message_data' => $message_data, 'thread_guid' => $thread_guid);

                        $email_data['Subject'] = $from_user_detail['FullName'] . " sent you a message";
                        break;
                    case 'added_in_conversation':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['added_in_conversation'];
                        $email_data['StatusMessage'] = "";

                        $message_data = get_detail_by_id($notification_data['RefrenceID'], 25, '*', 2);
                        $thread_guid = $this->db->get_where(N_MESSAGE_THREAD, array('ThreadID' => $message_data['ThreadID']))->row()->ThreadGUID;
                        $email_data['Data'] = array('thread_guid' => $thread_guid);

                        $email_data['Subject'] = $from_user_detail['FullName'] . " has added you to conversation";
                        break;
                    case 'birthday_reminder':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['birthday_reminder'];
                        $email_data['StatusMessage'] = "";

                        $email_data['Data'] = array('friends_data' => $notification_data['FriendsData'], 'Subject' => $notification_data['Subject']);

                        $email_data['FromUserID'] = $notification_data['FriendsData']['FromUserID'];
                        $email_data['Subject'] = $notification_data['Subject'];
                        break;
                    case 'daily_digest':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['daily_digest'];
                        $email_data['StatusMessage'] = "";

                        $email_data['Data'] = array('activity_data' => $notification_data['ActivityData']);

                        $email_data['Subject'] = "Weekly Digest";
                        break;
                    case 'upcoming_events':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['upcoming_events'];
                        $email_data['StatusMessage'] = "";

                        $this->load->model('events/event_model');

                        $event_data = $notification_data['EventData'];

                        $email_data['Data'] = array('event_data' => $event_data, 'Subject' => $notification_data['Subject']);

                        $email_data['Subject'] = $notification_data['Subject'];
                        break;
                    case 'review_marked_helpful':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['review_marked_helpful'];
                        $email_data['StatusMessage'] = "";

                        $rating_data = get_detail_by_id($notification_data['RefrenceID'], 23, '*', 2);
                        $page_data = get_detail_by_id($rating_data['ModuleEntityID'], $rating_data['ModuleID'], '*', 2);
                        $review_data = $this->rating_model->get_review($rating_data['RatingID']);
                        $email_data['Data'] = array('rating_data' => $rating_data, 'page_data' => $page_data, 'review_data' => $review_data);

                        $email_data['Subject'] = $from_user_detail['FullName'] . " has marked your review as helpful";
                        break;
                    case 'flag_a_review':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['flag_a_review'];
                        $email_data['StatusMessage'] = "";
                        $this->load->model('ratings/rating_model');
                        $rating_data = get_detail_by_id($notification_data['RefrenceID'], 23, '*', 2);
                        $flag_data = $this->rating_model->get_flag_data($rating_data['RatingID'], $notification_data['UserID']);
                        $page_data = get_detail_by_id($rating_data['ModuleEntityID'], $rating_data['ModuleID'], '*', 2);
                        $review_data = $this->rating_model->get_review($rating_data['RatingID']);
                        $email_data['Data'] = array('rating_data' => $rating_data, 'page_data' => $page_data, 'review_data' => $review_data, 'flag_data' => $flag_data);

                        $email_data['Subject'] = $from_user_detail['FullName'] . " has flagged a review you posted";
                        break;
                    case 'skill_approved':
                        $testing = true;
                        $parameters = $notification_data['Parameters'];
                        $skill_id = $parameters[0]['ReferenceID'];
                        $this->load->model('admin/skills_model');
                        $skill_data = $this->skills_model->get_skills_by_id($skill_id);
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $email_data['Subject'] = " Admin approved new skills added by you.";
                        $email_data['Data']['Skill'] = $skill_data;
                        break;
                    case 'skill_rejected':
                        $testing = true;
                        $parameters = $notification_data['Parameters'];
                        $skill_id = $parameters[0]['ReferenceID'];
                        $this->load->model('admin/skills_model');
                        $skill_data = $this->skills_model->get_skills_by_id($skill_id);
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $email_data['Subject'] = " Admin disapproved new skills added by you.";
                        $email_data['Data']['Skill'] = $skill_data;
                        break;
                    case 'removed_skill':
                        $testing = true;
                        $parameters = $notification_data['Parameters'];
                        $skill_id = $parameters[0]['ReferenceID'];
                        $this->load->model('admin/skills_model');
                        $skill_data = $this->skills_model->get_skills_by_id($skill_id);
                        $skill_name_array = array();
                        if ($skill_data['ParentCategorName']) {
                            $skill_name_array[] = $skill_data['ParentCategorName'];
                        }

                        if ($skill_data['CategoryName']) {
                            $skill_name_array[] = $skill_data['CategoryName'];
                        }

                        if ($skill_data['Name']) {
                            $skill_name_array[] = $skill_data['Name'];
                        }

                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $email_data['Subject'] = " Admin removed skill added on your profile.";
                        $email_data['Data']['Skill'] = $skill_data;
                        break;
                    case 'skill_edited':
                        $testing = true;
                        $parameters = $notification_data['Parameters'];
                        $skill_id = $parameters[0]['ReferenceID'];
                        //$skill_data = get_data('Name', SKILLSMASTER, array('SkillID' => $skill_id), 1, '');
                        $this->load->model('admin/skills_model');
                        $skill_data = $this->skills_model->get_skills_by_id($skill_id);
                        if ($skill_data['ParentCategorName']) {
                            $skill_name_array[] = $skill_data['ParentCategorName'];
                        }

                        if ($skill_data['CategoryName']) {
                            $skill_name_array[] = $skill_data['CategoryName'];
                        }

                        if ($skill_data['Name']) {
                            $skill_name_array[] = $skill_data['Name'];
                        }

                        $skill_data['OldData'] = $parameters[0]['SkillData']['OldData'];
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $email_data['Subject'] = " Admin edited skill added on your profile.";
                        $email_data['Data']['Skill'] = $skill_data;
                        break;
                    case 'skill_subcat_edited':
                    case 'skill_category_edited':
                        $testing = true;
                        $parameters = $notification_data['Parameters'];
                        $category_id = $parameters[0]['ReferenceID'];                            
                        $this->load->model('admin/skills_model');
                        $skill_data_new = $this->skills_model->get_skills_by_category($category_id, 3, $notification_data['ToUserID']);
                        $skill_data['OldData'] = $parameters[0]['CategoryData']['OldData'];
                        $skill_data['NewData'] = $skill_data_new;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $email_data['Subject'] = " Admin edited skill added on your profile.";
                        $email_data['Data']['Skill'] = $skill_data;
                        break;
                    case 'endorse_existing_skill':
                    case 'endorse_new_skill':
                        $testing = true;
                        $parameters = $notification_data['Parameters'];
                        $skill_id_array = $parameters[1]['SkillData'];
                        $user_id = $parameters[0]['ReferenceID'];
                        $this->load->model('users/user_model');
                        $entity_details = $this->user_model->getUserName($user_id, 3);
                        $email_data['Data']['EndorseUser'] = $entity_details['FirstName'] . ' ' . $entity_details['LastName'];
                        $email_data['Data']['EndorseUserImage'] = $entity_details['ProfilePicture'];
                        $email_data['Data']['EndorseUserGUID'] = $entity_details['ModuleEntityGUID'];
                        if (!empty($skill_id_array)) {
                            $email_data['Data']['Skill'] = array();
                            foreach ($skill_id_array as $item) {
                                $skill_data = $this->skills_model->get_skills_by_id($item);
                                $skill_name_array = array();
                                if ($skill_data['ParentCategorName']) {
                                    $skill_name_array[] = $skill_data['ParentCategorName'];
                                }

                                if ($skill_data['CategoryName']) {
                                    $skill_name_array[] = $skill_data['CategoryName'];
                                }

                                if ($skill_data['Name']) {
                                    $skill_name_array[] = $skill_data['Name'];
                                }

                                $email_data['Data']['Skill'][] = $skill_data;
                            }
                            $email_data['EmailTypeID'] = $this->email_type['comment'];
                            $email_data['StatusMessage'] = "";
                            $email_data['Subject'] = $email_data['Data']['EndorseUser'] . " endorsed you";
                        }
                        break;
                    case 'skill_merged':
                        $testing = true;
                        $parameters = $notification_data['Parameters'];
                        $skill_id = $parameters[0]['ReferenceID'];                            
                        $this->load->model('admin/skills_model');
                        $skill_data_new = $this->skills_model->get_skills_by_id($skill_id);
                        $skill_data['OldData'] = $parameters[0]['SkillData']['OldData'];
                        $skill_data['NewData'] = $skill_data_new;
                        $email_data['EmailTypeID'] = $this->email_type['comment'];
                        $email_data['StatusMessage'] = "";
                        $email_data['Subject'] = " Admin merged skill added on your profile.";
                        $email_data['Data']['Skill'] = $skill_data;
                        break;
                    case 'reply_on_question':
                    case 'request_reply_on_question':
                    case 'request_a_question':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['question'];
                        $email_data['StatusMessage'] = "";
                        $CustomParams = $notification_data['Params'];
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'ActivityTypeID,PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        //$activity_data['PostTitle'] = $this->get_post_title($notification_data['RefrenceID'], $activity_data);
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'], $user_detail['UserID']);
                        $entity_data = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], '*', 2);
                        if($activity_data['ModuleID'] == 14){
                            $this->load->model('events/event_model');
                            $entity_data['ProfileURL'] = $this->event_model->getViewEventUrl($entity_data['EventGUID'], $entity_data['Title'], false, 'wall');
                        }
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);                        

                        $is_owner = 0;
                        $entity_type = 'post';
                        if ($activity_data['ActivityTypeID'] == 5 || $activity_data['ActivityTypeID'] == 6 || $activity_data['ActivityTypeID'] == 13) {
                            $entity_type = 'album';
                        }
                        if ($owner_data['UserID'] == $user_detail['UserID']) {
                            $is_owner = 1;
                        }
                        if ($activity_data['ActivityTypeID'] == 16 || $activity_data['ActivityTypeID'] == 17) {
                            $entity_type = 'review';
                        }
                        $email_data['Data'] = array('is_owner' => $is_owner, 'activity_data' => $activity_data, 'entity_data' => $entity_data, 'owner_data' => $owner_data, 'entity_type' => $entity_type);

                        $email_data['TemplateName'] = "emailer/n_question";
                        $email_data['Data']['description_text'] = "";
                        if ($template_key == 'request_a_question') {
                            $email_data['Data']['description_text'] = " ask a question to you - ";
                        } elseif ($template_key == 'reply_on_question') {
                            $email_data['Data']['description_text'] = " replied to your question ";
                        } elseif ($template_key == 'request_reply_on_question') {
                            $email_data['Data']['description_text'] = " replied to a question on your request - ";
                        }

                        $entity_name = '';
                        if ($activity_data['ModuleID'] == 1) {
                            $entity_name = $entity_data['GroupName'];
                        } elseif ($activity_data['ModuleID'] == 14) {
                            $entity_name = $entity_data['Title'];
                        } elseif ($activity_data['ModuleID'] == 18) {
                            $entity_name = $entity_data['Title'];
                        }
                        $email_data['Subject'] = $from_user_detail['FullName'] . $email_data['Data']['description_text'] . $entity_name;
                        break;
                    case 'comment_on_tagged_post':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['comment_on_tagged_post'];
                        $email_data['StatusMessage'] = "";
                        $CustomParams = $notification_data['Params'];
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'ActivityTypeID,PostContent,PostTitle,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        if ($CustomParams) {
                            $comment_temp = json_decode($CustomParams);
                            $comment_id = $comment_temp->Comment; 
                            if(!empty($comment_id)){
                             $activity_data['Link']=$activity_data['Link'].'?cguid='.get_detail_by_id($comment_id,20);   
                            }
                        }
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'],$user_detail['UserID']);
                        $entity_data = get_detail_by_id($activity_data['ModuleEntityID'], $activity_data['ModuleID'], '*', 2);
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        $comment_data = $this->activity_model->get_last_comment($notification_data['RefrenceID'], $from_user_detail['UserID']);

                        $comment_data['MediaImage'] = '';
                        if ($comment_data['IsMediaExists'] == 1) {
                            $media_data = $this->activity_model->get_comment_media($comment_data['PostCommentID']);
                            $comment_data['MediaImage'] = $media_data['ImageName'];
                        }

                        $is_owner = 0;
                        $entity_type = 'post';
                        if ($activity_data['ActivityTypeID'] == 5 || $activity_data['ActivityTypeID'] == 6 || $activity_data['ActivityTypeID'] == 13) {
                            $entity_type = 'album';
                        }
                        if ($owner_data['UserID'] == $user_detail['UserID']) {
                            $is_owner = 1;
                        }
                        if ($activity_data['ActivityTypeID'] == 16 || $activity_data['ActivityTypeID'] == 17) {
                            $entity_type = 'review';
                        }
                        $notification_data['Summary'] = $comment_data['PostComment'];
                        $email_data['Data'] = array('is_owner' => $is_owner, 'activity_data' => $activity_data, 'entity_data' => $entity_data, 'owner_data' => $owner_data, 'comment_data' => $comment_data, 'entity_type' => $entity_type);                                        
                        $email_data['Subject'] = $from_user_detail['FullName'] . " commented on a post you are tagged in";
                        break;
                    case 'activity_reminder':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['activity_reminder'];
                        $email_data['StatusMessage'] = "";                    
                        $this->load->helper('activity');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostContent,PostTitle,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);                        
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);                        
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'],$user_detail['UserID']);
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
                        if(isset($notification_data['Parameters'])) {
                            if(isset($notification_data['Parameters']) && !is_array($notification_data['Parameters']))
                                $reminder_notification_params = json_decode($notification_data['Parameters']);
                            else
                                $reminder_notification_params = $notification_data['Parameters'];
                            $upcoming_reminder_date = $reminder_notification_params[0]['ReferenceID'];
                        }

                        //Convert Reminder DateTime in users Timezone 
                        $this->load->model('timezone/timezone_model');
                        $user_timezone = $this->timezone_model->getUserTimeZone($user_detail['UserID']);
                        $converted_datetime = $this->timezone_model->convert_date_to_time_zone($upcoming_reminder_date, 'UTC', $user_timezone, 'j F Y \a\t g:ia');
                        $email_data['Data'] = array('activity_data' => $activity_data, 'owner_data' => $owner_data, 'reminder_date'=>$converted_datetime);

                        if(isset($upcoming_reminder_date,$converted_datetime))
                            $email_data['Subject'] = "Your reminder is arriving at " . $converted_datetime;
                        else
                            $email_data['Subject'] = "Your reminder is arriving in 15 minutes.";
                        break;
                    case 'winning_contest':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['winning_contest'];
                        $email_data['StatusMessage'] = "";                    
                        $this->load->helper('activity');
                        $this->load->model('contest/contest_model');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostContent,PostTitle,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID,ParentActivityID', 2);
                        $winner_details = $this->contest_model->get_contest_winners($activity_data['ParentActivityID'],$user_detail['UserID'],array($user_detail['UserID']));                        
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);                        
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'],$user_detail['UserID']);
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'winner_details' => $winner_details);
                        $email_data['Subject'] = "Congratulations! You won the contest.";
                        break;
                    case 'contest_winner_announced':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['contest_winner_announced'];
                        $email_data['StatusMessage'] = "";                    
                        $this->load->helper('activity');
                        $this->load->model('contest/contest_model');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostContent,PostTitle,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID,ParentActivityID', 2);
                        $winner_details = $this->contest_model->get_contest_winners($activity_data['ParentActivityID'],$user_detail['UserID']);                        
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);                        
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'],$user_detail['UserID']);
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                         $email_data['Data'] = array('activity_data' => $activity_data, 'winner_details' => $winner_details);
                        $email_data['Subject'] = "The contest results have been announced!";
                        break;
                    case 'contest_end':
                        $testing = true;
                        $email_data['EmailTypeID'] = $this->email_type['contest_end'];
                        $email_data['StatusMessage'] = "";                    
                        $this->load->helper('activity');
                        $this->load->model('contest/contest_model');
                        $activity_data = get_detail_by_id($notification_data['RefrenceID'], 0, 'PostContent,PostTitle,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID,ParentActivityID', 2);
                        $participant_details = $this->contest_model->get_participant_friends($notification_data['RefrenceID'],$user_detail['UserID']);
                        $activity_data['Link'] = get_single_activity_url($notification_data['RefrenceID']);
                        $activity_data['Album'] = $this->activity_model->get_albums($notification_data['RefrenceID'],$user_detail['UserID']);
                        $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                        $email_data['Data'] = array('activity_data' => $activity_data, 'participant_details' => $participant_details);
                        $email_data['Subject'] = "Contest is going to close soon";
                        break;
                    default:
                        return FALSE;
                        break;
                }

                $this->load->model('users/user_model');
                if (isset($email_data['Data']['activity_data']['PostContent'])) {
                    $email_data['Data']['activity_data']['PostContent'] = $this->activity_model->parse_tag_html($email_data['Data']['activity_data']['PostContent']);
                }

                if (isset($email_data['Data']['comment_data']['PostComment'])) {
                    $email_data['Data']['comment_data']['PostComment'] = $this->activity_model->parse_tag_html($email_data['Data']['comment_data']['PostComment']);
                }

                if ($testing) {
                    if (isset($user_detail['UserID'])) {
                        $user_detail['ProfileURL'] = get_entity_url($user_detail['UserID'], "User", 1);
                    }
                    if (isset($from_user_detail['UserID'])) {
                        $from_user_detail['ProfileURL'] = get_entity_url($from_user_detail['UserID'], "User", 1);
                    }
                    if ($template_key == 'comment' || $template_key == 'reply_on_question' || $template_key == 'request_reply_on_question' || $template_key == 'request_a_question') {
                        if (isset($email_data['Data']['comment_data']) && $email_data['Data']['comment_data']['PostAsModuleID'] == 18) {
                            $page_id = $email_data['Data']['comment_data']['PostAsModuleEntityID'];
                            $this->db->select('P.PageGUID as UserGUID ,P.Title as FirstName ,"" as LastName,P.PageID as UserID,P.Title as FullName,if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture, PageURL as ProfileURL', false);
                            $this->db->from(PAGES . ' P');
                            $this->db->join(CATEGORYMASTER . ' CM', 'CM.CategoryID=P.CategoryID', 'left');
                            $this->db->where('P.PageID', $page_id);
                            $query_page = $this->db->get();
                            $from_user_detail = $query_page->row_array();
                            if ($email_data['Data']['is_owner']) {
                                $email_data['Subject'] = $from_user_detail['FullName'] . " commented on your " . $entity_type;
                            } else {
                                $email_data['Subject'] = $from_user_detail['FullName'] . " commented on a " . $entity_type;
                            }
                        }
                        if (isset($email_data['Data']['activity_data']) && $email_data['Data']['activity_data']['PostAsModuleID'] == 18) {
                            $activity_page_id = $email_data['Data']['activity_data']['PostAsModuleEntityID'];

                            $this->db->select('P.PageGUID ,P.Title,P.PageID,if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture, PageURL', false);
                            $this->db->from(PAGES . ' P');
                            $this->db->join(CATEGORYMASTER . ' CM', 'CM.CategoryID=P.CategoryID', 'left');
                            $this->db->where('P.PageID', $activity_page_id);
                            $query_page = $this->db->get();
                            $page_temp_detail = $query_page->row_array();
                            $email_data['Data']['owner_data']['UserID'] = $page_temp_detail['PageID'];
                            $email_data['Data']['owner_data']['UserGUID'] = $page_temp_detail['PageGUID'];
                            $email_data['Data']['owner_data']['FirstName'] = $page_temp_detail['Title'];
                            $email_data['Data']['owner_data']['LastName'] = '';
                            $email_data['Data']['owner_data']['ProfilePicture'] = $page_temp_detail['ProfilePicture'];
                        }
                    }
                    $email_data['Data']['To'] = $user_detail;
                    $email_data['Data']['From'] = $from_user_detail;

                    //get guid by entityID
                    if (isset($email_data['Data']['activity_data']['ActivityGUID']) && !$this->settings_model->isDisabled('32')) {
                        $entityGUID = $email_data['Data']['activity_data']['ActivityGUID'];
                        $email_data['EntityGUID'] = $entityGUID; //send entity id with mail in reply_to header
                    }
                    if ($notification_settings['send_email_notification']) {
                       // sendEmailAndSave($email_data, 1); // Common function to send email
                    }
                    
                    if ($notification_settings['send_mobile_notification']) {
                        /* send push notification - created by gautam - starts */
                        initiate_worker_job('SendPushMsg', array('ToUserID'=>$notification_data['ToUserID'], 'Subject'=>$email_data['Subject'], 'notifications'=>$notification_data),'','notification');
                        /* send push notification - created by gautam - ends */
                    }
                }
            }
            
            return TRUE;
        }

	function get_post_title($activity_id, $activity_data) {
            $post_title = '';
            if (!empty($activity_data)) {
                if (!empty($activity_data['PostTitle'])) {
                    $post_title = $activity_data['PostTitle'];
                } else if (!empty($activity_data['PostContent'])) {
                    $post_title = $post_title = get_activity_title('', $activity_data['PostContent']);
                }
            } else {
                $post_title = get_activity_title($activity_id, '');
            }
            return $post_title;
	}
	/*
		     * Function to get notification type key by id
		     * @param : notification_type_id(int)
		     * @output : string/boolean
	*/

	/* function get_template_key_by_id($notification_type_id) {
		$NotificationTypeKey = array();
		$template_name = '';
		$NotificationTypeKey = $this->cache->get('notification_type');
		if (!empty($NotificationTypeKey)) {
			if (isset($NotificationTypeKey['$notification_type_id'])) {
				$template_name = $NotificationTypeKey['$notification_type_id'];
			}
		}

		if (!$template_name) {
			$this->db->select('NotificationTypeKey');
			$this->db->from(NOTIFICATIONTYPES);
			$this->db->where('NotificationTypeID', $notification_type_id);
			$res = $this->db->get()->row_array();
			if (!empty($res)) {
				$template_name = $NotificationTypeKey[$notification_type_id] = $res['NotificationTypeKey'];
			}

			if (CACHE_ENABLE) {
				$this->cache->save('notification_type', $NotificationTypeKey, CACHE_EXPIRATION);
			}
		}
		return $template_name;

	}*/

	/**
	 * Function Name: get_notification_value
	 * @param NotificationParamValue, UserID
	 * Description: Get parameters value of notification
	 */
	function get_notification_value($NotificationParamValue, $user_id) {
		$value = '';
		$NotificationParam['Name'] = '';
		$ParamVal = json_decode($NotificationParamValue, true);
		$NotificationParam['Name'] = '';
		$NotificationParam['Thumbnail'] = THEAM_PATH . 'img/avatar2.png';
		$NotificationParam['ReferenceID'] = $ParamVal['ReferenceID'];
		if ($ParamVal['ReferenceID'] == $user_id && $ParamVal['Type'] == 'User') {
			$NotificationParam['Name'] = 'You';
		} else {
			//$result = $this->db->query($sql);
			if ($ParamVal['Type'] == 'User') {
				$this->db->select('FirstName, LastName, UserGuID, ProfilePicture');
				$this->db->from(USERS);
				$this->db->where('UserID', $ParamVal['ReferenceID']);
				//$sql = 'SELECT FirstName, LastName FROM '.USERS.' WHERE UserID="'.$ParamVal['ReferenceID'].'"';
			} elseif ($ParamVal['Type'] == 'Group') {
				$this->db->select('Type,GroupName');
				$this->db->from(GROUPS);
				$this->db->where('GroupID', $ParamVal['ReferenceID']);
				//$sql = 'SELECT GroupName FROM '.GROUPS.' WHERE GroupID="'.$ParamVal['ReferenceID'].'"';
			} elseif ($ParamVal['Type'] == 'Event') {
				$this->db->select('Title');
				$this->db->from(EVENTS);
				$this->db->where('EventID', $ParamVal['ReferenceID']);
			} elseif ($ParamVal['Type'] == 'Page') {
				$this->db->select('Title');
				$this->db->from(PAGES);
				$this->db->where('PageID', $ParamVal['ReferenceID']);
			} elseif ($ParamVal['Type'] == 'EntityType') {
				$arr = array('1' => 'Post', '2' => 'Album', '3' => 'Photo', '4' => 'Video', '5' => 'Comment');
				$NotificationParam['Name'] = $arr[$ParamVal['ReferenceID']];
				return $NotificationParam;
			} elseif ($ParamVal['Type'] == 'Album') {
				$this->db->select('AlbumName');
				$this->db->from(ALBUMS);
				$this->db->where('AlbumID', $ParamVal['ReferenceID']);
			} else {
				return FALSE;
			}
			$result = $this->db->get();
			if ($result->num_rows()) {
				$Data = $result->row();
				if ($ParamVal['Type'] == 'User') {
					$NotificationParam['Name'] = $Data->FirstName . ' ' . $Data->LastName;
					$NotificationParam['UserPicture'] = $Data->ProfilePicture;
				} elseif ($ParamVal['Type'] == 'Group') {
					if ($Data->Type == 'FORMAL') {
						$NotificationParam['Name'] = $Data->GroupName;
					} else {
						$this->load->model('group/group_model');
						$Notification['Name'] = $this->group_model->get_informal_group_name($ParamVal['ReferenceID'], $user_id);
					}
				} elseif ($ParamVal['Type'] == 'Event') {
					$NotificationParam['Name'] = $Data->Title;
				} elseif ($ParamVal['Type'] == 'Page') {
					$NotificationParam['Name'] = $Data->Title;
				} elseif ($ParamVal['Type'] == 'Album') {
					$NotificationParam['Name'] = $Data->AlbumName;
				}
			}
		}
		return $NotificationParam;
	}

	/**
	 * Function Name: get_notification_user_list
	 * @param ActivityID
	 * Description: Get List of User which we need to send notifications
	 */
	public function get_notification_user_list($activity_id, $user_id = 0, $All = true, $entity_type = "ACTIVITY") {
		// Define subscribe and unsubscribe array
		$Subscribe = array();
		$Unsubscribe = array();

		// Get list of all subscribed and unsubscribed users
		$this->db->where('EntityType', $entity_type);
		$this->db->where('EntityID', $activity_id);
		if ($All) {
			$this->db->where('MaxNotifications !=', '0');
		} else {
			$this->db->where('MaxNotifications', '-1');
		}
		$this->db->where('ModuleID', 3);
		$this->db->group_by('ModuleEntityID');
		$query = $this->db->get(SUBSCRIBE);
		if ($query->num_rows()) {
			foreach ($query->result() as $result) {
				if ($result->StatusID == 2) {
					$Subscribe[] = $result->ModuleEntityID;
				} else if ($result->StatusID == 3) {
					$Unsubscribe[] = $result->ModuleEntityID;
				}
			}
		}

		if ($entity_type == "ACTIVITY") {
			// Get userid of activity owner and entityid (if module is 3 => 'User')
			$query = $this->db->get_where(ACTIVITY, array('ActivityID' => $activity_id));
			if ($query->num_rows()) {
				$row = $query->row();
				if (!in_array($row->UserID, $Subscribe) && !in_array($row->UserID, $Unsubscribe)) {
					$Subscribe[] = $row->UserID;
				}
				if ($row->ModuleID == 3) {
					if (!in_array($row->ModuleEntityID, $Subscribe) && !in_array($row->ModuleEntityID, $Unsubscribe)) {
						$Subscribe[] = $row->ModuleEntityID;
					}
				}
			}
		}

		if (in_array($user_id, $Subscribe)) {
			unset($Subscribe[array_search($user_id, $Subscribe)]);
		}
		if (!$All) {
			return $Subscribe;
		}
		return $Subscribe;
	}

	/**
	 * Function Name: reset_particular_notification
	 * @param user_id
	 * Description: set / reset particular notification type
	 */
	function reset_particular_notification($user_id,$notification_type_key,$module_id)
	{
		$this->db->where('UserID', $user_id);
		$this->db->where('ModuleID',$module_id);
		$query = $this->db->get(MODULENOTIFICATION);

		if(!$query->num_rows())
		{
			$data = array('UserID' => $user_id, 'ModuleID' => $module_id, 'IsEnabled' => '1', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
			$this->db->insert(MODULENOTIFICATION,$data);
		}

		$this->db->where('UserID', $user_id);
		$this->db->where('NotificationTypeKey',$notification_type_key);
		$this->db->delete(USERNOTIFICATIONSETTINGS);

		$notification_data = array('UserID' => $user_id, 'NotificationTypeKey' => $notification_type_key, 'Email' => '1', 'Mobile' => '1', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
		$this->db->insert(USERNOTIFICATIONSETTINGS,$notification_data);
	}

	/**
	 * Function Name: set_all_notification_on
	 * @param user_id
	 * Description: Set all notifications turned on its for first time user
	 */
	function set_all_notification_on($user_id) {
		$this->db->set('Notification', '1');
		$this->db->set('EmailNotification', '1');
		$this->db->set('MobileNotification', '1');
		$this->db->where('UserID', $user_id);
		$this->db->update(USERS);

		$this->db->where('UserID', $user_id);
		$this->db->delete(MODULENOTIFICATION);

		$this->db->where('UserID', $user_id);
		$this->db->delete(USERNOTIFICATIONSETTINGS);

		$module_query = array();
		foreach ($this->email_notification_modules as $module) {
			$module_query[] = array('UserID' => $user_id, 'ModuleID' => $module, 'IsEnabled' => '1', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
		}
		$this->db->insert_batch(MODULENOTIFICATION, $module_query);

		$query = $this->db->get_where(NOTIFICATIONTYPES, array('IsCustomizable' => '1'));
		if ($query->num_rows()) {
			$notification_settings_query = array();
			foreach ($query->result_array() as $notification) {
				$notification_settings_query[] = array('UserID' => $user_id, 'NotificationTypeKey' => $notification['NotificationTypeKey'], 'Email' => '1', 'Mobile' => '1', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
			}
			$this->db->insert_batch(USERNOTIFICATIONSETTINGS, $notification_settings_query);
		}
		if (CACHE_ENABLE) {
			$notification_details = $this->get_user_notification_settings($user_id, true);
			$this->cache->save('user_notification_setting' . $user_id, $notification_details, CACHE_EXPIRATION);
		}
	}

	/**
	 * Function Name: get_user_notification_settings
	 * @param user_id
	 * @param no_cache
	 * Description: get notification settings of particular user
	 */
	function get_user_notification_settings($user_id, $no_cache = false) {

            $order = array('2' => '1', '1' => '2', '18' => '3', '14' => '4', '10' => '5', '25' => '6', '23' => '7', '26' => '8', '29' => '9', '33' => '10', '34' => '11', '19' => '12','36' => '13');
            $cache_support = false;
            if (CACHE_ENABLE && !$no_cache) {
                $cache_support = true;
                $data = $this->cache->get('user_notification_setting' . $user_id);
                if ($data) {
                    return $data;
                }
            }

            $data = array('AllNotifications' => 0, 'EmailNotifications' => 0, 'MobileNotifications' => 0, 'Modules' => array(), 'Notifications' => array());

            $this->db->select('Notification,EmailNotification,MobileNotification');
            $this->db->from(USERS);
            $this->db->where('UserID', $user_id);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $row = $query->row();

                $data['AllNotifications'] = ($row->Notification) ? true : false;
                $data['EmailNotifications'] = ($row->EmailNotification) ? true : false;
                $data['MobileNotifications'] = ($row->MobileNotification) ? true : false;

                $this->db->select('MN.ModuleID,MN.IsEnabled,M.ModuleName, M.IsActive');
                $this->db->from(MODULENOTIFICATION . ' MN');
                $this->db->join(MODULES . ' M', "M.ModuleID=MN.ModuleID AND M.IsActive='1'");
                $this->db->where('MN.UserID', $user_id);
                $module_query = $this->db->get();
                if ($module_query->num_rows()) {
                    foreach ($module_query->result_array() as $module_data) {
                        $module_data['IsEnabled'] = ($module_data['IsEnabled']) ? true : false;

                        $data['Modules'][] = array(
                            'ModuleID' => $module_data['ModuleID'], 
                            'ModuleName' => $module_data['ModuleName'], 
                            'Value' => $module_data['IsEnabled'], 
                            'IsActive' => $module_data['IsActive'], 
                            'DisplayOrder' => $order[$module_data['ModuleID']]
                        );
                    }
                }

                $this->db->select('NT.NotificationTypeKey,NT.NotificationTypeName,NT.ModuleID,UNS.Email,UNS.Mobile,NT.DisplayOrder');

                $this->db->from(NOTIFICATIONTYPES . ' NT');
                $this->db->join(USERNOTIFICATIONSETTINGS . ' UNS', 'UNS.NotificationTypeKey=NT.NotificationTypeKey', 'left');
                $this->db->join(MODULES . ' M', 'M.ModuleID = NT.ModuleID AND M.IsActive = "1"', 'left');
                $this->db->where('M.ModuleID IS NOT NULL', null, FALSE);
                $this->db->where('UNS.UserID', $user_id);
                $this->db->where('NT.IsCustomizable', '1');

                $this->db->order_by('NT.DisplayOrder', 'DESC');

                $notification_query = $this->db->get();     //echo $this->db->last_query(); die;
                if ($notification_query->num_rows()) {
                    foreach ($notification_query->result_array() as $notification) {
                        $notification['Email'] = ($notification['Email']) ? true : false;
                        $notification['Mobile'] = ($notification['Mobile']) ? true : false;
                        $data['Notifications'][] = $notification;
                    }
                }
            }
            if ($cache_support) {
                $this->cache->save('user_notification_setting' . $user_id, $data, CACHE_EXPIRATION);
            }
            return $data;
	}
        
        
	/**
	 * Function Name: set_user_notification_settings
	 * @param user_id
	 * @param all_notifications
	 * @param email_notifications
	 * @param mobile_notifications
	 * @param modules
	 * @param notifications
	 * Description: set notifications of particular user
	 */
	function set_user_notification_settings($user_id, $all_notifications, $email_notifications, $mobile_notifications, $modules = array(), $notifications = array()) {
		if (CACHE_ENABLE) {                    
                    $this->cache->delete('user_notification_setting' . $user_id);
		}
            
                $this->db->set('Notification', $all_notifications);
		if ($all_notifications) {
			$this->db->set('EmailNotification', $email_notifications);
			$this->db->set('MobileNotification', $mobile_notifications);
		}
		$this->db->where('UserID', $user_id);
		$this->db->update(USERS);

		if ($all_notifications) {
                    $module_key_val = array();
                    if ($modules) {
                        $update_module = array();
                        foreach ($modules as $module) {
                            $update_module[] = array('UserID' => $user_id, 'ModuleID' => $module['ModuleID'], 'IsEnabled' => $module['Value'], 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                            $module_key_val[$module['ModuleID']] = $module['Value'];
                        }
                        if ($update_module) {
                            $this->db->where('UserID', $user_id);
                            $this->db->update_batch(MODULENOTIFICATION, $update_module, 'ModuleID');
                        }
                    }

                    if ($notifications && ($mobile_notifications == '1' || $email_notifications == '1')) {
                        $update_notification = array();
                        foreach ($notifications as $notification) {
                            if (isset($module_key_val[$notification['ModuleID']]) && $module_key_val[$notification['ModuleID']] == 1) {
                                $array = array('NotificationTypeKey' => $notification['NotificationTypeKey'], 'UserID' => $user_id, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                                if ($mobile_notifications) {
                                        $array['Mobile'] = $notification['Mobile'];
                                }
                                if ($email_notifications) {
                                        $array['Email'] = $notification['Email'];
                                }
                                $update_notification[] = $array;
                            }
                        }
                        if ($update_notification) {
                                $this->db->where('UserID', $user_id);
                                $this->db->update_batch(USERNOTIFICATIONSETTINGS, $update_notification, 'NotificationTypeKey');
                        }
                    }
		}
		if (CACHE_ENABLE) {
                    $notification_details = $this->get_user_notification_settings($user_id, true);
                    //$this->cache->save('user_notification_setting' . $user_id, $notification_details, CACHE_EXPIRATION);
		}
	}
        
        /**
	 * Function Name: set_user_notification_settings
	 * @param user_id
	 * @param all_notifications
	 * @param email_notifications
	 * @param mobile_notifications
	 * @param modules
	 * @param notifications
	 * Description: set notifications of particular user
	 */
	function set_push_notification_settings($user_id, $push_notifications) {		
            $this->db->set('MobileNotification', $push_notifications);
            $this->db->where('UserID', $user_id);
            $this->db->update(USERS);
            if (CACHE_ENABLE) {
                $this->cache->delete('user_notification_setting' . $user_id);
                $this->cache->delete('user_profile_' . $user_id);
            }
	}

        public function check_user_notification_settings($user_id, $notification_type_key, $notification_module_id) {
            $notification_details = $this->get_user_notification_settings($user_id);    
            $notification_settings = array(
                    'send_notification' => FALSE, 
                    'send_email_notification' => FALSE, 
                    'send_mobile_notification' => FALSE
                );
            if($notification_details['AllNotifications'] == 1 && ($notification_details['EmailNotifications'] == 1 || $notification_details['MobileNotifications'] == 1)) {
                $notification_settings['send_notification'] = TRUE;
            
                $notification_modules = $notification_details['Modules'];
                $notifications = $notification_details['Notifications'];
                foreach($notification_modules as $n_module) {
                    if($n_module['ModuleID'] == $notification_module_id && $n_module['Value']) {
                        foreach($notifications as $notification) {
                            if($notification['NotificationTypeKey'] == $notification_type_key) {
                                $notification_settings['send_email_notification'] = $notification['Email'];
                                $notification_settings['send_mobile_notification'] = $notification['Mobile'];
                                break;
                            }
                        }
                        break;
                    }
                }
                
                if(!($notification_settings['send_email_notification'] || $notification_settings['send_mobile_notification'])) {
                    $notification_settings['send_notification'] = FALSE;
                }
            }
            return $notification_settings;
        }
	/* public function check_email_notification($user_id, $NotificationTypeID) {
		$module_id = 0;
		$notification_type_key = '';
		$this->db->select('ModuleID,NotificationTypeKey');
		$this->db->from(NOTIFICATIONTYPES);
		$this->db->where('NotificationTypeID', $NotificationTypeID);
		$module_query = $this->db->get();
		if ($module_query->num_rows()) {
			$module_row = $module_query->row();
			$module_id = $module_row->ModuleID;
			$notification_type_key = $module_row->NotificationTypeKey;
		}

		$this->db->select('Notification,EmailNotification');
		$this->db->from(USERS);
		$this->db->where('UserID', $user_id);
		$query = $this->db->get();
		if ($query->num_rows()) {
			$row = $query->row();
			if ($row->Notification == 1 && $row->EmailNotification == 1) {
				$this->db->select('IsEnabled');
				$this->db->from(MODULENOTIFICATION);
				$this->db->where('UserID', $user_id);
				$this->db->where('ModuleID', $module_id);
				$module_notification_query = $this->db->get();
				if ($module_notification_query->num_rows()) {
					$module_notification_row = $module_notification_query->row();
					if ($module_notification_row->IsEnabled == 1) {
						$this->db->select('Email');
						$this->db->from(USERNOTIFICATIONSETTINGS);
						$this->db->where('NotificationTypeKey', $notification_type_key);
						$this->db->where('UserID', $user_id);
						$notification_query = $this->db->get();
						if ($notification_query->num_rows()) {
							if ($notification_query->row()->Email == 1) {
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	} */

	/**
	 * [mark_notifications_as_read Used to update status as read of all notification for particular entity for logged in user]
	 * @param  [int] $user_id     [logged in user id]
	 * @param  [int] $entity_id   [Entity ID]
	 * @param  [string] $entity_type [Entity Type]
	 */
	function mark_notifications_as_read($user_id, $entity_id, $entity_type) {
		$notification_type = array();
		$notification_type1 = array();
		switch ($entity_type) {
		case 'USER':
			$notification_type = array(5, 16, 17); //follower, friend_request_received, friend_request_accepted
			//$notification_type1 = array(17);    //friend_request_accepted
			break;
		case 'POST':
			$notification_type = array(19, 18, 2, 21, 55, 3, 20, 54, 47, 48, 84, 85); //wall_post, tagged, comment, tagged_in_comment,  comment_on_tagged_post, like, like_comment, like_tagged_post, share_post, share_your_post
			break;
		case 'GROUP':
			$notification_type = array(22, 23, 25, 62, 43, 4); //group_request_received, group_request_accepted, group_join, group_block, make_group_admin, group_left, group_member_added
			break;
		case 'GROUP_MEMBER':
			$notification_type = array(22, 23, 24, 25, 62, 43, 4, 83); //group_request_received, group_request_accepted, group_join, group_block, make_group_admin, group_left, group_member_added
			break;
		case 'GROUP_POST':
			$notification_type = array(1, 18, 2, 21, 55, 3, 20, 54, 47, 48, 84, 85); //group_post, tagged, comment, tagged_in_comment,  comment_on_tagged_post, like, like_comment, like_tagged_post, share_post, share_your_post
			break;
		case 'EVENT':
			$notification_type = array(26, 27, 28, 29, 30, 31, 32, 33, 56, 58, 67); //event_edited_host, event_edited_admin, event_edited_attending, event_edited_may_attending, event_invitation, event_request_accept_host, event_request_accept_admin, event_cancel, event_cancel_to_admin, make_event_admin
			break;
		case 'EVENT_POST':
			$notification_type = array(61, 18, 2, 21, 55, 3, 20, 54, 47, 48, 84, 85); //event_post_added, tagged, comment, tagged_in_comment,  comment_on_tagged_post, like, like_comment, like_tagged_post, share_post, share_your_post
			break;
		case 'PAGE':
			$notification_type = array(45, 68, 69, 70); //page_follow, make_page_admin, page_block, page_tagged
			break;
		case 'PAGE_POST':
			$notification_type = array(46, 18, 2, 21, 55, 3, 20, 54, 47, 48, 84, 85); //page_post, tagged, comment, tagged_in_comment,  comment_on_tagged_post, like, like_comment, like_tagged_post, share_post, share_your_post
			break;
		case 'RATING':
			$notification_type = array(52, 53, 60, 63); //rating_added, rating_edited, review_marked_helpful, rating_comments
			break;
		case 'MEDIA':
			$notification_type = array(51, 21, 55, 49, 50); //media_comment, tagged_in_comment,  comment_on_tagged_post, like_media, like_media_comment
			break;
		case 'ALBUM':
			$notification_type = array(2, 3, 21); //comment, like, tagged_in_comment
			break;
		default:
			# code...
			break;
		}

		if ($notification_type) {
			$this->db->set('StatusID', '17');
			$this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));

			$this->db->where('RefrenceID', $entity_id);
			$this->db->where('ToUserID', $user_id);
			$this->db->where_in('NotificationTypeID', $notification_type);
			$this->db->where_in('StatusID', array(15, 16));
			$this->db->update(NOTIFICATIONS);
		}
	}

	/**
	 * [mark_notifications_as_read update status as read of all notification for logged in user]
	 * @param  [int] $user_id     [logged in user id]
	 */
	function mark_all_notifications_as_read($user_id) {
            $this->db->set('StatusID', '17');
            $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->where('ToUserID', $user_id);
            //$this->db->where('LocalityID', $this->LocalityID);
            $this->db->where_in('StatusID', array(15, 16));
            $this->db->update(NOTIFICATIONS);
	}

	public function get_params($notification_id) {
		$params = array();
		$this->db->select('NotificationParamName,NotificationParamValue');
		$this->db->from(NOTIFICATIONPARAMS);
		$this->db->where('NotificationID', $notification_id);
		$notification_params = $this->db->get();
		if ($notification_params->num_rows()) {
			$params = $notification_params->result_array();
		}
		return $params;
	}

	public function get_single_notifications($user_id, $NotificationGUID) {
		$entity_type_arr = array('1' => 'Post', '2' => 'Album', '3' => 'Photo', '4' => 'Video', '5' => 'Comment');
		$this->load->model(array('group/group_model', 'activity/activity_model', 'timezone/timezone_model', 'users/friend_model', 'category/category_model'));
		$group_list = $this->group_model->get_joined_groups($user_id);
		$condition1 = "(
                    IF(N.NotificationTypeID=1 OR N.NotificationTypeID=2 OR N.NotificationTypeID=3 OR N.NotificationTypeID=18 OR N.NotificationTypeID=19 OR N.NotificationTypeID=20 OR N.NotificationTypeID=21, (
                        (SELECT A.StatusID FROM " . ACTIVITY . " A WHERE A.ActivityID = N.RefrenceID) = 2
                    ), N.StatusID )
                )";
		if (!empty($group_list)) {

			$condition2 = "(
                IF(N.NotificationTypeID=23 OR N.NotificationTypeID=24 OR N.NotificationTypeID=25 OR N.NotificationTypeID=4 OR N.NotificationTypeID=43 OR N.NotificationTypeID=44,
            N.RefrenceID IN(" . $group_list . ")
            ,true)
            )";
			$this->db->where($condition2, NULL, FALSE);
		}

		//$Co
		$this->db->select('U.UserID as UserID,U.UserGUID, GROUP_CONCAT(DISTINCT(N.NotificationID) ORDER BY N.NotificationID DESC) as Notifications, NT.NotificationTypeKey, N.UserID as fromUserID, N.NotificationGUID, MAX(N.CreatedDate) AS CreatedDate, MIN(N.StatusID) AS StatusID, N.NotificationTypeID, N.RefrenceID, NT.NotificationTypeName, NT.NotificationText, U.ProfilePicture, N.Params as CustomParams', FALSE);
		$this->db->from(NOTIFICATIONS . ' N');
		$this->db->join(NOTIFICATIONTYPES . ' NT', 'N.NotificationTypeID=NT.NotificationTypeID', 'left');
		$this->db->join(USERS . ' U', 'N.UserID=U.UserID', 'left', FALSE);
		$this->db->where($condition1, NULL, FALSE);
		$this->db->where('N.NotificationGUID', $NotificationGUID);
		$this->db->where('N.ToUserID', $user_id);
		$this->db->order_by('CreatedDate', 'desc');

		$Notification = $this->db->get();
		//echo $this->db->last_query();
		$data = array();

		if ($Notification->num_rows()) {
			foreach ($Notification->result_array() as $result) {
				$arr = array();
				$arr['NotificationGUID'] = $result['NotificationGUID'];
				$arr['NotificationTypeID'] = $result['NotificationTypeID'];
				$arr['NotificationText'] = $result['NotificationText'];
				$arr['CreatedDate'] = $result['CreatedDate'];
				$arr['StatusID'] = $result['StatusID'];
				$arr['ShowAcceptDeny'] = 0;
				$arr['Class'] = $this->get_notification_icon($result['NotificationTypeID']);
				$arr['IsLink'] = 1;
				$arr['Link'] = site_url();
				$arr['Members'] = array();
				$arr['ProfilePicture'] = $result['ProfilePicture'];
				$summary_data = $this->get_activity_summary($result['NotificationTypeID'], $result['RefrenceID'], $result['CustomParams'], $user_id);
				$arr['Summary'] = $summary_data['Summary'];

				if ($arr['NotificationTypeID'] == "20") {
					$post_type = $this->get_post_type($result['RefrenceID']);
					if ($post_type == '2') {
						$arr['NotificationText'] = str_replace('liked', 'upvoted', $arr['NotificationText']);
						$arr['NotificationText'] = str_replace('comment', 'answer', $arr['NotificationText']);
					}
				}

				//$album_details = $this->activity_model->getSingleUserActivity(get_detail_by_id($result['RefrenceID'], 0, "UserID"), $result['RefrenceID']);
				$album_details = $summary_data['Album'];
				//var_dump($album_details); die;
				if (isset($album_details[0]['Album'])) {
					$arr['Album'] = $album_details[0]['Album'];
				}
				$arr['UserGUID'] = $result['UserGUID'];
				if ($arr['Summary']) {
					//$arr['Album'] = array();
				}
				if ($result['fromUserID'] == $user_id) {
					$arr['UserName'] = '';
					$arr['ProfilePicture'] = ASSET_BASE_URL . 'img/logo.svg';
				}
				$arr['ProfileName'] = '';
				$PageID = 0;
				$entity_type = '';

				if ($result['NotificationTypeID'] == '16') {
					$friend_status = $this->friend_model->checkFriendStatus($result['fromUserID'], $user_id);
					if ($friend_status == 2) {
						$arr['ShowAcceptDeny'] = 1;
					}
				}

				$notifications = explode(',', $result['Notifications']);
				if ($notifications) {
					foreach ($notifications as $notification) {
						$params = $this->get_params($notification);
						if ($params) {
							foreach ($params as $param) {
								$key = 'P' . $param['NotificationParamName'];
								$param_details = json_decode($param['NotificationParamValue'], true);
								$module_id = 0;
								if ($param_details['Type'] == 'Page') {
									$module_id = 18;
								}
								if ($param_details['Type'] == 'Group') {
									$module_id = 1;
									if ($this->group_model->is_informal($param_details['ReferenceID'])) {
										$arr['Members'] = $this->group_model->members($param_details['ReferenceID'], $user_id);
									}
								}
								if ($param_details['Type'] == 'Event') {
									$module_id = 14;
								}
								if ($param_details['Type'] == 'User') {
									$module_id = 3;
								}
								if ($param_details['Type'] == 'Skills') {
									$module_id = 29;
								}
								if ($param_details['Type'] == 'Forum') {
									$module_id = 33;
								}
								if ($param_details['Type'] == 'ForumCategory') {
									$module_id = 34;
								}
								if ($param_details['Type'] == 'EntityType') {
									$e_details = array('FirstName' => $entity_type_arr[$param_details['ReferenceID']], 'LastName' => '', 'ProfilePicture' => '', 'ProfileURL' => '', 'ModuleID' => 0, 'ModuleEntityGUID' => '');
									if (isset($arr[$key]) && !empty($arr[$key])) {
										if (!in_array($e_details, $arr[$key])) {
											$arr[$key][] = $e_details;
										}
									} else {
										$arr[$key][] = $e_details;
									}
								} else if ($param_details['Type'] == 'Question') {
									$arr[$key][] = array('FirstName' => get_activity_title($param_details['ReferenceID'], ''), 'LastName' => '', 'ProfilePicture' => '', 'ProfileURL' => '', 'ModuleID' => 0, 'ModuleEntityGUID' => 0);
									if ($param['NotificationParamName'] == 3) {
										echo 'aaaa';die;
										$arr[$key][] = 'Hello';
									}
								} else if ($param_details['Type'] == 'RequestNote') {
									$arr[$key][] = $arr[$key][] = array('FirstName' => get_request_note($param_details['ReferenceID'], $user_id), 'LastName' => '', 'ProfilePicture' => '', 'ProfileURL' => '', 'ModuleID' => 0, 'ModuleEntityGUID' => 0);
								} 
								elseif($param_details['Type'] == 'time')
                                {
                                    $arr[$key][] = array('time'=>$param_details['ReferenceID']);
                                }
								else {
									$ignore_logged_in_user = FALSE;
									if ($result['NotificationTypeID'] == 86) {
										$ignore_logged_in_user = TRUE;
									}
									$entity_details = $this->user_model->getUserName($user_id, $module_id, $param_details['ReferenceID'], $ignore_logged_in_user);
									if (isset($arr[$key]) && !empty($arr[$key])) {
										if (!in_array($entity_details, $arr[$key])) {
											$arr[$key][] = $entity_details;
										}
									} else {
										$arr[$key][] = $entity_details;
									}
								}
								if (in_array($result['NotificationTypeKey'], array('make_group_admin', 'make_event_admin', 'make_page_admin'))) {
									$entity_type = $param_details['Type'];
									$PageID = $param_details['ReferenceID'];
								}

								// Start
								if ($key == 'P2' && $result['NotificationTypeID'] == 46) {
									$PageID = $param_details['ReferenceID'];
								}
								if ($key == 'P2' && $result['NotificationTypeID'] == 48) {
									$PageID = $param_details['ReferenceID'];
								}
								if ($key == 'P2' && $result['NotificationTypeID'] == 61) {
									$PageID = $param_details['ReferenceID'];
								}
								if ($key == 'P2' && ($result['NotificationTypeID'] == 52 || $result['NotificationTypeID'] == 53)) {
									$PageID = $param_details['ReferenceID'];
								}
								if ($result['NotificationTypeKey'] == 'review_marked_helpful' || $result['NotificationTypeID'] == 63 || $result['NotificationTypeID'] == 64) {
									$PageID = $this->db->select('ModuleEntityID')->from(RATINGS)->where('RatingID', $result['RefrenceID'])->get()->row()->ModuleEntityID;
								}
								// Ends
							}
						}
					}
				}

				if ($result['NotificationTypeID'] == 2) {
					$activity_data = get_detail_by_id($result['RefrenceID'], 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);

					$original_notification_text = $arr['NotificationText'];

					if ($activity_data['ModuleID'] == 18) {
						$owner_data = $this->user_model->getUserName($activity_data['UserID'], 18, $activity_data['ModuleEntityID']);
						$owner_data['UserID'] = $activity_data['UserID'];
					} else {
						$owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
					}

					if ($owner_data['UserID'] != $user_id && ($activity_data['ModuleEntityID'] != $user_id && $activity_data['ModuleID'] == '3')) {
						if ($owner_data['UserID'] == $result['fromUserID'] && isset($arr['P1']) && count($arr['P1']) == '1') {
							$arr['NotificationText'] = str_replace('commented on your', 'commented on their', $arr['NotificationText']);
						} else {
							$arr['NotificationText'] = str_replace('commented on your', 'commented on ' . $owner_data['FirstName'] . ' ' . $owner_data['LastName'] . '\'s', $arr['NotificationText']);
						}
					} elseif ($activity_data['ModuleID'] != '3' && $owner_data['UserID'] != $user_id) {
						$arr['NotificationText'] = str_replace('commented on your', 'commented on ' . $owner_data['FirstName'] . ' ' . $owner_data['LastName'] . '\'s', $arr['NotificationText']);
					}

					if ($activity_data['ModuleID'] == '18') {
						if (isset($arr['P1']) && count($arr['P1']) == '1' && $arr['P1'][0]['ModuleID'] == '18') {
							$page_module_id = get_detail_by_guid($arr['P1'][0]['ModuleEntityGUID'], 18, 'PageID', 1);
							if ($activity_data['ModuleEntityID'] == $page_module_id) {
								$arr['NotificationText'] = str_replace('commented on your', 'commented on their', $original_notification_text);
							}
						}
					}
				}

				if ($result['NotificationTypeID'] == 4 || $result['NotificationTypeID'] == 89 || $result['NotificationTypeID'] == 97 || $result['NotificationTypeID'] == 100 || $result['NotificationTypeID'] == 103 || $result['NotificationTypeID'] == 104 || $result['NotificationTypeID'] == 105 || $result['NotificationTypeID'] == 106 || $result['NotificationTypeID'] == 107 || $result['NotificationTypeID'] == 108 || $result['NotificationTypeID'] == 109 || $result['NotificationTypeID'] == 110 || $result['NotificationTypeID'] == 111 || $result['NotificationTypeID'] == 112) {
					$arr['ProfilePicture'] = array();
					$arr['Album'] = array();
				}

				if ($result['NotificationTypeID'] == 3) {
					$activity_user_details = get_detail_by_id($result['RefrenceID'], 0, 'ModuleID,ModuleEntityID,UserID', 2);
					if ($activity_user_details['UserID'] != $user_id) {
						$this->load->model('users/user_model');
						$entity_details = $this->user_model->getUserName($user_id, $activity_user_details['ModuleID'], $activity_user_details['ModuleEntityID']);
						if ($result['fromUserID'] == $activity_user_details['UserID']) {
							$arr['NotificationText'] = str_replace('your', 'their', $arr['NotificationText']);
						} else {
							$arr['NotificationText'] = str_replace('your', $entity_details['FirstName'] . ' ' . $entity_details['LastName'] . "'s", $arr['NotificationText']);
						}
					}
				}

				if($result['NotificationTypeID'] == 131)
                {                  
                    $arr['NotificationText'] = str_replace('#p1#', $arr[$key][0]['time'], $arr['NotificationText']);
                    unset($arr['P1']);
                    $arr['ProfilePicture'] = $result['ProfilePicture'];
                }

				if ($result['NotificationTypeID'] == 51) {
					$activity_data = get_detail_by_id($result['RefrenceID'], 21, '*', 2);
					$owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);

					if ($owner_data['UserID'] != $user_id) {
						if ($owner_data['UserID'] == $result['fromUserID'] && isset($arr['P1']) && count($arr['P1']) == '1') {
							$arr['NotificationText'] = str_replace('commented on your', 'commented on their', $arr['NotificationText']);
						} else {
							$arr['NotificationText'] = str_replace('commented on your', 'commented on ' . $owner_data['FirstName'] . ' ' . $owner_data['LastName'] . '\'s', $arr['NotificationText']);
						}
					}
				}

				if ($result['NotificationTypeID'] == 85) {
					$activity_data = get_detail_by_id($result['RefrenceID'], 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);

					if ($activity_data['ModuleID'] == 3 && $activity_data['ModuleEntityID'] == $user_id) {
						$arr['NotificationText'] = str_replace("#p2#'s", 'your', $arr['NotificationText']);
					}
				}

/*added by gautam*/
				if ($this->IsApp == 1) {
					$arr['Link'] = $this->get_notification_link_phone($result['NotificationTypeID'], $result['RefrenceID'], $result['fromUserID'], $user_id, $PageID, $result['NotificationTypeKey'], $entity_type, $result['CustomParams']);
				} else {
					$arr['Link'] = $this->get_notification_link($result['NotificationTypeID'], $result['RefrenceID'], $result['fromUserID'], $user_id, $PageID, $result['NotificationTypeKey'], $entity_type, $result['CustomParams']);

					if ($result['NotificationTypeID'] == '49' || $result['NotificationTypeID'] == '50' || $result['NotificationTypeID'] == '51') {
						$arr['Link'] = get_detail_by_id($result['RefrenceID'], 21);
						$arr['IsLink'] = 0;
					} else {
						$arr['Link'] = explode(site_url(), $arr['Link']);
						$arr['Link'] = $arr['Link'][1];
					}

				}

				if (isset($arr['P1'][0])) {
					$arr['ProfilePicture'] = $arr['P1'][0]['ProfilePicture'];
					$arr['ProfileName'] = $arr['P1'][0]['FirstName'] . ' ' . $arr['P1'][0]['LastName'];
				}
				if ($arr['ProfilePicture'] == '' && $arr['P1'][0]['ModuleID'] == 18) {
					$entity = get_detail_by_guid($arr['P1'][0]['ModuleEntityGUID'], 18, "CategoryID", 2);
					if ($entity) {
						$category_name = $this->category_model->get_category_by_id($entity['CategoryID']);
						$category_icon = $category_name['Icon'];
						$arr['ProfilePicture'] = "icon_" . $category_icon;
					}
				}

				return $arr;
			}
		}
	}

	public function get_post_type($activity_id) {
		$post_type = 1;
		$this->db->select('PostType');
		$this->db->from(ACTIVITY);
		$this->db->where('ActivityID', $activity_id);
		$query = $this->db->get();
		if ($query->num_rows()) {
			$post_type = $query->row()->PostType;
		}
		return $post_type;
	}

	public function get_new_notifications($user_id, $page_no = PAGE_NO, $page_size = PAGE_SIZE, $CountFlag = false, $All = false, $TotalRecords = 0, $status_filter = "", $list = false) {
		$entity_type = array('1' => 'Post', '2' => 'Album', '3' => 'Photo', '4' => 'Video', '5' => 'Comment');
		$this->load->model(array('group/group_model', 'activity/activity_model', 'timezone/timezone_model', 'users/friend_model', 'category/category_model'));
		$group_list = 0;//$this->group_model->get_joined_groups($user_id);
		if(empty($group_list))
		{
			$group_list = 0;
		}
		$condition1 = "(
                    IF(N.NotificationTypeID IN(1,2,3,18,19,20,21), (
                        (SELECT A.StatusID FROM " . ACTIVITY . " A WHERE A.ActivityID = N.RefrenceID) = 2
                    ), N.StatusID )
                )";
		$condition2 = "(
                IF(N.NotificationTypeID IN(23,24,25,4,43,44),
            N.RefrenceID IN(" . $group_list . ")
            ,true)
            )";
		//$Co

		$this->db->select('U.UserID as UserID,U.UserGUID,  NT.NotificationTypeKey, N.UserID as fromUserID, N.NotificationGUID,  N.NotificationTypeID, N.RefrenceID, NT.NotificationTypeName, NT.NotificationText, U.ProfilePicture', FALSE);
		if ($list) {
                    $this->db->select("N.NotificationID as Notifications, N.CreatedDate, N.StatusID, N.Params as CustomParams");
		} else {
                    $this->db->select("GROUP_CONCAT(DISTINCT(N.NotificationID) ORDER BY N.NotificationID DESC) as Notifications,MAX(N.CreatedDate) AS CreatedDate, MIN(N.StatusID) AS StatusID,MAX(N.Params) as CustomParams", FALSE);
		}
		$this->db->from(NOTIFICATIONS . ' N');
		$this->db->join(NOTIFICATIONTYPES . ' NT', 'N.NotificationTypeID=NT.NotificationTypeID', 'left');
		$this->db->join(USERS . ' U', 'N.UserID=U.UserID', 'left', FALSE);
                $this->db->join(MODULES . ' M', "NT.ModuleID=M.ModuleID AND M.IsActive = '1'", 'inner', FALSE);

		$this->db->where($condition1, NULL, FALSE);
		if ($group_list) {
			$this->db->where($condition2, NULL, FALSE);
		}
		$this->db->where('N.ToUserID', $user_id);
                //$this->db->where('N.LocalityID', $this->LocalityID);
		$this->db->order_by('CreatedDate', 'desc');
		if (!$list) {
                    $this->db->group_by('N.RefrenceID');
                    $this->db->group_by('N.NotificationTypeID');
                    $this->db->_protect_identifiers = FALSE;
                    $this->db->group_by("IF(NT.NotificationTypeKey='like_comment',N.Params,'')");
                    $this->db->_protect_identifiers = TRUE;
		}
		if ($CountFlag && $TotalRecords == '0') {
                    $this->db->where_in('N.StatusID', array(15, 16));
		} else {
                    if ($status_filter == "unread") {
                        $this->db->where_in('N.StatusID', array(15, 16));
                    }
                    if (!$All) {
                        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
                    }
		}

		$Notification = $this->db->get();
		if ($CountFlag) {
                    return $Notification->num_rows();
		}
		//echo $this->db->last_query();die;
		$data = array();

		if ($Notification->num_rows()) {
			foreach ($Notification->result_array() as $result) {
				$arr = array();
				$arr['NotificationGUID'] = $result['NotificationGUID'];
				$arr['NotificationTypeID'] = $result['NotificationTypeID'];
				$arr['NotificationText'] = $result['NotificationText'];
				$arr['CreatedDate'] = $result['CreatedDate'];
				$arr['StatusID'] = $result['StatusID'];
				$arr['ShowAcceptDeny'] = 0;
				$arr['Class'] = $this->get_notification_icon($result['NotificationTypeID']);
				$arr['IsLink'] = 1;
				$arr['Link'] = site_url();
				$arr['ProfilePicture'] = $result['ProfilePicture'];
				$arr['Members'] = array();
				$summary_data = $this->get_activity_summary($result['NotificationTypeID'], $result['RefrenceID'], $result['CustomParams'], $user_id);
				$arr['Summary'] = $summary_data['Summary'];

				if ($arr['NotificationTypeID'] == "20") {
					$post_type = $this->get_post_type($result['RefrenceID']);
					if ($post_type == '2') {
						$arr['NotificationText'] = str_replace('liked', 'upvoted', $arr['NotificationText']);
						$arr['NotificationText'] = str_replace('comment', 'answer', $arr['NotificationText']);
					}
				}

				//$album_details = $this->activity_model->getSingleUserActivity(get_detail_by_id($result['RefrenceID'], 0, "UserID"), $result['RefrenceID']);
				//var_dump($album_details); die;
				$arr['Album'] = array();
				$album_details = $summary_data['Album'];
				if (isset($album_details[0]['Album'])) {
					$arr['Album'] = $album_details[0]['Album'];
				}

				$arr['UserGUID'] = $result['UserGUID'];
				if ($arr['Summary']) {
					//$arr['Album'] = array();
				}
				if ($result['fromUserID'] == $user_id) {
					$arr['UserName'] = '';
					$arr['ProfilePicture'] = ASSET_BASE_URL . 'img/logo.svg';
				}
				$PageID = 0;
				$entity_type = '';

				if ($result['NotificationTypeID'] == '16') {
					$friend_status = $this->friend_model->checkFriendStatus($user_id, $result['fromUserID']);
					if ($friend_status == 3) {
						$arr['ShowAcceptDeny'] = 1;
					}
				}

				$notifications = explode(',', $result['Notifications']);
				if ($notifications) {
					foreach ($notifications as $notification) {
						$params = $this->get_params($notification);
						if ($params) {
							foreach ($params as $param) {
								$key = 'P' . $param['NotificationParamName'];
								$param_details = json_decode($param['NotificationParamValue'], true);
								$module_id = 0;
								if ($param_details['Type'] == 'Page') {
									$module_id = 18;
								}
								if ($param_details['Type'] == 'InformalGroup') {
									$module_id = 1;
									$arr['Members'] = $this->group_model->members($param_details['ReferenceID'], $user_id);
								}
								if ($param_details['Type'] == 'Group') {
									$module_id = 1;
									if ($this->group_model->is_informal($param_details['ReferenceID'])) {
										$arr['Members'] = $this->group_model->members($param_details['ReferenceID'], $user_id);
									}
								}
								if ($param_details['Type'] == 'Event') {
									$module_id = 14;
								}
								if ($param_details['Type'] == 'User') {
									$module_id = 3;
								}
								if ($param_details['Type'] == 'Skills') {
									$module_id = 29;
								}
								if ($param_details['Type'] == 'Category') {
									$module_id = 27;
								}
								if ($param_details['Type'] == 'Forum') {
									$module_id = 33;
								}
								if ($param_details['Type'] == 'ForumCategory') {
									$module_id = 34;
								}
								if ($param_details['Type'] == 'EntityType') {
									$entity_type_details = array('1' => 'Post', '2' => 'Album', '3' => 'Photo', '4' => 'Video', '5' => 'Comment');
									if (isset($entity_type_details[$param_details['ReferenceID']])) {
										$e_details = array('FirstName' => $entity_type_details[$param_details['ReferenceID']], 'LastName' => '', 'ProfilePicture' => '', 'ProfileURL' => '', 'ModuleID' => 0, 'ModuleEntityGUID' => '', 'ModuleEntityID' => '');
									} else {
										$e_details = array('FirstName' => '', 'LastName' => '', 'ProfilePicture' => '', 'ProfileURL' => '', 'ModuleID' => 0, 'ModuleEntityGUID' => '', 'ModuleEntityID' => '');
									}
									if (isset($arr[$key]) && !empty($arr[$key])) {
										if (!in_array($e_details, $arr[$key])) {
											$arr[$key][] = $e_details;
										}
									} else {
										$arr[$key][] = $e_details;
									}
								} else if ($param_details['Type'] == 'Question') {
									// $PostContent = $this->db->get_where(ACTIVITY, array('ActivityID' => $param_details['ReferenceID']))->row()->PostContent;

									$arr[$key][] = array('FirstName' => get_activity_title($param_details['ReferenceID'], ''), 'LastName' => '', 'ProfilePicture' => '', 'ProfileURL' => '', 'ModuleID' => 0, 'ModuleEntityGUID' => 0);
								} else if ($param_details['Type'] == 'RequestNote') {
									$arr[$key][] = $arr[$key][] = array('FirstName' => get_request_note($param_details['ReferenceID'], $user_id), 'LastName' => '', 'ProfilePicture' => '', 'ProfileURL' => '', 'ModuleID' => 0, 'ModuleEntityGUID' => 0);
								} 
								elseif($param_details['Type'] == 'time')
								{
									$arr[$key][] = array('time'=>$param_details['ReferenceID']);
								}
								elseif($param_details['Type'] == 'count')
								{
									$arr[$key][0] = array('FirstName'=>$param_details['ReferenceID'],'LastName'=>'');
								}
								else {
									$ignore_logged_in_user = FALSE;
									if ($result['NotificationTypeID'] == 86) {
										$ignore_logged_in_user = TRUE;
									}
									if ($key == 'P1' && $param_details['Type'] == 'InformalGroup') {
										$entity_details = $this->user_model->getUserName($user_id, $module_id, $param_details['ReferenceID'], $ignore_logged_in_user, 1);
									} else {
										$entity_details = $this->user_model->getUserName($user_id, $module_id, $param_details['ReferenceID'], $ignore_logged_in_user);
									}

									if (isset($arr[$key]) && !empty($arr[$key])) {
										if (!in_array($entity_details, $arr[$key])) {
											$arr[$key][] = $entity_details;
										}
									} else {
										$arr[$key][] = $entity_details;
									}
								}
								if (in_array($result['NotificationTypeKey'], array('make_group_admin', 'make_event_admin', 'make_page_admin'))) {
									$entity_type = $param_details['Type'];
									$PageID = $param_details['ReferenceID'];
								}

								// Start
								if ($key == 'P2' && $result['NotificationTypeID'] == 46) {
									$PageID = $param_details['ReferenceID'];
								}
								if ($key == 'P2' && $result['NotificationTypeID'] == 48) {
									$PageID = $param_details['ReferenceID'];
								}
								if ($key == 'P2' && $result['NotificationTypeID'] == 61) {
									$PageID = $param_details['ReferenceID'];
								}
								if ($key == 'P2' && ($result['NotificationTypeID'] == 52 || $result['NotificationTypeID'] == 53)) {
									$PageID = $param_details['ReferenceID'];
								}
								if ($result['NotificationTypeKey'] == 'review_marked_helpful' || $result['NotificationTypeID'] == 63 || $result['NotificationTypeID'] == 64) {
									$PageID = $this->db->select('ModuleEntityID')->from(RATINGS)->where('RatingID', $result['RefrenceID'])->get()->row()->ModuleEntityID;
								}
								// Ends
							}
						}
					}
				}

				if ($result['NotificationTypeID'] == 3) {
					$activity_user_details = get_detail_by_id($result['RefrenceID'], 0, 'ModuleID,ModuleEntityID,UserID', 2);
					if ($activity_user_details['UserID'] != $user_id) {
						$this->load->model('users/user_model');
						$entity_details = $this->user_model->getUserName($user_id, $activity_user_details['ModuleID'], $activity_user_details['ModuleEntityID']);
						if ($result['fromUserID'] == $activity_user_details['UserID']) {
							$arr['NotificationText'] = str_replace('your', 'their', $arr['NotificationText']);
						} else {
							$arr['NotificationText'] = str_replace('your', $entity_details['FirstName'] . ' ' . $entity_details['LastName'] . "'s", $arr['NotificationText']);
						}
					}
				}
				if($result['NotificationTypeID'] == 131)
				{
					$arr['NotificationText'] = str_replace('#p1#', $arr[$key][0]['time'], $arr['NotificationText']);
					unset($arr['P1']);
					$arr['ProfilePicture'] = $result['ProfilePicture'];
				}

				if ($result['NotificationTypeID'] == 4 || $result['NotificationTypeID'] == 89 || $result['NotificationTypeID'] == 97 || $result['NotificationTypeID'] == 100 || $result['NotificationTypeID'] == 103 || $result['NotificationTypeID'] == 104 || $result['NotificationTypeID'] == 105 || $result['NotificationTypeID'] == 106 || $result['NotificationTypeID'] == 107 || $result['NotificationTypeID'] == 108 || $result['NotificationTypeID'] == 109 || $result['NotificationTypeID'] == 110 || $result['NotificationTypeID'] == 111 || $result['NotificationTypeID'] == 112) {
					$arr['ProfilePicture'] = array();
					$arr['Album'] = array();
				}

				if ($result['NotificationTypeID'] == 2) {
					$activity_data = get_detail_by_id($result['RefrenceID'], 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
					$owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);

					$original_notification_text = $arr['NotificationText'];

					if ($owner_data['UserID'] != $user_id && ($activity_data['ModuleEntityID'] != $user_id && $activity_data['ModuleID'] == '3')) {

						if ($owner_data['UserID'] == $result['fromUserID'] && isset($arr['P1']) && count($arr['P1']) == '1') {
							$arr['NotificationText'] = str_replace('commented on your', 'commented on their', $arr['NotificationText']);
						} else {
							$arr['NotificationText'] = str_replace('commented on your', 'commented on ' . $owner_data['FirstName'] . ' ' . $owner_data['LastName'] . '\'s', $arr['NotificationText']);
						}
					} elseif ($activity_data['ModuleID'] != '3' && $owner_data['UserID'] != $user_id) {
						$arr['NotificationText'] = str_replace('commented on your', 'commented on ' . $owner_data['FirstName'] . ' ' . $owner_data['LastName'] . '\'s', $arr['NotificationText']);
					}

					if ($activity_data['ModuleID'] == '18') {
						if (isset($arr['P1']) && count($arr['P1']) == '1' && $arr['P1'][0]['ModuleID'] == '18') {
							$page_module_id = get_detail_by_guid($arr['P1'][0]['ModuleEntityGUID'], 18, 'PageID', 1);
							if ($activity_data['ModuleEntityID'] == $page_module_id) {
								$arr['NotificationText'] = str_replace('commented on your', 'commented on their', $original_notification_text);
							}
						}
					}
				}

				if ($result['NotificationTypeID'] == 51) {
					$activity_data = get_detail_by_id($result['RefrenceID'], 21, '*', 2);
					$owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);

					if ($owner_data['UserID'] != $user_id) {
						if ($owner_data['UserID'] == $result['fromUserID'] && isset($arr['P1']) && count($arr['P1']) == '1') {
							$arr['NotificationText'] = str_replace('commented on your', 'commented on their', $arr['NotificationText']);
						} else {
							$arr['NotificationText'] = str_replace('commented on your', 'commented on ' . $owner_data['FirstName'] . ' ' . $owner_data['LastName'] . '\'s', $arr['NotificationText']);
						}
					}
				}

				if ($result['NotificationTypeID'] == 85) {
					$activity_data = get_detail_by_id($result['RefrenceID'], 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);

					if ($activity_data['ModuleID'] == 3 && $activity_data['ModuleEntityID'] == $user_id) {
						$arr['NotificationText'] = str_replace("#p2#'s", 'your', $arr['NotificationText']);
					}
				}

				/*added by gautam*/
				if ($this->IsApp == 1) {
					$arr['Link'] = $this->get_notification_link_phone($result['NotificationTypeID'], $result['RefrenceID'], $result['fromUserID'], $user_id, $PageID, $result['NotificationTypeKey'], $entity_type, $result['CustomParams']);
				} else {
                                    $arr['Link'] = $this->get_notification_link($result['NotificationTypeID'], $result['RefrenceID'], $result['fromUserID'], $user_id, $PageID, $result['NotificationTypeKey'], $entity_type, $result['CustomParams']);

                                    if ($result['NotificationTypeID'] == '49' || $result['NotificationTypeID'] == '50' || $result['NotificationTypeID'] == '51') {
                                        $arr['Link'] = get_detail_by_id($result['RefrenceID'], 21);
                                        $arr['IsLink'] = 0;
                                    } else if (isset($arr['Link']['EntityURL'])) {
                                        $arr['Link']['EntityURL'] = explode(site_url(), $arr['Link']['EntityURL']);
                                        $arr['Link']['EntityURL'] = $arr['Link']['EntityURL'][1];
                                    } else {
                                        $arr['Link'] = explode(site_url(), $arr['Link']);
                                        if (isset($arr['Link'][1])) {
                                            $arr['Link'] = $arr['Link'][1];
                                        }
                                    }
                                }

				
				if (isset($arr['P1'][0]) && $result['NotificationTypeID'] != 131) {
					$arr['ProfilePicture'] = $arr['P1'][0]['ProfilePicture'];
				}
				if (isset($arr['ProfilePicture'], $arr['P1'][0]['ModuleID']) && $arr['ProfilePicture'] == '' && $arr['P1'][0]['ModuleID'] == 18) {
					$entity = get_detail_by_guid($arr['P1'][0]['ModuleEntityGUID'], 18, "CategoryID", 2);
					if ($entity) {
						$category_name = $this->category_model->get_category_by_id($entity['CategoryID']);
						$category_icon = $category_name['Icon'];
						$arr['ProfilePicture'] = "icon_" . $category_icon;
					}
				}
				$data[] = $arr;
			}
		}
		return $data;
	}

	/**
	 * [get_notifications_test Get list of pagination or unreaded notification count depends on CountFlag]
	 * @param  [int]  $user_id       [User ID]
	 * @param  [int]  $page_no       [Page No]
	 * @param  [int]  $page_size     [Page Size]
	 * @param  boolean $CountFlag    [CountFlag]
	 * @param  boolean $All          [All]
	 * @param  [int] $TotalRecords [TotalRecords]
	 * @return [type]                [description]
	 */
	function get_notifications($user_id, $page_no = PAGE_NO, $page_size = PAGE_SIZE, $CountFlag = false, $All = false, $TotalRecords = 0, $status_filter = "", $list = false) {
		$this->load->model(array('group/group_model', 'activity/activity_model', 'timezone/timezone_model'));
		$group_list = $this->group_model->get_joined_groups($user_id);
		$condition1 = "(
                    IF(N.NotificationTypeID IN(1,2,3,18,19,20,21), (
                        (SELECT A.StatusID FROM " . ACTIVITY . " A WHERE A.ActivityID = N.RefrenceID) = 2
                    ), N.StatusID )
                )";
		$condition2 = "(
                IF(N.NotificationTypeID IN(23,24,25,4,43,44),
                    N.RefrenceID IN(" . $group_list . ")
                    ,true)
                )";
		//$Co
		$this->db->select('U.UserGUID,N.NotificationID, NT.NotificationTypeKey, N.UserID as fromUserID, N.NotificationGUID, N.CreatedDate, N.StatusID, N.NotificationTypeID, N.RefrenceID, NT.NotificationTypeName, NT.NotificationText, U.ProfilePicture, N.Params as CustomParams', FALSE);
		$this->db->from(NOTIFICATIONS . ' N');
		$this->db->join(NOTIFICATIONTYPES . ' NT', 'N.NotificationTypeID=NT.NotificationTypeID', 'left');
		$this->db->join(USERS . ' U', 'N.UserID=U.UserID AND U.StatusID=2', 'left', FALSE);
		$this->db->where($condition1, NULL, FALSE);
		$this->db->where($condition2, NULL, FALSE);
		$this->db->where('N.ToUserID', $user_id);

		//$this->db->where('U.StatusID','2');
		//$this->db->order_by('N.StatusID','asc');
		$this->db->order_by('N.CreatedDate', 'desc');
		if (!$list) {
			$this->db->where('N.NotificationID = (SELECT max(NS.NotificationID) FROM ' . NOTIFICATIONS . ' NS WHERE N.NotificationTypeID=NS.NotificationTypeID AND N.RefrenceID=NS.RefrenceID AND NS.ToUserID=' . $user_id . ')', NULL, FALSE);
			$this->db->group_by('N.RefrenceID');
			$this->db->group_by('N.NotificationTypeID');
			//$this->db->group_by('N.Params');
		}
		if ($CountFlag && $TotalRecords == '0') {
			$this->db->where_in('N.StatusID', array(15, 16));
		} else {
			if ($status_filter == "unread") {
				$this->db->where_in('N.StatusID', array(15, 16));
			}
			if (!$All) {

				$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
			}
		}

		$Notification = $this->db->get();
		//echo $this->db->last_query();
		$NotificationCount = $Notification->num_rows();
		if ($CountFlag) {
			return $NotificationCount;
		}

		$User = array();
		if ($NotificationCount > 0) {
			$TimeZone = $this->timezone_model->getUserTimeZone($user_id);
			foreach ($Notification->result_array() as $UserData) {

				$this->db->select('NotificationParamName,NotificationParamValue');
				$this->db->from(NOTIFICATIONPARAMS);
				$this->db->where('NotificationID', $UserData['NotificationID']);
				$NotificationParam = $this->db->get();
				//if called from list page
				if ($list) {
					$NotificationID = $UserData['NotificationID'];
				} else {
					//click on bell icon
					$NotificationID = false;
				}

				$NotificationUserData = $this->get_notification_username($user_id, $UserData['NotificationTypeID'], $UserData['RefrenceID'], $NotificationID);
				//$NotificationParam = $this->db->query("SELECT NotificationParamName,NotificationParamValue FROM " . NOTIFICATIONPARAMS . " WHERE NotificationID = '" . $UserData['NotificationID'] . "' ");

				$UserData['UserName'] = '';
				$PageID = '';
				$entity_type = '';

				if ($UserData['NotificationTypeID'] == 2) {
					$activity_data = get_detail_by_id($UserData['RefrenceID'], 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
					$owner_data = get_detail_by_id($activity_data['UserID'], 3, 'UserID,FirstName,LastName,ProfilePicture', 2);
					/* echo $owner_data;
                      echo ' - '.$user_id; */
					if ($owner_data['UserID'] != $user_id) {
						$UserData['NotificationText'] = str_replace('commented on your', 'commented on a', $UserData['NotificationText']);
					}
				}

				foreach ($NotificationParam->result_array() as $Val) {

					if ($UserData['NotificationTypeID'] == 52 && $Val['NotificationParamName'] == 1) {
						$prms = json_decode($Val['NotificationParamValue']);
						if ($prms->Type == 'Page') {
							$this->load->model('pages/page_model');
							$page_details = $this->page_model->get_page_detail_by_page_id($prms->ReferenceID);
							$NotificationUserData['ProfilePicture'] = $page_details['ProfilePicture'];
						}
					}

					if ($Val['NotificationParamName'] == 2 && $UserData['NotificationTypeID'] == 46) {
						$PageID = $Val['NotificationParamValue'];
					}
					if ($Val['NotificationParamName'] == 2 && $UserData['NotificationTypeID'] == 48) {
						$PageID = $Val['NotificationParamValue'];
					}
					if ($Val['NotificationParamName'] == 2 && $UserData['NotificationTypeID'] == 61) {
						$PageID = $Val['NotificationParamValue'];
					}
					if ($Val['NotificationParamName'] == 2 && ($UserData['NotificationTypeID'] == 52 || $UserData['NotificationTypeID'] == 53)) {
						$PageID = $Val['NotificationParamValue'];
					}
					if ($UserData['NotificationTypeKey'] == 'review_marked_helpful' || $UserData['NotificationTypeID'] == 63 || $UserData['NotificationTypeID'] == 64) {
						$PageID = $this->db->select('ModuleEntityID')->from(RATINGS)->where('RatingID', $UserData['RefrenceID'])->get()->row()->ModuleEntityID;
					}
					$Value = $this->get_notification_value($Val['NotificationParamValue'], $this->UserID);
					if ($Val['NotificationParamName'] == 1 && ($UserData['NotificationTypeKey'] !== 'make_group_admin' && $UserData['NotificationTypeKey'] !== 'make_event_admin' && $UserData['NotificationTypeKey'] !== 'make_page_admin')) {
						$UserData['UserName'] = $NotificationUserData['UserName'];
						if ($UserData['NotificationTypeKey'] == 'event_cancel_to_admin' || $UserData['NotificationTypeKey'] == 'event_cancel') {
							$UserData['UserName'] = $Value['Name'];
						}
						$UserData['NotificationText'] = str_replace('#p' . $Val['NotificationParamName'] . '#', '', $UserData['NotificationText']);
						$pos = strpos($UserData['NotificationText'], '#Gender#');
						if ($pos !== false) {
							$ParamVal = json_decode($Val['NotificationParamValue'], true);
							if ($ParamVal['Type'] == 'User') {
								$Gender = $this->get_gender($ParamVal['ReferenceID']);
							} else {
								$Gender = 'their';
							}
							$UserData['NotificationText'] = str_replace('#Gender#', $Gender, $UserData['NotificationText']);
						}
					} else {
						$ParamVal = json_decode($Val['NotificationParamValue'], true);
						if ($UserData['NotificationTypeID'] == 48 && $UserData['fromUserID'] == $ParamVal['ReferenceID']) {

							if ($ParamVal['Type'] == 'User') {
								//$ParamVal                   = json_decode($Val['NotificationParamValue'], true);
								$Gender = $this->get_gender($ParamVal['ReferenceID']);
							} else {
								$Gender = 'their';
							}
							$UserData['NotificationText'] = str_replace('#p' . $Val['NotificationParamName'] . "#'s", $Gender, $UserData['NotificationText']);
						} else {
							//$UserData['NotificationText'] = str_replace('#p' . $Val['NotificationParamName'] . '#', '#'.$Value['Name']."#", $UserData['NotificationText']);

							$UserData['NotificationText'] = str_replace('#p' . $Val['NotificationParamName'] . '#', $Value['Name'], $UserData['NotificationText']);
						}
					}
					if ($UserData['NotificationTypeKey'] == 'make_group_admin' || $UserData['NotificationTypeKey'] == 'make_event_admin' || $UserData['NotificationTypeKey'] == 'make_page_admin') {
						$UserData['NotificationText'] = str_replace('#p' . $Val['NotificationParamName'] . '#', '#' . $Value['Name'] . '#', $UserData['NotificationText']);
						$n_data = json_decode($Val['NotificationParamValue'], true);
						$entity_type = $n_data['Type'];
						$PageID = $n_data['ReferenceID'];
					}

					if ($UserData['NotificationTypeID'] == 3 && $Val['NotificationParamName'] == 2) {
						$param_details = json_decode($Val['NotificationParamValue']);
						if ($param_details->ReferenceID == 1) {
							$activity_user_id = get_detail_by_id($UserData['RefrenceID'], 0, 'UserID', 1);
							if ($activity_user_id != $user_id) {
								$UserData['NotificationText'] = str_replace('your Post', 'a Post on your wall', $UserData['NotificationText']);
							}
						}
					}
				}
				$UserData['NotificationText'] = trim(str_replace(' #p3#.', '', $UserData['NotificationText']));

				$UserData['NotificationText'] = trim(str_replace('#p2#', ' #Post#', $UserData['NotificationText']));

				$DateTime = $UserData['CreatedDate'];

				$summary_data = $this->get_activity_summary($UserData['NotificationTypeID'], $UserData['RefrenceID'], $UserData['CustomParams'], $user_id);
				$UserData['Summary'] = $summary_data['Summary'];

				$UserData['ProfilePicture'] = $NotificationUserData['ProfilePicture']; //get_full_path($type = 'profile_image','', $UserData['ProfilePicture'], $height = '192', $width = '192', $size = '192');
				$UserData['Link'] = $this->get_notification_link($UserData['NotificationTypeID'], $UserData['RefrenceID'], $UserData['fromUserID'], $user_id, $PageID, $UserData['NotificationTypeKey'], $entity_type, $UserData['CustomParams']);
				$UserData['DateOrder'] = date('M d', strtotime($DateTime));
				$UserData['CreatedDate'] = $UserData['CreatedDate'];
				$UserData['Album'] = array();

				$album_details = $summary_data['Album'];
				$UserData['IsLink'] = 1;
				if ($UserData['NotificationTypeID'] == '49' || $UserData['NotificationTypeID'] == '50' || $UserData['NotificationTypeID'] == '51') {
					$UserData['Link'] = get_detail_by_id($UserData['RefrenceID'], 21);
					$UserData['IsLink'] = 0;
				} else {
					$UserData['Link'] = explode(site_url(), $UserData['Link']);
					$UserData['Link'] = $UserData['Link'][1];
				}

				//For system generated notifications
				if ($UserData['fromUserID'] == $user_id) {
					$UserData['UserName'] = '';
					$UserData['ProfilePicture'] = ASSET_BASE_URL . 'img/logo.svg';
				}

				if ($UserData['NotificationTypeID'] == '16') {
					$this->load->model('users/friend_model');
					$friend_status = $this->friend_model->checkFriendStatus($UserData['fromUserID'], $user_id);
					if ($friend_status == 2) {
						$UserData['ShowAcceptDeny'] = 1;
					} else {
						$UserData['ShowAcceptDeny'] = 0;
					}
				} else {
					$UserData['ShowAcceptDeny'] = 0;
				}

				$UserData['StatusID'] = trim($UserData['StatusID']);
				$UserData['Class'] = $this->get_notification_icon($UserData['NotificationTypeID']);
				unset($UserData['NotificationID']);
				unset($UserData['DateOrder']);
				unset($UserData['fromUserID']);
				unset($UserData['NotificationTypeName']);
				unset($UserData['NotificationTypeID']);
				unset($UserData['RefrenceID']);
				unset($UserData['NotificationTypeKey']);
				$User[] = $UserData;
			}
		}
		return $User;
	}

	/**
	 * Function Name: get_unread_count
	 * @param user_id
	 * Description: get count off the unread notification
	 * of the user id passed as param
	 * @return int;
	 */
	function get_unread_count($user_id) {
            $this->db->where('ToUserID', $user_id);
           // $this->db->where('N.LocalityID', $this->LocalityID);
            $this->db->where_in('StatusID', array(15, 16));
            $res = $this->db->get(NOTIFICATIONS);
            return $res->num_rows();
	}

	/**
	 * Function Name: get_notification_icon
	 * @param type_id
	 * Description: get class to show notification icon
	 * @return string;
	 */
	function get_notification_icon($type_id) {
		$class = '';
		switch ($type_id) {
		case 1:
		case 2:
		case 19:
		case 51:
		case 55:
		case 63:
		case 86:
		case 87:
		case 113:
		case 120:
		case 131:
		case 127:
			$class = 'ficon-comment f-lg';
			break;
		case 5:
			$class = 'ficon-followers';
			break;
		case 3:
		case 20:
		case 49:
		case 50:
		case 54:
			$class = 'ficon-heart';
			break;
		case 18:
		case 21:
		case 74:
		case 70:
		case 71:
		case 72:
			$class = 'ficon-tag';
			break;
		case 16:
		case 17:
			$class = 'ficon-addfriend';
			break;
		case 26:
		case 27:
		case 28:
		case 29:
		case 30:
		case 31:
		case 32:
		case 33:
		case 34:
		case 35:
		case 61:
		case 67:
			$class = 'ficon-calc';
			break;
		case 47:
		case 48:
			$class = 'ficon-share  f-lg';
			break;
		case 4:
		case 22:
		case 23:
		case 24:
		case 25:
		case 43:
		case 44:
		case 62:
		case 130:
		case 83:
			$class = 'ficon-friends f-sm';
			break;
		case 45:
		case 46:
		case 52:
		case 53:
		case 60:
		case 68:
		case 69:
			$class = 'ficon-document';
			break;
		case 82:
			$class = 'ficon-video';
			break;
		case 64:
		case 65:
			$class = 'ficon-flag';
			break;
		case 84:
		case 85:
			$class = 'ficon-restore';
			break;
		case 110:
		case 111:
		case 112:
			$class = 'ficon-poll';
			break;
		case 125:
		case 126:
			$class = 'ficon-announcement';
			break;
		}
		return $class;
	}

	function archive_users_notifications($user_id=0,$no_of_days='',$no_of_notifications='') {
            if(!$no_of_notifications)
                $no_of_notifications = MOSTRECENTNOTIFICATIONCOUNT;
            if(!$no_of_days)
                $no_of_days = MOSTRECENTNOTIFICATIONDAYS;
                    //select all users
            if(!$user_id) {
                $this->db->select('UserID');
                $this->db->order_by('UserID');
                $query = $this->db->get(USERS);
                if($query->num_rows()) {
                    foreach ($query->result_array() as $value) {				
                        $this->archive_notification_queries($value['UserID'],$no_of_days,$no_of_notifications);
                    }
                }            
            } else {
                $this->archive_notification_queries($user_id,$no_of_days,$no_of_notifications);
            }
            return true;
	}

	function archive_notification_queries($user_id,$no_of_days,$no_of_notifications) {  
            // $this->db->simple_query('SET SESSION group_concat_max_len=300');
		//get last 15 Days Notification
		$query_most_recent_days = "SELECT NotificationID
				FROM  `Notifications` 
				WHERE CreatedDate > DATE_SUB( DATE( NOW( ) ) , INTERVAL '".$no_of_days."' 
				DAY ) 
				AND ToUserID = '$user_id'
				ORDER BY NotificationID DESC";
            $noti_most_recent_days = $this->db->query($query_most_recent_days);
            $notification_days=$notification_count=array();
            if($noti_most_recent_days->num_rows())
            {
                foreach ($noti_most_recent_days->result_array() as $value) 
                {
                    $notification_days[] = $value['NotificationID'];
                }
            }

        
            //get Last 300 Notifications
            $query_most_recent_count = "SELECT NotificationID
                            FROM  `Notifications` 
                            WHERE ToUserID = '$user_id'
                            ORDER BY NotificationID DESC 
				LIMIT ". $this->db->escape_str($no_of_notifications);
            $noti_most_recent_count = $this->db->query($query_most_recent_count);
        
		$most_recent_notification_count = $this->get_most_recent_notification_count($user_id,$no_of_days,$no_of_notifications);

            if($noti_most_recent_count->num_rows())
            {
                foreach ($noti_most_recent_count->result_array() as $value) 
                {
                    $notification_count[] = $value['NotificationID'];
                }
            }
            if(empty($notification_days))
                $notification_days = 0;
            else
                $notification_days = implode(',',$notification_days);

        if(empty($notification_count))
            $notification_count = 0;
        else
            $notification_count = implode(',',$notification_count);

		//Archive all notifications of last 300 or given count
        if($most_recent_notification_count)
        {            
    		if($most_recent_notification_count < $no_of_notifications)
    		{
    			$insert = "INSERT INTO ".NOTIFICATIONS_ARCHIVE." (SELECT * 
    				FROM ".NOTIFICATIONS."
    				WHERE NotificationID NOT 
    				IN (".$notification_count.")
    				AND ToUserID = '$user_id' )";
    			$delete = "DELETE FROM ".NOTIFICATIONS."
    				WHERE NotificationID NOT 
    				IN (".$notification_count.")
    				AND ToUserID = '$user_id' ";
    		}
    		else
    		{
    			//Archive all notifications of last 15 or given days
    			$insert = "INSERT INTO ".NOTIFICATIONS_ARCHIVE." (SELECT * 
    				FROM ".NOTIFICATIONS."
    				WHERE NotificationID NOT 
    				IN (".$notification_days.")
    				AND ToUserID = '$user_id' )";
    			$delete = "DELETE FROM ".NOTIFICATIONS."
    				WHERE NotificationID NOT 
    				IN (".$notification_days.")
    				AND ToUserID = '$user_id'";
    		}
    		$this->db->query($insert);
    		$this->db->query($delete);		
            // $total_noti = $this->get_total_user_notification($user_id);
            // echo $user_id.'**'.$most_recent_notification_count.'**'.$total_noti.'<br>';
        }
        
	}

	function get_most_recent_notification_count($user_id,$no_of_days,$no_of_notifications)
	{
		$this->db->select('NotificationID');
		$this->db->where("CreatedDate > DATE_SUB( DATE( NOW( ) ) , INTERVAL ".MOSTRECENTNOTIFICATIONDAYS." DAY )",NULL,FALSE);
		$this->db->where('ToUserID',$user_id);
		$query = $this->db->get(NOTIFICATIONS);
		return $query->num_rows();
	}

        function get_total_user_notification($user_id)
        {
            $this->db->select('NotificationID');
            // $this->db->where("CreatedDate > DATE_SUB( DATE( NOW( ) ) , INTERVAL ".MOSTRECENTNOTIFICATIONDAYS." DAY )",NULL,FALSE);
            $this->db->where('ToUserID',$user_id);
            $query = $this->db->get(NOTIFICATIONS);
            return $query->num_rows();
        }
        
        function post_notification($data) {
            $sender_id = !empty($data['UserID']) ? $data['UserID'] : 0;
            $sender_name = !empty($data['SenderName']) ? $data['SenderName'] : '';
            $activity_id = !empty($data['ActivityID']) ? $data['ActivityID'] : 0;
            $locality_id = !empty($data['LocalityID']) ? $data['LocalityID'] : 0;
            $mentions = !empty($data['Mentions']) ? $data['Mentions'] : array();
            $mentions[] = $sender_id;
            if($sender_id && $sender_name && $activity_id) {
                $this->load->model(array('activity/activity_model'));
                
                $notification_type_key = $data['NotificationTypeKey'];
                $notification_type_id = 0;
                $subject = $sender_name . " posted a new message.";
                if($notification_type_key == 'post_message') {
                    $notification_type_id = 153;
                }
                if($notification_type_key == 'post_feature') {
                    $subject = $sender_name . " as featured a new post.";
                }
                if($notification_type_key == 'pin_post') {
                    $subject = $sender_name . " has pinned a new post.";
                }
                $notification_data = array("NotificationTypeKey" => $notification_type_key, "UserID" => $sender_id, "FromUserDetails" => "", "NotificationTypeID" => $notification_type_id, "RefrenceID" => $activity_id, "Params" => "");
                
                $post_content = !empty($data['PostContent']) ? $data['PostContent'] : '';            
                $post_content = $this->activity_model->parse_tag_html($post_content);
                $notification_data['Summary'] = $post_content;
                                
                $this->db->select("AL.UserID");
                $this->db->from(ACTIVELOGINS . ' AL');
                $this->db->join(USERS . ' U', 'U.UserID=AL.UserID AND U.StatusID NOT IN (3,4)');            
                if (!empty($mentions)) {
                    $this->db->where_not_in('AL.UserID', $mentions);
                }
                $this->db->group_by('AL.UserID');
                $query = $this->db->get();
                //echo $this->db->last_query();die;
                if($query->num_rows()) {       
                    
                    foreach ($query->result_array() as $userdata) {
                        
                        $notification_data['ToUserID'] = $userdata['UserID'];
                        $notification_data['ToUserDetails'] = "";
                        initiate_worker_job('SendPushMsg', array('ToUserID'=>$notification_data['ToUserID'], 'Subject'=>$subject, 'notifications'=>$notification_data),'','notification');
                        
                       /* $parameters[0]['ReferenceID'] = $sender_id;
                        $parameters[0]['Type'] = 'User';
                        $this->add_notification(153, $sender_id, array($userdata['UserID']), $activity_id, $parameters, true, 0, array(), $locality_id);
                        * 
                        */
                    }
                }
            }
        }
        
        function badge_notification($data) {
            $sender_id = !empty($data['UserID']) ? $data['UserID'] : 0;
            $activity_id = !empty($data['ActivityID']) ? $data['ActivityID'] : 0;
            $mentions = !empty($data['Mentions']) ? $data['Mentions'] : array();
            $mentions[] = $sender_id;
            if($sender_id && $activity_id) {
                $notification_type_key = $data['NotificationTypeKey'];
                $notification_type_id = 0;
                $subject = $data['SenderName'] . " posted a new message.";
                if($notification_type_key == 'post_message') {
                    $notification_type_id = 154;
                }
                if($notification_type_key == 'post_feature') {
                    $subject = $data['SenderName'] . " as featured a new post.";
                }
                if($notification_type_key == 'pin_post') {
                    $subject = $data['SenderName'] . " has pinned a new post.";
                }
                $notification_data = array("NotificationTypeKey" => $notification_type_key, "UserID" => $sender_id, "FromUserDetails" => "", "NotificationTypeID" => $notification_type_id, "RefrenceID" => $activity_id, "Params" => "");
                
                $this->load->helper('activity');
                $activity_data = get_detail_by_id($activity_id, 0, 'PostTitle,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);

                $activity_data['PostContent'] = $this->activity_model->parse_tag_html($activity_data['PostContent']);
                $notification_data['Summary'] = $activity_data['PostContent'];
                                
                $this->db->select("U.UserGUID, U.UserID");
                $this->db->from(USERS . ' U');
                //$this->db->where('U.UserID !=', $sender_id);
                $this->db->where_not_in('U.StatusID', array(3, 4));
                if(!empty($mentions)) {
                    $this->db->where_not_in('U.UserID', $mentions);
                }
                $query = $this->db->get();
                //echo $this->db->last_query();die;
                $directory_data = array();
                if($query->num_rows()) {       
                    
                    foreach ($query->result_array() as $userdata) {
                        
                        $notification_data['ToUserID'] = $userdata['UserID'];
                        $notification_data['ToUserDetails'] = "";
                        initiate_worker_job('SendPushMsg', array('ToUserID'=>$notification_data['ToUserID'], 'Subject'=>$subject, 'notifications'=>$notification_data),'','notification');
                        
                       /* $parameters[0]['ReferenceID'] = $sender_id;
                        $parameters[0]['Type'] = 'User';
                        $this->add_notification(154, $sender_id, array($userdata['UserID']), $activity_id, $parameters);
                        * 
                        */
                    }
                }
            }
        }
        
        function get_notification_type_data($notification_type_id) {            
            $notification_type_data = array();
            if (CACHE_ENABLE) {
                $notification_type_data = $this->cache->get('notification_type_'.$notification_type_id);
            }           

            if (empty($notification_type_data)) {
                
                $this->db->select('ModuleID,NotificationTypeKey');
		$this->db->from(NOTIFICATIONTYPES);
		$this->db->where('NotificationTypeID', $notification_type_id);
		$module_query = $this->db->get();
		if ($module_query->num_rows()) {
                    $module_row = $module_query->row();
                    $module_id = $module_row->ModuleID;
                    $notification_type_key = $module_row->NotificationTypeKey;
                    $notification_type_data = array('NotificationTypeKey' => $notification_type_key, 'ModuleID' => $module_id);
		}
                
                if (CACHE_ENABLE) {
                    $this->cache->save('notification_type_'.$notification_type_id, $notification_type_data, CACHE_EXPIRATION);
                }
            }
            return $notification_type_data;
        }
        //medthod owner : trilok umath 
        function send_notification($data){
        	$useridsString = !empty($data['useridsString']) ? $data['useridsString'] : 0;
        	$notification_text = !empty($data['notification_text']) ? $data['notification_text'] : 0;
        	$isSms = !empty($data['isSms']) ? $data['isSms'] : 0;
        	if($isSms==0){

	        	$Query = $this->db->query("SELECT UserID,DeviceToken, DeviceTypeID FROM `ActiveLogins` WHERE UserID IN (".$useridsString.") AND DeviceToken!='' GROUP BY DeviceToken, DeviceTypeID ");
	            if ($Query->num_rows() > 0) {
	                foreach ($Query->result_array() as $Notifications) { 
	                    $token = $Notifications['DeviceToken'];
	                    
	                    $push_notification = array("EntityID" => "", "ModuleID" => "", "ModuleEntityGUID" => "", "Refer" => "UPDATE_APP", "EntityGUID" => "");
	                    $message = $notification_text;
	                    $return = push_notification_android(array($token), $message, 0, array("PushNotification" => $push_notification));                 
	                                    
	                }
	            } 
            } else {
            	
		        $Query = $this->db->query("SELECT UserID,PhoneNumber FROM `Users` WHERE UserID IN (".$useridsString.")");
	            if ($Query->num_rows() > 0) {
	                foreach ($Query->result_array() as $Notifications) { 
	                    
	                    $phone_number = $Notifications['PhoneNumber'];
	                    //send sms using sms library 	 
		               	$this->load->library('TwoFactorSMS');
				        $TwoF = new TwoFactorSMS(TWO_FACTOR_SMS_API_KEY, TWO_FACTOR_SMS_API_ENDPOINT);
				        $result = $TwoF->SendPromotionalSMS("PROMOS","91".$phone_number, $notification_text, "");              
	                                    
	                }
	            }

            }
            	
        }
            
        
}
