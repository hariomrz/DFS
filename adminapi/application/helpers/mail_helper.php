<?php

//Mail function

	function send_email( $to , $subject = "" , $message = "" , $from_email = FROM_ADMIN_EMAIL , $from_name = FROM_EMAIL_NAME , $bcc = FALSE )
	{
		if($to ==''){
			return false;
		}

		$email = new CI_Email();
		$config[ 'wordwrap' ]  = TRUE;
		$config[ 'mailtype' ]  = 'html';
		$config[ 'charset' ]   = "utf-8";
		$config[ 'protocol' ]  = PROTOCOL;
		$config[ 'smtp_user' ] = SMTP_USER;
		$config[ 'smtp_pass' ] = SMTP_PASS;
		$config[ 'smtp_host' ] = SMTP_HOST;
		$config[ 'smtp_port' ] = SMTP_PORT;
		$config['smtp_crypto'] 	= SMTP_CRYPTO;
		$config['newline'] 		= "\r\n";  // SES hangs with just \n

		$email->initialize( $config );

		$email->clear();
		$email->from( $from_email , $from_name );
		$email->reply_to(NO_REPLY_EMAIL , $from_name);
		$email->to( $to );
		if($bcc !== FALSE){
			$email->bcc($bcc);
		}
		$email->subject( $subject );
		$email->message( $message );
		$email->send();
		return true;
	}

	function send_email_old( $to , $subject = "" , $message = "" , $from_email = FROM_ADMIN_EMAIL , $from_name = FROM_EMAIL_NAME )
	{
		if($to ==''){
			return false;
		}
		//require 'PHPMailerAutoload.php';
		require_once(ROOT_PATH ."application/third_party/smtp/PHPMailerAutoload.php");
		//Create a new PHPMailer instance
		$mail              = new PHPMailer();
		//Tell PHPMailer to use SMTP
		$mail->isSMTP();

		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		
		$mail->SMTPDebug   = 0;
		//Ask for HTML-friendly debug output
		$mail->Debugoutput = 'html';
		//Set the hostname of the mail server
		$mail->Host        = SMTP_HOST;
		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port        = SMTP_PORT;
		//Whether to use SMTP authentication
		$mail->SMTPAuth    = true;
		$mail->SMTPSecure  = SMTP_CRYPTO;
		//Username to use for SMTP authentication
		$mail->Username    = SMTP_USER;
		//Password to use for SMTP authentication
		$mail->Password    = SMTP_PASS;
		//Set who the message is to be sent from
		$mail->setFrom( $from_email , $from_name );
		//Set an alternative reply-to address
		$mail->addReplyTo( NO_REPLY_EMAIL , $from_name );
		//Set who the message is to be sent to
		$mail->addAddress( $to , $subject );
		//$mail->addAddress('viscus008@hotmail.com', 'This is a subject Ultimate 11');
		//Set the subject line
		$mail->Subject     = $subject;
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$mail->msgHTML( $message );
		//Replace the plain text body with one created manually
		// $mail->AltBody = 'This is a plain-text message body';
		//Attach an image file
		// $mail->addAttachment('images/phpmailer_mini.png');

		//send the message, check for errors
		$mail->send();
		return true;
	}
?>