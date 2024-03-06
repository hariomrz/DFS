<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of commission_model
 *
 * @author nitins
 */
class Category_model extends Admin_Common_Model
{

    public function __construct()
    {
        parent::__construct();
        
        
        // Set exclude modules
        $this->exclude_modules = [0];
        $check_modules = [1, 18, 14, 29, 31, 33, 34];
        
        foreach($check_modules as $check_module) {
            if ($this->settings_model->isDisabled($check_module)) {
                $this->exclude_modules[] = $check_module;
            }
        }
        
    }

    /**
     * Function for get commissions detail for Users listing
     * Parameters : start_offset, end_offset, start_date, end_date, user_status, search_keyword, sort_by, order_by
     * Return : Users array
     */
    public function get_list($start_offset = 0, $end_offset = "", $start_date = "", $end_date = "", $module_id = array(), $search_keyword = "", $sort_by = "", $order_by = "", $locality_id = "")
    {
        /* Load Global settings */
        //$global_settings = $this->config->item("global_settings");
        $this->db->select('C.CategoryID as category_id,C.ModuleID');
        $this->db->select('C.Name AS name', FALSE);
        $this->db->select('C.Description AS description', FALSE);
        $this->db->select('C.Icon AS icon', FALSE);
        $this->db->select('IF(MD.ImageName="" || MD.ImageName IS NULL || MD.ImageName=0,"",MD.ImageName) as ImageName', FALSE);
        $this->db->select('MD.MediaGUID AS MediaGUID', FALSE);
        $this->db->select('M.ModuleName', FALSE);
        
        $this->db->select('IFNULL(C.Address,"") as Address', FALSE);
        $this->db->select('IFNULL(C.Mobile,"") as Mobile', FALSE);
        $this->db->select('IFNULL(C.OwnerName,"") as OwnerName', FALSE);
        $this->db->select('IFNULL(C.Miscellaneous,"") as Miscellaneous', FALSE);
        $this->db->select('IFNULL(C.LocalityID,"") as LocalityID', FALSE);
        
        $this->db->join(MEDIA . ' MD', 'MD.MediaID = C.MediaID', 'LEFT');
        $this->db->join(MODULES . ' M', 'M.ModuleID = C.ModuleID', 'LEFT');

        $this->db->from(CATEGORYMASTER . "  C");

        $this->db->select('P.CategoryID as parent_id');
        $this->db->select('P.Name AS parent_name', FALSE);
        $this->db->join(CATEGORYMASTER . ' P', 'P.CategoryID=C.ParentID', 'LEFT');

        $this->db->select('S.StatusName as status, S.StatusID as status_id');
        $this->db->join(STATUS . ' S', 'S.StatusID=C.StatusID');
        if (!empty($search_keyword))
        {
            $this->db->like('C.Name', $search_keyword);
        }

        //$this->db->where('C.StatusID !=', 3);
        $this->db->where_not_in('C.StatusID', array('3','20'));
        if (!empty($module_id))
        {
            if (is_array($module_id))
            {
                $this->db->where_in('C.ModuleID', $module_id);    
            }
            else
            {
                $this->db->where('C.ModuleID', $module_id);                
            }
        }

        if (!empty($locality_id))
        {
            $this->db->where('C.LocalityID', $locality_id);
        }
        
        // Exclude disabled moduels data.
        $this->db->where_not_in('C.ModuleID', $this->exclude_modules);
        
        
        
        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Sort_by, Order_by */
        if ($sort_by == 'Name' || $sort_by == '')
            $sort_by = 'Name';

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
        //echo $this->db->last_query();


        $results['results'] = $query->result_array();
        // foreach ($results['results'] as $key => $value) {
        //     $value['media'][] = $this->get_cat_media($value['category_id']);            
        // }
        return $results;
    }

