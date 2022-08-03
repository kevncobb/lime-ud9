<?php

namespace Drupal\Tests\symfony_mailer_bc\Functional;

use Drupal\Tests\symfony_mailer\Functional\SymfonyMailerTestBase;

/**
 * Test the Symfony Mailer Back-compatibility module.
 *
 * @group symfony_mailer
 */
class TestBcMail extends SymfonyMailerTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'symfony_mailer',
    'symfony_mailer_bc',
    'symfony_mailer_test',
    'symfony_mailer_bc_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser([
      'administer mailer',
      'administer themes',
      'view the administration theme',
    ]);
  }

  /**
   * Test sending a backwards compatible email rendered via hook_mail().
   */
  public function testSendBcMail() {
    $this->drupalLogin($this->adminUser);
    // Trigger sending a backwards compatible email via hook_mail().
    $this->drupalGet('admin/symfony_mailer_bc_test/send');
    $this->submitForm([], 'Send test mail');
    $this->readMail();

    // Check email recipients.
    $this->assertTo('test@example.com', '');
    $this->assertCc('cc@example.com', '');

    // Check email contents.
    $this->assertSubject("Backwards compatible mail sent via hook_mail().");
    $this->assertBodyContains("This email is sent via hook_mail().");
    $this->assertBodyContains("This is the default BC test mail template.");
    $this->assertBodyContains("Rendered in theme: stark");
  }

  /**
   * Test sending a backwards compatible email with custom email body template.
   */
  public function testSendBcMailWithTheme() {
    $this->drupalLogin($this->adminUser);
    // Switch the current default theme and admin theme, and test if template
    // renders in the default theme instead of the admin theme, even though the
    // admin theme is active when the mail gets triggered.
    \Drupal::service('theme_installer')->install(['test_bc_mail_theme', 'stark']);
    $this->config('system.theme')
      ->set('default', 'test_bc_mail_theme')
      ->set('admin', 'stark')
      ->save();

    /** @var \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler */
    $theme_handler = $this->container->get('theme_handler');
    $this->assertTrue($theme_handler->themeExists('test_bc_mail_theme'));

    // Trigger sending a backwards compatible email via hook_mail().
    $this->drupalGet('admin/symfony_mailer_bc_test/send');
    $this->assertSession()->pageTextContains('Current theme: stark');
    $this->submitForm([], 'Send test mail');
    $this->readMail();

    // Check email recipients.
    $this->assertTo('test@example.com', '');
    $this->assertCc('cc@example.com', '');

    // Check email contents.
    $this->assertSubject("Backwards compatible mail sent via hook_mail().");
    $this->assertBodyContains("This email is sent via hook_mail().");
    $this->assertBodyContains("This is the overridden BC test mail template.");
    $this->assertBodyContains("Rendered in theme: test_bc_mail_theme");
  }

}
