<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['input_invalid_format']               ="รูปแบบการป้อนข้อมูลไม่ถูกต้อง";
$lang['global_error']                       ="กรุณาใส่พารามิเตอร์ที่ถูกต้อง";
$lang['status']                             ="สถานะ";
$lang['invalid_status']                     ="สถานะไม่ถูกต้อง";
$lang['valid_leaderboard_type']             ="ประเภทกระดานผู้นำไม่ถูกต้อง";
$lang['sports_id']                          ="รหัสกีฬา";
$lang['league_id']                          ="lege id";
$lang['collection_master_id']               ="Collection Master ID";
$lang['collection_id']                      ="รหัสรวบรวม";
$lang['player_uid']                         ="ผู้เล่น uid";
$lang['player_team_id']                     ="กุญแจผู้เล่น";
$lang['contest_id']                         ="รหัสการประกวด";
$lang['contest_unique_id']                  ="การประกวด ID ที่ไม่ซ้ำกัน";
$lang['lineup_master_id']                   ="Lineup Master ID";
$lang['lineup_master_contest_id']           ="รายชื่อการประกวด Master Master";
$lang['season_game_uid']                    ="เกมซีซั่น UID";
$lang['no_of_match']                        ="จำนวนการแข่งขัน";
$lang['against_team']                       ="กับทีม";
$lang['promo_code']                         ="รหัสโปรโมชั่น";
$lang['match_status']                       ="การจับคู่สถานะ";
$lang['lineup']                             ="เข้าแถว";
$lang['team_name']                          ="ชื่อทีม";
$lang['format']                             ="รูปแบบ";
$lang['join_code']                          ="เข้าร่วมรหัส";
$lang['prize_type']                         ="ประเภทรางวัล";
$lang['salary_cap']                         ="หมวกเงินเดือน";
$lang['size']                               ="ขนาด";
$lang['size_min']                           ="ขนาดเล็ก";
$lang['game_name']                          ="ชื่อเกม";
$lang['category']                           ="หมวดหมู่";
$lang['game_desc']                          ="เกม desc";
$lang['entry_fee']                          ="ค่าธรรมเนียมเข้า";
$lang['prize_pool']                         ="สระว่ายน้ำรางวัล";
$lang['number_of_winners']                  ="จำนวนผู้ชนะ";
$lang['prize_distribution_detail']          ="รายละเอียดรางวัล";
$lang['disable_private_contest']            ="ปัจจุบันคุณลักษณะนี้ปิดการใช้งานโดยผู้ดูแลระบบ";
$lang["contest_added_success"]              ="สร้างการประกวดเรียบร้อยแล้ว";
$lang["contest_added_error"]                ="ปัญหาในขณะที่การประกวดสร้าง กรุณาลองอีกครั้ง.";
$lang['leaderboard_type']                   ="ประเภทกระดานผู้นำ";
$lang['user_id']                            ="ID ผู้ใช้";
$lang["source"]                             ="แหล่งที่มา";
$lang['file_not_found']                     ="คุณไม่ได้เลือกไฟล์ที่จะอัปโหลด";
$lang['invalid_image_size']                 ="โปรดอัปโหลดไฟล์รูปภาพสูงสุด {size} ขนาด";
$lang['invalid_image_ext']                  ="โปรดอัปโหลดรูปภาพด้วย% s s เท่านั้น";
$lang["icon_upload_success"]                ="อัปโหลดรูปภาพสำเร็จแล้ว";
$lang["image_removed"]                      ="ลบภาพสำเร็จแล้ว";
$lang["image_removed_error"]                ="ขออภัยมีบางอย่างผิดพลาดขณะลบภาพ";
$lang['file_upload_error']                  ="ขออภัยมีปัญหาบางอย่างเกี่ยวกับการอัพโหลดไฟล์ กรุณาลองอีกครั้ง.";
$lang['match_not_found_msg']                ="ไม่พบรายละเอียดการแข่งขัน";
$lang['match_custom_msg_sent']              ="ข้อความที่กำหนดเองส่งสำเร็จ";
$lang["successfully_cancel_collection"]     ="คอลเลกชันถูกยกเลิกเรียบร้อยแล้ว";
$lang["successfully_cancel_contest"]        ="ยกเลิกการแข่งขันสำเร็จแล้ว";
$lang["no_contest_for_cancel"]     			= "There is not contest for cancel.";
$lang["delete_contest"]                     = "Contest deleted successfully";
$lang["no_change"]                          = "No change";
$lang['invalid_image_dimension']            = 'Please upload image of size less than or equal to {max_width}x{max_height} ';

