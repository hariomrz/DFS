"use strict";
var express = require('express')
var app = express()
var async =  require('async');
var CONSTANTS = require('../constants')
var helper = require('../helper/default_helper')
var main_config = require('../config')
var config = main_config[process.env.NODE_ENV]
const redis = require('redis')
const _ = require('lodash')
const request = require('request')
const client = redis.createClient(main_config.redis);
var moment =  require('moment')
/**
 * Store database credentials in a separate config.js file
 * Load the file/module and its values
 */ 
var predictionModel  = require('../models/predictionModel')

exports.getPredictions = async function(req,res)
{
	helper.res.error = {};
	helper.res.data = {};
	helper.res.message='';

	req.body.currect_user_id = 0;
	if(req.body.user_data)
	{
  		req.body.currect_user_id = req.body.user_data.user_id
	}

	if(!req.body.limit)
	{
  		req.body.limit=10000	
	}

	if(!req.body.offset)
	{
  		req.body.offset = 0
	}

	//get user predicted data
	var user_predicted = await predictionModel.get_user_predicted(req);
	var pm_res = await predictionModel.get_prediction_masters(req);
	if(!pm_res.length)
	{
		helper.res.message ='No prediction available.'
		helper.res.data = {};
		helper.res.data.user_predicted = user_predicted;
		helper.res.data.predictions = [];
		helper.res.response_code =200;
		res.json(helper.res)
		return;
  	}

	var pr_data ={}
	pr_data.result = pm_res;
	var total = await helper.get_total_rows(req);
	pr_data.total = total;

	let pm_ids = pm_res.map(function(e){return e.prediction_master_id}).join(',')
	var predictions= await predictionModel.get_predictions(req,pm_ids);

	let predictions_list = helper.array_values(pm_res);
	pm_ids = helper.array_column(pm_res,'prediction_master_id')

	let prediction_options = {};
	predictions.forEach(function(element,index){
		if(!prediction_options[element.prediction_master_id])
		{
			prediction_options[element.prediction_master_id] = [];
			prediction_options[element.prediction_master_id].push(element); 
		}
		else
		{
			prediction_options[element.prediction_master_id].push(element); 
		}
	});

	predictions_list.forEach((prediction,index)=>{
		if(prediction_options[prediction.prediction_master_id])
		{
			prediction.option = prediction_options[prediction.prediction_master_id];

				if(!prediction.today)
			{
				var todayDate =helper.getUtcTime();
				var deadlineDate =  helper.getUtcTime(prediction.deadline_date);
				prediction.today =todayDate*1000;
				prediction.deadline_time =deadlineDate*1000;
			}
		}
	})

	
	helper.res.data.predictions = helper.array_values(predictions_list)
	helper.res.data.total = pr_data.total
	//helper.res.data.coin_balance = await predictionModel.get_coin_balance(req.body.currect_user_id);
	helper.res.data.user_predicted = user_predicted;
	if(helper.res.data.predictions==undefined)
	{
		helper.res.data.predictions = [];
	}
	helper.res.data.offset = req.body.offset+helper.res.data.predictions.length
	helper.res.response_code = 200
	res.json(helper.res)
}

exports.getOnePrediction = function(req,res)
{
	  req.checkBody('season_game_uid', 'Please provide a valid match id.').notEmpty();

	  // check for errors!
	  helper.res.error = {};
	  helper.res.data = {};
	  var errors = req.validationErrors()
	  if (errors) {
	  	helper.res.error = helper.filterError(errors)
	  	helper.res.response_code = 500
	  	helper.res.global_error =errors[0].msg
	    res.status(500).send(helper.res)
	    return;
	  }

	  req.body.currect_user_id = req.body.user_data.user_id
	  async.waterfall([
	  	
	  	function firstStep(done) {
		    predictionModel.get_prediction_masters(req,function(pm_err,pm_res){
			  	if(pm_err)
			  	{
		 			res.status(500).send(pm_err);
			   		return;
			  	}

			  	if(!pm_res.length)
			  	{
			  		helper.res.message ='No prediction available.'
			  		helper.res.data = [];
			  		helper.res.response_code =200;
				  	res.json(helper.res)
				  	return;
			  	}

	  	 		done(null, pm_res); // <- set value to passed to step 2
		  	})

		  },
		  function secondStep(pm_res, done) {

		  	//var pm_res = pr_data.result
		    let pm_ids = pm_res.map(function(e){return e.prediction_master_id}).join(',')
		    predictionModel.get_predictions(req,pm_ids,function(err,r){
			  	if(err)
			  	{
			  		res.status(500).send(err);
			   		return
			  	}
			  	let predictions_list = helper.array_column_key(pm_res,'prediction_master_id')
				r.forEach(function(element){
					
					if(predictions_list[element.prediction_master_id])
					{
						if(!predictions_list[element.prediction_master_id].option)
						{
							predictions_list[element.prediction_master_id].option = []
						}
						predictions_list[element.prediction_master_id].option.push(element)
					}
				});
				
			  	helper.res.data.predictions = helper.array_values(predictions_list)
			  	helper.res.response_code = 200
			  	res.json(helper.res)

		  })

		    //done(null, 'Value from step 2'); // <- set value to passed to step 3
		  }
		],
		function (err) {
		  if (err) {
		    throw new Error(err);
		  } else {
		  }
		})
}

