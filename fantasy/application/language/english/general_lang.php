<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['global_error'] = 'Please input valid parameters.';
$lang['invalid_status'] = 'Invalid Status.';
$lang['valid_leaderboard_type'] = 'Invalid leaderboard type.';
$lang['sports_id'] = 'sports id';
$lang['league_id'] = 'league id';
$lang['collection_master_id'] = 'collection master id';
$lang['player_uid'] = 'player uid';
$lang['player_team_id'] = 'player key';
$lang['contest_id'] = 'contest id';
$lang['contest_unique_id'] = 'contest unique id';
$lang['lineup_master_id'] = 'lineup master id';
$lang['lineup_master_contest_id'] = 'lineup master contest id';
$lang['season_game_uid'] = 'season game uid';
$lang['no_of_match'] = 'number of matches';
$lang['against_team'] = 'Against Team';
$lang['promo_code'] = 'Promo Code';
$lang['match_status'] = 'Match status';
$lang['lineup'] = 'lineup';
$lang['team_name'] = 'team name';
$lang['format'] = 'format';
$lang['join_code'] = 'join code';
$lang['prize_type'] = 'prize type';
$lang['salary_cap'] = 'salary cap';
$lang['size'] = 'size';
$lang['size_min'] = 'min size';
$lang['game_name'] = 'game name';
$lang['game_desc'] = 'game desc';
$lang['entry_fee'] = 'entry fee';
$lang['prize_pool'] = 'prize pool';
$lang['number_of_winners'] = 'number of winners';
$lang['prize_distribution_detail'] = 'prize detail';
$lang['multiple_lineup'] = 'multiple lineups';
$lang['disable_private_contest'] = "currently this feature disabled by admin.";
$lang["contest_added_success"] = "contest created successfully.";
$lang["contest_added_error"] = "Problem while contest create. please try again.";
$lang['currency_type'] = 'currency type';
$lang["same_currency_prize_type"] = "currency type and prize type should be same.";

//generalmessage
$lang["lineup_required"] = "lineup required";

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
$lang['contest']["you_already_joined_to_max_limit"] = "You already joined with this contest to maximum team limit.";
$lang['contest']["join_multiple_time_error"] = "You can not join this contest multiple time.";
$lang['contest']["you_already_joined_this_contest"]	= "You already joined this contest by selected lineup.";
$lang['contest']["provide_a_valid_lineup_master_id"] = "Please provide valid lineup master id.";
$lang['contest']["not_a_valid_team_for_contest"] = "Not a valid team for the contest.";
$lang['contest']['exceed_promo_used_count'] = "You have exceed allowed used count.";
$lang['contest']['team_detail_not_found'] = "We are processing team data.";
$lang['contest']["invalid_previous_team_for_collecton"]	= "Invalid previous team for selected contest.";
$lang['contest']["team_switch_success"] = "Team switched successfully.";
$lang['contest']["invalid_team_for_collecton"] = "Invalid lineup for selected contest.";
$lang['contest']['processing_team_pdf_data'] = "We are processing team data, it will available soon.";
$lang['contest']["join_game_email_subject"] = "Your contest joining is confirmed!";
$lang['contest_cancel_mail_subject'] = '['.SITE_TITLE.'] Contest cancellation information';
$lang['contest']["process_contest_pdf"] = "We are processing team pdf, it will available soon.";
$lang['contest']["self_exclusion_limit_reached"] = "Cannot join contest, Joining limit exceed.";
$lang['contest']["state_banned_error"] = "Sorry, but players from {{STATE_LIST}} are not able to enter in paid contest.";
$lang['contest']["state_required_error"] = "Please update state in your profile.";

//mulitple team join
$lang['contest']["select_min_one_team"] = "Please select atleast one team.";
$lang['contest']["already_joined_with_teams"] = "Sorry you have already joined this contest by selected team(s).";
$lang['contest']["contest_max_allowed_team_limit_exceed"] = "Sorry you can't join this contest with more then {TEAM_LIMIT} teams.";
$lang['contest']["problem_while_join_game_some_team"] = "Problem while join game with {TEAM_COUNT} teams.";
$lang['contest']["multiteam_join_game_success"] = "You have successfully joined contest with {TEAM_COUNT} team(s).";
$lang['contest']["rookie_user_not_allowed_for_this_contest"] = "you are not allowed to join this contest";

$lang['contest']["max_usage_limit_code"] = "Maximum usage for this promo-code limit exceeded";

