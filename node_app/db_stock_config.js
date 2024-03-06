
var mysql = require('mysql')
var config = require('./config')[process.env.NODE_ENV]
// Add the credentials to access your database
var db;

function connectDatabase() {
    if (!db) {
        db = mysql.createPool({
                    connectionLimit:100,
				   	host:      process.env.STOCK_DBHOSTNAME,
				    user:       process.env.STOCK_DBUSERNAME,
				    password: process.env.STOCK_DBPASSWORD,
				    port:       3306, 
				    database: process.env.STOCK_DBNAME
				});
    }
    return db;
}

module.exports = connectDatabase();


