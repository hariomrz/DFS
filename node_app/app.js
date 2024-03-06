var express = require('express')
var app = express()

require('dotenv').config({ path: '../.env' })

if(process.env.HTTP_PROTOCOL =='http')
{
  var server = require('http').Server(app);
}  
else{
  	var fs = require('fs');
    var http = require('https');

  if(process.env.NODE_ENV=='testing')
  {
    var key = fs.readFileSync('/etc/apache2/ssl/key.key');
    var ca = fs.readFileSync('/etc/apache2/ssl/bundle.crt');
    var cert = fs.readFileSync('/etc/apache2/ssl/crt.crt' );
    
    var options = {
    key: key,
    cert: cert,
    ca: ca
    };
  }
  else{ //production case
   
    var key = fs.readFileSync('/etc/apache2/ssl/key.key');
    var ca = fs.readFileSync('/etc/apache2/ssl/bundle.crt');
    var cert = fs.readFileSync('/etc/apache2/ssl/crt.crt' );
    
    var options = {
    key: key,
    cert: cert,
    ca: ca
    };

  }  
  
 var server  = http.createServer(options, app);

}



var io = require('socket.io')(server);
 const EventEmitter = require('events');
 
//---------------------------------------------

var moment = require('moment-timezone');
moment().tz("UTC").format();

//---------------------------------------------

var helper = require('./helper/default_helper')
var main_config = require('./config')

var mongo_db_url = helper.mongo_url();

/**
 * This middleware provides a consistent API 
 * for MySQL connections during request/response life cycle
 */ 
//var myConnection  = require('express-myconnection')
/**
 * Store database credentials in a separate config.js file
 * Load the file/module and its values
 */ 


/**
 * 3 strategies can be used
 * single: Creates single database connection which is never closed.
 * pool: Creates pool of connections. Connection is auto release when response ends.
 * request: Creates new connection per new request. Connection is auto close when response ends.
 */ 

 

/**
 * setting up the templating view engine
 */ 
app.set('view engine', 'ejs')

var index = require('./routes/routes')


//var users = require('./routes/users')
 
 
 
/**
 * body-parser module is used to read HTTP POST data
 * it's an express middleware that reads form's input 
 * and store it as javascript object
 */ 
var bodyParser = require('body-parser')

app.use(bodyParser.urlencoded({ extended: true }))
app.use(bodyParser.json({
    type: "*/*"
})); 

app.use(function(req, res, next) {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Origin,Content-Type,Accept, Authorization, Content-Length, X-Requested-With,session_key,Sessionkey,Version,Apiversion,User-Token,Device,RequestTime,Cookie,_ga_token,X-RefID,Ult,loc_check');

    //intercepts OPTIONS method
    if ('OPTIONS' === req.method) {
      //respond with 200
      res.sendStatus(200);
      return;
    }
    else {
    //move on
      next();
    }
});

const cors = require('cors');
app.use(cors({
    origin: '*'
}));

// app.options("/*", function(req, res, next){
//   res.header('Access-Control-Allow-Origin', '*');
//   res.header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS');
//   res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization, Content-Length, X-Requested-With');
//   res.send(200);
// });




/**
 * This module shows flash messages
 * generally used to show success or error messages
 * 
 * Flash messages are stored in session
 * So, we also have to install and use 
 * cookie-parser & session modules
 */ 

app.use('/', index)

const emitter = new EventEmitter()
emitter.setMaxListeners(100)
// or 0 to turn off the limit
emitter.setMaxListeners(0)

var lineupMasterIds = {};
// create game room

require('./open_predictor')(io,app);
require('./fixed_open_predictor')(io,app);
require('./pickem')(io,app);
require('./game_center')(io,app);
require('./live_fantasy')(io,app);
require('./opinion_trade')(io,app);
require('./stock')(io,app);

