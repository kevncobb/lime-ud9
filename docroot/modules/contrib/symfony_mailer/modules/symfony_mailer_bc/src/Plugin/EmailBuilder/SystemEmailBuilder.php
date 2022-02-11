<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Processor\TokenProcessorTrait;

/**
 * Defines the Email Builder plug-in for system module.
 *
 * @EmailBuilder(
 *   id = "system",
 *   sub_types = { "action_send_email" = @Translation("Send mail") },
 * )
 */
class SystemEmailBuilder extends EmailProcessorBase {

  use TokenProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    $body = [
      '#type' => 'processed_text',
      '#text' => $email->getParam('message'),
    ];

    $email->setSubject($email->getParam('subject'))
      ->setBody($body);
  }

}
