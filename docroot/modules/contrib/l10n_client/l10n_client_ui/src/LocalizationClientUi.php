<?php

namespace Drupal\l10n_client_ui;

/**
 * Helper class for a localization client ui.
 */
class LocalizationClientUi {

  /**
   * Static cache for the access result.
   *
   * @var bool
   */
  protected static $access;

  /**
   * Returns whether the localization client interface should be added.
   *
   * @return bool
   *   TRUE if the current user can run localization client interface,
   *   and FALSE if not.
   */
  public static function access(): bool {
    if (isset(self::$access)) {
      return self::$access;
    }

    self:: $access = \Drupal::currentUser()->hasPermission('use localization client ui') &&
    (
      \Drupal::languageManager()->getCurrentLanguage()->getID() != 'en' ||
      locale_is_translatable('en')
    );
    return self::$access;
  }

}
