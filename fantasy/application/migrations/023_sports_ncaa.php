<?php  defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_sports_ncaa extends CI_Migration 
{

  public function up()
  {
    
    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    //Insert master sports
    
    $this->db->insert(MASTER_SPORTS,
                        array(
                              'sports_id'      => NCAA_SPORTS_ID,
                              'sports_name'       => 'NCAAF',
                              'updated_date'      => format_date(),
                              'active'            => 0,
                              'team_player_count' => 11,
                              'order'             => 13
                          )
                      );
    
    $this->db->insert(MASTER_SPORTS_FORMAT,
                        array(
                              'sports_id'         => NCAA_SPORTS_ID,
                              'display_name'      => 'NCAAF',
                              'en_display_name'   => 'NCAAF',
                              'hi_display_name'   => 'फुटबॉल',
                              'guj_display_name'  => 'ફૂટબ .લ',
                              'fr_display_name'  => 'NCAAF',
                              'ben_display_name'  => 'NCAAF',
                              'pun_display_name'  => 'NCAAF',
                              'format_type'  => 'DAILY',
                              'description'  => 'Daily Fantasy'
                          )
                      );

     $this->db->insert_batch(MASTER_LINEUP_POSITION, array(
                        array(
                              'sports_id'             => NCAA_SPORTS_ID,
                              'position_name'         => 'QB',
                              'position_display_name'  => 'QB',
                              'number_of_players'     => '1',
                              'position_order'        => '1',
                              'max_player_per_position'  => '2',
                              'allowed_position'        => 'QB'
                          ),
                          array(
                              'sports_id'             => NCAA_SPORTS_ID,
                              'position_name'         => 'RB',
                              'position_display_name'  => 'RB',
                              'number_of_players'     => '3',
                              'position_order'        => '2',
                              'max_player_per_position'  => '6',
                              'allowed_position'        => 'RB'
                          ),
                            array(
                              'sports_id'             => NCAA_SPORTS_ID,
                              'position_name'         => 'WR',
                              'position_display_name'  => 'WR',
                              'number_of_players'     => '3',
                              'position_order'        => '3',
                              'max_player_per_position'  => '6',
                              'allowed_position'        => 'WR'
                          )
                        )    
                      );

      $this->db->insert(LEAGUE,
                        array(
                              'league_uid'     => 2,
                              'sports_id'      => NCAA_SPORTS_ID,
                              'league_abbr'       => 'NCAA',
                              'league_name'       => 'NCAA',
                              'league_display_name'       => 'NCAA',
                              'active'            => 1,
                              'max_player_per_team' => 7,
                              'updated_date'      => format_date(),
                          )
                      );

    
       //scoring rules
       $this->db->insert(MASTER_SCORING_CATEGORY,
                        array(
                              'scoring_category_name'       => 'normal',
                              'en_scoring_category_name'      => 'normal',
                              'hi_scoring_category_name'            => 'normal',
                              'guj_scoring_category_name' => 'normal',
                              'sports_id'    => NCAA_SPORTS_ID
                            )
                      );
      $master_scoring_category_id = $this->db->insert_id(); 
      //insert rules
      $this->db->insert_batch(MASTER_SCORING_RULES,array(

                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Passing Yards',
                              'en_score_position'         => 'Passing Yards',
                              'hi_score_position'         => 'Passing Yards',
                              'guj_score_position'        => 'Passing Yards',
                              'score_points'              => '0.04',
                              'points_unit'               => '0',
                              'meta_key'                  => 'PASSING_YARDS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Passing Touchdowns',
                              'en_score_position'         => 'Passing Touchdowns',
                              'hi_score_position'         => 'Passing Touchdowns',
                              'guj_score_position'        => 'Passing Touchdowns',
                              'score_points'              => '4',
                              'points_unit'               => '0',
                              'meta_key'                  => 'PASSING_TOUCHDOWNS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Passing Interceptions',
                              'en_score_position'         => 'Passing Interceptions',
                              'hi_score_position'         => 'Passing Interceptions',
                              'guj_score_position'        => 'Passing Interceptions',
                              'score_points'              => '-2',
                              'points_unit'               => '0',
                              'meta_key'                  => 'PASSING_INTERCEPTIONS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Rushing Yards',
                              'en_score_position'         => 'Rushing Yards',
                              'hi_score_position'         => 'Rushing Yards',
                              'guj_score_position'        => 'Rushing Yards',
                              'score_points'              => '0.1',
                              'points_unit'               => '0',
                              'meta_key'                  => 'RUSHING_YARDS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Rushing Touchdowns',
                              'en_score_position'         => 'Rushing Touchdowns',
                              'hi_score_position'         => 'Rushing Touchdowns',
                              'guj_score_position'        => 'Rushing Touchdowns',
                              'score_points'              => '6',
                              'points_unit'               => '0',
                              'meta_key'                  => 'RUSHING_TOUCHDOWNS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Receiving Yards',
                              'en_score_position'         => 'Receiving Yards',
                              'hi_score_position'         => 'Receiving Yards',
                              'guj_score_position'        => 'Receiving Yards',
                              'score_points'              => '0.1',
                              'points_unit'               => '0',
                              'meta_key'                  => 'RECEIVING_YARDS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Receiving Touchdowns',
                              'en_score_position'         => 'Receiving Touchdowns',
                              'hi_score_position'         => 'Receiving Touchdowns',
                              'guj_score_position'        => 'Receiving Touchdowns',
                              'score_points'              => '6',
                              'points_unit'               => '0',
                              'meta_key'                  => 'RECEIVING_TOUCHDOWNS',
                              'meta_key_alias'            => ''
                            ),
                       )
                      );

      
      //Insert statistics table
      $this->dbforge->add_field(array(
      'league_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE
      ),
      'season_game_uid' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => FALSE
      ),
      'week' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),
      'scheduled' => array(
        'type' => 'VARCHAR',
        'constraint' => 500,
        'null' => FALSE
      ),
      'scheduled_date' => array(
        'type' => 'DATETIME',
        'default' => NULL,
        'null' => FALSE
      ),
      'home_uid' => array(
        'type' => 'VARCHAR',
        'constraint' => 50,
        'null' => FALSE
      ),
      'away_uid' => array(
        'type' => 'VARCHAR',
        'constraint' => 50,
        'null' => FALSE
      ),
      'home_score' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),
      'away_score' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),
      'team_uid' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => FALSE
      ),
      'player_uid' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        
        'null' => FALSE
      ),
      
      'passing_yards' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),

      'passing_touch_downs' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),

      'passing_interceptions' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),

      'passing_two_pt' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),
      'rushing_yards' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),


      'rushing_touch_downs' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),

      'rushing_two_pt' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),

      'receiving_yards' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),

      'receptions' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),

      'receiving_touch_downs' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),

      'receiving_two_pt' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),


      'points_allowed' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),

      'minutes' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),

      'player_minute' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0,
        'null' => FALSE
      ),
      'updated_at' => array(
        'type' => 'DATETIME',
        'null' => TRUE,
        'default' => NULL
      )
      
    ));
    
    $this->dbforge->add_key('league_id', TRUE);
    $this->dbforge->add_key('season_game_uid', TRUE);
    $this->dbforge->add_key('player_uid', TRUE);
    $this->dbforge->add_key('week', TRUE);
    $this->dbforge->create_table(GAME_STATISTICS_NCAA);

    //Trasaction end
      $this->db->trans_complete();
      if ($this->db->trans_status() === FALSE )
      {
          $this->db->trans_rollback();
      }
      else
      {
          $this->db->trans_commit();
      }
  }

  public function down()
  {
	    //Trasaction start
      $this->db->trans_strict(TRUE);
      $this->db->trans_start();
      //Delete scoring rules
      $this->db->query(" DELETE MSR 
                              FROM ".$this->db->dbprefix(MASTER_SCORING_RULES)." AS MSR
                              INNER JOIN ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." AS MSC ON MSC.master_scoring_category_id = MSR.master_scoring_category_id
                              INNER JOIN ".$this->db->dbprefix(MASTER_SPORTS)." AS MS ON MS.sports_id = MSC.sports_id  
                              WHERE MS.sports_id = ".NCAA_SPORTS_ID." 
                                  ");
      $this->db->query(" DELETE MSC 
                              FROM ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." AS MSC
                              INNER JOIN ".$this->db->dbprefix(MASTER_SPORTS)." AS MS ON MS.sports_id = MSC.sports_id  
                              WHERE MS.sports_id = ".NCAA_SPORTS_ID." 
                                  ");
      //Down script for master sports
      $this->db->where('sports_id',  NCAA_SPORTS_ID);
      $this->db->delete(LEAGUE);

      $this->db->where('sports_id' , NCAA_SPORTS_ID);
      $this->db->delete(MASTER_SPORTS_FORMAT);

      $this->db->where('sports_id' , NCAA_SPORTS_ID);
      $this->db->delete(MASTER_SPORTS);
      
      //Down script for statistic
      $this->dbforge->drop_table(GAME_STATISTICS_NCAA);
      //Trasaction end
      $this->db->trans_complete();
      if ($this->db->trans_status() === FALSE )
      {
          $this->db->trans_rollback();
      }
      else
      {
          $this->db->trans_commit();
      }
  }

}