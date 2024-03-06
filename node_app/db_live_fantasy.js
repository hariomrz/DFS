var mysql = require('mysql')
var config = require('./config')[process.env.NODE_ENV]
// Add the credentials to access your database
var db;

function connectDatabase() {
    if (!db) {
        db = mysql.createPool({
                    connectionLimit:100,
				   	host:      process.env.LF_DBHOSTNAME,
				    user:       process.env.LF_DBUSERNAME,
				    password: process.env.LF_DBPASSWORD,
				    port:       3306, 
				    database: process.env.LF_DBNAME,
					acquireTimeout:60000
				});
    }
    return db;
}

module.exports = connectDatabase();