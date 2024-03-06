<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Media_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Count total rows 
     * @param string $table
     * @param string $condtion
     * @return int
     */
    public function getTotal($condtion = null) {
        if (!empty($condtion)) {
            $this->db->where($condtion, null, false);
        }
        $this->db->where('M.StatusID !=', '3');        
        $this->db->join(STATUS . " AS S", ' S.StatusID = M.StatusID', 'right');
        $this->db->join(MEDIASECTIONS . " AS MS", ' MS.MediaSectionID = M.MediaSectionID', 'right');
        $this->db->join(MEDIAEXTENSIONS . " AS ME", ' ME.MediaExtensionID = M.MediaExtensionID', 'right');
        $this->db->join(MEDIATYPES . " AS MT", ' MT.MediaTypeId = ME.MediaTypeId', 'right');
        $this->db->join(USERS . " AS U", ' U.UserID = M.userID', 'right');
        $this->db->from(MEDIA . " AS M");
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query->num_rows();
    }

    /**
     * Get Media TYpe COunt
     * @param type $mediaTypeId
     * @param type $condtion
     * @return type
     */
    public function getMediaTypeCount($mediaTypeId, $condtion = NULL) {
        
        $mediaTypeId = $this->db->escape_str($mediaTypeId);
        $this->db->select('M.Size');
        $this->db->where(" M.MediaExtensionID IN (SELECT ME.MediaExtensionID FROM " . MEDIAEXTENSIONS . " ME WHERE ME.MediaTypeId=" . $mediaTypeId . " )", NULL, FALSE);
        $this->db->where('StatusID != ', '3');
        if ($condtion !== NULL) {
            $this->db->where($condtion, NULL, FALSE);
        }
        $query = $this->db->get(MEDIA . ' M');
        $result = array(
            'total' => $query->num_rows(),
            'Data' => $query->result_array()
        );
        return $result;
    }

    /**
     * Function for get media for Media lising for user
     * Parameters : user_id, start_offset, end_offset, adminApproved, search_keyword, sort_by, order_by
     * Return : array
     */
    public function getMedia($userId = NUll, $sort_by = "", $order_by = "", $start_offset = 0, $end_offset = "", $IsAdminApproved, $filter) {
        
        $this->db->select('M.MediaID', FALSE);
        $this->db->select('M.ImageName', FALSE);
        $this->db->select('M.StatusID', FALSE);
        $this->db->select('M.MediaSectionID', FALSE);
        $this->db->select('M.DeviceID', FALSE);
        $this->db->select('M.SourceID', FALSE);
        $this->db->select('M.MediaExtensionID', FALSE);
        $this->db->select('M.AbuseCount', FALSE);
        $this->db->select('M.IsAdminApproved', FALSE);
        $this->db->select('M.Size', FALSE);
        $this->db->select('M.MediaSizeID', FALSE);
        $this->db->select('M.ImageUrl', FALSE);

        $this->db->select('S.StatusName', FALSE);
        $this->db->select('MS.Name as MediaSection', FALSE);
        $this->db->select('MS.MediaSectionAlias', FALSE);
        $this->db->select('ME.Name as MediaExtension', FALSE);
        $this->db->select('ME.MediaTypeId', FALSE);
        $this->db->select('MT.Name as MediaType', FALSE);

        //$this->db->select('DATE_FORMAT(M.CreatedDate, "' . $mysql_date . '") AS CreatedDate', FALSE);
        $this->db->select('M.CreatedDate AS CreatedDate', FALSE);
        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS UserName', FALSE);
        $this->db->select('U.UserGUID');

        $this->db->join(STATUS . " AS S", ' S.StatusID = M.StatusID', 'right');
        $this->db->join(MEDIASECTIONS . " AS MS", ' MS.MediaSectionID = M.MediaSectionID', 'right');
        $this->db->join(MEDIAEXTENSIONS . " AS ME", ' ME.MediaExtensionID = M.MediaExtensionID', 'right');
        $this->db->join(MEDIATYPES . " AS MT", ' MT.MediaTypeId = ME.MediaTypeId', 'right');
        $this->db->join(USERS . " AS U", ' U.UserID = M.userID', 'right');
        $this->db->from(MEDIA . " AS M");

        if ($IsAdminApproved != 2) {
            $this->db->where_in('M.IsAdminApproved', $IsAdminApproved);
        }
        
        if ($filter['Extensions']) {
            $this->db->where_in('M.MediaExtensionID', $filter['Extensions']);
        }
        if ($filter['Sizes']) {
            $this->db->where_in('M.MediaSizeID', $filter['Sizes']);
        }
        if ($filter['Sources']) {
            $this->db->where_in('M.SourceID', $filter['Sources']);
        }
        if ($filter['Sections']) {
            $this->db->where_in('M.MediaSectionID', $filter['Sections']);
        }
        if ($filter['Devices']) {
            $this->db->where_in('M.DeviceID', $filter['Devices']);
        }

        $this->db->where('M.StatusID != ', 3);

        if (!empty($userId) && $userId !== NULL) {
            $this->db->where('M.UserID', $userId);
        }

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $results['total_records'] = $tempdb->count_all_results();

        /* Sort_by / order_by */
        $order_by = 'DESC';

        if ($sort_by == 'recent' || $sort_by == '')
            $sort_by = 'MediaID';

        $this->db->order_by($sort_by, $order_by);

        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset, $start_offset);


        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results['results'] = $query->result_array();
        return $results;
    }

    /**
     * Update Media
     * @param type $data
     * @param type $key
     */
    function updateMedias($data, $key) {
        $this->db->update_batch(MEDIA, $data, $key);
    }

    /**
     * Delete Media
     * @param type $data
     * @param type $key
     */
    function deleteMedia($mediaID) {
        $this->db->delete(MEDIAABUSE, array('MediaID' => $mediaID));
        return true;
    }

    /**
     * 
     * @param type $userId
     * @param type $approved
     * @return type
     */
    function getDeviceTypeCounts($userId = NULL, $approved = 0) {

        $this->db->select('count(DeviceID) AS ApproveCount,0 AS YetToApproveCount', FALSE);
        $this->db->select('DeviceID');
        $this->db->select('DT.Name');

        $this->db->join(DEVICETYPES . " AS DT", ' DT.DeviceTypeID = DeviceID', 'right');
        $this->db->from(MEDIA . " AS M");

        $this->db->where('M.StatusID !=', 3);
        if ($approved != 2) {
            //$this->db->where('M.IsAdminApproved', $approved);
        }

        if ($userId != '')
            $this->db->where('M.UserID', $userId);

        $this->db->group_by('DeviceID');

        $query1 = $this->db->get();
        $r1 = $query1->result_array();

        $this->db->select('count(DeviceID) AS counts');
        $this->db->select('DeviceID');
        $this->db->select('DT.Name');

        $this->db->join(DEVICETYPES . " AS DT", ' DT.DeviceTypeID = DeviceID', 'right');
        $this->db->from(MEDIA . " AS M");

        $this->db->where('M.StatusID !=', 3);
        $this->db->where('M.IsAdminApproved', '0');

        if ($userId != '')
            $this->db->where('M.UserID', $userId);

        $this->db->group_by('DeviceID');

        $query2 = $this->db->get();

        $r2 = $query2->result_array();

        $data = $r1;
        $i = 0;
        foreach ($r1 as $d1) {
            foreach ($r2 as $d2) {
                if ($d1['Name'] == $d2['Name']) {
                    $data[$i]['ApproveCount'] = $data[$i]['ApproveCount'] - $d2['counts'];
                    $data[$i]['YetToApproveCount'] = $data[$i]['YetToApproveCount'] + $d2['counts'];
                }
            }
            $i++;
        }
        return $data;
    }

    function getDeviceTypeCountsAll() {
        $this->db->select('MediaDeviceCounts.DeviceTypeID as DeviceID,MediaDeviceCounts.ApproveCount,MediaDeviceCounts.YetToApproveCount,DeviceTypes.Name');
        $this->db->from('MediaDeviceCounts');
        $this->db->join('DeviceTypes', 'MediaDeviceCounts.DeviceTypeID=DeviceTypes.DeviceTypeID', 'left');
        $this->db->where('MediaDeviceCounts.DeviceTypeID is not null', '', false);
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * 
     * @param type $userId
     * @param type $approved
     * @return type
     */
    function getExtensionCount($userId = NULL, $approved = 0) {

        $this->db->select('count(M.MediaExtensionID) AS ApproveCount,0 AS YetToApproveCount', FALSE);
        $this->db->select('M.MediaExtensionID');
        $this->db->select('ME.Name');
        $this->db->select('MT.Name as MediaType');

        $this->db->from(MEDIA . " AS M");        
        $this->db->join(MEDIAEXTENSIONS . " AS ME", ' ME.MediaExtensionID = M.MediaExtensionID', 'right');
        $this->db->join(MEDIATYPES." as MT", 'MT.MediaTypeID = ME.MediaTypeID', 'right');
        

        $this->db->where('M.StatusID !=', 3);

        if ($userId != '')
            $this->db->where('M.UserID', $userId);

        $this->db->group_by('M.MediaExtensionID');

        $query1 = $this->db->get();
        $r1 = $query1->result_array();

        $this->db->select('count(M.MediaExtensionID) AS counts');
        $this->db->select('M.MediaExtensionID');
        $this->db->select('ME.Name');
        $this->db->select('MT.Name as MediaType');

        $this->db->from(MEDIA . " AS M");        
        $this->db->join(MEDIAEXTENSIONS . " AS ME", ' ME.MediaExtensionID = M.MediaExtensionID', 'right');
        $this->db->join(MEDIATYPES." as MT", 'MT.MediaTypeID = ME.MediaTypeID', 'right');
        
        $this->db->where('M.StatusID !=', 3);
        $this->db->where('M.IsAdminApproved', '0');

        if ($userId != '')
            $this->db->where('M.UserID', $userId);

        $this->db->group_by('M.MediaExtensionID');

        $query2 = $this->db->get();
        $r2 = $query2->result_array();

        $data = $r1;
        $i = 0;
        foreach ($r1 as $d1) {
            foreach ($r2 as $d2) {
                if ($d1['Name'] == $d2['Name']) {
                    $data[$i]['ApproveCount'] = $data[$i]['ApproveCount'] - $d2['counts'];
                    $data[$i]['YetToApproveCount'] = $data[$i]['YetToApproveCount'] + $d2['counts'];
                }
            }
            $i++;
        }        
        return $data;
    }

    function getExtensionCountAll() {
        $this->db->select('MEC.MediaExtensionID,MEC.ApproveCount,MEC.YetToApproveCount,ME.Name, MT.Name as MediaType');
        $this->db->from(MEDIAEXTENSIONCOUNT." as MEC");
        $this->db->join(MEDIAEXTENSIONS." as ME", 'MEC.MediaExtensionID = ME.MediaExtensionID', 'left');
        $this->db->join(MEDIATYPES." as MT", 'MT.MediaTypeID = ME.MediaTypeID', 'left');
        $this->db->where('MEC.MediaExtensionID is not null', '', false);
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * 
     * @param type $userId
     * @param type $approved
     * @return type
     */
    function getSectionCount($userId = NULL, $approved = 0) {

        $this->db->select('count(M.MediaSectionID) AS ApproveCount,0 AS YetToApproveCount', FALSE);
        $this->db->select('M.MediaSectionID');
        $this->db->select('MS.Name');

        $this->db->join(MEDIASECTIONS . " AS MS", ' MS.MediaSectionID = M.MediaSectionID ', 'right');
        $this->db->from(MEDIA . " AS M");

        $this->db->where('M.StatusID !=', 3);

        if ($userId != '')
            $this->db->where('M.UserID', $userId);

        $this->db->group_by('M.MediaSectionID');

        $query1 = $this->db->get();
        $r1 = $query1->result_array();

        $this->db->select('count(M.MediaSectionID) as counts');
        $this->db->select('M.MediaSectionID');
        $this->db->select('MS.Name');

        $this->db->join(MEDIASECTIONS . " AS MS", ' MS.MediaSectionID = M.MediaSectionID ', 'right');
        $this->db->from(MEDIA . " AS M");

        $this->db->where('M.StatusID !=', 3);
        $this->db->where('M.IsAdminApproved', '0');

        if ($userId != '')
            $this->db->where('M.UserID', $userId);

        $this->db->group_by('M.MediaSectionID');

        $query2 = $this->db->get();
        $r2 = $query2->result_array();

        $data = $r1;
        $i = 0;
        foreach ($r1 as $d1) {
            foreach ($r2 as $d2) {
                if ($d1['Name'] == $d2['Name']) {
                    $data[$i]['ApproveCount'] = $data[$i]['ApproveCount'] - $d2['counts'];
                    $data[$i]['YetToApproveCount'] = $data[$i]['YetToApproveCount'] + $d2['counts'];
                }
            }
            $i++;
        }
        return $data;
    }

    function getSectionCountAll() {
        $this->db->select('MediaSectionCount.MediaSectionID,MediaSectionCount.ApproveCount,MediaSectionCount.YetToApproveCount,' . MEDIASECTIONS . '.Name');
        $this->db->from('MediaSectionCount');
        $this->db->join(MEDIASECTIONS, 'MediaSectionCount.MediaSectionID=' . MEDIASECTIONS . '.MediaSectionID', 'left');
        $this->db->where('MediaSectionCount.MediaSectionID is not null', '', false);
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * 
     * @param type $userId
     * @param type $approved
     * @return type
     */
    function getSizeCount($userId = NULL, $approved = 0) {

        $this->db->select('count(M.MediaSizeID) AS ApproveCount,0 AS YetToApproveCount', FALSE);
        $this->db->select('M.MediaSizeID');
        $this->db->select('MS.Name');

        $this->db->join(MEDIASIZES . " AS MS", ' MS.MediaSizeID = M.MediaSizeID ', 'right');
        $this->db->from(MEDIA . " AS M");

        $this->db->where('M.StatusID !=', 3);

        if ($userId != '')
            $this->db->where('M.UserID', $userId);

        $this->db->group_by('M.MediaSizeID');

        $query1 = $this->db->get();
        $r1 = $query1->result_array();

        $this->db->select('count(M.MediaSizeID) as counts');
        $this->db->select('M.MediaSizeID');
        $this->db->select('MS.Name');

        $this->db->join(MEDIASIZES . " AS MS", ' MS.MediaSizeID = M.MediaSizeID ', 'right');
        $this->db->from(MEDIA . " AS M");

        $this->db->where('M.StatusID !=', 3);
        $this->db->where('M.IsAdminApproved', '0');

        if ($userId != '')
            $this->db->where('M.UserID', $userId);

        $this->db->group_by('M.MediaSizeID');

        $query2 = $this->db->get();
        $r2 = $query2->result_array();

        $data = $r1;
        $i = 0;
        foreach ($r1 as $d1) {
            foreach ($r2 as $d2) {
                if ($d1['Name'] == $d2['Name']) {
                    $data[$i]['ApproveCount'] = $data[$i]['ApproveCount'] - $d2['counts'];
                    $data[$i]['YetToApproveCount'] = $data[$i]['YetToApproveCount'] + $d2['counts'];
                }
            }
            $i++;
        }
        return $data;
    }

    function getSizeCountAll() {
        $this->db->select('MediaSizeCounts.MediaSizeID,MediaSizeCounts.ApproveCount,MediaSizeCounts.YetToApproveCount,' . MEDIASIZES . '.Name');
        $this->db->from('MediaSizeCounts');
        $this->db->join(MEDIASIZES, 'MediaSizeCounts.MediaSizeID=' . MEDIASIZES . '.MediaSizeID', 'left');
        $this->db->where('MediaSizeCounts.MediaSizeID is not null', '', false);
        $this->db->order_by(MEDIASIZES.".MediaSizeID ASC");
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * 
     * @param type $userId
     * @param type $approved
     * @return type
     */
    function getSourceCount($userId = NULL, $approved = 0) {

        $this->db->select('count(M.SourceID) AS ApproveCount,0 AS YetToApproveCount', FALSE);
        $this->db->select('M.SourceID');
        $this->db->select('S.Name');

        $this->db->join(SOURCES . " AS S", ' S.SourceID = M.SourceID ', 'right');
        $this->db->from(MEDIA . " AS M");

        $this->db->where('M.StatusID !=', 3);

        if ($userId != '')
            $this->db->where('M.UserID', $userId);

        $this->db->group_by('M.SourceID');

        $query1 = $this->db->get();
        $r1 = $query1->result_array();

        $this->db->select('count(M.SourceID) as counts');
        $this->db->select('M.SourceID');
        $this->db->select('S.Name');

        $this->db->join(SOURCES . " AS S", ' S.SourceID = M.SourceID ', 'right');
        $this->db->from(MEDIA . " AS M");

        $this->db->where('M.StatusID !=', 3);
        $this->db->where('M.IsAdminApproved', '0');

        if ($userId != '')
            $this->db->where('M.UserID', $userId);

        $this->db->group_by('M.SourceID');

        $query2 = $this->db->get();
        $r2 = $query2->result_array();

        $data = $r1;
        $i = 0;
        foreach ($r1 as $d1) {
            foreach ($r2 as $d2) {
                if ($d1['Name'] == $d2['Name']) {
                    $data[$i]['ApproveCount'] = $data[$i]['ApproveCount'] - $d2['counts'];
                    $data[$i]['YetToApproveCount'] = $data[$i]['YetToApproveCount'] + $d2['counts'];
                }
            }
            $i++;
        }
        return $data;
    }

    function getSourceCountAll() {
        $this->db->select('MediaSourceCount.SourceID,MediaSourceCount.ApproveCount,MediaSourceCount.YetToApproveCount,' . SOURCES . '.Name');
        $this->db->from('MediaSourceCount');
        $this->db->join(SOURCES, 'MediaSourceCount.SourceID=' . SOURCES . '.SourceID', 'left');
        $this->db->where('MediaSourceCount.SourceID is not null', '', false);
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Function for get abused media total count
     * Parameters : $DeviceId,$MediaExtensionId,$SourceId,$MediaSectionId,$MediaSizeId
     * Return : array
     */
    function getAbusedMediaTotal($DeviceId, $MediaExtensionId, $SourceId, $MediaSectionId, $MediaSizeId) {
        $this->db->select('COUNT(DISTINCT M.MediaID) AS total');
        $this->db->from(MEDIA . " AS M ");

        $this->db->where(" M.MediaID IN (SELECT MediaID FROM " . MEDIAABUSE . " GROUP BY MediaID)", NULL, FALSE);

        if (isset($DeviceId) && $DeviceId != '')
            $this->db->where("M.DeviceID IN ", rtrim($DeviceId, ","));

        if (isset($MediaExtensionId) && $MediaExtensionId != '')
            $this->db->where("M.MediaExtensionID IN ", rtrim($MediaExtensionId, ","));

        if (isset($SourceId) && $SourceId != '')
            $this->db->where("M.SourceID IN ", rtrim($SourceId, ","));

        if (isset($MediaSectionId) && $MediaSectionId != '')
            $this->db->where("M.MediaSectionID IN ", rtrim($MediaSectionId, ","));

        if (isset($MediaSizeId) && $MediaSizeId != '')
            $this->db->where("M.Size IN ", rtrim($MediaSizeId, ","));

        $query = $this->db->get();
        return $query->row_array();
    }

    public function getMediaAbuseTotalResult($filter){
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        //Make full URL and Get abusedMediaIDS
        $ids = $this->getAbusedMediaID();

        $this->db->select('M.MediaID', FALSE);
        $this->db->select('M.ImageName', FALSE);
        $this->db->select('M.StatusID', FALSE);
        $this->db->select('M.MediaSectionID', FALSE);
        $this->db->select('M.DeviceID', FALSE);
        $this->db->select('M.SourceID', FALSE);
        $this->db->select('M.MediaExtensionID', FALSE);
        $this->db->select('M.IsAdminApproved', FALSE);
        $this->db->select('M.Size', FALSE);
        $this->db->select('M.ImageUrl', FALSE);
        $this->db->select('COUNT(MediaAbuseID) AbuseCount');

        $this->db->select('S.StatusName', FALSE);
        $this->db->select('MS.Name as MediaSection', FALSE);
        $this->db->select('MS.MediaSectionAlias', FALSE);
        $this->db->select('ME.Name as MediaExtension', FALSE);

        $this->db->select('DATE_FORMAT(M.CreatedDate, "' . $mysql_date . '") AS CreatedDate', FALSE);

        $this->db->join(STATUS . " AS S", ' S.StatusID = M.StatusID', 'right');
        $this->db->join(MEDIASECTIONS . " AS MS", ' MS.MediaSectionID = M.MediaSectionID', 'right');
        $this->db->join(MEDIAEXTENSIONS . " AS ME", ' ME.MediaExtensionID = M.MediaExtensionID', 'right');
        $this->db->join(MEDIAABUSE . " AS MA", ' MA.MediaID = M.MediaID')->group_by('M.MediaID');
        $this->db->from(MEDIA . " AS M ");

        $ids = explode(',', $ids);
        $this->db->where_in('M.MediaID ', $ids);
        if ($filter['Extensions']) {
            $this->db->where_in('M.MediaExtensionID', $filter['Extensions']);
        }
        if ($filter['Sizes']) {
            $this->db->where_in('M.MediaSizeID', $filter['Sizes']);
        }
        if ($filter['Sources']) {
            $this->db->where_in('M.SourceID', $filter['Sources']);
        }
        if ($filter['Sections']) {
            $this->db->where_in('M.MediaSectionID', $filter['Sections']);
        }
        if ($filter['Devices']) {
            $this->db->where_in('M.DeviceID', $filter['Devices']);
        }
        return $this->db->get()->num_rows();
    }

    /**
     * Function for get abused media for Abused Media lising page
     * Parameters : $sort_by, $order_by, $start_offset, $end_offset
     * Return : array
     */
    public function getAbusedMedia($sort_by = "", $order_by = "", $start_offset = 0, $end_offset = "",$filter) {
        
        //Make full URL and Get abusedMediaIDS
        $ids = $this->getAbusedMediaID();

        $this->db->select('M.MediaID', FALSE);
        $this->db->select('M.ImageName', FALSE);
        $this->db->select('M.StatusID', FALSE);
        $this->db->select('M.MediaSectionID', FALSE);
        $this->db->select('M.DeviceID', FALSE);
        $this->db->select('M.SourceID', FALSE);
        $this->db->select('M.MediaExtensionID', FALSE);
        $this->db->select('M.IsAdminApproved', FALSE);
        $this->db->select('M.Size', FALSE);
        $this->db->select('M.ImageUrl', FALSE);
        $this->db->select('COUNT(MA.MediaAbuseID) AbuseCount');

        $this->db->select('S.StatusName', FALSE);
        $this->db->select('MA.CreatedDate', FALSE);
        $this->db->select('MS.Name as MediaSection', FALSE);
        $this->db->select('MS.MediaSectionAlias', FALSE);
        $this->db->select('ME.Name as MediaExtension', FALSE);
        
        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS UserName', FALSE);
        $this->db->select('U.UserGUID');
                
        $this->db->join(STATUS . " AS S", ' S.StatusID = M.StatusID', 'right');
        $this->db->join(MEDIASECTIONS . " AS MS", ' MS.MediaSectionID = M.MediaSectionID', 'right');
        $this->db->join(MEDIAEXTENSIONS . " AS ME", ' ME.MediaExtensionID = M.MediaExtensionID', 'right');
        $this->db->join(MEDIAABUSE . " AS MA", ' MA.MediaID = M.MediaID')->group_by('M.MediaID');
        $this->db->join(USERS . " AS U", ' U.UserID = M.UserID', 'right');
        $this->db->order_by("MA.CreatedDate","DESC");
        //Sub Query for select last flagged date of media
       /* $sub = $this->subquery->start_subquery('select');
        $sub->select('SMA.CreatedDate AS CreatedDate',FALSE);
        $sub->from(MEDIAABUSE." AS SMA ");
        $sub->where('SMA.MediaID = M.MediaID');
        $sub->order_by("SMA.CreatedDate","DESC");
        $sub->limit("1");
        $this->subquery->end_subquery('CreatedDate');*/
        
        $this->db->from(MEDIA . " AS M ");

        $media_ids = explode(',', $ids);
        $this->db->where_in('M.MediaID ', $media_ids);
        //$this->db->where_in('M.IsAdminApproved ', 0);
        if ($filter['Extensions']) {
            $this->db->where_in('M.MediaExtensionID', $filter['Extensions']);
        }
        if ($filter['Sizes']) {
            $this->db->where_in('M.MediaSizeID', $filter['Sizes']);
        }
        if ($filter['Sources']) {
            $this->db->where_in('M.SourceID', $filter['Sources']);
        }
        if ($filter['Sections']) {
            $this->db->where_in('M.MediaSectionID', $filter['Sections']);
        }
        if ($filter['Devices']) {
            $this->db->where_in('M.DeviceID', $filter['Devices']);
        }

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        /* Sort_by / order_by */
        $order_by = 'DESC';

        if ($sort_by == 'recent' || $sort_by == '')
            $sort_by = 'MA.CreatedDate';

        $this->db->order_by($sort_by, $order_by);

        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset, $start_offset);

        $query = $this->db->get();
        $total_records = $tempdb->get()->num_rows();
        //echo $this->db->last_query();
        $results['total_records'] = $total_records;
        $results['results'] = $query->result_array();

        return $results;
    }

    /*
     * Function for get All abused media id's from table and return 
     * In the form of Comma seprated string
     */

    public function getAbusedMediaID() {
        //First Get all abused media ID's and then get result from media table
        $this->db->select('MediaID');
        $this->db->from(MEDIAABUSE);
        $this->db->group_by('MediaID');

        $query = $this->db->get();
        $results = $query->result_array();

        $ids = '';
        $i = 1;
        if (!empty($results)) {
            foreach ($results as $result) {
                if ($i == count($results))
                    $ids .= $result['MediaID'];
                else
                    $ids .= $result['MediaID'] . ',';

                $i++;
            }
        }
        return $ids;
    }

    /**
     * 
     * @param type $userId
     * @param type $approved
     * @return type
     */
    function getAbusedDeviceTypeCounts() {
        $ids = $this->getAbusedMediaID();
        if ($ids != '') {
            $this->db->select('COUNT(DeviceID) AS counts');
            $this->db->select('DeviceID');
            $this->db->select('DT.Name');

            $this->db->join(DEVICETYPES . ' AS DT', 'DT.DeviceTypeID = DeviceID', 'right');
            $this->db->from(MEDIA . ' AS M');

            $ids = explode(',', $ids);
            $this->db->where_in('M.MediaID', $ids);
            $this->db->group_by('DeviceID');

            $query = $this->db->get();
            return $query->result_array();
        }
    }

    /**
     * 
     * @param type $userId
     * @param type $approved
     * @return type
     */
    function getAbusedExtensionCount() {
        $ids = $this->getAbusedMediaID();
        if ($ids != '') {
            $this->db->select('COUNT(M.MediaExtensionID) AS counts');
            $this->db->select('M.MediaExtensionID');
            $this->db->select('ME.Name');

            $this->db->join(MEDIAEXTENSIONS . ' AS ME', 'ME.MediaExtensionID = M.MediaExtensionID', 'right');
            $this->db->from(MEDIA . ' AS M');

            $ids = explode(',', $ids);
            $this->db->where_in('M.MediaID', $ids);
            $this->db->group_by('M.MediaExtensionID');

            $query = $this->db->get();
            return $query->result_array();
        }
    }

    /**
     * 
     * @param type $userId
     * @param type $approved
     * @return type
     */
    function getAbusedSectionCount() {
        $ids = $this->getAbusedMediaID();
        if ($ids != '') {
            $this->db->select('COUNT(M.MediaSectionID) AS counts');
            $this->db->select('M.MediaSectionID');
            $this->db->select('MS.Name');

            $this->db->join(MEDIASECTIONS . ' AS MS', 'MS.MediaSectionID = M.MediaSectionID', 'right');
            $this->db->from(MEDIA . ' AS M');

            $ids = explode(',', $ids);
            $this->db->where_in('M.MediaID', $ids);
            $this->db->group_by('M.MediaSectionID');

            $query = $this->db->get();
            return $query->result_array();
        }
    }

    /**
     * 
     * @param type $userId
     * @param type $approved
     * @return type
     */
    function getAbusedSizeCount() {
        $ids = $this->getAbusedMediaID();
        if ($ids != '') {
            $this->db->select('COUNT(M.MediaSizeID) AS counts');
            $this->db->select('M.MediaSizeID');
            $this->db->select('MS.Name');

            $this->db->join(MEDIASIZES . ' AS MS', 'MS.MediaSizeID = M.MediaSizeID', 'right');
            $this->db->from(MEDIA . ' AS M');

            $ids = explode(',', $ids);
            $this->db->where_in('M.MediaID', $ids);
            $this->db->group_by('M.MediaSizeID');

            $query = $this->db->get();
            return $query->result_array();
        }
    }

    /**
     * 
     * @param type $userId
     * @param type $approved
     * @return type
     */
    function getAbusedSourceCount() {
        $ids = $this->getAbusedMediaID();
        if ($ids != '') {
            $this->db->select('COUNT(M.SourceID) AS counts');
            $this->db->select('M.SourceID');
            $this->db->select('S.Name');

            $this->db->join(SOURCES . ' AS S', 'S.SourceID = M.SourceID', 'right');
            $this->db->from(MEDIA . ' AS M');

            $ids = explode(',', $ids);
            $this->db->where_in('M.MediaID', $ids);
            $this->db->group_by('M.SourceID');

            $query = $this->db->get();
            return $query->result_array();
        }
    }
    
    function resetMediaCounts(){
        
        //For Update media device count
        $this->db->select('M.DeviceID as DeviceTypeID',FALSE);
        $this->db->select('COUNT(CASE WHEN IsAdminApproved = 1 then 1 ELSE NULL END) as ApproveCount', FALSE);
        $this->db->select('COUNT(CASE WHEN IsAdminApproved = 0 then 1 ELSE NULL END) as YetToApproveCount', FALSE);
        $this->db->from(MEDIA."  M ");
        $this->db->where('M.StatusID != ', '3');
        $this->db->group_by('M.DeviceID');        
        $device_query = $this->db->get();
        $deviceArray = $device_query->result_array();
        
        $this->db->truncate(MEDIADEVICECOUNTS);//For Truncate table data
        $this->db->insert_batch(MEDIADEVICECOUNTS, $deviceArray);//For insert new updated count
        
        //For Media Extension Count
        $this->db->select('M.MediaExtensionID as MediaExtensionID',FALSE);
        $this->db->select('COUNT(CASE WHEN IsAdminApproved = 1 then 1 ELSE NULL END) as ApproveCount', FALSE);
        $this->db->select('COUNT(CASE WHEN IsAdminApproved = 0 then 1 ELSE NULL END) as YetToApproveCount', FALSE);
        $this->db->from(MEDIA."  M ");
        $this->db->where('M.StatusID != ', '3');
        $this->db->group_by('M.MediaExtensionID');        
        $extension_query = $this->db->get();
        $extensionArray = $extension_query->result_array();
        
        $this->db->truncate(MEDIAEXTENSIONCOUNT);//For Truncate table data
        $this->db->insert_batch(MEDIAEXTENSIONCOUNT, $extensionArray);//For insert new updated count
        
        //For Update Media Section Count
        $this->db->select('M.MediaSectionID as MediaSectionID',FALSE);
        $this->db->select('COUNT(CASE WHEN IsAdminApproved = 1 then 1 ELSE NULL END) as ApproveCount', FALSE);
        $this->db->select('COUNT(CASE WHEN IsAdminApproved = 0 then 1 ELSE NULL END) as YetToApproveCount', FALSE);
        $this->db->from(MEDIA."  M ");
        $this->db->where('M.StatusID != ', '3');
        $this->db->group_by('M.MediaSectionID');        
        $section_query = $this->db->get();
        $sectionArray = $section_query->result_array();
        
        $this->db->truncate(MEDIASECTIONCOUNT);//For Truncate table data
        $this->db->insert_batch(MEDIASECTIONCOUNT, $sectionArray);//For insert new updated count
        
        //For Update Media Size Counts
        $this->db->select('MS.MediaSizeID as MediaSizeID',FALSE);
        $this->db->select('COUNT(CASE WHEN IsAdminApproved = 1 then 1 ELSE NULL END) as ApproveCount', FALSE);
        $this->db->select('COUNT(CASE WHEN IsAdminApproved = 0 then 1 ELSE NULL END) as YetToApproveCount', FALSE);
        $this->db->from(MEDIA."  M ");
        $this->db->join(MEDIASIZES." AS MS", ' MS.MaxSize >= ((M.Size)/(1024)) and MS.MinSize <= ((M.Size)/(1024)) ','inner');
        $this->db->where('M.StatusID != ', '3');
        $this->db->group_by('MS.MediaSizeID');        
        $size_query = $this->db->get();
        $sizeArray = $size_query->result_array();
        
        $this->db->truncate(MEDIASIZECOUNTS);//For Truncate table data
        $this->db->insert_batch(MEDIASIZECOUNTS, $sizeArray);//For insert new updated count
        
        
        //For Update Media Source Count
        $this->db->select('M.SourceID as SourceID',FALSE);
        $this->db->select('COUNT(CASE WHEN IsAdminApproved = 1 then 1 ELSE NULL END) as ApproveCount', FALSE);
        $this->db->select('COUNT(CASE WHEN IsAdminApproved = 0 then 1 ELSE NULL END) as YetToApproveCount', FALSE);
        $this->db->from(MEDIA."  M ");
        $this->db->where('M.StatusID != ', '3');
        $this->db->group_by('M.SourceID');        
        $source_query = $this->db->get();
        $sourceArray = $source_query->result_array();
        
        $this->db->truncate(MEDIASOURCECOUNT);//For Truncate table data
        $this->db->insert_batch(MEDIASOURCECOUNT, $sourceArray);//For insert new updated count
        
    }

    /**
     * Function to get media count
     * @author     Anoop Singh
     * Parameters : [$paramArray('DeviceID','SourceID','MediaExtensionID','MediaSectionID','MediaSizeID','userId')],[$updateFlag]
     * Return : array(admin_approved,admin_yet_to_approve,admin_approved_for_device,admin_yet_to_approve_for_device,admin_approved_for_source,admin_yet_to_approve_for_source,admin_approved_for_mediaext,admin_yet_to_approve_for_mediaext,admin_approved_for_media_sec_ref,admin_yet_to_approve_for_media_sec_ref,admin_approved_for_media_size,admin_yet_to_approve_for_media_size)
     * (index of return array depends on the passes parameters)
     */
    function checkMediaCounts($paramArray = array(), $updateFlag = false) {
        $where = 'StatusID != 3';

        $selectFromArray = array();
        $whereFromArray = array();

        $select = 'COUNT(CASE WHEN IsAdminApproved = 1 then 1 ELSE NULL END) as admin_approved, COUNT(CASE WHEN IsAdminApproved = 0 then 1 ELSE NULL END) as admin_yet_to_approve';


        if (isset($paramArray['DeviceID'])) {
            $selectFromArray['selectForDeviceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND DeviceID=' . $paramArray['DeviceID'] . ') then 1 ELSE NULL END) as admin_approved_for_device, COUNT(CASE WHEN (IsAdminApproved = 0 AND DeviceID=' . $paramArray['DeviceID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_device';
            $whereFromArray['DeviceID'] = $paramArray['DeviceID'];

            /* filter by user */
            if (isset($paramArray['userId'])) {
                $selectFromArray['filterForDeviceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND DeviceID=' . $paramArray['DeviceID'] . ' AND UserID=' . $paramArray['userId'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_device, COUNT(CASE WHEN (IsAdminApproved = 0 AND DeviceID=' . $paramArray['DeviceID'] . ' AND UserID=' . $paramArray['userId'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_device';
            }
            /* end filter by user */
        }

        if (isset($paramArray['SourceID'])) {
            $selectFromArray['selectForSourceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND SourceID=' . $paramArray['SourceID'] . ') then 1 ELSE NULL END) as admin_approved_for_source, COUNT(CASE WHEN (IsAdminApproved = 0 AND SourceID=' . $paramArray['SourceID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_source';
            $whereFromArray['SourceID'] = $paramArray['SourceID'];

            /* filter by user */
            if (isset($paramArray['userId'])) {
                $selectFromArray['filterForSourceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND SourceID=' . $paramArray['SourceID'] . ' AND UserID=' . $paramArray['userId'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_source, COUNT(CASE WHEN (IsAdminApproved = 0 AND SourceID=' . $paramArray['SourceID'] . ' AND UserID=' . $paramArray['userId'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_source';
            }
            /* end filter by user */
        }

        if (isset($paramArray['MediaExtensionID'])) {
            $selectFromArray['selectForMediaExtensionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaExtensionID=' . $paramArray['MediaExtensionID'] . ') then 1 ELSE NULL END) as admin_approved_for_mediaext, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaExtensionID=' . $paramArray['MediaExtensionID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_mediaext';
            $whereFromArray['MediaExtensionID'] = $paramArray['MediaExtensionID'];

            /* filter by user */
            if (isset($paramArray['userId'])) {
                $selectFromArray['filterForMediaExtensionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaExtensionID=' . $paramArray['MediaExtensionID'] . ' AND UserID=' . $paramArray['userId'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_mediaext, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaExtensionID=' . $paramArray['MediaExtensionID'] . ' AND UserID=' . $paramArray['userId'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_mediaext';
            }
            /* end filter by user */
        }

        if (isset($paramArray['MediaSectionID'])) {
            $selectFromArray['selectForMediaSectionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSectionID=' . $paramArray['MediaSectionID'] . ') then 1 ELSE NULL END) as admin_approved_for_media_sec_ref, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSectionID=' . $paramArray['MediaSectionID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_media_sec_ref';
            $whereFromArray['MediaSectionID'] = $paramArray['MediaSectionID'];

            /* filter by user */
            if (isset($paramArray['userId'])) {
                $selectFromArray['filterForMediaSectionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSectionID=' . $paramArray['MediaSectionID'] . ' AND UserID=' . $paramArray['userId'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_media_sec_ref, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSectionID=' . $paramArray['MediaSectionID'] . ' AND UserID=' . $paramArray['userId'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_media_sec_ref';
            }
            /* end filter by user */
        }

        if (isset($paramArray['MediaSizeID'])) {
            $selectFromArray['selectForMediaSizeID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSizeID=' . $paramArray['MediaSizeID'] . ') then 1 ELSE NULL END) as admin_approved_for_media_size, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSizeID=' . $paramArray['MediaSizeID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_media_size';
            $whereFromArray['MediaSizeID'] = $paramArray['MediaSizeID'];

            /* filter by user */
            if (isset($paramArray['userId'])) {
                $selectFromArray['filterForMediaSizeID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSizeID=' . $paramArray['MediaSizeID'] . ' AND UserID=' . $paramArray['userId'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_media_size, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSizeID=' . $paramArray['MediaSizeID'] . ' AND UserID=' . $paramArray['userId'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_media_size';
            }
            /* end filter by user */
        }


        if (!empty($selectFromArray)) {
            $select = '';
            foreach ($selectFromArray as $key => $val) {
                if ($select != '')
                    $select .= ', ';

                $select .= $val;
            }

            if (!empty($whereFromArray)) {
                $tempWhere = '';
                foreach ($whereFromArray as $key => $val) {
                    if ($tempWhere != '')
                        $tempWhere .= ' OR ';

                    $tempWhere .= $key . '=' . $val;
                }
                $where .= ' AND (' . $tempWhere . ')';
            }
        }


        $this->db->select($select);
        $this->db->from(MEDIA);

        if (!empty($where)) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        $dataArray = $query->row_array();


        /* update media count */
        if ($updateFlag) {
            foreach ($paramArray as $key => $val) {

                if ($key == 'DeviceID') {/* update MediaDeviceCounts */
                    $insertUpdateArray = array(
                        'table' => MEDIADEVICECOUNTS,
                        'where' => array('colName' => 'DeviceTypeID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_device'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_device'])
                    );
                    $this->insertUpdate($insertUpdateArray);
                } elseif ($key == 'SourceID') {/* update MediaSourceCount */
                    $insertUpdateArray = array(
                        'table' => MEDIASOURCECOUNT,
                        'where' => array('colName' => 'SourceID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_source'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_source'])
                    );
                    $this->insertUpdate($insertUpdateArray);
                } elseif ($key == 'MediaExtensionID') {/* update MediaExtensionCount */
                    $insertUpdateArray = array(
                        'table' => MEDIAEXTENSIONCOUNT,
                        'where' => array('colName' => 'MediaExtensionID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_mediaext'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_mediaext'])
                    );
                    $this->insertUpdate($insertUpdateArray);
                } elseif ($key == 'MediaSectionID') {/* update MediaSectionCount */
                    $insertUpdateArray = array(
                        'table' => MEDIASECTIONCOUNT,
                        'where' => array('colName' => 'MediaSectionID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_media_sec_ref'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_media_sec_ref'])
                    );
                    $this->insertUpdate($insertUpdateArray);
                } elseif ($key == 'MediaSizeID') {/* update MediaSizeCounts */
                    $insertUpdateArray = array(
                        'table' => MEDIASIZECOUNTS,
                        'where' => array('colName' => 'MediaSizeID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_media_size'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_media_size'])
                    );
                    $this->insertUpdate($insertUpdateArray);
                }
            }
        }
        /* update media count */


        /* put user filter data in main parameter */
        if (isset($paramArray['userId']) && isset($paramArray['DeviceID'])) {
            $dataArray['admin_approved_for_device'] = $dataArray['filter_admin_approved_for_device'];
            $dataArray['admin_yet_to_approve_for_device'] = $dataArray['filter_admin_yet_to_approve_for_device'];
        }

        if (isset($paramArray['userId']) && isset($paramArray['SourceID'])) {
            $dataArray['admin_approved_for_source'] = $dataArray['filter_admin_approved_for_source'];
            $dataArray['admin_yet_to_approve_for_source'] = $dataArray['filter_admin_yet_to_approve_for_source'];
        }

        if (isset($paramArray['userId']) && isset($paramArray['MediaExtensionID'])) {
            $dataArray['admin_approved_for_mediaext'] = $dataArray['filter_admin_approved_for_mediaext'];
            $dataArray['admin_yet_to_approve_for_mediaext'] = $dataArray['filter_admin_yet_to_approve_for_mediaext'];
        }

        if (isset($paramArray['userId']) && isset($paramArray['MediaSectionID'])) {
            $dataArray['admin_approved_for_media_sec_ref'] = $dataArray['filter_admin_approved_for_media_sec_ref'];
            $dataArray['admin_yet_to_approve_for_media_sec_ref'] = $dataArray['filter_admin_yet_to_approve_for_media_sec_ref'];
        }

        if (isset($paramArray['userId']) && isset($paramArray['MediaSizeID'])) {
            $dataArray['admin_approved_for_media_size'] = $dataArray['filter_admin_approved_for_media_size'];
            $dataArray['admin_yet_to_approve_for_media_size'] = $dataArray['filter_admin_yet_to_approve_for_media_size'];
        }

        /* end put user filter data in main parameter */

        return $dataArray;
        //echo $this->db->last_query().'<br>';
        //print_r($dataArray);die;
    }

    /**
     * Function to insert/update
     * @author     Anoop Singh
     * Parameters : $Data['table','where'=>array('colName','val'),'data'=>array()]
     * Return : 
     */
    function insertUpdate($data = array()) {
        $this->db->select($data['where']['colName']);
        $this->db->where(array($data['where']['colName'] => $data['where']['val']));
        $this->db->from($data['table']);
        $this->db->limit(1);
        $query = $this->db->get();
        $resArray = $query->row_array();
        if (!empty($resArray)) {/* update */
            $this->db->where(array($data['where']['colName'] => $data['where']['val']));
            $this->db->update($data['table'], $data['data']);
        } else {/* insert */
            $insertArray = array_merge($data['data'], array($data['where']['colName'] => $data['where']['val']));
            $this->db->insert($data['table'], $insertArray);
        }
    }

    /**
     * Function to getMediaDetails
     * @author     Anoop Singh
     * Parameters : MediaIDs
     * Return : 
     */
    function getMediaDetails($MediaIDs = array()) {
        $this->db->select(array('MediaID', 'MediaSectionID', 'DeviceID', 'SourceID', 'MediaExtensionID', 'MediaSizeID'));
        $mediaIdIn = implode(',', $MediaIDs);
        $this->db->where('MediaID IN(' . $mediaIdIn . ')');
        $this->db->from(MEDIA);
        $query = $this->db->get();
        $resArray = $query->result_array();

        return $resArray;
    }

    /* function getMediaCount(){
      $sql = "SELECT TypeName,ApproveCount,YetToApproveCount,TypeID FROM (
      SELECT 'DeviceTypeID' as TypeName,ApproveCount,YetToApproveCount,DeviceTypeID as TypeID FROM MediaDeviceCounts
      UNION
      SELECT 'MediaExtensionID' as TypeName,ApproveCount,YetToApproveCount,MediaExtensionID as TypeID FROM MediaExtensionCount
      UNION
      SELECT 'MediaSectionID' as TypeName,ApproveCount,YetToApproveCount,MediaSectionID as TypeID FROM MediaSectionCount
      UNION
      SELECT 'MediaSizeID' as TypeName,ApproveCount,YetToApproveCount,MediaSizeID as TypeID FROM MediaSizeCounts
      UNION
      SELECT 'SourceID' as TypeName,ApproveCount,YetToApproveCount,SourceID as TypeID FROM MediaSourceCount
      ) tbl where TypeID is not null";
      $result = $this->db->query($sql);
      if($result->num_rows()){
      return $result->result_array();
      }


      } */
    
    /**
     * Function for get section name by section id
     * Parameters : $secion_id
     * Return : string
     */
    public function getMediaSectionNameById($secion_id)
    {
        $this->db->select('MediaSectionAlias');
        $this->db->from(MEDIASECTIONS);
        $this->db->where('MediaSectionID',$secion_id);
        $query = $this->db->get();
        $result = $query->row_array();
        return $result['MediaSectionAlias'];
    }
    
    
    /*
     * Function for delete media from media table and add in delete media table
     * Parameters : $deleteMediaArr
     * Return : boolean
     */
    function deleteMediaFiles($deleteMediaArr){
        $rtn = 0;
        if($deleteMediaArr && !empty($deleteMediaArr)){
            $this->db->insert_batch(DELETEDMEDIA,$deleteMediaArr);
            $rtn = 1;
        }
        return $rtn;
    }
    /*function deleteMediaFiles($user_id,$StatusID,$type,$currentMedia){
        $SectionID = $this->getSectionID($type);
        $data = $this->db->get_where(MEDIA,array('MediaSectionID'=>$SectionID,'UserID'=>$user_id));
        if($data->num_rows()){
            $m = array();
            foreach($data->result() as $val){
                if(!$this->isAlreadyDeleted($val->MediaID) && $val->MediaID!=$currentMedia){
                    $Media['StatusID'] = $StatusID;
                    $Media['MediaID'] = $val->MediaID;
                    $Media['CreatedDate'] = $val->CreatedDate;
                    $Media['DeletedDate'] = date('Y-m-d H:i:s');
                    $m[] = $Media;
                }
            }
            if($m){
                $this->db->insert_batch(DELETEDMEDIA,$m);
            }
        }
    }*/
    
    function check_and_update_activity($activity_ids)
    {
        if($activity_ids)
        {
            foreach($activity_ids as $activity_id)
            {
                $this->db->select('PostContent');
                $this->db->from(ACTIVITY);
                $this->db->where('ActivityID',$activity_id);
                $this->db->where('StatusID','2');
                $query = $this->db->get();
                if($query->num_rows())
                {
                    $row = $query->row();
                    if(!$row->PostContent)
                    {
                        $this->db->select('MediaID');
                        $this->db->from(MEDIA);
                        $this->db->where('MediaSectionID','3');
                        $this->db->where('MediaSectionReferenceID',$activity_id);
                        $this->db->where('StatusID','2');
                        $q = $this->db->get();
                        if(!$q->num_rows())
                        {
                            $this->db->set('StatusID','3');
                            $this->db->where('ActivityID',$activity_id);
                            $this->db->update(ACTIVITY);
                        }
                    }
                }
            }
        }
    }

    /*
     * Function for get media details by media id
     * Parameters : $MediaID
     * Return : array
     */
    function getMediaDetailById($MediaID){
        $this->db->select('*');
        $this->db->from(MEDIA);
        $this->db->where('MediaID',$MediaID);
        $query = $this->db->get();
        
        return $query->row_array();
    }

    function isAlreadyDeleted($MediaID){
        $query = $this->db->get_where(DELETEDMEDIA,array('MediaID'=>$MediaID,'StatusID'=>'3'));
        if($query->num_rows()){
            return true;
        } else {
            return false;
        }
    } 
    
    /**
     * Function for get media for Media lising for user
     * Parameters : ustart_offset, end_offset, dataArr
     * Return : array
     */
    public function getUserMedia($start_offset = 0, $end_offset = "",$dataArr = array()) {
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);

        $this->db->select('M.MediaID', FALSE);
        $this->db->select('M.ImageName', FALSE);
        $this->db->select('M.StatusID', FALSE);
        $this->db->select('M.MediaSectionID', FALSE);
        $this->db->select('M.DeviceID', FALSE);
        $this->db->select('M.SourceID', FALSE);
        $this->db->select('M.MediaExtensionID', FALSE);
        $this->db->select('M.AbuseCount', FALSE);
        $this->db->select('M.IsAdminApproved', FALSE);
        $this->db->select('M.Size', FALSE);
        $this->db->select('M.MediaSizeID', FALSE);
        $this->db->select('M.ImageUrl', FALSE);

        $this->db->select('S.StatusName', FALSE);
        $this->db->select('MS.Name as MediaSection', FALSE);
        $this->db->select('MS.MediaSectionAlias', FALSE);
        $this->db->select('ME.Name as MediaExtension', FALSE);
        $this->db->select('ME.MediaTypeId', FALSE);
        $this->db->select('MT.Name as MediaType', FALSE);

        $this->db->select('DATE_FORMAT(M.CreatedDate, "' . $mysql_date . '") AS CreatedDate', FALSE);
        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS UserName', FALSE);
        $this->db->select('U.UserGUID');

        $this->db->join(STATUS . " AS S", ' S.StatusID = M.StatusID', 'right');
        $this->db->join(MEDIASECTIONS . " AS MS", ' MS.MediaSectionID = M.MediaSectionID', 'right');
        $this->db->join(MEDIAEXTENSIONS . " AS ME", ' ME.MediaExtensionID = M.MediaExtensionID', 'right');
        $this->db->join(MEDIATYPES . " AS MT", ' MT.MediaTypeId = ME.MediaTypeId', 'right');
        $this->db->join(USERS . " AS U", ' U.UserID = M.userID', 'right');
        $this->db->from(MEDIA . " AS M");

        $this->db->where('M.StatusID != ', 3);
        $this->db->where('M.IsAdminApproved ', 0);

        if(isset($dataArr['mediaSectionId']) && $dataArr['mediaSectionId'] != 0){
            $this->db->where('M.MediaSectionID = ', $dataArr['mediaSectionId']);
        }
        
        if(isset($dataArr['mediaSourceId']) && $dataArr['mediaSourceId'] != 0){
            $this->db->where('M.SourceID = ', $dataArr['mediaSourceId']);
        }
        
        if(isset($dataArr['mediaTypeId']) && $dataArr['mediaTypeId'] != 0){
            $this->db->where('MT.MediaTypeID = ', $dataArr['mediaTypeId']);
        }
        
        if(isset($dataArr['userId']) && $dataArr['userId'] != 0){
            $this->db->where('M.UserID = ', $dataArr['userId']);
        }
        
        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $results['total_records'] = $tempdb->count_all_results();

        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset, $start_offset);
        
        $query = $this->db->get();
        //echo $this->db->last_query();
        $results['results'] = $query->result_array();
        return $results;
    }
    
    /**
     * Function for check Media already mark abuse by user
     * @param integer $MediaID
     * @param integer $user_id
     * @return string
     */
    function checkMediaAlreadyMarkAbuseByUser($MediaID,$user_id){
        
        $this->db->select('*');
        $this->db->from(MEDIAABUSE);
        $this->db->where('MediaID',$MediaID);
        $this->db->where('UserID',$user_id);
                
        $query = $this->db->get();
        if ($query->num_rows()) {
            $return = 'reported';
        } else {
            $return = 'notreported';
        }
        
        return $return;
    } 
        
    /**
     * Function for mark media as abuse from frontend website
     * @param array $dataArr
     * @return integer
     */
    function markMediaAsAbuse($dataArr){
        
        $this->db->insert(MEDIAABUSE, $dataArr);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    } 
    
    /**
     * Function for update media abuse count when user mark media as abuse
     * @param integer $MediaID
     * @return integer
     */
    function updateMediaAbuseCount($MediaID){
        
        $this->db->set('AbuseCount', 'AbuseCount+1',FALSE);
        $this->db->where('MediaID', $MediaID);
        $this->db->update(MEDIA);
        return $this->db->affected_rows();
    } 
    
    /**
     * Function to get default media settings, media section and user list
     * @author     Anoop Singh
     * Parameters : array('mediaType'=>Image/Video)
     * Return : array
     */
    function getMediaParamData($param = array('mediaType' => 'Image')) {
        /* get media section */
        $this->db->select('*');
        $this->db->from(MEDIASECTIONS);
        $query = $this->db->get();
        $param['media_section'] = $query->result_array();
        
        /* get media types */
        $this->db->select('*');
        $this->db->from(MEDIATYPES);
        $media_type_query = $this->db->get();
        $param['media_type'] = $media_type_query->result_array();
        
        /* get media types */
        $this->db->select('*');
        $this->db->from(SOURCES);
        $source_query = $this->db->get();
        $param['media_source'] = $source_query->result_array();
        
        /* get user list */
        $user_status = 2;
        $sort_by = 'FirstName';
        $order_by = 'ASC';
        $this->db->select('UserID, FirstName, LastName');
        $this->db->from(USERS);
        $this->db->where('StatusID', $user_status);
        $this->db->order_by($sort_by, $order_by);
        $users_query = $this->db->get();
        $tempUserArr = $users_query->result_array();
        
        foreach($tempUserArr as $user){
            $user['FirstName'] = stripslashes($user['FirstName']);
            $user['LastName'] = stripslashes($user['LastName']);
            $param['users'][] = $user;
        }
        
        return $param;
    }
    
    /*
     * Function for get abused media details by media id
     * Parameters : $MediaID
     * Return : array
     */
    function getAbusedMediaDetailById($MediaID){
        
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        
        $this->db->select('M.MediaID', FALSE);
        $this->db->select('M.ImageName', FALSE);
        $this->db->select('M.StatusID', FALSE);
        $this->db->select('M.ImageUrl', FALSE);
        $this->db->select('COUNT(MediaAbuseID) AbuseCount');
        $this->db->select('MS.Name as MediaSection', FALSE);
        $this->db->select('MS.MediaSectionAlias', FALSE);

        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS UserName', FALSE);
        $this->db->select('U.UserGUID');
        $this->db->select('DATE_FORMAT(U.CreatedDate, "'.$mysql_date.'") AS membersince', FALSE);
        $this->db->select('U.ProfilePicture AS profilepicture', FALSE);
        
        $this->db->join(MEDIASECTIONS . " AS MS", ' MS.MediaSectionID = M.MediaSectionID', 'right');
        $this->db->join(MEDIAABUSE . " AS MA", ' MA.MediaID = M.MediaID')->group_by('M.MediaID');
        $this->db->join(USERS . " AS U", ' U.UserID = M.userID', 'right');
        $this->db->from(MEDIA . " AS M ");
        
        $this->db->where('M.MediaID',$MediaID);
        $query = $this->db->get();
        
        return $query->row_array();
    }   
    
    /*
     * Function for get abused media comments by media id
     * Parameters : $MediaID
     * Return : array
     */
    function getAbusedMediaCommnetsById($MediaID){
        
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $mysql_date = $mysql_date.' %h:%i %p';
        
        $this->db->select('MA.MediaAbuseID', FALSE);
        $this->db->select('MA.UserID', FALSE);
        $this->db->select('MA.MediaID', FALSE);
        $this->db->select('MA.Description', FALSE);
        $this->db->select('DATE_FORMAT(MA.CreatedDate, "'.$mysql_date.'") AS CreatedDate', FALSE);

        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS UserName', FALSE);
        $this->db->select('U.UserGUID');        
        $this->db->select('U.ProfilePicture AS profilepicture', FALSE);

        $this->db->join(USERS . " AS U", ' U.UserID = MA.UserID', 'right');
        $this->db->from(MEDIAABUSE . " AS MA ");
        
        $this->db->where('MA.MediaID',$MediaID);
        $query = $this->db->get();
        
        return $resArray = $query->result_array();        
    }
    
    
    /**
     * Function for get media for Media lising for user
     * Parameters : user_id, start_offset, end_offset, adminApproved, search_keyword, sort_by, order_by
     * Return : array
     */
    public function getMediaAnalytics($start_offset=0, $end_offset="", $start_date="", $end_date="", $search_keyword="", $sort_by="", $order_by="")
    {
        
        $this->db->select('U.UserID AS userid', FALSE);
        $this->db->select('U.FirstName AS firstname', FALSE);
        $this->db->select('U.LastName AS lastname', FALSE);
        $this->db->select('CONCAT(U.FirstName, " ", U.LastName) AS username', FALSE);
        $this->db->select('U.ProfilePicture AS profilepicture', FALSE);
        $this->db->select('U.Location AS location', FALSE);
        $this->db->select('U.UserGUID AS userguid', FALSE);
        $this->db->select('U.StatusID AS statusid', FALSE);
        
        $this->db->select('SUM(M.Size) as size', FALSE);
        $this->db->select('SUM(CASE M.AbuseCount WHEN 0 THEN 0 ELSE 1 END ) AS flagged', FALSE);
        $this->db->select('SUM(CASE M.StatusID WHEN 3 THEN 1 ELSE 0 END) AS deleted', FALSE);
        $this->db->select('COUNT(M.MediaID) AS uploaded', FALSE);
        
        /* $sub = $this->subquery->start_subquery('select');
        $sub->select('GROUP_CONCAT(UR.RoleID)',FALSE)->from(USERROLES.' AS UR');
        $sub->where('UR.UserID = U.UserID');
        $this->subquery->end_subquery('userroleid');
         * 
         */
        
        $this->db->join(MEDIA . " AS M", ' M.UserID = U.userID', 'inner');
        $this->db->from(USERS . " AS U");
        $this->db->where("U.StatusID != 3");
                
        /* start_date, end_date for filters */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(M.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'"', NULL, FALSE);
        }
        
        if(isset($search_keyword) && $search_keyword !=''){
            $this->db->like('CONCAT(U.FirstName, U.LastName)',$search_keyword);
        }

        $this->db->group_by('U.UserID');
        
        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Sort_by, Order_by */
        if($sort_by == 'username' || $sort_by == '' )
           $sort_by='FirstName';

        if($order_by == false || $order_by == '' )
           $order_by='ASC';

        if($order_by == 'true')
           $order_by = 'DESC';

        $this->db->order_by($sort_by, $order_by);

        /* Start_offset, end_offset */
        if(isset($start_offset) && $end_offset !=''){
             $this->db->limit($end_offset,$start_offset);
        }

        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results['results'] = $query->result_array();
        return $results;
    }
    
     /**
     * Function for get media analytics report
     * Parameters : $start_date, $end_date
     * Return : array
     */
    public function getMediaAnalyticsReport($start_date="", $end_date="")
    {
        
        $this->db->select('COUNT(M.MediaID) as total_media', FALSE);
        $this->db->select('SUM(M.Size) as total_size', FALSE);
        
        // ME.MediaTypeID == 1 For Image and ME.MediaTypeID == 2 For Video from MEDIAEXTENSIONS Table
        $this->db->select('SUM(CASE ME.MediaTypeID WHEN 1 THEN 1 ELSE 0 END) AS picture_count', FALSE);
        $this->db->select('SUM(CASE ME.MediaTypeID WHEN 2 THEN 1 ELSE 0 END) AS video_count', FALSE);
        $this->db->select('SUM(CASE ME.MediaTypeID WHEN 1 THEN M.Size ELSE 0 END) AS picture_size', FALSE);
        $this->db->select('SUM(CASE ME.MediaTypeID WHEN 2 THEN M.Size ELSE 0 END) AS video_size', FALSE);
        
        $this->db->select('SUM(CASE M.AbuseCount WHEN 0 THEN 0 ELSE 1 END) AS abuse_count', FALSE);
        $this->db->select('SUM(CASE M.AbuseCount WHEN 0 THEN 0 ELSE M.Size END) AS abuse_size', FALSE);      
        
        // ME.MediaTypeID == 1 For Image and ME.MediaTypeID == 2 For Video from MEDIAEXTENSIONS Table and check in MEDIA Table
        // M.AbuseCount == 0 means not mark abuse by any user, otherwise count 1 for abuse count
        $this->db->select('SUM(CASE ME.MediaTypeID WHEN 1 THEN (CASE M.AbuseCount WHEN 0 THEN 0 ELSE 1 END) ELSE 0 END) AS abuse_picture_count', FALSE);
        $this->db->select('SUM(CASE ME.MediaTypeID WHEN 2 THEN (CASE M.AbuseCount WHEN 0 THEN 0 ELSE 1 END) ELSE 0 END) AS abuse_video_count', FALSE);
        $this->db->select('SUM(CASE ME.MediaTypeID WHEN 1 THEN (CASE M.AbuseCount WHEN 0 THEN 0 ELSE M.Size END) ELSE 0 END) AS abuse_picture_size', FALSE);
        $this->db->select('SUM(CASE ME.MediaTypeID WHEN 2 THEN (CASE M.AbuseCount WHEN 0 THEN 0 ELSE M.Size END) ELSE 0 END) AS abuse_video_size', FALSE);
        
        $this->db->join(MEDIAEXTENSIONS." AS ME", ' ME.MediaExtensionID = M.MediaExtensionID','inner');
        $this->db->join(USERS." AS U", ' U.UserID = M.UserID','inner');
        $this->db->from(MEDIA . " AS M");
        $this->db->where("U.StatusID != 3");
        /* start_date, end_date for filters */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(M.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'"', NULL, FALSE);
        }
        
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results = $query->row_array();
        return $results;
    }
    
    function updateMedia($Media, $entity_id, $user_id=0, $AlbumID=0, $deleteRemaining=false, $MediaSectionID=0,$status=FALSE){
        $update = array();
        //$AlbumID = 0;     
        if($Media){       

            $StatusID = 2;
            if($status==10)
            {
                $StatusID = 10;
            }  
            //$AlbumID = get_album_id($user_id, $AlbumName, $module_id, $module_entity_id);
            $Count = 0 ;
            $media_guids = array();
            foreach($Media as $m){                
                if(isset($m['MediaGUID']) && !empty($m['MediaGUID']))
                {
                    $media_guids[] = $m['MediaGUID'];
                    $u = array('UserID'=>$user_id,'MediaGUID'=>$m['MediaGUID'],'MediaSectionReferenceID'=>$entity_id,'StatusID'=>$StatusID,'Caption'=>isset($m['Caption']) ? $m['Caption'] : '','AlbumID'=> $AlbumID);
                    $update[] = $u;                    
                }
                ++$Count;               
            }
            
            if($deleteRemaining){   
                $this->db->set('StatusID','3');
                $this->db->where('MediaSectionID',$MediaSectionID);
                $this->db->where('MediaSectionReferenceID',$entity_id);
                $this->db->where_not_in('MediaGUID',$media_guids);
                $this->db->update(MEDIA);
            }
            
                     
            if(!empty($AlbumID) && $Count > 0) {
                $Album = get_detail_by_id($AlbumID, 13, "MediaID, AlbumName", 2);
                $cover_media_id   = $Album['MediaID'];
                $AlbumName      = $Album['AlbumName'];
                if(empty($cover_media_id) || $AlbumName==DEFAULT_PROFILE_ALBUM || $AlbumName==DEFAULT_PROFILECOVER_ALBUM || $AlbumName==DEFAULT_WALL_ALBUM) {
                    $index          = $Count - 1;
                    $MediaGUID      = $Media[$index]['MediaGUID'];
                    $media_details  = get_detail_by_guid($MediaGUID, 21, "MediaID, AlbumID", 2);
                    $cover_media_id   = $media_details['MediaID'];
                    if(!empty($media_details['AlbumID']))
                    {
                        --$Count;    
                    }
                    $this->db->set("MediaID", $cover_media_id, FALSE);
                } 
                
                $set_field = "MediaCount";
                $this->db->set($set_field, "$set_field+($Count)", FALSE);
                $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->where('AlbumID',$AlbumID);        
                $this->db->update(ALBUMS);
                //echo $this->db->last_query();die;
            } 

            $this->db->update_batch(MEDIA, $update, 'MediaGUID');           
        }
        return $AlbumID;
    }
    
}

//End of file media_model.php
