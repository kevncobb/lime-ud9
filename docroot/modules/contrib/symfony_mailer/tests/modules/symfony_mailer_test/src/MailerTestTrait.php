<?php

namespace Drupal\symfony_mailer_test;

use Drupal\Core\DestructableInterface;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer_test\MailerTestServiceInterface;

/**
 * Tracks sent emails for testing.
 */
trait MailerTestTrait {

  protected $emails;
  protected $email;

  /**
   * Gets the next email, removing it from the list.
   *
   * @return \Symfony\Component\Mime\Email
   *   The email.
   */
  public function readMail() {
    $this->init();
    $this->email = array_shift($this->emails);
    $this->assertNotNull($this->email);
  }

  /**
   * Checks that the most recently sent email contains text.
   *
   * @param string $value
   *   Text to check for.
   *
   * @return $this
   */
  public function assertBodyContains(string $value) {
    $this->assertStringContainsString($value, $this->email->getHtmlBody());
    return $this;
  }

  /**
   * Checks the subject of the most recently sent email.
   *
   * @param string $value
   *   Text to check for.
   *
   * @return $this
   */
  public function assertSubject($value) {
    $this->assertEquals($value, $this->email->getSubject());
    return $this;
  }

  /**
   * Checks the to address of the most recently sent email.
   *
   * @param string $email
   *   The email address.
   * @param string $display_name
   *   (Optional) The display name.
   *
   * @return $this
   */
  public function assertTo(string $email, string $display_name = '') {
    $to = $this->email->getTo();
    $this->assertCount(1, $to);
    $this->assertEquals($email, $to[0]->getEmail());
    $this->assertEquals($this->adminUser->getDisplayName(), $to[0]->getDisplayName());
    return $this;
  }

  /**
   * Checks there are no more emails.
   *
   * @return $this
   */
  protected function noMail() {
    $this->init();
    $this->assertCount(0, $this->emails, 'All emails have been checked.');
    \Drupal::state()->delete(MailerTestServiceInterface::STATE_KEY);
    unset($this->emails);
  }

  protected function init() {
    if (is_null($this->emails)) {
      $this->emails = \Drupal::state()->get(MailerTestServiceInterface::STATE_KEY, []);
    }
  }

}
