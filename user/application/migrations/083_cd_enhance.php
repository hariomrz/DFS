<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cd_enhance extends CI_Migration{

    public function up(){
        
        // $sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `ru_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
        // $this->db->query($sql);
        //adding a default category on 14th id
        $check = $this->db->query("select * from ".$this->db->dbprefix(CD_EMAIL_CATEGORY)." WHERE category_id = 14 and category_name != 'Promotion for Deposit';")->row_array();
        if($check)
        {
            $pre_categories = array(
                    'category_name'		=> $check['category_name'],
                    'status'			=> $check['status'],
                    'added_date'		=> $check['added_date'],
            );
            $this->db->insert(CD_EMAIL_CATEGORY,$pre_categories);    
        
        $categories = array(
				'category_id'		=> 14,
				'category_name'		=> "Promotion for Deal",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
		);
        $this->db->update(CD_EMAIL_CATEGORY,$categories,['category_id'=>$categories['category_id']]);
        }
        else
        {
            $categories = array(
                    'category_id'		=> 14,
                    'category_name'		=> "Promotion for Deal",
                    'status'			=> 1,
                    'added_date'		=> format_date('today'),
            );
            $this->db->insert(CD_EMAIL_CATEGORY,$categories);
        }

        $check = $this->db->query("select * from ".$this->db->dbprefix(CD_EMAIL_CATEGORY)." WHERE category_id = 15 and category_name != 'Promotion for Promocode';")->row_array();
        if($check)
        {
            $pre_categories = array(
                    'category_name'		=> $check['category_name'],
                    'status'			=> $check['status'],
                    'added_date'		=> $check['added_date'],
            );
            $this->db->insert(CD_EMAIL_CATEGORY,$pre_categories);    
        
        $categories = array(
				'category_id'		=> 15,
				'category_name'		=> "Promotion for Promocode",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
		);
        $this->db->update(CD_EMAIL_CATEGORY,$categories,['category_id'=>$categories['category_id']]);
        }
        else
        {
            $categories = array(
                    'category_id'		=> 15,
                    'category_name'		=> "Promotion for Promocode",
                    'status'			=> 1,
                    'added_date'		=> format_date('today'),
            );
            $this->db->insert(CD_EMAIL_CATEGORY,$categories);
        }
        //Promotion for Promocode

        //addding templated for deal and promocode 
        $deal_template = array(
            array(
                'category_id'=>14,
                'template_name'=>'deal_template',
                'subject'=>'Never RUN OUT of Bonus or Coins',
                'notification_type'=>434,
                'status'=>1,
                'type'=>1,
                'message_body'=>'Add real cash of {{amount}} and get extra benefits of - {{deal_benifit}} ',
                'message_url'=>'',
                'redirect_to'=>7,
                'message_type'=>2,
                'display_label'=>'Deal Promotion',
                'modified_date'=>format_date('today'),
            ),
            // array(
            //     'category_id'=>15,
            //     'template_name'=>'new_promocode_template',
            //     'subject'=>'Apply promocode & get more',
            //     'notification_type'=>435,
            //     'status'=>1,
            //     'type'=>1,
            //     'message_body'=>'Apply promocode {{new_promocode}} and get extra benefits of - {{promocode_benifit}} ',
            //     'message_url'=>'',
            //     'redirect_to'=>7,
            //     'message_type'=>2,
            //     'display_label'=>'Promocode Promotion',
            //     'modified_date'=>format_date('today'),
            // ),
            array(
                'category_id'=>15,
                'template_name'=>'contest_join_promocode',
                'subject'=>'Apply promocode & get more',
                'notification_type'=>435,
                'status'=>1,
                'type'=>1,
                'message_body'=>'Apply {{new_promocode}} & get discount on contest joining  {{promocode_benifit}}{{capping_text}}',
                'message_url'=>'',
                'redirect_to'=>7,
                'message_type'=>2,
                'display_label'=>'Contest Join Promocode',
                'modified_date'=>format_date('today'),
            ),
            array(
                'category_id'=>15,
                'template_name'=>'deposit_promocode',
                'subject'=>'Apply promocode & get more',
                'notification_type'=>435,
                'status'=>1,
                'type'=>1,
                'message_body'=>'Apply {{new_promocode}} & get extra benefits of {{promocode_benifit}}{{capping_text}}',
                'message_url'=>'',
                'redirect_to'=>7,
                'message_type'=>2,
                'display_label'=>'Deposit Promocode',
                'modified_date'=>format_date('today'),
            ),
            array(
                'category_id'=>15,
                'template_name'=>'deposit_range_promocode',
                'subject'=>'Apply promocode & get more',
                'notification_type'=>435,
                'status'=>1,
                'type'=>1,
                'message_body'=>'Apply {{new_promocode}}{{deposit_range}} & get extra benefits of {{promocode_benifit}}{{capping_text}}',
                'message_url'=>'',
                'redirect_to'=>7,
                'message_type'=>2,
                'display_label'=>'Deposit Promocode',
                'modified_date'=>format_date('today'),
            ),
            array(
                'category_id'=>15,
                'template_name'=>'first_deposit_promocode',
                'subject'=>'Apply promocode & get more',
                'notification_type'=>435,
                'status'=>1,
                'type'=>1,
                'message_body'=>'Apply {{new_promocode}} on your first deposit & and get extra benefits of {{promocode_benifit}}{{capping_text}}',
                'message_url'=>'',
                'redirect_to'=>7,
                'message_type'=>2,
                'display_label'=>'Deposit Promocode',
                'modified_date'=>format_date('today'),
            ),
        );
        $this->db->insert_batch(CD_EMAIL_TEMPLATE,$deal_template);
        
        
        //updated affiliate notification as I only added rupyeee sign in notification.
        $notifications = array(
			array(
				'notification_type'		=>'420',
				'message'				=>'User signup through your affiliate program and you got ₹ {{amount}}',
				'en_message'			=>'User signup through your affiliate program and you got ₹ {{amount}}',
				'hi_message'			=>'अपके संबद्ध कार्यक्रम के माध्यम से उपयोगकर्ता साइनअप और आप मिल गया ₹ {{amount}}',
				'guj_message'			=>'તમારા સંલગ્ન કાર્યક્રમ દ્વારા વપરાશકર્તા સાઇનઅપ અને તમે મળી ₹ {{amount}}',
				'fr_message'			=>'inscription de l\'utilisateur grâce à votre programme d\'affiliation et vous avez ₹ {{amount}} quantité',
				'ben_message'			=>'আপনার অধিভুক্ত প্রোগ্রাম মাধ্যমে ব্যবহারকারীর সাইনআপ এবং আপনি পেয়েছেন ₹ {{amount}}',
				'pun_message'			=>'ਆਪਣੇ ਐਫੀਲੀਏਟ ਪ੍ਰੋਗਰਾਮ ਦੁਆਰਾ ਯੂਜ਼ਰ ਸਾਇਨਅਪ ਅਤੇ ਤੁਹਾਨੂੰ ਮਿਲੀ ₹ {{amount}} ਦੀ ਰਕਮ',
				'tam_message'			=>'உங்கள் இணைப்பு திட்டம் மூலம் பயனர் இணைந்ததற்கு நீங்கள் கிடைத்தது ₹ {{amount}}',
                'th_message'			=>'สมัครใช้งานของผู้ใช้ผ่านโปรแกรมพันธมิตรของคุณและคุณได้ ₹ {{amount}}',
                'kn_message'            =>'ನಿಮ್ಮ ಅಂಗ ಪ್ರೋಗ್ರಾಂ ಮೂಲಕ ಬಳಕೆದಾರ ಸೈನ್ ಅಪ್ ಮತ್ತು ನೀವು ಸಿಕ್ಕಿತು ₹ {{amount}}',
				'tl_message'			=>'pag-signup ng user sa pamamagitan ng iyong affiliate program at mayroon ₹ {{amount}}',
				'ru_message'			=>'Регистрация пользователя с помощью вашей партнерской программы, и вы получили ₹ {{amount}}',
				'id_message'			=>'Pengguna pendaftaran melalui program afiliasi Anda dan Anda mendapat ₹ {{amount}}',
				'zh_message'			=>'用户注册通过您的联盟计划和你有 ₹{{amount}}',
            ),
            array(
				'notification_type'		=>'421',
				'message'				=>'User deposit through your affiliate program and you got ₹ {{amount}}  ',
				'en_message'			=>'User deposit through your affiliate program and you got ₹ {{amount}}  ',
				'hi_message'			=>'अपके संबद्ध कार्यक्रम के माध्यम से उपयोगकर्ता जमा और आप मिल गया ₹ {{amount}}',
				'guj_message'			=>'તમારા સંલગ્ન કાર્યક્રમ દ્વારા વપરાશકર્તા થાપણ અને તમે મળી ₹ {{amount}}',
				'fr_message'			=>'Dépôt de l\'utilisateur grâce à votre programme d\'affiliation et vous avez ₹ {{amount}} quantité',
				'ben_message'			=>'আপনার অধিভুক্ত প্রোগ্রাম মাধ্যমে ব্যবহারকারীর আমানত এবং আপনি পেয়েছেন ₹ {{amount}}',
				'pun_message'			=>'ਆਪਣੇ ਐਫੀਲੀਏਟ ਪ੍ਰੋਗਰਾਮ ਯੂਜ਼ਰ ਨੂੰ ਪੇਸ਼ਗੀ ਅਤੇ ਤੁਹਾਨੂੰ ਮਿਲੀ ₹ {{amount}} ਦੀ ਰਕਮ',
				'tam_message'			=>'உங்கள் இணைப்பு திட்டம் மூலம் பயனர் வைப்பு மற்றும் நீங்கள் கிடைத்தது ₹ {{amount}}',
                'th_message'			=>'เงินฝากของผู้ใช้ผ่านโปรแกรมพันธมิตรของคุณและคุณได้ ₹ {{amount}}',
                'kn_message'            =>'ನಿಮ್ಮ ಅಂಗ ಪ್ರೋಗ್ರಾಂ ಮೂಲಕ ಬಳಕೆದಾರ ಠೇವಣಿ ಮತ್ತು ನೀವು ಸಿಕ್ಕಿತು ₹ {{amount}}',
				'tl_message'			=>'User deposit sa pamamagitan ng iyong affiliate program at mayroon ₹ {{amount}}',
				'ru_message'			=>'Депозит пользователя через вашу партнерскую программу, и вы получили ₹ {{amount}}',
				'id_message'			=>'Pengguna deposito melalui program afiliasi Anda dan Anda mendapat ₹ {{amount}}',
				'zh_message'			=>'通过你的联盟计划的用户存款和你有 ₹ {{amount}}',
            ),
            array(
				'notification_type'		=>'60',
				'message'				=>'Pancard verification real cash {{amount}} credited to your account.',
				'en_message'			=>'Pancard verification real cash {{amount}} credited to your account.',
				'hi_message'			=>'बधाई हो ! आपको पैन कार्ड के जांच पूर्ण के लिए {{amount}} की राशी मिली है।',
				'guj_message'			=>'અભિનંદન! તમે પઐન​ એક સંપૂર્ણ તપાસ માટે {{amount}} જથ્થો છે.',
				'fr_message'			=>'Crédit de vérification {{amount}} sur votre compte.',
				'ben_message'			=>'প্যানকার্ড যাচাইয়ের আসল নগদ আপনার অ্যাকাউন্টে {{amount}} জমা হয়েছে।',
				'pun_message'			=>'ਪੈਨਕਾਰਡ ਦੀ ਤਸਦੀਕ ਅਸਲ ਨਕਦ {{amount}}. ਤੁਹਾਡੇ ਖਾਤੇ ਵਿੱਚ ਜਮ੍ਹਾਂ.',
				'tam_message'			=>'சரிபார்ப்பு உண்மையான பண {{amount}} உங்கள் கணக்கில் வரவு.',
                'th_message'			=>'ตรวจสอบ Pancard เงินสดจริง {{amount}} โอนไปยังบัญชีของคุณ',
                'kn_message'            =>'ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನಿಜವಾದ ನಗದು {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
				'tl_message'			=>'Pancard pag-verify real cash {{amount}}-credit sa iyong account.',
				'ru_message'			=>'Pancard проверка реальных денег {{amount}} на Ваш счет.',
				'id_message'			=>'Pancard verifikasi uang nyata {{amount}} dikreditkan ke akun Anda.',
				'zh_message'			=>'验证真正的现金{{amount}}存入您的帐户。',
			),
		);
        $this->db->update_batch(NOTIFICATION_DESCRIPTION,$notifications,'notification_type');
        
        //as it is necessary I am adding just a record here in table
        $new_notifications = array(
			array(
				'notification_type'		=>'434',
				'message'				=>'Add real cash of {{amount}} and get extra benefits - {{deal_benifit}} ',
				'en_message'			=>'',
				'hi_message'			=>'',
				'guj_message'			=>'',
				'fr_message'			=>'',
				'ben_message'			=>'',
				'pun_message'			=>'',
				'tam_message'			=>'',
                'th_message'			=>'',
                'kn_message'            =>'',
				'tl_message'			=>'',
				'ru_message'			=>'',
				'id_message'			=>'',
				'zh_message'			=>'',
            ),
            array(
				'notification_type'		=>'435',
				'message'				=>'Apply promocode {{new_promocode}} and get extra benefits - {{promocode_benifit}} ',
				'en_message'			=>'',
				'hi_message'			=>'',
				'guj_message'			=>'',
				'fr_message'			=>'',
				'ben_message'			=>'',
				'pun_message'			=>'',
				'tam_message'			=>'',
                'th_message'			=>'',
                'kn_message'            =>'',
				'tl_message'			=>'',
				'ru_message'			=>'',
				'id_message'			=>'',
				'zh_message'			=>'',
			),
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$new_notifications);

        //rename template name
        $sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		template_name='promotion-for-fixture'
		WHERE cd_email_template_id = 4";
        $this->db->query($sql);

        //adding columns for scheduler in recommunication table
        $scheduler_fields = array(
			'noti_schedule' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => false,
				'default'=>1,
			),
			'schedule_date' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
				'default'=>NULL,
            ),
            'is_processed' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => false,
				'default'=>0,
            ),
            'custom_data' => array(
                'type' => 'JSON',
                'default' => NULL,
            ),
		);
		
		$this->dbforge->add_column(CD_RECENT_COMMUNICATION, $scheduler_fields);
    }

    public function down()
    {

    }

}
/** this migration is created on 31 may 2021 to add deal promotion category and template as default in db
 */


 ?>