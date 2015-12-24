var _       = require('underscore');
var path    = require('path');
var Sticker = require('../models/sticker.js');

var Pos      = require('../models/position.js');
var generate = require('../libs/generate.js');

var mongoose = require('mongoose');

var db_server = 'mongodb://localhost/318_expo';

var sticker_path = path.join(__dirname, '../public/images/stickers');
var cache_path   = path.join(__dirname, '../public/images/stickers/cache');

var gracefulExit = function() {
  mongoose.connection.close(function () {
    console.log('Mongoose default connection with DB :' + db_server + ' is disconnected through app termination');
    process.exit(0);
  });
}

// If the Node process ends, close the Mongoose connection
process.on('SIGINT', gracefulExit).on('SIGTERM', gracefulExit);

try {
  mongoose.connect(db_server);
  console.log("Trying to connect to DB " + db_server);
} catch (err) {
  console.log("Sever initialization failed " , err.message);
  throw err;
}

var db = mongoose.connection;
db.on('error', console.error.bind(console, 'connection error:'));
db.on('open', function(callback) {
  
  var p_all = Sticker.all();
  p_all.then(function(stickers) {
    stickers.forEach(function(stk, idx, this_arr) { generate.sticker_asset(stk); });
  });

});