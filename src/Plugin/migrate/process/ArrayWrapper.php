<?php

namespace Drupal\migrate_default_content\Plugin\migrate\process;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Iterator requires an array, so wraps content to be iterable..
 *
 * @MigrateProcessPlugin(
 *   id = "array_wrapper",
 *   handle_multiples = TRUE
 * )
 */
class ArrayWrapper extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Wraps value into an array.
    return isset($value) ? [$value] : $value;
  }

}
