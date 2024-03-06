"use strict";
var express = require('express')
const util = require('util')
var app = express()
var CONSTANTS = require('../constants')
var helper = require('../helper/default_helper')
var conn = require('../db_prediction_config')    
var game_conn = require('../db_fantasy_config')    
var user_pool = require('../user_db')    

var query = util.promisify(conn.query).bind(conn);
let predictionModel = {
    get_prediction_masters:async function(req)
    {
        var current_date = helper.format_date();
        if(!req.body.limit)
        {
            req.body.limit=10
        }

        if(!req.body.offset)
        {
            req.body.offset=0
        }

        var user_sql = '';
        if(req.body.currect_user_id)
        {
            user_sql = ' AND UP.user_id<>'+req.body.currect_user_id;
        }

        var sql_str = ``;
        if(req.body.prediction_master_id)
        {
            sql_str=` AND PM.prediction_master_id=`+req.body.prediction_master_id+` `;
        }

        var inner_query=`(select p.prediction_master_id from `+CONSTANTS.USER_PREDICTION+` as u INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` as p on p.prediction_option_id=u.prediction_option_id and u.user_id = `+req.body.currect_user_id+`)`
        var sql_query =`SELECT SQL_CALC_FOUND_ROWS PM.prediction_master_id,PM.desc,PM.season_game_uid,PM.sports_id,DATE_FORMAT(PM.deadline_date,"%Y-%m-%d %H:%i:%s") as deadline_date,PM.status,PM.total_user_joined,PM.added_date,PM.updated_date,PM.site_rake,PM.total_pool,PM.prize_pool,PM.is_pin
        ,COUNT(UP.user_prediction_id) as total_predictions, 
            IFNULL(SUM(UP.bet_coins),0) as total_pool ,PM.is_pin
            FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
            INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
            LEFT JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id `+user_sql+` 
            WHERE PM.season_game_uid='`+req.body.season_game_uid+`' `+sql_str+` AND PM.deadline_date>'`+current_date+`' AND PM.status=0 AND PO.prediction_master_id NOT IN `+inner_query+` GROUP BY PM.prediction_master_id ORDER BY PM.is_pin DESC,PM.deadline_date ASC,PM.prediction_master_id DESC LIMIT `+req.body.offset+`,`+req.body.limit;
            
        let rows =[];
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
    get_season_data:(req,cb)=>{
        var sql_query=`SELECT S.home_uid,S.away_uid,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) as  away,S.season_game_uid,DATE_FORMAT(S.season_scheduled_date,"%Y-%m-%d %H:%i:%s") as season_scheduled_date,IFNULL(L.league_display_name,L.league_name) AS league_name,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,S.format,IFNULL(S.score_data,'') as score_data,S.playing_announce,S.delay_minute,S.delay_message,S.custom_message
            FROM vi_season S
            INNER JOIN vi_league L ON L.league_id = S.league_id
            INNER JOIN vi_team_league TL1 ON TL1.team_uid = S.home_uid AND TL1.league_id = S.league_id
            INNER JOIN vi_team T1 on T1.team_id = TL1.team_id
            INNER JOIN vi_team_league TL2 ON TL2.team_uid = S.away_uid AND TL2.league_id = S.league_id
            INNER JOIN vi_team T2 on T2.team_id = TL2.team_id
            WHERE S.season_game_uid='${req.body.season_game_uid}'`;
       
       game_conn.query(sql_query, function(err, rows,fields) {
         //if(err) throw err
         if (err) {
             cb(err)
         } else {                
             cb(null,rows)
         }
     })
    },
    get_predictions: async function (req,pm_ids,cb) {
        var sql_query=`SELECT PO.*,UP.user_id,
           COUNT(UP.user_id) as prediction_count,SUM(IFNULL(UP.bet_coins,0)) as option_total_coins
           FROM `+CONSTANTS.PREDICTION_OPTION+` PO 
           LEFT JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id
           WHERE PO.prediction_master_id IN(`+pm_ids+`) GROUP BY PO.prediction_option_id ORDER BY PO.prediction_option_id ASC`;

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
    get_user_predicted:async function(req)
    {

        var sql_query =`SELECT PM.prediction_master_id,PO.is_correct,PM.status
                FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
                INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
                INNER JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id  
                WHERE PM.season_game_uid='`+req.body.season_game_uid+`' AND UP.user_id=${req.body.currect_user_id}`;
       
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
    get_prediction_details:function(req,cb){
        var sql_query=`SELECT PM.* 
        FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
        WHERE PM.prediction_master_id=`+req.body.prediction_master_id+` AND PM.status>0`;

     conn.query(sql_query, function(err, rows,fields) {
        //if(err) throw err
        if (err) {
            cb(err)
        } else {                
            cb(null,rows)
        }
    })

    },
    make_user_prediction:function(req,cb)
    {
        var datetime = helper.format_date();
        var user_prediction_obj = {
            user_id: req.body.currect_user_id,
            prediction_option_id: req.body.prediction_option_id,
            bet_coins:req.body.bet_coins,
            added_date:datetime,
            updated_date: datetime
        }

        //req.getConnection(function(error,conn){
              conn.query(`INSERT INTO `+CONSTANTS.USER_PREDICTION+` SET ?`, user_prediction_obj, function(err, result) {

                 if (err) {
                                cb(err)
                            } else {                
                                cb(null,result)
                            }
                })
             
        //})
    },
    get_prediction_master_details:function(req,cb){

        var prediction_master_sql = `SELECT * FROM `+CONSTANTS.PREDICTION_MASTER+ ` WHERE prediction_master_id =`+req.body.prediction_master_id;

        conn.query(prediction_master_sql, function(err, result) {
        //if(err) throw err
        if (err) {
            cb(err)
        } else {   
            cb(null,result)
        }
    })

    },
    update_prediction_master:function(req,user_amount,cb){
        var prediction_master_sql = `UPDATE `+CONSTANTS.PREDICTION_MASTER+ ` SET total_pool=total_pool+`+req.body.bet_coins+`,prize_pool=prize_pool+`+user_amount+`,total_user_joined=total_user_joined+1   WHERE prediction_master_id =`+req.body.prediction_master_id;
        conn.query(prediction_master_sql, function(err, rows,fields) {
            //if(err) throw err
            if (err) {
                cb(err)
            } else {   
                cb(null,rows)
            }
        })

    },
    get_my_prediction_fixtures:function(req,cb){
            var sql_cond = ``;
            var current_date = helper.format_date();
            if(parseInt(req.body.match_status)  == 2)
            {
                sql_cond = ` PM.status = 2 `
            }
            else if(req.body.match_status == 0)
            {
                sql_cond= ` (PM.status = 0 OR PM.status = 3) AND PM.deadline_date >  '`+current_date+`'`;
            }
            else if(req.body.match_status == 1)
            {
                sql_cond= ` (PM.status = 0 OR PM.status = 3) AND PM.deadline_date <  '`+current_date+`'`;
            }

            var sql_query =`SELECT SQL_CALC_FOUND_ROWS DISTINCT PM.season_game_uid 
            FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
            INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
            INNER JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id
            WHERE UP.user_id=`+req.body.currect_user_id+` 
            AND `+sql_cond+` 
            AND PM.sports_id=`+req.body.sports_id+`
             GROUP BY PM.prediction_master_id ORDER BY PM.prediction_master_id DESC LIMIT `+req.body.offset+`,`+req.body.limit;

             //console.log("SQL:",sql_query);
            conn.query(sql_query, function(err, rows,fields) {
               
                if (err) {
                    cb(err)
                } else {   
                    cb(null,rows)
                }
            })
        
    },
      get_user_prediction_masters:function(req,cb)
    {
        
            var sql_cond = ``;
            var current_date = helper.format_date();
            let status = parseInt(req.body.status); 
            if(status == 2)
            {
                //sql_cond = ` PM.status > 0 `
                sql_cond = ` PM.status = 2 `;
            }
            else if(status == 0)
            {
                sql_cond= ` (PM.status = 0 OR PM.status = 3) AND PM.deadline_date >  '`+current_date+`'`;
            }
            else if(status == 1)
            {
                sql_cond= ` (PM.status = 0 OR PM.status = 3) AND PM.deadline_date <  '`+current_date+`'`;
            }
            else if(status == 3)
            {
                sql_cond= ` (PM.status = 0 OR PM.status = 3) `;
            }

            var sql_query = `SELECT PM.*,PM.total_user_joined as total_predictions, 
                IFNULL(PM.total_pool,0) as total_pool,UP.win_coins
                FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
                INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
                LEFT JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id
                WHERE PM.season_game_uid='`+req.body.season_game_uid+`' AND `+sql_cond+` AND UP.user_id=`+req.body.currect_user_id +` GROUP BY PM.prediction_master_id ORDER BY PM.updated_date DESC`;

                conn.query(sql_query, function(err, rows,fields) {
                //if(err) throw err
                if (err) {
                    cb(err)
                } else {                
                    cb(null,rows)
                }
            })
        //})
    },
    get_user_predictions: function (req,pm_ids,cb) {

            if(pm_ids=='')
            {
                cb(null,[]);
                return false;
            }

            let sql_query =`SELECT PO.*,UP.user_id,
               COUNT(UP.prediction_option_id) as prediction_count,IF(PO.prediction_option_id= UP1.prediction_option_id ,UP.prediction_option_id,0) as user_selected_option,IFNULL(UP1.bet_coins,'') as bet_coins,SUM(IFNULL(UP.bet_coins,0)) as option_total_coins
               FROM `+CONSTANTS.PREDICTION_OPTION+` PO 
               LEFT JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id
               LEFT JOIN `+CONSTANTS.USER_PREDICTION+` UP1 ON  UP1.prediction_option_id=PO.prediction_option_id AND UP1.user_id=`+req.body.currect_user_id+`
               WHERE PO.prediction_master_id IN(`+pm_ids+`) GROUP BY PO.prediction_option_id ORDER BY PO.prediction_option_id ASC LIMIT 0,1000`
               
             conn.query(sql_query, function(err, rows,fields) {
               
                if (err) {
                    cb(err)
                } else {                
                    cb(null,rows)
                }
            })
    },
  
    get_user_estimated_winning:(req,cb)=>{

        var pre_query =`(SELECT SUM(UP.bet_coins) as option_total,GROUP_CONCAT(UP.user_id) as user_ids,PO.prediction_option_id
FROM vi_prediction_master PM 
INNER JOIN vi_prediction_option PO ON PM.prediction_master_id=PO.prediction_master_id 
INNER JOIN vi_user_prediction UP ON UP.prediction_option_id=PO.prediction_option_id  
GROUP BY PO.prediction_option_id)`;

        var sql_query =`SELECT PM.prediction_master_id,PM.prize_pool,UP.bet_coins,PM.total_pool,TR.option_total,
        ROUND((PM.prize_pool*(UP.bet_coins/TR.option_total)))  as estimated_winning,UP.win_coins FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
        INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PM.prediction_master_id=PO.prediction_master_id 
        INNER JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id 
        INNER JOIN `+pre_query+` TR ON TR.prediction_option_id=UP.prediction_option_id WHERE UP.user_id=`+req.body.currect_user_id+` GROUP BY PO.prediction_option_id`;

        conn.query(sql_query, function(err, rows,fields) {
           
            if (err) {
                cb(err)
            } else {                
                cb(null,rows)
            }
        })
    }
     ,
    get_prediction_pool:function(req,cb){

        let sql_query =`SELECT PM.deadline_date,IFNULL(TRUNCATE((CEIL((SUM(UP.bet_coins)/100000))+1)*100000,0),0) as total_pool
               FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
               LEFT JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id=PM.prediction_master_id
               LEFT JOIN `+CONSTANTS.USER_PREDICTION+` UP ON  UP.prediction_option_id=PO.prediction_option_id 
               WHERE PM.season_game_uid='`+req.body.season_game_uid+`'`
              
             conn.query(sql_query, function(err, rows,fields) {
                
                if (err) {
                    cb(err)
                } else {                
                    cb(null,rows)
                }
            })

    },
    check_prediction_joined:(req,cb)=>{
        let sql_query =`SELECT PM.prediction_master_id,UP.user_id
        FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
        INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id=PM.prediction_master_id
        INNER JOIN `+CONSTANTS.USER_PREDICTION+` UP ON  UP.prediction_option_id=PO.prediction_option_id 
        WHERE PM.prediction_master_id='`+req.body.prediction_master_id+`' AND UP.user_id=`+req.body.currect_user_id;
        conn.query(sql_query, function(err, rows,fields) {
         
         if (err) {
             cb(err)
         } else {                
             cb(null,rows)
         }
     })
    },
    get_all_prediction_masters:function(req,cb)
    {
		//req.getConnection(function(error,conn){

            var current_date = helper.format_date();
            if(!req.body.limit)
            {
                req.body.limit=10
            }

            if(!req.body.offset)
            {
                req.body.offset=0
            }

            var user_sql = '';
            if(req.body.currect_user_id)
            {
                user_sql = ' AND UP.user_id<>'+req.body.currect_user_id;
            }

            var sql_str = ``;
            if(req.body.prediction_master_id)
            {
                sql_str=` AND PM.prediction_master_id=`+req.body.prediction_master_id+` `;
            }

            var inner_query=`(select p.prediction_master_id from `+CONSTANTS.USER_PREDICTION+` as u INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` as p on p.prediction_option_id=u.prediction_option_id and u.user_id = `+req.body.currect_user_id+`)`
            var sql_query =`SELECT SQL_CALC_FOUND_ROWS PM.prediction_master_id,PM.desc,PM.season_game_uid,PM.sports_id,DATE_FORMAT(PM.deadline_date,"%Y-%m-%d %H:%i:%s") as deadline_date,PM.status,PM.total_user_joined,PM.added_date,PM.updated_date,PM.site_rake,PM.total_pool,PM.prize_pool,PM.is_pin
            ,COUNT(UP.user_prediction_id) as total_predictions, 
                IFNULL(SUM(UP.bet_coins),0) as total_pool ,PM.is_pin
                FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
                INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
                LEFT JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id `+user_sql+` 
                WHERE`; 
                
                if(req.body.season_game_uid)
                {
                    sql_query+= `PM.season_game_uid='`+req.body.season_game_uid+`' AND `;
                }
                
                sql_query+=` PM.deadline_date>'`+current_date+`' `+sql_str+` AND PM.status=0 AND PO.prediction_master_id NOT IN `+inner_query+` GROUP BY PM.prediction_master_id ORDER BY PM.is_pin DESC,PM.deadline_date ASC,PM.prediction_master_id DESC LIMIT `+req.body.offset+`,`+req.body.limit;

            
      
        	 conn.query(sql_query, function(err, rows,fields) {
                //if(err) throw err
                if (err) {
                    cb(err)
                } else {              
                    cb(null,rows)
                }
            })
        //})
    },
    get_coin_balance: async (user_id)=>{

        const qb = await user_pool.get_connection();
        try {
            const response = await qb.select('point_balance')
                .from(CONSTANTS.USER)
                .where('user_id',user_id)
                .limit(1)
                .get();

       // console.log("Query : " + qb.last_query());
            return response[0]['point_balance'];
        } catch (err) {
            return console.error("Uh oh! Couldn't get results (Get Coin Balance): " + err);
        } finally {
            //qb.disconnect();
        }
    }


};




module.exports = predictionModel;