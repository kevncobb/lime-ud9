<?php

namespace Drupal\Tests\event_log_track\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Base class for event_log_track functional browser tests.
 */
abstract class EventsLogTrackTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with some relevant administrative permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A user without any permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create users with specific permissions.
    $this->adminUser = $this->drupalCreateUser([
      'administer site configuration',
      'access event log track',
    ]);
    $this->webUser = $this->drupalCreateUser();
  }

  /**
   * Set the database logs configuration.
   */
  protected function setDbLogs($disable = FALSE) {
    $this->config('event_log_track.settings')->set('disable_db_logs', $disable)->save();
  }

}
