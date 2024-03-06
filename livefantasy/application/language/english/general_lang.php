<?php defined('BASEPATH') or exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['input_invalid_format']               = 'Input format is invalid.';
$lang['global_error']                       = 'Please input valid parameters.';
$lang['status']                             = 'Status';
$lang['invalid_status']                     = 'Invalid Status.';
$lang['valid_leaderboard_type']             = 'Invalid leaderboard type.';
$lang['sports_id']                          = 'sports id';
$lang['league_id']                          = 'league id';
$lang['collection_master_id']               = 'collection master id';
$lang['collection_id']                      = 'collection id';
$lang['player_uid']                         = 'player uid';
$lang['player_team_id']                     = 'player key';
$lang['contest_id']                         = 'contest id';
$lang['contest_unique_id']                  = 'contest unique id';
$lang['lineup_master_id']                   = 'lineup master id';
$lang['lineup_master_contest_id']           = 'lineup master contest id';
$lang['season_game_uid']                    = 'season game uid';
$lang['no_of_match']                        = 'number of matches';
$lang['against_team']                       = 'Against Team';
$lang['promo_code']                         = 'Promo Code';
$lang['match_status']                       = 'Match status';
$lang['lineup']                             = 'lineup';
$lang['team_name']                          = 'Portfolio name';
$lang['format']                             = 'format';
$lang['join_code']                          = 'join code';
$lang['prize_type']                         = 'prize type';
$lang['salary_cap']                         = 'salary cap';
$lang['size']                               = 'size';
$lang['size_min']                           = 'min size';
$lang['game_name']                          = 'game name';
$lang['category']                           = 'Category';
$lang['game_desc']                          = 'game desc';
$lang['entry_fee']                          = 'entry fee';
$lang['prize_pool']                         = 'prize pool';
$lang['number_of_winners']                  = 'number of winners';
$lang['prize_distribution_detail']          = 'prize detail';
$lang['disable_private_contest']            = "currently this feature disabled by admin.";
$lang["contest_added_success"]              = "contest created successfully.";
$lang["contest_added_error"]                = "Problem while contest create. please try again.";
$lang['leaderboard_type']                   = 'leaderboard type';
$lang['user_id']                            = 'user id';
$lang["source"]                             = "source";
$lang['file_not_found']                     = 'You did not select a file to upload.';
$lang['invalid_image_size']                 = 'Please upload image file max {size} size';
$lang['invalid_image_ext']                  = 'Please upload image with %s extension only';
$lang["icon_upload_success"]                = 'Image uploaded successfully';
$lang["image_removed"]                      = "image removed successfully.";
$lang["image_removed_error"]                = "Sorry, something went wrong while remove image.";
$lang['file_upload_error']                  = 'Sorry, there is some issue with file upload. please try again.';
$lang['match_not_found_msg']                = "Match details not found";
$lang['match_custom_msg_sent']              = "Custom message added successfully.";
$lang['match_custom_msg_remove']            = "Custom message removed successfully.";
$lang["successfully_cancel_collection"]     = "Fixture over has been successfully cancelled";
$lang["successfully_cancel_contest"]        = "Contest have been successfully cancelled";
$lang["successfully_cancel_fixture"]     = "Fixture has been cancelled successfully.";
$lang["no_contest_for_cancel"]     = "There is not contest for cancel.";
$lang["delete_contest"]                     = "Contest deleted successfully";
$lang["no_change"]                          = "No change";
$lang["successfully_cancel_over"]  = "Fixture over has been successfully cancelled";
$lang["error_cancel_over"]  = "Fixture over is not cancelled. Please try again.";
$lang['invalid_image_dimension']            = 'Please upload image of size less than or equal to {max_width}x{max_height} ';
$lang['type']                               = 'type';
$lang['filter']                             = 'filter';

//merchandise
$lang["merchandise_id"]                     = "merchandise id";
$lang["merchandise_name"]                   = "merchandise name";
$lang["merchandise_price"]                  = "merchandise price";
$lang["merchandise_image"]                  = "merchandise image";
$lang['error_in_add_merchandise']           = "Error in adding merchandise.";
$lang['success_add_merchandise']            = "Merchandise added successfully.";
$lang['merchandise_not_found']              = 'Merchandise not found.';
$lang['merchandise_updated']                = 'Merchandise updated successfully.';
$lang['update_error']                       = 'Error in updating merchandise.';
$lang['merchandise_invalid_image_size']     = 'Please upload image of size less than or equal to {max_width}x{max_height} ';

//groups
$lang["get_group_success"]      = "Get group list successfully";
$lang["get_group_error"]        = 'Problem in fetching group list.';
$lang["delete_group_success"]   = 'Group deleted successfully';
$lang["delete_group_error"]     = 'Problem in deleting group.';
$lang["cannot_delete_group"]    = 'You can\'t delete this group, because contest is created within this group.';
$lang["group_id"]               = 'Group id';
$lang["group_name"]             = 'Group Name';
$lang["description_val"]        = 'Group Description';
$lang["icon_val"]               = 'Icon Name';
$lang["sort_order"]             = 'Display order';
$lang["group_update_success"]   = 'Group updated successfully';
$lang["group_update_error"]     = 'Problem in updating group.';
$lang["create_group_success"]   = 'Category added successfully';
$lang["create_group_error"]     = 'Problem in creating category, please try after some time.';
$lang["duplicate_group_error"]  = 'Sorry this group name already exist.';

