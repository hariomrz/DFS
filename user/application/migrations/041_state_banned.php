<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_state_banned extends CI_Migration{

	public function up(){

		$legality_values =  array(
						'page_title' => 'Legality',
						'page_alias'=> 'legality',
						'meta_keyword'=> 'Legality',
						'meta_desc'=> 'Legality',
						'page_url'=> 'legality',
						'page_content'=> 'Legality',
						'status'=> '1',
						'modified_by'=> '0',
						'added_date'=> date('Y-m-d'),
						'modified_date'=> date('Y-m-d'),
						'custom_data'=> NULL,
						'en_meta_keyword'=> 'Legality',
						'hi_meta_keyword'=> 'वैधता',
						'fr_meta_keyword'=> 'légalité',
						'guj_meta_keyword'=> 'કાયદેસરતાને',
						'ben_meta_keyword'=> 'বৈধতা',
						'pun_meta_keyword'=> 'ਕਾਨੂੰਨੀ',
						'tam_meta_keyword'=> 'சட்டப்பூர்வ',
						'en_page_title'=> 'Legality',
						'hi_page_title'=> 'वैधता',
						'guj_page_title'=> 'કાયદેસરતાને',
						'fr_page_title'=> 'légalité',
						'ben_page_title'=> 'বৈধতা',
						'pun_page_title'=> 'ਕਾਨੂੰਨੀ',
						'tam_page_title'=> 'சட்டப்பூர்வ',
						'en_meta_desc'=> 'Legality',
						'hi_meta_desc'=> 'वैधता',
						'guj_meta_desc'=> 'કાયદેસરતાને',
						'fr_meta_desc'=> 'légalité',
						'ben_meta_desc'=> 'বৈধতা',
						'pun_meta_desc'=> 'ਕਾਨੂੰਨੀ',
						'tam_meta_desc'=> 'சட்டப்பூர்வ',
						'en_page_content'=> 'Legality',
						'hi_page_content'=> 'वैधता',
						'guj_page_content'=> 'કાયદેસરતાને',
						'fr_page_content'=> 'légalité',
						'ben_page_content'=> 'বৈধতা',
						'pun_page_content'=> 'ਕਾਨੂੰਨੀ',
						'tam_page_content'=> 'சட்டப்பூர்வ'
					);
		$this->db->insert(CMS_PAGES,$legality_values);

		$fields = array(
	        'state_declaration' => array(
	                'type' => 'TINYINT',
	                'constraint' => 1,
	                'default' => 0,
	                'after' => 'bank_rejected_reason',
	                'null' => FALSE
	        )
	  	);
	  	$this->dbforge->add_column(USER,$fields);

	}

	public function down()
	{
		//down script 
		$this->db->where('page_alias', 'legality');
		$this->db->delete(CMS_PAGES);

		$this->dbforge->drop_column(USER, 'state_declaration');
	}
}
?>