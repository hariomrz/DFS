<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| Custom Config File for Global Settings
|--------------------------------------------------------------------------
*/

/*
 * Useful for apply Layouting in project.
 * Now assign admin_layout to config variable
 *
 */
$config['admin_layout'] = 'admin/layout/layout';
$config['title'] = 'Admin';

//$config['default_layout'] = 'usersite/layout/layout';
$config['email_layout'] = 'emailer/layout';
$config['betainvite_error_layout'] = 'layout/betainvitelayout';


$rightsKeyValArr = array();
//Users action group
$rightsKeyValArr['registered_user'] = 1;
$rightsKeyValArr['deleted_user'] = 2;
$rightsKeyValArr['blocked_user'] = 3;
$rightsKeyValArr['waiting_for_approval'] = 4;
$rightsKeyValArr['suspended_user'] = 144;
$rightsKeyValArr['view_profile_event'] = 5;
$rightsKeyValArr['delete_user_event'] = 6;
$rightsKeyValArr['block_user_event'] = 7;
$rightsKeyValArr['unblock_user_event'] = 8;
$rightsKeyValArr['approve_user_event'] = 9;
$rightsKeyValArr['communicate_user_event'] = 10;
$rightsKeyValArr['change_password_event'] = 11;
$rightsKeyValArr['download_users_event'] = 12;
$rightsKeyValArr['user_profile'] = 13;
$rightsKeyValArr['user_profile_overview'] = 14;
$rightsKeyValArr['user_profile_communicate'] = 15;
$rightsKeyValArr['user_profile_media'] = 16;
$rightsKeyValArr['login_as_user_event'] = 106;
$rightsKeyValArr['dummy_user_manager'] = 145;
$rightsKeyValArr['manage_announcement'] = 146;
$rightsKeyValArr['profile_question'] = 147;
$rightsKeyValArr['flag_users'] = 148;
$rightsKeyValArr['activity_dashboard'] = 150;
$rightsKeyValArr['newsletter_subscriber'] = 155;
$rightsKeyValArr['subscriber_list'] = 156;


//Content group(Media section) 
$rightsKeyValArr['media_list'] = 17;
$rightsKeyValArr['media_delete_event'] = 18;
$rightsKeyValArr['media_approve_event'] = 19;
$rightsKeyValArr['media_view_event'] = 20;
$rightsKeyValArr['media_abusemedia'] = 21;
$rightsKeyValArr['media_abusemedia_viewdetail'] = 22;

//Analytics section
$rightsKeyValArr['analytics_tool'] = 46;
$rightsKeyValArr['login_analytics'] = 23;
$rightsKeyValArr['signup_analytics'] = 24;
$rightsKeyValArr['media_analytics'] = 25;
$rightsKeyValArr['most_active_users'] = 26;
$rightsKeyValArr['google_analytics'] = 27;
$rightsKeyValArr['google_analytics_deviceinfo'] = 27;
$rightsKeyValArr['analytic_download_event'] = 28;
$rightsKeyValArr['analytic_user_delete_event'] = 29;//Remove
$rightsKeyValArr['analytic_user_block_event'] = 30;//Remove
$rightsKeyValArr['analytic_user_viewprofile_event'] = 31;//Remove
$rightsKeyValArr['analytic_user_communicate_event'] = 32;//Remove
$rightsKeyValArr['email_analytics'] = 81;
$rightsKeyValArr['email_analytics_emails'] = 82;
$rightsKeyValArr['email_analytics_emails_view_event'] = 83;
$rightsKeyValArr['email_analytics_emails_resend_event'] = 84;

//Email Settings Section
$rightsKeyValArr['smtp_settings'] = 38;
$rightsKeyValArr['smtp_settings_add_event'] = 39;//Remove
$rightsKeyValArr['smtp_settings_make_active_event'] = 40;
$rightsKeyValArr['smtp_settings_make_inactive_event'] = 41;
$rightsKeyValArr['smtp_settings_authentication'] = 42;
$rightsKeyValArr['smtp_settings_local'] = 43;
$rightsKeyValArr['smtp_settings_save_add_event'] = 44;
$rightsKeyValArr['smtp_settings_save_edit_event'] = 45;
$rightsKeyValArr['smtp_settings_delete_event'] = 80;
$rightsKeyValArr['smtp_emails'] = 100;
$rightsKeyValArr['smtp_emails_make_active_event'] = 101;
$rightsKeyValArr['smtp_emails_make_inactive_event'] = 102;
$rightsKeyValArr['smtp_emails_edit_event'] = 103;

