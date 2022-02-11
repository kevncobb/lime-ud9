<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\Processor\TokenProcessorTrait;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Email Builder plug-in for user registration password module.
 *
 * @EmailBuilder(
 *   id = "user_registrationpassword",
 *   sub_types = {
 *     "register_confirmation_with_pass" = @Translation("Welcome (no approval required, password is set)"),
 *   },
 * )
 */
class UserRegistrationPasswordEmailBuilder extends EmailProcessorBase {

  use TokenProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    $this->tokenOptions(['callback' => 'user_registrationpassword_mail_tokens', 'clear' => TRUE]);
  }

}
