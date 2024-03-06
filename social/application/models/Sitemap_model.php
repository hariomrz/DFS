<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * A class for generating XML sitemaps and sitemap indexes with the CodeIgniter PHP Framework
 * More information about sitemaps: http://www.sitemaps.org/protocol.html
 *
 */
class Sitemap_model extends Common_Model {
	
	/**
	 * Prepare the class variables for storing items and checking valid changefreq values
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		$this->urls = array();
		$this->changefreqs = array(
			'always',
			'hourly',
			'daily',
			'weekly',
			'monthly',
			'yearly',
			'never'
		);
	}
	
	/**
	 * Add an item to the array of items for which the sitemap will be generated
	 * 
	 * @param string $loc URL of the page. This URL must begin with the protocol (such as http) and end with a trailing slash, if your web server requires it. This value must be less than 2,048 characters.
	 * @param string $lastmod The date of last modification of the file. This date should be in W3C Datetime format. This format allows you to omit the time portion, if desired, and use YYYY-MM-DD.
	 * @param string $changefreq How frequently the page is likely to change. This value provides general information to search engines and may not correlate exactly to how often they crawl the page.
	 * @param number $priority The priority of this URL relative to other URLs on your site. Valid values range from 0.0 to 1.0. This value does not affect how your pages are compared to pages on other sites�it only lets the search engines know which pages you deem most important for the crawlers.
	 * @access public
	 * @return boolean
	 */
	public function add($loc, $lastmod = NULL, $changefreq = NULL, $priority = NULL) {
		// Do not continue if the changefreq value is not a valid value
		if ($changefreq !== NULL && !in_array($changefreq, $this->changefreqs)) {
			show_error('Unknown value for changefreq: '.$changefreq);
			return false;
		}
		// Do not continue if the priority value is not a valid number between 0 and 1 
		if ($priority !== NULL && ($priority < 0 || $priority > 1)) {
			show_error('Invalid value for priority: '.$priority);
			return false;
		}
		$item = new stdClass();
		$item->loc = $loc;
		$item->lastmod = $lastmod;
		$item->changefreq = $changefreq;
		$item->priority = $priority;
		$this->urls[] = $item;
		
		return true;
	}
	
	/**
	 * Generate the sitemap file and replace any output with the valid XML of the sitemap
	 * 
	 * @param string $type Type of sitemap to be generated. Use 'urlset' for a normal sitemap. Use 'sitemapindex' for a sitemap index file.
	 * @access public
	 * @return void
	 */
	public function output($type = 'urlset') {
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><'.$type.'/>');
		$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		if ($type == 'urlset') {
			foreach ($this->urls as $url) {
				$child = $xml->addChild('url');
				$child->addChild('loc', strtolower($url->loc));
				if (isset($url->lastmod)) $child->addChild('lastmod', $url->lastmod);
				if (isset($url->changefreq)) $child->addChild('changefreq', $url->changefreq);
				if (isset($url->priority)) $child->addChild('priority', number_format($url->priority, 1));
			}
		} elseif ($type == 'sitemapindex') {
			foreach ($this->urls as $url) {
				$child = $xml->addChild('sitemap');
				$child->addChild('loc', strtolower($url->loc));
				if (isset($url->lastmod)) $child->addChild('lastmod', $url->lastmod);
			}
		}
		$this->output->set_content_type('application/xml')->set_output($xml->asXml());
	}
	
	/**
	 * Clear all items in the sitemap to be generated
	 * 
	 * @access public
	 * @return boolean
	 */
	public function clear() {
		$this->urls = array();
		return true;
	}
        
        public function activity_url($post_type=array(4), $user_id=0) {
            
            $this->load->model(array('group/group_model','forum/forum_model'));
            
            $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 23, 24, 25, 26);
                        
            $group_list = $this->group_model->get_visible_groups($user_id);            
            $group_list[] = 0;
            $group_list = implode(',', $group_list);            
            $category_list = $this->forum_model->get_visible_categories($user_id);
            $category_list[] = 0;
            
            $event_list = $this->event_model->get_all_joined_events($user_id);
                    
