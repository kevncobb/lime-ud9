<?php

namespace Drupal\dark_mode_switch\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Dark Mode Switch' Block.
 *
 * @Block(
 *   id = "dark_mode_switch_block",
 *   admin_label = @Translation("Dark Mode Switch"),
 *   category = @Translation("Dark Mode"),
 * )
 */
class DarkModeSwitch extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $config = \Drupal::config('dark_mode_switch.settings');
    $dark_class = $config->get('dark_class');
    $parent_element = $config->get('parent_element');

    $build = [];
    $build['#theme'] = 'dark_mode_switch_block';
    $build['#attached']['library'][] = 'dark_mode_switch/dark_mode_switch';
    $build['#attached']['drupalSettings']['dark_mode_switch']['dark_class'] = $dark_class;
    $build['#attached']['drupalSettings']['dark_mode_switch']['parent_element'] = $parent_element;

    return $build;
  }

}
