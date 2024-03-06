
"use strict";
var util = require('util');
var helper = require('../helper/default_helper')
var conn = require('../db_opinion_trade')  
var query = util.promisify(conn.query).bind(conn);
var cnst = require('../constants')

module.exports = {

  get_match_score: async function(collection_id){

    return 0; 
  },
  get_match_users_rank: async function(collection_id,users_list){

    
    return 0;
  }
}