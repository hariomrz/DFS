<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['global_error'] = 'Mangyaring maglagay ng wastong mga parameter.';
$lang['invalid_status'] = 'Di-wastong Katayuan.';
$lang['valid_leaderboard_type'] = 'Di-wastong uri ng leaderboard.';
$lang['sports_id'] = 'sports id';
$lang['league_id'] = 'liga id';
$lang['collection_master_id'] = 'koleksyon master id';
$lang['player_uid'] = 'player uid';
$lang['player_team_id'] = 'player key';
$lang['contest_id'] = 'contest id';
$lang['contest_unique_id'] = 'natatanging paligsahan id';
$lang['lineup_master_id'] = 'lineup master id';
$lang['lineup_master_contest_id'] = 'lineup master contest id';
$lang['season_game_uid'] = 'season game uid';
$lang['no_of_match'] = 'bilang ng mga tugma';
$lang['against_team'] = 'Laban sa Koponan';
$lang['promo_code'] = 'Promo Code';
$lang['match_status'] = 'Katayuan sa pagtutugma';
$lang['lineup'] = 'lineup';
$lang['team_name'] = 'pangalan ng pangkat';
$lang['format'] = 'format';
$lang['join_code'] = 'sumali sa code';
$lang['prize_type'] = 'uri ng premyo';
$lang['salary_cap'] = 'takip ng suweldo';
$lang['size'] = 'size';
$lang['size_min'] = 'min size';
$lang['game_name'] = 'pangalan ng laro';
$lang['game_desc'] = 'game desc';
$lang['entry_fee'] = 'entry fee';
$lang['prize_pool'] = 'prize pool';
$lang['number_of_winners'] = 'bilang ng mga nagwagi';
$lang['prize_distribution_detail'] = 'detalye ng premyo';
$lang['disable_private_contest'] = "kasalukuyang tampok na ito hindi pinagana ng admin.";
$lang["contest_added_success"] = "matagumpay na nilikha.";
$lang["contest_added_error"] = "Problema habang lumilikha ng paligsahan. mangyaring subukang muli.";
$lang['currency_type'] = 'uri ng pera';
$lang["same_currency_prize_type"] = "Ang uri ng pera at uri ng premyo ay dapat na pareho.";

//generalmessage
$lang["lineup_required"] = "kailangan ng lineup";

//ContestLanguage
$lang['contest'] ["invalid_contest"] = "Mangyaring pumili ng wastong paligsahan.";
$lang['contest'] ["invalid_contest_code"] = "Hindi wastong code ng League.";
$lang['contest'] ["contest_not_found"] = "Hindi nakita ang mga detalye ng paligsahan.";
$lang['contest'] ["problem_while_join_game"] = "Problema habang sumali sa laro.";
$lang['contest'] ["contest_already_started"] = "Nagsimula na ang paligsahan.";
$lang['contest'] ["contest_already_full"] = "Napuno na ang paligsahan na ito.";
$lang['contest'] ["contest_closed"] = "Sarado ang paligsahan.";
$lang['contest'] ["not_enough_coins"] = "Hindi sapat ang mga barya.";
$lang['contest'] ["not_enough_balance"] = "Hindi sapat na balanse.";
$lang['contest'] ["join_game_success"] = "Matagumpay kang sumali sa paligsahan.";
$lang["contest"] ["invalid_promo_code"] = "Di-wastong promo code. mangyaring ipasok ang wastong code.";
$lang["contest"] ["allowed_limit_exceed"] = "Nagamit mo na ang promocode na ito para sa maximum na oras.";
$lang["contest"] ["promo_code_exp_used"] = "Nag-expire na ang promocode o nagamit na!";
$lang['contest'] ["you_already_joined_to_max_limit"] = "Sumali ka na sa patimpalak na ito hanggang sa maximum na limitasyon sa koponan.";
$lang['contest'] ["join_multiple_time_error"] = "Hindi ka maaaring sumali sa patimpalak na ito ng maraming oras.";
$lang['contest'] ["you_already_joined_this_contest"] = "Sumali ka na sa patimpalak na ito sa pamamagitan ng napiling lineup.";
$lang['contest'] ["provide_a_valid_lineup_master_id"] = "Mangyaring magbigay ng wastong lineup master id.";
$lang['contest'] ["not_a_valid_team_for_contest"] = "Hindi wastong koponan para sa patimpalak.";
$lang['contest'] ['exceed_promo_used_count'] = "Lumagpas ka sa pinapayagang ginamit na bilang.";
$lang['contest'] ['team_detail_not_found'] = "Pinoproseso namin ang data ng koponan.";
$lang['contest'] ["invalid_previous_team_for_collecton"] = "Di-wastong nakaraang koponan para sa napiling paligsahan.";
$lang['contest'] ["team_switch_success"] = "Matagumpay na lumipat ang koponan.";
$lang['contest'] ["invalid_team_for_collecton"] = "Di-wastong pila para sa napiling paligsahan.";
$lang['contest'] ['processing_team_pdf_data'] = "Pinoproseso namin ang data ng koponan, magagamit ito sa lalong madaling panahon.";
$lang['contest'] ["join_game_email_subject"] = "Ang iyong pagsali sa patimpalak ay nakumpirma!";
$lang['contest_cancel_mail_subject'] = '[' .SITE_TITLE. '] Impormasyon sa pagkansela ng paligsahan';
$lang['contest'] ["process_contest_pdf"] = "Pinoproseso namin ang koponan ng pdf, magagamit ito sa lalong madaling panahon.";

