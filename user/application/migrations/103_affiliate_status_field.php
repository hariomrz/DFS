<?php
defined("BASEPATH") OR exit("No direct access allowed");

class Migration_Affiliate_status_field extends CI_Migration{

    /**
     * up function
     */
    public function up(){
        $field = array(
            'aff_request_date' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
				'default'=>NULL,
                "after" =>'affiliate_date'
			),
            'commission_type' => array(
				'type' => 'TINYINT',
				'null' => FALSE,
				'default'=>4,
                "after" =>'deposit_commission'
			),
        );
        $this->dbforge->add_column(USER,$field);

        $sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN (select added_date,user_id FROM vi_user) as FR ON FR.user_id = U.user_id SET `aff_request_date` = FR.added_date WHERE U.is_affiliate =1 and aff_request_date IS NULL";
        $this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." SET `message` = 'You have received {{amount}} coins for Daily Check-In Day {{day_number}}' WHERE notification_type=138;";
        $this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." SET `en_message` = 'You have received {{amount}} coins for Daily Check-In Day {{day_number}}' WHERE notification_type=138;";
        $this->db->query($sql);

    }

    /**
     * down function 
     */
    public function down(){

    }
}
?>