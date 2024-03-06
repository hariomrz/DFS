<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Nodb_model extends CI_Model {

    public function __construct() 
    {
       	parent::__construct();
    }

    public function send_email($to, $subject = "", $message = "", $from_email = FROM_ADMIN_EMAIL, $from_name = FROM_EMAIL_NAME )
	{
        
        /* configuration and sending email */
        $config = array();
        $config['smtp_host'] = SMTP_HOST;
        $config['smtp_user'] = SMTP_USER;
        $config['smtp_pass'] = SMTP_PASS;
        $config['smtp_port'] = SMTP_PORT;
        $config['protocol']  = PROTOCOL;
        $config['smtp_crypto'] = SMTP_CRYPTO;
        $config['mailpath']  = '';
        $config['mailtype']  = 'html';
        $config['charset']   = 'utf-8';
        $config['wordwrap']  = TRUE;

		$this->load->library('email');
        $email = new CI_Email();
        //var_dump($email);
        $email->initialize($config);
        //echo '<pre>';
        //print_r($config);die;
        $email->set_newline("\r\n");
        $email->clear();
        $email->from($from_email, $from_name);
        $email->to(trim($to));
        $email->subject($subject);

        $email->reply_to(NO_REPLY_EMAIL, $from_name);
        $email->message($message);

        if ($email->send()) {
            // echo 'send';die();
            return TRUE;
        } else {
        	// $email->print_debugger(); //for debuging die();
            return FALSE;
        }
    }
    
}
