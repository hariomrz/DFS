<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Admin_withdraw_notification extends CI_Migration {

    public function up() {

        $transaction_messages = array(
            array(
                'source' => 184,
                'en_message' => 'Admin withdrawal',
                'hi_message' => 'निकासी करें',
                'guj_message' => 'એડમિન ખસી',
            ),
        );

        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);

        $notification_description = array(
            array(
                'notification_type' => 184,
                'message' => 'Admin withdrawal  ₹{{amount}} from your account {{reason}}',
                'en_message' => 'Admin withdrawal  ₹{{amount}} from your account {{reason}}',
                'hi_message' => 'अपने खाते से व्यवस्थापन निकासी ₹{{amount}}  {{reason}}',
                'guj_message' => 'તમારા એકાઉન્ટમાંથી એડમિશન પાછી ખેંચી. ₹{{amount}} {{reason}}'
            ),
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION, $notification_description);
    }

    public function down() {
        //down script 
        $this->db->where_in('source', array(184));
        $this->db->delete(TRANSACTION_MESSAGES);

        $this->db->where_in('notification_type', array(184));
        $this->db->delete(NOTIFICATION_DESCRIPTION);
    }

}