            $select_array=array();
            $select_array[]='A.ActivityGUID, A.ActivityID, A.ModuleID, A.ModuleEntityID, A.PostType, A.PostTitle, A.PostContent, ATY.ActivityTypeID';
            
            $case_array=array();
            if ($group_list) {            
                $case_array[]=" A.ActivityTypeID IN (4,7) 
                                    OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                    THEN 
                                        A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
            }
            if($category_list) {
                $case_array[] = " A.ActivityTypeID=26 
                                    THEN 
                                    A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                                ";
            }
            if (!empty($event_list)) {
                $case_array[] = " A.ActivityTypeID IN (11,23,14) OR (A.ActivityTypeID=24 AND A.ModuleID=14)
                                    THEN 
                                    A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")
                               ";
            }
            if(!empty($case_array)) {
                $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
            }
            if (empty($condition)) {
                $condition = "A.ModuleEntityID=" . $user_id;
            }
            
            $condition .= " AND ((CASE WHEN (A.Privacy=3) THEN A.UserID IN (0)";
            $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END))";
            
            // Check parent activity privacy for shared activity
            $privacy_condition = "
            IF(A.ActivityTypeID IN(9,10,14,15),
                (
                    CASE
                        WHEN A.ActivityTypeID IN(9,10) 
                            THEN
                                A.ParentActivityID=(
                                SELECT ActivityID FROM " . ACTIVITY . " WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                                (IF(Privacy=1 AND ActivityTypeID!=7,true,false) OR
                                IF(Privacy=2 AND ActivityTypeID!=7,UserID IN (0),false) OR
                                IF(Privacy=3 AND ActivityTypeID!=7,UserID IN (0),false) OR
                                IF(Privacy=4 AND ActivityTypeID!=7,UserID='" . $user_id . "',false) OR
                                IF(ActivityTypeID=7,ModuleID=1 AND ModuleEntityID IN(" . $group_list . "),false))
                                )
                        WHEN A.ActivityTypeID IN(14,15)
                            THEN
                                A.ParentActivityID=(
                                SELECT MediaID FROM " . MEDIA . " WHERE StatusID=2 AND A.ParentActivityID=MediaID
                                )
                    ELSE
                    '' 
                    END 

                ),         
            true)";
            
            
            
            $this->db->from(ACTIVITY . ' A');
            $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
            $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
            
            
            $this->db->where_in('A.PostType',$post_type);
            
            $this->db->where_in('A.ModuleID', $modules_allowed);
            $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2,10),A.StatusID=2)", null, false);
            $this->db->where('ATY.StatusID', '2');
            
            $this->db->where($condition, NULL, FALSE);
            $this->db->where($privacy_condition, null, false);
            
            $this->db->order_by('A.ModifiedDate', 'DESC');
            
            $this->db->select(implode(',', $select_array),false);
            $this->db->group_by('A.ActivityID');
            $query = $this->db->get();
            if($query->num_rows()) {
                $feed_result = $query->result_array();
                //echo $this->db->last_query(); die;
                foreach ($feed_result as $res) {
                    $activity_url = get_single_post_url($res);
                    $this->add(base_url($activity_url), NULL, 'daily', 0.8);
                }
            }
        }
        
        public function event_url($user_id=0) {
            $current_user_group_ids = array();
            if(!empty($user_id)){
                $this->load->model('group/group_model');
                $current_user_group_ids = $this->group_model->get_users_groups($user_id);            
            }
            $current_user_group_ids[] = 0;
        
            $this->db->select('E.EventGUID, E.Title', false);
            $this->db->from(EVENTS . ' AS E');        
            $this->db->join(USERS . ' AS U', 'U.UserID=E.CreatedBy');
            $this->db->join(EVENTUSERS . ' AS EU', 'EU.EventID=E.EventID','LEFT');
            $this->db->join(GROUPS . ' AS G', 'G.GroupID=E.ModuleEntityID AND E.ModuleID = 1','left');
            
            $this->db->where('E.IsDeleted', '0');
            $this->db->where('E.Privacy', 'PUBLIC');
            $this->db->where("IF((G.IsPublic=0 || G.IsPublic=2),G.GroupID IN(" . implode(',', $current_user_group_ids) . "),TRUE)",null,false);
            
            $this->db->group_by('E.EventID');
            $this->db->order_by('E.LastActivity', 'DESC');
            $query = $this->db->get();
            if($query->num_rows()) {
                $this->load->model('events/event_model');
                $feed_result = $query->result_array();
                //echo $this->db->last_query(); die;
                foreach ($feed_result as $res) {
                    $event_url = $this->event_model->getViewEventUrl($res['EventGUID'], $res['Title']);
                    $this->add(base_url($event_url), NULL, 'daily', 0.8);
                }
            }
        }
        
        function community_url($user_id=0) {
        
            $this->db->select('F.ForumID,F.ForumGUID, IFNULL(F.URL,"") as URL', FALSE);
            $this->db->from(FORUM . ' F');
            $this->db->join(USERS . ' U', 'F.CreatedBy = U.UserID');
            $this->db->where('F.StatusID', 2);
            $this->db->where('F.Visible', 1);
            $this->db->order_by('DisplayOrder', 'ASC');
        
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $this->load->model('forum/forum_model');
                $feed_result = $query->result_array();
                $permission = array();
                // Set default permissions
                $permission['IsCreator'] = FALSE;
                $permission['IsSuperAdmin'] = FALSE;
                $permission['IsAdmin'] = FALSE;
                $permission['IsMember'] = FALSE;
                foreach ($feed_result as $row) {
                    $community_url = $row['URL'];
                    if($community_url) {
                        $community_url = 'community/'.$community_url;
                        $this->add(base_url($community_url), NULL, 'daily', 0.8);
                    
                        $all_categories = $this->forum_model->get_forum_category($row['ForumID'], $user_id, $permission, 0, TRUE);
                        if (!empty($all_categories)) {
                            foreach ($all_categories as $category) {
                                $cat_url = $community_url.'/'.$category['URL']; 
                                $this->add(base_url($cat_url), NULL, 'daily', 0.8); 
                                $sub_categories = $category['SubCategory'];                                
                                foreach ($sub_categories as $sub_category) {
                                    $sub_cat_url = $cat_url.'/'.$sub_category['URL']; 
                                    $this->add(base_url($sub_cat_url), NULL, 'daily', 0.8);                                     
                                }
                            }
                        }
                    }
                }
            }
        }
        
        function group_url($user_id=0) {
        
            $condition = array('C.ModuleID' => 1); // If only root category needed
            $condition['C.ParentID'] = 0; // If specific level of category needed

            $this->db->select('C.CategoryID,C.ParentID');
            $this->db->select('C.Name', FALSE);            
            $this->db->from(CATEGORYMASTER . "  C");
            $this->db->where($condition);
            $this->db->where('C.StatusID', 2);
            $this->db->order_by('C.Name', 'ASC'); 
            
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $feed_result = $query->result_array();
                $this->load->model('group/group_model');
                foreach ($feed_result as $row) {
                    $category_name = $row['Name'];
                    $category_id = $row['CategoryID'];
                    $sub_category = array();
                    $sub_category[] = $category_id;
                    $group_count = $this->group_model->category_group($user_id, TRUE, '', '', $sub_category);

                    if ($group_count > 0) {                    
                        $category_name = preg_replace('/ /', '', $category_name);
                        //$category_name = $category_name.replace(/ /g,'');                    
                        $category_name = strtolower($category_name);

                        if($category_name) {
                            $category_url = 'group/discover/'.$category_name.'/'.$category_id;
                            $this->add(base_url($category_url), NULL, 'daily', 0.8);

                           $all_groups = $this->group_model->category_group($user_id, FALSE, '', '', $sub_category, -1, TRUE, '', '', TRUE, TRUE);
                           
                            if (!empty($all_groups)) {
                                foreach ($all_groups as $group) {
                                    $group_url = 'group/'.$group['ProfileURL']; 
                                    $this->add(base_url($group_url), NULL, 'daily', 0.8);
                                }
                            }                            
                        }
                    }
                }
            }
        }

}