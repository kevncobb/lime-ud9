<?php

namespace Drupal\facets_exposed_filters\Plugin\views\filter;

use Drupal\Component\Utility\Random;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\facets_exposed_filters\Plugin\views\FacetsViewsPluginTrait;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides exposing facets as a filter.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("facets_filter")
 */
class FacetsFilter extends FilterPluginBase {

  use FacetsViewsPluginTrait;

  /**
   * {@inheritdoc}
   */
  public $no_operator = TRUE;

  /**
   * The facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * The entity storage used for facets.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $facetStorage;

  /**
   * Constructs a new FacetsFilter instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   The facet manager.
   * @param \Drupal\Core\Entity\EntityStorageInterface $facet_storage
   *   The entity storage used for facets.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DefaultFacetManager $facet_manager, EntityStorageInterface $facet_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->facetManager = $facet_manager;
    $this->facetStorage = $facet_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('facets.manager'),
      $container->get('entity_type.manager')->getStorage('facets_facet')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $random = new Random();
    $options = parent::defineOptions();
    $options['exposed'] = ['default' => TRUE];
    $options['expose']['contains']['identifier'] = ['default' => 'facet_' . $random->name()];
    $options['expose']['contains']['multiple'] = ['default' => TRUE];
    $options['facet']['default'] = NULL;
    $options['label_display']['default'] = BlockPluginInterface::BLOCK_LABEL_VISIBLE;
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $this->facetsViewsBuildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    return $this->options['facet'];
  }

  /**
   * {@inheritdoc}
   */
  public function valueForm(&$form, FormStateInterface $form_state) {

    // Dirty check if we are in views UI. Return no values as it removes
    // settings from the view when editing settings for an existing facet
    // filter.
    if(isset($form["admin_label"])) {
      $form['value'] = [];
      return;
    }

    static $is_processing = NULL;

    if ($is_processing) {
      $form_state->set('facets_not_built', TRUE);
      $form['value'] = [];
      return;
    }

    $is_processing = TRUE;
    $form['value'] = $this->facetsViewsGetFacets();
    $is_processing = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function acceptExposedInput($input) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateExposeForm($form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function canGroup() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

}
