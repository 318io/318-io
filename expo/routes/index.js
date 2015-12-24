var _       = require('underscore');
var express = require('express');
var router  = express.Router();

var Sticker  = require('../models/sticker.js');
var Position = require('../models/position.js');
var Config   = require('../models/config.js');
//var path    = require('path');
//var JSON3 = require('json3');


/**
 * Returns a random integer between min (inclusive) and max (inclusive)
 * Using Math.round() will give you a non-uniform distribution!
 * from : https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/random
 */
function random(min, max) {
   return Math.floor(Math.random() * (max - min + 1)) + min;
}

function removeArrayItem(arr, idx) {
  return _.reduce(arr, function(acc, item, i) {
    if(i != idx) return acc.concat([item]);
    else         return acc;
  }, []);
}

function copySticker(obj) {

  var new_obj = {
    sid: obj.sid,
    kind: obj.kind,
    open: false
  };

  // dynamically calculate the public318 picuture size for display
  if(obj.kind == 2 && obj.public318 == true) {
    new_obj.img_width  = 500;
    new_obj.img_height = Math.floor((obj.public318_height * 500)/obj.public318_width);
  } else if(obj.kind == 2) {
    new_obj.img_width = 500;
    new_obj.img_height = 500;
  }

  if('title' in obj) new_obj.title = obj.title;
  if('author' in obj) new_obj.author = obj.author;
  if('content' in obj) new_obj.content = obj.content;
  if('pic_url' in obj) new_obj.pic_url = obj.pic_url;
  if('target_url' in obj) new_obj.target_url = obj.target_url;
  if('video_url' in obj) new_obj.video_url = obj.video_url;

  // public318 is not used for rendering
  //if('public318' in obj) new_obj.public318 = obj.public318;
  //if('public318_id' in obj) new_obj.public318_id = obj.public318_id;
  //if('public318_width' in obj) new_obj.public318_width = obj.public318_width;
  //if('public318_height' in obj) new_obj.public318_height = obj.public318_height;

  return new_obj;
}

function assignPosition(sticker, pos) {
  sticker.pos_x = pos.left;
  sticker.pos_y = pos.top;
  sticker.width = pos.right - pos.left;
  sticker.height = pos.bottom - pos.top;
  sticker.pos_id = pos.id;
}


function main_map(pop_sticker, res) {

  var p_random_stickers;
  var has_pop_out = false;

  if(pop_sticker == (undefined || null)) {
    p_random_stickers = Sticker.random(77);
  } else {
    p_random_stickers = Sticker.random(76, { sid: { '$ne' : pop_sticker.sid }});
    has_pop_out = true;
  }

  var position_db = Position.copy(); // position object array
  
  var pick               = random(0, 77);
  var sunflower_position = position_db[pick];
  var position_db = removeArrayItem(position_db, pick); // remove the reserved position   

  if(has_pop_out) {
    pick = random(0, 76);
    var pop_position = position_db[pick];
    var position_db = removeArrayItem(position_db, pick); // remove the poped sticker postion    
  }

  p_random_stickers.then( function(sticker_array) {
    
    var new_sticker_array = sticker_array.map(function(sticker) {

      var nsticker = copySticker(sticker);

      // assign position
      var pos = position_db.shift(); // get one position
      if(pos) { assignPosition(nsticker, pos); }

      return nsticker;
    });

    // TODO: assing sunflower position 
    var sunflower = {
      sid: 0,
      title: '太陽花、香蕉與太陽餅',
      content: '<p>18公民運動有一些「花絮」，「太陽花」算其中之一。因為三、四月之交正值向日葵產季，有中南部的花農為表達支持之意，送來的大批的「太陽花」，於是「太陽花學運」的名稱，不脛而走。</p><p>妙的是，名嘴邱毅居然有一天指著立法院議場發言席上的太陽花，說他「查證過了」，那堆黃黃的東西是民進黨送的香蕉，所以民進黨是此次社運的幕後主使者。從那天起，整個夏天，都有人到菜市場買太陽花，老闆就會給你香蕉。</p><p>與邱毅並稱哼哈二將的是蕭家淇，是在「進攻行政院」的行動中，有某家媒體報導進入行政院主建築的學生中有人把在其中辦公者桌上的零食偷吃掉，作為這些人是「暴民」的表徵，蕭家淇加碼爆料他的「太陽餅」也不見了。</p><p>經過媒體的報導以後，「暴民」們的「暴力度」瞬間暴減，他們的暴力也不過就是把人家桌上的太陽餅吃了。其後就有很多人開始揶揄蕭家淇，其中有一位公民買了150盒同樣品牌的太陽餅送給蕭家淇，被婉拒了，這些太陽餅就被送往立法院抗議會場，分送給在場的群眾。</p><p>不過，幾天後，淇淇消失的太陽餅被找到了，不知道有沒有過期（到底藏在哪裡呢？好擔心，好可惜）？</p>',
      kind: 1, // story      
    }
    assignPosition(sunflower, sunflower_position);    
    new_sticker_array.push(sunflower);

    if(has_pop_out) {
      var new_pop = copySticker(pop_sticker);
      assignPosition(new_pop, pop_position);
      new_pop.open = true;
      new_sticker_array.push(new_pop);
    }

    res.render('boot', {'stickers': new_sticker_array, 'pop_doc': false })
  });  
} 