//mulitple team join
$lang['contest'] ["select_min_one_team"] = "Mangyaring pumili atleast isang koponan.";
$lang['contest'] ["already_joined_with_teams"] = "Paumanhin sumali ka na sa patimpalak na ito ng mga piling koponan.";
$lang['contest'] ["contest_max_allowed_team_limit_exceed"] = "Paumanhin hindi ka maaaring sumali sa patimpalak na ito sa higit pang mga koponan ng {TEAM_LIMIT}.";
$lang['contest'] ["problem_while_join_game_some_team"] = "Problema habang sumali sa laro sa mga koponan ng {TEAM_COUNT}.";
$lang['contest'] ["multiteam_join_game_success"] = "Matagumpay kang sumali sa paligsahan sa {TEAM_COUNT} (na) koponan.";
$lang['contest']["rookie_user_not_allowed_for_this_contest"] = "Hindi ka pinapayagang sumali sa paligsahan na ito";
$lang['contest']["max_usage_limit_code"] = "Ang pinakamataas na paggamit para sa limitasyon ng promo-code na ito ay lumampas";

//Lineup Language
$lang['lineup'] = array ();
$lang['lineup'] ["contest_not_found"] = "Hindi nahanap ang paligsahan.";
$lang['lineup'] ["contest_started"] = "Nagsimula na ang paligsahan.";
$lang['lineup'] ["match_detail_not_found"] = "Hindi nahanap ang mga detalye ng pagtutugma.";
$lang['lineup'] ['invalid_collection_player'] = "Di-wastong napiling mga manlalaro. mangyaring i-reset ang lineup ng koponan at lumikha ng bago.";
$lang['lineup'] ["lineup_not_exist"] = "Walang Koponan";
$lang['lineup'] ['team_name_already_exist'] = 'Mayroon nang pangalan ng pangkat.';
$lang['lineup'] ["lineup_team_rquired"] = "Kinakailangan ang koponan ng liga ng player.";
$lang['lineup'] ["lineup_player_id_required"] = "Kinakailangan ang natatanging id ng player.";
$lang['lineup'] ["lineup_player_team_required"] = "Kinakailangan ang team ng player.";
$lang['lineup'] ["position_invalid"] = "hindi wastong posisyon";
$lang['lineup'] ["salary_required"] = "Kinakailangan ang suweldo ng manlalaro.";
$lang['lineup'] ["lineup_player_id_duplicate"] = "Hindi ka maaaring pumili ng solong manlalaro ng dalawang beses sa oras";
$lang['lineup'] ["lineup_max_limit"] = "Dapat mong piliin ang %s mga manlalaro upang lumikha ng koponan.";
$lang['lineup'] ["lineup_team_limit_exceeded"] = "Mangyaring itama ang iyong lineup. Maaari kang pumili ng maximum %s na mga manlalaro mula sa isang koponan.";
$lang['lineup'] ["position_exceeded_invalid"] = "Lumagpas ka sa limitasyon sa posisyon ng manlalaro.";
$lang['lineup'] ["salary_cap_not_enough"] = "Ang suweldo ng mga manlalaro na lumampas sa max na magagamit na sahod.";
$lang['lineup'] ["lineup_posisiotn_not_found"] = "Mangyaring piliin ang %s player";
$lang['lineup'] ['already_created_same_team'] = "Nilikha mo na ang koponan na ito.";
$lang['lineup'] ["lineup_success"] = "Matagumpay mong nilikha ang koponan";
$lang['lineup'] ["lineup_update_success"] = "Matagumpay na na-update ang iyong koponan";
$lang['lineup'] ["lineup_captain_error"] = "Kailangan ng kapitan ng koponan";
$lang['lineup'] ["lineup_vice_captain_error"] = "Kinakailangan ng bise kapitan ng koponan";
$lang['lineup'] ['team_detail_not_found'] = "Hindi nakita ang mga detalye ng koponan.";
$lang['lineup']["allow_team_limit_error"] = "Hindi ka makakalikha ng higit pang %s na mga koponan.";
$lang['captain'] = "Kapitan";
$lang['vice_captain'] = "Vice Captain";

