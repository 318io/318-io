module.exports = (function() {

//var fs      = require('fs');
//var fs      = require('graceful-fs')
var _         = require('underscore');
var FileQueue = require('filequeue');
var fs        = new FileQueue(300);
var path    = require('path');
var Canvas  = require('canvas');
var gm      = require('gm');
var mkdirp  = require('mkdirp')
//var path    = require('path');
//var request = require('request');

var ef  = require('./easier.file.js');
var Pos = require('../models/position.js');

/* file name should be png */
function gen_sticker(path_file, text, font_ratio, size_x, size_y, from_x, from_y) {

  var Image = Canvas.Image;
  var canvas = new Canvas(size_x, size_y);
  var ctx = canvas.getContext('2d');

  var font_size = Math.floor(size_y / font_ratio);
  //console.log('font_size: ' + font_size);

  var line_height = font_size + 2;

  if(from_x == undefined) from_x = 0;
  if(from_y == undefined) from_y = 0;

  ctx.font = (font_size + 'px') + ' Impact';
  //ctx.font = '30px sans-serif';

  ctx.fillStyle = '#FEF778';
  ctx.fillRect(0, 0, size_x, size_y);

  ctx.fillStyle = '#000000';
  ctx.textBaseline = 'top';
  //ctx.fillText(text, 15, 30);

  var w = canvas.width, i = 0, x = from_x, y = from_y, lw, line = '';

  for(; i < text.length; i++) {
    lw = ctx.measureText(line).width;
    if (lw < w - ctx.measureText(text[i]).width) {
      line += text[i];
    } else {
      ctx.fillText(line, x, y);
      line = text[i];
      x = 0;
      y += line_height;
    }
  }
  if (line.length > 0) ctx.fillText(line, x, y);

  //var pth = path.join(__dirname, '../public/images/stickers/');
  //var sticker_file = pth + sid + ".png";

  var folder = path.dirname(path_file);

  mkdirp(folder, function(err) {
    if(!err) {
      var out = fs.createWriteStream(path_file);
      var stream = canvas.pngStream();
      stream.on('data', function(chunk) { out.write(chunk); })
      .on('end', function(){
        out.end();
      })      
    }
  });
}


/* 
 * title font size / canvas height = 1 / 4
 * path_file : /a/b/c/d.png
 */
function generate_title(path_file, text, size_x, size_y) {

  //var font_size = Math.floor(size_y / 4);
  //var line_height = font_size + 2;
  //var line_count = Math.floor(size_y / line_height);    
  //var start_line = Math.floor(line_count / 2);

  var from_x = 0; 
  var from_y = 0;

  //if(start_line == 0) from_y = 0;
  //else                from_y = start_line * line_height;

  //console.log('size_x: ' + size_x);
  //console.log('size_y: ' + size_y);
  //console.log('line_height: ' + line_height);
  //console.log('line_count: ' + line_count);
  //console.log('start_line: ' + start_line);    
  //console.log('from_x: ' + from_x);
  //console.log('from_y: ' + from_y);
  gen_sticker(path_file, text, 4, size_x, size_y, from_x, from_y);
}

/* 
 * sticker font size / canvas height = 1 / 10
 * file_name : /a/b/c/d.png
 */
function generate_sticker(path_file, text, size_x, size_y) {
  //var font_size = Math.floor(size_y / 10);
  //var line_height = font_size + 2;
  //var line_count = size_y / line_height;
  var from_x = 0;
  var from_y = 0;
  gen_sticker(path_file, text, 10, size_x, size_y, from_x, from_y);
}

function random_id(count) {
  var text = "";
  var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  for( var i=0; i < count; i++ )
     text += possible.charAt(Math.floor(Math.random() * possible.length));
  return text;
}

function get_real_width(width, height)  { return (width  * 770)/height; }

function get_real_height(width, height) { return (height * 800)/width;  }

function crop_public318_image(src_path_file, dst_path_file, width, height, callback) {

  // default handler
  if(callback == undefined || !_.isFunction(callback)) {
    callback = function(err) {
      if(err) throw err;
    }
  }

  var start_x = 0,
      start_y = 0;
  if(width >= height) {
    height = get_real_height(width, height);
  } else {
    width = get_real_width(width, height);
    start_x = (800 - width) / 2;
    height = 770;
  } 

  gm(src_path_file)
  .options({imageMagick: true})
  .crop(width, height, start_x, start_y)    
  .noProfile()
  .write(dst_path_file, callback);
}


function make_thumb_width(src_path_file, dst_path_file, width, callback) {
  var folder = path.dirname(dst_path_file);

  // default handler
  if(callback == undefined || !_.isFunction(callback)) {
    callback = function(err) {
      if(err) throw err;
    }
  }

  mkdirp(folder, function(err) {
    if(!err) {
      gm(src_path_file)
      .options({imageMagick: true})
      .resize(width)    // height will be according to the ratio
      .noProfile()
      .write(dst_path_file, callback);
    }
  });
}

function make_thumb(src_path_file, dst_path_file, width, height, callback) {
  var folder = path.dirname(dst_path_file);

  // default handler
  if(callback == undefined || !_.isFunction(callback)) {
    callback = function(err) {
      if(err) throw err;
    }
  }

  mkdirp(folder, function(err) {
    if(!err) {
      gm(src_path_file)
      .options({imageMagick: true})
      .resize(width, height)    // size should depends on sticker size
      .noProfile()
      .write(dst_path_file, callback);      
    }
  });
}


function generate_sticker_asset(stk, is_public318) {

  if(is_public318 == undefined) is_public318 = false;

  var sticker_path   = path.join(__dirname, '../public/images/stickers/');
  var cache_path     = path.join(__dirname, '../public/images/stickers/cache/');
  var sticker_folder = path.join(sticker_path, 'sid_' + stk.sid); // sid_1
  //var cache_folder = path.join(cache_path, 'sid_' + stk.sid);

  switch(stk.kind) {
    case 0: // sticker
      for(var i = 1; i <= 78; i++) {
        console.log('generate: ' + path.join(sticker_folder, Pos.get(i).id + '.png'));
        generate_sticker(path.join(sticker_folder, Pos.get(i).id + '.png'), _.unescape(stk.content), Pos.width(i), Pos.height(i)); 
      }
      break;
    case 1: // story
      for(var i = 1; i <= 78; i++) {
        console.log('generate: ' + path.join(sticker_folder, Pos.get(i).id + '.png'));
        generate_title(path.join(sticker_folder, Pos.get(i).id + '.png'), _.unescape(stk.title), Pos.width(i), Pos.height(i));         
      }
      break;
    case 2: // picture
    case 3: // video
      var raw_file  = cache_path + 'raw_' + stk.sid + '.jpg';
      var cache_file = cache_path + 'sid_' + stk.sid + '.jpg';
      if('pic_url' in stk && stk.pic_url != '') {
        try {
          ef.fetchfrom_url(stk.pic_url, function(tmp_file) {
            if(is_public318) {
              crop_public318_image(tmp_file, raw_file, stk.public318_width, stk.public318_height, function(err) {
                if(err) throw err;
                make_thumb_width(raw_file, cache_file, 500, function(err) {
                  if(err) throw err;
                  for(var i = 1; i <= 78; i++) {
                    console.log('generate: ' + path.join(sticker_folder, Pos.get(i).id + '.png'));
                    make_thumb(cache_file, path.join(sticker_folder, Pos.get(i).id + '.png'), Pos.width(i), Pos.height(i));                    
                  }
                });
              });
            } else {
              make_thumb_width(tmp_file, cache_file, 500, function(err) {
                if(err) throw err;
                for(var i = 1; i <= 78; i++) {
                  console.log('generate: ' + path.join(sticker_folder, Pos.get(i).id + '.png'));
                  make_thumb(cache_file, path.join(sticker_folder, Pos.get(i).id + '.png'), Pos.width(i), Pos.height(i));
                }              
              });                                            
            }
          });              
        } catch(err) {
          console.log('fetch error' + err);
        }
      }
      break;
  }
}

return {
  'random_id'   : random_id,
  '318crop'     : crop_public318_image,
  'thumb_width' : make_thumb_width,
  'thumb'       : make_thumb,
  'sticker'     : generate_sticker,
  'title_sticker' : generate_title,
  'sticker_asset' : generate_sticker_asset
};

})();




