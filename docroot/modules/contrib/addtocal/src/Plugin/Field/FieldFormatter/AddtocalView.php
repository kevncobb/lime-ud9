<?php

namespace Drupal\addtocal\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeCustomFormatter;
use Spatie\CalendarLinks\Generators\Google;
use Spatie\CalendarLinks\Generators\Ics;
use Spatie\CalendarLinks\Generators\WebOutlook;
use Spatie\CalendarLinks\Generators\WebOffice;
use Spatie\CalendarLinks\Generators\Yahoo;
use Spatie\CalendarLinks\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\smart_date\Plugin\Field\FieldType\SmartDateItem;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Add to Cal view formatter.
 *
 * @FieldFormatter(
 *  id = "addtocal_view",
 *  label = @Translation("Add to Cal"),
 *  field_types = {
 *    "date",
 *    "datestamp",
 *    "datetime",
 *    "daterange",
 *    "date_recur",
 *    "smartdate",
 *  }
 * )
 */
class AddtocalView extends DateTimeCustomFormatter {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->moduleHandler = $container->get('module_handler');
    $instance->token = $container->get('token');
    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public static function defaultSettings() {
    return [
      'separator' => '-',
      'event_title' => '',
      'location' => '',
      'description' => '',
      'past_events' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritDoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $field = $this->fieldDefinition;

    // Date Range field type settings.
    if ($field->getType() == 'daterange' || $field->getType() == 'date_recur') {
      $summary[] = $this->t('Separator: %separator', ['%separator' => $this->getSetting('separator')]);
    }

    $title = $this->getSetting('event_title');
    $summary[] = $this->t('Event title: %title', ['%title' => $title ?: $this->t('Entity label')]);

    $location = $this->getSetting('location');
    if ($location) {
      $summary[] = $this->t('Event location: %location', ['%location' => $location]);
    }

    $description = $this->getSetting('description');
    if ($description) {
      $summary[] = $this->t('Event description: %description', ['%description' => $description]);
    }

    $past_events = $this->getSetting('past_events') ? 'Yes' : 'No';
    $summary[] = $this->t('Show the widget for past events: %past_events', ['%past_events' => $past_events]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $field = $this->fieldDefinition;

    // Date Range field type settings.
    if ($field->getType() == 'daterange' || $field->getType() == 'date_recur') {
      $form['separator'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Date separator'),
        '#description' => $this->t('The string to separate the start and end dates'),
        '#default_value' => $this->getSetting('separator'),
      ];
    }

    $form['event_title'] = [
      '#title' => $this->t('Event title'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('event_title'),
      '#description' => $this->t('Optional - if left empty, the entity label will be used. You can use static text or tokens.'),
    ];

    $form['location'] = [
      '#title' => $this->t('Event location'),
      '#type' => 'textarea',
      '#default_value' => $this->getSetting('location'),
      '#description' => $this->t('Optional. You can use static text or tokens.'),
    ];

    $form['description'] = [
      '#title' => $this->t('Event description'),
      '#type' => 'textarea',
      '#default_value' => $this->getSetting('description'),
      '#description' => $this->t('Optional. You can use static text or tokens.'),
    ];

    $form['past_events'] = [
      '#title' => $this->t('Show Add to Cal widget for past events?'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('past_events'),
    ];

    $form['token_tree_link'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [
        $field->getTargetEntityTypeId(),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $entity = $items->getEntity();
    $field = $this->fieldDefinition;

    $elements['#attached']['library'][] = 'addtocal/addtocal';
    $elements['#cache']['contexts'][] = 'timezone';

    foreach ($items as $delta => $item) {
      $elements[$delta] = [];

      /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
      $start_date = $item->start_date ?? $item->date ?? NULL;
      /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
      $end_date = $item->end_date ?? $start_date;

      if ($item instanceof SmartDateItem) {
        $timezone = empty($item->timezone) ? NULL : $item->timezone;
        $start_date = DrupalDateTime::createFromTimestamp($item->value, $timezone);
        $end_date = DrupalDateTime::createFromTimestamp($item->end_value, $timezone);
      }

      if (!$start_date || !$end_date) {
        continue;
      }

      $this->setTimeZone($start_date);
      $this->setTimeZone($end_date);

      $is_all_day = in_array($this->getFieldSetting('datetime_type'), ['date', 'allday']);

      if ($is_all_day) {
        // A date without time will pick up the current time, set to midnight.
        $start_date->modify('midnight');
        $end_date->modify('midnight');
      }

      $is_start_date_before_end_date = $start_date->getPhpDateTime() < $end_date->getPhpDateTime();
      $is_now_before_start_date = new \DateTime('now') < $start_date->getPhpDateTime();

      $elements[$delta]['start_date']['#plain_text'] = $this->formatDate($start_date);

      if ($is_start_date_before_end_date) {
        $separator = $this->getSetting('separator');
        $elements[$delta]['separator']['#plain_text'] = $separator ? ' ' . $separator . ' ' : ' ';

        $elements[$delta]['end_date']['#plain_text'] = $this->formatDate($end_date);
      }

      $token_data = [
        $field->getTargetEntityTypeId() => $entity,
      ];

      $title = $this->token->replace($this->getSetting('event_title'), $token_data, ['clear' => TRUE]) ?: $entity->label();

      if ($is_all_day) {
        $date_diff = $end_date->diff($start_date);
        // Google calendar all day events count days a little differently:
        $diff_days = 1 + $date_diff->days;
        $link = Link::createAllDay($title, $start_date->getPhpDateTime(), $diff_days);
      }
      else {
        $link = Link::create($title, $start_date->getPhpDateTime(), $end_date->getPhpDateTime());
      }

      $link->address($this->token->replace($this->getSetting('location'), $token_data, ['clear' => TRUE]));
      $link->description($this->token->replace($this->getSetting('description'), $token_data, ['clear' => TRUE]));

      $element_id = 'addtocal-' . $entity->bundle() . '-' . $field->getName() . '-' . $entity->id() . '--' . $delta;

      $addtocal_access = $this->getSetting('past_events') ? TRUE : $is_now_before_start_date;

      $links = [
        '#theme' => 'addtocal_links',
        '#addtocal_link' => $link,
        '#id' => $element_id,
        '#attributes' => [],
        '#button_text' => $this->t('Add to Calendar'),
        '#button_attributes' => [
          'aria-label' => $this->t('Open Add to Calendar menu'),
        ],
        '#menu_attributes' => [],
        '#items' => [
          'google' => [
            'title' => $this->t('Google'),
            'aria-label' => $this->t('Add to Google Calendar'),
            'generator' => new Google(),
          ],
          'yahoo' => [
            'title' => $this->t('Yahoo!'),
            'aria-label' => $this->t('Add to Yahoo Calendar'),
            'generator' => new Yahoo(),
          ],
          'web_outlook' => [
            'title' => $this->t('Outlook.com'),
            'aria-label' => $this->t('Add to Outlook.com Calendar'),
            'generator' => new WebOutlook(),
          ],
          'web_office' => [
            'title' => $this->t('Office.com'),
            'aria-label' => $this->t('Add to Office.com Calendar'),
            'generator' => new WebOffice(),
          ],
          'ics' => [
            'title' => $this->t('iCal / MS Outlook'),
            'aria-label' => $this->t('Add to iCal / MS Outlook'),
            'generator' => new Ics(),
          ],
        ],
        '#access' => $addtocal_access,
      ];

      $context = [
        'items' => $items,
        'langcode' => $langcode,
        'delta' => $delta,
      ];
      $this->moduleHandler->alter('addtocal_links', $links, $context);

      $elements[$delta]['addtocal'] = $links;
    }

    return $elements;
  }

}
