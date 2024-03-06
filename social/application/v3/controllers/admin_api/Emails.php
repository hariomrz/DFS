<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* All process like : emails_listing
* @package    Emails
* @author     Girish soni (20-01-2015)
* @version    1.0
*/

class Emails extends Admin_API_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/communication_model','admin/media_model'));        
    }
        
    /**
     * Function for show emails listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function index_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/emails';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        $global_settings = $this->config->item("global_settings");

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('email_analytics_emails'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;
            
            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';

            if(isset($Data['EmailType']))  $email_type=$Data['EmailType']; else $email_type= '';

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';
            
            $tempResults = array();
            $emailsTemp = $this->communication_model->getCommunicationEmails($start_offset, $end_offset, $email_type, $start_date, $end_date, $sort_by, $order_by);                
            
            foreach ($emailsTemp['results'] as $temp)
            {
                $temp['username'] = stripslashes($temp['username']);
                $temp['created_date'] = date($global_settings['date_format'].' '.$global_settings['time_format'],  strtotime($temp['created_date']));
                $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
                if(!isset($temp['profilepicture'])){
                    $temp['profilepicture'] = '';
                }
                $temp['profilepicture'] = get_image_path($profileSection, $temp['profilepicture'],ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);
                $tempResults[] = $temp;
            }
            $Return['Data']['total_records'] = $emailsTemp['total_records'];
            $Return['Data']['results'] = $tempResults;
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
    }
        
}//End of file emails.php