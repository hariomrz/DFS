<?php
/**
 * @Summary: send a email function
 * @access: public
 * @param: $to,$subject,$message, $from_email, $from_name
 * @return: Send email
 */
function send_email( $to,$subject,$message,$from_mail= FROM_EMAIL,$from_title = FROM_EMAIL_TITLE )
{       
        $CI =& get_instance();
        $CI->load->library('email');
        //Set the hostname of the mail server
        $config['smtp_host'] = SMTP_HOST;
        
        //Username to use for SMTP authentication
        $config['smtp_user'] = SMTP_USER;
        
        //Password to use for SMTP authentication
        $config['smtp_pass'] = SMTP_PASS;
        
        //Set the SMTP port number - likely to be 25, 465 or 587
        $config['smtp_port'] = SMTP_PORT;
        
        //Set the SMTP PROTOCOL
        $config['protocol'] = PROTOCOL;
        
        //Set the other configuration for Mail
        $config['mailpath'] = MAILPATH;
        $config['mailtype'] = MAILTYPE;
        //$config['charset'] = CHARSET;
        //$config['wordwrap'] = WORDWRAP;
        //$config['smtp_crypto'] 	= 'ssl';
        $config['_smtp_auth'] = TRUE;
        
        //Create a new CIMailer instance
        $email = new CI_Email();
        $email->initialize($config);
        $email->set_newline("\r\n");
        $email->clear();
        
        //Set who the message is to be sent from
        $email->from(FROM_EMAIL, FROM_EMAIL_TITLE);
        $email->to(trim($to));
        $email->subject($subject);
        $email->reply_to(NO_REPLY_EMAIL, NO_REPLY_EMAIL_TITLE);
        $email->message($message);
        //Send Email
        //$email->send();
        //$email->print_debugger();
        return true;
}


if ( ! function_exists('sendMail')) {
     /**
      * [sendMails description]
      * @param  [string] $from             [From email address]
      * @param  [string] $from_name        [Mail sender name]
      * @param  [string] $to               [To email address]
      * @param  [string] $subject          [Mail Subject]
      * @param  [string] $template_content [Mail Content]
      * @return [boolean]                   [true/false]
      */

     function sendMail($smtpData = array(), $to, $subject, $template_content, $additional_headers = array()) {
        $CI =& get_instance();
        $CI->load->library('email');
        if(isset($smtpData['ServerName']))
            $SMTP_HOST = $smtpData['ServerName']; 
        else $SMTP_HOST = SMTP_HOST;
        if(isset($smtpData['UserName']))
            $SMTP_USER = $smtpData['UserName']; 
        else $SMTP_USER = SMTP_USER;
        if(isset($smtpData['Password']))
            $SMTP_PASS = $smtpData['Password']; 
        else $SMTP_PASS = SMTP_PASS;
        if(isset($smtpData['SPortNo']))
            $SMTP_PORT = $smtpData['SPortNo']; 
        else $SMTP_PORT = SMTP_PORT;
        if(isset($smtpData['FromEmail']))
            $FROM_EMAIL =  $smtpData['FromEmail']; 
        else $FROM_EMAIL = FROM_EMAIL;
        if(isset($smtpData['FromName']))
            $FROM_NAME = $smtpData['FromName']; 
        else $FROM_NAME = FROM_EMAIL_TITLE;
        if(isset($smtpData['ReplyTo']))
            $REPLY_EMAIL = $smtpData['ReplyTo']; 
        else $REPLY_EMAIL = EMAIL_NOREPLY_FROM;


        $message = $template_content;
        /* configuration and sending email */
        $config['smtp_host'] = $SMTP_HOST;
        $config['smtp_user'] = $SMTP_USER;
        $config['smtp_pass'] = $SMTP_PASS;
        $config['smtp_port'] = $SMTP_PORT;
        $config['protocol']  = PROTOCOL;
        $config['mailpath']  = MAILPATH;
        $config['mailtype']  = MAILTYPE;
        $config['charset']   = 'utf-8';
        $config['wordwrap']  = TRUE;

        $email = new CI_Email();
        $email->initialize($config);
        $email->set_newline("\r\n");
        $email->clear();
        $email->from($FROM_EMAIL, $FROM_NAME);
        $email->to(trim($to));
        $email->subject($subject);
        

        //set custom reply_to header and also check if external communication is enabled
        /*$CI->load->model('Settings_model');        
        if(isset($additional_headers['reply_to']) && !empty($additional_headers['reply_to']) && !$CI->settings_model->isDisabled('32'))            
        {
            $email->reply_to($additional_headers['reply_to']);//$REPLY_EMAIL, $FROM_NAME);        
            $REPLY_EMAIL = $additional_headers['reply_to'];//.'-noreply@vinfotech.com';
        }
        else*/
            $email->reply_to($REPLY_EMAIL, $FROM_NAME);

        /*if(is_array($additional_headers) && count($additional_headers) > 0)
        {
             $email->set_additional_header($additional_headers);
        }*/   

        $email->message($message);
        //$email->send();
       // echo $email->print_debugger();die;
        if ($email->send()) {
            return TRUE;
        } else {
            return FALSE;
        }
     }
} 