//Lineup Language
$lang['lineup'] = array();
$lang['lineup']["contest_not_found"] = "Contest not found.";
$lang['lineup']["contest_started"] = "Contest already started.";
$lang['lineup']["match_detail_not_found"] = "Match details not found.";
$lang['lineup']['invalid_collection_player'] = "Invalid selected players. please reset team lineup and create new one.";
$lang['lineup']["lineup_not_exist"] = "Team Not exist";
$lang['lineup']['team_name_already_exist'] = 'Team name already exist.';
$lang['lineup']["lineup_team_rquired"] = "Player league team id required.";
$lang['lineup']["lineup_player_id_required"] = "Player unique id required.";
$lang['lineup']["lineup_player_team_required"] = "Player team id required.";
$lang['lineup']["position_invalid"] = "invalid position";
$lang['lineup']["salary_required"] = "player salary required.";
$lang['lineup']["lineup_player_id_duplicate"] = "You can't select single player twice time";
$lang['lineup']["lineup_max_limit"] = "You should select %s players to create team.";
$lang['lineup']["lineup_team_limit_exceeded"] = "Please correct your lineup. You can select maximum %s players from one team.";
$lang['lineup']["position_exceeded_invalid"] = "You have exceeded player position limit.";
$lang['lineup']["salary_cap_not_enough"] = "Players salary exceeding max available salary.";
$lang['lineup']["lineup_posisiotn_not_found"] = "Please select %s player";
$lang['lineup']['already_created_same_team'] = "You have already created this team.";
$lang['lineup']["lineup_success"] = "You have created team successfully";
$lang['lineup']["lineup_update_success"] = "Your team has been updated successfully";
$lang['lineup']["lineup_captain_error"] = "Team captain required";
$lang['lineup']["lineup_vice_captain_error"] = "Team vice captain required";
$lang['lineup']['team_detail_not_found'] = "Team details not found.";
$lang['lineup']['team_generate_error'] = "Sorry, Some problem while team generate. please try again.";
$lang['lineup']['c_vc_same_error'] = "Captain and Vice Captain should be different.";
$lang['lineup']['username_empty_error'] = "Please update username from profile section before joining contest.";
$lang['lineup']["allow_team_limit_error"] = "You can't create more then {team_limit} teams.";
$lang['captain'] = "Captain";
$lang['vice_captain'] = "Vice Captain";

$lang['tr_lineup'] = array();
$lang['tr_lineup']["match_not_found"] = "Match not found.";
$lang['tr_lineup']["match_started"] = "Match already started.";
$lang['tr_lineup']["tournament_season_id"] = "Tournament season ID";
$lang['tr_lineup']['invalid_match_player'] = "Invalid selected players. please reset team lineup and create new one.";

$lang['lineup']['team_generate_error'] = "Sorry, Some problem while team generate. please try again.";

$lang["tournament_season_id"] = "Tournament Season ID";
$lang["user_tournament_season_id"] = "User tournament Season ID";
$lang["tournament_id"] = "Tournament ID";
$lang["tournament_team_id"] = "Tournament Team ID";

//ContestLanguage
$lang['tournament']["invalid_tournament"]                   = "Please select a valid tournament.";
$lang['tournament']["invalid_tournament_code"]              = "Not a valid Tournament code.";
$lang['tournament']["tournament_not_found"]                 = "Tournament details not found.";
$lang['tournament']["problem_while_join_tournament"]        = "Problem while join tournament.";
$lang['tournament']["match_already_started"]                = "Match already started.";
$lang['tournament']["tournament_closed"]                    = "Tournament closed.";
$lang['tournament']["not_enough_coins"]                     = "Not enough coins.";
$lang['tournament']["not_enough_balance"]                   = "Not enough balance.";
$lang['tournament']["join_tournament_success"]              = "You have joined tournament successfully.";
$lang["tournament"]["invalid_promo_code"]                   = "Invalid promo code. please enter valid code.";
$lang["tournament"]["allowed_limit_exceed"]                 = "You have already used this promocode for the maximum time.";
$lang["tournament"]["promo_code_exp_used"]                  = "Promocode is expired or already used!";
$lang['tournament']["join_multiple_time_error"]             = "You can not join this tournament multiple time.";
$lang['tournament']["you_already_joined_this_contest"]	    = "You already joined this match by selected lineup.";
$lang['tournament']["provide_a_valid_tournament_team_id"]   = "Please provide valid tournament team id.";
$lang['tournament']["not_a_valid_team_for_match"]           = "Not a valid team for the match.";
$lang['tournament']['exceed_promo_used_count']              = "You have exceed allowed used count.";
$lang['tournament']['team_detail_not_found']                = "We are processing team data.";
$lang['tournament']["team_switch_success"]                  = "Team switched successfully.";
$lang['tournament']["invalid_team_for_match"]               = "Invalid lineup for selected match.";
$lang['tournament']['processing_team_pdf_data']             = "We are processing team data, it will available soon.";
$lang['tournament']["join_tournament_email_subject"]        = "Your tournament joining is confirmed!";
$lang['tournament_cancel_mail_subject']                     = '['.SITE_TITLE.'] Tournament cancellation information';
$lang['tournament']["process_contest_pdf"]                  = "We are processing team pdf, it will available soon.";
$lang['tournament']["state_banned_error"]                   = "Sorry, but players from {{STATE_LIST}} are not able to enter in paid tournament.";
$lang['tournament']["state_required_error"]                 = "Please update state in your profile.";

