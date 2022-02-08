<?php
namespace Drupal\addtocal\Plugin\Field\FieldFormatter;

use Drupal\addtocal\Form\AddToCalForm;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeCustomFormatter;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;

/**
 *
 *
 * @FieldFormatter(
 *  id = "addtocal_view",
 *  label = @Translation("Add to Cal"),
 *  field_types = {
 *    "date",
 *    "datestamp",
 *    "datetime",
 *    "daterange",
 *  }
 * )
 */
class AddtocalView extends DateTimeCustomFormatter {

  /**
   * Defines the default settings for this plugin.
   *
   * @return array
   *   A list of default settings, keyed by the setting name.
   */
  static public function defaultSettings() {
    return [
        'location' => ['value' => FALSE, 'tokenized' => ''],
        'description' => ['value' => FALSE, 'tokenized' => ''],
        'past_events' => FALSE,
        'separator' => '-',
      ] + parent::defaultSettings();
  }

  /**
   *
   * @return string[]
   *   A short summary of the formatter settings.
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $settings = $this->getSettings();
    $location = $settings['location']['value'] ? $settings['location']['value'] : $this->t("Static Text");
    $description = $settings['description']['value'] ? $settings['description']['value'] : $this->t("Static Text");
    $summary[] = $this->t('Location field: %location', ['%location' => $location]);
    $summary[] = $this->t('Description field: %description', ['%description' => $description]);

    // Date Range field type settings
    $field = $this->fieldDefinition;
    if ($field->getType() == 'daterange') {
      if ($separator = $this->getSetting('separator')) {
        $summary[] = $this->t('Separator: %separator', ['%separator' => $separator]);
      }
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $settings = $this->getSettings();
    $field = $this->fieldDefinition;
    $location_field_types = ['string', 'text_with_summary', 'address'];
    $description_field_types = ['string', 'text_long', 'text_with_summary'];
    $description_options = $location_options = [FALSE => 'None'];

    $entity_field_list = \Drupal::service('entity_field.manager')
      ->getFieldDefinitions($field->getTargetEntityTypeId(), $field->getTargetBundle());
    foreach ($entity_field_list as $entity_field) {
      // Filter out base fields like nid, uuid, revisions, etc.
      if ($entity_field->getFieldStorageDefinition()->isBaseField() == FALSE) {
        if (in_array($entity_field->get('field_type'), $location_field_types)) {
          $location_options[$entity_field->get('field_name')] = $entity_field->getLabel();
        }
        if (in_array($entity_field->get('field_type'), $description_field_types)) {
          $description_options[$entity_field->get('field_name')] = $entity_field->getLabel();
        }
      }
    }

    // Date Range field type settings
    if ($field->getType() == 'daterange') {
      $form['separator'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Date separator'),
        '#description' => $this->t('The string to separate the start and end dates'),
        '#default_value' => $this->getSetting('separator'),
      ];
    }

    $form['location'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Location'),
      '#open' => TRUE,
    ];
    $form['location']['value'] = [
      '#title' => $this->t('Location Field:'),
      '#type' => 'select',
      '#options' => $location_options,
      '#default_value' => isset($settings['location']['value']) ? $settings['location']['value'] : '',
      '#description' => $this->t('A field to use as the location for calendar events.'),
    ];
    $form['location']['tokenized'] = [
      '#title' => $this->t('Tokenized Location Contents:'),
      '#type' => 'textarea',
      '#default_value' => isset($settings['location']['tokenized']) ? $settings['location']['tokenized'] : '',
      '#description' => $this->t('You can insert static text or use tokens (see the token chart below).'),
    ];
    $form['description'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Description'),
      '#open' => TRUE,
    ];
    $form['description']['value'] = [
      '#title' => $this->t('Description Field:'),
      '#type' => 'select',
      '#options' => $description_options,
      '#default_value' => $this->getSetting('description'),
      '#description' => $this->t('A field to use as the description for calendar events. <em>The contents used from this field will be truncated to 1024 characters</em>.'),
    ];
    $form['description']['tokenized'] = [
      '#title' => $this->t('Tokenized Description Contents:'),
      '#type' => 'textarea',
      '#default_value' => isset($settings['description']['tokenized']) ? $settings['description']['tokenized'] : '',
      '#description' => $this->t('You can insert static text or use tokens (see the token chart below).'),
    ];
    $form['past_events'] = [
      '#title' => $this->t('Show Add to Cal widget for Past Events'),
      '#type' => 'checkbox',
      '#default_value' => $settings['past_events'],
      '#description' => $this->t('Show the widget for past events.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $entity = $items->getEntity();
    $settings = $this->getSettings();

    // Appends the field name into the settings for form use
    $field = $this->fieldDefinition;
    $field_name = $field->get('field_name');
    $settings['field_name'] = $field_name;

    foreach ($items as $delta => $item) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
      $start_date = NULL;
      /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
      $end_date = NULL;

      if ($field->getType() == 'daterange') {
        if (!empty($item->start_date)) {
          $start_date = $item->start_date;
        }
        if (!empty($item->end_date)) {
          $end_date = $item->end_date;
        }
      }
      else if ($field->getType() == 'date_recur') {
        if (!empty($item->value)) {
          $start_date = new DrupalDateTime($item->value, 'UTC');
        }
        if (!empty($item->end_value)) {
          $end_date = new DrupalDateTime($item->end_value, 'UTC');
        }
      }
      else if (!empty($item->date)) {
        $start_date = $item->date;
      }

      if ($start_date && $end_date && $start_date->format('U') !== $end_date->format('U')) {
        $elements[$delta] = [
          'start_date' => $this->buildDate($start_date),
          'separator' => ['#plain_text' => ' ' . $settings['separator'] . ' '],
          'end_date' => $this->buildDate($end_date),
        ];
      }
      elseif ($start_date) {
        $elements[$delta] = $this->buildDate($start_date);
      }
      else {
        $elements[$delta] = [];
      }

      // Attaches the calendar form to each element
      $form = new AddToCalForm($entity, $settings, $delta);
      $form = \Drupal::formBuilder()->getForm($form);
      $elements[$delta] += $form;
    }

    return $elements;
  }

  /**
   * Creates a render array from a date object.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   A date object.
   *
   * @return array
   *   A render array.
   */
  protected function buildDate(DrupalDateTime $date) {
    if ($this->getFieldSetting('datetime_type') == DateTimeItem::DATETIME_TYPE_DATE) {

      // A date without time will pick up the current time, use the default.
      $date->setDefaultDateTime();
    }
    $this->setTimeZone($date);

    $build = [
      '#plain_text' => $this->formatDate($date),
      '#cache' => [
        'contexts' => [
          'timezone',
        ],
      ],
    ];

    return $build;
  }

}
