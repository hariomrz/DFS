<?php
// require("stripe-php-master/init.php");
require_once APPPATH . '../../vendor/autoload.php'; 

class New_stripe{
    public function __construct($data) {
        // $publish_key = "pk_live_51IngrZHoKrGPOoe9xgfvpD34NHdwidks5yFwsiGo13DrdMsBs94Y0m2n2paURFmpDTo2IGZkZUPB1ayuHLw0HSeO00eivPDNVq";
        // $secret_key = "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";
       \Stripe\Stripe::setApiKey($data['s_key']);
   }
    
}

?>