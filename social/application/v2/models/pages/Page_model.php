<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Page_model extends Common_Model
{

    protected $module_id = '';
    protected $user_page_list = array();
    protected $feed_page_condition = '';

    public function __construct()
    {
        parent::__construct();
        $this->module_id = 18;
    }

    /**
     * [set_user_pages_list used to set user liked page in variable]
     * @param type $user_id
     */
    function set_user_pages_list($user_id)
    {
        $this->user_page_list = $this->page_model->get_liked_pages_list($user_id);
    }

    /**
     * [get_user_pages_list used to return user liked page data]
     * @return type
     */
    function get_user_pages_list()
    {
        return $this->user_page_list;
    }

    /**
     * [set_user_pages_list used to set user liked page in variable]
     * @param type $user_id
     */
    function set_feed_pages_condition($user_id)
    {
        if($this->settings_model->isDisabled(18)) { // Return empty if page module is disabled
             $this->feed_page_condition = '';
            return;
        }
        
        $join_page_id = array();
        $final_condition = '';
        $this->db->select('GROUP_CONCAT(P.PageID) as PageID ');
        $this->db->from(PAGES . ' P');
        $this->db->join(PAGEMEMBERS . ' PM', 'PM.PageID=P.PageID');
        $this->db->where('PM.UserID', $user_id);
        $this->db->where('P.StatusID', '2');
        $this->db->where('PM.StatusID', '2');
        $this->db->order_by('PM.ModuleRoleID', 'ASC');
        $pages = $this->db->get();
        
        if ($pages->num_rows())
        {
            $page=$pages->row_array();
            $join_page_id=array();
            if(!empty($page['PageID']))
            {
                $join_page_id = explode(',', $page['PageID']) ;
            }
        }
        
        if (!empty($join_page_id))
        {
            $this->db->select('GROUP_CONCAT(UserID) as UserID ');
            $this->db->from(PAGEMEMBERS);
            $this->db->where_in('PageID', $join_page_id);
            $this->db->where_in('ModuleRoleID',array(7,8));
            $query = $this->db->get();
            
            if ($query->num_rows())
            {
                $user=$query->row_array();
                $page_admin_id=array();
                if(!empty($user['UserID']))
                {
                    $page_admin_id = explode(',', $user['UserID']) ;
                }
            }
            $page_admin_id[]=0;
             
            // get follower user id;
            $friend_followers_list = $this->user_model->get_friend_followers_list();
            $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array() ;
            $follow[]=$user_id;
            $follow = array_unique(array_merge($follow, $page_admin_id));
            $final_condition = ' (A.ModuleEntityID IN ('.implode(',',$join_page_id).') AND A.UserID IN (' . implode(',', $follow).'))';
           
        }
       
        $this->feed_page_condition=$final_condition;
    }

    /**
     * [get_user_pages_list used to return user liked page data]
     * @return type
     */
    function get_feed_pages_condition()
    {
        if($this->settings_model->isDisabled(18)) { // Return empty if page module is disabled
            return '';
        }
        return $this->feed_page_condition;
    }
    
    function get_user_most_active_pages($user_id, $limit, $returnOnlyIds = false) {
        $this->db->select(' COUNT(A.ActivityID) A_Count, P.PageID');
        
        $this->db->from(ACTIVITY . ' A');        
        $this->db->join(PAGEMEMBERS . ' PM', "PM.PageID = A.ModuleEntityID AND A.ModuleID = 18 AND PM.UserID = $user_id ", 'left');            
        $this->db->join(PAGES . ' P', " P.PageID = PM.PageID ", 'left');
        
        $this->db->where("P.PageID IS NOT NULL", NULL, FALSE);    
        
        $this->db->group_by('P.PageID');
        $this->db->order_by('A_Count', 'DESC');      
        
        $this->db->limit($limit);
        
        $query = $this->db->get();        
        $rowSet = $query->result_array();   //echo $this->db->last_query(); echo '============='; die;
        
        if(!$returnOnlyIds) {
            return $rowSet;
        }
        
        $ids = [];
        foreach ($rowSet as $row) {
            $ids[] = $row['PageID'];
        }
        
        return $ids;
        
    }
    
    function get_parent_category($category_id)
    {
        $this->db->select('ParentID');
        $this->db->from(CATEGORYMASTER);
        $this->db->where('CategoryID', $category_id);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->row()->ParentID;
        }
    }

    function get_page_category($page_guid)
    {
        $this->db->select('CategoryID');
        $this->db->from(PAGES);
        $this->db->where('PageGUID', $page_guid);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->row()->CategoryID;
        }
    }

    /**
     * [getFilteredPages used to get filtered pages]
     */
    function get_filtered_pages($user_id, $search_text, $category_id = 0, $limit = 8, $offset = 1, $count_only = 0,$city_id = 0,$order_by = '')
    {
        $pages = array();
        $this->load->model('users/user_model');
        $friend_followers_list = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $friends = $friend_followers_list['Friends'];
        $friends[] = $user_id;

        $this->db->select('if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture,PU.Url as CreatedProfileUrl,P.Title,P.Description,P.PageGUID,CMP.Name as Category,CMP.CategoryID,CM.Icon as CategoryIcon,P.CreatedDate,P.LastActionDate as LastActivity,CONCAT(U.FirstName," ",U.LastName) as CreatedBy,P.PageURL,P.IsVerified,P.NoOfLikes,P.WebsiteURL,P.NoOfFollowers,U.UserGUID AS CreatorGUID,P.Popularity', false);
        if ($friends)
        {
            $this->db->select('(SELECT COUNT(UserID) FROM ' . FOLLOW . ' WHERE UserID IN (' . implode(',', $friends) . ') AND Type="page" AND StatusID="2" AND TypeEntityID=P.PageID) as FriendsCount', false);
        }
        else
        {
            $this->db->select("'0' as FriendsCount",false);
        }
        
        $search_text = $this->db->escape_like_str($search_text); 
        
        $this->db->select("IF(P.Title LIKE '%" . $search_text . "%',1,2) as OrdBy", false);
        $this->db->from(PAGES . ' P');
        $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=P.PageID', 'left');
        $this->db->join(CATEGORYMASTER . ' CMP', 'CMP.CategoryID=EC.CategoryID', 'left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(CATEGORYMASTER . ' CM', 'IF(CMP.ParentID=0,CMP.CategoryID,CMP.ParentID)=CM.CategoryID', 'left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->join(USERS . ' U', 'U.UserID=P.UserID', 'left');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID', 'left');

        $this->db->_protect_identifiers = FALSE;
        $this->db->select("IFNULL(PM.ModuleRoleID,1) as UserRole");
        $this->db->join(PAGEMEMBERS." PM","PM.PageID=P.PageID AND PM.StatusID='2' AND PM.UserID='".$user_id."'",'left');
        $this->db->_protect_identifiers = TRUE;

        $this->db->where('PU.EntityType', 'User');
        
        if ($category_id)
        {
            if(is_array($category_id))
            {
                $cond = "ParentID IN('" . implode(',',$category_id) . "')) OR CMP.CategoryID IN('" . implode(',',$category_id) . "')";
            }
            else
            {
                $cond = "ParentID='" . $category_id . "') OR CMP.CategoryID='" . $category_id . "'";
            }
            $this->db->where("(CMP.CategoryID IN(SELECT CategoryID FROM " . CATEGORYMASTER . " WHERE ".$cond.")", NULL, FALSE);
        }

        if($city_id)
        {
            if(is_array($city_id))
            {
                $this->db->where_in('P.CityID',$city_id);
            }
            else
            {
                $this->db->where('P.CityID',$city_id);
            }
        }
        /* $this->db->like('P.Title',$search_text);
          $this->db->or_like('P.Description',$search_text); */
        $this->db->where("(P.Title LIKE '%" . $search_text . "%' OR P.Description LIKE '%" . $search_text . "%' OR CM.Name LIKE '%".$search_text."%' OR CMP.Name LIKE '%".$search_text."%')", NULL, FALSE);
        $this->db->where('EC.ModuleID', $this->module_id);
        $this->db->where('P.StatusID', '2');
        if($order_by == 'NameAsc')
        {
            $this->db->order_by('P.Title','ASC');
        }
        else if($order_by == 'NameDesc')
        {
            $this->db->order_by('P.Title','DESC');
        }
        else if($order_by == 'Members')
        {
            $this->db->order_by('P.NoOfFollowers','DESC');
        }
        else if($order_by == 'ActivityLevel')
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD(P.Popularity,"HIGH","Moderate","Low")');
            $this->db->_protect_identifiers = TRUE;
        }
        else if($order_by == 'Recent Updated')
        {
            $this->db->order_by('P.ModifiedDate','DESC');
        }
        else
        {
            $this->db->order_by('UserRole','DESC');
        }
        if ($friends)
        {
            $this->db->order_by('FriendsCount', 'DESC');
        }
        $this->db->group_by('P.PageID');
        $this->db->_protect_identifiers = FALSE;
        $this->db->having('ProfilePicture!=', '""');
        $this->db->_protect_identifiers = TRUE;
        $this->db->order_by('OrdBy', 'ASC');
        $this->db->order_by('P.LastActionDate', 'DESC');
        $this->db->order_by('P.NoOfLikes', 'DESC');
        if (!$count_only)
        {
            $this->db->limit($limit, $this->get_pagination_offset($offset, $limit));
        }
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($count_only)
        {
            return $query->num_rows();
        }
        if ($query->num_rows())
        {
            foreach ($query->result_array() as $page)
            {
                $page['FollowStatus'] = 0;
                if($this->check_user_follow_page_status($page['PageGUID'], $user_id))
                {
                    $page['FollowStatus'] = 1;
                }
                $page['Friends'] = $this->get_friends_in_page($user_id,$page['PageGUID'],2);
                unset($page['OrdBy']);
                $pages[] = $page;
            }
        }
        return $pages;
    }

    /**
     * [get_pages  used to get list of pages ]
     * @param [array]       $input      [OrderBy, SearchText, UserID, Limit, Offset, LoginSessionKey]
     */
    function get_pages($input)
    {
        $return = '';
        // Mobile Api Change
        if ($input['ListingType'] == 'Joined' || $input['ListingType'] == 'All') 
        {
            // get user folloe pages
            $this->db->select('Group_Concat(F.TypeEntityID) as pageIDS');
            $this->db->from(FOLLOW . " as F");
            $this->db->join(PAGES . " as P", 'F.EntityOwnerID = P.PageID');
            $this->db->where('F.UserID', $input['UserID']);
            $this->db->where('F.Type', 'page');
            $this->db->where_not_in('P.UserID', $input['UserID']);
            $query = $this->db->get();
            if ($query->num_rows() > 0)
            {
                $follow_page_ids = $query->row_array();
                $follow_page_ids = $follow_page_ids['pageIDS'];
            } else
            {
                $follow_page_ids = '';
            }
        }

        $this->db->select('Group_Concat(PM.PageID) as PageIDs');
        $this->db->from(PAGEMEMBERS . " as PM");
        $this->db->where('PM.UserID', $input['UserID']);
        $members_pages = array(); 

        // old code
        /*if ($input['ListingType'] == 'Joined')
        {
            $this->db->where('(PM.ModuleRoleID = 7 OR PM.ModuleRoleID = 8 )');
            //$this->db->where_in('PM.PageID',explode(",",$follow_page_ids));                    
            $member_query = $this->db->get();
            $resultArr = $member_query->row_array();

            $page_ids = explode(",", $resultArr['PageIDs']);

            $follow_page_ids = explode(",", $follow_page_ids);
            $members_pages = array_diff($follow_page_ids, array_intersect($follow_page_ids, $page_ids));
        } else
        {
            $this->db->where('(PM.ModuleRoleID = 7 OR PM.ModuleRoleID = 8 )');
            $member_query = $this->db->get();
            $resultArr = $member_query->row_array();
            $members_pages = explode(",", $resultArr['PageIDs']);
        }*/

        // new code
        $this->db->where('(PM.ModuleRoleID = 7 OR PM.ModuleRoleID = 8 )');  
        $member_query = $this->db->get();  
        $resultArr = $member_query->row_array();

        if ($input['ListingType'] == 'Joined')
        {

            $page_ids = explode(",", $resultArr['PageIDs']);

            $follow_page_ids = explode(",", $follow_page_ids);
            $members_pages = array_diff($follow_page_ids, array_intersect($follow_page_ids, $page_ids));
        }  
        else if ($input['ListingType'] == 'All') 
        {
            $page_ids = explode(",", $resultArr['PageIDs']);            
            $members_pages = array_merge($page_ids,explode(",", $follow_page_ids) );
        } 
        else
        {
            $members_pages = explode(",", $resultArr['PageIDs']);
        }
        
        $this->db->select('P.PageID,P.PageGUID,U.UserGUID,P.Title,P.Description,P.CategoryID as Type,CM.Name as Category,CM.Icon as CategoryIcon,P.CreatedDate as CreateDate,P.NoOfViews as ViewCount,P.LastActionDate as LastActivity,U.FirstName as CreatedByFirstName,U.LastName as CreatedByLastName,P.PostalCode,P.IsVerified,P.PageURL,P.WebsiteURL,P.NoOfLikes,P.NoOfFollowers, if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture,PL.Url as CreatedByURL', false);

        // Mobile Api Change
        if($this->IsApp == 1){
            $this->db->select('U.UserGUID as CreatedByUserGUID, CoverPicture',false);
        }

        $this->db->from(USERS . " as U");
        $this->db->join(PROFILEURL . " as PL", 'PL.EntityID=U.UserID', 'left');
        $this->db->join(PAGES . " as P", 'U.UserID = P.UserID and P.StatusID != 3');
        $this->db->join(CATEGORYMASTER . " as CM", 'P.CategoryID = CM.CategoryID');
        $this->db->where('PL.EntityType', 'User');
        if ($input['SearchText'] != "")
        {
            $this->db->like('P.Title', $input['SearchText']);
        }
        if ($input['SortBy'] != '')
        {
            $this->db->order_by($input['SortBy'], $input['OrderBy']);
        }

        if (!empty($members_pages))
        {
            $this->db->where_in('P.PageID', $members_pages);
        } else
        {
            $this->db->where_in('P.PageID', 0);
        }

        $this->db->limit($input['Limit'], $input['Offset']);
        $result = $this->db->get();
        if ($result->num_rows() > 0)
        {
            return $result->result_array();
        } else
        {
            return array();
        }
    }
    /**
     * [get_pages  used to get list of pages ]
     * @param [array]       $input      [OrderBy, SearchText, UserID, Limit, Offset, LoginSessionKey]
     */
    function my_pages($user_id)
    {
        $entity = array();
        $userdata=array();
        /*if (CACHE_ENABLE) {
                $entity = $this->cache->get('mypage_'.$user_id);
                $user_file_data = $this->cache->get('user_profile_'.$user_id);
                if(!empty($user_file_data)){
                  $userdata['ModuleEntityGUID']=$user_file_data['UserGUID'];  
                  $userdata['ModuleID']=3;  
                  $userdata['Name']=$user_file_data['EntityName'];  
                  $userdata['ProfilePicture']=$user_file_data['ProfilePicture'];  
                }
            }*/
        if(empty($entity))
        {
            if(empty($userdata))
            {
                $this->db->select('UserGUID as ModuleEntityGUID,CONCAT(FirstName," ",LastName) as Name, IF(ProfilePicture="","",ProfilePicture) as ProfilePicture, "3" as ModuleID',false);
                $this->db->from(USERS);
                $this->db->where('UserID',$user_id);
                $query = $this->db->get();
                if($query->num_rows())
                {
                    $entity[] = $query->row_array();
                }
            }
            else
            {
                $entity[] = $userdata;
            }
            $this->db->select('P.PageGUID as ModuleEntityGUID,P.Title as Name,  if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture, "18" as ModuleID',false);
            $this->db->from(PAGES.' P');
            $this->db->join(PAGEMEMBERS.' PM','P.PageID=PM.PageID AND PM.StatusID=2 AND PM.ModuleRoleID IN(7,8)');
            $this->db->join(CATEGORYMASTER.' CM','CM.CategoryID=P.CategoryID','left');
            $this->db->group_by('P.PageID');
            $this->db->where('PM.UserID',$user_id);
            $this->db->where('P.StatusID',2);

            $query = $this->db->get();
            //echo $this->db->last_query();die;
            if($query->num_rows())
            {
                foreach($query->result_array() as $item){
                    $entity[] =$item;
                }
            }
            if (CACHE_ENABLE) {
                    $this->cache->save('mypage_'.$user_id, $entity,CACHE_EXPIRATION);
                }
        }
        return $entity;
    }

    /**
     * [get_total_pages  used to get no of pages]
     * @param [string]  $user_id   [UserID]
     */
    function get_total_pages($user_id , $all_pages=FALSE)
    {
        $this->db->select('count(P.PageID) as TotalRecords');
        $this->db->from(PAGES . " as P");
	if(!$all_pages)
	{
		$this->db->join(USERS . " as U", 'P.UserID = U.UserID');
		$this->db->where('U.UserID', $user_id);
	}
	else
	{
		$this->db->where("EXISTS (SELECT PageMemeberID FROM `PageMembers` WHERE PageID=P.PageID AND UserID='".$user_id."') ", NULL, FALSE);
	}
        $this->db->where('P.StatusID', 2);
      
        $query = $this->db->get();
        $result = $query->row_array();
        if (!empty($result))
        {
            return $result['TotalRecords'];
        } else
        {
            return false;
        }
    }

    /**
     * [create Used to Create Pages]
     * @param  [array]$input        [PageGUID, Title, Description, ModifiedDate, LastActionDate, StatusID, VerificationRequest, PageURL, Location, 
     *                               PostalCodePhone, CategoryIds, WebsiteURL, UserID, CategoryID, ProfilePicture, CoverPicture]        
     */
    function create($input)
    {
        $error = 0;
        $input['PageURL'] = str_replace(" ", "", $input['PageURL']);
        $page_url = $input['PageURL'];
        $page_guid = $input['PageGUID'];
        $return = array();
        $this->db->select('Title, PageURL, CategoryID, UserID');
        $this->db->where('LOWER(Title)', strtolower($input['Title']));
        $this->db->or_where('LOWER(PageURL)', strtolower($input['PageURL']));
        $checkquery = $this->db->get(PAGES);
        if ($checkquery->num_rows() > 0)
        {
            $result = $checkquery->result_array();
            foreach ($result as $row)
            {
                if (strtolower($row['Title']) == strtolower($input['Title']))
                {
                    if ($row['CategoryID'] == $input['CategoryID'] || $row['UserID'] == $input['UserID'])
                    {
                        $error = 1;
                        return 509;
                    }
                }

                if (strtolower($row['PageURL']) == strtolower($input['PageURL']))
                {
                    $error = 1;
                    return 412;
                }
            }
        }

        if ($error == 0)
        {
            $input['IsVerified'] = 0;
            if (!empty($input['Location']))
            {
                $location = explode(',', $input['Location']);
                if (count($location) > 2)
                {
                    $country = trim($location['2']);
                    $state = trim($location['1']);
                    $city = trim($location['0']);
                } else
                {
                    $country = trim($location['1']);
                    $state = trim($location['0']);
                }

                //for get city, state and country ids
                $city = ($city) ? $city : '';
                $state = ($state) ? $state : '';
                $country = ($country) ? $country : '';

                $location_array = array("City" => $city, "Country" => $country, "CountryCode" => $input['CountryCode'], "State" => $state, "StateCode" => $input['StateCode']);
                $this->load->helper('location');
                $location_data = update_location($location_array);
                $input['CountryID'] = $location_data['CountryID'];
                $input['StateID'] = $location_data['StateID'];
                $input['CityID'] = $location_data['CityID'];
            }
            unset($input['CountryCode']);
            unset($input['StateCode']);
            $category_ids = $input['CategoryIds'];
            unset($input['Location']);
            unset($input['CategoryIds']);

            $this->db->insert(PAGES, $input);
            $page_id = $this->db->insert_id();
            //$this->page_cache($page_id);
            initiate_worker_job('page_cache', array('PageID'=>$page_id ));
            $entity = array();
            for ($i = 0; $i < sizeof($category_ids); $i++)
            {
                $entity_category = array();
                $entity_category['EntityCategoryGUID'] = get_guid();

                $entity_category['CategoryID'] = $category_ids[$i];

                $entity_category['ModuleID'] = $this->module_id;
                $entity_category['ModuleEntityID'] = $page_id;
                $entity_category['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');

                $entity[] = $entity_category;
            }
            if ($entity != "" and ! empty($entity))
            {
                $this->db->insert_batch(ENTITYCATEGORY, $entity);
            }

            // $this->db->insert(ALBUMS, array('AlbumGUID' => get_guid(), 'AlbumName' => $input['Title'], 'UserID' => $input['UserID'], 'ModuleID' => $this->module_id, 'ModuleEntityID' => $page_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'Description' => $input['Description']));

            $user_id = $input['UserID'];
            //Create Defualt album
            create_default_album($user_id, $this->module_id, $page_id);

            $this->load->model('subscribe_model');
            $this->subscribe_model->toggle_subscribe($user_id, 'PAGE', $page_id);

            #get ModuleRoleID - starts
            $this->db->select('ModuleRoleID');
            $this->db->where('ModuleID', $this->module_id);
            $this->db->where('RoleName', 'Creator');
            $checkquery = $this->db->get(MODULEROLES);
            $result = $checkquery->row();
            #get ModuleRoleID - ends
            $this->db->insert(PAGEMEMBERS, array('PageID' => $page_id, 'UserID' => $user_id, 'ModuleRoleID' => $result->ModuleRoleID, 'StatusID' => 2));
            $return['PageGUID'] = $page_guid;
            $return['PageURL'] = $page_url;

            $this->subscribe_model->subscribe_email($user_id, $page_id, 'create_page');
            if (CACHE_ENABLE) 
            {
               $this->cache->delete('mypage_'.$user_id);
            }
            return $return;
        }
    }

    /**
     * [update Used to update Pages]
     * @param  [array]$input             [PageGUID, Title, Description, ModifiedDate, LastActionDate, StatusID, VerificationRequest, PageURL,                                           Location, PostalCodePhone, CategoryIds, WebsiteURL, UserID, CategoryID, ProfilePicture, CoverPicture]
     * @return [int] $page_guid         
     */
    function update($input)
    {
        $error = 0;
        $input['PageURL'] = str_replace(" ", "", $input['PageURL']);
        $input['IsVerified'] = isset($input['IsVerified']) ? $input['IsVerified'] : 0;
        $this->db->select('Title, PageURL, CategoryID, UserID');
        $this->db->where('PageGUID != ', $input['PageGUID']);
        $this->db->where('(LOWER(Title) = "' . strtolower($input['Title']) . '" OR LOWER(PageURL) = "' . strtolower($input['PageURL']) . '")');
        $checkquery = $this->db->get(PAGES);

        if ($checkquery->num_rows() > 0)
        {
            $result = $checkquery->result_array();
            foreach ($result as $row)
            {
                if (strtolower($row['Title']) == strtolower($input['Title']))
                {
                    if ($row['CategoryID'] == $input['CategoryID'] || $row['UserID'] == $input['UserID'])
                    {
                        $error = 1;
                        $return['ResponseCode'] = 509;
                        $return['Message'] = lang('page_exist');
                        return $return;
                    }
                }
                if (strtolower($row['PageURL']) == strtolower($input['PageURL']))
                {
                    $error = 1;
                    $return['ResponseCode'] = 412;
                    $return['Message'] = lang('pageURL_exist');
                    return $return;
                }
            }
        }

        if ($error == 0)
        {
            $return = $this->return;
            $result = $this->db->select('PageGUID, PageID');
            $this->db->where('PageGUID', $input['PageGUID']);
            $result = $this->db->get(PAGES);
            $result = $result->row_array();
            if (!empty($result))
            {
                $country = '';
                $state = '';
                $city = '';
                if (!empty($input['Location']))
                {
                    $location = explode(',', $input['Location']);
                    $city = $location['0'];
                    $state = $location['1'];
                    $country = $location['2'];
                }

                //for get city, state and country ids
                $location_array = array("City" => $city, "Country" => $country, "CountryCode" => $input['CountryCode'], "State" => $state, "StateCode" => $input['StateCode']);
                $this->load->helper('location');
                $location_data = update_location($location_array);
                $input['CountryID'] = $location_data['CountryID'];
                $input['StateID'] = $location_data['StateID'];
                $input['CityID'] = $location_data['CityID'];

                $category_ids = $input['CategoryIds'];
                unset($input['Location']);
                unset($input['CategoryIds']);

                $this->db->where('PageID', $result['PageID']);
                $this->db->update(PAGES, array(
                    'Name' => $input['Name'],
                    'Mobile' => $input['Mobile'],
                    'Email' => $input['Email'],
                    'Latitude' => $input['Latitude'],
                    'Longitude' => $input['Longitude'],
                    'WorkingHours' => $input['WorkingHours'],
                    'Title' => EscapeString($input['Title']), 'Description' => $input['Description'], 
                    'WebsiteURL' => $input['WebsiteURL'], 'PageURL' => $input['PageURL'], 'CityID' => $input['CityID'], 
                    'StateID' => $input['StateID'], 'CountryID' => $input['CountryID'], 'Phone' => $input['Phone'], 'PostalCode' => $input['PostalCode'], 
                    'StatusID' => $input['StatusID'], 'VerificationRequest' => $input['VerificationRequest'], 
                    'IsVerified' => $input['IsVerified'], 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 
                    'LastActionDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'Verified' => 0   // Change status to unverified on profile update.
                ));

                $this->db->where('ModuleEntityID', $result['PageID']);
                $this->db->where('ModuleID', $this->module_id);
                $checkquery = $this->db->get(ENTITYCATEGORY);
                $row = $checkquery->num_rows();
                if (!empty($row))
                {
                    $this->db->where('ModuleID', $this->module_id);
                    $this->db->where('ModuleEntityID', $result['PageID']);
                    $this->db->delete(ENTITYCATEGORY);
                }
                $entity = array();
                for ($i = 0; $i < sizeof($category_ids); $i++)
                {
                    $entity_category = array();
                    $entity_category['EntityCategoryGUID'] = get_guid();

                    $entity_category['CategoryID'] = $category_ids[$i];

                    $entity_category['ModuleID'] = $this->module_id;
                    $entity_category['ModuleEntityID'] = $result['PageID'];
                    $entity_category['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');

                    $entity[] = $entity_category;
                }
                if ($entity != "" and ! empty($entity))
                {
                    $this->db->insert_batch(ENTITYCATEGORY, $entity);
                }
                $return['ResponseCode'] = 200;
                $return['Message'] = lang('page_updated');
                $return['Data']['PageGUID'] = $input['PageGUID'];
                $return['Data']['PageURL'] = $input['PageURL'];
                if (CACHE_ENABLE) 
                {
                   $this->cache->delete('mypage_'.$input['UserID']);
                }
                initiate_worker_job('page_cache', array('PageID'=>$result['PageID'] ));
                return $return;
            } else
            {
                $return['ResponseCode'] = 412;
                $return['Message'] = lang('invalid_PageGUID');
            }
            return $return;
        }
    }

    /**
     * [get_page_detail Used to get users detail's]
     * @param  [array]  $input    [UserLoginID, PageGUID]
     */
    function get_page_detail($input)
    {
        $this->db->select('P.PageID,P.PageGUID,P.UserID,P.Title,P.Description,P.CategoryID as Type,P.CategoryID as MainCategoryID,P.CreatedDate as DateCreated,P.NoOfViews as ViewCount,P.LastActionDate as LastActivity,P.PostalCode,P.IsVerified,P.PageURL,P.WebsiteURL,P.NoOfLikes,P.NoOfFollowers,U.FirstName as CreatedBy,C.Name as City,S.Name as State,CNM.CountryName as Country,P.VerificationRequest,CM.Icon as LogoImage,CM.Name as Category,U.UserID as PageCreaterUserID,GROUP_CONCAT(EC.CategoryID) as SubCategoryID,P.Phone,if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture,P.CoverPicture as ProfileCover,if(P.CoverPicture!="","1","0") as CoverExists', false);
        
        // Mobile Api Change
        if($this->IsApp == 1){
            $this->db->select('U.UserGUID as CreatedByGUID,CONCAT_WS(", ", C.Name, S.Name, CNM.CountryName) AS Location,GROUP_CONCAT(SCM.Name) as SubCategoryName,, P.WorkingHours, P.Name, P.Mobile, P.Email,P.Latitude,P.Longitude');
        }

        $this->db->from(USERS . " as U");
        $this->db->join(PAGES . " as P", 'U.UserID = P.UserID');
        $this->db->join(CATEGORYMASTER . " as CM", 'P.CategoryID = CM.CategoryID');
        $this->db->join(ENTITYCATEGORY . " as EC", 'EC.ModuleEntityID = P.PageID and EC.ModuleID = ' . $this->module_id);
        // Mobile Api Change
        if($this->IsApp == 1){
            $this->db->join(CATEGORYMASTER . " as SCM", 'EC.CategoryID = SCM.CategoryID');
        }
        $this->db->join(CITIES . " as C", 'C.CityID = P.CityID', 'left');
        $this->db->join(STATES . " as S", 'S.StateID = P.StateID', 'left');
        $this->db->join(COUNTRYMASTER . " as CNM", 'CNM.CountryID = P.CountryID', 'left');
        $this->db->where('EC.ModuleID', $this->module_id);
        $this->db->where('P.PageGUID', $input['PageGUID']);
        //$this->db->where('P.IsVerified', 1);
        $result = $this->db->get();
        $result = $result->row_array();
        if ($result['PageID'])
        {
            $result['IsFollowed'] = $this->check_user_follow_page_status($input['PageGUID'], $input['UserLoginID']);
            $result['IsLiked'] = $this->check_user_liked_page_status($input['PageGUID'], $input['UserLoginID']);
            $result['IsPremission'] = $this->get_user_page_permission($input['PageGUID'], $input['UserLoginID']);
            $result['currentUserID'] = $input['UserLoginID'];
            $result['IsOwner'] = 0;
            $result['IsSubscribed'] = $this->subscribe_model->is_subscribed($result['currentUserID'], 'PAGE', $result['PageID']);
            if ($result['UserID'] == $input['UserLoginID'])
            {
                $result['IsOwner'] = 1;
            }

            $result['ProfilePicture'] = $result['ProfilePicture'];

            // Mobile Api Change
            if($this->IsApp == 1){
                /*Get Page Followers*/
                $current_user_id = $this->UserID;
                $page_id = get_detail_by_guid($input['PageGUID'], $this->module_id);
                 $is_blocked = check_blocked_user($current_user_id, $this->module_id, $page_id);

                /*Get Wall Album Details*/
                $InputAr['field'] = 'AlbumID';
                $InputAr['table'] = ALBUMS;
                $InputAr['where'] = array('AlbumName' => 'Wall Media', 'ModuleID' => 18, 'ModuleEntityID'=>'1');
                $InputAr['limit'] = 1;                   
                $InputAr['orderBy'] = 'MediaID DESC';
                $Album        =  get_data_new($InputAr);
                $result['Media'] =FALSE;
                if(!empty($Album)){
                    /* Get media Data - starts */
                    $InputAr['field']   = 'MediaGUID,ImageName';
                    $InputAr['table']   = MEDIA;
                    $InputAr['where']   = array('AlbumID' => $Album['AlbumID']);
                    $InputAr['limit']   = 3;                   
                    $InputAr['orderBy'] = 'MediaID DESC';
                    $result['Media']    =  get_data_new($InputAr);
                }
if($this->IsApp == 1){ /*For Mobile */
                /*Get group members - Added By Gautam */
                $input = array('PageID'=>$result['PageID'],'SearchText'=>'','UserID'=>$this->UserID,'Type'=>'All','OrderBy'=>'CreatedDate','Status'=>2);
                $result['Follower']      = $this->get_page_follower($input,0,5,$this->UserID);
                /*Get group members - - ends*/
                /*Get media - Added By Gautam - starts */
                $result['Media']        =   $this->get_page_media($result['PageID'], 3);
                /*Get media - Added By Gautam - ends */
}            
            }
            return $result;
        } else
        {
            return '0';
        }
        
    }
    /**
     * [get_page_detail Used to get users detail's]
     * @param  [array]  $input    [UserLoginID, PageGUID]
     */
    function get_page_detail_cache($page_id)
    {
        $result=array();
        if (CACHE_ENABLE) 
        {
            $result = $this->cache->get('page_'.$page_id);
        }
        if(empty($result))
        {
            $this->db->select('P.PageGUID ,P.Title,  if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture, PageURL',false);
            $this->db->from(PAGES.' P');
            $this->db->join(CATEGORYMASTER.' CM','CM.CategoryID=P.CategoryID','left');
            $this->db->where('P.PageID',$page_id);
            $query_page = $this->db->get();
            $result=$query_page->row_array();
        }
        return $result;
    }

    /**
     * [get_page_detail_by_page_url Used to get page detail's]
     * @param  string $page_url
     */
    function get_page_detail_by_page_url($page_url)
    {
        $this->db->select('P.PageID,P.PageGUID,P.UserID,P.Title,P.Description,P.CategoryID as Type,P.CreatedDate as DateCreated,P.NoOfViews as ViewCount,P.LastActionDate as LastActivity,P.PostalCode,P.IsVerified,P.PageURL,P.WebsiteURL,P.NoOfLikes,P.NoOfFollowers,U.FirstName as CreatedBy,C.Name as City,S.Name as State,CNM.CountryName as Country,P.VerificationRequest,CM.Icon as LogoImage,CM.Name as Category,U.UserID as PageCreaterUserID,GROUP_CONCAT(EC.CategoryID) as SubCategoryID,P.Phone,if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture,P.CoverPicture as ProfileCover,if(P.CoverPicture!="","1","0") as CoverExists', false);
        $this->db->from(USERS . " as U");
        $this->db->join(PAGES . " as P", 'U.UserID = P.UserID');
        $this->db->join(CATEGORYMASTER . " as CM", 'P.CategoryID = CM.CategoryID');
        $this->db->join(ENTITYCATEGORY . " as EC", 'EC.ModuleEntityID = P.PageID and EC.ModuleID = ' . $this->module_id);
        $this->db->join(CITIES . " as C", 'C.CityID = P.CityID', 'left');
        $this->db->join(STATES . " as S", 'S.StateID = P.StateID', 'left');
        $this->db->join(COUNTRYMASTER . " as CNM", 'CNM.CountryID = P.CountryID', 'left');
        $this->db->where('EC.ModuleID', $this->module_id);
        $this->db->where('P.PageURL', $page_url);
        $this->db->where('P.StatusID', 2);
        $result = $this->db->get();
        $result = $result->row_array();
        if ($result['PageID'])
        {
            return $result;
        } else
        {
            return '0';
        }
    }

    function get_page_detail_by_page_id($page_id)
    {
        $this->db->select('P.PageID,P.PageGUID,P.UserID,P.Title,P.Description,P.CategoryID as Type,P.CreatedDate as DateCreated,P.NoOfViews as ViewCount,P.LastActionDate as LastActivity,P.PostalCode,P.IsVerified,P.PageURL,P.WebsiteURL,P.NoOfLikes,P.NoOfFollowers,U.FirstName as CreatedBy,C.Name as City,S.Name as State,CNM.CountryName as Country,P.VerificationRequest,CM.Icon as LogoImage,CM.Name as Category,U.UserID as PageCreaterUserID,GROUP_CONCAT(EC.CategoryID) as SubCategoryID,P.Phone,if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture,P.CoverPicture as ProfileCover,if(P.CoverPicture!="","1","0") as CoverExists', false);
        $this->db->from(USERS . " as U");
        $this->db->join(PAGES . " as P", 'U.UserID = P.UserID');
        $this->db->join(CATEGORYMASTER . " as CM", 'P.CategoryID = CM.CategoryID');
        $this->db->join(ENTITYCATEGORY . " as EC", 'EC.ModuleEntityID = P.PageID and EC.ModuleID = ' . $this->module_id);
        $this->db->join(CITIES . " as C", 'C.CityID = P.CityID', 'left');
        $this->db->join(STATES . " as S", 'S.StateID = P.StateID', 'left');
        $this->db->join(COUNTRYMASTER . " as CNM", 'CNM.CountryID = P.CountryID', 'left');
        $this->db->where('EC.ModuleID', $this->module_id);
        $this->db->where('P.PageID', $page_id);
        $result = $this->db->get();
        $result = $result->row_array();
        if ($result['PageID'])
        {
            return $result;
        } else
        {
            return '0';
        }
    }

    /**
     * [get_page_follower Used to get follower users list]
     * @param  [array]  $input    [UserLoginID, PageGUID]
     */
    function get_follower($input, $page_no, $page_size = PAGE_SIZE,$count_only=false)
    {
        $results = array();
        if ($input['PageID'])
        {
            $this->db->where("PageMembers.PageID", $input['PageID']);
        }
        if ($input['SearchText'])
        {
            $this->db->where(" (Users.FirstName like'" . $this->db->escape_like_str($input['SearchText']) . "%' or  Users.LastName like '" . $this->db->escape_like_str($input['SearchText']) . "%' or concat(Users.FirstName,' ',Users.LastName) like '" . $this->db->escape_like_str($input['SearchText']) . "%')", NULL, FALSE);
        }
        $this->db->select('Pages.PageGUID,PageMembers.CanPostOnWall,PageMembers.PageMemeberID, PageMembers.PageID,PageMembers.ModuleRoleID,PageMembers.UserID ,Users.UserGUID, Users.FirstName,Users.UserID,Users.ProfilePicture,Users.LastName');
        $this->db->select('p.Url as ProfileLink');
        $this->db->from(PAGES);
        $this->db->join(PAGEMEMBERS, 'Pages.PageID = PageMembers.PageID', 'left');
        $this->db->join(USERS, 'PageMembers.UserID = Users.UserID', 'inner');
        $this->db->join(USERDETAILS . ' ud', 'ud.UserID = Users.UserID');
        //$this->db->join(USERPRIVACY.' UP','UP.UserID=U.UserID','left');
        $this->db->join(PROFILEURL . " as p", "p.EntityID = Users.UserID and p.EntityType = 'User'", "LEFT");
        $this->db->where("PageMembers.StatusID", '2');
        //$sql = $this->db->_compile_select();

        if($input['Type'] == 'Followers')
        {
            $this->db->where('PageMembers.ModuleRoleID','9');
        }
        else
        {
            $this->db->where_in('PageMembers.ModuleRoleID',array('7','8'));
        }
        $this->db->order_by('PageMembers.PageMemeberID');

        if(!$count_only)
        {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }

        $query = $this->db->get();
        $num_rows = $query->num_rows();
        if($count_only)
        {
            return $num_rows;
        }

        $data = array();
        
        foreach($query->result_array() as $key=>$val)
        {
            /*get image or video - by gautam - starts*/
            if($this->IsApp == 1)
            { 
                /*For Mobile */
                $val[$key]['Location'] = $this->user_model->get_user_location($val['UserID']);                
                $val[$key]['MediaType'] ='PHOTO';
                if($val[$key]['ProfilePicture']!=''){
                    $str = explode(".",$val[$key]['ProfilePicture']);
                    if($str[1]!='jpg'){
                        $val[$key]['MediaType'] ='VIDEO';
                    }
                }
                /*privacy check - starts added by gautam*/
                /*privacy check - ends added by gautam*/
                if ($input['UserID'] != $val['UserID'])
                {
                    $users_relation = get_user_relation($input['UserID'], $val['UserID']);
                    $privacy_details = $this->privacy_model->details($val['UserID']);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label'])
                    {
                        foreach ($privacy_details['Label'] as $privacy_label)
                        {
                            if(isset($privacy_label[$privacy]))
                            {
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation))
                                {
                                    $val[$key]['ProfilePicture'] = '';
                                }
                            }
                        }
                    }
                }
            }
            /*get image or video - by gautam - ends*/
            $data[] = $val;
        }
        return $data;
    }

    /**
     * [get_page_follower Used to get follower users list]
     * @param  [array]  $input    [UserLoginID, PageGUID]
     */
    function get_page_follower($input, $offset, $page_size = PAGE_SIZE)
    {
        $results = array();
        if ($input['PageID'])
        {
            $this->db->where("PageMembers.PageID", $input['PageID']);
        }
        if ($input['SearchText'])
        {
            $this->db->where(" (Users.FirstName like'" . $this->db->escape_like_str($input['SearchText']) . "%' or  Users.LastName like '" . $this->db->escape_like_str($input['SearchText']) . "%' or concat(Users.FirstName,' ',Users.LastName) like '" . $this->db->escape_like_str($input['SearchText']) . "%')", NULL, FALSE);
        }
        /*$privacy_condition = "
            IF(UP.Value='everyone',true, 
                IF(UP.Value='network', U.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
                IF(UP.Value='friend',U.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
            )
        ";*/
        $this->db->select('Pages.PageGUID,PageMembers.CanPostOnWall,PageMembers.PageMemeberID, PageMembers.PageID,PageMembers.ModuleRoleID,PageMembers.UserID ,Users.UserGUID, Users.FirstName,Users.UserID,Users.ProfilePicture,Users.LastName');
        $this->db->select('p.Url as ProfileLink');
        $this->db->from(PAGES);
        $this->db->join(PAGEMEMBERS, 'Pages.PageID = PageMembers.PageID', 'left');
        $this->db->join(USERS, 'PageMembers.UserID = Users.UserID', 'inner');
        $this->db->join(USERDETAILS . ' ud', 'ud.UserID = Users.UserID');
        //$this->db->join(USERPRIVACY.' UP','UP.UserID=U.UserID','left');
        $this->db->join(PROFILEURL . " as p", "p.EntityID = Users.UserID and p.EntityType = 'User'", "LEFT");
        $this->db->where("PageMembers.StatusID", '2');
        $sql = $this->db->_compile_select();

        // fetch creator and admin list
        $creator_query = $sql . " AND PageMembers.ModuleRoleID IN (7,8) ORDER BY PageMembers.PageMemeberID LIMIT " . $offset . "," . $page_size;
        $creator_list = $this->db->query($creator_query)->result_array();

        if($this->IsApp == 1){ /*For Mobile added by gautam*/
            foreach($creator_list as $key=>$val)
            {
                $creator_list[$key]['Location'] = $this->user_model->get_user_location($val['UserID']);
                /*get image or video - by gautam - starts*/
                if($this->IsApp == 1){ /*For Mobile */
                $creator_list[$key]['MediaType'] ='PHOTO';
                    if($creator_list[$key]['ProfilePicture']!=''){
                        $str = explode(".",$creator_list[$key]['ProfilePicture']);
                        if($str[1]!='jpg'){
                            $creator_list[$key]['MediaType'] ='VIDEO';
                        }
                    }
                }
                /*get image or video - by gautam - ends*/
            }
        }

        //fetch user list
        $sql.= " AND PageMembers.ModuleRoleID = '9' ORDER BY PageMembers.PageMemeberID ";
        $results['TotalRecords'] = $this->db->query($sql)->num_rows()+1;
        $user_query = $sql . " LIMIT " . $offset . "," . $page_size;
        $user_list = $this->db->query($user_query)->result_array();

        foreach($user_list as $key=>$val)
        {
            /*get image or video - by gautam - starts*/
            if($this->IsApp == 1){ /*For Mobile */
            $user_list[$key]['Location'] = $this->user_model->get_user_location($val['UserID']);                
            $user_list[$key]['MediaType'] ='PHOTO';
            if($user_list[$key]['ProfilePicture']!=''){
                $str = explode(".",$user_list[$key]['ProfilePicture']);
                if($str[1]!='jpg'){
                    $user_list[$key]['MediaType'] ='VIDEO';
                }
            }
            /*privacy check - starts added by gautam*/
            /*privacy check - ends added by gautam*/
            if ($input['UserID'] != $val['UserID'])
            {
                $users_relation = get_user_relation($input['UserID'], $val['UserID']);
                $privacy_details = $this->privacy_model->details($val['UserID']);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label'])
                {
                    foreach ($privacy_details['Label'] as $privacy_label)
                    {
                        if(isset($privacy_label[$privacy]))
                        {
                            if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation))
                            {
                                $user_list[$key]['ProfilePicture'] = '';
                            }
                        }
                    }
                }
            }
            }
            /*get image or video - by gautam - ends*/
        }

        $results['Creator'] = $creator_list;
        $results['Users'] = $user_list;
        return $results;
    }

    /**
     * [check_page_permission Used to Check Permission]
     * @param  [int]    $page_guid       [Page GUID]
     * @param  [int]    $user_id         [UserID]
     */
    function check_page_permission($user_id, $page_guid)
    {

        $this->db->select('P.PageID,PM.ModuleRoleID');
        $this->db->from(PAGES . " as P");
        $this->db->join(PAGEMEMBERS . " as PM", 'P.PageID = PM.PageID', 'inner');
        $this->db->where('P.PageGUID', $page_guid);
        $this->db->where('P.StatusID', 2);
        $this->db->where('(P.UserID = ' . $user_id . " OR PM.UserID=" . $user_id . " )");
        $query = $this->db->get();
        $result = $query->row_array();
        if (!empty($result))
        {
            $RoleID = $result['ModuleRoleID'];
            if ($RoleID == '7' || $RoleID == '8')
            {
                return true;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }

    /**
     * [get_category_details  function is used to get category and its sub-category detail]
     * @param [integer]  $category_id
     */
    function get_category_details($category_id)
    {
        $this->db->select('Icon, Name, ModuleID');
        $this->db->where('CategoryID', $category_id);
        $this->db->where('StatusID', 2);
        $this->db->limit(1);
        $CategoryDetail = $this->db->get(CATEGORYMASTER);
        if ($CategoryDetail->num_rows() > 0)
        {
            $result = $CategoryDetail->row_array();
            return $result;
        } else
        {
            return false;
        }
    }

    /**
     * [delete Used to reset Votes]
     * @param [string] $page_guid    [Page GUID]    
     */
    function delete($user_id, $page_guid)
    {
        $this->db->where('PageGUID', $page_guid);
        $this->db->where('userID', $user_id);
        $this->db->update(PAGES, array('StatusID' => 3));
        return $this->db->affected_rows();
    }

    /**
     * [suggestions  used to get list of all suggested Pages ]  
     * @param [int]         $offset             [Offset]
     * @param [int]         $page_size           [Limit]
     * @param [int]         $user_id             [Logged in User ID]
     */
    function suggestions($user_id, $offset, $page_size = PAGE_SIZE)
    {
        $this->load->model('ignore_model');
        $ignoredPageList = $this->ignore_model->get_ignored_list($user_id, 'Page');

        $this->db->select('P.PageGUID,CONCAT(U.FirstName," ",U.LastName) AS CreatedBy ,P.LastActionDate,P.PageID,P.Title,P.PageURL,P.Description,CM.Name as Category,CM.CategoryID,CM.Icon as CategoryIcon,Pm.ModuleRoleID,P.CreatedDate,P.NoOfFollowers,if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture,(select Count(f.FriendID) from ' . PAGEMEMBERS . ' as Pm,Friends f where Pm.UserID  = f.FriendID and f.UserID = ' . $user_id . ' AND f.Status = 1 and Pm.PageID = P.PageID AND Pm.StatusID=2) as total', FALSE);
        $this->db->from(PAGES . ' P');
        $this->db->join(PAGEMEMBERS . ' Pm', 'P.PageID = Pm.PageID');
        $this->db->join(USERS . ' U', 'P.UserID = U.UserID', 'inner');
        $this->db->join(CATEGORYMASTER . " as CM", 'P.CategoryID = CM.CategoryID');
        $this->db->where("(exists (select FriendID as my_friends_userId from " . FRIENDS . " f where f.UserID = " . $user_id . " AND f.Status = 1 And Pm.UserID = f.FriendID AND Pm.UserID != " . $user_id . "  AND P.StatusID =2 AND P.UserID <> " . $user_id . ") OR Exists (select PageID from Pages where UserID != " . $user_id . " AND StatusID = 2 AND Pm.UserID != " . $user_id . ")) AND P.PageID not in (select PageID from " . PAGEMEMBERS . " where UserID = " . $user_id . " AND StatusID = '2') AND P.PageID not in (select EntityOwnerID from " . FOLLOW . " where UserID = " . $user_id . " and Type = 'page') AND P.StatusID=2 AND Pm.UserID != " . $user_id . "", NULL, FALSE);
        //$this->db->where('P.IsVerified', 1);
        if ($ignoredPageList)
        {
            $this->db->where_not_in('P.PageID', $ignoredPageList);
        }
        $this->db->group_by('P.PageID');
        $this->db->order_by(" total desc,P.NoOfFollowers desc ", NULL, FALSE);

        /*Count All Records bu Gautam*/ 
        if($this->IsApp == 1){ // Mobile Api Change
         // Mobile Api Change
            $tempdb = clone $this->db;
            $temp_q = $tempdb->get();
            $returnArr['total_records'] = $temp_q->num_rows(); 
        }

        $this->db->limit($page_size, $offset);

        $res = $this->db->get();
        $data = array();
        if ($res->num_rows())
        {
            foreach ($res->result_array() as $result)
            {
                if(strlen($result['Description'])>50)
                {
                    $result['Description'] = substr($result['Description'],0,50).'...';
                }
                $result['Friends'] = $this->get_friends_in_page($user_id, $result['PageGUID']);
                /*edited by gutam - removed full path*/
                if($this->IsApp == 1){ // Mobile Api Change
                    $result['PageIcon'] = $result['ProfilePicture'];
                    $result['ProfilePicture'] = $result['ProfilePicture'];
                }
                $data[] = $result;
            }
        }
        $returnArr['data'] = $data; 
        return $returnArr;
    }

    function get_friends_in_page($user_id, $page_guid,$limit=0)
    {
        $this->db->select("PU.Url as ProfileURL,U.FirstName,U.LastName,U.ProfilePicture,U.UserGUID");
        $this->db->from(USERS . ' U');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID', 'left');
        $this->db->join(PAGEMEMBERS . ' PM', 'PM.UserID=U.UserID', 'left');
        $this->db->join(PAGES . ' P', 'P.PageID=PM.PageID', 'left');
        $this->db->join(FRIENDS . ' F', 'F.FriendID=U.UserID', 'left');
        $this->db->where('F.UserID', $user_id);
        $this->db->where('F.Status', '1');
        $this->db->where('PU.EntityType', 'User');
        $this->db->where('PM.StatusID', '2');
        $this->db->where('P.PageGUID', $page_guid);
        if($limit)
        {
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->result_array();
        }
        return array();
    }

    /**
     * [CheckUserFollowStatus  used to check user follow page or not and written 1 or 0 respectively ]  
     * @param [int]         $page_guid           [PageGUID]
     * @param [int]         $user_login_id        [Logged in User ID]
     */
    function check_user_follow_page_status($page_guid, $user_login_id)
    {
        $this->db->select('F.FollowID');
        $this->db->where('F.UserID', $user_login_id);
        $this->db->where('F.Type', 'page');
        $this->db->where('P.PageGUID', $page_guid);
        $this->db->from(FOLLOW . ' F');
        $this->db->join(PAGES . ' P', 'P.PageID = F.EntityOwnerID');
        $sql = $this->db->get();

        if ($sql->num_rows() > 0)
        {
            return 1;
        } else
        {
            return 0;
        }
    }

    /**
     * [check_user_liked_page_status   used to check user like page or not and written 1 or 0 respectively  ]   
     * @param [int]         $page_guid           [PageGUID]
     * @param [int]         $user_login_id        [Logged in User ID]
     */
    function check_user_liked_page_status($page_guid, $user_login_id)
    {
        $this->db->select('PL.PostLikeID');
        $this->db->where('PL.UserID', $user_login_id);
        $this->db->where('PL.EntityType', 'PAGE');
        $this->db->where('P.PageGUID', $page_guid);
        $this->db->where('PL.StatusID', 2);
        $this->db->from(POSTLIKE . ' PL');
        $this->db->join(PAGES . ' P', 'P.PageID = PL.EntityID');
        $sql = $this->db->get();

        if ($sql->num_rows() > 0)
        {
            return 1;
        } else
        {
            return 0;
        }
    }

    /**
     * [get_user_page_permission   used to check user has premission to do action on page or not and written 1 or 0 respectively  ]    
     * @param [int]         $page_guid           [PageGUID]
     * @param [int]         $user_login_id        [Logged in User ID]
     */
    function get_user_page_permission($page_guid, $user_login_id)
    {
        $this->db->select('PM.ModuleRoleID');
        $this->db->where('PM.UserID', $user_login_id);
        $this->db->where('P.PageGUID', $page_guid);
        $this->db->where('PM.StatusID', 2);
        $this->db->from(PAGEMEMBERS . ' PM');
        $this->db->join(PAGES . ' P', 'P.PageID = PM.PageID');
        $sql = $this->db->get();

        if ($sql->num_rows() > 0)
        {
            $row = $sql->row_array();
            $role_status_id = $row['ModuleRoleID'];
            if ($role_status_id == '7' || $role_status_id == '8')
            {
                return 1;
            } else
            {
                return 0;
            }
        } else
        {
            return 0;
        }
    }

    /**
     * [user_page_permission   used to check user has premission to do action on page or not and written 1 or 0 respectively  ]    
     * @param [int]         $page_guid           [PageGUID]
     * @param [int]         $user_login_id        [Logged in User ID]
     */
    function user_page_permission($page_guid, $user_login_id, $ModuleRoleID)
    {
        $this->db->select('PM.ModuleRoleID');
        $this->db->where('PM.UserID', $user_login_id);
        $this->db->where('P.PageGUID', $page_guid);
        $this->db->where('PM.StatusID', 2);
        $this->db->where('PM.ModuleRoleID', $ModuleRoleID);
        $this->db->from(PAGEMEMBERS . ' PM');
        $this->db->join(PAGES . ' P', 'P.PageID = PM.PageID');
        $sql = $this->db->get();

        if ($sql->num_rows() > 0)
        {
            return 1;
        } else
        {
            return 0;
        }
    }

    /**
     * [insert_page_member Used to insert page member]
     * @param  $page_id, $user_id     [PageID, UserID]        
     * When User Follow and Like page both then we insert user entry into pagemember table
     */
    function insert_page_member($page_id, $user_id)
    {
        $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $page_member_id = '';
        $module_role_id = '';  // 7 - Creator, 8 - Admin, 9 - User
        // check UserID entry is already exist into PageMember Table wrt PageID
        $this->db->select('PageMemeberID, ModuleRoleID');
        $this->db->where('PageID', $page_id);
        $this->db->where('UserID', $user_id);
        $query = $this->db->get(PAGEMEMBERS);

        if ($query->num_rows() > 0)
        {
            $row = $query->row_array();
            $page_member_id = $row['PageMemeberID'];
            $module_role_id = $row['ModuleRoleID'];
        }

        // check user follow and Like page
        $this->db->select('FollowID,PostLikeID');
        $this->db->from(FOLLOW . ' as F');
        $this->db->join(POSTLIKE . ' as P', 'P.EntityID = F.TypeEntityID  AND F.UserID = P.UserID AND LOWER(P.EntityType) = F.Type', 'LEFT');
        $this->db->where('F.Type', 'page');
        $this->db->where('F.TypeEntityID', $page_id);
        $this->db->where('F.UserID', $user_id);
        /*      $this->db->where('P.EntityType', 'PAGE');
          $this->db->where('P.StatusID', 2); */
        $this->db->limit(1);
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if ($query->num_rows() > 0)
        {
            if ($page_member_id == '')
            {
                //get ModuleRoleID - starts
                $this->db->select('ModuleRoleID');
                $this->db->where('ModuleID', $this->module_id);
                $this->db->where('RoleName', 'User');
                $checkquery = $this->db->get(MODULEROLES);
                $result = $checkquery->row();
                //get ModuleRoleID - ends
                // insert new entry in PAGEMEMBERS table
                $this->db->insert(PAGEMEMBERS, array('PageID' => $page_id, 'UserID' => $user_id, 'ModuleRoleID' => $result->ModuleRoleID, 'StatusID' => 2));
            } else
            {
                if ($module_role_id != '7')
                {
                    // update statusID=2 of existing record wrt PageMemeberID
                    $this->db->where('PageMemeberID', $page_member_id)
                            ->update(PAGEMEMBERS, array('StatusID' => 2, 'ModuleRoleID' => 9, 'JoinedAt' => $created_date));
                }
            }
        } else
        {
            if ($module_role_id != '7')
            {
                // update statusID=3 of existing record wrt PageMemeberID
                $this->db->where('PageMemeberID', $page_member_id)
                        ->update(PAGEMEMBERS, array('StatusID' => 3, 'ModuleRoleID' => 9, 'JoinedAt' => $created_date));
            }
        }
    }

    /**
     * [check_blocked_user  to check if user is blocked]  
     * @param [int]         $user_id             
     * @param [int]         $module_id           [ModuleID]
     * @param [String]      $module_entity_id     [ModuleEntityID]
     */
    function check_blocked_user($user_id, $module_id, $module_entity_id)
    {
        $this->db->select('StatusID');
        $this->db->where('ModuleID', $module_id);
        $this->db->where('UserID', $user_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $sql = $this->db->get(BLOCKUSER);

        if ($sql->num_rows() > 0)
        {
            return true;
        } else
        {
            return false;
        }
    }

    function get_all_admins($page_id)
    {
        $data = array();
        $this->db->select('UserID');
        $this->db->from(PAGEMEMBERS);
        $this->db->where('PageID', $page_id);
        $this->db->where_in('ModuleRoleID', array(7, 8));
        $query = $this->db->get();
        if ($query->num_rows())
        {
            foreach ($query->result_array() as $result)
            {
                $data[] = $result['UserID'];
            }
        }
        return $data;
    }

    /**
     * [check_page_owner to check user is admin/owner of a paticular page]   
     * @param [int]         $user_id             
     * @param [int]         $module_entity_id     [Page ID]
     */
    function check_page_owner($user_id, $module_entity_id)
    {
        $this->db->select('UserID');
        $this->db->where('PageID', $module_entity_id);
        $this->db->where('UserID', $user_id);
        $this->db->where('StatusID', 2);
        $this->db->where('(ModuleRoleID = "7" OR ModuleRoleID = "8") ');
        $sql = $this->db->get(PAGEMEMBERS);
        //echo $this->db->last_query();

        if ($sql->num_rows() > 0)
        {
            return true;
        } else
        {
            return false;
        }
    }

    /**
     * [check_page_creator to check user is createor of a page]    
     * @param [int]      $user_id        
     * @param [int]      $module_entity_id   [page ID]
     */
    function check_page_creator($user_id, $module_entity_id)
    {
        $this->db->select('UserID');
        $this->db->where('PageID', $module_entity_id);
        $this->db->where('UserID', $user_id);
        $this->db->where('StatusID', 2);
        $this->db->where('ModuleRoleID', 7);
        $sql = $this->db->get(PAGEMEMBERS);
        if ($sql->num_rows() > 0)
        {
            return true;
        } else
        {
            return false;
        }
    }

    /**
     * [get_page_creator_id to get page creator id]    
     * @param [int]      $module_entity_id   [page ID]
     */
    function get_page_creator_id($module_entity_id)
    {
        $this->db->select('UserID');
        $this->db->where('PageID', $module_entity_id);
        $this->db->where('StatusID', 2);
        $this->db->where('ModuleRoleID', 7);
        $sql = $this->db->get(PAGEMEMBERS);
        if ($sql->num_rows() > 0)
        {
            $user = $sql->row_array();
            return $user['UserID'];
        } else
        {
            return '';
        }
    }

    function get_page_members_id($page_id)
    {
        $data = array();
        $this->db->select('UserID');
        $this->db->from(PAGEMEMBERS);
        $this->db->where('PageID', $page_id);
        $this->db->where('StatusID', '2');
        $query = $this->db->get();
        if ($query->num_rows())
        {
            foreach ($query->result() as $page_member)
            {
                $data[] = $page_member->UserID;
            }
        }
        return $data;
    }

    /**
     * [check_member Used to Check member already exist in a page or not]
     * @param  [int]    $page_id     [Page Id]
     * @param  [int]    $member_id         [User ID]
     * @return [bool]   [true/False]        
     */
    function check_member($page_id, $member_id)
    {
        $this->db->where('PageID', $page_id);
        $this->db->where('UserID', $member_id);
        $this->db->where('StatusID', '2');
        $result = $this->db->get(PAGEMEMBERS);
        if ($result->num_rows() > 0)
        {
            return true;
        } else
        {
            return false;
        }
    }

    /**
     * [toggle_can_post_on_wall to change user's page wall post permission]
     * @param  [int]    $page_id       [Page Id]
     * @param  [int]    $user_id        [User Id]
     * @param [int]     $can_post_on_wall       [0/1]       
     */
    function toggle_can_post_on_wall($page_id, $user_id, $can_post_on_wall)
    {
        $this->db->where('PageID', $page_id);
        $this->db->where('UserID', $user_id);
        $this->db->update(PAGEMEMBERS, array('CanPostOnWall' => $can_post_on_wall));
    }

    /**
     * [get_user_page_role Get user's role in particular page]
     * @param  [int] $user_id  [user id]
     * @param  [int] $page_id [page id]
     * @return [type]          [description]
     */
    function get_user_page_role($user_id, $page_id)
    {
        $this->db->select('ModuleRoleID');
        $this->db->where('PageID', $page_id);
        $this->db->where('UserID', $user_id);
        $sql = $this->db->get(PAGEMEMBERS);
        if ($sql->num_rows() > 0)
        {
            return $sql->row()->ModuleRoleID;
        } else
        {
            return false;
        }
    }

    /**
     * [toggle_user_role ,Owner/Creator/Admin of page can assign/remove user as a admin]
     * @param  [int]        $data           [page details]
     */
    function toggle_user_role($data, $user_id = 0)
    {
        if (!empty($data))
        {
            $update_data = array('ModuleRoleID' => $data['RoleID']);
            if ($data['RoleID'] == 8)
            {
                $update_data['CanPostOnWall'] = 1;
            }
            $this->db->where('PageID', $data['PageID']);
            $this->db->where('UserID', $data['UserID']);
            $this->db->update(PAGEMEMBERS, $update_data);
            $result['Message'] = lang('page_role_changed');
            if ($data['RoleID'] == 8)
            {
                $parameters = array();
                $parameters[0]['ReferenceID'] = $data['PageID'];
                $parameters[0]['Type'] = 'Page';
                $this->notification_model->add_notification(68, $user_id, array($data['UserID']), $data['PageID'], $parameters);
            }
        }
        return $result;
    }

    /**
     * [unlike_page To mark Unlike an entity]
     * @param  [array] $data [input data for toggleLike Request]
     * @return [array]       [array of response code and message]
     */
    function unlike_page($data)
    {
        $return['Message'] = lang('success');
        $return['ResponseCode'] = 200;
        $user_id = $data['UserID'];
        $entity_type = strtoupper($data['EntityType']);
        $entity_guid = $data['EntityGUID'];
        $device_type_id = $data['DeviceTypeID'];
        $status_id = 2;
        $count = 1;
        $module_id = 3;
        $module_entity_id = "";
        $parent_entity_id = "";
        /* get EntityId by Entity GuID */
        switch ($entity_type)
        {
            case 'PAGE':
                $entity_id = get_detail_by_guid($entity_guid, $this->module_id);
                $module_id = $this->module_id;
                $module_entity_id = $entity_id;
                break;
            default:
                $return['ResponseCode'] = 412;
                $return['Message'] = sprintf(lang('valid_value'), "Entity Type");
                return $return;
                break;
        }
        if (empty($entity_id))
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = sprintf(lang('valid_value'), "entity GUID");
            return $return;
        }
        /* End get EntityId */
        $created_date = get_current_date('%Y-%m-%d %H:%i:%s');

        $this->db->select('PostLikeID, StatusID');
        $this->db->where('EntityID', $entity_id);
        $this->db->where('EntityType', $entity_type);
        $this->db->where('UserID', $user_id);
        $this->db->limit(1);
        $query = $this->db->get(POSTLIKE);

        $this->load->model('follow/follow_model');
        if ($query->num_rows() > 0)
        {
            $row = $query->row_array();
            if ($row['StatusID'] == 2)
            {
                $status_id = 3;
                $count = -1;
            }

            if ($entity_type == 'PAGE')
            {
                // check user already follow that page or not
                $this->db->select('FollowID');
                $this->db->where('TypeEntityID', $entity_id);
                $this->db->where('UserID', $user_id);
                $this->db->where('Type', 'page');
                $this->db->limit(1);
                $query_new = $this->db->get(FOLLOW);
                if ($query_new->num_rows() > 0 && $status_id = 3)
                {
                    $data = array('TypeEntityID' => $entity_id, 'UserID' => $user_id, 'Type' => 'page');
                    $this->follow_model->follow($data);
                }
                if ($query_new->num_rows() == 0 && $status_id = 2)
                {
                    $data = array('TypeEntityID' => $entity_id, 'UserID' => $user_id, 'Type' => 'page');
                    $this->follow_model->follow($data);
                }
            }
            $this->db->where('PostLikeID', $row['PostLikeID'])
                    ->update(POSTLIKE, array('StatusID' => $status_id, 'ModifiedDate' => $created_date, 'DeviceTypeID' => $device_type_id));
        } else
        {
            // follow page when user liked page first time
            if ($entity_type == 'PAGE')
            {
                $data = array('TypeEntityID' => $entity_id, 'UserID' => $user_id, 'Type' => 'page');
                $this->follow_model->follow($data);
            }
            $input = array('UserID' => $user_id, 'EntityID' => $entity_id, 'EntityType' => $entity_type, 'CreatedDate' => $created_date, 'ModifiedDate' => $created_date, 'DeviceTypeID' => $device_type_id);
            $this->db->insert(POSTLIKE, $input);
        }

        // call insert_page_member function to insert follow page userid 
        if ($entity_type == 'PAGE')
        {
            $this->insert_page_member($entity_id, $user_id);
        }
        $this->load->model('activity/activity_model');
        $this->activity_model->update_like_count($entity_id, $entity_type, $count);
        return $return;
    }

    function is_admin($user_id, $page_id)
    {
        $this->db->select('PageMemeberID');
        $this->db->from(PAGEMEMBERS);
        $this->db->where('PageID', $page_id);
        $this->db->where('UserID', $user_id);
        $this->db->where_in('ModuleRoleID', array(7, 8));
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return true;
        }
        return false;
    }

    function get_page_owner($page_guid)
    {
        $this->db->select('UserID');
        $this->db->where('PageGUID', $page_guid);
        //$this->db->where('ModuleRoleID', 4);
        //$this->db->where('StatusID', 2);
        $sql = $this->db->get(PAGES);

        if ($sql->num_rows() > 0)
        {
            $res = $sql->row();
            return $res->UserID;
        } else
        {
            return false;
        }
    }

    function get_page_post_permission($user_id, $page_guid)
    {
        $this->db->select('PM.PageMemeberID');
        $this->db->where('PM.UserID', $user_id);
        $this->db->where('P.PageGUID', $page_guid);
        $this->db->where('PM.StatusID', 2);
        $this->db->where('PM.CanPostOnWall', 1);
        $this->db->from(PAGEMEMBERS . ' PM');
        $this->db->join(PAGES . ' P', 'P.PageID = PM.PageID');
        $sql = $this->db->get();
        //echo $this->db->last_query();
        if ($sql->num_rows() > 0)
        {
            return 1;
        } else
        {
            return 0;
        }
    }

    function get_liked_pages_list($user_id, $array = false)
    {
        $arr = array(0);
        if($this->settings_model->isDisabled(18)) { // Return empty if page module is disabled
            return  ($array) ? $arr : '0';
        }
        
        
        $this->db->select('P.PageID');
        $this->db->from(PAGES . ' P');
        $this->db->join(PAGEMEMBERS . ' PM', 'PM.PageID=P.PageID', 'left');
        $this->db->where('PM.UserID', $user_id);
        $this->db->where('P.StatusID', '2');
        $this->db->where('PM.StatusID', '2');
        $pages = $this->db->get();
        if ($pages->num_rows())
        {
            foreach ($pages->result() as $page)
            {
                $arr[] = $page->PageID;
            }
        }
         $arr=array_filter($arr);
        if ($array)
        {            
            return $arr;
        } else
        {
            return implode(',', $arr);
        }
    }

    /**
     * [get_page_members_details description]
     * @param  [int]  $page_id     [Page ID]
     * @param  [int]  $page_no      [Page Number]
     * @param  [int]  $page_size    [Page Size]
     * @param  boolean $count_flag   [Count only flag]
     * @param  array   $only_friends [Return only firends]
     * @return [array]                [Page Member details]
     */
    function get_page_members_details($page_id, $page_no, $page_size, $count_flag = FALSE, $only_friends = array())
    {
        $this->db->select('U.FirstName, U.LastName, U.ProfilePicture, P.Url as ProfileURL');
        $this->db->from(PAGEMEMBERS . " AS PM");
        $this->db->join(USERS . ' U', 'U.UserID=PM.UserID', 'left');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID AND P.EntityType="User"', 'left');
        $this->db->where("PM.PageID", $page_id);
        $this->db->where("PM.StatusID", 2);

        if ($only_friends)
        {
            $this->db->where_in('PM.UserID', $only_friends);
        }

        if ($count_flag) // check if array needed
        {
            return $this->db->get()->num_rows();
        }

        if (!empty($page_size))
        {
            $Offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $Offset);
        }
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->result_array();
        }
    }


    function get_page_media($PageID, $Limit){
        
        $PageID = $this->db->escape_str($PageID);
        
        $query = $this->db->query("SELECT M.MediaGUID,M.ImageName FROM `Pages` P, `Albums` A, `Media` M WHERE M.AlbumID=A.`AlbumID` AND A.ModuleID=18 AND A.`ModuleEntityID`=P.`PageID` AND A.`AlbumName`='Wall Media' AND P.PageID=$PageID LIMIT $Limit");
        if($query->num_rows()>0){
            return $query->result_array();
        }else{
            return array();
        }
    }
    public function get_top_user_pages($user_id,$search,$page_no,$page_size,$uid=0)
    {
        /*if(CACHE_ENABLE) {
            $cache_data=$this->cache->get('top_page_'.$user_id);
            if(!empty($cache_data)) {
                return $cache_data;
            }
        }*/
        $data = array();
        //P.Description,P.CategoryID as Type, P.CreatedDate as CreateDate, P.NoOfViews as ViewCount, P.LastActionDate as LastActivity, P.PostalCode, P.IsVerified, P.WebsiteURL,P.NoOfLikes,P.NoOfFollowers, , CM.Name as Category, CM.Icon as CategoryIcon
        $this->db->select('P.PageID, P.PageGUID, P.Title, P.PageURL, if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture, 1 as FollowStatus', false);

        //$this->db->select('U.UserGUID, U.FirstName as CreatedByFirstName, U.LastName as CreatedByLastName, PL.Url as CreatedByURL',false);
        
        $this->db->from(PAGEMEMBERS . " as PM");
        $this->db->join(PAGES . " as P", 'P.PageID = PM.PageID and P.StatusID != 3');
        $this->db->join(USERS.' U','U.UserID=PM.UserID AND U.StatusID NOT IN (3,4)');
       // $this->db->join(PROFILEURL . " as PL", 'PL.EntityID=U.UserID AND PL.EntityType="User"', 'left');                
        $this->db->join(CATEGORYMASTER . " as CM", 'P.CategoryID = CM.CategoryID');

        if ($search != "") {
            $this->db->like('P.Title', $search);
        }

        $this->db->where('PM.UserID',$user_id);
        $this->db->where('PM.StatusID','2');

        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $query = $this->db->get();
        if($query->num_rows()) {
            $result = $query->result_array();
            if(!empty($uid) && $user_id != $uid) {
                foreach($result as $d) {
                    $d['FollowStatus'] = 0;
                    if($this->check_user_follow_page_status($d['PageGUID'], $uid)) {
                        $d['FollowStatus'] = 1;
                    }
                    $data[] = $d;
                }
            } else {
                $data = $result;    
            } 
        }
        if(CACHE_ENABLE && !empty($data)) {
            $this->cache->save('top_page_'.$user_id, $data,600);
        }
        return $data;
    }


    /**
     * [get_page_userlist_for_answer Used to get follower users list]
     * @param  [array]  $input    [UserLoginID, PageGUID]
     */
    function get_page_userlist_for_answer($PageID , $ModuleType = array(), $StatusID = '')
    {
        $results = array();       
        $this->db->select('GROUP_CONCAT(PageMembers.UserID) as PageMembers');
        $this->db->from(PAGES);
        $this->db->join(PAGEMEMBERS, 'Pages.PageID = PageMembers.PageID', 'left');
        $this->db->where("PageMembers.PageID", $PageID);
        if (!empty($ModuleType))
        {
            $this->db->where_in('PageMembers.ModuleRoleID', $ModuleType);
        }
        $this->db->where("PageMembers.StatusID", '2');
        $sql = $this->db->get();
        if($sql->num_rows())
        {
            $data = $sql->row_array();
            $results = $data['PageMembers'];
        }
        return $results;
    }


    function page_cache($page_id)
    {
        if(CACHE_ENABLE)
        {
            $this->cache->delete('page_'.$page_id);
        }
        $this->db->select('P.PageGUID,P.UserID,P.Title,P.Description,P.CategoryID ,P.LastActionDate as LastActivity,P.PostalCode,P.IsVerified,P.PageURL,P.WebsiteURL,P.NoOfFollowers,C.Name as City,S.Name as State,CNM.CountryName as Country,CM.Icon as LogoImage,CM.Name as Category,GROUP_CONCAT(EC.CategoryID) as SubCategoryID,P.Phone,if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture,P.CoverPicture as ProfileCovery', false);
        $this->db->from(PAGES.' as P');
        $this->db->join(CATEGORYMASTER . " as CM", 'P.CategoryID = CM.CategoryID');
        $this->db->join(ENTITYCATEGORY . " as EC", 'EC.ModuleEntityID = P.PageID and EC.ModuleID = 18');
        $this->db->join(CITIES . " as C", 'C.CityID = P.CityID', 'left');
        $this->db->join(STATES . " as S", 'S.StateID = P.StateID', 'left');
        $this->db->join(COUNTRYMASTER . " as CNM", 'CNM.CountryID = P.CountryID', 'left');
        $this->db->where('EC.ModuleID', 18);
        $this->db->where('P.PageID',$page_id );
        $result = $this->db->get();
        $result = $result->row_array();
        
        if(CACHE_ENABLE && !empty($result))
        {
            $this->cache->save('page_'.$page_id, $result,CACHE_EXPIRATION);
        }
        
    }

    function calculate_page_popularity($page_id)
    {
        $popularity = 'low';

        $this->db->select('COUNT(ID) as ActivityCount',false);
        $this->db->from(USERSACTIVITYLOG);
        $this->db->where('ModuleID','18');
        $this->db->where('ModuleEntityID',$page_id);
        $this->db->where("ActivityDate BETWEEN '".get_current_date('%Y-%m-%d', 7)."' AND '".get_current_date('%Y-%m-%d')."'",null,false);
        $query = $this->db->get();
        if($query->num_rows())
        {
            $activity_count = $query->row()->ActivityCount;
            if($activity_count >= PAGE_POPULARITY_ACTIVITY){
                $popularity = 'High';
            }
            else
            {
                $popularity = 'Low';
            }
        }
        else
        {
            $popularity = 'Low';
        }

        $this->db->set('Popularity',$popularity);
        $this->db->where('PageID',$page_id);
        $this->db->update(PAGES);
    }
}
