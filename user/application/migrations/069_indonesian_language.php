<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Indonesian_language extends CI_Migration {

	public function up() {

        $notification_field = array(
			'id_message' => array(
                'type' => 'LONGTEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
			'id_subject' => array(
			'type' => 'LONGTEXT',
			'character_set' => 'utf8 COLLATE utf8_general_ci',
			'null' => FALSE,
			),
		);
		$this->dbforge->add_column(NOTIFICATION_DESCRIPTION, $notification_field);

		$transection_field = array(
			'id_message' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
		);
		$this->dbforge->add_column(TRANSACTION_MESSAGES, $transection_field);
		
		$sportshub_field = array(
			'id_title' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'id_desc' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			
		);
		
		$this->dbforge->add_column(SPORTS_HUB, $sportshub_field);
		
		$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `id_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `id_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `id_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `id_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
		$this->db->query($sql);

			
		$common_content_field = array(
			'id_header'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'id_body'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);
	
		$this->dbforge->add_column(COMMON_CONTENT, $common_content_field);

		$field = array(
			'id' => array(
                'type' => 'JSON',
                'null' => TRUE,
				'default' => NULL,
			  ),
		);
		$this->dbforge->add_column(EARN_COINS, $field);

		$faq_question_fields = array(
			'id_question'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'id_answer'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);

		$this->dbforge->add_column(FAQ_QUESTIONS, $faq_question_fields);
		
		$faq_category_fields = array(
			'id_category'=>array(
				'type' => 'VARCHAR',
				'constraint' => 30,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);
		$this->dbforge->add_column(FAQ_CATEGORY, $faq_category_fields);
		

		//updating columns now

		$sports_hub_arr = array(
			array (
                'id_title' => 'MODE TURNAMEN',
                'id_desc' => 'Musim Pemain Panjang Pro? Mainkan semua musim di sini ',
                'game_key' => 'allow_tournament',
                ), array (
                'id_title' => 'OLAHRAGA FANTASI HARIAN',
                'id_desc' => 'Olahraga fantasi harian jauh lebih menarik daripada olahraga fantasi tradisional',
                'game_key' => 'allow_dfs',
                ), array (
                'id_title' => 'PRAKIRAAN & MENANGKAN KOIN',
                'id_desc' => 'Tidak diperlukan keahlian fantasi. Prediksikan saja hasilnya dan menangkan koin ',
                'game_key' => 'allow_prediction',
                ), array (
                'id_title' => 'Pick’em dengan Kolam Hadiah',
                'id_desc' => 'Game itu sendiri sangat sederhana. Pilih saja pihak yang menang ',
                'game_key' => 'allow_pickem',
                ), array (
                'id_title' => 'MULTI GAME',
                'id_desc' => 'Game fantasi multi game jauh lebih menarik daripada game fantasi tradisional',
                'game_key' => 'allow_multigame',
                ), array (
                'id_title' => 'BUKA PRAKIRAAN',
                'id_desc' => 'Prediksi saja hasilnya dan menangkan koin',
                'game_key' => 'allow_open_predictor',
                ), array (
                'id_title' => 'Gratis untuk bermain',
                'id_desc' => 'Mainkan fantasi setiap hari secara gratis dan menangkan hadiah menarik.',
                'game_key' => 'allow_free2play',
                ), array (
                'id_title' => 'prediktor terbuka tetap',
                'id_desc' => 'Prediksi saja hasilnya dan menangkan hadiahnya',
                'game_key' => 'allow_fixed_open_predictor',
                ), array (
                'id_title' => '',
                'id_desc' => '',
                'game_key' => 'allow_prop_fantasy',
                ),
		);

		$this->db->update_batch(SPORTS_HUB,$sports_hub_arr,'game_key');
		
		$common_content_arr = array(
            array (
                'id_header' => 'Total',
                'id_body' => 'Menang + bonus tunai + setoran',
                'content_key' => 'wallet',
            ),
		);
		$this->db->update_batch(COMMON_CONTENT,$common_content_arr,'content_key');
		  
		$earn_coins =array (
            
			array (
                'module_key' => 'refer-a-friend',
                'id' =>
                json_encode (array (
                'label' => 'Undang teman',
                'description' => 'Dapatkan 100 koin untuk setiap teman yang terdaftar',
                'button_text' => 'Merujuk ke',
                )),
                ),
                
                array (
                'module_key' => 'daily_streak_bonus',
                'id' =>
                json_encode (array (
                'label' => 'Bonus pendaftaran HARIAN',
                'description' => 'Dapatkan koin setiap hari dengan login',
                'button_text' => 'Pelajari lebih lanjut',
                )),
                ),
                
                array (
                'module_key' => 'prediction',
                'id' =>
                json_encode (array (
                'label' => 'PUTAR Prediksi',
                'description' => 'Prediksi dan dapatkan koin',
                'button_text' => 'Prediksi',
                )),
                ),
                
                array (
                'module_key' => 'promotions',
                'id' =>
                json_encode (array (
                'label' => 'Promosi',
                'description' => 'Koin habis? Tonton videonya dan isi ulang dompet koin Anda ',
                'button_text' => 'View',
                )),
                ),
                
                array (
                'module_key' => 'feedback',
                'id' =>
                json_encode (array (
                'label' => 'Review',
                'description' => 'Proposal otentik akan diberikan setelah persetujuan administrator',
                'button_text' => 'Email kami',
                )),
                ),
		  );

		$this->db->update_batch(EARN_COINS,$earn_coins,'module_key');

		$categories = array (
            array (
                'category_alias' => 'registration',
                'id_category' => 'Pendaftaran',
                ), array (
                'category_alias' => 'playing_the_game',
                'id_category' => '"Memainkan game"',
                ), array (
                'category_alias' => 'scores_points',
                'id_category' => 'Rating dan rating',
                ), array (
                'category_alias' => 'contests',
                'id_category' => '"Persaingan"',
                ), array (
                'category_alias' => 'account_balance',
                'id_category' => '"Saldo akun"',
                ), array (
                'category_alias' => 'verification',
                'id_category' => 'Konfirmasi',
                ), array (
                'category_alias' => 'withdrawals',
                'id_category' => 'Hapus',
                ), array (
                'category_alias' => 'legality',
                'id_category' => 'Right',
                ), array (
                'category_alias' => 'fair_play_violation',
                'id_category' => 'Pelanggaran Fair Play',
                ), array (
                'category_alias' => 'payments',
                'id_category' => '"Pembayaran"',
                ),
		);
		$this->db->update_batch(FAQ_CATEGORY,$categories,'category_alias');
		
		$cms_data = array (
            array (
            'page_alias' => 'about',
            'id_meta_keyword' => 'Tentang Kami',
            'id_page_title' => 'Tentang Kami',
            'id_meta_desc' => 'Tentang kami',
            'id_page_content' => 'Tentang Kami',
            ), array (
            'page_alias' => 'how_it_works',
            'id_meta_keyword' => 'Bagaimana cara kerjanya?',
            'id_page_title' => 'Bagaimana cara kerjanya?',
            'id_meta_desc' => 'Bagaimana cara kerjanya?',
            'id_page_content' => 'Bagaimana cara kerjanya?',
            ), array (
            'page_alias' => 'terms_of_use',
            'id_meta_keyword' => 'Ketentuan Layanan',
            'id_page_title' => 'Persyaratan Layanan',
            'id_meta_desc' => 'Persyaratan Layanan',
            'id_page_content' => 'Persyaratan Layanan',
            ), array (
            'page_alias' => 'privacy_policy',
            'id_meta_keyword' => 'Kebijakan Privasi',
            'id_page_title' => 'Kebijakan Privasi',
            'id_meta_desc' => 'Kebijakan Privasi',
            'id_page_content' => 'Kebijakan Privasi',
            ), array (
            'page_alias' => 'faq',
            'id_meta_keyword' => '"FAQ"',
            'id_page_title' => 'Pertanyaan yang sering diajukan',
            'id_meta_desc' => 'Pertanyaan yang sering diajukan',
            'id_page_content' => 'Pertanyaan yang sering diajukan',
            ), array (
            'page_alias' => 'support',
            'id_meta_keyword' => 'Dukungan',
            'id_page_title' => 'Dukungan',
            'id_meta_desc' => 'Dukungan',
            'id_page_content' => 'Dukungan',
            ), array (
            'page_alias' => 'affiliations',
            'id_meta_keyword' => 'Mitra',
            'id_page_title' => 'Mitra',
            'id_meta_desc' => 'Mitra',
            'id_page_content' => 'Mitra',
            ), array (
            'page_alias' => 'rules_and_scoring',
            'id_meta_keyword' => 'Aturan dan penilaian',
            'id_page_title' => 'Aturan dan Nilai',
            'id_meta_desc' => 'Aturan dan Penilaian',
            'id_page_content' => 'Aturan dan Evaluasi',
            ), array (
            'page_alias' => 'career',
            'id_meta_keyword' => 'Karir',
            'id_page_title' => 'Pekerjaan',
            'id_meta_desc' => 'Karir',
            'id_page_content' => 'Profesi',
            ), array (
            'page_alias' => 'press_media',
            'id_meta_keyword' => 'Pers dan Media',
            'id_page_title' => 'Pers dan Media',
            'id_meta_desc' => 'Pers dan Media',
            'id_page_content' => 'Pers & Media',
            ), array (
            'page_alias' => 'referral',
            'id_meta_keyword' => 'Direktori',
            'id_page_title' => 'Referensi',
            'id_meta_desc' => 'Direktori',
            'id_page_content' => 'Tautan',
            ), array (
            'page_alias' => 'offers',
            'id_meta_keyword' => 'Saran',
            'id_page_title' => 'Penawaran',
            'id_meta_desc' => 'Penawaran',
            'id_page_content' => 'Penawaran',
            ), array (
            'page_alias' => 'contact_us',
            'id_meta_keyword' => 'Tentang Kami',
            'id_page_title' => 'Tentang Kami',
            'id_meta_desc' => 'Tentang kami',
            'id_page_content' => 'Tentang Kami',
            ), array (
            'page_alias' => 'legality',
            'id_meta_keyword' => 'Legal',
            'id_page_title' => 'Legal',
            'id_meta_desc' => 'Legal',
            'id_page_content' => 'Legal',
            ), array (
            'page_alias' => 'refund_policy',
            'id_meta_keyword' => 'Kebijakan Pengembalian',
            'id_page_title' => 'Kebijakan Pengembalian Dana',
            'id_meta_desc' => 'Kebijakan Pengembalian',
            'id_page_content' => 'Kebijakan Pengembalian Dana',
            ),
        );
		
        $this->db->update_batch(CMS_PAGES,$cms_data,'page_alias');
}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'id_message');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'id_subject');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'id_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'id_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'id_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'id_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'id_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'id_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'id_page_content');
		$this->dbforge->drop_column(COMMON_CONTENT, 'id_header');
		$this->dbforge->drop_column(COMMON_CONTENT, 'id_body');
		$this->dbforge->drop_column(EARN_COINS, 'id');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'id_question');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'id_answer');
		$this->dbforge->drop_column(FAQ_CATEGORY, 'id_category');
	}

}