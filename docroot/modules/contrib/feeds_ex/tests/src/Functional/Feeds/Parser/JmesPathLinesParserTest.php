<?php

namespace Drupal\Tests\feeds_ex\Functional\Feeds\Parser;

/**
 * @coversDefaultClass \Drupal\feeds_ex\Feeds\Parser\JmesPathLinesParser
 * @group feeds_ex
 */
class JmesPathLinesParserTest extends ParserTestBase {

  /**
   * The ID of the parser to test.
   *
   * @var string
   */
  protected $parserId = 'jmespathlines';

  /**
   * Does a basic mapping test.
   */
  public function testMapping() {
    $this->doMappingTest();
  }

}