$lang['tr_lineup'] = array();
$lang['tr_lineup']["match_not_found"] = "Hindi nahanap na tugma.";
$lang['tr_lineup']["match_started"] = "Nagsimula na ang laban.";
$lang['tr_lineup'] ['invalid_match_player'] = "Di-wastong napiling mga manlalaro. mangyaring i-reset ang lineup ng koponan at lumikha ng bago.";

$lang["tournament_season_id"] = "Tournament Season ID";
$lang["user_tournament_season_id"] = "User tournament Season ID";
$lang["tournament_id"] = "Tournament ID";
$lang["tournament_team_id"] = "Tournament Team ID";


$lang['tournament']["invalid_tournament"]                   ="Mangyaring pumili ng wastong paligsahan.";
$lang['tournament']["invalid_tournament_code"]              ="Hindi isang wastong Tournament code.";
$lang['tournament']["tournament_not_found"]                 ="Tournament detalyeng hindi natagpuan.";
$lang['tournament']["problem_while_join_tournament"]        ="Problema habang sumali sa paligsahan.";
$lang['tournament']["match_already_started"]                ="Itugma ang nasimulan.";
$lang['tournament']["tournament_closed"]                    ="Tournament sarado.";
$lang['tournament']["not_enough_coins"]                     ="Hindi sapat ang barya.";
$lang['tournament']["not_enough_balance"]                   ="Hindi sapat na balanse.";
$lang['tournament']["join_tournament_success"]              ="Sumali ka tournament matagumpay.";
$lang["tournament"]["invalid_promo_code"]                   ="Di-wastong promo code. mangyaring ipasok ang wastong code.";
$lang["tournament"]["allowed_limit_exceed"]                 ="Nakuha mo na ginagamit ito promocode para sa maximum na oras.";
$lang["tournament"]["promo_code_exp_used"]                  ="Promocode ay nag-expire o ginagamit!";
$lang['tournament']["join_multiple_time_error"]             ="Hindi ka makakasali sa tournament na ito ng maramihang mga pagkakataon.";
$lang['tournament']["you_already_joined_this_contest"]="Mayroon ka nang sumali sa laban na ito sa pamamagitan ng mga napiling lineup.";
$lang['tournament']["provide_a_valid_tournament_team_id"]   ="Mangyaring magbigay ng wastong team tournament id.";
$lang['tournament']["not_a_valid_team_for_match"]           ="Hindi isang wastong team para sa mga tugma.";
$lang['tournament']['exceed_promo_used_count']              ="Ikaw ay lumampas sa pinapayagan na ginamit count.";
$lang['tournament']['team_detail_not_found']                ="Pinoproseso namin ang team data.";
$lang['tournament']["team_switch_success"]                  ="Team inililipat matagumpay.";
$lang['tournament']["invalid_team_for_match"]               ="Di-wastong lineup para sa mga napiling tugma.";
$lang['tournament']['processing_team_pdf_data']             ="Pinoproseso namin ang team ng data, ito ay magagamit sa lalong madaling panahon.";
$lang['tournament']["join_tournament_email_subject"]        ="Ang iyong tournament pagsali ay nakumpirma na!";
$lang['tournament_cancel_mail_subject']                     ="[".SITE_TITLE."] Tournament impormasyon pagkansela";
$lang['tournament']["process_contest_pdf"]                  ="Pinoproseso namin ang team pdf, ito ay magagamit sa lalong madaling panahon.";
$lang['tournament']["state_banned_error"]                   ="Paumanhin, ngunit manlalaro mula sa {{STATE_LIST}} ay hindi nangakapasok bayad na paligsahan.";
$lang['tournament']["state_required_error"]                 ="Mangyaring i-update ang estado sa iyong profile.";

$lang['tournament']["join_tournament_to_continue"] ="Mangyaring sumali sa tournament upang magpatuloy";
$lang['tournament']["join_match_success"] ="Sumali ka match matagumpay.";

$lang['tournament']["err_tournament_cancelled"] ="Ang tournament na ito ay kinansela";
$lang['tournament']["err_tournament_completed"] ="Ang tournament na ito ay nakumpleto";
//private contest
$lang["enter_valid_sport_id"] = "Mangyaring ipasok ang wastong sport id.";
$lang["enter_valid_season_game_uid"] = "Mangyaring ipasok ang wastong match id.";

$lang['group_name_1'] = "Mega Contest";
$lang['group_description_1'] = "Ipasok ang pinakamainit na paligsahan na may mega premyo.";

$lang['group_name_9'] = "Mainit na Paligsahan";
$lang['group_description_9'] = "Walang sinabi na magiging madali";

