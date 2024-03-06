"use strict";
var express = require('express')
var app = express()
var async =  require('async');
var CONSTANTS = require('../constants')
var helper = require('../helper/default_helper')
var main_config = require('../config')
var _ = require('lodash')

var user_conn = require('../db_user_config')  

var MongoClient = require('mongodb').MongoClient;
var mongo_db_url = helper.mongo_url();
// if(!main_config.mongo_dtls.user)
// {
//  	mongo_db_url = 'mongodb://'+main_config.mongo_dtls.host;
// }
// else{
// 	mongo_db_url = 'mongodb://'+main_config.mongo_dtls.user+`:`+main_config.mongo_dtls.password+`@`+main_config.mongo_dtls.host;
// }

var mongo_client = 	MongoClient.connect(mongo_db_url,{ useNewUrlParser: true });

exports.session_key_validate = function(req,res,cb)
{
    req.checkBody('Sessionkey', 'Please provide session key.').notEmpty();

    // check for errors!
    helper.res.error = {};
    helper.res.data = {};
    helper.res.global_error  = '';
    var errors = req.validationErrors()
    if (errors) {
        helper.res.error = helper.filterError(errors)
        helper.res.response_code = 500
        helper.res.global_error =errors[0].msg
      res.status(500).send(helper.res)
      return;
    }

    var Sessionkey = req.body.Sessionkey;
    var role = 1;
    async.waterfall([
        function get_one_record(done){
            mongo_client.then(function (client) { 
                var db = client.db(main_config.mongo_dtls.db_name);
                db.collection('active_login').findOne({Sessionkey:Sessionkey},function(err, obj) {
                    if (err)
                    {
                        throw err;
                      } 
                      
                      if(obj)
                      {
                        cb(null,obj);
                    }
                    else{
                        done(null,[]);
                    }
    
                });
              })
              .catch(function (err) {
              })
        },
        (activeResp,done)=>{

            user_conn.query(`SELECT U.user_id,user_unique_id,date_created,first_name,last_name,email,user_name,status,U.bonus_balance,U.winning_balance,U.balance,U.referral_code,AL.role,AL.device_type,U.point_balance,AL.device_id,U.language,U.phone_no 
            FROM vi_active_login AL 
            INNER JOIN vi_user U ON U.user_id = AL.user_id WHERE AL.key='`+Sessionkey+`' LIMIT 1`, function(err,rows,fields) {
                if (err) throw err;

                if(rows.length > 0 )
                {
                  mongo_client.then(function (client) { 

                    var db = client.db(main_config.mongo_dtls.db_name);
                     // insert document to 'users' collection using insertOne
                     db.collection("active_login").insertOne(rows[0], function(err, response) {
                      if (err) throw err;
                      cb(null,rows[0]);
                    });
                  })
                  .catch(function (err) {
                  })  
                }
                else
                {
                    helper.res.global_error ='Unauthorized'
                      helper.res.data = [];
                      helper.res.response_code =500;
                      res.json(helper.res)
                      return;
                }
            });

        }
        ],
      function (err) {
        if (err) {
          throw new Error(err);
        } else {
        }
      })


}