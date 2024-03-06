<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of Contest
 * @copyright (c) 2015, Vinfotech
 * @author mohitb <mohit.bumb@vinfotech.com>
 * @version 1.0
 */
class Contest extends Common_API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contest/contest_model');
    }

    /**
     * Function Name: create
     * @param Title,Description,Image,Heading,NoOfSeats,StartDate,EndDate,WinnerAnnouncementDate
     * Description: Create / Update contest
     */
    public function create_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        /* Validation - starts */
        if ($this->form_validation->run('api/contest/create') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        }/* Validation - end */ else
        {
            $update_data = array();
            $update_data['Title']                       = $data['Title'];
            $update_data['Description']                 = $data['Description'];
            $update_data['Image']                       = $data['Image'];
            $update_data['Heading']                     = $data['Heading'];
            $update_data['NoOfSeats']                   = $data['NoOfSeats'];
            $update_data['StartDate']                   = $data['StartDate'];
            $update_data['EndDate']                     = $data['EndDate'];
            $update_data['WinnerAnnouncementDate']      = $data['WinnerAnnouncementDate'];

            $contest_id = isset($data['ActivityID']) ? $data['ActivityID'] : '' ;

            $this->contest_model->insert_update_contest($update_data,$contest_id);
        }
        $this->response($return);
    }

    /**
     * Function Name: add_participant
     * @param ActivityID,ParticipantID
     * Description: Add participant to contest
     */
    public function add_participant_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        $user_id = $this->UserID;

        /* Validation - starts */
        if ($this->form_validation->run('api/contest/add_participant') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        }/* Validation - end */ 
        else
        {
            $update_data = array();
            $update_data['ActivityID']      = $data['ActivityID'];
            $update_data['ParticipantID']   = $user_id;
            $update_data['JoiningDate']     = get_current_date('%Y-%m-%d %H:%i:%s');

            $is_valid = $this->contest_model->is_valid_participation($data['ActivityID'],$user_id);

            if($is_valid)
            {
                $this->contest_model->add_participant($update_data);
            }
            else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('invalid_participation');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: mark_participant_as_winner
     * @param ActivityID,Participants[]
     * Description: Marks participants as winner of particular contest
     */
    public function mark_participant_as_winner_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        /* Validation - starts */
        if ($this->form_validation->run('api/contest/mark_participant_as_winner') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        }/* Validation - end */ else
        {
            $update_data = array();
            $contest_id   = $data['ActivityID'];
            $participants = $data['Participants'];

            $this->contest_model->mark_participant_as_winner($contest_id,$participants);
        }
        $this->response($return);
    }

    /**
     * Function Name: delete_contest
     * @param ActivityID
     * Description: Delete contest
     */
    public function delete_contest_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        /* Validation - starts */
        if ($this->form_validation->run('api/contest/delete_contest') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        }/* Validation - end */ else
        {
            $contest_id       = $data['ActivityID'];
            
            $this->contest_model->delete_contest($contest_id);
        }
        $this->response($return);
    }

    /**
     * Function Name: delete_participant
     * @param ActivityID, ParticipantID
     * Description: Delete participant from contest
     */
    public function delete_participant_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        /* Validation - starts */
        if ($this->form_validation->run('api/contest/delete_participant') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        }/* Validation - end */ else
        {
            $contest_id         = $data['ActivityID'];
            $participant_id     = $data['ParticipantID'];
            
            $is_valid = $this->contest_model->is_participating($contest_id,$participant_id);

            if($is_valid)
            {
                $this->contest_model->delete_participant($contest_id,$participant_id);
            }
            else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('invalid_participation');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: delete_participant
     * @param ActivityID, ParticipantID
     * Description: Delete participant from contest
     */
    public function participant_list_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        /* Validation - starts */
        if ($this->form_validation->run('api/contest/get_participant_list') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        }/* Validation - end */ else
        {
            $activity_id = $data['ActivityID'];
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1 ;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : 8 ;
            $return['Data'] = $this->contest_model->get_participant_list($activity_id,$page_no,$page_size);
            $return['TotalRecords'] = $this->contest_model->get_participant_list($activity_id);
        }
        $this->response($return);
    }
}