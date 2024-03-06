<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * This Class used as REST API for Rule module 
 * @category     Controller
 * @author       Vinfotech Team
 */

class Rules extends Admin_API_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(array('admin/login_model', 'admin/rules_model', 'users/user_model'));

        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
    }

    /**
     * Function for show configuration setting listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function add_rule_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        // If no data posted
        if (!isset($data) || !$data) {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        // Check if user is super admin 
        if (!$this->user_model->is_super_admin($this->UserID)) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('permission_denied');
            $this->response($return);
        }
        $activity_rule_id = isset($data['ActivityRuleID']) ? $data['ActivityRuleID'] : '';
        $name = isset($data['Name']) ? $data['Name'] : '';
        $is_unique_name = '';

        $get_rule_data = get_data('Name, IsEditable', DEFAULTACTIVITYRULE, array('ActivityRuleID' => $activity_rule_id), '1', '');

        if (isset($get_rule_data->Name) && $get_rule_data->Name != $name) {
            $is_unique_name = '|is_unique[' . DEFAULTACTIVITYRULE . '.Name]';
        }

        //If rule is not editable
        if (isset($get_rule_data->IsEditable) && !$get_rule_data->IsEditable) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'Rule is not editable.';
            $this->response($return);
        }

        $validation_rule[] = array(
            'field' => 'Name',
            'label' => 'Name',
            'rules' => 'trim|required|min_length[2]|max_length[100]' . $is_unique_name,
            'errors' => array('is_unique' => 'A rule with this name already exists.'),
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }

        // Populate data
        $this->load->helper('location');
        $location = isset($data['Location']) ? $data['Location'] : array();
        $registration_from_date = isset($data['RegistrationFromDate']) ? $data['RegistrationFromDate'] : '';
        $registration_to_date = isset($data['RegistrationToDate']) ? $data['RegistrationToDate'] : '';
        $gender = isset($data['Gender']) ? $data['Gender'] : 0;
        $age_group_id = isset($data['AgeGroupID']) ? $data['AgeGroupID'] : 0;
        $interset_ids = isset($data['InterestIDs']) ? (($data['InterestIDs'] == '') ? NULL : $data['InterestIDs']) : NULL;
        $specific_users = isset($data['SpecificUser']) ? $data['SpecificUser'] : array();
        $user_ids = array();
        $tag_ids = array();
        if (!empty($specific_users)) {
            foreach ($specific_users as $specific_user) {
                if (isset($specific_user['EntityType']) && $specific_user['EntityType'] == 'Tag') {
                    $tag_ids[] = $specific_user['EntityID'];
                }
                else
                {
                    $user_ids[] = $specific_user['UserID'];
                }
            }
        }

        $city_ids = array();
        if (!empty($location)) {
            foreach ($location as $item) {
                $LocationData = update_location($item);
                $city_ids[] = $LocationData['CityID'];
            }
        }

        // Transform values
        $user_ids = (!empty($user_ids)) ? implode(',', $user_ids) : NULL;
        $tag_ids = (!empty($tag_ids)) ? implode(',', $tag_ids) : NULL;
        $city_ids = (!empty($city_ids)) ? implode(',', $city_ids) : NULL;
        $interset_ids = (!empty($interset_ids)) ? implode(',', $interset_ids) : NULL;


        // Check rule exists
        if ($city_ids == NULL && $registration_from_date == '' && $registration_to_date == '' && $gender == 0 && $age_group_id == 0 && $interset_ids == NULL && $user_ids == NULL && $tag_ids == NULL) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'This rule already exists';
            $this->response($return);
        }

        // Add new rule
        $rule_id = $this->rules_model->add_rule($name, $city_ids, $registration_from_date, $registration_to_date, $gender, $age_group_id, $interset_ids, $user_ids, $tag_ids, $location, $specific_users, $activity_rule_id);

        $return['Data']['ActivityRuleID'] = $rule_id;

        $this->response($return);
    }

    /**
     * [get_rule_post used to get rule list]
     */
    function get_rule_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'DisplayOrder';
        $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'ASC';
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : '';
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : '';

        $temp_data = $this->rules_model->get_rule($sort_by, $order_by, $page_no, $page_size);
        $return['Data'] = $temp_data['Data'];
        $return['TotalRecords'] = $temp_data['TotalRecords'];

        $this->response($return);
    }

    /**
     * [delete_post used to delete rule]
     */
    function delete_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if (isset($data) && $data != NULL) {
            $activity_rule_id = isset($data['ActivityRuleID']) ? $data['ActivityRuleID'] : '';
            if (!$this->user_model->is_super_admin($this->UserID) || $activity_rule_id == 1) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            $validation_rule[] = array(
                'field' => 'ActivityRuleID',
                'label' => 'ActivityRuleID',
                'rules' => 'trim|required',
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {

                $this->db->select('DAR.IsEditable');
                $this->db->from(DEFAULTACTIVITYRULE . ' as DAR');
                $this->db->where('DAR.ActivityRuleID', $activity_rule_id);
                $query = $this->db->get();
                $row = $query->row_array();

                if (isset($row['IsEditable']) && !$row['IsEditable']) {
                    $error = $this->form_validation->rest_first_error_string();
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = $error;
                    $this->response($return);
                }

                $this->db->where('ActivityRuleID', $activity_rule_id);
                $this->db->update(DEFAULTACTIVITYRULE, array('StatusID' => 3));
            }
        } else {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [rule_welcome_post used to set welcome message for activity rule.]
     */
    function rule_welcome_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if (isset($data) && $data != NULL) {
            if (!$this->user_model->is_super_admin($this->UserID)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            $welcome = isset($data['Welcome']) ? $data['Welcome'] : '';

            $validation_rule[] = array(
                'field' => 'Welcome',
                'label' => 'Welcome',
                'rules' => 'trim|required|max_length[500]',
            );
            $validation_rule[] = array(
                'field' => 'ActivityRuleID',
                'label' => 'ActivityRuleID',
                'rules' => 'trim|required',
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $activity_rule_id = isset($data['ActivityRuleID']) ? $data['ActivityRuleID'] : '';
                $this->rules_model->rule_welcome($activity_rule_id, $welcome);
            }
        } else {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }


    /**
     * [rule_question_post used to set welcome Questions on fronttend for activity rule.]
     */
    function rule_question_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if (isset($data) && $data != NULL) {
            if (!$this->user_model->is_super_admin($this->UserID)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            $welcome_questions = isset($data['QuestionActivityID']) ? $data['QuestionActivityID'] : '';

            $validation_rule[] = array(
                'field' => 'QuestionActivityID[]',
                'label' => 'QuestionActivityID',
                'rules' => 'required'
            );
            $validation_rule[] = array(
                'field' => 'ActivityRuleID',
                'label' => 'ActivityRuleID',
                'rules' => 'trim|required',
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                if(count($welcome_questions) > 5)
                {                    
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = "Only 5 questions are allowed.";
                }
                else
                {
                    $activity_rule_id = isset($data['ActivityRuleID']) ? $data['ActivityRuleID'] : '';
                    $this->rules_model->rule_question($activity_rule_id, $welcome_questions);                    
                }
            }
        } else {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [get_welcome_questions_post used to get welcome Questions for activity rule.]
     */
    function get_welcome_questions_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data) && $data != NULL) {
            if (!$this->user_model->is_super_admin($this->UserID)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }            
            
            $validation_rule[] = array(
                'field' => 'ActivityRuleID',
                'label' => 'ActivityRuleID',
                'rules' => 'trim|required',
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $activity_rule_id = isset($data['ActivityRuleID']) ? $data['ActivityRuleID'] : '';
                $return = $this->rules_model->get_welcome_questions($activity_rule_id,$user_id);
            }
        } else {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [rule_posts_post used to define post rules.]
     */
    function rule_posts_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if (isset($data) && $data != NULL) {
            if (!$this->user_model->is_super_admin($this->UserID)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            
            $validation_rule[] = array(
                'field' => 'ActivityRuleID',
                'label' => 'ActivityRuleID',
                'rules' => 'trim|required',
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $this->load->helper('location');
                $activity_rule_id = isset($data['ActivityRuleID']) ? $data['ActivityRuleID'] : '';
                $post_with_tags = isset($data['PostWithTags']) ? $data['PostWithTags'] : '';
                $popular_post = isset($data['PopularPost']) ? $data['PopularPost'] : '';
                $specific_users = isset($data['SpecificUsers']) ? $data['SpecificUsers'] : array();
                $customize_post_ids = isset($data['CustomizePostIDs']) ? $data['CustomizePostIDs'] : '';
                $all_public_post = isset($data['AllPublicPost']) ? $data['AllPublicPost'] : 0;
                $public_post = isset($data['PublicPost']) ? $data['PublicPost'] : '';
                $this->rules_model->rule_posts($activity_rule_id, $post_with_tags, $popular_post, $specific_users, $customize_post_ids, $all_public_post, $public_post);
            }
        } else {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [rule_profile_post used to define profile rules.]
     */
    function rule_profile_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if (isset($data) && $data != NULL) {
            if (!$this->user_model->is_super_admin($this->UserID)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            
            $validation_rule[] = array(
                'field' => 'ActivityRuleID',
                'label' => 'ActivityRuleID',
                'rules' => 'trim|required',
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $this->load->helper('location');
                $activity_rule_id = isset($data['ActivityRuleID']) ? $data['ActivityRuleID'] : '';
                $profiles_with_tags = isset($data['ProfilesWithTags']) ? $data['ProfilesWithTags'] : '';
                $popular_profiles = isset($data['PopularProfiles']) ? $data['PopularProfiles'] : '';
                $customize_profiles = isset($data['CustomizeProfiles']) ? $data['CustomizeProfiles'] : '';

                $this->rules_model->rule_profile($activity_rule_id, $profiles_with_tags, $popular_profiles, $customize_profiles);
            }
        } else {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * [rule_tags_post used to define tags rule]
     */
    function rule_tags_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if (isset($data) && $data != NULL) {
            if (!$this->user_model->is_super_admin($this->UserID)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            
            $validation_rule[] = array(
                'field' => 'ActivityRuleID',
                'label' => 'ActivityRuleID',
                'rules' => 'trim|required',
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $this->load->helper('location');
                $activity_rule_id = isset($data['ActivityRuleID']) ? $data['ActivityRuleID'] : '';
                $trending_tags = isset($data['TrendingTags']) ? $data['TrendingTags'] : '';
                //$popular_tags  = isset($data['PopularTags']) ? $data['PopularTags'] : '';
                $customize_tags = isset($data['CustomizeTags']) ? $data['CustomizeTags'] : '';
                $this->rules_model->rule_tags($activity_rule_id, $trending_tags, $customize_tags);
            }
        } else {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * Function Name: change_order
     * Description: Change forum order  
     */
    function change_order_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $user_id = $this->UserID;
        if (isset($data) && $data != NULL) {
            $user_id = $this->UserID;
            $is_super_admin = $this->user_model->is_super_admin($user_id);

            if (!$is_super_admin) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            $order_data = isset($data['OrderData']) ? $data['OrderData'] : '';

            if (!empty($order_data)) {
                $this->rules_model->change_order($order_data);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }


    public function get_age_group_post()
    {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        
        $user_id = $this->UserID;

        $return['Data'] = $this->rules_model->get_age_group();
        
        $this->response($return);
    }

    public function get_interest_suggestions_get()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $keyword = $_GET['Keyword'];
        $return['Data'] = $this->rules_model->get_interest_suggestions($user_id, $keyword);
        $this->response($return);
    }

    public function get_users_get()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $keyword = $_GET['Keyword'];
        $return['Data'] = $this->rules_model->get_users($keyword, false);
        $this->response($return);
    }

    public function get_tags_get()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $keyword = $_GET['Keyword'];
        $TagType = isset($_GET['TagType'])?$_GET['TagType']:'ACTIVITY';
        $return['Data'] = $this->rules_model->get_tags($keyword, $TagType);
        $this->response($return);
    }

    function set_rules_config_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = '';
        $Return['ServiceName'] = 'admin_api/rules/set_rules_config';
        $Return['Data'] = array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if (!in_array(getRightsId('configuration_management_change_event'), getUserRightsData($this->DeviceType))) {
            $Return['ResponseCode'] = '598';
            $Return['Message'] = lang('permission_denied');
            /* Final Output */
            $Outputs = $Return;
            $this->response($Outputs);
        }
        
        // Check data posted.
        if (!isset($Data) || !$Data) {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
            $this->response($Return);
        }

        /* Validation - starts */
        if ($this->form_validation->run('api/admin/rules/configuration') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = 511;
            $Return['Message'] = $error; //Shows all error messages as a string
            $this->response($Return);
        }

        $this->load->model(array('admin/configuration_model'));
        // Update no of friends config
        $ConfigValue = $Data['NoOfFrndConfVal'];
        $BUConfigID = NoOfFrndConfID;
        $dataArr = array();
        $dataArr['Value'] = $ConfigValue;
        $this->configuration_model->updateConfigurationSetting($dataArr, $BUConfigID);
        
        // Update no of posts config
        $ConfigValue = $Data['NoOfPostConfVal'];
        $BUConfigID = NoOfPostConfID;
        $dataArr = array();
        $dataArr['Value'] = $ConfigValue;
        $this->configuration_model->updateConfigurationSetting($dataArr, $BUConfigID);
        
        //$currentSetting = $this->configuration_model->getConfigurationSettingByKeyAndValue('BUCID', $BUConfigID);

        
        $Return['Message'] = lang('configuration_setting_update_msg');
        //For delete exisitng configuration setting cache data
        deleteCacheData(GLOBALSETTINGS);
        $this->response($Return);
    }
    
    public function get_rules_config_post() {
        /*$global_settings = $this->config->item("global_settings");
        $NoOfPost = $global_settings['NoOfPost'];
        $NoOfFriends = $global_settings['NoOfFriends'];*/
        $this->load->model(array('admin/configuration_model'));
        $NoOfPost = $this->configuration_model->getConfigurationSettingByKeyAndValue('ConfigID',NoOfPostConfID,'Value');
        $NoOfFriends = $this->configuration_model->getConfigurationSettingByKeyAndValue('ConfigID',NoOfFrndConfID,'Value');
        $Return['ResponseCode'] = '200';
        $Return['Message'] = '';
        $Return['ServiceName'] = 'admin_api/rules/get_rules_config';
        $Return['Data'] = array(
            'NoOfFrndConfVal' => isset($NoOfFriends['Value'])?$NoOfFriends['Value']:0,
            'NoOfPostConfVal' => isset($NoOfPost['Value'])?$NoOfPost['Value']:0,
        );
        
        $this->response($Return);
    }

    public function get_location_id_post()
    {
        $this->load->helper('location');
        $return = $this->return;
        $data = $this->post_data;
        $return['Data'] = update_location($data);
        $this->response($return);
    }

    public function get_activity_post()
    {
        $return = $this->return;
        $data = $this->post_data;

        $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : '' ;

        $return['Data'] = $this->rules_model->get_activity_details($activity_guid);
        if(!$return['Data'])
        {
            $return['ResponseCode'] = 511;
        }
        $this->response($return);
    }

    public function get_rule_details_post()
    {
        $return = $this->return;
        $data = $this->post_data;

        $rule_id = isset($data['ActivityRuleID']) ? $data['ActivityRuleID'] : '' ;

        $return['Data'] = $this->rules_model->get_rule_details($rule_id);
        $this->response($return);   
    }

}

//End of file configuration.php
