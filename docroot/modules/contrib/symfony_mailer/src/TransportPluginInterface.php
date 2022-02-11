<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

interface TransportPluginInterface extends ConfigurableInterface, PluginInspectionInterface, PluginFormInterface {

  /**
   * Gets the DSN
   *
   * @return string
   *   The DSN.
   */
  public function getDsn();

}
