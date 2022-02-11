<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\Processor\TokenProcessorTrait;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Email Builder plug-in for simplenews module.
 *
 * @EmailBuilder(
 *   id = "simplenews",
 *   sub_types = {
 *     "subscribe" = @Translation("Subscription confirmation"),
 *     "validate" = @Translation("Validate"),
 *   },
 * )
 */
class SimplenewsEmailBuilder extends EmailProcessorBase {
  use TokenProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    $this->tokenData($email->getParam('context'));
  }

}
