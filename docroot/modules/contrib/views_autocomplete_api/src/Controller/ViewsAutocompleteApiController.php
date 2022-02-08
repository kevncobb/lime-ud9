<?php

namespace Drupal\views_autocomplete_api\Controller;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\views\Entity\View;
use Drupal\views\ViewExecutable;
use Drupal\views\ViewExecutableFactory;
use Drupal\views_autocomplete_api\Service\ViewsAutocompleteApiManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ViewAutocompleteController. Provides an autocomplete with views route.
 *
 * @package Drupal\views_autocomplete_api\Controller
 *
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 */
class ViewsAutocompleteApiController extends ControllerBase {

  /**
   * @var \Drupal\views_autocomplete_api\Service\ViewsAutocompleteApiManager
   */
  protected $viewsAutocompleteManager;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  use LoggerChannelTrait;

  /**
   * ViewsAutocompleteApiController constructor.
   *
   * @param \Drupal\views_autocomplete_api\Service\ViewsAutocompleteApiManager $views_autocomplete_manager
   */
  public function __construct(ViewsAutocompleteApiManager $views_autocomplete_manager) {
    $this->viewsAutocompleteManager = $views_autocomplete_manager;
    $this->logger = $this->getLogger('views_auto_complete_api');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('views_autocomplete_api.manager')
    );
  }

  /**
   * Callback controller return the views results data in JSON.
   *
   * @param array $view_name
   *   Array of views loaded see ViewsConverter.
   * @param string $display_id
   *   Display id of view to used.
   * @param string $views_arguments
   *   Views argument to used.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The http request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getViewsDataJson(array $view_name, $display_id, $views_arguments, Request $request) {

    // Get the query.
    $search = $request->query->get('q');
    if (empty($search) || empty($view_name)) {
      $this->logger->error(
        'Calling views autocomplete API without search words or views name.'
      );

      return new JsonResponse([]);
    }
    $display_ids = $this->viewsAutocompleteManager->getViewsDisplayId($display_id, count($view_name));
    $args_views = $this->viewsAutocompleteManager->prepareArgumentViews(
      $views_arguments,
      count($view_name)
    );

    // autocomplete).
    $view_data = [];
    // Initialise the view data which we formatted to return a correct
    // key-Value.
    $delta = 0;
    foreach ($view_name as $view_id => $view) {
      if (empty($view)) {
        $this->logger->error('Can\'t load views "%view_name"', ['%view_name' => $view_id]);
        $delta++;
        continue;
      }
      if (empty($display_ids[$delta])) {
        $display_ids[$delta] = 'default';
      }
      $this->viewsAutocompleteManager->executeViews($view, $display_ids[$delta], $search, $view_data, $args_views[$delta]);
      $delta++;
    }

    return new JsonResponse($view_data);
  }

}
