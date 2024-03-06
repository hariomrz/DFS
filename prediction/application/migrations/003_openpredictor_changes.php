<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Openpredictor_changes extends CI_Migration {
    function __construct()
    {
        $this->db_open_prediction =$this->load->database('open_predictor_db',TRUE);
        $this->open_predictor_forge = $this->load->dbforge($this->db_open_prediction, TRUE);
    }

    public function up()
    {
        $fields = array(
            'entry_type' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => FALSE,
                'comment' => '0=>pool,1=>fixed',
                'default' => 0
            ),
            'entry_fee' => array(
              'type' => 'INT',
              'constraint' => 10,
              'null' => FALSE,
              'comment' => 'entry fee in coins in case of entry_type pool',
              'default' => 0
            ),
            'win_prize' => array(
              'type' => 'INT',
              'constraint' => 11,
              'null' => FALSE,
              'comment' => 'in case of entry_type pool winning prize'
            ),
            );
    
            $this->open_predictor_forge->add_column(PREDICTION_MASTER, $fields); 
    }

    function down()
    {
      $this->open_predictor_forge->drop_column(PREDICTION_MASTER, 'entry_type');
      $this->open_predictor_forge->drop_column(PREDICTION_MASTER, 'entry_fee');
      $this->open_predictor_forge->drop_column(PREDICTION_MASTER, 'win_prize');

    }

}