    /**
     * Save new category
     * @param type $data
     * @return type
     */
    public function save_category($data,$user_id=0)
    {

        $result = true;
        if (isset($data['CategoryID']) && !empty($data['CategoryID']))
        {
            // delete cache for fetching sub-category ids
            if (CACHE_ENABLE) {
               $this->cache->delete('api_subCategory'.$data['CategoryID']);
               if(isset($data['ParentID']) && $data['ParentID'] != '0'){
                    $this->cache->delete('api_subCategory'.$data['ParentID']);
               }
            }

            $category_data = $this->get_old_category($data['CategoryID']);
            $this->db->where('CategoryID', $data['CategoryID']);
            $this->db->update(CATEGORYMASTER, $data);
            $result = $data['CategoryID'];
            if (!empty($category_data['Users']))
            {
                if ($data['Name'] != $category_data['CategoryName'] || $data['ParentID'] != $category_data['ParentCategoryID'])
                {
                    $NotificationTypeID = 108;
                    if ($category_data['ParentCategoryID'])
                    {
                        $NotificationTypeID = 109;
                    }
                    $parameters[0]['ReferenceID'] = $data['CategoryID'];
                    $parameters[0]['Type'] = 'Category';
                    $change_category_data['OldData'] = array('CategoryName' => $category_data['CategoryName'], 'ParentCategorName' => $category_data['ParentCategorName']);
                    $parameters[0]['CategoryData'] = $change_category_data;
                    $this->notification_model->add_notification($NotificationTypeID, $user_id, $category_data['Users'], $data['CategoryID'], $parameters);
                }
            }
        }
        else
        {
            if (isset($data['CategoryID']))
            {
                unset($data['CategoryID']);
            }
            $this->db->insert(CATEGORYMASTER, $data);
            $result = $this->db->insert_id();
        }
        if (CACHE_ENABLE) {
           $this->cache->delete('category_'.$data['ModuleID'].'_'.$data['ParentID']);
           $this->cache->delete('category');
           $this->cache->delete('api_category_'.$data['ModuleID'].'_'.$data['ParentID']);
        }
        return $result;
    }

    /**
     * 
     */
    public function update_status($category_id, $status_id)
    {
        $this->db->where('CategoryID', $category_id);
        $this->db->update(CATEGORYMASTER, array('StatusID' => $status_id));
        if (CACHE_ENABLE) {
           $this->cache->delete('category');
           
           $this->db->select(" CM.ParentID, CM.ModuleID", false);
           $this->db->from(CATEGORYMASTER . " AS CM ");
           $this->db->where("CM.CategoryID ", $category_id);
           $query = $this->db->get();
           $results = $query->row_array();
           $this->cache->delete('api_category_'.$results['ModuleID'].'_'.$results['ParentID']);
        }
    }

    public function get_category_dropdown($module_id, $category_id = "", $locality_id = "", $parent_id = 0, $multiplier = 0)
    {
        $this->db->select('C.CategoryID as category_id');
        $this->db->select('C.Address as address');
        $this->db->select('C.Mobile as phone_number');
        $this->db->select('C.LocalityID as locality_id');
        $this->db->select('C.OwnerName as owner');
        $this->db->select('C.Miscellaneous as miscellaneous');
        $this->db->select('C.Name AS name', FALSE);
        $this->db->select('C.Description AS description', FALSE);
        $this->db->select('C.Icon AS icon', FALSE);
        $this->db->from(CATEGORYMASTER . "  C");
        $this->db->select('P.CategoryID as parent_id');
        $this->db->select('P.Name AS parent_name', FALSE);
        $this->db->join(CATEGORYMASTER . ' P', 'P.CategoryID=C.ParentID', 'LEFT');
        $this->db->select('S.StatusName as status, S.StatusID as status_id');
        $this->db->join(STATUS . ' S', 'S.StatusID=C.StatusID');
        $this->db->where('C.StatusID !=', 3);
        // $this->db->where_in('C.ModuleID', array(1,3,14));
        $this->db->where('C.ParentID', $parent_id);
        if (!empty($module_id))
        {
            $this->db->where('C.ModuleID', $module_id);
        }
        if (!empty($category_id))
        {
            $this->db->where('C.CategoryID !=', $category_id);
            $this->db->where('C.ParentID !=', $category_id);
        }
        
        // Exclude disabled moduels data.
        $this->db->where_not_in('C.ModuleID', $this->exclude_modules);

        $query = $this->db->get();
        $results = $query->result_array();
        $list = array();
        if ($multiplier == 0)
        {
            $list = array(array('category_id' => '0', 'name' => lang('SelectParentCategory')));
        }
        foreach ($results as $result)
        {
            $result['name'] = str_repeat(' ', $multiplier) . (($multiplier > 0) ? '--' : '') . $result['name'];
            $list[] = $result; //print_r($result);
            //$child = $this->get_category_dropdown($module_id, $category_id, $result['category_id'], $multiplier + 1);
            if (!empty($child))
            {
                $list = array_merge($list, $child); //print_r($list);die;
            }
        }
        return $list;
    }

