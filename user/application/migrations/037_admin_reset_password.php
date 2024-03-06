<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Admin_reset_password extends CI_Migration {

  public function up()
  {
        $admin_reset_password_template = 
                array(
                    array(
                        'notification_type' => 404,
                        'template_name'=> 'admin-reset-password',
                        'subject' => 'Reset Password',
                        'template_path' => 'admin_reset_password',
                        'status' => 1,
                        'type' => 0,
                        'display_label' => 'Reset Password',
                        'date_added' => '2020-01-08 03:15:41'
                    )
                );
        $this->db->insert_batch(EMAIL_TEMPLATE,$admin_reset_password_template);
  }

  public function down()
  {
	//down script 
    $this->db->where_in('notification_type', array(404));
	$this->db->delete(EMAIL_TEMPLATE);
  }
}
