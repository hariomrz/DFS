<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_pan_card_notification extends CI_Migration 
{
  
    public function up() {
        $this->db->where('notification_type', 44);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Your {{p_to_id}} card has been rejected. Reason: {{pan_rejected_reason}}',
            'en_message' => 'Your {{p_to_id}} card has been rejected. Reason: {{pan_rejected_reason}}',
            'hi_message' => 'आपका {{p_to_id}} कार्ड अस्वीकार कर दिया गया है. {{pan_rejected_reason}}',
            'guj_message' => 'તમારું {{p_to_id}} કાર્ડ નકારવામાં આવી છે. {{pan​_rejected_reason}}',
            'fr_message' => 'Votre {{p_to_id}} card a été rejeté. Motif: {{pan_rejected_reason}}',
            'ben_message' => 'আপনার {{p_to_id}} কার্ড প্রত্যাখ্যান করা হয়েছে। কারণ: {{pan_rejected_reason}}',
            'pun_message' => 'ਤੁਹਾਡਾ {{p_to_id}} ਕਾਰਡ ਖਾਰਜ ਕਰ ਦਿੱਤਾ ਗਿਆ ਹੈ. ਕਾਰਨ: {{pan_rejected_reason}}',
            'tam_message' => 'உங்கள் {{p_to_id}} card நிராகரிக்கப்பட்டுள்ளது. காரணம்: {{pan_rejected_reason}}',
            'th_message' => '{{p_to_id}} card ของคุณได้รับการปฏิเสธ เหตุผล: {{pan_rejected_reason}}',
            'kn_message' => 'ನಿಮ್ಮ {{p_to_id}} ಕಾರ್ಡ್ ತಿರಸ್ಕರಿಸಲಾಗಿದೆ. ಕಾರಣ: {{pan_rejected_reason}}',
            'kn_subject' => '',
            'ru_message' => 'Ваш {{p_to_id}} card отклонено. Причина: {{pan_rejected_reason}}',
            'ru_subject' => '',
            'id_message' => '{{p_to_id}} card Anda telah ditolak. Alasan: {{pan_rejected_reason}} pan_rejected_reason',
            'id_subject' => '',
            'tl_message' => 'Ang iyong {{p_to_id}} card ay tinanggihan. Dahilan: {{pan_rejected_reason}}',
            'tl_subject' => '',
            'zh_message' => '您潘卡已被拒绝。原因：{{pan_rejected_reason}}',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 35);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Congratulations ! {{name}} referred by you, has verified {{p_to_id}} card on the site. You have earned {{amount}} Bonus.',
            'en_message' => 'Congratulations ! {{name}} referred by you, has verified {{p_to_id}} card on the site. You have earned {{amount}} Bonus.',
            'hi_message' => 'बधाई हो ! आपको हमारी साइट पर अपने मित्र {{name}} का {{p_to_id}} कार्ड के जांच पूर्ण के लिए B{{amount}} का बोनस मिला है।',
            'guj_message' => 'અભિનંદન! તમે ₹ {{amount}} તેમના મિત્ર એક સંપૂર્ણ તપાસ માટે {{name}} {{p_to_id}} અમારી સાઇટ પર કાર્ડની એક બોનસ કમાવ્યા છે.',
            'fr_message' => 'Toutes nos félicitations ! {{name}} référé par vous, a vérifié {{p_to_id}} card sur le site. Vous avez gagné {{amount}} Bonus.',
            'ben_message' => 'অভিনন্দন! {{name}} আপনার দ্বারা উল্লেখ করা হয়েছে, সাইটে {{p_to_id}} কার্ড যাচাই করেছে। তুমি অর্জন করেছ {{amount}} বোনাস.',
            'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਦੱਸੇ ਗਏ {{name}} ਨੇ ਸਾਈਟ 'ਤੇ {{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਕੀਤੀ ਹੈ. ਤੁਸੀਂ {{amount}} ਬੋਨਸ ਪ੍ਰਾਪਤ ਕੀਤਾ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'ขอแสดงความยินดี! {{name}} ชื่อเรียกโดยคุณได้ตรวจสอบ {{p_to_id}} card บนเว็บไซต์ คุณได้รับ {{amount}} จำนวนโบนัส',
            'kn_message' => 'ಅಭಿನಂದನೆಗಳು! {{name}} ನೀವು ಮೂಲಕ ಉಲ್ಲೇಖಿಸಲಾಗುತ್ತದೆ ಸೈಟ್ನಲ್ಲಿ {{p_to_id}} ‌ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ಬೋನಸ್ ಮಾಡಿದ್ದಾರೆ.',
            'kn_subject' => '',
            'ru_message' => 'Поздравляем! {{name}} называется вами, проверил {{p_to_id}} card на сайте. Вы заработали {{amount}} бонус.',
            'ru_subject' => '',
            'id_message' => 'Selamat! {{name}} disebut oleh Anda, telah diverifikasi {{p_to_id}} card di situs. Anda telah mendapatkan {{amount}} Bonus.',
            'id_subject' => '',
            'tl_message' => 'Congratulations! {{Name}} na tinutukoy sa pamamagitan ng sa iyo, ay napatunayan {{p_to_id}} card sa site. Natamo mo na {{amount}} Bonus.',
            'tl_subject' => '',
            'zh_message' => '恭喜！ {{name}}由你提到，已经验证的网站上 {{p_to_id}} card。你赢得了{{amount}}奖金。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 59);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => '{{p_to_id}} card verification bonus cash {{amount}} credited to your account.',
            'en_message' => '{{p_to_id}} card verification bonus cash {{amount}} credited to your account.',
            'hi_message' => 'बधाई हो ! आपको {{p_to_id}} कार्ड के जांच पूर्ण के लिएB {{amount}} का बोनस मिला है।',
            'guj_message' => 'અભિનંદન! તમે {{p_to_id}} કાર્ડ એક સંપૂર્ણ તપાસ માટે ₹ {{amount}} એક બોનસ કમાવ્યા છે.',
            'fr_message' => 'Argent de bonus de vérification de {{p_to_id}} carte {{amount}} crédité sur votre compte.',
            'ben_message' => '{{p_to_id}} কার্ড যাচাই বোনাস নগদ আপনার অ্যাকাউন্টে {{amount}} জমা হয়েছে।',
            'pun_message' => "{{p_to_id}} ਕਾਰਡ ਵੈਰੀਫਿਕੇਸ਼ਨ ਬੋਨਸ ਨਕਦ {{amount}} your ਤੁਹਾਡੇ ਖਾਤੇ ਵਿੱਚ ਜਮ੍ਹਾਂ.",
            'tam_message' => '',
            'th_message' => 'โบนัสเงินสดตรวจสอบ {{p_to_id}} card {{amount}} โอนไปยังบัญชีของคุณ',
            'kn_message' => '{{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ಬೋನಸ್ ನಗದು {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
            'kn_subject' => '',
            'ru_message' => '{{p_to_id}} card проверки бонус наличных {{amount}} на Ваш счет.',
            'ru_subject' => '',
            'id_message' => '{{p_to_id}} card tunai verifikasi bonus {{amount}} dikreditkan ke akun Anda.',
            'id_subject' => '',
            'tl_message' => '{{p_to_id}} card bonus verification cash {{amount}}-credit sa iyong account.',
            'tl_subject' => '',
            'zh_message' => '{{p_to_id}} card验证奖金现金{{amount}}存入您的帐户。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 60);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => '{{p_to_id}} card verification real cash ₹{{amount}} credited to your account.',
            'en_message' => '{{p_to_id}} card verification real cash ₹{{amount}} credited to your account.',
            'hi_message' => 'बधाई हो ! आपको {{p_to_id}} कार्ड के जांच पूर्ण के लिए ₹ {{amount}} की राशी मिली है।',
            'guj_message' => 'અભિનંદન! તમે {{p_to_id}} કાર્ડ એક સંપૂર્ણ તપાસ માટે ₹ {{amount}} જથ્થો છે.',
            'fr_message' => 'Vérification de {{p_to_id}} card en argent réel {{amount}} crédité sur votre compte.',
            'ben_message' => '{{p_to_id}} কার্ড যাচাইয়ের আসল নগদ ₹ আপনার অ্যাকাউন্টে {{amount}} জমা হয়েছে।',
            'pun_message' => "{{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਅਸਲ ਨਕਦ {{amount}}. ਤੁਹਾਡੇ ਖਾਤੇ ਵਿੱਚ ਜਮ੍ਹਾਂ.",
            'tam_message' => 'சரிபார்ப்பு உண்மையான பண ₹ {{amount}} உங்கள் கணக்கில் வரவு.',
            'th_message' => 'ตรวจสอบ {{p_to_id}} card เงินสดจริง₹ {{amount}} โอนไปยังบัญชีของคุณ',
            'kn_message' => '{{p_to_id}} ‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನಿಜವಾದ ನಗದು ₹ {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
            'kn_subject' => '',
            'ru_message' => '{{p_to_id}} card проверка реальных денег ₹ {{amount}} на Ваш счет.',
            'ru_subject' => '',
            'id_message' => '{{p_to_id}} card verifikasi uang nyata ₹ {{amount}} dikreditkan ke akun Anda.',
            'id_subject' => '',
            'tl_message' => '{{p_to_id}} card pag-verify real cash ₹ {{amount}}-credit sa iyong account.',
            'tl_subject' => '',
            'zh_message' => '{{p_to_id}} card验证真正的现金₹{{amount}}存入您的帐户。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 61);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => '{{p_to_id}} card verification coins ₹{{amount}} credited to your account.',
            'en_message' => '{{p_to_id}} card verification coins ₹{{amount}} credited to your account.',
            'hi_message' => 'बधाई हो ! आपको {{p_to_id}} कार्ड के जांच पूर्ण के लिए {{amount}} सिक्के मिले है।।',
            'guj_message' => 'અભિનંદન! તમે {{p_to_id}} કાર્ડ એક સંપૂર્ણ તપાસ માટે ₹ {{amount}} સિક્કા મળી.',
            'fr_message' => 'Pièces de vérification {{p_to_id}} card {{amount}} créditées sur votre compte.',
            'ben_message' => '{{p_to_id}} কার্ড যাচাইয়ের মুদ্রা {{amount}} আপনার অ্যাকাউন্টে জমা',
            'pun_message' => "{{p_to_id}} ਕਾਰਡ ਵੈਰੀਫਿਕੇਸ਼ਨ ਸਿੱਕੇ {{amount}}. ਤੁਹਾਡੇ ਖਾਤੇ ਵਿੱਚ ਜਮ੍ਹਾਂ ਹੋ ਗਏ.",
            'tam_message' => '',
            'th_message' => 'เหรียญตรวจสอบ {{p_to_id}} card {{amount}} โอนไปยังบัญชีของคุณ',
            'kn_message' => '{{p_to_id}} ‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನಾಣ್ಯಗಳು {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
            'kn_subject' => '',
            'ru_message' => '{{p_to_id}} card проверки монет {{amount}} на Ваш счет.',
            'ru_subject' => '',
            'id_message' => 'koin verifikasi {{p_to_id}} card {{amount}} dikreditkan ke akun Anda.',
            'id_subject' => '',
            'tl_message' => '{{p_to_id}} card pag-verify barya {{amount}}-credit sa iyong account.',
            'tl_subject' => '',
            'zh_message' => '{{p_to_id}} card验证硬币{{amount}}存入您的帐户。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 62);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her {{p_to_id}} card. You have earned {{amount}} bonus cash.',
            'en_message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her {{p_to_id}} card. You have earned {{amount}} bonus cash.',
            'hi_message' => 'बधाई हो ! आपको हमारी साइट पर अपने मित्र {{friend_name}} का {{p_to_id}} कार्ड के जांच पूर्ण के लिए B {{amount}} का बोनस मिला है।',
            'guj_message' => 'અભિનંદન! તમે ₹ {{amount}} તેમના મિત્ર એક સંપૂર્ણ તપાસ માટે {{friend_name}} {{p_to_id}} અમારી સાઇટ પર કાર્ડની એક બોનસ કમાવ્યા છે.',
            'fr_message' => 'Toutes nos félicitations! {{friend_name}} que vous avez référé a vérifié son {{p_to_id}} card. Vous avez gagné {{amount}} cash en bonus.',
            'ben_message' => 'অভিনন্দন! {{friend_name}} আপনার দ্বারা উল্লেখ করা তার {{p_to_id}} কার্ড যাচাই করেছে। তুমি অর্জন করেছ {{amount}} বোনাস নগদ',
            'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਦਰਸਾਏ ਗਏ _ {{friend_name}} his ਨੇ ਉਸ ਦੇ {{p_to_id}} ਕਾਰਡ ਦੀ ਪੁਸ਼ਟੀ ਕੀਤੀ ਹੈ. ਤੁਸੀਂ {{amount}} ਬੋਨਸ ਨਕਦ ਕਮਾਈ ਕੀਤੀ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'ขอแสดงความยินดี! {{friend_name}} เรียกคุณได้ verifed / {{p_to_id}} card ของเขาและเธอ คุณได้รับ {{amount}} เงินสดเงินโบนัส',
            'kn_message' => 'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಲಾಗಿದೆ ಮಾಡಿದೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
            'kn_subject' => '',
            'ru_message' => 'Поздравляем! {{friend_name}} называют вами уже verifed его / ее {{p_to_id}} card. Вы заработали {{amount}} бонус наличными.',
            'ru_subject' => '',
            'id_message' => 'Selamat! {{friend_name}} disebut oleh Anda telah verifed / nya {{p_to_id}} card nya. Anda telah mendapatkan {{amount}} bonus tunai.',
            'id_subject' => '',
            'tl_message' => 'Congratulations! {{friend_name}} na tinutukoy sa pamamagitan mo ay verifed kanyang / kanyang {{p_to_id}} card. Natamo mo na {{amount}} bonus cash.',
            'tl_subject' => '',
            'zh_message' => '恭喜！ {{friend_name}}通过你提到已经verifed他/她的{{p_to_id}} card。你赢得了{{amount}}奖金现金。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 63);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her {{p_to_id}} card. You have earned {{amount}} real cash.',
            'en_message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her {{p_to_id}} card. You have earned {{amount}} real cash.',
            'hi_message' => 'बधाई हो ! आपको हमारी साइट पर अपने मित्र {{friend_name}} का {{p_to_id}} कार्ड के जांच पूर्ण के लिए ₹ {{amount}} की राशी मिली है।',
            'guj_message' => 'અભિનંદન! તમે અમારી સાઇટ પર {સંપૂર્ણ {{p_to_id}} ના ₹ {{amount}} તપાસ માટે} {{friend_name}} તમારા મિત્રોને જથ્થો છે.',
            'fr_message' => 'Toutes nos félicitations! {{friend_name}} que vous avez référé a vérifié son {{p_to_id}} card. Vous avez gagné ₹ {{amount}} argent réel.',
            'ben_message' => 'অভিনন্দন! {{friend_name}} আপনার দ্বারা উল্লেখ করা তার {{p_to_id}} কার্ড যাচাই করেছে। আপনি অর্জন করেছেন ₹ {{amount}} আসল নগদ',
            'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਦਰਸਾਏ ਗਏ _ {{friend_name}} his ਨੇ ਉਸ ਦੇ {{p_to_id}} ਕਾਰਡ ਦੀ ਪੁਸ਼ਟੀ ਕੀਤੀ ਹੈ. ਤੁਸੀਂ cash {{amount}} ਅਸਲ ਨਕਦ ਕਮਾਈ ਕੀਤੀ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'ขอแสดงความยินดี! {{friend_name}} เรียกคุณได้ verifed / {{p_to_id}} card ของเขาและเธอ คุณได้รับ₹ {{amount}} จำนวนเงินสดจริง',
            'kn_message' => 'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಲಾಗಿದೆ ಮಾಡಿದೆ. ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
            'kn_subject' => '',
            'ru_message' => 'Поздравляем! {{friend_name}} называют вами уже verifed его / ее {{p_to_id}} card. Вы заработали ₹ {{amount}} реальные деньги.',
            'ru_subject' => '',
            'id_message' => 'Selamat! {{friend_name}} disebut oleh Anda telah verifed / nya {{p_to_id}} card nya. Anda telah mendapatkan ₹ {{amount}} uang nyata.',
            'id_subject' => '',
            'tl_message' => 'Congratulations! {{friend_name}} na tinutukoy sa pamamagitan mo ay verifed kanyang / kanyang {{p_to_id}} card. Nakaipon ka ₹ {{amount}} real cash.',
            'tl_subject' => '',
            'zh_message' => '恭喜！ {{friend_name}}通过你提到已经verifed他/她的{{p_to_id}} card。你赢得了₹{{amount}}真正的现金。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 64);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her {{p_to_id}} card. You have earned {{amount}} coins.',
            'en_message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her {{p_to_id}} card. You have earned {{amount}} coins.',
            'hi_message' => 'बधाई हो ! आपको हमारी साइट पर अपने मित्र {{friend_name}} का {{p_to_id}} कार्ड के जांच पूर्ण के लिए {{amount}} सिक्के मिले है।',
            'guj_message' => 'અભિનંદન! તમે અમારી સાઇટ પર સિક્કા {{p_to_id}} સંપૂર્ણ તપાસ માટે ₹ {{amount}} તમારા મિત્રોને {{friend_name}} પર મળી.',
            'fr_message' => 'Toutes nos félicitations! {{friend_name}} que vous avez référé a vérifié son {{p_to_id}} card. Vous avez gagné {{amount}} pièces.',
            'ben_message' => 'অভিনন্দন! {{friend_name}} আপনার দ্বারা উল্লেখ করা তার {{p_to_id}} কার্ড যাচাই করেছে। আপনি {{amount}} কয়েন অর্জন করেছেন।',
            'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਦਰਸਾਏ ਗਏ _ {{friend_name}} his ਨੇ ਉਸ ਦੇ {{p_to_id}} ਕਾਰਡ ਦੀ ਪੁਸ਼ਟੀ ਕੀਤੀ ਹੈ. ਤੁਸੀਂ {{amount}} ਸਿੱਕੇ ਪ੍ਰਾਪਤ ਕੀਤੇ ਹਨ.",
            'tam_message' => '',
            'th_message' => 'ขอแสดงความยินดี! {{friend_name}} เรียกคุณได้ verifed / {{p_to_id}} card ของเขาและเธอ คุณได้รับ {{amount}} จำนวนเหรียญ',
            'kn_message' => 'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಲಾಗಿದೆ ಮಾಡಿದೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
            'kn_subject' => '',
            'ru_message' => 'Поздравляем! {{friend_name}} называют вами уже verifed его / ее {{p_to_id}} card. Вы заработали {{amount}} монеты.',
            'ru_subject' => '',
            'id_message' => 'Selamat! {{friend_name}} disebut oleh Anda telah verifed / nya {{p_to_id}} card nya. Anda telah mendapatkan {{amount}} koin.',
            'id_subject' => '',
            'tl_message' => 'Congratulations! {{friend_name}} na tinutukoy sa pamamagitan mo ay verifed kanyang / kanyang {{p_to_id}} card. Natamo mo na {{amount}} barya.',
            'tl_subject' => '',
            'zh_message' => '恭喜！ {{friend_name}}通过你提到已经verifed他/她的{{p_to_id}} card。你赢得了{{amount}}硬币。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 65);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Hurray! By using the referral code you have earned extra {{amount}} bonus cash for verifying the {{p_to_id}} card.',
            'en_message' => 'Hurray! By using the referral code you have earned extra {{amount}} bonus cash for verifying the {{p_to_id}} card.',
            'hi_message' => 'बधाई हो ! आपको रेफरल व {{p_to_id}} कार्ड के जांच पूर्ण के लिए B {{amount}} का अधिक बोनस मिला है।',
            'guj_message' => 'અભિનંદન! તમે રેફરલ તપાસો અને {{p_to_id}} વધુ બોનસ ₹ {{amount}} ને પૂર્ણ કરવા મળી.',
            'fr_message' => 'Hourra! En utilisant le code de parrainage, vous avez gagné un bonus supplémentaire de {{amount}} en espèces pour la vérification du {{p_to_id}} card.',
            'ben_message' => 'অভিনন্দন! রেফারেল কোড ব্যবহার করে আপনি {{p_to_id}} কার্ড যাচাই করার জন্য অতিরিক্ত {{amount}} বোনাস নগদ অর্জন করেছেন।',
            'pun_message' => "ਹੁਰੈ! ਰੈਫਰਲ ਕੋਡ ਦੀ ਵਰਤੋਂ ਕਰਕੇ ਤੁਸੀਂ {{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਕਰਨ ਲਈ ਵਾਧੂ {{amount}} ਬੋਨਸ ਨਕਦ ਕਮਾਇਆ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'เย่! โดยใช้รหัสอ้างอิงที่คุณได้รับเป็นพิเศษ {{amount}} จำนวนโบนัสเงินสดในการตรวจสอบ {{p_to_id}} card',
            'kn_message' => 'ಭಲೆ! ನೀವು {{p_to_id}} ‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆಯ ಹೆಚ್ಚುವರಿ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು.',
            'kn_subject' => '',
            'ru_message' => 'Ура! При использовании кода направления вы заработали дополнительные {{amount}} бонус наличными для проверки {{p_to_id}} card.',
            'ru_subject' => '',
            'id_message' => 'Hore! Dengan menggunakan kode referral Anda telah mendapatkan uang ekstra {{amount}} bonus untuk memverifikasi {{p_to_id}} card tersebut.',
            'id_subject' => '',
            'tl_message' => 'Hurrah! Sa pamamagitan ng paggamit ng mga referral code natamo mo na dagdag na {{amount}} bonus cash para sa pagpapatunay ng {{p_to_id}} card.',
            'tl_subject' => '',
            'zh_message' => '欢呼！通过使用你已经获得额外{{amount}}奖励现金验证{{p_to_id}} card的推荐码。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 66);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Hurray! By using the referral code you have earned extra ₹{{amount}} real cash for verifying the {{p_to_id}} card.',
            'en_message' => 'Hurray! By using the referral code you have earned extra ₹{{amount}} real cash for verifying the {{p_to_id}} card.',
            'hi_message' => 'बधाई हो ! आपको रेफरल व {{p_to_id}} कार्ड के जांच पूर्ण के लिए ₹ {{amount}} की अधिक राशी मिली है।',
            'guj_message' => 'અભિનંદન! તમે રેફરલ્સ અને {{p_to_id}} કાર્ડ એક સંપૂર્ણ તપાસ માટે વધુ નાણાં માટે ₹ {{amount}} હોય છે.',
            'fr_message' => 'Hourra! En utilisant le code de parrainage, vous avez gagné un montant réel de ₹ {{amount}} en espèces supplémentaires pour la vérification du {{p_to_id}} card.',
            'ben_message' => 'অভিনন্দন! রেফারেল কোড ব্যবহার করে আপনি অতিরিক্ত ₹ অর্জন করেছেন ₹ {{amount}} {{p_to_id}} কার্ড যাচাই করার জন্য প্রকৃত নগদ।',
            'pun_message' => "ਹੁਰੈ! ਰੈਫਰਲ ਕੋਡ ਦੀ ਵਰਤੋਂ ਕਰਕੇ ਤੁਸੀਂ {{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਕਰਨ ਲਈ ਵਾਧੂ cash {{amount}} ਅਸਲ ਨਕਦ ਕਮਾਈ ਕੀਤੀ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'เย่! โดยใช้รหัสอ้างอิงที่คุณได้รับเป็นพิเศษ₹ {{amount}} จำนวนเงินสดจริงในการตรวจสอบ {{p_to_id}} card',
            'kn_message' => 'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆಯ ಹೆಚ್ಚುವರಿ ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
            'kn_subject' => '',
            'ru_message' => 'Ура! При использовании кода направления вы заработали дополнительный ₹ {{amount}} реальные деньги для проверки {{p_to_id}} card.',
            'ru_subject' => '',
            'id_message' => 'Hore! Dengan menggunakan kode referral Anda telah mendapatkan tambahan ₹ {{amount}} uang nyata untuk memverifikasi {{p_to_id}} card tersebut.',
            'id_subject' => '',
            'tl_message' => 'Hurrah! Sa pamamagitan ng paggamit ng mga referral code natamo mo na dagdag na ₹ {{amount}} real cash para sa pagpapatunay ng {{p_to_id}} card.',
            'tl_subject' => '',
            'zh_message' => '欢呼！通过使用推荐码，你已经赢得了额外的₹{{amount}}真正的现金用于验证{{p_to_id}} card。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 67);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Hurray! By using the referral code you have earned extra {{amount}} coins for verifying the {{p_to_id}} card.',
            'en_message' => 'Hurray! By using the referral code you have earned extra {{amount}} coins for verifying the {{p_to_id}} card.',
            'hi_message' => 'बधाई हो ! आपको रेफरल व {{p_to_id}} कार्ड के जांच पूर्ण के लिए {{amount}} अधिक सिक्के मिले है।',
            'guj_message' => 'અભિનંદન! તમે રેફરલ્સ અને {{p_to_id}} ₹ {{amount}} વધુ સિક્કા એક સંપૂર્ણ તપાસ માટે મળ્યા હતા.',
            'fr_message' => 'Hourra! En utilisant le code de parrainage, vous avez gagné des {{amount}} pièces supplémentaires pour vérifier le {{p_to_id}} card.',
            'ben_message' => 'অভিনন্দন! রেফারেল কোড ব্যবহার করে আপনি {{p_to_id}} কার্ড যাচাই করার জন্য অতিরিক্ত {{amount}} কয়েন অর্জন করেছেন।',
            'pun_message' => "ਹੁਰੈ! ਰੈਫਰਲ ਕੋਡ ਦੀ ਵਰਤੋਂ ਕਰਕੇ ਤੁਸੀਂ {{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਕਰਨ ਲਈ ਵਾਧੂ cash {{amount}} ਅਸਲ ਨਕਦ ਕਮਾਈ ਕੀਤੀ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'เย่! โดยใช้รหัสอ้างอิงที่คุณได้รับเป็นพิเศษ {{amount}} จำนวนเหรียญสำหรับการตรวจสอบ {{p_to_id}} card',
            'kn_message' => 'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆಯ ಹೆಚ್ಚುವರಿ {{amount}} ನಾಣ್ಯಗಳು ತಂದುಕೊಟ್ಟಿವೆ.',
            'kn_subject' => '',
            'ru_message' => 'Ура! При использовании кода направления вы заработали дополнительные {{amount}} монеты для проверки {{p_to_id}} card.',
            'ru_subject' => '',
            'id_message' => 'Hore! Dengan menggunakan kode referral Anda telah mendapatkan tambahan {{amount}} koin untuk memverifikasi {{p_to_id}} card tersebut.',
            'id_subject' => '',
            'tl_message' => 'Hurrah! Sa pamamagitan ng paggamit ng mga referral code natamo mo na dagdag na {{amount}} barya para sa pagpapatunay ng {{p_to_id}} card.',
            'tl_subject' => '',
            'zh_message' => '欢呼！通过使用推荐码，你已经赢得了额外的{{amount}}硬币验证{{p_to_id}} card。',
            'zh_subject' => ''
        ));


        $this->db->where('transaction_messages_id', 14);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Referral bonus for {{p_to_id}} card verification',
            'hi_message' => '{{p_to_id}} कार्ड सत्यापन के लिए रेफरल बोनस',
            'guj_message' => '{{p_to_id}} કાર્ડ ચકાસણી માટે રેફરલ બોનસ',
            'fr_message' => 'Bonus de parrainage pour la vérification de la carte {{p_to_id}}',
            'ben_message' => '{{p_to_id}} কার্ড যাচাইয়ের জন্য রেফারেল বোনাস',
            'pun_message' => "{{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਰੈਫਰਲ ਬੋਨਸ",
            'tam_message' => '',
            'th_message' => 'โบนัสผู้อ้างอิงสำหรับการยืนยันบัตร {{p_to_id}}',
            'ru_message' => 'Реферальный бонус за подтверждение карты {{p_to_id}}',
            'id_message' => 'bonus rujukan untuk verifikasi kartu {{p_to_id}}',
            'tl_message' => 'Referral bonus para sa {{p_to_id}}-verify ng card',
            'zh_message' => '{{p_to_id}} 卡验证的推荐奖金',
            'kn_message' => '{{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ಉಲ್ಲೇಖಿತ ಬೋನಸ್'
        ));

        $this->db->where('transaction_messages_id', 59);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Bonus cash awarded for {{p_to_id}} card verification',
            'hi_message' => '{{p_to_id}} कार्ड सत्यापन के लिए बोनस नकद प्रदान किया गया',
            'guj_message' => '{{p_to_id}} કાર્ડ ચકાસણી માટે બોનસ રોકડ',
            'fr_message' => 'Prime en espèces attribuée pour la vérification de la carte {{p_to_id}}',
            'ben_message' => '{{p_to_id}} কার্ড যাচাইয়ের জন্য বোনাস নগদ প্রদান করা হয়',
            'pun_message' => "{{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਬੋਨਸ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'โบนัสเงินสดที่ได้รับสำหรับ {{p_to_id}} การยืนยันบัตร',
            'ru_message' => 'Бонусные деньги за подтверждение карты {{p_to_id}}',
            'id_message' => 'Bonus uang tunai diberikan untuk verifikasi kartu {{p_to_id}}',
            'tl_message' => 'Bonus cash na ibinigay para sa {{p_to_id}}-verify ng card',
            'zh_message' => '授予 {{p_to_id}} 卡验证现金奖励',
            'kn_message'=>'ಪರಿಶೀಲನೆಗಾಗಿ ಬೋನಸ್ ನಗದು {{p_to_id}} ಕಾರ್ಡ್ ನೀಡಲಾಗುತ್ತದೆ', 
        ));

        $this->db->where('transaction_messages_id', 60);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Real Cash awarded for {{p_to_id}} card verification',
            'hi_message' => '{{p_to_id}} कार्ड सत्यापन के लिए वास्तविक नकद प्रदान किया गया',
            'guj_message' => '{{p_to_id}} કાર્ડ ચકાસણી માટે રીઅલ કેશ આપવામાં આવ્યું',
            'fr_message' => 'Real Cash décerné pour la vérification de la carte {{p_to_id}}',
            'ben_message' => '{{p_to_id}} কার্ড যাচাইয়ের জন্য রিয়েল নগদ প্রদান করা',
            'pun_message' => "{{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਅਸਲ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'รางวัลเงินสดจริงสำหรับเช็คแพน {{p_to_id}} เช็ค',
            'ru_message' => 'Real Cash присуждается за {{p_to_id}} проверки карты',
            'id_message' => 'Nyata Kas diberikan untuk verifikasi kartu {{p_to_id}}',
            'tl_message' => 'Real Cash ibinigay para sa {{p_to_id}}-verify ng card',
            'zh_message' => '真实现金奖励{{p_to_id}}卡验证',
            'kn_message'=> 'ರಿಯಲ್ ನಗದು {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
        ));

        $this->db->where('transaction_messages_id', 61);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Coins awarded for {{p_to_id}} card verification',
            'hi_message' => '{{p_to_id}} कार्ड सत्यापन के लिए सम्मानित किया गया सिक्के',
            'guj_message' => '{{p_to_id}} કાર્ડ ચકાસણી માટે સિક્કા એનાયત કરાયા',
            'fr_message' => 'Pièces attribuées pour la vérification de la carte {{p_to_id}}',
            'ben_message' => '{{p_to_id}} কার্ড যাচাইয়ের জন্য পুরষ্কার দেওয়া কয়েন',
            'pun_message' => "{{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਸਿੱਕੇ ਦਿੱਤੇ ਗਏ",
            'tam_message' => '',
            'th_message' => 'เหรียญจะได้รับสำหรับการตรวจสอบบัตร {{p_to_id}}',
            'ru_message' => 'Монеты, присуждаемые за {{p_to_id}} проверки карты',
            'id_message' => 'Koin diberikan untuk verifikasi kartu {{p_to_id}}',
            'tl_message' => 'Barya ibinigay para sa {{p_to_id}}-verify ng card',
            'zh_message' => '授予 {{p_to_id}} 卡验证币',
            'kn_message'=> '{{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು ನಾಣ್ಯಗಳು', 
        ));

        $this->db->where('transaction_messages_id', 62);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Bonus cash awarded on {{p_to_id}} card verification by Friend',
            'hi_message' => 'मित्र द्वारा {{p_to_id}} कार्ड सत्यापन पर बोनस नकद प्रदान किया गया',
            'guj_message' => 'મિત્ર દ્વારા {{p_to_id}} કાર્ડ ચકાસણી પર બોનસ રોકડ આપવામાં આવ્યું',
            'fr_message' => 'Argent bonus attribué lors de la vérification de la carte {{p_to_id}} par un ami',
            'ben_message' => 'বন্ধুর দ্বারা {{p_to_id}} কার্ড যাচাইকরণে বোনাস নগদ প্রদান করা',
            'pun_message' => "ਮਿੱਤਰ ਦੁਆਰਾ {{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਕਰਨ 'ਤੇ ਬੋਨਸ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'โบนัสเงินสดจะได้รับใน {{p_to_id}} เช็คการ์ดโดยเพื่อน',
            'ru_message' => 'Бонус наличные предоставляются на {{p_to_id}} проверки карты по другу',
            'id_message' => 'Bonus uang tunai diberikan pada verifikasi kartu {{p_to_id}} oleh Teman',
            'tl_message' => 'Bonus cash iginawad sa {{p_to_id}}-verify ng card sa pamamagitan Kaibigan',
            'zh_message' => 'The cash bonus was awarded by a friend to verify the {{p_to_id}}card',
            'kn_message'=> 'ಬೋನಸ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು', 
        ));

        $this->db->where('transaction_messages_id', 63);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Real cash awarded on {{p_to_id}} card verification by Friend',
            'hi_message' => 'मित्र द्वारा {{p_to_id}} कार्ड सत्यापन पर वास्तविक नकद राशि प्रदान की गई',
            'guj_message' => 'મિત્ર દ્વારા {{p_to_id}} કાર્ડ ચકાસણી પર વાસ્તવિક રોકડ આપવામાં આવે છે',
            'fr_message' => 'Argent réel attribué lors de la vérification de la carte {{p_to_id}} par un ami',
            'ben_message' => 'বন্ধুর দ্বারা {{p_to_id}} কার্ড যাচাইকরণে রিয়েল নগদ পুরষ্কার',
            'pun_message' => "ਮਿੱਤਰ ਦੁਆਰਾ {{p_to_id}} ਕਾਰਡ ਵੈਰੀਫਿਕੇਸ਼ਨ 'ਤੇ ਅਸਲ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'เงินสดจริงได้รับรางวัลเป็นเช็คการ์ด {{p_to_id}} จากเพื่อน',
            'ru_message' => 'Реальные денежные присуждаются на {{p_to_id}} проверки карты на друзьях',
            'id_message' => 'uang nyata diberikan pada verifikasi kartu {{p_to_id}} oleh Teman',
            'tl_message' => 'Real cash iginawad sa {{p_to_id}}-verify ng card sa pamamagitan Kaibigan',
            'zh_message' => '真实现金奖励{{p_to_id}}朋友卡验证',
            'kn_message'=> 'ರಿಯಲ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು', 
        ));

        $this->db->where('transaction_messages_id', 64);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Coins awarded on {{p_to_id}} card verification by Friend',
            'hi_message' => 'मित्र द्वारा {{p_to_id}} कार्ड सत्यापन पर दिए गए सिक्के',
            'guj_message' => 'મિત્ર દ્વારા {{p_to_id}} કાર્ડ ચકાસણી પર સિક્કા આપવામાં આવ્યા',
            'fr_message' => 'Pièces attribuées lors de la vérification de la carte {{p_to_id}} par un ami',
            'ben_message' => '{{p_to_id}} কার্ড যাচাইয়ের জন্য বন্ধুর দ্বারা পুরষ্কার দেওয়া কয়েন',
            'pun_message' => "ਮਿੱਤਰ ਦੁਆਰਾ {{p_to_id}} ਕਾਰਡ ਵੈਰੀਫਿਕੇਸ਼ਨ 'ਤੇ ਦਿੱਤੇ ਗਏ ਸਿੱਕੇ",
            'tam_message' => '',
            'th_message' => 'เหรียญจะได้รับใน {{p_to_id}} รีวิวการ์ดโดยเพื่อน',
            'ru_message' => 'Монеты, полученные во время жарки {{p_to_id}} проверки карт на друга',
            'id_message' => 'Koin diberikan verifikasi kartu {{p_to_id}} oleh Teman',
            'tl_message' => 'Barya iginawad sa {{p_to_id}}-verify ng card sa pamamagitan Kaibigan',
            'zh_message' => '通过好友获得{{p_to_id}}卡验证币',
            'kn_message'=> 'ಸ್ನೇಹದ ಮೂಲಕ {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು ನಾಣ್ಯಗಳು', 
        ));

        $this->db->where('transaction_messages_id', 65);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Bonus cash awarded for {{p_to_id}} card verification',
            'hi_message' => '{{p_to_id}} कार्ड सत्यापन के लिए बोनस नकद प्रदान किया गया',
            'guj_message' => '{{p_to_id}} કાર્ડ ચકાસણી માટે બોનસ રોકડ',
            'fr_message' => 'Prime en espèces attribuée pour la vérification de la carte {{p_to_id}}',
            'ben_message' => '{{p_to_id}} কার্ড যাচাইয়ের জন্য বোনাস নগদ প্রদান করা হয়',
            'pun_message' => "{{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਬੋਨਸ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'โบนัสเงินสดที่ได้รับสำหรับ {{p_to_id}} การยืนยันบัตร',
            'ru_message' => 'Бонус наличные присуждаются за {{p_to_id}} проверки карты',
            'id_message' => 'Bonus uang tunai diberikan untuk verifikasi kartu {{p_to_id}}',
            'tl_message' => 'Bonus cash na ibinigay para sa {{p_to_id}}-verify ng card',
            'zh_message' => '授予 {{p_to_id}} 卡验证现金奖励',
            'kn_message'=> 'ಬೋನಸ್ ನಗದು {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
        ));

        $this->db->where('transaction_messages_id', 66);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Real Cash awarded for {{p_to_id}} card verification',
            'hi_message' => '{{p_to_id}} कार्ड सत्यापन के लिए वास्तविक नकद प्रदान किया गया',
            'guj_message' => '{{p_to_id}} કાર્ડ ચકાસણી માટે રીઅલ કેશ આપવામાં આવ્યું',
            'fr_message' => 'Real Cash décerné pour la vérification de la carte {{p_to_id}}',
            'ben_message' => '{{p_to_id}} কার্ড যাচাইয়ের জন্য রিয়েল নগদ প্রদান করা',
            'pun_message' => "{{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਅਸਲ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'รางวัลเงินสดจริงสำหรับเช็คการ์ด {{p_to_id}} ใบ',
            'ru_message' => 'Real Cash присуждается за {{p_to_id}} проверки карты',
            'id_message' => 'Nyata Kas diberikan untuk verifikasi kartu {{p_to_id}}',
            'tl_message' => 'Real Cash ibinigay para sa {{p_to_id}}-verify ng card',
            'zh_message' => '真实现金奖励{{p_to_id}}-卡验证',
            'kn_message'=> 'ರಿಯಲ್ ನಗದು {{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
        ));

        $this->db->where('transaction_messages_id', 67);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Coins awarded for {{p_to_id}} card verification',
            'hi_message' => '{{p_to_id}} कार्ड सत्यापन के लिए सम्मानित किया गया सिक्के',
            'guj_message' => '{{p_to_id}} કાર્ડ ચકાસણી માટે સિક્કા એનાયત કરાયા',
            'fr_message' => 'Pièces attribuées pour la vérification de la carte {{p_to_id}}',
            'ben_message' => '{{p_to_id}} কার্ড যাচাইয়ের জন্য পুরষ্কার দেওয়া কয়েন',
            'pun_message' => "{{p_to_id}} ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਸਿੱਕੇ ਦਿੱਤੇ ਗਏ",
            'tam_message' => '',
            'th_message' => 'เหรียญจะได้รับสำหรับการตรวจสอบบัตร {{p_to_id}}',
            'ru_message' => 'Монеты, присуждаемые за {{p_to_id}} проверки карты',
            'id_message' => 'Koin diberikan untuk verifikasi kartu {{p_to_id}}',
            'tl_message' => 'Barya ibinigay para sa {{p_to_id}}-verify ng card',
            'zh_message' => '授予{{p_to_id}}卡片验证币',
            'kn_message'=> '{{p_to_id}} ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು ನಾಣ್ಯಗಳು', 
        ));


        
    }

    public function down() {
        $this->db->where('notification_type', 44);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Your pancard has been rejected. Reason: {{pan_rejected_reason}}',
            'en_message' => 'Your pancard has been rejected. Reason: {{pan_rejected_reason}}',
            'hi_message' => 'आपका पैन कार्ड अस्वीकार कर दिया गया है. {{pan_rejected_reason}}',
            'guj_message' => 'તમારું પાન કાર્ડ નકારવામાં આવી છે. {{pan​_rejected_reason}}',
            'fr_message' => 'Votre pancard a été rejeté. Motif: {{pan_rejected_reason}}',
            'ben_message' => 'আপনার প্যানকার্ড প্রত্যাখ্যান করা হয়েছে। কারণ: {{pan_rejected_reason}}',
            'pun_message' => 'ਤੁਹਾਡਾ ਪੈਨਕਾਰਡ ਖਾਰਜ ਕਰ ਦਿੱਤਾ ਗਿਆ ਹੈ. ਕਾਰਨ: {{pan_rejected_reason}}',
            'tam_message' => 'உங்கள் pancard நிராகரிக்கப்பட்டுள்ளது. காரணம்: {{pan_rejected_reason}}',
            'th_message' => 'pancard ของคุณได้รับการปฏิเสธ เหตุผล: {{pan_rejected_reason}}',
            'kn_message' => 'ನಿಮ್ಮ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ತಿರಸ್ಕರಿಸಲಾಗಿದೆ. ಕಾರಣ: {{pan_rejected_reason}}',
            'kn_subject' => '',
            'ru_message' => 'Ваш pancard отклонено. Причина: {{pan_rejected_reason}}',
            'ru_subject' => '',
            'id_message' => 'Pancard Anda telah ditolak. Alasan: {{pan_rejected_reason}} pan_rejected_reason',
            'id_subject' => '',
            'tl_message' => 'Ang iyong pancard ay tinanggihan. Dahilan: {{pan_rejected_reason}}',
            'tl_subject' => '',
            'zh_message' => '您潘卡已被拒绝。原因：{{pan_rejected_reason}}',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 35);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Congratulations ! {{name}} referred by you, has verified pancard on the site. You have earned {{amount}} Bonus.',
            'en_message' => 'Congratulations ! {{name}} referred by you, has verified pancard on the site. You have earned {{amount}} Bonus.',
            'hi_message' => 'बधाई हो ! आपको हमारी साइट पर अपने मित्र {{name}} का पैन कार्ड के जांच पूर्ण के लिए B{{amount}} का बोनस मिला है।',
            'guj_message' => 'અભિનંદન! તમે ₹ {{amount}} તેમના મિત્ર એક સંપૂર્ણ તપાસ માટે {{name}} પાન અમારી સાઇટ પર કાર્ડની એક બોનસ કમાવ્યા છે.',
            'fr_message' => 'Toutes nos félicitations ! {{name}} référé par vous, a vérifié pancard sur le site. Vous avez gagné {{amount}} Bonus.',
            'ben_message' => 'অভিনন্দন! {{name}} আপনার দ্বারা উল্লেখ করা হয়েছে, সাইটে প্যানকার্ড যাচাই করেছে। তুমি অর্জন করেছ {{amount}} বোনাস.',
            'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਦੱਸੇ ਗਏ {{name}} ਨੇ ਸਾਈਟ 'ਤੇ ਪੈਨਕਾਰਡ ਦੀ ਤਸਦੀਕ ਕੀਤੀ ਹੈ. ਤੁਸੀਂ {{amount}} ਬੋਨਸ ਪ੍ਰਾਪਤ ਕੀਤਾ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'ขอแสดงความยินดี! {{name}} ชื่อเรียกโดยคุณได้ตรวจสอบ pancard บนเว็บไซต์ คุณได้รับ {{amount}} จำนวนโบนัส',
            'kn_message' => 'ಅಭಿನಂದನೆಗಳು! {{name}} ನೀವು ಮೂಲಕ ಉಲ್ಲೇಖಿಸಲಾಗುತ್ತದೆ ಸೈಟ್ನಲ್ಲಿ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ಬೋನಸ್ ಮಾಡಿದ್ದಾರೆ.',
            'kn_subject' => '',
            'ru_message' => 'Поздравляем! {{name}} называется вами, проверил pancard на сайте. Вы заработали {{amount}} бонус.',
            'ru_subject' => '',
            'id_message' => 'Selamat! {{name}} disebut oleh Anda, telah diverifikasi Pancard di situs. Anda telah mendapatkan {{amount}} Bonus.',
            'id_subject' => '',
            'tl_message' => 'Congratulations! {{Name}} na tinutukoy sa pamamagitan ng sa iyo, ay napatunayan pancard sa site. Natamo mo na {{amount}} Bonus.',
            'tl_subject' => '',
            'zh_message' => '恭喜！ {{name}}由你提到，已经验证的网站上pancard。你赢得了{{amount}}奖金。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 59);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Pancard verification bonus cash {{amount}} credited to your account.',
            'en_message' => 'Pancard verification bonus cash {{amount}} credited to your account.',
            'hi_message' => 'बधाई हो ! आपको पैन कार्ड के जांच पूर्ण के लिएB {{amount}} का बोनस मिला है।',
            'guj_message' => 'અભિનંદન! તમે પઐન​ એક સંપૂર્ણ તપાસ માટે ₹ {{amount}} એક બોનસ કમાવ્યા છે.',
            'fr_message' => 'Argent de bonus de vérification de pancarte {{amount}} crédité sur votre compte.',
            'ben_message' => 'প্যানকার্ড যাচাই বোনাস নগদ আপনার অ্যাকাউন্টে {{amount}} জমা হয়েছে।',
            'pun_message' => "ਪੈਨਕਾਰਡ ਵੈਰੀਫਿਕੇਸ਼ਨ ਬੋਨਸ ਨਕਦ {{amount}} your ਤੁਹਾਡੇ ਖਾਤੇ ਵਿੱਚ ਜਮ੍ਹਾਂ.",
            'tam_message' => '',
            'th_message' => 'โบนัสเงินสดตรวจสอบ Pancard {{amount}} โอนไปยังบัญชีของคุณ',
            'kn_message' => 'ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ಬೋನಸ್ ನಗದು {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
            'kn_subject' => '',
            'ru_message' => 'Pancard проверки бонус наличных {{amount}} на Ваш счет.',
            'ru_subject' => '',
            'id_message' => 'Pancard tunai verifikasi bonus {{amount}} dikreditkan ke akun Anda.',
            'id_subject' => '',
            'tl_message' => 'Pancard bonus verification cash {{amount}}-credit sa iyong account.',
            'tl_subject' => '',
            'zh_message' => 'Pancard验证奖金现金{{amount}}存入您的帐户。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 60);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Pancard verification real cash ₹{{amount}} credited to your account.',
            'en_message' => 'Pancard verification real cash ₹{{amount}} credited to your account.',
            'hi_message' => 'बधाई हो ! आपको पैन कार्ड के जांच पूर्ण के लिए ₹ {{amount}} की राशी मिली है।',
            'guj_message' => 'અભિનંદન! તમે પઐન​ એક સંપૂર્ણ તપાસ માટે ₹ {{amount}} જથ્થો છે.',
            'fr_message' => 'Vérification de Pancard en argent réel ₹ {{amount}} crédité sur votre compte.',
            'ben_message' => 'প্যানকার্ড যাচাইয়ের আসল নগদ ₹ আপনার অ্যাকাউন্টে {{amount}} জমা হয়েছে।',
            'pun_message' => "ਪੈਨਕਾਰਡ ਦੀ ਤਸਦੀਕ ਅਸਲ ਨਕਦ {{amount}}. ਤੁਹਾਡੇ ਖਾਤੇ ਵਿੱਚ ਜਮ੍ਹਾਂ.",
            'tam_message' => '',
            'th_message' => 'ตรวจสอบ Pancard เงินสดจริง₹ {{amount}} โอนไปยังบัญชีของคุณ',
            'kn_message' => 'ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನಿಜವಾದ ನಗದು ₹ {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
            'kn_subject' => '',
            'ru_message' => 'Pancard проверка реальных денег ₹ {{amount}} на Ваш счет.',
            'ru_subject' => '',
            'id_message' => 'Pancard verifikasi uang nyata ₹ {{amount}} dikreditkan ke akun Anda.',
            'id_subject' => '',
            'tl_message' => 'Pancard pag-verify real cash ₹ {{amount}}-credit sa iyong account.',
            'tl_subject' => '',
            'zh_message' => 'Pancard验证真正的现金₹{{amount}}存入您的帐户。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 61);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Pancard verification coins ₹{{amount}} credited to your account.',
            'en_message' => 'Pancard verification coins ₹{{amount}} credited to your account.',
            'hi_message' => 'बधाई हो ! आपको पैन कार्ड के जांच पूर्ण के लिए {{amount}} सिक्के मिले है।।',
            'guj_message' => 'અભિનંદન! તમે પઐન​ એક સંપૂર્ણ તપાસ માટે ₹ {{amount}} સિક્કા મળી.',
            'fr_message' => 'Pièces de vérification Pancard {{amount}} créditées sur votre compte.',
            'ben_message' => 'প্যানকার্ড যাচাইয়ের মুদ্রা {{amount}} আপনার অ্যাকাউন্টে জমা',
            'pun_message' => "ਪੈਨਕਾਰਡ ਵੈਰੀਫਿਕੇਸ਼ਨ ਸਿੱਕੇ {{amount}}. ਤੁਹਾਡੇ ਖਾਤੇ ਵਿੱਚ ਜਮ੍ਹਾਂ ਹੋ ਗਏ.",
            'tam_message' => '',
            'th_message' => 'เหรียญตรวจสอบ Pancard {{amount}} โอนไปยังบัญชีของคุณ',
            'kn_message' => 'ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನಾಣ್ಯಗಳು {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
            'kn_subject' => '',
            'ru_message' => 'Pancard проверки монет {{amount}} на Ваш счет.',
            'ru_subject' => '',
            'id_message' => 'koin verifikasi Pancard {{amount}} dikreditkan ke akun Anda.',
            'id_subject' => '',
            'tl_message' => 'Pancard pag-verify barya {{amount}}-credit sa iyong account.',
            'tl_subject' => '',
            'zh_message' => 'Pancard验证硬币{{amount}}存入您的帐户。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 62);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her pancard. You have earned {{amount}} bonus cash.',
            'en_message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her pancard. You have earned {{amount}} bonus cash.',
            'hi_message' => 'बधाई हो ! आपको हमारी साइट पर अपने मित्र {{friend_name}} का पैन कार्ड के जांच पूर्ण के लिए B {{amount}} का बोनस मिला है।',
            'guj_message' => 'અભિનંદન! તમે ₹ {{amount}} તેમના મિત્ર એક સંપૂર્ણ તપાસ માટે {{friend_name}} પાન અમારી સાઇટ પર કાર્ડની એક બોનસ કમાવ્યા છે.',
            'fr_message' => 'Toutes nos félicitations! {{friend_name}} que vous avez référé a vérifié son pancard. Vous avez gagné {{amount}} cash en bonus.',
            'ben_message' => 'অভিনন্দন! {{friend_name}} আপনার দ্বারা উল্লেখ করা তার প্যানকার্ড যাচাই করেছে। তুমি অর্জন করেছ {{amount}} বোনাস নগদ',
            'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਦਰਸਾਏ ਗਏ _ {{friend_name}} his ਨੇ ਉਸ ਦੇ ਪੈਨਕਾਰਡ ਦੀ ਪੁਸ਼ਟੀ ਕੀਤੀ ਹੈ. ਤੁਸੀਂ {{amount}} ਬੋਨਸ ਨਕਦ ਕਮਾਈ ਕੀਤੀ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'ขอแสดงความยินดี! {{friend_name}} เรียกคุณได้ verifed / pancard ของเขาและเธอ คุณได้รับ {{amount}} เงินสดเงินโบนัส',
            'kn_message' => 'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಲಾಗಿದೆ ಮಾಡಿದೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
            'kn_subject' => '',
            'ru_message' => 'Поздравляем! {{friend_name}} называют вами уже verifed его / ее pancard. Вы заработали {{amount}} бонус наличными.',
            'ru_subject' => '',
            'id_message' => 'Selamat! {{friend_name}} disebut oleh Anda telah verifed / nya Pancard nya. Anda telah mendapatkan {{amount}} bonus tunai.',
            'id_subject' => '',
            'tl_message' => 'Congratulations! {{friend_name}} na tinutukoy sa pamamagitan mo ay verifed kanyang / kanyang pancard. Natamo mo na {{amount}} bonus cash.',
            'tl_subject' => '',
            'zh_message' => '恭喜！ {{friend_name}}通过你提到已经verifed他/她的pancard。你赢得了{{amount}}奖金现金。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 63);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her pancard. You have earned {{amount}} real cash.',
            'en_message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her pancard. You have earned {{amount}} real cash.',
            'hi_message' => 'बधाई हो ! आपको हमारी साइट पर अपने मित्र {{friend_name}} का पैन कार्ड के जांच पूर्ण के लिए ₹ {{amount}} की राशी मिली है।',
            'guj_message' => 'અભિનંદન! તમે અમારી સાઇટ પર {સંપૂર્ણ પાન ના ₹ {{amount}} તપાસ માટે} {{friend_name}} તમારા મિત્રોને જથ્થો છે.',
            'fr_message' => 'Toutes nos félicitations! {{friend_name}} que vous avez référé a vérifié son pancard. Vous avez gagné ₹ {{amount}} argent réel.',
            'ben_message' => 'অভিনন্দন! {{friend_name}} আপনার দ্বারা উল্লেখ করা তার প্যানকার্ড যাচাই করেছে। আপনি অর্জন করেছেন ₹ {{amount}} আসল নগদ',
            'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਦਰਸਾਏ ਗਏ _ {{friend_name}} his ਨੇ ਉਸ ਦੇ ਪੈਨਕਾਰਡ ਦੀ ਪੁਸ਼ਟੀ ਕੀਤੀ ਹੈ. ਤੁਸੀਂ cash {{amount}} ਅਸਲ ਨਕਦ ਕਮਾਈ ਕੀਤੀ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'ขอแสดงความยินดี! {{friend_name}} เรียกคุณได้ verifed / pancard ของเขาและเธอ คุณได้รับ₹ {{amount}} จำนวนเงินสดจริง',
            'kn_message' => 'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಲಾಗಿದೆ ಮಾಡಿದೆ. ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
            'kn_subject' => '',
            'ru_message' => 'Поздравляем! {{friend_name}} называют вами уже verifed его / ее pancard. Вы заработали ₹ {{amount}} реальные деньги.',
            'ru_subject' => '',
            'id_message' => 'Selamat! {{friend_name}} disebut oleh Anda telah verifed / nya Pancard nya. Anda telah mendapatkan ₹ {{amount}} uang nyata.',
            'id_subject' => '',
            'tl_message' => 'Congratulations! {{friend_name}} na tinutukoy sa pamamagitan mo ay verifed kanyang / kanyang pancard. Nakaipon ka ₹ {{amount}} real cash.',
            'tl_subject' => '',
            'zh_message' => '恭喜！ {{friend_name}}通过你提到已经verifed他/她的pancard。你赢得了₹{{amount}}真正的现金。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 64);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her pancard. You have earned {{amount}} coins.',
            'en_message' => 'Congratulations! {{friend_name}} referred by you has verifed his/her pancard. You have earned {{amount}} coins.',
            'hi_message' => 'बधाई हो ! आपको हमारी साइट पर अपने मित्र {{friend_name}} का पैन कार्ड के जांच पूर्ण के लिए {{amount}} सिक्के मिले है।',
            'guj_message' => 'અભિનંદન! તમે અમારી સાઇટ પર સિક્કા {પાન} સંપૂર્ણ તપાસ માટે ₹ {{amount}} તમારા મિત્રોને {{friend_name}} પર મળી.',
            'fr_message' => 'Toutes nos félicitations! {{friend_name}} que vous avez référé a vérifié son pancard. Vous avez gagné {{amount}} pièces.',
            'ben_message' => 'অভিনন্দন! {{friend_name}} আপনার দ্বারা উল্লেখ করা তার প্যানকার্ড যাচাই করেছে। আপনি {{amount}} কয়েন অর্জন করেছেন।',
            'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਦਰਸਾਏ ਗਏ _ {{friend_name}} his ਨੇ ਉਸ ਦੇ ਪੈਨਕਾਰਡ ਦੀ ਪੁਸ਼ਟੀ ਕੀਤੀ ਹੈ. ਤੁਸੀਂ {{amount}} ਸਿੱਕੇ ਪ੍ਰਾਪਤ ਕੀਤੇ ਹਨ.",
            'tam_message' => '',
            'th_message' => 'ขอแสดงความยินดี! {{friend_name}} เรียกคุณได้ verifed / pancard ของเขาและเธอ คุณได้รับ {{amount}} จำนวนเหรียญ',
            'kn_message' => 'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಲಾಗಿದೆ ಮಾಡಿದೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
            'kn_subject' => '',
            'ru_message' => 'Поздравляем! {{friend_name}} называют вами уже verifed его / ее pancard. Вы заработали {{amount}} монеты.',
            'ru_subject' => '',
            'id_message' => 'Selamat! {{friend_name}} disebut oleh Anda telah verifed / nya Pancard nya. Anda telah mendapatkan {{amount}} koin.',
            'id_subject' => '',
            'tl_message' => 'Congratulations! {{friend_name}} na tinutukoy sa pamamagitan mo ay verifed kanyang / kanyang pancard. Natamo mo na {{amount}} barya.',
            'tl_subject' => '',
            'zh_message' => '恭喜！ {{friend_name}}通过你提到已经verifed他/她的pancard。你赢得了{{amount}}硬币。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 65);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Hurray! By using the referral code you have earned extra {{amount}} bonus cash for verifying the pancard.',
            'en_message' => 'Hurray! By using the referral code you have earned extra {{amount}} bonus cash for verifying the pancard.',
            'hi_message' => 'बधाई हो ! आपको रेफरल व पैन कार्ड के जांच पूर्ण के लिए B {{amount}} का अधिक बोनस मिला है।',
            'guj_message' => 'અભિનંદન! તમે રેફરલ તપાસો અને પાન વધુ બોનસ ₹ {{amount}} ને પૂર્ણ કરવા મળી.',
            'fr_message' => 'Hourra! En utilisant le code de parrainage, vous avez gagné un bonus supplémentaire de {{amount}} en espèces pour la vérification du pancard.',
            'ben_message' => 'অভিনন্দন! রেফারেল কোড ব্যবহার করে আপনি প্যানকার্ড যাচাই করার জন্য অতিরিক্ত {{amount}} বোনাস নগদ অর্জন করেছেন।',
            'pun_message' => "ਹੁਰੈ! ਰੈਫਰਲ ਕੋਡ ਦੀ ਵਰਤੋਂ ਕਰਕੇ ਤੁਸੀਂ ਪੈਨਕਾਰਡ ਦੀ ਤਸਦੀਕ ਕਰਨ ਲਈ ਵਾਧੂ {{amount}} ਬੋਨਸ ਨਕਦ ਕਮਾਇਆ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'เย่! โดยใช้รหัสอ้างอิงที่คุณได้รับเป็นพิเศษ {{amount}} จำนวนโบนัสเงินสดในการตรวจสอบ pancard',
            'kn_message' => 'ಭಲೆ! ನೀವು ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆಯ ಹೆಚ್ಚುವರಿ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು.',
            'kn_subject' => '',
            'ru_message' => 'Ура! При использовании кода направления вы заработали дополнительные {{amount}} бонус наличными для проверки pancard.',
            'ru_subject' => '',
            'id_message' => 'Hore! Dengan menggunakan kode referral Anda telah mendapatkan uang ekstra {{amount}} bonus untuk memverifikasi Pancard tersebut.',
            'id_subject' => '',
            'tl_message' => 'Hurrah! Sa pamamagitan ng paggamit ng mga referral code natamo mo na dagdag na {{amount}} bonus cash para sa pagpapatunay ng pancard.',
            'tl_subject' => '',
            'zh_message' => '欢呼！通过使用你已经获得额外{{amount}}奖励现金验证pancard的推荐码。',
            'zh_subject' => ''
        ));
        
        $this->db->where('notification_type', 66);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Hurray! By using the referral code you have earned extra ₹{{amount}} real cash for verifying the pancard.',
            'en_message' => 'Hurray! By using the referral code you have earned extra ₹{{amount}} real cash for verifying the pancard.',
            'hi_message' => 'बधाई हो ! आपको रेफरल व पैन कार्ड के जांच पूर्ण के लिए ₹ {{amount}} की अधिक राशी मिली है।',
            'guj_message' => 'અભિનંદન! તમે રેફરલ્સ અને પઐન​ કાર્ડ એક સંપૂર્ણ તપાસ માટે વધુ નાણાં માટે ₹ {{amount}} હોય છે.',
            'fr_message' => 'Hourra! En utilisant le code de parrainage, vous avez gagné un montant réel de ₹ {{amount}} en espèces supplémentaires pour la vérification du pancard.',
            'ben_message' => 'অভিনন্দন! রেফারেল কোড ব্যবহার করে আপনি অতিরিক্ত ₹ অর্জন করেছেন ₹ {{amount}} প্যানকার্ড যাচাই করার জন্য প্রকৃত নগদ।',
            'pun_message' => "ਹੁਰੈ! ਰੈਫਰਲ ਕੋਡ ਦੀ ਵਰਤੋਂ ਕਰਕੇ ਤੁਸੀਂ ਪੈਨਕਾਰਡ ਦੀ ਤਸਦੀਕ ਕਰਨ ਲਈ ਵਾਧੂ cash {{amount}} ਅਸਲ ਨਕਦ ਕਮਾਈ ਕੀਤੀ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'เย่! โดยใช้รหัสอ้างอิงที่คุณได้รับเป็นพิเศษ₹ {{amount}} จำนวนเงินสดจริงในการตรวจสอบ pancard',
            'kn_message' => 'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆಯ ಹೆಚ್ಚುವರಿ ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
            'kn_subject' => '',
            'ru_message' => 'Ура! При использовании кода направления вы заработали дополнительный ₹ {{amount}} реальные деньги для проверки pancard.',
            'ru_subject' => '',
            'id_message' => 'Hore! Dengan menggunakan kode referral Anda telah mendapatkan tambahan ₹ {{amount}} uang nyata untuk memverifikasi Pancard tersebut.',
            'id_subject' => '',
            'tl_message' => 'Hurrah! Sa pamamagitan ng paggamit ng mga referral code natamo mo na dagdag na ₹ {{amount}} real cash para sa pagpapatunay ng pancard.',
            'tl_subject' => '',
            'zh_message' => '欢呼！通过使用推荐码，你已经赢得了额外的₹{{amount}}真正的现金用于验证pancard。',
            'zh_subject' => ''
        ));

        $this->db->where('notification_type', 67);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
            'pun_subject' => '',   
            'ben_subject' => '',
            'fr_subject' => '',
            'guj_subject' => '', 
            'hi_subject' => '',
            'en_subject' => '',
            'th_subject' => '',
            'message' => 'Hurray! By using the referral code you have earned extra {{amount}} coins for verifying the pancard.',
            'en_message' => 'Hurray! By using the referral code you have earned extra {{amount}} coins for verifying the pancard.',
            'hi_message' => 'बधाई हो ! आपको रेफरल व पैन कार्ड के जांच पूर्ण के लिए {{amount}} अधिक सिक्के मिले है।',
            'guj_message' => 'અભિનંદન! તમે રેફરલ્સ અને પાન ₹ {{amount}} વધુ સિક્કા એક સંપૂર્ણ તપાસ માટે મળ્યા હતા.',
            'fr_message' => 'Hourra! En utilisant le code de parrainage, vous avez gagné des {{amount}} pièces supplémentaires pour vérifier le pancard.',
            'ben_message' => 'অভিনন্দন! রেফারেল কোড ব্যবহার করে আপনি প্যানকার্ড যাচাই করার জন্য অতিরিক্ত {{amount}} কয়েন অর্জন করেছেন।',
            'pun_message' => "ਹੁਰੈ! ਰੈਫਰਲ ਕੋਡ ਦੀ ਵਰਤੋਂ ਕਰਕੇ ਤੁਸੀਂ ਪੈਨਕਾਰਡ ਦੀ ਤਸਦੀਕ ਕਰਨ ਲਈ ਵਾਧੂ cash {{amount}} ਅਸਲ ਨਕਦ ਕਮਾਈ ਕੀਤੀ ਹੈ.",
            'tam_message' => '',
            'th_message' => 'เย่! โดยใช้รหัสอ้างอิงที่คุณได้รับเป็นพิเศษ {{amount}} จำนวนเหรียญสำหรับการตรวจสอบ pancard',
            'kn_message' => 'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆಯ ಹೆಚ್ಚುವರಿ {{amount}} ನಾಣ್ಯಗಳು ತಂದುಕೊಟ್ಟಿವೆ.',
            'kn_subject' => '',
            'ru_message' => 'Ура! При использовании кода направления вы заработали дополнительные {{amount}} монеты для проверки pancard.',
            'ru_subject' => '',
            'id_message' => 'Hore! Dengan menggunakan kode referral Anda telah mendapatkan tambahan {{amount}} koin untuk memverifikasi Pancard tersebut.',
            'id_subject' => '',
            'tl_message' => 'Hurrah! Sa pamamagitan ng paggamit ng mga referral code natamo mo na dagdag na {{amount}} barya para sa pagpapatunay ng pancard.',
            'tl_subject' => '',
            'zh_message' => '欢呼！通过使用推荐码，你已经赢得了额外的{{amount}}硬币验证pancard。',
            'zh_subject' => ''
        ));


        $this->db->where('transaction_messages_id', 14);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Referral bonus for pan card verification',
            'hi_message' => 'पैन कार्ड सत्यापन के लिए रेफरल बोनस',
            'guj_message' => 'પાન કાર્ડ ચકાસણી માટે રેફરલ બોનસ',
            'fr_message' => 'Bonus de parrainage pour la vérification de la carte panoramique',
            'ben_message' => 'প্যান কার্ড যাচাইয়ের জন্য রেফারেল বোনাস',
            'pun_message' => "ਪੈਨ ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਰੈਫਰਲ ਬੋਨਸ",
            'tam_message' => '',
            'th_message' => 'โบนัสการอ้างอิงสำหรับการตรวจสอบบัตรกระทะ',
            'ru_message' => 'Направление бонуса для паной проверки карты',
            'id_message' => 'bonus rujukan untuk verifikasi kartu pan',
            'tl_message' => 'Referral bonus para sa pan-verify ng card',
            'zh_message' => '推荐奖金锅卡验证',
            'kn_message' => 'ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ರೆಫರಲ್ ಬೋನಸ್'
        ));

        $this->db->where('transaction_messages_id', 59);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Bonus cash awarded for pan card verification',
            'hi_message' => 'पैन कार्ड सत्यापन के लिए बोनस नकद प्रदान किया गया',
            'guj_message' => 'પાનકાર્ડ ચકાસણી માટે બોનસ રોકડ',
            'fr_message' => 'Prime en espèces attribuée pour la vérification de la carte panoramique',
            'ben_message' => 'প্যান কার্ড যাচাইয়ের জন্য বোনাস নগদ প্রদান করা হয়',
            'pun_message' => "ਪੈਨ ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਬੋਨਸ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'โบนัสเงินสดที่ได้รับรางวัลสำหรับการยืนยันบัตรกระทะ',
            'ru_message' => 'Бонус наличные присуждаются за панорамирование проверки карты',
            'id_message' => 'Bonus uang tunai diberikan untuk verifikasi kartu pan',
            'tl_message' => 'Bonus cash na ibinigay para sa pan-verify ng card',
            'zh_message' => '授予泛卡验证现金红利',
            'kn_message'=>'ಬೋನಸ್ ನಗದು ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
        ));

        $this->db->where('transaction_messages_id', 60);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Real Cash awarded for pan card verification',
            'hi_message' => 'पैन कार्ड सत्यापन के लिए वास्तविक नकद प्रदान किया गया',
            'guj_message' => 'પાન કાર્ડ ચકાસણી માટે રીઅલ કેશ આપવામાં આવ્યું',
            'fr_message' => 'Real Cash décerné pour la vérification de la carte panoramique',
            'ben_message' => 'প্যান কার্ড যাচাইয়ের জন্য রিয়েল নগদ প্রদান করা',
            'pun_message' => "ਪੈਨ ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਅਸਲ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'เงินสดจริงได้รับรางวัลสำหรับการตรวจสอบบัตรกระทะ',
            'ru_message' => 'Real Cash присуждается за панорамирование проверки карты',
            'id_message' => 'Nyata Kas diberikan untuk verifikasi kartu pan',
            'tl_message' => 'Real Cash ibinigay para sa pan-verify ng card',
            'zh_message' => '真正的现金奖励泛卡验证',
            'kn_message'=> 'ರಿಯಲ್ ನಗದು ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
        ));

        $this->db->where('transaction_messages_id', 61);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Coins awarded for pan card verification',
            'hi_message' => 'पैन कार्ड सत्यापन के लिए सम्मानित किया गया सिक्के',
            'guj_message' => 'પાન કાર્ડ ચકાસણી માટે સિક્કા એનાયત કરાયા',
            'fr_message' => 'Pièces attribuées pour la vérification de la carte panoramique',
            'ben_message' => 'প্যান কার্ড যাচাইয়ের জন্য পুরষ্কার দেওয়া কয়েন',
            'pun_message' => "ਪੈਨ ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਸਿੱਕੇ ਦਿੱਤੇ ਗਏ",
            'tam_message' => '',
            'th_message' => 'เหรียญที่ได้รับรางวัลสำหรับการตรวจสอบบัตรกระทะ',
            'ru_message' => 'Монеты, присуждаемые за панорамирование проверки карты',
            'id_message' => 'Koin diberikan untuk verifikasi kartu pan',
            'tl_message' => 'Barya ibinigay para sa pan-verify ng card',
            'zh_message' => '授予泛卡验证钱币',
            'kn_message'=> 'ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು ನಾಣ್ಯಗಳು', 
        ));

        $this->db->where('transaction_messages_id', 62);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Bonus cash awarded on pan card verification by Friend',
            'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर बोनस नकद प्रदान किया गया',
            'guj_message' => 'મિત્ર દ્વારા પાન કાર્ડ ચકાસણી પર બોનસ રોકડ આપવામાં આવ્યું',
            'fr_message' => 'Argent bonus attribué lors de la vérification de la carte panoramique par un ami',
            'ben_message' => 'বন্ধুর দ্বারা প্যান কার্ড যাচাইকরণে বোনাস নগদ প্রদান করা',
            'pun_message' => "ਮਿੱਤਰ ਦੁਆਰਾ ਪੈਨ ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਕਰਨ 'ਤੇ ਬੋਨਸ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'โบนัสเงินสดที่ได้รับรางวัลในการตรวจสอบบัตรกระทะโดยเพื่อน',
            'ru_message' => 'Бонус наличные предоставляются на панорамирование проверки карты по другу',
            'id_message' => 'Bonus uang tunai diberikan pada verifikasi kartu pan oleh Teman',
            'tl_message' => 'Bonus cash iginawad sa pan-verify ng card sa pamamagitan Kaibigan',
            'zh_message' => '现金红利由朋友授予了锅卡验证',
            'kn_message'=> 'ಬೋನಸ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು', 
        ));

        $this->db->where('transaction_messages_id', 63);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Real cash awarded on pan card verification by Friend',
            'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर वास्तविक नकद राशि प्रदान की गई',
            'guj_message' => 'મિત્ર દ્વારા પાન કાર્ડ ચકાસણી પર વાસ્તવિક રોકડ આપવામાં આવે છે',
            'fr_message' => 'Argent réel attribué lors de la vérification de la carte panoramique par un ami',
            'ben_message' => 'বন্ধুর দ্বারা প্যান কার্ড যাচাইকরণে রিয়েল নগদ পুরষ্কার',
            'pun_message' => "ਮਿੱਤਰ ਦੁਆਰਾ ਪੈਨ ਕਾਰਡ ਵੈਰੀਫਿਕੇਸ਼ਨ 'ਤੇ ਅਸਲ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'เงินสดจริงที่ได้รับรางวัลในการตรวจสอบบัตรกระทะโดยเพื่อน',
            'ru_message' => 'Реальные денежные присуждаются на панорамирование проверки карты на друзьях',
            'id_message' => 'uang nyata diberikan pada verifikasi kartu pan oleh Teman',
            'tl_message' => 'Real cash iginawad sa pan-verify ng card sa pamamagitan Kaibigan',
            'zh_message' => '真正的现金奖励潘卡验证通过朋友',
            'kn_message'=> 'ರಿಯಲ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು', 
        ));

        $this->db->where('transaction_messages_id', 64);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Coins awarded on pan card verification by Friend',
            'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
            'guj_message' => 'મિત્ર દ્વારા પાન કાર્ડ ચકાસણી પર સિક્કા આપવામાં આવ્યા',
            'fr_message' => 'Pièces attribuées lors de la vérification de la carte panoramique par un ami',
            'ben_message' => 'প্যান কার্ড যাচাইয়ের জন্য বন্ধুর দ্বারা পুরষ্কার দেওয়া কয়েন',
            'pun_message' => "ਮਿੱਤਰ ਦੁਆਰਾ ਪੈਨ ਕਾਰਡ ਵੈਰੀਫਿਕੇਸ਼ਨ 'ਤੇ ਦਿੱਤੇ ਗਏ ਸਿੱਕੇ",
            'tam_message' => '',
            'th_message' => 'เหรียญที่ได้รับรางวัลในการตรวจสอบบัตรกระทะโดยเพื่อน',
            'ru_message' => 'Монеты награжденных на сковороде проверке карты на Другу',
            'id_message' => 'Koin diberikan verifikasi kartu pan oleh Teman',
            'tl_message' => 'Barya iginawad sa pan-verify ng card sa pamamagitan Kaibigan',
            'zh_message' => '通过朋友获得泛卡验证钱币',
            'kn_message'=> 'ಸ್ನೇಹದ ಮೂಲಕ ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು ನಾಣ್ಯಗಳು', 
        ));

        $this->db->where('transaction_messages_id', 65);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Bonus cash awarded for pan card verification',
            'hi_message' => 'पैन कार्ड सत्यापन के लिए बोनस नकद प्रदान किया गया',
            'guj_message' => 'પાનકાર્ડ ચકાસણી માટે બોનસ રોકડ',
            'fr_message' => 'Prime en espèces attribuée pour la vérification de la carte panoramique',
            'ben_message' => 'প্যান কার্ড যাচাইয়ের জন্য বোনাস নগদ প্রদান করা হয়',
            'pun_message' => "ਪੈਨ ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਬੋਨਸ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'โบนัสเงินสดที่ได้รับรางวัลสำหรับการยืนยันบัตรกระทะ',
            'ru_message' => 'Бонус наличные присуждаются за панорамирование проверки карты',
            'id_message' => 'Bonus uang tunai diberikan untuk verifikasi kartu pan',
            'tl_message' => 'Bonus cash na ibinigay para sa pan-verify ng card',
            'zh_message' => '授予泛卡验证现金红利',
            'kn_message'=> 'ಬೋನಸ್ ನಗದು ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
        ));

        $this->db->where('transaction_messages_id', 66);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Real Cash awarded for pan card verification',
            'hi_message' => 'पैन कार्ड सत्यापन के लिए वास्तविक नकद प्रदान किया गया',
            'guj_message' => 'પાન કાર્ડ ચકાસણી માટે રીઅલ કેશ આપવામાં આવ્યું',
            'fr_message' => 'Real Cash décerné pour la vérification de la carte panoramique',
            'ben_message' => 'প্যান কার্ড যাচাইয়ের জন্য রিয়েল নগদ প্রদান করা',
            'pun_message' => "ਪੈਨ ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਅਸਲ ਨਕਦ ਦਿੱਤਾ ਗਿਆ",
            'tam_message' => '',
            'th_message' => 'เงินสดจริงได้รับรางวัลสำหรับการตรวจสอบบัตรกระทะ',
            'ru_message' => 'Real Cash присуждается за панорамирование проверки карты',
            'id_message' => 'Nyata Kas diberikan untuk verifikasi kartu pan',
            'tl_message' => 'Real Cash ibinigay para sa pan-verify ng card',
            'zh_message' => '真正的现金奖励泛卡验证',
            'kn_message'=> 'ರಿಯಲ್ ನಗದು ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
        ));

        $this->db->where('transaction_messages_id', 67);
        $this->db->update(TRANSACTION_MESSAGES, array(
            'en_message' => 'Coins awarded for pan card verification',
            'hi_message' => 'पैन कार्ड सत्यापन के लिए सम्मानित किया गया सिक्के',
            'guj_message' => 'પાન કાર્ડ ચકાસણી માટે સિક્કા એનાયત કરાયા',
            'fr_message' => 'Pièces attribuées pour la vérification de la carte panoramique',
            'ben_message' => 'প্যান কার্ড যাচাইয়ের জন্য পুরষ্কার দেওয়া কয়েন',
            'pun_message' => "ਪੈਨ ਕਾਰਡ ਦੀ ਤਸਦੀਕ ਲਈ ਸਿੱਕੇ ਦਿੱਤੇ ਗਏ",
            'tam_message' => '',
            'th_message' => 'เหรียญที่ได้รับรางวัลสำหรับการตรวจสอบบัตรกระทะ',
            'ru_message' => 'Монеты, присуждаемые за панорамирование проверки карты',
            'id_message' => 'Koin diberikan untuk verifikasi kartu pan',
            'tl_message' => 'Barya ibinigay para sa pan-verify ng card',
            'zh_message' => '授予泛卡验证钱币',
            'kn_message'=> 'ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು ನಾಣ್ಯಗಳು', 
        ));

    }
}