<?php
require_once('easier.tools.php');

function _form_new($form_model) {
  $relcol_partof = $form_model[8]['html']['attr'];
  $relcol_relatedto = $form_model[9]['html']['attr'];
  $digi_path = $form_model[27]['html']['attr'];

  $form = <<<FORM
      <a href="javascript: w2ui.form_new.goto(0);" style="padding-right: 10px">描述</a> |
      <a href="javascript: w2ui.form_new.goto(1);" style="padding: 10px">分類</a> |
      <a href="javascript: w2ui.form_new.goto(2);" style="padding: 10px">關係</a> |
      <a href="javascript: w2ui.form_new.goto(3);" style="padding: 10px">測量</a> |
      <a href="javascript: w2ui.form_new.goto(4);" style="padding: 10px">數化</a> |
      <a href="javascript: w2ui.form_new.goto(5);" style="padding: 10px">紀錄</a> |
      <a href="javascript: w2ui.form_new.goto(6);" style="padding: 10px">授權</a>

      <div style="height: 10px;"></div>

      <div id="form_new" style="width: 650px;">

          <div class="w2ui-page page-0">
              <div class="w2ui-field">
                  <label>識別號:</label>
                  <div>
                      <input name="field_identifier" type="text" maxlength="100" size="20" disabled/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>主要形式:</label>
                  <div>
                      <input name="field_mainformat" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>所屬事件:</label>
                  <div>
                      <input name="field_event" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>作者:</label>
                  <div>
                      <input name="field_creator" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>提供者:</label>
                  <div>
                      <input name="field_provider" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>取得者:</label>
                  <div>
                      <input name="field_collector" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>取得日期:</label>
                  <div>
                      <input name="field_collected_time" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>取得地點:</label>
                  <div>
                      <input name="field_collected_place" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>取得方式:</label>
                  <div>
                      <input name="field_collected_method" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>產製日期:</label>
                  <div>
                      <input name="field_created_time" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>產製地點:</label>
                  <div>
                      <input name="field_created_place" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>原件典藏者:</label>
                  <div>
                      <input name="field_repository" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>原件典藏編號:</label>
                  <div>
                      <input name="field_repository_id" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>原件典藏位置:</label>
                  <div>
                      <input name="field_repository_place" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>藏品狀況:</label>
                  <div>
                      <input name="field_condition" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>藏品狀況註記:</label>
                  <div>
                      <textarea name="field_condition_note" type="text" style="width: 100%; height: 80px; resize: none"></textarea>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>描述</label>
                  <div>
                      <textarea name="field_description" type="text" style="width: 100%; height: 80px; resize: none"></textarea>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>內容</label>
                  <div>
                      <textarea name="field_content" type="text" style="width: 100%; height: 80px; resize: none"></textarea>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>備註:</label>
                  <div>
                      <textarea name="field_note" type="text" style="width: 100%; height: 80px; resize: none"></textarea>
                  </div>
              </div>
          </div>
          <div class="w2ui-page page-1">
              <div class="w2ui-field">
                  <label>形式分類:</label>
                  <div>
                      <input name="field_format_category" type="text" maxlength="100" size="60"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>內容分類:</label>
                  <div>
                      <input name="field_content_category" type="text" maxlength="100" size="60"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>標籤:</label>
                  <div>
                      <input name="field_tagtag" type="text" maxlength="40" size="60"/>
                  </div>
              </div>
          </div>

          <div class="w2ui-page page-2">
              <div class="w2ui-field">
                  <label>關係藏品-整體:</label>
                  <div>
                      <input name="field_relcol_partof" type="text" maxlength="100" size="40" $relcol_partof/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>關係藏品-關聯:</label>
                  <div>
                      <input name="field_relcol_relatedto" type="text" maxlength="100" size="40" $relcol_relatedto/>
                  </div>
              </div>
          </div>

          <div class="w2ui-page page-3">
              <div class="w2ui-field">
                  <label>材質:</label>
                  <div>
                      <input name="field_material" type="text" maxlength="100" size="60"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>測量:</label>
                  <div>
                      <textarea name="field_measurement" type="text" style="width: 100%; height: 80px; resize: none"></textarea>
                  </div>
              </div>
          </div>

          <div class="w2ui-page page-4">
              <div class="w2ui-field">
                  <label>數位化方式:</label>
                  <div>
                      <input name="field_digi_method" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>數位化時間:</label>
                  <div>
                      <input name="field_digi_time" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>數位原始檔類型:</label>
                  <div>
                      <input name="field_digi_type" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>數位原始檔儲存位置:</label>
                  <div>
                      <textarea name="field_digi_path" type="text" style="width: 400px; height: 80px; resize: none" $digi_path></textarea>
                  </div>
              </div>
          </div>

          <div class="w2ui-page page-5">
              <div class="w2ui-field">
                  <label>登錄人</label>
                  <div>
                      <input name="field_recorder" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>登錄時間</label>
                  <div>
                      <input name="field_recorded_time" type="text" maxlength="100" size="20" disabled/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>更新人</label>
                  <div>
                      <input name="field_updator" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>更新時間:</label>
                  <div>
                      <input name="field_updated_time" type="text" maxlength="100" size="20" disabled/>
                  </div>
              </div>
          </div>

          <div class="w2ui-page page-6">
              <div class="w2ui-field">
                  <label>公開與否:</label>
                  <div>
                      <input name="field_public" type="checkbox" class="w2ui-toggle"/><div><div></div></div>
                 </div>
              </div>
              <div class="w2ui-field">
                  <label>公開與否註記:</label>
                  <div>
                      <textarea name="field_public_note" type="text" style="width: 100%; height: 80px; resize: none"></textarea>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>隱私權疑慮與否:</label>
                  <div>
                      <input name="field_privacy" type="checkbox" class="w2ui-toggle"/><div><div></div></div>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>隱私權疑慮註記 :</label>
                  <div>
                      <textarea name="field_privacy_note" type="text" style="width: 100%; height: 80px; resize: none"></textarea>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>公眾授權與否:</label>
                  <div>
                      <input name="field_release" type="checkbox" class="w2ui-toggle"/><div><div></div></div>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>釋出條款:</label>
                  <div>
                      <input name="field_license" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>姓名標示值:</label>
                  <div>
                      <input name="field_license_note" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>權利狀態:</label>
                  <div>
                      <input name="field_rightgranted" type="text"/>
                  </div>
              </div>
              <div class="w2ui-field">
                  <label>權利依據:</label>
                  <div>
                      <input name="field_rightgranted_note" type="text" maxlength="100" size="20"/>
                  </div>
              </div>
          </div>

          <div class="w2ui-buttons">
              <button class="btn" name="Clear">Clear</button>
              <button class="btn" name="Save">Save</button>
          </div>
      </div>
FORM;
  return $form;
}

