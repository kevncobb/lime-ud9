<?php

namespace Drupal\l10n_client_ui\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\l10n_client_ui\Ajax\SaveTranslationCommand;

/**
 * Settings form for the localization client user interface module.
 */
class TranslationForm extends FormBase {

  /**
   * Enabled language names. An array is keyed by language code.
   *
   * @var array
   */
  protected $languages;

  /**
   * String translations.
   *
   * An array is keyed by language code, context and English source text.
   * Array contains translated strings or FALSE value (if there is no
   * translation for specific string).
   *
   * @var array
   */
  protected $strings;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'l10n_client_ui_translation_form';
  }

  /**
   * Sets languages and strings.
   *
   * @param array $languages
   *   Array of enabled language names.
   * @param array $strings
   *   Array of string translations.
   */
  public function setValues(array $languages, array $strings) {
    $this->languages = $languages;
    $this->strings = $strings;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['filters'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['clearfix']],
    ];
    $form['filters']['language'] = [
      '#title' => $this->t('Language'),
      '#type' => 'select',
      '#options' => $this->languages,
    ];
    $form['filters']['stats'] = [
      '#title' => $this->t('Stats'),
      '#type' => 'item',
      '#markup' => '<div class="l10n_client_ui--stats"><div class="l10n_client_ui--stats-done"></div></div>',
    ];
    $form['filters']['type'] = [
      '#title' => $this->t('Find and translate'),
      '#type' => 'select',
      '#options' => [
        'false' => $this->t('Untranslated strings'),
        'true' => $this->t('Translated strings'),
      ],
    ];
    $form['filters']['search'] = [
      '#title' => $this->t('Contains'),
      '#type' => 'search',
      '#placeholder' => $this->t('Search'),
    ];

    $form['list'] = [
      '#type' => 'container',
    ];
    $form['list']['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Source'),
        $this->t('Translation'),
        $this->t('Save'),
        $this->t('Skip'),
      ],
    ];

    // Order arrays alphabetically to make it more user-friendly.
    foreach ($this->strings as &$contexts) {
      ksort($contexts);
      foreach ($contexts as &$strings) {
        ksort($strings);
      }
    }
    unset($strings, $contexts);

    foreach ($this->strings as $langcode => $contexts) {
      foreach ($contexts as $context => $strings) {
        foreach ($strings as $string => $translation) {
          $i = $translation['lid'];
          $form['list']['table'][$i]['source'] = [
            '#plain_text' => $string,
            '#wrapper_attributes' => [
              'class' => ['l10n_client_ui--source-string'],
            ],
          ];
          $form['list']['table'][$i]['target'] = [
            '#type' => 'textarea',
            '#default_value' => $translation['translation'],
            '#rows' => 1,
            '#attributes' => [
              'data-l10n-client-ui-source' => $string,
              'data-l10n-client-ui-langcode' => $langcode,
              'data-l10n-client-ui-context' => $context,
              'data-l10n-client-ui-translated' => !empty($translation['translation']) ? "true" : "false",
              'data-l10n-client-ui-translation' => $translation['translation'],
            ],
          ];
          $form['list']['table'][$i]['save'] = [
            '#type' => 'submit',
            '#value' => '',
            '#name' => 'l10n-client-ui-save-' . $i,
            '#attributes' => [
              'class' => ['use-ajax-submit'],
              'data-l10n-client-ui-row' => $i,
            ],
            '#wrapper_attributes' => [
              'class' => ['l10n_client_ui--save'],
            ],
            '#ajax' => [
              'callback' => '::saveTranslation',
              'event' => 'click',
              'progress' => ['type' => 'throbber', 'message' => NULL],
            ],
          ];
          $form['list']['table'][$i]['skip'] = [
            '#plain_text' => 'X',
            '#wrapper_attributes' => [
              'class' => ['l10n_client_ui--skip'],
            ],
          ];
          $form['list'][$i] = [
            '#type' => 'container',
          ];
          $form['list'][$i]['langcode'] = [
            '#type' => 'value',
            '#value' => $langcode,
          ];
          $form['list'][$i]['context'] = [
            '#type' => 'value',
            '#value' => $context,
          ];
        }
      }
    }

    // Add a hidden button to trigger form rebuilding. When a cache render is
    // enabled the module can't collect all strings from a page, ajax api
    // ignores a render cache, and it allows us to collect all strings from
    // the page.
    $form['#preifx'] = '<div id="l10n-client-ui-translation-form">';
    $form['#suffix'] = '</div>';
    $form['rebuild'] = [
      '#type' => 'button',
      '#attributes' => [
        'class' => [
          'rebuild',
        ],
        'style' => 'display: none !important',
      ],
      '#ajax' => [
        'wrapper'  => 'l10n-client-ui-translation-form',
        'callback' => '::rebuild',
      ],
    ];

    return $form;
  }

  /**
   * Rebuilds the form on ajax request.
   *
   * During ajax drupal doesn't use a render cache it allows us to show all
   * strings on the active page.
   *
   * @param array $form
   *   Render array representing from.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Array of ajax commands to execute on form rebuild.
   */
  public function rebuild(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $response->addCommand(new SettingsCommand(['l10n_client_ui_strings' => $this->strings], TRUE));
    $response->addCommand(new ReplaceCommand(NULL, $form));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $triggeringElement = $form_state->getTriggeringElement();
    $row = $triggeringElement['#attributes']['data-l10n-client-ui-row'];

    $source = $form['list']['table'][$row]['source']['#plain_text'];
    $langcode = $form['list'][$row]['langcode']['#value'];
    $context = $form['list'][$row]['context']['#value'];
    $target = $form_state->getValue(['table', $row, 'target']);

    /** @var \Drupal\locale\StringDatabaseStorage $stringStorage */
    $stringStorage = \Drupal::service('locale.storage');
    $string = $stringStorage->findTranslation([
      'language' => $langcode,
      'source' => $source,
      'context' => $context,
    ]);

    if ($string) {
      $stringStorage->createTranslation([
        'lid' => $string->lid,
        'language' => $langcode,
        'translation' => $target,
      ])->save();

      _locale_refresh_translations([$langcode], [$string->lid]);
      _locale_refresh_configuration([$langcode], [$string->lid]);
    }

    $form_state->setRebuild();
  }

  /**
   * Implements the submit handler for the ajax call.
   *
   * @param array $form
   *   Render array representing from.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Array of ajax commands to execute on submit of the modal form.
   */
  public function saveTranslation(array $form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();

    $triggeringElement = $form_state->getTriggeringElement();
    $submit_selector = $triggeringElement['#attributes']['data-drupal-selector'];
    $response->addCommand(new SaveTranslationCommand('[data-drupal-selector="' . $submit_selector . '"]'));

    return $response;
  }

}