//admin
$lang['invalid_input_parameter'] = "Invalid input parameter";
$lang['publish_season_error'] = "Unable to publish fixture";
$lang['publish_season_success'] = "Contest added successfully.";
$lang['match_delay_message'] = "Match delay marked successfully.";
$lang['match_delay_0_48_msg'] = "Delay hours should be 0 to 47";
$lang['match_delay_0_59_msg'] = "Delay minute should be 0 to 59";
$lang['match_delay_hour_minute_msg'] = "Hour or minute should be greater than 0";
$lang['match_start_delay_msg'] = "Match already started. You can't add delay.";
$lang['match_prepond_time_limit_msg'] = "You can't prepone match before current time.";
$lang['match_prepond_delay_msg'] = "You can't prepond match.";
$lang['match_not_found_msg'] = "Match details not found";
$lang['match_custom_msg_sent'] = "Custom message send successfully.";
$lang['collection_not_found_msg'] = "Contest not created for this match. Please create contest first.";


//ContestLanguage
$lang['contest']["invalid_contest"] = "Please select a valid contest.";
$lang['contest']["invalid_contest_code"] = "Not a valid League code.";
$lang['contest']["contest_not_found"] = "Contest details not found.";
$lang['contest']["problem_while_join_game"] = "Problem while join game.";
$lang['contest']["contest_already_started"] = "Contest already started.";
$lang['contest']["contest_already_full"] = "This contest already full.";
$lang['contest']["contest_closed"] = "Contest closed.";
$lang['contest']["not_enough_coins"] = "Not enough coins.";
$lang['contest']["not_enough_balance"] = "Not enough balance.";
$lang['contest']["join_game_success"] = "You have joined contest successfully.";
$lang["contest"]["invalid_promo_code"] = "Invalid promo code. please enter valid code.";
$lang["contest"]["allowed_limit_exceed"] = "You have already used this promocode for the maximum time.";
$lang["contest"]["promo_code_exp_used"] = "Promocode is expired or already used!";
$lang['contest']["you_already_joined_to_max_limit"] = "You already joined with this contest to maximum entry limit.";
$lang['contest']["join_multiple_time_error"] = "You can not join this contest multiple time.";
$lang['contest']["you_already_joined_this_contest"]	= "You already joined this contest by selected lineup.";
$lang['contest']['exceed_promo_used_count'] = "You have exceed allowed used count.";
$lang['contest']["join_game_email_subject"] = "Your contest joining is confirmed!";
$lang['contest']["max_usage_limit_code"] = "Maximum usage for this promo-code limit exceeded";
$lang['contest']["state_banned_error"] = "Sorry, but players from {{STATE_LIST}} are not able to enter in paid contest.";
$lang['contest']["state_required_error"] = "Please update state in your profile.";
$lang["status_upd_success"]				= "Status has been updated successfully.";
$lang["invalid_inn_over"]				= "Invalid inn over value.";
$lang["market_scoring_updated"]				= "Points has been successfully updated.";
$lang["market_scoring_updated_error"]	= "Sorry! Points is not update. Please try again.";
$lang["invalid_maket_odds"]	= "Invalid market odds.";
$lang["invalid_ball_status"]	= "Invalid status.";
$lang["ball_status_not_change_to_play"]	= "Sorry! Stauts is not changed to play. Please try again.";
$lang["invalid_status_for_play"]	= "Ball status can not changed. Ball has already play.";
$lang["ball_status_change_successfully"] = "Ball status has been successfully changed.";
$lang["ball_status_not_available_for_update"] = "Ball status has already changed. Now points can not update.";
$lang["ball_can_not_undo"] = "This ball can not mark undo because next ball is marked play."; 
$lang["ball_result_saved"] = "Ball result has been successfully saved."; 
$lang["ball_result_updated_error"] = "Sorry! Ball result is not saved. Please try again."; 
$lang["previous_status_not_stm"] = "You have not update odds for this ball."; 
$lang["ball_result_already_updated"] = "Sorry! Ball result already saved."; 
$lang["previous_ball_should_closed"] = "Please saved previous balls result."; 
$lang["update_batsman_bowler"] = "Please update points first."; 

$lang['inavlid_market_id'] = "Inavlid market id.";
$lang['already_predict'] = "You have already submitted answer.";
$lang['predict_time_over'] = "Sorry, Answer predict time is over.";
$lang['user_team_not_found'] = "User team not found.";

$lang['private_contest']['invalid_visibility'] 		= 'Invalid visibility value provided.';
$lang['private_contest']['visibility_updated'] 		= 'Visibility value updated successfully.';
$lang['private_contest']['rake_updated'] 			= 'Rake percentage value updated successfully.';
$lang['private_contest']['invalid_rake_percentage'] = 'Sum of owner commission and admin commission percentage must be between 0 & 100.';