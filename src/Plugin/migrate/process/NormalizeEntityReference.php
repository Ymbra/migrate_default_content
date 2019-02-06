<?php

namespace Drupal\migrate_default_content\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Transforms values into an array of arrays.
 *
 * @MigrateProcessPlugin(
 *   id = "normalize_entity_reference",
 *   handle_multiples = TRUE
 * )
 */
class NormalizeEntityReference extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_string($value)) {
      $value = explode($this->configuration['delimiter'], $value);
    }
    $value = $value ?? [];
    foreach ($value as $key => &$subvalue) {
      if (is_array($subvalue)) {
        $count = 0;
        foreach (array_keys($subvalue) as $subkey) {
          // Replace any key in the first index with the replacement.
          if ($count === 0 && $subkey != $this->configuration['replacement_key']) {
            $subvalue[$this->configuration['replacement_key']] = $subvalue[$subkey];
            unset($subvalue[$subkey]);
          }
          else {
            break;
          }
          $count++;
        }
      }
      elseif (is_string($subvalue)) {
        $subvalue = [$this->configuration['replacement_key'] => $subvalue];
      }
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
