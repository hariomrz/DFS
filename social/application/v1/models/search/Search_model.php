<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Search_model extends Common_Model
{
    function __construct()
    {
        parent::__construct();
    }



	/**
	 * [use to add/edit/delete search filters]
	 * @param  [Array]  $Input [Array]
	 */
	function AddEditDeleteFilter($Input){
            if(!empty($Input['FilterGUID'])){
	                $Data = $this->GetSearchFilters($Input['FilterGUID']);
	                if(!$Data) {
	                    return FALSE;
	                }
	        }
	        /* Delete */
	        if(!empty($Input['FilterGUID']) && empty($Input['FilterValues'])){
	            $this->db->query("DELETE FROM ".SEARCHFILTERS." WHERE FilterGUID='".$Input['FilterGUID']."' AND UserID='".$Input['UserID']."' LIMIT 1");
	            return TRUE;
	        }
	        /* Edit */
	        elseif(!empty($Input['FilterGUID']) && !empty($Input['FilterValues'])){
	        $this->db->query("UPDATE ".SEARCHFILTERS." SET FilterName='".$Input['FilterName']."', FilterValues='".$Input['FilterValues']."' WHERE FilterGUID='".$Input['FilterGUID']."' AND UserID='".$Input['UserID']."' LIMIT 1");
	            return TRUE;  
	        }
	        /* Insert */
	        elseif(empty($Input['FilterGUID']) && !empty($Input['FilterValues'])){
	        $this->db->query("INSERT INTO  ".SEARCHFILTERS." (FilterGUID, FilterName, FilterValues, UserID) VALUES('".get_guid()."','".$Input['FilterName']."','".$Input['FilterValues']."','".$Input['UserID']."') ");
	            return $this->GetSearchFilters($this->db->insert_id(),'FilterID');  
	        }
	        return FALSE;
	    }
	/**
	 * [use to get Search filter records]
	 * @param  [Array]  $Input [Field] [ID]
	 */
	function GetSearchFilters($ID='', $Field='FilterGUID', $Limit=1){

	        /* Get Data - starts */
	        $InputAr['field'] = 'FilterGUID, FilterName, FilterValues';
	        $InputAr['table'] = SEARCHFILTERS;
	        $InputAr['where'] = array($Field => $ID);
	        $InputAr['limit'] = $Limit;                   
	        $InputAr['orderBy'] = 'FilterID DESC';

	        $Data = get_data_new($InputAr);
	        if(!$Data) {
	            return FALSE;
	        }
	        return $Data;
	}

    /**
     * [use to get search records]
     * @param  [Array]  $Input [User ID]
     */
    function search($UserID, $data=array()){
        $CaseSelect = $CaseOrder = $CaseWhere ='';   

        $Input['SearchKey']  = isset($data['SearchKey']) ? $data['SearchKey'] : '' ;

        $Input['Interest']  = isset($data['Interest']) ? $data['Interest'] : '' ;
        $Input['Type']      = isset($data['Type']) ? $data['Type'] : '' ;
        $Input['City']      = isset($data['City']) ? $data['City'] : '' ;
        $Input['Latitude']  = isset($data['Latitude']) ? $data['Latitude'] : '' ;
        $Input['Longitude'] = isset($data['Longitude']) ? $data['Longitude'] : '' ;

        $Input['DistanceLimitAbove'] = isset($data['DistanceLimitAbove']) ? $data['DistanceLimitAbove'] : '' ;
        $Input['DistanceLimitUnder'] = isset($data['DistanceLimitUnder']) ? $data['DistanceLimitUnder'] : '' ;

        $Input['PageNo'] = isset($data['PageNo']) ? $data['PageNo'] : 0 ;
        $Input['PageSize'] = isset($data['PageSize']) ? $data['PageSize'] : 10 ;


           
        /* Select Interest - starts */
        $CategoryIDs = $this->db->query("SELECT GROUP_CONCAT(CM.CategoryID) AS CategoryIDs FROM `EntityCategory` EC, `CategoryMaster` CM
         WHERE (EC.CategoryID=CM.CategoryID AND EC.ModuleID='3' AND EC.ModuleEntityID='".$UserID."')")->row_array();
        $Interest = '';
        //$array1 = explode(',',$CategoryIDs['CategoryIDs']);
        $array1 = array();
        $array2 = explode(',',$Input['Interest']);
        $array = array_filter(array_merge($array1,$array2));
        if(!empty($array)){
            $Interest = "'" .implode("', '", $array). "'";
        }
        /* Select Interest - ends */

        /*this is called Haversine formula and the constant 6371 is used to get distance in KM, while 3959 is used to get distance in miles.*/
        if($Input['Latitude']!='' && $Input['Longitude']!='' && ($Input['Type']=='USERS' || $Input['Type']=='PAGES')){
                $CaseSelect .="
                ,( 3959 * ACOS( COS( RADIANS(".$Input['Latitude'].") ) * COS( RADIANS( UD.`latitude` ) ) * COS( RADIANS( UD.`longitude` ) - RADIANS
                (".$Input['Longitude'].") ) + SIN( RADIANS(".$Input['Latitude'].") ) * SIN( RADIANS( UD.`latitude` ) ) ) ) AS Distance";
                $CaseOrder.="Distance ASC, ";
        }else{
             $CaseSelect .=", 0 as Distance";
        }




        /* Where Case */
        if($Interest!='' && $Input['Type']=='USERS'){/* Interest */
                $CaseSelect .= "
                , (SELECT GROUP_CONCAT(CM.`Name`) FROM `EntityCategory` EC, `CategoryMaster` CM WHERE EC.`ModuleEntityID`=U.UserID AND EC.ModuleID='3' AND EC.`CategoryID`=CM.`CategoryID`
                AND CM.`CategoryID` IN ($Interest)) AS Interests";

                $CaseWhere .= "
                AND EXISTS (
                SELECT EC.`CategoryID` FROM `EntityCategory` EC, `CategoryMaster` CM WHERE EC.`ModuleEntityID`=U.UserID AND EC.ModuleID='3' AND EC.`CategoryID`=CM.`CategoryID`
                AND CM.`CategoryID` IN ($Interest)
                )
                ";
        }
        /*Filter by City Name*/
        if(!empty($Input['City']) && $Input['Type']!='GROUPS'){
                $CaseWhere .= "
                AND C.Name='".$Input['City']."'
                ";
        }

        /*Filter by City Keyword*/
    if(!empty($Input['SearchKey'])){
            if($Input['Type']=='USERS'){
                
                $Input['SearchKey'] = $this->db->escape_like_str($Input['SearchKey']); 
                
                    $CaseWhere .= "
                    AND CONCAT_WS(' ',FirstName,LastName) LIKE '%".$Input['SearchKey']."%' ";
            }elseif($Input['Type']=='PAGES'){
                    $CaseWhere .= "
                    AND UD.Title LIKE '%".$Input['SearchKey']."%' ";
            }
            elseif($Input['Type']=='GROUPS'){
                    $CaseWhere .= "
                    AND G.GroupName LIKE '%".$Input['SearchKey']."%' ";
            }
    }

    if($Input['Type']=='USERS'){
        $CaseWhere .= "
        AND FirstName!='' ";
    }



        /*Filter by Distancee*/
        if($Input['Latitude']!='' && $Input['Longitude']!='' && $Input['DistanceLimitAbove']!='' && $Input['DistanceLimitUnder']!='' && $Input['Type']!='GROUPS'){/*Filter by DistanceLimitAbove*/
                $CaseWhere .= "
                HAVING (Distance >= ".$Input['DistanceLimitAbove']." AND  Distance <= ".$Input['DistanceLimitUnder'].")
                ";
        };


        /* Final Queries - starts */        
        if($Input['Type']=='USERS'){/*Select Data of Users*/
            /*Do not show block users*/
            $CaseWhere .= "
            AND NOT EXISTS (SELECT BlockUserID FROM ".BLOCKUSER." WHERE ModuleID='3' AND UserID='$UserID' AND EntityID=U.UserID)";

            $Query = "
            SELECT
            U.UserGUID AS EntityGUID, U.UserID EntityID, CONCAT_WS(' ',FirstName,LastName) AS Name, ProfilePicture
            $CaseSelect
            FROM `Users` U, `UserDetails` UD LEFT JOIN `Cities` C ON UD.`CityID`=C.`CityID`
            WHERE U.UserID=UD.UserID AND  U.`StatusID`=2 AND U.UserID!=$UserID
            $CaseWhere
            ";
            $CaseOrder.="FirstName ASC";
        }elseif($Input['Type']=='PAGES'){/*Select Data of Pages*/
            $Query = "
            SELECT
            UD.PageGUID AS EntityGUID, UD.PageID EntityID, UD.Title AS Name, (SELECT CM.Name FROM `CategoryMaster` CM, `EntityCategory` EC
 WHERE CM.CategoryID=EC.CategoryID AND EC.ModuleID='18' AND EC.ModuleEntityID=UD.PageID) Interests, UD.ProfilePicture            
            $CaseSelect
            FROM `Pages` UD LEFT JOIN `Cities` C ON UD.`CityID`=C.`CityID`
            WHERE 1=1
            $CaseWhere
            ";
            $CaseOrder.="UD.Title ASC";
        }elseif($Input['Type']=='GROUPS'){/*Select Data of Groups*/
            $CaseWhere .= " AND G.IsPublic='1'
            ";
            $Query = "
            SELECT
            G.GroupGUID AS EntityGUID, G.GroupID EntityID, G.GroupName AS Name, CM.Name Interests, G.GroupImage AS ProfilePicture, G.IsPublic              
            $CaseSelect
            FROM `Groups` G, EntityCategory EC  LEFT JOIN CategoryMaster CM ON CM.CategoryID=EC.CategoryID
            WHERE G.GroupID=EC.ModuleEntityID AND EC.ModuleID=1
            $CaseWhere
            ";
            /*Add order by name*/
            $CaseOrder.="Name ASC";
        }
        /* Count Total Records - starts */
        $query = $this->db->query($Query);
        $Return['TotalRecords'] = $query->num_rows();
        /* Count Total Records - ends */

        if($CaseOrder!=''){/*Order By*/
            $Query .= "
            ORDER BY $CaseOrder LIMIT ".$this->get_pagination_offset($Input['PageNo'], $Input['PageSize']).", ".$Input['PageSize']." ";




        }       
        /* Final Queries - ends */

        $query = $this->db->query($Query);
        //echo $this->db->last_query();
        if($query->num_rows()>0){
            foreach($query->result_array() as $value) {
            if(empty($value['Interests'])){
                $value['Interests']='';
            }
            if($Input['Type']=='USERS'){
                $value['FriendStatus'] = $this->friend_model->checkFriendStatus($value['EntityID'],$UserID);
                $value['FollowStatus'] = $this->friend_model->checkFollowStatus($value['EntityID'],$UserID);
            }

/*privacy check - starts added by gautam*/
            if ($UserID != $value['EntityID'] && $Input['Type']=='USERS')
            {
                $users_relation = get_user_relation($UserID, $value['EntityID']);
                $privacy_details = $this->privacy_model->details($value['EntityID']);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label'])
                {
                    foreach ($privacy_details['Label'] as $privacy_label)
                    {
                        if(isset($privacy_label[$privacy]))
                        {
                            if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation))
                            {
                                $value['ProfilePicture'] = '';
                            }
                        }
                    }
                }
            }
/*privacy check - ends added by gautam*/



                unset($value['EntityID']);
                $Data[] = $value;
            }
            $Return['Records'] = $Data;
        }else{
            return FALSE;
        }
return $Return;
    }
     

    /**
     * [get_city_list]
     * @param [string] [Keyword]
     * @return [json] [return json boject]
     */
    public function get_city_list($keyword)
    {
        $keyword = $this->db->escape_like_str($keyword); 
        $data = array();
        $this->db->select('CityID,Name');
        $this->db->from(CITIES);
        $this->db->where("Name LIKE '%".$keyword."%'",null,false);
        $query = $this->db->get();
        if($query->num_rows())
        {
            $data = $query->result_array();
        }
        return $data;
    }

    /**
     * [get_school_list]
     * @param [string] [Keyword]
     * @return [json] [return json boject]
     */
    public function get_school_list($keyword)
    {
        $keyword = $this->db->escape_like_str($keyword); 
        $data = array();
        $this->db->select('EducationID,University');
        $this->db->from(EDUCATION);
        $this->db->where("University LIKE '%".$keyword."%'",null,false);
        $this->db->group_by("University");
        $query = $this->db->get();
        if($query->num_rows())
        {
            $data = $query->result_array();
        }
        return $data;
    }

    /**
     * [get_company_list]
     * @param [string] [Keyword]
     * @return [json] [return json boject]
     */
    public function get_company_list($keyword)
    {
        $keyword = $this->db->escape_like_str($keyword); 
        $data = array();
        $this->db->select('WorkExperienceID,OrganizationName');
        $this->db->from(WORKEXPERIENCE);
        $this->db->where("OrganizationName LIKE '%".$keyword."%'",null,false);
        $this->db->group_by("OrganizationName");
        $query = $this->db->get();
        if($query->num_rows())
        {
            $data = $query->result_array();
        }
        return $data;
    }

    /**
     * [get_categories]
     * @param [string] [Keyword]
     * @return [json] [return json boject]
     */
    public function get_categories($keyword,$parent_id=0,$module_id=31)
    {
        $keyword = $this->db->escape_like_str($keyword); 
        $data = array();
        $this->db->select('ParentID,CategoryID,Name');
        $this->db->from(CATEGORYMASTER);
        $this->db->where('ParentID',$parent_id);
        $this->db->where('ModuleID',$module_id);
        $this->db->where("Name LIKE '%".$keyword."%'",null,false);
        $parent_query = $this->db->get();
        if($parent_query->num_rows())
        {
                foreach($parent_query->result_array() as $parent_category)
                {
                    if($parent_category['ParentID'] == 0)
                    {
                        if($module_id!=1)
                        {
                            $parent_category['Subcategory'] = $this->get_interest($keyword,$parent_category['CategoryID'],$module_id);
                        }
                    }
                    unset($parent_category['ParentID']);
                    $data[] = $parent_category;
                }

        }
        return $data;
    }

    /**
     * [get_interest]
     * @param [string] [Keyword]
     * @return [json] [return json boject]
     */
    public function get_interest($keyword,$parent_id=0,$module_id=31)
    {
        $data = array();
        $this->db->select('ParentID,CategoryID,Name');
        $this->db->from(CATEGORYMASTER);
        $this->db->where('ParentID',$parent_id);
        $this->db->where('ModuleID',$module_id);
        if($parent_id)
        {
            $keyword = $this->db->escape_like_str($keyword); 
        	$this->db->where("Name LIKE '%".$keyword."%'",null,false);
        }
        $parent_query = $this->db->get();
        if($parent_query->num_rows())
        {
            foreach($parent_query->result_array() as $parent_category)
            {
            	if($parent_category['ParentID'] == 0)
            	{
            		$parent_category['Subcategory'] = $this->get_interest($keyword,$parent_category['CategoryID'],$module_id);
            	}
            	unset($parent_category['ParentID']);
            	$data[] = $parent_category;
            }

        }
        return $data;
    }

    /**
     * [get_skills]
     * @param [string] [Keyword]
     * @return [json] [return json boject]
     */
    public function get_skills($keyword,$parent_id=0,$module_id=29,$module_entity_id=0,$skills=0)
    {
        $keyword = $this->db->escape_like_str($keyword); 
        $data = array();
        $this->db->select('SkillID,Name');
        $this->db->from(SKILLSMASTER);
      	$this->db->where("Name LIKE '%".$keyword."%'",null,false);
        $query = $this->db->get();
        //echo $this->db->last_query();
        if($query->num_rows())
        {
            $data = $query->result_array();
        }
        return $data;
    }

    /**
     * [get_user_details]
     * @return [json] [return json boject]
     */
    public function get_user_details($user_id,$keyword)
    {
        $data = array('Cities'=>array(),'Schools'=>array(),'Companies'=>array(),'Interest'=>array());

        $privacy_condition = "
            IF(UP.Value='everyone',true, 
                IF(UP.Value='network', u.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
                IF(UP.Value='friend',u.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
            )
        ";

        $this->db->select("IFNULL(GROUP_CONCAT(u.UserID),0) as Users", false);
        $this->db->from(USERS . " u");
        $this->db->join(PROFILEURL . " as p", "p.EntityID = u.UserID and p.EntityType = 'User'", "LEFT");
        $this->db->join(USERDETAILS . " as ud", "ud.UserID = u.UserID", "LEFT");
        $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=u.UserID', 'left');
        $this->db->where('UP.PrivacyLabelKey', 'search');
        $this->db->where($privacy_condition, NULL, FALSE);
        $this->db->where_not_in('u.StatusID', array(3, 4));
        $sqlCondition = array('u.UserID !=' => $user_id);
        $this->db->where($sqlCondition);
        //$search_key = '';
        if (!empty($keyword))
        {
            $this->db->where("(u.FirstName like '%" . $this->db->escape_like_str($keyword) . "%' or u.LastName like '%" . $this->db->escape_like_str($keyword) . "%' or concat(u.FirstName,' ',u.LastName) like '%" . $this->db->escape_like_str($keyword) . "%')");
        }
        $query = $this->db->get();
        if($query->num_rows())
        {
            $users = $query->row()->Users;

            $this->db->select('CM.CategoryID,CM.Name');
            $this->db->from(CATEGORYMASTER . ' CM');
            $this->db->join(ENTITYCATEGORY . ' EC', "CM.CategoryID=EC.CategoryID AND EC.ModuleID=3 AND EC.ModuleEntityID IN(" . $users . ")", 'left outer');
            $this->db->where('CM.ModuleID', '31');
            $this->db->where('CM.StatusID', '2');
            $this->db->where('EC.CategoryID is not NULL', null, false);
            $this->db->group_by('CM.CategoryID');
            $query = $this->db->get();
            if($query->num_rows())
            {
                $data['Interest'] = $query->result_array();
            }

            $this->db->select('C.CityID,C.Name');
            $this->db->from(CITIES.' C');
            $this->db->where("C.CityID IN(SELECT CONCAT(CityID,',',HomeCityID) FROM ".USERDETAILS." WHERE UserID IN(".$users."))",NULL,FALSE);
            $query = $this->db->get();

            if($query->num_rows())
            {
                $data['Cities'] = $query->result_array();
            }

            $this->db->select('C.CityID,C.Name');
            $this->db->from(CITIES.' C');
            $this->db->where("C.CityID IN(SELECT CONCAT(CityID,',',HomeCityID) FROM ".USERDETAILS." WHERE UserID IN(".$users."))",NULL,FALSE);
            $query = $this->db->get();

            if($query->num_rows())
            {
                $data['Cities'] = $query->result_array();
            }

            $this->db->select('EducationID,University');
            $this->db->from(EDUCATION);
            $this->db->where_in("UserID",explode(',',$users));
            $this->db->group_by("University");
            $query = $this->db->get();
            if($query->num_rows())
            {
                $data['Schools'] = $query->result_array();
            }

            $this->db->select('WorkExperienceID,OrganizationName');
            $this->db->from(WORKEXPERIENCE);
            $this->db->where_in("UserID",explode(',',$users));
            $this->db->group_by("OrganizationName");
            $query = $this->db->get();
            if($query->num_rows())
            {
                $data['Companies'] = $query->result_array();
            }

        }
        return $data;
    }

    public function get_tags($search)
    {
        $this->db->select('TagID,Name');
        $this->db->from(TAGS);
        $this->db->like('Name',$search);
        $query = $this->db->get();
        if($query->num_rows())
        {
            return $query->result_array();
        }
        return array();
    }

    /**
     * [user_n_group Used to search user and group in admin to add permission for group]
     * @param  [int] $search_keyword [Search Keyword]
     * @param  [int] $page_no        [Page Number]
     * @param  [int] $page_size      [Page Size]
     * @return [array]                 [Details array]
     */
    function user_n_group($search_keyword, $page_no = 0, $page_size = 0)
    {      
        $search_keyword = $this->db->escape_like_str($search_keyword); 
        $sql = "SELECT ModuleEntityGUID, ModuleEntityID, Name, ProfilePicture, Privacy, ModuleID, Type, GroupDescription FROM (";
        $sql.= "SELECT DISTINCT U.UserGUID AS ModuleEntityGUID, U.UserID AS ModuleEntityID, CONCAT(U.FirstName,' ',U.LastName) AS Name, if(U.ProfilePicture!='',U.ProfilePicture,'user_default.jpg') AS ProfilePicture, '' AS Privacy,3 AS ModuleID,'User' AS Type,'' AS GroupDescription
        FROM " . USERS . " U WHERE U.StatusID NOT IN (3,4) AND (U.FirstName LIKE '%" . $search_keyword . "%' OR U.LastName LIKE '%" . $search_keyword . "%' OR CONCAT(U.FirstName,' ',U.LastName) LIKE '%" . $search_keyword . "%')";

        $sql.=" UNION ALL";

        $sql.= ' SELECT DISTINCT G.GroupGUID AS ModuleEntityGUID, G.GroupID AS ModuleEntityID, G.GroupName AS Name';

        $sql.=" , if(G.GroupImage!='',G.GroupImage,'group-no-img.jpg') AS ProfilePicture, G.IsPublic AS Privacy,1 AS ModuleID, G.Type, G.GroupDescription
        FROM " . GROUPS . " AS G ";
        
        $sql.=" WHERE G.StatusID=2 AND G.Type!='INFORMAL'";
        
        $sql.=" HAVING Name LIKE '%" . $search_keyword . "%' OR G.GroupDescription LIKE '%" . $search_keyword . "%'
        ";        
        $sql.=") tbl ORDER BY Name ASC";  
        if (!empty($page_size)) // Check for pagination
        {
            $offset = $this->get_pagination_offset($page_no, $page_size);
            //$this->db->limit($page_size, $offset);
            $sql.=" LIMIT ".$offset.", ".$page_size;
        }     
        $query = $this->db->query($sql);
       // echo $this->db->last_query();die;
        if ($query->num_rows())
        {
            $result = $query->result_array();            
            return $result;
        } 
        else
        {
            return array();
        }
    }

    /**
     * [top_search Used to search user and group from top search]
     * @param  [int] $search_keyword [Search Keyword]
     * @param  [int] $page_no        [Page Number]
     * @param  [int] $page_size      [Page Size]
     * @return [array]                 [Details array]
     */
    function top_search($user_id, $search_keyword, $page_no = 0, $page_size = 3)
    {                
        $search_keyword = $this->db->escape_like_str($search_keyword); 
        $this->load->model('activity/activity_model');
        $this->load->model('groups/group_model');

        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        
        $group_ids = $this->group_model->get_users_groups($user_id);
        $group_ids[] = 0;

        $blocked_group_list = $this->group_model->get_blocked_group_list($user_id);
        $blocked_group_list[] = 0;    
        $limit = "";    
        if (!empty($page_size)) // Check for pagination
        {
            $offset = $this->get_pagination_offset($page_no, $page_size);
            //$this->db->limit($page_size, $offset);
            $limit = " LIMIT ".$offset.", ".$page_size;
        } 

        $privacy_condition = "
            IF(UP.Value='everyone',true, 
                IF(UP.Value='network', U.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
                IF(UP.Value='friend',U.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
            )
        ";

        $sql = "SELECT ModuleEntityGUID, ModuleEntityID, Name, FirstName, LastName, ProfilePicture, Privacy, ModuleID, ProfileLink, ord FROM (";
        $sql.= "(SELECT DISTINCT U.UserGUID AS ModuleEntityGUID, U.UserID AS ModuleEntityID, CONCAT(U.FirstName,' ',U.LastName) AS Name, U.FirstName, U.LastName, if(U.ProfilePicture!='',U.ProfilePicture,'user_default.jpg') AS ProfilePicture, '' AS Privacy, 3 AS ModuleID, PU.Url as ProfileLink, 1 as ord
        FROM " . USERS . " U JOIN ".PROFILEURL." AS PU ON U.UserID=PU.EntityID AND PU.EntityType='User' JOIN ".USERPRIVACY." UP ON UP.UserID=U.UserID AND UP.PrivacyLabelKey='search' WHERE ".$privacy_condition." AND U.StatusID NOT IN (3,4) AND U.UserID NOT IN (" . implode(',', $blocked_users) . ") AND (U.FirstName LIKE '%" . $search_keyword . "%' OR U.LastName LIKE '%" . $search_keyword . "%' OR CONCAT(U.FirstName,' ',U.LastName) LIKE '%" . $search_keyword . "%') ORDER BY Name ASC".$limit.")";


        if(!$this->settings_model->isDisabled(1)) {
            $sql .= " UNION ALL";

            $sql .= " (SELECT DISTINCT G.GroupGUID AS ModuleEntityGUID, G.GroupID AS ModuleEntityID, G.GroupName AS Name, G.GroupName AS FirstName, '' AS LastName";

            $sql .= " , if(G.GroupImage!='',G.GroupImage,'group-no-img.jpg') AS ProfilePicture, G.IsPublic AS Privacy, 1 AS ModuleID, G.GroupID AS ProfileLink, 2 as ord FROM " . GROUPS . " AS G ";

            $sql .= " WHERE G.StatusID=2 AND G.Type!='INFORMAL' AND G.GroupID NOT IN (" . implode(',', $blocked_group_list) . ") AND IF(G.IsPublic=2,G.GroupID IN(" . implode(',', $group_ids) . "),TRUE)";

            $sql.=" HAVING Name LIKE '%" . $search_keyword . "%' ORDER BY Name ASC".$limit.")";
        }

        $sql.=") tbl ORDER BY ord ASC";  
            
        $query = $this->db->query($sql);
        //echo $this->db->last_query();die;
        if ($query->num_rows())
        {
            $result = $query->result_array();                 
            $this->load->model(array('group/group_model'));
                        
            foreach ($result as $key => $row) {                
                if($row['ModuleID'] == 1) {                
                    $group_data = $this->group_model->get_group_details_by_id($row['ModuleEntityID'], '', array(
                        'GroupName' => $row['Name'],
                        'GroupGUID' => $row['ModuleEntityGUID'],
                    ));
                    $result[$key]['ProfileLink'] = $this->group_model->get_group_url($row['ModuleEntityID'], $group_data['GroupNameTitle'], true, 'index'); 
                }                                
            }
            
            return $result;
        } 
        else
        {
            return array();
        }
    }

    /**
     * [top_search Used to search user and group from top search]
     * @param  [int] $search_keyword [Search Keyword]
     * @param  [int] $page_no        [Page Number]
     * @param  [int] $page_size      [Page Size]
     * @return [array]                 [Details array]
     */
    function top_search_home($user_id, $search_keyword, $page_no = 0, $page_size = 10)
    {

        
        $this->load->model('activity/activity_model');
        $this->load->model('groups/group_model');

        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        
        $group_ids = $this->group_model->get_users_groups($user_id);
        $group_ids[] = 0;

        $blocked_group_list = $this->group_model->get_blocked_group_list($user_id);
        $blocked_group_list[] = 0;    
        $limit = "";    
        if (!empty($page_size)) // Check for pagination
        {
            $offset = $this->get_pagination_offset($page_no, $page_size);
            //$this->db->limit($page_size, $offset);
            $limit = " LIMIT ".$offset.", ".$page_size;
        }

        $joinCond = "LEFT JOIN ".FORUMCATEGORY." AS FC ON A.ModuleEntityID = FC.ForumCategoryID  "
                . "LEFT JOIN ".FORUMCATEGORYMEMBER." AS FCM ON FCM.ForumCategoryID=FC.ForumCategoryID "
                . "LEFT JOIN ".FORUMCATEGORYVISIBILITY." AS FCV ON FCV.ForumCategoryID=FC.ForumCategoryID "
                . "LEFT JOIN ".FORUM." AS F ON FC.ForumID = F.ForumID "
                . "LEFT JOIN ".MEDIA." AS M ON FC.MediaID = M.MediaID ";


        
        $joinCond1 = "LEFT JOIN ".GROUPS." AS G ON A.ModuleEntityID = G.GroupID ";
        $joinCond2 = "LEFT JOIN ".ENTITYTAGS." AS ET ON A.ActivityID = ET.EntityID AND ET.StatusID=2 AND ET.EntityType = 'ACTIVITY' LEFT JOIN ".TAGS." AS TG ON ET.TagID = TG.TagID";
        
        $search_keyword = $this->db->escape_like_str($search_keyword); 
        /*search in Title,tags,content*/
        $inner_sql[] = "(SELECT DISTINCT A.ActivityGUID AS ModuleEntityGUID,
            A.ModuleEntityID AS ModuleEntityID,
            IF(A.PostTitle='',LEFT(A.PostContent,20),A.PostTitle) AS NAME,
            IF(A.PostTitle='',LEFT(A.PostContent,20),A.PostTitle) AS FirstName,
            TG.Name AS LastName,
            'Post' AS EntityType,
            IF(A.ModuleID=34,IFNULL(M.ImageName,'category_default.png'),IF(G.GroupImage != '',G.GroupImage,'group-no-img.jpg')) AS ProfilePicture,
            A.Privacy AS Privacy,
            A.ModuleID AS ModuleID,
            IF(A.ModuleID=34,CONCAT(F.URL,'/',FC.URL),G.GroupID) AS ProfileLink,
            1 AS ord, 
            A.ActivityGUID,IF(A.PostTitle='',LEFT(A.PostContent,20),A.PostTitle) as PostTitle,A.PostContent,A.PostType,A.ActivityTypeID,A.ActivityID "
                . "FROM ".ACTIVITY." A ".$joinCond.$joinCond1.$joinCond2
                . " WHERE A.ModuleID=34 AND A.StatusID =2 AND ( A.PostTitle LIKE '%".$search_keyword."%' OR TG.Name LIKE '%".$search_keyword."%' OR A.PostContent LIKE '%".$search_keyword."%') "
            ."AND ( CASE 
                      WHEN 
                        FC.Visibility=2
                        THEN 
                        ( CASE 
                          WHEN FCV.ModuleID = 3 
                              THEN FCV.ModuleEntityID = ".$user_id."         
                          WHEN FCM.ModuleID = 3 
                              THEN FCM.ModuleEntityID = ".$user_id."          
                          ELSE
                          '' 
                          END 
                        )
                    ELSE
                    true 
                    END )
                "
                . " GROUP BY A.ActivityID ORDER BY Name ASC".$limit.")";

        
        $inner_sql = implode(" UNION ALL ", $inner_sql);
       
        
        
        $sql = "SELECT EntityType,ModuleEntityGUID, ModuleEntityID, Name, FirstName, LastName, ProfilePicture, Privacy, ModuleID, ProfileLink, ord,ActivityGUID,PostTitle,PostContent,PostType,ActivityTypeID, ActivityID FROM (".$inner_sql.") tbl ORDER BY ord ASC";  
            
        $query = $this->db->query($sql);
        //echo $this->db->last_query();die;
        if ($query->num_rows())
        {
            $result = array();
            foreach($query->result_array() as $r)
            {
                if($r['EntityType'] == 'Post')
                {
                    $r['FirstName'] = strip_tags($r['FirstName']);
                    $r['PostTitle'] = strip_tags($r['PostTitle']);
                    $r['ActivityURL'] = get_single_post_url($r);
                }
                else
                {
                    $r['ActivityURL'] = '';
                }
                if($r['FirstName'] == '')
                {
                    $r['FirstName'] = 'No Title';
                }
                $result[] = $r;
            }          
            return $result;
        } 
        else
        {
            return array();
        }
    
    }
}
