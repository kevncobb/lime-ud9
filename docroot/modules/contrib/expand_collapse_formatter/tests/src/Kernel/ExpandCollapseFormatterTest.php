<?php

namespace Drupal\Tests\expand_collapse_formatter\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;

/**
 * Tests the formatter "expand_collapse_formatter".
 *
 * @group expand_collapse_formatter
 */
class ExpandCollapseFormatterTest extends FieldKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'user',
    'system',
    'field',
    'text',
    'filter',
    'entity_test',
    'expand_collapse_formatter',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create the necessary formats.
    $this->installConfig(['filter']);
    FilterFormat::create([
      'format' => 'no_filters',
      'name' => 'No filters',
      'filters' => [],
    ])->save();
  }

  /**
   * Tests the html output when using the expand_collapse_formatter formatter.
   *
   * @param string[] $expected
   *   A list of expected html to be found in the rendered output.
   * @param array $settings
   *   Configuration for the expand_collapse_formatter formatter.
   *
   * @dataProvider htmlOutputDataProvider
   */
  public function testHtmlOutput(array $expected, array $settings = []) {
    // Create a field.
    $field_name = mb_strtolower($this->randomMachineName());
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'entity_test',
      'type' => 'text_long',
    ]);
    $field_storage->save();
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'entity_test',
      'label' => $this->randomMachineName() . '_label',
    ])->save();

    // Setup display.
    $display = EntityViewDisplay::create([
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent($field_name, [
      'type' => 'expand_collapse_formatter',
      'settings' => $settings,
    ]);
    $display->save();

    // Create an entity.
    $entity = EntityTest::create([
      $field_name => [
        'value' => 'Ecerepere velitaero enihilicit, odi blatum facipsam velecti rerferor aute nonectus doluption parum fugit, adio cumquo di berferiat evel molutae cereperae vendae eum es debis et velendit, utaeperis si doluptas quam faceat.',
        'format' => 'no_filters',
      ],
    ]);
    $entity->save();

    // Check the html output.
    $build = $entity->{$field_name}->view('default');
    $rendered_entity = (string) $this->container->get('renderer')->renderRoot($build);
    foreach ($expected as $string) {
      $this->assertStringContainsString($string, $rendered_entity);
    }
  }

  /**
   * Data provider for testHtmlOutput().
   */
  public function htmlOutputDataProvider(): array {
    return [
      'default settings' => [
        'expected' => [
          'class="expand-collapse"',
          'data-trim-length="300"',
          'data-default-state="collapsed"',
          'data-link-text-open="Show more"',
          'data-link-text-close="Show less"',
          'data-link-class-open="ecf-open"',
          'data-link-class-close="ecf-close"',
        ],
        'settings' => [],
      ],
      'custom settings' => [
        'expected' => [
          'class="expand-collapse"',
          'data-trim-length="450"',
          'data-default-state="expanded"',
          'data-link-text-open="Read more"',
          'data-link-text-close="Hide"',
          'data-link-class-open="foo"',
          'data-link-class-close="bar"',
        ],
        'settings' => [
          'trim_length' => 450,
          'default_state' => 'expanded',
          'link_text_open' => 'Read more',
          'link_text_close' => 'Hide',
          'link_class_open' => 'foo',
          'link_class_close' => 'bar',
        ],
      ],
    ];
  }

}
