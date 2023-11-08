<?php

declare(strict_types=1);

namespace Drupal\Tests\viewsreference\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use function version_compare;

/**
 * Test viewsreference upgrade path 8.x-1.x (8102) to 8.x-2.x (8103).
 *
 * @group viewsreference
 */
class ViewsReferenceUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    if (version_compare(\Drupal::VERSION, '10', '>=')) {
      $this->databaseDumpFiles = [
        DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
      ];
    }
    else {
      // Simple drupal standard + field viewsreference in CT page and 1 node
      // with 2 revisions. Similar for the term entity.
      $this->databaseDumpFiles = [
        __DIR__ . '/../../../fixtures/update/drupal-8.9.20.viewsreference-8.x-1.4.php.gz',
        __DIR__ . '/../../../fixtures/update/data_view_mode.php.gz',
      ];
    }
  }

  /**
   * Tests the update hook 8103.
   */
  public function testUpdateHook8103(): void {
    if (version_compare(\Drupal::VERSION, '10', '>=')) {
      $this->markTestSkipped('Test can only be run in Drupal core 9.x');
    }
    $connection = $this->container->get('database');
    /** @var \Drupal\Core\Database\Schema $schema */
    $schema = $connection->schema();
    /** @var \Drupal\Core\Update\UpdateHookRegistry $update_hook_registry */
    $update_hook_registry = $this->container->get('update.update_hook_registry');

    // We do not want a false-positive therefore we check what needs to be there
    // before we run the test.
    $columns = ['field_viewsreference_argument', 'field_viewsreference_title'];
    foreach ($columns as $column) {
      $this->assertTrue($schema->fieldExists('node__field_viewsreference', $column));
      $this->assertTrue($schema->fieldExists('node_revision__field_viewsreference', $column));
    }
    $columns_term = [
      'field_viewsreference_viewsrefere_argument',
      'field_viewsreference_viewsrefere_title',
    ];
    foreach ($columns_term as $column) {
      $this->assertTrue($schema->fieldExists('taxonomy_term__field_viewsreference_viewsrefere', $column));
      $this->assertTrue($schema->fieldExists('taxonomy_term_r__ab4292d2b7', $column));
    }

    $query = $connection->select('node__field_viewsreference', 'vr');
    $query->addField('vr', 'field_viewsreference_data');
    $result = $query->execute();
    $result_data = $result->fetchField();
    $this->assertEquals('a:1:{s:9:"view_mode";s:12:"preview_wide";}', $result_data);

    // Test revision 1 without data value.
    $query = $connection->select('node_revision__field_viewsreference', 'vr');
    $query->addField('vr', 'field_viewsreference_data');
    $query->condition('revision_id', 1);
    $result = $query->execute();
    $result_data = $result->fetchField();
    $this->assertNull($result_data, 'Revisions data is NULL');

    // Test revision 1 with data value.
    $query = $connection->select('node_revision__field_viewsreference', 'vr');
    $query->addField('vr', 'field_viewsreference_data');
    $query->condition('revision_id', 2);
    $result = $query->execute();
    $result_data = $result->fetchField();
    $this->assertEquals('a:1:{s:9:"view_mode";s:12:"preview_wide";}', $result_data, 'Revisions data contains data');

    $this->assertSame(8102, $update_hook_registry->getInstalledVersion('viewsreference'));
    $this->runUpdates();
    $this->assertSame(8103, $update_hook_registry->getInstalledVersion('viewsreference'));

    foreach ($columns as $column) {
      $this->assertFalse($schema->fieldExists('node__field_viewsreference', $column));
      $this->assertFalse($schema->fieldExists('node_revision__field_viewsreference', $column));
    }
    foreach ($columns_term as $column) {
      $this->assertFalse($schema->fieldExists('taxonomy_term__field_viewsreference_viewsrefere', $column));
      $this->assertFalse($schema->fieldExists('taxonomy_term_r__ab4292d2b7', $column));
    }

    /** @var \Drupal\node\NodeStorageInterface $storage */
    $storage = $this->container->get('entity_type.manager')->getStorage('node');

    $node = $storage->loadRevision(1);
    $value = $node->get('field_viewsreference')->first()->getValue();
    $this->assertEquals([
      'target_id' => 'content_recent',
      'display_id' => 'block_1',
      'data' => 'a:2:{s:5:"title";s:1:"1";s:8:"argument";s:4:"test";}',
    ], $value);

    $node = $storage->loadRevision(2);
    $value = $node->get('field_viewsreference')->first()->getValue();
    // phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
    $this->assertEquals([
      'target_id' => 'content_recent',
      'display_id' => 'block_1',
      'data' => 'a:3:{s:9:"view_mode";s:12:"preview_wide";s:5:"title";s:1:"1";s:8:"argument";s:4:"test";}',
    ], $value);
    //phpcs:enable
    $storage = $this->container->get('entity_type.manager')->getStorage('taxonomy_term');
    $term = $storage->load(1);
    $value = $term->get('field_viewsreference_viewsrefere')->first()->getValue();
    $this->assertEquals([
      'target_id' => 'content_recent',
      'display_id' => 'block_1',
      'data' => 'a:2:{s:5:"title";s:1:"1";s:8:"argument";s:4:"test";}',
    ], $value);
  }

}
