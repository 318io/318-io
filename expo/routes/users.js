var express = require('express');
var router = express.Router();
var Config = require('../models/config.js');

/* GET users listing. */
router.get('/', function(req, res, next) {
  if(!Config.internal) return res.redirect('/');
  console.log('here');
  res.send('respond with a resource');
});

module.exports = router;
