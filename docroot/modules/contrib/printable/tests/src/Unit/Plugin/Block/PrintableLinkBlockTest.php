<?php

namespace Drupal\Tests\printable\Unit\Plugin\Block;

use Drupal\Tests\UnitTestCase;
use Drupal\printable\Plugin\Block\PrintableLinksBlock;

/**
 * Tests the printable links block plugin.
 *
 * @group Printable
 */
class PrintableLinkBlockTest extends UnitTestCase {

  /**
   * Configuration.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * Plugin ID.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * Plugin definition.
   *
   * @var array
   */
  protected $pluginDefinition = [];

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct();
    $this->pluginId = 'printable_links_block:node';
    $this->pluginDefinition['module'] = 'printable';
    $this->pluginDefinition['provider'] = '';
  }

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Printable Block',
      'descriptions' => 'Tests the printable block plugin class.',
      'group' => 'Printable',
    ];
  }

  /**
   * Tests the block build method.
   *
   * @covers PrintableLinksBlock::build
   */
  public function testBuild() {
    $routematch = $this->createMock('Drupal\Core\Routing\CurrentRouteMatch');
    $routematch->expects($this->exactly(2))
      ->method('getMasterRouteMatch')
      ->will($this->returnSelf());
    $routematch->expects($this->exactly(2))
      ->method('getParameter')
      ->will($this->returnValue($this->createMock('Drupal\Core\Entity\EntityInterface')));
    $links = [
      'title' => 'Print',
      'url' => '/foo/1/printable/print',
      'attributes' => [
        'target' => '_blank',
      ],
    ];
    $links_builder = $this->createMock('Drupal\printable\PrintableLinkBuilderInterface');
    $links_builder->expects($this->once())
      ->method('buildLinks')
      ->will($this->returnValue($links));

    $dateFormatter = $this->getMockBuilder('Drupal\Core\Datetime\DateFormatter')
      ->disableOriginalConstructor()
      ->getMock();

    $entityTypeManager = $this->getMockBuilder('Drupal\Core\Entity\EntityTypeManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $block = new PrintableLinksBlock($this->configuration, $this->pluginId, $this->pluginDefinition, $routematch, $links_builder, $dateFormatter, $entityTypeManager);

    $expected_build = [
      '#theme' => 'links__entity__printable',
      '#links' => $links,
      '#cache' => [
        'contexts' => ['route'],
        'tags' => ['node:'],
        'max-age' => 180,
      ],
    ];
    $this->assertEquals($expected_build, $block->build());
  }

}
