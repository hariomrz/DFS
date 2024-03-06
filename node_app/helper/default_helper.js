

const request = require("request");
const moment = require('moment');
const util = require('util');
var CONSTANTS = require('../constants')
var config = require('../config')[process.env.NODE_ENV]
var conn = require('../db_prediction_config')    
var user_conn = require('../db_user_config')    
var main_config = require('../config')

const redis = require('redis');
var main_config = require('../config');
const client = redis.createClient(main_config.redis);


var query = util.promisify(conn.query).bind(conn);

var user_query = util.promisify(user_conn.query).bind(user_conn);
var helper = {

   res: {
       response_code:500,
       service_name:'',
       message:'',
       error:{},
       data:{},
       global_error:""     
    },
    mongo_url:function() {
      var mongo_db_url = '';
      var mongo_srv= '';
      if(main_config.mongo_dtls.srv===1 || main_config.mongo_dtls.srv==1)
      {
        var mongo_srv= '+srv';
      }
      if(!main_config.mongo_dtls.user)
      {
        mongo_db_url = 'mongodb'+mongo_srv+'://'+main_config.mongo_dtls.host;
      }
      else
      {
        mongo_db_url = 'mongodb'+mongo_srv+'://'+main_config.mongo_dtls.user+`:`+main_config.mongo_dtls.password+`@`+main_config.mongo_dtls.host;
      } 
    
      return mongo_db_url
  },
   mongoose_url:function() {
      var mongo_db_url = '';
      var mongo_srv= '';
      if(main_config.mongo_dtls.srv===1)
      {
        var mongo_srv= '+srv';
      }
      if(!main_config.mongo_dtls.user)
      {
        mongo_db_url = 'mongodb'+mongo_srv+'://'+main_config.mongo_dtls.host+'/'+main_config.mongo_dtls.db_name;
      }
      else
      {
        mongo_db_url = 'mongodb'+mongo_srv+'://'+main_config.mongo_dtls.user+`:`+main_config.mongo_dtls.password+`@`+main_config.mongo_dtls.host+'/'+main_config.mongo_dtls.db_name;
      } 
      return mongo_db_url
  },

    mongo_url:function() {
      var mongo_db_url = '';
      var mongo_srv= '';
      if(main_config.mongo_dtls.srv===1 || main_config.mongo_dtls.srv==1)
      {
        var mongo_srv= '+srv';
      }
      if(main_config.mongo_dtls.user == '')
      {
        mongo_db_url = 'mongodb'+mongo_srv+'://'+main_config.mongo_dtls.host;
      }
      else
      {
        mongo_db_url = 'mongodb'+mongo_srv+'://'+main_config.mongo_dtls.user+`:`+main_config.mongo_dtls.password+`@`+main_config.mongo_dtls.host;
      } 
    console.log('detl;',mongo_db_url);
      return mongo_db_url
  },
   mongoose_url:function() {
      var mongo_db_url = '';
      var mongo_srv= '';
      if(main_config.mongo_dtls.srv===1)
      {
        var mongo_srv= '+srv';
      }
      if(!main_config.mongo_dtls.user)
      {
        mongo_db_url = 'mongodb'+mongo_srv+'://'+main_config.mongo_dtls.host+'/'+main_config.mongo_dtls.db_name;
      }
      else
      {
        mongo_db_url = 'mongodb'+mongo_srv+'://'+main_config.mongo_dtls.user+`:`+main_config.mongo_dtls.password+`@`+main_config.mongo_dtls.host+'/'+main_config.mongo_dtls.db_name;
      } 
      return mongo_db_url
  },
    array_column_key:function(array, columnName) {

        var temp = {};
        array.forEach(function(element) {
          temp[element[columnName]] = element;
        })
        return temp
    },

    array_column:function(array, columnName) {

        var temp = [];
        array.forEach(function(element) {
          temp.push(element[columnName]);
        })
        return temp
    },
    array_values : function(input) {

          var tmpArr = []
          var key = ''

          for (key in input) {
            tmpArr[tmpArr.length] = input[key]
          }

          return tmpArr
        },
       onlyUnique:(value, index, self)=> { 
          return self.indexOf(value) === index;
      },
     filterError:function(errors)
     {
        var temp = {};

         for (key in errors) {
            temp[errors[key]['param']] = errors[key]['msg']
          }

          return temp;
     },
     authenticateSession:function(req,res,cb){
          var options = {
             headers: {
                'Accept': 'application/json',
                'Accept-Charset': 'utf-8'
               
              },
            url: process.env.HTTP_PROTOCOL+'://'+process.env.DOMAIN_NAME+'/user/auth/session_key_validate',
            method: 'POST',
            formData: {
            Sessionkey: req.headers.sessionkey?req.headers.sessionkey:''
            } 
          }

        function callback(error, response, body) {
            var authResponse =JSON.parse(body);
            if(authResponse.response_code ==500 || authResponse.response_code ==401 )
            {
              res.json(authResponse);
            }
            else
            {
              cb(null,authResponse);
            }
        }
        request(options, callback);

     } ,
     httpRequest:function(url,data,req,res,cb){
         var options = {
             headers: {
                'Accept': 'application/json',
                'Accept-Charset': 'utf-8',
                'Sessionkey':req.headers.sessionkey
               
              },
            url: process.env.HTTP_PROTOCOL+'://'+process.env.DOMAIN_NAME+'/'+url,
            method: 'POST',
            formData: data
          }

        function callback(error, response, body) {
            var authResponse = JSON.parse(body);
            if(authResponse.response_code ==500)
            {
              res.json(authResponse);
            }
            else
            {
              cb(null,authResponse.data);
            }
        }

        request(options, callback);

     },
     crawlRequest:function(url,data,req,res,cb){
      var options = {
          headers: {
             'Accept': 'text/html',
             'Accept-Charset': 'utf-8',
             //'session_key':req.headers.session_key
            
           },
         url: url,
         method: 'GET',
         //formData: data
       }

     function callback(error, response, body) {
       cb(null,body);
     }

     request(options, callback);

  },
     nodeHttpRequest:function(url,data,req,res,cb){
      var options = {
          headers: {
             'Accept': 'application/json',
             'Accept-Charset': 'utf-8',
             'Sessionkey':req.headers.sessionkey
            
           },
         url: process.env.NODE_BASE_URL+url,
         method: 'POST',
         formData: data
       }

     function callback(error, response, body) {
         var authResponse =JSON.parse(body);
         if(authResponse.response_code ==500)
         {
           res.json(authResponse);
         }
         else
         {
           cb(null,authResponse.data);
         }
     }
     request(options, callback);
  },
     format_date:function( date  ,format  )
     {
        if(!format)
        {
          format =CONSTANTS.DEFAULT_DATETIME_FORMAT
        }

        if(!date)
        {
          date =CONSTANTS.DEFAULT_DATETIME
        }

        if(date!=='' && process.env.NODE_ENV !=='production')
        {
           return moment(date).format(format)
        }
        else
        {
          return moment(new Date()).format(format)
        }
      
    },
    getUtcTime:(date)=>{

      if(!date)
      {
         return moment.utc().valueOf();
      }
      else{
         return moment.utc( new Date(date) ).valueOf()
      }
    },
    get_total_rows:async function(req,cb)
    {
        try {
          rows = await query(`SELECT FOUND_ROWS() as total`);
        }
        catch (e) {
        console.log(e);
        console.log("helper total row - catch block");
      }
      return rows[0].total;
    
    },
    get_total_rows1: function(req,cb)
    {
    
      conn.query(`SELECT FOUND_ROWS() as total`, function(err, rows,fields) {
               
        if (err) {
            cb(err)
        } else {   
            cb(null,rows[0].total)
        }
    })
    
    },
    get_cache_data:async (cache_key)=>{
      if (!cache_key || !CONSTANTS.ENABLE_REDIS) {
        return false;
      }

      const data= await client.get(`${CONSTANTS.CACHE_PREFIX}_${cache_key}`);
      return data;
    },
    set_cache_data:(cache_key,data_arr,expire_time=3600)=>{
      if (!cache_key || !CONSTANTS.ENABLE_REDIS) {
        return false;
      }

      client.setex(`${CONSTANTS.CACHE_PREFIX}_${cache_key}`, expire_time, JSON.stringify(data_arr));
      return true;
     
    },
    get_app_config:async function()
    {
          var sql_query = `SELECT * FROM vi_app_config`;
            try {
              rows = await user_query(sql_query);
            }
            catch (e) {
             console.log(e);
             console.log("helper app config - catch block");
           }
        return rows;
       
    },
    get_app_config_value:async function (key_name){
      var app_config = await this.get_app_config();
	    var app_config =this.array_column_key(app_config,'key_name');
      return app_config[key_name]['key_value'];
    }
}
   

module.exports = helper


