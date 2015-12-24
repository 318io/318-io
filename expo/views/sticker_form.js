$(function () {
  $('#sticker_form').w2form({
    name  : 'content',
    url   : 'server/post',
    fields: [
      { field: 'title',    type: 'text' },
      { field: 'content',  type: 'textarea'},
      { field: 'pic_url',  type: 'text'},
      { field: 'X', type: 'text'},
      { field: 'Y', type: 'text'},
      { field: 'W', type: 'text'},
      { field: 'H', type: 'text'}
    ],
    actions: {
      reset: function () {
        this.clear();
      },
      save: function () {
        //this.save();
        console.log(this.record);
        var html = $('#content').htmlarea('html');
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

  $('#content').htmlarea({
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