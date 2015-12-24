module.exports = (function() {

var fs       = require('fs');
var request  = require('request');
//var generate = require('./generate.js');

function random_id(count) {
  var text = "";
  var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  for( var i=0; i < count; i++ )
     text += possible.charAt(Math.floor(Math.random() * possible.length));
  return text;
}

/*
 * The downloaded file will be cached
 * url: the file url to download
 * cache_file : /path/to/save/file
 * callback: function(cache_file) { }
 */
function cache_process_remote_file(url, cache_file, callback) {
  var tmpfile = "/tmp/" + random_id(10);

  fs.open(cache_file, 'r', function(err, fd) {
    if(err && err.code === 'ENOENT') {  // cache not exist
      request.get(url)
      .on('error', function(err) { throw err;})
      .on('end', function() {
        var source = fs.createReadStream(tmpfile);
        var dest = fs.createWriteStream(cache_file);
        source.pipe(dest);
        source
        .on('end', function() { 
          callback(cache_file);
        })
        .on('error', function(err) {  
          throw err;
        });
      })
      .pipe(fs.createWriteStream(tmpfile)); // if file exist ? should overwrite it
    } else if(err) { 
      throw err;
    } else {
      fs.closeSync(fd);
      // do something with cache file
      callback(cache_file);      
    }
  });
}

function process_remote_file(url, callback) {
  var tmpfile = "/tmp/" + random_id(10);
  request.get(url)
  .on('error', function(err) {
    throw err;
  })
  .on('end', function() {
    callback(tmpfile);
  })
  .pipe(fs.createWriteStream(tmpfile)); // if file exist ? should overwrite it  
}

return {
  'fetchfrom_url_or_cache' : cache_process_remote_file,
  'fetchfrom_url'          : process_remote_file
}

})();