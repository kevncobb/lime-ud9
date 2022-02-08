<?php

namespace Drupal\organigrams\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\VocabularyInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\organigrams\TaxonomyTermTree;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for organigrams.module.
 */
class OrganigramsController extends ControllerBase {

  /**
   * The Taxonomy Tree builder.
   *
   * @var \Drupal\organigrams\TaxonomyTermTree
   */
  protected $taxonomyTermTree;

  /**
   * Constructs a OrganigramsController object.
   *
   * @param \Drupal\organigrams\TaxonomyTermTree $taxonomyTermTree
   *   The Taxonomy Tree builder.
   */
  public function __construct(TaxonomyTermTree $taxonomyTermTree) {
    $this->taxonomyTermTree = $taxonomyTermTree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('organigrams.taxonomy_term_tree'));
  }

  /**
   * Returns a form to add a new vocabulary.
   *
   * @return array
   *   The vocabulary add form.
   */
  public function addForm() {
    $term = $this->entityTypeManager()->getStorage('taxonomy_vocabulary')->create();
    return $this->entityFormBuilder()->getForm($term);
  }

  /**
   * Returns a form to add a new term to a vocabulary.
   *
   * @param \Drupal\taxonomy\VocabularyInterface $taxonomy_vocabulary
   *   The vocabulary this term will be added to.
   *
   * @return array
   *   The organigram as a render array.
   */
  public function viewOrganigram(VocabularyInterface $taxonomy_vocabulary) {
    $output = [];

    // Check for permission.
    if (!$this->currentUser()->hasPermission('view organigrams')) {
      return $output;
    }

    // Construct the orgchart settings.
    $organigram_settings = [
      'organigram_settings' => $taxonomy_vocabulary->getThirdPartySettings('organigrams'),
      'nodes' => [],
    ];

    // Use our own service to get the hierarchical overview of taxonomy terms
    // in this vocabulary.
    $output = $this->taxonomyTermTree->loadList($taxonomy_vocabulary->id());

    // Include the excanvas library if it exists.
    $output['#attached']['library'][] = 'organigrams/explorercanvas';

    // Include the orgchart library.
    $output['#attached']['library'][] = 'organigrams/orgchart';

    // Include the organigram content loader.
    $output['#attached']['library'][] = 'organigrams/organigrams';

    // Add the organigram to the organigrams list.
    $output['#attached']['drupalSettings']['organigrams']['organigrams'] = [
      $taxonomy_vocabulary->id() => $organigram_settings,
    ];

    return $output;
  }

  /**
   * Checks if a vocabulary contains organigrams settings.
   *
   * @param \Drupal\taxonomy\VocabularyInterface $taxonomy_vocabulary
   *   The vocabulary to perform the access check on.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function viewOrganigramAccess(VocabularyInterface $taxonomy_vocabulary) {
    if (!empty($taxonomy_vocabulary->getThirdPartySettings('organigrams'))) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
