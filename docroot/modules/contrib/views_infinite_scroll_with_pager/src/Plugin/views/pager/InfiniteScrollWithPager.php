<?php

namespace Drupal\views_infinite_scroll_with_pager\Plugin\views\pager;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views_infinite_scroll\Plugin\views\pager\InfiniteScroll;

/**
 * Views pager plugin to handle infinite scrolling.
 *
 * @ViewsPager(
 *  id = "infinite_scroll_with_pager",
 *  title = @Translation("Infinite Scroll with Pager"),
 *  short_title = @Translation("Infinite Scroll With Pager"),
 *  help = @Translation("A views plugin which provides infinte scroll with pager."),
 *  theme = "views_infinite_scroll_with_pager"
 * )
 */
class InfiniteScrollWithPager extends InfiniteScroll {

  public function defineOptions() {
    $options = parent::defineOptions();

    $options['pager_values']['contains'] = [
      'quantity' => ['default' => 9],
      'user_friendly_keys' => ['default' => TRUE],
      'first' => ['default' => $this->t('« First')],
      'previous' => ['default' => '«'],
      'next' => ['default' => '»'],
      'last' => ['default' => $this->t('Last »')],
    ];

    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $options = $this->options['pager_values'];
    $form['pager_values'] = [
      '#title' => $this->t('Infinite Scroll Pager Options'),
      '#type' => 'details',
      '#open' => TRUE,
      '#tree' => TRUE,
      '#input' => TRUE,
      // Setting weight to -99 to be just below the infinite scroll settings
      '#weight' => -99,
      'quantity' => [
        '#type' => 'number',
        '#min' => 0,
        '#title' => $this->t('Number of pager links visible'),
        '#description' => $this->t('Specify the number of links to pages to display in the pager.'),
        '#default_value' => $options['quantity'],
      ],
      'user_friendly_keys' => [
        '#type' => 'checkbox',
        '#title' => $this->t('User friendly keys'),
        '#description' => $this->t('Pager numbers shown to user will be starting with 1 instead of 0. Does not affect functionality'),
        '#default_value' => $options['user_friendly_keys'],
      ],
      'first' => [
        '#type' => 'textfield',
        '#title' => $this->t('First page link text'),
        '#default_value' => $options['first'],
      ],
      'previous' => [
        '#type' => 'textfield',
        '#title' => $this->t('Previous page link text'),
        '#default_value' => $options['previous'],
      ],
      'next' => [
        '#type' => 'textfield',
        '#title' => $this->t('Next page link text'),
        '#default_value' => $options['next'],
      ],
      'last' => [
        '#type' => 'textfield',
        '#title' => $this->t('Last page link text'),
        '#default_value' => $options['last'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($input) {
    $render = parent::render($input);
    $options = $this->options['pager_values'];
    $tags = [
      'user_friendly_keys' => $options['user_friendly_keys'],
      'first' => $options['first'],
      'previous' => $options['previous'],
      'next' => $options['next'],
      'last' => $options['last'],
    ];
    $total_pages = $this->options['total_pages'] !== NULL
      ? $this->options['total_pages'] : 9;

    $quantity = $options['quantity'] !== null
      ? $options['quantity'] : $total_pages;

    // Makes sure that amount of pager links will correspond with amount of items
    if ($quantity * $this->getItemsPerPage()  > $this->getTotalItems()) {
      $quantity = (int) ceil($this->getTotalItems() / $this->getItemsPerPage());
    }

    $render['#tags'] = $tags;
    $render['#theme'] = $this->themeFunctions();
    $render['#quantity'] = $quantity;
    $render['#route_name'] = !empty($this->view->live_preview) ? '<current>' : '<none>';
    $render['#attached'] = [
      'library' => ['views_infinite_scroll_with_pager/views-infinite-scroll-with-pager'],
    ];

    return $render;
  }
}
