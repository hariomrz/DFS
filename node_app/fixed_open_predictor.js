
module.exports = function (io,app) {
    var openPredictor = {};
    
   // const io = require('./app.js').io;
    io.on('connection', function(socket){
        //console.log('a user connected');
      
      socket.on('disconnect', function(){
        //console.log('user disconnected');
      });

      socket.on('JoinAddFixedOpenPredictionRoom', function(data) {
        var category_id = data.category_id;
        var room = 'notify_add_fixed_open_prediction_list_'+category_id;
        socket.join(room);
      });

      socket.on('JoinDeleteFixedOpenPredictionRoom', function(data) {
        var category_id = data.category_id;
        var room = 'notify_delete_fixed_open_prediction_'+category_id;
        socket.join(room);
      });
    
      socket.on('JoinPausePlayFixedOpenPredictionRoom', function(data) {
        var category_id = data.category_id;
        var room = 'notify_pause_play_fixed_open_prediction_list_'+category_id;
        socket.join(room);
      });
    
      socket.on('JoinWonFixedOpenPredictionRoom', function(data) {
        var user_id = data.user_id;
        var room = 'notify_won_fixed_open_prediction_'+user_id;
        socket.join(room);
      });  
    

    });   
    
    app.post('/newFixedOpenPredictionAlert', function(req, res) {
      
        if(req.body.category_id)
        {
          res.sendStatus(200);
          var room = 'notify_add_fixed_open_prediction_list_'+req.body.category_id;
          io.to(room).emit('NotifyNewFixedOpenPrediction', req.body);
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
      
      app.post('/updateFixedOpenPredictionAlert', function(req, res) {
        if(req.body.category_id)
        {
          res.sendStatus(200);
          var room = 'notify_update_fixed_open_prediction_'+req.body.category_id;
          io.to(room).emit('NotifyUpdateFixedOpenPrediction', req.body);
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
      
      app.post('/pausePlayFixedOpenPrediction', function(req, res) {
        
        if(req.body.prediction_master_id && req.body.category_id)
        {
          res.sendStatus(200);
          var room = 'notify_pause_play_fixed_open_prediction_list_'+req.body.category_id;
          
          io.to(room).emit('NotifyPausePlayFixedOpenPrediction', req.body);
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
      
      app.post('/wonFixedOpenPrediction', function(req, res) {
        
        if(req.body.prediction_master_id && req.body.user_id)
        {
          res.sendStatus(200);
          var room = 'notify_won_fixed_open_prediction_'+req.body.user_id;
          
          io.to(room).emit('NotifyWonFixedOpenPrediction', req.body);
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
      
      app.post('/deleteFixedOpenPrediction', function(req, res) {
       
        if(req.body.prediction_master_id)
        {
          res.sendStatus(200);
          var room = 'notify_delete_fixed_open_prediction_'+req.body.category_id;
          
          io.to(room).emit('NotifyDeleteFixedOpenPrediction', req.body);
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

    
};
