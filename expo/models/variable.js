module.exports = (function() {

var mongoose = require('mongoose');

var Schema = mongoose.Schema;

var variableSchema = new mongoose.Schema({
  name:  { type: String , unique: true, required: true }, // index later
  value: { type: Schema.Types.Mixed , required: true }
});

var Variable = mongoose.model('Variable', variableSchema);


function variable_set(name, value) {

  Variable.update(
    {'name': name},
    { $set: { 'value': value } },
    {	upsert: true },
    function(err, value) { if(err) console.log('variable_set(): ' + err); }
  );

}

function test1(name, default_value) {

  var p = Variable.find({'name': name})
  .select('value')
  .exec(function(err, obj){
    console.log(err);   // 找不到不代表是 error
    console.log(obj);
  });
}

function test2(name) {

  var p = Variable.findOne({'name': name}).exec();

  p.then(function(data){
    console.log('test2');
    console.log(data);
  });
}


function variable_get(name, default_value) {   // use findOne ?
  var p = Variable.find({'name': name})
                  .select('value')
                  .exec(); // get a promise

  return p.then(function(data){
    if(data.length === 0) return default_value;
    else                  return data[0].value;
  }, function(error){
    return default_value;
  });
}

function variable_del(name) {

  Variable.findOne({ 'name': name}, function(err, v) {
    if(!err) {
      v.remove(function(err) {
        if(err) console.log('variable_del():' + err);
      });
    } else {
      console.log('variable_del():' + err);
    }
  });
}

return {
  'set' : variable_set,
  'get' : variable_get,
  'del' : variable_del,
  'test': test2
};

})();
