
module.exports = function (io,app) {
    var openPredictor = {};
    
   // const io = require('./app.js').io;
    io.on('connection', function(socket){
        //console.log('a user connected');
      
      socket.on('disconnect', function(){
        //console.log('user disconnected');
      });

      socket.on('JoinAddOpenPredictionRoom', function(data) {
        var category_id = data.category_id;
        var room = 'notify_add_open_prediction_list_'+category_id;
        socket.join(room);
      });

      socket.on('JoinDeleteOpenPredictionRoom', function(data) {
        var category_id = data.category_id;
        var room = 'notify_delete_open_prediction_'+category_id;
        socket.join(room);
      });
    
      socket.on('JoinPausePlayOpenPredictionRoom', function(data) {
        var category_id = data.category_id;
        var room = 'notify_pause_play_open_prediction_list_'+category_id;
        socket.join(room);
      });
    
      socket.on('JoinWonOpenPredictionRoom', function(data) {
        var user_id = data.user_id;
        var room = 'notify_won_open_prediction_'+user_id;
        socket.join(room);
      });  
    

    });   
    
    app.post('/newOpenPredictionAlert', function(req, res) {
      
        if(req.body.category_id)
        {
          res.sendStatus(200);
          var room = 'notify_add_open_prediction_list_'+req.body.category_id;
          io.to(room).emit('NotifyNewOpenPrediction', req.body);
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
      
      app.post('/updateOpenPredictionAlert', function(req, res) {
        if(req.body.category_id)
        {
          res.sendStatus(200);
          var room = 'notify_update_open_prediction_'+req.body.category_id;
          io.to(room).emit('NotifyUpdateOpenPrediction', req.body);
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
      
      app.post('/pausePlayOpenPrediction', function(req, res) {
        
        if(req.body.prediction_master_id && req.body.category_id)
        {
          res.sendStatus(200);
          var room = 'notify_pause_play_open_prediction_list_'+req.body.category_id;
          
          io.to(room).emit('NotifyPausePlayOpenPrediction', req.body);
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
      
      app.post('/wonOpenPrediction', function(req, res) {
        
        if(req.body.prediction_master_id && req.body.user_id)
        {
          res.sendStatus(200);
          var room = 'notify_won_open_prediction_'+req.body.user_id;
          
          io.to(room).emit('NotifyWonOpenPrediction', req.body);
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
      
      app.post('/deleteOpenPrediction', function(req, res) {
       
        if(req.body.prediction_master_id)
        {
          res.sendStatus(200);
          var room = 'notify_delete_open_prediction_'+req.body.category_id;
          
          io.to(room).emit('NotifyDeleteOpenPrediction', req.body);
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
