<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\Processor\TokenProcessorTrait;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Email Builder plug-in for user module.
 *
 * @EmailBuilder(
 *   id = "user",
 *   sub_types = {
 *     "cancel_confirm" = @Translation("Account cancellation confirmation"),
 *     "password_reset" = @Translation("Password recovery"),
 *     "register_admin_created" = @Translation("Account created by administrator"),
 *     "register_no_approval_required" = @Translation("Registration confirmation (No approval required)"),
 *     "register_pending_approval" = @Translation("Registration confirmation (Pending approval)"),
 *     "register_pending_approval_admin" = @Translation("Admin (user awaiting approval)"),
 *     "status_activated" = @Translation("Account activation"),
 *     "status_blocked" = @Translation("Account blocked"),
 *     "status_canceled" = @Translation("Account cancelled"),
 *   },
 * )
 *
 * @todo Notes for adopting Symfony Mailer into Drupal core. This builder can
 * set langcode, to, reply-to so the calling code doesn't need to.
 */
class UserEmailBuilder extends EmailProcessorBase {
  use TokenProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    $this->tokenOptions(['callback' => 'user_mail_tokens', 'clear' => TRUE]);
  }

}
