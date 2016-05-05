<?php
function _collpub_page() {
  return _coll_search_page();
}

function _collpub_icon($identifier) {
  $file = _coll_get_feature_image_uri($identifier);
  if (file_exists($file)) {
    header("Content-type: image/jpeg");
    readfile($file);
    exit;
  }
}

function _collpub_misc_settings() {
  $r = drupal_get_form('_collpub_misc_settings_form');
  return $r;
}

function _collpub_misc_settings_form($form, &$form_state) {
  $form = array();
  $licenses_default =
    array(
      'CC0'   => 'https://creativecommons.org/choose/zero/',
      'CC-by'   => 'https://creativecommons.org/by/',
    );
  $options = variable_get('license_link_map',   $licenses_default);
  $s = list_allowed_values_string($options);
  $form['license_link_map'] = array(
                                '#title' => t('Licenses'),
                                '#type' => 'textarea',
                                '#description' => t ('license|url pair'),
                                '#default_value' => $s,
                              );
  $form['ft'] =
    array(
      '#type' => 'fieldset',
      '#title' => t('full text search'),
      '#collapsible' => TRUE,
      '#collapsed' => true,
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

function _collpub_misc_settings_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $v = DT::list_extract_values($values['license_link_map']);
  variable_set('license_link_map', $v);
  $ft_columns = array();
  foreach($values['expsearch_fields'] as $key => $value) {
    if($value) $ft_columns[] = $value;
  }
  variable_set('ft_columns', $ft_columns);
  drupal_set_message(t('configuration saved!'));
}
