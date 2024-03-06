<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Booster_for_other_sports extends CI_Migration 
{

	public function up() {
		
		//Trasaction start
    	$this->db->trans_strict(TRUE);
    	$this->db->trans_start();

    	
	    //save football default record
	    $booster_list = array(
              	array(
                    'booster_id' => 5,
                    'sports_id' => NFL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'Gladiator',
                    'display_name' => 'Gladiator',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
                array(
                    'booster_id' => 6,
                    'sports_id' => NFL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'Iron Wall',
                    'display_name' => 'Iron Wall',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
                array(
                    'booster_id' => 7,
                    'sports_id' => NFL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'Hot Potato',
                    'display_name' => 'Hot Potato',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
                ),
                array(
                    'booster_id' => 8,
                    'sports_id' => NFL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'New Kicks',
                    'display_name' => 'New Kicks',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 9,  
                    'sports_id' => NFL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'Running Bomb',
                    'display_name' => 'Running Bomb',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 10,  
                    'sports_id' => NFL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'No Fumble',
                    'display_name' => 'No Fumble',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 11,    
                    'sports_id' => NFL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'Captain',
                    'display_name' => 'Captain',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 12,    
                    'sports_id' => NFL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "I'm Open",
                    'display_name' => "I'm Open",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
                    ),
              	array(
                    'booster_id' => 13,    
                    'sports_id' => BASKETBALL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Dickey's Middle Ring",
                    'display_name' => "Dickey's Middle Ring",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 14,    
                    'sports_id' => BASKETBALL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Miss Butlers Turnover",
                    'display_name' => "Miss Butlers Turnover",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 15,    
                    'sports_id' => BASKETBALL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Grand Theft Baller",
                    'display_name' => "Grand Theft Baller",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 16,    
                    'sports_id' => BASKETBALL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Block Party",
                    'display_name' => "Block Party",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 17,    
                    'sports_id' => BASKETBALL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Captain",
                    'display_name' => "Captain",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 18,    
                    'sports_id' => SOCCER_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Own Goal",
                    'display_name' => "Own Goal",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 19,    
                    'sports_id' => SOCCER_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Goal",
                    'display_name' => "Goal",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 20,    
                    'sports_id' => SOCCER_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Sharp Shooter",
                    'display_name' => "Sharp Shooter",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 21,    
                    'sports_id' => SOCCER_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Saver",
                    'display_name' => "Saver",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 22,    
                    'sports_id' => SOCCER_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "No Red Zone",
                    'display_name' => "No Red Zone",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 23,    
                    'sports_id' => BASEBALL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "J Walker",
                    'display_name' => "J Walker",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 24,    
                    'sports_id' => BASEBALL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "No Shortcuts",
                    'display_name' => "No Shortcuts",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 25,    
                    'sports_id' => BASEBALL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Fully Loaded",
                    'display_name' => "Fully Loaded",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'booster_id' => 26,    
                    'sports_id' => BASEBALL_SPORTS_ID,
                    'position_id' => 0,
                    'name' => "Walk or Run?",
                    'display_name' => "Walk or Run?",
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	)
          	);

    	      $this->db->insert_batch(BOOSTER,$booster_list);

	  	//Trasaction end
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE ) {
                  $this->db->trans_rollback();
            } else {
                  $this->db->trans_commit();
            }
	}

	public function down() {
            //$this->db->delete(BOOSTER,array("sports_id" => BASEBALL_SPORTS_ID));
		//$this->db->delete(BOOSTER,array("sports_id" => SOCCER_SPORTS_ID));
            //$this->db->delete(BOOSTER,array("sports_id" => BASKETBALL_SPORTS_ID));
            //$this->db->delete(BOOSTER,array("sports_id" => NFL_SPORTS_ID));            
	}

}