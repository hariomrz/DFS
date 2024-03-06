"use strict";
const { connections } = require('mongoose');
let helper = require('../helper/default_helper')
const gameCenterModel  = require('../models/gameCenterModel')

exports.get_live_match_list = async function(req,res)
{
    console.log('fdfd');
    helper.res.data = {};
    const cache_key ="gc_live_matches";
    var live_match =await helper.get_cache_data(cache_key);
    if(!live_match)
    {
        live_match = await gameCenterModel.get_live_match_list(req.body.sports_id);
        helper.set_cache_data(cache_key,live_match);
    }

    if(!live_match.length)
    {
    	  helper.res.message ='No Match available.'
    	  helper.res.data = {};
    	  helper.res.response_code =200;
    	  res.json(helper.res)
    	  return;
    }

    helper.res.data.live_match = live_match;
    helper.res.response_code = 200
    res.json(helper.res)
    return ;
}