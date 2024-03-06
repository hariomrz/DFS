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
$route['feeds'] = "user_profile/dashboard/feeds";
$route['community'] = "user_profile/community";
$route['community/type/(:any)'] = "user_profile/community";
$route['signin'] = "signup/SignIn";
$route['signup'] = "signup/SignUp";
$route['thanks/(:any)'] = "signup/thanks/$1";
$route['signup-step1'] = "signup/Step1";
$route['forgot-password'] = "signup/forgotpassword";
$route['user/(:any)'] = "user_profile/profile/$1";
$route['notifications'] = 'user_profile/notifications';
$route['dashboard'] = "user_profile/dashboard";
$route['article'] = "user_profile/wiki";
$route['article/(:any)'] = "user_profile/wiki/$1";
$route['setcover/(:any)'] = "user_profile/set_cover/$1";

// Events 
$route['events/(:any)/about/(:any)'] = "events/about/$1/$2";
$route['events/(:any)/wall/(:any)'] = "events/wall/$1/$2";
$route['events/(:any)/media/(:any)'] = "events/media/$1/$2";
$route['events/(:any)/members/(:any)'] = "events/members/$1/$2";

/* $route['events/about/(:any)'] = "events/about/$1";
$route['events/(:any)'] = "events/wall/$1";
$route['events/wall/(:any)/wall'] = "events/wall/$1";
$route['events/wall/activity/(:any)'] = "events/wall/$1/$2";
$route['events/media/(:any)'] = "events/media/$1";
$route['events/members/(:any)'] = "events/members/$1";
 * 
 */

$route['group/discover_categories'] = "group/discover_categories";
$route['group/discover'] = "group";
$route['group/discover/(:any)/(:num)'] = "group";

$route['group/(:any)/wall/(:num)'] = "group/wall/$1/$2";
$route['group/(:any)/members/(:num)'] = "group/members/$1/$2";
$route['group/(:any)/event/(:num)'] = "group/event/$1/$2";
$route['group/(:any)/article/(:num)'] = "group/article/$1/$2";
$route['group/(:any)/files/(:num)'] = "group/files/$1/$2";
$route['group/(:any)/links/(:num)'] = "group/links/$1/$2";
$route['group/(:any)/setting/(:num)'] = "group/settings/$1/$2";
$route['group/(:any)/(:num)'] = "group/index/$1/$2";
$route['group/(:num)'] = "group/index/$1";


$route['group/(:any)/media/(:num)'] = "group/media/$1/$2";

$route['group/(:any)/media/(:num)/(:any)/(:any)'] = "group/media/$1/$2/$3/$4";

$route['group/(:any)/media/(:num)/(:any)'] = "group/media/$1/$2/$3";





$route['messages/(:any)'] 	= "messages/index/$1/User";
$route['messages/(:any)/(:any)'] 	= "messages/index/$1/$2";

$route['search/(:any)'] 	= "search/index/$1";

$route['activity/(:any)'] 	= "wall/activity/$1";

$route['pages/(:any)/createPage'] 	= "pages/createPage/$1";
$route['page/(:any)/ratings/(:any)'] 	= "pages/ratings/$1/$2";
$route['page/(:any)/ratings'] 	= "pages/ratings/$1";

$route['page/(:any)/media/(:any)'] 	= "pages/media/$1/$2";
$route['page/(:any)/media/(:any)/(:any)'] 	= "pages/media/$1/$2/$3";
$route['page/(:any)/media'] 	= "pages/media/$1";
$route['page/(:any)/event'] 	= "pages/event/$1";
$route['page/(:any)/files'] 	= "pages/files/$1";
$route['page/(:any)/links'] 	= "pages/links/$1";
$route['page/(:any)/followers'] 	= "pages/followers/$1";


$route['page/(:any)/about'] 	= "pages/about/$1";
$route['page/(:any)/endorsment'] 	= "pages/endorsment/$1";

$route['page/(:any)'] 	= "pages/pageDetails/$1";
$route['page/(:any)/activity/(:any)'] 	= "pages/pageDetails/$1/activity/$2";

$route['payment/(:any)/(:any)'] = 'payment/index/$1/$1';

$route['community/(:any)/(:any)/wiki'] 	= "forum/wiki/$1/$2";
$route['community/(:any)/(:any)/(:any)/wiki'] 	= "forum/wiki/$1/$2/$3";

$route['community/(:any)/(:any)/members'] 	= "forum/members/$1/$2";
$route['community/(:any)/(:any)/(:any)/members'] 	= "forum/members/$1/$2/$3";

$route['community/manage_admin/(:any)'] 	= "forum/manage_admin/$1";
$route['community/members_settings/(:any)/(:any)'] 	= "forum/members_settings/$1/$2";
$route['community/members/(:any)/(:any)'] 	= "forum/members/$1/$2";
$route['community/wall/(:any)/(:any)'] 	= "forum/wall/$1/$2";

$route['community/(:any)/(:any)/files'] 	= "forum/files/$1/$2";
$route['community/(:any)/(:any)/(:any)/files'] 	= "forum/files/$1/$2/$3";

$route['community/(:any)/(:any)/links'] 	= "forum/links/$1/$2";
$route['community/(:any)/(:any)/(:any)/links'] 	= "forum/links/$1/$2/$3";

$route['community/(:any)/(:any)/media'] 	= "forum/media/list/$1/$2";
$route['community/(:any)/(:any)/(:any)/media'] 	= "forum/media/list/$1/$2/$3";

$route['community/(:any)/(:any)/media/create'] 	= "forum/media/create/$1/$2";
$route['community/(:any)/(:any)/(:any)/media/create'] 	= "forum/media/create/$1/$2/$3";

$route['community/(:any)/(:any)/media/(:any)'] 	= "forum/media/detail/$1/$2/$3";
$route['community/(:any)/(:any)/(:any)/media/(:any)'] 	= "forum/media/detail/$1/$2/$3/$4";


$route['community/discover'] 	= "forum/index";
$route['community/(:any)'] 	= "forum/index/$1";

$route['community/(:any)/(:any)'] 	= "forum/index/$1/$2";

$route['community/(:any)/(:any)/(:any)'] 	= "forum/index/$1/$2/$3";

$route['community/(:any)/(:any)/(:any)/(:any)'] 	= "forum/index/$1/$2/$3/$4";

$route['terms-condition'] = "StaticPage/termsAndCondition";

$route['home/language_file.js'] 	= "home/language_file";

$route['sitemap'] = "sitemap";
$route['sitemap/general\.xml'] = "sitemap/general";
$route['sitemap/post\.xml'] = "sitemap/post";
$route['sitemap/article\.xml'] = "sitemap/articles";
$route['sitemap/events\.xml'] = "sitemap/events";
$route['sitemap/community\.xml'] = "sitemap/community";
$route['sitemap/group\.xml'] = "sitemap/groups";

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

/* End of file routes.php */
/* Location: ./application/config/routes.php */
