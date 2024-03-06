<?php
	class Settings extends Common_Controller {

	    public function __construct() {
	        parent::__construct();
	    }

	    public function generateSettingFile(){
	    	$output = $this->settings_model->generateSettingFile();
	    	$fp = fopen(SETTINGS_FILE,"w+");
			fwrite($fp,$output);
			fclose($fp);
	    }

	    public function getSettingDetails(){
	    	$result = $this->settings_model->getModuleSettings();
			$this->output->set_output($result);
	    }
            
        public function generateModuleSettings(){
            $data = $_GET;
            $module_id = $data['module_id'];
            if(empty($data['api_key']) || $data['api_key'] != MODULE_SETTINGS_KEY) {
                return;
            }

            if(empty($data['module_id'])) {
                return;
            }
            $data['module_status'] = isset($data['module_status']) ? $data['module_status'] : 0;

            $this->db->where('ModuleID', $data['module_id']);
            $this->db->update(MODULES, array(
                'IsActive' => $data['module_status']
            ));

            if($module_id == 36 || $module_id == 37) {
                $this->disable_activity_type($data['module_id'], $data['module_status']);
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
    }
?>