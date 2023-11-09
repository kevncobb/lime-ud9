<?php

namespace Drupal\event_log_track_auth\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class used for helping Track user Authorization info.
 */
class EventLogTrackAuthExit implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::TERMINATE] = 'onExit';
    return $events;
  }

  /**
   * React to post-response kernel event.
   *
   * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
   *   Response object from server.
   *
   * @throws \UnexpectedValueException
   */
  public function onExit(TerminateEvent $event) {
    $status_code = $event->getResponse()->getStatusCode();
    if ($status_code == 403) {
      $log = [
        'type' => 'authorization',
        'operation' => 'fail',
        'description' => $this->t('Unauthorized access attempt'),
      ];
      event_log_track_insert($log);
    }
  }

}
