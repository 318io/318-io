<?php

require_once 'collarchive.crud.import.inc';
require_once 'collarchive.crud.export.inc';
require_once 'collarchive.crud.sync.inc';

function _collarchive_crud_page() {
  $form = drupal_get_form('_collarchive_crud_form');
  return $form;
}

function _collarchive_crud_form($form, &$form_state) {
  $form = array();
  $vars = _coll_ef_variables();
  $form['import'] =
    array(
      '#type' => 'fieldset',
      '#title' => t('Import metadata'),
      '#collapsible' => TRUE,
      '#collapsed' => false,
    );

  $form['import']['import_file'] =
    array(
      '#title' => '',
      '#type' => 'managed_file',
      '#description' => t('Choose a file (.xlsx). If providing file is excel, please make sure all cells are setting to "text"'),
      '#default_value' => '',
      '#upload_validators' => array(
        'file_validate_extensions' => array('xlsx csv'),
      ),
      '#upload_location' => $vars['path']['source'],
      '#process' => array('_collarchive_crud_import_file_element_process')
    );
  $form['import']['button_import'] =
    array(
      '#type' => 'submit',
      '#value' => t('Import'),
    );

  $minid = db_query("SELECT nid FROM {node} ORDER BY nid")->fetchField();
  $maxid = db_query("SELECT nid FROM {node} ORDER BY nid DESC")->fetchField();
  $form['export']['archive'] =
    array(
      '#type' => 'fieldset',
      '#title' => t('export metadata'),
      '#collapsible' => true,
      '#description' => t('for editing'),
    );
  $form['export']['archive']['force_text'] =
    array(
      '#type' => 'checkbox',
      '#title' => t('force text'),
      '#default_value' => 1,
      '#description' => t('if excel not rendering csv correctly, uncheck this and assign node type by yourself'),
    );
  $desc = t('syntax: [indetifier]-[identifier], min: @minid, max: @maxid',
            array('@minid' => $minid, '@maxid' => $maxid));
  $form['export']['archive']['nid_range'] =
    array(
      '#type' => 'textfield',
      '#title' => t('identifier range'),
      '#default_value' => $minid.'-'.$maxid,
      '#size' => 20,
      '#description' => $desc
    );
  $form['export']['archive']['button_export_archive'] = array(
        '#type' => 'submit',
        '#value' => t('export archive'),
      );
  $form['export']['sync'] =
    array(
      '#type' => 'fieldset',
      '#title' => t('sync to public'),
      '#collapsible' => true,
      '#description' => t('All files will be exported to public system, but you have to import metadata manually'),
    );
  $public_system_url = variable_get('public_system_url',  null);
  $public_system_www_root = variable_get('public_system_www_root',  null);
  $public_site_path = variable_get('public_site_path');
  $public_digifolder = $public_system_www_root.'/sites/'.$public_site_path.'/files/digicoll';
  if(!$public_system_www_root || !$public_system_url) {
    $form['export']['sync']['sync_update_file'] =
      array(
        '#markup' => t('please set public_system_www_root first (in <a href="/admin/config/coll/settings">/admin/config/coll/settings</a>)'),
      );
  } else {
    $form['export']['sync']['sync_update_file'] =
      array(
        '#type' => 'checkbox',
        '#title' => t('update files'),
        '#default_value' => 0,
        '#description' => t('if checked, all media files in public system (path=:path, url=:url) will be replaced (may need very long time)', array(':path'=> $public_digifolder, ':url'=>$public_system_url)),
      );
    $form['export']['sync']['button_sync'] = array(
          '#type' => 'submit',
          '#value' => t('sync to public'),
        );
  }
  return $form;
}

function _collarchive_crud_import_file_element_process($element, &$form_state, $form) {
  $element = file_managed_file_process($element, $form_state, $form);
  $element['upload_button']['#access'] = FALSE;
  return $element;
}

function _collarchive_crud_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  switch($form_state['clicked_button']['#value']) {
    case $values['button_import']:
      $file = file_load($values['import_file']);
      $uploaded = file_save($file);
      if ($uploaded == TRUE) {
        $file_path = $file->uri;
        _collarchive_crud_process_import($file_path);
      } else {
        drupal_set_message(t('The file could not be uploaded. Please contact the site administrator.'), 'error');
        return;
      }
      break;
    case $values['button_export_archive']:
      _collarchive_crud_process_export_archive($values);
      break;
    case $values['button_sync']:
      $vars = _coll_ef_variables();
      $path = $vars['path'];
      $public_system_www_root = variable_get('public_system_www_root');
      $public_site_path = variable_get('public_site_path');
      $options['digifolder'] = $public_system_www_root.'/sites/'.$public_site_path.'/files/digicoll/public';
      $options['archivefolder'] = $public_system_www_root.'/sites/'.$public_site_path.'/files/digicoll/archive';
      $options['metafolder'] = $path['export'];
      $options['withfile'] = $values['sync_update_file'];
      _collarchive_crud_process_export_public($options);
      break;
  }
  return;
}
