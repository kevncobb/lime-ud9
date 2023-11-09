<?php

namespace Drupal\Tests\moderation_note\Unit\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\moderation_note\Controller\ModerationNoteController;
use Drupal\moderation_note\ModerationNoteInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests for the ModerationNoteController class.
 *
 * @group moderation_note
 */
class ModerationNoteControllerTest extends UnitTestCase {

  /**
   * The controller to be tested.
   *
   * @var \Drupal\moderation_note\Controller\ModerationNoteController
   */
  protected $controller;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $account;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\Sql\SqlEntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $storage;

  /**
   * The entity view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewBuilder;

  /**
   * The entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $query;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $formBuilder;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * The translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translation;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->languageManager = $this->createMock(LanguageManagerInterface::class);
    $this->viewBuilder = $this->createMock(EntityViewBuilderInterface::class);
    $this->query = $this->createMock(QueryInterface::class);
    $this->storage = $this->createMock(SqlEntityStorageInterface::class);
    $this->storage->method('getQuery')
      ->willReturn($this->query);
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityTypeManager->method('getStorage')
      ->with('moderation_note')
      ->willReturn($this->storage);
    $this->entityTypeManager->method('getViewBuilder')
      ->with('moderation_note')
      ->willReturn($this->viewBuilder);

    $this->formBuilder = $this->createMock(EntityFormBuilderInterface::class);

    $this->account = $this->createMock(AccountInterface::class);

    $cacheContextsManager = $this->createMock(CacheContextsManager::class);
    $cacheContextsManager->expects($this->any())
      ->method('assertValidTokens')
      ->willReturnMap([
        [['user.permissions'], TRUE],
        [['user.permissions', 'user'], TRUE],
      ]);

    $this->translation = $this->createStub(TranslationInterface::class);

    $container = new ContainerBuilder();
    $container->set('cache_contexts_manager', $cacheContextsManager);
    $container->set('current_user', $this->account);
    $container->set('entity_type.manager', $this->entityTypeManager);
    $container->set('entity.form_builder', $this->formBuilder);
    $container->set('language_manager', $this->languageManager);
    $container->set('string_translation', $this->translation);
    \Drupal::setContainer($container);

    // Set up the ModerationNoteController with the mocked dependencies.
    $this->controller = new ModerationNoteController();
  }

  /**
   * Test createNote method.
   */
  public function testCreateNote() {
    // Create mock entity, field_name, langcode, and view_mode_id.
    $entity = $this->createMock(EntityInterface::class);
    $field_name = 'field_example';
    $langcode = 'en';
    $view_mode_id = 'default';

    $note = $this->createMock(ModerationNoteInterface::class);
    $this->storage->expects($this->once())
      ->method('create')
      ->willReturn($note);
    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->willReturn([]);

    // Call the createNote method.
    $form = $this->controller->createNote($entity, $field_name, $langcode, $view_mode_id);

    // Assert that the result is an array.
    $this->assertIsArray($form);
    $this->assertTrue($form['#attributes']['data-moderation-note-new-form']);
  }

  /**
   * Test createNoteAccess method.
   */
  public function testCreateNoteAccess() {
    require_once __DIR__ . '/../../../../moderation_note.module';
    // Create a mock entity.
    $entity = $this->createMock(EntityInterface::class);
    $entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);
    $entity->expects($this->once())
      ->method('getCacheMaxAge')
      ->willReturn(0);
    // Call the createNoteAccess method.
    $accessResult = $this->controller->createNoteAccess($entity, 'field_example', 'en', 'default');

