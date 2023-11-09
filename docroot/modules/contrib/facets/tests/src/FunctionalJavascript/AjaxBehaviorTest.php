<?php

namespace Drupal\Tests\facets\FunctionalJavascript;

use Drupal\views\Entity\View;

/**
 * Tests for the JS that powers ajax.
 *
 * @group facets
 */
class AjaxBehaviorTest extends JsBase {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Force ajax.
    $view = View::load('search_api_test_view');
    $display = $view->getDisplay('page_1');
    $display['display_options']['use_ajax'] = TRUE;
    $view->save();
  }

  /**
   * Tests links with exposed filters.
   */
  public function testLinksWithExposedFilter() {
    $view = View::load('search_api_test_view');
    $display = $view->getDisplay('page_1');
    $display['display_options']['filters']['search_api_fulltext']['expose']['required'] = TRUE;
    $view->save();

    $this->createFacet('owl');
    $this->drupalGet('search-api-test-fulltext');

    $page = $this->getSession()->getPage();
    $block_owl = $page->findById('block-owl-block');
    $block_owl->isVisible();

    $this->assertSession()->fieldExists('edit-search-api-fulltext')->setValue('baz');
    $this->click('.form-submit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Displaying 3 search results');

    $owl_link = $this->assertSession()->elementExists("xpath", "//label[@for='owl-item']/span[1]");
    $owl_link->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Displaying 1 search results');
  }

}