    function get_old_category($CategoryID)
    {

        $this->db->select(" IFNULL(CM.Name,'') as CategoryName,IFNULL(CM1.Name,'') as ParentCategorName,IFNULL(CM1.CategoryID,'0') as ParentCategoryID,IFNULL(CM.CategoryID,'0') as CategoryID", false);

        $this->db->from(CATEGORYMASTER . " AS CM ");
        $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left");

        $this->db->where("CM.ModuleID ", 29);
        $this->db->where("CM.CategoryID ", $CategoryID);
        $query = $this->db->get();
        $results = $query->row_array();
        $results['Users'] = array();
        if (isset($results['CategoryID']))
        {
            $this->db->select("ES.ModuleEntityID");
            $this->db->from(ENTITYCATEGORY . " AS EC ");
            $this->db->join(ENTITYSKILLS . " AS ES", "EC.ModuleEntityID=ES.SkillID AND ES.ModuleID=3");
            $this->db->where("EC.ModuleID ", 29);
            $this->db->where("EC.CategoryID ", $CategoryID);
            $this->db->group_by('ES.ModuleEntityID');
            $query_user = $this->db->get();

            if ($query_user->num_rows())
            {
                $results_user = $query_user->result_array();
                foreach ($results_user as $item)
                {
                    $results['Users'][] = $item['ModuleEntityID'];
                }
            }
        }
        return $results;
    }
    
    
    /**
     * [run_uploaded_profile check for all valid entries in file and register users for right entry]
     * @return [json] [success / error message and response code]
     */
    public function run_uploaded_profile($filename, $module_id, $locality_id) {
        $return['ResponseCode'] = 200;
        $return['Message'] = lang('success');
        $current_user_id = isset($this->UserID) ? $this->UserID : 0;
        $return['Data'] = array();
        //Check if file headers are valid        
        $header_validation = $this->check_file_headers($filename['upload_data']['full_path']);
        if (!$header_validation['Status']) {
            $return['Error'] = (isset($header_validation['ErrorMessages']) && !empty($header_validation['ErrorMessages'])) ? $header_validation['ErrorMessages'] : array("Invalid File Format");
            return $return;
        }
        $filename = $filename['upload_data']['file_name'];
        $file_name = $filename;
        $filename = PATH_IMG_UPLOAD_FOLDER . $filename; //$Data['filename']
        //check if the file exists or not        
        if (file_exists($filename)) {
            $file_data = $this->get_file_data($filename);
            $errorReport = array();
            $required_fields = $overwrite_data = $rows_updated = $missing_rows = $parameters = $errors = array();
            $mandatory_check = $employeeid_array = $emails_array = array();
            $unique_field_error = array('C' => array('value' => 'Email', 'unique' => 1));
            try {
                if (!empty($file_data['values'])) {

                    $insertingRecords = 0;
                    $updatingRecords = 0;

                    $actual_row_index = 2; //this value will decide if any row is missing
                    $excel_errors_fixes = [];


                    foreach ($file_data['values'] as $row => $values) {
                        $row_validation_arr = $this->validate_file_row($row, $values, $actual_row_index, $file_data, $errorReport);                        
                        if ($row_validation_arr['is_error']) {
                            $excel_errors_fixes[] = $row_validation_arr['error'];
                            continue;
                        }

                        $isCategoryExists = $this->isCategoryExists($row_validation_arr['returingValues'], $module_id, $locality_id);

                        if ($isCategoryExists) {
                            $updatingRecords++;
                        } else {
                            $insertingRecords++;
                        }
                    }
                    
                    //initiate_worker_job('add_category_from_excel_job', array('file_name' => $file_name, 'current_user_id' => $current_user_id));                    
                    $this->add_category_from_excel_job($file_name, $module_id, $locality_id);
                    
                    $response_msg = "";
                    $return['ResponseCode'] = 200;
                    $return['excel_errors_fixes'] = $excel_errors_fixes;
                    $return['Message'] = "Upload category is in progress! $insertingRecords will be inserted."; // You will be notified by email after completion
                }
            } catch (Exception $e) {
                $return['Error'] = array($e->getMessage());
                $return['ResponseCode'] = 412;
                ;
            }
            //create Reply for error
        } else {
            $return['ResponseCode'] = 404;
            $return['Message'] = "File does not exists";
        }
        /* Final Output */
        return $return; //$this->response($return);
    }

