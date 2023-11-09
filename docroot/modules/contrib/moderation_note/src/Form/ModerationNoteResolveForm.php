<?php

namespace Drupal\moderation_note\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\moderation_note\Ajax\AddModerationNoteCommand;
use Drupal\moderation_note\Ajax\RemoveModerationNoteCommand;
use Drupal\moderation_note\ModerationNoteMenuCountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for resolving a moderation note.
 */
class ModerationNoteResolveForm extends ContentEntityConfirmFormBase {

  /**
   * The moderation note menu count service.
   *
   * @var \Drupal\moderation_note\ModerationNoteMenuCountInterface
   */
  protected $menu;

  /**
   * Constructs a new ModerationNoteResolveForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\moderation_note\ModerationNoteMenuCountInterface $menu
   *   The moderation note menu count service.
   */
  public function __construct(
    EntityRepositoryInterface $entity_repository,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    TimeInterface $time,
    ModerationNoteMenuCountInterface $menu
    ) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->menu = $menu;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('moderation_note.menu_count')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Resolve moderation note');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    /** @var \Drupal\moderation_note\ModerationNoteInterface $note */
    $note = $this->entity;

    return $this->t('<p>Are you sure you want to @action this note?</p>', [
      '@action' => $note->isPublished() ? 'resolve' : 're-open',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    // This is unused but required by the base class.
    return new Url('');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\moderation_note\ModerationNoteInterface $note */
    $note = $this->entity;

    // Wrap our form so that our submit callback can re-render the form.
    $form['#prefix'] = '<div class="moderation-note-form-wrapper" data-moderation-note-form-id="' . $note->id() . '">';
    $form['#suffix'] = '</div>';

    $form['#attributes']['class'][] = 'moderation-note-form';
    $form['#attributes']['class'][] = 'moderation-note-form-resolve';
    if ($note->hasParent()) {
      $form['#attributes']['class'][] = 'moderation-note-form-reply';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\moderation_note\ModerationNoteInterface $note */
    $note = $this->entity;

    return [
      'submit' => [
        '#type' => 'submit',
        '#value' => $note->isPublished() ? $this->t('Resolve') : $this->t('Re-open'),
        '#ajax' => [
          'callback' => '::submitFormCallback',
          'method' => 'replace',
          'disable-refocus' => TRUE,
          'progress' => [
            'type' => 'fullscreen',
            'message' => NULL,
          ],
        ],
      ],
      'cancel' => [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#executes_submit_callback' => FALSE,
        '#ajax' => [
          'callback' => '::cancelForm',
          'method' => 'replace',
          'disable-refocus' => TRUE,
          'progress' => [
            'type' => 'fullscreen',
            'message' => NULL,
          ],
        ],
      ],
    ];
  }

  /**
   * Submission callback when the cancel button is clicked.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response which cancels the form.
   */
  public function cancelForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    /** @var \Drupal\moderation_note\ModerationNoteInterface $note */
    $note = $this->entity;
    $selector = '[data-moderation-note-form-id="' . $note->id() . '"]';
    $content = $this->entityTypeManager->getViewBuilder('moderation_note')->view($note);
    $command = new ReplaceCommand($selector, $content);

    $response->addCommand($command);

    return $response;
  }

  /**
   * Submission callback when the Resolve/Re-open button is clicked.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response which removes or adds the note.
   */
  public function submitFormCallback(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\moderation_note\ModerationNoteInterface $note */
    $note = $this->entity;

    $selector = '[data-moderation-note-form-id="' . $note->id() . '"]';

    // If the form has errors, return the contents of the form.
    // @todo Use trait that comes with https://www.drupal.org/node/2896535
    if ($form_state->hasAnyErrors()) {
      $response = new AjaxResponse();
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -1000,
      ];
      $command = new ReplaceCommand($selector, $form);
      $response->addCommand($command);
      return $response;
    }

    $response = new AjaxResponse();
    if (!$note->isPublished()) {
      $command = new RemoveModerationNoteCommand($note);
      $response->addCommand($command);
      $command = new CloseDialogCommand('#drupal-off-canvas');
      $response->addCommand($command);
      // This message will only be visible if the note is displayed outside of
      // the modal context.
      $message = $this->t('<p>The moderation note and its replies has been @action. To view the notated content, <a href="@url">click here</a>.</p>', [
        '@url' => $note->getModeratedEntity()->toUrl()->toString(),
        '@action' => $note->isPublished() ? 're-opened' : 'resolved',
      ]);
      $command = new ReplaceCommand('.moderation-note-sidebar-wrapper', $message);
    }
    else {
      $command = new AddModerationNoteCommand($note);
      $response->addCommand($command);
      $command = new CloseDialogCommand('#drupal-off-canvas');
    }
    $response->addCommand($command);

    $entity = $note->getModeratedEntity();
    $entity_type = $entity->getEntityTypeId();
    $entity_id = $entity->id();
    $tab_selector = '.use-ajax.tabs__link.js-tabs-link[data-drupal-link-system-path="moderation-note/list/' . $entity_type . '/' . $entity_id . '"]';
    $link = $this->menu->contentLink($entity_type, $entity_id);
    $command = new ReplaceCommand($tab_selector, $link);

    $response->addCommand($command);
    if ($this->currentUser()->id() == ($note->getAssignee() ? $note->getAssignee()->id() : 0)) {
      $link = $this->menu->assignedTo($this->currentUser()->id());
      $command = new ReplaceCommand('.toolbar-menu.moderation-note', $link);
      $response->addCommand($command);
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\moderation_note\ModerationNoteInterface $note */
    $note = $this->entity;

    // Toggle publishing status of the note and its children.
    /** @var \Drupal\moderation_note\ModerationNoteInterface $child */
    foreach ($note->getChildren() as $child) {
      if ($note->isPublished()) {
        $child->setUnpublished();
      }
      else {
        $child->setPublished();
      }
      $child->setValidationRequired(FALSE);
      $child->save();
    }
    if ($note->isPublished()) {
      $note->setUnpublished();
    }
    else {
      $note->setPublished();
    }
    $note->setValidationRequired(FALSE);
    $note->save();

    // Clear the Drupal messages, as this form uses AJAX to display its
    // results. Displaying a deletion message on the next page the user visits
    // is awkward.
    $this->messenger()->deleteAll();
  }

}
