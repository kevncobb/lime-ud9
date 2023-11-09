<?php

namespace Drupal\Tests\moderation_note\Unit;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\moderation_note\AccessControlHandler;
use Drupal\moderation_note\ModerationNoteInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the moderation note access control handler.
 *
 * @coversDefaultClass \Drupal\moderation_note\AccessControlHandler
 * @group moderation_note
 */
class AccessControlHandlerTest extends UnitTestCase {

  /**
   * The access control handler under test.
   *
   * @var \Drupal\moderation_note\AccessControlHandler
   */
  protected $accessControlHandler;

  /**
   * The mocked entity.
   *
   * @var \Drupal\moderation_note\ModerationNoteInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entity;

  /**
   * The mocked account.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $account;

  /**
   * The mocked language.
   *
   * @var \Drupal\Core\Language\LanguageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $language;

  /**
   * The mocked module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = $this->createMock(ModerationNoteInterface::class);
    $this->account = $this->createMock(AccountInterface::class);
    $entity_type = $this->createMock(EntityTypeInterface::class);
    $entity_type->expects($this->any())
      ->method('id')
      ->willReturn('node');
    $this->language = $this->createMock(LanguageInterface::class);
    $this->language->expects($this->once())
      ->method('getId')
      ->willReturn('en');

    $this->moduleHandler = $this->createMock(ModuleHandlerInterface::class);
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn([]);

    $cacheContextsManager = $this->createMock(CacheContextsManager::class);
    $cacheContextsManager->expects($this->any())
      ->method('assertValidTokens')
      ->willReturnMap([
        [['user.permissions'], TRUE],
        [['user.permissions', 'user'], TRUE],
      ]);

    $container = new ContainerBuilder();
    $container->set('module_handler', $this->moduleHandler);
    $container->set('cache_contexts_manager', $cacheContextsManager);
    \Drupal::setContainer($container);

    $this->accessControlHandler = new AccessControlHandler($entity_type, 'moderation_note', [], $this->createMock(EntityTypeManagerInterface::class));

    require_once __DIR__ . '/../../../moderation_note.module';
  }

  /**
   * Tests 'administer moderation notes' checking for the view operation.
   *
   * @covers ::checkAccess
   */
  public function testViewAccessAdminister() {
    $node = $this->createMock(EntityInterface::class);
    $node->expects($this->once())
      ->method('access')
      ->with('view', $this->account, TRUE)
      ->willReturn(AccessResult::allowed());

    $this->entity->expects($this->once())
      ->method('getModeratedEntity')
      ->willReturn($node);
    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);

    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::allowed()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'view', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'access moderation notes' checking for the view operation.
   *
   * @covers ::checkAccess
   */
  public function testViewAccess() {

    $node = $this->createMock(EntityInterface::class);
    $node->expects($this->once())
      ->method('access')
      ->with('view', $this->account, TRUE)
      ->willReturn(AccessResult::allowed());

    $this->entity->expects($this->once())
      ->method('getModeratedEntity')
      ->willReturn($node);
    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);

    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::neutral()],
        ['access moderation notes', AccessResult::allowed()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'view', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'administer moderation notes' checking for the create operation.
   *
   * @covers ::checkAccess
   */
  public function testCreateAccessAdminister() {
    $node = $this->createMock(EntityInterface::class);

    $this->entity->expects($this->once())
      ->method('getModeratedEntity')
      ->willReturn($node);

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);

    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::allowed()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'create', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'create moderation notes' checking for the create operation.
   *
   * @covers ::checkAccess
   */
  public function testCreateAccess() {

    $node = $this->createMock(EntityInterface::class);

    $this->entity->expects($this->once())
      ->method('getModeratedEntity')
      ->willReturn($node);

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);

    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', FALSE],
        ['create moderation notes', AccessResult::allowed()],
        ['create moderation notes on uneditable entities',
          AccessResult::forbidden(),
        ],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'create', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'create moderation notes on uneditable entities' checking create.
   *
   * @covers ::checkAccess
   */
  public function testCreateUneditableEntitiesAccess() {

    $node = $this->createMock(EntityInterface::class);

    $this->entity->expects($this->once())
      ->method('getModeratedEntity')
      ->willReturn($node);

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);

    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', FALSE],
        ['create moderation notes', AccessResult::forbidden()],
        ['create moderation notes on uneditable entities',
          AccessResult::allowed(),
        ],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'create', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'administer moderation notes' checking for the update operation.
   *
   * @covers ::checkAccess
   */
  public function testUpdateAccessAdminister() {

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('isPublished')
      ->willReturn(TRUE);
    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', TRUE],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'update', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests checking for the update operation.
   *
   * @covers ::checkAccess
   */
  public function testUpdateAccessIsOwner() {
    $this->account->expects($this->exactly(5))
      ->method('id')
      ->willReturn(1);

    $this->entity->expects($this->exactly(2))
      ->method('getOwner')
      ->willReturn($this->account);

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('isPublished')
      ->willReturn(1);
    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', FALSE],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'update', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'administer moderation notes' checking for the delete operation.
   *
   * @covers ::checkAccess
   */
  public function testDeleteAccessAdminister() {

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);

    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::allowed()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'delete', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests checking for the delete operation.
   *
   * @covers ::checkAccess
   */
  public function testDeleteAccessIsOwnerUnpublished() {
    $this->account->expects($this->exactly(5))
      ->method('id')
      ->willReturn(1);

    $this->entity->expects($this->exactly(2))
      ->method('getOwner')
      ->willReturn($this->account);

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('isPublished')
      ->willReturn(0);
    $this->entity->expects($this->once())
      ->method('hasParent')
      ->willReturn(FALSE);
    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::neutral()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'delete', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests checking for the delete operation.
   *
   * @covers ::checkAccess
   */
  public function testDeleteAccessIsOwnerHasParent() {
    $this->account->expects($this->exactly(5))
      ->method('id')
      ->willReturn(1);

    $this->entity->expects($this->exactly(2))
      ->method('getOwner')
      ->willReturn($this->account);

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);

    $this->entity->expects($this->once())
      ->method('hasParent')
      ->willReturn(TRUE);
    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::neutral()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'delete', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'administer moderation notes' checking for the  reply operation.
   *
   * @covers ::checkAccess
   */
  public function testReplyAccessAdminister() {

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);

    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::allowed()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'reply', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'create moderation note replies' checking for the create operation.
   *
   * @covers ::checkAccess
   */
  public function testReplyAccess() {

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);

    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::neutral()],
        ['create moderation note replies', AccessResult::allowed()],
        ['create moderation notes', AccessResult::forbidden()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'reply', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'create moderation notes' checking for the reply operation.
   *
   * @covers ::checkAccess
   */
  public function testReplyCreateAccess() {

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);

    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::neutral()],
        ['create moderation note replies', AccessResult::neutral()],
        ['create moderation notes', AccessResult::allowed()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'reply', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'administer moderation notes' checking for the resolve operation.
   *
   * @covers ::checkAccess
   */
  public function testResolveAccessAdminister() {

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('hasParent')
      ->willReturn(FALSE);
    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::allowed()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'resolve', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests 'resolve moderation notes on editable entities' for resolve.
   *
   * @covers ::checkAccess
   */
  public function testResolveAccessUpdateEntity() {
    $node = $this->createMock(EntityInterface::class);
    $node->expects($this->once())
      ->method('access')
      ->with('update', $this->account, TRUE)
      ->willReturn(AccessResult::allowed());

    $this->entity->expects($this->once())
      ->method('getModeratedEntity')
      ->willReturn($node);
    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('hasParent')
      ->willReturn(FALSE);
    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::neutral()],
        ['resolve moderation notes on editable entities',
          AccessResult::allowed(),
        ],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'resolve', $this->account);
    $this->assertTrue($result);
  }

  /**
   * Tests checking for the delete operation.
   *
   * @covers ::checkAccess
   */
  public function testResolveAccessIsOwner() {

    $this->account->expects($this->exactly(5))
      ->method('id')
      ->willReturn(1);

    $this->entity->expects($this->exactly(2))
      ->method('getOwner')
      ->willReturn($this->account);

    $this->entity->expects($this->once())
      ->method('language')
      ->willReturn($this->language);
    $this->entity->expects($this->once())
      ->method('getCacheContexts')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('getCacheTags')
      ->willReturn([]);
    $this->entity->expects($this->once())
      ->method('hasParent')
      ->willReturn(FALSE);
    $this->account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        ['administer moderation notes', AccessResult::neutral()],
      ]);

    $result = $this->accessControlHandler->access($this->entity, 'resolve', $this->account);
    $this->assertTrue($result);
  }

}
