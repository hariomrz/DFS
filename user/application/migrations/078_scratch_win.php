<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Scratch_win extends CI_Migration {

	public function up()
	{
		$fields = array(
			'scratch_card_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'prize_type' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => NULL,
				'comment' => '0=>Bonus,1=>Real,2=>Coin'
			),
			'amount' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE
			),
			'result_text' => array(
			  'type' => 'VARCHAR',
			  'constraint' => 255,
			  'null' => FALSE
			),
			'status' => array(
			  'type' => 'ENUM("0","1")',
			  'default' => '0',
			  'null' => FALSE
			),
			'created_date' => array(
			  'type' => 'DATETIME',
			  'null' => TRUE,
			  'default' => NULL
			),
			'updated_date' => array(
			  'type' => 'DATETIME',
			  'null' => TRUE,
			  'default' => NULL
			)
		);
		$attributes = array('ENGINE'=>'InnoDB');
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('scratch_card_id', TRUE);
		$this->dbforge->create_table(SCRATCH_WIN,FALSE,$attributes);


		$fields = array(
			'scratch_win_claimed_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'user_id' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => FALSE
			),
			'contest_id' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => FALSE
			),
			'claimed_date' => array(
			   'type' => 'DATETIME',
				'null' => TRUE,
				'default' => NULL
			),
			'scratch_details' => array(
			  'type' => 'json',
			  'null' => TRUE,
			  'default' => NULL
			),
			'status' => array(
				'type' => 'ENUM("0","1")',
				'default' => '0',
				'null' => FALSE
			  )
		);

		$attributes = array('ENGINE'=>'InnoDB');
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('scratch_win_claimed_id', TRUE);
		$this->dbforge->create_table(SCRATCH_WIN_CLAIMED,FALSE,$attributes);


		$scratch_default_data = array(
			array(
				'prize_type'=>'1',
				'amount'=>'0',
				'result_text'=>'Better luck next time',
				'status'=>'1',
				'created_date'=>date('Y-m-d H:i:s'),
			),
			array(
				'prize_type'=>'0',
				'amount'=>'15',
				'result_text'=>'Congratulations you won B 15',
				'status'=>'1',
				'created_date'=>date('Y-m-d H:i:s'),
			),array(
				'prize_type'=>'2',
				'amount'=>'30',
				'result_text'=>'Congratulations you won C 30',
				'status'=>'1',
				'created_date'=>date('Y-m-d H:i:s'),
			),array(
				'prize_type'=>'1',
				'amount'=>'50',
				'result_text'=>'Congratulations you won Rs. 50',
				'status'=>'1',
				'created_date'=>date('Y-m-d H:i:s'),
			),array(
				'prize_type'=>'1',
				'amount'=>'22',
				'result_text'=>'Congratulations you won Rs. 22',
				'status'=>'1',
				'created_date'=>date('Y-m-d H:i:s'),
			),array(
				'prize_type'=>'1',
				'amount'=>'6',
				'result_text'=>'Congratulations you won Rs. 6',
				'status'=>'1',
				'created_date'=>date('Y-m-d H:i:s'),
			),array(
				'prize_type'=>'2',
				'amount'=>'25',
				'result_text'=>'Congratulations you won C 25',
				'status'=>'1',
				'created_date'=>date('Y-m-d H:i:s'),
			),array(
				'prize_type'=>'2',
				'amount'=>'45',
				'result_text'=>'Congratulations you won C 45',
				'status'=>'1',
				'created_date'=>date('Y-m-d H:i:s'),
			),array(
				'prize_type'=>'0',
				'amount'=>'100',
				'result_text'=>'Congratulations you won B 100',
				'status'=>'1',
				'created_date'=>date('Y-m-d H:i:s'),
			),array(
				'prize_type'=>'0',
				'amount'=>'65',
				'result_text'=>'Congratulations you won B 65',
				'status'=>'1',
				'created_date'=>date('Y-m-d H:i:s'),
			),
		);

		$this->db->insert_batch(SCRATCH_WIN,$scratch_default_data);

		$notifications = array(
			array(
				'notification_type'		=>'431',
				'message'				=>'Congratulations you won {{amount}} in scratch & win',
				'en_message'			=>'Congratulations you won {{amount}} in scratch & win',
				'hi_message'			=>'बधाई है कि आपने स्क्रैच और जीत में {{amount}} जीता',
				'guj_message'			=>'અભિનંદન કે તમે સ્ક્રેચ અને જીતમાં {{amount}} જીત્યાં',
				'fr_message'			=>'Félicitations, vous avez gagné {{amount}} au scratch & win',
				'ben_message'			=>'অভিনন্দন আপনি স্ক্র্যাচ এবং জিতে {{amount}} জিতেছেন',
				'pun_message'			=>'ਮੁਬਾਰਕਾਂ ਤੁਸੀਂ ਸਕ੍ਰੈਚ ਅਤੇ ਜਿੱਤ ਵਿੱਚ {{amount}} ਜਿੱਤੀ',
				'tam_message'			=>'புதிதாக & வென்றதில் {{amount}} ஐ வென்றதற்கு வாழ்த்துக்கள்',
				'th_message'			=>'ขอแสดงความยินดีที่คุณชนะ {{amount}} ในการขูดขีดและชนะ',
				'kn_message'			=>'ಅಭಿನಂದನೆಗಳು ನೀವು {{amount}} ಸ್ಕ್ರ್ಯಾಚ್ ಮತ್ತು ಗೆಲುವಿನಲ್ಲಿ ಗೆದ್ದಿದ್ದೀರಿ',
				'tl_message'			=>'Binabati kita nanalo ka ng {{amount}} sa simula at manalo',
				'ru_message'			=>'Поздравляем, вы выиграли {{amount}} в нулях и победах',
				'id_message'			=>'Selamat, Anda memenangkan {{amount}} awal & menang',
				'zh_message'			=>'恭喜，您赢了{{prize_type}} {{amount}}并赢了'
			),
		);
		$this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notifications);

		$transactions_message = array(
			array(
				'source'				=>'381',
				'en_message'			=>'Earned in scratch & win',
				'hi_message'			=>'स्क्रैच और जीत बोनस',
				'guj_message'			=>'જીત્યું સ્ક્રેચ અને જીતી બોનસ',
				'fr_message'			=>'Bonus à gratter gagné',
				'ben_message'			=>'স্ক্র্যাচ জিতেছে এবং বোনাস জিতেছে',
				'pun_message'			=>'ਸਕ੍ਰੈਚ ਜਿੱਤੀ ਅਤੇ ਬੋਨਸ ਜਿੱਤੀ',
				'tam_message'			=>'கீறல் வென்று போனஸ் வென்றது',
				'th_message'			=>'ชนะเกาและรับโบนัส',
				'kn_message'			=>'ಸ್ಕ್ರ್ಯಾಚ್ ಗೆದ್ದರು ಮತ್ತು ಬೋನಸ್ ಗೆದ್ದರು',
				'ru_message'			=>'Выигранный бонус за скрэтч и выигрыш',
				'tl_message'			=>'Nanalo ng simula at manalo ng bonus',
				'id_message'			=>'Menangkan bonus awal & menang',
				'zh_message'			=>'赢得刮奖并赢得奖金'
			),
		);
		$this->db->insert_batch(TRANSACTION_MESSAGES,$transactions_message);
    }

	public function down()
	{
		$this->dbforge->drop_table(SCRATCH_WIN);
		$this->dbforge->drop_table(SCRATCH_WIN_CLAIMED);
		$this->db->where_in('notification_type', array(431));
		$this->db->delete(NOTIFICATION_DESCRIPTION);
		$this->db->where_in('source', array(381));
		$this->db->delete(TRANSACTION_MESSAGES);
	}

}
