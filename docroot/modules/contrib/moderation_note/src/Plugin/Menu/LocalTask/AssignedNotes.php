<?php

namespace Drupal\moderation_note\Plugin\Menu\LocalTask;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a local task that shows the number of notes.
 */
class AssignedNotes extends LocalTaskDefault implements ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /**
   * The moderation note query service.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $query;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface
   */
  protected $match;

  /**
   * Construct the Assigned Notes object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The moderation note query service.
   * @param \Drupal\Core\Routing\ResettableStackedRouteMatchInterface $match
   *   The route match service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    QueryInterface $query,
    ResettableStackedRouteMatchInterface $match
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->query = $query;
    $this->match = $match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
        ->getStorage('moderation_note')
        ->getQuery(),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(Request $request = NULL) {

    $user = $this->match->getParameter('user');
    if ($user instanceof UserInterface) {
      $assignee_id = $user->id();
    }
    elseif (is_string($user)) {
      $assignee_id = intval($user);
    }
    else {
      return $this->t('Assigned Notes');
    }

    $count = $this->query->accessCheck(TRUE)
      ->condition('assignee', $assignee_id)
      ->condition('published', 1)
      ->count()
      ->execute();

    return $this->formatPlural($count, 'Assigned Note (1)', 'Assigned Notes (@count)');
  }

}