    /**
     * [check_file_headers check file's format with specified format given in sample and returns true if file is perfect]
     * @return [json] [success / error message and response code]
     */
    public function check_file_headers($file) {
        $sourceFile = ROOT_PATH . '/upload/csv_file/CategoryDataSampleFormat.xls';
        $baseFile = $this->get_file_data($sourceFile);
        $userFile = $this->get_file_data($file);

        $diff = array_diff_assoc($baseFile['header'][1], $userFile['header'][1]);
        $header_validation = $this->validate_file_header_format($userFile['header']);
        if (count($baseFile['header']) == count($userFile['header']) && empty($diff) && !$header_validation['Status']) {
            return array('Status' => true);
        } else if (isset($header_validation['ErrorMessages']) && !empty($header_validation['ErrorMessages'])) {
            return array('Status' => false, 'ErrorMessages' => $header_validation['ErrorMessages']);
        }
        return array('Status' => false);
    }

    /**
     * [get_file_data returns file's data]
     * @return [json] [success / error message and response code]
     */
    public function get_file_data($file) {
        $this->load->library('excel');
        //read file from path
        $objPHPExcel = PHPExcel_IOFactory::load($file);
        //get only the Cell Collection
        $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
        //extract to a PHP readable array format
        foreach ($cell_collection as $cell) {
            $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
            $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
            if ($row > 1 && in_array($column, array('E'))) { //array('K','O')
                //$data_value = PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell($cell)->getValue());
                $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getFormattedValue();
            } else {
                $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
            }
            //header will/should be in row 1 only. of course this can be modified to suit your need.
            if ($row == 1) {
                $header[$row][$column] = $data_value;
            } else {
                $arr_data[$row][$column] = $data_value;
            }
        }
        //send the data in an array format
        $data['header'] = isset($header) ? $header : array();
        $data['values'] = isset($arr_data) ? $arr_data : array();
        // echo '<pre>';print_r($data);die;
        return $data;
    }
    
        /**
     * [validate_file_header_format
     * @return [json] [success / error message and response code]
     */
    public function validate_file_header_format($fileHeaders) {
        //check if the file headers are in correct format 
        $error = FALSE;
        $error_msg = array();
        foreach ($fileHeaders[1] as $key => $value) {
            switch ($key) {
                case 'A' : //Category
                    if ($value != 'Category') {
                        $error = true;
                        $error_msg[] = "Row 1 Col A- $value is an invalid header";
                    }
                    break;
                case 'B' : //SubCategory 
                    if ($value != 'SubCategory') {
                        $error = true;
                        $error_msg[] = "Row 1 Col B- $value is an invalid header";
                    }
                    break;
                case 'C' : //OwnerName
                    if ($value != 'OwnerName') {
                        $error = true;
                        $error_msg[] = "Row 1 Col C- $value is an invalid header";
                    }
                    break;
                case 'D' : //Address
                    if ($value != 'Address') {
                        $error = true;
                        $error_msg[] = "Row 1 Col D- $value is an invalid header";
                    }
                    break;
                case 'E' : //Phone
                    if ($value != 'Phone') {
                        $error = true;
                        $error_msg[] = "Row 1 Col E- $value is an invalid header";
                    }
                    break;
                case 'F' : //Miscellaneous
                    if ($value != 'Miscellaneous') {
                        $error = true;
                        $error_msg[] = "Row 1 Col F- $value is an invalid header";
                    }
                    break;
                case 'G' : //Description
                    if ($value != 'Description') {
                        $error = true;
                        $error_msg[] = "Row 1 Col G- $value is an invalid header";
                    }
                    break;
                    break;
            }
        }

        return array('Status' => $error, 'ErrorMessages' => $error_msg);
    }
    
