<?php

declare(strict_types = 1);

namespace Drupal\sendgrid_api\Cache;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\monitoring\Sensor\SensorManager;

/**
 * Intercepts requests for cache tag invalidation.
 */
final class SendGridApiCacheTagInvalidator implements CacheTagsInvalidatorInterface {

  /**
   * Monitoring sensor manager, if it exists.
   *
   * @var \Drupal\monitoring\Sensor\SensorManager|null
   */
  protected $sensorManager;

  /**
   * SendGridApiCacheTagInvalidator constructor.
   *
   * @param \Drupal\monitoring\Sensor\SensorManager|null $sensorManager
   *   Monitoring sensor manager, if it exists.
   */
  public function __construct(SensorManager $sensorManager = NULL) {
    $this->sensorManager = $sensorManager;
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags) {
    // Delete Monitoring plugin definitions when keys change.
    // Tag relies on https://www.drupal.org/project/key/issues/3157136.
    if ($this->sensorManager && in_array('key_plugins', $tags, TRUE)) {
      $this->sensorManager->clearCachedDefinitions();
    }
  }

}
