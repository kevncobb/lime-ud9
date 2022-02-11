<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Email Builder plug-in for update module.
 *
 * @EmailBuilder(
 *   id = "update",
 *   sub_types = { "status_notify" = @Translation("Available updates") },
 * )
 */
class UpdateEmailBuilder extends EmailProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    foreach ($email->getParams() as $msg_type => $msg_reason) {
      $messages[] = _update_message_text($msg_type, $msg_reason);
    }

    $site_name = \Drupal::config('system.site')->get('name');
    $email->setVariable('site_name', $site_name)
      ->setVariable('update_status', Url::fromRoute('update.status')->toString())
      ->setVariable('update_settings', Url::fromRoute('update.settings')->toString())
      ->setVariable('messages', $messages);

    if (Settings::get('allow_authorize_operations', TRUE)) {
      $email->setVariable('update_manager', Url::fromRoute('update.report_update')->toString());
    }
  }

}
