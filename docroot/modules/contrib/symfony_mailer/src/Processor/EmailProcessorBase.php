<?php

namespace Drupal\symfony_mailer\Processor;

use Drupal\Core\Plugin\PluginBase;
use Drupal\symfony_mailer\EmailInterface;

class EmailProcessorBase extends PluginBase implements EmailProcessorInterface {

  const DEFAULT_WEIGHT = 500;

  /**
   * {@inheritdoc}
   */
  public function preBuild(EmailInterface $email) {
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
  }

  /**
   * {@inheritdoc}
   */
  public function postRender(EmailInterface $email) {
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(string $function) {
    $weight = $this->getPluginDefinition()['weight'] ?? static::DEFAULT_WEIGHT;
    return is_array($weight) ? $weight[$function] : $weight;
  }

}
