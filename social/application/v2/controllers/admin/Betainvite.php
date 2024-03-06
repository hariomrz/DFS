<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All Betainvite related views rendering functions
 * @package    BetaInvite
 * @author     Girish Patidar : 11-03-2015
 * @version    1.0
 */

class Betainvite extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/users_model');
                
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
    }

    /**
     * Function for show Beta Invite Users Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index() {
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('beta_invite_invited_users'), getUserRightsData($this->DeviceType)) && !in_array(getRightsId('beta_invite_not_joined_yet'), getUserRightsData($this->DeviceType)) && !in_array(getRightsId('beta_invite_deleted_users'), getUserRightsData($this->DeviceType)) && !in_array(getRightsId('beta_invite_removed_access_users'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }

        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/betainvite/betainviteusers';
        $this->page_name = "betainvite";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for send beta invite
     * Return : Load View files
     */
    public function sendbetainvite(){

        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('beta_invite_send_beta_invite'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/betainvite/sendbetainvite';
        $this->page_name = "betainvite";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for download sample csv file
     * Return : Download csv file
     */
    public function downloadsample(){
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('send_beta_invite_import_file'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $file_name = 'SampleFile.csv';
        $file_path  = DOC_PATH.SUBDIR.PATH_IMG_UPLOAD_FOLDER.'csv_file/'.$file_name;
        
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"".basename($file_name)."\"");
        
        readfile($file_path);
        exit();
    }
    
    public function importcsvfile(){
        $return['Error'] = '';
        $return['Result'] = 0;
        
        if(!in_array(getRightsId('send_beta_invite_import_file'), getUserRightsData($this->DeviceType))){
            $return['ResponseCode']='598';
            $return['Error']= lang('permission_denied');
            $this->output->set_content_type('application/json')->set_output(json_encode($return));
        }else{
        
            if (isset($_FILES['csv_file']['tmp_name']) && is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
                $betafileName = $_FILES['csv_file']["name"];
                $betafileError = $_FILES['csv_file']["error"];
                $betafileTmpName = $_FILES['csv_file']["tmp_name"];
                $validation = true;
                if (empty($betafileName) && $validation == true) {
                    $return['Error'] = 'Please select csv file.';
                    $validation = false;
                }
                if ($validation == true) {
                    $exp = explode(".", $betafileName);
                    $ext = end($exp);
                    if ((strtolower($ext) == "csv")) {
                        $validation = true;
                    }else {
                        $return['Error'] = 'Please upload csv file.';
                        $validation = false;
                    }
                }
                if ($betafileError > 0 && $validation == true) {
                    $return['Error'] = 'Error in file.';
                    $validation = false;
                }
                if ($validation == true) {
                    $userinfo = array();
                    $filename = $betafileTmpName;
                    $cntr = 0;
                    if ((strtolower($ext) == "csv")) {
                        $handle = fopen($filename, "r");
                        while (($data = fgetcsv($handle)) !== FALSE) {                            
                            if ($cntr > 0) {
                                if(isset($data[0]) && $data[0] != "" && isset($data[1]) && $data[1] != "" && valid_email($data[1])){
                                    $userinfo[] = array("name" => $data[0], "email" => $data[1]);
                                }
                            }
                            $cntr++;
                        }
                    }
                    $return['Result'] = 1;
                    $return['UserArr'] = $userinfo;
                }

            }else {
                $return['Error'] = 'Please select CSV file.';
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($return));
        }
    }

}

//End of file betainvite.php