
var mysql = require('mysql')
var config = require('./config')[process.env.NODE_ENV]
// Add the credentials to access your database
var db;

function connectDatabase() {
    if (!db) {
        db = mysql.createPool({
                    connectionLimit:100,
				   	host:      process.env.FIXED_OPEN_PREDICTOR_DBHOSTNAME,
				    user:       process.env.FIXED_OPEN_PREDICTOR_DBUSERNAME,
				    password: process.env.FIXED_OPEN_PREDICTOR_DBPASSWORD,
				    port:       3306, 
				    database: process.env.FIXED_OPEN_PREDICTOR_DBNAME
				});
    }
    return db;
}

module.exports = connectDatabase();


