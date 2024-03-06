<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cashfree_reorder extends CI_Migration {

	public function up()
	{
		$sql = "UPDATE ".$this->db->dbprefix(CASHFREE_WALLET_BANK)." SET `payment_option` = 'PhonePe', `payment_code` = '4009' WHERE `id` = '8';"; // for phone pay
	  	$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CASHFREE_WALLET_BANK)." SET `payment_option` = 'Airtel Money', `payment_code` = '4006' WHERE `id` = '3';"; // for Airtel money
	  	$this->db->query($sql);
	  	
	  	$sql = "UPDATE ".$this->db->dbprefix(CASHFREE_WALLET_BANK)." SET `status` = '0' WHERE `payment_code` = '4009';"; // for Phone pay (wallet)
	  	$this->db->query($sql);

	}

	public function down()
	{
		//down script
	}
}
