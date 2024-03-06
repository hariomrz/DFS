<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * All Betainvite related api services
 * @package    BetaInvite
 * @author     Girish Patidar : 16-03-2015
 * @version    1.0
 */

class Betainvite extends Common_API_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/betainvite_model'));
    }
        
    /**
     * Function for validate user entered code.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function verifycode_post(){
        $Return['ResponseCode']='200';
        $Return['Message']= lang('verified_beta_code');
        $Return['ServiceName']='api/betainvite/verifycode';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        if(isset($Data) && $Data!=NULL ){

            if(isset($Data['InviteCode']))  $InviteCode = $Data['InviteCode']; else $InviteCode = '';
            if($InviteCode == ''){
                $Return['ResponseCode']='519';
                $Return['Message']= lang('invite_code_required');
            }else{
                $result = $this->betainvite_model->verifyBetaInvitationCode($InviteCode);
                if($result["result"] == "valid"){
                    $sid =  $this->session->userdata('session_id');
                    $InviteLogArr['BetaInviteID'] = $result['BetaInviteID'];
                    $InviteLogArr['SessionID'] = $sid;
                    $InviteLogArr['IPAddress'] = getRealIpAddr();
                    $InviteLogArr['IsAccessByCode'] = '1';

                    if($InviteLogArr['IPAddress'] == '127.0.0.1')
                        $InviteLogArr['IPAddress'] = DEFAULT_IP_ADDRESS;

                    if ($InviteLogArr['IPAddress'] != '')
                    {                        
                        $this->load->helper('location');
                        $locationData = get_ip_location_details($InviteLogArr['IPAddress']);
                    }

                    if ($locationData['statusCode'] == "OK") {
                        $InviteLogArr['Location'] = $locationData['CityName'].' '.$locationData['StateName'].', '.$locationData['CountryName'];
                    } else {
                        $InviteLogArr['Location'] = '';
                    }
                    $InviteLogArr['CreatedDate'] = date('Y-m-d H:i:s');
                    
                    $this->betainvite_model->saveBetaInviteLogs($InviteLogArr);                    
                    
                    $url = base_url();
                    $this->session->set_userdata('IsBetaVerify', "1");
                    $Return['Dataurl'] = $url;
                }else{
                    $Return['ResponseCode'] = '519';
                    $Return['Message'] = lang('invalid_invite_code');
                }                
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }    
        
}//End of file betainvite.php