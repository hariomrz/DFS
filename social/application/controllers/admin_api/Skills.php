<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user related process like : getprofiledata, editprofile, settings, changepassword
 * @package    User
 * @author     ashwin kumar soni(25-09-2014)
 * @version    1.0
 */

//require APPPATH.'/libraries/REST_Controller.php';

class Skills extends Admin_API_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('admin/users_model', 'admin/login_model', 'admin/media_model', 'admin/skills_model', 'notification_model'));

        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200)
        {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
    }

    public function index()
    {
        
    }

    /**
     * Function for get user profile data.
     * Parameters : $user_id, $start_date, $end_date
     * Return : Array of user data
     */
    public function list_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/get_skills';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {

            $search_key = (isset($Data['Keyword'])) ? $Data['Keyword'] : '';
            $categoryID_array = (isset($Data['CategoryIDs'])) ? $Data['CategoryIDs'] : array();
            $page_no = (isset($Data['PageNo'])) ? $Data['PageNo'] : 1;
            $page_size = (isset($Data['PageSize'])) ? $Data['PageSize'] : 50;
            $sort_by = (isset($Data['SortBy'])) ? $Data['SortBy'] : 'SM.NoOfEndorsements';
            $order_by = (isset($Data['OrderBy'])) ? $Data['OrderBy'] : 'DESC';
            $start_date = (isset($Data['StartDate'])) ? ($Data['StartDate']) : '';
            $end_date = (isset($Data['EndDate'])) ? ($Data['EndDate']) : '';
            $skill_type = (isset($Data['SkillType'])) ? ($Data['SkillType']) : '';
            $skillID_array = (isset($Data['SkillIDs'])) ? ($Data['SkillIDs']) : array();

            $Input = array('sort_by' => $sort_by, 'order_by' => $order_by, 'categoryID_array' => $categoryID_array, 'skill_type' => $skill_type, 'skillID_array' => $skillID_array, 'search_key' => $search_key);

            $result = $this->skills_model->get_skills($Input, $page_no, $page_size, $start_date, $end_date);
            $Return['Data'] = $result['Data'];
            $Return['TotalRecords'] = $result['total_records'];
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function add_category_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/add_category';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {
            $category_id = (isset($Data['CategoryID'])) ? $Data['CategoryID'] : 0;
            $category_name = (isset($Data['CategoryName'])) ? $Data['CategoryName'] : '';
            $sub_category_data = (isset($Data['SubCategories'])) ? $Data['SubCategories'] : array();
            $Skills_data = (isset($Data['Skills'])) ? $Data['Skills'] : array();
            $sub_category_id = 0;
            // print_r($sub_category_data);die;
            if ($category_id == '' && $category_name == '')
            {
                /* Error - Invalid JSON format */
                $Return['ResponseCode'] = '519';
                $Return['Message'] = lang('select_add_category');
            }
            else
            {
                // Add new category in Category
                if (empty($category_id) && !empty($category_name))
                {
                    $category_id = $this->skills_model->check_category_by_name($category_name, 0, 1);
                }

                if (!empty($sub_category_data))
                {
                    foreach ($sub_category_data as $sub_category_data_item)
                    {
                        $sub_category_id = $sub_category_data_item['ID'];
                        $sub_category_name = $sub_category_data_item['Name'];

                        if (empty($sub_category_id) && !empty($sub_category_name))
                        {
                            $sub_category_id = $this->skills_model->check_category_by_name($sub_category_name, $category_id, 0);
                        }

                        $entity_category_id = $sub_category_id;
                        if ($entity_category_id)
                        {
                            $skill_data_array = $sub_category_data_item['Skill'];
                            if (!empty($skill_data_array))
                            {
                                foreach ($skill_data_array as $skill_data_array_item)
                                {
                                    $skill_id = $skill_data_array_item['ID'];
                                    $skill_name = $skill_data_array_item['Name'];

                                    $this->skills_model->check_skill_by_name($skill_id, $skill_name, $entity_category_id);
                                }
                            }
                        }
                    }
                }

                if (!empty($Skills_data))
                {
                    foreach ($Skills_data as $Skills_data_item)
                    {
                        $skill_id = $Skills_data_item['ID'];
                        $skill_name = $Skills_data_item['Name'];

                        $this->skills_model->check_skill_by_name($skill_id, $skill_name, $entity_category_id);
                    }
                }
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function save_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/save';
        $Return['Data'] = array();
        $Data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($Data) && $Data != NULL)
        {

            if ($this->form_validation->run('admin_api/skills/save') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error;
            }
            else
            {
                $skill_name = (isset($Data['Name'])) ? $Data['Name'] : '';
                $skill_id = (isset($Data['SkillID'])) ? $Data['SkillID'] : '';
                $category_id = (isset($Data['CategoryID'])) ? $Data['CategoryID'] : '0';
                $sub_category_id = (isset($Data['SubCategoryID'])) ? $Data['SubCategoryID'] : '0';
                $MediaGUID = (isset($Data['MediaGUID'])) ? $Data['MediaGUID'] : '0';
                $similar_skill_array = (isset($Data['SimilarSkillIDs'])) ? $Data['SimilarSkillIDs'] : array();
                $media_id = 0;
                if ($MediaGUID)
                {
                    $get_media = get_data('MediaID', MEDIA, array('MediaGUID' => $MediaGUID), '1', '');
                    if ($get_media)
                    {
                        $media_id = $get_media->MediaID;
                    }
                }
                $entity_category_id = $sub_category_id;
                if (!$entity_category_id)
                {
                    $entity_category_id = $category_id;
                }
                if ($skill_id)
                {
                    $skill_id_temp = $this->skills_model->updat_skill($user_id, $skill_name, $skill_id, $entity_category_id, $media_id);
                    if ($skill_id_temp['ResponseCode'] == 200)
                    {
                        $this->skills_model->add_similar_skills($skill_id, $similar_skill_array);
                        $Return['Message'] = 'Skill update successfully';
                    }
                    else
                    {
                        $Return['ResponseCode'] = '519';
                        $Return['Message'] = 'Skill already exists';
                    }
                }
                else
                {
                    $skill_id_temp = $this->skills_model->check_skill_by_name('', $skill_name, $entity_category_id, $media_id);
                    $skill_id = $skill_id_temp['SkillID'];
                    if ($skill_id_temp['ResponseCode'] == 200)
                    {
                        $this->skills_model->add_similar_skills($skill_id, $similar_skill_array);
                        $Return['Message'] = 'Skill saved successfully';
                    }
                    else
                    {
                        $Return['ResponseCode'] = '519';
                        $Return['Message'] = 'Skill already exists';
                    }
                }
                //   if (!empty($similar_skill_array))
                //{
                //}
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function remove_skill_category_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/remove_skill_category';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {

            if ($this->form_validation->run('admin_api/skills/remove_category') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error;
            }
            else
            {
                $category_id_array = (isset($Data['CategoryIDs'])) ? $Data['CategoryIDs'] : array();

                if (!empty($category_id_array))
                {
                    foreach ($category_id_array as $category_id_array_item)
                    {
                        $category_id = $category_id_array_item;
                        // Update Category and SubCategory Status=3
                        $this->skills_model->update_data(CATEGORYMASTER, array('StatusID' => 3), array('CategoryID' => $category_id));
                        $this->skills_model->update_data(CATEGORYMASTER, array('StatusID' => 3), array('ParentID' => $category_id));

                        // Get all subcategory IDs
                        $get_sub_category = get_data('CategoryID', CATEGORYMASTER, array('ParentID' => $category_id), '', '');
                        $sub_cat_id_array = array($category_id);
                        if ($get_sub_category)
                        {
                            foreach ($get_sub_category as $get_sub_category_item)
                            {
                                $sub_cat_id_array[] = $get_sub_category_item->CategoryID;
                            }
                        }
                        if (!empty($sub_cat_id_array))
                        {
                            // Get all skill IDs
                            $skill_id_array = $this->skills_model->get_category_skill_id($sub_cat_id_array);

                            // Delete users skill 
                            if (!empty($skill_id_array))
                            {
                                $this->skills_model->delete_data(ENTITYSKILLS, 'SkillID', $skill_id_array);
                            }

                            // Delete skill relation
                            if (!empty($sub_cat_id_array))
                            {
                                $this->skills_model->delete_data(ENTITYCATEGORY, 'CategoryID', $sub_cat_id_array);
                            }
                        }
                    }
                }
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function categories_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/categories';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {

            $search_key = (isset($Data['Keyword'])) ? $Data['Keyword'] : '';
            $page_no = (isset($Data['PageNo'])) ? $Data['PageNo'] : 1;
            $page_size = (isset($Data['PageSize'])) ? $Data['PageSize'] : PAGE_SIZE;
            $sort_by = (isset($Data['SortBy'])) ? $Data['SortBy'] : 'Name';
            $order_by = (isset($Data['OrderBy'])) ? $Data['OrderBy'] : 'ASC';
            $parent_id = (isset($Data['ParentID'])) ? $Data['ParentID'] : '';

            $Input = array('sort_by' => $sort_by, 'order_by' => $order_by, 'search_key' => $search_key, 'parent_id' => $parent_id);

            $result = $this->skills_model->categories($Input, $page_no, $page_size);
            $Return['Data'] = $result['Data'];
            $Return['TotalRecords'] = $result['total_records'];
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function merge_skills_details_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/merge_skills_details';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {
            $skillID_array = (isset($Data['SkillIDs'])) ? ($Data['SkillIDs']) : array();
            if (!empty($skillID_array))
            {
                $Input = array();
                $result = $this->skills_model->merge_skills_details($Input, $skillID_array);
                $Return['Data'] = $result['Data'];
                $Return['TotalRecords'] = $result['total_records'];
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function merge_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/merge';
        $Return['Data'] = array();
        $Data = $this->post_data;
          $user_id = $this->UserID;
        if (isset($Data) && $Data != NULL)
        {
            if ($this->form_validation->run('admin_api/skills/merge_skills') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error;
            }
            else
            {
                $skillID_array = (isset($Data['SkillIDs'])) ? ($Data['SkillIDs']) : array();
                $skill_name = (isset($Data['Name'])) ? ($Data['Name']) : '';
                $category_id = (isset($Data['CategoryID'])) ? ($Data['CategoryID']) : '';
                $parent_category_name = (isset($Data['ParentCategoryName'])) ? ($Data['ParentCategoryName']) : '';
                $sub_category_id = (isset($Data['SubCategoryID'])) ? ($Data['SubCategoryID']) : '';
                $sub_category_name = (isset($Data['SubCategoryName'])) ? ($Data['SubCategoryName']) : '';
                $media_id = (isset($Data['MediaID'])) ? $Data['MediaID'] : '0';
                if (empty($category_id) && $parent_category_name != '')
                {
                    $category_id = $this->skills_model->check_category_by_name($parent_category_name, 0, 1);
                }
                if (empty($sub_category_id) && $sub_category_name != '')
                {
                    $sub_category_id = $this->skills_model->check_category_by_name($sub_category_name, $category_id, 0);
                }

                $entity_category_id = $category_id;
                if ($sub_category_id)
                {
                    $entity_category_id = $sub_category_id;
                }

                $skill_id_temp = $this->skills_model->check_skill_by_name('', $skill_name, $entity_category_id, $media_id);
                $skill_id = $skill_id_temp['SkillID'];
                if ($skill_id_temp['ResponseCode'] == 200)
                {
                    $this->skills_model->manage_endorsements($user_id,$skillID_array, $skill_id);
                    $Return['Message'] = 'Skill merge successfully';
                }
                else
                {
                    $Return['ResponseCode'] = '519';
                    $Return['Message'] = 'Skill already exists';
                }
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function similar_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/similar';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {

            $search_key = (isset($Data['Keyword'])) ? $Data['Keyword'] : '';
            $skillID_array = (isset($Data['SkillIDs'])) ? ($Data['SkillIDs']) : array();
            $page_no = (isset($Data['PageNo'])) ? $Data['PageNo'] : 1;
            $page_size = (isset($Data['PageSize'])) ? $Data['PageSize'] : 50;
            $sort_by = (isset($Data['SortBy'])) ? $Data['SortBy'] : '';
            $order_by = (isset($Data['OrderBy'])) ? $Data['OrderBy'] : 'DESC';

            $Input = array('sort_by' => $sort_by, 'order_by' => $order_by, 'search_key' => $search_key, 'skillID_array' => $skillID_array);

            $result = $this->skills_model->similar_skill($Input, $page_no, $page_size);
            $Return['Data'] = $result['Data'];
            $Return['TotalRecords'] = $result['total_records'];
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function suggested_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/suggested';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {

            $search_key = (isset($Data['Keyword'])) ? $Data['Keyword'] : '';
            $page_no = (isset($Data['PageNo'])) ? $Data['PageNo'] : 1;
            $page_size = (isset($Data['PageSize'])) ? $Data['PageSize'] : 10;
            $sort_by = (isset($Data['SortBy'])) ? $Data['SortBy'] : '';
            $order_by = (isset($Data['OrderBy'])) ? $Data['OrderBy'] : 'DESC';

            $Input = array('sort_by' => $sort_by, 'order_by' => $order_by, 'search_key' => $search_key);

            $result = $this->skills_model->suggested_category($Input, $page_no, $page_size);
            $Return['Data'] = $result['Data'];
            //  $Return['TotalRecords'] = $result['total_records'];
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function remove_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/remove';
        $Return['Data'] = array();
        $Data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($Data) && $Data != NULL)
        {
            if ($this->form_validation->run('admin_api/skills/remove') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error;
            }
            else
            {
                $skill_id_array = (isset($Data['SkillIDs'])) ? $Data['SkillIDs'] : array();
                $type = (isset($Data['Type'])) ? $Data['Type'] : '';

                if (!empty($skill_id_array))
                {
                    $this->skills_model->remove_skill($user_id, $skill_id_array);
                }
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function skill_profile_count_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/skill_profile_count';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {
            if ($this->form_validation->run('admin_api/skills/skill_profile_count') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error;
            }
            else
            {
                $skill_id = (isset($Data['SkillID'])) ? $Data['SkillID'] : '';

                if (!empty($skill_id))
                {
                    $entity_skill_endorsement = get_data('SUM(NoOfEndorsements) as NoOfEndorsements', ENTITYSKILLS, array('SkillID' => $skill_id), '1', '');
                    $entity_skill_profile = get_data('COUNT(NoOfEndorsements) as ProfileCount', ENTITYSKILLS, array('SkillID' => $skill_id), '1', '');
                    //echo $this->db->last_query();die;

                    $Return['EndorsementsCount'] = 0;
                    $Return['ProfileCount'] = 0;

                    if ($entity_skill_endorsement)
                    {
                        $Return['EndorsementsCount'] = ($entity_skill_endorsement->NoOfEndorsements) ? $entity_skill_endorsement->NoOfEndorsements : 0;
                    }
                    if ($entity_skill_profile)
                    {
                        $Return['ProfileCount'] = ($entity_skill_profile->ProfileCount) ? $entity_skill_profile->ProfileCount : 0;
                    }
                }
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function category_profile_count_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/category_profile_count';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {
            if ($this->form_validation->run('admin_api/skills/category_profile_count') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error;
            }
            else
            {
                $category_id = (isset($Data['CategoryID'])) ? $Data['CategoryID'] : '';

                if (!empty($category_id))
                {
                    $Return['SubCategoryCount'] = 0;
                    $Return['SkillCount'] = 0;
                    $Return['EndorsementsCount'] = 0;


                    $result = $this->skills_model->skill_count_by_category($category_id);

                    $Return['SubCategoryCount'] = $result['SubCategoryCount'];
                    $Return['SkillCount'] = $result['SkillCount'];
                    $Return['EndorsementsCount'] = $result['EndorsementsCount'];

                    //  $entity_skill_profile = get_data('COUNT(NoOfEndorsements) as ProfileCount', ENTITYSKILLS, array('SkillID' => $skill_id), '1', '');
                    //echo $this->db->last_query();die;
                }
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    public function add_similar_skills_post()
    {
        $result = $this->skills_model->add_similar_skills(1, array(2, 3, 4, 5, 6));
    }

    public function get_single_skill_post()
    {

        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/skills/get_single_skill';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL)
        {
            if ($this->form_validation->run('admin_api/skills/get_single_skill') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error;
            }
            else
            {
                $skill_id = (isset($Data['SkillID'])) ? $Data['SkillID'] : '';

                if (!empty($skill_id))
                {

                    $Return['Data'] = $this->skills_model->get_single_skill($skill_id);
                }
            }
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    function validate_SkillName($Name)
    {
        $this->form_validation->set_message('validate_SkillName', 'Please add atleast one alphabet value');
        if (!preg_match('/[A-Za-z]{1,}/', $Name, $val))
        {
            return FALSE;
        }
        return TRUE;
    }

}

//End of file user.php