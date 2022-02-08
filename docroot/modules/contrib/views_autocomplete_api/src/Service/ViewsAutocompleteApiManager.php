<?php

namespace Drupal\views_autocomplete_api\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\views\Entity\View;
use Drupal\views\ViewExecutable;
use Drupal\views\ViewExecutableFactory;

/**
 * Class ViewsAutocompleteApiManager
 *
 * @package Drupal\views_autocomplete_api\Service
 *
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 */
class ViewsAutocompleteApiManager {

  /**
   * The object views execute.
   *
   * @var \Drupal\views\ViewExecutableFactory
   */
  protected $viewsExecute;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  use LoggerChannelTrait;

  /**
   * ViewsAutocompleteApiController constructor.
   *
   * @param \Drupal\views\ViewExecutableFactory $views_execute
   *   The object views execute.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer service.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   */
  public function __construct(ViewExecutableFactory $views_execute, AccountProxyInterface $current_user, Renderer $renderer, ConfigFactory $config_factory) {
    $this->viewsExecute = $views_execute;
    $this->currentUser = $current_user;
    $this->renderer = $renderer;
    $this->configFactory = $config_factory;
    $this->logger = $this->getLogger('views_auto_complete_api');
  }

  /**
   * Extract display id, built it if not send.
   *
   * @param string $display_id
   *   All display ids of all views.
   * @param int $count_view
   *   The number of views.
   *
   * @return array
   *   An array of display_id of all views.
   */
  public function getViewsDisplayId($display_id, $count_view) {
    if (!empty($display_id)) {
      $display_id = explode(',', $display_id);
      if (count($display_id) !== count($count_view)) {
        $this->logger->warning('Number of display are different from number of views, calling in parameters controller.');
      }

      return $display_id;
    }
    $display_id = [];
    for ($i = 0; $i < $count_view; $i++) {
      $display_id[$i] = 'default';
    }

    return $display_id;

  }

  /**
   * Prepare the arguments of views.
   *
   * @param string $views_arguments
   *   All arguments of all views.
   * @param int $count_view
   *   The number of views.
   *
   * @return array
   *   An array of all arguments of all views.
   */
  public function prepareArgumentViews($views_arguments, $count_view) {
    // Prepare arguments.
    $args_views = [];
    if (!empty($views_arguments)) {
      return $args_views;
    }
    $args = explode(',', $views_arguments);
    if (count($args) !== $count_view) {
      $this->logger->warning(
        'Number of views arguments are different from number of views, calling in parameters controller.'
      );
    }
    foreach ($args as $arg) {
      $args_views[] = explode('&', $arg);
    }

    return $args_views;
  }

  /**
   * Execute the views.
   *
   * @param \Drupal\views\Entity\View $view
   *   The view entity to execute.
   * @param string $display_id
   *   The display id of view.
   * @param string $search
   *   The words searched.
   * @param array $args_views
   *   Arguments of views.
   *
   * @throws \Exception
   */
  public function executeViews(View $view, $display_id, $search, array &$view_data, array $args_views = []) {
    // Init views.
    $view_execute = $this->initViews($view, $display_id, $args_views);
    $this->setFilter($view_execute, $search);
    // Execute the view to get results.
    $view_execute->executeDisplay();
    // Gets the current style plugin object.
    /* @var $currentStylePlugin \Drupal\views\Plugin\views\style\DefaultStyle */
    $currentStylePlugin = $view_execute->getStyle();
    $rendered_fields = [];

    if (!empty($view_execute->result && !empty($view_execute->field))) {
      foreach (array_keys($view_execute->result) as $index) {
        foreach (array_keys($view_execute->field) as $field_name) {
          $rendered_fields[$index][$field_name] = $currentStylePlugin->getField(
            $index,
            $field_name
          );
        }
      }
    }

    if ($data = $this->getData($rendered_fields, $search)) {
      // @todo catch display even if no results for header and footer.
      // Add Header if exist.
      if (!empty($view_execute->display_handler->options['header'])) {
        $header = $this->formatSpecialRow(
          'header',
          $view_execute->display_handler->renderArea('header'),
          $search
        );
        // Insert header.
        if (!empty($header)) {
          array_unshift($data, implode(PHP_EOL, $header));
        }
      }
      // Add Header if exist.
      if (!empty($view_execute->display_handler->options['footer'])) {
        $footer = $this->formatSpecialRow(
          'footer',
          $view_execute->display_handler->renderArea('footer'),
          $search
        );
        if (!empty($footer)) {
          $data[] = implode(PHP_EOL, $footer);
        }
      }
      $view_data = array_merge($view_data, $data);
    }
    elseif (!empty($view_execute->display_handler->options['empty'])) {
      $empty = $this->formatSpecialRow(
        'empty',
        $view_execute->display_handler->renderArea('empty'),
        $search
      );
      if (!empty($empty)) {
        $view_data = array_merge($view_data, [implode(PHP_EOL, $empty)]);
      }
    }
  }

