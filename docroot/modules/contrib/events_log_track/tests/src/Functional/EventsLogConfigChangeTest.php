<?php

namespace Drupal\Tests\event_log_track\Functional;

/**
 * Verifies log entries and user access based on permissions.
 *
 * @group events_log_track
 */
class EventsLogConfigChangeTest extends EventsLogTrackTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'event_log_track',
    'event_log_track_ui',
  ];

  /**
   * Tests logging cli actions.
   */
  public function testCliLog() {
    // Log in the admin user.
    $this->drupalLogin($this->adminUser);
    // Enable database logs.
    $this->setDbLogs();
    // Reason this wasn't installed before was that it logs setDbLogs to TRUE.
    \Drupal::service('module_installer')->install(['event_log_track_config']);
    $this->resetAll();

    // Set error reporting to display all notices.
    $this->config('system.logging')
      ->set('error_level', ERROR_REPORTING_DISPLAY_ALL)
      ->save();
    // Test whether the log is stored in the database.
    $this->drupalGet('admin/reports/events-track');
    // Verify config change to error_level was not logged.
    // as by default, a ClI action should not be logged.
    $this->assertSession()->pageTextContains('No events found.');

    // Turn on the CLI log.
    $this->config('event_log_track.settings')->set('log_cli', TRUE)->save();
    // Verify the cli action is logged.
    $this->drupalGet('admin/reports/events-track');
    $this->assertSession()->pageTextContains('new: log_cli: true old: log_cli: false');
  }

}
