<?php

namespace Drupal\Tests\moderation_note\Unit;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\moderation_note\ModerationNoteMenuCountInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the moderation_note_menu_local_tasks_alter function.
 *
 * @group moderation_note
 */
class ModerationNoteMenuLocalTasksAlterTest extends UnitTestCase {

  /**
   * The mocked route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $routeMatch;

  /**
   * The mocked entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The mocked user account.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $account;

  /**
   * The mocked translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $translation;

  /**
   * The mocked moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moderationInfo;

  /**
   * The mocked container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * The mocked menu count service.
   *
   * @var \Drupal\moderation_note\ModerationNoteMenuCountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $menuCount;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->routeMatch = $this->createMock(RouteMatchInterface::class);
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->account = $this->createMock(AccountInterface::class);
    $cacheContextsManager = $this->createMock(CacheContextsManager::class);
    $cacheContextsManager->expects($this->any())
      ->method('assertValidTokens')
      ->willReturnMap([
        [['user.permissions'], TRUE],
        [['user.permissions', 'user'], TRUE],
      ]);

    // Stub the translation() method.
    $this->translation = $this->createStub(TranslationInterface::class);
    $this->moderationInfo = $this->createMock(ModerationInformationInterface::class);
    $this->menuCount = $this->createMock(ModerationNoteMenuCountInterface::class);
    $this->container = new ContainerBuilder();
    $this->container->set('string_translation', $this->translation);
    $this->container->set('entity_type.manager', $this->entityTypeManager);
    $this->container->set('content_moderation.moderation_information', $this->moderationInfo);
    $this->container->set('current_user', $this->account);
    $this->container->set('cache_contexts_manager', $cacheContextsManager);
    $this->container->set('moderation_note.menu_count', $this->menuCount);
    \Drupal::setContainer($this->container);

    require_once __DIR__ . '/../../../moderation_note.module';
  }

  /**
   * Tests moderation_note_menu_local_tasks_alter function.
   */
  public function testMenuLocalTasksAlter() {
    $this->menuCount->expects($this->once())
      ->method('contentLink')
      ->with('test_entity', 1)
      ->willReturn([
        '#theme' => 'menu_local_task',
        '#link' => [
          'title' => '',
          'url' => '',
          'localized_options' => [
            'attributes' => [
              'class' => ['use-ajax'],
              'data-dialog-type' => 'dialog',
              'data-dialog-renderer' => 'off_canvas',
            ],
          ],
        ],
      ]);
    // Create a mock entity.
    $entity = $this->createMock(EntityInterface::class);
    $entity->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $entity->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('test_entity');
    $this->moderationInfo->expects($this->once())
      ->method('isModeratedEntity')
      ->with($entity)
      ->willReturn(TRUE);

    // Mock the route match service.
    $this->routeMatch
      ->expects($this->once())
      ->method('getParameters')
      ->willReturn([$entity]);

    $this->container->set('current_route_match', $this->routeMatch);

    // Call the function being tested.
    $cacheability = $this->createMock(RefinableCacheableDependencyInterface::class);
    $data = [];
    moderation_note_menu_local_tasks_alter($data, 'some.route.name', $cacheability);

    $this->assertSame(['moderation_note/main'], $data['tabs'][0]['moderation_note.list']['#attached']['library']);
  }

}
