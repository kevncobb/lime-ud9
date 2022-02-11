<?php

namespace Drupal\symfony_mailer\Plugin\MailerTransport;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\symfony_mailer\TransportPluginInterface;

/**
 * Base class for Mailer Transport plug-ins.
 */
abstract class TransportBase extends PluginBase implements TransportPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getDsn() {
    $cfg = $this->configuration;
    $dsn = $this->getPluginId() . '://' .
      (isset($cfg['user']) ? urlencode($cfg['user']) : '') .
      (isset($cfg['pass']) ? ':' . urlencode($cfg['pass']) : '') .
      (isset($cfg['user']) ? '@' : '') .
      (urlencode($cfg['host'] ?? 'default')) .
      (isset($cfg['port']) ? ':' . $cfg['port'] : '') .
      (!empty($cfg['query']) ? '?' . http_build_query($cfg['query']) : '');

    return $dsn;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

}
