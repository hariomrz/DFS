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

$lang['captain'] = "Captain";
$lang['vice_captain'] = "Vice Captain";

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

$lang['module_not_activated'] = "ไม่ได้เปิดใช้งานโมดูล";

$lang["problem_while_join_game_network"] = "เกิดปัญหาขณะเข้าร่วมเกม! กรุณาลองอีกครั้ง.";
$lang["action_cant_completed_err"] = "ไม่สามารถดำเนินการให้เสร็จสิ้นได้! กรุณาลองอีกครั้ง.";
$lang['contest']["self_exclusion_limit_reached"] = "ไม่สามารถเข้าร่วมการแข่งขันเกินขีด จำกัด การเข้าร่วม";

//mulitple team join
$lang['contest']["select_min_one_team"] = "โปรดเลือกอย่างน้อยหนึ่งทีม";
$lang['contest']["already_joined_with_teams"] = "ขออภัยคุณได้เข้าร่วมการแข่งขันนี้แล้วโดยทีมที่เลือก";
$lang['contest']["contest_max_allowed_team_limit_exceed"] = "ขออภัยคุณไม่สามารถเข้าร่วมการแข่งขันนี้กับทีมมากกว่า {TEAM_LIMIT} ทีม";
$lang['contest']["problem_while_join_game_some_team"] = "ปัญหาขณะเข้าร่วมเกมกับ {TEAM_COUNT}";
$lang['contest']["multiteam_join_game_success"] = "คุณเข้าร่วมการแข่งขันกับ {TEAM_COUNT} ทีมสำเร็จแล้ว";