<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Inapp_purchase_notification extends CI_Migration {

    public function up() {


        $value = array(
        array(
        "notification_type" =>425,
        "message" => '{{amount}} coins credited to you account.',
        "en_message"=>'{{amount}} coins credited to you account.', 
        "hi_message"=>'{{amount}} आपके खाते में जमा किए गए सिक्के।',
        "tam_message"=> '{{amount}} நாணயங்கள் உங்கள் கணக்கில் வரவு வைக்கப்பட்டுள்ளன.',
        "ben_message"=>'আপনার অ্যাকাউন্টে জমা দেওয়া কয়েন {{amount}}।',
        "pun_message"=>'{{amount}} ਸਿੱਕੇ ਤੁਹਾਡੇ ਖਾਤੇ ਵਿੱਚ ਕ੍ਰੈਡਿਟ.',
        "fr_message"=>'{{amount}} pièces créditées sur votre compte.',
        "guj_message"=>'{{amount}} સિક્કા તમારા ખાતામાં જમા થયા.',
        "th_message"=>'{{amount}} เหรียญเข้าบัญชีของคุณ',
        ),
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$value);

        //code to create a record for email template for in app purchase of coins currently not required
        //   $inapp_purchase_notification = 
        //         array(
        //             array( 
        //                 'notification_type' => 425,
        //                 'template_name'=> 'inapp_purchase',
        //                 'subject' => 'Coins purchase',
        //                 'template_path' => 'inapp_purchase',
        //                 'status' => 1,
        //                 'type' => 0,
        //                 'display_label' => 'In app purchase',
        //                 'date_added' => '2020-11-09 03:15:41'
        //             ),
        //         );
        // $this->db->insert_batch(EMAIL_TEMPLATE,$inapp_purchase_notification);

        $transaction_messages = array(
            array(
                'source' => 325,
                'en_message' => 'In app coins purchase',
                'hi_message' => 'एप्लिकेशन सिक्कों की खरीद में',
                "tam_message"=> 'பயன்பாட்டு நாணயங்கள் வாங்குவதில்',
                "ben_message"=>'অ্যাপ্লিকেশন কয়েন ক্রয়',
                "pun_message"=>'ਐਪ ਸਿੱਕੇ ਖਰੀਦਣ ਵਿੱਚ',
                "fr_message"=>'Achat de pièces dans l\'application',
                "guj_message"=>'એપ્લિકેશન સિક્કા ખરીદી',
                "th_message"=>'ในการซื้อเหรียญแอป',
            ),
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
             
    }
    
    public function down() {
        	//down script 
        // $this->dbforge->drop_table('user_affiliate_records');
        $this->db->where('notification_type',425)->delete(EMAIL_TEMPLATE);
        $this->db->where_in('source',[325])->delete(TRANSACTION_MESSAGES);
        $this->db->where_in('notification_type',[425])->delete(NOTIFICATION_DESCRIPTION);
    }
}
