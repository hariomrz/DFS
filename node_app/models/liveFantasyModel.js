
"use strict";
var util = require('util');
var helper = require('../helper/default_helper')
var conn = require('../db_live_fantasy')  
var query = util.promisify(conn.query).bind(conn);
var cnst = require('../constants')

module.exports = {

  get_match_score: async function(collection_id){

    let sql_query = `SELECT cm.collection_id,s.season_game_uid,s.season_scheduled_date,IFNULL(t1.display_team_abbr,t1.team_abbr) AS home,IFNULL(t2.display_team_abbr,t2.team_abbr) AS away,s.score_data,l.sports_id,s.status
    FROM ${cnst.TABLE_PREFIX+cnst.COLLECTION} cm
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.SEASON} s ON s.season_game_uid=cm.season_game_uid AND s.league_id=cm.league_id
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.TEAM} t1 ON t1.team_uid=s.home_uid
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.TEAM} t2 ON t2.team_uid=s.away_uid
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.LEAGUE} l ON s.league_id=l.league_id
    WHERE cm.collection_id=${collection_id}
    ` ;

    let rows =[];
    try {
        rows = await query(sql_query);
      }
      catch (e) {
       console.log(e);
       console.log("LF get_match_score - catch block");
     }
    return rows[0];
  },
  get_match_users_rank: async function(collection_id,users_list){

    let sql_query =`SELECT ut.user_team_id,ut.user_name,ut.team_name,ut.user_id,uc.total_score,uc.game_rank,uc.is_winner,uc.contest_id,c.contest_title as contest_name,c.prize_distibution_detail as prize_detail
    FROM ${cnst.TABLE_PREFIX+cnst.USER_TEAM} ut
    INNER JOIN  ${cnst.TABLE_PREFIX+cnst.USER_CONTEST} uc ON uc.user_team_id=ut.user_team_id
    INNER JOIN ${cnst.TABLE_PREFIX+cnst.CONTEST} c ON c.contest_id=uc.contest_id 
    WHERE uc.fee_refund=0 AND c.status !=1 AND c.total_user_joined >= c.minimum_size AND c.collection_id=${collection_id} AND ut.collection_id=${collection_id} AND 
    ut.user_id IN(${users_list.join(',')})
    GROUP BY uc.user_contest_id
    ORDER BY uc.game_rank ASC`;
    let rows =[];
    try {
        rows = await query(sql_query);
      }
      catch (e) {
       console.log(e);
       console.log("LF get_match_users_rank - catch block");
     }
    return rows;
  }
}