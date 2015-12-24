var express = require('express');
var router  = express.Router();
var Sticker = require('../models/sticker.js');
var Config  = require('../models/config.js');
var JSON3   = require('json3');

// GET storys/add
router.get('/add', function(req, res, next) {
  if(!Config.internal) res.redirect('/');
  res.render('story_add', {title: 'Add Story'});
});

// POST storys/add
// get json object of sticker, add it to database
router.post('/add', function(req, res, next) {

  if(!Config.internal) return res.redirect('/');

  if(req.is('application/json')) { // ref http://expressjs.com/api.html#request

    // console.log(req.body.title); // the bodyParser.json(), parse the json
    try{
      //console.log(req.body.content);
      Sticker.new(req.body);
    } catch(e) {
      res.status(400).json({ error: e.message });
    }

    res.send({ status: true, message: 'success'}); // send json object back
  } else {
    res.sendStatus(400);
  }
});

// POST storys/add
// get json object of sticker, add it to database
router.post('/edit', function(req, res, next) {

  if(!Config.internal) return res.redirect('/');

  if(req.is('application/json')) { // ref http://expressjs.com/api.html#request

    // console.log(req.body.title); // the bodyParser.json(), parse the json
    try{
      //console.log(req.body.content);
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
