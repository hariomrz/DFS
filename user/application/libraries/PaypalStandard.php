<?php

/**
 * @package Paypal Standard Payment Process Library
 * @author 
 * @copyright
 * @link
 * @since
 * @updated 04/12/2015
 * @filesource
 */
class PaypalStandard {

    public $production_url = "https://www.paypal.com/cgi-bin/webscr";
    public $production_main_url = "www.paypal.com";
    public $sandbox_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
    public $sandbox_main_url = "www.sandbox.paypal.com";
    public $cmd = "_xclick";
    public $paymentaction = '';
    public $charset = "utf-8";
    public $amount = "";
    public $notify_url = "";
    public $business = "";
    public $currency_code = "";
    public $invoice = "";
    public $item_name = "";
    public $item_number = "";
    public $image_url = "";
    public $return = "";
    public $cancel_return = "";
    public $first_name = "";
    public $last_name = "";
    public $address1 = "";
    public $address2 = "";
    public $city = "";
    public $state = "";
    public $zip = "";
    public $country = "";
    public $email = "";
    public $no_note = "0";
    public $no_shipping = "0";
    public $rm = "2";
    public $custom = "";

    /**
     * Constructor
     *
     * @access  public
     * @param   array   config preferences
     * @return  void
     */
    function __construct($DataArray) {
        if (is_array($DataArray)) {
            foreach ($DataArray as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    private function convert_to_url() {
        $url = "cmd=" . urlencode($this->cmd) . "&"
                . "paymentaction=" . urlencode($this->paymentaction) . "&"
                . "amount=" . urlencode($this->amount) . "&"
                . "notify_url=" . urlencode($this->notify_url) . "&"
                . "business=" . urlencode($this->business) . "&"
                . "currency_code=" . urlencode($this->currency_code) . "&"
                . "invoice=" . urlencode($this->invoice) . "&"
                . "item_name=" . urlencode($this->item_name) . "&"
                . "item_number=" . urlencode($this->item_number) . "&"
                . "image_url=" . urlencode($this->image_url) . "&"
                . "return=" . urlencode($this->return) . "&"
                . "cancel_return=" . urlencode($this->cancel_return) . "&"
                . "first_name=" . urlencode($this->first_name) . "&"
                . "last_name=" . urlencode($this->last_name) . "&"
                . "address1=" . urlencode($this->address1) . "&"
                . "address2=" . urlencode($this->address2) . "&"
                . "city=" . urlencode($this->city) . "&"
                . "state=" . urlencode($this->state) . "&"
                . "zip=" . urlencode($this->zip) . "&"
                . "country=" . urlencode($this->country) . "&"
                . "email=" . urlencode($this->email) . "&"
                . "no_note=" . urlencode($this->no_note) . "&"
                . "no_shipping=" . urlencode($this->no_shipping) . "&"
                . "charset=" . urlencode($this->charset) . "&"
                . "custom=" . urlencode($this->custom) . "&"
                . "rm=" . urlencode($this->rm) . "&";
        return $url;
    }

    function get_redirect_url($mode) {
        if($mode == "TEST"){
            $main_url = $this->sandbox_url;
        } else {
            $main_url = $this->production_url;
        }

        $url = $main_url . "?" . $this->convert_to_url();

        return $url;
    }

}
?>