<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Edit_redemtion_noty extends CI_Migration {

    public function up() {
        //You have recieved {{amount}} bonus for redeem coins
        $update_data = array(
            "message" => 'You have redeemed {{coins}} coins for {{amount}} {{event}}. ',
            "en_message"=>'You have redeemed {{coins}} coins for {{amount}} {{event}}.', 
            "hi_message"=>'आप के लिए {{coins}} सिक्के भुना चुके हैं {{amount}} {{event}}।',
            "guj_message"=>'તમે {{coins}} સિક્કા રિડીમ કરી છે {{amount}} {{event}}.',
            "fr_message"=>'Vous avez racheté {{coins}} pièces de monnaie pièces de monnaie pour 
            {{amount}} {{event}}.',
            "ben_message"=>'আপনি এর জন্য {{coins}} কয়েন মুক্ত করেছ {{amount}} {{event}}।',
            "tam_message"=> 'আপনি এর {{coins}} কয়েন মুক্ত করেছ {{amount}} {{event}}.',
            "pun_message"=>'ਤੁਸੀਂ {{coins}} ਦੇ ਸਿੱਕਿਆਂ ਨੂੰ {{amount}} {{event}} e ਲਈ ਛੁਡਾ ਲਿਆ ਹੈ.',
            "th_message"=>'คุณได้แลก {{coins}} เหรียญสำหรับ {{amount}} {{event}}',
        );
        $this->db->where_in('notification_type',array(139,140));
        $this->db->update(NOTIFICATION_DESCRIPTION,$update_data);
        //gift voucher
        $update_data = array(
            "message" => 'You have redeemed {{coins}} coins for {{reward_text}}. Admin will contact you shortly on the registered Mobile No. for further details.',
            "en_message"=>'You have redeemed {{coins}} coins for {{reward_text}}. Admin will contact you shortly on the registered Mobile No. for further details.', 
            "hi_message"=>'आप के लिए {{coins}} सिक्के भुना चुके हैं {{reward_text}}। व्यवस्थापक अधिक जानकारी के लिए पंजीकृत मोबाइल नंबर पर शीघ्र ही आपसे संपर्क करेंगे।',
            "guj_message"=>'તમે {{coins}} સિક્કા રિડીમ કરી છે {{reward_text}}. સંચાલન વધુ વિગતો માટે નોંધાવેલા મોબાઇલ નં પર ટૂંક સમયમાં તમારો સંપર્ક કરશે.',
            "fr_message"=>'Vous avez racheté {{coins}} pièces de monnaie pièces de monnaie pour {{reward_text}}. Admin vous contactera sous peu sur le mobile enregistré n ° pour plus de détails.',
            "tam_message"=> 'আপনি এর {{coins}} কয়েন মুক্ত করেছ {{reward_text}}. மேலதிக விபரங்களுக்கு பதிவுசெய்த மொபைல் எண்ணில் நிர்வாகி விரைவில் உங்களைத் தொடர்புகொள்வார்',
            "ben_message"=>'আপনি এর জন্য {{coins}} কয়েন মুক্ত করেছ {{reward_text}}। এডমিন আরও বিস্তারিত জানার জন্য নিবন্ধীকৃত মোবাইল নং উপর শীঘ্রই আপনার সাথে যোগাযোগ করবে।',
            "pun_message"=>'ਤੁਸੀਂ {{coins}} ਦੇ ਸਿੱਕਿਆਂ ਨੂੰ {{reward_text}} e ਲਈ ਛੁਡਾ ਲਿਆ ਹੈ e ਲਈ ਛੁਡਾ ਲਿਆ ਹੈ. ਹੋਰ ਵੇਰਵਿਆਂ ਲਈ ਐਡਮਿਨ ਤੁਹਾਡੇ ਨਾਲ ਜਲਦੀ ਰਜਿਸਟਰਡ ਮੋਬਾਈਲ ਨੰਬਰ ਤੇ ਸੰਪਰਕ ਕਰੇਗਾ',
            "th_message"=>'คุณได้แลก {{coins}} เหรียญสำหรับ {{reward_text}} ผู้ดูแลระบบจะติดต่อคุณโดยเร็วตามหมายเลขโทรศัพท์มือถือที่ลงทะเบียนสำหรับรายละเอียดเพิ่มเติมs',
        );
        $this->db->where('notification_type',141);
        $this->db->update(NOTIFICATION_DESCRIPTION,$update_data);
    }
    
    public function down() {
        	//down script 
        $this->db->where_in('notification_type',[139,140,141])->delete(NOTIFICATION_DESCRIPTION);
    }
}
