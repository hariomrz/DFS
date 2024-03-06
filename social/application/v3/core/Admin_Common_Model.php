<?php

class Admin_Common_Model extends CI_Model {

    public $_tablePrefix = "";
    public $suggested_users;
    public $IsApp=0;
    function __construct() {
        parent::__construct();
        $this->_tableprefix = $this->db->dbprefix;
    }

    /* common function used to get single row from any table
     * @param String $select
     * @param String $table
     * @param Array/String $where
     */

    function get_single_row($select = '*', $table, $where = "", $order_by = "") {
        $this->db->select($select);
        $this->db->from($table);
        if ($where != "") {
            $this->db->where($where);
        }
        
        if(isset($order_by) && $order_by != ""){
            $this->db->order_by($order_by,FALSE);
        }
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }    

    function insert($table_name, $data, $return = 'id') {
        $this->db->insert($table_name, $data);
        $id = $this->db->insert_id();
        return $id;
    }

    /**
     * This method updates fields in my table.
     * @param String $fieldName
     * @param String $value
     * @param Integer $id
     */
    function update_field($table = "", $fieldName, $fieldValue, $where = "") {
        if (empty($fieldName)) {
            log_message('error', 'Got empty fieldName: ' . $fieldName);
            return FALSE;
        } else if ($table == "") {
            log_message('error', 'Got empty table name');
            return FALSE;
        } else if ($where == "") {
            return false;
        } else {
            $this->db->where($where);
            $query = $this->db->update($table, array($fieldName => $fieldValue));
        }
    }

    /**
     * Updates whole row [unlike update_field()]
     * @param Array $data
     * @param Integer $id
     */
    function update($table_name = "", $data_array, $where = "") {
        if ($table_name && is_array($data_array)) {
            $columns = $this->getTableFields($table_name);
            foreach ($columns as $coloumn_data)
                $column_name[] = $coloumn_data['Field'];

            foreach ($data_array as $key => $val) {
                if (in_array(trim($key), $column_name)) {
                    $data[$key] = trim($val);
                }
            }
            if (!is_array($data)) {
                log_message('error', 'Supposed to get an array!');
                return FALSE;
            } else if ($table_name == "") {
                log_message('error', 'Got empty table name');
                return FALSE;
            } else if ($where == "") {
                return false;
            } else {
                $this->db->where($where);
                $this->db->update($table_name, $data);
            }
        }
    }

    /**
     * Delete row 
     * @param String $table
     * @param Array/String $where
     */
    function delete($table = "", $where = "") {

        if ($table == "") {
            log_message('error', 'Got empty table name');
            return FALSE;
        } else if ($where == "") {
            return false;
        } else {
            $this->db->where($where);
            $this->db->delete($table);
        }
    }

    function getTableFields($table_name) {
        $query = "SHOW COLUMNS FROM " . $this->db->dbprefix . "$table_name";
        $rs = $this->db->query($query);
        return $rs->result_array();
    }

    function remember_me() {
        if ($this->input->cookie('remember_me')) {
            $user_id = $this->input->cookie('remember_me');
            $result = $this->get_user_data($user_id);
            $user_session = array();
            if (!empty($result)) {
                if ($result['status'] == 0) {
                    $user_session = array('inactive_user_id' => $result['user_id'],
                        'inactive_name' => $result['name'],
                        'inactive_email' => $result['email']
                    );
                } else {

                    $user_session['user_id'] = $result['user_id'];
                    $user_session['unique_id'] = $result['unique_id'];
                    $user_session['avatar_id'] = $result['avatar_id'];
                    $user_session['name'] = $result['name'];
                    $user_session['email'] = $result['email'];
                    $user_session['image'] = $result['image'];
                }
                $this->session->set_userdata($user_session);
            }
        }
    }

    /*
      |--------------------------------------------------------------------------
      | Use to get error message by error code
      | @Inputs: errorcode
      |--------------------------------------------------------------------------
     */

    function getError($errorcode) {
        $row = array();
        /* Query to get ErrorCode Description - Starts */
        $data = array('ErrorCode' => $errorcode);
        $query = $this->db->limit(1)->get_where(ERRORCODES, $data, 1);
        /* Query to get ErrorCode Description - Ends */

        if ($query->num_rows() == 1) {
            $row = $query->row_array();
        } else {
            $row['Description'] = 'Invalid errorcode.';
        }
        return $row['Description'];
    }

