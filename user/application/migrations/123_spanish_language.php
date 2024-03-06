<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Spanish_language extends CI_Migration {

	public function up() {

        $notification_field = array(
			'es_message' => array(
                'type' => 'LONGTEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
			'es_subject' => array(
			'type' => 'LONGTEXT',
			'character_set' => 'utf8 COLLATE utf8_general_ci',
			'null' => FALSE,
			),
		);
		$this->dbforge->add_column(NOTIFICATION_DESCRIPTION, $notification_field);

		$transection_field = array(
			'es_message' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
		);
		$this->dbforge->add_column(TRANSACTION_MESSAGES, $transection_field);
		
		$sportshub_field = array(
			'es_title' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'es_desc' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			
		);
		
		$this->dbforge->add_column(SPORTS_HUB, $sportshub_field);
		
		$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `es_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `es_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `es_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `es_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
		$this->db->query($sql);

			
		$common_content_field = array(
			'es_header'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'es_body'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);
	
		$this->dbforge->add_column(COMMON_CONTENT, $common_content_field);

		$field = array(
			'es' => array(
                'type' => 'JSON',
                'null' => TRUE,
				'default' => NULL,
			  ),
		);
		$this->dbforge->add_column(EARN_COINS, $field);

		$faq_question_fields = array(
			'es_question'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'es_answer'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);

		$this->dbforge->add_column(FAQ_QUESTIONS, $faq_question_fields);
		
		$faq_category_fields = array(
			'es_category'=>array(
				'type' => 'VARCHAR',
				'constraint' => 30,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);
		$this->dbforge->add_column(FAQ_CATEGORY, $faq_category_fields);

        $sql = "ALTER TABLE ".$this->db->dbprefix(BANNER_MANAGEMENT)." ADD `es_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
        $this->db->query($sql);
		

		//updating columns now

		$sports_hub_arr = array(
            array (
                'es_title' => 'MODO DE TORNEO',
                'es_desc' => '¿Jugador de temporada profesional? Juega durante toda la temporada aquí',
                'game_key' => 'allow_tournament',
                ),array (
                'es_title' => 'Deportes de fantasía diarios',
                'es_desc' => 'Los deportes de fantasía diarios son emocionantes que los deportes de fantasía tradicionales',
                'game_key' => 'allow_dfs',
                ),array (
                'es_title' => 'Predecir y ganar monedas',
                'es_desc' => 'No se requieren habilidades de juego. Solo predice el resultado y gane monedas',
                'game_key' => 'allow_prediction',
                ),array (
                'es_title' => 'Elige la piscina del premio',
                'es_desc' => 'El juego es súper fácil. Solo elige el lado ganador',
                'game_key' => 'allow_pickem',
                ),array (
                'es_title' => 'Juegos múltiples',
                'es_desc' => 'Los deportes de fantasía de múltiples juegos son mucho más emocionantes que los deportes de fantasía tradicionales',
                'game_key' => 'allow_multigame',
                ),array (
                'es_title' => 'Predictor abierto - premio piscina',
                'es_desc' => 'Solo predice el resultado y gane monedas',
                'game_key' => 'allow_open_predictor',
                ),array (
                'es_title' => 'LIBRE PARA JUGAR',
                'es_desc' => 'Juega la fantasía diaria totalmente gratis y gane premios emocionantes.',
                'game_key' => 'allow_free2play',
                ),array (
                'es_title' => 'Predictor abierto - tabla de clasificación',
                'es_desc' => 'Solo predice el resultado y gane premios',
                'game_key' => 'allow_fixed_open_predictor',
                ),array (
                'es_title' => '',
                'es_desc' => '',
                'game_key' => 'allow_prop_fantasy',
                ),
		);

		$this->db->update_batch(SPORTS_HUB,$sports_hub_arr,'game_key');
		
		$common_content_arr = array(
            array (
                'es_header' => 'Balance total',
                'es_body' => 'Ganancias + Bono en Efectivo + Depósito',
                'content_key' => 'wallet',
            ),
		);
		$this->db->update_batch(COMMON_CONTENT,$common_content_arr,'content_key');


        $banner_arr = array(
            array (
                'es_name' => 'Recomendar un amigo',
                'banner_id' => '1'
            ),array (
                'es_name' => 'Depósito',
                'banner_id' => '2'
            ),array (
                'es_name' => 'Depósito',
                'banner_id' => '3'
            ),
        );

        $this->db->update_batch(BANNER_MANAGEMENT,$banner_arr,'banner_id');

		  
		$earn_coins =array (
            
            array (
                'module_key' => 'refer-a-friend',
                'es' =>
                json_encode (array (
                'label' => 'RECOMENDAR UN AMIGO',
                 'description' => 'Gane # monedas # monedas para el registro de cada amigo',
                 'button_text' => 'REFERIRSE',
                )),
                ),array (
                'module_key' => 'daily_streak_bonus',
                'es' =>
                json_encode (array (
                'label' => 'Bonificación de check-in diario',
                 'description' => 'Gane monedas diariamente iniciando sesión',
                 'button_text' => 'Aprende más',
                )),
                ),array (
                'module_key' => 'prediction',
                'es' =>
                json_encode (array (
                'label' => 'Predicción de juego',
                 'description' => 'Predecir y ganar monedas',
                 'button_text' => 'PREDECIR',
                )),
                ),array (
                'module_key' => 'promotions',
                'es' =>
                json_encode (array (
                'label' => 'Promociones',
                 'description' => 'Se quedó sin monedas? Mira un video y rellena tu billetera de monedas',
                 'button_text' => 'RELOJ',
                )),
                ),array (
                'module_key' => 'feedback',
                'es' =>
                json_encode (array (
                'label' => 'RETROALIMENTACIÓN',
                 'description' => 'Los comentarios genuinos recibirán monedas después de la aprobación del administrador',
                 'button_text' => 'Escribenos',
                )),
                ),
		  );

		$this->db->update_batch(EARN_COINS,$earn_coins,'module_key');

		$categories = array (
            array (
                'category_alias' => 'registration',
                'es_category' => 'registro',
                ),array (
                'category_alias' => 'playing_the_game',
                'es_category' => 'jugando el juego',
                ),array (
                'category_alias' => 'scores_points',
                'es_category' => 'Scores_Points',
                ),array (
                'category_alias' => 'contests',
                'es_category' => 'concursos',
                ),array (
                'category_alias' => 'account_balance',
                'es_category' => 'saldo de la cuenta',
                ),array (
                'category_alias' => 'verification',
                'es_category' => 'verificación',
                ),array (
                'category_alias' => 'withdrawals',
                'es_category' => 'retiros',
                ),array (
                'category_alias' => 'legality',
                'es_category' => 'legalidad',
                ),array (
                'category_alias' => 'fair_play_violation',
                'es_category' => 'jair_play_violation',
                ),array (
                'category_alias' => 'payments',
                'es_category' => 'pagos',
                ),
		);
		$this->db->update_batch(FAQ_CATEGORY,$categories,'category_alias');
		
		$cms_data = array (
            array (
                'page_alias' => 'about',
                'es_meta_keyword' => 'Sobre nosotros',
                'es_page_title' => 'Sobre nosotros',
                'es_meta_desc' => '',
                'es_page_content' => 'Acerca del texto de contenido',
                ),array (
                'page_alias' => 'how_it_works',
                'es_meta_keyword' => 'Cómo funciona',
                'es_page_title' => 'Cómo funciona',
                'es_meta_desc' => '',
                'es_page_content' => 'Cómo funciona el texto',
                ),array (
                'page_alias' => 'terms_of_use',
                'es_meta_keyword' => 'Términos y condiciones, cómo usar, usa reglas',
                'es_page_title' => 'Términos de Uso',
                'es_meta_desc' => 'Términos de uso Meta Desc',
                'es_page_content' => 'Terminar texto',
                ),array (
                'page_alias' => 'privacy_policy',
                'es_meta_keyword' => 'Política de privacidad',
                'es_page_title' => 'Política de privacidad',
                'es_meta_desc' => '',
                'es_page_content' => 'Texto de la política de privacidad',
                ),array (
                'page_alias' => 'faq',
                'es_meta_keyword' => 'Preguntas más frecuentes',
                'es_page_title' => 'Preguntas más frecuentes',
                'es_meta_desc' => '',
                'es_page_content' => 'Texto de las preguntas frecuentes',
                ),array (
                'page_alias' => 'support',
                'es_meta_keyword' => 'Apoyo',
                'es_page_title' => 'Apoyo',
                'es_meta_desc' => 'Apoyo',
                'es_page_content' => 'texto de soporte',
                ),array (
                'page_alias' => 'affiliations',
                'es_meta_keyword' => 'Afiliaciones',
                'es_page_title' => 'Afiliaciones',
                'es_meta_desc' => '',
                'es_page_content' => 'texto de afiliaciones',
                ),array (
                'page_alias' => 'rules_and_scoring',
                'es_meta_keyword' => 'Reglas y puntuación1',
                'es_page_title' => 'Reglas y anotaciones',
                'es_meta_desc' => 'Reglas y puntuación 12',
                'es_page_content' => 'texto de reglas',
                ),array (
                'page_alias' => 'career',
                'es_meta_keyword' => 'Carrera profesional',
                'es_page_title' => 'Carrera profesional',
                'es_meta_desc' => '',
                'es_page_content' => 'texto de carrera',
                ),array (
                'page_alias' => 'press_media',
                'es_meta_keyword' => 'Prensa y medios',
                'es_page_title' => 'Prensa y medios',
                'es_meta_desc' => '',
                'es_page_content' => 'Presione el texto de los medios',
                ),array (
                'page_alias' => 'referral',
                'es_meta_keyword' => 'Remisión',
                'es_page_title' => 'Remisión',
                'es_meta_desc' => '',
                'es_page_content' => 'texto de referencia',
                ),array (
                'page_alias' => 'offers',
                'es_meta_keyword' => 'Oferta',
                'es_page_title' => 'Oferta',
                'es_meta_desc' => '',
                'es_page_content' => 'Ofrece texto',
                ),array (
                'page_alias' => 'contact_us',
                'es_meta_keyword' => 'Contáctenos',
                'es_page_title' => 'Contáctenos',
                'es_meta_desc' => '',
                'es_page_content' => 'Contactos usa texto',
                ),array (
                'page_alias' => 'refund_policy',
                'es_meta_keyword' => 'Politica de reembolso',
                'es_page_title' => 'Politica de reembolso',
                'es_meta_desc' => 'Politica de reembolso',
                'es_page_content' => 'Texto de rufund',
                ),array (
                'page_alias' => 'legality',
                'es_meta_keyword' => 'Legalidad',
                'es_page_title' => 'Legalidad',
                'es_meta_desc' => 'Legalidad',
                'es_page_content' => 'texto de legalidad',
                ),
        );
		
        $this->db->update_batch(CMS_PAGES,$cms_data,'page_alias');


        $transaction_msg_data = array (
            array(
                'source'=>1,
                'es_message'=>'Tarifa de entrada para %s', 
                ),array(
                'source'=>2,
                'es_message'=>'Reembolso de tarifas para el concurso', 
                ),array(
                'source'=>3,
                'es_message'=>'Premio del concurso de ganancias', 
                ),array(
                'source'=>4,
                'es_message'=>'Amigo refferal por %s', 
                ),array(
                'source'=>5,
                'es_message'=>'Bonus expiró', 
                ),array(
                'source'=>6,
                'es_message'=>'Por promoción', 
                ),array(
                'source'=>7,
                'es_message'=>'Cantidad depositada', 
                ),array(
                'source'=>8,
                'es_message'=>'Monto retiro', 
                ),array(
                'source'=>9,
                'es_message'=>'Bono de crédito en el depósito', 
                ),array(
                'source'=>10,
                'es_message'=>'Depósito de monedas', 
                ),array(
                'source'=>11,
                'es_message'=>'TDS total deducido', 
                ),array(
                'source'=>12,
                'es_message'=>'Bonus al registrarse', 
                ),array(
                'source'=>13,
                'es_message'=>'Bono de referencia para la verificación móvil', 
                ),array(
                'source'=>14,
                'es_message'=>'Bonificación de referencia para {{p_to_id}} verificación de la tarjeta', 
                ),array(
                'source'=>15,
                'es_message'=>'Concurso referido Únase a Bonus', 
                ),array(
                'source'=>20,
                'es_message'=>'Revertir premio del concurso', 
                ),array(
                'source'=>21,
                'es_message'=>'Premio del concurso de ganancias', 
                ),array(
                'source'=>30,
                'es_message'=>'Promocode {cash_type} recibido', 
                ),array(
                'source'=>31,
                'es_message'=>'Promocode {cash_type} recibido', 
                ),array(
                'source'=>32,
                'es_message'=>'Promocode {cash_type} recibido', 
                ),array(
                'source'=>37,
                'es_message'=>'Monedas para el trato', 
                ),array(
                'source'=>40,
                'es_message'=>'Bet Coins para la predicción', 
                ),array(
                'source'=>41,
                'es_message'=>'La predicción ganó', 
                ),array(
                'source'=>50,
                'es_message'=>'Bonus Efectivo otorgado por registro', 
                ),array(
                'source'=>51,
                'es_message'=>'Real efectivo otorgado por registro', 
                ),array(
                'source'=>52,
                'es_message'=>'Monedas de referencia otorgadas en el registro de un amigo', 
                ),array(
                'source'=>53,
                'es_message'=>'Bono de referencia efectivo otorgado en el registro por un amigo', 
                ),array(
                'source'=>54,
                'es_message'=>'Referencia real en efectivo otorgado en el registro por un amigo', 
                ),array(
                'source'=>55,
                'es_message'=>'Monedas de referencia otorgadas en el registro de un amigo', 
                ),array(
                'source'=>56,
                'es_message'=>'Bonus Efectivo otorgado por registro', 
                ),array(
                'source'=>57,
                'es_message'=>'Real efectivo otorgado por registro', 
                ),array(
                'source'=>58,
                'es_message'=>'Monedas de referencia otorgadas en el registro de un amigo', 
                ),array(
                'source'=>59,
                'es_message'=>'Bonus Efectivo otorgado por {{p_to_id}} Verificación de la tarjeta', 
                ),array(
                'source'=>60,
                'es_message'=>'Efectivo real otorgado por {{p_to_id}} verificación de la tarjeta', 
                ),array(
                'source'=>61,
                'es_message'=>'Monedas otorgadas por {{p_to_id}} verificación de la tarjeta', 
                ),array(
                'source'=>62,
                'es_message'=>'Bonus Efectivo otorgado en {{p_to_id}} Verificación de la tarjeta por amigo', 
                ),array(
                'source'=>63,
                'es_message'=>'Real efectivo otorgado en {{p_to_id}} Verificación de la tarjeta por amigo', 
                ),array(
                'source'=>64,
                'es_message'=>'Monedas otorgadas en {{p_to_id}} Verificación de la tarjeta por amigo', 
                ),array(
                'source'=>65,
                'es_message'=>'Bonus Efectivo otorgado por {{p_to_id}} Verificación de la tarjeta', 
                ),array(
                'source'=>66,
                'es_message'=>'Efectivo real otorgado por {{p_to_id}} verificación de la tarjeta', 
                ),array(
                'source'=>67,
                'es_message'=>'Monedas otorgadas por {{p_to_id}} verificación de la tarjeta', 
                ),array(
                'source'=>68,
                'es_message'=>'Únete a un concurso de efectivo por amigo', 
                ),array(
                'source'=>69,
                'es_message'=>'Únete a un concurso de efectivo por amigo', 
                ),array(
                'source'=>70,
                'es_message'=>'Únete a un concurso de efectivo por amigo', 
                ),array(
                'source'=>71,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>72,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>73,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>74,
                'es_message'=>'Únete a un concurso de efectivo por amigo', 
                ),array(
                'source'=>75,
                'es_message'=>'Únete a un concurso de efectivo por amigo', 
                ),array(
                'source'=>76,
                'es_message'=>'Únete a un concurso de efectivo por amigo', 
                ),array(
                'source'=>77,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>78,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>79,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>80,
                'es_message'=>'Únete a un concurso de efectivo por amigo', 
                ),array(
                'source'=>81,
                'es_message'=>'Únete a un concurso de efectivo por amigo', 
                ),array(
                'source'=>82,
                'es_message'=>'Únete a un concurso de efectivo por amigo', 
                ),array(
                'source'=>83,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>84,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>85,
                'es_message'=>'Únete a un concurso de efectivo por amigo', 
                ),array(
                'source'=>86,
                'es_message'=>'Bonus Efectivo otorgado para la verificación por correo electrónico', 
                ),array(
                'source'=>87,
                'es_message'=>'Real efectivo otorgado para la verificación por correo electrónico', 
                ),array(
                'source'=>88,
                'es_message'=>'Monedas de bonificación otorgadas para la verificación por correo electrónico', 
                ),array(
                'source'=>89,
                'es_message'=>'Bonus Efectivo otorgado en la verificación de correo electrónico por amigo', 
                ),array(
                'source'=>90,
                'es_message'=>'Real efectivo otorgado en la verificación de correo electrónico por amigo', 
                ),array(
                'source'=>91,
                'es_message'=>'Monedas otorgadas en la verificación de correo electrónico por amigo', 
                ),array(
                'source'=>92,
                'es_message'=>'Bonus Efectivo otorgado para la verificación por correo electrónico', 
                ),array(
                'source'=>93,
                'es_message'=>'Real efectivo otorgado para la verificación por correo electrónico', 
                ),array(
                'source'=>94,
                'es_message'=>'Monedas de bonificación otorgadas para la verificación por correo electrónico', 
                ),array(
                'source'=>95,
                'es_message'=>'Bonus Efectivo otorgado para el depósito de amigos', 
                ),array(
                'source'=>96,
                'es_message'=>'Real efectivo otorgado para el depósito de amigos', 
                ),array(
                'source'=>97,
                'es_message'=>'Monedas otorgadas para el depósito de amigos', 
                ),array(
                'source'=>98,
                'es_message'=>'Bonus Efectivo otorgado en depósito por amigo', 
                ),array(
                'source'=>99,
                'es_message'=>'Real efectivo otorgado en depósito por amigo', 
                ),array(
                'source'=>100,
                'es_message'=>'Monedas otorgadas en depósito por amigo', 
                ),array(
                'source'=>102,
                'es_message'=>'Pedir cancelación (reembolso)', 
                ),array(
                'source'=>105,
                'es_message'=>'Bonus Efectivo otorgado para el depósito de amigos', 
                ),array(
                'source'=>106,
                'es_message'=>'Real efectivo otorgado para el depósito de amigos', 
                ),array(
                'source'=>107,
                'es_message'=>'Monedas otorgadas para el depósito de amigos', 
                ),array(
                'source'=>132,
                'es_message'=>'Bonificación para la verificación {{b_to_c}}', 
                ),array(
                'source'=>133,
                'es_message'=>'Cantidad real para {{b_to_c}} verificación', 
                ),array(
                'source'=>134,
                'es_message'=>'Monedas para {{b_to_c}} verificación', 
                ),array(
                'source'=>135,
                'es_message'=>'Bonificación por el trato', 
                ),array(
                'source'=>136,
                'es_message'=>'Real para el trato', 
                ),array(
                'source'=>137,
                'es_message'=>'Monedas para el trato', 
                ),array(
                'source'=>138,
                'es_message'=>'Bonificación para la verificación {{b_to_c}}', 
                ),array(
                'source'=>139,
                'es_message'=>'Cantidad real para {{b_to_c}} verificación', 
                ),array(
                'source'=>140,
                'es_message'=>'Monedas para {{b_to_c}} verificación', 
                ),array(
                'source'=>141,
                'es_message'=>'Bonificación para la verificación {{b_to_c}}', 
                ),array(
                'source'=>142,
                'es_message'=>'Cantidad real para {{b_to_c}} verificación', 
                ),array(
                'source'=>143,
                'es_message'=>'Monedas para {{b_to_c}} verificación', 
                ),array(
                'source'=>144,
                'es_message'=>'Monedas de racha diaria', 
                ),array(
                'source'=>145,
                'es_message'=>'Bonificación recibida por redimencias de monedas', 
                ),array(
                'source'=>146,
                'es_message'=>'Cantidad real recibida por monedas redimidas', 
                ),array(
                'source'=>147,
                'es_message'=>'Deducto de monedas en monedas redimidas', 
                ),array(
                'source'=>151,
                'es_message'=>'Monedas agregadas en la retroalimentación aprobada', 
                ),array(
                'source'=>153,
                'es_message'=>'Editar recompensa del código de referencia - bonificación', 
                ),array(
                'source'=>154,
                'es_message'=>'Editar recompensa del código de referencia - Efectivo real', 
                ),array(
                'source'=>155,
                'es_message'=>'Editar recompensa del código de referencia - Monedas', 
                ),array(
                'source'=>156,
                'es_message'=>'5ta referencia de registro - Bonificación', 
                ),array(
                'source'=>157,
                'es_message'=>'5ta referencia de registro: efectivo real', 
                ),array(
                'source'=>158,
                'es_message'=>'5ª referencia de registro - Monedas', 
                ),array(
                'source'=>159,
                'es_message'=>'Décima referencia de registro - bonificación', 
                ),array(
                'source'=>160,
                'es_message'=>'Décima referencia de registro - efectivo real', 
                ),array(
                'source'=>161,
                'es_message'=>'Décima referencia de registro - monedas', 
                ),array(
                'source'=>162,
                'es_message'=>'15 ° referencia de registro - bonificación', 
                ),array(
                'source'=>163,
                'es_message'=>'15 ° referencia de registro: efectivo real', 
                ),array(
                'source'=>164,
                'es_message'=>'15 ° referencia de registro - Monedas', 
                ),array(
                'source'=>165,
                'es_message'=>'Verificación del teléfono - Bonificación', 
                ),array(
                'source'=>166,
                'es_message'=>'Verificación del teléfono: efectivo real', 
                ),array(
                'source'=>167,
                'es_message'=>'Verificación del teléfono - Monedas', 
                ),array(
                'source'=>168,
                'es_message'=>'Referencia de verificación telefónica - bonificación', 
                ),array(
                'source'=>169,
                'es_message'=>'Referencia de verificación telefónica: efectivo real', 
                ),array(
                'source'=>170,
                'es_message'=>'Referencia de verificación telefónica: monedas', 
                ),array(
                'source'=>171,
                'es_message'=>'Referencia de verificación telefónica - bonificación', 
                ),array(
                'source'=>172,
                'es_message'=>'Referencia de verificación telefónica: efectivo real', 
                ),array(
                'source'=>173,
                'es_message'=>'Referencia de verificación telefónica: monedas', 
                ),array(
                'source'=>174,
                'es_message'=>'Reembolso de la tarifa de entrada para la cancelación de predicción', 
                ),array(
                'source'=>181,
                'es_message'=>'Ganado para Pick\'em of Game {{home}} vs {{away}} {{match_date}}', 
                ),array(
                'source'=>184,
                'es_message'=>'Retiro de administrador', 
                ),array(
                'source'=>220,
                'es_message'=>'Bet Coins para la predicción', 
                ),array(
                'source'=>221,
                'es_message'=>'La predicción ganó', 
                ),array(
                'source'=>224,
                'es_message'=>'Reembolso de la tarifa de entrada para la cancelación de predicción', 
                ),array(
                'source'=>225,
                'es_message'=>'Ganancias de la tabla de clasificación de predicción', 
                ),array(
                'source'=>226,
                'es_message'=>'Ganancias de la tabla de clasificación de predicción', 
                ),array(
                'source'=>227,
                'es_message'=>'Ganancias de la tabla de clasificación de predicción', 
                ),array(
                'source'=>230,
                'es_message'=>'Mini-liga ganó', 
                ),array(
                'source'=>240,
                'es_message'=>'Tarifa de entrada para %s', 
                ),array(
                'source'=>241,
                'es_message'=>'Premio del concurso de ganancias', 
                ),array(
                'source'=>242,
                'es_message'=>'Reembolso de tarifas para el concurso', 
                ),array(
                'source'=>250,
                'es_message'=>'Bet for Pick\'em of Game {{home}} vs {{away}} {{match_date}}', 
                ),array(
                'source'=>251,
                'es_message'=>'Reembolsado para Pick\'em of Game {{home}} vs {{away}} {{match_date}}', 
                ),array(
                'source'=>264,
                'es_message'=>'Won {{entity_no}} {{type}} Redieve de referencia', 
                ),array(
                'source'=>265,
                'es_message'=>'Won {{entity_no}} {{type}} Fantasy Raeperboard', 
                ),array(
                'source'=>270,
                'es_message'=>'Un concurso de efectivo unido por amigo', 
                ),array(
                'source'=>271,
                'es_message'=>'Un concurso de efectivo unido por amigo', 
                ),array(
                'source'=>272,
                'es_message'=>'Un concurso de efectivo unido por amigo', 
                ),array(
                'source'=>273,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>274,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>275,
                'es_message'=>'Únete a un concurso de efectivo', 
                ),array(
                'source'=>276,
                'es_message'=>'Beneficio semanal adicional en el concurso de amigos.', 
                ),array(
                'source'=>277,
                'es_message'=>'Beneficio semanal adicional en el concurso de amigos.', 
                ),array(
                'source'=>278,
                'es_message'=>'Beneficio semanal adicional en el concurso de amigos.', 
                ),array(
                'source'=>279,
                'es_message'=>'Beneficio semanal adicional en el concurso.', 
                ),array(
                'source'=>280,
                'es_message'=>'Beneficio semanal adicional en el concurso.', 
                ),array(
                'source'=>281,
                'es_message'=>'Beneficio semanal adicional en el concurso.', 
                ),array(
                'source'=>282,
                'es_message'=>'Comprar moneda', 
                ),array(
                'source'=>283,
                'es_message'=>'Cantidad de débito para la moneda de compra', 
                ),array(
                'source'=>320,
                'es_message'=>'Comisión para el registro del usuario a través del programa de afiliados', 
                ),array(
                'source'=>321,
                'es_message'=>'Comisión para el depósito del usuario a través del programa de afiliados', 
                ),array(
                'source'=>325,
                'es_message'=>'En la compra de monedas de aplicaciones', 
                ),array(
                'source'=>370,
                'es_message'=>'Tarifa de entrada para %s', 
                ),array(
                'source'=>371,
                'es_message'=>'Reembolso de tarifas para %s torneo', 
                ),array(
                'source'=>372,
                'es_message'=>'Premio del torneo Won', 
                ),array(
                'source'=>373,
                'es_message'=>'Torneo DFS: TD TDS deducido', 
                ),array(
                'source'=>381,
                'es_message'=>'Ganado en Scratch & Win', 
                ),array(
                'source'=>401,
                'es_message'=>'Ganó el premio diario de la tabla de clasificación', 
                ),array(
                'source'=>402,
                'es_message'=>'Ganó el premio semanal de la tabla de clasificación', 
                ),array(
                'source'=>403,
                'es_message'=>'Ganó el premio mensual de la tabla de clasificación', 
                ),array(
                'source'=>404,
                'es_message'=>'Tabla de clasificación de pickem: TDS totales deducidos', 
                ),array(
                'source'=>437,
                'es_message'=>'En la compra de monedas de aplicaciones', 
                ),array(
                'source'=>450,
                'es_message'=>'Monedas acreditadas para el nivel-{{level_number}} promoción', 
                ),array(
                'source'=>451,
                'es_message'=>'Beneficio de depósito de devolución de efectivo para el nivel-{{level_number}}', 
                ),array(
                'source'=>452,
                'es_message'=>'Concurso Unir el beneficio de reembolso para el nivel-{{level_number}}', 
                ),array(
                'source'=>460,
                'es_message'=>'Tarifa de entrada para %s', 
                ),array(
                'source'=>461,
                'es_message'=>'Reembolso de tarifas para el concurso', 
                ),array(
                'source'=>462,
                'es_message'=>'Premio del concurso de ganancias', 
                ),array(
                'source'=>463,
                'es_message'=>'TDS total deducido', 
                ),array(
                'source'=>464,
                'es_message'=>'Won {{entity_no}} {{type}} Stock Raeperboard', 
                ),array(
                'source'=>465,
                'es_message'=>'Won {{entity_no}} {{type}} Stock Equity Raeperboard', 
                ),array(
                'source'=>466,
                'es_message'=>'Won {{entity_no}} {{type}} stock Predicte de clasificación', 
                ),array(
                'source'=>470,
                'es_message'=>'Monedas obtenidas en cuestionario de {{Scheduled_Date}}', 
                ),array(
                'source'=>471,
                'es_message'=>'Monedas acreditadas para la descarga de aplicaciones.', 
                ),array(
                'source'=>475,
                'es_message'=>'moneda expirada', 
                ),array(
                'source'=>500,
                'es_message'=>'Fantasía en vivo  {{match}} sobre {{over}} concurso {{contest}} unido', 
                ),array(
                'source'=>501,
                'es_message'=>'Reembolso de la tarifa de cancelación del concurso. Fantasía en vivo  {{match}} sobre {{over}} {{contest}}', 
                ),array(
                'source'=>502,
                'es_message'=>'Fantasía en vivo  {{match}} sobre {{over}} Credit ganador del concurso', 
                ),array(
                'source'=>503,
                'es_message'=>'Comisión de concurso privado de fantasía en vivo', 
                ),array(
                'source'=>504,
                'es_message'=>'Deducción de tds de fantasía en vivo', 
                ),array(
                'source'=>505,
                'es_message'=>'Props Fantasy  {{match}} concurso {{contest}} unido', 
                ),array(
                'source'=>506,
                'es_message'=>'Props Fantasy  {{match}} concurso {{contest}} reembolso de la tarifa de cancelación', 
                ),array(
                'source'=>507,
                'es_message'=>'Props Fantasy  {{match}} Credit ganador del concurso', 
                ),array(
                'source'=>508,
                'es_message'=>'Comisión de concurso privado de fantasía de accesorios', 
                ),array(
                'source'=>509,
                'es_message'=>'Props Deducción de TDS de fantasía', 
                ),array(
                'source'=>510,
                'es_message'=>'Concurso de borrador de serpiente {{contest}} unido', 
                ),array(
                'source'=>511,
                'es_message'=>'Snake Draft Concurso {{contest}} reembolso de la tarifa de cancelación', 
                ),array(
                'source'=>512,
                'es_message'=>'Concurso de Draft Snake {{contest}} crédito ganador', 
                ),array(
                'source'=>513,
                'es_message'=>'Comisión de concursos privados del borrador de serpiente', 
                ),array(
                'source'=>514,
                'es_message'=>'Deducción del borrador de serpiente TDS', 
                ),
        );
		
        $this->db->update_batch(TRANSACTION_MESSAGES,$transaction_msg_data,'source');

        $notification_message_data = array(
            array(
                'notification_type'=>0,
                'es_message'=>'Categoría de custome creada por administrador',
                ),array(
                'notification_type'=>1,
                'es_message'=>'Juego {{contest_name}} - {{collection_name}} unido con éxito',
                ),array(
                'notification_type'=>2,
                'es_message'=>'El concurso {{contest_name}} se ha cancelado debido a una participación insuficiente',
                ),array(
                'notification_type'=>3,
                'es_message'=>'¡Felicidades! Eres un ganador en el juego {{collection_name}}.',
                ),array(
                'notification_type'=>4,
                'es_message'=>'Felicidades ! Ha recibido una bonificación de {{amount}} para referir a su amigo {{name}} en nuestro sitio',
                ),array(
                'notification_type'=>6,
                'es_message'=>'₹ {{amount}} se ha depositado a su cuenta de la transacción reciente {{reason}}',
                ),array(
                'notification_type'=>7,
                'es_message'=>'El retiro iniciado por ₹ {{amount}}, el monto se debita desde el saldo de su sitio.',
                ),array(
                'notification_type'=>8,
                'es_message'=>'Está invitado a unirse {{contest_name}}. Haga clic para unirse.',
                ),array(
                'notification_type'=>9,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido al sitio. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>10,
                'es_message'=>'Jugador lesionado',
                ),array(
                'notification_type'=>11,
                'es_message'=>'Cambio de club',
                ),array(
                'notification_type'=>12,
                'es_message'=>'Partido pospuesto',
                ),array(
                'notification_type'=>13,
                'es_message'=>'Jugador suspendido',
                ),array(
                'notification_type'=>14,
                'es_message'=>'Inscribirse',
                ),array(
                'notification_type'=>15,
                'es_message'=>'Has olvidado tu contraseña',
                ),array(
                'notification_type'=>16,
                'es_message'=>'Usuario de administración invitada',
                ),array(
                'notification_type'=>17,
                'es_message'=>'Distribuidor de administración invitada',
                ),array(
                'notification_type'=>18,
                'es_message'=>'Amigo Refferal Invite',
                ),array(
                'notification_type'=>19,
                'es_message'=>'{{message}}',
                ),array(
                'notification_type'=>20,
                'es_message'=>'{{match_name}} ¡ Por favor, vea como fue',
                ),array(
                'notification_type'=>22,
                'es_message'=>'Su (s) concurso (s) se han visto afectados como Match {{match_name}} se retrasan debido a la lluvia.',
                ),array(
                'notification_type'=>23,
                'es_message'=>'Su concurso {{contest_name}} ha sido cancelado debido a la cancelación de los partidos (s). Su tarifa de entrada ha sido devuelta a su saldo.',
                ),array(
                'notification_type'=>24,
                'es_message'=>'Su (s) concurso (s) se han cancelado debido a la abandonada de Match {{match_name}}. Su tarifa de entrada ha sido devuelta a su saldo.',
                ),array(
                'notification_type'=>25,
                'es_message'=>'Se ha aprobado su solicitud de retiro de {{amount}}',
                ),array(
                'notification_type'=>26,
                'es_message'=>'Su solicitud de retiro de ₹ {{amount}} ha sido rechazada. ({{reason}})',
                ),array(
                'notification_type'=>27,
                'es_message'=>'Felicidades ! Has sido recompensado {{amount}} coin (s) {{reason}}',
                ),array(
                'notification_type'=>28,
                'es_message'=>'₹ {{amount}} {{reason}} debitado desde el saldo de su moneda',
                ),array(
                'notification_type'=>29,
                'es_message'=>'La liga {{contest_name}} se ha trasladado a la próxima temporada.',
                ),array(
                'notification_type'=>30,
                'es_message'=>'{{name}} te ha invitado a unir concursos de {{collection_name}}',
                ),array(
                'notification_type'=>31,
                'es_message'=>'{{name}} te ha invitado a unirte {{contest_name}}.',
                ),array(
                'notification_type'=>33,
                'es_message'=>'Felicidades ! Bonificación de registro {{amount}} deposite con éxito en su cuenta!',
                ),array(
                'notification_type'=>34,
                'es_message'=>'Felicidades ! {{name}} referido por usted, tiene un número de teléfono verificado en el sitio. Has ganado {{amount}} Bonus.',
                ),array(
                'notification_type'=>35,
                'es_message'=>'Felicidades ! {{name}} referido por usted, ha verificado Pancard en el sitio. Has ganado {{amount}} Bonus.',
                ),array(
                'notification_type'=>36,
                'es_message'=>'¡Felicidades! El concurso referido por usted se ha unido al usuario. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>37,
                'es_message'=>'¡Felicidades! La colección referida por usted se ha unido por el usuario. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>38,
                'es_message'=>'Invitación de registro del administrador',
                ),array(
                'notification_type'=>39,
                'es_message'=>'Se le ha otorgado una entrada gratuita para {{contest_name}}.',
                ),array(
                'notification_type'=>42,
                'es_message'=>'Su pago ha fallado.',
                ),array(
                'notification_type'=>43,
                'es_message'=>'¡Nos complace anunciar una nueva función lanzada en nuestro sitio web!',
                ),array(
                'notification_type'=>44,
                'es_message'=>'Tu pancard ha sido rechazado. Razón: {{pan_rejected_reason}}',
                ),array(
                'notification_type'=>45,
                'es_message'=>'Concurso {{contest_name}} ya estaba lleno',
                ),array(
                'notification_type'=>50,
                'es_message'=>'Regístrese en efectivo de bonos {{amount}} acreditado en su cuenta.',
                ),array(
                'notification_type'=>51,
                'es_message'=>'Regístrese en efectivo real ₹ {{amount}} acreditado en su cuenta.',
                ),array(
                'notification_type'=>52,
                'es_message'=>'Registre monedas {{amount}} acreditadas en su cuenta.',
                ),array(
                'notification_type'=>53,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido al sitio. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>54,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido al sitio. Has ganado zón {{amount}} Efectivo real.',
                ),array(
                'notification_type'=>55,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido al sitio. Has ganado {{amount}} monedas.',
                ),array(
                'notification_type'=>56,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado extra {{amount}} bono en efectivo.',
                ),array(
                'notification_type'=>57,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado ₹ {{amount}} efectivo real.',
                ),array(
                'notification_type'=>58,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado monedas adicionales {{amount}}.',
                ),array(
                'notification_type'=>59,
                'es_message'=>'Bonificación de verificación de pancard en efectivo {{amount}} acreditado en su cuenta.',
                ),array(
                'notification_type'=>60,
                'es_message'=>'Pancard Verificación Real Efectivo ₹ {{amount}} acreditado en su cuenta.',
                ),array(
                'notification_type'=>61,
                'es_message'=>'Monedas de verificación Pancard {{amount}} acreditadas en su cuenta.',
                ),array(
                'notification_type'=>62,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha verificado su pancard. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>63,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha verificado su pancard. Has ganado zón {{amount}} Efectivo real.',
                ),array(
                'notification_type'=>64,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha verificado su pancard. Has ganado {{amount}} monedas.',
                ),array(
                'notification_type'=>65,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado extra {{amount}} en efectivo de bonificación para verificar el Pancard.',
                ),array(
                'notification_type'=>66,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado ₹ {{amount}} efectivo real para verificar el pancard.',
                ),array(
                'notification_type'=>67,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado monedas adicionales {{amount}} para verificar el Pancard.',
                ),array(
                'notification_type'=>68,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>69,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado zón {{amount}} Efectivo real.',
                ),array(
                'notification_type'=>70,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado {{amount}} monedas.',
                ),array(
                'notification_type'=>71,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado extra {{amount}} bono en efectivo para unir el concurso de efectivo.',
                ),array(
                'notification_type'=>72,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado ₹ {{amount}} efectivo real para unir el concurso de efectivo.',
                ),array(
                'notification_type'=>73,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha obtenido monedas adicionales {{amount}} para unir el concurso de efectivo.',
                ),array(
                'notification_type'=>74,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>75,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado zón {{amount}} Efectivo real.',
                ),array(
                'notification_type'=>76,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado {{amount}} monedas.',
                ),array(
                'notification_type'=>77,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado extra {{amount}} bono en efectivo para unir el concurso de efectivo.',
                ),array(
                'notification_type'=>78,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado ₹ {{amount}} efectivo real para unir el concurso de efectivo.',
                ),array(
                'notification_type'=>79,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha obtenido monedas adicionales {{amount}} para unir el concurso de efectivo.',
                ),array(
                'notification_type'=>80,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>81,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado zón {{amount}} Efectivo real.',
                ),array(
                'notification_type'=>82,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado {{amount}} monedas.',
                ),array(
                'notification_type'=>83,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado extra {{amount}} bono en efectivo para unir el concurso de efectivo.',
                ),array(
                'notification_type'=>84,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha ganado ₹ {{amount}} efectivo real para unir el concurso de efectivo.',
                ),array(
                'notification_type'=>85,
                'es_message'=>'¡Viva! Al usar el código de referencia, ha obtenido monedas adicionales {{amount}} para unir el concurso de efectivo.',
                ),array(
                'notification_type'=>86,
                'es_message'=>'Ha ganado {{amount}} Bonus Efectivo verificando su ID de correo electrónico.',
                ),array(
                'notification_type'=>87,
                'es_message'=>'Usted ha ganado ₹ {{amount}} Efectivo real verificando su ID de correo electrónico.',
                ),array(
                'notification_type'=>88,
                'es_message'=>'Ha ganado {{amount}} monedas verificando su ID de correo electrónico.',
                ),array(
                'notification_type'=>89,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha verificado su correo electrónico. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>90,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha verificado su correo electrónico. Has ganado zón {{amount}} Efectivo real.',
                ),array(
                'notification_type'=>91,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha verificado su correo electrónico. Has ganado {{amount}} monedas.',
                ),array(
                'notification_type'=>92,
                'es_message'=>'Ha ganado {{amount}} Bonus Efectivo verificando su ID de correo electrónico.',
                ),array(
                'notification_type'=>93,
                'es_message'=>'Usted ha ganado ₹ {{amount}} Efectivo real verificando su ID de correo electrónico.',
                ),array(
                'notification_type'=>94,
                'es_message'=>'Ha ganado {{amount}} monedas verificando su ID de correo electrónico.',
                ),array(
                'notification_type'=>95,
                'es_message'=>'¡Felicitaciones por su primer depósito! Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>96,
                'es_message'=>'¡Felicitaciones por su primer depósito! Has ganado zón {{amount}} Efectivo real.',
                ),array(
                'notification_type'=>97,
                'es_message'=>'¡Felicitaciones por su primer depósito! Has ganado {{amount}} monedas.',
                ),array(
                'notification_type'=>98,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha depositado. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>99,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha depositado. Has ganado zón {{amount}} Efectivo real.',
                ),array(
                'notification_type'=>100,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha depositado. Has ganado {{amount}} monedas.',
                ),array(
                'notification_type'=>101,
                'es_message'=>'Felicidades ! Su orden- {{product_name}} (id: {{product_order_unique_id}}) se ha colocado.',
                ),array(
                'notification_type'=>102,
                'es_message'=>'Su pedido- {{product_name}} (id: {{product_order_unique_id}}) ha sido cancelado.',
                ),array(
                'notification_type'=>103,
                'es_message'=>'¡Felicitaciones! Su orden- {{product_name}} (id: {{product_order_unique_id}}) se ha completado.',
                ),array(
                'notification_type'=>105,
                'es_message'=>'¡Felicitaciones por su primer depósito! Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>106,
                'es_message'=>'¡Felicitaciones por su primer depósito! Has ganado zón {{amount}} Efectivo real.',
                ),array(
                'notification_type'=>107,
                'es_message'=>'¡Felicitaciones por su primer depósito! Has ganado {{amount}} monedas.',
                ),array(
                'notification_type'=>120,
                'es_message'=>'Promoción para depósito',
                ),array(
                'notification_type'=>121,
                'es_message'=>'Promoción para el concurso',
                ),array(
                'notification_type'=>122,
                'es_message'=>'Promoción del accesorio',
                ),array(
                'notification_type'=>123,
                'es_message'=>'Recomendar un amigo',
                ),array(
                'notification_type'=>124,
                'es_message'=>'Promoción para el primer depósito',
                ),array(
                'notification_type'=>125,
                'es_message'=>'El concurso {{contest_name}} ha sido cancelado por el administrador. Se ha enviado una razón a su correo electrónico.',
                ),array(
                'notification_type'=>130,
                'es_message'=>'₹ {{amount}} deducido como TDS',
                ),array(
                'notification_type'=>132,
                'es_message'=>'El lanzamiento tuvo lugar para el partido {{collection_name}}, y se anuncian los equipos. Puede editar su equipo hasta que el partido comience en {{FRONTEND_BITLY_URL}}. Juego encendido!',
                ),array(
                'notification_type'=>134,
                'es_message'=>'Personalizado',
                ),array(
                'notification_type'=>135,
                'es_message'=>'Nota personalizada',
                ),array(
                'notification_type'=>136,
                'es_message'=>'El administrador ha rechazado sus datos bancarios',
                ),array(
                'notification_type'=>137,
                'es_message'=>'Su cuenta bloqueada por administrador',
                ),array(
                'notification_type'=>138,
                'es_message'=>'Ha recibido {{amount}} monedas para el día de chequeo diario {{day_number}}',
                ),array(
                'notification_type'=>139,
                'es_message'=>'Has recibido {{amount}} Bonificación para redimir monedas',
                ),array(
                'notification_type'=>140,
                'es_message'=>'Has recibido {{amount}} real para {{event}}',
                ),array(
                'notification_type'=>141,
                'es_message'=>'{{amount}} monedas deducidas para {{event}}',
                ),array(
                'notification_type'=>142,
                'es_message'=>'Ha recibido {{amount}} bono para la verificación bancaria',
                ),array(
                'notification_type'=>143,
                'es_message'=>'Ha recibido {{amount}} efectivo real para la verificación bancaria',
                ),array(
                'notification_type'=>144,
                'es_message'=>'Ha recibido {{amount}} monedas para la verificación bancaria',
                ),array(
                'notification_type'=>145,
                'es_message'=>'Ha recibido {{amount}} Bonificación para la verificación bancaria de su amigo',
                ),array(
                'notification_type'=>146,
                'es_message'=>'Ha recibido {{amount}} efectivo real para la verificación bancaria por parte de su amigo',
                ),array(
                'notification_type'=>147,
                'es_message'=>'Hemos recibido {{amount}} monedas para la verificación bancaria de su amigo',
                ),array(
                'notification_type'=>148,
                'es_message'=>'Ha recibido {{amount}} bono para la verificación bancaria',
                ),array(
                'notification_type'=>149,
                'es_message'=>'Ha recibido {{amount}} efectivo real para la verificación bancaria',
                ),array(
                'notification_type'=>150,
                'es_message'=>'Ha recibido {{amount}} monedas para la verificación bancaria',
                ),array(
                'notification_type'=>151,
                'es_message'=>'Ha recibido monedas {{amount}} para la aprobación de retroalimentación del administrador.',
                ),array(
                'notification_type'=>153,
                'es_message'=>'Ha recibido una bonificación {{amount}} para editar su código de referencia',
                ),array(
                'notification_type'=>154,
                'es_message'=>'Ha recibido {{amount}} efectivo real para editar su código de referencia',
                ),array(
                'notification_type'=>155,
                'es_message'=>'Ha recibido monedas {{amount}} para editar su código de referencia',
                ),array(
                'notification_type'=>156,
                'es_message'=>'¡Súper! Has logrado un hito de la quinta referencia exitosa y recibió {{amount}} bono',
                ),array(
                'notification_type'=>157,
                'es_message'=>'¡Súper! Has logrado un hito de la quinta referencia exitosa y recibió {{amount}} Real Efectivo',
                ),array(
                'notification_type'=>158,
                'es_message'=>'¡Súper! Ha logrado un hito de la quinta referencia exitosa y recibió {{amount}} monedas',
                ),array(
                'notification_type'=>159,
                'es_message'=>'¡Súper! Has logrado un hito de la décima referencia exitosa y recibió {{amount}} bono',
                ),array(
                'notification_type'=>160,
                'es_message'=>'¡Súper! Has logrado un hito de la décima referencia exitosa y recibió {{amount}} Real Efectivo',
                ),array(
                'notification_type'=>161,
                'es_message'=>'¡Súper! Ha logrado un hito de la décima referencia exitosa y recibió {{amount}} monedas',
                ),array(
                'notification_type'=>162,
                'es_message'=>'¡Súper! Has logrado un hito de la 15ª referencia exitosa y recibió {{amount}} bono',
                ),array(
                'notification_type'=>163,
                'es_message'=>'¡Súper! Has logrado un hito de la 15ª referencia exitosa y recibió {{amount}} Real Efectivo',
                ),array(
                'notification_type'=>164,
                'es_message'=>'¡Súper! Ha logrado un hito de la 15ª referencia exitosa y recibió {{amount}} monedas',
                ),array(
                'notification_type'=>165,
                'es_message'=>'Ha ganado {{amount}} bono en efectivo verificando su número de teléfono',
                ),array(
                'notification_type'=>166,
                'es_message'=>'Ha ganado {{{amount}} efectivo real verificando su número de teléfono',
                ),array(
                'notification_type'=>167,
                'es_message'=>'Ha ganado {{amount}} monedas en efectivo verificando su número de teléfono',
                ),array(
                'notification_type'=>168,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha verificado su número de teléfono. Ha ganado {{amount}} bono en efectivo',
                ),array(
                'notification_type'=>169,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha verificado su número de teléfono. Has ganado {{amount}} Efectivo real',
                ),array(
                'notification_type'=>170,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted ha verificado su número de teléfono. Has ganado {{amount}} monedas',
                ),array(
                'notification_type'=>171,
                'es_message'=>'Ha ganado {{amount}} bono en efectivo verificando su número de teléfono',
                ),array(
                'notification_type'=>172,
                'es_message'=>'Ha ganado {{{amount}} efectivo real verificando su número de teléfono',
                ),array(
                'notification_type'=>173,
                'es_message'=>'Ha ganado {{amount}} monedas en efectivo verificando su número de teléfono',
                ),array(
                'notification_type'=>174,
                'es_message'=>'Ha recibido {{amount}} monedas como reembolso para cancelar la predicción por admin',
                ),array(
                'notification_type'=>175,
                'es_message'=>'{{question}} Predecir ahora!',
                ),array(
                'notification_type'=>176,
                'es_message'=>'¡Oye! Use sus habilidades y predice en {{match}} coincidencia, ¡las predicciones están en vivo ahora!',
                ),array(
                'notification_type'=>181,
                'es_message'=>'Su elección {{correct_answer}} es correcta para el juego {{home}} vs {{away}} {{date}}.',
                ),array(
                'notification_type'=>183,
                'es_message'=>'Felicitaciones por predecir la respuesta correcta por {{home}} vs {{away}} coincidencia. Mira los resultados.',
                ),array(
                'notification_type'=>184,
                'es_message'=>'Retiro de administrador {{amount}} de su cuenta',
                ),array(
                'notification_type'=>200,
                'es_message'=>'¡Te has unido al juego {{contest_name}} de {{tournament_name}} con éxito!',
                ),array(
                'notification_type'=>201,
                'es_message'=>'¡Felicidades! Eres un ganador en el juego {{tournament_name}}.',
                ),array(
                'notification_type'=>202,
                'es_message'=>'El concurso {{contest_name}} se ha cancelado debido a una participación insuficiente',
                ),array(
                'notification_type'=>203,
                'es_message'=>'El concurso {{contest_name}} ha sido cancelado por el administrador. Se ha enviado una razón a su correo electrónico.',
                ),array(
                'notification_type'=>220,
                'es_message'=>'Ha recibido {{amount}} monedas como reembolso para cancelar la predicción por admin',
                ),array(
                'notification_type'=>221,
                'es_message'=>'{{question}} Predecir ahora!',
                ),array(
                'notification_type'=>222,
                'es_message'=>'¡Oye! Use sus habilidades y predice en {{category}}, ¡las predicciones están en vivo ahora!',
                ),array(
                'notification_type'=>223,
                'es_message'=>'Felicitaciones por predecir la respuesta correcta por {{category}}. Mira los resultados.',
                ),array(
                'notification_type'=>224,
                'es_message'=>'Ha recibido {{amount}} monedas como reembolso para cancelar la predicción por admin',
                ),array(
                'notification_type'=>225,
                'es_message'=>'¡Felicidades! Has ganado {{amount}} en la clasificación diaria de {{start_date}} logrando {{rank_value}} rango.',
                ),array(
                'notification_type'=>226,
                'es_message'=>'¡Felicidades! Has ganado {{amount}} en la tabla de clasificación semanal de la semana {{start_date}} a {{end_date}} logrando {{rank_value}} rango.',
                ),array(
                'notification_type'=>227,
                'es_message'=>'¡Felicidades! Has ganado {{amount}} en la tabla de clasificación mensual de {{start_date}} mes logrando el rango {{rank_value}}.',
                ),array(
                'notification_type'=>230,
                'es_message'=>'¡Felicidades! Eres un ganador en {{mini_league_name}} mini-league',
                ),array(
                'notification_type'=>231,
                'es_message'=>'Mini-liga {{mini_league_name}} Únete con éxito',
                ),array(
                'notification_type'=>240,
                'es_message'=>'¡Oye! Use sus habilidades y predice en {{category}}, ¡las predicciones están en vivo ahora!',
                ),array(
                'notification_type'=>241,
                'es_message'=>'Felicitaciones por predecir la respuesta correcta por {{category}}. Mira los resultados.',
                ),array(
                'notification_type'=>250,
                'es_message'=>'Juego {{contest_name}} Únete con éxito',
                ),array(
                'notification_type'=>251,
                'es_message'=>'¡Ha recibido {{amount}} monedas como reembolso al cancelar Pick\'em por admin!',
                ),array(
                'notification_type'=>252,
                'es_message'=>'¡Ups! Eligió {{user_selected_option}} incorrecto para el juego {{home}} vs {{away}} {{match_date}}. No te preocupes, muchos otros juegos para que juegues, ¡sigue jugando!',
                ),array(
                'notification_type'=>253,
                'es_message'=>'Su concurso {{contest_name}} ha sido cancelado debido a la cancelación de los partidos (s). Su tarifa de entrada ha sido devuelta a su saldo.',
                ),array(
                'notification_type'=>254,
                'es_message'=>'¡Felicidades! Eres un ganador en el concurso {{contest_name}} del partido {{collection_name}}.',
                ),array(
                'notification_type'=>255,
                'es_message'=>'El concurso {{contest_name}} se ha cancelado debido a una participación insuficiente',
                ),array(
                'notification_type'=>261,
                'es_message'=>'¡Felicidades! Has ganado {{amount}} en la tabla de clasificación diaria de referencia de {{start_date}} logrando el rango {{rank_value}}.',
                ),array(
                'notification_type'=>262,
                'es_message'=>'¡Felicidades! Has ganado {{amount}} en la tabla de clasificación mensual de referencia del rango {{start_date}} logrando {{rank_value}}.',
                ),array(
                'notification_type'=>263,
                'es_message'=>'¡Felicidades! Has ganado {{amount}} en la tabla de clasificación mensual de referencia del rango {{start_date}} logrando {{rank_value}}.',
                ),array(
                'notification_type'=>264,
                'es_message'=>'¡Felicidades! ganó {{amount}} en {{entity_name}} Redieve de referencia',
                ),array(
                'notification_type'=>265,
                'es_message'=>'¡Felicidades! ganó {{amount}} en {{entity_name}} Fantasy Raeperboard',
                ),array(
                'notification_type'=>270,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado zón {{amount}} Efectivo real.',
                ),array(
                'notification_type'=>271,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado {{amount}} Bonus Cash.',
                ),array(
                'notification_type'=>272,
                'es_message'=>'¡Felicidades! {{friend_name}} referido por usted se ha unido a un concurso. Has ganado {{amount}} monedas.',
                ),array(
                'notification_type'=>273,
                'es_message'=>'Ha recibido zón {{amount}} efectivo real por unirse a un concurso de efectivo.',
                ),array(
                'notification_type'=>274,
                'es_message'=>'Ha recibido {{amount}} Bonus Cash por unirse a un concurso de efectivo.',
                ),array(
                'notification_type'=>275,
                'es_message'=>'Ha recibido {{amount}} monedas para unirse a un concurso de efectivo.',
                ),array(
                'notification_type'=>276,
                'es_message'=>'¡Felicidades! Obtuvo un beneficio semanal adicional de {{amount}} efectivo real en la unión del concurso de su amigo.',
                ),array(
                'notification_type'=>277,
                'es_message'=>'¡Felicidades! Obtuvo un beneficio semanal adicional de {{amount}} efectivo de bonificación en la unión del concurso de su amigo.',
                ),array(
                'notification_type'=>278,
                'es_message'=>'¡Felicidades! Obtuvo un beneficio semanal adicional de {{amount}} monedas en la unión del concurso de su amigo.',
                ),array(
                'notification_type'=>279,
                'es_message'=>'¡Felicidades! Obtuvo un beneficio semanal adicional de ₹ {{amount}} Real efectivo en el concurso.',
                ),array(
                'notification_type'=>280,
                'es_message'=>'¡Felicidades! Obtuvo un beneficio semanal adicional de {{amount}} Bonus Cash en el concurso.',
                ),array(
                'notification_type'=>281,
                'es_message'=>'¡Felicidades! Obtuvo un beneficio semanal adicional de {{amount}} monedas en la unión del concurso.',
                ),array(
                'notification_type'=>300,
                'es_message'=>'No dejes caer esta captura, hoy es un gran partido. Jugar {{home}} vs {{away}} ahora y ganar grande. Visite {{FRONTEND_BITLY_URL}}',
                ),array(
                'notification_type'=>301,
                'es_message'=>'Estás registrado',
                ),array(
                'notification_type'=>331,
                'es_message'=>'¡Wohoo! Coin {{coins}} se acredita a su saldo de monedas en su compra de monedas.',
                ),array(
                'notification_type'=>332,
                'es_message'=>'Se debita la billetera {{amount}} para {{coins}} monedas en la compra de monedas.',
                ),array(
                'notification_type'=>401,
                'es_message'=>'Juego {{contest_name}} Únete con éxito',
                ),array(
                'notification_type'=>402,
                'es_message'=>'El concurso {{contest_name}} se ha cancelado debido a una participación insuficiente',
                ),array(
                'notification_type'=>403,
                'es_message'=>'¡Felicidades! Eres un ganador en el juego {{collection_name}}.',
                ),array(
                'notification_type'=>410,
                'es_message'=>'Su concurso {{contest_name}} ha sido cancelado debido a la cancelación de los partidos (s). Su tarifa de entrada ha sido devuelta a su saldo.',
                ),array(
                'notification_type'=>411,
                'es_message'=>'¡Wohoo! Ganó {{amount}} monedas en girar la rueda',
                ),array(
                'notification_type'=>412,
                'es_message'=>'¡Wohoo! Ganó {{amount}} dinero real en girar la rueda',
                ),array(
                'notification_type'=>413,
                'es_message'=>'¡Wohoo! Ganó {{amount}} bono en girar la rueda',
                ),array(
                'notification_type'=>414,
                'es_message'=>'¡Wohoo! Ganaste {{name}} en girar la rueda',
                ),array(
                'notification_type'=>420,
                'es_message'=>'Registrarse en el usuario a través de su programa de afiliados y obtuvo {{amount}}',
                ),array(
                'notification_type'=>421,
                'es_message'=>'Depósito del usuario a través de su programa de afiliados y obtuvo {{amount}}',
                ),array(
                'notification_type'=>422,
                'es_message'=>'Eres un afiliado ahora. Bienvenido al equipo. Mira lo que tienes en la tienda para ti.',
                ),array(
                'notification_type'=>425,
                'es_message'=>'{{amount}} monedas acreditadas a su cuenta.',
                ),array(
                'notification_type'=>426,
                'es_message'=>'Su {{amount}} efectivo de bonificación está expirando en los próximos 7 días.',
                ),array(
                'notification_type'=>431,
                'es_message'=>'Felicitaciones que ganó {{amount}} en Scratch & Win',
                ),array(
                'notification_type'=>436,
                'es_message'=>'{{collection_name}} se ha completado con éxito',
                ),array(
                'notification_type'=>437,
                'es_message'=>'¡Hurra! Paquete de monedas suscrito con éxito. Las monedas {{amount}} se acreditan en equilibrio',
                ),array(
                'notification_type'=>438,
                'es_message'=>'¡Se cancela la suscripción al paquete de monedas!',
                ),array(
                'notification_type'=>439,
                'es_message'=>'Su paquete de monedas se renueva correctamente, {{amount}} Las monedas se acreditan en equilibrio',
                ),array(
                'notification_type'=>440,
                'es_message'=>'Únete al gran concurso más popular {{colección_name}} y gana enorme',
                ),array(
                'notification_type'=>441,
                'es_message'=>'{{username}}, {{collection_name}} se va a vivir en los próximos 15 minutos. ¡Prepárese con sus equipos y gane en grande!',
                ),array(
                'notification_type'=>442,
                'es_message'=>'{{username}}, {{collection_name}} se ha publicado. ¡Ve, prueba tu suerte para ganar en grande!',
                ),array(
                'notification_type'=>443,
                'es_message'=>'{{UserName}}, nuevo concurso {{contest_name}} en {{collection_name}} te está esperando. Es hora de traer tus habilidades y ganar premios increíbles.',
                ),array(
                'notification_type'=>470,
                'es_message'=>'Torneo {{name}} unido con éxito.',
                ),array(
                'notification_type'=>471,
                'es_message'=>'¡Hola, {{name}} Torneo está en vivo ahora! Verifique su puntaje.',
                ),array(
                'notification_type'=>472,
                'es_message'=>'Eres un ganador en el torneo {{name}}.',
                ),array(
                'notification_type'=>473,
                'es_message'=>'{{new_match_count}} Nuevos accesorios están disponibles en el torneo {{name}}.',
                ),array(
                'notification_type'=>474,
                'es_message'=>'El torneo {{name}} es cancelado por Admin.',
                ),array(
                'notification_type'=>475,
                'es_message'=>'{{name}} juego de torneo {{match}} es cancelado por Admin.',
                ),array(
                'notification_type'=>476,
                'es_message'=>'{{currency}} {{amount}} deducida como TDS',
                ),array(
                'notification_type'=>480,
                'es_message'=>'El torneo {{name}} es cancelado por Admin.',
                ),array(
                'notification_type'=>481,
                'es_message'=>'{{name}} juego de torneo {{match}} es cancelado por Admin.',
                ),array(
                'notification_type'=>501,
                'es_message'=>'Has recibido {{prize}} por superar en la clasificación diaria.',
                ),array(
                'notification_type'=>502,
                'es_message'=>'Has recibido {{prize}} por superar en la clasificación semanal',
                ),
        );
        $this->db->update_batch(NOTIFICATION_DESCRIPTION,$notification_message_data,'notification_type');
        // echo $this->db->last_query();die;

}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'es_message');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'es_subject');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'es_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'es_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'es_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'es_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'es_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'es_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'es_page_content');
		$this->dbforge->drop_column(COMMON_CONTENT, 'es_header');
		$this->dbforge->drop_column(COMMON_CONTENT, 'es_body');
		$this->dbforge->drop_column(EARN_COINS, 'id');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'es_question');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'es_answer');
		$this->dbforge->drop_column(FAQ_CATEGORY, 'es_category');
	}

}
