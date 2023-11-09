<?php

namespace Drupal\Tests\moderation_note\Unit\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\moderation_note\ModerationNoteForm;
use Drupal\moderation_note\ModerationNoteInterface;
use Drupal\moderation_note\ModerationNoteMenuCountInterface;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the ModerationNoteForm form.
 *
 * @group moderation_note
 */
class ModerationNoteFormTest extends UnitTestCase {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The moderation note menu count service.
   *
   * @var \Drupal\moderation_note\ModerationNoteMenuCountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $menuCountService;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $account;

  /**
   * The translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translation;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Set up the container with necessary services.
    $container = new ContainerBuilder();
    $this->translation = $this->getStringTranslationStub();
    $container->set('string_translation', $this->translation);

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $container->set('entity_type.manager', $this->entityTypeManager);

    $this->menuCountService = $this->createMock(ModerationNoteMenuCountInterface::class);
    $container->set('moderation_note.menu_count', $this->menuCountService);

    $this->renderer = $this->createMock(RendererInterface::class);
    $container->set('renderer', $this->renderer);

    $this->account = $this->createMock(AccountInterface::class);
    $container->set('current_user', $this->account);

    \Drupal::setContainer($container);

    require_once __DIR__ . '/../../../../moderation_note.module';
  }

  /**
   * Tests buildForm method of ModerationNoteForm.
   */
  public function testBuildForm() {

    $entity_type = $this->createMock(EntityTypeInterface::class);
    $entity_type->expects($this->exactly(2))
      ->method('showRevisionUi')
      ->willReturn(FALSE);

    // Mocking entity.
    $entity = $this->createMock(ModerationNoteInterface::class);
    $entity->expects($this->any())
      ->method('isNew')
      ->willReturn(FALSE);
    $entity->expects($this->any())
      ->method('id')
      ->willReturn(1);
    $entity->expects($this->any())
      ->method('isPublished')
      ->willReturn(TRUE);
    $entity->expects($this->exactly(2))
      ->method('getEntityType')
      ->willReturn($entity_type);

    $entity_form_display = $this->createMock(EntityFormDisplayInterface::class);
    $entity_form_display->expects($this->once())
      ->method('buildForm')
      ->willReturn([]);
    // Mock FormStateInterface.
    $form_state = $this->createMock(FormStateInterface::class);
    $form_state->expects($this->once())
      ->method('has')
      ->with('entity_form_initialized')
      ->willReturn(TRUE);
    $form_state->expects($this->once())
      ->method('get')
      ->with('form_display')
      ->willReturn($entity_form_display);

    // Create form object.
    $note_form = new ModerationNoteForm(
      $this->createMock(EntityRepositoryInterface::class),
      $this->createMock(EntityTypeBundleInfoInterface::class),
      $this->createMock(TimeInterface::class),
      $this->menuCountService
    );
    $note_form->setEntity($entity);

    // Build the form.
    $form = $note_form->buildForm([], $form_state);

    // Perform assertions.
    $this->assertSame('<div class="moderation-note-form-wrapper" data-moderation-note-form-id="">', $form['#prefix']);

    $this->assertSame('</div>', $form['#suffix']);
  }

