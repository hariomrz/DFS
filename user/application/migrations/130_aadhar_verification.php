<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_aadhar_verification extends CI_Migration {

	public function up()
	{
		$fields = array(
	        'aadhar_status' => array(
                'type' => 'TINYINT',
				'constraint' => '1',
                'default' => 0
	        ),
	        'aadhar_rejected_reason' => array(
                'type' => 'VARCHAR',
				'constraint' => '255',
                'null' => TRUE
	        )
		);

	  	$this->dbforge->add_column(USER, $fields);

	  	$fields = array(
	        'aadhar_id' => array(
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => TRUE,
                'null' => FALSE
	        ),
	        'user_id' => array(
	          'type' => 'INT',
	          'constraint' => 11,
	          'null' => FALSE,
	        ),
	        'name' => array(
	          'type' => 'VARCHAR',
	          'constraint' => 255,
	          'null' => FALSE
	        ),
	        'aadhar_number' => array(
	          'type' => 'VARCHAR',
	          'constraint' => 15,
	          'null' => FALSE
	        ),
	        'front_image' => array(
	          'type' => 'VARCHAR',
	          'constraint' => 100,
	          'null' => FALSE
	        ),
	        'back_image' => array(
	          'type' => 'VARCHAR',
	          'constraint' => 100,
	          'null' => FALSE
	        ),
	        'status' => array(
                'type' => 'TINYINT',
				'constraint' => '1',
                'default' => 0
	        ),
	        'added_date' => array(
	          'type' => 'DATETIME',
	          'null' => TRUE
	        ),
	        'modified_date' => array(
	          'type' => 'DATETIME',
	          'null' => TRUE
	        )
        );

      	$attributes = array('ENGINE' => 'InnoDB');
      	$this->dbforge->add_field($fields);
      	$this->dbforge->add_key('aadhar_id',TRUE);
      	$this->dbforge->create_table(USER_AADHAR ,FALSE,$attributes);

      	//add unique key
      	$query = "ALTER TABLE vi_user_aadhar ADD UNIQUE (user_id,aadhar_number)";
      	$this->db->query($query);

      	//Add Notification type
      	$query = "INSERT INTO `vi_notification_description` (`notification_description_id`, `notification_type`, `pun_subject`, `ben_subject`, `fr_subject`, `guj_subject`, `hi_subject`, `en_subject`, `th_subject`, `message`, `en_message`, `hi_message`, `guj_message`, `fr_message`, `ben_message`, `pun_message`, `tam_message`, `th_message`, `ru_message`, `ru_subject`, `id_message`, `id_subject`, `tl_message`, `tl_subject`, `zh_message`, `zh_subject`, `kn_message`, `kn_subject`, `es_message`, `es_subject`) VALUES (NULL, '532', '', '', '', '', '', '', '', 'Your {{a_to_id}} card has been rejected. Reason: {{aadhar_rejected_reason}}', 'Your {{a_to_id}} card has been rejected. Reason: {{aadhar_rejected_reason}}', 'आपका {{a_to_id}} कार्ड अस्वीकार कर दिया गया है। कारण: {{आधार_अस्वीकार_कारण}}', '\r\nતમારું {{a_to_id}} કાર્ડ નકારવામાં આવ્યું છે. કારણ: {{aadhar_rejected_reason}}', 'Aadhaar carte {{a_to_id}} a été rejetée. Raison : {{aadhar_rejected_reason}}', 'আপনার {{a_to_id}} কার্ড বাতিল করা হয়েছে। কারণ: {{aadhar_rejected_reason}}', 'ਤੁਹਾਡਾ {{a_to_id}} ਕਾਰਡ ਰੱਦ ਕਰ ਦਿੱਤਾ ਗਿਆ ਹੈ। ਕਾਰਨ: {{aadhar_rejected_reason}}', 'உங்கள் {{a_to_id}} கார்டு நிராகரிக்கப்பட்டது. காரணம்: {{aadhar_rejected_reason}}', '{{a_to_id}} card ของคุณได้รับการปฏิเสธ เหตุผล: {{aadhar_rejected_reason}}', 'Ваш {{a_to_id}} card отклонено. Причина: {{aadhar_rejected_reason}}', '', '{{a_to_id}} card Anda telah ditolak. Alasan: {{aadhar_rejected_reason}}', '', 'Ang iyong {{a_to_id}} card ay tinanggihan. Dahilan: {{aadhar_rejected_reason}}', '', '您潘卡已被拒绝。原因：{{aadhar_rejected_reason}}', '', 'ನಿಮ್ಮ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ತಿರಸ್ಕರಿಸಲಾಗಿದೆ. ಕಾರಣ: {{aadhar_rejected_reason}}', '', 'Tu Aadhaar ha sido rechazado. Razón: {{aadhar_rejected_reason}}', 'aadhar')";
      	$this->db->query($query);

      	//Add email template
      	$query = "INSERT INTO `vi_email_template` (`email_template_id`, `template_name`, `subject`, `template_path`, `notification_type`, `status`, `type`, `email_body`, `message_body`, `display_label`, `date_added`, `modified_date`) VALUES (NULL, 'admin-aadhar-card-reject', 'Aadhaar Card Reject', 'admin-aadhar-card-reject', '532', '1', '0', NULL, NULL, 'Aadhaar Card Reject', NULL, NULL)";
      	$this->db->query($query);
	}

	public function down()
	{

	}

}
