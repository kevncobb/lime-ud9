<?php

namespace Drupal\Tests\expand_collapse_formatter\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the JavaScript functionality of the expand_collapse_formatter.
 *
 * @group expand_collapse_formatter
 */
class ExpandCollapseFormatterTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'text',
    'node',
    'expand_collapse_formatter',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'page']);

    $account = $this->drupalCreateUser([
      'create page content',
      'edit own page content',
    ]);
    $this->drupalLogin($account);
  }

  /**
   * Tests if collapse link is shown.
   */
  public function testCollapseBehavior() {
    $settings = [];

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');
    $display_repository->getViewDisplay('node', 'page', 'full')
      ->setComponent('body', [
        'type' => 'expand_collapse_formatter',
        'settings' => $settings,
      ])
      ->save();

    $text = 'Ecerepere velitaero enihilicit, odi blatum facipsam velecti rerferor aute nonectus doluption parum fugit, adio cumquo di berferiat evel molutae cereperae vendae eum es debis et velendit, utaeperis si doluptas quam faceat. Sum idestem exersped et fugia cus eium solore dipsa quam, cusam fugit eati oditis dus, vercit es ea quas sum, necupta solecum eosapid ullabore conecepta num audiant, simusci ustiatiur sum fugit fugit et eatur alicil magnam, sed et parchitin perio et aliquid min re cor aceptati a commoditior aperis es simus venti odit laut autatqu atemqui nobis et que voluptatem nonse officiatus.';
    $trimmed_text = 'Ecerepere velitaero enihilicit, odi blatum facipsam velecti rerferor aute nonectus doluption parum fugit, adio cumquo di berferiat evel molutae cereperae vendae eum es debis et velendit, utaeperis si doluptas quam faceat. Sum idestem exersped et fugia cus eium solore dipsa quam, cusam ...';

    $node = $this->createNode([
      'body' => [
        [
          'value' => $text,
          'summary' => $this->randomMachineName(32),
          'format' => filter_default_format(),
        ],
      ],
    ]);

    $this->drupalGet('node/1');
    $assert_session = $this->assertSession();

    // Assert that there is a 'Show more', but not a 'Show less' link.
    $assert_session->linkExists('Show more');
    $assert_session->linkNotExists('Show less');

    // Assert that the trimmed text gets displayed, but not the full text.
    $assert_session->pageTextContains($trimmed_text);
    $assert_session->pageTextNotContains($text);

    // Now click the 'Show more' link.
    $this->clickLink('Show more');
    $assert_session->waitForElementVisible('css', '.ecf-close');

    // Assert that there's now a link called 'Show less' and assert that the
    // link 'Show more' is gone'.
    $assert_session->linkExists('Show less');
    $assert_session->linkNotExists('Show more');

    // Assert that the full text now gets displayed and that the trimmed text is
    // gone.
    $assert_session->pageTextContains($text);
    $assert_session->pageTextNotContains($trimmed_text);

    // Click the 'Show less' button.
    $this->clickLink('Show less');
    $assert_session->waitForElementVisible('css', '.ecf-open');

    // Assert that the button text changed back to 'Show more' and that the
    // trimmed text is displayed again.
    $assert_session->linkExists('Show more');
    $assert_session->linkNotExists('Show less');
    $assert_session->pageTextContains($trimmed_text);
    $assert_session->pageTextNotContains($text);
  }

}
