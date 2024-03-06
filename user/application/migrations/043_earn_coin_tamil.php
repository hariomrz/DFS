<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Earn_coin_tamil extends CI_Migration {

	public function up() {

$field = array(
            'tam' => array(
                'type' => 'JSON',
                'null' => TRUE,
                'default' => NULL,
                'after'=>'pun'
            ),
			'th' => array(
                'type' => 'JSON',
                'null' => TRUE,
				'default' => NULL,
				'after'=>'tam'
              ),
		);
		$this->dbforge->add_column(EARN_COINS, $field);

		$earn_coins =array (
            
            array (
              'module_key' => 'refer-a-friend',
              'th' => 
              json_encode(array (
                'label' => 'แนะนำเพื่อน',
                'description' => 'ได้รับ 100 เหรียญสำหรับเพื่อนทุกคน \'s ลงทะเบียน',
                'button_text' => 'อ้างถึง',
              )),
            ),
           
            array (
              'module_key' => 'daily_streak_bonus',
              'th' => 
              json_encode(array (
                'label' => 'โบนัส DAILY การเช็คอิน',
                'description' => 'ได้รับเหรียญทุกวันโดยการเข้าสู่ระบบ',
                'button_text' => 'เรียนรู้เพิ่มเติม',
              )) ,
            ),
            
            array (
              'module_key' => 'prediction',
              'th' => 
              json_encode(array (
                'label' => 'PLAY ทำนาย',
                'description' => 'ทำนายและได้รับเหรียญ',
                'button_text' => 'ทำนาย',
              )) ,
            ),
            
            array (
              'module_key' => 'promotions',
              'th' => 
              json_encode(array (
                'label' => 'โปรโมชั่น',
                'description' => 'วิ่งออกมาจากเหรียญ? ดูวิดีโอและเติมเงินกระเป๋าสตางค์เหรียญของคุณ',
                'button_text' => 'ดู',
              )),
            ),
           
            array (
              'module_key' => 'feedback',
              'th' => 
              json_encode( array (
                'label' => 'ผลตอบรับ',
                'description' => 'ข้อเสนอแนะของแท้จะได้รับเหรียญหลังจากได้รับอนุมัติผู้ดูแลระบบ',
                'button_text' => 'เขียนถึงเรา',
              )),
            ),
            array (
                'module_key' => 'refer-a-friend',
                'tam' => 
                json_encode(array (
                  'label' => 'நண்பரைப் பரிந்துரைக்கவும்',
                  'description' => 'ஒவ்வொரு நண்பரின் அடையாளம் வரை 100 நாணயங்கள் சம்பாதிக்க',
                  'button_text' => 'பார்க்கவும்',
                )),
              ),
             
              array (
                'module_key' => 'daily_streak_bonus',
                'tam' => 
                json_encode(array (
                  'label' => 'தினசரி சோதனை போனஸ்',
                  'description' => 'மரம்வெட்டுதல் மூலம் தினசரி நாணயங்கள் சம்பாதிக்க',
                  'button_text' => 'மேலும் அறிக',
                )) ,
              ),
              
              array (
                'module_key' => 'prediction',
                'tam' => 
                json_encode(array (
                  'label' => 'விளையாடு கணிப்பை',
                  'description' => 'கணிக்கவும் மற்றும் நாணயங்கள் சம்பாதிக்க',
                  'button_text' => 'கணிக்கவும்',
                )) ,
              ),
              
              array (
                'module_key' => 'promotions',
                'tam' => 
                json_encode(array (
                  'label' => 'விளம்பரங்கள்',
                  'description' => 'ரான் நாணயங்கள் வெளியே வீடியோவைக் உங்கள் நாணயம் பணப்பையை நிரப்பித் தர?',
                  'button_text' => 'கடிகாரம்',
                )),
              ),
             
              array (
                'module_key' => 'feedback',
                'tam' => 
                json_encode( array (
                  'label' => 'கருத்து',
                  'description' => 'உண்மையான கருத்துக்களை நிர்வாக ஒப்புதலுக்கு பின்னர் நாணயங்கள் கிடைக்கும்',
                  'button_text' => 'எங்களை எழுது',
                )),
              ),
        );

        $this->db->update_batch(EARN_COINS,$earn_coins,'module_key');
		
}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'th_message');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'th_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'th_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'th_desc');
		
		$this->dbforge->drop_column(CMS_PAGES, 'th_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'th_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'th_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'th_page_content');
		$this->dbforge->drop_column(COMMON_CONTENT, 'th_header');
		$this->dbforge->drop_column(COMMON_CONTENT, 'th_body');
		$this->dbforge->drop_column(EARN_COINS, 'th');
	}

}
