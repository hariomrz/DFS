
var mysql = require('mysql')
var config = require('./config')[process.env.NODE_ENV]
// Add the credentials to access your database
var db;

function connectDatabase() {
    if (!db) {
        db = mysql.createPool({
                    connectionLimit:100,
				   	host:      process.env.PREDICTION_DBHOSTNAME,
				    user:       process.env.PREDICTION_DBUSERNAME,
				    password: process.env.PREDICTION_DBPASSWORD,
				    port:       3306, 
				    database: process.env.PREDICTION_DBNAME
				});
    }
    return db;
}

module.exports = connectDatabase();


