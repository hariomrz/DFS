const helper = require('./helper/default_helper');


module.exports = function (io,app) {
  var match_users = {};
   // const io = require('./app.js').io;
  var gameCenterModel = require('./models/gameCenterModel');
  let clients = {};
  io.on('connection', function(socket){
    clients[socket.id] = socket;
    //console.log('user connected center');
    socket.on('disconnect', function(){
      delete clients[socket.id];
    });

    
    socket.on('JoinGameCenter', async function(data) 
    {
      //console.log("match==",data);
      var user_id = data.user_id;
      var collection_master_id = data.collection_master_id;
      if(!match_users[collection_master_id])
      {
        match_users[collection_master_id] = [];
      }

      if(match_users[collection_master_id].indexOf(user_id)==-1)
      {
        match_users[collection_master_id].push(user_id);
      }

      console.log("match_users[collection_master_id]:",match_users[collection_master_id].length);
      var room = `game_center_${collection_master_id}`;
      var collection_user_room = `game_center_${collection_master_id}_${user_id}`;
      var user_room = `user_${user_id}`;
      socket.join(room);
      socket.join(collection_user_room);
      socket.join(user_room);

      var match_data = await gameCenterModel.get_match_score(collection_master_id);
      let deadline_time=0;
      let ms = 0;
      if (match_data ) {

        if(match_data.deadline_time && match_data.deadline_time >= 0)
        {
          deadline_time = match_data.deadline_time;
        }
        const season_scheduled_date = new Date(match_data.season_scheduled_date);
        ms = season_scheduled_date.getMilliseconds();
			}
      
      match_data.game_starts_in = (ms - (deadline_time *60))*  1000;
      match_data.status = parseInt(match_data.status);
      /**if (isset($collection_info['deadline_time']) && $collection_info['deadline_time'] >= 0) {
				$deadline_time = $collection_info['deadline_time'];
			}

      $data['game_starts_in'] = (strtotime($data['season_scheduled_date']) - ($deadline_time *60))*  1000;**/
      io.to(room).emit('updateMatchScore', match_data);
      //console.log('match_score:',match_data);

      //get live match list from cache
      let cache_key ="gc_live_matches";
      var live_match =await helper.get_cache_data(cache_key);
      if(!live_match)
      {
        live_match = await gameCenterModel.get_live_match_list(match_data.sports_id);
        //console.log("Live Match:",live_match);
        //set cache live match
        helper.set_cache_data(cache_key,live_match);
      }
      io.to(collection_user_room).emit('updateLiveMatch', live_match);

      var user_games = await gameCenterModel.get_match_users_rank(collection_master_id,[user_id]);
     // io.to(room).emit('updateMatchRank', match_rank);

     let user_wise_teams = [];
     user_games.map( (item,index)=>{
        if(!user_wise_teams[item.user_id])
        {
          user_wise_teams[item.user_id] = []; 
        } 

        item.prize_distibution_detail = JSON.parse(item.prize_distibution_detail);
        user_wise_teams[item.user_id].push(item);
        
      });

      //console.log("xxxxxxxb",user_wise_teams);
      user_wise_teams.map( (item,index)=>{
        io.to(`user_${index}`).emit('updateMatchRank', item);
        //console.log("check 1",index);
      });
    });
  });
    
  app.post('/updateMatchScore', function(req, res) {
    var status = 200;
    if(req.body.data) {
      var post_data = req.body.data;
      var data = JSON.parse(JSON.stringify(post_data));
      var collection_master_id = data.collection_master_id;
      var room = 'game_center_'+collection_master_id;
      io.to(room).emit('updateMatchScore', post_data);
      res.sendStatus(status);
      //console.log("users",match_users);
    } else {
      req.on('end', function () {
          res.sendStatus(status);
      });
    } 
  });

  app.post('/updateMatchRank', async function(req, res) {
    var status = 200;
    if(req.body.data) {
      var post_data = req.body.data;
      var data = JSON.parse(JSON.stringify(post_data));
      var collection_master_id = data.collection_master_id;
      var room = 'game_center_'+collection_master_id;
      //var user_games = await getUsersRank(collection_master_id,match_users);

      if(match_users[collection_master_id] === undefined || match_users[collection_master_id] == 'undefined' )
      {
        res.sendStatus(status);
        return;
      }
      else{

       // console.log("match_users:",match_users[collection_master_id]);

        let uniqueArray = match_users[collection_master_id].filter(function(item, pos) {
          return match_users[collection_master_id].indexOf(item) == pos;
        })
        var user_games = await gameCenterModel.get_match_users_rank(collection_master_id,uniqueArray);

        let user_wise_teams = [];
        user_games.map( (item,index)=>{
          if(!user_wise_teams[item.user_id])
          {
            user_wise_teams[item.user_id] = []; 
          } 

          item.prize_distibution_detail = JSON.parse(item.prize_distibution_detail);
          user_wise_teams[item.user_id].push(item);
          
        });

        //console.log("xxxxxxxb",user_wise_teams);
        user_wise_teams.map( (item,index)=>{
          io.to(`user_${index}`).emit('updateMatchRank', item);
          //console.log("check 1",index);
        });
       // console.log("check 2");
        res.sendStatus(status);
      }
    } else {
      req.on('end', function () {
          res.sendStatus(status);
      });
    } 

  
  });

  
};