    /*
      |--------------------------------------------------------------------------
      | Use get DeviceTypeID
      |@Inputs: (Defined in devicetypes DB Table)
      |--------------------------------------------------------------------------
     */

    function GetDeviceTypeID($DeviceType) {
        $DeviceTypeID = '';
        $this->db->select('DeviceTypeID');
        $this->db->where('Name', $DeviceType);
        $this->db->limit(1);
        $query = $this->db->get(DEVICETYPES);

        if ($query->num_rows() > 0) {
            $Data = $query->row_array();
            $DeviceTypeID = $Data['DeviceTypeID'];
        } else {
            $DeviceType = DEFAULT_DEVICE_TYPE;
            $this->db->select('DeviceTypeID');
            $this->db->where('Name', $DeviceType);
            $this->db->limit(1);
            $query = $this->db->get(DEVICETYPES);

            if ($query->num_rows() > 0) {
                $Data = $query->row_array();
                $DeviceTypeID = $Data['DeviceTypeID'];
            }
        }
        return $DeviceTypeID;
    }

    /*
      |--------------------------------------------------------------------------
      | Use get SourceID
      |@Inputs: (Defined in sources DB Table)
      |--------------------------------------------------------------------------
     */

    function GetSourceID($SocialType) {
        $SourceID = '';
        $this->db->select('SourceID');
        $this->db->where('Name', $SocialType);
        $this->db->limit(1);
        $query = $this->db->get(SOURCES);
        if ($query->num_rows() > 0) {
            $Data = $query->row_array();
            $SourceID = $Data['SourceID'];
        }
        return $SourceID;
    }

    /*
      |--------------------------------------------------------------------------
      | Use get Resolution
      |@Inputs: (Defined in resolution DB Table)
      |--------------------------------------------------------------------------
     */

    function GetResolutionID($Resolution) {
        $ResolutionID = '';
        $this->db->select('ResolutionID');
        $this->db->where('Name', $Resolution);
        $this->db->limit(1);
        $query = $this->db->get(RESOLUTION);
        if ($query->num_rows() > 0) {
            $Data = $query->row_array();
            $ResolutionID = $Data['ResolutionID'];
        } else {
            $Resolution = DEFAULT_RESOLUTION;
            $this->db->select('ResolutionID');
            $this->db->where('Name', $Resolution);
            $this->db->limit(1);
            $query = $this->db->get(RESOLUTION);
            if ($query->num_rows() > 0) {
                $Data = $query->row_array();
                $ResolutionID = $Data['ResolutionID'];
            }
        }
        return $ResolutionID;
    }

    function downgrade($data) {
        $sql = "update " . USERROLES . " set RoleID = " . $data['RoleId'] . "  where UserID = " . $data['UserId'] . "";
        $res = $this->db->query($sql);
        if ($res) {
            return 'true';
        } else {
            return 'false';
        }
    }

    function getWeekDayID($Day) {
        $this->db->where('Name', $Day);
        $this->db->limit(1);
        $Week = $this->db->get(WEEKDAYS);
        if ($Week->num_rows()) {
            $WeekID = $Week->row();
            return $WeekID->WeekdayID;
        } else {
            return false;
        }
    }

    function getTimeSlot() {
        $d = date('H i');
        $d = explode(" ", $d);
        $min = $d[1] / 60;
        $dec = $d[0] + $min;
        $query = $this->db->query("SELECT TimeSlotID FROM TimeSlots WHERE '" . $dec . "' BETWEEN ValueRangeFrom AND ValueRangeTo");

        if ($query->num_rows()) {
            return $query->row()->TimeSlotID;
        } else {
            return false;
        }
    }
    //method owner : trilok umath
    function getUserRoles($userID) {
        
        $query = $this->db->query("select GROUP_CONCAT(UR.RoleID) As ROLES from UserRoles as UR where UR.UserID = $userID");

        if ($query->num_rows()) {
            return $query->row()->ROLES;
        } else {
            return false;
        }
    }
    
