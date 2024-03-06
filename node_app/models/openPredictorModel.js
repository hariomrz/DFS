"use strict";
var express = require('express')
var app = express()
var CONSTANTS = require('../constants')
var helper = require('../helper/default_helper')
var conn = require('../db_open_predictor_config')    
var game_conn = require('../db_fantasy_config')    

let openPredictorModel = {
    get_all_prediction_masters:function(req,cb)
    {
		//req.getConnection(function(error,conn){

            var current_date = helper.format_date();
            if(!req.body.limit)
            {
                req.body.limit=20
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
            var sql_query =`SELECT SQL_CALC_FOUND_ROWS PM.prediction_master_id,PM.desc,PM.category_id,DATE_FORMAT(PM.deadline_date,"%Y-%m-%d %H:%i:%s") as deadline_date,PM.status,PM.total_user_joined,PM.added_date,PM.updated_date,PM.site_rake,PM.total_pool,PM.prize_pool,PM.is_pin,PM.source_desc,PM.source_url
            ,COUNT(UP.user_prediction_id) as total_predictions, 
                IFNULL(SUM(UP.bet_coins),0) as total_pool ,PM.is_pin,C.name as category_name,PM.entry_type,PM.entry_fee,PM.win_prize
                FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
                INNER JOIN `+CONSTANTS.CATEGORY+` C ON C.category_id=PM.category_id
                INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
                LEFT JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id `+user_sql+` 
                WHERE`; 
                
                if(req.body.category_id)
                {
                    sql_query+= ` PM.category_id='`+req.body.category_id+`' AND `;
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
    get_predictions: function (req,pm_ids,cb) {

        //req.getConnection(function(error,conn){
            //req.body.currect_user_id
            var sql_query=`SELECT PO.*,UP.user_id,
               COUNT(UP.user_id) as prediction_count,SUM(IFNULL(UP.bet_coins,0)) as option_total_coins
               FROM `+CONSTANTS.PREDICTION_OPTION+` PO 
               LEFT JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id
               WHERE PO.prediction_master_id IN(`+pm_ids+`) GROUP BY PO.prediction_option_id ORDER BY PO.prediction_option_id ASC`;
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
    get_category_data:(req,cb)=>{
        var sql_query=`SELECT *
            FROM `+CONSTANTS.CATEGORY+` 
            WHERE category_id=`+req.body.category_id;
       
            conn.query(sql_query, function(err, rows,fields) {
         //if(err) throw err
         if (err) {
             cb(err)
         } else {                
             cb(null,rows)
         }
     })
    },
    get_user_predicted:function(req,cb)
    {
        //req.getConnection(function(error,conn){


             var sql_query=`SELECT PM.* 
                FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
                INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
                INNER JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id
                WHERE PM.prediction_master_id=`+req.body.prediction_master_id+` AND UP.user_id=`+req.body.currect_user_id+` GROUP BY PM.prediction_master_id`;

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
    get_prediction_details:function(req,cb){
        var sql_query=`SELECT PM.* 
        FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
        WHERE PM.prediction_master_id=`+req.body.prediction_master_id;

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
    get_my_prediction_category:function(req,cb){
        var sql_cond = ``;
        var current_date = helper.format_date();
        if(parseInt(req.body.prediction_status)  == 2)
        {
            sql_cond = ` PM.status = 2 `
        }
        else if(req.body.prediction_status == 0)
        {
            sql_cond= ` (PM.status = 0 OR PM.status = 3) AND PM.deadline_date >  '`+current_date+`'`;
        }
        else if(req.body.prediction_status == 1)
        {
            sql_cond= ` (PM.status = 0 OR PM.status = 3) AND PM.deadline_date <  '`+current_date+`'`;
        }

        var sql_query =`SELECT DISTINCT PM.category_id,C.name as category_name,C.image 
        FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
        INNER JOIN `+CONSTANTS.CATEGORY+` C ON C.category_id=PM.category_id
        INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
        INNER JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id
        WHERE UP.user_id=`+req.body.currect_user_id+` 
        AND `+sql_cond+` 
         GROUP BY PM.prediction_master_id ORDER BY PM.prediction_master_id DESC LIMIT `+req.body.offset+`,`+req.body.limit;

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
 get_user_prediction_masters:function(req,cb)
    {
        
            var sql_cond = ``;
            var sort_order=`ASC`;
            var current_date = helper.format_date();
            if(parseInt(req.body.status) == 2)
            {
                sql_cond = ` PM.status = 2 `
                sort_order=`DESC`;
            }
            else if(parseInt(req.body.status) == 0)
            {
                sql_cond= ` (PM.status = 0 OR PM.status = 3) AND PM.deadline_date >  '`+current_date+`'`;
            }
            else if(parseInt(req.body.status) == 1)
            {
                sql_cond= ` (PM.status = 0 OR PM.status = 3) AND PM.deadline_date <  '`+current_date+`'`;
            }

            var sql_query = `SELECT PM.*,DATE_FORMAT(PM.deadline_date,"%Y-%m-%d %H:%i:%s") as deadline_date,PM.total_user_joined as total_predictions, 
                IFNULL(PM.total_pool,0) as total_pool,UP.win_coins,PM.entry_type,PM.entry_fee,PM.win_prize,PM.source_desc,PM.source_url,PM.proof_desc,PM.proof_image
                FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
                INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
                LEFT JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id
                WHERE PM.category_id='`+req.body.category_id+`' AND `+sql_cond+` AND UP.user_id=`+req.body.currect_user_id +` GROUP BY PM.prediction_master_id ORDER BY PM.deadline_date `+sort_order;

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
};

module.exports = openPredictorModel;