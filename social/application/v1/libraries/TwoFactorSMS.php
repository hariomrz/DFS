<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Library for 2 Factor SMS API
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @author          Ankit Patidar
 * @license         
 * @link            vinfotech
 * @version         
 */
class TwoFactorSMS {

    private static $__API_URL_WITH_ENDPOINT;

    public function __construct($API_KEY = null, $API_ENDPOINT = null) {
        if ($API_KEY !== null && $API_KEY !== null):
            self::setAPI($API_KEY, $API_ENDPOINT);
        endif;
    }

    public static function setAPI($API_KEY, $API_ENDPOINT) {
        self::$__API_URL_WITH_ENDPOINT = $API_ENDPOINT . $API_KEY . '/';
    }

    //SEND SMS OTP
    public static function CheckSMSBalance() {
        return self::TFARequest('GET', 'BAL/SMS');
    }

    public static function SendSMSOTPAutoGen($CellNumber) {
        return self::TFARequest('GET', 'SMS/' . $CellNumber . '/AUTOGEN');
    }

    public static function SendSMSOTPAutoGenWithTemplate($CellNumber, $TemplateName) {
        return self::TFARequest('GET', 'SMS/' . $CellNumber . '/AUTOGEN/' . $TemplateName);
    }

    public static function SendSMSOTPCustom($CellNumber, $Otp) {
        return self::TFARequest('GET', 'SMS/' . $CellNumber . '/' . $Otp);
    }

    public static function SendSMSOTPCustomWithTemplate($CellNumber, $Otp, $TemplateName) {
        return self::TFARequest('GET', 'SMS/' . $CellNumber . '/' . $Otp . '/' . $TemplateName);
    }

    public static function VerifySMSOTP($SessionId, $OtpInput) {
        return self::TFARequest('GET', 'SMS/VERIFY/' . $SessionId . '/' . $OtpInput);
    }

    //SEND TRANSACTION SMS
    public function CheckTransactionalSMSCreditBalance() {
        return self::TFARequest('GET', 'ADDON_SERVICES/BAL/TRANSACTIONAL_SMS');
    }

    public function PullDeliveryReport($SessionId) {
        return self::TFARequest('GET', 'ADDON_SERVICES/RPT/TSMS/' . $SessionId);
    }

    public function SendTransactionSMSOpenTemplate($From, $To, $Msg, $SendAt = '') {
        return self::TFARequest('POST', 'ADDON_SERVICES/SEND/TSMS', "{'From':'$Form', 'To':'$To', 'Msg':'$Msg', 'SendAt':'$SendAt'}");
    }

    public function SendTransactionSMSDynamicTemplate($From, $To, $Msg, $Var1 = '', $Var2 = '', $Var3 = '', $Var4 = '', $Var5 = '') {
        return self::TFARequest('POST', 'ADDON_SERVICES/SEND/TSMS', "{'From':'$Form', 'To':'$To', 'Msg':'$Msg', 'Var1':'$Var1'}, 'Var2':'$Var2'}, 'Var3':'$Var3'}, 'Var4':'$Var4'}, 'Var5':'$Var5'}");
    }

    //SEND PROMOTIONAL SMS
    public function CheckPromotionalSMSCreditBalance() {
        return self::TFARequest('GET', 'ADDON_SERVICES/BAL/PROMOTIONAL_SMS');
    }

    public function SendPromotionalSMS($From, $To, $Msg, $SendAt = '') {
        return self::TFARequest('POST', 'ADDON_SERVICES/SEND/PSMS', '{"From":"'.$From.'", "To":"'.$To.'", "Msg":"'.$Msg.'", "SendAt":"'.$SendAt.'"}');
    }

    public static function TFARequest($Method, $Params, $CurlPostFields = '{}') {
        $Curl = curl_init();
        curl_setopt_array($Curl, array(
            CURLOPT_URL => self::$__API_URL_WITH_ENDPOINT . $Params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $Method,
            CURLOPT_POSTFIELDS => $CurlPostFields,
        ));

        $Response = curl_exec($Curl);
        $Error = curl_error($Curl);
        curl_close($Curl);

        if ($Error) {
            return $Error;
        } else {
            return $Response;
        }
    }

}
