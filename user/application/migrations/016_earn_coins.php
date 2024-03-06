<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Earn_coins extends CI_Migration {

    public function up() {

        $fields = array(
            'earn_coins_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE,
                    'null' => FALSE
            ),
            'module_key' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => FALSE
            ),
            'en' => array(
              'type' => 'JSON',
              'null' => TRUE,
              'default' => NULL
            ),
            'hi' => array(
                'type' => 'JSON',
                'null' => TRUE,
                'default' => NULL
              ),
              'guj' => array(
                'type' => 'JSON',
                'null' => TRUE,
                'default' => NULL
              ),
              'fr' => array(
                'type' => 'JSON',
                'null' => TRUE,
                'default' => NULL
              ),
              'ben' => array(
                'type' => 'JSON',
                'null' => TRUE,
                'default' => NULL
              ),
              'pun' => array(
                'type' => 'JSON',
                'null' => TRUE,
                'default' => NULL
              ),
           
            'image_url' => array(
              'type' => 'VARCHAR',
              'constraint' => 100,
              'null' => TRUE
            ),
            'status' => array(
              'type' => 'TINYINT',
              'constraint' => 1,
              'null' => FALSE,
              'default' => 1,
              'comment' => '0=>inactive,1=>active'
            ),
            'url' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
                )
            );
    
          $attributes = array('ENGINE' => 'InnoDB');
          $this->dbforge->add_field($fields);
          $this->dbforge->add_key('earn_coins_id',TRUE);
          $this->dbforge->create_table(EARN_COINS ,FALSE,$attributes);   
          $earn_coins =array (
            
            array (
              'module_key' => 'refer-a-friend',
              'en' => 
              json_encode(array (
                'label' => 'REFER A FRIEND',
                'description' => 'Earn #coins# coins for every friend\'s sign up',
                'button_text' => 'REFER',
              )),
              'hi' => 
              json_encode(array (
                'label' => 'मित्र को आमंत्रित करें',
                'description' => 'हर दोस्त के साइन अप के लिए #coins# सिक्के कमाएँ',
                'button_text' => 'संदर्भ लें',
              )) ,
              'guj' => 
              json_encode(array (
                'label' => 'મિત્રને નો સંદર્ભ લો',
                'description' => 'દરેક મિત્ર સાઇન અપ માટે #coins# સિક્કા કમાઓ',
                'button_text' => 'સંદર્ભ',
              )) ,
              'fr' => 
              json_encode(array (
                'label' => 'RÉFÉREZ UN AMI',
                'description' => 'Gagnez #coins# pièces pour chaque ami \'s vous inscrire',
                'button_text' => 'RÉFÉRER',
              )),
              'ben' => 
              json_encode(array (
                'label' => 'একটা বন্ধু উল্লেখ কর',
                'description' => 'প্রত্যেক বন্ধু \ জন্য #coins# কয়েন উপার্জন \'s সাইন আপ',
                'button_text' => 'পড়ুন',
              )),
              'pun' => 
              json_encode(array (
                'label' => 'ਇੱਕ ਦੋਸਤ ਨੂੰ ਵੇਖੋ',
                'description' => 'ਹਰੇਕ ਦੋਸਤ ਦੇ ਸਾਈਨ ਅਪ ਲਈ #coins# ਸਿੱਕੇ ਕਮਾਓ',
                'button_text' => 'ਹਵਾਲਾ',
              )),   
              'image_url' => 'refer-img1.png',
              'status' => 1,
              'url' => '',
            ),
           
            array (
              'module_key' => 'daily_streak_bonus',
              'en' => 
              json_encode(array (
                'label' => 'DAILY CHECK-IN BONUS',
                'description' => 'Earn coins daily by logging in',
                'button_text' => 'Learn more',
              )) ,
              'hi' => 
              json_encode(array (
                'label' => 'रोज चेक-इन बोनस',
                'description' => 'में दैनिक सिक्के कमाएँ प्रवेश द्वारा',
                'button_text' => 'और अधिक जानें',
              )) ,
              'guj' => 
              json_encode(array (
                'label' => 'દૈનિક ચેક-ઇન બોનસ',
                'description' => 'દૈનિક સિક્કા કમાઓ લોગીંગ દ્વારા',
                'button_text' => 'વધુ શીખો',
              )) ,
              'fr' => 
              json_encode(array (
                'label' => 'TOUS LES JOURS CHECK-IN BONUS',
                'description' => 'Gagnez tous les jours des pièces en vous connectant',
                'button_text' => 'Apprendre encore plus',
              )) ,
              'ben' => 
              json_encode(array (
                'label' => 'দৈনিক চেক-ইন বোনাস',
                'description' => 'দৈনিক কয়েন উপার্জন লগ ইন করে',
                'button_text' => 'আরো জানুন',
              )) ,
              'pun' => 
              json_encode(array (
                'label' => 'ਡੇਲੀ ਚੈਕ-ਇਨ ਬੋਨਸ',
                'description' => 'ਲੌਗਇਨ ਕਰਕੇ ਰੋਜ਼ਾਨਾ ਸਿੱਕੇ ਕਮਾਓ',
                'button_text' => 'ਜਿਆਦਾ ਜਾਣੋ',
              )) ,
              'image_url' => 'checkins-img-ic.png',
              'status' => 1,
              'url' => '',
            ),
            
            array (
              'module_key' => 'prediction',
              'en' => 
              json_encode(array (
                'label' => 'PLAY PREDICTION',
                'description' => 'Predict and earn coins',
                'button_text' => 'PREDICT',
              )) ,
              'hi' => 
              json_encode(array (
                'label' => 'खेलने पूर्वानुमान',
                'description' => 'भविष्यवाणी और सिक्कों कमाने',
                'button_text' => 'भविष्यवाणी',
              )),
              'guj' => 
               json_encode(array (
                'label' => 'નાટક આગાહી',
                'description' => 'અનુમાન અને સિક્કા કમાઇ',
                'button_text' => 'આગાહી',
              )),
              'fr' => 
              json_encode(array (
                'label' => 'JEU PRÉVISION',
                'description' => 'Prédire et gagner des pièces',
                'button_text' => 'PRÉDIRE',
              )) ,
              'ben' => 
              json_encode(array (
                'label' => 'প্লে ভবিষ্যদ্বাণী',
                'description' => 'পূর্বাভাস দিন এবং কয়েন উপার্জন',
                'button_text' => 'ভবিষ্যদ্বাণী',
              )) ,
              'pun' => 
              json_encode(array (
                'label' => 'PREDICTION ਖੇਡੋ',
                'description' => 'ਭਵਿੱਖਬਾਣੀ ਕਰੋ ਅਤੇ ਸਿੱਕੇ ਕਮਾਓ',
                'button_text' => 'ਅੰਦਾਜ਼ਾ',
              )) ,
              'image_url' => 'prediction-img-ic.png',
              'status' => 1,
              'url' => '',
            ),
            
            array (
              'module_key' => 'promotions',
              'en' => 
              json_encode(array (
                'label' => 'PROMOTIONS',
                'description' => 'Ran out of coins? Watch a video and refill your coin wallet',
                'button_text' => 'WATCH',
              )),
              'hi' => 
              json_encode(array (
                'label' => 'प्रचार',
                'description' => 'कम सिक्के? एक वीडियो देखें और अपने सिक्का बटुआ फिर से भरना',
                'button_text' => 'देखें',
              )),
              'guj' => 
              json_encode(array (
                'label' => 'બઢતી',
                'description' => 'ઓછી સિક્કા? વિડિઓ જુઓ અને તમારા સિક્કો વોલેટ રિફિલ',
                'button_text' => 'જો',
              )),
              'fr' => 
              json_encode(array (
                'label' => 'PROMOTIONS',
                'description' => 'A manqué de pièces de monnaie? Regarder une vidéo et remplir votre porte-monnaie de la pièce',
                'button_text' => 'REGARDER',
              )),
              'ben' => 
              json_encode(array (
                'label' => 'প্রচার',
                'description' => 'কয়েন পরিমাণ স্বল্প? একটি ভিডিও দেখুন এবং আপনার মুদ্রা মানিব্যাগ ভর্তি',
                'button_text' => 'ঘড়ি',
              )),
              'pun' => 
              json_encode(array (
                'label' => 'ਤਰੱਕੀ',
                'description' => 'ਸਿੱਕੇ ਖਤਮ ਹੋ ਗਏ? ਵੀਡੀਓ ਵੇਖੋ ਅਤੇ ਆਪਣਾ ਸਿੱਕਾ ਵਾਲਾ ਬਟੂਆ ਭਰੋ',
                'button_text' => 'ਵਾਚ',
              )),
              'image_url' => 'promotion-img-ic.png',
              'status' => 1,
              'url' => '',
            ),
           
            array (
              'module_key' => 'feedback',
              'en' => 
              json_encode( array (
                'label' => 'Feedback',
                'description' => 'Genuine feedback will get coins after admin approval',
                'button_text' => 'Write Us',
              )),
              'hi' => 
              json_encode(array (
                'label' => 'प्रतिपुष्टि',
                'description' => 'वास्तविक प्रतिक्रिया व्यवस्थापक अनुमोदन के बाद सिक्के मिल जाएगा',
                'button_text' => 'हमें लिखें',
              )),
              'guj' => 
              json_encode(array (
                'label' => 'પ્રતિસાદ',
                'description' => 'જેન્યુઇન પ્રતિસાદ એડમિન મંજૂરી પછી સિક્કા મળશે',
                'button_text' => 'અમને લખો',
              )),
              'fr' => 
              json_encode( array (
                'label' => 'Retour d\'information',
                'description' => 'rétroaction authentique sera obtenir des pièces après approbation de l\'administrateur',
                'button_text' => 'Écrivez-nous',
              )),
              'ben' => 
              json_encode( array (
                'label' => 'প্রতিক্রিয়া',
                'description' => 'জেনুইন প্রতিক্রিয়া অ্যাডমিন অনুমোদনের পরে কয়েন পাবেন',
                'button_text' => 'আমাদের লিখুন',
              )),
              'pun' => 
              json_encode( array (
                'label' => 'ਸੁਝਾਅ',
                'description' => 'ਸਹੀ ਫੀਡਬੈਕ ਐਡਮਿਨ ਦੀ ਮਨਜ਼ੂਰੀ ਤੋਂ ਬਾਅਦ ਸਿੱਕੇ ਪ੍ਰਾਪਤ ਕਰੇਗਾ',
                'button_text' => 'ਸਾਨੂੰ ਲਿਖੋ',
              )),
              'image_url' => 'feedback-img-ic.png',
              'status' => 1,
              'url' => '',
            ),
        );

        $this->db->insert_batch(EARN_COINS,$earn_coins);
    }
    
    public function down() {
        	//down script 
	    $this->dbforge->drop_table(EARN_COINS);
    }
}
