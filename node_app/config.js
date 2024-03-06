var config = {
    mongo_dtls:{
            host:process.env.MONGO_DBHOSTNAME,//"134.209.156.22",
            user:process.env.MONGO_DBUSERNAME,
            password:process.env.MONGO_DBPASSWORD,
            db_name:process.env.MONGO_DBNAME,
            port:process.env.MONGO_PORT,
            srv:process.env.MONGO_SRV
    },
    redis:{
        host:process.env.REDIS_HOST,
        port:process.env.REDIS_PORT,
        password : process.env.REDIS_PASSWORD
    },
    development:{
        database_game: {
           
        },
        database_fantasy: {
           
        },
        server: {
            host: '127.0.0.1',
            port: '4000'
        },
        redis:{
          
        },
        NODE_BASE_URL:'http://192.168.5.128:4000/',
        twitter:{
            
            },

        aws:{
           
        }
    },
    testing:{
        database_game: {
           
        },
        database_fantasy: {
          
        },
        server: {
            host: 'node.vinfotech.org',
            port: '4000'
        },
         redis:{
           
        },
        NODE_BASE_URL:'http://node.vinfotech.org:4000/',
        twitter:{
             
            },
        aws:{
           

        }
    },
     production:{
         database_game: {
           
        },
        database_fantasy: {
         
        },
        server: {
            host: 'cricjam.com',
            port: '4000'
        },
         redis:{
           

        },
        NODE_BASE_URL:'https://cricjam.com:4000/',
        twitter:{
           
            },
        aws:{
           
            
            }
    }
    
}
 
module.exports = config