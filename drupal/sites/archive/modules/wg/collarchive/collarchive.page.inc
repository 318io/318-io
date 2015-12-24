<?php
function _collarchive_page() {
  return _coll_search_page();
}

function _collarchive_misc_settings() {
  $r = drupal_get_form('_collarchive_misc_settings_form');
  return $r;
}

function _collarchive_misc_settings_form($form, &$form_state) {
  $public_system_url = variable_get('public_system_url', 'http://public.318.test');
  $public_system_www_root = variable_get('public_system_www_root', '/data/drupal');
  $public_site_path = variable_get('public_site_path', 'public');
  $archive_site_path = variable_get('archive_site_path', 'archive');

  variable_set('public_system_url', $public_system_url);
  variable_set('public_system_www_root', $public_system_www_root);
  variable_set('public_site_path', $public_site_path);
  variable_set('archive_site_path', $archive_site_path);

  $form = array();
  $form['public_system'] =
    array(
      '#type' => 'fieldset',
      '#title' => t('Public system'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => t ('for sync'),
    );
  $form['public_system']['public_system_url'] =
    array(
      '#title' => t('public system url'),
      '#type' => 'textfield',
      '#description' => t ('without tailing slash'),
      '#default_value' => $public_system_url,
    );
  $form['public_system']['public_system_www_root'] =
    array(
      '#title' => t('www root'),
      '#type' => 'textfield',
      '#default_value' => $public_system_www_root,
    );
  $form['public_system']['public_site_path'] =
    array(
      '#title' => t('site path'),
      '#type' => 'textfield',
      '#default_value' => $public_site_path,
    );

  $form['ft'] =
    array(
      '#type' => 'fieldset',
      '#title' => t('full text search'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
  $options = _expsearch_admin_settings_ft_field_options();
  $ft_columns = variable_get('ft_columns', array_keys($options));
  variable_set('ft_columns', $ft_columns);
  $form['ft']['expsearch_fields'] = array(
                                      '#type' => 'checkboxes',
                                      '#title' => t('Fileds for full text search.'),
                                      '#options' => $options,
                                      '#default_value' => $ft_columns,
                                    );
  $index_table_name = _expsearch_admin_settings_index_table();
  $form['ft']['expsearch_index'] =
    array(
      '#markup' => t('<p>indextable is :index_table</p>', array(':index_table'=>$index_table_name)),
    );
  $form['submit'] =
    array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );
  return $form;
}

function _collarchive_misc_settings_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  variable_set('public_system_www_root', $values['public_system_www_root']);
  variable_set('public_system_url', $values['public_system_url']);
  $ft_columns = array();
  foreach($values['expsearch_fields'] as $key => $value) {
    if($value) $ft_columns[] = $value;
  }
  variable_set('ft_columns', $ft_columns);
  drupal_set_message(t('configuration saved!'));
}
