<?php

namespace Drupal\facets_exposed_filters\Plugin\facets\url_processor;

use Drupal\Core\Cache\UnchangingCacheableDependencyTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\facets\FacetInterface;
use Drupal\facets\UrlProcessor\UrlProcessorPluginBase;
use Drupal\views\Entity\View;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Query string URL processor.
 *
 * @FacetsUrlProcessor(
 *   id = "views_exposed_filters",
 *   label = @Translation("Views exposed filters"),
 *   description = @Translation("Views exposed filters moves all url logic to
 *   Views, which provides a better AJAX/Views integration.")
 * )
 */
class ViewsExposedFilters extends UrlProcessorPluginBase {

  use UnchangingCacheableDependencyTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Request $request, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $request, $entity_type_manager);
    $this->initializeActiveFilters();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildUrls(FacetInterface $facet, array $results) {
    // No results are found for this facet, so don't try to create urls.
    if (empty($results)) {
      return [];
    }

    $display_id = $this->configuration['facet']->getFacetSource()->pluginDefinition["display_id"];
    $parts = explode(':', $display_id);
    $views_info = explode('__', $parts[1]);
    $views_name = $views_info[0];
    $views_display = $views_info[1];

    // We dont build real urls here. Views will handle this part for us. However
    // our code still depends on the url being set, so we set all results to use
    // the views display url.
    $url = Url::fromRoute('view.' . $views_name . '.' . $views_display);
    foreach ($results as $key => $result) {
      $results[$key]->setUrl($url);
    }

    return $results;
  }

  /**
   * Initializes the active filters from the request query.
   *
   * Get all the filters that are active by checking the request query and store
   * them in activeFilters which is an array where key is the facet id and value
   * is an array of raw values.
   */
  protected function initializeActiveFilters() {
    $url_parameters = $this->request->query;
    $active_filters = [];
    $display_id = $this->configuration['facet']->getFacetSource()->pluginDefinition["display_id"];
    $parts = explode(':', $display_id);
    $views_info = explode('__', $parts[1]);
    $views_name = $views_info[0];
    $views_display = $views_info[1];
    $view = View::load($views_name);
    $display = $view->getDisplay($views_display);
    if (!isset($display["display_options"]["filters"])) {
      $display = $view->getDisplay('default');
    }
    foreach ($display["display_options"]["filters"] as $filter) {
      if ($filter["plugin_id"] == 'facets_filter') {
        $facet_id = $filter['facet'];
        $identifier = $filter["expose"]["identifier"];
        $query_params = $url_parameters->all();
        if (isset($query_params[$identifier]) && $query_params[$identifier]) {
          if ($filter["expose"]["multiple"]) {
            $active_filters[$facet_id] = $url_parameters->all()[$identifier];
          }
          else {
            if ($url_parameters->all()[$identifier] != 'All') {
              $active_filters[$facet_id][] = $url_parameters->all()[$identifier];
            }
          }
        }
      }
    }
    $this->activeFilters = $active_filters;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['url.query_args'];
  }

}
