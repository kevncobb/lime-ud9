<?php

namespace Drupal\moderation_note\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\moderation_note\ModerationNoteMenuCountInterface;

/**
 * Provides a service for updating the Moderation Note menu count.
 */
class MenuCountService implements ModerationNoteMenuCountInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type manager service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    TranslationInterface $string_translation
    ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function contentLink(string $entity_type, int $entity_id) {
    $count = $this->entityTypeManager->getStorage('moderation_note')->getQuery()
      ->accessCheck(TRUE)
      ->condition('entity_type', $entity_type)
      ->condition('entity_id', $entity_id)
      ->condition('published', 1)
      ->condition('parent', NULL, 'IS NULL')
      ->count()
      ->execute();

    $link = [
      '#theme' => 'menu_local_task',
      '#link' => [
        'title' => $this->stringTranslation->formatPlural($count, 'View Note (1)', 'View Notes (@count)'),
        'url' => Url::fromRoute('moderation_note.list', [
          'entity_type' => $entity_type,
          'entity' => $entity_id,
        ]),
        'localized_options' => [
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'dialog',
            'data-dialog-renderer' => 'off_canvas',
          ],
        ],
      ],
    ];

    return $link;
  }

  /**
   * {@inheritdoc}
   */
  public function assignedTo(int $uid) {
    $count = $this->entityTypeManager->getStorage('moderation_note')->getQuery()
      ->accessCheck(TRUE)
      ->condition('assignee', $uid)
      ->condition('published', 1)
      ->condition('parent', NULL, 'IS NULL')
      ->count()
      ->execute();

    $link = [
      '#theme' => 'links__toolbar_user',
      '#links' => [
        'moderation_note_link' => [
          'title' => $this->stringTranslation->formatPlural($count, 'Assigned Note (1)', 'Assigned Notes (@count)'),
          'url' => Url::fromRoute('moderation_note.assigned_list', ['user' => $uid]),
          'attributes' => [
            'title' => $this->t('View the Assigned Notes page'),
          ],
        ],
      ],
      '#attributes' => [
        'class' => ['toolbar-menu', 'moderation-note'],
      ],
    ];

    return $link;
  }

}
