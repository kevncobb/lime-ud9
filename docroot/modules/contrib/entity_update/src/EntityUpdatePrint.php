<?php

namespace Drupal\entity_update;

/**
 * EntityCheck CLI Print class.
 */
class EntityUpdatePrint {

  /**
   * Enable / Disable echo.
   */
  protected static $echoPrintEnable = TRUE;

  /**
   * Enable / Disable echo.
   */
  public static function setEchoPrintEnable($echoPrintEnable) {
    self::$echoPrintEnable = $echoPrintEnable;
  }

  /**
   * Print.
   *
   * @param $text
   */
  public static function echoPrint($text, $indent = 0, $handle = NULL, $newline = TRUE) {
    if (!self::$echoPrintEnable) {
      return;
    }
    echo "$text";
    if ($newline) {
      echo "\n";
    }
  }

  /**
   * Check is CLI then run print.
   */
  public static function drushPrint($message = '', $indent = 0, $handle = NULL, $newline = TRUE) {
    if (php_sapi_name() == 'cli') {
      self::echoPrint($message, $indent, $handle, $newline);
    }
  }

  /**
   * Check is CLI then run drush_log.
   */
  public static function drushLog($message, $type = LogLevel::NOTICE, $error = NULL, $ui_print = FALSE) {
    if (php_sapi_name() == 'cli') {
      self::echoPrint($message);
    }
    elseif ($ui_print) {
      \Drupal::messenger()->addMessage($message, $type);
    }
  }

  /**
   * Show the summary of an entity type.
   *
   * @param string $type
   *   The entity type id.
   */
  public static function displaySummery($type) {
    try {
      $entity_type = \Drupal::entityTypeManager()->getDefinition($type);

      $query = \Drupal::entityQuery($type);
      $ids = $query->execute();

      self::echoPrint("Entity type  : " . $type);
      self::echoPrint("Label        : " . $entity_type->getLabel());
      self::echoPrint("Group        : " . $entity_type->getGroupLabel());
      self::echoPrint("Class        : " . $entity_type->getClass());
      self::echoPrint("Nb of Items  : " . count($ids));
      self::echoPrint("Base table   : " . $entity_type->getBaseTable());
      self::echoPrint("Data table   : " . $entity_type->getDataTable());
      self::echoPrint("Bundle Label : " . $entity_type->getBundleLabel());
      self::echoPrint("Bundle Of    : " . $entity_type->getBundleOf());
      self::echoPrint("Bundle Type  : " . $entity_type->getBundleEntityType());
      self::echoPrint("Admin perm   : " . $entity_type->getAdminPermission());
    }
    catch (\Exception $ex) {
      self::echoPrint($ex->getMessage());
    }
  }

  /**
   * Print a table to drush terminal.
   *
   * @param array $table
   *   The table to print.
   */
  public static function drushPrintTable(array $table) {

    // Check execution from CLI.
    if (php_sapi_name() != 'cli') {
      return;
    }

    $cols = exec('tput cols');
    $line_empty = "|" . str_repeat("-", $cols - 2) . "|";
    self::echoPrint($line_empty);

    $header = empty($table['#header']) ? NULL : $table['#header'];
    $rows = empty($table['#rows']) ? NULL : $table['#rows'];

    // Calculate colones size.
    $csizes = [];
    if ($rows) {
      if ($header) {
        $rows['header'] = $header;
      }
      foreach ($rows as $row) {
        $idx = 0;
        foreach ($row as $txt) {
          if (empty($csizes[$idx]) || $csizes[$idx] < strlen($txt)) {
            $csizes[$idx] = strlen($txt);
          }
          $idx++;
        }
      }
      // Remove temporerly added header.
      if ($header) {
        unset($rows['header']);
      }
    }
    elseif ($header) {
      foreach ($header as $txt) {
        $csizes[] = strlen($txt);
      }
    }

    // Print caption.
    if (!empty($table['#caption'])) {
      $t = strlen($table['#caption']);
      if ($t < $cols - 2) {
        $line = str_repeat(" ", ((int) ($cols - $t) / 2));
        $line = $line . $table['#caption'] . $line;
      }
      else {
        $line = $table['#caption'];
      }
      self::echoPrint($line);
      self::echoPrint($line_empty);
    }

    // Print header.
    if ($header) {
      $line = "|";
      $idx = 0;
      foreach ($header as $txt) {
        $line .= " " . $txt . str_repeat(" ", $csizes[$idx] - strlen($txt)) . "|";
        $idx++;
      }
      $line = substr($line, 0, $cols);
      self::echoPrint($line);
      self::echoPrint($line_empty);
    }

    // Print data.
    if ($rows) {
      foreach ($rows as $row) {
        $line = "|";
        $idx = 0;
        foreach ($row as $txt) {
          $line .= " " . $txt . str_repeat(" ", $csizes[$idx] - strlen($txt)) . "|";
          $idx++;
        }
        $line = substr($line, 0, $cols);
        self::echoPrint($line);
      }
    }

    // Print data empty message.
    else {
      $txt = dt('No data to display');
      $t = strlen($txt);
      if ($t < $cols - 2) {
        $line = str_repeat(" ", ((int) ($cols - $t) / 2));
        $line = $line . $txt . $line;
      }
      else {
        $line = $txt;
      }
      self::echoPrint($line);
    }

    self::echoPrint($line_empty);
  }

}
