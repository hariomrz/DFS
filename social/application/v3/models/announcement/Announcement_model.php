<?php

/**
 * This model is used for getting Announcement
 * @package    Announcement
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Announcement_model extends Common_Model {

    function __construct() {
        parent::__construct();
    }
    
    public function details($user_id) {
        $this->db->select('B.BlogGUID, B.CreatedDate, B.CanIgnore', FALSE);
        $this->db->select('IFNULL(B.Description,"") AS Description', FALSE);
        $this->db->select('IFNULL(B.ActionText,"") AS ActionText', FALSE);
        $this->db->select('IFNULL(B.URL,"") AS URL', FALSE);
        $this->db->select('IFNULL(B.Type,"") AS Type', FALSE);
        $this->db->select('IFNULL(B.MediaID,"") AS MediaID', FALSE);
        $this->db->select('IFNULL(B.Title,"") AS Title', FALSE);
        $this->db->select('IFNULL(B.RedirectTo,"") AS RedirectTo', FALSE);

        $this->db->from(BLOG . ' AS B');
        $this->db->where('B.EntityType', 4);
        $this->db->where('B.Status', 'PUBLISHED');

        $this->db->where("B.BlogID NOT IN (SELECT BlogID FROM ".USERIGNOREDANNOUCEMENT." WHERE UserID='".$user_id."' AND BlogID=B.BlogID)",null,false);

        $this->db->order_by('B.BlogID', 'DESC');
        $this->db->limit(1);
        $res = $this->db->get();
        
        $result = array();
        if ($res->num_rows()) {
            $result = $res->row_array();
            $result['ImageName'] = '';
            $result['ANDROID_VERSION'] = ANDROID_VERSION;
            $result['IOS_VERSION'] = IOS_VERSION;
            if(!empty($result['MediaID'])){
                $media = get_detail_by_id($result['MediaID'], 21,'ImageName, Resolution', 2);
                $result['ImageName'] = $media['ImageName'];
                $result['Resolution'] = $media['Resolution'];
            }

            if($result['URL']=='CUSTOM_URL') {
                $result['CustomUrl'] = $result['RedirectTo'];
            } else if($result['URL']=='POST') {
                $result['ActivityGUID'] = $result['RedirectTo'];
            } else if($result['URL']=='FEEDBACK') {
                $result['UserGUID'] = $result['RedirectTo'];

                $users = get_detail_by_guid($result['UserGUID'], 3, 'FirstName, LastName', 2);
                $result = array_merge($result, $users);

            } else if($result['URL']=='POST_TAG') {
                $this->load->model(array('tag/tag_model'));
                $tag = $this->tag_model->details($result['RedirectTo']);
                $result = array_merge($result, $tag);
            } else if($result['URL']=='QUESTION_CATEGORY') {
                $result['TagID'] = 20;
                $result['TagCategoryName'] = $this->get_tag_category_name($result['RedirectTo']);
                $result['TagCategoryID'] = $result['RedirectTo'];
            } else if($result['URL']=='CLASSIFIED_CATEGORY') {
                $result['TagID'] = 6;
                $result['TagCategoryName'] = $this->get_tag_category_name($result['RedirectTo']);
                $result['TagCategoryID'] = $result['RedirectTo'];
            } else if($result['URL']=='QUIZ') {
                $result['QuizGUID'] = $result['RedirectTo'];
                $result['QuizTitle'] =  get_detail_by_guid($result['RedirectTo'], 47, 'Title');
            }

            unset($result['MediaID']);
            unset($result['RedirectTo']);
        }
        return $result;
    }

    public function list($user_id) {
        $this->db->select('B.BlogGUID, B.CreatedDate, B.CanIgnore', FALSE);
        $this->db->select('IFNULL(B.Description,"") AS Description', FALSE);
        $this->db->select('IFNULL(B.ActionText,"") AS ActionText', FALSE);
        $this->db->select('IFNULL(B.URL,"") AS URL', FALSE);
        $this->db->select('IFNULL(B.Type,"") AS Type', FALSE);
        $this->db->select('IFNULL(B.MediaID,"") AS MediaID', FALSE);
        $this->db->select('IFNULL(B.Title,"") AS Title', FALSE);
        $this->db->select('IFNULL(B.RedirectTo,"") AS RedirectTo', FALSE);

        $this->db->from(BLOG . ' AS B');
        $this->db->where('B.EntityType', 4);
        $this->db->where('B.Status', 'PUBLISHED');

        $this->db->where("B.BlogID NOT IN (SELECT BlogID FROM ".USERIGNOREDANNOUCEMENT." WHERE UserID='".$user_id."' AND BlogID=B.BlogID)",null,false);

        $this->db->order_by('B.BlogID', 'DESC');

        $res = $this->db->get();
        
        $results = array();
        if ($res->num_rows()) {
            $results = $res->result_array();
            
            foreach ($results as $key => $result) {
                $result['ImageName'] = '';
                if(!empty($result['MediaID'])){
                    $media = get_detail_by_id($result['MediaID'], 21,'ImageName, Resolution', 2);
                    $result['ImageName'] = $media['ImageName'];
                    $result['Resolution'] = $media['Resolution'];
                }

                if($result['URL']=='CUSTOM_URL') {
                    $result['CustomUrl'] = $result['RedirectTo'];
                } else if($result['URL']=='POST') {
                    $result['ActivityGUID'] = $result['RedirectTo'];
                } else if($result['URL']=='FEEDBACK') {
                    $result['UserGUID'] = $result['RedirectTo'];

                    $users = get_detail_by_guid($result['UserGUID'], 3, 'FirstName, LastName', 2);
                    $result = array_merge($result, $users);

                } else if($result['URL']=='POST_TAG') {
                    $this->load->model(array('tag/tag_model'));
                    $tag = $this->tag_model->details($result['RedirectTo']);
                    $result = array_merge($result, $tag);
                } else if($result['URL']=='QUESTION_CATEGORY') {
                    $result['TagID'] = 20;
                    $result['TagCategoryName'] = $this->get_tag_category_name($result['RedirectTo']);
                    $result['TagCategoryID'] = $result['RedirectTo'];
                } else if($result['URL']=='CLASSIFIED_CATEGORY') {
                    $result['TagID'] = 6;
                    $result['TagCategoryName'] = $this->get_tag_category_name($result['RedirectTo']);
                    $result['TagCategoryID'] = $result['RedirectTo'];
                } else if($result['URL']=='QUIZ') {
                    $result['QuizGUID'] = $result['RedirectTo'];
                    $result['QuizTitle'] =  get_detail_by_guid($result['RedirectTo'], 47, 'Title');
                }

                unset($result['MediaID']);
                unset($result['RedirectTo']);

                $results[$key] = $result;
            }
        }
        return $results;
    }

    /**
     * ignore: To skip Announcemnet for a user          
     * @param [int] $blog_id
     * @param [int] $user_id
     */
    public function ignore($blog_id, $user_id)
    {
        $insert_data = array(
            'BlogID'=>$blog_id,
            'UserID'=>$user_id,
            'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'));            
        $this->db->insert(USERIGNOREDANNOUCEMENT,$insert_data);
        
        return true;
    }


    function get_tag_category_name($tag_category_id) {
        $name = '';
        $this->db->select('Name');
        $this->db->from(TAGCATEGORY);
        $this->db->where('TagCategoryID', $tag_category_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $name = $query->row()->Name;
        }
        return $name;
    }

}
