<?php

namespace Drupal\address\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Sql\DefaultTableMapping;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drush\Commands\DrushCommands;
use Drush\Drupal\Commands\sql\SanitizePluginInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Provides a drush sql:sanitize plugin for address fields.
 *
 * It overwrites key columns with fixed data.
 */
class SanitizeAddress extends DrushCommands implements SanitizePluginInterface {

  /**
   * The address field columns to be sanitized.
   *
   * @var string[]
   */
  const FIELD_COLUMNS = [
    'country_code',
    'administrative_area',
    'locality',
    'dependent_locality',
    'postal_code',
    'sorting_code',
    'address_line1',
    'address_line2',
    'address_line3',
    'organization',
    'given_name',
    'additional_name',
    'family_name',
  ];

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates the address field sanitizer.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(Connection $database, EntityFieldManagerInterface $entity_field_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct();
    $this->database = $database;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   *
   * Overwrites address field columns.
   *
   * @hook post-command sql-sanitize
   */
  public function sanitize($result, CommandData $commandData) {
    $address_fields = $this->getAddressFields($commandData->options());
    foreach ($address_fields as $entity_type_id => $field_names) {
      $context = ['@entity_type_id' => $entity_type_id];
      try {
        $storage = $this->entityTypeManager->getStorage($entity_type_id);
        $mapping = $this->getTableMapping($entity_type_id);
        foreach ($field_names as $field_name) {
          $column_names = $mapping->getColumnNames($field_name);
          foreach ($this->getAllFieldTableNames($entity_type_id, $field_name) as $field_table) {
            foreach (static::FIELD_COLUMNS as $field_column) {
              $this->sanitizeColumn($field_table, $column_names[$field_column], $field_column);
            }
          }
        }

        $storage->resetCache();
        $this->logger()
          ->success(dt('Sanitized @entity_type_id address fields.', $context));
      }
      catch (\Exception $e) {
        $context += ['@message' => $e->getMessage()];
        $this->logger()->warning(dt("Unable to sanitize @enitity_type_id address fields: @message", $context));
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @hook on-event sql-sanitize-confirms
   */
  public function messages(&$messages, InputInterface $input) {
    $messages[] = dt('Sanitize address fields.');
  }

  /**
   * Returns the names of address fields to be sanitized.
   *
   * @return string[][]
   *   An associative array keyed by entity type ID of arrays of field names.
   */
  protected function getAddressFields(array $options) {
    // Get an array of arrays of address field names, keyed by entity type ID.
    $fields = array_map(function (array $field_bundle_data) {
      return array_keys($field_bundle_data);
    }, $this->entityFieldManager->getFieldMapByFieldType('address'));
    // Filter out allowed fields.
    $allowed = $this->getAllowedFields($options);
    $filtered = array_map(function (array $field_names) use ($allowed) {
      return array_diff($field_names, $allowed);
    }, $fields);
    // Filter out entity types that no longer have any fields to be sanitized.
    return array_filter($filtered);
  }

  /**
   * Returns the fields that have been explicitly allowed via an option.
   *
   * It supports the 'allowlist-fields' and deprecated 'whitelist-fields'
   * options.
   *
   * @param array $options
   *   The options array.
   *
   * @return string[]
   *   An array of allowed field names.
   *
   * @see \Drush\Drupal\Commands\sql\SanitizeUserFieldsCommands::options()
   */
  protected function getAllowedFields(array $options) {
    /** @deprecated Use $options['allowlist-fields'] instead. */
    $allowed = explode(',', $options['whitelist-fields']);
    $allowed = array_merge($allowed, explode(',', $options['allowlist-fields']));
    return array_filter($allowed);
  }

  /**
   * Update a table column with sanitized data.
   *
   * @param string $table
   *   The table name to update.
   * @param string $column
   *   The database column name to update.
   * @param string $field_name
   *   The field name to update.
   */
  protected function sanitizeColumn(string $table, string $column, string $field_name) {
    $not_empty = $this->database->condition('AND')
      ->condition($column, NULL, 'IS NOT NULL')
      ->condition($column, '', '<>');
    $replacement = '[' . dt('Sanitized') . ']';
    // Certain fields have expected formatting.
    if ($field_name == 'country_code') {
      $replacement = 'US';
    }
    elseif ($field_name == 'administrative_area') {
      $replacement = 'DC';
    }
    elseif ($field_name == 'postal_code') {
      $replacement = '20500';
    }
    $this->database->update($table)
      ->condition($not_empty)
      ->fields([$column => $replacement])
      ->execute();
  }

  /**
   * Gets all the table names in which an entity field is stored.
   *
   * When there's no need to support Drupal 8 switch to
   * Drupal\Core\Entity\Sql\TableMappingInterface::getAllFieldTableNames().
   *
   * @param string $entity_type_id
   *   The ID of the entity type the field's attached to.
   * @param string $field_name
   *   The name of the entity field to return the tables names for.
   *
   * @return string[]
   *   An indexed array of table names.
   */
  protected function getAllFieldTableNames($entity_type_id, $field_name) {
    $mapping = $this->getDefaultTableMapping($entity_type_id);
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $definitions = $this->entityFieldManager->getFieldStorageDefinitions($entity_type_id);
    $definition = $definitions[$field_name];

    $tables = [$mapping->getFieldTableName($field_name)];
    // Ensure we get both the main table and revision table where appropriate.
    if ($entity_type->isRevisionable()) {
      if ($mapping->requiresDedicatedTableStorage($definition)) {
        $tables[] = $mapping->getDedicatedRevisionTableName($definition);
      }
      else {
        $tables[] = $entity_type->getRevisionDataTable();
      }
    }
    return $tables;
  }

  /**
   * Returns the table mapping for a given entity type backed by SQL storage.
   *
   * @param string $entity_type_id
   *   The entity type ID to get the table mapping for.
   *
   * @return \Drupal\Core\Entity\Sql\TableMappingInterface
   *   The table mapping object.
   *
   * @throws \RuntimeException
   *   If the entity storage doesn't implement \Drupal\Core\Entity\Sql\SqlEntityStorageInterface.
   */
  protected function getTableMapping($entity_type_id) {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    if (!$storage instanceof SqlEntityStorageInterface) {
      $context = ['!entity_type_id' => $entity_type_id];
      throw new \RuntimeException(dt("Unable to get table mapping from !entity_type_id entity storage service.", $context));
    }
    return $storage->getTableMapping();
  }

  /**
   * Returns the table mapping for an entity type.
   *
   * In Drupal 8 the table mapping interface didn't provide sufficient methods
   * for some kinds of low-level operations and we need to tightly couple to the
   * core default table mapping class rather than an interface.
   *
   * @param string $entity_type_id
   *   The entity type to get the table mapping for.
   *
   * @return \Drupal\Core\Entity\Sql\DefaultTableMapping
   *   The table mapping object.
   *
   * @throws \RuntimeException
   *   If the entity type storage doesn't use or expose the default table
   *   mapping.
   *
   * @see https://www.drupal.org/project/drupal/issues/2960147
   */
  protected function getDefaultTableMapping($entity_type_id) {
    $mapping = $this->getTableMapping($entity_type_id);
    if (!$mapping instanceof DefaultTableMapping) {
      throw new \RuntimeException(dt("Table mapping class must be instance of DefaultTableMapping"));
    }
    return $mapping;
  }

}
