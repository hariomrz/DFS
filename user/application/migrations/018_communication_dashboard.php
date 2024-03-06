<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Communication_dashboard extends CI_Migration{

	public function up(){
		$preferences =array(
			'sport_preference_id'=>array(
				'type'=>'INT',
				'constraint'=>10,
				'auto_increment'=>TRUE,
			),
			'sports_id'=>array(
				'type'=>'INT',
				'constraint'=>10,
				'null'=>FALSE,
			),
			'min_value'=>array(
				'type'=>'TINYINT',
				'constraint'=>5,
				'null'=>TRUE,
				'default'=>NULL,
			),
			'max_value'=>array(
				'type'=>'INT',
				'constraint'=>10,
				'null'=>TRUE,
				'default'=>NULL,
			),
			'status' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'comment' => '0=>inactive,1=>Active'
			),
			'added_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE
			),
		);
		$attributes = array('ENGINE'=>'InnoDB');
		$this->dbforge->add_field($preferences);
		$this->dbforge->add_key('sport_preference_id', TRUE);
		$this->dbforge->create_table(CD_SPORTS_PREFERENCE,FALSE,$attributes);

		$user_base_list=array(
			'user_base_list_id'=>array(
				'type'=>'INT',
				'constraint'=>10,
				'auto_increment'=>TRUE,
			),
			'list_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 40,
				'null' => FALSE,
			),
			'sport_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 225,
				'null' => FALSE,
			),
			'location' => array(
				'type' => 'VARCHAR',
				'constraint' => 225,
				'null' => TRUE,
				'default'=>NULL,
			),
			'age_group' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'profile_status' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'gender' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'admin_created_contest_join' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'admin_created_contest_won' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'admin_created_contest_lost' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'private_contest_join' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'private_contest_won' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'private_contest_lost' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'money_deposit' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'money_won' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'money_lost' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'coin_earn' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'coin_lost' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'coin_redeem' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'referral' => array(
				'type' => 'JSON',
				'null' => TRUE,
				'default'=>NULL,
			),
			'count' => array(
				'type' => 'INT',
				'constraint' => 10,
				'null' => TRUE,
				'default'=>NULL,
			),
			'user_ids' => array(
				'type' => 'TEXT',
				'null' => TRUE,
				'default'=>NULL,
			),
			'status' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default'=>1,
				'comment' => '0=>inactive,1=>Active'
			),
			'added_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE
			),
		);
		$attributes = array('ENGINE'=>'InnoDB');
		$this->dbforge->add_field($user_base_list);
		$this->dbforge->add_key('user_base_list_id', TRUE);
		$this->dbforge->create_table(CD_USER_BASED_LIST,FALSE,$attributes);

		$category =array(
			'category_id'=>array(
				'type'=>'INT',
				'constraint'=>10,
				'auto_increment'=>TRUE,
			),
			'category_name'=>array(
				'type'=>'VARCHAR',
				'constraint'=>30,
				'null'=>FALSE,
			),
			'status' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default'=>1,
				'comment' => '0=>inactive,1=>Active',
			),
			'added_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE,
			),
		);
		$attributes = array('ENGINE'=>'InnoDB');
		$this->dbforge->add_field($category);
		$this->dbforge->add_key('category_id', TRUE);
		$this->dbforge->create_table(CD_EMAIL_CATEGORY,FALSE,$attributes);

		$categories = array(
			array(
				'category_id'		=> 1,
				'category_name'		=> "Promotion for Deposit",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 2,
				'category_name'		=> "Promotion for Contest",
				'status'			=> 0,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 3,
				'category_name'		=> "Refer a friend",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 4,
				'category_name'		=> "Promotion for Fixture",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 5,
				'category_name'		=> "SMS buy Notification",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 6,
				'category_name'		=> "Notification buy Notification",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 7,
				'category_name'		=> "Fixture Delay",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 8,
				'category_name'		=> "Lineup Announced",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 9,
				'category_name'		=> "Custom SMS",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 10,
				'category_name'		=> "Custom Notification",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 11,
				'category_name'		=> "Email buy Notification",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 12,
				'category_name'		=> "Daily login earn coins",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			),
			array(
				'category_id'		=> 13,
				'category_name'		=> "Redeem coins",
				'status'			=> 1,
				'added_date'		=> format_date('today'),
			)
		);
		$this->db->insert_batch(CD_EMAIL_CATEGORY,$categories);

		$template_field = array(
			      'message_type'=>array(
				  'type'=>'TINYINT',
				  'constraint'=>5,
				  'after'=>'message_body',
				  'comment' => "1=> message,2=> notification,0=>default templtes"
				  ),
				  'category_id'=>array(
				  'type'=>'INT',
				  'constraint'=>10,
				  'after'=>'cd_email_template_id',
				  ),
				  'message_url'=>array(
				  'type'=>'VARCHAR',
				  'constraint'=>50,
				  'after'=>'message_type',
				  ),
				  'redirect_to'=>array(
				  'type'=>'VARCHAR',
				  'constraint'=>50,
				  'after'=>'message_url',
				  ),
			); 
		$this->dbforge->add_column(CD_EMAIL_TEMPLATE,$template_field);

		$template_field = array(
			'user_base_list_id'=>array(
				'type'=>'INT',
				'constraint'=>11,
				'default' => NULL
				),
	  ); 
  		$this->dbforge->add_column(CD_RECENT_COMMUNICATION,$template_field);
	}

	public function down(){
		$this->dbforge->drop_table(CD_SPORTS_PREFERENCE);
		$this->dbforge->drop_table(CD_USER_BASED_LIST);
		$this->dbforge->drop_table(CD_EMAIL_CATEGORY);
		$this->dbforge->drop_column(CD_EMAIL_TEMPLATE, 'message_type');
		$this->dbforge->drop_column(CD_EMAIL_TEMPLATE, 'category_id');
		$this->dbforge->drop_column(CD_EMAIL_TEMPLATE, 'message_url');
		$this->dbforge->drop_column(CD_EMAIL_TEMPLATE, 'redirect_to');
	}
}