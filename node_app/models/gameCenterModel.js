"use strict";
var util = require('util');
var helper = require('../helper/default_helper')
var conn = require('../db_fantasy_config')  
var query = util.promisify(conn.query).bind(conn);
var cnst = require('../constants')

module.exports = {
  get_match_score: async function(collection_master_id){
    let sql_query = `cm.collection_master_id,s.season_game_uid,s.season_scheduled_date,s.score_data,l.sports_id,s.status,cm.deadline_time,s.batting_team_uid,IFNULL(t1.display_team_abbr,t1.team_abbr) as home,,IFNULL(t2.display_team_abbr,t2.team_abbr) as away 
    FROM ${cnst.TABLE_PREFIX+cnst.COLLECTION_MASTER} cm
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.COLLECTION_SEASON} cs ON cs.collection_master_id=cm.collection_master_id
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.SEASON} s ON s.season_id=cs.season_id
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.LEAGUE} l ON s.league_id=l.league_id 
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.TEAM} t1 ON t1.team_id=s.home_id 
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.TEAM} t2 ON t2.team_id=s.away_id 
    WHERE cs.collection_master_id=${collection_master_id}
    `;

    let rows =[];
    try {
        rows = await query(sql_query);
      }
      catch (e) {
       console.log(e);
       console.log("get_match_score - catch block");
     }
    return rows[0];
  },
  get_match_users_rank: async function(collection_master_id,users_list){
    let sql_query =`SELECT lm.user_name,lm.team_name,lm.user_id,lmc.total_score,lmc.game_rank,c.contest_title as contest_name,c.prize_distibution_detail,lmc.is_winner,lmc.contest_id
    FROM ${cnst.TABLE_PREFIX+cnst.LINEUP_MASTER} lm
    INNER JOIN  ${cnst.TABLE_PREFIX+cnst.LINEUP_MASTER_CONTEST} lmc ON lm.lineup_master_id=lmc.lineup_master_id
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.CONTEST} c ON c.contest_id=lmc.contest_id 
    WHERE c.status !=1 AND lm.collection_master_id=${collection_master_id} AND 
    lm.user_id IN(${users_list.join(',')})
    ORDER BY lmc.game_rank ASC`;

    let rows =[];
    try {
        rows = await query(sql_query);
      }
      catch (e) {
       console.log(e);
       console.log("get_match_users_rank - catch block");
     }
    return rows;
  },
  get_live_match_list: async function(sports_id){
    let current_date = helper.format_date();
    let sql_query = `SELECT cm.collection_master_id,s.season_game_uid,s.season_scheduled_date,IFNULL(T2.display_team_abbr,T2.team_abbr) as home,IFNULL(T2.display_team_abbr,T2.team_abbr) as away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag
    FROM ${cnst.TABLE_PREFIX+cnst.SEASON} s
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.LEAGUE} l ON l.league_id=s.league_id
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.COLLECTION_SEASON} cs ON s.season_id=cs.season_id
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.COLLECTION_MASTER} cm ON cm.collection_master_id=cs.collection_master_id 
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.TEAM} T1 ON T1.team_id = S.home_id 
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.TEAM} T2 ON T2.team_id = S.away_id
    WHERE l.sports_id=${sports_id}
    AND s.season_scheduled_date <='${current_date}'
    AND s.status =1 
    AND cm.is_gc = 1
    AND cm.season_game_count = 1
    ORDER BY s.season_scheduled_date DESC LIMIT 0,10`;

    let rows =[];
    try {
      console.log('SQL:',sql_query);
        rows = await query(sql_query);
      }
      catch (e) {
       console.log(e);
       console.log("get_live_match_list - catch block");
     }
    return rows;
  }
}
