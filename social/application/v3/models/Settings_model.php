<?php

class Settings_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function generateSettingFile() {
        $this->load->database();
        $query = $this->db->get(MODULES);
        $array = array();
        if ($query->num_rows()) {
            foreach ($query->result() as $result) {
                $array[$result->ModuleID]['Status'] = $result->IsActive;
                $array[$result->ModuleID]['ModuleName'] = $result->ModuleName;
            }
        }
        return json_encode($array);
    }

    public function isDisabled($moduleID) {

        if (!empty($this->moduleSettings) && isset($this->moduleSettings['m' . $moduleID])) {
            if ($this->moduleSettings['m' . $moduleID] == 1) {
                return true;
            } else {
                return false;
            }
        }

        if (realpath(SETTINGS_FILE)) {
            $fp = fopen(SETTINGS_FILE, 'r');
            $content = fread($fp, filesize(SETTINGS_FILE));
            fclose($fp);
            $content = json_decode($content, true);
            if (isset($content[$moduleID]['Status'])) {
                if ($content[$moduleID]['Status'] == '0') {
                    return true;
                }
            }
        }
        return false;
    }    
    
    /**
     * [get_source_id Used to get source ID]
     * @param  [string] $source_type [source type]
     * @return [int]                 [source ID]
     */
    function get_source_id($source_type) {
        $source = array('Web' => 1, 'Facebook API' => 2, 'Twitter API' => 3, 'Google API' => 4, 'Camera' => 5, 'Gallery' => 6, 'LinkedIN API' => 7, 'Instagram API' => 8);
        $source_id = '';
        if(isset($source[$source_type])) {
            $source_id = $source[$source_type];
        } 
        return $source_id;
    }
}
