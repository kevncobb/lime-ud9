<?php

namespace Drupal\symfony_mailer\Plugin\MailerTransport;

use Drupal\Core\Form\FormStateInterface;
use Drupal\symfony_mailer\TransportPluginInterface;

/**
 * Defines the native Mail Transport plug-in.
 *
 * @MailerTransport(
 *   id = "native",
 *   label = @Translation("Native"),
 * )
 */
class NativeTransport extends TransportBase {

  // @todo Maybe should override the options to pass -bs.
  // @see https://swiftmailer.symfony.com/docs/sending.html

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
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
  }

}
