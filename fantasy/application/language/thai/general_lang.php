<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['global_error'] = 'กรุณาใส่พารามิเตอร์ที่ถูกต้อง';
$lang['invalid_status'] = 'สถานะไม่ถูกต้อง';
$lang['valid_leaderboard_type'] = 'ประเภทลีดเดอร์บอร์ดไม่ถูกต้อง';
$lang['sports_id'] = 'รหัสกีฬา';
$lang['league_id'] = 'รหัสลีก';
$lang['collection_master_id'] = 'รหัสหลักคอลเลกชัน';
$lang['player_uid'] = 'ผู้เล่น uid';
$lang['player_team_id'] = 'คีย์ผู้เล่น';
$lang['contest_id'] = 'รหัสการแข่งขัน';
$lang['contest_unique_id'] = 'รหัสเฉพาะการแข่งขัน';
$lang['lineup_master_id'] = 'lineup master id';
$lang['lineup_master_contest_id'] = 'lineup master รหัสการแข่งขัน';
$lang['season_game_uid'] = 'เกมประจำฤดูกาล uid';
$lang['no_of_match'] = 'จำนวนการแข่งขัน';
$lang['against_team'] = 'ทีมต่อต้าน';
$lang['promo_code'] = 'รหัสโปรโมชั่น';
$lang['match_status'] = 'สถานะการจับคู่';
$lang['lineup'] = 'ผู้เล่นตัวจริง';
$lang['team_name'] = 'ชื่อทีม';
$lang['format'] = 'รูปแบบ';
$lang['join_code'] = 'รหัสเข้าร่วม';
$lang['prize_type'] = 'ประเภทรางวัล';
$lang['salary_cap'] = 'เงินเดือนสูงสุด';
$lang['size'] = 'ขนาด';
$lang['size_min'] = 'ขนาดต่ำสุด';
$lang['game_name'] = 'ชื่อเกม';
$lang['game_desc'] = 'game desc';
$lang['entry_fee'] = 'ค่าธรรมเนียมแรกเข้า';
$lang['prize_pool'] = 'เงินรางวัลรวม';
$lang['number_of_winners'] = 'จำนวนผู้ชนะ';
$lang['prize_distribution_detail'] = 'รายละเอียดรางวัล';
$lang['disable_private_contest'] = "ขณะนี้คุณลักษณะนี้ถูกปิดใช้งานโดยผู้ดูแลระบบ";
$lang["contest_added_success"] = "สร้างการแข่งขันสำเร็จแล้ว";
$lang["contest_added_error"] = "ปัญหาขณะสร้างการแข่งขันโปรดลองอีกครั้ง";
$lang['currency_type'] = 'ประเภทสกุลเงิน';
$lang["same_currency_prize_type"] = "ประเภทสกุลเงินและประเภทรางวัลควรเหมือนกัน";

// ข้อความทั่วไป
$lang["lineup_required"] = "รายการที่ต้องการ";

