<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Enable_phone_pay extends CI_Migration {

	public function up()
	{
	  	$sql = "UPDATE ".$this->db->dbprefix(CASHFREE_WALLET_BANK)." SET `status` = '1' WHERE `payment_code` = '4009';"; // for Phone pay (wallet)
	  	$this->db->query($sql);
	}

	public function down()
	{
		//down script
	}
}
