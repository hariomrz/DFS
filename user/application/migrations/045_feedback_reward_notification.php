<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Feedback_reward_notification extends CI_Migration {

    public function up() {


        $value = array(
        array(
        "notification_type" =>151,
        "message" => 'Appreciated your feedback! You have received {{amount}} coins for this support.',
        "en_message"=>'Appreciated your feedback! You have received {{amount}} coins for this support.', 
        "hi_message"=>'आपकी प्रतिक्रिया की सराहना की! आप इस समर्थन के लिए {{amount}} सिक्के प्राप्त हुआ है',
        "tam_message"=> 'உங்கள் கருத்தைப் பாராட்டினேன்! இந்த ஆதரவுக்காக நீங்கள் {{amount}} நாணயங்களைப் பெற்றுள்ளீர்கள்',
        "ben_message"=>'আপনার মতামত প্রশংসা! আপনি এই সমর্থনের জন্য {{amount}} কয়েন পেয়েছি',
        "pun_message"=>'ਤੁਹਾਡੇ ਫੀਡਬੈਕ ਦੀ ਪ੍ਰਸ਼ੰਸਾ ਕੀਤੀ! ਤੁਹਾਨੂੰ ਇਸ ਸਹਾਇਤਾ ਲਈ {{amount}} ਸਿੱਕੇ ਪ੍ਰਾਪਤ ਹੋਏ ਹਨ',
        "fr_message"=>'Apprécié vos commentaires! Vous avez reçu {{amount}} pièces pour ce soutien',
        "guj_message"=>'તમારી પ્રતિક્રિયા પ્રશંસા! તમે આ આધાર માટે {{amount}} સિક્કા પ્રાપ્ત થઈ છે',
        "th_message"=>'ชื่นชมความคิดเห็นของคุณ! คุณได้รับ {{amount}} เหรียญสำหรับการสนับสนุนนี้',
        ),
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$value); 

        //You have recieved {{amount}} bonus for redeem coins


        $update_data = array(
            "message" => 'You have redeemed {{coins}} coins for {{amount}} {{event}}',
            "en_message"=>'You have redeemed {{coins}} coins for {{amount}} {{event}}', 
            "hi_message"=>'आप के लिए {{coins}} सिक्के भुना चुके हैं {{amount}} {{event}}',
            "tam_message"=> 'আপনি এর {{coins}} কয়েন মুক্ত করেছ {{amount}} {{event}}',
            "ben_message"=>'আপনি এর জন্য {{coins}} কয়েন মুক্ত করেছ {{amount}} {{event}}',
            "pun_message"=>'ਤੁਸੀਂ {{coins}} ਦੇ ਸਿੱਕਿਆਂ ਨੂੰ {{amount}} {{event}} e ਲਈ ਛੁਡਾ ਲਿਆ ਹੈ',
            "fr_message"=>'Vous avez racheté {{coins}} pièces de monnaie pièces de monnaie pour {{amount}} {{event}}',
            "guj_message"=>'તમે {{coins}} સિક્કા રિડીમ કરી છે {{amount}} {{event}}',
            "th_message"=>'คุณได้แลก {{coins}} เหรียญสำหรับ {{amount}} {{event}}',
        );
        $this->db->where_in('notification_type',array(139,140));
        $this->db->update(NOTIFICATION_DESCRIPTION,$update_data);
        //gift voucher
        $update_data = array(
            "message" => 'You have redeemed {{coins}} coins for {{reward_text}}',
            "en_message"=>'You have redeemed {{coins}} coins for {{reward_text}}', 
            "hi_message"=>'आप के लिए {{coins}} सिक्के भुना चुके हैं {{reward_text}}',
            "tam_message"=> 'আপনি এর {{coins}} কয়েন মুক্ত করেছ {{reward_text}}',
            "ben_message"=>'আপনি এর জন্য {{coins}} কয়েন মুক্ত করেছ {{reward_text}}',
            "pun_message"=>'ਤੁਸੀਂ {{coins}} ਦੇ ਸਿੱਕਿਆਂ ਨੂੰ {{reward_text}} e ਲਈ ਛੁਡਾ ਲਿਆ ਹੈ',
            "fr_message"=>'Vous avez racheté {{coins}} pièces de monnaie pièces de monnaie pour {{reward_text}}',
            "guj_message"=>'તમે {{coins}} સિક્કા રિડીમ કરી છે {{reward_text}}',
            "th_message"=>'คุณได้แลก {{coins}} เหรียญสำหรับ {{reward_text}}',
        );
        $this->db->where('notification_type',141);
        $this->db->update(NOTIFICATION_DESCRIPTION,$update_data);
        

        $value = array(
             array(
                "template_name" => "redeem-coin-reward",
                "subject" => "Redeemed Coins Reward unlocked",
                "template_path" => "redeem-coin-reward",
                "status" => 1,
                "notification_type" =>139,
                "date_added" =>format_date()),
            array(
                "template_name" => "redeem-coin-reward",
                "subject" => "Redeemed Coins Reward unlocked",
                "template_path" => "redeem-coin-reward",
                "status" => 1,
                "notification_type" =>140,
                "date_added" =>format_date()),
            array(
                "template_name" => "redeem-coin-reward",
                "subject" => "Redeemed Coins Reward unlocked",
                "template_path" => "redeem-coin-reward",
                "status" => 1,
                "notification_type" =>141,
                "date_added" =>format_date())
        );
        
        $this->db->insert_batch(EMAIL_TEMPLATE,$value); 
        
        
    }
    
    public function down() {
        	//down script 
        $this->db->where_in('notification_type',[151,139,140,141])->delete(NOTIFICATION_DESCRIPTION);
        $this->db->where_in('notification_type',array(139,140,141))->delete(EMAIL_TEMPLATE);
    }
}
