<?php

namespace Drupal\Tests\moderation_note\Unit\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\moderation_note\Service\MenuCountService;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * @coversDefaultClass \Drupal\moderation_note\Service\MenuCountService
 * @group moderation_note
 */
class MenuCountServiceTest extends UnitTestCase {

  /**
   * The prophet.
   *
   * @var \Prophecy\Prophet
   */
  private $prophet;

  /**
   * The mocked moderation note storage.
   *
   * @var \Drupal\Core\Entity\Sql\SqlEntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moderationNoteStorage;

  /**
   * The mocked entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The mocked translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $translation;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->prophet = new Prophet();
    $this->moderationNoteStorage = $this->createMock(SqlEntityStorageInterface::class);
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->translation = $this->createMock(TranslationInterface::class);
  }

  /**
   * @covers ::contentLink
   */
  public function testContentLink() {
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('moderation_note')
      ->willReturn($this->moderationNoteStorage);

    $query = $this->createMock(QueryInterface::class);
    $query->expects($this->once())
      ->method('accessCheck')
      ->with(TRUE)
      ->willReturnSelf();

    $query->expects($this->exactly(4))
      ->method('condition')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('count')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('execute')
      ->willReturn(2);

    $this->moderationNoteStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($query);

    $url = $this->prophet->prophesize(Url::class);
    $url->fromRoute('moderation_note.list', Argument::type('array'))->willReturn($url->reveal());

    $this->translation->expects($this->once())
      ->method('formatPlural')
      ->with(2, 'View Note (1)', 'View Notes (@count)')
      ->willReturn('View Notes (2)');

    $service = new MenuCountService(
      $this->entityTypeManager,
      $this->translation
    );

    $link = $service->contentLink('node', 123);

    // Perform assertions on $link.
    $this->assertEquals('menu_local_task', $link['#theme']);
    $this->assertEquals('View Notes (2)', $link['#link']['title']);
    $this->assertEquals(['use-ajax'], $link['#link']['localized_options']['attributes']['class']);
    $this->assertEquals('dialog', $link['#link']['localized_options']['attributes']['data-dialog-type']);
    $this->assertEquals('off_canvas', $link['#link']['localized_options']['attributes']['data-dialog-renderer']);

  }

  /**
   * @covers ::contentLink
   */
  public function testContentLinkSingle() {
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('moderation_note')
      ->willReturn($this->moderationNoteStorage);

    $query = $this->createMock(QueryInterface::class);
    $query->expects($this->once())
      ->method('accessCheck')
      ->with(TRUE)
      ->willReturnSelf();

    $query->expects($this->exactly(4))
      ->method('condition')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('count')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('execute')
      ->willReturn(1);

    $this->moderationNoteStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($query);

    $url = $this->prophet->prophesize(Url::class);
    $url->fromRoute('moderation_note.list', Argument::type('array'))->willReturn($url->reveal());

    $this->translation->expects($this->once())
      ->method('formatPlural')
      ->with(1, 'View Note (1)', 'View Notes (@count)')
      ->willReturn('View Note (1)');

    $service = new MenuCountService(
      $this->entityTypeManager,
      $this->translation
    );

    $link = $service->contentLink('node', 123);

    // Perform assertions on $link.
    $this->assertEquals('menu_local_task', $link['#theme']);
    $this->assertEquals('View Note (1)', $link['#link']['title']);
    $this->assertEquals(['use-ajax'], $link['#link']['localized_options']['attributes']['class']);
    $this->assertEquals('dialog', $link['#link']['localized_options']['attributes']['data-dialog-type']);
    $this->assertEquals('off_canvas', $link['#link']['localized_options']['attributes']['data-dialog-renderer']);

  }

  /**
   * @covers ::contentLink
   */
  public function testContentLinkZero() {
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('moderation_note')
      ->willReturn($this->moderationNoteStorage);

    $query = $this->createMock(QueryInterface::class);
    $query->expects($this->once())
      ->method('accessCheck')
      ->with(TRUE)
      ->willReturnSelf();

    $query->expects($this->exactly(4))
      ->method('condition')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('count')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('execute')
      ->willReturn(0);

    $this->moderationNoteStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($query);

    $url = $this->prophet->prophesize(Url::class);
    $url->fromRoute('moderation_note.list', Argument::type('array'))->willReturn($url->reveal());

    $this->translation->expects($this->once())
      ->method('formatPlural')
      ->with(0, 'View Note (1)', 'View Notes (@count)')
      ->willReturn('View Notes (0)');

    $service = new MenuCountService(
      $this->entityTypeManager,
      $this->translation
    );

    $link = $service->contentLink('node', 123);

    // Perform assertions on $link.
    $this->assertEquals('menu_local_task', $link['#theme']);
    $this->assertEquals('View Notes (0)', $link['#link']['title']);
    $this->assertEquals(['use-ajax'], $link['#link']['localized_options']['attributes']['class']);
    $this->assertEquals('dialog', $link['#link']['localized_options']['attributes']['data-dialog-type']);
    $this->assertEquals('off_canvas', $link['#link']['localized_options']['attributes']['data-dialog-renderer']);

  }