io.on('connection', function(socket){
  //console.log('a user connected');

socket.on('disconnect', function(){
  //console.log('user disconnected');
});


  socket.on('JoinAddPredictionRoom', function(data) {
    var season_game_uid = data.season_game_uid;
    var room = 'notify_add_prediction_list_'+season_game_uid;
    socket.join(room);
  });

  socket.on('JoinDeletePredictionRoom', function(data) {
    var season_game_uid = data.season_game_uid;
    var room = 'notify_delete_prediction_'+season_game_uid;
    socket.join(room);
  });

  socket.on('JoinPausePlayPredictionRoom', function(data) {
    var season_game_uid = data.season_game_uid;
    var room = 'notify_pause_play_prediction_list_'+season_game_uid;
    socket.join(room);
  });


  socket.on('JoinWonPredictionRoom', function(data) {
    var user_id = data.user_id;
    var room = 'notify_won_prediction_'+user_id;
    socket.join(room);
  });  

  socket.on('JoinLossPredictionRoom', function(data) {
    var user_id = data.user_id;
    var room = 'notify_loss_prediction_'+user_id;
    socket.join(room);
  });  


  socket.on('updatePredictionChangeServer', function(data) {
    var season_game_uid = data.season_game_uid;
    var room = 'notify_update_prediction_'+season_game_uid;
    socket.to(room).emit('NotifyUpdatePrediction', {filter_one_prediction:1,
    prediction_master_id:data.prediction_master_id});
  });

  socket.on('JoinLiveScore', function(data){
		var contest_unique_id    = data.contest_id;
		socket.contest_unique_id = contest_unique_id;
		socket.join(contest_unique_id);
	});

	socket.on('JoinmatchRoom', function(data) {
		var sports_id = data.sports_id;
		var room = 'notify_'+sports_id;
		socket.join(room);
	});
	socket.on('createRoom', function(data) {

		var contest_unique_id = data.contest_unique_id;
		socket.room = contest_unique_id;
		if (data.lineup_master_id != "undefined") {
			socket.lineup_master_id = data.lineup_master_id;
			lineupMasterIds[data.lineup_master_id] = data.lineup_master_id;
			socket.broadcast.to(socket.room).emit('connectUserStatus', {lineup_master_id:lineupMasterIds});
		}

		var socketObj = socket.adapter.sids[socket.id];	

		if(socketObj[data.contest_unique_id] == true)
		{
		}
		else
		{
			socket.join(contest_unique_id);
			setTimeout(function(){
				io.to(contest_unique_id).emit('connectUserStatus', {lineup_master_id:lineupMasterIds});
			}, 3000);
		}
	});

	socket.on('disconnect', function(){
		socket.broadcast.to(socket.room).emit('disconnectUserStatus', {lineup_master_id:socket.lineup_master_id});
		delete lineupMasterIds[socket.lineup_master_id];
		socket.leave(socket.room);
	});

	/* chat for game*/	
	var chatrooms = [];
	// var users = {};
	socket.on('addUser', function(data) {
		if (chatrooms.indexOf(data.contest_unique_id) != '-1')
		{

		}
		else
		{
			if (data.user_id != "undefined")
			{
				chatrooms.push(data.contest_unique_id);
				// users[data.user_id] = socket.id;
				chatrooms[socket.id] = { user_id : data.user_id, socket : socket };
				socket.join(chatrooms[data.contest_unique_id]);
				socket.broadcast.to(chatrooms[data.contest_unique_id]).emit('changeUserStatus', {user_id:data.user_id});
			}
		}
	});

	socket.on('sendChatToAll', function(recieveChat){
		socket.broadcast.to(recieveChat.contest_unique_id).emit('updatechat',recieveChat);
	});

	socket.on('sendChatToMember', function(recieveChat){
		socket.broadcast.to(recieveChat.contest_unique_id).emit('updatechat', recieveChat);
	});

});

app.post('/newPredictionAlert', function(req, res) {
 
  if(req.body.season_game_uid)
  {
    res.sendStatus(200);
    var room = 'notify_add_prediction_list_'+req.body.season_game_uid;
    io.to(room).emit('NotifyNewPrediction', req.body);
  }
  else{
    var responsedata = "";
    req.on('data', function(data){
      responsedata += data;
    });
    req.on('end', function(){
      res.sendStatus(200);
    });
  }
	
});

