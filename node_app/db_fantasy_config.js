
var mysql = require('mysql')
var config = require('./config')[process.env.NODE_ENV]
// Add the credentials to access your database
var db;

function connectDatabase() {
    if (!db) {
        db = mysql.createPool({
                    connectionLimit:100,
				   	host:      process.env.FANTASY_DBHOSTNAME,
				    user:       process.env.FANTASY_DBUSERNAME,
				    password: process.env.FANTASY_DBPASSWORD,
				    port:       3306, 
				    database: process.env.FANTASY_DBNAME,
					acquireTimeout:60000
				});
    }
    return db;
}

module.exports = connectDatabase();