function _form_new_script($model) {

  $script=<<<CODE
$(function () {
  var form_model = {
        name   : 'form_new',
        header : 'Collection Form',
        url    : '/json/new/collection',
        fields : $model,
        actions: {
            'Save': function (event) {
                //console.log('save', event);
                //console.log(this.record);
                //console.log(this.recid);
                //console.log(this.url);

                //w2utils.lock(this, { spinner: true, opacity : 1 });
                w2ui.form_new.lock('Loading...', true);

                $.ajax({
                  type: "POST",
                  url: this.url,
                  data: JSON.stringify(this.record),
                  contentType: "application/json; charset=utf-8",
                  dataType: "json",
                  processData: true,
                  success: function (data, status, jqXHR) {
                    w2ui.form_new.unlock();
                    //alert("success..." + data);
                  },
                  error: function (xhr) {
                    w2ui.form_new.unlock();
                    //alert(xhr.responseText);
                    alert('Error!!!')
                  }
                });

                //this.save(); // this here is w2ui['form'] object
            },
            'Clear': function (event) {
                // console.log('clear', event);
                this.clear();
            },
        },
        onChange: function (event) {
          if(event.target == 'field_privacy') { // 隱私權疑慮與否
            event.onComplete = function () {
              //console.log(this.record);
              this.record['field_public'] = 0;
              this.refresh();
            }
          }
        }
    };

  $('#form_new').w2form(form_model);
  w2ui['form_new'].enable_multiple();
});
CODE;
  return $script;
}

