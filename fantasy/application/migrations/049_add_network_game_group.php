<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_network_game_group extends CI_Migration {

	public function up() {
		//add hall of fame group
		$insert_data = array(
						"group_name" => "Network Game",
						"description" => "Enter the hottest network contest with mega prizes.",
						"icon" => "network_games.png",
						"is_private" => 0,
						"sort_order" => 14,
						"status" => 1
					);
		$this->db->insert(MASTER_GROUP,$insert_data);
	}

	public function down() {
		// $this->db->delete(MASTER_GROUP,array("group_name" => "Network Game"));
	}

}