<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Network_fantasy_notification_transaction extends CI_Migration 
{
  function __construct()
  {
    
  }

  public function up()
  {
      //Trasaction start
      $this->db->trans_strict(TRUE);
      $this->db->trans_start();    

        $sql = "UPDATE 
                  ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)."
                SET
                  hi_message  = 'आपने खेल​ {{contest_name}} में सफलतापूर्वक प्रवेश लिया किया है।',
                  guj_message = 'તમે સફળતાપૂર્વક રમત દાખલ કરેલ {{contest_name}}',
                  fr_message  = 'Participation réussie au jeu {{contest_name}}',
                  ben_message = 'গেম {{contest_name}} সফলভাবে যোগদান করুন',
                  pun_message = 'ਗੇਮ {{contest_name}} ਸਫਲਤਾਪੂਰਵਕ ਸ਼ਾਮਲ ਹੋਵੋ',
                  tam_message = 'விளையாட்டு {{contest_name}} வெற்றிகரமாக சேர',
                  th_message  = 'เข้าร่วมเกม {{contest_name}} สำเร็จ',
                  kn_message  = 'ಗೇಮ್ {{contest_name}} ಯಶಸ್ವಿಯಾಗಿ ಸೇರಲು',
                  ru_message  = 'Игра {{contest_name}} присоединиться успешно',
                  id_message  = 'Game {{contest_name}} bergabung berhasil',
                  tl_message  = 'Laro {{contest_name}} matagumpay na sumali',
                  zh_message  = '游戏{{contest_name}}成功加入'
                WHERE 
                  notification_type ='250';";
        $this->db->query($sql);

         $sql = "UPDATE 
                  ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)."
                SET
                  hi_message  = 'मैच रद्द होने के कारण आपकी प्रतियोगिता {{contest_name}} रद्द कर दी गई है। आपका प्रवेश शुल्क आपके बटुए में वापस कर दिया गया है।',
                  guj_message = 'મેચ તમારી સ્પર્ધા રદ થયું {{contest_name}} રદ કારણે. તમારી એન્ટ્રી ફી તમારા ખિસ્સા માં પાછા આવી છે.',
                  fr_message  = 'Votre concours {{contest_name}} a été annulé en raison de lannulation de match (s). Vos frais dinscription ont été retournés dans votre solde.',
                  ben_message = 'ম্যাচ (গুলি) বাতিল হওয়ার কারণে আপনার প্রতিযোগিতার {{contest_name}} বাতিল করা হয়েছে। আপনার প্রবেশ ফি আপনার ব্যালেন্সে ফিরে এসেছে।',
                  pun_message = 'ਤੁਹਾਡਾ ਮੁਕਾਬਲਾ {{contest_name}} ਮੈਚ (ਮੈਚਾਂ) ਨੂੰ ਰੱਦ ਕਰਨ ਕਾਰਨ ਰੱਦ ਕਰ ਦਿੱਤਾ ਗਿਆ ਹੈ. ਤੁਹਾਡੀ ਐਂਟਰੀ ਫੀਸ ਤੁਹਾਡੇ ਬਕਾਏ ਵਿਚ ਵਾਪਸ ਕਰ ਦਿੱਤੀ ਗਈ ਹੈ.',
                  tam_message = 'போட்டி (கள்) ரத்து செய்யப்பட்டதால் உங்கள் போட்டி {{contest_name}} ரத்து செய்யப்பட்டுள்ளது. உங்கள் நுழைவு கட்டணம் உங்கள் இருப்புக்கு திரும்பியுள்ளது.',
                  th_message  = 'การแข่งขันของคุณ {{contest_name}} ของคุณถูกยกเลิกเนื่องจากการยกเลิกการแข่งขัน ค่าธรรมเนียมแรกเข้าของคุณถูกคืนเข้าสู่ยอดเงินของคุณแล้ว',
                  kn_message  = 'ಪಂದ್ಯ (ಗಳ) ರದ್ದತಿಯಿಂದಾಗಿ ನಿಮ್ಮ ಸ್ಪರ್ಧೆ {{contest_name}} ರದ್ದುಗೊಂಡಿದೆ. ನಿಮ್ಮ ಪ್ರವೇಶ ಶುಲ್ಕವನ್ನು ನಿಮ್ಮ ಬಾಕಿ ಮೊತ್ತಕ್ಕೆ ಹಿಂತಿರುಗಿಸಲಾಗಿದೆ.',
                  ru_message  = 'Ваш конкурс {{contest_name}} был отменен из-за отмены матча (-ов). Ваш вступительный взнос был возвращен на ваш баланс.',
                  id_message  = 'Kontes Anda {{contest_name}} telah dibatalkan karena pembatalan pertandingan. Biaya masuk Anda telah dikembalikan ke saldo Anda.',
                  tl_message  = 'Ang iyong paligsahan na {{contest_name}} ay nakansela dahil sa pagkansela ng (mga) tugma. Ang iyong bayad sa pagpasok ay naibalik sa iyong balanse.',
                  zh_message  = '您的比赛{{contest_name}}因取消比赛而被取消。 您的报名费已退还至您的余额中。'
                WHERE 
                  notification_type ='253';";
        $this->db->query($sql);

        $sql = "UPDATE 
                  ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)."
                SET
                  hi_message  = 'बधाई हो! आप {{contest_name}} मैच {{collection_name}} में विजेता हैं।',
                  guj_message = 'અભિનંદન! તમે {{contest_name}} મેચમાં {{collection_name}} વિજેતા છો.',
                  fr_message  = 'Toutes nos félicitations! Vous êtes un gagnant dans le match {{contest_name}} Concours de {{collection_name}}.',
                  ben_message = 'অভিনন্দন! আপনি {{प्रतियोगिता_নাম}} {{collection_name}} ম্যাচের প্রতিযোগিতা।',
                  pun_message = 'ਵਧਾਈਆਂ! ਤੁਸੀਂ {{contest_name}} ਮੁਕਾਬਲਾ name {{collection_name}} ਮੈਚ ਵਿੱਚ ਜੇਤੂ ਹੋ.',
                  tam_message = 'வாழ்த்துக்கள்! {{contest_name}} {{collection_name}} போட்டியில் நீங்கள் வெற்றியாளராக உள்ளீர்கள்.',
                  th_message  = 'ยินดีด้วย! คุณเป็นผู้ชนะในการแข่งขัน {{contest_name}} การแข่งขันของ {{collection_name}}',
                  kn_message  = 'ಅಭಿನಂದನೆಗಳು! {{collection_name}} {{contest_name}} ಪಂದ್ಯದ ಸ್ಪರ್ಧೆಯಲ್ಲಿ ನೀವು ವಿಜೇತರಾಗಿದ್ದೀರಿ.',
                  ru_message  = 'Поздравляю! Вы победитель в матче конкурса {{contest_name}} конкурса {{collection_name}}.',
                  id_message  = 'Selamat! Anda adalah pemenang dalam Pertandingan {{contest_name}} Kontes {{collection_name}}.',
                  tl_message  = 'Binabati kita! Nagwagi ka sa tugma sa {{contest_name}} Paligsahan ng {{koleksyon_name}}.',
                  zh_message  = '恭喜你！ 您是{{collection_name}}比赛的{{contest_name}}比赛的获胜者。'
                WHERE 
                  notification_type ='254';";
        $this->db->query($sql);
        
        $sql = "UPDATE 
                  ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)."
                SET
                  hi_message  = 'खेल​ {{contest_name}} कम लोग की भागीदारी के कारण {{collection_name}} शुरू नहीं हो रहा है और रद्द कर दिया गया है।',
                  guj_message = 'રમતગમત {{contest_name}} કારણ કે થોડા લોકો સંડોવણી શરૂ ન થાય {{collection_name}} રદ કરવામાં આવી છે.',
                  fr_message  = 'Le concours {{contest_name}} a été annulé en raison dune participation insuffisante',
                  ben_message = 'অপর্যাপ্ত অংশগ্রহণের কারণে প্রতিযোগিতা {{contest_name}} বাতিল করা হয়েছে',
                  pun_message = 'ਮੁਕਾਬਲਾ {{contest_name}} ins ਨਾਕਾਫੀ ਭਾਗੀਦਾਰੀ ਕਰਕੇ ਰੱਦ ਕਰ ਦਿੱਤਾ ਗਿਆ ਹੈ',
                  tam_message = 'போதுமான பங்கேற்பு காரணமாக போட்டி {{contest_name}} ரத்து செய்யப்பட்டுள்ளது',
                  th_message  = 'การแข่งขัน {{contest_name}} ถูกยกเลิกเนื่องจากการเข้าร่วมไม่เพียงพอ',
                  kn_message  = 'ಸಾಕಷ್ಟು ಭಾಗವಹಿಸುವಿಕೆಯಿಂದಾಗಿ ಸ್ಪರ್ಧೆ {{contest_name}} ರದ್ದುಗೊಂಡಿದೆ',
                  ru_message  = 'Конкурс {{contest_name}} был отменен из-за недостаточного участия',
                  id_message  = 'Kontes {{contest_name}} telah dibatalkan karena Partisipasi tidak mencukupi',
                  tl_message  = 'Kinansela ang {{contest_name}} dahil sa hindi sapat na Paglahok',
                  zh_message  = '由于参与不足，比赛{{contest_name}}已被取消'
                WHERE 
                  notification_type ='255';";
        $this->db->query($sql);
        
        //Transaction message update
        $sql = "UPDATE 
                  ".$this->db->dbprefix(TRANSACTION_MESSAGES)."
                SET 
                  hi_message  = '%s के लिए प्रवेश शुल्क',
                  guj_message = '%s માટે પ્રવેશ ફી',
                  fr_message  = 'Frais dinscription pour %s',
                  ben_message = '%s এর জন্য প্রবেশ ফি',
                  pun_message = '%s ਲਈ ਐਂਟਰੀ ਫੀਸ',
                  tam_message = '%s க்கான நுழைவு கட்டணம்',
                  th_message  = 'ค่าธรรมเนียมแรกเข้าสำหรับ %s',
                  kn_message  = '%s ಪ್ರವೇಶ ಶುಲ್ಕ',
                  ru_message  = 'Стартовый взнос для %s',
                  id_message  = 'Biaya masuk untuk %s',
                  tl_message  = 'Bayad sa pagpasok para sa %s',
                  zh_message  = '%s 的报名费' 
                WHERE 
                  source ='240';";
      
       $this->db->query($sql);

       $sql = "UPDATE 
                  ".$this->db->dbprefix(TRANSACTION_MESSAGES)."
                SET 
                  hi_message  = 'प्रतियोगिता का पुरस्कार जीता',
                  guj_message = 'કોન્ટેસ્ટ પ્રાઇઝ જીત્યો',
                  fr_message  = 'Prix du concours remporté',
                  ben_message = 'প্রতিযোগিতা পুরস্কার জিতেছে',
                  pun_message = 'ਮੁਕਾਬਲਾ ਇਨਾਮ ਜਿੱਤਿਆ',
                  tam_message = 'போட்டி பரிசு வென்றது',
                  th_message  = 'ได้รับรางวัลการประกวด',
                  kn_message  = 'ಗೆದ್ದಿದ್ದು ಸ್ಪರ್ಧೆ ಪ್ರಶಸ್ತಿ',
                  ru_message  = 'Выигранный приз конкурса',
                  id_message  = 'Memenangkan Hadiah Kontes',
                  tl_message  = 'Nanalong Premyo sa Paligsahan',
                  zh_message  = '赢得竞赛奖' 
                WHERE 
                  source ='241';";
       $this->db->query($sql);

       $sql = "UPDATE 
                  ".$this->db->dbprefix(TRANSACTION_MESSAGES)."
                SET 
                  hi_message  = 'प्रतियोगिता के लिए शुल्क वापसी',
                  guj_message = 'હરીફાઈ માટે ફી પરત',
                  fr_message  = 'Remboursement des frais pour le concours',
                  ben_message = 'প্রতিযোগিতার জন্য ফি ফেরত',
                  pun_message = 'ਮੁਕਾਬਲੇ ਲਈ ਫੀਸ ਦੀ ਰਿਫੰਡ',
                  tam_message = 'போட்டிக்கான கட்டணம் திரும்பப்பெறுதல்',
                  th_message  = 'การคืนเงินค่าธรรมเนียมสำหรับการแข่งขัน',
                  kn_message  = 'ಸ್ಪರ್ಧೆ ಫಾರ್ ಶುಲ್ಕ ಮರುಪಾವತಿ',
                  ru_message  = 'Возврат комиссии за конкурс',
                  id_message  = 'Pengembalian Biaya Kontes',
                  tl_message  = 'Pagbabayad sa Bayad Para sa Paligsahan',
                  zh_message  = '费退款大赛' 
                WHERE 
                  source ='242';";
       $this->db->query($sql);
    
    //Trasaction end
      $this->db->trans_complete();
      if ($this->db->trans_status() === FALSE )
      {
          $this->db->trans_rollback();
      }
      else
      {
          $this->db->trans_commit();
      }  

  }

  public function down()
  {
	   
  }

}