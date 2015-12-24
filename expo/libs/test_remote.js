var generate = require('./generate.js');

var url = 'http://public.318.io/sites/318_public/files/digicoll/public/013/13439_001.jpg';

generate.cache_process_remote_file(url, './test2.jpg', function(file_name) {
   console.log(file_name);
});
