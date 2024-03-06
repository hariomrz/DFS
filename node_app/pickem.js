
module.exports = function (io,app) {
    var openPredictor = {};
    
   // const io = require('./app.js').io;
    io.on('connection', function(socket){
        //console.log('a user connected');
      
      socket.on('disconnect', function(){
        //console.log('user disconnected');
      });

      socket.on('JoinAddPickemRoom', function(data) {
       
        var room = 'notify_add_pickem_list';
        socket.join(room);
      });

      socket.on('JoinDeletePickemRoom', function(data) {
        var room = 'notify_delete_pickem';
        socket.join(room);
      });

    
  
    

    });   
    
    app.post('/newPickemAlert', function(req, res) {
      
        if(req.body.pickem)
        {
          res.sendStatus(200);
          var room = 'notify_add_pickem_list';
          io.to(room).emit('NotifyNewPickem', req.body);
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

      app.post('/deletePickem', function(req, res) {
       
        if(req.body.pickem_id)
        {
          res.sendStatus(200);
          var room = 'notify_delete_pickem';
          
          io.to(room).emit('NotifyDeletePickem', req.body);
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