    /**
    * [downloadExcelFile : used to download Excel File]
    * @param array $dataArr
    * @return array
    */
    function downloadExcelFile($dataArr) {
        //load our new PHPExcel library
        $this->load->library('excel');
        $letters = range('A', 'Z');

        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");
        $exportDate = date($global_settings['date_format'] . " " . $global_settings['time_format']);

        $headerArray = $dataArr['headerArray'];
        $sheetTitle = $dataArr['sheetTitle'];
        $fileName = $dataArr['fileName'];
        $folderPath = $dataArr['folderPath'];
        $inputData = $dataArr['inputData'];
        $ReportHeader = $dataArr['ReportHeader'];

        //activate worksheet number 1
        $this->excel->setActiveSheetIndex(0);
        //name the worksheet
        $this->excel->getActiveSheet()->setTitle($sheetTitle);

        // set xls header and style
        $cell_count = count($headerArray) - 1;
        $styleArray = array('font' => array('bold' => true, 'color' => array('rgb' => 'ffffff'), 'size' => 12));

        $col = 1;
        if ($ReportHeader) {
            //For first row header
            $this->excel->getActiveSheet()->getStyle('A1:' . $letters[$cell_count] . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $this->excel->getActiveSheet()->getStyle('A1:' . $letters[$cell_count] . '1')->getFill()->getStartColor()->setRGB('12456B');
            $this->excel->getActiveSheet()->getStyle('A1:G20')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $this->excel->setActiveSheetIndex(0)->mergeCells('A1:' . $letters[$cell_count] . '1');
            $this->excel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
            $this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
            $this->excel->getActiveSheet()->setCellValue('A1', $ReportHeader['ReportName'] . "(" . $ReportHeader['dateFilterText'] . ")");

            //For Report date header
            $this->excel->getActiveSheet()->getStyle('A2:' . $letters[$cell_count] . '2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $this->excel->getActiveSheet()->getStyle('A2:' . $letters[$cell_count] . '2')->getFill()->getStartColor()->setRGB('12456B');
            $this->excel->getActiveSheet()->getStyle('A2:G20')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $this->excel->setActiveSheetIndex(0)->mergeCells('A2:' . $letters[$cell_count] . '2');
            $this->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $this->excel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
            $this->excel->getActiveSheet()->setCellValue('A2', "Export Date : " . $exportDate);

            $col = 3;
        }



        //For Fields header
        $this->excel->getActiveSheet()->getStyle('A' . $col . ':' . $letters[$cell_count] . $col)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $this->excel->getActiveSheet()->getStyle('A' . $col . ':' . $letters[$cell_count] . $col)->getFill()->getStartColor()->setRGB('185C8F');
        $this->excel->getActiveSheet()->getStyle('A' . $col . ':G20')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $i = 0;
        foreach ($headerArray as $key => $value) {
            $cell_name = $letters[$i] . $col;
            $this->excel->getActiveSheet()->setCellValue($cell_name, $value);
            //change the font size
            $this->excel->getActiveSheet()->getStyle($cell_name)->applyFromArray($styleArray);
            $this->excel->getActiveSheet()->getRowDimension(3)->setRowHeight(25);

            //make the font become bold
            $this->excel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
            $this->excel->getActiveSheet()->getColumnDimension($letters[$i])->setAutoSize(true);
            $this->excel->getActiveSheet()->getStyle($cell_name)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $i++;
        }

        //Set dynamic data
        if (is_array($inputData) && !empty($inputData)) {
            // set xls body
            $j = 0;
            $col++;
            foreach ($inputData as $user) {
                $i = 0;
                foreach ($headerArray as $key => $value) {
                    $cell_name = $letters[$i];
                    $this->excel->getActiveSheet()->setCellValue($cell_name . ($j + $col), $user[$key]);
                    $this->excel->getActiveSheet()->getRowDimension($j + $col)->setRowHeight(18);
                    $i++;
                }
                $j++;
            }
        } else {
            $this->excel->getActiveSheet()->setCellValue('A' . $col, 'No Record Found.');
        }

        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="' . $fileName . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');

        $xls_filename = $folderPath . $fileName;
        //force user to download the Excel file without writing it to server's HD
        $objWriter->save($xls_filename);
        return true;
    }

    function format_dob($dob) {
        if (empty($dob)) {
            return $dob = '0000-00-00';
        }
        if (!empty($dob)) {
            $dob2 = explode('/', $dob);
            $dob = $dob2[2] . '-' . $dob2[0] . '-' . $dob2[1];
            return $dob;
        }                
    }
}
//End of file Admin_Common_model.php
