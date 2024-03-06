<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//----------------------------------------------------------------------------
// Mailchimp API v3 REST Client
// ---------------------------------------------------------------------------
// Settings file
//
// @author    Stefan Ashwell
// @version   1.0
// @updated   14/03/2016
//----------------------------------------------------------------------------

/**
 * API Key
 *
 * Your API Key from your account
 */

if(ENVIRONMENT == 'production') {
    $config['api_key']      = 'a7d5fe0aa4700c1a55c8ab4af583b3f1-us17';
} else if(ENVIRONMENT == 'testing') {
    $config['api_key']      = 'a7d5fe0aa4700c1a55c8ab4af583b3f1-us17';
} else {
    $config['api_key']      = '6c62188ba0417594a7b7d37fdad1bdb1-us9';
}
 

//$config['api_key']      = 'a7d5fe0aa4700c1a55c8ab4af583b3f1-us17';

/**
 * API Endpoint
 *
 * Typically this can remain as the default https://<dc>.api.mailchimp.com/3.0/
 */

$config['api_endpoint'] = 'https://<dc>.api.mailchimp.com/3.0/';

/**
* Default Campaign array for create list
*/
$config['campaign_defaults'] = array(
									'from_name'     => FROM_EMAIL_TITLE,
								    'from_email'    => FROM_EMAIL,
								    'subject'       => MC_CAMPAIGN_SUBJECT,
								    'language'      => MC_LANGUAGE,
								);

/**
* Contact(Owner) Info for create list
*/
$config['list_contact_info'] = array(
									'company'       => MC_LIST_CONTACT_COMPANY,
						            'address1'      => MC_LIST_CONTACT_ADDRESS1,
						            'address2'      => MC_LIST_CONTACT_ADDRESS2,
						            'city'          => MC_LIST_CONTACT_CITY,
						            'state'         => MC_LIST_CONTACT_STATE,
						            'zip'           => MC_LIST_CONTACT_ZIP,
						            'country'       => MC_LIST_CONTACT_COUNTRY,
						            'phone'         => MC_LIST_CONTACT_PHONE
								);