     public function validate_file_row($row, $values, $actual_row_index, $file_data, $errorReport) {

        $errors = [];

        $mandatory_check[$row] = array(
            'A' => array('value' => 'Category', 'exists' => 0, 'required' => 1),
            'B' => array('value' => 'SubCategory', 'exists' => 0, 'required' => 1),
            'C' => array('value' => 'OwnerName', 'exists' => 0, 'required' => 0),
            'D' => array('value' => 'Address', 'exists' => 0, 'required' => 0),
            'E' => array('value' => 'Phone', 'exists' => 0, 'required' => 0),
            'F' => array('value' => 'Miscellaneous', 'exists' => 0, 'required' => 0),
            'G' => array('value' => 'Description', 'exists' => 0, 'required' => 0)
        );

        //try validation on each field
        foreach ($values as $key => $value) {
            switch ($key) {
                case 'A' : //Category
                    if (trim($file_data['header'][1][$key]) == 'Category' && $value != '') {
                        $mandatory_check[$row][$key]['exists'] = 1;                        
                        $parameters[$row]['Category'] = $value;
                    }
                    break;
                case 'B' : //Sub Category
                    if (trim($file_data['header'][1][$key]) == 'SubCategory' && $value != '') {
                        $mandatory_check[$row][$key]['exists'] = 1;
                        $parameters[$row]['SubCategory'] = $value;
                    }
                    break;
                case 'C' : //Owner Name
                    $parameters[$row]['OwnerName'] = $value;
                    break;
                case 'D' : //Address
                    $parameters[$row]['Address'] = $value;
                    break;
                case 'E' : //Phone
                    if (trim($file_data['header'][1][$key]) == 'Phone' && $value != '') {
                        $parameters[$row]['Phone'] = $value;
                        
                       /* $flag = (bool) preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $value);
                        if(!$flag || strlen($value)!=10) {
                            $errors[] = "Row $row Col $key- Not a valid phone number.";
                        }
                        * 
                        */
                    }
                    break;
                case 'F' : //Miscellaneous
                    $parameters[$row]['Miscellaneous'] = $value;
                    break;
                case 'G' : //Description
                    $parameters[$row]['Description'] = $value;
                    break;
                default :
                    //default condition
                    break;
            }
            //echo '<br>'.$key . '=>' . $value;                    
        }
        //check if the row is missing
        if ($actual_row_index != $row) {
            $missing_rows[] = $actual_row_index;
            $actual_row_index = $row;
        }
        $actual_row_index++;
        $is_row_deleted = 1;
        foreach ($mandatory_check[$row] as $k => $v) {
            if ($v['required'] == 1 && $v['exists'] == 0) {
                $required_fields[$row] = $v;
                // $return['Error']['requiredFieldsError'][$row] = $v['value']." is mandatory in row $row";
                $mandatory_err = "Row $row Col $k- " . $v['value'] . " is mandatory";
                array_unshift($errors, $mandatory_err);
            }
            if (isset($return['Error']['duplicateRecordError'][$row])) {
                $return['Data']['duplicateRecordData'][$row] = $parameters[$row];
            }
        }

        //delete record of row having any kind of specified error
        foreach ($mandatory_check[$row] as $k => $v) {
            //find data to overwrite
            if (isset($errorReport[$row][$k]['NotUnique']) && $errorReport[$row][$k]['NotUnique'] == 1) {
                $overwrite_data[$row] = $parameters[$row];
            }
            //find fields which failed required validation
            if ($v['required'] == 1 && $v['exists'] == 0) {
                //fields need to be filled in the excel
                //$required_fields[$row][$k] = $v;
            }
            //delete all data from list having any kind of error
            if (isset($errorReport[$row]) || ($v['required'] == 1 && $v['exists'] == 0)) {
                if(isset($parameters)) { unset($parameters[$row]); }
                $is_row_deleted = 0;
                break;
            }
        }


        $returingValues = [];
        
        //try validation on each field
        foreach ($values as $key => $value) {
            switch ($key) {
                case 'A' : //Category
                    if (trim($file_data['header'][1][$key]) == 'Category' && $value != '') {
                        $returingValues['Category'] = $value;
                    }
                    break;
                case 'B' : //Sub Category
                    if (trim($file_data['header'][1][$key]) == 'SubCategory' && $value != '') {
                        $returingValues['SubCategory'] = $value;
                    }
                    break;
                case 'C' : //Owner Name
                    $returingValues['OwnerName'] = $value;
                    break;
                case 'D' : //Address
                    $returingValues['Address'] = $value;
                    break;
                case 'E' : //Phone
                    $returingValues['Phone'] = $value;
                    break;
                case 'F' : //Miscellaneous
                    $returingValues['Miscellaneous'] = $value;
                    break;
                case 'G' : //State
                    $returingValues['Description'] = $value;
                    break;
                default :
                    //default condition
                    break;
            }
        }


        if (count($errors)) {
            
        }

        return array(
            'is_error' => count($errors),
            'error' => $errors,
            'values' => $values,
            'returingValues' => $returingValues
        );
    }
    
