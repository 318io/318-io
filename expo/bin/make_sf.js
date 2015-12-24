
var path     = require('path');
var generate = require('../libs/generate.js');
var Pos      = require('../models/position.js');
var sticker_path   = path.join(__dirname, '../public/images/stickers/');
var cache_path     = path.join(__dirname, '../public/images/stickers/cache/');
var sticker_folder = path.join(sticker_path, 'sid_0'); // sid_1

var sunflower = {
    sid: 0,
    kind: 2, // actually, its story, we use picture(2) only for build thumb 
}

var cache_file = cache_path + 'sid_0.png';
 
//generate.sticker_asset(sunflower);
for(var i = 1; i <= 78; i++) {
  console.log('generate: ' + path.join(sticker_folder, Pos.get(i).id + '.png'));
  generate.thumb(cache_file, path.join(sticker_folder, Pos.get(i).id + '.png'), Pos.width(i), Pos.height(i));
}