exports.makePrediction = function(req,res)
{
	let min_coins = parseInt(process.env.MIN_BET_COINS)-1;
	  req.checkBody('prediction_master_id', 'Please provide a valid prediction.').notEmpty();
	  req.checkBody('prediction_option_id', 'Please provide a valid option.').notEmpty();
	  req.checkBody('bet_coins', 'Please provide a valid bet coins.').notEmpty();
	  req.checkBody('bet_coins', 'Minimum coins required to predict is '+process.env.MIN_BET_COINS+'.').isInt({ gt: min_coins });

	  // check for errors!
	  helper.res.error = {};
	  helper.res.data = {};
	  helper.res.message ='';
	  helper.res.global_error ='';
	  var errors = req.validationErrors()
	  if (errors) {
	  	helper.res.error = helper.filterError(errors)
	  	helper.res.response_code = 500
	  	helper.res.global_error =errors[0].msg
	    res.status(500).send(helper.res)
	    return;
	  }

	  if(parseInt(req.body.user_data.point_balance) < parseInt(req.body.bet_coins) )
	  {
		  	helper.res.error = {};
		  	helper.res.response_code = 500
		  	helper.res.global_error ="Insufficient Coins."
		    res.status(500).send(helper.res)
		    return;
	  }

	  var prediction_data ={};
	  var currect_user_id = req.body.user_data.user_id
	  req.body.currect_user_id = currect_user_id;
	  async.waterfall([
		  function firstStep(done) {
		    
		    predictionModel.get_user_predicted(req,function(pm_err,pm_res){

		  	if(pm_err)
		  	{
	 			res.status(500).send(pm_err);
		   		return;
		  	}

		  	if(pm_res.length > 0)
		  	{
	  			helper.res.response_code = 500
			  	helper.res.global_error ='You already predicted for this prediction.'
			    res.status(500).send(helper.res)
			    return;
		  	}
		  	else{

		  	 	done(null, pm_res); // <- set value to passed to step 2
		  	}
		  
		  })

			},
			(pm_res,done)=>{


				predictionModel.get_prediction_details(req,function(pm_err,pm_res){

					if(pm_err)
					{
					   res.status(500).send(pm_err);
						 return;
					}
	  
					if(pm_res.length > 0)
					{
						helper.res.response_code = 500
						helper.res.global_error ='Sorry, this prediction is closed. Predict other questions and win lots of coins.'
					  res.status(500).send(helper.res)
					  return;
					}
					else{
	  
						 done(null, pm_res); // <- set value to passed to step 2
					}
				
				})

				
			},
		  function secondStep(pm_res, done) {
		   //make entry for prediction
			predictionModel.make_user_prediction(req,function(mp_err,mp_res){
				if(mp_err)
			  	{
		 			res.status(500).send(mp_err);
			   		return;
			  	}
			
					done(null,mp_res);
			  
			})
		    //done(null, 'Value from step 2'); // <- set value to passed to step 3
			},
			//withdraw for prediction
			(pm_res,done)=>{
				var param = {
					user_id:req.body.currect_user_id,
					amount: req.body.bet_coins,
					source:40,//make prediction
					source_id:pm_res.insertId,
					plateform:1,//fantasy
					cash_type:3
				}

				helper.httpRequest('user/finance/withdraw_coins',param,req,res,function(err,withdraw_result){
					if(withdraw_result.response_code ==500)
					{
							helper.res=withdraw_result
							res.json(helper.res)
							return ;
					}
					else{
						//proceed for make prediction
						done(null,pm_res)
					}
				});
			},
			(res_temp,done)=>{//get prediction detail for siterake
				predictionModel.get_prediction_master_details(req,function(pm_err,pm_res){
				 
					prediction_data = pm_res[0];
				 done(null,pm_res);
				});
			},
			//update total total_pool and prize_pool
			(pm_res,done)=>{
				//calculate prize pool with sire rake
				var user_amount_except_site_rake = ((100-pm_res[0].site_rake)*req.body.bet_coins)/100;
				
				predictionModel.update_prediction_master(req,user_amount_except_site_rake,function(pm_err,pm_res){
					helper.res.response_code = 200
			  	//delete fw_prediction_list from redis
			  	if(CONSTANTS.ENABLE_REDIS)
			  	{
			  		client.del(CONSTANTS.CACHE_PREFIX+'prediction_list');
				  }

				//update prediction data
				updatePredictionViaNode(req,prediction_data);
				  

			  	helper.res.message ='You have made your prediction successfully.'
			  	res.json(helper.res)
			  	return;
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

	function updatePredictionViaNode(req,prediction_data)
	{
		predictionModel.get_predictions(req,[req.body.prediction_master_id],function(err,r){
			if(err)
			{
				res.status(500).send(err);
				 return
			}

			prediction_data.option = [];
		  r.forEach(function(element){
			  
				prediction_data.option.push(element)
				var todayDate =helper.getUtcTime();
				var deadlineDate =  helper.getUtcTime(prediction_data.deadline_date);
				prediction_data.today =todayDate*1000;
				prediction_data.deadline_time =deadlineDate*1000;	  
		  });

		  const options = {
			method: 'POST',
			url: process.env.NODE_BASE_URL+'updatePredictionAlert',
			headers: {
				"Content-Type":"application/json",
				"Accept":"application/json",
				 "User-Agent":"Mozilla/5.0 (Windows NT 6.3; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0"
			},
			body: {
				"prediction":JSON.stringify(prediction_data),
				"season_game_uid":prediction_data.season_game_uid
			},
			json: true  // JSON stringifies the body automatically
		  }
		  
		  function callback(error, response, body) {
			if (!error && response.statusCode == 200) {
			}
		  }

		  request(options, callback);
		
	})
	}


exports.get_my_prediction_fixtures = function(req,res){

		req.checkBody('status', 'Please provide a valid status.').notEmpty()
	// req.checkBody('status', 'Please provide a valid status.').notEmpty();
	    req.checkBody('status', 'Please provide a valid status.').isIn([1,2])//1 => live , 2=> completed
	  // check for errors!
	  helper.res.error = {};
	  helper.res.data = {};
	  helper.res.message ='';
	  helper.res.global_error ='';
	  var errors = req.validationErrors()
	  if (errors) {
	  	helper.res.error = helper.filterError(errors)
	  	helper.res.response_code = 500
	  	helper.res.global_error =errors[0].msg
	    res.status(500).send(helper.res)
	    return;
	  }

	  req.body.currect_user_id = req.body.user_data.user_id
	  helper.res.data  ={};
	//get my prediction fixtures ids first
	  async.waterfall([
	  	function cacehStep(done){

	  		if(CONSTANTS.ENABLE_REDIS)
	  		{
	  			client.get(CONSTANTS.CACHE_PREFIX+'my_prediction_fixtures_'+req.body.currect_user_id, function (err, data) {
			        if (err) throw err;

			        if (data != null) {
			        	helper.res.data.match_list = JSON.parse(data) 
					  	helper.res.response_code = 200
					  	res.json(helper.res)
					  	return;
			            //res.send(respond(org, data));
			        } else {
			            done(null,[]);
			        }
			    })
	  		}
	  		else{
	  			 done(null,[]);
	  		}
	  		 

	  	},
		  function firstStep(caches_res,done) {
		    
		  	if(!req.body.limit)
		  	{
		  		req.body.limit=10	
		  	}

		  	if(!req.body.offset)
		  	{
		  		req.body.offset = 0
		  	}

		  	predictionModel.get_my_prediction_fixtures(req,function(s1_err,season_game_uids){
				if(s1_err)
			  	{
		 			res.status(500).send(s1_err);
			   		return;
			  	}

			  	if(!season_game_uids.length)
			  	{
			  		//no prediction joind yet
			  		helper.res.message ='You have not joined any prediction yet.'
			  		helper.res.data = [];
			  		helper.res.response_code =200;
				  	res.json(helper.res)
				  	return;
			  	}

			  	let season_uids_list = helper.array_column(season_game_uids,'season_game_uid')

			  	
			  	done(null,season_uids_list)
			  
			})
		  },
		   function get_total_rows(season_uids_list,done){
		  	var pr_data ={}
		  	 pr_data.season_uids_list = season_uids_list
		  	 helper.get_total_rows(req,function(err,total){
		  		pr_data.total = total
		  		done(null,pr_data)
		  	})

		  },
		  function secondStep(result, done) {

		   //get season by season game uids
			helper.httpRequest('sports/season/get_season_by_season_uid',{season_game_uid:result.season_uids_list,status:req.body.status},req,res,function(err,season_result){
				


			if(season_result.length && CONSTANTS.ENABLE_REDIS)
			{
				client.setex(CONSTANTS.CACHE_PREFIX+'my_prediction_fixtures_'+req.body.currect_user_id, CONSTANTS.REDIS_EXPIRE, JSON.stringify(season_result));
			}	
				
			
			helper.res.message =''
	  		helper.res.data.match_list =season_result;
	  		helper.res.data.total =result.total;
	  		helper.res.data.offset = req.body.offset+helper.res.data.match_list.length
	  		helper.res.data.is_load_more = true;

	  		if(req.body.limit > helper.res.data.match_list.length)
	  		{
	  			helper.res.data.is_load_more = false;
	  		}
	  		helper.res.response_code =200;
		  	res.json(helper.res)
		  	return;
				
			});

		    //done(null, 'Value from step 2'); // <- set value to passed to step 3
		  }
		],
		function (err) {
		  if (err) {
		    throw new Error(err);
		  } else {
		  }
		})

}

exports.get_my_contest_fixtures_predictions = function(req,res){

	req.checkBody('season_game_uid', 'Please provide a valid matchId.').notEmpty();
	req.checkBody('status', 'Please provide a valid status.').notEmpty();
	req.checkBody('status', 'Please provide a valid status.').isIn([0,1,2,3])//0 => open,live , 2=> completed, 3 => for open and live
	  // check for errors!
	  helper.res.error = {};
	  helper.res.data = {};
	  helper.res.message ='';
	  helper.res.global_error ='';
	  var errors = req.validationErrors()
	  if (errors) {
	  	helper.res.error = helper.filterError(errors)
	  	helper.res.response_code = 500
	  	helper.res.global_error =errors[0].msg
	    res.status(500).send(helper.res)
	    return;
	  }


	  req.body.currect_user_id = req.body.user_data.user_id;
	  var estimated_wining_arr = {};
	//get my prediction fixtures ids first
	  async.waterfall([
	  	function cacehStep(done){

	  		if(CONSTANTS.ENABLE_REDIS)
	  		{
	  			client.get(CONSTANTS.CACHE_PREFIX+'my_contest_fixtures_predictions_'+req.body.currect_user_id, function (err, data) {
			        if (err) throw err;

			        if (data != null) {
			        	helper.res.data.match_list = JSON.parse(data) 
					  	helper.res.response_code = 200
					  	res.json(helper.res)
					  	return;
			            //res.send(respond(org, data));
			        } else {
			            done(null,[]);
			        }
			    })
	  		}
	  		else{
	  			 done(null,{});
	  		}
	  		 

		  },
		  function getEstimatedWinning(result,done){
			predictionModel.get_user_estimated_winning(req,(ew_err,ew_res)=>{
	
				var prediction_ew_map = [];
				_.map(ew_res,(item)=>{
					prediction_ew_map[item.prediction_master_id] = item.estimated_winning;
				})
				estimated_wining_arr.prediction_ew_map = prediction_ew_map;
				done(null,estimated_wining_arr);
				//result.prediction_ew_map = helper.array_column('')
		})
	},
		  function firstStep(arr,done) {
		    
			  	if(!req.body.limit)
			  	{
			  		req.body.limit=10	
			  	}

			  	if(!req.body.offset)
			  	{
			  		req.body.offset = 0
			  	}
			    predictionModel.get_user_prediction_masters(req,function(pm_err,pm_res){

			  	if(pm_err)
			  	{
		 			res.status(500).send(pm_err);
			   		return;
			  	}
			  	 done(null, pm_res); // <- set value to passed to step 2
			  })

		  },	
		   function get_total_rows(pm_res,done){
		  	var pr_data ={}
		  	 pr_data.result = pm_res
		  	 helper.get_total_rows1(req,function(err,total){
		  		pr_data.total = total
		  		done(null,pr_data)
		  	})

		  },	  
		  function secondStep(pr_data, done) {

		  	var pm_res = pr_data.result
		    let pm_ids = pm_res.map(function(e){return e.prediction_master_id}).join(',')
		    predictionModel.get_user_predictions(req,pm_ids,function(err,r){
			  	if(err)
			  	{
			  		res.status(500).send(err);
			   		return
			  	}

			  	let predictions_list = helper.array_column_key(pm_res,'prediction_master_id')
				r.forEach(function(element){
					
					if(predictions_list[element.prediction_master_id])
					{
						if(!predictions_list[element.prediction_master_id].option)
						{
							predictions_list[element.prediction_master_id].option = []
						}
						predictions_list[element.prediction_master_id].option.push(element)

						if(estimated_wining_arr.prediction_ew_map[element.prediction_master_id])
						{
							predictions_list[element.prediction_master_id].estimated_winning =estimated_wining_arr.prediction_ew_map[element.prediction_master_id]; 
						}
						else{
							predictions_list[element.prediction_master_id].estimated_winning= 0;
						}
					}
				});
				
			  	helper.res.data.predictions = helper.array_values(predictions_list)
			  	helper.res.data.total = pr_data.total
			  	helper.res.data.offset = req.body.offset+helper.res.data.predictions.length


			  	if(CONSTANTS.ENABLE_REDIS)
			  	{
			  		client.setex(CONSTANTS.CACHE_PREFIX+'prediction_list_'+req.body.currect_user_id, CONSTANTS.REDIS_EXPIRE, JSON.stringify(helper.res.data.predictions));
			  	}
			  	helper.res.response_code = 200
			  	res.json(helper.res)

		  })

		    //done(null, 'Value from step 2'); // <- set value to passed to step 3
		  }
		],
		function (err) {
		  if (err) {
		    throw new Error(err);
		  } else {
		  }
		})

}

exports.get_my_prediction_season = function(req,res){
	
	req.checkBody('match_status', 'Please provide a valid match status.').notEmpty()
	req.checkBody('sports_id', 'Please provide a valid Sports Id.').notEmpty();
	req.checkBody('match_status', 'Please provide a valid match status.').isIn([0,1,2])//0=>upcoming 1 => live , 2=> completed
	// check for errors!
	helper.res.error = {};
	helper.res.data = {};
	helper.res.message ='';
	helper.res.global_error ='';
	var errors = req.validationErrors()
	if (errors) {
		helper.res.error = helper.filterError(errors)
		helper.res.response_code = 500
		helper.res.global_error =errors[0].msg
		res.status(500).send(helper.res)
		return;
	}

	req.body.currect_user_id = req.body.user_data.user_id
	helper.res.data  ={};
//get my prediction fixtures ids first
	async.waterfall([
		function firstStep(done) {
			
			if(!req.body.limit)
			{
				req.body.limit=5	
			}

			if(!req.body.offset)
			{
				req.body.offset = 0
			}

			predictionModel.get_my_prediction_fixtures(req,function(s1_err,season_game_uids){
				if(s1_err)
				{
				 res.status(500).send(s1_err);
					 return;
				}


				if(!season_game_uids.length)
				{
					//no prediction joind yet
					helper.res.message ='You have not joined any prediction yet.'
					helper.res.data = [];
					helper.res.response_code =200;
					res.json(helper.res)
					return;
				}

				let season_uids_list = helper.array_column(season_game_uids,'season_game_uid')

				
				done(null,season_uids_list)
			
			})
		},
	 	function get_total_rows(season_uids_list,done){
			var pr_data ={}
			 pr_data.season_uids_list = season_uids_list
			 helper.get_total_rows1(req,function(err,total){
				pr_data.total = total;
				done(null,pr_data)
			})
		},
		//get estimated winning
		(result,done)=>{

			predictionModel.get_user_estimated_winning(req,(ew_err,ew_res)=>{

					var prediction_ew_map = [];
					_.map(ew_res,(item)=>{
						prediction_ew_map[item.prediction_master_id] = item.estimated_winning;
					})
					result.prediction_ew_map = prediction_ew_map;
					done(null,result);
			})
		},
		function secondStep(result, done) {
			if(!req.body.sports_id)
			{
				req.body.sports_id = 10;
			}
			
			//get season by season game uids
			helper.httpRequest('prediction/prediction/get_season_by_season_uid',{season_game_uid:result.season_uids_list.join(',')
				,status:req.body.match_status,
				sports_id:req.body.sports_id
			},req,res,function(err,season_result){
                //console.log("season_result",season_result);
				result.match_list = season_result;
			
				helper.res.message =''
				helper.res.data.match_list =result.match_list;
				helper.res.data.total =result.total;
				helper.res.data.offset = req.body.offset+helper.res.data.match_list.length

				helper.res.data.is_load_more = true;
		  		if(req.body.limit > helper.res.data.match_list.length)
		  		{
		  			helper.res.data.is_load_more = false;
		  		}
				helper.res.response_code =200;
				res.json(helper.res)
				return;
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
exports.get_my_predictions = function(req,res){

	req.checkBody('status', 'Please provide a valid status.').notEmpty()
	req.checkBody('sports_id', 'Please provide a valid Sports Id.').notEmpty();
	req.checkBody('season_game_uid', 'Please provide a valid season game uid.').notEmpty();
	req.checkBody('status', 'Please provide a valid status.').isIn([0,1,2])//0=>open 1 => close , 2=> prize distributed
	// check for errors!
	helper.res.error = {};
	helper.res.data = {};
	helper.res.message ='';
	helper.res.global_error ='';
	var errors = req.validationErrors()
	if (errors) {
		helper.res.error = helper.filterError(errors)
		helper.res.response_code = 500
		helper.res.global_error =errors[0].msg
		res.status(500).send(helper.res)
		return;
	}

	req.body.currect_user_id = req.body.user_data.user_id
	helper.res.data  ={};
	var result = {};
	result.season_game_uid = req.body.season_game_uid
	async.waterfall([

		function firstStep(done){
				predictionModel.get_user_estimated_winning(req,(ew_err,ew_res)=>{
		
					var prediction_ew_map = [];
					_.map(ew_res,(item)=>{
						prediction_ew_map[item.prediction_master_id] = item.estimated_winning;
					})
					result.prediction_ew_map = prediction_ew_map;
					done(null,result);
					//result.prediction_ew_map = helper.array_column('')
			})
		},
		function secondStep(result,done) {
					
					req.body.season_game_uid = result.season_game_uid
						predictionModel.get_user_prediction_masters(req,function(pm_err,pm_res){
	
						if(pm_err)
						{
						 res.status(500).send(pm_err);
							 return;
						}
	
						 done(null, pm_res); // <- set value to passed to step 2
					})
	
				},
		function thirdStep(pm_res, done) {

			let pm_ids = pm_res.map(function(e){return e.prediction_master_id}).join(',')
			predictionModel.get_user_predictions(req,pm_ids,function(err,r){
				if(err)
				{
					res.status(500).send(err);
					 return
				}
				
				let predictions_list = helper.array_column_key(pm_res,'prediction_master_id')
				
				r.forEach(function(element){
				
				if(predictions_list[element.prediction_master_id])
				{
					if(!predictions_list[element.prediction_master_id].option)
					{
						predictions_list[element.prediction_master_id].option = []
					}
					predictions_list[element.prediction_master_id].option.push(element)

					if(result.prediction_ew_map[element.prediction_master_id])
					{
						predictions_list[element.prediction_master_id].estimated_winning =result.prediction_ew_map[element.prediction_master_id]; 
					}
					else{
						predictions_list[element.prediction_master_id].estimated_winning= 0;
					}
				}
			});
			
			  	predictions_list.prediction_list = helper.array_values(predictions_list);
				  
				let prediction_data = predictions_list.prediction_list;
				//cb(prediction_data)

				helper.res.message =''
				helper.res.data.prediction_list =prediction_data;
				helper.res.data.offset = 0;//req.body.offset+helper.res.data.prediction_list.length

				helper.res.data.is_load_more = true;
					
				
				helper.res.response_code =200;
				res.json(helper.res)
				return;

		})

			//done(null, 'Value from step 2'); // <- set value to passed to step 3
		},

		
	],
	function (err) {
		if (err) {
			throw new Error(err);
		} else {
		}
	})
}

	function prepare_prediction_list(req,res,result,cb){
			
			async.waterfall([
				function firstStep(done) {
					
					req.body.season_game_uid = result.season_game_uid
						predictionModel.get_user_prediction_masters(req,function(pm_err,pm_res){
	
						if(pm_err)
						{
						 res.status(500).send(pm_err);
							 return;
						}
	
						 done(null, pm_res); // <- set value to passed to step 2
					})
	
				},	
				 function get_total_rows(pm_res,done){
					var pr_data ={}
					 pr_data.result = pm_res
					 helper.get_total_rows1(req,function(err,total){
						pr_data.total = total
						done(null,pr_data)
					})
	
				},	  
				function secondStep(pr_data, done) {
	
					var pm_res = pr_data.result
					let pm_ids = pm_res.map(function(e){return e.prediction_master_id}).join(',')
					predictionModel.get_user_predictions(req,pm_ids,function(err,r){
						if(err)
						{
							res.status(500).send(err);
							 return
						}
						
						let predictions_list = helper.array_column_key(pm_res,'prediction_master_id')
						r.forEach(function(element){
						
						if(predictions_list[element.prediction_master_id])
						{
							if(!predictions_list[element.prediction_master_id].option)
							{
								predictions_list[element.prediction_master_id].option = []
							}
							predictions_list[element.prediction_master_id].option.push(element)

							if(result.prediction_ew_map[element.prediction_master_id])
							{
								predictions_list[element.prediction_master_id].estimated_winning =result.prediction_ew_map[element.prediction_master_id]; 
							}
							else{
								predictions_list[element.prediction_master_id].estimated_winning= 0;
							}
						}
					});
					
						// helper.res.data.predictions = helper.array_values(predictions_list)
						// helper.res.data.total = pr_data.total
						// helper.res.data.offset = req.body.offset+helper.res.data.predictions.length
	
					  predictions_list.prediction_list = helper.array_values(predictions_list);
						let prediction_data = predictions_list.prediction_list;
						cb(prediction_data)
	
				})
	
					//done(null, 'Value from step 2'); // <- set value to passed to step 3
				}
			],
			function (err) {
				if (err) {
					throw new Error(err);
				} else {
				}
			})
			// tell async that that particular element of the iterator is done
	}	

exports.getPredictionPoolFixture = function(req,res){

	req.checkBody('season_game_uid', 'Please provide a valid matchId.').notEmpty();
	
	  helper.res.error = {};
	  helper.res.data = {};
	  helper.res.message ='';
	  helper.res.global_error ='';
	  var errors = req.validationErrors()
	  if (errors) {
	  	helper.res.error = helper.filterError(errors)
	  	helper.res.response_code = 500
	  	helper.res.global_error =errors[0].msg
	    res.status(500).send(helper.res)
	    return;
		}
		
		req.body.currect_user_id = req.body.user_data.user_id;
		
		predictionModel.get_prediction_pool(req,function(pm_err,pm_res){

			if(pm_err)
			{
			 res.status(500).send(pm_err);
				 return;
			}
			helper.res.data = {};
			helper.res.data.prediction_data = pm_res;
			helper.res.response_code = 200
			res.json(helper.res)
			 //done(null, pm_res); // <- set value to passed to step 2
		})

}

exports.getPredictionDetail = function(req,res)
{
	//   req.checkBody('prediction_master_id', 'Please provide a valid prediction id.').notEmpty();
	//   req.checkBody('season_game_uid', 'Please provide a valid match id.').notEmpty();

	  // check for errors!
	  helper.res.error = {};
	  helper.res.data = {};
	  helper.res.global_error  = '';
	//   var errors = req.validationErrors()
	//   if (errors) {
	//   	helper.res.error = helper.filterError(errors)
	//   	helper.res.response_code = 500
	//   	helper.res.global_error =errors[0].msg
	//     res.status(500).send(helper.res)
	//     return;
	//   }

	  req.body.currect_user_id = 0;
	  if(req.body.user_data)
	  {
		  req.body.currect_user_id = req.body.user_data.user_id
	  }
	  async.waterfall([
	  	(done)=>{

			predictionModel.get_season_data(req,function(pm_err,pm_res){

				if(pm_err)
				{
				   res.status(500).send(pm_err);
					 return;
				}

				if(!pm_res.length)
				{
					helper.res.message ='No prediction available.'
					helper.res.data = [];
					helper.res.response_code =200;
					res.json(helper.res)
					return;
				}
				helper.res.data.match_data = pm_res[0];

				helper.res.data.match_data.game_starts_in = moment(helper.res.data.match_data.season_scheduled_date).valueOf();
				helper.res.data.match_data.season_scheduled_date = moment(helper.res.data.match_data.season_scheduled_date).format('YYYY-MM-DD HH:mm:ss');
				 done(null, pm_res); 
			})

		  },
		  async function firstStep(season_data) {
		    
			   let pm_res = await predictionModel.get_prediction_masters(req);

			   if(!pm_res.length)
			   {
				   helper.res.message ='No prediction available.'
				   helper.res.data = [];
				   helper.res.response_code =200;
				   res.json(helper.res)
				   return;
			   }

			   return [pm_res];
				 // <- set value to passed to step 2
		  
		  },
		  async function secondStep([pm_res]) {

		  	//var pm_res = pr_data.result
		    let pm_ids = pm_res.map(function(e){return e.prediction_master_id}).join(',')


		    let r=  await predictionModel.get_predictions(req,pm_ids);

		  	let predictions_list = helper.array_column_key(pm_res,'prediction_master_id')
			r.forEach(function(element){
					
					if(predictions_list[element.prediction_master_id])
					{
						var deadline_date = moment(predictions_list[element.prediction_master_id].deadline_date); // some mock date
						predictions_list[element.prediction_master_id].deadline_date = deadline_date.format('YYYY-MM-DD HH:mm:ss');
						predictions_list[element.prediction_master_id].deadline_time = deadline_date.valueOf()*1000; 
						if(!predictions_list[element.prediction_master_id].option)
						{
							predictions_list[element.prediction_master_id].option = []
						}
						predictions_list[element.prediction_master_id].option.push(element)
					}
			});
				
			helper.res.data.prediction = helper.array_values(predictions_list)
			helper.res.response_code = 200
			res.json(helper.res)

		    //done(null, 'Value from step 2'); // <- set value to passed to step 3
		  }
		],
		function (err) {
		  if (err) {
		    throw new Error(err);
		  } else {
		  }
		})
}

exports.checkPredictionUserJoined = function(req,res)
{
	  req.checkBody('prediction_master_id', 'Please provide a prediction master id.').notEmpty();

	  // check for errors!
	  helper.res.error = {};
	  helper.res.data = {};
	  var errors = req.validationErrors()
	  if (errors) {
	  	helper.res.error = helper.filterError(errors)
	  	helper.res.response_code = 500
	  	helper.res.global_error =errors[0].msg
	    res.status(500).send(helper.res)
	    return;
	  }

	  req.body.currect_user_id = req.body.user_data.user_id
	  async.waterfall([
	  	
		  function firstStep(done) {
		    
			    predictionModel.check_prediction_joined(req,function(pm_err,pm_res){

			  	if(pm_err)
			  	{
		 			res.status(500).send(pm_err);
			   		return;
			  	}

				helper.res.message =''
				helper.res.data = {};
				helper.res.response_code =200;
			  	if(!pm_res.length)
			  	{
			  		helper.res.data.is_joined = 0;
			  		res.json(helper.res)
				  	return;
				  }
				  else{
					helper.res.data.is_joined = 1;
				  }
				 // helper.res.data.
			  	res.json(helper.res)

			  	 done(null, pm_res); // <- set value to passed to step 2
			  })

		  }
		],
		function (err) {
		  if (err) {
		    throw new Error(err);
		  } else {
		  }
		})
}

exports.getAllPredictions = function(req,res)
{
	  // check for errors!
	  helper.res.error = {};
	  helper.res.data = {};
	  helper.res.message='';
	  
	  req.body.currect_user_id = 0;
	  if(req.body.user_data)
	  {
		  req.body.currect_user_id = req.body.user_data.user_id
	  }
	  async.waterfall([
		  function firstStep(done) {
		    
			  	if(!req.body.limit)
			  	{
			  		req.body.limit=10000	
			  	}

			  	if(!req.body.offset)
			  	{
			  		req.body.offset = 0
				  }
				 
			    predictionModel.get_all_prediction_masters(req,function(pm_err,pm_res){

			  	if(pm_err)
			  	{
		 			res.status(500).send(pm_err);
			   		return;
			  	}
			  	if(!pm_res.length)
			  	{
			  		helper.res.message ='No prediction available.'
			  		helper.res.data = [];
			  		helper.res.response_code =200;
				  	res.json(helper.res)
				  	return;
			  	}

			  	 done(null, pm_res); // <- set value to passed to step 2
			  })

		  },
		  function get_total_rows(pm_res,done){
			  var pr_data ={}
			  pr_data.result = pm_res
			  helper.get_total_rows1(req,function(err,total){
				  pr_data.total = total
		  		done(null,pr_data)
		  	})

		  },
		  function secondStep(pr_data, done) {
		  	var pm_res = pr_data.result
		    let pm_ids = pm_res.map(function(e){return e.prediction_master_id}).join(',')
		    predictionModel.get_predictions(req,pm_ids,function(err,r){
			  	if(err)
			  	{
			  		res.status(500).send(err);
			   		return
			  	}



				 // let predictions_list = helper.array_column_key(pm_res,'prediction_master_id')
				  let predictions_list = helper.array_values(pm_res);
				  let pm_ids = helper.array_column(pm_res,'prediction_master_id')

				  let prediction_options = {};
				  r.forEach(function(element,index){
					  
					if(!prediction_options[element.prediction_master_id])
					{
						prediction_options[element.prediction_master_id] = [];
						prediction_options[element.prediction_master_id].push(element); 
					}
					else
					{
						prediction_options[element.prediction_master_id].push(element); 
					}
					
				});

				predictions_list.forEach((prediction,index)=>{
					if(prediction_options[prediction.prediction_master_id])
					{
						prediction.option = prediction_options[prediction.prediction_master_id];

							if(!prediction.today)
						{
							var todayDate =helper.getUtcTime();
							var deadlineDate =  helper.getUtcTime(prediction.deadline_date);
							prediction.today =todayDate*1000;
							prediction.deadline_time =deadlineDate*1000;
						}

					}

				})

				//var sorted_predictions = predictions_list.reverse()
			  	helper.res.data.predictions = helper.array_values(predictions_list)
			  	helper.res.data.predictions =helper.res.data.predictions;
			  	helper.res.data.total = pr_data.total
			  	helper.res.data.offset = req.body.offset+helper.res.data.predictions.length


			  	if(CONSTANTS.ENABLE_REDIS)
			  	{
			  		client.setex(CONSTANTS.CACHE_PREFIX+'prediction_list_'+req.body.currect_user_id, CONSTANTS.REDIS_EXPIRE, JSON.stringify(helper.res.data.predictions));
			  	}
			  	helper.res.response_code = 200
			  	res.json(helper.res)

		  })

		    //done(null, 'Value from step 2'); // <- set value to passed to step 3
		  }
		],
		function (err) {
		  if (err) {
		    throw new Error(err);
		  } else {
		  }
		})
}


