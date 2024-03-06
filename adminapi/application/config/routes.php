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
|	http://codeigniter.com/user_guide/general/routing.html
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

$route['default_controller']   		= 'template/template/layout';
$route['404_override'] 		   		= 'template/template/_404';
$route['translate_uri_dashes'] 		= FALSE;
$route['template/(:any)/(:any)'] 	= "template/template/template/$1/$2";

/*Common layout routing*/
$route['user']						= 'template/template/layout';
$route['manageadmin']				= 'template/template/layout';
$route['user_detail/(:any)']		= 'template/template/layout';
$route['roster']					= 'template/template/layout';
$route['team']						= 'template/template/layout';
$route['contest']					= 'template/template/layout';
$route['sl_contest']				= 'template/template/layout';
$route['league']					= 'template/template/layout';
$route['season']					= 'template/template/layout';
$route['manage_scoring']			= 'template/template/layout';
$route['withdrawal']			    = 'template/template/layout';
$route['badge']			   			= 'template/template/layout';
$route['reports']			    	= 'template/template/layout';
$route['campaign']			    	= 'template/template/layout';
$route['sd_contest']				= 'template/template/layout';
//$route['desktop_notification']    = 'template/template/layout';
$route['promo_code']				= 'template/template/layout';
$route['pages']						= 'template/template/layout';
$route['feedback']					= 'template/template/layout';
$route['manage_team']				= 'template/template/layout';
$route['app_banner']				= 'template/template/layout';
$route['collection']				= 'template/template/layout';
$route['communication']				= 'template/template/layout';
$route['banner']					= 'template/template/layout';
$route['coins']					    = 'template/template/layout';
$route['dashobard']					= 'template/template/layout';
$route['communication_dashboard/user_segmentation/notify_by_selection']					= 'communication_dashboard/new_campaign/notify_by_selection';
$route['communication_dashboard/user_segmentation/get_filter_result_test']					= 'communication_dashboard/new_campaign/get_filter_result_test';
$route['communication_dashboard/user_segmentation/get_segementation_template_list']					= 'communication_dashboard/new_campaign/get_segementation_template_list';
$route['communication_dashboard/user_segmentation/notify_by_selection_counts']					= 'communication_dashboard/new_campaign/notify_by_selection_counts';

$route['do_upload'] = 'common/do_upload';
$route['remove_media'] = 'common/remove_media';
$route['sports_config'] = 'league/sports_config';
$route['save_sports_config'] = 'league/save_sports_config';
