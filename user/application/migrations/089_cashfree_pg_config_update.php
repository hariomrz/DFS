<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cashfree_pg_config_update extends CI_Migration {

	public function up()
	{
		$sql = "UPDATE ".$this->db->dbprefix(CASHFREE_WALLET_BANK)." SET `status` = '0' WHERE `payment_code` = '4008';"; // for Amazon pay (wallet)
	  	$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CASHFREE_WALLET_BANK)." SET `status` = '0' WHERE `payment_code` = '3021';"; // for HDFC bank (BANK)
	  	$this->db->query($sql);
	}

	public function down()
	{
		//down script
	}
}