// ContestLanguage
$lang['contest'] ["invalid_contest"] = "โปรดเลือกการแข่งขันที่ถูกต้อง";
$lang['contest'] ["invalid_contest_code"] = "รหัสลีกไม่ถูกต้อง";
$lang['contest'] ["contest_not_found"] = "ไม่พบรายละเอียดการแข่งขัน";
$lang['contest'] ["problem_while_join_game"] = "ปัญหาขณะเข้าร่วมเกม";
$lang['contest'] ["contest_already_started"] = "การแข่งขันเริ่มแล้ว";
$lang['contest'] ["contest_already_full"] = "การแข่งขันนี้เต็มแล้ว";
$lang['contest'] ["contest_closed"] = "ปิดการแข่งขันแล้ว";
$lang['contest'] ["not_enough_coins"] = "เหรียญไม่พอ";
$lang['contest'] ["not_enough_balance"] = "ยอดคงเหลือไม่เพียงพอ";
$lang['contest'] ["join_game_success"] = "คุณเข้าร่วมการแข่งขันสำเร็จแล้ว";
$lang["contest"] ["invalid_promo_code"] = "รหัสโปรโมชั่นไม่ถูกต้องโปรดป้อนรหัสที่ถูกต้อง";
$lang["contest"] ["allowed_limit_exceed"] = "คุณได้ใช้รหัสส่งเสริมการขายนี้เป็นเวลาสูงสุดแล้ว";
$lang["contest"] ["promo_code_exp_used"] = "Promocode หมดอายุหรือถูกใช้ไปแล้ว!";
$lang['contest'] ["you_already_joined_to_max_limit"] = "คุณเข้าร่วมการแข่งขันนี้จนถึงขีด จำกัด สูงสุดของทีมแล้ว";
$lang['contest'] ["join_multiple_time_error"] = "คุณไม่สามารถเข้าร่วมการแข่งขันหลายครั้งได้";
$lang['contest'] ["you_already_joined_this_contest"] = "คุณเข้าร่วมการแข่งขันนี้แล้วตามรายการที่เลือก";
$lang['contest'] ["provide_a_valid_lineup_master_id"] = "โปรดระบุรหัสหลักของรายการที่ถูกต้อง";
$lang['contest'] ["not_a_valid_team_for_contest"] = "ไม่ใช่ทีมที่ถูกต้องสำหรับการแข่งขัน";
$lang['contest'] ['exceed_promo_used_count'] = "คุณใช้งานเกินจำนวนที่อนุญาต";
$lang['contest'] ['team_detail_not_found'] = "ไม่พบรายละเอียดทีม";
$lang['contest'] ["invalid_previous_team_for_collecton"] = "ทีมก่อนหน้าไม่ถูกต้องสำหรับการแข่งขันที่เลือก";
$lang['contest'] ["team_switch_success"] = "สลับทีมเรียบร้อยแล้ว";
$lang['contest'] ["invalid_team_for_collecton"] = "รายการไม่ถูกต้องสำหรับการแข่งขันที่เลือก";
$lang['contest'] ['processing_team_pdf_data'] = "เรากำลังประมวลผลข้อมูลของทีมซึ่งจะพร้อมใช้งานเร็ว ๆ นี้";
$lang['contest'] ["join_game_email_subject"] = "การเข้าร่วมการแข่งขันของคุณได้รับการยืนยันแล้ว!";
$lang['contest_cancel_mail_subject'] = '['.SITE_TITLE.'] ข้อมูลการยกเลิกการแข่งขัน ';
$lang['contest'] ["process_contest_pdf"] = "เรากำลังดำเนินการกับทีม pdf ซึ่งจะพร้อมใช้งานเร็ว ๆ นี้";
$lang['contest']["self_exclusion_limit_reached"] = "ไม่สามารถเข้าร่วมการแข่งขันเกินขีด จำกัด การเข้าร่วม.";
$lang['contest']["rookie_user_not_allowed_for_this_contest"] = "คุณไม่ได้รับอนุญาตให้เข้าร่วมการแข่งขันนี้";
$lang['contest']["max_usage_limit_code"] = "การใช้งานสูงสุดสำหรับขีด จำกัด รหัสโปรโมชั่นนี้";

