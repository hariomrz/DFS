var express = require('express')
var app = express()
 var util = require('util');
var router = express.Router()
var expressValidator = require('express-validator');
var bodyParser = require('body-parser');

var config = require('../config')[process.env.NODE_ENV]
var helper = require('../helper/default_helper')
var newLineupCtrl = require('../controllers/newLineupCtrl')
var predictionCtrl = require('../controllers/predictionCtrl')
var authCtrl = require('../controllers/authCtrl')
const gameCenterCtrl = require('../controllers/gameCenterCtrl')
app.use(bodyParser.urlencoded({
	extended: true
}));
app.use(bodyParser.json());
app.use(expressValidator());

var by_pass_routes = [
	'/prediction/get_predictions'
]; 

function authenticateSession(req, res, next) {

	helper.authenticateSession(req,res,function(err,res){
		 req.body.user_data = res.data;
		 next()
	})
}

function authenticateSessionByPass(req, res, next) {

	if(req.headers.sessionkey)
	{
		helper.authenticateSession(req,res,function(err,res){
			req.body.user_data = res.data;
			next()
	   })
	}
	else{
		next()
	}
}

function auth(req, res, next)
{
	if(req.headers.sessionkey)
	{
		req.body.Sessionkey = req.headers.sessionkey; 
		authCtrl.session_key_validate(req,res,function(err,res){

			req.body.user_data = res;
			next()
	   })
	}
}
function authBypass(req, res, next)
{
	if(req.headers.sessionkey)
	{
		req.body.Sessionkey = req.headers.sessionkey; 
		authCtrl.session_key_validate(req,res,function(err,res){
			req.body.user_data = res;
			next()
	   })
	}
	else{
		next()
	}
}

var request = require('request');
var bucket_url = process.env.BUCKET+'.s3.'+process.env.BUCKET_REGION+'.amazonaws.com';
if(process.env.BUCKET_TYPE =='DO')
{
	bucket_url=process.env.BUCKET+'.nyc3.digitaloceanspaces.com';
}
else if(process.env.BUCKET_TYPE =='CJ')
{
	bucket_url=process.env.BUCKET_REGION+'.cloudjiffy.net/'+process.env.BUCKET;
}else{
	bucket_url = process.env.BUCKET+'.s3.'+process.env.BUCKET_REGION+'.amazonaws.com';
}
async function checkPredictionAllowed(req, res, next)
{
	var bucket_data_prefix = await helper.get_app_config_value('bucket_data_prefix');
	var a_prediction = await helper.get_app_config_value('allow_prediction_system');
	//console.log("enter:",bucket_data_prefix);
	request('https://'+bucket_url+'/appstatic/'+bucket_data_prefix+'app_master_data.json', function (error, response, body) {
		
		//console.log("response:",response.statusCode,body);
		if(response.statusCode==404)
		{
			if(a_prediction == '1')
			{
				next()
				return;
			}
			else{
				helper.res.response_code = 500
				helper.res.global_error ="This Module is not Allowed."
				res.status(500).send(helper.res)
				return;
			}
		}
	if (!error && response.statusCode == 200) {
		   var importedJSON = JSON.parse(body);
		  // console.log("importedJSON:",importedJSON.a_prediction);
			if(importedJSON.a_prediction=='1')
			{
				next()
			}
			else{
			
				helper.res.response_code = 500
				helper.res.global_error ="This Module is not Allowed."
				res.status(500).send(helper.res)
				return;
			}
		}
	  })
	  
}

async function checkOpenPredictorAllowed(req, res, next)
{
	var bucket_data_prefix = await helper.get_app_config_value('bucket_data_prefix');
	var a_open_predictor = await helper.get_app_config_value('allow_open_predictor');
	//console.log("enter:",bucket_data_prefix,a_open_predictor);
	request('https://'+bucket_url+'/appstatic/'+bucket_data_prefix+'app_master_data.json', function (error, response, body) {

		if(response.statusCode==404)
		{
			if(a_open_predictor == '1')
			{
				next();
				return;
			}
			else 
			{
				helper.res.response_code = 500
				helper.res.global_error ="This Module is not Allowed."
				res.status(500).send(helper.res)
				return;
			}
		}
		//console.log("response:",response.statusCode,body);
	if (!error && response.statusCode == 200) {
		   var importedJSON = JSON.parse(body);
		   //console.log("importedJSON:",importedJSON.a_prediction);
			if(importedJSON.a_open_predictor=='1')
			{
				next()
			}
			else{
			
				helper.res.response_code = 500
				helper.res.global_error ="This Module is not Allowed."
				res.status(500).send(helper.res)
				return;
			}
		}
	  })
	  
}

