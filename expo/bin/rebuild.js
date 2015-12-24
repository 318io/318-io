var _       = require('underscore');
var path    = require('path');
var Sticker = require('../models/sticker.js');

var Pos      = require('../models/position.js');
var generate = require('../libs/generate.js');

var mongoose = require('mongoose');

var db_server = 'mongodb://localhost/318_expo';

var sticker_path = path.join(__dirname, '../public/images/stickers');
var cache_path   = path.join(__dirname, '../public/images/stickers/cache');

var request  = require('request');
var JSON3    = require('json3');


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

function getFileName(url) {
  return url.split('/').pop();
}

function isPicture(url) {
  var extension = url.split('/').pop().split('.').pop().toLowerCase();
  var format = {
    'jpg' : '',
    'jpeg': '',
    'png' : '',
    'bmp' : '',
    'gif' : ''
  }
  return (extension in format ) ? true : false;
}

//function get_real_width(width, height)  { return (width  * 770)/height; }

function get_real_height(width, height) { return (height * 500)/width;  }


var db = mongoose.connection;
db.on('error', console.error.bind(console, 'connection error:'));
db.on('open', function(callback) {
  
  var p_all = Sticker.all();
  p_all.then(function(stickers) {
    stickers.forEach(function(stk, idx, this_arr) { 
      console.log('sticker: ' + stk.sid + '[' + stk.kind + ']');
      if(stk.kind == 2) { // picture

        var col = stk.target_url.split('/').pop();
        //console.log('request:' + col);
        var url = 'http://public.318.io/api/meta/' + col + '?pi314159=pi314159';
        var target = 'http://public.318.io/' + col;
        
        request(url, function (error, response, body) {
          if (!error && response.statusCode == 200) {
            //console.log(body) // Show the HTML for the Google homepage.
            meta = JSON3.parse(body);

            if(isPicture(meta.file[0])) {
              var file_name = getFileName(meta.file[0]);
              var title = '', author = '', content = '';
              if('license_note' in meta.metadata) author  = meta.metadata.license_note[0];
              if('description' in meta.metadata)  content = meta.metadata.description[0];
              if('content' in meta.metadata)      content = meta.metadata.content[0];

              var newFields = {
                'title': title,
                'author': author,
                'content': content,
                'public318': true,
                'public318_id': parseInt(col),
                'public318_width': meta.metadata.mediainfo[file_name].width,
                'public318_height': meta.metadata.mediainfo[file_name].height              
              };
              console.log('updating collection: ' + col);
              Sticker.update_obj_only(stk.sid, newFields);
            }
          }
        });
        
        //var w = 500;
        //var h = get_real_height(stk.public318_width, stk.public318_height);
      }
      //generate.sticker_asset(stk); 
    });
  });

});

/*
request(url, function (error, response, body) {
  if (!error && response.statusCode == 200) {
    //console.log(body) // Show the HTML for the Google homepage.
    meta = JSON3.parse(body);
    var file_name = getFileName(meta.file[0]);

    if(isPicture(meta.file[0])) {
      var file_name = getFileName(meta.file[0]);
      var title = '', author = '', content = '';
      if('description' in meta.metadata)  title   = meta.metadata.description[0];
      if('license_note' in meta.metadata) author  = meta.metadata.license_note[0];
      if('content' in meta.metadata)      content = meta.metadata.content[0];

      var obj = {
        'kind': 2,       // picture
        'title': title,
        'author': author,
        'content': content,
        'pic_url': meta.file[0],
        'target_url': target,
        'public318': true,
        'public318_id': parseInt(col),
        'public318_width': meta.metadata.mediainfo[file_name].width,
        'public318_height': meta.metadata.mediainfo[file_name].height
      };

    var newFields = {
      'public318': true,
      'public318_id': parseInt(col),
      'public318_width': meta.metadata.mediainfo[file_name].width,
      'public318_height': meta.metadata.mediainfo[file_name].height              
    };
    console.log('updating collection: ' + col);
    Sticker.update_obj(stk.sid, newFields);
  }
});
*/