//Lineup Language
$lang['lineup'] = array ();
$lang['lineup'] ["contest_not_found"] = "ไม่พบการแข่งขัน";
$lang['lineup'] ["contest_started"] = "การแข่งขันเริ่มแล้ว";
$lang['lineup'] ["match_detail_not_found"] = "ไม่พบรายละเอียดการแข่งขัน";
$lang['lineup'] ['invalid_collection_player'] = "ผู้เล่นที่เลือกไม่ถูกต้องโปรดรีเซ็ตผู้เล่นตัวจริงของทีมและสร้างใหม่";
$lang['lineup'] ["lineup_not_exist"] = "ไม่มีทีม";
$lang['lineup'] ['team_name_already_exist'] = 'ชื่อทีมมีอยู่แล้ว';
$lang['lineup'] ["lineup_team_rquired"] = "ต้องการรหัสทีมของลีกผู้เล่น";
$lang['lineup'] ["lineup_player_id_required"] = "ต้องระบุรหัสเฉพาะของผู้เล่น";
$lang['lineup'] ["lineup_player_team_required"] = "ต้องการรหัสทีมผู้เล่น";
$lang['lineup'] ["position_invalid"] = "ตำแหน่งไม่ถูกต้อง";
$lang['lineup'] ["salary_required"] = "เงินเดือนผู้เล่นที่ต้องการ";
$lang['lineup'] ["lineup_player_id_duplicate"] = "คุณไม่สามารถเลือกผู้เล่นเดี่ยวสองครั้งได้";
$lang['lineup'] ["lineup_max_limit"] = "คุณควรเลือก %s ผู้เล่นเพื่อสร้างทีม";
$lang['lineup'] ["lineup_team_limit_exceeded"] = "โปรดแก้ไขผู้เล่นตัวจริงของคุณคุณสามารถเลือกผู้เล่นสูงสุด %s จากทีมเดียว";
$lang['lineup'] ["position_exceeded_invalid"] = "คุณมีตำแหน่งผู้เล่นเกินขีด จำกัด ";
$lang['lineup'] ["salary_cap_not_enough"] = "เงินเดือนผู้เล่นเกินเงินเดือนสูงสุด";
$lang['lineup'] ["lineup_posisiotn_not_found"] = "โปรดเลือก %s player";
$lang['lineup'] ['already_created_same_team'] = "คุณได้สร้างทีมนี้แล้ว";
$lang['lineup'] ["lineup_success"] = "คุณสร้างทีมสำเร็จแล้ว";
$lang['lineup'] ["lineup_update_success"] = "ทีมของคุณได้รับการอัปเดตเรียบร้อยแล้ว";
$lang['lineup'] ["lineup_captain_error"] = "กัปตันทีมต้องการ";
$lang['lineup'] ["lineup_vice_captain_error"] = "ต้องการรองกัปตันทีม";
$lang['lineup'] ['team_detail_not_found'] = "ไม่พบรายละเอียดทีม";
$lang['lineup']['team_generate_error'] = "ขออภัยมีปัญหาบางอย่างขณะสร้างทีม กรุณาลองอีกครั้ง.";
$lang['lineup']['c_vc_same_error'] = "กัปตันและรองกัปตันควรจะแตกต่างกัน";
$lang['lineup']['username_empty_error'] = "โปรดอัปเดตชื่อผู้ใช้จากส่วนโปรไฟล์ก่อนเข้าร่วมการแข่งขัน";
$lang['lineup']["allow_team_limit_error"] = "คุณไม่สามารถสร้างทีมได้มากกว่า %s ทีม";
$lang['captain'] = "Captain";
$lang['vice_captain'] = "Vice Captain";
$lang['lineup']['team_generate_error'] = "ขออภัยปัญหาบางอย่างในขณะที่ทีมสร้าง กรุณาลองอีกครั้ง.";


$lang['tr_lineup'] = array();
$lang['tr_lineup']["match_not_found"] = "ไม่พบรายการที่ตรงกัน";
$lang['tr_lineup']["match_started"] = "เริ่มการแข่งขันแล้ว";
$lang['tr_lineup'] ['invalid_match_player'] = "ผู้เล่นที่เลือกไม่ถูกต้องโปรดรีเซ็ตผู้เล่นตัวจริงของทีมและสร้างใหม่";

$lang["tournament_season_id"] = "Tournament Season ID";
$lang["user_tournament_season_id"] = "User tournament Season ID";
$lang["tournament_id"] = "Tournament ID";
$lang["tournament_team_id"] = "Tournament Team ID";


