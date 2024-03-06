<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Referral_new_development extends CI_Migration {

  public function up()
  {

        //up script for affiliate master entry
        $affiliate_master_arr =array(
            array(
              'affiliate_type' => 22,
              'amount_type' => 2,
              'affiliate_description' => 'Every cash contest(referral)',
              'invest_money' => 0,
              'bonus_amount' => 0,
              'real_amount' => 3,
              'coin_amount' => 0,
              'user_bonus' => 0,
              'user_real' => 0,
              'user_coin' => 0,
              'is_referral' => 1,
              'max_earning_amount' => 0,
              'status' => 1,
              'order' => 21,
              'last_update_date' => '2020-07-10 01:00:00'
              ),
              array(
              'affiliate_type' => 23,
              'amount_type' => 2,
              'affiliate_description' => "Extra weekly benefit on friend's contest joining.(referral)",
              'invest_money' => 50000,
              'bonus_amount' => 0,
              'real_amount' => 1,
              'coin_amount' => 0,
              'user_bonus' => 0,
              'user_real' => 0,
              'user_coin' => 0,
              'is_referral' => 1,
              'max_earning_amount' => 0,
              'status' => 1,
              'order' => 21,
              'last_update_date' => '2020-07-10 01:00:00'
              )
                ) ;
      
            $this->db->insert_batch(AFFILIATE_MASTER,$affiliate_master_arr);



          //up script for notification descriptins  
          $notification_messages =array(
            array(
              'notification_type' => 270,
              'message' => 'Congratulations! {{friend_name}} referred by you has joined a contest. You have earned ₹{{amount}} real cash.',
              'en_message' => 'Congratulations! {{friend_name}} referred by you has joined a contest. You have earned ₹{{amount}} real cash.',
              'hi_message' => 'बधाई हो ! आपको हमारी साइट पर अपने मित्र {{friend_name}} का खेल में खेलने के लिए ₹ {{amount}} की राशी मिली है।',
              'guj_message' => 'અભિનંદન! તમે જથ્થો છે ₹ {{amount}} અમારી સાઇટ પર તમારા મિત્રો {{friend_name}} રમત રમવા માટે.',
              'fr_message' => 'Toutes nos félicitations! {{friend_name}} que vous avez référé a rejoint un concours. Vous avez gagné ₹ {{amount}} argent réel.',
              'ben_message' => "অভিনন্দন! {{friend_name}} আপনার দ্বারা উল্লেখ করা একটি প্রতিযোগিতায় যোগ দিয়েছেন। আপনি অর্জন করেছেন ₹ {{amount}} আসল নগদ",
              'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਜ਼ਿਕਰ ਕੀਤਾ {{friend_name}} a ਇੱਕ ਮੁਕਾਬਲੇ ਵਿੱਚ ਸ਼ਾਮਲ ਹੋਇਆ ਹੈ. ਤੁਸੀਂ cash {{amount}} ਅਸਲ ਨਕਦ ਕਮਾਈ ਕੀਤੀ ਹੈ.",
              //'es_message'  => "¡Felicidades! {{friend_name}} referido por que se ha unido a un concurso. Se han ganado ₹ {{amount}} de dinero real."

             ),
            array(
              'notification_type' => 271,
              'message' => 'Congratulations! {{friend_name}} referred by you has joined a contest. You have earned {{amount}} bonus cash.',
              'en_message' => 'Congratulations! {{friend_name}} referred by you has joined a contest. You have earned {{amount}} bonus cash.',
              'hi_message' => 'बधाई हो! आपके द्वारा निर्दिष्ट {{friend_name}} एक प्रतियोगिता में शामिल हो गया है। आपने {{amount}} बोनस नकद अर्जित किया है।',
              'guj_message' => 'અભિનંદન! તમારા દ્વારા ઉલ્લેખિત {{friend_name}} એક હરીફાઈમાં જોડાયા છે. તમે us {{amount}} બોનસ રોકડ મેળવી છે.',
              'fr_message' => 'Toutes nos félicitations! {{friend_name}} que vous avez référé a rejoint un concours. Vous avez gagné un bonus de {{amount}} en espèces.',
              'ben_message' => "অভিনন্দন! {{friend_name}} আপনার দ্বারা উল্লেখ করা একটি প্রতিযোগিতায় যোগ দিয়েছেন। আপনি অর্জন করেছেন {{amount}} আসল নগদ",
              'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਜ਼ਿਕਰ ਕੀਤਾ {{friend_name}} a ਇੱਕ ਮੁਕਾਬਲੇ ਵਿੱਚ ਸ਼ਾਮਲ ਹੋਇਆ ਹੈ. ਤੁਸੀਂ {{amount}} ਅਸਲ ਨਕਦ ਕਮਾਈ ਕੀਤੀ ਹੈ.",
              //'es_message'  => "¡Felicidades! {{friend_name}} referido por que se ha unido a un concurso. Se han ganado ₹ {{amount}} de dinero real."

             ),
            array(
              'notification_type' => 272,
              'message' => 'Congratulations! {{friend_name}} referred by you has joined a contest. You have earned {{amount}} coins.',
              'en_message' => 'Congratulations! {{friend_name}} referred by you has joined a contest. You have earned {{amount}} coins.',
              'hi_message' => 'बधाई हो! {{friend_name}} आपके द्वारा संदर्भित एक प्रतियोगिता में शामिल हो गया है। आपने {{amount}} सिक्के अर्जित किए हैं',
              'guj_message' => 'અભિનંદન! તમારા દ્વારા ઉલ્લેખિત {{friend_name}} એક હરીફાઈમાં જોડાયો છે. તમે {{amount}} સિક્કા મેળવી લીધા છે',
              'fr_message' => "Toutes nos félicitations! {{friend_name}} que vous avez référé a rejoint un concours. Vous avez gagné {{amount}} pièces",
              'ben_message' => "অভিনন্দন! আপনি উল্লেখ করেছেন {{friend_name}} একটি প্রতিযোগিতায় যোগ দিয়েছেন। আপনি {{amount}} মুদ্রা অর্জন করেছেন",
              'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਡੇ ਦੁਆਰਾ ਜ਼ਿਕਰ ਕੀਤਾ {{friend_name}} ਇੱਕ ਮੁਕਾਬਲੇ ਵਿੱਚ ਸ਼ਾਮਲ ਹੋਇਆ ਹੈ. ਤੁਸੀਂ {{amount}} ਸਿੱਕੇ ਪ੍ਰਾਪਤ ਕੀਤੇ ਹਨ",
              //'es_message'  => "¡Felicidades! {{friend_name}} referido por que se ha unido a un concurso. Se han ganado ₹ {{amount}} de dinero real."

             ),
            array(
              'notification_type' => 273,
              'message' => 'You have received ₹ {{amount}} real cash for joining a cash contest.',
              'en_message' => 'You have received ₹ {{amount}} real cash for joining a cash contest.',
              'hi_message' => "नकद प्रतियोगिता में शामिल होने के लिए आपको ₹ {{amount}} वास्तविक नकद प्राप्त हुए हैं।",
              'guj_message' => 'તમે રોકડ હરીફાઈમાં જોડાવા માટે ₹ {{amount}} પ્રત્યક્ષ રોકડ પ્રાપ્ત કરી છે.',
              'fr_message' => 'Vous avez reçu ₹ {{amount}} en argent réel pour participer à un concours en espèces.',
              'ben_message' => "নগদ প্রতিযোগিতায় যোগ দেওয়ার জন্য আপনি ₹ {{amount}} প্রকৃত নগদ পেয়েছেন।",
              'pun_message' => "ਤੁਹਾਨੂੰ ਨਕਦ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ ਲਈ ₹ {{amount}} ਅਸਲ ਨਕਦ ਪ੍ਰਾਪਤ ਹੋਇਆ ਹੈ.",
              //'es_message'  => "You have received {{amount}} real cash for joining a cash contest."

             ),
              array(
              'notification_type' => 274,
              'message' => "You have received {{amount}} bonus cash for joining a cash contest.",
              'en_message' => "You have received {{amount}} bonus cash for joining a cash contest.",
              'hi_message' => "नकद प्रतियोगिता में शामिल होने के लिए आपको {{amount}} बोनस नकद मिले हैं।",
              'guj_message' => "રોકડ હરીફાઈમાં જોડાવા માટે તમને {{amount}} બોનસ રોકડ મળી છે.",
              'fr_message' => "Vous avez reçu un bonus de {{amount}} en espèces pour participer à un concours en espèces.",
              'ben_message' => "নগদ প্রতিযোগিতায় যোগ দেওয়ার জন্য আপনি {{amount}} ডলার বোনাস পেয়েছেন।",
              'pun_message' => "ਨਕਦ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ ਲਈ ਤੁਹਾਨੂੰ {{amount}} ਦਾ ਬੋਨਸ ਨਕਦ ਪ੍ਰਾਪਤ ਹੋਇਆ ਹੈ.",
              //'es_message'  => "You have received {{amount}} bonus cash for joining a cash contest."

             ),
              array(
              'notification_type' => 275,
              'message' => "You have received {{amount}} coins for joining a cash contest.",
              'en_message' => "You have received {{amount}} coins for joining a cash contest.",
              'hi_message' => "आपको नकद प्रतियोगिता में शामिल होने के लिए {{amount}} के सिक्के प्राप्त हुए हैं।",
              'guj_message' => "તમને રોકડ હરીફાઈમાં જોડાવા માટે {{amount}} સિક્કા પ્રાપ્ત થયા છે.",
              'fr_message' => "Vous avez reçu {{amount}} pièces pour participer à un concours en espèces.",
              'ben_message' => "নগদ প্রতিযোগিতায় যোগ দেওয়ার জন্য আপনি {{amount}} কয়েন পেয়েছেন।",
              'pun_message' => "ਤੁਹਾਨੂੰ ਨਕਦ ਮੁਕਾਬਲੇ ਵਿੱਚ ਸ਼ਾਮਲ ਹੋਣ ਲਈ {{amount}} ਸਿੱਕੇ ਪ੍ਰਾਪਤ ਹੋਏ ਹਨ.",
              //'es_message'  => "You have received {{amount}} coins for joining a cash contest."

             ),
              array(
              'notification_type' => 276,
              'message' => "Congratulations! You got extra weekly benefit of ₹ {{amount}} real cash on your friend's contest joining.",
              'en_message' => "Congratulations! You got extra weekly benefit of ₹ {{amount}} real cash on your friend's contest joining.",
              'hi_message' => "बधाई हो! आपको अपने मित्र की प्रतियोगिता में शामिल होने पर ₹ {{amount}} वास्तविक नकदी का अतिरिक्त साप्ताहिक लाभ मिला।",
              'guj_message' => "અભિનંદન! તમને તમારા મિત્રની હરીફાઈમાં જોડાવા પર ₹ {{amount}} વાસ્તવિક રોકડનો અતિરિક્ત સાપ્તાહિક લાભ મળ્યો છે.",
              'fr_message' => "Toutes nos félicitations! Vous bénéficiez d'un avantage hebdomadaire supplémentaire de ₹ {{amount}} en argent réel lors de la participation au concours de votre ami.",
              'ben_message' => "অভিনন্দন! আপনি আপনার বন্ধুর প্রতিযোগিতায় যোগ দিতে ₹ {{amount}} আসল নগদের অতিরিক্ত সাপ্তাহিক সুবিধা পেয়েছেন।",
              'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਨੂੰ ਆਪਣੇ ਦੋਸਤ ਦੇ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ 'ਤੇ ₹ {{amount}} ਦੀ ਅਸਲ ਨਕਦ ਦਾ ਵਾਧੂ ਹਫਤਾਵਾਰੀ ਲਾਭ ਪ੍ਰਾਪਤ ਹੋਇਆ.",
              //'es_message'  => "Congratulations! You got extra weekly benefit of  {{amount}} real cash on your friend's contest joining."

             ),
              array(
              'notification_type' => 277,
              'message' => "Congratulations! You got extra weekly benefit of {{amount}} bonus cash on your friend's contest joining.",
              'en_message' => "Congratulations! You got extra weekly benefit of {{amount}} bonus cash on your friend's contest joining.",
              'hi_message' => "बधाई हो! आपको अपने मित्र की प्रतियोगिता में शामिल होने पर {{amount}} बोनस नकद का अतिरिक्त साप्ताहिक लाभ मिला।",
              'guj_message' => "અભિનંદન! તમને તમારા મિત્રની હરીફાઈમાં જોડાવા પર {{amount}} બોનસ રોકડનો વધારાનો સાપ્તાહિક લાભ મળ્યો છે.",
              'fr_message' => "Toutes nos félicitations! Vous bénéficiez d'un avantage hebdomadaire supplémentaire de {{amount}} cash en bonus lors de l'inscription au concours de votre ami.",
              'ben_message' => "অভিনন্দন! আপনি আপনার বন্ধুর প্রতিযোগিতায় যোগদানের জন্য {{amount}} বোনাস নগদের অতিরিক্ত সাপ্তাহিক সুবিধা পেয়েছেন।",
              'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਨੂੰ ਆਪਣੇ ਦੋਸਤ ਦੇ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ 'ਤੇ {{amount}} ਬੋਨਸ ਨਕਦ ਦਾ ਹਫਤਾਵਾਰੀ ਲਾਭ ਪ੍ਰਾਪਤ ਹੋਇਆ.",
              //'es_message'  => "Congratulations! You got extra weekly benefit of  {{amount}} bonus cash on your friend's contest joining."

             ),
              array(
              'notification_type' => 278,
              'message' => "Congratulations! You got extra weekly benefit of  {{amount}} coins on your friend's contest joining.",
              'en_message' => "Congratulations! You got extra weekly benefit of  {{amount}} coins on your friend's contest joining.",
              'hi_message' => "बधाई हो! आपको अपने मित्र की प्रतियोगिता में शामिल होने पर {{amount}} सिक्कों का अतिरिक्त साप्ताहिक लाभ मिला।",
              'guj_message' => "અભિનંદન! તમને તમારા મિત્રની હરીફાઈમાં જોડાવા પર {{amount}} સિક્કાનો અતિરિક્ત સાપ્તાહિક લાભ મળ્યો છે.",
              'fr_message' => "Toutes nos félicitations! Vous bénéficiez d'un avantage hebdomadaire supplémentaire de {{amount}} pièces en participant au concours de votre ami.",
              'ben_message' => "অভিনন্দন! আপনি আপনার বন্ধুর প্রতিযোগিতায় যোগ দিতে {{amount}} মুদ্রার অতিরিক্ত সাপ্তাহিক সুবিধা পেয়েছেন।",
              'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਨੂੰ ਆਪਣੇ ਦੋਸਤ ਦੇ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ 'ਤੇ {{amount}} ਸਿੱਕਿਆਂ ਦਾ ਵਾਧੂ ਹਫਤਾਵਾਰੀ ਲਾਭ ਮਿਲਿਆ.",
              //'es_message'  => "Congratulations! You got extra weekly benefit of  {{amount}} coins on your friend's contest joining."

             ),
            array(
              'notification_type' => 279,
              'message' => "Congratulations! You got extra weekly benefit of ₹ {{amount}} real cash on contest joining.",
              'en_message' => "Congratulations! You got extra weekly benefit of ₹ {{amount}} real cash on contest joining.",
              'hi_message' => "बधाई हो! प्रतियोगिता में शामिल होने पर आपको ₹ {{amount}} वास्तविक नकदी का अतिरिक्त साप्ताहिक लाभ मिला।",
              'guj_message' => "અભિનંદન! તમને હરીફાઈમાં જોડાવા પર cash ₹ {{amount}} પ્રત્યક્ષ રોકડનો અતિરિક્ત સાપ્તાહિક લાભ મળ્યો છે.",
              'fr_message' => "Toutes nos félicitations! Vous bénéficiez d'un avantage hebdomadaire supplémentaire de ₹ {{amount}} argent réel à l'inscription au concours.",
              'ben_message' => "অভিনন্দন! প্রতিযোগিতায় যোগদানের জন্য আপনি weekly ₹ {{amount}} প্রকৃত নগদের অতিরিক্ত সাপ্তাহিক সুবিধা পেয়েছেন।",
              'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਨੂੰ ਮੁਕਾਬਲੇ ਵਿੱਚ ਸ਼ਾਮਲ ਹੋਣ ਤੇ ₹ {{amount}} cash ਅਸਲ ਨਕਦ ਦਾ ਹਫਤਾਵਾਰੀ ਲਾਭ ਪ੍ਰਾਪਤ ਹੋਇਆ.",
              //'es_message'  => "Congratulations! You got extra weekly benefit of  {{amount}} real cash on contest joining."

             ),
             array(
              'notification_type' => 280,
              'message' => "Congratulations! You got extra weekly benefit of {{amount}} bonus cash on contest joining.",
              'en_message' => "Congratulations! You got extra weekly benefit of {{amount}} bonus cash on contest joining.",
              'hi_message' => "बधाई हो! प्रतियोगिता में शामिल होने पर आपको {{amount}} बोनस नकद का अतिरिक्त साप्ताहिक लाभ मिला।",
              'guj_message' => "અભિનંદન! હરીફાઈમાં જોડાવા પર તમને {{amount}} બોનસ રોકડનો વધારાનો સાપ્તાહિક લાભ મળ્યો છે.",
              'fr_message' => "Toutes nos félicitations! Vous bénéficiez d'un avantage hebdomadaire supplémentaire de {{amount}} bonus en espèces lors de l'inscription au concours.",
              'ben_message' => "অভিনন্দন! প্রতিযোগিতায় যোগদানের জন্য আপনি weekly {{amount}} বোনাস নগদের অতিরিক্ত সাপ্তাহিক সুবিধা পেয়েছেন।",
              'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਨੂੰ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ 'ਤੇ {{amount}} ਬੋਨਸ ਨਕਦ ਦਾ ਹਫਤਾਵਾਰੀ ਲਾਭ ਪ੍ਰਾਪਤ ਹੋਇਆ.",
              //'es_message'  => "Congratulations! You got extra weekly benefit of  {{amount}} bonus cash on contest joining."

             ),
             array(
              'notification_type' => 281,
              'message' => "Congratulations! You got extra weekly benefit of  {{amount}} coins on contest joining.",
              'en_message' => "Congratulations! You got extra weekly benefit of  {{amount}} coins on contest joining.",
              'hi_message' => "बधाई हो! प्रतियोगिता में शामिल होने पर आपको {{amount}} सिक्कों का अतिरिक्त साप्ताहिक लाभ मिला।",
              'guj_message' => "અભિનંદન! તમને હરીફાઈમાં જોડાવા પર {{amount}} સિક્કાનો અતિરિક્ત સાપ્તાહિક લાભ મળ્યો.",
              'fr_message' => "Toutes nos félicitations! Vous bénéficiez d'un avantage hebdomadaire supplémentaire de {{amount}} pièces lors de l'inscription au concours.",
              'ben_message' => "অভিনন্দন! প্রতিযোগিতায় যোগ দেওয়ার জন্য আপনি weekly {{amount}} মুদ্রার অতিরিক্ত সাপ্তাহিক সুবিধা পেয়েছেন।",
              'pun_message' => "ਵਧਾਈਆਂ! ਤੁਹਾਨੂੰ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ 'ਤੇ {{amount}} ਸਿੱਕਿਆਂ ਦਾ ਵਾਧੂ ਹਫਤਾਵਾਰੀ ਲਾਭ ਪ੍ਰਾਪਤ ਹੋਇਆ.",
              //'es_message'  => "Congratulations! You got extra weekly benefit of  {{amount}} coins on contest joining."

             )   
          ) ;
      
            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notification_messages);

        

      
   


    $transaction_messages = array(
          array(
              'source' => 270,
              'en_message' => 'A cash contest joined by friend',
              'hi_message' => 'मित्र द्वारा एक नकद प्रतियोगिता में शामिल हुए',
              'guj_message' => 'મિત્ર સાથે રોકડ હરીફાઈ',
              'fr_message' => 'Un concours de cash rejoint par un ami',
              'ben_message' => 'একটি নগদ প্রতিযোগিতা বন্ধুর সাথে যোগদান',
              'pun_message' => 'ਇੱਕ ਨਕਦ ਮੁਕਾਬਲੇ ਵਿੱਚ ਦੋਸਤ ਸ਼ਾਮਲ ਹੋਏ',
              //'es_message'  => 'Un concurso de efectivo unido por amigo'
          ),
          array(
              'source' => 271,
              'en_message' => 'A cash contest joined by friend',
              'hi_message' => 'मित्र द्वारा एक नकद प्रतियोगिता में शामिल हुए',
              'guj_message' => 'મિત્ર સાથે રોકડ હરીફાઈ',
              'fr_message' => 'Un concours de cash rejoint par un ami',
              'ben_message' => 'একটি নগদ প্রতিযোগিতা বন্ধুর সাথে যোগদান',
              'pun_message' => 'ਇੱਕ ਨਕਦ ਮੁਕਾਬਲੇ ਵਿੱਚ ਦੋਸਤ ਸ਼ਾਮਲ ਹੋਏ',
              //'es_message'  => 'Un concurso de efectivo unido por amigo'
          ),
          array(
              'source' => 272,
              'en_message' => 'A cash contest joined by friend',
              'hi_message' => 'मित्र द्वारा एक नकद प्रतियोगिता में शामिल हुए',
              'guj_message' => 'મિત્ર સાથે રોકડ હરીફાઈ',
              'fr_message' => 'Un concours de cash rejoint par un ami',
              'ben_message' => 'একটি নগদ প্রতিযোগিতা বন্ধুর সাথে যোগদান',
              'pun_message' => 'ਇੱਕ ਨਕਦ ਮੁਕਾਬਲੇ ਵਿੱਚ ਦੋਸਤ ਸ਼ਾਮਲ ਹੋਏ',
              //'es_message'  => 'Un concurso de efectivo unido por amigo'
          ),
          array(
              'source' => 273,
              'en_message' => 'Join a cash contest',
              'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
              'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
              'fr_message' => "participer à un concours d'argent",
              'ben_message' => 'নগদ প্রতিযোগিতায় যোগ দিন',
              'pun_message' => 'ਇੱਕ ਨਕਦ ਮੁਕਾਬਲੇ ਵਿੱਚ ਸ਼ਾਮਲ ਹੋਵੋ',
              //'es_message'  => 'Únete a un concurso de efectivo'
          ),
          array(
              'source' => 274,
              'en_message' => 'Join a cash contest',
              'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
              'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
              'fr_message' => "participer à un concours d'argent",
              'ben_message' => 'নগদ প্রতিযোগিতায় যোগ দিন',
              'pun_message' => 'ਇੱਕ ਨਕਦ ਮੁਕਾਬਲੇ ਵਿੱਚ ਸ਼ਾਮਲ ਹੋਵੋ',
              //'es_message'  => 'Únete a un concurso de efectivo'
          ),
          array(
              'source' => 275,
              'en_message' => 'Join a cash contest',
              'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
              'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
              'fr_message' => "participer à un concours d'argent",
              'ben_message' => 'নগদ প্রতিযোগিতায় যোগ দিন',
              'pun_message' => 'ਇੱਕ ਨਕਦ ਮੁਕਾਬਲੇ ਵਿੱਚ ਸ਼ਾਮਲ ਹੋਵੋ',
              //'es_message'  => 'Únete a un concurso de efectivo'
          ),
          array(
              'source' => 276,
              'en_message' => "Extra weekly benefit on friend's contest joining.",
              'hi_message' => 'मित्र की प्रतियोगिता में शामिल होने पर अतिरिक्त साप्ताहिक लाभ।',
              'guj_message' => 'મિત્રની હરીફાઈમાં જોડાવા પર વિશેષ સાપ્તાહિક લાભ.',
              'fr_message' => "Avantage hebdomadaire supplémentaire lors de l'inscription au concours d'un ami.",
              'ben_message' => 'বন্ধুর প্রতিযোগিতায় যোগদানের অতিরিক্ত সাপ্তাহিক সুবিধা।',
              'pun_message' => 'ਦੋਸਤ ਦੇ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ ਤੇ ਵਾਧੂ ਹਫਤਾਵਾਰੀ ਲਾਭ.',
              //'es_message'  => 'Beneficio semanal adicional al unirse al concurso de amigos.'
          ),
          array(
              'source' => 277,
              'en_message' => "Extra weekly benefit on friend's contest joining.",
              'hi_message' => 'मित्र की प्रतियोगिता में शामिल होने पर अतिरिक्त साप्ताहिक लाभ।',
              'guj_message' => 'મિત્રની હરીફાઈમાં જોડાવા પર વિશેષ સાપ્તાહિક લાભ.',
              'fr_message' => "Avantage hebdomadaire supplémentaire lors de l'inscription au concours d'un ami.",
              'ben_message' => 'বন্ধুর প্রতিযোগিতায় যোগদানের অতিরিক্ত সাপ্তাহিক সুবিধা।',
              'pun_message' => 'ਦੋਸਤ ਦੇ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ ਤੇ ਵਾਧੂ ਹਫਤਾਵਾਰੀ ਲਾਭ.',
              //'es_message'  => 'Beneficio semanal adicional al unirse al concurso de amigos.'
          ),
          array(
              'source' => 278,
              'en_message' => "Extra weekly benefit on friend's contest joining.",
              'hi_message' => 'मित्र की प्रतियोगिता में शामिल होने पर अतिरिक्त साप्ताहिक लाभ।',
              'guj_message' => 'મિત્રની હરીફાઈમાં જોડાવા પર વિશેષ સાપ્તાહિક લાભ.',
              'fr_message' => "Avantage hebdomadaire supplémentaire lors de l'inscription au concours d'un ami.",
              'ben_message' => 'বন্ধুর প্রতিযোগিতায় যোগদানের অতিরিক্ত সাপ্তাহিক সুবিধা।',
              'pun_message' => 'ਦੋਸਤ ਦੇ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ ਤੇ ਵਾਧੂ ਹਫਤਾਵਾਰੀ ਲਾਭ.',
              //'es_message'  => 'Beneficio semanal adicional al unirse al concurso de amigos.'
          ),
          array(
              'source' => 279,
              'en_message' => "Extra weekly benefit on contest joining.",
              'hi_message' => 'प्रतियोगिता में शामिल होने पर अतिरिक्त साप्ताहिक लाभ',
              'guj_message' => 'હરીફાઈમાં જોડાવા પર વિશેષ સાપ્તાહિક લાભ',
              'fr_message' => "Avantage hebdomadaire supplémentaire lors de l'inscription au concours",
              'ben_message' => 'প্রতিযোগিতায় যোগদানের জন্য অতিরিক্ত সাপ্তাহিক সুবিধা',
              'pun_message' => 'ਦੋਸਤ ਦੇ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ ਤੇ ਵਾਧੂ ਹਫਤਾਵਾਰੀ ਲਾਭ.',
              //'es_message'  => 'Beneficio semanal adicional al unirse al concurso'
          ),
          array(
              'source' => 280,
              'en_message' => "Extra weekly benefit on contest joining.",
              'hi_message' => 'प्रतियोगिता में शामिल होने पर अतिरिक्त साप्ताहिक लाभ',
              'guj_message' => 'હરીફાઈમાં જોડાવા પર વિશેષ સાપ્તાહિક લાભ',
              'fr_message' => "Avantage hebdomadaire supplémentaire lors de l'inscription au concours",
              'ben_message' => 'প্রতিযোগিতায় যোগদানের জন্য অতিরিক্ত সাপ্তাহিক সুবিধা',
              'pun_message' => 'ਦੋਸਤ ਦੇ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ ਤੇ ਵਾਧੂ ਹਫਤਾਵਾਰੀ ਲਾਭ.',
              //'es_message'  => 'Beneficio semanal adicional al unirse al concurso'
          ),
          array(
              'source' => 281,
              'en_message' => "Extra weekly benefit on contest joining.",
              'hi_message' => 'प्रतियोगिता में शामिल होने पर अतिरिक्त साप्ताहिक लाभ',
              'guj_message' => 'હરીફાઈમાં જોડાવા પર વિશેષ સાપ્તાહિક લાભ',
              'fr_message' => "Avantage hebdomadaire supplémentaire lors de l'inscription au concours",
              'ben_message' => 'প্রতিযোগিতায় যোগদানের জন্য অতিরিক্ত সাপ্তাহিক সুবিধা',
              'pun_message' => 'ਦੋਸਤ ਦੇ ਮੁਕਾਬਲੇ ਵਿਚ ਸ਼ਾਮਲ ਹੋਣ ਤੇ ਵਾਧੂ ਹਫਤਾਵਾਰੀ ਲਾਭ.',
              //'es_message'  => 'Beneficio semanal adicional al unirse al concurso'
          )
      );
    $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);

    


 
  }

  public function down()
  {
      //down scripts 
      $this->db->where_in('affiliate_type',array(22,23));
      $this->db->delete(AFFILIATE_MASTER);
      
      $this->db->where_in('notification_type',array(270,271,272,273,274,275,276,277,278,279,280,281));
      $this->db->delete(NOTIFICATION_DESCRIPTION);

      $this->db->where_in('source',array(270,271,272,273,274,275,276,277,278,279,280,281));
      $this->db->delete(TRANSACTION_MESSAGES);

      
  }

}