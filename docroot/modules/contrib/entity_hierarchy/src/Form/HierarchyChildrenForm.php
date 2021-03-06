<?php

namespace Drupal\entity_hierarchy\Form;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity_hierarchy\Information\ParentCandidateInterface;
use Drupal\entity_hierarchy\Storage\EntityTreeNodeMapperInterface;
use Drupal\entity_hierarchy\Storage\NestedSetNodeKeyFactory;
use Drupal\entity_hierarchy\Storage\NestedSetStorageFactory;
use Drupal\Core\Entity\ContentEntityForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Defines a form for re-ordering children.
 */
class HierarchyChildrenForm extends ContentEntityForm {
  const CHILD_ENTITIES_STORAGE = 'child_entities';

  /**
   * The hierarchy being displayed.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * Nested set storage factory.
   *
   * @var \Drupal\entity_hierarchy\Storage\NestedSetStorageFactory
   */
  protected $nestedSetStorageFactory;

  /**
   * Nested set node key factory.
   *
   * @var \Drupal\entity_hierarchy\Storage\NestedSetNodeKeyFactory
   */
  protected $nodeKeyFactory;

  /**
   * Parent candidate.
   *
   * @var \Drupal\entity_hierarchy\Information\ParentCandidateInterface
   */
  protected $parentCandidate;

  /**
   * Tree node mapper.
   *
   * @var \Drupal\entity_hierarchy\Storage\EntityTreeNodeMapperInterface
   */
  protected $entityTreeNodeMapper;