$lang['tournament']["join_tournament_to_continue"]                 = "Please join tournament to continue";
$lang['tournament']["join_match_success"]              = "You have joined match successfully.";

$lang['tournament']["err_tournament_cancelled"] ="This tournament is canceled";
$lang['tournament']["err_tournament_completed"] ="This tournament is completed";
//private contest
$lang["enter_valid_sport_id"] = "Please enter valid sport id.";
$lang["enter_valid_season_game_uid"] = "Please enter valid match id.";

$lang['group_name_1'] ="Mega Contest";
$lang['group_description_1'] ="Enter the hottest contest with mega prizes.";

$lang['group_name_9'] ="Hot Contest";
$lang['group_description_9'] ="No one said it's gonna be easy";

$lang['group_name_8'] ="Gang War";
$lang['group_description_8'] ="When your team is your weapon";

$lang['group_name_2'] ="Head2Head";
$lang['group_description_2'] ="Feel the thrill of the ultimate one on one Fantasy Face off.";

$lang['group_name_10'] ="Winner Takes All";
$lang['group_description_10'] ="Big Risk, Bigger Reward!";

$lang['group_name_3'] ="Top 50% win";
$lang['group_description_3'] ="Half the players win for sure. Enter and try your luck!";

$lang['group_name_11'] ="Everyone Wins";
$lang['group_description_11'] ="Something for everybody.";

$lang['group_name_4'] ="Only for Beginners";
$lang['group_description_4'] ="Play your very first contest now";

$lang['group_name_5'] ="More Contest";
$lang['group_description_5'] ="No fuss! This is your zone to play for free.";

$lang['group_name_6'] ="Free Contest";
$lang['group_description_6'] ="No fuss! This is your zone to play for free & win cash";

$lang['group_name_7'] ="Private Contest";
$lang['group_description_7'] ="It's exclusive and it's fun! Play with your friends now.";

$lang['group_name_12'] ="Contest for Champions";
$lang['group_description_12'] ="Contest for champions";

$lang['module_disable_error'] = "Sorry, this module not enabled. please contact admin.";

$lang['file_upload_error'] = "Sorry, there is some issue with file upload. please try again.";
$lang['players'] = "players";

$lang['module_not_activated'] = "Module not activated";

$lang['player_detail_not_found'] = "Player details not found.";

$lang['invalid_booster_id'] = "Sorry, Invalid booster id for match.";
$lang['save_booster_error'] = "Sorry, there is some issue while apply booster. please try again.";
$lang['save_booster_success'] = "Booster applied successfully.";
$lang['update_booster_success'] = "Booster updated successfully.";
$lang['booster_only_for_dfs'] = "Sorry, Booster only applicable on classic fantasy teams.";
$lang['invalid_team_for_match'] = "Invalid team for match.";

$lang['err_2nd_inning_format_contest'] = "You cannot create 2nd Inning private contest for (T10 /Test match)";

//bench module
$lang['max_bench_limit_error'] = "Max 4 bench players allowed.";
$lang['invalid_collection_bench_player'] = "Invalid selected players id. please select valid players.";
$lang['bench_player_team_pl_error'] = "Bench players should be different from team players.";
$lang['bench_player_save_error'] = "Sorry, there is some issue while save bench players. please try again.";
$lang['save_bench_player_success'] = "Bench players saved successfully.";
$lang["bench_process_waiting_error"] = "Team will be available soon, try again in sometime.";

$lang['invalid_prize_distribution_error'] = "Invalid prize distribution details. Please try with correct data.";
$lang['invalid_prize_pool_error'] = "Please provide correct prize pool.";

$lang['contest']["h2h_game_join_limit_error"] = "Sorry you can't join more then {CONTEST_LIMIT} H2H contest.";


$lang["match_started_error"] = "Match already started. You can't save your team.";
$lang["lineup_player_limit"] = "{player_limit} players required to create team.";
$lang["lineup_team_limit_exceeded"] = "You can select maximum {team_player_limit} players from one team.";
$lang["team_save_success"] = "Your team has been saved successfully";
$lang["team_save_error"] = "Error! while saving team. please try again.";
$lang["team_detail_not_found"] = "Sorry, Team details not found.";
$lang["team_view_not_allowed"] = "Please wait till the match start to view other user team.";
$lang["guru_allowed_dfs"] = "This module only available for single fixture game.";
$lang["invalid_cm_id"] = "Invalid match id. Please provide valid match id.";
$lang["cm_started_pc_error"] = "Match already started. You can't create contest.";