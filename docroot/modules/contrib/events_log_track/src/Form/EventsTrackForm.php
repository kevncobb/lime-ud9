<?php

namespace Drupal\event_log_track\Form;

/**
 * @file
 * Contains Drupal\event_log_track\Form\EventsTrackForm.
 */

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Config Form for run events log track in Admin UI.
 */
class EventsTrackForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'events_track_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'event_log_track.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('event_log_track.adminsettings');
    $form['enable_log_deletion'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable log deletion'),
      '#default_value' => $config->get('enable_log_deletion') ?: FALSE,
    ];
    $form['timespan_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Timespan Limit'),
      '#description' => $this->t('Maximum days to keep the events log track.'),
      '#required' => TRUE,
      '#default_value' => $config->get('timespan_limit') ?: 30,
      '#min' => 1,
      '#step' => 1,
    ];
    $form['batch_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch Size'),
      '#description' => $this->t('Batch size to process the data.'),
      '#required' => TRUE,
      '#default_value' => $config->get('batch_size') ?: 50,
      '#min' => 1,
      '#step' => 1,
    ];

    $form['disable_db_logs'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Logging to DB'),
      '#description' => $this->t('Best used when using syslog.'),
      '#default_value' => $config->get('disable_db_logs') ?: FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('event_log_track.adminsettings')
      ->set('timespan_limit', $form_state->getValue('timespan_limit'))
      ->set('batch_size', $form_state->getValue('batch_size'))
      ->set('enable_log_deletion', $form_state->getValue('enable_log_deletion'))
      ->set('disable_db_logs', $form_state->getValue('disable_db_logs'))
      ->save();
  }

}
