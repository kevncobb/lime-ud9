<?php

namespace Drupal\symfony_mailer\Processor;

use Drupal\symfony_mailer\EmailInterface;

interface EmailProcessorInterface {

  /**
   * Runs the pre-build function on an email message.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to process.
   */
  public function preBuild(EmailInterface $email);

  /**
   * Runs the pre-render function on an email message.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to process.
   */
  public function preRender(EmailInterface $email);

  /**
   * Runs the post-render function on an email message.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to process.
   */
  public function postRender(EmailInterface $email);

  /**
   * Gets the weight of the email processor.
   *
   * @param string $function
   *   The function that will be called: 'preBuild', 'preRender' or
   *   'postRender'.
   *
   * @return int
   *   The weight.
   */
  public function getWeight(string $function);

}
