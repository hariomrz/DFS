<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Hub_page_banner extends CI_Migration {

    public function up() {

    $banners = 
    array(
        array(
            'banner_type_id' => 7,
            'banner_type'=> "Sports Hub Featured",
            'status' => 1,
        ),array(
            'banner_type_id' => 8,
            'banner_type'=> "Sports Hub Ads",
            'status' => 1,
        ),
    );
    $this->db->insert_batch(BANNER_TYPE,$banners);

    }
    public function down()
    {
        $this->dbforge->drop_column(SPORTS_HUB, 'game_type');
    }
}