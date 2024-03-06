<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Skills extends Common_API_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->check_module_status(29);
        $this->load->model(array('group/group_model', 'activity/activity_model','notification_model','skills/skills_model'));        
    }

    /**
     * [save_post Used to add skill for an entity]
     * @return [type] [description]
     */
    function save_post()
    {
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
                $this->skills_model->save($skills, $module_id, $module_entity_id);
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
     * Function Name: skills_list_for_endorsement
     * Description: get the list of skills to endorse for autocomplete
     */
    function skills_list_for_endorsement_get()
    {
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

            $return['Data'] = $this->skills_model->skills_list_for_endorsement($module_id, $module_entity_id, $visitor_module_id, $visitor_module_entity_id, $search);
        }
        $this->response($return['Data']);
    }

    /**
     * Function Name: skills_list
     * Description: get the list of skills for autocomplete
     */
    function skills_list_get()
    {
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

            $return['Data'] = $this->skills_model->skills_list($module_id, $module_entity_id, $search_keyword);
        }
        $this->response($return['Data']);
    }

    /**
     * Function Name: get_user_skills
     * Description: get the list of skills for endorsement suggestion
     */
    function get_user_skills_post()
    {
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

            $return['Data'] = $this->skills_model->get_user_skills($module_id, $module_entity_id, $page_no, $page_size);
        }
        $this->response($return);
    }

    /**
     * Function Name: endorse_suggestion
     * Description: get the list of skills for endorsement suggestion
     */
    function endorse_suggestion_post()
    {
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

            $return['Data'] = $this->skills_model->endorse_suggestion($module_id, $module_entity_id, $visitor_module_id, $visitor_module_entity_id, $page_no, $page_size);
            $return['CanEndorse'] = $can_endorse;
        }
        $this->response($return);
    }

    /**
     * Function Name: save_endorsement
     * Description: get the details of user privacy settings
     */
    function save_endorsement_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
            $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
            $visitor_module_id = isset($data['VisitorModuleID']) ? $data['VisitorModuleID'] : 3;
            $visitor_module_entity_guid = isset($data['VisitorModuleEntityGUID']) ? $data['VisitorModuleEntityGUID'] : '';
            $skills = isset($data['Skills']) ? $data['Skills'] : array();

            $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
            $visitor_module_entity_id = get_detail_by_guid($visitor_module_entity_guid, $visitor_module_id);

            $this->skills_model->save_endorsement($user_id, $module_id, $module_entity_id, $visitor_module_id, $visitor_module_entity_id, $skills);
            $return['Message'] = 'Endorsed successfully';
        }
        else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: details
     * Description: get the details of user skills
     */
    function details_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
            $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $filter = isset($data['Filter']) ? $data['Filter'] : 0;
            $ignore_entity_skill_guid = isset($data['IgnoreEntitySkillGUID']) ? $data['IgnoreEntitySkillGUID'] : '';
            $module_entity_id = $user_id;
            $is_owner = FALSE;


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

            if ($module_id == 3 && $user_id == $module_entity_id)
            {
                $is_owner = TRUE;
            }
            else if ($module_id == 18)
            {
                $is_owner = checkPermission($user_id, $module_id, $module_entity_id, 'IsOwner');
            }
            else if ($module_id == 1)
            {
                $is_owner = $this->group_model->is_admin($user_id, $module_entity_id); 
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

            $return['TotalRecords'] = $this->skills_model->details($module_id, $module_entity_id, TRUE, $page_no, $page_size, $is_owner, $visitor_module_id, $visitor_module_entity_id, $filter, $ignore_entity_skill_guid);
            $return['Data'] = $this->skills_model->details($module_id, $module_entity_id, FALSE, $page_no, $page_size, $is_owner, $visitor_module_id, $visitor_module_entity_id, $filter, $ignore_entity_skill_guid);
            $return['CanEndorse'] = $can_endorse;
        }
        else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * [delete_skills_post Used to delete skill for an entity]
     * @return [type] [description]
     */
    function delete_skills_post()
    {
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
                $this->skills_model->delete_skills($skills, $module_id, $module_entity_id);
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
    {
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
     * [delete_endorsement_post Used to delete endorsement for an skills]
     * @return [type] [description]
     */
    function delete_endorsement_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $skill_id = isset($data['SkillID']) ? $data['SkillID'] : '';
            if (empty($skill_id))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "skill ID");
            }
            else
            {
                $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
                $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
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

                $this->skills_model->delete_endorsement($skill_id, $module_id, $module_entity_id, $visitor_module_id, $visitor_module_entity_id);
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
     * [endorsement_list Used to get the list of endorsement for an skills]
     * @return [type] [description]
     */
    function endorsement_list_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $entity_skill_id = isset($data['EntitySkillID']) ? $data['EntitySkillID'] : '';
            if (empty($entity_skill_id))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "entity skill ID");
            }
            else
            {
                $visitor_module_entity_id = $user_id;
                $keyword = isset($data['keyword']) ? $data['keyword'] : '';
                $visitor_module_id = (isset($data['VisitorModuleID']) && !empty($data['VisitorModuleID'])) ? $data['VisitorModuleID'] : 3;
                $visitor_module_entity_guid = (isset($data['VisitorModuleEntityGUID']) && !empty($data['VisitorModuleEntityGUID'])) ? $data['VisitorModuleEntityGUID'] : '';
                $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
                $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;

                if (!empty($visitor_module_entity_guid))
                {
                    $visitor_module_entity_id = get_detail_by_guid($visitor_module_entity_guid, $visitor_module_id);
                }


                if (!empty($visitor_module_id) && !empty($visitor_module_entity_id))
                {
                    /* $return['IsEndorse'] = FALSE;
                      $return['IsEndorse'] = $this->is_endorse($entity_skill_id, $visitor_module_id, $visitor_module_entity_id); */

                    $return['TotalEndorsement'] = $this->skills_model->endorsement_list($entity_skill_id, TRUE, $page_no, $page_size, '', '', $keyword);
                    $return['Data'] = $this->skills_model->endorsement_list($entity_skill_id, FALSE, $page_no, $page_size, $visitor_module_id, $visitor_module_entity_id, $keyword);
                }
                else
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "visitor guid");
                }
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
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data))
        {
            $order_data = isset($data['OrderData']) ? $data['OrderData'] : '';

            if (!empty($order_data))
            {
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
    {
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
    {
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
    {
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
    {
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
