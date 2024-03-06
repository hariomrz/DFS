<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cashfree_wallet_bank extends CI_Migration {

    public function up() {

        $fields = array(
              'id'=>array(
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => TRUE,
                'null' => FALSE
              ),
              'payment_option' => array(
                'type' => 'CHAR',
                'constraint' => '20',
                'default'=>NULL,
            ),
                'type_code' => array(
                'type' => 'CHAR',
                'constraint' => '5',
                'default'=>NULL,
            ),
            'payment_code' => array(
                'type' => 'INT',
                'constraint' => '10',
                'default'=>NULL,
            ),
            'payment_type'=>array(
                'type' => 'INT',
                'constraint' => '10',
                'default' => NULL,
                'comment'=>'0 -> FOR WALLET , 1 -> FOR NETBANKING'
            ),
            'status' => array(
                'type' => 'INT',
                'constraint' => '2',
                'default'=>0,
              ),
          );

          $attributes = array('ENGINE' => 'InnoDB');
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id',TRUE);
        $this->dbforge->create_table(CASHFREE_WALLET_BANK ,FALSE,$attributes);



        //   $this->dbforge->add_column(USER,$fields);
         
        //   $fields = array(
        //     'is_affiliate'=>array(
        //       'type' => 'TINYINT',
        //       'constraint' => 1,
        //       'null' => FALSE,
        //       'default' => 0,
        //       'comment' => '0=>normal_user,1=>affiliated_user'
        //     ),
        // );
        // $this->dbforge->add_column(USER_AFFILIATE_HISTORY,$fields);

        $wallet_list = 
                array(
                    array( 
                        'payment_option'=> 'Paytm',
                        'type_code' => 'wallet',
                        'payment_code' => '4007',
                        'payment_type' => 0,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Amazon Pay',
                        'type_code' => 'wallet',
                        'payment_code' => '4008',
                        'payment_type' => 0,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'PhonePe',
                        'type_code' => 'wallet',
                        'payment_code' => '4009',
                        'payment_type' => 0,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'FreeCharge',
                        'type_code' => 'wallet',
                        'payment_code' => '4001',
                        'payment_type' => 0,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'MobiKwik',
                        'type_code' => 'wallet',
                        'payment_code' => '4002',
                        'payment_type' => 0,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Ola Money',
                        'type_code' => 'wallet',
                        'payment_code' => '4003',
                        'payment_type' => 0,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Reliance Jio Money',
                        'type_code' => 'wallet',
                        'payment_code' => '4004',
                        'payment_type' => 0,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Airtel Money',
                        'type_code' => 'wallet',
                        'payment_code' => '4006',
                        'payment_type' => 0,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Axis Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3003',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Kotak Mahindra Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3032',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'HDFC Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3021',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'State Bank Of India',
                        'type_code' => 'nb',
                        'payment_code' => '3044',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'ICICI Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3022',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Allahabad Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3001',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Andhra Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3002',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Bank of Baroda - Retail Banking',
                        'type_code' => 'nb',
                        'payment_code' => '3005',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Bank of India',
                        'type_code' => 'nb',
                        'payment_code' => '3006',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Bank of Maharashtra',
                        'type_code' => 'nb',
                        'payment_code' => '3007',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Canara Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3009',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Catholic Syrian Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3010',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Central Bank of India',
                        'type_code' => 'nb',
                        'payment_code' => '3011',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'City Union Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3012',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Corporation Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3013',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Dena Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3015',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Deutsche Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3016',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'DBS Bank Ltd',
                        'type_code' => 'nb',
                        'payment_code' => '3017',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'DCB Bank - Personal',
                        'type_code' => 'nb',
                        'payment_code' => '3018',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Dhanlakshmi Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3019',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Federal Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3020',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'IDBI Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3023',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'IDFC Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3024',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Indian Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3026',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Indian Overseas Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3027',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'IndusInd Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3028',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Jammu and Kashmir Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3029',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Karnataka Bank Ltd',
                        'type_code' => 'nb',
                        'payment_code' => '3030',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Karur Vysya Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3031',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Laxmi Vilas Bank - Retail Net Banking',
                        'type_code' => 'nb',
                        'payment_code' => '3033',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'PNB (Erstwhile Oriental Bank of Commerce)',
                        'type_code' => 'nb',
                        'payment_code' => '3035',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Punjab & Sind Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3037',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Punjab National Bank - Retail Banking',
                        'type_code' => 'nb',
                        'payment_code' => '3038',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'RBL Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3039',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Saraswat Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3040',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'South Indian Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3042',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Standard Chartered Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3043',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Syndicate Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3050',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Tamilnad Mercantile Bank Ltd',
                        'type_code' => 'nb',
                        'payment_code' => '3052',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'UCO Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3054',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Union Bank of India',
                        'type_code' => 'nb',
                        'payment_code' => '3055',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'PNB (Erstwhile United Bank of India)',
                        'type_code' => 'nb',
                        'payment_code' => '3056',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Vijaya Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3057',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Yes Bank Ltd',
                        'type_code' => 'nb',
                        'payment_code' => '3058',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Bank of Baroda - Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3060',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Bank of India - Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3061',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'DCB Bank - Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3062',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Lakshmi Vilas Bank - Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3064',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Punjab National Bank - Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3065',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'State Bank of India - Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3066',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Union Bank of India - Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3067',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Andhra Bank Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3070',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Axis Bank Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3071',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Dhanlaxmi Bank Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3072',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'ICICI Corporate Netbanking',
                        'type_code' => 'nb',
                        'payment_code' => '3073',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Ratnakar Corporate Banking',
                        'type_code' => 'nb',
                        'payment_code' => '3074',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Shamrao Vithal Bank Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3075',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Equitas Small Finance Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3076',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Yes Bank Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3077',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'UBI (Erstwhile Corporation Bank) - Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3078',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Bandhan Bank- Corporate banking',
                        'type_code' => 'nb',
                        'payment_code' => '3079',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Barclays Corporate- Corporate Banking - Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3080',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Indian Overseas Bank Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3081',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'PNB (Erstwhile Oriental Bank of Commerce) - Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3082',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'City Union Bank of Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3083',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'HDFC Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3084',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Allahabad Corporate',
                        'type_code' => 'nb',
                        'payment_code' => '3085',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Shamrao Vitthal Co-operative Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3041',
                        'payment_type' => 1,
                        'status' => 1
                    ),array( 
                        'payment_option'=> 'Tamil Nadu State Co-operative Bank',
                        'type_code' => 'nb',
                        'payment_code' => '3051',
                        'payment_type' => 1,
                        'status' => 1
                    ),
                );
        $this->db->insert_batch(CASHFREE_WALLET_BANK,$wallet_list);

       
             
    }
    
    public function down() {
        	//down script 
        $this->dbforge->drop_table(CASHFREE_WALLET_BANK);
        // $this->dbforge->drop_column(CASHFREE_WALLET_BANK, 'is_affiliate');
        // $this->db->where('notification_type',422)->delete(EMAIL_TEMPLATE);
    }
}
