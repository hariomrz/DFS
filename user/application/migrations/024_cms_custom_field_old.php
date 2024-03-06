<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cms_custom_field_old extends CI_Migration{

	public function up(){

		$cms_custom_field = array(
				'custom_data' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
				'after'=>'modified_date'
			),
		); 
		$this->dbforge->add_column(CMS_PAGES,$cms_custom_field);
	

	$category =array(
			'category_id'=>array(
				'type'=>'INT',
				'constraint'=>10,
				'auto_increment'=>TRUE,
			),
			'category_alias'=>array(
				'type'=>'VARCHAR',
				'constraint'=>30,
				'null'=>FALSE,
			),
			'hi_category'=>array(
				'type'=>'VARCHAR',
				'constraint'=>30,
				'null'=>FALSE,
			),
			'en_category'=>array(
				'type'=>'VARCHAR',
				'constraint'=>30,
				'null'=>FALSE,
			),
			'es_category'=>array(
				'type'=>'VARCHAR',
				'constraint'=>30,
				'null'=>FALSE,
			),
			'fr_category'=>array(
				'type'=>'VARCHAR',
				'constraint'=>30,
				'null'=>FALSE,
			),
			'guj_category'=>array(
				'type'=>'VARCHAR',
				'constraint'=>30,
				'null'=>FALSE,
			),
			'ben_category'=>array(
				'type'=>'VARCHAR',
				'constraint'=>30,
				'null'=>FALSE,
			),
			'pun_category'=>array(
				'type'=>'VARCHAR',
				'constraint'=>30,
				'null'=>FALSE,
			),
			'tam_category'=>array(
				'type'=>'VARCHAR',
				'constraint'=>30,
				'null'=>FALSE,
			),
			'status' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default'=>1,
				'comment' => '0=>inactive,1=>Active',
			),
			'added_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE,
			),
		);
		$attributes = array('ENGINE'=>'InnoDB');
		$this->dbforge->add_field($category);
		$this->dbforge->add_key('category_id', TRUE);
		$this->dbforge->create_table(FAQ_CATEGORY,FALSE,$attributes);

