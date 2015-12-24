<?php
function _coll_ef_variables() {
  $nodetype = array('name' => 'collection', 'description' => 'a collection.');
  $field =
    array(
      array('is-public' => true,  'name'=>'identifier',       'label'=> '識別號',   'type'=>'text'),
      array('is-public' => true,  'name'=>'mainformat',       'label'=> '主要形式', 'type'=>'taxon', 'vocabulary' => 'mainform',    'widget'=>'category'),
      array('is-public' => true,  'name'=>'format_category',  'label'=> '形式分類', 'type'=>'taxon', 'vocabulary' => 'formcate',    'widget'=>'category', 'cardinality' => -1),
      array('is-public' => true,  'name'=>'content_category', 'label'=> '內容分類', 'type'=>'taxon', 'vocabulary' => 'contentcate', 'widget'=>'category', 'cardinality' => -1),
      array('is-public' => true,  'name'=>'event',            'label'=> '所屬事件', 'type'=>'taxon', 'vocabulary' => 'event',       'widget'=>'category', 'cardinality' => -1),
      array('is-public' => true,  'name'=>'tagtag',           'label'=> 'Tag',      'type'=>'taxon', 'vocabulary' => 'tag', 'widget'=>'tag', 'cardinality' => -1),
      array('is-public' => true,  'name'=>'description',      'label'=> '描述',     'type'=>'longtext', 'format'=>'normal_text'),
      array('is-public' => true,  'name'=>'content',          'label'=> '內容',     'type'=>'longtext', 'format'=>'normal_text'),

      array('is-public' => true,  'name'=>'relcol_partof',    'label'=> '關係藏品-整體', 'type'=>'text', 'cardinality' => -1),
      array('is-public' => true,  'name'=>'relcol_relatedto', 'label'=> '關係藏品-關聯', 'type'=>'text', 'cardinality' => -1),

      array('is-public' => false, 'name'=>'creator',          'label'=> '作者',      'type'=>'taxon', 'vocabulary' => 'people', 'widget'=>'category', 'cardinality' => -1),
      array('is-public' => false, 'name'=>'provider',         'label'=> '提供者',    'type'=>'taxon', 'vocabulary' => 'people', 'widget'=>'category', 'cardinality' => -1),
      array('is-public' => false, 'name'=>'collector',        'label'=> '取得者',    'type'=>'taxon', 'vocabulary' => 'people', 'widget'=>'category'),
      array('is-public' => false, 'name'=>'collected_time',   'label'=> '取得日期',  'type'=>'text'),
      array('is-public' => false, 'name'=>'collected_place',  'label'=> '取得地點',  'type'=>'text'),
      array('is-public' => false, 'name'=>'collected_method', 'label'=> '取得方式',  'type'=>'taxon', 'vocabulary' => 'collmethod', 'widget'=>'category'),

      array('is-public' => true, 'name'=>'created_time',     'label'=> '產製日期',  'type'=>'text'),
      array('is-public' => true, 'name'=>'created_place',    'label'=> '產製地點',  'type'=>'text'),

      array('is-public' => false, 'name'=>'repository',       'label'=> '原件典藏者',   'type'=>'taxon', 'vocabulary' => 'people', 'widget'=>'category'),
      array('is-public' => false, 'name'=>'repository_id',    'label'=> '原件典藏編號', 'type'=>'text'),
      array('is-public' => false, 'name'=>'repository_place', 'label'=> '原件典藏位置', 'type'=>'text'),
      array('is-public' => false, 'name'=>'condition',        'label'=> '藏品狀況',     'type'=>'taxon', 'vocabulary' => 'condition', 'widget'=>'category'),
      array('is-public' => false, 'name'=>'condition_note',   'label'=> '藏品狀況註記', 'type'=>'longtext'),
      array('is-public' => true,  'name'=>'material',         'label'=> '材質',         'type'=>'taxon', 'vocabulary' => 'material', 'widget'=>'tag', 'cardinality' => -1),
      array('is-public' => true,  'name'=>'measurement',      'label'=> '測量',         'type'=>'longtext'),
      array('is-public' => false, 'name'=>'digi_method',      'label'=> '數位化方式',         'type'=>'taxon', 'vocabulary' => 'digimethod', 'widget'=>'category'),
      array('is-public' => false, 'name'=>'digi_type',        'label'=> '數位原始檔類型',     'type'=>'taxon', 'vocabulary' => 'mediatype', 'widget'=>'category'),
      array('is-public' => false, 'name'=>'digi_path',        'label'=> '數位原始檔儲存位置', 'type'=>'longtext', 'cardinality' => -1),
      array('is-public' => false, 'name'=>'digi_time',        'label'=> '數位化時間',         'type'=>'text'),

      array('is-public' => false, 'name'=>'recorder',         'label'=> '登錄人',   'type'=>'taxon', 'vocabulary' => 'people', 'widget'=>'category'),
      array('is-public' => false, 'name'=>'recorded_time',    'label'=> '登錄時間', 'type'=>'text'),
      array('is-public' => false, 'name'=>'updator',          'label'=> '更新人',   'type'=>'taxon', 'vocabulary' => 'people', 'widget'=>'category'),
      array('is-public' => false, 'name'=>'updated_time',     'label'=> '更新時間', 'type'=>'text'),

      array('is-public' => false, 'name'=>'note',             'label'=> '備註',     'type'=>'longtext'),

      array('is-public' => false, 'name'=>'public',           'label'=> '公開與否',       'type'=>'taxon', 'vocabulary' => 'boolean'),
      array('is-public' => false, 'name'=>'public_note',      'label'=> '公開與否註記',   'type'=>'longtext'),

      array('is-public' => false, 'name'=>'privacy',          'label'=> '隱私權疑慮與否', 'type'=>'taxon', 'vocabulary' => 'boolean'),
      array('is-public' => false, 'name'=>'privacy_note',     'label'=> '隱私權疑慮註記', 'type'=>'longtext'),

      array('is-public' => false, 'name'=>'high_resolution',  'label'=> '高解析開放與否','type'=>'taxon', 'vocabulary' => 'boolean'),
      array('is-public' => false, 'name'=>'no_mosaic',          'label'=> '無馬賽克檔開放與否','type'=>'taxon', 'vocabulary' => 'boolean'),

      array('is-public' => false, 'name'=>'release',          'label'=> '公眾授權與否',   'type'=>'taxon', 'vocabulary' => 'boolean'),
      array('is-public' => true,  'name'=>'license',          'label'=> '釋出條款',       'type'=>'taxon', 'vocabulary' => 'license', 'widget'=>'category'),
      array('is-public' => true,  'name'=>'license_note',     'label'=> '姓名標示值',     'type'=>'text'),

      array('is-public' => true,  'name'=>'rightgranted',      'label'=> '權利狀態',     'type'=>'taxon', 'vocabulary' => 'rightgranted'),
      array('is-public' => false, 'name'=>'rightgranted_note', 'label'=> '權利依據',     'type'=>'text'),
    );

  $path_base = COLL_FILE_BASE_SCHEMA;
  $path = array('is-public' => false,
                'base' => $path_base,
                'upload' => $path_base . '/' .'upload',
                'export' => $path_base . '/' .'export',
                'archive0' => $path_base . '/' .'archive0', //no_moasic
                'archive' => $path_base . '/' .'archive',
                'archive_mosbk' => $path_base . '/' .'archive_mosbk', //moasic backup
                'public0' =>  $path_base . '/' .'public0',
                'public' =>  $path_base . '/' .'public',
                'source' => $path_base . '/' .'source',
                'meta' => $path_base . '/' .'meta',
               );

  $vars = array(
            'node' => array(array('bundle'=>'node', 'entity' => $nodetype, 'field' => $field,)),
            'path' => $path,
          );
  return $vars;
}
