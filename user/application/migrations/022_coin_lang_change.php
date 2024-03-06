<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_coin_lang_change     extends CI_Migration {

	public function up() {
		//up script
	   	$sql = "UPDATE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." SET `hi_message` = 'आप प्राप्त करने वाले {{amount}} दैनिक चेकइन के लिए सिक्के दिवस {{day_number}}' WHERE notification_type = 138;
           ";
        $this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." SET `guj_message` = 'તમે પ્રાપ્ત થયો છે {{amount}} દૈનિક ચેકઇન માટે સિક્કા ડે {{day_number}}' WHERE notification_type = 138;
        ";
     $this->db->query($sql);
    }

	public function down() {
		
	}

}