<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Game_stats_changes extends CI_Migration {

  public function up()
  {
      //up script
      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_CRICKET)." ADD INDEX `season_game_uid` (`season_game_uid`) USING BTREE;";
      $this->db->query($sql);

      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_SOCCER)." ADD INDEX `season_game_uid` (`season_game_uid`) USING BTREE;";
      $this->db->query($sql);

      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_KABADDI)." ADD INDEX `season_game_uid` (`season_game_uid`) USING BTREE;";
      $this->db->query($sql);

      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_BASEBALL)." ADD INDEX `season_game_uid` (`season_game_uid`) USING BTREE;";
      $this->db->query($sql);
  }

  public function down()
  {
     //down script
     $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_CRICKET)." DROP INDEX `season_game_uid`;";
     $this->db->query($sql);

     $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_SOCCER)." DROP INDEX `season_game_uid`;";
     $this->db->query($sql);

     $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_KABADDI)." DROP INDEX `season_game_uid`;";
     $this->db->query($sql);

     $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_BASEBALL)." DROP INDEX `season_game_uid`;";
     $this->db->query($sql);
  }
}