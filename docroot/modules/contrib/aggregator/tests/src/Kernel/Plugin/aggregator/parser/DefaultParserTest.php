<?php

namespace Drupal\Tests\aggregator\Kernel\Plugin\aggregator\parser;

use Drupal\aggregator\Entity\Feed;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\aggregator\Plugin\aggregator\parser\DefaultParser
 * @group aggregator
 */
class DefaultParserTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['aggregator', 'options'];

  /**
   * An instance of the DefaultParser plugin.
   *
   * @var \Drupal\aggregator\Plugin\aggregator\parser\DefaultParser
   */
  protected $parser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('aggregator_feed');
    $this->installEntitySchema('aggregator_item');

    /** @var \Drupal\aggregator\Plugin\AggregatorPluginManager $plugin_manager */
    $plugin_manager = $this->container->get('plugin.manager.aggregator.parser');
    /** @var \Drupal\aggregator\Plugin\aggregator\parser\DefaultParser $parser */
    $this->parser = $plugin_manager->createInstance('aggregator');
  }

  /**
   * @covers ::parse()
   *
   * Test that post-dated item dates are corrected. A NULL value in
   * $expected_result indicates that it should be the request time.
   *
   * @dataProvider provideDateData
   */
  public function testParsePostDatedItem(string $date, ?bool $normalize, ?int $expected_result) {
    if ($expected_result === NULL) {
      $expected_result = \Drupal::time()->getRequestTime();
    }

    $feed = Feed::create([
      'title' => 'Processor test feed',
      'url' => 'https://example.com/rss.xml',
      'source_string' => <<<EOT
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="0.91">
          <channel>
            <title>Example</title>
            <link>https://example.com/</link>
            <item>
              <title>Example Item</title>
              <link>https://example.com/</link>
              <pubDate>$date</pubDate>
            </item>
          </channel>
        </rss>
        EOT,
    ]);
    $feed->save();

    $config = \Drupal::configFactory()->getEditable('aggregator.settings');
    $config->set('normalize_post_dates', $normalize);
    $config->save();

    $this->parser->parse($feed);

    $this->assertSame($expected_result, $feed->items[0]['timestamp']);
  }

  /**
   * The data provider for testParseDateModified().
   *
   * @return array
   */
  public function provideDateData(): array {
    return [
      'past_date_not_normalized' => [
        'Sat, Jan 01 2000 00:00:00 -0500',
        FALSE,
        946702800,
      ],
      'past_date_normalized' => [
        'Sat, Jan 01 2000 00:00:00 -0500',
        TRUE,
        946702800,
      ],
      'future_date_not_normalized' => [
        'Sat, Jan 01 3000 00:00:00 -0500',
        FALSE,
        32503957200,
      ],
      'future_date_normalized' => [
        'Sat, Jan 01 3000 00:00:00 -0500',
        TRUE,
        NULL,
      ],
      'null_setting' => [
        'Sat, Jan 01 3000 00:00:00 -0500',
        NULL,
        32503957200,
      ],
    ];
  }

}
