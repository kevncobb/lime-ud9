<?php

/**
 * @file
 * Theme & preprocess functions for the Views Slideshow: cycle module.
 */

/**
 * Views Slideshow: Theme the main frame wrapper.
 *
 * @ingroup vss_theme
 */
function template_preprocess_views_slideshow_cycle2_main_frame(&$vars) {
  $settings = $vars['settings'];
  $rows = $vars['rows'];

  // Load jQuery Cycle2 library.
  $library_location = \Drupal::config('views_slideshow_cycle2.settings')->get('library')['location'];
  $vars['#attached']['library'][] = 'views_slideshow_cycle2/' . $library_location;
  $vars['#attached']['library'][] = 'views_slideshow_cycle2/style';

  $vars['attributes']['class'][] = 'views-slideshow-frame';

  $vars['rendered_rows'] = [];
  $items = [];
  $i = 1;
  foreach ($rows as $count => $item) {
    $item['#count'] = $i;
    $items[] = $item;
    if (!($i % (int) $settings['main']['rows_per_frame']) || $i == count($rows)) {
      $vars['rendered_rows'][] = [
        '#theme' => $vars['view']->buildThemeFunctions('views_slideshow_cycle2_main_frame_row'),
        '#items' => $items,
        '#view' => $vars['view'],
        '#count' => $count,
        '#settings' => $vars['settings'],
      ];
      $items = [];
    }
    $i++;
  }
}

/**
 * Views Slideshow slideshow rows.
 *
 * @ingroup vss_theme
 */
function template_preprocess_views_slideshow_cycle2_main_frame_row(&$vars) {
  $current = $vars['count'] + 1;
  $vars['attributes']['class'][] = 'views-slideshow-slide';
  $vars['attributes']['class'][] = 'views-slideshow-slide-' . $current;
  $vars['attributes']['class'][] = ($vars['count'] % 2) ? 'views-slideshow-slide-even' : 'views-slideshow-slide-odd';

  $vars['rendered_items'] = [];
  foreach ($vars['items'] as $item_count => $item) {
    $vars['rendered_items'][] = [
      '#theme' => $vars['view']->buildThemeFunctions('views_slideshow_cycle2_main_frame_row_item'),
      '#item' => $item,
      '#item_count' => $item_count,
      '#count' => $vars['count'],
      '#view' => $vars['view'],
      '#length' => count($vars['view']->result),
      '#settings' => $vars['settings'],
    ];
  }
}

/**
 * Views Slideshow slideshow row items.
 *
 * @ingroup vss_theme
 */
function template_preprocess_views_slideshow_cycle2_main_frame_row_item(&$vars) {
  $vars['attributes']['class'][] = 'views-row';
  $vars['attributes']['class'][] = 'views-row-' . $vars['item_count'];
  $vars['attributes']['class'][] = ($vars['item_count'] % 2) ? 'views-row-even' : 'views-row-odd';
}
