$(function () {
  $('#content').w2form({
    name  : 'content',
    url   : 'server/post',
    fields: [
      { field: 'first_name', type: 'text', required: true },
      { field: 'last_name',  type: 'text', required: true },
      { field: 'comment',    type: 'textarea'},
      { field: 'master',     type: 'toggle'},
      { field: 'slave',      type: 'toggle'}
    ],
    actions: {
      reset: function () {
        this.clear();
      },
      save: function () {
        //this.save();
        console.log(this.record);
        var html = $('#comment').htmlarea('html');
        console.log(html);
      }
    },

    onChange: function (event) {
      if(event.target == 'master') {
        event.onComplete = function () {
          console.log(this.record);
          this.record['slave'] = 1;
          this.refresh();
        }
      }
      //console.log(event);
    }
  });

  $('#comment').htmlarea({
    toolbar: [
    "html",
    "|",
    "bold", "italic", "underline", "strikethrough",
    "|",
    "h1", "h2", "h3", "h4", "h5", "h6",
    "|",
    "image", "link",
    "|",
    "orderedList", "unorderedList"
    ]
  });
});