$lang['group_name_8'] = "Gang War";
$lang['group_description_8'] = "Kapag ang iyong koponan ang iyong sandata";

$lang['group_name_2'] = "Head2Head";
$lang['group_description_2'] = "Pakiramdam ang pangingilig sa panghuli ng isa sa isang Fantasy Face.";

$lang['group_name_10'] = "Nagwagi ang Lahat sa Nagwagi";
$lang['group_description_10'] = "Malaking Peligro, Mas Malalaking Gantimpala!";

$lang['group_name_3'] = "Nangungunang 50% panalo";
$lang['group_description_3'] = "Ang kalahati ng mga manlalaro ay panigurado. Ipasok at subukan ang iyong kapalaran!";

$lang['group_name_11'] = "Lahat Nagwagi";
$lang['group_description_11'] = "Isang bagay para sa lahat.";

$lang['group_name_4'] = "Para sa Mga Nagsisimula";
$lang['group_description_4'] = "I-play ang iyong kauna-unahang patimpalak ngayon";

$lang['group_name_5'] = "Higit Pang Paligsahan";
$lang['group_description_5'] = "No fuss! Ito ang iyong zone upang maglaro nang libre.";

$lang['group_name_6'] = "Libreng Paligsahan";
$lang['group_description_6'] = "Walang abala! Ito ang iyong zone upang maglaro nang libre at manalo ng cash";

$lang['group_name_7'] = "Pribadong Paligsahan";
$lang['group_description_7'] = "Eksklusibo ito at nakakatuwa! Maglaro kasama ang iyong mga kaibigan ngayon.";

$lang['group_name_12'] = "Paligsahan para sa Mga Champions";
$lang['group_description_12'] = "Paligsahan para sa mga kampeon";

$lang['module_disable_error'] = "Paumanhin, ang module na ito ay hindi pinagana. mangyaring makipag-ugnay sa admin.";

$lang['file_upload_error'] = "Paumanhin, mayroong ilang isyu sa pag-upload ng file. mangyaring subukang muli.";
$lang['players'] = "players";

$lang['module_not_activated'] = "Hindi na-aktibo ang module";

$lang['multiple_lineup'] = 'maraming mga lineup';
$lang['lineup'] ['team_generate_error'] = "Paumanhin, ang ilang problema habang ang koponan ay bumubuo. Pakiulit muli.";
$lang['contest'] ["self_exclusion_limit_reached"] = "Hindi makasali sa paligsahan, lumagpas sa pagsasama ng limitasyon.";
$lang['contest'] ["state_banned_error"] = "Paumanhin, ngunit ang mga manlalaro mula sa {{STATE_LIST}} ay hindi makapasok sa bayad na paligsahan.";
$lang['contest'] ["state_required_error"] = "Mangyaring i-update ang estado sa iyong profile.";

$lang['invalid_booster_id'] = "Paumanhin, Di-wastong booster id para sa tugma.";
$lang['save_booster_error'] = "Paumanhin, mayroong ilang isyu habang naglalapat ng booster. ulitin mo ulit.";
$lang['save_booster_success'] = "Matagumpay na nag-apply ang Booster.";
$lang['update_booster_success'] = "Matagumpay na na-update ang booster.";
$lang['booster_only_for_dfs'] = "Paumanhin, nalalapat lamang ang Booster sa mga klasikong pangkat ng pantasiya.";
$lang['invalid_team_for_match'] = "Di-wastong koponan para sa tugma.";

$lang['err_2nd_inning_format_contest'] = "Hindi ka maaaring lumikha ng 2nd inning pribadong paligsahan para sa (t10 / test match)";

//bench module
$lang['max_bench_limit_error'] = "Max 4 bench players pinapayagan.";
$lang['invalid_collection_bench_player'] = "Di-wastong mga napiling manlalaro id. mangyaring piliin valid players.";
$lang['bench_player_team_pl_error'] = "Bench players ay dapat na naiiba mula sa mga manlalaro ng koponan.";
$lang['bench_player_save_error'] = "Paumanhin, mayroong ilang mga isyu habang makatipid bench players. Pakiulit muli.";
$lang['save_bench_player_success'] = "Bench players matagumpay na nai-save.";
$lang["bench_process_waiting_error"] = "Team ay magiging available sa lalong madaling panahon, subukan muli sa ilang sandali.";

$lang['invalid_prize_distribution_error'] = "Di-wastong mga detalye sa pamamahagi ng premyo. Mangyaring subukan sa tamang data.";
$lang['invalid_prize_pool_error'] = "Mangyaring magbigay ng tamang premyo pool.";

$lang['contest']["h2h_game_join_limit_error"] = "Paumanhin hindi ka maaaring sumali nang higit pa pagkatapos {CONTEST_LIMIT} H2H contest.";

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