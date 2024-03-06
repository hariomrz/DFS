<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Coin_expiry extends CI_Migration {

	public function up() {
	
        $field_one = array(
            'total_coins'       => array(
                'type'          => 'INT',
                'constraint'    => '11',
                'null'          => FALSE,
                'default'       => 0,
                'after'         =>'used_bonus',
            ),
        );


        if(!$this->db->field_exists('total_coins', USER_BONUS_CASH)){
            $this->dbforge->add_column(USER_BONUS_CASH,$field_one);
        }

        $field_two = array(
            'used_coins'        => array(
                'type'          => 'INT',
                'constraint'    => '11',
                'null'          => FALSE,
                'default'       => 0,
                'after'         =>'total_coins',
            ),
        );

        if(!$this->db->field_exists('used_coins', USER_BONUS_CASH)){
            $this->dbforge->add_column(USER_BONUS_CASH,$field_two);
        }

        $field_three = array(
            'is_coin_exp'       => array(
                'type'          => 'TINYINT',
                'constraint'    => 1,
                'default'       => 1,
                'null'          => FALSE,
                'comment'       => '1 - Not Expired, 2 - Expired',
                'after'         =>'is_expired',
            ),
        );

        if(!$this->db->field_exists('is_coin_exp', USER_BONUS_CASH)){
            $this->dbforge->add_column(USER_BONUS_CASH,$field_three);
        }

        $notification_description = array(
            array(
                "notification_type" =>589,
                // "tam_subject"=>"",
                "en_subject"    =>"Coins Expired! {{sad_emoji}}",
                "hi_subject"    =>"सिक्के समाप्त! {{sad_emoji}}",
                "guj_subject"   =>"સિક્કા સમાપ્ત! {{sad_emoji}}",
                "fr_subject"    =>"Pièces expirées ! {{sad_emoji}}",
                "ben_subject"   =>"মুদ্রার মেয়াদ শেষ! {{sad_emoji}}",
                "pun_subject"   =>"ਸਿੱਕਿਆਂ ਦੀ ਮਿਆਦ ਪੁੱਗ ਗਈ! {{sad_emoji}}",
                "th_subject"    =>"เหรียญหมดอายุ! {{sad_emoji}}",
                "kn_subject"    =>"ನಾಣ್ಯಗಳ ಅವಧಿ ಮುಗಿದಿದೆ! {{sad_emoji}}",
                "ru_subject"    =>"Срок действия монет истек! {{sad_emoji}}",
                "id_subject"    =>"Koin Kedaluwarsa! {{sad_emoji}}",
                "tl_subject"    =>"Nag-expire na ang mga barya! {{sad_emoji}}",
                "zh_subject"    =>"硬币已过期！ {{sad_emoji}}",
                "message"           =>"{{coins}} coins expired today. Coin balance is {{coin_icon}}{{coin_balance}}.",
                "en_message"        =>"{{coins}} coins expired today. Coin balance is {{coin_icon}}{{coin_balance}}.",
                "hi_message"        =>"{{coins}} सिक्के आज समाप्त हो गए। कॉइन बैलेंस है {{coin_icon}}{{coin_balance}}",
                "guj_message"       =>"{{coins}} સિક્કા આજે સમાપ્ત થઈ ગયા. સિક્કાનું સંતુલન છે {{coin_icon}}{{coin_balance}}",
                "fr_message"        =>"{{coins}} pièces ont expiré aujourd'hui. Le solde de la pièce est {{coin_icon}}{{coin_balance}}",
                "ben_message"       =>"{{coins}} মুদ্রার মেয়াদ আজ শেষ হয়েছে৷ কয়েন ব্যালেন্স হল {{coin_icon}}{{coin_balance}}",
                "pun_message"       =>"{{coins}} ਸਿੱਕਿਆਂ ਦੀ ਮਿਆਦ ਅੱਜ ਸਮਾਪਤ ਹੋ ਗਈ ਹੈ। ਸਿੱਕਾ ਸੰਤੁਲਨ ਹੈ {{coin_icon}}{{coin_balance}}",
                "tam_message"       =>"{{coins}} நாணயங்கள் இன்று காலாவதியானது. நாணயம் இருப்பு {{coin_icon}}{{coin_balance}}",
                "th_message"        =>"เหรียญ {{coins}} หมดอายุวันนี้ Balance Coin คือ {{coin_icon}}{{coin_balance}}",
                "kn_message"        =>"{{coins}} ನಾಣ್ಯಗಳು ಇಂದು ಅವಧಿ ಮುಗಿದಿದೆ. ನಾಣ್ಯ ಸಮತೋಲನವು {{coin_icon}}{{coin_balance}}",
                "ru_message"        =>"{{coins}} Монеты истекли сегодня. Баланс монет {{coin_icon}}{{coin_balance}}",
                "id_message"        =>"{{coins}} koin kedaluwarsa hari ini. Saldo koin adalah {{coin_icon}}{{coin_balance}}",
                "tl_message"        =>"{{coins}} Mga barya Nag-expire na ngayon. Balanse ng barya ay {{coin_icon}}{{coin_balance}}",
                "zh_message"        =>"{{coins}} 今天已过期。硬币平衡是{{coin_icon}}{{coin_balance}}",
               // "es_message" => "Torneo {{name}} unió con éxito."
            ),
            array(
                "notification_type" =>590,
                // "tam_subject"=>"",
                "en_subject"    =>"Coins Expiring Soon!",
                "hi_subject"    =>"सिक्के जल्द ही समाप्त हो रहे हैं!",
                "guj_subject"   =>"સિક્કા ટૂંક સમયમાં સમાપ્ત થાય છે!",
                "fr_subject"    =>"Pièces expirant bientôt !",
                "ben_subject"   =>"কয়েন শীঘ্রই মেয়াদ শেষ!",
                "pun_subject"   =>"ਸਿੱਕੇ ਦੀ ਮਿਆਦ ਜਲਦੀ ਹੀ ਖਤਮ ਹੋ ਰਹੀ ਹੈ!",
                "th_subject"    =>"เหรียญใกล้หมดอายุ!",
                "kn_subject"    =>"ನಾಣ್ಯಗಳು ಶೀಘ್ರದಲ್ಲೇ ಮುಕ್ತಾಯಗೊಳ್ಳಲಿವೆ!",
                "ru_subject"    =>"Срок действия монет скоро истекает!",
                "id_subject"    =>"Koin Segera Kedaluwarsa!",
                "tl_subject"    =>"Malapit nang mag-expire ang mga barya!",
                "zh_subject"    =>"硬币即将到期！",
                "message"           =>"Your {{coins}} coins are expiring tomorrow. {{worried_emoji}} Use them now and gain benefits. {{coin_icon}}",
                "en_message"        =>"Your {{coins}} coins are expiring tomorrow. {{worried_emoji}} Use them now and gain benefits. {{coin_icon}}",
                "hi_message"        =>"आपके {{coins}} सिक्के कल समाप्त हो रहे हैं। {{worried_emoji}} अभी उनका उपयोग करें और लाभ प्राप्त करें। {{coin_icon}}",
                "guj_message"       =>"તમારા {{coins}} સિક્કા આવતીકાલે સમાપ્ત થઈ રહ્યા છે. {{worried_emoji}} હમણાં જ તેનો ઉપયોગ કરો અને લાભ મેળવો. {{coin_icon}}",
                "fr_message"        =>"Vos {{coins}} coins expirent demain. {{worried_emoji}} Utilisez-les maintenant et obtenez des avantages. {{coin_icon}}",
                "ben_message"       =>"আপনার {{coins}} কয়েনের মেয়াদ আগামীকাল শেষ হচ্ছে৷ {{worried_emoji}} এগুলি এখনই ব্যবহার করুন এবং সুবিধাগুলি পান৷ {{coin_icon}}",
                "pun_message"       =>"ਤੁਹਾਡੇ {{coins}} ਸਿੱਕਿਆਂ ਦੀ ਮਿਆਦ ਕੱਲ੍ਹ ਸਮਾਪਤ ਹੋ ਰਹੀ ਹੈ। {{worried_emoji}} ਉਹਨਾਂ ਨੂੰ ਹੁਣੇ ਵਰਤੋ ਅਤੇ ਲਾਭ ਪ੍ਰਾਪਤ ਕਰੋ। {{ਸਿੱਕਾ_ਆਈਕਨ}}",
                "tam_message"       =>"உங்கள் {{coins}} நாணயங்கள் நாளை காலாவதியாகின்றன. {{worried_emoji}} இப்போது அவற்றைப் பயன்படுத்தி நன்மைகளைப் பெறுங்கள். {{coin_icon}}",
                "th_message"        =>"เหรียญ {{coins}} ของคุณจะหมดอายุในวันพรุ่งนี้ {{worried_emoji}} ใช้ตอนนี้และรับผลประโยชน์ {{coin_icon}}",
                "kn_message"        =>"ನಿಮ್ಮ {{ನಾಣ್ಯಗಳ}} ನಾಣ್ಯಗಳು ನಾಳೆ ಮುಕ್ತಾಯಗೊಳ್ಳಲಿವೆ. {{worried_emoji}} ಈಗ ಅವುಗಳನ್ನು ಬಳಸಿ ಮತ್ತು ಪ್ರಯೋಜನಗಳನ್ನು ಪಡೆಯಿರಿ. {{coin_icon}}",
                "ru_message"        =>"Завтра истекает срок действия ваших монет ({{Coins}}). {{worried_emoji}} Используйте их сейчас и получите преимущества. {{coin_icon}}",
                "id_message"        =>"Koin {{coins}} Anda akan kedaluwarsa besok. {{worried_emoji}} Gunakan sekarang dan dapatkan keuntungannya. {{coin_icon}}",
                "tl_message"        =>"Ang iyong {{coins}} coin ay mag-e-expire bukas. {{worried_emoji}} Gamitin ang mga ito ngayon at makakuha ng mga benepisyo. {{coin_icon}}",
                "zh_message"        =>"您的 {{coins}} 代币将于明天到期。 {{worried_emoji}} 现在使用它们并获得好处。 {{coin_icon}}",
               // "es_message" => "Torneo {{name}} unió con éxito."
            ),
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION, $notification_description);

        $transaction_messages = array(
            'source' => 475,
            'en_message'        =>'Coin expired',
            "hi_message"        =>'सिक्के समाप्त हो गए',
            "guj_message"       =>'સિક્કો સમાપ્ત થઈ ગયો',
            "fr_message"        =>'COIN EXPIRÉ',
            "ben_message"       =>'মুদ্রা মেয়াদ শেষ',
            "pun_message"       =>'ਸਿੱਕਾ ਖਤਮ ਹੋ ਗਿਆ',
            "tam_message"       =>'நாணயம் காலாவதியானது',
            "th_message"        =>'เหรียญหมดอายุแล้ว',
            "kn_message"        =>'ನಾಣ್ಯ ಅವಧಿ ಮುಗಿದಿದೆ',
            "ru_message"        =>'Монета истекла',
            "id_message"        =>'koin kedaluwarsa.',
            "tl_message"        =>'Nag-expire ang barya',
            "zh_message"        =>'硬币过期',
            
        );
    $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);

    }

	public function down() {
        
        $this->db->where_in('notification_type', array(589,590));
        $this->db->delete(NOTIFICATION_DESCRIPTION);
	}

}