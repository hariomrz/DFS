const { extendWith } = require('lodash');
const helper = require('./helper/default_helper');


module.exports = function (io,app) {
  var match_users = {};
  var lf_users = {};
   // const io = require('./app.js').io;
  var opinionTradeModel = require('./models/opinionTradeModel');
  let clients = {};
  io.on('connection', function(socket){
    clients[socket.id] = socket;
    
    socket.on('disconnect', function(){
      delete clients[socket.id];
    });

    socket.on('JoinOpinonTrade', async function(data) 
    {
      
      var user_id = data.user_id;
      console.log('user_id'+user_id);
      // question trade room
      var room = `ot_question_trade`;
      socket.join(room);

      // user room
      var room = `ot_user_${user_id}`;
      socket.join(room);
    });
  });


  app.post('/QestnTradeOT', function(req, res){
    var status = 200;
    if(req.body.data) {
      var req_data = req.body.data;
     
      var post_data = JSON.parse(JSON.stringify(req_data));
      var room = 'ot_question_trade';
      console.log('question common room ='+room);
      console.log('question post data ='+JSON.stringify(post_data));

      io.to(room).emit('UpdateQestnTradeOT',post_data);
      res.sendStatus(status);
     
    } else {
      req.on('end', function () {
        res.sendStatus(status);
      });
    } 
  });

  app.post('/UserQestnTradeOT', function(req, res){
    var status = 200;
    if(req.body.data) {
      var post_data = req.body.data;
     
      var post_data = JSON.parse(JSON.stringify(post_data));
      //console.log(JSON.stringify(post_data));
      
      for(const item of post_data){
        const userId = item.user_id;
        
        var room = 'ot_user_'+userId;

        console.log('User Question room ='+room);
        console.log('User Question post data ='+JSON.stringify(item));

        io.to(room).emit('UpdateUserQestnTradeOT',item);
      }

      res.sendStatus(status);
    
    } else {
      req.on('end', function () {
        res.sendStatus(status);
      });
    } 
  });

  app.post('/UserMatchupTradeOT', function(req, res){
    var status = 200;
    if(req.body.data) {
      var post_data = req.body.data;
     
      var post_data = JSON.parse(JSON.stringify(post_data));
      var userId = post_data.user_id
      var room = 'ot_user_'+userId;

      console.log('User matchup room ='+room);
      console.log('User matchup post data ='+JSON.stringify(post_data));

      io.to(room).emit('UserMatchupTradeOT',post_data);

      res.sendStatus(status);
    
    } else {
      req.on('end', function () {
        res.sendStatus(status);
      });
    } 
  });

  

  
};
