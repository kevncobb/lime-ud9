<?php

namespace Drupal\Tests\entity_reference_revisions\Kernel;

use Drupal\entity_composite_relationship_test\Entity\EntityTestCompositeRelationship;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\entity_reference_revisions\Traits\EntityReferenceRevisionsTrait;

/**
 * Tests the entity_reference_revisions NeedsSaveInterface.
 *
 * @group entity_reference_revisions
 */
class EntityReferenceRevisionsSaveTest extends KernelTestBase {

  use EntityReferenceRevisionsTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'user',
    'system',
    'field',
    'entity_reference_revisions',
    'entity_composite_relationship_test',
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create article content type.
    $values = ['type' => 'article', 'name' => 'Article'];
    $node_type = NodeType::create($values);
    $node_type->save();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('entity_test_composite');
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);
  }

  /**
   * Test for NeedsSaveInterface implementation.
   *
   * Tests that the referenced entity is saved when needsSave() is TRUE.
   */
  public function testNeedsSave() {
    $this->generateEntityReferenceRevisionField('node', 'entity_test_composite', 'article');

    $text = 'Dummy text';
    // Create the test composite entity.
    $entity_test = EntityTestCompositeRelationship::create(array(
      'uuid' => $text,
      'name' => $text,
    ));
    $entity_test->save();

    $text = 'Clever text';
    // Set the name to a new text.
    /** @var \Drupal\entity_composite_relationship_test\Entity\EntityTestCompositeRelationship $entity_test */
    $entity_test->name = $text;
    $entity_test->setNeedsSave(TRUE);
    // Create a node with a reference to the test entity and save.
    $node = Node::create([
      'title' => $this->randomMachineName(),
      'type' => 'article',
      'entity_reference_revisions' => $entity_test,
    ]);

    // Check the name is properly set and that getValue() returns the entity
    // when it is marked as needs save."
    $values = $node->entity_reference_revisions->getValue();
    $this->assertTrue(isset($values[0]['entity']));
    static::assertEquals($values[0]['entity']->name->value, $text);
    $node->entity_reference_revisions->setValue($values);
    static::assertEquals($node->entity_reference_revisions->entity->name->value, $text);

    $node->save();

    // Check that the name has been updated when the parent has been saved.
    /** @var \Drupal\entity_composite_relationship_test\Entity\EntityTestCompositeRelationship $entity_test_after */
    $entity_test_after = EntityTestCompositeRelationship::load($entity_test->id());
    static::assertEquals($entity_test_after->name->value, $text);

    $new_text = 'Dummy text again';
    // Set another name and save the node without marking it as needs saving.
    $entity_test_after->name = $new_text;
    $entity_test_after->setNeedsSave(FALSE);

    // Load the Node and check the composite reference entity is not returned
    // from getValue() if it is not marked as needs saving.
    $node = Node::load($node->id());
    $values = $node->entity_reference_revisions->getValue();
    $this->assertFalse(isset($values[0]['entity']));
    $node->entity_reference_revisions = $entity_test_after;
    $node->save();

    // Check the name is not updated.
    \Drupal::entityTypeManager()->getStorage('entity_test_composite')->resetCache();
    $entity_test_after = EntityTestCompositeRelationship::load($entity_test->id());
    static::assertEquals($text, $entity_test_after->name->value);

    // Test if after delete the referenced entity there are no problems setting
    // the referencing values to the parent.
    $entity_test->delete();
    $node = Node::load($node->id());
    $node->save();

    // Test if the needs save variable is set as false after saving.
    $entity_needs_save = EntityTestCompositeRelationship::create([
      'uuid' => $text,
      'name' => $text,
    ]);
    $entity_needs_save->setNeedsSave(TRUE);
    $entity_needs_save->save();
    $this->assertFalse($entity_needs_save->needsSave());
  }

  /**
   * Test for NeedsSaveInterface implementation.
   *
   * Tests that the fields in the parent are properly updated.
   */
  public function testSaveNewEntity() {
    // Add the entity_reference_revisions field to article.
    $this->generateEntityReferenceRevisionField('node', 'entity_test_composite', 'article');

    $text = 'Dummy text';
    // Create the test entity.
    $entity_test = EntityTestCompositeRelationship::create(array(
      'uuid' => $text,
      'name' => $text,
    ));

    // Create a node with a reference to the test entity and save.
    $node = Node::create([
      'title' => $this->randomMachineName(),
      'type' => 'article',
      'entity_reference_revisions' => $entity_test,
    ]);
    $validate = $node->validate();
    $this->assertEmpty($validate);
    $node->save();

    // Test that the fields on node are properly set.
    $node_after = Node::load($node->id());
    static::assertEquals($node_after->entity_reference_revisions[0]->target_id, $entity_test->id());
    static::assertEquals($node_after->entity_reference_revisions[0]->target_revision_id, $entity_test->getRevisionId());
    // Check that the entity is not new after save parent.
    $this->assertFalse($entity_test->isNew());

    // Create a new test entity.
    $text = 'Smart text';
    $second_entity_test = EntityTestCompositeRelationship::create(array(
      'uuid' => $text,
      'name' => $text,
    ));
    $second_entity_test->save();

    // Set the new test entity to the node field.
    $node_after->entity_reference_revisions = $second_entity_test;
    // Check the fields have been updated.
    static::assertEquals($node_after->entity_reference_revisions[0]->target_id, $second_entity_test->id());
    static::assertEquals($node_after->entity_reference_revisions[0]->target_revision_id, $second_entity_test->getRevisionId());
  }

  /**
   * Tests entity_reference_revisions default value and config dependencies.
   */
  public function testEntityReferenceRevisionsDefaultValue() {
    // Create an entity reference field to reference to the test target node.
    $field = $this->generateEntityReferenceRevisionField();

    // Create a test target node used as entity reference by another test node.
    $node_target = Node::create([
      'title' => 'Target node',
      'type' => 'article',
      'body' => 'Target body text',
      'uuid' => '2d04c2b4-9c3d-4fa6-869e-ecb6fa5c9410',
    ]);
    $node_target->save();

    $field->setSettings([
      'required' => FALSE,
      'handler_settings' => [
        'target_bundles' => ['article' => 'article'],
      ]
    ]);
    // Add reference values to field config that will be used as default value.
    $default_value = [
      [
        'target_id' => $node_target->id(),
        'target_revision_id' => $node_target->getRevisionId(),
        'target_uuid' => $node_target->uuid(),
      ],
    ];
    $field->setDefaultValue($default_value)->save();

    // Resave the target node, so that the default revision is not the one we
    // want to use.
    $revision_id = $node_target->getRevisionId();
    $node_target_after = Node::load($node_target->id());
    $node_target_after->setNewRevision();
    $node_target_after->save();
    $this->assertTrue($node_target_after->getRevisionId() != $revision_id);

    // Create another node.
    $node_host = Node::create([
      'title' => 'Host node',
      'type' => 'article',
      'body' => 'Host body text',
      'entity_reference_revisions' => $node_target,
    ]);
    $node_host->save();

    // Check if the ERR default values are properly created.
    $node_host_after = Node::load($node_host->id());
    $this->assertEquals($node_host_after->entity_reference_revisions->target_id, $node_target->id());
    $this->assertEquals($node_host_after->entity_reference_revisions->target_revision_id, $revision_id);

    // Check if the configuration dependencies are properly created.
    $dependencies = $field->calculateDependencies()->getDependencies();
    $this->assertEquals($dependencies['content'][0], 'node:article:2d04c2b4-9c3d-4fa6-869e-ecb6fa5c9410');
    $this->assertEquals($dependencies['config'][0], 'field.storage.node.entity_reference_revisions');
    $this->assertEquals($dependencies['config'][1], 'node.type.article');
    $this->assertEquals($dependencies['module'][0], 'entity_reference_revisions');
  }

  /**
   * Tests FieldType\EntityReferenceRevisionsItem::deleteRevision
   */
  public function testEntityReferenceRevisionsDeleteHandleDeletedChild() {
    $this->generateEntityReferenceRevisionField();

    $child = Node::create([
      'type' => 'article',
      'title' => 'Child node',
    ]);
    $child->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Parent node',
      'entity_reference_revisions' => [
        [
          'target_id' => $child->id(),
          'target_revision_id' => $child->getRevisionId(),
        ],
      ],
    ]);

    // Create two revisions.
    $node->save();
    $revisionId = $node->getRevisionId();
    $node->setNewRevision(TRUE);
    $node->save();

    // Force delete the child Paragraph.
    // Core APIs allow this although it is an inconsistent storage situation
    // for Paragraphs.
    $child->delete();

    // Previously deleting a revision with a lost child failed fatal.
    \Drupal::entityTypeManager()->getStorage('node')->deleteRevision($revisionId);
  }

  /**
   * Tests that a scalar value can be passed as a reference.
   */
  public function testScalarValueTargetId() {
    $this->generateEntityReferenceRevisionField();

    $child = Node::create(['type' => 'article', 'title' => 'Child node']);
    $child->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'Parent node',
      'entity_reference_revisions' => $child->id(),
    ]);

    $node->save();
    $this->assertEquals($node->entity_reference_revisions->first()->getValue(), [
      'target_id' => $child->id(),
      'target_revision_id' => $child->getRevisionId(),
    ]);
    $this->assertEquals($node->entity_reference_revisions->first()->entity->id(), $child->id());
  }

}
