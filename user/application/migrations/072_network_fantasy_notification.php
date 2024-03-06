<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Network_fantasy_notification extends CI_Migration 
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
                  fr_message = 'Participation réussie au jeu {{contest_name}}',
                  ben_message = 'গেম {{contest_name} সফলভাবে যোগদান করুন',
                  pun_message = 'ਗੇਮ {{contest_name}} ਸਫਲਤਾਪੂਰਵਕ ਸ਼ਾਮਲ ਹੋਵੋ',
                  tam_message = 'விளையாட்டு {{contest_name}} வெற்றிகரமாக சேர',
                  th_message = 'เข้าร่วมเกม {{contest_name}} สำเร็จ'
                WHERE 
                  notification_type ='250';";
        $this->db->query($sql);

         $sql = "UPDATE 
                  ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)."
                SET 
                  fr_message = 'Votre concours {{contest_name}} a été annulé en raison de l\'annulation de match (s). Vos frais d\'inscription ont été retournés dans votre solde.',
                  ben_message = 'ম্যাচ (গুলি) বাতিল হওয়ার কারণে আপনার প্রতিযোগিতার {{contest_name} বাতিল করা হয়েছে। আপনার প্রবেশ ফি আপনার ব্যালেন্সে ফিরে এসেছে।',
                  pun_message = 'ਤੁਹਾਡਾ ਮੁਕਾਬਲਾ {{contest_name} ਮੈਚ (ਮੈਚਾਂ) ਨੂੰ ਰੱਦ ਕਰਨ ਕਾਰਨ ਰੱਦ ਕਰ ਦਿੱਤਾ ਗਿਆ ਹੈ. ਤੁਹਾਡੀ ਐਂਟਰੀ ਫੀਸ ਤੁਹਾਡੇ ਬਕਾਏ ਵਿਚ ਵਾਪਸ ਕਰ ਦਿੱਤੀ ਗਈ ਹੈ.',
                  tam_message = 'போட்டி (கள்) ரத்து செய்யப்பட்டதால் உங்கள் போட்டி {{contest_name}} ரத்து செய்யப்பட்டுள்ளது. உங்கள் நுழைவு கட்டணம் உங்கள் இருப்புக்கு திரும்பியுள்ளது.',
                  th_message = 'การแข่งขันของคุณ {{contest_name}} ของคุณถูกยกเลิกเนื่องจากการยกเลิกการแข่งขัน ค่าธรรมเนียมแรกเข้าของคุณถูกคืนเข้าสู่ยอดเงินของคุณแล้ว'
                WHERE 
                  notification_type ='253';";
        $this->db->query($sql);

        $sql = "UPDATE 
                  ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)."
                SET 
                  message = 'Congratulations! You\'re a winner in the {{contest_name}} Contest of {{collection_name}} match.',
                  en_message = 'Congratulations! You\'re a winner in the {{contest_name}} Contest of {{collection_name}} match.',
                  hi_message = 'बधाई हो! आप {{contest_name}} मैच {{collection_name}} में विजेता हैं।',
                  guj_message = 'અભિનંદન! તમે {{contest_name}} મેચમાં {{collection_name}} વિજેતા છો.',
                  fr_message = 'Toutes nos félicitations! Vous êtes un gagnant dans le match {{contest_name}} Concours de {{collection_name}}.',
                  ben_message = 'অভিনন্দন! আপনি {{प्रतियोगिता_নাম}} {{collection_name}} ম্যাচের প্রতিযোগিতা।',
                  pun_message = 'ਵਧਾਈਆਂ! ਤੁਸੀਂ {{contest_name}} ਮੁਕਾਬਲਾ name {{collection_name}} ਮੈਚ ਵਿੱਚ ਜੇਤੂ ਹੋ.',
                  tam_message = 'வாழ்த்துக்கள்! {{contest_name}} {{collection_name}} போட்டியில் நீங்கள் வெற்றியாளராக உள்ளீர்கள்.',
                  th_message = 'ยินดีด้วย! คุณเป็นผู้ชนะในการแข่งขัน {{contest_name}} การแข่งขันของ {{collection_name}}'
                WHERE 
                  notification_type ='254';";
        $this->db->query($sql);

        $sql = "UPDATE 
                  ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)."
                SET 
                  fr_message = 'Le concours {{contest_name}} a été annulé en raison d\'une participation insuffisante',
                  ben_message = 'অপর্যাপ্ত অংশগ্রহণের কারণে প্রতিযোগিতা {{contest_name}} বাতিল করা হয়েছে',
                  pun_message = 'ਮੁਕਾਬਲਾ {{contest_name}} ins ਨਾਕਾਫੀ ਭਾਗੀਦਾਰੀ ਕਰਕੇ ਰੱਦ ਕਰ ਦਿੱਤਾ ਗਿਆ ਹੈ',
                  tam_message = 'போதுமான பங்கேற்பு காரணமாக போட்டி {{contest_name}} ரத்து செய்யப்பட்டுள்ளது',
                  th_message = 'การแข่งขัน {{contest_name}} ถูกยกเลิกเนื่องจากการเข้าร่วมไม่เพียงพอ'
                WHERE 
                  notification_type ='255';";
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