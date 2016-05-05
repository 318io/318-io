

$(function () {
  var form_model = { 
        name   : 'form',
        header : 'Collection Form',
        url    : '/json/save/collection',
        fields : [],
        actions: {
            'Save': function (event) {
                //console.log('save', event);
                console.log(this.record);
                //console.log(this.recid);
                console.log(this.url);

                $.ajax({
                  type: "POST",
                  url: this.url,
                  data: JSON.stringify(this.record),
                  contentType: "application/json; charset=utf-8",
                  dataType: "json",
                  processData: true,
                  success: function (data, status, jqXHR) {
                    alert("success..." + data);
                  },
                  error: function (xhr) {
                    alert(xhr.responseText);
                  }
                });

                //this.save(); // this here is w2ui['form'] object
            },
            'Clear': function (event) {
                // console.log('clear', event);
                this.clear();
            },
        }
    };
  
  $.post('/json/get/form/model/collection', function(data) {
    var model = JSON.parse(data);
    form_model.fields = model;
    $('#form').w2form(form_model);  
    w2ui['form'].enable_multiple();
  });

  //field = w2ui['form'].get('first_name');
  //alert(w2ui['form'].formHTML);
});
