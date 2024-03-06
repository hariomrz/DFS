<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Openpredictor_proof_changes extends CI_Migration {
    function __construct()
    {
        $this->db_open_prediction =$this->load->database('open_predictor_db',TRUE);
        $this->open_predictor_forge = $this->load->dbforge($this->db_open_prediction, TRUE);
    }

    public function up()
    {
        $fields = array(
            'source_url' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
            ),
            'source_desc' => array(
              'type' => 'VARCHAR',
              'constraint' => 255,
              'null' => TRUE,
              'default' => NULL
            ),
            'proof_desc' => array(
              'type' => 'VARCHAR',
              'constraint' => 255,
              'null' => TRUE,
              'default' => NULL
            ),
            'proof_image' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
              )
            );
    
            $this->open_predictor_forge->add_column(PREDICTION_MASTER, $fields); 
    }

    function down()
    {
      $this->open_predictor_forge->drop_column(PREDICTION_MASTER, 'source_url');
      $this->open_predictor_forge->drop_column(PREDICTION_MASTER, 'source_desc');
      $this->open_predictor_forge->drop_column(PREDICTION_MASTER, 'proof_desc');
      $this->open_predictor_forge->drop_column(PREDICTION_MASTER, 'proof_image');

    }

}