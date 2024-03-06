"use strict";
var util = require('util');
var CONSTANTS = require('../constants')
var helper = require('../helper/default_helper')
var conn = require('../db_stock_config')  
var query = util.promisify(conn.query).bind(conn);
module.exports = {
    get_current_live_collection: async function(stock_type='1,2'){

    	var current_date = helper.format_date();
    	
            //var cond=``;
            var rows=[];
            var cond=` DATE_SUB(CM.scheduled_date,INTERVAL 0 MINUTE) <'`+current_date+`' AND `;

            var where_cond = ` AND CM.is_lineup_processed=0`;
          
            var sql_query=`SELECT  CM.collection_id,CM.scheduled_date,CM.category_id,CM.published_date,CM.stock_type
               FROM `+CONSTANTS.TABLE_PREFIX+CONSTANTS.COLLECTION+`  CM
               WHERE `+cond+` CM.status=0 `+where_cond+` AND CM.stock_type IN(${stock_type}) ORDER BY CM.scheduled_date DESC LIMIT 0,1`;
               console.log('sql:',sql_query);

               try {
                 rows = await query(sql_query);
               }
               catch (e) {
                console.log("entering catch block");
                console.log(e);
                console.log("leaving catch block");
              }
        	 return rows;
        //})
    },
    get_lineup_master_data: async function (collection_master_data) {

    	var rows=[];
            var sql_query=`SELECT  lineup_master_id,team_data FROM `+CONSTANTS.TABLE_PREFIX+CONSTANTS.LINEUP_MASTER+`  
               WHERE collection_id=`+collection_master_data.collection_id ;
               console.log('collection_master_data:',collection_master_data);

               try {
                rows = await query(sql_query);
              }
              catch (e) {
                console.log("entering catch block");
                console.log(e);
                console.log("leaving catch block");
              }
            return rows;
        	 
        
    },
    get_all_stocks:async function (collection_master_data) {

        var sql_query=`SELECT CS.stock_id,CS.stock_name,CS.lot_size,IFNULL(SH.close_price,0) as last_price ,S.last_price as current_price,0 as is_wish,(IFNULL(SH.open_price,0)-S.last_price) as price_diff,IFNULL(S.logo,"") as logo
        FROM ${CONSTANTS.TABLE_PREFIX+CONSTANTS.COLLECTION_STOCK} AS CS
        INNER JOIN ${CONSTANTS.TABLE_PREFIX+CONSTANTS.COLLECTION} C ON C.collection_id = CS.collection_id
        LEFT JOIN ${CONSTANTS.TABLE_PREFIX+CONSTANTS.STOCK_HISTORY} SH ON SH.stock_id=CS.stock_id AND SH.schedule_date=DATE_FORMAT('${collection_master_data.scheduled_date}','%Y-%m-%d')
	    LEFT JOIN ${CONSTANTS.TABLE_PREFIX+CONSTANTS.STOCK} S ON S.stock_id = CS.stock_id `;
   
        sql_query+=` WHERE
        CS.collection_id=${collection_master_data.collection_id}
        GROUP BY CS.stock_id ORDER BY CS.stock_name ASC`;
      //console.log('sql_query#',sql_query);
        var rows =[];
        try {
            rows = await query(sql_query);
          }
          catch (e) {
            console.log("entering catch block");
            console.log(e);
            console.log("leaving catch block");
          }
        return rows;
    },
    create_lineup_table:async function (collection_master_id) {
      var rows =[];
        let createLinupSql = `CREATE TABLE IF NOT EXISTS `+CONSTANTS.TABLE_PREFIX+CONSTANTS.LINEUP+collection_master_id+`  (
            lineup_id int(11) NOT NULL AUTO_INCREMENT,
            lineup_master_id int(11) NOT NULL,
            master_lineup_position_id int(11) NOT NULL,
            roster_category_id int(11) NOT NULL DEFAULT '0' COMMENT 'roster category id in case of turbo lineup',
            player_unique_id varchar(100) NOT NULL,
            player_team_id int(11) NOT NULL DEFAULT '0' COMMENT 'Actual player team id from player table',
            team_league_id int(11) NOT NULL DEFAULT '0',
            player_salary float NOT NULL DEFAULT '0',
            week int(11) NOT NULL DEFAULT '0',
            score decimal(50,2) NOT NULL DEFAULT '0.00',
            captain tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=>Captain, 2=>Vice Captain',
            is_rule_violate tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flag for player limit per team rule. 0: No, 1:Yes',
            is_club_mail_send tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flag to identify player club change notification send or not, 0: Not send, 1: Send',
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
                console.log("entering catch block");
                console.log(e);
                console.log("leaving catch block");
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
        console.log("entering catch block");
        console.log(e);
        console.log("leaving catch block");
      }

    return rows;
      
    },
    get_stock_type_data: async  (collection_master_data) => {

        let stock_type   = collection_master_data.stock_type;
        let sql_query=`SELECT config_data FROM ${CONSTANTS.TABLE_PREFIX+CONSTANTS.STOCK_TYPE} WHERE type=${stock_type}`;
        
          let rows=[];
          try {
              rows = await query(sql_query);
            }
            catch (e) {
              console.log("entering catch block");
              console.log(e);
              console.log("leaving catch block");
            }

          return rows;
  },
}