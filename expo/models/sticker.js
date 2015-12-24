module.exports = (function() {

var _            = require('underscore');
var mongoose     = require('mongoose');
var sanitizeHtml = require('sanitize-html');
var path         = require('path');
var variable     = require('./variable.js');
var random       = require('mongoose-random');
//var random = require('mongoose-simple-random');

var generate = require('../libs/generate.js');
var ef       = require('../libs/easier.file.js');

/*
3 kinds of stickers

Sticker (plain text)
   title: no title
   content: plain text, no media
   pic_url: auto generated from content

   use content to build thumbs

Story (an article)
   title: plain text
   content: can contain media
   pic_url: auto generated from title

   use title to build thumbs

Picture
   title: plain text
   pic_url: user specified picture url
   content: no content

   request pic_url to build thumbs, what if request fail ?
*/

var stickerSchema = new mongoose.Schema({

  sid: { type: Number, unique: true },  // sticker id, 1 ... N, index later

  title: String,   // sticker title, optional

  author: String, 

  content: String, // sticker content, optional

  pic_url: { type: String, 'default': '' },

  video_url: { type: String, 'default': '' },
  
  target_url: { type: String, 'default': '' },

  kind: Number,   // 0: sticker, 1: story, 2: picture, 3: video

  status: Number, // 0: pending, 1: unpublish, 2: publish

  createdOn: { type: Date, 'default': Date.now },

  public318: Boolean,
  public318_id: Number,
  public318_width: Number,
  public318_height: Number
  //pos_x : Number,  // top left position X
  //pos_y : Number,  // top left position Y
  //width : Number,  // sticker width
  //height: Number,  // sticker height
  //map_id: { type: Number, 'default': 0 }  // map id
});

stickerSchema.plugin(random);

var Sticker = mongoose.model('Sticker', stickerSchema);

/*
Sticker.syncRandom(function (err, result) {
  console.log(result.updated);
});
*/

var title_allow = { 
  allowedTags: [ 'a', 'b', 'i', 'strong', 'em'], 
  allowedAttributes: {
    a: [ 'href', 'name', 'target' ],
  }
};

var content_allow = {
  allowedTags: [ 'iframe', 'video', 'source', 'img', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'p', 'a', 'ul', 'ol', 'nl', 'li', 'b', 'i', 'strong', 'em', 'strike', 'code', 'hr', 'br', 'div', 'table', 'thead', 'caption', 'tbody', 'tr', 'th', 'td', 'pre' ],
  allowedAttributes: {
    a: [ 'href', 'name', 'target' ],
    img: [ 'src', 'alt', 'border', 'hspace', 'vspace', 'style' ],
    video: [ 'controls', 'height', 'width', 'id', 'poster'],
    iframe: ['allowfullscreen', 'allowscriptaccess','frameborder', 'height', 'scrolling', 'src', 'width'],
    source: ['src', 'type']
  },
  // Lots of these won't come up by default because we don't allow them
  selfClosing: [ 'img', 'br', 'hr', 'area', 'base', 'basefont', 'input', 'link', 'meta', 'source' ],
  // URL schemes we permit
  allowedSchemes: [ 'http', 'https', 'data' ]
};

function last_id() {
  var p = Sticker.find({}).select('sid').sort({sid: -1}).limit(1).exec();
  return p.then(function(data) {
    if(data.length === 0) return 0;
    else                  return data[0].sid;
  }, function(err){
    console.log('last_id(): ' + err);
    return -1;
  });
}

function validate_sticker(obj) {
  switch(obj.kind) {
    case 0: //sticker
      break;
    case 1: // story
      break;
    case 3: // video
      if(!('video_url' in obj)) throw Error('validate_sticker(): video_url is not defined.');  // note, no break here !!!
    case 2: // picture
      if(!('pic_url' in obj)) throw Error('validate_sticker(): pic_url is not defined.');
      else {
        if(!('target_url' in obj)) obj.target_url = obj.pic_url;
      }
      break;
  }
}


function sticker_new(obj) {
 
  if('author' in obj)  obj.author  = sanitizeHtml(obj.author, title_allow); 
  if('title' in obj)   obj.title   = sanitizeHtml(obj.title, title_allow);
  if('content' in obj) obj.content = sanitizeHtml(obj.content, content_allow);

  /*
   * After sanitize, the 'author' , 'title' , 'content' are escaped content
   * < == &lt;
   * > == &gt;
   *
   * use !{content} in jade template
   */
 
  var p = last_id();

  p.then(function (sid) {
    if(sid == -1) throw new Error("sticker_new(): sid is -1.");
    obj.sid = (sid + 1);

    validate_sticker(obj); // validate and fix some field

    var newSticker = new Sticker(obj);
    newSticker.save(function(err) { if(err) console.log('sticker_new(): ' + err); });

    if('public318' in obj && obj.public318 == true) generate.sticker_asset(obj, true);
    else                                            generate.sticker_asset(obj); // default is false
    //generate.sticker_asset(obj);
  });
}


/*
function sticker_new(obj) {

  obj.title = sanitizeHtml(obj.title, title_allow);
  obj.content = sanitizeHtml(obj.content, content_allow);

  var p = variable.get('sid', 0);
  p.then(function (sid) {
    obj.sid = (sid + 1);
    var newSticker = new Sticker(obj);
    newSticker.save(function(err) { if(err) console.log('sticker_new(): ' + err); });
    variable.set('sid', (sid+1));

    console.log(obj.pic_url);

    if(obj.pic_url != '') create_thumb(obj);
  });
}
*/

function sticker_random(count, condition) {
  if(condition == undefined) condition = {};
  try{
    var p = Sticker.findRandom(condition).limit(count).exec();
    return p;    
  } catch(e) {
    console.log(e);
    throw e;
  }
  //return p;
}

function sticker_all() {
  var p = Sticker.find({}).exec();
  return p;
}

function sticker_get(sid) {
  var p = Sticker.findOne({'sid' : sid}).exec();
  return p; // 若找不到 p.then 得到的會是 null
}

function sticker_del(sid) {
  Sticker.findOne({ 'sid': sid}, function(err, sticker) {
    if(!err) {
      sticker.remove(function(err) {
        if(err) console.log('sticker_del():' + err);
      });
    } else {
      console.log('sticker_del():' + err);
    }
  });
}

/*
 * no sticker assets are generated
 */
function update_obj_field(sid, object) {

  object.author  = sanitizeHtml(object.author, title_allow); 
  object.title   = sanitizeHtml(object.title, title_allow);
  object.content = sanitizeHtml(object.content, content_allow);

  Sticker.update(
    {'sid': sid},
    { $set: object },
    { upsert: true },
    function(err, value) { 
      if(err) { console.log('update_obj(): throw error.'); throw err }
    }
  );
}



function update_obj(sid, object) {

  object.author  = sanitizeHtml(object.author, title_allow); 
  object.title   = sanitizeHtml(object.title, title_allow);
  object.content = sanitizeHtml(object.content, content_allow);

  //console.log('update obj()');

  var p_stkr = sticker_get(sid);

  Sticker.update(
    {'sid': sid},
    { $set: object },
    {	upsert: true },
    function(err, value) { 
      if(err) { console.log('update_obj(): throw error.'); throw err }
      if(p_stkr) {
        p_stkr.then(function(stkr) {
          generate.sticker_asset(stkr, ('public318' in stkr) ? stkr.public318 : false);
        });
      }
    }
  );
}


function update_content(sid, new_content) {
  var safe_new_content = sanitizeHtml(new_content, content_allow);

  Sticker.update(
    {'sid': sid},
    { $set: { 'content': safe_new_content } },
    {	upsert: true },
    function(err, value) { if(err) console.log('update_content(): ' + err); }
  );
}

function update_title(sid, new_title) {
  var safe_new_title = sanitizeHtml(new_title, title_allow);

  Sticker.update(
    {'sid': sid},
    { $set: { 'title': safe_new_title } },
    {	upsert: true },
    function(err, value) { if(err) console.log('update_title(): ' + err); }
  );
}

function update_pic(sid, new_url) {
  Sticker.update(
    {'sid': sid },
    { $set: { 'pic_url': new_url } },
    {	upsert: true },
    function(err, value) { if(err) console.log('update_pic(): ' + err); }
  );
}

return {
  'new' : sticker_new,
  'del' : sticker_del,
  'get' : sticker_get,
  'all' : sticker_all,
  'random' : sticker_random,
  'update_content' : update_content,
  'update_title'   : update_title,
  'update_pic'     : update_pic,
  'update_obj'     : update_obj,
  'update_obj_only': update_obj_field,
  'test' : last_id
};

})();
