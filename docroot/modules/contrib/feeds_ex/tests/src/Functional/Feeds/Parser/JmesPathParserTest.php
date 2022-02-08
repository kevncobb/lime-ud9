<?php

namespace Drupal\Tests\feeds_ex\Functional\Feeds\Parser;

/**
 * @coversDefaultClass \Drupal\feeds_ex\Feeds\Parser\JmesPathParser
 * @group feeds_ex
 */
class JmesPathParserTest extends ParserTestBase {

  use ContextTestTrait;

  /**
   * The ID of the parser to test.
   *
   * @var string
   */
  protected $parserId = 'jmespath';

  /**
   * {@inheritdoc}
   */
  public function dataProviderValidContext() {
    return [
      ['items'],
      ['length(people)'],
      ['sort_by(people, &age)'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function dataProviderInvalidContext() {
    return [
      ['!! ', 'Syntax error at character'],
    ];
  }

  /**
   * Tests mapping validation.
   */
  public function testInvalidMappingSource() {
    $this->drupalGet('/admin/structure/feeds/manage/' . $this->feedType->id() . '/mapping');

    // First, set context.
    $edit = [
      'context' => '@',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Now try to configure an invalid mapping source.
    $edit = [
      'mappings[1][map][value][select]' => '__new',
      // Invalid source expression. Closing bracket is missing.
      'mappings[1][map][value][__new][value]' => 'items[].join(`__`,[title,description)',
      'mappings[1][map][value][__new][machine_name]' => 'title_desc',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Assert that a warning is displayed.
    $this->assertSession()->pageTextContains('Syntax error at character');

    // Now check the parser configuration.
    $this->feedType = $this->reloadEntity($this->feedType);
    $this->assertEquals([], $this->feedType->getParser()->getConfiguration('sources'));
  }

  /**
   * Tests an import with an invalid source expression.
   */
  public function testImportWithInvalidExpression() {
    // Add body field.
    node_add_body_field($this->nodeType);

    // Create a feed type with an invalid jmespath source value.
    $feed_type = $this->createFeedType([
      'parser' => 'jmespath',
      'parser_configuration' => [
        'context' => [
          'value' => '@',
        ],
        'sources' => [
          'title' => [
            'label' => 'Title',
            'value' => 'items[].title',
          ],
          'title_desc' => [
            'label' => 'Title and description',
            // Invalid source expression. Closing bracket is missing.
            'value' => 'items[].join(`__`,[title,description)',
          ],
        ],
      ],
      'custom_sources' => [
        'title' => [
          'label' => 'Title',
          'value' => 'items[].title',
          'machine_name' => 'title',
        ],
        'title_desc' => [
          'label' => 'Title and description',
          // Invalid source expression. Closing bracket is missing.
          'value' => 'items[].join(`__`,[title,description)',
          'machine_name' => 'title_desc',
        ],
      ],
      'mappings' => [
        [
          'target' => 'title',
          'map' => ['value' => 'title'],
          'unique' => ['value' => TRUE],
        ],
        [
          'target' => 'body',
          'map' => ['value' => 'title_desc'],
          'settings' => [
            'format' => 'plain_text',
          ],
        ],
      ],
    ]);

    // And try to do a batch import.
    $feed = $this->createFeed($feed_type->id(), [
      'source' => $this->resourcesUrl() . '/test.json',
    ]);
    $this->batchImport($feed);

    // And assert that it failed gracefully.
    $this->assertSession()->pageTextContains('There are no new Article items.');
    $this->assertSession()->pageTextContains('Syntax error at character');
  }

}
