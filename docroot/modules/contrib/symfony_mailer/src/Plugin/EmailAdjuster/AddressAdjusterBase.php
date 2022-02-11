<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\Core\Form\FormStateInterface;
use Drupal\symfony_mailer\Processor\EmailAdjusterBase;
use Drupal\symfony_mailer\EmailInterface;
use Symfony\Component\Mime\Address;

/**
 * Defines a base class for Email Adjusters that set an address field.
 */
abstract class AddressAdjusterBase extends EmailAdjusterBase {
  // @todo Allow multiple values
  // @todo Setting whether to replace existing addresses or add to them.

  /**
   * Sets the address in the appropriate header.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to process.
   * @param \Symfony\Component\Mime\Address|string $address
   *   The address to set.
   */
  abstract protected function setAddress(EmailInterface $email, $address);

  /**
   * {@inheritdoc}
   */
  public function postRender(EmailInterface $email) {
    $mail = $this->configuration['value'];
    $display = $this->configuration['display'];
    $address = $display ? new Address($mail, $display) : $mail;
    $this->setAddress($email, $address);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['value'] = [
      '#type' => 'textfield',
      '#title' => t('Address'),
      '#default_value' => $this->configuration['value'] ?? NULL,
      '#required' => TRUE,
      '#description' => $this->t('Email address.'),
    ];

    $form['display'] = [
      '#type' => 'textfield',
      '#title' => t('Display name'),
      '#default_value' => $this->configuration['display'] ?? NULL,
      '#description' => $this->t('Human-readable display name.'),
    ];

    return $form;
  }

}