$lang['tournament']["invalid_tournament"]                   ="กรุณาเลือกทัวร์นาเมนต์ที่ถูกต้อง";
$lang['tournament']["invalid_tournament_code"]              ="ไม่ได้เป็นรหัสการแข่งขันที่ถูกต้อง";
$lang['tournament']["tournament_not_found"]                 ="ไม่พบรายละเอียดการแข่งขัน";
$lang['tournament']["problem_while_join_tournament"]        ="ปัญหาในขณะที่เข้าร่วมการแข่งขัน";
$lang['tournament']["match_already_started"]                ="การแข่งขันเริ่มต้นแล้ว";
$lang['tournament']["tournament_closed"]                    ="ทัวร์นาเมนต์ปิด";
$lang['tournament']["not_enough_coins"]                     ="เหรียญไม่พอ.";
$lang['tournament']["not_enough_balance"]                   ="ไม่สมดุลเพียงพอ";
$lang['tournament']["join_tournament_success"]              ="คุณได้เข้าร่วมการแข่งขันประสบความสำเร็จ";
$lang["tournament"]["invalid_promo_code"]                   ="รหัสโปรโมชั่ไม่ถูกต้อง กรุณาใส่รหัสที่ถูกต้อง";
$lang["tournament"]["allowed_limit_exceed"]                 ="คุณมีใช้อยู่แล้ว Promocode นี้เวลาสูงสุด";
$lang["tournament"]["promo_code_exp_used"]                  ="Promocode หมดอายุหรือใช้แล้ว!";
$lang['tournament']["join_multiple_time_error"]             ="คุณไม่สามารถเข้าร่วมได้เวลาหลายทัวร์นาเมนต์นี้";
$lang['tournament']["you_already_joined_this_contest"]="คุณได้เข้าร่วมการแข่งขันครั้งนี้โดยผู้เล่นตัวจริงที่เลือก";
$lang['tournament']["provide_a_valid_tournament_team_id"]   ="โปรดระบุ ID ทีมทัวร์นาเมนต์ที่ถูกต้อง";
$lang['tournament']["not_a_valid_team_for_match"]           ="ไม่ได้เป็นทีมที่ถูกต้องสำหรับการแข่งขัน";
$lang['tournament']['exceed_promo_used_count']              ="คุณได้รับอนุญาตเกินนับสินค้า";
$lang['tournament']['team_detail_not_found']                ="เรากำลังประมวลผลข้อมูลทีม";
$lang['tournament']["team_switch_success"]                  ="ทีมประสบความสำเร็จในการเปลี่ยน";
$lang['tournament']["invalid_team_for_match"]               ="ผู้เล่นตัวจริงที่ไม่ถูกต้องสำหรับการแข่งขันที่เลือก";
$lang['tournament']['processing_team_pdf_data']             ="เรากำลังประมวลผลข้อมูลที่ทีมงานก็จะมีเร็ว ๆ นี้";
$lang['tournament']["join_tournament_email_subject"]        ="ทัวร์นาเมนต์ของคุณได้รับการยืนยันการเข้าร่วม!";
$lang['tournament_cancel_mail_subject']                     ="[".SITE_TITLE."] ข้อมูลการยกเลิกการแข่งขัน";
$lang['tournament']["process_contest_pdf"]                  ="เรากำลังประมวลผลทีม PDF, มันจะใช้ได้เร็ว ๆ นี้";
$lang['tournament']["state_banned_error"]                   ="ขออภัยผู้เล่นจาก {{STATE_LIST}} ไม่สามารถที่จะใส่ในการแข่งขันจ่าย";
$lang['tournament']["state_required_error"]                 ="กรุณารัฐปรับปรุงในโปรไฟล์ของคุณ";
$lang['tournament']["join_tournament_to_continue"] ="โปรดเข้าร่วมการแข่งขันเพื่อดำเนินการต่อ";
$lang['tournament']["join_match_success"] ="คุณได้เข้าร่วมการแข่งขันประสบความสำเร็จ";

$lang['tournament']["err_tournament_cancelled"] ="การแข่งขันครั้งนี้จะถูกยกเลิก";
$lang['tournament']["err_tournament_completed"] ="การแข่งขันครั้งนี้จะเสร็จสมบูรณ์";

// การประกวดส่วนตัว
$lang["enter_valid_sport_id"] = "โปรดป้อนรหัสกีฬาที่ถูกต้อง";
$lang["enter_valid_season_game_uid"] = "โปรดป้อนรหัสการจับคู่ที่ถูกต้อง";

$lang['group_name_1'] = "การแข่งขันระดับใหญ่";
$lang['group_description_1'] = "เข้าร่วมการแข่งขันที่ร้อนแรงที่สุดพร้อมรางวัลใหญ่";

$lang['group_name_9'] = "การแข่งขันสุดฮอต";
$lang['group_description_9'] = "ไม่มีใครบอกว่ามันจะง่าย";

$lang['group_name_8'] = "สงครามแก๊ง";
$lang['group_description_8'] = "เมื่อทีมของคุณเป็นอาวุธของคุณ";

$lang['group_name_2'] = "Head2Head";
$lang['group_description_2'] = "รู้สึกตื่นเต้นไปกับสุดยอด Fantasy Face off";

$lang['group_name_10'] = "ผู้ชนะจะได้ทั้งหมด";
$lang['group_description_10'] = "เสี่ยงยิ่งใหญ่รางวัลใหญ่!";

$lang['group_name_3'] = "ชนะ 50% สูงสุด";
$lang['group_description_3'] = "ผู้เล่นครึ่งหนึ่งชนะแน่นอนเข้าร่วมและเสี่ยงโชค!";

$lang['group_name_11'] = "ทุกคนชนะ";
$lang['group_description_11'] = "บางอย่างสำหรับทุกคน";

$lang['group_name_4'] = "สำหรับผู้เริ่มต้นเท่านั้น";
$lang['group_description_4'] = "เล่นการแข่งขันครั้งแรกของคุณเดี๋ยวนี้";

