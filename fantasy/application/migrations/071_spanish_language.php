<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Spanish_language extends CI_Migration {

	public function up() {


        //up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." ADD `es_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);
          
        $master_cat_field = array(
            'es_scoring_category_name' => array(
                'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
			),
		);
        $this->dbforge->add_column(MASTER_SCORING_CATEGORY, $master_cat_field);

        $master_rule_field = array(
			'es_score_position' => array(
				'type' => 'VARCHAR',
				'constraint' => 1000,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
            ),
        );

        $this->dbforge->add_column(MASTER_SCORING_RULES, $master_rule_field);
        

        $master_category_arr = array(
			array (
                'es_scoring_category_name' => 'normal',
                'scoring_category_name' => 'normal'
            ),array (
                'es_scoring_category_name' => 'prima',
                'scoring_category_name' => 'bonus'
            ),array (
                'es_scoring_category_name' => 'Economy_rate',
                'scoring_category_name' => 'economy_rate'
            ),array (
                'es_scoring_category_name' => 'golpe',
                'scoring_category_name' => 'hitting'
            ),array (
                'es_scoring_category_name' => 'cabeceo',
                'scoring_category_name' => 'pitching'
            ),array (
                'es_scoring_category_name' => 'porcentaje de acertamiento',
                'scoring_category_name' => 'strike_rate'
            ),
        );

        $this->db->update_batch(MASTER_SCORING_CATEGORY,$master_category_arr,'scoring_category_name');

        $master_format_arr =array(
            array (
                'es_display_name' => 'BÉISBOL',
                 'sports_id' => 1,
            ),array (
                'es_display_name' => 'GRILLO',
                 'sports_id' => 7,
            ),array (
                'es_display_name' => 'FÚTBOL',
                 'sports_id' => 5,
            ),array (
                'es_display_name' => 'FÚTBOL',
                 'sports_id' => 2,
            ),array (
                'es_display_name' => 'BALONCESTO',
                 'sports_id' => 4,
            ),array (
                'es_display_name' => 'Kabaddi',
                 'sports_id' => 8,
            ),array (
                'es_display_name' => 'GOLF',
                 'sports_id' => 9,
            ),array (
                'es_display_name' => 'BÁDMINTON',
                 'sports_id' => 10,
            ),array (
                'es_display_name' => 'TENIS',
                 'sports_id' => 11,
            ),array (
                'es_display_name' => 'NCAA',
                 'sports_id' => 13,
            ),array (
                'es_display_name' => 'CFL',
                 'sports_id' => 17,
            ),array (
                'es_display_name' => 'Baloncesto de la NCAA',
                 'sports_id' => 18,
            ),
        );

        $this->db->update_batch(MASTER_SPORTS_FORMAT,$master_format_arr,'sports_id');

        $master_rule_data = array(
			array(
				'meta_key'=>'STARTING_7',
				'es_score_position'=>'Ser parte de los 7 iniciales',
				),array(
				'meta_key'=>'SUBSTITUTE',
				'es_score_position'=>'Haciendo una apariencia sustituta',
				),array(
				'meta_key'=>'SUCCESSFUL_RAID_TOUCH',
				'es_score_position'=>'Cada punto de contacto de RAID exitoso',
				),array(
				'meta_key'=>'RAID_BONUS',
				'es_score_position'=>'Bono de incursión',
				),array(
				'meta_key'=>'SUCCESSFUL_TACKLE',
				'es_score_position'=>'Cada tackle exitoso',
				),array(
				'meta_key'=>'SUPER_TACKLE',
				'es_score_position'=>'Súper tacle',
				),array(
				'meta_key'=>'PUSHING_ALL_OUT_7',
				'es_score_position'=>'Empujando todo (comenzando 7)',
				),array(
				'meta_key'=>'GETTING_ALL_OUT_7',
				'es_score_position'=>'Hacer todo fuera (comenzando 7)',
				),array(
				'meta_key'=>'UNSUCCESSFUL_RAID',
				'es_score_position'=>'Cada una incursión fallida',
				),array(
				'meta_key'=>'GREEN_CARD',
				'es_score_position'=>'Tarjeta verde',
				),array(
				'meta_key'=>'YELLOW_CARD',
				'es_score_position'=>'Tarjeta amarilla',
				),array(
				'meta_key'=>'RED_CARD',
				'es_score_position'=>'Tarjeta roja',
				),array(
				'meta_key'=>'DOUBLE_EAGLE',
				'es_score_position'=>'Águila bicéfala',
				),array(
				'meta_key'=>'EAGLE',
				'es_score_position'=>'Águila',
				),array(
				'meta_key'=>'BIRDIE',
				'es_score_position'=>'Pajarito',
				),array(
				'meta_key'=>'PAR',
				'es_score_position'=>'Par',
				),array(
				'meta_key'=>'BOGEY',
				'es_score_position'=>'Espectro',
				),array(
				'meta_key'=>'DOUBLE_BOGEY',
				'es_score_position'=>'Doble bogey',
				),array(
				'meta_key'=>'WORSE_THAN_DOUBLE_BOGEY',
				'es_score_position'=>'Peor que el doble bogey',
				),array(
				'meta_key'=>'RANK_1',
				'es_score_position'=>'Rango 1',
				),array(
				'meta_key'=>'RANK_2',
				'es_score_position'=>'Rango 2',
				),array(
				'meta_key'=>'RANK_3',
				'es_score_position'=>'Rango 3',
				),array(
				'meta_key'=>'RANK_4',
				'es_score_position'=>'Rango 4',
				),array(
				'meta_key'=>'RANK_5',
				'es_score_position'=>'Rango 5',
				),array(
				'meta_key'=>'RANK_6',
				'es_score_position'=>'Rango 6',
				),array(
				'meta_key'=>'RANK_7',
				'es_score_position'=>'Rango 7',
				),array(
				'meta_key'=>'RANK_8',
				'es_score_position'=>'Rango 8',
				),array(
				'meta_key'=>'RANK_9',
				'es_score_position'=>'Rango 9',
				),array(
				'meta_key'=>'RANK_10',
				'es_score_position'=>'Rango 10',
				),array(
				'meta_key'=>'RANK_11_15',
				'es_score_position'=>'Rango 11-15',
				),array(
				'meta_key'=>'RANK_16_20',
				'es_score_position'=>'Rango 16-20',
				),array(
				'meta_key'=>'RANK_21_25',
				'es_score_position'=>'Rango 21-25',
				),array(
				'meta_key'=>'RANK_26_30',
				'es_score_position'=>'Rango 26-30',
				),array(
				'meta_key'=>'RANK_31_40',
				'es_score_position'=>'Rango 31-40',
				),array(
				'meta_key'=>'RANK_41_50',
				'es_score_position'=>'Rango 41-50',
				),array(
				'meta_key'=>'STREAK_OF_3_BIRDIES_OF_BETTER',
				'es_score_position'=>'Racha de 3 birdies de mejor',
				),array(
				'meta_key'=>'BOGEY_FREE_ROUND',
				'es_score_position'=>'Ronda libre de bogey',
				),array(
				'meta_key'=>'ALL_4_ROUNDS_UNDER_70_STROKES',
				'es_score_position'=>'Las 4 rondas menores de 70 golpes',
				),array(
				'meta_key'=>'HOLE_IN_ONE',
				'es_score_position'=>'Hoyo en uno',
				),array(
				'meta_key'=>'STARTING_BONUS',
				'es_score_position'=>'Bono inicial',
				),array(
				'meta_key'=>'SINGLES',
				'es_score_position'=>'Cada punto anotado - Singles',
				),array(
				'meta_key'=>'DOUBLES',
				'es_score_position'=>'Cada punto anotado - dobles',
				),array(
				'meta_key'=>'TRUMP_MATCH',
				'es_score_position'=>'Triunfo',
				),array(
				'meta_key'=>'CAPTAIN',
				'es_score_position'=>'Capitán (puntaje x 1.5)',
				),array(
				'meta_key'=>'FIELD_GOALS_MISSED',
				'es_score_position'=>'Fast FG fallado',
				),array(
				'meta_key'=>'FREE_THROWS_MISSED',
				'es_score_position'=>'Fallado FT',
				),array(
				'meta_key'=>'REBOUNDS',
				'es_score_position'=>'Rebote',
				),array(
				'meta_key'=>'ASSISTS',
				'es_score_position'=>'Asistir',
				),array(
				'meta_key'=>'BLOCKED_SHOT',
				'es_score_position'=>'Bloquear',
				),array(
				'meta_key'=>'STEALS',
				'es_score_position'=>'Robar',
				),array(
				'meta_key'=>'TURNOVERS',
				'es_score_position'=>'Rotación',
				),array(
				'meta_key'=>'EACH_POINT',
				'es_score_position'=>'Punto',
				),array(
				'meta_key'=>'PASSING_YARDS',
				'es_score_position'=>'Yardas de pase',
				),array(
				'meta_key'=>'PASSING_TOUCHDOWNS',
				'es_score_position'=>'Pasando touchdowns',
				),array(
				'meta_key'=>'PASSING_INTERCEPTIONS',
				'es_score_position'=>'Pasando intercepciones',
				),array(
				'meta_key'=>'RUSHING_YARDS',
				'es_score_position'=>'Yardas por tierra',
				),array(
				'meta_key'=>'RUSHING_TOUCHDOWNS',
				'es_score_position'=>'Touchdowns apresurados',
				),array(
				'meta_key'=>'RECEPTIONS',
				'es_score_position'=>'Recepciones',
				),array(
				'meta_key'=>'RECEIVING_YARDS',
				'es_score_position'=>'Yardas de recepción',
				),array(
				'meta_key'=>'RECEIVING_TOUCHDOWNS',
				'es_score_position'=>'Recibir touchdowns',
				),array(
				'meta_key'=>'PASSING_TWO_POINT',
				'es_score_position'=>'Pasando conversiones de 2 puntos',
				),array(
				'meta_key'=>'RUSHING_TWO_POINT',
				'es_score_position'=>'Conversiones de 2 puntos apresurando',
				),array(
				'meta_key'=>'RECEVING_TWO_POINT',
				'es_score_position'=>'Recibiendo conversiones de 2 puntos',
				),array(
				'meta_key'=>'FUMBLES_LOST',
				'es_score_position'=>'Balones sueltos perdidos',
				),array(
				'meta_key'=>'KICK_RETURN_TOUCHDOWNS',
				'es_score_position'=>'Touchdowns de retorno de patada',
				),array(
				'meta_key'=>'PUNT_RETURN_TOUCHDOWNS',
				'es_score_position'=>'Touchdowns de retorno de despeje',
				),array(
				'meta_key'=>'DEFENSE_SACK',
				'es_score_position'=>'Capturas de defensa',
				),array(
				'meta_key'=>'DEFENSE_INTERCEPTIONS',
				'es_score_position'=>'Intercepciones de defensa',
				),array(
				'meta_key'=>'DEFENSE_FUMBLES_RECOVERED',
				'es_score_position'=>'Los balones sueltos de defensa se recuperaron',
				),array(
				'meta_key'=>'DEFENSE_SAFETIES',
				'es_score_position'=>'Seguros de defensa',
				),array(
				'meta_key'=>'DEFENSE_TOUCHDOWNS',
				'es_score_position'=>'Touchdowns defensivos',
				),array(
				'meta_key'=>'FUMBLE_RECOVERY_TOUCHDOWNS',
				'es_score_position'=>'TD de recuperación de balón suelto ofensivo',
				),array(
				'meta_key'=>'KICKER_EXTRA_PT_MADE',
				'es_score_position'=>'Pateador extra pt hecho',
				),array(
				'meta_key'=>'DEFENSE_FUMBLES_RECOVERY_TD',
				'es_score_position'=>'TD de recuperación de balón suelto de defensa',
				),array(
				'meta_key'=>'KICKER_FIELD_GOAL_BLOCKED',
				'es_score_position'=>'El gol de campo de pateador se perdió/bloqueó',
				),array(
				'meta_key'=>'KICKER_EXTRA_PT_BLOCKED',
				'es_score_position'=>'Pateador extra pt perdido/bloqueado',
				),array(
				'meta_key'=>'KICKER_FG_0_19',
				'es_score_position'=>'Pateador 0-19 yardas FG',
				),array(
				'meta_key'=>'KICKER_FG_20_29',
				'es_score_position'=>'Pateador de 20-29 yardas FG',
				),array(
				'meta_key'=>'KICKER_FG_30_39',
				'es_score_position'=>'Pateador de 30-39 yardas FG',
				),array(
				'meta_key'=>'KICKER_FG_40_49',
				'es_score_position'=>'Pateador de 40-49 yardas FG',
				),array(
				'meta_key'=>'KICKER_FG_50PLUS',
				'es_score_position'=>'Pateador de más de 50 yardas FG',
				),array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_0',
				'es_score_position'=>'Puntos de defensa permitidos (0)',
				),array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_1_6',
				'es_score_position'=>'Puntos de defensa permitidos (1-6)',
				),array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_7_13',
				'es_score_position'=>'Puntos de defensa permitidos (7-13)',
				),array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_14_20',
				'es_score_position'=>'Puntos de defensa permitidos (14-20)',
				),array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_21_27',
				'es_score_position'=>'Puntos de defensa permitidos (21-27)',
				),array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_28_34',
				'es_score_position'=>'Puntos de defensa permitidos (28-34)',
				),array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_35plus',
				'es_score_position'=>'Puntos de defensa permitidos (35+)',
				),array(
				'meta_key'=>'DEFENSE_KICK_RETURN_TOUCHDOWNS',
				'es_score_position'=>'Toque de retorno de la patada defensiva',
				),array(
				'meta_key'=>'DEFENSE_PUNT_RETURN_TOUCHDOWNS',
				'es_score_position'=>'Return de despeje defensivo Toque Down',
				),array(
				'meta_key'=>'DEFENSE_DEFAULT_POINTS',
				'es_score_position'=>'Puntos de incumplimiento defensivo',
				),array(
				'meta_key'=>'INNING_PITCHED',
				'es_score_position'=>'Entradas lanzadas',
				),array(
				'meta_key'=>'EARNED_RUNS_ALLOWED',
				'es_score_position'=>'Carreras ganadas permitidas',
				),array(
				'meta_key'=>'WALKS',
				'es_score_position'=>'Camina',
				),array(
				'meta_key'=>'WINS',
				'es_score_position'=>'Victorias',
				),array(
				'meta_key'=>'SAVES',
				'es_score_position'=>'Ahorra',
				),array(
				'meta_key'=>'HOME_RUN',
				'es_score_position'=>'Jonrones',
				),array(
				'meta_key'=>'RUNS',
				'es_score_position'=>'Carreras',
				),array(
				'meta_key'=>'STRIKE_OUTS',
				'es_score_position'=>'Salpicaduras',
				),array(
				'meta_key'=>'HIT_BATSMAN',
				'es_score_position'=>'Bateador',
				),array(
				'meta_key'=>'PASSING_YARDS',
				'es_score_position'=>'Yardas de pase',
				),array(
				'meta_key'=>'PASSING_TOUCHDOWNS',
				'es_score_position'=>'Pasando touchdowns',
				),array(
				'meta_key'=>'PASSING_INTERCEPTIONS',
				'es_score_position'=>'Pasando intercepciones',
				),array(
				'meta_key'=>'RUSHING_YARDS',
				'es_score_position'=>'Yardas por tierra',
				),array(
				'meta_key'=>'RUSHING_TOUCHDOWNS',
				'es_score_position'=>'Touchdowns apresurados',
				),array(
				'meta_key'=>'RECEIVING_YARDS',
				'es_score_position'=>'Yardas de recepción',
				),array(
				'meta_key'=>'RECEIVING_TOUCHDOWNS',
				'es_score_position'=>'Recibir touchdowns',
				),array(
				'meta_key'=>'STARTING_11',
				'es_score_position'=>'Al comenzar 11',
				),array(
				'meta_key'=>'RUN',
				'es_score_position'=>'Correr',
				),array(
				'meta_key'=>'WICKET',
				'es_score_position'=>'Postigo (excluyendo la renta)',
				),array(
				'meta_key'=>'CATCH',
				'es_score_position'=>'Captura',
				),array(
				'meta_key'=>'STUMPING',
				'es_score_position'=>'Tocón',
				),array(
				'meta_key'=>'RUN_OUT_THROWER',
				'es_score_position'=>'Corre (lanzador)',
				),array(
				'meta_key'=>'RUN_OUT_CATCHER',
				'es_score_position'=>'CORRE (Catcher)',
				),array(
				'meta_key'=>'RUN_OUT_DIRECT_HIT',
				'es_score_position'=>'Correr (golpe directo)',
				),array(
				'meta_key'=>'DUCK',
				'es_score_position'=>'Despido por un pato (bateador, portero y todo terreno)',
				),array(
				'meta_key'=>'FOUR',
				'es_score_position'=>'Bono de límite',
				),array(
				'meta_key'=>'SIX',
				'es_score_position'=>'Seis bono',
				),array(
				'meta_key'=>'HALF_CENTURY',
				'es_score_position'=>'Bono de medio siglo',
				),array(
				'meta_key'=>'CENTURY',
				'es_score_position'=>'Bono del siglo',
				),array(
				'meta_key'=>'STARTING_11',
				'es_score_position'=>'Al comenzar 11',
				),array(
				'meta_key'=>'RUN',
				'es_score_position'=>'Correr',
				),array(
				'meta_key'=>'WICKET',
				'es_score_position'=>'postigo, excluyendo la renta,',
				),array(
				'meta_key'=>'CATCH',
				'es_score_position'=>'Captura',
				),array(
				'meta_key'=>'STUMPING',
				'es_score_position'=>'Tocón',
				),array(
				'meta_key'=>'RUN_OUT_THROWER',
				'es_score_position'=>'Corre (lanzador)',
				),array(
				'meta_key'=>'RUN_OUT_CATCHER',
				'es_score_position'=>'CORRE (Catcher)',
				),array(
				'meta_key'=>'RUN_OUT_DIRECT_HIT',
				'es_score_position'=>'Correr (golpe directo)',
				),array(
				'meta_key'=>'DUCK',
				'es_score_position'=>'Despido por un pato (bateador, portero y todo terreno)',
				),array(
				'meta_key'=>'FOUR',
				'es_score_position'=>'Bono de límite',
				),array(
				'meta_key'=>'SIX',
				'es_score_position'=>'Seis bono',
				),array(
				'meta_key'=>'HALF_CENTURY',
				'es_score_position'=>'Bono de medio siglo',
				),array(
				'meta_key'=>'CENTURY',
				'es_score_position'=>'Bono del siglo',
				),array(
				'meta_key'=>'MAIDEN_OVER',
				'es_score_position'=>'doncella encima',
				),array(
				'meta_key'=>'MINIMUM_BOWLING_BOWLED_OVER',
				'es_score_position'=>'Mínimo número de overs para que se disparen para calcular las reglas a continuación para la tasa de economía',
				),array(
				'meta_key'=>'ECONOMY_BELOW_25',
				'es_score_position'=>'Por debajo de 2.5 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_25_349',
				'es_score_position'=>'Entre 2.5-3.49 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_35_45',
				'es_score_position'=>'Entre 3.5-4.5 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_7_8',
				'es_score_position'=>'Entre 7-8 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_801_9',
				'es_score_position'=>'Entre 8.01-9 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_ABOVE_9',
				'es_score_position'=>'Por encima de 9 carreras por más',
				),array(
				'meta_key'=>'MINIMUM_BALL_PLAYED',
				'es_score_position'=>'Min no de las bolas que se jugarán para calcular las reglas a continuación para la tasa de ataque (excepto el jugador de bolos)',
				),array(
				'meta_key'=>'STRIKE_RATE_ABOVE_140',
				'es_score_position'=>'Por encima de 140 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_12001_140',
				'es_score_position'=>'Entre 120.01-140 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_100_120',
				'es_score_position'=>'Entre 100-120 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_40_50',
				'es_score_position'=>'Entre 40-50 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_30_3999',
				'es_score_position'=>'Entre 30-39.99 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_BELOW_30',
				'es_score_position'=>'Por debajo de 30 carreras por 100 bolas',
				),array(
				'meta_key'=>'STARTING_11',
				'es_score_position'=>'En alineaciones anunciadas',
				),array(
				'meta_key'=>'RUN',
				'es_score_position'=>'Correr',
				),array(
				'meta_key'=>'WICKET',
				'es_score_position'=>'postigo, excluyendo la renta,',
				),array(
				'meta_key'=>'CATCH',
				'es_score_position'=>'Captura',
				),array(
				'meta_key'=>'STUMPING',
				'es_score_position'=>'Tocón',
				),array(
				'meta_key'=>'RUN_OUT_THROWER',
				'es_score_position'=>'Corre (lanzador)',
				),array(
				'meta_key'=>'RUN_OUT_CATCHER',
				'es_score_position'=>'CORRE (Catcher)',
				),array(
				'meta_key'=>'RUN_OUT_DIRECT_HIT',
				'es_score_position'=>'Correr (golpe directo)',
				),array(
				'meta_key'=>'DUCK',
				'es_score_position'=>'Despido por un pato (bateador, portero y todo terreno)',
				),array(
				'meta_key'=>'FOUR',
				'es_score_position'=>'Bono de límite',
				),array(
				'meta_key'=>'SIX',
				'es_score_position'=>'Seis bono',
				),array(
				'meta_key'=>'HALF_CENTURY',
				'es_score_position'=>'Bono de medio siglo',
				),array(
				'meta_key'=>'CENTURY',
				'es_score_position'=>'Bono del siglo',
				),array(
				'meta_key'=>'CATCH_3',
				'es_score_position'=>'3 Bonificación de captura',
				),array(
				'meta_key'=>'TWO_WICKET',
				'es_score_position'=>'2 bono de postigo',
				),array(
				'meta_key'=>'THREE_WICKET',
				'es_score_position'=>'3 bono de postigo',
				),array(
				'meta_key'=>'FOUR_WICKET',
				'es_score_position'=>'4 bono de postigo',
				),array(
				'meta_key'=>'FIVE_WICKET',
				'es_score_position'=>'5 bono de postigo',
				),array(
				'meta_key'=>'MINIMUM_BOWLING_BOWLED_OVER',
				'es_score_position'=>'Mínimo número de overs para que se disparen para calcular las reglas a continuación para la tasa de economía',
				),array(
				'meta_key'=>'ECONOMY_BELOW_5',
				'es_score_position'=>'Por debajo de 5 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_5_599',
				'es_score_position'=>'Entre 5-5.99 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_6_7',
				'es_score_position'=>'Entre 6-7 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_10_11',
				'es_score_position'=>'Entre 10-11 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_1101_12',
				'es_score_position'=>'Entre 11.01-12 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_ABOVE_12',
				'es_score_position'=>'Por encima de 12 carreras por más',
				),array(
				'meta_key'=>'MINIMUM_BALL_PLAYED',
				'es_score_position'=>'Min no de las bolas que se jugarán para calcular las reglas a continuación para la tasa de ataque (excepto el jugador de bolos)',
				),array(
				'meta_key'=>'STRIKE_RATE_ABOVE_170',
				'es_score_position'=>'Por encima de 170 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_15001_170',
				'es_score_position'=>'Entre 150.01-170 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_130_150',
				'es_score_position'=>'Entre 130-150 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_60_70',
				'es_score_position'=>'Entre 60-70 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_50_5999',
				'es_score_position'=>'Entre 350-59.99 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_BELOW_50',
				'es_score_position'=>'Por debajo de 50 carreras por 100 bolas',
				),array(
				'meta_key'=>'STARTING_11',
				'es_score_position'=>'En alineaciones anunciadas',
				),array(
				'meta_key'=>'RUN',
				'es_score_position'=>'Correr',
				),array(
				'meta_key'=>'WICKET',
				'es_score_position'=>'Wicket, excluyendo la renta,',
				),array(
				'meta_key'=>'CATCH',
				'es_score_position'=>'Captura',
				),array(
				'meta_key'=>'STUMPING',
				'es_score_position'=>'Tocón',
				),array(
				'meta_key'=>'RUN_OUT_THROWER',
				'es_score_position'=>'Corre (lanzador)',
				),array(
				'meta_key'=>'RUN_OUT_CATCHER',
				'es_score_position'=>'CORRE (Catcher)',
				),array(
				'meta_key'=>'RUN_OUT_DIRECT_HIT',
				'es_score_position'=>'Correr (golpe directo)',
				),array(
				'meta_key'=>'DUCK',
				'es_score_position'=>'Despido por un pato (bateador, portero y todo terreno)',
				),array(
				'meta_key'=>'FOUR',
				'es_score_position'=>'Bono de límite',
				),array(
				'meta_key'=>'SIX',
				'es_score_position'=>'Seis bono',
				),array(
				'meta_key'=>'30_BONUS',
				'es_score_position'=>'bono de 30 carreras',
				),array(
				'meta_key'=>'HALF_CENTURY',
				'es_score_position'=>'Bono de medio siglo',
				),array(
				'meta_key'=>'LBW_BOWLED',
				'es_score_position'=>'Bonificación (LBW / Bowled)',
				),array(
				'meta_key'=>'MINIMUM_BOWLING_BOWLED_OVER',
				'es_score_position'=>'Mínimo número de overs para que se disparen para calcular las reglas a continuación para la tasa de economía',
				),array(
				'meta_key'=>'ECONOMY_BELOW_7',
				'es_score_position'=>'Por debajo de 7 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_7_799',
				'es_score_position'=>'Entre 7-7.99 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_8_9',
				'es_score_position'=>'Entre 8-9 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_14_15',
				'es_score_position'=>'Entre 14-15 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_1501_16',
				'es_score_position'=>'Entre 15.01-16 carreras por más',
				),array(
				'meta_key'=>'ECONOMY_ABOVE_16',
				'es_score_position'=>'Por encima de 16 carreras por más',
				),array(
				'meta_key'=>'MINIMUM_BALL_PLAYED',
				'es_score_position'=>'Min no de las bolas que se jugarán para calcular las reglas a continuación para la tasa de ataque (excepto el jugador de bolos)',
				),array(
				'meta_key'=>'STRIKE_RATE_ABOVE_190',
				'es_score_position'=>'Por encima de 190 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_17001_190',
				'es_score_position'=>'Entre 170.01-190 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_150_170',
				'es_score_position'=>'Entre 150-170 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_70_80',
				'es_score_position'=>'Entre 70-80 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_60_6999',
				'es_score_position'=>'Entre 60-69.99 carreras por 100 bolas',
				),array(
				'meta_key'=>'STRIKE_RATE_BELOW_60',
				'es_score_position'=>'Por debajo de 60 carreras por 100 bolas',
				),array(
				'meta_key'=>'STARTING_11',
				'es_score_position'=>'Al comenzar 11',
				),array(
				'meta_key'=>'SUBSTITUTE',
				'es_score_position'=>'Viendo como sustituto',
				),array(
				'meta_key'=>'GOAL_STRIKER',
				'es_score_position'=>'Meta de un delantero',
				),array(
				'meta_key'=>'GOAL_MID_FIELDER',
				'es_score_position'=>'Objetivo de un jardinero medio',
				),array(
				'meta_key'=>'GOAL_DEF_GK',
				'es_score_position'=>'Gol de un defensor u portero',
				),array(
				'meta_key'=>'ASSIST',
				'es_score_position'=>'Asistir',
				),array(
				'meta_key'=>'SHOT_ON_TARGET',
				'es_score_position'=>'Disparado en el objetivo (incluye goles)',
				),array(
				'meta_key'=>'CHANCE_CREATED',
				'es_score_position'=>'Chance creado, el pase final que conduce a un disparo (en el objetivo que incluye goles, bloqueado o fuera del objetivo)',
				),array(
				'meta_key'=>'PASSES_COMPLETED',
				'es_score_position'=>'5 pases completados',
				),array(
				'meta_key'=>'TACKLE_WON',
				'es_score_position'=>'Tackle ganó',
				),array(
				'meta_key'=>'INTERCEPTION_WON',
				'es_score_position'=>'Interception ganó',
				),array(
				'meta_key'=>'SAVES_GK',
				'es_score_position'=>'Salva (GK)',
				),array(
				'meta_key'=>'PENALTY_SAVED_GK',
				'es_score_position'=>'Penalización guardada (GK)',
				),array(
				'meta_key'=>'CLEAN_SHEET_GK_DEF',
				'es_score_position'=>'Limpiar la hoja GK/def (jugando +55 minutos)',
				),array(
				'meta_key'=>'YELLOW_CARD',
				'es_score_position'=>'Tarjeta amarilla',
				),array(
				'meta_key'=>'RED_CARD',
				'es_score_position'=>'tarjeta roja',
				),array(
				'meta_key'=>'OWN_GOAL',
				'es_score_position'=>'Gol en propia puerta',
				),array(
				'meta_key'=>'GOAL_CONCEDED_GK_DEF',
				'es_score_position'=>'Los objetivos concedieron GK/DEF (en el campo cuando se anota el objetivo)',
				),array(
				'meta_key'=>'PENALTY_MISSED',
				'es_score_position'=>'Penalización perdida',
				),array(
				'meta_key'=>'SINGLE',
				'es_score_position'=>'Único',
				),array(
				'meta_key'=>'DOUBLE',
				'es_score_position'=>'Doble',
				),array(
				'meta_key'=>'TRIPLES',
				'es_score_position'=>'Triples',
				),array(
				'meta_key'=>'HITTING_HOME_RUN',
				'es_score_position'=>'Jonrones',
				),array(
				'meta_key'=>'RUNS_BATTED_IN',
				'es_score_position'=>'Carreras bateadas en',
				),array(
				'meta_key'=>'HITTING_RUNS',
				'es_score_position'=>'Carreras',
				),array(
				'meta_key'=>'HITTING_WALKS',
				'es_score_position'=>'Camina',
				),array(
				'meta_key'=>'STOLEN_BASES',
				'es_score_position'=>'Bases robadas',
				),array(
				'meta_key'=>'HIT_BY_PITCH',
				'es_score_position'=>'Hit por campo',
				),array(
				'meta_key'=>'CAUGHT_STEALING',
				'es_score_position'=>'Atrapado robando',
				),
		);

		$this->db->update_batch(MASTER_SCORING_RULES,$master_rule_data,'meta_key');
	}

	public function down() {
		$this->dbforge->drop_column(BANNER_MANAGEMENT, 'es_name');
		$this->dbforge->drop_column(MASTER_SCORING_CATEGORY, 'es_scoring_category_name');
		$this->dbforge->drop_column(MASTER_SCORING_RULES, 'es_score_position');
		$this->dbforge->drop_column(MASTER_SPORTS_FORMAT, 'es_display_name');
	}

}
