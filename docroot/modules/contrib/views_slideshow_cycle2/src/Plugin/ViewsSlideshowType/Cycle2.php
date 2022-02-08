<?php

namespace Drupal\views_slideshow_cycle2\Plugin\ViewsSlideshowType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\views_slideshow\ViewsSlideshowTypeBase;
use Drupal\Core\Link;

/**
 * Provides a slideshow type based on jquery cycle.
 *
 * @ViewsSlideshowType(
 *   id = "views_slideshow_cycle2",
 *   label = @Translation("Cycle2"),
 *   accepts = {},
 *   calls = {},
 * )
 */
class Cycle2 extends ViewsSlideshowTypeBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'contains' => [
        'main' => ['default' => [
          'rows_per_frame' => 1,
          'paused' => FALSE,
          'reverse' => FALSE,
          'random' => FALSE,
          'swipe' => FALSE,
          'swipe-fx' => FALSE,
        ]],
        'transition' => ['default' => [
          'fx' => 'fade',
          'advanced' => FALSE,
          'timeout' => 4000,
          'speed' => 500,
          'manual-speed' => NULL,
        ]],
        'pager' => ['default' => [
          'type' => 'default',
          'pager' => NULL,
          'pager-template' => '<span>&bull;</span>',
          'pager-event' => 'click',
        ]],
        'controls' => ['default' => [
          'type' => 'default',
          'use_text' => FALSE,
          'prev_text' => 'Previous',
          'next_text' => 'Next',
        ]]
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $config = $this->getConfiguration();

    $form['views_slideshow_cycle2']['#tree'] = TRUE;

    $form['views_slideshow_cycle2']['main'] = $this->_mainSection($config['main']);
    $form['views_slideshow_cycle2']['transition'] = $this->_transitionSection($config['transition']);
    $form['views_slideshow_cycle2']['pager'] = $this->_pagerSection($config['pager']);
    $form['views_slideshow_cycle2']['controls'] = $this->_controlsSection($config['controls']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  private function _mainSection($config) {
    $section = [
      '#type' => 'details',
      '#title' => $this->t('Main settings'),
      '#open' => TRUE,
    ];
    $section['rows_per_frame'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of rows per frame'),
      '#default_value' => $config['rows_per_frame'],
    ];
    $section['paused'] = [
      '#type' => 'checkbox',
      '#title' => 'paused',
      '#description' => $this->t('If true the slideshow will begin in a paused state.'),
      '#default_value' => $config['paused'],
    ];
    $section['random'] = [
      '#type' => 'checkbox',
      '#title' => 'random',
      '#description' => $this->t('If true the order of the slides will be randomized.'),
      '#default_value' => $config['random'],
    ];
    $section['reverse'] = [
      '#type' => 'checkbox',
      '#title' => 'reverse',
      '#description' => $this->t('If true the slideshow will proceed in reverse order and transitions that support this option will run a reverse animation.'),
      '#default_value' => $config['reverse'],
    ];
    $section['swipe'] = [
      '#type' => 'checkbox',
      '#title' => 'swipe',
      '#description' => $this->t('Set to true to enable swipe gesture support for advancing the slideshow forward or back.'),
      '#default_value' => $config['swipe'],
    ];
    $section['swipe-fx'] = [
      '#type' => 'select',
      '#title' => 'swipe-fx',
      '#description' => $this->t('The transition effect to use for swipe-triggered transitions. If not provided the transition declared in the data-manual-fx or data-fx attribute will be used.'),
      '#options' => $this->_fxTypes(),
      '#default_value' => $config['swipe-fx'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[views_slideshow_cycle2][main][swipe]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    return $section;
  }

  private function _transitionSection($config) {
    $section = [
      '#type' => 'details',
      '#title' => $this->t('Transition'),
      '#open' => TRUE,
    ];
    $section['fx'] = [
      '#type' => 'select',
      '#title' => $this->t('Effect') . ' (fx)',
      '#description' => $this->t('The name of the slideshow transition to use.'),
      '#options' => $this->_fxTypes(),
      '#default_value' => $config['fx'],
    ];
    $section['advanced'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Advanced settings'),
      '#default_value' => $config['advanced'],
    ];
    $section['timeout'] = [
      '#type' => 'textfield',
      '#size' => 6,
      '#title' => 'timeout',
      '#description' => $this->t('The time between slide transitions in milliseconds.'),
      '#default_value' => $config['timeout'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[views_slideshow_cycle2][transition][advanced]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $section['speed'] = [
      '#type' => 'textfield',
      '#size' => 6,
      '#title' => 'speed',
      '#description' => $this->t('The speed of the transition effect in milliseconds.'),
      '#default_value' => $config['speed'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[views_slideshow_cycle2][transition][advanced]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $section['manual-speed'] = [
      '#type' => 'textfield',
      '#size' => 6,
      '#title' => 'manual-speed',
      '#description' => $this->t('The speed (in milliseconds) for transitions that are manually initiated, such as those caused by clicking a "next" button or a pager link. By default, manual transitions occur at the same speed as automatic (timer-based) transitions.'),
      '#default_value' => $config['manual-speed'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[views_slideshow_cycle2][transition][advanced]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $section;
  }

  private function _pagerSection($config) {
    $section = [
      '#type' => 'details',
      '#title' => $this->t('Pager'),
      '#open' => TRUE,
    ];
    $section['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $this->_detailTypes(),
      '#default_value' => $config['type'],
    ];
    $section['pager'] = [
      '#type' => 'textfield',
      '#size' => 60,
      '#title' => $this->t('A selector string which identifies the element to use as the container for pager links.'),
      '#default_value' => $config['pager'],
      '#states' => [
        'visible' => [
          'select[name="style_options[views_slideshow_cycle2][pager][type]"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $section['pager-template'] = [
      '#type' => 'textfield',
      '#size' => 60,
      '#title' => $this->t('A template string which defines how the pager links should be formatted.'),
      '#default_value' => $config['pager-template'],
      '#states' => [
        'visible' => [
          'select[name="style_options[views_slideshow_cycle2][pager][type]"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $section['pager-event'] = [
      '#type' => 'textfield',
      '#size' => 60,
      '#title' => $this->t('The type of event that is bound on the pager links. By default, Cycle2 binds click events.'),
      '#default_value' => $config['pager-event'],
      '#states' => [
        'visible' => [
          'select[name="style_options[views_slideshow_cycle2][pager][type]"]' => ['value' => 'custom'],
        ],
      ],
    ];

    return $section;
  }

  private function _controlsSection($config) {
    $section = [
      '#type' => 'details',
      '#title' => $this->t('Controls'),
      '#open' => TRUE,
    ];
    $section['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $this->_detailTypes(),
      '#default_value' => $config['type'],
    ];
    $section['use_text'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use text'),
      '#default_value' => $config['use_text'],
      '#states' => [
        'visible' => [
          'select[name="style_options[views_slideshow_cycle2][controls][type]"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $section['prev_text'] = [
      '#type' => 'textfield',
      '#size' => 20,
      '#title' => $this->t('Label for previous frame action'),
      '#default_value' => $config['prev_text'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[views_slideshow_cycle2][controls][use_text]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $section['next_text'] = [
      '#type' => 'textfield',
      '#size' => 20,
      '#title' => $this->t('Label for next frame action'),
      '#default_value' => $config['next_text'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[views_slideshow_cycle2][controls][use_text]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $section;
  }

  private function _detailTypes() {
    return [
      'none' => $this->t('None'),
      'default' => $this->t('Default'),
      'custom' => $this->t('Custom'),
    ];
  }

  private function _fxTypes() {
    return [
      'none' => 'none',
      'fade' => 'Fade',
      'fadeout' => 'FadeOut',
      'scrollHorz' => 'ScrollHorz',
    ];
  }

}
