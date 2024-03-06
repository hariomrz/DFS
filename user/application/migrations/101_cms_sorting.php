<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cms_sorting extends CI_Migration {

    public function up() {
        $sort = array(
                'sort_order' => array(
                'type' => 'INT',
                'constraint' => 11,
				'default' => null,
			),
		);
        $this->dbforge->add_column(CMS_PAGES, $sort);
        //UPDATE `vi_cms_pages` SET `modified_by` = '1' WHERE `vi_cms_pages`.`page_id` = 1;
        
        $sql = "UPDATE ".$this->db->dbprefix(CMS_PAGES)." SET sort_order=0 where page_alias='about';";
        $this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(CMS_PAGES)." SET sort_order=1 where page_alias='faq';";
        $this->db->query($sql);
          
        $sql = "UPDATE ".$this->db->dbprefix(CMS_PAGES)." SET sort_order=2 where page_alias='terms_of_use';";
        $this->db->query($sql);
        
        $sql = "UPDATE ".$this->db->dbprefix(CMS_PAGES)." SET sort_order=3 where page_alias='rules_and_scoring';";
        $this->db->query($sql);
          
        $sql = "UPDATE ".$this->db->dbprefix(CMS_PAGES)." SET sort_order=4 where page_alias='privacy_policy';";
        $this->db->query($sql);
        
        $sql = "UPDATE ".$this->db->dbprefix(CMS_PAGES)." SET sort_order=5 where page_alias='contact_us';";
        $this->db->query($sql);
        
        $sql = "UPDATE ".$this->db->dbprefix(CMS_PAGES)." SET sort_order=6 where page_alias='legality';";
        $this->db->query($sql);
        
        $sql = "UPDATE ".$this->db->dbprefix(CMS_PAGES)." SET sort_order=7 where page_alias='offers';";
        $this->db->query($sql);
          
        $sql = "UPDATE ".$this->db->dbprefix(CMS_PAGES)." SET sort_order=8 where page_alias='refund_policy';";
        $this->db->query($sql);
        
        $sql = "UPDATE ".$this->db->dbprefix(CMS_PAGES)." SET sort_order=9 where page_alias='how_it_works';";
	  	$this->db->query($sql);
    }

    public function down()
    {

    }

}
