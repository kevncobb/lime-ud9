<?php

namespace Drupal\Tests\moderation_note\Unit;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\moderation_note\ModerationNoteMenuCountInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the moderation_note_toolbar_alter function.
 *
 * @group moderation_note
 */
class ModerationNoteToolbarAlterTest extends UnitTestCase {

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
   * Tests moderation_note_toolbar_alter function.
   */
  public function testToolbarAlter() {
    $this->menuCount->expects($this->once())
      ->method('assignedTo')
      ->with(1)
      ->willReturn([
        '#theme' => 'links__toolbar_user',
        '#links' => [
          'moderation_note_link' => [
            'title' => '',
            'url' => '',
            'attributes' => [
              'title' => 'View the Assigned Notes page',
            ],
          ],
        ],
        '#attributes' => [
          'class' => ['toolbar-menu', 'moderation-note'],
        ],
      ]);
    // Create a mock entity.
    $this->account->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $this->account->expects($this->once())
      ->method('hasPermission')
      ->with('access moderation notes')
      ->willReturn(TRUE);

    $this->container->set('current_route_match', $this->routeMatch);

    // Call the function being tested.
    $data = ['user' => []];
    moderation_note_toolbar_alter($data);

    $this->assertSame(['toolbar-menu', 'moderation-note'], $data['user']['tray']['moderation_note']['#attributes']['class']);
    $this->assertSame(['user'], $data['user']['tray']['moderation_note']['#cache']['contexts']);
    $this->assertSame(['moderation_note:user:1'], $data['user']['tray']['moderation_note']['#cache']['tags']);

    // $data['user']['tray']['moderation_note']
  }

}