app.post('/updatePredictionAlert', function(req, res) {
  if(req.body.season_game_uid)
  {
    res.sendStatus(200);
    var room = 'notify_update_prediction_'+req.body.season_game_uid;
    io.to(room).emit('NotifyUpdatePrediction', req.body);
  }
  else{
    var responsedata = "";
    req.on('data', function(data){
      
      responsedata += data;
    });
    req.on('end', function(){
      res.sendStatus(200);

    });
  }
	
});

app.post('/pausePlayPrediction', function(req, res) {
  
  if(req.body.prediction_master_id && req.body.season_game_uid)
  {
    res.sendStatus(200);
    var room = 'notify_pause_play_prediction_list_'+req.body.season_game_uid;
    
    io.to(room).emit('NotifyPausePlayPrediction', req.body);
  }
  else{
    var responsedata = "";
    req.on('data', function(data){
      responsedata += data;
    });
    req.on('end', function(){
      res.sendStatus(200);
      
    });
  }
});

app.post('/wonPrediction', function(req, res) {
  
  if(req.body.prediction_master_id && req.body.user_id)
  {
    res.sendStatus(200);
    var room = 'notify_won_prediction_'+req.body.user_id;
    io.to(room).emit('NotifyWonPrediction', req.body);
  }
  else{
    var responsedata = "";
    req.on('data', function(data){
      responsedata += data;
    });
    req.on('end', function(){
      res.sendStatus(200);
      
    });
  }
});

app.post('/lossPrediction', function(req, res) {
  
  console.log('notify_loss_prediction_DATA',req.body);
  if(req.body.prediction_master_id && req.body.user_id)
  {
    res.sendStatus(200);
    console.log('notify_loss_prediction_',req.body.user_id);
    var room = 'notify_loss_prediction_'+req.body.user_id;
    io.to(room).emit('NotifyLossPrediction', req.body);
  }
  else{
    var responsedata = "";
    req.on('data', function(data){
      responsedata += data;
    });
    req.on('end', function(){
      res.sendStatus(200);
      
    });
  }
});



app.post('/deletePrediction', function(req, res) {
 
  if(req.body.prediction_master_id)
  {
    res.sendStatus(200);
    var room = 'notify_delete_prediction_'+req.body.season_game_uid;
    
    io.to(room).emit('NotifyDeletePrediction', req.body);
  }
  else{
    var responsedata = "";
    req.on('data', function(data){
      responsedata += data;
    });
    req.on('end', function(){
      res.sendStatus(200);
    });
  }
	
});

app.post('/playerDrafted', function(req, res) {
	var responsedata = "";
	req.on('data', function(data){
		responsedata += data;
	});
	req.on('end', function(){
		res.sendStatus(200);
		var result = JSON.parse( responsedata );
		io.to(result.contest_unique_id).emit('NotifyplayerDrafted', result);
	});
});

app.post('/contestStarted', function(req, res) {
	var responsedata = "";
	req.on('data', function(data){
		responsedata += data;
	});
	req.on('end', function(){
		res.sendStatus(200);
		var result = JSON.parse( responsedata );
		var room = 'notifiy_'+result.contest_unique_id;
		io.to(room).emit('NotifyContestStarted', result);
	});
});


//recieve scores
app.post('/recieveScore', function (req, res) {
	var target = true;
	var response = '';
	req.on('data', function (data) {
		response += data;
	});
	req.on('end', function () {
		res.sendStatus(200);
		var result = JSON.parse( response );		
		broadCastLiveScore( result );
	});	
});

var db = require('./mongodb')
console.log(mongo_db_url,'URL');
db.connect(mongo_db_url,{ useNewUrlParser: true, useUnifiedTopology: true },main_config.mongo_dtls.db_name, function(err) {
  if (err) {
    console.log('Unable to connect to Mongo.')
    process.exit(1)
  } else {
    server.listen(4000, { 'perMessageDeflate': false })
  }
})

function broadCastLiveScore( result ){
  io.to('notify_'+result.sports_id).emit('RecieveliveScore', result.collection_master_ids);
}