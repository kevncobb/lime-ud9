<?php

namespace Drupal\Tests\symfony_mailer\Functional;

/**
 * Test Mailer overrides.
 *
 * @group symfony_mailer
 */
class OverrideTest extends SymfonyMailerTestBase {

  /**
   * URL for override info page.
   */
  const OVERRIDE_INFO = 'admin/config/system/mailer/override';

  /**
   * URL for override import all page.
   */
  const IMPORT_ALL = '/admin/config/system/mailer/override/_/import';

  /**
   * URL for override import page for user module.
   */
  const IMPORT_USER = '/admin/config/system/mailer/override/user/import';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['contact', 'user'];

  /**
   * Test sending a test email.
   */
  public function testOverride() {
    $session = $this->assertSession();
    $this->drupalLogin($this->adminUser);

    // Check the override info page with defaults.
    $expected = [
      ['Contact form', 'Disabled', 'Contact form recipients', 'Enable & import'],
      ['Personal contact form', 'Disabled', '', 'Enable'],
      ['User', 'Disabled', 'Update notification addresses', "User email settings\nWarning: This overrides the default HTML messages with imported plain text versions"],
      ['*All*', '', '', 'Enable & import'],
    ];
    $this->drupalGet(self::OVERRIDE_INFO);
    $this->checkOverrideInfo($expected);
    $session->linkByHrefExists(self::IMPORT_ALL);

    // Import all.
    $this->drupalGet(self::IMPORT_ALL);
    $session->pageTextContains('Import unavailable for Personal contact form');
    $session->pageTextContains('Import skipped for User: This overrides the default HTML messages with imported plain text versions');
    $session->pageTextContains('Run enable for override Personal contact form');
    $session->pageTextContains('Run import for override Contact form');
    $session->pageTextContains('Run enable for override User');
    $session->pageTextContains('Importing overwrites existing policy.');
    $this->submitForm([], 'Enable & import');

    // Check the override info page again.
    $expected[0][1] = 'Enabled & Imported';
    $expected[0][3] = 'Re-import';
    $expected[1][1] = $expected[2][1] = 'Enabled';
    $expected[1][3] = $expected[2][3] = 'Reset';
    $session->pageTextContains('Completed Enable & import for all overrides');
    $this->checkOverrideInfo($expected);

    // Import all again - nothing to do.
    $this->drupalGet(self::IMPORT_ALL);
    $session->pageTextContains('No available actions');
    $button = $this->getSession()->getPage()->findButton('Enable & import');
    $this->assertTrue($button->hasAttribute('disabled'));
    $this->clickLink('Cancel');

    // Force import the user override.
    $session->linkByHrefExists(self::IMPORT_USER);
    $this->drupalGet(self::IMPORT_USER);
    $session->pageTextContains('This overrides the default HTML messages with imported plain text versions');
    $this->submitForm([], 'Import');

    // Check the override info page again.
    $expected[2][1] = 'Enabled & Imported';
    $expected[2][3] = 'Re-import';
    $session->pageTextContains('Completed import for override User');
    $this->checkOverrideInfo($expected);
  }

  /**
   * Checks the override info page.
   *
   * @param array $expected
   *   Array of expected table cell contents.
   */
  protected function checkOverrideInfo(array $expected) {
    $this->assertSession()->addressEquals(self::OVERRIDE_INFO);
    foreach ($this->xpath('//tbody/tr') as $row) {
      $expected_row = array_pop($expected);
      foreach ($row->find('xpath', '/td') as $cell) {
        $expected_cell = array_pop($expected_row);
        $this->assertEquals($expected_cell, $cell->getText());
      }
    }
  }

}