function main_map_with_doc(doc_id, res) {

  var p_random_stickers = Sticker.random(77);
  var position_db = Position.copy(); // position object array
    
  var pick               = random(0, 77);
  var sunflower_position = position_db[pick];
  var position_db = removeArrayItem(position_db, pick); // remove the reserved position   

  p_random_stickers.then( function(sticker_array) {
      
    var new_sticker_array = sticker_array.map(function(sticker) {
      var nsticker = copySticker(sticker);
      // assign position
      var pos = position_db.shift(); // get one position
      if(pos) { assignPosition(nsticker, pos); }
      return nsticker;
    });

    // TODO: assing sunflower position 
    var sunflower = {
      sid: 0,
      title: '太陽花、香蕉與太陽餅',
      content: '<p>18公民運動有一些「花絮」，「太陽花」算其中之一。因為三、四月之交正值向日葵產季，有中南部的花農為表達支持之意，送來的大批的「太陽花」，於是「太陽花學運」的名稱，不脛而走。</p><p>妙的是，名嘴邱毅居然有一天指著立法院議場發言席上的太陽花，說他「查證過了」，那堆黃黃的東西是民進黨送的香蕉，所以民進黨是此次社運的幕後主使者。從那天起，整個夏天，都有人到菜市場買太陽花，老闆就會給你香蕉。</p><p>與邱毅並稱哼哈二將的是蕭家淇，是在「進攻行政院」的行動中，有某家媒體報導進入行政院主建築的學生中有人把在其中辦公者桌上的零食偷吃掉，作為這些人是「暴民」的表徵，蕭家淇加碼爆料他的「太陽餅」也不見了。</p><p>經過媒體的報導以後，「暴民」們的「暴力度」瞬間暴減，他們的暴力也不過就是把人家桌上的太陽餅吃了。其後就有很多人開始揶揄蕭家淇，其中有一位公民買了150盒同樣品牌的太陽餅送給蕭家淇，被婉拒了，這些太陽餅就被送往立法院抗議會場，分送給在場的群眾。</p><p>不過，幾天後，淇淇消失的太陽餅被找到了，不知道有沒有過期（到底藏在哪裡呢？好擔心，好可惜）？</p>',
      kind: 1, // story      
    }
    assignPosition(sunflower, sunflower_position);
      
    new_sticker_array.push(sunflower);

    res.render('boot', {'stickers': new_sticker_array , 'pop_doc': true, 'doc_num': doc_id});
  });  
}

router.get('/doc_1', function(req, res, next) {    
  main_map_with_doc(1, res);
});

router.get('/doc_2', function(req, res, next) {    
  main_map_with_doc(2, res);
});

router.get('/doc_3', function(req, res, next) {
  main_map_with_doc(3, res);
});

/* GET home page. */
router.get('/:id?', function(req, res, next) {

  var sid    = req.params.id;

  if(/^\d+$/.test(sid)) {
    var p_stkr = Sticker.get(sid);

    if(p_stkr) {
      p_stkr.then(function(stkr) {
        console.log('opening: ' + stkr.sid); 
        main_map(stkr, res);
      });
    } else {
      main_map(null, res);
    }    
  } else {
    main_map(null, res);
  }
});

function safe(s) {
  var _s = JSON.stringify(s);
  return _s.slice(1, _s.length-1);
}

