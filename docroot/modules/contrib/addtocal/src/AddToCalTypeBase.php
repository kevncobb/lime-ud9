<?php

namespace Drupal\addtocal;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList;
use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AddToCalTypeBase extends PluginBase implements AddToCalTypeInterface {

  const RF3339_FORMAT = 'Ymd\THis\Z';
  const DT_FORMAT = 'Ymd\THis';

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a AddToCalPluginBase object.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param array $settings
   * @param int $delta
   * @return array
   */
  protected function extractEventDetails(EntityInterface $entity, array $settings, $delta) {
    $entity_type = $entity->getEntityTypeId();
    $field_name = $settings['field_name'];

    /** @var DateTimeFieldItemList $dates */
    $dates = $entity->get($field_name);

    // Date range field has different structure
    if (!empty($dates[$delta]->start_date) && !empty($dates[$delta]->end_date)) {
      $start_date_object = $dates[$delta]->start_date;
      $end_date_object = $dates[$delta]->end_date;
    }
    else {
      $start_date_object = $end_date_object = $dates[$delta]->date;
    }

    $timezone = !empty($settings['timezone_override']) ? $settings['timezone_override'] : NULL;
    $start_date = $this->dateFormatter->format($start_date_object->getTimestamp(), 'custom', $settings['date_format'], $timezone);
    $end_date = $this->dateFormatter->format($end_date_object->getTimestamp(), 'custom', $settings['date_format'], $timezone);

    if ($settings['location']['value']) {
      $location = $this->extractFieldValue($entity, $settings['location']['value']);
    }
    else {
      // @TODO: Token replace
      $location = $settings['location']['tokenized'];
    }

    if ($settings['description']['value']) {
      $description = $this->extractFieldValue($entity, $settings['description']['value']);
    }
    else {
      // @TODO: Token replace
      $description = $settings['description']['tokenized'];
    }

    if (strlen($description) > 1024) {
      $description = Unicode::truncate($description, 1024, TRUE, TRUE);
    }

    $url = $entity->toUrl()->setAbsolute()->toString();

    $title = $entity->label();

    $overridable_items = [
      'override_status' => false,
      'title' => $title,
      'location' => $location,
      'description' => $description
    ];

    // Trigger hook_addtocal_alter().
    // Allow modules to override addtocal fields.
    \Drupal::service('module_handler')->alter('addtocal', $overridable_items);

    // If any override was found - set the fields to the override values.
    if ($overridable_items['override_status']) {
      $title       = $overridable_items['title'];
      $location    = $overridable_items['location'];
      $description = $overridable_items['description'];
    }

    return [
      'title' => $title,
      'start' => $start_date,
      'end' => $end_date,
      'timezone' => $timezone,
      'location' => $location,
      'description' => $description,
      'entity_id' => $entity->id(),
      'entity_type' => $entity_type,
      'url' => $url,
      'rfc3339' => $this->rfc3339Date($start_date_object, $end_date_object, $timezone),
    ];
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param $field_name
   * @return string
   */
  protected function extractFieldValue(EntityInterface $entity, $field_name) {
    $output = '';

    if (!empty($field_name)) {
      $value = $entity->get($field_name)->getValue();
      $instance = FieldStorageConfig::loadByName($entity->getEntityTypeId(), $field_name);
      if ($instance->getType() == 'address') {
        $address = $value[0];
        $string = '';
        if (!empty($address['address_line1'])) {
          $string .= $address['address_line1'] . ' ';
        }
        if (!empty($address['address_line2'])) {
          $string .= $address['address_line2'] . ', ';
        }
        if (!empty($address['locality'])) {
          $string .= $address['locality'] . ', ';
        }
        if (!empty($address['administrative_area'])) {
          $string .= $address['administrative_area'] . ' ';
        }
        if (!empty($address['postal_code'])) {
          $string .= $address['postal_code'] . ', ';
        }
        if (!empty($address['country_code'])) {
          $string .= $address['country_code'];
        }
        $output = $string;
      }
      else {
        $replace_strings = [
          '&nbsp;' => '',
          '<br />' => '',
          PHP_EOL => '',
        ];

        $output = $value[0]['value'];

        foreach ($replace_strings as $search => $replace) {
          $output = str_replace($search, $replace, $output);
        }
      }
    }
    return strip_tags($output);
  }

  /**
   * Returns an array containing RFC 3339 formatted start and end dates.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $start
   * @param \Drupal\Core\Datetime\DrupalDateTime $end
   * @param $timezone
   * @return array
   */
  protected function rfc3339Date(DrupalDateTime $start, DrupalDateTime $end, $timezone) {
    $start_timestamp = $start->getTimestamp();
    $end_timestamp = $end->getTimestamp();

    $start_date = gmdate(self::RF3339_FORMAT, $start_timestamp);
    $end_date = gmdate(self::RF3339_FORMAT, $end_timestamp);

    return [
      'start' => $start_date,
      'end' => $end_date,
      'both' => $start_date . '/' . $end_date,
      'local_start' => $this->dateFormatter->format($start_timestamp, 'custom', self::DT_FORMAT, $timezone),
      'local_end' => $this->dateFormatter->format($end_timestamp, 'custom', self::DT_FORMAT, $timezone),
    ];
  }

}
