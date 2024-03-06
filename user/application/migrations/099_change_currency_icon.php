

<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Change_currency_icon extends CI_Migration {

    public function up() {

        $sql = "UPDATE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." SET 
        message=REPLACE(message,'₹','{{currency}}'),
        pun_subject=REPLACE(pun_subject,'₹','{{currency}}'), 
        ben_subject=REPLACE(ben_subject,'₹','{{currency}}'), 
        fr_subject=REPLACE(fr_subject,'₹','{{currency}}'), 
        guj_subject=REPLACE(guj_subject,'₹','{{currency}}'), 
        hi_subject=REPLACE(hi_subject,'₹','{{currency}}'), 
        en_subject=REPLACE(en_subject,'₹','{{currency}}'), 
        th_subject=REPLACE(th_subject,'₹','{{currency}}'), 
        en_message=REPLACE(en_message,'₹','{{currency}}'), 
        hi_message=REPLACE(hi_message,'₹','{{currency}}'), 
        guj_message=REPLACE(guj_message,'₹','{{currency}}'), 
        fr_message=REPLACE(fr_message,'₹','{{currency}}'), 
        ben_message=REPLACE(ben_message,'₹','{{currency}}'), 
        pun_message=REPLACE(pun_message,'₹','{{currency}}'), 
        tam_message=REPLACE(tam_message,'₹','{{currency}}'), 
        th_message=REPLACE(th_message,'₹','{{currency}}'), 
        kn_message=REPLACE(kn_message,'₹','{{currency}}'), 
        kn_subject=REPLACE(kn_subject,'₹','{{currency}}'), 
        ru_message=REPLACE(ru_message,'₹','{{currency}}'), 
        ru_subject=REPLACE(ru_subject,'₹','{{currency}}'), 
        id_message=REPLACE(id_message,'₹','{{currency}}'), 
        id_subject=REPLACE(id_subject,'₹','{{currency}}'), 
        tl_message=REPLACE(tl_message,'₹','{{currency}}'), 
        tl_subject=REPLACE(tl_subject,'₹','{{currency}}'), 
        zh_message=REPLACE(zh_message,'₹','{{currency}}'), 
        zh_subject=REPLACE(zh_subject,'₹','{{currency}}'); ";
        $this->db->query($sql);
    }

    public function down() {
      //down script 
    }

}
