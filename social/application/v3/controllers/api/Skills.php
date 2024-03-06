<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Skills extends Common_API_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->check_module_status(29);
        $this->load->model(array('group/group_model', 'activity/activity_model','notification_model'));        
    }

    function index_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        
        $this->load->model(array('skills/skills_model'));
        $data = $this->skills_model->get_skills(3, $user_id);
        
        $this->return['Data'] = $data;        
        $this->response($this->return);
    }

    /**
     * [save_post Used to add skill for an entity]
     * @return [type] [description]
     */
    function save_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data)) {
            $skills = isset($data['Skills']) ? $data['Skills'] : array();
            
            $this->load->model(array('skills/skills_model'));
            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
            $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
            $module_entity_id = $user_id;
            if (!empty($module_entity_guid)) {
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
            }
            $this->skills_model->save($skills, $module_id, $module_entity_id);
            
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: details
     * Description: get the details of user profile skills
     */
    function details_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data)) {
            $page_no = safe_array_key($data, 'PageNo', PAGE_NO);
            $page_size = safe_array_key($data, 'PageSize', '');
            $module_id = safe_array_key($data, 'ModuleID', 3);
            $module_entity_guid = safe_array_key($data, 'ModuleEntityGUID', '');
            $filter = safe_array_key($data, 'Filter', 0);
            $ignore_entity_skill_guid = safe_array_key($data, 'IgnoreEntitySkillGUID', '');

            $module_entity_id = $user_id;
            $is_owner = FALSE;


            $visitor_module_entity_id = $user_id;
            $visitor_module_id = safe_array_key($data, 'VisitorModuleID', 3);
            $visitor_module_entity_guid = safe_array_key($data, 'VisitorModuleEntityGUID', '');
            
            if (!empty($module_entity_guid)) {
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
            }

            $can_endorse = 1;
            if (!empty($visitor_module_entity_guid)) {
                $visitor_module_entity_id = get_detail_by_guid($visitor_module_entity_guid, $visitor_module_id);
            }

            if ($module_id == 3 && $user_id == $module_entity_id) {
                $is_owner = TRUE;
                $can_endorse = 0;
            } else if ($module_id == 18) {
                $is_owner = checkPermission($user_id, $module_id, $module_entity_id, 'IsOwner');
            } else if ($module_id == 1) {
                $is_owner = $this->group_model->is_admin($user_id, $module_entity_id); 
            }
            /* 
            if ($module_id == 3 && $visitor_module_entity_id != $module_entity_id) {
                $can_endorse = FALSE;

                $users_relation = get_user_relation($module_entity_id, $visitor_module_entity_id);
                $privacy_details = $this->privacy_model->details($module_entity_id);

                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label'])
                {
                    foreach ($privacy_details['Label'] as $privacy_label)
                    {
                        if ($privacy_label['Value'] == 'endorse_skill' && in_array($privacy_label[$privacy], $users_relation))
                        {
                            $can_endorse = TRUE;
                        }
                    }
                }
            }
            */
            $this->load->model(array('skills/skills_model'));
            //$return['TotalRecords'] = $this->skills_model->details($module_id, $module_entity_id, TRUE, $page_no, $page_size, $is_owner, $visitor_module_id, $visitor_module_entity_id, $filter, $ignore_entity_skill_guid);
            $return['Data'] = $this->skills_model->details($module_id, $module_entity_id, FALSE, $page_no, $page_size, $is_owner, $visitor_module_id, $visitor_module_entity_id, $filter, $ignore_entity_skill_guid);
            $return['CanEndorse'] = $can_endorse;
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }


    /**
     * Function Name: save_endorsement
     * Description: get the details of user privacy settings
     */
    function save_endorsement_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($data) {
            $config = array(
                array(
                    'field' => 'EntitySkillID',
                    'label' => 'entity skill id',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $entity_skill_id = $data['EntitySkillID'];
                
                $visitor_module_id = safe_array_key($data, 'VisitorModuleID', 3);
                $visitor_module_entity_guid = safe_array_key($data, 'VisitorModuleEntityGUID', '');
                
                $visitor_module_entity_id = $user_id;
                if (!empty($visitor_module_entity_guid)) {
                    $visitor_module_entity_id   = get_detail_by_guid($visitor_module_entity_guid, $visitor_module_id);
                }
                $this->load->model(array('skills/skills_model'));
                $this->skills_model->save_endorsement($user_id, $visitor_module_id, $visitor_module_entity_id, $entity_skill_id);
                $return['Message'] = 'Endorsed successfully';
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * [delete_endorsement_post Used to delete endorsement for an skills]
     * @return [type] [description]
     */
    function delete_endorsement_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'EntitySkillID',
                    'label' => 'entity skill id',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $entity_skill_id = $data['EntitySkillID'];
                
                $visitor_module_id = safe_array_key($data, 'VisitorModuleID', 3);
                $visitor_module_entity_guid = safe_array_key($data, 'VisitorModuleEntityGUID', '');
                
                $visitor_module_entity_id = $user_id;
                if (!empty($visitor_module_entity_guid)) {
                    $visitor_module_entity_id   = get_detail_by_guid($visitor_module_entity_guid, $visitor_module_id);
                }

                $this->load->model(array('skills/skills_model'));
                $this->skills_model->delete_endorsement($entity_skill_id, $visitor_module_id, $visitor_module_entity_id);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * [endorsement_list Used to get the list of endorsement for an skills]
     * @return [type] [description]
     */
    function endorsement_list_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'EntitySkillID',
                    'label' => 'entity skill id',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $entity_skill_id = $data['EntitySkillID'];
                $page_no = safe_array_key($data, 'PageNo', PAGE_NO);
                $page_size = safe_array_key($data, 'PageSize', PAGE_SIZE);
                $keyword = safe_array_key($data, 'keyword', '');
              
                $this->load->model(array('skills/skills_model'));
                $return['Data'] = $this->skills_model->endorsement_list($entity_skill_id, $user_id, FALSE, $page_no, $page_size, $keyword);                
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }




    /**
     * Function Name: skills_list_for_endorsement
     * Description: get the list of skills to endorse for autocomplete
     */
    function skills_list_for_endorsement_get()
    {exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 29;
            $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
            $search = isset($data['Search']) ? $data['Search'] : '';

            $module_entity_id = $user_id;
            $visitor_module_entity_id = $user_id;

            $visitor_module_id = (isset($data['VisitorModuleID']) && !empty($data['VisitorModuleID'])) ? $data['VisitorModuleID'] : 3;
            $visitor_module_entity_guid = (isset($data['VisitorModuleEntityGUID']) && !empty($data['VisitorModuleEntityGUID'])) ? $data['VisitorModuleEntityGUID'] : '';

            if (!empty($module_entity_guid))
            {
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
            }

            if (!empty($visitor_module_entity_guid))
            {
                $visitor_module_entity_id = get_detail_by_guid($visitor_module_entity_guid, $visitor_module_id);
            }
            $this->load->model(array('skills/skills_model'));
            $return['Data'] = $this->skills_model->skills_list_for_endorsement($module_id, $module_entity_id, $visitor_module_id, $visitor_module_entity_id, $search);
        }
        $this->response($return['Data']);
    }

    /**
     * Function Name: skills_list
     * Description: get the list of skills for autocomplete
     */
    function skills_list_get()
    {exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
            $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
            $search_keyword = isset($data['Keyword']) ? $data['Keyword'] : '';

            $module_entity_id = $user_id;

            if (!empty($module_entity_guid))
            {
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
            }
            $this->load->model(array('skills/skills_model'));
            $return['Data'] = $this->skills_model->skills_list($module_id, $module_entity_id, $search_keyword);
        }
        $this->response($return['Data']);
    }

    /**
     * Function Name: get_user_skills
     * Description: get the list of skills for endorsement suggestion
     */
    function get_user_skills_post()
    {exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
            $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;

            $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
            $this->load->model(array('skills/skills_model'));
            $return['Data'] = $this->skills_model->get_user_skills($module_id, $module_entity_id, $page_no, $page_size);
        }
        $this->response($return);
    }

    /**
     * Function Name: endorse_suggestion
     * Description: get the list of skills for endorsement suggestion
     */
    function endorse_suggestion_post()
    {exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
            $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $module_entity_id = $user_id;
            $visitor_module_entity_id = $user_id;
            $visitor_module_id = (isset($data['VisitorModuleID']) && !empty($data['VisitorModuleID'])) ? $data['VisitorModuleID'] : 3;
            $visitor_module_entity_guid = (isset($data['VisitorModuleEntityGUID']) && !empty($data['VisitorModuleEntityGUID'])) ? $data['VisitorModuleEntityGUID'] : '';

            if (!empty($module_entity_guid))
            {
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
            }

            if (!empty($visitor_module_entity_guid))
            {
                $visitor_module_entity_id = get_detail_by_guid($visitor_module_entity_guid, $visitor_module_id);
            }


            $can_endorse = TRUE;
            if ($module_id == 3 && $visitor_module_entity_id != $module_entity_id)
            {
                $can_endorse = FALSE;
                $users_relation = get_user_relation($module_entity_id, $visitor_module_entity_id);
                $privacy_details = $this->privacy_model->details($module_entity_id);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label'])
                {
                    foreach ($privacy_details['Label'] as $privacy_label)
                    {
                        if ($privacy_label['Value'] == 'endorse_skill' && in_array($privacy_label[$privacy], $users_relation))
                        {
                            $can_endorse = TRUE;
                        }
                    }
                }
            }
            $this->load->model(array('skills/skills_model'));
            $return['Data'] = $this->skills_model->endorse_suggestion($module_id, $module_entity_id, $visitor_module_id, $visitor_module_entity_id, $page_no, $page_size);
            $return['CanEndorse'] = $can_endorse;
        }
        $this->response($return);
    }

        
    /**
     * [delete_skills_post Used to delete skill for an entity]
     * @return [type] [description]
     */
    function delete_skills_post()
    {exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $skills = isset($data['Skills']) ? $data['Skills'] : '';
            if (empty($skills))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "skills");
            }
            else
            {
                $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
                $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
                $module_entity_id = $user_id;
                if (!empty($module_entity_guid))
                {
                    $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
                }
                $this->load->model(array('skills/skills_model'));
                $this->skills_model->delete_user_skills($skills, $module_id, $module_entity_id);
            }
        }
        else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * [approve_pending_skills_post Used to approve pending skill for an entity]
     * @return [type] [description]
     */
    function approve_pending_skills_post()
    {exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $skills = isset($data['Skills']) ? $data['Skills'] : '';
            if (empty($skills))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "skills");
            }
            else
            {
                $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
                $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
                $module_entity_id = $user_id;
                if (!empty($module_entity_guid))
                {
                    $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
                }
                $this->load->model(array('skills/skills_model'));
                $this->skills_model->approve_pending_skills($skills, $module_id, $module_entity_id);
            }
        }
        else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }
     
    /**
     * Function Name: change_skill_order
     * Description: change order of user skils
     */
    function change_skill_order_post()
    {exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $order_data = isset($data['OrderData']) ? $data['OrderData'] : '';

            if (!empty($order_data))
            {
                $this->load->model(array('skills/skills_model'));
                $this->skills_model->change_skill_order($order_data);
            }
        }
        else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: manage_save
     * Description: manage user skills when user change skill order or delete skills
     */
    function manage_save_post()
    {exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $skills = isset($data['Skills']) ? $data['Skills'] : '';
            if (empty($skills))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "skills");
            }
            else
            {
                $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
                $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
                $module_entity_id = $user_id;
                if (!empty($module_entity_guid))
                {
                    $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
                }
                $this->load->model(array('skills/skills_model'));
                $this->skills_model->manage_save($skills, $module_id, $module_entity_id);
            }
        }
        else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: delete_pending_skill
     * Description: Deleted user pending skills
     */
    function delete_pending_skill_post()
    {exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $entity_skillGUID_array = isset($data['EntitySkillGUIDs']) ? $data['EntitySkillGUIDs'] : array();
            if (empty($entity_skillGUID_array))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "skill ID");
            }
            else
            {
                $this->load->model(array('skills/skills_model'));
                $this->skills_model->delete_pending_skill($entity_skillGUID_array);
            }
        }
        else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: get_endorsement
     * Description: get all endorsement according to user
     */
    function get_endorsement_post()
    {exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {

            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
            $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
            $endorsment_entity_guid = isset($data['EndorsmentEntityGUID']) ? $data['EndorsmentEntityGUID'] : '';
            $module_entity_id = $user_id;
            $visitor_module_entity_id = $user_id;
            $visitor_module_id = (isset($data['VisitorModuleID']) && !empty($data['VisitorModuleID'])) ? $data['VisitorModuleID'] : 3;
            $visitor_module_entity_guid = (isset($data['VisitorModuleEntityGUID']) && !empty($data['VisitorModuleEntityGUID'])) ? $data['VisitorModuleEntityGUID'] : '';
            $endorsment_entity_id = '';
            if (!empty($module_entity_guid))
            {
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
            }

            if (!empty($visitor_module_entity_guid))
            {
                $visitor_module_entity_id = get_detail_by_guid($visitor_module_entity_guid, $visitor_module_id);
            }
            if (!empty($endorsment_entity_guid))
            {
                $endorsment_entity_id = get_detail_by_guid($endorsment_entity_guid, 3);
            }

            $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;

            if (!empty($visitor_module_id) && !empty($visitor_module_entity_id))
            {
                $this->load->model(array('skills/skills_model'));
                /* $return['IsEndorse'] = FALSE;
                  $return['IsEndorse'] = $this->is_endorse($entity_skill_id, $visitor_module_id, $visitor_module_entity_id); */

                $result = $this->skills_model->get_endorsement($visitor_module_id, $visitor_module_entity_id, $module_id, $module_entity_id, $page_no, $page_size, $endorsment_entity_id);
                $return['Data'] = $result['Data'];
                $return['TotalRecords'] = $result['TotalRecords'];
            }
            else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "visitor guid");
            }
        }
        else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: endorse_connection
     * Description: get  endorsement  user data
     */
    function endorse_connection_post()
    { exit;
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {

            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
            $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
            $module_entity_id = $user_id;
            $endorse_module_entity_id = $user_id;
            $endorse_module_id = (isset($data['EndorseModuleID']) && !empty($data['EndorseModuleID'])) ? $data['EndorseModuleID'] : 3;
            $endorse_module_entity_guid = (isset($data['endorseModuleEntityGUID']) && !empty($data['endorseModuleEntityGUID'])) ? $data['endorseModuleEntityGUID'] : '';

            if (!empty($module_entity_guid))
            {
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
            }

            if (!empty($endorse_module_entity_guid))
            {
                $endorse_module_entity_id = get_detail_by_guid($endorse_module_entity_guid, $endorse_module_id);
            }

            $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;

            if (!empty($endorse_module_id) && !empty($endorse_module_entity_id))
            {
                $this->load->model(array('skills/skills_model'));
                /* $return['IsEndorse'] = FALSE;
                  $return['IsEndorse'] = $this->is_endorse($entity_skill_id, $visitor_module_id, $visitor_module_entity_id); */

                $result = $this->skills_model->endorse_connection($module_id, $module_entity_id, $endorse_module_id, $endorse_module_entity_id, $page_no, $page_size);
                //  print_r($result);die;
                $return['Data'] = $result['Data'];
                ;
            }
            else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "visitor guid");
            }
        }
        else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

}
