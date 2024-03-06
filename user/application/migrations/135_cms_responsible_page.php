<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cms_responsible_page extends CI_Migration {

    public function up() {
        $field = array (
            array(
            'page_title' => 'responsible',
            'page_alias' => 'responsible',
            'meta_keyword' => 'responsible',
            'page_url' => 'responsible',
            'en_page_title'=>'responsible',
            'status' =>'0',
            'sort_order'=>'11',           
            ),
    );
    $this->db->insert_batch(CMS_PAGES,$field);   
        
    }

    public function down()
    {

    }

}