  /**
   * @covers ::assignedTo
   */
  public function testAssignedTo() {
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('moderation_note')
      ->willReturn($this->moderationNoteStorage);

    $query = $this->createMock(QueryInterface::class);
    $query->expects($this->once())
      ->method('accessCheck')
      ->with(TRUE)
      ->willReturnSelf();
    $query->expects($this->exactly(3))
      ->method('condition')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('count')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('execute')
      ->willReturn(3);

    $this->moderationNoteStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($query);

    $url = $this->prophet->prophesize(Url::class);
    $url->fromRoute('moderation_note.assigned_list', Argument::type('array'))->willReturn($url->reveal());

    $this->translation->expects($this->once())
      ->method('formatPlural')
      ->with(3, 'Assigned Note (1)', 'Assigned Notes (@count)')
      ->willReturn('Assigned Notes (3)');

    $service = new MenuCountService(
      $this->entityTypeManager,
      $this->translation
    );

    $link = $service->assignedTo(5);

    // Perform assertions on $link.
    $this->assertEquals('links__toolbar_user', $link['#theme']);
    $this->assertEquals('Assigned Notes (3)', $link['#links']['moderation_note_link']['title']);
    $this->assertEquals(['toolbar-menu', 'moderation-note'], $link['#attributes']['class']);

  }

  /**
   * @covers ::assignedTo
   */
  public function testAssignedToSingle() {
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('moderation_note')
      ->willReturn($this->moderationNoteStorage);

    $query = $this->createMock(QueryInterface::class);
    $query->expects($this->once())
      ->method('accessCheck')
      ->with(TRUE)
      ->willReturnSelf();
    $query->expects($this->exactly(3))
      ->method('condition')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('count')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('execute')
      ->willReturn(1);

    $this->moderationNoteStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($query);

    $url = $this->prophet->prophesize(Url::class);
    $url->fromRoute('moderation_note.assigned_list', Argument::type('array'))->willReturn($url->reveal());

    $this->translation->expects($this->once())
      ->method('formatPlural')
      ->with(1, 'Assigned Note (1)', 'Assigned Notes (@count)')
      ->willReturn('Assigned Note (1)');

    $service = new MenuCountService(
      $this->entityTypeManager,
      $this->translation
    );

    $link = $service->assignedTo(5);

    // Perform assertions on $link.
    $this->assertEquals('links__toolbar_user', $link['#theme']);
    $this->assertEquals('Assigned Note (1)', $link['#links']['moderation_note_link']['title']);
    $this->assertEquals(['toolbar-menu', 'moderation-note'], $link['#attributes']['class']);

  }

  /**
   * @covers ::assignedTo
   */
  public function testAssignedToZero() {
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('moderation_note')
      ->willReturn($this->moderationNoteStorage);

    $query = $this->createMock(QueryInterface::class);
    $query->expects($this->once())
      ->method('accessCheck')
      ->with(TRUE)
      ->willReturnSelf();
    $query->expects($this->exactly(3))
      ->method('condition')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('count')
      ->willReturnSelf();
    $query->expects($this->once())
      ->method('execute')
      ->willReturn(0);

    $this->moderationNoteStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($query);

    $url = $this->prophet->prophesize(Url::class);
    $url->fromRoute('moderation_note.assigned_list', Argument::type('array'))->willReturn($url->reveal());

    $this->translation->expects($this->once())
      ->method('formatPlural')
      ->with(0, 'Assigned Note (1)', 'Assigned Notes (@count)')
      ->willReturn('Assigned Notes (0)');

    $service = new MenuCountService(
      $this->entityTypeManager,
      $this->translation
    );

    $link = $service->assignedTo(5);

    // Perform assertions on $link.
    $this->assertEquals('links__toolbar_user', $link['#theme']);
    $this->assertEquals('Assigned Notes (0)', $link['#links']['moderation_note_link']['title']);
    $this->assertEquals(['toolbar-menu', 'moderation-note'], $link['#attributes']['class']);

  }

}
