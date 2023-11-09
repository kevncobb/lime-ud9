<?php

namespace Drupal\facets_exposed_filters\Plugin\facets\widget;

use Drupal\facets\FacetInterface;
use Drupal\facets\Widget\WidgetPluginBase;

/**
 * A specific widget used with the views_exposed_filters url processor.
 *
 * @FacetsWidget(
 *   id = "views_exposed_filter",
 *   label = @Translation("Views exposed filters"),
 * )
 */
class ViewsExposedFilterWidget extends WidgetPluginBase {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $results = $facet->getResults();
    return $this->buildOneLevel($results);
  }

  /**
   * Builds one level from results.
   *
   * @param \Drupal\facets\Result\ResultInterface[] $results
   *   A list of results.
   *
   * @return array
   *   Generated build.
   */
  protected function buildOneLevel(array $results): array {
    $items = [];

    foreach ($results as $result) {
      $label = $result->getDisplayValue();
      if ($this->getConfiguration()['show_numbers'] && $result->getCount() !== FALSE) {
        $label .= ' ('. $result->getCount() .')';
      }
      $items[$result->getRawValue()] = $label;
    }

    return $items;
  }

}
