<?php

/**
 * Description of flags_model
 *
 * @author nitins
 */
class Flags_model extends Common_Model
{

    /**
     * Get List
     * @param type $start_offset
     * @param type $end_offset
     * @param type $start_date
     * @param type $end_date
     * @param type $modue_id
     * @param type $search_keyword
     * @param string $sort_by
     * @param type $order_by
     * @return type
     */
    public function get_list($entity_type = 'ARTICLE', $start_offset = 0, $end_offset = "", $start_date = "", $end_date = "", $search_keyword = "", $sort_by = "", $order_by = "")
    {
        $this->load->model(array('activity/activity_model'));
        $entity_type = strtoupper($entity_type);

        $this->db->select('count(F.FlagID) as flag_count');
        $this->db->where('F.StatusID', 2);
        $this->db->group_by(array("F.EntityType", "F.EntityID"));
        $this->db->from(FLAG . "  F");


        //join user
        switch ($entity_type):
            case 'ARTICLE':
                if (!empty($search_keyword))
                {
                    $this->db->like('A.Title', $search_keyword);
                }

                $this->db->where('F.EntityType', $entity_type);
                $this->db->where('A.StatusID', 2);
                $this->db->select('A.ArticleGuID as article_guid, A.Title as article_title, A.CreatedDate as created_date');
                $this->db->join(ARTICLE . ' A', 'A.ArticleID=F.EntityID');

                $this->db->select('U.FirstName, U.LastName');
                $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'LEFT');

                break;
            case 'ACTIVITY':
                if (!empty($search_keyword))
                {
                    $this->db->like('A.PostContent', $search_keyword);
                }

                $this->db->where('F.EntityType', $entity_type);
                $this->db->where('A.StatusID', 2);
                $this->db->select('A.ActivityID,A.ActivityGUID as activity_guid, A.PostContent as activity_post, A.CreatedDate as created_date');
                $this->db->join(ACTIVITY . ' A', 'A.ActivityID=F.EntityID');

                $this->db->select('U.FirstName, U.LastName');
                $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'LEFT');

                break;
            case 'BLOG':
                if (!empty($search_keyword))
                {
                    $this->db->like('B.Title', $search_keyword);
                }

                $this->db->where('F.EntityType', $entity_type);
                $this->db->where('B.Status', 'PUBLISHED');
                $this->db->select('B.BlogGUID as blog_guid, B.Title as blog_post, B.CreatedDate as created_date');
                $this->db->join(BLOG . ' B', 'B.BlogID=F.EntityID');

                $this->db->select('U.FirstName, U.LastName');
                $this->db->join(USERS . ' U', 'U.UserID=B.UserID', 'LEFT');

                break;
            case 'PAGE':
                if (!empty($search_keyword))
                {
                    $this->db->like('P.Title', $search_keyword);
                }

                $this->db->where('F.EntityType', $entity_type);
                $this->db->where('P.StatusID', 2);
                $this->db->where_not_in('U.StatusID', array(3, 4));
                $this->db->select('P.PageGUID as page_guid, P.Title,P.PageID,P.Description, P.PageURL');
                $this->db->select('P.CreatedDate AS created_date', FALSE);
                $this->db->join(PAGES . ' P', 'P.PageID=F.EntityID');

                $this->db->select('CONCAT(U.FirstName, " ", U.LastName)AS pageauthor', FALSE);
                $this->db->join(USERS . ' U', 'U.UserID=P.UserID', 'LEFT');
                if($sort_by=='CreatedDate'){
                    $sort_by='P.CreatedDate';
                }
                break;
            case 'USER':
                if (!empty($search_keyword))
                {
                    //$this->db->like('CONCAT(U.FirstName, " ", U.LastName)', $search_keyword);
                    $this->db->where("(U.FirstName like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_keyword) . "%' or concat(U.FirstName,' ',U.LastName) like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.Email like '%" . $this->db->escape_like_str($search_keyword) . "%')");
                }
                
                $this->db->where('F.EntityType', $entity_type);
                $this->db->where_not_in('U.StatusID', array(3, 4));
                $this->db->select('U.CreatedDate AS created_date', FALSE);
                $this->db->select('U.UserGUID ', FALSE);
                $this->db->select('CONCAT(U.FirstName, " ", U.LastName) AS Username, U.Email', FALSE);
                $this->db->select('p.Url as ProfileURL', FALSE);
                $this->db->join(USERS . ' U', 'U.UserID=F.EntityID', 'LEFT');
                $this->db->join(PROFILEURL . " as p", "p.EntityID = U.UserID and p.EntityType = 'User'", "LEFT");
                if($sort_by=='CreatedDate'){
                    $sort_by='U.CreatedDate';
                }
                break;
        endswitch;


        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Sort_by, Order_by */
        if ($sort_by == 'FlagID' || $sort_by == '')
            $sort_by = 'FlagID';

        if ($order_by == false || $order_by == '')
            $order_by = 'ASC';

        if ($order_by == 'true')
            $order_by = 'DESC';

        $this->db->order_by($sort_by, $order_by);

        /* Start_offset, end_offset */
        if (isset($start_offset) && $end_offset != '')
        {
            $this->db->limit($end_offset, $start_offset);
        }

        $query = $this->db->get();
        //echo $this->db->last_query();die;

        //$results['results'] = $query->result_array();
        $final_result=array();
        foreach ($query->result_array() as $row)
        {
            if(isset($row['activity_post']))
            {
                $row['activity_post']=$this->activity_model->parse_tag($row['activity_post'],$row['ActivityID'],0);
            }
            $final_result[]=$row;
        }
        $results['results']=$final_result;
        return $results;
    }

    /**
     * Remove Flag
     * @param type $entityType
     * @param type $entity_id
     */
    function remove_flag($entityType, $entity_id)
    {
        $entity_type = strtoupper($entityType);
        $this->db->where('EntityType', $entity_type);
        $this->db->where('EntityID', $entity_id);
        $this->db->update(FLAG, array('StatusID' => 3));
        $this->db->affected_rows();
        
        $this->db->where('ActivityID', $entity_id);
        $this->db->update(ACTIVITY,array('Flaggable'=>0));
        
        if(CACHE_ENABLE && $entity_type == 'ACTIVITY'){
            $this->db->select('UserID');
            $this->db->where('EntityType',$entity_type);
            $this->db->where('EntityID',$entity_id);
            $query = $this->db->get(FLAG);
            if($query->num_rows()){
                $result = $query->result_array();
                foreach ($result as $value)
                {
                    $this->cache->delete('user_flagged_activity' . $value['UserID']);
                }
            }
        }
    }

    /**
     * Get entity flags
     * @param type $entity_type
     * @param type $entity_id
     */
    function entity_flags($entity_type, $entity_id)
    {
        $entity_type = strtoupper($entity_type);
        $this->db->where('F.EntityType', $entity_type);
        $this->db->where('F.EntityID', $entity_id);
        $this->db->where('F.StatusID', 2);
        $this->db->select('SUBSTRING(F.FlagReason, 1, CHAR_LENGTH(F.FlagReason) - 1) AS FlagReason, F.CreatedDate');
        $this->db->from(FLAG . "  F");

        $this->db->select('U.FirstName, U.LastName');
        $this->db->select('CONCAT(U.FirstName, " ", U.LastName) AS Username', FALSE);
        $this->db->join(USERS . ' U', 'U.UserID=F.UserID', 'LEFT');
        $query = $this->db->get();
        return $query->result_array();
    }

}
