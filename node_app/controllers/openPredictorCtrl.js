"use strict";
var express = require('express')
var app = express()
var async =  require('async');
var CONSTANTS = require('../constants')
var helper = require('../helper/default_helper')

const _ = require('lodash')
const request = require('request')
var moment =  require('moment')
/**
 * Store database credentials in a separate config.js file
 * Load the file/module and its values
 */ 
var predictionModel  = require('../models/openPredictorModel')

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

exports.getPredictionDetail = function(req,res)
{
	  req.checkBody('prediction_master_id', 'Please provide a valid prediction id.').notEmpty();
	  req.checkBody('category_id', 'Please provide a valid category id.').notEmpty();

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

	  req.body.currect_user_id = 0;
	  if(req.body.user_data)
	  {
		  req.body.currect_user_id = req.body.user_data.user_id
	  }
	  async.waterfall([
	  	(done)=>{

			predictionModel.get_category_data(req,function(pm_err,pm_res){

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
				helper.res.data.category_data = pm_res[0];

				 done(null, pm_res); 
			})

		  },
		  function firstStep(season_data,done) {
		    
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



/**
*Make predictions
*/

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
	  
					if(pm_res.length && (pm_res[0].status == 1 || pm_res[0].status == 4))
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

			//check if betcoins different in case of fixed entry type contest then overwrite betcoins
			if(pm_res[0].entry_type ==1 && pm_res[0].entry_fee !== req.body.bet_coins)
			{
				req.body.bet_coins = pm_res[0].entry_fee;
			}
		   //make entry for prediction
			predictionModel.make_user_prediction(req,function(mp_err,mp_res){
				if(mp_err)
			  	{
		 			res.status(500).send(mp_err);
			   		return;
				}
				  
				mp_res.entry_type = pm_res[0].entry_type;
				done(null,mp_res);
			  
			})
		    //done(null, 'Value from step 2'); // <- set value to passed to step 3
			},
			//withdraw for prediction
			(pm_res,done)=>{
				var param = {
					user_id:req.body.currect_user_id,
					amount: req.body.bet_coins,
					source:220,//make prediction
					source_id:pm_res.insertId,
					plateform:1,//fantasy
					cash_type:3,
					entry_type:pm_res.entry_type
				}

				if(param.amount > 0)
				{
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
				}
				else{
					done(null,{})
				}
				
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
			url: process.env.NODE_BASE_URL+'updateOpenPredictorAlert',
			headers: {
				"Content-Type":"application/json",
				"Accept":"application/json",
				 "User-Agent":"Mozilla/5.0 (Windows NT 6.3; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0"
			},
			body: {
				"prediction":JSON.stringify(prediction_data),
				"category_id":prediction_data.category_id
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

	exports.get_my_prediction_category = function(req,res){
	
		req.checkBody('prediction_status', 'Please provide a valid prediction status.').notEmpty()
		req.checkBody('prediction_status', 'Please provide a valid prediction status.').isIn([0,1,2])//0=>upcoming 1 => live , 2=> completed
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
	
				predictionModel.get_my_prediction_category(req,function(s1_err,category_ids){
				if(s1_err)
					{
					 res.status(500).send(s1_err);
						 return;
					}
	
					if(!category_ids.length)
					{
						//no prediction joind yet
						helper.res.message ='You have not joined any prediction yet.'
						helper.res.data = [];
						helper.res.response_code =200;
						res.json(helper.res)
						return;
					}
	
					helper.res.message =''
						helper.res.data.category_list =category_ids;
						helper.res.data.total =category_ids.length;
						helper.res.data.offset = req.body.offset+category_ids.length
		
						helper.res.data.is_load_more = true;
						if(req.body.limit > category_ids.length)
						{
							helper.res.data.is_load_more = false;
						}
						helper.res.response_code =200;
						res.json(helper.res)
						return;

					
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

	exports.get_my_contest_category_predictions = function(req,res){

		req.checkBody('category_id', 'Please provide a valid Category ID.').notEmpty();
		req.checkBody('status', 'Please provide a valid status.').notEmpty();
		req.checkBody('status', 'Please provide a valid status.').isIn([0,1,2])//0 => open,live , 2=> completed
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
			  function getEstimatedWinning(done){
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
					  var todayDate =helper.getUtcTime();
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

							var deadlineDate =  helper.getUtcTime(predictions_list[element.prediction_master_id].deadline_date);
							predictions_list[element.prediction_master_id].today =todayDate*1000;
							predictions_list[element.prediction_master_id].deadline_time =deadlineDate*1000;

						}
					});
					



					  helper.res.data.predictions = helper.array_values(predictions_list)
					  helper.res.data.total = pr_data.total
					  helper.res.data.offset = req.body.offset+helper.res.data.predictions.length
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