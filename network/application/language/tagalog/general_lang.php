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


$lang["problem_while_join_game_network"] = "Problema habang sumali sa laro! Mangyaring subukang muli.";
$lang["action_cant_completed_err"] = "Hindi nakumpleto ang pagkilos! Mangyaring subukang muli.";
$lang['contest'] ["self_exclusion_limit_reached"] = "Hindi makasali sa paligsahan, lumagpas sa pagsasama ng limitasyon.";

//mulitple team join
$lang['contest']["select_min_one_team"] = "Mangyaring pumili ng atleast isang koponan";
$lang['contest']["already_joined_with_teams"] = "Paumanhin sumali ka na sa patimpalak na ito ng mga napiling (mga) koponan.";
$lang['contest']["contest_max_allowed_team_limit_exceed"] = "Paumanhin hindi ka maaaring sumali sa patimpalak na ito sa higit pang mga koponan sa {TEAM_LIMIT}.";
$lang['contest']["problem_while_join_game_some_team"] = "May problema habang sumali sa laro sa mga koponan ng {TEAM_COUNT}.";
$lang['contest']["multiteam_join_game_success"] = "Matagumpay kang sumali sa paligsahan sa {TEAM_COUNT} (na) koponan.";