async function checkFixedOpenPredictorAllowed(req, res, next)
{
	var bucket_data_prefix = await helper.get_app_config_value('bucket_data_prefix');
	var a_fixed_open_predictor = await helper.get_app_config_value('allow_fixed_open_predictor');
	//console.log("enter:",bucket_data_prefix);
	request('https://'+bucket_url+'/appstatic/'+bucket_data_prefix+'app_master_data.json', function (error, response, body) {

	
		if(response.statusCode==404)
		{
			if(a_fixed_open_predictor == '1')
			{
				
				next();
				return;
			}
			else{
				helper.res.response_code = 500
				helper.res.global_error ="This Module is not Allowed."
				res.status(500).send(helper.res)
				return;
			}
		}
		//console.log("response:",response.statusCode,body);
	if (!error && response.statusCode == 200) {
		   var importedJSON = JSON.parse(body);
		  // console.log("importedJSON:",importedJSON.a_fixed_open_predictor);
			if(importedJSON.a_fixed_open_predictor=='1')
			{
				next()
			}
			else{
			
				helper.res.response_code = 500
				helper.res.global_error ="This Module is not Allowed."
				res.status(500).send(helper.res)
				return;
			}
		}
	  })
	  
}

//predictions
router.post('/prediction/get_predictions' ,checkPredictionAllowed,authBypass,predictionCtrl.getPredictions)
router.post('/prediction/get_one_predictions' ,checkPredictionAllowed,auth,predictionCtrl.getOnePrediction)
router.post('/prediction/get_prediction_detail' ,checkPredictionAllowed,authBypass,predictionCtrl.getPredictionDetail)
router.post('/prediction/make_prediction',checkPredictionAllowed,auth,predictionCtrl.makePrediction)
router.post('/prediction/get_my_prediction_fixture',checkPredictionAllowed ,auth,predictionCtrl.get_my_prediction_fixtures)
router.post('/prediction/get_my_contest_fixtures_predictions',checkPredictionAllowed ,auth,predictionCtrl.get_my_contest_fixtures_predictions)
router.post('/prediction/get_prediction_pool_fixture',checkPredictionAllowed ,auth,predictionCtrl.getPredictionPoolFixture)
router.post('/prediction/check_prediction_user_joined',checkPredictionAllowed ,auth,predictionCtrl.checkPredictionUserJoined)

router.post('/prediction/get_my_predictions' ,checkPredictionAllowed,auth,predictionCtrl.get_my_predictions)
router.post('/prediction/get_my_prediction_season' ,checkPredictionAllowed,auth,predictionCtrl.get_my_prediction_season)

//open predictor routes
var openPredictorCtrl = require('../controllers/openPredictorCtrl')
router.post('/open_predictor/get_predictions' ,checkOpenPredictorAllowed,authBypass,openPredictorCtrl.getAllPredictions)
router.post('/open_predictor/get_prediction_detail' ,checkOpenPredictorAllowed,authBypass,openPredictorCtrl.getPredictionDetail)
router.post('/open_predictor/make_prediction',checkOpenPredictorAllowed,auth,openPredictorCtrl.makePrediction)
router.post('/open_predictor/get_my_prediction_category' ,checkOpenPredictorAllowed,auth,openPredictorCtrl.get_my_prediction_category)
router.post('/open_predictor/get_my_contest_category_predictions',checkOpenPredictorAllowed ,auth,openPredictorCtrl.get_my_contest_category_predictions)

//fixed open predictor routes
var fixedOpenPredictorCtrl = require('../controllers/fixedOpenPredictorCtrl')
router.post('/fixed_open_predictor/get_predictions' ,checkFixedOpenPredictorAllowed,authBypass,fixedOpenPredictorCtrl.getAllPredictions)
router.post('/fixed_open_predictor/get_prediction_detail' ,checkFixedOpenPredictorAllowed,authBypass,fixedOpenPredictorCtrl.getPredictionDetail)
router.post('/fixed_open_predictor/make_prediction',checkFixedOpenPredictorAllowed,auth,fixedOpenPredictorCtrl.makePrediction)
router.post('/fixed_open_predictor/get_my_prediction_category' ,checkFixedOpenPredictorAllowed,auth,fixedOpenPredictorCtrl.get_my_prediction_category)
router.post('/fixed_open_predictor/get_my_contest_category_predictions',checkFixedOpenPredictorAllowed ,auth,fixedOpenPredictorCtrl.get_my_contest_category_predictions)
router.post('/fixed_open_predictor/check_prediction_user_joined',checkFixedOpenPredictorAllowed ,auth,fixedOpenPredictorCtrl.checkPredictionUserJoined)

//1 for 2nd inning , 0 for normal DFS
router.get('/lineup/lineup_move/:for_2nd_inning',newLineupCtrl.lineup_move)//common lib done

router.post('/fixtures/live_match_list' ,gameCenterCtrl.get_live_match_list);
//include stocks
router.use('/stocks', require('./stocks').router);
app.use('/', router)
/** 
 * We assign app object to module.exports
 * 
 * module.exports exposes the app object as a module
 * 
 * module.exports should be used to return the object 
 * when this file is required in another module like app.js
 */ 
module.exports = app;