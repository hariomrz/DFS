<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_tds extends CI_Migration{

  public function up(){
    //add net winning field in vi_user
    $fields = array(
            'net_winning' => array(
              'type' => 'DECIMAL',
              'constraint' => '10,2',
              'default'=>'0.00',
              'after' => 'point_balance'
            )
    );
    if(!$this->db->field_exists('net_winning', USER)){
      $this->dbforge->add_column(USER,$fields);
    }

    //add tds field in vi_order
    $fields = array(
            'tds' => array(
              'type' => 'DECIMAL',
              'constraint' => '10,2',
              'default'=>'0.00',
              'after' => 'points'
            )
    );
    if(!$this->db->field_exists('tds', ORDER)){
      $this->dbforge->add_column(ORDER,$fields);
    }

    //user_tds_certificate
    if(!$this->db->table_exists(USER_TDS_CERTIFICATE))
    {
      $fields = array(
        'id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'user_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'fy' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE,
          'comment' => 'Financial Year like 2023-2024'
        ),
        'gov_id' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE,
          'collation' => 'utf8mb4_general_ci',
          'comment' => 'Government id for TDS like PAN number'
        ),
        'file_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 150,
          'null' => FALSE
        ),
        'date_added' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('id', TRUE);
      $this->dbforge->create_table(USER_TDS_CERTIFICATE,FALSE,$attributes);
    }

    //user_tds_report
    if(!$this->db->table_exists(USER_TDS_REPORT))
    {
      $fields = array(
        'id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'module_type' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 1,
          'comment' => '1-DFS,2-DFS tournament,3-Marketing Leaderboard'
        ),
        'user_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'sports_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'entity_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
          'comment' => 'cm_id or tournament_id and etc'
        ),
        'entity_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE,
          'comment' => 'cm_name or tournament name and etc'
        ),
        'scheduled_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        ),
        'total_entry' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'default'=>'0.00'
        ),
        'total_winning' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'default'=>'0.00'
        ),
        'net_winning' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'default'=>'0.00'
        ),
        'tds' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'default'=>'0.00'
        ),
        'tds_txn_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
          'comment' => 'order of tds deduction'
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0,
          'comment' => '0-Pending,1-Success'
        ),
        'date_added' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('id', TRUE);
      $this->dbforge->create_table(USER_TDS_REPORT,FALSE,$attributes);

      //add unique key
      $sql = "ALTER TABLE ".$this->db->dbprefix(USER_TDS_REPORT)." ADD UNIQUE KEY module_type(module_type,user_id,sports_id,entity_id);";
      $this->db->query($sql);
    }

    $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',130)->get()->row_array();
    if(empty($result)){
      $data_arr = array(
              'source' => '130',
              'name' => 'TDS deduction'
            );
      $this->db->insert(MASTER_SOURCE,$data_arr);
    }

    $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',535)->get()->row_array();
    if(empty($result)){
      $data_arr = array(
              'source' => '535',
              'name' => 'FY TDS settlement'
            );
      $this->db->insert(MASTER_SOURCE,$data_arr);
    }

    //existing tds record
    $tds_msg = array(
            'source' => 130,
            'en_message'      => '{{name}} Winning TDS deduction',
            'hi_message'      => '{{name}} टीडीएस कटौती जीतना',
            'guj_message'     => '{{name}} TDS કપાત જીતી',
            'fr_message'      => '{{name}} Déduction TDS gagnante',
            'ben_message'     => '{{name}} জিতে TDS কাটছাঁট',
            'pun_message'     => '{{name}} TDS ਕਟੌਤੀ ਜਿੱਤਣਾ',
            'tam_message'     => '{{name}} TDS விலக்கை வென்றது',
            'th_message'      => '{{name}} ชนะการหัก TDS',
            'kn_message'      => '{{name}} ಗೆಲ್ಲುವ TDS ಕಡಿತ',
            'ru_message'      => '{{name}} Выигрыш в вычете TDS',
            'id_message'      => '{{name}} Memenangkan pengurangan TDS',
            'tl_message'      => '{{name}} Panalong TDS deduction',
            'zh_message'      => '{{name}} 中獎 TDS 扣除',
        );
    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "130")
            ->get()
            ->row_array();
    if(!empty($row)) {
      $this->db->where('source', 130);
      $this->db->update(TRANSACTION_MESSAGES, $tds_msg);
    }else{
      $this->db->insert(TRANSACTION_MESSAGES, $tds_msg);
    }

    //FY tds settlement
    $tds_msg = array(
            'source' => 535,
            'en_message'      => '{{fy}} TDS settlement',
            'hi_message'      => '{{fy}} टीडीएस निपटान',
            'guj_message'     => '{{fy}} TDS સેટલમેન્ટ',
            'fr_message'      => '{{fy}} règlement TDS',
            'ben_message'     => '{{fy}} টিডিএস নিষ্পত্তি',
            'pun_message'     => '{{fy}} TDS ਨਿਪਟਾਰਾ',
            'tam_message'     => '{{fy}} TDS தீர்வு',
            'th_message'      => '{{fy}} การชำระ TDS',
            'kn_message'      => '{{fy}} TDS ಪರಿಹಾರ',
            'ru_message'      => '{{fy}} Расчет TDS',
            'id_message'      => '{{fy}} penyelesaian TDS',
            'tl_message'      => '{{fy}} TDS settlement',
            'zh_message'      => '{{fy}} TDS 結算',
        );
    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', 535)
            ->get()
            ->row_array();
    if(!empty($row)) {
      $this->db->where('source', 535);
      $this->db->update(TRANSACTION_MESSAGES, $tds_msg);
    }else{
      $this->db->insert(TRANSACTION_MESSAGES, $tds_msg);
    }
  }

  public function down(){
    //down script
  }
}
