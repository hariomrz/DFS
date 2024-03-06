<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Prediction_feed_changes extends CI_Migration {
	function __construct()
	{
		$this->db_prediction =$this->load->database('prediction_db',TRUE);
		$this->prediction_forge = $this->load->dbforge($this->db_prediction, TRUE);
	}
	public function up()
	{
		$fields = array(
            'is_prediction_feed' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => FALSE,
                'default' => 0,
                'comment'=>'1=>Feed Published,0=>Internal only'
            	)
            );
    	if(!$this->db_prediction->field_exists('is_prediction_feed', PREDICTION_MASTER)){
          	$this->prediction_forge->add_column(PREDICTION_MASTER, $fields); 

        }

        $fields = array(
            'feed_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
                'default' => 0,
                'comment'=>'Reference Id for feed question publish'
            	)
            );
    	if(!$this->db_prediction->field_exists('feed_id', PREDICTION_MASTER)){
          	$this->prediction_forge->add_column(PREDICTION_MASTER, $fields); 

        }

        $fields = array(
            'feed_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
                'default' => 0,
                'comment'=>'Reference Id for feed answer publish'
            	)
            );
    	if(!$this->db_prediction->field_exists('feed_id', PREDICTION_OPTION)){
          	$this->prediction_forge->add_column(PREDICTION_OPTION, $fields); 

        }

	}

    public function down()
    {
            //$this->prediction_forge->drop_column(PREDICTION_MASTER, 'is_prediction_feed');
            //$this->prediction_forge->drop_column(PREDICTION_MASTER, 'feed_id');
            //$this->prediction_forge->drop_column(PREDICTION_OPTION, 'feed_id');
    }

}