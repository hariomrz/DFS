<?php

class Settings_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }

    public function generateSettingFile() {
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

    public function getModuleSettings($array = false) {
        if (realpath(SETTINGS_FILE)) {
            $fp = fopen(SETTINGS_FILE, 'r');
            $content = fread($fp, filesize(SETTINGS_FILE));
            fclose($fp);
            $content = json_decode($content, true);
            $arr = array();
            if ($content) {
                foreach ($content as $key => $val) {
                    $arr['m' . $key] = $val['Status'];
                }
            }
            if ($array) {
                return $arr;
            } else {
                return json_encode($arr);
            }
        }
    }
}
