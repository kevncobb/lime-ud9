<?php

namespace Drupal\Tests\feeds_ex\Functional\Feeds\Parser;

/**
 * @coversDefaultClass \Drupal\feeds_ex\Feeds\Parser\JsonPathParser
 * @group feeds_ex
 */
class JsonPathParserTest extends ParserTestBase {

  use ContextTestTrait;

  /**
   * The ID of the parser to test.
   *
   * @var string
   */
  protected $parserId = 'jsonpath';

  /**
   * {@inheritdoc}
   */
  public function dataProviderValidContext() {
    return [
      ['$.items.*'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function dataProviderInvalidContext() {
    return [
      ['.hello*', 'Unable to parse token hello* in expression: .hello*'],
      ['!!', 'Unable to parse token !! in expression: .!!'],
    ];
  }

  /**
   * Tests mapping validation.
   */
  public function testInvalidMappingSource() {
    $this->drupalGet('/admin/structure/feeds/manage/' . $this->feedType->id() . '/mapping');

    // First, set context.
    $edit = [
      'context' => '$.items.*',
    ];

    $this->drupalPostForm('/admin/structure/feeds/manage/' . $this->feedType->id() . '/mapping', $edit, 'Save');

    // Now setup bad mapping.
    $edit = [
      'mappings[1][map][value][select]' => '__new',
      'mappings[1][map][value][__new][value]' => '.hello*',
      'mappings[1][map][value][__new][machine_name]' => '_hello_',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Assert that a warning is displayed.
    $this->assertSession()->pageTextContains('Unable to parse token hello* in expression: .hello*');

    // Now check the parser configuration.
    $this->feedType = $this->reloadEntity($this->feedType);
    $this->assertEquals([], $this->feedType->getParser()->getConfiguration('sources'));
  }

}