function normalize_for_editing(obj) {
  if('title' in obj) obj.title = safe(_.unescape(obj.title));
  else               obj.title = '';

  if('author' in obj) obj.author = safe(_.unescape(obj.author));
  else                obj.author = '';

  if('content' in obj) obj.content = safe(_.unescape(obj.content));
  else                 obj.content = '';

  if(!'pic_url' in obj)    obj.pic_url = '';
  if(!'target_url' in obj) obj.target_url = '';
  
  return obj;
}

router.get('/edit/:id', function(req, res, next) {

  if(!Config.internal) return res.redirect('/');

  var sid = req.params.id;
  var p_stkr = Sticker.get(sid);

  p_stkr.then(function(stkr) {

    normalize_for_editing(stkr);
    
    switch(stkr.kind) {
      case 0: // sticker
        stkr.content = stkr.content.replace(/\r?\n|\r/g, ' ');
        res.render('sticker_edit', {'sticker': stkr });
        break;
      case 1: // story
        stkr.content = stkr.content.replace(/\r?\n|\r/g, '');
        //console.log(stkr.content);
        res.render('story_edit', {'sticker': stkr });
        break;
      case 2: // picture
        res.render('picture_edit', {'sticker': stkr });
        break;
      default: // no sticker find
        console.log('??' + stkr.kind);
        res.sendStatus(400);
    }
  });
});


router.get('/foo/:id', function(req, res, next) {
  if(!Config.internal) return res.redirect('/');
  var id = req.params.id;
  console.log(id);
  res.render('foo');
});


/*
router.get('/map', function(req, res, next) {

  var p_all_stickers = Sticker.all();
  var _mapdb = mapdb.slice(); // clone

  p_all_stickers.then( function(sticker_array) {

    var new_sticker_array = sticker_array.map(function(sticker) {
      var nsticker = copySticker(sticker);

      // picture content
      if(nsticker.kind == 2) {
        nsticker.content = '<a href=\"' + nsticker.pic_url + '\"><img src=\"' + nsticker.pic_url + '\"/>' + '</a>';
      }

      // assign position
      var pos = _mapdb.shift(); // get one position
      if(pos) {
        nsticker.pos_x = pos.left;
        nsticker.pos_y = pos.top;
        nsticker.width = pos.right - pos.left;
        nsticker.height = pos.bottom - pos.top;
      }
      return nsticker;
    });
    res.render('map2', {'stickers': new_sticker_array })
  });

  //res.render('index');
});

*/

/*
router.get('/map/:id', function(req, res, next) {
  var map_id = req.params.id;

  var p_all_stickers = Sticker.all();

  p_all_stickers.then(function(sticker_array) {
    res.render('map', {'map_id': map_id, 'stickers': sticker_array })
  });

  //res.render('map', { id: map_id  });
});
*/

module.exports = router;

