<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Transaction_messages extends CI_Migration {

  public function up()
  {

    $fields = array(
        'transaction_messages_id' => array(
                'type' => 'INT',
                'constraint' => 10,
                //'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'null' => FALSE
        ),
        'source' => array(
          'type' => 'INT',
          'constraint' => 10,
          'null' => FALSE,
        ),
        'en_message' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => FALSE,
          ),
          'hi_message' => array(
            'type' => 'VARCHAR',
            'constraint' => 100,
            'null' => FALSE, 
          ),
          'guj_message' => array(
            'type' => 'VARCHAR',
            'constraint' => 100,
            'null' => FALSE, 
          )
       
        );

      $attributes = array('ENGINE' => 'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('transaction_messages_id',TRUE);
      $this->dbforge->create_table(TRANSACTION_MESSAGES ,FALSE,$attributes);   


    $transaction_messages = array (
        0 => 
        array (
          '_id' => 
          array (
            '$oid' => '5def725e2718ccaac1398276',
          ),
          'source' => '1',
          'en_message' => 'Entry fee for %s',
          'hi_message' => '%s के लिए प्रवेश शुल्क',
          'guj_message' => '%s માટે પ્રવેશ ફી',
        ),
        1 => 
        array (
          '_id' => 
          array (
            '$oid' => '5def72702718ccaac1398283',
          ),
          'source' => '2',
          'en_message' => 'Fee Refund For Contest',
          'hi_message' => 'प्रतियोगिता के लिए शुल्क वापसी',
          'guj_message' => 'હરીફાઈ માટે ફી પરત',
        ),
        2 => 
        array (
          '_id' => 
          array (
            '$oid' => '5def72782718ccaac1398290',
          ),
          'source' => '3',
          'en_message' => 'Won Contest Prize',
          'hi_message' => 'प्रतियोगिता का पुरस्कार जीता',
          'guj_message' => 'કોન્ટેસ્ટ પ્રાઇઝ જીત્યો',
        ),
        3 => 
        array (
          '_id' => 
          array (
            '$oid' => '5def728c2718ccaac1398298',
          ),
          'source' => '4',
          'en_message' => 'Friend refferal by %s',
          'hi_message' => '%s द्वारा मित्र का संदर्भ',
          'guj_message' => '%s દ્વારા ફ્રેન્ડ રેફરલ',
        ),
        4 => 
        array (
          '_id' => 
          array (
            '$oid' => '5def74722718ccaac1398312',
          ),
          'source' => '5',
          'en_message' => 'Bonus expired',
          'hi_message' => 'बोनस समाप्त हो गया',
          'guj_message' => 'બોનસ સમાપ્ત',
        ),
        5 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522d3',
          ),
          'source' => 6,
          'en_message' => 'By Promocode',
          'hi_message' => 'प्रोमोकोड द्वारा',
          'guj_message' => 'પ્રોમોકોડ દ્વારા',
        ),
        6 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522d4',
          ),
          'source' => 7,
          'en_message' => 'Amount Deposited',
          'hi_message' => 'जमा राशि',
          'guj_message' => 'જમા રકમ',
        ),
        7 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522d5',
          ),
          'source' => 8,
          'en_message' => 'Amount withdrawal',
          'hi_message' => 'राशि की निकासी',
          'guj_message' => 'રકમ ઉપાડ',
        ),
        8 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522d6',
          ),
          'source' => 9,
          'en_message' => 'Credit bonus on deposit',
          'hi_message' => 'जमा पर क्रेडिट बोनस',
          'guj_message' => 'થાપણ પર ક્રેડિટ બોનસ',
        ),
        9 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522d7',
          ),
          'source' => 10,
          'en_message' => 'Coins deposit',
          'hi_message' => 'सिक्के जमा',
          'guj_message' => 'સિક્કા જમા',
        ),
        10 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522d8',
          ),
          'source' => 11,
          'en_message' => 'Total TDS Deducted',
          'hi_message' => 'कुल टीडीएस घटाया गया',
          'guj_message' => 'કુલ ટીડીએસ કપાત',
        ),
        11 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522d9',
          ),
          'source' => 12,
          'en_message' => 'Signup bonus',
          'hi_message' => 'साइनअप बोनस',
          'guj_message' => 'સાઇનઅપ બોનસ',
        ),
        12 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522da',
          ),
          'source' => 13,
          'en_message' => 'Referral bonus for mobile verification',
          'hi_message' => 'मोबाइल सत्यापन के लिए रेफरल बोनस',
          'guj_message' => 'મોબાઇલ ચકાસણી માટે રેફરલ બોનસ',
        ),
        13 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522db',
          ),
          'source' => 14,
          'en_message' => 'Referral bonus for pan card verification',
          'hi_message' => 'पैन कार्ड सत्यापन के लिए रेफरल बोनस',
          'guj_message' => 'પાન કાર્ડ ચકાસણી માટે રેફરલ બોનસ',
        ),
        14 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522dc',
          ),
          'source' => 15,
          'en_message' => 'Referred Contest Join Bonus',
          'hi_message' => 'रेफरेड कॉन्टेस्ट जॉइन बोनस',
          'guj_message' => 'ઉલ્લેખિત હરીફાઈ બોનસમાં જોડાઓ',
        ),
        15 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522dd',
          ),
          'source' => 16,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        16 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522de',
          ),
          'source' => 17,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        17 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522df',
          ),
          'source' => 18,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        18 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522e0',
          ),
          'source' => 19,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        19 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522e1',
          ),
          'source' => 20,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        20 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522e2',
          ),
          'source' => 21,
          'en_message' => 'Redeemed from Store',
          'hi_message' => 'स्टोर से भुनाया गया',
          'guj_message' => 'સ્ટોરમાંથી છૂટકારો મળ્યો',
        ),
        21 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522e3',
          ),
          'source' => 22,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        22 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522e4',
          ),
          'source' => 23,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        23 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522e5',
          ),
          'source' => 24,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        24 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522e6',
          ),
          'source' => 25,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        25 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522e7',
          ),
          'source' => 26,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        26 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522e8',
          ),
          'source' => 27,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        27 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522e9',
          ),
          'source' => 28,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        28 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522ea',
          ),
          'source' => 29,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        29 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522eb',
          ),
          'source' => 30,
          'en_message' => 'Promocode {cash_type} Received',
          'hi_message' => 'प्रोमोकोड {cash_type} प्राप्त हुआ',
          'guj_message' => 'પ્રોમોકોડ પ્રાપ્ત થયો',
        ),
        30 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522ec',
          ),
          'source' => 31,
          'en_message' => 'Promocode {cash_type} Received',
          'hi_message' => 'प्रोमोकोड {cash_type} प्राप्त हुआ',
          'guj_message' => 'પ્રોમોકોડ પ્રાપ્ત થયો',
        ),
        31 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522ed',
          ),
          'source' => 32,
          'en_message' => 'Promocode {cash_type} Received',
          'hi_message' => 'प्रोमोकोड {cash_type} प्राप्त हुआ',
          'guj_message' => 'પ્રોમોકોડ પ્રાપ્ત થયો',
        ),
        32 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522ee',
          ),
          'source' => 33,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        33 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522ef',
          ),
          'source' => 34,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        34 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522f0',
          ),
          'source' => 35,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        35 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522f1',
          ),
          'source' => 36,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        36 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522f2',
          ),
          'source' => 37,
          'en_message' => 'coins for deal',
          'hi_message' => 'सौदा करने के लिए सिक्के',
          'guj_message' => 'સોદા માટે સિક્કા',
        ),
        37 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522f3',
          ),
          'source' => 38,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        38 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522f4',
          ),
          'source' => 39,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        39 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522f5',
          ),
          'source' => 40,
          'en_message' => 'Bet Coins For Prediction',
          'hi_message' => 'शर्त के लिए बेट सिक्के',
          'guj_message' => 'આગાહી માટે સિક્કાઓ',
        ),
        40 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522f6',
          ),
          'source' => 41,
          'en_message' => 'Prediction Won',
          'hi_message' => 'भविष्यवाणी जीता',
          'guj_message' => 'આગાહી જીતી',
        ),
        41 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522f7',
          ),
          'source' => 42,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        42 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522f8',
          ),
          'source' => 43,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        43 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522f9',
          ),
          'source' => 44,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        44 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522fa',
          ),
          'source' => 45,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        45 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522fb',
          ),
          'source' => 46,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        46 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522fc',
          ),
          'source' => 47,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        47 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522fd',
          ),
          'source' => 48,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        48 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522fe',
          ),
          'source' => 49,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        49 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce4522ff',
          ),
          'source' => 50,
          'en_message' => 'Bonus cash awarded for sign up',
          'hi_message' => 'साइन अप के लिए बोनस नकद प्रदान किया गया',
          'guj_message' => 'સાઇન અપ કરવા માટે બોનસ રોકડ આપવામાં આવે છે',
        ),
        50 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452300',
          ),
          'source' => 51,
          'en_message' => 'Real cash awarded for sign up',
          'hi_message' => 'साइन अप के लिए वास्तविक नकद प्रदान किया गया',
          'guj_message' => 'સાઇન અપ કરવા માટે પ્રત્યક્ષ રોકડ આપવામાં આવે છે',
        ),
        51 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452301',
          ),
          'source' => 52,
          'en_message' => 'Referral coins awarded on sign up by Friend',
          'hi_message' => 'मित्र द्वारा साइन अप पर सम्मानित किए गए रेफरल सिक्के',
          'guj_message' => 'મિત્ર દ્વારા સાઇન અપ કરવા પર રેફરલ સિક્કા આપવામાં આવ્યા',
        ),
        52 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452302',
          ),
          'source' => 53,
          'en_message' => 'Referral bonus cash awarded on sign up by Friend',
          'hi_message' => 'मित्र द्वारा साइन अप पर सम्मानित किया गया रेफरल बोनस नकद',
          'guj_message' => 'મિત્ર દ્વારા સાઇન અપ કરવા પર રેફરલ બોનસ રોકડ આપવામાં આવે છે',
        ),
        53 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452303',
          ),
          'source' => 54,
          'en_message' => 'Referral real cash awarded on sign up by Friend',
          'hi_message' => 'मित्र द्वारा साइन अप करने पर रेफरल वास्तविक नकद प्रदान किया जाता है',
          'guj_message' => 'મિત્ર દ્વારા સાઇન અપ કરવા પર રેફરલ રીઅલ રોકડ',
        ),
        54 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452304',
          ),
          'source' => 55,
          'en_message' => 'Referral coins awarded on sign up by Friend',
          'hi_message' => 'मित्र द्वारा साइन अप पर सम्मानित किए गए रेफरल सिक्के',
          'guj_message' => 'મિત્ર દ્વારા સાઇન અપ કરવા પર રેફરલ સિક્કા આપવામાં આવ્યા',
        ),
        55 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452305',
          ),
          'source' => 56,
          'en_message' => 'Bonus cash awarded for sign up',
          'hi_message' => 'साइन अप के लिए बोनस नकद प्रदान किया गया',
          'guj_message' => 'સાઇન અપ કરવા માટે બોનસ રોકડ આપવામાં આવે છે',
        ),
        56 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452306',
          ),
          'source' => 57,
          'en_message' => 'Real cash awarded for sign up',
          'hi_message' => 'साइन अप के लिए वास्तविक नकद प्रदान किया गया',
          'guj_message' => 'સાઇન અપ કરવા માટે પ્રત્યક્ષ રોકડ આપવામાં આવે છે',
        ),
        57 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452307',
          ),
          'source' => 58,
          'en_message' => 'Referral coins awarded on sign up by Friend',
          'hi_message' => 'मित्र द्वारा साइन अप पर सम्मानित किए गए रेफरल सिक्के',
          'guj_message' => 'મિત્ર દ્વારા સાઇન અપ કરવા પર રેફરલ સિક્કા આપવામાં આવ્યા',
        ),
        58 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452308',
          ),
          'source' => 59,
          'en_message' => 'Bonus cash awarded for pan card verification',
          'hi_message' => 'पैन कार्ड सत्यापन के लिए बोनस नकद प्रदान किया गया',
          'guj_message' => 'પાનકાર્ડ ચકાસણી માટે બોનસ રોકડ',
        ),
        59 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452309',
          ),
          'source' => 60,
          'en_message' => 'Real Cash awarded for pan card verification',
          'hi_message' => 'पैन कार्ड सत्यापन के लिए वास्तविक नकद प्रदान किया गया',
          'guj_message' => 'પાન કાર્ડ ચકાસણી માટે રીઅલ કેશ આપવામાં આવ્યું',
        ),
        60 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45230a',
          ),
          'source' => 61,
          'en_message' => 'Coins awarded for pan card verification',
          'hi_message' => 'पैन कार्ड सत्यापन के लिए सम्मानित किया गया सिक्के',
          'guj_message' => 'પાન કાર્ડ ચકાસણી માટે સિક્કા એનાયત કરાયા',
        ),
        61 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45230b',
          ),
          'source' => 62,
          'en_message' => 'Bonus cash awarded on pan card verification by Friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर बोनस नकद प्रदान किया गया',
          'guj_message' => 'મિત્ર દ્વારા પાન કાર્ડ ચકાસણી પર બોનસ રોકડ આપવામાં આવ્યું',
        ),
        62 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45230c',
          ),
          'source' => 63,
          'en_message' => 'Real cash awarded on pan card verification by Friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर वास्तविक नकद राशि प्रदान की गई',
          'guj_message' => 'મિત્ર દ્વારા પાન કાર્ડ ચકાસણી પર વાસ્તવિક રોકડ આપવામાં આવે છે',
        ),
        63 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45230d',
          ),
          'source' => 64,
          'en_message' => 'Coins awarded on pan card verification by Friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા પાન કાર્ડ ચકાસણી પર સિક્કા આપવામાં આવ્યા',
        ),
        64 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45230e',
          ),
          'source' => 65,
          'en_message' => 'Bonus cash awarded for pan card verification',
          'hi_message' => 'पैन कार्ड सत्यापन के लिए बोनस नकद प्रदान किया गया',
          'guj_message' => 'પાનકાર્ડ ચકાસણી માટે બોનસ રોકડ',
        ),
        65 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45230f',
          ),
          'source' => 66,
          'en_message' => 'Real Cash awarded for pan card verification',
          'hi_message' => 'पैन कार्ड सत्यापन के लिए वास्तविक नकद प्रदान किया गया',
          'guj_message' => 'પાન કાર્ડ ચકાસણી માટે રીઅલ કેશ આપવામાં આવ્યું',
        ),
        66 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452310',
          ),
          'source' => 67,
          'en_message' => 'Coins awarded for pan card verification',
          'hi_message' => 'पैन कार्ड सत्यापन के लिए सम्मानित किया गया सिक्के',
          'guj_message' => 'પાન કાર્ડ ચકાસણી માટે સિક્કા એનાયત કરાયા',
        ),
        67 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452311',
          ),
          'source' => 68,
          'en_message' => 'join a cash contest by friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા રોકડ હરીફાઈમાં જોડાઓ',
        ),
        68 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452312',
          ),
          'source' => 69,
          'en_message' => 'join a cash contest by friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા રોકડ હરીફાઈમાં જોડાઓ',
        ),
        69 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452313',
          ),
          'source' => 70,
          'en_message' => 'join a cash contest by friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા રોકડ હરીફાઈમાં જોડાઓ',
        ),
        70 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452314',
          ),
          'source' => 71,
          'en_message' => 'join a cash contest',
          'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
          'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
        ),
        71 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452315',
          ),
          'source' => 72,
          'en_message' => 'join a cash contest',
          'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
          'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
        ),
        72 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452316',
          ),
          'source' => 73,
          'en_message' => 'join a cash contest',
          'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
          'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
        ),
        73 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452317',
          ),
          'source' => 74,
          'en_message' => 'join a cash contest by friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા રોકડ હરીફાઈમાં જોડાઓ',
        ),
        74 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452318',
          ),
          'source' => 75,
          'en_message' => 'join a cash contest by friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા રોકડ હરીફાઈમાં જોડાઓ',
        ),
        75 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452319',
          ),
          'source' => 76,
          'en_message' => 'join a cash contest by friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા રોકડ હરીફાઈમાં જોડાઓ',
        ),
        76 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45231a',
          ),
          'source' => 77,
          'en_message' => 'join a cash contest',
          'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
          'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
        ),
        77 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45231b',
          ),
          'source' => 78,
          'en_message' => 'join a cash contest',
          'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
          'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
        ),
        78 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45231c',
          ),
          'source' => 79,
          'en_message' => 'join a cash contest',
          'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
          'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
        ),
        79 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45231d',
          ),
          'source' => 80,
          'en_message' => 'join a cash contest by friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા રોકડ હરીફાઈમાં જોડાઓ',
        ),
        80 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45231e',
          ),
          'source' => 81,
          'en_message' => 'join a cash contest by friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા રોકડ હરીફાઈમાં જોડાઓ',
        ),
        81 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45231f',
          ),
          'source' => 82,
          'en_message' => 'join a cash contest by friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા રોકડ હરીફાઈમાં જોડાઓ',
        ),
        82 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452320',
          ),
          'source' => 83,
          'en_message' => 'join a cash contest',
          'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
          'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
        ),
        83 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452321',
          ),
          'source' => 84,
          'en_message' => 'join a cash contest',
          'hi_message' => 'एक नकद प्रतियोगिता में शामिल हों',
          'guj_message' => 'રોકડ હરીફાઈમાં જોડાઓ',
        ),
        84 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452322',
          ),
          'source' => 85,
          'en_message' => 'join a cash contest by friend',
          'hi_message' => 'मित्र द्वारा पैन कार्ड सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા રોકડ હરીફાઈમાં જોડાઓ',
        ),
        85 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452323',
          ),
          'source' => 86,
          'en_message' => 'Bonus cash awarded for email verification',
          'hi_message' => 'ईमेल सत्यापन के लिए बोनस नकद प्रदान किया गया',
          'guj_message' => 'ઇમેઇલ ચકાસણી માટે બોનસ રોકડ આપવામાં આવે છે',
        ),
        86 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452324',
          ),
          'source' => 87,
          'en_message' => 'Real cash awarded for Email Verification',
          'hi_message' => 'ईमेल सत्यापन के लिए वास्तविक नकद राशि प्रदान की गई',
          'guj_message' => 'ઇમેઇલ ચકાસણી માટે પ્રત્યક્ષ રોકડ આપવામાં આવે છે',
        ),
        87 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452325',
          ),
          'source' => 88,
          'en_message' => 'Bonus coins awarded for email verification',
          'hi_message' => 'ईमेल सत्यापन के लिए बोनस सिक्के प्रदान किए गए',
          'guj_message' => 'ઇમેઇલ ચકાસણી માટે બોનસ સિક્કા આપવામાં આવ્યા',
        ),
        88 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452326',
          ),
          'source' => 89,
          'en_message' => 'Bonus cash awarded on email verification by Friend',
          'hi_message' => 'मित्र द्वारा ईमेल सत्यापन पर बोनस नकद प्रदान किया गया',
          'guj_message' => 'મિત્ર દ્વારા ઇમેઇલ ચકાસણી પર બોનસ રોકડ આપવામાં આવ્યું',
        ),
        89 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452327',
          ),
          'source' => 90,
          'en_message' => 'Real cash awarded on email verification by Friend',
          'hi_message' => 'मित्र द्वारा ईमेल सत्यापन पर वास्तविक नकद प्रदान किया गया',
          'guj_message' => 'મિત્ર દ્વારા ઇમેઇલ ચકાસણી પર વાસ્તવિક રોકડ આપવામાં આવે છે',
        ),
        90 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452328',
          ),
          'source' => 91,
          'en_message' => 'Coins awarded on email verification by Friend',
          'hi_message' => 'मित्र द्वारा ईमेल सत्यापन पर दिए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા ઇમેઇલ ચકાસણી પર સિક્કા આપવામાં આવ્યા',
        ),
        91 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452329',
          ),
          'source' => 92,
          'en_message' => 'Bonus cash awarded for email verification',
          'hi_message' => 'ईमेल सत्यापन के लिए बोनस नकद प्रदान किया गया',
          'guj_message' => 'ઇમેઇલ ચકાસણી માટે બોનસ રોકડ આપવામાં આવે છે',
        ),
        92 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45232a',
          ),
          'source' => 93,
          'en_message' => 'Real cash awarded for Email Verification',
          'hi_message' => 'ईमेल सत्यापन के लिए वास्तविक नकद राशि प्रदान की गई',
          'guj_message' => 'ઇમેઇલ ચકાસણી માટે પ્રત્યક્ષ રોકડ આપવામાં આવે છે',
        ),
        93 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45232b',
          ),
          'source' => 94,
          'en_message' => 'Bonus coins awarded for email verification',
          'hi_message' => 'ईमेल सत्यापन के लिए बोनस सिक्के प्रदान किए गए',
          'guj_message' => 'ઇમેઇલ ચકાસણી માટે બોનસ સિક્કા આપવામાં આવ્યા',
        ),
        94 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45232c',
          ),
          'source' => 95,
          'en_message' => 'Bonus cash awarded for First deposit',
          'hi_message' => 'प्रथम जमा के लिए बोनस नकद प्रदान किया गया',
          'guj_message' => 'ફર્સ્ટ ડિપોઝિટ માટે બોનસ રોકડ',
        ),
        95 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45232d',
          ),
          'source' => 96,
          'en_message' => 'Real Cash awarded for First deposit',
          'hi_message' => 'वास्तविक नकद प्रथम जमा के लिए प्रदान किया गया',
          'guj_message' => 'ફર્સ્ટ ડિપોઝિટ માટે રીઅલ કેશ આપવામાં આવે છે',
        ),
        96 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45232e',
          ),
          'source' => 97,
          'en_message' => 'Coins awarded for First deposit',
          'hi_message' => 'पहले जमा के लिए सम्मानित किया गया सिक्के',
          'guj_message' => 'પ્રથમ થાપણ માટે આપેલા સિક્કા',
        ),
        97 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45232f',
          ),
          'source' => 98,
          'en_message' => 'Bonus cash awarded on first deposit by Friend',
          'hi_message' => 'मित्र द्वारा पहले जमा पर बोनस नकद प्रदान किया गया',
          'guj_message' => 'મિત્ર દ્વારા પ્રથમ થાપણ પર બોનસ રોકડ આપવામાં આવે છે',
        ),
        98 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452330',
          ),
          'source' => 99,
          'en_message' => 'Real cash awarded on first deposit by Friend',
          'hi_message' => 'मित्र द्वारा प्रथम जमा पर वास्तविक नकद प्रदान किया गया',
          'guj_message' => 'મિત્ર દ્વારા પ્રથમ થાપણ પર પ્રત્યક્ષ રોકડ આપવામાં આવે છે',
        ),
        99 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452331',
          ),
          'source' => 100,
          'en_message' => 'Coins awarded on first deposit by Friend',
          'hi_message' => 'मित्र द्वारा प्रथम जमा पर प्रदान किए गए सिक्के',
          'guj_message' => 'મિત્ર દ્વારા પ્રથમ થાપણ પર સિક્કા આપવામાં આવ્યા',
        ),
        100 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452332',
          ),
          'source' => 101,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        101 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452333',
          ),
          'source' => 102,
          'en_message' => 'Order Cancel (Refund)',
          'hi_message' => 'ऑर्डर रद्द (वापसी)',
          'guj_message' => 'ઓર્ડર રદ (રીફંડ)',
        ),
        102 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452334',
          ),
          'source' => 103,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        103 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452335',
          ),
          'source' => 104,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        104 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452336',
          ),
          'source' => 105,
          'en_message' => 'Bonus cash awarded for First deposit',
          'hi_message' => 'प्रथम जमा के लिए बोनस नकद प्रदान किया गया',
          'guj_message' => 'ફર્સ્ટ ડિપોઝિટ માટે બોનસ રોકડ',
        ),
        105 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452337',
          ),
          'source' => 106,
          'en_message' => 'Real Cash awarded for First deposit',
          'hi_message' => 'वास्तविक नकद प्रथम जमा के लिए प्रदान किया गया',
          'guj_message' => 'ફર્સ્ટ ડિપોઝિટ માટે રીઅલ કેશ આપવામાં આવે છે',
        ),
        106 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452338',
          ),
          'source' => 107,
          'en_message' => 'Coins awarded for First deposit',
          'hi_message' => 'पहले जमा के लिए सम्मानित किया गया सिक्के',
          'guj_message' => 'પ્રથમ થાપણ માટે આપેલા સિક્કા',
        ),
        107 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452339',
          ),
          'source' => 108,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        108 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45233a',
          ),
          'source' => 109,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        109 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45233b',
          ),
          'source' => 110,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        110 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45233c',
          ),
          'source' => 111,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        111 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45233d',
          ),
          'source' => 112,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        112 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45233e',
          ),
          'source' => 113,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        113 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45233f',
          ),
          'source' => 114,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        114 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452340',
          ),
          'source' => 115,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        115 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452341',
          ),
          'source' => 116,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        116 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452342',
          ),
          'source' => 117,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        117 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452343',
          ),
          'source' => 118,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        118 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452344',
          ),
          'source' => 119,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        119 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452345',
          ),
          'source' => 120,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        120 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452346',
          ),
          'source' => 121,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        121 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452347',
          ),
          'source' => 122,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        122 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452348',
          ),
          'source' => 123,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        123 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452349',
          ),
          'source' => 124,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        124 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45234a',
          ),
          'source' => 125,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        125 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45234b',
          ),
          'source' => 126,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        126 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45234c',
          ),
          'source' => 127,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        127 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45234d',
          ),
          'source' => 128,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        128 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45234e',
          ),
          'source' => 129,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        129 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45234f',
          ),
          'source' => 130,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        130 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452350',
          ),
          'source' => 131,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        131 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452351',
          ),
          'source' => 132,
          'en_message' => 'Bonus for Bank Verification',
          'hi_message' => 'बैंक सत्यापन के लिए बोनस',
          'guj_message' => 'બેંક ચકાસણી માટે બોનસ',
        ),
        132 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452352',
          ),
          'source' => 133,
          'en_message' => 'Real amount for Bank Verification',
          'hi_message' => 'बैंक सत्यापन के लिए वास्तविक राशि',
          'guj_message' => 'બેંક ચકાસણી માટે વાસ્તવિક રકમ',
        ),
        133 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452353',
          ),
          'source' => 134,
          'en_message' => 'Coins for Bank Verification',
          'hi_message' => 'बैंक सत्यापन के लिए सिक्के',
          'guj_message' => 'બેંક ચકાસણી માટે સિક્કા',
        ),
        134 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452354',
          ),
          'source' => 135,
          'en_message' => 'Bonus for Deal',
          'hi_message' => 'डील के लिए बोनस',
          'guj_message' => 'ડીલ કરવા બદલ બોનસ',
        ),
        135 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452355',
          ),
          'source' => 136,
          'en_message' => 'real for deal',
          'hi_message' => 'सौदा के लिए वास्तविक राशि',
          'guj_message' => 'સોદા માટે વાસ્તવિક રકમ',
        ),
        136 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452356',
          ),
          'source' => 137,
          'en_message' => 'Coins for Deal',
          'hi_message' => 'डील के लिए सिक्के',
          'guj_message' => 'ડીલ માટે સિક્કા',
        ),
        137 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452357',
          ),
          'source' => 138,
          'en_message' => 'Bonus for Bank Verification',
          'hi_message' => 'बैंक सत्यापन के लिए बोनस',
          'guj_message' => 'બેન્ક ચકાસણી માટે બોનસ',
        ),
        138 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452358',
          ),
          'source' => 139,
          'en_message' => 'Real amount for Bank Verification',
          'hi_message' => 'बैंक सत्यापन के लिए रियल राशि',
          'guj_message' => 'બેન્ક ચકાસણી માટે રિયલ રકમ',
        ),
        139 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452359',
          ),
          'source' => 140,
          'en_message' => 'Coins for Bank Verification',
          'hi_message' => 'बैंक सत्यापन के लिए सिक्के',
          'guj_message' => 'બેન્ક ચકાસણી માટે સિક્કા',
        ),
        140 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45235a',
          ),
          'source' => 141,
          'en_message' => 'Bonus for Bank Verification',
          'hi_message' => 'बैंक सत्यापन के लिए बोनस',
          'guj_message' => 'બેન્ક ચકાસણી માટે બોનસ',
        ),
        141 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45235b',
          ),
          'source' => 142,
          'en_message' => 'Real amount for Bank Verification',
          'hi_message' => 'बैंक सत्यापन के लिए रियल राशि',
          'guj_message' => 'બેન્ક ચકાસણી માટે રિયલ રકમ',
        ),
        142 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45235c',
          ),
          'source' => 143,
          'en_message' => 'Coins for Bank Verification',
          'hi_message' => 'बैंक सत्यापन के लिए सिक्के',
          'guj_message' => 'બેન્ક ચકાસણી માટે સિક્કા',
        ),
        143 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45235d',
          ),
          'source' => 144,
          'en_message' => 'Daily streak coins',
          'hi_message' => 'डेली लकीर सिक्के',
          'guj_message' => 'દૈનિક દોર સિક્કા',
        ),
        144 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45235e',
          ),
          'source' => 145,
          'en_message' => 'Bonus received for coins redeem',
          'hi_message' => 'सिक्के के एवज लिए प्राप्त बोनस',
          'guj_message' => 'સિક્કા રિડીમ માટે પ્રાપ્ત બોનસ',
        ),
        145 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce45235f',
          ),
          'source' => 146,
          'en_message' => 'Real amount received for coins redeem',
          'hi_message' => 'सिक्के के एवज लिए प्राप्त रियल राशि',
          'guj_message' => 'સિક્કા રિડીમ માટે પ્રાપ્ત રિયલ રકમ',
        ),
        146 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452360',
          ),
          'source' => 147,
          'en_message' => 'coin deduct on coins redeem',
          'hi_message' => 'सिक्के के एवज पर सिक्का घटा',
          'guj_message' => 'સિક્કા રિડીમ પર સિક્કો કપાત',
        ),
        147 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452361',
          ),
          'source' => 148,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        148 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452362',
          ),
          'source' => 149,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        149 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df09abf8884011bce452363',
          ),
          'source' => 150,
          'en_message' => '',
          'hi_message' => '',
          'guj_message' => '',
        ),
        150 => 
        array (
          '_id' => 
          array (
            '$oid' => '5df1cceed4fedd9bf7049023',
          ),
          'source' => 151,
          'en_message' => 'Coins added on feedback approved',
          'hi_message' => 'प्रतिक्रिया पर जोड़ा सिक्के अनुमोदित',
          'guj_message' => 'પ્રતિસાદ પર ઉમેરવામાં સિક્કા મંજૂર',
        ),
    );

    foreach ($transaction_messages as $key => &$val)
    {
        unset($val["_id"]);
    }

    $this->db->insert_batch(TRANSACTION_MESSAGES,$transaction_messages);
    //update notification messages
    $this->db->where('notification_type', 141);
    $this->db->update(NOTIFICATION_DESCRIPTION, array(
      'message' => '{{amount}} coins deducted for {{event}}',
      'en_message' => '{{amount}} coins deducted for {{event}}',
      'hi_message' => '{{amount}} coins deducted for {{event}}',
      'guj_message' => '{{amount}} coins deducted for {{event}}',

    )); 

    $this->db->where('notification_type', 140);
    $this->db->update(NOTIFICATION_DESCRIPTION, array(
      'message' => '{{amount}} coins deducted for {{event}}',
      'en_message' => '{{amount}} coins deducted for {{event}}',
      'hi_message' => '{{amount}} coins deducted for {{event}}',
      'guj_message' => '{{amount}} coins deducted for {{event}}',

    )); 
  
  }

  public function down()
  {
    //down script 
    $this->dbforge->drop_table(TRANSACTION_MESSAGES);
	
  }
}