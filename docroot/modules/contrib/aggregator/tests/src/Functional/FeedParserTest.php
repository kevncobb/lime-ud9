<?php

namespace Drupal\Tests\aggregator\Functional;

use Drupal\aggregator\FeedStorageInterface;
use Drupal\aggregator\ItemInterface;
use Drupal\Core\Url;
use Drupal\aggregator\Entity\Feed;
use Drupal\aggregator\Entity\Item;

/**
 * Tests the built-in feed parser with valid feed samples.
 *
 * @group aggregator
 */
class FeedParserTest extends AggregatorTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Do not delete old aggregator items during these tests, since our sample
    // feeds have hardcoded dates in them (which may be expired when this test
    // is run).
    $this->config('aggregator.settings')->set('items.expire', FeedStorageInterface::CLEAR_NEVER)->save();
  }

  /**
   * Tests a feed that uses the RSS 0.91 format.
   */
  public function testRSS091Sample() {
    $feed = $this->createFeed($this->getRSS091Sample());
    $feed->refreshItems();
    $this->drupalGet('aggregator/sources/' . $feed->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('First example feed item title');
    $this->assertSession()->linkByHrefExists('http://example.com/example-turns-one');
    $this->assertSession()->pageTextContains('First example feed item description.');
    $this->assertSession()->responseContains('<img src="http://example.com/images/druplicon.png"');

    // Several additional items that include elements over 255 characters.
    $this->assertSession()->pageTextContains("Second example feed item title.");
    $this->assertSession()->pageTextContains('Long link feed item title');
    $this->assertSession()->pageTextContains('Long link feed item description');
    $this->assertSession()->linkByHrefExists('http://example.com/tomorrow/and/tomorrow/and/tomorrow/creeps/in/this/petty/pace/from/day/to/day/to/the/last/syllable/of/recorded/time/and/all/our/yesterdays/have/lighted/fools/the/way/to/dusty/death/out/out/brief/candle/life/is/but/a/walking/shadow/a/poor/player/that/struts/and/frets/his/hour/upon/the/stage/and/is/heard/no/more/it/is/a/tale/told/by/an/idiot/full/of/sound/and/fury/signifying/nothing');
    $this->assertSession()->pageTextContains('Long author feed item title');
    $this->assertSession()->pageTextContains('Long author feed item description');
    $this->assertSession()->linkByHrefExists('http://example.com/long/author');

    // Test author fields.
    $items = \Drupal::entityTypeManager()->getStorage('aggregator_item')->loadByProperties(['title' => 'Long author feed item title.']);
    $this->assertCount(1, $items);
    $item = reset($items);
    assert($item instanceof ItemInterface);
    $this->assertStringContainsString('I wanted to get out and walk eastward toward', $item->getAuthor());

    $items = \Drupal::entityTypeManager()->getStorage('aggregator_item')->loadByProperties(['title' => 'laminas-feed compatible author']);
    $this->assertCount(1, $items);
    $item = reset($items);
    assert($item instanceof ItemInterface);
    $this->assertStringContainsString('John Doe', $item->getAuthor());

    // Assert that the item with the empty <author> tag was parsed.
    $items = \Drupal::entityTypeManager()->getStorage('aggregator_item')->loadByProperties(['title' => 'Empty author feed item title.']);
    $this->assertCount(1, $items);
    $item = reset($items);
    assert($item instanceof ItemInterface);
    $this->assertSame(NULL, $item->getAuthor());
  }

  /**
   * Tests a feed that uses the Atom format.
   */
  public function testAtomSample() {
    $feed = $this->createFeed($this->getAtomSample());
    $feed->refreshItems();
    $this->drupalGet('aggregator/sources/' . $feed->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Atom-Powered Robots Run Amok');
    $this->assertSession()->linkByHrefExists('http://example.org/2003/12/13/atom03');
    $this->assertSession()->pageTextContains('Some text.');
    $item_ids = \Drupal::entityQuery('aggregator_item')
      ->accessCheck(FALSE)
      ->condition('link', 'http://example.org/2003/12/13/atom03')
      ->execute();
    $item = Item::load(array_values($item_ids)[0]);
    $this->assertEquals('urn:uuid:1225c695-cfb8-4ebb-aaaa-80da344efa6a', $item->getGuid(), 'Atom entry id element is parsed correctly.');

    // Check for second feed entry.
    $this->assertSession()->pageTextContains('We tried to stop them, but we failed.');
    $this->assertSession()->linkByHrefExists('http://example.org/2003/12/14/atom03');
    $this->assertSession()->pageTextContains('Some other text.');
    $item_ids = \Drupal::entityQuery('aggregator_item')
      ->accessCheck(FALSE)
      ->condition('link', 'http://example.org/2003/12/14/atom03')
      ->execute();
    $item = Item::load(array_values($item_ids)[0]);
    $this->assertEquals('urn:uuid:1225c695-cfb8-4ebb-bbbb-80da344efa6a', $item->getGuid(), 'Atom entry id element is parsed correctly.');
  }

  /**
   * Tests a feed that uses HTML entities in item titles.
   */
  public function testHtmlEntitiesSample() {
    $feed = $this->createFeed($this->getHtmlEntitiesSample());
    $feed->refreshItems();
    $this->drupalGet('aggregator/sources/' . $feed->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains("Quote&quot; Amp&amp;");
  }

  /**
   * Tests that a redirected feed is tracked to its target.
   */
  public function testRedirectFeed() {
    $test_cases = [
      '301' => [
        'route' => 'aggregator_test.feed',
        'parameters' => [],
      ],
      '302' => [
        'route' => 'aggregator_test.redirect',
        'parameters' => ['status_code' => 302],
      ],
      '307' => [
        'route' => 'aggregator_test.redirect',
        'parameters' => ['status_code' => 307],
      ],
      '308' => [
        'route' => 'aggregator_test.feed',
        'parameters' => [],
      ],
    ];

    foreach ($test_cases as $status_code => $expected_url_params) {
      $parameters = ['status_code' => $status_code];
      $redirect_url = Url::fromRoute('aggregator_test.redirect', $parameters)->setAbsolute()->toString();
      $feed = Feed::create([
        'url' => $redirect_url,
        'title' => $this->randomMachineName(),
      ]);
      $feed->save();
      $feed->refreshItems();

      // The feed URL should be updated in the case of a 301 or 308 status, but
      // not in the case of 302 or 307.
      $expected_url = Url::fromRoute(
        $expected_url_params['route'],
        $expected_url_params['parameters'],
        ['absolute' => TRUE]
      )->toString();
      $this->assertSame($expected_url, $feed->getUrl());
    }
  }

  /**
   * Tests error handling when an invalid feed is added.
   */
  public function testInvalidFeed() {
    // Simulate a typo in the URL to force a curl exception.
    $invalid_url = 'https:/www.drupal.org';
    $feed = Feed::create(['url' => $invalid_url, 'title' => $this->randomMachineName()]);
    $feed->save();

    // Update the feed. Use the UI to be able to check the message easily.
    $this->drupalGet('admin/config/services/aggregator');
    $this->clickLink('Update items');
    $this->assertSession()->pageTextContains('The feed from ' . $feed->getUrl() . ' seems to be broken because of error');
  }

}
