<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Network_fantasy extends CI_Migration {
function __construct()
{
  
}



  public function up()
  {
      //Trasaction start
      $this->db->trans_strict(TRUE);
      $this->db->trans_start();    

          $notification_messages =array(
            array(
              'notification_type' => 250,
              'message' => 'Game {{contest_name}} join successfully',
              'en_message' => 'Game {{contest_name}} join successfully',
              'hi_message' => 'आपने खेल​ {{contest_name}} में सफलतापूर्वक प्रवेश लिया किया है।',
              'guj_message' => 'તમે સફળતાપૂર્વક રમત દાખલ કરેલ {{contest_name}}',
              'fr_message' => 'Game {{contest_name}} join successfully'),
              array(
                'notification_type' => 254,
                'message' => 'Congratulations! You\'re a winner in the {{collection_name}} match.',
                'en_message' => 'Congratulations! You\'re a winner in the {{collection_name}} match.',
                'fr_message' => 'Congratulations! You\'re a winner in the {{collection_name}} match.',
                'hi_message' => 'बधाई हो! आप {{collection_name}} मैच में विजेता हैं।',
                'guj_message' => 'અભિનંદન! તમે {{collection_name}} મેચમાં વિજેતા છો.'),
              array(
                'notification_type' => 255,
                'message' => 'Contest {{contest_name}} has been canceled due to insufficient participation',
                'en_message' => 'Contest {{contest_name}} has been canceled due to insufficient participation',
                'fr_message' => 'Contest {{contest_name}} has been canceled due to insufficient participation',
                'hi_message' => 'खेल​ {{contest_name}} कम लोग की भागीदारी के कारण {{collection_name}} शुरू नहीं हो रहा है और रद्द कर दिया गया है।',
                'guj_message' => 'રમતગમત {{contest_name}} કારણ કે થોડા લોકો સંડોવણી શરૂ ન થાય {{collection_name}} રદ કરવામાં આવી છે.'),
              array(
                'notification_type' => 253,
                'message' => 'Your contest {{contest_name}} has been cancelled due to cancellation of match(s). Your entry fee has been returned into your balance.',
                'en_message' => 'Your contest {{contest_name}} has been cancelled due to cancellation of match(s). Your entry fee has been returned into your balance.',
                'fr_message' => 'Your contest {{contest_name}} has been cancelled due to cancellation of match(s). Your entry fee has been returned into your balance.',
                'hi_message' => 'मैच रद्द होने के कारण आपकी प्रतियोगिता {{contest_name}} रद्द कर दी गई है। आपका प्रवेश शुल्क आपके बटुए में वापस कर दिया गया है।',
                'guj_message' => 'મેચ તમારી સ્પર્ધા રદ થયું {{contest_name}} રદ કારણે. તમારી એન્ટ્રી ફી તમારા ખિસ્સા માં પાછા આવી છે.')
                ) ;
      
            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notification_messages);

        

       $email_templates = array(

                      array(
                        'template_name' => 'network-join-contest',
                        'subject'       => 'Your contest joining is confirmed!',
                        'template_path' => 'network-join-contest',
                        'notification_type' => 250,
                        'status' => 1,
                        'display_label' => 'Network Join Contest'
                      ),
                      array(
                        'template_name' => 'network-contest-won',
                        'subject' => 'Wohoo! You just WON!',
                        'template_path' => 'network-contest-won',
                        'notification_type' => 254,
                        'status' => 1,
                        'display_label' => 'Network Contest Won'
                      ),

                      array(
                        'template_name' => 'network-contest-cancel',
                        'subject' => 'Sorry! Your room did not fill up :(',
                        'template_path' => 'network-contest-cancel',
                        'notification_type' => 255,
                        'status' => 1,
                        'display_label' => 'Network contest cancel'
                      ),
                      array(
                      'template_name' => 'network-match-canceled-emailer',
                      'subject' => 'Oops! Match Cancelled!',
                      'template_path' => 'network-match-canceled-emailer',
                      'notification_type' => 253,
                      'status' => 1,
                      'display_label' => 'Network match cancel'
                    )



                      );     

                
       $this->db->insert_batch(EMAIL_TEMPLATE,$email_templates);
   


    $transaction_messages = array(
          array(
              'source' => 240,
              'en_message' => 'Entry fee for %s',
              'fr_message' => 'Entry fee for %s',
              'hi_message' => '%s के लिए प्रवेश शुल्क',
              'guj_message' => '%s માટે પ્રવેશ ફી',
          ),
          array(
              'source' => 241,
              'en_message' => 'Won Contest Prize',
              'fr_message' => 'Won Contest Prize',
              'hi_message' => 'प्रतियोगिता का पुरस्कार जीता',
              'guj_message' => 'કોન્ટેસ્ટ પ્રાઇઝ જીત્યો',
          ),
          array(
              'source' => 242,
              'en_message' => 'Fee Refund For Contest',
              'fr_message' => 'Fee Refund For Contest',
              'hi_message' => 'प्रतियोगिता के लिए शुल्क वापसी',
              'guj_message' => 'હરીફાઈ માટે ફી પરત',
          )
      );
    $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);

    
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
	    
    //Trasaction start
      $this->db->trans_strict(TRUE);
      $this->db->trans_start();
      
      //down script 
      $this->db->where_in('notification_type',array(250,254,255,253));
      $this->db->delete(NOTIFICATION_DESCRIPTION);
      
      $this->db->where_in('notification_type',array(250,254,255,253));
      $this->db->delete(EMAIL_TEMPLATE);

      $this->db->where_in('source', array(240,241,242));
      $this->db->delete(TRANSACTION_MESSAGES);


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
}