<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_user_bonus_cash extends CI_Migration {

	public function up() {
		$fields = array(
            'bonus_cash_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'total_bonus' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => FALSE,
                'default' => 0.00
            ),
            'used_bonus' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => FALSE,
                'default' => 0.00
            ),  
            'bonus_date' => array(
                'type' => 'DATE',
                'null' => FALSE
            ),
            'is_expired' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => FALSE,
                'comment' => '1 - Not Expired, 2 - Expired'
            ),
            'modified_date' => array(
                'type' => 'DATETIME',
                'null' => FALSE 
            )
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('bonus_cash_id',TRUE);
        $this->dbforge->create_table(USER_BONUS_CASH ,FALSE,$attributes);
      
        $sql = "ALTER TABLE ".$this->db->dbprefix(USER_BONUS_CASH)." ADD UNIQUE( `user_id`, `bonus_date`)";
        $this->db->query($sql);

        $notification_description = array(
            array(
                'notification_type' => 426,
                'message' => "Your's {{amount}} Bonus Cash is expiring in next 7 days.",
                'en_message' => "Your's {{amount}} Bonus Cash is expiring in next 7 days.",
                'hi_message' => 'अगले 7 दिनों में आपका {{amount}} बोनस कैश समाप्त हो रहा है',
                'guj_message' => '{{amount}} બોનસ કેશ આગામી 7 દિવસમાં સમાપ્ત થાય છે.',
                'fr_message' => 'Votre Bonus Cash {{amount}} expire dans les 7 prochains jours.',
                'ben_message' => 'আপনার {{amount}} বোনাস নগদ আগামী 7 দিনের মধ্যে শেষ হচ্ছে।.',
                'pun_message' => 'ਤੁਹਾਡਾ {{amount}} ਬੋਨਸ ਨਕਦ ਅਗਲੇ 7 ਦਿਨਾਂ ਵਿੱਚ ਖਤਮ ਹੋ ਰਿਹਾ ਹੈ.',
                'th_message' => 'เงินสดโบนัส {{amount}} ของคุณกำลังจะหมดอายุใน 7 วันข้างหน้า.',
                'tam_message' => 'உங்கள் {{amount}} போனஸ் ரொக்கம் அடுத்த 7 நாட்களில் காலாவதியாகிறது.'
            ),
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION, $notification_description);

    }

	public function down() {
        $this->dbforge->drop_table(USER_BONUS_CASH);
        
        $this->db->where_in('notification_type', array(426));
        $this->db->delete(NOTIFICATION_DESCRIPTION);
	}

}