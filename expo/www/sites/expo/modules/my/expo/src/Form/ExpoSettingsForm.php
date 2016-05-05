<?php

/**
 * @file
 * Contains \Drupal\system\Form\SiteInformationForm.
 */

namespace Drupal\expo\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

use Drupal\wg\DT;

/**
 * Configure site information settings for this site.
 */
class ExpoSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'expo_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['expo.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('expo.settings');

    $form['schema'] = array(
                        '#type' => 'details',
                        '#title' => t('schema path'),
                        '#open' => TRUE,
                        '#tree' => TRUE,
                      );
    $form['schema']['base'] = array(
                                '#type' => 'textfield',
                                '#title' => t('base'),
                                '#default_value' => $config->get('schema.base'),
                                '#required' => TRUE,
                              );
    $form['schema']['media'] = array(
                                 '#type' => 'textfield',
                                 '#title' => t('icon'),
                                 '#default_value' => $config->get('schema.media'),
                                 '#description' => t("."),
                               );
    $form['schema']['json'] = array(
                                '#type' => 'textfield',
                                '#title' => t('json'),
                                '#default_value' => $config->get('schema.json'),
                                '#description' => t("."),
                              );
    $form['ns'] = array(
                    '#type' => 'details',
                    '#title' => t('ns'),
                    '#open' => TRUE,
                    '#tree' => TRUE,
                  );
    $form['ns']['public318'] = array(
                                 '#type' => 'textfield',
                                 '#title' => t('public318'),
                                 '#default_value' => $config->get('ns.public318'),
                                 '#description' => t("."),
                               );

    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('expo.settings')
    ->set('ns.public318', $form_state->getValue(['ns', 'public318']))
    ->save();
    $schema_base = $form_state->getValue(['schema', 'base']);
    $schema_json = $schema_base . $form_state->getValue(['schema', 'json']);
    $schema_media = $schema_base . $form_state->getValue(['schema', 'media']);
    file_prepare_directory($schema_json, FILE_CREATE_DIRECTORY);
    file_prepare_directory($schema_media, FILE_CREATE_DIRECTORY);
  }

}
