<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
class Settings extends Admin_API_Controller {


    public function __construct() {
        parent::__construct();
        $this->load->model(array(
            'admin/modules_model'
        ));
    }
    
    public function update_module_settings_post() {
        $this->load->model(array('settings_model'));
        $output = $this->settings_model->generateSettingFile();
        $fp = fopen(SETTINGS_FILE,"w+");
        fwrite($fp,$output);
        fclose($fp);
    }

    public function get_modules_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $return['Data'] = $this->modules_model->get_modules();

        $this->response($return);
    }

    public function install_module_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : '';
        $status = isset($data['Status']) ? $data['Status'] : 0;
        $key = isset($data['ModuleSettingsKey']) ? $data['ModuleSettingsKey'] : '' ;

        if($status == '0')
        {
            if($module_id == 31 || $module_id == 33)
            {
                $modules = $this->modules_model->get_modules();
                foreach($modules as $k=>$v)
                {
                    if(($v['ModuleID'] == 31 && $module_id == 33) || ($v['ModuleID'] == 33 && $module_id == 31))
                    {
                        if($v['IsActive'] == '0')
                        {
                            $return['ResponseCode'] = '511';
                            $return['Message'] = lang('interest_follow_deactivate_error');
                        }
                    }
                }
            }
        }

        if($key != MODULE_SETTINGS_KEY) {
            $return['ResponseCode'] = '511';
            $return['Message'] = lang('input_settings_key');
        }
        else
        {
            $this->modules_model->install_module($module_id, $status);
            if($module_id == 33)
            {
                $this->modules_model->install_module(34, $status);    
            }
        }
        
        $this->response($return);
    }
}