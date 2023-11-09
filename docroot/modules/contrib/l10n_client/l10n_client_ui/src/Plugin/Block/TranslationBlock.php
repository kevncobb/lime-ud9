<?php

namespace Drupal\l10n_client_ui\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\l10n_client_ui\Form\TranslationForm;
use Drupal\l10n_client_ui\LocalizationClientUi;

/**
 * Provides a 'TranslationBlock' Block.
 *
 * This block renders our translation form via a lazy_builder.
 * We retrieve translatable strings of a current request from a
 * InterfaceTranslationRecorder, but we can lose some strings if our form is
 * rendered before loading of those strings.
 *
 * @Block(
 *   id = "l10n_client_ui_translation",
 *   admin_label = @Translation("Client translation block"),
 * )
 */
class TranslationBlock extends BlockBase implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return ['buildClientUiForm'];
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    if (!LocalizationClientUi::access()) {
      return AccessResult::forbidden();
    }
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#lazy_builder' => [__CLASS__ . '::buildClientUiForm', []],
      '#create_placeholder' => TRUE,
    ];
  }

  /**
   * Block's lazy builder callback.
   *
   * @return array
   *   Block render array.
   */
  public static function buildClientUiForm(): array {
    // Collect a list of language names for the used languages too.
    $language_list = \Drupal::languageManager()->getLanguages();
    $languages = [];

    // Handle recorded interface translation data.
    /** @var \Drupal\l10n_client_ui\InterfaceTranslationRecorder $interface_recorder */
    $interface_recorder = \Drupal::service('string_translator.l10n_client_ui');
    $strings = $interface_recorder->getRecordedData();
    if (count($strings)) {
      foreach ($strings as $langcode => $contexts) {
        $languages[$langcode] = $language_list[$langcode]->getName();
        foreach ($contexts as $context => $string_list) {
          foreach ($string_list as $string => &$target) {
            /** @var \Drupal\locale\StringDatabaseStorage $translation */
            $translation = \Drupal::service('locale.storage')->findTranslation([
              'language' => $langcode,
              'source' => $string,
              'context' => $context,
            ]);
            $strings[$langcode][$context][$string] = [
              'translation' => $translation && !empty($translation->translation) ? $translation->translation : FALSE,
              'lid' => $translation->lid ?? NULL,
            ];
          }
        }
      }
    }

    $form = new TranslationForm();
    $form->setValues($languages, $strings);
    $for = \Drupal::formBuilder()->getForm($form);

    // l10n_client_ui wrapper added because of
    // https://www.drupal.org/project/drupal/issues/2609250
    return [
      'l10n_client_ui' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'l10n_client_ui--container',
          ],
        ],
        'form' => $for,
        '#attached' => [
          'library' => ['l10n_client_ui/l10n_client_ui'],
          'drupalSettings' => [
            'l10n_client_ui_strings' => $strings,
          ],
        ],
      ],
    ];
  }

}
