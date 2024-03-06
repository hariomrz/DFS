<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Skills_model extends Admin_Common_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    function insert_data($table, $data)
    {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    function update_data($table, $data, $where)
    {
        $this->db->where($where);
        $this->db->update($table, $data);
        return true;
    }

    function delete_data($table, $field, $where)
    {
        $this->db->where_in($field, $where);
        $this->db->delete($table);
        return true;
    }

    function updat_skill($user_id, $skill_name, $skill_id, $entity_category_id, $media_id)
    {
        $Return['ResponseCode'] = '200';
        $Return['SkillID'] = '$skill_id';
        $this->db->select('SkillID');
        $this->db->from(SKILLSMASTER . ' AS SM');
        $this->db->join(ENTITYCATEGORY . ' AS EC', 'EC.ModuleEntityID=SM.SkillID AND EC.ModuleID=29 ');
        $this->db->where('SM.Name', $skill_name);
        $this->db->where('EC.CategoryID', $entity_category_id);
        $this->db->where('SM.SkillID != ', $skill_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            $Return['ResponseCode'] = '509';
        }
        else
        {
            $skill_entity_data = $this->get_skills_by_id($skill_id);
            $skill_data = get_data('Name,StatusID,AddedBy', SKILLSMASTER, array('SkillID' => $skill_id), 1, '');
            $this->db->where('SkillID', $skill_id);
            $this->db->update(SKILLSMASTER, array('Name' => $skill_name, 'StatusID' => 2, 'MediaID' => $media_id));
            $this->db->where(array('ModuleID' => 29, 'ModuleEntityID' => $skill_id));
            $this->db->update(ENTITYCATEGORY, array('CategoryID' => $entity_category_id));
            if ($skill_data)
            {
                if ($skill_data->StatusID == 1)
                {
                    $parameters[0]['ReferenceID'] = $skill_id;
                    $parameters[0]['Type'] = 'Skills';
                    $this->notification_model->add_notification(103, $user_id, array($skill_data->AddedBy), $skill_id, $parameters);
                }
                if ($skill_data->StatusID == 2 && ($skill_data->Name != $skill_name || $skill_entity_data['CategoryID'] != $entity_category_id ))
                {
                    $entity_skill_data = get_data('ModuleEntityID', ENTITYSKILLS, array('SkillID' => $skill_id, 'ModuleID' => 3), '', '');
                    if ($entity_skill_data)
                    {
                        $notify_user = array();
                        foreach ($entity_skill_data as $entity_skill_data_item)
                        {
                            $notify_user[] = $entity_skill_data_item->ModuleEntityID;
                        }
                        $parameters[0]['ReferenceID'] = $skill_id;
                        $parameters[0]['Type'] = 'Skills';
                        $change_skill_data['OldData'] = array('Name' => $skill_entity_data['Name'], 'CategoryName' => $skill_entity_data['CategoryName'], 'ParentCategorName' => $skill_entity_data['ParentCategorName']);
                        $parameters[0]['SkillData'] = $change_skill_data;
                        $this->notification_model->add_notification(106, $user_id, $notify_user, $skill_id, $parameters);
                    }
                }
            }
        }
        return $Return;
    }

    function remove_skill($user_id, $skill_id_array)
    {
        // Pending skill Data
        $this->db->select('SkillID,AddedBy');
        $this->db->from(SKILLSMASTER);
        $this->db->where_in('SkillID', $skill_id_array);
        $this->db->where('StatusID', 1);

        $query = $this->db->get();
        if ($query->num_rows())
        {
            $results = $query->result_array();
            foreach ($results as $result_item)
            {
                $skill_id = $result_item['SkillID'];
                $parameters[0]['ReferenceID'] = $skill_id;
                $parameters[0]['Type'] = 'Skills';
                $this->notification_model->add_notification(104, $user_id, array($result_item['AddedBy']), $skill_id, $parameters);
            }
        }

        // Remove skill Data
        $this->db->select('ES.ModuleEntityID,ES.SkillID');
        $this->db->from(SKILLSMASTER.' as SM');
        $this->db->join(ENTITYSKILLS.' as ES','SM.SkillID=ES.SkillID');
        $this->db->where_in('ES.SkillID', $skill_id_array);
        $this->db->where('SM.StatusID', 2);

        $query = $this->db->get();
        if ($query->num_rows())
        {
            $results = $query->result_array();
            foreach ($results as $result_item)
            {
                $skill_id = $result_item['SkillID'];
                $parameters[0]['ReferenceID'] = $skill_id;
                $parameters[0]['Type'] = 'Skills';
                $this->notification_model->add_notification(105, $user_id, array($result_item['ModuleEntityID']), $skill_id, $parameters);
            }
        }
        // Delete users skill 
        $this->skills_model->delete_data(ENTITYSKILLS, 'SkillID', $skill_id_array);

        // Delete Master Skill
        //$this->skills_model->delete_data(SKILLSMASTER, 'SkillID', $skill_id_array);
        $this->db->where_in('SkillID', $skill_id_array);
        $this->db->update(SKILLSMASTER, array('StatusID' => 3));

        // Delete Category relation
        $this->db->where_in('ModuleEntityID', $skill_id_array);
        $this->db->where('ModuleID', 29);
        $this->db->delete(ENTITYCATEGORY);
    }

    function skill_count_by_category($category_id)
    {
        $Return['SubCategoryCount'] = 0;
        $Return['SkillCount'] = 0;
        $Return['EndorsementsCount'] = 0;

        $category_data = get_data('CategoryID', CATEGORYMASTER, array('ParentID' => $category_id, 'ModuleID' => 29), '', '');
        $category_id_array = array($category_id);

        if ($category_data)
        {
            foreach ($category_data as $category_item)
            {
                $category_id_array[] = $category_item->CategoryID;
            }
        }

        if (!empty($category_id_array))
        {
            $Return['SubCategoryCount'] = count($category_id_array) - 1;
            $skill_data = $this->skills_model->get_category_skill_id($category_id_array);
            $Return['SkillCount'] = count($skill_data);
            if (!empty($skill_data))
            {
                $this->db->select('SUM(NoOfEndorsements) as NoOfEndorsements');
                $this->db->from(ENTITYSKILLS);
                $this->db->where_in('SkillID', $skill_data);
                $query = $this->db->get();
                $results = $query->row_array();
                $Return['EndorsementsCount'] = $results['NoOfEndorsements'];
            }
        }
        return $Return;
    }

    function get_category_skill_id($category_id_array)
    {
        $this->db->select('ModuleEntityID');
        $this->db->from(ENTITYCATEGORY);
        $this->db->where_in('CategoryID', $category_id_array);
        $query = $this->db->get();
        $results = $query->result_array();
        $ids_array = array();
        if ($results)
        {
            foreach ($results as $results_item)
            {
                $ids_array[] = $results_item['ModuleEntityID'];
            }
        }
        return $ids_array;
    }

    function check_category_by_name($category_name, $parent_id, $isparent)
    {
        if ($isparent)
        {
            $where = array('Name' => $category_name, 'ModuleID' => 29, 'ParentID' => 0);
        }
        else
        {
            $where = array('Name' => $category_name, 'ModuleID' => 29, 'ParentID ' => $parent_id);
        }

        // Check if category allready exist then get category id
        $check_category = get_data('CategoryID', CATEGORYMASTER, $where, 1, '');
        if ($check_category)
        {
            $category_id = $check_category->CategoryID;
        }
        else
        {
            $field_array['ModuleID'] = 29;
            $field_array['Name'] = $category_name;
            $field_array['Icon'] = '';
            $field_array['ParentID'] = $parent_id;
            $category_id = $this->skills_model->insert_data(CATEGORYMASTER, $field_array);
        }
        return $category_id;
    }

    function check_skill_by_name($skill_id, $skill_name, $entity_category_id = 0, $media_id = 0)
    {
        $Return['ResponseCode'] = '200';
        $Return['SkillID'] = '';
        if (!$entity_category_id)
            $entity_category_id = 0;
        if ($skill_id == '' && $skill_name != '')
        {
            $this->db->select('SkillID');
            $this->db->from(SKILLSMASTER . ' AS SM');
            $this->db->join(ENTITYCATEGORY . ' AS EC', 'EC.ModuleEntityID=SM.SkillID AND EC.ModuleID=29 AND EC.CategoryID=' . $entity_category_id);
            $this->db->where('SM.Name', $skill_name);
            $this->db->limit(1);
            $query = $this->db->get();
            if ($query->num_rows())
            {
                $results = $query->row_array();
                $skill_id = $results['SkillID'];
                $Return['ResponseCode'] = '509';
            }
            else
            {
                $field_array['Name'] = $skill_name;
                $field_array['MediaID'] = $media_id;
                $field_array['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $skill_id = $this->skills_model->insert_data(SKILLSMASTER, $field_array);
            }
        }

        $check_entity_category = get_data('*', ENTITYCATEGORY, array('CategoryID' => $entity_category_id, 'ModuleID' => 29, 'ModuleEntityID' => $skill_id), 1, '');
        if (!$check_entity_category)
        {
            $entity_array['CategoryID'] = $entity_category_id;
            $entity_array['EntityCategoryGUID'] = get_guid();
            $entity_array['ModuleID'] = 29;

            $entity_array['ModuleEntityID'] = $skill_id;
            $entity_array['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $this->skills_model->insert_data(ENTITYCATEGORY, $entity_array);
        }
        $Return['SkillID'] = $skill_id;

        return $Return;
    }

    function get_skills_by_id($skill_id)
    {
        $this->db->select(" SM.SkillID as ID, SM.Name ,COUNT(ES.SkillID) as ProfileCount,IFNULL(CM.Name,'') as CategoryName,IFNULL(CM1.Name,'') as ParentCategorName,IFNULL(CM.CategoryID,'') as CategoryID,IFNULL(CM1.CategoryID,'') as ParentCategoryID", false);
        //  $this->db->select("IF((SM.MediaID != NULL && SM.MediaID !=0 ),'M.ImageName','test') as ImageName ",FALSE);
        $this->db->select('IF(SM.MediaID="" || SM.MediaID IS NULL || SM.MediaID=0,"",M.ImageName) as SkillImageName', FALSE);

        $this->db->from(SKILLSMASTER . " AS SM ");
        $this->db->join(ENTITYSKILLS . " AS ES ", "ES.SkillID=SM.SkillID AND (ES.StatusID=1 OR ES.StatusID=2)", "left");
        $this->db->join(ENTITYCATEGORY . " AS EC ", "SM.SkillID=EC.ModuleEntityID AND EC.ModuleID=29");
        $this->db->join(CATEGORYMASTER . " AS CM ", "CM.CategoryID=EC.CategoryID AND CM.ModuleID=29", "left");
        $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left");
        $this->db->join(MEDIA . " AS M ", "M.MediaID=SM.MediaID ", "left");
        $this->db->where("SM.SkillID ", $skill_id);
        $query = $this->db->get();
        // echo $this->db->last_query();die;
        $results_item = $query->row_array();


        $results_item['CategoryImageName'] = '';
        if (empty($results_item['SkillImageName']))
        {

            $category_id = $results_item['ParentCategoryID'];
            if (!$category_id)
            {
                $category_id = $results_item['CategoryID'];
            }
            $category_data = $this->get_category_icon($category_id);

            if (!empty($category_data))
            {
                $results_item['CategoryImageName'] = $category_data['CategoryImageName'];
            }
        }
        return $results_item;
    }

    function get_skills($Input, $page_no, $page_size, $start_date, $end_date)
    {
        $Return = array();
        $Return['Data'] = array();
        $this->db->select(" SM.SkillID as ID, SM.Name ,COUNT(ES.SkillID) as ProfileCount,IFNULL(CM.Name,'') as CategoryName,IFNULL(CM1.Name,'') as ParentCategorName,IFNULL(CM.CategoryID,'') as CategoryID,IFNULL(CM1.CategoryID,'') as ParentCategoryID", false);
        //  $this->db->select("IF((SM.MediaID != NULL && SM.MediaID !=0 ),'M.ImageName','test') as ImageName ",FALSE);
        $this->db->select('IF(SM.MediaID="" || SM.MediaID IS NULL || SM.MediaID=0,"",M.ImageName) as SkillImageName', FALSE);
        $this->db->from(SKILLSMASTER . " AS SM ");
        $this->db->join(ENTITYSKILLS . " AS ES ", "ES.SkillID=SM.SkillID AND ES.StatusID=2", "left");
        $this->db->join(ENTITYCATEGORY . " AS EC ", "SM.SkillID=EC.ModuleEntityID AND EC.ModuleID=29", "left");
        $this->db->join(CATEGORYMASTER . " AS CM ", "CM.CategoryID=EC.CategoryID AND CM.ModuleID=29", "left");
        $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left");
        $this->db->join(MEDIA . " AS M ", "M.MediaID=SM.MediaID ", "left");
        $this->db->group_by("SM.SkillID");

        if (!empty($Input['search_key']))
        {
            $this->db->like('SM.Name', $Input['search_key']);
        }

        if (isset($Input['skill_type']) && $Input['skill_type'] == 'Popular')
        {
            $this->db->where("SM.StatusID ", 2);
            $this->db->limit("10");
            $this->db->having("ProfileCount > ", 0);
        }
        else if (isset($Input['skill_type']) && $Input['skill_type'] == 'Other')
        {
            $this->db->where("SM.StatusID ", 2);
            if (!empty($Input['skillID_array']))
            {
                $this->db->where_not_in('SM.SkillID', $Input['skillID_array']);
            }
        }
        else if (isset($Input['skill_type']) && $Input['skill_type'] == 'Pending')
        {
            $this->db->where("SM.StatusID ", 1);
        }
        else
        {
            $this->db->where("SM.StatusID ", 2);
        }

        $this->db->order_by('ProfileCount', 'DESC');

        if (!empty($Input['categoryID_array']))
        {
            $this->db->where_in('CM.CategoryID', $Input['categoryID_array']);
        }

        /* start_date, end_date for filters */
        if (!empty($start_date) && !empty($end_date))
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(ES.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '"', NULL, FALSE);
        }

        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $Return['total_records'] = $temp_q->num_rows();

        if ($page_size)
        {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }


        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $final_array = array();
        if ($query->num_rows() > 0)
        {
            $results = $query->result_array();

            foreach ($results as $results_item)
            {
                $results_item['CategoryImageName'] = '';
                if (empty($results_item['SkillImageName']))
                {

                    $category_id = $results_item['ParentCategoryID'];
                    if (!$category_id)
                    {
                        $category_id = $results_item['CategoryID'];
                    }
                    $category_data = $this->get_category_icon($category_id);

                    if (!empty($category_data))
                    {
                        $results_item['CategoryImageName'] = $category_data['CategoryImageName'];
                    }
                }
                $final_array[] = $results_item;
            }
        }
        // echo $this->db->last_query();die;
        if ($Input['skill_type'] == 'Popular')
        {
            $final_array = dynamic_values($final_array, 'ProfileCount', '101', '192');
        }

        $Return['Data'] = $final_array;
        //  print_r($results);die;
        return $Return;
    }

    function similar_skill($Input, $page_no, $page_size)
    {
        $Return = array();
        $this->db->select(" SM.SkillID as ID, SM.Name ,COUNT(ES.SkillID) as ProfileCount,IFNULL(CM.Name,'') as CategoryName,IFNULL(CM1.Name,'') as ParentCategorName", false);
        $this->db->from(SKILLSMASTER . " AS SM ");
        $this->db->join(ENTITYSKILLS . " AS ES ", "ES.SkillID=SM.SkillID AND ES.StatusID=2", "left");
        $this->db->join(ENTITYCATEGORY . " AS EC ", "SM.SkillID=EC.ModuleEntityID AND EC.ModuleID=29");
        $this->db->join(CATEGORYMASTER . " AS CM ", "CM.CategoryID=EC.CategoryID AND CM.ModuleID=29", "left");
        $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left");
        $this->db->group_by("SM.SkillID");

        if (!empty($Input['search_key']))
        {
            $this->db->like('SM.Name', $Input['search_key']);
            $this->db->or_like('CM.Name', $Input['search_key']);
        }
        if (!empty($Input['skillID_array']))
        {
            $this->db->where_not_in('SM.SkillID', $Input['skillID_array']);
        }
        $this->db->where("SM.StatusID ", 2);
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $Return['total_records'] = $temp_q->num_rows();
        if ($page_size)
        {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }

        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results = $query->result_array();
        $Return['Data'] = $results;
        //  print_r($results);die;
        return $Return;
    }

    function suggested_category($Input, $page_no, $page_size)
    {
        $Return = array();
        $this->db->select(" SM.SkillID as ID, SM.Name ,IFNULL(CM.Name,'') as SubCategoryName,IFNULL(CM1.Name,'') as CategorName,IFNULL(CM1.CategoryID,'') as CategoryID,IFNULL(CM.CategoryID,'') as SubCategoryID", false);
        $this->db->from(SKILLSMASTER . " AS SM ");
        $this->db->join(ENTITYCATEGORY . " AS EC ", "SM.SkillID=EC.ModuleEntityID AND EC.ModuleID=29 AND EC.CategoryID!=0");
        $this->db->join(CATEGORYMASTER . " AS CM ", "CM.CategoryID=EC.CategoryID AND CM.ModuleID=29", "left");
        $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left");
        $this->db->group_by('EC.CategoryID');
        if (!empty($Input['search_key']))
        {
            $this->db->like('SM.Name', $Input['search_key']);
        }

        $this->db->where("SM.StatusID ", 2);
        /*  $tempdb = clone $this->db;
          $temp_q = $tempdb->get();
          $Return['total_records'] = $temp_q->num_rows(); */
        if ($page_size)
        {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }

        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows() > 0)
        {
            $results = $query->result_array();
            $Return['Data'] = $results;
        }
        else
        {
            $this->db->select(" IFNULL(CM.Name,'') as SubCategoryName,IFNULL(CM1.Name,'') as CategorName,IFNULL(CM1.CategoryID,'') as CategoryID,IFNULL(CM.CategoryID,'') as SubCategoryID", false);

            $this->db->from(CATEGORYMASTER . " AS CM ");
            $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left");
            if (!empty($Input['search_key']))
            {
                $this->db->like('CM.Name', $Input['search_key']);
            }

            $this->db->where("CM.ModuleID ", 29);
            $this->db->where("CM.StatusID ", 2);
            $query = $this->db->get();
            $results = $query->result_array();
            $Return['Data'] = $results;
        }

        //  print_r($results);die;
        return $Return;
    }

    function categories($Input, $page_no, $page_size)
    {
        $Return = array();
        $this->db->select(" Name,CategoryID", false);
        $this->db->from(CATEGORYMASTER);

        $this->db->where('ModuleID', 29);
        $this->db->where('StatusID', 2);
        /*   if (empty($Input['search_key']))
          { */

        if ($Input['parent_id'])
        {
            $this->db->where('ParentID', $Input['parent_id']);
        }
        else
        {
            $this->db->where('ParentID =', 0);
        }
        //  }

        if (!empty($Input['search_key']))
        {
            $this->db->like('Name', $Input['search_key']);
        }
        if (!empty($Input['sort_by']) && !empty($Input['order_by']))
        {
            $this->db->order_by($Input['sort_by'], $Input['order_by']);
        }

        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $Return['total_records'] = $temp_q->num_rows();
        if (empty($Input['parent_id']) && empty($Input['search_key']))
        {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }

        $query = $this->db->get();
        $results = $query->result_array();

        $final_array = array();
        $parent_id_array = array();
        if ($results)
        {
            $i = 0;
            foreach ($results as $results_item)
            {
                $item_array = array();
                $sub_category = array();


                $item_array['SubCategories'] = $sub_category;
                $item_array['ID'] = $results_item['CategoryID'];
                $item_array['Name'] = $results_item['Name'];
                $parent_id_array[$item_array['ID']] = $i;
                $final_array[] = $item_array;
                $i++;
            }
        }

        if (!empty($Input['search_key']))
        {
            $sub_category_array = $this->skills_model->search_subcategory($Input['search_key']);
            if ($sub_category_array)
            {
                foreach ($sub_category_array as $sub_category_item)
                {
                    if (isset($parent_id_array[$sub_category_item['ParentID']]))
                    {
                        $final_array[$parent_id_array[$sub_category_item['ParentID']]]['SubCategories'][] = array('Name' => $sub_category_item['Name'], 'ID' => $sub_category_item['CategoryID']);
                    }
                    else
                    {
                        $parent_id_array[$sub_category_item['ParentID']] = count($parent_id_array);

                        $get_category = get_data('Name,CategoryID ', CATEGORYMASTER, array('CategoryID' => $sub_category_item['ParentID']), 1, '');

                        $parent_cat_array = array();
                        $parent_cat_array['Name'] = $get_category->Name;
                        $parent_cat_array['ID'] = $get_category->CategoryID;
                        $parent_cat_array['SubCategories'][] = array('Name' => $sub_category_item['Name'], 'ID' => $sub_category_item['CategoryID']);
                        $final_array[] = $parent_cat_array;
                    }
                }
            }
        }

        $Return['Data'] = $final_array;
        return $Return;
    }

    function search_subcategory($search_key)
    {
        $this->db->select(" Name,CategoryID,ParentID", false);
        $this->db->from(CATEGORYMASTER);
        $this->db->where('ModuleID', 29);
        $this->db->where('StatusID', 2);

        $this->db->where('ParentID !=', 0);
        if (!empty($search_key))
        {
            $this->db->like('Name', $search_key);
        }
        $query = $this->db->get();
        //echo $this->db->last_query();
        // die;
        $results = $query->result_array();
        return $results;
    }

    function merge_skills_details($Input, $skillID_array)
    {
        $Return = array();
        $this->db->select("SM.SkillID as ID,SM.Name as Name,SM.SkillID,COUNT(ES.SkillID) as ProfileCount,IFNULL(CM.Name,'') as CategoryName,IFNULL(CM1.Name,'') as ParentCategorName", FALSE);
        $this->db->from(SKILLSMASTER . " AS SM ");
        $this->db->join(ENTITYSKILLS . " AS ES ", "ES.SkillID=SM.SkillID", 'left');
        $this->db->join(ENTITYCATEGORY . " AS EC ", "SM.SkillID=EC.ModuleEntityID AND EC.ModuleID=29", 'left');
        $this->db->join(CATEGORYMASTER . " AS CM ", "CM.CategoryID=EC.CategoryID AND CM.ModuleID=29", "left");
        $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left");
        $this->db->where_in('SM.SkillID', $skillID_array);
        $this->db->group_by('SM.SkillID');
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $Return['total_records'] = $temp_q->num_rows();
        $query = $this->db->get();
        //echo $this->db->last_query();
        //die;
        $final_array = array();
        if ($query->num_rows())
        {
            $results = $query->result_array();
            foreach ($results as $item)
            {
                $item['Detail'] = array();
                $item['Detail'] = $this->skills_model->get_added_detail($item['SkillID'], 3);
                unset($item['SkillID']);
                $final_array[] = $item;
            }
        }
        $Return['Data'] = $final_array;
        //  print_r($results);die;
        return $Return;
    }

    function get_added_detail($skill_id, $limit)
    {
        
        $this->load->model('group/group_model');
        
        $Return = array();
        $this->db->select('ES.EntitySkillID,ES.ModuleID,ES.ModuleEntityID');

        $this->db->select('(CASE ES.ModuleID  
                                            WHEN 3 THEN PU.Url
                                            WHEN 18 THEN P.PageURL   
                                            ELSE "" END) AS ProfileURL', FALSE);

        $this->db->select('(CASE ES.ModuleID 
                                            WHEN 1 THEN if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg")
                                            WHEN 3 THEN IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture)
                                            WHEN 18 THEN IF(P.ProfilePicture="",CM1.Icon,P.ProfilePicture)   
                                            ELSE "" END) AS ProfilePicture', FALSE);

        $this->db->select('(CASE ES.ModuleID 
                                            WHEN 1 THEN G.GroupGUID 
                                            WHEN 3 THEN U.UserGUID 
                                            WHEN 18 THEN P.PageGUID ELSE "" END) AS ModuleEntityGUID', FALSE);

        $this->db->select('CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,""), " ",IFNULL(G.GroupName,""), "   ",IFNULL(P.Title,"")) AS Name', FALSE);
        $this->db->from(ENTITYSKILLS . " AS ES ");
        $this->db->join(USERS . " U", "U.UserID=ES.ModuleEntityID AND ES.ModuleID=3", "LEFT");
        $this->db->join(PAGES . " P", "P.PageID=ES.ModuleEntityID AND ES.ModuleID=18", "LEFT");
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->join(GROUPS . " G", "G.GroupID=ES.ModuleEntityID AND ES.ModuleID=1", "LEFT");
        $this->db->join(ENTITYCATEGORY . " EC", "EC.ModuleEntityID=G.GroupID AND EC.ModuleID=1", "LEFT");
        $this->db->join(CATEGORYMASTER . " CM", "CM.CategoryID = EC.CategoryID", "LEFT");
        $this->db->join(CATEGORYMASTER . " CM1", "CM1.CategoryID = P.CategoryID", "LEFT");
        $this->db->where_in('ES.SkillID', $skill_id);
        $this->db->limit($limit);
        $this->db->order_by("ES.CreatedDate", "DESC");
        $query = $this->db->get();

        $final_array = array();
        if ($query->num_rows())
        {
            $results = $query->result_array();
            foreach ($results as $item)
            {
                $module_id = $item['ModuleID'];
                if ($module_id == 1) {                   
                    $group_url_details = $this->group_model->get_group_details_by_id($item['ModuleEntityID'], '', array(
                        'GroupName' => $item['Name'],
                        'GroupGUID' => $item['ModuleEntityGUID'],
                    ));
                    $item['ProfileURL'] = $this->group_model->get_group_url($item['ModuleEntityID'], $group_url_details['GroupNameTitle'], false, 'index');       
                    
                }
                unset($item['EntitySkillID']);
                $final_array[] = $item;
            }
        }
        return $final_array;
    }

    function manage_endorsements($user_id, $skillID_array, $new_skill_id)
    {
        $Return = array();
        $this->db->select('ES.ModuleEntityID,ES.ModuleID,group_concat(DISTINCT ES.EntitySkillID) as EntitySkillID,group_concat(DISTINCT ES.SkillID) as SkillID, ES.DisplayOrder', FALSE);
        $this->db->from(ENTITYSKILLS . " AS ES ");
        $this->db->where_in('ES.SkillID', $skillID_array);
        $this->db->group_by('ES.ModuleID');
        $this->db->group_by('ES.ModuleEntityID');

        $query = $this->db->get();
        $final_array = array();
        $temp_skill_data = array();
        $temp_user_data = array();
        if ($query->num_rows() > 0)
        {
            foreach ($skillID_array as $item_skill_id)
            {
                $temp_skill_data[$item_skill_id] = $this->get_single_skill($item_skill_id);
            }

            $results = $query->result_array();
            foreach ($results as $item)
            {

                if ($item['ModuleID'] == 3)
                {
                    $user_temp_skill = explode(',', $item['SkillID']);
                    foreach ($user_temp_skill as $user_temp_skill_id)
                    {
                        $temp_user_data[$item['ModuleEntityID']][] = $user_temp_skill_id;
                    }
                }

                $this->db->query("DELETE EE1 FROM " . ENTITYENDORSEMENT . " EE1
                  , " . ENTITYENDORSEMENT . " EE2
                  WHERE EE1.ModuleEntityID = EE2.ModuleEntityID
                  AND EE1.EndorsementID < EE2.EndorsementID
                  AND EE1.EntitySkillID IN(" . $item['EntitySkillID'] . ")
                  AND EE1.ModuleID=" . $item['ModuleID'] . " ");

                $this->db->select('COUNT(ModuleEntityID) as NoOfEndorsements');
                $this->db->from(ENTITYENDORSEMENT);
                $this->db->where_in('EntitySkillID', explode(',', $item['EntitySkillID']));
                $this->db->group_by('ModuleID');

                $query = $this->db->get();

                $NoOfEndorsements = 0;
                if ($query->num_rows())
                {
                    $results_row = $query->row_array();
                    $NoOfEndorsements = $results_row['NoOfEndorsements'];
                }
                $data_insert_array = array();
                $data_insert_array['EntitySkillGUID'] = get_guid();
                $data_insert_array['ModuleEntityID'] = $item['ModuleEntityID'];
                $data_insert_array['ModuleID'] = $item['ModuleID'];
                $data_insert_array['SkillID'] = $new_skill_id;
                $data_insert_array['DisplayOrder'] = $item['DisplayOrder'];
                $data_insert_array['NoOfEndorsements'] = $NoOfEndorsements;
                $data_insert_array['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');

                $entity_skill_id = $this->skills_model->insert_data(ENTITYSKILLS, $data_insert_array);

                $this->skills_model->delete_data(ENTITYSKILLS, 'EntitySkillID', explode(',', $item['EntitySkillID']));

                $this->db->where_in('EntitySkillID', explode(',', $item['EntitySkillID']));
                $this->db->where('ModuleID', $item['ModuleID']);
                $this->db->update(ENTITYENDORSEMENT, array('EntitySkillID' => $entity_skill_id));
            }

            $this->db->where_in('SkillID', $skillID_array);
            $this->db->update(SKILLSMASTER, array('StatusID' => 3, 'NoOfProfiles' => 0, 'AddedBy' => 0));
            //$this->skills_model->delete_data(SKILLSMASTER, 'SkillID', $skillID_array);

            $this->db->where_in('ModuleEntityID', $skillID_array);
            $this->db->where('ModuleID', 29);
            $this->db->delete(ENTITYCATEGORY);

            $entity_skill_profile = get_data('COUNT(NoOfEndorsements) as ProfileCount', ENTITYSKILLS, array('SkillID' => $new_skill_id), '1', '');

            $this->db->where('SkillID', $new_skill_id);
            $this->db->update(SKILLSMASTER, array('NoOfProfiles' => $entity_skill_profile->ProfileCount));

            if (!empty($temp_user_data))
            {

                 foreach ($temp_user_data as $key => $val)
                  {
                  $temp_user_skill_data = array();
                  foreach ($val as $val_item)
                  {
                  $temp_user_skill_data[] = array('SkillImageName' => $temp_skill_data[$val_item]['SkillImageName'],'CategoryImageName' => $temp_skill_data[$val_item]['CategoryImageName'],'Name' => $temp_skill_data[$val_item]['Name'], 'CategoryName' => $temp_skill_data[$val_item]['CategoryName'], 'ParentCategorName' => $temp_skill_data[$val_item]['ParentCategorName']);
                  }
                  $parameters[0]['ReferenceID'] = $new_skill_id;
                  $parameters[0]['Type'] = 'Skills';
                  $parameters[0]['SkillData']['OldData'] = $temp_user_skill_data;
                  $this->notification_model->add_notification(107, $user_id, array($key), $new_skill_id, $parameters);
                  } 
            }
        }
        else
        {
            //$this->skills_model->delete_data(SKILLSMASTER, 'SkillID', $skillID_array);

            $this->db->where_in('SkillID', $skillID_array);
            $this->db->update(SKILLSMASTER, array('StatusID' => 3, 'NoOfProfiles' => 0, 'AddedBy' => 0));

            $this->db->where_in('ModuleEntityID', $skillID_array);
            $this->db->where('ModuleID', 29);
            $this->db->delete(ENTITYCATEGORY);
        }
        return $Return;
    }

    function add_similar_skills($skill_id, $skillID_array)
    {
        if (!empty($skill_id))
        {
            $this->skills_model->delete_data(SIMILARSKILLS, 'SkillID', array($skill_id));
            $this->skills_model->delete_data(SIMILARSKILLS, 'SimilarID', array($skill_id));
            $final_array = array();
            if (!empty($skillID_array))
            {
                foreach ($skillID_array as $skillID_array_item)
                {
                    $insert_array = array();
                    $insert_array['SkillID'] = $skill_id;
                    $insert_array['SimilarID'] = $skillID_array_item;
                    $insert_array['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $final_array[] = $insert_array;

                    $insert_array = array();
                    $insert_array['SkillID'] = $skillID_array_item;
                    $insert_array['SimilarID'] = $skill_id;
                    $insert_array['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $final_array[] = $insert_array;
                }
            }
            if (!empty($final_array))
            {
                $this->db->insert_batch(SIMILARSKILLS, $final_array);
            }
        }
    }

    function get_single_skill($skill_id)
    {
        $results = array();
        $this->db->select(" SM.SkillID as ID, SM.Name ,COUNT(ES.SkillID) as ProfileCount,IFNULL(CM.CategoryID,'') as CategoryID,IFNULL(CM1.CategoryID,'') as ParentCategoryID,IFNULL(CM.Name,'') as CategoryName,IFNULL(CM1.Name,'') as ParentCategorName", false);
        //  $this->db->select("IF((SM.MediaID != NULL && SM.MediaID !=0 ),'M.ImageName','test') as ImageName ",FALSE);
        $this->db->select('IF((SM.MediaID = NULL || SM.MediaID = 0),"",M.ImageName) as SkillImageName', false);
        $this->db->select('IF((M.MediaGUID = NULL),"",M.MediaGUID) as MediaGUID', false);

        $this->db->from(SKILLSMASTER . " AS SM ");
        $this->db->join(ENTITYSKILLS . " AS ES ", "ES.SkillID=SM.SkillID AND ES.StatusID=2", "left");
        $this->db->join(ENTITYCATEGORY . " AS EC ", "SM.SkillID=EC.ModuleEntityID AND EC.ModuleID=29");
        $this->db->join(CATEGORYMASTER . " AS CM ", "CM.CategoryID=EC.CategoryID AND CM.ModuleID=29", "left");
        $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left");
        $this->db->join(MEDIA . " AS M ", "M.MediaID=SM.MediaID ", "left");
        $this->db->where("SM.SkillID ", $skill_id);

        $query = $this->db->get();
        // echo $this->db->last_query();die;

        if ($query->num_rows() > 0)
        {
            $results = $query->row_array();
            $results['CategoryImageName'] = '';
            $results['similarSkill'] = $this->get_similar_skill_by_id($skill_id);
            //$results['MediaGUID'] = '';
            if (empty($results['SkillImageName']))
            {


                $category_id = $results['ParentCategoryID'];
                if (!$category_id)
                {
                    $category_id = $results['CategoryID'];
                }
                $category_data = $this->get_category_icon($category_id);

                if (!empty($category_data))
                {
                    $results['CategoryImageName'] = $category_data['CategoryImageName'];
                    $results['MediaGUID'] = $category_data['MediaGUID'];
                }
            }
        }
        return $results;
    }

    function get_category_icon($category_id)
    {
        $this->db->select('IF((CM.MediaID IS NULL || CM.MediaID = 0 || CM.MediaID = ""),"",M.ImageName) as CategoryImageName', false);
        $this->db->select('IF((M.MediaGUID = NULL),"",M.MediaGUID) as MediaGUID', false);
        $this->db->from(CATEGORYMASTER . " AS CM ");
        $this->db->join(MEDIA . " AS M ", "M.MediaID=CM.MediaID ", "left");
        $this->db->where('CM.CategoryID', $category_id);
        $query = $this->db->get();

        return $results = $query->row_array();
    }

    function get_similar_skill_by_id($skill_id)
    {
        $Return = array();
        $this->db->select(" SM.SkillID as ID, SM.Name ,IFNULL(CM.Name,'') as CategoryName,IFNULL(CM1.Name,'') as ParentCategorName", false);
        $this->db->from(SKILLSMASTER . " AS SM ");
        $this->db->join(SIMILARSKILLS . " AS SS ", "SS.SimilarID=SM.SkillID ");
        $this->db->join(ENTITYCATEGORY . " AS EC ", "SM.SkillID=EC.ModuleEntityID AND EC.ModuleID=29");
        $this->db->join(CATEGORYMASTER . " AS CM ", "CM.CategoryID=EC.CategoryID AND CM.ModuleID=29", "left");
        $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left");
        $this->db->where('SS.SkillID', $skill_id);
        $query = $this->db->get();
        $results = $query->result_array();
        return $results;
    }

    function get_skills_by_category($category_id, $module_id = 3, $module_entity_id = 0)
    {
        $this->db->select(" SM.SkillID as ID, SM.Name ,IFNULL(CM.Name,'') as CategoryName,IFNULL(CM1.Name,'') as ParentCategorName,IFNULL(CM.CategoryID,'') as CategoryID,IFNULL(CM1.CategoryID,'') as ParentCategoryID", FALSE);
        //  $this->db->select("IF((SM.MediaID != NULL && SM.MediaID !=0 ),'M.ImageName','test') as ImageName ",FALSE);
        $this->db->select('IF(SM.MediaID="" || SM.MediaID IS NULL || SM.MediaID=0,"",M.ImageName) as SkillImageName', FALSE);

        $this->db->from(SKILLSMASTER . " AS SM ", FALSE);
        $this->db->join(ENTITYSKILLS . " AS ES ", "ES.SkillID=SM.SkillID AND ES.StatusID=2 AND ES.ModuleID='" . $module_id . "' ", "left", FALSE);
        $this->db->join(ENTITYCATEGORY . " AS EC ", "SM.SkillID=EC.ModuleEntityID AND EC.ModuleID=29");
        $this->db->join(CATEGORYMASTER . " AS CM ", "CM.CategoryID=EC.CategoryID AND CM.ModuleID=29", "left", FALSE);
        $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left", FALSE);
        $this->db->join(MEDIA . " AS M ", "M.MediaID=SM.MediaID ", "left", FALSE);
        //$this->db->where("EC.CategoryID ", $category_id);
        //$this->db->or_where("CM.ParentID ", $category_id);


        $this->db->where(" (EC.CategoryID='" . $category_id . "' OR CM.ParentID= '" . $category_id . "' )", '', FALSE);
        $this->db->where("ES.ModuleEntityID ", $module_entity_id);
        $this->db->group_by("SM.SkillID");
        $query = $this->db->get();
        // echo $this->db->last_query();die;

        $final_result = array();
        if ($query->num_rows() > 0)
        {
            $results = $query->result_array();
            foreach ($results as $results_item)
            {
                $results_item['CategoryImageName'] = '';
                if (empty($results_item['SkillImageName']))
                {

                    $category_id = $results_item['ParentCategoryID'];
                    if (!$category_id)
                    {
                        $category_id = $results_item['CategoryID'];
                    }
                    $category_data = $this->get_category_icon($category_id);

                    if (!empty($category_data))
                    {
                        $results_item['CategoryImageName'] = $category_data['CategoryImageName'];
                    }
                }

                $final_result[] = $results_item;
            }
        }


        return $final_result;
    }

}

//End of file users_model.php
