<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Buy_coin_transaction extends CI_Migration{

    public function up(){
        $transactions_message = array(
			array(
				'source'				=>'282',
				'en_message'			=>'Buy Coin',
				'hi_message'			=>'खरीदें सिक्का',
				'guj_message'			=>'ખરીદો સિક્કો',
				'fr_message'			=>'Acheter Coin',
				'ben_message'			=>'কিনুন মুদ্রা',
				'pun_message'			=>'ਖਰੀਦੋ ਸਿੱਕਾ',
				'tam_message'			=>'நாணயம் வாங்க',
				'th_message'			=>'ซื้อเหรียญ',
				'kn_message'			=>'ನಾಣ್ಯ ಖರೀದಿ',
				'ru_message'			=>'Купить монеты',
				'tl_message'			=>'Bumili ng barya',
				'id_message'			=>'Beli Coin',
				'zh_message'			=>'购买硬币',
            ),
            array(
                'source'				=>'283',
				'en_message'			=>'Debit Amount for Buy Coin',
				'hi_message'			=>'खरीदें सिक्का के लिए डेबिट राशि',
				'guj_message'			=>'ખરીદો સિક્કો માટે ડેબિટ રકમ',
				'fr_message'			=>'Débit Montant pour Acheter Coin',
				'ben_message'			=>'কিনুন মুদ্রা জন্য ডেবিট পরিমাণ',
				'pun_message'			=>'ਖਰੀਦੋ ਸਿੱਕਾ ਲਈ ਡੈਬਿਟ ਮਾਤਰਾ',
				'tam_message'			=>'வாங்க நாணய பற்று தொகை',
				'th_message'			=>'เดบิตจำนวนเงินสำหรับการซื้อเหรียญ',
				'kn_message'			=>'ನಾಣ್ಯ ಖರೀದಿ ಡೆಬಿಟ್ ಪ್ರಮಾಣ',
				'ru_message'			=>'Дебет Сумма для покупки монет',
				'tl_message'			=>'Halaga debit para Bumili ng barya',
				'id_message'			=>'Jumlah debit untuk Beli Coin',
				'zh_message'			=>'借方金额为购买硬币',
            ),
		);
		$this->db->insert_batch(TRANSACTION_MESSAGES,$transactions_message);
    }

    public function down(){
        $this->db->where_in('source', [282,283]);
		$this->db->delete(TRANSACTION_MESSAGES);
    }

}

?>