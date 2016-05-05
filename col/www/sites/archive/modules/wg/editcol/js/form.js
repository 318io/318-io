

$(function () {

    $('#form').w2form({ 
        name   : 'form',
        header : 'Collection Form',
        url    : '/json/foo',
        fields : [
            { name: 'first_name', type: 'text', required: true, html: { caption: 'First Name', attr: 'class="multiple_field" init_num="1" style="width: 300px"' } },
            { name: 'last_name',  type: 'text', required: false, html: { caption: 'Last Name', attr: 'class="multiple_field" max_num="999" style="width: 300px"' } },
            { name: 'age',  type: 'text', required: false, html: { caption: 'Age', attr: 'style="width: 300px" disabled' } },
            { name: 'select', type: 'toggle', required: false },
            { name: 'comments',   type: 'textarea', html: { caption: 'Comments', attr: 'style="width: 300px; height: 90px"' } },
            { name: 'upload', type: 'file', required: false },
            { name: 'taxon', type: 'enum', required: false, 
              options: { 
                items: ['hello', {id: 1, text:'world'}],
                openOnFocus: true,        
                markSearch: true,
                max: 1,
                onNew: function (event) { event.item.style = 'background-color: rgb(255, 232, 232); border: 1px solid red;';}
              },
              html: { caption: 'Taxonomy' }
            }
        ],
        record: {
          'first_name[0]': 'Harry',
          'last_name[0]': 'Chang',
          'age' : '18',
          'select' : 1,
        },
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
    });

    w2ui['form'].enable_multiple();


    //field = w2ui['form'].get('first_name');
    //alert(w2ui['form'].formHTML);
});