  /**
   * Tests submitForm method of ModerationNoteForm.
   */
  public function testSubmitFormCallback() {

    $this->account->expects($this->exactly(3))
      ->method('id')
      ->willReturn(1);

    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');

    // Mocking entity.
    $note = $this->createMock(ModerationNoteInterface::class);
    $note->expects($this->any())
      ->method('isNew')
      ->willReturn(FALSE);
    $note->expects($this->any())
      ->method('id')
      ->willReturn(1);
    $note->expects($this->any())
      ->method('isPublished')
      ->willReturn(TRUE);
    $note->expects($this->once())
      ->method('getModeratedEntity')
      ->willReturn($node);
    $note->expects($this->exactly(2))
      ->method('getAssignee')
      ->willReturn($this->account);

    $form_state = new FormState();

    // Create form object.
    $note_form = new ModerationNoteForm(
      $this->createMock(EntityRepositoryInterface::class),
      $this->createMock(EntityTypeBundleInfoInterface::class),
      $this->createMock(TimeInterface::class),
      $this->menuCountService
    );
    $note_form->setEntity($note);
    $note_form->setOperation('create');

    $this->menuCountService->expects($this->once())
      ->method('contentLink')
      ->with('node', 1)
      ->willReturn('View Note (1)');

    $this->menuCountService->expects($this->once())
      ->method('assignedTo')
      ->with(1)
      ->willReturn('Assigned Note (1)');

    // Build the form.
    $form = [];
    $response = $note_form->submitFormCallback($form, $form_state);
    $commands = $response->getCommands();

    $this->assertSame('add_moderation_note', $commands[0]['command']);
    // Closes the dialog.
    $this->assertSame('insert', $commands[1]['command']);
    $this->assertSame('.use-ajax.tabs__link.js-tabs-link[data-drupal-link-system-path="moderation-note/list/node/1"]', $commands[1]['selector']);
    $this->assertFalse($commands[3]['persist']);
    // Updates the moderation note count in the tabs.
    $this->assertSame('closeDialog', $commands[3]['command']);
    $this->assertSame('replaceWith', $commands[2]['method']);
    $this->assertSame('#drupal-off-canvas', $commands[3]['selector']);
    $this->assertSame('View Note (1)', $commands[1]['data']);
    // Updates the moderation note count in the toolbar.
    $this->assertSame('insert', $commands[2]['command']);
    $this->assertSame('replaceWith', $commands[2]['method']);
    $this->assertSame('.toolbar-menu.moderation-note', $commands[2]['selector']);
    $this->assertSame('Assigned Note (1)', $commands[2]['data']);

  }

  /**
   * Test submit form callback with different author.
   */
  public function testSubmitFormCallbackDifferentAuthor() {

    $this->account->expects($this->once())
      ->method('id')
      ->willReturn(1);

    $user = $this->createMock(AccountInterface::class);
    $user->expects($this->once())
      ->method('id')
      ->willReturn(2);

    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');

    // Mocking entity.
    $note = $this->createMock(ModerationNoteInterface::class);
    $note->expects($this->any())
      ->method('isNew')
      ->willReturn(FALSE);
    $note->expects($this->any())
      ->method('id')
      ->willReturn(1);
    $note->expects($this->any())
      ->method('isPublished')
      ->willReturn(TRUE);
    $note->expects($this->once())
      ->method('getModeratedEntity')
      ->willReturn($node);
    $note->expects($this->exactly(2))
      ->method('getAssignee')
      ->willReturn($user);

    $form_state = new FormState();

    // Create form object.
    $note_form = new ModerationNoteForm(
      $this->createMock(EntityRepositoryInterface::class),
      $this->createMock(EntityTypeBundleInfoInterface::class),
      $this->createMock(TimeInterface::class),
      $this->menuCountService
    );
    $note_form->setEntity($note);
    $note_form->setOperation('create');

    $this->menuCountService->expects($this->once())
      ->method('contentLink')
      ->with('node', 1)
      ->willReturn('View Note (1)');

    // Build the form.
    $form = [];
    $response = $note_form->submitFormCallback($form, $form_state);
    $commands = $response->getCommands();

    $this->assertSame('add_moderation_note', $commands[0]['command']);
    // Closes the dialog.
    $this->assertSame('insert', $commands[1]['command']);
    $this->assertSame('replaceWith', $commands[1]['method']);
    $this->assertSame('View Note (1)', $commands[1]['data']);
    $this->assertSame('.use-ajax.tabs__link.js-tabs-link[data-drupal-link-system-path="moderation-note/list/node/1"]', $commands[1]['selector']);
    // Updates the moderation note count in the tabs.
    $this->assertSame('closeDialog', $commands[2]['command']);
    $this->assertSame('#drupal-off-canvas', $commands[2]['selector']);
    $this->assertFalse($commands[2]['persist']);

  }

}
