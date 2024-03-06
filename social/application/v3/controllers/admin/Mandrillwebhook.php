<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All Mandrill API related views rendering functions
 * @package    IPs
 * @author     Girish Patidar : 16-04-2015
 * @version    1.0
 */

class Mandrillwebhook extends CI_Controller {

    public function __construct() {
        parent::__construct();     
        $this->load->model(array('admin/mandrill_model'));
    }

    public function response(){
        $response = json_decode(stripslashes($_POST['mandrill_events']), true);
        
        //$data = json_encode($response[0],JSON_UNESCAPED_SLASHES);
        //sendMail(array(),"girishp.vinfotech@gmail.com","Mandrill Response Data-".date("Y-m-d H:i:s"),$data);
        $result = $this->mandrill_model->saveMandrillResponseData($response[0]);
        //sendMail(array(),"girishp.vinfotech@gmail.com","Mandrill Response -Status = ".$result." :: ".date("Y-m-d H:i:s"), json_encode($response[0]));
        return $result;
    }

}
//End of file ips.php