<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Forum_user_categories_model extends Common_Model {

    protected $module_id = '';
    protected $user_page_list = array();
    protected $feed_page_condition = '';
    protected $user_category_list = array();
    protected $visible_category_list = array();

    public function __construct() {
        parent::__construct();
        $this->module_id = 33;
        $this->load->model(array('group/group_model', 'activity/activity_model', 'notification/notification_model', 'users/user_model', 'forum/forum_model'));
    }
    
    /**
     * Function: lists
     * Description : List of categories
     * @param type $user_id
     * @param type $page_no
     * @param type $page_size
     * @return array
     */
    function lists($user_id, $page_no, $page_size, $only_selected = false, $search = '') {
        $result = array();
        $cache_id = ($only_selected) ? 'user_visible_categories_only_selected' . $user_id : 'user_visible_categories_' . $user_id;
        if (CACHE_ENABLE) {
            //$this->cache->delete('user_categories_'.$user_id);
            $result = $this->cache->get($cache_id);
            if ($result) {
                return $result;
            }
        }
        if (!$result) {
            $join_type = ($only_selected) ? 'inner' : 'left';
            $user_groups_array = $this->group_model->get_users_groups($user_id);
            $user_groups = 0;
            if (!empty($user_groups_array)) {
                $user_groups = implode(',', $user_groups_array);
            }
            $this->db->select("SQL_CALC_FOUND_ROWS FC.*, IF(FUC.ForumCategoryID IS NULL, 0, 1) AS Selected, IFNULL(M.ImageName,'') as ProfilePicture, M.MediaGUID, F.URL FURL", false);
            $this->db->from(FORUMCATEGORY . ' FC');
            $this->db->join(FORUMUSERCATEGORY . ' FUC', 'FUC.ForumCategoryID=FC.ForumCategoryID AND FUC.UserID = '.$user_id.' ', $join_type);
            $this->db->join(MEDIA . ' M', 'FC.MediaID = M.MediaID','left');
            $this->db->join(FORUM . ' F', 'F.ForumID = FC.ForumID','left');
            $this->db->join(FORUMCATEGORYMEMBER . ' FCM', "FC.ForumCategoryID=FCM.ForumCategoryID AND FCM.ModuleID='3' AND FCM.ModuleEntityID='" . $user_id . "'", 'left');
            $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
            
            
            $condition = " CASE 
                                WHEN 
                                    FC.Visibility=2
                                    THEN 
                                    ( CASE 
                                            WHEN FCV.ModuleID = 3 
                                                THEN FCV.ModuleEntityID = " . $user_id . "  
                                            WHEN FCV.ModuleID = 1 
                                                THEN FCV.ModuleEntityID IN (" . $user_groups . ")
                                            WHEN FCM.ModuleID = 3 
                                                THEN FCM.ModuleEntityID = " . $user_id . "  
                                            WHEN FCM.ModuleID = 1 
                                                THEN FCM.ModuleEntityID IN (" . $user_groups . ")  
                                            WHEN (FCM.ModuleID is null AND FCV.ModuleID is null)
                                            	THEN false             
                                        ELSE
                                        '' 
                                        END 
                                    )
                        ELSE
                        true 
                        END 
                        
                        AND FC.ParentCategoryID = 0
                        
                        AND FC.StatusID = 2

                    ";
            
            if($search) {
                $search = $this->db->escape_like_str($search);
                $this->db->where("FC.Name Like '%$search%'", NULL, FALSE);
            }
            
            
            $this->db->where($condition, NULL, FALSE);
            $this->db->group_by('FC.ForumCategoryID');
            //$this->db->order_by('FC.ForumCategoryID', 'DESC');
            
            $offset = $this->get_pagination_offset($page_no, $page_size);
            
            if(!$only_selected) {
                $this->db->limit($page_size, $offset);
            }
            
            $query = $this->db->get();
            // echo $this->db->last_query();die;
            $result = $query->result_array();
            
            $this->db->select('FOUND_ROWS() AS Count',false);
            $query = $this->db->get();
            $total_cateogries = $query->row_array()['Count'];
            
            if($only_selected) {
                foreach ($result as $key => $row) {
                     $permission =$this->forum_model->check_forum_category_permissions($user_id, $row['ForumCategoryID'],FALSE);
                   // $permission =$this->forum_model->check_forum_category_permissions($user_id, $row['ForumCategoryID'],FALSE);
                    //$all_categories = $this->forum_model->get_forum_category($row['ForumID'],$user_id,$permission); 
                    //$subcategories = $this->forum_model->get_subcategory($row['ForumCategoryID'],$user_id,$row['ForumID'],$permission);
                     unset($permission['Details']);
                     $subcategories=$this->forum_model->get_forum_subcategory($row['ForumCategoryID'],$user_id,$row['ForumID'],$permission);
                    $result[$key]['all_categories'] = $subcategories;
                }
            }
            
        }

        if ($result && CACHE_ENABLE) {
            //$this->cache->save($cache_id, $result);
        }
        return array(
            'total' => $total_cateogries,
            'entities' => $result
        );
    }
    
    
    /**
     * Function: save_user_categories
     * Description : Save user categories
     * @param type $user_id
     * @param type $categories
     * @return array
     */
    function save_user_categories($user_id, $categories, $only_remove = false) {
        $this->db->select('ForumCategoryID');
        $this->db->from(FORUMUSERCATEGORY);
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        
        $db_categories = [];
        foreach ($query->result_array() as $cat) {
            $db_categories[] = $cat['ForumCategoryID'];
        }
        
        $new_categories = array_diff($categories, $db_categories);
        $deleted_categories = array_diff($db_categories, $categories);
        
        if($only_remove) {
            $deleted_categories = $categories;
        }
        
        if(count($deleted_categories)) {
            $this->db->where_in('ForumCategoryID', $deleted_categories); 
            $this->db->where('UserID', $user_id); 
            $this->db->delete(FORUMUSERCATEGORY);
            
            if($only_remove) {
                return;
            }
        }
        
        
        $insert_arr = [];
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        foreach ($new_categories as $new_category) {
            $insert_arr[] = array(
                'ForumCategoryID' => $new_category,
                'UserID' => $user_id,
                'CreatedDate' => $current_date,
                'ModifiedDate' => $current_date,
            );
        }
        
        if(count($insert_arr)) {
            $this->db->insert_batch(FORUMUSERCATEGORY, $insert_arr);
        }
        
    }

}
