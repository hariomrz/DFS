<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Events extends Common_API_Controller
{

    // Class Constructor
    function __construct()
    {
        parent::__construct();
        $this->check_module_status(14);
        $this->lang->load('event');
        $this->load->model(array('events/event_model', 'users/friend_model', 'activity/activity_model', 'favourite_model', 'subscribe_model', 'notification_model'));
    }

    /**
     * Function Name: add
     * @param Title
     * @param StartDate
     * @param StartTime
     * @param EndDate
     * @param EndTime
     * @param Venue
     * @param Location
     * @param CategoryID
     * Description: Add new event
     */
    public function add_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        
        $location = isset($data['Location']) ? $data['Location'] :array();
        $locations = isset($data['Locations']) ? $data['Locations'] :array();
        if(!empty($location)) {
           $locations[] =  $location;
        }
                
        if(empty($locations)){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'The Event Location field is required.';
        } else if ($this->form_validation->run('api/events/add') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            // check user email is verified or not
            $this->check_user_status($this->UserID);

            $title      = $data['Title'];
            $start_date = $data['StartDate'];
            $start_time = $data['StartTime'];
            $end_date   = $data['EndDate'];
            $end_time   = $data['EndTime'];
            $venue      = $data['Venue'];
            $category_id = $data['CategoryID'];
            //$locations = $data['Locations'];

            $module_id = 3;$module_entity_id =$user_id;
            if(isset($data['ModuleID']) && !empty($data['ModuleID'])){
                $module_id = $data['ModuleID'];
            }
            
            if(isset($data['ModuleEntityID']) && !empty($data['ModuleEntityID'])){
                $module_entity_id = $data['ModuleEntityID'];
            }
            /*$module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;;
            $module_entity_id = isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : $user_id;*/
            $event_group = isset($data['EventGroup']) ? $data['EventGroup'] : '';
            $description = isset($data['Description']) ? $data['Description'] : '';
            $summary = isset($data['Summary']) ? $data['Summary'] : '';
            $url = isset($data['URL']) ? $data['URL'] : '';

            if(!empty($url)){
                $url = is_valid_url($url);
            }

            $r_rule = isset($data['RRule']) ? $data['RRule'] : '';
            $privacy = !empty($data['Privacy']) ? $data['Privacy'] : 'PUBLIC';
            $archive_days = isset($data['EventArchiveThreshold']) ? $data['EventArchiveThreshold'] : 10;

            $event_archive_threshold = $archive_days;

            if (date("Y-m-d H:i:s", strtotime($start_date . ' ' . $start_time)) >= date("Y-m-d H:i:s", strtotime($end_date . ' ' . $end_time)))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'End time should be greater than start time';
                $this->response($return);
            }

            
            $flag = true;
            if (!empty($event_group))
            {
                $event_group_id = (!empty($event_group['EventGroupId']) ? $event_group['EventGroupId'] : '');
                $is_event_group = (!empty($event_group['IsEventGroup']) ? $event_group['IsEventGroup'] : "");
                if (!empty($is_event_group)) // Check event group status
                {
                    if (empty($event_group['EventGroupGUID']))
                    {
                        // check if event group title already exists
                        $condition = array('title' => $event_group['EventGroupTitle'], 'UserID' => $user_id);
                        $already_exists = $this->event_model->checkEventGroup($condition);
                        if (!$already_exists)
                        {
                            $event_group_data = array('title' => $event_group['EventGroupTitle'], 'EventGroupGUID' => get_guid(), 'UserID' => $user_id);
                            $event_group_id = $this->event_model->insertEventGroup($event_group_data);
                        } else
                        {
                            $flag = false;
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = lang('Event_group_title_already_exists');
                        }
                    } else
                    {
                        $condition = array('EventGroupGUID' => $event_group['EventGroupGUID']);
                        $event_group_id = $this->event_model->checkEventGroup($condition, TRUE, $event_group['EventGroupTitle']);
                        if (empty($event_group_id))
                        {
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = "Invalid Event Group ID!!";
                            $flag = false;
                        }
                    }
                }
            }

            if (!isset($event_group_id))
            {
                $event_group_id = 0;
            }

            if ($flag)
            {
                // prepare Data to insert
                $insert_data = array(
                    'EventGUID' => get_guid(),
                    'EventGroupID' => $event_group_id,
                    'Title' => $title,
                    'ModuleID'=> $module_id,
                    'ModuleEntityID' => $module_entity_id,
                    'IsFullDay' => 1,
                    'StartDate' => $start_date,
                    'StartTime' => $start_time,
                    'EndDate' => $end_date,
                    'EndTime' => $end_time,
                    'EventUrl' => $url,
                    'Venue' => $venue,
                    'Summary' => $summary,
                    'Description' => $description,
                    'Privacy' => empty($privacy) ? 0 : $privacy,
                    'ArchiveThreshold' => $event_archive_threshold,
                    'RRule' => $r_rule,                    
                    'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'LastActivity' => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'CreatedBy' => $user_id
                );
                
                $event_data = $this->event_model->save_event($insert_data, $locations, array($category_id), $user_id);
                if ($event_data)
                {
                    $event_data[0]['ProfileURL'] = $this->event_model->getViewEventUrl($insert_data['EventGUID'], $insert_data['Title'], false, 'about');
                    $return['ResponseCode'] = self::HTTP_OK;
                    $return['Message'] =  sprintf(lang('event_created'), $title);
                    $return['Data'] = array('EventGUID' => $insert_data['EventGUID'], 'EventData' => $event_data);
                } else
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('empty_eventdata');
                }
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: edit
     * @param EventGUID
     * @param Title
     * @param StartDate
     * @param StartTime
     * @param EndDate
     * @param EndTime
     * @param Venue
     * @param Location
     * @param CategoryID
     * Description: Update existing event
     */
    public function edit_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        
        $location = isset($data['Location']) ? $data['Location'] :array();
        $locations = isset($data['Locations']) ? $data['Locations'] :array();
        if(empty($locations)) {
           $locations[] =  $location;
        }
                
        if(empty($locations)){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'The Event Location field is required.';
        } else if ($this->form_validation->run('api/events/edit') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $title = $data['Title'];
            $start_date = $data['StartDate'];
            $start_time = $data['StartTime'];
            $end_date = $data['EndDate'];
            $end_time = $data['EndTime'];
            $venue = $data['Venue'];
            //$locations = $data['Locations'];
            $category_id = $data['CategoryID'];
            $event_guid = $data['EventGUID'];

            $event_group = isset($data['EventGroup']) ? $data['EventGroup'] : '';
            $description = isset($data['Description']) ? $data['Description'] : '';
            $summary = isset($data['Summary']) ? $data['Summary'] : '';
            $url = isset($data['URL']) ? $data['URL'] : '';
            if(!empty($url)){
                $url = is_valid_url($url);
            }
            $r_rule = isset($data['RRule']) ? $data['RRule'] : '';
            $privacy = isset($data['Privacy']) ? $data['Privacy'] : '';
            $archive_days = isset($data['EventArchiveThreshold']) ? $data['EventArchiveThreshold'] : 10;

            $event_archive_threshold = $archive_days;

            if (date("Y-m-d H:i:s", strtotime($start_date . ' ' . $start_time)) >= date("Y-m-d H:i:s", strtotime($end_date . ' ' . $end_time)))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'End time should be greater than start time';
                $this->response($return);
            }

            $flag = true;
            if (!empty($event_group))
            {
                $event_group_id = (!empty($event_group['EventGroupId']) ? $event_group['EventGroupId'] : '');
                $is_event_group = (!empty($event_group['IsEventGroup']) ? $event_group['IsEventGroup'] : "");
                if (!empty($is_event_group)) // Check event group status
                {
                    if (empty($event_group['EventGroupGUID']))
                    {
                        // check if event group title already exists
                        $condition = array('title' => $event_group['EventGroupTitle'], 'UserID' => $user_id);
                        $already_exists = $this->event_model->checkEventGroup($condition);
                        if (!$already_exists)
                        {
                            $event_group_data = array('title' => $event_group['EventGroupTitle'], 'EventGroupGUID' => get_guid(), 'UserID' => $user_id);
                            $event_group_id = $this->event_model->insertEventGroup($event_group_data);
                        } else
                        {
                            $flag = false;
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = lang('Event_group_title_already_exists');
                        }
                    } else
                    {
                        $condition = array('EventGroupGUID' => $event_group['EventGroupGUID']);
                        $event_group_id = $this->event_model->checkEventGroup($condition, TRUE, $event_group['EventGroupTitle']);
                        if (empty($event_group_id))
                        {
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = "Invalid Event Group ID!!";
                            $flag = false;
                        }
                    }
                }
            }

            if (!isset($event_group_id))
            {
                $event_group_id = 0;
            }

            if ($flag)
            {
                // prepare Data to update
                $update_data = array(
                    'EventGroupID' => $event_group_id,
                    'Title' => $title,
                    'IsFullDay' => 1,
                    'StartDate' => $start_date,
                    'StartTime' => $start_time,
                    'EndDate' => $end_date,
                    'EndTime' => $end_time,
                    'Venue' => $venue,
                    'RRule' => $r_rule,
                    'Summary' => $summary,
                    'Description' => $description,
                    'EventUrl' => $url,
                    'Privacy' => $privacy,
                    'ArchiveThreshold' => $event_archive_threshold,
                    'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')
                );

                $update_data['StartTime'] = str_replace('00:', '12:', $update_data['StartTime']);
                $update_data['EndTime'] = str_replace('00:', '12:', $update_data['EndTime']);

                $status = $this->event_model->save_event($update_data, $locations, array($category_id), $user_id, $event_guid);

                if ($status) {                    
                    $return['ResponseCode'] = self::HTTP_OK;
                    $return['Message'] = lang('event_updated');
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('empty_eventdata');
                }
            }
        }
        $this->response($return);
    }

    /**
     * [details_post get event details]
     * @return [json] [event details]
     */
    public function details_post()
    {
        $return = $this->return;
        /* Define variables - ends */
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/event/details') == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        } else
        {
            $event_guid = $data['EventGUID'];
            $is_edit = isset($data['IsEdit']) ? $data['IsEdit'] : 0;
            $result = $this->event_model->details($event_guid, $user_id, $is_edit, $this->UserID);

            $return['Data'] = $result;
            
        }
        $this->response($return);
    }

    /**
     * [list_post get event list]
     * @return [json] [event list]
     */
    public function list_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;

        $page_no        = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
        $page_size      = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
        $event_type     = isset($data['Filter']) ? $data['Filter'] : 'AllPublicEvents';
        $order_by       = isset($data['OrderBy']) ? $data['OrderBy'] : 'EventID';
        $order_type     = isset($data['OrderType']) ? $data['OrderType'] : 'DESC';
        $category_ids   = isset($data['CategoryIDs']) ? $data['CategoryIDs'] : '';
        $start_date     = isset($data['StartDate']) ? $data['StartDate'] : '';
        $end_date       = isset($data['EndDate']) ? $data['EndDate'] : '';
        $city_id        = isset($data['CityID']) ? $data['CityID'] : '';
        $keyword        = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $event_guid     = isset($data['EventGUID']) ? $data['EventGUID'] : '';
        $suggested      = isset($data['Suggested']) ? $data['Suggested'] : '';
        $latitude       = isset($data['Latitude']) ? $data['Latitude'] : '';
        $longitude      = isset($data['Longitude']) ? $data['Longitude'] : '';

        $input = array(
            'UserID' => $user_id,
            'CategoryIDs' => $category_ids,
            'CityID'    => $city_id,
            'EventGUID' => $event_guid,
            'Suggested' => $suggested,
            'Latitude'  => $latitude,
            'Longitude'  => $longitude,
        );

        if (!empty($event_guid))
        {
            $validation_rule[] = array(
                'field' => 'EventGUID',
                'label' => 'EventGUID',
                'rules' => 'required|validate_guid[14]'
            );

            $this->form_validation->set_rules($validation_rule);

            if ($this->form_validation->run() == FALSE) // Check for empty request
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
                $this->response($return);
            } else
            {
                $input['EventID'] = get_detail_by_guid($event_guid, 14); // Get EventID by EventGUID
                unset($input['EventGUID']);
            }
        }

        $input['Filter'] = array('StartDate' => $start_date,'EndDate' => $end_date,'Keyword' => $keyword, 'OrderBy' => $order_by, 'OrderType' => $order_type);

        $return['Data'] = $this->event_model->get_events($input, $user_id, $event_type, false, $page_no, $page_size);
        $return['TotalRecords'] = $this->event_model->get_events($input, $user_id, $event_type, true);

        if($return['TotalRecords'] == 0 && !empty($user_id) && !empty($data['userLocationFiterOn'])){
            $return['Data'] = $this->event_model->get_events_by_user_location($input, $user_id, $event_type, false, $page_no, $page_size);
            $return['TotalRecords'] = $this->event_model->get_events_by_user_location($input, $user_id, $event_type, true);
        }

        $return['PageNo'] = $page_no;
        $return['PageSize'] = $page_size;

        $this->response($return);
    }

    /**
     * [get_event_location_post get event location list]
     * @return [json] [event location list]
     */
    public function get_event_locations_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;

        $event_type     = isset($data['Filter']) ? $data['Filter'] : 'AllPublicEvents';
        $category_ids   = isset($data['CategoryIDs']) ? $data['CategoryIDs'] : '';
        $start_date     = isset($data['StartDate']) ? $data['StartDate'] : '';
        $end_date       = isset($data['EndDate']) ? $data['EndDate'] : '';
        $keyword        = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $event_guid     = isset($data['EventGUID']) ? $data['EventGUID'] : '';
        $suggested      = isset($data['Suggested']) ? $data['Suggested'] : '';
        $latitude       = isset($data['Latitude']) ? $data['Latitude'] : '';
        $longitude      = isset($data['Longitude']) ? $data['Longitude'] : '';

        $input = array(
            'UserID'        => $user_id,
            'CategoryIDs'   => $category_ids,
            'EventGUID'     => $event_guid,
            'Suggested'     => $suggested,
            'Latitude'      => $latitude,
            'Longitude'     => $longitude,
        );

        $input['Filter'] = array('StartDate' => $start_date,'EndDate' => $end_date,'Keyword' => $keyword);
        $return['Data'] = $this->event_model->get_event_locations($input, $user_id, $event_type);
        
        if(empty($return['Data']) && !empty($user_id) && $data['userLocationFiterOn']){
            $return['Data'] = $this->event_model->get_events_location_by_user_location($input, $user_id, $event_type);
        }

        $this->response($return);
    }

    /**
     * [get_event_categories_post get event categories list]
     * @return [json] [event categories list]
     */
    public function get_event_categories_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;

        $event_type     = isset($data['Filter']) ? $data['Filter'] : 'AllPublicEvents';
        $city_id        = isset($data['CityID']) ? $data['CityID'] : '';
        $start_date     = isset($data['StartDate']) ? $data['StartDate'] : '';
        $end_date       = isset($data['EndDate']) ? $data['EndDate'] : '';
        $keyword        = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $event_guid     = isset($data['EventGUID']) ? $data['EventGUID'] : '';
        $suggested      = isset($data['Suggested']) ? $data['Suggested'] : '';
        $latitude       = isset($data['Latitude']) ? $data['Latitude'] : '';
        $longitude      = isset($data['Longitude']) ? $data['Longitude'] : '';

        $input = array(
            'UserID'        => $user_id,
            'CityID'        => $city_id,
            'EventGUID'     => $event_guid,
            'Suggested'     => $suggested,
            'Latitude'      => $latitude,
            'Longitude'     => $longitude,
        );

        $input['Filter'] = array('StartDate' => $start_date,'EndDate' => $end_date,'Keyword' => $keyword);
        $return['Data'] = $this->event_model->get_event_categories($input, $user_id, $event_type);
        if(empty($return['Data']) && !empty($user_id) && $data['userLocationFiterOn']){
            $return['Data'] = $this->event_model->get_events_categories_by_user_location($input, $user_id, $event_type);
        }
        $this->response($return);
    }

    /**
     * [get_module_event get event list wrt module]
     * @return [json] [event list]
     */
    public function get_module_event_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;

        $page_no            = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
        $page_size          = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
        $order_by           = isset($data['OrderBy']) ? $data['OrderBy'] : 'EventID';
        $order_type         = isset($data['OrderType']) ? $data['OrderType'] : 'DESC';
        $keyword            = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $module_id          = isset($data['ModuleID']) ? $data['ModuleID'] : '';
        $module_entity_id   = isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : '';
        $event_type         = isset($data['Filter']) ? $data['Filter'] : 'created';

        $input = array(
            'ModuleID'          => $module_id,
            'ModuleEntityID'    => $module_entity_id
        );
        
        $input['Filter'] = array('Keyword' => $keyword, 'OrderBy' => $order_by, 'OrderType' => $order_type);

        $return['Data'] = $this->event_model->module_event($input, $user_id, $event_type, false, $page_no, $page_size);
        $return['TotalRecords'] = $this->event_model->module_event($input, $user_id, $event_type, true);
        $this->response($return);
    }

    /**
     * [event_owner_detail user to get event qwner detail]
     * @return [json] 
     */
    public function event_owner_detail_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if ($this->form_validation->run('api/events/event_owner_detail') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $return['Data'] = $this->event_model->get_event_owner_detail($data['EventGUID'], $user_id);
            
        }
        $this->response($return);
    }

    /**
     * [event_user_detail user to get event user detail]
     * @return [json] 
     */
    public function event_user_detail_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if ($this->form_validation->run('api/events/event_user_detail') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $event_id   = get_detail_by_guid($data['EventGUID'], 14);
            $page_no    = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size  = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;

            $return['Data']['Status']   = $this->event_model->get_user_event_status($event_id, $user_id);
            $return['TotalRecords']     = 0;
            $return['Data']['Invitees'] = []; 

            if(!empty($return['Data']['Status'])){
                $return['TotalRecords'] = $this->event_model->get_invite_list($event_id,$user_id, TRUE);
                $return['Data']['Invitees'] = $this->event_model->get_invite_list($event_id,$user_id, FALSE,$page_no, $page_size);
            }
        }
        $this->response($return);
    }


    /**
     * [event_attende_list user to get event attende list]
     * @return [json] 
     */
    public function event_attende_list_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if ($this->form_validation->run('api/events/event_attende_list') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $event_details = get_detail_by_guid($data['EventGUID'], 14, 'EventID, Title', 2);
            $event_id = $event_details['EventID'];
            $title = $event_details['Title'];
            $page_size = PAGE_SIZE;
            if(!empty($data['PageSize'])){
                $page_size = $data['PageSize'];
            }
            $return['TotalRecords'] = $this->event_model->event_members($event_id,$user_id, TRUE);
            $return['Data'] = $this->event_model->event_members($event_id,$user_id, FALSE, $page_size);
            $return['EntityMemberURL'] = $this->event_model->getViewEventUrl($data['EventGUID'], $title, false, 'members');
            
        }
        $this->response($return);
    }

    /**
    * [get_similar_event used to get similar event list] 
    * @return [json] 
    */
    public function get_similar_event_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;

        if ($this->form_validation->run('api/events/get_similar_event') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $event_id = get_detail_by_guid($data['EventGUID'], 14);
            $page_no        = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            //$page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : 1;
            $return['TotalRecords'] = $this->event_model->similar_event($event_id,$user_id, TRUE);
            $return['Data'] = $this->event_model->similar_event($event_id,$user_id, FALSE,$page_no, $page_size);
            
        }
        $this->response($return);
    }

    public function get_past_event_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        
        $event_id = isset($data['EventGUID'])?get_detail_by_guid($data['EventGUID'], 14):'';
        $page_no        = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : 10;
        $return['TotalRecords'] = $this->event_model->past_event($event_id,$user_id, TRUE);
        $return['Data'] = $this->event_model->past_event($event_id,$user_id, FALSE,$page_no, $page_size);
            
        $this->response($return);
    }
    /**
     * [friend_suggestion_post et List of friends to make them event member or to Send Invitation]
     * @return [json] [description]
     */
    function friend_suggestion_get()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        $user_id = $this->UserID;

        $search_keyword = $data['SearchKey'];

        $event_guid = $data['EventGUID'];

        //$event_id = get_detail_by_guid($event_guid, 14);
        
        $event_detail = get_detail_by_guid($event_guid, 14,'EventID, Title, ModuleID, ModuleEntityID',2); // Get EventID by EventGUID
        
        if(empty($event_detail['EventID'])) {
            $this->response($return['Data']);
        }

        $return['Data'] = $this->event_model->friend_suggestion($search_keyword, $user_id, $event_detail['EventID'], $event_detail['ModuleID'], $event_detail['ModuleEntityID']);

        $this->response($return['Data']);
    }

    /* 
        --------------------------------------------------------------------------------------------------------------------------------------
      | @Method : To invited users
      | @Param  : Users(array),EventGUID
        -------------------------------------------------------------------------------------------------------------------------------------- 
    */

    Public function InviteEventUsers_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $user_id = $this->UserID; // Get User ID

        $validation_rule = $this->form_validation->_config_rules['api/event/invitemanageevent'];
        $validation_rule[] = array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        );
        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $event_detail = get_detail_by_guid($data['EventGUID'], 14,'EventID, Title, ModuleID, ModuleEntityID',2); // Get EventID by EventGUID
            $event_id     = $event_detail['EventID'];
            $event_title  = $event_detail['Title'];
            
            
            
            // Get group/page members in case of ModuleID = 1, 18
            if(isset($event_detail['ModuleID']) && in_array($event_detail['ModuleID'], [1, 18])) {
                $data['Users'] = $this->get_entity_members($event_detail['ModuleID'], $event_detail['ModuleEntityID'], $data['Users'], $user_id, $event_id);
            }
            
            
            
            $user_str     = '';
            $users = array();
            if (!empty($data['Users']))
            {
                $total_users = count($data['Users']);
                foreach ($data['Users'] as $key => $user)
                {
                    if (!empty($user))
                    {
                        
                        $user_detail = get_detail_by_guid($user['UserGUID'], 3,'UserID,FirstName as name',2);                                                                        
                        $event_user_id  = $user_detail['UserID'];
                        $user_name = $user_detail['name'];

                        if($total_users == 1){
                            $user_str.= $user_name;
                        }else if($total_users > 1){
                            if($key == 0){
                                $user_str.= $user_name;
                            }else if($key == 1){
                                $seprator = ',';
                                if($total_users == 2){
                                    $seprator = 'and';
                                }
                                $user_str.= " $seprator ".$user_name;
                            }else if($key == 2){
                                $remain_user_count = $total_users - 2;
                                $str = ' other';
                                if($remain_user_count > 1){
                                    $str = ' others';
                                }
                                $user_str.= " and $remain_user_count $str";
                            }
                        }

                        if (!empty($event_user_id))
                        {
                            $users[$key] = $user;
                            $users[$key]['UserID'] = $event_user_id;
                        }
                    }
                }
            }

            if (!empty($data['ManageUsers'])) // Code for Manage Users section
            {
                $res = $this->event_model->ManageEventUsers($event_id, $users, $user_id);
                if ($res['status'] == true)
                {
                    $message = lang("event_users_update_success");
                } else
                {
                    $message = lang("users_invalid_data");
                }
            } else // Code for Invite Users
            {
                $res = $this->event_model->addEventInvites($event_id, $users, $user_id);
                if ($res['res_status'] == 1 || $res['res_status'] == 2)
                {
                    //$message = lang("success_invite");
                    $message = sprintf(lang('sucess_invite_event'), $user_str, $event_title);
                } else
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $message = lang("users_invalid_data");
                }
            }
            
            $return['Message'] = $message;
            unset($res['status']);
            unset($res['res_status']);
        }
        $this->response($return);
    }
    
    public function get_entity_members($module_id, $module_entity_id, $selected_members = [], $exclude_user_id = 0, $event_id = 0) {
        $user_id = $this->UserID; // Get User ID
        if($module_id == 18) {
            $this->db->select('U.UserGUID');
            $this->db->from(PAGEMEMBERS . " as PM");
            $this->db->join(USERS . " as U", "U.UserID = PM.UserID");
            $this->db->where('PM.PageID', $module_entity_id);            
        } else {
            $this->db->select('U.UserGUID');
            $this->db->from(GROUPMEMBERS . " as GM");
            $this->db->join(USERS . " as U", "U.UserID = GM.ModuleEntityID AND GM.ModuleID = 3");            
            $this->db->where('GM.GroupID', $module_entity_id);
        }
        
        $this->db->join(EVENTUSERS . " as EU", "U.UserID = EU.UserID AND EU.EventID = $event_id", 'LEFT');
        
        $blockedUsers = $this->activity_model->block_user_list($user_id, 3);
        $blockedUsers[] = $exclude_user_id;
        if ($blockedUsers) {
            $this->db->where_not_in('U.UserID', $blockedUsers);
        }
        
        $this->db->where('EU.UserID IS NULL', NULL, FALSE);
        
        $query = $this->db->get();
        $members = $query->result_array();
        
        $final_members_list = [];
        $final_selected_members_list = [];
        foreach($members as $member) {
            
            $final_members_list[] = array('UserGUID' => $member['UserGUID'], 'ModuleRoleID' => 3);
            
            foreach($selected_members as $selected_member) {
                if($selected_member['UserGUID'] == $member['UserGUID']) {
                   $final_selected_members_list[] = array('UserGUID' => $member['UserGUID'], 'ModuleRoleID' => 3);
                   break;
                }
            }
        }
        
        return (count($final_selected_members_list)) ? $final_selected_members_list : $final_members_list;
        
    }


    /**
    * [get_recent_invites used to get recent invited user list] 
    * @return [json] 
    */
    public function get_recent_invites_post()
    {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;

        if ($this->form_validation->run('api/events/get_similar_event') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $event_id       = get_detail_by_guid($data['EventGUID'], 14);
            $page_no        = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size      = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $return['TotalRecords'] = $this->event_model->recent_invites($event_id,$user_id, TRUE);
            $return['Data'] = $this->event_model->recent_invites($event_id,$user_id, FALSE,$page_no, $page_size);
            
        }
        $this->response($return);
    }

    /**
     * [update_presence_post To Update User's Presence]
     */
    public function update_presence_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $user_id = $this->UserID; // Get User ID

        $validation_rule = $this->form_validation->_config_rules['api/event/update_presence'];
        $validation_rule[] = array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        );
        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $target_presence = $data['TargetPresence'];
            $event_detail = get_detail_by_guid($data['EventGUID'], 14, 'EventID,ModuleID,ModuleEntityID,Title',2); // Get EventID by EventGUID
            
            $event_id = $event_detail['EventID'];
            $module_id = $event_detail['ModuleID'];
            $module_entity_id = $event_detail['ModuleEntityID'];
            $module_title = $event_detail['Title'];

            if($module_id == 1){
                $permission =check_group_permissions($user_id, $module_entity_id);
                if(!$permission['IsMember']){
                    $this->load->model('group/group_model');
                    $members_list = array(array('ModuleEntityID' => $user_id, 'ModuleID' => 3));
                    $this->group_model->add_members($module_entity_id, $members_list, FALSE, 2, '', 3, $user_id, FALSE, TRUE);
                }   
            }else if($module_id == 18){
                $this->load->model("pages/page_model");
                $page_member = $this->page_model->get_page_members_id($module_entity_id);
                if(!in_array($user_id, $page_member)){
                    $data = array('TypeEntityID' => $module_entity_id, 'UserID' => $user_id, 'Type' => 'page');
                    $this->load->model('follow/follow_model');
                    $result = $this->follow_model->follow($data);
                }
            }
            
            $check_status = $this->event_model->check_event_current_status($event_id);

            if (!$check_status)
            {
                /*if ($target_presence == 'ATTENDING' || $target_presence == 'MAY_BE' || $target_presence == 'ARRIVED' || $target_presence == 'ATTENDED' || $target_presence == 'INVITED')
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = 'This event is expired now.';
                    $this->response($return);
                }*/
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'This event is expired now.';
                $this->response($return);
            }

            $old_presence = $this->event_model->get_user_presence($user_id, $event_id);

            
            if (!empty($old_presence))
            {
                $update_invite_status = $this->event_model->update_invite_status($user_id, $event_id, $old_presence, $target_presence);

                switch ($old_presence)
                {
                    case 'INVITED':
                        switch ($target_presence)
                        {
                            case 'INVITED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You are already invited.";
                                break;
                            case 'ATTENDING':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'MAY_BE':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'NOT_ATTENDING':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'DECLINED':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'ARRIVED':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'ATTENDED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                            default:
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                        }
                        break;

                    case 'ATTENDING':
                        switch ($target_presence)
                        {
                            case 'INVITED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already attending this event.";
                                break;
                            case 'ATTENDING':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already attending this event.";
                                $this->response($return);
                                break;
                            case 'MAY_BE':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'NOT_ATTENDING':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'ARRIVED':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'ATTENDED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                            default:
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                        }
                        break;
                    case 'MAY_BE':
                        switch ($target_presence)
                        {
                            case 'INVITED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already set as you may attend this event.";
                                break;
                            case 'ATTENDING':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'MAY_BE':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already set as you may attend this event.";
                                $this->response($return);
                                break;
                            case 'NOT_ATTENDING':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'ARRIVED':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'ATTENDED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                            default:
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                        }
                        break;
                    case 'NOT_ATTENDING':
                        switch ($target_presence)
                        {
                            case 'INVITED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already set as you are not attending this event.";
                                break;
                            case 'ATTENDING':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'MAY_BE':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'NOT_ATTENDING':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already set as you are not attending this event.";
                                break;
                            case 'ARRIVED':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'ATTENDED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                            default:
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                        }
                        break;
                    case 'ARRIVED':
                        switch ($target_presence)
                        {
                            case 'INVITED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already arrived at the event.";
                                break;
                            case 'ATTENDING':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already arrived at the event.";
                                break;
                            case 'MAY_BE':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already arrived at the event.";
                                break;
                            case 'NOT_ATTENDING':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already arrived at the event.";
                                break;
                            case 'ARRIVED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already arrived at the event.";
                                break;
                            case 'ATTENDED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                            default:
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                        }
                        break;
                    case 'ATTENDED':
                        switch ($target_presence)
                        {
                            case 'INVITED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "Event is over.";
                                break;
                            case 'ATTENDING':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "Event is over.";
                                break;
                            case 'MAY_BE':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "Event is over.";
                                break;
                            case 'NOT_ATTENDING':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "Event is over.";
                                break;
                            case 'ARRIVED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "Event is over.";
                                break;
                            case 'ATTENDED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "Event is over.";
                                break;
                            default:
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                        }
                        break;
                    case 'DECLINED':
                        switch ($target_presence)
                        {
                            case 'INVITED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already set as you are not attending this event.";
                                break;
                            case 'ATTENDING':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'MAY_BE':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'NOT_ATTENDING':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You already set as you are not attending this event.";
                                break;
                            case 'ARRIVED':
                                $status = $this->event_model->update_presence($user_id, $event_id, $target_presence);
                                break;
                            case 'ATTENDED':
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                            default:
                                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                $return['Message'] = "You can not do this action.";
                                break;
                        }
                        break;
                    default:
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = "You can not do this action.";
                        break;
                }
            } else
            {
                switch ($target_presence)
                {
                    case 'INVITED':
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = "Self invite not possible here.";
                        break;
                    case 'ATTENDING':
                        $status = $this->event_model->update_presence($user_id, $event_id, $target_presence, true);
                        break;
                    case 'MAY_BE':
                        $status = $this->event_model->update_presence($user_id, $event_id, $target_presence, true);
                        break;
                    case 'NOT_ATTENDING':
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = "You can not do this action.";
                        break;
                    case 'ARRIVED':
                        $status = $this->event_model->update_presence($user_id, $event_id, $target_presence, true);
                        break;
                    case 'ATTENDED':
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = "You can not do this action.";
                        break;
                    default:
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = "You can not do this action.";
                        break;
                }
            }

            if (!empty($status))
            {
                $return['ResponseCode'] = self::HTTP_OK;
                //$return['Message'] = lang("event_users_update_success");
                if($target_presence == 'ATTENDING'){
                    $return['Message'] = sprintf(lang("sucess_attend_status"),$module_title);
                }else if($target_presence == 'NOT_ATTENDING'){
                    $return['Message'] = sprintf(lang("sucess_not_attending_status"),$module_title);
                }else if($target_presence == 'DECLINED'){
                    $return['Message'] = sprintf(lang("sucess_decline_status"),$module_title);
                }else{
                    $return['Message'] = lang("event_users_update_success");
                }
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang("users_invalid_data");
            }
        }
        $this->response($return);
    }

    /*****************************************************OLD CODE*************************************************************************/
    

    public function events_near_you_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;

        $lat = isset($data['Lat']) ? $data['Lat'] : '' ;
        $lng = isset($data['Lng']) ? $data['Lng'] : '' ;

        $return['Data'] = $this->event_model->event_near_you($lat,$lng,$user_id);
        $this->response($return);
    }

    public function invited_events_list_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;

        $return['Data'] = $this->event_model->invited_events_list($user_id);

        $this->response($return);
    }

    /**
     * Function Name: invite_users
     * Description: Add single/multiple member to a group
     */
    function invite_users_post()
    {

        $return = $this->return;

        $data = $this->post_data;

        if ($this->form_validation->run('api/event/invite_users') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else
        {
            $users_guid = $data['UsersGUID'];

            if (!is_array($users_guid))
            {
                $users_guid = json_decode($users_guid);
            }
            $current_user_id = $this->UserID;
            $event_guid = $data['EventGUID'];
            $event_id = get_detail_by_guid($event_guid, 14);
            $permission = checkPermission($current_user_id, 14, $event_id, 'IsAccess');

            if ($permission['IsOwner'])
            {
                foreach ($users_guid as $user_guid)
                {
                    $user_id = get_detail_by_guid($user_guid, 3);
                    if ($user_id)
                    {
                        $event_user = $this->event_model->check_member($event_id, $user_id, TRUE);
                        $is_deleted = 0;
                        $is_exit = FALSE;
                        $update_flag = FALSE;
                        if ($event_user)
                        {
                            $is_exit = TRUE;
                            $is_deleted = $event_user['IsDeleted'];
                            if ($is_deleted == 1)
                            {
                                $is_exit = FALSE;
                                $update_flag = TRUE;
                            }
                        }
                        if (!$is_exit)
                        {
                            $this->event_model->invite_users($event_id, $user_id, 3, $current_user_id, "INVITED", $update_flag);
                        }
                        $return['Message'] = lang('success_invite');
                    }
                }
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * [delete_post To Delete Event by EventGUID]
     * @return [json] [description]
     */
    public function delete_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $user_id = $this->UserID; // Get User ID

        $validation_rule[] = array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        );
        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $is_delete = isset($data['IsDeleted']) ? $data['IsDeleted'] : 1;
            $event_id = get_detail_by_guid($data['EventGUID'], 14);
            $permission = checkPermission($user_id, 14, $event_id, 'IsAccess');
            if ($permission['IsOwner'])
            {
                $status = $this->event_model->delete($event_id, $is_delete, $user_id);
                $return['ResponseCode'] = self::HTTP_OK;
                $return['Message'] = lang('event_deleted');
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * [members_post To get event users]
     * @return [json] [description]
     */
    public function members_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $user_id = $this->UserID;
        $validation_rule[] = array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        );

        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $event_id = get_detail_by_guid($data['EventGUID'], 14);

            $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $filter = isset($data['Filter']) ? $data['Filter'] : '';
            $search_keyword = (!empty($data['SearchKeyword']) ? $data['SearchKeyword'] : '');

            $return['TotalRecords'] = $this->event_model->members($event_id, $user_id, TRUE, $search_keyword, '', '', $filter,TRUE);
            if (!empty($return['TotalRecords']))
            {
                if($filter == 'Admin'){
                    $return['Data'] = $this->event_model->members($event_id, $user_id, '', $search_keyword, '', '', $filter,TRUE);
                    $return['TotalFriendsRecords'] = 0;
                }else{
                    $return['Data'] = $this->event_model->members($event_id, $user_id, '', $search_keyword, $page_no, $page_size, $filter,TRUE);
                    if(!empty($user_id)){
                        $filter = 'FriendCount';
                        $return['TotalFriendsRecords'] = $this->event_model->members($event_id, $user_id, TRUE, $search_keyword, '', '', $filter,TRUE);
                    }else{
                        $return['TotalFriendsRecords'] = 0;
                    }
                }
            } else
            {
                $return['Message'] = "No Record Found !";
                $return['TotalRecords'] = 0;
                $return['ResponseCode'] = self::HTTP_OK;
            }
        }
        $this->response($return);
    }

    /**
     * [get_invitees_list To get event invity users list]
     * @return [json] [description]
     */
    public function get_invitees_list_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $user_id = $this->UserID;
        $validation_rule[] = array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        );

        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $event_id       = get_detail_by_guid($data['EventGUID'], 14);
            $page_no        = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size      = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $search_keyword = (!empty($data['SearchKeyword']) ? $data['SearchKeyword'] : '');
            $return['TotalRecords'] = $this->event_model->invitees_list($event_id, $user_id, TRUE, $search_keyword, '', '');
            if (!empty($return['TotalRecords']))
            {
                $return['Data'] = $this->event_model->invitees_list($event_id, $user_id, '', $search_keyword, $page_no, $page_size);
            } else
            {
                $return['Message'] = "No Record Found !";
                $return['TotalRecords'] = 0;
                $return['ResponseCode'] = self::HTTP_OK;
            }
        }
        $this->response($return);
    }

    /**
     * [toggle_user_role_post Event admin/creator can assign/remove role to event member]
     */
    function toggle_user_role_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $role_action = (!empty($data['RoleAction']) ? ucfirst($data['RoleAction']) : '');

        $role_id = (!empty($data['RoleID']) ? $data['RoleID'] : "");

        if ($this->form_validation->run('api/event/toggle_user_role') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } elseif ($role_action == 'Add' && $role_id == '')
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'RoleID is required.';
        } else
        {
            $entity_guid = $data['EntityGUID'];
            $module_id = $data['ModuleID'];
            $module_entity_guid = $data['ModuleEntityGUID'];
            //$can_post_on_wall 	= $data['CanPostOnWall'];

            $current_user_id = $this->UserID;
            $event_id = get_detail_by_guid($module_entity_guid, $module_id);
            $user_id = get_detail_by_guid($entity_guid, 3);

            $permission = checkPermission($current_user_id, $module_id, $event_id, 'IsAccess');
            if ($permission['IsOwner'])
            {
                $user_role_id = $this->event_model->get_user_event_role($user_id, $event_id);
                if ($user_role_id == $role_id && $role_action == 'Add')
                {
                    $return['ResponseCode'] = self::HTTP_OK;
                    $return['Message'] = lang('role_already_assigned');
                } else
                {
                    $permission = checkPermission($user_id, $module_id, $event_id, 'IsAccess');

                    if ($permission['IsMember'])
                    {
                        $data = array('EventID' => $event_id, 'UserID' => $user_id, 'RoleID' => $role_id, 'RoleAction' => $role_action);
                        $result = $this->event_model->toggle_user_role($data, $current_user_id);
                        $return['Message'] = $result['Message'];

                        $status_id = 2;
                        if ($role_action == 'Remove')
                        {
                            $status_id = 3;
                        }
                        $this->load->model('subscribe_model');
                        $this->subscribe_model->update_subscription($user_id, 'EVENT', $event_id, $status_id);
                    } else
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('user_not_exists');
                    }
                }
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * [can_post_on_wall_post Owner/Creator/Admin of any module can remove post on wall permission]
     * @return [JSON] [description]
     */
    function can_post_on_wall_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if ($this->form_validation->run('api/event/can_post_on_wall') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else
        {
            $entity_guid = $data['EntityGUID'];
            $module_id = $data['ModuleID'];
            $module_entity_guid = $data['ModuleEntityGUID'];
            $can_post_on_wall = $data['CanPostOnWall'];

            $current_user_id = $this->UserID;
            $event_id = get_detail_by_guid($module_entity_guid, $module_id);
            $user_detail = get_detail_by_guid($entity_guid, 3,'UserID,Group_Concat(FirstName," ",LastName) as name',2);
            $user_id  = $user_detail['UserID'];
            $user_name = $user_detail['name'];

            $permission = checkPermission($current_user_id, $module_id, $event_id, 'IsAccess');

            if ($permission['IsOwner'])
            {
                $permission = checkPermission($user_id, $module_id, $event_id, 'IsAccess');
                if ($permission['IsMember'])
                {
                    $this->event_model->toggle_can_post_on_wall($event_id, $user_id, $can_post_on_wall);
                    if($can_post_on_wall){
                        $return['Message'] = sprintf(lang('can_add_post'), $user_name);
                    }else{
                        $return['Message'] = sprintf(lang('can_not_add_post'), $user_name);
                    }
                } else
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('user_not_exists');
                }
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * [leave_post allows user to leave a event]
     * @return [json] [description]
     */
    function leave_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if ($this->form_validation->run('api/event/leave') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else
        {
            $event_guid = $data['EventGUID'];
            $user_guid = $data['UserGUID'];
            $event_id = get_detail_by_guid($event_guid, 14);
            $user_detail = get_detail_by_guid($user_guid, 3,'UserID,Group_Concat(FirstName," ",LastName) as name',2);
            $user_id  = $user_detail['UserID'];
            $user_name = $user_detail['name'];

            // to check if user joined this event
            $member_role = $this->event_model->get_user_event_role($user_id, $event_id);
            if ($member_role)
            {
                $data = array('EventID' => $event_id, 'UserID' => $user_id, 'memberEventRole' => $member_role);
                $result = $this->event_model->leave($data);
                //$return['Message'] = $result['Message'];
                $return['Message'] = sprintf($result['Message'], $user_name);

                $status_id = 3;
                $this->load->model('subscribe_model');
                $this->subscribe_model->update_subscription($user_id, 'EVENT', $event_id, $status_id);
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'Record not found';
            }
        }
        $this->response($return);
    }


    /* --------------------------------------------------------------------------------------------------------------------------------------
      | @Method - Add New Event
      | @Params - EventGroup(JSON),Title(String),CategoryGUID(Int),Description(String),URL(String),StartDate(Datetime).
      | @Params - StartTime(Time),EndDate(Time),EndTime(Time),Venue(String),Location(JSON),RRule(),Privacy(String),EventArchiveThreshold(Int).
      | @Output - eventGUID(Int),Final Output(JSON)
      -------------------------------------------------------------------------------------------------------------------------------------- */

    public function SaveEvent_post()
    {
        $Return = $this->return;
        $data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get post data
        $validation_rule = $this->form_validation->_config_rules['api/event/saveEvent'];
        if (!empty($data['EventGUID']))
        {
            $validation_rule[] = array(
                'field' => 'EventGUID',
                'label' => 'EventGUID',
                'rules' => 'required|validate_guid[14]'
            );
        }
        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else
        {
            if (isset($data['EventGroup']))
                $EventGroup = $data['EventGroup'];
            else
                $EventGroup = '';
            if (isset($data['Title']))
                $Title = $data['Title'];
            else
                $Title = '';
            if (isset($data['CategoryID']))
                $CategoryID = array($data['CategoryID']);
            else
                $CategoryID = array();
            if (isset($data['Description']))
                $Description = $data['Description'];
            else
                $Description = '';
            if (isset($data['URL']))
                $EventUrl = $data['URL'];
            else
                $EventUrl = '';
            if (isset($data['StartDate']))
                $StartDate = $data['StartDate'];
            else
                $StartDate = '';
            if (isset($data['StartTime']))
                $StartTime = date("H:i:s", strtotime($data['StartTime']));
            else
                $StartTime = '';
            if (isset($data['EndDate']))
                $EndDate = $data['EndDate'];
            else
                $EndDate = '';
            if (isset($data['EndTime']))
                $EndTime = date("H:i:s", strtotime($data['EndTime']));
            else
                $EndTime = '';
            if (isset($data['Venue']))
                $Venue = $data['Venue'];
            else
                $Venue = '';
            if (isset($data['Location']))
                $Location = $data['Location'];
            else
                $Location = '';
            if (isset($data['RRule']))
                $RRule = $data['RRule'];
            else
                $RRule = '';
            if (isset($data['Privacy']))
                $Privacy = $data['Privacy'];
            else
                $Privacy = 'PUBLIC';

            if (isset($data['EventArchiveThreshold']))
                $ArchiveDays = $data['EventArchiveThreshold'];
            else
                $ArchiveDays = '10';

            // Set date +10 days from current date
            $EventArchiveThreshold = date("Y-m-d h:i:s", strtotime("+" . $ArchiveDays . " days", strtotime(get_current_date('%Y-%m-%d %H:%i:%s'))));
            if (isset($data['EventGroup']))
                $EventGroup = $data['EventGroup'];
            else
                $EventGroup = '';
            $flag = true;
            if (!empty($EventGroup))
            {
                $EventGroupId = (!empty($EventGroup['EventGroupId']) ? $EventGroup['EventGroupId'] : '');
                $IsEventGroup = (!empty($EventGroup['IsEventGroup']) ? $EventGroup['IsEventGroup'] : "");
                if (!empty($IsEventGroup)) // Check event group status
                {
                    if (empty($EventGroup['EventGroupGUID']))
                    {
                        // check if event group title already exists
                        $condition = array('title' => $EventGroup['EventGroupTitle'], 'UserID' => $this->UserID);
                        $alreadyExists = $this->event_model->checkEventGroup($condition);
                        if (!$alreadyExists)
                        {
                            $EventGroupData = array('title' => $EventGroup['EventGroupTitle'], 'EventGroupGUID' => get_guid(), 'UserID' => $this->UserID);
                            $EventGroupId = $this->event_model->insertEventGroup($EventGroupData);
                        } else
                        {
                            $flag = false;
                            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $Return['Message'] = lang('Event_group_title_already_exists');
                        }
                    } else
                    {
                        $condition = array('EventGroupGUID' => $EventGroup['EventGroupGUID']);
                        $EventGroupId = $this->event_model->checkEventGroup($condition, TRUE, $EventGroup['EventGroupTitle']);
                        if (empty($EventGroupId))
                        {
                            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $Return['Message'] = "Invalid Event Group ID!!";
                            $flag = false;
                        }
                    }
                }
            }

            // Insert Event Data if Event Group ID exists
            $EventGroupId = (!empty($EventGroupId) ? $EventGroupId : 0);

            if (!empty($data['EventGUID']) && $flag == true)
            {
                // prepare Data to update
                $updateData = array(
                    'EventGroupID' => $EventGroupId,
                    'Title' => $Title, 'IsFullDay' => 1, 'StartDate' => $StartDate,
                    'StartTime' => $StartTime, 'EndDate' => $EndDate, 'EndTime' => $EndTime,
                    'Venue' => $Venue, 'RRule' => $RRule, 'Description' => $Description,
                    'EventUrl' => $EventUrl,
                    'Privacy' => $Privacy, 'ArchiveThreshold' => $EventArchiveThreshold,
                    'RRule' => $RRule,
                    'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));

                $status = $this->event_model->saveEvent($updateData, $Location, $CategoryID, $UserID, $data['EventGUID']);

                if ($status)
                {
                    $Return['ResponseCode'] = self::HTTP_OK;
                    $Return['Message'] = lang('event_updated');
                    $Return['ServiceName'] = 'event/SaveEvent';
                } else
                {
                    $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $Return['Message'] = lang('empty_eventdata');
                }
            } else if ($flag)
            {
                // prepare Data to insert
                $insertData = array(
                    'EventGUID' => get_guid(), 'EventGroupID' => $EventGroupId,
                    'Title' => $Title, 'IsFullDay' => 1, 'StartDate' => $StartDate,
                    'StartTime' => $StartTime, 'EndDate' => $EndDate, 'EndTime' => $EndTime,
                    'EventUrl' => $EventUrl,
                    'Venue' => $Venue, 'RRule' => $RRule, 'Description' => $Description,
                    'Privacy' => $Privacy, 'ArchiveThreshold' => $EventArchiveThreshold,
                    'RRule' => $RRule,
                    'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'LastActivity' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedBy' => $UserID);
                $EventData = $this->event_model->saveEvent($insertData, $data['Location'], $CategoryID, $UserID);
                if ($EventData)
                {
                    $Return['ResponseCode'] = self::HTTP_OK;
                    $Return['Message'] = lang('event_created');
                    $Return['ServiceName'] = 'event/SaveEvent';
                    $Return['Data'] = array('EventGUID' => $insertData['EventGUID'], 'EventData' => $EventData);
                } else
                {
                    $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $Return['Message'] = lang('empty_eventdata');
                }
            }
        }
        $this->response($Return);  // Final Output 
    }

    /* --------------------------------------------------------------------------------------------------------------------------------------
      | @Method - Event's List
      | @Output - array
      -------------------------------------------------------------------------------------------------------------------------------------- */

    Public function eventList_post()
    {
        $Return = $this->return;
        $Return['TotalRecords'] = 0;
        /* Define variables - ends */
        /* Gather Inputs - starts */
        $Data = $this->post_data;
        if ($Data != NULL && isset($Data))
        {
            if (isset($Data['OrderBy']))
                $OrderBy = $Data['OrderBy'];
            else
                $OrderBy = '';
            if (isset($Data['OrderType']))
                $OrderType = $Data['OrderType'];
            else
                $OrderType = 'ASC';
            if (isset($Data['Keyword']))
                $Keyword = $Data['Keyword'];
            else
                $Keyword = '';
            if (isset($Data['SortBy']))
                $SortBy = $Data['SortBy'];
            else
                $SortBy = 'DESC';
            if (isset($Data['Status']))
                $Status = $Data['Status'];
            else
                $Status = '';
            if (isset($Data['PageType']))
                $PageType = $Data['PageType'];
            else
                $PageType = 'dashboard';
            if (isset($Data[AUTH_KEY]))
                $LoginSessionKey = $Data[AUTH_KEY];
            else
                $LoginSessionKey = '';
            if (isset($Data['Filter']))
                $Filter = $Data['Filter'];
            else
                $Filter = '';
            if (isset($Data['EventType']))
                $EventType = $Data['EventType'];
            else
                $EventType = 'HOST';
            if (isset($Data['Suggested']))
                $Suggested = $Data['Suggested'];
            else
                $Suggested = '';
            $UserID = $this->UserID;

            $Input = array('OrderBy' => 'EventID', 'SortBy' => 'DESC', 'Status' => $Status, 'UserID' => $UserID, 'PageType' => $PageType, 'Filter' => $Filter, 'Suggested' => $Suggested);

            $PageNo = PAGE_NO;
            $PageSize = 2;

            if (isset($Data['PageNo']))
            {
                $PageNo = $Data['PageNo'];
            }

            if (isset($Data['PageSize']))
            {
                $PageSize = $Data['PageSize'];
            }

            $Input['Filter'] = array('Keyword' => $Keyword, 'OrderBy' => $OrderBy, 'OrderType' => $OrderType);
            $Input['EventGUID'] = (!empty($Data['EventGUID']) ? $Data['EventGUID'] : '');
            if (!empty($Input['EventGUID']))
            {
                $validation_rule[] = array(
                    'field' => 'EventGUID',
                    'label' => 'EventGUID',
                    'rules' => 'required|validate_guid[14]'
                );

                $this->form_validation->set_rules($validation_rule);

                if ($this->form_validation->run() == FALSE) // Check for empty request
                {
                    $error = $this->form_validation->rest_first_error_string();
                    $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $Return['Message'] = $error;
                    $this->response($Return);
                } else
                {
                    $Input['EventID'] = get_detail_by_guid($Input['EventGUID'], 14); // Get EventID by EventGUID
                    unset($Input['EventGUID']);
                }
            }

            $R1 = $this->event_model->get_events($Input, $UserID, $EventType, false, $PageNo, $PageSize);
            $TotalRecords = $this->event_model->get_events($Input, $UserID, $EventType, true);
            $Return['Data'] = $R1;
            $Return['TotalRecords'] = $TotalRecords;
            $Return['PageNo'] = $PageNo;
            $Return['PageSize'] = $PageSize;
            $Return['ResponseCode'] = self::HTTP_OK;
        } else
        {
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = lang('input_invalid_format');
        }

        $this->response($Return);
    }

    /* --------------------------------------------------------------------------------------------------------------------------------------
      | @Method : To Manage Users
      | @Param  : Users(array),EventGUID
      -------------------------------------------------------------------------------------------------------------------------------------- */

    Public function ManageEventUsers_post()
    {
        $Return = $this->return;
        $data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get User ID
        $validation_rule = $this->form_validation->_config_rules['api/event/invitemanageevent'];
        $validation_rule[] = array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        );
        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else
        {
            $EventID = get_detail_by_guid($data['EventGUID'], 14); // Get EventID by EventGUID
            $Users = array();
            if (!empty($data['Users']))
            {
                foreach ($data['Users'] as $key => $User)
                {
                    if (!empty($User))
                    {
                        $EventUserID = get_detail_by_guid($User['UserGUID'], 3);
                        if (!empty($EventUserID))
                        {
                            $Users[$key] = $User;
                            $Users[$key]['UserID'] = $EventUserID;
                        }
                    }
                }
            }

            if (!empty($Users)) // Code for Manage Users section
            {
                $res = $this->event_model->ManageEventUsers($EventID, $Users, $UserID);
                if ($res['status'] == true)
                {
                    $Message = lang("event_users_update_success");
                } else
                {
                    $Message = lang("users_invalid_data");
                }

                if ($res['status'] == true)
                {
                    unset($res['status']);
                    unset($res['res_status']);

                    $Return['ResponseCode'] = self::HTTP_OK;
                    $Return['Message'] = $Message;

                    if (!empty($res)) // Return data if exists
                    {
                        $Return['data'] = $res;
                    }
                } else
                {
                    unset($res['res_status']);
                    unset($res['status']);
                    $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $Return['Message'] = $Message;
                    if (!empty($res)) // Return data if exists
                    {
                        $Return['data'] = $res;
                    }
                }
            } else
            {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang("users_invalid_data");
            }
        }
        $this->response($Return);
    }

    public function GetActiveEventUsers_post()
    {
        $return = $this->return;
        $Data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get User ID

        $validation_rule[] = array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        );

        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $EventID = get_detail_by_guid($Data['EventGUID'], 14);

            $PageNo = PAGE_NO;
            $PageSize = self::HTTP_OK;
            if (isset($Data['PageNo']) && $Data['PageNo'] != '')
            {
                $PageNo = $Data['PageNo'];
            }
            if (isset($Data['PageSize']) && $Data['PageSize'] != '')
            {
                $PageSize = $Data['PageSize'];
            }

            $KeyWord = (!empty($Data['KeyWord']) ? $Data['KeyWord'] : '');
            $return['Data'] = $this->event_model->GetActiveEventUsers($EventID, '', $KeyWord, $PageNo, $PageSize);
            if (!empty($return['Data']))
            {
                $return['TotalRecords'] = $this->event_model->GetActiveEventUsers($EventID, 1, $KeyWord);
                $return['ResponseCode'] = self::HTTP_OK;
                $return['PageNo'] = $PageNo;
                $return['PageSize'] = $PageSize;
            } else
            {
                $return['Message'] = "No Record Found !";
                $return['TotalRecords'] = 0;
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['PageNo'] = $PageNo;
                $return['PageSize'] = $PageSize;
            }
        }
        $this->response($return);
    }

    /* -------------------------------------------------
      | @Method - To get event members count
      | @Params - EventGUID(String)
      | @Output - array
      -------------------------------------------------- */

    public function getEventMemberCount_post()
    {
        $return = $this->return;

        $Data = $this->post_data; // Get post data

        $validation_rule[] = array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        );

        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $EventID = get_detail_by_guid($Data['EventGUID'], 14);
            $Presence = $this->event_model->getEventMemberCount($EventID);
            $return['Data'] = $Presence;
            $return['ResponseCode'] = self::HTTP_OK;
        }
        return $this->response($return);
    }

    /* -----------------------------------------------------
      | @Method - get user's presence for a particular event
      | @Params - EventGUID(String)
      | @Output - array
      ----------------------------------------------------- */

    public function GetUsersPresence_post()
    {
        $return = $this->return;
        $UserID = $this->UserID; // Get User ID
        $Data = $this->post_data; // Get post data
        $validation_rule[] = array(
            'field' => 'EventGUID',
            'label' => 'EventGUID',
            'rules' => 'required|validate_guid[14]'
        );

        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $EventID = get_detail_by_guid($Data['EventGUID'], 14); // Get EventID by EventGUID
            $Presence = $this->event_model->get_user_presence($UserID, $EventID);
            $Presence = getPresenceFromConfig($Presence);
            $EventRole = $this->event_model->get_user_event_role($UserID, $EventID);
            $return['Data'] = array('Presence' => $Presence, "EventRole" => $EventRole);
            $return['ResponseCode'] = self::HTTP_OK;
        }
        return $this->response($return);
    }    

    /* -------------------------------------------------
      | @Method - To add event media
      | @Params - EventGUID(String),MediaGUID(String),TargetModule(String)
      | @Output - Boolean
      -------------------------------------------------- */

    public function AddEventMedia_post()
    {
        $return = $this->return;
        $Data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get User ID
        $validation_rule = $this->form_validation->_config_rules['api/event/addedeleteventmedia'];
        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $status = $this->event_model->AddEventMedia($Data);
            $return['ResponseCode'] = self::HTTP_OK;
            $return['Message'] = lang('add_media_success');
        }
        $this->response($return);
    }

    /* -------------------------------------------------
      | @Method - To delete media
      | @Params - MediaGUID(String),MediaSectionID(Int)
      | @Output - Boolean
      -------------------------------------------------- */

    public function DeleteMedia_post()
    {
        $return = $this->return;
        $Data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get User ID

        $validation_rule = $this->form_validation->_config_rules['api/event/addedeleteventmedia'];

        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $status = $this->event_model->DeleteMedia($Data);
            $return['ResponseCode'] = self::HTTP_OK;
            $return['Message'] = lang('delete_media_success');
        }
        $this->response($return);
    }

    public function upcoming_events_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $current_user = $this->UserID; // Get User ID

        $user_id = isset($data['UserGUID']) ? get_detail_by_guid($data['UserGUID'],3) : $current_user ;

        $return['Data'] = $this->event_model->get_upcoming_events($user_id,$current_user);
        
        $this->response($return);
    }
    
    /**
     * [places, get event places list]
     */
    public function places_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'GUID',
                    'label' => 'Event GUID',
                    'rules' => 'trim|required|validate_guid[14]'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $event_guid = $data['GUID'];                                
                $event_id = get_detail_by_guid($event_guid, 14);
                $return['Data'] = $this->event_model->get_locations($event_id, FALSE);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

}

/* End of file events.php */
/* Location: ./application/controllers/api/events.php */
