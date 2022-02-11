<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Bcc Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "email_bcc",
 *   label = @Translation("Bcc header"),
 *   description = @Translation("Sets the email bcc header."),
 * )
 */
class BccEmailAdjuster extends AddressAdjusterBase {

  /**
   * {@inheritdoc}
   */
  protected function setAddress(EmailInterface $email, $address) {
    $email->setBcc($address);
  }

}
