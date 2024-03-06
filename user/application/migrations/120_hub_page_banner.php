<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Hub_page_banner extends CI_Migration {

    public function up() {

        $fields = array(
	        'game_type' => array(
	                'type' => 'TINYINT',
	                'constraint' => 1,
	                'default' => 1,
	                'after' => 'game_key',
                    'comment'=>'1 -> real game, 2 -> coin game',
	                'null' => FALSE
	        )
	  	);
	  	$this->dbforge->add_column(SPORTS_HUB,$fields);

    
    
          $coin_games = array(
        "allow_prediction",
        "allow_pickem",
        "allow_open_predictor",
        "allow_fixed_open_predictor"
    );      
    $this->db->where_in('game_key', $coin_games);
    $this->db->update(SPORTS_HUB, array('game_type' => '2',));

    //updating columns now

    $sports_hub_arr = array(
        array (
            'en_title'   => 'Predictor Prize Pool',
            'hi_title'   => 'पूर्वानुमान पुरस्कार पूल',
            'guj_title'  => 'આગાહી કરનાર પુરસ્કાર પૂલ',
            'fr_title'   => 'Prix ​​de prédicteur',
            'ben_title'  => 'Predictor Prize পুল',
            'pun_title'  => 'ਭਵਿੱਖਬਾਣੀ ਦਾ ਇਨਾਮ ਪੂਲ',
            'tam_title'  => 'Predictor பரிசு பூல்',
            'th_title'   => 'Predictor Prize Pool',
            'kn_title'   => 'ಪ್ರಿಡಿಕ್ಟರ್ ಪ್ರಶಸ್ತಿ ಪೂಲ್',
            'ru_title'   => 'Предиктор призовой бассейн',
            'id_title'   => 'Predictor Hadiah Pool.',
            'tl_title'   => 'Predictor Prize Pool.',
            'zh_title'   => '预测奖池',
            'game_key'   => 'allow_open_predictor',
            ), array (
            'en_title'   => 'Predictor Leaderboard',
            'hi_title'   =>'भविष्यवक्ता लीडरबोर्ड',
            'guj_title'  =>'આગાહી કરનાર લીડરબોર્ડ',
            'fr_title'   =>'Classeur de prédicteur',
            'ben_title'  =>'Predictor লিডারবোর্ড',
            'pun_title'  =>'ਭਵਿੱਖਬਾਣੀ ਲੀਡਰਬੋਰਡ',
            'tam_title'  =>'முன்னறிவிப்பு லீடர்போர்டு',
            'th_title'   =>'กระดานผู้นำตัวทำนาย',
            'kn_title'   =>'ಮುನ್ಸೂಚಕ ಲೀಡರ್ಬೋರ್ಡ್',
            'ru_title'   =>'Предсказатель лидеров',
            'id_title'   =>'Papan peringkat prediktor',
            'tl_title'   =>'Predictor leaderboard.',
            'zh_title'   =>'预测者排行榜',
            'game_key'   => 'allow_fixed_open_predictor',
            ), array (
            'en_title'   => 'Daily Fantasy',
            'hi_title'   =>'दैनिक काल्पनिक',
            'guj_title'  =>'દૈનિક ફૅન્ટેસી',
            'fr_title'   =>'Fantaisie quotidienne',
            'ben_title'  =>'দৈনিক ফ্যান্টাসি',
            'pun_title'  =>'ਰੋਜ਼ਾਨਾ ਕਲਪਨਾ',
            'tam_title'  =>'தினசரி பேண்டஸி',
            'th_title'   =>'แฟนตาซีทุกวัน',
            'kn_title'   =>'ದೈನಂದಿನ ಫ್ಯಾಂಟಸಿ',
            'ru_title'   =>'Ежедневная фантастика.',
            'id_title'   =>'Fantasi harian',
            'tl_title'   =>'Araw-araw na pantasiya',
            'zh_title'   =>'每日幻想',
            'game_key'   => 'allow_dfs',
            ), array (
            'en_title'   => 'Predict & Win',
            'hi_title'   =>'भविष्यवाणी और जीत',
            'guj_title'  =>'આગાહી અને જીત',
            'fr_title'   =>'Prédire et gagner',
            'ben_title'  =>'পূর্বাভাস এবং জয়',
            'pun_title'  =>'ਭਵਿੱਖਬਾਣੀ ਅਤੇ ਜਿੱਤ',
            'tam_title'  =>'கணிப்பு & வெற்றி',
            'th_title'   =>'ทำนายและชนะ',
            'kn_title'   =>'ಊಹಿಸಲು ಮತ್ತು ಗೆಲ್ಲಲು',
            'ru_title'   =>'Предсказать и победить',
            'id_title'   =>'Prediksi & Win.',
            'tl_title'   =>'Hulaan at manalo',
            'zh_title'   =>'预测胜利',
            'game_key'   => 'allow_prediction',
            ), array (
            'en_title'   => 'Pick’em Pool',
            'hi_title'   =>'पिकम पूल',
            'guj_title'  =>'પિક\'મ પૂલ',
            'fr_title'   =>'Piscine choisie',
            'ben_title'  =>'পিক\'এম পুল',
            'pun_title'  =>'ਪਿਕਲੀਮ ਪੂਲ',
            'tam_title'  =>'பிக்\'எம் குளம்',
            'th_title'   =>'พิกเค็ม พูล',
            'kn_title'   =>'ಪಿಕ್ಮೆಮ್ ಪೂಲ್',
            'ru_title'   =>'Выбери пул',
            'id_title'   =>'Pick\'em pool.',
            'tl_title'   =>'Pick\'em pool.',
            'zh_title'   =>'挑选池',
            'game_key'   => 'allow_pickem',
            ), 
    );

		$this->db->update_batch(SPORTS_HUB,$sports_hub_arr,'game_key');

    }
    public function down()
    {
        $this->dbforge->drop_column(SPORTS_HUB, 'game_type');
    }
}