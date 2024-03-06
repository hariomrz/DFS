<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['global_error'] = 'กรุณาป้อนพารามิเตอร์ที่ถูกต้อง';
$lang['invalid_status'] = 'สถานะไม่ถูกต้อง';
$lang['sports_id'] = 'รหัสกีฬา';
$lang['league_id'] = 'รหัสลีก';
$lang['season_id'] = 'รหัสการแข่งขัน';
$lang['team_id'] = 'รหัสทีม';
$lang['player_id'] = 'รหัสผู้เล่น';
$lang['player_team_id'] = 'รหัสผู้เล่น';
$lang['contest_id'] = 'รหัสการแข่งขัน';
$lang['user_team_id'] = 'รหัสทีมผู้ใช้';
$lang['match_status'] = 'สถานะการแข่งขัน';
$lang['lineup'] = 'lineup';
$lang['team_name'] = 'ชื่อทีม';
$lang['format'] = 'รูปแบบ';
$lang['join_code'] = 'รหัสเข้าร่วม';
$lang['promo_code'] = 'รหัสส่งเสริมการขาย';

$lang['players'] = "ผู้เล่น";
$lang['player_info'] = "โปรดระบุข้อมูลผู้เล่นทั้งหมด";
$lang['payout_type'] = 'ประเภทการจ่ายเงิน';
$lang['currency_type'] = 'ประเภทสกุลเงิน';
$lang['prop_id'] = 'รหัสเงื่อนไข';
$lang['season_prop_id'] = 'รหัสประกอบฤดูกาล';
$lang['entry_fee'] = 'ค่าธรรมเนียมแรกเข้า';
$lang['select_min_picks'] = 'โปรดเลือกขั้นต่ำ {min_picks} รายการ';
$lang['invalid_team'] = 'ทีมผู้ใช้ไม่ถูกต้อง';
$lang['team_success'] = 'บันทึกทีมสำเร็จ';
$lang['join_entry_email_subject'] = 'ชวนแฟนมาสมัคร';
$lang['min_max_bet_limit'] = 'เงินเดิมพันไม่ควรน้อยกว่า {min_bet} และมากกว่า {max_bet}';
$lang['winning_limit_exceed'] = 'คุณใช้ขีดจำกัดการชนะสำหรับเดือนนี้หมดแล้ว ซึ่งก็คือ {amount} จะรีเซ็ตในวันที่ 1 ของเดือนถัดไป';
$lang['payout_disabled_error'] = 'การจ่ายเงินถูกปิดใช้งาน โปรดเลือกชุดตัวเลือกอื่น';
$lang['remaining_winning_limit'] = 'ผลงานของคุณเกินขีดจำกัดของโอกาสที่จะชนะ กรุณาปรับให้เท่ากับหรือน้อยกว่า {remaining_limit}';
$lang['multi_sports_pl_error'] = 'โปรดเลือกผู้เล่นที่มีกีฬาประเภทเดียวกันเท่านั้น';
$lang['contest_already_started'] = 'การแข่งขันได้เริ่มขึ้นแล้ว';

//Admin
$lang['invalid_league_id'] = "ไม่พบรายละเอียดของลีก";
$lang['league_status_success'] = "ปรับปรุงสถานะลีกเรียบร้อยแล้ว";
$lang['league_status_error'] = "มีบางอย่างผิดพลาดขณะอัพเดตสถานะ";
$lang['image_invalid_ext'] = 'ประเภทรูปภาพไม่ถูกต้อง ประเภทสื่อที่อนุญาต {media_type}';
$lang['image_invalid_dim'] = 'โปรดอัปโหลดภาพที่มีขนาด {max_width}x{max_height}';
$lang['image_invalid_size_error'] = 'ขนาดไฟล์มีเดียสูงสุดที่อนุญาตคือ {size}MB';
$lang['image_file_upload_error'] = 'ขออภัย มีปัญหาในการอัพโหลดไฟล์ กรุณาลองอีกครั้ง.';
$lang['media_removed'] = "ลบไฟล์มีเดียออกเรียบร้อยแล้ว";
$lang['team_edit_success'] = "อัปเดตทีมเรียบร้อยแล้ว";
$lang['team_edit_failure'] = "การอัปเดตทีมล้มเหลว";
$lang['player_edit_success'] = 'อัปโหลดภาพผู้เล่นสำเร็จแล้ว';
$lang['player_edit_failure'] = "เกิดข้อผิดพลาดในการอัพโหลดรูปภาพ";
$lang['payout_update_success'] = "อัปเดตข้อมูลการจ่ายเงินเรียบร้อยแล้ว";
$lang['invalid_payout_id'] = "ไม่พบรายละเอียดการจ่ายเงิน";
$lang['payout_status_success'] = "อัปเดตสถานะการจ่ายเงินเรียบร้อยแล้ว";
$lang['payout_status_error'] = "เกิดข้อผิดพลาดขณะอัปเดตสถานะ";
$lang['user_status_error'] = "มีบางอย่างผิดพลาดขณะอัปเดตสถานะ โปรดลองใหม่อีกครั้ง";
$lang['user_status_success'] = "อัปเดตสถานะผู้ใช้เรียบร้อยแล้ว";
$lang['user_winning_limit_error'] = 'เกิดข้อผิดพลาดขณะอัปเดตขีดจำกัดที่ชนะ กรุณาลองใหม่อีกครั้ง';
$lang['user_winning_limit_success'] = "อัปเดตขีดจำกัดการชนะของผู้ใช้เรียบร้อยแล้ว";
$lang['pl_props_status_success'] = "อัปเดตสถานะอุปกรณ์ประกอบฉากของผู้เล่นเรียบร้อยแล้ว";