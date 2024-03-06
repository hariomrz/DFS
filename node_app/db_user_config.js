
var mysql = require('mysql')
var config = require('./config')[process.env.NODE_ENV]
// Add the credentials to access your database
var db;

function connectDatabase() {
    if (!db) {
        db = mysql.createPool({
                    connectionLimit:100,
				   	host:      process.env.USER_DBHOSTNAME,
				    user:       process.env.USER_DBUSERNAME,
				    password: process.env.USER_DBPASSWORD,
				    port:       3306, 
				    database: process.env.USER_DBNAME
				});
    }
    return db;
}

module.exports = connectDatabase();