function _uploader($id) {
  $uploader =<<<HTML
      <div id="$id">
        <p>Your browser does not have Flash, Silverlight or HTML5 support.</p>
      </div>
HTML;
  return $uploader;
}

/*
  $options = array(
    'video_only' => false,   // 只允許新增影片 ogv, video_only = true 時, interactive 必為 false
    'image_only' => false,   // 只允許新增圖片 tif
    'interactive' => false   // 互動式新增馬賽克檔
  )
*/
function _single_uploader_script($id, $key, $finish_url, $on_complete_code, $nid = 0, $options = array()) {

  $p = drupal_get_path('module', 'editcol');

  $loader_img = file_create_url($p . '/images/ajax-loader.gif');

  $interactive = <<<INTERACTIVE
  interactive: false
INTERACTIVE;

  $media_allowed = <<<MEDIA
  {title : "Image files", extensions : "tif"},
  {title : "Video files", extensions : "ogv"}
MEDIA;

  if(!empty($options)) {
    if(array_key_exists('video_only', $options) && $options['video_only']) {
      $media_allowed = <<<MEDIA
      {title : "Video files", extensions : "ogv"}
MEDIA;
    }

    if(array_key_exists('image_only', $options) && $options['image_only']) {
      $media_allowed = <<<MEDIA
      {title : "Image files", extensions : "tif"},
MEDIA;
    }

    if(array_key_exists('interactive', $options) && $options['interactive']) {
      if(array_key_exists('video_only', $options) && $options['video_only']) {
         // use default
      } else {
        $interactive = <<<INTERACTIVE
        interactive: true
INTERACTIVE;
      }
    }
  }

  $uploader_script = <<<CODE
$(function() {

  var mosaic_file   = null;
  var orig_filename = null; // the non mosaic selected file's name
  var orig_filename_ext = "";

  var reset_global = function() {
    mosaic_file = null;
    orig_filename = null;
    orig_filename_ext = "";
    document.getElementById("uploadInput").value = ""; // reset, ref: http://stackoverflow.com/questions/3144419/how-do-i-remove-a-file-from-the-filelist
  }

  var add_mosaic_file = function() {

    var hasAdded = false;
    var selected_files = document.getElementById("uploadInput").files;
    var num_of_files = selected_files.length; // Here should be 1 only.

    for (var fid = 0; fid < num_of_files; fid++) {
      mosaic_file = selected_files[fid];
    }

    // prepare the mosaic file name
    var mosaic_file_name = "";
    if(orig_filename != null) {
      if(orig_filename_ext !== "") {
        mosaic_file_name = orig_filename + "_mosaic." + orig_filename_ext;
      } else {
        mosaic_file_name = orig_filename + "_mosaic";
      }
    }

    // unbind the 'FilesAdded' evnt for preventing from the infinite invoking of this function
    uploader.unbind('filesadded', prog_add);

    /*
     * Uploader.addFile(), http://www.plupload.com/docs/Uploader#addFile-filefileName-method
     */
     if(mosaic_file != null && orig_filename != null) {
       uploader.addFile(mosaic_file, mosaic_file_name);
       hasAdded = true;
     } else {
       alert("add_mosaic_file(): the selected file's name or it's mosaic file object is null.")
     }

    // bind this handler for again for adding more files.
    if(hasAdded) uploader.bind('filesadded', post_prog_add); // 需要上面程式有 call uploader.addFile 才 bind post_prog_add。
                                                             // 用來消耗掉 uploader.addFile 所 fire 的 filesadded event。
                                                             // 否則會陷入無窮迴圈。
    else         uploader.bind('filesadded', prog_add);
  }

  var dialog = $( "#dialog" ).dialog({
    height: 235,
    width: 417,
    modal: true,
    autoOpen: false,
    show: {
      effect: "blind",
      duration: 500
    },
    hide: {
      effect: "explode",
      duration: 500
    },
    buttons: {
      '選擇': function() {
        add_mosaic_file();
        reset_global();
        dialog.dialog("close");
      },
      '放棄': function() {
        reset_global();
        dialog.dialog("close");
      }
    }
  });

  $("#$id").plupload({
    // General settings
    runtimes : 'html5,flash,silverlight,html4',
    url : '/collection/pl_upload',

    multi_selection: false,       // for interactive mode

    // Maximum file size
    max_file_size : '11gb',

    chunk_size: '30mb',

    max_retries: 3,

    multipart_params: {
      uniq : '$key',
      $interactive
    },

    // Specify what files to browse for
    filters : [
      $media_allowed
    ],

    // Sort files
    sortable: true,

    // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
    dragdrop: true,

    // Views to activate
    views: {
        list: true,
        thumbs: true, // Show thumbs
        active: 'thumbs'
    },
    max_file_count: 0,

  });

  /*
   * Retrieve internal plupload.Uploader
   * Uploader ref: http://www.plupload.com/docs/Uploader
   *               http://www.plupload.com/punbb/viewtopic.php?id=542
   */
  var uploader = $("#$id").plupload('getUploader');

  var radioBtn =  $('<input type="checkbox" name="interactive" id="int_check" />');
  radioBtn.appendTo("#$id").after(' 互動式新增馬賽克檔(Interactive) ?');

  var fade = $('<div id="fade"></div>');
  var modal = $("<div id='modal'><img id='loader' src='$loader_img' /></div>");
  fade.appendTo("#$id");
  modal.appendTo("#$id");

  // 初始化 interactive checkbox
  var interactive_for_now = uploader.getOption().multipart_params.interactive;
  if(interactive_for_now) {
    radioBtn.prop('checked',true);
  }

  radioBtn.click( function(){
     //alert(uploader.getOption().multiple_queues);
     //alert(uploader.getOption().max_file_count);
     if( $(this).is(':checked') ) {    // checked handler
       //uploader.setOption('multi_selection', false);     // 不知為何，只要 setOption 後, start upload button 就會被 disabled。暫時先拿掉。
     } else {                          // unckecked handler
       //uploader.setOption('multi_selection', true);
     }
  });

  var post_prog_add = function(up, files) {
    up.unbind('filesadded', post_prog_add);
    up.bind('filesadded', prog_add);
  }

  var prog_add = function(up, files) {

    // check if the interactive mosaic upload option is enabled. if this option is disabled, do nothing.
    if(!radioBtn.prop('checked')) return;

    // make sure only one file is added.
    var the_file = files[0];
    var to_remove;
    if(files.length > 1) {
      to_remove = files.slice(1, files.length); // head::[tail], to_remove is the tail array.
      to_remove.forEach(function(f) { up.removeFile(f); });
    }

    // get the selected file's name
    var _fname = the_file.name.split(".");
    if(_fname.length > 1) { orig_filename_ext = _fname.pop(); } // remote the last element(the file extension)
    orig_filename = _fname.join(".");

    // pop up mosaic window asking for a mosaic file
    dialog.dialog("open");
  }

  uploader.bind('filesadded', prog_add); // bind handler first

  $("#$id").on('started', function(event, args) {
    openModal();
  });

  // upload complete
  $("#$id").on('complete', function(event, args) {
    var uploader = args.up;
    var options = uploader.getOption();
    var uniq = options.multipart_params.uniq;

    $.ajax({
      type: "POST",
      url: '$finish_url',
      data: JSON.stringify({ 'uniq': uniq , 'nid': $nid}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      processData: true,
      success: function (data, status, jqXHR) {
        try {
          closeModal();
          $on_complete_code
        } catch(e) {
          closeModal();
          alert(e);
        }
      },
      error: function (xhr) {
        alert(xhr.statusText);
        //alert(xhr.responseText);
      }
    });
  });
});
CODE;
  return $uploader_script;
}


function _multiple_uploader_script($id, $key, $finish_url) {

  $p = drupal_get_path('module', 'editcol');

  $loader_img = file_create_url($p . '/images/ajax-loader.gif');

  $uploader_script = <<<CODE
$(function() {

  $("#$id").plupload({
    // General settings
    runtimes : 'html5,flash,silverlight,html4',
    url : '/collection/pl_upload',

    multi_selection: false,       // for interactive mode

    // Maximum file size
    max_file_size : '11gb',

    chunk_size: '30mb',

    max_retries: 3,

    multipart_params: {
      uniq : '$key'
    },

    // Specify what files to browse for
    filters : [
	    {title : "Zip files", extensions : "zip"},
      {title : "Image files", extensions : "tif"},
      {title : "Video files", extensions : "ogv"}
    ],

    // Rename files by clicking on their titles
    rename: true,

    // Sort files
    sortable: true,

    // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
    dragdrop: true,

    // Views to activate
    views: {
        list: true,
        thumbs: true, // Show thumbs
        active: 'thumbs'
    },

    // Flash settings
    //flash_swf_url : '/plupload/js/Moxie.swf',

    // Silverlight settings
    //silverlight_xap_url : '/plupload/js/Moxie.xap'
  });

/*
  var fade = $('<div id="fade"></div>');
  var modal = $("<div id='modal'><img id='loader' src='$loader_img' /></div>");
  fade.appendTo("#$id");
  modal.appendTo("#$id");

  $("#$id").on('started', function(event, args) {
    openModal();
  });
*/

  // upload complete
  $("#$id").on('complete', function(event, args) {
    var uploader = args.up;
    var options = uploader.getOption();
    var uniq = options.multipart_params.uniq;

    $.ajax({
      type: "POST",
      url: '$finish_url',
      data: JSON.stringify({ 'uniq': uniq }),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      processData: true,
      success: function (data, status, jqXHR) {
        try {
          //closeModal();
          var obj = JSON.parse(data);
          var url = '/action/export/csv/' + obj.range;
          window.location.href = url;
        } catch(e) {
          //closeModal();
          alert(e);
        }
      },
      error: function (xhr) {
        alert(xhr.statusText);
      }
    });
  });
});
CODE;
  return $uploader_script;
}

function _multiple_updater_script($id, $key, $finish_url) {

  $p = drupal_get_path('module', 'editcol');

  $uploader_script = <<<CODE
$(function() {

  $("#$id").plupload({
    // General settings
    runtimes : 'html5,flash,silverlight,html4',
    url : '/collection/pl_upload',

    multi_selection: false,       // for interactive mode

    // Maximum file size
    max_file_size : '11gb',

    chunk_size: '30mb',

    max_retries: 3,

    multipart_params: {
      uniq : '$key'
    },

    // Specify what files to browse for
    filters : [
	    {title : "Zip files", extensions : "zip"},
    ],

    // Rename files by clicking on their titles
    rename: true,

    // Sort files
    sortable: true,

    // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
    dragdrop: true,

    // Views to activate
    views: {
        list: true,
        thumbs: true, // Show thumbs
        active: 'thumbs'
    },

    // Flash settings
    //flash_swf_url : '/plupload/js/Moxie.swf',

    // Silverlight settings
    //silverlight_xap_url : '/plupload/js/Moxie.xap'
  });

  // upload complete
  $("#$id").on('complete', function(event, args) {
    var uploader = args.up;
    var options = uploader.getOption();
    var uniq = options.multipart_params.uniq;

    $.ajax({
      type: "POST",
      url: '$finish_url',
      data: JSON.stringify({ 'uniq': uniq }),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      processData: true,
      success: function (data, status, jqXHR) {
        try {
          var obj = JSON.parse(data);
          var url = '/action/export/csv/unordered/' + obj.key;
          window.location.href = url;
        } catch(e) {
          alert(e);
        }
      },
      error: function (xhr) {
        alert(xhr.statusText);
      }
    });
  });
});
CODE;
  return $uploader_script;
}


function _control_panel_for_admin($tab_id, $form_model) {

  $id1 = uniqid();
  $id2 = uniqid();
  $id3 = uniqid();

  $form_new = _form_new($form_model);
  $model = json_func_expr(json_encode($form_model));
  $form__new_script = _form_new_script($model);

  $suploader = _uploader('suploader');
  $suploader_on_complete = <<<SCODE
    var obj = JSON.parse(data);
    window.location.href = "/collection/edit/" + obj.new_id; // redirection
SCODE;
  $options = array(
    'interactive' => true    // default enable interactive mode.
  );
  $suploader_script = _single_uploader_script('suploader', $id1, '/collection/single_upload/finish', $suploader_on_complete, 0, $options);

  $muploader = _uploader('muploader');
  $muploader_script = _multiple_uploader_script('muploader', $id2, '/collection/multiple_upload/finish');

  $mupdater = _uploader('mupdater');
  $mupdater_script = _multiple_updater_script('mupdater', $id3, '/collection/multiple_update/finish');

  if(empty($tab_id)) $tab_id = 1;

  $p = drupal_get_path('module', 'editcol');
  $p = url($p);

  $form = <<<FORM

<div id="dialog" title="選擇一個馬賽克檔案" style="display:none">
	<p>
		<input id="uploadInput" type="file" name="myFiles">
	</p>
</div>

<div id="tab-control_panel">
    <div id="top_tabs" style="width: 100%; height: 50px;"></div>

    <div id="top_tab1" class="tab">
      $form_new
    </div>

    <div id="top_tab2" class="tab">
      $suploader
      <p><p>
  	  說明:
  	  <ul>
  	    <li>單筆藏品上傳只需將同一筆的所有檔案拖曳進入上傳介面即可開始上傳，上傳完畢後，系統會導至此筆新增藏品之「後設資料」輸入介面。</li>
        <li>所有「後綴 <span style="color:red">_mosaic</span>」的檔名，皆被視為馬賽克檔，如假設典藏檔名為「foo.tif」，則相對的馬賽克檔為「foo_mosaic.tif」。</li>
        <li>預設「互動式馬賽克上傳模式」開啟，每新增一個典藏檔，系統會讓使用者選擇其相對應的馬賽克檔，此時所選的檔案會<span style="color:red">自動改名</span>為相對的馬賽克檔。</li>
        <li>若關閉「互動式馬賽克上傳模式」時，上傳馬賽克檔<span style="color:red">需手動</span>將馬賽克檔名改成「後綴 _mosaic」的檔名，如假設典藏檔名為「foo.tif」，則相對的馬賽克檔為「foo_mosaic.tif」。</li>
        <li>影片類藏品不會有馬賽克檔，請檢查上傳的影片檔名，其不應該有「foo_mosaic.ogv」這樣的檔名。</li>
        <li>上傳影片時，請<span style="color:red">關閉</span>「互動式馬賽克上傳模式」。</li>
  	  	<li>上傳前請先確認檔案系統有足夠空間，否則會有異常的行為發生。</li>
  	  </ul>
      </p></p>
    </div>

    <div id="top_tab3" class="tab">
      $muploader
      <p><p>
  	  說明:
  	  <ul>
  	    <li>多筆藏品上傳可一次新增多比藏品，藏品分成「單檔藏品」與「多檔藏品」兩種。</li>
  		  <li>「單檔藏品」指的是一件藏品只有一個檔案，此時需將檔案名稱改成「原件典藏編號」後拖曳進入上傳介面。</li>
  		  <li>「多檔藏品」指的是一件藏品有多個檔案，此時需將這些檔案置入一以「原件典藏編號」為名之目錄，然後壓縮此目錄為一 zip 檔後拖曳進入上傳介面。</li>
        <li>所有「後綴 <span style="color:red">_mosaic</span>」的檔名，皆被視為馬賽克檔，如假設典藏檔名為「foo.tif」，則相對的馬賽克檔為「foo_mosaic.tif」。</li>
        <li><span style="color:red">影片類藏品不會有馬賽克檔</span>，請檢查上傳的影片檔名，其不應該有「foo_mosaic.ogv」這樣的檔名。</li>
  		  <li>所有藏品皆拖入上傳介面後，便可開始上傳，上傳完畢，系統會導到 CRUD 介面，此時最上方有一 CSV 檔下載連結，此為這些新增的藏品的後設資料表格，請用試算表開啟填寫後設資料後，再進到 CRUD 介面進行後設資料更新。</li>
  		  <li><span style="color:blue">當一次有多筆藏品要上傳且多為影音資料時，請務必使用此「多筆藏品上傳介面」，因為此介面會進行循序轉檔，不會使系統負荷過重。</span></li>
  		  <li>上傳前請先確認檔案系統有足夠空間，否則會有異常的行為發生。</li>
  	  </ul>
      </p></p>
    </div>

    <div id="top_tab4" class="tab">
      $mupdater
      <p><p>
  	  說明:
  	  <ul>
  	    <li>多筆藏品數位檔案更新乃是<span style="color:red">重建</span>藏品的數位檔，即是把所有的舊檔刪除，再重新上傳。</li>
        <li>此介面只能接受「<span style="color:red">zip</span>」附檔名的檔案。</li>
        <li>這些更新檔必需使用「<span style="color:red">原件典藏編號</span>」當檔名，系統會根據這個編號找出相對應的藏品進行更新。</li>
        <li>範例:
           <p>假設 IA00001 這件藏品，共有四件如下，而其中 AAAA、CCCC　有馬賽克的圖： </p>

           <p>AAAA、BBBB、CCCC、DDDD</p>

           <p>它的壓縮檔名及內容物會是如下：</p>

           <pre>
IA00001.zip

IA00001/AAAA.tif
IA00001/AAAA_mosaic.tif
IA00001/BBBB.tif
IA00001/CCCC.tif
IA00001/CCCC_mosaic.tif
IA00001/DDDD.tif
           </pre>
        </li>
        <li>從上例可見只有目錄名稱才需要使用原件典藏編號，內含檔案只要遵從馬賽克命名規則即可。</li>
        <li>若有一個檔案只有馬賽克檔而無原始檔，則會被忽略處理。</li>
        <li>經由此介面更新的藏品會預設為「<span style="color:red">公開</span>」，若需「不公開」請經由藏品介面關閉之。</li>
  	  </ul>
      </p></p>
    </div>


    <div id="top_tab5" class="tab">
      <ul>
         <li><a href='/admin/config/coll/archive_crud'>後設資料大量輸出入與公眾系統同步。</a></li>
      </ul>
    </div>

    <div id="top_tab6" class="tab">
      <ul>
         <li><a href='/admin/config/coll/unverified_claim'>未確認的認領</a></li>
         <li><a href='/admin/config/coll/verified_claim'>已確認的認領</a></li>
         <li><a href='/admin/config/coll/conflict_claim'>「未確認的認領」和「已確認的認領」衝突表</a></li>
      </ul>
    </div>
</div>

<script type="text/javascript">
  $form__new_script
  $suploader_script
  $muploader_script
  $mupdater_script
</script>

<script type="text/javascript">
var config = {
    tabs: {
        name: 'top_tabs',
        active: 'top_tab1',
        tabs: [
            { id: 'top_tab1', caption: '建立無檔藏品' },
            { id: 'top_tab2', caption: '單筆藏品上傳' },
            { id: 'top_tab3', caption: '多筆藏品上傳' },
            { id: 'top_tab4', caption: '多筆藏品更新' },
            { id: 'top_tab5', caption: 'CRUD' },
            { id: 'top_tab6', caption: '認領管理' },
        ],
        onClick: function (event) {
            $('#tab-control_panel .tab').hide();
            $('#tab-control_panel #' + event.target).show();
            if(event.target == 'top_tab1') w2ui['form_new'].refresh();
        }
    }
}

$(function () {
    $('#top_tabs').w2tabs(config.tabs);
    $('#tab-control_panel .tab').hide();
    w2ui['top_tabs'].click('top_tab$tab_id');
});
</script>

FORM;
  return $form;
}

function _control_panel($tab_id, $form_model) {
  return _control_panel_for_admin($tab_id, $form_model);
}
