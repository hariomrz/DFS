
const QueryBuilder = require('node-querybuilder');
var pool;

function connectDatabase() {
    if (!pool) {
       let settings = {
            connectionLimit:100,
            host:      process.env.USER_DBHOSTNAME,
            user:       process.env.USER_DBUSERNAME,
            password: process.env.USER_DBPASSWORD,
            port:       3306, 
            database: process.env.USER_DBNAME
        }
        pool = new QueryBuilder(settings, 'mysql', 'pool');
       
    }
    return pool;
}

module.exports = connectDatabase();


