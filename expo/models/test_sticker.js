var mongoose = require('mongoose');
var sticker = require('./sticker.js');

var db_server = 'mongodb://localhost/318_expo';

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
}

var db = mongoose.connection;
db.on('error', console.error.bind(console, 'connection error:'));

db.on('open', function(callback) {

   var p = sticker.test();

   p.then(function(data){ console.log(data); });
});