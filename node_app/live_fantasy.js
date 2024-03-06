const helper = require('./helper/default_helper');


module.exports = function (io,app) {
  var match_users = {};
  var lf_users = {};
   // const io = require('./app.js').io;
  var liveFantasyModel = require('./models/liveFantasyModel');
  let clients = {};
  io.on('connection', function(socket){
    clients[socket.id] = socket;
    //console.log('user connected center');
    socket.on('disconnect', function(){
      delete clients[socket.id];
    });

    socket.on('JoinLF', async function(data) 
    {
      var user_id = data.user_id;
      var collection_id = data.collection_id;
      if(!match_users[collection_id])
      {
        match_users[collection_id] = [];
      }

      if(match_users[collection_id].indexOf(user_id)==-1)
      {
        match_users[collection_id].push(user_id);
      }

      //console.log("match_users[collection_id]:",match_users[collection_id].length);
      var room = `lf_game_${collection_id}`;
      var user_room = `lf_user_${collection_id}_${user_id}`;
      socket.join(room);
      socket.join(user_room);

      /*var match_data = await liveFantasyModel.get_match_score(collection_id);
      let deadline_time=0;
      let ms = 0;
      if (match_data ) {
        const season_scheduled_date = new Date(match_data.season_scheduled_date);
        ms = season_scheduled_date.getMilliseconds();
			}
      
      match_data.game_starts_in = (ms - (deadline_time *60))*  1000;
      match_data.status = parseInt(match_data.status);
      io.to(room).emit('updateMatchScoreLF', match_data);*/
      //console.log('match_score:',match_data);

      var user_games = await liveFantasyModel.get_match_users_rank(collection_id,[user_id]);
      let user_wise_teams = [];
      user_games.map( (item,index)=>{
        if(!user_wise_teams[item.user_id])
        {
          user_wise_teams[item.user_id] = []; 
        } 

        item.prize_detail = JSON.parse(item.prize_detail);
        user_wise_teams[item.user_id].push(item);
      });

      if(user_wise_teams.length > 0){
        user_wise_teams.map( (item,index)=>{
          io.to(`lf_user_${collection_id}_${index}`).emit('updateMatchRankLF', item);
        });
      }else{
        io.to(`lf_user_${collection_id}_${user_id}`).emit('updateMatchRankLF', {});
      }

    });

    socket.on('JoinMatchLF', async function(data) 
    {
      var collection_id = data.collection_id;
      var room = `lf_match_${collection_id}`;
      socket.join(room);
    });

    socket.on('JoinTimerLF', async function(data) 
    {
      var room = `lf_timer`;
      socket.join(room);
    });

    socket.on('JoinLFGame', async function(data) 
    {
      var user_id = data.user_id;
      var room = `lf_game_users_${user_id}`;
      socket.join(room);
    });
  });

  app.post('/updateMatchScoreLF', function(req, res) {
    var status = 200;
    if(req.body.data) {
      var post_data = req.body.data;
      var data = JSON.parse(JSON.stringify(post_data));
      var collection_id = data.collection_id;
      var room = 'lf_game_'+collection_id;
      io.to(room).emit('updateMatchScoreLF', post_data);
      res.sendStatus(status);
      //console.log("users",match_users);
    } else {
      req.on('end', function () {
          res.sendStatus(status);
      });
    } 
  });
  app.post('/updateMatchRankLF', async function(req, res) {
    var status = 200;
    if(req.body.data) {
      var post_data = req.body.data;
      var data = JSON.parse(JSON.stringify(post_data));
      var collection_id = data.collection_id;
      var room = 'lf_game_'+collection_id;
      if(match_users[collection_id] === undefined || match_users[collection_id] == 'undefined' )
      {
        res.sendStatus(status);
        return;
      }
      else{
        let uniqueArray = match_users[collection_id].filter(function(item, pos) {
          return match_users[collection_id].indexOf(item) == pos;
        })
        var user_games = await liveFantasyModel.get_match_users_rank(collection_id,uniqueArray);
        let user_wise_teams = [];
        user_games.map( (item,index)=>{
          if(!user_wise_teams[item.user_id])
          {
            user_wise_teams[item.user_id] = []; 
          } 

          item.prize_detail = JSON.parse(item.prize_detail);
          user_wise_teams[item.user_id].push(item);
        });

        user_wise_teams.map( (item,index)=>{
          io.to(`lf_user_${collection_id}_${index}`).emit('updateMatchRankLF', item);
        });
        res.sendStatus(status);
      }
    } else {
      req.on('end', function () {
          res.sendStatus(status);
      });
    }
  });
  app.post('/updateMatchOverOdds', function(req, res) {
    //console.log('updateCollectionInfo');
    var status = 200;
    if(req.body.data) {        
      var match_odds = req.body.data;
      var data = JSON.parse(JSON.stringify(match_odds));
      var collection_id = data.collection_id;
      var room = 'lf_game_'+collection_id;
      io.to(room).emit('updateMatchOddsLF', data);
      res.sendStatus(status);
      //console.log('ball_odds', data);
    } else {
      req.on('end', function () {
          res.sendStatus(status);
      });
    }
  });
  app.post('/updateMatchOddsResult', function(req, res) {
    //console.log('updateCollectionInfo');
    var status = 200;
    if(req.body.data) {        
      var match_odds = req.body.data;
      var data = JSON.parse(JSON.stringify(match_odds));
      var collection_id = data.collection_id;
      var room = 'lf_game_'+collection_id;
      io.to(room).emit('updateMatchOddsResultLF', data);
      res.sendStatus(status);
      //console.log('odds_result', data);
    } else {
      req.on('end', function () {
          res.sendStatus(status);
      });
    }
  });
  app.post('/updateMatchOverStatus', function(req, res) {
    var status = 200;
    if(req.body.data) {        
      var over_status = req.body.data;
      var data = JSON.parse(JSON.stringify(over_status));
      var collection_id = data.collection_id;
      var room = 'lf_match_'+collection_id;
      io.to(room).emit('updateMatchOverStatus', data);
      res.sendStatus(status);
      //console.log('over status', data);
    } else {
      req.on('end', function () {
          res.sendStatus(status);
      });
    }
  });
  app.post('/updateMatchOverTimer', function(req, res) {
    var status = 200;
    if(req.body.data) {        
      var timer_data = req.body.data;
      var data = JSON.parse(JSON.stringify(timer_data));
      var collection_id = data.collection_id;
      var room = 'lf_timer';
      io.to(room).emit('updateMatchOverTimer', data);
      res.sendStatus(status);
      //console.log('over status', data);
    } else {
      req.on('end', function () {
          res.sendStatus(status);
      });
    }
  });
  app.post('/updateMatchOverLive', function(req, res) {
    var status = 200;
    if(req.body.data) {        
      var timer_data = req.body.data;
      var data = JSON.parse(JSON.stringify(timer_data));
      var collection_id = data.collection_id;
      var user_ids = data.user_ids;
      user_ids.map( (user_id,index)=>{
        var gm_data = {"collection_id":collection_id,"user_id":user_id};
        io.to(`lf_game_users_${user_id}`).emit('updateMatchOverLive', gm_data);
      });
      res.sendStatus(status);
      //console.log('over live', user_ids);
    } else {
      req.on('end', function () {
          res.sendStatus(status);
      });
    }
  });
};