/*
function generate_title(sid, txt, size_x, size_y, font_size, from_x, from_y) {
  var Image = Canvas.Image;
  var canvas = new Canvas(size_x, size_y);
  var ctx = canvas.getContext('2d');
  var fs = require('fs');

  if(from_x == undefined) from_x = 0;
  if(from_y == undefined) from_y = 0;

  ctx.font = font_size + ' Impact';
  //ctx.font = '30px sans-serif';

  ctx.fillStyle = '#FEF778';
  ctx.fillRect(0, 0, size_x, size_y);

  ctx.fillStyle = '#000000';
  ctx.textBaseline = 'top';
  //ctx.fillText(text, 15, 30);
  //var txt = 'Blogger 的成人內容政策要調整了，未來將不提供成人內容公開傳播了，只能設定成私人網誌。影響會是我得搬家了，還有以後要找資訊會變難找了。我還沒想好要怎麼應對，要搬到哪之類的。讓我想想再跟你們說',

  var w = canvas.width, i = 0, x = from_x, y = from_y, lw, line = '';

  for(; i < txt.length; i++) {
    lw = ctx.measureText(line).width;
    if (lw < w - ctx.measureText(txt[i]).width) {
      line += txt[i];
    } else {
      ctx.fillText(line, x, y);
      line = txt[i];
      x = 0;
      y += 90;
    }
  }
  if (line.length > 0) ctx.fillText(line, x, y);

  var pth = path.join(__dirname, '../public/images/stickers/');
  var sticker_file = pth + sid + ".png";

  var out = fs.createWriteStream(sticker_file);
  var stream = canvas.pngStream();

  stream.on('data', function(chunk) {
    out.write(chunk);
  });
}


function generate_sticker(sid, txt, size_x, size_y, font_size, from_x, from_y) {
  var Image = Canvas.Image;
  var canvas = new Canvas(size_x, size_y);
  var ctx = canvas.getContext('2d');
  var fs = require('fs');

  if(from_x == undefined) from_x = 0;
  if(from_y == undefined) from_y = 0;

  ctx.font = font_size + ' Impact';
  //ctx.font = '30px sans-serif';

  ctx.fillStyle = '#FEF778';
  ctx.fillRect(0, 0, size_x, size_y);

  ctx.fillStyle = '#000000';
  ctx.textBaseline = 'top';
  //ctx.fillText(text, 15, 30);
  //var txt = 'Blogger 的成人內容政策要調整了，未來將不提供成人內容公開傳播了，只能設定成私人網誌。影響會是我得搬家了，還有以後要找資訊會變難找了。我還沒想好要怎麼應對，要搬到哪之類的。讓我想想再跟你們說',

  var w = canvas.width, i = 0, x = from_x, y = from_y, lw, line = '';

  for(; i < txt.length; i++) {
    lw = ctx.measureText(line).width;
    if (lw < w - ctx.measureText(txt[i]).width) {
      line += txt[i];
    } else {
      ctx.fillText(line, x, y);
      line = txt[i];
      x = 0;
      y += 50;
    }
  }
  if (line.length > 0) ctx.fillText(line, x, y);

  var pth = path.join(__dirname, '../public/images/stickers/');
  var sticker_file = pth + sid + ".png";

  var out = fs.createWriteStream(sticker_file);
  var stream = canvas.pngStream();

  stream.on('data', function(chunk) {
    out.write(chunk);
  });
}

function create_thumb(obj, width, height) {

  var sid     = obj.sid;
  var pic_url = obj.pic_url;
  var w       = width;
  var h       = height;

  var p = path.join(__dirname, '../public/images/stickers/');
  //var tmp_id = chance.string({length: 6});
  var tmpfile = "/tmp/" + sid;
  var thumbfile = p + sid + ".png";

  //console.log('here');

  request.get(pic_url)
  .on('error', function(err) {
    console.log(err)
  })
  .on('end', function(){

    // 做縮圖
    gm(tmpfile)
    .options({imageMagick: true})
    .resize(w, h)    // size should depends on sticker size
    .noProfile()
    .write(thumbfile, function (err) {
      if (!err) console.log('done');
      else      console.log(err);
    });

  }).pipe(fs.createWriteStream(tmpfile)); // if file exist ? should overwrite it
}

*/