/*
var mapdb = [
  { left: 319, top: 10, right: 394, bottom: 86 },    // 1
  { left: 295, top: 56, right: 331, bottom: 91 },    // 2
  { left: 378, top: 46, right: 414, bottom: 82 },    // 3
  { left: 218, top: 79, right: 291, bottom: 116 },   // 4
  { left: 295, top: 95, right: 332, bottom: 171 },   // 5
  { left: 336, top: 75, right: 372, bottom: 107 },   // 6
  { left: 336, top: 110, right: 372, bottom: 148 },  // 7
  { left: 377, top: 87, right: 429, bottom: 137 },   // 8
  { left: 197, top: 119, right: 235, bottom: 155 },  // 9
  { left: 239, top: 120, right: 291, bottom: 171 },  // 10
  { left: 336, top: 150, right: 373, bottom: 185 },  // 11
  { left: 379, top: 144, right: 429, bottom: 193 },  // 12
  { left: 167, top: 156, right: 250, bottom: 250 },  // 13
  { left: 258, top: 176, right: 332, bottom: 211 },  // 14
  { left: 336, top: 191, right: 373, bottom: 228 },  // 15
  { left: 379, top: 197, right: 439, bottom: 233 },  // 16
  { left: 130, top: 208, right: 167, bottom: 245 },  // 17
  { left: 254, top: 216, right: 292, bottom: 250 },  // 18
  { left: 295, top: 216, right: 331, bottom: 252 },  // 19
  { left: 335, top: 231, right: 373, bottom: 269 },  // 20
  { left: 376, top: 237, right: 413, bottom: 273 },  // 21
  { left: 117, top: 249, right: 153, bottom: 286 },  // 22
  { left: 158, top: 256, right: 232, bottom: 291 },  // 23
  { left: 237, top: 256, right: 276, bottom: 292 },  // 24
  { left: 279, top: 256, right: 332, bottom: 308 },  // 25
  { left: 335, top: 272, right: 371, bottom: 309 },  // 26
  { left: 375, top: 276, right: 417, bottom: 326 },  // 27
  { left: 101, top: 290, right: 153, bottom: 340 },  // 28
  { left: 158, top: 297, right: 195, bottom: 332 },  // 29
  { left: 200, top: 296, right: 274, bottom: 333 },  // 30
  { left: 278, top: 311, right: 316, bottom: 348 },  // 31
  { left: 320, top: 313, right: 381, bottom: 373 },  // 32
  { left: 60, top: 338, right: 99, bottom: 374 },    // 33
  { left: 98, top: 345, right: 168, bottom: 412 },   // 34
  { left: 160, top: 338, right: 233, bottom: 374 },  // 35
  { left: 236, top: 338, right: 274, bottom: 374 },  // 36
  { left: 279, top: 352, right: 316, bottom: 389},   // 37
  { left: 33, top: 379, right: 96, bottom: 414 },    // 38
  { left: 180, top: 378, right: 218, bottom: 469 },  // 39
  { left: 221, top: 378, right: 274, bottom: 429 },  // 40
  { left: 278, top: 394, right: 318, bottom: 429 },  // 41
  { left: 320, top: 377, right: 373, bottom: 429 },  // 42
  { left: 22, top: 419, right: 60, bottom: 455 },    // 43
  { left: 63, top: 419, right: 100, bottom: 456 },   // 44
  { left: 102, top: 418, right: 176, bottom: 455 },  // 45
  { left: 222, top: 436, right: 260, bottom: 470 },  // 46
  { left: 263, top: 434, right: 301, bottom: 470 },  // 47
  { left: 303, top: 433, right: 364, bottom: 470 },  // 48
  { left: 36, top: 459, right: 104, bottom: 496 },   // 49
  { left: 108, top: 459, right: 144, bottom: 495 },  // 50
  { left: 148, top: 459, right: 185, bottom: 494 },  // 51
  { left: 193, top: 474, right: 269, bottom: 511 },  // 52
  { left: 273, top: 475, right: 310, bottom: 511 },  // 53
  { left: 313, top: 475, right: 352, bottom: 511 },  // 54
  { left: 25, top: 499, right: 62, bottom: 535 },    // 55
  { left: 65, top: 499, right: 101, bottom: 534 },   // 56
  { left: 112, top: 500, right: 193, bottom: 590 },  // 57
  { left: 196, top: 516, right: 236, bottom: 550 },  // 58
  { left: 237, top: 517, right: 275, bottom: 550 },  // 59
  { left: 278, top: 517, right: 347, bottom: 551 },  // 60
  { left: 24, top: 540, right: 62, bottom: 590 },    // 61
  { left: 65, top: 540, right: 118, bottom: 591 },   // 62
  { left: 200, top: 555, right: 280, bottom: 590 },  // 63
  { left: 283, top: 555, right: 320, bottom: 592 },  // 64
  { left: 58, top: 594, right: 96, bottom: 630 },    // 65
  { left: 99, top: 595, right: 137, bottom: 630 },   // 66
  { left: 138, top: 593, right: 220, bottom: 630 },  // 67
  { left: 223, top: 594, right: 260, bottom: 630 },  // 68
  { left: 263, top: 595, right: 299, bottom: 631 },  // 69
  { left: 86, top: 636, right: 124, bottom: 671 },   // 70
  { left: 127, top: 636, right: 164, bottom: 686 },  // 71
  { left: 167, top: 636, right: 220, bottom: 686 },  // 72
  { left: 223, top: 636, right: 286, bottom: 672 },  // 73
  { left: 141, top: 690, right: 180, bottom: 727 },  // 74
  { left: 183, top: 690, right: 220, bottom: 725 },  // 75
  { left: 223, top: 675, right: 260, bottom: 726 },  // 76
  { left: 179, top: 729, right: 243, bottom: 766 },  // 77
  { left: 202, top: 770, right: 242, bottom: 803 }   // 78
];
*/