    public function isCategoryExists($params, $module_id, $locality_id, $is_check_parent_only=false, $parent_category_id=0) {
        
        $parent_category_name = trim($params['Category']);
        $sub_category_name = trim($params['SubCategory']);
        
        if($parent_category_id) {
            $this->db->select('CategoryID');
            $this->db->from(CATEGORYMASTER);
            $this->db->where('ModuleID', $module_id);
            $this->db->where('LocalityID', $locality_id);
            $this->db->where('ParentID', $parent_category_id);
            $this->db->where('LOWER(Name)', strtolower($sub_category_name),NULL,FALSE);
            $query = $this->db->get();
            if($query->num_rows() > 0) {
                $sub_query_data = $query->row_array();
                $sub_category_id = $sub_query_data['CategoryID'];
                return $sub_category_id;
            } 
            return 0;
        }
        
        $this->db->select('CategoryID');
        $this->db->from(CATEGORYMASTER);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('LocalityID', $locality_id);
        $this->db->where('ParentID', 0);
        $this->db->where('LOWER(Name)', strtolower($parent_category_name),NULL,FALSE);
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $query_data = $query->row_array();
            $parent_category_id = $query_data['CategoryID'];
            
            if($is_check_parent_only) {
                return $parent_category_id;
            }
            $this->db->select('CategoryID');
            $this->db->from(CATEGORYMASTER);
            $this->db->where('ModuleID', $module_id);
            $this->db->where('LocalityID', $locality_id);
            $this->db->where('ParentID', $parent_category_id);
            $this->db->where('LOWER(Name)', strtolower($sub_category_name),NULL,FALSE);
            $query = $this->db->get();
            if($query->num_rows() > 0) {
                $sub_query_data = $query->row_array();
                $sub_category_id = $sub_query_data['CategoryID'];
                return $sub_category_id;
            }        
        }         
        return 0;
    }
    
    /**
     * Add category, uploaded by Admin from Excel sheet (this will work in background job)   
     * @param [String] $filename [file path]
     * @return Boolean [True/False] 
     */
    public function add_category_from_excel_job($filename, $module_id, $locality_id) {
        
        if (file_exists(PATH_IMG_UPLOAD_FOLDER . $filename)) {
            $file_data = $this->get_file_data(PATH_IMG_UPLOAD_FOLDER . $filename);
            $error_in_rows = array();
            $parameters = array();
            $errorReport = [];
            $actual_row_index = 2; //this value will decide if any row is missing
            
            try {
                if (!empty($file_data['values'])) {
                    foreach ($file_data['values'] as $row => $values) {
                                                
                        $row_validation_arr = $this->validate_file_row($row, $values, $actual_row_index, $file_data, $errorReport);
                        
                        if($row_validation_arr['is_error']) {
                            continue;
                        }
                        
                        $is_new_added = $this->add_category_from_excel($row_validation_arr['returingValues'], $module_id, $locality_id);
                    }                    
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                log_message('error', $error);
            }
            //create Reply for error
        } else {
            log_message('error', 'File does not exist: ' . PATH_IMG_UPLOAD_FOLDER . $filename);
        }
    }
    
     /**
     * Function to get the requiered value from the required table 
     * @param array $params [insert data]
     * @return array $result [return resulting array with new insertID]
     */
    public function add_category_from_excel($params, $module_id, $locality_id) {

        $new_added = false;
        
        $parent_category_id = $this->isCategoryExists($params, $module_id, $locality_id, true);

        $parent_category_name = trim($params['Category']);
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        
        if(empty($parent_category_id)){
            $category_data = array(                   
                    'ModuleID' => $module_id,
                    'ParentID' => 0,
                    'Name' => $parent_category_name,
                    'Description' => '',
                    'Icon' => '',
                    'LocalityID' => $locality_id,
                    'Address' => '',
                    'Mobile' => '',
                    'OwnerName' => '',
                    'Miscellaneous' => '',
                    'CreatedDate' => $current_date,
                    'ModifiedDate' => $current_date,
                    'StatusID' => 2
                );
            $this->db->insert(CATEGORYMASTER, $category_data);
            $parent_category_id = $this->db->insert_id();
        }
        
        $sub_category_id = $this->isCategoryExists($params, $module_id, $locality_id, false, $parent_category_id);
        
        $sub_category_data['Name'] = isset($params['SubCategory']) ? trim($params['SubCategory']) : '';
        $sub_category_data['OwnerName'] = isset($params['OwnerName']) ? trim($params['OwnerName']) : '';
        $sub_category_data['Description'] = isset($params['Description']) ? trim($params['Description']) : '';
        $sub_category_data['Address'] = isset($params['Address']) ? trim($params['Address']) : '';
        $sub_category_data['Miscellaneous'] = isset($params['Miscellaneous']) ? trim($params['Miscellaneous']) : '';
        $sub_category_data['Mobile'] = isset($params['Phone']) ? trim($params['Phone']) : '';
        $sub_category_data['ParentID'] = $parent_category_id;
        $sub_category_data['ModuleID'] = $module_id;
        $sub_category_data['LocalityID'] = $locality_id;
        $sub_category_data['CreatedDate'] = $current_date;
        $sub_category_data['ModifiedDate'] = $current_date;
        $sub_category_data['StatusID'] = 2;
        $sub_category_data['Icon'] = '';
        
       // print_r($sub_category_data);
        // Update subscriber details
        if (!empty($sub_category_id)) {
            $this->db->where('CategoryID', $sub_category_id);
            $this->db->update(CATEGORYMASTER, $sub_category_data);
        } else { // Insert subscriber
            $this->db->insert(CATEGORYMASTER, $sub_category_data);
            $new_added = true;
        }
        return $new_added;
    }

    public function update_category_media($categoryID, $media = array())
    {
        if (!empty($categoryID))
        {
            $this->db->where('MediaSectionID', 10);
            $this->db->where('MediaSectionReferenceID', $categoryID);
            $this->db->set('StatusID', 3);
            $this->db->update(MEDIA);
        }
        if (!empty($media))
        {
            foreach ($media as $key => $value)
            {
                $this->db->where('MediaGUID', $value['MediaGUID']);
                $this->db->set('ModuleID', 27);
                $this->db->set('ModuleEntityID', $categoryID);
                $this->db->set('MediaSectionReferenceID', $categoryID);
                $this->db->set('StatusID', 2);
                $this->db->update(MEDIA);
            }
        }
    }

    public function get_cat_media($CategoryID)
    {
            $this->db->select('MediaGUID, ImageName');
            $this->db->where('MediaSectionID', 10);
            $this->db->where('MediaSectionReferenceID', $CategoryID);
            $this->db->where('StatusID', 2);
            $result = $this->db->get(MEDIA)->result_array();
            return $result;
    }

}
