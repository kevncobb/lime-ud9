<?php

namespace Drupal\facets_exposed_filters\Plugin\views;

use Drupal\Core\Form\FormStateInterface;

/**
 * Helper for the main Views plugin.
 */
trait FacetsViewsPluginTrait {

  /**
   * Builds the options form.
   *
   * @param array $form
   *   The form array that is being added to.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function facetsViewsBuildOptionsForm(array &$form, FormStateInterface $form_state) {
    $options = [];

    /** @var \Drupal\facets\Entity\Facet[] $facets */
    $facets = $this->facetStorage->loadMultiple();

    $format = 'search_api:views_%s__%s__%s';
    $source = sprintf($format, $this->view->getDisplay()
      ->getPluginId(), $this->view->id(), $this->view->current_display);
    foreach ($facets as $facet) {
      if ($facet->getFacetSourceId() === $source) {
        $options[$facet->id()] = $facet->label();
      }
    }

    $form['facet'] = [
      '#title' => 'Facet',
      '#options' => $options,
      '#type' => 'radios',
      '#required' => TRUE,
      '#default_value' => isset($this->options['facet']) ? $this->options['facet'] : NULL,
    ];
  }

  /**
   * Gets the facets to render.
   *
   * @return array
   *   The facet blocks to be output, in render array format.
   */
  public function facetsViewsGetFacets() {
    $build = [];

    if (!$this->options['facet']) {
      return $build;
    }

    /** @var \Drupal\facets\Entity\Facet $facet */
    $facet = $this->facetStorage->load($this->options['facet']);
    $facet_build = $this->facetManager->build($facet);

    if (!isset($facet_build[0]) || !$facet_build[0]) {
      return $build;
    }

    $options = [];
    // Empty behavior is not supported. Ensure we have actual results.
    if (isset($facet_build[0]) && !isset($facet_build[0]["#type"])) {
      $build = [
        '#type' => 'select',
        '#options' => $facet_build[0],
        '#multiple' => $this->options["expose"]["multiple"],
      ];
    }

    return $build;
  }

}
