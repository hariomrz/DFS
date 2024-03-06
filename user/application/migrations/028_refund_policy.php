<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Refund_policy extends CI_Migration{

	public function up(){

		 $refund_policy_values = 
                    array(
                        'page_id' => '14',
                        'page_title' => 'Refund Policy',
                        'page_alias'=> 'refund_policy',
                        'meta_keyword'=> 'Refund Policy',
                        'meta_desc'=> 'Refund Policy',
                        'page_url'=> 'refund_policy',
                        'page_content'=> 'Refund Policy',
                        'status'=> '1',
                        'modified_by'=> '0',
                        'added_date'=> date('Y-m-d'),
                        'modified_date'=> date('Y-m-d'),
                        'custom_data'=> NULL,
                        'en_meta_keyword'=> 'Refund Policy',
                        'hi_meta_keyword'=> 'धन वापसी नीति',
                        'fr_meta_keyword'=> 'Politique de remboursement',
                        'guj_meta_keyword'=> 'રીફંડ નીતિ',
                        'ben_meta_keyword'=> 'প্রত্যর্পণ নীতি',
                        'pun_meta_keyword'=> 'ਰਿਫੰਡ ਨੀਤੀ',
                        //'es_meta_keyword'=> 'Politica de reembolso',
                        'tam_meta_keyword'=> 'திரும்பப்பெறும் கொள்கை',
                        'en_page_title'=> 'Refund Policy',
                        'hi_page_title'=> 'धन वापसी नीति',
                        'guj_page_title'=> 'રીફંડ નીતિ',
                        'fr_page_title'=> 'Politique de remboursement',
                        'ben_page_title'=> 'প্রত্যর্পণ নীতি',
                        'pun_page_title'=> 'ਰਿਫੰਡ ਨੀਤੀ',
                        //'es_page_title'=> 'Politica de reembolso',
                        'tam_page_title'=> 'திரும்பப்பெறும் கொள்கை',
                        'en_meta_desc'=> 'Refund Policy',
                        'hi_meta_desc'=> 'धन वापसी नीति',
                        'guj_meta_desc'=> 'રીફંડ નીતિ',
                        'fr_meta_desc'=> 'Politique de remboursement',
                        'ben_meta_desc'=> 'প্রত্যর্পণ নীতি',
                        'pun_meta_desc'=> 'ਰਿਫੰਡ ਨੀਤੀ',
                        //'es_meta_desc'=> 'Politica de reembolso',
                        'tam_meta_desc'=> 'திரும்பப்பெறும் கொள்கை',
                        'en_page_content'=> 'Refund Policy',
                        'hi_page_content'=> 'धन वापसी नीति',
                        'guj_page_content'=> 'રીફંડ નીતિ',
                        'fr_page_content'=> 'Politique de remboursement',
                        'ben_page_content'=> 'প্রত্যর্পণ নীতি',
                        'pun_page_content'=> 'ਰਿਫੰਡ ਨੀਤੀ',
                        //'es_page_content'=> 'Politica de reembolso',
                        'tam_page_content'=> 'திரும்பப்பெறும் கொள்கை'
                      );
        $this->db->insert(CMS_PAGES,$refund_policy_values);

	}

	public function down()
  {
	//down script  
	$this->db->where('page_alias', 'refund_policy');
	$this->db->delete(CMS_PAGES);
  }

}
?>