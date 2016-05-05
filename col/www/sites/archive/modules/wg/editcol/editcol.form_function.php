<?php

require_once('easier.tools.php');
require_once('editcol.control_panel.php');

/*
 * 和單筆藏品上傳相同的的介面, 只是上傳後的處理流程不同
 *
 * $option = 0 更新，代表介面只用來上傳影片檔，互動模式需關閉
 *         = 1 更新，代表介面只用來上傳圖片檔，互動模式預設開啟
 *         = 2 上傳，代表介面可用來上傳影片或是圖片，互動模式預設關閉。
 */
function _collection_all_file_update_form($nid, $type = 0) {
  $id = uniqid();

  $uploader = _uploader('uploader'); // defined at editcol.control_panel.php

  $uploader_on_complete = <<<SCODE
    var obj = JSON.parse(data);
    window.location.href = "/" + obj.nid; // redirection
SCODE;

  $options = array();

  switch($type) {
    case 0:
      $options = array(
        'video_only' => true,   // 只允許新增影片 ogv, video_only = true 時, interactive 必為 false
      );
      break;
    case 1:
      $options = array(
        'image_only' => true,
        'interactive' => true, // 預設開啟互動模式
      );
      break;
    case 2:
      $options = array(
        'interactive' => true // 預設開啟互動模式
      );
      break;
    default:
  }

  $uploader_script = _single_uploader_script('uploader', $id, '/collection/all_file_update', $uploader_on_complete, $nid, $options); // defined at editcol.control_panel.php

  $html =<<< FORM

  <div id="dialog" title="選擇一個馬賽克檔案" style="display:none">
  	<p>
  		<input id="uploadInput" type="file" name="myFiles">
  	</p>
  </div>

  <div id="uploader">
    <p>Your browser doesnt have Flash, Silverlight  HTML5 support</p>
  </div>
  <p><p>
   說明:
   <ul>
     <li>上傳前請先確認檔案系統有足夠空間，否則會有異常的行為發生。</li>
   </ul>
  </p></p>

  <script type="text/javascript">
    $uploader_script
  </script>
FORM;
  return $html;
}



function _collection_idv_file_update_form($nid, $filename, $op) {

    $id = uniqid();

    $html = <<<FORM

  <div id="uploader">
    <p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
  </div>
  <div id="fade"></div>
  <div id='modal'><img id='loader' src='/sites/318_archive/modules/wg/editcol/images/ajax-loader.gif' /></div>
  <p><p>
   說明:
   <ul>
     <li>上傳前請先確認檔案系統有足夠空間，否則會有異常的行為發生。</li>
   </ul>
  </p></p>

  <script type="text/javascript">
  // Initialize the widget when the DOM is ready
  $(function() {

    $("#uploader").plupload({
      // General settings
      runtimes : 'html5,flash,silverlight,html4',
      url : '/collection/pl_upload',

      multi_selection: false,

      // Maximum file size
      max_file_size : '1000mb',

      chunk_size: '20mb',

      max_retries: 3,

      multipart_params: {
        uniq : '$id'
      },

      // Specify what files to browse for
      filters : [
        {title : "Image files", extensions : "tif"}
        //{title : "Video files", extensions : "ogv"}
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
      max_file_count : 1 // only 1 file is allowed
    });

    $("#uploader").on('started', function(event, args) {
      openModal();
    });

    // upload complete
    $("#uploader").on('complete', function(event, args) {
      var uploader = args.up;
      var options = uploader.getOption();
      var uniq = options.multipart_params.uniq;

      $.ajax({
        type: "POST",
        url: '/collection/idv_file_update',
        data: JSON.stringify({ 'uniq': uniq , 'nid': $nid, 'op' : '$op', 'filename' : '$filename' }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        processData: true,
        success: function (data, status, jqXHR) {
          try{
            closeModal();
            var obj = JSON.parse(data);
            window.location.href = "/" + obj.nid; // redirection
          } catch(e) {
            closeModal();
            alert('ajax error');
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
  </script>
FORM;

    return $html;
}

function _collection_multi_page_edit_form($form_model, $form_record) {
  $record = json_encode($form_record);

  $relcol_partof = $form_model[8]['html']['attr'];
  $relcol_relatedto = $form_model[9]['html']['attr'];
  $digi_path = $form_model[27]['html']['attr'];

  $model = json_func_expr(json_encode($form_model));

  $form = <<<FORM
<a href="javascript: w2ui.form.goto(0);" style="padding-right: 10px">描述</a> |
<a href="javascript: w2ui.form.goto(1);" style="padding: 10px">分類</a> |
<a href="javascript: w2ui.form.goto(2);" style="padding: 10px">關係</a> |
<a href="javascript: w2ui.form.goto(3);" style="padding: 10px">測量</a> |
<a href="javascript: w2ui.form.goto(4);" style="padding: 10px">數化</a> |
<a href="javascript: w2ui.form.goto(5);" style="padding: 10px">紀錄</a> |
<a href="javascript: w2ui.form.goto(6);" style="padding: 10px">授權</a>

<div style="height: 10px;"></div>

<div id="form" style="width: 650px;">

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
            <label>高解析度下載與否:</label>
            <div>
                <input name="field_high_resolution" type="checkbox" class="w2ui-toggle"/><div><div></div></div>
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

<script type="text/javascript">
$(function () {
  var form_model = {
        name   : 'form',
        header : 'Collection Form',
        url    : '/json/edit/collection',
        fields : $model,
        record : $record,
        actions: {
            'Save': function (event) {
                //console.log('save', event);
                //console.log(this.record);
                //console.log(this.recid);
                //console.log(this.url);

                //w2utils.lock(this, { spinner: true, opacity : 1 });
                w2ui.form.lock('Loading...', true);

                $.ajax({
                  type: "POST",
                  url: this.url,
                  data: JSON.stringify(this.record),
                  contentType: "application/json; charset=utf-8",
                  dataType: "json",
                  processData: true,
                  success: function (data, status, jqXHR) {
                    w2ui.form.unlock();
                    //alert("success..." + data);

                  },
                  error: function (xhr) {
                    w2ui.form.unlock();
                    alert(xhr.responseText);
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

  $('#form').w2form(form_model);
  w2ui['form'].enable_multiple();
});

</script>
FORM;
  return $form;
}
