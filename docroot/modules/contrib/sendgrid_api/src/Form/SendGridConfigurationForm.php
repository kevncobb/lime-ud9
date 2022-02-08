<?php

declare(strict_types = 1);

namespace Drupal\sendgrid_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * SendGrid API configuration form.
 */
class SendGridConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sendgrid_api_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['sendgrid_api.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('sendgrid_api.settings');

    $form['key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('API key'),
      '#default_value' => $config->get('api_key'),
      '#config_data_store' => 'sendgrid_api.settings:api_key',
      '#key_filters' => [
        'type' => 'sendgrid_api_key',
      ],
    ];

    $form['use_guzzle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Experimental: Use Guzzle shim'),
      '#description' => $this->t('This will use the HTTP client built into Drupal rather than direct calls to Curl.'),
      '#default_value' => $config->get('http_client_shim'),
      '#config_data_store' => 'sendgrid_api.settings:http_client_shim',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $key = $form_state->getValue('key');
    $useGuzzle = $form_state->getValue('use_guzzle');
    $this->config('sendgrid_api.settings')
      ->set('api_key', $key)
      ->set('http_client_shim', $useGuzzle)
      ->save();
  }

}