//Tools Section
$rightsKeyValArr['analytics_tool'] = 46;
$rightsKeyValArr['analytics_tool_save_add_event'] = 47;//Remove
$rightsKeyValArr['analytics_tool_save_edit_event'] = 48;
$rightsKeyValArr['support_request_listing'] = 49;
$rightsKeyValArr['support_request_listing_pending'] = 50;
$rightsKeyValArr['support_request_listing_ignored'] = 51;
$rightsKeyValArr['support_request_listing_completed'] = 52;
$rightsKeyValArr['support_request_listing_suppport_request_view'] = 53;
$rightsKeyValArr['support_request_listing_export_to_excel_event'] = 54;
$rightsKeyValArr['support_request_listing_delete_event'] = 55;
$rightsKeyValArr['support_request_listing_complete_event'] = 56;
$rightsKeyValArr['support_request_listing_ignore_event'] = 57;
$rightsKeyValArr['support_request_listing_unignore_event'] = 58;
$rightsKeyValArr['support_request_listing_all_error_filter'] = 59;
$rightsKeyValArr['support_request_listing_feature_error_filter'] = 60;
$rightsKeyValArr['support_request_listing_reported_error_filter'] = 61;
$rightsKeyValArr['support_request_listing_server_error_filter'] = 62;
$rightsKeyValArr['support_request_listing_other_error_filter'] = 63;
$rightsKeyValArr['module_settings'] = 157;

//Admin Site
$rightsKeyValArr['admin_site_view'] = 79;

$rightsKeyValArr['list_roles'] = 123;
$rightsKeyValArr['list_role_users'] = 124;
$rightsKeyValArr['list_permissions'] = 125;

//Manage Roles Section
$rightsKeyValArr['viewroles'] = 68;
$rightsKeyValArr['addroles'] = 69;
$rightsKeyValArr['editroles'] = 70;
$rightsKeyValArr['deleteroles'] = 71;
$rightsKeyValArr['managerolepermissions'] = 72;

//Manage Users Section
$rightsKeyValArr['viewusers'] = 73;
$rightsKeyValArr['addusers'] = 74;
$rightsKeyValArr['editusers'] = 75;
$rightsKeyValArr['deleteusers'] = 76;

//Manage Permissions section
$rightsKeyValArr['viewmanagepermissions'] = 77;//Remove
$rightsKeyValArr['changepermsissions'] = 78;

//IPs Section
$rightsKeyValArr['ips_admin'] = 86;
$rightsKeyValArr['ips_user'] = 87;
$rightsKeyValArr['ips_add_event'] = 88;
$rightsKeyValArr['ips_edit_event'] = 89;
$rightsKeyValArr['ips_delete_event'] = 90;
$rightsKeyValArr['ips_active_event'] = 126;
$rightsKeyValArr['ips_inactive_event'] = 127;

//Configuration Management
$rightsKeyValArr['configuration_management_change_event'] = 105;

//Category Management
$rightsKeyValArr['category_admin'] = 130;
$rightsKeyValArr['category_add_event'] = 131;
$rightsKeyValArr['category_edit_event'] = 132;
$rightsKeyValArr['category_active_inactive_event'] = 133;
$rightsKeyValArr['category_delete_event'] = 134;

//Beta Invite Section
$rightsKeyValArr['beta_invite_invited_users'] = 108;
$rightsKeyValArr['beta_invite_not_joined_yet'] = 109;
$rightsKeyValArr['beta_invite_deleted_users'] = 110;
$rightsKeyValArr['beta_invite_removed_access_users'] = 111;
$rightsKeyValArr['beta_invite_reinvite_event'] = 113;
$rightsKeyValArr['beta_invite_delete_event'] = 112;
$rightsKeyValArr['beta_invite_grant_access_event'] = 129;
$rightsKeyValArr['beta_invite_remove_access_event'] = 128;
$rightsKeyValArr['beta_invite_download_event'] = 114;
$rightsKeyValArr['beta_invite_send_beta_invite_event'] = 115;//Remove
$rightsKeyValArr['beta_invite_send_beta_invite'] = 116;
$rightsKeyValArr['send_beta_invite_manual_invite'] = 117;
$rightsKeyValArr['send_beta_invite_manual_invite_send_invite_event'] = 118;//Remove
$rightsKeyValArr['send_beta_invite_import_file'] = 119;
$rightsKeyValArr['send_beta_invite_import_file_download_sample_file_event'] = 120;//Remove
$rightsKeyValArr['send_beta_invite_import_file_upload_event'] = 121;//Remove
$rightsKeyValArr['send_beta_invite_import_file_delete_event'] = 122;
$rightsKeyValArr['login_dashboard'] = 123;

//Group Management
$rightsKeyValArr['group_admin'] = 135;
$rightsKeyValArr['group_creation'] = 136;
$rightsKeyValArr['group_wiki_creation'] = 137;
//manage Popups
$rightsKeyValArr['popup'] = 154;

$config['rightsKeyValArr'] = $rightsKeyValArr;

/* End of file custom_config.php */
/* Location: ./application/config/custom_config.php */
