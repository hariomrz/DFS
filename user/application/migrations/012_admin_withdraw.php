<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Admin_withdraw extends CI_Migration {

  public function up()
  {
  	    $notification_description = 
                    array(
                        'notification_type' => 184,
                        'template_name'=> 'admin-fund-withdraw',
                        'template_path'=> 'admin-fund-withdraw',
                        'status' => '1',
                        'display_label' => 'Admin Withdraw',
                        'subject' => 'Admin Withdrawal'
                      );
        $this->db->insert(EMAIL_TEMPLATE,$notification_description);
  }

  public function down()
  {
	//down script  
	$this->db->where_in('notification_type', array(184));
	$this->db->delete(EMAIL_TEMPLATE);
  }
}
