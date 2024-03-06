
module.exports = function (io,app) {
   // const io = require('./app.js').io;
    io.on('connection', function(socket){
        //console.log('a user connected');
      
      socket.on('disconnect', function(){
       // console.log('user disconnected');
      });

      socket.on('JoinCollectionRoom', function(data) {
        var collection_id = data.collection_id;
        var user_id = data.user_id;
        var room = 'collection_'+user_id+'_'+collection_id;
        console.log('JoinCollectionRoom ', room);
        socket.join(room);
      });     

    });   
    
    app.post('/updateCollectionInfo', function(req, res) {
      //console.log('updateCollectionInfo');
      var status = 200;
      if(req.body.collection) {        
        var collection_info = req.body.collection;
        //console.log('collection_info', collection_info);
        var data = JSON.parse(JSON.stringify(collection_info));
        res.sendStatus(status);
        for(var key in data) { 
            var collection = JSON.stringify(data[key]);
            var room = 'collection_'+key;
            console.log('Collection room => '+room);
            console.log('Collection Info => ', collection);
            io.to(room).emit('updateCollectionInfo', collection);                
        }        
      } else {
        req.on('end', function () {
            res.sendStatus(status);
        });
      }
    });    
};
