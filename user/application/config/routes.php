<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['default_controller'] = 'Auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//$route['template/(:any)/(:any)'] 	= "template/template/template/$1/$2";
$route['deposit/payumoney/success']		= 'payumoney/payumoney/success';
$route['deposit/payumoney/failure']		= 'payumoney/payumoney/failure';
$route['deposit/payumoney']				= 'payumoney/payumoney/deposit';

$route['vpay/success']		= 'vpay/vpay/success';
$route['vpay/failure']		= 'vpay/vpay/failure';
$route['deposit/vpay']				= 'vpay/vpay/deposit';

//0-phone, 1-email, 2-email-single_page
if(LOGIN_FLOW == 1){
	$route['auth/login']				= 'emailauth/emailauth/login';
	$route['auth/validate_otp']			= 'emailauth/emailauth/validate_otp';
	$route['auth/resend_otp']			= 'emailauth/emailauth/resend_otp';
	$route['auth/validate_login'] 		= 'emailauth/emailauth/validate_login';
	$route['auth/forgot_password']		= 'emailauth/emailauth/forgot_password';
	$route['auth/forgot_password_validate_code'] = 'emailauth/emailauth/forgot_password_validate_code';
	$route['auth/reset_password'] 		= 'emailauth/emailauth/reset_password';
	$route['emailauth/update_profile_data'] = 'emailauth/emailauth_profile/update_profile_data';
	$route['auth/social_login']			= 'emailauth/emailauth/social_login';
}else if(LOGIN_FLOW == 2){
	$route['auth/signup']				= 'emailauth/emailauth/signup';
	$route['auth/signup_validate']		= 'emailauth/emailauth/signup_validate';
	$route['auth/login']				= 'emailauth/emailauth/email_login';
	$route['auth/forgot_password']		= 'emailauth/emailauth/forgot_password';
	$route['auth/forgot_password_validate_code'] = 'emailauth/emailauth/forgot_password_validate_code';
	$route['auth/reset_password'] 		= 'emailauth/emailauth/reset_password';
	$route['auth/resend_otp']			= 'emailauth/emailauth/resend_otp';
}

//this code used for api version
if(API_VERSION == "v2"){
	$route['auth/get_signup_referral_data']	= 'v2/v2_auth/get_signup_referral_data';
	$route['finance/withdraw']			= 'v2/v2_finance/withdraw';
}
