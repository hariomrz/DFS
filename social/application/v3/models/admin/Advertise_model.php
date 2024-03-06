<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Advertise_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Function for get users detail for Users listing
     * Parameters : start_offset, end_offset, start_date, end_date, user_status, search_keyword, sort_by, order_by
     * Return : Users array
     */
    //craete blog data
    function create_banner($BlogData = array()) {
        if(isset($BlogData['Advertiser']) && $BlogData['Advertiser']=='bhopu') {
            $this->db->select('BlogID', false);
            $this->db->from(ADVERTISE);
            $this->db->where('Advertiser', trim($BlogData['Advertiser']));
            $this->db->where('Status', 2);
            $sql = $this->db->get();
            //echo $this->db->last_query();die;
            if ($sql->num_rows() > 0) {
                $BlogInfo = $sql->row();
                $BlogID = $BlogInfo->BlogID;
                $this->update_blog(array('BlogID' => $BlogID, 'Status' =>3));
            }
        }    
        $this->db->insert(ADVERTISE, $BlogData);
        $BlogID = $this->db->insert_id();
        return $BlogID;
    }

    //updated static blog data
    function update_banner($BlogData = array()) {
        $this->db->where_in('BlogID', $BlogData['BlogID']);
        unset($BlogData['BlogID']);
        $this->db->update(ADVERTISE, $BlogData);
        return true;
    }

    /**
     * Get banner images 
     * @param string $Advertiser
     * @return type
     */
    function get_banner_images($Advertiser, $BannerModule) {
        /*
         * $this->db->select('B.BlogID, B.BlogImage, B.BannerSize');
          $this->db->from(BLOG . " AS B");

          $this->db->where('Type','banner');
          $this->db->where('Status != 3');
          $this->db->where("BlogUniqueID != 'contactus'");

          $this->db->group_by('B.BlogImage');
         */
        $this->db->select('M.ImageName');
        $this->db->from(MEDIA . " AS M");
        $this->db->join(ADVERTISER . ' A', 'A.AdvertiserID=M.ModuleEntityID', 'left'); // M.ModuleEntityID stores AdvertiserID

        $this->db->where('M.StatusID', 2);
        $this->db->where("M.MediaSectionID", 7); // 7: Advertise Banner

        if (!empty($Advertiser)) {
            $this->db->where('A.AdvertiserName', trim($Advertiser));
        }

        if (!empty($BannerModule)) {

            $Images_300x300 = array('article_detail', 'chat_detail', 'photo_detail', 'video_detail', 'profile', 'dashboard', 'monthly_competition', 'notification', 'manics', 'home_page_sidebar2',
                'race_event_sidebar1', 'race_event_sidebar2', 'race_event_sidebar3', 'race_event_sidebar4', 'race_event_sidebar5', 'race_event_sidebar6', 'race_event_sidebar7', 'race_event_sidebar8'
            );
            $Images_1600x400 = array('home_page_carousel', 'race_event_carousel');
            $Images_300x600 = array('home_page_sidebar1');

            if (in_array($BannerModule, $Images_300x300)) {
                $this->db->where_in("M.Caption", $Images_300x300);
            } else if (in_array($BannerModule, $Images_1600x400)) {
                $this->db->where_in("M.Caption", $Images_1600x400);
            } else if (in_array($BannerModule, $Images_300x600)) {
                $this->db->where_in("M.Caption", $Images_300x600);
            }
        }

        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    /**
     * Get banner images 
     * @param string $Advertiser
     * @return type
     */
    public function get_banner($start_offset, $end_offset, $start_date, $end_date, $status_text, $search_keyword, $module_text, $sort_by, $order_by) {
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $this->db->select('B.BlogID, B.BlogTitle, B.CreatedDate, B.Type, B.BlogImage, B.Status, B.BannerSize');
        $this->db->select('B.BlogUniqueID, B.BannerSource, DATE_FORMAT(B.StartDate,"%b %d, %Y") AS StartDate, DATE_FORMAT(B.EndDate,"%b %d, %Y") AS EndDate, B.Advertiser', false);
        $this->db->select('LEFT( B.BlogDescription, 50 ) AS BlogDescription', false);
        $this->db->select('S.StatusName');
        $this->db->from(ADVERTISE . " AS B ");
        $this->db->join(STATUS . ' S', 'B.Status=S.StatusID', 'left');
        $this->db->where("B.Type", "banner");

        if (!empty($status_text)) {
            if ($status_text == 'Active') {
                $this->db->where("B.Status", 2);
                $this->db->where('DATE_FORMAT(B.EndDate, "%Y-%m-%d") >=', date('Y-m-d'));
            } else if ($status_text == 'Inactive') {
                $this->db->where("B.Status", 4);
            } else if ($status_text == 'Expire') {
                $this->db->where('DATE_FORMAT(B.EndDate, "%Y-%m-%d") <', date('Y-m-d'));
                $this->db->where("B.Status !=3");
            }
        } else {
            $this->db->where("B.Status !=3");
        }

        if (!empty($module_text)) {
            $this->db->where("B.BlogUniqueID", $module_text);
        }

        if (!empty($search_keyword)) {
            $search_keyword = strtolower($search_keyword);
            $SearchStr = "( LOWER(B.BlogTitle) like '%" . $this->db->escape_like_str($search_keyword) . "%'";
            $SearchStr .= " or LOWER(B.Advertiser) like '%" . $this->db->escape_like_str($search_keyword) . "%'";
            $SearchStr .= ')';
            $this->db->where($SearchStr);
        }

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Sort_by, Order_by */
        if ($sort_by == '') {
            $sort_by = 'B.BlogUniqueID';
        }

        if ($order_by == false || $order_by == '') {
            $order_by = 'ASC';
        }

        if ($order_by == 'true') {
            $order_by = 'DESC';
        }

        $this->db->order_by($sort_by, $order_by);

        /* Start_offset, end_offset */
        if (isset($start_offset) && $end_offset != '') {
            $this->db->limit($end_offset, $start_offset);
        }

        $query = $this->db->get();
        //$results['results'] = $query->result_array();
        //echo $this->db->last_query();die;

        $results['results'] = array();
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $banner) {
                if (strtotime($banner['EndDate']) < strtotime(date('Y-m-d'))) {
                    $banner['StatusName'] = 'Expire';
                }
                $results['results'][] = $banner;
            }
        }
        return $results;
    }

    /**
     * Get banner images 
     * @param string $Advertiser
     * @return type
     */
    function GetBannerData($Data) {
        $this->db->select('B.BlogID, B.BlogTitle, B.BlogDescription, B.CreatedDate, B.Type, B.BlogImage, B.Status, B.BlogUniqueID', FALSE);
        $this->db->select('B.BannerSource, B.URL, B.BannerSize, B.Duration, B.NoOfHits, B.Advertiser, B.AdvertiserContact, B.SourceScript, B.Location', FALSE);
        $this->db->select('DATE_FORMAT(B.StartDate,"%b %d, %Y") AS StartDate, DATE_FORMAT(B.EndDate,"%b %d, %Y") AS EndDate', FALSE);
        $this->db->from(ADVERTISE . " AS B");
        $this->db->where('B.BlogID', $Data['BlogID']);
        $sql = $this->db->get();
        if ($sql->num_rows()) {
            $data = $sql->row();
            $data->BlogImage = $data->BlogImage;
            $data->BlogImageSrc = IMAGE_SERVER_PATH . 'upload/banner/' . $data->BlogImage;
            $data->BlogDescription = html_entity_decode(str_replace('%7B%7BSITEURL%7D%7D', IMAGE_SERVER_PATH, $data->BlogDescription));
            return $data;
        }
        return FALSE;
    }

    /**
     * Get banner images 
     * @param string $Advertiser
     * @return type
     */
    function GetDefaultBannerData($Data) {
        $this->db->select('B.BlogID, B.BlogTitle, B.BlogDescription, B.CreatedDate, B.Type, B.BlogImage, B.Status, B.BlogUniqueID', FALSE);
        $this->db->select('B.BannerSource, B.URL, B.BannerSize, B.Duration, B.NoOfHits, B.Advertiser, B.AdvertiserContact, B.SourceScript', FALSE);
        $this->db->select('DATE_FORMAT(B.StartDate,"%b %d, %Y") AS StartDate, DATE_FORMAT(B.EndDate,"%b %d, %Y") AS EndDate', FALSE);
        $this->db->from(ADVERTISE . " AS B");

        if (!empty($Data['BlogID'])) {
            $this->db->where('B.BlogID', $Data['BlogID']);
        }
        if (!empty($Data['Type'])) {
            $this->db->where('B.Type', $Data['Type']);
        }

        $sql = $this->db->get();
        if ($sql->num_rows()) {
            $data = $sql->row();
            $data->BlogImage = $data->BlogImage;
            $data->BlogImageSrc = IMAGE_SERVER_PATH . 'upload/banner/' . $data->BlogImage;
            $data->BlogDescription = html_entity_decode(str_replace('%7B%7BSITEURL%7D%7D', IMAGE_SERVER_PATH, $data->BlogDescription));
            return $data;
        }
        return FALSE;
    }

    /**
     * Save Advertiser name in Advertiser master
     * @param string $Advertiser [Advertiser name]
     * @param integer $AdvertiserID
     */
    function save_advertiser($Advertiser) {
        if (!empty($Advertiser)) {
            $this->db->select('AdvertiserName, AdvertiserID', false);
            $this->db->from(ADVERTISER . ' AS A');
            $this->db->where('AdvertiserName', trim($Advertiser));
            $sql = $this->db->get();
            //echo $this->db->last_query();die;
            if ($sql->num_rows() > 0) {
                $AdvertiserInfo = $sql->row();
                $AdvertiserID = $AdvertiserInfo->AdvertiserID;
            } else {
                $this->db->insert(ADVERTISER, array('AdvertiserName' => trim($Advertiser)));
                $AdvertiserID = $this->db->insert_id();
            }
            return $AdvertiserID;
        } else {
            return FALSE;
        }
    }

    /**
     * Get banner images 
     * @param string $Advertiser
     * @return type
     */
    function get_advertiser($Data) {
        $this->db->select('A.AdvertiserName');
        $this->db->from(ADVERTISER . " AS A");

        //$this->db->where("AdvertiserName like %'".$Data['SearchText']."'%");
        $this->db->like('AdvertiserName', $Data['SearchText']);

        $query = $this->db->get();
        $result = $query->result_array();

        $result = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $advertiser) {
                $result[] = $advertiser['AdvertiserName'];
            }
        }
        return $result;
    }

    //craete blog data
    function create_blog($BlogData = array()) {
        $this->db->insert(ADVERTISE, $BlogData);
        $BlogID = $this->db->insert_id();
        return $BlogID;
    }

    //updated static blog data
    function update_blog($BlogData = array()) {
        $this->db->where_in('BlogID', $BlogData['BlogID']);
        unset($BlogData['BlogID']);
        $this->db->update(ADVERTISE, $BlogData);
        return true;
    }
    
    function get_feed_banner() {
        $result = array();
        if (CACHE_ENABLE) {
            $result = $this->cache->get('fban');            
        }
        if(empty($result)) {
            $this->db->select('BlogImage as ImageName, BannerSize as Resolution');
            $this->db->from(ADVERTISE);
            $this->db->where('Advertiser', 'bhopu');
            $this->db->where('Status', 2);
            $this->db->limit(1);
            $query = $this->db->get();
            //echo $this->db->last_query();die;

            if ($query->num_rows() > 0) {
                $result = $query->row_array();
                if (CACHE_ENABLE) {
                    $this->cache->save('fban', $result, CACHE_EXPIRATION);
                }
            }
        }
        return $result;
    }
}
