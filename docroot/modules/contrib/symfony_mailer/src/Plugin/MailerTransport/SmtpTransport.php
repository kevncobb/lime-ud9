<?php

namespace Drupal\symfony_mailer\Plugin\MailerTransport;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the SMTP Mail Transport plug-in.
 *
 * @MailerTransport(
 *   id = "smtp",
 *   label = @Translation("SMTP"),
 * )
 */
class SmtpTransport extends TransportBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'user' => '',
      'pass' => '',
      'host' => '',
      'port' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['user'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User name'),
      '#default_value' => $this->configuration['user'] ?? '',
      '#description' => $this->t('User name to log in'),
    ];

    $form['pass'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#default_value' => $this->configuration['pass'] ?? '',
      '#description' => $this->t('Password to log in'),
    ];

    $form['host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Host name'),
      '#default_value' => $this->configuration['host'] ?? '',
      '#description' => $this->t('SMTP host name'),
      '#required' => TRUE,
    ];

    $form['port'] = [
      '#type' => 'number',
      '#title' => $this->t('Port'),
      '#default_value' => $this->configuration['port'] ?? '',
      '#description' => $this->t('SMTP port'),
      '#min' => 0,
      '#max' => 65535,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['user'] = $form_state->getValue('user');
    $this->configuration['pass'] = $form_state->getValue('pass');
    $this->configuration['host'] = $form_state->getValue('host');
    $this->configuration['port'] = $form_state->getValue('port');
  }

}
