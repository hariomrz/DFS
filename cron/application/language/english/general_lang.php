<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */

$lang['dummy']					= 'The {field} field is required.';
$lang['feedback_mail_subject']	= 'User Feed Back';
$lang['feedback_success']		= 'Feedback send successfully';
$lang['contest_not_found']		= 'Contest not found.';

/*Start Lineup Section*/
$lang['invalid_user_to_join_contest']				= 'Invalid user to join contest';
$lang['game_cancel']								= 'Contest has been Cancelled.';
$lang['game_entry_full']							= 'Entry is full for this game';
$lang['game_already_started']						= 'This game has already been started.';
$lang['lineup_unknown_error']						= 'Lineup unknown error';
$lang['invalid_game']								= "Invalid game.";
$lang['invalid_lineup'] 							= "Invalid lineup";	
$lang['already_joined_game_invalid_id']				= 'You have already joined the game.Please provide valid lineup id to update the lineup';
$lang['line_up_incomplete']							= 'Please select a player for each position.';
$lang['season_game_unique_id_not_found'] 			= "Player not selected from valid matches.";
$lang['lineup_player_exceed']						= "Lineup player limit exceed.";
$lang['lineup_invalid_player']						= "Invalid lineup player";
$lang['lineup_position_player_required'] 			= "Lineup player position required.";
$lang['insufficient_amount']						= 'You have insufficient amount to join this contest.';
$lang['lineup_entered']								= 'Your line-up has been entered successfully.';
$lang['line_up_changed']							= 'Your team has been changed successfully.';
$lang['successfully_saved_template']				= 'Lineup template saved successfully.';
$lang['not_found_lineup_template']					= 'Lineup template not found.';
$lang['same_match_players']							= 'Please select player in two different matches.';
$lang['swap_player_not_found']						= 'Swap player not found in your lineup';
$lang['new_player_already_exsit_in_lineup']			= 'New player is already exist in current lineup.';
$lang['player_swap_successfully']					= 'Player swaped successfully';
$lang['invaild_player_swaping']						= 'Invaild player swaping';
/*End Lineup Sectoin*/
/**/
$lang['no_account_found']                = 'No account was found using this detail. Please try again or sign up.';
$lang['file_not_found']                  = 'You did not select a file to upload.';
$lang['invalid_image_size']              = 'Invalid Image Size.';
$lang['login_session_expired']           = 'Login session expired.';
$lang['profile_added_successfully']      = 'Profile updated Successfully.';
$lang['something_went_wrong']            = 'Something went Wrong.';
$lang['email_contest']                   = 'Contest';
$lang['contest_cancel_mail_subject']     = '['.SITE_TITLE.'] Contest cancellation information'; 
$lang['email_hi']                        = "Hi";
$lang['email_contest_canceled_message']  = 'The contest that you joined, %s, did not have enough players to fill all spots and has been cancelled. Your funds paid to play have been refunded to your account.';
$lang['team']                            = SITE_TITLE.' Team';
$lang['email_status']                    = "Status";
$lang['email_date']                      = 'Date';
$lang['email_contest_canceled_message2'] = 'Please %s to join another contest.';
$lang['cheers']                          = 'Cheers';
$lang['email_click_here']                = 'click here';
$lang['email_canceled']                  = 'Cancelled';
$lang["contest_create_successfully"]     = "Contest has been successfully created.";
$lang["invalid_contest_type"]            = "Invalid contest type";
/**/
$lang['selected_matches_invalid']        = 'Selected machtes is invalid';

$lang['logout_successfully']       = 'Logout successfully.';
$lang['myteam_not_found']          = 'My teams is not found';
$lang['myopponents_not_found']     = 'My opponents is not found';
$lang['reserve_lineup_is_expired'] = 'Reserve lineup is expired';

//myprofile
$lang['problem_while_fee_deduct'] = 'Problem while fee deduct.';


//social check
$lang['social_required']          = 'At least one social detail required.';
$lang['invalid_device_type']      = 'Invalid Device Type.';
$lang['email_already_exists']     = 'Email Id already exist.';
$lang['user_name_already_exists'] = 'User name already exist.';
$lang['phone_no_already_exists']  = 'Phone number already exist.';
$lang['please_select_language']   = 'Please select a language.';
$lang['invalid_image_url']        = 'Invalid image url.';
$lang['password_required']        = 'Password is required.';
$lang['phone_no_required']        = 'Phone number is required.';
$lang['user_name_required']       = 'Username is required.';

