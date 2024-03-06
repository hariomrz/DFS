"use strict";
var express = require('express')
var util = require('util');
var _ = require('lodash')
var CONSTANTS = require('../constants')
var helper = require('../helper/default_helper')
var fantasy_conn = require('../db_fantasy_config')  
var lineupModel  = require('../models/newLineupModel')
var query = util.promisify(fantasy_conn.query).bind(fantasy_conn);


exports.lineup_move = async function(req,res)
{
     var bench_config = await lineupModel.get_app_config('bench_player');
     var allow_bench = 0;
     if(bench_config.length && bench_config[0].key_value =='1')
     {
          allow_bench = 1;
     }
   
     var for_2nd_inning = req.params.for_2nd_inning;
     var start = new Date();
	var end = 0;
     var collection_master_data= await lineupModel.get_current_live_collection(for_2nd_inning,allow_bench);
     
     if(!collection_master_data.length)
     {
          helper.res.message ='No Match available.'
          helper.res.data = [];
          helper.res.response_code =200;
          res.json(helper.res)
          return;
     }
     var collection_master_data = collection_master_data[0];
     //console.log("collection_master_data",collection_master_data);
     var lineup_master_data=await lineupModel.get_lineup_master_data(collection_master_data,for_2nd_inning);
     //console.log("lineup_master_data",lineup_master_data);
     if(!lineup_master_data.length)
	{   
          try {
               var update_cond = ` is_lineup_processed=1`;
               if(for_2nd_inning==1)
               {
                    update_cond = ` is_2nd_inn_lineup_processed=1`; 
               }
               var rows = await query(`UPDATE  `+CONSTANTS.TABLE_PREFIX+CONSTANTS.COLLECTION_MASTER+` SET `+update_cond+` WHERE collection_master_id=`+collection_master_data.collection_master_id);
          }
          catch (e) {
               console.log(e);
               console.log("lineup_move s1 - catch block");
          }

          helper.res.message ='No lineup available.'
          helper.res.data = [];
          helper.res.response_code =200;
          res.json(helper.res)
          return;
     }

     //get players
     var rosters = await lineupModel.get_all_rosters(collection_master_data)
     
     if(!rosters.length)
     {
          helper.res.message = 'No Player available.';
          helper.res.data = [];
          helper.res.response_code =200;
          res.json(helper.res)
          return;
     }

     var player_team_id_arr = helper.array_column_key(rosters,'player_team_id');
     var table_created = await lineupModel.create_lineup_table(collection_master_data.collection_master_id);
     var position_master_data = await lineupModel.get_master_position(collection_master_data.sports_id)
     if(!position_master_data.length)
     {
          helper.res.message = 'No Position available.';
          helper.res.data = [];
          helper.res.response_code =200;
          res.json(helper.res)
          return;
     }
     var position_arr = helper.array_column_key(position_master_data,'position');
     var team_chunks = _.chunk(lineup_master_data,499);
     var player_count = await insert_batch_players(collection_master_data.collection_master_id,team_chunks,position_arr,player_team_id_arr);
     try{
          var total_rosters = await query(`SELECT COUNT(lineup_id) as total_players FROM `+CONSTANTS.TABLE_PREFIX+CONSTANTS.LINEUP+collection_master_data.collection_master_id);
     }
     catch(e)
     {
          console.log(e);
          console.log("lineup_move s2 - catch block");
     }
     
     if(total_rosters.length > 0 &&  total_rosters[0].total_players >= player_count)
     {
          try{
               var update_lm_cond = ` is_lineup_processed=1`;
               if(for_2nd_inning==1)
               {
                    update_lm_cond = ` is_2nd_inn_lineup_processed=1`; 
               }
               var update_lineup_process = await query(`UPDATE  `+CONSTANTS.TABLE_PREFIX+CONSTANTS.COLLECTION_MASTER+` SET `+update_lm_cond+` WHERE collection_master_id=`+collection_master_data.collection_master_id)
          }
          catch(e)
          {
               console.log(e);
               console.log("lineup_move s3 - catch block");
          }

          end = new Date() - start;
          console.info('[Execution time]: %ds', end/1000);
          helper.res.message ='Lineup moved. [Execution time]:'+end/1000;

          helper.res.data = [];
          helper.res.response_code =200;
          res.json(helper.res)
          return;
     }
}

async function insert_batch_players(collection_master_id,team_chunks,position_arr,player_team_id_arr)
{
     var sql = `INSERT INTO `+CONSTANTS.TABLE_PREFIX+CONSTANTS.LINEUP+collection_master_id+` (lineup_master_id,
          master_lineup_position_id,
          player_unique_id,
          player_team_id,
          team_id,
          player_salary,
          score,
          captain,
          added_date) VALUES ?`;

     var update_fields = ` ON DUPLICATE KEY UPDATE lineup_master_id =VALUES(lineup_master_id),
                    master_lineup_position_id=VALUES(master_lineup_position_id),
                    player_unique_id=VALUES(player_unique_id),
                    player_team_id=VALUES(player_team_id),
                    player_salary=VALUES(player_salary),
                    team_id=VALUES(team_id),
                    score=VALUES(score),
                    captain=VALUES(captain),
                    added_date=VALUES(added_date)`;	

     var player_count = 0;	
     var lineup_players_count = 0;
     var unique_lineup= [];	
     
     team_chunks.forEach(async (oneChunk) => {
          
          var lineup_team_data = [];
			     _.map(oneChunk,(oneTeam,ti)=>{

					var team_players=  JSON.parse(oneTeam.team_data);
				 	if(team_players.pl && team_players.pl.length>0)
				 	{
				
						lineup_players_count = 0;
				 		_.map(team_players.pl,(player,pi)=>{
				 			if(lineup_players_count< 11)
				 			{
								var captain = 0;
								if(player==team_players.c_id)
								{
									captain=1;
								}

								if(player==team_players.vc_id)
								{
									captain=2;
								}
								var master_lineup_position_id = 0;
								if(position_arr[player_team_id_arr[player].position])
								{
									master_lineup_position_id =position_arr[player_team_id_arr[player].position].master_lineup_position_id;
								}

								lineup_team_data.push([
								oneTeam.lineup_master_id,
								master_lineup_position_id,
								player_team_id_arr[player].player_uid,
								player_team_id_arr[player].player_team_id,
								player_team_id_arr[player].team_id,
								player_team_id_arr[player].salary,
								0,
								captain,
								helper.format_date()
								]);
				 				lineup_players_count++;
							}
							
							
				 		});
					}
			     });

          try {
               var  rows = await query(sql+` `+update_fields, [lineup_team_data]);
               player_count+=lineup_team_data.length;
               }
          finally{
                    //conn.end();
          }

        });

        return player_count;
}