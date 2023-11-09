<?php

namespace Drupal\event_log_track_config\EventSubscriber;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class used for tracking info for configuration changes.
 */
class EventLogTrackConfigSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ConfigEvents::SAVE => 'configSave',
      ConfigEvents::DELETE => 'configDelete',
    ];
  }

  /**
   * React to a config object being saved.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Config crud event.
   *
   * @throws \UnexpectedValueException
   */
  public function configSave(ConfigCrudEvent $event) {
    $config = $event->getConfig();
    $changed = FALSE;
    $raw = $config->getRawData();
    $diff = [];
    foreach ($raw as $key => $value) {
      $origin = $config->getOriginal($key);
      if ($origin !== $value) {
        $changed = TRUE;
        $diff['new'][$key] = $value;
        $diff['old'][$key] = $origin;
      }
    }
    if ($changed) {
      $log = [
        'type' => 'config',
        'operation' => 'save',
        'description' => Yaml::encode($diff),
        'ref_char' => $config->getName(),
      ];
      event_log_track_insert($log);
    }

  }

  /**
   * React to a config object being deleted.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Config crud event.
   *
   * @throws \UnexpectedValueException
   */
  public function configDelete(ConfigCrudEvent $event) {
    $config = $event->getConfig();
    $log = [
      'type' => 'config',
      'operation' => 'delete',
      'description' => $config->getName(),
      'ref_char' => $config->getName(),
    ];
    event_log_track_insert($log);
  }

}
