<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* All process like : configuration settings
* @package    Configuration
* @author     Girish Patidar(07-02-2015)
* @version    1.0
*/

class Configuration extends Admin_API_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/login_model','admin/configuration_model'));
        
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];

    }
        
    /**
     * Function for show configuration setting listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function index_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/configuration';
        $Return['Data']=array();
        $Data = $this->post_data;

        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';

            $tempConfigData = array();
            $configurationResults = $this->configuration_model->getConfigurationSettings($start_offset, $end_offset, $sort_by, $order_by);
            foreach($configurationResults['results'] as $configArr){
                
                if($configArr['DataTypeID'] == '3'){
                    if($configArr['ConfigValue']=='1'){$configArr['ConfigValue']='true';}else{$configArr['ConfigValue'] = 'false';}
                }
                if(strlen($configArr['ConfigValue']) > 150){
                    $configArr['currentValue'] = substr($configArr['ConfigValue'], 0, 150).'...';
                }else{
                    $configArr['currentValue'] = $configArr['ConfigValue'];
                }
                $configArr['Description'] = str_replace("{BASE_URL}", base_url(), $configArr['Description']);
                $tempConfigData[] = $configArr;
            }
            
            //Culture info array
            $languageArr = array();
            $language = getLanguageList();
            
            foreach($language as $key => $val){
                $languageArr[] = $key;
            }
            
            $Return['Data']['total_records'] = $configurationResults['total_records'];
            $Return['Data']['results'] = $tempConfigData;
            $Return['Data']['cultureInfo'] = $languageArr;
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for update configuration setting.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function update_config_setting_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('smtp_create_success');
        $Return['ServiceName']='admin_api/emailsetting/update_config_setting';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('configuration_management_change_event'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {   
            /* Validation - starts */            
            if ($this->form_validation->run('api/configuration') == FALSE) { // for web
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error; //Shows all error messages as a string
            } else {     
                
                if(isset($Data['BUConfigID'])) $BUConfigID = $Data['BUConfigID']; else $BUConfigID = '';
                if(isset($Data['ConfigValue'])) $ConfigValue = $Data['ConfigValue']; else $ConfigValue = '';                
                
                $dataArr = array();
                $dataArr['Value'] = $ConfigValue;

                if(is_numeric($BUConfigID)){
                    $currentSetting = $this->configuration_model->getConfigurationSettingByKeyAndValue('BUCID',$BUConfigID);
                    
                    if($currentSetting['ConfigID'] == GLOBALEMAILSENDING){
                        $tempSetting = $this->configuration_model->getConfigurationSettingByKeyAndValue('ConfigID',SENDMAILVIAMANDRILL);
                    }else if($currentSetting['ConfigID'] == SENDMAILVIAMANDRILL){
                        $tempSetting = $this->configuration_model->getConfigurationSettingByKeyAndValue('ConfigID',GLOBALEMAILSENDING);
                    }
                    
                    if(isset($tempSetting) && is_array($tempSetting) && $tempSetting['Value'] == $ConfigValue && $ConfigValue == 1){
                        $Return['ResponseCode']='519';
                        $Return['Message'] = str_replace("SETTING_KEY", $tempSetting['Name'], lang('email_setting_disable_error'));
                    }else{
                        $this->configuration_model->updateConfigurationSetting($dataArr,$BUConfigID);
                        $Return['Message'] = lang('configuration_setting_update_msg');
                        //For delete exisitng configuration setting cache data
                        deleteCacheData(GLOBALSETTINGS);
                    }
                }else{
                    $Return['ResponseCode']='519';
                    $Return['Message']= lang('input_invalid_format');
                }
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    public function set_group_permission_post()
    {
        $this->check_module_status(1);
        
        $return = $this->return;
        $data   = $this->post_data;

        $default = array(array('ModuleID'=>0,'ModuleEntityGUID'=>0));

        $qa         = isset($data['QA'])        ? $data['QA']       : $default ;
        $wiki       = isset($data['Wiki'])      ? $data['Wiki']     : $default ;
        $tasks      = isset($data['Tasks'])     ? $data['Tasks']    : $default ;
        $ideas      = isset($data['Ideas'])     ? $data['Ideas']    : $default ;
        $polls      = isset($data['Polls'])     ? $data['Polls']    : $default ;
        $discussion = isset($data['Discussion']) ? $data['Discussion'] : $default ;
        $announcements = isset($data['Announcements']) ? $data['Announcements'] : $default ;

        $this->configuration_model->set_group_permission($qa,$wiki,$tasks,$ideas,$polls,$discussion,$announcements);
        $this->response($return);
    }

    public function get_group_permission_post()
    {
        $this->check_module_status(1);
        
        $return = $this->return;
        $data   = $this->post_data;
        $return['data'] = $this->configuration_model->get_group_permission();
        $this->response($return);
    }
    
    /**
     * Function for show culture info listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function cultureinfo_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/configuration/cultureinfo';
        $Return['Data']=array();
        $Data = $this->post_data;

        if(isset($Data) && $Data!=NULL )
        {
            $languageArr = array();
            $language = getLanguageList();
            
            foreach($language as $key => $val){
                $languageArr[] = array('culture_name' => $key, 'language_name' => $val);
            }
            $Return['Data']['total_records'] = count($language);
            $Return['Data']['results'] = $languageArr;
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
        
}//End of file configuration.php