$categories = array(
			array(
				'category_id'		=> 1,
				'category_alias'		=> "registration",
				'hi_category'		=> "पंजीकरण",
				'en_category'		=> "Registration",
				'es_category'		=> "Registro",
				'fr_category'		=> "enregistrement",
				'guj_category'		=> "નોંધણી",
				'ben_category'		=> "নিবন্ধন",
				'pun_category'		=> "ਰਜਿਸਟਰੇਸ਼ਨ",
				'tam_category'		=> "பதிவு",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 2,
				'category_alias'		=> "playing_the_game",
				'hi_category'		=> "गेम खेल रहा हूँ",
				'en_category'		=> "Playing the Game",
				'es_category'		=> "Jugando el juego",
				'fr_category'		=> "Jouer le jeu",
				'guj_category'		=> "રમત રમવાની",
				'ben_category'		=> "খেলাটি খেলিতেছি",
				'pun_category'		=> "ਖੇਡ ਖੇਡਣ ਨੂੰ",
				'tam_category'		=> "பிளேயிங் த கேம்",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 3,
				'category_alias'		=> "scores_points",
				'hi_category'		=> "स्कोर और अंक",
				'en_category'		=> "Scores & Points",
				'es_category'		=> "Resultados & Puntos",
				'fr_category'		=> "Partitions et Points",
				'guj_category'		=> "સ્કોર્સ અને પોઇંટ્સ",
				'ben_category'		=> "স্কোর & পয়েন্ট",
				'pun_category'		=> "ਸਕੋਰ & ਬਿੰਦੂ",
				'tam_category'		=> "ச்கொர்ஸ் & amp; புள்ளிகள்",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 4,
				'category_alias'		=> "contests",
				'hi_category'		=> "प्रतियोगिताएं",
				'en_category'		=> "Contests",
				'es_category'		=> "concursos",
				'fr_category'		=> "concours",
				'guj_category'		=> "પ્રતિસ્પર્ધાઓ",
				'ben_category'		=> "প্রতিযোগিতা",
				'pun_category'		=> "ਮੁਕਾਬਲੇ",
				'tam_category'		=> "போட்டிகள்",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 5,
				'category_alias'		=> "account_balance",
				'hi_category'		=> "खाते में शेष",
				'en_category'		=> "Account Balance",
				'es_category'		=> "Saldo de la cuenta",
				'fr_category'		=> "Solde du compte",
				'guj_category'		=> "એકાઉન્ટ બેલેન્સ",
				'ben_category'		=> "হিসাবের পরিমান",
				'pun_category'		=> "ਖਾਤੇ ਦਾ ਬਕਾਇਆ",
				'tam_category'		=> "கணக்கு இருப்பு",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 6,
				'category_alias'		=> "verification",
				'hi_category'		=> "सत्यापन",
				'en_category'		=> "Verification",
				'es_category'		=> "Verificación",
				'fr_category'		=> "Vérification",
				'guj_category'		=> "ચકાસણી",
				'ben_category'		=> "প্রতিপাদন",
				'pun_category'		=> "ਤਸਦੀਕ",
				'tam_category'		=> "சரிபார்ப்பு",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 7,
				'category_alias'		=> "withdrawals",
				'hi_category'		=> "निकासी",
				'en_category'		=> "Withdrawals",
				'es_category'		=> "Retiros",
				'fr_category'		=> "retraits",
				'guj_category'		=> "ઉપાડ",
				'ben_category'		=> "তোলার",
				'pun_category'		=> "ਕਢਵਾਈ",
				'tam_category'		=> "விலகியவர்கள்",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 8,
				'category_alias'		=> "legality",
				'hi_category'		=> "वैधता",
				'en_category'		=> "Legality",
				'es_category'		=> "Legalidad",
				'fr_category'		=> "Légalité",
				'guj_category'		=> "કાયદેસરતા",
				'ben_category'		=> "বৈধতা",
				'pun_category'		=> "ਕਾਨੂੰਨੀ",
				'tam_category'		=> "சட்டப்பூர்வத்தன்மை",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 9,
				'category_alias'		=> "fair_play_violation",
				'hi_category'		=> "फेयर प्ले वॉयलेशन",
				'en_category'		=> "Fair Play Violation",
				'es_category'		=> "Fair Play Violación",
				'fr_category'		=> "Fair-Play Violation",
				'guj_category'		=> "ન્યાયી રમતના ઉલ્લંઘન",
				'ben_category'		=> "ফেয়ার প্লে লঙ্ঘন",
				'pun_category'		=> "ਫੇਅਰ ਪਲੇ ਉਲੰਘਣਾ",
				'tam_category'		=> "ஃபேர் ப்ளே மீறல்",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 10,
				'category_alias'		=> "payments",
				'hi_category'		=> "भुगतान",
				'en_category'		=> "Payments",
				'es_category'		=> "pagos",
				'fr_category'		=> "Paiements",
				'guj_category'		=> "ચુકવણીઓ",
				'ben_category'		=> "পেমেন্টস্",
				'pun_category'		=> "ਭੁਗਤਾਨ",
				'tam_category'		=> "கொடுப்பனவு",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),

		);
		$this->db->insert_batch(FAQ_CATEGORY,$categories);

		$questions =array(
			'question_id'=>array(
				'type'=>'INT',
				'constraint'=>10,
				'auto_increment'=>TRUE,
			),
			'category_id'=>array(
				'type'=>'INT',
				'constraint'=>10,
				'null'=>FALSE,
			),
			'hi_question'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'hi_answer'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'en_question'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'en_answer'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'es_question'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'es_answer'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'fr_question'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'fr_answer'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'guj_question'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'guj_answer'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'ben_question'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'ben_answer'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'pun_question'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'pun_answer'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'tam_question'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'tam_answer'=>array(
				'type'=>'TEXT',
				'default'=>NULL,
			),
			'added_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE,
			),
			'status' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default'=>1,
				'comment' => '0=>inactive,1=>Active',
			)
		);
		$attributes = array('ENGINE'=>'InnoDB');
		$this->dbforge->add_field($questions);
		$this->dbforge->add_key('question_id', TRUE);
		$this->dbforge->create_table(FAQ_QUESTIONS,FALSE,$attributes);

}
	public function down(){
		$this->dbforge->drop_column(CMS_PAGES, 'custom_data');
		$this->dbforge->drop_table(FAQ_CATEGORY);
		$this->dbforge->drop_table(FAQ_QUESTIONS);
	}
}