$lang['group_name_5'] = "การแข่งขันเพิ่มเติม";
$lang['group_description_5'] = "ไม่เอะอะ! นี่คือโซนของคุณให้เล่นฟรี";

$lang['group_name_6'] = "การแข่งขันฟรี";
$lang['group_description_6'] = "ไม่เอะอะ! นี่คือโซนของคุณสำหรับเล่นฟรีและรับเงินสด";

$lang['group_name_7'] = "การแข่งขันส่วนตัว";
$lang['group_description_7'] = "มันพิเศษและสนุกมาก! เล่นกับเพื่อนของคุณตอนนี้";

$lang['group_name_12'] = "การแข่งขันเพื่อชิงแชมป์";
$lang['group_description_12'] = "การแข่งขันเพื่อชิงแชมป์";

$lang['module_disable_error'] = "ขออภัยโมดูลนี้ไม่ได้เปิดใช้งานโปรดติดต่อผู้ดูแลระบบ";

$lang['file_upload_error'] = "ขออภัยมีปัญหาในการอัปโหลดไฟล์ กรุณาลองอีกครั้ง.";
$lang['players'] = "ผู้เล่น";

$lang['module_not_activated'] = "Module not activated";

$lang['multiple_lineup'] = 'ผู้เล่นตัวจริงหลายรายการ';
$lang['contest'] ["self_exclusion_limit_reached"] = "ไม่สามารถเข้าร่วมการแข่งขันเกินขีด จำกัด การเข้าร่วม";
$lang['contest'] ["state_banned_error"] = "ขออภัยผู้เล่นจาก {{STATE_LIST}} ไม่สามารถเข้าร่วมการแข่งขันแบบชำระเงิน";
$lang['contest'] ["state_required_error"] = "โปรดอัปเดตสถานะในโปรไฟล์ของคุณ";
$lang['player_detail_not_found'] = "ไม่พบรายละเอียดผู้เล่น";

$lang['invalid_booster_id'] = "ขออภัย ID ผู้สนับสนุนไม่ถูกต้องสำหรับการจับคู่";
$lang['save_booster_error'] = "ขออภัย มีปัญหาบางอย่างในขณะที่ใช้บูสเตอร์ กรุณาลองอีกครั้ง.";
$lang['save_booster_success'] = "ใช้ Booster สำเร็จ";
$lang['update_booster_success'] = "อัปเดต Booster สำเร็จแล้ว";
$lang['booster_only_for_dfs'] = "ขออภัย Booster ใช้ได้กับทีมแฟนตาซีคลาสสิกเท่านั้น";
$lang['invalid_team_for_match'] = "ทีมที่ไม่ถูกต้องสำหรับการแข่งขัน";

$lang['err_2nd_inning_format_contest'] = "คุณไม่สามารถสร้างการประกวดส่วนตัวที่ 2 สำหรับ (T10 / การแข่งขันทดสอบ)";

//bench module
$lang['max_bench_limit_error'] = "แม็กซ์ผู้เล่น 4 ม้านั่งที่ได้รับอนุญาต";
$lang['invalid_collection_bench_player'] = "ไม่ถูกต้อง ID ผู้เล่นที่เลือก โปรดเลือกผู้เล่นที่ถูกต้อง";
$lang['bench_player_team_pl_error'] = "ผู้เล่นที่ม้านั่งควรจะแตกต่างจากผู้เล่นทีม";
$lang['bench_player_save_error'] = "ขออภัยมีปัญหาบางอย่างในขณะบันทึกผู้เล่นที่ม้านั่ง กรุณาลองอีกครั้ง.";
$lang['save_bench_player_success'] = "ผู้เล่นที่ม้านั่งบันทึกเรียบร้อยแล้ว";
$lang["bench_process_waiting_error"] = "ทีมจะสามารถใช้ได้ในเร็ว ๆ นี้ลองอีกครั้งในบางครั้ง";

$lang['invalid_prize_distribution_error'] = "รายละเอียดการแจกรางวัลไม่ถูกต้อง โปรดลองด้วยข้อมูลที่ถูกต้อง";
$lang['invalid_prize_pool_error'] = "โปรดระบุเงินรางวัลรวมที่ถูกต้อง";

$lang['contest']["h2h_game_join_limit_error"] = "ขออภัยคุณไม่สามารถเข้าร่วมการประกวด H2h ได้มากกว่า {CONTEST_LIMIT}";

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