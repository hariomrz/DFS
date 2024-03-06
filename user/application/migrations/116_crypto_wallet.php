<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_crypto_wallet extends CI_Migration {

	public function up() {
		//up script
		$fields = array(
			'type' => array(
				'type' => 'TINYINT',
				'constraint' => 2,
				'null' => false,
              	'default' => 1,
				'comment'=>'1=>bank,2=>crypto wallet',
			)
		);
		if(!$this->db->field_exists('type', USER_BANK_DETAIL)){
			$this->dbforge->add_column(USER_BANK_DETAIL,$fields);
		}

		$sql = "UPDATE ".
		$this->db->dbprefix(NOTIFICATION_DESCRIPTION).
		" SET `guj_message` = 'અભિનંદન! તમે {{p_to_id}} કાર્ડ એક સંપૂર્ણ તપાસ માટે {{amount}} એક બોનસ કમાવ્યા છે.' WHERE notification_type=59;";
	  	$this->db->query($sql);


		$sql = "UPDATE ".
		$this->db->dbprefix(NOTIFICATION_DESCRIPTION).
		" SET `message` = '{{p_to_id}} card verification real cash {{amount}} credited to your account.',
		`en_message` = '{{p_to_id}} card verification real cash {{amount}} credited to your account.',
		`guj_message` = 'અભિનંદન! તમે {{p_to_id}} કાર્ડ એક સંપૂર્ણ તપાસ માટે {{amount}} જથ્થો છે.' WHERE notification_type=60;";
		$this->db->query($sql);

		$sql = "UPDATE ".
		$this->db->dbprefix(NOTIFICATION_DESCRIPTION).
		" SET `message` = '{{p_to_id}} card verification coins {{amount}} credited to your account.',
		`en_message` = '{{p_to_id}} card verification coins {{amount}} credited to your account.',
		`guj_message` = 'અભિનંદન! તમે {{p_to_id}} કાર્ડ એક સંપૂર્ણ તપાસ માટે {{amount}} સિક્કા મળી.' WHERE notification_type=61;";
		$this->db->query($sql);

		$update_crypto_notification_arr = array(
			array(
				'notification_type'		=>'136',
				'message'				=>'Your {{b_to_c}} details has been rejected by admin',
				'en_message'			=>'Your {{b_to_c}} details has been rejected by admin',
				'hi_message'			=>'आपका {{b_to_c}} विवरण व्यवस्थापक द्वारा अस्वीकार कर दिया गया है',
				'guj_message'			=>'તમારી {{b_to_c}} વિગતોને એડમિન દ્વારા નકારી કાઢવામાં આવી છે',
				'fr_message'			=>'Vos détails {{b_to_c}} ont été rejetés par admin',
				'ben_message'			=>'আপনার {{b_to_c}} বিস্তারিত প্রশাসক দ্বারা প্রত্যাখ্যাত হয়েছে',
				'pun_message'			=>'ਤੁਹਾਡਾ {{b_to_c}} add ਪ੍ਰਸ਼ਾਸਕ ਦੁਆਰਾ ਰੱਦ ਕਰ ਦਿੱਤਾ ਗਿਆ ਹੈ',
				'tam_message'			=>'உங்கள் {{b_to_c}} விவரங்கள் நிர்வாகத்தால் நிராகரிக்கப்பட்டுள்ளன',
				'th_message'			=>'รายละเอียด {{b_to_c}} ของคุณถูกปฏิเสธโดยผู้ดูแลระบบ',
				'kn_message'			=>'ನಿಮ್ಮ {{b_to_c}} ವಿವರಗಳನ್ನು ನಿರ್ವಾಹಕರಿಂದ ತಿರಸ್ಕರಿಸಲಾಗಿದೆ',
				'tl_message'			=>'Ang mga detalye ng iyong {{b_to_c}} ay tinanggihan ng admin',
				'ru_message'			=>'Ваш {{b_to_c}} детали были отклонены администратором',
				'id_message'			=>'Detail {{b_to_c}} Anda telah ditolak oleh admin',
				'zh_message'			=>'您的{{b_to_c}}详细信息已被管理员拒绝',
			),
			array(
				'notification_type'		=>'142',
				'message'				=>'You have received {{amount}} bonus for {{b_to_c}} verification',
				'en_message'			=>'You have received {{amount}} bonus for {{b_to_c}} verification',
				'hi_message'			=>'आपको {{b_to_c}} सत्यापन के लिए {{amount}} बोनस प्राप्त हुआ है',
				'guj_message'			=>'તમે {{b_to_c}} ચકાસણી માટે {{amount}} બોનસ પ્રાપ્ત કર્યું છે',
				'fr_message'			=>'Vous avez reçu {{amount}} bonus pour {{b_to_c}} vérification',
				'ben_message'			=>'আপনি {{amount}} বোনাস {{b_to_c}} যাচাইয়ের জন্য পেয়েছেন',
				'pun_message'			=>'ਤੁਹਾਨੂੰ {{b_to_c}} ਤਸਦੀਕ ਲਈ {{amount}} ਬੋਨਸ ਪ੍ਰਾਪਤ ਹੋਇਆ ਹੈ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கு {{amount}} போனஸ் கிடைத்தது',
				'th_message'			=>'คุณได้รับโบนัส {{amount}} สำหรับการตรวจสอบ {{b_to_c}}',
				'kn_message'			=>'ನೀವು {{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ {{amount}} ಬೋನಸ್ಗಳನ್ನು ಸ್ವೀಕರಿಸಿದ್ದೀರಿ',
				'tl_message'			=>'Natanggap mo ang {{amount}} bonus para sa {{b_to_c}} verification',
				'ru_message'			=>'Вы получили бонус {{amount}} для {{b_to_c}} проверки',
				'id_message'			=>'Anda telah menerima {{amount}} bonus untuk verifikasi {{b_to_c}}',
				'zh_message'			=>'您已收到{{b_to_c}}验证的{{amount}}奖金',
			),
			array(
				'notification_type'		=>'143',
				'message'				=>'You have received {{amount}} real cash for {{b_to_c}} verification',
				'en_message'			=>'You have received {{amount}} real cash for {{b_to_c}} verification',
				'hi_message'			=>'आपको {{b_to_c}} सत्यापन के लिए {{amount}} वास्तविक नकद प्राप्त हुई है',
				'guj_message'			=>'તમને {{b_to_c}} ચકાસણી માટે {{amount}} વાસ્તવિક રોકડ મળી છે',
				'fr_message'			=>'Vous avez reçu {{amount}} réel argent pour {{b_to_c}} vérification',
				'ben_message'			=>'আপনি {{b_to_c}} যাচাইয়ের জন্য {{amount}} রিয়েল ক্যাশ পেয়েছেন',
				'pun_message'			=>'ਤੁਹਾਨੂੰ {{b_to_c}} ਤਸਦੀਕ ਲਈ {{amount}} ਅਸਲ ਨਕਦ ਮਿਲਿਆ ਹੈ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கான {{amount}} உண்மையான பணத்தை நீங்கள் பெற்றுள்ளீர்கள்',
				'th_message'			=>'คุณได้รับ {{amount}} เงินสดจริงสำหรับการตรวจสอบ {{b_to_c}}',
				'kn_message'			=>'ನೀವು {{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ {{amount}} ರಿಯಲ್ ನಗದು ಸ್ವೀಕರಿಸಿದ್ದೀರಿ',
				'tl_message'			=>'Nakatanggap ka ng {{amount}} real cash para sa {{b_to_c}} verification',
				'ru_message'			=>'Вы получили {{amount}} Real Cash for {{b_to_c}} проверки',
				'id_message'			=>'Anda telah menerima {{amount}} uang tunai nyata untuk {{b_to_c}} verifikasi',
				'zh_message'			=>'您已收到{{b_to_c}}验证的{{amount}}真实现金',
			),
			array(
				'notification_type'		=>'144',
				'message'				=>'You have received {{amount}} coins for {{b_to_c}} verification',
				'en_message'			=>'You have received {{amount}} coins for {{b_to_c}} verification',
				'hi_message'			=>'आपको {{b_to_c}} सत्यापन के लिए {{amount}} सिक्के प्राप्त हुए हैं',
				'guj_message'			=>'તમે {{b_to_c}} ચકાસણી માટે {{amount}} સિક્કા પ્રાપ્ત થયા છે',
				'fr_message'			=>'Vous avez reçu {{amount}} monnaies pour {{b_to_c}} vérification',
				'ben_message'			=>'আপনি {{amount}} এর জন্য {{b_to_c}} যাচাইয়ের জন্য পেয়েছেন',
				'pun_message'			=>'ਤੁਹਾਨੂੰ {{b_to_c}} {{amount}} ਪ੍ਰਾਪਤ ਕਰੋ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கான {{amount}} நாணயங்களைப் பெற்றுள்ளீர்கள்',
				'th_message'			=>'คุณได้รับเหรียญ {{amount}} สำหรับการตรวจสอบ {{b_to_c}}',
				'kn_message'			=>'ನೀವು {{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ {{amount}} ನಾಣ್ಯಗಳನ್ನು ಸ್ವೀಕರಿಸಿದ್ದೀರಿ',
				'tl_message'			=>'Nakatanggap ka ng {{amount}} barya para sa {{b_to_c}} verification',
				'ru_message'			=>'Вы получили монеты {{amount}} для проверки {{b_to_c}}',
				'id_message'			=>'Anda telah menerima {{amount}} koin untuk verifikasi {{b_to_c}}',
				'zh_message'			=>'您已收到{{b_to_c}}验证的{{amount}}硬币',
			),
			array(
				'notification_type'		=>'145',
				'message'				=>'You have received {{amount}} bonus for {{b_to_c}} verification by your friend',
				'en_message'			=>'You have received {{amount}} bonus for {{b_to_c}} verification by your friend',
				'hi_message'			=>'आपके मित्र द्वारा {{b_to_c}} सत्यापन के लिए आपको {{amount}} बोनस प्राप्त हुआ है',
				'guj_message'			=>'તમે તમારા મિત્ર દ્વારા {{b_to_c}} ચકાસણી માટે {{amount}} બોનસ પ્રાપ્ત કર્યું છે',
				'fr_message'			=>'Vous avez reçu {{amount}} bonus pour {{b_to_c}} vérification par votre ami',
				'ben_message'			=>'আপনি আপনার বন্ধু দ্বারা {{b_to_c}} যাচাইয়ের জন্য {{amount}} বোনাস পেয়েছেন।',
				'pun_message'			=>'ਤੁਹਾਨੂੰ ਤੁਹਾਡੇ ਦੋਸਤ ਦੁਆਰਾ {{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ {{amount}} ਬੋਨਸ ਮਿਲਿਆ ਹੈ',
				'tam_message'			=>'உங்கள் நண்பரால் சரிபார்க்க {{b_to_c}} க்கான {{amount}} போனஸ் கிடைத்தது',
				'th_message'			=>'คุณได้รับโบนัส {{amount}} สำหรับการตรวจสอบ {{b_to_c}} โดยเพื่อนของคุณ',
				'kn_message'			=>'ನಿಮ್ಮ ಸ್ನೇಹಿತರಿಂದ {{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ನೀವು {{amount}} ಬೋನಸ್ ಅನ್ನು ಸ್ವೀಕರಿಸಿದ್ದೀರಿ',
				'tl_message'			=>'Natanggap mo ang {{amount}} bonus para sa {{b_to_c}} verification ng iyong kaibigan',
				'ru_message'			=>'Вы получили бонус {{amount}} для {{b_to_c}} проверки вашего друга',
				'id_message'			=>'Anda telah menerima {{amount}} bonus untuk {{b_to_c}} verifikasi oleh teman Anda',
				'zh_message'			=>'您已收到您朋友验证的{{b_to_c}}验证的{{amount}}奖金',
			),
			array(
				'notification_type'		=>'146',
				'message'				=>'You have received {{currency}}{{amount}} real cash for {{b_to_c}} verification by your friend',
				'en_message'			=>'You have received {{currency}}{{amount}} real cash for {{b_to_c}} verification by your friend',
				'hi_message'			=>'आपके मित्र द्वारा {{b_to_c}} सत्यापन के लिए आपको {{currency}}{{amount}} वास्तविक नकद प्राप्त हुई है',
				'guj_message'			=>'તમે તમારા મિત્ર દ્વારા {{currency}}{{amount}} {{b_to_c}} વાસ્તવિક રોકડ પ્રાપ્ત થઈ છે',
				'fr_message'			=>'Vous avez reçu {{currency}}{{amount}} Véritable argent pour {{b_to_c}} vérification par votre ami',
				'ben_message'			=>'আপনি {{currency}}{{amount}} {{b_to_c}} যাচাইয়ের জন্য যাচাই করেছেন আপনার বন্ধুর জন্য',
				'pun_message'			=>'ਤੁਹਾਨੂੰ ਆਪਣੇ ਦੋਸਤ ਦੁਆਰਾ {{currency}}{{amount}} {{b_to_c}} "ਤੁਹਾਡੇ ਦੋਸਤ ਦੁਆਰਾ ਤਸਦੀਕ ਲਈ ਅਸਲ ਨਕਦ ਪ੍ਰਾਪਤ ਹੋਇਆ ਹੈ',
				'tam_message'			=>'உங்கள் நண்பரால் {{b_to_c}} சரிபார்ப்புக்காக {{currency}}{{amount}} உண்மையான பணத்தைப் பெற்றுள்ளீர்கள்',
				'th_message'			=>'คุณได้รับ {{currency}}{{amount}} เงินสดจริงสำหรับการตรวจสอบ {{b_to_c}} โดยเพื่อนของคุณ',
				'kn_message'			=>'ನಿಮ್ಮ ಸ್ನೇಹಿತರಿಂದ {{b_to_c}} ಗಾಗಿ ನೀವು {{currency}}{{amount}} ವಸಾಹತುವನ್ನು ಸ್ವೀಕರಿಸಿದ್ದೀರಿ',
				'tl_message'			=>'Nakatanggap ka ng {{currency}}{{amount}} real cash para sa {{b_to_c}} verification ng iyong kaibigan',
				'ru_message'			=>'Вы получили {{currency}}{{amount}} Real Cash for {{b_to_c}} подтверждение вашего друга',
				'id_message'			=>'Anda telah menerima {{currency}}{{amount}} uang tunai nyata untuk {{b_to_c}} verifikasi oleh teman Anda',
				'zh_message'			=>'您已收到{{currency}}{{amount}}验证的{{b_to_c}}验证',
			),
			array(
				'notification_type'		=>'147',
				'message'				=>'You have received {{amount}} coins for {{b_to_c}} verification by your friend',
				'en_message'			=>'You have received {{amount}} coins for {{b_to_c}} verification by your friend',
				'hi_message'			=>'आपके मित्र द्वारा सत्यापन {{b_to_c}} के लिए आपको {{amount}} सिक्के प्राप्त हुए हैं',
				'guj_message'			=>'તમને તમારા મિત્ર દ્વારા {{b_to_c}} ચકાસણી માટે {{amount}} સિક્કા મળ્યા છે',
				'fr_message'			=>'Vous avez reçu {{amount}} monnaie pour {{b_to_c}} vérification par votre ami',
				'ben_message'			=>'আপনি আপনার বন্ধুর দ্বারা {{b_to_c}} যাচাইয়ের জন্য এর জন্য {{amount}} কয়েন পেয়েছেন।',
				'pun_message'			=>'ਤੁਹਾਨੂੰ ਤੁਹਾਡੇ ਦੋਸਤ ਦੁਆਰਾ {{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ {{amount}} ਸਿੱਕੇ ਪ੍ਰਾਪਤ ਹੋਏ ਹਨ',
				'tam_message'			=>'உங்கள் நண்பரால் சரிபார்க்க {{b_to_c}} க்கான {{amount}} நாணயங்களைப் பெற்றுள்ளீர்கள்',
				'th_message'			=>'คุณได้รับเหรียญ {{amount}} สำหรับการตรวจสอบ {{b_to_c}} โดยเพื่อนของคุณ',
				'kn_message'			=>'ನಿಮ್ಮ ಸ್ನೇಹಿತರಿಂದ {{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ನೀವು {{amount}} ನಾಣ್ಯಗಳನ್ನು ಸ್ವೀಕರಿಸಿದ್ದೀರಿ',
				'tl_message'			=>'Natanggap mo ang {{amount}} barya para sa {{b_to_c}} verification ng iyong kaibigan',
				'ru_message'			=>'Вы получили монеты {{amount}} для {{b_to_c}} проверки вашего друга',
				'id_message'			=>'Anda telah menerima {{amount}} koin untuk {{b_to_c}} verifikasi oleh teman Anda',
				'zh_message'			=>'您的朋友收到了{{b_to_c}}验证的{{amount}}硬币',
			),
			array(
				'notification_type'		=>'148',
				'message'				=>'You have received {{amount}} bonus for {{b_to_c}} verification',
				'en_message'			=>'You have received {{amount}} bonus for {{b_to_c}} verification',
				'hi_message'			=>'आपको {{b_to_c}} सत्यापन के लिए {{amount}} बोनस प्राप्त हुआ है',
				'guj_message'			=>'તમે {{b_to_c}} ચકાસણી માટે {{amount}} બોનસ પ્રાપ્ત કર્યું છે',
				'fr_message'			=>'Vous avez reçu {{amount}} bonus pour {{b_to_c}} vérification',
				'ben_message'			=>'আপনি {{amount}} বোনাস {{b_to_c}} যাচাইয়ের জন্য পেয়েছেন',
				'pun_message'			=>'ਤੁਹਾਨੂੰ {{b_to_c}} ਤਸਦੀਕ ਲਈ {{amount}} ਬੋਨਸ ਪ੍ਰਾਪਤ ਹੋਇਆ ਹੈ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கு {{amount}} போனஸ் கிடைத்தது',
				'th_message'			=>'คุณได้รับโบนัส {{amount}} สำหรับการตรวจสอบ {{b_to_c}}',
				'kn_message'			=>'ನೀವು {{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ {{amount}} ಬೋನಸ್ಗಳನ್ನು ಸ್ವೀಕರಿಸಿದ್ದೀರಿ',
				'tl_message'			=>'Natanggap mo ang {{amount}} bonus para sa {{b_to_c}} verification',
				'ru_message'			=>'Вы получили бонус {{amount}} для {{b_to_c}} проверки',
				'id_message'			=>'Anda telah menerima {{amount}} bonus untuk verifikasi {{b_to_c}}',
				'zh_message'			=>'您已收到{{b_to_c}}验证的{{amount}}奖金',
			),
			array(
				'notification_type'		=>'149',
				'message'				=>'You have received {{currency}}{{amount}} real cash for {{b_to_c}} verification',
				'en_message'			=>'You have received {{currency}}{{amount}} real cash for {{b_to_c}} verification',
				'hi_message'			=>'{{b_to_c}} सत्यापन के लिए आपको {{currency}}{{amount}} वास्तविक नकद मिली है',
				'guj_message'			=>'તમે {{b_to_c}} ચકાસણી માટે {{currency}}{{amount}} વાસ્તવિક રોકડ પ્રાપ્ત થઈ છે',
				'fr_message'			=>'Vous avez reçu {{currency}}{{amount}} réel argent pour {{b_to_c}} vérification',
				'ben_message'			=>'আপনি {{currency}}{{amount}} {{b_to_c}} যাচাইয়ের জন্য আসল নগদ পেয়েছেন',
				'pun_message'			=>'ਤੁਹਾਨੂੰ {{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ {{currency}}{{amount}} ਅਸਲ ਨਕਦੀ ਪ੍ਰਾਪਤ ਹੋਈ ਹੈ',
				'tam_message'			=>'{{currency}}{{amount}} {{b_to_c}} சரிபார்ப்புக்கான உண்மையான பணத்தை நீங்கள் பெற்றுள்ளீர்கள்',
				'th_message'			=>'คุณได้รับ {{currency}}{{amount}} เงินสดจริงสำหรับการตรวจสอบ {{b_to_c}}',
				'kn_message'			=>'{{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ನೀವು {{currency}}{{amount}} ರಿಯಲ್ ನಗದು ಸ್ವೀಕರಿಸಿದ್ದೀರಿ',
				'tl_message'			=>'Nakatanggap ka ng {{currency}}{{amount}} real cash para sa {{b_to_c}} verification',
				'ru_message'			=>'Вы получили {{currency}}{{amount}} Real Cash for {{b_to_c}} проверки',
				'id_message'			=>'Anda telah menerima {{currency}}{{amount}} uang tunai nyata untuk {{b_to_c}} verifikasi',
				'zh_message'			=>'您已收到{{currency}}{{amount}} {{b_to_c}}验证的真实现金',
			),
			array(
				'notification_type'		=>'150',
				'message'				=>'You have received {{amount}} coins for {{b_to_c}} verification',
				'en_message'			=>'You have received {{amount}} coins for {{b_to_c}} verification',
				'hi_message'			=>'आपको {{b_to_c}} सत्यापन के लिए {{amount}} सिक्के प्राप्त हुए हैं',
				'guj_message'			=>'તમે {{b_to_c}} ચકાસણી માટે {{amount}} સિક્કા પ્રાપ્ત થયા છે',
				'fr_message'			=>'Vous avez reçu {{amount}} monnaies pour {{b_to_c}} vérification',
				'ben_message'			=>'আপনি {{amount}} এর জন্য {{b_to_c}} যাচাইয়ের জন্য পেয়েছেন',
				'pun_message'			=>'ਤੁਹਾਨੂੰ {{b_to_c}} {{amount}} ਪ੍ਰਾਪਤ ਕਰੋ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கான {{amount}} நாணயங்களைப் பெற்றுள்ளீர்கள்',
				'th_message'			=>'คุณได้รับเหรียญ {{amount}} สำหรับการตรวจสอบ {{b_to_c}}',
				'kn_message'			=>'ನೀವು {{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ {{amount}} ನಾಣ್ಯಗಳನ್ನು ಸ್ವೀಕರಿಸಿದ್ದೀರಿ',
				'tl_message'			=>'Nakatanggap ka ng {{amount}} barya para sa {{b_to_c}} verification',
				'ru_message'			=>'Вы получили монеты {{amount}} для проверки {{b_to_c}}',
				'id_message'			=>'Anda telah menerima {{amount}} koin untuk verifikasi {{b_to_c}}',
				'zh_message'			=>'您已收到{{b_to_c}}验证的{{amount}}硬币',
			),
	   );
	   $this->db->update_batch(NOTIFICATION_DESCRIPTION,$update_crypto_notification_arr,'notification_type');

		$result = $this->db->select('*')->from(EMAIL_TEMPLATE)->where('notification_type',595)->get()->num_rows();
        if(!$result){
            $sql = "INSERT INTO ".$this->db->dbprefix(EMAIL_TEMPLATE)." (template_name, subject, template_path, notification_type, status, type, email_body, message_body, display_label, date_added, modified_date) VALUES
            ('admin-crypto-approve', 'Your crypto address whitelisted', 'admin-crypto-approve', 595, 1, 0, NULL, NULL, 'Crypto Address Whitelisted', NULL, NULL);";
            $this->db->query($sql);
        }

		$sql = "ALTER TABLE `vi_user_bank_detail` CHANGE `upi_id` `upi_id` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL";
        $this->db->query($sql);


		$update_crypto_transaction_arr = array(
			array(
				'source'				=>'132',
				'en_message'			=>'Bonus for {{b_to_c}} Verification',
				'hi_message'			=>'{{b_to_c}} सत्यापन के लिए बोनस',
				'guj_message'			=>'{{b_to_c}} ચકાસણી માટે બોનસ',
				'fr_message'			=>'Bonus pour la vérification {{b_to_c}}',
				'ben_message'			=>'{{b_to_c}} যাচাইকরণের জন্য বোনাস',
				'pun_message'			=>'{{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ ਬੋਨਸ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கான போனஸ்',
				'th_message'			=>'โบนัสสำหรับการยืนยัน {{b_to_c}}',
				'kn_message'			=>'{{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ಬೋನಸ್',
				'tl_message'			=>'{{b_to_c}} ధృవీకరణ కోసం బోనస్',
				'ru_message'			=>'Бонус за проверку {{b_to_c}}',
				'id_message'			=>'Bonus untuk {{b_to_c}} Verifikasi',
				'zh_message'			=>'{{b_to_c}} 验证奖励',
			),
			array(
				'source'				=>'133',
				'en_message'			=>'Real amount for {{b_to_c}} Verification',
				'hi_message'			=>'{{b_to_c}} सत्यापन के लिए वास्तविक राशि',
				'guj_message'			=>'{{b_to_c}} ચકાસણી માટે વાસ્તવિક રકમ',
				'fr_message'			=>'Montant réel pour {{b_to_c}} Vérification',
				'ben_message'			=>'{{b_to_c}} যাচাইকরণের জন্য আসল পরিমাণ',
				'pun_message'			=>'{{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ ਅਸਲ ਰਕਮ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்பிற்கான உண்மையான தொகை',
				'th_message'			=>'จำนวนเงินจริงสำหรับการยืนยัน {{b_to_c}}',
				'kn_message'			=>'{{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ನಿಜವಾದ ಮೊತ್ತ',
				'tl_message'			=>'{{b_to_c}} ధృవీకరణ కోసం నిజమైన మొత్తం',
				'ru_message'			=>'Реальная сумма для {{b_to_c}} подтверждения',
				'id_message'			=>'Jumlah nyata untuk {{b_to_c}} Verifikasi',
				'zh_message'			=>'{{b_to_c}} 验证的实际金额',
			),
			array(
				'source'				=>'134',
				'en_message'			=>'Coins for {{b_to_c}} Verification',
				'hi_message'			=>'{{b_to_c}} सत्यापन के लिए सिक्के',
				'guj_message'			=>'{{b_to_c}} ચકાસણી માટેના સિક્કા',
				'fr_message'			=>'Pièces pour la vérification {{b_to_c}}',
				'ben_message'			=>'{{b_to_c}} যাচাইকরণের জন্য কয়েন',
				'pun_message'			=>'{{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ ਸਿੱਕੇ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கான நாணயங்கள்',
				'th_message'			=>'เหรียญสำหรับการยืนยัน {{b_to_c}}',
				'kn_message'			=>'{{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ನಾಣ್ಯಗಳು',
				'tl_message'			=>'{{b_to_c}} ధృవీకరణ కోసం నాణేలు',
				'ru_message'			=>'Монеты для проверки {{b_to_c}}',
				'id_message'			=>'Koin untuk Verifikasi {{b_to_c}}',
				'zh_message'			=>'{{b_to_c}} 验证的硬币',
			),
			array(
				'source'				=>'138',
				'en_message'			=>'Bonus for {{b_to_c}} Verification',
				'hi_message'			=>'{{b_to_c}} सत्यापन के लिए बोनस',
				'guj_message'			=>'{{b_to_c}} ચકાસણી માટે બોનસ',
				'fr_message'			=>'Bonus pour la vérification {{b_to_c}}',
				'ben_message'			=>'{{b_to_c}} যাচাইকরণের জন্য বোনাস',
				'pun_message'			=>'{{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ ਬੋਨਸ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கான போனஸ்',
				'th_message'			=>'โบนัสสำหรับการยืนยัน {{b_to_c}}',
				'kn_message'			=>'{{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ಬೋನಸ್',
				'tl_message'			=>'{{b_to_c}} ధృవీకరణ కోసం బోనస్',
				'ru_message'			=>'Бонус за проверку {{b_to_c}}',
				'id_message'			=>'Bonus untuk {{b_to_c}} Verifikasi',
				'zh_message'			=>'{{b_to_c}} 验证奖励',
			),
			array(
				'source'				=>'139',
				'en_message'			=>'Real amount for {{b_to_c}} Verification',
				'hi_message'			=>'{{b_to_c}} सत्यापन के लिए वास्तविक राशि',
				'guj_message'			=>'{{b_to_c}} ચકાસણી માટે વાસ્તવિક રકમ',
				'fr_message'			=>'Montant réel pour {{b_to_c}} Vérification',
				'ben_message'			=>'{{b_to_c}} যাচাইকরণের জন্য আসল পরিমাণ',
				'pun_message'			=>'{{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ ਅਸਲ ਰਕਮ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்பிற்கான உண்மையான தொகை',
				'th_message'			=>'จำนวนเงินจริงสำหรับการยืนยัน {{b_to_c}}',
				'kn_message'			=>'{{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ನಿಜವಾದ ಮೊತ್ತ',
				'tl_message'			=>'{{b_to_c}} ధృవీకరణ కోసం నిజమైన మొత్తం',
				'ru_message'			=>'Реальная сумма для {{b_to_c}} подтверждения',
				'id_message'			=>'Jumlah nyata untuk {{b_to_c}} Verifikasi',
				'zh_message'			=>'{{b_to_c}} 验证的实际金额',
			),
			array(
				'source'				=>'140',
				'en_message'			=>'Coins for {{b_to_c}} Verification',
				'hi_message'			=>'{{b_to_c}} सत्यापन के लिए सिक्के',
				'guj_message'			=>'{{b_to_c}} ચકાસણી માટેના સિક્કા',
				'fr_message'			=>'Pièces pour la vérification {{b_to_c}}',
				'ben_message'			=>'{{b_to_c}} যাচাইকরণের জন্য কয়েন',
				'pun_message'			=>'{{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ ਸਿੱਕੇ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கான நாணயங்கள்',
				'th_message'			=>'เหรียญสำหรับการยืนยัน {{b_to_c}}',
				'kn_message'			=>'{{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ನಾಣ್ಯಗಳು',
				'tl_message'			=>'{{b_to_c}} ధృవీకరణ కోసం నాణేలు',
				'ru_message'			=>'Монеты для проверки {{b_to_c}}',
				'id_message'			=>'Koin untuk Verifikasi {{b_to_c}}',
				'zh_message'			=>'{{b_to_c}} 验证的硬币',
			),
			array(
				'source'				=>'141',
				'en_message'			=>'Bonus for {{b_to_c}} Verification',
				'hi_message'			=>'{{b_to_c}} सत्यापन के लिए बोनस',
				'guj_message'			=>'{{b_to_c}} ચકાસણી માટે બોનસ',
				'fr_message'			=>'Bonus pour la vérification {{b_to_c}}',
				'ben_message'			=>'{{b_to_c}} যাচাইকরণের জন্য বোনাস',
				'pun_message'			=>'{{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ ਬੋਨਸ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கான போனஸ்',
				'th_message'			=>'โบนัสสำหรับการยืนยัน {{b_to_c}}',
				'kn_message'			=>'{{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ಬೋನಸ್',
				'tl_message'			=>'{{b_to_c}} ధృవీకరణ కోసం బోనస్',
				'ru_message'			=>'Бонус за проверку {{b_to_c}}',
				'id_message'			=>'Bonus untuk {{b_to_c}} Verifikasi',
				'zh_message'			=>'{{b_to_c}} 验证奖励',
			),
			array(
				'source'				=>'142',
				'en_message'			=>'Real amount for {{b_to_c}} Verification',
				'hi_message'			=>'{{b_to_c}} सत्यापन के लिए वास्तविक राशि',
				'guj_message'			=>'{{b_to_c}} ચકાસણી માટે વાસ્તવિક રકમ',
				'fr_message'			=>'Montant réel pour {{b_to_c}} Vérification',
				'ben_message'			=>'{{b_to_c}} যাচাইকরণের জন্য আসল পরিমাণ',
				'pun_message'			=>'{{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ ਅਸਲ ਰਕਮ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்பிற்கான உண்மையான தொகை',
				'th_message'			=>'จำนวนเงินจริงสำหรับการยืนยัน {{b_to_c}}',
				'kn_message'			=>'{{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ನಿಜವಾದ ಮೊತ್ತ',
				'tl_message'			=>'{{b_to_c}} ధృవీకరణ కోసం నిజమైన మొత్తం',
				'ru_message'			=>'Реальная сумма для {{b_to_c}} подтверждения',
				'id_message'			=>'Jumlah nyata untuk {{b_to_c}} Verifikasi',
				'zh_message'			=>'{{b_to_c}} 验证的实际金额',
			),
			array(
				'source'				=>'143',
				'en_message'			=>'Coins for {{b_to_c}} Verification',
				'hi_message'			=>'{{b_to_c}} सत्यापन के लिए सिक्के',
				'guj_message'			=>'{{b_to_c}} ચકાસણી માટેના સિક્કા',
				'fr_message'			=>'Pièces pour la vérification {{b_to_c}}',
				'ben_message'			=>'{{b_to_c}} যাচাইকরণের জন্য কয়েন',
				'pun_message'			=>'{{b_to_c}} ਪੁਸ਼ਟੀਕਰਨ ਲਈ ਸਿੱਕੇ',
				'tam_message'			=>'{{b_to_c}} சரிபார்ப்புக்கான நாணயங்கள்',
				'th_message'			=>'เหรียญสำหรับการยืนยัน {{b_to_c}}',
				'kn_message'			=>'{{b_to_c}} ಪರಿಶೀಲನೆಗಾಗಿ ನಾಣ್ಯಗಳು',
				'tl_message'			=>'{{b_to_c}} ధృవీకరణ కోసం నాణేలు',
				'ru_message'			=>'Монеты для проверки {{b_to_c}}',
				'id_message'			=>'Koin untuk Verifikasi {{b_to_c}}',
				'zh_message'			=>'{{b_to_c}} 验证的硬币',
			),
		);
		$this->db->update_batch(TRANSACTION_MESSAGES,$update_crypto_transaction_arr,'source');


		$referral_master_arr = array(
			array(
				'affiliate_master_id'				=>'16',
				'affiliate_description'			=>'Bank/Crypto Verify w/o referral',
			),
			array(
				'affiliate_master_id'				=>'17',
				'affiliate_description'			=>'Bank/Crypto Verify with referral',
			),
		);
		$this->db->update_batch(AFFILIATE_MASTER,$referral_master_arr,'affiliate_master_id');
	}

	public function down() {
		//$this->dbforge->drop_column(USER, 'bs_status');
	}

}
