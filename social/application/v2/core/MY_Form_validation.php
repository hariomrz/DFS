<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
 
/**
 * Additional validations for URL format.
 *
 * @package      Module Creator
 * @subpackage  ThirdParty
 * @category    Libraries
 */
 
class MY_Form_validation extends CI_Form_validation{
     
   public function __construct()
   {
     parent::__construct();
   }  
                         
    /**
     * Validate URL format
     *
     * @access  public
     * @param   string
     * @return  string
     */
    function valid_url_custom($str)
    {
        $pattern = "/^((ht|f)tp(s?)\:\/\/|~/|/)?([w]{2}([\w\-]+\.)+([\w]{2,5}))(:[\d]{1,5})?/";
         if (!preg_match($pattern, $url))
         {
             $this->form_validation->set_message('validate_url', 'The URL you entered is not correctly formatted.'); 
             return false;
         }

         return false;
    }       
 
    // --------------------------------------------------------------------
     
 
    /**
     * Validates that a URL is accessible. Also takes ports into consideration. 
     * Note: If you see "php_network_getaddresses: getaddrinfo failed: nodename nor servname provided, or not known" 
     *          then you are having DNS resolution issues and need to fix Apache
     *
     * @access  public
     * @param   string
     * @return  string
     */
    function url_exists($url){                                   
        $url_data = parse_url($url); // scheme, host, port, path, query
        if(!fsockopen($url_data['host'], isset($url_data['port']) ? $url_data['port'] : 80)){
            $this->set_message('url_exists', 'The URL you entered is not accessible.');
            return FALSE;
        }               
         
        return TRUE;
    }  
}
// END Form Validation Class

/* End of file My_Form_validation.php */
/* Location: ./application/libraries/My_Form_validation.php */