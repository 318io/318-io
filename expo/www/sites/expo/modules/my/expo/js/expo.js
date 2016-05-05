(function($) {
  $.myvar = new Object();

  Drupal.behaviors.expo = {
    attach: function(context, settings) {
      $.collItemEditStart();

      $('.ajaximport').each(function() {
        var el = $(this);
        var url = el.data('url');
        $.get(url).done(function(data) {
          el.parent().html(data);
          $.collItemEditStart();
          $.myvar.collitems_sortable = $("#collitems ul").sortable();
        });

      })

      $('.public318search', context).once('public318search_behavior').click(function() {
        var el = $(this);
        return $.public318search(el);
      });

      $('.public318add', context).once('public318add_behavior').click(function() {
        var el = $(this);
        return $.public318add(el);
      });

      $.expoeditformSubmit();
      $('.public318search', context).click();

    }


  };

  $.expoeditformSubmit = function() {
    $('form#expo-edit-form').submit(function(e) {
      //e.preventDefault();
      var v = [];
      $('#collitems ul li').each(function() {
        var el = $(this);
        v.push({
          'target': $('.thetarget', el).val(),
          'annotation': $('.theannotation', el).val(),
        })
      })

      var s = JSON.stringify(v);
      $('#edit-collitems-data').val(s);
      return true;
    })

  }

  $.collItemEditStart = function() {
    var el = $('#collitems ul li');
    if (el.length > 0) $('.collitem-edit').show();
    $('.collitem-edit').once('edit_behavior').click(
      function(e) {
        e.preventDefault();
        $('#collitems ul li').addClass('editing');
        if($.myvar.hasOwnProperty('collitems_sortable')){
          $.myvar.collitems_sortable.sortable("disable");
        }
        $('.collitem-edit').hide();
        $('.collitem-edit-fin').show();
        return false;
      }
    );

    $('.collitem-remove').once('remove_behavior').click(
      function(e) {
        e.preventDefault();

        var el = $(this);
        var index = el.data('index');

        $('#collitem-index-' + index).parent().remove();

      }
    )

    $('.collitem-edit-fin').once('editfin_behavior').click($.collitemEditFin);
  }

  $.collitemEditFin = function() {
    $('#collitems ul li').removeClass('editing');
    $.myvar.collitems_sortable.sortable("enable");
    $('.collitem-edit').show();
    $('.collitem-edit-fin').hide();
    return false;

  }

  $.public318search = function(el) {
    var url = '/expo/add/public318search';
    var v = $('#edit-public318-searchtext').val();
    var wrapperId = '#search-result';
    $(wrapperId).html('<i class="fa fa-spinner fa-spin fa-5x"></i>');


    $.get(url, {
      "qs": v
    }).done(function(data) {
      $(wrapperId).html(data);
      $("select").imagepicker({
        show_label: true
      });
      $('select#theitems').once('theitems_behavior').change(function() {
        var el = $(this);
        var values = el.val();
        var s = JSON.stringify(values);
        $('#edit-public318-selected').val(s);
      });
    });

  }

  $.public318add = function(el) {
    var url = '/expo/add/public318add';
    var v = $('#edit-public318-selected').val();
    var wrapperId = '#collitems ul';
    $(wrapperId).append('<i id="add-spin" class="fa fa-spinner fa-spin fa-5x"></i>');

    $.get(url, {
      "ids": v
    }).done(function(data) {
      $('#add-spin').remove();
      if(data){
      $(wrapperId).append(data);
      $.myvar.collitems_sortable = $("#collitems ul").sortable();
      $.collitemEditFin();
      $.collItemEditStart();
    }
    });

  }

}(jQuery));
