<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Modules_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }

    function get_modules()
    {
    	$data = array();

    	$this->db->select('ModuleID,ModuleName,Icon,Version,IsActive');
    	$this->db->from(MODULES);
    	$this->db->where('Installable','1');
    	$query = $this->db->get();
    	if($query->num_rows())
    	{
    		$data = $query->result_array();
    	}

    	return $data;
    }

    function install_module($module_id,$status)
    {
        $this->db->where('ModuleID', $module_id);
        $this->db->update(MODULES, array(
            'IsActive' => $status
        ));

        if($module_id == 36 || $module_id == 37) {
            $this->disable_activity_type($module_id, $status);
        }
        if (CACHE_ENABLE) {
            $this->cache->clean();
        }
        $this->generateSettingFile();
        $this->load->driver('cache');
        $this->cache->file->delete(GLOBALSETTINGS);
    }

    private function disable_activity_type($module_id, $status) {
        $activity_type = array();
        if($module_id == 36) {
            $activity_type = array(37,38,39);
        }
        if($module_id == 37) {
            $activity_type = array(36);
        }

        $status_id = 3;
        if($status) {
            $status_id = 2;
        }

        if($activity_type) {
            $this->db->where_in('ActivityTypeID', $activity_type);
            $this->db->update(ACTIVITYTYPE, array(
                'StatusID' => $status_id
            ));
        }
    }

    public function generateSettingFile(){
    	/*if(chmod(SETTINGS_FILE,0777))
    	{*/
                $this->load->model(array('settings_model'));
	    	$output = $this->settings_model->generateSettingFile();
	    	$fp = fopen(SETTINGS_FILE,"w+");
			fwrite($fp,$output);
			fclose($fp);
    	/*}*/
    }
}

?>