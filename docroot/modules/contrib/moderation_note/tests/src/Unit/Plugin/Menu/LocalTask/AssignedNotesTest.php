<?php

namespace Drupal\Tests\moderation_note\Unit\Plugin\Menu\LocalTask;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\moderation_note\Plugin\Menu\LocalTask\AssignedNotes;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the AssignedNotes local task plugin.
 *
 * @group moderation_note
 */
class AssignedNotesTest extends UnitTestCase {

  /**
   * The moderation note query service.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $query;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $match;

  /**
   * The user account.
   *
   * @var \Drupal\user\UserInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $account;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->query = $this->createMock(QueryInterface::class);
    $this->match = $this->createMock(ResettableStackedRouteMatchInterface::class);
    $this->account = $this->createMock(UserInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();
    $container = new ContainerBuilder();
    $container->set('string_translation', $this->stringTranslation);
    \Drupal::setContainer($container);
  }

  /**
   * Tests the getTitle method of the AssignedNotes class.
   */
  public function testGetTitlePlural() {
    // Create a mock Request.
    $request = $this->createMock(Request::class);
    $this->account->expects($this->once())
      ->method('id')
      ->willReturn(1);
    // Set up the match mock.
    $this->match
      ->expects($this->once())
      ->method('getParameter')
      ->with('user')
      ->willReturn($this->account);

    // Set up the query mock.
    $this->query
      ->expects($this->once())
      ->method('accessCheck')
      ->with(TRUE)
      ->willReturnSelf();

    $this->query
      ->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();

    $this->query
      ->expects($this->once())
      ->method('count')
      ->willReturnSelf();

    $this->query
      ->expects($this->once())
      ->method('execute')
      ->willReturn(5);

    // Create the AssignedNotes instance.
    $assignedNotes = new AssignedNotes(
      [],
      'assigned_notes',
      [],
      $this->query,
      $this->match
    );

    // Call the getTitle method and verify the output.
    $title = $assignedNotes->getTitle($request);
    $this->assertEquals('Assigned Notes (5)', $title);
  }

  /**
   * Tests the getTitle method of the AssignedNotes class.
   */
  public function testGetTitleSingle() {
    // Create a mock Request.
    $request = $this->createMock(Request::class);
    $this->account->expects($this->once())
      ->method('id')
      ->willReturn(1);
    // Set up the match mock.
    $this->match
      ->expects($this->once())
      ->method('getParameter')
      ->with('user')
      ->willReturn($this->account);

    // Set up the query mock.
    $this->query
      ->expects($this->once())
      ->method('accessCheck')
      ->with(TRUE)
      ->willReturnSelf();

    $this->query
      ->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();

    $this->query
      ->expects($this->once())
      ->method('count')
      ->willReturnSelf();

    $this->query
      ->expects($this->once())
      ->method('execute')
      ->willReturn(1);

    // Create the AssignedNotes instance.
    $assignedNotes = new AssignedNotes(
      [],
      'assigned_notes',
      [],
      $this->query,
      $this->match
    );

    // Call the getTitle method and verify the output.
    $title = $assignedNotes->getTitle($request);
    $this->assertEquals('Assigned Note (1)', $title);
  }

  /**
   * Tests the getTitle method of the AssignedNotes class.
   */
  public function testGetTitleZero() {
    // Create a mock Request.
    $request = $this->createMock(Request::class);
    $this->account->expects($this->once())
      ->method('id')
      ->willReturn(1);
    // Set up the match mock.
    $this->match
      ->expects($this->once())
      ->method('getParameter')
      ->with('user')
      ->willReturn($this->account);

    // Set up the query mock.
    $this->query
      ->expects($this->once())
      ->method('accessCheck')
      ->with(TRUE)
      ->willReturnSelf();

    $this->query
      ->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();

    $this->query
      ->expects($this->once())
      ->method('count')
      ->willReturnSelf();

    $this->query
      ->expects($this->once())
      ->method('execute')
      ->willReturn(0);

    // Create the AssignedNotes instance.
    $assignedNotes = new AssignedNotes(
      [],
      'assigned_notes',
      [],
      $this->query,
      $this->match
    );

    // Call the getTitle method and verify the output.
    $title = $assignedNotes->getTitle($request);
    $this->assertEquals('Assigned Notes (0)', $title);
  }

}
