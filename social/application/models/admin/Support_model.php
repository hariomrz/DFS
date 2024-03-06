<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Support_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }
    
/**************** Admin section support functions **********************/
    /**
    * Function for get all support error logs listing
    * Parameters : start_offset, end_offset, start_date, end_date, error_status, sort_by, order_by
    * Return : errorl log array
    */
   public function getSupportErrorLogList($start_offset=0, $end_offset="", $start_date="", $end_date="", $error_status="", $sort_by="", $order_by="", $ErrorTypeId = 0, $search_keyword = ""){
       
       $this->db->select('EL.ErrorLogID AS ErrorLogID', FALSE);
       $this->db->select('EL.Title AS Title', FALSE);
       $this->db->select('EL.Source AS Source', FALSE);
       $this->db->select('EL.ErrorDescription AS ErrorDescription', FALSE);
       $this->db->select('EL.BrowserDetail AS BrowserDetail', FALSE);
       $this->db->select('EL.OperatingSystem AS OperatingSystem', FALSE);
       $this->db->select('EL.IPAddress AS IPAddress', FALSE);
       $this->db->select('EL.City AS City', FALSE);
       $this->db->select('EL.State AS State', FALSE);
       $this->db->select('EL.Country AS Country', FALSE);
       $this->db->select('EL.Reporter AS Reporter', FALSE);
       $this->db->select('EL.ReporterEmail AS ReporterEmail', FALSE);
       $this->db->select('EL.CreatedDate AS CreatedDate', FALSE);
       $this->db->select('EL.ModifiedDate AS ModifiedDate', FALSE);
       $this->db->select('EL.StatusID AS StatusID', FALSE);       
       $this->db->select('ET.ErrorTypeID AS ErrorTypeID', FALSE);
       $this->db->select('ET.Name AS ErrorType', FALSE);
       $this->db->select('COUNT(ELA.ErrorLogAttachmentID) AS ErrorFiles', FALSE);
       
       $this->db->join(ERRORLOGATTACHMENTS." AS ELA", ' ELA.ErrorLogID = EL.ErrorLogID','left');
       $this->db->join(ERRORTYPES." AS ET", ' ET.ErrorTypeID = EL.ErrorTypeID','inner');
       $this->db->from(ERRORLOGS."  EL ");

       if(isset($search_keyword) && $search_keyword !=''){
            $this->db->like('EL.Title',$search_keyword);
        }

       /* start_date, end_date for filters */
       if(isset($start_date) && $end_date !='')
       {
           $start_date = date("Y-m-d", strtotime($start_date));
           $end_date = date("Y-m-d", strtotime($end_date));

           $this->db->where('DATE(EL.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'"', NULL, FALSE);
       }

       if(isset($error_status) && $error_status !=''){
           $this->db->where('EL.StatusID',$error_status);
       }
       
       if(isset($ErrorTypeId) && $ErrorTypeId != "0"){
           $this->db->where('EL.ErrorTypeID', $ErrorTypeId);
       }
       
       $this->db->group_by('EL.ErrorLogID');

       //Here we clone the DB object for get all Count rows
       $tempdb = clone $this->db;
       $temp_q = $tempdb->get();
       $results['total_records'] = $temp_q->num_rows();

       /* Sort_by, Order_by */
       if($sort_by == 'CreatedDate' || $sort_by == '' )
          $sort_by='ErrorLogID';

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
       $results['results'] = $query->result_array();
       return $results;
   }
   
   /*
     * Function for get error log details by error log id
     * Parameters : $ErrorLogID
     * Return : array
     */
    function getErrorLogDetailById($ErrorLogID){
        
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        
        $this->db->select('EL.ErrorLogID AS ErrorLogID', FALSE);
        $this->db->select('EL.Title AS Title', FALSE);
        $this->db->select('EL.Source AS Source', FALSE);
        $this->db->select('EL.ErrorDescription AS ErrorDescription', FALSE);
        $this->db->select('EL.BrowserDetail AS BrowserDetail', FALSE);
        $this->db->select('EL.OperatingSystem AS OperatingSystem', FALSE);
        $this->db->select('EL.IPAddress AS IPAddress', FALSE);
        $this->db->select('EL.City AS City', FALSE);
        $this->db->select('EL.State AS State', FALSE);
        $this->db->select('EL.Country AS Country', FALSE);
        $this->db->select('EL.Reporter AS Reporter', FALSE);
        $this->db->select('EL.ReporterEmail AS ReporterEmail', FALSE);
        $this->db->select('DATE_FORMAT(EL.CreatedDate, "'.$mysql_date.'") AS CreatedDate', FALSE);
        $this->db->select('EL.StatusID AS StatusID', FALSE);       
        $this->db->select('ET.ErrorTypeID AS ErrorTypeID', FALSE);
        $this->db->select('ET.Name AS ErrorType', FALSE);
        $this->db->select('EL.QueryTime AS QueryTime', FALSE);
        $this->db->select('GROUP_CONCAT(ELA.Path SEPARATOR ",") as Path',FALSE);
       
        $this->db->join(ERRORLOGATTACHMENTS." AS ELA", ' ELA.ErrorLogID = EL.ErrorLogID','left');
        $this->db->join(ERRORTYPES." AS ET", ' ET.ErrorTypeID = EL.ErrorTypeID','inner');
        $this->db->from(ERRORLOGS."  EL ");
                
        $this->db->where('EL.ErrorLogID',$ErrorLogID);
        $query = $this->db->get();
        
        return $query->row_array();
    }
   
   /**
     * Update error log(s) detail
     * @param type $data
     * @param type $key
     */
    function updateBatchErrorLogInfo($data, $key) {
        $this->db->update_batch(ERRORLOGS, $data, $key);
    }
    
    
/************ Frontend Support section Functions ******************/        
    /**
     * Function to get media path
     * Parameters : void
     * Return : support media path
     */
    function getSupportImageServerPath() {
        return ROOT_FOLDER . '/upload/support/';
    }
    

    /**
     * @Summary: This function is used for upload images.
     * @access: public
     * @param: param['imageServerPath',thumbInfoArray['pathInfo','width','height'],thumbnailQuality,dpi], 
     * @return: array('image_uri','image_name','success','size','thumbSizeTotal','image_url')
     */
    function uploadImage($param = array()) {

        $thumbInfoArray = array();

        if (isset($param['imageServerPath']))
            $imageServerPath = $param['imageServerPath'];
        
        $file_name = time() . uniqid();
        $name_parts = pathinfo($_FILES['qqfile']['name']);
        $ext = $name_parts['extension'];
        $file_name .= "." . $ext;
        $file_name = str_replace(" ", "_", $file_name);
        $temp_file = $_FILES['qqfile']['tmp_name'];
        $file_type = $_FILES['qqfile']['type'];
        $size = $_FILES['qqfile']['size'];
        $image_path = DOC_PATH . $imageServerPath . $file_name;
        
        $dpi = '';
        if (isset($param['dpi'])) {
            $dpi = $param['dpi'];
        }
        
        $data = array('image_uri' => $imageServerPath . $file_name, 'image_name' => $file_name, 'success' => 0, 'size' => $size, 'image_url' => base_url() . $imageServerPath . $file_name);
        
        if (!is_dir('phpthumb\cache')) {
            //mkdir('phpthumb\cache', '0777');
        }

        if (strtolower(IMAGE_SERVER) == 'remote' && $param['IMAGE_SERVER'] != 'local') {//if upload on s3 is enabled
            $image_path_remote = str_replace(DOC_PATH . ROOT_FOLDER . '/', '', $image_path);
            $imageServerPathRemote = str_replace(ROOT_FOLDER . '/', '', $imageServerPath);
            //instantiate the class
            $s3 = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY);
            $is_s3_upload = $s3->putObjectFile($temp_file, BUCKET, $image_path_remote, S3::ACL_PUBLIC_READ, array(), $file_type);

            if ($is_s3_upload) {
                $data['success'] = 1;            
            }
        }else {
            if (@copy($temp_file, $image_path)) {
                $data['success'] = 1;
            }
        }

        return $data;
    }
    
    /**
     * Function Name: getUserLocationDetails
     * @param IPAddress
     * Description: Get location related data IPAdress,CityID  
     */
    function getUserLocationDetails($IPAddress){
        $locationDetails['CityName'] = '';
        $locationDetails['StateName'] = '';
        $locationDetails['CountryName'] = '';
                
        if($IPAddress=='')
            $IPAddress = getRealIpAddr();
        
        $locationData = array('statusCode' => '');
        
        if($IPAddress != '')
        {
          $this->load->helper('location');
          $locationData = get_ip_location_details($IPAddress);
        }
        
        if($locationData['statusCode'] == "OK") {
            $locationDetails['CityName'] = $locationData['CityName'];
            $locationDetails['StateName'] = $locationData['StateName'];
            $locationDetails['CountryName'] = $locationData['CountryName'];
        }
        
        $locationDetails['IPAddress']   = $IPAddress;
        return $locationDetails;
    }
    
    /**
     * Function for create support ticket setting
     * @param array $dataArr
     * @return integer
     */
    function createSupportTicket($dataArr){
        
        $this->db->insert(ERRORLOGS, $dataArr);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
    
    /**
     * Function for save support ticket files
     * @param array $dataArr
     * @return integer
     */
    function saveBatchSupportTicketFiles($dataArr){
        $rtn = 0;
        if(!empty($dataArr)){
            $this->db->insert_batch(ERRORLOGATTACHMENTS, $dataArr);      
            $rtn = 1;
        }
        return $rtn;
    }
    

}

//End of file support_model.php
