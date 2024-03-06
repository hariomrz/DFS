<?php

//Mail function
function send_email( $to , $subject = "" , $message = "" , $from_email =''  , $from_name = '' )
{
	if($to ==''){
		return false;
	}

	if($from_email == '')
	{
		$from_email=getenv('CD_FROM_ADMIN_EMAIL');
	}

	if($from_name == '')
	{
		$from_name=getenv('CD_FROM_EMAIL_NAME');
	}
	//require 'PHPMailerAutoload.php';
	require_once APPPATH.'third_party/smtp/PHPMailerAutoload.php';

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
	$mail->Host        = getenv('CD_SMTP_HOST');
	//Set the SMTP port number - likely to be 25, 465 or 587
	$mail->Port        = getenv('CD_SMTP_PORT') ;
	//Whether to use SMTP authentication
	$mail->SMTPAuth    = TRUE;
	$mail->SMTPSecure  = '';
	//Username to use for SMTP authentication
	$mail->Username    = getenv('CD_SMTP_USER') ;
	//Password to use for SMTP authentication
	$mail->Password    = getenv('CD_SMTP_PASS') ;
	//Set who the message is to be sent from
	$mail->setFrom( $from_email , $from_name );
	//Set an alternative reply-to address
	$mail->addReplyTo( $from_email , $from_name );
	//Set who the message is to be sent to

	$emails = explode(',', $to);

	foreach ($emails as $key => $value) 
	{
		$mail->addAddress( $value , "");
	}
		
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
	//$mail->send();
	return $mail->send();
}


function two_factor_SMS_cd($From,$To,$Msg,$sms_type=1,$config)
{
    $YourAPIKey = $config['sms_gateway_auth_key'];
   
    $api_url = "/ADDON_SERVICES/SEND/TSMS";

    //sms type 1=transactional
    //sms type 2=promotional
    switch ($sms_type) {
        case 1:
            $api_url = "/ADDON_SERVICES/SEND/TSMS";
            break;
        case 2:
            $api_url = "/ADDON_SERVICES/SEND/PSMS";
            break;
        
        default:
            # code...
            break;
    }

    ### DO NOT Change anything below this line

    $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

    $url = $config['sms_gateway_api_endpoint']."$YourAPIKey".$api_url; 
    $ch = curl_init(); 
    curl_setopt($ch,CURLOPT_URL,$url); 
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); 
    curl_setopt($ch,CURLOPT_POSTFIELDS,"From=$From&To=$To&Msg=$Msg"); 
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    $output = curl_exec($ch); 
    curl_close($ch);

    return $output;
}  



/**
 * send msg91 ms
 * @param array $post_data
 * @return array
 */
if (!function_exists('send_msg91_sms_cd')) {

    function send_msg91_sms_cd($post_data = array(),$config) {
        //http://api.msg91.com/ $config['']
        $url = $config['sms_gateway_api_endpoint'] . "api/sendhttp.php";
        $country_code = DEFAULT_PHONE_CODE;
        if(isset($post_data['phone_code']) && $post_data['phone_code'] != ""){
            $country_code = $post_data['phone_code'];
        }
        $route = $config['sms_gateway_route_id'];
        if(isset($post_data['route']) && $post_data['route'] != ""){
            $route = $post_data['route'];
        }
        $post_array = array(
            "route" => $route,
            "sender" => $config['sms_gateway_sender_id'],
            "authkey" => $config['sms_gateway_auth_key'],
            "country" => $country_code,
            "mobiles" => $post_data['mobile'],
            "message" => isset($post_data['message']) ? $post_data['message'] : "",
            "encrypt" => "",
            "flash" => "",
            "unicode" => '1',
            "afterminutes" => "",
            "response" => "",
            "campaign" => "",
        );

        $template_id = $config['sms_gateway_template'];
        if(isset($post_data['template_id']) && $post_data['template_id'] != ""){
            $template_id = $post_data['template_id'];
        }

        if($template_id != ""){
            $post_array['DLT_TE_ID'] = $template_id;
        }

        $query = http_build_query($post_array);
        $url = $url . "?" . $query;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return array("response" => $err);
        } else {
            return $response;
        }
    }

}

?>