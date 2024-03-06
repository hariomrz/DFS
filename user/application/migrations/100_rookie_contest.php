<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Rookie_contest extends CI_Migration {

  public function up()
  {

    $row = $this->db->select('key_name')
    ->from(APP_CONFIG)
    ->where('key_name','allow_rookie_contest')
    ->get()
    ->row_array();

    if(empty($row))
    {
      $this->db->insert(APP_CONFIG,array(
        'name' => "Allow rookie Contest",
        'key_name'=>"allow_rookie_contest",
        'key_value' => 0,
        'custom_data'=> json_encode(array('winning_amount' => 1000,'month_number'=> 6,"group_id"=> ""))
    ));
    }
  
  }
}
