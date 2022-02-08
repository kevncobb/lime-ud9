<?php

namespace Drupal\organigrams;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityFieldManager;

/**
 * Loads taxonomy terms in a tree.
 *
 * Thanks to Danny Sipos:
 * https://www.webomelette.com/loading-taxonomy-terms-tree-drupal-8.
 */
class TaxonomyTermTree {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * TaxonomyTermTree constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Contains the entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManager $entityFieldManager
   *   Contains the entity field manager.
   */
  public function __construct(EntityTypeManager $entityTypeManager, EntityFieldManager $entityFieldManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * Loads the tree of a vocabulary.
   *
   * @param string $vocabulary
   *   Contains the vocabulary machine name.
   *
   * @return array
   *   Contains the taxonomy tree.
   */
  public function load($vocabulary) {
    // Load all terms of the vocabulary.
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vocabulary, 0, NULL, TRUE);

    // Define the tree array and iterate through the terms to fill it.
    $tree = [];
    foreach ($terms as $tree_object) {
      $this->buildTree($tree, $tree_object, $vocabulary);
    }

    return $tree;
  }

  /**
   * Populates a tree array given a taxonomy term tree object.
   *
   * @param array $tree
   *   Contains the tree so far.
   * @param object $object
   *   Contains a taxonomy term possibly with children.
   * @param string $vocabulary
   *   Contains the vocabulary machine name.
   */
  protected function buildTree(array &$tree, $object, $vocabulary) {
    // Do nothing when depth is not 0.
    if ($object->depth != 0) {
      return;
    }

    // Add the term to the tree and create a children entry.
    $tree[$object->id()] = $object;
    $tree[$object->id()]->children = [];

    // Reference the tree children to the object children.
    $object_children = &$tree[$object->id()]->children;

    // Load the children of this taxonomy term.
    $children = $this->entityTypeManager->getStorage('taxonomy_term')->loadChildren($object->id());
    // Stop if no children are found.
    if (!$children) {
      return;
    }

    // Iterate through all children and recursively add them to the tree array.
    $child_tree_objects = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vocabulary, $object->id(), NULL, TRUE);
    foreach ($children as $child) {
      foreach ($child_tree_objects as $child_tree_object) {
        if ($child_tree_object->id() == $child->id()) {
          $this->buildTree($object_children, $child_tree_object, $vocabulary);
        }
      }
    }
  }

  /**
   * Loads the tree of a vocabulary and puts it in an item list.
   *
   * @param string $vocabulary
   *   Contains the vocabulary machine name.
   *
   * @return array
   *   Renderable array containing an item list.
   */
  public function loadList($vocabulary) {
    // Get all taxonomy terms.
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vocabulary, 0, NULL, TRUE);

    // Get all taxonomy term fields.
    $fields = $this->entityFieldManager->getFieldDefinitions('taxonomy_term', $vocabulary);

    // Put all fields starting with 'field_o_' in an array.
    $organigram_fields = [];
    foreach ($fields as $field_name => $field) {
      if (substr($field_name, 0, 8) == 'field_o_') {
        $organigram_fields[$field_name] = $field;
      }
    }

    // Build a hierarchical taxonomy term array.
    $items = [];
    foreach ($terms as $tree_object) {
      $this->buildListTree($items, $tree_object, $vocabulary, $organigram_fields);
    }

    // Return an item list.
    return [
      '#theme' => 'item_list',
      '#type' => 'ul',
      '#wrapper_attributes' => [
        'class' => ['organigram', 'organigram-' . $vocabulary],
      ],
      '#items' => $items,
    ];
  }

  /**
   * Populates a tree array with list items given a taxonomy term tree object.
   *
   * @param array $items
   *   The populated tree so far.
   * @param object $object
   *   Contains a taxonomy term.
   * @param string $vocabulary
   *   Contains the machine name of the vocabulary.
   * @param array $fields
   *   Contains the fields to show for taxonomy terms.
   */
  protected function buildListTree(array &$items, $object, $vocabulary, array $fields) {
    // Stop if depth is not 0.
    if ($object->depth != 0) {
      return;
    }

    // Create the list item.
    $items[$object->id()] = [
      '#markup' => $object->getName(),
      '#wrapper_attributes' => [
        'field_o_item_id' => $object->id(),
        'field_o_parent' => $object->get('parent')->target_id,
        'field_o_text' => $object->getName(),
      ],
      'children' => [],
    ];

    // Check if there are fields and iterate through them.
    if (!empty($fields)) {
      foreach ($fields as $field_name => $field_config) {
        // Get the field.
        $field = $object->get($field_name)->first();
        if (empty($field)) {
          continue;
        }

        // Get the field value.
        $field_value = $field->getValue();
        if (empty($field_value['value'])) {
          continue;
        }

        // Add the field with value as attribute to the list item.
        $items[$object->id()]['#wrapper_attributes'][$field_name] = $field_value['value'];
      }
    }

    // Load the children of this taxonomy term.
    $object_children = &$items[$object->id()]['children'];
    $children = $this->entityTypeManager->getStorage('taxonomy_term')->loadChildren($object->id());

    // Stop if no children are found.
    if (!$children) {
      return;
    }

    // Iterate through all children and recursively add them to the tree array.
    $child_tree_objects = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vocabulary, $object->id(), NULL, TRUE);
    foreach ($children as $child) {
      foreach ($child_tree_objects as $child_tree_object) {
        if ($child_tree_object->id() == $child->id()) {
          $this->buildListTree($object_children, $child_tree_object, $vocabulary, $fields);
        }
      }
    }
  }

}
