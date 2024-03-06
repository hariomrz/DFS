<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Stock_fantasy extends CI_Migration {

    function __construct() {
      $this->db_stock =$this->load->database('stock_db',TRUE);
      $this->stock_forge = $this->load->dbforge($this->db_stock, TRUE);
    }

    public function up() {
        $fields = array(
                    'market_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),             
                    'name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'null' => FALSE
                    ),                     
                    'config_data' => array(
                        'type' => 'json',
                        'null' => TRUE,
                        'default' => NULL
                    ),
                    'status' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 1,
                        'null' => FALSE,
                        'comment' => '0 - In Active, 1 - Active'
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    )
                );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('market_id',TRUE);
        $this->stock_forge->create_table(MARKET ,FALSE,$attributes); 
    
        $data = array(
            'name' => 'NSE',
            'config_data' => json_encode(array(
                                'tc' => 10,
                                'b' => 10,
                                's' => 10,
                            )),
            'added_date' => format_date('today')
        );

        $this->db_stock->insert(MARKET,$data);

        $fields = array(
                    'category_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),             
                    'name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'en_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'hi_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'guj_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'fr_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'ben_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'pun_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'tam_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'th_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'ru_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'id_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'tl_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'zh_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'kn_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    )
                    );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('category_id',TRUE);
        $this->stock_forge->create_table(CONTEST_CATEGORY ,FALSE,$attributes);
    
        $data = array(
                        array(
                            'category_id'   => 1,
                            'name'		    => "Daily",
                            'en_name'       => "Daily Stock Fantasy",
                            'hi_name'       => "काल्पनिक दैनिक स्टॉक",
                            'guj_name'      => 'દૈનિક સ્ટોક ફantન્ટેસી',
                            'fr_name'       => "Fantaisie quotidienne des actions",
                            'ben_name'      => 'দৈনিক স্টক ফ্যান্টাসি',
                            'pun_name'      => "ਰੋਜ਼ਾਨਾ ਸਟਾਕ ਕਲਪਨਾ",
                            'tam_name'      => 'டெய்லி ஸ்டாக் பேண்டஸி',
                            'th_name'       => 'แฟนตาซีหุ้นรายวัน',
                            'ru_name'       => 'Ежедневная фантазия о запасах',
                            'id_name'       => 'Fantasi Stok Harian',
                            'tl_name'       => 'డైలీ స్టాక్ ఫాంటసీ',
                            'zh_name'       => '每日股票幻想',
                            'kn_name'       => 'ಡೈಲಿ ಸ್ಟಾಕ್ ಫ್ಯಾಂಟಸಿ',
                            'added_date'	=> format_date('today'),
                        ),
                        array(
                            'category_id'   => 2,
                            'name'		    => "Weekly",
                            'en_name'       => "Weekly Stock Fantasy",
                            'hi_name'       => "काल्पनिक साप्ताहिक स्टॉक",
                            'guj_name'      => 'સાપ્તાહિક સ્ટોક ફantન્ટેસી',
                            'fr_name'       => "Stock Fantasy hebdomadaire",
                            'ben_name'      => 'সাপ্তাহিক স্টক ফ্যান্টাসি',
                            'pun_name'      => "ਹਫਤਾਵਾਰੀ ਸਟਾਕ ਕਲਪਨਾ",
                            'tam_name'      => 'வாராந்திர பங்கு பேண்டஸி',
                            'th_name'       => 'แฟนตาซีหุ้นรายสัปดาห์',
                            'ru_name'       => 'Еженедельная фондовая фантазия',
                            'id_name'       => 'Fantasi Saham Mingguan',
                            'tl_name'       => 'వీక్లీ స్టాక్ ఫాంటసీ',
                            'zh_name'       => '每週股票幻想',
                            'kn_name'       => 'ಸಾಪ್ತಾಹಿಕ ಸ್ಟಾಕ್ ಫ್ಯಾಂಟಸಿ',
                            'added_date'	=> format_date('today'),
                        ),
                        array(
                            'category_id'   => 3,
                            'name'		    => "Monthly",
                            'en_name'       => "Monthly Stock Fantasy",
                            'hi_name'       => "काल्पनिक मासिक स्टॉक",
                            'guj_name'      => 'માસિક સ્ટોક ફantન્ટેસી',
                            'fr_name'       => "Fantaisie d'actions mensuelles",
                            'ben_name'      => 'মাসিক স্টক ফ্যান্টাসি',
                            'pun_name'      => "ਮਾਸਿਕ ਸਟਾਕ ਕਲਪਨਾ",
                            'tam_name'      => 'மாத பங்கு பேண்டஸி',
                            'th_name'       => 'แฟนตาซีหุ้นรายเดือน',
                            'ru_name'       => 'Ежемесячная фондовая фантазия',
                            'id_name'       => 'Fantasi Saham Bulanan',
                            'tl_name'       => 'మంత్లీ స్టాక్ ఫాంటసీ',
                            'zh_name'       => '每月股票幻想',
                            'kn_name'       => 'ಮಾಸಿಕ ಸ್ಟಾಕ್ ಫ್ಯಾಂಟಸಿ',
                            'added_date'	=> format_date('today'),
                        )
                    );
        
        $this->db_stock->insert_batch(CONTEST_CATEGORY,$data);            

        $fields = array(
                    'group_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),             
                    'group_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 150,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),             
                    'description' => array(
                        'type' => 'text',
                        'default'=>NULL,
                    ),
                    'icon' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'default'=>NULL,
                        'null' => TRUE
                    ),
                    'is_private' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - No , 1 - Yes'
                    ),
                    'sort_order' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'null' => FALSE
                    ),
                    'status' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 1,
                        'null' => FALSE,
                        'comment' => '0 - In Active, 1 - Active'
                    )
                    );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('group_id',TRUE);
        $this->stock_forge->create_table(MASTER_GROUP ,FALSE,$attributes);

        $fields = array(
                    'master_stock_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),  
                    'market_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),     
                    'exchange_token' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'default' => NULL,
                        'null' => TRUE
                    ),     
                    'instrument_token' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'default' => NULL,
                        'null' => TRUE,
                        'comment' => 'Numerical identifier used to get historical data'
                    ),      
                    'name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'default' => NULL,
                        'null' => TRUE
                    ),   
                    'trading_symbol' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 150,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),                      
                    'lot_size' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'null' => FALSE
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    )
            );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (market_id) REFERENCES '.$this->db_stock->dbprefix(MARKET).' (market_id) ON DELETE CASCADE');
        $this->stock_forge->add_key('master_stock_id',TRUE);
        $this->stock_forge->create_table(MASTER_STOCK ,FALSE,$attributes);
        
        $sql="ALTER TABLE ".$this->db_stock->dbprefix(MASTER_STOCK)." ADD UNIQUE KEY `master_stock` (`market_id`,`trading_symbol`);";
	  	$this->db_stock->query($sql);

        $fields = array(
                    'stock_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),  
                    'market_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),     
                    'exchange_token' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'default' => NULL,
                        'null' => TRUE
                    ),     
                    'instrument_token' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'default' => NULL,
                        'null' => TRUE,
                        'comment' => 'Numerical identifier used to get historical data'
                    ),      
                    'name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'default' => NULL,
                        'null' => TRUE
                    ),  
                    'display_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 150,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),    
                    'trading_symbol' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 150,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),    
                    'logo' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'default' => NULL,
                        'null' => TRUE
                    ),
                    'last_price' => array(
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default'=>'0.00',
                        'null' => FALSE,
                        'comment' => 'Last traded market price'
                    ),
                    'open_price' => array(
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default'=>'0.00',
                        'null' => FALSE,
                        'comment' => 'Price at market opening'
                    ),  
                    'high_price' => array(
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default'=>'0.00',
                        'null' => FALSE,
                        'comment' => 'Highest price today'
                    ),  
                    'low_price' => array(
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default'=>'0.00',
                        'null' => FALSE,
                        'comment' => 'Lowest price today'
                    ), 
                    'lot_size' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'null' => FALSE
                    ),
                    'status' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 1,
                        'null' => FALSE,
                        'comment' => '0 - In-Active, 1 - Active'
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    ),
                    'modified_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    ),
                    'price_updated_at' => array(
                        'type' => 'DATETIME',
                        'default' => NULL,
                        'null' => TRUE 
                    )
                );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (market_id) REFERENCES '.$this->db_stock->dbprefix(MARKET).' (market_id) ON DELETE CASCADE');
        $this->stock_forge->add_key('stock_id',TRUE);
        $this->stock_forge->create_table(STOCK ,FALSE,$attributes);

        $fields = array(
                    'history_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),
                    'schedule_date' => array(
                        'type' => 'DATE',
                        'null' => FALSE 
                    ),  
                    'stock_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),
                    'open_price' => array(
                        'type' => 'DECIMAL',
					    'constraint' => '10,2',
                        'default'=>'0.00',
                        'null' => FALSE,
                        'comment' => 'Price at market opening'
                    ),  
                    'high_price' => array(
                        'type' => 'DECIMAL',
					    'constraint' => '10,2',
                        'default'=>'0.00',
                        'null' => FALSE,
                        'comment' => 'Highest price today'
                    ),  
                    'low_price' => array(
                        'type' => 'DECIMAL',
					    'constraint' => '10,2',
                        'default'=>'0.00',
                        'null' => FALSE,
                        'comment' => 'Lowest price today'
                    ),  
                    'close_price' => array(
                        'type' => 'DECIMAL',
					    'constraint' => '10,2',
                        'default'=>'0.00',
                        'null' => FALSE,
                        'comment' => 'Closing price of the instrument from the last trading day'
                    ), 
                    'volume' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => 'Total Volume traded on this day'
                    ),
                    'status' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - feed prize, 1 - Prize updated by admin and approved, 2 - Final prize approved by admin'
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    )
                );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (stock_id) REFERENCES '.$this->db_stock->dbprefix(STOCK).' (stock_id) ON DELETE CASCADE');
        $this->stock_forge->add_key('history_id',TRUE);
        $this->stock_forge->create_table(STOCK_HISTORY ,FALSE,$attributes);

        $sql="ALTER TABLE ".$this->db_stock->dbprefix(STOCK_HISTORY)." ADD UNIQUE KEY `stock_history` (`stock_id`,`schedule_date`);";
	  	$this->db_stock->query($sql);

        
        $fields = array(
            'history_detail_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),
            'schedule_date' => array(
                'type' => 'DATETIME',
                'null' => FALSE 
            ), 
            'schedule_date_utc' => array(
                'type' => 'DATETIME',
                'null' => FALSE 
            ),   
            'stock_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),  
            'close_price' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default'=>'0.00',
                'null' => FALSE,
                'comment' => 'Closing price of the instrument from the last trading day'
            ),
            'status' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'comment' => '0-Feed Price, 1- Price updated by Admin'
            ),
            'added_date' => array(
                'type' => 'DATETIME',
                'null' => FALSE 
            ),
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (stock_id) REFERENCES '.$this->db_stock->dbprefix(STOCK).' (stock_id) ON DELETE CASCADE');
        $this->stock_forge->add_key('history_detail_id',TRUE);
        $this->stock_forge->create_table(STOCK_HISTORY_DETAILS ,FALSE,$attributes);

        $sql="ALTER TABLE ".$this->db_stock->dbprefix(STOCK_HISTORY_DETAILS)." ADD UNIQUE KEY `stock_history_details` (`stock_id`,`schedule_date`);";
        $this->db_stock->query($sql);

        $sql = "ALTER TABLE ".$this->db_stock->dbprefix(STOCK_HISTORY_DETAILS)." ADD INDEX(`stock_id`)";
        $this->db->query($sql);
        $sql = "ALTER TABLE ".$this->db_stock->dbprefix(STOCK_HISTORY_DETAILS)." ADD INDEX(`schedule_date_utc`)";
        $this->db->query($sql);

        $fields = array(
                    'template_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),
                    'group_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),  
                    'contest_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),  
                    'contest_title' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'default' => 0,
                        'null' => TRUE
                    ),             
                    'contest_description' => array(
                        'type' => 'text',
                        'default'=>NULL,
                    ), 
                    'minimum_size' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'null' => FALSE
                    ), 
                    'size' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'null' => FALSE
                    ), 
                    'site_rake' => array(
                        'type' => 'FLOAT',
                        'default' => 0,
                        'null' => FALSE
                    ),
                    'multiple_lineup' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0-No multi lineup, Enter no for multiple lineup allowed to user in this game'
                    ),
                    'is_auto_recurring' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - No, 1 - Yes'
                    ),
                    'currency_type' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 1,
                        'null' => FALSE,
                        'comment' => '0 - bonus, 1 - real amount, 2 - points'
                    ),
                    'max_bonus_allowed' => array(
                        'type' => 'TINYINT',
                        'constraint' => 4,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => 'max bonus entry allowed in percentage'
                    ),  
                    'entry_fee' => array(
                        'type' => 'FLOAT',
                        'null' => FALSE
                    ),  
                    'prize_pool' => array(
                        'type' => 'FLOAT',
                        'default' => 0,
                        'null' => FALSE
                    ),
                    'prize_type' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - bonus, 1 - real amount, 2 - points'
                    ),
                    'is_custom_prize_pool' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - Auto, 1 - custom'
                    ),  
                    'prize_pool' => array(
                        'type' => 'FLOAT',
                        'default' => 0,
                        'null' => FALSE
                    ),
                    'guaranteed_prize' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - No guarantee, 1 - Custom prize, 2 - Guaranteed'
                    ),             
                    'prize_distibution_detail' => array(
                        'type' => 'json',
                        'null' => TRUE,
                        'default' => NULL
                    ),
                    'prize_value_type' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - Fixed, 1 - Percentage'
                    ),
                    'is_tie_breaker' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - No, 1 - Yes'
                    ),  
                    'sponsor_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'default' => NULL,
                        'null' => TRUE
                    ),  
                    'sponsor_logo' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'default' => NULL,
                        'null' => TRUE
                    ),  
                    'sponsor_link' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'default' => NULL,
                        'null' => TRUE
                    ),  
                    'sponsor_contest_dtl_image' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'default' => NULL,
                        'null' => TRUE
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    ),
                    'modified_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    )
                    );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (group_id) REFERENCES '.$this->db_stock->dbprefix(MASTER_GROUP).' (group_id) ON DELETE CASCADE');
        $this->stock_forge->add_key('template_id',TRUE);
        $this->stock_forge->create_table(CONTEST_TEMPLATE ,FALSE,$attributes);
        
        $fields = array(
                    'template_category_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ), 
                    'template_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ), 
                    'category_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    )
                    );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (template_id) REFERENCES '.$this->db_stock->dbprefix(CONTEST_TEMPLATE).' (template_id) ON DELETE CASCADE');
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (category_id) REFERENCES '.$this->db_stock->dbprefix(CONTEST_CATEGORY).' (category_id) ON DELETE CASCADE');
        $this->stock_forge->add_key('template_category_id',TRUE);
        $this->stock_forge->create_table(CONTEST_TEMPLATE_CATEGORY ,FALSE,$attributes);

        $fields = array(
                    'collection_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),
                    'category_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),  
                    'name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => TRUE
                    ),
                    'published_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    ),
                    'scheduled_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    ),
                    'end_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    ),
                    'status' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - Open, 1 - Complete'
                    ),
                    'is_lineup_processed' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0-Pending, 1-Lineup moved from mongo, 2-Lineup moved to Mongo, 3-deleted from mysql'
                    ),             
                    'custom_message' => array(
                        'type' => 'text',
                        'default'=>NULL,
                    ),
                    'score_updated_date' => array(
                        'type' => 'DATETIME',
                        'default' => NULL,
                        'null' => TRUE 
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    ),
                    'modified_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    )
                );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (category_id) REFERENCES '.$this->db_stock->dbprefix(CONTEST_CATEGORY).' (category_id) ON DELETE CASCADE');
        $this->stock_forge->add_key('collection_id',TRUE);
        $this->stock_forge->create_table(COLLECTION,FALSE,$attributes);

        $fields = array(
            'collection_stock_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),
            'collection_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'stock_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),  
            'stock_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 150,
                'character_set' => 'utf8 COLLATE utf8_general_ci',
                'null' => FALSE
            ), 
            'lot_size' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => FALSE
            ),
            'open_price' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'null' => FALSE
            ),
             'close_price' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'null' => FALSE
            )

        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('collection_stock_id',TRUE);
        $this->stock_forge->create_table(COLLECTION_STOCK,FALSE,$attributes);

        $sql="ALTER TABLE ".$this->db_stock->dbprefix(COLLECTION_STOCK)." ADD UNIQUE KEY `collection_stock` (`collection_id`,`stock_id`);";
	  	$this->db_stock->query($sql);

       
        $fields = array(
                    'contest_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),  
                    'contest_unique_id' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'null' => FALSE
                    ), 
                    'collection_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ), 
                    'category_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ), 
                    'template_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default'=>NULL
                    ), 
                    'group_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),
                    'scheduled_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    ),  
                    'contest_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ),  
                    'contest_title' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'default' => 0,
                        'null' => TRUE
                    ),             
                    'contest_description' => array(
                        'type' => 'text',
                        'default'=>NULL,
                    ), 
                    'minimum_size' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'null' => FALSE
                    ), 
                    'size' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'null' => FALSE
                    ),
                    'multiple_lineup' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0-No multi lineup, Enter no for multiple lineup allowed to user in this game'
                    ),
                    'is_auto_recurring' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - No, 1 - Yes'
                    ),
                    'currency_type' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 1,
                        'null' => FALSE,
                        'comment' => '0 - bonus, 1 - real amount, 2 - points'
                    ),
                    'max_bonus_allowed' => array(
                        'type' => 'TINYINT',
                        'constraint' => 4,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => 'max bonus entry allowed in percentage'
                    ),  
                    'entry_fee' => array(
                        'type' => 'FLOAT',
                        'null' => FALSE
                    ),  
                    'site_rake' => array(
                        'type' => 'FLOAT',
                        'null' => FALSE
                    ),
                    'host_rake' => array(
                        'type' => 'FLOAT',
                        'default' => 0,
                        'null' => FALSE
                    ),
                    'prize_pool' => array(
                        'type' => 'FLOAT',
                        'default' => 0,
                        'null' => FALSE
                    ),  
                    'max_prize_pool' => array(
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => '0.00',
                        'null' => TRUE
                    ),
                    'prize_type' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - bonus, 1 - real amount, 2 - points'
                    ),
                    'is_custom_prize_pool' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - Auto, 1 - custom'
                    ),
                    'guaranteed_prize' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - No guarantee, 1 - Custom prize, 2 - Guaranteed'
                    ),             
                    'prize_distibution_detail' => array(
                        'type' => 'json',
                        'null' => TRUE,
                        'default' => NULL
                    ),             
                    'base_prize_details' => array(
                        'type' => 'json',
                        'null' => TRUE,
                        'default' => NULL
                    ),
                    'prize_value_type' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - Fixed, 1 - Percentage'
                    ),
                    'is_tie_breaker' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - No, 1 - Yes'
                    ), 
                    'total_user_joined' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'null' => FALSE
                    ),
                    'is_pin_contest' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - No, 1 - Yes'
                    ),
                    'is_win_notify' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => 'Is notification send to winner users? 0 - No, 1 - Yes'
                    ),
                    'is_rank_calculate' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - No, 1 - Yes'
                    ),
                    'is_pdf_generated' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - Pending, 1 - Pushed, 2 - Generated'
                    ), 
                    'user_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => 'user id when user create game'
                    ),
                    'status' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - Open, 1 - Cancel, 2 - Complete/Close, 3 - Prize Distributed'
                    ),
                    'contest_access_type' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0: for public game, 1: private game'
                    ),             
                    'cancel_reason' => array(
                        'type' => 'text',
                        'default' => NULL,
                    ),  
                    'sponsor_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'default' => NULL,
                        'null' => TRUE
                    ),  
                    'sponsor_logo' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'default' => NULL,
                        'null' => TRUE
                    ),  
                    'sponsor_link' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'default' => NULL,
                        'null' => TRUE
                    ),  
                    'sponsor_contest_dtl_image' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'default' => NULL,
                        'null' => TRUE
                    ),
                    'completed_date' => array(
                        'type' => 'DATETIME',
                        'default' => NULL,
                        'null' => TRUE
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    ),
                    'modified_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    )
                );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (collection_id) REFERENCES '.$this->db_stock->dbprefix(COLLECTION).' (collection_id) ON DELETE CASCADE');
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (category_id) REFERENCES '.$this->db_stock->dbprefix(CONTEST_CATEGORY).' (category_id) ON DELETE CASCADE');
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (template_id) REFERENCES '.$this->db_stock->dbprefix(CONTEST_TEMPLATE).' (template_id) ON DELETE CASCADE');
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (group_id) REFERENCES '.$this->db_stock->dbprefix(MASTER_GROUP).' (group_id) ON DELETE CASCADE');
        $this->stock_forge->add_key('contest_id',TRUE);
        $this->stock_forge->create_table(CONTEST,FALSE,$attributes);

        $fields = array(
                    'lineup_master_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ), 
                    'collection_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),
                    'user_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ), 
                    'user_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ), 
                    'team_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => FALSE
                    ), 
                    'team_short_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'character_set' => 'utf8 COLLATE utf8_general_ci',
                        'null' => TRUE,
                        'default' => NULL
                    ),  
                    'team_data' => array(
                        'type' => 'longtext',
                        'null' => TRUE
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    ),
                    'modified_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    )
                    );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (collection_id) REFERENCES '.$this->db_stock->dbprefix(COLLECTION).' (collection_id) ON DELETE CASCADE');
        $this->stock_forge->add_key('lineup_master_id',TRUE);
        $this->stock_forge->create_table(LINEUP_MASTER,FALSE,$attributes);

        $fields = array(
                    'lineup_master_contest_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ), 
                    'lineup_master_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ), 
                    'contest_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ), 
                    'total_score' => array(
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => '0.0',
                        'null' => FALSE
                    ), 
                    'game_rank' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),
                    'fee_refund' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE,
                        'comment' => '0 - No, 1 - Yes'
                    ),
                    'is_winner' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'null' => FALSE
                    ), 
                    'won_amount' => array(
                        'type' => 'FLOAT',
                        'default' => 0,
                        'null' => FALSE
                    ),  
                    'prize_data' => array(
                        'type' => 'longtext',
                        'null' => TRUE
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => FALSE 
                    )
                    );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (lineup_master_id) REFERENCES '.$this->db_stock->dbprefix(LINEUP_MASTER).' (lineup_master_id) ON DELETE CASCADE');
        $this->stock_forge->add_field('CONSTRAINT FOREIGN KEY (contest_id) REFERENCES '.$this->db_stock->dbprefix(CONTEST).' (contest_id) ON DELETE CASCADE');
        $this->stock_forge->add_key('lineup_master_contest_id',TRUE);
        $this->stock_forge->create_table(LINEUP_MASTER_CONTEST,FALSE,$attributes);

        $fields = array(
                    'merchandise_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),
                    'name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'null' => FALSE
                    ),
                    'price' => array(
                        'type' => 'FLOAT',
                        'null' => FALSE,
                        'default' => 0,
                    ),
                    'image_name' => array(
                        'type' => 'VARCHAR',
                        'constraint' => 150,
                        'default' => NULL,
                    ),
                    'status' => array(
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 1,
                        'comment' => '0 - In Active, 1 - Active'
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => TRUE,
                        'default' => NULL,
                    ),
                    'updated_date' => array(
                        'type' => 'DATETIME',
                        'null' => TRUE,
                        'default' => NULL,
                    )
                );
    
        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('merchandise_id',TRUE);
        $this->stock_forge->create_table(MERCHANDISE ,FALSE,$attributes); 

        $fields = array(
            'wishlist_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),  
            'stock_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),  
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'added_date' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'default' => NULL,
            )
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('wishlist_id',TRUE);
        $this->stock_forge->create_table(WISHLIST ,FALSE,$attributes);

        $sql="ALTER TABLE ".$this->db_stock->dbprefix(WISHLIST)." ADD UNIQUE KEY `wishlist` (`stock_id`,`user_id`);";
	  	$this->db_stock->query($sql);


        $fields = array(
            'data_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),
            'data_desc' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE
            ),
            'admin_fixed' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0=>fixed,1=>variable'
            ),
            'user_fixed' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0=>fixed,1=>variable'
            ),  
            'admin_lower_limit' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),  
            'admin_upper_limit' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),  
            'user_lower_limit' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),  
            'user_upper_limit' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'updated_date' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'default' => NULL,
            )
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('data_id',TRUE);
        $this->stock_forge->create_table(MASTER_DATA_ENTRY ,FALSE,$attributes);  

        $data = array(
            array(
                'data_id'   => 1,
                'data_desc'		    => "entry_fee",
                'admin_fixed'       => "1",
                'user_fixed'        => "1",
                'admin_lower_limit' => '200',
                'admin_upper_limit' => "5000",
                'user_lower_limit'  => '200',
                'user_upper_limit'  => "5000",
                'updated_date'      => format_date('today'),
            ),
            array(
                'data_id'   => 2,
                'data_desc'		    => "size",
                'admin_fixed'       => "1",
                'user_fixed'        => "1",
                'admin_lower_limit' => '4',
                'admin_upper_limit' => "20",
                'user_lower_limit'  => '4',
                'user_upper_limit'  => "6",
                'updated_date'      => format_date('today'),
            ),
            array(
                'data_id'   => 3,
                'data_desc'		    => "prize_pool",
                'admin_fixed'       => "1",
                'user_fixed'        => "1",
                'admin_lower_limit' => '0',
                'admin_upper_limit' => "0",
                'user_lower_limit'  => '0',
                'user_upper_limit'  => "10000",
                'updated_date'      => format_date('today'),
            )
        );

        $this->db_stock->insert_batch(MASTER_DATA_ENTRY,$data);  

        $fields = array(
            'lineup_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),  
            'lineup_master_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),  
            'stock_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ), 
            'score' => array(
                'type' => 'DECIMAL',
                'constraint' => '50,2',
                'default' => '0.0',
                'null' => FALSE
            ),
            'user_price' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'null' => FALSE
            ),
            'gain_loss' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'null' => FALSE
            ),
            'accuracy_percent' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'null' => FALSE,
                'comment' => 'For Stock Type 3'
            ),
            'user_lot_size' => array(
                'type' => 'INT',
                'constraint' => 9,
                'default' => NULL,
                'null' =>TRUE
            ),
            'captain' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1 => Captain'
            ),
            'added_date' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            )
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('lineup_id',TRUE);
        $this->stock_forge->create_table(LINEUP ,FALSE,$attributes); 

        $sql="ALTER TABLE ".$this->db_stock->dbprefix(LINEUP)." ADD UNIQUE KEY `lineup_master` (`lineup_master_id`,`stock_id`);";
	  	$this->db_stock->query($sql);


        $fields = array(
            'team_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),  
            'lineup_master_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),  
            'collection_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),  
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),                     
            'team_data' => array(
                'type' => 'json',
                'null' => FALSE
            ),
            'added_date' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            )
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('team_id',TRUE);
        $this->stock_forge->create_table(COMPLETED_TEAM ,FALSE,$attributes);   

        $sql="ALTER TABLE ".$this->db_stock->dbprefix(COMPLETED_TEAM)." ADD UNIQUE KEY `completed_team` (`collection_id`,`lineup_master_id`,`user_id`);";
	  	$this->db_stock->query($sql);


        $fields = array(
            'invite_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),  
            'contest_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),  
            'contest_unique_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE
            ),  
            'email' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => TRUE,
                'default' => NULL
            ),  
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
                'default' => 0
            ),  
            'invite_from' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                'default' => NULL,
                'comment' => 'include the user id of the user inviting'
            ),             
            'message' => array(
                'type' => 'text',
                'default'=>NULL,
            ),
            'code' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default'=>1,
                'null' => TRUE
            ),  
            'status' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                'default' => NULL
            ), 
            'expire_date' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'default' => NULL
            ), 
            'created_date' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'default' => NULL
            )
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('invite_id',TRUE);
        $this->stock_forge->create_table(INVITE ,FALSE,$attributes);  

        $sql="ALTER TABLE ".$this->db_stock->dbprefix(INVITE)." ADD UNIQUE KEY `invite_unique_id` (`contest_id`,`email`,`user_id`);";
	  	$this->db_stock->query($sql);

        $sql="ALTER TABLE ".$this->db_stock->dbprefix(INVITE)." ADD KEY `invite_user_id` (`user_id`);";
	  	$this->db_stock->query($sql);

        $sql="ALTER TABLE ".$this->db_stock->dbprefix(LINEUP_MASTER_CONTEST)." ADD UNIQUE `contest_lineup_id` (`contest_id`, `lineup_master_id`)";
	  	$this->db_stock->query($sql);
          
        $sql="ALTER TABLE ".$this->db_stock->dbprefix(CONTEST)." CHANGE `contest_title` `contest_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db_stock->query($sql);
       
        $sql="ALTER TABLE ".$this->db_stock->dbprefix(LINEUP_MASTER_CONTEST)."  ADD `last_score` DECIMAL(10,2) NULL DEFAULT '0' AFTER `total_score`;";
	  	$this->db_stock->query($sql);

        $fields = array(
            'holiday_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),  
            'market_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),   
            'holiday_date' => array(
                'type' => 'DATE',
                'null' => FALSE 
            ), 
            'year' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE 
            ),           
            'description' => array(
                'type' => 'TEXT',
                'default' => NULL,
                'null' => TRUE
            ),
            'added_date' => array(
                'type' => 'DATETIME',
                'null' => FALSE 
            )
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('holiday_id',TRUE);
        $this->stock_forge->create_table(HOLIDAY,FALSE,$attributes);

        $sql="ALTER TABLE ".$this->db_stock->dbprefix(HOLIDAY)." ADD UNIQUE KEY `holiday_uid` (`market_id`,`holiday_date`, `year`);";
        $this->db->query($sql);  

                $fields = array(
            'industry_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),  
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE
            ),   
            'display_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => FALSE 
            ), 
            'status' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default'=>1,
                'null' => FALSE ,
                'comment' => '0 - In Active, 1 - Active'
            ),           
            'added_date' => array(
                'type' => 'DATETIME',
                'null' => FALSE 
            )
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('industry_id',TRUE);
        $this->stock_forge->create_table(INDUSTRY,FALSE,$attributes);

    }

    public function down() {
      //down script   
     /* 
      $this->stock_forge->drop_table(HOLIDAY);
      $this->stock_forge->drop_table(INVITE);     
      $this->stock_forge->drop_table(MASTER_DATA_ENTRY);     
      $this->stock_forge->drop_table(WISHLIST);      
      $this->stock_forge->drop_table(MERCHANDISE);
      $this->stock_forge->drop_table(LINEUP_MASTER_CONTEST);
      $this->stock_forge->drop_table(LINEUP_MASTER);
      $this->stock_forge->drop_table(CONTEST);
      $this->stock_forge->drop_table(COLLECTION_STOCK);
      $this->stock_forge->drop_table(COLLECTION);
      $this->stock_forge->drop_table(CONTEST_TEMPLATE_CATEGORY);
      $this->stock_forge->drop_table(CONTEST_TEMPLATE);
      $this->stock_forge->drop_table(STOCK_HISTORY_DETAILS);
      $this->stock_forge->drop_table(STOCK_HISTORY);
      $this->stock_forge->drop_table(STOCK);
      $this->stock_forge->drop_table(MASTER_STOCK);
      $this->stock_forge->drop_table(MASTER_GROUP);
      $this->stock_forge->drop_table(CONTEST_CATEGORY);
      $this->stock_forge->drop_table(MARKET);  
      */
    
    }

}
