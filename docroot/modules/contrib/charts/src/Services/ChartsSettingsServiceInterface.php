<?php

namespace Drupal\charts\Services;

/**
 * Charts Settings Service Interface.
 *
 * @deprecated in charts:5.0.0-alpha3 and is removed from charts:5.0.0-alpha4.
 *   Use $config = \Drupal::config('charts.settings') instead.
 */
interface ChartsSettingsServiceInterface {

  /**
   * Get Charts Settings.
   *
   * @return array
   *   Charts settings.
   */
  public function getChartsSettings();

}
