<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\symfony_mailer\Processor\EmailAdjusterBase;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Plain text alternative Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "email_plain",
 *   label = @Translation("Plain text alternative"),
 *   description = @Translation("Sets the email plain text alternative."),
 * )
 */
class PlainEmailAdjuster extends EmailAdjusterBase {

  /**
   * {@inheritdoc}
   */
  public function postRender(EmailInterface $email) {
    $email->setTextBody($this->configuration['value']);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['value'] = [
      '#type' => 'textarea',
      '#default_value' => $this->configuration['value'] ?? NULL,
      '#required' => TRUE,
      '#description' => $this->t('Plain text alternative.'),
    ];

    return $form;
  }

}