$lang['type']                               	= 'type';
$lang['filter']                             	= 'filter';
$lang["sponsor_name"]				            = "Sponsor name";
$lang["sponsor_logo"]				            = "Sponsor image";
$lang["sponsor_contest_dtl_image"]				= "Sponsor contest detail image";
$lang["sponsor_link"]				            = "Sponsor link";
$lang["invalid_sponsor_image_size"]			    = "Please upload image of size less than or equal to {max_width}x{max_height} ";
$lang['site_rake'] = 'Site Rake';
$lang["invalid_game_size"]						= "Please select games";
$lang["invalid_game_multiple_lineup_size"]			= "Multiple lineup should be less than or equal to size.";
$lang["invalid_tie_breaker_status"]				= "Merchandise prize type should always be in tie breaker.";
$lang["auto_recurrent_merchandise_error"]       = "Auto recurrent can't be created for merchandise prize type.";
$lang['auto_recurrent_create_error'] = "You can't create auto recurrent free contest which prize pool is greater than zero";

//Lineup Language
$lang['lineup'] = array();
$lang['lineup']["contest_not_found"] = "ไม่พบการแข่งขัน";
$lang['lineup']["contest_started"] = "เริ่มการแข่งขันแล้ว";
$lang['lineup']["match_detail_not_found"] = "ไม่พบรายละเอียดการแข่งขัน";
$lang['lineup']["lineup_max_limit"] = "คุณควรเลือกสูงสุด %s คำถามเพื่อสร้างทีม";
$lang['lineup']['invalid_collection_player'] = "ผู้เล่นที่เลือกไม่ถูกต้อง โปรดรีเซ็ตรายชื่อทีมและสร้างใหม่";
$lang['lineup']['team_name_already_exist'] = 'ชื่อทีมมีอยู่แล้ว';
$lang['lineup']["lineup_success"] = "คุณสร้างการเลือกสำเร็จแล้ว";
$lang['lineup']["update_lineup_success"] = "คุณอัปเดตตัวเลือกสำเร็จแล้ว";

//ContestLanguage
$lang['contest']["invalid_contest"] = "โปรดเลือกการแข่งขันที่ถูกต้อง";
$lang['contest']["contest_not_found"] = "ไม่พบรายละเอียดการแข่งขัน";
$lang['contest']["problem_while_join_game"] = "ปัญหาขณะเข้าร่วมเกม";
$lang['contest']["contest_already_started"] = "เริ่มการแข่งขันแล้ว";
$lang['contest']["contest_already_full"] = "การแข่งขันนี้เต็มแล้ว";
$lang['contest']["contest_closed"] = "ปิดการแข่งขัน";
$lang['contest']["not_enough_coins"] = "เหรียญไม่พอ.";
$lang['contest']["not_enough_balance"] = "ยอดเงินไม่เพียงพอ";
$lang['contest']["join_multiple_time_error"] = "คุณไม่สามารถเข้าร่วมการแข่งขันนี้หลายครั้ง";
$lang['contest']["you_already_joined_this_contest"]	= "คุณได้เข้าร่วมการแข่งขันนี้แล้วโดยผู้เล่นตัวจริงที่เลือก";
$lang['contest']["provide_a_valid_user_team_id"] = "โปรดระบุรหัสทีมผู้ใช้ที่ถูกต้อง";
$lang['contest']["join_game_success"] = "คุณเข้าร่วมการแข่งขันสำเร็จแล้ว";
$lang['contest']["join_game_email_subject"] = "การเข้าร่วมการแข่งขันของคุณได้รับการยืนยันแล้ว!";
$lang['contest']["you_already_joined_to_max_limit"] = "คุณได้เข้าร่วมการแข่งขันนี้ถึงขีดจำกัดสูงสุดของทีมแล้ว";
$lang['contest']["self_exclusion_limit_reached"] = "ไม่สามารถเข้าร่วมการแข่งขัน เกินขีดจำกัดการเข้าร่วม";
$lang['contest']["invalid_previous_team_for_season"]	= "ทีมก่อนหน้าไม่ถูกต้องสำหรับการแข่งขันที่เลือก";
$lang['contest']["invalid_team_for_season"] = "ผู้เล่นตัวจริงไม่ถูกต้องสำหรับการแข่งขันที่เลือก";
$lang['contest']["team_switch_success"]                  = "เปลี่ยนทีมสำเร็จ";