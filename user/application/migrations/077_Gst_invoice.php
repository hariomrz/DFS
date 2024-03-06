<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Gst_invoice extends CI_Migration {

  public function up()
  {
        $admin_reset_password_template = 
                array(
                    array(
                        'notification_type' => 424,
                        'template_name'=> 'gst-invoice',
                        'subject' => 'Gst Invoice',
                        'template_path' => 'gst-invoice',
                        'status' => 1,
                        'type' => 0,
                        'display_label' => 'Gst Invoice',
                        'date_added' => '2020-12-02 03:15:41'
                    )
                );
        $this->db->insert_batch(EMAIL_TEMPLATE,$admin_reset_password_template);
  }

  public function down()
  {
	//down script 
    $this->db->where_in('notification_type', array(423));
	$this->db->delete(EMAIL_TEMPLATE);
  }
}
