<?php

namespace Drupal\Tests\nomarkup\FunctionalJavascript;

use Behat\Mink\Exception\ElementNotFoundException;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * A nomarkup integration test.
 *
 * @group nomarkup
 */
class IntegrationTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'field', 'field_ui', 'nomarkup'];

  /**
   * An admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $this->node = $this->drupalCreateNode([
      'title' => $this->randomString(),
      'type' => 'article',
      'body' => 'Body field value.',
    ]);
    $this->adminUser = $this->drupalCreateUser(['access content', 'administer node display']);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test the basic settings.
   */
  public function testBasicSettings() {
    $session = $this->assertSession();
    $manage_display = '/admin/structure/types/manage/article/display';
    $this->drupalGet($manage_display);

    $this->submitForm([], 'body_settings_edit');
    $session->assertWaitOnAjaxRequest();

    $this->submitForm([
      'fields[body][label]' => 'above',
      'fields[body][settings_edit_form][third_party_settings][nomarkup][enabled]' => true,
    ], 'Update');
    $session->assertWaitOnAjaxRequest();

    $this->submitForm([], 'Save');

    $this->drupalGet('/node/' . $this->node->id());
    try {
      $session->elementExists('css', '.field--name-body');
      throw new \AssertionError('Field wrapper should be skipped.');
    }
    catch (ElementNotFoundException $exception) {
      $this->assertSame('Element matching css ".field--name-body" not found.', $exception->getMessage());
    }
    $session->pageTextContainsOnce('Body field value.');
  }

}
