<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Description of Activity_model
 *
 * 
 */
class Activity_entities_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_user_activity_entities($user_id, $page_no = 1, $page_size = 20, $filter = array(), $format = 1) {
        
        if(empty($filter)) {
            $filter = array(
                'GROUPS' => 'MYOWN',
                'FORUMCATEGORY' => 'MYOWN'
            );
        }
        
        $this->load->model(array('group/group_model', 'forum/forum_model'));
        if($filter['GROUPS'] != 'MYOWN') {
            $group_list = $this->group_model->get_visible_groups($user_id);
        } 
        
        if($filter['FORUMCATEGORY'] != 'MYOWN') {
            $category_group_list = $this->forum_model->get_visible_categories($user_id);
        }
        
        
        $forum_cat_sql = '';
        if (!empty($category_group_list) || $filter['FORUMCATEGORY'] == 'MYOWN') {
            
            $where_conds = '';
            $forum_member_join = '';
            if($filter['FORUMCATEGORY'] != 'MYOWN') {
                $where_conds = ' AND FC.ForumCategoryID IN(   '.implode(",", $category_group_list).'   )';
            } else {
                $forum_member_join = "Inner Join ".FORUMCATEGORYMEMBER." FCM  ON FC.ForumCategoryID=FCM.ForumCategoryID AND FCM.ModuleID='3' AND FCM.ModuleEntityID='".$user_id."'";
            }
            
            $forum_cat_sql = ' 
                SELECT 
                FC.ForumCategoryGUID AS ModuleEntityGUID, FC.ForumCategoryID AS ModuleEntityID,FC.Name as Name ,34 AS ModuleID, IFNULL(M.ImageName, "") As Image
                FROM '.FORUMCATEGORY.' as FC 
                '.$forum_member_join.'
                LEFT JOIN '.MEDIA.' AS M ON M.MediaID = FC.MediaID
                WHERE FC.StatusID=2  
                '.$where_conds.'
            ';
        }
        
        $group_sql = '';
        if (!empty($group_list) || $filter['GROUPS'] == 'MYOWN') {
            $where_conds = '';
            $group_member_join = '';
            if($filter['GROUPS'] != 'MYOWN') {
                $where_conds = ' AND G.GroupID IN(     '.implode(",", $group_list) .'    )';
            } else {
                $group_member_join = 'Inner Join '.GROUPMEMBERS.' GM ON GM.GroupID=G.GroupID AND GM.ModuleID="3" AND GM.ModuleEntityID="'.$user_id.'"';
            }
            
            $group_sql = ' 
                    SELECT 
                    G.GroupGUID AS ModuleEntityGUID, G.GroupID AS ModuleEntityID,G.GroupName as Name,1 AS ModuleID, G.GroupImage AS Image
                    FROM '.GROUPS.' as G 
                    '.$group_member_join.'
                    WHERE G.Type="FORMAL" 
                    AND G.StatusID=2 
                    '.$where_conds.'
            ';
        }
                
        // Pages query
        $pages_sql = "
            SELECT P.PageGUID AS ModuleEntityGUID, P.PageID AS ModuleEntityID, P.Title AS Name, 18 AS ModuleID, P.ProfilePicture As Image
            FROM ".PAGES." P 
            Inner Join ".PAGEMEMBERS." PM ON PM.PageID = P.PageID
            WHERE P.StatusID = 2  AND PM.UserID = '.$user_id.'
        "; 
        
        
        // Events query
        $events_sql = '
            SELECT  E.EventGUID AS ModuleEntityGUID, E.EventID AS ModuleEntityID, E.Title AS Name, 14 AS ModuleID, IFNULL(M.ImageName, "") As Image
            FROM '.EVENTS.' E
            Inner JOIN '.EVENTUSERS . ' AS EU ON EU.EventID=E.EventID
            
            LEFT JOIN '.ENTITYCATEGORY . ' AS EC ON EC.ModuleEntityID=E.EventID AND EC.ModuleID=14
            LEFT JOIN '.CATEGORYMASTER . ' AS CM ON CM.CategoryID=EC.CategoryID
            
            LEFT JOIN '.USERS . ' AS U ON U.UserID=E.CreatedBy
            LEFT JOIN '.MEDIA.' AS M ON M.MediaID = E.ProfileImageID
            
            WHERE E.IsDeleted=0 AND EU.UserID = '.$user_id.'
            
            AND (EU.ModuleRoleID IN (1, 2) OR EU.ModuleRoleID = 3 AND EU.Presence = "ATTENDING")
            
            Group By  E.EventID
        ';
     
        
        // Prepare union of all queries.
        $union_sqls = '';
        $union_sqlsArr = array(
            $group_sql,
            $forum_cat_sql,
            $pages_sql,
            $events_sql,
        );
        
        foreach($union_sqlsArr as $union_sql) {
            if(!$union_sql) {
                continue;
            }
            
            if(!$union_sqls) {
                $union_sqls = "($union_sql)";
            } else {
                $union_sqls .= " Union All ($union_sql)";
            }
            
        }
 
        $offset = $this->get_pagination_offset($page_no, $page_size);
        
        $union_sqls .= " ORDER BY ModuleID, Name ASC Limit $page_size OFFSET $offset ";    //echo $union_sqls; die;
        $query = $this->db->query($union_sqls);
        //echo $this->db->last_query(); die;
        
        $entities = $query->result_array();
        
        if($format) {
            $entities = $this->user_activities_entities_fomatter($entities);
        }
        
        return $entities;
        
    }
    
    protected function user_activities_entities_fomatter($entities) {
        $modules = array(1 => 'GROUP', 14 => 'EVENT', 18 => 'PAGE', 34 => 'FORUMCATEGORY');
        $formatted_entities = array(
            $modules[1] => [],
            $modules[14] => [],
            $modules[18] => [],
            $modules[34] => [],
        );
        
        foreach ($entities as $entity) {
            $formatted_entities[$modules[$entity['ModuleID']]][] = $entity;
        }
        
        return $formatted_entities;
    }

    public function get_pagination_offset($PageNo, $Limit) {
        if(empty($PageNo)) 
        {
            $PageNo = 1;
        }
        $offset = ($PageNo-1)*$Limit;
        return $offset;
    }

}
