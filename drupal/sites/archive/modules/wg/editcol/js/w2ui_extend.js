$(function extend_form() {
  
  function model_add_field_next_to(form, pre_field_name, new_field_obj) {
    var fields = w2ui[form].fields;

    w2ui[form].fields = fields.reduce(function(acc, item){
      acc.push(item);
      if(item.name == pre_field_name) acc.push(new_field_obj);
      return acc;
    }, []);

    w2ui[form].resize();
  }

  function model_get_field(form, name) {
    var fields = w2ui[form].fields;

    // should modify for efficience
    return fields.reduce(function(acc, item) {
      if(item.name == name) return $.extend(true, {}, item); // deep copy, clone
      else return acc;
    }, {});
  }

  function model_rename_field(form, old_name, new_name) {
    var fields = w2ui[form].fields;
    w2ui[form].fields = fields.map(function(item) {
      if(item.name == old_name) { item.name = new_name; item.field = new_name }
      return item;
    });
    //w2ui[form].refresh();
  }

  function model_remove_field(form, name) {
    var fields = w2ui[form].fields;
    w2ui[form].fields = fields.reduce(function(acc, item){
      if(item.name != name) acc.push(item);
      return acc;
    }, []);
    delete w2ui[form].record[name];
    w2ui[form].resize();
  }

  //var pattern = new RegExp("([a-zA-Z0-9_\-]+)\\[([0-9]+)\\]");  // bug in browser ??? why ???
  function extract_name_order_number(name) {
    var pattern = /([a-zA-Z0-9_\-]+)\[([0-9]+)\]/;
    return pattern.exec(name); // return [ 'blabla[0]', 'blabla', '0', index: 0, input: 'blabla[0]' ] or null
  }

  function _save_counter(form, key, count) {
    if(w2ui[form].counter == undefined) w2ui[form].counter = [];
    w2ui[form].counter[key] = count;
  }

  function _get_counter(form, key) {
    return w2ui[form].counter[key];
  }

  w2obj.form.prototype.enable_multiple = function() {

    var form_name = this.name;

    // rename all multiple field
    $('.multiple_field').each(function(index) {
      var old_name = $(this).attr('name');
      var new_name = old_name + "[0]";
      $(this).attr('name', new_name);  // !!! NOTE: view first, then model
      _save_counter(form_name, old_name, 1);
      model_rename_field(form_name, old_name, new_name);
    });
     
    // attach control to multiple fields
    var wrappers = $('.multiple_field').parent('div');

    wrappers.each(function(index) {
      $.data($(this), 'count', 1); // set counter
    });

    wrappers.append('<a href="#" class="add_field" style="height:24px;width:32px;display:block;float:right"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAApgAAAKYB3X3/OAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAILSURBVEiJpdZPiM1RFAfwz31jjJQ/WUixmSaxEfIiGwtlaYEsBgspZWFK2VA2KAsLNjayUBZipSY1FsJKaBQSKzWGxEimZibTjFyL93vmzp3f+5dTp37vnO/5nnvP7/6+94UYo1YWQgjoQxUzGI4xjrYshBhjqaMHZ/EQPxEzH8MQBtDVkKcB+Ta8LSFt5M+wsWUDdOMiZjsgr/svnEZo1uBSSeEnXMVhbMAmHMU1fC/BD5Q2wA78zsA3sTzBLMWi5Pdq3MtqJtE3rwGW4H0G7M92tx/jGMX2LHcqq31SH1UdcD4D3Ch58S+S/O2S/GDGcSxt8DxJfMSyEoKRdIUl+TX4kWDuxhhVQgjd2GzO7sQYJ3RoMcavuJ+EqvWHrdnWDhYrOoBHeFX4jPkvsh4fwu6iZiDjWgXHs2AvVmIqizfzz+jCziy+p1Ky21DfdQcTmsafpPafVTCcxaoxxnEcUhvR68JnE8xkEh/EkVibUTXjeklNHqbNbetyA30a0eQUFZhbCeZDjFElxjhbrKRu/SGEFW0Pp7AQwlrsTULD1EYED5LEOlwp4fiWPI+V5K+rHY661TgTqXinuVTsU/uQvmBXC6l4LJWKAlS1UKZzsevRWuwm0NtIri9YeMY7lesTze6DLpwz/6tt16dwst0rcwvedED+FOvbvpOLJotxxn9e+qEga2r/87flL5gUmG+NYE7/AAAAAElFTkSuQmCC9d1364a52db9c4a6a530fba4ce6f9019"/></a>');

    wrappers.on("click",".add_field", function(e) {
        
        e.preventDefault(); 

        //var current_input = $(this).parent('div').children('input');
        var current_input = $(this).parent('div').children();
        var key = extract_name_order_number(current_input.attr('name'))[1];
        var max_num = parseInt(current_input.attr('max_num'));
        var counter = _get_counter(form_name, key);

        if(counter > max_num-1) return undefined; // do nothing

        var n_field_wrapper = $(this).parent('div').clone(true);  // deep clone, the callbacks are cloned as well.
        var new_field = n_field_wrapper.children();        
        //var new_field = n_field_wrapper.children('input');
        
        // remove id if exists
        new_field.removeAttr('id');

        // reset old content
        new_field.val("");            

        // change the name
        var old_name = new_field.attr('name');
        var order = extract_name_order_number(old_name);
        if(order != null) {
          var new_order = parseInt(order[2]) + 1;
          new_field.attr('name', order[1] + '[' + new_order +']');
        }

        var field_name = new_field.attr('name');
        var field_obj  = model_get_field(form_name, old_name); // use old name to get a copy of old field object.

        field_obj.name = field_name; // change model object to the new name

        $(this).parent('div').after(n_field_wrapper); // append after this (div)

        model_add_field_next_to(form_name, old_name, field_obj); // add to model

        //$(this).removeClass('add_field').addClass('remove_field').text('[-]').on('click', function(e) { 
        $(this).removeClass('add_field').addClass('remove_field').empty().append('<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAApgAAAKYB3X3/OAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAHuSURBVEiJrdRPiI1RGAbw35k7jAXJNNRY2dgwU/7GzoqSEmOFlYWFkAULK/kzxYospmykSJpkg5WNjUkiUxIWItKEhUQNojkW996ce+53v3sv3no357zP83zveZ/vDTFGnUQIYRCrMI3JGOPnjoAxxsLEbBzBbUwhJjmDlxjHzlYcMcZiAazEk4y0LG9hsK0AenEcP7sgr+cn7GoncKYA+A5j2INhrMN+XMLXgvpthQJYj19Z8UXML5nTEtzNMO8x0CCAOXiRDXGkbHiJSMDpTGQ8FziRFYx1Qp6IVPAg49icCqSXrzC3G4EaxzJ8T3jO1/+xWdnFyW7JE5E7Cc9EjFEvhtDnTzyCEMJWHMSA8niL0Rjjwxp2Y+18RQihAnuzt1uMeYot2Conax2MZOfDPS2+qqLqjn+O3lpbaayJMd4MIezGIfS34XiD0To2OZ/Gs7rIN/9/yPdSm95PLl77O5su1+jGc6nAMY3DudAlef2pU45NqUAfnmYFTZuxBXkPzmbYK0XLbrXmNX0V/SXkSzGRYaawoEmgBjiVFUd8UN2q+7AWG3AY12pOyeu3NHAWLK2j+FEAbJcfsaOpyxatDxUMrSyvY2EhVxtnHMAN1X2TEs7gOS5je5kJQn2ltosQwiLVP3Uaj2OMXzrB/QbuLz7MV8zDvwAAAABJRU5ErkJgggcd8cd3a0b1dbaf267ccf70073c7af446"/>').on('click', function(e) { 
          var field_name = $(this).parent('div').children('input').attr('name');
          //alert(field_name);
          $(this).parent('div').remove();
          model_remove_field(form_name, field_name); // remove model
          _save_counter(form_name, key, _get_counter(form_name, key)-1);
        });

        _save_counter(form_name, key, counter+1);

    });

    $('.multiple_field').each(function(index) {
      var _count = $(this).attr('init_num');
      var count  = parseInt(_count ? _count : 1);
      for(var i = 1; i < count; i++) $(this).parent('div').parent('div').find('.add_field').trigger('click');
    }); 
  }  
});

/*
w2obj.form.prototype.hello = function() { alert('hello world'); }
w2ui['form'].fields.push({field: 'name[]', type: 'text', required: true, html: { caption: "[2]", attr: 'style="width:300px"'}});
w2ui['form'].formHTML = w2ui['form'].generateHTML();
w2ui['form'].render();
w2ui['form'].hello();
*/
