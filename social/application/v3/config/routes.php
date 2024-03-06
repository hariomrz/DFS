<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

$route['default_controller'] = "user_profile/dashboard";

//Custom routing for site
//$route['feeds'] = "user_profile/dashboard/feeds";
//$route['community'] = "user_profile/community";
//$route['community/type/(:any)'] = "user_profile/community";
/* $route['signin'] = "signup/SignIn";
$route['signup'] = "signup/SignUp";
$route['thanks/(:any)'] = "signup/thanks/$1";
$route['signup-step1'] = "signup/Step1";
$route['forgot-password'] = "signup/forgotpassword";
$route['user/(:any)'] = "user_profile/profile/$1";
$route['notifications'] = 'user_profile/notifications';
$route['dashboard'] = "user_profile/dashboard";


$route['search/(:any)'] 	= "search/index/$1";
$route['activity/(:any)'] 	= "wall/activity/$1";
*/

$route['terms-condition'] = "StaticPage/termsAndCondition";
$route['home/language_file.js'] 	= "home/language_file";

/* $route['sitemap'] = "sitemap";
$route['sitemap/general\.xml'] = "sitemap/general";
$route['sitemap/post\.xml'] = "sitemap/post";
$route['sitemap/article\.xml'] = "sitemap/articles";
$route['sitemap/events\.xml'] = "sitemap/events";
$route['sitemap/community\.xml'] = "sitemap/community";
$route['sitemap/group\.xml'] = "sitemap/groups";
*/
// example: '/en/about' -> use controller 'about'
$route['^fr/(.+)$'] = "$1";
$route['^en/(.+)$'] = "$1";
 
// '/en' and '/fr' -> use default controller
$route['^fr$'] = $route['default_controller'];
$route['^en$'] = $route['default_controller'];

$route['404_override'] = 'user_profile/entity_profile';
$route['translate_uri_dashes'] = FALSE;

//custom routing for users
$route['admin'] = "admin/login";
$route['site_maintenance'] = "admin/errors/site_maintenance";
$route['access_denied'] = "admin/errors/access_denied";
$route['blockedip'] = "admin/errors/blockedip";

//this code used for api version
if(API_VERSION == "v4" || API_VERSION == "v5"){
	$route['api/activity/index']	= 'api/v4/activity';
	$route['api/activity']	= 'api/v4/activity';
	$route['api/locality/list']	= 'api/v4/locality/list';
	$route['api/ward/get_featured_user']	= 'api/v4/ward/get_featured_user';
	$route['api/login/check_apk_ver']	= 'api/v4/login/check_apk_ver';	
} 

$route['api/login/master_data']		= 'api/v4/login/master_data';
/* End of file routes.php */
/* Location: ./application/config/routes.php */
