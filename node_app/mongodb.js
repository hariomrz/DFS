var MongoClient = require('mongodb').MongoClient

var state = {
  db: null,
}

exports.connect = function(url,ops,dbname, done) {
  if (state.db) return done()

  MongoClient.connect(url,ops, function(err, client) {
    if (err) return done(err)
    state.db = client.db(dbname)
    done()
  })
}

exports.get = function() {
  return state.db
}

exports.close = function(done) {
  if (state.db) {
    state.db.close(function(err, result) {
      state.db = null
      state.mode = null
      done(err)
    })
  }
}