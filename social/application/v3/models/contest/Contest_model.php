<?php
/**
 * This model is used for getting and storing Catetgory related information
 * @package    Contest_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Contest_model extends Common_Model {

    function __construct() 
    {
        parent::__construct();
    }

    /**
     * Function Name: insert_update_contest
     * @param update_data[], activity_id
     * Description: Function checks if contest is already exists or not. If yes then it updates otherwise create new contest
     */
    function insert_update_contest($update_data,$activity_id)
    {
    	$insertUpdateArray = array(
            'table' => CONTEST,
            'where' => array('colName' => 'ActivityID', 'val' => $activity_id),
            'data' => $update_data
        );
        $this->insertUpdate($insertUpdateArray);
    }

    /**
     * Function Name: add_participant
     * @param data[]
     * Description: Function add new participant in particular contest and increase no of participants in contest table
     */
    function add_participant($data)
    {
        $this->load->model('activity/activity_model');
        $this->insert(PARTICIPANTS,$data);

        $params = get_detail_by_id($data['ActivityID'],19,'Params',1);
        $params = json_decode($params,true);
        if(!isset($params['NoOfParticipants']))
        {
            $params['NoOfParticipants'] = 0;   
        }
        $params['NoOfParticipants'] = $params['NoOfParticipants']+1;
        $this->db->set('Params',"'".json_encode($params)."'",false);
        $this->db->where('ActivityID',$data['ActivityID']);
        $this->db->update(ACTIVITY);

        //$this->activity_model->addActivity(3, $data['ParticipantID'], 38, $data['ParticipantID'], $data['ActivityID'], '', 1, '', array(), '0', 0, 3, $data['ParticipantID'], 0);
    }

    /**
     * Function Name: get_participant_friends
     * @param activity_id,user_id
     * Description: get list of friends who participate in contest
     */
    function get_participant_friends($activity_id,$user_id)
    {
        $data = array();

        $this->load->model('users/friend_model');
        $friend_ids = $this->friend_model->getFriendIDS($user_id);
        if($friend_ids)
        {
            $this->db->select('U.UserID,U.FirstName,U.LastName,U.UserGUID,PU.Url as ProfileURL',false);
            $this->db->from(PARTICIPANTS.' P');
            $this->db->join(USERS.' U','U.UserID=P.ParticipantID','left');
            $this->db->join(PROFILEURL.' PU','U.UserID=PU.EntityID AND PU.EntityType="User"','left');
            $this->db->where('P.ActivityID',$activity_id);
            $this->db->where_in('P.ParticipantID',$friend_ids);
            $this->db->limit(3);
            $query = $this->db->get();

            if($query->num_rows())
            {
                foreach($query->result_array() as $user)
                {
                    $data[] = $user;
                }
            }
        }

        return $data;
    }

     /**
     * Function Name: get_participant_list
     * @param activity_id,user_id
     * Description: get list of participant in contest
     */
    function get_participant_list($activity_id,$page_no='',$page_size='')
    {
        $data = array();

        $this->db->select('U.UserGUID,U.FirstName,U.LastName,IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture, U.UserID,PU.Url AS ProfileURL', false);
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CN.CountryName,"") as CountryName', FALSE);
        //$this->db->select("IF(U.UserID='" . $user_id . "',2,IF(U.UserID IN('" . $friends_list . "'),1,0)) as OrderByVar", false);
        $this->db->from(PARTICIPANTS.' P');
        $this->db->join(USERS . ' U', 'U.UserID=P.ParticipantID', 'left');
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->join(USERDETAILS . ' UD', 'U.UserID=UD.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CN', 'CN.CountryID=UD.CountryID', 'left');
        $this->db->where('P.ActivityID',$activity_id);

        if (!empty($page_size))
        {
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }
        $query = $this->db->get();

        if(!$page_no && !$page_size)
        {
            return $query->num_rows();
        }

        if($query->num_rows())
        {
            foreach($query->result_array() as $user)
            {
                $data[] = $user;
            }
        }

        return $data;
    }

    /**
     * Function Name: get_contest_winners
     * @param activity_id,user_id
     * Description: get winners list of contest
     */
    function get_contest_winners($activity_id,$user_id,$exclude=array())
    {
        $data = array();

        $this->db->select('U.UserID,U.FirstName,U.LastName,U.ProfilePicture,U.UserGUID,PU.Url as ProfileURL',false);
        $this->db->from(PARTICIPANTS.' P');
        $this->db->join(USERS.' U','U.UserID=P.ParticipantID','left');
        $this->db->join(PROFILEURL.' PU','U.UserID=PU.EntityID AND PU.EntityType="User"','left');
        $this->db->where('P.ActivityID',$activity_id);
        $this->db->where('P.IsWinner','1');
        if($exclude)
        {
            $this->db->where_not_in('P.ParticipantID',$exclude);
        }
        $query = $this->db->get();

        if($query->num_rows())
        {
            foreach($query->result_array() as $user)
            {
                $data[] = $user;
            }
        }

        return $data;
    }

    /**
     * Function Name: update_contest
     * @param activity_id
     * Description: check and update contest
     */
    function update_contest($activity_id)
    {
        $this->load->model('activity/activity_model');

        $user_id = 0;

        $this->db->select('UserID');
        $this->db->from(USERROLES);
        $this->db->where('RoleID', '1');
        $this->db->limit(1);
        $admin_query = $this->db->get();
        if ($admin_query->num_rows()) {
            $user_id = $admin_query->row()->UserID;
        }

        $this->db->select('A.*');
        $this->db->from(ACTIVITY.' A');
        $this->db->where('A.ActivityID',$activity_id);
        $activity_query = $this->db->get();

        if($activity_query->num_rows())
        {
            $row = $activity_query->row_array();

            $params         = json_decode($row['Params'],true);

            $no_of_winners  = $params['NoOfWinners'];

            $this->db->select('ParticipationID');
            $this->db->from(PARTICIPANTS);
            $this->db->where('ActivityID',$activity_id);
            $this->db->limit($no_of_winners);
            $this->db->order_by('RAND()');
            $query = $this->db->get();

            if($query->num_rows())
            {
                foreach($query->result_array() as $user)
                {
                    $this->db->set('IsWinner','1');
                    $this->db->where('ParticipationID',$user['ParticipationID']);
                    $this->db->update(PARTICIPANTS);
                }
            }
            $this->db->set('IsWinnerAnnounced','1');
            $this->db->set('StatusID','3');
            $this->db->where('ActivityID',$activity_id);
            $this->db->update(ACTIVITY);

            $new_activity_id = $this->activity_model->addActivity('3', $user_id, 39, $user_id, $row['ActivityID'], $row['PostContent'], 1, '', $params,$row['IsMediaExist'], 0, 3, 0, 0,$row['PostTitle']);

            $this->db->select('ParticipantID,IsWinner');
            $this->db->from(PARTICIPANTS);
            $this->db->where('ActivityID',$activity_id);
            $query_for_notification = $this->db->get();

            $this->db->set('MediaSectionReferenceID',$new_activity_id);
            $this->db->where('MediaSectionID','3');
            $this->db->where('MediaSectionReferenceID',$activity_id);
            $this->db->update(MEDIA);

            if($query_for_notification->num_rows())
            {
                $winner_list = array();
                $participant_list = array();
                foreach($query_for_notification->result_array() as $r)
                {
                    if($r['IsWinner'] == '1')
                    {
                        $winner_list[] = $r['ParticipantID'];
                    }
                    else
                    {
                        $participant_list[] = $r['ParticipantID'];
                    }
                }

                if($winner_list)
                {
                    $this->notification_model->add_notification(ADMIN_USER_ID, $user_id, $winner_list, $new_activity_id, array());
                }
                if($participant_list)
                {
                    $this->notification_model->add_notification(146, $user_id, $participant_list, $new_activity_id, array());
                }
            }
            else
            {
                $this->db->set('StatusID','3');
                $this->db->where('ActivityID',$new_activity_id);
                $this->db->update(ACTIVITY);
            }

            //$this->notification_model->add_notification(47, $user_id, array($module_entity_id), $activity_id, $parameters);
        }
    }

    /**
     * Function Name: get_contests
     * @param user_id
     * Description: returns id of contests of particular user
     */
    function get_contests($user_id)
    {
        $data = array();
        $data[] = 0;

        $this->db->select('ActivityID');
        $this->db->from(PARTICIPANTS);
        $this->db->where('ParticipantID',$user_id);
        $query = $this->db->get();
        if($query->num_rows())
        {
            foreach($query->result_array() as $activity)
            {
                $data[] = $activity['ActivityID'];
            }
        }
        return $data;
    }

    /**
     * Function Name: delete_contest
     * @param activity_id
     * Description: Function delete particular contest
     */
    function delete_contest($activity_id)
    {
    	$this->db->where('ActivityID',$activity_id);
    	$this->db->delete(CONTEST);

    	$this->db->where('ActivityID',$activity_id);
    	$this->db->delete(PARTICIPANTS);
    }

    /**
     * Function Name: delete_participant
     * @param activity_id, participant_id
     * Description: Function delete participant from contest and reduce no of participants
     */
    function delete_participant($activity_id,$participant_id)
    {
    	$this->db->where('ActivityID',$activity_id);
    	$this->db->where('ParticipantID',$participant_id);
    	$this->db->delete(PARTICIPANTS);

        $params = get_detail_by_id($data['ActivityID'],19,'Params',1);
        $params = json_decode($params,true);
        if(!isset($params['NoOfParticipants']))
        {
            $params['NoOfParticipants'] = 0;   
        }
        $params['NoOfParticipants'] = $params['NoOfParticipants']-1;
        $this->db->set('Params',"'".json_encode($params)."'",false);
        $this->db->where('ActivityID',$data['ActivityID']);
        $this->db->update(ACTIVITY);
    }

    /**
     * Function Name: is_valid_participation
     * @param activity_id, participant_id
     * Description: Function checks if person try to participate in this contest is eligible or not
     */
    function is_valid_participation($activity_id,$participant_id)
    {
    	$valid = false;
    	$contest_details = $this->get_contest_details($activity_id);
    	if($contest_details)
    	{
            /*$contest_details['Params'] = json_decode($contest_details['Params'],true);
            if(!isset($contest_details['Params']['NoOfSeats']))
            {
                $contest_details['Params']['NoOfSeats'] = 0;
            }
            if(!isset($contest_details['Params']['NoOfParticipants']))
            {
                $contest_details['Params']['NoOfParticipants'] = 0;
            }*/
            //print_r($contest_details);
    		/*if($contest_details['Params']['NoOfSeats'] > $contest_details['Params']['NoOfParticipants'])
    		{*/
    			if($contest_details['ContestEndDate'] > get_current_date('%Y-%m-%d %H:%i:%s'))
    			{
    				if(!$this->is_participating($activity_id,$participant_id))
    				{
    					$valid = true;
    				}
    			}
    		/*}*/
    	}

    	return $valid;
    }

    /**
     * Function Name: is_participating
     * @param activity_id, participant_id
     * Description: Function checks if person is already participating or not
     */
    function is_participating($activity_id,$participant_id)
    {
    	$this->db->select('ParticipationID');
    	$this->db->from(PARTICIPANTS);
    	$this->db->where('ParticipantID',$participant_id);
    	$this->db->where('ActivityID',$activity_id);
    	$query = $this->db->get();
    	if($query->num_rows())
    	{
    		return true;
    	}
    	return false;
    }

    /**
     * Function Name: get_contest_details
     * @param activity_id
     * Description: Function returns basic contest details
     */
    function get_contest_details($activity_id)
    {
    	$data = [];
    	$this->db->select('PostTitle,PostContent,CreatedDate,ContestEndDate,Params');
    	$this->db->from(ACTIVITY);
    	$this->db->where('ActivityID',$activity_id);
    	$query = $this->db->get();
    	if($query->num_rows())
    	{
    		$data = $query->row_array();
    	}
    	return $data;
    }

    /**
     * Function Name: insertUpdate
     * @param data
     * Description: Function checks if record is already exists or not. If exists then it updates record otherwise add
     */
    function insertUpdate($data = array()) {
        $this->db->select($data['where']['colName']);
        $this->db->where(array($data['where']['colName'] => $data['where']['val']));
        $this->db->from($data['table']);
        $this->db->limit(1);
        $query = $this->db->get();
        $resArray = $query->row_array();
        if (!empty($resArray)) {/* update */
            $this->db->where(array($data['where']['colName'] => $data['where']['val']));
            $this->db->update($data['table'], $data['data']);
        } else {/* insert */
            $insertArray = array_merge($data['data'], array($data['where']['colName'] => $data['where']['val']));
            $this->db->insert($data['table'], $insertArray);
        }
    }

    /**
     * Function Name: mark_participant_as_winner
     * @param activity_id, participants
     * Description: Function marks multiple participants as winner of contest
     */
    function mark_participant_as_winner($activity_id,$participants)
    {
        if($participants)
        {
            $data = array();
            foreach ($variable as $key => $value) {
                $data[] = array('IsWinner' => '1' ,'ParticipantID' => $value);
            }
            $this->db->update_batch(PARTICIPANTS, $data, 'ParticipantID'); 
        }
    }
}