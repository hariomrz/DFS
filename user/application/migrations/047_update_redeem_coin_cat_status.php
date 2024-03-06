<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_redeem_coin_cat_status extends CI_Migration {

    public function up() {
        $sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_CATEGORY)." SET status = '0' WHERE category_id in('13','14');";
        $this->db->query($sql);
        
        // $check = "SELECT count(template_name) as countt FROM ".$this->db->dbprefix(EMAIL_TEMPLATE)." WHERE notification_type=136";
        // $result = $this->db->query($check);
        $result = $this->db->select('*')->from(EMAIL_TEMPLATE)->where('notification_type',136)->get()->num_rows();
        if(!$result){
            $sql = "INSERT INTO ".$this->db->dbprefix(EMAIL_TEMPLATE)." (template_name, subject, template_path, notification_type, status, type, email_body, message_body, display_label, date_added, modified_date) VALUES
            ('admin-bank-reject', 'Bank Document Reject', 'admin-bank-reject', 136, 1, 0, NULL, NULL, 'Bank Document Reject', NULL, NULL);";
            $this->db->query($sql);
        }
    }

    public function down(){
        
    }
}