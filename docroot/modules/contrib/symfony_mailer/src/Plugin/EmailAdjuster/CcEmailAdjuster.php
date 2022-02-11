<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Cc Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "email_cc",
 *   label = @Translation("Cc header"),
 *   description = @Translation("Sets the email cc header."),
 * )
 */
class CcEmailAdjuster extends AddressAdjusterBase {

  /**
   * {@inheritdoc}
   */
  protected function setAddress(EmailInterface $email, $address) {
    $email->setCc($address);
  }

}
