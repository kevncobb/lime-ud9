<?php

namespace Drupal\Tests\feeds_ex\Functional\Feeds\Parser;

/**
 * @coversDefaultClass \Drupal\feeds_ex\Feeds\Parser\JsonPathLinesParser
 * @group feeds_ex
 */
class JsonPathLinesParserTest extends ParserTestBase {

  /**
   * The ID of the parser to test.
   *
   * @var string
   */
  protected $parserId = 'jsonpathlines';

  /**
   * Does a basic mapping test.
   */
  public function testMapping() {
    $this->doMappingTest();
  }

}