//login
$lang['invalid_facebook_id']           = 'Invalid Facebook ID.';
$lang['invalid_twitter_id']            = 'Invalid Twitter ID.';
$lang['invalid_google_id']             = 'Invalid Google ID.';
$lang['invalid_email']                 = 'Invalid Email.';
$lang['invalid_user_name']             = 'Invalid Username.';
$lang['enter_valid_password']          = 'Enter valid password.';
$lang['not_authorized']                = 'Not authorized.';
$lang['enter_valid_otp']               = 'Enter valid OTP.';
$lang['confirm_code']                  = 'OTP code';
$lang['phone_verified_success']        = 'Your phone number verified successfully.';
$lang['phone_verified_failed']         = 'Please enter a valid code.';
$lang['otp_send_success']              = 'OTP sent on your phone is valid for 30 seconds only.';
$lang['signup_success']              = 'Signup successfull';
$lang['space_not_allowed_in_username'] = 'Space not allowed in username.';
$lang['login_success'] = 'Login successfully.';
$lang['language_array']         = array('en','ar');
$lang['eighteen_years_old'] = 'Only 18 years old allowed.';

//forgot password
$lang['enter_email_username_number']         = "Please enter valid email, username or mobile number";
$lang['forgot_password_email_subject'] = '['.SITE_TITLE.'] Forgot your password';
$lang['forgot_passwork_email_message']       = "Please follow below link to reset your password.";
$lang['forgot_pass_mail_sent']               = "Please visit %s to reset your password.";
$lang['no_record_found']                     = "No record found.";
$lang['valid_code']                          = "Valid code";
$lang['not_a_valid_code']                    = "Please enter a valid code.";
$lang['reset_password_done']                 = "Password changed successfully.";
$lang['reset_code']                          = "Reset Code";
$lang['password']                            = "Password";
$lang['confirmed_password']                  = "Confirmed Password";
$lang['select_other_than_previous_password'] = "Please select new password other than old password.";
$lang['not_a_valid_user']                    = "Not a valid user.";
$lang['email_phone_not_found']               = "This email is not registered with us.";
$lang['enter_valid_phone_no']                = "Please enter a valid phone no.";
$lang['signup_email_subject']                = "Stars League: OTP for phone number verification";
$lang['signup_email_message']       		 = "Your OTP to verify phone number #PHONE_NO# is #OTP#.";

$lang['welcome_email_subject']		= '['.SITE_TITLE.'] Thank you for registering with us';
$lang['dear']				= 'Dear';
$lang['email_paragraph1']	= "Welcome to ".SITE_TITLE.". We&apos;re happy to have you as our newest player on our website.";
$lang['email_paragraph3']	= "Please visit %s to confirm your email and activate your account.";
$lang['email_paragraph5']	= "Welcome to ".SITE_TITLE.", we wish you great games.";
$lang['cheers']				= 'Cheers';
$lang['team']				= SITE_TITLE.' Team';
$lang['footer_text']		= 'AUTOMATIC EMAIL - PLEASE DO NOT REPLY.';

$lang['invalid_link']				= 'This link has been expired.';
$lang['account_already_verified']	= "Your account has been already verified.";
$lang['account_confirm']			= 'Your account has been confirmed.';
$lang['email_not_verified'] = 'Please verify your email.';
$lang['reminder_email_subject'] = 'Reminder For Profile Complete';
$lang['lineup_out_push_title'] = 'Lineup Announced';
$lang['lineup_out_push_message'] = "The lineups for the %s match are announced. Hurry and edit your teams now!";

$lang["withdraw_email_approve_subject"] = "Money on the way";
$lang["withdraw_email_reject_subject"] 	= "Amount withdrawal Rejected";
$lang["withdraw_failed"] 				= "Our payment processor having some issue in clearing the payment in your account, please try after some time.";



/* End of file general_lang.php */
/* Location: ./application/language/english/general_lang.php */
