"use strict";
var express = require('express')
var app = express()
var util = require('util');
var CONSTANTS = require('../constants')
var helper = require('../helper/default_helper')
var conn = require('../db_fantasy_config')  
var query = util.promisify(conn.query).bind(conn);


var user_conn = require('../db_user_config') 
var user_query = util.promisify(user_conn.query).bind(user_conn);
module.exports = {
    get_current_live_collection: async function(for_2nd_inning,allow_bench){

    	var current_date = helper.format_date();
    	
            //var cond=``;
            var rows=[];
            var cond=` DATE_SUB(season_scheduled_date,INTERVAL deadline_time MINUTE) <'`+current_date+`' AND `;

            var where_cond = ` AND CM.is_lineup_processed=0`;
            //2nd inning condition
            if(for_2nd_inning ==1)
            {
              where_cond = ` AND CM.is_2nd_inn_lineup_processed=0`;
              var cond=` DATE_SUB(2nd_inning_date,INTERVAL deadline_time MINUTE) <'`+current_date+`' AND `;
            }

            var bench_str = ``;
            if(allow_bench===1)
            {
              bench_str = ` AND CM.bench_processed > 0 `;
            }
            var sql_query=`SELECT  CM.collection_master_id,CM.season_scheduled_date,CM.league_id,L.sports_id
               FROM `+CONSTANTS.TABLE_PREFIX+CONSTANTS.COLLECTION_MASTER+`  CM
               INNER JOIN `+CONSTANTS.TABLE_PREFIX+CONSTANTS.LEAGUE+` L ON CM.league_id=L.league_id
               WHERE ${cond} CM.status=0 ${where_cond} ${bench_str} ORDER BY CM.season_scheduled_date DESC LIMIT 0,1`;
               //console.log('sql:',sql_query);

               try {
                 rows = await query(sql_query);
               }
               catch (e) {
                console.log(e);
                console.log("get_current_live_collection - catch block");
              }
        	 return rows;
        //})
    },
    get_lineup_master_data: async function (collection_master_data,for_2nd_inning) {

    	var current_date = helper.format_date();
      var cond = ` AND is_2nd_inning=0`;
      //2nd inning condition
      if(for_2nd_inning ==1)
      {
        cond = ` AND is_2nd_inning=1`;
      }

    	var rows=[];
            var sql_query=`SELECT  lineup_master_id,team_data FROM `+CONSTANTS.TABLE_PREFIX+CONSTANTS.LINEUP_MASTER+`  
               WHERE collection_master_id=`+collection_master_data.collection_master_id+cond ;
               console.log('collection_master_data:',collection_master_data);

               try {
                rows = await query(sql_query);
              }
              catch (e) {
                console.log(e);
                console.log("get_lineup_master_data - catch block");
              }
            return rows;
        	 
        
    },
    get_all_rosters:async function (collection_master_data) {

        var sql_query=`SELECT P.player_id,P.player_uid,T.team_id,T.team_uid,T.team_name,IFNULL(T.display_team_abbr,T.team_abbr) as team_abbreviation,S.season_game_uid,P.display_name as full_name,PT.position,ROUND(IFNULL(PT.salary,0),1) as salary,IFNULL(P.nick_name,'') as nick_name,IFNULL(P.display_name,'') as display_name,PT.player_team_id,P.sports_id,S.league_id,IFNULL(T.jersey,T.feed_jersey) as jersey,IFNULL(T.flag,T.feed_flag) as flag,(CASE WHEN JSON_SEARCH(S.playing_list,'one',P.player_uid) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,S.playing_announce
        FROM `+CONSTANTS.TABLE_PREFIX+CONSTANTS.COLLECTION_SEASON+ ` AS CS
        INNER JOIN `+CONSTANTS.TABLE_PREFIX+CONSTANTS.SEASON+ ` S ON S.season_id = CS.season_id 
        INNER JOIN `+CONSTANTS.TABLE_PREFIX+CONSTANTS.PLAYER_TEAM+` PT ON PT.season_id=S.season_id 
        INNER JOIN `+CONSTANTS.TABLE_PREFIX+CONSTANTS.TEAM+` T ON T.team_id = PT.team_id 	
        INNER JOIN `+CONSTANTS.TABLE_PREFIX+CONSTANTS.PLAYER+` P ON P.player_id = PT.player_id `;
   
        var sql_query_league='';
        if(collection_master_data.league_id != '')
        {
            sql_query_league+=` AND S.league_id=`+collection_master_data.league_id;
        }  
        

        sql_query+=` WHERE PT.is_deleted=0
        AND CS.collection_master_id=`+collection_master_data.collection_master_id+`
        AND PT.player_status=1
        AND PT.is_published=1 `+sql_query_league+` GROUP BY P.player_uid ORDER BY P.full_name ASC`;
      
        var rows =[];
        try {
            rows = await query(sql_query);
          }
          catch (e) {
            console.log(e);
            console.log("get_all_rosters - catch block");
          }
        return rows;
    },
    create_lineup_table:async function (collection_master_id) {
      var rows =[];
        let createLinupSql = `CREATE TABLE IF NOT EXISTS `+CONSTANTS.TABLE_PREFIX+CONSTANTS.LINEUP+collection_master_id+`  (
            lineup_id int(11) NOT NULL AUTO_INCREMENT,
            lineup_master_id int(11) NOT NULL,
            master_lineup_position_id int(11) NOT NULL,
            player_unique_id varchar(100) NOT NULL,
            player_team_id int(11) NOT NULL DEFAULT '0' COMMENT 'Actual player team id from player table',
            team_id int(11) NOT NULL DEFAULT '0',
            player_salary float NOT NULL DEFAULT '0',
            score decimal(10,2) NOT NULL DEFAULT '0.00',
            booster_points decimal(10,2) NOT NULL DEFAULT '0.00',
            captain tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=>Captain, 2=>Vice Captain',
            is_substitute tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-for substituted (out player)',
            in_minutes int(11) DEFAULT '0' COMMENT '	When player substitute enter in minutes',
            out_minutes int(11) DEFAULT '0' COMMENT 'When player substitute enter out minutes',
            status tinytext CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '1=>IN ,2 => OUT',
            substituted_by varchar(100) NOT NULL DEFAULT '0' COMMENT 'Player unique id of new player',
            added_date datetime DEFAULT NULL,
            PRIMARY KEY (lineup_id),
            UNIQUE KEY lineup_master_id (lineup_master_id,player_unique_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;`;
             
            try {
                rows = await query(createLinupSql);
              }
              catch (e) {
                console.log(e);
                console.log("create_lineup_table - catch block");
              }

              return rows;
                                 
    },
    get_master_position:async (sports_id)=>{

        var sport_cond = ``;
        if([1].indexOf(sports_id) == -1){
            sport_cond=` AND position_name = allowed_position`; // to avoid FLEX position
        }
        var sql_query=`SELECT master_lineup_position_id,position_name as position, position_name, position_display_name,number_of_players,max_player_per_position,position_order  FROM `+CONSTANTS.TABLE_PREFIX+CONSTANTS.MASTER_LINEUP_POSITION+` 
            WHERE sports_id=`+sports_id+` 
     `+sport_cond+` order by position_order ASC`;

     var rows=[];
     try {
        rows = await query(sql_query);
      }
      catch (e) {
        console.log(e);
        console.log("get_master_position - catch block");
      }
      return rows;
    },
    get_app_config:async (key_name)=>{

      var sql_query=`SELECT key_name,key_value,custom_data  FROM `+CONSTANTS.TABLE_PREFIX+CONSTANTS.APP_CONFIG+` 
          WHERE key_name='${key_name}'`;
          
      var rows=[];
      try {
          rows = await user_query(sql_query);
        }
        catch (e) {
          console.log(e);
          console.log("lineup move get_app_config - catch block");
        }
      return rows;
    }
}