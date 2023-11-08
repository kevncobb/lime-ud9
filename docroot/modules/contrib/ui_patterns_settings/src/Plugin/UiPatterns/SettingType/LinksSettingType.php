<?php

namespace Drupal\ui_patterns_settings\Plugin\UiPatterns\SettingType;

use Drupal\ui_patterns_settings\Plugin\ComplexSettingTypeBase;

/**
 * Complex setting type for links.
 *
 * @UiPatternsSettingType(
 *   id = "links",
 *   label = @Translation("Links")
 * )
 */
class LinksSettingType extends ComplexSettingTypeBase {

  /**
   * Normalize menu items.
   *
   * Don't inject URL object into patterns templates, use "title" as item
   * label and "url" as item target.
   *
   * @param array $items
   *   The items to convert.
   *
   * @return array
   */
  public static function normalize(array $items): array {
    foreach ($items as $index => &$item) {
      if (!is_array($item)) {
        unset($items[$index]);
        continue;
      }
      if (array_key_exists("text", $item)) {
        // Examples: links.html.twig, breadcrumb.html.twig, pager.html.twig,
        // views_mini_pager.html.twig.
        $item["title"] = $item["text"];
        unset($item["text"]);
      }
      if (!array_key_exists("title", $item)) {
        $item["title"] = $index;
      }
      if (array_key_exists("href", $item)) {
        // Examples: pager.html.twig, views_mini_pager.html.twig.
        $item["url"] = $item["href"];
        unset($item["href"]);
      }
      if (!isset($item["url"]) && isset($item["link"])) {
        // Example: links.html.twig.
        $item["url"] = $item["link"]["#url"];
        $item["url"]->setOptions($item["link"]["#options"]);
        unset($item["link"]);
      }
      if (array_key_exists("url", $item) && ($item["url"] instanceof Url)) {
        // Examples: menu.html.twig, links.html.twig.
        $url = $item["url"];
        $item["url"] = $url->toString();
        $options = $url->getOptions();
        if (isset($options["attributes"])) {
          $item["link_attributes"] = new Attribute($options["attributes"]);
        }
      }
      if (array_key_exists("below", $item)) {
        $item["below"] = self::normalize($item["below"]);
      }
    }
    $items = array_values($items);
    return $items;
  }

  /**
   * Convert pager to menu.
   *
   * Convert pager data structure to menu data structure. Useful for
   * pager.html.twig presenter template.
   *
   * @param array $items
   *   The pager items to convert.
   * @param int $current
   *   The current page.
   *
   * @return array
   */
  public static function convertPagerToMenu(array $pager, int $current): array {
    $items = [];
    if (isset($pager["first"])) {
      $items[] = $pager["first"];
    }
    if (isset($pager["previous"])) {
      $items[] = $pager["previous"];
    }
    if (isset($pager["pages"])) {
      foreach ($pager["pages"] as $index => $item) {
        $item["text"] = $index;
        if ($index == $current) {
          unset($item["href"]);
        }
        $items[] = $item;
      }
    }
    if (isset($pager["next"])) {
      $items[] = $pager["next"];
    }
    if (isset($pager["last"])) {
      $items[] = $pager["last"];
    }
    return $items;
  }

  /**
   * Convert mini pager to menu.
   *
   * Convert views mini pager data structure to menu data structure. Useful for
   * views-mini-pager.html.twig presenter template.
   *
   * @param array $items
   *   The pager items to convert.
   *
   * @return array
   */
  public static function convertMiniPagerToMenu(array $pager): array {
    $items = [];
    if ($pager["previous"]) {
      $items[] = $pager["previous"];
    }
    $items[] = [
      "text" => $pager["current"],
    ];
    if ($pager["next"]) {
      $items[] = $pager["next"];
    }
    return $items;
  }

}