  /**
   * Init the views and return the executable object.
   *
   * @param \Drupal\views\Entity\View $view
   *   The view entity.
   * @param string $display_id
   *   The dispaly id of view.
   * @param array $args_views
   *   Arguments of views.
   *
   * @return bool|\Drupal\views\ViewExecutable
   *   False if error, or the views executable object.
   */
  private function initViews(View $view, $display_id, array $args_views = []) {
    $view_execute = $this->viewsExecute->get($view);
    /* @var $view \Drupal\views\ViewExecutable */
    if (!$view_execute) {
      $this->logger->error('Can\'t load views "%view_name"', ['%view_name' => $view->id()]);

      return FALSE;
    }
    // Set display.
    if ($view_execute->setDisplay($display_id) == FALSE) {
      $this->logger->error('No display "%display_name" found for the views "%view_name"', [
        '%display_name' => $display_id,
        '%view_name' => $view->id(),
      ]);
      return FALSE;
    }
    // Check permission to the view display default(master).
    if (!$view_execute->access($display_id) && !$this->currentUser->hasPermission('administer views')) {
      $this->logger->warning('Access denied for the views "%view_name"', ['%view_name' => $view->id()]);
      return FALSE;
    }

    if (!empty($args_views)) {
      $view_execute->setArguments($args_views);
    }

    return $view_execute;
  }

  /**
   * Set filter on views executable.
   *
   * @param \Drupal\views\ViewExecutable $view_execute
   *   The view executable object.
   * @param string $search
   *   The word searched.
   */
  private function setFilter(ViewExecutable $view_execute, $search) {

    // Loop on each exposed filter.
    $filters = [];
    $display_handler = $view_execute->getDisplay();

    $options_filter = $display_handler->getOption('filters');
    foreach ($options_filter as &$options) {
      if (!empty($options['exposed']) && $options['exposed'] === TRUE && !empty($options['expose']['identifier'])) {
        $options['value'] = $search;
      }
    }
    $display_handler->overrideOption('filters', $filters);
  }

  /**
   * Get header of the view if exist.
   *
   * @param string $type
   *   Type request i.e header or footer.
   * @param array $data_views
   *   The header of the view.
   * @param string $search
   *   The search text.
   *
   * @return array
   *   An array of views data header.
   *
   * @throws \Exception
   */
  protected function formatSpecialRow($type, array $data_views, $search) {
    if (empty($data_views)) {
      return [];
    }
    $view_data_formatted = [];
    $row = '';
    foreach ($data_views as $area) {
      $value = $this->renderer->render($area);
      if ($value instanceof Markup) {
        $value = $value->__toString();
      }
      if (!empty($value) && strpos($value, '[autocomplete]')) {
        $value = str_replace('[autocomplete]', $search, $value);
      }
      $row .= $value;
    }
    $element = [
      '#theme' => 'views_autocomplete_api_special_row',
      '#type_group' => $type,
      '#row' => $row,
    ];
    $view_data_formatted[] = $this->renderer->render($element);

    return $view_data_formatted;
  }

