<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Distributo_email_template extends CI_Migration {

    public function up() {
        $sql = "SELECT * FROM ".$this->db->dbprefix(EMAIL_TEMPLATE)." WHERE notification_type=301";
        $result = $this->db->query($sql)->result_array();
        if(empty($result)){

            $sql="INSERT INTO ".$this->db->dbprefix(EMAIL_TEMPLATE)." (`template_name`, `subject`, `template_path`, `notification_type`, `status`, `type`,
             `email_body`, `message_body`, `display_label`, `date_added`, `modified_date`) VALUES
            ('registration_from_admin', 'Registration', 'registration_from_admin', 301, 1, 0, NULL, NULL, 'Registration', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."');";
            $this->db->query($sql);
        }
        else{
            $sql = "UPDATE ".$this->db->dbprefix(EMAIL_TEMPLATE)." SET 
		template_name='registration_from_admin',
        subject='Registration',
        template_path='registration_from_admin',
		notification_type=301,
		status=1,
        type=0,
        email_body=NULL,
		message_body=NULL,
		display_label='Registration',
		date_added='".date('Y-m-d H:i:s')."',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE notification_type = 301";
		$this->db->query($sql);
        }


    }

    function down()
    {
        $this->db->where('notification_type', 301);
        $this->db->delete(EMAIL_TEMPLATE);
    
    }
}