var mongoose = require('mongoose');
var variable = require('./variable.js');

var db_server = 'mongodb://localhost/mytest';

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

  variable.set('hello', 'world');

  var p = variable.get('hello', 'nothing');
  p.then(function(v){
    console.log(v);
  });

  var p2 = variable.get('haha', 'nothing');
  p2.then(function(v){
    console.log(v);
  });

  variable.del('hello');

  variable.set('count', 120);
  var p3 = variable.get('count', 0);
  p3.then(function(v){
    console.log(v);
  })

  //console.log('done');
  variable.test('count');
  variable.test('aaa');
});