  /**
   * Re-format the views data rendered.
   *
   * @param array $rendered_fields
   *   An array of rendered field.
   * @param string $search
   *   The search text.
   *
   * @return array
   *   An array wuth the data of views (the last and before last row).
   */
  protected function getData(array $rendered_fields, $search) {
    $view_data_formatted = [];
    // The String Which search for.
    foreach ($rendered_fields as $row) {
      // Content of rendered fields.
      $row_values = array_values($row);
      $count = count($row_values);

      $key = $rendered = $row_values[count($row) - 2];
      // Take the last field to allow to call more that one and
      // "Rewrite field" and call them all.
      if ($count > 1) {
        $rendered = $row_values[$count - 1];
      }
      // We doesn't allow html for key input.
      $viewData['value'] = strip_tags($key);
      // Highlight search word.
      if ($this->configFactory->get('views_autocomplete_api.settings')
          ->get('highlight') == TRUE) {
        $rendered = $this->highlightStr($rendered, $search);
      }
      $viewData['label'] = $rendered;
      $view_data_formatted[] = $viewData;
    }

    return $view_data_formatted;
  }

  /**
   * Higlight string searched.
   *
   * @param $haystack
   * @param $needle
   *
   * @return $haystack
   */
  public function highlightStr($haystack, $needle) {
    // Return $haystack if there is no highlight color or strings given,
    // nothing to do.
    if (empty($haystack) || empty($needle)) {
      return $haystack;
    }
    $patterns = $replacements = [];
    // Old regex : "/(?![^<]*>)$needle+/i".
    // First replacement.
    // $patterns[] = "'(?!((<.*?)|(<a.*?)))($needle)(?!(([^<>]*?)>)|([^>]*?</a>))'si";
    $patterns[] = "/(?![^<]*>)$needle+/i";
    $element = [
      '#theme' => 'views_autocomplete_api_highlight',
      '#search_word' => $needle,
    ];
    $replacements[] = $this->renderer->render($element);

    // Addon check translitered search query.
    $translitered_match = $this->removeAccents($needle);
    if ($needle != $translitered_match) {
      // Old regex : "/(?![^<]*>)$translitered_match+/i".
      // $patterns[] = "'(?!((<.*?)|(<a.*?)))($translitered_match)(?!(([^<>]*?)>)|([^>]*?</a>))'si";
      $patterns[] = "/(?![^<]*>)$translitered_match+/i";
      $element = [
        '#theme' => 'views_autocomplete_api_highlight',
        '#search_word' => $translitered_match,
      ];
      $replacements[] = $this->renderer->render($element);
    }
    // Replace for highlighting.
    $haystack = preg_replace($patterns, $replacements, $haystack);

    return $haystack;
  }

  /**
   * Delete accent from string.
   *
   * @param string $str
   *   String to convert.
   * @param string $encoding
   *   Format encoding.
   *
   * @return string
   *   The transformed string.
   * @todo find way to get list on all encoding et put it in config module.
   *
   */
  public function removeAccents($str, $encoding = 'utf-8') {
    // Convert all applicable characters to HTML entities.
    $str = htmlentities($str, ENT_NOQUOTES, $encoding);

    // Replace the html entiies, to get just the first letter without accent.
    // Exemple : "&ecute;" => "e", "&Ecute;" => "E", "Ã " => "a" ...
    $str = preg_replace(
      '#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#',
      '\1',
      $str
    );

    // Replace ligatures as : Œ, Æ ...
    // Example "Å“" => "oe".
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    // Delete other special character.
    $str = preg_replace('#&[^;]+;#', '', $str);

    return $str;
  }

}
