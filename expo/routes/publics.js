var express  = require('express');
var router   = express.Router();
var Sticker  = require('../models/sticker.js');
var Config   = require('../models/config.js');
var request  = require('request');
var JSON3    = require('json3');
var fs       = require('fs');

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

function isVideo(url) {
  var extension = url.split('/').pop().split('.').pop().toLowerCase();
  var format = {
    'webm' : '',
    'ogg': '',
    'ogv' : '',
    'mp4' : '',
    'wmv' : ''
  }
  return (extension in format ) ? true : false;
}


function getFileName(url) {
  return url.split('/').pop();
}

// GET pictures/add
router.get('/add', function(req, res, next) {
  if(!Config.internal) return res.redirect('/');
  res.render('public_add', {title: 'Add Collection'});
});


// POST publics/add
// get json object of sticker, add it to database
router.post('/add', function(req, res, next) {

  if(!Config.internal) return res.redirect('/');

  if(req.is('application/json')) { // ref http://expressjs.com/api.html#request

    // console.log(req.body.title); // the bodyParser.json(), parse the json
    try{
      var col = req.body.col;
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
            Sticker.new(obj);
          } else if(isVideo(meta.file[0])) {
            var pic_file_name = getFileName(meta.file[1]);
            var title = '', author = '', content = '';
            if('license_note' in meta.metadata) author  = meta.metadata.license_note[0];
            if('description' in meta.metadata)  content = meta.metadata.description[0];
            if('content' in meta.metadata)      content = meta.metadata.content[0];
            var obj = {
              'kind': 3,
              'title': title,
              'author': author,
              'content': content,
              'video_url': meta.file[0],
              'pic_url': meta.file[1],
              'target_url' : target,
              'public318': true,
              'public318_id': parseInt(col),
              'public318_width': meta.metadata.mediainfo[pic_file_name].width,
              'public318_height': meta.metadata.mediainfo[pic_file_name].height
            }
            Sticker.new(obj);
          }
        } else {
          res.status(400).json({ 'error': error });
        }
      });
      //Sticker.new(req.body);
    } catch(e) {
      res.status(400).json({ error: e.message });
    }
    res.send({ status: true, message: 'success'}); // send json object back
  } else {
    res.sendStatus(400);
  }
});

// POST publics/edit
// get json object of sticker, add it to database
router.post('/edit', function(req, res, next) {

  if(!Config.internal) return res.redirect('/');

  if(req.is('application/json')) { // ref http://expressjs.com/api.html#request

    // console.log(req.body.title); // the bodyParser.json(), parse the json
    try{
      console.log(req.body.content);
      Sticker.update_obj(req.body.sid, req.body);
    } catch(e) {
      res.status(400).json({ error: e.message });
    }

    res.send({ status: true, message: 'success'}); // send json object back
  } else {
    res.sendStatus(400);
  }
});


module.exports = router;
