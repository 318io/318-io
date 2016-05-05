(function ($) {
  "use strict";
  Drupal.AjaxCommands.prototype.colorboxLoadOpen = function (ajax, response) {
    $.colorbox({
      html: response.data,
      width: '90%',
      height: '90%'
    });
  };
})(jQuery);