    // Assert that the result is an AccessResult instance.
    $this->assertInstanceOf(AccessResult::class, $accessResult);
  }

  /**
   * Test viewNote method.
   */
  public function testViewNote() {
    // Create a mock ModerationNoteInterface.
    $moderation_note = $this->createMock(ModerationNoteInterface::class);
    $moderation_note->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $moderation_note->expects($this->once())
      ->method('getQuote')
      ->willReturn('There comes a time when silence is betrayal.');
    $moderation_note->expects($this->once())
      ->method('getQuoteOffset')
      ->willReturn(100);
    $moderation_note->expects($this->once())
      ->method('getChildren')
      ->willReturn([]);

    $this->viewBuilder->expects($this->once())
      ->method('view')
      ->willReturn(['#markup' => 'Moderation Note View']);
    $this->viewBuilder->expects($this->once())
      ->method('viewMultiple')
      ->willReturn(['#markup' => 'Moderation Note Replies']);

    // Create a mock Request.
    $request = $this->createMock(Request::class);

    // Call the viewNote method.
    $build = $this->controller->viewNote($moderation_note, $request);

    // Assert that the result is an array.
    $this->assertIsArray($build);
    $this->assertEquals(1, $build['#attached']['drupalSettings']['highlight_moderation_note']['id']);
    $this->assertEquals('There comes a time when silence is betrayal.', $build['#attached']['drupalSettings']['highlight_moderation_note']['quote']);
    $this->assertEquals(100, $build['#attached']['drupalSettings']['highlight_moderation_note']['quote_offset']);
    $this->assertEquals('Moderation Note View', $build[0]['#markup']);
    $this->assertEquals('Moderation Note Replies', $build[1]['#markup']);
  }

  /**
   * Test viewNoteAccess method.
   */
  public function testListNotes() {
    $language = $this->createMock(LanguageInterface::class);
    $language->expects($this->once())
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($language);

    // Create a mock Entity.
    $entity = $this->createMock(EntityInterface::class);
    $entity->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('test_entity');

    $this->query->expects($this->once())
      ->method('accessCheck')
      ->willReturnSelf();
    $this->query->expects($this->exactly(3))
      ->method('condition')
      ->willReturnSelf();
    $this->query->expects($this->exactly(2))
      ->method('sort')
      ->willReturnSelf();
    $this->query->expects($this->once())
      ->method('notExists')
      ->willReturnSelf();
    $this->query->expects($this->once())
      ->method('execute')
      ->willReturn([1, 2, 3]);

    $moderation_note_1 = $this->createMock(ModerationNoteInterface::class);
    $moderation_note_2 = $this->createMock(ModerationNoteInterface::class);
    $moderation_note_3 = $this->createMock(ModerationNoteInterface::class);

    $this->storage->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([$moderation_note_1, $moderation_note_2, $moderation_note_3]);

    $this->viewBuilder->expects($this->once())
      ->method('viewMultiple')
      ->willReturn(['#markup' => 'Moderation Note Multiple View']);
    // Call the viewNote method.
    $build = $this->controller->listNotes($entity);

    // Assert that the result is an array.
    $this->assertIsArray($build);
    $this->assertEquals('Moderation Note Multiple View', $build[0]['#markup']);
  }

  /**
   * Test listNotes method has no notes.
   */
  public function testListNotesNoNotes() {
    $language = $this->createMock(LanguageInterface::class);
    $language->expects($this->once())
      ->method('getId')
      ->willReturn('en');
    $this->languageManager->expects($this->once())
      ->method('getCurrentLanguage')
      ->willReturn($language);

    // Create a mock Entity.
    $entity = $this->createMock(EntityInterface::class);
    $entity->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('test_entity');

    $this->query->expects($this->once())
      ->method('accessCheck')
      ->willReturnSelf();
    $this->query->expects($this->exactly(3))
      ->method('condition')
      ->willReturnSelf();
    $this->query->expects($this->exactly(2))
      ->method('sort')
      ->willReturnSelf();
    $this->query->expects($this->once())
      ->method('notExists')
      ->willReturnSelf();
    $this->query->expects($this->once())
      ->method('execute')
      ->willReturn([]);

    // Call the viewNote method.
    $build = $this->controller->listNotes($entity);

    // Assert that the result is an array.
    $this->assertIsArray($build);
    $this->assertEquals("<p>There are no notes for this entity. Go create some!</p>", $build[0]['#markup']->getUntranslatedString());
  }

  /**
   * Test listAssignedNotes method.
   */
  public function testListAssignedNotes() {
    $user = $this->createMock(UserInterface::class);

    $this->query->expects($this->once())
      ->method('accessCheck')
      ->willReturnSelf();
    $this->query->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();
    $this->query->expects($this->once())
      ->method('execute')
      ->willReturn([1, 2, 3]);

    $moderation_note_1 = $this->createMock(ModerationNoteInterface::class);
    $moderation_note_2 = $this->createMock(ModerationNoteInterface::class);
    $moderation_note_3 = $this->createMock(ModerationNoteInterface::class);

    $this->storage->expects($this->once())
      ->method('loadMultiple')
      ->willReturn([$moderation_note_1, $moderation_note_2, $moderation_note_3]);

    $this->viewBuilder->expects($this->once())
      ->method('viewMultiple')
      ->willReturn(['#markup' => 'Moderation Note Multiple View']);
    // Call the viewNote method.
    $build = $this->controller->listAssignedNotes($user);

    // Assert that the result is an array.
    $this->assertIsArray($build);
    $this->assertEquals('Moderation Note Multiple View', $build[0]['#markup']);
  }

  /**
   * Test listAssignedNotes method no notes.
   */
  public function testListAssignedNoNotes() {
    $user = $this->createMock(UserInterface::class);

    $this->query->expects($this->once())
      ->method('accessCheck')
      ->willReturnSelf();
    $this->query->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();
    $this->query->expects($this->once())
      ->method('execute')
      ->willReturn([]);

    // Call the viewNote method.
    $build = $this->controller->listAssignedNotes($user);

    // Assert that the result is an array.
    $this->assertIsArray($build);
    $this->assertEquals("<p>There are no assigned notes for this user.</p>", $build[0]['#markup']->getUntranslatedString());
  }

  /**
   * Test deleteNote method.
   */
  public function testDeleteNote() {
    $moderation_note = $this->createMock(ModerationNoteInterface::class);
    $moderation_note->expects($this->once())
      ->method('id')
      ->willReturn(1);

    $response = $this->controller->deleteNote($moderation_note);

    $commands = $response->getCommands();
    $this->assertEquals('insert', $commands[0]['command']);
    $this->assertEquals('replaceWith', $commands[0]['method']);
    $this->assertEquals('[data-moderation-note-id="1"]', $commands[0]['selector']);
  }

  /**
   * Test resolveNote method.
   */
  public function testResolveNote() {
    $moderation_note = $this->createMock(ModerationNoteInterface::class);
    $moderation_note->expects($this->once())
      ->method('id')
      ->willReturn(1);

    $response = $this->controller->resolveNote($moderation_note);

    $commands = $response->getCommands();
    $this->assertEquals('insert', $commands[0]['command']);
    $this->assertEquals('replaceWith', $commands[0]['method']);
    $this->assertEquals('[data-moderation-note-id="1"]', $commands[0]['selector']);
  }

  /**
   * Test editNote method.
   */
  public function testEditNote() {
    $moderation_note = $this->createMock(ModerationNoteInterface::class);
    $moderation_note->expects($this->once())
      ->method('id')
      ->willReturn(1);

    $response = $this->controller->editNote($moderation_note);

    $commands = $response->getCommands();

    $this->assertEquals('insert', $commands[0]['command']);
    $this->assertEquals('replaceWith', $commands[0]['method']);
    $this->assertEquals('[data-moderation-note-id="1"]', $commands[0]['selector']);
  }

  /**
   * Test replyToNote method.
   */
  public function testReplyToNote() {

    $moderation_note = $this->createMock(ModerationNoteInterface::class);
    $moderation_note->expects($this->once())
      ->method('getModeratedEntityTypeId')
      ->willReturn('test_entity');
    $moderation_note->expects($this->once())
      ->method('getModeratedEntityId')
      ->willReturn(1);

    $reply_note = $this->createMock(ModerationNoteInterface::class);
    $this->storage->expects($this->once())
      ->method('create')
      ->willReturn($reply_note);

    $response = $this->controller->replyToNote($moderation_note);

    $commands = $response->getCommands();

    $this->assertEquals('insert', $commands[0]['command']);
    $this->assertEquals('append', $commands[0]['method']);
    $this->assertEquals('.moderation-note-sidebar-wrapper', $commands[0]['selector']);
  }

}
