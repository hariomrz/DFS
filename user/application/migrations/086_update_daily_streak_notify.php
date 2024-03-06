<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_daily_streak_notify extends CI_Migration 
{
  
  public function up()
  {
        $sql = "UPDATE 
        ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)."
        SET 
            en_message = 'You have received {{amount}} coins for Daily Check-In Day {{day_number}}',
            message = 'You have received {{amount}} coins for Daily Check-In Day {{day_number}}'
        WHERE 
        notification_type ='138';";
        $this->db->query($sql);
  }
}