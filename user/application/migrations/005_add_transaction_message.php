<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_transaction_message extends CI_Migration {

    public function up() {

        $transaction_messages = array(
            array(
                'source' => 181,
                'en_message' => 'Won Pickem Prize',
                'hi_message' => 'पिक एम का पुरस्कार जीता',
                'guj_message' => 'પીક એમ ઇનામ જીત્યો',
            ),
        );

        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);

        $notification_description = array(
            array(
                'notification_type' => 181,
                'message' => 'You have recieved {{amount}} points for the correct pick',
                'en_message' => 'You have recieved {{amount}} points for the correct pick',
                'hi_message' => 'आपने सही पिक के लिए {{amount}} अंक प्राप्त किए हैं',
                'guj_message' => 'તમે સાચા ચૂંટેલા માટે {{amount}} પોઇન્ટ મેળવ્યા છે'
            ),
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION, $notification_description);
    }

    public function down() {
        //down script 
        $this->db->where_in('source', array(181));
        $this->db->delete(TRANSACTION_MESSAGES);

        $this->db->where_in('notification_type', array(181));
        $this->db->delete(NOTIFICATION_DESCRIPTION);
    }

}
