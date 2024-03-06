<?php
/**
 * This model is used for getting and storing Catetgory related information
 * @package    Category_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Category_model extends Common_Model {

    function __construct() 
    {
        parent::__construct();
        $this->load->database();
    }
    
    /*-------------------------------------------------
	| @Method : To get master categories
	| @params : module_id(Int),$parent_category_id(Int)
	| @Output : array     
	-------------------------------------------------*/
	function get_categories($module_id = 14,$parent_category_id=0,$user_id=0)
	{
            $data=array();
            if (CACHE_ENABLE) {
                $data = $this->cache->get('api_category_'.$module_id.'_'.$parent_category_id);
            }
            if(empty($data))
            {
                $condition = array('C.ModuleID'=>$module_id,"C.ParentID"=>0);// If only root category needed
                if(!empty($parent_category_id))
                {
                    $condition['C.ParentID'] = $parent_category_id; // If specific level of category needed
                }
                $select_followers = "(SELECT COUNT(CategoryID) FROM ".ENTITYCATEGORY." WHERE ModuleID='3' AND CategoryID=C.CategoryID) as Followers";
                $this->db->select('C.CategoryID,C.ModuleID,C.ParentID'); //,C.ParentID as Followers
                if($module_id!=18) {
                    $this->db->select($select_followers,FALSE);
                }
                $this->db->select('C.Name', FALSE);
                $this->db->select('C.Description', FALSE);
                $this->db->select('C.Icon', FALSE);
                $this->db->select('IF(MD.ImageName="" || MD.ImageName IS NULL || MD.ImageName=0,"",MD.ImageName) as ImageName', FALSE);
                $this->db->select('MD.MediaGUID AS MediaGUID', FALSE);
                $this->db->select('M.ModuleName', FALSE);

               /* if($user_id)
                {
                     $this->db->select("IF(EC.CategoryID is not NULL,1,0) as IsInterested",false);
                     $this->db->join(ENTITYCATEGORY . ' EC', "C.CategoryID=EC.CategoryID AND EC.ModuleID=3 AND EC.ModuleEntityID='" . $user_id . "'", 'left outer');
                }
                else
                {*/
                     $this->db->select("'0' as IsInterested",false);
                //}
                $this->db->join(MEDIA . ' MD', 'MD.MediaID = C.MediaID', 'LEFT');
                $this->db->join(MODULES . ' M', 'M.ModuleID = C.ModuleID', 'LEFT'); 
                $this->db->from(CATEGORYMASTER . "  C");  
                $this->db->select('S.StatusID'); //S.StatusName as status, 
                $this->db->join(STATUS . ' S', 'S.StatusID=C.StatusID'); 
                $this->db->where($condition);
                $this->db->where('C.StatusID',2);

                $query = $this->db->get(); 
                $data=$query->result_array(); 
                if (CACHE_ENABLE) {
                    $this->cache->save('api_category_'.$module_id.'_'.$parent_category_id, $data,CACHE_EXPIRATION);
                }
            }
            if($module_id==31) {
                $temp_data=array();
                foreach($data as $item)
                {
                   $item['IsInterested'] =$this->check_intrest_category($user_id,$item['CategoryID']);
                   $temp_data[]= $item;       
                }
                $data=$temp_data;
                 /*$this->db->select("IF(EC.CategoryID is not NULL,1,0) as IsInterested",false);
                 $this->db->join(ENTITYCATEGORY . ' EC', "C.CategoryID=EC.CategoryID AND EC.ModuleID=3 AND EC.ModuleEntityID='" . $user_id . "'", 'left outer');*/
            }
         return $data;
    }

    /*-------------------------------------------------
    | @Method : To get master categories
    | @params : module_id(Int),$parent_category_id(Int)
    | @Output : array     
    -------------------------------------------------*/
    function get_interests($module_id=31,$parent_category_id=0,$user_id=0)
    {
        $data=array();
        if(empty($data))
        {
            $condition = array('C.ModuleID'=>$module_id,"C.ParentID"=>0);// If only root category needed
            if(!empty($parent_category_id))
            {
                $condition['C.ParentID'] = $parent_category_id; // If specific level of category needed
            }
            $select_followers = "(SELECT COUNT(CategoryID) FROM ".ENTITYCATEGORY." WHERE ModuleID='3' AND CategoryID=C.CategoryID) as Followers";
            $this->db->select('C.CategoryID,C.ModuleID,C.ParentID,C.ParentID as Followers');
            $this->db->select($select_followers,FALSE);
            $this->db->select('C.Name', FALSE);
            $this->db->select('C.Description', FALSE);
            $this->db->select('C.Icon', FALSE);
            $this->db->select('IF(MD.ImageName="" || MD.ImageName IS NULL || MD.ImageName=0,"",MD.ImageName) as ImageName', FALSE);
            $this->db->select('MD.MediaGUID AS MediaGUID', FALSE);
            $this->db->select('M.ModuleName', FALSE);
            $this->db->select("'0' as IsInterested",false);
            $this->db->join(MEDIA . ' MD', 'MD.MediaID = C.MediaID', 'LEFT');
            $this->db->join(MODULES . ' M', 'M.ModuleID = C.ModuleID', 'LEFT'); 
            $this->db->from(CATEGORYMASTER . "  C");  
            $this->db->select('S.StatusName as status, S.StatusID');
            $this->db->join(STATUS . ' S', 'S.StatusID=C.StatusID'); 
            $this->db->where($condition);
            $this->db->where('C.StatusID',2);

            $query = $this->db->get();
            if($query->num_rows() > 0 && $parent_category_id == 0)
            {
                foreach($query->result_array() as $result)
                {
                    $result['interest'] = $this->get_interests($module_id,$result['CategoryID'],$user_id);
                    $data[] = $result;
                }
            }
            else
            {
                $data = $query->result_array();
            }
        }
        if($module_id==31 && $parent_category_id!==0)
        {
           $temp_data=array();
           foreach($data as $item)
           {
              $item['IsInterested'] =$this->check_intrest_category($user_id,$item['CategoryID']);
              $temp_data[]= $item;       
           }
           $data=$temp_data;
        }
        return $data;
    }
    
    function check_intrest_category($user_id, $category_id)
    {
        $this->db->select("IF(CategoryID is not NULL,1,0) as IsInterested",false);
        $this->db->from(ENTITYCATEGORY);
        $this->db->where('CategoryID ='.$category_id.' AND ModuleID=3 AND ModuleEntityID='.$user_id,NULL);
        $this->db->limit(1);
        $query = $this->db->get(); 
        $data=$query->row_array();
        if(!empty($data))
        {
            return $data['IsInterested'];
        }else
        {
            return 0;
        }
    }
    /*-------------------------------------------------
	| @Method - To update category entity id
	| @Params - CategoryID(array)
	| @Output - int/bool
	--------------------------------------------------*/
	function update_entity_category($category_ids, $entity_id)
	{
		if(!empty($category_ids) && is_array($category_ids))
		{
			// Remove categories code here
			$this->db->where('ModuleEntityID', $entity_id);
			$this->db->delete(ENTITYCATEGORY);
			$insert_data = array();
			// Prepare data for insertion
			foreach($category_ids as $key=>$category_id)
			{
				$insert_data[$key]['CategoryID'] = $category_id;
				$insert_data[$key]['EntityCategoryGUID'] = get_guid();
				$insert_data[$key]['ModuleEntityID'] = $entity_id;
				$insert_data[$key]['ModuleID'] = 14; // ID 14 for Event module
				$insert_data[$key]['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
			}
			$this->db->insert_batch(ENTITYCATEGORY, $insert_data); 
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
     * [insert_update_category Used to insert/update any module entity category]
     * @param  [array] $category_ids        [array of category id]
     * @param  [int] $module_id           [module id]
     * @param  [int] $module_entity_id     [module entity id]
     * @return [bool]                     [true/false]
     */
    function insert_update_category($category_ids, $module_id, $module_entity_id, $action='update') 
    {
        if(count($category_ids) > 0 && !empty($category_ids) && is_array($category_ids)) 
        {
            $entity_categories = array();
 			$insert_data = array();
            foreach($category_ids as $key=>$category_id) 
            {
                $this->db->select("EntityCategoryGUID");
                $this->db->from(ENTITYCATEGORY);
                $this->db->where(array('CategoryID' => $category_id,'ModuleID' => $module_id,'ModuleEntityID'=>$module_entity_id));
                $query = $this->db->get();
                $entity_categories[] = $category_id;
 
                 if( $query->num_rows() == 0 ) {               
                    $insert_data[$key]['CategoryID']                = $category_id;
                    $insert_data[$key]['EntityCategoryGUID']        = get_guid();
                    $insert_data[$key]['ModuleEntityID']            = $module_entity_id;
                    $insert_data[$key]['ModuleID']                    = $module_id;     // ID 14 for Event module
                    $insert_data[$key]['CreatedDate']                = get_current_date('%Y-%m-%d %H:%i:%s');
                }         
            } // foreach Close
               
            if(!empty($insert_data))
            {
            	$data = $this->db->insert_batch(ENTITYCATEGORY,$insert_data);	
            }



                if(count($entity_categories) > 0 /*added by gautam - start */&& $action=='update'/*added by gautam - end */) 
                {
                    $this->db->where(array('ModuleID' => $module_id,'ModuleEntityID'=>$module_entity_id));
                    $this->db->where_not_in('CategoryID', $entity_categories);
                    $this->db->delete(ENTITYCATEGORY);       
                }

           
            return true;
        } 
        else 
        {   // if NO CATEGORY ID SUBMITTED THEN DELETE ALL THE EXISTING CATEGORY FOR GIVEN MODULE AND MODULE ENTITY ID
            $this->db->where(array('ModuleID' => $module_id,'ModuleEntityID'=>$module_entity_id));
            $this->db->delete(ENTITYCATEGORY); 
            return false;
        }
    }


    /**
     * [get_all_subcategoryids Used to fetch sub category ids of category]
     * @param  [int] $category_id       [category id]
     * @param  [int] $module_id         [module id]
     * @return [array] sub-categoryids  [categoryids]
     */

    function get_all_subcategories($category_id, $module_id){
        $category_ids=array();
        if (CACHE_ENABLE) 
        {
            $category_ids = $this->cache->get('api_subCategory'.$category_id);
        }

        if(empty($category_ids))
        {
            // check given categoryid is parent category or not
            $this->db->select('GROUP_CONCAT(c.CategoryID) as CategoryIDs');
            $this->db->where('c.ModuleID', 1);
            $this->db->where('c.ParentID', $category_id);
            $this->db->from(CATEGORYMASTER . ' c');
            $sql = $this->db->get();
            if ($sql->num_rows() > 0)
            {
                $category = $sql->row_array();
                if(!empty($category['CategoryIDs'])){ // check if subcategory is present 
                    $category_ids = explode(",", $category['CategoryIDs']);
                    if (CACHE_ENABLE) 
                    {
                        $this->cache->save('api_subCategory'.$category_id, $category_ids,CACHE_EXPIRATION);
                    }
                }
            }
        }
        return $category_ids;
    }

    /*-------------------------------------------------
    | @Method - To remove category of entity
    | @Params - CategoryID(array)
    | @Output - int/bool
    --------------------------------------------------*/
    function remove_entity_category($category_id,  $module_id, $entity_id)
    {
        if(!empty($category_id))
        {
            // Remove categories code here
            $this->db->where('CategoryID', $category_id);
            $this->db->where('ModuleID', $module_id);
            $this->db->where('ModuleEntityID', $entity_id);
            $this->db->delete(ENTITYCATEGORY);
            return true;
        }
        else
        {
            return false;
        }
    }

    function get_category_by_id($category_id)
    {
        $cache_data=array();
        if (CACHE_ENABLE) {
            $data=$this->cache->get('category');
            if(isset($data[$category_id]))
            {
                $cache_data=$data[$category_id];
            }
        }
        if(empty($cache_data))
        {
            $this->db->select('*');
            $this->db->from(CATEGORYMASTER);
            $this->db->where('CategoryID',$category_id);
            $query = $this->db->get(); 
            $cache_data=$query->row_array(); 
            initiate_worker_job('category_cache', array());
        }
        return $cache_data;
    }
    function category_cache()
    {
            $this->db->select('*');
            $this->db->from(CATEGORYMASTER);
            $query = $this->db->get(); 
            $data=$query->result_array(); 
            $cache_data=array();
            foreach($data as $item)
            {
                $cache_data[$item['CategoryID']]=$item;
            }
            if (CACHE_ENABLE) {
                $this->cache->save('category', $cache_data,CACHE_EXPIRATION);
            }
    }
    
    /*-------------------------------------------------
    | @Method - To insert or add or delete entity categories
    | @Params - CategoryID(array)
    | @Output - CategoryID(array) last output 
    --------------------------------------------------*/
    public function insert_update_interest($category_ids, $module_id, $module_entity_id, $user_type = 1, $only_add = false, $only_remove = false, $hard_delete = false) {
        
        // check categories type
        $typed_category_ids = [];
        foreach ($category_ids as $category_id) {
            if(is_numeric($category_id)) {
                $typed_category_ids[] = $category_id;
            }
        }
        $category_ids = $typed_category_ids;
        
        if($hard_delete && !empty($category_ids)) {
            $this->db->where('ModuleID', $module_id);
            $this->db->where('ModuleEntityID', $module_entity_id);
            $this->db->where_in('CategoryID', $category_ids);
            $this->db->delete(ENTITYCATEGORY);
            return;
        }
        
        
        $entity_categories = array();
        $insert_data = array();
        $new_categories = [];
        $update_entity_count_categories = [];
        $update_entity_user_type_categories = [];
        $update_entity_user_type_opp_categories = [];
        $deleted_categories = [];
        // Get all existing categories
        $this->db->select("CategoryID, EntityCategoryGUID, ModuleEntityUserType, ModuleEntityCount");
        $this->db->from(ENTITYCATEGORY);
        //$this->db->where_in('CategoryID', $category_ids);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $query = $this->db->get();
        $entity_categories = $query->result_array();
        
        
        
        // In case of only remove 
        if($only_remove) {
            $tem_removing_categories = $category_ids;
            $category_ids = [];
            foreach($entity_categories as $entity_category) {
                $category_ids[] = $entity_category['CategoryID'];
            }
            
            $category_ids = array_diff($category_ids, $tem_removing_categories);
        }
        
        $returning_categories = [];
        
        foreach($entity_categories as $entity_category) {
            if(in_array($entity_category['CategoryID'], $category_ids)) {// Existing categories
//                if($only_remove) {
//                    continue;
//                }
                // Update ModuleEntityCount
                if($user_type == $entity_category['ModuleEntityUserType'] || $entity_category['ModuleEntityUserType'] == 3) {
                    $update_entity_count_categories[] = $entity_category['CategoryID'];
                } else { // Update user type and count
                    $update_entity_user_type_categories[] = $entity_category['CategoryID'];
                }
                
                $returning_categories[] = $entity_category['CategoryID'];
                
            } else { // Deleted categories
                
                if($only_add) { // Don't remove others if flag true;
                    continue;
                }
                
                if($entity_category['ModuleEntityUserType'] == 3) {
                    $update_entity_user_type_opp_categories[] = $entity_category['CategoryID'];
                } else if($user_type == $entity_category['ModuleEntityUserType']) {
                    $deleted_categories[] = $entity_category['CategoryID'];
                }
            }              
        } // foreach Close
        
        // Insert new categories
        $new_categories = array_diff($category_ids, $deleted_categories, $update_entity_count_categories, $update_entity_user_type_categories, $update_entity_user_type_opp_categories);
        foreach ($new_categories as $new_category) {
            $insert_data[] = array(
                'CategoryID' => $new_category,
                'EntityCategoryGUID' => get_guid(),
                'ModuleEntityID' => $module_entity_id,
                'ModuleID' => $module_id,
                'ModuleEntityCount' => 1,
                'ModuleEntityUserType' => $user_type,
                'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
            );
        }
        if(!empty($insert_data) && !$only_remove) {
            $data = $this->db->insert_batch(ENTITYCATEGORY,$insert_data);	
        }
        
        $update_datas = array(
            //Update category type
            'type_and_count_update' => array(
                'ModuleEntityUserType' => 3,
                'ModuleEntityCount' => ' ModuleEntityCount + 1 ',
                'categories' => $update_entity_user_type_categories,
            ),
            
            //Update category count
            'count_update' => array(
                'ModuleEntityCount' => ' ModuleEntityCount + 1 ',
                'categories' => $update_entity_count_categories,
            ),
            
            //Detete categories for current user
            'type_update' => array(
                'ModuleEntityCount' => ($user_type == 1) ?' ModuleEntityCount - 1 ' : 1,
                'ModuleEntityUserType' => ($user_type == 1) ? 2 : 1 ,
                'categories' => $update_entity_user_type_opp_categories,
            )
        );
        
        foreach($update_datas as $update_data) {
            $categories = $update_data['categories'];
            unset($update_data['categories']);
            if(empty($categories)) {
                continue;
            }
//            $this->db->where('ModuleID', $module_id);
//            $this->db->where('ModuleEntityID', $module_entity_id);
//            $this->db->where_in('CategoryID', $categories);
//            $this->db->update(ENTITYCATEGORY, $update_data);
            
            $update_data_fields = [];
            foreach ($update_data as $field => $val) {
                $update_data_fields[] = " $field = $val ";
            }
            $update_data_fields = implode(',', $update_data_fields);
            $update_entity_catetory_query = " Update ".ENTITYCATEGORY." SET $update_data_fields ";
            $update_entity_catetory_query .= " Where ModuleID = $module_id AND ModuleEntityID = $module_entity_id AND CategoryID IN(". implode(',', $categories).")"; 
            $query = $this->db->query($update_entity_catetory_query);
        }
        
        // Delete categories
        if(empty($deleted_categories)) {
            return $returning_categories;
        }
        $this->db->where('ModuleID', $module_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $this->db->where_in('CategoryID', $deleted_categories);
        $this->db->delete(ENTITYCATEGORY);
        
        return $returning_categories;
    }
    
    


    public function insert_interest_category($categoryName, $check_parent = true) {
        $this->db->select("CategoryID");
        $this->db->from(CATEGORYMASTER);
        $this->db->where('ModuleID', 31);
        $this->db->where('Name', $categoryName);
        
        $query = $this->db->get();
        $entity_category = $query->row_array();
        
        if(!empty($entity_category['CategoryID'])) {
            return $entity_category['CategoryID'];
        }
        
        // Get parent category Id 
        $parent_id = 0;
        if($check_parent) {
            $parent_id = $this->insert_interest_category('Others', false);
        }
        
        
        $data = array(
            'ModuleID' => 31,
            'ParentID' => $parent_id,
            'Name' => $categoryName,
        );
        $this->db->insert(CATEGORYMASTER, $data);
        return $this->db->insert_id();
    }
    
    
    function utility($module_id, $search_keyword, $count_only = 0, $parent_id=0, $order_by = "Name", $sort_by = "ASC") {
        
        $search_parent_category = array();
        if (!empty($search_keyword)) {
            $this->db->select('C.CategoryID');
            $this->db->from(CATEGORYMASTER . "  C");
            $this->db->where('C.StatusID', 2);
            $this->db->where("(C.Name like '%" . $this->db->escape_like_str($search_keyword) . "%')");
            if($parent_id >= 0) {
                $this->db->where('C.ParentID', $parent_id);
            }
            $this->db->where('C.ModuleID', $module_id);
           // $this->db->where('C.LocalityID', $this->LocalityID);
            $query = $this->db->get();  
            if($query->num_rows()) {
                foreach ($query->result_array() as $cdata) {  
                    $search_parent_category[] = $cdata['CategoryID'];
                }
            }
        }
        
        $this->db->select('C.CategoryID,C.ModuleID');
        $this->db->select('C.Name AS name', FALSE);      
        $this->db->select('IFNULL(C.Description,"") as Description', FALSE);
        $this->db->select('IFNULL(C.Address,"") as Address', FALSE);
        $this->db->select('IFNULL(C.Mobile,"") as Mobile', FALSE);
        $this->db->select('IFNULL(C.OwnerName,"") as OwnerName', FALSE);
        $this->db->select('IFNULL(C.Miscellaneous,"") as Miscellaneous', FALSE);
        
        $this->db->from(CATEGORYMASTER . "  C");
        $this->db->where('C.StatusID', 2);
        if($parent_id >= 0) {
            $this->db->where('C.ParentID', $parent_id);
        }        
        if($parent_id == -1) {
            $this->db->where('C.ParentID >', 0);
        }
        $this->db->where('C.ModuleID', $module_id);
        //$this->db->where('C.LocalityID', $this->LocalityID);        
       
        
                
        if ($count_only) {
            $this->db->select('COUNT(DISTINCT C.CategoryID) as TotalRow ' );            
            $query = $this->db->get();
            $count_data=$query->row_array();
            return $count_data['TotalRow'];
        }
        if($order_by == 'Name') {
            $this->db->order_by('C.Name', $sort_by);
        } else {
            $this->db->order_by('C.CreatedDate', $sort_by);
        }
        
        $query = $this->db->get();        
        $category_data = array();
        if($query->num_rows()) {
            foreach ($query->result_array() as $cdata) {  
                $search_str = $search_keyword;
                if(in_array($cdata['CategoryID'], $search_parent_category)) {
                    $search_str = ''; 
                }
                $cdata['TotalSubCategory'] =  $this->sub_utility($search_str, 1, $cdata['CategoryID'], $order_by, $sort_by);
                if($cdata['TotalSubCategory'] <= 0) {
                    continue;
                }
                $cdata['SubCategory'] =  $this->sub_utility($search_str, 0, $cdata['CategoryID'], $order_by, $sort_by);
                $category_data[] = $cdata;
            }
        }        
        return $category_data;
    } 
    
    function sub_utility($search_keyword, $count_only = 0, $parent_id, $order_by = "Name", $sort_by = "ASC") {
        $this->db->select('C.CategoryID,C.ModuleID');
        $this->db->select('C.Name AS name', FALSE);      
        $this->db->select('IFNULL(C.Description,"") as Description', FALSE);
        $this->db->select('IFNULL(C.Address,"") as Address', FALSE);
        $this->db->select('IFNULL(C.Mobile,"") as Mobile', FALSE);
        $this->db->select('IFNULL(C.OwnerName,"") as OwnerName', FALSE);
        $this->db->select('IFNULL(C.Miscellaneous,"") as Miscellaneous', FALSE);
        
        $this->db->from(CATEGORYMASTER . "  C");
        $this->db->where('C.StatusID', 2);
        $this->db->where('C.ParentID', $parent_id);
        
        if (!empty($search_keyword)) {
            $this->db->where("(C.Name like '%" . $this->db->escape_like_str($search_keyword) . "%')");
        }
                
        if ($count_only) {
            $this->db->select('COUNT(DISTINCT C.CategoryID) as TotalRow ' );            
            $query = $this->db->get();
            $count_data=$query->row_array();
            return $count_data['TotalRow'];
        }
        if($order_by == 'Name') {
            $this->db->order_by('C.Name', $sort_by);
        } else {
            $this->db->order_by('C.CreatedDate', $sort_by);
        }
        
        $query = $this->db->get();        
        $category_data = array();
        if($query->num_rows()) {
            foreach ($query->result_array() as $cdata) {
                $cdata['Media'] =  $this->get_media($cdata['CategoryID']);
                $cdata['Mobile'] =  (string)$cdata['Mobile'];
                $category_data[] = $cdata;
            }
        }        
        return $category_data;
    }
    
    function get_media($category_id) {
        $this->db->select('MediaGUID, ImageName');
        $this->db->where('MediaSectionID', 10);
        $this->db->where('MediaSectionReferenceID', $category_id);
        $this->db->where('StatusID', 2);
        $result = $this->db->get(MEDIA)->result_array();
        return $result;
    }
}
