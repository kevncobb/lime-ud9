<?php

namespace Drupal\event_log_track;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Service's functions.
 *
 * @package Drupal\event_log_track
 */
class EventLogTrackApi {

  use StringTranslationTrait;

  /**
   * This module's configuration.
   *
   * @var array
   */
  protected $config;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * EventLogTrackApi constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Connection $database) {
    $this->config = $config_factory->get('event_log_track.settings');
    $this->database = $database;
  }

  /**
   * Method to get the old records.
   */
  public function getOldRecords() {
    $timespan_days = $this->config->get('timespan_limit');
    $timespan = strtotime('-' . $timespan_days . 'days midnight');
    $query = $this->database->select('event_log_track', 'e')
      ->fields('e', ['lid'])
      ->condition('e.created', $timespan, '<');
    return $query->execute()->fetchCol();
  }

  /**
   * Helper function to create batches.
   */
  public function deleteOldRecords($records) {
    if (!empty($records)) {
      $data_chunks = array_chunk($records, $this->config->get('batch_size'));
      $operations = [];
      foreach ($data_chunks as $data_chunk) {
        $operations[] = ['_event_log_track_process_old_records', [$data_chunk]];
      }
      // Define your batch operation here.
      $batch = [
        'title' => $this->t('Deleting events track logs'),
        'operations' => $operations,
      ];
      batch_set($batch);
    }
  }

}
