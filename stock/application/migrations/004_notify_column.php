
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Notify_column extends CI_Migration {

    public function up() {

        
        $check = "SELECT count(*) AS count FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'vi_collection' AND table_schema = 'fw_stock' AND column_name = 'is_notified'";
        $check_res = $this->db->query($check)->row_array();
        if($check_res['count']==0)
        {
            $sql = "ALTER TABLE ".$this->db->dbprefix(COLLECTION)." ADD is_notified TINYINT(1) NOT NULL DEFAULT '0';";
            $this->db->query($sql);
        }
    }

    function down()
    {
        
    }
}