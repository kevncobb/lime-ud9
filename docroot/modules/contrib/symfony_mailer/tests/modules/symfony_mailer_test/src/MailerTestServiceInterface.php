<?php

namespace Drupal\symfony_mailer_test;

use Drupal\symfony_mailer\EmailInterface;

/**
 * Tracks sent emails for testing.
 */
interface MailerTestServiceInterface {

  /**
   * The name of the state key used for storing sent emails.
   */
  const STATE_KEY = 'mailer_test.emails';

}
