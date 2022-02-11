<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the To Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "email_to",
 *   label = @Translation("To header"),
 *   description = @Translation("Sets the email to header."),
 * )
 */
class ToEmailAdjuster extends AddressAdjusterBase {

  /**
   * {@inheritdoc}
   */
  protected function setAddress(EmailInterface $email, $address) {
    $email->setTo($address);
  }

}
