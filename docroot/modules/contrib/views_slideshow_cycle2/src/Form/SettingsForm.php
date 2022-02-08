<?php

namespace Drupal\views_slideshow_cycle2\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['views_slideshow_cycle2.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'views_slideshow_cycle2_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('views_slideshow_cycle2.settings');
    $local_library = \Drupal::service('library.discovery')->getLibraryByName('views_slideshow_cycle2', 'local');
    $remote_library = \Drupal::service('library.discovery')->getLibraryByName('views_slideshow_cycle2', 'remote');

    $form['library'] = [
      '#title' => $this->t('Library settings'),
      '#type' => 'details',
      '#tree' => TRUE,
      '#open' => TRUE,
    ];
    $form['library']['location'] = [
      '#type' => 'select',
      '#title' => $this->t('Library location'),
      '#options' => [
        'local' => $this->t('Local'),
        'remote' => $this->t('Remote'),
      ],
      '#required' => TRUE,
      '#description' => $this->t('Select location from which to load library.'),
      '#default_value' => $config->get('library.location'),
    ];

    $form['library']['local_description'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          'select[name="library[location]"]' => ['value' => 'local'],
        ],
      ],
      'text' => [
        '#type' => 'markup',
        '#markup' => $this->t('Expecting file at %location', ['%location' => $local_library['js'][0]['data']]),
      ]
    ];
    $form['library']['remote_description'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          'select[name="library[location]"]' => ['value' => 'remote'],
        ],
      ],
      'text' => [
        '#type' => 'markup',
        '#markup' => $this->t('Will load remote file from %location', ['%location' => $remote_library['js'][0]['data']]),
      ]
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if ($form_state->getValue('library')['location'] == 'local') {
      $local_library = \Drupal::service('library.discovery')->getLibraryByName('views_slideshow_cycle2', 'local');
      if (!isset($local_library['js'][0]['data']) || !file_exists($local_library['js'][0]['data'])) {
        $form_state->setErrorByName('library][location', $this->t('Local library not found at %location.', ['%location' => $local_library['js'][0]['data']]));
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('views_slideshow_cycle2.settings');

    $config->set('library', $form_state->getValue('library'));
    $config->save();
    drupal_set_message($this->t('Settings saved.'));
  }

}