  /**
   * Constructs a new HierarchyChildrenForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   Entity type manager.
   * @param \Drupal\entity_hierarchy\Storage\NestedSetStorageFactory $nestedSetStorageFactory
   *   Nested set storage.
   * @param \Drupal\entity_hierarchy\Storage\NestedSetNodeKeyFactory $nodeKeyFactory
   *   Node key factory.
   * @param \Drupal\entity_hierarchy\Information\ParentCandidateInterface $parentCandidate
   *   Parent candidate service.
   * @param \Drupal\entity_hierarchy\Storage\EntityTreeNodeMapperInterface $entityTreeNodeMapper
   *   Tree node mapper.
   */
  public function __construct(EntityManagerInterface $entity_manager, NestedSetStorageFactory $nestedSetStorageFactory, NestedSetNodeKeyFactory $nodeKeyFactory, ParentCandidateInterface $parentCandidate, EntityTreeNodeMapperInterface $entityTreeNodeMapper) {
    parent::__construct($entity_manager);
    $this->nestedSetStorageFactory = $nestedSetStorageFactory;
    $this->nodeKeyFactory = $nodeKeyFactory;
    $this->parentCandidate = $parentCandidate;
    $this->entityTreeNodeMapper = $entityTreeNodeMapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_hierarchy.nested_set_storage_factory'),
      $container->get('entity_hierarchy.nested_set_node_factory'),
      $container->get('entity_hierarchy.information.parent_candidate'),
      $container->get('entity_hierarchy.entity_tree_node_mapper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    // Don't show a parent form here.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $cache = (new CacheableMetadata())->addCacheableDependency($this->entity);

    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields */
    $fields = $this->parentCandidate->getCandidateFields($this->entity);
    if (!$fields) {
      throw new NotFoundHttpException();
    }
    $fieldName = $form_state->getValue('fieldname') ?: reset($fields);
    if (count($fields) === 1) {
      $form['fieldname'] = [
        '#type' => 'value',
        '#value' => $fieldName,
      ];
    }
    else {
      $form['select_field'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['container-inline'],
        ],
      ];
      $form['select_field']['fieldname'] = [
        '#type' => 'select',
        '#title' => $this->t('Field'),
        '#description' => $this->t('Field to reorder children in.'),
        '#options' => array_map(function ($field_name) {
          return $this->entity->getFieldDefinitions()[$field_name]->getLabel();
        }, $fields),
        '#default_value' => $fieldName,
      ];
      $form['select_field']['update'] = [
        '#type' => 'submit',
        '#value' => $this->t('Update'),
        '#submit' => ['::updateField'],
      ];
    }
    /** @var \PNX\NestedSet\Node[] $children */
    /** @var \PNX\NestedSet\NestedSetInterface $storage */
    $storage = $this->nestedSetStorageFactory->get($fieldName, $this->entity->getEntityTypeId());
    $children = $storage->findChildren($this->nodeKeyFactory->fromEntity($this->entity));
    $childEntities = $this->entityTreeNodeMapper->loadAndAccessCheckEntitysForTreeNodes($this->entity->getEntityTypeId(), $children, $cache);
    $form_state->setTemporaryValue(self::CHILD_ENTITIES_STORAGE, $childEntities);
    $form['#attached']['library'][] = 'entity_hierarchy/entity_hierarchy.nodetypeform';
    $form['children'] = [
      '#type' => 'table',
      '#header' => [t('Child'), t('Type'), t('Weight'), t('Operations')],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'children-order-weight',
        ],
      ],
      '#empty' => $this->t('There are no children to reorder'),
    ];

    $bundles = FALSE;

    foreach ($children as $weight => $node) {
      if (!$childEntities->contains($node)) {
        // Doesn't exist or is access hidden.
        continue;
      }
      /** @var \Drupal\Core\Entity\ContentEntityInterface $childEntity */
      $childEntity = $childEntities->offsetGet($node);
      if (!$childEntity->isDefaultRevision()) {
        // We only update default revisions here.
        continue;
      }
      $child = $node->getId();
      $form['children'][$child]['#attributes']['class'][] = 'draggable';
      $form['children'][$child]['#weight'] = $weight;
      $form['children'][$child]['title'] = $childEntity->toLink()
        ->toRenderable();
      if (!$bundles) {
        $bundles = $this->entityTypeBundleInfo->getBundleInfo($childEntity->getEntityTypeId());
      }
      $form['children'][$child]['type'] = ['#markup' => $bundles[$childEntity->bundle()]['label']];
      $form['children'][$child]['weight'] = [
        '#type' => 'weight',
        '#delta' => 50,
        '#title' => t('Weight for @title', ['@title' => $childEntity->label()]),
        '#title_display' => 'invisible',
        '#default_value' => $childEntity->{$fieldName}->weight,
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['children-order-weight']],
      ];
      // Operations column.
      $form['children'][$child]['operations'] = [
        '#type' => 'operations',
        '#links' => [],
      ];
      if ($childEntity->access('update') && $childEntity->hasLinkTemplate('edit-form')) {
        $form['children'][$child]['operations']['#links']['edit'] = [
          'title' => t('Edit'),
          'url' => $childEntity->toUrl('edit-form'),
        ];
      }
      if ($childEntity->access('delete') && $childEntity->hasLinkTemplate('delete-form')) {
        $form['children'][$child]['operations']['#links']['delete'] = [
          'title' => t('Delete'),
          'url' => $childEntity->toUrl('delete-form'),
        ];
      }
    }

    $cache->applyTo($form);

    return $form;
  }

  /**
   * Submit handler for update field button.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   */
  public function updateField(array $form, FormStateInterface $formState) {
    $formState->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update child order');
    unset($actions['delete']);
    // Don't show the actions links if there are no children.
    if (empty(Element::children($form['children']))) {
      unset($actions['submit']);
    }
    $fields = $this->parentCandidate->getCandidateFields($this->entity);
    $fieldName = $form_state->getValue('fieldname') ?: reset($fields);
    $entityType = $this->entity->getEntityType();
    if ($entityType->hasHandlerClass('entity_hierarchy') && ($childBundles = $this->parentCandidate->getCandidateBundles($this->entity)) && isset($childBundles[$fieldName])) {
      $handlerClass = $entityType->getHandlerClass('entity_hierarchy');
      /** @var \Drupal\entity_hierarchy\Handler\EntityHierarchyHandlerInterface $handler */
      $handler = new $handlerClass();
      if (count($childBundles[$fieldName]) > 1) {
        $actions['add_child'] = [
          '#type' => 'dropbutton',
          '#links' => [],
        ];
        foreach ($childBundles[$fieldName] as $id => $info) {
          $actions['add_child']['#links'][$id] = [
            'title' => $this->t('Create new @bundle', ['@bundle' => $info['label']]),
            'url' => $handler->getAddChildUrl($entityType, $this->entity, $id, $fieldName),
          ];
        }
      }
      else {
        reset($childBundles[$fieldName]);
        $id = key($childBundles[$fieldName]);
        $actions['add_child'] = [
          '#type' => 'link',
          '#title' => $this->t('Create new @bundle', ['@bundle' => $childBundles[$fieldName][$id]['label']]),
          '#url' => $handler->getAddChildUrl($entityType, $this->entity, $id, $fieldName),
          '#attributes' => [
            'class' => ['button', 'button--primary'],
          ],
          '#weight' => -100,
        ];
      }
    }
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $children = $form_state->getValue('children');
    $childEntities = $form_state->getTemporaryValue(self::CHILD_ENTITIES_STORAGE);
    $fieldName = $form_state->getValue('fieldname');
    $batch = [
      'title' => new TranslatableMarkup('Reordering children ...'),
      'operations' => [],
      'finished' => [static::class, 'finished'],
    ];
    foreach ($childEntities as $node) {
      $childEntity = $childEntities->offsetGet($node);
      if (!$childEntity->isDefaultRevision()) {
        // We don't operate on other than the default revision.
        continue;
      }
      $batch['operations'][] = [
        [static::class, 'reorder'],
        [$fieldName, $childEntity, $children[$node->getId()]['weight']],
      ];

    }
    batch_set($batch);
  }

  /**
   * Reorder batch callback.
   *
   * @param string $fieldName
   *   Field name.
   * @param \Drupal\Core\Entity\ContentEntityInterface $childEntity
   *   Child entity being updated.
   * @param int $weight
   *   New weight.
   */
  public static function reorder($fieldName, ContentEntityInterface $childEntity, $weight) {
    $childEntity->{$fieldName}->weight = $weight;
    $childEntity->save();
  }

  /**
   * Batch finished callback.
   */
  public static function finished() {
    drupal_set_message(new TranslatableMarkup('Updated child order.'));
  }

}
