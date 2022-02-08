<?php

namespace Drupal\Tests\feeds_ex\Functional\Feeds\Parser;

use Drupal\Tests\feeds_ex\Functional\FeedsExBrowserTestBase;

/**
 * Base class for parser functional tests.
 */
abstract class ParserTestBase extends FeedsExBrowserTestBase {

  /**
   * The feed type entity.
   *
   * @var \Drupal\feeds\Entity\FeedType
   */
  protected $feedType;

  /**
   * The ID of the parser to test.
   *
   * @var string
   */
  protected $parserId = '';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create a feed type.
    $this->feedType = $this->createFeedType([
      'parser' => $this->parserId,
    ]);
  }

  /**
   * Tests basic mapping.
   */
  public function doMappingTest() {
    $this->drupalGet('/admin/structure/feeds/manage/' . $this->feedType->id() . '/mapping');

    // Set source for title target.
    $edit = [
      'mappings[1][map][value][select]' => '__new',
      'mappings[1][map][value][__new][value]' => 'name',
      'mappings[1][map][value][__new][machine_name]' => 'name',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Now check the parser configuration.
    $this->feedType = $this->reloadEntity($this->feedType);
    $expected_sources = [
      'name' => [
        'label' => 'name',
        'value' => 'name',
      ],
    ];
    $this->assertEquals($expected_sources, $this->feedType->getParser()->getConfiguration('sources'));
  }

}
